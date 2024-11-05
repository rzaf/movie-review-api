<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Person extends Model
{
    /** @use HasFactory<\Database\Factories\PersonFactory> */
    use HasFactory;
    use Filterable;
    
    protected $table = 'people';
    protected $fillable = ['name', 'is_male', 'birth_date'];
    // protected $hidden = ['pivot'];

    public function movies(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'movie_actors');
    }

    public function moviesWorkedIn(): BelongsToMany
    {
        return $this->belongsToMany(Movie::class, 'movie_actors')->withPivot('job');
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followings', 'following_id', 'follower_id');
    }

    public function scopeSortBy(Builder $query, ?string $sortType): void
    {
        $sortType ??= '';
        $order = 'created_at';
        $dir = 'desc';
        switch ($sortType) {
            case '':
                break;
            case 'newest-created':
                $order = 'created_at';
                $dir = 'desc';
                $query->orderBy($order, $dir)->orderBy('id','asc');
                return;
            case 'oldest-created':
                $order = 'created_at';
                $dir = 'asc';
                $query->orderBy($order, $dir)->orderBy('id','desc');
                return;
            case 'youngest':
                $order = 'birth_date';
                $dir = 'desc';
                break;
            case 'oldest':
                $order = 'birth_date';
                $dir = 'asc';
                break;
            case 'most-followers':
                $order = 'followers_count';
                $dir = 'desc';
                break;
            case 'least-followers':
                $order = 'followers_count';
                $dir = 'asc';
                break;
            case 'most-movies':
                $order = 'movies_count';
                $dir = 'desc';
                break;
            case 'least-movies':
                $order = 'movies_count';
                $dir = 'asc';
                break;
            default:
                abort(400, 'invalid sort type:' . $sortType . '. valid sort types are:newest-created,oldest-created,youngest,oldest,most-followers,least-followers,most-movies,least-movies');
        }
        $query->orderBy($order, $dir);
    }
}
