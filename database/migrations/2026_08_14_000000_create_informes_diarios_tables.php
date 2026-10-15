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
        Schema::create('informes_diarios', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->unique();
            $table->text('observaciones_generales')->nullable();
            $table->string('destinatario')->default('Secretaría General');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('informe_diario_detalles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('informe_diario_id')->constrained('informes_diarios')->cascadeOnDelete();
            $table->foreignId('agente_id')->constrained('agentes')->cascadeOnDelete();
            $table->enum('tipo_novedad', [
                'presente',
                'licencia',
                'falta_justificada',
                'ausente_sin_aviso',
                'tardanza',
            ])->default('presente');
            $table->foreignId('solicitud_id')->nullable()->constrained('solicituds')->nullOnDelete();
            $table->foreignId('tipo_solicitud_id')->nullable()->constrained('tipo_solicituds')->nullOnDelete();
            $table->string('hora_llegada')->nullable();
            $table->integer('minutos_tardanza')->nullable();
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['informe_diario_id', 'agente_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informe_diario_detalles');
        Schema::dropIfExists('informes_diarios');
    }
};
