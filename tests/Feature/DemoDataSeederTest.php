<?php

use App\Enums\EstadoIncidencia;
use App\Enums\TipoIncidencia;
use App\Models\Area;
use App\Models\Empleado;
use App\Models\HistorialIncidencia;
use App\Models\Incidencia;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;
use Illuminate\Support\Carbon;

test('el seeder demo crea datos operativos validos', function () {
    $this->seed(DemoDataSeeder::class);
    $this->seed(DemoDataSeeder::class);

    expect(User::where('rol', 'subdirector')->count())->toBe(1)
        ->and(User::where('rol', 'capital_humano')->count())->toBe(1)
        ->and(User::where('rol', 'sindicato')->count())->toBe(1)
        ->and(User::where('rol', 'jefe_inmediato')->count())->toBe(3)
        ->and(Area::count())->toBe(3)
        ->and(Empleado::count())->toBe(20)
        ->and(Incidencia::count())->toBe(600)
        ->and(HistorialIncidencia::count())->toBeGreaterThan(600);

    Area::with('jefes')->get()->each(function (Area $area): void {
        expect($area->jefe_id)->not->toBeNull()
            ->and($area->jefes)->toHaveCount(1)
            ->and($area->jefes->first()->rol->value)->toBe('jefe_inmediato');
    });

    Empleado::query()->each(function (Empleado $empleado): void {
        expect(Incidencia::where('numero_empleado', $empleado->numero_empleado)->count())->toBe(30);
    });

    expect(Incidencia::whereDate('fecha_incidencia', '>', now()->toDateString())->count())->toBe(0)
        ->and(Incidencia::where('tipo_incidencia', TipoIncidencia::Retardo->value)
            ->where(fn ($query) => $query->whereNull('minutos_retardo')->orWhere('minutos_retardo', '<', 11)->orWhere('minutos_retardo', '>', 30))
            ->count())->toBe(0)
        ->and(Incidencia::where('tipo_incidencia', '!=', TipoIncidencia::Retardo->value)->whereNotNull('minutos_retardo')->count())->toBe(0)
        ->and(Incidencia::where('estado', EstadoIncidencia::PendienteSindicato->value)->whereNull('enviado_sindicato_at')->count())->toBe(0);

    Incidencia::where('tipo_incidencia', TipoIncidencia::Retardo->value)
        ->where('estado', '!=', EstadoIncidencia::Rechazada->value)
        ->get()
        ->groupBy(fn (Incidencia $incidencia) => implode('-', [
            $incidencia->numero_empleado,
            $incidencia->fecha_incidencia->format('Y-m'),
            $incidencia->fecha_incidencia->day <= 15 ? 'primera' : 'segunda',
        ]))
        ->each(fn ($incidencias) => expect($incidencias->count())->toBeLessThanOrEqual(2));

    Incidencia::where('tipo_incidencia', TipoIncidencia::PermisoEconomico->value)
        ->where('estado', '!=', EstadoIncidencia::Rechazada->value)
        ->get()
        ->groupBy(fn (Incidencia $incidencia) => $incidencia->numero_empleado.'-'.$incidencia->fecha_incidencia->format('Y-m'))
        ->each(fn ($incidencias) => expect($incidencias->count())->toBeLessThanOrEqual(3));

    Incidencia::where('tipo_incidencia', TipoIncidencia::PermisoEconomico->value)
        ->where('estado', '!=', EstadoIncidencia::Rechazada->value)
        ->get()
        ->groupBy(fn (Incidencia $incidencia) => $incidencia->numero_empleado.'-'.$incidencia->fecha_incidencia->year)
        ->each(fn ($incidencias) => expect($incidencias->count())->toBeLessThanOrEqual(12));

    expect(Carbon::parse(Incidencia::min('fecha_incidencia'))->year)->toBe(now()->year);
});
