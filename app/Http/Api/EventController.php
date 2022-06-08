<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use App\Core\ApiCodeEnum;
use App\Helpers\DistanceHelper;
use App\Helpers\GoogleStorageHelper;
use App\Http\Api\Base\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\FilterToEvent;
use App\Models\PhotoEvent;
use App\Repositories\EventRepositoryInterface;
use App\Repositories\PromoteEventRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Services\LogServiceInterface;
use Geocoder\Laravel\Facades\Geocoder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Helpers\MediaHelper;
use DateTime;
use Throwable;
use App\Repositories\GuestRepositoryInterface;

class EventController extends ApiController
{
    private EventRepositoryInterface $eventRepository;
    private UserRepositoryInterface $userRepository;
    private PromoteEventRepositoryInterface $promoteEventRepository;
    private GuestRepositoryInterface $guestRepository;

    public function __construct(
        EventRepositoryInterface $eventRepository,
        LogServiceInterface $logger,
        UserRepositoryInterface $userRepository,
        PromoteEventRepositoryInterface $promoteEventRepository,
        GuestRepositoryInterface $guestRepository
    ) {
        parent::__construct($logger);
        $this->eventRepository =  $eventRepository;
        $this->userRepository = $userRepository;
        $this->promoteEventRepository = $promoteEventRepository;
        $this->guestRepository =  $guestRepository;
    }

    public function me($filter = null): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();
        try {
            if ($filter == null)
                $query = $this->eventRepository->me(Auth::id(), "promote_event");
            else
                $query = $this->eventRepository->me(Auth::id(), "promote_event", $filter);

            $this->eventRepository->put_events_totals_and_img($query);
            $this->eventRepository->put_final_date($query);
            $this->eventRepository->set_date_timezone($query);
            $response->setData($query);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }


    public function list($filter = '', $time = ''): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();
        try {
            if ($filter == '') {
                $timezone = Request()->header()['timezone'][0];
                $query = Event::where('date_time', '>=', Carbon::now($timezone))->orderBy('date_time', 'ASC')->get();
                $this->eventRepository->put_events_totals_and_img($query);
                $this->eventRepository->put_final_date($query);
                $this->eventRepository->set_date_timezone($query);
                $response->setData($query);
            } else {
                if ($time == '') {
                    $query = $this->eventRepository->list_filter($filter);
                } else {
                    $query = $this->eventRepository->list_filter($filter, $time);
                }
                $this->eventRepository->put_events_totals_and_img($query);
                $this->eventRepository->put_final_date($query);
                $this->eventRepository->set_date_timezone($query);
                $response->setData($query);
            }
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }
        return response()->json($response);
    }

    // Filtro de busqueda avanzada
    public function list_search(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'string|nullable',
                'location' => 'string|nullable',
                'dates' => 'string',
                'type' => 'string',
                'category' => 'array'
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            $query = $this->eventRepository->list_search($request->name, $request->location, $request->dates, $request->type, $request->category, $request->orderby);
            $response->setData($query);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    // list events NEARBY
    public function list_nearby($local_lat, $local_long): JsonResponse
    {
        // dd($local_lat);
        $events = new Collection();
        $nearby = env('NEARBY', 1000);
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->eventRepository->all();
            $this->eventRepository->put_events_totals_and_img($query);
            $this->eventRepository->put_final_date($query);
            $this->eventRepository->set_date_timezone($query);
            foreach ($query as $event) {
                $event_lat = $event->latitude;
                $event_long = $event->longitude;
                $distance = DistanceHelper::calculate_distance($local_lat, $local_long, $event_lat, $event_long);
                // dd($nearby);

                if ($distance <= $nearby) {
                    $events[] = $event;
                }
            }

            $response->setData($events);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function list_with_user(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->eventRepository->list_with_user();
            $this->eventRepository->put_events_totals_and_img($query);
            $this->eventRepository->put_final_date($query);
            $this->eventRepository->set_date_timezone($query);
            $response->setData($query);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function list_total_filter(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->eventRepository->list_total_filter();
            $this->eventRepository->put_events_totals_and_img($query);
            $this->eventRepository->put_final_date($query);
            $this->eventRepository->set_date_timezone($query);
            $response->setData($query);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function list_detail(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->eventRepository->list_with_detail();
            $this->eventRepository->put_events_totals_and_img($query);
            $this->eventRepository->put_final_date($query);
            $this->eventRepository->set_date_timezone($query);
            $response->setData($query);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function find($id): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->eventRepository->find($id);
            if (!isset($query)) {
                $response->setError('Event not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }
            $this->eventRepository->put_events_totals_and_img($query, true);
            $this->eventRepository->put_final_date($query, true);
            $this->eventRepository->set_date_timezone($query, true);
            $response->setData($query);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function find_with_user($id): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->eventRepository->find($id);
            if (!isset($query)) {
                $response->setError('Event not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }
            $query = $this->eventRepository->find_with_user($id);
            $this->eventRepository->put_events_totals_and_img($query);
            $this->eventRepository->put_final_date($query);
            $this->eventRepository->set_date_timezone($query);
            $response->setData($query);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }
        return response()->json($response);
    }

    public function find_detail($id): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->eventRepository->find($id);
            if (!isset($query)) {
                $response->setError('Event not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }
            $query = $this->eventRepository->find_with_detail($id);
            $this->eventRepository->put_events_totals_and_img($query);
            $this->eventRepository->put_final_date($query);
            $this->eventRepository->set_date_timezone($query);
            $response->setData($query);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }
        return response()->json($response);
    }

    // soft delete and restore.
    public function soft_remove($id): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->eventRepository->deleteLogic($id);
            if ($query == true) {
                $response->setMessage('Event deleted Successfully!');
            } else {
                $response->setError('Something went wrong!');
                return response()->json($response);
            }
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function publish_event(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $user = $this->userRepository->find(Auth::id());
            if (!isset($user)) {
                $response->setError('User not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            if ($request->get('event_id') != null) {
                $db = $this->eventRepository->find($request->get('event_id'));
                if (!isset($db)) {
                    $response->setError('Event not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                } else {
                    $db->published = $request->get('publish');
                    if ($this->eventRepository->save($db)) {
                        $this->eventRepository->put_events_totals_and_img($db, true);
                        $this->eventRepository->put_final_date($db, true);
                        $this->eventRepository->set_date_timezone($db, true);
                        $response->setData($db);
                        $response->setCode(ApiCodeEnum::SUCCESS);
                    } else {
                        $response->setError('Something went wrong!');
                        return response()->json($response);
                    }
                }
            } else {
                $response->setError('Event not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function soft_restore($id): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->eventRepository->restore($id);
            if ($query == true) {
                $response->setMessage('Event restored Successfully!');
            } else {
                $response->setError('Something went wrong!');
                return response()->json($response);
            }
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    // Create and update
    public function add_update(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'id' => 'nullable|integer',
                'name' => 'required|string|max:100',
                'date_time' => 'required',
                'duration' => 'required',
                'privacy' => ['required', 'string', Rule::in(['Private', 'Public'])],
                'price' => 'required|numeric',
                'attendee_limit' => 'required|integer',
                'focused_on_gender' => ['required', 'string', Rule::in(['Women', 'Men', 'Everyone'])],
                'focused_on_age_range' => 'required|string|max:7',
                'recurrence' => ['required', 'string', Rule::in(['No Repeat', 'Daily', 'Weekly', 'Monthly'])],
                'location' => 'required|string',
                'longitude' => 'required|numeric',
                'latitude' => 'required|numeric',
                'description' => 'required|string',
                'published' => 'required|boolean',
                // 'user_id' => 'required|integer',
                'promote_event_id' => 'nullable|integer',
                'type' => 'required',
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            $user = $this->userRepository->find(Auth::id());
            if (!isset($user)) {
                $response->setError('User not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            if ($request->get('promote_event_id') != null) {
                $promoteEvent = $this->promoteEventRepository->find($request->get('promote_event_id'));
                if (!isset($promoteEvent)) {
                    $response->setError('Promote Event not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
            }

            if ($request->get('id') != null) {
                $db = $this->eventRepository->find($request->get('id'));
                if (!isset($db)) {
                    $response->setError('App not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
            } else {
                $db = new Event();
            }

            $input_date_time = $request->date_time;
            $timezone = Request()->header()['timezone'][0];

            $carbon_date_time = Carbon::createFromFormat('Y-m-d H:i:s', $input_date_time, $timezone);
            $utc_date_time = $carbon_date_time->setTimezone('UTC');

            $db->name = $request->name;
            $db->date_time = $utc_date_time;
            $db->duration = $request->duration;
            $db->privacy = $request->privacy;
            $db->price = $request->price;
            $db->attendee_limit = $request->attendee_limit;
            $db->focused_on_gender = $request->focused_on_gender;
            $db->focused_on_age_range = $request->focused_on_age_range;
            $db->recurrence = $request->recurrence;
            $db->location = $request->location;
            $db->longitude = $request->longitude;
            $db->latitude = $request->latitude;
            $db->description = $request->description;
            $db->published = $request->published;
            $db->user_id = Auth::id();
            $db->promote_event_id = $request->promote_event_id;
            $db->type = $request->type;

            if ($this->eventRepository->save($db)) {
                $this->eventRepository->put_events_totals_and_img($db, true);
                $this->eventRepository->put_final_date($db, true);
                $this->eventRepository->set_date_timezone($db, true);
                $response->setData($db);
                $response->setCode(ApiCodeEnum::CREATED);
            } else {
                $response->setError('Something went wrong!');
                return response()->json($response);
            }
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }


    public function search_geocode(Request $request)
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $geocodes = Geocoder::geocode($request->address)->get();

            if ($geocodes->count() > 0) {
                $geocode = $geocodes->first();
                $response->setData(collect([
                    'latitude' => $geocode->getCoordinates()->getLatitude(),
                    'longitude' => $geocode->getCoordinates()->getLongitude(),
                ]));
                $response->setMessage('Address Successfully!');
            } else {
                $response->setError('Address not found!');
                return response()->json($response);
            }
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }


    public function copy(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->eventRepository->find($request->event_id);
            if (!isset($query)) {
                $response->setError('Event not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }
            $query = $this->eventRepository->find_with_detail($request->event_id);
            // dd(isset($query[0]->filter_to_events));
            // dd(count($query[0]->filter_to_events));

            $db = new Event();
            $db->name = $query[0]->name;
            $db->date_time = $query[0]->date_time;
            $db->duration = $query[0]->duration;
            $db->privacy = $query[0]->privacy;
            $db->price = $query[0]->price;
            $db->attendee_limit = $query[0]->attendee_limit;
            $db->focused_on_gender = $query[0]->focused_on_gender;
            $db->focused_on_age_range = $query[0]->focused_on_age_range;
            $db->recurrence = $query[0]->recurrence;
            $db->location = $query[0]->location;
            $db->longitude = $query[0]->longitude;
            $db->latitude = $query[0]->latitude;
            $db->description = $query[0]->description;
            $db->published = $query[0]->published;
            $db->user_id = $query[0]->user_id;
            $db->promote_event_id = $query[0]->promote_event_id;
            $db->type = $query[0]->type;

            if ($this->eventRepository->save($db)) {
                if (isset($query[0]->photo_event)) {
                    $name = Str::random(44) . ".png";
                    $path = $name;
                    $dir = public_path("images/events/");
                    if (copy($dir . $query[0]->photo_event->name, $dir . $name)) {
                        // google Storage
                        $local = $dir . $name;
                        $remote = "images/events/" . $name;
                        GoogleStorageHelper::upload($local, $remote);

                        $db_photo = new PhotoEvent();
                        $db_photo->name = $name;
                        $db_photo->path = $path;
                        $db_photo->event_id = $db->id;
                        $db_photo->save();
                    }

                    if (count($query[0]->filter_to_events) > 0) {
                        foreach ($query[0]->filter_to_events as $key => $filter) {
                            $db_filter = new FilterToEvent();
                            $db_filter->filter_id = $filter->filter_id;
                            $db_filter->event_id = $db->id;

                            $db_filter->save();
                        }
                    }
                }
                $this->eventRepository->put_events_totals_and_img($db, true);
                $this->eventRepository->put_final_date($db, true);
                $this->eventRepository->set_date_timezone($db, true);
                $response->setData($db);
                $response->setCode(ApiCodeEnum::CREATED);
            } else {
                $response->setError('Something went wrong!');
                return response()->json($response);
            }
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }
        return response()->json($response);
    }

    public function event_responses(Request $request): JsonResponse
    {
        try {
            $response = new ApiModel();
            $response->setSuccess();
            $resp = $this->eventRepository->get_event_responses($request->event_id, $request->filter, $request->order_by, $request->search);
            $totGoing = 0;
            $totInvited = 0;
            $totInterested = 0;
            $totMaybe = 0;
            $tot = 0;
            foreach ($resp as &$ev) {
                $tot++;
                if ($ev['status'] === 'Going')
                    $totGoing++;
                else if ($ev['status'] === 'Invited')
                    $totInvited++;
                else if ($ev['status'] === 'Interested')
                    $totInterested++;
                else if ($ev['status'] === 'Maybe')
                    $totMaybe++;
            }
            $response->total_responses = $tot;
            //the following 4 fields totals are only for list_by_event endpoint
            $response->total_responses_invited = $totInvited;
            $response->total_responses_going = $totGoing;
            $response->total_responses_interested = $totInterested;
            $response->total_responses_maybe = $totMaybe;
            $response->setData($resp);
            $response->setCode(ApiCodeEnum::SUCCESS);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function list_nearby_attending(Request $request): JsonResponse
    {
        // dd($local_lat);
        
        $response = new ApiModel();
        $response->setSuccess();

        try {

            $validator = Validator::make($request->all(), [
                'lat' => 'required|numeric',
                'long' => 'required|numeric'
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            $user_id = Auth::id();
            $events = new Collection();
            $nearby = env('NEARBY');

            $status = "Going";

            $guests = $this->guestRepository->get_by_user_and_status($user_id, $status);

            foreach ($guests as $g) {
                $event = $this->eventRepository->find($g->event_id);
                $event_date = new Carbon($event->date_time);
                $now_date = Carbon::now();

                $event_lat = $event->latitude;
                $event_long = $event->longitude;
                $distance = DistanceHelper::calculate_distance($request->lat, $request->long, $event_lat, $event_long);


                if ($event_date->gt($now_date) && $distance <= $nearby) {
                    $events[] = $event;
                }
            }

            $this->eventRepository->put_events_totals_and_img($events);
            $this->eventRepository->put_final_date($events);
            $this->eventRepository->set_date_timezone($events);

            $response->setData($events);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function current_nearby(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {

            $user_id = Auth::id();
            $events = new Collection();
            $nearby = env('NEARBY');

            $all_events = $this->eventRepository->all();

            foreach ($all_events as $event) {

                

                $event_date = new Carbon($event->date_time);
                $now_date = Carbon::now();

                $min_date_start = Carbon::now();
                $max_date_start = Carbon::now()->addHours(24);

                $duration = $event->duration;
                $date = $event->date_time;

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

                $carbon_final = Carbon::createFromFormat('Y-m-d H:i:s', $final_date, 'UTC');

                if (($carbon_final->gt($now_date) && $now_date->gt($event_date)) || ($event_date->gt($min_date_start) && $max_date_start->gt($event_date))) {
                    $events[] = $event;
                }
            }

            $this->eventRepository->put_events_totals_and_img($events);
            $this->eventRepository->put_final_date($events);
            $this->eventRepository->set_date_timezone($events);

            $response->setData($events);

        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }
}
