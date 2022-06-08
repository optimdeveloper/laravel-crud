<?php


namespace App\Repositories;

use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface MatchsRepositoryInterface extends BaseRepositoryInterface
{
    public function find_by_users($user_one_id, $user_two_id): Collection;
    public function find_by_user($user_id): Collection;
    public function find_by_user_not_rejected($user_id, $order = null): Collection;
    public function find_by_user_not_rejected_sent($user_id, $order = null): Collection;
    public function find_by_user_not_rejected_received($user_id, $order = null): Collection;
}
