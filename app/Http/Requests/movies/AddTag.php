<?php

namespace App\Http\Requests\movies;

use App\Models\Movie;
use App\Models\Tag;
use Illuminate\Foundation\Http\FormRequest;

class AddTag extends FormRequest
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

    public function all($keys = null)
	{
	  $request = parent::all($keys);

	  $request['name'] = $this->route('name');

	  return $request;
	}

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();
        $movie = Movie::where(['url' => $this->route('movie_url')])->first('id');
        abort_if($movie == null, 404, 'movie not found');
        $validated['movie_id'] = $movie->id;
        $tag = Tag::createOrFirst(['name' => $this->route('name')]);
        $validated['tag_id'] = $tag->id;
        return $validated;
    }

}
