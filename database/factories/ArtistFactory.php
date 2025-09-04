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
            'owner_id' => User::factory(),
            'spotify_id' => $this->faker->optional(0.7)->regexify('[A-Za-z0-9]{22}'),
            'name' => $this->faker->name(),
            'popularity' => $this->faker->optional(0.8)->numberBetween(0, 100),
            'followers_count' => $this->faker->optional(0.6)->numberBetween(100, 10000000),
        ];
    }

    /**
     * Create an artist with high popularity
     */
    public function popular(): static
    {
        return $this->state(fn (array $attributes) => [
            'popularity' => $this->faker->numberBetween(70, 100),
            'followers_count' => $this->faker->numberBetween(1000000, 50000000),
        ]);
    }

    /**
     * Create an artist with low popularity
     */
    public function unpopular(): static
    {
        return $this->state(fn (array $attributes) => [
            'popularity' => $this->faker->numberBetween(0, 30),
            'followers_count' => $this->faker->numberBetween(100, 10000),
        ]);
    }

    /**
     * Create an artist with no Spotify data
     */
    public function withoutSpotifyData(): static
    {
        return $this->state(fn (array $attributes) => [
            'spotify_id' => null,
            'popularity' => null,
            'followers_count' => null,
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
