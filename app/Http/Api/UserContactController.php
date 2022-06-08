<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use App\Core\ApiCodeEnum;
use App\Models\User;
use App\Http\Api\Base\ApiController;
use App\Http\Controllers\Controller;
use App\Models\UserContact;
use App\Repositories\UserContactRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\Services\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Throwable;

class UserContactController extends ApiController
{
    private UserContactRepositoryInterface $userContactRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        UserContactRepositoryInterface $userContactRepository,
        LogServiceInterface $logger,
        UserRepositoryInterface $userRepository
    ) {
        parent::__construct($logger);
        $this->userContactRepository =  $userContactRepository;
        $this->userRepository = $userRepository;
    }

    public function list(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $query = $this->userContactRepository->all();
            $response->setData($query);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }

        return response()->json($response);
    }

    public function add_update_my_contacts(Request $request): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                'numbers' => 'array',
            ]);

            if ($validator->fails()) {
                $response->setError($validator->getMessageBag());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }
            //avoid my onw number
            $me = User::where('id', Auth::id())->first();
            //get the current contacts and the ones that have the requested phone numbers
            $current_contacts = UserContact::where('user_id', Auth::id())->where('is_phone_contact',true)->get();
            $current_users_ids = UserContact::where('user_id', Auth::id())->where('is_phone_contact',true)->pluck('contact_id');
            $requested_users = User::whereIn('phone_number', $request->numbers)->where('phone_number', '!=', $me->phone_number)->whereNotIn('id',$current_users_ids)->get();

            if (isset($requested_users) && count($requested_users) > 0) {
                $allmycontacts = [];
                //get the current user contacts
                if (isset($current_contacts)) {
                    foreach ($current_contacts as $cc) {
                        $contact = new UserContact();
                        $contact->status = $cc->status;
                        $contact->user_id = Auth::id();
                        $contact->contact_id = $cc->contact_id;
                        $allmycontacts[] = $contact->attributesToArray();
                    }
                }
                //add the requested users
                foreach ($requested_users as $cc) {
                    $contact = new UserContact();
                    $contact->status = 'Unblock';
                    $contact->user_id = Auth::id();
                    $contact->contact_id = $cc->id;
                    $allmycontacts[] = $contact->attributesToArray();
                }
                //delete user contacts to insert the new ones
                $deletedRows = UserContact::where('user_id', Auth::id())->where('is_phone_contact',true)->forceDelete();
                //bulk insert all the user contacts
                $allmycontacts=array_map(function ($a) { 
                    return array_merge($a,['created_at'=> 
                    Carbon::now(),'updated_at'=> Carbon::now()]
                                       ); 
                                 }, $allmycontacts); 

                UserContact::insert($allmycontacts);
            }
            
            //get the current contacts
            $contacts = UserContact::select('users.id','users.name','users.phone_number','user_contacts.status','user_contacts.id as contact_id', 'user_profiles.work as details')
            ->leftjoin('users', 'users.id', '=', 'user_contacts.contact_id')
            ->leftjoin('user_profiles', 'user_profiles.user_id', '=', 'user_contacts.contact_id')
            ->where('user_contacts.user_id', Auth::id())
            //->where('user_contacts.is_phone_contact',true)
            ->where('user_contacts.status','Unblock')
            ->get();

            $response->setData($contacts);
            $response->setMessage('Total contacts: ' . count($contacts));
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
            $query = $this->userContactRepository->find($id);
            if (!isset($query)) {
                $response->setError('User Contact not found!');
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
            $query = $this->userContactRepository->deleteLogic($id);
            if ($query == true) {
                $response->setMessage('User Contact deleted Successfully!');
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
            $query = $this->userContactRepository->restore($id);
            if ($query == true) {
                $response->setMessage('User Contact restored Successfully!');
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
                //'id' => 'nullable|integer',
                'status' => ['required', 'string', Rule::in(['Unblock', 'Block', 'Other'])],
                // 'user_id' => 'required|integer',
                'contact_id' => 'required|integer',
                'is_phone_contact' => 'required|integer',
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

            $db = $this->userRepository->find($request->get('contact_id'));
            if (!isset($db)) {
                $response->setError('Contact not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            if ($request->get('contact_id') != null) {
                //$db = $this->userContactRepository->find($request->get('contact_id'));
                //$db = UserContact::where('user_id', Auth::id())->where('contact_id', $request->get('contact_id'))->where('is_phone_contact', $request->get('is_phone_contact'))->first();
                $db = UserContact::where('user_id', Auth::id())->where('contact_id', $request->get('contact_id'))->first();
                if (!isset($db)) {
                    //$response->setError('User Contact not found!');
                    //$response->setCode(ApiCodeEnum::NO_CONTENT);
                    //return response()->json($response);
                    $db = new UserContact();
                }
            } else {
                $db = new UserContact();
            }

            $db->status = $request->status;
            $db->user_id = Auth::id();
            $db->contact_id = $request->contact_id;
            $db->is_phone_contact = $request->is_phone_contact;
            
            if ($this->userContactRepository->save($db)) {
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
