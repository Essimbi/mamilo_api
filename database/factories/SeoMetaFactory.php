<?php

namespace Database\Factories;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SeoMeta>
 */
class SeoMetaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'description' => $this->faker->sentence(),
            'keywords' => implode(',', $this->faker->words(5)),
            'og_title' => $this->faker->sentence(),
            'og_description' => $this->faker->sentence(),
            'og_image' => $this->faker->imageUrl(),
            'canonical_url' => $this->faker->url(),
        ];
    }
}
