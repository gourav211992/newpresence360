<?php

namespace App\Http\Controllers;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Helpers\SaleModuleHelper;
use App\Http\Requests\SaleOrderImportRequest;
use App\Imports\SaleOrderShufabImport;
use App\Models\SaleOrderImportShufab;
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
        $soImportFile = SaleModuleHelper::getSoImports();
        $sampleFile = isset($soImportFile[$version]) ? $soImportFile[$version] : '';
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
            SaleOrderImportShufab::where('created_by', $user->auth_user_id)->delete();
            Excel::import(new \App\Imports\Sales\SaleOrderShufabImport($bookId, $locationId, $user -> auth_user_id), $request->file('attachment'));
            $uploads = SaleOrderImportShufab::where('created_by', $user -> auth_user_id) -> where('is_migrated', "0") -> get();
            DB::commit();
            return response() -> json([
                'data' => $uploads
            ], 200);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error occurred while reading the file',
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
            $uploads = SaleOrderImportShufab::where('created_by', $user -> auth_user_id) -> where('is_migrated', "0") -> get();
            $response = SaleModuleHelper::shufabImportDataSave($uploads, $bookId, $locationId, $user, $documentStatus);
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
}
