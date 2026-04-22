<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->enum('tipo', ['docente', 'administrativo', 'paae'])
                ->nullable()
                ->after('email');
        });

        // Expand enum on MySQL/MariaDB (SQLite uses plain text).
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE incidencias MODIFY COLUMN tipo_solicitante ENUM('docente','administrativo','paae') NOT NULL");
        }
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropColumn('tipo');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE incidencias MODIFY COLUMN tipo_solicitante ENUM('docente','administrativo') NOT NULL");
        }
    }
};
