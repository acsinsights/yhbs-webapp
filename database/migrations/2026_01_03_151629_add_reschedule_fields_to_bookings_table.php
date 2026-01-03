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
            $table->timestamp('reschedule_requested_at')->nullable()->after('cancelled_by');
            $table->enum('reschedule_status', ['pending', 'approved', 'rejected'])->nullable()->after('reschedule_requested_at');
            $table->text('reschedule_reason')->nullable()->after('reschedule_status');
            $table->datetime('new_check_in')->nullable()->after('reschedule_reason');
            $table->datetime('new_check_out')->nullable()->after('new_check_in');
            $table->decimal('reschedule_fee', 10, 2)->nullable()->after('new_check_out');
            $table->decimal('extra_fee', 10, 2)->nullable()->after('reschedule_fee');
            $table->text('extra_fee_remark')->nullable()->after('extra_fee');
            $table->unsignedBigInteger('rescheduled_by')->nullable()->after('extra_fee_remark');
            $table->foreign('rescheduled_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['rescheduled_by']);
            $table->dropColumn([
                'reschedule_requested_at',
                'reschedule_status',
                'reschedule_reason',
                'new_check_in',
                'new_check_out',
                'reschedule_fee',
                'extra_fee',
                'extra_fee_remark',
                'rescheduled_by'
            ]);
        });
    }
};
