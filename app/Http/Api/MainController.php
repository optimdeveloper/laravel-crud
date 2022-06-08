<?php

namespace App\Http\Api;

use App\AppModels\ApiModel;
use App\AppModels\KeyValueModel;
use App\Core\ApiCodeEnum;
use App\Http\Api\Base\ApiController;
use App\Services\LogServiceInterface;
use Illuminate\Http\JsonResponse;

class MainController extends ApiController
{
    public function __construct(LogServiceInterface $logger)
    {
        parent::__construct($logger);
    }

    public function health(): JsonResponse
    {
        $response = new ApiModel();

        $version = new KeyValueModel();
        $version->setKey('APP_VERSION');
        $version->setValue(env('APP_VERSION'));

        $response->setData($version);
        $response->setTimestamp(now()->timestamp);
        $response->setCode(ApiCodeEnum::SUCCESS);
        return response()->json($response);
    }
}
