<?php

namespace Database\Factories;

use App\Enums\EstadoIncidencia;
use App\Enums\TipoIncidencia;
use App\Enums\TipoSolicitante;
use App\Models\Area;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Incidencia>
 */
class IncidenciaFactory extends Factory
{
    protected $model = Incidencia::class;

    public function definition(): array
    {
        static $counter = 1;
        $counter++;

        return [
            'folio' => 'INC-'.now()->year.'-'.str_pad($counter, 4, '0', STR_PAD_LEFT),
            'numero_empleado' => str_pad($counter, 5, '0', STR_PAD_LEFT),
            'reportante_nombre' => 'Empleado '.$counter,
            'email_reportante' => 'empleado'.$counter.'@test.com',
            'tipo_solicitante' => TipoSolicitante::cases()[0],
            'area_id' => Area::factory(),
            'fecha_incidencia' => now()->subDays(rand(1, 60))->format('Y-m-d'),
            'tipo_incidencia' => TipoIncidencia::PermisoEconomico,
            'minutos_retardo' => null,
            'descripcion' => null,
            'estado' => EstadoIncidencia::PendienteJefe,
            'token_seguimiento' => Str::random(32),
            'user_id' => null,
            'revisado_por' => null,
            'motivo_rechazo' => null,
        ];
    }

    public function estadoJefe(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => EstadoIncidencia::PendienteJefe,
        ]);
    }

    public function estadoCapitalHumano(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => EstadoIncidencia::PendienteCapitalHumano,
        ]);
    }

    public function estadoSubdireccion(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => EstadoIncidencia::PendienteSubdireccion,
        ]);
    }

    public function aprobada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => EstadoIncidencia::Aprobada,
            'revisado_por' => User::factory(),
        ]);
    }

    public function rechazada(): static
    {
        return $this->state(fn (array $attributes) => [
            'estado' => EstadoIncidencia::Rechazada,
            'revisado_por' => User::factory(),
            'motivo_rechazo' => 'No cumple con los requisitos establecidos.',
        ]);
    }
}