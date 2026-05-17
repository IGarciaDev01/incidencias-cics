<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE incidencias MODIFY COLUMN estado ENUM(
                'pendiente_jefe',
                'pendiente_capital_humano',
                'pendiente_sindicato',
                'pendiente_subdireccion',
                'aprobada',
                'rechazada'
            ) NOT NULL DEFAULT 'pendiente_jefe'");
        }

        Schema::table('incidencias', function (Blueprint $table) {
            $table->timestamp('enviado_sindicato_at')->nullable()->after('revisado_por');
        });
    }

    public function down(): void
    {
        DB::table('incidencias')
            ->where('estado', 'pendiente_sindicato')
            ->update(['estado' => 'pendiente_capital_humano']);

        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropColumn('enviado_sindicato_at');
        });

        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE incidencias MODIFY COLUMN estado ENUM(
                'pendiente_jefe',
                'pendiente_capital_humano',
                'pendiente_subdireccion',
                'aprobada',
                'rechazada'
            ) NOT NULL DEFAULT 'pendiente_jefe'");
        }
    }
};
