<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use App\Core\ApiCodeEnum;
use App\Http\Api\Base\ApiController;
use App\Http\Controllers\Controller;
use App\Models\PersonalFilterToFilter;
use App\Repositories\FilterRepositoryInterface;
use App\Repositories\PersonalFilterRepositoryInterface;
use App\Repositories\PersonalFilterToFilterRepositoryInterface;
use App\Services\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;
use App\Core\PersonalFilterTypeEnum;
use Illuminate\Database\Eloquent\Collection;

class PersonalFilterToFilterController extends ApiController
{
    private PersonalFilterToFilterRepositoryInterface $personalFilterToFilterRepository;
    private PersonalFilterRepositoryInterface $personalFilterRepository;
    private FilterRepositoryInterface $filterRepository;

    public function __construct(
        PersonalFilterToFilterRepositoryInterface $personalFilterToFilterRepository,
        LogServiceInterface $logger,
        PersonalFilterRepositoryInterface $personalFilterRepository,
        FilterRepositoryInterface $filterRepository
    ) {
        parent::__construct($logger);
        $this->personalFilterToFilterRepository =  $personalFilterToFilterRepository;
        $this->personalFilterRepository = $personalFilterRepository;
        $this->filterRepository = $filterRepository;
    }

    public function list(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->personalFilterToFilterRepository->all();
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
            $query = $this->personalFilterToFilterRepository->find($id);
            if (!isset($query)) {
                $response->setError('Personal Filter to Filter not found!');
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


            foreach ($request->id as $id) {
                $query = $this->personalFilterToFilterRepository->deleteLogic($id);
                if ($query == true) {
                    $response->setMessage('Personal Filter to Filter deleted Successfully!');
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
            $query = $this->personalFilterToFilterRepository->restore($id);
            if ($query == true) {
                $response->setMessage('Personal Filter to Filter restored Successfully!');
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
                'filter_id' => 'required|array',
                'personal_filter_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }


            $db = $this->personalFilterRepository->find($request->get('personal_filter_id'));
            if (!isset($db)) {
                $response->setError('Personal Filter not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            $tempFilters = $this->personalFilterToFilterRepository->find_filters($request->personal_filter_id, $request->type);
            foreach ($tempFilters as $filter) {
                $this->personalFilterToFilterRepository->delete($filter->id);
            }


            foreach ($request->filter_id as $filter) {
                $db = $this->filterRepository->find($filter);
                if (!isset($db)) {
                    $response->setError('Filter not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }

                // if ($request->get('id') != null) {
                //     $db = $this->personalFilterToFilterRepository->find($request->get('id'));
                //     if (!isset($db)) {
                //         $response->setError('Filter not found!');
                //         $response->setCode(ApiCodeEnum::NO_CONTENT);
                //         return response()->json($response);
                //     }
                // } else {
                //     $db = new PersonalFilterToFilter();
                // }

                $db = new PersonalFilterToFilter();
                $db->filter_id = $filter;
                $db->personal_filter_id = $request->personal_filter_id;
                if (isset($request->type) && $request->type == 1)
                    $db->type = $request->type;

                if ($this->personalFilterToFilterRepository->save($db)) {
                    // $response->setData($db);
                    $response->setCode(ApiCodeEnum::CREATED);
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

    // Create and update ME
    public function add_update_me(Request $request): JsonResponse
    {

        $response = new ApiModel();
        $response->setSuccess();

        $type = PersonalFilterTypeEnum::me;

        try {
            $validator = Validator::make($request->all(), [
                'filter_id' => 'required|array'
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }


            $pf = $this->personalFilterRepository->get_user_personal_filter();

            if (!isset($pf)) {
                //create default personal filter
                $new_personal_filter = $this->personalFilterRepository->create_default(); 
                if (!$new_personal_filter) {
                    $response->setError('Error creating default filters for user!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
                $pf = $this->personalFilterRepository->get_user_personal_filter();
            }
            $personal_filter_id = $pf->id;

            $tempFilters = $this->personalFilterToFilterRepository->find_filters($personal_filter_id, $type);
            foreach ($tempFilters as $filter) {
                $this->personalFilterToFilterRepository->delete($filter->id);
            }


            foreach ($request->filter_id as $filter) {
                $db = $this->filterRepository->find($filter);
                if (!isset($db)) {
                    $response->setError('Filter not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }

                $db = new PersonalFilterToFilter();
                $db->filter_id = $filter;
                $db->personal_filter_id = $personal_filter_id;
                $db->type = $type;

                if ($this->personalFilterToFilterRepository->save($db)) {
                    $response->setCode(ApiCodeEnum::CREATED);
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

    // Create and update LOOKING
    public function add_update_looking(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        $type = PersonalFilterTypeEnum::looking;

        try {
            $validator = Validator::make($request->all(), [
                'filter_id' => 'required|array'
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }


            $pf = $this->personalFilterRepository->get_user_personal_filter();
            if (!isset($pf)) {
                //create default personal filter
                $new_personal_filter = $this->personalFilterRepository->create_default(); 
                if (!$new_personal_filter) {
                    $response->setError('Error creating default filters for user!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
                $pf = $this->personalFilterRepository->get_user_personal_filter();
            }
            $personal_filter_id = $pf->id;

            $tempFilters = $this->personalFilterToFilterRepository->find_filters($personal_filter_id, $type);
            foreach ($tempFilters as $filter) {
                $this->personalFilterToFilterRepository->delete($filter->id);
            }


            foreach ($request->filter_id as $filter) {
                $db = $this->filterRepository->find($filter);
                if (!isset($db)) {
                    $response->setError('Filter not found!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }

                $db = new PersonalFilterToFilter();
                $db->filter_id = $filter;
                $db->personal_filter_id = $personal_filter_id;
                $db->type = $type;

                if ($this->personalFilterToFilterRepository->save($db)) {
                    $response->setCode(ApiCodeEnum::CREATED);
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

    //list SELF
    public function list_me(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        $type = PersonalFilterTypeEnum::me;

        try {
            $pf = $this->personalFilterRepository->get_user_personal_filter();
            if (!isset($pf)) {
                //create default personal filter
                $new_personal_filter = $this->personalFilterRepository->create_default(); 
                if (!$new_personal_filter) {
                    $response->setError('Error creating default filters for user!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
                $pf = $this->personalFilterRepository->get_user_personal_filter();
            }
            $personal_filter_id = $pf->id;

            $tFilters = $this->personalFilterToFilterRepository->find_filters($personal_filter_id, $type);
            if (!isset($tFilters)) {
                $response->setError('Filters not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            $filters = new Collection();
            foreach ($tFilters as $f) {
                $fid = $f->filter_id;

                $filter = $this->filterRepository->find($fid);
                if(!isset($filter)){
                    $response->setError('One of the filters does not exist!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
                $filters->push($filter);
            }

            $response->setData($filters);

        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    //list LOOKING
    public function list_looking(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        $type = PersonalFilterTypeEnum::looking;

        try {
            $pf = $this->personalFilterRepository->get_user_personal_filter();
            if (!isset($pf)) {
                //create default personal filter
                $new_personal_filter = $this->personalFilterRepository->create_default(); 
                if (!$new_personal_filter) {
                    $response->setError('Error creating default filters for user!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
                $pf = $this->personalFilterRepository->get_user_personal_filter();
            }
            $personal_filter_id = $pf->id;

            $tFilters = $this->personalFilterToFilterRepository->find_filters($personal_filter_id, $type);
            if (!isset($tFilters)) {
                $response->setError('Filters not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            $filters = new Collection();
            foreach ($tFilters as $f) {
                $fid = $f->filter_id;

                $filter = $this->filterRepository->find($fid);
                if(!isset($filter)){
                    $response->setError('One of the filters does not exist!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
                $filters->push($filter);
            }

            $response->setData($filters);

        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function list_user_self(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        $type = PersonalFilterTypeEnum::me;

        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer'
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            $user_id = $request->user_id;

            $pf = $this->personalFilterRepository->get_personal_filter_by_user($user_id);
            if (!isset($pf)) {
                //create default personal filter
                $new_personal_filter = $this->personalFilterRepository->create_default_by_user($user_id); 
                if (!$new_personal_filter) {
                    $response->setError('Error creating default filters for user!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
                $pf = $this->personalFilterRepository->get_personal_filter_by_user($user_id);
            }
            $personal_filter_id = $pf->id;

            $tFilters = $this->personalFilterToFilterRepository->find_filters($personal_filter_id, $type);
            if (!isset($tFilters)) {
                $response->setError('Filters not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            $filters = new Collection();
            foreach ($tFilters as $f) {
                $fid = $f->filter_id;

                $filter = $this->filterRepository->find($fid);
                if(!isset($filter)){
                    $response->setError('One of the filters does not exist!');
                    $response->setCode(ApiCodeEnum::NO_CONTENT);
                    return response()->json($response);
                }
                $filters->push($filter);
            }

            $response->setData($filters);

        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }
}
