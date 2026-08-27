<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VotingTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $recipe = Recipe::factory()->create();

        $response = $this->post(route('votes.store'), [
            'recipe_id' => $recipe->id,
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_a_user_who_submitted_can_vote_on_todays_recipe()
    {
        $user = User::factory()->has(Recipe::factory())->create();
        $recipe = Recipe::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('votes.store'), [
                'recipe_id' => $recipe->id,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $vote = $user->votes()->first();

        $this->assertNotNull($vote);
        $this->assertSame($recipe->id, $vote->recipe_id);
        $this->assertTrue($vote->voted_on->isToday());
    }

    public function test_a_user_who_has_not_submitted_a_recipe_cannot_vote()
    {
        $user = User::factory()->create();
        $recipe = Recipe::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('votes.store'), [
                'recipe_id' => $recipe->id,
            ]);

        $response->assertSessionHasErrors('recipe_id');

        $this->assertSame(0, Vote::count());
    }

    public function test_a_user_only_gets_three_votes_per_day()
    {
        $user = User::factory()->has(Recipe::factory())->create();
        $recipes = Recipe::factory()->count(4)->create();

        foreach ($recipes->take(3) as $recipe) {
            $this
                ->actingAs($user)
                ->post(route('votes.store'), [
                    'recipe_id' => $recipe->id,
                ])
                ->assertSessionHasNoErrors();
        }

        $response = $this
            ->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('votes.store'), [
                'recipe_id' => $recipes->last()->id,
            ]);

        $response->assertSessionHasErrors('recipe_id');

        $this->assertSame(3, $user->votes()->count());
    }

    public function test_a_user_cannot_vote_for_the_same_recipe_twice()
    {
        $user = User::factory()->has(Recipe::factory())->create();
        $recipe = Recipe::factory()->create();

        Vote::factory()->for($user)->for($recipe)->create();

        $response = $this
            ->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('votes.store'), [
                'recipe_id' => $recipe->id,
            ]);

        $response->assertSessionHasErrors('recipe_id');

        $this->assertSame(1, $user->votes()->count());
    }

    public function test_a_user_cannot_vote_on_a_recipe_from_a_past_day()
    {
        $user = User::factory()->has(Recipe::factory())->create();
        $yesterdaysRecipe = Recipe::factory()->past()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('votes.store'), [
                'recipe_id' => $yesterdaysRecipe->id,
            ]);

        $response->assertSessionHasErrors('recipe_id');

        $this->assertSame(0, Vote::count());
    }

    public function test_votes_are_reset_the_next_day()
    {
        $user = User::factory()->create();
        $yesterday = today()->subDay();

        Recipe::factory()->for($user)->create(['submitted_on' => $yesterday]);
        $yesterdaysRecipes = Recipe::factory()->count(3)->create(['submitted_on' => $yesterday]);

        foreach ($yesterdaysRecipes as $recipe) {
            Vote::factory()->for($user)->for($recipe)->create(['voted_on' => $yesterday]);
        }

        $this->assertSame(3, $user->votes()->count());

        $this->travel(1)->day();

        Recipe::factory()->for($user)->create();
        $todaysRecipe = Recipe::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('votes.store'), [
                'recipe_id' => $todaysRecipe->id,
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertSame(1, $user->votes()->castToday()->count());
    }
}
