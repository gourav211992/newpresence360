<?php

namespace App\Imports;

use App\Helpers\Helper;
use App\Helpers\ConstantHelper;
use App\Http\Controllers\FixedAsset\RegistrationController;
use App\Models\FixedAssetRegistration;
use App\Models\Organization;
use App\Models\UploadFAMaster;
use App\Services\FAImportExportService;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Log;
use App\Models\FixedAssetSub;
use Illuminate\Support\Facades\DB;
use Exception;

class FAImport implements ToModel, WithHeadingRow, WithChunkReading, WithStartRow
{
    protected $successfulItems = [];
    protected $failedItems = [];
    protected $service;
    protected $user;
    protected $book;

    public function __construct(FAImportExportService $service, $user,$book)
    {
        $this->service = $service;
        $this->user = $user;
        $this->book = $book;
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
            'series'       => isset($row['series']) ? trim($row['series']) : null,
            'asset_code'       => isset($row['asset_code']) ? trim($row['asset_code']) : null,
            'asset_name'       => isset($row['asset_name']) ? trim($row['asset_name']) : null,
            'location'         => isset($row['location']) ? trim($row['location']) : null,
            'cost_center'      => isset($row['cost_center']) ? trim($row['cost_center']) : null,
            'category'         => isset($row['category']) ? trim($row['category']) : null,
            'ledger'           => isset($row['ledger']) ? trim($row['ledger']) : null,
            'capitalize_date'  => isset($row['capitalize_date']) ? trim($row['capitalize_date']) : null,
            'quantity'         => isset($row['quantity']) ? trim($row['quantity']) : null,
            'maintenance_schedule' => isset($row['maintenance_schedule']) ? trim($row['maintenance_schedule']) : null,
            'useful_life'      => isset($row['useful_life']) ? trim($row['useful_life']) : null,
            'current_value'    => isset($row['current_value']) ? trim($row['current_value']) : null,
            'vendor'           => isset($row['vendor']) ? trim($row['vendor']) : null,
            'currency'         => isset($row['currency']) ? trim($row['currency']) : null,
            'book_date'        => isset($row['book_date']) ? trim($row['book_date']) : null,
        ];

        $user = Helper::getAuthenticatedUser();
        try {
            // Validate required fields
            $this->service->checkRequiredFields($mappedRow);
            $data = $this->service->processData($mappedRow);
            $data['organization_id'] = $user->organization_id;
            $data['created_by'] = $user->id;
            $data['type'] = get_class($user);
            $data['company_id'] = $user->organization->company_id;
            $data['group_id'] = $user->organization->group_id;
            $data['revision_number'] = 0; // Default revision number
            $docData = RegistrationController::genrateDocNo($this->book);
            if ($docData == null) {
                throw new Exception("Document number generation failed.");
            }
            $data = array_merge($data, $docData);
            $item = FixedAssetRegistration::create($data);
            if ($item) {
                FixedAssetSub::generateSubAssets(
                    $item->id,
                    $item->asset_code,
                    $item->quantity,
                    $item->current_value,
                    $item->salvage_value,
                );
            }


            $approveDocument = Helper::approveDocument($item->book_id, $item->id, $item->revision_number, null, null, 1, 'submit', $item->current_value, get_class($item));
            $item->document_status = $approveDocument['approvalStatus'] ?? 'submitted';
            $item->approval_level = $approveDocument['approvalLevel'] ?? 1;
            $item->save();

            $uploadData = [
                'import_status' => 'Success',
                'import_remarks' => 'success'
            ];
            $uploadData = array_merge($data, $uploadData);



            $uploadedItem = UploadFAMaster::create($uploadData);

            $this->onSuccess($uploadedItem);
            return $uploadedItem;
        } catch (Exception $e) {
            Log::error("Error importing item: " . $e->getMessage(), [
                'row' => $row,
                'exception' => $e,
            ]);
            $uploadData = [
                'import_status' => 'Failed',
                'asset_code' => $mappedRow['asset_code'] ?? null,
                'asset_name' => $mappedRow['asset_name'] ?? null,
                'import_remarks' => $e->getMessage(),

            ];



            if (isset($data) && is_array($data)) {
                $uploadData = array_merge($data, $uploadData); // Optional: merge with any additional data
            } else {
                $uploadData = array_merge(
                    $mappedRow, // Save all mapped row data
                    [
                        'import_status' => 'Failed',
                        'import_remarks' => $e->getMessage(),

                    ]
                );
            }

            $uploadedItem = UploadFAMaster::create($uploadData);

            $this->onFailure($uploadedItem);
            return null;
        }
    }
}
