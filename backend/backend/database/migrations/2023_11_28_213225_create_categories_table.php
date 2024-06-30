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
            'categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            }
        );
    }
    

    /**
     * TODO: update
     * name	varchar(255)	utf8mb4_unicode_ci		No	None			Change Change	Drop Drop	
   * 	4	slug	varchar(255)	utf8mb4_unicode_ci		No	None			Change Change	Drop Drop	
   * 	5	image	varchar(255)	utf8mb4_unicode_ci		Yes	NULL			Change Change	Drop Drop	
   * 	6	created_at	timestamp			Yes	NULL			Change Change	Drop Drop	
   * 	7	updated_at	timestamp			Yes	NULL			Change Change	Drop Drop	
   * 8	nav_menu_image1	varchar(255)	utf8mb4_unicode_ci		Yes	NULL			Change Change	Drop Drop	
   * 	9	nav_menu_image2	
     * 
     * 
     */

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
