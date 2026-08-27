<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Recipe>
 */
class RecipeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->words(3, true),
            'url' => fake()->url(),
            'content' => null,
            'submitted_on' => today(),
        ];
    }

    /**
     * Indicate the recipe is shared as plain text instead of a link.
     */
    public function text(): static
    {
        return $this->state(fn (array $attributes) => [
            'url' => null,
            'content' => fake()->paragraphs(3, true),
        ]);
    }

    /**
     * Indicate the recipe was submitted on the given date.
     */
    public function onDate(CarbonInterface|string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'submitted_on' => Carbon::parse($date)->toDateString(),
        ]);
    }

    /**
     * Indicate the recipe was submitted on a previous day.
     */
    public function past(): static
    {
        return $this->state(fn (array $attributes) => [
            'submitted_on' => today()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }
}
