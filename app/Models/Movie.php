<?php

namespace App\Models;

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

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'movie_tags');
    }

    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable')->where('is_liked', '=', '1');
    }

    public function dislikes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable')->where('is_liked', '=', '0');
    }
}
