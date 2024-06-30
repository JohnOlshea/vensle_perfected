<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Specification;

class SpecificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Specification::create(['name' => 'Touchscreen']);
        Specification::create(['name' => 'Wireless']);
        Specification::create(['name' => 'Waterproof']);
    }
}
