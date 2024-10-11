<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieTag extends Model
{
    /** @use HasFactory<\Database\Factories\MovieTagFactory> */
    use HasFactory;

    protected $table = 'movie_tags';
    protected $fillable = ['movie_id','tag_id'];
}
