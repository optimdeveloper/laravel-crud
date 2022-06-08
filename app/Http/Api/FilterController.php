<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use App\Core\ApiCodeEnum;
use App\Http\Api\Base\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Filter;
use App\Repositories\FilterRepositoryInterface;
use App\Services\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class FilterController extends ApiController
{
    private FilterRepositoryInterface $filterRepository;

    public function __construct(FilterRepositoryInterface $filterRepository, LogServiceInterface $logger)
    {
        parent::__construct($logger);
        $this->filterRepository =  $filterRepository;
    }

    // list filters
    public function list(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->filterRepository->all();
            $response->setData($query);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function list_type_event(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->filterRepository->list_type_event();
            $response->setData($query);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function list_type_advanced($mode): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->filterRepository->list_type_advanced($mode);
            $response->setData($query);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function list_type_advanced_sub($mode, $parent_id): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->filterRepository->list_type_advanced_sub($mode, $parent_id);
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
            $query = $this->filterRepository->find($id);
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
    public function soft_remove($id): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->filterRepository->deleteLogic($id);
            if ($query == true) {
                $response->setMessage('Filter deleted Successfully!');
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
            $query = $this->filterRepository->restore($id);
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
                'type' => ['required', 'string', Rule::in(['Advanced', 'Event'])],
                'name' => 'required|string|max:50',
                'value' => 'string|max:100',
                'mode' => ['required', 'string', Rule::in(['Networking', 'Love', 'Friendship'])],
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            if ($request->get('id') != null) {
                $db = $this->filterRepository->find($request->get('id'));
                if (!isset($db)) {
                    $response->setError('Filter not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
            } else {
                $db = new Filter();
            }

            $db->type = $request->type;
            $db->name = $request->name;
            $db->value = $request->value;
            $db->mode = $request->mode;

            if ($this->filterRepository->save($db)) {
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

    // list filters: mode null = event / parent null = main advanced
    public function list_filters(Request $request)
    {
        $response = new ApiModel();
        $response->setSuccess();
        
        /* example input
        {
            "mode": 'Friendship',
            "parent" : 39
        }
        */

        try {

            $validator = Validator::make($request->all(), [
                'mode' => 'nullable|string',
                'parent' => 'nullable|integer'
            ]);

            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            $mode = $request->mode;
            $parent = $request->parent; //parent can be null, but if clause is not necessary

            $query = $this->filterRepository->list($mode, $parent);
            $response->setData($query);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }
    
        return response()->json($response);

    }
}