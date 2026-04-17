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
        Schema::create('firmas_digitales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicituds')->cascadeOnDelete();
            $table->foreignId('aprobacion_id')->constrained('aprobacions')->cascadeOnDelete();
            $table->foreignId('agente_id')->constrained('agentes')->cascadeOnDelete();
            $table->enum('etapa', ['jefatura', 'secretaria', 'direccion']);
            $table->string('hash_documento', 128);
            $table->string('ip_firma', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('fecha_firma');
            $table->longText('firma_base64')->nullable();
            $table->string('pin_hash')->nullable();
            $table->timestamps();

            $table->unique(['solicitud_id', 'etapa']);
            $table->index(['aprobacion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firmas_digitales');
    }
};
