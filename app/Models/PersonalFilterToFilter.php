<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PersonalFilterToFilter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'personal_filter_to_filters';

    public function personal_filter()
    {
        return $this->belongsTo('App\Models\PersonalFilter', 'personal_filter_id', 'id');
    }

    public function filters()
    {
        return $this->belongsTo('App\Models\Filter', 'filter_id', 'id');
    }
}
