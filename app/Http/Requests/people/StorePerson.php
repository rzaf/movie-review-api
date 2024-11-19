<?php

namespace App\Http\Requests\people;

use App\Models\Country;
use Illuminate\Foundation\Http\FormRequest;

class StorePerson extends FormRequest
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
            'name' => 'required|string',
            'url' => 'required|string',
            'is_male' => 'required|bool',
            'birth_date' => 'required|date',
            'about' => 'sometimes|string|max:1024',
            'country' => 'required|string',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();
        $country = Country::whereAny(['country_name', 'country_code'], '=', $validated['country'])->first();
        abort_if($country == null, 400, 'invalid country name or code');
        $validated['birth_country'] = $country->id;
        unset($validated['country']);
        return $validated;
    }
}
