<?php

namespace App\Http\Controllers\WHM;

use App\Exceptions\ApiGenericException;
use App\Helpers\CommonHelper;
use App\Helpers\Helper;
use App\Helpers\StoragePointHelper;
use App\Http\Controllers\Controller;
use App\Lib\Services\WHM\WhmJob;
use App\Models\WHM\ErpItemUniqueCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use DB;

class BinTransferController extends Controller
{
    public function index(Request $request){
        $validator = Validator::make($request->all(),[
            'storage_number' => ['required'],
        ],[
            'storage_number.required' => 'Storage number is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $storeId = $request->input('store_id');
        $subStoreId = $request->input('sub_store_id');
        $storageNumber = $request->storage_number;

        $storagePointDetail = StoragePointHelper::getStoragePointDetail($storageNumber);
        if($storagePointDetail['status'] == "error"){
            throw ValidationException::withMessages([
                'storage_point_id' => $storagePointDetail['message'],
            ]);
        }

        $storageData = $storagePointDetail['data'];

        $items = ErpItemUniqueCode::where('storage_point_id',$storageData->id)
            ->where('doc_type',CommonHelper::RECEIPT)
            ->when($storeId, function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            })
            ->when($subStoreId, function ($query) use ($subStoreId) {
                $query->where('sub_store_id', $subStoreId);
            })
            ->whereNull('utilized_id')
            ->whereNotNull('storage_point_id')
            ->select('item_id','item_code','item_name','item_attributes',DB::raw('COUNT(*) as quantity'))
            ->groupBy('item_id')
            ->get();
        
            return [
                "data" => [
                    'storage_point' => $storageData,
                    'items' => $items
                ]
            ];
    }

    public function binTransfer(Request $request){
        $validator = Validator::make($request->all(),[
            'item_ids' => ['required', 'array'],
            'from_storage_number' => ['required'],
            'to_storage_number' => ['required'],
        ],[
            'item_ids.required' => 'Item IDs are required',
            'from_storage_number.required' => 'Storage number id is required',
            'to_storage_number.required' => 'Storage number id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $fromStoragePointDetail = StoragePointHelper::getStoragePointDetail($request->from_storage_number);
        if($fromStoragePointDetail['status'] == "error"){
            throw ValidationException::withMessages([
                'from_storage_number' => $fromStoragePointDetail['message'],
            ]);
        }

        $toStoragePointDetail = StoragePointHelper::getStoragePointDetail($request->to_storage_number);
        if($toStoragePointDetail['status'] == "error"){
            throw ValidationException::withMessages([
                'to_storage_number' => $toStoragePointDetail['message'],
            ]);
        }

        if ($request->from_storage_number == $request->to_storage_number) {
            throw ValidationException::withMessages([
                'to_storage_number' => 'From and To storage point cannot be the same.',
            ]);
        }

        $fromStoragePoint = $fromStoragePointDetail['data'];
        $toStoragePoint = $toStoragePointDetail['data'];

        $items = ErpItemUniqueCode::where('storage_point_id',$fromStoragePoint->id)
            ->where('doc_type',CommonHelper::RECEIPT)
            ->whereIn('item_id',$request->item_ids)
            ->whereNull('utilized_id')
            ->whereNotNull('storage_point_id')
            ->get();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'item_ids' => 'No valid items found for transfer.',
            ]);
        }

        $itemIds = $items->pluck('item_id')->unique()->toArray();
        foreach ($itemIds as $itemId) {
            $response = StoragePointHelper::getStoragePoints(
                $itemId,
                null,
                $fromStoragePoint->store_id,
                $fromStoragePoint->sub_store_id
            );

            $storageNumbers = $response['data']->pluck('storage_number')->toArray();
            // dd($fromStoragePoint,$toStoragePoint, $items,$storageNumbers,$itemIds);
            if(!in_array($request->to_storage_number,$storageNumbers)){
                throw ValidationException::withMessages([
                    'to_storage_number' => "Storage point is not mapped to item ID: {$itemId}",
                ]);

            }
        }

        \DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();
            (new WhmJob())->binTransfer($items, $toStoragePoint->id, $user->id);
            \DB::commit();
            return [
                'message' => 'Data transferred successfully.'
            ];
        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }

    }

    public function scanPackets(Request $request){
        $validator = Validator::make($request->all(),[
            'packet_ids' => ['required', 'array'],
            'from_storage_number' => ['required'],
            'to_storage_number' => ['required'],
        ],[
            'packet_ids.required' => 'Packet IDs are required',
            'from_storage_number.required' => 'Storage point is required',
            'to_storage_number.required' => 'Storage point is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $fromStoragePointDetail = StoragePointHelper::getStoragePointDetail($request->from_storage_number);
        if($fromStoragePointDetail['status'] == "error"){
            throw ValidationException::withMessages([
                'from_storage_number' => $fromStoragePointDetail['message'],
            ]);
        }

        $toStoragePointDetail = StoragePointHelper::getStoragePointDetail($request->to_storage_number);
        if($toStoragePointDetail['status'] == "error"){
            throw ValidationException::withMessages([
                'to_storage_number' => $toStoragePointDetail['message'],
            ]);
        }

        if ($request->from_storage_number == $request->to_storage_number) {
            throw ValidationException::withMessages([
                'to_storage_number' => 'From and To storage point cannot be the same.',
            ]);
        }

        $fromStoragePoint = $fromStoragePointDetail['data'];
        $toStoragePoint = $toStoragePointDetail['data'];

        $items = ErpItemUniqueCode::where('storage_point_id',$fromStoragePoint->id)
            ->where('doc_type',CommonHelper::RECEIPT)
            ->whereIn('item_uid',$request->packet_ids)
            ->whereNull('utilized_id')
            ->whereNotNull('storage_point_id')
            ->get();

        if ($items->isEmpty()) {
            throw ValidationException::withMessages([
                'item_ids' => 'No valid items found for transfer.',
            ]);
        }

        $itemIds = $items->pluck('item_uid', 'item_id')->toArray();
        foreach ($itemIds as $itemId => $packetId) {
            $response = StoragePointHelper::getStoragePoints(
                $itemId,
                null,
                $fromStoragePoint->store_id,
                $fromStoragePoint->sub_store_id
            );

            $storageNumbers = $response['data']->pluck('storage_number')->toArray();
            if(!in_array($request->to_storage_number,$storageNumbers)){
                throw ValidationException::withMessages([
                    'to_storage_number' => "Storage point is not mapped to packet ID: {$packetId}",
                ]);

            }

            // $isMapped = StoragePointHelper::isStoragePointMappedToItem(
            //     $itemId,
            //     $toStoragePoint->id,
            //     $toStoragePoint->store_id,
            //     $toStoragePoint->sub_store_id
            // );

            // if (!$isMapped) {
            //     throw ValidationException::withMessages([
            //         'to_storage_number' => "Storage point is not mapped to packet ID: {$packetId}",
            //     ]);
            // }
        }

        \DB::beginTransaction();
        try {
            $user = Helper::getAuthenticatedUser();

            (new WhmJob())->binTransfer($items, $toStoragePoint->id, $user->id);
            
            \DB::commit();
            return [
                'message' => 'Data transferred successfully.'
            ];
        } catch (\Exception $e) {
            \DB::rollback();
            throw new ApiGenericException($e->getMessage());
        }

    }

    public function validateQr(Request $request){
        $validator = Validator::make($request->all(),[
            'packet_id' => ['required'],
            'storage_point_id' => ['required'],
        ],[
            'packet_id.required' => 'Packet ID are required',
            'storage_point_id.required' => 'Storage point id is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $item = ErpItemUniqueCode::where('storage_point_id',$request->storage_point_id)
            ->where('item_uid',$request->packet_id)
            ->where('doc_type',CommonHelper::RECEIPT)
            ->whereNull('utilized_id')
            ->whereNotNull('storage_point_id')
            ->select('uid','job_id','morphable_id as putaway_item_id','group_id','company_id','organization_id','book_code','doc_no','doc_date','status','item_id','item_uid','item_name','item_code','item_attributes','status','vendor_id','batch_number','manufacturing_year','expiry_date','serial_no')
            ->first();

        if (!$item) {
            throw ValidationException::withMessages([
                'packet_id' => 'Invalid packet ID for the given storage point.',
            ]);
        }

        return [
            'message' => 'Packet validated successfully.',
            'data' => $item
        ];
    }

    public function validatePoint(Request $request){
        $validator = Validator::make($request->all(),[
            'storage_number' => ['required'],
            'sub_store_id' => ['required'],
            'store_id' => ['required'],
        ],[
            'storage_number.required' => 'Storage number is required',
            'sub_store_id.required' => 'Sub store is required',
            'store_id.required' => 'Store is required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $storageNumber = $request->input('storage_number');
        $storagePoint = \DB::table('erp_wh_details')
            ->where('sub_store_id',$request->sub_store_id)
            ->where('store_id',$request->store_id)
            ->where('storage_number', $request->storage_number)
            ->first();

        
        if (!$storagePoint) {
            throw ValidationException::withMessages([
                'storage_number' => ['Storage point not found.'],
            ]);
        }

        return [
            'data' => $storagePoint,
            'message' => "Storage point details fetched successfully.", $storageNumber,
        ];
    }
}
