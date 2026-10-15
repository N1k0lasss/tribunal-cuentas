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
            $table->foreignId('solicitud_origen_id')
                ->nullable()
                ->after('tipo_solicitud_id')
                ->constrained('solicituds')
                ->nullOnDelete();

            $table->boolean('es_prorroga')->default(false)->after('estado');
            $table->timestamp('justificacion_vence_en')->nullable()->after('es_prorroga');
            $table->timestamp('justificacion_presentada_en')->nullable()->after('justificacion_vence_en');
            $table->timestamp('prorroga_vence_en')->nullable()->after('justificacion_presentada_en');

            $table->index(['solicitud_origen_id', 'es_prorroga']);
            $table->index(['justificacion_vence_en']);
            $table->index(['prorroga_vence_en']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicituds', function (Blueprint $table) {
            $table->dropConstrainedForeignId('solicitud_origen_id');
            $table->dropColumn([
                'es_prorroga',
                'justificacion_vence_en',
                'justificacion_presentada_en',
                'prorroga_vence_en',
            ]);
        });
    }
};
