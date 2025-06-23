<?php

namespace App\Imports;

use App\Helpers\Helper;
use App\Helpers\ConstantHelper;
use App\Http\Controllers\FixedAsset\RegistrationController;
use App\Models\FixedAssetRegistration;
use App\Models\UploadFAMaster;
use App\Services\FAImportExportService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Log;
use Exception;

class FAImport implements ToModel, WithHeadingRow, WithChunkReading, WithStartRow
{
    protected $successfulItems = [];
    protected $failedItems = [];
    protected $service;
    protected $user;

    public function __construct(FAImportExportService $service, $user)
    {
        $this->service = $service;
        $this->user = $user;
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function startRow(): int
    {
        return 3;
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function onSuccess($row)
    {
        $this->successfulItems[] = $row;
    }

    public function onFailure($uploadedItem)
    {
        $this->failedItems[] = $uploadedItem;
    }

    public function getSuccessfulItems()
    {
        return $this->successfulItems;
    }

    public function getFailedItems()
    {
        return $this->failedItems;
    }

    public function model(array $row)
    {
        if (collect($row)->filter()->isEmpty()) {
            return null;
        }

        $uploadedItem = null;
        $status = 'Success';

        // Normalize and map row fields to expected keys
        $mappedRow = [
            'asset_code'       => $row['asset_code'] ?? null,
            'asset_name'       => $row['asset_name'] ?? null,
            'location'         => $row['location'] ?? null,
            'cost_center'      => $row['cost_center'] ?? null,
            'category'         => $row['category'] ?? null,
            'ledger'           => $row['ledger'] ?? null,
            'capitalize_date'  => $row['capitalize_date'] ?? null,
            'quantity'         => $row['quantity'] ?? null,
            'mt_sch'           => $row['mt_sch'] ?? null,
            'useful_life'      => $row['useful_life'] ?? null,
            'current_value'    => $row['current_value'] ?? null,
            'life'             => $row['life'] ?? null,
            'vendor'           => $row['vendor'] ?? null,
            'currency'         => $row['currency'] ?? null,
            'tax'              => $row['tax'] ?? 0,
            'book_date'        => $row['book_date'] ?? null,
        ];

        try {
            // Validate required fields
            $this->service->checkRequiredFields($mappedRow);
            $data = $this->service->processData($mappedRow);
            $data['organization_id'] = $this->user->organization_id;
            $data['created_by'] = $this->user->id;
            $data['type'] = get_class($this);
            $data['company_id'] = $this->user->company_id;
            $data['group_id']= $this->user->group_id;
            $docData = RegistrationController::genrateDocNo();
            if($docData==null)
            {
                throw new Exception("Document number generation failed.");
            }
            $data = array_merge($data, $docData);
            $item = FixedAssetRegistration::create($data);
            $approveDocument = Helper::approveDocument($item->book_id, $item->id, $item->revision_number , null, null, 1,'submit', $item->current_value, get_class($item));
            $item->document_status = $approveDocument['approvalStatus'] ?? 'submitted';
            $item->approval_level = $approveDocument['approvalLevel'] ?? 1;
            $item->save();
            
           

            // Create upload record
            $uploadedItem = UploadFAMaster::create([
                'import_status' => 'Success',
                'import_remarks' => 'Successfully imported item.',
            ]);

            $this->onSuccess($uploadedItem);
            return $uploadedItem;
        } catch (Exception $e) {
            Log::error("Error importing item: " . $e->getMessage(), [
                'row' => $row,
                'exception' => $e,
            ]);
            $uploadData = [
                'import_status' => 'Failed',
                'import_remarks' => $e->getMessage(),
            ];

            if (isset($data) && is_array($data)) {
                $uploadData = array_merge($data, $uploadData);
            }

            $uploadedItem = UploadFAMaster::create($uploadData);
            $this->onFailure($uploadedItem);
            return null;
        }
    }
}
