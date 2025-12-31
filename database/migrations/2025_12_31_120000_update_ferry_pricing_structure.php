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
        Schema::table('boats', function (Blueprint $table) {
            // Remove old ferry per-person pricing (child pricing removed, adult/child same now)
            $table->dropColumn([
                'price_per_person_child',
                'private_trip_price',
                'private_trip_return_price',
                'price_per_person_adult'
            ]);

            // Add private trip pricing (per hour) with weekday/weekend distinction
            $table->decimal('ferry_private_weekday', 10, 2)->nullable();
            $table->decimal('ferry_private_weekend', 10, 2)->nullable();

            // Add public trip pricing (per person/hour) with weekday/weekend distinction
            $table->decimal('ferry_public_weekday', 10, 2)->nullable();
            $table->decimal('ferry_public_weekend', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boats', function (Blueprint $table) {
            // Restore old columns
            $table->decimal('price_per_person_adult', 10, 2)->nullable();
            $table->decimal('price_per_person_child', 10, 2)->nullable();
            $table->decimal('private_trip_price', 10, 2)->nullable();
            $table->decimal('private_trip_return_price', 10, 2)->nullable();

            // Remove new columns
            $table->dropColumn([
                'ferry_private_weekday',
                'ferry_private_weekend',
                'ferry_public_weekday',
                'ferry_public_weekend'
            ]);
        });
    }
};
