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
        Schema::create('stud_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boar_id')->constrained('boars');
            $table->string('client_name');
            $table->date('service_date');
            $table->enum('service_fee_type', ['pig', 'money']);
            $table->integer('service_fee_amount');
            $table->enum('service_status', ['pending', 'completed', 'cancelled']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stud_services');
    }
};
