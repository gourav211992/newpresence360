<?php

namespace App\Lib\Services\WHM;

use App\Helpers\CommonHelper;
use App\Helpers\ConstantHelper;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;

class MaterialIssueWhmJob
{
    protected $referenceNo;  
    protected $referenceHeader; 
    protected $storeId; 
    protected $subStoreId; 

    public function createJob($id, $namespace, $jobType)
    {
        // Step 1: Get Header
        $header = app($namespace)::findOrFail($id);
        $referenceType = null;
        $referenceId = null;
        $this->referenceNo = null;
        $this->referenceHeader = $header;
        //In Case of Putaway (Issue) - To
        if ($jobType === CommonHelper::PUTAWAY) {
            $this->storeId = isset($header->to_store_id) ? $header->to_store_id : null;
            $this->subStoreId = isset($header->to_sub_store_id) ? $header->to_sub_store_id : null;
        } else {
            //In Case of Picking and Dispatch (Issue) - From
            $this->storeId = isset($header->from_store_id) ? $header->from_store_id : null;
            $this->subStoreId = isset($header->from_sub_store_id) ? $header->from_sub_store_id : null;
        }

        $type = $jobType;
        $trnstype = ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME;

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
                'status' => 'pending',
                'trns_type' => $trnstype,
                'store_id' => $this->storeId ?? null,
                'sub_store_id' => $this->subStoreId ?? null,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reference_no' => $this->referenceNo,
            ]
        );
        //PUTAWAY JOB
        if ($job -> trns_type === CommonHelper::PICKING) {
            $this -> copyQRCodes($job);
        }
        return;
    }

    public function copyQRCodes($job)
    {
        $subStoreId = $job -> sub_store_id;
        $packets = $job->itemUniqueCodes()
            ->where('status', CommonHelper::SCANNED)
            ->where('doc_type', CommonHelper::ISSUE)
            ->whereNull('utilized_id')
            ->get();
        $mainWhmJob = new WhmJob();
        if($packets->isNotEmpty()){
            foreach ($packets as $packet) {
                $newRecord = ErpItemUniqueCode::create([
                    'uid' => $mainWhmJob->generateUniqueUid(),
                    'job_id' => $packet->job_id,
                    'organization_id' => $packet->organization_id,
                    'group_id' => $packet->group_id,
                    'company_id' => $packet->company_id,
                    'morphable_type' => $packet->morphable_type,
                    'morphable_id' => $packet->morphable_id,
                    'job_type' => $packet->job_type,
                    'trns_type' => $job -> trns_type,
                    'doc_type' => CommonHelper::RECEIPT,
                    'doc_no' => $packet->doc_no ?? null,
                    'doc_date' => $packet->doc_date ?? null,
                    'book_id' => $packet->book_id ?? null,
                    'store_id' => $packet->store_id ?? null,
                    'sub_store_id' => $subStoreId ?? null,
                    'book_code' => $packet->book_code ?? null,
                    'item_attributes' => json_encode($packet->item_attributes),
                    'item_id' => $packet->item_id,
                    'item_name' => $packet->item_name,
                    'item_code' => $packet->item_code,
                    'vendor_id' => $packet->vendor_id,
                    'item_uid' => $packet->item_uid, 
                    'storage_point_id' => $packet->storage_point_id, 
                    'type' => 'qr',
                    'qty' => 1,
                    'status' => CommonHelper::SCANNED,
                    'created_at' => now(),
                    'updated_at' => now(),
                    'action_by' => $packet->action_by,
                    'action_at' => now()
                ]);
                //Utilize the issue records
                $packet->utilized_id = $newRecord->uid;
                $packet->save();
            }
        }
    }
}