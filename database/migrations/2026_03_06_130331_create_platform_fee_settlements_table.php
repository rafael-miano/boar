<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('platform_fee_settlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boar_raiser_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('receipt_image');
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamp('submitted_at')->useCurrent();
            $table->timestamp('verified_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('platform_fee_settlements');
    }
};
