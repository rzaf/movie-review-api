<?php

namespace App\Http\Requests\medias;

use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddPerson extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (auth()->user()->isAdmin()) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'job' => ['required', 'string', Rule::in(['director', 'producer', 'writer', 'actor', 'music'])],
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();
        $media = Media::where(['url' => $this->route('media_url')])->first('id');
        abort_if($media == null, 404, 'media not found');
        $validated['media_id'] = $media->id;
        $validated['person_id'] = $this->route('person_id');
        return $validated;
    }

}
