<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieStaff extends Model
{
    use HasFactory;

    protected $table = 'movie_actors';

    protected $fillable = ['job', 'person_id', 'movie_id'];

}
