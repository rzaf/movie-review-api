<?php

namespace App\Http\Requests\reviews;

use App\Models\Review;
use Illuminate\Foundation\Http\FormRequest;

class DestroyReviewLike extends FormRequest
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
        $id = $this->route('review_id');
        if (Review::find($id) == null) {
            abort(404,'review not found');
        }
        $validated['likeable_id'] = $id;
        $validated['likeable_type'] = Review::class;
        $validated['user_id'] = auth()->user()->id;
        return $validated;
    }
}
