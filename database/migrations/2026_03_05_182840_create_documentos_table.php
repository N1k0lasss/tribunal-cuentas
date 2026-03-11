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
            $table->string('nombre_archivo', 100);

            $table->string('ruta', 200);

            $table->string('tipo_documento', 50);

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
