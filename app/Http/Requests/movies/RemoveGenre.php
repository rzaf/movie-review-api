<?php

namespace App\Http\Requests\movies;

use App\Models\Genre;
use App\Models\Movie;
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
        $movie = Movie::where(['url' => $this->route('movie_url')])->first('id');
        abort_if($movie == null, 404, 'movie not found');
        $validated['movie_id'] = $movie->id;
        $genre = Genre::createOrFirst(['name' => $this->route('name')]);
        abort_if($genre == null, 404, 'genre not found');
        $validated['genre_id'] = $genre->id;
        return $validated;
    }
}
