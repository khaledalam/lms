<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->sentence(3);

        return [
            'title'       => $this->faker->unique()->sentence(3),
            'slug'        => Str::slug($title),
            'description' => $this->faker->paragraph(),
            'published'   => $this->faker->boolean(70),
            // 'instructor_id' will be set explicitly in seeder
        ];
    }
}
