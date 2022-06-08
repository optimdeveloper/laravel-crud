<?php


namespace App\Repositories;

use App\Models\Event;
use App\Models\PersonalFilter;
use App\Models\User;
use App\Repositories\Base\BaseRepository;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

//Models.
use App\Models\Subscription;
use App\Models\UserPhoto;
use App\Models\UserProfile;
use App\Models\UserToPassion;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function list_with_detail(): Collection
    {
        return $this->model::with(
            'user_profile',
            'personal_filter',
            'user_photos',
            'events',
            'subscription'
        )->get();
    }

    public function find_with_detail($id): Collection
    {
        return $this->model->where('id', $id)->with(
            'user_profile',
            'personal_filter',
            'user_photos',
            'subscription',
            'user_to_passions'
        )->get();
    }
}
