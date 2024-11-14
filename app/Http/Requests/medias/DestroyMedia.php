<?php

namespace App\Http\Requests\medias;

use Illuminate\Foundation\Http\FormRequest;

class DestroyMedia extends FormRequest
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
}
