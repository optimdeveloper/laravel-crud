<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use App\Core\ApiCodeEnum;
use App\Http\Api\Base\ApiController;
use App\Http\Controllers\Controller;
use App\Models\PromoteEvent;
use App\Repositories\PromoteEventRepositoryInterface;
use App\Services\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class PromoteEventController extends ApiController
{
    private PromoteEventRepositoryInterface $promoteEventRepository;

    public function __construct(PromoteEventRepositoryInterface $promoteEventRepository, LogServiceInterface $logger)
    {
        parent::__construct($logger);
        $this->promoteEventRepository =  $promoteEventRepository;
    }

    public function me(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->promoteEventRepository->me(Auth::id());
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
            $query = $this->promoteEventRepository->all();
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
            $query = $this->promoteEventRepository->find($id);
            if (!isset($query)) {
                $response->setError('Promote Event not found!');
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
    public function soft_remove($id): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->promoteEventRepository->deleteLogic($id);
            if ($query == true) {
                $response->setMessage('Promote Event deleted Successfully!');
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
            $query = $this->promoteEventRepository->restore($id);
            if ($query == true) {
                $response->setMessage('Promote Event restored Successfully!');
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
                'number' => ['required', 'integer', Rule::in([1, 5, 10])],
                'used' => 'required|integer',
                'active' => 'required|boolean',
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            if ($request->get('id') != null) {
                $db = $this->promoteEventRepository->find($request->get('id'));
                if (!isset($db)) {
                    $response->setError('Promote Event not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
            } else {
                $db = new PromoteEvent();
            }

            $db->number = $request->number;
            $db->used = $request->used;
            $db->active = $request->active;
            $db->user_id = Auth::id();

            if ($this->promoteEventRepository->save($db)) {
                $response->setData($db);
                $response->setCode(ApiCodeEnum::CREATED);
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
}
