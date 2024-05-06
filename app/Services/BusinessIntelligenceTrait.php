<?php

namespace Noorfarooqy\Flexcube\Services;

use Artisaninweb\SoapWrapper\SoapWrapper;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Facades\Http;
use Noorfarooqy\NoorAuth\Traits\ResponseHandler;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;

trait BusinessIntelligenceTrait
{
    use ResponseHandler;

    public function BankStatementReport($account, $from_date, $to_date, $format = 'pdf')
    {
        $absolute_path = config('flexcube.bi_reports.services.public_report_path');
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
                    <pub:userID>weblogic</pub:userID>
                    <pub:password>weblogic123</pub:password>
                </pub:runReport>
            </soapenv:Body>
        </soapenv:Envelope>";

        $response = Http::withHeaders([
            'Content-Type' => 'application/xml',
            'SOAPAction' => 'http://schemas.xmlsoap.org/soap/envelope/'
        ])->withBody($payload)->post(config('flexcube.bi_reports.endpoit') . config('flexcube.bi_reports.services.public_report'));
        // Log::info('resonpose--- ' . json_encode($response->body()));
        $start = strpos($response->body(), '<soapenv:Envelope');
        $end = strpos($response->body(), '</soapenv:Envelope>');
        // Log::info("start at $start and end at $end");
        $xml_body = substr($response->body(), $start, $end);

        // $xml_body = str_replace('xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"', ' ', $xml_body);

        $xml_body = str_replace('<runReportResponse xmlns="http://xmlns.oracle.com/oxp/service/PublicReportService">', '<runReportResponse>', $xml_body);

        // Log::info($xml_body);
        $soap = simplexml_load_string($xml_body);
        $response_children = $soap->children('http://schemas.xmlsoap.org/soap/envelope/')->Body;
        // Log::info($response_children);
        if ($response_children->children('soapenv', true)?->Fault->children()?->faultcode != null) {
            $this->setError(env('APP_DEBUG') ? $response_children->children('soapenv', true)?->Fault->children()->faultstring : 'Request to the CBS Failed. Please contact admin for assistance');
            Log::info('error is here --' . $response_children->children('soapenv', true)?->Fault->children()->faultstring);
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
        Log::info(json_encode($request_body));
        Log::info($operation);
        Log::info($service);
        $response = $soapWrapper->call($operation, $request_body);
        Log::info('Response --- ' . json_encode($response));
        // Log::info(json_encode($soapWrapper->client->)
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
