<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
}
