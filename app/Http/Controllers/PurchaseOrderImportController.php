<?php

namespace App\Http\Controllers;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\Purchase\PurchaseOrderImportHelper;
use App\Http\Requests\PurchaseOrderImportRequest;
use App\Http\Requests\PurchaseOrderItemImportRequest;
use App\Imports\PurchaseOrderShufabImport;
use App\Models\PurchaseOrderImport;
use App\Imports\PurchaseOrderImport as POI;
use App\Models\PurchaseOrderImportShufab;
use App\Models\SoItemImport;
use DB;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class PurchaseOrderImportController extends Controller
{
    public function import(Request $request)
    {
        $orderType = ConstantHelper::PO_SERVICE_ALIAS;
        request() -> merge(['type' => $orderType]);
        $orderType = ConstantHelper::PO_SERVICE_ALIAS;
        $redirectUrl = route('po.index',['type' => 'purchase-order']);
        //Get the menu 
        $parentUrl = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $soImportFile = PurchaseOrderImportHelper::getPoImports();
        $headersSection = PurchaseOrderImportHelper::getPoImportHeaders();
        $sampleFile = isset($soImportFile['v1']) ? $soImportFile['v1'] : '';
        $headers = isset($headersSection['v1']) ? $headersSection['v1'] : '';
        if (!($sampleFile) || !($headers)) {
            return redirect() -> route('/');
        }
        $user = Helper::getAuthenticatedUser();
        $books = Helper::getBookSeriesNew($orderType, $parentUrl) -> get();
        $stores = InventoryHelper::getAccessibleLocations(ConstantHelper::STOCKK);
        $data = [
            'series' => $books,
            'type' => $orderType,
            'user' => $user,
            'books' => $books,
            'stores' => $stores,
            'services' => $servicesBooks['services'],
            'sampleFile' => $sampleFile,
            'headers' => $headers,
            'redirectUrl' => $redirectUrl
        ];
        return view('po.import', $data);
    }

    //Import Save
    public function importSave(PurchaseOrderImportRequest $request)
    {
        DB::beginTransaction();
        try {
            $bookId = (int) $request -> book_id;
            $locationId = (int) $request -> location_id;
            $procurementType = (string) $request -> procurement_type;
            $user = Helper::getAuthenticatedUser();
            $uploads = [];
            PurchaseOrderImport::where('created_by', $user->auth_user_id)->delete();
            Excel::import(new POI($bookId, $locationId, $user -> auth_user_id, $procurementType), $request->file('attachment'));
            $uploads = PurchaseOrderImport::where('created_by', $user -> auth_user_id) -> where('is_migrated', "0") -> get();
            $response = PurchaseOrderImportHelper::generateValidInvalidUi('v1', $uploads);
            DB::commit();
        
            return response() -> json([
                'data' => $response
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while reading the file. Please download the Sample File and try again',
                'error' => $e -> getMessage(),
            ], 500);
        }
    }

    public function bulkUploadOrders(Request $request,string $version="v1")
    {
        DB::beginTransaction();
        try {
            $bookId = (int) $request -> book_id;
            $locationId = (int) $request -> location_id;
            $procurementType = (string) $request -> procurement_type;
            $documentStatus = $request -> document_status;
            $user = Helper::getAuthenticatedUser();
            if ($version == "v1") { //Shufab
                $uploads = PurchaseOrderImport::where('created_by', $user -> auth_user_id) -> where('is_migrated', "0") -> get();
                $response = PurchaseOrderImportHelper::v2ImportDataSave($uploads, $bookId, $locationId, $procurementType,$user, $documentStatus);
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Invalid import version',
                ], 422);
            }
            if($response['status'] != 200) {
                DB::rollBack();
                return response()->json([
                    'message' => $response['message']
                ], $response['status']);
            }
            DB::commit();
            return response() -> json([
                'message' => $response['message']
            ], $response['status']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while uploading documents',
                'error' => $e -> getMessage(),$e -> getLine(),$e -> getFile(),
            ], 500);
        }
    }

    public function importSaveItem(PurchaseOrderImportRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $uploads = [];
            SoItemImport::where('created_by', $user->auth_user_id)->delete();
            Excel::import(new \App\Imports\Sales\SalesOrderItemImport($user -> auth_user_id), $request->file('attachment'));
            $uploads = SoItemImport::where('created_by', $user -> auth_user_id) -> where('is_migrated', "0") -> get();
            $response = PurchaseOrderImportHelper::generateValidInvalidUiItem($uploads);
            DB::commit();
            return response() -> json([
                'data' => $response
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while reading the file. Please download the Sample File and try again',
                'error' => $e ->getLine(),
            ], 500);
        }
    }

    public function bulkUploadItems(Request $request)
    {
        try {
            $user = Helper::getAuthenticatedUser();
            $uploads = SoItemImport::with('item.alternateUOMs.uom')->where('created_by', $user -> auth_user_id) -> whereJsonLength('reason', 0) -> where('is_migrated', "0") -> get();
            return response() -> json([
                'data' => $uploads
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Error occurred while uploading documents',
                'error' => $e -> getMessage(),
            ], 500);
        }
    }
}
