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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_archivo');

            $table->string('ruta');

            $table->string('tipo_documento');

            $table->text('descripcion')->nullable();

            $table->foreignId('solicitud_id')
                ->nullable()
                ->constrained();

            $table->foreignId('resolucion_id')
                ->nullable()
                ->constrained();

            $table->foreignId('agente_id')
                ->nullable()
                ->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
