<?php

namespace App\Http\Requests\medias;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class StoreMedia extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (auth()->user()->isAdmin()) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'url' => 'required|string',
            'release_date' => 'required|date|max:2020',
            'category_name' => 'required|string',
            'summary' => 'sometimes|string|max:256',
            'storyline' => 'sometimes|string|max:2048',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();
        $cat = Category::where(['name' => $validated['category_name']])->first(['id']);
        abort_if($cat == null, 404, 'category not found');
        $validated['category_id'] = $cat->id;
        unset($validated['category_name']);
        return $validated;
    }
}
