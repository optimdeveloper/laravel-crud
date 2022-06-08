<?php


namespace App\AppModels;

use App\Core\ApiCodeEnum;
use Illuminate\Support\Collection;

class ApiModel
{
    public int $code;
    public object $data;
    public Collection $list;
    public ?int $timestamp;
    public string $message;

    public function setSuccess()
    {
        $this->setCode(ApiCodeEnum::SUCCESS);
        $this->setMessage(ApiCodeEnum::toString(ApiCodeEnum::SUCCESS));
    }

    public function setError($message = null)
    {
        if (!$message)
            $message = ApiCodeEnum::toString(ApiCodeEnum::ERROR);

        $this->setCode(ApiCodeEnum::ERROR);
        $this->setMessage($message);
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    /**
     * @return object
     */
    public function getData(): object
    {
        return $this->data;
    }

    /**
     * @param object $data
     */
    public function setData(object $data): void
    {
        $this->data = $data;
    }

    /**
     * @return Collection
     */
    public function getList(): Collection
    {
        return $this->list;
    }

    /**
     * @param Collection $list
     */
    public function setList(Collection $list): void
    {
        $this->list = $list;
    }

    /**
     * @return int|null
     */
    public function getTimestamp(): ?int
    {
        return $this->timestamp;
    }

    /**
     * @param int|null $timestamp
     */
    public function setTimestamp(?int $timestamp): void
    {
        $this->timestamp = $timestamp;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }
}
