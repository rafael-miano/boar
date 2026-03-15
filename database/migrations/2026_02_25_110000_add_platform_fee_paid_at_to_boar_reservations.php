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
            $table->timestamp('platform_fee_paid_at')->nullable()->after('boar_raiser_share');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boar_reservations', function (Blueprint $table) {
            $table->dropColumn('platform_fee_paid_at');
        });
    }
};

