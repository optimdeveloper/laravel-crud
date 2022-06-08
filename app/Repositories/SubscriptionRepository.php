<?php


namespace App\Repositories;

use App\Models\Subscription;
use App\Repositories\Base\BaseWithUserRepository;

class SubscriptionRepository extends BaseWithUserRepository implements SubscriptionRepositoryInterface
{
    public function __construct(Subscription $model)
    {
        parent::__construct($model);
    }
}
