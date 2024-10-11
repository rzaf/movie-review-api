<?php

namespace App\Http\Requests\users;

use Illuminate\Foundation\Http\FormRequest;

class DestroyUser extends FormRequest
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
}
