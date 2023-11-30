<?php

namespace Noorfarooqy\NoorAuth\Interfaces;

interface ServicesInterface
{
    
    public function setStatus(int $status);
    public function getStatus(): int;
    public function setError(string $error);
    public function getMessage(): string;

    public function setSuccess(string $error);
    public function getSuccessMessage(): string;

    public function customValidate();

    public function validatedData(): array;
    public function setResponseType();

    public function getResponse($data): object;
}
