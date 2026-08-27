<?php

namespace App\Http\Resources;

use App\Models\Recipe;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read int $id
 * @property-read string $title
 * @property-read string|null $url
 * @property-read string|null $content
 * @property-read int $votes_count
 * @property-read bool $voted_by_me
 *
 * @mixin Recipe
 */
class RecipeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'url' => $this->url,
            'content' => $this->content,
            'author' => $this->user->name,
            'votesCount' => (int) $this->votes_count,
            'votedByMe' => (bool) $this->voted_by_me,
            'isMine' => $this->user_id === $request->user()?->id,
        ];
    }
}
