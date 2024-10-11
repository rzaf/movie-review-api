<?php

namespace App\Http\Requests\movies;

use Illuminate\Foundation\Http\FormRequest;

class DestroyMovie extends FormRequest
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
