<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class FolioService
{
    public function generar(): string
    {
        return DB::transaction(function () {
            $year = now()->year;

            DB::table('folio_counters')->insertOrIgnore([
                'year' => $year,
                'last_number' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $counter = DB::table('folio_counters')
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            $numero = ((int) $counter->last_number) + 1;

            DB::table('folio_counters')
                ->where('year', $year)
                ->update([
                    'last_number' => $numero,
                    'updated_at' => now(),
                ]);

            return sprintf('INC-%d-%04d', $year, $numero);
        }, attempts: 5);
    }
}
