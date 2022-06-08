<?php


namespace App\Repositories;

use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface FilterToEventRepositoryInterface extends BaseRepositoryInterface
{
    public function find_filters($event_id, $relation = null): Collection;
}
