<?php

namespace App\Http\Requests\movies;

use App\Models\Movie;
use Illuminate\Foundation\Http\FormRequest;

class StoreMovieLike extends FormRequest
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
        $movie = Movie::where(['url' => $this->route('movie_url')])->first(['id']);
        abort_if($movie == null, 404, 'movie not found');
        $validated['likeable_id'] = $movie->id;
        $validated['likeable_type'] = Movie::class;
        $validated['user_id'] = auth()->user()->id;
        return $validated;
    }

}
