<?php

namespace App\Http\Api\Twilio;

use App\AppModels\ApiModel;
use App\AppModels\KeyValueModel;
use App\Http\Api\Base\ApiController;
use App\Services\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\ChatGrant;

class ChatController extends ApiController
{
    public function __construct(LogServiceInterface $logger)
    {
        parent::__construct($logger);
    }

    public function token(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {
            if (Auth::check()) {
                $user = Auth::user();

                // Required for all Twilio access tokens
                $twilio_account_sid = env('TWILIO_ACCOUNT_SID', '');
                $twilio_api_key = env('TWILIO_API_KEY', '');
                $twilio_api_secret = env('TWILIO_API_SECRET', '');

                // Required for Chat grant
                $service_sid = env('TWILIO_CHAT_SERVICE_SID', '');

                // choose a random username for the connecting user
                $identity = $user->email;

                // Create access token, which we will serialize and send to the client
                $token = new AccessToken(
                    $twilio_account_sid,
                    $twilio_api_key,
                    $twilio_api_secret,
                    86400,//3600
                    $identity
                );

                // Create Chat grant
                $chatGrant = new ChatGrant();
                $chatGrant->setServiceSid($service_sid);

                // Add grant to token
                $token->addGrant($chatGrant);

                // render token to string
                $data = new KeyValueModel();
                $data->setKey('token');
                $data->setValue($token->toJWT());

                $response->setData($data);
            }
        } catch (Throwable $ex) {
            $this->logger->save($ex);
            $response->setError($ex->getMessage());
        }

        return response()->json($response);
    }
}
