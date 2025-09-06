<?php

namespace Database\Factories;

use App\Models\Artist;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Artist>
 */
class ArtistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->regularUser()->create(),
            'spotify_id' => $this->faker->optional(0.7)->regexify('[A-Za-z0-9]{22}'),
            'name' => $this->faker->name(),
        ];
    }

    /**
     * Create an artist with no Spotify data
     */
    public function withoutSpotifyData(): static
    {
        return $this->state(fn (array $attributes) => [
            'spotify_id' => null,
        ]);
    }

    /**
     * Create an artist for a specific owner
     */
    public function forOwner(User $owner): static
    {
        return $this->state(fn (array $attributes) => [
            'owner_id' => $owner->id,
        ]);
    }
}
