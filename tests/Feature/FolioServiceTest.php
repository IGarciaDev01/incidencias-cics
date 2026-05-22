<?php

use App\Services\FolioService;
use Illuminate\Support\Facades\DB;

test('genera folios secuenciales por anio usando contador bloqueado', function () {
    $service = app(FolioService::class);
    $year = now()->year;

    expect($service->generar())->toBe("INC-{$year}-0001")
        ->and($service->generar())->toBe("INC-{$year}-0002");

    $this->assertDatabaseHas('folio_counters', [
        'year' => $year,
        'last_number' => 2,
    ]);
});

test('continua desde un contador existente', function () {
    $year = now()->year;

    DB::table('folio_counters')->insert([
        'year' => $year,
        'last_number' => 25,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(app(FolioService::class)->generar())->toBe("INC-{$year}-0026");
});
