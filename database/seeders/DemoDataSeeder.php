<?php

namespace Database\Seeders;

use App\Enums\EstadoIncidencia;
use App\Enums\TipoIncidencia;
use App\Models\Area;
use App\Models\Empleado;
use App\Models\HistorialIncidencia;
use App\Models\Incidencia;
use App\Models\User;
use Faker\Factory as FakerFactory;
use Faker\Generator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private Generator $faker;

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

    public function run(): void
    {
        $this->faker = FakerFactory::create('es_MX');

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
            $emailBase = strtolower(str_replace(' ', '.', $nombre)).$i;

            Empleado::factory()->create([
                'numero_empleado' => $numeroEmpleado,
                'nombre' => $nombre,
                'email' => $emailBase.'@'.$this->faker->randomElement($correos),
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
        $fechaInicio = now()->subMonths(2);

        foreach ($empleados as $empleado) {
            $numIncidencias = $this->faker->randomElement([1, 2]);

            for ($j = 0; $j < $numIncidencias; $j++) {
                $fechaIncidencia = $this->faker->dateTimeBetween($fechaInicio, 'now');
                $fechaCreacion = $this->faker->dateTimeBetween($fechaIncidencia, (clone $fechaIncidencia)->modify('+2 days'));
                $estado = $this->faker->randomElement($estadosFinales);
                $area = $areas->random();

                $esRetardo = $this->faker->boolean(40);
                $minutosRetardo = $esRetardo ? $this->faker->numberBetween(5, 29) : null;

                $incidencia = Incidencia::factory()->create([
                    'folio' => 'INC-'.$fechaCreacion->format('Y').'-'.str_pad($folioBase++, 4, '0', STR_PAD_LEFT),
                    'numero_empleado' => $empleado->numero_empleado,
                    'reportante_nombre' => $empleado->nombre,
                    'email_reportante' => $empleado->email,
                    'tipo_solicitante' => $empleado->tipo,
                    'area_id' => $area->id,
                    'fecha_incidencia' => $fechaIncidencia->format('Y-m-d'),
                    'tipo_incidencia' => $esRetardo ? TipoIncidencia::Retardo : $this->faker->randomElement($tiposIncidencia),
                    'minutos_retardo' => $minutosRetardo,
                    'descripcion' => $this->faker->optional(0.8)->sentence(),
                    'estado' => $estado,
                    'token_seguimiento' => Str::random(32),
                    'user_id' => null,
                    'revisado_por' => $users->random()->id,
                    'motivo_rechazo' => $estado === EstadoIncidencia::Rechazada
                        ? $this->faker->randomElement($motivosRechazo)
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

        $daysJefe = $this->faker->numberBetween(1, 3);
        HistorialIncidencia::create([
            'incidencia_id' => $incidencia->id,
            'user_id' => $userJefe?->id,
            'tipo_accion' => 'aprobada',
            'comentario' => $this->faker->optional(0.6)->sentence(),
            'es_interno' => false,
            'created_at' => (clone $incidencia->created_at)->modify("+{$daysJefe} days"),
        ]);

        $daysCapital = $this->faker->numberBetween(4, 7);
        HistorialIncidencia::create([
            'incidencia_id' => $incidencia->id,
            'user_id' => $userCapitalHumano?->id,
            'tipo_accion' => 'aprobada',
            'comentario' => $this->faker->optional(0.6)->sentence(),
            'es_interno' => false,
            'created_at' => (clone $incidencia->created_at)->modify("+{$daysCapital} days"),
        ]);

        $daysSubdireccion = $this->faker->numberBetween(8, 15);
        HistorialIncidencia::create([
            'incidencia_id' => $incidencia->id,
            'user_id' => $userSubdireccion?->id,
            'tipo_accion' => $incidencia->estado === EstadoIncidencia::Aprobada ? 'aprobada' : 'rechazada',
            'comentario' => $incidencia->estado === EstadoIncidencia::Rechazada ? $incidencia->motivo_rechazo : $this->faker->optional(0.5)->sentence(),
            'es_interno' => false,
            'created_at' => (clone $incidencia->created_at)->modify("+{$daysSubdireccion} days"),
        ]);

        if ($this->faker->boolean(30)) {
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
                'comentario' => $this->faker->randomElement($comentarios),
                'es_interno' => $this->faker->boolean(30),
                'created_at' => (clone $incidencia->created_at)->modify('+'.$this->faker->numberBetween(2, 5).' days'),
            ]);
        }
    }
}