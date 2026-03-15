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
        Schema::create('boar_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boar_id')->constrained('boars');
            $table->foreignId('user_id')->constrained('users');
            $table->string('address');
            $table->date('service_date');
            $table->enum('service_fee_type', ['pig', 'money']);
            $table->integer('service_fee_amount');
            $table->string('female_pig_photo')->nullable();
            $table->text('notes')->nullable();
            $table->enum('reservation_status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->enum('service_status', ['pending', 'completed', 'cancelled'])->default('pending');
            // Expected birth date based on service_date (~115 days)
            $table->date('expected_due_date')->nullable();
            // Customer confirmation
            $table->timestamp('birth_confirmed_at')->nullable();
            $table->unsignedInteger('piglet_count')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boar_reservations');
    }
};
