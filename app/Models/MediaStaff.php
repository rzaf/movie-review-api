<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaStaff extends Model
{
    use HasFactory;
    use Filterable;

    protected $table = 'media_actors';

    protected $fillable = ['job', 'person_id', 'media_id'];


    public function scopeSortBy(Builder $query, ?string $sortType)
    {
        $sortType ??= '';
        switch ($sortType) {
            case '':
            case 'newest':
                $query->orderBy('medias.created_at', 'desc')->orderBy('medias.id','desc');
                return;
            case 'oldest':
                $query->orderBy('medias.created_at', 'asc')->orderBy('medias.id','asc');
                return;
            case 'newest-release':
                $order = 'release_date';
                $dir = 'desc';
                break;
            case 'oldest-release':
                $order = 'release_date';
                $dir = 'asc';
                break;
                default:
                    abort(400, 'invalid sort type:' . $sortType . '. valid sort types are:newest,oldest,newest-release,oldest-release');
        }
        $query->orderBy($order, $dir);
    }
    
}
