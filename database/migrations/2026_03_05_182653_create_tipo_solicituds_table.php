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
        Schema::create('tipo_solicituds', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('categoria', 50)->nullable();
            $table->string('articulo_legal', 50)->nullable();
            $table->integer('duracion_maxima_dias')->nullable();
            
            // Nuevos campos del plan
            $table->enum('unidad_medida', ['dias', 'horas'])->default('dias');
            $table->enum('tipo_dias', ['corridos', 'habiles'])->nullable();
            $table->integer('goce_sueldo_porcentaje')->default(100);
            $table->boolean('requiere_intervencion_medica')->default(false);
            $table->integer('limite_mensual_dias_horas')->nullable();
            
            $table->boolean('requiere_adjunto')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_solicituds');
    }
};
