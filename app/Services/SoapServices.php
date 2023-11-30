<?php

namespace Noorfarooqy\Flexcube\Services;

class SoapServices
{
    public function __construct()
    {
        $this->SetRequest();
        $this->SetHeader();
    }

    /*
     * @var Array
     * QUERYACCBAL_IOFS_REQ
     */
    protected $QUERYACCBAL_IOFS_REQ;

    /*
     * FCUBS_HEADER
     */
    protected $FCUBS_HEADER;

    protected $FCUBS_BODY;

    public function GetRequestData()
    {
        return $this->QUERYACCBAL_IOFS_REQ;
    }
    public function SetRequest()
    {
        $this->QUERYACCBAL_IOFS_REQ = [
            'QUERYACCBAL_IOFS_REQ' => [
                'FCUBS_HEADER' => $this->FCUBS_HEADER,
                'FCUBS_BODY' => $this->FCUBS_BODY,
            ],

        ];
    }

    public function SetHeader($service = 'FCUBSAccService', $operation = 'QueryAccBal', $branch = '001', $source = 'FCAT', $ubscamp = 'FCUBS', $userid = 'FCATOP')
    {
        $this->FCUBS_HEADER = [
            'SOURCE' => $source,
            'UBSCOMP' => $ubscamp,
            'USERID' => $userid,
            'BRANCH' => $branch,
            'SERVICE' => $service,
            'OPERATION' => $operation,
        ];
    }
    public function SetBody($REQUEST_BODY)
    {
        $this->FCUBS_BODY = $REQUEST_BODY;
    }
}
