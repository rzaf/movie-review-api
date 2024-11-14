<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaGenre extends Model
{
    /** @use HasFactory<\Database\Factories\MediaGenreFactory> */
    use HasFactory;

    protected $table = 'media_genres';
    protected $fillable = ['media_id','genre_id'];
}
