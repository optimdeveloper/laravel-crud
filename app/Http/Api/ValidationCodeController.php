<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use App\Core\ApiCodeEnum;
use App\Helpers\SmsHelper;
use App\Http\Api\Base\ApiController;
use App\Http\Controllers\Controller;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\ValidationCodeRepositoryInterface;
use App\Services\LogServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ValidationCodeController extends ApiController
{
    private ValidationCodeRepositoryInterface $validationCodeRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(LogServiceInterface $logger, ValidationCodeRepositoryInterface $validationCodeRepository,
                                UserRepositoryInterface $userRepository)
    {
        parent::__construct($logger);
        $this->validationCodeRepository = $validationCodeRepository;
        $this->userRepository = $userRepository;
    }
    // public function send_code()
    // {
    //     $code = random_int(99999, 1000000);
    //     $message = 'Myngly: your security code is: '. $code .'. Do not share this code with anyone.';
    //     $number = '+573013283038';
    //     $response = SmsHelper::send_sms($message, $number);
    //     print($response);
    // }

    public function validate_code(Request $request) : JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            $validator = Validator::make($request->all(), [
                // 'user_id' => 'required|integer',
                'code' => 'required|integer',
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

            $db = $this->validationCodeRepository->find_code_user(Auth::id());
            //dd($db->expiration == Carbon::today());
            if (!isset($db)) {
                $response->setError('Code not found!');
                $response->setCode(ApiCodeEnum::NO_CONTENT);
                return response()->json($response);
            }

            if ($db->used == 1) {
                $response->setError("The code has already been used!");
                return response()->json($response);
            }

            if ($db->expiration == Carbon::today()) {
                $response->setError("The code has already expired!");
                return response()->json($response);
            }

            if ($db->code == $request->get('code')) {
                $db->used = true;
                if($this->validationCodeRepository->save($db))
                {
                    $response->setData($db);
                    $response->setCode(ApiCodeEnum::CREATED);
                }else {
                    $response->setError('Something went wrong!');
                    return response()->json($response);
                }
            } else {
                $response->setError("The code doesn't match!");
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
