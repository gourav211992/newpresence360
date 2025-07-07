<?php

namespace App\Imports;

use App\Helpers\Helper;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ConstantHelper;
use App\Models\Ledger;
use App\Models\UploadLedgerMaster;
use App\Models\UploadPendingPaymentMaster;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use App\Services\CrDrImportExportService;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Exception;
class CrDrReportImport implements ToModel, WithHeadingRow, WithChunkReading, WithStartRow
{
    protected $successfulItems = [];
    protected $failedItems = [];
    protected $service;
    protected $user;
    protected $type;

    public function chunkSize(): int
    {
        return 500;
    }

    public function __construct(CrDrImportExportService $service, $user, $type)
    {
        $this->service = $service;
        $this->user = $user;
        $this->type = $type;
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
        $this->successfulItems[] = [
            'ledger_name' => $row->ledger_name,
            'voucher_no' => $row->voucher_no,
            'ledger_group' =>  $row->ledger_group,
            'balance' => $row->balance,
            'settle_amount' => $row->settle_amount,
            'series' => $row->series,
            'id' => $row->id,
            'status_check' => 'success',
            'remarks' => 'Success',
        ];
    }

    public function onFailure($uploadedItem)
    {
        $this->failedItems[] = [
           'ledger_name' => $uploadedItem->ledger_name,
            'voucher_no' => $uploadedItem->voucher_no,
            'ledger_group' =>  $uploadedItem->ledger_group,
            'balance' => $uploadedItem->balance,
            'settle_amount' => $uploadedItem->settle_amount,
            'series' => $uploadedItem->series,
            'id' => $uploadedItem->id,
            'status_check' => 'failed',
            'remarks' => $uploadedItem->import_remarks,
        ];
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
        $organization = $this->user->organization;

        // Always create pending item first


        try {
            // First, validate required fields (stop if missing)
            $this->service->validateImportRow($row);
            $row = $this->service->processData($row,$this->type);
            $uploadedItem = $this->savePendingPaymentImport($row, $this->user, $organization, 'Success', null,$this->type);


            // --- SUCCESS: Save with success remarks ---
            if ($uploadedItem) {
                $this->onSuccess($uploadedItem);
            }

        } catch (\Exception $e) {
            // This catches any *unexpected* error in the logic above
            Log::error("Error importing row: " . $e->getMessage(), ['error' => $e]);
            $uploadedItem = $this->savePendingPaymentImport($row, $this->user, $organization, 'Success', null,$this->type);
            $uploadedItem->update([
                'import_status' => 'Failed',
                'import_remarks' => str_replace(',', '', $e->getMessage()),
            ]);
            $this->onFailure($uploadedItem);
            return;
        }
    }


    protected function savePendingPaymentImport($row, $user, $organization, $status, $remarks,$type)
    {
       return UploadPendingPaymentMaster::create([
            'ledger_name'      => $row['ledger_name'] ?? null,
            'doc_type'=> $type,
            'ledger_group'     => $row['ledger_group'] ?? null,
            'voucher_no'       => $row['voucher_no'] ?? null,
            'voucher_id'=>  $row['voucher_id']??null,
            'ledger_id'=>   $row['ledger_id']??null,
            'ledger_group_id'=>   $row['ledger_group_id']??null,
            'settle_amount'    => $row['settle_amount'] ?? null,
            'balance'          => $row['balance'] ?? null,
            'series'          => $row['series'] ?? null,
            'user_id'          => $user->id,
            'group_id'         => $organization->group_id,
            'company_id'       => $organization->company_id,
            'organization_id'  => $organization->id,
            'import_status'    => $status,
            'import_remarks'   => $remarks,
        ]);
        

    }

    // protected function saveSuccessImport($row, $user, $organization)
    // {
    //    return UploadPendingPaymentMaster::create([
    //         'ledger_name'      => $row['ledger_name'] ?? null,
    //         'ledger_group'     => $row['ledger_group'] ?? null,
    //         'voucher_no'       => $row['voucher_no'] ?? null,
    //         'settle_amount'    => $row['settle_amount'] ?? null,
    //         'balance'          => $row['balance'] ?? null,
    //         'user_id'          => $user->id,
    //         'group_id'         => $organization->group_id,
    //         'company_id'       => $organization->company_id,
    //         'organization_id'  => $organization->id,
    //         'import_status'    => 'Success',
    //         'import_remarks'   => 'Success',
    //     ]);

        
    // }
}
