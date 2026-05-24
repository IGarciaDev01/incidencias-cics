<?php

namespace App\Services;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use App\Models\Incidencia;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AuditLogService
{
    /**
     * @param  array<string, mixed>|null  $metadata
     */
    public function record(
        AuditAction $action,
        string $description,
        ?Model $subject = null,
        ?array $metadata = null,
        ?User $actor = null,
        ?string $actorType = null,
        ?string $actorIdentifier = null,
        ?Incidencia $incidencia = null,
    ): AuditLog {
        $request = request();
        $request = $request instanceof Request ? $request : null;
        $actor ??= $request?->user();

        if ($subject instanceof Incidencia) {
            $incidencia = $subject;
        }

        return AuditLog::create([
            'actor_user_id' => $actor?->id,
            'actor_type' => $actorType ?? ($actor ? 'user' : 'system'),
            'actor_identifier' => $actorIdentifier,
            'action' => $action,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey() ? (string) $subject->getKey() : null,
            'incidencia_id' => $incidencia?->id,
            'description' => $description,
            'metadata' => $this->sanitizeMetadata($metadata),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'route_name' => $request?->route()?->getName(),
            'method' => $request?->method(),
        ]);
    }

    /**
     * @param  array<string, mixed>|null  $metadata
     * @return array<string, mixed>|null
     */
    private function sanitizeMetadata(?array $metadata): ?array
    {
        if ($metadata === null) {
            return null;
        }

        $metadata = Arr::except($metadata, [
            'password',
            'password_confirmation',
            'current_password',
            'token',
            'remember_token',
            'two_factor_secret',
            'two_factor_recovery_codes',
        ]);

        return array_map(function (mixed $value): mixed {
            return is_array($value) ? $this->sanitizeMetadata($value) : $value;
        }, $metadata);
    }
}
