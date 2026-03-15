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

            $table->date('fecha_fin');

            $table->integer('dias_solicitados');

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
