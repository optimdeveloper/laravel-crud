<?php


namespace App\Services;

use App\Core\ApiCodeEnum;
use App\Core\AppErrorEnum;
use App\Repositories\LogRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models;
use Throwable;

class LogService implements LogServiceInterface
{
    private LogRepositoryInterface $repository;

    public function __construct(LogRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function save(Throwable $ex, $user_id = null): void
    {
        $this->saveDb(strval(AppErrorEnum::GENERIC), $ex->getMessage(), $ex->getTraceAsString(), $user_id);
    }

    public function log(string $error, $user_id = null): void
    {
        $this->saveDb(strval(AppErrorEnum::GENERIC), $error, "", $user_id);
    }

    private function saveDb(string $code, string $message, string $trace, $user_id = null): void
    {
        try {

            if ($user_id == null) {
                if (Auth::check() && Auth::user() != null)
                    $user_id = Auth::user()->id;
            }

            $db = new Models\Log();

            if (strlen($message) > 250)
                $message = substr($message, 0, 250);

            if (strlen($trace) > 750)
                $trace = substr($trace, 0, 750);

            $db->code = $code;
            $db->error = $message;
            $db->trace = $trace;
            // $db->user_id = $user_id;
            $db->published = 1;
            $this->repository->save($db);
        } catch (Throwable $e) {
            Log::error($e->getMessage());
        }
    }
}
