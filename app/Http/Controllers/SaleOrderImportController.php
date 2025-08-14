<?php

namespace App\Http\Controllers;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\Sales\ImportHelper;
use App\Http\Requests\SaleOrderImportRequest;
use App\Http\Requests\SaleOrderItemImportRequest;
use App\Imports\SaleOrderShufabImport;
use App\Models\SaleOrderImport;
use App\Models\SaleOrderImportShufab;
use App\Models\SoItemImport;
use DB;
use Exception;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class SaleOrderImportController extends Controller
{
    public function import(Request $request, string $version)
    {
        $orderType = ConstantHelper::SO_SERVICE_ALIAS;
        $redirectUrl = route('sale.order.index');
        request() -> merge(['type' => $orderType]);
        $orderType = ConstantHelper::SO_SERVICE_ALIAS;
        //Get the menu 
        $parentUrl = request() -> segments()[0];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentUrl);
        if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $soImportFile = ImportHelper::getSoImports();
        $headersSection = ImportHelper::getSoImportHeaders();
        $sampleFile = isset($soImportFile[$version]) ? $soImportFile[$version] : '';
        $headers = isset($headersSection[$version]) ? $headersSection[$version] : '';
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
        return view('salesOrder.import', $data);
    }

    //Import Save
    public function importSave(SaleOrderImportRequest $request, string $version)
    {
        DB::beginTransaction();
        try {
            $bookId = (int) $request -> book_id;
            $locationId = (int) $request -> location_id;
            $user = Helper::getAuthenticatedUser();
            $uploads = [];
            if ($version == "v1") { //Shufab
                SaleOrderImportShufab::where('created_by', $user->auth_user_id)->delete();
                Excel::import(new \App\Imports\Sales\SaleOrderShufabImport($bookId, $locationId, $user -> auth_user_id), $request->file('attachment'));
                $uploads = SaleOrderImportShufab::where('created_by', $user -> auth_user_id) -> where('is_migrated', "0") -> get();
                $response = ImportHelper::generateValidInvalidUi($version, $uploads);
                DB::commit();
            } else if ($version == "v2") { //Common
                SaleOrderImport::where('created_by', $user->auth_user_id)->delete();
                Excel::import(new \App\Imports\Sales\SaleOrderImportV2($bookId, $locationId, $user -> auth_user_id), $request->file('attachment'));
                $uploads = SaleOrderImport::where('created_by', $user -> auth_user_id) -> where('is_migrated', "0") -> get();
                $response = ImportHelper::generateValidInvalidUi($version, $uploads);
                DB::commit();
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Invalid import version',
                ], 422);
            }
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

    public function bulkUploadOrders(Request $request, string $version)
    {
        DB::beginTransaction();
        try {
            $bookId = (int) $request -> book_id;
            $locationId = (int) $request -> location_id;
            $documentStatus = $request -> document_status;
            $user = Helper::getAuthenticatedUser();
            if ($version == "v1") { //Shufab
                $uploads = SaleOrderImportShufab::where('created_by', $user -> auth_user_id) -> where('is_migrated', "0") -> get();
                $response = ImportHelper::shufabImportDataSave($uploads, $bookId, $locationId, $user, $documentStatus);
            } else if ($version == 'v2') {
                $uploads = SaleOrderImport::where('created_by', $user -> auth_user_id) -> where('is_migrated', "0") -> get();
                $response = ImportHelper::v2ImportDataSave($uploads, $bookId, $locationId, $user, $documentStatus);
            } else {
                DB::rollBack();
                return response()->json([
                    'message' => 'Invalid import version',
                ], 422);
            }
            DB::commit();
            return response() -> json([
                'message' => $response['message']
            ], $response['status']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while uploading documents',
                'error' => $e -> getMessage(),
            ], 500);
        }
    }

    public function importSaveItem(SaleOrderItemImportRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            $uploads = [];
            SoItemImport::where('created_by', $user->auth_user_id)->delete();
            Excel::import(new \App\Imports\Sales\SalesOrderItemImport($user -> auth_user_id), $request->file('attachment'));
            $uploads = SoItemImport::where('created_by', $user -> auth_user_id) -> where('is_migrated', "0") -> get();
            $response = ImportHelper::generateValidInvalidUiItem($uploads);
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
