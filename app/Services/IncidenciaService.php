<?php

namespace App\Services;

use App\Enums\EstadoIncidencia;
use App\Enums\TipoAccionHistorial;
use App\Enums\TipoSolicitante;
use App\Models\Empleado;
use App\Models\Incidencia;
use App\Models\User;
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
    ) {}

    // ─── Creación pública ────────────────────────────────────────────────────

    public function crear(array $data): Incidencia
    {
        return DB::transaction(function () use ($data) {
            $numeroEmpleado = (string) $data['numero_empleado'];

            $empleadoExistente = Empleado::where('numero_empleado', $numeroEmpleado)->first();

            $tipo = $empleadoExistente?->tipo
                ?? (isset($data['tipo_empleado']) ? TipoSolicitante::from($data['tipo_empleado']) : null);

            abort_if(! $tipo, 422, 'Debes indicar el tipo de empleado.');

            $incidencia = Incidencia::create([
                'folio' => $this->folioService->generar(),
                'numero_empleado' => $numeroEmpleado,
                'reportante_nombre' => $data['reportante_nombre'],
                'email_reportante' => $data['email_reportante'] ?? null,
                'tipo_solicitante' => $tipo,
                'area_id' => $data['area_id'],
                'fecha_incidencia' => $data['fecha_incidencia'],
                'tipo_incidencia' => $data['tipo_incidencia'],
                'minutos_retardo' => $data['minutos_retardo'] ?? null,
                'descripcion' => $data['descripcion'] ?? null,
                'estado' => EstadoIncidencia::PendienteJefe,
                'token_seguimiento' => Str::random(64),
            ]);

            Empleado::updateOrCreate(
                ['numero_empleado' => $numeroEmpleado],
                [
                    'nombre' => $data['reportante_nombre'],
                    'email' => $data['email_reportante'] ?? null,
                    'tipo' => $tipo,
                ],
            );

            $this->historialService->registrar(
                incidencia: $incidencia,
                tipo: TipoAccionHistorial::Creada,
            );

            $this->notificacionService->enviarConfirmacion($incidencia);

            return $incidencia;
        });
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
        });
    }

    // ─── Rechazo (cualquier nivel) ───────────────────────────────────────────

    public function rechazar(Incidencia $incidencia, User $revisor, string $motivo): void
    {
        abort_if(
            $incidencia->estado->esFinal(),
            422,
            'La incidencia ya se encuentra en un estado final.'
        );

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
