<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use App\Models\Vote;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchiveTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('archive.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_past_days_are_listed_with_counts()
    {
        $user = User::factory()->create();

        $yesterday = today()->subDay();
        $recipe = Recipe::factory()->for($user)->create(['submitted_on' => $yesterday]);
        Vote::factory()->for($recipe)->create(['voted_on' => $yesterday]);

        Recipe::factory()->count(2)->create(['submitted_on' => today()->subDays(2)]);

        Recipe::factory()->for($user)->create();

        $response = $this
            ->actingAs($user)
            ->get(route('archive.index'));

        $response->assertOk();

        $days = $response->viewData('page')['props']['days'];

        $this->assertSame(2, count($days));
        $this->assertSame($yesterday->toDateString(), $days[0]['date']);
        $this->assertSame(1, $days[0]['recipesCount']);
        $this->assertSame(1, $days[0]['votesCount']);
    }

    public function test_a_past_day_can_be_viewed_with_its_recipes()
    {
        $user = User::factory()->create();

        $yesterday = today()->subDay();
        $recipe = Recipe::factory()->for($user)->create(['submitted_on' => $yesterday]);
        Vote::factory()->count(2)->for($recipe)->create(['voted_on' => $yesterday]);

        $response = $this
            ->actingAs($user)
            ->get(route('archive.show', ['date' => $yesterday->toDateString()]));

        $response->assertOk();

        $recipes = $response->viewData('page')['props']['recipes'];

        $this->assertSame(1, count($recipes));
        $this->assertSame($recipe->id, $recipes[0]['id']);
        $this->assertSame(2, $recipes[0]['votesCount']);
    }

    public function test_today_is_not_part_of_the_archive()
    {
        $user = User::factory()->has(Recipe::factory())->create();

        $response = $this
            ->actingAs($user)
            ->get(route('archive.show', ['date' => today()->toDateString()]));

        $response->assertNotFound();
    }

    public function test_unknown_dates_are_not_found()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('archive.show', ['date' => today()->addYears(5)->toDateString()]));

        $response->assertNotFound();
    }
}
