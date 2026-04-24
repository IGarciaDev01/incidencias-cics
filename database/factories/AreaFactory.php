<?php

namespace Database\Factories;

use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Area>
 */
class AreaFactory extends Factory
{
    protected $model = Area::class;

    public function definition(): array
    {
        return [
            'nombre' => fake()->unique()->department(),
            'slug' => fn (array $attributes) => Str::slug($attributes['nombre'] ?? fake()->words(2, true)),
            'descripcion' => fake()->optional()->sentence(),
            'subdirector_id' => null,
            'activa' => true,
        ];
    }
}
