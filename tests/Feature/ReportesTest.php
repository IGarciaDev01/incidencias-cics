<?php

use App\Enums\EstadoIncidencia;
use App\Enums\TipoIncidencia;
use App\Enums\TipoSolicitante;
use App\Models\Area;
use App\Models\Incidencia;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('subdireccion puede consultar reportes por dia', function () {
    $subdirector = User::factory()->create(['rol' => 'subdirector', 'activo' => true]);
    $area = Area::factory()->create(['nombre' => 'Servicios Escolares']);

    Incidencia::factory()->create([
        'folio' => 'INC-DIA-001',
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-05-20',
        'estado' => EstadoIncidencia::Aprobada,
        'tipo_incidencia' => TipoIncidencia::PermisoEconomico,
        'tipo_solicitante' => TipoSolicitante::Docente,
    ]);

    Incidencia::factory()->create([
        'folio' => 'INC-DIA-002',
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-05-21',
        'estado' => EstadoIncidencia::Rechazada,
        'tipo_incidencia' => TipoIncidencia::Retardo,
        'tipo_solicitante' => TipoSolicitante::Administrativo,
    ]);

    $this->actingAs($subdirector)
        ->get(route('panel.subdireccion.reportes.index', ['fecha' => '2026-05-20']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Panel/Subdireccion/Reportes/Index')
            ->where('filtros.fecha', '2026-05-20')
            ->where('filtros.desde', '2026-05-20')
            ->where('filtros.hasta', '2026-05-20')
            ->where('estadisticas.total', 1)
            ->where('estadisticas.aprobadas', 1)
            ->where('porDia.2026-05-20', 1)
            ->has('opciones.estados')
            ->has('opciones.tiposIncidencia')
            ->has('opciones.areas')
            ->etc()
        );
});

test('capital humano puede filtrar reportes por rango estado tipo y area', function () {
    $capitalHumano = User::factory()->create(['rol' => 'capital_humano', 'activo' => true]);
    $areaIncluida = Area::factory()->create(['nombre' => 'Capital Humano']);
    $areaExcluida = Area::factory()->create(['nombre' => 'Otra Área']);

    Incidencia::factory()->create([
        'area_id' => $areaIncluida->id,
        'fecha_incidencia' => '2026-05-10',
        'estado' => EstadoIncidencia::PendienteJefe,
        'tipo_incidencia' => TipoIncidencia::ComisionOficial,
        'tipo_solicitante' => TipoSolicitante::Administrativo,
    ]);

    Incidencia::factory()->create([
        'area_id' => $areaIncluida->id,
        'fecha_incidencia' => '2026-05-11',
        'estado' => EstadoIncidencia::Aprobada,
        'tipo_incidencia' => TipoIncidencia::ComisionOficial,
        'tipo_solicitante' => TipoSolicitante::Administrativo,
    ]);

    Incidencia::factory()->create([
        'area_id' => $areaExcluida->id,
        'fecha_incidencia' => '2026-05-10',
        'estado' => EstadoIncidencia::PendienteJefe,
        'tipo_incidencia' => TipoIncidencia::ComisionOficial,
        'tipo_solicitante' => TipoSolicitante::Administrativo,
    ]);

    $this->actingAs($capitalHumano)
        ->get(route('panel.capital_humano.reportes.index', [
            'desde' => '2026-05-01',
            'hasta' => '2026-05-31',
            'estado' => EstadoIncidencia::PendienteJefe->value,
            'tipo_incidencia' => TipoIncidencia::ComisionOficial->value,
            'tipo_solicitante' => TipoSolicitante::Administrativo->value,
            'area_id' => $areaIncluida->id,
        ]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Panel/CapitalHumano/Reportes/Index')
            ->where('estadisticas.total', 1)
            ->where('porArea.Capital Humano', 1)
            ->where('filtros.area_id', (string) $areaIncluida->id)
            ->where('filtros.estado', EstadoIncidencia::PendienteJefe->value)
            ->etc()
        );
});

test('exportacion csv respeta filtros del reporte', function () {
    $subdirector = User::factory()->create(['rol' => 'subdirector', 'activo' => true]);
    $area = Area::factory()->create(['nombre' => 'Área Exportable']);

    Incidencia::factory()->create([
        'folio' => 'INC-CSV-001',
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-05-15',
        'estado' => EstadoIncidencia::Aprobada,
        'tipo_incidencia' => TipoIncidencia::PermisoSindical,
    ]);

    Incidencia::factory()->create([
        'folio' => 'INC-CSV-002',
        'area_id' => $area->id,
        'fecha_incidencia' => '2026-05-16',
        'estado' => EstadoIncidencia::Rechazada,
        'tipo_incidencia' => TipoIncidencia::PermisoSindical,
    ]);

    $response = $this->actingAs($subdirector)
        ->get(route('panel.subdireccion.reportes.exportar', [
            'fecha' => '2026-05-15',
            'estado' => EstadoIncidencia::Aprobada->value,
        ]));

    $response->assertOk();

    expect($response->headers->get('content-type'))->toContain('text/csv')
        ->and($response->headers->get('content-disposition'))->toContain('reporte-incidencias-2026-05-15.csv')
        ->and($response->getContent())->toContain('INC-CSV-001')
        ->and($response->getContent())->not->toContain('INC-CSV-002');
});
