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
            $table->json('unavailable_days')->nullable()->after('sort_order');
        });

        Schema::table('houses', function (Blueprint $table) {
            $table->json('unavailable_days')->nullable()->after('library');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boats', function (Blueprint $table) {
            $table->dropColumn('unavailable_days');
        });

        Schema::table('houses', function (Blueprint $table) {
            $table->dropColumn('unavailable_days');
        });
    }
};
