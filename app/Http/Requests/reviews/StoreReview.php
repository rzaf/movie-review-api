<?php

namespace App\Http\Requests\reviews;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;

class StoreReview extends FormRequest
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
            'review' => 'required|string|max:500',
            'score' => 'required|int|max:100|min:0',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $arr = parent::validated();
        $arr['user_id'] = auth()->user()->id;
        $media = Media::where(['url' => $this->route('media_url')])->first('id');
        abort_if($media == null, 404, 'media not found');
        $arr['media_id'] = $media->id;
        return $arr;
    }

}
