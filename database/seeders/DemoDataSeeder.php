<?php

namespace Database\Seeders;

use App\Enums\EstadoIncidencia;
use App\Enums\TipoIncidencia;
use App\Models\Area;
use App\Models\Empleado;
use App\Models\HistorialIncidencia;
use App\Models\Incidencia;
use App\Models\User;
use DateTime;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private const AREAS = [
        'Actividades Culturales',
        'Actividades Deportivas',
        'Becas',
        'Biblioteca',
        'Capital Humano',
        'Convenios',
        'Coordinación de Enlace y Gestión Técnica',
        'Defensoría de los Derechos Politécnicos',
        'Departamento de Formación Básica e Interdisciplinaria',
        'Departamento de Odontología',
        'Departamento de Optometría',
        'Departamento de Psicología',
        'Evaluación y Seguimiento',
        'Gestión Escolar',
        'Innovación',
        'Movilidad Académica',
        'Orientación Juvenil',
        'Perspectiva de Género',
        'Protección Civil',
        'Sección de Estudios de Posgrado e Investigación',
        'Seguimiento a Egresados',
        'Servicio Social',
        'Titulación',
        'Tutorías',
        'Unidad de Informática',
        'Unidad de Tecnología y Campus Virtual',
        'UPIS',
    ];

    // -------------------------------------------------------------------------
    // Helpers nativos (reemplazo de Faker)
    // -------------------------------------------------------------------------

    private function randomElement(array $array): mixed
    {
        return $array[array_rand($array)];
    }

    private function numberBetween(int $min, int $max): int
    {
        return rand($min, $max);
    }

    private function boolean(int $percentChance = 50): bool
    {
        return rand(1, 100) <= $percentChance;
    }

    private function optional(float $probability, mixed $value): mixed
    {
        return rand(1, 100) <= ($probability * 100) ? $value : null;
    }

    private function sentence(): string
    {
        $oraciones = [
            'El empleado llegó tarde por tráfico intenso en la zona.',
            'Se solicitó permiso por cita médica urgente.',
            'La comisión fue asignada por el área de coordinación.',
            'El retardo se debió a un imprevisto de transporte público.',
            'Se adjunta documentación de soporte para la solicitud.',
            'El evento fue por causas de fuerza mayor.',
            'La salida anticipada fue autorizada verbalmente.',
            'Se presenta justificante médico como respaldo.',
            'La incidencia fue notificada al jefe inmediato.',
            'Se solicitó permiso con anticipación suficiente.',
            'El empleado presentó comprobante de asistencia a evento oficial.',
            'La situación fue comunicada oportunamente al área correspondiente.',
        ];

        return $this->randomElement($oraciones);
    }

    private function randomDateBetween(DateTime $start, DateTime $end): DateTime
    {
        $ts = rand($start->getTimestamp(), $end->getTimestamp());
        return (new DateTime())->setTimestamp($ts);
    }

    // -------------------------------------------------------------------------
    // Seeder principal
    // -------------------------------------------------------------------------

    public function run(): void
    {
        $this->crearAreas();
        $this->crearUsuarios();
        $this->crearEmpleados();
        $this->crearIncidencias();
    }

    private function crearAreas(): void
    {
        foreach (self::AREAS as $nombre) {
            Area::create([
                'nombre' => $nombre,
                'activa' => true,
                'slug' => Str::slug($nombre),
            ]);
        }
    }

    private function crearUsuarios(): void
    {
        User::create([
            'nombre' => 'Admin Sistema',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'rol' => 'admin',
            'activo' => true,
        ]);

        $jefesNombres = [
            'Dr. Carlos Ramírez', 'Dra. Laura Mendoza', 'Mtro. Jorge Castillo',
            'Dra. Patricia Herrera', 'Lic. Ana Flores', 'Ing. Mario Torres',
            'Lic. Sandra López', 'Mtro. Pedro Sánchez', 'Dra. Claudia Reyes',
            'Mtro. Roberto Vega', 'Lic. Elena Castro', 'Ing. Manuel Ortega',
            'Lic. Rosa María Mendoza', 'Dr. José Luis Ortiz', 'Lic. Adriana Domínguez',
            'Dr. Fernando Morales', 'Dra. Isabel González', 'Mtro. Roberto Díaz',
            'Lic. Lucía Cruz', 'Ing. Antonio Jiménez', 'Lic. Margarita Vargas',
            'Mtro. Francisco Reyes', 'Dra. Elena Castro', 'Lic. Manuel Ortega',
            'Mtra. Rosa María Mendoza', 'Dr. José Luis Ortiz',
        ];

        $areas = Area::all();
        foreach ($areas as $i => $area) {
            User::create([
                'nombre' => $jefesNombres[$i] ?? 'Jefe del área',
                'email' => 'jefe.'.Str::slug($area->nombre, '_').'@test.com',
                'password' => bcrypt('password'),
                'rol' => 'jefe_inmediato',
                'area_id' => $area->id,
                'activo' => true,
            ]);
        }

        User::create([
            'nombre' => 'María López',
            'email' => 'capital_humano@test.com',
            'password' => bcrypt('password'),
            'rol' => 'capital_humano',
            'activo' => true,
        ]);

        User::create([
            'nombre' => 'Alan',
            'email' => 'subdireccion@test.com',
            'password' => bcrypt('password'),
            'rol' => 'subdirector',
            'activo' => true,
        ]);
    }

    private function crearEmpleados(): void
    {
        $nombres = [
            'Ana García', 'Pedro Martínez', 'Laura Hernández', 'Jorge Sánchez',
            'Sofia Ramírez', 'Miguel Torres', 'Carmen López', 'Diego Ruiz',
            'Patricia Flores', 'Fernando Morales', 'Isabel González', 'Roberto Díaz',
            'Lucía Cruz', 'Antonio Jiménez', 'Margarita Vargas', 'Francisco Reyes',
            'Elena Castro', 'Manuel Ortega', 'Rosa María Mendoza', 'José Luis Ortiz',
            'Adriana Domínguez', 'Ricardo Fuentes', 'Claudia Vega', 'Gustavo Rojas',
            'Sandra Aguilar', 'Alfonso Castro', 'Verónica Silva', 'Mauricio Delgado',
            'Diana Herrera', 'Alejandro Ríos', 'Gabriela Salas', 'Ernesto Paredes',
        ];

        $tipos = ['docente', 'administrativo', 'paae'];
        $correos = ['gmail.com', 'outlook.com', 'yahoo.com', 'hotmail.com'];

        foreach ($nombres as $i => $nombre) {
            $numeroEmpleado = str_pad(1000 + $i, 5, '0', STR_PAD_LEFT);
            $emailBase = strtolower(str_replace(
                [' ', 'á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ'],
                ['.', 'a', 'e', 'i', 'o', 'u', 'u', 'n'],
                $nombre
            )).$i;

            Empleado::factory()->create([
                'numero_empleado' => $numeroEmpleado,
                'nombre' => $nombre,
                'email' => $emailBase.'@'.$this->randomElement($correos),
                'tipo' => $tipos[$i % 3],
            ]);
        }
    }

    private function crearIncidencias(): void
    {
        $areas = Area::all();
        $empleados = Empleado::all();
        $users = User::whereIn('rol', ['jefe_inmediato', 'capital_humano', 'subdireccion'])->get();

        $estadosFinales = [
            EstadoIncidencia::Aprobada,
            EstadoIncidencia::Rechazada,
        ];

        $tiposIncidencia = [
            TipoIncidencia::Retardo,
            TipoIncidencia::PermisoEconomico,
            TipoIncidencia::ComisionOficial,
            TipoIncidencia::SalidaAnticipada,
        ];

        $motivosRechazo = [
            'No cumple con los requisitos establecidos.',
            'Falta documentación de soporte.',
            'Ya se alcanzó el límite de permisos económicos del mes.',
            'El área no cuenta con presupuesto disponible.',
            'Se alcanzó el límite de retardos quincenales.',
        ];

        $folioBase = 1;
        $fechaInicio = new DateTime('-2 months');
        $fechaHoy = new DateTime();

        foreach ($empleados as $empleado) {
            $numIncidencias = $this->randomElement([1, 2]);

            for ($j = 0; $j < $numIncidencias; $j++) {
                $fechaIncidencia = $this->randomDateBetween($fechaInicio, $fechaHoy);
                $fechaCreacionMax = (clone $fechaIncidencia)->modify('+2 days');
                $fechaCreacion = $this->randomDateBetween($fechaIncidencia, $fechaCreacionMax);

                $estado = $this->randomElement($estadosFinales);
                $area = $areas->random();

                $esRetardo = $this->boolean(40);
                $minutosRetardo = $esRetardo ? $this->numberBetween(5, 29) : null;

                $incidencia = Incidencia::factory()->create([
                    'folio' => 'INC-'.$fechaCreacion->format('Y').'-'.str_pad($folioBase++, 4, '0', STR_PAD_LEFT),
                    'numero_empleado' => $empleado->numero_empleado,
                    'reportante_nombre' => $empleado->nombre,
                    'email_reportante' => $empleado->email,
                    'tipo_solicitante' => $empleado->tipo,
                    'area_id' => $area->id,
                    'fecha_incidencia' => $fechaIncidencia->format('Y-m-d'),
                    'tipo_incidencia' => $esRetardo ? TipoIncidencia::Retardo : $this->randomElement($tiposIncidencia),
                    'minutos_retardo' => $minutosRetardo,
                    'descripcion' => $this->optional(0.8, $this->sentence()),
                    'estado' => $estado,
                    'token_seguimiento' => Str::random(32),
                    'user_id' => null,
                    'revisado_por' => $users->random()->id,
                    'motivo_rechazo' => $estado === EstadoIncidencia::Rechazada
                        ? $this->randomElement($motivosRechazo)
                        : null,
                    'created_at' => $fechaCreacion,
                    'updated_at' => $fechaCreacion,
                ]);

                $this->crearHistorialParaIncidencia($incidencia);
            }
        }
    }

    private function crearHistorialParaIncidencia(Incidencia $incidencia): void
    {
        $userJefe = User::where('rol', 'jefe_inmediato')->first();
        $userCapitalHumano = User::where('rol', 'capital_humano')->first();
        $userSubdireccion = User::where('rol', 'subdirector')->first();

        HistorialIncidencia::create([
            'incidencia_id' => $incidencia->id,
            'user_id' => null,
            'tipo_accion' => 'creada',
            'comentario' => null,
            'es_interno' => false,
            'created_at' => $incidencia->created_at,
        ]);

        $daysJefe = $this->numberBetween(1, 3);
        HistorialIncidencia::create([
            'incidencia_id' => $incidencia->id,
            'user_id' => $userJefe?->id,
            'tipo_accion' => 'aprobada',
            'comentario' => $this->optional(0.6, $this->sentence()),
            'es_interno' => false,
            'created_at' => (clone $incidencia->created_at)->modify("+{$daysJefe} days"),
        ]);

        $daysCapital = $this->numberBetween(4, 7);
        HistorialIncidencia::create([
            'incidencia_id' => $incidencia->id,
            'user_id' => $userCapitalHumano?->id,
            'tipo_accion' => 'aprobada',
            'comentario' => $this->optional(0.6, $this->sentence()),
            'es_interno' => false,
            'created_at' => (clone $incidencia->created_at)->modify("+{$daysCapital} days"),
        ]);

        $daysSubdireccion = $this->numberBetween(8, 15);
        HistorialIncidencia::create([
            'incidencia_id' => $incidencia->id,
            'user_id' => $userSubdireccion?->id,
            'tipo_accion' => $incidencia->estado === EstadoIncidencia::Aprobada ? 'aprobada' : 'rechazada',
            'comentario' => $incidencia->estado === EstadoIncidencia::Rechazada
                ? $incidencia->motivo_rechazo
                : $this->optional(0.5, $this->sentence()),
            'es_interno' => false,
            'created_at' => (clone $incidencia->created_at)->modify("+{$daysSubdireccion} days"),
        ]);

        if ($this->boolean(30)) {
            $comentarios = [
                'Solicito amablemente se revise a la brevedad.',
                'Ya son varios los retrasos este mes.',
                'El evento fue por causas de fuerza mayor.',
                'Adjunto comprobante de la cita médica.',
                'Esperando aprobación de la brevedad.',
            ];
            HistorialIncidencia::create([
                'incidencia_id' => $incidencia->id,
                'user_id' => $userJefe?->id,
                'tipo_accion' => 'comentario',
                'comentario' => $this->randomElement($comentarios),
                'es_interno' => $this->boolean(30),
                'created_at' => (clone $incidencia->created_at)->modify('+'.$this->numberBetween(2, 5).' days'),
            ]);
        }
    }
}