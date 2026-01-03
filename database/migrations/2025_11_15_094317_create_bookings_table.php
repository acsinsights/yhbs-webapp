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

            // Cancellation fields
            $table->timestamp('cancellation_requested_at')->nullable();
            $table->enum('cancellation_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->decimal('refund_amount', 10, 2)->nullable();
            $table->enum('refund_status', ['pending', 'processing', 'completed', 'failed'])->nullable();
            $table->unsignedBigInteger('cancelled_by')->nullable();
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');

            // Reschedule fields
            $table->timestamp('reschedule_requested_at')->nullable();
            $table->enum('reschedule_status', ['pending', 'approved', 'rejected'])->nullable();
            $table->text('reschedule_reason')->nullable();
            $table->datetime('new_check_in')->nullable();
            $table->datetime('new_check_out')->nullable();
            $table->decimal('reschedule_fee', 10, 2)->nullable();
            $table->decimal('extra_fee', 10, 2)->nullable();
            $table->text('extra_fee_remark')->nullable();
            $table->unsignedBigInteger('rescheduled_by')->nullable();
            $table->foreign('rescheduled_by')->references('id')->on('users')->onDelete('set null');

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
