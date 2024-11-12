<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieGenre extends Model
{
    /** @use HasFactory<\Database\Factories\MovieGenreFactory> */
    use HasFactory;

    protected $table = 'movie_genres';
    protected $fillable = ['movie_id','genre_id'];
}
