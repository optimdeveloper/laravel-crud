<?php

namespace App\Http\Api\Messaging;

use Ably;
use App\AppModels\ApiModel;
use App\Core\ApiCodeEnum;
use App\Http\Api\Base\ApiController;
use App\Services\LogServiceInterface;
use Illuminate\Http\JsonResponse;
use Throwable;

class AblyController extends ApiController
{
    public function __construct(LogServiceInterface $logger)
    {
        parent::__construct($logger);
    }
    public function send(): JsonResponse
    {
        $response = new ApiModel();
        $response->setSuccess();

        try {

            echo Ably::time();
            $token = Ably::auth()->requestToken([ 'clientId' => 'client123', ]);
            Ably::channel('testChannel')->publish('testEvent', 'testPayload', 'testClientId');

            $response->setData($token);
        } catch (Throwable $ex) {
            $this->logger->save($ex);
        }

        return response()->json($response);
    }
}
