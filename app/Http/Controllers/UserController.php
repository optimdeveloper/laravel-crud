<?php

namespace App\Http\Controllers;

use App\AppModels\ApiModel;
use App\Repositories\EventRepositoryInterface;
use App\Repositories\PersonalFilterRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

use App\Helpers\MediaHelper;
use App\Models\UserProfile;

class UserController extends Controller
{

    private UserRepositoryInterface $userRepository;
    private EventRepositoryInterface $eventRepository;
    private PersonalFilterRepositoryInterface $personalFilterRepository;

    public function __construct(
        UserRepositoryInterface           $userRepository,
        EventRepositoryInterface          $eventRepository,
        PersonalFilterRepositoryInterface $personalFilterRepository
    )
    {
        $this->userRepository = $userRepository;
        $this->eventRepository = $eventRepository;
        $this->personalFilterRepository = $personalFilterRepository;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('users.index');
    }


    public function list(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->userRepository->all();
            $response->setData($query);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
        }

        return response()->json($response);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = $this->userRepository->find_with_detail($id);
        if (count($user[0]->user_photos) != 0) {
            $user[0]->user_photos[0]->path = MediaHelper::getImageUrl(MediaHelper::getUserPath(), $user[0]->user_photos[0]->name);
        }else{
            $user[0]->user_photos[] = (object)["path" => MediaHelper::getImageUrl(MediaHelper::defaultImage(), MediaHelper::defaultImage())];
        }

        if ($user[0]->user_profile == null) {
            $user[0]->user_profile = (object)[
                "about_me" => "",
                "lives_in" => "",
                "from" => "",
                "work" => "",
                "education" => "",
            ];
        }
        // dd($user[0]->user_photos[0]->path);

        $data = [
            'User' => $user,
        ];
        return view('users.detail', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($id > 2) {
            $user = $this->userRepository->find($id);

            $user->subscription->delete();

            $this->userRepository->deleteLogic($id);
        }

        return redirect('users')->with('delete', 'ok');
    }

    public function destroyCascade($id)
    {
        if ($id > 2) {
            $user = $this->userRepository->find($id);

            $user->user_photos()->delete();
            $user->user_to_passions()->delete();
            $user->subscription()->delete();
            $user->validation_code()->delete();
            $user->connected_apps()->delete();
            $user->contacts()->delete();
            $user->contact()->delete();
            $user->reports()->delete();
            $user->guests()->delete();
            $user->user_matches_one()->delete();
            $user->user_matches_two()->delete();

            $events = $user->events()->get('id');
            foreach ($events as $event) {
                $this->eventRepository->find($event->id)->promote_event()->delete();
                $this->eventRepository->find($event->id)->photo_event()->delete();
                $this->eventRepository->find($event->id)->me_reports()->delete();
                $this->eventRepository->find($event->id)->guests()->delete();
                $this->eventRepository->find($event->id)->filter_to_events()->delete();
            }
            $user->events()->delete();

            $personalFilters = $user->personal_filter()->get('id');
            foreach ($personalFilters as $personalFilter) {
                $this->personalFilterRepository->find($personalFilter->id)->personal_to_filters()->delete();
            }
            $user->personal_filter()->delete();
            $user->user_profile()->delete();

            $user->delete();
            // dd('.');
            return redirect('users')->with('delete', 'ok');
        }
        return redirect('users');


    }
}
