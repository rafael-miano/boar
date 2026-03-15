<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Admin approves first (legitimacy check); then boar raiser makes final accept/reject.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE boar_reservations MODIFY COLUMN reservation_status ENUM('pending', 'accepted', 'rejected', 'pending_boar_raiser') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Move any pending_boar_raiser back to pending so we can shrink the enum
        DB::table('boar_reservations')
            ->where('reservation_status', 'pending_boar_raiser')
            ->update(['reservation_status' => 'pending']);

        DB::statement("ALTER TABLE boar_reservations MODIFY COLUMN reservation_status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending'");
    }
};
