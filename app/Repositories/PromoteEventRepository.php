<?php


namespace App\Repositories;

use App\Models\PromoteEvent;
use App\Repositories\Base\BaseRepository;

class PromoteEventRepository extends BaseRepository implements PromoteEventRepositoryInterface
{
    public function __construct(PromoteEvent $model)
    {
        parent::__construct($model);
    }
}
