<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserContact extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'user_contacts';

    public function user()
    {
        return $this->belongsTo('App\Models\User', 'user_id', 'id');
    }

    public function user_contact_id()
    {
        return $this->belongsTo('App\Models\User', 'contact_id', 'id');
    }
}
