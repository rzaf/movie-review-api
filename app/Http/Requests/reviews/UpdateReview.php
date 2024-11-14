<?php

namespace App\Http\Requests\reviews;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;

class UpdateReview extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'review' => 'required|string|max:500',
            'score' => 'required|int|max:100|min:0',
        ];
    }

}
