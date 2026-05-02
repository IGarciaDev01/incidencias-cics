<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private const AREAS = [
        'Actividades Culturales',
        'Actividades Deportivas',
        'Becas',
        'Biblioteca',
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
    // Seeder principal
    // -------------------------------------------------------------------------

    public function run(): void
    {
        $this->crearAreas();
        $this->crearUsuarios();
    }

    private function crearAreas(): void
    {
        $jefesNombres = [
            'Dr. Carlos Ramírez', 'Dra. Laura Mendoza', 'Mtro. Jorge Castillo',
            'Dra. Patricia Herrera', 'Lic. Ana Flores', 'Ing. Mario Torres',
            'Lic. Sandra López', 'Mtro. Pedro Sánchez', 'Dra. Claudia Reyes',
            'Mtro. Roberto Vega', 'Lic. Elena Castro', 'Ing. Manuel Ortega',
            'Lic. Rosa María Mendoza', 'Dr. José Luis Ortiz', 'Lic. Adriana Domínguez',
            'Dr. Fernando Morales', 'Dra. Isabel González', 'Mtro. Roberto Díaz',
            'Lic. Lucía Cruz', 'Ing. Antonio Jiménez', 'Lic. Margarita Vargas',
            'Mtro. Francisco Reyes', 'Lic. Gabriela Navarro', 'Mtra. Sofía Herrera',
            'Dr. Ricardo Molina',
        ];

        $jefesACrear = [];
        foreach (self::AREAS as $i => $nombre) {
            $slug = Str::slug($nombre);
            $existingArea = Area::where('slug', $slug)->first();

            if ($existingArea) {
                if (! $existingArea->jefe_id) {
                    $jefe = User::firstOrCreate(
                        ['email' => 'jefe.'.$slug.'@test.com'],
                        [
                            'nombre' => $jefesNombres[$i] ?? 'Jefe del área',
                            'password' => bcrypt('password'),
                            'rol' => 'jefe_inmediato',
                            'area_id' => null,
                            'activo' => true,
                        ]
                    );
                    $existingArea->update(['jefe_id' => $jefe->id]);
                }
            } else {
                $jefesACrear[] = ['nombre' => $jefesNombres[$i] ?? 'Jefe del área', 'slug' => $slug, 'areaNombre' => $nombre, 'index' => $i];
            }
        }

        foreach ($jefesACrear as $data) {
            $jefe = User::firstOrCreate(
                ['email' => 'jefe.'.$data['slug'].'@test.com'],
                [
                    'nombre' => $data['nombre'],
                    'password' => bcrypt('password'),
                    'rol' => 'jefe_inmediato',
                    'area_id' => null,
                    'activo' => true,
                ]
            );

            Area::create([
                'nombre' => $data['areaNombre'],
                'activa' => true,
                'slug' => $data['slug'],
                'jefe_id' => $jefe->id,
            ]);
        }
    }

    private function crearUsuarios(): void
    {
        User::firstOrCreate(
            ['email' => 'capital_humano@test.com'],
            [
                'nombre' => 'María López',
                'password' => bcrypt('password'),
                'rol' => 'capital_humano',
                'activo' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'subdireccion@test.com'],
            [
                'nombre' => 'Alan',
                'password' => bcrypt('password'),
                'rol' => 'subdirector',
                'activo' => true,
            ]
        );
    }
}
