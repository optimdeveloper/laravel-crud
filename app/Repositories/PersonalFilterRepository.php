<?php


namespace App\Repositories;

use App\Models\PersonalFilter;
use App\Repositories\Base\BaseRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Collection;

class PersonalFilterRepository extends BaseRepository implements PersonalFilterRepositoryInterface
{
    public function __construct(PersonalFilter $model)
    {
        parent::__construct($model);
    }

    public function get_user_personal_filter()
    {
        $user_id = Auth::id();
        $response = $this->model->where('user_id', $user_id)->first();

        return $response;
    }

    public function get_personal_filter_by_user($user_id)
    {
        $response = $this->model->where('user_id', $user_id)->first();

        return $response;
    }

    public function create_default()
    {
        $default_interested= "Everyone";
        $default_age_range= "20-40";
        $default_distance= 5;
        $default_verified_profyle_only= 0;
        $default_mode= "Networking";
        $default_height= "0.5-2.5";

        $response = false;

        $user_id = Auth::id();

        $db = new PersonalFilter();

        $db->user_id = $user_id;
        $db->interested = $default_interested;
        $db->age_range = $default_age_range;
        $db->distance = $default_distance;
        $db->verified_profyle_only = $default_verified_profyle_only;
        $db->mode = $default_mode;
        $db->height = $default_height;

        if ($db->save()) {
            $response = true;
        }

        return $response;
    }

    public function create_default_by_user($user_id)
    {
        $default_interested= "Everyone";
        $default_age_range= "20-40";
        $default_distance= 5;
        $default_verified_profyle_only= 0;
        $default_mode= "Networking";
        $default_height= "0.5-2.5";

        $response = false;

        $db = new PersonalFilter();

        $db->user_id = $user_id;
        $db->interested = $default_interested;
        $db->age_range = $default_age_range;
        $db->distance = $default_distance;
        $db->verified_profyle_only = $default_verified_profyle_only;
        $db->mode = $default_mode;
        $db->height = $default_height;

        if ($db->save()) {
            $response = true;
        }

        return $response;
    }
}
