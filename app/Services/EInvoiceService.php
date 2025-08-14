<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\ErpEinvoiceLog;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use GuzzleHttp\Exception\RequestException;

class EInvoiceService
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
        $this->baseURL = "https://einv1api.gstsandbox.nic.in/"; // Set the base URL
        $this->authDetails = $authDetails; // Set the base URL
    }

    // General request method for sending HTTP requests (GET, POST, etc.)
    private function request(string $method, string $endpoint, array $payload = [])
    {
        $this->ensureTokenIsValid(); // Ensure the token is valid before making the request
        // $this->createApiLog($endpoint, $method, $payload); //Creating eInvoice API Log
        try {
            // Set the request headers, including the Authorization token
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Client_Id' => $this->authDetails['client_id'],
                'Client_Secret' => $this->authDetails['client_secret'],
                'GSTIN' => $this->authDetails['gstin'],
                "user_name" => $this->authDetails['user_name'],
                'AuthToken' => $this->getCachedToken(), // Use cached token for authorization
            ];

            // Send the HTTP request and return the response as an associative array
            $response = $this->client->request($method, $this->baseURL . $endpoint, [
                'headers' => $headers,
                'json' => $payload,
            ]);
            $result =  json_decode($response->getBody(), true);
            // dd($result, $response);
            $this->createApiLog($endpoint, $method, $payload, $result); //Updating API response in eInvoice Log
            return $result; // Decode and return the response body
        } catch (RequestException $e) {
            $errorResponse = "ERROR: ". $e->getMessage();
            $this->createApiLog($endpoint, $method, $payload, $errorResponse, true); //Updating error message in eInvoice Log
            // Handle request errors
            $this->returnResponse($errorResponse);
        }
    }

    // Method to fetch and store the authentication token
    private function getToken()
    {
        try {

            // generate appKey is set
            $appKey = base64_encode($this->createAESKey());

            $authCredentials = [
                "UserName" => $this->authDetails['user_name'],
                "Password" => $this->authDetails['password'],
                "AppKey" => $appKey,
                // "ForceRefreshAccessToken" => True,
                "ForceRefreshAccessToken" => False,

            ];

            $headers = [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'Client_Id' => $this->authDetails['client_id'],
                    'Client_Secret' => $this->authDetails['client_secret'],
                    'GSTIN' => $this->authDetails['gstin'],
            ];

            $payload = $this->encryptAsymmetricKey($authCredentials); // Encrypt the auth credentials before sending

            $response = $this->client->post($this->baseURL . 'eivital/v1.04/auth', [
                'json' => $payload,
                'headers' => $headers,
            ]);


            // $this->createAuthLog('eivital/v1.04/auth', 'POST', $payload, $response); //Updating API response in eInvoice Log
            $responseBody = json_decode($response->getBody(), true); // Decode the response

            if ($responseBody['Status'] === 1) {
                $tokenData = $responseBody['Data'];
                $tokenData['AppKey'] = $appKey;
                Cache::put('irn_token_data', $tokenData, now()->parse($tokenData['TokenExpiry']));
                $tokenData = Cache::get('irn_token_data'); // Get the cached token
                return $tokenData; // Return the token
            }

            $errorMsg = "ERROR: Unable to fetch token: " . $responseBody['ErrorDetails'];
            return $this->returnResponse($errorMsg);
        } catch (\Exception $e) {
            $errorMsg = "ERROR: Token fetching failed: " . $e->getMessage();
            $this->createAuthLog('eivital/v1.04/auth', 'POST', null, $errorMsg);
            return $this->returnResponse($errorMsg);

        }
    }

    // Method to ensure the cached token is valid before making a request
    private function ensureTokenIsValid()
    {
        // $tokenData = null; // Get the cached token
        $tokenData = Cache::get('irn_token_data'); // Get the cached token
        // dd($tokenData);
        $expiryTime = Carbon::parse(@$tokenData['TokenExpiry'], 'Asia/Kolkata');
        
        // if (!$tokenData || now()->gte(now()->parse($tokenData['TokenExpiry']))) {
        if (!$tokenData || Carbon::now('Asia/Kolkata')->greaterThan($expiryTime)) {

            $data = $this->getToken(); // If the token is invalid or expired, fetch a new one
            if(isset($data['Status']) && ($data['Status'] ==0)) {
                return $data;
            }
        }
    }


    // Method to retrieve the cached token
    private function getCachedToken()
    {
        $tokenData = Cache::get('irn_token_data'); //// Return the cached token
        return $tokenData['AuthToken'] ?? null;
    }

    // Method to retrieve the sek from cached
    private function getSek()
    {
        $tokenData = Cache::get('irn_token_data'); //// Return the cached sek
        return $tokenData['Sek'] ?? null;
    }

    // Method to retrieve the App Key from cached
    private function getAppKey()
    {
        $tokenData = Cache::get('irn_token_data'); //// Return the cached sek
        return $tokenData['AppKey'] ?? null;
    }

    // Example method to generate an invoice
    public function generateInvoice(array $invoiceData)
    {
        try {
            $ensureTokenIsValid = $this->ensureTokenIsValid();
            // dd($ensureTokenIsValid);
            if($ensureTokenIsValid && $ensureTokenIsValid['Status'] == 0){
                $errorMsg = $ensureTokenIsValid['ErrorMessage'];
                return $this->returnResponse($errorMsg);
            }

            $decryptedSekBytes = $this->decrptBySymmetricKeySEK();
            $payload = $this->encryptBySymmetricKey($invoiceData, $decryptedSekBytes);
            $response = $this->request('POST', 'eicore/v1.03/Invoice', $payload); // Send the request to generate the invoice
            if($response['Status'] == '1'){
                $decryptedResponse = json_decode($this->decryptResponseUsingSek($response['Data'],$decryptedSekBytes),true);
                // dd($decryptedResponse);
                return $decryptedResponse;
                // return "Success: ".$decryptedResponse['Irn'];

            }
            // dd($response['Status'], $invoiceData);
            if($response['Status'] == '0'){
                $this->returnResponse("Error-".$response['ErrorDetails'][0]['ErrorCode'].": ".$response['ErrorDetails'][0]['ErrorMessage']);
            }
            return $response;
        } catch (\Exception $e) {
            dd($e);
            $errorMsg = "ERROR: Invoice generation failed: " . $e->getMessage();
            $this->createApiLog('eicore/v1.03/Invoice', 'POST', null, $errorMsg);
            return $this->returnResponse($errorMsg);

        }
    }

    // Example method to cancel an invoice
    public function cancelInvoice(array $cancelData)
    {
        try {
            $this->ensureTokenIsValid();
            $decryptedSekBytes = $this->decrptBySymmetricKeySEK();
            $payload = $this->encryptBySymmetricKey($cancelData,$decryptedSekBytes);
            $response = $this->request('POST', 'eicore/v1.03/Invoice/Cancel', $payload); // Send the request to cancel the invoice
            if($response['Status'] == '1'){
                $decryptedResponse = $this->decryptResponseUsingSek($response['Data'],$decryptedSekBytes);
                return $decryptedResponse;
            }
            return $response;
        } catch (\Exception $e) {
            $errorMsg = "ERROR: Invoice cancellation failed: {$e->getMessage()}";
            $this->createApiLog('eicore/v1.03/Invoice/Cancel', 'POST', null, $errorMsg);
            return $this->returnResponse($errorMsg);

        }
    }

    // Example method to fetch an invoice by its IRN (Invoice Reference Number)
    public function getInvoiceByIRN(string $irn)
    {
        try {
            $this->ensureTokenIsValid();
            $response = $this->request('GET', "eicore/v1.03/Invoice/irn/{$irn}"); // Send the request to get invoice by IRN
            if($response['Status'] == '1'){
                $decryptedSekBytes = $this->decrptBySymmetricKeySEK();
                $decryptedResponse = $this->decryptResponseUsingSek($response['Data'],$decryptedSekBytes);
                return $decryptedResponse;
            }
            return $response;
        } catch (\Exception $e) {
            $errorMsg = "ERROR: Failed to fetch irn details: {$e->getMessage()}";
            $this->createApiLog("eicore/v1.03/Invoice/irn/{$irn}", 'GET', null, $errorMsg);
            return $this->returnResponse($errorMsg);
        }
    }

    // Example method to fetch GSTIN details
    public function getGSTINDetails(string $gstin)
    {
        try {
            $this->ensureTokenIsValid();

            $response = $this->request('GET', "eivital/v1.04/Master/gstin/{$gstin}"); // Send the request to get GSTIN details
            if($response['Status'] == '1'){
                $decryptedSekBytes = $this->decrptBySymmetricKeySEK();
                $decryptedResponse = $this->decryptResponseUsingSek($response['Data'],$decryptedSekBytes);
                return $decryptedResponse;
            }
            return $response;
        } catch (\Exception $e) {
            $errorMsg = "ERROR: Failed to fetch GSTIN details: {$e->getMessage()}";
            $this->createApiLog("eivital/v1.04/Master/gstin/{$gstin}", 'GET', null, $errorMsg);
            return $this->returnResponse($errorMsg);

        }
    }

    // Example method to sync GSTIN details
    public function syncGSTIN(string $gstin)
    {
        try {
            // $payload = $this->encryptAsymmetricKey($syncData); // Encrypt the sync data
            $this->ensureTokenIsValid();
            return $this->request('GET', "eivital/v1.04/Master/syncgstin/{$gstin}"); // Send the request to sync GSTIN details
        } catch (\Exception $e) {
            $errorMsg = "ERROR: Failed to sync details: {$e->getMessage()}";
            $this->createApiLog("eivital/v1.04/Master/syncgstin/{$gstin}", 'GET', null, $errorMsg);
            return $this->returnResponse($errorMsg);

        }
    }

    // Example method to generate an e-waybill by IRN
    public function generateEwaybillByIRN(array $ewaybillData)
    {
        try {
            // dd($ewaybillData);
            $this->ensureTokenIsValid();
            $decryptedSekBytes = $this->decrptBySymmetricKeySEK();
            $payload = $this->encryptBySymmetricKey($ewaybillData,$decryptedSekBytes); // Encrypt the e-waybill data
            $response = $this->request('POST', 'eiewb/v1.03/ewaybill', $payload); // Send the request to generate the e-waybill
            if($response['Status'] == '1'){
                $decryptedSekBytes = $this->decrptBySymmetricKeySEK();
                $decryptedResponse = $this->decryptResponseUsingSek($response['Data'],$decryptedSekBytes);
                return json_decode($decryptedResponse,true);
            }
            return $response;
        } catch (\Exception $e) {
            $errorMsg = "ERROR: Waybill generation failed: {$e->getMessage()}";
            // $this->createApiLog("eiewb/v1.03/ewaybill", 'POST', null, $errorMsg);
            return $this->returnResponse($errorMsg);
        }
    }

    // Example method to get an e-waybill by IRN
    public function getEwaybillByIRN(string $irn)
    {
        try {
            $this->ensureTokenIsValid();
            $response = $this->request('GET', "eiewb/v1.03/ewaybill/irn/{$irn}"); // Send the request to get e-waybill by IRN
            if($response['Status'] == '1'){
                $decryptedSekBytes = $this->decrptBySymmetricKeySEK();
                $decryptedResponse = $this->decryptResponseUsingSek($response['Data'],$decryptedSekBytes);
                return $decryptedResponse;
            }
            return $response;
        } catch (\Exception $e) {
            $errorMsg = "ERROR: Failed to get details: {$e->getMessage()}";
            // $this->createApiLog("eiewb/v1.03/ewaybill/irn/{$irn}", 'GET', null, $errorMsg);
            return $this->returnResponse($errorMsg);
        }
    }

    // Example method to cancel an e-waybill by IRN
    public function cancelEwaybillByIRN(array $cancelData)
    {
        try {
            $this->ensureTokenIsValid();
            $decryptedSekBytes = $this->decrptBySymmetricKeySEK();
            $payload = $this->encryptBySymmetricKey($cancelData,$decryptedSekBytes);
            $payload = [
                "action" => "CANEWB",
                "data" => $payload['Data'],
            ];
            $response = $this->request('POST', 'ewaybillapi/v1.03/ewayapi', $payload); // Send the request to cancel the invoice
            if($response['Status'] == '1'){
                $decryptedSekBytes = $this->decrptBySymmetricKeySEK();
                $decryptedResponse = $this->decryptResponseUsingSek($response['Data'],$decryptedSekBytes);
                return $decryptedResponse;
            }
            return $response;
        } catch (\Exception $e) {
            $errorMsg = "ERROR: Ewaybill cancellation failed: {$e->getMessage()}";
            // $this->createApiLog("ewaybillapi/v1.03/ewayapi", 'POST', null, $errorMsg);
            return $this->returnResponse($errorMsg);
        }
    }

    // Example method to fetch IRN by document details
    public function getIRNByDocDetails(string $docType, string $docNum, string $docDate)
    {
        try {
            $this->ensureTokenIsValid();
            $endpoint = "eicore/v1.03/Invoice/irnbydocdetails?doctype={$docType}&docnum={$docNum}&docdate={$docDate}";
            $response = $this->request('GET', $endpoint); // Send the request to fetch IRN by document details
            if($response['Status'] == '1'){
                $decryptedSekBytes = $this->decrptBySymmetricKeySEK();
                $decryptedResponse = $this->decryptResponseUsingSek($response['Data'],$decryptedSekBytes);
                return $decryptedResponse;
            }
            return $response;
        } catch (\Exception $e) {
            $errorMsg = "ERROR:Failed to fetch IRN details: {$e->getMessage()}";
            // $this->createApiLog("eicore/v1.03/Invoice/irnbydocdetails?doctype={$docType}&docnum={$docNum}&docdate={$docDate}", 'GET', null, $errorMsg);
            return $this->returnResponse($errorMsg);
        }
    }

    // Example method to get details of rejected IRNs for a specific date
    public function getRejectedIRNsDetails(string $date)
    {
        try {
            $this->ensureTokenIsValid();
            return $this->request('GET', "eicore/v1.03/Invoice/rejectedirns?date={$date}"); // Send the request to fetch rejected IRN details
        } catch (\Exception $e) {
            $errorMsg = "ERROR: Failed to fetch rejected details: {$e->getMessage()}";
            // $this->createApiLog("eicore/v1.03/Invoice/rejectedirns?date={$date}", 'GET', null, $errorMsg);
            return $this->returnResponse($errorMsg);
        }
    }

    // Example method for health check (ping) of the IRN service
    public function healthCheck()
    {
        return $this->request('GET', 'eivital/v1.04/heartbeat/ping'); // Send the request for a health check
    }

    // Encrypts the payload before sending it in the request (for security purposes)
    private function encryptAsymmetricKey($jsonPayload)
    {

        try {
            // Convert the array to JSON string before encoding it
            $jsonString = json_encode($jsonPayload);

            // Check if the json_encode was successful
            if (!$jsonString || $jsonString === false) {
                $this->returnResponse("ERROR: Failed to encode JSON");
            }

            // Encode the JSON string to base64
            $encryptedPayload = base64_encode($jsonString);

            // Get the public key
            $publicKey = $this->getPublicKey();

            // Check if the public key was loaded correctly
            if (!$publicKey) {
                $this->returnResponse("ERROR: Failed to load the public key: " . openssl_error_string());
            }

            // Perform public key encryption
            $encryptedText = null;
            $encryptionSuccess = openssl_public_encrypt($encryptedPayload, $encryptedText, $publicKey, OPENSSL_PKCS1_PADDING);

            // Check if encryption was successful
            if (!$encryptionSuccess) {
                $error = openssl_error_string();
                $this->returnResponse("ERROR: Public key encryption failed: " . $error);
            }


            // Return the encrypted payload
            return [
                'Data' => base64_encode($encryptedText),
            ];
        } catch (\Exception $e) {
            $this->returnResponse('ERROR: Exception ' . $e->getMessage());
        }

    }

    function encryptBySymmetricKey($json, $decryptedSek) {
        // Decode the Base64 encrypted SEK (Symmetric Encryption Key)
        $sekByte = base64_decode($decryptedSek);

        // Encrypt the data using AES in ECB mode with PKCS5 padding
        try {
            // Convert the array to JSON string before encoding it
            $jsonString = json_encode($json);
            // dd($jsonString);

            // Check if the json_encode was successful
            if (!$jsonString || $jsonString === false) {
                $this->returnResponse("ERROR: Error encoding JSON");
            }

            // Encrypt using AES-128-ECB (AES with 128-bit key and ECB mode)
            $encryptedJsonBytes = openssl_encrypt($jsonString, 'AES-256-ECB', $sekByte, OPENSSL_RAW_DATA);

            // If encryption failed, return an error message
            if (!$encryptedJsonBytes || $encryptedJsonBytes === false) {
                $this->returnResponse('ERROR: Encryption failed');
            }

            // Encode the encrypted bytes in Base64 and return the result
            $encryptedJson = base64_encode($encryptedJsonBytes);

            // Return the encrypted payload
            return [
                'Data' => $encryptedJson,
            ];
        } catch (\Exception $e) {
            $this->returnResponse('ERROR: Exception ' . $e->getMessage());
        }
    }

    function decrptBySymmetricKeySEK(){
        try {
            $appkey = $this->getAppKey(); // Same App Key that was sent in auth request only needs to be used to decrypt the SEK that has come as the response to the API call using Symmetric AES algorithm.
            $encryptedSek = $this->getSek(); // The secret key from token data
            $aesKey = base64_decode($appkey);
            $encryptedSekBytes = base64_decode($encryptedSek);
            $decryptedSekBytes = base64_encode(openssl_decrypt($encryptedSekBytes, 'AES-256-ECB', $aesKey, OPENSSL_RAW_DATA));

            // If encryption failed, return an error message
            if (!$decryptedSekBytes || $decryptedSekBytes === false) {
                $this->returnResponse('ERROR: Decryption failed');
            }

            return $decryptedSekBytes;
        } catch (\Exception $e) {
            $this->returnResponse('ERROR: Exception ' . $e->getMessage());
        }
    }


    function decryptResponseUsingSek($data,$sek){
        try {
            $sekByte = base64_decode($sek);
            $encryptedJsonBytes = base64_decode($data);
            $decryptedJsonString = openssl_decrypt($encryptedJsonBytes, 'AES-256-ECB', $sekByte, OPENSSL_RAW_DATA);

            if (!$decryptedJsonString || $decryptedJsonString === false) {
                $this->returnResponse('ERROR: Decryption failed');
            }

            return $decryptedJsonString;
        } catch (\Exception $e) {
            $this->returnResponse('ERROR: Exception ' . $e->getMessage());
        }
    }

    private function getPublicKey()
    {
        $keyPath = public_path('irn_public_key/einv_sandbox.cer');

        if (!file_exists($keyPath)) {
            throw new \Exception("Public key file not found.");
        }

        $publicKey = file_get_contents($keyPath);
        return openssl_pkey_get_public($publicKey);
    }

    private function createApiLog($endpoint, $method, $payload, $response = null, $isError = false)
    {
        if(!$this->requestUid || empty($this->requestUid)){
            $this->returnResponse("Error: Request id is required");
        }

        $this->eInvoice = $this->eInvoice ?: new ErpEinvoiceLog();
        $this->eInvoice->request_uid = $this->requestUid;
        $this->eInvoice->api_name = $endpoint;
        $this->eInvoice->method = $method;
        $this->eInvoice->is_error = $isError;
        $this->eInvoice->request_payload = json_encode($payload);
        $this->eInvoice->response_payload = $response ? json_encode($response) : null;
        // dd($this->eInvoice);
        $this->eInvoice->save();
        return $this->eInvoice;
    }

    private function createAuthLog($endpoint, $method, $payload, $response = null, $isError = false)
    {
        $eInvoice = new ErpEinvoiceLog();
        $eInvoice->request_uid = $this->requestUid;
        $eInvoice->api_name = $endpoint;
        $eInvoice->method = $method;
        $eInvoice->is_error = $isError;
        $eInvoice->request_payload = $payload;
        $eInvoice->response_payload = $response ? $response : null;
        $eInvoice->save();
        return $this->eInvoice;
    }

    private function createAESKey()
    {
        return random_bytes(32); // 128-bit AES key
    }

    private function response($message)
    {
        $response= [
           "Status" => 0,
            "ErrorDetails" => [
                [
                    "ErrorCode" => "500",
                    "ErrorMessage" => $message
                ]
            ],
            "Data" => null,
            "InfoDtls" => null
        ];

        header('Content-Type: application/json');
        echo json_encode($response);
        exit();
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

    private function successResponse($response,$data)
    {
        return [
            "Status" => $response['Status'],
            "ErrorDetails" => $response['ErrorDetails'],
            "Data" => json_decode($data,true),
            "InfoDtls" => $response['InfoDtls']
        ];
    }

}
