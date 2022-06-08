<?php

namespace App\Http\Middleware;

use App\AppModels\ApiModel;
use Closure;
use Illuminate\Http\Request;

use JWTAuth;
use Exception;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use App\Core\ApiCodeEnum;


class JwtMiddleware extends BaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = new ApiModel();
        $response->setCode(ApiCodeEnum::UNAUTHORIZED);

        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                $response->setMessage('Token is Invalid');
                return response()->json($response);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                $response->setMessage('Token is Expired');
                return response()->json($response);
            }else{
                $response->setMessage('Authorization Token not found');
                return response()->json($response);
            }
        }
        return $next($request);
    }
}
