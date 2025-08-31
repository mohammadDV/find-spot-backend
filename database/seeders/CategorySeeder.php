<?php

namespace Database\Seeders;

use Domain\Business\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create root categories
        $restaurant = Category::firstOrCreate(
            ['title' => 'Restaurant'],
            [
                'status' => 1,
                'priority' => 1,
                'parent_id' => 0,
            ]
        );

        $hotel = Category::firstOrCreate(
            ['title' => 'Hotel'],
            [
                'status' => 1,
                'priority' => 2,
                'parent_id' => 0,
            ]
        );

        $shopping = Category::firstOrCreate(
            ['title' => 'Shopping'],
            [
                'status' => 1,
                'priority' => 3,
                'parent_id' => 0,
            ]
        );

        // Create child categories for Restaurant
        Category::firstOrCreate(
            ['title' => 'Italian Restaurant'],
            [
                'status' => 1,
                'priority' => 1,
                'parent_id' => $restaurant->id,
            ]
        );

        Category::firstOrCreate(
            ['title' => 'Persian Restaurant'],
            [
                'status' => 1,
                'priority' => 2,
                'parent_id' => $restaurant->id,
            ]
        );

        Category::firstOrCreate(
            ['title' => 'Fast Food'],
            [
                'status' => 1,
                'priority' => 3,
                'parent_id' => $restaurant->id,
            ]
        );

        // Create child categories for Hotel
        Category::firstOrCreate(
            ['title' => '5-Star Hotel'],
            [
                'status' => 1,
                'priority' => 1,
                'parent_id' => $hotel->id,
            ]
        );

        Category::firstOrCreate(
            ['title' => 'Boutique Hotel'],
            [
                'status' => 1,
                'priority' => 2,
                'parent_id' => $hotel->id,
            ]
        );

        // Create child categories for Shopping
        Category::firstOrCreate(
            ['title' => 'Clothing Store'],
            [
                'status' => 1,
                'priority' => 1,
                'parent_id' => $shopping->id,
            ]
        );

        Category::firstOrCreate(
            ['title' => 'Electronics Store'],
            [
                'status' => 1,
                'priority' => 2,
                'parent_id' => $shopping->id,
            ]
        );

        $this->command->info('Categories seeded successfully!');
    }
}
