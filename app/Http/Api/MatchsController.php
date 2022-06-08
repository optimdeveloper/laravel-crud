<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use App\Core\ApiCodeEnum;
use App\Http\Api\Base\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Matchs;
use App\Repositories\MatchsRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\UserContactRepositoryInterface;
use App\Repositories\UserPhotoRepositoryInterface;
use App\Repositories\UserProfileRepositoryInteface;
use App\Repositories\PersonalFilterRepositoryInterface;
use App\Services\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;
use App\Repositories\GuestRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Helpers\MediaHelper;
use App\Helpers\MatchsHelper;

class MatchsController extends ApiController
{
    private MatchsRepositoryInterface $matchsRepository;
    private UserRepositoryInterface $userRepository;
    private UserContactRepositoryInterface $userContactRepository;
    private UserPhotoRepositoryInterface $userPhotoRepository;
    private UserProfileRepositoryInteface $userProfileRepository;
    private GuestRepositoryInterface $guestRepository;
    private PersonalFilterRepositoryInterface $personalFilterRepository;

    public function __construct(
        MatchsRepositoryInterface $matchsRepository,
        LogServiceInterface $logger,
        UserRepositoryInterface $userRepository,
        GuestRepositoryInterface $guestRepository,
        UserPhotoRepositoryInterface $userPhotoRepository,
        UserProfileRepositoryInteface $userProfileRepository,
        UserContactRepositoryInterface $userContactRepository,
        PersonalFilterRepositoryInterface $personalFilterRepository
    ) {
        parent::__construct($logger);
        $this->matchsRepository =  $matchsRepository;
        $this->userRepository = $userRepository;
        $this->userPhotoRepository =  $userPhotoRepository;
        $this->userProfileRepository =  $userProfileRepository;
        $this->guestRepository =  $guestRepository;
        $this->userContactRepository = $userContactRepository;
        $this->personalFilterRepository = $personalFilterRepository;
    }

    public function list(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->matchsRepository->all();
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
            $query = $this->matchsRepository->find($id);
            if (!isset($query)) {
                $response->setError('Match not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }
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
            $query = $this->matchsRepository->deleteLogic($id);
            if ($query == true) {
                $response->setMessage('Match deleted Successfully!');
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

    public function soft_restore($id): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->matchsRepository->restore($id);
            if ($query == true) {
                $response->setMessage('Match restored Successfully!');
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
                // 'user_one_id' => 'required|integer',
                'user_two_id' => 'required|integer',
                // 'status' => ['required','string', Rule::in([1, 2])],
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            $db = $this->userRepository->find(Auth::id());
            if (!isset($db)) {
                $response->setError('User 1 not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            $db = $this->userRepository->find($request->get('user_two_id'));
            if (!isset($db)) {
                $response->setError('User 2 not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            $data = $this->matchsRepository->find_by_users(Auth::id(), $request->user_two_id);
            if (count($data) == 0) {
                $data = $this->matchsRepository->find_by_users($request->user_two_id, Auth::id());
                if (count($data) == 0) {
                    $db = new Matchs();
                    $db->user_one_id = Auth::id();
                    $db->user_two_id = $request->user_two_id;
                    $db->status = 0;
                } else {
                    $response->setMessage('Nothing to do');
                    return response()->json($response);
                }
            } else {
                $db = $data[0];
                if ($db->status == 1) {
                    $response->setMessage('Nothing to do');
                    return response()->json($response);
                }
                $db->status = 1;
            }

            if ($this->matchsRepository->save($db)) {
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

    public function list_by_event_old(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'event_id' => 'required|integer',
                'status' => 'nullable|string',
                'limit' => 'nullable|integer',
                'order_by' => 'nullable|integer',
                'search' => 'nullable|string'
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            //Parameters rules:
            //1) if $request->status can be Going, Interested, Invited or Maybe but if it is null please get all the status
            //2) if $request->limit is null get all the maches, otherwise get the limit number
            $matches = new Collection();

            $limit = null;

            if (isset($request->limit) && !is_null($request->limit)) {
                $limit = $request->limit;
            }

            if (isset($request->status) && !is_null($request->status)) {
                $events = $this->guestRepository->get_by_event_and_status($request->event_id, $request->status, null);
            } else {
                $events = $this->guestRepository->get_by_event($request->event_id, null);
            }

            $totGoing = 0;
            $totInvited = 0;
            $totInterested = 0;
            $totMaybe = 0;
            foreach ($events as $e) {
                $user = $this->userRepository->find($e->user_id);
                $imagepath = $this->userPhotoRepository->get_first_user_photo($e->user_id);
                if (isset($imagepath))
                    $imagepath = $imagepath->name;
                $userwork = $this->userProfileRepository->get_user_work($e->user_id);
                if (isset($userwork))
                    $userwork = $userwork->work;
                else
                    $userwork = '';
                $user->status = $e->status;
                $user->image_url = MediaHelper::getImageUrl(MediaHelper::getUserPath(), $imagepath);
                $user->work = $userwork;
                $is_blocked = $this->userContactRepository->user_is_blocked($e->user_id);
                if (isset($is_blocked))
                    $is_blocked = "yes";
                else
                    $is_blocked = "no";
                $user->is_blocked = $is_blocked;
                if ($e->status === 'Going')
                    $totGoing++;
                else if ($e->status === 'Invited')
                    $totInvited++;
                else if ($e->status === 'Interested')
                    $totInterested++;
                else if ($e->status === 'Maybe')
                    $totMaybe++;

                /*getting relevance*/
                $relevance = $this->guestRepository->get_user_relevance($e->user_id);
                $user->relevance = $relevance;
                $matches->push($user);
            }

            //search by name
            if ($request->search !== null && $request->search !== '') {
                $matches = $matches->filter(function ($item) use ($request) {
                    if (str_contains(strtolower($item->name), strtolower($request->search)))
                        return true;
                    return false;
                })->values();
                //restart count
                $totGoing = 0;
                $totInvited = 0;
                $totInterested = 0;
                $totMaybe = 0;
                foreach ($matches as $e) {
                    if ($e->status === 'Going')
                        $totGoing++;
                    else if ($e->status === 'Invited')
                        $totInvited++;
                    else if ($e->status === 'Interested')
                        $totInterested++;
                    else if ($e->status === 'Maybe')
                        $totMaybe++;
                }
            }
            ////////////////
            $response->total_matches = count($matches);
            //the following 4 fields totals are only for list_by_event endpoint
            $response->total_matches_invited = $totInvited;
            $response->total_matches_going = $totGoing;
            $response->total_matches_interested = $totInterested;
            $response->total_matches_maybe = $totMaybe;

            if ($request->order_by === 0) {
                // $col = 'birthday_at';
                $matches = $matches->sortByDesc(function ($item) {
                    return $item->birthday_at;
                })->values();
            } else if ($request->order_by === 1) {
                // $col = 'name';
                $matches = $matches->sortBy(function ($item) {
                    return $item->name;
                })->values();
            } else if ($request->order_by === 2) {
                //Ordenar por relevancia (el que tenga mas count user_id en la tabla guests)
                // $col = 'relevance';
                $matches = $matches->sortByDesc(function ($item) {
                    return $item->relevance;
                })->values();
            }

            $limit_matches = $matches->slice(0, $limit);
            $response->setData($limit_matches);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function matches_inbox_old(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'limit' => 'nullable|integer'
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            $matches = new Collection();

            $limit = null;

            if (isset($request->limit) && !is_null($request->limit)) {
                $limit = $request->limit;
            }

            $match_users = $this->userRepository->all();

            foreach ($match_users as $mu) {
                $user = $mu;
                $imagepath = $this->userPhotoRepository->get_first_user_photo($mu->id);
                if (isset($imagepath))
                    $imagepath = $imagepath->name;
                $userwork = $this->userProfileRepository->get_user_work($mu->id);
                if (isset($userwork))
                    $userwork = $userwork->work;
                else
                    $userwork = '';
                $user->image_url = MediaHelper::getImageUrl(MediaHelper::getUserPath(), $imagepath);
                $user->work = $userwork;

                /*getting relevance*/
                $relevance = $this->guestRepository->get_user_relevance($mu->id);
                $user->relevance = $relevance;

                $matches->push($user);
            }

            $response->total_matches = count($matches);

            $limit_matches = $matches->slice(0, $limit);

            $response->setData($limit_matches);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function list_by_event(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'event_id' => 'required|integer',
                'status' => 'nullable|string',
                'limit' => 'nullable|integer',
                'order_by' => 'nullable|integer',
                'search' => 'nullable|string'
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            // $userid = Auth::id();
            // dd($userid);
            //Parameters rules:
            //1) if $request->status can be Going, Interested, Invited or Maybe but if it is null please get all the status
            //2) if $request->limit is null get all the matches, otherwise get the limit number
            $matches = MatchsHelper::getFromGuests(Auth::id(), $request->event_id, $request->status, $request->order_by, $request->search);

            //adding additional information

            $totGoing = 0;
            $totInvited = 0;
            $totInterested = 0;
            $totMaybe = 0;
            foreach ($matches as $m) {
                $imagepath = $this->userPhotoRepository->get_first_user_photo($m->id);
                if (isset($imagepath))
                    $imagepath = $imagepath->name;

                $m->image_url = MediaHelper::getImageUrl(MediaHelper::getUserPath(), $imagepath);

                if ($m->status === 'Going')
                    $totGoing++;
                else if ($m->status === 'Invited')
                    $totInvited++;
                else if ($m->status === 'Interested')
                    $totInterested++;
                else if ($m->status === 'Maybe')
                    $totMaybe++;
            }
            ////////////////
            $response->total_matches = count($matches);
            //the following 4 fields totals are only for list_by_event endpoint
            $response->total_matches_invited = $totInvited;
            $response->total_matches_going = $totGoing;
            $response->total_matches_interested = $totInterested;
            $response->total_matches_maybe = $totMaybe;

            //limit

            $limit = null;

            if (isset($request->limit) && !is_null($request->limit)) {
                $limit = $request->limit;
            }

            $limit_matches = $matches->slice(0, $limit);
            $response->setData($limit_matches);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function matches_inbox(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'limit' => 'nullable|integer',
                'order_by' => 'nullable|integer',
                'lat' => 'nullable|numeric',
                'long' => 'nullable|numeric',
                'search' => 'nullable|string'
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            $matches = MatchsHelper::getAll(Auth::id(), $request->order_by, $request->lat, $request->long, $request->search);

            foreach ($matches as $m) {
                $imagepath = $this->userPhotoRepository->get_first_user_photo($m->id);
                if (isset($imagepath))
                    $imagepath = $imagepath->name;

                $m->image_url = MediaHelper::getImageUrl(MediaHelper::getUserPath(), $imagepath);
            }

            $response->total_matches = count($matches);

            //limit
            $limit = null;

            if (isset($request->limit) && !is_null($request->limit)) {
                $limit = $request->limit;
            }

            $limit_matches = $matches->slice(0, $limit);

            $response->setData($limit_matches);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function create_match(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'event_id' => 'nullable|integer',
                'user_two_id' => 'required|integer',
                'text' => 'nullable|string',
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            //check if user 2 exists
            $db = $this->userRepository->find($request->get('user_two_id'));
            if (!isset($db)) {
                $response->setError('User 2 does not exist');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            //check if match already created
            $data = $this->matchsRepository->find_by_users(Auth::id(), $request->user_two_id);
            if (count($data) > 0) {
                $response->setMessage('match already exists');
                return response()->json($response);
            }

            $db = new Matchs();
            $db->user_one_id = Auth::id();
            $db->user_two_id = $request->user_two_id;
            if($request->event_id !== null)
                $db->event_id = $request->event_id;
            else
                $db->event_id = 0;
            $db->status = 0;

            if ($this->matchsRepository->save($db)) {
                $response->setData($db);
                $response->setCode(ApiCodeEnum::CREATED);
            } else {
                $response->setError('Error saving match');
                return response()->json($response);
            }

        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function decide_match(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'match_id' => 'required|integer',
                'status' => 'required|integer'
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            //check if match exists
            $db = $this->matchsRepository->find($request->get('match_id'));
            if (!isset($db)) {
                $response->setError('Match not found');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            //update match
            $db->status = $request->get('status');

            if ($this->matchsRepository->save($db)) {
                $response->setData($db);
                $response->setCode(ApiCodeEnum::CREATED);
            } else {
                $response->setError('Error updating match');
                return response()->json($response);
            }

        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function get_logged_matches(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'order' => 'nullable|integer'
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            //order 
            if(is_int($request->get('order'))) {
                $order=$request->get('order');
            }
            else {
                $order = null;
            }

            $logged_user_id = Auth::id();
            $query = $this->matchsRepository->find_by_user_not_rejected($logged_user_id, $order);

            $matches = new Collection();

            foreach ($query as $m) {

                $match = $m;

                $user_id=$m->user_two_id;
                if($user_id == $logged_user_id) {
                    $user_id=$m->user_one_id;
                }
                $imagepath = $this->userPhotoRepository->get_first_user_photo($user_id);
                if (isset($imagepath))
                    $imagepath = $imagepath->name;

                
                $user = $this->userRepository->find($user_id);
                // $user_profile = $this->userProfileRepository->find($user_id);

                $profile_verified = "false";

                if(!is_null($user->email_verified_at)) {
                    $profile_verified = "true";
                }

                $pf = $this->personalFilterRepository->get_personal_filter_by_user($user_id);
                if (!isset($pf)) {
                    $mode = "None";
                }
                else {
                    $mode = $pf->mode;
                }

                $user_profile = $this->userProfileRepository->get_user_work($user_id);

                if (!isset($user_profile)) {
                    $user_profile_status = "None";
                }
                else {
                    $user_profile_status = $user_profile->status;
                }

                $match->image_url = MediaHelper::getImageUrl(MediaHelper::getUserPath(), $imagepath);
                $match->user_name = $user->name;
                $match->last_message = "";
                $match->mode = $mode;
                $match->user_profile_status = $user_profile_status;
                $match->user_email_verified = $profile_verified;
                $matches->push($match);
            }

            /* set order */
            if($order == 2) {
                $matches = $matches->sortBy(function ($item) {
                    return $item->user_name;
                })->values();
            }

            $response->setData($matches);

            

        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function get_logged_matches_sent(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'order' => 'nullable|integer'
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            //order 
            if(is_int($request->get('order'))) {
                $order=$request->get('order');
            }
            else {
                $order = null;
            }

            $logged_user_id = Auth::id();
            $query = $this->matchsRepository->find_by_user_not_rejected_sent($logged_user_id, $order);

            $matches = new Collection();

            foreach ($query as $m) {

                $match = $m;

                $user_id=$m->user_two_id;
                if($user_id == $logged_user_id) {
                    $user_id=$m->user_one_id;
                }
                $imagepath = $this->userPhotoRepository->get_first_user_photo($user_id);
                if (isset($imagepath))
                    $imagepath = $imagepath->name;

                
                $user = $this->userRepository->find($user_id);
                // $user_profile = $this->userProfileRepository->find($user_id);

                $profile_verified = "false";

                if(!is_null($user->email_verified_at)) {
                    $profile_verified = "true";
                }

                $pf = $this->personalFilterRepository->get_personal_filter_by_user($user_id);
                if (!isset($pf)) {
                    $mode = "None";
                }
                else {
                    $mode = $pf->mode;
                }

                $user_profile = $this->userProfileRepository->get_user_work($user_id);

                if (!isset($user_profile)) {
                    $user_profile_status = "None";
                }
                else {
                    $user_profile_status = $user_profile->status;
                }

                $user_contacts_status = 'none';

                $user_contact= $this->userContactRepository->get_by_user_and_contact($logged_user_id, $user_id);

                if($user_contact) {
                    $user_contacts_status = $user_contact->status;
                }

                $match->image_url = MediaHelper::getImageUrl(MediaHelper::getUserPath(), $imagepath);
                $match->user_name = $user->name;
                $match->last_message = "";
                $match->mode = $mode;
                $match->user_profile_status = $user_profile_status;
                $match->user_email_verified = $profile_verified;
                $match->user_contacts_status = $user_contacts_status;
                $matches->push($match);
            }

            /* set order */
            if($order == 2) {
                $matches = $matches->sortBy(function ($item) {
                    return $item->user_name;
                })->values();
            }

            $response->setData($matches);

            

        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function get_logged_matches_received(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'order' => 'nullable|integer'
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            //order 
            if(is_int($request->get('order'))) {
                $order=$request->get('order');
            }
            else {
                $order = null;
            }

            $logged_user_id = Auth::id();
            $query = $this->matchsRepository->find_by_user_not_rejected_received($logged_user_id, $order);

            $matches = new Collection();

            foreach ($query as $m) {

                $match = $m;

                $user_id=$m->user_two_id;
                if($user_id == $logged_user_id) {
                    $user_id=$m->user_one_id;
                }
                $imagepath = $this->userPhotoRepository->get_first_user_photo($user_id);
                if (isset($imagepath))
                    $imagepath = $imagepath->name;

                
                $user = $this->userRepository->find($user_id);
                // $user_profile = $this->userProfileRepository->find($user_id);

                $profile_verified = "false";

                if(!is_null($user->email_verified_at)) {
                    $profile_verified = "true";
                }

                $pf = $this->personalFilterRepository->get_personal_filter_by_user($user_id);
                if (!isset($pf)) {
                    $mode = "None";
                }
                else {
                    $mode = $pf->mode;
                }

                $user_profile = $this->userProfileRepository->get_user_work($user_id);

                if (!isset($user_profile)) {
                    $user_profile_status = "None";
                }
                else {
                    $user_profile_status = $user_profile->status;
                }

                $user_contacts_status = 'none';

                $user_contact= $this->userContactRepository->get_by_user_and_contact($logged_user_id, $user_id);

                if($user_contact) {
                    $user_contacts_status = $user_contact->status;
                }

                $match->image_url = MediaHelper::getImageUrl(MediaHelper::getUserPath(), $imagepath);
                $match->user_name = $user->name;
                $match->last_message = "";
                $match->mode = $mode;
                $match->user_profile_status = $user_profile_status;
                $match->user_email_verified = $profile_verified;
                $match->user_contacts_status = $user_contacts_status;
                $matches->push($match);
            }

            /* set order */
            if($order == 2) {
                $matches = $matches->sortBy(function ($item) {
                    return $item->user_name;
                })->values();
            }

            $response->setData($matches);

            

        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }
}
