<?php


namespace App\Repositories;

use App\Models\ConectedApp;
use App\Repositories\Base\BaseWithUserRepository;

class ConectedAppRepository extends BaseWithUserRepository implements ConectedAppRepositoryInterface
{
    public function __construct(ConectedApp $model)
    {
        parent::__construct($model);
    }
}
