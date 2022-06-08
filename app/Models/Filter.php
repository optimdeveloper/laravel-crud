<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Filter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'filters';

    public function personal_to_filters()
    {
        return $this->hasMany('App\Models\PersonalFilterToFilter', 'filter_id', 'id');
    }

    public function filter_to_events()
    {
        return $this->hasMany('App\Models\FilterToEvent', 'filter_id', 'id');
    }
}
