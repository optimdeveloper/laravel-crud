<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PromoteEvent extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'promote_events';

    public function event()
    {
        return $this->hasOne('App\Models\Event', 'promote_event_id', 'id');
    }
}
