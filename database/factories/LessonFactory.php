<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lesson>
 */
class LessonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'   => $this->faker->sentence(4),
            'content' => $this->faker->paragraphs(3, true),
            'order'   => 1, // will be overridden in seeder to be sequential
            // 'course_id' will be set in seeder
        ];
    }
}
