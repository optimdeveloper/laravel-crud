<?php

namespace App\Repositories\Base;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class BaseWithUserRepository extends BaseRepository implements BaseWithUserRepositoryInterface
{
    public function __construct(Model $model)
    {
        parent::__construct($model);
    }

    public function list_with_user(): Collection {
        return $this->model::with('user')->get();
    }

    public function find_with_user($id): Collection {
        return $this->model->where('id', $id)->with('user')->get();
    }
}
