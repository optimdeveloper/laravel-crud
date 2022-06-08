<?php


namespace App\Repositories;

use App\Models\FilterToEvent;
use App\Repositories\Base\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class FilterToEventRepository extends BaseRepository implements FilterToEventRepositoryInterface
{
    public function __construct(FilterToEvent $model)
    {
        parent::__construct($model);
    }

    public function find_filters($event_id, $relation = null): Collection{
        if ($relation == null)
            return $this->model->where('event_id', $event_id)->get();

        return $this->model->where('event_id', $event_id)->with($relation)->get();
    }
}
