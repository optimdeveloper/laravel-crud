<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'events';

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function photo_event()
    {
        return $this->hasOne('App\Models\PhotoEvent', 'event_id', 'id');
    }

    public function promote_event()
    {
        return $this->belongsTo('App\Models\PromoteEvent', 'promote_event_id', 'id');
    }

    public function me_reports()
    {
        return $this->hasMany('App\Models\Report', 'event_id', 'id');
    }

    public function guests()
    {
        return $this->hasMany('App\Models\Guest', 'event_id', 'id');
    }

    public function filter_to_events()
    {
        return $this->hasMany('App\Models\FilterToEvent', 'event_id', 'id');
    }
}
