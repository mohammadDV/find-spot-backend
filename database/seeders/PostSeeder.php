<?php

namespace Database\Seeders;

use Domain\Post\Models\Post;
use Domain\User\Models\User;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a user for posts
        $user = User::firstOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'first_name' => 'مدیر',
                'last_name' => 'سیستم',
                'nickname' => 'admin',
                'customer_number' => 'ADMIN001',
                'role_id' => 1,
                'status' => 1,
                'mobile' => '09120000000',
                'password' => bcrypt('123456789'),
                'level' => 0,
            ]
        );

        // Create 10 Persian posts
        Post::factory(10)
            ->active()
            ->create([
                'user_id' => $user->id,
            ]);

        // Create some special posts for slider
        Post::factory(3)
            ->active()
            ->special()
            ->create([
                'user_id' => $user->id,
            ]);

        // Create some video posts
        Post::factory(2)
            ->active()
            ->video()
            ->create([
                'user_id' => $user->id,
            ]);

        $this->command->info('✅ 15 Persian posts created successfully!');
        $this->command->info('📝 10 regular posts');
        $this->command->info('🖼️ 3 special posts (slider)');
        $this->command->info('🎥 2 video posts');
    }
}
