<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\VoteFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $recipe_id
 * @property Carbon $voted_on
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['recipe_id', 'voted_on'])]
class Vote extends Model
{
    /** @use HasFactory<VoteFactory> */
    use HasFactory;

    /**
     * The number of votes each user can cast per day.
     */
    public const int DAILY_LIMIT = 3;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'voted_on' => 'date',
        ];
    }

    /**
     * Get the user that cast the vote.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the recipe the vote was cast for.
     */
    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /**
     * Scope the query to votes cast on the given date.
     */
    public function scopeCastOn(Builder $query, CarbonInterface $date): void
    {
        $query->whereDate('voted_on', $date->toDateString());
    }

    /**
     * Scope the query to votes cast today.
     */
    public function scopeCastToday(Builder $query): void
    {
        $query->castOn(today());
    }
}
