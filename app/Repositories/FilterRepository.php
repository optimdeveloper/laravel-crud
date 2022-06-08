<?php


namespace App\Repositories;

use App\Models\Filter;
use App\Repositories\Base\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class FilterRepository extends BaseRepository implements FilterRepositoryInterface
{
    public function __construct(Filter $model)
    {
        parent::__construct($model);
    }

    public function list_type_event(): Collection{
        return $this->model->where('type', 'Event')->where('parent', null)->get();
    }

    public function list_type_advanced($mode): Collection{
        return $this->model->where('type', 'Advanced')
                            ->where('parent', null)
                            ->where('mode', $mode)
                            ->get();
    }

    public function list_type_advanced_sub($mode, $parent_id): Collection{
        return $this->model->where('type', 'Advanced')
                            ->where('parent', $parent_id)
                            ->where('mode', $mode)
                            ->get();
    }

    public function list($mode = null, $parent = null): Collection{
        return $this->model->where('parent', $parent)
                            ->where('mode', $mode)
                            ->get();
    }
}
