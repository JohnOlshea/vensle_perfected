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
            'product_request_specifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_request_id');
                $table->unsignedBigInteger('specification_id');
                $table->timestamps();

                $table->foreign('product_request_id')->references('id')->on('product_requests')->onDelete('cascade');
                $table->foreign('specification_id')->references('id')->on('specifications')->onDelete('cascade');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_request_specifications');
    }
};
