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
        Schema::table(
            'products', function (Blueprint $table) {
                // Add a foreign key to the products table referencing the id column of the images table
                $table->unsignedBigInteger('display_image_id')->nullable();
                $table->foreign('display_image_id')
                    ->references('id')->on('images')
                    ->onDelete('set null');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table(
            'products', function (Blueprint $table) {
                // Drop the foreign key constraint
                $table->dropForeign(['display_image_id']);
                $table->dropColumn('display_image_id');
            }
        );
    }
};
