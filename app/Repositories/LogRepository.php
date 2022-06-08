<?php


namespace App\Repositories;

use App\Models\Log;
use App\Repositories\Base\BaseRepository;

class LogRepository extends BaseRepository implements LogRepositoryInterface
{
    public function __construct(Log $model)
    {
        parent::__construct($model);
    }
}
