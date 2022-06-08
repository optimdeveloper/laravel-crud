<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'reports';

    public function users()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function profiles()
    {
        return $this->belongsTo('App\Models\User', 'profile_id', 'id');
    }

    public function events()
    {
        return $this->belongsTo('App\Models\Event', 'event_id', 'id');
    }
}
