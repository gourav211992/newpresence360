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

class EInvoiceHelper
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

    public static function getGstInvoiceType($partyId, $partyCountryId, $sellerCountryId, string $partyType = 'customer'): string|null
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
        $gstRegistered = $party->compliances?->gst_applicable;
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
            if (isset($documentHeader->irnDetail) && $documentHeader->irnDetail) {
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
        $postData = self::prepareRequestPayload($documentHeader, $documentDetails);
        $authCredentials = self::getAuthCredentials();
        $requestUid = 'GOV-EINVOICE-' . date('dmy') . time();
        $eInvoiceService = new EInvoiceService($authCredentials, $requestUid);
        $response = $eInvoiceService->generateInvoice($postData);
        return $response;
    }

    private static function prepareRequestPayload($documentHeader, $documentDetails)
    {
        $user = Helper::getAuthenticatedUser();

        $invoiceDtls = self::getInvoiceDetail($documentHeader, $documentDetails);
        // dd($invoiceDtls);
        $invoiceData = [
            "Version" => '1.1',
            "TranDtls" => $invoiceDtls['tranDetails'],
            "DocDtls" => $invoiceDtls['docDetails'],
            "SellerDtls" => $invoiceDtls['sellerDetails'],
            "BuyerDtls" => $invoiceDtls['buyerDetails'],
            "DispDtls" => $invoiceDtls['dispatchDetails'],
            "ShipDtls" => $invoiceDtls['shipDetails'],
            "ItemList" => $invoiceDtls['itemList'],
            "ValDtls" => $invoiceDtls['valDtls'],
            "PayDtls" => $invoiceDtls['payDtls'],
            "RefDtls" => $invoiceDtls['payDtls'],
            "AddlDocDtls" => $invoiceDtls['addlDocDtls'],
            "ExpDtls" => $invoiceDtls['expDtls'],
            // "EwbDtls" => $invoiceDtls['ewbDtls'],
        ];

        return $invoiceData;
    }

    public function cancelInvoice(Request $request)
    {
        $cancelData = $request->all();

        $authCredentials = self::getAuthCredentials();
        $requestUid = '1';
        $eInvoiceService = new EInvoiceService($authCredentials, $requestUid);
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
                'Gstin' => $info['gstin'] ?? '',
                'TradeName' => $info['tradeNam'] ?? '',
                'LegalName' => $info['lgnm'] ?? '',
                'AddrBnm' => $addr['bnm'] ?? '',
                'AddrBno' => $addr['bno'] ?? '',
                'AddrFlno' => $addr['flno'] ?? '',
                'AddrSt' => $addr['st'] ?? '',
                'AddrLoc' => $addr['loc'] ?? '',
                'StateCode' => $stateCode,
                'AddrPncd' => $addr['pncd'] ?? '',
                'TxpType' => $info['dty'] ?? '',
                'Status' => (isset($info['sts']) && $info['sts'] === 'Active') ? 'ACT' : ($info['sts'] ?? ''),
                'BlkStatus' => 'U',
                'DtReg' => isset($info['rgdt']) && !empty($info['rgdt'])
                    ? \DateTime::createFromFormat('d/m/Y', str_replace('\\/', '/', $info['rgdt']))->format('Y-m-d')
                    : '',
                'DtDReg' => null,
            ];
            $final_response = [
                'Status' => 1,
                'errorMsg' => '',
                'successMsg' => '',
                'checkGstIn' => json_encode($formatted),
            ];
            // dd($final_response);
            return $final_response;

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public static function validateGstinName(string $gstNumber)
    {
        try {

            $gstin = $gstNumber;
            // $authCredentials = self::getAuthCredentials();
            $requestUid = 'GOV-EINVOICE-' . date('dmy') . time();
            ;
            // $eInvoiceService = new MasterIndiaService($authCredentials,$requestUid);
            $eInvoiceService = new MasterIndiaService($requestUid);


            $authToken = $eInvoiceService->getAuthToken();
            $baseUrl = config('app.masterindia.base_url');
            $gstinUrl = $baseUrl . 'commonapis/searchgstin?gstin=' . urlencode($gstin);
            $clientId = config('app.masterindia.gstin_client_id');
            $requestHeader = array(
                'client_id:' . $clientId,
                'Authorization:Bearer ' . $authToken,
                'Content-Type: application/json'
            );
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $gstinUrl);
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
        $validator = Validator::make($request->all(), [
            'irn' => [
                'required'
            ]
        ]);

        if ($validator->fails()) {
            $response = [
                'Status' => 0,
                'ErrorDetails' => [
                    [
                        "ErrorCode" => 500,
                        'ErrorMessage' => $validator->errors()->first()
                    ]
                ],
                'Data' => null,
                'InfoDtls' => null,
            ];
            return response()->json($response);
        }

        $authCredentials = self::getAuthCredentials();
        $requestUid = '1';
        $eInvoiceService = new EInvoiceService($authCredentials, $requestUid);
        try {
            $response = $eInvoiceService->getInvoiceByIRN($request->irn);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getGstDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => [
                'required'
            ]
        ]);

        if ($validator->fails()) {
            $response = [
                'Status' => 0,
                'ErrorDetails' => [
                    [
                        "ErrorCode" => 500,
                        'ErrorMessage' => $validator->errors()->first()
                    ]
                ],
                'Data' => null,
                'InfoDtls' => null,
            ];
            return response()->json($response);
        }

        $authCredentials = self::getAuthCredentials();
        $requestUid = '1';
        $eInvoiceService = new EInvoiceService($authCredentials, $requestUid);
        try {
            $response = $eInvoiceService->getGSTINDetails($request->gstin);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function syncGstDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gstin' => [
                'required'
            ]
        ]);

        if ($validator->fails()) {
            $response = [
                'Status' => 0,
                'ErrorDetails' => [
                    [
                        "ErrorCode" => 500,
                        'ErrorMessage' => $validator->errors()->first()
                    ]
                ],
                'Data' => null,
                'InfoDtls' => null,
            ];
            return response()->json($response);
        }

        $authCredentials = self::getAuthCredentials();
        $requestUid = '1';
        $eInvoiceService = new EInvoiceService($authCredentials, $requestUid);
        try {
            $response = $eInvoiceService->syncGSTIN($request->gstin);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function irnByDocDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
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

        if ($validator->fails()) {
            $response = [
                'Status' => 0,
                'ErrorDetails' => [
                    [
                        "ErrorCode" => 500,
                        'ErrorMessage' => $validator->errors()->first()
                    ]
                ],
                'Data' => null,
                'InfoDtls' => null,
            ];
            return response()->json($response);
        }

        $authCredentials = self::getAuthCredentials();
        $requestUid = '1';
        $eInvoiceService = new EInvoiceService($authCredentials, $requestUid);
        try {
            $response = $eInvoiceService->getIRNByDocDetails($request->doctype, $request->docnum, $request->docdate);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function rejectedIrn(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => [
                'required'
            ]
        ]);

        if ($validator->fails()) {
            $response = [
                'Status' => 0,
                'ErrorDetails' => [
                    [
                        "ErrorCode" => 500,
                        'ErrorMessage' => $validator->errors()->first()
                    ]
                ],
                'Data' => null,
                'InfoDtls' => null,
            ];
            return response()->json($response);
        }

        $authCredentials = self::getAuthCredentials();
        $requestUid = '1';
        $eInvoiceService = new EInvoiceService($authCredentials, $requestUid);
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
            'client_id' => $configurations['e_invoice_client_id'] ?? env('EINVOICE_CLIENT_ID', ''),
            'client_secret' => $configurations['e_invoice_client_secret'] ?? env('EINVOICE_CLIENT_SECRET', ''),
            'user_name' => $configurations['e_invoice_client_username'] ?? env('EINVOICE_USER_NAME', ''),
            'password' => $configurations['e_invoice_client_password'] ?? env('EINVOICE_PASSWORD', ''),
            'gstin' => $organization->gst_number ?? env('EINVOICE_GSTIN', ''),
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
        $documentNumber = $documentHeader->book_code . '/' . $documentHeader->document_number;

        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();
        $shippingAddress = $documentHeader->shippingAddress;
        $storeAddress = $documentHeader->store_address;
        $buyerAddress = $documentHeader?->location_address_details;
        $sellerShippingAddress = $documentHeader->latestShippingAddress();
        $sellerBillingAddress = $documentHeader->latestBillingAddress();
        $sellerStateCode = self::getStateCode($organizationAddress->state_id);
        $buyerStateCode = self::getStateCode($sellerBillingAddress->state_id);
        $shipStateCode = self::getStateCode($sellerBillingAddress->state_id);

        $tranDetails = (object) [
            "TaxSch" => "GST",
            "SupTyp" => "B2B",
            "RegRev" => "Y",
            "EcmGstin" => null,
            "IgstOnIntra" => "N"
        ];

        $docDetails = (object) [
            "Typ" => 'INV',
            "No" => $documentNumber,
            "Dt" => date('d/m/Y', strtotime($documentHeader->document_date)),
        ];

        $sellerDetails = (object) [
            "Gstin" => $organization?->gst_number,
            "LglNm" => $organization->name,
            "TrdNm" => null,
            "Addr1" => $organizationAddress->line_1,
            "Addr2" => $organizationAddress->line_2,
            "Loc" => $organizationAddress?->city?->name,
            "Pin" => $organizationAddress->postal_code,
            // "Stcd"  =>  $sellerStateCode,
            "Stcd" => '7',
            "Ph" => $organizationAddress->phone,
            "Em" => $organization?->email
        ];

        $buyerDetails = (object) [
            "Gstin" => $documentHeader?->vendor->compliances->gstin_no,
            // "Gstin" =>  '11AAACT5131A2Z9',
            "LglNm" => $documentHeader?->vendor?->company_name,
            "TrdNm" => null,
            "Pos" => '11',
            "Addr1" => $sellerBillingAddress?->address,
            "Addr2" => null,
            "Loc" => $sellerBillingAddress?->city?->name,
            "Pin" => @$sellerBillingAddress->pincode,
            // "Pin"   =>  '737132',
            "Stcd" => '11',
            // "Stcd"  =>  $buyerStateCode,
            "Ph" => $sellerBillingAddress->phone,
            "Em" => $documentHeader?->vendor?->email
        ];

        $dispatchDetails = (object) [
            "Nm" => $documentHeader?->erpStore?->store_name,
            "Addr1" => $buyerAddress?->address,
            "Addr2" => null,
            "Loc" => $buyerAddress?->city?->name,
            "Pin" => @$buyerAddress->pincode,
            // "Pin"   =>  '737132',
            "Stcd" => '7',
            // "Stcd" => $buyerStateCode,
        ];

        $shipDetails = (object) [
            // "Gstin" =>  $documentHeader?->vendor->compliances->gstin_no,
            "Gstin" => $documentHeader?->vendor->compliances->gstin_no,
            "LglNm" => $documentHeader?->vendor?->company_name,
            "TrdNm" => null,
            "Pos" => '11',
            "Addr1" => $sellerBillingAddress?->address,
            "Addr2" => null,
            "Loc" => $sellerBillingAddress?->city?->name,
            "Pin" => @$sellerBillingAddress->pincode,
            // "Pin"   =>  '737132',
            "Stcd" => '11',
            // "Stcd"  =>  $shipStateCode,
            "Ph" => $sellerBillingAddress->phone,
            "Em" => $documentHeader?->vendor?->email
        ];

        foreach ($documentDetails as $key => $val) {
            $uom = Unit::find($val?->uom_id);
            $orderQty = (isset($val?->accepted_qty) && ($val?->accepted_qty)) ? ($val?->accepted_qty) : ($val?->order_qty);
            $itemDiscount = (isset($val?->discount_amount) && ($val?->discount_amount)) ? ($val?->discount_amount) : ($val?->item_discount_amount);
            $headerDiscount = (isset($val?->header_discount_amount) && ($val?->header_discount_amount)) ? ($val?->header_discount_amount) : ($val?->header_discount_amount);
            $totalAmt = ($orderQty * $val?->rate) - ($itemDiscount + $headerDiscount);
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
            $totalCGSTValue += $val->cgst_value['value'];
            $totalSGSTValue += $val->sgst_value['value'];
            $totalIGSTValue += $val->igst_value['value'];
            $totalTaxValue = $totalCGSTValue + $totalIGSTValue + $totalSGSTValue;
            $itemList[] = (object) [
                "SlNo" => (string) $val?->item_id,
                "PrdDesc" => $val?->item?->item_name,
                "IsServc" => "N",
                // "HsnCd" => '39233010',
                "HsnCd" => $val?->hsn_code,
                "Barcde" => null,
                "Qty" => round($orderQty),
                "FreeQty" => round($orderQty),
                "Unit" => (string) $uom?->name,
                "UnitPrice" => round($val?->rate),
                "TotAmt" => round($orderQty * $val?->rate),
                "Discount" => round($itemDiscount + $headerDiscount),
                "PreTaxVal" => round($val?->tax_value),
                "AssAmt" => round($totalAmt),
                "GstRt" => 18,
                "IgstAmt" => $val->igst_value['value'],
                "CgstAmt" => $val->cgst_value['value'],
                "SgstAmt" => $val->sgst_value['value'],
                "CesRt" => 0,
                "CesAmt" => 0,
                "CesNonAdvlAmt" => 0,
                "StateCesRt" => 0,
                "StateCesAmt" => 0,
                "StateCesNonAdvlAmt" => 0,
                "OthChrg" => 0,
                "TotItemVal" => round($totalItemValue, 2),
                "OrdLineRef" => null,
                "OrgCntry" => null,
                "PrdSlNo" => null,
                // "BchDtls" => (object)[
                //     "Nm" => $documentHeader->document_number,
                //     "ExpDt" => null,
                //     "WrDt" => null
                // ],
                // "AttribDtls" => (object)[]
            ];
        }

        $valDtls = (object) [
            "AssVal" => round($documentHeader->taxable_amount, 2),
            "CgstVal" => round($totalCGSTValue, 2),
            "SgstVal" => round($totalSGSTValue, 2),
            "IgstVal" => round($totalIGSTValue, 2),
            "CesVal" => 0,
            "StCesVal" => 0,
            "Discount" => 0,
            "OthChrg" => round($documentHeader->expense_amount, 2),
            "RndOffAmt" => 0,
            "TotInvVal" => round(($documentHeader->total_amount), 2),
            "TotInvValFc" => 0
        ];

        $payDtls = (object) [
            "Nm" => null,
            "AccDet" => null,
            "Mode" => null,
            "FinInsBr" => null,
            "PayTerm" => null,
            "PayInstr" => null,
            "CrTrn" => null,
            "DirDr" => null,
            "CrDay" => 0,
            "PaidAmt" => 0,
            "PaymtDue" => 0,
            "TotInvValFc" => 0
        ];

        // $refDtls = (object) [
        //     "InvRm" => null,
        //     "DocPerdDtls" => (object) [
        //         "InvStDt"   => null,
        //         "InvEndDt"  => null,
        //     ],
        //     "PrecDocDtls" => (object) [
        //     [
        //         "InvNo"     => null,
        //         "InvDt"     => null,
        //         "OthRefNo"  => null
        //     ]
        //     ],
        //     "ContrDtls" => (object) []
        // ];

        $addlDocDtls = [
            (object) array(
                "Url" => "https://einv-apisandbox.nic.in",
                "Docs" => "Test Doc",
                "Info" => "Document Test"
            )
        ];

        $expDtls = (object) [
            "ShipBNo" => null,
            "ShipBDt" => null,
            "Port" => null,
            "RefClm" => null,
            "ForCur" => null,
            "CntCode" => null,
            "ExpDuty" => null,
        ];

        $ewbDtls = (object) [
            "TransId" => 'null',
            "TransName" => 'null',
            "Distance" => 100,
            "TransDocNo" => '12345',
            "TransDocDt" => '01/03/2025',
            "VehNo" => null,
            "VehType" => 'R',
            "TransMode" => '1',
        ];

        $result = [
            'tranDetails' => $tranDetails,
            'docDetails' => $docDetails,
            'sellerDetails' => $sellerDetails,
            'buyerDetails' => $buyerDetails,
            'dispatchDetails' => $dispatchDetails,
            'shipDetails' => $shipDetails,
            'itemList' => $itemList,
            'valDtls' => $valDtls,
            'payDtls' => $payDtls,
            'addlDocDtls' => $addlDocDtls,
            'expDtls' => $expDtls,
            // 'ewbDtls' => $ewbDtls,
        ];

        return $result;

    }

    public static function generateQRCodeBase64($signedQRCode)
    {
        $qrCode = QrCode::create($signedQRCode)
            ->setMargin(0); // Remove padding
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

    public static function getGstDetail($gstNumber)
    {
        $authCredentials = self::getAuthCredentials();
        $requestUid = 'GOV-EINVOICE-' . date('dmy') . time();
        $eInvoiceService = new EInvoiceService($authCredentials, $requestUid);
        $response = $eInvoiceService->getGSTINDetails($gstNumber);
        return $response;
    }

    private static function getStateCode($stateId)
    {
        $stateCode = State::find($stateId);
        return $stateCode ? $stateCode->state_code : null;
    }

    private static function generateIrn($docId, $document, $documentType)
    {
        $condition = self::checkIfGstInShouldGenerate($document, $documentType);
        if ($condition) {
            $documentHeader = $document;
            $documentDetails = $document->items;
            // dd($document->itemstoArray());
            $generateInvoice = EInvoiceHelper::generateInvoice($documentHeader, $documentDetails);
            if (isset($generateInvoice['ErrorDetails']) && !empty($generateInvoice['ErrorDetails'])) {
                return $generateInvoice;
            }
            $documentHeader->irnDetail()->create([
                'ack_no' => $generateInvoice['AckNo'],
                'ack_date' => $generateInvoice['AckDt'],
                'irn_number' => $generateInvoice['Irn'],
                'signed_invoice' => $generateInvoice['SignedInvoice'],
                'signed_qr_code' => $generateInvoice['SignedQRCode'],
                'ewb_no' => $generateInvoice['EwbNo'],
                'ewb_date' => $generateInvoice['EwbDt'],
                'ewb_valid_till' => $generateInvoice['EwbValidTill'],
                'status' => $generateInvoice['Status'],
                'remarks' => $generateInvoice['Remarks']
            ]);

            return $generateInvoice;
        }
    }

    // On Submit check gst number
    private static function validateGstIn($docId, $document, $documentType)
    {
        $user = Helper::getAuthenticatedUser();
        $condition = self::checkIfGstInShouldGenerate($document, $documentType);
        if ($condition) {
            $documentHeader = $document;
            $documentDetails = $document?->items ?? [];
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
    public static function validateGstNumber($gstNumber)
    {
        $user = Helper::getAuthenticatedUser();

        $checkGstIn = EInvoiceHelper::getGstDetail($gstNumber);
        if (!(is_string($checkGstIn))) {
            if (!$checkGstIn['Status']) {
                $errorMsg = "";
                if ($checkGstIn['ErrorDetails'][0]['ErrorMessage'] == "Requested data is not available") {
                    $errorMsg = "Error: " . @$checkGstIn['ErrorDetails'][0]['ErrorCode'] . ' - Invalid GST Number';
                } else {
                    $errorMsg = "Error: " . @$checkGstIn['ErrorDetails'][0]['ErrorCode'] . ' -' . $checkGstIn['ErrorDetails'][0]['ErrorMessage'];
                }
                return [
                    'checkGstIn' => $checkGstIn,
                    'successMsg' => '',
                    'errorMsg' => $errorMsg,
                    'Status' => 0
                ];
            }
        } else {
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
        $serviceAlias = $document?->book?->service?->alias;
        if (
            $serviceAlias === ConstantHelper::PURCHASE_RETURN_SERVICE_ALIAS || $serviceAlias === ConstantHelper::SR_SERVICE_ALIAS ||
            ($serviceAlias === ConstantHelper::SI_SERVICE_ALIAS) ||
            ($serviceAlias === ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS)
        ) {
            return true;
        } else {
            return false;
        }
    }

    public static function saveGstIn(Model $document)
    {
        $value = self::checkIfGstInShouldGenerate($document, null);
        if ($value) {
            // Validate GSTIN
            // $validateGstIn = array();
            // $validateGstIn = self::validateGstIn($document -> id, $document, null);
            // if(isset($validateGstIn) && !$validateGstIn['Status']){
            //     return [
            //         'status' => 'error',
            //         'message' => $validateGstIn['errorMsg'],
            //     ];
            // }

            // Generate Invoice
            $generateInvoice = self::generateIrn($document->id, $document, null);
            if (isset($generateInvoice) && !$generateInvoice['Status']) {
                return [
                    'status' => 'error',
                    'message' => "Error: " . @$generateInvoice['ErrorDetails'][0]['ErrorCode'] . ' -' . $generateInvoice['ErrorDetails'][0]['ErrorMessage'],
                ];
            } else {
                return $generateInvoice;
            }
        }
        return $value;
    }

    // Generate Eway Bill
    private static function generateEwayBillData($document)
    {
        $user = Helper::getAuthenticatedUser();

        $documentHeader = $document;
        $eInvoice = $documentHeader->irnDetail()->first();
        $irnNumber = $eInvoice?->irn_number;
        $documentNumber = $documentHeader->book_code . '-' . $documentHeader->document_number;

        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationAddress = Address::with(['city', 'state', 'country'])
            ->where('addressable_id', $user->organization_id)
            ->where('addressable_type', Organization::class)
            ->first();

        $ewbDetails = [
            "Irn" => $irnNumber,
            "TransId" => $organization?->gst_number,
            "TransName" => $organization?->name,
            "Distance" => 1521,
            "TransDocNo" => $documentNumber,
            "TransDocDt" => date('d/m/Y', strtotime($documentHeader->document_date)),
            "VehNo" => $documentHeader->vehicle_no,
            "VehType" => 'R',
            "TransMode" => $documentHeader->transportationMode?->code,
        ];

        return $ewbDetails;

    }

    public static function generateEwayBill($documentHeader)
    {
        $user = Helper::getAuthenticatedUser();
        $postData = self::generateEwayBillData($documentHeader);
        $authCredentials = self::getAuthCredentials();
        $requestUid = 'GOV-EINVOICE-' . date('dmy') . time();
        $eInvoiceService = new EInvoiceService($authCredentials, $requestUid);
        $response = $eInvoiceService->generateEwaybillByIRN($postData);
        // dd($response);
        if (isset($response['ErrorDetails']) && !empty($response['ErrorDetails'])) {
            return [
                'status' => 'error',
                'message' => "Error: " . @$response['ErrorDetails'][0]['ErrorCode'] . ' -' . $response['ErrorDetails'][0]['ErrorMessage'],
            ];
        } else {
            return $response;
        }
    }

}
