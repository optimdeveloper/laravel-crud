<?php


namespace App\Repositories;

use App\Models\Report;
use App\Repositories\Base\BaseRepository;

class ReportRepository extends BaseRepository implements ReportRepositoryInterface
{
    public function __construct(Report $model)
    {
        parent::__construct($model);
    }
}
