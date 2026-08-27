<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipeSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->post(route('recipes.store'), [
            'title' => 'Grandma\'s Apple Pie',
            'url' => 'https://example.com/apple-pie',
        ]);

        $response->assertRedirect(route('login'));
    }

    public function test_a_user_can_submit_a_recipe_with_a_link()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('recipes.store'), [
                'title' => 'Grandma\'s Apple Pie',
                'url' => 'https://example.com/apple-pie',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('dashboard'));

        $recipe = $user->recipes()->first();

        $this->assertNotNull($recipe);
        $this->assertSame('Grandma\'s Apple Pie', $recipe->title);
        $this->assertSame('https://example.com/apple-pie', $recipe->url);
        $this->assertTrue($recipe->submitted_on->isToday());
    }

    public function test_a_user_can_submit_a_recipe_as_text()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->post(route('recipes.store'), [
                'title' => 'Grandma\'s Chicken Soup',
                'content' => 'Simmer the chicken all day long.',
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('recipes', [
            'user_id' => $user->id,
            'title' => 'Grandma\'s Chicken Soup',
            'content' => 'Simmer the chicken all day long.',
            'url' => null,
        ]);
    }

    public function test_a_recipe_requires_a_title()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('recipes.store'), [
                'title' => '',
                'url' => 'https://example.com/apple-pie',
            ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_a_recipe_requires_either_a_link_or_text()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('recipes.store'), [
                'title' => 'Grandma\'s Apple Pie',
            ]);

        $response->assertSessionHasErrors(['url', 'content']);
    }

    public function test_a_recipe_link_must_be_a_valid_url()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('recipes.store'), [
                'title' => 'Grandma\'s Apple Pie',
                'url' => 'not-a-url',
            ]);

        $response->assertSessionHasErrors('url');
    }

    public function test_a_user_can_only_submit_one_recipe_per_day()
    {
        $user = User::factory()->create();

        Recipe::factory()->for($user)->create();

        $response = $this
            ->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('recipes.store'), [
                'title' => 'Second Recipe of the Day',
                'url' => 'https://example.com/seconds',
            ]);

        $response->assertSessionHasErrors('recipe');

        $this->assertSame(1, $user->recipes()->count());
    }

    public function test_a_user_can_submit_a_recipe_again_the_next_day()
    {
        $user = User::factory()->create();

        Recipe::factory()->for($user)->create();

        $this->travel(1)->day();

        $response = $this
            ->actingAs($user)
            ->post(route('recipes.store'), [
                'title' => 'Fresh Recipe for a New Day',
                'url' => 'https://example.com/fresh',
            ]);

        $response->assertSessionHasNoErrors();

        $this->assertSame(2, $user->recipes()->count());
    }
}
