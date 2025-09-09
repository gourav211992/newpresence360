<?php

namespace App\Lib\Services\WHM;

use App\Helpers\CommonHelper;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
use Illuminate\Support\Str;

class WhmJob
{
    // protected $referenceNo;  
    // protected $referenceHeader; 
    // protected $storeId; 
    // protected $subStoreId; 

    // public function createJob($id, $namespace, $jobType = null, $subStoreType = null)
    // {
    //     // Step 1: Get Header
    //     $header = app($namespace)::findOrFail($id);
    //     $morphableType = $namespace;
    //     $morphableId = $header->id;
    //     $referenceType = null;
    //     $referenceId = null;
    //     $this->referenceNo = null;
    //     $this->referenceHeader = $header;
    //     $this->storeId = isset($header->store_id) ? $header->store_id : null;
    //     $this->subStoreId = isset($header->sub_store_id) ? $header->sub_store_id : null;

    //     // ✅ Conditionally skip MRN headers with no is_inspection = 0
    //     if ($namespace === \App\Models\MrnHeader::class) {
    //         $hasInspectionItems = $header->items()->where('is_inspection', 0)->exists();
    //         if (!$hasInspectionItems) {
    //             return; // ⛔ No job creation
    //         }
    //     }

    //     if ($namespace == \App\Models\ErpPlHeader::class) {
    //         $this->subStoreId = isset($header->main_sub_store_id) ? $header->main_sub_store_id : null;
    //     }

    //     if ($namespace === \App\Models\InspectionHeader::class) {
    //         $referenceType = ConstantHelper::INSPECTION_SERVICE_ALIAS;
    //         $referenceId = $header->id;
    //         $this->referenceNo = $header->book_code.'-'.$header->doc_no;

    //         if($subStoreType === 'rejected_store'){
    //             $this->subStoreId = isset($header->rejected_sub_store_id) ? $header->rejected_sub_store_id : null;
    //         } else{
    //             $this->subStoreId = isset($header->sub_store_id) ? $header->sub_store_id : null;
    //         }
            
    //         $namespace = \App\Models\MrnHeader::class;
    //         $id = $header->mrn_header_id;
    //         $header = app($namespace)::findOrFail($id);
    //     }

    //     $type = $jobType ?? CommonHelper::getJobType($namespace);
    //     $trnstype = CommonHelper::getJobTransactionType($namespace);

    //     // Step 2: Get or Create Job (prevents duplicate job on edit)
    //     $job = ErpWhmJob::firstOrCreate(
    //         [
    //             'morphable_type' => $namespace,
    //             'morphable_id' => $header->id,
    //             'type' => $type,
    //         ],
    //         [
    //             'organization_id' => $header->organization_id,
    //             'group_id' => $header->group_id,
    //             'company_id' => $header->company_id,
    //             'status' => 'pending',
    //             'trns_type' => $trnstype,
    //             'store_id' => $this->storeId ?? null,
    //             'sub_store_id' => $this->subStoreId ?? null,
    //             'reference_type' => $referenceType,
    //             'reference_id' => $referenceId,
    //             'reference_no' => $this->referenceNo,
    //         ]
    //     );

    //     // ❗ Skip unique code generation if it's ErpPlHeader or ErpMaterialIssueHeader
    //     if (in_array($namespace, [\App\Models\ErpPlHeader::class])) {
    //         return;
    //     }

    //     // Skip unique code generation
    //     if ($namespace === \App\Models\ErpMaterialIssueHeader::class && $jobType == CommonHelper::PICKING) {
    //         return;
    //     }

    //     // Step 3: Fetch Details with attributes
    //     if (!method_exists($header, 'items')) {
    //         throw new \Exception("Model does not have 'items' relationship defined.");
    //     }

    //     $detailsQuery = $header->items()->with('attributes');
        
    //     if ($namespace === \App\Models\MrnHeader::class) {
    //         $detailsQuery->where('is_inspection', 0);
    //     }

    //     if($referenceType == ConstantHelper::INSPECTION_SERVICE_ALIAS){
    //         $detailsQuery = InspectionDetail::where('header_id',$referenceId)->with('attributes');
    //     }
        
    //     $details = $detailsQuery->get();

    //     // Step 3: Loop through each detail and create unique item codes
    //     foreach ($details as $detail) {
    //         $detalNamespace = get_class($detail);
    //         $this->generateUniqueQRCodes($header, $job, $detalNamespace, $detail, $type, $trnstype);
    //     }

    // }

    // private function generateUniqueQRCodes($header, $job, $namespace, $detail, $type, $trnstype)
    // {
    //     $attributes = $this->getAttributes($detail);
    //     $qty = intval($detail->inventory_uom_qty);

    //     $batchData = [];
    //     if (in_array($namespace, [\App\Models\MrnDetail::class,\App\Models\InspectionDetail::class])) {
    //         $batchData = $detail->batches()
    //             ->where('header_id',$this->referenceHeader->id)
    //             ->where('detail_id',$detail->id)
    //             ->where('item_id',$detail->item_id)
    //             ->get();
    //     }

    //     // Check if this is MrnDetail and has gate_entry_detail_id
    //     if ($namespace === \App\Models\MrnDetail::class && isset($detail->gate_entry_detail_id) && $detail->gate_entry_detail_id) {
    //         if ($batchData->count() > 0) {
    //             foreach($batchData as $batch){
    //                 $qty = isset($batch->accepted_inv_uom_qty) & $batch->accepted_inv_uom_qty ? $batch->accepted_inv_uom_qty : intval($batch->inventory_uom_qty);
    //                 $existingQRCodes = $this->getUnloadingQr($detail->geItem, $qty);
    //                 if ($existingQRCodes->count() > 0) {
    //                     $this->copyQrCodes($existingQRCodes,$detail, $header, $job, $namespace, $attributes, $type, CommonHelper::PENDING, CommonHelper::RECEIPT, $trnstype, $batch);
    //                 }else {
    //                     // ❗ Fall back to fresh QR code creation
    //                     $this->createUniqueCode($header, $job, $namespace, $detail, $attributes, $type, $trnstype, $batch, $qty);
    //                 }
    //             }

    //             return; // Exit here so fresh creation logic is not executed for MRN
    //         }

    //         $existingQRCodes = $this->getUnloadingQr($detail->geItem, $qty);
    //         if ($existingQRCodes->count() > 0) {
    //             $this->copyQrCodes($existingQRCodes,$detail, $header, $job, $namespace, $attributes, $type, CommonHelper::PENDING, CommonHelper::RECEIPT, $trnstype, NULL);
    //             return; // exit after copying
    //         }
    //     }

    //     // Check if this is InspectionDetail and has mrn_header_id
    //     if ($namespace === \App\Models\InspectionDetail::class && isset($detail->mrn_detail_id) && $detail->mrn_detail_id) {
    //         $mrnDetail = MrnDetail::find($detail->mrn_detail_id);
    //         if (isset($mrnDetail->gate_entry_detail_id) && $mrnDetail->gate_entry_detail_id) {

    //             if ($batchData->count() > 0) {
    //                 foreach($batchData as $batch){
    //                     $qty = isset($batch->accepted_inv_uom_qty) & $batch->accepted_inv_uom_qty ? $batch->accepted_inv_uom_qty : intval($batch->inventory_uom_qty);
    //                     $existingQRCodes = $this->getUnloadingQr($mrnDetail->geItem, $qty);
    //                     if ($existingQRCodes->count() > 0) {
    //                         $this->copyQrCodes($existingQRCodes,$detail, $header, $job, $namespace, $attributes, $type, CommonHelper::PENDING, CommonHelper::RECEIPT, $trnstype, $batch);
    //                     }else{
    //                         $this->createUniqueCode($header, $job, $namespace, $detail, $attributes, $type, $trnstype, $batch, $qty);
    //                     }
    //                 }

    //                 return; // Exit here so fresh creation logic is not executed for MRN
    //             }


    //             $existingQRCodes = $this->getUnloadingQr($mrnDetail->geItem, $qty);
    //             if ($existingQRCodes->count() > 0) {
    //                 $this->copyQrCodes($existingQRCodes,$detail, $header, $job, $namespace, $attributes, $type, CommonHelper::PENDING, CommonHelper::RECEIPT, $trnstype, NULL);
    //                 return; // exit after copying
    //             }
    //         }
    //     }

    //     // Check if this is ErpInvoiceItem and has pl_item_id
    //     if ($namespace === \App\Models\ErpInvoiceItem::class && isset($detail->pl_item_id) && $detail->pl_item_id) {
    //         // $qty = intval($detail->order_qty);
    //         $existingQRCodes = $this->getPickingQr($detail->plItem, $qty);
    //         $this->copyQrCodes($existingQRCodes,$detail, $header, $job, $namespace, $attributes, $type, CommonHelper::PENDING, CommonHelper::ISSUE, $trnstype, NULL);
    //         return; // exit after copying
    //     }

    //     // Check if this is MrnDetail and has gate_entry_detail_id
    //     if ($namespace === \App\Models\ErpMiItem::class && $job == CommonHelper::DISPATCH) {
    //         $existingQRCodes = $this->getPickingQr($detail, $qty);
    //         $this->copyQrCodes($existingQRCodes,$detail, $header, $job, $namespace, $attributes, $type, CommonHelper::PENDING, CommonHelper::RECEIPT);
    //         return; // exit after copying
    //     }
        
    //     if (in_array($namespace, [\App\Models\MrnDetail::class,\App\Models\InspectionDetail::class])) {
    //         if ($batchData->count() > 0) {
    //             foreach($batchData as $batch){
    //                 $qty = isset($batch->accepted_inv_uom_qty) & $batch->accepted_inv_uom_qty ? $batch->accepted_inv_uom_qty : intval($batch->inventory_uom_qty);
    //                 $this->createUniqueCode($header, $job, $namespace, $detail, $attributes, $type, $trnstype, $batch,$qty);
    //             }

    //             return; // Exit here so fresh creation logic is not executed for MRN
    //         }
    //     }

    //     // ❗ Fresh creation logic (same as before)
    //     $existingCount = $detail->uniqueCodes()->where('job_id', $job->id)
    //         ->count();
    //     if ($qty > $existingCount) {
    //         $diff = $qty - $existingCount;
    //         $this->createUniqueCode($header, $job, $namespace, $detail, $attributes, $type, $trnstype, NULL, $diff);
    //     } elseif ($qty < $existingCount) {
    //         $diff = $existingCount - $qty;

    //         ErpItemUniqueCode::where('job_id', $job->id)
    //             ->where('item_id', $detail->item_id)
    //             ->where('morphable_type', $namespace)
    //             ->where('morphable_id', $detail->id)
    //             ->where('status', 'pending')
    //             ->orderBy('id', 'desc')
    //             ->limit($diff)
    //             ->delete();
    //     }
    // }

    // private function createUniqueCode($header, $job, $namespace, $detail, $attributes, $type, $trnstype, $batch, $qty){
    //     $records = [];
    //     $uid = null;
    //     $referenceType = null;
    //     $referenceDetailId = null;

    //     $morphableType = $namespace;
    //     $morphableId = $detail->id;

    //     if ($namespace === \App\Models\InspectionDetail::class){
    //         $morphableType = \App\Models\MrnDetail::class;
    //         $morphableId = $detail->mrn_detail_id;
    //         $referenceType = ConstantHelper::INSPECTION_SERVICE_ALIAS;
    //         $referenceDetailId = $detail->id;
    //     }

    //     for ($i = 0; $i < $qty; $i++) {
    //         //Preserve the UID if present and qty is 1
    //         // if ($qty === 1 && isset($detail -> item_uid) && $detail -> item_uid) {
    //         //     $uid = $detail -> item_uid;
    //         // } else {
    //             // $uid = $this -> generateUniqueUid();
    //         // }
    //         $records[] = [
    //             'uid' => $this->generateUniqueUid(),
    //             'job_id' => $job->id,
    //             'organization_id' => $header->organization_id,
    //             'group_id' => $header->group_id,
    //             'company_id' => $header->company_id,
    //             'morphable_type' => $morphableType,
    //             'morphable_id' => $morphableId,
    //             'job_type' => $type,
    //             'trns_type' => $trnstype,
    //             'doc_type' => CommonHelper::RECEIPT,
    //             'doc_no' => $header->document_number ?? null,
    //             'doc_date' => $header->document_date ?? null,
    //             'book_id' => $header->book_id ?? null,
    //             'store_id' => $this->storeId ?? null,
    //             'sub_store_id' => $this->subStoreId ?? null,
    //             'book_code' => $header->book_code ?? null,
    //             'item_attributes' => json_encode($attributes),
    //             'item_id' => $detail->item_id,
    //             'item_name' => $detail->item->item_name,
    //             'item_code' => $detail->item_code,
    //             'vendor_id' => $header->vendor_id,
    //             'item_uid' => $this->generateUniqueUid(),
    //             'batch_id' => $batch ? $batch->id : NULL,
    //             'batch_number' => $batch ? $batch->batch_number : NULL,
    //             'manufacturing_year' => $batch ? ($batch->manufacturing_year == 0 ? NULL : $batch->manufacturing_year) : NULL,
    //             'expiry_date' => $batch ? ($batch->expiry_date ? date('Y-m-d',strtotime($batch->expiry_date)) : NULL) : NULL,
    //             'type' => 'qr',
    //             'qty' => 1,
    //             'status' => 'pending',
    //             'reference_type' => $referenceType,
    //             'reference_detail_id' => $referenceDetailId,
    //             'reference_no' => $this->referenceNo,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ];
    //     }

    //     foreach (array_chunk($records, 500) as $chunk) {
    //         ErpItemUniqueCode::insert($chunk);
    //     }

    // }

    // private function getUnloadingQr($geDetail, $qty)
    // {
    //     $existingGateQRCodes = $geDetail->uniqueCodes()->where('status', CommonHelper::SCANNED)
    //         ->whereNull('utilized_id')
    //         ->limit($qty)
    //         ->get();

    //     return $existingGateQRCodes;
    // }

    // private function getPickingQr($plItem, $qty)
    // {
    //     $existingQRCodes = $plItem->uniqueCodes()->where('status', CommonHelper::SCANNED)
    //         ->whereNull('utilized_id')
    //         ->limit($qty)
    //         ->get();

    //     return $existingQRCodes;
    // }

    // private function copyQrCodes($existingQRCodes,$detail, $header, $job, $namespace, $attributes, $type, $status, $docType = CommonHelper::RECEIPT, $trnstype, $batch){
    //     $morphableType = $namespace;
    //     $morphableId = $detail->id;
    //     $referenceType = null;
    //     $referenceDetailId = null;

    //     if ($namespace === \App\Models\InspectionDetail::class){
    //         $morphableType = \App\Models\MrnDetail::class;
    //         $morphableId = $detail->mrn_detail_id;
    //         $referenceType = ConstantHelper::INSPECTION_SERVICE_ALIAS;
    //         $referenceDetailId = $detail->id;
    //     }

    //     foreach ($existingQRCodes as $code) {
    //         $newRecord = ErpItemUniqueCode::create([
    //             'uid' => $this->generateUniqueUid(),
    //             'job_id' => $job->id,
    //             'organization_id' => $header->organization_id,
    //             'group_id' => $header->group_id,
    //             'company_id' => $header->company_id,
    //             'morphable_type' => $morphableType,
    //             'morphable_id' => $morphableId,
    //             'job_type' => $type,
    //             'trns_type' => $trnstype,
    //             'doc_type' => $docType,
    //             'doc_no' => $header->document_number ?? null,
    //             'doc_date' => $header->document_date ?? null,
    //             'book_id' => $header->book_id ?? null,
    //             'store_id' => $this->storeId ?? null,
    //             'sub_store_id' => $this->subStoreId ?? null,
    //             'book_code' => $header->book_code ?? null,
    //             'item_attributes' => json_encode($attributes),
    //             'item_id' => $detail->item_id,
    //             'item_name' => $detail->item->item_name,
    //             'item_code' => $detail->item_code,
    //             'vendor_id' => isset($header->vendor_id) ? $header->vendor_id : NULL,
    //             'item_uid' => $code->item_uid, 
    //             'batch_id' => $batch ? $batch->id : NULL,
    //             'batch_number' => $batch ? $batch->batch_number : NULL,
    //             'manufacturing_year' => $batch ? ($batch->manufacturing_year == 0 ? NULL : $batch->manufacturing_year) : NULL,
    //             'expiry_date' => $batch ? ($batch->expiry_date ? date('Y-m-d',strtotime($batch->expiry_date)) : NULL) : NULL,
    //             'type' => 'qr',
    //             'qty' => 1,
    //             'status' => $status,
    //             'reference_type' => $referenceType,
    //             'reference_detail_id' => $referenceDetailId,
    //             'reference_no' => $this->referenceNo,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //         ]);

    //         $code->utilized_id = $newRecord->uid;
    //         $code->save();
    //     }
    // }

    // private function getAttributes($detail){
    //     $attributeJsonArray = [];
    //     if(isset($detail->attributes) && !empty($detail->attributes)){
    //         foreach($detail->attributes as $key1 => $attribute) {
    //             $attributeJsonArray[] = [
    //                 "attr_name" => (string)$attribute->attr_name,
    //                 "attribute_name" => (string)@$attribute->attributeName->name,
    //                 "attr_value" => (string)@$attribute->attr_value,
    //                 "attribute_value" => (string)@$attribute->attributeValue->value,
    //             ];
    //         }
    //     }

    //     return $attributeJsonArray;
    // }

    public function createJob($header, $morphableType, $jobType, $trnstype, $storeId, $subStoreId = NULL, $referenceType = NULL, $referenceId = NULL, $referenceNo = NULL){
        $job = ErpWhmJob::firstOrCreate(
            [
                'morphable_type' => $morphableType,
                'morphable_id' => $header->id,
                'type' => $jobType,
            ],
            [
                'organization_id' => $header->organization_id,
                'group_id' => $header->group_id,
                'company_id' => $header->company_id,
                'status' => CommonHelper::PENDING,
                'trns_type' => $trnstype,
                'store_id' => $storeId,
                'sub_store_id' => $subStoreId,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reference_no' => $referenceNo,
            ]
        );

        return $job;
    }

    public function createUniqueCode($header, $job, $morphableType, $morphableId, $detail, $attributes, $jobType, $trnsType, $qty, $docType, $storeId, $subStoreId = NULL, $referenceType = NULL, $referenceDetailId = NULL, $referenceNo = NULL, $totalPacket, $batch = NULL){
        $records = [];

        for ($packet = 1; $packet <= $totalPacket; $packet++) {
            for ($i = 0; $i < $qty; $i++) {
                $itemUid = $totalPacket > 1 ? $this->generateUniqueUid(). '-' . $packet : $this->generateUniqueUid();
                $packetNo = $totalPacket > 1 ? $packet : $totalPacket;
                $records[] = [
                    'uid' => $this->generateUniqueUid(),
                    'job_id' => $job->id,
                    'organization_id' => $job->organization_id,
                    'group_id' => $job->group_id,
                    'company_id' => $job->company_id,
                    'morphable_type' => $morphableType,
                    'morphable_id' => $morphableId,
                    'job_type' => $jobType,
                    'trns_type' => $trnsType,
                    'doc_type' => $docType,
                    'doc_no' => $header->document_number ?? null,
                    'doc_date' => $header->document_date ?? null,
                    'book_id' => $header->book_id ?? null,
                    'store_id' => $storeId ?? null,
                    'sub_store_id' => $subStoreId ?? null,
                    'book_code' => $header->book_code ?? null,
                    'item_attributes' => json_encode($attributes),
                    'item_id' => $detail->item_id,
                    'item_name' => $detail->item->item_name,
                    'item_code' => $detail->item_code,
                    'vendor_id' => $header->vendor_id,
                    'item_uid' => $itemUid,
                    'packet_no' => $packetNo,
                    'total_packets' => $totalPacket,
                    'type' => 'qr',
                    'qty' => 1,
                    'status' => CommonHelper::PENDING,
                    'batch_id' => $batch ? $batch->id : NULL,
                    'batch_number' => $batch ? $batch->batch_number : NULL,
                    'manufacturing_year' => $batch ? ($batch->manufacturing_year == 0 ? NULL : $batch->manufacturing_year) : NULL,
                    'expiry_date' => $batch ? ($batch->expiry_date ? date('Y-m-d',strtotime($batch->expiry_date)) : NULL) : NULL,
                    'reference_type' => $referenceType,
                    'reference_detail_id' => $referenceDetailId,
                    'reference_no' => $referenceNo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($records, 500) as $chunk) {
            ErpItemUniqueCode::insert($chunk);
        }
    }


    public function copyExistingQrCodes($existingQRCodes, $job, $header, $morphableType, $morphableId, $jobType, $trnstype, $docType, $storeId, $subStoreId, $batch = NULL, $referenceType = NULL, $referenceDetailId = NULL, $referenceNo = NULL, $status = CommonHelper::PENDING){
        foreach ($existingQRCodes as $code) {
            $newRecord = ErpItemUniqueCode::create([
                'uid' => $this->generateUniqueUid(),
                'job_id' => $job->id,
                'organization_id' => $job->organization_id,
                'group_id' => $job->group_id,
                'company_id' => $job->company_id,
                'morphable_type' => $morphableType,
                'morphable_id' => $morphableId,
                'job_type' => $jobType,
                'trns_type' => $trnstype,
                'doc_type' => $docType,
                'doc_no' => $header->document_number ?? null,
                'doc_date' => $header->document_date ?? null,
                'book_id' => $header->book_id ?? null,
                'store_id' => $storeId,
                'sub_store_id' => $subStoreId,
                'book_code' => $header->book_code ?? null,
                'item_attributes' => json_encode($code->item_attributes),
                'item_id' => $code->item_id,
                'item_name' => $code->item_name,
                'item_code' => $code->item_code,
                'vendor_id' => $code->vendor_id,
                'item_uid' => $code->item_uid, 
                'packet_no' => $code->packet_no,
                'total_packets' => $code->total_packets,
                'batch_id' => $batch ? $batch->id : $code->batch_id,
                'batch_number' => $batch ? $batch->batch_number : $code->batch_number,
                'manufacturing_year' => $batch ? ($batch->manufacturing_year == 0 ? NULL : $batch->manufacturing_year) : $code->manufacturing_year,
                'expiry_date' => $batch ? ($batch->expiry_date ? date('Y-m-d',strtotime($batch->expiry_date)) : NULL) : $code->expiry_date,
                'serial_no' => $code->serial_no,
                'type' => 'qr',
                'qty' => 1,
                'status' => $status,
                'reference_type' => $referenceType,
                'reference_detail_id' => $referenceDetailId,
                'reference_no' => $referenceNo,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $code->utilized_id = $newRecord->uid;
            $code->save();
        }
    }

    public function deleteUniqueCode($job, $detailId, $totalPacket, $qty){
        for ($packet = 1; $packet <= $totalPacket; $packet++) {
            $items = $job->itemUniqueCodes()
                ->where('morphable_id',$detailId)
                ->where('status', 'pending')
                ->where('packet_no', $packet)
                ->limit($qty)
                ->get();

            if($items->isEmpty()){
                $items = $job->itemUniqueCodes()
                ->where('morphable_id',$detailId)
                ->where('status', 'scanned')
                ->whereNull('utilized_id')
                ->where('packet_no', $packet)
                ->limit($qty)
                ->get();
            }

            if ($items->isNotEmpty()) {
                ErpItemUniqueCode::whereIn('id', $items->pluck('id'))->delete();

            }
        }
    }

    public function generateUniqueUid($length = 15)
    {
        $raw = str_replace('-', '', Str::uuid()); // 15-character hex
        $uid = strtoupper(substr($raw, 0, $length)); // Alphanumeric only, uppercase
        return $uid;
    }

    // public function generateQRCodesForPickList($detail, $header, $jobId, $packetIds, $storagePointId, $userId, $jobType, $trnstype)
    // {
    //     $attributes = $detail->attributes;

    //     $packets = ErpItemUniqueCode::whereIn('item_uid', $packetIds)
    //         ->where('storage_point_id',$storagePointId)
    //         ->whereNull('utilized_id')
    //         ->whereIn('trns_type', $trnstype)
    //         ->where('status', CommonHelper::SCANNED)
    //         ->get();
    //     // dd($packets);

    //     $namespace = get_class($detail);
    //     $storeId = $header->store_id;
    //     $subStoreId = $header->main_sub_store_id;
    //     // if ($trnstype == ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME) {
    //     //     $storeId = $header -> to_store_id;
    //     //     $subStoreId = $header -> to_sub_store_id;
    //     // } 

        
    //     foreach ($packets as $packet) {
    //         $newRecord = ErpItemUniqueCode::create([
    //             'uid' => $this->generateUniqueUid(),
    //             'job_id' => $jobId,
    //             'organization_id' => $header->organization_id,
    //             'group_id' => $header->group_id,
    //             'company_id' => $header->company_id,
    //             'morphable_type' => $namespace,
    //             'morphable_id' => $detail->id,
    //             'job_type' => $jobType,
    //             'trns_type' => ConstantHelper::PL_SERVICE_ALIAS,
    //             'doc_type' => CommonHelper::RECEIPT,
    //             'doc_no' => $header->document_number ?? null,
    //             'doc_date' => $header->document_date ?? null,
    //             'book_id' => $header->book_id ?? null,
    //             'store_id' =>$storeId ?? null,
    //             'sub_store_id' => $subStoreId ?? null,
    //             'book_code' => $header->book_code ?? null,
    //             'item_attributes' => json_encode($attributes),
    //             'item_id' => $detail->item_id,
    //             'item_name' => $detail->item->item_name,
    //             'item_code' => $detail->item_code,
    //             'vendor_id' => $header->vendor_id,
    //             'item_uid' => $packet->item_uid, 
    //             'storage_point_id' => Null, 
    //             'type' => 'qr',
    //             'qty' => 1,
    //             'status' => CommonHelper::SCANNED,
    //             'created_at' => now(),
    //             'updated_at' => now(),
    //             'action_by' => $userId,
    //             'action_at' => now()
    //         ]);

    //         $packet->utilized_id = $newRecord->uid;
    //         $packet->save();
    //     }
    // }

    public function binTransfer($items, $storagePointId, $userId){
        foreach($items as $item){
            $newRecord = ErpItemUniqueCode::create([
                'uid' => $this->generateUniqueUid(),
                'job_id' => $item->job_id,
                'organization_id' => $item->organization_id,
                'group_id' => $item->group_id,
                'company_id' => $item->company_id,
                'morphable_type' => $item->morphable_type,
                'morphable_id' => $item->morphable_id,
                'job_type' => CommonHelper::TRANSFERRED,
                'trns_type' => $item->trns_type,
                'doc_type' => $item->doc_type,
                'doc_no' => $item->doc_no,
                'doc_date' => $item->doc_date,
                'book_id' => $item->book_id,
                'store_id' => $item->store_id,
                'sub_store_id' => $item->sub_store_id,
                'book_code' => $item->book_code,
                'item_attributes' => json_encode($item->item_attributes),
                'item_id' => $item->item_id,
                'item_name' => $item->item_name,
                'item_code' => $item->item_code,
                'vendor_id' => $item->vendor_id,
                'item_uid' => $item->item_uid,
                'type' => $item->type,
                'qty' => $item->qty,
                'status' => $item->status,
                'storage_point_id' => $storagePointId,
                'created_at' => now(),
                'updated_at' => now(),
                'action_by' => $userId,
                'action_at' => now()
            ]);

            $item->utilized_id = $newRecord->uid;
            // $item->status = CommonHelper::TRANSFERRED;
            $item->save();
        }
    }
}