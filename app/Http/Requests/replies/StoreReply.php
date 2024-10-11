<?php

namespace App\Http\Requests\replies;

use App\Models\Reply;
use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreReply extends FormRequest
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
            'text' => 'required|string|max:250',
            'review_id' => 'sometimes|int|min:1',
            'reply_id' => 'sometimes|int|min:1',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $arr = parent::validated();
        $arr['user_id'] = auth()->user()->id;
        if (!isset($arr['review_id']) && !isset($arr['reply_id'])) {
            throw (ValidationException::withMessages(['review_id or reply_id are missing']));
        }
        if (isset($arr['review_id']) && isset($arr['reply_id'])) {
            throw (ValidationException::withMessages(['one of review_id and reply_id should be present']));
        }
        if (isset($arr['reply_id'])) {
            $reply = Reply::find($arr['reply_id']);
            abort_if($reply == null, 404, 'reply_id not found');
        }
        if (isset($arr['review_id'])) {
            $review = Review::find($arr['review_id']);
            abort_if($review == null, 404, 'review_id not found');
        }
        return $arr;
    }

}
