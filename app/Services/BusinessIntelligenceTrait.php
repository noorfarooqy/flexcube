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
                    'byPassCache' => true,
                    'flattenXML' => true,
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
                                'multiValuesAllowed' => false,
                                'refreshParamOnChange' => true,
                                'selectAll' => true,
                                'useNullForAll' => false,
                                'templateParam' => false,
                                'name' => 'To_Date',
                                'value' => [
                                    'item' => $to_date
                                ],
                            ],
                        ],
                    ],
                    'reportAbsolutePath' => config('flexcube.bi_reports.services.public_report_path'),
                    // 'sizeOfDataChunkDownload' => 'cid:974432364637',
                    'sizeOfDataChunkDownload' => -1,

                ],
                'userID' => config('flexcube.bi_reports.user_id'),
                'password' => config('flexcube.bi_reports.password'),
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
        Log::info(json_encode($request_body));
        Log::info($operation);
        Log::info($service);
        $response = $soapWrapper->call($operation, $request_body);
        Log::info(json_encode($response));
        // Log::info(json_encode($soapWrapper->client->)
        $failed = $response?->Fault ?? false;
        if (!$failed) {
            Log::channel(config('flexcube.log_channel'))->error(json_encode($response));
            $default_message = 'Request to the CBS Failed. Please contact admin for assistance';
            $this->setError(json_encode($response?->Fault?->faultstring ?? '--- ' . $default_message) ?? $default_message);
            return false;
        }
        return $response;
    }

}
