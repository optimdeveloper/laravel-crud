<?php


namespace App\Repositories;

use App\Models\UserToPassion;
use App\Repositories\Base\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class UserToPassionRepository extends BaseRepository implements UserToPassionRepositoryInterface
{
    public function __construct(UserToPassion $model)
    {
        parent::__construct($model);
    }


    public function find_passion($id_passion, $id_user): bool{
        $passion = $this->model->where('passion_id', $id_passion)->where('user_id', $id_user)->get();
        if (count($passion) > 0)
            return false;

        return true;
    }

    public function find_with_detail($id): Collection {
        //return $this->model->where('user_id', $id)->with('user')->get();
        return $this->model->where('user_id', $id)->with('passions')->get(); //we dont need the user detail here for now...
    }

    public function find_passions($user_id): Collection {
        return $this->model->where('user_id', $user_id)->get();
    }
}
