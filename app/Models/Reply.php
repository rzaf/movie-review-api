<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Reply extends Model
{
    /** @use HasFactory<\Database\Factories\ReplyFactory> */
    use HasFactory;

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
}
