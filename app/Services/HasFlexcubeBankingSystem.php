<?php

namespace Noorfarooqy\Flexcube\Services;

use Artisaninweb\SoapWrapper\SoapWrapper;
use Illuminate\Support\Facades\Log;
use Noorfarooqy\Flexcube\Helpers\ErrorCodes;
use Noorfarooqy\NoorAuth\Services\NoorServices;

Trait HasFlexcubeBankingSystem 
{


    public $user_id;

    public function __construct()
    {
        $this->user_id  = config('flexcube.user_id');
    }
    public function QueryIACustAccount($request_body)
    {

        $service = 'FCUBSIAService';
        $operation = 'QueryIACustAcc';
        $operation_query = 'FCUBSIAService.QueryIACustAccIO';
        $response = $this->SendCoreBankingRequest($request_body, $service, $operation, $operation_query);
        if ($response) {
            return $response->{'Cust-Account-Full'};
        }
        return false;
    }

    public function QueryAccountBalance($request_body)
    {
        $service = 'FCUBSAccService';
        $operation = 'QueryAccBal';
        $operation_query = 'FCUBSAccService.QueryAccBalIO';
        $response = $this->SendCoreBankingRequest($request_body, $service, $operation, $operation_query);
        if ($response) {
            return $response->{'ACC-Balance'}?->{'ACC_BAL'};
        }
        return false;
    }

    public function CreateTransaction($request_body)
    {
        $service = 'FCUBSRTService';
        $operation = 'CreateTransaction';
        $operation_query = 'FCUBSRTService.CreateTransactionFS';
        $response = $this->SendCoreBankingRequest($request_body, $service, $operation, $operation_query);
        if ($response) {
            return $response->{'Transaction-Details'};
        }
        return false;
    }

    public function ReverseTransaction($request_body)
    {
        $service = 'FCUBSRTService';
        $operation = 'ReverseTransaction';
        $operation_query = 'FCUBSRTService.ReverseTransactionIO';
        $response = $this->SendCoreBankingRequest($request_body, $service, $operation, $operation_query);
        if ($response) {
            return $response->{'FCUBS_WARNING_RESP'};
        }
        return false;
    }

    public function QueryAccountTransactions($request_body)
    {
        $service = 'FCUBSACService';
        $operation = 'QueryAccTrns';
        $operation_query = 'FCUBSACService.QueryAccTrnsIO';
        $response = $this->SendCoreBankingRequest($request_body, $service, $operation, $operation_query);
        if ($response) {
            return $response->{'Acc-Details-Full'};
        }
        return false;
    }

    public function QueryCustomer($request_body)
    {
        $service = 'FCUBSCustomerService';
        $operation = 'QueryCustomer';
        $operation_query = 'FCUBSCustomerService.QueryCustomerIO';
        $response = $this->SendCoreBankingRequest($request_body, $service, $operation, $operation_query);
        if ($response) {
            return $response->{'Customer-Full'};
        }
        return false;
    }

    public function QueryCustomerDetails($request_body)
    {
        $service = 'FCUBSCustomerService';
        $operation = 'QueryCustomerDetails';
        $operation_query = 'FCUBSCustomerService.QueryCustomerDetailsIO';
        $response = $this->SendCoreBankingRequest($request_body, $service, $operation, $operation_query);
        if ($response) {
            return $response->{'Stvws-Stdcifqy-Query-Full'};
        }
        return false;
    }

    public function QueryCustomerAccountDetails($request_body)
    {
        $service = 'FCUBSCustomerService';
        $operation = 'QueryCustAccDetail';
        $operation_query = 'FCUBSCustomerService.QueryCustAccDetailIO';
        $response = $this->SendCoreBankingRequest($request_body, $service, $operation, $operation_query);
        if ($response) {
            return $response->{'Sttms-Customer-Full'};
        }
        return false;
    }


    public function QueryExchangeRate($request_body)
    {
        $service = 'FCUBSCcyService';
        $operation = 'QueryCYDRATEE';
        $operation_query = 'FCUBSCcyService.QueryCYDRATEEIO';
        $response = $this->SendCoreBankingRequest($request_body, $service, $operation, $operation_query);
        if ($response) {
            return $response->{'Ccy-Rate-Master-Full'};
        }
        return false;
    }
    public function SendCoreBankingRequest($request_body, $service, $operation, $operation_query)
    {
        $soapServices = new SoapServices();
        $soapServices->SetBody($request_body);
        $branch = config('flexcube.branch');
        $source = config('flexcube.source');
        $ubscamp = config('flexcube.source');
        $userid = $this->user_id;

        ini_set('default_socket_timeout', 5000);
        $soapServices->SetHeader($service, $operation, $branch, $source, $ubscamp, $userid);
        $soapServices->SetRequest();
        $data = $soapServices->GetRequestData();
        $soapWrapper = new SoapWrapper();
        $url = config('flexcube.fcc_endpoint');
        $service_url = $url . $service . "/" . $service . "?WSDL";
        $soapWrapper->add($service, function ($service) use ($service_url) {
            $service->wsdl($service_url)
                ->trace(true)
                ->classmap(
                    // [
                    //     CustomerAccRequest::class,
                    //     CustomerAccReponse::class,
                    // ]
                );
        });

        $response = $soapWrapper->call($operation_query, $data);

        $failed = $response->FCUBS_HEADER->MSGSTAT != "SUCCESS";
        if (env('APP_DEBUG')) {
            Log::channel('debug')->info(json_encode($response));
        }

        // return $failed;
        if ($failed) {
            $errors = $response->FCUBS_BODY?->FCUBS_ERROR_RESP?->ERROR ?? '';
            if (env('APP_DEBUG')) {
                // LogCbsFailsJob::dispatch($request_body, json_encode($errors));
                Log::channel('debug')->info(json_encode($errors));
            }
            $default_error = 'Request to the CBS Failed. Please contact admin for assistance';
            $this->setError(is_array($errors) ? (count($errors) > 0 ? $errors[1]?->EDESC ?? $default_error : $default_error) : $errors?->{'EDESC'}, ErrorCodes::DEFAULT_ERROR->value);
            return false;
        }
        return $response->FCUBS_BODY;
    }
}
