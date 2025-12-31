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
        Schema::create('amenity_boat', function (Blueprint $table) {
            $table->foreignId('amenity_id')->constrained('amenities')->onDelete('cascade');
            $table->foreignId('boat_id')->constrained('boats')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amenity_boat');
    }
};
