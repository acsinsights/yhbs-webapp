<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('boats', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('service_type', ['yacht', 'taxi', 'ferry', 'limousine'])->default('yacht');
            $table->text('description')->nullable();
            $table->text('image')->nullable();
            $table->json('images')->nullable();

            // Capacity
            $table->integer('min_passengers')->default(1);
            $table->integer('max_passengers');

            // Pricing - based on service type
            // For marina trips and taxi: per hour pricing
            $table->decimal('price_per_hour', 10, 2)->nullable();
            $table->decimal('price_1hour', 10, 2)->nullable();
            $table->decimal('price_2hours', 10, 2)->nullable();
            $table->decimal('price_3hours', 10, 2)->nullable();
            $table->decimal('additional_hour_price', 10, 2)->nullable();

            // For ferry and public services: per person pricing
            $table->decimal('price_per_person_adult', 10, 2)->nullable();
            $table->decimal('price_per_person_child', 10, 2)->nullable();
            $table->decimal('private_trip_price', 10, 2)->nullable();
            $table->decimal('private_trip_return_price', 10, 2)->nullable();

            // For limousine: time-based pricing
            $table->decimal('price_15min', 10, 2)->nullable();
            $table->decimal('price_30min', 10, 2)->nullable();
            $table->decimal('price_full_boat', 10, 2)->nullable();

            // Additional info
            $table->string('location')->nullable();
            $table->text('features')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();

            // Buffer time in minutes (for cleaning/preparation between bookings)
            $table->integer('buffer_time')->default(0)->comment('Buffer time in minutes');

            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boats');
    }
};
