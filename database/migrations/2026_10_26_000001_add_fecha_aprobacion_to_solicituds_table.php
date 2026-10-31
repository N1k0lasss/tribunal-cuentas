<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registra la fecha en la que la solicitud quedó aprobada definitivamente.
     * Es la fecha que usa la resolución diaria para agrupar las licencias.
     */
    public function up(): void
    {
        Schema::table('solicituds', function (Blueprint $table) {
            $table->date('fecha_aprobacion')->nullable()->after('estado');
        });

        DB::table('solicituds')
            ->where('estado', 'aprobado')
            ->chunkById(100, function ($solicitudes): void {
                foreach ($solicitudes as $solicitud) {
                    $fecha = DB::table('aprobacions')
                        ->where('solicitud_id', $solicitud->id)
                        ->where('estado', 'aprobado')
                        ->max('fecha');

                    DB::table('solicituds')
                        ->where('id', $solicitud->id)
                        ->update([
                            'fecha_aprobacion' => $fecha
                                ? Carbon::parse($fecha)->toDateString()
                                : Carbon::parse($solicitud->updated_at)->toDateString(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('solicituds', function (Blueprint $table) {
            $table->dropColumn('fecha_aprobacion');
        });
    }
};
