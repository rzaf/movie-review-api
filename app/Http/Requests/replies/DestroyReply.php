<?php

namespace App\Http\Requests\replies;

use Illuminate\Foundation\Http\FormRequest;

class DestroyReply extends FormRequest
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
        $user = auth()->user();
        $arr['id'] = $this->route('reply_id');
        if ($user->role == 'normal') {
            $arr['user_id'] = $user->id;
        }
        return $arr;
    }
}
