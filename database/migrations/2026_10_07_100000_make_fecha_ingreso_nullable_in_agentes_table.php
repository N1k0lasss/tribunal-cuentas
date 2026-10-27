<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Los agentes sincronizados desde la API de Legajos pueden no tener
     * legajo activo (dada de baja o nunca dada de alta): en ese caso no hay
     * fecha de ingreso que traer y el registro queda como simple referencia
     * de identidad (integracion-legajos-api.md §2.1).
     */
    public function up(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->date('fecha_ingreso')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * Solo puede volver a NOT NULL si no quedan agentes sin fecha de ingreso.
     */
    public function down(): void
    {
        Schema::table('agentes', function (Blueprint $table) {
            $table->date('fecha_ingreso')->nullable(false)->change();
        });
    }
};
