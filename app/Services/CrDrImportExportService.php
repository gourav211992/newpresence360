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
            'document_no'   => 'Voucher No',
            'series'   => 'Series',
            'settle_amount' => 'Settle Amount',
            'balance'       => 'Balance'
        ];

        foreach ($requiredFields as $key => $label) {
            if (!isset($row[$key]) || is_null($row[$key]) || $row[$key] === '') {
                throw new Exception("Required field '{$label}' is missing in import file.");
        
            }
        }
        return true;
    }
    public function processData(array $row,$type){
        $ledgerName   = isset($row['ledger_name']) ? trim($row['ledger_name']) : null;
        $ledgerGroup  = isset($row['ledger_group']) ? trim($row['ledger_group']) : null;
        $voucherNo    = isset($row['document_no']) ? trim($row['document_no']) : null;
        $settleAmount = isset($row['settle_amount']) ? trim($row['settle_amount']) : null;
        $balance      = isset($row['balance']) ? trim($row['balance']) : null;
        $series      = isset($row['series']) ? trim($row['series']) : null;
        
        if (!is_numeric($row['settle_amount'])) {
            throw new Exception("Settle Amount must be a valid number.");
        }
        
        if (!is_numeric($row['balance'])) {
            throw new Exception("Balance must be a valid number.");
        }
        
        
        $ledger = Ledger::withDefaultGroupCompanyOrg()
            ->where('name', $ledgerName)
            ->first();

        if (empty($ledger)) {
            throw new Exception("Ledger '{$ledgerName}' does not exist.");
        }
        
        $group = Helper::getGroupsQuery()
            ->where('name', $ledgerGroup)
            ->first();

        if (empty($group)) {
            throw new Exception("Ledger group '{$ledgerGroup}' does not exist.");
        }
        
        $invoices = Helper::getVoucherBalance($voucherNo,$type,$ledger->id,$group->id);
        $voucher = collect($invoices->getData()->data)
            ->first(function ($item) use ($voucherNo, $series) {
                // balance & voucher must match
                if ($item->balance <= 0 || $item->voucher_no !== $voucherNo) {
                    return false;
                }

                // if a series is required, check it
                if ($series) {
                    return isset($item->series?->book_code) &&
                        $item->series->book_code === $series;
                }
                return true;
            });

        if (!$voucher) {
            throw new Exception(
                $series
                    ? "Series '{$series}' not exist related to the Voucher no# '{$voucherNo}'."
                    : "Voucher no# '{$voucherNo}' not valid."
            );
        }
        
        $row['voucher_id'] = $voucher->id;
        $row['ledger_id']=$ledger->id;
        $row['ledger_group_id']=$group->id;
        
        $voucherBalance = $voucher->balance;
        if($balance!=$voucherBalance){
             throw new Exception("Balance not match expected balance ".$voucherBalance.' found '.$balance);
        }
        
        $balance = Helper::removeCommas($balance);
        $settleAmount = Helper::removeCommas($settleAmount);

        if ($balance == 0) {
            throw new Exception("Balance must not be zero.");
        }
        if ($settleAmount > $balance) {
            throw new Exception("Settle Amount ({$settleAmount}) cannot be greater than Balance ({$balance}).");
        }
        return $row;
    }    
}
