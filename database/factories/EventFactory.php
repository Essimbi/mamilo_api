<?php

namespace Database\Factories;

use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
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
            'description' => $this->faker->paragraphs(3, true),
            'location' => $this->faker->city() . ', ' . $this->faker->country(),
            'event_date' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'type' => $this->faker->randomElement(['conference', 'seminar', 'workshop', 'webinar', 'forum']),
            'status' => 'upcoming',
            'likes_count' => $this->faker->numberBetween(0, 50),
        ];
    }
}
