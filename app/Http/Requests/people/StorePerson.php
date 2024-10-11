<?php

namespace App\Http\Requests\people;

use Illuminate\Foundation\Http\FormRequest;

class StorePerson extends FormRequest
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
            'name' => 'required|string',
            'is_male' => 'required|bool',
            'birth_date' => 'required|date',
        ];
    }
}
