<?php


namespace App\Repositories;

use App\Models\PersonalFilterToFilter;
use App\Repositories\Base\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class PersonalFilterToFilterRepository extends BaseRepository implements PersonalFilterToFilterRepositoryInterface
{
    public function __construct(PersonalFilterToFilter $model)
    {
        parent::__construct($model);
    }

    public function find_filters($personal_filter_id, $type = null): Collection
    {
        if (!isset($type))
            return $this->model->where('personal_filter_id', $personal_filter_id)->get();
        else
            return $this->model->where('personal_filter_id', $personal_filter_id)
                                ->where('type', $type)->get();
    }
}
