<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('boar_reservations', function (Blueprint $table) {
            $table->decimal('platform_fee', 10, 2)->nullable()->after('service_fee_amount');
            $table->decimal('boar_raiser_share', 10, 2)->nullable()->after('platform_fee');
        });
    }

    public function down(): void
    {
        Schema::table('boar_reservations', function (Blueprint $table) {
            $table->dropColumn(['platform_fee', 'boar_raiser_share']);
        });
    }
};
