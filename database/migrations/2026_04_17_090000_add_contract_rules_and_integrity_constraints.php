<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tipo_solicituds', function (Blueprint $table): void {
            $table->json('contratos_habilitados')->nullable()->after('categoria');
        });

        DB::table('tipo_solicituds')->update([
            'contratos_habilitados' => json_encode(['comun', 'servicio', 'planta_permanente']),
        ]);

        DB::table('agentes')
            ->select(['id', 'tipo_contrato'])
            ->orderBy('id')
            ->lazyById()
            ->each(function (object $agente): void {
                $tipoOriginal = is_string($agente->tipo_contrato)
                    ? mb_strtolower(trim($agente->tipo_contrato))
                    : null;

                $tipoNormalizado = match ($tipoOriginal) {
                    'comun', 'común', 'planta transitoria', 'transitoria' => 'comun',
                    'servicio', 'contrato de locación', 'contrato de locacion', 'locación', 'locacion' => 'servicio',
                    'planta permanente', 'permanente' => 'planta_permanente',
                    default => in_array($agente->tipo_contrato, ['comun', 'servicio', 'planta_permanente'], true)
                        ? $agente->tipo_contrato
                        : null,
                };

                DB::table('agentes')
                    ->where('id', $agente->id)
                    ->update(['tipo_contrato' => $tipoNormalizado]);
            });

        Schema::table('aprobacions', function (Blueprint $table): void {
            $table->unique(['solicitud_id', 'etapa'], 'aprobacions_solicitud_etapa_unique');
        });

        Schema::table('solicituds', function (Blueprint $table): void {
            $table->index(['agente_id', 'estado'], 'solicituds_agente_estado_index');
            $table->index(['estado', 'fecha_solicitud'], 'solicituds_estado_fecha_solicitud_index');
            $table->index(['tipo_solicitud_id', 'estado'], 'solicituds_tipo_estado_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicituds', function (Blueprint $table): void {
            $table->dropIndex('solicituds_agente_estado_index');
            $table->dropIndex('solicituds_estado_fecha_solicitud_index');
            $table->dropIndex('solicituds_tipo_estado_index');
        });

        Schema::table('aprobacions', function (Blueprint $table): void {
            $table->dropUnique('aprobacions_solicitud_etapa_unique');
        });

        Schema::table('tipo_solicituds', function (Blueprint $table): void {
            $table->dropColumn('contratos_habilitados');
        });
    }
};
