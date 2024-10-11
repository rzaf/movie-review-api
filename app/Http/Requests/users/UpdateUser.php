<?php

namespace App\Http\Requests\users;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUser extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->user()->username != $this->route('username')) {
            abort(403, 'not allowed to update user:`'.$this->route('username').'`');
        }
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
            'new_username' => 'required|string',
            'new_name' => 'sometimes|string',
            'new_email' => 'sometimes|string',
            'new_password' => 'sometimes|string|confirmed',
            'new_password_confirmation' => 'sometimes|string',
        ];
    }
    
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();
        $validated['username'] = $validated['new_username'];
        unset($validated['new_username']);
        if (isset($validated['new_name'])) {
            $validated['name'] = $validated['new_name'];
            unset($validated['new_name']);
        }
        if (isset($validated['new_email'])) {
            $validated['email'] = $validated['new_email'];
            unset($validated['new_email']);
        }
        if (isset($validated['new_password'])) {
            $validated['password'] = $validated['new_password'];
            unset($validated['new_password']);
        }
        return $validated;
    }
}
