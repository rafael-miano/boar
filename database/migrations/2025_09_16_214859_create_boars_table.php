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
        Schema::create('boars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('boar_picture')->nullable();
            $table->string('boar_name');
            $table->enum('boar_type', ['pietrain', 'large-white', 'duroc', 'other']);
            $table->string('boar_type_other')->nullable();
            $table->date('breeding_maturity_date');
            $table->enum('health_status', ['healthy', 'sick', 'injured', 'other']);
            $table->string('health_status_other')->nullable();
            $table->enum('breeding_status', ['active', 'inactive', 'other']);
            $table->string('breeding_status_other')->nullable();
            $table->boolean('is_published')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boars');
    }
};
