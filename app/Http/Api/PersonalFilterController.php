<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use App\Core\ApiCodeEnum;
use App\Http\Api\Base\ApiController;
use App\Http\Controllers\Controller;
use App\Models\PersonalFilter;
use App\Repositories\PersonalFilterRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Services\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Throwable;

class PersonalFilterController extends ApiController
{
    private PersonalFilterRepositoryInterface $personalFilterRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        PersonalFilterRepositoryInterface $personalFilterRepository,
        LogServiceInterface $logger,
        UserRepositoryInterface $userRepository
    ) {
        parent::__construct($logger);
        $this->personalFilterRepository =  $personalFilterRepository;
        $this->userRepository = $userRepository;
    }

    public function me(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->personalFilterRepository->me(Auth::id(), 'personal_to_filters');
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
            $query = $this->personalFilterRepository->all();
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
            $query = $this->personalFilterRepository->find($id);
            if (!isset($query)) {
                $response->setError('Personal Filter not found!');
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
            $query = $this->personalFilterRepository->deleteLogic($id);
            if ($query == true) {
                $response->setMessage('Personal Filter deleted Successfully!');
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
            $query = $this->personalFilterRepository->restore($id);
            if ($query == true) {
                $response->setMessage('Personal Filter restored Successfully!');
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
                'interested' => ['required', 'string', Rule::in(['Women', 'Men', 'Everyone'])],
                'age_range' => 'required|string|max:7',
                'distance' => 'required|integer',
                'verified_profyle_only' => 'required|boolean',
                'mode' => 'required|string',
                'heigth' => 'nullable',
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
                $db = $this->personalFilterRepository->find($request->get('id'));
                if (!isset($db)) {
                    $response->setError('Filter not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
            } else {
                //get the current user's personal filter
                $CurrentPersonalFilters = PersonalFilter::where('user_id',$db->id)->get();
                //delete the previous filter to add the new one to ensure that the user always will have only 1 personal filter
                foreach($CurrentPersonalFilters as $pf){
                    $query = $this->personalFilterRepository->delete($pf->id);
                    if ($query == false) {
                        $response->setError('Something went wrong!');
                        return response()->json($response);
                    }
                }
                $db = new PersonalFilter();
            }

            $db->interested = $request->interested;
            $db->age_range = $request->age_range;
            $db->distance = $request->distance;
            $db->verified_profyle_only = $request->verified_profyle_only;
            $db->mode = $request->mode;
            $db->height = $request->height;
            $db->user_id = Auth::id();

            if ($this->personalFilterRepository->save($db)) {

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

    // Add or Update Height range
    public function add_update_height_range(Request $request): JsonResponse
    {

        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'min_height' => 'required|numeric',
                'max_height' => 'required|numeric'
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

            $personal_filter = $this->personalFilterRepository->get_user_personal_filter();

            if (!isset($personal_filter)) {
                //create default personal filter
                $new_personal_filter = $this->personalFilterRepository->create_default();
                if (!$new_personal_filter) {
                    $response->setError('Error creating default filters for user!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
                $personal_filter = $this->personalFilterRepository->get_user_personal_filter();
            }
            $height_range = $request->min_height . "-" . $request->max_height;

            $personal_filter->height = $height_range;

            if ($this->personalFilterRepository->save($personal_filter)) {

                $response->setData($personal_filter);
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
