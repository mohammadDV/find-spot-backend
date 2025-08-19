<?php

namespace Database\Seeders;

use Domain\Business\Models\Filter;
use Illuminate\Database\Seeder;

class FilterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $filters = [
            'Fast Service',
            '24/7 Available',
            'Free Delivery',
            'Premium Quality',
            'Budget Friendly',
            'Eco-Friendly',
            'Certified',
            'Award Winning',
            'Family Owned',
            'Local Business',
            'Online Booking',
            'Mobile App',
            'Parking Available',
            'Wheelchair Accessible',
            'Pet Friendly',
            'Smoke Free',
            'WiFi Available',
            'Credit Card Accepted',
            'Cash Only',
            'Insurance Accepted'
        ];

        foreach ($filters as $filterTitle) {
            Filter::create([
                'title' => $filterTitle,
                'status' => 1, // Active by default
            ]);
        }
    }
}
