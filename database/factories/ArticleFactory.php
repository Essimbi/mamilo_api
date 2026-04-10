<?php

namespace Database\Factories;

use App\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence();
        return [
            'title' => $title,
            'slug' => \Illuminate\Support\Str::slug($title),
            'excerpt' => $this->faker->paragraph(),
            'status' => 'published',
            'published_at' => now(),
            'author_id' => \App\Models\User::factory(),
            'reading_time' => $this->faker->numberBetween(1, 15),
            'likes_count' => $this->faker->numberBetween(0, 100),
        ];
    }
}
