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
        Schema::create('piglet_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users');
            $table->string('listing_title');
            $table->longText('description');
            $table->json('photos')->nullable();
            $table->unsignedSmallInteger('piglets_available');
            $table->unsignedTinyInteger('minimum_order')->nullable();
            $table->decimal('price_per_piglet', 10, 2)->nullable();
            $table->char('currency', 3)->default('PHP');
            $table->date('available_from')->nullable();
            $table->date('available_until')->nullable();
            $table->string('pickup_location');
            $table->text('pickup_details')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('facebook_profile_link')->nullable();
            $table->boolean('delivery_available')->default(false);
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->enum('sale_status', ['draft', 'listed', 'reserved', 'sold', 'cancelled'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('sold_out_at')->nullable();
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
        Schema::dropIfExists('piglet_sales');
    }
};
