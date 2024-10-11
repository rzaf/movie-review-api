<?php

namespace App\Http\Requests\replies;

use App\Models\Reply;
use Illuminate\Foundation\Http\FormRequest;

class DestroyReplyLike extends FormRequest
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
        $validated = parent::validated();
        $reply = Reply::find($this->route('reply_id'));
        abort_if($reply == null, 404, 'reply not found');
        $validated['likeable_id'] = $this->route('reply_id');
        $validated['likeable_type'] = Reply::class;
        $validated['user_id'] = auth()->user()->id;
        return $validated;
    }
}
