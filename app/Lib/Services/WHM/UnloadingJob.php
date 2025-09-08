<?php

namespace App\Lib\Services\WHM;

use App\Helpers\CommonHelper;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
use Illuminate\Support\Str;

class UnloadingJob
{
    public function createJob($id, $namespace)
    {
        // Step 1: Get Header
        $header = app($namespace)::findOrFail($id);
        
        $type = $jobType ?? CommonHelper::getJobType($namespace);
        $trnstype = CommonHelper::getJobTransactionType($namespace);

        // Step 2: Get or Create Job (prevents duplicate job on edit)
        $job = (new WhmJob())->createJob($header, $namespace, $type, $trnstype, $header->store_id, NULL, NULL, NULL, NULL);


        // Step 3: Fetch Details with attributes
        if (!method_exists($header, 'items')) {
            throw new \Exception("Model does not have 'items' relationship defined.");
        }

        $details = $header->items()->with('attributes')->get();
        
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
        $storageUomCount = intval(optional($detail->item)->storage_uom_count);
        $totalPacket = $storageUomCount > 0 ? $storageUomCount : 1;

        // ❗ Fresh creation logic (same as before)
        $existingCount = $detail->uniqueCodes()
            ->where('job_id', $job->id)
            ->groupBy('packet_no')
            ->count();

        if ($qty > $existingCount) {
            $diff = $qty - $existingCount;
            (new WhmJob())->createUniqueCode($header, $job, $namespace, $detail->id, $detail, $attributes, $type, $trnstype, $diff, CommonHelper::RECEIPT, $header->store_id, NULL, NULL, NULL, NULL, $totalPacket, NULL);
        } elseif ($qty < $existingCount) {
            $diff = $existingCount - $qty;
            (new WhmJob())->deleteUniqueCode($job, $detail->id, $totalPacket, $diff);
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
}