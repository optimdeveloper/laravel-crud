<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use App\Core\ApiCodeEnum;
use App\Http\Api\Base\ApiController;
use App\Http\Controllers\Controller;
use App\Models\UserToPassion;
use App\Repositories\PassionRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\UserToPassionRepositoryInterface;
use App\Services\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Throwable;

class UserToPassionController extends ApiController
{
    private UserToPassionRepositoryInterface $userToPassionRepository;
    private UserRepositoryInterface $userRepository;
    private PassionRepositoryInterface $passionRepository;

    public function __construct(
        UserToPassionRepositoryInterface $userToPassionRepository,
        LogServiceInterface $logger,
        UserRepositoryInterface $userRepository,
        PassionRepositoryInterface $passionRepository
    ) {
        parent::__construct($logger);
        $this->userToPassionRepository =  $userToPassionRepository;
        $this->userRepository = $userRepository;
        $this->passionRepository = $passionRepository;
    }

    public function me(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->userToPassionRepository->me(Auth::id(), "passions");
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

        try {
            $query = $this->userToPassionRepository->all();
            $response->setData($query);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function list_for_user($user_id): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->userToPassionRepository->find_with_detail($user_id);
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
            $query = $this->userToPassionRepository->find($id);
            if (!isset($query)) {
                $response->setError('User to Passion not found!');
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
                $query = $this->userToPassionRepository->deleteLogic($id);
                if ($query == true) {
                    $response->setMessage('User to Passion deleted Successfully!');
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
            $query = $this->userToPassionRepository->restore($id);
            if ($query == true) {
                $response->setMessage('User to Passion restored Successfully!');
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

    // Create
    public function add_passions(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'passion_id' => 'required|array',
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            $passions = $this->userToPassionRepository->me(Auth::id());
            foreach ($passions as $passion) {
                $this->userToPassionRepository->delete($passion->id);
            }

            $db = $this->userRepository->find(Auth::id());
            if (!isset($db)) {
                $response->setError('User not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }


            $tempFilters = $this->userToPassionRepository->find_passions(Auth::id());
            foreach ($tempFilters as $filter) {
                $this->userToPassionRepository->delete($filter->id);
            }


            //add the new selected passions
            foreach ($request->passion_id as $passion) {
                $db = $this->passionRepository->find($passion);
                if (!isset($db)) {
                    $response->setError('Passion not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }

                $db = new UserToPassion();
                $db->user_id = Auth::id();
                $db->passion_id = $passion;

                if($this->userToPassionRepository->find_passion($passion, Auth::id()))
                {
                    if ($this->userToPassionRepository->save($db)) {
                        $response->setCode(ApiCodeEnum::CREATED);
                    } else {
                        $response->setError('Something went wrong!');
                        return response()->json($response);
                    }
                }

            }

        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }
}
