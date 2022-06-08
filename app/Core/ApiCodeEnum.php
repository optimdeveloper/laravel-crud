<?php


namespace App\Core;


class ApiCodeEnum extends BaseEnum
{
    const UNDEFINED = 0;
    const SUCCESS = 200;
    const ERROR = 100;

    const CREATED = 201;
    const NO_CONTENT = 204;
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const MOBILEFAILURE = 404;
}
