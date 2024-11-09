<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Reply extends Model
{
    /** @use HasFactory<\Database\Factories\ReplyFactory> */
    use HasFactory;
    use Filterable;

    protected $table = 'review_replies';
    protected $fillable = ['text', 'user_id', 'review_id', 'reply_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function review(): BelongsTo
    {
        return $this->belongsTo(Review::class);
    }

    public function reply(): BelongsTo
    {
        return $this->belongsTo(Reply::class);
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
                abort(400, 'invalid sort type:' . $sortType . '. valid sort types are:newest,oldest,most-likes,least-likes,most-dislikes,least-dislikes,most-replies,least-replies');
        }
        $query->orderBy($order, $dir);
    }
}
