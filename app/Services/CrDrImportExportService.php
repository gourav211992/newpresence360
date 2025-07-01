<?php

namespace App\Services;

use App\Helpers\ConstantHelper;
use App\Models\Ledger;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Models\Group;
use App\Models\Voucher;
use Exception;

class CrDrImportExportService
{
    public function validateImportRow(array $row)
    {
        $requiredFields = [
            'ledger_name'   => 'Ledger Name',
            'ledger_group'  => 'Ledger Group',
            'document_no'   => 'Voucher No',
            'settle_amount' => 'Settle Amount',
            'balance'       => 'Balance'
        ];
        // dd($requiredFields);

        foreach ($requiredFields as $key => $label) {
            if (!isset($row[$key]) || is_null($row[$key]) || $row[$key] === '') {
                throw new \Exception("Required field '{$label}' is missing in import file.");
            }
        }
        return true;
    }

    public function validateNumericAmounts(array $row)
    {
        if (!is_numeric($row['settle_amount'])) {
            throw new \Exception("Settle Amount must be a valid number.");
        }
        if (!is_numeric($row['balance'])) {
            throw new \Exception("Balance must be a valid number.");
        }
        return true;
    }

    public function validateBalanceInVoucher($balance, $settleAmount, $voucherId,  $type, $ledgerId, $ledgerGroupId)
    {
        // getVoucherBalance
        $Voucherbalance = Helper::getVoucherBalance($settleAmount, $voucherId, $type, $ledgerId, $ledgerGroupId);
        // dd($Voucherbalance,$settleAmount, $voucherId, $type, $ledgerId, $ledgerGroupId);
        \Log::info('Voucher Balance: ', ['Voucherbalance' => $Voucherbalance]);
        // Assuming $voucher is an Eloquent model with a debit_amt property
        if ($Voucherbalance != $balance) {
            throw new \Exception("Given Balance {$balance} does not match voucher amount ({$Voucherbalance}).");
        }
        return true;
    }

    public function validateSettleVsBalance($settleAmount, $balance)
    {
        $balance = $this->normalizeNumber($balance);
        $settleAmount = $this->normalizeNumber($settleAmount);

        if ($balance == 0) {
            throw new \Exception("Balance must not be zero.");
        }
        if ($settleAmount > $balance) {
            throw new \Exception("Settle Amount ({$settleAmount}) cannot be greater than Balance ({$balance}).");
        }
        return true;
    }

    protected function normalizeNumber($value)
    {
        if (is_null($value)) {
            return 0;
        }
        $cleaned = str_replace(',', '', trim($value));
        return floatval($cleaned);
    }
    public function checkLedger(string $ledgerName): Ledger
    {
        $ledger = Ledger::withDefaultGroupCompanyOrg()
            ->where('name', $ledgerName)
            ->first();

        if (!$ledger) {
            throw new \Exception("Ledger '{$ledgerName}' does not exist.");
        }

        return $ledger;
    }

    public function checkLedgerGroup(string $groupName): Group
    {
        $group = Group::withDefaultGroupCompanyOrg()
            ->where('name', $groupName)
            ->first();

        if (!$group) {
            throw new \Exception("Ledger group '{$groupName}' does not exist.");
        }

        return $group;
    }

    // public function checkLedgerParentGroup(string $parentGroupName): bool
    // {
    //     $group = Group::withDefaultGroupCompanyOrg()
    //         ->where('name', $parentGroupName)
    //         ->first();

    //     if (!$group) {
    //         throw new \Exception("Parent group '{$parentGroupName}' does not exist.");
    //     }
    //     $groupIds = is_array($group->id) ? $group->id : [$group->id];
    //     $hasLedgers = Ledger::withDefaultGroupCompanyOrg()
    //         ->where('ledger_group_id', $groupIds)
    //         ->exists();

    //     if (!$hasLedgers) {
    //         throw new \Exception("No ledgers found under parent group '{$parentGroupName}'.");
    //     }

    //     return true;
    // }
    public function checkLedgerParentGroup(string $parentGroupName, Ledger $ledger): bool
    {
        $group = Group::withDefaultGroupCompanyOrg()
            ->where('name', $parentGroupName)
            ->first();

        if (!$group) {
            throw new \Exception("Parent group '{$parentGroupName}' does not exist.");
        }

        // Check if the given ledger belongs to this group
 // Ledger group_id can be JSON array or int
    $ledgerGroupId = $ledger->ledger_group_id;

    // Decode if JSON array, else use as is
    $ids = is_string($ledgerGroupId) && $this->isJson($ledgerGroupId)
        ? json_decode($ledgerGroupId, true)
        : (array)$ledgerGroupId;

    if (!in_array($group->id, $ids)) {
        throw new \Exception("Ledger '{$ledger->name}' does not belong to parent group '{$parentGroupName}'.");
    }

        return true;
    }

    public function checkVoucheNoWithLedger($ledgerGroupIds, Ledger $ledger, ?string $voucherNo = null)
    {
        $accessibleLocations = InventoryHelper::getAccessibleLocations();
        $locationIds = $accessibleLocations->pluck('id')->toArray();
          $ids = is_string($ledgerGroupIds) && $this->isJson($ledgerGroupIds)
        ? json_decode($ledgerGroupIds, true)
        : (array)$ledgerGroupIds;
// dd($ledgerGroupIds);
        $voucherQuery = Voucher::withDefaultGroupCompanyOrg()
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->whereIn('location', $locationIds)
            ->withWhereHas('items', function ($query) use ($ledger, $ids) {
                $query->where('ledger_id', $ledger->id)
                    ->whereIn('ledger_parent_id', $ids);
            });

        if ($voucherNo) {
            $voucherQuery->where('voucher_no', $voucherNo);
        }

        $voucher = $voucherQuery->first();

        if (!$voucher) {
            throw new \Exception("Voucher with ledger '{$ledger->name}'" . ($voucherNo ? " and Document no '{$voucherNo}'" : "") . " does not exist.");
        }

        return $voucher;
    }

    protected function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
