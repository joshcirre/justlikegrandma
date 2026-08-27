<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecipeRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class RecipeController extends Controller
{
    /**
     * Store today's recipe for the authenticated user.
     */
    public function store(StoreRecipeRequest $request): RedirectResponse
    {
        $request->user()->recipes()->create([
            ...$request->validated(),
            'submitted_on' => today(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Your recipe has been shared for today.')]);

        return to_route('dashboard');
    }
}
