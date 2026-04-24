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
        static $counter = 1;
        $nombre = 'Área '.$counter++;

        return [
            'nombre' => $nombre,
            'slug' => Str::slug($nombre),
            'descripcion' => null,
            'subdirector_id' => null,
            'activa' => true,
        ];
    }
}