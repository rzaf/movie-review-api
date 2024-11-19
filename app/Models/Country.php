<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';

    public $fillable = ['country_name', 'country_code'];

    public function getNameAttribute()
    {
        return $this->country_name;
    }
}
