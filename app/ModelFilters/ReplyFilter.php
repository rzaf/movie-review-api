<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;
use Illuminate\Database\Eloquent\Builder;


class ReplyFilter extends ModelFilter
{
    /**
     * Related Models that have ModelFilters as well as the method on the ModelFilter
     * As [relationMethod => [input_key1, input_key2]].
     *
     * @var array
     */
    public $relations = [];
    
    public function searchTerm($term)
    {
        return $this->whereLike('text', "%$term%");
    }

    public function username($username)
    {
        return $this->whereRelation('user', function (Builder $query) use ($username){
            $query->where('username',$username);
        });
    }

    public function likesCount($cnt)
    {
        return $this->has('likes', '=', $cnt);   
    }

    public function dislikesCount($cnt)
    {
        return $this->has('dislikes', '=', $cnt);   
    }

    public function repliesCount($cnt)
    {
        return $this->has('replies', '=', $cnt);   
    }
}
