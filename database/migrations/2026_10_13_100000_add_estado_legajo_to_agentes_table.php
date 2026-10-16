<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Estado del legajo en el Sistema de Legajos (1 = activo, 0 = dado de
     * baja) y datos de la baja. Se importan desde la API para poder mostrar
     * a los agentes dados de baja sin confundirlos con los activos.
     *
     * Ver integracion-legajos-api.md y `php artisan legajos:sync`.
     */
    public function up(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->unsignedTinyInteger('estado_legajo')->nullable();
            $table->date('fecha_baja')->nullable();
            $table->string('motivo_baja', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->dropColumn(['estado_legajo', 'fecha_baja', 'motivo_baja']);
        });
    }
};
