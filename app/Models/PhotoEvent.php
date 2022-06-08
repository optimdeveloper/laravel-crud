<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhotoEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'photo_events';

    public function event()
    {
        return $this->belongsTo('App\Models\Event', 'event_id', 'id');
    }
}
