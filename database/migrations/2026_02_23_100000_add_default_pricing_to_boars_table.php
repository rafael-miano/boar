<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Default price (money), downpayment (half of price), and default pay-with-pigs for boar raiser.
     */
    public function up(): void
    {
        Schema::table('boars', function (Blueprint $table) {
            $table->unsignedInteger('default_price_money')->default(0)->after('breeding_status_other');
            $table->unsignedInteger('default_downpayment')->default(0)->after('default_price_money');
            $table->unsignedInteger('default_pay_with_pigs')->default(1)->after('default_downpayment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boars', function (Blueprint $table) {
            $table->dropColumn(['default_price_money', 'default_downpayment', 'default_pay_with_pigs']);
        });
    }
};
