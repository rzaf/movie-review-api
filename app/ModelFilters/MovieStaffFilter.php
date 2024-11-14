<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class MovieStaffFilter extends ModelFilter
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
        return $this->where('release_date', '=', $date);
    }

    public function work($job)
    {
        return $this->where('job', '=', $job);
    }

    public function category($name)
    {
        return $this->where('category_name', '=', $name);
    }

    public function search($term)
    {
        return $this->whereLike('movies.name', $term);
    }

}
