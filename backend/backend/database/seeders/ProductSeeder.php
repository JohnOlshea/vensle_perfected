<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use App\Models\Specification;
use App\Models\Image;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
	$categoryElectronics = Category::where('name', 'Electronics')->first();
	$categoryHome = Category::where('name', 'Home & Appliances')->first();
	$specificationTouchscreen = Specification::where('name', 'Touchscreen')->first();
	$specificationWireless = Specification::where('name', 'Wireless')->first();

	$products = Product::factory(10)->create();
	foreach ($products as $product) {
            // Create images for each product
            $images = Image::factory(3)->create(['product_id' => $product->id]);

            // Designate the second image as the display image
            $product->update(['display_image_id' => $images[1]->id]);

            // Attach specifications to products
            $product->specifications()->attach([$specificationTouchscreen->id, $specificationWireless->id]);
        }
	
	/**
        $product1 = Product::create([
            'name' => 'Sample Laptop',
	    'category_id' => $categoryElectronics->id,
            'condition' => 'New',
            'price' => 999.99,
            'address' => '123 Main St',
            'phone_number' => '+1234567890',
            'description' => 'A high-end laptop with amazing features.',
            'type' => 'Laptop',
            'ratings' => 4.5,
            'quantity' => 10,
            'sold' => 2,
            'views' => 50,
            'status' => 'Active',
        ]);

        $product2 = Product::create([
            'name' => 'Sample Smart Fridge',
	    'category_id' => $categoryElectronics->id,
            'condition' => 'New',
            'price' => 1499.99,
            'address' => '456 Oak St',
            'phone_number' => '+9876543210',
            'description' => 'A smart fridge that makes your life easier.',
	    'type' => 'Appliance',
            'ratings' => 3.8,
            'quantity' => 5,
            'sold' => 1,
            'views' => 30,	    
            'status' => 'Active',
	]);

	//Attach specifications to products
	$product1->specifications()->attach($specificationTouchscreen->id);
	$product2->specifications()->attach($specificationWireless->id);
	 */
    }
}
