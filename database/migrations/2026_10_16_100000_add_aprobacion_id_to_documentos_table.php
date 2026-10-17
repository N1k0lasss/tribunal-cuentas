<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Vincula cada documento a la aprobación (etapa) en la que fue incorporado.
     *
     * Se usa para el circuito de firma externa: cada entidad descarga la solicitud,
     * la firma digitalmente y sube el PDF resultante. Ese archivo queda asociado a
     * la aprobación de su etapa.
     */
    public function up(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->foreignId('aprobacion_id')
                ->nullable()
                ->after('solicitud_id')
                ->constrained('aprobacions')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documentos', function (Blueprint $table) {
            $table->dropForeign(['aprobacion_id']);
            $table->dropColumn('aprobacion_id');
        });
    }
};
