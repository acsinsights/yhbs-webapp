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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('discount_type', ['percentage', 'fixed', 'free_nights'])->default('percentage');
            $table->decimal('discount_value', 10, 2);
            $table->integer('min_nights_required')->nullable()->comment('Minimum nights required for free_nights discount type');
            $table->decimal('min_booking_amount', 10, 2)->default(0);
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            $table->date('valid_from');
            $table->date('valid_until');
            $table->integer('usage_limit')->nullable(); // null = unlimited
            $table->integer('usage_count')->default(0);
            $table->integer('usage_limit_per_user')->default(1);
            $table->boolean('is_active')->default(true);

            // Property selection fields
            $table->enum('applicable_to', ['all', 'specific'])->default('all');
            $table->json('applicable_rooms')->nullable();
            $table->json('applicable_houses')->nullable();
            $table->json('applicable_boats')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
