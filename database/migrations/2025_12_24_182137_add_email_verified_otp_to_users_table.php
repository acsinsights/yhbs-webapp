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
        // Email verified at already exists, just add type to password_reset_otps
        Schema::table('password_reset_otps', function (Blueprint $table) {
            $table->string('type', 20)->default('password_reset')->after('otp'); // password_reset or registration
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('password_reset_otps', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
