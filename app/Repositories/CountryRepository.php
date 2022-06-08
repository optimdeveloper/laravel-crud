<?php


namespace App\Repositories;

use App\Models\Country;
use App\Repositories\Base\BaseRepository;
use Illuminate\Support\Collection;
use Throwable;

class CountryRepository extends BaseRepository implements CountryRepositoryInterface
{
    public function __construct(Country $model)
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

    public function search(string $word, int $top = 25): Collection
    {
        return $this->model->where('deleted', 0)
            ->where('published', 1)
            ->where('name', 'like', $word . '%')
            ->orderBy('name', 'asc')
            ->take($top)
            ->get();
    }
}
