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
            'feedback', function (Blueprint $table) {
                $table->id();
                $table->text('content')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->integer('rating')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users');
                $table->foreign('product_id')->references('id')->on('products');
                $table->foreign('parent_id')->references('id')->on('feedback');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
