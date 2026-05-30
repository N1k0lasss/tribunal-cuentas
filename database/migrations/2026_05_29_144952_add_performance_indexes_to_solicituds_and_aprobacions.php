<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicituds', function (Blueprint $table) {
            $table->index('estado');
            $table->index('fecha_solicitud');
            $table->index(['agente_id', 'estado']);
            $table->index(['fecha_inicio', 'fecha_fin']);
        });

        Schema::table('aprobacions', function (Blueprint $table) {
            $table->index(['solicitud_id', 'etapa']);
            $table->index(['solicitud_id', 'etapa', 'estado']);
            $table->index('aprobado_por');
        });
    }

    public function down(): void
    {
        Schema::table('solicituds', function (Blueprint $table) {
            $table->dropIndex(['estado']);
            $table->dropIndex(['fecha_solicitud']);
            $table->dropIndex(['agente_id', 'estado']);
            $table->dropIndex(['fecha_inicio', 'fecha_fin']);
        });

        Schema::table('aprobacions', function (Blueprint $table) {
            $table->dropIndex(['solicitud_id', 'etapa']);
            $table->dropIndex(['solicitud_id', 'etapa', 'estado']);
            $table->dropIndex(['aprobado_por']);
        });
    }
};
