<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovieStaff extends Model
{
    use HasFactory;
    use Filterable;

    protected $table = 'movie_actors';

    protected $fillable = ['job', 'person_id', 'movie_id'];


    public function scopeSortBy(Builder $query, ?string $sortType)
    {
        $sortType ??= '';
        $order = 'movies.created_at';
        $dir = 'desc';
        switch ($sortType) {
            case '':
                break;
            case 'newest':
                $order = 'movies.created_at';
                $dir = 'desc';
                $query->orderBy($order, $dir)->orderBy('movies.id','asc');
                return;
            case 'oldest':
                $order = 'movies.created_at';
                $dir = 'asc';
                $query->orderBy($order, $dir)->orderBy('movies.id','desc');
                return;
            case 'newest-release':
                $order = 'release_date';
                $dir = 'desc';
                break;
            case 'oldest-release':
                $order = 'release_date';
                $dir = 'asc';
                break;
                default:
                    abort(400, 'invalid sort type:' . $sortType . '. valid sort types are:newest,oldest,newest-release,oldest-release');
        }
        $query->orderBy($order, $dir);
    }
    
}
