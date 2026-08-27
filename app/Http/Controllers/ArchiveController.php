<?php

namespace App\Http\Controllers;

use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use App\Models\Vote;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class ArchiveController extends Controller
{
    /**
     * List the days that have already passed.
     */
    public function index(): Response
    {
        $recipeCounts = Recipe::query()
            ->whereDate('submitted_on', '<', today()->toDateString())
            ->groupBy('submitted_on')
            ->selectRaw('submitted_on, count(*) as recipes_count')
            ->orderByDesc('submitted_on')
            ->get()
            ->mapWithKeys(fn (Recipe $recipe): array => [
                $recipe->submitted_on->toDateString() => (int) $recipe->recipes_count,
            ]);

        $voteCounts = Vote::query()
            ->whereDate('voted_on', '<', today()->toDateString())
            ->groupBy('voted_on')
            ->selectRaw('voted_on, count(*) as votes_count')
            ->get()
            ->mapWithKeys(fn (Vote $vote): array => [
                $vote->voted_on->toDateString() => (int) $vote->votes_count,
            ]);

        $days = $recipeCounts
            ->map(fn (int $recipesCount, string $date): array => [
                'date' => $date,
                'recipesCount' => $recipesCount,
                'votesCount' => $voteCounts->get($date, 0),
            ])
            ->values();

        return Inertia::render('archive/index', [
            'days' => $days,
        ]);
    }

    /**
     * Show the recipes and results for a past day.
     */
    public function show(string $date): Response
    {
        abort_unless(Carbon::canBeCreatedFromFormat($date, 'Y-m-d'), 404);

        $day = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();

        abort_unless($day->isBefore(today()), 404);

        $recipes = Recipe::query()
            ->submittedOn($day)
            ->with('user:id,name')
            ->withCount('votes')
            ->orderByDesc('votes_count')
            ->orderBy('created_at')
            ->get();

        abort_if($recipes->isEmpty(), 404);

        return Inertia::render('archive/show', [
            'date' => $day->toDateString(),
            'recipes' => RecipeResource::collection($recipes)->resolve(),
        ]);
    }
}
