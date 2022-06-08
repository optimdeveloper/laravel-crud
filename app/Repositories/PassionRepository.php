<?php


namespace App\Repositories;

use App\Models\Passion;
use App\Repositories\Base\BaseRepository;

class PassionRepository extends BaseRepository implements PassionRepositoryInterface
{
    public function __construct(Passion $model)
    {
        parent::__construct($model);
    }
}
