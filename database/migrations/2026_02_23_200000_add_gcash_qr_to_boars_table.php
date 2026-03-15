<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * GCash QR image for payment when service fee type is money.
     */
    public function up(): void
    {
        Schema::table('boars', function (Blueprint $table) {
            $table->string('gcash_qr_image')->nullable()->after('default_pay_with_pigs');
        });
    }

    public function down(): void
    {
        Schema::table('boars', function (Blueprint $table) {
            $table->dropColumn('gcash_qr_image');
        });
    }
};
