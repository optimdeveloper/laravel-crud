<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use App\Core\ApiCodeEnum;
use App\Http\Api\Base\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Repositories\SubscriptionRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Services\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class SubscriptionController extends ApiController
{
    private SubscriptionRepositoryInterface $subscriptionRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(SubscriptionRepositoryInterface $subscriptionRepository, LogServiceInterface $logger,
                                UserRepositoryInterface $userRepository)
    {
        parent::__construct($logger);
        $this->subscriptionRepository =  $subscriptionRepository;
        $this->userRepository = $userRepository;
    }

    public function list(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try{
            $query = $this->subscriptionRepository->all();
            $response->setData($query);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function list_with_user() : JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try{
            $query = $this->subscriptionRepository->list_with_user();
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
            $query = $this->subscriptionRepository->find($id);
            if (!isset($query)) {
                $response->setError('Suscription not found!');
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

    public function find_with_user($id): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->subscriptionRepository->find($id);
            if (!isset($query)) {
                $response->setError('Subcription not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }
            $query = $this->subscriptionRepository->find_with_user($id);
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
             $query = $this->subscriptionRepository->deleteLogic($id);
             if ($query == true) {
                 $response->setMessage('Suscription deleted Successfully!');
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
             $query = $this->subscriptionRepository->restore($id);
             if ($query == true) {
                 $response->setMessage('Suscription restored Successfully!');
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
                'type' => ['required','string', Rule::in(['Free', 'Premium'])],
                'time' => 'required|integer',
                // 'user_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            $db = $this->userRepository->find(Auth::id());
            if (!isset($db)) {
                $response->setError('User not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            if ($request->get('id') != null) {
                $db = $this->subscriptionRepository->find($request->get('id'));
                if (!isset($db)) {
                    $response->setError('Suscription not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
            }else {
                $db = new Subscription();
            }

            $db->type = $request->type;
            $db->time = $request->time;
            $db->user_id = Auth::id();

            if($this->subscriptionRepository->save($db))
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
