<?php


namespace App\Repositories;

use App\Models\UserPhoto;
use App\Repositories\Base\BaseWithUserRepository;

class UserPhotoRepository extends BaseWithUserRepository implements UserPhotoRepositoryInterface
{
    public function __construct(UserPhoto $model)
    {
        parent::__construct($model);
    }

    public function get_first_user_photo ($userid)
    {
        return UserPhoto::where('user_id', $userid)->orderBy('id','ASC')->first();
    }
}
