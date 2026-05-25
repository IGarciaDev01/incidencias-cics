<?php

namespace Database\Seeders;

use App\Enums\EstadoIncidencia;
use App\Enums\RolUsuario;
use App\Enums\TipoAccionHistorial;
use App\Enums\TipoIncidencia;
use App\Enums\TipoSolicitante;
use App\Models\Area;
use App\Models\Empleado;
use App\Models\HistorialIncidencia;
use App\Models\Incidencia;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $password = Hash::make('11223344');

            $subdirector = $this->usuario('subdirector@test.com', 'Dra. Subdirección Administrativa', '900001', RolUsuario::Subdirector, $password);
            $capitalHumano = $this->usuario('capital.humano@test.com', 'Lic. Capital Humano', '900002', RolUsuario::CapitalHumano, $password);
            $sindicato = $this->usuario('sindicato@test.com', 'Representante Sindicato', '900003', RolUsuario::Sindicato, $password);

            $jefes = collect([
                $this->usuario('jefe.area.1@test.com', 'Jefe de Servicios Escolares', '900101', RolUsuario::JefeInmediato, $password),
                $this->usuario('jefe.area.2@test.com', 'Jefe de Administración', '900102', RolUsuario::JefeInmediato, $password),
                $this->usuario('jefe.area.3@test.com', 'Jefe de Vinculación', '900103', RolUsuario::JefeInmediato, $password),
            ]);

            $areas = collect([
                ['nombre' => 'Servicios Escolares', 'slug' => 'servicios-escolares'],
                ['nombre' => 'Administración', 'slug' => 'administracion'],
                ['nombre' => 'Vinculación', 'slug' => 'vinculacion'],
            ])->map(function (array $areaData, int $index) use ($jefes): Area {
                $jefe = $jefes[$index];
                $area = Area::updateOrCreate(
                    ['slug' => $areaData['slug']],
                    [
                        'nombre' => $areaData['nombre'],
                        'descripcion' => 'Área operativa con jefe asignado para datos demo.',
                        'jefe_id' => $jefe->id,
                        'activa' => true,
                    ]
                );

                $area->usuarios()->syncWithoutDetaching([
                    $jefe->id => ['es_jefe' => true],
                ]);

                return $area;
            });

            $empleados = collect(range(1, 20))->map(fn (int $index) => Empleado::updateOrCreate(
                ['numero_empleado' => '80'.str_pad((string) $index, 4, '0', STR_PAD_LEFT)],
                [
                    'nombre' => 'Empleado Demo '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                    'email' => 'empleado.demo.'.str_pad((string) $index, 2, '0', STR_PAD_LEFT).'@test.com',
                    'tipo' => $index % 3 === 0 ? TipoSolicitante::Docente : TipoSolicitante::Administrativo,
                    'password' => $password,
                ]
            ));

            $folio = 1;
            $year = now()->year;

            foreach ($empleados as $empleadoIndex => $empleado) {
                foreach ($this->incidenciasAnuales() as $incidenciaIndex => $data) {
                    $area = $areas[($empleadoIndex + $incidenciaIndex) % $areas->count()];
                    $jefe = $jefes->firstWhere('id', $area->jefe_id);
                    $estado = $this->estadoPara($incidenciaIndex);
                    $revisor = $this->revisorPara($estado, $jefe, $capitalHumano, $subdirector, $sindicato);
                    $fecha = $data['fecha'];
                    $createdAt = $fecha->setTime(8 + ($incidenciaIndex % 8), ($incidenciaIndex * 7) % 60);
                    $folioActual = sprintf('INC-%d-%04d', $year, $folio++);

                    $incidencia = Incidencia::updateOrCreate(
                        ['folio' => $folioActual],
                        [
                            'numero_empleado' => $empleado->numero_empleado,
                            'reportante_nombre' => $empleado->nombre,
                            'email_reportante' => $empleado->email,
                            'tipo_solicitante' => $empleado->tipo,
                            'area_id' => $area->id,
                            'fecha_incidencia' => $fecha->toDateString(),
                            'hora_incidencia' => $data['tipo'] === TipoIncidencia::Retardo ? $createdAt->format('H:i') : null,
                            'tipo_incidencia' => $data['tipo'],
                            'minutos_retardo' => $data['tipo'] === TipoIncidencia::Retardo ? 11 + (($empleadoIndex + $incidenciaIndex) % 20) : null,
                            'descripcion' => $this->descripcionPara($data['tipo'], $fecha),
                            'estado' => $estado,
                            'user_id' => null,
                            'revisado_por' => $revisor?->id,
                            'enviado_sindicato_at' => $estado === EstadoIncidencia::PendienteSindicato ? $createdAt->addDays(2) : null,
                            'motivo_rechazo' => $estado === EstadoIncidencia::Rechazada ? 'No cumple con la documentación requerida.' : null,
                        ]
                    );

                    $incidencia->forceFill([
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt->addDays($estado->esFinal() ? 4 : 1),
                    ])->saveQuietly();

                    $this->registrarHistorial($incidencia, $estado, $jefe, $capitalHumano, $subdirector, $sindicato, $createdAt);
                }
            }

            DB::table('folio_counters')->updateOrInsert(
                ['year' => $year],
                ['last_number' => $folio - 1, 'created_at' => now(), 'updated_at' => now()]
            );
        });
    }

    private function usuario(string $email, string $nombre, string $numeroEmpleado, RolUsuario $rol, string $password): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'nombre' => $nombre,
                'numero_empleado' => $numeroEmpleado,
                'password' => $password,
                'rol' => $rol,
                'activo' => true,
            ]
        );
    }

    /**
     * @return array<int, array{fecha: CarbonImmutable, tipo: TipoIncidencia}>
     */
    private function incidenciasAnuales(): array
    {
        $inicio = CarbonImmutable::now()->startOfYear()->addDays(4);
        $fin = CarbonImmutable::now()->endOfDay();
        $diasOperacion = max(1, (int) $inicio->diffInDays($fin));
        $paso = max(1, intdiv($diasOperacion, 30));
        $tipos = [
            TipoIncidencia::Retardo,
            TipoIncidencia::PermisoEconomico,
            TipoIncidencia::ComisionOficial,
            TipoIncidencia::SalidaAnticipada,
            TipoIncidencia::IncidenciaMedica,
            TipoIncidencia::BuenaConducta,
            TipoIncidencia::PermisoSindical,
            TipoIncidencia::ComisionOficial,
            TipoIncidencia::Retardo,
            TipoIncidencia::PermisoEconomico,
            TipoIncidencia::SalidaAnticipada,
            TipoIncidencia::IncidenciaMedica,
            TipoIncidencia::BuenaConducta,
            TipoIncidencia::ComisionOficial,
            TipoIncidencia::PermisoSindical,
            TipoIncidencia::PermisoEconomico,
            TipoIncidencia::Retardo,
            TipoIncidencia::SalidaAnticipada,
            TipoIncidencia::IncidenciaMedica,
            TipoIncidencia::ComisionOficial,
            TipoIncidencia::BuenaConducta,
            TipoIncidencia::PermisoEconomico,
            TipoIncidencia::PermisoSindical,
            TipoIncidencia::ComisionOficial,
            TipoIncidencia::Retardo,
            TipoIncidencia::SalidaAnticipada,
            TipoIncidencia::IncidenciaMedica,
            TipoIncidencia::PermisoEconomico,
            TipoIncidencia::BuenaConducta,
            TipoIncidencia::ComisionOficial,
        ];

        return collect($tipos)
            ->map(fn (TipoIncidencia $tipo, int $index) => [
                'fecha' => $inicio->addDays(min($diasOperacion, $index * $paso)),
                'tipo' => $tipo,
            ])
            ->all();
    }

    private function estadoPara(int $index): EstadoIncidencia
    {
        return match ($index % 10) {
            0 => EstadoIncidencia::PendienteJefe,
            1 => EstadoIncidencia::PendienteCapitalHumano,
            2 => EstadoIncidencia::PendienteSubdireccion,
            3 => EstadoIncidencia::PendienteSindicato,
            4 => EstadoIncidencia::Rechazada,
            default => EstadoIncidencia::Aprobada,
        };
    }

    private function revisorPara(EstadoIncidencia $estado, User $jefe, User $capitalHumano, User $subdirector, User $sindicato): ?User
    {
        return match ($estado) {
            EstadoIncidencia::PendienteJefe => null,
            EstadoIncidencia::PendienteCapitalHumano => $jefe,
            EstadoIncidencia::PendienteSubdireccion, EstadoIncidencia::PendienteSindicato => $capitalHumano,
            EstadoIncidencia::Rechazada => $jefe,
            EstadoIncidencia::Aprobada => $subdirector,
        };
    }

    private function descripcionPara(TipoIncidencia $tipo, CarbonImmutable $fecha): string
    {
        return $tipo->label().' registrada para simular operación histórica del '.$fecha->format('d/m/Y').'.';
    }

    private function registrarHistorial(Incidencia $incidencia, EstadoIncidencia $estado, User $jefe, User $capitalHumano, User $subdirector, User $sindicato, CarbonImmutable $createdAt): void
    {
        HistorialIncidencia::where('incidencia_id', $incidencia->id)->delete();

        $this->historial($incidencia, TipoAccionHistorial::Creada, null, 'Incidencia creada desde formulario público.', $createdAt);

        if ($estado === EstadoIncidencia::PendienteJefe) {
            return;
        }

        if ($estado === EstadoIncidencia::Rechazada) {
            $this->historial($incidencia, TipoAccionHistorial::Rechazada, $jefe, 'No cumple con la documentación requerida.', $createdAt->addDays(2));

            return;
        }

        $this->historial($incidencia, TipoAccionHistorial::Aprobada, $jefe, 'Aprobada por jefe inmediato.', $createdAt->addDay());

        if ($estado === EstadoIncidencia::PendienteCapitalHumano) {
            return;
        }

        if ($estado === EstadoIncidencia::PendienteSindicato) {
            $this->historial($incidencia, TipoAccionHistorial::Asignada, $capitalHumano, 'Enviada a Sindicato por Capital Humano.', $createdAt->addDays(2));

            return;
        }

        $this->historial($incidencia, TipoAccionHistorial::Aprobada, $capitalHumano, 'Aprobada por Capital Humano.', $createdAt->addDays(2));

        if ($estado === EstadoIncidencia::PendienteSubdireccion) {
            return;
        }

        $this->historial($incidencia, TipoAccionHistorial::Aprobada, $incidencia->revisado_por === $sindicato->id ? $sindicato : $subdirector, 'Resolución final aprobada.', $createdAt->addDays(4));
    }

    private function historial(Incidencia $incidencia, TipoAccionHistorial $tipo, ?User $user, string $comentario, CarbonImmutable $createdAt): void
    {
        $historial = HistorialIncidencia::create([
            'incidencia_id' => $incidencia->id,
            'user_id' => $user?->id,
            'tipo_accion' => $tipo,
            'comentario' => $comentario,
            'es_interno' => false,
            'metadata' => null,
        ]);

        $historial->forceFill(['created_at' => $createdAt])->saveQuietly();
    }
}
