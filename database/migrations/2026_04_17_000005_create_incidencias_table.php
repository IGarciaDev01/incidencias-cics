<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('incidencias', function (Blueprint $table) {
            $table->id();
            $table->string('folio', 20)->unique();
            $table->string('numero_empleado', 20);
            $table->string('reportante_nombre', 150);
            $table->string('email_reportante', 150)->nullable();
            $table->enum('tipo_solicitante', ['docente', 'administrativo']);
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->date('fecha_incidencia');
            $table->enum('tipo_incidencia', [
                'retardo',
                'permiso_economico',
                'comision_oficial',
                'salida_anticipada',
            ]);
            $table->unsignedSmallInteger('minutos_retardo')->nullable();
            $table->text('descripcion')->nullable();
            $table->enum('estado', [
                'pendiente_jefe',
                'pendiente_capital_humano',
                'pendiente_subdireccion',
                'aprobada',
                'rechazada',
            ])->default('pendiente_jefe');
            $table->text('motivo_rechazo')->nullable();
            $table->string('token_seguimiento', 64)->unique();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('revisado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['estado', 'numero_empleado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incidencias');
    }
};
