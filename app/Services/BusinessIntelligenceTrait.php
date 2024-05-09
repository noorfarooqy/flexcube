<?php

namespace Noorfarooqy\Flexcube\Services;

use Artisaninweb\SoapWrapper\SoapWrapper;
use Illuminate\Support\Facades\Http;
use Noorfarooqy\NoorAuth\Traits\ResponseHandler;
use Illuminate\Support\Facades\Log;
use Noorfarooqy\NoorAuth\Traits\Helper;

trait BusinessIntelligenceTrait
{
    use ResponseHandler;
    use Helper;

    public function BankStatementReport($account, $from_date, $to_date, $format = 'pdf')
    {
        $absolute_path = config('flexcube.bi_reports.services.public_report_path');
        $user = config('flexcube.bi_reports.user_id');
        $password = config('flexcube.bi_reports.password');
        $payload = "
        <soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/'
            xmlns:pub='http://xmlns.oracle.com/oxp/service/PublicReportService'>
            <soapenv:Header />
            <soapenv:Body>
                <pub:runReport>
                    <pub:reportRequest>
                        <pub:attributeFormat>$format</pub:attributeFormat>
                        <pub:byPassCache>true</pub:byPassCache>
                        <pub:flattenXML>false</pub:flattenXML>
                        <pub:parameterNameValues>
                            <!--Zero or more repetitions:-->
                            <pub:item>
                                <pub:name>Account</pub:name>
                                <pub:values>
                                    <pub:item>$account</pub:item>
                                </pub:values>
                            </pub:item>
                            <pub:item>
                                <pub:name>From_Date</pub:name>
                                <pub:values>
                                    <pub:item>$from_date</pub:item>
                                </pub:values>
                            </pub:item>
                            <pub:item>
                                <pub:name>To_Date</pub:name>
                                <pub:values>
                                    <pub:item>$to_date</pub:item>
                                </pub:values>
                            </pub:item>
                        </pub:parameterNameValues>
                        <pub:reportAbsolutePath>$absolute_path</pub:reportAbsolutePath>
                        <pub:sizeOfDataChunkDownload>-1</pub:sizeOfDataChunkDownload>
                    </pub:reportRequest>
                    <pub:userID>$user</pub:userID>
                    <pub:password>$password</pub:password>
                </pub:runReport>
            </soapenv:Body>
        </soapenv:Envelope>";

        $response = Http::withHeaders([
            'Content-Type' => 'application/xml',
            'SOAPAction' => 'http://schemas.xmlsoap.org/soap/envelope/'
        ])->withBody($payload)->post(config('flexcube.bi_reports.endpoit') . config('flexcube.bi_reports.services.public_report'));
        $start = strpos($response->body(), '<soapenv:Envelope');
        $end = strpos($response->body(), '</soapenv:Envelope>');
        $xml_body = substr($response->body(), $start, $end);
        $xml_body = str_replace('<runReportResponse xmlns="http://xmlns.oracle.com/oxp/service/PublicReportService">', '<runReportResponse>', $xml_body);
        $soap = simplexml_load_string($xml_body);
        $response_children = $soap->children('http://schemas.xmlsoap.org/soap/envelope/')->Body;
        // $this->debugLog($response_children);
        if ($response_children->children('soapenv', true)?->Fault->children()?->faultcode != null) {
            $this->setError(env('APP_DEBUG') ? $response_children->children('soapenv', true)?->Fault->children()->faultstring : 'Request to the CBS Failed. Please contact admin for assistance');
            $this->debugLog('error is here --' . $response_children->children('soapenv', true)?->Fault->children()->faultstring);
            return false;
        }
        $response = $response_children->children()->runReportResponse;
        return $response;
    }

    public function GetCustomersList($from_date, $to_date)
    {

        $absolute_path = config('flexcube.bi_reports.services.customers_report_path');
        $user = config('flexcube.bi_reports.user_id');
        $password = config('flexcube.bi_reports.password');
        $payload = "
        <soapenv:Envelope xmlns:soapenv='http://schemas.xmlsoap.org/soap/envelope/'
            xmlns:pub='http://xmlns.oracle.com/oxp/service/PublicReportService'>
            <soapenv:Header />
            <soapenv:Body>
                <pub:runReport>
                    <pub:reportRequest>
                        <pub:attributeFormat>csv</pub:attributeFormat>
                        <pub:parameterNameValues>
                            <pub:item>
                                <pub:name>p_from_date</pub:name>
                                <pub:values>
                                    <pub:item>$from_date</pub:item>
                                </pub:values>
                            </pub:item>
                            <pub:item>
                                <pub:name>p_to_date</pub:name>
                                <pub:values>
                                    <pub:item>$to_date</pub:item>
                                </pub:values>
                            </pub:item>
                        </pub:parameterNameValues>
                        <pub:reportAbsolutePath>/Internal Reports/Reports/Static Data.xdo</pub:reportAbsolutePath>
                        <pub:sizeOfDataChunkDownload>-1</pub:sizeOfDataChunkDownload>
                    </pub:reportRequest>
                    <pub:userID>$user</pub:userID>
                    <pub:password>$password</pub:password>
                </pub:runReport>
            </soapenv:Body>
        </soapenv:Envelope>
        ";

        $response = Http::withHeaders([
            'Content-Type' => 'application/xml',
            'SOAPAction' => 'http://schemas.xmlsoap.org/soap/envelope/'
        ])->withBody($payload)->post(config('flexcube.bi_reports.endpoit') . config('flexcube.bi_reports.services.public_report'));
        $start = strpos($response->body(), '<soapenv:Envelope');
        $end = strpos($response->body(), '</soapenv:Envelope>');
        $xml_body = substr($response->body(), $start, $end);
        $xml_body = str_replace('<runReportResponse xmlns="http://xmlns.oracle.com/oxp/service/PublicReportService">', '<runReportResponse>', $xml_body);
        $soap = simplexml_load_string($xml_body);
        $response_children = $soap->children('http://schemas.xmlsoap.org/soap/envelope/')->Body;
        // $this->debugLog($response_children);
        if ($response_children->children('soapenv', true)?->Fault->children()?->faultcode != null) {
            $this->setError(env('APP_DEBUG') ? $response_children->children('soapenv', true)?->Fault->children()->faultstring : 'Request to the CBS Failed. Please contact admin for assistance');
            $this->debugLog('error in customers is here --' . $response_children->children('soapenv', true)?->Fault->children()->faultstring);
            return false;
        }
        $response = $response_children->children()->runReportResponse;
        return $response;

    }
    public function RunReport($request_body, $service = null)
    {
        $service = 'PublicReportService';//$service ?? config('flexcube.bi_reports.services.public_report');
        $endpoint = config('flexcube.bi_reports.endpoit') . '/' . $service . '?WSDL';
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
        $operation = 'PublicReportService.runReport';
        $this->debugLog(json_encode($request_body));
        $this->debugLog($operation);
        $this->debugLog($service);
        $response = $soapWrapper->call($operation, $request_body);
        $this->debugLog('Response --- ' . json_encode($response));
        // $this->debugLog(json_encode($soapWrapper->client->)
        $failed = $response?->Fault ?? false;
        if (!$failed) {
            Log::channel(config('flexcube.log_channel'))->error(json_encode($response));
            $default_message = 'Request to the CBS Failed. Please contact admin for assistance';
            $this->setError(json_encode($response?->Fault?->faultstring ?? '---' . $default_message) ?? $default_message);
            return false;
        }
        return $response;
    }

}
