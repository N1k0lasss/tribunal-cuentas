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
        Schema::create('workflow_eventos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_id')->constrained('solicituds')->cascadeOnDelete();
            $table->string('evento', 100);
            $table->string('etapa', 30)->nullable();
            $table->string('estado_anterior', 30)->nullable();
            $table->string('estado_nuevo', 30)->nullable();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('actor_agente_id')->nullable()->constrained('agentes')->nullOnDelete();
            $table->boolean('es_delegacion')->default(false);
            $table->foreignId('delegacion_id')->nullable()->constrained('delegaciones')->nullOnDelete();
            $table->text('detalle')->nullable();
            $table->string('ip', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('ocurrio_en');
            $table->timestamps();

            $table->index(['solicitud_id', 'ocurrio_en']);
            $table->index(['evento', 'ocurrio_en']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workflow_eventos');
    }
};
