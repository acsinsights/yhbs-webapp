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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->morphs('bookingable');
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->integer('adults')->nullable();
            $table->integer('children')->nullable();
            $table->json('guest_details')->nullable()->comment('Array of guest names');
            $table->datetime('check_in')->nullable();
            $table->datetime('check_out')->nullable();
            $table->time('arrival_time')->nullable()->comment('Expected arrival time');
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('price_per_hour', 10, 2)->nullable();
            $table->foreignId('coupon_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->string('status')->default('pending')->comment('pending, booked, checked_in, cancelled, checked_out');
            $table->string('payment_status')->default('pending')->comment('pending, paid, failed');
            $table->string('payment_session_id')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->default('other')->comment('cash, card, other, online');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
