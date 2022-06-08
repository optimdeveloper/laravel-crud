<?php


namespace App\Repositories;

use App\Repositories\Base\BaseWithUserRepositoryInterface;

interface UserPhotoRepositoryInterface extends BaseWithUserRepositoryInterface
{
    public function get_first_user_photo ($userid);
}
