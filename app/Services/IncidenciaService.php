<?php

namespace App\Services;

use App\Enums\EstadoIncidencia;
use App\Enums\TipoAccionHistorial;
use App\Exceptions\LimiteIncidenciaExcepcion;
use App\Models\Empleado;
use App\Models\Incidencia;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class IncidenciaService
{
    public function __construct(
        private readonly FolioService $folioService,
        private readonly HistorialService $historialService,
        private readonly NotificacionService $notificacionService,
        private readonly ArchivoService $archivoService,
        private readonly ValidacionIncidenciaService $validacionIncidenciaService,
    ) {}

    // ─── Creación pública ────────────────────────────────────────────────────

    public function crear(array $data): Incidencia
    {
        $numeroEmpleado = (string) $data['numero_empleado'];

        $empleado = Empleado::where('numero_empleado', $numeroEmpleado)->firstOrFail();

        $tipo = $empleado->tipo;

        abort_if(! $tipo, 422, 'El empleado no tiene un tipo asignado. Contacta a Capital Humano.');

        $resultado = DB::transaction(function () use ($data, $numeroEmpleado, $tipo) {
            $minutosRetardo = (int) ($data['minutos_retardo'] ?? 0);
            $fechaIncidencia = Carbon::parse($data['fecha_incidencia'])->startOfDay();
            $tipoIncidencia = (string) $data['tipo_incidencia'];

            $razon = $this->validacionIncidenciaService->obtenerRazonRechazo(
                $numeroEmpleado,
                $tipo,
                $fechaIncidencia,
                $minutosRetardo,
                $tipoIncidencia
            );

            $esLimitExcedido = $razon !== null;

            $incidencia = Incidencia::create([
                'folio' => $this->folioService->generar(),
                'numero_empleado' => $numeroEmpleado,
                'reportante_nombre' => $data['reportante_nombre'],
                'email_reportante' => $data['email_reportante'] ?? null,
                'tipo_solicitante' => $tipo,
                'area_id' => $data['area_id'],
                'fecha_incidencia' => $data['fecha_incidencia'],
                'hora_incidencia' => $data['hora_incidencia'] ?? null,
                'tipo_incidencia' => $data['tipo_incidencia'],
                'minutos_retardo' => $data['minutos_retardo'] ?? null,
                'descripcion' => $data['descripcion'] ?? null,
                'estado' => $esLimitExcedido ? EstadoIncidencia::Rechazada : EstadoIncidencia::PendienteJefe,
                'token_seguimiento' => Str::random(64),
                'motivo_rechazo' => $esLimitExcedido ? $razon : null,
            ]);

            $this->historialService->registrar(
                incidencia: $incidencia,
                tipo: TipoAccionHistorial::Creada,
            );

            if ($esLimitExcedido) {
                $this->notificacionService->enviarRechazoPorLimite($incidencia, $razon);
            } else {
                $this->notificacionService->enviarConfirmacion($incidencia);
            }

            return ['incidencia' => $incidencia, 'esLimitExcedido' => $esLimitExcedido, 'razon' => $razon];
        });

        if ($resultado['esLimitExcedido']) {
            throw new LimiteIncidenciaExcepcion($resultado['razon']);
        }

        return $resultado['incidencia'];
    }

    // ─── Flujo de aprobación — Jefe Inmediato ────────────────────────────────

    public function aprobarJefe(Incidencia $incidencia, User $jefe, ?string $comentario = null): void
    {
        abort_if(
            $incidencia->estado !== EstadoIncidencia::PendienteJefe,
            422,
            'La incidencia no está pendiente de aprobación del jefe.'
        );

        DB::transaction(function () use ($incidencia, $jefe, $comentario) {
            $incidencia->update([
                'estado' => EstadoIncidencia::PendienteCapitalHumano,
                'revisado_por' => $jefe->id,
            ]);

            $this->historialService->registrar(
                incidencia: $incidencia,
                tipo: TipoAccionHistorial::Aprobada,
                userId: $jefe->id,
                comentario: $comentario ?? 'Aprobada por jefe inmediato.',
            );

            $this->notificacionService->enviarCambioEstado($incidencia);
        });
    }

    // ─── Flujo de aprobación — Capital Humano ───────────────────────────────

    public function aprobarCapitalHumano(Incidencia $incidencia, User $capitalHumano, ?string $comentario = null): void
    {
        abort_if(
            $incidencia->estado !== EstadoIncidencia::PendienteCapitalHumano,
            422,
            'La incidencia no está pendiente de aprobación de Capital Humano.'
        );

        DB::transaction(function () use ($incidencia, $capitalHumano, $comentario) {
            $incidencia->update([
                'estado' => EstadoIncidencia::PendienteSubdireccion,
                'revisado_por' => $capitalHumano->id,
            ]);

            $this->historialService->registrar(
                incidencia: $incidencia,
                tipo: TipoAccionHistorial::Aprobada,
                userId: $capitalHumano->id,
                comentario: $comentario ?? 'Aprobada por Capital Humano.',
            );

            $this->notificacionService->enviarCambioEstado($incidencia);
        });
    }

    // ─── Flujo de aprobación — Subdirección Académica ───────────────────────

    public function aprobarSubdireccion(Incidencia $incidencia, User $subdirector, ?string $comentario = null): void
    {
        abort_if(
            $incidencia->estado !== EstadoIncidencia::PendienteSubdireccion,
            422,
            'La incidencia no está pendiente de aprobación de la Subdirección.'
        );

        DB::transaction(function () use ($incidencia, $subdirector, $comentario) {
            $incidencia->update([
                'estado' => EstadoIncidencia::Aprobada,
                'revisado_por' => $subdirector->id,
            ]);

            $this->historialService->registrar(
                incidencia: $incidencia,
                tipo: TipoAccionHistorial::Aprobada,
                userId: $subdirector->id,
                comentario: $comentario ?? 'Aprobada por Subdirección Académica.',
            );

            $this->notificacionService->enviarCambioEstado($incidencia);
            $this->notificacionService->enviarResolucionFinal($incidencia);
        });
    }

    // ─── Flujo de aprobación — Sindicato ──────────────────────────────────

    public function aprobarSindicato(Incidencia $incidencia, User $sindicato, ?string $comentario = null): void
    {
        abort_if(
            $incidencia->estado->esFinal(),
            422,
            'La incidencia ya se encuentra en un estado final.'
        );

        DB::transaction(function () use ($incidencia, $sindicato, $comentario) {
            $incidencia->update([
                'estado' => EstadoIncidencia::Aprobada,
                'revisado_por' => $sindicato->id,
            ]);

            $this->historialService->registrar(
                incidencia: $incidencia,
                tipo: TipoAccionHistorial::Aprobada,
                userId: $sindicato->id,
                comentario: $comentario ?? 'Aprobada por Sindicato.',
            );

            $this->notificacionService->enviarCambioEstado($incidencia);
            $this->notificacionService->enviarResolucionFinal($incidencia);
        });
    }

    // ─── Rechazo (cualquier nivel) ───────────────────────────────────────────

    public function rechazar(Incidencia $incidencia, User $revisor, string $motivo, EstadoIncidencia ...$esperados): void
    {
        abort_if(
            $incidencia->estado->esFinal(),
            422,
            'La incidencia ya se encuentra en un estado final.'
        );

        if (! empty($esperados)) {
            abort_if(
                ! in_array($incidencia->estado, $esperados, true),
                422,
                'No puedes rechazar una incidencia en el estado actual.'
            );
        }

        DB::transaction(function () use ($incidencia, $revisor, $motivo) {
            $incidencia->update([
                'estado' => EstadoIncidencia::Rechazada,
                'revisado_por' => $revisor->id,
                'motivo_rechazo' => $motivo,
            ]);

            $this->historialService->registrar(
                incidencia: $incidencia,
                tipo: TipoAccionHistorial::Rechazada,
                userId: $revisor->id,
                comentario: $motivo,
            );

            $this->notificacionService->enviarCambioEstado($incidencia);
            $this->notificacionService->enviarResolucionFinal($incidencia);
        });
    }

    // ─── Acciones compartidas ────────────────────────────────────────────────

    public function comentar(
        Incidencia $incidencia,
        ?User $user,
        string $comentario,
        bool $esInterno = false,
    ): void {
        $this->historialService->registrar(
            incidencia: $incidencia,
            tipo: TipoAccionHistorial::Comentario,
            userId: $user?->id,
            comentario: $comentario,
            esInterno: $esInterno,
        );
    }

    public function adjuntarArchivo(Incidencia $incidencia, UploadedFile $file, ?User $user = null): void
    {
        DB::transaction(function () use ($incidencia, $file, $user) {
            $this->archivoService->almacenar($file, $incidencia, $user?->id);

            $this->historialService->registrar(
                incidencia: $incidencia,
                tipo: TipoAccionHistorial::ArchivoAdjunto,
                userId: $user?->id,
                comentario: "Archivo adjunto: {$file->getClientOriginalName()}",
            );
        });
    }
}
