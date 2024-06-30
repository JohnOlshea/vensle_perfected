<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProductRequest;
use App\Models\Category;
use App\Models\Specification;

class ProductRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorySporting = Category::where('name', 'Home & Appliances')->first();
        $specificationWaterproof = Specification::where('name', 'Waterproof')->first();

        $product_request = ProductRequest::create([
            'name' => 'First request',
            'category_id' => $categorySporting->id,
            'price' => 999.99,
            'address' => '123 Main St',
            'phone_number' => '+1234567890',
            'description' => 'A low-end phone no features.',
            'type' => 'Laptop',
            'ratings' => 5,
            'sold' => 5,
            'views' => 12,
        ]);

        //Attach specifications to product request
        $product_request->specifications()->attach($specificationWaterproof->id);

    }
}
