<?php


namespace App\Repositories;

use App\Repositories\Base\BaseRepositoryInterface;

interface UserContactRepositoryInterface extends BaseRepositoryInterface
{
    public function user_is_blocked ($userid);
    public function get_by_user_and_contact ($user_id, $contact_id);
}
