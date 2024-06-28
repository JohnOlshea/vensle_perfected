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
            'orders', function (Blueprint $table) {
                $table->id();
		        //TODO:?->onDelete('cascade');
                $table->foreignId('user_id')->constrained();
		        $table->foreignId('driver_id')->nullable()->constrained('users');
		        //$table->foreignId('assigned_driver_id')->nullable()->constrained('users');
		        $table->unsignedBigInteger('shipping_address_id')->nullable();

                $table->string('stripe_session_id')->nullable();
                $table->string('order_number')->unique()->nullable();
		        $table->enum('payment_method', ['pay_on_delivery', 'stripe'])->default('stripe');
		        $table->enum('proof_type', ['code', 'picture'])->default('picture');
		        $table->string('delivery_code')->nullable();
        	    $table->string('delivery_proof_image')->nullable();
                $table->boolean('paid')->default(false);
		        $table->enum('status', ['Inactive', 'Ongoing', 'Pending', 'Processing', 'Completed', 'Cancelled'])->default('Inactive');
                $table->decimal('total_price', 8, 2);
	            $table->json('rejected_driver_ids')->nullable();
                $table->timestamps();

		        $table->foreign('shipping_address_id')->references('id')->on('shipping_addresses')->onDelete('set null');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
