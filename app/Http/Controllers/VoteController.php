<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVoteRequest;
use App\Models\Vote;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class VoteController extends Controller
{
    /**
     * Cast a vote on today's recipe for the authenticated user.
     */
    public function store(StoreVoteRequest $request): RedirectResponse
    {
        $request->user()->votes()->create([
            'recipe_id' => $request->integer('recipe_id'),
            'voted_on' => today(),
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Your vote has been counted.')]);

        return back();
    }
}
