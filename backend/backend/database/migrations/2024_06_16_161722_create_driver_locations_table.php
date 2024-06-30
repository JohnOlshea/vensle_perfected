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
        Schema::create('driver_locations', function (Blueprint $table) {
            $table->id();
	    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->double('latitude');
            $table->double('longitude');
	    $table->boolean('is_online')->default(false);
	    $table->enum('status', ['free', 'assigned'])->default('free');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_locations');
    }
};
