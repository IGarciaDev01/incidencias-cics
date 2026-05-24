<?php

namespace Database\Factories;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'actor_user_id' => User::factory(),
            'actor_type' => 'user',
            'actor_identifier' => null,
            'action' => AuditAction::Login,
            'subject_type' => User::class,
            'subject_id' => null,
            'incidencia_id' => null,
            'description' => 'Evento de auditoría de prueba',
            'metadata' => null,
            'ip_address' => '127.0.0.1',
            'user_agent' => 'Testing',
            'route_name' => null,
            'method' => 'GET',
        ];
    }
}
