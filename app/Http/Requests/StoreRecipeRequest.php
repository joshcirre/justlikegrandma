<?php

namespace App\Http\Requests;

use App\Models\Recipe;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Validator;

class StoreRecipeRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'required_without:content', 'string', 'max:2048', 'url:http,https'],
            'content' => ['nullable', 'required_without:url', 'string', 'max:10000'],
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

            $alreadySubmitted = Recipe::query()
                ->where('user_id', (int) Auth::id())
                ->submittedToday()
                ->exists();

            if ($alreadySubmitted) {
                $validator->errors()->add(
                    'recipe',
                    __('You have already shared a recipe today. Come back tomorrow!')
                );
            }
        });
    }
}
