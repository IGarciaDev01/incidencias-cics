<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('folio_counters', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('year')->unique();
            $table->unsignedInteger('last_number')->default(0);
            $table->timestamps();
        });

        $folios = DB::table('incidencias')->pluck('folio');
        $counters = [];

        foreach ($folios as $folio) {
            if (! preg_match('/^INC-(\d{4})-(\d+)$/', (string) $folio, $matches)) {
                continue;
            }

            $year = (int) $matches[1];
            $number = (int) $matches[2];
            $counters[$year] = max($counters[$year] ?? 0, $number);
        }

        foreach ($counters as $year => $lastNumber) {
            DB::table('folio_counters')->insert([
                'year' => $year,
                'last_number' => $lastNumber,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('folio_counters');
    }
};
