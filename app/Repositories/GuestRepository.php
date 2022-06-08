<?php


namespace App\Repositories;

use App\Models\Guest;
use App\Repositories\Base\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class GuestRepository extends BaseRepository implements GuestRepositoryInterface
{
    public function __construct(Guest $model)
    {
        parent::__construct($model);
    }

    public function search_guest($event_id, $user_id): ?Model {
        return $this->model->where('event_id', $event_id)->where('user_id', $user_id)->first();
    }

    public function get_by_event($event_id, $limit = null): Collection {

        $query = $this->model->where('event_id', $event_id);
        
        if(!is_null($limit)) {
            $query->skip(0)->take($limit);
        }

        return $query->get();
    }

    public function get_by_event_and_status($event_id, $status, $limit = null): Collection {

        $query = $this->model->where('event_id', $event_id)
        ->where('status', 'like', $status);

        if(!is_null($limit)) {
            $query->skip(0)->take($limit);
        }

        return $query->get();
    }

    public function get_user_relevance($user_id): int {

        $query = $this->model->where('user_id', $user_id)->get();

        return count($query);
    }

    public function get_by_user_and_status($user_id, $status, $limit = null): Collection {

        $query = $this->model->where('user_id', $user_id)
        ->where('status', 'like', $status);

        if(!is_null($limit)) {
            $query->skip(0)->take($limit);
        }

        return $query->get();
    }
}
