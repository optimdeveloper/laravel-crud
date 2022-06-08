<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use App\Core\ApiCodeEnum;
use App\Http\Api\Base\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Repositories\EventRepositoryInterface;
use App\Repositories\ReportRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Services\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class ReportController extends ApiController
{
    private ReportRepositoryInterface $reportRepository;
    private UserRepositoryInterface $userRepository;
    private EventRepositoryInterface $eventRepository;

    public function __construct(ReportRepositoryInterface $reportRepository, LogServiceInterface $logger,
                                UserRepositoryInterface $userRepository, EventRepositoryInterface $eventRepository)
    {
        parent::__construct($logger);
        $this->reportRepository =  $reportRepository;
        $this->userRepository = $userRepository;
        $this->eventRepository = $eventRepository;
    }

    public function list(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try{
            $query = $this->reportRepository->all();
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
            $query = $this->reportRepository->find($id);
            if (!isset($query)) {
                $response->setError('Report not found!');
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
     public function soft_remove($id) : JsonResponse
     {
         $response = new ApiModel();
         $response->setSuccess();

         try{
             $query = $this->reportRepository->deleteLogic($id);
             if ($query == true) {
                 $response->setMessage('Report deleted Successfully!');
             }else {
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

     public function soft_restore($id) : JsonResponse
     {
         $response = new ApiModel();
         $response->setSuccess();

         try{
             $query = $this->reportRepository->restore($id);
             if ($query == true) {
                 $response->setMessage('Report restored Successfully!');
             }else {
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
    public function add_update(Request $request) : JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'id' => 'nullable|integer',
                'type' => ['required','string', Rule::in(['Profile', 'Event'])],
                // 'user_id'  => 'required|integer',
                'profile_id' => 'nullable|integer',
                'event_id' => 'nullable|integer',
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }


            if ($request->type == 'Profile'){
                $db = $this->userRepository->find($request->get('profile_id'));
                if (!isset($db)) {
                    $response->setError('Profile not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
            }

            if ($request->type == 'Event') {
                $db = $this->eventRepository->find($request->get('event_id'));
                if (!isset($db)) {
                    $response->setError('Event not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
            }


            if ($request->get('id') != null) {
                $db = $this->reportRepository->find($request->get('id'));
                if (!isset($db)) {
                    $response->setError('Report not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
            }else {
                $db = new Report();
            }

            $db->type = $request->type;
            $db->user_id = Auth::id();
            $db->profile_id = $request->profile_id;
            $db->event_id = $request->event_id;

            if($this->reportRepository->save($db))
            {
                $response->setData($db);
                $response->setCode(ApiCodeEnum::CREATED);
            }else {
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
}
