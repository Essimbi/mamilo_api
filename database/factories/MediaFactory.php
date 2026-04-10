<?php

namespace Database\Factories;

use App\Models\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Media>
 */
class MediaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'file_name' => $this->faker->word() . '.jpg',
            'mime_type' => 'image/jpeg',
            'disk' => 'public',
            'size' => $this->faker->numberBetween(1000, 1000000),
            'manipulations' => [],
            'custom_properties' => [],
            'generated_conversions' => [],
            'responsive_images' => [],
            'collection_name' => 'default',
            'model_type' => 'App\Models\Article',
            'model_id' => \Illuminate\Support\Str::uuid(),
            'width' => 800,
            'height' => 600,
            'alt_text' => $this->faker->sentence(),
            'caption' => $this->faker->sentence(),
            'path' => 'uploads/' . \Illuminate\Support\Str::random(10) . '.jpg',
            'thumbnail_path' => 'uploads/thumbnails/' . \Illuminate\Support\Str::random(10) . '.jpg',
        ];
    }
}
