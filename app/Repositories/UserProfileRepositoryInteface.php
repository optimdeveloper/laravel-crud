<?php


namespace App\Repositories;

use App\Repositories\Base\BaseWithUserRepositoryInterface;

interface UserProfileRepositoryInteface extends BaseWithUserRepositoryInterface
{
    public function get_user_work ($userid);

}
