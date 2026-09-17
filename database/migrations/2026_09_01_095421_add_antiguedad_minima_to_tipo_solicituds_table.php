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
        Schema::table('tipo_solicituds', function (Blueprint $table) {
            $table->integer('antiguedad_minima_meses')->nullable()->after('limite_mensual_dias_horas');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipo_solicituds', function (Blueprint $table) {
            $table->dropColumn('antiguedad_minima_meses');
        });
    }
};
