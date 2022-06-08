<?php


namespace App\Repositories;

use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface PersonalFilterToFilterRepositoryInterface extends BaseRepositoryInterface
{
    public function find_filters($personal_filter_id, $type = null): Collection;
}
