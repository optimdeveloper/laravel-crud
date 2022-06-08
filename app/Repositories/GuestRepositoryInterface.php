<?php


namespace App\Repositories;

use App\Repositories\Base\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Ramsey\Uuid\Type\Integer;

interface GuestRepositoryInterface extends BaseRepositoryInterface
{
    public function search_guest($event_id, $user_id): ?Model;
    public function get_by_event($event_id, $limit = null): Collection;
    public function get_by_event_and_status($event_id, $status, $limit = null): Collection;
    public function get_user_relevance($user_id): int;
    public function get_by_user_and_status($user_id, $status, $limit = null): Collection;
}
