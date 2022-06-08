<?php


namespace App\Services;

use Illuminate\Support\Collection;
use Throwable;

interface LogServiceInterface
{
    public function save(Throwable $ex, $user_id = null): void;

    public function log(string $error, $user_id = null): void;
}
