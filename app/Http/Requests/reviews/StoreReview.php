<?php

namespace App\Http\Requests\reviews;

use App\Models\Movie;
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
        $movie = Movie::where(['url' => $this->route('movie_url')])->first('id');
        abort_if($movie == null, 404, 'movie not found');
        $arr['movie_id'] = $movie->id;
        return $arr;
    }

}
