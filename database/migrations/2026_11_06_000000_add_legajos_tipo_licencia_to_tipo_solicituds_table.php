<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipo_solicituds', function (Blueprint $table): void {
            $table->string('legajos_tipo_licencia', 100)
                ->nullable()
                ->after('nombre')
                ->comment('Equivalente del tipo en Legajos. NULL: no se envía.');
        });

        // Compatibilidad con el comportamiento anterior: los tipos medidos en
        // días ya se enviaban con su propio nombre. Los tipos en horas quedan
        // sin mapeo (NULL) hasta acordar su equivalencia con Legajos.
        DB::table('tipo_solicituds')
            ->where('unidad_medida', 'dias')
            ->update(['legajos_tipo_licencia' => DB::raw('nombre')]);
    }

    public function down(): void
    {
        Schema::table('tipo_solicituds', function (Blueprint $table): void {
            $table->dropColumn('legajos_tipo_licencia');
        });
    }
};
