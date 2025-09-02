<?php
namespace App\Helpers;

use DB;
use Auth;

use App\Models\Hsn;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Book;
use App\Models\Item;
use App\Models\City;
use App\Models\State;
use App\Models\PoItem;
use App\Models\Vendor;
use App\Models\Address;
use App\Models\Country;
use App\Models\ErpStore;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\MrnDetail;
use App\Models\MrnHeader;
use App\Models\CostCenter;
use App\Models\ErpAddress;
use App\Models\PaymentTerm;
use App\Models\AlternateUOM;
use App\Models\Organization;
use App\Models\Configuration;
use App\Models\NumberPattern;
use App\Models\AttributeGroup;

use App\Models\ErpAttribute;
use App\Models\ItemAttribute;

use App\Models\PRHeader;
use App\Models\PRDetail;
use App\Models\PRItemLocation;
use App\Models\PRItemAttribute;

use App\Helpers\ItemHelper;
use App\Helpers\ConstantHelper;

use Illuminate\Http\Request;
use App\Services\EInvoiceService;
use App\Services\MasterIndiaService;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;

use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;

class MasterIndiaHelperBack
{
    public function __construct()
	{
		// $logFactory = new LoggerFactory();
		// $this->log = $logFactory->setPath('logs/services')->createLogger('gov-e-invoice');
	}

    const B2B_INVOICE_TYPE = "B2B";
    const B2C_INVOICE_TYPE = "B2CL";
    const EXPORT_INVOICE_TYPE = "Export";
    const CREDIT_NOTE_INVOICE_TYPE = "CDNR";
    const TRANPORTER_DOC_NO_MAX_LIMIT = 15;
    const EWAY_BILL_MIN_AMOUNT_LIMIT = 50000;

    public static function getGstInvoiceType($partyId, $partyCountryId, $sellerCountryId, string $partyType = 'customer') : string|null
    {
        //Retrieve party first
        $party = null;
        if ($partyType === 'customer') {
            $party = Customer::find($partyId);
        } else if ($partyType === 'vendor') {
            $party = Vendor::find($partyId);
        } else {
            $party = null;
        }
        if (!isset($party)) {
            return null;
        }
        //Get the GST
        $gstRegistered = $party -> compliances ?-> gst_applicable;
        if ($gstRegistered) {
            if ($partyCountryId === $sellerCountryId) {
                return self::B2B_INVOICE_TYPE;
            } else {
                return self::B2C_INVOICE_TYPE;
            }
        } else {
            if ($partyCountryId !== $sellerCountryId) {
                return self::EXPORT_INVOICE_TYPE;
            } else {
                return self::B2C_INVOICE_TYPE;
            }
        }
    }

    public static function getEInvoicePendingDocumentStatus(Model $documentHeader, string|null $gstInvoiceType)
    {
        if ($gstInvoiceType === self::B2B_INVOICE_TYPE) {
            if (isset($documentHeader -> irnDetail) && $documentHeader -> irnDetail) {
                return ConstantHelper::GENERATED;
            } else {
                return ConstantHelper::PENDING;
            }
        } else {
            return null;
        }
    }

    public static function generateInvoice($documentHeader, $documentDetails)
    {
        $user = Helper::getAuthenticatedUser();

        $authCredentials = self::getAuthCredentials();
        $requestUid = 'GOV-EINVOICE-'.date('dmy').time();
        $masterIndiaService = new MasterIndiaService($authCredentials,$requestUid);
        // $authToken = $masterIndiaService->getAuthToken();
        $authToken = config('app.masterindia.e_invoice_access_token');;
        $postData = self::prepareRequestPayload($documentHeader, $documentDetails, $authToken, $authCredentials);
        $response = $masterIndiaService->generateInvoice($postData);
        return $response;
    }

    private static function prepareRequestPayload($documentHeader, $documentDetails, $authToken, $authCredentials)
    {
        $user = Helper::getAuthenticatedUser();

        $invoiceDtls = self::getInvoiceDetail($documentHeader, $documentDetails);
        $invoiceData = [
            "access_token" => $authToken,
            "user_gstin" => $authCredentials['gstin'],
            "data_source" => "erp",
            "transaction_details" => $invoiceDtls['tranDetails'],
            "document_details" => $invoiceDtls['docDetails'],
            "seller_details" => $invoiceDtls['sellerDetails'],
            "buyer_details" => $invoiceDtls['buyerDetails'],
            "dispatch_details" => $invoiceDtls['dispatchDetails'],
            "ship_details" => $invoiceDtls['shipDetails'],
            "item_list" => $invoiceDtls['itemList'],
            "value_details" => $invoiceDtls['valDtls'],
            "payment_details" => $invoiceDtls['payDtls'],
            "reference_details" => $invoiceDtls['refDtls'],
            "additional_document_details" => $invoiceDtls['addlDocDtls'],
            "export_details" => $invoiceDtls['expDtls'],
            "ewaybill_details" => $invoiceDtls['ewbDtls'],
        ];
        return $invoiceData;
    }

    public function cancelInvoice(Request $request)
    {
        $cancelData = $request->all();

        $authCredentials = self::getAuthCredentials();
        $requestUid = '1';
        $eInvoiceService = new EInvoiceService($authCredentials,$requestUid);
        try {
            $response = $eInvoiceService->cancelInvoice($cancelData);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public static function formatGstinResponse($input_response)
    {
        try {
            $data = json_decode($input_response, true);

            $info = $data['data'];
            $addr = $info['pradr']['addr'] ?? [];
            $stateCode = '';
            if (!empty($addr['stcd'])) {
                $state = $addr['stcd'];
                $stateModel = State::where('name', 'like', '%' . $state . '%')->first();
                $stateCode = $stateModel ? $stateModel->state_code : '';
            }

            $formatted = [
                'Gstin'      => $info['gstin'] ?? '',
                'TradeName'  => $info['tradeNam'] ?? '',
                'LegalName'  => $info['lgnm'] ?? '',
                'AddrBnm'    => $addr['bnm'] ?? '',
                'AddrBno'    => $addr['bno'] ?? '',
                'AddrFlno'   => $addr['flno'] ?? '',
                'AddrSt'     => $addr['st'] ?? '',
                'AddrLoc'    => $addr['loc'] ?? '',
                'StateCode'  => $stateCode,
                'AddrPncd'   => $addr['pncd'] ?? '',
                'TxpType'    => $info['dty'] ?? '',
                'Status' => (isset($info['sts']) && $info['sts'] === 'Active') ? 'ACT' : ($info['sts'] ?? ''),
                'BlkStatus'  => 'U',
                'DtReg' => isset($info['rgdt']) && !empty($info['rgdt'])
                    ? \DateTime::createFromFormat('d/m/Y', str_replace('\\/', '/', $info['rgdt']))->format('Y-m-d')
                    : '',
                'DtDReg'     => null,
            ];
            $final_response = [
                'Status' => 1,
                'errorMsg' => '',
                'successMsg' => '',
                'checkGstIn' => json_encode($formatted),
            ];
            return $final_response;

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public static function validateGstinName(Request $request){
        try{
            $gstin = $request->gstin;
            $authCredentials = self::getAuthCredentials();
            $requestUid = '1';
            $masterIndiaService = new MasterIndiaService($authCredentials,$requestUid);

            $authToken = $masterIndiaService->getAuthToken();
            $baseUrl = config('app.masterindia.base_url');
            $gstinUrl = $baseUrl . 'commonapis/searchgstin?gstin=' . urlencode($gstin);
            $clientId = config('app.masterindia.gstin_client_id');
            $requestHeader = array(
                'client_id:'.$clientId,
                'Authorization:Bearer '.$authToken,
                'Content-Type: application/json'
            );
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL,$gstinUrl);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $requestHeader);
            $result = curl_exec($curl);
            $decodedResult = json_decode($result, true);
            if (isset($decodedResult['error']) && $decodedResult['error'] === false) {
                $final_result = EInvoiceHelper::formatGstinResponse($result);
            } else {
                $final_result = [
                    'Status' => 0,
                    'errorMsg' => $decodedResult['data'] ?? '',
                    'successMsg' => '',
                    'checkGstIn' => ''
                ];
            }
            return $final_result;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);

        }

    }

    public function getIrnDetails(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'irn' => [
                'required'
            ]
        ]);

        if($validator->fails()){
            $response = [
                'Status' => 0,
                'ErrorDetails' => [
                    ["ErrorCode" => 500,
                    'ErrorMessage' => $validator->errors()->first()]
                ],
                'Data' => null,
                'InfoDtls' => null,
            ];
            return response()->json($response);
        }

        $authCredentials = self::getAuthCredentials();
        $requestUid = '1';
        $eInvoiceService = new EInvoiceService($authCredentials,$requestUid);
        try {
            $response = $eInvoiceService->getInvoiceByIRN($request->irn);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getGstDetails(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'gstin' => [
                'required'
            ]
        ]);

        if($validator->fails()){
            $response = [
                'Status' => 0,
                'ErrorDetails' => [
                    ["ErrorCode" => 500,
                    'ErrorMessage' => $validator->errors()->first()]
                ],
                'Data' => null,
                'InfoDtls' => null,
            ];
            return response()->json($response);
        }

        $authCredentials = self::getAuthCredentials();
        $requestUid = '1';
        $eInvoiceService = new EInvoiceService($authCredentials,$requestUid);
        try {
            $response = $eInvoiceService->getGSTINDetails($request->gstin);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function syncGstDetails(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'gstin' => [
                'required'
            ]
        ]);

        if($validator->fails()){
            $response = [
                'Status' => 0,
                'ErrorDetails' => [
                    ["ErrorCode" => 500,
                    'ErrorMessage' => $validator->errors()->first()]
                ],
                'Data' => null,
                'InfoDtls' => null,
            ];
            return response()->json($response);
        }

        $authCredentials = self::getAuthCredentials();
        $requestUid = '1';
        $eInvoiceService = new EInvoiceService($authCredentials,$requestUid);
        try {
            $response = $eInvoiceService->syncGSTIN($request->gstin);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function irnByDocDetails(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'doctype' => [
                'required'
            ],
            'docnum' => [
                'required'
            ],
            'docdate' => [
                'required'
            ],
        ]);

        if($validator->fails()){
            $response = [
                'Status' => 0,
                'ErrorDetails' => [
                    ["ErrorCode" => 500,
                    'ErrorMessage' => $validator->errors()->first()]
                ],
                'Data' => null,
                'InfoDtls' => null,
            ];
            return response()->json($response);
        }

        $authCredentials = self::getAuthCredentials();
        $requestUid = '1';
        $eInvoiceService = new EInvoiceService($authCredentials,$requestUid);
        try {
            $response = $eInvoiceService->getIRNByDocDetails($request->doctype,$request->docnum,$request->docdate);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function rejectedIrn(Request $request)
    {
        $validator = Validator::make($request->all(),[
            'date' => [
                'required'
            ]
        ]);

        if($validator->fails()){
            $response = [
                'Status' => 0,
                'ErrorDetails' => [
                    ["ErrorCode" => 500,
                    'ErrorMessage' => $validator->errors()->first()]
                ],
                'Data' => null,
                'InfoDtls' => null,
            ];
            return response()->json($response);
        }

        $authCredentials = self::getAuthCredentials();
        $requestUid = '1';
        $eInvoiceService = new EInvoiceService($authCredentials,$requestUid);
        try {
            $response = $eInvoiceService->getRejectedIRNsDetails($request->date);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private static function getAuthCredentials()
    {
        $user = Helper::getAuthenticatedUser();
        $authCredentials = array();
        $organization = Organization::find($user->organization_id);
        // Get configuration values in a key-value pair
        $configurations = Configuration::where('type', 'organization')
        ->where('type_id', $user->organization_id)
        ->whereIn('config_key', [
            ConstantHelper::CLIENT_ID,
            ConstantHelper::CLIENT_SECRET,
            ConstantHelper::CLIENT_USERNAME,
            ConstantHelper::CLIENT_PASSWORD
        ])
        ->pluck('config_value', 'config_key');

        $authCredentials = [
            'client_id'    => $configurations['e_invoice_client_id'] ?? config('app.masterindia.client_id'),
            'client_secret' => $configurations['e_invoice_client_secret'] ?? config('app.masterindia.client_secret'),
            'user_name'    => $configurations['e_invoice_client_username'] ?? config('app.masterindia.user_name'),
            'password'     => $configurations['e_invoice_client_password'] ?? config('app.masterindia.password'),
            'gstin'        => $organization->gst_number ?? env('EINVOICE_GSTIN', ''),
        ];

        return $authCredentials;
    }

    private static function getInvoiceDetail($documentHeader, $documentDetails)
    {
        $user = Helper::getAuthenticatedUser();

        $itemList = array();
        $result = array();

        $orderQty = 0;
        $totalAmt = 0;
        $itemDiscount = 0;
        $headerDiscount = 0;
        $totalItemValue = 0;
        $taxBracket = [];
        $totalTaxValue = 0.00;
        $totalCGSTValue = 0.00;
        $totalSGSTValue = 0.00;
        $totalIGSTValue = 0.00;
        $documentNumber = $documentHeader->book_code .'-'. $documentHeader->document_number;

        $organization = Organization::where('id', $user->organization_id)->first();
        // $organizationAddress = Address::with(['city', 'state', 'country'])
        //     ->where('addressable_id', $user->organization_id)
        //     ->where('addressable_type', Organization::class)
        //     ->first();
        $organizationAddress = $documentHeader?->location_address_details;
        $buyerAddress = $documentHeader?->location_address_details;
        $sellerBillingAddress = $documentHeader->latestBillingAddress();
        $sellerStateCode = self::getStateCode($organizationAddress->state_id);
        $buyerStateCode = self::getStateCode($sellerBillingAddress->state_id);

        $tranDetails = (object) [
            "supply_type" => $documentHeader->gst_invoice_type,
            "charge_type" => "N",
            "igst_on_intra" => "N",
            "ecommerce_gstin" => ""
        ];

        $docDetails = (object) [
            "document_type" => 'INV',
            "document_number" => $documentNumber,
            "document_date" => date('d/m/Y', strtotime($documentHeader->document_date)),
        ];

        $sellerDetails = (object) [
            "gstin" => $organization?->gst_number,
            "legal_name" => $organization->name,
            "trade_name" => null,
            // "address1" => $organizationAddress->line_1,
            "address1" => substr($organizationAddress?->address ?? '', 0, 90),
            // "address2" => $organizationAddress->line_2,
            "address2" => null,
            "location" => $organizationAddress?->city?->name,
            // "pincode" => $organizationAddress->postal_code,
            "pincode" => $organizationAddress->pincode,
            "state_code" => $sellerStateCode->name,
            "phone_number" => $organizationAddress->phone,
            "email" => $organization?->email
        ];

        $buyerDetails = (object) [
            "gstin" => $documentHeader?->vendor->compliances->gstin_no,
            "legal_name" => $documentHeader?->vendor?->company_name,
            "trade_name" => null,
            "address1" => substr($sellerBillingAddress?->address ?? '', 0, 90),
            "address2" => null,
            "location" => $sellerBillingAddress?->city?->name,
            "pincode" => $sellerBillingAddress->pincode,
            "state_code" => $buyerStateCode->name,
            "place_of_supply" => $buyerStateCode->state_code,
            "phone_number" => $sellerBillingAddress->phone,
            "email" => $documentHeader?->vendor?->email
        ];

        $dispatchDetails = (object) [
            "company_name" => $documentHeader?->erpStore?->store_name,
            // "address1" => $organizationAddress->line_1,
            "address1" => substr($organizationAddress?->address ?? '', 0, 90),
            // "address2" => $organizationAddress->line_2,
            "address2" => null,
            "location" => $organizationAddress?->city?->name,
            // "pincode" => $organizationAddress->postal_code,
            "pincode" => $organizationAddress->pincode,
            "state_code" => $sellerStateCode->name,
        ];

        $shipDetails = (object) [
            // "gstin" => "05AAAPG7885R002",
            "gstin" => $documentHeader?->vendor->compliances->gstin_no,
            "legal_name" => $documentHeader?->vendor?->company_name,
            "trade_name" => null,
            "address1" => substr($sellerBillingAddress?->address ?? '', 0, 90),
            "address2" => null,
            "location" => $sellerBillingAddress?->city?->name,
            "pincode" => $sellerBillingAddress->pincode,
            "state_code" => $buyerStateCode->name,
        ];

        foreach($documentDetails as $key => $val){
            $uom = Unit::find($val?->uom_id);
            $orderQty = (isset($val?->accepted_qty) && ($val?->accepted_qty)) ? ($val?->accepted_qty) : ($val?->order_qty);
            $itemDiscount = (isset($val?->discount_amount) && ($val?->discount_amount)) ? ($val?->discount_amount) : ($val?->item_discount_amount);
            $headerDiscount = (isset($val?->header_discount_amount) && ($val?->header_discount_amount)) ? ($val?->header_discount_amount) : ($val?->header_discount_amount);
            $totalAmt = ($orderQty*$val?->rate) - ($itemDiscount + $headerDiscount);
            $totalItemValue = $totalAmt + ($val->cgst_value['value'] + $val->sgst_value['value'] + $val->igst_value['value']);
            if (count($val->taxes)) {
                foreach ($val->taxes as $taxs) {
                    $taxName = $taxs->ted_name . " " . number_format($taxs->ted_percentage, 2) . " %";
                    if (isset($taxBracket[$taxName])) {
                        $taxBracket[$taxName][0] += $taxs->ted_amount;
                        $taxBracket[$taxName][1] += $taxs->assesment_amount;
                    } else {
                        $taxBracket[$taxName][0] = $taxs->ted_amount;
                        $taxBracket[$taxName][1] = $taxs->assesment_amount;
                    }
                }
            }
            $gstRate = 0;
            if ((float)$val->igst_value['rate']) {
                $gstRate = $val->igst_value['rate'];
            } else {
                $gstRate = $val->cgst_value['rate'];
            }
            $totalCGSTValue += $val->cgst_value['value'];
            $totalSGSTValue += $val->sgst_value['value'];
            $totalIGSTValue += $val->igst_value['value'];
            $totalTaxValue = $totalCGSTValue + $totalIGSTValue + $totalSGSTValue;
            $itemList[] = (object) [
                "item_serial_number" => (string) $key,
				"product_description" => $val?->item?->item_name,
				"is_service" => "N",
				"hsn_code" => $val?->hsn_code,
				"bar_code" => null,
				"quantity" => round($orderQty),
				"free_quantity" => round($orderQty),
				"unit" => (string) $uom?->name,
				"unit_price" => round($val?->rate),
				"total_amount" => round($orderQty*$val?->rate),
				"pre_tax_value" => round($val?->tax_value),
				"discount" => round($itemDiscount + $headerDiscount),
				"other_charge" => 0,
				"assessable_value" => round($totalAmt),
				"gst_rate" => $gstRate,
				"igst_amount" => $val->igst_value['value'],
				"cgst_amount" => $val->cgst_value['value'],
				"sgst_amount" => $val->sgst_value['value'],
                "igst_rate" => $val->igst_value['rate'],
				"cgst_rate" => $val->cgst_value['rate'],
				"sgst_rate" => $val->sgst_value['rate'],
				"cess_rate" => 0,
				"cess_amount" => 0,
				"cess_nonadvol_amount" => 0,
				"state_cess_rate" => 0,
				"state_cess_amount" => 0,
				"state_cess_nonadvol_amount" => 0,
				"total_item_value" => round($totalItemValue, 2),
				"country_origin" => null,
				"order_line_reference" => null,
				"product_serial_number" => "",
				"batch_details" => array(
					"name" => $documentHeader->book_code.'-'.$documentHeader->document_number,
					"expiry_date" => '',
					"warranty_date" => ''
				),
				"attribute_details" => []
            ];
        }

        $valDtls = (object) [
            "total_assessable_value" => round($documentHeader->taxable_amount,2),
            "total_cgst_value" => round($totalCGSTValue, 2),
            "total_sgst_value" => round($totalSGSTValue, 2),
            "total_igst_value" => round($totalIGSTValue, 2),
            "total_cess_value" => 0,
            "total_discount" => 0,
            "total_other_charge" => round($documentHeader->expense_amount, 2),
            "total_invoice_value" => round(($documentHeader->total_amount),2),
            "total_cess_value_of_state" => 0,
            "round_off_amount" => 0,
            "total_invoice_value_additional_currency" => 0
        ];

        $payDtls = (object) [
            "bank_account_number" => null,
            "paid_balance_amount" => null,
            "credit_days" => null,
            "credit_transfer" => null,
            "direct_debit" => null,
            "branch_or_ifsc" => null,
            "payment_mode" => null,
            "payee_name" => null,
            "outstanding_amount" => null,
            "payment_instruction" => null,
            "payment_term" => null
        ];

        $refDtls = (object) [
            "invoice_remarks" => "",
            "preceding_document_details" => [],
            "contract_details" => [],
        ];

        $addlDocDtls =  [];

        $expDtls = (object) [
            "ship_bill_number" => null,
            "ship_bill_date" => null,
            "country_code" => null,
            "foreign_currency" => null,
            "refund_claim" => null,
            "port_code" => null,
            "export_duty" => null
        ];

        $ewbDtls = (object) [
            "transporter_id" => "",
            "transporter_name" => $documentHeader->transportation_name,
            "transportation_mode" => $documentHeader->transportation_mode,
            "transportation_distance" => 100,
            "transporter_document_number" => "12345",
            "transporter_document_date" => now()->format('d/m/Y'),
            "vehicle_number" => $documentHeader->vehicle_no,
            "vehicle_type" => "R"
        ];

        $result = [
            'tranDetails' => $tranDetails,
            'docDetails'  =>  $docDetails,
            'sellerDetails'  =>  $sellerDetails,
            'buyerDetails'  =>  $buyerDetails,
            'dispatchDetails'  =>  $dispatchDetails,
            'shipDetails'  =>  $shipDetails,
            'itemList'  =>  $itemList,
            'valDtls'  =>  $valDtls,
            'payDtls' => $payDtls,
            'addlDocDtls'  =>  $addlDocDtls,
            'expDtls'  =>  $expDtls,
            'ewbDtls' => $ewbDtls,
            'refDtls' => $refDtls
        ];
        return $result;

    }

    public static function generateQRCodeBase64($signedQRCode)
    {
        $qrCode = QrCode::create($signedQRCode)
        ->setMargin(0);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Load the image and trim white spaces
        $image = imagecreatefromstring($result->getString());
        $croppedImage = self::trimImage($image);

        // Save cropped image to string
        ob_start();
        imagepng($croppedImage);
        $imageData = ob_get_clean();

        // Convert QR code to base64
        return 'data:image/png;base64,' . base64_encode($imageData);
    }

    // Trim white spaces from image
    public static function trimImage($img)
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $top = $bottom = $left = $right = 0;

        // Get top margin
        for (; $top < $h; ++$top) {
            for ($x = 0; $x < $w; ++$x) {
                if (imagecolorat($img, $x, $top) !== 0xFFFFFF) {
                    break 2;
                }
            }
        }

        // Get bottom margin
        for (; $bottom < $h; ++$bottom) {
            for ($x = 0; $x < $w; ++$x) {
                if (imagecolorat($img, $x, $h - $bottom - 1) !== 0xFFFFFF) {
                    break 2;
                }
            }
        }

        // Get left margin
        for (; $left < $w; ++$left) {
            for ($y = 0; $y < $h; ++$y) {
                if (imagecolorat($img, $left, $y) !== 0xFFFFFF) {
                    break 2;
                }
            }
        }

        // Get right margin
        for (; $right < $w; ++$right) {
            for ($y = 0; $y < $h; ++$y) {
                if (imagecolorat($img, $w - $right - 1, $y) !== 0xFFFFFF) {
                    break 2;
                }
            }
        }

        // Crop the image
        return imagecrop($img, [
            'x' => $left,
            'y' => $top,
            'width' => $w - $right - $left,
            'height' => $h - $bottom - $top
        ]);
    }

    public static function getGstDetail($gstNumber){
        $authCredentials = self::getAuthCredentials();
        $requestUid = 'GOV-EINVOICE-'.date('dmy').time();
        $eInvoiceService = new EInvoiceService($authCredentials,$requestUid);
        $response = $eInvoiceService->getGSTINDetails($gstNumber);
        return $response;
    }

    private static function getStateCode($stateId){
        $stateCode = State::find($stateId);
        return $stateCode ? $stateCode : null;
    }

    private  static function generateIrn($docId, $document, $documentType) {
        $condition = self::checkIfGstInShouldGenerate($document, $documentType);
        if($condition){
            $documentHeader = $document;
            $documentDetails = $document -> items;
            $generateInvoice = MasterIndiaHelper::generateInvoice($documentHeader, $documentDetails);
            if ((isset($generateInvoice['results']) && isset(['results']['message']) && isset(['results']['message']['alert'])) && !empty($generateInvoice['results']['message']['alert'])) {
                return [
                        'results' => [
                                    'status' => 'Error',
                                    'message' => $generateInvoice['results']['message']['alert']
                                ]
                        ];
            }
            if (isset($generateInvoice['results']) && isset($generateInvoice['results']['errorMessage']) && !empty($generateInvoice['results']['errorMessage'])) {
                return [
                    'results' => [
                        'status' => 'Error',
                        'message' => $generateInvoice['results']['errorMessage']
                    ]
                ];
            }
            if (!isset($generateInvoice['results'])) {
                return [
                    'results' => [
                        'status' => 'Error',
                        'message' => "Cannot access Master India Service. Please check the credentials you're using and try again"
                    ]
                ];
            }
            $documentHeader->irnDetail()->create([
                'ack_no' => $generateInvoice['results']['message']['AckNo'],
                'ack_date' => $generateInvoice['results']['message']['AckDt'],
                'irn_number' => $generateInvoice['results']['message']['Irn'],
                'signed_invoice' => $generateInvoice['results']['message']['SignedInvoice'],
                'signed_qr_code' => $generateInvoice['results']['message']['SignedQRCode'],
                'ewb_no' => $generateInvoice['results']['message']['EwbNo'],
                'ewb_date' => $generateInvoice['results']['message']['EwbDt'],
                'ewb_valid_till' => $generateInvoice['results']['message']['EwbValidTill'],
                'status' => $generateInvoice['results']['message']['Status'],
                'remarks' => $generateInvoice['results']['message']['Remarks'],
                'type' => 'IRN'
            ]);
            return $generateInvoice;
        }
    }

    // On Submit check gst number
    private  static function validateGstIn($docId, $document, $documentType) {
        $user = Helper::getAuthenticatedUser();
        $condition = self::checkIfGstInShouldGenerate($document, $documentType);
        if($condition){
            $documentHeader = $document;
            $documentDetails = $document ?-> items ?? [];
            $eInvoice = $document?->irnDetail()->first();

            $organization = Organization::where('id', $user->organization_id)->first();
            $organizationAddress = Address::with(['city', 'state', 'country'])
                ->where('addressable_id', $user->organization_id)
                ->where('addressable_type', Organization::class)
                ->first();

            $shippingAddress = $documentHeader->shippingAddress;
            $storeAddress = $documentHeader->store_address;
            $buyerAddress = $documentHeader?->erpStore?->address;
            $sellerShippingAddress = $documentHeader->latestShippingAddress();
            $sellerBillingAddress = $documentHeader->latestBillingAddress();

        }
    }


    // Common check gst number
    public  static function validateGstNumber($gstNumber) {
        $user = Helper::getAuthenticatedUser();

        $checkGstIn = EInvoiceHelper::getGstDetail($gstNumber);
        if(!(is_string($checkGstIn))){
            if(!$checkGstIn['Status']){
                $errorMsg = "";
                if($checkGstIn['ErrorDetails'][0]['ErrorMessage'] == "Requested data is not available"){
                    $errorMsg = "Error: ". @$checkGstIn['ErrorDetails'][0]['ErrorCode'].' - Invalid GST Number';
                } else{
                    $errorMsg = "Error: ". @$checkGstIn['ErrorDetails'][0]['ErrorCode'].' -'.$checkGstIn['ErrorDetails'][0]['ErrorMessage'];
                }
                return [
                    'checkGstIn' => $checkGstIn,
                    'successMsg' => '',
                    'errorMsg' => $errorMsg,
                    'Status' => 0
                ];
            }
        }else {
            return [
                'checkGstIn' => $checkGstIn,
                'successMsg' => '',
                'errorMsg' => '',
                'Status' => 1
            ];
        }
    }

    public static function checkIfGstInShouldGenerate(Model $document, $documentType = null)
    {
        $serviceAlias = $document ?-> book ?-> service ?-> alias;
        if ($serviceAlias === ConstantHelper::PURCHASE_RETURN_SERVICE_ALIAS || $serviceAlias === ConstantHelper::SR_SERVICE_ALIAS ||
        ($serviceAlias === ConstantHelper::SI_SERVICE_ALIAS) ||
        ($serviceAlias === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS && !$document -> invoice_required)) {
            return true;
        } else {
            return false;
        }
    }

    public static function saveGstIn(Model $document)
    {
        $value = self::checkIfGstInShouldGenerate($document, null);
        if ($value) {
            // Generate Invoice
            $generateInvoice = self::generateIrn($document -> id, $document, null);
            if(isset($generateInvoice['results']) && $generateInvoice['results']['status'] == "Error"){
                return [
                    'status' => 'error',
                    'message' => "Error: ". @$generateInvoice['results']['message'],
                ];
            } else{
                return $generateInvoice;
            }
        }
        return $value;
    }

    // Generate Eway Bill
    public static function generateEwayBillData($document) {
        $user = Helper::getAuthenticatedUser();
        $authCredentials = self::getAuthCredentials();
        $requestUid = 'GOV-EINVOICE-'.date('dmy').time();
        $masterIndiaService = new MasterIndiaService($authCredentials,$requestUid);
        // $authToken = $masterIndiaService->getAuthToken();
        $configurations = Configuration::where('type', 'organization')
        ->where('type_id', $user->organization_id)
        ->whereIn('config_key', [
            ConstantHelper::CLIENT_ID,
            ConstantHelper::CLIENT_SECRET,
            ConstantHelper::CLIENT_USERNAME,
            ConstantHelper::CLIENT_PASSWORD,
            ConstantHelper::CLIENT_ACCESS_TOKEN
        ])
        ->pluck('config_value', 'config_key');
        $authToken = $configurations['e_invoice_access_token'] ?? null;
        // $authToken = config('app.masterindia.e_invoice_access_token');
        $documentHeader = $document;
        // $eInvoice = $documentHeader->irnDetail()->first();
        // $irnNumber = $eInvoice?->irn_number;
        // $documentNumber = $documentHeader->book_code .'-'. $documentHeader->document_number;

        // $organization = Organization::where('id', $user->organization_id)->first();
        // $organizationAddress = Address::with(['city', 'state', 'country'])
        //     ->where('addressable_id', $user->organization_id)
        //     ->where('addressable_type', Organization::class)
        //     ->first();
        $masterIndiaService = new MasterIndiaService($authCredentials,$requestUid);
        // $distance = $masterIndiaService->getDistance($documentHeader, $authToken);
        $distance = 100;
        // dd($distance);
        $requestData = self::generateHeader($documentHeader, $authToken, $distance);
        return $requestData;

    }

    public static function generateHeader($documentHeader, $authToken, $distance)
	{
        $documentDetails = $documentHeader -> items;
        $data = self::getInvoiceDetail($documentHeader, $documentDetails);
        $itemData = [];
        foreach ($data['itemList'] as $key2 => $item) {
            $itemData = self::generateItems($item);
        }

		return [
            'itemList' => $itemData,
			'access_token' => $authToken,
			'userGstin' => $data['sellerDetails']->gstin,
			'supply_type' => "outward",
			'sub_supply_type' => 'Supply',
			'sub_supply_description' => '',
			'document_type' => $data['docDetails']->document_type,
			'document_number' => $data['docDetails']->document_number,
			'document_date' => $data['docDetails']->document_date,

			'gstin_of_consignor' => $data['sellerDetails']->gstin,
			'legal_name_of_consignor' => $data['sellerDetails']->legal_name,
			'address1_of_consignor' => $data['sellerDetails']->address1,
			'address2_of_consignor' => $data['sellerDetails']->address2,
			'place_of_consignor' => $data['sellerDetails']->location,
			'pincode_of_consignor' => $data['sellerDetails']->pincode,
			'state_of_consignor' => $data['sellerDetails']->state_code,
			'actual_from_state_name' => $data['sellerDetails']->state_code,


			'gstin_of_consignee' => $data['buyerDetails']->gstin,
			'legal_name_of_consignee' => $data['buyerDetails']->legal_name,
			'address1_of_consignee' => $data['buyerDetails']->address1,
			'address2_of_consignee' => $data['buyerDetails']->address2,
			'place_of_consignee' => $data['buyerDetails']->location,
			'pincode_of_consignee' => $data['buyerDetails']->pincode,
			'state_of_supply' => $data['buyerDetails']->place_of_supply,
			'actual_to_state_name' => $data['buyerDetails']->state_code,

			'transaction_type' => '',
			'other_value' => $data['valDtls']->total_other_charge,
			'total_invoice_value' => $data['valDtls']->total_invoice_value,
			'taxable_amount' => $data['valDtls']->total_assessable_value,
			'cgst_amount' => $data['valDtls']->total_cgst_value,
			'sgst_amount' => $data['valDtls']->total_sgst_value,
			'igst_amount' => $data['valDtls']->total_igst_value,
			'cess_amount' => $data['valDtls']->total_cess_value,
			'cess_nonadvol_value' => '',


			'transporter_id' => $data['ewbDtls']->transporter_id,
			'transporter_name' => $data['ewbDtls']->transporter_name,
			'transporter_document_number' => $data['ewbDtls']->transporter_document_number,
			'transporter_document_date' => $data['ewbDtls']->transporter_document_date,
			'transportation_mode' => $data['ewbDtls']->transportation_mode,
			'transportation_distance' => $distance,
			'vehicle_number' => $data['ewbDtls']->vehicle_number,
			'vehicle_type' => $data['ewbDtls']->vehicle_type,
			'generate_status' => 1,
			'data_source' => '',
			'user_ref' => '',
			'location_code' => '',
			'eway_bill_status' => '',
			'auto_print' => '',
			'email' => '',
		];
	}

	public static function generateItems($item)
	{
		return [
			'product_name' => "",
			'product_description' => $item->product_description,
			'hsn_code' => $item->hsn_code,
			'quantity' => $item->quantity,
			'unit_of_product' => $item->unit,
			'cgst_rate' => $item->cgst_rate,
			'sgst_rate' => $item->sgst_rate,
			'igst_rate' => $item->igst_rate,
			'cess_rate' => $item->cess_rate,
			'cessNonAdvol' => "",
			'taxable_amount' => $item->total_item_value
		];
	}


    public static function generateEwayBill($documentHeader)
    {
        $user = Helper::getAuthenticatedUser();
        $postData = self::generateEwayBillData($documentHeader);
        $authCredentials = self::getAuthCredentials();
        $requestUid = 'GOV-EINVOICE-'.date('dmy').time();
        $eInvoiceService = new MasterIndiaService($authCredentials,$requestUid);
        $response = $eInvoiceService->generateEwaybillByIRN($postData);
        if(isset($response['status']) && $response['status'] != 'Success'){
            return [
                'status' => 'error',
                'message' => "Error: ". @$response['ErrorMessage'],
            ];
        } else{
            return $response;
        }
    }

}
