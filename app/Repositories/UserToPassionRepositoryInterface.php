<?php


namespace App\Repositories;

use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface UserToPassionRepositoryInterface extends BaseRepositoryInterface
{
    public function find_with_detail($id): Collection;
    public function find_passion($id_passion, $id_user): bool;
    public function find_passions($personal_filter_id): Collection;
}
