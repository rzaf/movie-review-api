<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    public $fillable = ['name', 'parent_id', 'has_items'];
    // public $hidden = ['id'];

    public function parentCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // public function childrenCategories(): HasMany
    // {
    //     return $this->hasMany(Category::class,'parent_id','id');
    // }

    public function movies(): HasMany
    {
        return $this->hasMany(Movie::class);
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
                break;
            case 'oldest':
                $order = 'created_at';
                $dir = 'asc';
                break;
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
