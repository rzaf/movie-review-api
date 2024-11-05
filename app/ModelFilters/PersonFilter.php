<?php

namespace App\ModelFilters;

use EloquentFilter\ModelFilter;

class PersonFilter extends ModelFilter
{

    public function gender($str)
    {
        return $this->where('is_male', '=', $str==='male');
    }

    public function followersCount($cnt)
    {
        return $this->has('followers', '=', $cnt);   
    }

    public function moviesCount($cnt)
    {
        return $this->has('movies', '=', $cnt);   
    }
}