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
        Schema::create('driver_details', function (Blueprint $table) {
            $table->id();
	    $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('license_identification_number')->nullable();
            $table->string('vehicle_registration_number')->nullable();
            $table->string('vehicle_make_model')->nullable();
            $table->string('vehicle_color')->nullable();
            $table->string('license_plate_number')->nullable();
	    $table->string('license_image_path')->nullable();
            $table->string('vehicle_photo_path')->nullable();
	    $table->decimal('ratings', 5, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_details');
    }
};
