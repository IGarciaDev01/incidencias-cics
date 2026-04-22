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
        return [
            'numero_empleado' => (string) $this->faker->numerify('#####'),
            'nombre' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'tipo' => $this->faker->randomElement(['docente', 'administrativo', 'paae']),
        ];
    }
}
