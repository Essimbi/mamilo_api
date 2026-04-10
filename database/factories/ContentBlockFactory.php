<?php

namespace Database\Factories;

use App\Models\ContentBlock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContentBlock>
 */
class ContentBlockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'article_id' => \App\Models\Article::factory(),
            'type' => $this->faker->randomElement(['paragraph', 'heading', 'image', 'quote']),
            'position' => $this->faker->numberBetween(0, 10),
            'content' => ['text' => $this->faker->paragraph()],
        ];
    }
}
