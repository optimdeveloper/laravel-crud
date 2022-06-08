<?php


namespace App\Repositories;

use App\Repositories\Base\BaseWithUserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

interface EventRepositoryInterface extends BaseWithUserRepositoryInterface
{
    public function list_with_detail(): Collection;
    public function find_with_detail($id): Collection; // event, user, photo, promote_event
    public function list_filter($filter, $time=null): Collection;
    public function list_total_filter(): Collection;
    public function list_search($name=null, $location=null, $dates=null, $type=null, $category=null, $orderby=0): Collection;
    public function put_events_totals_and_img($query, $onlyEvent = false, $castarray = false);
    public function set_date_timezone($query, $onlyEvent = false);
    public function put_final_date($query, $onlyEvent = false);
    public function get_event_responses($eventid, $filter, $orderby, $search);
}
