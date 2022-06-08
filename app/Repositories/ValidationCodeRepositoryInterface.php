<?php


namespace App\Repositories;

use App\Repositories\Base\BaseWithUserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface ValidationCodeRepositoryInterface extends BaseWithUserRepositoryInterface
{
    public function find_code_user($id): Collection;

}
