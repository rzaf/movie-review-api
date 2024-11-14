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

    protected $fillable = ['name', 'url', 'category_id', 'release_date', 'summary', 'storyline'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function languages(): BelongsToMany
    {
        return $this->belongsToMany(Language::class, 'movie_languages');
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class, 'movie_countries');
    }
    
    public function keywords(): BelongsToMany
    {
        return $this->belongsToMany(Keyword::class, 'movie_keywords');
    }

    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'movie_companies');
    }

    public function staff(): BelongsToMany
    {
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
        switch ($sortType) {
            case '':
            case 'newest':
                $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
                return;
            case 'oldest':
                $query->orderBy('created_at', 'asc')->orderBy('id', 'asc');
                return;
            case 'newest-release':
                $order = 'release_date';
                $dir = 'desc';
                break;
            case 'oldest-release':
                $order = 'release_date';
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
