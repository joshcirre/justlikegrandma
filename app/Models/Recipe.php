<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Database\Factories\RecipeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $url
 * @property string|null $content
 * @property Carbon $submitted_on
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['title', 'url', 'content', 'submitted_on'])]
class Recipe extends Model
{
    /** @use HasFactory<RecipeFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submitted_on' => 'date',
        ];
    }

    /**
     * Get the user that submitted the recipe.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the votes cast for the recipe.
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Scope the query to recipes submitted on the given date.
     */
    public function scopeSubmittedOn(Builder $query, CarbonInterface $date): void
    {
        $query->whereDate('submitted_on', $date->toDateString());
    }

    /**
     * Scope the query to recipes submitted today.
     */
    public function scopeSubmittedToday(Builder $query): void
    {
        $query->submittedOn(today());
    }
}
