<?php

namespace App\Http\Requests\medias;

use App\Models\Genre;
use App\Models\Media;
use Illuminate\Foundation\Http\FormRequest;

class RemoveGenre extends FormRequest
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

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();
        $media = Media::where(['url' => $this->route('media_url')])->first('id');
        abort_if($media == null, 404, 'media not found');
        $validated['media_id'] = $media->id;
        $genre = Genre::createOrFirst(['name' => $this->route('name')]);
        abort_if($genre == null, 404, 'genre not found');
        $validated['genre_id'] = $genre->id;
        return $validated;
    }
}