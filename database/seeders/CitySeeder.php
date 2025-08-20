<?php

namespace Database\Seeders;

use Domain\Address\Models\City;
use Illuminate\Database\Seeder;

class CitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Istanbul city
        City::create([
            'id' => 1,
            'title' => 'استانبول',
            'status' => 1,
            'priority' => 1,
            'country_id' => 1, // References Turkey country
            'created_at' => '2023-11-07 02:06:39',
            'updated_at' => '2023-11-07 02:06:39',
        ]);
    }
}
