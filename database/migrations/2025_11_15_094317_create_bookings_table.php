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
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();

            $table->morphs('bookingable');
            $table->foreignId('user_id')->constrained('users');
            $table->integer('adults')->nullable();
            $table->integer('children')->nullable();
            $table->dateTime('check_in')->nullable();
            $table->dateTime('check_out')->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->string('status')->default('pending')->comment('pending, booked, checked_in, cancelled, checked_out');
            $table->string('payment_status')->default('pending')->comment('pending, paid, failed');
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
