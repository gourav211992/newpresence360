<?php

namespace App\Lib\Services\WHM;

use App\Helpers\CommonHelper;
use App\Helpers\ConstantHelper;
use App\Models\InspectionDetail;
use App\Models\MrnDetail;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
use Illuminate\Support\Str;

class PutawayJob
{
    protected $referenceNo;  
    protected $referenceHeader; 
    protected $storeId; 
    protected $subStoreId; 

    public function createJob($id, $namespace, $jobType = null, $subStoreType = null)
    {
        // Step 1: Get Header
        $header = app($namespace)::findOrFail($id);
        $referenceType = null;
        $referenceId = null;
        $this->referenceNo = null;
        $this->referenceHeader = $header;
        $this->storeId = isset($header->store_id) ? $header->store_id : null;
        $this->subStoreId = isset($header->sub_store_id) ? $header->sub_store_id : null;

        // ✅ Conditionally skip MRN headers with no is_inspection = 0
        if ($namespace === \App\Models\MrnHeader::class) {
            $hasInspectionItems = $header->items()->where('is_inspection', 0)->exists();
            if (!$hasInspectionItems) {
                return; // ⛔ No job creation
            }
        }

        if ($namespace === \App\Models\InspectionHeader::class) {
            $referenceType = ConstantHelper::INSPECTION_SERVICE_ALIAS;
            $referenceId = $header->id;
            $this->referenceNo = $header->book_code.'-'.$header->doc_no;

            if($subStoreType === 'rejected_store'){
                $this->subStoreId = isset($header->rejected_sub_store_id) ? $header->rejected_sub_store_id : null;
            } else{
                $this->subStoreId = isset($header->sub_store_id) ? $header->sub_store_id : null;
            }
            
            $namespace = \App\Models\MrnHeader::class;
            $id = $header->mrn_header_id;
            $header = app($namespace)::findOrFail($id);
        }

        $type = $jobType ?? CommonHelper::getJobType($namespace);
        $trnstype = CommonHelper::getJobTransactionType($namespace);
        
        // Step 2: Get or Create Job (prevents duplicate job on edit)
        $job = ErpWhmJob::firstOrCreate(
            [
                'morphable_type' => $namespace,
                'morphable_id' => $header->id,
                'type' => $type,
            ],
            [
                'organization_id' => $header->organization_id,
                'group_id' => $header->group_id,
                'company_id' => $header->company_id,
                'status' => CommonHelper::PENDING,
                'trns_type' => $trnstype,
                'store_id' => $this->storeId ?? null,
                'sub_store_id' => $this->subStoreId ?? null,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reference_no' => $this->referenceNo,
            ]
        );

        // Step 3: Fetch Details with attributes
        if (!method_exists($header, 'items')) {
            throw new \Exception("Model does not have 'items' relationship defined.");
        }

        $detailsQuery = $header->items()->with('attributes');
        
        if ($namespace === \App\Models\MrnHeader::class) {
            $detailsQuery->where('is_inspection', 0);
        }

        if($referenceType == ConstantHelper::INSPECTION_SERVICE_ALIAS){
            $detailsQuery = InspectionDetail::where('header_id',$referenceId)->with('attributes');
        }
        
        $details = $detailsQuery->get();

        // Step 3: Loop through each detail and create unique item codes
        foreach ($details as $detail) {
            $detalNamespace = get_class($detail);
            $this->generateUniqueQRCodes($header, $job, $detalNamespace, $detail, $type, $trnstype);
        }

    }

    private function generateUniqueQRCodes($header, $job, $namespace, $detail, $type, $trnstype)
    {
        $attributes = $this->getAttributes($detail);
        $qty = intval($detail->inventory_uom_qty);

        $batchData = $detail->batches()
            ->where('header_id',$this->referenceHeader->id)
            ->where('detail_id',$detail->id)
            ->where('item_id',$detail->item_id)
            ->get();
        
        // Check if this is MrnDetail and has gate_entry_detail_id
        if ($namespace === \App\Models\MrnDetail::class && isset($detail->gate_entry_detail_id) && $detail->gate_entry_detail_id) {
            if ($batchData->count() > 0) {
                foreach($batchData as $batch){
                    $qty = isset($batch->accepted_inv_uom_qty) & $batch->accepted_inv_uom_qty ? $batch->accepted_inv_uom_qty : intval($batch->inventory_uom_qty);
                    $existingQRCodes = $this->getUnloadingQr($detail->geItem, $qty);
                    if ($existingQRCodes->count() > 0) {
                        $this->copyQrCodes($existingQRCodes,$detail, $header, $job, $namespace, $attributes, $type, CommonHelper::RECEIPT, $trnstype, $batch);
                    }else {
                        // ❗ Fall back to fresh QR code creation
                        $this->createUniqueCode($header, $job, $namespace, $detail, $attributes, $type, $trnstype, $batch, $qty);
                    }
                }

                return; // Exit here so fresh creation logic is not executed for MRN
            }

            $existingQRCodes = $this->getUnloadingQr($detail->geItem, $qty);
            if ($existingQRCodes->count() > 0) {
                $this->copyQrCodes($existingQRCodes,$detail, $header, $job, $namespace, $attributes, $type, CommonHelper::RECEIPT, $trnstype, NULL);
                return; // exit after copying
            }
        }

        // Check if this is InspectionDetail and has mrn_header_id
        if ($namespace === \App\Models\InspectionDetail::class && isset($detail->mrn_detail_id) && $detail->mrn_detail_id) {
            $mrnDetail = MrnDetail::find($detail->mrn_detail_id);
            if (isset($mrnDetail->gate_entry_detail_id) && $mrnDetail->gate_entry_detail_id) {

                if ($batchData->count() > 0) {
                    foreach($batchData as $batch){
                        $qty = isset($batch->accepted_inv_uom_qty) & $batch->accepted_inv_uom_qty ? $batch->accepted_inv_uom_qty : intval($batch->inventory_uom_qty);
                        $existingQRCodes = $this->getUnloadingQr($mrnDetail->geItem, $qty);
                        if ($existingQRCodes->count() > 0) {
                            $this->copyQrCodes($existingQRCodes,$detail, $header, $job, $namespace, $attributes, $type, CommonHelper::RECEIPT, $trnstype, $batch);
                        }else{
                            $this->createUniqueCode($header, $job, $namespace, $detail, $attributes, $type, $trnstype, $batch, $qty);
                        }
                    }

                    return; // Exit here so fresh creation logic is not executed for MRN
                }


                $existingQRCodes = $this->getUnloadingQr($mrnDetail->geItem, $qty);
                if ($existingQRCodes->count() > 0) {
                    $this->copyQrCodes($existingQRCodes,$detail, $header, $job, $namespace, $attributes, $type, CommonHelper::RECEIPT, $trnstype, NULL);
                    return; // exit after copying
                }
            }
        }
        
        if ($batchData->count() > 0) {
            foreach($batchData as $batch){
                $qty = isset($batch->accepted_inv_uom_qty) & $batch->accepted_inv_uom_qty ? $batch->accepted_inv_uom_qty : intval($batch->inventory_uom_qty);
                $this->createUniqueCode($header, $job, $namespace, $detail, $attributes, $type, $trnstype, $batch,$qty);
            }

            return; // Exit here so fresh creation logic is not executed for MRN
        }

        // ❗ Fresh creation logic (same as before)
        $existingCount = $detail->uniqueCodes()->where('job_id', $job->id)
            ->count();
        if ($qty > $existingCount) {
            $diff = $qty - $existingCount;
            $this->createUniqueCode($header, $job, $namespace, $detail, $attributes, $type, $trnstype, NULL, $diff);
        } elseif ($qty < $existingCount) {
            $diff = $existingCount - $qty;

            ErpItemUniqueCode::where('job_id', $job->id)
                ->where('item_id', $detail->item_id)
                ->where('morphable_type', $namespace)
                ->where('morphable_id', $detail->id)
                ->where('status', 'pending')
                ->orderBy('id', 'desc')
                ->limit($diff)
                ->delete();
        }
    }

    private function createUniqueCode($header, $job, $namespace, $detail, $attributes, $type, $trnstype, $batch, $qty){
        $records = [];
        $referenceType = null;
        $referenceDetailId = null;

        $morphableType = $namespace;
        $morphableId = $detail->id;

        if ($namespace === \App\Models\InspectionDetail::class){
            $morphableType = \App\Models\MrnDetail::class;
            $morphableId = $detail->mrn_detail_id;
            $referenceType = ConstantHelper::INSPECTION_SERVICE_ALIAS;
            $referenceDetailId = $detail->id;
        }

        for ($i = 0; $i < $qty; $i++) {
            $records[] = [
                'uid' => $this->generateUniqueUid(),
                'job_id' => $job->id,
                'organization_id' => $header->organization_id,
                'group_id' => $header->group_id,
                'company_id' => $header->company_id,
                'morphable_type' => $morphableType,
                'morphable_id' => $morphableId,
                'job_type' => $type,
                'trns_type' => $trnstype,
                'doc_type' => CommonHelper::RECEIPT,
                'doc_no' => $header->document_number ?? null,
                'doc_date' => $header->document_date ?? null,
                'book_id' => $header->book_id ?? null,
                'store_id' => $this->storeId ?? null,
                'sub_store_id' => $this->subStoreId ?? null,
                'book_code' => $header->book_code ?? null,
                'item_attributes' => json_encode($attributes),
                'item_id' => $detail->item_id,
                'item_name' => $detail->item->item_name,
                'item_code' => $detail->item_code,
                'vendor_id' => $header->vendor_id,
                'item_uid' => $this->generateUniqueUid(),
                'batch_id' => $batch ? $batch->id : NULL,
                'batch_number' => $batch ? $batch->batch_number : NULL,
                'manufacturing_year' => $batch ? ($batch->manufacturing_year == 0 ? NULL : $batch->manufacturing_year) : NULL,
                'expiry_date' => $batch ? ($batch->expiry_date ? date('Y-m-d',strtotime($batch->expiry_date)) : NULL) : NULL,
                'type' => 'qr',
                'qty' => 1,
                'status' => CommonHelper::PENDING,
                'reference_type' => $referenceType,
                'reference_detail_id' => $referenceDetailId,
                'reference_no' => $this->referenceNo,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach (array_chunk($records, 500) as $chunk) {
            ErpItemUniqueCode::insert($chunk);
        }

    }

    private function getUnloadingQr($geDetail, $qty)
    {
        $existingGateQRCodes = $geDetail->uniqueCodes()->where('status', CommonHelper::SCANNED)
            ->whereNull('utilized_id')
            ->limit($qty)
            ->get();

        return $existingGateQRCodes;
    }


    private function copyQrCodes($existingQRCodes, $detail, $header, $job, $namespace, $attributes, $type, $docType = CommonHelper::RECEIPT, $trnstype, $batch){
        $morphableType = $namespace;
        $morphableId = $detail->id;
        $referenceType = null;
        $referenceDetailId = null;

        if ($namespace === \App\Models\InspectionDetail::class){
            $morphableType = \App\Models\MrnDetail::class;
            $morphableId = $detail->mrn_detail_id;
            $referenceType = ConstantHelper::INSPECTION_SERVICE_ALIAS;
            $referenceDetailId = $detail->id;
        }

        foreach ($existingQRCodes as $code) {
            $newRecord = ErpItemUniqueCode::create([
                'uid' => $this->generateUniqueUid(),
                'job_id' => $job->id,
                'organization_id' => $header->organization_id,
                'group_id' => $header->group_id,
                'company_id' => $header->company_id,
                'morphable_type' => $morphableType,
                'morphable_id' => $morphableId,
                'job_type' => $type,
                'trns_type' => $trnstype,
                'doc_type' => $docType,
                'doc_no' => $header->document_number ?? null,
                'doc_date' => $header->document_date ?? null,
                'book_id' => $header->book_id ?? null,
                'store_id' => $this->storeId ?? null,
                'sub_store_id' => $this->subStoreId ?? null,
                'book_code' => $header->book_code ?? null,
                'item_attributes' => json_encode($attributes),
                'item_id' => $detail->item_id,
                'item_name' => $detail->item->item_name,
                'item_code' => $detail->item_code,
                'vendor_id' => isset($header->vendor_id) ? $header->vendor_id : NULL,
                'item_uid' => $code->item_uid, 
                'batch_id' => $batch ? $batch->id : NULL,
                'batch_number' => $batch ? $batch->batch_number : NULL,
                'manufacturing_year' => $batch ? ($batch->manufacturing_year == 0 ? NULL : $batch->manufacturing_year) : NULL,
                'expiry_date' => $batch ? ($batch->expiry_date ? date('Y-m-d',strtotime($batch->expiry_date)) : NULL) : NULL,
                'type' => 'qr',
                'qty' => 1,
                'status' => CommonHelper::PENDING,
                'reference_type' => $referenceType,
                'reference_detail_id' => $referenceDetailId,
                'reference_no' => $this->referenceNo,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $code->utilized_id = $newRecord->uid;
            $code->save();
        }
    }

    private function getAttributes($detail){
        $attributeJsonArray = [];
        if(isset($detail->attributes) && !empty($detail->attributes)){
            foreach($detail->attributes as $key1 => $attribute) {
                $attributeJsonArray[] = [
                    "attr_name" => (string)$attribute->attr_name,
                    "attribute_name" => (string)@$attribute->attributeName->name,
                    "attr_value" => (string)@$attribute->attr_value,
                    "attribute_value" => (string)@$attribute->attributeValue->value,
                ];
            }
        }

        return $attributeJsonArray;
    }

    private function generateUniqueUid($length = 15)
    {
        $raw = str_replace('-', '', Str::uuid()); // 15-character hex
        $uid = strtoupper(substr($raw, 0, $length)); // Alphanumeric only, uppercase
        return $uid;
    }
}