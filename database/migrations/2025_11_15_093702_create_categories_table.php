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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('slug')->unique();
            $table->text('icon')->nullable();
            $table->string('type')->default('room')->comment('room, yatch');

            $table->timestamps();
        });

        Schema::create('category_room', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('room_id')->constrained('rooms');
        });

        Schema::create('category_yatch', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained('categories');
            $table->foreignId('yatch_id')->constrained('yatches');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
