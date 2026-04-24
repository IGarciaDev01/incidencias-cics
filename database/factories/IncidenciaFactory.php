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
        $tipoIncidencia = fake()->randomElement(TipoIncidencia::cases());
        $minutos = $tipoIncidencia === TipoIncidencia::Retardo ? fake()->numberBetween(5, 120) : null;

        return [
            'folio' => 'INC-'.now()->year.'-'.str_pad(fake()->unique()->numberBetween(1, 99999), 4, '0', STR_PAD_LEFT),
            'numero_empleado' => (string) fake()->numerify('#####'),
            'reportante_nombre' => fake()->name(),
            'email_reportante' => fake()->safeEmail(),
            'tipo_solicitante' => fake()->randomElement(TipoSolicitante::cases()),
            'area_id' => Area::factory(),
            'fecha_incidencia' => fake()->dateTimeBetween('-3 months', 'now'),
            'tipo_incidencia' => $tipoIncidencia,
            'minutos_retardo' => $minutos,
            'descripcion' => fake()->optional(0.7)->sentence(),
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
            'motivo_rechazo' => fake()->randomElement([
                'No cumple con los requisitos establecidos.',
                'Falta documentación de soporte.',
                'Ya se acercó el límite de permisos económicos del año.',
                'El área no cuenta con presupuesto disponible.',
                'El incidente ya prescribió.',
            ]),
        ]);
    }
}
