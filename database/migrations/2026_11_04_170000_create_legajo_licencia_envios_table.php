<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legajo_licencia_envios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('documento_id')->constrained('documentos')->restrictOnDelete();
            $table->string('dni', 10);
            $table->unsignedBigInteger('legajo_id')->nullable();
            $table->json('datos');
            $table->string('estado', 20)->default('pendiente')->index();
            $table->unsignedInteger('intentos')->default(0);
            $table->timestamp('proximo_intento_at')->nullable()->index();
            $table->timestamp('iniciado_at')->nullable();
            $table->timestamp('enviado_at')->nullable();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->text('ultimo_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legajo_licencia_envios');
    }
};
