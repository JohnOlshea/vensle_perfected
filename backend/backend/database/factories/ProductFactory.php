<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = Category::pluck('id')->toArray();

        return [
            'name' => $this->faker->sentence,
            'category_id' => $this->faker->randomElement($categories),
            'condition' => $this->faker->randomElement(['New', 'Fairly Used', 'N/A']),
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'address' => $this->faker->address,
            'phone_number' => $this->faker->phoneNumber,
            'description' => $this->faker->paragraph,
            'type' => $this->faker->randomElement(['Laptop', 'Appliance', 'Other']),
            'ratings' => $this->faker->randomFloat(1, 1, 5),
            'quantity' => $this->faker->numberBetween(1, 100),
            'sold' => $this->faker->numberBetween(0, 50),
            'views' => $this->faker->numberBetween(0, 100),
            'status' => $this->faker->randomElement(['Active', 'Inactive']),
        ];
        /**
                return [
                    'name' => $this->faker->word,
             'category_id' => function () {
                        return \App\Models\Category::factory()->create()->id;
                    },
                    'condition' => $this->faker->randomElement(['New', 'Fairly Used', 'N/A']),
                    'price' => $this->faker->randomFloat(2, 10, 1000),
                    'address' => $this->faker->address,
                    'phone_number' => $this->faker->phoneNumber,
                    'description' => $this->faker->sentence,
                    'type' => $this->faker->word,
                    'ratings' => $this->faker->randomFloat(1, 1, 5),
                    'quantity' => $this->faker->numberBetween(1, 100),
                    'sold' => $this->faker->numberBetween(0, 50),
                    'views' => $this->faker->numberBetween(0, 100),
                    'status' => $this->faker->randomElement(['Active', 'Inactive']),
                    //'specifications' => function () {
                    //    return \App\Models\Specification::factory()->create()->id;
                    //},
         ];
         */
    }

    /**
     * Indicate that the product has specifications.
     *
     * @return ProductFactory
     */
    public function withSpecifications(): self
    {
        return $this->afterCreating(
            function (Product $product) {
                $product->specifications()->attach(
                    \App\Models\Specification::factory()->create()->id
                );
            }
        );
    }
}
