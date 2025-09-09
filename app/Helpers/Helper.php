<?php

namespace App\Helpers;

use App\Models\AmendmentWorkflow;
use App\Models\ApprovalWorkflow;
use App\Models\FixedAssetRegistration;
use App\Models\Scopes\DefaultGroupCompanyOrgScope;
use App\Models\AuthUser;
use Illuminate\Validation\Rule;
use App\Models\CostCenterOrgLocations;
use App\Models\Book;
use App\Models\BookLevel;
use App\Models\BookType;
use App\Models\DocumentApproval;
use App\Models\Employee;
use App\Models\EmployeeBookMapping;
use App\Models\EmployeeRole;
use App\Models\ErpAddress;
use App\Models\CRM\ErpCurrencyMaster;
use App\Models\FixedAssetSetup;
use App\Models\FixedAssetSub;
use App\Models\MrnHeader;
use App\Models\MrnDetail;
use App\Models\ErpFinancialYear;
use App\Models\Group;
use App\Models\HomeLoan;
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
use App\Models\PLGroups;
use App\Models\RecoveryLoan;
use App\Models\User;
use App\Models\Voucher;
use App\Models\ErpOrganizationMasterPolicy;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Route;
// use Request;
use Illuminate\Http\Request;
use stdClass;
use Illuminate\Support\Str;

use App\Models\Contact;
use App\Models\BankInfo;
use App\Models\Note;
use App\Models\Compliance;
use App\Http\Controllers\VoucherController;
use App\Models\ErpFyMonth;
use App\Models\MrnAssetDetail;
use P360\Core\Interfaces\TagCacheInterface;
use App\Models\PbHeader;

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
        $user = self::getAuthenticatedUser();
        $startDate = request()->cookie('fyear_start_date') ?? $date;
        $endDate = request()->cookie('fyear_end_date') ?? $date;


        $financialYear = ErpFinancialYear::where('start_date', '<=', $startDate)
            ->where('end_date', '>=', $endDate)
            ->first();
        if ($financialYear != null) {

            $startYear = \Carbon\Carbon::parse($financialYear->start_date)->format('Y');
            $endYearShort = \Carbon\Carbon::parse($financialYear->end_date)->format('y'); // e
            $authorized = true;
            $currentUserId = $user->auth_user_id;
            $currentUserType = $user->authenticable_type;
            if ($financialYear->fy_close == true && is_array($financialYear->access_by)) {

                $authorized = !collect($financialYear->access_by)->contains(function ($entry) use ($currentUserId, $currentUserType) {
                    return isset($entry['user_id'], $entry['authorized'], $entry['authenticable_type'], $entry['locked']) &&
                        $entry['user_id'] == $currentUserId &&
                        $entry['authenticable_type'] == $currentUserType &&
                        (
                            $entry['authorized'] == false || $entry['locked'] == true
                        );
                });
            }
            return [
                'alias' => $financialYear->alias,
                'id' => $financialYear->id,
                'start_date' => $financialYear->start_date,
                'end_date' => $financialYear->end_date,
                'lock_fy' => $financialYear->lock_fy,
                'fy_close' => $financialYear->fy_close,
                'range' => $startYear . '-' . $endYearShort,
                'authorized' => $authorized,
            ];
        } else {
            return [
                'alias' => '',
                'id' => '',
                'start_date' => '',
                'end_date' => '',
                'lock_fy' => '',
                'fy_close' => '',
                'range' => '',
                'authorized' => '',
            ];
        }
    }

    public static function getCurrentFinancialYearMonths(): array
    {
        $user = self::getAuthenticatedUser();
        \Log::info('Authenticated user:', [
            'id' => $user->auth_user_id ?? null,
            'type' => $user->authenticable_type ?? null,
            'full_user' => $user
        ]);

        $startDate = request()->cookie('fyear_start_date') ?? date('Y-m-d');
        $endDate = request()->cookie('fyear_end_date') ?? date('Y-m-d');

        \Log::info('Cookies for Financial Year:', [
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ]);

        if (!$startDate || !$endDate) {
            \Log::warning('Missing financial year start/end date cookies.');
            return [];
        }

        // 1. Find current financial year
        $financialYear = Helper::getFinancialYear(date('Y-m-d'));
        \Log::info('Financial Year from Helper:', $financialYear);

        if (!$financialYear['authorized']) {
            \Log::warning('User not authorized for current financial year', [
                'financialYear' => $financialYear
            ]);
            return [];
        }

        // 2. Get all ErpFyMonth for this financial year
        $months = ErpFyMonth::where('fy_id', $financialYear['id'])
            ->orderBy('start_date')
            ->get();

        \Log::info('Fetched months:', $months->toArray());

        $currentUserId = $user->auth_user_id;
        $currentUserType = $user->authenticable_type;

        $result = [];
        foreach ($months as $month) {
            $authorized = true;
            \Log::debug('Checking month:', [
                'month_id' => $month->id,
                'start_date' => $month->start_date,
                'end_date'   => $month->end_date,
                'access_by'  => $month->access_by,
            ]);

            if (is_array($month->access_by)) {
                foreach ($month->access_by as $entry) {
                    \Log::debug('Access check entry:', $entry);

                    if (
                        isset($entry['user_id'], $entry['authorized'], $entry['authenticable_type'], $entry['locked']) &&
                        $entry['user_id'] == $currentUserId &&
                        $entry['authenticable_type'] == $currentUserType &&
                        (
                            $entry['authorized'] == false ||
                            $entry['locked'] == true
                        )
                    ) {
                        $authorized = false;
                        \Log::warning('Access denied for user in this month', [
                            'month_id' => $month->id,
                            'user_id' => $currentUserId,
                            'type' => $currentUserType,
                            'entry' => $entry
                        ]);
                        break;
                    }
                }
            }

            $monthData = $month->toArray();
            $monthData['authorized'] = $authorized;
            $result[] = $monthData;
        }

        \Log::info('Final month result:', $result);

        return $result;
    }

    public static function getFinancialYearQuarter(string $date): mixed
    {
        $targetDate = Carbon::parse($date);
        $financialYear = ErpFinancialYear::where('start_date', '<=', $date)
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

    public static function firstOrNewDocumentNumber(int $book_id, string $document_date, ?string $document_number = null, stdClass $parameters = null, $authUser = null): mixed
    {
        $book = Book::find($book_id);
        $pattern = NumberPattern::where('book_id', $book_id)->orderBy('id', 'DESC')->first();
        $serviceAlias = $pattern?->book?->org_service?->alias;
        $modelName = ConstantHelper::SERVICE_ALIAS_MODELS[$serviceAlias] ?? '';

        $financialYear = self::getFinancialYear($document_date);
        $financialQuarter = self::getFinancialYearQuarter($document_date);
        $financialMonth = self::getFinancialMonth($document_date);

        if ($pattern && $modelName) {
            $model = resolve("App\\Models\\{$modelName}");
            if ($pattern->series_numbering === ConstantHelper::DOC_NO_TYPE_AUTO) {
                $startFrom = max(0, $pattern->starting_no - 1);

                $prefix = '';
                $suffix = '';

                if ($pattern->reset_pattern === ConstantHelper::DOC_RESET_PATTERN_NEVER) {
                    $prefix = $pattern->prefix;
                    $suffix = $pattern->suffix;
                    $currentDocNo = $model->withDefaultGroupCompanyOrg($authUser)
                        ->where('book_id', $book_id)
                        ->whereNotNull('doc_no')
                        ->orderByRaw('CAST(doc_no AS UNSIGNED) DESC')
                        ->value('doc_no') ?? $startFrom;
                } elseif ($pattern->reset_pattern === ConstantHelper::DOC_RESET_PATTERN_YEARLY) {
                    if (!$financialYear) {
                        return self::errorData('Financial Year not setup');
                    }
                    $prefix = $financialYear['alias'];
                    $suffix = $pattern->suffix;
                    $currentDocNo = $model->withDefaultGroupCompanyOrg($authUser)
                        ->where('book_id', $book_id)
                        ->whereNotNull('doc_no')
                        ->whereBetween('document_date', [$financialYear['start_date'], $financialYear['end_date']])
                        ->orderBy('doc_no', 'DESC')
                        ->value('doc_no') ?? $startFrom;
                } elseif ($pattern->reset_pattern === ConstantHelper::DOC_RESET_PATTERN_QUARTERLY) {
                    if (!$financialQuarter) {
                        return self::errorData('Financial Year not setup');
                    }
                    $prefix = $financialYear['alias'] . '-' . $financialQuarter['alias'];
                    $suffix = $pattern->suffix;
                    $currentDocNo = $model->withDefaultGroupCompanyOrg($authUser)
                        ->where('book_id', $book_id)
                        ->whereNotNull('doc_no')
                        ->whereBetween('document_date', [$financialQuarter['start_date'], $financialQuarter['end_date']])
                        ->orderBy('doc_no', 'DESC')
                        ->value('doc_no') ?? $startFrom;
                } else {
                    if (!$financialMonth) {
                        return self::errorData('Financial Year not setup');
                    }
                    $prefix = $financialYear['alias'] . '-' . $financialMonth['alias'];
                    $suffix = $pattern->suffix;
                    $currentDocNo = $model->withDefaultGroupCompanyOrg($authUser)
                        ->where('book_id', $book_id)
                        ->whereNotNull('doc_no')
                        ->whereBetween('document_date', [$financialMonth['start_date'], $financialMonth['end_date']])
                        ->orderBy('doc_no', 'DESC')
                        ->value('doc_no') ?? $startFrom;
                }


                $currentDocNo = $document_number ?? ($currentDocNo ?: 0) + 1;
                $voucher_no = ($prefix ? $prefix . '-' : '') . $currentDocNo . ($suffix ? '-' . $suffix : '');

                $shouldCheckTransportDocForPrSr = in_array($book->service?->service?->alias, [
                    ConstantHelper::PURCHASE_RETURN_SERVICE_ALIAS,
                    ConstantHelper::SR_SERVICE_ALIAS
                ]);
                $shouldCheckTransportDocForSi = (
                    $serviceAlias === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS
                    && isset($parameters->{ServiceParametersHelper::INVOICE_TO_FOLLOW_PARAM})
                    && $parameters->{ServiceParametersHelper::INVOICE_TO_FOLLOW_PARAM}[0] === "no"
                );

                if (($shouldCheckTransportDocForPrSr || $shouldCheckTransportDocForSi)
                    && strlen($book->book_code . '-' . $voucher_no) > EInvoiceHelper::TRANPORTER_DOC_NO_MAX_LIMIT
                ) {
                    return self::errorData('Document Number cannot exceed 15 characters');
                }

                return [
                    'type' => ConstantHelper::DOC_NO_TYPE_AUTO,
                    'document_number' => $voucher_no,
                    'prefix' => $prefix,
                    'suffix' => $suffix,
                    'doc_no' => $currentDocNo,
                    'reset_pattern' => $pattern->reset_pattern,
                    'error' => null
                ];
            }

            return [
                'type' => ConstantHelper::DOC_NO_TYPE_MANUAL,
                'document_number' => null,
                'prefix' => null,
                'suffix' => null,
                'doc_no' => null,
                'reset_pattern' => $pattern->reset_pattern,
                'error' => null
            ];
        }

        return self::errorData('Transaction not setup');
    }

    private static function errorData(string $message): array
    {
        return [
            'type' => null,
            'document_number' => null,
            'prefix' => null,
            'suffix' => null,
            'doc_no' => null,
            'reset_pattern' => null,
            'error' => $message
        ];
    }

    public static function generateDocumentNumberNew(int $book_id, string $document_date, stdClass $parameters = null, $authUser = null): mixed
    {

        $book = Book::find($book_id);
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
                    $currentDocNo = $model->withDefaultGroupCompanyOrg($authUser)->where('book_id', $book_id)
                        ->whereNotNull('doc_no')
                        // ->orderBy('doc_no', 'DESC')
                        ->orderByRaw('CAST(doc_no AS UNSIGNED) DESC')
                        ->pluck('doc_no')->first() ?? $startFrom;
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
                    $currentDocNo = $model->withDefaultGroupCompanyOrg($authUser)->where('book_id', $book_id)
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
                    $currentDocNo = $model->withDefaultGroupCompanyOrg($authUser)->where('book_id', $book_id)
                        ->whereNotNull('doc_no')
                        ->whereBetween('document_date', [$financialQuarter['start_date'], $financialQuarter['end_date']])
                        ->orderBy('doc_no', 'DESC')->pluck('doc_no')->first() ?? $startFrom;
                } else {
                    if (isset($financialYear) && isset($financialQuarter) && isset($financialMonth)) {
                        $prefix = $financialYear['alias'] . "-" . $financialMonth['alias'];
                        $suffix = $data->suffix;
                        $currentDocNo = $model->withDefaultGroupCompanyOrg($authUser)->where('book_id', $book_id)
                            ->whereNotNull('doc_no')
                            ->whereBetween('document_date', [$financialMonth['start_date'], $financialMonth['end_date']])
                            ->orderBy('doc_no', 'DESC')->pluck('doc_no')->first() ?? $startFrom;
                    }
                }

                $currentDocNo = ($currentDocNo ? $currentDocNo : 0) + 1;

                $voucher_no = ($prefix ? $prefix . "-" : "") . ($currentDocNo) . ($suffix ? "-" . $suffix : "");

                //Condition for Sales Invoice/ Sales Return and Purchase Return
                $shouldCheckTransportDocForPrSr = in_array($book->service?->service?->alias, [
                    ConstantHelper::PURCHASE_RETURN_SERVICE_ALIAS,
                    ConstantHelper::SR_SERVICE_ALIAS
                ]);
                $shouldCheckTransportDocForSi = false;

                if (
                    $serviceAlias === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS && isset($parameters) &&
                    isset($parameters->{ServiceParametersHelper::INVOICE_TO_FOLLOW_PARAM})
                ) {
                    $shouldCheckTransportDocForSi = $parameters->{ServiceParametersHelper::INVOICE_TO_FOLLOW_PARAM}[0] == "no";
                }

                if ($shouldCheckTransportDocForPrSr || $shouldCheckTransportDocForSi) {
                    if (strlen($book->book_code . '-' . $voucher_no) > EInvoiceHelper::TRANPORTER_DOC_NO_MAX_LIMIT) {
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

        $user = request()->user();
        if ($user) {
            $data = ['user_id' => $user->id, 'user_type' => get_class($user), 'type' => $user->authenticable_type];
            return $data;
        }

        return [
            'user_id' => null,
            'user_type' => null,
            'type' => null
        ];
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

    public static function getGroupsData($groups, $startDate, $endDate, $organizations, $currency = 'org', $cost = null, $location = null)
    {
        $profitLoss = Helper::getReservesSurplus($startDate, $endDate, $organizations, 'trialBalance', $currency, $cost, $location);

        $fy = self::getFinancialYear($startDate);

        foreach ($groups as $master) {

            $allChildIds = $master->getAllChildIds();
            $allChildIds[] = $master->id;
            $allChildIds = Helper::getGroupsQuery(organizations: $organizations)
                ->whereIn('id', $allChildIds)->pluck('id')->toArray();
            $non_carry = Helper::getNonCarryGroups();
            if (in_array($master->id, $non_carry))
                $carry = 0;
            else
                $carry = 1;


            $ledgers = Ledger::where(function ($query) use ($allChildIds) {
                $query->whereIn('ledger_group_id', $allChildIds)
                    ->orWhere(function ($subQuery) use ($allChildIds) {
                        foreach ($allChildIds as $child) {
                            $subQuery->orWhereJsonContains('ledger_group_id', (string) $child)->orWhereJsonContains('ledger_group_id', $child);
                        }
                    });
            })->where('status', 1)->pluck('id')->toArray();


            $transactions = ItemDetail::whereIn('ledger_parent_id', $allChildIds)
                ->when($cost, function ($query) use ($cost) {
                    // $query->where('cost_center_id', $cost);
                    return is_array($cost)
                        ? $query->whereIn('cost_center_id', $cost)
                        : $query->where('cost_center_id', $cost);
                })
                ->whereIn('ledger_id', $ledgers)
                ->whereHas('voucher', function ($query) use ($organizations, $startDate, $endDate, $location) {
                    $query->whereBetween('document_date', [$startDate, $endDate]);
                    $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);
                    $query->when(!empty($organizations), function ($query) use ($organizations) {
                        $query->whereIn('organization_id', $organizations);
                    });
                    $query->when(!empty($location), function ($query) use ($location) {
                        $query->where('location', (int) $location);
                    });
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
                    // $query->where('cost_center_id', $cost);
                    return is_array($cost)
                        ? $query->whereIn('cost_center_id', $cost)
                        : $query->where('cost_center_id', $cost);
                })
                ->whereIn('ledger_id', $ledgers)
                ->whereHas('voucher', function ($query) use ($organizations, $startDate, $endDate, $fy, $carry, $cost, $location) {
                    $query->where('document_date', '<', $startDate);
                    if (!$carry)
                        $query->where('document_date', '>=', $fy['start_date']);
                    $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);
                    $query->when(!empty($organizations), function ($query) use ($organizations) {
                        $query->whereIn('organization_id', $organizations);
                    });
                    $query->when(!empty($location), function ($query) use ($location) {
                        $query->where('location', (int) $location);
                    });
                })
                ->selectRaw("SUM(debit_amt_{$currency}) as total_debit, SUM(credit_amt_{$currency}) as total_credit")
                ->first();


            $opening = $openingData->total_debit - $openingData->total_credit ?? 0;
            if ($master->name == "Liabilities") {
                if ($profitLoss['closing_type'] == "Dr")
                    $opening = $opening + $profitLoss['closingFinal'];
                else if ($profitLoss['closing_type'] == "Cr")
                    $opening = $opening - $profitLoss['closingFinal'];
            }
            $opening_type = $opening < 0 ? 'Cr' : 'Dr';
            $open = $openingData->total_debit - $openingData->total_credit;
            $closingText = '';
            $closing = $open + $totalMasterDebit - $totalMasterCredit;
            if ($closing != 0) {
                $closingText = $closing < 0 ? 'Cr' : 'Dr';
            }



            // Adding calculated totals to master group
            $master->group_id = $master->id;
            $master->total_credit = $totalMasterCredit;
            $master->total_debit = $totalMasterDebit;
            $master->opening = abs($opening);
            $master->open = $opening;
            $master->opening_type = $opening_type;
            $master->closing = $closing < 0 ? abs($closing) : $closing;
            $master->closing_type = $closing > 0 ? 'Dr' : 'Cr';

            unset($master->children);
        }

        return $groups;
    }

    public static function getBalanceSheetData($groups, $startDate, $endDate, $organizations, $type, $currency = "org", $cost = null, $location = null)
    {
        foreach ($groups as $master) {
            $allChildIds = $master->getAllChildIds();
            $allChildIds[] = $master->id;
            $allChildIds = Helper::getGroupsQuery($organizations)
                ->whereIn('id', $allChildIds)->pluck('id')->toArray();
            $allChildIds = Helper::getGroupsQuery($organizations)
                ->whereIn('id', $allChildIds)->pluck('id')->toArray();


            $ledgers = Ledger::where(function ($query) use ($allChildIds) {
                $query->whereIn('ledger_group_id', $allChildIds)
                    ->orWhere(function ($subQuery) use ($allChildIds) {
                        foreach ($allChildIds as $child) {
                            $subQuery->orWhereJsonContains('ledger_group_id', (string) $child)->orWhereJsonContains('ledger_group_id', $child);
                        }
                    });
            })->where('status', 1)->pluck('id')->toArray();


            $transactions = ItemDetail::whereIn('ledger_id', $ledgers)

                ->whereIn('ledger_parent_id', $allChildIds)
                ->whereHas('voucher', function ($query) use ($organizations, $startDate, $endDate, $location) {
                    $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);

                    $query->when(!empty($organizations), function ($query) use ($organizations) {
                        $query->whereIn('organization_id', $organizations);
                    });
                    $query->when(!empty($location), function ($query) use ($location) {
                        $query->where('location', $location);
                    });
                    $query->whereBetween('document_date', [$startDate, $endDate]);
                })
                ->when($cost, function ($query) use ($cost) {
                    // $query->where('cost_center_id', $cost);
                    return is_array($cost)
                        ? $query->whereIn('cost_center_id', $cost)
                        : $query->where('cost_center_id', $cost);
                })->get();

            // Calculate totals for the master group

            $creditField = "credit_amt_{$currency}";
            $debitField = "debit_amt_{$currency}";

            $totalMasterCredit = $transactions->sum(fn($t) => self::removeCommas($t->$creditField));
            $totalMasterDebit = $transactions->sum(fn($t) => self::removeCommas($t->$debitField));

            $openingData = ItemDetail::whereIn('ledger_id', $ledgers)
                ->when($cost, function ($query) use ($cost) {
                    // $query->where('cost_center_id', $cost);
                    return is_array($cost)
                        ? $query->whereIn('cost_center_id', $cost)
                        : $query->where('cost_center_id', $cost);
                })
                ->whereIn('ledger_parent_id', $allChildIds)
                ->whereHas('voucher', function ($query) use ($organizations, $startDate, $location) {
                    $query->where('document_date', '<', $startDate);
                    $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);

                    $query->when(!empty($organizations), function ($query) use ($organizations) {
                        $query->whereIn('organization_id', $organizations);
                    });
                    $query->when(!empty($location), function ($query) use ($location) {
                        $query->where('location', $location);
                    });
                })
                ->selectRaw("SUM(debit_amt_{$currency}) as total_debit, SUM(credit_amt_{$currency}) as total_credit")
                ->first();

            $openingt = $openingData->total_debit - $openingData->total_credit ?? 0;
            $opening = $openingData->total_credit - $openingData->total_debit ?? 0;





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
        // Remove any existing commas
        $number = str_replace(',', '', $number);

        // Ensure it's a float and keep two decimal places
        $number = number_format(floatval($number), 2, '.', '');

        // Split into whole and decimal parts
        $parts = explode('.', $number);
        $wholePart = $parts[0];
        $decimalPart = isset($parts[1]) ? $parts[1] : '00';

        // If length of whole part is less than or equal to 3, no formatting needed
        if (strlen($wholePart) <= 3) {
            return $wholePart . '.' . $decimalPart;
        }

        // Break into last 3 digits and the rest
        $lastThreeDigits = substr($wholePart, -3);
        $restOfTheNumber = substr($wholePart, 0, -3);

        // Format the rest with commas after every 2 digits
        $restOfTheNumber = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $restOfTheNumber);

        // Combine and return formatted number
        return $restOfTheNumber . ',' . $lastThreeDigits . '.' . $decimalPart;
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

    public static function getLedgerData($ledger_id, $startDate, $endDate, $companyId, $organization_id, $ledger_parent, $currency = "org", $cost = null, $location = null)
    {

        $itemVouchers = ItemDetail::where('ledger_id', $ledger_id)
            ->when($cost, function ($query) use ($cost) {
                // $query->where('cost_center_id', $cost)
                return is_array($cost)
                    ? $query->whereIn('cost_center_id', $cost)
                    : $query->where('cost_center_id', $cost);;
            })
            ->where('ledger_parent_id', $ledger_parent)
            ->whereHas('voucher', function ($query) use ($organization_id, $startDate, $endDate, $location) {

                $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);
                $query->where('organization_id', $organization_id);
                $query->whereBetween('document_date', [$startDate, $endDate]);
                $query->when(!empty($location), function ($query) use ($location) {
                    $query->where('location', (int) $location);
                });
            })->pluck('voucher_id')
            ->toArray();




        $data = Voucher::whereIn('id', $itemVouchers)
            ->where('organization_id', $organization_id)
            ->whereNotNull('approvalStatus')
            ->whereBetween('document_date', [$startDate, $endDate])
            ->when(!empty($location), function ($query) use ($location) {
                $query->where('location', (int) $location);
            })
            ->with([
                'items' => function ($it) use ($currency, $cost) {
                    $it->when($cost, function ($query) use ($cost) {
                        // $query->where('cost_center_id', $cost);
                        return is_array($cost)
                            ? $query->whereIn('cost_center_id', $cost)
                            : $query->where('cost_center_id', $cost);
                    });
                    $it->select('id', "debit_amt_{$currency} as debit_amt", "credit_amt_{$currency} as credit_amt", 'voucher_id', 'ledger_id', 'ledger_parent_id')->with([
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

    public static function getBalanceSheetLedgers($group_id, $startDate, $endDate, $organizations, $currency = "org", $cost = null, $location = null)
    {

        $liabilities_group = Helper::getGroupsQuery($organizations)->where('name', "Liabilities")
            ->value('id');



        $assets_group = Helper::getGroupsQuery($organizations)->where('name', "Assets")
            ->value('id');

        $liabilities = Helper::getGroupsQuery($organizations)
            ->where('parent_group_id', $liabilities_group)
            ->pluck('id')->toArray();

        $type = "assets";
        if (in_array($group_id, $liabilities)) {
            $type = "liabilities";
        }
        $group = Group::find($group_id);
        $childrens = $group->getAllChildIds();
        $childrens[] = $group->id;
        $childrens = Helper::getGroupsQuery($organizations)
            ->whereIn('id', $childrens)->pluck('id')->toArray();


        $data = Ledger::where(function ($query) use ($childrens) {
            $query->whereIn('ledger_group_id', $childrens)
                ->orWhere(function ($subQuery) use ($childrens) {
                    foreach ($childrens as $child) {
                        $subQuery->orWhereJsonContains('ledger_group_id', (string) $child)->orWhereJsonContains('ledger_group_id', $child);
                    }
                });
        })->where('status', 1)
            ->select('id', 'name', 'ledger_group_id')
            ->withSum([
                'details as details_sum_debit_amt' => function ($query) use ($startDate, $endDate, $childrens, $cost, $organizations, $location) {
                    $query->whereIn('ledger_parent_id', $childrens)
                        ->when($cost, function ($query) use ($cost) {
                            // $query->where('cost_center_id', $cost);
                            return is_array($cost)
                                ? $query->whereIn('cost_center_id', $cost)
                                : $query->where('cost_center_id', $cost);
                        })
                        ->withwhereHas('voucher', function ($query) use ($startDate, $endDate, $organizations, $location) {

                            $query->when(!empty($organizations), function ($query) use ($organizations) {
                                $query->whereIn('organization_id', $organizations);
                            });
                            $query->when(!empty($location), function ($query) use ($location) {
                                $query->where('location', $location);
                            });

                            $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);
                            $query->whereBetween('document_date', [$startDate, $endDate]);
                            $query->orderBy('document_date', 'asc');
                        });
                }
            ], "debit_amt_{$currency}")
            ->withSum([
                'details as details_sum_credit_amt' => function ($query) use ($startDate, $endDate, $childrens, $cost, $organizations, $location) {
                    $query->whereIn('ledger_parent_id', $childrens)
                        ->when($cost, function ($query) use ($cost) {
                            // $query->where('cost_center_id', $cost);
                            return is_array($cost)
                                ? $query->whereIn('cost_center_id', $cost)
                                : $query->where('cost_center_id', $cost);
                        })
                        ->withwhereHas('voucher', function ($query) use ($startDate, $endDate, $organizations, $location) {

                            $query->when(!empty($organizations), function ($query) use ($organizations) {
                                $query->whereIn('organization_id', $organizations);
                            });
                            $query->when(!empty($location), function ($query) use ($location) {
                                $query->where('location', $location);
                            });
                            $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);
                            $query->whereBetween('document_date', [$startDate, $endDate]);
                            $query->orderBy('document_date', 'asc');
                        });
                }
            ], "credit_amt_{$currency}")
            ->with([
                'details' => function ($query) use ($startDate, $endDate, $childrens, $cost, $organizations, $location) {
                    $query->whereIn('ledger_parent_id', $childrens)
                        ->when($cost, function ($query) use ($cost) {
                            // $query->where('cost_center_id', $cost);
                            return is_array($cost)
                                ? $query->whereIn('cost_center_id', $cost)
                                : $query->where('cost_center_id', $cost);
                        })
                        ->withwhereHas('voucher', function ($query) use ($startDate, $endDate, $organizations, $location) {

                            $query->when(!empty($organizations), function ($query) use ($organizations) {
                                $query->whereIn('organization_id', $organizations);
                            });
                            $query->when(!empty($location), function ($query) use ($location) {
                                $query->where('location', $location);
                            });
                            $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);
                            $query->whereBetween('document_date', [$startDate, $endDate]);
                            $query->orderBy('document_date', 'asc');
                        });
                }
            ])
            ->get()
            ->map(function ($ledger) use ($type, $childrens, $organizations, $startDate, $currency, $cost, $location) {
                // Set to 0 if the sum is null
                $details_sum_debit_amt = $ledger->details_sum_debit_amt ?? 0;
                $details_sum_credit_amt = $ledger->details_sum_credit_amt ?? 0;

                $openingData = ItemDetail::where('ledger_id', $ledger->id)
                    ->when($cost, function ($query) use ($cost) {
                        // $query->where('cost_center_id', $cost);
                        return is_array($cost)
                            ? $query->whereIn('cost_center_id', $cost)
                            : $query->where('cost_center_id', $cost);
                    })
                    ->whereIn('ledger_parent_id', $childrens)
                    ->whereHas('voucher', function ($query) use ($organizations, $startDate, $location) {
                        $query->where('document_date', '<', $startDate);
                        $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);

                        $query->when(!empty($organizations), function ($query) use ($organizations) {
                            $query->whereIn('organization_id', $organizations);
                        });
                        $query->when(!empty($location), function ($query) use ($location) {
                            $query->where('location', $location);
                        });
                    })
                    ->selectRaw("SUM(debit_amt_{$currency}) as total_debit, SUM(credit_amt_{$currency}) as total_credit")
                    ->first();

                $openingt = $openingData->total_debit - $openingData->total_credit ?? 0;
                $opening = $openingData->total_credit - $openingData->total_debit ?? 0;



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

                    $ledger->ledger_group_id = (int) $filteredValue ?? null; // Use found value or null if no match
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
        $amendDelete = false;
        $delete = false;
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
            if ($user->auth_user_id === $createdBy) {
                $draft = true;
                $submit = true;
            }
            if ($revisionNumber == 0 && $createdBy === $user->auth_user_id) {
                $delete = true;
            }
        }
        if ($docStatus == ConstantHelper::SUBMITTED) {
            $approvalWorkflow = BookLevel::where('book_id', $book->id)
                ->where('organization_id', $user->organization_id)
                ->where('level', 1)
                ->where('min_value', '<=', $docValue)
                ->whereHas('approvers', function ($approver) use ($user) {
                    $approver->where('user_id', $user->auth_user_id);
                    // ->where('user_type', $currUser['type']);
                })
                ->orderByDesc('min_value')
                ->first();


            if ($approvalWorkflow) {
                $approve = true;
            }
            //Creator of document cannot approve
            // if ($user->auth_user_id === $createdBy && self::userCheck()['type'] == $creatorType) {

            // dd($user->auth_user_id, $createdBy);
            if ($user->auth_user_id === $createdBy) {
                $approve = false;
                $revoke = true;
            }
        }
        if ($docStatus == ConstantHelper::APPROVED || $docStatus == ConstantHelper::APPROVAL_NOT_REQUIRED) {
            $amendmentWorkflow = AmendmentWorkflow::where('book_id', $book->id)
                ->where('organization_id', $user->organization_id)
                ->whereHas('approvers', function ($approvers) use ($user) {
                    $approvers->where('user_id', $user->auth_user_id);
                    // ->where('user_type', $currUser['type']);
                })
                ->where('min_value', '<=', $docValue)
                ->orderByDesc('min_value')
                ->first();
            if ($amendmentWorkflow) {
                $amend = true;
            }

            if ($bookTypeServiceAlias == ConstantHelper::MO_SERVICE_ALIAS) {
                $close = true;
            } else {
                $postingRequiredParam = OrganizationBookParameter::where('book_id', $bookId)
                    ->where('parameter_name', ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM)->first();
                if (isset($postingRequiredParam)) {
                    $isPostingRequired = ($postingRequiredParam->parameter_value[0] ?? '') === "yes" ? true : false;
                    $post = $isPostingRequired;
                }
            }
            if ($revisionNumber == 0 && $user->auth_user_id === $createdBy) {
                $amendDelete = true;
            }
            $print = true;
        }
        if ($docStatus == ConstantHelper::PARTIALLY_APPROVED) {
            $approvalWorkflow = BookLevel::where('book_id', $book->id)
                ->where('organization_id', $user->organization_id)
                ->where('min_value', '<=', $docValue)
                ->whereHas('approvers', function ($approver) use ($user) {
                    $approver->where('user_id', $user->auth_user_id);
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

        // $amend=true;

        return [
            'draft' => $draft,
            'submit' => $submit,
            'approve' => $approve,
            'delete' => $delete,
            'amend' => $amend,
            'amendDelete' => $amendDelete,
            'post' => $post,
            'voucher' => $voucher,
            'revoke' => $revoke,
            'close' => $close,
            'print' => $print,
            'user' => $user

        ];
    }
    public static function actionButtonDisplayLedger($bookId, $docStatus, $docId, $docValue, $docApprLevel, int $createdBy = 0, $creatorType = 'user', $revisionNumber = 0)
    {
        $draft = false;
        $submit = false;
        $approve = false;
        $amend = false;
        $amendDelete = false;
        $delete = false;
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
            if ($user->auth_user_id === $createdBy) {
                $draft = true;
                $submit = true;
            }
            if ($revisionNumber == 0 && $createdBy === $user->auth_user_id) {
                $delete = true;
            }
        }
        if ($docStatus == ConstantHelper::SUBMITTED || $docStatus == ConstantHelper::REJECTED) {
            if ($revisionNumber == 0 && $createdBy === $user->auth_user_id) {
                $delete = true;
            }
        }
        if ($docStatus == ConstantHelper::DRAFT) {
            if ($revisionNumber == 1 && $createdBy === $user->auth_user_id) {
                $delete = true;
            }
        }
        if ($docStatus == ConstantHelper::SUBMITTED) {
            $approvalWorkflow = BookLevel::where('book_id', $book->id)
                ->where('organization_id', $user->organization_id)
                ->where('level', 1)
                ->where('min_value', '<=', $docValue)
                ->whereHas('approvers', function ($approver) use ($user) {
                    $approver->where('user_id', $user->auth_user_id);
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
                ->whereHas('approvers', function ($approvers) use ($user) {
                    $approvers->where('user_id', $user->auth_user_id);
                    // ->where('user_type', $currUser['type']);
                })
                ->where('min_value', '<=', $docValue)
                ->orderByDesc('min_value')
                ->first();
            if ($amendmentWorkflow) {
                $amend = true;
            }

            if ($bookTypeServiceAlias == ConstantHelper::MO_SERVICE_ALIAS) {
                $close = true;
            } else {
                $postingRequiredParam = OrganizationBookParameter::where('book_id', $bookId)
                    ->where('parameter_name', ServiceParametersHelper::GL_POSTING_REQUIRED_PARAM)->first();
                if (isset($postingRequiredParam)) {
                    $isPostingRequired = ($postingRequiredParam->parameter_value[0] ?? '') === "yes" ? true : false;
                    $post = $isPostingRequired;
                }
            }
            if ($revisionNumber == 0 && $user->auth_user_id === $createdBy) {
                $amendDelete = true;
            }
            $print = true;
        }
        if ($docStatus == ConstantHelper::PARTIALLY_APPROVED) {
            $approvalWorkflow = BookLevel::where('book_id', $book->id)
                ->where('organization_id', $user->organization_id)
                ->where('min_value', '<=', $docValue)
                ->whereHas('approvers', function ($approver) use ($user) {
                    $approver->where('user_id', $user->auth_user_id);
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
            'delete' => $delete,
            'amend' => $amend,
            'amendDelete' => $amendDelete,
            'post' => $post,
            'voucher' => $voucher,
            'revoke' => $revoke,
            'close' => $close,
            'print' => $print,
            'user' => $user

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
                ->whereHas('approvers', function ($approver) use ($user) {
                    $approver->where('user_id', $user->auth_user_id);
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
                ->whereHas('approvers', function ($approver) use ($user) {
                    $approver->where('user_id', $user->auth_user_id);
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
                ->whereHas('approvers', function ($approver) use ($user) {
                    $approver->where('user_id', $user->auth_user_id);
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
                        $reject = true;
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
                ->whereHas('approvers', function ($approver) use ($user) {
                    $approver->where('user_id', $user->auth_user_id);
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


        if ($userCheck['user_id'] === $createdBy && $userCheck['type'] === $creatorType) {
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
            'voucher' => false
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
                ->whereHas('approvers', function ($approver) use ($user) {
                    $approver->where('user_id', $user->auth_user_id);
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
                ->whereHas('approvers', function ($approver) use ($user) {
                    $approver->where('user_id', $user->auth_user_id);
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
        if ($user->auth_user_id === $createdBy) {
            $buttons['approve'] = false;
        }

        return $buttons;
    }

    public static function approveDocument($bookId, $docId, $revisionNumber, $remarks, $attachments, $currentLevel, $actionType, $docValue = 0, $modelName = null, $documentType = NULL)
    {
        $user = self::getAuthenticatedUser();
        $book = Book::where('id', $bookId)->first();
        $bookTypeServiceAlias = $book?->master_service->alias;
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
        $docApproval->user_type = $user->authenticable_type;
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
            $createdBy = $document?->created_by;
            if (isset($document) && isset($document->document_status)) {
                if ($actionType == ConstantHelper::REVOKE && $document->document_status != ConstantHelper::SUBMITTED) {
                    $message = "Can't Revoke. Document is already Approved/Rejected";
                }
                if (($actionType == "approve" || $actionType == "reject") && $document->document_status == ConstantHelper::DRAFT) {
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
                })->where('user_id', '!=', $createdBy)
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
                    if ($bookTypeServiceAlias != ConstantHelper::MO_SERVICE_ALIAS) {
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
            if ($bookTypeServiceAlias == ConstantHelper::TR_SERVICE_ALIAS) {
                $nextLevel = $currentLevel;
                $approvalStatus = ConstantHelper::COMPLETED;
            }
        }
        if ($actionType == 'auto-closed' || $actionType == 'closed') {
            if ($bookTypeServiceAlias == ConstantHelper::TR_SERVICE_ALIAS) {
                $nextLevel = $currentLevel;
                $approvalStatus = ConstantHelper::CLOSED;
            }
        }
        if ($actionType == 'shortlist') {
            if ($bookTypeServiceAlias == ConstantHelper::TR_SERVICE_ALIAS) {
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
                })->where('user_id', '!=', $createdBy)
                ->get();
            if (count($approvalWorkflow) == 0) {
                $approvalStatus = ConstantHelper::APPROVAL_NOT_REQUIRED;
            } else {
                $approvalStatus = self::checkApprovalRequired($book->id, $docValue);
            }
            if ($approvalStatus === ConstantHelper::APPROVAL_NOT_REQUIRED) {
                if ($bookTypeServiceAlias != ConstantHelper::MO_SERVICE_ALIAS) {
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
                $approvalWorkflow = ApprovalWorkflow::where('book_id', $book->id)
                    ->where('organization_id', $user->organization_id)
                    ->whereHas('level')->get();
                if (count($approvalWorkflow) == 0) {
                    $approvalStatus = ConstantHelper::APPROVAL_NOT_REQUIRED;
                } else {
                    $approvalStatus = ConstantHelper::SUBMITTED;
                }
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
            if ($bookTypeServiceAlias = ConstantHelper::MO_SERVICE_ALIAS) {
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
                $custom->user_type = $authUser?->user_type;

                $custom->setRawAttributes([
                    'user_id' => $approvalAr,
                    'name' => $userName ?? null,
                    'approval_date' => null,
                    'remarks' => null,
                    'approval_type' => 'pending',
                    'user_type' => $authUser?->user_type
                ], true);

                $data[] = $custom;
            }
        }

        $history = DocumentApproval::select('id', 'user_id', 'remarks', 'approval_type', 'user_type', 'created_at')
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

    public static function getTrialBalanceGroupLedgers($group_id, $startDate, $endDate, $organizations, $currency = "org", $cost = null, $location = null)
    {
        $non_carry = Helper::getNonCarryGroups();
        if (in_array($group_id, $non_carry))
            $carry = 0;
        else
            $carry = 1;

        $fy = self::getFinancialYear($startDate);


        $groups = Helper::getGroupsQuery($organizations)
            ->where('parent_group_id', $group_id)
            ->pluck('id');


        $datas = Group::whereIn('id', $groups)->get();



        if (self::checkCount($datas) > 0) {
            $type = 'group';
            $data = self::getGroupsData($datas, $startDate, $endDate, $organizations, $currency, $cost, $location);
        } else {
            $type = 'ledger';

            $data = Ledger::where(function ($query) use ($group_id) {
                $query->whereJsonContains('ledger_group_id', (string) $group_id)
                    ->orWhere('ledger_group_id', $group_id);
            })->where('status', 1)->select('id', 'name')
                ->with([
                    'details' => function ($query) use ($startDate, $endDate, $group_id, $cost, $organizations, $location) {
                        $query->where('ledger_parent_id', $group_id)
                            ->when(!empty($cost), function ($query) use ($cost) {
                                return is_array($cost)
                                    ? $query->whereIn('cost_center_id', $cost)
                                    : $query->where('cost_center_id', $cost);
                                // $query->where('cost_center_id', $cost);
                            })
                            ->withwhereHas('voucher', function ($query) use ($startDate, $endDate, $organizations, $location) {

                                $query->when(!empty($organizations), function ($query) use ($organizations, $location) {
                                    $query->whereIn('organization_id', $organizations);
                                });
                                $query->when(!empty($location), function ($query) use ($location) {
                                    $query->where('location', $location);
                                });
                                $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);
                                $query->orderBy('document_date', 'asc');
                                $query->whereBetween('document_date', [$startDate, $endDate]);
                            });
                    }
                ])->withSum([
                    'details as details_sum_debit_amt' => function ($query) use ($startDate, $endDate, $group_id, $cost, $organizations, $location) {
                        $query->where('ledger_parent_id', $group_id)
                            ->when(!empty($cost), function ($query) use ($cost) {
                                // dd($cost);
                                // $query->where('cost_center_id', $cost);
                                return is_array($cost)
                                    ? $query->whereIn('cost_center_id', $cost)
                                    : $query->where('cost_center_id', $cost);
                            })
                            ->withwhereHas('voucher', function ($query) use ($startDate, $endDate, $organizations, $location) {

                                $query->when(!empty($organizations), function ($query) use ($organizations) {
                                    $query->whereIn('organization_id', $organizations);
                                });
                                $query->when(!empty($location), function ($query) use ($location) {
                                    $query->where('location', $location);
                                });

                                $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);
                                $query->orderBy('document_date', 'asc');
                                $query->whereBetween('document_date', [$startDate, $endDate]);
                            });
                    }
                ], "debit_amt_{$currency}")
                ->withSum([
                    'details as details_sum_credit_amt' => function ($query) use ($startDate, $endDate, $group_id, $cost, $organizations, $location) {
                        $query->where('ledger_parent_id', $group_id)
                            ->when(!empty($cost), function ($query) use ($cost) {
                                // $query->where('cost_center_id', $cost);
                                return is_array($cost)
                                    ? $query->whereIn('cost_center_id', $cost)
                                    : $query->where('cost_center_id', $cost);
                            })
                            ->withwhereHas('voucher', function ($query) use ($startDate, $endDate, $organizations, $location) {

                                $query->when(!empty($organizations), function ($query) use ($organizations) {
                                    $query->whereIn('organization_id', $organizations);
                                });
                                $query->when(!empty($location), function ($query) use ($location) {
                                    $query->where('location', $location);
                                });
                                $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);
                                $query->orderBy('document_date', 'asc');
                                $query->whereBetween('document_date', [$startDate, $endDate]);
                            });
                    }
                ], "credit_amt_{$currency}")
                ->get()
                ->map(function ($ledger) use ($group_id, $organizations, $startDate, $endDate, $currency, $fy, $carry, $cost, $location) {

                    $openingData = ItemDetail::where('ledger_parent_id', $group_id)
                        ->when(!empty($cost), function ($query) use ($cost) {
                            // $query->where('cost_center_id', $cost);
                            return is_array($cost)
                                ? $query->whereIn('cost_center_id', $cost)
                                : $query->where('cost_center_id', $cost);
                        })
                        ->where('ledger_id', $ledger->id)
                        ->whereHas('voucher', function ($query) use ($organizations, $startDate, $endDate, $fy, $carry, $location) {
                            $query->where('document_date', '<', $startDate);
                            if (!$carry)
                                $query->where('document_date', '>=', $fy['start_date']);
                            $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);

                            $query->when(!empty($organizations), function ($query) use ($organizations) {
                                $query->whereIn('organization_id', $organizations);
                            });
                            $query->when(!empty($location), function ($query) use ($location) {
                                $query->where('location', $location);
                            });
                        })
                        ->selectRaw("SUM(debit_amt_{$currency}) as total_debit, SUM(credit_amt_{$currency}) as total_credit")
                        ->first();

                    $opening = $openingData->total_debit - $openingData->total_credit ?? 0;
                    $opening_type = ($openingData->total_debit > $openingData->total_credit) ? 'Dr' : 'Cr';
                    if ($ledger->details_sum_debit_amt == 0 && $ledger->details_sum_credit_amt == 0) {
                        $voucher = ItemDetail::where('ledger_id', $ledger->id)->with('vouchers');
                        Log::info('Zero-sum ledger detected', [
                            'ledger_id' => $ledger->id,
                            'vouchers' => $voucher,
                            'ledger_name' => $ledger->name,
                            'group_id' => $group_id ?? 'N/A',
                        ]);
                    }


                    // Set to 0 if the sum is null
                    $ledger->details_sum_debit_amt = $ledger->details_sum_debit_amt ?? 0;
                    $ledger->details_sum_credit_amt = $ledger->details_sum_credit_amt ?? 0;
                    $closing = $opening + ($ledger->details_sum_debit_amt - $ledger->details_sum_credit_amt);

                    $ledger->opening = abs($opening);
                    $ledger->open = $opening;
                    $ledger->closing = abs($closing);
                    $ledger->closing_type = $closing < 0 ? "Cr" : "Dr";
                    $ledger->opening_type = $opening_type;
                    $ledger->group_id = $group_id; // Default type if no details exist

                    unset($ledger->details);

                    return $ledger;
                });
        }

        return ['type' => $type, 'data' => $data, 'date0' => $startDate, 'date1' => $endDate];
    }
    public static function getReservesSurplus($startDate, $endDate, $organizations, $type, $currency = "org", $cost = null, $location = null)
    {
        // Get previous day from current start date

        $data = self::getPlGroupsData($startDate, $endDate, $organizations, $currency, $cost, null, $location);
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

    public static function getPlGroupsData($startDate, $endDate, $organizations, $currency = "org", $cost = null, $type = null, $location = null)
    {

        $data = PLGroups::select('id', 'name', 'group_ids')->get()->map(function ($plGroup) use ($type, $cost, $startDate, $endDate, $organizations, $currency, $location) {
            $groups = Helper::getGroupsQuery($organizations)->whereIn('id', $plGroup->group_ids)
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
                $allChildIds = Helper::getGroupsQuery($organizations)
                    ->whereIn('id', $allChildIds)->pluck('id')->toArray();


                $ledgers = Ledger::where(function ($query) use ($allChildIds) {
                    $query->whereIn('ledger_group_id', $allChildIds)
                        ->orWhere(function ($subQuery) use ($allChildIds) {
                            foreach ($allChildIds as $child) {
                                $subQuery->orWhereJsonContains('ledger_group_id', (string) $child)->orWhereJsonContains('ledger_group_id', $child);
                            }
                        });
                })->where('status', 1)->pluck('id')->toArray();
                $non_carry = Helper::getNonCarryGroups();
                $fy = self::getFinancialYear($startDate);


                if (in_array($master->id, $non_carry))
                    $carry = 0;
                else
                    $carry = 1;
                $transactions = ItemDetail::whereIn('ledger_id', $ledgers)
                    ->whereIn('ledger_parent_id', $allChildIds)
                    ->when($cost, function ($query) use ($cost) {
                        return is_array($cost)
                            ? $query->whereIn('cost_center_id', $cost)
                            : $query->where('cost_center_id', $cost);
                    })
                    ->whereHas('voucher', function ($query) use ($organizations, $startDate, $endDate, $carry, $fy, $location) {
                        $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);

                        $query->when(!empty($organizations), function ($query) use ($organizations) {
                            $query->whereIn('organization_id', $organizations);
                        });
                        $query->when(!empty($location), function ($query) use ($location) {
                            $query->where('location', $location);
                        });
                        $query->whereBetween('document_date', [$startDate, $endDate]);
                    })->get();


                // Calculate totals
                $creditField = "credit_amt_{$currency}";
                $debitField = "debit_amt_{$currency}";



                $transactionsOpen = ItemDetail::whereIn('ledger_id', $ledgers)
                    ->whereIn('ledger_parent_id', $allChildIds)
                    ->when($cost, function ($query) use ($cost) {
                        return is_array($cost)
                            ? $query->whereIn('cost_center_id', $cost)
                            : $query->where('cost_center_id', $cost);
                    })
                    ->whereHas('voucher', function ($query) use ($organizations, $startDate, $endDate, $carry, $fy, $location) {
                        $query->where('document_date', '<', $startDate);
                        //if(!$carry)
                        //$query->where('document_date', '>=', $fy['start_date']);
                        $query->whereIn('approvalStatus', ConstantHelper::DOCUMENT_STATUS_APPROVED);

                        $query->when(!empty($organizations), function ($query) use ($organizations) {
                            $query->whereIn('organization_id', $organizations);
                        });
                        $query->when(!empty($location), function ($query) use ($location) {
                            $query->where('location', $location);
                        });
                    })->get();

                $totalCreditOpen += $transactionsOpen->sum(fn($t) => self::removeCommas($t->$creditField));
                $totalDebitOpen += $transactionsOpen->sum(fn($t) => self::removeCommas($t->$debitField));


                $totalCredit += $transactions->sum(fn($t) => self::removeCommas($t->$creditField));
                $totalDebit += $transactions->sum(fn($t) => self::removeCommas($t->$debitField));


                unset($master->children);
            }
            $opening = $totalDebitOpen - $totalCreditOpen;
            $plGroup->opening = $opening;
            $plGroup->totalCredit = $totalCredit;
            $plGroup->totalDebit = $totalDebit;




            $closingText = '';
            if ($type == "profitloss")
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
            switch ($group->name) {
                case "Opening Stock":
                    $totalPurchase += $group->closing;
                    $opening = $group->closing;
                    break;
                case "Purchase Accounts":
                    $totalPurchase += $group->closing;
                    $purchase = $group->closing;
                    break;
                case "Direct Expenses":
                    $totalPurchase += $group->closing;
                    $directExpense = $group->closing;
                    break;
                case "Indirect Expenses":
                    $purchaseInd += $group->closing;
                    $indirectExpense = $group->closing;
                    break;
                case "Sales Accounts":
                    $totalSales += $group->closing;
                    $salesAccount = $group->closing;
                    break;
                case "Direct Income":
                    $totalSales += $group->closing;
                    $directIncome = $group->closing;
                    break;
                case "Indirect Income":
                    $saleInd += $group->closing;
                    $indirectIncome = $group->closing;
                    break;
            }
        }

        $difference = $totalSales - $totalPurchase;
        $diffVal = abs($difference);

        // Calculate Gross Profit/Loss
        if ($totalSales >= $totalPurchase) {
            $grossProfit = $diffVal;
            $subTotal = $totalSales;
        } else {
            $grossLoss = $diffVal;
            $subTotal = $totalPurchase;
        }

        // Calculate Gross Profit or Loss
        $grossProfit = ($salesAccount + $directIncome + $opening) - ($opening + $purchase + $directExpense);
        $grossLoss = 0;
        if ($grossProfit < 0) {
            $grossLoss = abs($grossProfit);
            $grossProfit = 0;
        }

        // Calculate Net Profit or Loss
        if ($grossProfit > 0) {
            $net = $grossProfit + $indirectIncome - $indirectExpense;
            if ($net >= 0) {
                $netProfit = $net;
            } else {
                $netLoss = abs($net);
            }
        } elseif ($grossLoss > 0) {
            $net = $indirectIncome - ($grossLoss + $indirectExpense);
            if ($net >= 0) {
                $netProfit = $net;
            } else {
                $netLoss = abs($net);
            }
        } else {
            // Only indirect values exist
            $net = $indirectIncome - $indirectExpense;
            if ($net >= 0) {
                $netProfit = $net;
            } else {
                $netLoss = abs($net);
            }
        }

        // Subtotal (Gross Profit side)
        $subTotal = max($grossProfit, $grossLoss);

        if ($netProfit > 0) {
            $saleInd = $subTotal + $indirectIncome;
            $purchaseInd = $subTotal + $indirectExpense;
        } elseif ($netLoss > 0) {
            $saleInd = $subTotal + $indirectIncome + $netLoss;
            $purchaseInd = $subTotal + $indirectExpense;
        }
        $overAllTotal = max($saleInd, $purchaseInd);

        return [
            'salesInd' => $saleInd,
            'purchaseInd' => $purchaseInd,
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

        $authUser = request()->user();

        $ck = "iam:{$authUser->group_id}:{$authUser->id}";
        $ttl = 1200; // 20 minutes; adjust or use forever with manual busting

        $user = app(TagCacheInterface::class)->remember(
            key: $ck . ':get-authenticated-user',
            ttl: $ttl,
            callback: function () use ($authUser) {
                $user = $authUser->authUser();
                $user->authenticable_type = $authUser->authenticable_type;
                $user->auth_user_id = $authUser->id;
                $user->db_name = $authUser->db_name;
                // $user->current_organization_id = $authUser->organization_id;
                return $user;
            },
            tags: [
                'get-authenticated-user',
                "group:{$authUser->group_id}",
                "user:{$authUser->id}",
            ]
        );

        return $user;
    }

    public static function getOrgWiseUserAndEmployees($organizationId)
    {
        $employeeIds = Employee::where(function ($query) use ($organizationId) {
            $query->whereHas('access_rights_org', function ($subQuery) use ($organizationId) {
                $subQuery->where('organization_id', $organizationId);
            })->orWhere('organization_id', $organizationId);
        })->get()->pluck('id');

        $userIds = User::where(function ($query) use ($organizationId) {
            $query->whereHas('access_rights_org', function ($subQuery) use ($organizationId) {
                $subQuery->where('organization_id', $organizationId);
            })->orWhere('organization_id', $organizationId);
        })->get()->pluck('id');

        $user = self::getAuthenticatedUser();
        $employees = AuthUser::where('db_name', $user->db_name)
            ->where(function ($subQuery) use ($employeeIds, $userIds) {
                $subQuery->where(function ($empQuery) use ($employeeIds) {
                    $empQuery->where('authenticable_type', 'employee')->whereIn('authenticable_id', $employeeIds);
                })->orWhere(function ($userQuery) use ($userIds) {
                    $userQuery->where('authenticable_type', 'user')->whereIn('authenticable_id', $userIds);
                });
            })->whereNotIn('user_type', [ConstantHelper::IAM_VENDOR_USER, ConstantHelper::IAM_ROOT_USER])
            ->where('status', ConstantHelper::ACTIVE)
            ->get();
        return $employees;
    }

    public static function getOrganizationLogo($organizationId)
    {

        $logoUrl = "";
        $organization = Organization::find($organizationId);
        if ($organization && $organization->organization_logo) {
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
    public static function getModelFromServiceAlias($alias)
    {
        $baseNamespace = "App\\Models\\";

        // Get the model name from the mapping array
        $modelName = ConstantHelper::SERVICE_ALIAS_MODELS[$alias] ?? null;
        if ($modelName) {
            $modelClass = $baseNamespace . $modelName;
            if (class_exists($modelClass)) {
                return $modelClass;
            } else
                return null;
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

                    if ($table['model_type'] == 'detail' && is_array($headerHistoryId) && count($headerHistoryId)) {
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
        try {
            $arr = [];

            foreach ($modelObj as $modelOb) {
                $modelData = $modelOb->getRawOriginal();
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

                //compliances
                if (method_exists($modelOb, 'compliances') && $modelOb->compliances()->exists()) {
                    $compliance = $modelOb->compliances;

                    Compliance::create([
                        'morphable_id' => $insertedHistoryId,
                        'morphable_type' => $HistoryModelInstance::class,
                        'country_id' => $compliance->country_id,
                        'tds_applicable' => $compliance->tds_applicable,
                        'wef_date' => $compliance->wef_date,
                        'tds_certificate_no' => $compliance->tds_certificate_no,
                        'tds_tax_percentage' => $compliance->tds_tax_percentage,
                        'tds_category' => $compliance->tds_category,
                        'tds_value_cab' => $compliance->tds_value_cab,
                        'tan_number' => $compliance->tan_number,
                        'gst_applicable' => $compliance->gst_applicable,
                        'gstin_no' => $compliance->gstin_no,
                        'gst_registered_name' => $compliance->gst_registered_name,
                        'gstin_registration_date' => $compliance->gstin_registration_date,
                        'msme_registered' => $compliance->msme_registered,
                        'msme_no' => $compliance->msme_no,
                        'msme_type' => $compliance->msme_type,
                        'gst_certificate' => $compliance->gst_certificate,
                        'msme_certificate' => $compliance->msme_certificate,
                        'status' => $compliance->status,
                    ]);
                }
                //bank Info
                if (method_exists($modelOb, 'bankInfos') && $modelOb->bankInfos()->exists()) {
                    $bankInfos = $modelOb->bankInfos;

                    foreach ($bankInfos as $bankInfo) {
                        BankInfo::create([
                            'morphable_id' => $insertedHistoryId,
                            'morphable_type' => $HistoryModelInstance::class,
                            'bank_name' => $bankInfo->bank_name ?? null,
                            'beneficiary_name' => $bankInfo->beneficiary_name ?? null,
                            'account_number' => $bankInfo->account_number ?? null,
                            're_enter_account_number' => $bankInfo->re_enter_account_number ?? null,
                            'ifsc_code' => $bankInfo->ifsc_code ?? null,
                            'primary' => $bankInfo->primary ?? null,
                            'cancel_cheque' => $bankInfo->cancel_cheque ?? null,
                            'status' => $bankInfo->status ?? null,
                        ]);
                    }
                }

                //contact
                if (method_exists($modelOb, 'contacts') && $modelOb->contacts()->count()) {
                    foreach ($modelOb->contacts as $contact) {
                        Contact::create([
                            'contactable_id' => $insertedHistoryId,
                            'contactable_type' => $HistoryModelInstance::class,
                            'primary' => $contact->primary,
                            'salutation' => $contact->salutation,
                            'name' => $contact->name,
                            'email' => $contact->email,
                            'mobile' => $contact->mobile,
                            'phone' => $contact->phone,
                            'status' => $contact->status,
                        ]);
                    }
                }

                //Note
                if (method_exists($modelOb, 'notes') && $modelOb->notes()->count()) {
                    foreach ($modelOb->notes as $note) {
                        $note = Note::create([
                            'noteable_id' => $insertedHistoryId,
                            'noteable_type' => $HistoryModelInstance::class,
                            'remark' => $note->remark,
                            'created_by_type' => $note->created_by_type,
                            'created_by' => $note->created_by,
                        ]);
                    }
                }
            }
            return $arr;
        } catch (Exception $e) {
            $error = $e->getMessage();
            Log::error("documentAmendment Error: $error");
        }
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

    public static function getAccessibleServicesFromMenuAlias(string $menuAlias, string $selectedServiceAlias = '', $authUser = null): array
    {
        $authUser = $authUser ?: Helper::getAuthenticatedUser();
        $organizationMenu = OrganizationMenu::withDefaultGroupCompanyOrg()->where([
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
                $services = EmployeeBookMapping::where('service_menu_id', $organizationMenu?->serviceMenu?->id)->where('employee_id', $authUser->auth_user_id)->first();
                if (!isset($services)) { //Assign all services and books data if no record is found
                    $serviceIds = $organizationMenu?->serviceMenu?->erp_service_id ?? [];

                    if (is_string($serviceIds)) {
                        $serviceIds = json_decode($serviceIds, true) ?? [];
                    }

                    $bookIds = [];
                    $organizationServices = OrganizationService::withDefaultGroupCompanyOrg()->whereIn('service_id', $serviceIds ?? [])->when($selectedServiceAlias, function ($aliasQuery) use ($selectedServiceAlias) {
                        $aliasQuery->where('alias', $selectedServiceAlias);
                    })->get();
                    $currentBook = Book::withDefaultGroupCompanyOrg()->whereIn('service_id', $serviceIds ?? [])->first();
                    return [
                        'services' => $organizationServices,
                        'books' => [],
                        'current_book' => $currentBook,
                        'menu' => $organizationMenu,
                        'all_book_access' => true,
                        'message' => 'All Data Found'
                    ];
                } else {
                    $serviceIds = $services?->erp_service_ids ?? [];
                    $bookIds = $services?->book_ids ?? [];
                    $organizationServices = OrganizationService::withDefaultGroupCompanyOrg()->whereIn('service_id', $serviceIds)->when($selectedServiceAlias, function ($aliasQuery) use ($selectedServiceAlias) {
                        $aliasQuery->where('alias', $selectedServiceAlias);
                    })->get();
                    if ($organizationServices && count($organizationServices) > 0) {
                        $organizationServices = $organizationServices->filter(function ($orgService) use ($bookIds) {
                            $isBookExist = Book::withDefaultGroupCompanyOrg()->whereIn('id', $bookIds)->where('org_service_id', $orgService->id)->first();
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

    public static function setMenuAccessToEmployee(string $menuName, string $menuAlias, array $serviceIds): string
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

    public static function getPolicyByServiceId($serviceId, $authUser = null)
    {

        $authUser = $authUser ?: Helper::getAuthenticatedUser();

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
            ->where('document_id', $documentId)->where('approval_type', $actualStatus)->latest()->first();
        if (!isset($documentApproval)) {
            $documentApproval = DocumentApproval::where('document_name', $modelClass)
                ->where('document_id', $documentId)->where('approval_type', 'submit')->latest()->first();
        }
        return $documentApproval?->user?->name;
    }
    public static function numberToWords($num)
    {
        $ones = [
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen'
        ];

        $tens = [
            2 => 'twenty',
            3 => 'thirty',
            4 => 'forty',
            5 => 'fifty',
            6 => 'sixty',
            7 => 'seventy',
            8 => 'eighty',
            9 => 'ninety'
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

    private static function convertIntegerToWords($num, $ones, $tens, $levels)
    {
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

    private static function convertBelowThousand($num)
    {
        $ones = [
            0 => 'zero',
            1 => 'one',
            2 => 'two',
            3 => 'three',
            4 => 'four',
            5 => 'five',
            6 => 'six',
            7 => 'seven',
            8 => 'eight',
            9 => 'nine',
            10 => 'ten',
            11 => 'eleven',
            12 => 'twelve',
            13 => 'thirteen',
            14 => 'fourteen',
            15 => 'fifteen',
            16 => 'sixteen',
            17 => 'seventeen',
            18 => 'eighteen',
            19 => 'nineteen'
        ];

        $tens = [
            2 => 'twenty',
            3 => 'thirty',
            4 => 'forty',
            5 => 'fifty',
            6 => 'sixty',
            7 => 'seventy',
            8 => 'eighty',
            9 => 'ninety'
        ];

        if ($num < 20) {
            return $ones[$num];
        } elseif ($num < 100) {
            return $tens[intval($num / 10)] . ($num % 10 ? ' ' . $ones[$num % 10] : '');
        } else {
            return $ones[intval($num / 100)] . ' hundred' . ($num % 100 ? ' ' . self::convertBelowThousand($num % 100) : '');
        }
    }
    public static function getChildLedgerGroupsByNameArray($names, $ledger_name = null)
    {
        $organizationId = Helper::getAuthenticatedUser()->organization_id;
        $groups = collect(); // Initialize empty collection

        foreach ($names as $name) {
            // Get all matching groups (org-specific and global)


            $matchedGroups = Helper::getGroupsQuery()->where('name', $name)->get();

            $groups = $groups->merge($matchedGroups);
        }

        $allChildIds = [];

        foreach ($groups as $group) {
            $childIds = $group->getAllChildIds(); // Assume this returns array
            $childIds[] = $group->id; // Add parent group ID
            $allChildIds = array_merge($allChildIds, $childIds);
            $allChildIds = Helper::getGroupsQuery()
                ->whereIn('id', $allChildIds)->pluck('id')->toArray();
        }
        if ($ledger_name == "names")
            return Helper::getGroupsQuery()->whereIn('id', $allChildIds)->pluck('name')->toArray();

        // Remove duplicate IDs
        else
            return array_unique($allChildIds);
    }
    public static function getNonCarryGroups()
    {
        return self::getChildLedgerGroupsByNameArray(ConstantHelper::NON_CARRY_FORWARD_BALANCE_GROUPS);
    }

    public static function prepareValidatedDataWithPolicy($parentUrlAlias = null)
    {
        $user = self::getAuthenticatedUser();
        $organization = $user->organization;
        $validatedData = [];

        $parentUrl = $parentUrlAlias ?? '';

        $services = self::getAccessibleServicesFromMenuAlias($parentUrl, '', $user);

        if ($services && $services['services'] && $services['services']->isNotEmpty()) {
            $firstService = $services['services']->first();
            $serviceId = $firstService->service_id;

            $policyData = self::getPolicyByServiceId($serviceId, $user);

            if ($policyData && isset($policyData['policyLevelData'])) {
                $policyLevelData = $policyData['policyLevelData'];
                $validatedData['group_id'] = $policyLevelData['group_id'];
                $validatedData['company_id'] = $policyLevelData['company_id'];
                $validatedData['organization_id'] = $policyLevelData['organization_id'];
            } else {
                $validatedData['group_id'] = $organization->group_id;
                $validatedData['company_id'] = $organization->company_id;
                $validatedData['organization_id'] = null;
            }
        } else {
            $validatedData['group_id'] = $organization->group_id;
            $validatedData['company_id'] = $organization->company_id;
            $validatedData['organization_id'] = null;
        }

        return $validatedData;
    }

    //get active location
    public static function getStoreLocation($org_ids)
    {
        $query = InventoryHelper::getAccessibleLocations();

        $filtered = $query->filter(function ($store) use ($org_ids) {
            if (is_array($org_ids)) {
                return in_array($store->organization_id, $org_ids);
            } else {
                return $store->organization_id == $org_ids;
            }
        });

        return $filtered->values();
    }

    public static function uniqueRuleWithConditions(string $table, array $conditions = [], int $ignoreId = null, string $ignoreColumn = 'id', bool $checkDeletedAt = true)
    {
        $rule = Rule::unique($table)->where(function ($query) use ($conditions, $checkDeletedAt) {
            foreach ($conditions as $column => $value) {
                $query->where($column, $value);
            }
            if ($checkDeletedAt) {
                $query->whereNull('deleted_at');
            }
        });

        if ($ignoreId) {
            $rule->ignore($ignoreId, $ignoreColumn);
        }

        return $rule;
    }


    public static function getAllPastFinancialYear($organizationId = null): mixed
    {
        if ($organizationId) {

            $financialYears = ErpFinancialYear::where('organization_id', $organizationId)->get();
        } else {
            $financialYears = ErpFinancialYear::get();
        }
        if ($financialYears->isNotEmpty()) {
            return $financialYears
                ->filter(function ($financialYear) {
                    return \Carbon\Carbon::parse($financialYear->end_date)->isPast(); // Only past end dates
                })
                ->map(function ($financialYear) {
                    $startYear = \Carbon\Carbon::parse($financialYear->start_date)->format('Y');
                    $endYearShort = \Carbon\Carbon::parse($financialYear->end_date)->format('y'); // e.g., 24
                    return [
                        'id' => $financialYear->id,
                        'alias' => $financialYear->alias,
                        'start_date' => $financialYear->start_date,
                        'end_date' => $financialYear->end_date,
                        'range' => $startYear . '-' . $endYearShort,
                        'lock_fy' => $financialYear->lock_fy,
                        'fy_close' => $financialYear->fy_close,
                        'authorized_users' => $financialYear->authorizedUsers()
                    ];
                })->values();
        }

        return null;
    }

    public static function getFinancialYears($organizationId = null)
    {
        $currentUserId = Helper::getAuthenticatedUser()->auth_user_id;
        $currentUserType = Helper::getAuthenticatedUser()->authenticable_type;

        if ($organizationId) {
            $financialYears = ErpFinancialYear::where('organization_id', $organizationId)
                ->orderBy('id', 'desc')
                ->get();
        } else {
            $financialYears = ErpFinancialYear::orderBy('id', 'desc')
                ->get();
        }

        if ($financialYears->isNotEmpty()) {
            return $financialYears
                ->filter(function ($financialYear) use ($currentUserId, $currentUserType) {
                    if ($financialYear->fy_close === true && is_array($financialYear->access_by)) {
                        return !collect($financialYear->access_by)->contains(function ($entry) use ($currentUserId, $currentUserType) {
                            return isset($entry['user_id'], $entry['authorized'], $entry['authenticable_type'], $entry['locked']) &&
                                $entry['user_id'] == $currentUserId &&
                                $entry['authenticable_type'] == $currentUserType &&
                                $entry['authorized'] === false &&
                                $entry['locked'] !== true; // only filter if NOT locked
                        });
                    }
                    return true;
                })
                ->map(function ($financialYear) {
                    $startYear = \Carbon\Carbon::parse($financialYear->start_date)->format('Y');
                    $endYearShort = \Carbon\Carbon::parse($financialYear->end_date)->format('y');
                    return [
                        'id' => $financialYear->id,
                        'alias' => $financialYear->alias,
                        'start_date' => $financialYear->start_date,
                        'end_date' => $financialYear->end_date,
                        'range' => $startYear . '-' . $endYearShort,
                        'authorized_users' => $financialYear->authorizedUsers()
                    ];
                })
                ->values();
        }

        return null;
    }
    // public static function getFyAuthorizedUsers(string $date): mixed
    // {
    //     $financialYear = ErpFinancialYear::where('start_date', '<=', $date)
    //         ->where('end_date', '>=', $date)
    //         ->orWhere('fy_status',ConstantHelper::FY_CURRENT_STATUS)
    //         ->first();
    //     if (isset($financialYear)) {
    //         return [
    //             'alias' => $financialYear->alias,
    //             'authorized_users' => $financialYear->authorizedUsers()
    //         ];
    //     } else {
    //         return null;
    //     }
    // }

    public static function getGroupsQuery($organizations = [], $status = ["active"])
    {
        $groups = Group::where(function ($q) {
            $q->withDefaultGroupCompanyOrg()
                ->orWhere('edit', 0);
        })->whereIn('status', $status);

        return $groups;
    }

    public static function getCurrentFy($date = null)
    {
        $date = $date ?? date('Y-m-d');
        $startDate = session('fyear_start_date') ?? $date;
        $endDate = session('fyear_end_date') ?? $date;
        $financialYear = ErpFinancialYear::where(function ($query) use ($startDate, $endDate) {
            $query->where('start_date', '<=', $startDate)
                ->where('end_date', '>=', $endDate);
        })
            ->orWhere('fy_status', ConstantHelper::FY_CURRENT_STATUS)
            ->first();

        return $financialYear ?? null;
    }

    public static function getActiveCostCenters($id = null)
    {
        $query = CostCenterOrgLocations::with([
            'costCenter' => function ($query) {
                $query->where('status', 'active');
            }
        ]);

        // Apply location_id filter if $id is provided
        if ($id !== null) {
            $query->where('location_id', $id);
        }

        return $query->get()
            ->map(function ($item) {
                return $item->costCenter ? [
                    'id' => $item->costCenter->id,
                    'name' => $item->costCenter->name,
                    'cost_group_id' => $item->costCenter->cost_group_id,
                    'location' => $item->costCenter->locations,
                ] : null;
            })
            ->filter()
            ->unique('id')
            ->values()
            ->toArray();
    }

    public static function getVoucherBalance($voucher_id = null, $doc_type, $ledger, $group)
    {
        $request = new Request();
        $request->merge([
            'type' => $doc_type,
            'partyID' => $ledger,
            'ledgerGroup' => $group,
            'voucher_id' => $voucher_id,
        ]);
        $data = VoucherController::getLedgerVouchers($request);
        return $data;
    }


    public static function createPartyLedger($type, $name, $code, $group_id)
    {
        try {
            return DB::transaction(function () use ($type, $name, $code, $group_id) {
                $itemCodeType = "Manual";
                $parentUrl = ConstantHelper::LEDGERS_SERVICE_ALIAS;
                $book = null;
                $validatedData = Helper::prepareValidatedDataWithPolicy($parentUrl);
                $services = Helper::getAccessibleServicesFromMenuAlias($parentUrl);

                if ($services && $services['current_book']) {
                    if (isset($services['current_book'])) {
                        $book = $services['current_book'];
                        if ($book) {
                            $parameters = new \stdClass();
                            foreach (ServiceParametersHelper::SERVICE_PARAMETERS as $paramName => $paramNameVal) {
                                $param = ServiceParametersHelper::getBookLevelParameterValue($paramName, $book->id)['data'];
                                $parameters->{$paramName} = $param;
                            }
                            if (isset($parameters->ledger_code_type) && is_array($parameters->ledger_code_type)) {
                                $itemCodeType = $parameters->ledger_code_type[0] ?? null;
                            }
                        }
                    } else
                        return [
                            'success' => false,
                            'message' => 'Book not found for ledgers.',
                            'data' => []
                        ];
                } else
                    return [
                        'success' => false,
                        'message' => 'Service not found for ledgers.',
                        'data' => []
                    ];

                $group = $type === "customer" ? ConstantHelper::RECEIVABLE : ConstantHelper::PAYABLE;
                if (empty($group)) {
                    return [
                        'success' => false,
                        'message' => 'Ledger group type not found.',
                        'data' => []
                    ];
                }

                $groupParts = array_map('trim', explode(',', $group));
                $partyGroups = self::getGroupsQuery()
                    ->whereIn('name', $groupParts)->latest()->first();


                if (empty($partyGroups)) {
                    return [
                        'success' => false,
                        'message' => $group . " not found",
                        'data' => []
                    ];
                }




                $existingGroups = $partyGroups->getAllChildIds();
                $existingGroups[] = $partyGroups->id;


                if (!in_array((int)$group_id, $existingGroups))
                    return [
                        'success' => false,
                        'message' => 'Group ID not mapped with ' . $group,
                        'data' => []
                    ];


                if ($itemCodeType == "Auto") {
                    $itemInitials = Group::getPrefix($group_id);
                    $baseCode = $itemInitials;
                    $nextSuffix = '001';
                    $finalItemCode = $baseCode . $nextSuffix;

                    while (Ledger::where('code', $finalItemCode)->exists()) {
                        $nextSuffix = str_pad(intval($nextSuffix) + 1, 3, '0', STR_PAD_LEFT);
                        $finalItemCode = $baseCode . $nextSuffix;
                    }

                    $code = $finalItemCode;
                }

                if (Ledger::where('code', $code)->exists()) {
                    return [
                        'success' => false,
                        'message' => 'Ledger code already exists.',
                        'data' => []
                    ];
                }

                if (Ledger::where('name', $name)->exists()) {
                    return [
                        'success' => false,
                        'message' => 'Ledger name already exists.',
                        'data' => []
                    ];
                }
                $validatedData['ledger_code_type'] = $itemCodeType;
                $validatedData['book_id'] = $book->id;
                $validatedData['created_by'] = self::getAuthenticatedUser()->id;
                $validatedData['code'] = $code;
                $validatedData['name'] = $name;
                $validatedData['ledger_group_id'] = json_encode([(string)$group_id]);
                $validatedData['status'] = 1;
                $validatedData['document_status'] = ConstantHelper::APPROVAL_NOT_REQUIRED;


                $ledger = Ledger::create($validatedData);

                // $bookId = $ledger->book_id;
                // $docId = $ledger->id;
                // $currentLevel = $ledger->approval_level ?? 1;
                // $revisionNumber = $ledger->revision_number ?? 0;
                // $actionType = 'approve';
                // $modelName = get_class($ledger);
                // $totalValue = 0;

                // $approveDocument = Helper::approveDocument(
                //     $bookId,
                //     $docId,
                //     $revisionNumber,
                //     null,
                //     null,
                //     $currentLevel,
                //     $actionType,
                //     $totalValue,
                //     $modelName
                // );

                return [
                    'success' => true,
                    'message' => ucfirst($type) . ' Ledger created successfully.',
                    'data' => [
                        'ledger_id' => $ledger->id,
                        'ledger_code' => $code,
                        'ledger_name' => $name,
                        'ledger_group_id' => $group_id,
                    ]
                ];
            });
        } catch (\Exception $e) {
            Log::error('Error creating party ledger: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'An error occurred while creating the ledger.',
                'data' => [],
                'error' => $e->getMessage()
            ];
        }
    }
    public static function generateContraDocNumber(int $book_id, string $document_date, $organization_id)
    {

        $book = Book::find($book_id);
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
                    $currentDocNo = $model->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                        ->where('organization_id', $organization_id)
                        ->where('book_id', $book_id)
                        ->whereNotNull('doc_no')
                        // ->orderBy('doc_no', 'DESC')
                        ->orderByRaw('CAST(doc_no AS UNSIGNED) DESC')
                        ->pluck('doc_no')->first() ?? $startFrom;
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
                    $currentDocNo = $model->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                        ->where('organization_id', $organization_id)->where('book_id', $book_id)
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
                    $currentDocNo = $model->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                        ->where('organization_id', $organization_id)->where('book_id', $book_id)
                        ->whereNotNull('doc_no')
                        ->whereBetween('document_date', [$financialQuarter['start_date'], $financialQuarter['end_date']])
                        ->orderBy('doc_no', 'DESC')->pluck('doc_no')->first() ?? $startFrom;
                } else {
                    if (isset($financialYear) && isset($financialQuarter) && isset($financialMonth)) {
                        $prefix = $financialYear['alias'] . "-" . $financialMonth['alias'];
                        $suffix = $data->suffix;
                        $currentDocNo = $model->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                            ->where('organization_id', $organization_id)->where('book_id', $book_id)
                            ->whereNotNull('doc_no')
                            ->whereBetween('document_date', [$financialMonth['start_date'], $financialMonth['end_date']])
                            ->orderBy('doc_no', 'DESC')->pluck('doc_no')->first() ?? $startFrom;
                    }
                }

                $currentDocNo = ($currentDocNo ? $currentDocNo : 0) + 1;

                $voucher_no = ($prefix ? $prefix . "-" : "") . ($currentDocNo) . ($suffix ? "-" . $suffix : "");

                //Condition for Sales Invoice/ Sales Return and Purchase Return
                $shouldCheckTransportDocForPrSr = in_array($book->service?->service?->alias, [
                    ConstantHelper::PURCHASE_RETURN_SERVICE_ALIAS,
                    ConstantHelper::SR_SERVICE_ALIAS
                ]);
                $shouldCheckTransportDocForSi = false;

                if (
                    $serviceAlias === ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS && isset($parameters) &&
                    isset($parameters->{ServiceParametersHelper::INVOICE_TO_FOLLOW_PARAM})
                ) {
                    $shouldCheckTransportDocForSi = $parameters->{ServiceParametersHelper::INVOICE_TO_FOLLOW_PARAM}[0] == "no";
                }

                if ($shouldCheckTransportDocForPrSr || $shouldCheckTransportDocForSi) {
                    if (strlen($book->book_code . '-' . $voucher_no) > EInvoiceHelper::TRANPORTER_DOC_NO_MAX_LIMIT) {
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
    public static function getContraBooks()
    {
        $service = Service::where('alias', ConstantHelper::CONTRA_VOUCHER)->first();
        if (empty($service))
            return [];
        $books = Book::where('service_id', $service->id)
            ->where('manual_entry', 1)
            ->where('status', 'active')->get();
        return $books ?? [];
    }
    public static function generateAssetCode($category_id)
    {
        $itemInitials = FixedAssetSetup::getPrefix($category_id);
        $baseCode = $itemInitials;
        $nextSuffix = '001';
        $finalItemCode = $baseCode . $nextSuffix;

        while (
            FixedAssetRegistration::where('asset_code', $finalItemCode)
            ->exists()
        ) {
            $nextSuffix = str_pad(intval($nextSuffix) + 1, 3, '0', STR_PAD_LEFT);
            $finalItemCode = $baseCode . $nextSuffix;
        }

        return $finalItemCode;
    }

    public static function mrnAssetRegister($mrn_id,$alias): array
    {

        DB::beginTransaction();
        try {

             if(!empty($alias) && ($alias == ConstantHelper::PB_SERVICE_ALIAS))
            {
                $mrn_id = PbHeader::where('id', $mrn_id)->pluck('mrn_header_id')->first();
            }
            else
            {
                $mrn_id = $mrn_id;
            }
            $assets = MrnHeader::where('id', $mrn_id)
                ->whereHas('items', function ($q) {
                    $q->where('basic_value', '>', 0) // must have positive basic_value
                        ->whereHas('item', function ($q) {
                            $q->where('is_asset', 1); // must be an asset
                        })
                        ->doesntHave('asset'); // must not have linked asset
                })
                ->exists();

            $mrn_assets = MrnAssetDetail::where('header_id', $mrn_id)->get();


            if ($assets && !$mrn_assets->isEmpty()) {
                $mrn = MrnHeader::find($mrn_id);
                if (empty($mrn)) {
                    DB::rollBack();
                    return [
                        'message' => 'MRN not exist',
                        'status' => false
                    ];
                }

                $user = Helper::getAuthenticatedUser();
                $organization = $user->organization;
                $book = Book::find($mrn->book_id);
                if (empty($book)) {
                    DB::rollBack();
                    return [
                        'message' => 'MRN Book not found',
                        'status' => false
                    ];
                }

                $glPostingBookParam = OrganizationBookParameter::where('book_id', $book->id)
                    ->where('parameter_name', ServiceParametersHelper::GL_POSTING_SERIES_PARAM)
                    ->first();

                if (!isset($glPostingBookParam) || !isset($glPostingBookParam->parameter_value[0])) {
                    DB::rollBack();
                    return [
                        'status' => false,
                        'message' => 'Financial Book Code is not specified',
                        'data' => []
                    ];
                }

                $glPostingBookId = $glPostingBookParam->parameter_value[0];

                foreach ($mrn_assets as $mrn_asset)
                {
                    $category_id = $mrn_asset->asset_category_id;
                    $asset_name = $mrn_asset->asset_name;
                    $capitalize_date = $mrn_asset->capitalization_date;
                    $life = $mrn_asset->estimated_life;
                    $detail_id = $mrn_asset->detail_id;


                    // Input validation
                    if (empty($mrn_id) || empty($category_id) || empty($asset_name) || empty($capitalize_date) || empty($life) || empty($detail_id)) {
                        DB::rollBack();
                        return [
                            'status' => false,
                            'message' => 'All parameters (mrn_id, category_id, asset_name, capitalize_date, life, detail_id) are required.'
                        ];
                    }

                    // Validate capitalize_date format (Y-m-d)
                    try {
                        $capitalize_date = Carbon::parse($capitalize_date)->format('Y-m-d');
                    } catch (Exception $e) {
                        DB::rollBack();
                        return [
                            'status' => false,
                            'message' => 'Invalid capitalize_date format. Expected format: Y-m-d',
                            'error' => $e->getMessage()
                        ];
                    }

                    // Validate life (should be a positive number)
                    if (!is_numeric($life) || $life <= 0) {
                        DB::rollBack();
                        return [
                            'status' => false,
                            'message' => 'Asset life must be a positive number.'
                        ];
                    }

                    // Validate asset_name
                    if (!is_string($asset_name) || trim($asset_name) === '') {
                        DB::rollBack();
                        return [
                            'status' => false,
                            'message' => 'Asset name must be a non-empty string.'
                        ];
                    }

                    $setup = FixedAssetSetup::where('asset_category_id', $category_id)
                        ->where('act_type', 'company')->first();

                    if (empty($setup)) {
                        DB::rollBack();
                        return [
                            'message' => 'Setup not exist',
                            'status' => false
                        ];
                    }
                    $mrn_detail = MrnDetail::find($detail_id);

                    $exitingReg = FixedAssetRegistration::where('mrn_detail_id', $mrn_detail->id)
                        ->where('mrn_header_id', $mrn->id)->first();

                    if (!empty($exitingReg)) {
                        DB::rollBack();
                        return [
                            'message' => 'MRN already registered with asset code ' . $exitingReg->asset_code,
                            'status' => false
                        ];
                    }

                    if (!empty($existingAsset)) {
                        DB::rollBack();
                        return [
                            'status' => false,
                            'message' => 'Asset Code ' . $existingAsset->asset_code . ' already exists.',
                            'data' => []
                        ];
                    }

                     if(!empty($alias) && ($alias == ConstantHelper::PB_SERVICE_ALIAS))
                    {
                        $currentValue = $mrn_detail->pb_item_value;
                    }
                    else
                    {
                        $currentValue = $mrn_detail->basic_value + $mrn_detail->header_exp_amount;
                    }
                    $depreciationPercentage = $setup->salvage_percentage ?? $organization->dep_percentage ?? null;
                    $salvageValue = round($currentValue * ($depreciationPercentage / 100), 2);
                    $method = $organization->dep_method;

                    $depreciationRate = 0;
                    if ($method === 'SLM') {
                        $annualDepreciation = ($currentValue - $salvageValue) / $life;
                        $depreciationRate = round(($annualDepreciation / $currentValue) * 100, 2);
                    } elseif ($method === 'WDV') {
                        $depreciationRate = round((1 - pow($salvageValue / $currentValue, 1 / $life)) * 100, 2);
                    }




                if(count($mrn_detail->batches) > 0)
                {
                    $count=count($mrn_detail->batches);
                    $uniqueCodes = $mrn_detail->uniqueCodes->values();
                    $totalqty = 0;
                    $taxpercentage = ($mrn_detail->tax_value/($mrn_detail->basic_value-$mrn_detail->discount_amount));
                    foreach($mrn_detail->batches as $batch)
                    {
                        $totalqty += $batch->inventory_uom_qty;
                        $singlevalue = round($currentValue/$totalqty, 2);

                    }

                     $offset = 0;

                    foreach($mrn_detail->batches as $batch)
                    {
                        $salvageValue = round(($singlevalue * $batch->inventory_uom_qty) * ($depreciationPercentage / 100), 2);

                        $asset_code = self::generateAssetCode($category_id);
                        $existingAsset = FixedAssetRegistration::where('asset_code', $asset_code)->first();
                        $data = [
                        'organization_id' => $user->organization_id,
                        'group_id' => $organization->group_id,
                        'company_id' => $organization->company_id,
                        'created_by' => $user->id,
                        'type' => get_class($user),
                        'book_id' => $glPostingBookId,
                        'document_number' => $mrn->document_number,
                        'document_date' => $mrn->document_date,
                        'mrn_detail_id' => $mrn_detail->id,
                        'mrn_header_id' => $mrn->id,
                        'asset_code' => $asset_code,
                        'asset_name' => $asset_name,
                        'brand_name' => $mrn_asset->brand_name,
                        'model_no' => $mrn_asset->model_no,
                        'procurement_type' => $mrn_asset->procurement_type,
                        'quantity' => $batch->inventory_uom_qty,
                        'category_id' => $category_id,
                        'reference_doc_id' => $mrn->id,
                        'reference_series' => ConstantHelper::MRN_SERVICE_ALIAS,
                        'ledger_id' => $setup->ledger_id,
                        'ledger_group_id' => $setup->ledger_group_id,
                        'capitalize_date' => $capitalize_date,
                        'last_dep_date' => $capitalize_date,
                        'vendor_id' => $mrn->vendor_id,
                        'currency_id' => $mrn->vendor?->currency_id,
                        'sub_total' => $mrn_detail->basic_value ,
                        'tax' => $mrn_detail->tax_value,
                        'purchase_amount' => $mrn_detail->basic_value + $mrn_detail->tax_value,
                        'supplier_invoice_date' => $mrn->supplier_invoice_date,
                        'book_date' => $mrn_detail->created_at ?? null,
                        'supplier_invoice_no' => $mrn->supplier_invoice_no,
                        'location_id' => $mrn->sub_store_id ?? null,
                        'cost_center_id' => $mrn->cost_center_id ?? null,
                        'maintenance_schedule' => $setup->maintenance_schedule ?? null,
                        'depreciation_method' => $method,
                        'useful_life' => $life,
                        'salvage_value' => $salvageValue,
                        'depreciation_percentage' => $depreciationRate,
                        'depreciation_percentage_year' => $depreciationRate,
                        'total_depreciation' => 0,
                        'dep_type' => $organization->dep_type,
                        'current_value' => ($singlevalue * $batch->inventory_uom_qty),
                        'current_value_after_dep' => ($singlevalue * $batch->inventory_uom_qty),
                        'document_status' => 'approved',
                        'approval_level' => 1,
                        'revision_number' => 0,
                        'revision_date' => null,
                        'status' => 'active',
                    ];
                            $asset = FixedAssetRegistration::create($data);

                            FixedAssetSub::generateSubAssets(
                                $asset->id,
                                $asset->asset_code,
                                $batch->inventory_uom_qty,
                                $asset->current_value,
                                $asset->salvage_value
                            );


                            $mrn_asset->salvage_value = $salvageValue;
                            $mrn_asset->asset_code = $asset_code;
                            $mrn_asset->asset_id = $asset->id;
                            $mrn_asset->save();
                            $asset->batchupdateUniqueCodes($uniqueCodes,$batch,$offset);
                            $offset += $batch->inventory_uom_qty;
                    }

                }
                else
                {
                    $asset_code = self::generateAssetCode($category_id);
                    $existingAsset = FixedAssetRegistration::where('asset_code', $asset_code)->first();

                    $data = [
                        'organization_id' => $user->organization_id,
                        'group_id' => $organization->group_id,
                        'company_id' => $organization->company_id,
                        'created_by' => $user->id,
                        'type' => get_class($user),
                        'book_id' => $glPostingBookId,
                        'document_number' => $mrn->document_number,
                        'document_date' => $mrn->document_date,
                        'mrn_detail_id' => $mrn_detail->id,
                        'mrn_header_id' => $mrn->id,
                        'asset_code' => $asset_code,
                        'asset_name' => $asset_name,
                        'brand_name' => $mrn_asset->brand_name,
                        'model_no' => $mrn_asset->model_no,
                        'procurement_type' => $mrn_asset->procurement_type,
                        'quantity' => $mrn_detail->accepted_inv_uom_qty,
                        'category_id' => $category_id,
                        'reference_doc_id' => $mrn->id,
                        'reference_series' => ConstantHelper::MRN_SERVICE_ALIAS,
                        'ledger_id' => $setup->ledger_id,
                        'ledger_group_id' => $setup->ledger_group_id,
                        'capitalize_date' => $capitalize_date,
                        'last_dep_date' => $capitalize_date,
                        'vendor_id' => $mrn->vendor_id,
                        'currency_id' => $mrn->vendor?->currency_id,
                        'sub_total' => $currentValue,
                        'tax' => $mrn_detail->tax_value,
                        'purchase_amount' => $currentValue + $mrn_detail->tax_value,
                        'supplier_invoice_date' => $mrn->supplier_invoice_date,
                        'book_date' => $mrn_detail->created_at ?? null,
                        'supplier_invoice_no' => $mrn->supplier_invoice_no,
                        'location_id' => $mrn->store_id ?? null,
                        'cost_center_id' => $mrn->cost_center_id ?? null,
                        'maintenance_schedule' => $setup->maintenance_schedule ?? null,
                        'depreciation_method' => $method,
                        'useful_life' => $life,
                        'salvage_value' => $salvageValue,
                        'depreciation_percentage' => $depreciationRate,
                        'depreciation_percentage_year' => $depreciationRate,
                        'total_depreciation' => 0,
                        'dep_type' => $organization->dep_type,
                        'current_value' => $currentValue,
                        'current_value_after_dep' => $currentValue,
                        'document_status' => 'approved',
                        'approval_level' => 1,
                        'revision_number' => 0,
                        'revision_date' => null,
                        'status' => 'active',
                    ];
                    $asset = FixedAssetRegistration::create($data);

                    $batches= $mrn_detail->batches;

                    FixedAssetSub::generateSubAssets(
                        $asset->id,
                        $asset->asset_code,
                        $asset->quantity,
                        $asset->current_value,
                        $asset->salvage_value
                    );
                    $mrn_asset->salvage_value = $salvageValue;
                    $mrn_asset->asset_code = $asset_code;
                    $mrn_asset->asset_id = $asset->id;
                    $mrn_asset->save();

                    $asset->updateUniqueCodes();
                }

                }


                DB::commit();

                return [
                    'status' => true,
                    'message' => "Registration Added",
                    'data' => []
                ];
            } else {
                DB::commit();
                return [
                    'status' => true,
                    'message' => "MRN does not have any asset to register",
                    'data' => []
                ];
            }
        } catch (Exception $e) {

            DB::rollBack();
            Log::error('MRN Asset Register Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return [
                'status' => false,
                'error' => $e->getMessage(),
                'message' => 'An error occurred during asset registration.',

            ];
        }
    }
    public static function access_org()
    {
        $user = Helper::getAuthenticatedUser();
        $companies = $user?->access_rights_org;

        $companies = ($companies && $companies->isNotEmpty())
            ? $companies
            : collect([$user]);
        return $companies;
    }
}
