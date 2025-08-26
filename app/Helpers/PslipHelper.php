<?php

namespace App\Helpers;

use App\Models\Book;
use App\Models\Currency;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\ErpProductionSlip;
use Illuminate\Support\Collection;
use App\Models\OrganizationBookParameter;

class PslipHelper
{
    public static function pslipVoucherDetails(int $documentId, string $type)
    {
        $document = ErpProductionSlip::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }

        $postingArray = array(
            FinancialPostingHelper::WIP_ACCOUNT => [],
            FinancialPostingHelper::RM_ACCOUNT => []
        );

        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;

        //Status to check if all ledger entries were properly set
        $isLastStation = null;
        if($document->is_last_station) {
            $isLastStation = $document->is_last_station;
        }
        $ledgerErrorStatus = null;
        //COGS SETUP
        foreach ($document->items as $docItemKey => $docItem) {
            $itemValue = round(($docItem->rate * $docItem->qty), 2);
            $WipDebitAccount = $itemValue;
            if($isLastStation) {
                $WipLedgerDetails = AccountHelper::getStockLedgerGroupAndLedgerId($document->organization_id, $docItem->item_id, $document->book_id);
            } else{
                $WipLedgerDetails = AccountHelper::getWipLedgerGroupAndLedgerId($document->organization_id, $docItem->item_id, $document->book_id);
            }

            $wipLedgerId = is_a($WipLedgerDetails, Collection::class) ? @$WipLedgerDetails->first()['ledger_id'] : null;
            $wipLedgerGroupId = is_a($WipLedgerDetails, Collection::class) ? @$WipLedgerDetails->first()['ledger_group'] : null;
            $wipLedger = Ledger::find($wipLedgerId);
            $wipLedgerGroup = Group::find($wipLedgerGroupId);
            //LEDGER NOT FOUND
            if (!isset($wipLedger) || !isset($wipLedgerGroup)) {
                if($isLastStation) {
                    $ledgerErrorStatus = FinancialPostingHelper::ERROR_PREFIX . 'FG Stock Account not setup';
                }else{
                    $ledgerErrorStatus = FinancialPostingHelper::ERROR_PREFIX . 'WIP Account not setup';
                }
                break;
            }

            //Check for same ledger and group in WIP ACCOUNT
            $existingstockLedger = array_filter($postingArray[FinancialPostingHelper::WIP_ACCOUNT], function ($posting) use ($wipLedgerId, $wipLedgerGroupId) {
                return $posting['ledger_id'] == $wipLedgerId && $posting['ledger_group_id'] == $wipLedgerGroupId;
            });

            //Ledger found
            if (count($existingstockLedger) > 0) {
                $postingArray[FinancialPostingHelper::WIP_ACCOUNT][0]['debit_amount'] += $WipDebitAccount;
            } else { //Assign a new ledger
                array_push($postingArray[FinancialPostingHelper::WIP_ACCOUNT], [
                    'ledger_id' => $wipLedgerId,
                    'ledger_group_id' => $wipLedgerGroupId,
                    'ledger_code' => $wipLedger?->code,
                    'ledger_name' => $wipLedger?->name,
                    'ledger_group_code' => $wipLedgerGroup?->name,
                    'credit_amount' => 0,
                    'debit_amount' => $WipDebitAccount
                ]);
            }
        }

        foreach ($document->consumptions as $docItemKey => $docItem) {
            $itemValue = round(($docItem->rate * $docItem->consumption_qty), 2);
            $stockCreditAccount = $itemValue;
            $stockLedgerDetails = AccountHelper::getStockLedgerGroupAndLedgerId($document->organization_id, $docItem->item_id, $document->book_id);

            $stockLedgerId = is_a($stockLedgerDetails, Collection::class) ? @$stockLedgerDetails->first()['ledger_id'] : null;
            $stockLedgerGroupId = is_a($stockLedgerDetails, Collection::class) ? @$stockLedgerDetails->first()['ledger_group'] : null;
            $stockLedger = Ledger::find($stockLedgerId);
            $stockLedgerGroup = Group::find($stockLedgerGroupId);
            //LEDGER NOT FOUND
            if (!isset($stockLedger) || !isset($stockLedgerGroup)) {
                $ledgerErrorStatus = FinancialPostingHelper::ERROR_PREFIX . 'RM Stock Account not setup';
                break;
            }
            // Check for existing Stock ACCOUNT
            $existingStockLedger = array_filter($postingArray[FinancialPostingHelper::RM_ACCOUNT], function ($posting) use ($stockLedgerId, $stockLedgerGroupId) {
                return $posting['ledger_id'] == $stockLedgerId && $posting['ledger_group_id'] === $stockLedgerGroupId;
            });
            //Ledger found
            if (count($existingStockLedger) > 0) {
                $postingArray[FinancialPostingHelper::RM_ACCOUNT][0]['credit_amount'] += $stockCreditAccount;
            } else { //Assign new ledger
                array_push($postingArray[FinancialPostingHelper::RM_ACCOUNT], [
                    'ledger_id' => $stockLedgerId,
                    'ledger_group_id' => $stockLedgerGroupId,
                    'ledger_code' => $stockLedger?->code,
                    'ledger_name' => $stockLedger?->name,
                    'ledger_group_code' => $stockLedgerGroup?->name,
                    'credit_amount' => $stockCreditAccount,
                    'debit_amount' => 0
                ]);
            }
        }

        //Check if All Legders exists and posting is properly set
        if ($ledgerErrorStatus) {
            return array(
                'status' => false,
                'message' => $ledgerErrorStatus,
                'data' => []
            );
        }
        //Check debit and credit tally
        foreach ($postingArray as $postAccount) {
            foreach ($postAccount as $postingValue) {
                $totalCreditAmount += $postingValue['credit_amount'];
                $totalDebitAmount += $postingValue['debit_amount'];
            }
        }

        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => FinancialPostingHelper::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->org_currency_id);
        $userData = Helper::userCheck();
        $voucherHeader = [
            'voucher_no' => $document->document_number,
            'document_date' => $document->document_date,
            'book_id' => $glPostingBookId,
            'date' => $document->document_date,
            'amount' => $totalCreditAmount,
            'currency_id' => $document->currency_id,
            'currency_code' => $document->currency_code,
            'org_currency_id' => $document->org_currency_id,
            'org_currency_code' => $document->org_currency_code,
            'org_currency_exg_rate' => $document->org_currency_exg_rate,
            'comp_currency_id' => $document->comp_currency_id,
            'comp_currency_code' => $document->comp_currency_code,
            'comp_currency_exg_rate' => $document->comp_currency_exg_rate,
            'group_currency_id' => $document->group_currency_id,
            'group_currency_code' => $document->group_currency_code,
            'group_currency_exg_rate' => $document->group_currency_exg_rate,
            'reference_service' => $book?->service?->alias,
            'reference_doc_id' => $document->id,
            'group_id' => $document->group_id,
            'company_id' => $document->company_id,
            'organization_id' => $document->organization_id,
            'voucherable_type' => $userData['user_type'],
            'voucherable_id' => $userData['user_id'],
            'document_status' => ConstantHelper::APPROVED,
            'approvalLevel' => $document->approval_level,
            'location' => $document?->store_id
        ];
        $voucherDetails = FinancialPostingHelper::generateVoucherDetailsArray($postingArray, $voucherHeader, $document, 'org_currency_id');

        return array(
            'status' => true,
            'message' => 'Posting Details found',
            'data' => [
                    'voucher_header' => $voucherHeader,
                    'voucher_details' => $voucherDetails,
                    'document_date' => $document->document_date,
                    'ledgers' => $postingArray,
                    'total_debit' => $totalDebitAmount,
                    'total_credit' => $totalCreditAmount,
                    'book_code' => $book?->book_code,
                    'document_number' => $document->document_number,
                    'currency_code' => $currency?->short_name
                ]
        );
    }
}
