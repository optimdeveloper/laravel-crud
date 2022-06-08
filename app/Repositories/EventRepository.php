<?php


namespace App\Repositories;

use App\Helpers\DistanceHelper;
use App\Models\Event;
use App\Repositories\Base\BaseWithUserRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\PhotoEvent;
use App\Models\Guest;
use App\Helpers\MediaHelper;
use App\Models\UserPhoto;
use App\Repositories\GuestRepositoryInterface;
use App\Repositories\UserContactRepositoryInterface;

class EventRepository extends BaseWithUserRepository implements EventRepositoryInterface
{
    private GuestRepositoryInterface $guestRepository;
    private UserContactRepositoryInterface $userContactRepository;

    public function __construct(Event $model, GuestRepositoryInterface $guestRepository, UserContactRepositoryInterface $userContactRepository)
    {
        parent::__construct($model);
        $this->guestRepository =  $guestRepository;
        $this->userContactRepository = $userContactRepository;
    }

    public function list_with_detail(): Collection
    {
        return $this->model::with('user', 'photo_event', 'promote_event')->get();
    }

    public function find_with_detail($id): Collection
    {
        return $this->model->where('id', $id)->with('user', 'photo_event', 'promote_event', 'filter_to_events')->get();
    }

    public function list_total_filter(): Collection
    {
        // Total por evento para Going, Interested, Invited, Maybe *****************************************************************
        $events = $this->model->all();
        // ->paginate(
        //     $perPage = 2, $columns = ['*'], $pageName = 'users', $currentPage = 2
        // );
        // dd($events);

        foreach ($events as $key => $event_id) {

            $guest = [
                (object)[
                    "status" => "Going",
                    "total" => 0
                ],
                (object)[
                    "status" => "Maybe",
                    "total" => 0
                ],
                (object)[
                    "status" => "Invited",
                    "total" => 0
                ],
                (object)[
                    "status" => "Interested",
                    "total" => 0
                ],
            ];

            $guests_list = DB::select(
                'select
                g.status,
                count(event_id) as total
                from guests g
                where g.event_id = ' . $event_id->id . '
                group by g.status;'
            );
            foreach ($guests_list as $guest_list) {
                if ($guest[0]->status == $guest_list->status) $guest[0]->total = $guest_list->total;
                if ($guest[1]->status == $guest_list->status) $guest[1]->total = $guest_list->total;
                if ($guest[2]->status == $guest_list->status) $guest[2]->total = $guest_list->total;
                if ($guest[3]->status == $guest_list->status) $guest[3]->total = $guest_list->total;
            }
            $event_id->status = $guest;
        }

        return $events;
    }

    // Filtrado avanzado
    public function list_search($name = null, $location = null, $dates = null, $type = null, $category = null, $orderby = 0): Collection
    {
        $timezone = Request()->header()['timezone'][0];

        $queryName = '';
        $queryType = '';
        $queryDates = '';


        if (isset($name)) {
            $queryName = 'AND e.name LIKE "%' . $name . '%"';
        }

        if (isset($type)) {
            if ($type != "All types")
                $queryType = 'AND e.type = "' . $type . '"';
        }

        if (isset($dates)) {
            if ($dates == "All dates") $queryDates = 'WHERE published = 1 and deleted_at is null and e.date_time >= "' . Carbon::now($timezone) . '"';
            if ($dates == "Today") $queryDates = 'WHERE published = 1 and deleted_at is null and e.date_time >= "' . Carbon::now($timezone) . '" AND e.date_time < "' . Carbon::tomorrow($timezone) . '"';
            if ($dates == "Tomorrow") $queryDates = 'WHERE published = 1 and deleted_at is null and e.date_time >= "' . Carbon::tomorrow($timezone) . '" AND e.date_time < "' . Carbon::tomorrow($timezone)->addDay() . '"';
            if ($dates == "This Week") $queryDates = 'WHERE published = 1 and deleted_at is null and e.date_time >= "' . Carbon::now($timezone) . '" AND e.date_time < "' . Carbon::now($timezone)->endOfWeek(Carbon::SATURDAY) . '"';
            if ($dates == "This Weekend") $queryDates = 'WHERE published = 1 and deleted_at is null and e.date_time >= "' . Carbon::now($timezone)->endOfWeek(Carbon::SATURDAY) . '" AND e.date_time < "' . Carbon::now($timezone)->endOfWeek(Carbon::SATURDAY)->addDays(2) . '"';
            if ($dates == "Next Week") $queryDates = 'WHERE published = 1 and deleted_at is null and e.date_time >= "' . Carbon::now($timezone)->startOfWeek(Carbon::SUNDAY)->addWeek() . '" AND e.date_time < "' . Carbon::now($timezone)->endOfWeek(Carbon::SATURDAY)->addWeek() . '"';
            $date = explode("*", $dates);
            if (count($date) == 2) $queryDates = 'WHERE e.date_time >= "' . $date[0] . '" AND e.date_time < "' . $date[1] . '"';
        }

        $temp_events = DB::select(
            '

            select
            *
            from events e
            ' . $queryDates . '
            ' . $queryName . '
            ' . $queryType . '

            '
        );

        $temp_events_id = [];

        if (isset($location)) {
            [$local_lat, $local_long] = explode('*', $location);
            $location_events = [];
            $nearby = env('NEARBY', 1000);
            foreach ($temp_events as $temp_event) {
                $event_lat = $temp_event->latitude;
                $event_long = $temp_event->longitude;
                $distance = DistanceHelper::calculate_distance(doubleval($local_lat), doubleval($local_long), $event_lat, $event_long);

                if ($distance <= $nearby) {
                    $location_events[] = (array)$temp_event;
                }
            }
            $temp_events = $location_events;
            $temp_events_id = array_column($temp_events, 'id');
        }

        if (isset($category) && count($category) > 0) {
            $id_events = DB::table('filter_to_events')
                ->select(['event_id'])
                ->whereIn('event_id', $temp_events_id)
                ->whereIn('filter_id', $category)
                ->get()->toArray();

            $events_id = (array_unique(array_column($id_events, 'event_id')));
            if (count($events_id) > 0) {
                //$temp_events = array_intersect_key($temp_events, $events_id);
                $temp_events = array_filter(
                    $temp_events,
                    function ($key) use ($events_id) {
                        return in_array($key['id'], $events_id);
                    },
                    ARRAY_FILTER_USE_BOTH
                );
            }
        }

        $events = array_values($temp_events);
        $events = $this->put_events_totals_and_img($events, false, true);
        $events = $this->put_final_date($events);
        $events = $this->set_date_timezone($events);

        if ($orderby == 1) //order by relevance
        {
            usort($events, function ($a, $b) {
                return $a['total_going'] < $b['total_going'];
            });
        }

        if ($orderby == 2) //order by start time
        {
            usort($events, function ($a, $b) {
                return $a['date_time'] > $b['date_time'];
            });
        }

        return new Collection($events);
    }

    public function list_filter($filter, $time = ''): Collection
    {
        $timezone = Request()->header()['timezone'][0];
        $events = new Collection();
        $guests = new Collection();
        $events_list_id = [];
        // Filter Interested or Going **********************************************************
        if (strtolower($filter) == "going" || strtolower($filter) == "interested" || strtolower($filter) == "maybe" ||  strtolower($filter) == "invited") {
            if ($time == '') {

                $events_id = DB::select(
                    'select
                    g.event_id
                    from guests g
                    where g.user_id = ' . Auth::id() . ' and g.status = "' . $filter . '"'
                );

                foreach ($events_id as $event_id) {
                    $events[] = $this->model->find($event_id->event_id);
                }
            } else {
                if (strtolower($time)  == "current") {
                    $events_id = DB::select(
                        'select
                        g.event_id
                        from guests g
                        where g.user_id = ' . Auth::id() . ' and g.status = "' . $filter . '"'
                    );

                    foreach ($events_id as $event_id) {
                        $temp_event = $this->model
                            ->where('id', $event_id->event_id)
                            ->where('date_time', '>=', Carbon::now($timezone))
                            ->first();
                        if ($temp_event != null)
                            $events[] = $temp_event;
                    }
                }
                if (strtolower($time)  == "past") {
                    $events_id = DB::select(
                        'select
                        g.event_id
                        from guests g
                        where g.user_id = ' . Auth::id() . ' and g.status = "' . $filter . '"'
                    );

                    foreach ($events_id as $event_id) {
                        $temp_event = $this->model
                            ->where('id', $event_id->event_id)
                            ->where('date_time', '<', Carbon::now($timezone))
                            ->first();
                        if ($temp_event != null)
                            $events[] = $temp_event;
                    }
                }
            }
        }


        // Filter contact **********************************************************************
        if (strtolower($filter) == "contact") {
            $users_id = DB::select(
                'select
                uc.contact_id
                from user_contacts uc
                where uc.user_id = ' . Auth::id() . ' and uc.status = "Unblock" '
            );
            foreach ($users_id as $user_id) {
                $guests[] = DB::select(
                    'select
                    g.event_id
                    from guests g
                    where g.status != "Invited" and g.user_id = ' . $user_id->contact_id . ''
                );
            }
            foreach ($guests  as $user_event) {
                foreach ($user_event as $event_id) {
                    $events_list_id[] = $event_id->event_id;
                }
            }
            $events_list_id = array_unique($events_list_id);
            foreach ($events_list_id as $event_id) {
                $events[] = $this->model->find($event_id);
            }
        }

        // Filter Top **************************************************************************
        if (strtolower($filter) == "top") {
            $events_id = DB::select(
                'select
                g.event_id
                from guests g
                where g.status = "Going" or g.status = "Interested"
                group by g.event_id
                order by count(g.status) desc
                limit 10'
            );

            foreach ($events_id as $event_id) {
                $temp_event = $this->model
                    ->where('id', $event_id->event_id)
                    ->where('date_time', '>=', Carbon::now($timezone))
                    ->first();
                if ($temp_event != null)
                    $events[] = $temp_event;
            }
        }

        // Filter Week **************************************************************************
        if (strtolower($filter) == "week") {
            $now = Carbon::now($timezone);
            // $start = strval($now->startOfWeek(Carbon::SUNDAY));
            $start = strval($now);
            $end = strval($now->endOfWeek(Carbon::SATURDAY));
            $events = $this->model
                ->whereBetween('date_time', [$start, $end])
                ->get();
        }

        // Filter Today **************************************************************************
        if (strtolower($filter) == "today") {
            $events_id = DB::select(
                'select
                g.event_id
                from guests g
                where g.status = "Going" or g.status = "Interested"
                group by g.event_id
                order by count(g.status) desc
                limit 10'
            );
            // dd(Carbon::now());
            foreach ($events_id as $event_id) {
                $temp_event = $this->model
                    ->where('id', $event_id->event_id)
                    ->where('date_time', '>=', Carbon::now($timezone))
                    ->where('date_time', '<', Carbon::tomorrow())
                    ->first();
                if ($temp_event != null)
                    $events[] = $temp_event;
            }
        }

        return $events;
    }

    private function get_event_totals($id): Collection
    {
        $event = $this->model->where('id', $id)->withTrashed()->get();
        foreach ($event as $key => $item) {
            $guest = [
                (object)[
                    "status" => "Going",
                    "total" => 0
                ],
                (object)[
                    "status" => "Maybe",
                    "total" => 0
                ],
                (object)[
                    "status" => "Invited",
                    "total" => 0
                ],
                (object)[
                    "status" => "Interested",
                    "total" => 0
                ],
            ];

            $guests_list = DB::select(
                'select
                g.status,
                count(event_id) as total
                from guests g
                where g.event_id = ' . $item->id . '
                group by g.status;'
            );
            foreach ($guests_list as $guest_list) {
                if ($guest[0]->status == $guest_list->status) $guest[0]->total = $guest_list->total;
                if ($guest[1]->status == $guest_list->status) $guest[1]->total = $guest_list->total;
                if ($guest[2]->status == $guest_list->status) $guest[2]->total = $guest_list->total;
                if ($guest[3]->status == $guest_list->status) $guest[3]->total = $guest_list->total;
            }
            $item->status = $guest;
        }
        return $event;
    }

    public function put_events_totals_and_img($query, $onlyEvent = false, $castarray = false)
    {
        if ($onlyEvent == false) {
            foreach ($query as &$ev) {
                if ($castarray)
                    $ev = (array)$ev;
                $isgoing = Guest::where('user_id', Auth::id())->where('event_id', $ev['id'])->where('status', 'Going')->first();
                $ismaybe = Guest::where('user_id', Auth::id())->where('event_id', $ev['id'])->where('status', 'Maybe')->first();
                $isinvited = Guest::where('user_id', Auth::id())->where('event_id', $ev['id'])->where('status', 'Invited')->first();
                $isinterested = Guest::where('user_id', Auth::id())->where('event_id', $ev['id'])->where('status', 'Interested')->first();
                $going_id = -99;
                $maybe_id = -99;
                $invited_id = -99;
                $interested_id = -99;

                if (isset($isgoing)) {
                    $going_id = $isgoing->id;
                    $isgoing = "yes";
                } else
                    $isgoing = "no";

                if (isset($ismaybe)) {
                    $maybe_id = $ismaybe->id;
                    $ismaybe = "yes";
                } else
                    $ismaybe = "no";

                if (isset($isinvited)) {
                    $invited_id = $isinvited->id;
                    $isinvited = "yes";
                } else
                    $isinvited = "no";

                if (isset($isinterested)) {
                    $interested_id = $isinterested->id;
                    $isinterested = "yes";
                } else
                    $isinterested = "no";

                $image_name = PhotoEvent::where('event_id', $ev['id'])->first();
                $image_id = -99;
                if (isset($image_name)) {
                    $image_id = $image_name->id;
                    $image_name = MediaHelper::getImageUrl(MediaHelper::getEventPath(), $image_name->path);
                } else
                    $image_name = '';
                $ev['image_id'] = $image_id;
                $ev['image_url'] = $image_name;
                $totals = $this->get_event_totals($ev['id']);
                $ev['total_going'] = $totals[0]->status[0]->total;
                $ev['total_maybe'] = $totals[0]->status[1]->total;
                $ev['total_invited'] = $totals[0]->status[2]->total;
                $ev['total_interested'] = $totals[0]->status[3]->total;
                $ev['going'] = $isgoing;
                $ev['maybe'] = $ismaybe;
                $ev['invited'] = $isinvited;
                $ev['interested'] = $isinterested;
                $ev['going_id'] = $going_id;
                $ev['maybe_id'] = $maybe_id;
                $ev['invited_id'] = $invited_id;
                $ev['interested_id'] = $interested_id;
                $ev['event_link'] = 'https://myngly.com'; //TODO:
            }
            return $query;
        } else {
            $ev = $query;
            if ($castarray)
                $ev = (array)$ev;
            $isgoing = Guest::where('user_id', Auth::id())->where('event_id', $ev['id'])->where('status', 'Going')->first();
            $ismaybe = Guest::where('user_id', Auth::id())->where('event_id', $ev['id'])->where('status', 'Maybe')->first();
            $isinvited = Guest::where('user_id', Auth::id())->where('event_id', $ev['id'])->where('status', 'Invited')->first();
            $isinterested = Guest::where('user_id', Auth::id())->where('event_id', $ev['id'])->where('status', 'Interested')->first();
            $going_id = -99;
            $maybe_id = -99;
            $invited_id = -99;
            $interested_id = -99;

            if (isset($isgoing)) {
                $going_id = $isgoing->id;
                $isgoing = "yes";
            } else
                $isgoing = "no";

            if (isset($ismaybe)) {
                $maybe_id = $ismaybe->id;
                $ismaybe = "yes";
            } else
                $ismaybe = "no";

            if (isset($isinvited)) {
                $invited_id = $isinvited->id;
                $isinvited = "yes";
            } else
                $isinvited = "no";

            if (isset($isinterested)) {
                $interested_id = $isinterested->id;
                $isinterested = "yes";
            } else
                $isinterested = "no";
            $image_name = PhotoEvent::where('event_id', $ev['id'])->first();
            $image_id = -99;
            if (isset($image_name)) {
                $image_id = $image_name->id;
                $image_name = MediaHelper::getImageUrl(MediaHelper::getEventPath(), $image_name->path);
            } else
                $image_name = '';
            $ev['image_id'] = $image_id;
            $ev['image_url'] = $image_name;
            $totals = $this->get_event_totals($ev['id']);
            $ev['total_going'] = $totals[0]->status[0]->total;
            $ev['total_maybe'] = $totals[0]->status[1]->total;
            $ev['total_invited'] = $totals[0]->status[2]->total;
            $ev['total_interested'] = $totals[0]->status[3]->total;
            $ev['going'] = $isgoing;
            $ev['maybe'] = $ismaybe;
            $ev['invited'] = $isinvited;
            $ev['interested'] = $isinterested;
            $ev['going_id'] = $going_id;
            $ev['maybe_id'] = $maybe_id;
            $ev['invited_id'] = $invited_id;
            $ev['interested_id'] = $interested_id;
            $ev['event_link'] = 'https://myngly.com'; //TODO:
            return $ev;
        }
    }

    public function set_date_timezone($query, $onlyEvent = false)
    {

        $timezone = Request()->header()['timezone'][0];

        if ($onlyEvent == false) {
            foreach ($query as &$ev) {

                $date = $ev['date_time']; //utc from database

                if (!is_string($date)) {
                    $date = strval($date);
                }

                $utc_date = Carbon::createFromFormat('Y-m-d H:i:s', $date, 'UTC');

                $timezone_date = $utc_date->setTimezone($timezone);

                $ev['date_time'] = strval($timezone_date);
            }
            return $query;
        } else {
            $ev = $query;
            $date = $ev['date_time']; //utc from database

            if (!is_string($date)) {
                $date = strval($date);
            }

            $utc_date = Carbon::createFromFormat('Y-m-d H:i:s', $date, 'UTC');

            $timezone_date = $utc_date->setTimezone($timezone);

            $ev['date_time'] = strval($timezone_date);


            return $ev;
        }
    }

    public function put_final_date($query, $onlyEvent = false)
    {

        $timezone = Request()->header()['timezone'][0];
        if ($onlyEvent == false) {
            foreach ($query as &$ev) {

                $duration = $ev['duration'];
                $date = $ev['date_time'];

                $duration_array = explode(":", $duration);

                $days = 0;
                $hours = 0;
                $minutes = 0;

                if (sizeof($duration_array) > 2) {
                    $days = intval($duration_array[0]);
                    $hours = intval($duration_array[1]);
                    $minutes = intval($duration_array[2]);
                }

                $final_date = Carbon::parse($date)->addDays($days)->addHours($hours)->addMinutes($minutes)->format('Y-m-d H:i:s');

                $carbon_date = Carbon::createFromFormat('Y-m-d H:i:s', $final_date, 'UTC');
                $true_final_date = $carbon_date->setTimezone($timezone);

                $ev['final_date'] = strval($true_final_date);
            }
            return $query;
        } else {
            $ev = $query;
            $duration = $ev['duration'];
            $date = $ev['date_time'];

            $duration_array = explode(":", $duration);

            $days = 0;
            $hours = 0;
            $minutes = 0;

            if (sizeof($duration_array) > 2) {
                $days = intval($duration_array[0]);
                $hours = intval($duration_array[1]);
                $minutes = intval($duration_array[2]);
            }

            $final_date = Carbon::parse($date)->addDays($days)->addHours($hours)->addMinutes($minutes)->format('Y-m-d H:i:s');

            $carbon_date = Carbon::createFromFormat('Y-m-d H:i:s', $final_date, 'UTC');
            $true_final_date = $carbon_date->setTimezone($timezone);

            $ev['final_date'] = strval($true_final_date);

            return $ev;
        }
    }

    public function get_event_responses($eventid, $filter, $orderby, $search)
    {
        if ($search !== null && $search !== '') {
            $resp = Guest::select('users.id', 'users.name', 'users.email', 'users.phone_number', 'users.birthday_at', 'users.gender', 'user_profiles.work', 'guests.id as guests_id', 'guests.status')
                ->leftjoin('users', 'users.id', '=', 'guests.user_id')
                ->leftjoin('user_profiles', 'user_profiles.user_id', '=', 'guests.user_id')
                ->where('guests.event_id', $eventid)
                ->Where('users.name', 'like', '%' . $search . '%')
                ->orderBy('guests.id', 'ASC')
                ->get();
        } else {
            $resp = Guest::select('users.id', 'users.name', 'users.email', 'users.phone_number', 'users.birthday_at', 'users.gender', 'user_profiles.work', 'guests.id as guests_id', 'guests.status')
                ->leftjoin('users', 'users.id', '=', 'guests.user_id')
                ->leftjoin('user_profiles', 'user_profiles.user_id', '=', 'guests.user_id')
                ->where('guests.event_id', $eventid)
                ->orderBy('guests.id', 'ASC')
                ->get();
        }

        if ($filter !== null && $filter !== '')
            $resp = $resp->where('status', $filter);

        foreach ($resp as &$ev) {
            $imageuser = UserPhoto::where('user_id', $ev['id'])->orderBy('id', 'ASC')->first();
            if (isset($imageuser))
                $imageuser = MediaHelper::getImageUrl(MediaHelper::getUserPath(), $imageuser->name);
            else
                $imageuser = '';
            $ev['image_url'] = $imageuser;
            $relevance = $this->guestRepository->get_user_relevance($ev->id);
            $ev['relevance'] = $relevance;
            $is_blocked = $this->userContactRepository->user_is_blocked($ev->id);
            if (isset($is_blocked))
                $is_blocked = "yes";
            else
                $is_blocked = "no";
            $ev['is_blocked'] = $is_blocked;
        }

        if ($orderby === null)
            $resp = $resp->values();
        else if ($orderby === 0) {
            // $col = 'birthday_at';
            $resp = $resp->sortByDesc(function ($item) {
                return $item->birthday_at;
            })->values();
        } else if ($orderby === 1) {
            // $col = 'name';
            $resp = $resp->sortBy(function ($item) {
                return $item->name;
            })->values();
        } else if ($orderby === 2) {
            //Ordenar por relevancia (el que tenga mas count user_id en la tabla guests)
            // $col = 'relevance';
            $resp = $resp->sortByDesc(function ($item) {
                return $item->relevance;
            })->values();
        }
        return $resp;
    }
}
