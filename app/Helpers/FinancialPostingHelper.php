<?php

namespace App\Helpers;

use App\Models\Book;
use App\Models\ErpProductionSlip;
use App\Models\Scopes\DefaultGroupCompanyOrgScope;
use App\Models\ErpPsvHeader;
use App\Models\FixedAssetRegistration;
use Illuminate\Support\Facades\DB;
use App\Models\ErpSaleReturn;
use App\Models\LandLease;
use App\Models\FixedAssetDepreciation;
use App\Models\LandLeasePlot;
use App\Models\FixedAssetSplit;
use App\Models\FixedAssetMerger;
use App\Models\FixedAssetRevImp;
use App\Models\FixedAssetSub;
use App\Models\VoucherReference;
use App\Models\CostCenter;
use App\Models\Organization;

use App\Models\StockLedger;
use App\Models\Vendor;
use App\Models\Currency;
use App\Models\Customer;
use App\Models\DiscountMaster;
use Illuminate\Http\Request;
use App\Models\DocumentApproval;
use App\Models\ErpInvoiceItem;
use App\Models\ErpSaleInvoice;
use App\Models\ErpSaleInvoiceTed;
use App\Models\ErpSaleReturnTed;
use App\Models\ErpSaleOrder;
use App\Models\ExpenseMaster;
use App\Models\Group;
use App\Models\ItemDetail;
use App\Models\Ledger;
use App\Models\OrganizationBookParameter;
use App\Models\Service;
use App\Models\TaxDetail;
use App\Models\PaymentVoucher;
use App\Models\Voucher;
use App\Models\HomeLoan;
use App\Models\LoanDisbursement;
use App\Models\LoanFinancialAccount;
use App\Models\OrganizationCompany;
use App\Models\CurrencyExchange;
use App\Models\LoanProcessFee;
use App\Models\RecoveryLoan;
use App\Models\LoanSettlement;
use App\Models\ErpTransportInvoice;
use App\Models\ErpTransportInvoiceTed;

use App\Models\MrnHeader;
use App\Models\MrnDetail;
use App\Models\MrnExtraAmount;
use Illuminate\Support\Collection;

use App\Models\PbHeader;
use App\Models\PbDetail;
use App\Models\PbTed;

use App\Models\PRHeader;
use App\Models\PRDetail;
use App\Models\PRTed;

use App\Models\ExpenseHeader;
use App\Models\ExpenseDetail;
use App\Models\ExpenseTed;
use App\Models\MfgOrder;

class FinancialPostingHelper
{
    const DEBIT = "Debit";
    const CONTRA = "Contra";
    const CREDIT = "Credit";
    const COGS_ACCOUNT = 'COGS';
    const SALES_ACCOUNT = 'Sales';
    const STOCK_ACCOUNT = 'Stock';
    const SERVICE_ACCOUNT = 'Service';
    const PHYSICAL_STOCK_VARIANCE_ACCOUNT = 'PSVA';
    const WIP_ACCOUNT = 'FG/WIP';
    const RM_ACCOUNT = 'RM';
    const CUSTOMER_ACCOUNT = 'Customer';
    const VENDOR = 'Vendor';
    const OLD_ASSET = 'Old Asset';
    const NEW_ASSET = 'New Asset';
    const PAYMENT_ACCOUNT = 'Payment';
    const VENDOR_ACCOUNT = 'Party';
    const FIXEDASSET_ACCOUNT = 'Fixed Asset';
    const WRITE_OFF_ACCOUNT = 'Writeoff';
    const Loan_Customer_Receivable_ACCOUNT = 'Loan Customer Receivable';

    const INTEREST_ACCOUNT = 'Interest';
    const ProcessFee_ACCOUNT = 'ProcessFee';
    const Bank_ACCOUNT = 'Bank';

    const ASSET = 'Asset';

    const DEPRECIATION = 'Depreciation';
    const SUPPLIER_ACCOUNT = 'Supplier';
    const TAX_ACCOUNT = 'Tax';
    const CGST_TAX_ACCOUNT = 'CGST Tax';
    const IGST_TAX_ACCOUNT = 'IGST Tax';
    const SGST_TAX_ACCOUNT = 'SGST Tax';
    const EXPENSE_ACCOUNT = 'Expense';
    const SURPLUS_ACCOUNT = 'Surplus';
    const DISCOUNT_ACCOUNT = 'Discount';
    const GRIR_ACCOUNT = 'GR/IR';
    const PR_ACCOUNT = 'Purchase Return';

    const PV_ACCOUNT = 'Price Variance';
    const ERROR_PREFIX = "Error while posting : ";
    const DN_SERVICE_POSTING_ACCOUNT = [
        self::COGS_ACCOUNT => self::DEBIT,
        self::STOCK_ACCOUNT => self::CREDIT,
    ];
    const SI_SERVICE_POSTING_ACCOUNT = [
        self::CUSTOMER_ACCOUNT => self::DEBIT,
        self::SALES_ACCOUNT => self::CREDIT,
        self::TAX_ACCOUNT => self::CREDIT,
        // self::DISCOUNT_ACCOUNT => self::DEBIT,
    ];
    const LOAN_POSTING_ACCOUNT = [
        self::Bank_ACCOUNT => self::DEBIT,
        self::ProcessFee_ACCOUNT => self::CREDIT,
    ];
    const DIS_POSTING_ACCOUNT = [
        self::Bank_ACCOUNT => self::CREDIT,
        self::Loan_Customer_Receivable_ACCOUNT => self::DEBIT,
    ];

    const LOAN_RECOVER_POSTING_ACCOUNT = [
        self::Bank_ACCOUNT => self::DEBIT,
        self::CUSTOMER_ACCOUNT => self::CREDIT,
        self::INTEREST_ACCOUNT => self::CREDIT,
    ];
    const PSV_POSTING_ACCOUNT = [
        self::STOCK_ACCOUNT => self::DEBIT,
        self::PHYSICAL_STOCK_VARIANCE_ACCOUNT => self::CREDIT,
    ];
    const PAYMENT_VOUCHER_RECEIPT_POSTING_ACCOUNT = [
        self::Bank_ACCOUNT => self::DEBIT,
        self::CUSTOMER_ACCOUNT => self::CREDIT,
    ];
    const FIXED_ASSET_DEPRECIATION_POSTING_ACCOUNT = [
        self::DEPRECIATION => self::DEBIT,
        self::ASSET => self::CREDIT,
    ];
    const FIXED_ASSET_SPLIT_MERGER_POSTING_ACCOUNT = [
        self::NEW_ASSET => self::DEBIT,
        self::OLD_ASSET => self::CREDIT,
    ];
    const FIXED_ASSET_POSTING_ACCOUNT = [
        self::VENDOR_ACCOUNT => self::CREDIT,
        self::TAX_ACCOUNT => self::DEBIT,
        self::FIXEDASSET_ACCOUNT => self::DEBIT,
    ];

    const LOAN_SETTLE_POSTING_ACCOUNT = [
        self::CUSTOMER_ACCOUNT => self::CREDIT,
        self::WRITE_OFF_ACCOUNT => self::DEBIT,
    ];


    const DN_CUM_INVOICE_SERVICE_POSTING_ACCOUNT = [
        self::CUSTOMER_ACCOUNT => self::DEBIT,
        self::SALES_ACCOUNT => self::CREDIT,
        self::TAX_ACCOUNT => self::CREDIT,
        self::DISCOUNT_ACCOUNT => self::DEBIT,
        self::COGS_ACCOUNT => self::DEBIT,
        self::STOCK_ACCOUNT => self::CREDIT,
    ];
    const SERVICE_POSTING_MAPPING = [
        ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS => self::DN_SERVICE_POSTING_ACCOUNT,
        ConstantHelper::SI_SERVICE_ALIAS => self::SI_SERVICE_POSTING_ACCOUNT,
        ConstantHelper::HOMELOAN => self::LOAN_POSTING_ACCOUNT,
        ConstantHelper::VEHICLELOAN => self::LOAN_POSTING_ACCOUNT,
        ConstantHelper::TERMLOAN => self::LOAN_POSTING_ACCOUNT,
        ConstantHelper::LOAN_DISBURSEMENT => self::DIS_POSTING_ACCOUNT,
        ConstantHelper::LOAN_RECOVERY => self::LOAN_RECOVER_POSTING_ACCOUNT,
        ConstantHelper::LOAN_SETTLEMENT => self::LOAN_SETTLE_POSTING_ACCOUNT,
        ConstantHelper::PAYMENTS_SERVICE_ALIAS => self::PAYMENT_VOUCHER_RECEIPT_POSTING_ACCOUNT,
        ConstantHelper::RECEIPTS_SERVICE_ALIAS => self::PAYMENT_VOUCHER_RECEIPT_POSTING_ACCOUNT,
        ConstantHelper::FIXED_ASSET_DEPRECIATION => self::FIXED_ASSET_DEPRECIATION_POSTING_ACCOUNT,
        ConstantHelper::FIXED_ASSET_MERGER => self::FIXED_ASSET_SPLIT_MERGER_POSTING_ACCOUNT,
        ConstantHelper::FIXED_ASSET_SPLIT => self::FIXED_ASSET_SPLIT_MERGER_POSTING_ACCOUNT,
        ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS => self::DN_CUM_INVOICE_SERVICE_POSTING_ACCOUNT,
        ConstantHelper::PSV_SERVICE_ALIAS => self::PSV_POSTING_ACCOUNT,
    ];

    public static function financeVoucherPosting(int $bookId, int $documentId, string $type, bool $onApproval = false)
    {

        $contra_entries = [];
        //Check Book
        $book = Book::find($bookId);
        if (!isset($book)) {
            return array(
                'status' => false,
                'message' => 'Book not found',
                'data' => []
            );
        }
        //Check Service
        $service = Service::find($book->service_id);
        if (!isset($service)) {
            return array(
                'status' => false,
                'message' => 'Service not found',
                'data' => []
            );
        }
        $isFinanceVoucherDefined = ServiceParametersHelper::getFinancialServiceAlias($service->alias);
        if (!isset($isFinanceVoucherDefined)) {
            return array(
                'status' => false,
                'message' => '',
                'data' => []
            );
        }
        //Check Posting parameters
        $financialPostParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM)->first();
        if (!isset($financialPostParam)) {
            return array(
                'status' => false,
                'message' => 'GL Posting Parameter not specified',
                'data' => []
            );
        }
        $isPostingRequired = (($financialPostParam->parameter_value[0] ?? '') === 'yes' ? true : false);
        if (!$isPostingRequired) {
            return array(
                'status' => false,
                'message' => '',
                'data' => []
            );
        }
        //Check if this helper is called upon approval
        if ($onApproval) {
            $postOnApproveParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::POST_ON_ARROVE_PARAM)->first();
            if (!isset($postOnApproveParam)) {
                return array(
                    'status' => false,
                    'message' => 'Post on Approval Parameter not found',
                    'data' => []
                );
            }
            $isPostOnApprovalRequired = (($postOnApproveParam->parameter_value[0] ?? '') === "yes" ? true : false);
            if (!$isPostOnApprovalRequired) {
                return array(
                    'status' => false,
                    'message' => '',
                    'data' => []
                );
            }
        }
        $serviceAlias = $service->alias;

        if ($type === "view") {
            if ($serviceAlias === ConstantHelper::PAYMENTS_SERVICE_ALIAS || $serviceAlias === ConstantHelper::RECEIPTS_SERVICE_ALIAS)
                return self::getPaymentDocumentPostedVouchers($documentId, $service->alias);
            else
                return self::getDocumentPostedVoucher($documentId, $service->alias);

        }
        //Call helpers according to service

        if ($serviceAlias === ConstantHelper::SI_SERVICE_ALIAS) {
            $entries = self::dnVoucherDetails($documentId, $type);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::TI_SERVICE_ALIAS) {
            $entries = self::transportVoucherDetails($documentId, $type);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::SERVICE_INV_SERVICE_ALIAS) {
            $entries = self::invoiceCumDnVoucherDetails($documentId, $type);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        }
        // else if ($serviceAlias === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS) {
        //     $entries = self::dnVoucherDetails($documentId, $type);
        //     if (!$entries['status']) {
        //         return array(
        //             'status' => false,
        //             'message' => $entries['message'],
        //             'data' => []
        //         );
        //     }
        // }
        else if ($serviceAlias === ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS) {
            $entries = self::dnVoucherDetails($documentId, $type);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::MRN_SERVICE_ALIAS) {
            $entries = self::mrnVoucherDetails($documentId, $type);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::MO_SERVICE_ALIAS) {
            $entries = self::moVoucherDetails($documentId, $type);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::PB_SERVICE_ALIAS) {
            $entries = self::pbVoucherDetails($documentId, $type);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::EXPENSE_ADVISE_SERVICE_ALIAS) {
            $entries = self::expenseAdviseVoucherDetails($documentId, $type);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::SR_SERVICE_ALIAS) {
            $entries = self::typereturncheck($documentId);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::PURCHASE_RETURN_SERVICE_ALIAS) {
            $entries = self::purchaseReturnVoucherDetails($documentId, $type);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::LEASE_INVOICE_SERVICE_ALIAS) {
            $entries = self::leaseInvoiceVoucherDetails($documentId, $type);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS) {
            $entries = PslipHelper::pslipVoucherDetails($documentId, $type);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::RECEIPTS_SERVICE_ALIAS) {
            $pay = self::receiptInvoiceVoucherDetails($documentId, "");
            if (!$pay['status']) {
                return array(
                    'status' => false,
                    'message' => $pay['message'],
                    'data' => []
                );
            }
            $env[] = $pay;
            $entries = $env;
        } else if ($serviceAlias === ConstantHelper::PSV_SERVICE_ALIAS) {
            $entries = self::PsvVoucherDetails($documentId, "");
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::PAYMENTS_SERVICE_ALIAS) {

            $pay = self::paymentInvoiceVoucherDetails($documentId, '');

            $entries = self::contraVoucherDetails($documentId, '');

            $env = [];
            if (!empty($entries)) {
                $env[] = $pay;
                if (!(isset($entries['status']))) {
                    foreach ($entries as $entry) {
                        foreach ($entry as $e) {
                            $env[] = $e;

                        }
                    }

                } else
                    $env[] = $entries;



            } else {
                $env[] = $pay;
                if (isset($entries['status']) && !$entries['status'])
                    return array(
                        'status' => false,
                        'message' => $entries['message'],
                        'data' => []
                    );

            }
            $entries = $env;





        } else if ($serviceAlias === ConstantHelper::LOAN_DISBURSEMENT) {
            $entries = self::disinvoiceVoucherDetails($documentId, $type);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::LOAN_RECOVERY) {
            $entries = self::loanRecoverInvoiceVoucherDetails($documentId, "");
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::LOAN_SETTLEMENT) {
            $entries = self::loanSettleInvoiceVoucherDetails($documentId, "");
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::FIXED_ASSET_DEPRECIATION) {
            $entries = self::depVoucherDetails($documentId, $type);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::FIXED_ASSET_REV_IMP) {

            $document = FixedAssetRevImp::find($documentId);
            if (!isset($document)) {
                return array(
                    'status' => false,
                    'message' => 'Document not found',
                    'data' => []
                );
            }

            if ($document->document_type == 'impairement') {
                if ($type == "post") {

                    $register = FixedAssetRevImp::updateRegistration($documentId);
                    if (!$register['status']) {
                        return array(
                            'status' => false,
                            'message' => $register['message'],
                            'data' => []
                        );
                    }
                }
                $entries = self::impVoucherDetails($documentId, $type);
                if (!$entries['status']) {
                    return array(
                        'status' => false,
                        'message' => $entries['message'],
                        'data' => []
                    );
                }
            } else if ($document->document_type == 'revaluation') {
                $entries = self::revVoucherDetails($documentId, $type);
                if (!$entries['status']) {
                    return array(
                        'status' => false,
                        'message' => $entries['message'],
                        'data' => []
                    );
                }
            } else {
                $entries = self::writeOffVoucherDetails($documentId, $type);
                if (!$entries['status']) {
                    return array(
                        'status' => false,
                        'message' => $entries['message'],
                        'data' => []
                    );
                }

            }
        } else if ($serviceAlias === ConstantHelper::FIXED_ASSET_MERGER) {
            if ($type == 'post') {
                $register = FixedAssetMerger::makeRegistration($documentId);
                if (!$register['status']) {
                    return array(
                        'status' => false,
                        'message' => $register['message'],
                        'data' => []
                    );
                }
            }

            $entries = self::fixedAssetMergerVoucherDetails($documentId, $type);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::FIXEDASSET) {
            $entries = self::fixedAssetVoucherDetails($documentId, $type);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::FIXED_ASSET_SPLIT) {
            if ($type == 'post') {
                $register = FixedAssetSplit::makeRegistration($documentId);
                if (!$register['status']) {
                    return array(
                        'status' => false,
                        'message' => $register['message'],
                        'data' => []
                    );
                }
            }

            $entries = self::fixedAssetSplitVoucherDetails($documentId, $type);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else if ($serviceAlias === ConstantHelper::HOMELOAN || $serviceAlias === ConstantHelper::VEHICLELOAN || $serviceAlias === ConstantHelper::TERMLOAN) {
            $entries = [];
            $type = "";
        } else {
            $entries = array(
                'status' => false,
                'message' => 'No method found',
                'data' => []
            );
        }
        if ($type === 'post') {
            if (isset($entries['data'])) {
                return self::postVoucher($entries['data']);
            } else {
                $lastResponse = null;
                DB::beginTransaction();

                foreach ($entries as $entry) {
                    if (!$entry['status']) {
                        DB::rollBack();
                        return ['status' => false, 'message' => $entry['message']];
                    }
                    if (!isset($entry['data'])) {
                        DB::rollBack();
                        return ['status' => false, 'message' => 'Invalid entry data'];
                    }

                    $response = self::postVoucherPayment($entry['data']);

                    if ($response['status'] === false) {
                        DB::rollBack();
                        return $response;
                    }

                    $lastResponse = $response;
                }
                DB::commit();

                return $lastResponse; // Only returned if all vouchers succeed
            }
        } else {
            if (!empty($contra_entries))
                return ['contra_entries' => $contra_entries, 'entries' => $entries];
            else
                return $entries;
        }
    }

    public static function getDocumentPostedVoucher(int $documentId, string $serviceAlias)
    {
        $voucher = Voucher::with('items')->where('reference_service', $serviceAlias)->where('reference_doc_id', $documentId)->first();
        if (!isset($voucher)) {
            return array(
                'status' => false,
                'message' => 'No posted voucher found',
                'data' => []
            );
        }
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($voucher->ledger_items as $ledgerItem) {
            $totalDebit += $ledgerItem->debit_amount;
            $totalCredit += $ledgerItem->credit_amount;
        }
        $entries = $voucher->ledger_items->groupBy('entry_type');
        return array(
            'status' => true,
            'message' => 'Voucher found',
            'data' => array(
                'book_code' => $voucher->series?->book_code,
                'currency_code' => $voucher->currency_code,
                'document_date' => $voucher->document_date,
                'document_number' => $voucher->voucher_no,
                'ledgers' => $entries,
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit
            )
        );
    }
    public static function getPaymentDocumentPostedVouchers(int $documentId, string $serviceAlias)
    {
        $vouchers = Voucher::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->with(['ledger_items', 'series'])->where('reference_service', $serviceAlias)
            ->where('reference_doc_id', $documentId)->get();

        if ($vouchers->isEmpty()) {
            return [
                'status' => false,
                'message' => 'No posted vouchers found',
                'data' => []
            ];
        }

        $responseData = [];

        foreach ($vouchers as $voucher) {
            $totalDebit = 0;
            $totalCredit = 0;
            $voucherDetails = $voucher->items;
            $ledgersGrouped = [];

            // Calculate totals and prepare voucher details array


            // Group ledgers by entry_type for detailed ledger info
            $grouped = $voucher->items->groupBy('entry_type');
            foreach ($grouped as $entryType => $items) {
                $ledgersGrouped[$entryType] = $items->map(function ($ledger) {
                    return [
                        'ledger_id' => $ledger->ledger_id,
                        'ledger_group_id' => $ledger->ledger_group_id,
                        'ledger_code' => $ledger->ledger_code,
                        'ledger_name' => $ledger->ledger_name,
                        'cost_center_id' => $ledger->cost_center_id,
                        'cost_name' => $ledger?->costCenter?->name ?? '-',
                        'ledger_group_code' => $ledger->ledger_group_code,
                        'debit_amount' => $ledger->debit_amt_org,
                        'credit_amount' => $ledger->credit_amt_org,
                    ];
                })->toArray();
            }

            // Compose voucher header
            $header = [
                'voucher_no' => $voucher->voucher_no,
                'doc_number_type' => $voucher->doc_number_type ?? null,
                'doc_reset_pattern' => $voucher->doc_reset_pattern ?? null,
                'doc_prefix' => $voucher->doc_prefix ?? null,
                'doc_suffix' => $voucher->doc_suffix ?? null,
                'doc_no' => $voucher->doc_no ?? null,
                'voucher_name' => $voucher->voucher_name,
                'document_date' => $voucher->document_date,
                'book_id' => $voucher->book_id,
                'date' => $voucher->date,
                'amount' => $voucher->amount,
                'location' => $voucher->location,
                'currency_id' => $voucher->currency_id,
                'currency_code' => $voucher->currency_code,
                'org_currency_id' => $voucher->org_currency_id,
                'org_currency_code' => $voucher->org_currency_code,
                'org_currency_exg_rate' => $voucher->org_currency_exg_rate,
                'comp_currency_id' => $voucher->comp_currency_id,
                'comp_currency_code' => $voucher->comp_currency_code,
                'comp_currency_exg_rate' => $voucher->comp_currency_exg_rate,
                'group_currency_id' => $voucher->group_currency_id,
                'group_currency_code' => $voucher->group_currency_code,
                'group_currency_exg_rate' => $voucher->group_currency_exg_rate,
                'reference_service' => $voucher->reference_service,
                'reference_doc_id' => $voucher->reference_doc_id,
                'group_id' => $voucher->group_id,
                'company_id' => $voucher->company_id,
                'organization_id' => $voucher->organization_id,
                'voucherable_type' => $voucher->voucherable_type,
                'voucherable_id' => $voucher->voucherable_id,
                'approvalStatus' => $voucher->approvalStatus,
                'document_status' => $voucher->document_status,
                'approvalLevel' => $voucher->approvalLevel,
                'remarks' => $voucher->remarks ?? '',
            ];

            $responseData[] = [
                'status' => true,
                'message' => 'Posting Details found',
                'data' => [
                    'voucher_header' => $header,
                    'voucher_details' => $voucherDetails,
                    'document_date' => $voucher->document_date,
                    'ledgers' => $ledgersGrouped,
                    'total_debit' => $voucher->items->sum('debit_amt_org'),
                    'total_credit' => $voucher->items->sum('credit_amt_org'),
                    'book_code' => $voucher->series?->book_code,
                    'org' => $voucher->organization?->name ?? 'Organizations Name',
                    'org_id' => $voucher->organization_id,
                    'document_number' => $voucher->voucher_no,
                    'currency_code' => $voucher->currency_code,
                ]
            ];
        }
        return $responseData;
    }

    public static function loanVoucherPosting(int $bookId, int $documentId, string $type, string $remarks = null, Request $request = null)
    {
        if ($remarks == null) {
            $remarks = '';
        }
        //Check Book
        $book = Book::find($bookId);
        if (!isset($book)) {
            return array(
                'status' => false,
                'message' => 'Book not found',
                'data' => []
            );
        }
        //Check Service
        $service = Service::find($book->service_id);
        if (!isset($service)) {
            return array(
                'status' => false,
                'message' => 'Service not found',
                'data' => []
            );
        }
        $isFinanceVoucherDefined = ServiceParametersHelper::getFinancialServiceAlias($service->alias);
        if (!isset($isFinanceVoucherDefined)) {
            return array(
                'status' => false,
                'message' => '',
                'data' => []
            );
        }
        //Check Posting parameters
        $financialPostParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM)->first();
        if (!isset($financialPostParam)) {
            return array(
                'status' => false,
                'message' => 'GL Posting Parameter not specified',
                'data' => []
            );
        }
        $isPostingRequired = (($financialPostParam->parameter_value[0] ?? '') === 'yes' ? true : false);
        if (!$isPostingRequired) {
            return array(
                'status' => false,
                'message' => '',
                'data' => []
            );
        }

        //Call helpers according to service
        $serviceAlias = $service->alias;
        if ($serviceAlias === ConstantHelper::HOMELOAN || $serviceAlias === ConstantHelper::VEHICLELOAN || $serviceAlias === ConstantHelper::TERMLOAN) {
            $entries = self::loaninvoiceVoucherDetails($documentId, $type, $remarks, $request);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else {
            $entries = array(
                'status' => false,
                'message' => 'No method found',
                'data' => []
            );
        }
        if ($type === 'post') {
            $entries['data']['remarks'] = $remarks;
            return self::postVoucher($entries['data']);
        } else {
            return $entries;
        }
    }

    public static function disVoucherPosting(int $bookId, int $documentId, string $type)
    {
        //Check Book
        $book = Book::find($bookId);
        if (!isset($book)) {
            return array(
                'status' => false,
                'message' => 'Book not found',
                'data' => []
            );
        }
        //Check Service
        $service = Service::find($book->service_id);
        if (!isset($service)) {
            return array(
                'status' => false,
                'message' => 'Service not found',
                'data' => []
            );
        }
        $isFinanceVoucherDefined = ServiceParametersHelper::getFinancialServiceAlias($service->alias);
        if (!isset($isFinanceVoucherDefined)) {
            return array(
                'status' => false,
                'message' => '',
                'data' => []
            );
        }
        //Check Posting parameters
        $financialPostParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM)->first();
        if (!isset($financialPostParam)) {
            return array(
                'status' => false,
                'message' => 'GL Posting Parameter not specified',
                'data' => []
            );
        }
        $isPostingRequired = (($financialPostParam->parameter_value[0] ?? '') === 'yes' ? true : false);
        if (!$isPostingRequired) {
            return array(
                'status' => false,
                'message' => '',
                'data' => []
            );
        }

        //Call helpers according to service
        $serviceAlias = $service->alias;
        if ($serviceAlias === ConstantHelper::LOAN_DISBURSEMENT) {
            $entries = self::disinvoiceVoucherDetails($documentId, $type);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else {
            $entries = array(
                'status' => false,
                'message' => 'No method found',
                'data' => []
            );
        }
        if ($type === 'post') {
            return self::postVoucher($entries['data']);
        } else {
            return $entries;
        }
    }

    public static function loanRecoverVoucherPosting(int $bookId, int $documentId, string $type, string $remarks)
    {
        //Check Book
        $book = Book::find($bookId);
        if (!isset($book)) {
            return array(
                'status' => false,
                'message' => 'Book not found',
                'data' => []
            );
        }
        //Check Service
        $service = Service::find($book->service_id);
        if (!isset($service)) {
            return array(
                'status' => false,
                'message' => 'Service not found',
                'data' => []
            );
        }
        $isFinanceVoucherDefined = ServiceParametersHelper::getFinancialServiceAlias($service->alias);
        if (!isset($isFinanceVoucherDefined)) {
            return array(
                'status' => false,
                'message' => '',
                'data' => []
            );
        }
        //Check Posting parameters
        $financialPostParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM)->first();
        if (!isset($financialPostParam)) {
            return array(
                'status' => false,
                'message' => 'GL Posting Parameter not specified',
                'data' => []
            );
        }
        $isPostingRequired = (($financialPostParam->parameter_value[0] ?? '') === 'yes' ? true : false);
        if (!$isPostingRequired) {
            return array(
                'status' => false,
                'message' => '',
                'data' => []
            );
        }

        //Call helpers according to service
        $serviceAlias = $service->alias;
        if ($serviceAlias === ConstantHelper::LOAN_RECOVERY) {
            $entries = self::loanRecoverInvoiceVoucherDetails($documentId, $remarks);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else {
            $entries = array(
                'status' => false,
                'message' => 'No method found',
                'data' => []
            );
        }
        if ($type === 'post') {
            $entries['data']['remarks'] = $remarks;
            return self::postVoucher($entries['data']);
        } else {
            return $entries;
        }
    }
    public static function loanSettleVoucherPosting(int $bookId, int $documentId, string $type, string $remarks)
    {
        //Check Book
        $book = Book::find($bookId);
        if (!isset($book)) {
            return array(
                'status' => false,
                'message' => 'Book not found',
                'data' => []
            );
        }
        //Check Service
        $service = Service::find($book->service_id);
        if (!isset($service)) {
            return array(
                'status' => false,
                'message' => 'Service not found',
                'data' => []
            );
        }
        $isFinanceVoucherDefined = ServiceParametersHelper::getFinancialServiceAlias($service->alias);
        if (!isset($isFinanceVoucherDefined)) {
            return array(
                'status' => false,
                'message' => '',
                'data' => []
            );
        }
        //Check Posting parameters
        $financialPostParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM)->first();
        if (!isset($financialPostParam)) {
            return array(
                'status' => false,
                'message' => 'GL Posting Parameter not specified',
                'data' => []
            );
        }
        $isPostingRequired = (($financialPostParam->parameter_value[0] ?? '') === 'yes' ? true : false);
        if (!$isPostingRequired) {
            return array(
                'status' => false,
                'message' => '',
                'data' => []
            );
        }

        //Call helpers according to service
        $serviceAlias = $service->alias;
        if ($serviceAlias === ConstantHelper::LOAN_SETTLEMENT) {
            $entries = self::loanSettleInvoiceVoucherDetails($documentId, $remarks);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else {
            $entries = array(
                'status' => false,
                'message' => 'No method found',
                'data' => []
            );
        }
        if ($type === 'post') {
            $entries['data']['remarks'] = $remarks;
            return self::postVoucher($entries['data']);
        } else {
            return $entries;
        }
    }

    public static function postVoucher(array $details)
    {
        //Post Voucher
        $exitingVoucher = Voucher::where('reference_service', $details['voucher_header']['reference_service'])->where('reference_doc_id', $details['voucher_header']['reference_doc_id'])->first();
        if ($exitingVoucher) {
            return array(
                'message' => $exitingVoucher->voucher_no . ' Voucher already posted',
                'status' => false
            );
        }
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;
        $details['voucher_header']['approvalStatus'] = $details['voucher_header']['document_status'];
        $voucher = Voucher::create($details['voucher_header']);
        foreach ($details['voucher_details'] as &$voucherDetail) {
            $voucherDetail['voucher_id'] = $voucher->id;
            $totalCreditAmount += $voucherDetail['credit_amt'];
            $totalDebitAmount += $voucherDetail['debit_amt'];
            ItemDetail::create($voucherDetail);
        }
        if (round($totalDebitAmount, 6) !== round($totalCreditAmount, 6)) {
            return array(
                'message' => 'Credit Amount does not match Debit Amount',
                'status' => false
            );
        }
        //Create log
        $userData = Helper::getAuthenticatedUser();

        $referenceModelName = isset(ConstantHelper::SERVICE_ALIAS_MODELS[$voucher->reference_service]) ? ConstantHelper::SERVICE_ALIAS_MODELS[$voucher->reference_service] : null;
        if ($referenceModelName) {
            $referenceModel = resolve("App\\Models\\" . $referenceModelName);
            $referenceDoc = $referenceModel::find($voucher->reference_doc_id);
            if (isset($referenceDoc)) {
                //Post the original document
                $referenceDoc->document_status = ConstantHelper::POSTED;
                $referenceDoc->save();
                $docApproval = new DocumentApproval;
                $docApproval->document_type = $voucher->reference_service;
                $docApproval->document_id = $voucher->reference_doc_id;
                $docApproval->document_name = $referenceModel::class;
                $docApproval->approval_type = ConstantHelper::POSTED;
                $docApproval->approval_date = now();
                $docApproval->revision_number = $referenceDoc->revision_number ?? 0;
                $docApproval->remarks = null;
                $docApproval->user_id = $userData->auth_user_id;
                $user_type = $userData->authenticable_type;
                $docApproval->user_type = $user_type;
                $docApproval->save();
            }
        }
        //Push data in GSTR tables
        $gstrData = GstrHelper::pushVoucherDataToGstrTable($details['voucher_header']['reference_service'], $details['voucher_header']['reference_doc_id']);
        return $gstrData;
    }
    public static function postVoucherPayment(array $details)
    {
        //Post Voucher
        $exitingVoucher = Voucher::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
            ->where('voucher_no', $details['voucher_header']['voucher_no'])
            ->where('book_id', $details['voucher_header']['book_id'])
            ->when(!empty($details['voucher_header']['organization_id']), function ($query) use ($details) {
                $query->where('organization_id', $details['voucher_header']['organization_id']);
            })
            ->when(!empty($details['voucher_header']['group_id']), function ($query) use ($details) {
                $query->where('group_id', $details['voucher_header']['group_id']);
            })
            ->when(!empty($details['voucher_header']['company_id']), function ($query) use ($details) {
                $query->where('company_id', $details['voucher_header']['company_id']);
            })
            ->first();
        if ($exitingVoucher) {
            return array(
                'message' => $exitingVoucher->voucher_no . ' Voucher already posted',
                'status' => false
            );
        }
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;
        $details['voucher_header']['approvalStatus'] = $details['voucher_header']['document_status'];
        $voucher = Voucher::create($details['voucher_header']);
        foreach ($details['voucher_details'] as &$voucherDetail) {
            $voucherDetail['voucher_id'] = $voucher->id;
            $totalCreditAmount += $voucherDetail['credit_amt'];
            $totalDebitAmount += $voucherDetail['debit_amt'];
            ItemDetail::create($voucherDetail);
        }
        if (round($totalDebitAmount, 6) !== round($totalCreditAmount, 6)) {
            return array(
                'message' => 'Credit Amount does not match Debit Amount',
                'status' => false
            );
        }
        //Create log
        $user = Helper::getAuthenticatedUser();

        $referenceModelName = isset(ConstantHelper::SERVICE_ALIAS_MODELS[$voucher->reference_service]) ? ConstantHelper::SERVICE_ALIAS_MODELS[$voucher->reference_service] : null;
        if ($referenceModelName) {
            $referenceModel = resolve("App\\Models\\" . $referenceModelName);
            $referenceDoc = $referenceModel::find($voucher->reference_doc_id);
            if (isset($referenceDoc)) {
                //Post the original document
                $referenceDoc->document_status = ConstantHelper::POSTED;
                $referenceDoc->save();
                $docApproval = new DocumentApproval;
                $docApproval->document_type = $voucher->reference_service;
                $docApproval->document_id = $voucher->reference_doc_id;
                $docApproval->document_name = $referenceModel::class;
                $docApproval->approval_type = ConstantHelper::POSTED;
                $docApproval->approval_date = now();
                $docApproval->revision_number = $referenceDoc->revision_number ?? 0;
                $docApproval->remarks = null;
                $docApproval->user_id = $user->auth_user_id;
                $user_type = $user->authenticable_type;
                $docApproval->user_type = $user_type;
                $docApproval->save();
            }
        }
        //Push data in GSTR tables
        $gstrData = GstrHelper::pushVoucherDataToGstrTable($details['voucher_header']['reference_service'], $details['voucher_header']['reference_doc_id']);
        return $gstrData;
    }

    public static function invoiceVoucherDetails(int $documentId, string $type)
    {
        $accountSetup = isset(self::SERVICE_POSTING_MAPPING[ConstantHelper::SI_SERVICE_ALIAS])
            ? self::SERVICE_POSTING_MAPPING[ConstantHelper::SI_SERVICE_ALIAS] : [];
        if (!isset($accountSetup) || count($accountSetup) == 0) {
            return array(
                'status' => false,
                'message' => 'Account Setup not found',
                'data' => []
            );
        }
        $document = ErpSaleInvoice::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }
        //Make array according to setup
        $postingArray = array(
            self::CUSTOMER_ACCOUNT => [],
            self::DISCOUNT_ACCOUNT => [],
            self::SALES_ACCOUNT => [],
            self::TAX_ACCOUNT => [],
            self::EXPENSE_ACCOUNT => []
        );
        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;
        //Customer Account initialize
        $customer = Customer::find($document->customer_id);
        $customerLedgerId = $customer->ledger_id;
        $customerLedgerGroupId = $customer->ledger_group_id;
        $customerLedger = Ledger::find($customerLedgerId);
        $customerLedgerGroup = Group::find($customerLedgerGroupId);
        //Customer Ledger account not found
        if (!isset($customerLedger) || !isset($customerLedgerGroup)) {
            return array(
                'status' => false,
                'message' => 'Customer Ledger not setup',
                'data' => []
            );
        }
        // $discountPostingParam = OrganizationBookParameter::where('book_id', $document -> book_id)
        // -> where('parameter_name', ServiceParametersHelper::GL_SEPERATE_DISCOUNT_PARAM) -> first();
        // if (isset($discountPostingParam)) {
        //     $discountSeperatePosting = $discountPostingParam -> parameter_value[0] === "yes" ? true : false;
        // } else {
        $discountSeperatePosting = false;
        // }
        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;
        foreach ($document->items as $docItemKey => $docItem) {

            //Assign Item values
            $itemValue = $docItem->rate * $docItem->order_qty;
            $itemTotalDiscount = $docItem->header_discount_amount + $docItem->item_discount_amount;
            $itemValueAfterDiscount = $itemValue - $itemTotalDiscount;
            //SALES ACCOUNT
            $salesAccountLedgerDetails = AccountHelper::getLedgerGroupAndLedgerIdForSalesAccount($document->organization_id, $document->customer_id, $docItem->item_id, $document->book_id);

            $salesAccountLedgerId = is_a($salesAccountLedgerDetails, Collection::class) ? $salesAccountLedgerDetails->first()['ledger_id'] : null;
            $salesAccountLedgerGroupId = is_a($salesAccountLedgerDetails, Collection::class) ? $salesAccountLedgerDetails->first()['ledger_group'] : null;
            $salesAccountLedger = Ledger::find($salesAccountLedgerId);
            $salesAccountLedgerGroup = Group::find($salesAccountLedgerGroupId);
            //LEDGER NOT FOUND
            if (!isset($salesAccountLedger) || !isset($salesAccountLedgerGroup)) {
                $ledgerErrorStatus = 'Sales Account Ledger not setup';
                break;
            }
            $salesCreditAmount = $discountSeperatePosting ? $itemValue : $itemValueAfterDiscount;
            //Check for same ledger and group in SALES ACCOUNT
            $existingSalesLedger = array_filter($postingArray[self::SALES_ACCOUNT], function ($posting) use ($salesAccountLedgerId, $salesAccountLedgerGroupId) {
                return $posting['ledger_id'] == $salesAccountLedgerId && $posting['ledger_group_id'] == $salesAccountLedgerGroupId;
            });
            //Ledger found
            if (count($existingSalesLedger) > 0) {
                $postingArray[self::SALES_ACCOUNT][0]['credit_amount'] += $salesCreditAmount;
            } else { //Assign a new ledger
                array_push($postingArray[self::SALES_ACCOUNT], [
                    'ledger_id' => $salesAccountLedgerId,
                    'ledger_group_id' => $salesAccountLedgerGroupId,
                    'ledger_code' => $salesAccountLedger?->code,
                    'ledger_name' => $salesAccountLedger?->name,
                    'ledger_group_code' => $salesAccountLedgerGroup?->name,
                    'credit_amount' => $salesCreditAmount,
                    'debit_amount' => 0
                ]);
            }
            //Check for same ledger and group in CUSTOMER ACCOUNT
            $existingcustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
                return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
            });
            //Ledger found
            if (count($existingcustomerLedger) > 0) {
                $postingArray[self::CUSTOMER_ACCOUNT][0]['debit_amount'] += $itemValueAfterDiscount;
            } else { //Assign a new ledger
                array_push($postingArray[self::CUSTOMER_ACCOUNT], [
                    'ledger_id' => $customerLedgerId,
                    'ledger_group_id' => $customerLedgerGroupId,
                    'ledger_code' => $customerLedger?->code,
                    'ledger_name' => $customerLedger?->name,
                    'ledger_group_code' => $customerLedgerGroup?->name,
                    'debit_amount' => $itemValueAfterDiscount,
                    'credit_amount' => 0
                ]);
            }
        }
        //TAXES ACCOUNT
        $taxes = ErpSaleInvoiceTed::where('sale_invoice_id', $document->id)->where('ted_type', "Tax")->get();
        foreach ($taxes as $tax) {
            $taxDetail = TaxDetail::find($tax->ted_id);
            $taxLedgerId = $taxDetail->ledger_id ?? null; //MAKE IT DYNAMIC
            $taxLedgerGroupId = $taxDetail->ledger_group_id ?? null; //MAKE IT DYNAMIC
            $taxLedger = Ledger::find($taxLedgerId);
            $taxLedgerGroup = Group::find($taxLedgerGroupId);
            if (!isset($taxLedger) || !isset($taxLedgerGroup)) {
                $ledgerErrorStatus = 'Tax Account Ledger not setup';
                break;
            }
            $existingTaxLedger = array_filter($postingArray[self::TAX_ACCOUNT], function ($posting) use ($taxLedgerId, $taxLedgerGroupId) {
                return $posting['ledger_id'] == $taxLedgerId && $posting['ledger_group_id'] === $taxLedgerGroupId;
            });
            //Ledger found
            if (count($existingTaxLedger) > 0) {
                $postingArray[self::TAX_ACCOUNT][0]['credit_amount'] += $tax->ted_amount;
            } else { //Assign a new ledger
                array_push($postingArray[self::TAX_ACCOUNT], [
                    'ledger_id' => $taxLedgerId,
                    'ledger_group_id' => $taxLedgerGroupId,
                    'ledger_code' => $taxLedger?->code,
                    'ledger_name' => $taxLedger?->name,
                    'ledger_group_code' => $taxLedgerGroup?->name,
                    'credit_amount' => $tax->ted_amount,
                    'debit_amount' => 0,
                ]);
            }
            //Tax for CUSTOMER ACCOUNT
            $existingCustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
                return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
            });
            //Ledger found
            if (count($existingCustomerLedger) > 0) {
                $postingArray[self::CUSTOMER_ACCOUNT][0]['debit_amount'] += $tax->ted_amount;
            } else { //Assign new ledger
                array_push($postingArray[self::CUSTOMER_ACCOUNT], [
                    'ledger_id' => $taxLedgerId,
                    'ledger_group_id' => $taxLedgerGroupId,
                    'ledger_code' => $taxLedger?->code,
                    'ledger_name' => $taxLedger?->name,
                    'ledger_group_code' => $taxLedgerGroup?->name,
                    'credit_amount' => 0,
                    'debit_amount' => $tax->ted_amount,
                ]);
            }
        }
        //EXPENSES
        $expenses = ErpSaleInvoiceTed::where('sale_invoice_id', $document->id)->where('ted_type', "Expense")->get();
        foreach ($expenses as $expense) {
            $expenseDetail = ExpenseMaster::find($expense->ted_id);
            $expenseLedgerId = $expenseDetail?->expense_ledger_id; //MAKE IT DYNAMIC - 5
            $expenseLedgerGroupId = $expenseDetail?->expense_ledger_group_id; //MAKE IT DYNAMIC - 9
            $expenseLedger = Ledger::find($expenseLedgerId);
            $expenseLedgerGroup = Group::find($expenseLedgerGroupId);
            if (!isset($expenseLedger) || !isset($expenseLedgerGroup)) {
                $ledgerErrorStatus = 'Expense Account Ledger not setup';
                break;
            }
            $existingExpenseLedger = array_filter($postingArray[self::EXPENSE_ACCOUNT], function ($posting) use ($expenseLedgerId, $expenseLedgerGroupId) {
                return $posting['ledger_id'] == $expenseLedgerId && $posting['ledger_group_id'] === $expenseLedgerGroupId;
            });
            //Ledger found
            if (count($existingExpenseLedger) > 0) {
                $postingArray[self::EXPENSE_ACCOUNT][0]['credit_amount'] += $expense->ted_amount;
            } else { //Assign a new ledger
                array_push($postingArray[self::EXPENSE_ACCOUNT], [
                    'ledger_id' => $expenseLedgerId,
                    'ledger_group_id' => $expenseLedgerGroupId,
                    'ledger_code' => $expenseLedger?->code,
                    'ledger_name' => $expenseLedger?->name,
                    'ledger_group_code' => $expenseLedgerGroup?->name,
                    'credit_amount' => $expense->ted_amount,
                    'debit_amount' => 0,
                ]);
            }
            //Expense for CUSTOMER ACCOUNT
            $existingCustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
                return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
            });
            //Ledger found
            if (count($existingCustomerLedger) > 0) {
                $postingArray[self::CUSTOMER_ACCOUNT][0]['debit_amount'] += $expense->ted_amount;
            } else { //Assign new ledger
                array_push($postingArray[self::EXPENSE_ACCOUNT], [
                    'ledger_id' => $expenseLedgerId,
                    'ledger_group_id' => $expenseLedgerGroupId,
                    'ledger_code' => $expenseLedger?->code,
                    'ledger_name' => $expenseLedger?->name,
                    'ledger_group_code' => $expenseLedgerGroup?->name,
                    'credit_amount' => 0,
                    'debit_amount' => $expense->ted_amount,
                ]);
            }
        }
        //Seperate posting of Discount
        if ($discountSeperatePosting) {
            $discounts = ErpSaleInvoiceTed::where('sale_invoice_id', $document->id)->where('ted_type', "Discount")->get();
            foreach ($discounts as $discount) {
                $discountDetail = DiscountMaster::find($discount->ted_id);
                $discountLedgerId = $discountDetail?->discount_ledger_id; //MAKE IT DYNAMIC
                $discountLedgerGroupId = $discountDetail?->discount_ledger_group_id; //MAKE IT DYNAMIC
                $discountLedger = Ledger::find($discountLedgerId);
                $discountLedgerGroup = Group::find($discountLedgerGroupId);
                if (!isset($discountLedger) || !isset($discountLedgerGroup)) {
                    $ledgerErrorStatus = 'Discount Account Ledger not setup';
                    break;
                }
                $existingDiscountLedger = array_filter($postingArray[self::DISCOUNT_ACCOUNT], function ($posting) use ($discountLedgerId, $discountLedgerGroupId) {
                    return $posting['ledger_id'] == $discountLedgerId && $posting['ledger_group_id'] === $discountLedgerGroupId;
                });
                //Ledger found
                if (count($existingDiscountLedger) > 0) {
                    $postingArray[self::DISCOUNT_ACCOUNT][0]['debit_amount'] += $discount->ted_amount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::DISCOUNT_ACCOUNT], [
                        'ledger_id' => $discountLedgerId,
                        'ledger_group_id' => $discountLedgerGroupId,
                        'ledger_code' => $discountLedger?->code,
                        'ledger_name' => $discountLedger?->name,
                        'ledger_group_code' => $discountLedgerGroup?->name,
                        'debit_amount' => $discount->ted_amount,
                        'credit_amount' => 0,
                    ]);
                }
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
        //Balance does not match
        // if (round($totalDebitAmount,6) !== round($totalCreditAmount,6)) {
        //     return array(
        //         'status' => false,
        //         'message' => 'Credit Amount does not match Debit Amount',
        //         'data' => []
        //     );
        // }
        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
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
        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document);
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
    public static function invoiceCumDnVoucherDetails(int $documentId, string $type)
    {
        $accountSetup = isset(self::SERVICE_POSTING_MAPPING[ConstantHelper::SI_SERVICE_ALIAS])
            ? self::SERVICE_POSTING_MAPPING[ConstantHelper::SI_SERVICE_ALIAS] : [];
        if (!isset($accountSetup) || count($accountSetup) == 0) {
            return array(
                'status' => false,
                'message' => 'Account Setup not found',
                'data' => []
            );
        }
        $document = ErpSaleInvoice::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }
        //Make array according to setup
        $postingArray = array(
            self::CUSTOMER_ACCOUNT => [],
            self::DISCOUNT_ACCOUNT => [],
            self::SALES_ACCOUNT => [],
            self::TAX_ACCOUNT => [],
            self::EXPENSE_ACCOUNT => []
        );
        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;
        //Customer Account initialize
        $customer = Customer::find($document->customer_id);
        $customerLedgerId = $customer->ledger_id;
        $customerLedgerGroupId = $customer->ledger_group_id;
        $customerLedger = Ledger::find($customerLedgerId);
        $customerLedgerGroup = Group::find($customerLedgerGroupId);
        //Customer Ledger account not found
        if (!isset($customerLedger) || !isset($customerLedgerGroup)) {
            return array(
                'status' => false,
                'message' => 'Customer Ledger not setup',
                'data' => []
            );
        }
        $customerAccountDebit = 0;
        // $discountPostingParam = OrganizationBookParameter::where('book_id', $document -> book_id)
        // -> where('parameter_name', ServiceParametersHelper::GL_SEPERATE_DISCOUNT_PARAM) -> first();
        // if (isset($discountPostingParam)) {
        //     $discountSeperatePosting = $discountPostingParam -> parameter_value[0] === "yes" ? true : false;
        // } else {
        $discountSeperatePosting = false;
        // }
        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;
        foreach ($document->items as $docItemKey => $docItem) {

            //Assign Item values
            $itemValue = $docItem->rate * $docItem->order_qty;
            $itemTotalDiscount = $docItem->header_discount_amount + $docItem->item_discount_amount;
            $itemValueAfterDiscount = $itemValue - $itemTotalDiscount;
            //SALES ACCOUNT
            $salesAccountLedgerDetails = AccountHelper::getLedgerGroupAndLedgerIdForSalesAccount($document->organization_id, $document->customer_id, $docItem->item_id, $document->book_id);

            $salesAccountLedgerId = is_a($salesAccountLedgerDetails, Collection::class) ? $salesAccountLedgerDetails->first()['ledger_id'] : null;
            $salesAccountLedgerGroupId = is_a($salesAccountLedgerDetails, Collection::class) ? $salesAccountLedgerDetails->first()['ledger_group'] : null;
            $salesAccountLedger = Ledger::find($salesAccountLedgerId);
            $salesAccountLedgerGroup = Group::find($salesAccountLedgerGroupId);
            //LEDGER NOT FOUND
            if (!isset($salesAccountLedger) || !isset($salesAccountLedgerGroup)) {
                $ledgerErrorStatus = 'Sales Account Ledger not setup';
                break;
            }
            $salesCreditAmount = $discountSeperatePosting ? $itemValue : $itemValueAfterDiscount;
            //Check for same ledger and group in SALES ACCOUNT
            $existingSalesLedger = array_filter($postingArray[self::SALES_ACCOUNT], function ($posting) use ($salesAccountLedgerId, $salesAccountLedgerGroupId) {
                return $posting['ledger_id'] == $salesAccountLedgerId && $posting['ledger_group_id'] == $salesAccountLedgerGroupId;
            });
            //Ledger found
            if (count($existingSalesLedger) > 0) {
                $postingArray[self::SALES_ACCOUNT][0]['credit_amount'] += $salesCreditAmount;
            } else { //Assign a new ledger
                array_push($postingArray[self::SALES_ACCOUNT], [
                    'ledger_id' => $salesAccountLedgerId,
                    'ledger_group_id' => $salesAccountLedgerGroupId,
                    'ledger_code' => $salesAccountLedger?->code,
                    'ledger_name' => $salesAccountLedger?->name,
                    'ledger_group_code' => $salesAccountLedgerGroup?->name,
                    'credit_amount' => $salesCreditAmount,
                    'debit_amount' => 0
                ]);
            }
            // //Check for same ledger and group in CUSTOMER ACCOUNT
            // $existingcustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
            //     return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
            // });
            // //Ledger found
            // if (count($existingcustomerLedger) > 0) {
            //     $postingArray[self::CUSTOMER_ACCOUNT][0]['debit_amount'] += $itemValueAfterDiscount;
            // } else { //Assign a new ledger
            //     array_push($postingArray[self::CUSTOMER_ACCOUNT], [
            //         'ledger_id' => $customerLedgerId,
            //         'ledger_group_id' => $customerLedgerGroupId,
            //         'ledger_code' => $customerLedger?->code,
            //         'ledger_name' => $customerLedger?->name,
            //         'ledger_group_code' => $customerLedgerGroup?->name,
            //         'debit_amount' => $itemValueAfterDiscount,
            //         'credit_amount' => 0
            //     ]);
            // }
            $customerAccountDebit += $itemValueAfterDiscount;

        }
        //TAXES ACCOUNT
        $taxes = ErpSaleInvoiceTed::where('sale_invoice_id', $document->id)->where('ted_type', "Tax")->get();
        foreach ($taxes as $tax) {
            $taxDetail = TaxDetail::find($tax->ted_id);
            $taxLedgerId = $taxDetail->ledger_id ?? null; //MAKE IT DYNAMIC
            $taxLedgerGroupId = $taxDetail->ledger_group_id ?? null; //MAKE IT DYNAMIC
            $taxLedger = Ledger::find($taxLedgerId);
            $taxLedgerGroup = Group::find($taxLedgerGroupId);
            if (!isset($taxLedger) || !isset($taxLedgerGroup)) {
                $ledgerErrorStatus = 'Tax Account Ledger not setup';
                break;
            }
            $existingTaxLedger = array_filter($postingArray[self::TAX_ACCOUNT], function ($posting) use ($taxLedgerId, $taxLedgerGroupId) {
                return $posting['ledger_id'] == $taxLedgerId && $posting['ledger_group_id'] === $taxLedgerGroupId;
            });
            //Ledger found
            if (count($existingTaxLedger) > 0) {
                $postingArray[self::TAX_ACCOUNT][0]['credit_amount'] += $tax->ted_amount;
            } else { //Assign a new ledger
                array_push($postingArray[self::TAX_ACCOUNT], [
                    'ledger_id' => $taxLedgerId,
                    'ledger_group_id' => $taxLedgerGroupId,
                    'ledger_code' => $taxLedger?->code,
                    'ledger_name' => $taxLedger?->name,
                    'ledger_group_code' => $taxLedgerGroup?->name,
                    'credit_amount' => $tax->ted_amount,
                    'debit_amount' => 0,
                ]);
            }
            // //Tax for CUSTOMER ACCOUNT
            // $existingCustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
            //     return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
            // });
            // //Ledger found
            // if (count($existingCustomerLedger) > 0) {
            //     $postingArray[self::CUSTOMER_ACCOUNT][0]['debit_amount'] += $tax->ted_amount;
            // } else { //Assign new ledger
            //     array_push($postingArray[self::CUSTOMER_ACCOUNT], [
            //         'ledger_id' => $taxLedgerId,
            //         'ledger_group_id' => $taxLedgerGroupId,
            //         'ledger_code' => $taxLedger?->code,
            //         'ledger_name' => $taxLedger?->name,
            //         'ledger_group_code' => $taxLedgerGroup?->name,
            //         'credit_amount' => 0,
            //         'debit_amount' => $tax->ted_amount,
            //     ]);
            // }
            $customerAccountDebit += $tax->ted_amount;
        }
        //EXPENSES
        $expenses = ErpSaleInvoiceTed::where('sale_invoice_id', $document->id)->where('ted_type', "Expense")->get();
        foreach ($expenses as $expense) {
            $expenseDetail = ExpenseMaster::find($expense->ted_id);
            $expenseLedgerId = $expenseDetail?->expense_ledger_id; //MAKE IT DYNAMIC - 5
            $expenseLedgerGroupId = $expenseDetail?->expense_ledger_group_id; //MAKE IT DYNAMIC - 9
            $expenseLedger = Ledger::find($expenseLedgerId);
            $expenseLedgerGroup = Group::find($expenseLedgerGroupId);
            if (!isset($expenseLedger) || !isset($expenseLedgerGroup)) {
                $ledgerErrorStatus = 'Expense Account Ledger not setup';
                break;
            }
            $existingExpenseLedger = array_filter($postingArray[self::EXPENSE_ACCOUNT], function ($posting) use ($expenseLedgerId, $expenseLedgerGroupId) {
                return $posting['ledger_id'] == $expenseLedgerId && $posting['ledger_group_id'] === $expenseLedgerGroupId;
            });
            //Ledger found
            if (count($existingExpenseLedger) > 0) {
                $postingArray[self::EXPENSE_ACCOUNT][0]['credit_amount'] += $expense->ted_amount;
            } else { //Assign a new ledger
                array_push($postingArray[self::EXPENSE_ACCOUNT], [
                    'ledger_id' => $expenseLedgerId,
                    'ledger_group_id' => $expenseLedgerGroupId,
                    'ledger_code' => $expenseLedger?->code,
                    'ledger_name' => $expenseLedger?->name,
                    'ledger_group_code' => $expenseLedgerGroup?->name,
                    'credit_amount' => $expense->ted_amount,
                    'debit_amount' => 0,
                ]);
            }
            //Expense for CUSTOMER ACCOUNT
            // $existingCustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
            //     return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
            // });
            // //Ledger found
            // if (count($existingCustomerLedger) > 0) {
            //     $postingArray[self::CUSTOMER_ACCOUNT][0]['debit_amount'] += $expense->ted_amount;
            // } else { //Assign new ledger
            //     array_push($postingArray[self::EXPENSE_ACCOUNT], [
            //         'ledger_id' => $expenseLedgerId,
            //         'ledger_group_id' => $expenseLedgerGroupId,
            //         'ledger_code' => $expenseLedger?->code,
            //         'ledger_name' => $expenseLedger?->name,
            //         'ledger_group_code' => $expenseLedgerGroup?->name,
            //         'credit_amount' => 0,
            //         'debit_amount' => $expense->ted_amount,
            //     ]);
            // }
            $customerAccountDebit += $expense->ted_amount;

        }
        //Seperate posting of Discount
        if ($discountSeperatePosting) {
            $discounts = ErpSaleInvoiceTed::where('sale_invoice_id', $document->id)->where('ted_type', "Discount")->get();
            foreach ($discounts as $discount) {
                $discountDetail = DiscountMaster::find($discount->ted_id);
                $discountLedgerId = $discountDetail?->discount_ledger_id; //MAKE IT DYNAMIC
                $discountLedgerGroupId = $discountDetail?->discount_ledger_group_id; //MAKE IT DYNAMIC
                $discountLedger = Ledger::find($discountLedgerId);
                $discountLedgerGroup = Group::find($discountLedgerGroupId);
                if (!isset($discountLedger) || !isset($discountLedgerGroup)) {
                    $ledgerErrorStatus = 'Discount Account Ledger not setup';
                    break;
                }
                $existingDiscountLedger = array_filter($postingArray[self::DISCOUNT_ACCOUNT], function ($posting) use ($discountLedgerId, $discountLedgerGroupId) {
                    return $posting['ledger_id'] == $discountLedgerId && $posting['ledger_group_id'] === $discountLedgerGroupId;
                });
                //Ledger found
                if (count($existingDiscountLedger) > 0) {
                    $postingArray[self::DISCOUNT_ACCOUNT][0]['debit_amount'] += $discount->ted_amount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::DISCOUNT_ACCOUNT], [
                        'ledger_id' => $discountLedgerId,
                        'ledger_group_id' => $discountLedgerGroupId,
                        'ledger_code' => $discountLedger?->code,
                        'ledger_name' => $discountLedger?->name,
                        'ledger_group_code' => $discountLedgerGroup?->name,
                        'debit_amount' => $discount->ted_amount,
                        'credit_amount' => 0,
                    ]);
                }
            }
        }
        //Break Customer Account according to payment terms schedule - due date wise
        $invoicePaymentTerms = $document->payment_term_schedules()
            ->select('due_date', DB::raw('SUM(percent) as total_percentage'))->groupBy('due_date')->get();
        $totalPaymentTermsAmount = 0;
        if ($invoicePaymentTerms && count($invoicePaymentTerms)) {
            foreach ($invoicePaymentTerms as $invoicePaymentTerm) {
                $currentAmount = $customerAccountDebit * ($invoicePaymentTerm->total_percentage / 100);
                $totalPaymentTermsAmount += $currentAmount;
                //Check for same ledger and group in CUSTOMER ACCOUNT
                $existingcustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId, $invoicePaymentTerm) {
                    return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId && $posting['due_date'] === $invoicePaymentTerm->due_date;
                });
                //Ledger found
                if (count($existingcustomerLedger) > 0) {
                    $postingArray[self::CUSTOMER_ACCOUNT][0]['debit_amount'] += $currentAmount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::CUSTOMER_ACCOUNT], [
                        'ledger_id' => $customerLedgerId,
                        'ledger_group_id' => $customerLedgerGroupId,
                        'ledger_code' => $customerLedger?->code,
                        'ledger_name' => $customerLedger?->name,
                        'ledger_group_code' => $customerLedgerGroup?->name,
                        'debit_amount' => $currentAmount,
                        'credit_amount' => 0,
                        'due_date' => $invoicePaymentTerm->due_date,
                    ]);
                }
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
        //Balance does not match
        // if (round($totalDebitAmount,6) !== round($totalCreditAmount,6)) {
        //     return array(
        //         'status' => false,
        //         'message' => 'Credit Amount does not match Debit Amount',
        //         'data' => []
        //     );
        // }
        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
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
        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document);
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
    public static function leaseInvoiceVoucherDetails(int $documentId, string $type)
    {
        $document = ErpSaleInvoice::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }
        //Make array according to setup
        $postingArray = array(
            self::CUSTOMER_ACCOUNT => [],
            self::DISCOUNT_ACCOUNT => [],
            self::SALES_ACCOUNT => [],
            self::TAX_ACCOUNT => [],
            self::EXPENSE_ACCOUNT => []
        );
        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;
        //Customer Account initialize
        $customer = Customer::find($document->customer_id);
        $customerLedgerId = $customer->ledger_id;
        $customerLedgerGroupId = $customer->ledger_group_id;
        $customerLedger = Ledger::find($customerLedgerId);
        $customerLedgerGroup = Group::find($customerLedgerGroupId);
        //Customer Ledger account not found
        if (!isset($customerLedger) || !isset($customerLedgerGroup)) {
            return array(
                'status' => false,
                'message' => 'Customer Ledger not setup',
                'data' => []
            );
        }
        // $discountPostingParam = OrganizationBookParameter::where('book_id', $document -> book_id)
        // -> where('parameter_name', ServiceParametersHelper::GL_SEPERATE_DISCOUNT_PARAM) -> first();
        // if (isset($discountPostingParam)) {
        //     $discountSeperatePosting = $discountPostingParam -> parameter_value[0] === "yes" ? true : false;
        // } else {
        $discountSeperatePosting = false;
        // }
        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;
        foreach ($document->items as $docItemKey => $docItem) {
            //Assign Item values
            $itemValue = $docItem->rate * $docItem->order_qty;
            $itemTotalDiscount = $docItem->header_discount_amount + $docItem->item_discount_amount;
            $itemValueAfterDiscount = $itemValue - $itemTotalDiscount;
            //SALES ACCOUNT
            $landLeasePlot = LandLeasePlot::where('lease_id', $docItem->land_lease_id)->get()->first();
            $landParcelId = $landLeasePlot?->land_parcel_id;
            $salesAccountLedgerDetails = AccountHelper::getLedgerGroupAndLedgerIdForLeaseRevenue($landParcelId, $docItem->lease_item_type);
            $salesAccountLedgerId = $salesAccountLedgerDetails['ledger_id'];
            $salesAccountLedgerGroupId = $salesAccountLedgerDetails['ledger_group_id'];
            $salesAccountLedger = Ledger::find($salesAccountLedgerId);
            $salesAccountLedgerGroup = Group::find($salesAccountLedgerGroupId);
            //LEDGER NOT FOUND
            if (!isset($salesAccountLedger) || !isset($salesAccountLedgerGroup)) {
                $ledgerErrorStatus = 'Lease Revenue Ledger not setup';
                break;
            }
            $salesCreditAmount = $discountSeperatePosting ? $itemValue : $itemValueAfterDiscount;
            //Check for same ledger and group in SALES ACCOUNT
            $existingSalesLedger = array_filter($postingArray[self::SALES_ACCOUNT], function ($posting) use ($salesAccountLedgerId, $salesAccountLedgerGroupId) {
                return $posting['ledger_id'] == $salesAccountLedgerId && $posting['ledger_group_id'] == $salesAccountLedgerGroupId;
            });
            //Ledger found
            if (count($existingSalesLedger) > 0) {
                $postingArray[self::SALES_ACCOUNT][0]['credit_amount'] += $salesCreditAmount;
            } else { //Assign a new ledger
                array_push($postingArray[self::SALES_ACCOUNT], [
                    'ledger_id' => $salesAccountLedgerId,
                    'ledger_group_id' => $salesAccountLedgerGroupId,
                    'ledger_code' => $salesAccountLedger?->code,
                    'ledger_name' => $salesAccountLedger?->name,
                    'ledger_group_code' => $salesAccountLedgerGroup?->name,
                    'credit_amount' => $salesCreditAmount,
                    'debit_amount' => 0
                ]);
            }
            //Check for same ledger and group in CUSTOMER ACCOUNT
            $existingcustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
                return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
            });
            //Ledger found
            if (count($existingcustomerLedger) > 0) {
                $postingArray[self::CUSTOMER_ACCOUNT][0]['debit_amount'] += $itemValueAfterDiscount;
            } else { //Assign a new ledger
                array_push($postingArray[self::CUSTOMER_ACCOUNT], [
                    'ledger_id' => $customerLedgerId,
                    'ledger_group_id' => $customerLedgerGroupId,
                    'ledger_code' => $customerLedger?->code,
                    'ledger_name' => $customerLedger?->name,
                    'ledger_group_code' => $customerLedgerGroup?->name,
                    'debit_amount' => $itemValueAfterDiscount,
                    'credit_amount' => 0
                ]);
            }
        }
        //TAXES ACCOUNT
        $taxes = ErpSaleInvoiceTed::where('sale_invoice_id', $document->id)->where('ted_type', "Tax")->get();
        foreach ($taxes as $tax) {
            $taxDetail = TaxDetail::find($tax->ted_id);
            $taxLedgerId = $taxDetail->ledger_id ?? null; //MAKE IT DYNAMIC
            $taxLedgerGroupId = $taxDetail->ledger_group_id ?? null; //MAKE IT DYNAMIC
            $taxLedger = Ledger::find($taxLedgerId);
            $taxLedgerGroup = Group::find($taxLedgerGroupId);
            if (!isset($taxLedger) || !isset($taxLedgerGroup)) {
                $ledgerErrorStatus = 'Tax Account Ledger not setup';
                break;
            }
            $existingTaxLedger = array_filter($postingArray[self::TAX_ACCOUNT], function ($posting) use ($taxLedgerId, $taxLedgerGroupId) {
                return $posting['ledger_id'] == $taxLedgerId && $posting['ledger_group_id'] === $taxLedgerGroupId;
            });
            //Ledger found
            if (count($existingTaxLedger) > 0) {
                $postingArray[self::TAX_ACCOUNT][0]['credit_amount'] += $tax->ted_amount;
            } else { //Assign a new ledger
                array_push($postingArray[self::TAX_ACCOUNT], [
                    'ledger_id' => $taxLedgerId,
                    'ledger_group_id' => $taxLedgerGroupId,
                    'ledger_code' => $taxLedger?->code,
                    'ledger_name' => $taxLedger?->name,
                    'ledger_group_code' => $taxLedgerGroup?->name,
                    'credit_amount' => $tax->ted_amount,
                    'debit_amount' => 0,
                ]);
            }
            //Tax for CUSTOMER ACCOUNT
            $existingCustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
                return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
            });
            //Ledger found
            if (count($existingCustomerLedger) > 0) {
                $postingArray[self::CUSTOMER_ACCOUNT][0]['debit_amount'] += $tax->ted_amount;
            } else { //Assign new ledger
                array_push($postingArray[self::CUSTOMER_ACCOUNT], [
                    'ledger_id' => $taxLedgerId,
                    'ledger_group_id' => $taxLedgerGroupId,
                    'ledger_code' => $taxLedger?->code,
                    'ledger_name' => $taxLedger?->name,
                    'ledger_group_code' => $taxLedgerGroup?->name,
                    'credit_amount' => 0,
                    'debit_amount' => $tax->ted_amount,
                ]);
            }
        }
        //EXPENSES
        $expenses = ErpSaleInvoiceTed::where('sale_invoice_id', $document->id)->where('ted_type', "Expense")->get();
        foreach ($expenses as $expense) {
            $expenseDetail = ExpenseMaster::find($expense->ted_id);
            $expenseLedgerId = $expenseDetail?->expense_ledger_id; //MAKE IT DYNAMIC - 5
            $expenseLedgerGroupId = $expenseDetail?->expense_ledger_group_id; //MAKE IT DYNAMIC - 9
            $expenseLedger = Ledger::find($expenseLedgerId);
            $expenseLedgerGroup = Group::find($expenseLedgerGroupId);
            if (!isset($expenseLedger) || !isset($expenseLedgerGroup)) {
                $ledgerErrorStatus = 'Expense Account Ledger not setup';
                break;
            }
            $existingExpenseLedger = array_filter($postingArray[self::EXPENSE_ACCOUNT], function ($posting) use ($expenseLedgerId, $expenseLedgerGroupId) {
                return $posting['ledger_id'] == $expenseLedgerId && $posting['ledger_group_id'] === $expenseLedgerGroupId;
            });
            //Ledger found
            if (count($existingExpenseLedger) > 0) {
                $postingArray[self::EXPENSE_ACCOUNT][0]['credit_amount'] += $expense->ted_amount;
            } else { //Assign a new ledger
                array_push($postingArray[self::EXPENSE_ACCOUNT], [
                    'ledger_id' => $expenseLedgerId,
                    'ledger_group_id' => $expenseLedgerGroupId,
                    'ledger_code' => $expenseLedger?->code,
                    'ledger_name' => $expenseLedger?->name,
                    'ledger_group_code' => $expenseLedgerGroup?->name,
                    'credit_amount' => $expense->ted_amount,
                    'debit_amount' => 0,
                ]);
            }
            //Expense for CUSTOMER ACCOUNT
            $existingCustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
                return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
            });
            //Ledger found
            if (count($existingCustomerLedger) > 0) {
                $postingArray[self::CUSTOMER_ACCOUNT][0]['debit_amount'] += $expense->ted_amount;
            } else { //Assign new ledger
                array_push($postingArray[self::EXPENSE_ACCOUNT], [
                    'ledger_id' => $expenseLedgerId,
                    'ledger_group_id' => $expenseLedgerGroupId,
                    'ledger_code' => $expenseLedger?->code,
                    'ledger_name' => $expenseLedger?->name,
                    'ledger_group_code' => $expenseLedgerGroup?->name,
                    'credit_amount' => 0,
                    'debit_amount' => $expense->ted_amount,
                ]);
            }
        }
        //Seperate posting of Discount
        if ($discountSeperatePosting) {
            $discounts = ErpSaleInvoiceTed::where('sale_invoice_id', $document->id)->where('ted_type', "Discount")->get();
            foreach ($discounts as $discount) {
                $discountDetail = DiscountMaster::find($discount->ted_id);
                $discountLedgerId = $discountDetail?->discount_ledger_id; //MAKE IT DYNAMIC
                $discountLedgerGroupId = $discountDetail?->discount_ledger_group_id; //MAKE IT DYNAMIC
                $discountLedger = Ledger::find($discountLedgerId);
                $discountLedgerGroup = Group::find($discountLedgerGroupId);
                if (!isset($discountLedger) || !isset($discountLedgerGroup)) {
                    $ledgerErrorStatus = 'Discount Account Ledger not setup';
                    break;
                }
                $existingDiscountLedger = array_filter($postingArray[self::DISCOUNT_ACCOUNT], function ($posting) use ($discountLedgerId, $discountLedgerGroupId) {
                    return $posting['ledger_id'] == $discountLedgerId && $posting['ledger_group_id'] === $discountLedgerGroupId;
                });
                //Ledger found
                if (count($existingDiscountLedger) > 0) {
                    $postingArray[self::DISCOUNT_ACCOUNT][0]['debit_amount'] += $discount->ted_amount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::DISCOUNT_ACCOUNT], [
                        'ledger_id' => $discountLedgerId,
                        'ledger_group_id' => $discountLedgerGroupId,
                        'ledger_code' => $discountLedger?->code,
                        'ledger_name' => $discountLedger?->name,
                        'ledger_group_code' => $discountLedgerGroup?->name,
                        'debit_amount' => $discount->ted_amount,
                        'credit_amount' => 0,
                    ]);
                }
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
        //Balance does not match
        // if (round($totalDebitAmount,6) !== round($totalCreditAmount,6)) {
        //     return array(
        //         'status' => false,
        //         'message' => 'Credit Amount does not match Debit Amount',
        //         'data' => []
        //     );
        // }
        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
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
        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document);
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



    public static function salesReturnVoucherDetails(int $documentId, string $type)
    {
        $document = ErpSaleReturn::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }
        //Make array according to setup
        $postingArray = array(
            self::SALES_ACCOUNT => [],
            self::TAX_ACCOUNT => [],
            self::EXPENSE_ACCOUNT => [],
            self::DISCOUNT_ACCOUNT => [],
            self::CUSTOMER_ACCOUNT => [],
        );
        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;
        //Customer Account initialize
        $customer = Customer::find($document->customer_id);
        $customerLedgerId = $customer->ledger_id;
        $customerLedgerGroupId = $customer->ledger_group_id;
        $customerLedger = Ledger::find($customerLedgerId);
        $customerLedgerGroup = Group::find($customerLedgerGroupId);
        //Customer Ledger account not found
        if (!isset($customerLedger) || !isset($customerLedgerGroup)) {
            return array(
                'status' => false,
                'message' => 'Customer Ledger not setup',
                'data' => []
            );
        }
        // $discountPostingParam = OrganizationBookParameter::where('book_id', $document -> book_id) -> where('parameter_name', ServiceParametersHelper::GL_SEPERATE_DISCOUNT_PARAM) -> first();
        // if (isset($discountPostingParam)) {
        //     $discountSeperatePosting = $discountPostingParam -> parameter_value[0] === "yes" ? true : false;
        // } else {
        $discountSeperatePosting = false;
        // }
        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;
        foreach ($document->items as $docItemKey => $docItem) {
            //Assign Item values
            $itemValue = $docItem->rate * $docItem->order_qty;
            $itemTotalDiscount = $docItem->header_discount_amount + $docItem->item_discount_amount;
            $itemValueAfterDiscount = $itemValue - $itemTotalDiscount;
            //SALES ACCOUNT
            $salesAccountLedgerDetails = AccountHelper::getLedgerGroupAndLedgerIdForSalesAccount($document->organization_id, $document->customer_id, $docItem->item_id, $document->book_id);
            $salesAccountLedgerId = is_a($salesAccountLedgerDetails, Collection::class) ? $salesAccountLedgerDetails->first()['ledger_id'] : null;
            $salesAccountLedgerGroupId = is_a($salesAccountLedgerDetails, Collection::class) ? $salesAccountLedgerDetails->first()['ledger_group'] : null;
            $salesAccountLedger = Ledger::find($salesAccountLedgerId);
            $salesAccountLedgerGroup = Group::find($salesAccountLedgerGroupId);
            //LEDGER NOT FOUND
            if (!isset($salesAccountLedger) || !isset($salesAccountLedgerGroup)) {
                $ledgerErrorStatus = 'Sales Account Ledger not setup';
                break;
            }
            $salesDebitAmount = $discountSeperatePosting ? $itemValue : $itemValueAfterDiscount;
            //Check for same ledger and group in SALES ACCOUNT
            $existingSalesLedger = array_filter($postingArray[self::SALES_ACCOUNT], function ($posting) use ($salesAccountLedgerId, $salesAccountLedgerGroupId) {
                return $posting['ledger_id'] == $salesAccountLedgerId && $posting['ledger_group_id'] == $salesAccountLedgerGroupId;
            });
            //Ledger found
            if (count($existingSalesLedger) > 0) {
                $postingArray[self::SALES_ACCOUNT][0]['debit_amount'] += $salesDebitAmount;
            } else { //Assign a new ledger
                array_push($postingArray[self::SALES_ACCOUNT], [
                    'ledger_id' => $salesAccountLedgerId,
                    'ledger_group_id' => $salesAccountLedgerGroupId,
                    'ledger_code' => $salesAccountLedger?->code,
                    'ledger_name' => $salesAccountLedger?->name,
                    'ledger_group_code' => $salesAccountLedgerGroup?->name,
                    'credit_amount' => 0,
                    'debit_amount' => $salesDebitAmount
                ]);
            }
            //Check for same ledger and group in CUSTOMER ACCOUNT
            $existingcustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
                return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
            });
            //Ledger found
            if (count($existingcustomerLedger) > 0) {
                $postingArray[self::CUSTOMER_ACCOUNT][0]['credit_amount'] += $itemValueAfterDiscount;
            } else { //Assign a new ledger
                array_push($postingArray[self::CUSTOMER_ACCOUNT], [
                    'ledger_id' => $customerLedgerId,
                    'ledger_group_id' => $customerLedgerGroupId,
                    'ledger_code' => $customerLedger?->code,
                    'ledger_name' => $customerLedger?->name,
                    'ledger_group_code' => $customerLedgerGroup?->name,
                    'debit_amount' => 0,
                    'credit_amount' => $itemValueAfterDiscount
                ]);
            }
        }
        //TAXES ACCOUNT
        $taxes = ErpSaleReturnTed::where('sale_return_id', $document->id)->where('ted_type', "Tax")->get();
        foreach ($taxes as $tax) {
            $taxDetail = TaxDetail::find($tax->ted_id);
            $taxLedgerId = $taxDetail->ledger_id ?? null; //MAKE IT DYNAMIC
            $taxLedgerGroupId = $taxDetail->ledger_group_id ?? null; //MAKE IT DYNAMIC
            $taxLedger = Ledger::find($taxLedgerId);
            $taxLedgerGroup = Group::find($taxLedgerGroupId);
            if (!isset($taxLedger) || !isset($taxLedgerGroup)) {
                $ledgerErrorStatus = 'Tax Account Ledger not setup';
                break;
            }
            $existingTaxLedger = array_filter($postingArray[self::TAX_ACCOUNT], function ($posting) use ($taxLedgerId, $taxLedgerGroupId) {
                return $posting['ledger_id'] == $taxLedgerId && $posting['ledger_group_id'] === $taxLedgerGroupId;
            });
            //Ledger found
            if (count($existingTaxLedger) > 0) {
                $postingArray[self::TAX_ACCOUNT][0]['debit_amount'] += $tax->ted_amount;
            } else { //Assign a new ledger
                array_push($postingArray[self::TAX_ACCOUNT], [
                    'ledger_id' => $taxLedgerId,
                    'ledger_group_id' => $taxLedgerGroupId,
                    'ledger_code' => $taxLedger?->code,
                    'ledger_name' => $taxLedger?->name,
                    'ledger_group_code' => $taxLedgerGroup?->name,
                    'credit_amount' => 0,
                    'debit_amount' => $tax->ted_amount,
                ]);
            }
            //Tax for CUSTOMER ACCOUNT
            $existingCustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
                return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
            });
            //Ledger found
            if (count($existingCustomerLedger) > 0) {
                $postingArray[self::CUSTOMER_ACCOUNT][0]['credit_amount'] += $tax->ted_amount;
            } else { //Assign new ledger
                array_push($postingArray[self::CUSTOMER_ACCOUNT], [
                    'ledger_id' => $taxLedgerId,
                    'ledger_group_id' => $taxLedgerGroupId,
                    'ledger_code' => $taxLedger?->code,
                    'ledger_name' => $taxLedger?->name,
                    'ledger_group_code' => $taxLedgerGroup?->name,
                    'credit_amount' => $tax->ted_amount,
                    'debit_amount' => 0,
                ]);
            }
        }
        //EXPENSES
        $expenses = ErpSaleReturnTed::where('sale_return_id', $document->id)->where('ted_type', "Expense")->get();
        foreach ($expenses as $expense) {
            $expenseDetail = ExpenseMaster::find($expense->ted_id);
            $expenseLedgerId = $expenseDetail?->expense_ledger_id; //MAKE IT DYNAMIC - 5
            $expenseLedgerGroupId = $expenseDetail?->expense_ledger_group_id; //MAKE IT DYNAMIC - 9
            $expenseLedger = Ledger::find($expenseLedgerId);
            $expenseLedgerGroup = Group::find($expenseLedgerGroupId);
            if (!isset($expenseLedger) || !isset($expenseLedgerGroup)) {
                $ledgerErrorStatus = 'Expense Account Ledger not setup';
                break;
            }
            $existingExpenseLedger = array_filter($postingArray[self::EXPENSE_ACCOUNT], function ($posting) use ($expenseLedgerId, $expenseLedgerGroupId) {
                return $posting['ledger_id'] == $expenseLedgerId && $posting['ledger_group_id'] === $expenseLedgerGroupId;
            });
            //Ledger found
            if (count($existingExpenseLedger) > 0) {
                $postingArray[self::EXPENSE_ACCOUNT][0]['debit_amount'] += $expense->ted_amount;
            } else { //Assign a new ledger
                array_push($postingArray[self::EXPENSE_ACCOUNT], [
                    'ledger_id' => $expenseLedgerId,
                    'ledger_group_id' => $expenseLedgerGroupId,
                    'ledger_code' => $expenseLedger?->code,
                    'ledger_name' => $expenseLedger?->name,
                    'ledger_group_code' => $expenseLedgerGroup?->name,
                    'credit_amount' => 0,
                    'debit_amount' => $expense->ted_amount,
                ]);
            }
            //Expense for CUSTOMER ACCOUNT
            $existingCustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
                return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
            });
            //Ledger found
            if (count($existingCustomerLedger) > 0) {
                $postingArray[self::CUSTOMER_ACCOUNT][0]['credit_amount'] += $expense->ted_amount;
            } else { //Assign new ledger
                array_push($postingArray[self::EXPENSE_ACCOUNT], [
                    'ledger_id' => $expenseLedgerId,
                    'ledger_group_id' => $expenseLedgerGroupId,
                    'ledger_code' => $expenseLedger?->code,
                    'ledger_name' => $expenseLedger?->name,
                    'ledger_group_code' => $expenseLedgerGroup?->name,
                    'credit_amount' => $expense->ted_amount,
                    'debit_amount' => 0,
                ]);
            }
        }
        //Seperate posting of Discount
        if ($discountSeperatePosting) {
            $discounts = ErpSaleReturnTed::where('sale_return_id', $document->id)->where('ted_type', "Discount")->get();
            foreach ($discounts as $discount) {
                $discountDetail = DiscountMaster::find($discount->ted_id);
                $discountLedgerId = $discountDetail?->discount_ledger_id; //MAKE IT DYNAMIC
                $discountLedgerGroupId = $discountDetail?->discount_ledger_group_id; //MAKE IT DYNAMIC
                $discountLedger = Ledger::find($discountLedgerId);
                $discountLedgerGroup = Group::find($discountLedgerGroupId);
                if (!isset($discountLedger) || !isset($discountLedgerGroup)) {
                    $ledgerErrorStatus = 'Discount Account Ledger not setup';
                    break;
                }
                $existingDiscountLedger = array_filter($postingArray[self::DISCOUNT_ACCOUNT], function ($posting) use ($discountLedgerId, $discountLedgerGroupId) {
                    return $posting['ledger_id'] == $discountLedgerId && $posting['ledger_group_id'] === $discountLedgerGroupId;
                });
                //Ledger found
                if (count($existingDiscountLedger) > 0) {
                    $postingArray[self::DISCOUNT_ACCOUNT][0]['credit_amount'] += $discount->ted_amount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::DISCOUNT_ACCOUNT], [
                        'ledger_id' => $discountLedgerId,
                        'ledger_group_id' => $discountLedgerGroupId,
                        'ledger_code' => $discountLedger?->code,
                        'ledger_name' => $discountLedger?->name,
                        'ledger_group_code' => $discountLedgerGroup?->name,
                        'debit_amount' => 0,
                        'credit_amount' => $discount->ted_amount,
                    ]);
                }
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
        //Balance does not match
        // if (number_format($totalDebitAmount,2) !== number_format($totalCreditAmount,2)) {
        //     return array(
        //         'status' => false,
        //         'message' => 'Credit Amount does not match Debit Amount',
        //         'data' => []
        //     );
        // }
        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam)) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
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
        $voucherDetails = [];
        foreach ($postingArray as $entryType => $postDetails) {
            foreach ($postDetails as $post) {
                array_push($voucherDetails, [
                    'ledger_id' => $post['ledger_id'],
                    'ledger_parent_id' => $post['ledger_group_id'],
                    'debit_amt' => $post['debit_amount'],
                    'credit_amt' => $post['credit_amount'],
                    'debit_amt_org' => $post['debit_amount'] * $voucherHeader['org_currency_exg_rate'],
                    'credit_amt_org' => $post['credit_amount'] * $voucherHeader['org_currency_exg_rate'],
                    'debit_amt_comp' => $post['debit_amount'] * $voucherHeader['comp_currency_exg_rate'],
                    'credit_amt_comp' => $post['credit_amount'] * $voucherHeader['comp_currency_exg_rate'],
                    'debit_amt_group' => $post['debit_amount'] * $voucherHeader['group_currency_exg_rate'],
                    'credit_amt_group' => $post['credit_amount'] * $voucherHeader['group_currency_exg_rate'],
                    'entry_type' => $entryType,
                    // 'cost_center_id',
                    // 'notes',
                    // 'group_id' => $voucherHeader['group_id'],
                    // 'company_id' => $voucherHeader['company_id'],
                    // 'organization_id' => $voucherHeader['organization_id']
                ]);
            }
        }
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
    public static function srdnVoucherDetails(int $documentId, int $invoiceToFollow)
    {
        $document = ErpSaleReturn::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }
        $postingArray = array(
            self::SALES_ACCOUNT => [],
            self::TAX_ACCOUNT => [],
            self::EXPENSE_ACCOUNT => [],
            self::DISCOUNT_ACCOUNT => [],
            self::CUSTOMER_ACCOUNT => [],
            self::STOCK_ACCOUNT => [],
            self::COGS_ACCOUNT => []
        );
        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;


        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;
        //COGS SETUP
        foreach ($document->items as $docItemKey => $docItem) {
            $itemValue = 0;
            // $stockLedger = StockLedger::first();
            $isPulled = $docItem;
            if ($isPulled?->si_item_id && $isPulled?->invoice_item?->header?->document_type == 'dnote') {
                $doc_detail_id = $docItem->si_item_id ?? null;
                $doc_header_id = $isPulled->invoice_item->header->id ?? null;

                if ($doc_header_id && $doc_detail_id) {
                    $stockLedger = StockLedger::where('book_type', ConstantHelper::SR_SERVICE_ALIAS)
                        ->where('document_header_id', $doc_header_id)
                        ->where('document_detail_id', $doc_detail_id)
                        ->get();

                    if ($stockLedger->isNotEmpty()) {
                        $orgCurrencyCost = StockLedger::whereIn('utilized_id', $stockLedger->pluck('id'))->sum('org_currency_cost');
                    } else {
                        $orgCurrencyCost = $document->total_return_value - $document->total_discount_value ?? 0; // Fallback value
                    }
                } else {
                    $orgCurrencyCost = $document->total_return_value - $document->total_discount_value ?? 0; // Fallback if IDs are missing
                }
            } else {
                $orgCurrencyCost = $document->total_return_value - $document->total_discount_value ?? 0; // Fallback if IDs are missing
            }
            $itemValue = $orgCurrencyCost / $document->org_currency_exg_rate;
            // $itemValue = ($docItem -> rate * $docItem -> order_qty * 0.80);//CHANGE
            $stockDebitAmount = round($itemValue, 2);
            $cogsCreditAmount = round($itemValue, 2);
            $cogsLedgerDetails = AccountHelper::getCogsLedgerGroupAndLedgerId($document->organization_id, $docItem->item_id, $document->book_id);
            $cogsLedgerId = is_a($cogsLedgerDetails, Collection::class) ? $cogsLedgerDetails->first()['ledger_id'] : null;
            $cogsLedgerGroupId = is_a($cogsLedgerDetails, Collection::class) ? $cogsLedgerDetails->first()['ledger_group'] : null;
            $cogsLedger = Ledger::find($cogsLedgerId);
            $cogsLedgerGroup = Group::find($cogsLedgerGroupId);
            //LEDGER NOT FOUND
            if (!isset($cogsLedger) || !isset($cogsLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'COGS Account not setup';
                break;
            }
            //Check for same ledger and group in SALES ACCOUNT
            $existingCogsLedger = array_filter($postingArray[self::COGS_ACCOUNT], function ($posting) use ($cogsLedgerId, $cogsLedgerGroupId) {
                return $posting['ledger_id'] == $cogsLedgerId && $posting['ledger_group_id'] == $cogsLedgerGroupId;
            });
            //Ledger found
            if (count($existingCogsLedger) > 0) {
                $postingArray[self::COGS_ACCOUNT][0]['credit_amount'] += $cogsCreditAmount;
                $postingArray[self::COGS_ACCOUNT][0]['credit_amount_org'] += $orgCurrencyCost;
            } else { //Assign a new ledger
                array_push($postingArray[self::COGS_ACCOUNT], [
                    'ledger_id' => $cogsLedgerId,
                    'ledger_group_id' => $cogsLedgerGroupId,
                    'ledger_code' => $cogsLedger?->code,
                    'ledger_name' => $cogsLedger?->name,
                    'ledger_group_code' => $cogsLedgerGroup?->name,
                    'debit_amount' => 0,
                    'debit_amount_org' => 0,
                    'credit_amount' => $cogsCreditAmount,
                    'credit_amount_org' => $orgCurrencyCost
                ]);
            }

            $stockLedgerDetails = AccountHelper::getStockLedgerGroupAndLedgerId($document->organization_id, $docItem->item_id, $document->book_id);
            $stockLedgerId = is_a($stockLedgerDetails, Collection::class) ? $stockLedgerDetails->first()['ledger_id'] : null;
            $stockLedgerGroupId = is_a($stockLedgerDetails, Collection::class) ? $stockLedgerDetails->first()['ledger_group'] : null;
            $stockLedger = Ledger::find($stockLedgerId);
            $stockLedgerGroup = Group::find($stockLedgerGroupId);
            //LEDGER NOT FOUND
            if (!isset($stockLedger) || !isset($stockLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Stock Account not setup';
                break;
            }

            //Check for same ledger and group in SALES ACCOUNT
            $existingstockLedger = array_filter($postingArray[self::STOCK_ACCOUNT], function ($posting) use ($stockLedgerId, $stockLedgerGroupId) {
                return $posting['ledger_id'] == $stockLedgerId && $posting['ledger_group_id'] == $stockLedgerGroupId;
            });
            //Ledger found
            if (count($existingstockLedger) > 0) {
                $postingArray[self::STOCK_ACCOUNT][0]['debit_amount'] += $stockDebitAmount;
                $postingArray[self::STOCK_ACCOUNT][0]['debit_amount_org'] += $orgCurrencyCost;
            } else { //Assign a new ledger
                array_push($postingArray[self::STOCK_ACCOUNT], [
                    'ledger_id' => $stockLedgerId,
                    'ledger_group_id' => $stockLedgerGroupId,
                    'ledger_code' => $stockLedger?->code,
                    'ledger_name' => $stockLedger?->name,
                    'ledger_group_code' => $stockLedgerGroup?->name,
                    'debit_amount' => $stockDebitAmount,
                    'debit_amount_org' => $orgCurrencyCost,
                    'credit_amount' => 0,
                    'credit_amount_org' => 0,
                ]);
            }
        }
        //Customer Account initialize
        if (!$invoiceToFollow) {

            $customer = Customer::find($document->customer_id);
            $customerLedgerId = $customer->ledger_id;
            $customerLedgerGroupId = $customer->ledger_group_id;
            $customerLedger = Ledger::find($customerLedgerId);
            $customerLedgerGroup = Group::find($customerLedgerGroupId);
            //Customer Ledger account not found
            if (!isset($customerLedger) || !isset($customerLedgerGroup)) {
                return array(
                    'status' => false,
                    'message' => self::ERROR_PREFIX . 'Customer Account not setup',
                    'data' => []
                );
            }
            // $discountPostingParam = OrganizationBookParameter::where('book_id', $document -> book_id)
            // -> where('parameter_name', ServiceParametersHelper::GL_SEPERATE_DISCOUNT_PARAM) -> first();
            // if (isset($discountPostingParam)) {
            //     $discountSeperatePosting = $discountPostingParam -> parameter_value[0] === "yes" ? true : false;
            // } else {
            $discountSeperatePosting = false;
            // }
            foreach ($document->items as $docItemKey => $docItem) {
                //Assign Item values
                $itemValue = $docItem->rate * $docItem->order_qty;
                $itemTotalDiscount = $docItem->header_discount_amount + $docItem->item_discount_amount;
                $itemValueAfterDiscount = $itemValue - $itemTotalDiscount;
                //SALES ACCOUNT
                $salesAccountLedgerDetails = AccountHelper::getLedgerGroupAndLedgerIdForSalesAccount($document->organization_id, $document->customer_id, $docItem->item_id, $document->book_id);
                $salesAccountLedgerId = is_a($salesAccountLedgerDetails, Collection::class) ? $salesAccountLedgerDetails->first()['ledger_id'] : null;
                $salesAccountLedgerGroupId = is_a($salesAccountLedgerDetails, Collection::class) ? $salesAccountLedgerDetails->first()['ledger_group'] : null;
                $salesAccountLedger = Ledger::find($salesAccountLedgerId);
                $salesAccountLedgerGroup = Group::find($salesAccountLedgerGroupId);
                //LEDGER NOT FOUND
                if (!isset($salesAccountLedger) || !isset($salesAccountLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'Sales Account not setup';
                    break;
                }
                $salesDebitAmount = $discountSeperatePosting ? $itemValue : $itemValueAfterDiscount;
                //Check for same ledger and group in SALES ACCOUNT
                $existingSalesLedger = array_filter($postingArray[self::SALES_ACCOUNT], function ($posting) use ($salesAccountLedgerId, $salesAccountLedgerGroupId) {
                    return $posting['ledger_id'] == $salesAccountLedgerId && $posting['ledger_group_id'] == $salesAccountLedgerGroupId;
                });
                //Ledger found
                if (count($existingSalesLedger) > 0) {
                    $postingArray[self::SALES_ACCOUNT][0]['debit_amount'] += $salesDebitAmount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::SALES_ACCOUNT], [
                        'ledger_id' => $salesAccountLedgerId,
                        'ledger_group_id' => $salesAccountLedgerGroupId,
                        'ledger_code' => $salesAccountLedger?->code,
                        'ledger_name' => $salesAccountLedger?->name,
                        'ledger_group_code' => $salesAccountLedgerGroup?->name,
                        'debit_amount' => $salesDebitAmount,
                        'credit_amount' => 0
                    ]);
                }
                //Check for same ledger and group in CUSTOMER ACCOUNT
                $existingcustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
                    return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
                });
                //Ledger found
                if (count($existingcustomerLedger) > 0) {
                    $postingArray[self::CUSTOMER_ACCOUNT][0]['credit_amount'] += $itemValueAfterDiscount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::CUSTOMER_ACCOUNT], [
                        'ledger_id' => $customerLedgerId,
                        'ledger_group_id' => $customerLedgerGroupId,
                        'ledger_code' => $customerLedger?->code,
                        'ledger_name' => $customerLedger?->name,
                        'ledger_group_code' => $customerLedgerGroup?->name,
                        'credit_amount' => $itemValueAfterDiscount,
                        'debit_amount' => 0
                    ]);
                }
            }
            //TAXES ACCOUNT
            $taxes = ErpSaleReturnTed::where('sale_return_id', $document->id)->where('ted_type', "Tax")->get();
            foreach ($taxes as $tax) {
                $taxDetail = TaxDetail::find($tax->ted_id);
                $taxLedgerId = $taxDetail->ledger_id ?? null; //MAKE IT DYNAMIC
                $taxLedgerGroupId = $taxDetail->ledger_group_id ?? null; //MAKE IT DYNAMIC
                $taxLedger = Ledger::find($taxLedgerId);
                $taxLedgerGroup = Group::find($taxLedgerGroupId);
                if (!isset($taxLedger) || !isset($taxLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'Tax Account not setup';
                    break;
                }
                $existingTaxLedger = array_filter($postingArray[self::TAX_ACCOUNT], function ($posting) use ($taxLedgerId, $taxLedgerGroupId) {
                    return $posting['ledger_id'] == $taxLedgerId && $posting['ledger_group_id'] === $taxLedgerGroupId;
                });
                //Ledger found
                if (count($existingTaxLedger) > 0) {
                    $postingArray[self::TAX_ACCOUNT][0]['debit_amount'] += $tax->ted_amount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::TAX_ACCOUNT], [
                        'ledger_id' => $taxLedgerId,
                        'ledger_group_id' => $taxLedgerGroupId,
                        'ledger_code' => $taxLedger?->code,
                        'ledger_name' => $taxLedger?->name,
                        'ledger_group_code' => $taxLedgerGroup?->name,
                        'debit_amount' => $tax->ted_amount,
                        'credit_amount' => 0,
                    ]);
                }
                //Tax for CUSTOMER ACCOUNT
                $existingCustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
                    return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
                });
                //Ledger found
                if (count($existingCustomerLedger) > 0) {
                    $postingArray[self::CUSTOMER_ACCOUNT][0]['credit_amount'] += $tax->ted_amount;
                } else { //Assign new ledger
                    array_push($postingArray[self::CUSTOMER_ACCOUNT], [
                        'ledger_id' => $taxLedgerId,
                        'ledger_group_id' => $taxLedgerGroupId,
                        'ledger_code' => $taxLedger?->code,
                        'ledger_name' => $taxLedger?->name,
                        'ledger_group_code' => $taxLedgerGroup?->name,
                        'debit_amount' => 0,
                        'credit_amount' => $tax->ted_amount,
                    ]);
                }
            }
            //EXPENSES
            $expenses = ErpSaleReturnTed::where('sale_return_id', $document->id)->where('ted_type', "Expense")->get();
            foreach ($expenses as $expense) {
                $expenseDetail = ExpenseMaster::find($expense->ted_id);
                $expenseLedgerId = $expenseDetail?->expense_ledger_id; //MAKE IT DYNAMIC - 5
                $expenseLedgerGroupId = $expenseDetail?->expense_ledger_group_id; //MAKE IT DYNAMIC - 9
                $expenseLedger = Ledger::find($expenseLedgerId);
                $expenseLedgerGroup = Group::find($expenseLedgerGroupId);
                if (!isset($expenseLedger) || !isset($expenseLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'Expense Account not setup';
                    break;
                }
                $existingExpenseLedger = array_filter($postingArray[self::EXPENSE_ACCOUNT], function ($posting) use ($expenseLedgerId, $expenseLedgerGroupId) {
                    return $posting['ledger_id'] == $expenseLedgerId && $posting['ledger_group_id'] === $expenseLedgerGroupId;
                });
                //Ledger found
                if (count($existingExpenseLedger) > 0) {
                    $postingArray[self::EXPENSE_ACCOUNT][0]['debit_amount'] += $expense->ted_amount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::EXPENSE_ACCOUNT], [
                        'ledger_id' => $expenseLedgerId,
                        'ledger_group_id' => $expenseLedgerGroupId,
                        'ledger_code' => $expenseLedger?->code,
                        'ledger_name' => $expenseLedger?->name,
                        'ledger_group_code' => $expenseLedgerGroup?->name,
                        'debit_amount' => $expense->ted_amount,
                        'credit_amount' => 0,
                    ]);
                }
                //Expense for CUSTOMER ACCOUNT
                $existingCustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
                    return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
                });
                //Ledger found
                if (count($existingCustomerLedger) > 0) {
                    $postingArray[self::CUSTOMER_ACCOUNT][0]['credit_amount'] += $expense->ted_amount;
                } else { //Assign new ledger
                    array_push($postingArray[self::EXPENSE_ACCOUNT], [
                        'ledger_id' => $expenseLedgerId,
                        'ledger_group_id' => $expenseLedgerGroupId,
                        'ledger_code' => $expenseLedger?->code,
                        'ledger_name' => $expenseLedger?->name,
                        'ledger_group_code' => $expenseLedgerGroup?->name,
                        'debit_amount' => 0,
                        'credit_amount' => $expense->ted_amount,
                    ]);
                }
            }
            //Seperate posting of Discount
            if ($discountSeperatePosting) {
                $discounts = ErpSaleReturnTed::where('sale_return_id', $document->id)->where('ted_type', "Discount")->get();
                foreach ($discounts as $discount) {
                    $discountDetail = DiscountMaster::find($discount->ted_id);
                    $discountLedgerId = $discountDetail?->discount_ledger_id; //MAKE IT DYNAMIC
                    $discountLedgerGroupId = $discountDetail?->discount_ledger_group_id; //MAKE IT DYNAMIC
                    $discountLedger = Ledger::find($discountLedgerId);
                    $discountLedgerGroup = Group::find($discountLedgerGroupId);
                    if (!isset($discountLedger) || !isset($discountLedgerGroup)) {
                        $ledgerErrorStatus = self::ERROR_PREFIX . 'Discount Account not setup';
                        break;
                    }
                    $existingDiscountLedger = array_filter($postingArray[self::DISCOUNT_ACCOUNT], function ($posting) use ($discountLedgerId, $discountLedgerGroupId) {
                        return $posting['ledger_id'] == $discountLedgerId && $posting['ledger_group_id'] === $discountLedgerGroupId;
                    });
                    //Ledger found
                    if (count($existingDiscountLedger) > 0) {
                        $postingArray[self::DISCOUNT_ACCOUNT][0]['credit_amount'] += $discount->ted_amount;
                    } else { //Assign a new ledger
                        array_push($postingArray[self::DISCOUNT_ACCOUNT], [
                            'ledger_id' => $discountLedgerId,
                            'ledger_group_id' => $discountLedgerGroupId,
                            'ledger_code' => $discountLedger?->code,
                            'ledger_name' => $discountLedger?->name,
                            'ledger_group_code' => $discountLedgerGroup?->name,
                            'credit_amount' => $discount->ted_amount,
                            'debit_amount' => 0,
                        ]);
                    }
                }
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

        //Balance does not match
        // if (round($totalDebitAmount,6) != round($totalCreditAmount,6)) {
        //     return array(
        //         'status' => false,
        //         'message' => self::ERROR_PREFIX.'Credit Amount does not match Debit Amount',
        //         'data' => []
        //     );
        // }
        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam)) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
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
        $voucherDetails = [];
        foreach ($postingArray as $entryType => $postDetails) {
            foreach ($postDetails as $post) {
                $debitAmtOrg = $post['debit_amount'] * $voucherHeader['org_currency_exg_rate'];
                $creditAmtOrg = $post['credit_amount'] * $voucherHeader['org_currency_exg_rate'];

                $debitAmtComp = $post['debit_amount'] * $voucherHeader['comp_currency_exg_rate'];
                $creditAmtComp = $post['credit_amount'] * $voucherHeader['comp_currency_exg_rate'];

                $debitAmtGroup = $post['debit_amount'] * $voucherHeader['group_currency_exg_rate'];
                $creditAmtGroup = $post['credit_amount'] * $voucherHeader['group_currency_exg_rate'];

                if ($entryType === self::COGS_ACCOUNT || $entryType === self::STOCK_ACCOUNT) {
                    $debitAmtOrg = $post['debit_amount_org'];
                    $creditAmtOrg = $post['credit_amount_org'];
                    if ($voucherHeader['org_currency_code'] === $voucherHeader['comp_currency_code']) {
                        $debitAmtComp = $post['debit_amount_org'];
                        $creditAmtComp = $post['credit_amount_org'];
                    }
                    if ($voucherHeader['org_currency_code'] === $voucherHeader['group_currency_code']) {
                        $debitAmtGroup = $post['debit_amount_org'];
                        $creditAmtGroup = $post['credit_amount_org'];
                    }
                }
                array_push($voucherDetails, [
                    'ledger_id' => $post['ledger_id'],
                    'ledger_parent_id' => $post['ledger_group_id'],
                    'debit_amt' => $post['debit_amount'],
                    'credit_amt' => $post['credit_amount'],
                    'debit_amt_org' => $debitAmtOrg,
                    'credit_amt_org' => $creditAmtOrg,
                    'debit_amt_comp' => $debitAmtComp,
                    'credit_amt_comp' => $creditAmtComp,
                    'debit_amt_group' => $debitAmtGroup,
                    'credit_amt_group' => $creditAmtGroup,
                    'entry_type' => $entryType,
                ]);
            }
        }
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

    public static function typereturncheck(int $documentId)
    {
        $document = ErpSaleReturn::find($documentId);
        //Invoice to follow param has been removed - Now it's based on document_type of Invoice : Jagdeep
        $invocietofollow = in_array($document?->items[0]?->invoice_item?->header?->document_type, [ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS, ConstantHelper::SI_SERVICE_ALIAS]) ? 0 : 1;
        $type = $document?->items[0]?->invoice_item?->header?->document_type ?? "";
        if ($type == "si" && !$document->items[0]->invoice_item->dnote_item_id) {
            return self::salesReturnVoucherDetails($documentId, $type);
        } else {
            return self::srdnVoucherDetails($documentId, $invocietofollow);
        }
    }
    public static function loaninvoiceVoucherDetails(int $documentId, string $type, string $remarks = null, Request $data = null)
    {
        $loandata = $data->query('data') ?? $data->input('data');
        $loan = $data->query('loan_data') ?? $data->input('loan_data');
        $loan = json_decode($loan);

        $loandata = json_decode($loandata);

        if (!empty($loan) && $loan->type == 1) {
            $accountSetup = isset(self::SERVICE_POSTING_MAPPING[ConstantHelper::HOMELOAN])
                ? self::SERVICE_POSTING_MAPPING[ConstantHelper::HOMELOAN] : [];
        } else if (!empty($loan) && $loan->type === 3) {
            $accountSetup = isset(self::SERVICE_POSTING_MAPPING[ConstantHelper::TERMLOAN])
                ? self::SERVICE_POSTING_MAPPING[ConstantHelper::TERMLOAN] : [];
        } else {
            $accountSetup = isset(self::SERVICE_POSTING_MAPPING[ConstantHelper::VEHICLELOAN])
                ? self::SERVICE_POSTING_MAPPING[ConstantHelper::VEHICLELOAN] : [];
        }

        if (!isset($accountSetup) || count($accountSetup) == 0) {
            return array(
                'status' => false,
                'message' => 'Account Setup not found',
                'data' => []
            );
        }
        $document = HomeLoan::find($documentId);
        $document->currency_id = $loandata->currency_id;
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }
        //Make array according to setup
        $postingArray = array(
            self::Bank_ACCOUNT => [],
            self::ProcessFee_ACCOUNT => [],
        );
        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;
        //Customer Account initialize
        $financesetup = LoanFinancialAccount::first();
        if (!empty($financesetup)) {
            $FinanceLedgerId = $financesetup->pro_ledger_id;
            $FinanceLedgerGroupId = $financesetup->pro_ledger_group_id;
            $FinanceLedger = Ledger::find($FinanceLedgerId);
            $FinanceLedgerGroup = Group::find($FinanceLedgerGroupId);
        }

        //Customer Ledger account not found
        if (!isset($FinanceLedger) || !isset($FinanceLedgerGroup)) {
            return array(
                'status' => false,
                'message' => 'Finanical Setup Ledger not setup',
                'data' => []
            );
        }

        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;

        //$loandata = LoanProcessFee::where('loan_application_id',$document->id)->first();
        $loanLedger = Ledger::find($loandata->ledger_id);
        $loanLedgerGroup = Group::find($loandata->ledger_group_id);


        if (!isset($loanLedger) || !isset($loanLedgerGroup)) {
            return array(
                'status' => false,
                'message' => 'Loan Cash/Bank Setup Ledger not setup',
                'data' => []
            );
        }




        array_push($postingArray[self::Bank_ACCOUNT], [
            'ledger_id' => $loandata->ledger_id,
            'ledger_group_id' => $loandata->ledger_group_id,
            'ledger_code' => $loanLedger?->code,
            'ledger_name' => $loanLedger?->name,
            'ledger_group_code' => $loanLedgerGroup?->name,
            'debit_amount' => $loandata->fee_amount,
            'credit_amount' => 0
        ]);

        array_push($postingArray[self::ProcessFee_ACCOUNT], [
            'ledger_id' => $FinanceLedgerId,
            'ledger_group_id' => $FinanceLedgerGroupId,
            'ledger_code' => $FinanceLedger?->code,
            'ledger_name' => $FinanceLedger?->name,
            'ledger_group_code' => $FinanceLedgerGroup?->name,
            'credit_amount' => $loandata->fee_amount,
            'debit_amount' => 0
        ]);


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

                $totalDebitAmount += $postingValue['debit_amount'];
                $totalCreditAmount += $postingValue['credit_amount'];
            }
        }
        //Balance does not match
        // if (round($totalDebitAmount,6) !== round($totalCreditAmount,6)) {
        //     return array(
        //         'status' => false,
        //         'message' => 'Credit Amount does not match Debit Amount',
        //         'data' => []
        //     );
        // }
        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => 'Financial Book Code is not specified',
                'data' => []
            );
        }

        $currdata = OrganizationCompany::where('id', $document->company_id)->first();
        $excurrdata = CurrencyExchange::where('from_currency_id', $currdata->currency_id)->first();
        $currency = Currency::find($currdata->currency_id);

        $userData = Helper::userCheck();

        $booksdata = Book::where('book_name', 'JOURNAL_VOUCHER')->first();
        $numberPatternData = Helper::generateDocumentNumberNew($booksdata->id, $document->document_date);
        if (!isset($numberPatternData)) {
            return response()->json([
                'message' => "Invalid Book",
                'error' => "",
            ], 422);
        }
        $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : null;

        $voucherHeader = [
            'voucher_no' => $document_number,
            'voucher_name' => $booksdata->book_code,
            'doc_prefix' => $numberPatternData['prefix'],
            'doc_suffix' => $numberPatternData['suffix'],
            'doc_no' => $numberPatternData['doc_no'],
            'doc_reset_pattern' => $numberPatternData['reset_pattern'],
            'document_date' => $document->document_date,
            'book_id' => $booksdata->id,
            'book_type_id' => $booksdata->org_service_id,
            'date' => $document->document_date,
            'amount' => $totalCreditAmount,
            'currency_id' => $currdata->currency_id,
            'currency_code' => $currdata->currency_code,
            'org_currency_id' => $currdata->currency_id,
            'org_currency_code' => $currdata->currency_code,
            'org_currency_exg_rate' => $excurrdata->exchange_rate,
            'comp_currency_id' => $currdata->currency_id, // Missing comma added here
            'comp_currency_code' => $currdata->currency_code,
            'comp_currency_exg_rate' => $excurrdata->exchange_rate,
            'group_currency_id' => $currdata->currency_id,
            'group_currency_code' => $currdata->currency_code,
            'group_currency_exg_rate' => $excurrdata->exchange_rate,
            'reference_service' => $book?->service?->alias,
            'reference_doc_id' => $document->id,
            'group_id' => $document->group_id,
            'company_id' => $document->company_id,
            'organization_id' => $document->organization_id,
            'voucherable_type' => $userData['user_type'],
            'voucherable_id' => $userData['user_id'],
            'approvalStatus' => ConstantHelper::APPROVED,
            'document_status' => ConstantHelper::APPROVED,
            'approvalLevel' => $document->approval_level,
            'remarks' => $remarks,
        ];

        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document);
        return array(
            'status' => true,
            'message' => 'Posting Details found',
            'data' => [
                'voucher_header' => $voucherHeader,
                'voucher_details' => $voucherDetails,
                'document_date' => $document->created_at,
                'ledgers' => $postingArray,
                'total_debit' => $totalDebitAmount,
                'total_credit' => $totalCreditAmount,
                'book_code' => $booksdata?->book_code,
                'document_number' => $document_number,
                'currency_code' => $currency?->short_name
            ]
        );
    }

    public static function disinvoiceVoucherDetails(int $documentId, string $type)
    {
        $accountSetup = isset(self::SERVICE_POSTING_MAPPING[ConstantHelper::LOAN_DISBURSEMENT])
            ? self::SERVICE_POSTING_MAPPING[ConstantHelper::LOAN_DISBURSEMENT] : [];
        if (!isset($accountSetup) || count($accountSetup) == 0) {
            return array(
                'status' => false,
                'message' => 'Account Setup not found',
                'data' => []
            );
        }
        $document = LoanDisbursement::find($documentId);
        $document->currency_id = Helper::getAuthenticatedUser()->organization->currency_id;
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }
        //Make array according to setup
        $postingArray = array(
            self::Bank_ACCOUNT => [],
            self::Loan_Customer_Receivable_ACCOUNT => [],
        );
        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;
        //Customer Account initialize
        $financesetup = LoanFinancialAccount::first();
        if (!empty($financesetup)) {
            $FinanceLedgerId = $financesetup->dis_ledger_id;
            $FinanceLedgerGroupId = $financesetup->dis_ledger_group_id;
            $FinanceLedger = Ledger::find($FinanceLedgerId);
            $FinanceLedgerGroup = Group::find($FinanceLedgerGroupId);
        }

        //Customer Ledger account not found
        if (!isset($FinanceLedger) || !isset($FinanceLedgerGroup)) {
            return array(
                'status' => false,
                'message' => 'Finanical Setup Ledger not setup',
                'data' => []
            );
        }

        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;

        $loandata = HomeLoan::where('id', $document->home_loan_id)->first();
        if (!empty($loandata)) {
            $loanLedger = Ledger::find($loandata->cus_receivable_ledgerid);
            $loanLedgerGroup = Group::find($loandata->cus_receivable_ledgergroup);
        }

        if (!isset($loanLedger) || !isset($loanLedgerGroup)) {
            return array(
                'status' => false,
                'message' => 'Loan Cus Rec Ledger not setup',
                'data' => []
            );
        }


        array_push($postingArray[self::Bank_ACCOUNT], [
            'ledger_id' => $loandata->cus_receivable_ledgerid,
            'ledger_group_id' => $loandata->cus_receivable_ledgergroup,
            'ledger_code' => $loanLedger?->code,
            'ledger_name' => $loanLedger?->name,
            'ledger_group_code' => $loanLedgerGroup?->name,
            'debit_amount' => $document->actual_dis,
            'credit_amount' => 0
        ]);

        array_push($postingArray[self::Loan_Customer_Receivable_ACCOUNT], [
            'ledger_id' => $FinanceLedgerId,
            'ledger_group_id' => $FinanceLedgerGroupId,
            'ledger_code' => $FinanceLedger?->code,
            'ledger_name' => $FinanceLedger?->name,
            'ledger_group_code' => $FinanceLedgerGroup?->name,
            'credit_amount' => $document->actual_dis,
            'debit_amount' => 0
        ]);




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

                $totalDebitAmount += $postingValue['debit_amount'];
                $totalCreditAmount += $postingValue['credit_amount'];
            }
        }
        //Balance does not match
        // if (round($totalDebitAmount,6) !== round($totalCreditAmount,6)) {
        //     return array(
        //         'status' => false,
        //         'message' => 'Credit Amount does not match Debit Amount',
        //         'data' => []
        //     );
        // }
        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => 'Financial Book Code is not specified',
                'data' => []
            );
        }

        $currdata = OrganizationCompany::where('id', $document->company_id)->first();
        $excurrdata = CurrencyExchange::where('from_currency_id', $currdata->currency_id)->first();
        $currency = Currency::find($currdata->currency_id);

        $userData = Helper::userCheck();

        $booksdata = Book::where('book_name', 'JOURNAL_VOUCHER')->first();
        $numberPatternData = Helper::generateDocumentNumberNew($booksdata->id, $document->document_date);
        if (!isset($numberPatternData)) {
            return response()->json([
                'message' => "Invalid Book",
                'error' => "",
            ], 422);
        }
        $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : null;

        $voucherHeader = [
            'voucher_no' => $document_number,
            'voucher_name' => $booksdata->book_code,
            'doc_prefix' => $numberPatternData['prefix'],
            'doc_suffix' => $numberPatternData['suffix'],
            'doc_no' => $numberPatternData['doc_no'],
            'doc_reset_pattern' => $numberPatternData['reset_pattern'],
            'document_date' => $document->document_date,
            'book_id' => $booksdata->id,
            'book_type_id' => $booksdata->org_service_id,
            'date' => $document->document_date,
            'amount' => $totalCreditAmount,
            'currency_id' => $currdata->currency_id,
            'currency_code' => $currdata->currency_code,
            'org_currency_id' => $currdata->currency_id,
            'org_currency_code' => $currdata->currency_code,
            'org_currency_exg_rate' => $excurrdata->exchange_rate,
            'comp_currency_id' => $currdata->currency_id, // Missing comma added here
            'comp_currency_code' => $currdata->currency_code,
            'comp_currency_exg_rate' => $excurrdata->exchange_rate,
            'group_currency_id' => $currdata->currency_id,
            'group_currency_code' => $currdata->currency_code,
            'group_currency_exg_rate' => $excurrdata->exchange_rate,
            'reference_service' => $book?->service?->alias,
            'reference_doc_id' => $document->id,
            'group_id' => $document->group_id,
            'company_id' => $document->company_id,
            'organization_id' => $document->organization_id,
            'voucherable_type' => $userData['user_type'],
            'voucherable_id' => $userData['user_id'],
            'approvalStatus' => ConstantHelper::APPROVED,
            'document_status' => ConstantHelper::APPROVED,
            'approvalLevel' => $document->approval_level,
            'remarks' => $document->dis_remarks,
        ];

        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document);

        return array(
            'status' => true,
            'message' => 'Posting Details found',
            'data' => [
                'voucher_header' => $voucherHeader,
                'voucher_details' => $voucherDetails,
                'document_date' => $document->created_at,
                'ledgers' => $postingArray,
                'total_debit' => $totalDebitAmount,
                'total_credit' => $totalCreditAmount,
                'book_code' => $booksdata?->book_code,
                'document_number' => $document_number,
                'currency_code' => $currency?->short_name
            ]
        );
    }

    public static function loanRecoverInvoiceVoucherDetails(int $documentId, string $remarks)
    {
        $accountSetup = isset(self::SERVICE_POSTING_MAPPING[ConstantHelper::LOAN_RECOVERY])
            ? self::SERVICE_POSTING_MAPPING[ConstantHelper::LOAN_RECOVERY] : [];
        if (!isset($accountSetup) || count($accountSetup) == 0) {
            return array(
                'status' => false,
                'message' => 'Account Setup not found',
                'data' => []
            );
        }
        $document = RecoveryLoan::find($documentId);
        $document->currency_id = Helper::getAuthenticatedUser()->organization->currency_id;
        $loandata = $document;
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }
        //Make array according to setup
        $postingArray = array(
            self::Bank_ACCOUNT => [],
            self::CUSTOMER_ACCOUNT => [],
            self::INTEREST_ACCOUNT => [],
        );
        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;
        //Customer Account initialize
        $financesetup = LoanFinancialAccount::first();
        if (!empty($financesetup)) {
            $FinanceLedgerId = $financesetup->pro_ledger_id;
            $FinanceLedgerGroupId = $financesetup->pro_ledger_group_id;
            $FinanceLedger = Ledger::find($FinanceLedgerId);
            $FinanceLedgerGroup = Group::find($FinanceLedgerGroupId);
            $InterestLedgerId = $financesetup->int_ledger_id;
            $InterestLedgerGroupId = $financesetup->int_ledger_group_id;
            $InterestLedger = Ledger::find($InterestLedgerId);
            $InterestLedgerGroup = Group::find($InterestLedgerGroupId);
        }

        //Customer Ledger account not found
        if (!isset($FinanceLedger) || !isset($FinanceLedgerGroup)) {
            return array(
                'status' => false,
                'message' => 'Finanical Setup Ledger not setup',
                'data' => []
            );
        }

        //Interest Ledger account not found
        if (!isset($InterestLedger) || !isset($InterestLedgerGroup)) {
            return array(
                'status' => false,
                'message' => 'Finanical Setup Interest Ledger not setup',
                'data' => []
            );
        }
        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;



        if (!empty($loandata)) {
            $BankLedgerId = $loandata->bank->ledger_id;
            $BankLedgerGroupId = $loandata->bank->ledger_group_id;
            $BankLedger = Ledger::find($BankLedgerId);
            $BankLedgerGroup = Group::find($BankLedgerGroupId);
        }



        if(!isset($BankLedger)){
            return array(
                'status' => false,
                'message' => 'Bank Ledger not setup',
                'data' => []
            );
        }
        if(!isset($BankLedgerGroup)){
            return array(
                'status' => false,
                'message' => 'Bank Ledger Group not found',
                'data' => []
            );
        }


        array_push($postingArray[self::Bank_ACCOUNT], [
            'ledger_id' => $loandata->bank->ledger_id,
            'ledger_group_id' => $loandata->bank->ledger_id,
            'ledger_code' => $BankLedger?->code,
            'ledger_name' => $BankLedger?->name,
            'ledger_group_code' => $BankLedgerGroup?->name,
            'debit_amount' => $loandata->settled_principal + $loandata->settled_interest,
            'credit_amount' => 0
        ]);

        array_push($postingArray[self::CUSTOMER_ACCOUNT], [
            'ledger_id' => $FinanceLedgerId,
            'ledger_group_id' => $FinanceLedgerGroupId,
            'ledger_code' => $FinanceLedger?->code,
            'ledger_name' => $FinanceLedger?->name,
            'ledger_group_code' => $FinanceLedgerGroup?->name,
            'credit_amount' => $loandata->settled_principal,
            'debit_amount' => 0
        ]);


        array_push($postingArray[self::INTEREST_ACCOUNT], [
            'ledger_id' => $InterestLedgerId,
            'ledger_group_id' => $InterestLedgerGroupId,
            'ledger_code' => $InterestLedger?->code,
            'ledger_name' => $InterestLedger?->name,
            'ledger_group_code' => $InterestLedgerGroup?->name,
            'credit_amount' => $loandata->settled_interest,
            'debit_amount' => 0
        ]);

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

                $totalDebitAmount += $postingValue['debit_amount'];
                $totalCreditAmount += $postingValue['credit_amount'];
            }
        }
        //Balance does not match
        // if (round($totalDebitAmount,6) !== round($totalCreditAmount,6)) {
        //     return array(
        //         'status' => false,
        //         'message' => 'Credit Amount does not match Debit Amount',
        //         'data' => []
        //     );
        // }
        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => 'Financial Book Code is not specified',
                'data' => []
            );
        }

        $currdata = OrganizationCompany::where('id', $document->company_id)->first();
        $excurrdata = CurrencyExchange::where('from_currency_id', $currdata->currency_id)->first();
        $currency = Currency::find($currdata->currency_id);

        $userData = Helper::userCheck();

        $booksdata = Book::where('book_name', 'JOURNAL_VOUCHER')->first();
        $numberPatternData = Helper::generateDocumentNumberNew($booksdata->id, $document->document_date);
        if (!isset($numberPatternData)) {
            return response()->json([
                'message' => "Invalid Book",
                'error' => "",
            ], 422);
        }
        $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : null;

        $voucherHeader = [
            'voucher_no' => $document_number,
            'voucher_name' => $booksdata->book_code,
            'doc_prefix' => $numberPatternData['prefix'],
            'doc_suffix' => $numberPatternData['suffix'],
            'doc_no' => $numberPatternData['doc_no'],
            'doc_reset_pattern' => $numberPatternData['reset_pattern'],
            'document_date' => $document->document_date,
            'book_id' => $booksdata->id,
            'book_type_id' => $booksdata->org_service_id,
            'date' => $document->document_date,
            'amount' => $totalCreditAmount,
            'currency_id' => $currdata->currency_id,
            'currency_code' => $currdata->currency_code,
            'org_currency_id' => $currdata->currency_id,
            'org_currency_code' => $currdata->currency_code,
            'org_currency_exg_rate' => $excurrdata->exchange_rate,
            'comp_currency_id' => $currdata->currency_id, // Missing comma added here
            'comp_currency_code' => $currdata->currency_code,
            'comp_currency_exg_rate' => $excurrdata->exchange_rate,
            'group_currency_id' => $currdata->currency_id,
            'group_currency_code' => $currdata->currency_code,
            'group_currency_exg_rate' => $excurrdata->exchange_rate,
            'reference_service' => $book?->service?->alias,
            'reference_doc_id' => $document->id,
            'group_id' => $document->group_id,
            'company_id' => $document->company_id,
            'organization_id' => $document->organization_id,
            'voucherable_type' => $userData['user_type'],
            'voucherable_id' => $userData['user_id'],
            'approvalStatus' => ConstantHelper::APPROVED,
            'document_status' => ConstantHelper::APPROVED,
            'approvalLevel' => $document->approval_level,
            'remarks' => $remarks,
        ];

        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document);

        return array(
            'status' => true,
            'message' => 'Posting Details found',
            'data' => [
                'voucher_header' => $voucherHeader,
                'voucher_details' => $voucherDetails,
                'document_date' => $document->created_at,
                'ledgers' => $postingArray,
                'total_debit' => $totalDebitAmount,
                'total_credit' => $totalCreditAmount,
                'book_code' => $booksdata?->book_code,
                'document_number' => $document_number,
                'currency_code' => $currency?->short_name
            ]
        );
    }

    public static function loanSettleInvoiceVoucherDetails(int $documentId, string $remarks)
    {
        $accountSetup = isset(self::SERVICE_POSTING_MAPPING[ConstantHelper::LOAN_SETTLEMENT])
            ? self::SERVICE_POSTING_MAPPING[ConstantHelper::LOAN_SETTLEMENT] : [];
        if (!isset($accountSetup) || count($accountSetup) == 0) {
            return array(
                'status' => false,
                'message' => 'Account Setup not found',
                'data' => []
            );
        }
        $document = LoanSettlement::find($documentId);
        $document->currency_id = Helper::getAuthenticatedUser()->organization->currency_id;
        $loandata = $document;
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }
        //Make array according to setup
        $postingArray = array(
            self::CUSTOMER_ACCOUNT => [],
            self::WRITE_OFF_ACCOUNT => [],
        );
        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;
        //Customer Account initialize
        $financesetup = LoanFinancialAccount::first();
        if (!empty($financesetup)) {
            $FinanceLedgerId = $financesetup->pro_ledger_id;
            $FinanceLedgerGroupId = $financesetup->pro_ledger_group_id;
            $FinanceLedger = Ledger::find($FinanceLedgerId);
            $FinanceLedgerGroup = Group::find($FinanceLedgerGroupId);
            $WriteOffLedgerId = $financesetup->wri_ledger_id;
            $WriteOffLedgerGroupId = $financesetup->wri_ledger_group_id;
            $WriteOffLedger = Ledger::find($WriteOffLedgerId);
            $WriteOffLedgerGroup = Group::find($WriteOffLedgerGroupId);
        }

        //Customer Ledger account not found
        if (!isset($FinanceLedger) || !isset($FinanceLedgerGroup)) {
            return array(
                'status' => false,
                'message' => 'Finanical Setup Customer Ledger not setup',
                'data' => []
            );
        }

        //WriteOff Ledger account not found
        if (!isset($WriteOffLedger) || !isset($WriteOffLedgerGroup)) {
            return array(
                'status' => false,
                'message' => 'Finanical Setup WriteOff Ledger not setup',
                'data' => []
            );
        }
        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;



        array_push($postingArray[self::CUSTOMER_ACCOUNT], [
            'ledger_id' => $FinanceLedgerId,
            'ledger_group_id' => $FinanceLedgerGroupId,
            'ledger_code' => $FinanceLedger?->code,
            'ledger_name' => $FinanceLedger?->name,
            'ledger_group_code' => $FinanceLedgerGroup?->name,
            'credit_amount' => Helper::removeCommas($loandata->settle_amnnt),
            'debit_amount' => 0
        ]);


        array_push($postingArray[self::WRITE_OFF_ACCOUNT], [
            'ledger_id' => $WriteOffLedgerId,
            'ledger_group_id' => $WriteOffLedgerGroupId,
            'ledger_code' => $WriteOffLedger?->code,
            'ledger_name' => $WriteOffLedger?->name,
            'ledger_group_code' => $WriteOffLedgerGroup?->name,
            'credit_amount' => 0,
            'debit_amount' => Helper::removeCommas($loandata->settle_amnnt)
        ]);

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

                $totalDebitAmount += $postingValue['debit_amount'];
                $totalCreditAmount += $postingValue['credit_amount'];
            }
        }
        //Balance does not match
        // if (round($totalDebitAmount,6) !== round($totalCreditAmount,6)) {
        //     return array(
        //         'status' => false,
        //         'message' => 'Credit Amount does not match Debit Amount',
        //         'data' => []
        //     );
        // }
        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => 'Financial Book Code is not specified',
                'data' => []
            );
        }

        $currdata = OrganizationCompany::where('id', $document->company_id)->first();
        $excurrdata = CurrencyExchange::where('from_currency_id', $currdata->currency_id)->first();
        $currency = Currency::find($currdata->currency_id);

        $userData = Helper::userCheck();

        $booksdata = Book::where('book_name', 'JOURNAL_VOUCHER')->first();
        $numberPatternData = Helper::generateDocumentNumberNew($booksdata->id, $document->document_date);
        if (!isset($numberPatternData)) {
            return response()->json([
                'message' => "Invalid Book",
                'error' => "",
            ], 422);
        }
        $document_number = $numberPatternData['document_number'] ? $numberPatternData['document_number'] : null;

        $voucherHeader = [
            'voucher_no' => $document_number,
            'voucher_name' => $booksdata->book_code,
            'doc_prefix' => $numberPatternData['prefix'],
            'doc_suffix' => $numberPatternData['suffix'],
            'doc_no' => $numberPatternData['doc_no'],
            'doc_reset_pattern' => $numberPatternData['reset_pattern'],
            'document_date' => $document->document_date,
            'book_id' => $booksdata->id,
            'book_type_id' => $booksdata->org_service_id,
            'date' => $document->document_date,
            'amount' => $totalCreditAmount,
            'currency_id' => $currdata->currency_id,
            'currency_code' => $currdata->currency_code,
            'org_currency_id' => $currdata->currency_id,
            'org_currency_code' => $currdata->currency_code,
            'org_currency_exg_rate' => $excurrdata->exchange_rate,
            'comp_currency_id' => $currdata->currency_id, // Missing comma added here
            'comp_currency_code' => $currdata->currency_code,
            'comp_currency_exg_rate' => $excurrdata->exchange_rate,
            'group_currency_id' => $currdata->currency_id,
            'group_currency_code' => $currdata->currency_code,
            'group_currency_exg_rate' => $excurrdata->exchange_rate,
            'reference_service' => $book?->service?->alias,
            'reference_doc_id' => $document->id,
            'group_id' => $document->group_id,
            'company_id' => $document->company_id,
            'organization_id' => $document->organization_id,
            'voucherable_type' => $userData['user_type'],
            'voucherable_id' => $userData['user_id'],
            'approvalStatus' => ConstantHelper::APPROVED,
            'document_status' => ConstantHelper::APPROVED,
            'approvalLevel' => $document->approval_level,
            'remarks' => $remarks,
        ];

        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document);

        return array(
            'status' => true,
            'message' => 'Posting Details found',
            'data' => [
                'voucher_header' => $voucherHeader,
                'voucher_details' => $voucherDetails,
                'document_date' => $document->created_at,
                'ledgers' => $postingArray,
                'total_debit' => $totalDebitAmount,
                'total_credit' => $totalCreditAmount,
                'book_code' => $booksdata?->book_code,
                'document_number' => $document_number,
                'currency_code' => $currency?->short_name
            ]
        );
    }
    public static function depVoucherDetails(int $documentId, string $type)
    {
        $document = FixedAssetDepreciation::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }

        //Invoice to follow
        $postingArray = array(
            self::ASSET => [],
            self::DEPRECIATION => [],
        );
        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;


        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;
        //COGS SETUP
        $asset_details = json_decode($document->asset_details);
        //COGS SETUP
        foreach ($asset_details as $docItemKey => $docItem) {
            $itemValue = 0;
            $orgCurrencyCost = 0;
            $asset = FixedAssetRegistration::find((int) $docItem->asset_id);
            //$asset->updateTotalDep();
            $docValue = (float) $docItem->dep_amount;
            $assetsCreditAmount = $docValue;
            $assetsLedgerId = $asset->ledger_id;
            $assetsLedgerGroupId = $asset->ledger_group_id;


            $assetsLedger = Ledger::find($asset->ledger_id);
            $assetsLedgerGroup = Group::find($asset->ledger_group_id);
            //LEDGER NOT FOUND
            if (!isset($assetsLedger) || !isset($assetsLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Assets Account not setup';
                break;
            }

            $depLedgerId = $asset->category->setup->dep_ledger_id;
            $depLedgerGroupId = $asset->category->setup->dep_ledger_group_id;
            $depLedger = Ledger::find($depLedgerId);
            $depLedgerGroup = Group::find($depLedgerGroupId);
            if (!isset($depLedger) || !isset($depLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Depreciation Account not setup';
                break;
            }

            $found = false;

            foreach ($postingArray[self::ASSET] as &$entry) {
                if ($entry['ledger_id'] == $assetsLedgerId && $entry['ledger_group_id'] == $assetsLedgerGroupId) {
                    $entry['credit_amount'] += $docValue;
                    $entry['credit_amount_org'] += $docValue;
                    $found = true;
                    break;
                }
            }
            unset($entry);

            if (!$found) {
                $postingArray[self::ASSET][] = [
                    'ledger_id' => $assetsLedgerId,
                    'ledger_group_id' => $assetsLedgerGroupId,
                    'ledger_code' => $assetsLedger?->code,
                    'ledger_name' => $assetsLedger?->name,
                    'ledger_group_code' => $assetsLedgerGroup?->name,
                    'debit_amount' => 0,
                    'debit_amount_org' => 0,
                    'credit_amount' => $docValue,
                    'credit_amount_org' => $docValue,
                ];
            }



            $found = false;

            foreach ($postingArray[self::DEPRECIATION] as &$entry) {
                if ($entry['ledger_id'] == $depLedgerId && $entry['ledger_group_id'] == $depLedgerGroupId) {
                    $entry['debit_amount'] += $docValue;
                    $entry['debit_amount_org'] += $docValue;
                    $found = true;
                    break;
                }
            }
            unset($entry);

            if (!$found) {
                $postingArray[self::DEPRECIATION][] = [
                    'ledger_id' => $depLedgerId,
                    'ledger_group_id' => $depLedgerGroupId,
                    'ledger_code' => $depLedger?->code,
                    'ledger_name' => $depLedger?->name,
                    'ledger_group_code' => $depLedgerGroup?->name,
                    'credit_amount' => 0,
                    'credit_amount_org' => 0,
                    'debit_amount' => $docValue,
                    'debit_amount_org' => $docValue,
                ];
            }
        }
        if ($ledgerErrorStatus) {
            return array(
                'status' => false,
                'message' => $ledgerErrorStatus,
                'data' => []
            );
        }
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;
        //Check debit and credit tally
        foreach ($postingArray as $postAccount) {
            foreach ($postAccount as $postingValue) {
                $totalCreditAmount += $postingValue['credit_amount'];
                $totalDebitAmount += $postingValue['debit_amount'];
            }
        }
        // Balance does not match
        if (trim((string) $totalDebitAmount) != trim((string) $totalCreditAmount)) {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Credit Amount does not match Debit Amount',
                'data' => []
            );
        }
        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
        $currencyExc = CurrencyHelper::getCurrencyExchangeRates($document->currency_id, $document->document_date);
        $currencyExc = $currencyExc['data'];
        $userData = Helper::userCheck();
        $voucherHeader = [
            'voucher_no' => $document->document_number,
            'document_date' => $document->document_date,
            'book_id' => $glPostingBookId,
            'date' => $document->document_date,
            'amount' => $totalCreditAmount,
            'location' => $document->location_id ?? null,
            'currency_id' => $document->currency_id,
            'currency_code' => $currencyExc['party_currency_code'],
            'org_currency_id' => $currencyExc['org_currency_id'],
            'org_currency_code' => $currencyExc['org_currency_code'],
            'org_currency_exg_rate' => $currencyExc['org_currency_exg_rate'],
            'comp_currency_id' => $currencyExc['comp_currency_id'],
            'comp_currency_code' => $currencyExc['comp_currency_code'],
            'comp_currency_exg_rate' => $currencyExc['comp_currency_exg_rate'],
            'group_currency_id' => $currencyExc['group_currency_id'],
            'group_currency_code' => $currencyExc['group_currency_code'],
            'group_currency_exg_rate' => $currencyExc['group_currency_exg_rate'],
            'reference_service' => $book?->service?->alias,
            'reference_doc_id' => $document->id,
            'group_id' => $document->group_id,
            'company_id' => $document->company_id,
            'organization_id' => $document->organization_id,
            'voucherable_type' => $userData['user_type'],
            'voucherable_id' => $userData['user_id'],
            'document_status' => ConstantHelper::APPROVED,
            'approvalLevel' => $document->approval_level
        ];
        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document, 'currency_id', 'document_date', true);
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
                'book_code' => Book::find($glPostingBookId)?->book_code,
                'document_number' => $document->document_number,
                'currency_code' => $currency?->short_name
            ]
        );
    }
    public static function fixedAssetVoucherDetails(int $documentId, string $type)
    {
        $document = FixedAssetRegistration::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }

        $totaltaxes = isset($document->mrnDetail->taxes) ? $document->mrnDetail->taxes->sum('ted_amount') : 0;
        $taxes = isset($document->mrnDetail->taxes) ? $document->mrnDetail->taxes : [];
        //Invoice to follow
        $postingArray = array(
            self::ASSET => [],
            self::TAX_ACCOUNT => [],
            self::VENDOR_ACCOUNT => [],
        );
        $ledgerErrorStatus = null;
        $assetValue = $document->current_value;
        $totalValue = $assetValue + $totaltaxes;



        $assetLedgerId = $document->ledger_id;
        $assetLedgerGroupId = $document->ledger_group_id;
        $assetLedger = Ledger::find($assetLedgerId);
        $assetLedgerGroup = Group::find($assetLedgerGroupId);
        if (!isset($assetLedger) || !isset($assetLedgerGroup)) {
            $ledgerErrorStatus = self::ERROR_PREFIX . 'Asset Account not setup';
        }

        $postingArray[self::ASSET][] = [
            'ledger_id' => $assetLedgerId,
            'ledger_group_id' => $assetLedgerGroupId,
            'ledger_code' => $assetLedger?->code,
            'ledger_name' => $assetLedger?->name,
            'ledger_group_code' => $assetLedgerGroup?->name,
            'credit_amount' => 0,
            'credit_amount_org' => 0,
            'debit_amount' => $assetValue,
            'debit_amount_org' => $assetValue,
        ];

        if (!empty($taxes)) {
            foreach ($taxes as $tax) {
                $taxLedgerId = $tax->taxDetail->ledger_id ?? null;
                $taxLedgerGroupId = $tax->taxDetail->ledger_group_id ?? null;
                $taxLedger = Ledger::find($taxLedgerId);
                $taxLedgerGroup = Group::find($taxLedgerGroupId);
                if (!isset($taxLedger) || !isset($taxLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . $tax->ted_code . ' Tax Account not setup';
                }

                $postingArray[self::TAX_ACCOUNT][] = [
                    'ledger_id' => $taxLedgerId,
                    'ledger_group_id' => $taxLedgerGroupId,
                    'ledger_code' => $taxLedger?->code,
                    'ledger_name' => $taxLedger?->name,
                    'ledger_group_code' => $taxLedgerGroup?->name,
                    'credit_amount' => 0,
                    'credit_amount_org' => 0,
                    'debit_amount' => $tax->ted_amount,
                    'debit_amount_org' => $tax->ted_amount,
                ];
            }
        }


        $vendorLedgerId = $document?->vendor?->ledger_id;
        $vendorLedgerGroupId = $document?->vendor?->ledger_group_id;
        $vendorLedger = Ledger::find($vendorLedgerId);
        $vendorLedgerGroup = Group::find($vendorLedgerGroupId);
        if (!isset($vendorLedger) || !isset($vendorLedgerGroup)) {
            $ledgerErrorStatus = self::ERROR_PREFIX . 'Vendor Account not setup';
        }

        $postingArray[self::VENDOR_ACCOUNT][] = [
            'ledger_id' => $vendorLedgerId,
            'ledger_group_id' => $vendorLedgerGroupId,
            'ledger_code' => $vendorLedger?->code,
            'ledger_name' => $vendorLedger?->name,
            'ledger_group_code' => $vendorLedgerGroup?->name,
            'credit_amount' => $totalValue,
            'credit_amount_org' => $totalValue,
            'debit_amount' => 0,
            'debit_amount_org' => 0,
        ];






        if ($ledgerErrorStatus) {
            return array(
                'status' => false,
                'message' => $ledgerErrorStatus,
                'data' => []
            );
        }
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;


        //Check debit and credit tally
        foreach ($postingArray as $postAccount) {
            foreach ($postAccount as $postingValue) {
                $totalCreditAmount += $postingValue['credit_amount'];
                $totalDebitAmount += $postingValue['debit_amount'];
            }
        }
        // Balance does not match
        if (trim((string) $totalDebitAmount) != trim((string) $totalCreditAmount)) {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Credit Amount does not match Debit Amount',
                'data' => []
            );
        }

        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
        $currencyExc = CurrencyHelper::getCurrencyExchangeRates($document->currency_id, $document->document_date);
        $currencyExc = $currencyExc['data'];
        $userData = Helper::userCheck();
        $voucherHeader = [
            'voucher_no' => $document->document_number,
            'document_date' => $document->document_date,
            'book_id' => $glPostingBookId,
            'date' => $document->document_date,
            'amount' => $totalCreditAmount,
            'location' => $document->location_id ?? null,
            'currency_id' => $document->currency_id,
            'currency_code' => $currencyExc['party_currency_code'],
            'org_currency_id' => $currencyExc['org_currency_id'],
            'org_currency_code' => $currencyExc['org_currency_code'],
            'org_currency_exg_rate' => $currencyExc['org_currency_exg_rate'],
            'comp_currency_id' => $currencyExc['comp_currency_id'],
            'comp_currency_code' => $currencyExc['comp_currency_code'],
            'comp_currency_exg_rate' => $currencyExc['comp_currency_exg_rate'],
            'group_currency_id' => $currencyExc['group_currency_id'],
            'group_currency_code' => $currencyExc['group_currency_code'],
            'group_currency_exg_rate' => $currencyExc['group_currency_exg_rate'],
            'reference_service' => $book?->service?->alias,
            'reference_doc_id' => $document->id,
            'group_id' => $document->group_id,
            'company_id' => $document->company_id,
            'organization_id' => $document->organization_id,
            'voucherable_type' => $userData['user_type'],
            'voucherable_id' => $userData['user_id'],
            'document_status' => ConstantHelper::APPROVED,
            'approvalLevel' => $document->approval_level
        ];
        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document, 'currency_id', 'document_date', true);
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
                'book_code' => Book::find($glPostingBookId)?->book_code,
                'document_number' => $document->document_number,
                'currency_code' => $currency?->short_name
            ]
        );
    }

    public static function revVoucherDetails(int $documentId, string $type)
    {
        $document = FixedAssetRevImp::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }


        //Invoice to follow
        $postingArray = array(
            self::ASSET => [],
            self::SURPLUS_ACCOUNT => [],
        );

        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;
        $totalValue = 0;
        $asset_details = json_decode($document->asset_details);
        foreach ($asset_details as $docItemKey => $docItem) {
            $asset = FixedAssetSub::find($docItem->sub_asset_id);
            $docValue = $docItem->revaluate - $docItem->currentvalue;
            $assetsLedgerId = $asset->asset->ledger_id;
            $assetsLedgerGroupId = $asset->asset->ledger_group_id;
            $totalValue += $docValue;


            $assetsLedger = Ledger::find($assetsLedgerId);
            $assetsLedgerGroup = Group::find($assetsLedgerGroupId);
            //LEDGER NOT FOUND
            if (!isset($assetsLedger) || !isset($assetsLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Assets Account not setup';
                break;
            }


            $found = false;

            foreach ($postingArray[self::ASSET] as &$entry) {
                if ($entry['ledger_id'] == $assetsLedgerId && $entry['ledger_group_id'] == $assetsLedgerGroupId) {
                    $entry['debit_amount'] += $docValue;
                    $entry['debit_amount_org'] += $docValue;
                    $found = true;
                    break;
                }
            }
            unset($entry);

            if (!$found) {
                $postingArray[self::ASSET][] = [
                    'ledger_id' => $assetsLedgerId,
                    'ledger_group_id' => $assetsLedgerGroupId,
                    'ledger_code' => $assetsLedger?->code,
                    'ledger_name' => $assetsLedger?->name,
                    'ledger_group_code' => $assetsLedgerGroup?->name,
                    'credit_amount' => 0,
                    'credit_amount_org' => 0,
                    'debit_amount' => $docValue,
                    'debit_amount_org' => $docValue,
                ];
            }
        }

        $expLedgerId = $document->category->setup->rev_ledger_id;
        $expLedgerGroupId = $document->category->setup->rev_ledger_group_id;
        $expLedger = Ledger::find($expLedgerId);
        $expLedgerGroup = Group::find($expLedgerGroupId);
        if (!isset($expLedger) || !isset($expLedgerGroup)) {
            $ledgerErrorStatus = self::ERROR_PREFIX . 'Surplus Account not setup';
        }

        $postingArray[self::SURPLUS_ACCOUNT][] = [
            'ledger_id' => $expLedgerId,
            'ledger_group_id' => $expLedgerGroupId,
            'ledger_code' => $expLedger?->code,
            'ledger_name' => $expLedger?->name,
            'ledger_group_code' => $expLedgerGroup?->name,
            'credit_amount' => $totalValue,
            'credit_amount_org' => $totalValue,
            'debit_amount' => 0,
            'debit_amount_org' => 0,
        ];



        if ($ledgerErrorStatus) {
            return array(
                'status' => false,
                'message' => $ledgerErrorStatus,
                'data' => []
            );
        }
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;


        //Check debit and credit tally
        foreach ($postingArray as $postAccount) {
            foreach ($postAccount as $postingValue) {
                $totalCreditAmount += $postingValue['credit_amount'];
                $totalDebitAmount += $postingValue['debit_amount'];
            }
        }
        // Balance does not match
        if (trim((string) $totalDebitAmount) != trim((string) $totalCreditAmount)) {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Credit Amount does not match Debit Amount',
                'data' => []
            );
        }

        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
        $currencyExc = CurrencyHelper::getCurrencyExchangeRates($document->currency_id, $document->document_date);
        $currencyExc = $currencyExc['data'];
        $userData = Helper::userCheck();
        $voucherHeader = [
            'voucher_no' => $document->document_number,
            'document_date' => $document->document_date,
            'book_id' => $glPostingBookId,
            'date' => $document->document_date,
            'amount' => $totalCreditAmount,
            'location' => $document->location_id ?? null,
            'currency_id' => $document->currency_id,
            'currency_code' => $currencyExc['party_currency_code'],
            'org_currency_id' => $currencyExc['org_currency_id'],
            'org_currency_code' => $currencyExc['org_currency_code'],
            'org_currency_exg_rate' => $currencyExc['org_currency_exg_rate'],
            'comp_currency_id' => $currencyExc['comp_currency_id'],
            'comp_currency_code' => $currencyExc['comp_currency_code'],
            'comp_currency_exg_rate' => $currencyExc['comp_currency_exg_rate'],
            'group_currency_id' => $currencyExc['group_currency_id'],
            'group_currency_code' => $currencyExc['group_currency_code'],
            'group_currency_exg_rate' => $currencyExc['group_currency_exg_rate'],
            'reference_service' => $book?->service?->alias,
            'reference_doc_id' => $document->id,
            'group_id' => $document->group_id,
            'company_id' => $document->company_id,
            'organization_id' => $document->organization_id,
            'voucherable_type' => $userData['user_type'],
            'voucherable_id' => $userData['user_id'],
            'document_status' => ConstantHelper::APPROVED,
            'approvalLevel' => $document->approval_level
        ];
        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document, 'currency_id', 'document_date', true);
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
                'book_code' => Book::find($glPostingBookId)?->book_code,
                'document_number' => $document->document_number,
                'currency_code' => $currency?->short_name
            ]
        );
    }
    public static function impVoucherDetails(int $documentId, string $type)
    {
        $document = FixedAssetRevImp::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }


        //Invoice to follow
        $postingArray = array(
            self::ASSET => [],
            self::EXPENSE_ACCOUNT => [],
        );

        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;
        $totalValue = 0;
        $asset_details = json_decode($document->asset_details);
        foreach ($asset_details as $docItemKey => $docItem) {
            $asset = FixedAssetSub::find($docItem->sub_asset_id);
            $docValue = $docItem->currentvalue - $docItem->revaluate;
            $assetsLedgerId = $asset->asset->ledger_id;
            $assetsLedgerGroupId = $asset->asset->ledger_group_id;
            $totalValue += $docValue;


            $assetsLedger = Ledger::find($assetsLedgerId);
            $assetsLedgerGroup = Group::find($assetsLedgerGroupId);
            //LEDGER NOT FOUND
            if (!isset($assetsLedger) || !isset($assetsLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Assets Account not setup';
                break;
            }


            $found = false;

            foreach ($postingArray[self::ASSET] as &$entry) {
                if ($entry['ledger_id'] == $assetsLedgerId && $entry['ledger_group_id'] == $assetsLedgerGroupId) {
                    $entry['credit_amount'] += $docValue;
                    $entry['credit_amount_org'] += $docValue;
                    $found = true;
                    break;
                }
            }
            unset($entry);

            if (!$found) {
                $postingArray[self::ASSET][] = [
                    'ledger_id' => $assetsLedgerId,
                    'ledger_group_id' => $assetsLedgerGroupId,
                    'ledger_code' => $assetsLedger?->code,
                    'ledger_name' => $assetsLedger?->name,
                    'ledger_group_code' => $assetsLedgerGroup?->name,
                    'credit_amount' => $docValue,
                    'credit_amount_org' => $docValue,
                    'debit_amount' => 0,
                    'debit_amount_org' => 0,
                ];
            }
        }

        $impLedgerId = $document->category->setup->imp_ledger_id;
        $impLedgerGroupId = $document->category->setup->imp_ledger_group_id;
        $impLedger = Ledger::find($impLedgerId);
        $impLedgerGroup = Group::find($impLedgerGroupId);
        if (!isset($impLedger) || !isset($impLedgerGroup)) {
            $ledgerErrorStatus = self::ERROR_PREFIX . 'Expense Account not setup';
        }

        $postingArray[self::EXPENSE_ACCOUNT][] = [
            'ledger_id' => $impLedgerId,
            'ledger_group_id' => $impLedgerGroupId,
            'ledger_code' => $impLedger?->code,
            'ledger_name' => $impLedger?->name,
            'ledger_group_code' => $impLedgerGroup?->name,
            'credit_amount' => 0,
            'credit_amount_org' => 0,
            'debit_amount' => $totalValue,
            'debit_amount_org' => $totalValue,
        ];



        if ($ledgerErrorStatus) {
            return array(
                'status' => false,
                'message' => $ledgerErrorStatus,
                'data' => []
            );
        }
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;


        //Check debit and credit tally
        foreach ($postingArray as $postAccount) {
            foreach ($postAccount as $postingValue) {
                $totalCreditAmount += $postingValue['credit_amount'];
                $totalDebitAmount += $postingValue['debit_amount'];
            }
        }
        // Balance does not match
        if (trim((string) $totalDebitAmount) != trim((string) $totalCreditAmount)) {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Credit Amount does not match Debit Amount',
                'data' => []
            );
        }

        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
        $currencyExc = CurrencyHelper::getCurrencyExchangeRates($document->currency_id, $document->document_date);
        $currencyExc = $currencyExc['data'];
        $userData = Helper::userCheck();
        $voucherHeader = [
            'voucher_no' => $document->document_number,
            'document_date' => $document->document_date,
            'book_id' => $glPostingBookId,
            'date' => $document->document_date,
            'amount' => $totalCreditAmount,
            'location' => $document->location_id ?? null,
            'currency_id' => $document->currency_id,
            'currency_code' => $currencyExc['party_currency_code'],
            'org_currency_id' => $currencyExc['org_currency_id'],
            'org_currency_code' => $currencyExc['org_currency_code'],
            'org_currency_exg_rate' => $currencyExc['org_currency_exg_rate'],
            'comp_currency_id' => $currencyExc['comp_currency_id'],
            'comp_currency_code' => $currencyExc['comp_currency_code'],
            'comp_currency_exg_rate' => $currencyExc['comp_currency_exg_rate'],
            'group_currency_id' => $currencyExc['group_currency_id'],
            'group_currency_code' => $currencyExc['group_currency_code'],
            'group_currency_exg_rate' => $currencyExc['group_currency_exg_rate'],
            'reference_service' => $book?->service?->alias,
            'reference_doc_id' => $document->id,
            'group_id' => $document->group_id,
            'company_id' => $document->company_id,
            'organization_id' => $document->organization_id,
            'voucherable_type' => $userData['user_type'],
            'voucherable_id' => $userData['user_id'],
            'document_status' => ConstantHelper::APPROVED,
            'approvalLevel' => $document->approval_level
        ];
        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document, 'currency_id', 'document_date', true);
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
                'book_code' => Book::find($glPostingBookId)?->book_code,
                'document_number' => $document->document_number,
                'currency_code' => $currency?->short_name
            ]
        );
    }
    public static function writeOffVoucherDetails(int $documentId, string $type)
    {
        $document = FixedAssetRevImp::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }


        //Invoice to follow
        $postingArray = array(
            self::ASSET => [],
            self::EXPENSE_ACCOUNT => [],
        );

        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;
        $totalValue = 0;
        $asset_details = json_decode($document->asset_details);
        foreach ($asset_details as $docItemKey => $docItem) {
            $asset = FixedAssetSub::find($docItem->sub_asset_id);
            $docValue = $docItem->currentvalue - $docItem->revaluate;
            $assetsLedgerId = $asset->asset->ledger_id;
            $assetsLedgerGroupId = $asset->asset->ledger_group_id;
            $totalValue += $docValue;


            $assetsLedger = Ledger::find($assetsLedgerId);
            $assetsLedgerGroup = Group::find($assetsLedgerGroupId);
            //LEDGER NOT FOUND
            if (!isset($assetsLedger) || !isset($assetsLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Assets Account not setup';
                break;
            }


            $found = false;

            foreach ($postingArray[self::ASSET] as &$entry) {
                if ($entry['ledger_id'] == $assetsLedgerId && $entry['ledger_group_id'] == $assetsLedgerGroupId) {
                    $entry['credit_amount'] += $docValue;
                    $entry['credit_amount_org'] += $docValue;
                    $found = true;
                    break;
                }
            }
            unset($entry);

            if (!$found) {
                $postingArray[self::ASSET][] = [
                    'ledger_id' => $assetsLedgerId,
                    'ledger_group_id' => $assetsLedgerGroupId,
                    'ledger_code' => $assetsLedger?->code,
                    'ledger_name' => $assetsLedger?->name,
                    'ledger_group_code' => $assetsLedgerGroup?->name,
                    'credit_amount' => $docValue,
                    'credit_amount_org' => $docValue,
                    'debit_amount' => 0,
                    'debit_amount_org' => 0,
                ];
            }
        }

        $impLedgerId = $document->category->setup->wri_ledger_id;
        $impLedgerGroupId = $document->category->setup->wri_ledger_group_id;
        $impLedger = Ledger::find($impLedgerId);
        $impLedgerGroup = Group::find($impLedgerGroupId);
        if (!isset($impLedger) || !isset($impLedgerGroup)) {
            $ledgerErrorStatus = self::ERROR_PREFIX . 'Writeoff Expense Account not setup';
        }

        $postingArray[self::EXPENSE_ACCOUNT][] = [
            'ledger_id' => $impLedgerId,
            'ledger_group_id' => $impLedgerGroupId,
            'ledger_code' => $impLedger?->code,
            'ledger_name' => $impLedger?->name,
            'ledger_group_code' => $impLedgerGroup?->name,
            'credit_amount' => 0,
            'credit_amount_org' => 0,
            'debit_amount' => $totalValue,
            'debit_amount_org' => $totalValue,
        ];



        if ($ledgerErrorStatus) {
            return array(
                'status' => false,
                'message' => $ledgerErrorStatus,
                'data' => []
            );
        }
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;


        //Check debit and credit tally
        foreach ($postingArray as $postAccount) {
            foreach ($postAccount as $postingValue) {
                $totalCreditAmount += $postingValue['credit_amount'];
                $totalDebitAmount += $postingValue['debit_amount'];
            }
        }
        // Balance does not match
        if (trim((string) $totalDebitAmount) != trim((string) $totalCreditAmount)) {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Credit Amount does not match Debit Amount',
                'data' => []
            );
        }

        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
        $currencyExc = CurrencyHelper::getCurrencyExchangeRates($document->currency_id, $document->document_date);
        $currencyExc = $currencyExc['data'];
        $userData = Helper::userCheck();
        $voucherHeader = [
            'voucher_no' => $document->document_number,
            'document_date' => $document->document_date,
            'book_id' => $glPostingBookId,
            'date' => $document->document_date,
            'amount' => $totalCreditAmount,
            'location' => $document->location_id ?? null,
            'currency_id' => $document->currency_id,
            'currency_code' => $currencyExc['party_currency_code'],
            'org_currency_id' => $currencyExc['org_currency_id'],
            'org_currency_code' => $currencyExc['org_currency_code'],
            'org_currency_exg_rate' => $currencyExc['org_currency_exg_rate'],
            'comp_currency_id' => $currencyExc['comp_currency_id'],
            'comp_currency_code' => $currencyExc['comp_currency_code'],
            'comp_currency_exg_rate' => $currencyExc['comp_currency_exg_rate'],
            'group_currency_id' => $currencyExc['group_currency_id'],
            'group_currency_code' => $currencyExc['group_currency_code'],
            'group_currency_exg_rate' => $currencyExc['group_currency_exg_rate'],
            'reference_service' => $book?->service?->alias,
            'reference_doc_id' => $document->id,
            'group_id' => $document->group_id,
            'company_id' => $document->company_id,
            'organization_id' => $document->organization_id,
            'voucherable_type' => $userData['user_type'],
            'voucherable_id' => $userData['user_id'],
            'document_status' => ConstantHelper::APPROVED,
            'approvalLevel' => $document->approval_level
        ];
        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document, 'currency_id', 'document_date', true);
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
                'book_code' => Book::find($glPostingBookId)?->book_code,
                'document_number' => $document->document_number,
                'currency_code' => $currency?->short_name
            ]
        );
    }

    public static function fixedAssetMergerVoucherDetails(int $documentId, string $type)
    {
        $document = FixedAssetMerger::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }

        //Invoice to follow
        $postingArray = array(
            self::OLD_ASSET => [],
            self::NEW_ASSET => [],
        );
        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;


        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;
        //COGS SETUP
        $asset_details = json_decode($document->asset_details);
        //COGS SETUP
        foreach ($asset_details as $docItemKey => $docItem) {
            $itemValue = 0;
            $orgCurrencyCost = 0;
            $asset = FixedAssetRegistration::find((int) $docItem->asset_id);
            $docValue = $docItem->currentvalue;
            $assetsCreditAmount = $docValue;
            $assetsLedgerId = $asset->ledger_id;
            $assetsLedgerGroupId = $asset->ledger_group_id;


            $assetsLedger = Ledger::find($asset->ledger_id);
            $assetsLedgerGroup = Group::find($asset->ledger_group_id);
            //LEDGER NOT FOUND
            if (!isset($assetsLedger) || !isset($assetsLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Old Assets Account not setup';
                break;
            }


            $depLedgerId = $document->ledger_id;
            $depLedgerGroupId = $document->ledger_group_id;
            $depLedger = Ledger::find($depLedgerId);
            $depLedgerGroup = Group::find($depLedgerGroupId);
            if (!isset($depLedger) || !isset($depLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'New Asset Account not setup';
                break;
            }
            if ($depLedgerId == $assetsLedgerId) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Old Asset Ledger cannot be the same as New Asset Ledger';
                break;
            }
            //Check for same ledger and group in SALES ACCOUNT
            $existingAssetsLedger = array_filter($postingArray[self::OLD_ASSET], function ($posting) use ($assetsLedgerId, $assetsLedgerGroupId) {
                return $posting['ledger_id'] == $assetsLedgerId && $posting['ledger_group_id'] == $assetsLedgerGroupId;
            });
            //Ledger found
            if (count($existingAssetsLedger) > 0) {
                $existingIndex = array_key_first($existingAssetsLedger);
                $postingArray[self::OLD_ASSET][$existingIndex]['credit_amount'] += $assetsCreditAmount;
                $postingArray[self::OLD_ASSET][$existingIndex]['credit_amount_org'] += $assetsCreditAmount;
            } else { //Assign a new ledger
                array_push($postingArray[self::OLD_ASSET], [
                    'ledger_id' => $assetsLedgerId,
                    'ledger_group_id' => $assetsLedgerGroupId,
                    'ledger_code' => $assetsLedger?->code,
                    'ledger_name' => $assetsLedger?->name,
                    'ledger_group_code' => $assetsLedgerGroup?->name,
                    'debit_amount' => 0,
                    'debit_amount_org' => 0,
                    'credit_amount' => $assetsCreditAmount,
                    'credit_amount_org' => $assetsCreditAmount
                ]);
            }


            //Check for same ledger and group in SALES ACCOUNT
            $existingdepLedger = array_filter($postingArray[self::NEW_ASSET], function ($posting) use ($depLedgerId, $depLedgerGroupId) {
                return $posting['ledger_id'] == $depLedgerId && $posting['ledger_group_id'] == $depLedgerGroupId;
            });
            //Ledger found
            if (count($existingdepLedger) > 0) {
                $existingIndex = array_key_first($existingdepLedger);
                $postingArray[self::NEW_ASSET][$existingIndex]['debit_amount'] += $assetsCreditAmount;
                $postingArray[self::NEW_ASSET][$existingIndex]['debit_amount_org'] += $assetsCreditAmount;
            } else { //Assign a new ledger
                array_push($postingArray[self::NEW_ASSET], [
                    'ledger_id' => $depLedgerId,
                    'ledger_group_id' => $depLedgerGroupId,
                    'ledger_code' => $depLedger?->code,
                    'ledger_name' => $depLedger?->name,
                    'ledger_group_code' => $depLedgerGroup?->name,
                    'credit_amount' => 0,
                    'credit_amount_org' => 0,
                    'debit_amount' => $assetsCreditAmount,
                    'debit_amount_org' => $assetsCreditAmount,
                ]);
            }
        }
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
        //Balance does not match
        // if ($totalDebitAmount !== $totalCreditAmount) {
        //     return array(
        //         'status' => false,
        //         'message' => self::ERROR_PREFIX.'Credit Amount does not match Debit Amount',
        //         'data' => []
        //     );
        // }
        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
        $currencyExc = CurrencyHelper::getCurrencyExchangeRates($document->currency_id, $document->document_date);
        $currencyExc = $currencyExc['data'];
        $userData = Helper::userCheck();
        $voucherHeader = [
            'voucher_no' => $document->document_number,
            'document_date' => $document->document_date,
            'book_id' => $glPostingBookId,
            'date' => $document->document_date,
            'amount' => $totalCreditAmount,
            'location' => $document->location_id ?? null,
            'currency_id' => $document->currency_id,
            'currency_code' => $currencyExc['party_currency_code'],
            'org_currency_id' => $currencyExc['org_currency_id'],
            'org_currency_code' => $currencyExc['org_currency_code'],
            'org_currency_exg_rate' => $currencyExc['org_currency_exg_rate'],
            'comp_currency_id' => $currencyExc['comp_currency_id'],
            'comp_currency_code' => $currencyExc['comp_currency_code'],
            'comp_currency_exg_rate' => $currencyExc['comp_currency_exg_rate'],
            'group_currency_id' => $currencyExc['group_currency_id'],
            'group_currency_code' => $currencyExc['group_currency_code'],
            'group_currency_exg_rate' => $currencyExc['group_currency_exg_rate'],
            'reference_service' => $book?->service?->alias,
            'reference_doc_id' => $document->id,
            'group_id' => $document->group_id,
            'company_id' => $document->company_id,
            'organization_id' => $document->organization_id,
            'voucherable_type' => $userData['user_type'],
            'voucherable_id' => $userData['user_id'],
            'document_status' => ConstantHelper::APPROVED,
            'approvalLevel' => $document->approval_level
        ];
        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document, 'currency_id', 'document_date', true);
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
                'book_code' => Book::find($glPostingBookId)?->book_code,
                'document_number' => $document->document_number,
                'currency_code' => $currency?->short_name
            ]
        );
    }
    public static function fixedAssetSplitVoucherDetails(int $documentId, string $type)
    {
        $document = FixedAssetSplit::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }

        //Invoice to follow
        $postingArray = array(
            self::OLD_ASSET => [],
            self::NEW_ASSET => [],
        );
        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;
        $assetsAmount = (float) $document->current_value;


        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;
        $oldLedgerId = $document->asset->ledger_id;
        $oldLedgerGroupId = $document->asset->ledger_group_id;
        $oldLedger = Ledger::find($oldLedgerId);
        $oldLedgerGroup = Group::find($oldLedgerGroupId);
        if (!isset($oldLedger) || !isset($oldLedgerGroup)) {
            $ledgerErrorStatus = self::ERROR_PREFIX . 'Old Asset Account not setup';
        }
        array_push($postingArray[self::OLD_ASSET], [
            'ledger_id' => $oldLedgerId,
            'ledger_group_id' => $oldLedgerGroupId,
            'ledger_code' => $oldLedger?->code,
            'ledger_name' => $oldLedger?->name,
            'ledger_group_code' => $oldLedgerGroup?->name,
            'credit_amount' => $assetsAmount,
            'credit_amount_org' => $assetsAmount,
            'debit_amount' => 0,
            'debit_amount_org' => 0,
        ]);
        $asset_details = json_decode($document->sub_assets);
        foreach ($asset_details as $docItemKey => $docItem) {
            $docValue = (float) $docItem->current_value;
            $assetsCreditAmount = $docValue;
            $newLedgerId = $docItem->ledger;
            $newLedgerGroupId = $docItem->ledger_group;


            $newLedger = Ledger::find($docItem->ledger);
            $newLedgerGroup = Group::find($docItem->ledger_group);
            //LEDGER NOT FOUND
            if (!isset($newLedger) || !isset($newLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'New Assets Account not setup';
                break;
            }


            if ($oldLedgerId == $newLedgerId) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Old Asset Ledger cannot be the same as New Asset Ledger';
                break;
            }

            //Check for same ledger and group in SALES ACCOUNT
            $existingLedger = array_filter($postingArray[self::NEW_ASSET], function ($posting) use ($newLedgerId, $newLedgerGroupId) {
                return $posting['ledger_id'] == $newLedgerId && $posting['ledger_group_id'] == $newLedgerGroupId;
            });
            //Ledger found
            if (count($existingLedger) > 0) {
                $existingIndex = array_key_first($existingLedger);
                $postingArray[self::NEW_ASSET][$existingIndex]['debit_amount'] += $assetsCreditAmount;
                $postingArray[self::NEW_ASSET][$existingIndex]['debit_amount_org'] += $assetsCreditAmount;
            } else { //Assign a new ledger
                array_push($postingArray[self::NEW_ASSET], [
                    'ledger_id' => $newLedgerId,
                    'ledger_group_id' => $newLedgerGroupId,
                    'ledger_code' => $newLedger?->code,
                    'ledger_name' => $newLedger?->name,
                    'ledger_group_code' => $newLedgerGroup?->name,
                    'credit_amount' => 0,
                    'credit_amount_org' => 0,
                    'debit_amount' => $assetsCreditAmount,
                    'debit_amount_org' => $assetsCreditAmount,
                ]);
            }
        }




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
        //Balance does not match
        if ($totalDebitAmount !== $totalCreditAmount) {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Credit Amount does not match Debit Amount',
                'data' => []
            );
        }
        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
        $currencyExc = CurrencyHelper::getCurrencyExchangeRates($document->currency_id, $document->document_date);
        $currencyExc = $currencyExc['data'];
        $userData = Helper::userCheck();
        $voucherHeader = [
            'voucher_no' => $document->document_number,
            'document_date' => $document->document_date,
            'book_id' => $glPostingBookId,
            'date' => $document->document_date,
            'amount' => $totalCreditAmount,
            'currency_id' => $document->currency_id,
            'location' => $document->location_id ?? null,
            'currency_code' => $currencyExc['party_currency_code'],
            'org_currency_id' => $currencyExc['org_currency_id'],
            'org_currency_code' => $currencyExc['org_currency_code'],
            'org_currency_exg_rate' => $currencyExc['org_currency_exg_rate'],
            'comp_currency_id' => $currencyExc['comp_currency_id'],
            'comp_currency_code' => $currencyExc['comp_currency_code'],
            'comp_currency_exg_rate' => $currencyExc['comp_currency_exg_rate'],
            'group_currency_id' => $currencyExc['group_currency_id'],
            'group_currency_code' => $currencyExc['group_currency_code'],
            'group_currency_exg_rate' => $currencyExc['group_currency_exg_rate'],
            'reference_service' => $book?->service?->alias,
            'reference_doc_id' => $document->id,
            'group_id' => $document->group_id,
            'company_id' => $document->company_id,
            'organization_id' => $document->organization_id,
            'voucherable_type' => $userData['user_type'],
            'voucherable_id' => $userData['user_id'],
            'document_status' => ConstantHelper::APPROVED,
            'approvalLevel' => $document->approval_level
        ];
        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document, 'currency_id', 'document_date', true);
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
                'book_code' => Book::find($glPostingBookId)?->book_code,
                'document_number' => $document->document_number,
                'currency_code' => $currency?->short_name
            ]
        );
    }

    public static function dnVoucherDetails(int $documentId, string $type)
    {
        $document = ErpSaleInvoice::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }

        //Invoice to follow
        $invoiceToFollow = 0;
        $postingArray = array(
            self::CUSTOMER_ACCOUNT => [],
            self::DISCOUNT_ACCOUNT => [],
            self::SALES_ACCOUNT => [],
            self::TAX_ACCOUNT => [],
            self::EXPENSE_ACCOUNT => [],
            self::COGS_ACCOUNT => [],
            self::STOCK_ACCOUNT => []
        );
        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;


        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;
        //COGS SETUP
        foreach ($document->items as $docItemKey => $docItem) {
            $itemValue = 0;
            $orgCurrencyCost = 0;
            $dnDetailId = $docItem?->dnote_item_id;
            $deliveryNote = ErpInvoiceItem::find($dnDetailId);
            $stockLedger = StockLedger::whereIn('book_type', [ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS, ConstantHelper::DELIVERY_CHALLAN_CUM_SI_SERVICE_ALIAS])->where('document_header_id', $deliveryNote?->sale_invoice_id)->where('document_detail_id', $docItem->dnote_item_id)->first();
            if (isset($stockLedger)) {
                $orgCurrencyCost = StockLedger::where('utilized_id', $stockLedger->id)->get()->sum('org_currency_cost');
                $itemValue = $orgCurrencyCost / $document->org_currency_exg_rate;
            }
            // $itemValue = ($docItem -> rate * $docItem -> order_qty * 0.80);//CHANGE
            $stockCreditAmount = round($itemValue, 2);
            $cogsDebitAmount = round($itemValue, 2);

            $cogsLedgerDetails = AccountHelper::getCogsLedgerGroupAndLedgerId($document->organization_id, $docItem->item_id, $document->book_id);
            $cogsLedgerId = is_a($cogsLedgerDetails, Collection::class) ? $cogsLedgerDetails->first()['ledger_id'] : null;
            $cogsLedgerGroupId = is_a($cogsLedgerDetails, Collection::class) ? $cogsLedgerDetails->first()['ledger_group'] : null;
            $cogsLedger = Ledger::find($cogsLedgerId);
            $cogsLedgerGroup = Group::find($cogsLedgerGroupId);
            //LEDGER NOT FOUND
            if (!isset($cogsLedger) || !isset($cogsLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'COGS Account not setup';
                break;
            }
            //Check for same ledger and group in SALES ACCOUNT
            $existingCogsLedger = array_filter($postingArray[self::COGS_ACCOUNT], function ($posting) use ($cogsLedgerId, $cogsLedgerGroupId) {
                return $posting['ledger_id'] == $cogsLedgerId && $posting['ledger_group_id'] == $cogsLedgerGroupId;
            });
            //Ledger found
            if (count($existingCogsLedger) > 0) {
                $postingArray[self::COGS_ACCOUNT][0]['debit_amount'] += $cogsDebitAmount;
                $postingArray[self::COGS_ACCOUNT][0]['debit_amount_org'] += $orgCurrencyCost;
            } else { //Assign a new ledger
                array_push($postingArray[self::COGS_ACCOUNT], [
                    'ledger_id' => $cogsLedgerId,
                    'ledger_group_id' => $cogsLedgerGroupId,
                    'ledger_code' => $cogsLedger?->code,
                    'ledger_name' => $cogsLedger?->name,
                    'ledger_group_code' => $cogsLedgerGroup?->name,
                    'credit_amount' => 0,
                    'credit_amount_org' => 0,
                    'debit_amount' => $cogsDebitAmount,
                    'debit_amount_org' => $orgCurrencyCost
                ]);
            }

            $stockLedgerDetails = AccountHelper::getStockLedgerGroupAndLedgerId($document->organization_id, $docItem->item_id, $document->book_id);
            $stockLedgerId = is_a($stockLedgerDetails, Collection::class) ? $stockLedgerDetails->first()['ledger_id'] : null;
            $stockLedgerGroupId = is_a($stockLedgerDetails, Collection::class) ? $stockLedgerDetails->first()['ledger_group'] : null;
            $stockLedger = Ledger::find($stockLedgerId);
            $stockLedgerGroup = Group::find($stockLedgerGroupId);
            //LEDGER NOT FOUND
            if (!isset($stockLedger) || !isset($stockLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Stock Account not setup';
                break;
            }

            //Check for same ledger and group in SALES ACCOUNT
            $existingstockLedger = array_filter($postingArray[self::STOCK_ACCOUNT], function ($posting) use ($stockLedgerId, $stockLedgerGroupId) {
                return $posting['ledger_id'] == $stockLedgerId && $posting['ledger_group_id'] == $stockLedgerGroupId;
            });
            //Ledger found
            if (count($existingstockLedger) > 0) {
                $postingArray[self::STOCK_ACCOUNT][0]['credit_amount'] += $stockCreditAmount;
                $postingArray[self::STOCK_ACCOUNT][0]['credit_amount_org'] += $orgCurrencyCost;
            } else { //Assign a new ledger
                array_push($postingArray[self::STOCK_ACCOUNT], [
                    'ledger_id' => $stockLedgerId,
                    'ledger_group_id' => $stockLedgerGroupId,
                    'ledger_code' => $stockLedger?->code,
                    'ledger_name' => $stockLedger?->name,
                    'ledger_group_code' => $stockLedgerGroup?->name,
                    'credit_amount' => $stockCreditAmount,
                    'credit_amount_org' => $orgCurrencyCost,
                    'debit_amount' => 0,
                    'debit_amount_org' => 0,
                ]);
            }
        }
        $customerAccountDebit = 0;
        //Customer Account initialize
        if (!$invoiceToFollow) {

            $customer = Customer::find($document->customer_id);
            $customerLedgerId = $customer->ledger_id;
            $customerLedgerGroupId = $customer->ledger_group_id;
            $customerLedger = Ledger::find($customerLedgerId);
            $customerLedgerGroup = Group::find($customerLedgerGroupId);
            //Customer Ledger account not found
            if (!isset($customerLedger) || !isset($customerLedgerGroup)) {
                return array(
                    'status' => false,
                    'message' => self::ERROR_PREFIX . 'Customer Account not setup',
                    'data' => []
                );
            }
            // $discountPostingParam = OrganizationBookParameter::where('book_id', $document -> book_id)
            // -> where('parameter_name', ServiceParametersHelper::GL_SEPERATE_DISCOUNT_PARAM) -> first();
            // if (isset($discountPostingParam)) {
            //     $discountSeperatePosting = $discountPostingParam -> parameter_value[0] === "yes" ? true : false;
            // } else {
            $discountSeperatePosting = false;
            // }
            foreach ($document->items as $docItemKey => $docItem) {
                //Assign Item values
                $itemValue = $docItem->rate * $docItem->order_qty;
                $itemTotalDiscount = $docItem->header_discount_amount + $docItem->item_discount_amount;
                $itemValueAfterDiscount = $itemValue - $itemTotalDiscount;
                //SALES ACCOUNT
                $salesAccountLedgerDetails = AccountHelper::getLedgerGroupAndLedgerIdForSalesAccount($document->organization_id, $document->customer_id, $docItem->item_id, $document->book_id);
                $salesAccountLedgerId = is_a($salesAccountLedgerDetails, Collection::class) ? $salesAccountLedgerDetails->first()['ledger_id'] : null;
                $salesAccountLedgerGroupId = is_a($salesAccountLedgerDetails, Collection::class) ? $salesAccountLedgerDetails->first()['ledger_group'] : null;
                $salesAccountLedger = Ledger::find($salesAccountLedgerId);
                $salesAccountLedgerGroup = Group::find($salesAccountLedgerGroupId);
                //LEDGER NOT FOUND
                if (!isset($salesAccountLedger) || !isset($salesAccountLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'Sales Account not setup';
                    break;
                }
                $salesCreditAmount = $discountSeperatePosting ? $itemValue : $itemValueAfterDiscount;
                //Check for same ledger and group in SALES ACCOUNT
                $existingSalesLedger = array_filter($postingArray[self::SALES_ACCOUNT], function ($posting) use ($salesAccountLedgerId, $salesAccountLedgerGroupId) {
                    return $posting['ledger_id'] == $salesAccountLedgerId && $posting['ledger_group_id'] == $salesAccountLedgerGroupId;
                });
                //Ledger found
                if (count($existingSalesLedger) > 0) {
                    $postingArray[self::SALES_ACCOUNT][0]['credit_amount'] += $salesCreditAmount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::SALES_ACCOUNT], [
                        'ledger_id' => $salesAccountLedgerId,
                        'ledger_group_id' => $salesAccountLedgerGroupId,
                        'ledger_code' => $salesAccountLedger?->code,
                        'ledger_name' => $salesAccountLedger?->name,
                        'ledger_group_code' => $salesAccountLedgerGroup?->name,
                        'credit_amount' => $salesCreditAmount,
                        'debit_amount' => 0
                    ]);
                }
                // //Check for same ledger and group in CUSTOMER ACCOUNT
                // $existingcustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
                //     return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
                // });
                // //Ledger found
                // if (count($existingcustomerLedger) > 0) {
                //     $postingArray[self::CUSTOMER_ACCOUNT][0]['debit_amount'] += $itemValueAfterDiscount;
                // } else { //Assign a new ledger
                //     array_push($postingArray[self::CUSTOMER_ACCOUNT], [
                //         'ledger_id' => $customerLedgerId,
                //         'ledger_group_id' => $customerLedgerGroupId,
                //         'ledger_code' => $customerLedger?->code,
                //         'ledger_name' => $customerLedger?->name,
                //         'ledger_group_code' => $customerLedgerGroup?->name,
                //         'debit_amount' => $itemValueAfterDiscount,
                //         'credit_amount' => 0
                //     ]);
                // }
                $customerAccountDebit += $itemValueAfterDiscount;
            }
            //TAXES ACCOUNT
            $taxes = ErpSaleInvoiceTed::where('sale_invoice_id', $document->id)->where('ted_type', "Tax")->get();
            foreach ($taxes as $tax) {
                $taxDetail = TaxDetail::find($tax->ted_id);
                $taxLedgerId = $taxDetail->ledger_id ?? null; //MAKE IT DYNAMIC
                $taxLedgerGroupId = $taxDetail->ledger_group_id ?? null; //MAKE IT DYNAMIC
                $taxLedger = Ledger::find($taxLedgerId);
                $taxLedgerGroup = Group::find($taxLedgerGroupId);
                if (!isset($taxLedger) || !isset($taxLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'Tax Account not setup';
                    break;
                }
                $existingTaxLedger = array_filter($postingArray[self::TAX_ACCOUNT], function ($posting) use ($taxLedgerId, $taxLedgerGroupId) {
                    return $posting['ledger_id'] == $taxLedgerId && $posting['ledger_group_id'] === $taxLedgerGroupId;
                });
                //Ledger found
                if (count($existingTaxLedger) > 0) {
                    $postingArray[self::TAX_ACCOUNT][0]['credit_amount'] += $tax->ted_amount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::TAX_ACCOUNT], [
                        'ledger_id' => $taxLedgerId,
                        'ledger_group_id' => $taxLedgerGroupId,
                        'ledger_code' => $taxLedger?->code,
                        'ledger_name' => $taxLedger?->name,
                        'ledger_group_code' => $taxLedgerGroup?->name,
                        'credit_amount' => $tax->ted_amount,
                        'debit_amount' => 0,
                    ]);
                }
                // Tax for CUSTOMER ACCOUNT
                // $existingCustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
                //     return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
                // });
                // //Ledger found
                // if (count($existingCustomerLedger) > 0) {
                //     $postingArray[self::CUSTOMER_ACCOUNT][0]['debit_amount'] += $tax->ted_amount;
                // } else { //Assign new ledger
                //     array_push($postingArray[self::CUSTOMER_ACCOUNT], [
                //         'ledger_id' => $taxLedgerId,
                //         'ledger_group_id' => $taxLedgerGroupId,
                //         'ledger_code' => $taxLedger?->code,
                //         'ledger_name' => $taxLedger?->name,
                //         'ledger_group_code' => $taxLedgerGroup?->name,
                //         'credit_amount' => 0,
                //         'debit_amount' => $tax->ted_amount,
                //     ]);
                // }
                $customerAccountDebit += $tax->ted_amount;
            }
            //EXPENSES
            $expenses = ErpSaleInvoiceTed::where('sale_invoice_id', $document->id)->where('ted_type', "Expense")->get();
            foreach ($expenses as $expense) {
                $expenseDetail = ExpenseMaster::find($expense->ted_id);
                $expenseLedgerId = $expenseDetail?->expense_ledger_id; //MAKE IT DYNAMIC - 5
                $expenseLedgerGroupId = $expenseDetail?->expense_ledger_group_id; //MAKE IT DYNAMIC - 9
                $expenseLedger = Ledger::find($expenseLedgerId);
                $expenseLedgerGroup = Group::find($expenseLedgerGroupId);
                if (!isset($expenseLedger) || !isset($expenseLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'Expense Account not setup';
                    break;
                }
                $existingExpenseLedger = array_filter($postingArray[self::EXPENSE_ACCOUNT], function ($posting) use ($expenseLedgerId, $expenseLedgerGroupId) {
                    return $posting['ledger_id'] == $expenseLedgerId && $posting['ledger_group_id'] === $expenseLedgerGroupId;
                });
                //Ledger found
                if (count($existingExpenseLedger) > 0) {
                    $postingArray[self::EXPENSE_ACCOUNT][0]['credit_amount'] += $expense->ted_amount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::EXPENSE_ACCOUNT], [
                        'ledger_id' => $expenseLedgerId,
                        'ledger_group_id' => $expenseLedgerGroupId,
                        'ledger_code' => $expenseLedger?->code,
                        'ledger_name' => $expenseLedger?->name,
                        'ledger_group_code' => $expenseLedgerGroup?->name,
                        'credit_amount' => $expense->ted_amount,
                        'debit_amount' => 0,
                    ]);
                }
                // //Expense for CUSTOMER ACCOUNT
                // $existingCustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
                //     return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
                // });
                // //Ledger found
                // if (count($existingCustomerLedger) > 0) {
                //     $postingArray[self::CUSTOMER_ACCOUNT][0]['debit_amount'] += $expense->ted_amount;
                // } else { //Assign new ledger
                //     array_push($postingArray[self::EXPENSE_ACCOUNT], [
                //         'ledger_id' => $expenseLedgerId,
                //         'ledger_group_id' => $expenseLedgerGroupId,
                //         'ledger_code' => $expenseLedger?->code,
                //         'ledger_name' => $expenseLedger?->name,
                //         'ledger_group_code' => $expenseLedgerGroup?->name,
                //         'credit_amount' => 0,
                //         'debit_amount' => $expense->ted_amount,
                //     ]);
                // }
                $customerAccountDebit += $expense->ted_amount;
            }
            //Seperate posting of Discount
            if ($discountSeperatePosting) {
                $discounts = ErpSaleInvoiceTed::where('sale_invoice_id', $document->id)->where('ted_type', "Discount")->get();
                foreach ($discounts as $discount) {
                    $discountDetail = DiscountMaster::find($discount->ted_id);
                    $discountLedgerId = $discountDetail?->discount_ledger_id; //MAKE IT DYNAMIC
                    $discountLedgerGroupId = $discountDetail?->discount_ledger_group_id; //MAKE IT DYNAMIC
                    $discountLedger = Ledger::find($discountLedgerId);
                    $discountLedgerGroup = Group::find($discountLedgerGroupId);
                    if (!isset($discountLedger) || !isset($discountLedgerGroup)) {
                        $ledgerErrorStatus = self::ERROR_PREFIX . 'Discount Account not setup';
                        break;
                    }
                    $existingDiscountLedger = array_filter($postingArray[self::DISCOUNT_ACCOUNT], function ($posting) use ($discountLedgerId, $discountLedgerGroupId) {
                        return $posting['ledger_id'] == $discountLedgerId && $posting['ledger_group_id'] === $discountLedgerGroupId;
                    });
                    //Ledger found
                    if (count($existingDiscountLedger) > 0) {
                        $postingArray[self::DISCOUNT_ACCOUNT][0]['debit_amount'] += $discount->ted_amount;
                    } else { //Assign a new ledger
                        array_push($postingArray[self::DISCOUNT_ACCOUNT], [
                            'ledger_id' => $discountLedgerId,
                            'ledger_group_id' => $discountLedgerGroupId,
                            'ledger_code' => $discountLedger?->code,
                            'ledger_name' => $discountLedger?->name,
                            'ledger_group_code' => $discountLedgerGroup?->name,
                            'debit_amount' => $discount->ted_amount,
                            'credit_amount' => 0,
                        ]);
                    }
                }
            }
        }
        //Break Customer Account according to payment terms schedule - due date wise
        $invoicePaymentTerms = $document->payment_term_schedules()
            ->select('due_date', DB::raw('SUM(percent) as total_percentage'))->groupBy('due_date')->get();
        $totalPaymentTermsAmount = 0;
        if ($invoicePaymentTerms && count($invoicePaymentTerms)) {
            foreach ($invoicePaymentTerms as $invoicePaymentTerm) {
                $currentAmount = $customerAccountDebit * ($invoicePaymentTerm->total_percentage / 100);
                $totalPaymentTermsAmount += $currentAmount;
                //Check for same ledger and group in CUSTOMER ACCOUNT
                $existingcustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId, $invoicePaymentTerm) {
                    return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId && $posting['due_date'] === $invoicePaymentTerm->due_date;
                });
                //Ledger found
                if (count($existingcustomerLedger) > 0) {
                    $postingArray[self::CUSTOMER_ACCOUNT][0]['debit_amount'] += $currentAmount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::CUSTOMER_ACCOUNT], [
                        'ledger_id' => $customerLedgerId,
                        'ledger_group_id' => $customerLedgerGroupId,
                        'ledger_code' => $customerLedger?->code,
                        'ledger_name' => $customerLedger?->name,
                        'ledger_group_code' => $customerLedgerGroup?->name,
                        'debit_amount' => $currentAmount,
                        'credit_amount' => 0,
                        'due_date' => $invoicePaymentTerm->due_date,
                    ]);
                }
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
        //Balance does not match
        // if (round($totalDebitAmount,6) !== round($totalCreditAmount,6)) {
        //     return array(
        //         'status' => false,
        //         'message' => self::ERROR_PREFIX.'Credit Amount does not match Debit Amount',
        //         'data' => []
        //     );
        // }
        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
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
        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document, 'currency_id', 'document_date', true);
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

    public static function transportVoucherDetails(int $documentId, string $type)
    {

        $document = ErpTransportInvoice::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }

        //Invoice to follow
        $invoiceToFollow = 0;
        $postingArray = array(
            self::CUSTOMER_ACCOUNT => [],
            self::DISCOUNT_ACCOUNT => [],
            self::SALES_ACCOUNT => [],
            self::TAX_ACCOUNT => [],
            self::EXPENSE_ACCOUNT => [],
            self::COGS_ACCOUNT => [],
            self::STOCK_ACCOUNT => []
        );
        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;

        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;

        $customerAccountDebit = 0;
        //Customer Account initialize
        if (!$invoiceToFollow) {

            $customer = Customer::find($document->customer_id);
            $customerLedgerId = $customer->ledger_id;
            $customerLedgerGroupId = $customer->ledger_group_id;
            $customerLedger = Ledger::find($customerLedgerId);
            $customerLedgerGroup = Group::find($customerLedgerGroupId);
            //Customer Ledger account not found
            if (!isset($customerLedger) || !isset($customerLedgerGroup)) {
                return array(
                    'status' => false,
                    'message' => self::ERROR_PREFIX . 'Customer Account not setup',
                    'data' => []
                );
            }

            $discountSeperatePosting = false;
            foreach ($document->items as $docItemKey => $docItem) {
                //Assign Item values
                $itemValue = $docItem->rate * $docItem->order_qty;
                $itemTotalDiscount = $docItem->header_discount_amount + $docItem->item_discount_amount;
                $itemValueAfterDiscount = $itemValue - $itemTotalDiscount;
                //SALES ACCOUNT
                $salesAccountLedgerDetails = AccountHelper::getLedgerGroupAndLedgerIdForSalesAccount($document->organization_id, $document->customer_id, $docItem->item_id, $document->book_id);
                $salesAccountLedgerId = is_a($salesAccountLedgerDetails, Collection::class) ? $salesAccountLedgerDetails->first()['ledger_id'] : null;
                $salesAccountLedgerGroupId = is_a($salesAccountLedgerDetails, Collection::class) ? $salesAccountLedgerDetails->first()['ledger_group'] : null;
                $salesAccountLedger = Ledger::find($salesAccountLedgerId);
                $salesAccountLedgerGroup = Group::find($salesAccountLedgerGroupId);
                //LEDGER NOT FOUND
                if (!isset($salesAccountLedger) || !isset($salesAccountLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'Sales Account not setup';
                    break;
                }
                $salesCreditAmount = $discountSeperatePosting ? $itemValue : $itemValueAfterDiscount;
                //Check for same ledger and group in SALES ACCOUNT
                $existingSalesLedger = array_filter($postingArray[self::SALES_ACCOUNT], function ($posting) use ($salesAccountLedgerId, $salesAccountLedgerGroupId) {
                    return $posting['ledger_id'] == $salesAccountLedgerId && $posting['ledger_group_id'] == $salesAccountLedgerGroupId;
                });
                //Ledger found
                if (count($existingSalesLedger) > 0) {
                    $postingArray[self::SALES_ACCOUNT][0]['credit_amount'] += $salesCreditAmount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::SALES_ACCOUNT], [
                        'ledger_id' => $salesAccountLedgerId,
                        'ledger_group_id' => $salesAccountLedgerGroupId,
                        'ledger_code' => $salesAccountLedger?->code,
                        'ledger_name' => $salesAccountLedger?->name,
                        'ledger_group_code' => $salesAccountLedgerGroup?->name,
                        'credit_amount' => $salesCreditAmount,
                        'debit_amount' => 0
                    ]);
                }

                $customerAccountDebit += $itemValueAfterDiscount;
            }
            //TAXES ACCOUNT
            $taxes = ErpTransportInvoiceTed::where('transport_invoice_id', $document->id)->where('ted_type', "Tax")->get();
            foreach ($taxes as $tax) {
                $taxDetail = TaxDetail::find($tax->ted_id);
                $taxLedgerId = $taxDetail->ledger_id ?? null; //MAKE IT DYNAMIC
                $taxLedgerGroupId = $taxDetail->ledger_group_id ?? null; //MAKE IT DYNAMIC
                $taxLedger = Ledger::find($taxLedgerId);
                $taxLedgerGroup = Group::find($taxLedgerGroupId);
                if (!isset($taxLedger) || !isset($taxLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'Tax Account not setup';
                    break;
                }
                $existingTaxLedger = array_filter($postingArray[self::TAX_ACCOUNT], function ($posting) use ($taxLedgerId, $taxLedgerGroupId) {
                    return $posting['ledger_id'] == $taxLedgerId && $posting['ledger_group_id'] === $taxLedgerGroupId;
                });
                //Ledger found
                if (count($existingTaxLedger) > 0) {
                    $postingArray[self::TAX_ACCOUNT][0]['credit_amount'] += $tax->ted_amount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::TAX_ACCOUNT], [
                        'ledger_id' => $taxLedgerId,
                        'ledger_group_id' => $taxLedgerGroupId,
                        'ledger_code' => $taxLedger?->code,
                        'ledger_name' => $taxLedger?->name,
                        'ledger_group_code' => $taxLedgerGroup?->name,
                        'credit_amount' => $tax->ted_amount,
                        'debit_amount' => 0,
                    ]);
                }

                $customerAccountDebit += $tax->ted_amount;
            }

        }

        $totalPaymentTermsAmount = 0;
        $invoicePaymentTerms = [];

        //Check for same ledger and group in CUSTOMER ACCOUNT
        $customer = Customer::find($document->customer_id);
        $customerLedgerId = $customer->ledger_id;
        $customerLedgerGroupId = $customer->ledger_group_id;
        $customerLedger = Ledger::find($customerLedgerId);
        $customerLedgerGroup = Group::find($customerLedgerGroupId);
        //Customer Ledger account not found
        if (!isset($customerLedger) || !isset($customerLedgerGroup)) {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Customer Account not setup',
                'data' => []
            );
        }
        $existingcustomerLedger = array_filter($postingArray[self::CUSTOMER_ACCOUNT], function ($posting) use ($customerLedgerId, $customerLedgerGroupId) {
            return $posting['ledger_id'] == $customerLedgerId && $posting['ledger_group_id'] === $customerLedgerGroupId;
        });
        //Ledger found
        if (count($existingcustomerLedger) > 0) {
            $postingArray[self::CUSTOMER_ACCOUNT][0]['debit_amount'] += $salesCreditAmount;
        } else { //Assign a new ledger
            array_push($postingArray[self::CUSTOMER_ACCOUNT], [
                'ledger_id' => $customerLedgerId,
                'ledger_group_id' => $customerLedgerGroupId,
                'ledger_code' => $customerLedger?->code,
                'ledger_name' => $customerLedger?->name,
                'ledger_group_code' => $customerLedgerGroup?->name,
                'debit_amount' => $customerAccountDebit,
                'credit_amount' => 0,
            ]);
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
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
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

        $voucherDetails = self::generateVoucherDetailsArray(
            $postingArray,
            $voucherHeader,
            $document,
            'currency_id',
            'document_date',
            true
        );

        // har detail me se due_date hatao
        foreach ($voucherDetails as &$detail) {
            if (isset($detail['due_date'])) {
                unset($detail['due_date']);
            }
        }
        unset($detail); // reference clear
        // dd($voucherDetails);

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

    public static function mrnVoucherDetails(int $documentId, string $type)
    {
        $document = MrnHeader::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }
        //Invoice to follow
        $invoiceToFollow = ($document->bill_to_follow == 'yes') ? true : false;
        $postingArray = array(
            self::STOCK_ACCOUNT => [],
            self::TAX_ACCOUNT => [],
            self::EXPENSE_ACCOUNT => [],
            self::DISCOUNT_ACCOUNT => [],
            self::GRIR_ACCOUNT => [],
            self::PR_ACCOUNT => [],
            self::SUPPLIER_ACCOUNT => [],
        );
        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;
        $supplierAccountCredit = 0;

        // Vendor Detail
        $vendor = Vendor::find($document->vendor_id);
        $vendorLedgerId = $vendor->ledger_id;
        $vendorLedgerGroupId = $vendor->ledger_group_id;
        $vendorLedger = Ledger::find($vendorLedgerId);
        $vendorLedgerGroup = Group::find($vendorLedgerGroupId);

        //Vendor Ledger account not found
        if (!isset($vendorLedger) || !isset($vendorLedgerGroup)) {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Vendor Account not setup',
                'data' => []
            );
        }

        // $discountPostingParam = OrganizationBookParameter::where('book_id', $document -> book_id)
        // -> where('parameter_name', ServiceParametersHelper::GL_SEPERATE_DISCOUNT_PARAM) -> first();
        // if (isset($discountPostingParam)) {
        //     $discountSeperatePosting = $discountPostingParam -> parameter_value[0] === "yes" ? true : false;
        // } else {
        $discountSeperatePosting = false;
        // }

        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;
        //COGS SETUP
        foreach ($document->items as $docItemKey => $docItem) {
            $itemValue = ($docItem->rate * $docItem->accepted_qty);

            $itemTotalDiscount = $docItem->header_discount_amount + $docItem->discount_amount;
            $itemValueAfterDiscount = $itemValue - $itemTotalDiscount;
            $stockDebitAmount = $discountSeperatePosting ? $itemValue : $itemValueAfterDiscount;
            $rejectedValue = ($itemValueAfterDiscount / $docItem->accepted_qty) * $docItem->rejected_qty;

            // Stock Account
            $stockLedgerDetails = AccountHelper::getStockLedgerGroupAndLedgerId($document->organization_id, $docItem->item_id, $document->book_id);

            $stockLedgerId = is_a($stockLedgerDetails, Collection::class) ? @$stockLedgerDetails->first()['ledger_id'] : null;
            $stockLedgerGroupId = is_a($stockLedgerDetails, Collection::class) ? @$stockLedgerDetails->first()['ledger_group'] : null;
            $stockLedger = Ledger::find($stockLedgerId);
            $stockLedgerGroup = Group::find($stockLedgerGroupId);
            //LEDGER NOT FOUND
            if (!isset($stockLedger) || !isset($stockLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Stock Account not setup';
                break;
            }

            //Check for same ledger and group in SALES ACCOUNT
            $existingstockLedger = array_filter($postingArray[self::STOCK_ACCOUNT], function ($posting) use ($stockLedgerId, $stockLedgerGroupId) {
                return $posting['ledger_id'] == $stockLedgerId && $posting['ledger_group_id'] == $stockLedgerGroupId;
            });
            //Ledger found
            if (count($existingstockLedger) > 0) {
                $postingArray[self::STOCK_ACCOUNT][0]['debit_amount'] += $stockDebitAmount;
            } else { //Assign a new ledger
                array_push($postingArray[self::STOCK_ACCOUNT], [
                    'ledger_id' => $stockLedgerId,
                    'ledger_group_id' => $stockLedgerGroupId,
                    'ledger_code' => $stockLedger?->code,
                    'ledger_name' => $stockLedger?->name,
                    'ledger_group_code' => $stockLedgerGroup?->name,
                    'credit_amount' => 0,
                    'debit_amount' => $stockDebitAmount
                ]);
            }

            // Purchase Return Account For Rejected Stock
            if ($rejectedValue > 0) {
                $stockLedgerDetails = AccountHelper::getPurchaseReturnLedgerGroupAndLedgerId($document->organization_id, $docItem->item_id, $document->book_id);

                $stockLedgerId = is_a($stockLedgerDetails, Collection::class) ? @$stockLedgerDetails->first()['ledger_id'] : null;
                $stockLedgerGroupId = is_a($stockLedgerDetails, Collection::class) ? @$stockLedgerDetails->first()['ledger_group'] : null;
                $stockLedger = Ledger::find($stockLedgerId);
                $stockLedgerGroup = Group::find($stockLedgerGroupId);
                //LEDGER NOT FOUND
                if (!isset($stockLedger) || !isset($stockLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'Purchase Return Account not setup';
                    break;
                }

                //Check for same ledger and group in SALES ACCOUNT
                $existingstockLedger = array_filter($postingArray[self::PR_ACCOUNT], function ($posting) use ($stockLedgerId, $stockLedgerGroupId) {
                    return $posting['ledger_id'] == $stockLedgerId && $posting['ledger_group_id'] == $stockLedgerGroupId;
                });
                //Ledger found
                if (count($existingstockLedger) > 0) {
                    $postingArray[self::PR_ACCOUNT][0]['debit_amount'] += $rejectedValue;
                } else { //Assign a new ledger
                    array_push($postingArray[self::PR_ACCOUNT], [
                        'ledger_id' => $stockLedgerId,
                        'ledger_group_id' => $stockLedgerGroupId,
                        'ledger_code' => $stockLedger?->code,
                        'ledger_name' => $stockLedger?->name,
                        'ledger_group_code' => $stockLedgerGroup?->name,
                        'credit_amount' => 0,
                        'debit_amount' => $rejectedValue
                    ]);
                }
            }
            if ($invoiceToFollow) {
                $grirCreditAmount = ($itemValueAfterDiscount + $rejectedValue);
                $grirLedgerDetails = AccountHelper::getGrLedgerGroupAndLedgerId($document->organization_id, $docItem->item_id, $document->book_id);
                $grirLedgerId = is_a($grirLedgerDetails, Collection::class) ? @$grirLedgerDetails->first()['ledger_id'] : null;
                $grirLedgerGroupId = is_a($grirLedgerDetails, Collection::class) ? @$grirLedgerDetails->first()['ledger_group'] : null;
                $grirLedger = Ledger::find($grirLedgerId);
                $grirLedgerGroup = Group::find($grirLedgerGroupId);
                //LEDGER NOT FOUND
                if (!isset($grirLedger) || !isset($grirLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'GR/IR Account not setup';
                    break;
                }
                //Check for same ledger and group in SALES ACCOUNT
                $existingGrirLedger = array_filter($postingArray[self::GRIR_ACCOUNT], function ($posting) use ($grirLedgerId, $grirLedgerGroupId) {
                    return $posting['ledger_id'] == $grirLedgerId && $posting['ledger_group_id'] == $grirLedgerGroupId;
                });
                //Ledger found
                if (count($existingGrirLedger) > 0) {
                    $postingArray[self::GRIR_ACCOUNT][0]['credit_amount'] += $grirCreditAmount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::GRIR_ACCOUNT], [
                        'ledger_id' => $grirLedgerId,
                        'ledger_group_id' => $grirLedgerGroupId,
                        'ledger_code' => $grirLedger?->code,
                        'ledger_name' => $grirLedger?->name,
                        'ledger_group_code' => $grirLedgerGroup?->name,
                        'credit_amount' => $grirCreditAmount,
                        'debit_amount' => 0
                    ]);
                }
            } else {
                //Stock for SUPPLIER ACCOUNT
                $supplierCreditAmount = ($itemValueAfterDiscount + $rejectedValue);
                $supplierAccountCredit += $supplierCreditAmount;
            }
        }

        if (!$invoiceToFollow) {
            //TAXES ACCOUNT
            $taxes = MrnExtraAmount::where('mrn_header_id', $document->id)->where('ted_type', "Tax")->get();
            foreach ($taxes as $tax) {
                $taxDetail = TaxDetail::find($tax->ted_id);
                $taxLedgerId = $taxDetail->ledger_id ?? null; //MAKE IT DYNAMIC
                $taxLedgerGroupId = $taxDetail->ledger_group_id ?? null; //MAKE IT DYNAMIC
                $taxLedger = Ledger::find($taxLedgerId);
                $taxLedgerGroup = Group::find($taxLedgerGroupId);
                if (!isset($taxLedger) || !isset($taxLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'Tax Account not setup';
                    break;
                }
                $existingTaxLedger = array_filter($postingArray[self::TAX_ACCOUNT], function ($posting) use ($taxLedgerId, $taxLedgerGroupId) {
                    return $posting['ledger_id'] == $taxLedgerId && $posting['ledger_group_id'] === $taxLedgerGroupId;
                });
                //Ledger found
                if (count($existingTaxLedger) > 0) {
                    $postingArray[self::TAX_ACCOUNT][0]['debit_amount'] += $tax->ted_amount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::TAX_ACCOUNT], [
                        'ledger_id' => $taxLedgerId,
                        'ledger_group_id' => $taxLedgerGroupId,
                        'ledger_code' => $taxLedger?->code,
                        'ledger_name' => $taxLedger?->name,
                        'ledger_group_code' => $taxLedgerGroup?->name,
                        'credit_amount' => 0,
                        'debit_amount' => $tax->ted_amount,
                    ]);
                }
                //Tax for SUPPLIER ACCOUNT
                $supplierAccountCredit += $tax->ted_amount;
            }

            //EXPENSES
            $expenses = MrnExtraAmount::where('mrn_header_id', $document->id)->where('ted_type', "Expense")->get();
            foreach ($expenses as $expense) {
                $totalExpAmount = ($expense->ted_amount + $expense->tax_amount);
                $expenseDetail = ExpenseMaster::find($expense->ted_id);
                $expenseLedgerId = $expenseDetail?->expense_ledger_id; //MAKE IT DYNAMIC - 5
                $expenseLedgerGroupId = $expenseDetail?->expense_ledger_group_id; //MAKE IT DYNAMIC - 9
                $expenseLedger = Ledger::find($expenseLedgerId);
                $expenseLedgerGroup = Group::find($expenseLedgerGroupId);
                if (!isset($expenseLedger) || !isset($expenseLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'Expense Account not setup';
                    break;
                }
                $existingExpenseLedger = array_filter($postingArray[self::EXPENSE_ACCOUNT], function ($posting) use ($expenseLedgerId, $expenseLedgerGroupId) {
                    return $posting['ledger_id'] == $expenseLedgerId && $posting['ledger_group_id'] === $expenseLedgerGroupId;
                });
                //Ledger found
                if (count($existingExpenseLedger) > 0) {
                    $postingArray[self::EXPENSE_ACCOUNT][0]['debit_amount'] += $totalExpAmount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::EXPENSE_ACCOUNT], [
                        'ledger_id' => $expenseLedgerId,
                        'ledger_group_id' => $expenseLedgerGroupId,
                        'ledger_code' => $expenseLedger?->code,
                        'ledger_name' => $expenseLedger?->name,
                        'ledger_group_code' => $expenseLedgerGroup?->name,
                        'credit_amount' => 0,
                        'debit_amount' => $totalExpAmount,
                    ]);
                }
                //Expense for SUPPLIER ACCOUNT
                $supplierAccountCredit += $totalExpAmount;
            }

            //Break Supplier Account according to payment terms schedule - due date wise
            $invoicePaymentTerms = $document->payment_term_schedules()
                ->select('due_date', DB::raw('SUM(percent) as total_percentage'))->groupBy('due_date')->get();
            $totalPaymentTermsAmount = 0;
            if ($invoicePaymentTerms && count($invoicePaymentTerms)) {
                foreach ($invoicePaymentTerms as $invoicePaymentTerm) {
                    $currentAmount = $supplierAccountCredit * ($invoicePaymentTerm->total_percentage / 100);
                    $totalPaymentTermsAmount += $currentAmount;
                    //Check for same ledger and group in SUPPLIER ACCOUNT
                    $existingVendorLedger = array_filter($postingArray[self::SUPPLIER_ACCOUNT], function ($posting) use ($vendorLedgerId, $vendorLedgerGroupId, $invoicePaymentTerm) {
                        return $posting['ledger_id'] == $vendorLedgerId && $posting['ledger_group_id'] === $vendorLedgerGroupId && $posting['due_date'] === $invoicePaymentTerm->due_date;
                    });
                    //Ledger found
                    if (count($existingVendorLedger) > 0) {
                        $postingArray[self::SUPPLIER_ACCOUNT][0]['credit_amount'] += $currentAmount;
                    } else { //Assign a new ledger
                        array_push($postingArray[self::SUPPLIER_ACCOUNT], [
                            'ledger_id' => $vendorLedgerId,
                            'ledger_group_id' => $vendorLedgerGroupId,
                            'ledger_code' => $vendorLedger?->code,
                            'ledger_name' => $vendorLedger?->name,
                            'ledger_group_code' => $vendorLedgerGroup?->name,
                            'debit_amount' => 0,
                            'credit_amount' => $currentAmount,
                            'due_date' => $invoicePaymentTerm->due_date,
                        ]);
                    }
                }
            }
        }
        //Seperate posting of Discount
        if ($discountSeperatePosting) {
            $discounts = MrnExtraAmount::where('mrn_header_id', $document->id)->where('ted_type', "Discount")->get();
            foreach ($discounts as $discount) {
                $discountDetail = DiscountMaster::find($discount->ted_id);
                $discountLedgerId = $discountDetail?->discount_ledger_id; //MAKE IT DYNAMIC
                $discountLedgerGroupId = $discountDetail?->discount_ledger_group_id; //MAKE IT DYNAMIC
                $discountLedger = Ledger::find($discountLedgerId);
                $discountLedgerGroup = Group::find($discountLedgerGroupId);
                if (!isset($discountLedger) || !isset($discountLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'Discount Account not setup';
                    break;
                }
                $existingDiscountLedger = array_filter($postingArray[self::DISCOUNT_ACCOUNT], function ($posting) use ($discountLedgerId, $discountLedgerGroupId) {
                    return $posting['ledger_id'] == $discountLedgerId && $posting['ledger_group_id'] === $discountLedgerGroupId;
                });
                //Ledger found
                if (count($existingDiscountLedger) > 0) {
                    $postingArray[self::DISCOUNT_ACCOUNT][0]['credit_amount'] += $discount->ted_amount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::DISCOUNT_ACCOUNT], [
                        'ledger_id' => $discountLedgerId,
                        'ledger_group_id' => $discountLedgerGroupId,
                        'ledger_code' => $discountLedger?->code,
                        'ledger_name' => $discountLedger?->name,
                        'ledger_group_code' => $discountLedgerGroup?->name,
                        'debit_amount' => 0,
                        'credit_amount' => $discount->ted_amount,
                    ]);
                }
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
                'message' => self::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
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
        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document);

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

    public static function moVoucherDetails(int $documentId, string $type)
    {
        $document = MfgOrder::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }

        $postingArray = array(
            self::WIP_ACCOUNT => [],
            self::RM_ACCOUNT => []
        );

        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;

        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;
        //COGS SETUP
        foreach ($document->moProductions as $docItemKey => $docItem) {
            $itemValue = round(($docItem->rate * $docItem->produced_qty), 2);
            $WipDebitAccount = $itemValue;
            $WipLedgerDetails = AccountHelper::getStockLedgerGroupAndLedgerId($document->organization_id, $docItem->item_id, $document->book_id);

            $wipLedgerId = is_a($WipLedgerDetails, Collection::class) ? @$WipLedgerDetails->first()['ledger_id'] : null;
            $wipLedgerGroupId = is_a($WipLedgerDetails, Collection::class) ? @$WipLedgerDetails->first()['ledger_group'] : null;
            $wipLedger = Ledger::find($wipLedgerId);
            $wipLedgerGroup = Group::find($wipLedgerGroupId);
            //LEDGER NOT FOUND
            if (!isset($wipLedger) || !isset($wipLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'WIP Account not setup';
                break;
            }

            //Check for same ledger and group in WIP ACCOUNT
            $existingstockLedger = array_filter($postingArray[self::WIP_ACCOUNT], function ($posting) use ($wipLedgerId, $wipLedgerGroupId) {
                return $posting['ledger_id'] == $wipLedgerId && $posting['ledger_group_id'] == $wipLedgerGroupId;
            });

            //Ledger found
            if (count($existingstockLedger) > 0) {
                $postingArray[self::WIP_ACCOUNT][0]['debit_amount'] += $WipDebitAccount;
            } else { //Assign a new ledger
                array_push($postingArray[self::WIP_ACCOUNT], [
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

        foreach ($document->moItems as $docItemKey => $docItem) {
            $itemValue = round(($docItem->rate * $docItem->qty), 2);
            $stockCreditAccount = $itemValue;
            $stockLedgerDetails = AccountHelper::getStockLedgerGroupAndLedgerId($document->organization_id, $docItem->item_id, $document->book_id);

            $stockLedgerId = is_a($stockLedgerDetails, Collection::class) ? @$stockLedgerDetails->first()['ledger_id'] : null;
            $stockLedgerGroupId = is_a($stockLedgerDetails, Collection::class) ? @$stockLedgerDetails->first()['ledger_group'] : null;
            $stockLedger = Ledger::find($stockLedgerId);
            $stockLedgerGroup = Group::find($stockLedgerGroupId);
            //LEDGER NOT FOUND
            if (!isset($stockLedger) || !isset($stockLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Stock Account not setup';
                break;
            }
            // Check for existing Stock ACCOUNT
            $existingStockLedger = array_filter($postingArray[self::RM_ACCOUNT], function ($posting) use ($stockLedgerId, $stockLedgerGroupId) {
                return $posting['ledger_id'] == $stockLedgerId && $posting['ledger_group_id'] === $stockLedgerGroupId;
            });
            //Ledger found
            if (count($existingStockLedger) > 0) {
                $postingArray[self::RM_ACCOUNT][0]['credit_amount'] += $stockCreditAccount;
            } else { //Assign new ledger
                array_push($postingArray[self::RM_ACCOUNT], [
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
        //Balance does not match
        // if (round($totalDebitAmount,6) !== round($totalCreditAmount,6)) {
        //     return array(
        //         'status' => false,
        //         'message' => self::ERROR_PREFIX.'Credit Amount does not match Debit Amount',
        //         'data' => []
        //     );
        // }
        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
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
        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document);

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


    public static function pbVoucherDetails(int $documentId, string $type)
    {
        $document = PbHeader::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }
        $postingArray = array(
            self::GRIR_ACCOUNT => [],
            self::TAX_ACCOUNT => [],
            self::EXPENSE_ACCOUNT => [],
            self::DISCOUNT_ACCOUNT => [],
            self::PV_ACCOUNT => [],
            self::SUPPLIER_ACCOUNT => [],
        );
        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;
        $totalsupplierCredit = 0;

        // Vendor Detail
        $vendor = Vendor::find($document->vendor_id);
        $vendorLedgerId = $vendor->ledger_id;
        $vendorLedgerGroupId = $vendor->ledger_group_id;
        $vendorLedger = Ledger::find($vendorLedgerId);
        $vendorLedgerGroup = Group::find($vendorLedgerGroupId);

        //Vendor Ledger account not found
        if (!isset($vendorLedger) || !isset($vendorLedgerGroup)) {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Vendor Account not setup',
                'data' => []
            );
        }

        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;

        $discountSeperatePosting = false;

        foreach ($document->items as $docItemKey => $docItem) {
            //Assign Item values
            $itemValue = ($docItem->rate * $docItem->accepted_qty);
            $itemTotalDiscount = $docItem->header_discount_amount + $docItem->discount_amount;
            $itemValueAfterDiscount = $itemValue - $itemTotalDiscount;
            $rejectedValue = ($docItem->po_rate * $docItem->rejected_qty);
            $varianceAmount = $docItem->item_variance ?? 0.00;

            //COGS SETUP
            // $grirCreditAmount = $discountSeperatePosting ? $itemValue : $itemValueAfterDiscount;
            $poValueAfterDiscount = ($docItem->po_rate * $docItem->accepted_qty);
            $grirCreditAmount = $discountSeperatePosting ? $poValueAfterDiscount : $poValueAfterDiscount;
            $grirCreditAmount += $rejectedValue;
            $grirLedgerDetails = AccountHelper::getGrLedgerGroupAndLedgerId($document->organization_id, $docItem->item_id, $document->book_id);
            // $grirLedgerId = $grirLedgerDetails -> first()['ledger_id'] ?? null;
            // $grirLedgerGroupId = $grirLedgerDetails-> first()['ledger_group'] ?? null;
            $grirLedgerId = is_a($grirLedgerDetails, Collection::class) ? $grirLedgerDetails->first()['ledger_id'] : null;
            $grirLedgerGroupId = is_a($grirLedgerDetails, Collection::class) ? $grirLedgerDetails->first()['ledger_group'] : null;
            $grirLedger = Ledger::find($grirLedgerId);
            $grirLedgerGroup = Group::find($grirLedgerGroupId);
            //LEDGER NOT FOUND
            if (!isset($grirLedger) || !isset($grirLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'GR/IR Account not setup';
                break;
            }

            //Check for same ledger and group in SALES ACCOUNT
            $existingGrirLedger = array_filter($postingArray[self::GRIR_ACCOUNT], function ($posting) use ($grirLedgerId, $grirLedgerGroupId) {
                return $posting['ledger_id'] == $grirLedgerId && $posting['ledger_group_id'] == $grirLedgerGroupId;
            });
            //Ledger found
            if (count($existingGrirLedger) > 0) {
                $postingArray[self::GRIR_ACCOUNT][0]['debit_amount'] += $grirCreditAmount;
            } else { //Assign a new ledger
                array_push($postingArray[self::GRIR_ACCOUNT], [
                    'ledger_id' => $grirLedgerId,
                    'ledger_group_id' => $grirLedgerGroupId,
                    'ledger_code' => $grirLedger?->code,
                    'ledger_name' => $grirLedger?->name,
                    'ledger_group_code' => $grirLedgerGroup?->name,
                    'credit_amount' => 0,
                    'debit_amount' => $grirCreditAmount
                ]);
            }

            if ($varianceAmount != 0) {
                // Price Variance Account
                $stockLedgerDetails = AccountHelper::getPriceVarianceLedgerGroupAndLedgerId($document->organization_id, $docItem->item_id, $document->book_id);

                $stockLedgerId = is_a($stockLedgerDetails, Collection::class) ? @$stockLedgerDetails->first()['ledger_id'] : null;
                $stockLedgerGroupId = is_a($stockLedgerDetails, Collection::class) ? @$stockLedgerDetails->first()['ledger_group'] : null;
                $stockLedger = Ledger::find($stockLedgerId);
                $stockLedgerGroup = Group::find($stockLedgerGroupId);
                //LEDGER NOT FOUND
                if (!isset($stockLedger) || !isset($stockLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'Price Variance Account not setup';
                    break;
                }

                //Check for same ledger and group in SALES ACCOUNT
                $existingstockLedger = array_filter($postingArray[self::PV_ACCOUNT], function ($posting) use ($stockLedgerId, $stockLedgerGroupId) {
                    return $posting['ledger_id'] == $stockLedgerId && $posting['ledger_group_id'] == $stockLedgerGroupId;
                });
                //Ledger found
                if (count($existingstockLedger) > 0) {
                    if ($varianceAmount > 0) {
                        $postingArray[self::PV_ACCOUNT][0]['debit_amount'] += $varianceAmount;
                    }
                    if ($varianceAmount < 0) {
                        $postingArray[self::PV_ACCOUNT][0]['credit_amount'] += abs($varianceAmount);
                    }
                } else { //Assign a new ledger
                    if ($varianceAmount > 0) {
                        array_push($postingArray[self::PV_ACCOUNT], [
                            'ledger_id' => $stockLedgerId,
                            'ledger_group_id' => $stockLedgerGroupId,
                            'ledger_code' => $stockLedger?->code,
                            'ledger_name' => $stockLedger?->name,
                            'ledger_group_code' => $stockLedgerGroup?->name,
                            'credit_amount' => 0,
                            'debit_amount' => $varianceAmount
                        ]);
                    }
                    if ($varianceAmount < 0) {
                        array_push($postingArray[self::PV_ACCOUNT], [
                            'ledger_id' => $stockLedgerId,
                            'ledger_group_id' => $stockLedgerGroupId,
                            'ledger_code' => $stockLedger?->code,
                            'ledger_name' => $stockLedger?->name,
                            'ledger_group_code' => $stockLedgerGroup?->name,
                            'credit_amount' => abs($varianceAmount),
                            'debit_amount' => 0
                        ]);
                    }
                }
            }

            //SUPPLIER ACCOUNT
            $supplierCrAmount = ($itemValueAfterDiscount + $rejectedValue);
            $totalsupplierCredit += $supplierCrAmount;
        }

        //TAXES ACCOUNT
        $taxes = PbTed::where('header_id', $document->id)->where('ted_type', "Tax")->get();
        foreach ($taxes as $tax) {
            $taxDetail = TaxDetail::find($tax->ted_id);
            $taxLedgerId = $taxDetail->ledger_id ?? null; //MAKE IT DYNAMIC
            $taxLedgerGroupId = $taxDetail->ledger_group_id ?? null; //MAKE IT DYNAMIC
            $taxLedger = Ledger::find($taxLedgerId);
            $taxLedgerGroup = Group::find($taxLedgerGroupId);
            if (!isset($taxLedger) || !isset($taxLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Tax Account not setup';
                break;
            }
            $existingTaxLedger = array_filter($postingArray[self::TAX_ACCOUNT], function ($posting) use ($taxLedgerId, $taxLedgerGroupId) {
                return $posting['ledger_id'] == $taxLedgerId && $posting['ledger_group_id'] === $taxLedgerGroupId;
            });
            //Ledger found
            if (count($existingTaxLedger) > 0) {
                $postingArray[self::TAX_ACCOUNT][0]['debit_amount'] += $tax->ted_amount;
            } else { //Assign a new ledger
                array_push($postingArray[self::TAX_ACCOUNT], [
                    'ledger_id' => $taxLedgerId,
                    'ledger_group_id' => $taxLedgerGroupId,
                    'ledger_code' => $taxLedger?->code,
                    'ledger_name' => $taxLedger?->name,
                    'ledger_group_code' => $taxLedgerGroup?->name,
                    'credit_amount' => 0,
                    'debit_amount' => $tax->ted_amount,
                ]);
            }
            //Tax for SUPPLIER ACCOUNT
            $totalsupplierCredit += $tax->ted_amount;
        }
        //EXPENSES
        $expenses = PbTed::where('header_id', $document->id)->where('ted_type', "Expense")->get();
        foreach ($expenses as $expense) {
            $totalExpAmount = ($expense->ted_amount + $expense->tax_amount);
            $expenseDetail = ExpenseMaster::find($expense->ted_id);
            $expenseLedgerId = $expenseDetail?->expense_ledger_id; //MAKE IT DYNAMIC - 5
            $expenseLedgerGroupId = $expenseDetail?->expense_ledger_group_id; //MAKE IT DYNAMIC - 9
            $expenseLedger = Ledger::find($expenseLedgerId);
            $expenseLedgerGroup = Group::find($expenseLedgerGroupId);
            if (!isset($expenseLedger) || !isset($expenseLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Expense Account not setup';
                break;
            }
            $existingExpenseLedger = array_filter($postingArray[self::EXPENSE_ACCOUNT], function ($posting) use ($expenseLedgerId, $expenseLedgerGroupId) {
                return $posting['ledger_id'] == $expenseLedgerId && $posting['ledger_group_id'] === $expenseLedgerGroupId;
            });
            //Ledger found
            if (count($existingExpenseLedger) > 0) {
                $postingArray[self::EXPENSE_ACCOUNT][0]['debit_amount'] += $totalExpAmount;
            } else { //Assign a new ledger
                array_push($postingArray[self::EXPENSE_ACCOUNT], [
                    'ledger_id' => $expenseLedgerId,
                    'ledger_group_id' => $expenseLedgerGroupId,
                    'ledger_code' => $expenseLedger?->code,
                    'ledger_name' => $expenseLedger?->name,
                    'ledger_group_code' => $expenseLedgerGroup?->name,
                    'credit_amount' => 0,
                    'debit_amount' => $totalExpAmount,
                ]);
            }
            //Expense for SUPPLIER ACCOUNT
            $totalsupplierCredit += $totalExpAmount;
        }
        //Seperate posting of Discount
        if ($discountSeperatePosting) {
            $discounts = PbTed::where('header_id', $document->id)->where('ted_type', "Discount")->get();
            foreach ($discounts as $discount) {
                $discountDetail = DiscountMaster::find($discount->ted_id);
                $discountLedgerId = $discountDetail?->discount_ledger_id; //MAKE IT DYNAMIC
                $discountLedgerGroupId = $discountDetail?->discount_ledger_group_id; //MAKE IT DYNAMIC
                $discountLedger = Ledger::find($discountLedgerId);
                $discountLedgerGroup = Group::find($discountLedgerGroupId);
                if (!isset($discountLedger) || !isset($discountLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'Discount Account not setup';
                    break;
                }
                $existingDiscountLedger = array_filter($postingArray[self::DISCOUNT_ACCOUNT], function ($posting) use ($discountLedgerId, $discountLedgerGroupId) {
                    return $posting['ledger_id'] == $discountLedgerId && $posting['ledger_group_id'] === $discountLedgerGroupId;
                });
                //Ledger found
                if (count($existingDiscountLedger) > 0) {
                    $postingArray[self::DISCOUNT_ACCOUNT][0]['credit_amount'] += $discount->ted_amount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::DISCOUNT_ACCOUNT], [
                        'ledger_id' => $discountLedgerId,
                        'ledger_group_id' => $discountLedgerGroupId,
                        'ledger_code' => $discountLedger?->code,
                        'ledger_name' => $discountLedger?->name,
                        'ledger_group_code' => $discountLedgerGroup?->name,
                        'debit_amount' => 0,
                        'credit_amount' => $discount->ted_amount,
                    ]);
                }
            }
        }

        //Break Supplier Account according to payment terms schedule - due date wise
        $invoicePaymentTerms = $document->payment_term_schedules()
            ->select('due_date', DB::raw('SUM(percent) as total_percentage'))->groupBy('due_date')->get();
        if ($invoicePaymentTerms && count($invoicePaymentTerms)) {
            foreach ($invoicePaymentTerms as $invoicePaymentTerm) {
                $currentAmount = $totalsupplierCredit * ($invoicePaymentTerm->total_percentage / 100);
                //Check for same ledger and group in SUPPLIER ACCOUNT
                $existingVendorLedger = array_filter($postingArray[self::SUPPLIER_ACCOUNT], function ($posting) use ($vendorLedgerId, $vendorLedgerGroupId, $invoicePaymentTerm) {
                    return $posting['ledger_id'] == $vendorLedgerId && $posting['ledger_group_id'] === $vendorLedgerGroupId && $posting['due_date'] === $invoicePaymentTerm->due_date;
                });
                //Ledger found
                if (count($existingVendorLedger) > 0) {
                    $postingArray[self::SUPPLIER_ACCOUNT][0]['credit_amount'] += $currentAmount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::SUPPLIER_ACCOUNT], [
                        'ledger_id' => $vendorLedgerId,
                        'ledger_group_id' => $vendorLedgerGroupId,
                        'ledger_code' => $vendorLedger?->code,
                        'ledger_name' => $vendorLedger?->name,
                        'ledger_group_code' => $vendorLedgerGroup?->name,
                        'debit_amount' => 0,
                        'credit_amount' => $currentAmount,
                        'due_date' => $invoicePaymentTerm->due_date,
                    ]);
                }
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
        //Balance does not match
        // if (round($totalDebitAmount,6) !== round($totalCreditAmount,6)) {
        //     return array(
        //         'status' => false,
        //         'message' => self::ERROR_PREFIX.'Credit Amount does not match Debit Amount',
        //         'data' => []
        //     );
        // }
        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
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
        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document);

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

    public static function expenseAdviseVoucherDetails(int $documentId, string $type)
    {
        $document = ExpenseHeader::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }
        $postingArray = array(
            self::SERVICE_ACCOUNT => [],
            self::TAX_ACCOUNT => [],
            self::EXPENSE_ACCOUNT => [],
            self::DISCOUNT_ACCOUNT => [],
            self::GRIR_ACCOUNT => [],
            self::SUPPLIER_ACCOUNT => [],
        );
        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;

        // Vendor Detail
        $vendor = Vendor::find($document->vendor_id);
        $vendorLedgerId = $vendor->ledger_id;
        $vendorLedgerGroupId = $vendor->ledger_group_id;
        $vendorLedger = Ledger::find($vendorLedgerId);
        $vendorLedgerGroup = Group::find($vendorLedgerGroupId);

        //Vendor Ledger account not found
        if (!isset($vendorLedger) || !isset($vendorLedgerGroup)) {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Vendor Account not setup',
                'data' => []
            );
        }

        // $discountPostingParam = OrganizationBookParameter::where('book_id', $document -> book_id)
        // -> where('parameter_name', ServiceParametersHelper::GL_SEPERATE_DISCOUNT_PARAM) -> first();
        // if (isset($discountPostingParam)) {
        //     $discountSeperatePosting = $discountPostingParam -> parameter_value[0] === "yes" ? true : false;
        // } else {
        $discountSeperatePosting = false;
        // }

        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;
        //COGS SETUP
        foreach ($document->items as $docItemKey => $docItem) {
            $itemValue = ($docItem->rate * $docItem->accepted_qty);
            $itemTotalDiscount = $docItem->header_discount_amount + $docItem->discount_amount;
            $itemValueAfterDiscount = $itemValue - $itemTotalDiscount;
            $stockDebitAmount = $discountSeperatePosting ? $itemValue : $itemValueAfterDiscount;

            $stockLedgerDetails = AccountHelper::getServiceLedgerGroupAndLedgerId($document->organization_id, $docItem->item_id, $document->book_id);
            // $stockLedgerId = $stockLedgerDetails -> first()['ledger_id'] ?? null;
            // $stockLedgerGroupId = $stockLedgerDetails-> first()['ledger_group'] ?? null;
            $stockLedgerId = is_a($stockLedgerDetails, Collection::class) ? $stockLedgerDetails->first()['ledger_id'] : null;
            $stockLedgerGroupId = is_a($stockLedgerDetails, Collection::class) ? $stockLedgerDetails->first()['ledger_group'] : null;
            $stockLedger = Ledger::find($stockLedgerId);
            $stockLedgerGroup = Group::find($stockLedgerGroupId);
            //LEDGER NOT FOUND
            if (!isset($stockLedger) || !isset($stockLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Service Account not setup';
                break;
            }

            //Check for same ledger and group in SALES ACCOUNT
            $existingstockLedger = array_filter($postingArray[self::SERVICE_ACCOUNT], function ($posting) use ($stockLedgerId, $stockLedgerGroupId) {
                return $posting['ledger_id'] == $stockLedgerId && $posting['ledger_group_id'] == $stockLedgerGroupId;
            });
            //Ledger found
            if (count($existingstockLedger) > 0) {
                $postingArray[self::SERVICE_ACCOUNT][0]['debit_amount'] += $stockDebitAmount;
            } else { //Assign a new ledger
                array_push($postingArray[self::SERVICE_ACCOUNT], [
                    'ledger_id' => $stockLedgerId,
                    'ledger_group_id' => $stockLedgerGroupId,
                    'ledger_code' => $stockLedger?->code,
                    'ledger_name' => $stockLedger?->name,
                    'ledger_group_code' => $stockLedgerGroup?->name,
                    'credit_amount' => 0,
                    'debit_amount' => $stockDebitAmount
                ]);
            }

            //Stock for SUPPLIER ACCOUNT
            $existingVendorLedger = array_filter($postingArray[self::SUPPLIER_ACCOUNT], function ($posting) use ($vendorLedgerId, $vendorLedgerGroupId) {
                return $posting['ledger_id'] == $vendorLedgerId && $posting['ledger_group_id'] === $vendorLedgerGroupId;
            });
            //Ledger found
            if (count($existingVendorLedger) > 0) {
                $postingArray[self::SUPPLIER_ACCOUNT][0]['credit_amount'] += $itemValueAfterDiscount;
            } else { //Assign new ledger
                array_push($postingArray[self::SUPPLIER_ACCOUNT], [
                    'ledger_id' => $vendorLedgerId,
                    'ledger_group_id' => $vendorLedgerGroupId,
                    'ledger_code' => $vendorLedger?->code,
                    'ledger_name' => $vendorLedger?->name,
                    'ledger_group_code' => $vendorLedgerGroup?->name,
                    'credit_amount' => $itemValueAfterDiscount,
                    'debit_amount' => 0
                ]);
            }
        }

        //TAXES ACCOUNT
        $taxes = ExpenseTed::where('expense_header_id', $document->id)->where('ted_type', "Tax")->get();
        foreach ($taxes as $tax) {
            $taxDetail = TaxDetail::find($tax->ted_id);
            $taxLedgerId = $taxDetail->ledger_id ?? null; //MAKE IT DYNAMIC
            $taxLedgerGroupId = $taxDetail->ledger_group_id ?? null; //MAKE IT DYNAMIC
            $taxLedger = Ledger::find($taxLedgerId);
            $taxLedgerGroup = Group::find($taxLedgerGroupId);
            if (!isset($taxLedger) || !isset($taxLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Tax Account not setup';
                break;
            }
            $existingTaxLedger = array_filter($postingArray[self::TAX_ACCOUNT], function ($posting) use ($taxLedgerId, $taxLedgerGroupId) {
                return $posting['ledger_id'] == $taxLedgerId && $posting['ledger_group_id'] === $taxLedgerGroupId;
            });
            //Ledger found
            if (count($existingTaxLedger) > 0) {
                if (trim(strtolower($taxDetail->applicability_type)) === ConstantHelper::DEDUCTION) {
                    $postingArray[self::TAX_ACCOUNT][0]['credit_amount'] += $tax->ted_amount;
                } else {
                    $postingArray[self::TAX_ACCOUNT][0]['debit_amount'] += $tax->ted_amount;
                }
            } else { //Assign a new ledger
                if (trim(strtolower($taxDetail->applicability_type)) === ConstantHelper::DEDUCTION) {
                    $creditAmount = $tax->ted_amount;
                    $debitAmount = 0;
                } else {
                    $creditAmount = 0;
                    $debitAmount = $tax->ted_amount;
                }
                array_push($postingArray[self::TAX_ACCOUNT], [
                    'ledger_id' => $taxLedgerId,
                    'ledger_group_id' => $taxLedgerGroupId,
                    'ledger_code' => $taxLedger?->code,
                    'ledger_name' => $taxLedger?->name,
                    'ledger_group_code' => $taxLedgerGroup?->name,
                    'credit_amount' => $creditAmount,
                    'debit_amount' => $debitAmount,
                ]);
            }
            //Tax for SUPPLIER ACCOUNT
            $existingVendorLedger = array_filter($postingArray[self::SUPPLIER_ACCOUNT], function ($posting) use ($vendorLedgerId, $vendorLedgerGroupId) {
                return $posting['ledger_id'] == $vendorLedgerId && $posting['ledger_group_id'] === $vendorLedgerGroupId;
            });
            //Ledger found
            if (count($existingVendorLedger) > 0) {
                if (trim(strtolower($taxDetail->applicability_type)) === ConstantHelper::DEDUCTION) {
                    $postingArray[self::SUPPLIER_ACCOUNT][0]['credit_amount'] -= $tax->ted_amount;
                } else {
                    $postingArray[self::SUPPLIER_ACCOUNT][0]['credit_amount'] += $tax->ted_amount;
                }
            } else { //Assign new ledger
                array_push($postingArray[self::SUPPLIER_ACCOUNT], [
                    'ledger_id' => $vendorLedgerId,
                    'ledger_group_id' => $vendorLedgerGroupId,
                    'ledger_code' => $vendorLedger?->code,
                    'ledger_name' => $vendorLedger?->name,
                    'ledger_group_code' => $vendorLedgerGroup?->name,
                    'credit_amount' => $tax->ted_amount,
                    'debit_amount' => 0,
                ]);
            }
        }

        //EXPENSES
        $expenses = ExpenseTed::where('expense_header_id', $document->id)->where('ted_type', "Expense")->get();
        foreach ($expenses as $expense) {
            $expenseDetail = ExpenseMaster::find($expense->ted_id);
            $expenseLedgerId = $expenseDetail?->expense_ledger_id; //MAKE IT DYNAMIC - 5
            $expenseLedgerGroupId = $expenseDetail?->expense_ledger_group_id; //MAKE IT DYNAMIC - 9
            $expenseLedger = Ledger::find($expenseLedgerId);
            $expenseLedgerGroup = Group::find($expenseLedgerGroupId);
            if (!isset($expenseLedger) || !isset($expenseLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Expense Account not setup';
                break;
            }
            $existingExpenseLedger = array_filter($postingArray[self::EXPENSE_ACCOUNT], function ($posting) use ($expenseLedgerId, $expenseLedgerGroupId) {
                return $posting['ledger_id'] == $expenseLedgerId && $posting['ledger_group_id'] === $expenseLedgerGroupId;
            });
            //Ledger found
            if (count($existingExpenseLedger) > 0) {
                $postingArray[self::EXPENSE_ACCOUNT][0]['debit_amount'] += $expense->ted_amount;
            } else { //Assign a new ledger
                array_push($postingArray[self::EXPENSE_ACCOUNT], [
                    'ledger_id' => $expenseLedgerId,
                    'ledger_group_id' => $expenseLedgerGroupId,
                    'ledger_code' => $expenseLedger?->code,
                    'ledger_name' => $expenseLedger?->name,
                    'ledger_group_code' => $expenseLedgerGroup?->name,
                    'credit_amount' => 0,
                    'debit_amount' => $expense->ted_amount,
                ]);
            }
            //Expense for SUPPLIER ACCOUNT
            $existingVendorLedger = array_filter($postingArray[self::SUPPLIER_ACCOUNT], function ($posting) use ($vendorLedgerId, $vendorLedgerGroupId) {
                return $posting['ledger_id'] == $vendorLedgerId && $posting['ledger_group_id'] === $vendorLedgerGroupId;
            });
            //Ledger found
            if (count($existingVendorLedger) > 0) {
                $postingArray[self::SUPPLIER_ACCOUNT][0]['credit_amount'] += $expense->ted_amount;
            } else { //Assign new ledger
                array_push($postingArray[self::EXPENSE_ACCOUNT], [
                    'ledger_id' => $vendorLedgerId,
                    'ledger_group_id' => $vendorLedgerGroupId,
                    'ledger_code' => $vendorLedger?->code,
                    'ledger_name' => $vendorLedger?->name,
                    'ledger_group_code' => $vendorLedgerGroup?->name,
                    'credit_amount' => $expense->ted_amount,
                    'debit_amount' => 0,
                ]);
            }
        }
        //Seperate posting of Discount
        if ($discountSeperatePosting) {
            $discounts = ExpenseTed::where('expense_header_id', $document->id)->where('ted_type', "Discount")->get();
            foreach ($discounts as $discount) {
                $discountDetail = DiscountMaster::find($discount->ted_id);
                $discountLedgerId = $discountDetail?->discount_ledger_id; //MAKE IT DYNAMIC
                $discountLedgerGroupId = $discountDetail?->discount_ledger_group_id; //MAKE IT DYNAMIC
                $discountLedger = Ledger::find($discountLedgerId);
                $discountLedgerGroup = Group::find($discountLedgerGroupId);
                if (!isset($discountLedger) || !isset($discountLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'Discount Account not setup';
                    break;
                }
                $existingDiscountLedger = array_filter($postingArray[self::DISCOUNT_ACCOUNT], function ($posting) use ($discountLedgerId, $discountLedgerGroupId) {
                    return $posting['ledger_id'] == $discountLedgerId && $posting['ledger_group_id'] === $discountLedgerGroupId;
                });
                //Ledger found
                if (count($existingDiscountLedger) > 0) {
                    $postingArray[self::DISCOUNT_ACCOUNT][0]['credit_amount'] += $discount->ted_amount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::DISCOUNT_ACCOUNT], [
                        'ledger_id' => $discountLedgerId,
                        'ledger_group_id' => $discountLedgerGroupId,
                        'ledger_code' => $discountLedger?->code,
                        'ledger_name' => $discountLedger?->name,
                        'ledger_group_code' => $discountLedgerGroup?->name,
                        'debit_amount' => 0,
                        'credit_amount' => $discount->ted_amount,
                    ]);
                }
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
        //Balance does not match
        // if (round($totalDebitAmount,6) !== round($totalCreditAmount,6)) {
        //     return array(
        //         'status' => false,
        //         'message' => self::ERROR_PREFIX.'Credit Amount does not match Debit Amount',
        //         'data' => []
        //     );
        // }
        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();

        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
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
        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document);

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

    public static function receiptInvoiceVoucherDetails(int $documentId, string $remarks)
    {
        $accountSetup = isset(self::SERVICE_POSTING_MAPPING[ConstantHelper::RECEIPTS_SERVICE_ALIAS]) ? self::SERVICE_POSTING_MAPPING[ConstantHelper::RECEIPTS_SERVICE_ALIAS] : [];
        if (!isset($accountSetup) || count($accountSetup) == 0) {
            return array(
                'status' => false,
                'message' => 'Account Setup not found',
                'data' => []
            );
        }
        $document = PaymentVoucher::find($documentId);
        $vendors = $document->details;
        $vocuherdata = $document;
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }
        //Make array according to setup
        $postingArray = array(
            self::PAYMENT_ACCOUNT => [],
            self::VENDOR_ACCOUNT => [],
        );
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;

        $ledgerErrorStatus = null;

        if (!empty($vocuherdata)) {
            $BankLedgerId = $vocuherdata->ledger_id;
            $BankLedgerGroupId = $vocuherdata->ledger_group_id;
            $BankLedger = Ledger::find($BankLedgerId);
            $BankLedgerGroup = Group::find($BankLedgerGroupId);
        }

        if(!isset($BankLedger)){
            return array(
                'status' => false,
                'message' => 'Bank Ledger not setup',
                'data' => []
            );
        }

        if(!isset($BankLedgerGroup)){
            return array(
                'status' => false,
                'message' => 'Bank Ledger Group not found',
                'data' => []
            );
        }

        $cost = CostCenter::find($document?->cost_center_id);
        $ids = array_column(Helper::getActiveCostCenters($document->location), 'id');
        $exists = in_array($cost?->id, $ids);

        if (!$exists && $cost != null) {
            return array(
                'status' => false,
                'message' => $cost->name . ' not Mapped with header location',
                'data' => []
            );
        }


        array_push($postingArray[self::PAYMENT_ACCOUNT], [
            'ledger_id' => $vocuherdata->ledger_id,
            'ledger_group_id' => $vocuherdata->ledger_group_id,
            'ledger_code' => $BankLedger?->code,
            'cost_center_id' => $document?->cost_center_id,
            'cost_name' => $cost->name ?? "-",
            'ledger_name' => $BankLedger?->name,
            'ledger_group_code' => $BankLedgerGroup?->name,
            'debit_amount' => $vocuherdata->amount,
            'credit_amount' => 0
        ]);
        foreach ($vendors as $vendor) {
            if (!empty($vendor)) {
                if ($vendor->reference == "Invoice") {
                    $invoices = VoucherReference::where('voucher_details_id', $vendor->id)->where('party_id', $vendor->ledger_id)->get();
                    $VendorLedgerId = $vendor->ledger_id;
                    $VendorLedgerGroupId = $vendor->ledger_group_id;
                    $VendorLedger = Ledger::find($VendorLedgerId);
                    $VendorLedgerGroup = Group::find($VendorLedgerGroupId);


                    if (!isset($VendorLedger) || !isset($VendorLedgerGroup)) {
                        return array(
                            'status' => false,
                            'message' => 'Vendor Ledger not setup',
                            'data' => []
                        );
                    }

                    foreach ($invoices as $invoice) {
                        $item = ItemDetail::where('voucher_id', $invoice->voucher_id)
                            ->where('ledger_id', $vendor->ledger_id)
                            ->where('ledger_parent_id', $vendor->ledger_group_id)
                            ->where('debit_amt_org', '>', 0)
                            ->first();
                        $cost = CostCenter::find($item?->cost_center_id);
                        $ids = array_column(Helper::getActiveCostCenters($document->location), 'id');
                        $exists = in_array($cost?->id, $ids);

                        if (!$exists && $cost != null) {
                            return array(
                                'status' => false,
                                'message' => $cost->name . ' not Mapped with header location',
                                'data' => []
                            );
                        }


                        $newEntry = [
                            'ledger_id' => $VendorLedgerId,
                            'ledger_group_id' => $VendorLedgerGroupId,
                            'ledger_code' => $VendorLedger?->code,
                            'ledger_name' => $VendorLedger?->name,
                            'cost_center_id' => $item?->cost_center_id,
                            'cost_name' => $cost->name ?? "-",
                            'ledger_group_code' => $VendorLedgerGroup?->name,
                            'credit_amount' => $invoice->amount,
                            'debit_amount' => 0,
                        ];

                        $found = false;

                        // Check if a similar entry already exists
                        foreach ($postingArray[self::VENDOR_ACCOUNT] as &$existingEntry) {
                            if (
                                $existingEntry['ledger_id'] === $newEntry['ledger_id'] &&
                                $existingEntry['ledger_group_id'] === $newEntry['ledger_group_id'] &&
                                $existingEntry['cost_center_id'] === $newEntry['cost_center_id']
                            ) {
                                $existingEntry['credit_amount'] += $newEntry['credit_amount'];
                                $found = true;
                                break;
                            }
                        }

                        // If not found, push as a new entry
                        if (!$found) {
                            $postingArray[self::VENDOR_ACCOUNT][] = $newEntry;
                        }
                    }
                } else {
                    $VendorLedgerId = $vendor->ledger_id;
                    $VendorLedgerGroupId = $vendor->ledger_group_id;
                    $VendorLedger = Ledger::find($VendorLedgerId);
                    $VendorLedgerGroup = Group::find($VendorLedgerGroupId);


                    if (!isset($VendorLedger) || !isset($VendorLedgerGroup)) {
                        return array(
                            'status' => false,
                            'message' => 'Vendor Ledger not setup',
                            'data' => []
                        );
                    }
                    $cost = CostCenter::find($document?->cost_center_id);
                    $ids = array_column(Helper::getActiveCostCenters($document->location), 'id');
                    $exists = in_array($cost?->id, $ids);

                    if (!$exists && $cost != null) {
                        return array(
                            'status' => false,
                            'message' => $cost->name . ' not Mapped with header location',
                            'data' => []
                        );
                    }



                    array_push($postingArray[self::VENDOR_ACCOUNT], [
                        'ledger_id' => $VendorLedgerId,
                        'ledger_group_id' => $VendorLedgerGroupId,
                        'ledger_code' => $VendorLedger?->code,
                        'ledger_name' => $VendorLedger?->name,
                        'cost_center_id' => $document?->cost_center_id,
                        'cost_name' => $cost->name ?? "-",
                        'ledger_group_code' => $VendorLedgerGroup?->name,
                        'credit_amount' => $vendor->currentAmount,
                        'debit_amount' => 0,
                    ]);
                }
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

                $totalDebitAmount += $postingValue['debit_amount'];
                $totalCreditAmount += $postingValue['credit_amount'];
            }
        }

        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);


        $userData = Helper::userCheck();
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }

        $voucherHeader = [
            'voucher_no' => $document->voucher_no,
            'document_date' => $document->document_date,
            'book_id' => $glPostingBookId,
            'date' => $document->document_date,
            'amount' => $totalCreditAmount,
            'location' => $document->location ?? null,
            'currency_id' => $document->currency_id,
            'currency_code' => $document->currencyCode,
            'org_currency_id' => $document->org_currency_id,
            'org_currency_code' => $document->org_currency_code,
            'org_currency_exg_rate' => $document->org_currency_exg_rate,
            'comp_currency_id' => $document->comp_currency_id, // Missing comma added here
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
            'approvalStatus' => ConstantHelper::APPROVED,
            'document_status' => ConstantHelper::APPROVED,
            'approvalLevel' => $document->approval_level,
            'remarks' => $remarks,
        ];

        $voucherDetails = self::generateInvoiceDetailsArray($postingArray, $voucherHeader, $document);

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
                'document_number' => $document?->voucher_no,
                'currency_code' => $currency?->short_name
            ]
        );
    }
    public static function PsvVoucherDetails(int $documentId, string $remarks)
    {
        $accountSetup = isset(self::SERVICE_POSTING_MAPPING[ConstantHelper::PSV_SERVICE_ALIAS]) ? self::SERVICE_POSTING_MAPPING[ConstantHelper::PSV_SERVICE_ALIAS] : [];
        if (!isset($accountSetup) || count($accountSetup) == 0) {
            return array(
                'status' => false,
                'message' => 'Account Setup not found',
                'data' => []
            );
        }
        $document = ErpPsvHeader::find($documentId);
        $items = $document->items;
        $vocuherdata = $document;
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }
        //Make array according to setup
        $postingArray = array(
            self::STOCK_ACCOUNT => [],
            self::PHYSICAL_STOCK_VARIANCE_ACCOUNT => [],
        );
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;

        $ledgerErrorStatus = null;

        foreach ($items as $item) {
            if (!empty($vocuherdata)) {
                $psvAccountLedgerDetails = AccountHelper::getStockLedgerGroupAndLedgerId($document->organization_id, $item->item_id, $document->book_id);
                $psvAccountLedgerId = is_a($psvAccountLedgerDetails, Collection::class) ? $psvAccountLedgerDetails->first()['ledger_id'] : null;
                $psvAccountLedgerGroupId = is_a($psvAccountLedgerDetails, Collection::class) ? $psvAccountLedgerDetails->first()['ledger_group'] : null;
                $BankLedger = Ledger::find($psvAccountLedgerId);
                $BankLedgerGroup = Group::find($psvAccountLedgerGroupId);
                $BankLedgerId = $BankLedger->id;
                $BankLedgerGroupId = $BankLedgerGroup->id;
            }

            if (!isset($BankLedger) || !isset($BankLedgerGroup)) {
                if(!isset($BankLedger)){
                    return array(
                        'status' => false,
                        'message' => 'Bank Ledger not setup',
                        'data' => []
                    );
                }
                if(!isset($BankLedgerGroup)){
                    return array(
                        'status' => false,
                        'message' => 'Bank Ledger Group not found',
                        'data' => []
                    );
                }
            }
            $debAmount = 0;
            $credAmount = 0;
            if ($item->adjusted_qty > 0) {
                $debAmount = $item->rate * abs($item->adjusted_qty);
            } else {
                $credAmount = $item->rate * abs($item->adjusted_qty);
            }
            array_push($postingArray[self::STOCK_ACCOUNT], [
                'ledger_id' => 1,
                'ledger_group_id' => 19,
                'ledger_code' => $BankLedger?->code,
                'ledger_name' => $BankLedger?->name,
                'ledger_group_code' => $BankLedgerGroup?->name,
                'debit_amount' => $debAmount,
                'credit_amount' => $credAmount
            ]);

            if (!empty($item)) {
                $psvAccountLedgerDetails = AccountHelper::getPhysicalStockLedgerGroupAndLedgerId($document->organization_id, $item->item_id);
                $psvAccountLedgerId = is_a($psvAccountLedgerDetails, Collection::class) ? $psvAccountLedgerDetails->first()['ledger_id'] : null;
                $psvAccountLedgerGroupId = is_a($psvAccountLedgerDetails, Collection::class) ? $psvAccountLedgerDetails->first()['ledger_group'] : null;
                $VendorLedger = Ledger::find($psvAccountLedgerId);
                $VendorLedgerGroup = Group::find($psvAccountLedgerGroupId);
                $VendorLedgerId = $VendorLedger->id;
                $VendorLedgerGroupId = $VendorLedgerGroup->id;
            }

            if (!isset($VendorLedger) || !isset($VendorLedgerGroup)) {
                return array(
                    'status' => false,
                    'message' => 'Vendor Ledger not setup',
                    'data' => []
                );
            }
            array_push($postingArray[self::PHYSICAL_STOCK_VARIANCE_ACCOUNT], [
                'ledger_id' => $VendorLedgerId,
                'ledger_group_id' => $VendorLedgerGroupId,
                'ledger_code' => $VendorLedger?->code,
                'ledger_name' => $VendorLedger?->name,
                'ledger_group_code' => $VendorLedgerGroup?->name,
                'credit_amount' => $debAmount,
                'debit_amount' => $credAmount,
            ]);
        }
        // $postingArrays=[];
        // foreach ($postingArray as $accountType => $entries) {
        //     $grouped = [];

        //     foreach ($entries as $entry) {
        //         $key = $entry['ledger_id'] . '_' . $entry['ledger_group_id'];

        //         if (!isset($grouped[$key])) {
        //             $grouped[$key] = $entry;
        //         } else {
        //             $grouped[$key]['debit_amount'] += $entry['debit_amount'];
        //             $grouped[$key]['credit_amount'] += $entry['credit_amount'];
        //         }
        //     }

        //     // Rebuild a new array safely to avoid reference conflicts
        //     $finalGrouped = [];

        //     foreach ($grouped as $entry) {
        //         $debit = $entry['debit_amount'] ?? 0;
        //         $credit = $entry['credit_amount'] ?? 0;

        //         $difference = $credit - $debit;

        //         if ($difference > 0) {
        //             // Net credit
        //             $entry['credit_amount'] = $difference;
        //             $entry['debit_amount'] = 0;
        //         } elseif ($difference < 0) {
        //             // Net debit
        //             $entry['debit_amount'] = abs($difference);
        //             $entry['credit_amount'] = 0;
        //         } else {
        //             // Net zero
        //             $entry['debit_amount'] = 0;
        //             $entry['credit_amount'] = 0;
        //         }

        //         $finalGrouped[] = $entry;
        //     }

        //     $postingArrays[$accountType] = $finalGrouped;
        // }

        // $postingArray=$postingArrays;


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

                $totalDebitAmount += $postingValue['debit_amount'];
                $totalCreditAmount += $postingValue['credit_amount'];
            }
        }
        //Balance does not match
        // if (round($totalDebitAmount,6) !== round($totalCreditAmount,6)) {
        //     return array(
        //         'status' => false,
        //         'message' => 'Credit Amount does not match Debit Amount',
        //         'data' => []
        //     );
        // }

        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => 'Financial Book Code is not specified',
                'data' => []
            );
        }

        $currency = Currency::find($document->currency_id);
        $userData = Helper::userCheck();
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }
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
            'comp_currency_id' => $document->comp_currency_id, // Missing comma added here
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
            'approvalStatus' => ConstantHelper::APPROVED,
            'document_status' => ConstantHelper::APPROVED,
            'approvalLevel' => $document->approval_level,
            'remarks' => $remarks,
            'location' => $document?->store_id
        ];

        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document);

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
                'document_number' => $document?->document_number,
                'currency_code' => $currency?->short_name
            ]
        );
    }

    public static function receiptVoucherPosting(int $bookId, int $documentId, string $type, string $remarks)
    {
        //Check Book
        $book = Book::find($bookId);
        if (!isset($book)) {
            return array(
                'status' => false,
                'message' => 'Book not found',
                'data' => []
            );
        }
        //Check Service
        $service = Service::find($book->service_id);
        if (!isset($service)) {
            return array(
                'status' => false,
                'message' => 'Service not found',
                'data' => []
            );
        }
        $isFinanceVoucherDefined = ServiceParametersHelper::getFinancialServiceAlias($service->alias);
        if (!isset($isFinanceVoucherDefined)) {
            return array(
                'status' => false,
                'message' => '',
                'data' => []
            );
        }
        //Check Posting parameters
        $financialPostParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM)->first();
        if (!isset($financialPostParam)) {
            return array(
                'status' => false,
                'message' => 'GL Posting Parameter not specified',
                'data' => []
            );
        }
        $isPostingRequired = (($financialPostParam->parameter_value[0] ?? '') === 'yes' ? true : false);
        if (!$isPostingRequired) {
            return array(
                'status' => false,
                'message' => '',
                'data' => []
            );
        }

        //Call helpers according to service
        $serviceAlias = $service->alias;
        if ($serviceAlias === ConstantHelper::RECEIPTS_SERVICE_ALIAS) {
            $entries = self::receiptInvoiceVoucherDetails($documentId, $remarks);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else {
            $entries = array(
                'status' => false,
                'message' => 'No method found',
                'data' => []
            );
        }
        if ($type === 'post') {
            $entries['data']['remarks'] = $remarks;
            return self::postVoucher($entries['data']);
        } else {
            return $entries;
        }
    }
    public static function paymentInvoiceVoucherDetails(int $documentId, string $remarks)
    {
        $accountSetup = isset(self::SERVICE_POSTING_MAPPING[ConstantHelper::PAYMENTS_SERVICE_ALIAS]) ? self::SERVICE_POSTING_MAPPING[ConstantHelper::PAYMENTS_SERVICE_ALIAS] : [];
        if (!isset($accountSetup) || count($accountSetup) == 0) {
            return array(
                'status' => false,
                'message' => 'Account Setup not found',
                'data' => []
            );
        }
        $document = PaymentVoucher::find($documentId);
        $vendors = $document->details;
        $vocuherdata = $document;
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }
        //Make array according to setup
        $postingArray = array(
            self::PAYMENT_ACCOUNT => [],
            self::VENDOR_ACCOUNT => [],
        );
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;

        $ledgerErrorStatus = null;
        // dd($vocuherdata);

        if (!empty($vocuherdata)) {
            $BankLedgerId = $vocuherdata->ledger_id;
            $BankLedgerGroupId = $vocuherdata->ledger_group_id;
            // dd($BankLedgerGroupId);
            $BankLedger = Ledger::find($BankLedgerId);
            $BankLedgerGroup = Group::find($BankLedgerGroupId);
        }

        if(!isset($BankLedger)){
                return array(
                    'status' => false,
                    'message' => 'Bank Ledger not setup',
                    'data' => []
                );
        }

        if(!isset($BankLedgerGroup)){
            return array(
                'status' => false,
                'message' => 'Bank Ledger Group not found',
                'data' => []
            );
        }

        $cost = CostCenter::find($document?->cost_center_id);
        $ids = array_column(Helper::getActiveCostCenters($document->location), 'id');
        $exists = in_array($cost?->id, $ids);

        if (!$exists && $cost != null) {
            return array(
                'status' => false,
                'message' => $cost->name . ' not Mapped with header location',
                'data' => []
            );
        }



        array_push($postingArray[self::PAYMENT_ACCOUNT], [
            'ledger_id' => $vocuherdata->ledger_id,
            'ledger_group_id' => $vocuherdata->ledger_group_id,
            'cost_center_id' => $document?->cost_center_id,
            'cost_name' => $cost->name ?? "-",
            'ledger_code' => $BankLedger?->code,
            'ledger_name' => $BankLedger?->name,
            'ledger_group_code' => $BankLedgerGroup?->name,
            'debit_amount' => 0,
            'credit_amount' => $vocuherdata->amount
        ]);
        foreach ($vendors as $vendor) {
            if (!empty($vendor)) {
                if ($vendor->reference == "Invoice") {
                    $invoices = VoucherReference::where('voucher_details_id', $vendor->id)
                        ->where('party_id', $vendor->ledger_id)->get();
                    $VendorLedgerId = $vendor->ledger_id;
                    $VendorLedgerGroupId = $vendor->ledger_group_id;
                    $VendorLedger = Ledger::find($VendorLedgerId);
                    $VendorLedgerGroup = Group::find($VendorLedgerGroupId);


                    if (!isset($VendorLedger) || !isset($VendorLedgerGroup)) {
                        return array(
                            'status' => false,
                            'message' => 'Vendor Ledger not setup',
                            'data' => []
                        );
                    }

                    foreach ($invoices as $invoice) {
                        $item = ItemDetail::where('voucher_id', $invoice->voucher_id)
                            ->where('ledger_id', $vendor->ledger_id)
                            ->where('ledger_parent_id', $vendor->ledger_group_id)
                            ->where('credit_amt_org', '>', 0)
                            ->first();

                        $cost = CostCenter::find($item?->cost_center_id);
                        $ids = array_column(Helper::getActiveCostCenters($document->location), 'id');
                        $exists = in_array($cost?->id, $ids);

                        if (!$exists && $cost != null) {
                            return array(
                                'status' => false,
                                'message' => $cost->name . ' not Mapped with header location',
                                'data' => []
                            );
                        }


                        $newEntry = [
                            'ledger_id' => $VendorLedgerId,
                            'ledger_group_id' => $VendorLedgerGroupId,
                            'ledger_code' => $VendorLedger?->code,
                            'ledger_name' => $VendorLedger?->name,
                            'cost_center_id' => $item?->cost_center_id,
                            'cost_name' => $cost->name ?? "-",
                            'ledger_group_code' => $VendorLedgerGroup?->name,
                            'credit_amount' => 0,
                            'debit_amount' => $invoice->amount,
                        ];

                        $found = false;

                        // Check if a similar entry already exists
                        foreach ($postingArray[self::VENDOR_ACCOUNT] as &$existingEntry) {
                            if (
                                $existingEntry['ledger_id'] === $newEntry['ledger_id'] &&
                                $existingEntry['ledger_group_id'] === $newEntry['ledger_group_id'] &&
                                $existingEntry['cost_center_id'] === $newEntry['cost_center_id']
                            ) {
                                // Match found, update the debit amount
                                $existingEntry['debit_amount'] += $newEntry['debit_amount'];
                                $found = true;
                                break;
                            }
                        }

                        // If not found, push as a new entry
                        if (!$found) {
                            $postingArray[self::VENDOR_ACCOUNT][] = $newEntry;
                        }
                    }
                } else {
                    $VendorLedgerId = $vendor->ledger_id;
                    $VendorLedgerGroupId = $vendor->ledger_group_id;
                    $VendorLedger = Ledger::find($VendorLedgerId);
                    $VendorLedgerGroup = Group::find($VendorLedgerGroupId);


                    if (!isset($VendorLedger) || !isset($VendorLedgerGroup)) {
                        return array(
                            'status' => false,
                            'message' => 'Vendor Ledger not setup',
                            'data' => []
                        );
                    }
                    $cost = CostCenter::find($document?->cost_center_id);
                    $ids = array_column(Helper::getActiveCostCenters($document->location), 'id');
                    $exists = in_array($cost?->id, $ids);

                    if (!$exists && $cost != null) {
                        return array(
                            'status' => false,
                            'message' => $cost->name . ' not Mapped with header location',
                            'data' => []
                        );
                    }



                    array_push($postingArray[self::VENDOR_ACCOUNT], [
                        'ledger_id' => $VendorLedgerId,
                        'ledger_group_id' => $VendorLedgerGroupId,
                        'ledger_code' => $VendorLedger?->code,
                        'ledger_name' => $VendorLedger?->name,
                        'cost_center_id' => $document?->cost_center_id,
                        'cost_name' => $cost->name ?? "-",
                        'ledger_group_code' => $VendorLedgerGroup?->name,
                        'credit_amount' => 0,
                        'debit_amount' => $vendor->currentAmount,
                    ]);
                }
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

                $totalDebitAmount += $postingValue['debit_amount'];
                $totalCreditAmount += $postingValue['credit_amount'];
            }
        }
        //Balance does not match
        // if (round($totalDebitAmount,6) !== round($totalCreditAmount,6)) {
        //     return array(
        //         'status' => false,
        //         'message' => 'Credit Amount does not match Debit Amount',
        //         'data' => []
        //     );
        // }
        $currency = Currency::find($document->currency_id);

        $userData = Helper::userCheck();

        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $gl_book = Book::find($glPostingBookId);
        $voucherHeader = [
            'voucher_no' => $document->voucher_no,
            'document_date' => $document->document_date,
            'book_id' => $glPostingBookId,
            'voucher_name' => $gl_book?->book_code,
            'date' => $document->document_date,
            'amount' => $totalCreditAmount,
            'location' => $document->location ?? null,
            'currency_id' => $document->currency_id,
            'currency_code' => $document->currencyCode,
            'org_currency_id' => $document->org_currency_id,
            'org_currency_code' => $document->org_currency_code,
            'org_currency_exg_rate' => $document->org_currency_exg_rate,
            'comp_currency_id' => $document->comp_currency_id, // Missing comma added here
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
            'approvalStatus' => ConstantHelper::APPROVED,
            'document_status' => ConstantHelper::APPROVED,
            'approvalLevel' => $document->approval_level,
            'remarks' => $remarks,
        ];

        $voucherDetails = self::generateInvoiceDetailsArray($postingArray, $voucherHeader, $document);

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
                'org' => Helper::getAuthenticatedUser()?->organization?->name,
                'org_id' => Helper::getAuthenticatedUser()?->organization?->id,
                'book_code' => $book?->book_code,
                'document_number' => $document?->voucher_no,
                'currency_code' => $currency?->short_name
            ]
        );
    }
    public static function contraVoucherDetails(int $documentId, string $remarks)
    {
        $organization = Helper::getAuthenticatedUser()->organization;
        $accountSetup = isset(self::SERVICE_POSTING_MAPPING[ConstantHelper::PAYMENTS_SERVICE_ALIAS]) ? self::SERVICE_POSTING_MAPPING[ConstantHelper::PAYMENTS_SERVICE_ALIAS] : [];
        if (!isset($accountSetup) || count($accountSetup) == 0) {
            return array(
                'status' => false,
                'message' => 'Account Setup not found',
                'data' => []
            );
        }
        $document = PaymentVoucher::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }
        $vendors = $document->payments()
            ->with([
                'voucher' => function ($query) {
                    $query->withoutGlobalScope(DefaultGroupCompanyOrgScope::class);
                    $query->withoutGlobalScope('defaultLocation');

                }
            ])
            ->get();

        if ($vendors->isEmpty()) {
            return [];
        }



        $ledgerErrorStatus = null;
        $vouchersArray = [];

        foreach ($vendors as $key => $vendor) {

            $partyOrg = $vendor->voucher->organization;

            if ($partyOrg->id != $organization->id) {
                $sameOrgPosting = self::sameOrgPosting($partyOrg, $organization, $vendor);

                if (isset($sameOrgPosting['status']) && $sameOrgPosting['status'] === false)
                    return array(
                        'status' => false,
                        'message' => $sameOrgPosting['message'],
                        'data' => []
                    );

                $otherOrgPosting = self::otherOrgPosting($partyOrg, $organization, $vendor);
                if (isset($otherOrgPosting['status']) && $otherOrgPosting['status'] === false)
                    return array(
                        'status' => false,
                        'message' => $otherOrgPosting['message'],
                        'data' => []
                    );


                if (!isset($vouchersArray[$organization->id])) {
                    $vouchersArray[$organization->id] = []; // initialize blank if not set
                }

                foreach ($sameOrgPosting as $keyName => $entries) {
                    $vouchersArray[$organization->id][$keyName] = array_merge(
                        $vouchersArray[$organization->id][$keyName] ?? [],
                        $entries
                    );
                }

                $vouchersArray[$partyOrg->id] = array_merge($vouchersArray[$partyOrg->id] ?? [], (array) $otherOrgPosting);

                if ($key == 2) {


                }

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
        if (empty($vouchersArray))
            return [];

        foreach ($vouchersArray as $orgID => $postingArray) {
            $totalCreditAmount = 0;
            $totalDebitAmount = 0;

            $organization = Organization::find($orgID);
            //Check debit and credit tally
            foreach ($postingArray as $postAccount) {
                foreach ($postAccount as $postingValue) {
                    $totalDebitAmount += $postingValue['debit_amount'];
                    $totalCreditAmount += $postingValue['credit_amount'];
                }
            }

            $currency = Currency::find($document->currency_id);

            $userData = Helper::userCheck();

            $book = Book::find($document->book_id);
            $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::CONTRA_POSTING_SERIES_PARAM)->first();
            if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
                $glPostingBookId = $glPostingBookParam->parameter_value[0];
            } else {
                return array(
                    'status' => false,
                    'message' => self::ERROR_PREFIX . 'Contra Book Code is not specified',
                    'data' => []
                );
            }
            $doc = Helper::generateContraDocNumber($glPostingBookId, $document->document_date, $organization->id);
            if (!isset($doc))
                return array(
                    'status' => false,
                    'message' => "Invalid Contra Book",
                    'data' => []
                );
            $gl_book = Book::find($glPostingBookId);


            $voucherHeader = [
                'voucher_no' => $doc['document_number'],
                'doc_number_type' => $doc['type'],
                'doc_reset_pattern' => $doc['reset_pattern'],
                'doc_prefix' => $doc['prefix'],
                'doc_suffix' => $doc['suffix'],
                'doc_no' => $doc['doc_no'],
                'voucher_name' => $gl_book?->book_code,
                'document_date' => $document->document_date,
                'book_id' => $glPostingBookId,
                'date' => $document->document_date,
                'amount' => $totalCreditAmount,
                'location' => $document->location ?? null,
                'currency_id' => $document->currency_id,
                'currency_code' => $document->currencyCode,
                'org_currency_id' => $document->org_currency_id,
                'org_currency_code' => $document->org_currency_code,
                'org_currency_exg_rate' => $document->org_currency_exg_rate,
                'comp_currency_id' => $document->comp_currency_id, // Missing comma added here
                'comp_currency_code' => $document->comp_currency_code,
                'comp_currency_exg_rate' => $document->comp_currency_exg_rate,
                'group_currency_id' => $document->group_currency_id,
                'group_currency_code' => $document->group_currency_code,
                'group_currency_exg_rate' => $document->group_currency_exg_rate,
                'reference_service' => $book?->service?->alias,
                'reference_doc_id' => $document->id,
                'group_id' => $organization->group_id,
                'company_id' => $organization->company_id,
                'organization_id' => $organization->id,
                'voucherable_type' => $userData['user_type'],
                'voucherable_id' => $userData['user_id'],
                'approvalStatus' => ConstantHelper::APPROVED,
                'document_status' => ConstantHelper::APPROVED,
                'approvalLevel' => $document->approval_level,
                'remarks' => $remarks,
            ];

            $voucherDetails = self::generateInvoiceDetailsArray($postingArray, $voucherHeader, $document);

            $vouchers[$orgID][] = array(
                'status' => true,
                'message' => 'Posting Details found',
                'data' => [
                    'voucher_header' => $voucherHeader,
                    'voucher_details' => $voucherDetails,
                    'document_date' => $document->document_date,
                    'ledgers' => $postingArray,
                    'total_debit' => $totalDebitAmount,
                    'total_credit' => $totalCreditAmount,
                    'book_code' => $gl_book?->book_code,
                    'org' => $organization?->name,
                    'org_id' => $organization?->id,
                    'document_number' => $doc['document_number'],
                    'currency_code' => $currency?->short_name
                ]
            );
        }
        return $vouchers;
    }

    public static function paymentVoucherPosting(int $bookId, int $documentId, string $type, string $remarks)
    {
        //Check Book
        $book = Book::find($bookId);
        if (!isset($book)) {
            return array(
                'status' => false,
                'message' => 'Book not found',
                'data' => []
            );
        }
        //Check Service
        $service = Service::find($book->service_id);
        if (!isset($service)) {
            return array(
                'status' => false,
                'message' => 'Service not found',
                'data' => []
            );
        }
        $isFinanceVoucherDefined = ServiceParametersHelper::getFinancialServiceAlias($service->alias);
        if (!isset($isFinanceVoucherDefined)) {
            return array(
                'status' => false,
                'message' => '',
                'data' => []
            );
        }
        //Check Posting parameters
        $financialPostParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM)->first();
        if (!isset($financialPostParam)) {
            return array(
                'status' => false,
                'message' => 'GL Posting Parameter not specified',
                'data' => []
            );
        }
        $isPostingRequired = (($financialPostParam->parameter_value[0] ?? '') === 'yes' ? true : false);
        if (!$isPostingRequired) {
            return array(
                'status' => false,
                'message' => '',
                'data' => []
            );
        }

        //Call helpers according to service
        $serviceAlias = $service->alias;
        if ($serviceAlias === ConstantHelper::PAYMENTS_SERVICE_ALIAS) {
            $entries = self::paymentInvoiceVoucherDetails($documentId, $remarks);
            if (!$entries['status']) {
                return array(
                    'status' => false,
                    'message' => $entries['message'],
                    'data' => []
                );
            }
        } else {
            $entries = array(
                'status' => false,
                'message' => 'No method found',
                'data' => []
            );
        }
        if ($type === 'post') {
            $entries['data']['remarks'] = $remarks;
            return self::postVoucher($entries['data']);
        } else {
            return $entries;
        }
    }

    public static function purchaseReturnVoucherDetails(int $documentId, string $type)
    {
        $document = PRHeader::find($documentId);
        if (!isset($document)) {
            return array(
                'status' => false,
                'message' => 'Document not found',
                'data' => []
            );
        }
        $postingArray = array(
            self::SUPPLIER_ACCOUNT => [],
            self::TAX_ACCOUNT => [],
            self::EXPENSE_ACCOUNT => [],
            self::DISCOUNT_ACCOUNT => [],
            self::STOCK_ACCOUNT => [],
            self::PR_ACCOUNT => [],
        );
        //Assign Credit and Debit amount for tally check
        $totalCreditAmount = 0;
        $totalDebitAmount = 0;

        // Vendor Detail
        $vendor = Vendor::find($document->vendor_id);
        $vendorLedgerId = $vendor->ledger_id;
        $vendorLedgerGroupId = $vendor->ledger_group_id;
        $vendorLedger = Ledger::find($vendorLedgerId);
        $vendorLedgerGroup = Group::find($vendorLedgerGroupId);
        //Vendor Ledger account not found
        if (!isset($vendorLedger) || !isset($vendorLedgerGroup)) {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Vendor Account not setup',
                'data' => []
            );
        }

        // $discountPostingParam = OrganizationBookParameter::where('book_id', $document -> book_id)
        // -> where('parameter_name', ServiceParametersHelper::GL_SEPERATE_DISCOUNT_PARAM) -> first();
        // if (isset($discountPostingParam)) {
        //     $discountSeperatePosting = $discountPostingParam -> parameter_value[0] === "yes" ? true : false;
        // } else {
        $discountSeperatePosting = false;
        // }


        //Status to check if all ledger entries were properly set
        $ledgerErrorStatus = null;
        //COGS SETUP
        foreach ($document->items as $docItemKey => $docItem) {
            // qty_return_type
            $itemValue = ($docItem->rate * $docItem->accepted_qty);
            $itemTotalDiscount = $docItem->header_discount_amount + $docItem->discount_amount;
            $itemValueAfterDiscount = $itemValue - $itemTotalDiscount;
            $stockDebitAmount = $discountSeperatePosting ? $itemValue : $itemValueAfterDiscount;

            if ($document->qty_return_type == 'rejected') {
                // Purchase Return Account For Rejected Value
                $stockLedgerDetails = AccountHelper::getPurchaseReturnLedgerGroupAndLedgerId($document->organization_id, $docItem->item_id, $document->book_id);

                $stockLedgerId = is_a($stockLedgerDetails, Collection::class) ? @$stockLedgerDetails->first()['ledger_id'] : null;
                $stockLedgerGroupId = is_a($stockLedgerDetails, Collection::class) ? @$stockLedgerDetails->first()['ledger_group'] : null;
                $stockLedger = Ledger::find($stockLedgerId);
                $stockLedgerGroup = Group::find($stockLedgerGroupId);
                //LEDGER NOT FOUND
                if (!isset($stockLedger) || !isset($stockLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'Purchase Return Account not setup';
                    break;
                }

                //Check for same ledger and group in SALES ACCOUNT
                $existingstockLedger = array_filter($postingArray[self::PR_ACCOUNT], function ($posting) use ($stockLedgerId, $stockLedgerGroupId) {
                    return $posting['ledger_id'] == $stockLedgerId && $posting['ledger_group_id'] == $stockLedgerGroupId;
                });
                //Ledger found
                //Ledger found
                if (count($existingstockLedger) > 0) {
                    $postingArray[self::PR_ACCOUNT][0]['credit_amount'] += $stockDebitAmount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::PR_ACCOUNT], [
                        'ledger_id' => $stockLedgerId,
                        'ledger_group_id' => $stockLedgerGroupId,
                        'ledger_code' => $stockLedger?->code,
                        'ledger_name' => $stockLedger?->name,
                        'ledger_group_code' => $stockLedgerGroup?->name,
                        'credit_amount' => $stockDebitAmount,
                        'debit_amount' => 0
                    ]);
                }
            } else {
                // Stock Account
                $stockLedgerDetails = AccountHelper::getStockLedgerGroupAndLedgerId($document->organization_id, $docItem->item_id, $document->book_id);
                $stockLedgerId = is_a($stockLedgerDetails, Collection::class) ? $stockLedgerDetails->first()['ledger_id'] : null;
                $stockLedgerGroupId = is_a($stockLedgerDetails, Collection::class) ? $stockLedgerDetails->first()['ledger_group'] : null;
                $stockLedger = Ledger::find($stockLedgerId);
                $stockLedgerGroup = Group::find($stockLedgerGroupId);
                //LEDGER NOT FOUND
                if (!isset($stockLedger) || !isset($stockLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'Stock Account not setup';
                    break;
                }
                //Check for same ledger and group in SALES ACCOUNT
                $existingstockLedger = array_filter($postingArray[self::STOCK_ACCOUNT], function ($posting) use ($stockLedgerId, $stockLedgerGroupId) {
                    return $posting['ledger_id'] == $stockLedgerId && $posting['ledger_group_id'] == $stockLedgerGroupId;
                });
                //Ledger found
                if (count($existingstockLedger) > 0) {
                    $postingArray[self::STOCK_ACCOUNT][0]['credit_amount'] += $stockDebitAmount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::STOCK_ACCOUNT], [
                        'ledger_id' => $stockLedgerId,
                        'ledger_group_id' => $stockLedgerGroupId,
                        'ledger_code' => $stockLedger?->code,
                        'ledger_name' => $stockLedger?->name,
                        'ledger_group_code' => $stockLedgerGroup?->name,
                        'credit_amount' => $stockDebitAmount,
                        'debit_amount' => 0
                    ]);
                }
            }



            //Stock for SUPPLIER ACCOUNT
            $existingVendorLedger = array_filter($postingArray[self::SUPPLIER_ACCOUNT], function ($posting) use ($vendorLedgerId, $vendorLedgerGroupId) {
                return $posting['ledger_id'] == $vendorLedgerId && $posting['ledger_group_id'] === $vendorLedgerGroupId;
            });
            //Ledger found
            if (count($existingVendorLedger) > 0) {
                $postingArray[self::SUPPLIER_ACCOUNT][0]['debit_amount'] += $itemValueAfterDiscount;
            } else { //Assign new ledger
                array_push($postingArray[self::SUPPLIER_ACCOUNT], [
                    'ledger_id' => $vendorLedgerId,
                    'ledger_group_id' => $vendorLedgerGroupId,
                    'ledger_code' => $vendorLedger?->code,
                    'ledger_name' => $vendorLedger?->name,
                    'ledger_group_code' => $vendorLedgerGroup?->name,
                    'credit_amount' => 0,
                    'debit_amount' => $itemValueAfterDiscount
                ]);
            }
        }
        //TAXES ACCOUNT
        $taxes = PRTed::where('header_id', $document->id)->where('ted_type', "Tax")->get();
        foreach ($taxes as $tax) {
            $taxDetail = TaxDetail::find($tax->ted_id);
            $taxLedgerId = $taxDetail->ledger_id ?? null; //MAKE IT DYNAMIC
            $taxLedgerGroupId = $taxDetail->ledger_group_id ?? null; //MAKE IT DYNAMIC
            $taxLedger = Ledger::find($taxLedgerId);
            $taxLedgerGroup = Group::find($taxLedgerGroupId);
            if (!isset($taxLedger) || !isset($taxLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Tax Account not setup';
                break;
            }
            $existingTaxLedger = array_filter($postingArray[self::TAX_ACCOUNT], function ($posting) use ($taxLedgerId, $taxLedgerGroupId) {
                return $posting['ledger_id'] == $taxLedgerId && $posting['ledger_group_id'] === $taxLedgerGroupId;
            });
            //Ledger found
            if (count($existingTaxLedger) > 0) {
                $postingArray[self::TAX_ACCOUNT][0]['credit_amount'] += $tax->ted_amount;
            } else { //Assign a new ledger
                array_push($postingArray[self::TAX_ACCOUNT], [
                    'ledger_id' => $taxLedgerId,
                    'ledger_group_id' => $taxLedgerGroupId,
                    'ledger_code' => $taxLedger?->code,
                    'ledger_name' => $taxLedger?->name,
                    'ledger_group_code' => $taxLedgerGroup?->name,
                    'credit_amount' => $tax->ted_amount,
                    'debit_amount' => 0,
                ]);
            }
            //Tax for SUPPLIER ACCOUNT
            $existingVendorLedger = array_filter($postingArray[self::SUPPLIER_ACCOUNT], function ($posting) use ($vendorLedgerId, $vendorLedgerGroupId) {
                return $posting['ledger_id'] == $vendorLedgerId && $posting['ledger_group_id'] === $vendorLedgerGroupId;
            });
            //Ledger found
            if (count($existingVendorLedger) > 0) {
                $postingArray[self::SUPPLIER_ACCOUNT][0]['debit_amount'] += $tax->ted_amount;
            } else { //Assign new ledger
                array_push($postingArray[self::SUPPLIER_ACCOUNT], [
                    'ledger_id' => $vendorLedgerId,
                    'ledger_group_id' => $vendorLedgerGroupId,
                    'ledger_code' => $vendorLedger?->code,
                    'ledger_name' => $vendorLedger?->name,
                    'ledger_group_code' => $vendorLedgerGroup?->name,
                    'credit_amount' => 0,
                    'debit_amount' => $tax->ted_amount,
                ]);
            }
        }

        //EXPENSES
        $expenses = PRTed::where('header_id', $document->id)->where('ted_type', "Expense")->get();
        foreach ($expenses as $expense) {
            $expenseDetail = ExpenseMaster::find($expense->ted_id);
            $expenseLedgerId = $expenseDetail?->expense_ledger_id; //MAKE IT DYNAMIC - 5
            $expenseLedgerGroupId = $expenseDetail?->expense_ledger_group_id; //MAKE IT DYNAMIC - 9
            $expenseLedger = Ledger::find($expenseLedgerId);
            $expenseLedgerGroup = Group::find($expenseLedgerGroupId);
            if (!isset($expenseLedger) || !isset($expenseLedgerGroup)) {
                $ledgerErrorStatus = self::ERROR_PREFIX . 'Expense Account not setup';
                break;
            }
            $existingExpenseLedger = array_filter($postingArray[self::EXPENSE_ACCOUNT], function ($posting) use ($expenseLedgerId, $expenseLedgerGroupId) {
                return $posting['ledger_id'] == $expenseLedgerId && $posting['ledger_group_id'] === $expenseLedgerGroupId;
            });
            //Ledger found
            if (count($existingExpenseLedger) > 0) {
                $postingArray[self::EXPENSE_ACCOUNT][0]['credit_amount'] += $expense->ted_amount;
            } else { //Assign a new ledger
                array_push($postingArray[self::EXPENSE_ACCOUNT], [
                    'ledger_id' => $expenseLedgerId,
                    'ledger_group_id' => $expenseLedgerGroupId,
                    'ledger_code' => $expenseLedger?->code,
                    'ledger_name' => $expenseLedger?->name,
                    'ledger_group_code' => $expenseLedgerGroup?->name,
                    'credit_amount' => $expense->ted_amount,
                    'debit_amount' => 0,
                ]);
            }
            //Expense for SUPPLIER ACCOUNT
            $existingVendorLedger = array_filter($postingArray[self::SUPPLIER_ACCOUNT], function ($posting) use ($vendorLedgerId, $vendorLedgerGroupId) {
                return $posting['ledger_id'] == $vendorLedgerId && $posting['ledger_group_id'] === $vendorLedgerGroupId;
            });
            //Ledger found
            if (count($existingVendorLedger) > 0) {
                $postingArray[self::SUPPLIER_ACCOUNT][0]['debit_amount'] += $expense->ted_amount;
            } else { //Assign new ledger
                array_push($postingArray[self::EXPENSE_ACCOUNT], [
                    'ledger_id' => $vendorLedgerId,
                    'ledger_group_id' => $vendorLedgerGroupId,
                    'ledger_code' => $vendorLedger?->code,
                    'ledger_name' => $vendorLedger?->name,
                    'ledger_group_code' => $vendorLedgerGroup?->name,
                    'credit_amount' => 0,
                    'debit_amount' => $expense->ted_amount,
                ]);
            }
        }
        //Seperate posting of Discount
        if ($discountSeperatePosting) {
            $discounts = PRTed::where('header_id', $document->id)->where('ted_type', "Discount")->get();
            foreach ($discounts as $discount) {
                $discountDetail = DiscountMaster::find($discount->ted_id);
                $discountLedgerId = $discountDetail?->discount_ledger_id; //MAKE IT DYNAMIC
                $discountLedgerGroupId = $discountDetail?->discount_ledger_group_id; //MAKE IT DYNAMIC
                $discountLedger = Ledger::find($discountLedgerId);
                $discountLedgerGroup = Group::find($discountLedgerGroupId);
                if (!isset($discountLedger) || !isset($discountLedgerGroup)) {
                    $ledgerErrorStatus = self::ERROR_PREFIX . 'Discount Account not setup';
                    break;
                }
                $existingDiscountLedger = array_filter($postingArray[self::DISCOUNT_ACCOUNT], function ($posting) use ($discountLedgerId, $discountLedgerGroupId) {
                    return $posting['ledger_id'] == $discountLedgerId && $posting['ledger_group_id'] === $discountLedgerGroupId;
                });
                //Ledger found
                if (count($existingDiscountLedger) > 0) {
                    $postingArray[self::DISCOUNT_ACCOUNT][0]['debit_amount'] += $discount->ted_amount;
                } else { //Assign a new ledger
                    array_push($postingArray[self::DISCOUNT_ACCOUNT], [
                        'ledger_id' => $discountLedgerId,
                        'ledger_group_id' => $discountLedgerGroupId,
                        'ledger_code' => $discountLedger?->code,
                        'ledger_name' => $discountLedger?->name,
                        'ledger_group_code' => $discountLedgerGroup?->name,
                        'debit_amount' => $discount->ted_amount,
                        'credit_amount' => 0,
                    ]);
                }
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
        //Balance does not match
        // if (round($totalDebitAmount,6) !== round($totalCreditAmount,6)) {
        //     return array(
        //         'status' => false,
        //         'message' => self::ERROR_PREFIX.'Credit Amount does not match Debit Amount',
        //         'data' => []
        //     );
        // }
        //Get Header Details
        $book = Book::find($document->book_id);
        $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();
        if (isset($glPostingBookParam) && isset($glPostingBookParam->parameter_value[0])) {
            $glPostingBookId = $glPostingBookParam->parameter_value[0];
        } else {
            return array(
                'status' => false,
                'message' => self::ERROR_PREFIX . 'Financial Book Code is not specified',
                'data' => []
            );
        }
        $currency = Currency::find($document->currency_id);
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
        $voucherDetails = self::generateVoucherDetailsArray($postingArray, $voucherHeader, $document);

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

    public static function generateVoucherDetailsArray(array $postingArray, array $voucherHeader, mixed $document, string $currencyIdKey = 'currency_id', string $documentDateKey = 'document_date', bool $stockCogDnCheck = false)
    {
        $voucherDetails = [];
        foreach ($postingArray as $entryType => $postDetails) {
            foreach ($postDetails as $post) {
                // dd($post['credit_amount'], $document);
                $groupCurrencyCreditAmt = CurrencyHelper::convertAmtToGroupCompOrgCurrency($post['credit_amount'], $document->{$currencyIdKey}, $document->{$documentDateKey});
                $groupCurrencyDebitAmt = CurrencyHelper::convertAmtToGroupCompOrgCurrency($post['debit_amount'], $document->{$currencyIdKey}, $document->{$documentDateKey});

                $debitAmtOrg = $groupCurrencyDebitAmt['org_currency_amount'];
                $creditAmtOrg = $groupCurrencyCreditAmt['org_currency_amount'];

                $debitAmtComp = $groupCurrencyDebitAmt['comp_currency_amount'];
                $creditAmtComp = $groupCurrencyCreditAmt['comp_currency_amount'];

                $debitAmtGroup = $groupCurrencyDebitAmt['group_currency_amount'];
                $creditAmtGroup = $groupCurrencyCreditAmt['group_currency_amount'];

                if (($entryType === self::COGS_ACCOUNT || $entryType === self::STOCK_ACCOUNT) && $stockCogDnCheck) {
                    $debitAmtOrg = $post['debit_amount_org'];
                    $creditAmtOrg = $post['credit_amount_org'];
                    if ($voucherHeader['org_currency_code'] === $voucherHeader['comp_currency_code']) {
                        $debitAmtComp = $post['debit_amount_org'];
                        $creditAmtComp = $post['credit_amount_org'];
                    }
                    if ($voucherHeader['org_currency_code'] === $voucherHeader['group_currency_code']) {
                        $debitAmtGroup = $post['debit_amount_org'];
                        $creditAmtGroup = $post['credit_amount_org'];
                    }
                }
                array_push($voucherDetails, [
                    'ledger_id' => $post['ledger_id'],
                    'ledger_parent_id' => $post['ledger_group_id'],
                    'debit_amt' => $post['debit_amount'],
                    'credit_amt' => $post['credit_amount'],
                    'cost_center_id' => ($document?->cost_center_id) ?: null,
                    'debit_amt_org' => $debitAmtOrg,
                    'credit_amt_org' => $creditAmtOrg,
                    'debit_amt_comp' => $debitAmtComp,
                    'credit_amt_comp' => $creditAmtComp,
                    'debit_amt_group' => $debitAmtGroup,
                    'credit_amt_group' => $creditAmtGroup,
                    'entry_type' => $entryType,
                    'due_date' => isset($post['due_date']) ? $post['due_date'] : null
                ]);
            }
        }
        return $voucherDetails;
    }
    public static function generateInvoiceDetailsArray(array $postingArray, array $voucherHeader, mixed $document, string $currencyIdKey = 'currency_id', string $documentDateKey = 'document_date', bool $stockCogDnCheck = false)
    {
        $voucherDetails = [];
        foreach ($postingArray as $entryType => $postDetails) {
            foreach ($postDetails as $post) {
                $groupCurrencyCreditAmt = CurrencyHelper::convertAmtToGroupCompOrgCurrency($post['credit_amount'], $document->{$currencyIdKey}, $document->{$documentDateKey});
                $groupCurrencyDebitAmt = CurrencyHelper::convertAmtToGroupCompOrgCurrency($post['debit_amount'], $document->{$currencyIdKey}, $document->{$documentDateKey});

                $debitAmtOrg = $groupCurrencyDebitAmt['org_currency_amount'];
                $creditAmtOrg = $groupCurrencyCreditAmt['org_currency_amount'];

                $debitAmtComp = $groupCurrencyDebitAmt['comp_currency_amount'];
                $creditAmtComp = $groupCurrencyCreditAmt['comp_currency_amount'];

                $debitAmtGroup = $groupCurrencyDebitAmt['group_currency_amount'];
                $creditAmtGroup = $groupCurrencyCreditAmt['group_currency_amount'];

                if (($entryType === self::COGS_ACCOUNT || $entryType === self::STOCK_ACCOUNT) && $stockCogDnCheck) {
                    $debitAmtOrg = $post['debit_amount_org'];
                    $creditAmtOrg = $post['credit_amount_org'];
                    if ($voucherHeader['org_currency_code'] === $voucherHeader['comp_currency_code']) {
                        $debitAmtComp = $post['debit_amount_org'];
                        $creditAmtComp = $post['credit_amount_org'];
                    }
                    if ($voucherHeader['org_currency_code'] === $voucherHeader['group_currency_code']) {
                        $debitAmtGroup = $post['debit_amount_org'];
                        $creditAmtGroup = $post['credit_amount_org'];
                    }
                }
                array_push($voucherDetails, [
                    'ledger_id' => $post['ledger_id'],
                    'ledger_parent_id' => $post['ledger_group_id'],
                    'debit_amt' => $post['debit_amount'],
                    'credit_amt' => $post['credit_amount'],
                    'cost_center_id' => $post['cost_center_id'] ?? null,
                    'debit_amt_org' => $debitAmtOrg,
                    'credit_amt_org' => $creditAmtOrg,
                    'debit_amt_comp' => $debitAmtComp,
                    'credit_amt_comp' => $creditAmtComp,
                    'debit_amt_group' => $debitAmtGroup,
                    'credit_amt_group' => $creditAmtGroup,
                    'entry_type' => $entryType,
                ]);
            }
        }
        return $voucherDetails;
    }
    public static function sameOrgPosting($partyOrg, $organization, $vendor)
    {
        $postingArray = array(
            self::CONTRA => [],
            self::VENDOR_ACCOUNT => [],
        );
        $orgVendor = Vendor::where('enter_company_org_id', $partyOrg->id)
            ->where('company_name', $partyOrg->name)->first();


        if (empty($orgVendor))
            return array(
                'status' => false,
                'message' => $vendor?->voucher?->organization?->name . ' Vendor not found in ' . $organization->name,
                'data' => []
            );

        $contraLedgerId = $orgVendor->contra_ledger_id;
        if (empty($contraLedgerId))
            return array(
                'status' => false,
                'message' => $vendor?->voucher?->organization?->name . ' Contra Ledger not found in ' . $organization?->name,
                'data' => []
            );
        $contraLedgerGroupId = $orgVendor->contraLedger->group() ?? null;
        if (empty($contraLedgerGroupId))
            return array(
                'status' => false,
                'message' => $vendor?->voucher?->organization?->name . ' Contra Ledger Group not found in ' . $organization->name,
                'data' => []
            );
        $contraLedger = Ledger::find($contraLedgerId);
        $contraLedgerGroup = Group::find($contraLedgerGroupId[0]?->id);
        if (!isset($contraLedger) || !isset($contraLedgerGroup))
            return array(
                'status' => false,
                'message' => 'Contra Ledger not setup',
                'data' => []
            );
        array_push($postingArray[self::CONTRA], [
            'ledger_id' => $contraLedger->id,
            'ledger_group_id' => $contraLedgerGroup->id,
            'ledger_code' => $contraLedger?->code,
            'ledger_name' => $contraLedger?->name,
            'ledger_group_code' => $contraLedgerGroup?->name,
            'debit_amount' => $vendor->amount,
            'credit_amount' => 0,
        ]);


        $vendorLedgerId = $vendor->party_id;
        if (empty($vendorLedgerId))
            return array(
                'status' => false,
                'message' => 'Vendor Ledger not setup',
                'data' => []
            );
        $vendorLedgerGroupId = $vendor->ledger->group() ?? null;
        if ($vendorLedgerGroupId->isEmpty())
            return array(
                'status' => false,
                'message' => 'Vendor Ledger Group not setup',
                'data' => []
            );
        $vendorLedgerGroupId = $vendorLedgerGroupId[0]->id;

        $vendorLedger = Ledger::find($vendorLedgerId);
        $vendorLedgerGroup = Group::find($vendorLedgerGroupId);
        if (!isset($vendorLedger) || !isset($vendorLedgerGroup)) {
            return array(
                'status' => false,
                'message' => 'Vendor Ledger not setup',
                'data' => []
            );
        }
        array_push($postingArray[self::VENDOR_ACCOUNT], [
            'ledger_id' => $vendorLedger->id,
            'ledger_group_id' => $vendorLedgerGroup->id,
            'ledger_code' => $vendorLedger?->code,
            'ledger_name' => $vendorLedger?->name,
            'ledger_group_code' => $vendorLedgerGroup?->name,
            'debit_amount' => 0,
            'credit_amount' => $vendor->amount,
        ]);
        return $postingArray;
    }
    public static function otherOrgPosting($partyOrg, $organization, $vendor)
    {
        $postingArray = array(
            self::CONTRA => [],
            self::VENDOR_ACCOUNT => [],
        );
        $orgVendor = Vendor::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
            ->where('group_id', $partyOrg->group_id)
            ->where('company_id', $partyOrg->company_id)
            ->where('enter_company_org_id', $organization->id)
            ->where('company_name', $organization->name)->first();

        if (empty($orgVendor))
            return array(
                'status' => false,
                'message' => $organization->name . ' Vendor not found in ' . $vendor?->voucher?->organization?->name,
                'data' => []
            );

        $contraLedgerId = $orgVendor->contra_ledger_id;
        if (empty($contraLedgerId))
            return array(
                'status' => false,
                'message' => $organization->name . ' Contra Ledger not found in ' . $vendor?->voucher?->organization?->name,
                'data' => []
            );
        $contraLedgerGroupId = $orgVendor->contraLedger->group() ?? null;
        if (empty($contraLedgerGroupId))
            return array(
                'status' => false,
                'message' => $organization->name . ' Contra Ledger Group not found in ' . $vendor?->voucher?->organization?->name,
                'data' => []
            );

        $contraLedger = Ledger::find($contraLedgerId);
        $contraLedgerGroup = Group::find($contraLedgerGroupId[0]?->id);
        if (!isset($contraLedger) || !isset($contraLedgerGroup))
            return array(
                'status' => false,
                'message' => 'Contra Ledger not setup',
                'data' => []
            );
        array_push($postingArray[self::CONTRA], [
            'ledger_id' => $contraLedger->id,
            'ledger_group_id' => $contraLedgerGroup->id,
            'ledger_code' => $contraLedger?->code,
            'ledger_name' => $contraLedger?->name,
            'ledger_group_code' => $contraLedgerGroup?->name,
            'debit_amount' => 0,
            'credit_amount' => $vendor->amount,
        ]);

        $vendorLedgerId = $vendor->party_id;
        if (empty($vendorLedgerId))
            return array(
                'status' => false,
                'message' => 'Vendor Ledger not setup',
                'data' => []
            );
        $vendorLedgerGroupId = $vendor->ledger->group() ?? null;
        if ($vendorLedgerGroupId->isEmpty())
            return array(
                'status' => false,
                'message' => 'Vendor Ledger Group not setup',
                'data' => []
            );
        $vendorLedgerGroupId = $vendorLedgerGroupId[0]->id;

        $vendorLedger = Ledger::find($vendorLedgerId);
        $vendorLedgerGroup = Group::find($vendorLedgerGroupId);
        if (!isset($vendorLedger) || !isset($vendorLedgerGroup)) {
            return array(
                'status' => false,
                'message' => 'Vendor Ledger not setup',
                'data' => []
            );
        }

        array_push($postingArray[self::VENDOR_ACCOUNT], [
            'ledger_id' => $vendorLedger->id,
            'ledger_group_id' => $vendorLedgerGroup->id,
            'ledger_code' => $vendorLedger?->code,
            'ledger_name' => $vendorLedger?->name,
            'ledger_group_code' => $vendorLedgerGroup?->name,
            'debit_amount' => $vendor->amount,
            'credit_amount' => 0,
        ]);
        return $postingArray;
    }

}
