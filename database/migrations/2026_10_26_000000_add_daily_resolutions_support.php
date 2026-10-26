<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite que una resolución agrupe varias licencias (resolución del día)
     * sin dejar de soportar los registros históricos atados a una sola solicitud.
     */
    public function up(): void
    {
        Schema::table('resolucions', function (Blueprint $table) {
            $table->dropForeign(['solicitud_id']);
            $table->dropUnique(['solicitud_id']);
        });

        Schema::table('resolucions', function (Blueprint $table) {
            $table->foreignId('solicitud_id')->nullable()->change();
        });

        Schema::table('resolucions', function (Blueprint $table) {
            $table->foreign('solicitud_id')->references('id')->on('solicituds')->nullOnDelete();
        });

        Schema::create('resolucion_solicitud', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resolucion_id')->constrained('resolucions')->cascadeOnDelete();
            $table->foreignId('solicitud_id')->constrained('solicituds')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['resolucion_id', 'solicitud_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resolucion_solicitud');

        Schema::table('resolucions', function (Blueprint $table) {
            $table->dropForeign(['solicitud_id']);
        });

        Schema::table('resolucions', function (Blueprint $table) {
            $table->unsignedBigInteger('solicitud_id')->nullable(false)->change();
        });

        Schema::table('resolucions', function (Blueprint $table) {
            $table->foreign('solicitud_id')->references('id')->on('solicituds');
            $table->unique('solicitud_id');
        });
    }
};
