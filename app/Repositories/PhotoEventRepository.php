<?php


namespace App\Repositories;

use App\Models\PhotoEvent;
use App\Repositories\Base\BaseRepository;

class PhotoEventRepository extends BaseRepository implements PhotoEventRepositoryInterface
{
    public function __construct(PhotoEvent $model)
    {
        parent::__construct($model);
    }
}
