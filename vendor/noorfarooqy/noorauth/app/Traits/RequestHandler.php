<?php

namespace Noorfarooqy\NoorAuth\Traits;

use Illuminate\Support\Facades\Validator;

trait RequestHandler
{

    protected $rules;
    protected $request;
    protected $validator;
    protected $has_failed;
    protected $is_json;
    protected $checksum;

    public function customValidate(array $checksum_fields = [])
    {
        $this->validator = Validator::make($this->request->all(), $this->rules);
        $this->has_failed = $this->validator->fails();
        $this->setError($this->validator->errors()->first());
        $this->generateChecksum($checksum_fields);
    }

    public function generateChecksum($checksum_fields)
    {
        $this->checksum = hash('sha256', implode('', $checksum_fields));
    }

    public function validatedData(): array
    {
        return $this->validator->validated();
    }
    public function setResponseType()
    {
        $this->is_json = $this->request?->expectsJson() ?? true;
    }
}
