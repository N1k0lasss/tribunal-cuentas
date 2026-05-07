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
            $table->timestamp('justificacion_vencimiento_notificado_en')->nullable()->after('justificacion_presentada_en');
            $table->timestamp('prorroga_vencimiento_notificado_en')->nullable()->after('prorroga_vence_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicituds', function (Blueprint $table) {
            $table->dropColumn([
                'justificacion_vencimiento_notificado_en',
                'prorroga_vencimiento_notificado_en',
            ]);
        });
    }
};
