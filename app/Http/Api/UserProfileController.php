<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use App\Core\ApiCodeEnum;
use App\Http\Api\Base\ApiController;
use App\Http\Controllers\Controller;
use App\Models\UserProfile;
use App\Repositories\UserProfileRepositoryInteface;
use App\Repositories\UserRepositoryInterface;
use App\Services\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;
use Whoops\Handler\JsonResponseHandler;

class UserProfileController extends ApiController
{
    private UserProfileRepositoryInteface $userProfileRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(UserProfileRepositoryInteface $userProfileRepository, LogServiceInterface $logger,
                                UserRepositoryInterface $userRepository)
    {
        parent::__construct($logger);
        $this->userProfileRepository =  $userProfileRepository;
        $this->userRepository = $userRepository;
    }

    public function list(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try{
            $query = $this->userProfileRepository->all();
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
            $query = $this->userProfileRepository->list_with_user();
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
            $query = $this->userProfileRepository->find($id);
            if (!isset($query)) {
                $response->setError('User Profile not found!');
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
            $query = $this->userProfileRepository->find($id);
            if (!isset($query)) {
                $response->setError('User Profile not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }
            $query = $this->userProfileRepository->find_with_user($id);
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
            $query = $this->userProfileRepository->deleteLogic($id);
            if ($query == true) {
                $response->setMessage('Contact deleted Successfully!');
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
            $query = $this->userProfileRepository->restore($id);
            if ($query == true) {
                $response->setMessage('Contact restored Successfully!');
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
                'about_me' => 'string',
                'lives_in' => 'string',
                'from' => 'string',
                'work' => 'string',
                'education' => 'string',
                'status' => ['required','string', Rule::in(['Online', 'Offline', 'Other'])],
                // 'user_id' => 'required|integer',
                'height' => 'nullable|numeric'
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

            $tempUser = $this->userProfileRepository->me(Auth::id());
            if (count($tempUser) > 1) {
                $response->setError('The user already has a profile');
                $response->setCode(ApiCodeEnum::SUCCESS);
                return response()->json($response);
            }

            if ($request->get('id') != null) {
                $db = $this->userProfileRepository->find($request->get('id'));
                if (!isset($db)) {
                    $response->setError('User Profile not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
            }else {
                $db = new UserProfile();
            }

            $db->about_me = $request->about_me;
            $db->lives_in = $request->lives_in;
            $db->from = $request->from;
            $db->work = $request->work;
            $db->education = $request->education;
            $db->status = $request->status;
            $db->user_id = Auth::id();
            $db->height = $request->height;

            if($this->userProfileRepository->save($db))
            {
                $response->setData($db);
                $response->setCode(ApiCodeEnum::CREATED);
            }

        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function set_status(Request $request) : JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'status' => ['required','string', Rule::in(['Online', 'Offline', 'Other'])],
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            $db = $this->userProfileRepository->find(Auth::id());
            if (!isset($db)) {
                $response->setError('User Profile not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            $db->status = $request->status;

            if($this->userProfileRepository->save($db))
            {
                $response->setData($db);
                $response->setCode(ApiCodeEnum::CREATED);
            }

        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }
}
