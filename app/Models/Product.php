<?php

namespace App\Models;

use Faker\Provider\ar_JO\Person;
use Tymon\JWTAuth\Contracts\JWTSubject;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

use App\Models\Base\AppBaseModel;

class Product extends AppBaseModel
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'products';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'image',
        'price',
        'description',
        'title',

    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */


}
