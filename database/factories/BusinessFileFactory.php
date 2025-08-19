<?php

namespace Database\Factories;

use Domain\Business\Models\Business;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\Domain\Business\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileTypes = ['image', 'video', 'document'];
        $fileType = fake()->randomElement($fileTypes);


        return [
            'business_id' => Business::factory(),
            'path' => 'businesses/' . fake()->uuid,
            'type' => $fileType,
            'status' => fake()->randomElement([0, 1]),
        ];
    }

    /**
     * Indicate that the file is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 1,
        ]);
    }

    /**
     * Indicate that the file is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 0,
        ]);
    }
}
