<?php

namespace App\Models;

use Faker\Provider\ar_JO\Person;
use Tymon\JWTAuth\Contracts\JWTSubject;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'birthday_at',
        'gender',
        'receive_news',
        'show_gender',
        'city_id',
        'location_allowed',
        'notifications'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // JWT
    public function getJWTIdentifier()
    {
        return $this->getKey();

    }
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function user_profile()
    {
        return $this->hasOne('App\Models\UserProfile', 'user_id', 'id');
    }

    public function subscription()
    {
        return $this->hasOne('App\Models\Subscription', 'user_id', 'id');
    }

    public function personal_filter()
    {
        return $this->hasOne('App\Models\PersonalFilter', 'user_id', 'id')->with('personal_to_filters_type');
    }

    public function user_photos()
    {
        return $this->hasMany('App\Models\UserPhoto', 'user_id', 'id');
    }

    public function contacts()
    {
        return $this->hasMany('App\Models\UserContact', 'user_id', 'id');
    }

    public function contact()
    {
        return $this->hasMany('App\Models\UserContact', 'contact_id', 'id');
    }

    public function user_to_passions()
    {
        return $this->hasMany('App\Models\UserToPassion', 'user_id', 'id')->with('passions');
    }

    public function connected_apps()
    {
        return $this->hasMany('App\Models\ConectedApp', 'user_id', 'id');
    }

    public function events()
    {
        return $this->hasMany('App\Models\Event', 'user_id', 'id');
    }

    public function reports()
    {
        return $this->hasMany('App\Models\Report', 'user_id', 'id');
    }

    public function me_user_reports()
    {
        return $this->hasMany('App\Models\Report', 'profile_id', 'id');
    }

    public function guests()
    {
        return $this->hasMany('App\Models\Guest', 'user_id', 'id');
    }

    public function user_matches_one()
    {
        return $this->hasMany('App\Models\Matchs', 'user_one_id', 'id');
    }

    public function user_matches_two()
    {
        return $this->hasMany('App\Models\Matchs', 'user_two_id', 'id');
    }

    public function validation_code()
    {
        return $this->hasMany('App\Models\ValidationCode', 'user_id', 'id');
    }
}
