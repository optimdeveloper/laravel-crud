<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use App\Core\ApiCodeEnum;
use App\Http\Api\Base\ApiController;
use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Product;
use App\Repositories\EventRepositoryInterface;
use App\Repositories\GuestRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\ProductRepositoryInterface;
use App\Services\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Throwable;

class GuestController extends ApiController
{
    private GuestRepositoryInterface $guestRepository;
    private EventRepositoryInterface $eventRepository;
    private UserRepositoryInterface $userRepository;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        GuestRepositoryInterface $guestRepository,
        LogServiceInterface $logger,
        EventRepositoryInterface $eventRepository,
        ProductRepositoryInterface $productRepository,
        UserRepositoryInterface $userRepository,

    ) {
        parent::__construct($logger);
        $this->guestRepository =  $guestRepository;
        $this->eventRepository = $eventRepository;
        $this->userRepository = $userRepository;
        $this->productRepository = $productRepository;
    }

    public function me(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->guestRepository->me(Auth::id());
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
            $query = $this->guestRepository->all();
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
            $query = $this->guestRepository->find($id);
            if (!isset($query)) {
                $response->setError('Guest not found!');
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
            $query = $this->guestRepository->deleteLogic($id);
            if ($query == true) {
                $response->setMessage('Guest deleted Successfully!');
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

    public function remove($id): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $lguest = $this->guestRepository->find($id);
            $query = $this->guestRepository->delete($id);
            if ($query == true) {
                $response->setMessage('Guest deleted Successfully!');
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
            $query = $this->guestRepository->restore($id);
            if ($query == true) {
                $response->setMessage('Guest restored Successfully!');
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
                'status' => ['required', 'string', Rule::in(['Invited', 'Going', 'Maybe', 'Interested'])],
                'event_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            $db = $this->eventRepository->find($request->get('event_id'));
            if (!isset($db)) {
                $response->setError('Event not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            $db = $this->userRepository->find(Auth::id());
            if (!isset($db)) {
                $response->setError('User not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            $db = $this->guestRepository->search_guest($request->event_id, Auth::id());
            if (!isset($db)) {
                $db = new Guest();
            }

            // dd($db);
            $db->status = $request->status;
            $db->event_id = $request->event_id;
            $db->user_id = Auth::id();

            if ($this->guestRepository->save($db)) {
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


    public function only_update(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'status' => ['required', 'string', Rule::in(['Invited', 'Going', 'Maybe', 'Interested'])],
                'event_id' => 'required|integer',
            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            $db = $this->eventRepository->find($request->get('event_id'));
            if (!isset($db)) {
                $response->setError('Event not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            $db = $this->userRepository->find(Auth::id());
            if (!isset($db)) {
                $response->setError('User not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            $db = $this->guestRepository->search_guest($request->event_id, Auth::id());
            if (!isset($db)) {
                $response->setError('Guest not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            $db->status = $request->status;
            $db->event_id = $request->event_id;
            $db->user_id = Auth::id();

            if ($this->guestRepository->save($db)) {
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

    public function pusher_eventupdated(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();
        try {
            $myngly_events_url = env('MYNGLY_EVENTS_URL', '');
            if ($myngly_events_url != '')
            {
                $rsp = Http::get($myngly_events_url . $request->get('event_id'));
                $response->setCode($rsp->status());
            }
        } catch (Throwable $ex) {
            $response->setError('Something went wrong!');
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }
        return response()->json($response);
    }
    //crud
    public function create_product(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $validator = Validator::make($request->all(), [
            'image' =>  'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'price' => 'required|string',
            'description' => 'required|string',
            'title' => 'required|string',

        ]);
        if ($validator->fails()) {
            $response->setMessage($validator->errors());
            $response->setCode(ApiCodeEnum::BAD_REQUEST);
            return response()->json($response);
        }
       if($request->image != null){
        $db= Product::create(array_merge($validator->validate(),['image' =>upload($request)]));
       }else{
        $db= Product::create(array_merge($validator->validate()));
       }

        $response->setCode(ApiCodeEnum::CREATED);
        $response->setData($db);

        return response()->json($response);
    }

  public function list_products(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {

            $query = $this->productRepository->all();
            $response->setData($query);

        } catch (Throwable $ex) {

            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }
    public function delete_product($id): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->productRepository->delete($id);
            if ($query == true) {
                $response->setMessage('Product deleted Successfully!');
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
    public function update_product($id,Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'image' =>  'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'price' => 'required|string',
                'description' => 'required|string',
                'title' => 'required|string',

            ]);
            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }

            $db = $this->productRepository->find($id);
            if (!isset($db)) {
                $response->setError('Product not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }
            if($request->image != null){
                $file= $request->file('image');
                $filename= date('YmdHi').$file->getClientOriginalName();
                $file-> move(public_path('public/Image'), $filename);
                $db->image = $filename;
               }

            $db->title = $request->title;
            $db->description = $request->description;
            $db->price = $request->price;

            if ($this->productRepository->save($db)) {
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
