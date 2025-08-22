<?php

namespace Database\Seeders;

use Domain\User\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a test user
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'first_name' => 'Test',
                'last_name' => 'User',
                'nickname' => 'testuser',
                'customer_number' => 'CUST001',
                'role_id' => 2,
                'status' => 1,
                'mobile' => '09123456789',
                'password' => bcrypt('password'),
                'level' => 0,
            ]
        );

        // Seed filters
        $this->call([
            RoleSeeder::class,
            CountrySeeder::class,
            CitySeeder::class,
            AreaSeeder::class,
            EventSeeder::class,
            PostSeeder::class,
        ]);
    }
}
