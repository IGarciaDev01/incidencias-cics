<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('empleados')
            ->where('tipo', 'paae')
            ->update(['tipo' => 'administrativo']);

        DB::table('incidencias')
            ->where('tipo_solicitante', 'paae')
            ->update(['tipo_solicitante' => 'administrativo']);

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE empleados MODIFY COLUMN tipo ENUM('docente','administrativo') NULL");
            DB::statement("ALTER TABLE incidencias MODIFY COLUMN tipo_solicitante ENUM('docente','administrativo') NOT NULL");
        }

        if (Schema::hasColumn('incidencias', 'token_seguimiento')) {
            Schema::table('incidencias', function (Blueprint $table) {
                $table->dropUnique('incidencias_token_seguimiento_unique');
                $table->dropColumn('token_seguimiento');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('incidencias', 'token_seguimiento')) {
            Schema::table('incidencias', function (Blueprint $table) {
                $table->string('token_seguimiento', 64)->nullable()->unique()->after('motivo_rechazo');
            });
        }

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE empleados MODIFY COLUMN tipo ENUM('docente','administrativo','paae') NULL");
            DB::statement("ALTER TABLE incidencias MODIFY COLUMN tipo_solicitante ENUM('docente','administrativo','paae') NOT NULL");
        }
    }
};
