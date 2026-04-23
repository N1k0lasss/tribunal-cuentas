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
        Schema::create('delegaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('titular_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('delegado_user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('etapa', ['jefatura', 'secretaria', 'direccion']);
            $table->dateTime('vigente_desde');
            $table->dateTime('vigente_hasta');
            $table->text('motivo')->nullable();
            $table->boolean('activa')->default(true);
            $table->foreignId('creada_por_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['delegado_user_id', 'etapa', 'activa']);
            $table->index(['titular_user_id', 'etapa', 'activa']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delegaciones');
    }
};
