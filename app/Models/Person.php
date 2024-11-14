<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Person extends Model
{
    /** @use HasFactory<\Database\Factories\PersonFactory> */
    use HasFactory;
    use Filterable;

    protected $table = 'people';
    protected $fillable = ['name', 'url', 'is_male', 'birth_date', 'about', 'birth_country'];
    // protected $hidden = ['pivot'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'birth_country');
    }

    public function medias(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'media_actors');
    }

    public function mediasWorkedIn(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'media_actors')->withPivot('job');
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'followings', 'following_id', 'follower_id');
    }

    public function scopeSortBy(Builder $query, ?string $sortType): void
    {
        $sortType ??= '';
        switch ($sortType) {
            case '':
            case 'newest-created':
                $query->orderBy('created_at', 'desc')->orderBy('id', 'desc');
                return;
            case 'oldest-created':
                $query->orderBy('created_at', 'asc')->orderBy('id', 'asc');
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
            case 'most-medias':
                $order = 'medias_count';
                $dir = 'desc';
                break;
            case 'least-medias':
                $order = 'medias_count';
                $dir = 'asc';
                break;
            default:
                abort(400, 'invalid sort type:' . $sortType . '. valid sort types are:newest-created,oldest-created,youngest,oldest,most-followers,least-followers,most-medias,least-medias');
        }
        $query->orderBy($order, $dir);
    }
}
