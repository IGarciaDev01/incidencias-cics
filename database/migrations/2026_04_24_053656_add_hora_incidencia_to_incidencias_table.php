<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->string('hora_incidencia', 5)->nullable()->after('fecha_incidencia');
        });
    }

    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropColumn('hora_incidencia');
        });
    }
};
