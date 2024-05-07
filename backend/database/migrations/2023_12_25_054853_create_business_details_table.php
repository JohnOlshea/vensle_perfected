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
        Schema::create(
            'business_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained();
		$table->string('business_name')->nullable()->default(null);
                $table->string('business_email')->unique()->nullable();
                $table->string('phone')->nullable();
                $table->string('business_address')->nullable();
                $table->string('certificate')->nullable();
                $table->string('bank_name')->nullable();
                $table->string('account_number')->nullable();
                $table->string('profile_picture')->nullable();
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_details');
    }
};
