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
            'users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->string('address')->nullable();
                $table->string('phone_number')->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
		$table->decimal('lat', 10, 8)->nullable();
            	$table->decimal('lng', 11, 8)->nullable();
		$table->enum('status', ['Inactive', 'Pending', 'approved', 'banned'])->default('Pending');
                $table->decimal('rating', 3, 2)->nullable();
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
