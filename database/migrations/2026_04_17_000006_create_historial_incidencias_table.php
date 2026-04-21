<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_incidencias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incidencia_id')->constrained('incidencias')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('tipo_accion', [
                'creada',
                'aprobada',
                'rechazada',
                'asignada',
                'reasignada',
                'en_proceso',
                'resuelta',
                'cerrada',
                'reabierta',
                'comentario',
                'solicitud_info',
                'archivo_adjunto',
            ]);
            $table->text('comentario')->nullable();
            $table->boolean('es_interno')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_incidencias');
    }
};
