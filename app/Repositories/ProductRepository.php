<?php


namespace App\Repositories;

use App\Models\Event;
use App\Models\PersonalFilter;
use App\Models\Product;
use App\Repositories\Base\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

//Models.


class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    public function __construct(Product $model)
    {
        parent::__construct($model);
    }

    public function list_with_detail(): Collection
    {
        return $this->model::with(
            'image',
            'price',
            'description',
            'title',
        )->get();
    }



}
