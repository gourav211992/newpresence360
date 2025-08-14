<?php

namespace App\Imports;

use App\Helpers\Helper;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Ledger;
use App\Models\UploadPendingPaymentMaster;
use App\Services\CrDrImportExportService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Facades\Log;
use Exception;

class CrDrReportImport implements ToCollection, WithHeadingRow, WithStartRow
{
    protected $successfulItems = [];
    protected $failedItems = [];
    protected $service;
    protected $user;
    protected $type;

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
            'balance' => Helper::formatIndianNumber($row->balance),
            'settle_amount' => Helper::formatIndianNumber($row->settle_amount),
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
            'balance' => Helper::formatIndianNumber($uploadedItem->balance),
            'settle_amount' =>  Helper::formatIndianNumber($uploadedItem->settle_amount),
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

    public function collection(Collection $rows): void
    {
        $organization = $this->user->organization;

        // 1) Collect non-empty rows
        $rawRows = [];
        foreach ($rows as $row) {
            if (collect($row)->filter()->isEmpty()) {
                continue;
            }
            $rawRows[] = $row->toArray();
        }

        // 2) Build groups
        $grouped      = [];
        $groupTotals  = [];
        $groupBalances= [];
        $groupMeta    = [];  // will hold ledger_id, ledger_group_id, voucher_id

        foreach ($rawRows as $idx => $row) {
            $key = trim($row['ledger_name']) . '|' . trim($row['series']) . '|' . trim($row['voucher_no']);
            $grouped[$key][] = $idx;
        }

        // 3) For each group, compute total and lookup balance & IDs
        foreach ($grouped as $key => $indexes) {
            $settleTotal = 0;
            foreach ($indexes as $i) {
                $settleTotal += Helper::removeCommas($rawRows[$i]['settle_amount']);
            }

            list($ledgerName, $series, $voucherNo) = explode('|', $key);

            $ledgerObj = Ledger::where('name', $ledgerName)->first();
            if ($ledgerObj) {
                $groupObj = $ledgerObj->group()->first();

                $invoices = Helper::getVoucherBalance($voucherNo, $this->type, $ledgerObj->id, $groupObj->id);
                $data     = $invoices->getData()->data;

                // robust match
                $voucher = collect($data)->first(function($item) use ($voucherNo, $series) {
                    $itemVno    = strtolower(trim((string)$item->voucher_no));
                    $lookupVno  = strtolower(trim((string)$voucherNo));
                    $itemSeries = isset($item->series->book_code)
                                  ? strtolower(trim((string)$item->series->book_code))
                                  : '';
                    $lookupSer  = strtolower(trim((string)$series));

                    return $itemVno === $lookupVno
                        && (!$lookupSer || $itemSeries === $lookupSer)
                        && $item->balance > 0;
                });
                // dd($voucher);
                $balance   = $voucher->balance ?? 0;
                $voucherId = $voucher->id      ?? null;

                $groupBalances[$key] = $balance;
                $groupMeta[$key]     = [
                    'ledger_id'       => $ledgerObj->id,
                    'ledger_group_id' => $groupObj->id ?? null,
                    'voucher_id'      => $voucherId,
                    'ledger_group'    => $groupObj->name ?? null,
                ];
            } else {
                $groupBalances[$key] = 0;
                $groupMeta[$key]     = [
                    'ledger_id'       => null,
                    'ledger_group_id' => null,
                    'voucher_id'      => null,
                    'ledger_group'    => null,
                ];
            }

            $groupTotals[$key] = $settleTotal;
        }

        // 4) Mark invalid by total > balance
        $invalidRows = [];
        foreach ($grouped as $key => $indexes) {
            $total   = $groupTotals[$key];
            $balance = $groupBalances[$key];
            if ($total > $balance) {
                foreach ($indexes as $i) {
                    $invalidRows[$i] = [
                        'message' => "Total Settle Amount ({$total}) for ({$key}) exceeds Balance ({$balance}).",
                        'balance' => $balance,
                        'meta'    => $groupMeta[$key],
                    ];
                }
            }
        }

        // 5) Process each row
        foreach ($rawRows as $i => $row) {
            try {

                // a) Batch‐sum failure?
                if (isset($invalidRows[$i])) {
                    $info        = $invalidRows[$i];
                    $failureRow  = $row;
                    // inject meta & balance
                    $failureRow['balance']          = $info['balance'];
                    $failureRow['settle_amount']    = Helper::removeCommas($failureRow['settle_amount']);
                    $failureRow['voucher_id']       = $info['meta']['voucher_id'];
                    $failureRow['ledger_id']        = $info['meta']['ledger_id'];
                    $failureRow['ledger_group_id']  = $info['meta']['ledger_group_id'];
                    $failureRow['ledger_group']     = $info['meta']['ledger_group'];


                    $uploadedItem = $this->savePendingPaymentImport(
                        $failureRow,
                        $this->user,
                        $organization,
                        'Failed',
                        $info['message'],
                        $this->type
                    );
                    $this->onFailure($uploadedItem);
                    continue;
                }

                // b) Per-row validation
                $this->service->validateImportRow($row);
                $result = $this->service->processData($row, $this->type);

                if ($result['status']) {
                    $r = $result['row'];
                    $uploadedItem = $this->savePendingPaymentImport(
                        $r, $this->user, $organization, 'Success','Success',$this->type
                    );
                    $this->onSuccess($uploadedItem);
                } else {
                    $r = $result['row'];
                    $uploadedItem = $this->savePendingPaymentImport(
                        $r, $this->user, $organization,'Failed',$result['error'],$this->type
                    );
                    $this->onFailure($uploadedItem);
                }
            } catch (Exception $e) {
                Log::error("Error processing row: ".$e->getMessage(), ['row'=>$row]);
                $uploadedItem = $this->savePendingPaymentImport(
                    $row, $this->user, $organization,'Failed','Unexpected error',$this->type
                );
                $this->onFailure($uploadedItem);
            }
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

    
}