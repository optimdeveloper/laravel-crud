<?php


namespace App\Repositories;

use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface FilterRepositoryInterface extends BaseRepositoryInterface
{
    public function list_type_event(): Collection;
    public function list_type_advanced($mode): Collection;
    public function list_type_advanced_sub($mode, $parent_id): Collection;
    public function list($mode, $parent): Collection;
}
