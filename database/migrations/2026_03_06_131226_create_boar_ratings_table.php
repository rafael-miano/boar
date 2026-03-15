<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boar_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boar_reservation_id')->constrained('boar_reservations')->cascadeOnDelete();
            $table->foreignId('boar_id')->constrained('boars')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating'); // 1–5
            $table->text('comment')->nullable();
            $table->timestamps();

            // One rating per completed reservation
            $table->unique('boar_reservation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boar_ratings');
    }
};
