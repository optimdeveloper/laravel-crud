<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use App\Core\ApiCodeEnum;
use App\Http\Api\Base\ApiController;
use App\Http\Controllers\Controller;
use App\Models\ConectedApp;
use App\Repositories\ConectedAppRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Services\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ConectedAppController extends ApiController
{
    private ConectedAppRepositoryInterface $conectedAppRepository;
    private UserRepositoryInterface $userRepository;


    public function __construct(ConectedAppRepositoryInterface $conectedAppRepository, LogServiceInterface $logger,
                                UserRepositoryInterface $userRepository)
    {
        parent::__construct($logger);
        $this->conectedAppRepository =  $conectedAppRepository;
        $this->userRepository = $userRepository;
    }

    public function me(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->conectedAppRepository->me(Auth::id());
            $response->setData($query);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function list(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try{
            $query = $this->conectedAppRepository->all();
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
            $query = $this->conectedAppRepository->list_with_user();
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
            $query = $this->conectedAppRepository->find($id);
            if (!isset($query)) {
                $response->setError('App not found!');
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
            $query = $this->conectedAppRepository->find($id);
            if (!isset($query)) {
                $response->setError('App not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }
            $query = $this->conectedAppRepository->find_with_user($id);
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
             $query = $this->conectedAppRepository->deleteLogic($id);
             if ($query == true) {
                 $response->setMessage('App deleted Successfully!');
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
             $query = $this->conectedAppRepository->restore($id);
             if ($query == true) {
                 $response->setMessage('App restored Successfully!');
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
                'name' => 'string|max:50',
                // 'user_id' => 'required|integer',
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


            if ($request->get('id') != null) {
                $db = $this->conectedAppRepository->find($request->get('id'));
                if (!isset($db)) {
                    $response->setError('App not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
            }else {
                $db = new ConectedApp();
            }

            $db->name = $request->name;
            $db->user_id = Auth::id();

            if($this->conectedAppRepository->save($db))
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
