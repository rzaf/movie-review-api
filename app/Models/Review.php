<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Review extends Model
{
    /** @use HasFactory<\Database\Factories\ReviewFactory> */
    use HasFactory;
    use Filterable;

    protected $fillable = ['review', 'score', 'user_id', 'media_id'];
    protected $table = 'media_reviews';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(Reply::class);
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
            case 'highest-score':
                $order = 'score';
                $dir = 'desc';
                break;
            case 'lowest-score':
                $order = 'score';
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
            case 'most-replies':
                $order = 'replies_count';
                $dir = 'desc';
                break;
            case 'least-replies':
                $order = 'replies_count';
                $dir = 'asc';
                break;
            default:
                abort(400, 'invalid sort type:' . $sortType . '. valid sort types are:newest,oldest,highest-score,lowest-score,most-likes,least-likes,most-dislikes,least-dislikes,most-replies,least-replies');
        }
        $query->orderBy($order, $dir);
    }
}
