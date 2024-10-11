<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Like extends Model
{
    /** @use HasFactory<\Database\Factories\LikeFactory> */
    use HasFactory;

    protected $fillable = ['is_liked','user_id','likeable_type','likeable_id'];
    protected $table = 'likes';

    // movie,review,reply
    public function likeable(): MorphTo
    {
        return $this->morphTo();
    }
}
