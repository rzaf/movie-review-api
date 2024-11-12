<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Movie extends Model
{
    /** @use HasFactory<\Database\Factories\MovieFactory> */
    use HasFactory;
    use Filterable;

    protected $fillable = ['name', 'url', 'category_id', 'release_year'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function staff(): BelongsToMany
    {
        // return $this->belongsToMany(Person::class)->as('movie_actors');
        return $this->belongsToMany(Person::class, 'movie_actors')
            ->withPivot('job');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'movie_genres');
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable')->where('is_liked', '=', '1');
    }

    public function dislikes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable')->where('is_liked', '=', '0');
    }

    public function scopeSortBy(Builder $query, ?string $sortType): void
    {
        $sortType ??= '';
        $order = 'created_at';
        $dir = 'desc';
        switch ($sortType) {
            case '':
                break;
            case 'newest':
                $order = 'created_at';
                $dir = 'desc';
                $query->orderBy($order, $dir)->orderBy('id','asc');
                return;
            case 'oldest':
                $order = 'created_at';
                $dir = 'asc';
                $query->orderBy($order, $dir)->orderBy('id','desc');
                return;
            case 'newest-release':
                $order = 'release_year';
                $dir = 'desc';
                break;
            case 'oldest-release':
                $order = 'release_year';
                $dir = 'asc';
                break;
            case 'most-likes':
                $order = 'likes_count';
                $dir = 'desc';
                break;
            case 'least-likes':
                $order = 'likes_count';
                $dir = 'asc';
                break;
            case 'most-dislikes':
                $order = 'dislikes_count';
                $dir = 'desc';
                break;
            case 'least-dislikes':
                $order = 'dislikes_count';
                $dir = 'asc';
                break;
            case 'most-reviews':
                $order = 'reviews_count';
                $dir = 'desc';
                break;
            case 'least-reviews':
                $order = 'reviews_count';
                $dir = 'asc';
                break;
            case 'best-reviewed':
                $order = 'reviews_avg_score';
                $dir = 'desc';
                break;
            case 'worst-reviewed':
                $order = 'reviews_avg_score';
                $dir = 'asc';
                break;
            default:
                abort(400, 'invalid sort type:' . $sortType . '. valid sort types are:newest,oldest,newest-release,oldest-release,most-likes,least-likes,most-dislikes,least-dislikes,most-reviews,least-reviews,best-reviewed,worst-reviewed');
        }
        $query->orderBy($order, $dir);
    }
}
