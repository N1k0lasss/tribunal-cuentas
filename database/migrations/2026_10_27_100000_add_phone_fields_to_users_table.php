<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('telefono', 16)->nullable()->unique()->after('email');
            $table->timestamp('telefono_verified_at')->nullable()->after('telefono');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique(['telefono']);
            $table->dropColumn(['telefono', 'telefono_verified_at']);
        });
    }
};
