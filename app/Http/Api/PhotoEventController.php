<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use App\Core\ApiCodeEnum;
use App\Helpers\ImageHelper;
use App\Helpers\MediaHelper;
use App\Http\Api\Base\ApiController;
use App\Http\Controllers\Controller;
use App\Models\PhotoEvent;
use App\Repositories\EventRepositoryInterface;
use App\Repositories\PhotoEventRepositoryInterface;
use App\Services\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class PhotoEventController extends ApiController
{
    private PhotoEventRepositoryInterface $photoEventRepository;
    private EventRepositoryInterface $EventRepository;

    public function __construct(
        PhotoEventRepositoryInterface $photoEventRepository,
        LogServiceInterface $logger,
        EventRepositoryInterface $EventRepository
    ) {
        parent::__construct($logger);
        $this->photoEventRepository =  $photoEventRepository;
        $this->EventRepository = $EventRepository;
    }

    public function list(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->photoEventRepository->all();
            foreach ($query as $key => $img) {
                $img->path = MediaHelper::getImageUrl(MediaHelper::getEventPath(), $img->name);
            }
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
            $query = $this->photoEventRepository->find($id);
            if (!isset($query)) {
                $response->setError('Event Photo not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }
            $query->path = MediaHelper::getImageUrl(MediaHelper::getEventPath(), $query->name);
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
            $query = $this->photoEventRepository->deleteLogic($id);
            if ($query == true) {
                $response->setMessage('Event Photo deleted Successfully!');
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
            $query = $this->photoEventRepository->restore($id);
            if ($query == true) {
                $response->setMessage('Event Photo restored Successfully!');
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
                'event_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }
            $event = $this->EventRepository->find($request->get('event_id'));
            if (!isset($event)) {
                $response->setError('Event not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            if ($request->get('id') != null) {
                $db = $this->photoEventRepository->find($request->get('id'));
                if (!isset($db)) {
                    $response->setError('Event Photo not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
            } else {
                $db = new PhotoEvent();
            }

            if ($request->file) {
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $dataImage = ImageHelper::uploadImage($image, 'events');
                    // dd("este");
                    $db->name = $dataImage['name'];
                    $db->path = $dataImage['path'];
                    $db->event_id = $request->event_id;
                }

                if ($this->photoEventRepository->save($db)) {
                    $db->path = MediaHelper::getImageUrl(MediaHelper::getEventPath(), $db->name);
                    $response->setData($db);
                    $response->setCode(ApiCodeEnum::CREATED);
                } else {
                    $response->setError('Something went wrong!');
                    return response()->json($response);
                }
            } else {
                foreach ($request->image_url as $key => $img) {
                    // $db = new UserPhoto();
                    try {
                        $dataImage = ImageHelper::uploadImageUrl($img, 'events');
                        $db->name = $dataImage['name'];
                        $db->path = $dataImage['path'];
                        $db->event_id = $request->event_id;

                        if ($this->photoEventRepository->save($db)) {
                            $db->path = MediaHelper::getImageUrl(MediaHelper::getEventPath(), $db->name);
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
