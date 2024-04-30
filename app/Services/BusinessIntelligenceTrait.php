<?php

namespace Noorfarooqy\Flexcube\Services;

use Artisaninweb\SoapWrapper\SoapWrapper;
use Log;
use Noorfarooqy\NoorAuth\Traits\ResponseHandler;

trait BusinessIntelligenceTrait
{
    use ResponseHandler;

    public function BankStatementReport($account, $from_date, $to_date, $format = 'pdf')
    {
        $request_body = [
            'runReport' => [
                'reportRequest' => [
                    'attributeFormat' => $format,
                    'byPassCache' => 'Y',
                    'flattenXML' => 'Y',
                    'parameterNameValues' => [
                        'item' => [
                            [
                                'multiValuesAllowed' => false,
                                'refreshParamOnChange' => true,
                                'selectAll' => true,
                                'useNullForAll' => false,
                                'templateParam' => false,
                                'name' => 'Account',
                                'value' => [
                                    'item' => $account
                                ],
                            ],
                            [
                                'multiValuesAllowed' => false,
                                'refreshParamOnChange' => true,
                                'selectAll' => true,
                                'useNullForAll' => false,
                                'templateParam' => false,
                                'name' => 'From_Date',
                                'value' => [
                                    'item' => $from_date
                                ],
                            ],
                            [
                                'name' => 'To_Date',
                                'value' => [
                                    'item' => $to_date
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $response = $this->RunReport($request_body);
        return $response;

    }
    public function RunReport($request_body, $service = null)
    {
        $service = $service ?? config('flexcube.bi_reports.services.public_report');
        $endpoint = config('flexcube.bi_reports.endpoit') . '/' . $service . "?WSDL";
        $soapWrapper = new SoapWrapper();
        $soapWrapper->add($service, function ($service) use ($endpoint) {
            $service->wsdl($endpoint)
                ->trace(true)
                ->classmap(
                    [
                        SoapRequest::class,
                        SaopResponse::class,
                    ]
                );
        });
        $operation = $service . '.runReport';
        $response = $soapWrapper->call($operation, $request_body);
        // Log::info($soapWrapper->getLatestRequest());
        $failed = $response?->Fault;
        if ($failed) {
            Log::channel(config('flexcube.log_channel'))->error($response);
            $this->setError($response?->Fault?->faultstring ?? 'Request to the CBS Failed. Please contact admin for assistance', $response?->Fault?->faultcode);
            return false;
        }
        return $response;
    }

}
