<?php

namespace App\Http\Controllers;

use App\AppModels\ApiModel;
use App\Repositories\EventRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

use App\Helpers\MediaHelper;

class EventController extends Controller
{
    private EventRepositoryInterface $eventRepository;

    public function __construct(EventRepositoryInterface $eventRepository)
    {
        $this->eventRepository =  $eventRepository;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('events.index');
    }


    public function list(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try{
            $query = $this->eventRepository->all();
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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $event = $this->eventRepository->find_with_detail($id);

        if ($event[0]->photo_event != null) {
            $event[0]->photo_event->path = MediaHelper::getImageUrl(MediaHelper::getEventPath(), $event[0]->photo_event->name);
        }else{
            $event[0]->photo_event = (object)["path" => MediaHelper::getImageUrl(MediaHelper::defaultImage(), MediaHelper::defaultImage())];
        }

        $data = [
            'Event' => $event,
        ];
        return view('events.detail', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $query = $this->eventRepository->deleteLogic($id);
        return redirect('events')->with('delete', 'ok');
    }

    public function cancel_event($id)
    {
            $db = $this->eventRepository->find($id);
            if (!isset($db)) {
                return redirect('events')->with('cancel', 'Error');
            }

            $db->published = $db->published == 0 ? 1 : 0;

            if($this->eventRepository->save($db))
            {
                return redirect('events')->with('cancel', 'ok');
            }else {
                return redirect('events')->with('cancel', 'Error');
            }
    }

}
