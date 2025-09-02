<?php

namespace App\Services;

use App\Helpers\ConstantHelper;
use App\Models\ErpAddress;
use Carbon\Carbon;
use App\Models\ErpEinvoiceLog;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class MasterIndiaService
{
    private $client; // Guzzle HTTP client for making requests
    private $baseURL; // Base URL for the API
    private $eInvoice; // API request logs
    private $requestUid; // Request UID
    // private $authDetails; // Auth Credentials

    // Constructor to initialize the client, base URL, and authentication credentials
    public function __construct($requestUid)
    {
        $this->requestUid = $requestUid;
        $this->eInvoice = false;
        $this->client = new Client(); // Initialize the HTTP client
        $this->baseURL = config('app.masterindia.base_url'); // Set the base URL
        // $this->authDetails = $authDetails; // Set the base URL
    }

    private function returnResponse($message)
    {
        return [
            "Status" => 0,
            "ErrorMessage" => $message,
             "ErrorDetails" => [
                 [
                     "ErrorCode" => "500",
                     "ErrorMessage" => $message
                 ]
             ],
             "Data" => null,
             "InfoDtls" => null
         ];

    }

    private function createApiLog($endpoint, $method, $payload, $source, $response = null, $isError = false)
    {
        if(!$this->requestUid || empty($this->requestUid)){
            $this->returnResponse("Error: Request id is required");
        }

        $this->eInvoice = $this->eInvoice ?: new ErpEinvoiceLog();
        $this->eInvoice->request_uid = $this->requestUid;
        $this->eInvoice->api_name = $endpoint;
        $this->eInvoice->source = $source;
        $this->eInvoice->method = $method;
        $this->eInvoice->is_error = $isError;
        $this->eInvoice->request_payload = json_encode($payload);
        $this->eInvoice->response_payload = $response ? json_encode($response) : null;
        $this->eInvoice->save();
        return $this->eInvoice;
    }

    public function getAuthToken(){
        try{
            $userData = array(
                "username"=> config('app.masterindia.user_name'),
                "password"=> config('app.masterindia.password'),
                "client_id"=> config('app.masterindia.client_id'),
                "client_secret"=> config('app.masterindia.client_secret'),
                "grant_type"=> config('app.masterindia.grant_type')
                );
            $endpoint = 'oauth/access_token';
            $requestHeader = array(
                "Content-Type: application/json"
            );

            // Send the HTTP request and return the response as an associative array
            $response = $this->client->request('POST', $this->baseURL . $endpoint, [
                'headers' => $requestHeader,
                'json' => $userData,
            ]);

            $result =  json_decode($response->getBody(), true);
            $this->createApiLog($endpoint, 'POST', $userData, ConstantHelper::MASTERINDIA, $result); //Updating API response in eInvoice Log

            if(!isset($result['access_token']))
            {
                $errorMsg = "ERROR: Error in Master India Auth API: {$e->getMessage()}";
                return $this->returnResponse($errorMsg);
            }

            return $result['access_token'];

        } catch (\Exception $e) {
            $errorMsg = "ERROR: Master India Authentication failed: {$e->getMessage()}";
            return $this->returnResponse($errorMsg);
        }
    }

    public function generateInvoice(array $invoiceData)
    {
        try {
            $endpoint = "generateEinvoice";
            $requestHeader = array(
                    "Accept: application/json",
                    "Content-Type: application/json"
            );
            $einvoiceBaseUrl = config('app.masterindia.e_invoice_base_url');
            $response = $this->client->request('POST', $einvoiceBaseUrl . $endpoint, [
                'headers' => $requestHeader,
                'json' => $invoiceData,
            ]);
            $result =  json_decode($response->getBody(), true);
            $this->createApiLog($endpoint, 'POST', $invoiceData, ConstantHelper::MASTERINDIA, $result);
            return $result;
        } catch (\Exception $e) {
            $errorMsg = "ERROR: Invoice generation failed: " . $e->getMessage();
            return $this->returnResponse($errorMsg);

        }
    }

    public function generateEwaybillByIRN(array $ewaybillData)
    {
        try {
            $endpoint = 'ewayBillsGenerate';

            $requestHeader = array(
                "Content-Type:application/json",
            );
            $ewaybillUrl = config('app.masterindia.e_invoice_base_url');
            $response = $this->client->request('POST', $ewaybillUrl . $endpoint, [
                'headers' => $requestHeader,
                'json' => $ewaybillData,
            ]);

            $output =  json_decode($response->getBody(), true);
            $this->createApiLog($endpoint, 'POST', $ewaybillData, ConstantHelper::MASTERINDIA, $output);
            return $output;
        } catch (\Exception $e) {
            $errorMsg = "ERROR: Waybill generation failed: {$e->getMessage()}";
            return $this->returnResponse($errorMsg);
        }
    }

    public function getDistance($fromPincode, $toPincode, $authToken)
    {
        $auth = $authToken;
		$requestHeader = array(
			"Content-Type:application/json",
		);

		$endpoint = "distance?access_token=" . $auth . "&fromPincode=" . $fromPincode . "&toPincode=" . $toPincode;
        $einvoiceBaseUrl = config('app.masterindia.e_invoice_base_url');
		$response = $this->client->request('GET', $einvoiceBaseUrl . $endpoint, [
                'headers' => $requestHeader,
                'json' => [],
            ]);

        $result =  json_decode($response->getBody(), true);
        $this->createApiLog($endpoint, 'GET', $requestHeader, ConstantHelper::MASTERINDIA, $result);
        if(isset($result['results']))
        {
            $distance = [
                "status" => "success",
                "distance" => $result['results']['distance']
            ];
        }
        else
        {
            $distance = [
                "status" => "error",
                "distance" => $result['error_description']
            ];
        }
        return $distance;
    }

    public function cancelInvoice(array $cancelData)
    {
        try {
            $endpoint = "cancelEinvoice";
            $requestHeader = [
                "Accept" => "application/json",
                "Content-Type" => "application/json"
            ];

            $einvoiceBaseUrl = config('app.masterindia.e_invoice_base_url');
            $response = $this->client->request('POST', $einvoiceBaseUrl . $endpoint, [
                'headers' => $requestHeader,
                'json' => $cancelData,
            ]);
            $result =  json_decode($response->getBody(), true);
            $this->createApiLog($endpoint, 'POST', $cancelData, ConstantHelper::MASTERINDIA, $result);
            return $result;

        } catch (\Exception $e) {
            $errorMsg = "ERROR: Invoice cancellation failed: " . $e->getMessage();
            throw new \Exception($errorMsg);
        }
    }

}
