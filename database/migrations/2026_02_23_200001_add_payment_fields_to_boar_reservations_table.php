<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Payment receipt upload, verify/reject by boar raiser; confirmed = payment verified.
     */
    public function up(): void
    {
        Schema::table('boar_reservations', function (Blueprint $table) {
            $table->string('payment_receipt_image')->nullable()->after('notes');
            $table->enum('payment_status', ['pending', 'verified', 'rejected'])->default('pending')->after('payment_receipt_image');
            $table->timestamp('payment_verified_at')->nullable()->after('payment_status');
        });

        DB::statement("ALTER TABLE boar_reservations MODIFY COLUMN reservation_status ENUM('pending', 'accepted', 'rejected', 'pending_boar_raiser', 'confirmed') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::table('boar_reservations')->where('reservation_status', 'confirmed')->update(['reservation_status' => 'accepted']);
        DB::statement("ALTER TABLE boar_reservations MODIFY COLUMN reservation_status ENUM('pending', 'accepted', 'rejected', 'pending_boar_raiser') DEFAULT 'pending'");

        Schema::table('boar_reservations', function (Blueprint $table) {
            $table->dropColumn(['payment_receipt_image', 'payment_status', 'payment_verified_at']);
        });
    }
};
