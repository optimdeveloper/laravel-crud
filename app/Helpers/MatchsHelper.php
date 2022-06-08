<?php


namespace App\Helpers;

use Illuminate\Database\Eloquent\Collection;
use App\Models\Guest;
use App\Models\User;
use App\Models\PersonalFilter;
use App\Models\PersonalFilterToFilter;
use App\Models\UserContact;
use App\Models\UserProfile;
use App\Models\State;
use App\Models\City;
use App\Repositories\Base\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use App\Core\PersonalFilterTypeEnum;
use App\Helpers\DistanceHelper;
use Carbon\Carbon;

use function PHPUnit\Framework\isNull;

class MatchsHelper
{

    static function getFromGuests($user_id, $event_id, $status = null, $order_by = null, $search): Collection
    {
        $matches = new Collection();
        //get user preferences
        $p = PersonalFilter::select('id','interested', 'age_range', 'mode', 'verified_profyle_only', 'height')
        ->where('user_id', $user_id)->first();

        if(!$p) {
            return $matches; //must have personal filter
        }

        $interested_mode = $p->mode;

        if($p->interested == 'Women') {
            $interested_gender = "Woman";
        }
        elseif($p->interested == 'Men') {
            $interested_gender = "Man";
        }
        else {
            $interested_gender = $p->interested;
        }
        
        $interested_verified_profyle_only = $p->verified_profyle_only;
        $interested_height = $p->height;

        $interested_height_array = array();

        if(!is_null($interested_height)) {
            $interested_height_array = explode('-', $interested_height);
        }

        if(sizeof($interested_height_array)>1) {
            $interested_height_min = (float)trim($interested_height_array[0]);
            $interested_height_max = (float)trim($interested_height_array[1]);
        } else {
            $interested_height_min = 0;
            $interested_height_max = 2;
        }

        $interested_age = $p->age_range;

        $interested_age_array = array();

        if(!is_null($interested_age)) {
            $interested_age_array = explode('-', $interested_age);
        }

        if(sizeof($interested_age_array)>1) {
            $interested_age_min = (float)trim($interested_age_array[0]);
            $interested_age_max = (float)trim($interested_age_array[1]);
        } else {
            $interested_age_min = 21;
            $interested_age_max = 80;
        }

        $max_date = Carbon::now()->subYears($interested_age_min);
        $min_date = Carbon::now()->subYears($interested_age_max);

        $min_coincidences = 0;

        $query = Guest::select('users.*', 'guests.id as guests_id', 'guests.status as status'
        , 'user_profiles.work as work', 'user_profiles.height as height', 'guests.id as guests_id', 
        'guests.status as status', 'personal_filters.id as personal_filter_id')
        ->join('users', 'users.id', '=', 'guests.user_id')
        ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')//must have profile
        ->leftJoin('personal_filters', 'personal_filters.user_id', '=', 'users.id')
        ->leftJoin('user_contacts', function($join) use ($user_id)
        {
            $join->on('users.id', '=', 'user_contacts.contact_id')
            ->where('user_contacts.user_id', '=', $user_id)
            ->where('user_contacts.status', '=', 'Block');
        })//to check if blocked
        ->leftJoin('matches', function($join) use ($user_id)
        {
            $join->on('matches.user_two_id', '=', 'users.id')
                ->where('matches.user_one_id', '=', $user_id)
                ->where('matches.status', '=', 1)
            ->orOn('matches.user_one_id', '=', 'users.id')
                ->where('matches.user_two_id', '=', $user_id)
                ->where('matches.status', '=', 1);
        })//to check if not already in matches table
        ->where('user_contacts.contact_id', null) //no blocked
        ->where('matches.user_two_id', null) //no already match one way (even sent)
        ->where('matches.user_one_id', null) //no already match the other way (even sent)

        ->where('guests.event_id', $event_id);

        $query->where('users.id', '!=' , $user_id); //not myself

        if($interested_mode == 'Love'){
            if($interested_gender !== "Everyone"){
                $query->where('users.gender', $interested_gender);
            }

            if(sizeof($interested_age_array)>1) {
                $query->where('users.birthday_at', '>=' , $min_date);
                $query->where('users.birthday_at', '<=' , $max_date);
            }
        }
        elseif($interested_mode == 'Friendship') {
            if(sizeof($interested_age_array)>1) {
                $query->where('users.birthday_at', '>=' , $min_date);
                $query->where('users.birthday_at', '<=' , $max_date);
            }

        }

        elseif($interested_mode == 'Networking') {
            if($interested_gender !== "Everyone"){
                $query->where('users.gender', $interested_gender);
            }
        }

        if($interested_verified_profyle_only > 0) {
            $query->Where('users.email_verified_at', '!=', null);
        }

        // if(sizeof($interested_height_array)>1) {
        //     $query->where('user_profiles.height', '>' , 0);
        //     $query->where('user_profiles.height', '>=' , $interested_height_min);
        //     $query->where('user_profiles.height', '<=' , $interested_height_max);
        // }

        if(!is_null($status)) {
            $query->Where('guests.status', 'like', '%' . $status . '%');
        }

        if(!is_null($search)) {
            $query->Where('users.name', 'like', '%' . $search . '%');
        }

        if ($order_by === 0) {
            // $col = 'birthday_at';
            $query->orderBy('users.birthday_at', 'DESC');
        } else if ($order_by === 1) {
            // $col = 'name';
            $query->orderBy('users.name', 'ASC');
        }
        
        $first_results = $query->get();

        // additional  coincidences with personal filters

        $type_me = PersonalFilterTypeEnum::me;
        $type_looking = PersonalFilterTypeEnum::looking;

        $my_preferences = PersonalFilterToFilter::select('personal_filter_to_filters.id as id','filters.type as filter_type', 
        'filters.name as name', 'filters.mode as mode')
            ->join('filters', 'filters.id', '=', 'personal_filter_to_filters.filter_id')
            ->where('personal_filter_to_filters.personal_filter_id', $p->id)
            ->where('personal_filter_to_filters.type', $type_looking)->get();

            $counter = 0;

        foreach($first_results as $r) {

            $counter++;
            $coincidences = 1; //1 to coincidences as they past the first filters

            if($r->personal_filter_id && !is_null($r->personal_filter_id)) {
                foreach($my_preferences as $mp) {
                    $same_preferences = PersonalFilterToFilter::select('personal_filter_to_filters.id') //get same me as preferences
                    ->join('filters', 'filters.id', '=', 'personal_filter_to_filters.filter_id')
                    ->where('personal_filter_to_filters.personal_filter_id', $r->personal_filter_id)
                                    ->where('personal_filter_to_filters.type', $type_me)
                                    ->where('filters.type', $mp->filter_type)
                                    ->where('filters.name', $mp->name)
                                    ->where('filters.mode', $mp->mode)->get();

                    $coincidences = $coincidences+ sizeof($same_preferences);
                }
            }


            if($coincidences >= $min_coincidences) {
                //add additional info and add to results
                $user = $r;
                $user->coincidences = $coincidences;
                $is_blocked = "no";// always not blocked for previous restrictions
                $user->is_blocked = $is_blocked;

                /*getting relevance*/
                
                $rele = Guest::select('id')
                ->where('user_id', $user->id)->get();

                $relevance = count($rele);

                $user->relevance = $relevance;
                $matches->push($user);

            }

        }

        if ($order_by === 2) {
            //Ordenar por relevancia (el que tenga mas count user_id en la tabla guests)
            // $col = 'relevance';
            $matches = $matches->sortByDesc(function ($item) {
                return $item->relevance; 
            })->values();
        }

        return $matches;
    }

    static function getAll($user_id, $order_by = null, $lat = null, $long = null, $search): Collection
    { 
        $matches = new Collection();

        //get user preferences
        $p = PersonalFilter::select('id','interested', 'age_range', 'distance', 'mode', 'verified_profyle_only', 'height')
        ->where('user_id', $user_id)->first();

        if(!$p) {
            return $matches; //must have personal filter
        }
        //filter principal => mode => gender
        //theres no personal height ;***

        $interested_mode = $p->mode;

        if($p->interested == 'Women') {
            $interested_gender = "Woman";
        }
        elseif($p->interested == 'Men') {
            $interested_gender = "Man";
        }
        else {
            $interested_gender = $p->interested;
        }
        
        $interested_verified_profyle_only = $p->verified_profyle_only;
        $interested_height = $p->height;
        $interested_distance = $p->distance;

        $interested_height_array = array();

        if(!is_null($interested_height)) {
            $interested_height_array = explode('-', $interested_height);
        }

        if(sizeof($interested_height_array)>1) {
            $interested_height_min = (float)trim($interested_height_array[0]);
            $interested_height_max = (float)trim($interested_height_array[1]);
        } else {
            $interested_height_min = 0;
            $interested_height_max = 2;
        }

        $interested_age = $p->age_range;

        $interested_age_array = array();

        if(!is_null($interested_age)) {
            $interested_age_array = explode('-', $interested_age);
        }

        if(sizeof($interested_age_array)>1) {
            $interested_age_min = (float)trim($interested_age_array[0]);
            $interested_age_max = (float)trim($interested_age_array[1]);
        } else {
            $interested_age_min = 21;
            $interested_age_max = 80;
        }

        $max_date = Carbon::now()->subYears($interested_age_min);
        $min_date = Carbon::now()->subYears($interested_age_max);

        $min_coincidences = 0;

        $query = User::select('users.*'
        , 'user_profiles.work as work', 'user_profiles.height as height', 
        'personal_filters.id as personal_filter_id', 'user_profiles.lives_in as lives_in')
        ->join('user_profiles', 'users.id', '=', 'user_profiles.user_id')//must have profile
        ->leftJoin('personal_filters', 'personal_filters.user_id', '=', 'users.id')
        ->leftJoin('user_contacts', function($join) use ($user_id)
        {
            $join->on('users.id', '=', 'user_contacts.contact_id')
            ->where('user_contacts.user_id', '=', $user_id)
            ->where('user_contacts.status', '=', 'Block');
        })//to check if blocked
        ->leftJoin('matches', function($join) use ($user_id)
        {
            $join->on('matches.user_two_id', '=', 'users.id')
                ->where('matches.user_one_id', '=', $user_id)
                ->where('matches.status', '=', 1)
            ->orOn('matches.user_one_id', '=', 'users.id')
                ->where('matches.user_two_id', '=', $user_id)
                ->where('matches.status', '=', 1);
        })//to check if not already in matches table
        ->where('user_contacts.contact_id', null) //no blocked
        ->where('matches.user_two_id', null) //no already match one way (even sent)
        ->where('matches.user_one_id', null); //no already match the other way (even sent)

        $query->where('users.id', '!=' , $user_id); //not myself

        if($interested_mode == 'Love'){
            if($interested_gender !== "Everyone"){
                $query->where('users.gender', $interested_gender);
            }

            if(sizeof($interested_age_array)>1) {
                $query->where('users.birthday_at', '>=' , $min_date);
                $query->where('users.birthday_at', '<=' , $max_date);
            }
        }
        elseif($interested_mode == 'Friendship') {
            if(sizeof($interested_age_array)>1) {
                $query->where('users.birthday_at', '>=' , $min_date);
                $query->where('users.birthday_at', '<=' , $max_date);
            }

        }
        elseif($interested_mode == 'Networking') {
            if($interested_gender !== "Everyone"){
                $query->where('users.gender', $interested_gender);
            }
        }
        

        if($interested_verified_profyle_only > 0) {
            $query->where('users.email_verified_at', '!=', null);
        }

        // if(sizeof($interested_height_array)>1) {
        //     $query->where('user_profiles.height', '>' , 0);
        //     $query->where('user_profiles.height', '>=' , $interested_height_min);
        //     $query->where('user_profiles.height', '<=' , $interested_height_max);
        // }

        if(!is_null($search)) {
            $query->Where('users.name', 'like', '%' . $search . '%');
        }
        

        if ($order_by === 0) {
            // $col = 'birthday_at';
            $query->orderBy('users.birthday_at', 'DESC');
        } else if ($order_by === 1) {
            // $col = 'name';
            $query->orderBy('users.name', 'ASC');
        }

        // $response = array('query'=>$query->toSql(), 'vars' => $query->getBindings());
        // dd($response);

        $first_results = $query->get();

        // additional  coincidences with personal filters

        $type_me = PersonalFilterTypeEnum::me;
        $type_looking = PersonalFilterTypeEnum::looking;

        $my_preferences = PersonalFilterToFilter::select('personal_filter_to_filters.id as id','filters.type as filter_type', 
        'filters.name as name', 'filters.mode as mode')
            ->join('filters', 'filters.id', '=', 'personal_filter_to_filters.filter_id')
            ->where('personal_filter_to_filters.personal_filter_id', $p->id)
            ->where('personal_filter_to_filters.type', $type_looking)->get();

        foreach($first_results as $r) {

            $coincidences = 1; //1 to coincidences as they past the first filters

            if($r->personal_filter_id && !is_null($r->personal_filter_id)) {
                foreach($my_preferences as $mp) {
                    $same_preferences = PersonalFilterToFilter::select('personal_filter_to_filters.id') //get same me as preferences
                    ->join('filters', 'filters.id', '=', 'personal_filter_to_filters.filter_id')
                    ->where('personal_filter_to_filters.personal_filter_id', $r->personal_filter_id)
                                    ->where('personal_filter_to_filters.type', $type_me)
                                    ->where('filters.type', $mp->filter_type)
                                    ->where('filters.name', $mp->name)
                                    ->where('filters.mode', $mp->mode)->get();
    
                    $coincidences = $coincidences+ sizeof($same_preferences);
                }
            }

            if(!is_null($lat) && !is_null($long) && $interested_distance > 0){

                $nearby = $interested_distance;

                $li= $r->lives_in;
                $liarray = explode(',', $li);

                if(sizeof($liarray)>1) {
                    $city_name = trim($liarray[0]);
                    $state_iso = trim($liarray[1]);

                    $state = State::select('id')
                    ->where('iso_code', $state_iso)->first();

                    if($state) {
                        $city = City::select('latitude', 'longitude')
                        ->where('state_id', $state->id)
                        ->where('name', $city_name)->first();

                        if($city) {
                            $distance = DistanceHelper::calculate_distance($lat, $long, $city->latitude, $city->longitude);

                            if ($distance <= $nearby) {
                                $isnear = true;
                            }
                            else {
                                $isnear = false;
                            }
                        }
                        else {
                            $isnear = false;
                        }
                    }
                    else {
                        $isnear = false;
                    }
                    
                }
                else {
                    $isnear = false;
                }
            }
            else {
                $isnear = true; //get all
            }


            if($coincidences >= $min_coincidences && $isnear) {
                //add additional info and add to results
                $user = $r;
                $user->coincidences = $coincidences;
                $is_blocked = "no";// always not blocked for previous restrictions
                $user->is_blocked = $is_blocked;

                /*getting relevance*/
                
                $rele = Guest::select('id')
                ->where('user_id', $user->id)->get();

                $relevance = count($rele);

                $user->relevance = $relevance;
                $matches->push($user);
            }
        }

        if ($order_by === 2) {
            //Ordenar por relevancia (el que tenga mas count user_id en la tabla guests)
            // $col = 'relevance';
            $matches = $matches->sortByDesc(function ($item) {
                return $item->relevance; 
            })->values();
        }

        return $matches;
    }
}
