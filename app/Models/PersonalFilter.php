<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonalFilter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'personal_filters';

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function personal_to_filters()
    {
        return $this->hasMany('App\Models\PersonalFilterToFilter', 'personal_filter_id', 'id');
    }

    public function personal_to_filters_type()
    {
        return $this->hasMany('App\Models\PersonalFilterToFilter', 'personal_filter_id', 'id')->where('type', 1)->with('filters');
    }
}
