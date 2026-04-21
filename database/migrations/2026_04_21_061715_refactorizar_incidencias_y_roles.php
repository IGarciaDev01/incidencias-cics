<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $isMysql = DB::getDriverName() !== 'sqlite';

        // Only runs on existing databases that still have the old schema.
        $needsRefactor = $isMysql && Schema::hasColumn('incidencias', 'titulo');

        if ($needsRefactor) {
            DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM(
                'admin',
                'jefe_inmediato',
                'capital_humano',
                'subdireccion_academica'
            ) NOT NULL DEFAULT 'jefe_inmediato'");

            Schema::table('incidencias', function (Blueprint $table) {
                $table->dropIndex(['estado', 'prioridad']);
                $table->dropIndex(['prioridad']);
                $table->dropForeign(['categoria_id']);
                $table->dropForeign(['asignado_a']);
                $table->dropColumn([
                    'titulo',
                    'categoria_id',
                    'prioridad',
                    'es_anonima',
                    'reportante_email',
                    'reportante_telefono',
                    'fecha_limite',
                    'resolucion',
                    'asignado_a',
                ]);
            });

            DB::statement("ALTER TABLE incidencias MODIFY COLUMN estado ENUM(
                'pendiente_jefe',
                'pendiente_capital_humano',
                'pendiente_subdireccion',
                'aprobada',
                'rechazada'
            ) NOT NULL DEFAULT 'pendiente_jefe'");

            Schema::table('incidencias', function (Blueprint $table) {
                $table->string('numero_empleado', 20)->after('folio');
                $table->enum('tipo_solicitante', ['docente', 'administrativo'])->after('numero_empleado');
                $table->date('fecha_incidencia')->after('tipo_solicitante');
                $table->enum('tipo_incidencia', [
                    'retardo',
                    'permiso_economico',
                    'comision_oficial',
                    'salida_anticipada',
                ])->after('fecha_incidencia');
                $table->unsignedSmallInteger('minutos_retardo')->nullable()->after('tipo_incidencia');
                $table->text('descripcion')->nullable()->change();
                $table->index(['estado', 'numero_empleado']);
            });
        }
    }

    public function down(): void
    {
        $isMysql = DB::getDriverName() !== 'sqlite';

        if ($isMysql && Schema::hasColumn('incidencias', 'numero_empleado')) {
            Schema::table('incidencias', function (Blueprint $table) {
                $table->dropIndex(['estado', 'numero_empleado']);
                $table->dropColumn(['numero_empleado', 'tipo_solicitante', 'fecha_incidencia', 'tipo_incidencia', 'minutos_retardo']);
            });

            DB::statement("ALTER TABLE incidencias MODIFY COLUMN estado ENUM(
                'abierta', 'en_revision', 'aprobada', 'rechazada', 'en_proceso', 'resuelta', 'cerrada'
            ) NOT NULL DEFAULT 'abierta'");

            Schema::table('incidencias', function (Blueprint $table) {
                $table->string('titulo', 255)->default('');
                $table->foreignId('categoria_id')->constrained('categorias')->restrictOnDelete();
                $table->enum('prioridad', ['alta', 'media', 'baja'])->default('media');
                $table->boolean('es_anonima')->default(false);
                $table->string('reportante_email', 150)->nullable();
                $table->string('reportante_telefono', 20)->nullable();
                $table->timestamp('fecha_limite')->nullable();
                $table->text('resolucion')->nullable();
                $table->foreignId('asignado_a')->nullable()->constrained('users')->nullOnDelete();
                $table->index('prioridad');
                $table->index(['estado', 'prioridad']);
            });

            DB::statement("ALTER TABLE users MODIFY COLUMN rol ENUM(
                'admin', 'subdirector', 'coordinador'
            ) NOT NULL DEFAULT 'coordinador'");
        }
    }
};
