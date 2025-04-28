<?php

namespace App\Helpers;

use App\Models\AmendmentWorkflow;
use App\Models\ApprovalWorkflow;
use App\Models\AuthUser;
use App\Models\Address;
use App\Models\Bom;
use App\Models\Book;
use App\Models\BookLevel;
use App\Models\BookType;
use App\Models\DocumentApproval;
use App\Models\Employee;
use App\Models\EmployeeBookMapping;
use App\Models\EmployeeRole;
use App\Models\ErpAddress;
use App\Models\CRM\ErpCurrencyMaster;
use App\Models\ErpFinancialYear;
use App\Models\Group;
use App\Models\HomeLoan;
use App\Models\Item;
use App\Models\ItemDetail;
use App\Models\Ledger;
use App\Models\LoanDisbursement;
use App\Models\LoanLog;
use App\Models\Media;
use App\Models\NumberPattern;
use App\Models\Organization;
use App\Models\OrganizationMenu;
use App\Models\OrganizationService;
use App\Models\PermissionMaster;
use App\Models\Role;
use App\Models\RolePermission;
use App\Models\Service;
use App\Models\ServiceMenu;
use App\Models\Services;
use Exception;
use App\Models\OrganizationBookParameter;
use App\Models\OrganizationGroup;
use App\Models\PLGroups;
use App\Models\RecoveryLoan;
use App\Models\User;
use App\Models\Voucher;
use App\Models\ErpStore;
use App\Models\ErpMasterPolicy;
use App\Models\ErpOrganizationMasterPolicy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;
use Request;
use stdClass;
use Symfony\Component\Mime\Part\Multipart\MixedPart;
use Illuminate\Support\Str;

use App\Models\ErpSaleInvoice;
use App\Models\ErpInvoiceItem;
use App\Models\PRHeader;
use App\Models\PRDetail;

class Helper
{
    public static function formatStatus($status)
    {
        $status = str_replace('_', ' ', $status);
        $status = ucwords(strtolower($status));
        echo $status;
    }

    public static function generateVoucherNumber($book_id)
    {
        $data = NumberPattern::where('organization_id', self::getAuthenticatedUser()->organization_id)->where('book_id', $book_id)->orderBy('id', 'DESC')->first();
        $voucher_no = '';

        if ($data) {
            if ($data->series_numbering == "Auto") {
                if ($data->prefix != "") {
                    $voucher_no = $data->prefix;
                } else {
                    if ($data->reset_pattern == "Daily") {
                        $voucher_no = date('dFy');
                    } else if ($data->reset_pattern == "Monthly") {
                        $voucher_no = date('Fy');
                    } else if ($data->reset_pattern == "Yearly") {
                        $voucher_no = date('Y');
                    } elseif ($data->reset_pattern == "Quarterly") {
                        $voucher_no = "QA";
                    }
                }

                $voucher_no = $voucher_no . $data->current_no;

                if ($data->suffix) {
                    $voucher_no = $voucher_no . $data->suffix;
                }
            }
            $data = ['type' => $data->series_numbering, 'voucher_no' => $voucher_no];
            return $data;
        } else {
            $data = ['type' => 'Manually', 'voucher_no' => 1];
            return $data;
        }
    }

    public static function getSeriesCode($bookTypeName)
    {
        $series = Book::withDefaultGroupCompanyOrg()
            ->whereHas('org_service', function ($orgService) use ($bookTypeName) {
                $orgService->where('alias', $bookTypeName);
            })->where('status', ConstantHelper::ACTIVE);
        //Code modified due to change in requirement -> Jagdeep
        return $series;
    }

    public static function getBookSeries($serviceAlias)
    {
        $series = Book::withDefaultGroupCompanyOrg()
        ->whereHas('org_service', function ($orgService) use ($serviceAlias) {
            $orgService->where('alias', $serviceAlias);
        })->where('status', ConstantHelper::ACTIVE)->where('manual_entry', 1);
        //Code modified due to change in requirement -> Jagdeep
        return $series;
    }

    public static function getBookSeriesNew($serviceAlias, $menuServiceAlias = '', $isEdit = false)
    {
        $servicesBooks = self::getAccessibleServicesFromMenuAlias($menuServiceAlias, $isEdit ? $serviceAlias : '');
        $bookIds = $servicesBooks['books'];
        $allBookAccess = $servicesBooks['all_book_access'];
        $series = Book::withDefaultGroupCompanyOrg()
            ->whereHas('org_service', function ($orgService) use ($serviceAlias) {
                $orgService->where('alias', $serviceAlias);
            })->when($allBookAccess === false, function ($bookQuery) use ($bookIds) {
                $bookQuery->whereIn('id', $bookIds);
            })->where('status', ConstantHelper::ACTIVE)->where('manual_entry', 1);
        //Code modified due to change in requirement -> Jagdeep
        return $series;
    }

    public static function getBookTypes($serviceAlias)
    {
        $user = self::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first();
        $organizationId = $organization?->id;
        $companyId = $organization?->company_id;

        $bookTypes = BookType::selectRaw('*, COALESCE(company_id, ?) as company_id, COALESCE(organization_id, ?) as organization_id', [$companyId, $organizationId])
            ->where('group_id', $organization->group_id)
            ->with('books')
            ->whereHas('service', function ($service) use ($serviceAlias) {
                $service->whereIn('alias', $serviceAlias);
            })
            ->where('status', ConstantHelper::ACTIVE);
        return $bookTypes;
    }

    public static function getFinancialYear(string $date): mixed
    {
        $financialYear = ErpFinancialYear::withDefaultGroupCompanyOrg()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
        if (isset($financialYear)) {
            return [
                'alias' => $financialYear->alias,
                'start_date' => $financialYear->start_date,
                'end_date' => $financialYear->end_date,
            ];
        } else {
            return null;
        }
    }

    public static function getFinancialYearQuarter(string $date): mixed
    {
        $targetDate = Carbon::parse($date);
        $financialYear = ErpFinancialYear::withDefaultGroupCompanyOrg()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->first();
        $quarter = null;
        if (isset($financialYear)) {
            $startDate = Carbon::parse($financialYear->start_date);
            $endDate = Carbon::parse($financialYear->end_date);

            $quarter1Start = $startDate;
            $quarter1End = $startDate->copy()->addMonths(3)->subDay();

            $quarter2Start = $quarter1End->copy()->addDay();
            $quarter2End = $quarter2Start->copy()->addMonths(3)->subDay();

            $quarter3Start = $quarter2End->copy()->addDay();
            $quarter3End = $quarter3Start->copy()->addMonths(3)->subDay();

            $quarter4Start = $quarter3End->copy()->addDay();
            $quarter4End = $endDate;

            // Determine the quarter
            if ($targetDate->between($quarter1Start, $quarter1End)) {
                $quarter = [
                    'alias' => "Q1",
                    'start_date' => $quarter1Start,
                    'end_date' => $quarter1End
                ];
            } elseif ($targetDate->between($quarter2Start, $quarter2End)) {
                $quarter = [
                    'alias' => "Q2",
                    'start_date' => $quarter2Start,
                    'end_date' => $quarter2End
                ];
            } elseif ($targetDate->between($quarter3Start, $quarter3End)) {
                $quarter = [
                    'alias' => "Q3",
                    'start_date' => $quarter3Start,
                    'end_date' => $quarter3End
                ];
            } elseif ($targetDate->between($quarter4Start, $quarter4End)) {
                $quarter = [
                    'alias' => "Q4",
                    'start_date' => $quarter4Start,
                    'end_date' => $quarter4End
                ];
            } else {
                $quarter = null;
            }
        }
        return $quarter;
    }

    public static function getFinancialMonth(string $date): mixed
    {
        $targetDate = Carbon::parse($date);
        $startDate = $targetDate->copy()->startOfMonth();
        $endDate = $targetDate->copy()->endOfMonth();
        $monthName = strtoupper($targetDate->shortMonthName);
        return [
            'alias' => $monthName,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
    }

    public static function generateDocumentNumberNew(int $book_id, string $document_date, stdClass $parameters = null): mixed
    {

        $book = Book::find($book_id);
        $user = self::getAuthenticatedUser();
        $data = NumberPattern::where('book_id', $book_id)->orderBy('id', 'DESC')->first();
        $serviceAlias = $data?->book?->org_service?->alias;
        $modelName = isset(ConstantHelper::SERVICE_ALIAS_MODELS[$serviceAlias]) ? ConstantHelper::SERVICE_ALIAS_MODELS[$serviceAlias] : '';
        $financialYear = self::getFinancialYear($document_date);
        $financialQuarter = self::getFinancialYearQuarter($document_date);
        $financialMonth = self::getFinancialMonth($document_date);

        $prefix = "";
        $suffix = "";
        if ($data && $modelName) {

            $model = resolve('App\\Models\\' . $modelName);

            if ($data->series_numbering === ConstantHelper::DOC_NO_TYPE_AUTO) {
                $startFrom = $data->starting_no;
                if ($startFrom >= 1) {
                    $startFrom -= 1;
                }
                if ($data->reset_pattern === ConstantHelper::DOC_RESET_PATTERN_NEVER) {
                    $prefix = $data->prefix;
                    $suffix = $data->suffix;
                    $currentDocNo = $model->withDefaultGroupCompanyOrg()->where('book_id', $book_id)
                        ->whereNotNull('doc_no')->orderBy('doc_no', 'DESC')->pluck('doc_no')->first() ?? $startFrom;
                } else if ($data->reset_pattern === ConstantHelper::DOC_RESET_PATTERN_YEARLY) {
                    if (!(isset($financialYear) && isset($financialQuarter) && isset($financialMonth))) {
                        $data = [
                            'type' => null,
                            'document_number' => null,
                            'prefix' => null,
                            'suffix' => null,
                            'doc_no' => null,
                            'reset_pattern' => null,
                            'error' => 'Financial Year not setup'
                        ];
                        return $data;
                    }
                    $prefix = $financialYear['alias'];
                    $suffix = $data->suffix;
                    $currentDocNo = $model->withDefaultGroupCompanyOrg()->where('book_id', $book_id)
                        ->whereNotNull('doc_no')
                        ->whereBetween('document_date', [$financialYear['start_date'], $financialYear['end_date']])
                        ->orderBy('doc_no', 'DESC')->pluck('doc_no')->first() ?? $startFrom;
                } else if ($data->reset_pattern === ConstantHelper::DOC_RESET_PATTERN_QUARTERLY) {
                    if (!(isset($financialYear) && isset($financialQuarter) && isset($financialMonth))) {
                        $data = [
                            'type' => null,
                            'document_number' => null,
                            'prefix' => null,
                            'suffix' => null,
                            'doc_no' => null,
                            'reset_pattern' => null,
                            'error' => 'Financial Year not setup'
                        ];
                        return $data;
                    }
                    $prefix = $financialYear['alias'] . "-" . $financialQuarter['alias'];
                    $suffix = $data->suffix;
                    $currentDocNo = $model->withDefaultGroupCompanyOrg()->where('book_id', $book_id)
                        ->whereNotNull('doc_no')
                        ->whereBetween('document_date', [$financialQuarter['start_date'], $financialQuarter['end_date']])
                        ->orderBy('doc_no', 'DESC')->pluck('doc_no')->first() ?? $startFrom;
                } else {
                    if (isset($financialYear) && isset($financialQuarter) && isset($financialMonth)) {
                        $prefix = $financialYear['alias'] . "-" . $financialMonth['alias'];
                        $suffix = $data->suffix;
                        $currentDocNo = $model->withDefaultGroupCompanyOrg()->where('book_id', $book_id)
                            ->whereNotNull('doc_no')
                            ->whereBetween('document_date', [$financialMonth['start_date'], $financialMonth['end_date']])
                            ->orderBy('doc_no', 'DESC')->pluck('doc_no')->first() ?? $startFrom;
                    }

                }

                $currentDocNo = ($currentDocNo ? $currentDocNo : 0) + 1;

                $voucher_no = ($prefix ? $prefix . "-" : "") . ($currentDocNo) . ($suffix ? "-" . $suffix : "");

                //Condition for Sales Invoice/ Sales Return and Purchase Return
                $shouldCheckTransportDocForPrSr = in_array($book -> service ?-> service ?-> alias, [ConstantHelper::PURCHASE_RETURN_SERVICE_ALIAS,
                 ConstantHelper::SR_SERVICE_ALIAS]);
                $shouldCheckTransportDocForSi = false;

                if ($serviceAlias === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS && isset($parameters) && 
                    isset($parameters -> {ServiceParametersHelper::INVOICE_TO_FOLLOW_PARAM}))
                {
                    $shouldCheckTransportDocForSi = $parameters -> {ServiceParametersHelper::INVOICE_TO_FOLLOW_PARAM}[0] == "no";
                }
                
                if ($shouldCheckTransportDocForPrSr || $shouldCheckTransportDocForSi)
                {
                    if (strlen($book -> book_code . '-' . $voucher_no) > EInvoiceHelper::TRANPORTER_DOC_NO_MAX_LIMIT)
                    {
                        $data = [
                            'type' => null,
                            'document_number' => null,
                            'prefix' => null,
                            'suffix' => null,
                            'doc_no' => null,
                            'reset_pattern' => null,
                            'error' => 'Document Number cannot exceed 15 characters'
                        ];
                        return $data;
                    }
                }
                $data = [
                    'type' => ConstantHelper::DOC_NO_TYPE_AUTO,
                    'document_number' => $voucher_no,
                    'prefix' => $prefix,
                    'suffix' => $suffix,
                    'doc_no' => $currentDocNo,
                    'reset_pattern' => $data->reset_pattern,
                    'error' => null
                ];
                return $data;
            } else {
                $data = [
                    'type' => ConstantHelper::DOC_NO_TYPE_MANUAL,
                    'document_number' => null,
                    'prefix' => null,
                    'suffix' => null,
                    'doc_no' => null,
                    'reset_pattern' => null,
                    'error' => null
                ];
                return $data;
            }
        } else {
            $data = [
                'type' => null,
                'document_number' => null,
                'prefix' => null,
                'suffix' => null,
                'doc_no' => null,
                'reset_pattern' => null,
                'error' => 'Transaction not setup'
            ];
            return $data;
        }
    }
    public static function generateDocumentNumber($book_id)
    {
        $user = self::getAuthenticatedUser();
        $data = NumberPattern::where('organization_id', $user->organization_id)->where('book_id', $book_id)->orderBy('id', 'DESC')->first();
        $voucher_no = '';
        if ($data) {
            if ($data->series_numbering == "Auto") {
                if ($data->prefix != "") {
                    $voucher_no = $data->prefix;
                } else {
                    if ($data->reset_pattern == "Daily") {
                        $voucher_no = date('dFy');
                    } else if ($data->reset_pattern == "Monthly") {
                        $voucher_no = date('Fy');
                    } else if ($data->reset_pattern == "Yearly") {
                        $voucher_no = date('Y');
                    } elseif ($data->reset_pattern == "Quarterly") {
                        $voucher_no = "QA";
                    }
                }
                $voucher_no = $voucher_no . $data->current_no;

                if ($data->suffix) {
                    $voucher_no = $voucher_no . $data->suffix;
                }
            }
            $data = ['type' => $data->series_numbering, 'voucher_no' => $voucher_no];
            return $data;
        } else {
            $data = ['type' => 'Manually', 'voucher_no' => 1];
            return $data;
        }
    }

    public static function reGenerateDocumentNumber($book_id = null)
    {
        $user = self::getAuthenticatedUser();
        $numPattern = NumberPattern::where('organization_id', $user->organization_id)
            ->where('book_id', $book_id)
            ->orderBy('id', 'DESC')->first();
        // $document_number = null;

        if ($numPattern->series_numbering == 'Auto') {
            $document_number = self::generateDocumentNumber($numPattern->book_id);
            /*Udate current*/
            $numPattern->current_no = intval($numPattern->current_no) + 1;
            $numPattern->save();
            return $document_number['voucher_no'];
        }
    }

    public static function checkApprovalRequired($book_id = null, $docValue = 0)
    {
        $user = self::getAuthenticatedUser();
        $aw = BookLevel::where('book_id', $book_id)
            ->where('organization_id', $user->organization_id)
            ->where('level', 1)
            ->where('min_value', '<=', $docValue)
            ->orderByDesc('min_value')
            ->count();
        if ($aw > 0) {
            $document_status = ConstantHelper::SUBMITTED;
        } else {
            $document_status = ConstantHelper::APPROVAL_NOT_REQUIRED;
        }
        return $document_status;
    }

    public static function checkApprovalLoanRequired($book_id = null, $docValue = 0)
    {
        $user = self::getAuthenticatedUser();
        $aw = BookLevel::where('book_id', $book_id)
            ->where('organization_id', $user->organization_id)
            ->where('level', 1)
            ->where('min_value', '<=', $docValue)
            ->orderByDesc('min_value')
            ->count();

        if ($aw > 0) {
            $document_status = ConstantHelper::ASSESSED;
        } else {
            $document_status = ConstantHelper::APPROVAL_NOT_REQUIRED;
        }
        return $document_status;
    }

    public static function userCheck()
    {
        $authUser = Auth::guard('web')->user();
        if ($authUser) {
            $data = ['user_id' => Auth::guard('web')->user()->auth_user_id, 'user_type' => 'App\Models\User', 'type' => Auth::guard('web')->user()->authenticable_type];
            return $data;
        } else {
            $data = ['user_id' => Auth::guard('web2')->user()->auth_user_id, 'user_type' => 'App\Models\Employee', 'type' => Auth::guard('web2')->user()->authenticable_type];
            return $data;
        }
    }

    public static function checkCount($data)
    {
        if ($data) {
            $val = count(json_decode(json_encode($data), true));
        } else {
            $val = 0;
        }

        return $val;
    }

    public static function getCurrentFinancialYear()
    {
        $currentYear = date('Y');
        $currentMonth = date('n'); // Numeric representation of a month (1-12)

        if ($currentMonth >= 4) { // From April (4) to December (12)
            $startDate = "{$currentYear}-04-01"; // Start date is April 1st
            $endDate = ($currentYear + 1) . "-03-31"; // End date is March 31st of the next year
        } else { // From January (1) to March (3)
            $startDate = ($currentYear - 1) . "-04-01"; // Start date is April 1st of the previous year
            $endDate = "{$currentYear}-03-31"; // End date is March 31st of the current year
        }

        return [
            'start' => $startDate,
            'end' => $endDate,
        ];
    }

    public static function getGroupsData($groups, $startDate, $endDate, $organizations, $currency = 'org',$cost=null)
    {
        $fy = self::getFinancialYear($startDate);
                    
        foreach ($groups as $master) {
            $allChildIds = $master->getAllChildIds();
            $allChildIds[] = $master->id;
            $non_carry = Helper::getNonCarryGroups();
            if(in_array($master->id,$non_carry))
            $carry=0;
            else 
            $carry=1;
            
          
            $ledgers = Ledger::where(function ($query) use ($allChildIds) {
                $query->whereIn('ledger_group_id', $allChildIds)
                    ->orWhere(function ($subQuery) use ($allChildIds) {
                        foreach ($allChildIds as $child) {
                            $subQuery->orWhereJsonContains('ledger_group_id',(string)$child);
                        }
                    });
            })->whereIn('organization_id', $organizations)
                ->pluck('id')->toArray();
               
           
                $transactions = ItemDetail::whereIn('ledger_parent_id', $allChildIds)
                ->when($cost, function ($query) use ($cost) {
                    $query->where('cost_center_id', $cost);
                })
                ->whereIn('ledger_id', $ledgers)
                ->whereHas('voucher', function ($query) use ($organizations,$startDate,$endDate) {
                    $query->whereBetween('document_date', [$startDate, $endDate]);
                    $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);
                    $query->whereIn('organization_id', $organizations);

                    
                })
                ->get(); // Fetch results before summing

              
            
            
          $creditField = "credit_amt_{$currency}";
          $debitField = "debit_amt_{$currency}";
            $totalMasterCredit = $transactions->sum(fn($t) => self::removeCommas($t->$creditField));
            $totalMasterDebit = $transactions->sum(fn($t) => self::removeCommas($t->$debitField));
           
            
            // Get last closing of all ledgers for the master group
            // $lastClosingMaster = $transactions->pluck('closing')->last() ?? 0;
                
        
                $openingData = ItemDetail::whereIn('ledger_parent_id', $allChildIds)
                ->when($cost, function ($query) use ($cost) {
                    $query->where('cost_center_id', $cost);
                })
                ->whereIn('ledger_id',$ledgers)
                ->whereHas('voucher', function ($query) use($organizations,$startDate,$endDate,$fy,$carry,$cost) {
                    $query->where('document_date', '<', $startDate);
                    if(!$carry)
                    $query->where('document_date', '>=', $fy['start_date']);
                    $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
                    $query->whereIn('organization_id', $organizations);
                })
                ->selectRaw("SUM(debit_amt_{$currency}) as total_debit, SUM(credit_amt_{$currency}) as total_credit")
                ->first();
            
                $opening = $openingData->total_debit - $openingData->total_credit ?? 0;
                $opening_type = ($openingData->total_debit > $openingData->total_credit) ? 'Dr' : 'Cr';
                $open = $openingData->total_debit - $openingData->total_credit;
                $closingText = '';
                $closing = $open + $totalMasterDebit - $totalMasterCredit;
                if ($closing != 0) {
                    $closingText = $closing > 0 ? 'Dr' : 'Cr';
                }
                


            // Adding calculated totals to master group
            $master->group_id = $master->id;
            $master->total_credit = $totalMasterCredit;
            $master->total_debit = $totalMasterDebit;
            $master->opening = abs($opening);
            $master->open = $opening;
            $master->opening_type = $opening_type;
            $master->closing = $closing < 0 ? abs($closing):$closing;
            $master->closing_type = $closing > 0 ? 'Dr':'Cr';

            unset($master->children);
        }

        return $groups;
    }

    public static function getBalanceSheetData($groups, $startDate, $endDate, $organizations, $type, $currency = "org",$cost=null)
    {
        foreach ($groups as $master) {
            $allChildIds = $master->getAllChildIds();
            $allChildIds[] = $master->id;

            $ledgers = Ledger::where(function ($query) use ($allChildIds) {
                $query->whereIn('ledger_group_id', $allChildIds)
                    ->orWhere(function ($subQuery) use ($allChildIds) {
                        foreach ($allChildIds as $child) {
                            $subQuery->orWhereJsonContains('ledger_group_id',(string)$child);
                        }
                    });
            })->whereIn('organization_id', $organizations)
                ->pluck('id')->toArray();


            $transactions = ItemDetail::whereIn('ledger_id', $ledgers)
           
                ->whereIn('ledger_parent_id',$allChildIds)
                ->whereHas('voucher', function ($query) use ($organizations,$startDate,$endDate) {
                    $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
                    $query->whereIn('organization_id', $organizations);
                    $query->whereBetween('document_date', [$startDate, $endDate]);
                })
                ->when($cost, function ($query) use ($cost) {
                    $query->where('cost_center_id', $cost);
                })->get();
           
            // Calculate totals for the master group
        
            $creditField = "credit_amt_{$currency}";
            $debitField = "debit_amt_{$currency}";

            $totalMasterCredit = $transactions->sum(fn($t) => self::removeCommas($t->$creditField));
            $totalMasterDebit = $transactions->sum(fn($t) => self::removeCommas($t->$debitField));

            $openingData =  ItemDetail::whereIn('ledger_id', $ledgers)
            ->when($cost, function ($query) use ($cost) {
                $query->where('cost_center_id', $cost);
            })
            ->whereIn('ledger_parent_id',$allChildIds)
                ->whereHas('voucher', function ($query) use($organizations,$startDate) {
                    $query->where('document_date', '<', $startDate);
                    $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
                    $query->whereIn('organization_id', $organizations);
                })
                ->selectRaw("SUM(debit_amt_{$currency}) as total_debit, SUM(credit_amt_{$currency}) as total_credit")
                ->first();
            
                $openingt = $openingData->total_debit - $openingData->total_credit ?? 0;
                $opening =  $openingData->total_credit - $openingData->total_debit ?? 0;
                




            // Adding calculated totals to master group
            if ($type == "liabilities") {
                $master->closing = ($opening) + ($totalMasterCredit - $totalMasterDebit);
            } else {
                $master->closing = ($openingt) + ($totalMasterDebit - $totalMasterCredit);
            }

            $master->closingType = '';
            if ($master->closing != 0) {
                $master->closingType = $master->closing > 0 ? 'Dr' : 'Cr';
            }

            unset($master->children);
        }

        return $groups;
    }
    public static function convertDateRangeToIndianFormat($startDate, $endDate)
    {
        // Convert start date and end date to DateTime objects
        $start = \DateTime::createFromFormat('Y-m-d', $startDate);
        $end = \DateTime::createFromFormat('Y-m-d', $endDate);

        // Check if the conversion was successful
        if (!$start || !$end) {
            throw new \InvalidArgumentException('Invalid date format. Please use YYYY-MM-DD.');
        }

        // Format the dates to DD-MM-YYYY
        return [
            'start' => $start->format('j-M-Y'),
            'end' => $end->format('j-M-Y')
        ];
    }

    public static function formatIndianNumber($number)
    {
        // Remove any existing commas from the number
        $number = str_replace(',', '', $number);

        // Ensure the number is a float
        $number = floatval($number);

        // Convert to a string and round to two decimal places
        $number = number_format($number, 2, '.', ''); // Ensures two decimals, even if '00'

        // Split the whole part and decimal part
        $parts = explode('.', $number);
        $wholePart = $parts[0];
        $decimalPart = isset($parts[1]) ? $parts[1] : '00'; // Default to '00' if no decimal part

        // Regular expression to match the Indian format
        $lastThreeDigits = substr($wholePart, -3);
        $restOfTheNumber = substr($wholePart, 0, strlen($wholePart) - 3);

        if ($restOfTheNumber !== '') {
            // Apply Indian numbering system format: commas after every 2 digits
            $wholePart = preg_replace('/(\d)(?=(\d{2})+(?!\d))/', '$1,', $restOfTheNumber) . ',' . $lastThreeDigits;
        } else {
            // If there's no part left after the last 3 digits, keep the last 3 digits as is
            $wholePart = $lastThreeDigits;
        }

        // Return whole part with decimal part (ensures two decimal places)
        return $wholePart . '.' . $decimalPart; // Always show two decimal places
    }


     public static function removeCommas($input)
    {
        // Remove commas from the input using str_replace
        // return str_replace(',', '', $input);
        // Ensure the input is a string before applying str_replace
        if (is_string($input)) {
            return str_replace(',', '', $input);
        }

        // Return the input as-is if it's not a string
        return $input;
    }

    public static function getLedgerData($ledger_id, $startDate, $endDate, $companyId, $organization_id, $ledger_parent,$currency = "org",$cost=null)
    {

            $itemVouchers = ItemDetail::where('ledger_id', $ledger_id)
            ->when($cost, function ($query) use ($cost) {
                $query->where('cost_center_id', $cost);
            })
        ->where('ledger_parent_id',$ledger_parent)
        ->whereHas('voucher', function ($query) use ($organization_id,$startDate,$endDate){
            $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
            $query->where('organization_id', $organization_id);
            $query->whereBetween('document_date', [$startDate, $endDate]);

        })->pluck('voucher_id')
            ->toArray();




        $data = Voucher::whereIn('id', $itemVouchers)
            ->where('organization_id', $organization_id)
            ->whereNotNull('approvalStatus')
            ->whereBetween('document_date', [$startDate, $endDate])
            ->with([
                'items' => function ($it) use ($currency) {
                    $it->select('id', "debit_amt_{$currency} as debit_amt", "credit_amt_{$currency} as credit_amt", 'voucher_id', 'ledger_id','ledger_parent_id')->with([
                        'ledger' => function ($l) {
                            $l->select('id', 'name');
                        }
                    ]);
                },
                'documents' => function ($query) {
                    $query->select('id', 'name');
                },
                'series'
            ])->select('id', 'voucher_no', 'voucher_name', 'document_date as date', 'book_type_id', 'book_id')
            ->orderBy('document_date', 'asc')
            ->get();



        return $data;
    }

    public static function getBalanceSheetLedgers($group_id, $startDate, $endDate, $organizations, $currency = "org",$cost=null)
    {

        $liabilities_group = Group::where('name', 'Liabilities')
    ->where(function ($query) use ($organizations) {
        $query->whereIn('organization_id', $organizations)
              ->orWhereNull('organization_id');
    })
    ->value('id');

    $assets_group = Group::where('name', 'Assets')
        ->where(function ($query) use ($organizations) {
            $query->whereIn('organization_id', $organizations)
                ->orWhereNull('organization_id');
        })
        ->value('id');

        $liabilities = Group::where('parent_group_id', $liabilities_group)->pluck('id')->toArray();

        $type = "assets";
        if (in_array($group_id, $liabilities)) {
            $type = "liabilities";
        }
        $group = Group::find($group_id);
        $childrens = $group->getAllChildIds();
        $childrens[] = $group->id;

        $data = Ledger::where(function ($query) use ($childrens) {
            $query->whereIn('ledger_group_id', $childrens)
                ->orWhere(function ($subQuery) use ($childrens) {
                    foreach ($childrens as $child) {
                        $subQuery->orWhereJsonContains('ledger_group_id',(string)$child);
                    }
                });
        })->whereIn('organization_id', $organizations)
            ->select('id', 'name','ledger_group_id')
            ->withSum([
                'details as details_sum_debit_amt' => function ($query) use ($startDate, $endDate,$childrens,$cost) {
                    $query->whereIn('ledger_parent_id', $childrens)
                    ->when($cost, function ($query) use ($cost) {
                        $query->where('cost_center_id', $cost);
                    })
                    ->withwhereHas('voucher', function ($query) use($startDate,$endDate) {
                        $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
                        $query->whereBetween('document_date', [$startDate, $endDate]);
                        $query->orderBy('document_date', 'asc');

                    });
                }
            ], "debit_amt_{$currency}")
            ->withSum([
                'details as details_sum_credit_amt' => function ($query) use ($startDate, $endDate,$childrens,$cost) {
                    $query->whereIn('ledger_parent_id', $childrens)
                    ->when($cost, function ($query) use ($cost) {
                        $query->where('cost_center_id', $cost);
                    })
                    ->withwhereHas('voucher', function ($query) use($startDate,$endDate) {
                        $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
                        $query->whereBetween('document_date', [$startDate, $endDate]);
                        $query->orderBy('document_date', 'asc');

                    });
                }
            ], "credit_amt_{$currency}")
            ->with([
                'details' => function ($query) use ($startDate, $endDate,$childrens,$cost) {
                    $query->whereIn('ledger_parent_id', $childrens)
                    ->when($cost, function ($query) use ($cost) {
                        $query->where('cost_center_id', $cost);
                    })
                    ->withwhereHas('voucher', function ($query) use($startDate,$endDate) {
                        $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
                        $query->whereBetween('document_date', [$startDate, $endDate]);
                        $query->orderBy('document_date', 'asc');

                    });
                }
            ])
            ->get()
            ->map(function ($ledger) use ($type,$childrens,$organizations,$startDate,$currency,$cost) {
                // Set to 0 if the sum is null
                $details_sum_debit_amt = $ledger->details_sum_debit_amt ?? 0;
                $details_sum_credit_amt = $ledger->details_sum_credit_amt ?? 0;

                $openingData =  ItemDetail::where('ledger_id',$ledger->id)
                ->when($cost, function ($query) use ($cost) {
                    $query->where('cost_center_id', $cost);
                })
                    ->whereIn('ledger_parent_id',$childrens)
                   ->whereHas('voucher', function ($query) use($organizations,$startDate) {
                        $query->where('document_date', '<', $startDate);
                        $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
                        $query->whereIn('organization_id', $organizations);
                    })
                    ->selectRaw("SUM(debit_amt_{$currency}) as total_debit, SUM(credit_amt_{$currency}) as total_credit")
                    ->first();
                
                    $openingt = $openingData->total_debit - $openingData->total_credit ?? 0;
                    $opening =  $openingData->total_credit - $openingData->total_debit ?? 0;

                

                if ($type == "liabilities") {
                    $ledger->closing = ($opening) + ($details_sum_credit_amt - $details_sum_debit_amt);
                } else {
                    $ledger->closing = ($openingt) + ($details_sum_debit_amt - $details_sum_credit_amt);
                }
                $decodedLedgerGroupId = json_decode($ledger->ledger_group_id, true);

                // If it's an array (JSON), filter it; otherwise, return the original value
                if (is_array($decodedLedgerGroupId)) {
                    $filteredValue = collect($decodedLedgerGroupId)->first(function ($item) use ($childrens) {
                        return in_array($item, $childrens);
                    });

                    $ledger->ledger_group_id = (int)$filteredValue ?? null; // Use found value or null if no match
                }


                unset($ledger->details);

                return $ledger;
            });






        return $data;
    }

    public static function actionButtonDisplay($bookId, $docStatus, $docId, $docValue, $docApprLevel, int $createdBy = 0, $creatorType = 'user', $revisionNumber = 0)
    {
        $draft = false;
        $submit = false;
        $approve = false;
        $amend = false;
        $post = false;
        $voucher = false;
        $revoke = false;
        $close = false;
        $user = self::getAuthenticatedUser();
        $print = false;
        $book = Book::where('id', $bookId)->first();
        $bookTypeServiceAlias = $book?->service?->alias;
        $currUser = Helper::userCheck();

        if ($docStatus == ConstantHelper::DRAFT || $docStatus == ConstantHelper::REJECTED) {
            $draft = true;
            $submit = true;
        }
        if ($docStatus == ConstantHelper::SUBMITTED) {
            $approvalWorkflow = BookLevel::where('book_id', $book->id)
                ->where('organization_id', $user->organization_id)
                ->where('level', 1)
                ->where('min_value', '<=', $docValue)
                ->whereHas('approvers', function ($approver) use ($currUser) {
                    $approver->where('user_id', $currUser['user_id']);
                        // ->where('user_type', $currUser['type']);
                })
                ->orderByDesc('min_value')
                ->first();

            if ($approvalWorkflow) {
                $approve = true;
            }
            //Creator of document cannot approve
            // if ($user->auth_user_id === $createdBy && self::userCheck()['type'] == $creatorType) {
            if ($user->auth_user_id === $createdBy) {
                $approve = false;
                $revoke = true;
            }
        }
        if ($docStatus == ConstantHelper::APPROVED || $docStatus == ConstantHelper::APPROVAL_NOT_REQUIRED) {
            $amendmentWorkflow = AmendmentWorkflow::where('book_id', $book->id)
                ->where('organization_id', $user->organization_id)
                ->whereHas('approvers', function ($user) use ($currUser) {
                    $user->where('user_id', $currUser['user_id']);
                        // ->where('user_type', $currUser['type']);
                })
                ->where('min_value', '<=', $docValue)
                ->orderByDesc('min_value')
                ->first();
            if ($amendmentWorkflow) {
                $amend = true;
            }

            if($bookTypeServiceAlias == ConstantHelper::MO_SERVICE_ALIAS ) {
                $close = true;
            } else {
                $postingRequiredParam = OrganizationBookParameter::where('book_id', $bookId)
                ->where('parameter_name', ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM)->first();
                if (isset($postingRequiredParam)) {
                    $isPostingRequired = ($postingRequiredParam->parameter_value[0] ?? '') === "yes" ? true : false;
                    $post = $isPostingRequired;
                }
            }
            $print = true;
        }
        if ($docStatus == ConstantHelper::PARTIALLY_APPROVED) {
            $approvalWorkflow = BookLevel::where('book_id', $book->id)
                ->where('organization_id', $user->organization_id)
                ->where('min_value', '<=', $docValue)
                ->whereHas('approvers', function ($approver) use ($currUser) {
                    $approver->where('user_id', $currUser['user_id']);
                        // ->where('user_type', $currUser['type']);
                })
                ->orderByDesc('min_value')
                ->first();
            if ($approvalWorkflow) {
                // $docApproval = DocumentApproval::where('document_type', '=', "$bookTypeServiceAlias")
                //                 ->where('document_id', $docId)
                //                 ->where('user_id', $user->id)
                //                 ->where('user_type', $currUser['type'])
                //                 ->where('revision_number', $revisionNumber)
                //                 ->where('approval_type', 'approve')
                //                 ->first();
                $checkApproved = self::checkApprovedHistory($bookTypeServiceAlias, $docId, $revisionNumber, [$user->auth_user_id]);

                if (!count($checkApproved)) {
                    if ($approvalWorkflow->level == $docApprLevel) {
                        $approve = true;
                    }
                }
            }
        }

        if ($docStatus == ConstantHelper::CLOSED) {
            if ($bookTypeServiceAlias == ConstantHelper::MO_SERVICE_ALIAS) {
                $postingRequiredParam = OrganizationBookParameter::where('book_id', $bookId)
                ->where('parameter_name', ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM)->first();
                if (isset($postingRequiredParam)) {
                    $isPostingRequired = ($postingRequiredParam->parameter_value[0] ?? '') === "yes" ? true : false;
                    $post = $isPostingRequired;
                }
            }
            $print = true;
        }

        if ($docStatus == ConstantHelper::POSTED) {
            $voucher = true;
            $print = true;
        }

        return [
            'draft' => $draft,
            'submit' => $submit,
            'approve' => $approve,
            'amend' => $amend,
            'post' => $post,
            'voucher' => $voucher,
            'revoke' => $revoke,
            'close' => $close,
            'print' => $print
        ];
    }

    public static function actionButtonDisplayForLegal($bookId, $docStatus, $docId, $docApprLevel, int $createdBy = 0, $creatorType = 'user', $revisionNumber = 0)
    {
        $draft = false;
        $submit = false;
        $approve = false;
        $reject = false;
        $assign = false;
        $close = false;
        $view = false;
        $edit = false;
        $email = true;



        $user = self::getAuthenticatedUser();
        $userCheck = self::userCheck();
        $book = Book::where('id', $bookId)->first();
        $bookTypeServiceAlias = $book?->service?->alias;
        $currUser = Helper::userCheck();

        if ($docStatus == ConstantHelper::DRAFT || $docStatus == ConstantHelper::REJECTED) {
            $draft = true;
            $submit = true;
            $edit = true;
        }
        if ($docStatus == ConstantHelper::SUBMITTED) {
            $approvalWorkflow = BookLevel::where('book_id', $book->id)
                ->where('organization_id', $user->organization_id)
                ->where('level', 1)
                ->whereHas('approvers', function ($approver) use ($currUser) {
                    $approver->where('user_id', $currUser['user_id']);
                        // ->where('user_type', $currUser['type']);
                })
                ->orderByDesc('min_value')
                ->first();

            if ($approvalWorkflow) {
                $approve = true;
                $reject = true;
            }
            $view = true;
            $email = false;
        }
        if ($docStatus == ConstantHelper::APPROVED || $docStatus == ConstantHelper::APPROVAL_NOT_REQUIRED) {

            $approvalWorkflow = BookLevel::where('book_id', $book->id)
                ->where('organization_id', $user->organization_id)
                ->where('level', 1)
                ->whereHas('approvers', function ($approver) use ($currUser) {
                    $approver->where('user_id', $currUser['user_id']);
                        // ->where('user_type', $currUser['type']);
                })
                ->orderByDesc('min_value')
                ->first();

            if ($approvalWorkflow) {
                $reject = true;
            }

            $assign = true;
            $view = true;
        }
        if ($docStatus == ConstantHelper::PARTIALLY_APPROVED) {
            $approvalWorkflow = BookLevel::where('book_id', $book->id)
            ->where('organization_id', $user->organization_id)
            ->whereHas('approvers', function ($approver) use ($currUser) {
                $approver->where('user_id', $currUser['user_id']);
                    // ->where('user_type', $currUser['type']);
            })
            ->orderByDesc('min_value')
            ->first();
        if ($approvalWorkflow) {
            // $docApproval = DocumentApproval::where('document_type', '=', "$bookTypeServiceAlias")
            //                 ->where('document_id', $docId)
            //                 ->where('user_id', $user->id)
            //                 ->where('user_type', $currUser['type'])
            //                 ->where('revision_number', $revisionNumber)
            //                 ->where('approval_type', 'approve')
            //                 ->first();
            $checkApproved = self::checkApprovedHistory($bookTypeServiceAlias, $docId, $revisionNumber, [$user->id]);

            if (!count($checkApproved)) {
                if ($approvalWorkflow->level == $docApprLevel) {
                    $approve = true;
                    $reject=true;
                }
            }
        }

        $view = true;
        $email = false;

    }
        if ($docStatus == ConstantHelper::ASSIGNED) {
            $approvalWorkflow = BookLevel::where('book_id', $book->id)
                ->where('organization_id', $user->organization_id)
                ->where('level', 1)
                ->whereHas('approvers', function ($approver) use ($currUser) {
                    $approver->where('user_id', $currUser['user_id']);
                        // ->where('user_type', $currUser['type']);
                })
                ->orderByDesc('min_value')
                ->first();

            if ($approvalWorkflow) {
                $close = true;
            }
            $view = true;
            $close = true;
        }
        if ($docStatus == ConstantHelper::CLOSE) {
            $email = false;
            $view = true;
        }


         if ($userCheck['user_id'] === $createdBy &&  $userCheck['type']=== $creatorType ) {
            $approve = false;
        }

        return [
            'draft' => $draft,
            'submit' => $submit,
            'approve' => $approve,
            'reject' => $reject,
            'assign' => $assign,
            'close' => $close,
            'view' => $view,
            'email' => $email,
            'edit' => $edit,
        ];
    }
    public static function actionButtonDisplayForLoan($bookId, $docStatus = null, $docId = null, $docValue = null, $docApprLevel = 1, int $createdBy = 0, $creatorType = null, $revisionNumber = null)
    {
        $buttons = [
            'save_draft' => false,
            'delete' => false,
            'proceed' => false,
            'back' => false,
            'submit' => false,
            'return' => false,
            'update_appraisal' => false,
            'reject' => false,
            'approve' => false,
            'accept' => false,
            'fee_paid' => false,
            'legal_doc' => false,
            'post' => false,
            'voucher'=> false
        ];

        $user = self::getAuthenticatedUser();
        $userAuth = Helper::userCheck();
       $book = Book::findOrFail($bookId);

        $bookTypeServiceAlias = $book?->service?->alias;
        $currUser = Helper::userCheck();

        $disbursment = LoanDisbursement::where('home_loan_id', $docId)->get();
        $recovery = RecoveryLoan::where('home_loan_id', $docId)->get();

        // Handle Draft and Rejected Status
        if ($docStatus == ConstantHelper::DRAFT || $docStatus == ConstantHelper::REJECTED) {
            if ($disbursment->count() > 0) {
                $buttons['approve'] = true;
                $buttons['reject'] = true;
            } else if ($recovery->count() > 0) {
                $buttons['approve'] = true;
                $buttons['reject'] = true;
            } else {
                $buttons['save_draft'] = true;
                $buttons['delete'] = true;
                $buttons['proceed'] = true;
            }
        }

        // Handle Submitted Status
        if ($docStatus == ConstantHelper::SUBMITTED) {
            if ($disbursment->count() > 0) {
                $buttons['approve'] = true;
                $buttons['reject'] = true;
            } elseif ($recovery->count() > 0) {
                $buttons['approve'] = true;
                $buttons['reject'] = true;
            } else {
                $buttons['back'] = true;
                $buttons['return'] = true;
                $buttons['update_appraisal'] = true;
            }
        }

        // Handle Appraisal Status
        if ($docStatus == ConstantHelper::APPRAISAL) {
            $buttons['return'] = true;
            $buttons['reject'] = true;
            $buttons['proceed'] = true;
        }

        // Handle Request Status
        if ($docStatus == ConstantHelper::REQUEST) {
            $buttons['reject'] = true;
            $buttons['proceed'] = true;
        }

        // Handle Approval Status
        if ($docStatus == ConstantHelper::APPROVED || $docStatus == ConstantHelper::APPROVAL_NOT_REQUIRED) {
            $postingRequiredParam = OrganizationBookParameter::where('book_id', $bookId)
            ->where('parameter_name', ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM)->first();

            $postingRequiredParam2 = OrganizationBookParameter::where('book_id', $bookId)
            ->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)->first();

            if (isset($postingRequiredParam) && isset($postingRequiredParam2->parameter_value[0])) {
                $isPostingRequired = ($postingRequiredParam->parameter_value[0] ?? '') === "yes" ? true : false;
                $buttons['post'] = $isPostingRequired;
            }

            if ($disbursment->count() > 0 || $recovery->count() > 0) {
                $buttons['reject'] = true;
                $buttons['return'] = true;
                $buttons['submit'] = true;
            } else {
                $buttons['return'] = true;
                $buttons['reject'] = true;
                $buttons['accept'] = true;
            }
        }

        // Sanctioned
        if ($docStatus == ConstantHelper::SANCTIONED) {
            $buttons['return'] = true;
            $buttons['reject'] = true;
            $buttons['fee_paid'] = true;
        }


        if ($docStatus == ConstantHelper::POSTED) {
            $buttons['voucher'] = true;
        }


        // Processing Fee
        if ($docStatus == ConstantHelper::PROCESSING_FEE) {
            $buttons['return'] = true;
            $buttons['reject'] = true;
            $buttons['legal_doc'] = true;
        }

        // Handle Assessment Status
        if ($docStatus == ConstantHelper::ASSESSMENT || $docStatus == ConstantHelper::ASSESSED) {
            $approvalWorkflow = BookLevel::where('book_id', $book->id)
                ->where('organization_id', $user->organization_id)
                ->where('level', 1)
                ->where('min_value', '<=', $docValue)
                ->whereHas('approvers', function ($approver) use ($currUser) {
                    $approver->where('user_id', $currUser['user_id']);
                        // ->where('user_type', $currUser['type']);
                })
                ->orderByDesc('min_value')
                ->first();

            if ($approvalWorkflow && $disbursment->count() == 0 && $recovery->count() == 0) {
                $buttons['approve'] = true;
                $buttons['return'] = true;
                $buttons['reject'] = true;
            } elseif ($disbursment->count() > 0 && $approvalWorkflow) {
                $buttons['approve'] = true;
                $buttons['reject'] = true;
            } else {
                $buttons['approve'] = true;
                $buttons['return'] = true;
                $buttons['reject'] = true;
            }
        }

        if ($docStatus == ConstantHelper::PARTIALLY_APPROVED) {
            if ($disbursment->count() > 0 || $recovery->count() > 0) {
                $buttons['return'] = true;
            }
            $buttons['reject'] = true;
            $approvalWorkflow = BookLevel::where('book_id', $book->id)
                ->where('organization_id', $user->organization_id)
                ->where('min_value', '<=', $docValue)
                ->whereHas('approvers', function ($approver) use ($currUser) {
                    $approver->where('user_id', $currUser['user_id']);
                        // ->where('user_type', $currUser['type']);
                })
                ->orderByDesc('min_value')
                ->first();
            if ($approvalWorkflow) {
                $checkApproved = self::checkApprovedHistory($bookTypeServiceAlias, $docId, $revisionNumber, [$user->id]);

                if (!count($checkApproved)) {
                    if ($approvalWorkflow->level == $docApprLevel) {
                        $buttons['approve'] = true;
                    }
                }
            }
        }

        // Creator of document cannot approve
        // if ($user->id === $createdBy && self::userCheck()['type'] == $creatorType) {
        if ($user->auth_user_id === $createdBy ) {
            $buttons['approve'] = false;
        }

        return $buttons;
    }

    public static function approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue = 0, $modelName = null, $documentType = NULL)
    {
        $user = self::getAuthenticatedUser();
        $book = Book::where('id', $bookId)->first();
        $bookTypeServiceAlias = $book?->service->alias;
        $docApproval = new DocumentApproval;
        $docApproval->document_type = $bookTypeServiceAlias;
        $docApproval->document_id = $docId;
        $docApproval->document_name = $modelName;
        $docApproval->approval_type = $actionType ?? null;
        $docApproval->approval_date = now();
        $docApproval->revision_number = $revisionNumber;
        $docApproval->remarks = $remarks;
        $docApproval->user_id = $user->auth_user_id;
        // $user_type = null;
        // if (Auth::guard('web')->check()) {
        //     $user_type = 'user';
        // }
        // if (Auth::guard('web2')->check()) {
        //     $user_type = 'employee';
        // }
        $docApproval->user_type = $user -> authenticable_type;
        $docApproval->save();

        # Save attachment file
        if ($attachments) {
            $mediaFiles = $docApproval->uploadDocuments($attachments, 'approval_document', false);
        }

        $approvalStatus = null;
        $nextLevel = 0;
        $message = '';
        $createdBy = null;
        $document = null;
        if ($modelName) {
            $model = resolve($modelName);
            $document = $model::find($docId);
            $createdBy = $document ?-> created_by;
            if (isset($document) && isset($document -> document_status)) { //Document Exists
                if ($actionType == ConstantHelper::REVOKE && $document -> document_status != ConstantHelper::SUBMITTED) {
                    $message = "Can't Revoke. Document is already Approved/Rejected";
                }
                if (($actionType == "approve" || $actionType == "reject") && $document -> document_status == ConstantHelper::DRAFT) {
                    $message = "Can't Approve/Reject. Document has been revoked";
                }
            }
        }
        //Return error message if set
        if ($message) {
            return [
                'approvalStatus' => $approvalStatus,
                'nextLevel' => $nextLevel,
                'message' => $message
            ];
        }

        if ($actionType == 'approve') {
            $approvalWorkflows = ApprovalWorkflow::where('book_id', $book->id)
                ->where('organization_id', $user->organization_id)
                ->whereHas('level', function ($level) use ($currentLevel) {
                    $level->where('level', $currentLevel);
                }) -> where('user_id', '!=', $createdBy)
                ->get();
            $rights = isset($approvalWorkflows[0]) ? $approvalWorkflows[0]->bookLevel?->rights : '';
            if ($rights == 'all') {
                foreach ($approvalWorkflows as $approvalWorkflow) {

                    $checkApproved = self::checkApprovedHistory($bookTypeServiceAlias, $docId, $revisionNumber, [$approvalWorkflow->user_id]);

                    // $checkApproved = DocumentApproval::where('document_type', '=', "$bookTypeServiceAlias")
                    //                  ->where('document_id', $docId)
                    //                  ->where('user_id', $approvalWorkflow->user_id)
                    //                  ->where('user_type', $approvalWorkflow->user_type)
                    //                  ->where('revision_number', $revisionNumber)
                    //                  ->where('approval_type', 'approve')
                    //                  ->first();
                    if (!$checkApproved) {
                        $nextLevel = $currentLevel;
                        $approvalStatus = ConstantHelper::PARTIALLY_APPROVED;
                        break;
                    }
                }
            }

            if ($nextLevel < 1) {
                // $checNextLevel = ApprovalWorkflow::where('book_id', $book->id)
                //             ->where('organization_id', $user->organization_id)
                //             ->whereHas('level', function($level) use ($currentLevel,$docValue) {
                //                $level->where('level', $currentLevel + 1);
                //             })
                //             ->first();
                $checNextLevel = BookLevel::where('book_id', $book->id)
                    ->where('organization_id', $user->organization_id)
                    ->where('level', '>', $currentLevel)
                    ->where('min_value', '<=', $docValue)
                    ->orderBy('level')
                    ->first();
                if ($checNextLevel) {
                    $nextLevel = $checNextLevel->level;
                    $approvalStatus = ConstantHelper::PARTIALLY_APPROVED;
                } else {
                    $nextLevel = $currentLevel;
                    $approvalStatus = ConstantHelper::APPROVED;
                    if ($bookTypeServiceAlias != ConstantHelper::MO_SERVICE_ALIAS){
                        $nextLevel = $currentLevel;
                        $approvalStatus = ConstantHelper::APPROVED;
                        $postData = FinancialPostingHelper::financeVoucherPosting($book->id, $docId, 'post', true);
                        if (isset($postData['status']) && $postData['status']) {
                            $approvalStatus = ConstantHelper::POSTED;
                        } else {
                            $message = $postData['message'];
                        }
                    }
                }
            }
        }

        if ($actionType == 'reject') {
            $nextLevel = $currentLevel;
            $approvalStatus = ConstantHelper::REJECTED;
        }
        if ($actionType == 'completed' || $actionType == 'auto-completed') {
            if($bookTypeServiceAlias==ConstantHelper::TR_SERVICE_ALIAS){
                $nextLevel = $currentLevel;
                $approvalStatus = ConstantHelper::COMPLETED;
            }
        }
        if ($actionType == 'auto-closed' || $actionType == 'closed') {
            if($bookTypeServiceAlias==ConstantHelper::TR_SERVICE_ALIAS){
                $nextLevel = $currentLevel;
                $approvalStatus = ConstantHelper::CLOSED;
            }
        }
        if ($actionType == 'shortlist') {
            if($bookTypeServiceAlias==ConstantHelper::TR_SERVICE_ALIAS){
                $nextLevel = $currentLevel;
                $approvalStatus = ConstantHelper::SHORTLISTED;
            }
        }
        // if ($actionType == 'submitted') {
        //     $nextLevel = $currentLevel;
        //     $approvalStatus = ConstantHelper::SUBMITTED;
        // }

        if ($actionType == 'submit') {
            $approvalWorkflow = ApprovalWorkflow::where('book_id', $book->id)
            ->where('organization_id', $user->organization_id)
            ->whereHas('level', function ($level) use ($currentLevel) {
                $level->where('level', $currentLevel);
            }) -> where('user_id', '!=', $createdBy)
            ->get();
            if (count($approvalWorkflow) == 0) {
                $approvalStatus = ConstantHelper::APPROVAL_NOT_REQUIRED;
            } else {
            $approvalStatus = self::checkApprovalRequired($book -> id, $docValue);
            }
            if ($approvalStatus === ConstantHelper::APPROVAL_NOT_REQUIRED) {
                if ($bookTypeServiceAlias != ConstantHelper::MO_SERVICE_ALIAS){
                    //Finance posting
                    $postData = FinancialPostingHelper::financeVoucherPosting($book->id, $docId, 'post', true);
                    if (isset($postData['status']) && $postData['status']) {
                        $approvalStatus = ConstantHelper::POSTED;
                    } else {
                        $message = $postData['message'];
                    }
                }
            }
        }

        if ($actionType == 'amendment') {
            $approvalRequired = Helper::checkAfterAmendApprovalRequired($bookId);
            //Approval is required after amendment
            if (isset($approvalRequired->approval_required) && $approvalRequired->approval_required) {
                $approvalStatus = ConstantHelper::SUBMITTED;
            }
        }

        if ($actionType == ConstantHelper::REVOKE) {
            $approvalStatus = ConstantHelper::DRAFT;
        }

        if ($actionType != 'submit') {
            InventoryHelper::updateInventoryAndStock($docId, $bookTypeServiceAlias, $approvalStatus);
        }

        # When document close
        if ($actionType == 'close') {
            if ($bookTypeServiceAlias = ConstantHelper::MO_SERVICE_ALIAS){
                $approvalStatus = ConstantHelper::CLOSED;
                $postData = FinancialPostingHelper::financeVoucherPosting($book->id, $docId, 'post', true);
                if (isset($postData['status']) && $postData['status']) {
                    $approvalStatus = ConstantHelper::POSTED;
                } else {
                    $message = $postData['message'];
                }
            }
        }

        return [
            'approvalStatus' => $approvalStatus,
            'nextLevel' => $nextLevel,
            'message' => $message
        ];
    }

    public static function getApprovalHistory($bookId, $docId, $revisionNumber, $docValue = 0, $createdBy = 0)
    {
        $user = self::getAuthenticatedUser();
        $book = Book::where('id', $bookId)->first();
        $bookTypeServiceAlias = $book?->service?->alias;

        $docApproval = DocumentApproval::where('document_type', '=', "$bookTypeServiceAlias")
            ->where('document_id', $docId)
            ->where('revision_number', $revisionNumber)
            ->latest()
            ->first();
        $document_status = '';
        if ($docApproval?->document_name) {
            $document_status = $docApproval?->document?->document_status;
        }
        $data = collect();
        if ($document_status != ConstantHelper::APPROVED) {
            $approvalArr = [];
            $uniqueLevels = BookLevel::where('book_id', $book->id)
                ->where('organization_id', $user->organization_id)
                ->orderBy('level', 'DESC')
                ->distinct()
                ->pluck('level')
                ->toArray();

            $bookLevels = [];

            foreach ($uniqueLevels as $level) {
                $bookLevel = BookLevel::where('book_id', $book->id)
                    ->where('organization_id', $user->organization_id)
                    ->where('level', $level)
                    ->where('min_value', '<=', $docValue)
                    ->orderBy('min_value', 'DESC')
                    ->first();
                if ($bookLevel) {
                    $bookLevels[] = $bookLevel;
                }
            }

            foreach ($bookLevels as $bookLevel) {
                if ($bookLevel->rights == 'all') {
                    $levelUserIds = ApprovalWorkflow::where('book_level_id', $bookLevel->id)
                        ->whereNotIn('user_id', [$createdBy])
                        ->pluck('user_id')
                        ->toArray();
                    $checkCount = self::checkApprovedHistory($bookTypeServiceAlias, $docId, $revisionNumber, $levelUserIds);
                    $remainingUser = array_diff($levelUserIds, $checkCount);
                    $remainingUser = implode(',', $remainingUser);
                    if ($remainingUser) {
                        array_push($approvalArr, $remainingUser);
                    }
                } else {
                    $levelUserIds = ApprovalWorkflow::where('book_level_id', $bookLevel->id)
                        ->whereNotIn('user_id', [$createdBy])
                        ->pluck("user_id")
                        ->toArray();
                    $checkCount = self::checkApprovedHistory($bookTypeServiceAlias, $docId, $revisionNumber, $levelUserIds);

                    if (!count($checkCount)) {
                        $levelUserIds = implode('|', $levelUserIds);
                        if ($levelUserIds) {
                            array_push($approvalArr, $levelUserIds);
                        }
                    }
                }
            }
            # name , remark, date, status
            foreach ($approvalArr as $approvalAr) {
                $userId = $approvalAr;
                $authUser = AuthUser::find($approvalAr);
                $userName = null;
                if (str_contains($approvalAr, ',')) {
                    $userId = explode(',', $approvalAr);
                    $userName = AuthUser::whereIn('id', $userId)->pluck('name')->implode(',');
                }
                if (str_contains($approvalAr, '|')) {
                    $userId = explode('|', $approvalAr);
                    $userName = AuthUser::whereIn('id', $userId)->pluck('name')->implode('|');
                }
                if (!$userName) {
                    if (!is_array($userId)) {
                        $userId = [$userId];
                    }
                    $userName = AuthUser::whereIn('id', [$userId])->first()?->name;
                }
                $custom = new DocumentApproval;
                $custom->name = $userName ?? null;
                $custom->user_id = $approvalAr;
                $custom->approval_date = null;
                $custom->remarks = null;
                $custom->approval_type = 'pending';
                $custom->user_type = $authUser ?-> user_type;

                $custom->setRawAttributes([
                    'user_id' => $approvalAr,
                    'name' => $userName ?? null,
                    'approval_date' => null,
                    'remarks' => null,
                    'approval_type' => 'pending',
                    'user_type' => $authUser ?-> user_type
                ], true);

                $data[] = $custom;
            }
        }

        $history = DocumentApproval::select('id', 'user_id', 'approval_date', 'remarks', 'approval_type', 'user_type')
            ->where('document_type', '=', $bookTypeServiceAlias)
            ->where('document_id', $docId)
            ->where('revision_number', $revisionNumber);
        $history = $history->orderByDesc('id')->get();
        $mergedCollection = $data->merge($history);
        return $mergedCollection;
    }

    public static function checkApprovedHistory($bookTypeServiceAlias, $docId, $revisionNumber, $userId = [])
    {
        $lastReject = DocumentApproval::where('document_type', '=', "$bookTypeServiceAlias")
            ->where('document_id', $docId)
            ->where('revision_number', $revisionNumber)
            ->where('approval_type', 'reject')
            ->latest()
            ->first();
        $lastRejectId = $lastReject->id ?? 0;

        $history = DocumentApproval::where('document_type', '=', "$bookTypeServiceAlias")
            ->where('document_id', $docId)
            ->where('revision_number', $revisionNumber)
            ->whereIn('user_id', $userId)
            ->where('approval_type', 'approve')
            ->where('id', '>', $lastRejectId)
            ->pluck('user_id')
            ->toArray();
        return $history;
    }

    public static function getTrialBalanceGroupLedgers($group_id, $startDate, $endDate, $organizations, $currency = "org",$cost=null)
    {
        $non_carry = Helper::getNonCarryGroups();
        if(in_array($group_id,$non_carry))
        $carry=0;
        else 
        $carry=1;
    
        $fy = self::getFinancialYear($startDate);
                    
        $groups = Group::where('parent_group_id',$group_id)->pluck('id');

        $datas = Group::whereIn('id',$groups)->get();

    
   

        if (self::checkCount($datas) > 0) {
            $type = 'group';
            $data = self::getGroupsData($datas, $startDate, $endDate, $organizations, $currency,$cost);
        } else {
            $type = 'ledger';
            $data = Ledger::where(function ($query) use ($group_id) {
                $query->whereJsonContains('ledger_group_id', (string) $group_id)
                      ->orWhere('ledger_group_id', $group_id);
            })
            ->whereIn('organization_id', $organizations)
            ->select('id', 'name')
            ->with([
                'details' => function ($query) use ($startDate, $endDate, $group_id,$cost) {
                    $query->where('ledger_parent_id', $group_id)
                    ->when($cost, function ($query) use ($cost) {
                        $query->where('cost_center_id', $cost);
                    })
                          ->withwhereHas('voucher', function ($query) use($startDate,$endDate) {
                              $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
                              $query->orderBy('document_date', 'asc');
                             $query->whereBetween('document_date', [$startDate, $endDate]);
                          
                          });
                }
            ])->withSum([
                'details as details_sum_debit_amt' => function ($query) use ($startDate, $endDate, $group_id,$cost) {
                    $query->where('ledger_parent_id', $group_id)
                    ->when($cost, function ($query) use ($cost) {
                        $query->where('cost_center_id', $cost);
                    })
                    ->withwhereHas('voucher', function ($query) use($startDate,$endDate) {
                        $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
                        $query->orderBy('document_date', 'asc');
                       $query->whereBetween('document_date', [$startDate, $endDate]);
                    
                    });
                }
            ], "debit_amt_{$currency}")
            ->withSum([
                'details as details_sum_credit_amt' => function ($query) use ($startDate, $endDate, $group_id,$cost) {
                    $query->where('ledger_parent_id', $group_id)
                    ->when($cost, function ($query) use ($cost) {
                        $query->where('cost_center_id', $cost);
                    })
                    ->withwhereHas('voucher', function ($query) use($startDate,$endDate) {
                        $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
                        $query->orderBy('document_date', 'asc');
                       $query->whereBetween('document_date', [$startDate, $endDate]);
                    
                    });
                }
            ], "credit_amt_{$currency}")
            ->get()
            ->map(function ($ledger) use ($group_id,$organizations,$startDate,$endDate,$currency,$fy,$carry,$cost) {
                
                $openingData = ItemDetail::where('ledger_parent_id', $group_id)
                ->when($cost, function ($query) use ($cost) {
                    $query->where('cost_center_id', $cost);
                })
                ->where('ledger_id',$ledger->id)
                ->whereHas('voucher', function ($query) use($organizations,$startDate,$endDate,$fy,$carry) {
                    $query->where('document_date', '<', $startDate);
                    if(!$carry)
                    $query->where('document_date', '>=', $fy['start_date']);
                    $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
                    $query->whereIn('organization_id', $organizations);
                })
                ->selectRaw("SUM(debit_amt_{$currency}) as total_debit, SUM(credit_amt_{$currency}) as total_credit")
                ->first();
            
                $opening = $openingData->total_debit - $openingData->total_credit ?? 0;
                $opening_type = ($openingData->total_debit > $openingData->total_credit) ? 'Dr' : 'Cr';
            
            
                // Set to 0 if the sum is null
                $ledger->details_sum_debit_amt = $ledger->details_sum_debit_amt ?? 0;
                $ledger->details_sum_credit_amt = $ledger->details_sum_credit_amt ?? 0;
                $closing = $opening+($ledger->details_sum_debit_amt - $ledger->details_sum_credit_amt);
        
                $ledger->opening = abs($opening);
                $ledger->open = $opening;
                $ledger->closing = abs($closing);
                $ledger->closing_type = $closing<0?"Cr":"Dr";
                $ledger->opening_type = $opening_type;
                $ledger->group_id = $group_id; // Default type if no details exist
                
                unset($ledger->details);
        
                return $ledger;
            });
            
            
      }

        return ['type' => $type, 'data' => $data,'date0'=>$startDate,'date1'=>$endDate];
    }
    public static function getReservesSurplus($startDate,$endDate, $organizations, $type, $currency = "org",$cost=null)
    {
        // Get previous day from current start date

        $data = self::getPlGroupsData($startDate,$endDate, $organizations, $currency,$cost);
        $details = self::getPlGroupDetails($data);

        $netProfit = $details['netProfit'];
        $netLoss = $details['netLoss'];

        if ($type == "trialBalance") {
            $totalPlType = '';
            $totalPl = $netProfit - $netLoss;

            if ($totalPl != 0) {
                if ($totalPl > 0) {
                    $totalPlType = 'Cr';
                } else {
                    $totalPlType = 'Dr';
                }
            }
            return ['closingFinal' => $totalPl > 0 ? $totalPl : -$totalPl, 'closing_type' => $totalPlType];
        } else {
            return $netProfit - $netLoss;
        }
    }

    public static function getPlGroupsData($startDate, $endDate, $organizations, $currency = "org",$cost=null,$type=null)
    {
        
        $data = PLGroups::select('id', 'name', 'group_ids')->get()->map(function ($plGroup) use ($type,$cost,$startDate, $endDate, $organizations, $currency) {
            $groups = Group::whereIn('id', $plGroup->group_ids)
            ->where(function ($query) use ($organizations) {
                $query->whereIn('organization_id', $organizations) // Match organization IDs
                      ->orWhereNull('organization_id'); // Include groups with NULL organization_id
            })
            ->select('id', 'name')
            ->get();

            $totalCredit = 0;
            $totalDebit = 0;
            $opening = 0;
            $totalCreditOpen = 0;
            $totalDebitOpen = 0;
            foreach ($groups as $master) {
                $allChildIds = $master->getAllChildIds();
                $allChildIds[] = $master->id;

                $ledgers = Ledger::where(function ($query) use ($allChildIds) {
                $query->whereIn('ledger_group_id', $allChildIds)
                    ->orWhere(function ($subQuery) use ($allChildIds) {
                        foreach ($allChildIds as $child) {
                            $subQuery->orWhereJsonContains('ledger_group_id',(string)$child);
                        }
                    });
            })->whereIn('organization_id', $organizations)
                ->pluck('id')->toArray();
                $non_carry = Helper::getNonCarryGroups();
                $fy = self::getFinancialYear($startDate);
              

                if(in_array($master->id,$non_carry))
                $carry=0;
                else 
                $carry=1;

                $transactions = ItemDetail::whereIn('ledger_id', $ledgers)
                ->whereIn('ledger_parent_id',$allChildIds)
                ->when($cost, function ($query) use ($cost) {
                    $query->where('cost_center_id', $cost);
                })
                ->whereHas('voucher', function ($query) use ($organizations,$startDate,$endDate,$carry,$fy)  {
                    $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
                        $query->whereIn('organization_id', $organizations);
                        $query->whereBetween('document_date', [$startDate, $endDate]);

                    })->get();


                // Calculate totals
                $creditField = "credit_amt_{$currency}";
                $debitField = "debit_amt_{$currency}";
            
               
                

                $transactionsOpen = ItemDetail::whereIn('ledger_id', $ledgers)
                ->whereIn('ledger_parent_id',$allChildIds)
                ->when($cost, function ($query) use ($cost) {
                    $query->where('cost_center_id', $cost);
                })
                ->whereHas('voucher', function ($query) use ($organizations,$startDate,$endDate,$carry,$fy)  {
                    $query->where('document_date', '<', $startDate);
                    //if(!$carry)
                    //$query->where('document_date', '>=', $fy['start_date']);
                    $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
                    $query->whereIn('organization_id', $organizations);
                })->get();
                    
                $totalCreditOpen +=$transactionsOpen->sum(fn($t) => self::removeCommas($t->$creditField));
                $totalDebitOpen += $transactionsOpen->sum(fn($t) => self::removeCommas($t->$debitField));


                    $totalCredit +=$transactions->sum(fn($t) => self::removeCommas($t->$creditField));
                    $totalDebit += $transactions->sum(fn($t) => self::removeCommas($t->$debitField));


                unset($master->children);
            }
            $opening = $totalDebitOpen - $totalCreditOpen;
            $plGroup->opening = $opening;
            $plGroup->totalCredit = $totalCredit;
            $plGroup->totalDebit = $totalDebit;
           



            $closingText = '';
            if($type=="profitloss")
            $closing = $totalDebit - $totalCredit;
            else
            $closing = $opening + ($totalDebit - $totalCredit);
                
            if ($closing != 0) {
                $closingText = $closing > 0 ? 'Dr' : 'Cr';
            }

            $plGroup->closing = $closing < 0 ? -$closing : $closing;
            $plGroup->closingText = $closingText;

            unset($plGroup->group_ids);
            return $plGroup;
        });
        return $data;
    }
    public static function getPlGroupDetails($groups)
    {
        $totalSales = 0;
        $totalPurchase = 0;
        $saleInd = 0;
        $purchaseInd = 0;

        $indExpTotal = 0;
        $indIncTotal = 0;

        $opening = 0;
        $purchase = 0;
        $directExpense = 0;
        $indirectExpense = 0;
        $salesAccount = 0;
        $directIncome = 0;
        $indirectIncome = 0;
        $grossProfit = 0;
        $grossLoss = 0;
        $subTotal = 0;
        $overAllTotal = 0;
        $netProfit = 0;
        $netLoss = 0;

        foreach ($groups as $group) {
            if ($group->name == "Opening Stock") {
                $totalPurchase = ($group->closing + $totalPurchase);
                $opening= $group->closing;
            }
            if ($group->name == "Purchase Accounts") {
                $totalPurchase = ($group->closing + $totalPurchase);
                $purchase= $group->closing;
            }
            if ($group->name == "Direct Expenses") {
                $totalPurchase = ($group->closing + $totalPurchase);
                $directExpense = $group->closing;
            }
            if ($group->name == "Indirect Expenses") {
                $purchaseInd = ($group->closing + $purchaseInd);
                $indExpTotal =$group->closing;
                $indirectExpense = $group->closing;
            }
            if ($group->name == "Sales Accounts") {
                $totalSales = ($group->closing + $totalSales);
                $salesAccount=$group->closing;
            }
            if ($group->name == "Direct Income") {
                $totalSales=($group->closing + $totalSales);
                $directIncome=$group->closing;
            }
            if ($group->name == "Indirect Income") {
                $saleInd =($group->closing + $saleInd);
                $indIncTotal=$group->closing;
                $indirectIncome=$group->closing;
            }
        }

        $difference = $totalSales - $totalPurchase;
        $diffVal = $difference < 0 ? -$difference : $difference;

        if ($totalSales > $totalPurchase) {
            $grossProfit = $diffVal;
            $grossLoss = 0;
            $subTotal = $totalSales;
            $saleInd = $diffVal + $saleInd;
        } else {
            $grossLoss = $diffVal;
            $grossProfit = 0;
            $subTotal = $totalPurchase;
            $purchaseInd = $diffVal + $purchaseInd;
        }

        if ($saleInd > $purchaseInd) {
            $overAllTotal = $saleInd;
            $netProfit = $saleInd - $indExpTotal;
            $netLoss = 0;
        } else {
            $overAllTotal = $purchaseInd;
            $netLoss = $purchaseInd - $indIncTotal;
            $netProfit = 0;
        }
       
        return [
            'opening' => $opening,
            'purchase' => $purchase,
            'directExpense' => $directExpense,
            'indirectExpense' => $indirectExpense,
            'salesAccount' => $salesAccount,
            'directIncome' => $directIncome,
            'indirectIncome' => $indirectIncome,
            'grossProfit' => $grossProfit,
            'grossLoss' => $grossLoss,
            'subTotal' => $subTotal,
            'overAllTotal' => $overAllTotal,
            'netProfit' => $netProfit,
            'netLoss' => $netLoss
        ];
    }


    public static function getAuthenticatedUser()
    {
        //
        // $authUser = AuthUser::find(5);
        // Auth::guard('web')->login(User::find(2));
        // auth() -> user() -> authenticable_type = $authUser->authenticable_type;
        // auth() -> user() -> auth_user_id = $authUser->id;
        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->user();
        } elseif (Auth::guard('web2')->check()) {
            return Auth::guard('web2')->user();
        }
    }
    public static function getOrgWiseUserAndEmployees($organizationId)
    {
        $employeeIds = Employee::where(function ($query) use ($organizationId) {
            $query->whereHas('access_rights_org', function ($subQuery) use ($organizationId) {
                 $subQuery->where('organization_id', $organizationId);
             })->orWhere('organization_id', $organizationId);
        })->get()->pluck('id');

        $userIds= User::where(function ($query) use ($organizationId) {
            $query->whereHas('access_rights_org', function ($subQuery) use ($organizationId) {
                 $subQuery->where('organization_id', $organizationId);
             })->orWhere('organization_id', $organizationId);
        })->get()->pluck('id');

        $user = self::getAuthenticatedUser();
        $employees = AuthUser::where('db_name', $user->db_name)
        ->where(function ($empQuery) use($employeeIds) {
            $empQuery -> where('authenticable_type', 'employee') -> whereIn('authenticable_id', $employeeIds);
        }) -> orWhere(function ($userQuery) use($userIds) {
            $userQuery -> where('authenticable_type', 'user') -> whereIn('authenticable_id', $userIds);
        })
        ->get();
        return $employees;
    }

    public static function getOrganizationLogo($organizationId)
    {

        $logoUrl = "";
        $organization= Organization::find($organizationId);
        if($organization && $organization->organization_logo){
            return $organization->organization_logo;
        }


        $orgMedia = Media::where('model_type', 'App\Models\Organization')
            ->where('model_id', $organizationId)
            ->where('collection_name', 'logo')
            ->latest()
            ->first();

        if ($orgMedia) {
            $logoUrl = "https://login.thepresence360.com";
            $dbName = Session::get('DB_DATABASE', '');
            $logoUrl .= "/storage";
            if ($dbName)
                $logoUrl .= "/$dbName";
            $logoUrl .= "/$orgMedia->id/$orgMedia->file_name";
        }

        return $logoUrl;
    }

    public static function getInitials($name)
    {
        $words = explode(' ', $name);
        $initials = '';

        if (count($words) > 1) {
            // Get first letter of the first and last name
            $initials .= strtoupper($words[0][0]); // First letter of first name
            $initials .= strtoupper($words[count($words) - 1][0]); // First letter of last name
        } else {
            // If only one name, return first two letters
            $initials .= strtoupper($words[0][0]); // First letter
            if (strlen($words[0]) > 1) {
                $initials .= strtoupper($words[0][1]); // Second letter
            }
        }

        return $initials;
    }

    # Revision History
    // public static function createRevisionHistory($moduleModel)
    // {
    //     \DB::beginTransaction();
    //     try {
    //         $parentData = $moduleModel->getOriginal();
    //         $parentData['source_id'] = $parentData['id'];
    //         unset($parentData['id']);
    //         $parentModelName = $moduleModel ? get_class($moduleModel) : '';
    //         $parentHistoryModel = $parentModelName . 'History';

    //         $insertedHistoryId = null;
    //         if (class_exists($parentHistoryModel)) {
    //             $parentHistoryInstance = resolve($parentHistoryModel);
    //             $insertedHistoryId = $parentHistoryInstance::insertGetId($parentData);
    //         }

    //         foreach ($moduleModel->getRelations() as $relation => $value) {
    //             if ($value instanceof \Illuminate\Database\Eloquent\Collection) {
    //                 $modelName = $value->first() ? get_class($value->first()) : '';
    //                 $historyModel = $modelName . 'History';
    //                 if (class_exists($historyModel)) {
    //                     $foreignKey = $moduleModel->getForeignKey();
    //                     $modifiedArray = array_map(function ($model) {
    //                         $model->makeHidden($model->getAppends());
    //                         $dataArray = \Illuminate\Support\Arr::except($model->toArray(), ['id']);
    //                         $dataArray[$foreignKey] = $insertedHistoryId;
    //                         return $dataArray;
    //                     }, $value->all());
    //                     $historyInstance = resolve($historyModel);
    //                     $historyInstance::insert($modifiedArray);
    //                 }
    //             } else {
    //                 $modelName = get_class($value);
    //                 $historyModel = $modelName . 'History';
    //                 if (class_exists($historyModel)) {
    //                     $foreignKey = $moduleModel->getForeignKey();
    //                     $value->makeHidden($value->getAppends());
    //                     $singleModelArray = \Illuminate\Support\Arr::except($value->toArray(), ['id']);
    //                     $singleModelArray[$foreignKey] = $insertedHistoryId;
    //                     $historyInstance = resolve($historyModel);
    //                     $historyInstance::insert($singleModelArray);
    //                 }
    //             }
    //         }

    //         \DB::commit();
    //         return true;

    //     } catch (\Exception $e) {
    //         \DB::rollBack();
    //         \Log::error($e->getMessage());
    //         return false;
    //     }
    // }
    public static function getModelFromServiceAlias($alias){
        $baseNamespace = "App\\Models\\";

            // Get the model name from the mapping array
            $modelName = ConstantHelper::SERVICE_ALIAS_MODELS[$alias] ?? null;
            if ($modelName) {
                $modelClass = $baseNamespace.$modelName;
                if (class_exists($modelClass)) {
                    return $modelClass;
                }
                else return null;
            } else {
                return null;
            }

    }
    public static function getRouteNameFromServiceAlias($alias, $id)
    {
        // Get the route name from the constant array
        $routeName = ConstantHelper::SERVICE_ALIAS_VIEW_ROUTE[$alias] ?? null;

        // Check if the route exists before returning
        if ($routeName && Route::has($routeName)) {
            return route($routeName, $id);
        }

        return "#";
    }


    public static function documentAmendment($tables, $headerId)
    {
        try {
            if (count($tables)) {
                $headerHistoryId = [];
                $detailHistoryId = [];
                $detailColumn = null;
                foreach ($tables as $table) {
                    $mn = "App\\Models\\" . $table['model_name'];
                    $ModelInstance = resolve($mn);
                    if ($table['model_type'] == 'header') {
                        $headerModel = $ModelInstance::where('id', $headerId)->get();
                        if (!count($headerModel)) {
                            Log::error("documentAmendment Error: $mn " . 'Header Model not found.');
                        }
                        $headerHistoryId = self::createRevisionHistory($headerModel);
                    }

                    if ($table['model_type'] == 'detail' && count($headerHistoryId)) {
                        $detailColumn = $table['relation_column'];

                        $detailModel = $ModelInstance::where([[$detailColumn, $headerId]])->get();
                        if (!count($detailModel)) {
                            Log::error("documentAmendment Error: $mn " . 'Detail Model not found.');
                        }
                        $detailHistoryId = self::createRevisionHistory($detailModel, $table['relation_column'], $headerHistoryId);
                    }

                    if ($table['model_type'] == 'sub_detail' && count($detailHistoryId)) {
                        $subDetailModel = $ModelInstance::where([[$detailColumn, $headerId]])->get();
                        if (!count($subDetailModel)) {
                            Log::error("documentAmendment Error: $mn " . 'Sub Detail Model not found.');
                        }
                        self::createRevisionHistory($subDetailModel, $detailColumn, $headerHistoryId, $table['relation_column'], $detailHistoryId);
                    }
                }
                return true;
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            Log::error("documentAmendment Error: $error");
        }
        return false;
    }

    public static function createRevisionHistory($modelObj, $headerColumn = null, $headerId = [], $detailColumn = null, $detailId = [])
    {
        $arr = [];

        foreach ($modelObj as $modelOb) {
            $modelData = $modelOb->getOriginal();
            $modelData['source_id'] = $modelData['id'];
            if (isset($headerColumn) && count($headerId)) {
                $sourceHeaderId = $modelData[$headerColumn];
                $matchingItem = array_filter($headerId, function ($item) use ($sourceHeaderId) {
                    return $item['source_id'] === $sourceHeaderId;
                });

                $sourceHeaderHistoryId = !empty($matchingItem) ? reset($matchingItem)['history_id'] : null;
                $modelData[$headerColumn] = $sourceHeaderHistoryId;
            }

            if (isset($detailColumn) && count($detailId)) {
                $sourceHeaderId = $modelData[$detailColumn];
                $matchingItem = array_filter($detailId, function ($item) use ($sourceHeaderId) {
                    return $item['source_id'] === $sourceHeaderId;
                });
                $sourceHeaderHistoryId = !empty($matchingItem) ? reset($matchingItem)['history_id'] : null;
                $modelData[$detailColumn] = $sourceHeaderHistoryId;
            }

            unset($modelData['id']);
            $ModelName = $modelOb ? get_class($modelOb) : '';
            $HistoryModel = $ModelName . 'History';
            $HistoryModelInstance = resolve($HistoryModel);

            if (isset($modelData['attachment']) && empty($modelData['attachment'])) {
                $modelData['attachment'] = json_encode([]);
            } else if (isset($modelData['attachment'])) {
                $modelData['attachment'] = json_encode($modelData['attachment']);
            }

            $insertedHistoryId = $HistoryModelInstance::insertGetId($modelData);
            array_push($arr, ['source_id' => $modelData['source_id'], 'history_id' => $insertedHistoryId]);

            /*Media backup*/
            if (method_exists($modelOb, 'getDocuments') && $modelOb->getDocuments()->count()) {
                foreach ($modelOb->getDocuments() as $Document) {
                    $media = $HistoryModelInstance::where('id', $insertedHistoryId)->first();
                    $media->media()->create([
                        'uuid' => (string) Str::uuid(),
                        'model_name' => class_basename($media),
                        'collection_name' => $Document->collection_name,
                        'name' => $Document->name,
                        'file_name' => $Document->file_name,
                        'mime_type' => $Document->mime_type,
                        'disk' => $Document->disk,
                        'size' => $Document->size
                    ]);
                }
            }

            //Address backup
            if (method_exists($modelOb, 'addresses') && $modelOb->addresses()->count()) {
                foreach ($modelOb->addresses as $address) {
                    ErpAddress::create([
                        'addressable_id' => $insertedHistoryId,
                        'addressable_type' => $HistoryModelInstance::class,
                        'country_id' => $address->country_id,
                        'state_id' => $address->state_id,
                        'city_id' => $address->city_id,
                        'address' => $address->address,
                        'type' => $address->type,
                        'pincode' => $address->pincode,
                        'phone' => $address->phone,
                        'fax_number' => $address->fax_number,
                        'is_billing' => $address->is_billing,
                        'is_shipping' => $address->is_shipping
                    ]);
                }
            }
        }

        return $arr;
    }

    /*Check after amendment again approval request we need or not*/
    public static function checkAfterAmendApprovalRequired($bookId)
    {
        $book = Book::find($bookId);
        if (!$book) {
            return ['data' => [], 'message' => "No record found!", 'status' => 404];
        }
        return $book->amendment ?? null;
    }
    /*Created helper for the get created bom cost*/
    public static function getChildBomItemCost($itemId, $selectedAttributes)
    {
        $selectedAttributes = is_string($selectedAttributes) ? json_decode($selectedAttributes, true) : $selectedAttributes;
        $type = ['WIP/Semi Finished', 'Finished Goods'];
        $item = Item::whereHas('subTypes', function ($query) use ($type) {
            $query->whereHas('subType', function ($subTypeQuery) use($type) {
                $subTypeQuery ->whereIn('name', $type);
            });
        })->find($itemId);

        if (!$item) {
            return ['cost' => 0, 'status' => 422, 'message' => 'Not header item'];
        }
        $bom = Bom::where('item_id', $item->id)/*->where('document_status', ConstantHelper::APPROVED)*/;

        if (!$bom->first()) {
            return ['cost' => 0, 'status' => 422, 'message' => 'Not found header in BOM'];
        }
        $itemAttIds = $item->itemAttributes()->where('required_bom', 1)->pluck('attribute_group_id');
        foreach ($itemAttIds as $itemAttId) {
            $vId = null;
            foreach ($selectedAttributes as $selectedAttribute) {
                if (@$selectedAttribute['attr_id'] == $itemAttId) {
                    $vId = @$selectedAttribute['attr_value'];
                    break;
                }
            }
            $bom = $bom->whereHas('bomAttributes', function ($q1) use ($itemAttId, $vId) {
                $q1->where('attribute_name', $itemAttId)
                    ->where('attribute_value', $vId);
            });
        }
        $bom = $bom->first();
        if ($bom) {
            $totalValue = $bom->total_value ?? 0;
            return ['cost' => $totalValue, 'route' => route('bill.of.material.edit', $bom->id), 'status' => 200, 'message' => 'Fetched BOM header item cost'];
        }
        return ['cost' => 0, 'status' => 422, 'message' => 'Not found header in BOM'];
    }

    public static function formatNumber($number)
    {
        if ($number >= 10000000) { // For values 1 Crore and above
            return number_format($number / 10000000, 2) . ' Cr';
        } elseif ($number >= 100000) { // For values 1 Lakh and above
            return number_format($number / 100000, 2) . ' Lac';
        } elseif ($number >= 1000) { // For values 1 Thousand and above
            return number_format($number / 1000, 2) . ' K';
        } else { // Less than 1 Thousand
            return number_format($number, 2);
        }
    }

    public static function currencyFormat($number, $type = null)
    {
        $user = self::getAuthenticatedUser();
        $currencyMaster = ErpCurrencyMaster::where('organization_id', $user->organization_id)->where('status', ConstantHelper::ACTIVE)->first();
        if (!$currencyMaster) {
            return round($number);
        }
        $value = $currencyMaster->conversion_value;
        $updatedVal = round($number / $value);
        if ($type == 'display') {
            return $updatedVal > 0 ? $currencyMaster->symbol . '' . $updatedVal . '' . $currencyMaster->conversion_type : $currencyMaster->symbol . '' . round($number);
        } else {
            return $updatedVal;
        }
    }

    public static function documentListing($id)
    {
        $data = HomeLoan::with([
            'loanAppraisal.document',
            'loanApproval',
            'loanAssessment',
            'loanSanctLetter',
            'loanLegalDocs',
            'loanProcessFee'
        ])->where('id', $id)->first();

        $columnsToCheck = [
            'image',
            'loanAppraisal',
            'loanApproval',
            'loanAssessment',
            'loanSanctLetter',
            'loanLegalDocs',
            'loanProcessFee',
        ];

        $n = 1;
        $html = '';

        foreach ($columnsToCheck as $column) {
            if (isset($data->$column) && !is_null($data->$column)) {
                // Handle loanAppraisal with nested documents
                if ($column === 'loanAppraisal' && !empty($data->loanAppraisal->document)) {
                    foreach ($data->loanAppraisal->document as $appraisalDocument) {
                        $html .= '<tr>
                            <td>' . $n++ . '</td>
                            <td>' . (preg_replace('/(?<!^)([A-Z])/', ' $1', $appraisalDocument['document_type']) ?? 'KYC Document') . '</td>
                            <td>
                                <a target="_blank" href="' . asset('storage/' . $appraisalDocument['document']) . '">
                                    <i data-feather="download"></i>
                                </a>
                            </td>
                        </tr>';
                    }
                }
                // Handle loanLegalDoc with multiple entries directly
                elseif ($column === 'loanLegalDocs' && !empty($data->loanLegalDoc)) {
                    $html .= '<tr>
                        <td>' . $n++ . '</td>
                        <td>Legal Document</td>
                        <td>';
                    foreach ($data->loanLegalDocs as $legalDoc) {
                        $html .= '
                                <a target="_blank" href="' . asset('storage/' . $legalDoc->doc) . '">
                                    <i data-feather="download"></i>
                                </a>';
                    }
                    $html .= '</td>
                        </tr>';
                }
                // Handle single-object columns
                elseif (is_object($data->$column) && !empty($data->$column->doc)) {
                    $html .= '<tr>
                        <td>' . $n++ . '</td>
                        <td>
                            ' . ucwords(str_replace(
                        ['loan', 'Doc', 'Fee'],
                        ['Loan', 'Document', 'Fee'],
                        preg_replace('/(?<!^)([A-Z])/', ' $1', $column)
                    )) . '
                        </td>
                        <td>
                            <a target="_blank" href="' . asset('storage/' . $data->$column->doc) . '">
                                <i data-feather="download"></i>
                            </a>
                        </td>
                    </tr>';
                }
            }
        }



        return $html;
    }

    public static function logs(
        $series_id,
        $application_number,
        $loan_application_id,
        $organization_id,
        $module_type,
        $description,
        $created_by,
        $docs,
        $user_type,
        $revision_number,
        $revision_date,
        $active_status
    ) {

        LoanLog::create([
            'series_id' => $series_id,
            'application_number' => $application_number,
            'loan_application_id' => $loan_application_id,
            'organization_id' => $organization_id,
            'module_type' => $module_type,
            'description' => $description,
            'created_by' => $created_by,
            'document' => json_encode($docs),
            'user_type' => $user_type,
            'revision_number' => $revision_number,
            'revision_date' => $revision_date,
            'active_status' => $active_status
        ]);
    }

    public static function getLogs($application_id)
    {
        $logs = LoanLog::with('employee')->where('loan_application_id', $application_id)->get();
        // $logs = LoanLog::with('employee')->where('loan_application_id', 0)->get();

        $html = '';
        foreach ($logs as $key => $log) {
            $html .= '
            <tr>
    <td>' . ($key + 1) . '</td>
    <td class="text-nowrap">
        ' . explode(' ', date('d-m-Y', strtotime($log->created_at)))[0] . '
    </td>
    <td>' . $log->module_type . '</td>
    <td>';
            $decodedDocument = json_decode($log->document, true);

            if (is_array($decodedDocument)) {
                foreach ($decodedDocument as $list) {
                    $html .= '<a target="_blank" href="' . asset('storage/' . $list) . '" download><i data-feather="download" class="me-50"></i></a>';
                }
            }

            $html .= $log->description . '
    </td>
    <td>' . $log->employee?->name ?? '-' . '</td>
</tr>';
        }

        return $html;
    }

    public static function getAccessibleServicesFromMenuAlias(string $menuAlias, string $selectedServiceAlias = null): array
    {
        $authUser = Helper::getAuthenticatedUser();
        $organizationMenu = OrganizationMenu::withDefaultGroupCompanyOrg() -> where([
            ['alias', $menuAlias]
        ])->first();
        if (!isset($organizationMenu)) {
            return [
                'services' => [],
                'books' => [],
                'menu' => null,
                'all_book_access' => false,
                'message' => 'Organization Menu not found'
            ];
        } else {
            if ($selectedServiceAlias) {
                $organizationServices = OrganizationService::withDefaultGroupCompanyOrg()
                    ->where('alias', $selectedServiceAlias)->get();
                return [
                    'services' => $organizationServices,
                    'menu' => $organizationMenu,
                    'books' => [],
                    'all_book_access' => true,
                    'message' => 'All Data Found'
                ];
            } else {
                $services = EmployeeBookMapping::where('service_menu_id', $organizationMenu ?-> serviceMenu -> id) -> where('employee_id', $authUser -> auth_user_id) -> first();
                if (!isset($services)) { //Assign all services and books data if no record is found
                    $serviceIds = $organizationMenu ?-> serviceMenu ?-> erp_service_id ?? [];

                    if(is_string($serviceIds)) {
                        $serviceIds = json_decode($serviceIds, true) ?? [];
                    }

                    $bookIds = [];
                    $organizationServices = OrganizationService::withDefaultGroupCompanyOrg()->whereIn('service_id', $serviceIds ?? [])->when($selectedServiceAlias, function ($aliasQuery) use($selectedServiceAlias) {
                        $aliasQuery -> where('alias', $selectedServiceAlias);
                    })->get();
                    $currentBook = Book::withDefaultGroupCompanyOrg() -> whereIn('service_id', $serviceIds ?? []) -> first();
                    return [
                        'services' => $organizationServices,
                        'books' => [],
                        'current_book' => $currentBook,
                        'menu' => $organizationMenu,
                        'all_book_access' => true,
                        'message' => 'All Data Found'
                    ];
                } else {
                    $serviceIds = $services ?-> erp_service_ids ?? [];
                    $bookIds = $services ?-> book_ids ?? [];
                    $organizationServices = OrganizationService::withDefaultGroupCompanyOrg()->whereIn('service_id', $serviceIds)->when($selectedServiceAlias, function ($aliasQuery) use($selectedServiceAlias) {
                        $aliasQuery -> where('alias', $selectedServiceAlias);
                    })->get();
                    if ($organizationServices && count($organizationServices) > 0) {
                        $organizationServices = $organizationServices -> filter(function ($orgService) use($bookIds) {
                            $isBookExist = Book::withDefaultGroupCompanyOrg() -> whereIn('id', $bookIds) -> where('org_service_id', $orgService -> id) -> first();
                            return $isBookExist;
                        });
                    }
                    return [
                        'services' => $organizationServices,
                        'books' => $bookIds,
                        'current_book' => null,
                        'menu' => $organizationMenu,
                        'all_book_access' => false,
                        'message' => 'All Data Found'
                    ];
                }
            }
        }
    }

    public static function setMenuAccessToEmployee(String $menuName, String $menuAlias, array $serviceIds): string
    {
        DB::beginTransaction();
        if (!($menuName && $menuAlias && count($serviceIds)) > 0) {
            return "All datas are not specified";
        }
        try {
            $service = Services::first();
            $serviceMenu = ServiceMenu::where('alias', $menuAlias)->first();
            if (!isset($serviceMenu)) {
                $serviceMenu = ServiceMenu::create([
                    'service_id' => $service->id,
                    'erp_service_id' => $serviceIds,
                    'name' => $menuName,
                    'alias' => $menuAlias,
                ]);
            }
            $employees = Employee::all();
            foreach ($employees as $employee) {
                $employee = Employee::find($employee->id);
                $organization = Organization::find($employee->organization_id);
                if (!isset($organization)) {
                    continue;
                }
                $organizationMenu = OrganizationMenu::where('menu_id', $serviceMenu->id)->first();
                if (!isset($organizationMenu)) {
                    OrganizationMenu::create([
                        'group_id' => $organization->group_id,
                        'menu_id' => $serviceMenu->id,
                        'service_id' => $service->id,
                        'name' => $menuName,
                        'alias' => $menuAlias
                    ]);
                }
                $permissionMaster = PermissionMaster::where('alias', 'menu.' . $menuAlias)->first();
                if (!isset($permissionMaster)) {
                    $permissionMaster = PermissionMaster::create([
                        'service_id' => $service->id,
                        'name' => $menuName,
                        'alias' => 'menu.' . $menuAlias,
                        'type' => 'menu'
                    ]);
                }
                //Get employee roles
                $firstRole = Role::first();
                $employeeRole = EmployeeRole::where('employee_id', $employee->id)->first();
                if (!isset($employeeRoles)) {
                    $employeeRole = EmployeeRole::create([
                        'employee_id' => $employee->id,
                        'role_id' => $firstRole->id
                    ]);
                }
                $rolePermissionMaster = RolePermission::where('role_id', $employeeRole->role_id)->where('permission_id', $permissionMaster->id)->first();
                if (!isset($rolePermissionMaster)) {
                    $rolePermissionMaster = RolePermission::create([
                        'role_id' => $employeeRole->role_id,
                        'permission_id' => $permissionMaster->id
                    ]);
                }
            }
            DB::commit();
            return "Menu Assigned";
        } catch (Exception $ex) {
            DB::rollBack();
            return $ex->getMessage() . $ex->getFile() . $ex->getLine();
        }
    }

    public static function getPolicyByServiceId($serviceId)
    {
        $authUser = Helper::getAuthenticatedUser();
        if (!$authUser) {
            return [
                'error' => 'User not authenticated.'
            ];
        }
        $organization = $authUser->organization;
        $policy = ErpOrganizationMasterPolicy::where('service_id', $serviceId)
            ->where('organization_id', $organization->id)
            ->withDefaultGroupCompanyOrg()
            ->first();

        if (!$policy) {
            return [
                'error' => 'Policy not found for the given service and organization.'
            ];
        }
        if ($policy->policy_level == 'G') {
            $policyLevelData = [
                'group_id' => $policy->group_id,
                'company_id' => null,
                'organization_id' => null,
            ];
        } elseif ($policy->policy_level == 'C') {
            $policyLevelData = [
                'group_id' => $policy->group_id,
                'company_id' => $policy->company_id,
                'organization_id' => null,
            ];
        } elseif ($policy->policy_level == 'O') {
            $policyLevelData = [
                'group_id' => $policy->group_id,
                'company_id' => $policy->company_id,
                'organization_id' => $policy->organization_id,
            ];
        }
        return [
            'policyLevelData' => $policyLevelData,
        ];
    }

    public static function getDocStatusUser(string $modelClass, int $documentId, string $status)
    {
        $actualStatus = isset(ConstantHelper::DOC_APPROVAL_STATUS_MAPPING[$status]) ? ConstantHelper::DOC_APPROVAL_STATUS_MAPPING[$status] : $status;
        $documentApproval = DocumentApproval::where('document_name', $modelClass)
        -> where('document_id', $documentId) -> where('approval_type', $actualStatus) -> latest() -> first();
        if (!isset($documentApproval)) {
            $documentApproval =  DocumentApproval::where('document_name', $modelClass)
            -> where('document_id', $documentId) -> where('approval_type', 'submit') -> latest() -> first();
        }
        return $documentApproval ?-> user ?-> name;
    }
    public static function numberToWords($num) {
        $ones = [
            0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four',
            5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine',
            10 => 'ten', 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen',
            14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen',
            18 => 'eighteen', 19 => 'nineteen'
        ];

        $tens = [
            2 => 'twenty', 3 => 'thirty', 4 => 'forty', 5 => 'fifty',
            6 => 'sixty', 7 => 'seventy', 8 => 'eighty', 9 => 'ninety'
        ];

        $levels = ['', 'thousand', 'lakh', 'crore'];

        if ($num == 0) {
            return 'Zero';
        }

        // Split integer and decimal parts
        $numParts = explode('.', number_format($num, 2, '.', ''));
        $integerPart = (int) $numParts[0];
        $decimalPart = isset($numParts[1]) ? (int) $numParts[1] : 0;

        // Convert integer part
        $integerWords = self::convertIntegerToWords($integerPart, $ones, $tens, $levels);

        // Convert decimal part (if exists)
        $decimalWords = $decimalPart > 0 ? " and " . self::convertBelowThousand($decimalPart) . " paise" : "";

        return ucfirst(trim($integerWords . $decimalWords));
    }

    private static function convertIntegerToWords($num, $ones, $tens, $levels) {
        if ($num == 0) {
            return 'zero';
        }

        $numString = (string) $num;
        $numLength = strlen($numString);
        $parts = [];

        // Extract the last three digits (hundreds, tens, units)
        if ($numLength > 3) {
            $parts[] = substr($numString, -3);
            $numString = substr($numString, 0, -3);
        } else {
            $parts[] = $numString;
            $numString = '';
        }

        // Extract groups of two for lakh and crore
        while (strlen($numString) > 0) {
            if (strlen($numString) > 2) {
                $parts[] = substr($numString, -2);
                $numString = substr($numString, 0, -2);
            } else {
                $parts[] = $numString;
                $numString = '';
            }
        }

        $parts = array_reverse($parts);
        $words = [];

        foreach ($parts as $index => $part) {
            $partNum = intval($part);
            if ($partNum) {
                $words[] = self::convertBelowThousand($partNum) . ' ' . ($levels[count($parts) - $index - 1] ?? '');
            }
        }

        return trim(implode(' ', $words));
    }

    private static function convertBelowThousand($num) {
        $ones = [
            0 => 'zero', 1 => 'one', 2 => 'two', 3 => 'three', 4 => 'four',
            5 => 'five', 6 => 'six', 7 => 'seven', 8 => 'eight', 9 => 'nine',
            10 => 'ten', 11 => 'eleven', 12 => 'twelve', 13 => 'thirteen',
            14 => 'fourteen', 15 => 'fifteen', 16 => 'sixteen', 17 => 'seventeen',
            18 => 'eighteen', 19 => 'nineteen'
        ];

        $tens = [
            2 => 'twenty', 3 => 'thirty', 4 => 'forty', 5 => 'fifty',
            6 => 'sixty', 7 => 'seventy', 8 => 'eighty', 9 => 'ninety'
        ];

        if ($num < 20) {
            return $ones[$num];
        } elseif ($num < 100) {
            return $tens[intval($num / 10)] . ($num % 10 ? ' ' . $ones[$num % 10] : '');
        } else {
            return $ones[intval($num / 100)] . ' hundred' . ($num % 100 ? ' ' . self::convertBelowThousand($num % 100) : '');
        }
    }
    public static function getChildLedgerGroupsByNameArray($names,$ledger_name=null)
        {
            $organizationId = Helper::getAuthenticatedUser()->organization_id;
            $groups = collect(); // Initialize empty collection

            foreach ($names as $name) {
                // Get all matching groups (org-specific and global)
                $matchedGroups = Group::where('name', $name)
                    ->where(function ($query) use ($organizationId) {
                        $query->where('organization_id', $organizationId)
                            ->orWhereNull('organization_id');
                    })->get();

                $groups = $groups->merge($matchedGroups);
            }

            $allChildIds = [];

            foreach ($groups as $group) {
                $childIds = $group->getAllChildIds(); // Assume this returns array
                $childIds[] = $group->id; // Add parent group ID
                $allChildIds = array_merge($allChildIds, $childIds);
            }
            if($ledger_name=="names")
               return Group::whereIn('id',$allChildIds)->pluck('name')->toArray();

            // Remove duplicate IDs
            else
            return array_unique($allChildIds);
        }
        public static function getNonCarryGroups(){
            return self::getChildLedgerGroupsByNameArray(ConstantHelper::NON_CARRY_FORWARD_BALANCE_GROUPS);
        }

}


