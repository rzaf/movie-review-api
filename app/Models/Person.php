<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Person extends Model
{
    /** @use HasFactory<\Database\Factories\PersonFactory> */
    use HasFactory;

    protected $table = 'people';
    protected $fillable = ['name','is_male','birth_date'];
    // protected $hidden = ['pivot'];

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class,'movie_actors');
    }
    
    public function moviesWorkedIn(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class,'movie_actors')->withPivot('job');
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class,'followings','following_id','follower_id');
    }
}
