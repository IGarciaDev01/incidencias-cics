<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1 empleado con rol de capital humano
        $capitalHumano = User::firstOrCreate(
            ['email' => 'capital.humano@test.com'],
            [
                'nombre' => 'Lic. Capital Humano',
                'numero_empleado' => '000002',
                'password' => bcrypt('11223344'),
                'rol' => 'capital_humano',
                'activo' => true,
            ]
        );

        // 5 empleados con rol de jefe de área
        $nombresJefes = [
            'Jefe de Departamento de Psicología',
            'Jefe de Departamento de Optometría',
            'Jefe de Departamento de Odontología',
            'Jefe de Gestión Escolar',
            'Jefe de UDI',
            'Jefe de Servicio Social',
        ];

        for ($i = 0; $i < 6; $i++) {
            $numeroEmpleado = str_pad((string) (3 + $i), 6, '0', STR_PAD_LEFT);
            $jefe = User::firstOrCreate(
                ['email' => 'jefe'.($i + 1).'@test.com'],
                [
                    'nombre' => $nombresJefes[$i],
                    'numero_empleado' => $numeroEmpleado,
                    'password' => bcrypt('11223344'),
                    'rol' => 'jefe_inmediato',
                    'activo' => true,
                ]
            );
        }

        // 10 áreas sin jefe asignado
        $nombresAreas = [
            'Departamento de Psicología',
            'Departamento de Optometría',
            'Departamento de Odontología',
            'Gestión Escolar',
            'UDI',
            'Servicio Social',
            'Extensión y Apoyos',
        ];

        for ($i = 0; $i < 7; $i++) {
            Area::firstOrCreate(
                ['slug' => 'area-sin-jefe-'.($i + 1)],
                [
                    'nombre' => $nombresAreas[$i],
                    'descripcion' => 'Área sin jefe asignado',
                    'activa' => true,
                ]
            );
        }

        // Mantener el subdirector original
        User::firstOrCreate(
            ['email' => 'subdirector@test.com'],
            [
                'nombre' => 'Lic. Nombre Subdirector',
                'numero_empleado' => '000001',
                'password' => bcrypt('11223344'),
                'rol' => 'subdirector',
                'activo' => true,
            ]
        );
    }
}
