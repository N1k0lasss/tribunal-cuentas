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
        Schema::create('resolucions', function (Blueprint $table) {
            $table->id();
            $table->string('numero', 50)->unique();

            $table->date('fecha_emision');

            $table->text('detalle')->nullable();

            $table->foreignId('solicitud_id')
                ->unique()
                ->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resolucions');
    }
};
