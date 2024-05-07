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
            'product_requests', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->unsignedBigInteger('category_id');
                $table->decimal('price', 8, 2);
                $table->string('address');
                $table->string('phone_number');
                $table->text('description');
                $table->string('type');
                $table->decimal('ratings', 2, 1)->nullable();
                $table->integer('sold')->default(0);
                $table->integer('views')->default(0);
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_requests');
    }
};
