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
        Schema::table('solicituds', function (Blueprint $table) {
            $table->index(['estado', 'fecha_inicio', 'fecha_fin'], 'solicituds_estado_fechas_index');
            $table->index(['agente_id', 'fecha_solicitud'], 'solicituds_agente_fecha_solicitud_index');
            $table->index(['tipo_solicitud_id', 'fecha_solicitud'], 'solicituds_tipo_fecha_solicitud_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicituds', function (Blueprint $table) {
            $table->dropIndex('solicituds_estado_fechas_index');
            $table->dropIndex('solicituds_agente_fecha_solicitud_index');
            $table->dropIndex('solicituds_tipo_fecha_solicitud_index');
        });
    }
};
