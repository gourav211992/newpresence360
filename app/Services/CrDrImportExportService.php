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
            // 'ledger_group'  => 'Ledger Group',
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

        $validationErrors = [];
        $reportedLedgers = [];

        $ledger = Ledger::withDefaultGroupCompanyOrg()
            ->with('customer', 'vendor')
            ->where('name', $ledgerName)
            ->first();

        $relation = $type == ConstantHelper::RECEIPTS_SERVICE_ALIAS ? 'customer' : 'vendor';
        $ledgerNameDisplay = $ledger ? $ledger->name : ($row['ledger_name'] ?? 'Unknown Ledger');

        // Relation missing
        if (!$ledger || !$ledger->{$relation}) {
            if (!in_array($ledgerNameDisplay, $reportedLedgers)) {
                $validationErrors[] = "{$ledgerNameDisplay}'s {$relation} is missing";
                $reportedLedgers[] = $ledgerNameDisplay;
            }
        }

        // Credit days check
        $creditDays = $ledger->{$relation}->credit_days ?? null;
        if ($creditDays === null || $creditDays === '' || $creditDays == 0) {
            if (!in_array($ledgerNameDisplay, $reportedLedgers)) {
                $validationErrors[] = "{$ledgerNameDisplay}'s {$relation} has no credit days set";
                $reportedLedgers[] = $ledgerNameDisplay;
            }
        }
        if (empty($ledger)) {
            return [
                'status' => false,
                'row' => $row,
                'error' => "Ledger '{$ledgerName}' does not exist."
            ];
        }

        $group = $ledger->group()->first();
        $row['ledger_group']    =  $group?->name;
        $row['ledger_group_id'] = $group?->id;

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

         // Return here if validation errors exist
        if (!empty($validationErrors)) {
            return [
                'status' => false,
                'row' => $row,
                'error' => implode(', ', $validationErrors),
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
