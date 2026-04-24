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
        Schema::table('resolucions', function (Blueprint $table): void {
            $table->string('gde_expediente', 80)->nullable()->after('numero');
            $table->string('gde_documento', 80)->nullable()->after('gde_expediente');

            $table->index('gde_expediente');
            $table->index('gde_documento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('resolucions', function (Blueprint $table): void {
            $table->dropIndex(['gde_expediente']);
            $table->dropIndex(['gde_documento']);
            $table->dropColumn(['gde_expediente', 'gde_documento']);
        });
    }
};
