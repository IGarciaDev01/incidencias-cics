<?php

namespace Database\Factories;

use App\Models\Empleado;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Empleado>
 */
class EmpleadoFactory extends Factory
{
    protected $model = Empleado::class;

    public function definition(): array
    {
        static $counter = 1000;
        $counter++;

        return [
            'numero_empleado' => (string) str_pad($counter, 5, '0', STR_PAD_LEFT),
            'nombre' => 'Empleado '.$counter,
            'email' => 'empleado'.$counter.'@test.com',
            'tipo' => 'administrativo',
        ];
    }
}