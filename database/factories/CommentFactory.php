<?php

namespace Database\Factories;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $article = \App\Models\Article::factory()->create();
        
        return [
            'commentable_type' => \App\Models\Article::class,
            'commentable_id' => $article->id,
            'author_name' => $this->faker->name(),
            'author_avatar' => $this->faker->imageUrl(100, 100, 'people'),
            'content' => $this->faker->paragraph(),
            'is_approved' => $this->faker->boolean(80),
        ];
    }
}
