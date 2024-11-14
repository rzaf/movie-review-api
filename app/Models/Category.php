<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;
    use Filterable;

    public $fillable = ['name'];
    // public $hidden = ['id'];

    public function movies(): HasMany
    {
        return $this->hasMany(Movie::class);
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
            case 'most-movies':
                $order = 'movies_count';
                $dir = 'desc';
                break;
            case 'least-movies':
                $order = 'movies_count';
                $dir = 'asc';
                break;
            default:
                abort(400, 'invalid sort type:' . $sortType . '. valid sort types are:newest,oldest,most-movies,least-movies');
        }
        $query->orderBy($order, $dir);
    }
}
