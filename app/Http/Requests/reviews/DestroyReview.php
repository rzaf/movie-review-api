<?php

namespace App\Http\Requests\reviews;

use Illuminate\Foundation\Http\FormRequest;

class DestroyReview extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function validated($key = null, $default = null): array
    {
        $arr = parent::validated();
        $arr['id'] = $this->route('review_id');
        $user = auth()->user();
        if ($user->role == 'normal') {
            $arr['user_id'] = $user->id;
        }
        // $arr['user_id'] = $user->id;
        return $arr;
    }

}
