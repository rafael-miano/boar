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
        Schema::table('stud_services', function (Blueprint $table) {
            $table->foreignId('boar_reservation_id')
                ->nullable()
                ->after('boar_id')
                ->constrained('boar_reservations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stud_services', function (Blueprint $table) {
            $table->dropForeign(['boar_reservation_id']);
            $table->dropColumn('boar_reservation_id');
        });
    }
};

