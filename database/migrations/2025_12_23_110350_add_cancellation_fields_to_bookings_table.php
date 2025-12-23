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
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('cancellation_requested_at')->nullable()->after('notes');
            $table->enum('cancellation_status', ['pending', 'approved', 'rejected'])->nullable()->after('cancellation_requested_at');
            $table->text('cancellation_reason')->nullable()->after('cancellation_status');
            $table->timestamp('cancelled_at')->nullable()->after('cancellation_reason');
            $table->decimal('refund_amount', 10, 2)->nullable()->after('cancelled_at');
            $table->enum('refund_status', ['pending', 'processing', 'completed', 'failed'])->nullable()->after('refund_amount');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('refund_status');
            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn([
                'cancellation_requested_at',
                'cancellation_status',
                'cancellation_reason',
                'cancelled_at',
                'refund_amount',
                'refund_status',
                'cancelled_by'
            ]);
        });
    }
};
