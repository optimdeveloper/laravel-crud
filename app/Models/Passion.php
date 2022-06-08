<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Passion extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'passions';

    public function user_to_passion()
    {
        return $this->hasMany('App\Models\UserToPassion', 'passion_id', 'id');
    }
}
