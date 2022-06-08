<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FilterToEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'filter_to_events';

    public function filters()
    {
        return $this->belongsTo('App\Models\Filter', 'filter_id', 'id');
    }

    public function events()
    {
        return $this->belongsTo('App\Models\Event', 'event_id', 'id');
    }
}
