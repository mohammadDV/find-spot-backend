<?php

namespace Database\Seeders;

use Domain\Address\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Turkey country
        Country::create([
            'id' => 1,
            'title' => 'ترکیه',
            'image' => null,
            'status' => 1,
            'created_at' => '2023-11-07 02:06:39',
            'updated_at' => '2023-11-07 02:06:39',
        ]);
    }
}