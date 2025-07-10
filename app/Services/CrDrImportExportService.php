<?php

namespace App\Services;

use App\Helpers\ConstantHelper;
use App\Models\Ledger;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Models\Group;
use Illuminate\Support\Facades\Log;
use App\Models\Voucher;
use Exception;


class CrDrImportExportService
{
    public function validateImportRow(array $row)
    {
        $requiredFields = [
            'ledger_name'   => 'Ledger Name',
            'ledger_group'  => 'Ledger Group',
            'voucher_no'   => 'Voucher No',
            'series'   => 'Series',
            'settle_amount' => 'Settle Amount',
            // 'balance'       => 'Balance'
        ];

        foreach ($requiredFields as $key => $label) {
            if (!isset($row[$key]) || is_null($row[$key]) || $row[$key] === '') {
                throw new Exception("Required field '{$label}' is missing in import file.");
        
            }
        }
        return true;
    }
   public function processData(array $row, $type)
{
    try {
        $ledgerName   = isset($row['ledger_name']) ? trim($row['ledger_name']) : null;
        $ledgerGroup  = isset($row['ledger_group']) ? trim($row['ledger_group']) : null;
        $voucherNo    = isset($row['voucher_no']) ? trim($row['voucher_no']) : null;
        $series       = isset($row['series']) ? trim($row['series']) : null;
        $settleAmountRaw = isset($row['settle_amount']) ? trim($row['settle_amount']) : null;
        $settleAmount = Helper::removeCommas($settleAmountRaw);

        if (!is_numeric($settleAmount)) {
            return [
                'status' => false,
                'row' => $row,
                'error' => "Settle Amount must be a valid number. Found: '{$settleAmountRaw}'"
            ];
        }

        $ledger = Ledger::where('name', $ledgerName)
            ->first();

        if (empty($ledger)) {
            return [
                'status' => false,
                'row' => $row,
                'error' => "Ledger '{$ledgerName}' does not exist."
            ];
        }
        
        $group = Helper::getGroupsQuery()
            ->where('name', $ledgerGroup)
            ->first();

        if (empty($group)) {
            return [
                'status' => false,
                'row' => $row,
                'error' => "Ledger group '{$ledgerGroup}' does not exist."
            ];
        }
        
        $invoices = Helper::getVoucherBalance($voucherNo, $type, $ledger->id, $group->id);
        $voucher = collect($invoices->getData()->data)
            ->first(function ($item) use ($voucherNo, $series) {
                if ($item->balance <= 0 || $item->voucher_no !== $voucherNo) {
                    return false;
                }
                if ($series) {
                    return isset($item->series?->book_code) &&
                        $item->series->book_code === $series;
                }
                return true;
            });

        if (!$voucher) {
            return [
                'status' => false,
                'row' => $row,
                'error' => $series
                    ? "Series '{$series}' not exist related to the Voucher no# '{$voucherNo}'."
                    : "Voucher no# '{$voucherNo}' not valid."
            ];
        }

        $row['voucher_id']      = $voucher->id;
        $row['ledger_id']       = $ledger->id;
        $row['ledger_group_id'] = $group->id;
        $row['settle_amount']   = $settleAmount;
        $voucherBalance         = $voucher->balance;
        $row['balance']         = $voucherBalance;

        $balance      = Helper::removeCommas($row['balance']);
        $settleAmount = Helper::removeCommas($settleAmount);

        if ($balance == 0) {
            return [
                'status' => false,
                'row' => $row,
                'error' => "Balance must not be zero."
            ];
        }
        if ($settleAmount > $balance) {
            return [
                'status' => false,
                'row' => $row,
                'error' => "Settle Amount ({$settleAmount}) cannot be greater than Balance ({$voucherBalance})."
            ];
        }

        // Success!
        return [
            'status' => true,
            'row' => $row,
            'error' => null
        ];
    } catch (\Exception $e) {
        // Unexpected error, also return what we have.
        return [
            'status' => false,
            'row' => $row,
            'error' => 'Unexpected error: ' . $e->getMessage()
        ];
    }
}
  
}
