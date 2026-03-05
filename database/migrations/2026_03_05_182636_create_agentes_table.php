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
        Schema::create('agentes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');

            $table->string('dni')->unique();

            $table->string('cargo')->nullable();
            $table->string('tipo_contrato')->nullable();

            $table->foreignId('area_id')
              ->constrained()
              ->cascadeOnDelete();

            $table->date('fecha_ingreso');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agentes');
    }
};
