<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('incidencias', 'email_reportante')) {
            return;
        }

        Schema::table('incidencias', function (Blueprint $table) {
            $table->string('email_reportante', 150)->nullable()->after('reportante_nombre');
        });
    }

    public function down(): void
    {
        Schema::table('incidencias', function (Blueprint $table) {
            $table->dropColumn('email_reportante');
        });
    }
};
