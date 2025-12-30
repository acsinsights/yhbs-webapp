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
        Schema::create('amenities', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('icon')->nullable();
            $table->string('type')->default('room');

            $table->timestamps();
        });

        Schema::create('amenity_room', function (Blueprint $table) {
            $table->foreignId('amenity_id')->constrained('amenities');
            $table->foreignId('room_id')->constrained('rooms');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amenities');
    }
};
