<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Services\StateCodeMaster;
use App\Services\EInvoice\EInvoiceService;
use Illuminate\Support\Facades\Validator;
// use App\Services\LoggerFactory;

class EInvoiceServiceController extends Controller
{
    public $log;

	public function __construct()
	{
		// $logFactory = new LoggerFactory();
		// $this->log = $logFactory->setPath('logs/services')->createLogger('gov-e-invoice');
	}

    public function generateInvoice(Request $request)
    {
        // $this->log->info('Request info'. json_encode($request->all()));
		// if(!$request->issue_id){
		// 	die("ERROR: Please select issue id");
		// }
		// if(!$request->type){
		// 	die("ERROR: Please provide type either cr/dr/inv");
		// }
		// if(!$request->source){
		// 	die("Please specify for whom this invoice will be generated default value is sfl");
		// }

        $postData = $this->prepareRequestPayload($request);
        // dd(json_encode($postData));
        $authCredentials = [
            "user_name" => 'pratiksha',
            "password" => 'Prati@123',
            'client_id' => 'AAACS07TXP2Z0N7',
            'client_secret' => 'uA9OCc3q7WKkYyESsgao',
            'gstin' => '07AAACS0189B2ZP',
        ];
        // print_r($authCredentials);

        $requestUid = 'MIDC-EINVOICE-'.date('dmy').time();
        $eInvoiceService = new EInvoiceService($authCredentials,$requestUid);
        $response = $eInvoiceService->generateInvoice($postData);
        return $response;
    }

    private function prepareRequestPayload($request)
    {
        
        $invoiceData = [
            "Version" => $responseData['VERSION'],
            "TranDtls" => [
                "TaxSch" => $responseData['TRANSACTION_SCHEME'],
                "SupTyp" => $responseData['TYPE_OF_SUPPLY'],
                "RegRev" => "Y",
                "EcmGstin" => null,
                "IgstOnIntra" => $responseData['REGULAR_REVERSE']
            ],
            "DocDtls" => [
                "Typ" => $responseData['DOCUMENT_TYPE'],
                "No" => $responseData['DOCUMENT_NUMBER'],
                "Dt" => $responseData['DOCUMENT_DATE']
            ],
            "SellerDtls" => [
                "Gstin" => $responseData['SELLER_GSTIN'],
                "LglNm" => $responseData['SELLER_LEGAL_NAME_OF_BUSINESS'],
                "TrdNm" => $responseData['SELLER_TRADE_NAME_OF_BUSINESS'],
                "Pos" => $responseData['SELLER_POS'],
                "Addr1" => $responseData['SELLER_ADDRESS_1'],
                "Addr2" => $responseData['SELLER_ADDRESS_2'],
                "Loc" => $responseData['SELLER_LOCATION'],
                "Pin" => $responseData['SELLER_PIN_CODE'],
                "Stcd" => $this->getStateCode($responseData['SELLER_STATE_NAME']),
                "Ph" => $responseData['SELLER_PHONE_NUMBER'],
                "Em" => $responseData['SELLER_E_MAIL_ID']
            ],
            "BuyerDtls" => [
                "Gstin" => $responseData['BUYER_GSTIN'],
                "LglNm" => $responseData['BUYER_LEGAL_NAME_OF_BUSINESS'],
                "TrdNm" => $responseData['BUYER_TRADE_NAME_OF_BUSINESS'],
                "Pos" => $responseData['BUYER_POS'],
                "Addr1" => $responseData['BUYER_ADDRESS_1'],
                "Addr2" => $responseData['BUYER_ADDRESS_2'],
                "Loc" => $responseData['BUYER_LOCATION'],
                "Pin" => $responseData['BUYER_PIN_CODE'],                
                "Stcd" => $this->getStateCode($responseData['BUYER_STATE_NAME']),
                "Ph" => $responseData['BUYER_PHONE_NUMBER'],
                "Em" => $responseData['BUYER_E_MAIL_ID']
            ],
            "DispDtls" => [
                "Nm" => $responseData['DESPATCH_LOCATION_NAME'],
                "Addr1" => $responseData['DESPATCH_ADDRESS1'],
                "Addr2" => $responseData['DESPATCH_ADDRESS2'],
                "Loc" => $responseData['DESPATCH_CITY'],
                "Pin" => $responseData['DESPATCH_PINCODE'],
                "Stcd" => $responseData['DESPATCH_STATECODE']
            ],
            "ShipDtls" => [
                "Gstin" => $responseData['SHIPMENT_GSTIN'],
                "LglNm" => $responseData['SHIPMENT_NAME'],
                "TrdNm" => $responseData['SHIPMENT_TRADE_NAME'],
                "Addr1" => $responseData['SHIPMENT_ADDRESS1'],
                "Addr2" => $responseData['SHIPMENT_ADDRESS2'],
                "Loc" => $responseData['SHIPMENT_PLACE'],
                "Pin" => $responseData['SHIPMENT_PINCODE'],
                "Stcd" => $responseData['SHIPMENT_STATE_CODE']
            ],
            "ItemList" => [
                [
                    "SlNo" => $responseData['SL_NO'],
                    "PrdDesc" => $responseData['PRODUCT_DESCRIPTION'],
                    "IsServc" => "N",
                    "HsnCd" => $responseData['HSN_CODE'],
                    "Barcde" => $responseData['BAR_CODE'],
                    "Qty" => $responseData['QUANTITY'],
                    "FreeQty" => $responseData['FREE_QUANTITY'],
                    "Unit" => $responseData['UNIT'],
                    "UnitPrice" => $responseData['UNIT_PRICE'],
                    "TotAmt" => $responseData['TOTAL_AMOUNT'],
                    "Discount" => $responseData['DISCOUNT'],
                    "PreTaxVal" => $responseData['PRE_TAX_VALUE'],
                    "AssAmt" => $responseData['ASSESSABLE_TAXABLE_AMOUNT'],
                    "GstRt" => $responseData['GST_RATE'],
                    "IgstAmt" => $responseData['IGST_AMOUNT'],
                    "CgstAmt" => $responseData['CGST_AMOUNT'],
                    "SgstAmt" => $responseData['SGST_AMOUNT'],
                    "CesRt" => $responseData['CESS_RATE'],
                    "CesAmt" => $responseData['CESS_AMOUNT'],
                    "CesNonAdvlAmt" => $responseData['CESS_NON_ADVOL_AMOUNT'],
                    "StateCesRt" => $responseData['STATE_CESS_RATE'],
                    "StateCesAmt" => $responseData['STATE_CESS_AMOUNT'],
                    "StateCesNonAdvlAmt" => $responseData['STATE_CESS_NON_ADVOL_AMOUNT'],
                    "OthChrg" => $responseData['OTHER_CHARGES'],
                    "TotItemVal" => $responseData['TOTAL_ITEM_VALUE'],
                    "OrdLineRef" => $responseData['ORDER_LINE_REFERENCE'],
                    "OrgCntry" => $responseData['ORIGIN_COUNTRY'],
                    "PrdSlNo" => $responseData['PRODUCT_SERIAL_NO'],
                    "BchDtls" => [
                        "Nm" => $responseData['DOCUMENT_NUMBER'],
                        "ExpDt" => null,
                        "WrDt" => null
                    ],
                    "AttribDtls" => []
                ]
            ],
            "ValDtls" => [
                "AssVal" => $responseData['ASSESSABLE_AMOUNT'],
                "CgstVal" => $responseData['CGST_VALUE'],
                "SgstVal" => $responseData['SGST_VALUE'],
                "IgstVal" => $responseData['IGST_VALUE'],
                "CesVal" => $responseData['CESS_VALUE'],
                "StCesVal" => $responseData['STATE_CESS_VALUE'],
                "Discount" => $responseData['DISCOUNT'],
                "OthChrg" => $responseData['OTHER_CHARGES'],
                "RndOffAmt" => $responseData['ROUNDING_OFF'],
                "TotInvVal" => $responseData['TOTAL_INVOICE_VALUE'],
                "TotInvValFc" => $responseData['TOTAL_INV_VAL_IN_FRGN_CURRENCY']
            ],
            "PayDtls" => [
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
            ],
            "RefDtls" => [
                "InvRm" => null,
                "DocPerdDtls" => [
                    "InvStDt" => $responseData['INVOICE_START_DATE'],
                    "InvEndDt" => $responseData['INVOICE_END_DATE']
                ],
                "PrecDocDtls" => [
                [
                    "InvNo" => $responseData['REF_INVOICE_NO'],
                    "InvDt" => $responseData['REF_INVOICE_DATE'],
                    "OthRefNo" => null
                ]
                ],
                "ContrDtls" => []
            ],
            "AddlDocDtls" => [],
            "ExpDtls" => [
                "ShipBNo" => null,
                "ShipBDt" => null,
                "Port" => null,
                "RefClm" => null,
                "ForCur" => null,
                "CntCode" => null,
                "ExpDuty" => null,
            ],
            "EwbDtls" => [
                "TransId" => null,
                "TransName" => null,
                "Distance" => 0,
                "TransDocNo" => null,
                "TransDocDt" => null,
                "VehNo" => null,
                "VehType" => null,
                "TransMode" => null,
            ]
        ];

        return $invoiceData;
    }

    private function getStateCode($statename){
        $stateCode = StateCodeMaster::where('name',$statename)->first();
        return $stateCode ? $stateCode->code : null;
    }

    public function cancelInvoice(Request $request)
    {
        $cancelData = $request->all();

        $authCredentials = [
            "user_name" => env('EINVOICE_USER_NAME'),
            "password" => env('EINVOICE_PASSWORD'),
            'client_id' => env('EINVOICE_CLIENT_ID'),
            'client_secret' => env('EINVOICE_CLIENT_SECRET'),
            'gstin' => env('EINVOICE_GSTIN'),
        ];
        $requestUid = '1';
        $eInvoiceService = new EInvoiceService($authCredentials,$requestUid);
        try {
            $response = $eInvoiceService->cancelInvoice($cancelData);
            return response()->json($response);
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

        $authCredentials = [
            "user_name" => env('EINVOICE_USER_NAME'),
            "password" => env('EINVOICE_PASSWORD'),
            'client_id' => env('EINVOICE_CLIENT_ID'),
            'client_secret' => env('EINVOICE_CLIENT_SECRET'),
            'gstin' => env('EINVOICE_GSTIN'),
        ];
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

        $authCredentials = [
            "user_name" => env('EINVOICE_USER_NAME'),
            "password" => env('EINVOICE_PASSWORD'),
            'client_id' => env('EINVOICE_CLIENT_ID'),
            'client_secret' => env('EINVOICE_CLIENT_SECRET'),
            'gstin' => env('EINVOICE_GSTIN'),
        ];
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

        $authCredentials = [
            "user_name" => env('EINVOICE_USER_NAME'),
            "password" => env('EINVOICE_PASSWORD'),
            'client_id' => env('EINVOICE_CLIENT_ID'),
            'client_secret' => env('EINVOICE_CLIENT_SECRET'),
            'gstin' => env('EINVOICE_GSTIN'),
        ];
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

        $authCredentials = [
            "user_name" => env('EINVOICE_USER_NAME'),
            "password" => env('EINVOICE_PASSWORD'),
            'client_id' => env('EINVOICE_CLIENT_ID'),
            'client_secret' => env('EINVOICE_CLIENT_SECRET'),
            'gstin' => env('EINVOICE_GSTIN'),
        ];
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

        $authCredentials = [
            "user_name" => env('EINVOICE_USER_NAME'),
            "password" => env('EINVOICE_PASSWORD'),
            'client_id' => env('EINVOICE_CLIENT_ID'),
            'client_secret' => env('EINVOICE_CLIENT_SECRET'),
            'gstin' => env('EINVOICE_GSTIN'),
        ];
        $requestUid = '1';
        $eInvoiceService = new EInvoiceService($authCredentials,$requestUid);
        try {
            $response = $eInvoiceService->getRejectedIRNsDetails($request->date);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function generateEwaybill(Request $request){
        $ewaybillData = $request->all();
        $authCredentials = [
            "user_name" => env('EINVOICE_USER_NAME'),
            "password" => env('EINVOICE_PASSWORD'),
            'client_id' => env('EINVOICE_CLIENT_ID'),
            'client_secret' => env('EINVOICE_CLIENT_SECRET'),
            'gstin' => env('EINVOICE_GSTIN'),
        ];
        $requestUid = '1';
        $eInvoiceService = new EInvoiceService($authCredentials,$requestUid);
        try {
            $response = $eInvoiceService->generateEwaybillByIRN($ewaybillData);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getEwaybillDetails(Request $request){
        $validator = Validator::make($request->all(),[
            'irn' => [
                'required'
            ]
        ]);

        if($validator->fails()){
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

        $authCredentials = [
            "user_name" => env('EINVOICE_USER_NAME'),
            "password" => env('EINVOICE_PASSWORD'),
            'client_id' => env('EINVOICE_CLIENT_ID'),
            'client_secret' => env('EINVOICE_CLIENT_SECRET'),
            'gstin' => env('EINVOICE_GSTIN'),
        ];
        $requestUid = '1';
        $eInvoiceService = new EInvoiceService($authCredentials,$requestUid);
        try {
            $response = $eInvoiceService->getEwaybillByIRN($request->irn);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function cancelEwaybill(Request $request){
        $cancelData = $request->all();
        $authCredentials = [
            "user_name" => env('EINVOICE_USER_NAME'),
            "password" => env('EINVOICE_PASSWORD'),
            'client_id' => env('EINVOICE_CLIENT_ID'),
            'client_secret' => env('EINVOICE_CLIENT_SECRET'),
            'gstin' => env('EINVOICE_GSTIN'),
        ];
        $requestUid = '1';
        $eInvoiceService = new EInvoiceService($authCredentials,$requestUid);
        try {
            $response = $eInvoiceService->cancelEwaybillByIRN($cancelData);
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function healthCheck(){
        $authCredentials = [
            "user_name" => env('EINVOICE_USER_NAME'),
            "password" => env('EINVOICE_PASSWORD'),
            'client_id' => env('EINVOICE_CLIENT_ID'),
            'client_secret' => env('EINVOICE_CLIENT_SECRET'),
            'gstin' => env('EINVOICE_GSTIN'),
        ];
        $requestUid = '1';
        $eInvoiceService = new EInvoiceService($authCredentials,$requestUid);
        try {
            $response = $eInvoiceService->healthCheck();
            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
