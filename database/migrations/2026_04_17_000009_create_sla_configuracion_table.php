<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_configuracion', function (Blueprint $table) {
            $table->id();
            $table->enum('prioridad', ['alta', 'media', 'baja'])->unique();
            $table->unsignedSmallInteger('horas_primera_respuesta');
            $table->unsignedSmallInteger('horas_resolucion');
            $table->boolean('activa')->default(true);
            $table->timestamp('updated_at')->nullable();
        });

        DB::table('sla_configuracion')->insert([
            [
                'prioridad'               => 'alta',
                'horas_primera_respuesta' => 24,
                'horas_resolucion'        => 72,
                'activa'                  => true,
                'updated_at'              => now(),
            ],
            [
                'prioridad'               => 'media',
                'horas_primera_respuesta' => 72,
                'horas_resolucion'        => 168,
                'activa'                  => true,
                'updated_at'              => now(),
            ],
            [
                'prioridad'               => 'baja',
                'horas_primera_respuesta' => 168,
                'horas_resolucion'        => 336,
                'activa'                  => true,
                'updated_at'              => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_configuracion');
    }
};
