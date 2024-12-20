<?php

namespace App\ModelFilters;

use DB;
use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;

class MediaFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];
    
    
    public function releaseDate($date)
    {
        return $this->where('release_date','=', $date);
    }

    public function category($name)
    {
        return $this->related('category', 'name','=', $name);   
    }

    public function searchTerm($term)
    {
        return $this->whereLike('name', "%$term%");
    }

    public function score($score)
    {
        return $this->whereHas('reviews', function (Builder $query) {
            $query->select(DB::raw('AVG(score)'));
        }, '=', floatval($score)*10);
    }

    public function likesCount($cnt)
    {
        return $this->has('likes', '=', $cnt);   
    }

    public function dislikesCount($cnt)
    {
        return $this->has('dislikes', '=', $cnt);   
    }

    public function reviewsCount($cnt)
    {
        return $this->has('reviews', '=', $cnt);   
    }

}
