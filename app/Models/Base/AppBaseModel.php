<?php


namespace App\Models\Base;


use Illuminate\Database\Eloquent\Model;

class AppBaseModel extends Model
{
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
}
