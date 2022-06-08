<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use App\Core\ApiCodeEnum;
use App\Helpers\ImageHelper;
use App\Helpers\MediaHelper;
use App\Http\Api\Base\ApiController;
use App\Http\Controllers\Controller;
use App\Models\UserPhoto;
use App\Repositories\UserPhotoRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Services\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\TryCatch;
use Throwable;

class UserPhotoController extends ApiController
{
    private UserPhotoRepositoryInterface $userPhotoRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserPhotoRepositoryInterface $userPhotoRepository,
        LogServiceInterface $logger,
        UserRepositoryInterface $userRepository
    ) {
        parent::__construct($logger);
        $this->userPhotoRepository =  $userPhotoRepository;
        $this->userRepository = $userRepository;
    }

    public function me(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->userPhotoRepository->me(Auth::id());
            foreach ($query as $key => $img) {
                $img->path = MediaHelper::getImageUrl(MediaHelper::getUserPath(), $img->name);
            }
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
            $query = $this->userPhotoRepository->all();

            foreach ($query as $key => $img) {
                $img->path = MediaHelper::getImageUrl(MediaHelper::getUserPath(), $img->name);
            }

            $response->setData($query);
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
            $query = $this->userPhotoRepository->list_with_user();
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
            $query = $this->userPhotoRepository->find($id);
            if (!isset($query)) {
                $response->setError('User Photo not found!');
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
            $query = $this->userPhotoRepository->find($id);
            if (!isset($query)) {
                $response->setError('User Photo not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }
            $query = $this->userPhotoRepository->find_with_user($id);
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
            $query = $this->userPhotoRepository->deleteLogic($id);
            if ($query == true) {
                $response->setMessage('User Photo deleted Successfully!');
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
            $query = $this->userPhotoRepository->restore($id);
            if ($query == true) {
                $response->setMessage('User Photo restored Successfully!');
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
                'image' => 'image',
                'image_url' => 'array',
                'file' => 'required|boolean',
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
                $db = $this->userPhotoRepository->find($request->get('id'));
                if (!isset($db)) {
                    $response->setError('User Photo not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
            } else {
                $db = new UserPhoto();
            }


            if ($request->file) {
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $dataImage = ImageHelper::uploadImage($image, 'users');
                    $db->name = $dataImage['name'];
                    $db->path = $dataImage['path'];
                    $db->user_id = Auth::id();

                    if ($this->userPhotoRepository->save($db)) {
                        $db->path = MediaHelper::getImageUrl(MediaHelper::getUserPath(), $db->name);
                        $response->setData($db);
                        $response->setCode(ApiCodeEnum::CREATED);
                    } else {
                        $response->setError('Something went wrong!');
                        return response()->json($response);
                    }
                }
            } else {
                foreach ($request->image_url as $key => $img) {
                    // $db = new UserPhoto();
                    try {
                        $dataImage = ImageHelper::uploadImageUrl($img, 'users');
                        $db->name = $dataImage['name'];
                        $db->path = $dataImage['path'];
                        $db->user_id = Auth::id();

                        if ($this->userPhotoRepository->save($db)) {
                            $db->path = MediaHelper::getImageUrl(MediaHelper::getUserPath(), $db->name);
                            $response->setData($db);
                            $response->setCode(ApiCodeEnum::CREATED);
                        } else {
                            $response->setError('Something went wrong!');
                            return response()->json($response);
                        }
                    } catch (\Throwable $th) {
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
