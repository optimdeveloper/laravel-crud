<?php


namespace App\Repositories;

use App\Models\Matchs;
use App\Repositories\Base\BaseRepository;
use Illuminate\Database\Eloquent\Collection;

class MatchsRepository extends BaseRepository implements MatchsRepositoryInterface
{
    public function __construct(Matchs $model)
    {
        parent::__construct($model);
    }

    public function find_by_users($user_one_id, $user_two_id): Collection
    {
        return $this->model->where('user_one_id', $user_two_id)
                          ->where('user_two_id', $user_one_id)
                          ->get();
    }

    public function find_by_user($user_id): Collection
    {
        return $this->model->where('user_one_id', $user_id)
                          ->orWhere('user_two_id', $user_id)
                          ->get();
    }

    public function find_by_user_not_rejected($user_id, $order = null): Collection
    {
        $query = $this->model
        ->where('status', '!=', 2)
        ->where(function ($query) use ($user_id) {
            $query->where("user_one_id", "=", $user_id)
                ->orWhere('user_two_id', "=", $user_id);
        });

        if(!is_null($order)) {
            if($order == 0) {
                $query->orderBy('updated_at', 'asc');
            }
            elseif($order == 1) {
                $query->orderBy('updated_at', 'desc');
            }
        }
        else {
            $query->orderBy('status', 'asc')->orderBy('updated_at', 'desc');
        }

        return $query->get();
    }

    public function find_by_user_not_rejected_sent($user_id, $order = null): Collection
    {
        $query = $this->model
        ->where('status', '!=', 2)
        ->where("user_one_id", "=", $user_id);

        if(!is_null($order)) {
            if($order == 0) {
                $query->orderBy('updated_at', 'asc');
            }
            elseif($order == 1) {
                $query->orderBy('updated_at', 'desc');
            }
        }
        else {
            $query->orderBy('status', 'asc')->orderBy('updated_at', 'desc');
        }

        return $query->get();
    }

    public function find_by_user_not_rejected_received($user_id, $order = null): Collection
    {
        $query = $this->model
        ->where('status', '!=', 2)
        ->where('user_two_id', "=", $user_id);

        if(!is_null($order)) {
            if($order == 0) {
                $query->orderBy('updated_at', 'asc');
            }
            elseif($order == 1) {
                $query->orderBy('updated_at', 'desc');
            }
        }
        else {
            $query->orderBy('status', 'asc')->orderBy('updated_at', 'desc');
        }

        return $query->get();
    }

}
