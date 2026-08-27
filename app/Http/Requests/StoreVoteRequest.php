<?php

namespace App\Http\Requests;

use App\Models\Recipe;
use App\Models\Vote;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class StoreVoteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'recipe_id' => ['required', 'integer', 'exists:recipes,id'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $userId = (int) Auth::id();

            /** @var Recipe|null $recipe */
            $recipe = Recipe::query()->find($this->integer('recipe_id'));

            if ($recipe === null || ! $recipe->submitted_on->isToday()) {
                $validator->errors()->add(
                    'recipe_id',
                    __('You can only vote on recipes shared today.')
                );

                return;
            }

            $hasSubmittedToday = Recipe::query()
                ->where('user_id', $userId)
                ->submittedToday()
                ->exists();

            if (! $hasSubmittedToday) {
                $validator->errors()->add(
                    'recipe_id',
                    __('Share your own recipe before voting.')
                );

                return;
            }

            $alreadyVoted = Vote::query()
                ->where('user_id', $userId)
                ->where('recipe_id', $recipe->id)
                ->castToday()
                ->exists();

            if ($alreadyVoted) {
                $validator->errors()->add(
                    'recipe_id',
                    __('You already voted for this recipe today.')
                );

                return;
            }

            $votesCastToday = Vote::query()
                ->where('user_id', $userId)
                ->castToday()
                ->count();

            if ($votesCastToday >= Vote::DAILY_LIMIT) {
                $validator->errors()->add(
                    'recipe_id',
                    __('You have used all :count votes for today.', ['count' => Vote::DAILY_LIMIT])
                );
            }
        });
    }
}
