<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserToPassion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_to_passions';

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function passions()
    {
        return $this->belongsTo('App\Models\Passion', 'passion_id', 'id');
    }
}
