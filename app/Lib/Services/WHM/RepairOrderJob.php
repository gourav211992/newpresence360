<?php

namespace App\Lib\Services\WHM;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
use Illuminate\Support\Str;
use App\Helpers\ReManufacturing\RepairOrder\Constants as RepConstant;

class RepairOrderJob
{
    protected $referenceNo;  
    protected $referenceHeader; 
    protected $storeId; 
    protected $subStoreId; 

    public function createJob($id, $namespace)
    {
        // Step 1: Get Header
        $header = app($namespace)::findOrFail($id);
        $morphableType = $namespace;
        $morphableId = $header->id;
        $referenceType = null;
        $referenceId = null;
        $this->referenceNo = null;
        $this->referenceHeader = $header;
        $this->storeId = isset($header->store_id) ? $header->store_id : null;
        $this->subStoreId = isset($header->sub_store_id) ? $header->sub_store_id : null;

        // Step 2: Get or Create Job (prevents duplicate job on edit)
        $job = ErpWhmJob::firstOrCreate(
            [
                'morphable_type' => $namespace,
                'morphable_id' => $header->id,
                'type' => 'repair',
            ],
            [
                'organization_id' => $header->organization_id,
                'group_id' => $header->group_id,
                'company_id' => $header->company_id,
                'status' => 'pending',
                'trns_type' => RepConstant::SERVICE_ALIAS,
                'store_id' => $this->storeId ?? null,
                'sub_store_id' => $this->subStoreId ?? null,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reference_no' => $this->referenceNo,
            ]
        );

        $detailsQuery = $header->items()->with('attributes');
        $details = $detailsQuery->get();
        // Step 3: Loop through each detail and create unique item codes
        foreach ($details as $detail) {
            $detalNamespace = get_class($detail);
            $this->copyRGRQrCode($header, $job, $detalNamespace, $detail);
        }

    }

    private function copyRGRQrCode($header, $job, $namespace, $detail)
    {
        $attributes = $this->getAttributes($detail);        
        $rgrQrCode = ErpItemUniqueCode::find($detail -> rgr_job_detail_id);
        if ($rgrQrCode) {
            ErpItemUniqueCode::create([
                'uid' => $this->generateUniqueUid(),
                'job_id' => $job->id,
                'organization_id' => $header->organization_id,
                'group_id' => $header->group_id,
                'company_id' => $header->company_id,
                'morphable_type' => $namespace,
                'morphable_id' => $detail->id,
                'job_type' => $job->job_type,
                'trns_type' => RepConstant::SERVICE_ALIAS,
                'doc_type' => 'receipt',
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
                'vendor_id' => $header?->vendor_id,
                'item_uid' => $rgrQrCode->item_uid,
                'batch_id' => NULL,
                'batch_number' => NULL,
                'manufacturing_year' => NULL,
                'expiry_date' => NULL,
                'type' => 'qr',
                'qty' => 1,
                'status' => 'pending',
                'reference_type' => NULL,
                'reference_detail_id' => NULL,
                'reference_no' => NULL,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
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

    
    public function generateUniqueUid($length = 15)
    {
        $raw = str_replace('-', '', Str::uuid()); // 15-character hex
        $uid = strtoupper(substr($raw, 0, $length)); // Alphanumeric only, uppercase
        return $uid;
    }
}