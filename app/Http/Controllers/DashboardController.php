<?php

namespace App\Http\Controllers;

use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use App\Models\Vote;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show today's recipes and the current user's voting status.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $recipes = Recipe::query()
            ->submittedToday()
            ->with('user:id,name')
            ->withCount('votes')
            ->withExists(['votes as voted_by_me' => fn ($query) => $query->where('user_id', $user->id)])
            ->orderByDesc('votes_count')
            ->orderBy('created_at')
            ->get();

        $votesCastToday = Vote::query()
            ->where('user_id', $user->id)
            ->castToday()
            ->count();

        $myRecipe = $recipes->firstWhere('user_id', $user->id);

        return Inertia::render('dashboard', [
            'recipes' => RecipeResource::collection($recipes)->resolve(),
            'myRecipe' => $myRecipe === null ? null : new RecipeResource($myRecipe),
            'hasSubmittedToday' => $myRecipe !== null,
            'votesRemaining' => max(0, Vote::DAILY_LIMIT - $votesCastToday),
        ]);
    }
}
