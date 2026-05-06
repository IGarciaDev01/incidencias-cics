<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
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