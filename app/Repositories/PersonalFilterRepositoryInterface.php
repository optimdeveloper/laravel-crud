<?php


namespace App\Repositories;

use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface PersonalFilterRepositoryInterface extends BaseRepositoryInterface
{
    public function get_user_personal_filter();
    public function get_personal_filter_by_user($user_id);
    public function create_default();
    public function create_default_by_user($user_id);
}
