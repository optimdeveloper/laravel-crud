<?php


namespace App\Repositories;

use App\Models\UserProfile;
use App\Repositories\Base\BaseRepository;
use App\Repositories\Base\BaseWithUserRepository;
use Illuminate\Database\Eloquent\Collection;

class UserProfileRepository extends BaseWithUserRepository implements UserProfileRepositoryInteface
{
    public function __construct(UserProfile $model)
    {
        parent::__construct($model);
    }

    public function get_user_work ($userid)
    {
        return UserProfile::where('user_id',$userid)->orderBy('id', 'ASC')->first();
    }
}
