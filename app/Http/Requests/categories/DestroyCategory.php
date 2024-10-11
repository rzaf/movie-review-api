<?php

namespace App\Http\Requests\categories;

use Illuminate\Foundation\Http\FormRequest;

class DestroyCategory extends FormRequest
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
