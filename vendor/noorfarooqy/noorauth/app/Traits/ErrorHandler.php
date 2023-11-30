<?php

namespace Noorfarooqy\NoorAuth\Traits;

trait ErrorHandler
{

    private $error_message = '';
    private $status = 200;
    private $error_code = 0;
    private $success_message = '';
    private $error_descriptions = [];
    public function setError(string $error, $error_code = '_001')
    {
        $this->error_message = $error;
        $this->error_code = $error_code;
        $this->error_descriptions = $this->getErrorDescription();
        $this->error_description = $this->getErrorDescription();
    }
    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    public function getErrorDescription()
    {
        return config('error_codes.'.$this->error_code);
    }
    public function getErrorCode()
    {
        return $this->error_code;
    }

    public function getMessage(): string
    {
        return $this->error_message ?? '';
    }
    public function getStatus(): int
    {
        return $this->status;
    }

    public function setSuccess(string $message)
    {
        $this->success_message = $message;
    }
    public function getSuccessMessage(): string
    {
        return $this->success_message;
    }
}
