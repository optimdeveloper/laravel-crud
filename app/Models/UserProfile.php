<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserProfile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_profiles';

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }
}
