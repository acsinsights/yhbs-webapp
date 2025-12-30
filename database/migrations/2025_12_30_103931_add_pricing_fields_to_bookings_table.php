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
            $table->decimal('price_per_night', 10, 2)->nullable()->after('price');
            $table->integer('nights')->nullable()->after('price_per_night');
            $table->decimal('service_fee', 10, 2)->default(0)->after('nights');
            $table->decimal('tax', 10, 2)->default(0)->after('service_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['price_per_night', 'nights', 'service_fee', 'tax']);
        });
    }
};
