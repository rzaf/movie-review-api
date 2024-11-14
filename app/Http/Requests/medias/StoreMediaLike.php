<?php

namespace App\Http\Requests\medias;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;

class StoreMediaLike extends FormRequest
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
            'is_liked' => 'required|bool'
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();
        $media = Media::where(['url' => $this->route('media_url')])->first(['id']);
        abort_if($media == null, 404, 'media not found');
        $validated['likeable_id'] = $media->id;
        $validated['likeable_type'] = Media::class;
        $validated['user_id'] = auth()->user()->id;
        return $validated;
    }

}
