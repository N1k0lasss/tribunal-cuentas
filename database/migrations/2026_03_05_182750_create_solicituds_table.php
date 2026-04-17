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
        Schema::create('solicituds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agente_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('tipo_solicitud_id')
                ->constrained('tipo_solicituds');

            $table->date('fecha_solicitud');

            $table->date('fecha_inicio');

            // fecha_fin pasa a ser nullable porque licencias de 1 día u horas no lo ocupan
            $table->date('fecha_fin')->nullable();

            $table->integer('dias_solicitados')->nullable();

            // Nuevos campos para horas (franquicias)
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->decimal('horas_solicitadas', 4, 2)->nullable();

            $table->string('motivo', 100)->nullable();

            $table->enum('estado', [
                'pendiente',
                'en_revision',
                'aprobado',
                'rechazado',
            ])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicituds');
    }
};
