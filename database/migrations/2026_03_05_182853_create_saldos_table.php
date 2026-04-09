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
        Schema::create('saldos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agente_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->integer('anio');

            $table->string('tipo_saldo');

            $table->integer('dias_otorgados');

            $table->integer('dias_usados')->default(0);
            $table->timestamps();
            $table->unique(['agente_id', 'anio', 'tipo_saldo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('saldos');
    }
};
