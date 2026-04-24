<?php

namespace App\Models;

use App\Enums\EstadoIncidencia;
use App\Enums\TipoIncidencia;
use App\Enums\TipoSolicitante;
use Database\Factories\IncidenciaFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incidencia extends Model
{
    /** @use HasFactory<IncidenciaFactory> */
    use HasFactory;

    protected $fillable = [
        'folio',
        'numero_empleado',
        'reportante_nombre',
        'email_reportante',
        'tipo_solicitante',
        'area_id',
        'fecha_incidencia',
        'hora_incidencia',
        'tipo_incidencia',
        'minutos_retardo',
        'descripcion',
        'estado',
        'token_seguimiento',
        'user_id',
        'revisado_por',
        'motivo_rechazo',
        'resolucion',
    ];

    protected function casts(): array
    {
        return [
            'tipo_solicitante' => TipoSolicitante::class,
            'tipo_incidencia' => TipoIncidencia::class,
            'estado' => EstadoIncidencia::class,
            'fecha_incidencia' => 'date',
        ];
    }

    // ─── Relaciones ──────────────────────────────────────────────────────────

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function revisadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revisado_por');
    }

    public function historial(): HasMany
    {
        return $this->hasMany(HistorialIncidencia::class)->latest('created_at');
    }

    public function archivos(): HasMany
    {
        return $this->hasMany(ArchivoAdjunto::class)->latest('created_at');
    }

    public function notificaciones(): HasMany
    {
        return $this->hasMany(Notificacion::class)->latest('created_at');
    }

    // ─── Scopes ──────────────────────────────────────────────────────────────

    public function scopePorEstado(Builder $query, EstadoIncidencia $estado): Builder
    {
        return $query->where('estado', $estado);
    }

    public function scopeActivas(Builder $query): Builder
    {
        return $query->whereNotIn('estado', [
            EstadoIncidencia::Aprobada->value,
            EstadoIncidencia::Rechazada->value,
        ]);
    }

    public function scopePendientesJefe(Builder $query): Builder
    {
        return $query->where('estado', EstadoIncidencia::PendienteJefe);
    }

    public function scopePorArea(Builder $query, int $areaId): Builder
    {
        return $query->where('area_id', $areaId);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function tituloGenerado(): string
    {
        $tipo = $this->tipo_incidencia->label();

        if ($this->tipo_incidencia === TipoIncidencia::Retardo && $this->minutos_retardo) {
            $tipo .= " ({$this->minutos_retardo} min)";
        }

        return "{$tipo} — {$this->reportante_nombre}";
    }

    public function nombreReportante(): string
    {
        return $this->reportante_nombre ?? 'Sin nombre';
    }

    public function getTituloAttribute(): string
    {
        return $this->tituloGenerado();
    }

    public function emailDestino(): ?string
    {
        return $this->email_reportante ?: null;
    }
}
