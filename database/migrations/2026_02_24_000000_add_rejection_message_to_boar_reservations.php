<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Message from boar raiser when they reject a reservation (visible to customer).
     */
    public function up(): void
    {
        Schema::table('boar_reservations', function (Blueprint $table) {
            $table->text('rejection_message')->nullable()->after('payment_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('boar_reservations', function (Blueprint $table) {
            $table->dropColumn('rejection_message');
        });
    }
};
