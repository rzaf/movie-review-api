<?php

namespace App\Http\Requests\movies;

use App\Models\Movie;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddPerson extends FormRequest
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

	  $request['person_id'] = $this->route('person_id');

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
            'job' => ['required', 'string', Rule::in(['director', 'producer', 'writer', 'actor', 'music'])],
            'person_id' => 'required|int',
        ];
    }

    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated();
        $movie = Movie::where(['url' => $this->route('movie_url')])->first('id');
        abort_if($movie == null, 404, 'movie not found');
        $validated['movie_id'] = $movie->id;
        return $validated;
    }

}
