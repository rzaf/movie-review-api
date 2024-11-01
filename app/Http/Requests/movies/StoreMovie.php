<?php

namespace App\Http\Requests\movies;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class StoreMovie extends FormRequest
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
            'release_year' => 'required|int|max:2020',
            'category_name' => 'required|string',
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
