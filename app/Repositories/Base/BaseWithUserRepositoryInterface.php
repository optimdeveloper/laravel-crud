<?php


namespace App\Repositories\Base;

use Illuminate\Database\Eloquent\Collection;

interface BaseWithUserRepositoryInterface extends BaseRepositoryInterface
{
    public function list_with_user(): Collection;
    public function find_with_user($id): Collection;
}
