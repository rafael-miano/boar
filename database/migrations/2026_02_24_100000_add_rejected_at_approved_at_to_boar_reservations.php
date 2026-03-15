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
        Schema::table('boar_reservations', function (Blueprint $table) {
            $table->timestamp('rejected_at')->nullable()->after('rejection_message');
            $table->timestamp('approved_at')->nullable()->after('rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('boar_reservations', function (Blueprint $table) {
            $table->dropColumn(['rejected_at', 'approved_at']);
        });
    }
};
