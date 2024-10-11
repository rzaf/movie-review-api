<?php

namespace App\Http\Requests\movies;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMovie extends FormRequest
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
            'url' => 'sometimes|string',
            'release_year' => 'sometimes|int|max:2020',
            'category_name' => 'sometimes|string',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();
        if (isset($validated['category_name'])){
            $cat = Category::where(['name' => $validated['category_name']])->first(['id']);
            abort_if($cat == null, 404, 'category not found');
            $validated['category_id'] = $cat->id;
            unset($validated['category_name']);
        }
        return $validated;
    }
}
