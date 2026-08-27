<?php

namespace Database\Factories;

use App\Models\Recipe;
use App\Models\User;
use App\Models\Vote;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Vote>
 */
class VoteFactory extends Factory
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
            'recipe_id' => Recipe::factory(),
            'voted_on' => today(),
        ];
    }

    /**
     * Indicate the vote was cast on the given date.
     */
    public function onDate(CarbonInterface|string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'voted_on' => Carbon::parse($date)->toDateString(),
        ]);
    }
}
