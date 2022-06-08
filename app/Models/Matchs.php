<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Matchs extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'matches';

    public function match_users_one()
    {
        return $this->belongsTo('App\Models\Matchs', 'user_one_id', 'id');
    }

    public function match_users_two()
    {
        return $this->belongsTo('App\Models\Matchs', 'user_two_id', 'id');
    }
}
