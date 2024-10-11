<?php

namespace App\Http\Requests\people;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePerson extends FormRequest
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
            // 'id' => 'required|int',
            'name' => 'required|string',
            'birth_date' => 'sometimes|date',
        ];
    }

    // public function validated($key = null, $default = null): array
    // {
    //     $validated = self::validated();
    //     $validated['name'] = $validated['new_name'];
    //     $validated['birth_date'] = $validated['new_birth_date'];
    //     return $validated;
    // }
}
