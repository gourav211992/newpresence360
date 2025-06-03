<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\ErpEinvoiceLog;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Exception\RequestException;

class MasterIndiaService
{
    private $client; // Guzzle HTTP client for making requests
    private $baseURL; // Base URL for the API
    private $eInvoice; // API request logs
    private $requestUid; // Request UID
    private $authDetails; // Auth Credentials

    // Constructor to initialize the client, base URL, and authentication credentials
    public function __construct($authDetails,$requestUid)
    {
        $this->requestUid = $requestUid;
        $this->eInvoice = false;
        $this->client = new Client(); // Initialize the HTTP client
        $this->authDetails = $authDetails; // Set the base URL
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

    public function getAuthToken(){
        try{
            $userData = array(
                "username"=> env('MASTER_INDIA_USERNAME', ''),
                "password"=> env('MASTER_INDIA_PASSWORD', ''),
                "client_id"=>env('MASTER_INDIA_CLIENT_ID', ''),
                "client_secret"=>env('MASTER_INDIA_CLIENT_SECRET', ''),
                "grant_type"=>env('MASTER_INDIA_GRANT_TYPE', '')
                );
            $tokenUrl =  env('MASTER_INDIA_BASE_URL', '');
            $requestHeader = array(
                "Content-Type: application/json"
            );
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL,$tokenUrl);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($userData));
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $requestHeader);
            $token = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            $token = $token ? json_decode($token) : '';
            return $token->access_token;
            if($err)
            {
                $errorMsg = "ERROR: Error in Master India Auth API: {$e->getMessage()}";
                return $this->returnResponse($errorMsg);
            }
        } catch (\Exception $e) {
            $errorMsg = "ERROR: Master India Authentication failed: {$e->getMessage()}";
            return $this->returnResponse($errorMsg);
        }
    }

}
