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
        Schema::create('aprobacions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('aprobado_por')
                ->constrained('agentes');
            $table->enum('etapa',  
            [
                'jefatura',
                'secretaria',
                'direccion'
            ]);
            $table->enum('estado', [
                'pendiente',
                'aprobado',
                'rechazado'
            ])->default('pendiente');

            $table->text('observacion')->nullable();
            $table->date('fecha');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aprobacions');
    }
};
