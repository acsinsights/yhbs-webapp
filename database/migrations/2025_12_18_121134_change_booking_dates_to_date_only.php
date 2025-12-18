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
            // Change check_in and check_out from dateTime to date
            $table->date('check_in')->nullable()->change();
            $table->date('check_out')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Revert back to dateTime
            $table->dateTime('check_in')->nullable()->change();
            $table->dateTime('check_out')->nullable()->change();
        });
    }
};
