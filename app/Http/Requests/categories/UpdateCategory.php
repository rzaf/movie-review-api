<?php

namespace App\Http\Requests\categories;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCategory extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (auth()->user()->isAdmin()){
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
            'name' => 'required|string|max:16',
            'parent_name' => 'sometimes|string|max:16',
            'has_items' => 'sometimes|bool',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();
        if (isset($validated['parent_name'])) {
            $parent = Category::where(['name' => $validated['parent_name']])->first('id');
            abort_if($parent == null, 404, 'parent_name not found');
            $validated['parent_id'] = $parent->id;
            unset($validated['parent_name']);
        }
        return $validated;
    }

}
