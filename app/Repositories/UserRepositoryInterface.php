<?php


namespace App\Repositories;

use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface extends BaseRepositoryInterface
{
    public function list_with_detail(): Collection; // User, subscription, profile, photo, personal_filter.
    public function find_with_detail($id): Collection;
}
