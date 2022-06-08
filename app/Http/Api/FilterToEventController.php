<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use App\Core\ApiCodeEnum;
use App\Http\Api\Base\ApiController;
use App\Http\Controllers\Controller;
use App\Models\FilterToEvent;
use App\Repositories\EventRepositoryInterface;
use App\Repositories\FilterRepositoryInterface;
use App\Repositories\FilterToEventRepositoryInterface;
use App\Services\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class FilterToEventController extends ApiController
{
    private FilterToEventRepositoryInterface $filterToEventRepository;
    private FilterRepositoryInterface $filterRepository;
    private EventRepositoryInterface $eventRepository;

    public function __construct(
        FilterToEventRepositoryInterface $filterToEventRepository,
        LogServiceInterface $logger,
        FilterRepositoryInterface $filterRepository,
        EventRepositoryInterface $eventRepository
    ) {
        parent::__construct($logger);
        $this->filterToEventRepository =  $filterToEventRepository;
        $this->filterRepository = $filterRepository;
        $this->eventRepository = $eventRepository;
    }

    public function list(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->filterToEventRepository->all();
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
            $query = $this->filterToEventRepository->find($id);
            if (!isset($query)) {
                $response->setError('Filter not found!');
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
    public function soft_remove(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|array',
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            foreach($request->id as $id){
                $query = $this->filterToEventRepository->deleteLogic($id);
                if ($query == true) {
                    $response->setMessage('Filter deleted Successfully!');
                } else {
                    $response->setError('Something went wrong!');
                    return response()->json($response);
                }
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
            $query = $this->filterToEventRepository->restore($id);
            if ($query == true) {
                $response->setMessage('Filter restored Successfully!');
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
                'filter_id' => 'required|array',
                'event_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }


            $db = $this->eventRepository->find($request->event_id);
            if (!isset($db)) {
                $response->setError('Event not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            $filters = $this->filterToEventRepository->find_filters($request->event_id);
            foreach ($filters as $filter) {
                $this->filterToEventRepository->delete($filter->id);
            }

            foreach ($request->filter_id as $filter) {

                $db = $this->filterRepository->find($filter);
                if (!isset($db)) {
                    $response->setError('Filter not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }

                // if ($request->get('id') != null) {
                //     $db = $this->filterToEventRepository->find($request->get('id'));
                //     if (!isset($db)) {
                //         $response->setError('Filter to Event not found!');
                //         $response->setCode(ApiCodeEnum::NO_CONTENT);
                //         return response()->json($response);
                //     }
                // } else {
                //     $db = new FilterToEvent();
                // }

                $db = new FilterToEvent();
                $db->filter_id = $filter;
                $db->event_id = $request->event_id;

                if ($this->filterToEventRepository->save($db)) {
                    // $response->setData($db);
                    $response->setCode(ApiCodeEnum::CREATED);
                } else {
                    $response->setError('Something went wrong!');
                    return response()->json($response);
                }
            }
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }


    public function find_details(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->filterToEventRepository->find_filters($request->event_id, "filters");
            if (!isset($query)) {
                $response->setError('Event not found!');
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
}
