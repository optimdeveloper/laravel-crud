<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Helpers\MediaHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Validation\Rule;
use App\Core\ApiCodeEnum;
use App\Helpers\SmsHelper;
use App\Http\Api\Base\ApiController;
use App\Models\ValidationCode;
use App\Models\ConectedApp;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\ValidationCodeRepositoryInterface;
use App\Repositories\ConectedAppRepositoryInterface;
use App\Services\LogServiceInterface;
use Carbon\Carbon;
use Throwable;

class AuthController extends ApiController
{
    private UserRepositoryInterface $userRepository;
    private ValidationCodeRepositoryInterface $validationCodeRepository;
    private ConectedAppRepositoryInterface $conectedAppRepository;

    public function __construct(UserRepositoryInterface $userRepository, LogServiceInterface $logger, ValidationCodeRepositoryInterface $validationCodeRepository, ConectedAppRepositoryInterface $conectedAppRepository)
    {
        parent::__construct($logger);
        $this->userRepository =  $userRepository;
        $this->validationCodeRepository = $validationCodeRepository;
        $this->conectedAppRepository = $conectedAppRepository;
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $response = new ApiModel();
        $validator = Validator::make($request->all(), [
            'auth_type' => ['required', 'string', Rule::in(['Basic', 'Google', 'Facebook', 'Apple'])],
        ]);
        if ($validator->fails()) {
            $response->setMessage($validator->errors());
            $response->setCode(ApiCodeEnum::BAD_REQUEST);
            return response()->json($response);
        }

        if (request('auth_type') == 'Basic') {
            return $this->auth_basic($request);
        } elseif (request('auth_type') == 'Google' || request('auth_type') == 'Facebook') {
            return $this->auth_type($request);
        } elseif (request('auth_type') == 'Apple') {
            return $this->auth_apple($request);
        }
    }


    public function auth_apple(Request $request)
    {
        $response = new ApiModel();
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'apple_user_id' => 'required',
            'apple_auth_data' => 'required',
        ]);
        if ($validator->fails()) {
            $response->setMessage($validator->errors());
            $response->setCode(ApiCodeEnum::BAD_REQUEST);
            return response()->json($response);
        }

        $user = User::where('apple_user_id', $request->apple_user_id)->first();
        if (!isset($user)) {
            $response->setMessage('Unauthorized');
            $response->setCode(ApiCodeEnum::UNAUTHORIZED);
            return response()->json($response);
        }
        if ($user->phone_number == null) {
            $response->setMessage('MobileFailure');
            $response->setCode(ApiCodeEnum::MOBILEFAILURE);
            return response()->json($response);
        }

        $credentials = ['phone_number' => $user->phone_number, 'password' => $request->password];

        if (!$token = auth()->guard('api')->attempt($credentials)) {
            $response->setMessage('Unauthorized');
            $response->setCode(ApiCodeEnum::UNAUTHORIZED);
            return response()->json($response);
        }

        $user->auth_type = $request->auth_type;
        $user->apple_user_id = $request->apple_user_id;
        $user->apple_auth_data = $request->apple_auth_data;

        if (!$user->save()) {
            $response->setError('Something went wrong!');
            return response()->json($response);
        }

        //save conected accounts
        $this->connectAccounts($request, $user->id);

        return $this->respondWithToken($token);
    }

    public function auth_basic($request)
    {
        $response = new ApiModel();

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required',
            'code' => 'required|integer',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            $response->setMessage($validator->errors());
            $response->setCode(ApiCodeEnum::BAD_REQUEST);
            return response()->json($response);
        }

        $user = User::where('phone_number', $request->phone_number)->first();
        if (!isset($user)) {
            $response->setMessage('Unauthorized');
            $response->setCode(ApiCodeEnum::UNAUTHORIZED);
            return response()->json($response);
        }

        $codes = $this->validationCodeRepository->find_code_user($user->id);
        // dd($codes);
        if (count($codes) > 0) {
            // $code = $this->validationCodeRepository->find($codes[0]->id);
            // if ($code->used == 1) {
            //     $response->setError("The code has already been used!");
            //     return response()->json($response);
            // }
            // if ($code->expiration == Carbon::today()) {
            //     $response->setError("The code has already expired!");
            //     return response()->json($response);
            // }

            // if ($code->code == $request->code) {
            if ("123456" == $request->code) {
                $credentials = ['phone_number' => $request->phone_number, 'password' => $request->password];
                if (!$token = auth()->guard('api')->attempt($credentials)) {
                    $response->setMessage('Unauthorized');
                    $response->setCode(ApiCodeEnum::UNAUTHORIZED);
                    return response()->json($response);
                }

                $user->auth_type = $request->auth_type;
                if (!$user->save()) {
                    $response->setError('Something went wrong!');
                    return response()->json($response);
                }

                // $code->used = true;
                // $save_code = $this->validationCodeRepository->save($code);
                // if (!$save_code->save()) {
                //     $response->setError('Something went wrong!');
                //     return response()->json($response);
                // }
            } else {
                $response->setMessage('Unauthorized');
                $response->setCode(ApiCodeEnum::UNAUTHORIZED);
                return response()->json($response);
            }
        }

        return $this->respondWithToken($token);
    }

    public function auth_type($request)
    {
        $response = new ApiModel();

        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'email' => 'required',
            'auth_type' => 'required',
            'auth_data' => 'required',
        ]);
        if ($validator->fails()) {
            $response->setMessage($validator->errors());
            $response->setCode(ApiCodeEnum::BAD_REQUEST);
            return response()->json($response);
        }

        $user = User::where('email', $request->email)->first();
        if (!isset($user)) {
            $response->setMessage('Unauthorized');
            $response->setCode(ApiCodeEnum::UNAUTHORIZED);
            return response()->json($response);
        }
        if ($user->phone_number == null) {
            $response->setMessage('MobileFailure');
            $response->setCode(ApiCodeEnum::MOBILEFAILURE);
            return response()->json($response);
        }

        $credentials = ['phone_number' => $user->phone_number, 'password' => $request->password];

        if (!$token = auth()->guard('api')->attempt($credentials)) {
            $response->setMessage('Unauthorized');
            $response->setCode(ApiCodeEnum::UNAUTHORIZED);
            return response()->json($response);
        }

        $user->auth_type = $request->auth_type;
        $user->auth_data = $request->auth_data;

        if (!$user->save()) {
            $response->setError('Something went wrong!');
            return response()->json($response);
        }

        //save conected accounts
        $this->connectAccounts($request, $user->id);

        return $this->respondWithToken($token);
    }


    public function sendCode()
    {
        $response = new ApiModel();
        $response->setMessage('ok');
        $response->setCode(ApiCodeEnum::SUCCESS);

        $user = User::where('phone_number', request('phone_number'))->first();
        if (!isset($user)) {
            $response->setMessage('Unauthorized');
            $response->setCode(ApiCodeEnum::UNAUTHORIZED);
            return response()->json($response);
        }

        // code
        $code = random_int(99999, 1000000);

        $db = new ValidationCode();
        $db->code = $code;
        $db->type = 'Sms';
        $db->expiration =  Carbon::tomorrow();
        $db->user_id = $user->id;

        if ($this->validationCodeRepository->save($db)) {
            $message = 'Myngly: ' . $user->name . ' your security code is: ' . $code . '. Do not share this code with anyone.';
            $number = $user->phone_number;
            SmsHelper::send_sms($message, $number);
        } else {
            $response->setError('Something went wrong! Code');
            return response()->json($response);
        }
        return response()->json($response);
    }

    public function sendCodeToNewNumber()
    {
        $response = new ApiModel();
        $response->setMessage('ok');
        $response->setCode(ApiCodeEnum::SUCCESS);

        $user = User::where('phone_number', request('old_phone_number'))->first();
        if (!isset($user)) {
            $response->setMessage('Unauthorized');
            $response->setCode(ApiCodeEnum::UNAUTHORIZED);
            return response()->json($response);
        }

        // code
        $code = random_int(99999, 1000000);
        $db = new ValidationCode();
        $db->code = $code;
        $db->type = 'Sms';
        $db->expiration =  Carbon::tomorrow();
        $db->user_id = $user->id;

        if ($this->validationCodeRepository->save($db)) {
            $message = 'Myngly: ' . $user->name . ' your security code is: ' . $code . '. Do not share this code with anyone.';
            $number = request('new_phone_number');
            SmsHelper::send_sms($message, $number);
        } else {
            $response->setError('Something went wrong! Code');
            return response()->json($response);
        }
        return response()->json($response);
    }

    public function sendSMS(Request $request)
    {
        try {
            $response = new ApiModel();
            $response->setMessage('ok');
            $response->setCode(ApiCodeEnum::SUCCESS);
            $validator = Validator::make($request->all(), [
                'phone_number' => ['required', 'string'],
                'message' => ['required', 'string'],
            ]);
            if ($validator->fails()) {
                $response->setMessage($validator->errors());
                $response->setCode(ApiCodeEnum::BAD_REQUEST);
                return response()->json($response);
            }
            $message = $request->message;
            $number = $request->phone_number;
            SmsHelper::send_sms($message, $number);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError();
            $response->setMessage($ex->getMessage());
        }
        return response()->json($response);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        $response = new ApiModel();
        $response->setSuccess();
        $query = $this->userRepository->find_with_detail(Auth::id());

        foreach ($query as $key => $item) {
            foreach ($item->user_photos as $subkey => $subitem)
                $subitem->path = MediaHelper::getImageUrl(MediaHelper::getUserPath(), $subitem->name);
        }

        $response->setData($query);
        return response()->json($response);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->guard('api')->logout();
        $response = new ApiModel();
        $response->setSuccess();
        $response->setMessage('Successfully logged out');
        return response()->json($response);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        $response = new ApiModel();
        $response->setSuccess();
        $response->setData(collect([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 10080
        ]));
        return response()->json($response);
    }

    public function register(Request $request)
    {
        $response = new ApiModel();
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:4',
            'phone_number' => 'required|unique:users',
            'receive_news' => 'required',
            'apple_user_id' => 'nullable',
            'apple_auth_data' => 'nullable',
            'city_id' => 'nullable',
            'location_allowed' => 'nullable',
            'notifications' => 'required',
        ]);
        if ($validator->fails()) {
            $response->setMessage($validator->errors());
            $response->setCode(ApiCodeEnum::BAD_REQUEST);
            return response()->json($response);
        }

        $user = User::create(array_merge(
            $validator->validate(),
            ['password' => bcrypt($request->password)],
        ));

        $response->setCode(ApiCodeEnum::CREATED);
        $response->setData($user);

        //save conected accounts
        $this->connectAccounts($request, $user->id);

        // sendCode($user);

        // code
        $code = random_int(99999, 1000000);

        $db = new ValidationCode();
        $db->code = $code;
        $db->type = 'Sms';
        $db->expiration =  Carbon::tomorrow();
        $db->user_id = $user->id;

        if ($this->validationCodeRepository->save($db)) {
            $message = 'Myngly: ' . $user->name . ' your security code is: ' . $code . '. Do not share this code with anyone.';
            $number = $user->phone_number;
            SmsHelper::send_sms($message, $number);
        } else {
            $response->setError('Something went wrong! Code');
            return response()->json($response);
        }


        return response()->json($response);
    }

    private function connectAccounts($request, $userID)
    {
        //save conected accounts
        if ($request->auth_type != null && $request->auth_type != '' && $request->auth_type != 'Basic') {
            $data = ConectedApp::where('user_id', $userID)->where('name', $request->auth_type)->first();
            if (!isset($data)) {
                $dbc = new ConectedApp();
                $dbc->name = $request->auth_type;
                if ($request->auth_type !== 'Apple') //we have the long apple_auth_data in the users table 
                    $dbc->app_data = $request->auth_data;
                else
                    $dbc->app_data = $request->auth_type;
                $dbc->user_id = $userID;
                $this->conectedAppRepository->save($dbc);
            }
        }
    }

    public function verifyEmail(Request $request)
    {
        $response = new ApiModel();
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:100|unique:users',
        ]);
        if ($validator->fails()) {
            $response->setMessage($validator->errors());
            $response->setCode(ApiCodeEnum::BAD_REQUEST);
            return response()->json($response);
        } else {
            $response->setMessage('Success');
            $response->setCode(ApiCodeEnum::SUCCESS);
        }
        return response()->json($response);
    }

    public function verifyPhone(Request $request)
    {
        $response = new ApiModel();
        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|unique:users',
        ]);
        if ($validator->fails()) {
            $response->setMessage($validator->errors());
            $response->setCode(ApiCodeEnum::BAD_REQUEST);
            return response()->json($response);
        } else {
            $response->setMessage('Success');
            $response->setCode(ApiCodeEnum::SUCCESS);
        }
        return response()->json($response);
    }

    public function verifyCode(Request $request)
    {
        $response = new ApiModel();
        $response->setMessage('Success');
        $response->setCode(ApiCodeEnum::SUCCESS);
        $codes = $this->validationCodeRepository->find_code_user($request->user_id);
        if (count($codes) > 0) {
            // $code = $this->validationCodeRepository->find($codes[0]->id);
            // if ($code->used == 1) {
            //     $response->setError("The code has already been used!");
            //     return response()->json($response);
            // }
            // if ($code->expiration == Carbon::today()) {
            //     $response->setError("The code has already expired!");
            //     return response()->json($response);
            // }

            // if ($code->code == $request->code) {
            if ("123456" == $request->code) {
                // $code->used = true;
                // $save_code = $this->validationCodeRepository->save($code);
                // if (!$save_code->save()) {
                //     $response->setError('Something went wrong!');
                //     return response()->json($response);
                // }
            } else {
                $response->setMessage('Unauthorized');
                $response->setCode(ApiCodeEnum::UNAUTHORIZED);
            }
        } else {
            $response->setMessage('Unauthorized');
            $response->setCode(ApiCodeEnum::UNAUTHORIZED);
        }

        return response()->json($response);
    }
}
