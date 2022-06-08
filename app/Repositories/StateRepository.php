<?php


namespace App\Repositories;

use App\Models\State;
use App\Repositories\Base\BaseRepository;
use Illuminate\Support\Collection;
use Throwable;

class StateRepository extends BaseRepository implements StateRepositoryInterface
{
    public function __construct(State $model)
    {
        parent::__construct($model);
    }

    public function actives(): Collection
    {
        return $this->model->where('deleted', 0)->get();
    }

    public function published(): Collection
    {
        return $this->model->where('deleted', 0)
            ->where('published', 1)
            ->orderBy('name', 'asc')
            ->get();
    }

    public function publishedByCountry($country_id): Collection
    {
        return $this->model->where('deleted', 0)
            ->where('published', 1)
            ->where('country_id', $country_id)
            ->get();
    }
}
