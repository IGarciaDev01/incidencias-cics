<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::create('notificaciones_new', function (Blueprint $table) {
                $table->id();
                $table->foreignId('incidencia_id')->constrained('incidencias')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('destinatario_email', 150);
                $table->text('tipo')->nullable();
                $table->string('asunto', 255);
                $table->timestamp('enviada_at')->nullable();
                $table->timestamp('leida_at')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });

            DB::unprepared('INSERT INTO notificaciones_new SELECT * FROM notificaciones');
            Schema::drop('notificaciones');
            Schema::rename('notificaciones_new', 'notificaciones');
        } else {
            Schema::table('notificaciones', function (Blueprint $table) {
                DB::statement("ALTER TABLE notificaciones MODIFY COLUMN tipo ENUM('confirmacion_registro', 'asignacion', 'cambio_estado', 'alerta_sla', 'solicitud_info', 'resolucion_final') NOT NULL");
            });
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'sqlite') {
            Schema::create('notificaciones_old', function (Blueprint $table) {
                $table->id();
                $table->foreignId('incidencia_id')->constrained('incidencias')->cascadeOnDelete();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('destinatario_email', 150);
                $table->text('tipo')->nullable();
                $table->string('asunto', 255);
                $table->timestamp('enviada_at')->nullable();
                $table->timestamp('leida_at')->nullable();
                $table->timestamp('created_at')->useCurrent();
            });

            DB::unprepared('INSERT INTO notificaciones_old SELECT * FROM notificaciones');
            Schema::drop('notificaciones');
            Schema::rename('notificaciones_old', 'notificaciones');
        } else {
            Schema::table('notificaciones', function (Blueprint $table) {
                DB::statement("ALTER TABLE notificaciones MODIFY COLUMN tipo ENUM('confirmacion_registro', 'asignacion', 'cambio_estado', 'alerta_sla', 'solicitud_info') NOT NULL");
            });
        }
    }
};
