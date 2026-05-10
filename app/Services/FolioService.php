<?php

namespace App\Services;

use App\Models\Incidencia;
use Illuminate\Support\Facades\DB;

class FolioService
{
    public function generar(): string
    {
        return DB::transaction(function () {
            $year = now()->year;

            Incidencia::whereYear('created_at', $year)
                ->lockForUpdate()
                ->max('id');

            $numero = Incidencia::whereYear('created_at', $year)->count() + 1;

            return sprintf('INC-%d-%04d', $year, $numero);
        });
    }
}
