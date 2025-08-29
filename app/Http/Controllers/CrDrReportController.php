<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentVoucher;
use App\Models\UploadPendingPaymentMaster;
use App\Imports\CrDrReportImport;
use App\Exports\PendingPaymentExport;
use App\Exports\FailedPendingPaymentExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentVoucherDetails;
use App\Console\Commands\GenerateCrDrReport;
use Illuminate\Support\Facades\Cache;
use App\Helpers\Helper;
use App\Models\Scopes\DefaultGroupCompanyOrgScope;
use App\Helpers\InventoryHelper;
use App\Helpers\ConstantHelper;
use App\Models\Group;
use App\Models\VoucherReference;
use App\Models\Ledger;
use App\Models\Voucher;
use App\Models\ItemDetail;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\CrDrReportScheduler;
use Illuminate\Support\Facades\Response;
use App\Models\Organization;
use NumberFormatter\NumberFormatter;
use App\Models\ErpAddress;
use App\Models\Address;
use Illuminate\Support\Facades\Mail;
use App\Models\AuthUser;
use Carbon\Carbon;
use PDF;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Cookie;
use App\Exports\DebitorCreditoExcelExport;
use App\Mail\ImportComplete;
// use App\Mail\ImportComplete;
use App\Services\CrDrImportExportService;
use App\Models\CostGroup;




class CrDrReportController extends Controller
{
    protected $PendingPaymentImportExportService;

    public function __construct(CrDrImportExportService $PendingPaymentImportExportService)
    {
        $this->PendingPaymentImportExportService = $PendingPaymentImportExportService;
    }

    public function debit(Request $request)
    {
        $start = null;
        $end = null;
        $loc = null;
        $cost = null;
        $org = null;

        $due_date="invoice";
        if ($request->has('d'))
            $due_date = $request->d;

        if ($request->has('location_id'))
            $loc = $request->location_id;

        if ($request->has('cost_center_id'))
            $cost = $request->cost_center_id;

        if ($request->has('organization_id'))
            $org = array_filter(array_map('intval', explode(',', $request->organization_id)));


        if ($request->date) {
            $dates = explode(' to ', $request->date);
            $start = date('Y-m-d', strtotime($dates[0]));
            $end = isset($dates[1]) && $dates[1] ? date('Y-m-d', strtotime($dates[1])) : $start;
        }
        $cost_center_ids = null;
        if (!empty($request->cost_center_id)) {
            $cost_center_ids = $request->cost_center_id ?? null;
            // dd($cost_center_ids);
        } elseif (!empty($request->cost_group_id)) {
            $cost_group = CostGroup::with('costCenters')
                ->where('id', $request->cost_group_id)
                ->where('status', 'active')
                ->first();

            $cost_center_ids = optional($cost_group->costCenters)->pluck('id')->unique()->all();
            // dd($cost_center_ids);
        }



        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization_id;
        $companies = Helper::access_org();
        
        $cost_centers = Helper::getActiveCostCenters();

        $cost_groups = CostGroup::with('costCenters')->where('status', 'active')->get()->toArray();
        $locations = InventoryHelper::getAccessibleLocations();

        $group_name = Group::find($request->group)->name ?? ConstantHelper::RECEIVABLE;

        $customers = [];
        $all_ledgers = [];
        $group = Helper::getGroupsQuery()->where('name', $group_name)->first();
        $drp_group = Helper::getGroupsQuery()->where('name', ConstantHelper::RECEIVABLE)->first();

        if ($group) {
            $ledger_groups = Helper::getGroupsQuery()->where('parent_group_id', $group->id)->pluck('id');

            if (count($ledger_groups) > 0) {
                $all_ledgers = Ledger::where('status', 1)
                    ->where(function ($query) use ($ledger_groups) {
                        $query->whereIn('ledger_group_id', $ledger_groups);

                        foreach ($ledger_groups as $child) {
                            $query->orWhereJsonContains('ledger_group_id', (string) $child)->orWhereJsonContains('ledger_group_id', $child);
                        }
                    })
                    ->get();

                $ages_all = [$request->age0 ?? 30, $request->age1 ?? 60, $request->age2 ?? 90, $request->age3 ?? 120, $request->age4 ?? 180];
                if (!is_null($ledger_groups))
                    $customers = self::get_ledgers_data($ledger_groups, $ages_all, 'debit', $request->ledger, $start, $end, $org, $loc, $cost_center_ids,$due_date);
            } else if (isset($group->id)) {
                $ledger_groups = [$group->id];
                $all_ledgers = Ledger::where('status', 1)
                    ->where(function ($query) use ($ledger_groups) {
                        $query->whereIn('ledger_group_id', $ledger_groups);

                        foreach ($ledger_groups as $child) {
                            $query->orWhereJsonContains('ledger_group_id', (string) $child)->orWhereJsonContains('ledger_group_id', $child);
                        }
                    })
                    ->get();

                $ages_all = [$request->age0 ?? 30, $request->age1 ?? 60, $request->age2 ?? 90, $request->age3 ?? 120, $request->age4 ?? 180];
                if (!is_null($ledger_groups))
                    $customers = self::get_ledgers_data($ledger_groups, $ages_all, 'debit', $request->ledger, $start, $end, $org, $loc, $cost_center_ids,$due_date);
            }
        }
        $all_groups = Group::whereIn('id', $drp_group->getAllChildIds())->get();
        $date = $request->date;
        $date2 = $end ? \Carbon\Carbon::parse($end)->format('jS-F-Y') : \Carbon\Carbon::parse(date('Y-m-d'))->format('jS-F-Y');
        $customers = collect($customers)->reject(function ($item) {
            return (float) $item->total_outstanding === 0.0;
        });
        return view('finance_report.debitors', compact('cost_centers', 'companies', 'organizationId', 'locations', 'customers', 'all_groups', 'all_ledgers', 'date', 'date2', 'cost_groups'));
    }
    public function credit(Request $request)
    {
        $start = null;
        $end = null;
        $loc = null;
        $cost = null;
        $org = null;
        

        if ($request->has('location_id'))
            $loc = $request->location_id;
        
        $due_date="invoice";
        if ($request->has('d'))
            $due_date = $request->d;

        if ($request->has('cost_center_id'))
            $cost = $request->cost_center_id;

        if ($request->has('organization_id'))
            $org = array_filter(array_map('intval', explode(',', $request->organization_id)));


        if ($request->date) {
            $dates = explode(' to ', $request->date);
            $start = date('Y-m-d', strtotime($dates[0]));
            $end = isset($dates[1]) && $dates[1] ? date('Y-m-d', strtotime($dates[1])) : $start;
        }

        $cost_center_ids = null;
        if (!empty($request->cost_center_id)) {
            $cost_center_ids = $request->cost_center_id ?? null;
            // dd($cost_center_ids);
        } elseif (!empty($request->cost_group_id)) {
            $cost_group = CostGroup::with('costCenters')
                ->where('id', $request->cost_group_id)
                ->where('status', 'active')
                ->first();

            $cost_center_ids = optional($cost_group->costCenters)->pluck('id')->unique()->all();
            // dd($cost_center_ids);
        }
        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization_id;
        $companies = Helper::access_org();    
        $cost_centers = Helper::getActiveCostCenters();
        $cost_groups = CostGroup::with('costCenters')->where('status', 'active')->get()->toArray();
        $locations = InventoryHelper::getAccessibleLocations();

        $group_name = Group::find($request->group)->name ?? ConstantHelper::PAYABLE;
        $vendors = [];
        $group = Helper::getGroupsQuery()->where('name', $group_name)->first();
        $drp_group = Helper::getGroupsQuery()->where('name', ConstantHelper::PAYABLE)->first();

        if ($group) {
            $ledger_groups = Helper::getGroupsQuery()->where('parent_group_id', $group->id)->pluck('id');
            if (count($ledger_groups) > 0) {
                $ages_all = [$request->age0 ?? 30, $request->age1 ?? 60, $request->age2 ?? 90, $request->age3 ?? 120, $request->age4 ?? 180];
                $all_ledgers = Ledger::where('status', 1)
                    ->where(function ($query) use ($ledger_groups) {
                        $query->whereIn('ledger_group_id', $ledger_groups);

                        foreach ($ledger_groups as $child) {
                            $query->orWhereJsonContains('ledger_group_id', (string) $child)->orWhereJsonContains('ledger_group_id', $child);
                        }
                    })
                    ->get();
                if (!is_null($ledger_groups))
                    $vendors = self::get_ledgers_data($ledger_groups, $ages_all, 'credit', $request->ledger, $start, $end, $org, $loc, $cost_center_ids,$due_date);
            } else if (isset($group->id)) {
                $ledger_groups = [$group->id];
                $ages_all = [$request->age0 ?? 30, $request->age1 ?? 60, $request->age2 ?? 90, $request->age3 ?? 120, $request->age4 ?? 180];
                $all_ledgers = Ledger::where('status', 1)
                    ->where(function ($query) use ($ledger_groups) {
                        $query->whereIn('ledger_group_id', $ledger_groups);

                        foreach ($ledger_groups as $child) {
                            $query->orWhereJsonContains('ledger_group_id', (string) $child)->orWhereJsonContains('ledger_group_id', $child);
                        }
                    })
                    ->get();

                if (!is_null($ledger_groups))
                    $vendors = self::get_ledgers_data($ledger_groups, $ages_all, 'credit', $request->ledger, $start, $end, $org, $loc, $cost_center_ids,$due_date);
            }
        }
        $all_groups = Group::whereIn('id', $drp_group->getAllChildIds())->get();
        $date = $request->date;
        $date2 = $end ? \Carbon\Carbon::parse($end)->format('jS-F-Y') : \Carbon\Carbon::parse(date('Y-m-d'))->format('jS-F-Y');
        $vendors = collect($vendors)->reject(function ($item) {
            return (float) $item->total_outstanding === 0.0;
        });
        return view('finance_report.creditors', compact('cost_centers', 'companies', 'organizationId', 'locations', 'vendors', 'all_groups', 'all_ledgers', 'date', 'date2', 'cost_groups'));
    }



    static function get_bucket_ages($diffDays, $ages)
    {
        if ($diffDays <= $ages[0] && $diffDays >= 0) {
            return 'days_0_30';
        } elseif ($diffDays <= $ages[1] && $diffDays >= $ages[0] + 1) {
            return 'days_30_60';
        } elseif ($diffDays <= $ages[2] && $diffDays >= $ages[1] + 1) {
            return 'days_60_90';
        } elseif ($diffDays <= $ages[3] && $diffDays >= $ages[2] + 1) {
            return 'days_90_120';
        } elseif ($diffDays <= $ages[4] && $diffDays >= $ages[3] + 1) {
            return 'days_120_180';
        } elseif ($diffDays > $ages[4]) {
            return 'days_above_180';
        }
    }
    function get_ledgers_data($ledger_groups, $ages_all, $type, $filter, $start, $end, $org, $loc, $cost,$due_date)
    {
        $amount = $type . '_amt_org';
        $ages0 = $ages_all[0];
        $ages1 = $ages_all[1];
        $ages2 = $ages_all[2];
        $ages3 = $ages_all[3];
        $ages4 = $ages_all[4];
        $organization_id = Helper::getAuthenticatedUser()->organization_id;
        $doc_types = $type === 'debit' ? [ConstantHelper::RECEIPTS_SERVICE_ALIAS, 'Receipt'] : [ConstantHelper::PAYMENTS_SERVICE_ALIAS, 'Payment'];
        $cus_type = $type === 'debit' ? 'customer' : 'vendor';
        $ledger_groups_all = [];

        foreach ($ledger_groups as $group) {
            $ledgers = Ledger::where('status', 1)
                ->where(function ($query) use ($group) {
                    $query->where('ledger_group_id', $group)
                        ->orWhereJsonContains('ledger_group_id', (string) $group)->orWhereJsonContains('ledger_group_id', $group);
                })
                ->pluck('id')
                ->toArray();
            if ($ledgers) {
                $vouchers = Voucher::withWhereHas('items', function ($query) use ($cost, $ledgers, $group, $type, $filter) {
                    $query->whereIn('ledger_id', $ledgers);
                    if (!empty($filter)) {
                        $query->where('ledger_id', $filter);
                    }
                    $query->where('ledger_parent_id', $group);
                    $query->where($type . '_amt_org', '>', 0);
                    $query->when(!is_null($cost), function ($q) use ($cost) {
                        // $q->where('cost_center_id', $cost);
                        if (is_array($cost)) {
                            $q->whereIn('cost_center_id', $cost);
                        } else {
                            $q->where('cost_center_id', $cost);
                        }
                    });
                })->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
                    ->when(!is_null($loc), function ($query) use ($loc) {
                        $query->where('location', $loc);
                    })
                    ->when(!is_null($org), function ($query) use ($org) {
                        $query->whereIn('organization_id', $org);
                    });

                if (!empty($start) && !empty($end)) {
                    $vouchers->whereBetween('document_date', [$start, $end]); // Apply only if both values exist
                }

                $vouchers = $vouchers->orderBy('document_date', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->pluck('id')
                    ->toArray();


                $l_ledger = ItemDetail::whereIn('ledger_id', $ledgers)
                    ->whereIn('voucher_id', $vouchers)
                    ->when(!is_null($cost), function ($q) use ($cost) {
                        // $q->where('cost_center_id', $cost);
                        if (is_array($cost)) {
                            $q->whereIn('cost_center_id', $cost);
                        } else {
                            $q->where('cost_center_id', $cost);
                        }
                    })
                    ->where($type . '_amt_org', '>', 0)->get()
                    ->groupBy('ledger_id')
                    ->map(function ($items) use ($ages0, $ages1, $ages2, $ages3, $ages4, $amount,$due_date) {
                        $totals = (object) [
                            'ledger_id' => null,
                            'ledger_name' => '',
                            'ledger_parent_id' => '',
                            'days_0_30' => 0,
                            'days_30_60' => 0,
                            'days_60_90' => 0,
                            'days_90_120' => 0,
                            'days_120_180' => 0,
                            'days_above_180' => 0,
                            'total_outstanding' => 0
                        ];
                        foreach ($items as $item) {
                            $totals->ledger_id = $item->ledger_id;
                            $totals->ledger_name = Ledger::find($item->ledger_id)->name;
                            $totals->ledger_parent_name = Group::find($item->ledger_parent_id)->name;
                            $totals->ledger_parent_id = $item->ledger_parent_id;
                            
                            $d_date = ($due_date =="invoice" || empty($item->due_date)) 
                            ? Voucher::find($item->voucher_id)->document_date: $item->due_date;
                            $documentDate = \Carbon\Carbon::parse($d_date)->format('Y-m-d');
                    
                            $days_diff = $documentDate ? now()->diffInDays(\Carbon\Carbon::createFromFormat('Y-m-d', $documentDate)) : 0;

                            if ($days_diff <= $ages0) {
                                $totals->days_0_30 += $item->$amount;
                            } elseif ($days_diff <= $ages1) {
                                $totals->days_30_60 += $item->$amount;
                            } elseif ($days_diff <= $ages2) {
                                $totals->days_60_90 += $item->$amount;
                            } elseif ($days_diff <= $ages3) {
                                $totals->days_90_120 += $item->$amount;
                            } elseif ($days_diff <= $ages4) {
                                $totals->days_120_180 += $item->$amount;
                            } else {
                                $totals->days_above_180 += $item->$amount;
                            }
                            $totals->total_outstanding += $item->$amount;
                        }
                        return $totals;
                    })->values();


                foreach ($l_ledger as $customer) {
                    $ledger = $customer->ledger_id;
                    $voucher = Voucher::withWhereHas('items', function ($query) use ($due_date,$cost, $ledger, $group, $type) {
                        $query->where('ledger_id', $ledger);
                        $query->where('ledger_parent_id', $group);
                        $query->where($type . '_amt_org', '>', 0);
                        $query->when(!is_null($cost), function ($q) use ($cost) {
                            // $q->where('cost_center_id', $cost);
                            if (is_array($cost)) {
                                $q->whereIn('cost_center_id', $cost);
                            } else {
                                $q->where('cost_center_id', $cost);
                            }
                        });
                    })

                        ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
                        ->when(!is_null($loc), function ($query) use ($loc) {
                            $query->where('location', $loc);
                        })
                        ->when(!is_null($org), function ($query) use ($org) {
                            $query->whereIn('organization_id', $org);
                        });

                    if (!empty($start) && !empty($end)) {
                        $voucher->whereBetween('document_date', [$start, $end]); // Apply date range filter only if both values exist
                    }

                    $voucher = $voucher->orderBy('document_date', 'asc')
                        ->orderBy('created_at', 'asc')
                        ->pluck('id')
                        ->toArray();

                    $model = $type == 'debit' ? Customer::class : Vendor::class;
                    $credit_days = $model::where('ledger_group_id', $group)
                        ->where('ledger_id', $ledger)
                        ->value('credit_days');

                    $overdue = self::get_overdue($type, $ages_all, $doc_types, $cus_type, $voucher, $credit_days, $group, $ledger, null, $start, $end,'overdue',$due_date);
                    $ages0 = self::get_overdue($type, $ages_all, $doc_types, $cus_type, $voucher, $credit_days, $group, $ledger, null, $start, $end, 'days_0_30',$due_date);
                    $ages1 = self::get_overdue($type, $ages_all, $doc_types, $cus_type, $voucher, $credit_days, $group, $ledger, null, $start, $end, 'days_30_60',$due_date);
                    $ages2 = self::get_overdue($type, $ages_all, $doc_types, $cus_type, $voucher, $credit_days, $group, $ledger, null, $start, $end, 'days_60_90',$due_date);
                    $ages3 = self::get_overdue($type, $ages_all, $doc_types, $cus_type, $voucher, $credit_days, $group, $ledger, null, $start, $end, 'days_90_120',$due_date);
                    $ages4 = self::get_overdue($type, $ages_all, $doc_types, $cus_type, $voucher, $credit_days, $group, $ledger, null, $start, $end, 'days_120_180',$due_date);
                    $ages5 = self::get_overdue($type, $ages_all, $doc_types, $cus_type, $voucher, $credit_days, $group, $ledger, null, $start, $end, 'days_above_180',$due_date);
                    $total_outstanding = self::get_overdue($type, $ages_all, $doc_types, $cus_type, $voucher, $credit_days, $group, $ledger, null, $start, $end, 'total_outstanding',$due_date);
                    $customer->days_0_30 = $ages0;
                    $customer->days_30_60 = $ages1;
                    $customer->days_60_90 = $ages2;
                    $customer->days_90_120 = $ages3;
                    $customer->days_120_180 = $ages4;
                    $customer->days_above_180 = $ages5;
                    $customer->total_outstanding = $total_outstanding;
                    $customer->credit_days = $credit_days;
                    $customer->overdue = $overdue > 0 ? $overdue : 0;
                }
                if (!is_null($l_ledger))
                    $ledger_groups_all = array_merge($l_ledger->toArray(), $ledger_groups_all);
            } else {
                if ($filter == "") {
                    $childs = Group::find($group)->getAllChildIds();
                    $ledgers = Ledger::where('status', 1)
                        ->where(function ($query) use ($childs) {
                            $query->whereIn('ledger_group_id', $childs);
                            foreach ($childs as $child) {
                                $query->orWhereJsonContains('ledger_group_id', (string) $child)->orWhereJsonContains('ledger_group_id', $child);
                            }
                        })->pluck('id')->toArray();


                    $vouchers = Voucher::withWhereHas('items', function ($query) use ($cost, $childs, $type, $ledgers) {
                        $query->whereIn('ledger_parent_id', $childs);
                        $query->whereIn('ledger_id', $ledgers);
                        $query->where($type . '_amt_org', '>', 0);
                        $query->when(!is_null($cost), function ($q) use ($cost) {
                            // $q->where('cost_center_id', $cost);
                            // $q->where('cost_center_id', $cost);
                            if (is_array($cost)) {
                                $q->whereIn('cost_center_id', $cost);
                            } else {
                                $q->where('cost_center_id', $cost);
                            }
                        });
                    })
                        // ->where('organization_id', $organization_id)
                        ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
                        ->when(!is_null($loc), function ($query) use ($loc) {
                            $query->where('location', $loc);
                        })
                        ->when(!is_null($org), function ($query) use ($org) {
                            $query->whereIn('organization_id', $org);
                        });

                    if (!empty($start) && !empty($end)) {
                        $vouchers->whereBetween('document_date', [$start, $end]); // Apply date range only if both values exist
                    }

                    $vouchers = $vouchers->orderBy('document_date', 'asc')
                        ->orderBy('created_at', 'asc')
                        ->pluck('id')
                        ->toArray();


                    $customer = ItemDetail::whereIn('voucher_id', $vouchers)
                        ->when(!is_null($cost), function ($q) use ($cost) {
                            // $q->where('cost_center_id', $cost);
                            if (is_array($cost)) {
                                $q->whereIn('cost_center_id', $cost);
                            } else {
                                $q->where('cost_center_id', $cost);
                            }
                        })->where($type . '_amt_org', '>', 0)->get()
                        ->groupBy('ledger_parent_id')
                        ->map(function ($items) use ($group, $ages0, $ages1, $ages2, $ages3, $ages4, $amount,$due_date) {
                            $totals = (object) [
                                'ledger_id' => null,
                                'ledger_name' => '',
                                'ledger_parent_id' => '',
                                'days_0_30' => 0,
                                'days_30_60' => 0,
                                'days_60_90' => 0,
                                'days_90_120' => 0,
                                'days_120_180' => 0,
                                'days_above_180' => 0,
                                'total_outstanding' => 0
                            ];
                            foreach ($items as $item) {
                            $d_date = ($due_date =="invoice" || empty($item->due_date)) 
                            ? Voucher::find($item->voucher_id)->document_date: $item->due_date;
                            $documentDate = \Carbon\Carbon::parse($d_date)->format('Y-m-d');

                                $totals->ledger_parent_name = Group::find($group)->name;
                                $totals->ledger_parent_id = $group;
                                $days_diff = $documentDate ? now()->diffInDays(\Carbon\Carbon::createFromFormat('Y-m-d', $documentDate)) : 0;

                                if ($days_diff <= $ages0) {
                                    $totals->days_0_30 += $item->$amount;
                                } elseif ($days_diff <= $ages1) {
                                    $totals->days_30_60 += $item->$amount;
                                } elseif ($days_diff <= $ages2) {
                                    $totals->days_60_90 += $item->$amount;
                                } elseif ($days_diff <= $ages3) {
                                    $totals->days_90_120 += $item->$amount;
                                } elseif ($days_diff <= $ages4) {
                                    $totals->days_120_180 += $item->$amount;
                                } else {
                                    $totals->days_above_180 += $item->$amount;
                                }
                                $totals->total_outstanding += $item->$amount;
                            }
                            return $totals;
                        })->values();
                    if (isset($customer[0])) {

                        $ages0 = self::get_overdue($type, $ages_all, $doc_types, $cus_type, $vouchers, 0, $group, null, null, $start, $end, 'days_0_30',$due_date);
                        $ages1 = self::get_overdue($type, $ages_all, $doc_types, $cus_type, $vouchers, 0, $group, null, null, $start, $end, 'days_30_60',$due_date);
                        $ages2 = self::get_overdue($type, $ages_all, $doc_types, $cus_type, $vouchers, 0, $group, null, null, $start, $end, 'days_60_90',$due_date);
                        $ages3 = self::get_overdue($type, $ages_all, $doc_types, $cus_type, $vouchers, 0, $group, null, null, $start, $end, 'days_90_120',$due_date);
                        $ages4 = self::get_overdue($type, $ages_all, $doc_types, $cus_type, $vouchers, 0, $group, null, null, $start, $end, 'days_120_180',$due_date);
                        $ages5 = self::get_overdue($type, $ages_all, $doc_types, $cus_type, $vouchers, 0, $group, null, null, $start, $end, 'days_above_180',$due_date);
                        $total_outstanding = self::get_overdue($type, $ages_all, $doc_types, $cus_type, $vouchers, 0, $group, null, null, $start, $end, 'total_outstanding',$due_date);
                        $customer = $customer[0];
                        $customer->days_0_30 = $ages0;
                        $customer->days_30_60 = $ages1;
                        $customer->days_60_90 = $ages2;
                        $customer->days_90_120 = $ages3;
                        $customer->days_120_180 = $ages4;
                        $customer->days_above_180 = $ages5;
                        $customer->total_outstanding = $total_outstanding;
                        $customer->credit_days = "-";
                        $customer->overdue = "-";
                        if (!is_null($customer))
                            $ledger_groups_all = array_merge([$customer], $ledger_groups_all);
                    }
                }
            }
        }

        $ledger_groups_all = collect($ledger_groups_all)->map(function ($item) {
            return (object) $item;
        });
        return $ledger_groups_all;
    }
    static function getAgedReceipts($vouchers, $aging, $doc_types, $start, $end)
    {
        $ages0 = $aging[0];
        $ages1 = $aging[1];
        $ages2 = $aging[2];
        $ages3 = $aging[3];
        $ages4 = $aging[4];
        //  $organization_id = Helper::getAuthenticatedUser()->organization_id;
        $ages = [];
        $ages[0] = $ages[1] = $ages[2] = $ages[3] = $ages[4] = $ages[5] = $ages[6] = 0;
        $refQuery = VoucherReference::whereIn('voucher_id', $vouchers)
            ->withWhereHas('voucherPayRec', function ($query) use ($doc_types, $start, $end) {
                //$query->where('organization_id', $organization_id);
                $query->where('document_status', ConstantHelper::POSTED);
                $query->whereIn('document_type', $doc_types);

                if (!empty($start) && !empty($end)) {
                    $query->whereBetween('document_date', [$start, $end]); // Apply created_at filter
                }
            });

        $ages[0] = (clone $refQuery)->whereHas('voucher', function ($query) use ($ages0) {
            $query->whereBetween('document_date', [now()->subDays($ages0)->toDateString(), now()->toDateString()]);
        })->sum('amount');


        $ages[1] = (clone $refQuery)->whereHas('voucher', function ($query) use ($ages0, $ages1) {
            $query->whereBetween('document_date', [
                now()->subDays($ages1)->toDateString(),
                now()->subDays($ages0 + 1)->toDateString()
            ]);
        })->sum('amount');

        $ages[2] = (clone $refQuery)->whereHas('voucher', function ($query) use ($ages1, $ages2) {
            $query->whereBetween('document_date', [
                now()->subDays($ages2)->toDateString(),
                now()->subDays($ages1 + 1)->toDateString()
            ]);
        })->sum('amount');

        $ages[3] = (clone $refQuery)->whereHas('voucher', function ($query) use ($ages2, $ages3) {
            $query->whereBetween('document_date', [
                now()->subDays($ages3)->toDateString(),
                now()->subDays($ages2 + 1)->toDateString()
            ]);
        })->sum('amount');

        $ages[4] = (clone $refQuery)->whereHas('voucher', function ($query) use ($ages3, $ages4) {
            $query->whereBetween('document_date', [
                now()->subDays($ages4)->toDateString(),
                now()->subDays($ages3 + 1)->toDateString()
            ]);
        })->sum('amount');

        $ages[5] = (clone $refQuery)->whereHas('voucher', function ($query) use ($ages4) {
            $query->where('document_date', '<', now()->subDays($ages4 + 1)->toDateString());
        })->sum('amount');

        $ages[6] = (clone $refQuery)->sum('amount');


        return $ages;
    }

    static function getAdvanceOnAccountType($cus_type, $group, $ledger = null, $start, $end, $type = "On Account")
    {

        $advanceQuery = PaymentVoucherDetails::where('type', $cus_type)
            ->where('reference', $type)
            ->withWhereHas('voucher', function ($query) use ($start, $end) {
                //$query->where('organization_id', Helper::getAuthenticatedUser()->organization_id)
                $query->where('document_status', ConstantHelper::POSTED);
                //$query->orderBy('document_date','asc');
    
                if (!empty($start) && !empty($end)) {
                    $query->whereBetween('document_date', [$start, $end]); // Apply date range filter
                }
            })->orderBy('orgAmount', 'desc')
            ->with('partyName')
            ->get();
        if ($ledger == null) {
            $advance = (clone $advanceQuery)->filter(function ($adv) use ($group) {
                $ledgerGroupId = $adv->ledger_group_id ?? optional($adv->partyName)->ledger_group_id;
                return in_array($ledgerGroupId, (array) $group);
            });
        } else {
            $advance = (clone $advanceQuery)->filter(function ($adv) use ($ledger, $group) {
                $ledgerId = $adv->ledger_id ?? optional($adv->partyName)->ledger_id;
                $ledgerGroupId = $adv->ledger_group_id ?? optional($adv->partyName)->ledger_group_id;

                return $ledger
                    ? ($ledgerId == $ledger && $ledgerGroupId == $group)
                    : in_array($ledgerGroupId, (array) $group);
            });
        }


        return $advance;
    }


    function getLedgersByGroup($group)
    {
        $drp_group = Group::find($group);
        $grps = $drp_group->getAllChildIds();
        $grps[] = $drp_group->id;
        $search_ledger = Group::whereIn('id', $grps)->get()->pluck('id');

        $all_ledgers = Ledger::where('status', 1)
            ->where(function ($query) use ($search_ledger) {
                $query->whereIn('ledger_group_id', $search_ledger);

                foreach ($search_ledger as $child) {
                    $query->orWhereJsonContains('ledger_group_id', (string) $child)->orWhereJsonContains('ledger_group_id', $child);
                }
            })
            ->get();
        return response()->json(['data' => $all_ledgers, 'status' => 200, 'message' => 'fetched']);
    }

    public static function get_overdue($type, $ages_all, $doc_types, $cus_type, $vouchers, $credit_days, $group, $ledger, $details = null, $start, $end, $sum_column = 'overdue',$due_date="invoice")
    {
        $amount = $type . '_amt_org';
        $ages0 = $ages_all[0];
        $ages1 = $ages_all[1];
        $ages2 = $ages_all[2];
        $ages3 = $ages_all[3];
        $ages4 = $ages_all[4];

        $vendors = ItemDetail::whereIn('voucher_id', $vouchers)
            ->where('ledger_id', $ledger)
            ->where($amount, '>', 0)
            ->withWhereHas('voucher', function ($query) {
                $query->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED);
                $query->orderBy('document_date', 'asc');
                $query->orderBy('created_at', 'asc');
            })->get()
            ->groupBy('voucher_id')
            ->map(function ($items) use ($ages0, $ages1, $ages2, $ages3, $ages4, $amount,$due_date) {
                $totals = (object) [
                    'id' => '',
                    'ledger_parent_id' => '',
                    'ledger_id' => '',
                    'days_0_30' => 0,
                    'days_30_60' => 0,
                    'days_60_90' => 0,
                    'days_90_120' => 0,
                    'days_120_180' => 0,
                    'days_above_180' => 0,
                    'total_outstanding' => 0,
                    'invoice_amount' => 0,
                    'document_date' => "",
                    'days_diff' => 0,
                    'due_date'=>''
                ];
                foreach ($items as $item) {
                    $d_date = ($due_date =="invoice" || empty($item->due_date)) 
                    ? Voucher::find($item->voucher_id)->document_date:$item->due_date;
                    $totals->document_date = $d_date;
                    $totals->due_date = $item->due_date;
                    $totals->ledger_parent_id = $item->ledger_parent_id;
                    $totals->ledger_id = $item->ledger_id;

                    $documentDate = \Carbon\Carbon::parse($d_date)->format('Y-m-d');
                    $totals->id = $item->voucher_id;
                    $days_diff = $documentDate ? now()->diffInDays(\Carbon\Carbon::createFromFormat('Y-m-d', $documentDate)) : 0;

                    if ($days_diff <= $ages0) {
                        $totals->days_0_30 += $item->$amount;
                    } elseif ($days_diff <= $ages1) {
                        $totals->days_30_60 += $item->$amount;
                    } elseif ($days_diff <= $ages2) {
                        $totals->days_60_90 += $item->$amount;
                    } elseif ($days_diff <= $ages3) {
                        $totals->days_90_120 += $item->$amount;
                    } elseif ($days_diff <= $ages4) {
                        $totals->days_120_180 += $item->$amount;
                    } else {
                        $totals->days_above_180 += $item->$amount;
                    }
                    $totals->invoice_amount += $item->$amount;
                    $totals->total_outstanding += $item->$amount;
                    $totals->days_diff = $days_diff;
                }
                return $totals;
            })->values();


        $result = [];


        foreach ($vendors as $vendor) {
            $ages = self::getAgedReceipts([$vendor->id], $ages_all, $doc_types, $start, $end);
            $voucher = Voucher::find($vendor->id);
            $bill_no = "";
            $invoice_amount = "";
            $view_route = "";
            if ($voucher->reference_service != null) {
                $model = Helper::getModelFromServiceAlias($voucher->reference_service);
                if ($model != null) {

                    $referenceDoc = $model::find($voucher->reference_doc_id);
                    if ($referenceDoc)
                        $bill_no = trim(
                            ($referenceDoc->doc_prefix ? $referenceDoc->doc_prefix . '-' : '') .
                            $referenceDoc->doc_no .
                            ($referenceDoc->doc_suffix ? '-' . $referenceDoc->doc_suffix : ''),
                            '-'
                        );
                    $invoice_amount = $vendor->invoice_amount;
                    $view_route = Helper::getRouteNameFromServiceAlias($voucher->reference_service, $voucher->reference_doc_id);
                }
            }
            $vs = $voucher->reference_service ? strtoupper($voucher->reference_service) . "-" : "";
            $result[] = [
                'id' => $voucher->id,
                'ledger_parent_id' => $vendor->ledger_parent_id,
                'ledger_id' => $vendor->ledger_id,
                'due_date'=>$vendor->due_date,
                'bill_no' => $vs . $bill_no,
                'view_route' => $view_route,
                'created_at' => $voucher?->created_at,
                'voucher_no' => $voucher?->series?->book_code . "-" . $voucher->voucher_no,
                'document_date' => date('d-m-Y', strtotime($vendor->document_date)),
                'total_outstanding' => $vendor->total_outstanding - $ages[6],
                'days_0_30' => $vendor->days_0_30 - $ages[0],
                'days_30_60' => $vendor->days_30_60 - $ages[1],
                'days_60_90' => $vendor->days_60_90 - $ages[2],
                'days_90_120' => $vendor->days_90_120 - $ages[3],
                'days_120_180' => $vendor->days_120_180 - $ages[4],
                'days_above_180' => $vendor->days_above_180 - $ages[5],
                'overdue' => 0,
                'overdue_days' => 0,
                'diff_days' => $vendor->days_diff,
                'invoice_amount' => $invoice_amount,

            ];
        }

        $lastIndex = count($result) - 1; // Get last index of result
        usort($result, function ($a, $b) {
            return strtotime($a['document_date']) <=> strtotime($b['document_date']);
        });
        if ($ledger == null) {

            $advanceData = [];

            // Step 1: Collect unique ledger/parent combinations from $result
            $uniqueLedgerPairs = collect($result)->map(function ($res) {
                return [
                    'ledger_id' => $res['ledger_id'],
                    'ledger_parent_id' => $res['ledger_parent_id']
                ];
            })->unique()->values();

            // Step 2: Precompute advance for each unique combination
            foreach ($uniqueLedgerPairs as $pair) {
                $key = 'ledger' . $pair['ledger_id'] . '_parent' . $pair['ledger_parent_id'];

                $advance = self::getAdvanceOnAccountType(
                    $cus_type,
                    $pair['ledger_parent_id'],
                    $pair['ledger_id'],
                    $start,
                    $end,
                    'On Account'
                );

                $sum = (clone $advance)->sum('orgAmount');
                $latest = (clone $advance)->sortByDesc('document_date')->first();

                $advanceData[$key] = [
                    'remaining' => $sum,
                    'ageBucket' => $latest
                        ? self::get_bucket_ages(now()->diffInDays($latest->document_date), $ages_all)
                        : null,
                    'lastIndex' => null,
                ];
            }

            // Step 3: Deduct advance in the loop
            foreach ($result as $index => &$res) {
                $key = 'ledger' . $res['ledger_id'] . '_parent' . $res['ledger_parent_id'];

                if (!isset($advanceData[$key]) || $advanceData[$key]['remaining'] <= 0) {
                    continue;
                }

                $bucket = self::get_bucket_ages($res['diff_days'], $ages_all);
                $deduct = min($advanceData[$key]['remaining'], $res[$bucket]);

                $res[$bucket] -= $deduct;
                $res['total_outstanding'] -= $deduct;
                $advanceData[$key]['remaining'] -= $deduct;
                $advanceData[$key]['lastIndex'] = $index;
            }

            // Step 4: Apply remaining advance to last row per group
            foreach ($advanceData as $key => $groupl) {
                if ($groupl['remaining'] > 0 && $groupl['ageBucket'] && $groupl['lastIndex'] !== null) {
                    $idx = $groupl['lastIndex'];
                    $bucket = $groupl['ageBucket'];

                    if (isset($result[$idx][$bucket])) {
                        $result[$idx][$bucket] -= $groupl['remaining'];
                        $result[$idx]['total_outstanding'] -= $groupl['remaining'];
                    }
                }
            }


            //calculate advance
            // First, get unique pairs based on ledger_id and ledger_parent_id
            $uniquePairs = collect($result)->map(function ($res) {
                return [
                    'ledger_id' => $res['ledger_id'],
                    'ledger_parent_id' => $res['ledger_parent_id']
                ];
            })->unique();

            // Now, loop through each unique pair and apply the advance sum
            foreach ($uniquePairs as $pair) {
                // Get the advance items based on the current ledger_id and ledger_parent_id
                $advanceItems = self::getAdvanceOnAccountType($cus_type, $pair['ledger_parent_id'], $pair['ledger_id'], $start, $end, 'Advance');
                $remainingAdvanceAmount = $advanceItems->sum('orgAmount'); // Get total advance amount for the pair

                // If there is any advance amount, apply it to the result
                if ($remainingAdvanceAmount > 0) {
                    // Loop through the result set to apply the advance to each corresponding ledger
                    foreach ($result as &$res) {
                        if ($res['ledger_id'] == $pair['ledger_id'] && $res['ledger_parent_id'] == $pair['ledger_parent_id']) {
                            // Loop through each advance item for this ledger/parent pair
                            foreach ($advanceItems as $advanceItem) {
                                // Check if the advance's voucher document_date is earlier than res['document_date']
                                // For voucher document date (assumed format: Y-m-d)
                                $voucherDate = $advanceItem->voucher->document_date;         // Format: Y-m-d
                                $voucherCreatedAt = $advanceItem->voucher->created_at;       // Format: Y-m-d H:i:s or Carbon

                                $voucherTime = date('H:i:s', strtotime($voucherCreatedAt));  // Extract time part
                                $voucherDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $voucherDate . ' ' . $voucherTime);

                                $advanceVoucherDate = $voucherDateTime ? $voucherDateTime->getTimestamp() : null;


                                // --- Result Document DateTime ---
                                $resDocDate = $res['document_date'];         // Format: d-m-Y
                                $resCreatedAt = $res['created_at'];          // Format: Y-m-d H:i:s or similar

                                $resTime = date('H:i:s', strtotime($resCreatedAt));          // Extract time part
                                $resDateTime = \DateTime::createFromFormat('d-m-Y H:i:s', $resDocDate . ' ' . $resTime);

                                $resDate = $resDateTime ? $resDateTime->getTimestamp() : null;

                                if ($advanceVoucherDate < $resDate) { // Only proceed if the advance date is before the result date
                                    $buckets = ['days_0_30', 'days_30_60', 'days_60_90', 'days_90_120', 'days_120_180', 'days_above_180'];

                                    // Loop through the aging buckets for the current result
                                    foreach ($buckets as $bucket) {
                                        if ($remainingAdvanceAmount <= 0) {
                                            break; // Stop applying the advance if no amount is left
                                        }

                                        // Deduct the minimum of the remaining advance or the value in the current bucket
                                        $deductAmount = min($remainingAdvanceAmount, $res[$bucket]);
                                        $res[$bucket] -= $deductAmount; // Reduce the bucket value
                                        $remainingAdvanceAmount -= $deductAmount; // Reduce the advance amount
                                        $res['total_outstanding'] -= $deductAmount; // Reduce the total outstanding
                                    }
                                }
                            }
                        }
                    }
                }
            }




            // //get Advance

            // $advancePaymentMap = [];

            // // Step 1: Get unique combinations of ledger_id + ledger_parent_id
            // $uniquePairs = collect($result)->map(function ($res) {
            //     return [
            //         'ledger_id' => $res['ledger_id'],
            //         'ledger_parent_id' => $res['ledger_parent_id']
            //     ];
            // })->unique();

            // // Step 2: Precompute advance payment data
            // foreach ($uniquePairs as $pair) {
            //     $ledgerId = $pair['ledger_id'];
            //     $parentId = $pair['ledger_parent_id'];
            //     $advancePaymentKey = 'ledger' . $ledgerId . '_parent' . $parentId;

            //     $advance = self::getAdvanceOnAccountType($cus_type, $parentId, $ledgerId, $start, $end, 'Advance');
            //     $totalAdvanceAmount = (clone $advance)->sum('orgAmount');
            //     $latestAdvance = (clone $advance)->sortByDesc('document_date')->first();

            //     $advancePaymentMap[$advancePaymentKey] = [
            //         'remaining_advance_amount' => $totalAdvanceAmount,
            //         'advance_age_bucket' => $latestAdvance
            //             ? self::get_bucket_ages(now()->diffInDays($latestAdvance->document_date), $ages_all)
            //             : null,
            //         'last_applied_index' => null,
            //         ''

            //     ];
            // }

            // // Step 3: Apply advance to aging buckets
            // foreach ($result as $index => &$res) {
            //     $ledgerId = $res['ledger_id'];
            //     $parentId = $res['ledger_parent_id'];
            //     $advancePaymentKey = 'ledger' . $ledgerId . '_parent' . $parentId;

            //     if (
            //         !isset($advancePaymentMap[$advancePaymentKey]) ||
            //         $advancePaymentMap[$advancePaymentKey]['remaining_advance_amount'] <= 0
            //     ) {
            //         continue;
            //     }

            //     $agingBucket = self::get_bucket_ages($res['diff_days'], $ages_all);
            //     $vendorDateTimestamp = strtotime($advance->voucher->document_date);
            //     $resDateTimestamp = strtotime($res['document_date']);

            //     if ($vendorDateTimestamp < $resDateTimestamp) {
            //     $deductAmount = min($advancePaymentMap[$advancePaymentKey]['remaining_advance_amount'], $res[$agingBucket]);

            //     $res[$agingBucket] -= $deductAmount;
            //     $res['total_outstanding'] -= $deductAmount;
            //     $advancePaymentMap[$advancePaymentKey]['remaining_advance_amount'] -= $deductAmount;
            //     $advancePaymentMap[$advancePaymentKey]['last_applied_index'] = $index;
            //     }
            // }

            // // Step 4: If any remaining advance, apply to latest matched row
            // foreach ($advancePaymentMap as $advancePaymentKey => $data) {
            //     if (
            //         $data['remaining_advance_amount'] > 0 &&
            //         $data['advance_age_bucket'] &&
            //         $data['last_applied_index'] !== null
            //     ) {
            //         $idx = $data['last_applied_index'];
            //         $bucket = $data['advance_age_bucket'];

            //         if (isset($result[$idx][$bucket])) {
            //             $result[$idx][$bucket] -= $data['remaining_advance_amount'];
            //             $result[$idx]['total_outstanding'] -= $data['remaining_advance_amount'];
            //         }
            //     }
            // }





            //     $groups  = Group::find($group)->getAllChildIds();
            //     $groups[] = $group;

            // foreach($groups as $grp){
            //     $ledgers = Ledger::where('ledger_group_id',$grp)
            //     ->orWhereJsonContains('ledger_group_id', (string)$grp)->pluck('id')->toArray();






        } else {
            $advance = self::getAdvanceOnAccountType($cus_type, $group, $ledger, $start, $end, 'On Account');
            $advanceSum = (clone $advance)->sum('orgAmount');
            $advanceAges = (clone $advance)->sortByDesc('document_date')->first();

            if ($advanceAges) {
                $difDays = now()->diffInDays($advanceAges->document_date);
                $avanceAgesbucket = self::get_bucket_ages($difDays, $ages_all);
            }

            foreach ($result as &$res) {
                $bucket = self::get_bucket_ages($res['diff_days'], $ages_all);
                if ($advanceSum > 0) {
                    $deductAmount = min($advanceSum, $res[$bucket]);
                    $res[$bucket] -= $deductAmount; // Reduce the bucket value
                    $advanceSum -= $deductAmount; // Reduce the advance sum
                    $res['total_outstanding'] -= $deductAmount; // Track total deducted
                }
            }
            if (isset($avanceAgesbucket) && $advanceSum > 0) {
                $result[$lastIndex][$avanceAgesbucket] -= $advanceSum;
                $result[$lastIndex]['total_outstanding'] -= $advanceSum;
            }


            $advanceItems = self::getAdvanceOnAccountType($cus_type, $group, $ledger, $start, $end, 'Advance');

            // Initialize the array to store remaining advances by date
            $remainingAdvancesByDate = [];
            $totAdvancesByDate = [];
            $resDateTimestampArr = [];

            // Initialize the total remaining advance amount
            //$remainingAdvanceAmount = $advanceItems->sum('orgAmount');
            foreach ($advanceItems as $advanceItem) {
                // Get the voucher document date and created_at time for advance
                $documentDate = $advanceItem->voucher->document_date; // Format: 'Y-m-d'
                $createdAt = $advanceItem->voucher->created_at;       // Format: 'Y-m-d H:i:s'
                $advanceDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $documentDate . ' ' . date('H:i:s', strtotime($createdAt)));

                $vendorDateTimestamp = $advanceDateTime ? $advanceDateTime->getTimestamp() : null;

                // Store remaining advance for this specific date in the array
                if ($vendorDateTimestamp) {
                    $totAdvancesByDate[$vendorDateTimestamp] = (int) $advanceItem->orgAmount;
                    $remainingAdvancesByDate[$vendorDateTimestamp] = (int) $advanceItem->orgAmount;
                }
            }



            // Loop through the results
            foreach ($result as $index => &$res) {

                $bucket = self::get_bucket_ages($res['diff_days'], $ages_all);
                // Get the result document date and created_at time
                $docDateInput = Carbon::createFromFormat('d-m-Y', $res['document_date'])->format('Y-m-d'); // Format: 'd-m-Y'

                $createdTimeInput = $res['created_at']; // Format: 'Y-m-d H:i:s'

                // Extract the time part from created_at
                $timeFromCreated = date('H:i:s', strtotime($createdTimeInput));

                // Combine and create DateTime object for result
                $resDateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $docDateInput . ' ' . $timeFromCreated);
                $resDateTimestamp = $resDateTime ? $resDateTime->getTimestamp() : null;


                // Check if the result date is before the advance date

                // Reset bucketTotalDeducted before each advance deduction
                $filtered = array_filter(
                    $remainingAdvancesByDate,
                    fn($v, $k) => $k < $resDateTimestamp && $v > 0,
                    ARRAY_FILTER_USE_BOTH
                );

                if (!empty($filtered) && $res[$bucket] > 0) {
                    foreach ($filtered as $advanceDate => $advanceAmount) {
                        if ($res[$bucket] <= 0) {
                            break; // stop once there's nothing left to deduct
                        }

                        // Deduct the smaller between the available advance and the bucket amount
                        $deductAmount = min($advanceAmount, $res[$bucket]);
                        $res[$bucket] -= $deductAmount;
                        $remainingAdvancesByDate[$advanceDate] -= $deductAmount;
                        $res['total_outstanding'] -= $deductAmount;
                    }
                }
            }
        }
        //dd($remainingAdvancesByDate,$totAdvancesByDate);






        foreach ($result as &$res) {
            $creditDays = $credit_days ?? 0; // Ensure credit_days exists
            $dueDate = date('d-m-Y', strtotime("+$creditDays days", strtotime($res['document_date'])));
            $today = date('d-m-Y');

            $overdue = (strtotime($today) > strtotime($dueDate)) ? $res['total_outstanding'] : 0;
            $overdueDays = (strtotime($today) > strtotime($res['document_date'])) ? floor((strtotime($today) - strtotime($res['document_date'])) / (60 * 60 * 24)) : 0;
            $res['overdue'] = $overdue;
            $res['overdue_days'] = ($res['total_outstanding'] > 0) ? (int) $overdueDays : "-";
        }
        if ($details)
            return $result;
        else
            return array_sum(array_column($result, $sum_column));
    }


    function get_bucket($diffDays)
    {
        if ($diffDays <= 30 && $diffDays >= 0) {
            return 'days_0_30';
        } elseif ($diffDays <= 60 && $diffDays >= 31) {
            return 'days_30_60';
        } elseif ($diffDays <= 90 && $diffDays >= 61) {
            return 'days_60_90';
        } elseif ($diffDays <= 120 && $diffDays >= 91) {
            return 'days_90_120';
        } elseif ($diffDays <= 180 && $diffDays >= 121) {
            return 'days_120_180';
        } elseif ($diffDays > 180) {
            return 'days_above_180';
        }
    }
    public static function getLedgerDetails($type, $ledger, $group, Request $request)
    {
        $model = $type == 'debit' ? Customer::class : Vendor::class;
        $userData = $model::where('ledger_group_id', $group)
            ->where('ledger_id', $ledger)->first();
        $scheduler = CrDrReportScheduler::where('toable_id', $userData?->id)
            ->where('toable_type', $model)->first();

        $cc_users = Helper::getOrgWiseUserAndEmployees(Helper::getAuthenticatedUser()->organization_id);

        $userchk = Helper::userCheck();


        $to_users = $userData?->id;
        $to_user_mail = $userData?->email;
        $to_type = $model;




        $start = null;
        $end = null;
        if ($request->date) {
            $dates = explode(' to ', $request->date);
            $start = date('Y-m-d', strtotime($dates[0]));
            $end = isset($dates[1]) && $dates[1] ? date('Y-m-d', strtotime($dates[1])) : $start;
        }
        $loc = null;
        $cost = null;
        $org = null;

        if ($request->has('location_id'))
            $loc = $request->location_id;

        if ($request->has('cost_center_id'))
            $cost = $request->cost_center_id;

        $cost_center_ids = null;
        if (!empty($request->cost_center_id)) {
            $cost_center_ids = $request->cost_center_id ?? null;
            // dd($cost_center_ids);
        } elseif (!empty($request->cost_group_id)) {
            $cost_group = CostGroup::with('costCenters')
                ->where('id', $request->cost_group_id)
                ->where('status', 'active')
                ->first();

            $cost_center_ids = optional($cost_group->costCenters)->pluck('id')->unique()->all();
            // dd($cost_center_ids);
        }
        if ($request->has('organization_id'))
            $org = array_filter(array_map('intval', explode(',', $request->organization_id)));


        $organization_id = Helper::getAuthenticatedUser()->organization_id;
        $ages_all = [$request->age0 ?? 30, $request->age1 ?? 60, $request->age2 ?? 90, $request->age3 ?? 120, $request->age4 ?? 180];

        $ledger_name = Ledger::find($ledger)?->name;
        $group_name = Group::find($group)?->name;

        $credit_days = $model::where('ledger_group_id', $group)
            ->where('ledger_id', $ledger)
            ->value('credit_days');
        $credit_days = $credit_days ?? 0;
        $doc_types = $type === 'debit' ? [ConstantHelper::RECEIPTS_SERVICE_ALIAS, 'Receipt'] : [ConstantHelper::PAYMENTS_SERVICE_ALIAS, 'Payment'];
        $cus_type = $type === 'debit' ? 'customer' : 'vendor';

        $vouchers = Voucher::withWhereHas('items', function ($query) use ($cost_center_ids, $ledger, $group, $type) {
            $query->where('ledger_id', $ledger);
            $query->where('ledger_parent_id', $group);
            $query->where($type . '_amt_org', '>', 0);
            $query->when(!is_null($cost_center_ids), function ($q) use ($cost_center_ids) {
                // $q->where('cost_center_id', $cost);
                if (is_array($cost_center_ids)) {
                    $q->whereIn('cost_center_id', $cost_center_ids);
                } else {
                    $q->where('cost_center_id', $cost_center_ids);
                }
            });
        })
            // ->where('organization_id', $organization_id)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->when(!is_null($loc), function ($query) use ($loc) {
                $query->where('location', $loc);
            })
            ->when(!is_null($org), function ($query) use ($org) {
                $query->whereIn('organization_id', $org);
            });

        if (!empty($start) && !empty($end)) {
            $vouchers->whereBetween('document_date', [$start, $end]); // Apply filter for document_date
        }

        $vouchers = $vouchers->orderBy('document_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->pluck('id')
            ->toArray();
        if ($vouchers)
            $data = self::get_overdue($type, $ages_all, $doc_types, $cus_type, $vouchers, $credit_days, $group, $ledger, 1, $start, $end);
        else
            $data = [];

        $data = json_decode(json_encode($data));
        $date = $request->date;
        $date2 = $end ? \Carbon\Carbon::parse($end)->format('jS-F-Y') : \Carbon\Carbon::parse(date('Y-m-d'))->format('jS-F-Y');
        ;
        $user = Helper::getAuthenticatedUser();
        $organizationId = $user->organization_id;
        
        $companies = Helper::access_org();
        $cost_centers = Helper::getActiveCostCenters();
        $cost_groups = CostGroup::with('costCenters')->where('status', 'active')->get()->toArray();
        $locations = InventoryHelper::getAccessibleLocations();

        return view('finance_report.details', compact('cost_centers', 'companies', 'cost_groups', 'organizationId', 'locations', 'ledger_name', 'scheduler', 'group_name', 'credit_days', 'data', 'cc_users', 'to_users', 'to_user_mail', 'to_type', 'ledger', 'group', 'type', 'date', 'date2'));
    }
    public function addScheduler(Request $request)
    {
        // Validate request data
        $validatedData = $request->validate([
            'to' => 'required|array',
            'cc' => 'nullable|array',
            'type' => 'required|string',
            'date' => 'required|date',
            'remarks' => 'nullable|string',
            'ledger_id' => 'nullable|int',
            'ledger_group_id' => 'nullable|int',
            'report_type' => 'nullable|string'
        ]);
        $toIds = $validatedData['to'];

        foreach ($toIds as $toId) {
            CrDrReportScheduler::updateOrCreate(
                [
                    'toable_id' => $toId['id'],
                    'toable_type' => $toId['type']
                ],
                [
                    'type' => $validatedData['type'],
                    'date' => $validatedData['date'],
                    'cc' => json_encode($validatedData['cc']),
                    'remarks' => $validatedData['remarks'],
                    'ledger_group_id' => $validatedData['ledger_group_id'],
                    'ledger_id' => $validatedData['ledger_id'],
                    'report_type' => $validatedData['report_type'],
                    'organization_id' => Helper::getAuthenticatedUser()->organization_id,
                    'created_by' => Helper::getAuthenticatedUser()->auth_user_id,
                ]
            );
        }

        return Response::json(['success' => 'Scheduler Added Successfully!']);
    }
    public static function getLedgerDetailsReport($type, $ledger, $group)
    {
        $start = null;
        $end = null;
        $ages_all = [$request->age0 ?? 30, $request->age1 ?? 60, $request->age2 ?? 90, $request->age3 ?? 120, $request->age4 ?? 180];
        $model = $type == 'debit' ? Customer::class : Vendor::class;
        $credit_days = $model::where('ledger_group_id', $group)
            ->where('ledger_id', $ledger)
            ->value('credit_days');
        $credit_days = $credit_days ?? 0;
        $doc_types = $type === 'debit' ? [ConstantHelper::RECEIPTS_SERVICE_ALIAS, 'Receipt'] : [ConstantHelper::PAYMENTS_SERVICE_ALIAS, 'Payment'];
        $cus_type = $type === 'debit' ? 'customer' : 'vendor';
        $vouchers = Voucher::withWhereHas('items', function ($query) use ($ledger, $group, $type) {
            $query->where('ledger_id', $ledger);
            $query->where('ledger_parent_id', $group);
            $query->where($type . '_amt_org', '>', 0);
        })
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED);

        if (!empty($start) && !empty($end)) {
            $vouchers->whereBetween('document_date', [$start, $end]); // Apply filter for document_date
        }

        $vouchers = $vouchers->orderBy('document_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->pluck('id')
            ->toArray();
        if ($vouchers)
            $data = self::get_overdue($type, $ages_all, $doc_types, $cus_type, $vouchers, $credit_days, $group, $ledger, 1, $start, $end);
        else
            $data = [];

        $data = json_decode(json_encode($data));

        return $data;
    }
    public static function getLedgerDetailsPrint(Request $request = null, $type, $ledger, $group, $bill_type = "outstanding", $organization_id = null, $auth_user = null)
    {
        try {
            $start = null;
            $end = null;
            $loc = null;
            $cost = null;
            $org = null;

            if ($request->has('location_id'))
                $loc = $request->location_id;

            if ($request->has('cost_center_id'))
                $cost = $request->cost_center_id;

            if ($request->has('organization_id'))
                $org = array_filter(array_map('intval', explode(',', $request->organization_id)));


            if ($request->date) {
                $dates = explode(' to ', $request->date);
                $start = date('Y-m-d', strtotime($dates[0]));
                $end = date('Y-m-d', strtotime($dates[1]));
            }

            $cost_center_ids = null;
            if (!empty($request->cost_center_id)) {
                $cost_center_ids = $request->cost_center_id ?? null;
                // dd($cost_center_ids);
            } elseif (!empty($request->cost_group_id)) {
                $cost_group = CostGroup::with('costCenters')
                    ->where('id', $request->cost_group_id)
                    ->where('status', 'active')
                    ->first();

                $cost_center_ids = optional($cost_group->costCenters)->pluck('id')->unique()->all();
                // dd($cost_center_ids);
            }


            if ($organization_id == null)
                $organization_id = Helper::getAuthenticatedUser()->organization_id;

            $ages_all = [$request->age0 ?? 30, $request->age1 ?? 60, $request->age2 ?? 90, $request->age3 ?? 120, $request->age4 ?? 180];

            $ledger_name = Ledger::find($ledger)?->name ?? throw new \Exception('Ledger not found.');
            $group_name = Group::find($group)?->name ?? throw new \Exception('Group not found.');
            $model = $type == 'debit' ? Customer::class : Vendor::class;
            $credit_days = $model::where('ledger_group_id', $group)
                ->where('ledger_id', $ledger)
                ->value('credit_days') ?? 0;

            $doc_types = $type === 'debit' ? [ConstantHelper::RECEIPTS_SERVICE_ALIAS, 'Receipt'] : [ConstantHelper::PAYMENTS_SERVICE_ALIAS, 'Payment'];
            $cus_type = $type === 'debit' ? 'customer' : 'vendor';

            $vouchers = Voucher::withWhereHas('items', function ($query) use ($cost_center_ids, $ledger, $group, $type) {
                $query->where('ledger_id', $ledger);
                $query->where('ledger_parent_id', $group);
                $query->where($type . '_amt_org', '>', 0);
                $query->when(!is_null($cost_center_ids), function ($q) use ($cost_center_ids) {
                    // $q->where('cost_center_id', $cost);
                    if (is_array($cost_center_ids)) {
                        $q->whereIn('cost_center_id', $cost_center_ids);
                    } else {
                        $q->where('cost_center_id', $cost_center_ids);
                    }
                });

            })
                ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
                ->when(!is_null($loc), function ($query) use ($loc) {
                    $query->where('location', $loc);
                })
                ->when(!is_null($org), function ($query) use ($org) {
                    $query->whereIn('organization_id', $org);
                });

            if (!empty($start) && !empty($end)) {
                $vouchers->whereBetween('document_date', [$start, $end]);
            }

            $vouchers = $vouchers->orderBy('document_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->pluck('id')
                ->toArray();

            $data = $vouchers
                ? self::get_overdue($type, $ages_all, $doc_types, $cus_type, $vouchers, $credit_days, $group, $ledger, 1, $start, $end)
                : [];

            $data = json_decode(json_encode($data));
            $party = $model::where('ledger_group_id', $group)
                ->where('ledger_id', $ledger)
                ->first();

            if (!$party) {
                throw new \Exception("Party (" . ($type == 'debit' ? 'customer' : 'vendor') . ") not found for the selected group and ledger.");
            }

            $organization = Organization::find($organization_id);
            if (!$organization) {
                throw new \Exception("Organization not found.");
            }

            $organizationAddress = Address::with(['city', 'state', 'country'])
                ->where('addressable_id', $organization_id)
                ->where('addressable_type', Organization::class)
                ->first();

            if (!$organizationAddress) {
                throw new \Exception("Organization address not found.");
            }

            $party_address = ErpAddress::with(['city', 'state', 'country'])
                ->where('addressable_id', $party->id)
                ->where('addressable_type', $model)
                ->first();

            $total_value = $bill_type == "outstanding"
                ? array_sum(array_column(array_filter($data, fn($item) => $item->total_outstanding > 0), 'total_outstanding'))
                : array_sum(array_column(array_filter($data, fn($item) => $item->overdue > 0), 'overdue'));

            if ($total_value == 0)
                throw new \Exception('No outstanding due for this ledger.');

            $in_words = Helper::numberToWords($total_value) . " only.";
            $total_value = Helper::formatIndianNumber($total_value);

            $auth_user = $auth_user
                ? AuthUser::find($auth_user)
                : Helper::getAuthenticatedUser();

            $orgLogo = Helper::getOrganizationLogo($organization_id);

            $pdf = PDF::loadView('finance_report.print', [
                'orgLogo' => $orgLogo,
                'ledger_name' => $ledger_name,
                'group_name' => $group_name,
                'credit_days' => $credit_days,
                'data' => $data,
                'ledger' => $ledger,
                'group' => $group,
                'type' => $type,
                'party' => $party,
                'organization' => $organization,
                'party_address' => $party_address,
                'total_value' => $total_value,
                'in_words' => $in_words,
                'auth_user' => $auth_user,
                'bill_type' => $bill_type,
                'organizationAddress' => $organizationAddress,
            ]);

            $fileName = str_replace(' ', '_', $ledger_name)
                . '_Account_Statment (' . ($type == "debit" ? 'Debtor' : 'Creditor') . ')'
                . date('Y-m-d') . '.pdf';

            $pdf->setPaper('A4', 'portrait');
            return $pdf->stream($fileName);
        } catch (\Throwable $e) {
            Log::error("Ledger Print Error", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if (request()->ajax()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }
            return redirect()->route('crdr.report.ledger.details', [
                'type' => $type,
                'ledger' => $ledger,
                'group' => $group
            ])->with('print_error', $e->getMessage());
        }
    }

    public function exportDebitorCreditor(Request $request)
    {
        $type = $request->type;

        if ($type === 'credit') {
            $entities = is_string($request->vendors) ? json_decode($request->vendors) : $request->vendors;
        } elseif ($type === 'debit') {
            $entities = is_string($request->customers) ? json_decode($request->customers) : $request->customers;
        } else {
            $entities = [];
        }




        $groupIdFromRequest = $request->group_id;

        $organization = Helper::getAuthenticatedUser()->organization->name ?? '';
        // $defaultGroupName = ;

        // If group_id is selected, use it globally
        if ($groupIdFromRequest) {
            // $groupName = Group::find($groupIdFromRequest)?->name ?? '';

            $structuredRecords = [
                'group_name' => $organization,
                'type' => $type,
                'entities' => [],
                'date' => $request->date,
                'date2' => $request->date2,
            ];

            foreach ($entities as $item) {
                $ledger = $item->ledger_id;
                $ledgerData = self::prepareLedgerDataOnly($type, $ledger, $groupIdFromRequest, $request);

                $structuredRecords['entities'][] = [
                    'vendor_name' => $item->ledger_name,
                    'records' => $ledgerData['data'],
                    'credit_days' => $ledgerData['credit_days']
                ];
            }
        } else {
            // No group_id selected — still provide default group_name
            $structuredRecords = [
                'group_name' => $organization,
                'type' => $type,
                'date' => $request->date,
                'date2' => $request->date2,
                'entities' => []
            ];

            foreach ($entities as $item) {
                $ledger = $item->ledger_id;
                $groupId = $item->ledger_parent_id;

                if (!$groupId)
                    continue;

                $groupName = Group::find($groupId)?->name ?? 'Unknown Group';

                $ledgerData = self::prepareLedgerDataOnly($type, $ledger, $groupId, $request);

                $structuredRecords['entities'][] = [
                    'vendor_name' => $item->ledger_name,
                    'group_name' => $groupName,
                    'records' => $ledgerData['data'],
                    'credit_days' => $ledgerData['credit_days']
                ];
            }
        }
        $filename = $type === 'credit'
            ? 'creditor-ledger.xlsx'
            : ($type === 'debit' ? 'debitor-ledger.xlsx' : 'ledger-report.xlsx');

        return Excel::download(
            new DebitorCreditoExcelExport($structuredRecords),
            $filename
        );
    }



    public static function prepareLedgerDataOnly($type, $ledger, $group, Request $request)
    {
        $model = $type == 'debit' ? Customer::class : Vendor::class;

        $start = null;
        $end = null;
        if ($request->date) {
            $dates = explode(' to ', $request->date);
            $start = date('Y-m-d', strtotime($dates[0]));
            $end = date('Y-m-d', strtotime($dates[1]));
        }
        $loc = null;
        $cost = null;
        $org = null;

        if ($request->has('location_id'))
            $loc = $request->location_id;

        if ($request->has('cost_center_id'))
            $cost = $request->cost_center_id;

        if ($request->has('organization_id'))
            $org = array_filter(array_map('intval', explode(',', $request->organization_id)));

        $cost_center_ids = null;
        if (!empty($request->cost_center_id)) {
            $cost_center_ids = $request->cost_center_id ?? null;
            // dd($cost_center_ids);
        } elseif (!empty($request->cost_group_id)) {
            $cost_group = CostGroup::with('costCenters')
                ->where('id', $request->cost_group_id)
                ->where('status', 'active')
                ->first();

            $cost_center_ids = optional($cost_group->costCenters)->pluck('id')->unique()->all();
            // dd($cost_center_ids);
        }

        $ages_all = [
            $request->age0 ?? 30,
            $request->age1 ?? 60,
            $request->age2 ?? 90,
            $request->age3 ?? 120,
            $request->age4 ?? 180
        ];

        $credit_days = $model::where('ledger_group_id', $group)
            ->where('ledger_id', $ledger)
            ->value('credit_days') ?? 0;

        $doc_types = $type === 'debit'
            ? [ConstantHelper::RECEIPTS_SERVICE_ALIAS, 'Receipt']
            : [ConstantHelper::PAYMENTS_SERVICE_ALIAS, 'Payment'];

        $cus_type = $type === 'debit' ? 'customer' : 'vendor';

        $vouchers = Voucher::withWhereHas('items', function ($query) use ($ledger, $group, $type, $cost_center_ids) {
                $query->where('ledger_id', $ledger);
                $query->where('ledger_parent_id', $group);
                $query->where($type . '_amt_org', '>', 0);
                $query->when(!is_null($cost_center_ids), function ($q) use ($cost_center_ids) {
                    // $q->where('cost_center_id', $cost);
                    if (is_array($cost_center_ids)) {
                        $q->whereIn('cost_center_id', $cost_center_ids);
                    } else {
                        $q->where('cost_center_id', $cost_center_ids);
                    }
                });


            })
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->when(!is_null($loc), function ($query) use ($loc) {
                $query->where('location', $loc);
            })
            ->when(!is_null($org), function ($query) use ($org) {
                $query->whereIn('organization_id', $org);
            });

        if (!empty($start) && !empty($end)) {
            $vouchers->whereBetween('document_date', [$start, $end]);
        }

        $voucherIds = $vouchers
            ->orderBy('document_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->pluck('id')
            ->toArray();

        if ($voucherIds) {
            $data = self::get_overdue($type, $ages_all, $doc_types, $cus_type, $voucherIds, $credit_days, $group, $ledger, 1, $start, $end);
        } else {
            $data = [];
        }

        return [
            'data' => json_decode(json_encode($data)),
            'credit_days' => $credit_days
        ];
    }

    public function creditorsPendingPayment(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $userId = $user->auth_user_id;
        $organizationId = $user->organization_id;
        $organizations = [];
        $start = null;
        $end = null;
        $fyear = Helper::getFinancialYear(date('Y-m-d'));
        if ($request->date) {
            $dates = explode(' to ', $request->date);
            $start = date('Y-m-d', strtotime($dates[0]));
            $end = date('Y-m-d', strtotime($dates[1]));
        } else {
            if ($fyear) {
                $start = $fyear['start_date'];
                $today = Carbon::today();
                $end = Carbon::parse($fyear['end_date']);

                if ($end->greaterThan($today)) {
                    $end = $today;
                }
                $end = $end->format('Y-m-d');
            }
        }
        $group_name = Group::find($request->group)->name ?? ConstantHelper::PAYABLE;
        $vendors = [];
        $all_ledgers = [];
        $group = Helper::getGroupsQuery()->where('name', $group_name)->first();
        $drp_group = Helper::getGroupsQuery()->where('name', ConstantHelper::PAYABLE)->first();
        Log::info('Processing group', ['group' =>$group_name]);


        if ($group) {
            // Log group ID
            Log::info('Processing ledgers for group', ['group_id' => $group->id]);

            // Fetch child groups
            $ledger_groups = Helper::getGroupsQuery()
                ->where('parent_group_id', $group->id)
                ->pluck('id');
            $ledger_groups[] = $group->id;

            Log::info('Fetched child ledger groups', ['ledger_groups' => $ledger_groups]);

            // If no child groups, use the current group ID
            // if ($ledger_groups->isEmpty()) {
            //     $ledger_groups = collect([$group->id]);
            //     Log::info('No child groups found. Using current group as fallback', ['group_id' => $group->id]);
            // }
           

            // Fetch ledgers
            $all_ledgers = Ledger::where(function ($query) use ($ledger_groups) {
                $query->whereIn('ledger_group_id', $ledger_groups)
                    ->orWhere(function ($subQuery) use ($ledger_groups) {
                        foreach ($ledger_groups as $child) {
                            $subQuery->orWhereJsonContains('ledger_group_id', (string) $child)->orWhereJsonContains('ledger_group_id', $child);
                        }
                    });
            })->where('status', 1)->get();

            Log::info('Fetched ledgers for group(s)', [
                'ledger_group_ids' => $ledger_groups,
                'ledger_count' => $all_ledgers->count()
            ]);
        }
        $all_groups = Group::whereIn('id', $drp_group->getAllChildIds())->get();
        $date = $request->date;
        $date2 = $end ? \Carbon\Carbon::parse($end)->format('jS-F-Y') : \Carbon\Carbon::parse(date('Y-m-d'))->format('jS-F-Y');
        ;
        $type = 'credit';
        $books_t = Helper::getAccessibleServicesFromMenuAlias('vouchers')['services'];
        $user = Helper::getAuthenticatedUser();
       
        
        
        $mappings = Helper::access_org();
        
        
        
        $organizationId = $user->organization_id;
        $locations = InventoryHelper::getAccessibleLocations();
        $cost_centers = Helper::getActiveCostCenters();
        $cost_groups = CostGroup::with('costCenters')->where('status', 'active')->get()->toArray();
        return view('finance_report.creditors-pending-payment', compact('date', 'date2', 'type', 'all_ledgers', 'all_groups', 'books_t', 'organizationId', 'mappings', 'locations', 'cost_centers', 'cost_groups'));
    }
    public function debitorsPendingPayment(Request $request)
    {
        
        $start = null;
        $end = null;
        $fyear = Helper::getFinancialYear(date('Y-m-d'));
        if ($request->date) {
            $dates = explode(' to ', $request->date);
            $start = date('Y-m-d', strtotime($dates[0]));
            $end = date('Y-m-d', strtotime($dates[1]));
        } else {
            if ($fyear) {
                $start = $fyear['start_date'];
                $today = Carbon::today();
                $end = Carbon::parse($fyear['end_date']);

                if ($end->greaterThan($today)) {
                    $end = $today;
                }
                $end = $end->format('Y-m-d');
            }
        }
        $group_name = Group::find($request->group)->name ?? ConstantHelper::RECEIVABLE;

        $customers = [];
        $all_ledgers = [];
        $group = Helper::getGroupsQuery()->where('name', $group_name)->first();
        $drp_group = Helper::getGroupsQuery()->where('name', ConstantHelper::RECEIVABLE)->first();
        Log::info('Processing group', ['group' =>$group_name]);

        if ($group) {
            // Log group ID
            Log::info('Processing ledgers for group', ['group_id' => $group->id]);

            // Fetch child groups
            $ledger_groups = Helper::getGroupsQuery()
                ->where('parent_group_id', $group->id)
                ->pluck('id');
            $ledger_groups[] = $group->id;

            // Log::info('Fetched child ledger groups', ['ledger_groups' => $ledger_groups]);

            // // If no child groups, use the current group ID
            // if ($ledger_groups->isEmpty()) {
            //     $ledger_groups etI= collect([$group->id]);
            //     Log::info('No child groups found. Using current group as fallback', ['group_id' => $group->id]);
            // }

            // Fetch ledgers
            $all_ledgers = Ledger::where(function ($query) use ($ledger_groups) {
                $query->whereIn('ledger_group_id', $ledger_groups)
                    ->orWhere(function ($subQuery) use ($ledger_groups) {
                        foreach ($ledger_groups as $child) {
                            $subQuery->orWhereJsonContains('ledger_group_id', (string) $child)->orWhereJsonContains('ledger_group_id', $child);
                        }
                    });
            })->where('status', 1)->get();

            Log::info('Fetched ledgers for group(s)', [
                'ledger_group_ids' => $ledger_groups,
                'ledger_count' => $all_ledgers->count()
            ]);
        }
        $all_groups = Group::whereIn('id', $drp_group->getAllChildIds())->get();
        $date = $request->date;
        $date2 = $end ? \Carbon\Carbon::parse($end)->format('jS-F-Y') : \Carbon\Carbon::parse(date('Y-m-d'))->format('jS-F-Y');
        ;
        $type = 'debit';
        $books_t = Helper::getAccessibleServicesFromMenuAlias('vouchers')['services'];
        $user = Helper::getAuthenticatedUser();
        $user = Helper::getAuthenticatedUser();
        
        $mappings = Helper::access_org();
        $organizationId = $user->organization_id;
        $locations = InventoryHelper::getAccessibleLocations();
        $cost_centers = Helper::getActiveCostCenters();
        $cost_groups = CostGroup::with('costCenters')->where('status', 'active')->get()->toArray();
        return view('finance_report.debitors-pending-payment', compact('date', 'date2', 'type', 'all_ledgers', 'all_groups', 'books_t', 'organizationId', 'mappings', 'locations', 'cost_centers', 'cost_groups'));
    }

    public function getInvocies(Request $request)
    {
        $organizations = [];
        if ($request->organization_id) {
            $organizations = $request->organization_id;
        }
        else 
            $organizations = [Helper::getAuthenticatedUser()->organization_id];
        $cus_type = $request->type == ConstantHelper::RECEIPTS_SERVICE_ALIAS ? 'customer' : 'vendor';
        $ledger_account = $request->type == ConstantHelper::RECEIPTS_SERVICE_ALIAS ? ConstantHelper::RECEIVABLE : ConstantHelper::PAYABLE;
        $ledger_group = Helper::getGroupsQuery()->where('name', $ledger_account)->first();

        $group_id = $ledger_group->getAllChildIds();
        $group_id[] = $ledger_group->id;
        $accessibleLocations = InventoryHelper::getAccessibleLocations();
        $locationIds = $accessibleLocations->pluck('id')->toArray();
        $ledger_ids = Ledger::where(function ($query) use ($group_id, $request) {
                $query->where(function ($q) use ($group_id, $request) {
                    foreach ($group_id as $id) {
                        $q->orWhereJsonContains('ledger_group_id', (string) $id)->orWhereJsonContains('ledger_group_id', $id);
                    }

                    $q->orWhereIn('ledger_group_id', $group_id);
                });
                if ($request->filter_ledger) {
                    $query->where('id', $request->filter_ledger);
                }

                $query->where('status', 1);
            })
            ->get(['id', 'ledger_group_id']);


        $results = collect();
        $cost_center_ids = null;
        if (!empty($request->cost_center_id)) {
            $cost_center_ids = $request->cost_center_id ?? null;
            // dd($cost_center_ids);
        } elseif (!empty($request->cost_group_id)) {
            $cost_group = CostGroup::withDefaultGroupCompanyOrg()
                ->with('costCenters')
                ->where('id', $request->cost_group_id)
                ->where('status', 'active')
                ->first();

            $cost_center_ids = optional($cost_group->costCenters)->pluck('id')->unique()->all();
            // dd($cost_center_ids);
        }
        foreach ($ledger_ids as $ledger) {
            $ledgerGroupIds = is_array($ledger->ledger_group_id)
                ? $ledger->ledger_group_id
                : json_decode($ledger->ledger_group_id, true);
            $ledgerGroupIds = is_array($ledgerGroupIds)
                ? $ledgerGroupIds
                : [$ledger->ledger_group_id];
            $data = Voucher::when($request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS,function ($query){
                $query->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->withoutGlobalScope('defaultLocation');
            })
            ->when(!empty($organizations), function ($query) use ($organizations) {
                            $query->whereIn('organization_id', $organizations);
                        })->with([ 'ErpLocation' => function ($query) use ($request) {
        $query->when(function () use ($request) {
            return $request->type === ConstantHelper::PAYMENTS_SERVICE_ALIAS;
        }, function ($q) {
            $q->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)->withoutGlobalScope('defaultLocation');
        });
    }])
            ->with('organization')
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED);
            // ->when($request->type != ConstantHelper::PAYMENTS_SERVICE_ALIAS,function ($query) use($locationIds){
            //     $query->whereIn('location', $locationIds);
            // })
            $data->withWhereHas('items', function ($i) use ($ledger, $request, $ledgerGroupIds, $cost_center_ids) {
                    $i->where('ledger_id', $ledger->id)
                        ->whereIn('ledger_parent_id', $ledgerGroupIds);

                    if ($request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS) {
                        $i->where('credit_amt_org', '>', 0);
                    } else {
                        $i->where('debit_amt_org', '>', 0);
                    }
                    if ($cost_center_ids) {
                        if (is_array($cost_center_ids)) {
                            $i->whereIn('cost_center_id', $cost_center_ids);
                        } else {
                            $i->where('cost_center_id', $cost_center_ids);
                        }
                        // $i->where('cost_center_id', $request->cost_center_id);
                    }
                    if ($request->ledgerGroup) {
                        $i->whereHas('ledger_group', function ($lg) use ($request) {
                            $lg->where('id', $request->ledgerGroup);
                        });
                    }
                    $i->with([ 'ledger.vendor' => function ($query) use ($request) {
                        $query->when(function () use ($request) {
                            return $request->type === ConstantHelper::PAYMENTS_SERVICE_ALIAS;
                        }, function ($q) {
                            $q->withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
                            ;
                        });           }]);
                    $i->with([
                        'ledger.organization',
                        'ledger.customer',
                        'ledger_group',
                        'costCenter',
                    ]);
                })
                ->groupBy('id')
                ->orderBy('document_date', 'asc')
                ->orderBy('created_at', 'asc');

            if ($request->filled('date')) {
                // [$startDate, $endDate] = explode(' to ', $request->date);
                [$startDate, $endDate] = array_pad(explode(' to ', $request->date), 2, null);
                $start = Carbon::parse(trim($startDate))->format('Y-m-d');
                // $end = Carbon::parse(trim(isset($endDate) ? trim($endDate) : $startDate))->format('Y-m-d');
                $end = trim($endDate) ? Carbon::parse(trim($endDate))->format('Y-m-d') : $start;
                $data->whereBetween('document_date', [$start, $end]);
            }

            if ($request->book_code) {
                $data = $data->whereHas('series', function ($q) use ($request) {
                    $q->whereHas('org_service', function ($subQuery) use ($request) {
                        $subQuery->where('alias', $request->book_code);
                    });
                });
            }

            if ($request->document_no) {
                $data = $data->where('voucher_no', 'like', "%" . $request->document_no . "%");
            }

            $data = $data->with([
                'series' => function ($s) {
                    $s->select('id', 'book_code');
                }
            ])
                ->select('id', 'amount', 'book_id', 'document_date as date', 'created_at', 'voucher_name', 'voucher_no', 'location', 'organization_id')
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($voucher) use ($request, $ledger,$organizations)  {
                    $voucher->date = date('d/m/Y', strtotime($voucher->date));
                    $voucher->document_date = $voucher->document_date;

                    $balance = VoucherReference::where('voucher_id', $voucher->id)
                        ->withWhereHas('voucherPayRec', function ($query) use ($organizations,$request) {
                            $query->when($request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS,function ($q){
                            $q->withoutGlobalScope(DefaultGroupCompanyOrgScope::class);
                            $q->withoutGlobalScope('defaultLocation');
                        });
                            $query->when(!empty($organizations), function ($query) use ($organizations) {
                            $query->whereIn('organization_id', $organizations);
                        });
                            $query->whereNotIn('document_status', ConstantHelper::DOCUMENT_STATUS_REJECTED);
                        })->where('party_id', $ledger->id);

                    $amount = 0;
                    foreach ($voucher->items as $item) {
                        $amount += $request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS ? $item->credit_amt_org : $item->debit_amt_org;
                    }

                    $voucher->amount = $amount;
                    $balance = $balance->sum('amount');
                    $voucher->set = $balance;
                    $voucher->balance = $voucher->amount - $balance;

                    return $voucher;
                });

            $advanceSum = PaymentVoucherDetails::where('type', $cus_type)
                ->whereIn('reference', ['On Account'])
                ->withWhereHas('voucher', function ($query) use($organizations,$request) {
                $query->when($request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS,function ($q){
                $q->withoutGlobalScope(DefaultGroupCompanyOrgScope::class);
                $q->withoutGlobalScope('defaultLocation');
                 });
                  $query->when(!empty($organizations), function ($query) use ($organizations) {
                            $query->whereIn('organization_id', $organizations);
                        })->whereNotIn('document_status', ConstantHelper::DOCUMENT_STATUS_REJECTED);
                })
                ->with('partyName')->get()->filter(function ($adv) use ($ledger, $ledgerGroupIds) {
                    if (is_null($adv->ledger_id)) {
                        $ledgerGroupIds = is_array($ledgerGroupIds) ? $ledgerGroupIds : [$ledger->ledger_group_id];
                        return $adv->partyName && $adv->partyName->ledger_id == $ledger->id &&
                            in_array($adv->partyName->ledger_group_id, $ledgerGroupIds);
                        // $adv->partyName->ledger_group_id == $ledger->ledger_group_id;
                    } else {
                        return $adv->ledger_id == $ledger->id &&
                            in_array($adv->ledger_group_id, $ledgerGroupIds);
                        // $adv->ledger_group_id == $ledger->ledger_group_id;
                    }
                })->sum('orgAmount');

            foreach ($data as $v) {
                if ($advanceSum > 0 && isset($v->id)) {
                    $deductAmount = min($advanceSum, $v->balance);
                    $v->balance -= $deductAmount;
                    $advanceSum -= $deductAmount;
                } else {
                    break;
                }
            }

            $advanceItems = PaymentVoucherDetails::where('type', $cus_type)
                ->where('reference', 'Advance')
                ->withWhereHas('voucher', function ($query) use ($organizations,$request) {
                    $query->when($request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS,function ($q){
                $q->withoutGlobalScope(DefaultGroupCompanyOrgScope::class);
                $q->withoutGlobalScope('defaultLocation');
            });
                    $query->when(!empty($organizations), function ($query) use ($organizations) {
                            $query->whereIn('organization_id', $organizations);
                        })->whereNotIn('document_status', ConstantHelper::DOCUMENT_STATUS_REJECTED);
                })
                ->with('partyName')->get()->filter(function ($adv) use ($ledger, $ledgerGroupIds) {
                    if (is_null($adv->ledger_id)) {
                        $ledgerGroupIds = is_array($ledgerGroupIds) ? $ledgerGroupIds : [$ledger->ledger_group_id];
                        return $adv->partyName && $adv->partyName->ledger_id == $ledger->id &&
                            in_array($adv->partyName->ledger_group_id, $ledgerGroupIds);
                        //  $adv->partyName->ledger_group_id == $ledger->ledger_group_id;
                    } else {
                        return $adv->ledger_id == $ledger->id &&
                            in_array($adv->ledger_group_id, $ledgerGroupIds);
                        // $adv->ledger_group_id == $ledger->ledger_group_id;
                    }
                });

            foreach ($advanceItems as $advanceItem) {
                $remainingAdvanceAmount = $advanceItem->orgAmount;

                foreach ($data as $res) {
                    $combinedDateTime = \DateTime::createFromFormat(
                        'Y-m-d H:i:s',
                        $advanceItem->voucher->document_date . ' ' . date('H:i:s', strtotime($advanceItem->voucher->created_at))
                    );

                    $vendorDateTimestamp = $combinedDateTime?->getTimestamp();
                    $resTime = date('H:i:s', strtotime($res->created_at));
                    $parsedDate = \DateTime::createFromFormat('d/m/Y H:i:s', $res->date . ' ' . $resTime);
                    $resDateTimestamp = $parsedDate?->getTimestamp();

                    if ($vendorDateTimestamp < $resDateTimestamp) {
                        if ($remainingAdvanceAmount > 0) {
                            $deductAmount = min($remainingAdvanceAmount, $res->balance);
                            $res->balance -= $deductAmount;
                            $remainingAdvanceAmount -= $deductAmount;
                        }
                    }
                }
            }

            // Store or collect data per ledger
            if ($data->isNotEmpty()) {
                $results = $results->merge($data);
            }
        }


        return response()->json(['data' => $results]);
    }

    public function storeCrDrRowData(Request $request)
    {
        $payload = json_decode($request->getContent(), true);
        if ($payload) {
            if (!isset($payload['rows']) || !is_array($payload['rows'])) {
                return response()->json(['error' => 'Invalid rows data.'], 422);
            }


            $rows = $payload['rows'];
            $type = $payload['type'];



        } else {

            $rows = $request['rows'];
            $type = $request['type'];
        }
        // Flatten items
        $flattened = collect($rows)->flatMap(function ($voucher) {
            return collect($voucher['items'])->map(function ($item) use ($voucher) {
                return [
                    'voucher_id' => $voucher['id'],
                    'item_id' => $voucher['item_id'] ?? null,
                    'ledger_id' => $item['ledger_id'],
                    'ledger_code' => $item['ledger']['code'] ?? '-',
                    'ledger_name' => $item['ledger']['name'] ?? '-',
                    'ledger_group_name' => $item['ledger_group']['name'] ?? $item['ledger']['ledger_group']['name'] ?? '-',
                    'ledger_parent_id' => $item['ledger_parent_id'] ?? null,
                    'amount' => $item['amount'] ?? $voucher['amount'] ?? 0,
                    'settle_amt' => $voucher['settle_amt'] ?? 0,
                    'organization' => $voucher['organization']['name'] ?? '-',
                ];
            });
        });

        // Grouped by ledger_id
        $grouped = $flattened
            ->groupBy('ledger_id')
            ->map(function ($items, $ledgerId) {
                $first = $items->first();

                // Group by item_id within the current ledger_id group
                $itemGroups = $items->groupBy('item_id');

                // Sum of settle_amt per item_id, then total of all
                $settleAmtSumByItem = $itemGroups->map(function ($itemGroup) {
                    return $itemGroup->sum('settle_amt');
                });

                return [
                    'ledger_id' => $ledgerId,
                    'ledger_code' => $first['ledger_code'],
                    'ledger_name' => $first['ledger_name'],
                    'ledger_group_name' => $first['ledger_group_name'],
                    'ledger_parent_id' => $first['ledger_parent_id'],
                    'amount' => $settleAmtSumByItem->sum(), // ← Use summed settle_amt per item_id
                    'settle_amt' => $items->sum('settle_amt'),  // ← You can also keep this for reference
                    'voucher_id' => $first['voucher_id'],
                    'item_id' => $first['item_id'],
                    'items' => $items,
                    'organization' => $first['organization'],
                ];
            })->values();

        $raw = $flattened->map(function ($item) {
            return [
                'ledger_id' => $item['ledger_id'],
                'voucher_id' => $item['voucher_id'],
                'item_id' => $item['item_id'],
                'settle_amt' => $item['settle_amt'],
            ];
        });
      
        $token = 'selectedRows_' . uniqid();
        Cache::put($token, [
            'grouped' => $grouped,
            'raw' => $raw,
        ], 3600);

        $route = $type == ConstantHelper::RECEIPTS_SERVICE_ALIAS
            ? route('receipts.create', ['token' => $token])
            : route('payments.create', ['token' => $token]);

        return response()->json(['redirect' => $route]);
    }

    // IN PROGRESS  IMPORT  PENDING_PAYMENTS
    public function showImportForm($type)
    {
        return view('finance_report.pending-payment-import',compact('type'));
    }

    public function import(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls|max:30720',
            ]);
            if (!$request->hasFile('file')) {
                return response()->json([
                    'status' => false,
                    'message' => 'No file uploaded.',
                ], 400);
            }
            $file = $request->file('file');
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load(filename: $file);
            } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file format is incorrect or corrupted. Please upload a valid Excel file.',
                ], 400);
            }

            $sheet = $spreadsheet->getActiveSheet();
            $rowCount = $sheet->getHighestRow() - 1;
            if ($rowCount > 10000) {
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file contains more than 10000 items. Please upload a file with 10000 or fewer items.',
                ], 400);
            }
            if ($rowCount < 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'The uploaded file is empty.',
                ], 400);
            }
            $deleteQuery = UploadPendingPaymentMaster::where('user_id', $user->id)->where('doc_type',$request->type);
            $deleteQuery->delete();
            // dd($rowCount);

            $import = new CrDrReportImport($this->PendingPaymentImportExportService, $user, $request->type);
            Excel::import($import, $request->file('file'));

            $successfulItems = $import->getSuccessfulItems();
            $failedItems = $import->getFailedItems();
            $mailData = [
                'modelName' => 'Pending Payments',
                'successful_items' => $successfulItems,
                'failed_items' => $failedItems,
                'export_successful_url' => route('pending.payment.export.successful'),
                'export_failed_url' => route('pending.payment.export.failed'),
            ];
            if (count($failedItems) > 0) {
                $message = 'Items import failed.';
                $status = 'failure';
            } else {
                $message = 'Items imported successfully.';
                $status = 'success';
            }
            if ($user->email) {
                try {
                    Mail::to($user->email)->send(new ImportComplete($mailData));
                } catch (\Exception $e) {
                    $message .= " However, there was an error sending the email notification.";
                }
            }
            return response()->json([
                'status' => $status,
                'message' => $message,
                'successful_items' => $successfulItems,
                'failed_items' => $failedItems,
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid file format or file size. Please upload a valid .xlsx or .xls file with a maximum size of 30MB.',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to import items: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function exportSuccessfulItems()
    {
        $uploadItems = UploadPendingPaymentMaster::where('import_status', 'Success')
            ->get();
        // $items = Ledger::orderBy('id', 'desc')
        //     ->whereIn('code', $uploadItems->pluck('code'))->get();
        return Excel::download(new PendingPaymentExport($uploadItems, $this->PendingPaymentImportExportService), "successful-items.xlsx");
    }

    public function exportFailedItems()
    {
        $failedItems = UploadPendingPaymentMaster::where('import_status', 'Failed')
            ->get();
        return Excel::download(new FailedPendingPaymentExport($failedItems), "failed-items.xlsx");
    }

    public function updateCacheData(Request $request)
    {
       
        $payload = $request->all();
        // dd($payload);
        $token = $payload['token'] ?? null;
        $selectedVouchers = $payload['rows'] ?? [];

        if (!$token || !Cache::has($token)) {
            return response()->json(['error' => 'Invalid or missing token'], 404);
        }
        $delete = $payload['delete'] ?? false;
        $ledgerIdToDelete = $payload['ledger_id'] ?? null;
        $selectedToDelete = collect($selectedVouchers)->flatMap(function ($voucher) {
            return collect($voucher['items'])->map(function ($item) use ($voucher) {
                return [
                    'item_id' => $item['id'],
                    'ledger_id' => $item['ledger_id'],
                ];
            });
        });
        // dd(    $selectedVouchers );
        $cacheData = Cache::get($token);
        // 1. Update `raw` by item_id
        $existingRaw = collect($cacheData['raw']);
        $newRawItems = collect();
        $allSelectedItemIds = collect(); // To collect all valid selected item IDs

        foreach ($selectedVouchers as $voucher) {
            if (!isset($voucher['items']) || !is_array($voucher['items'])) {
                continue;
            }

            foreach ($voucher['items'] as $item) {
                $itemId = $item['id'];
                $allSelectedItemIds->push($itemId);

                $existing = $existingRaw->first(fn($rawItem) => $rawItem['item_id'] == $itemId);

                if ($existing) {
                    // Update if exists
                    $existing['settle_amt'] = $item['settle_amt'] ?? $voucher['settle_amt'] ?? 0;
                    $existingRaw = $existingRaw->map(function ($rawItem) use ($existing) {
                        return $rawItem['item_id'] == $existing['item_id'] ? $existing : $rawItem;
                    });
                } else {
                    // Add new if doesn't exist
                    $newRawItems->push([
                        'item_id' => $itemId,
                        'ledger_id' => $item['ledger_id'],
                        'settle_amt' => $item['settle_amt'] ?? $voucher['settle_amt'] ?? 0,
                        'voucher_id' => $voucher['id'],
                    ]);
                }
            }
        }

        // ❌ Remove any raw items not present in selectedVouchers
        if ($delete && $ledgerIdToDelete) {
            // Just remove all raw items where ledger_id matches
            $updatedRaw = $existingRaw->reject(fn($item) => $item['ledger_id'] == $ledgerIdToDelete)->values();
        } else {
            // Only keep items in payload, and update them
            $updatedRaw = collect($selectedVouchers)->flatMap(function ($voucher) {
                return collect($voucher['items'])->map(function ($item) use ($voucher) {
                    return [
                        'item_id' => $item['id'],
                        'ledger_id' => $item['ledger_id'],
                        'settle_amt' => $item['settle_amt'] ?? $voucher['settle_amt'] ?? 0,
                        'voucher_id' => $voucher['id'],
                    ];
                });
            })->values();
        }

        // ✅ Final merged raw data
        $updatedRaw = $existingRaw->merge($newRawItems)->values();
        // dd($updatedRaw);
        // 2. Update `grouped` by ledger_id and item_id
        $selectedVouchersCollection = collect($selectedVouchers);
        //    dd($selectedVouchersCollection);
        $allSelectedItems = $selectedVouchersCollection->flatMap(function ($voucher) {
            return collect($voucher['items'])->map(function ($item) use ($voucher) {
                return [
                    'voucher_id' => $voucher['id'],
                    'item_id' => $item['id'],
                    'ledger_id' => $item['ledger_id'],
                    'ledger_code' => $item['ledger']['code'] ?? '-',
                    'ledger_name' => $item['ledger']['name'] ?? '-',
                    'ledger_group_name' => $item['ledger_group']['name'] ?? $item['ledger']['ledger_group']['name'] ?? '-',
                    'ledger_parent_id' => $item['ledger_parent_id'] ?? null,
                    'amount' => $item['amount'] ?? $voucher['amount'] ?? 0,
                    'settle_amt' => $item['settle_amt'] ?? $voucher['settle_amt'] ?? 0,
                    'organization' => $item['ledger']['organization']['name'] ?? '-',
                ];
            });
        });
        // dd($allSelectedItems);
        $existingGrouped = collect($cacheData['grouped']);

        // Step 1: Remove unselected items from current grouped, and update matching ones
        $existingGrouped = $existingGrouped->map(function ($group) use ($selectedToDelete, $delete, $allSelectedItems) {
            $items = collect($group['items']);

            if ($delete) {
                // Remove only the selected items
                $items = $items->reject(function ($item) use ($selectedToDelete) {
                    return $selectedToDelete->contains(function ($del) use ($item) {
                        return $item['item_id'] == $del['item_id'] && $item['ledger_id'] == $del['ledger_id'];
                    });
                });
            } else {
                // ✅ Do not remove anything — just update matching items
                $items = $items->map(function ($item) use ($allSelectedItems) {
                    $match = $allSelectedItems->first(function ($sel) use ($item) {
                        return $sel['item_id'] == $item['item_id'] && $sel['ledger_id'] == $item['ledger_id'];
                    });
                    if ($match) {
                        $item['settle_amt'] = $match['settle_amt'];
                    }
                    return $item;
                });
            }

            if ($items->isEmpty())
                return null;

            return [
                ...$group,
                'items' => $items->values(),
                'amount' => $items->sum('settle_amt'),
                'settle_amt' => $items->sum('settle_amt'),
            ];
        })->filter();
        // remove null groups

        // Step 2: Add any new selected items not present in existing grouped
        $existingKeys = $existingGrouped->flatMap(function ($group) {
            return collect($group['items'])->map(fn($item) => $item['ledger_id'] . '_' . $item['item_id']);
        });

        $newItemsByLedger = $allSelectedItems->filter(function ($item) use ($existingKeys) {
            return !$existingKeys->contains($item['ledger_id'] . '_' . $item['item_id']);
        })->groupBy('ledger_id');

        $existingGrouped = $existingGrouped->map(function ($group) use ($newItemsByLedger) {
            $ledgerId = $group['ledger_id'];

            if ($newItemsByLedger->has($ledgerId)) {
                $newItems = $newItemsByLedger->get($ledgerId);
                $mergedItems = collect($group['items'])->merge($newItems)->values();

                return [
                    ...$group,
                    'items' => $mergedItems,
                    'amount' => $mergedItems->sum('settle_amt'),
                    'settle_amt' => $mergedItems->sum('settle_amt'),
                ];
            }

            return $group;
        });

        // Add new groups that don't exist in existingGrouped
        $newGroups = $newItemsByLedger->filter(function ($_, $ledgerId) use ($existingGrouped) {
            return !$existingGrouped->contains('ledger_id', $ledgerId);
        })->map(function ($items, $ledgerId) {
            $first = $items->first();

            return [
                'ledger_id' => $ledgerId,
                'ledger_code' => $first['ledger_code'] ?? '-',
                'ledger_name' => $first['ledger_name'] ?? '-',
                'ledger_group_name' => $first['ledger_group_name'] ?? '-',
                'ledger_parent_id' => $first['ledger_parent_id'],
                'amount' => $items->sum('settle_amt'),
                'settle_amt' => $items->sum('settle_amt'),
                'voucher_id' => $first['voucher_id'],
                'item_id' => $first['item_id'],
                'items' => $items,
                'organization' => $first['organization'] ?? '-',
            ];
        });

        // $updatedGrouped = $existingGrouped->merge($newGroups)->values();


        $updatedGrouped = $existingGrouped->merge($newGroups)->values();

        // dd($updatedGrouped,$existingGrouped);


        if (!$token) {
            return response()->json(['error' => 'Missing token'], 400);
        }

        Cache::put($token, [
            'grouped' => $updatedGrouped,
            'raw' => $updatedRaw,
        ], 3600);

        return response()->json(['status' => 'Cache updated']);
    }
    public function importingProgress(Request $request, $type)
{
    if (!($type == "payments" || $type == "receipts")) {
        if ($request->ajax()) {
            return response()->json(['error' => 'Invalid type'], 422);
        }
        return back();
    }

    $user = Helper::getAuthenticatedUser()->id;
           if ($request->type == ConstantHelper::PAYMENTS_SERVICE_ALIAS) {
                $orgs = Helper::getAuthenticatedUser()->access_rights_org->pluck('organization_id');
                $orgs = $orgs->isEmpty() ? [Helper::getAuthenticatedUser()->organization_id] : $orgs->toArray();
            } else {
                $orgs = [Helper::getAuthenticatedUser()->organization_id];
            }
    $data = UploadPendingPaymentMaster::where('user_id', $user)
        ->where('import_status', 'Success')
        ->where('doc_type', $type)
        ->get()->toArray();
    $grouped = collect($data)->groupBy('ledger_id');
    $data = $grouped->toArray();

    $validationErrors = [];
    $reportedLedgers = [];
    $group_id = $user->organization->group_id;
    

    $flattened = collect($data)->flatMap(function ($voucher) use ($orgs,$type, &$validationErrors, &$reportedLedgers,$group_id) {
        return collect($voucher)->map(function ($item) use ($type, &$validationErrors, &$reportedLedgers,$orgs,$group_id) {
    $ledger = Ledger::withoutGlobalScope(DefaultGroupCompanyOrgScope::class)
    ->where('group_id',$group_id)
    ->with([ 'vendor' => function ($query) use ($orgs,$type) {
        $query->when(function () use ($type) {
            return $type === ConstantHelper::PAYMENTS_SERVICE_ALIAS;
        }, function ($q) {
            $q->withoutGlobalScope(DefaultGroupCompanyOrgScope::class);
            $q->withoutGlobalScope('defaultLocation');
        })->whereIn('organization_id', $orgs);
    }])->with('customer')->find($item['ledger_id']);
            $group = Group::find($item['ledger_group_id']);
            $relation = $type == ConstantHelper::RECEIPTS_SERVICE_ALIAS ? 'customer' : 'vendor';
            $ledgerName = $ledger ? $ledger->name : ($item['ledger_name'] ?? 'Unknown Ledger');

            // Relation missing
            if (!$ledger || !$ledger->{$relation}) {
                if (!in_array($ledgerName, $reportedLedgers)) {
                    $validationErrors[] = "{$ledgerName}'s {$relation} is missing";
                    $reportedLedgers[] = $ledgerName;
                }
            }

            // Credit days check
            $creditDays = $ledger->{$relation}->credit_days ?? null;
            if ($creditDays === null || $creditDays === '' || $creditDays == 0) {
                if (!in_array($ledgerName, $reportedLedgers)) {
                    $validationErrors[] = "{$ledgerName}'s {$relation} has no credit days set";
                    $reportedLedgers[] = $ledgerName;
                }
            }

            $org = Voucher::find($item['voucher_id'])?->organization?->name;

            return [
                'voucher_id' => $item['voucher_id'],
                'item_id' => null,
                'ledger_id' => $ledger->id,
                'ledger_code' => $ledger->code ?? '-',
                'ledger_name' => $ledger->name ?? '-',
                'ledger_group_name' => $group->name ?? '-',
                'ledger_parent_id' => $group->id ?? null,
                'amount' => null,
                'settle_amt' => $item['settle_amount'] ?? 0,
                'organization' => $org ?? '-',
            ];
        });
    });

    // if (!empty($validationErrors)) {
    //     if ($request->ajax()) {
    //         // return JSON error with 422 status
    //         return response()->json(['errors' => $validationErrors], 422);
    //     }
    //     return back()->withErrors($validationErrors)->withInput();
    // }


    $grouped = $flattened
        ->groupBy('ledger_id')
        ->map(function ($items, $ledgerId) {
            $first = $items->first();
            $itemGroups = $items->groupBy('item_id');
            $settleAmtSumByItem = $itemGroups->map(function ($itemGroup) {
                return $itemGroup->sum('settle_amt');
            });
            return [
                'ledger_id' => $ledgerId,
                'ledger_code' => $first['ledger_code'],
                'ledger_name' => $first['ledger_name'],
                'ledger_group_name' => $first['ledger_group_name'],
                'ledger_parent_id' => $first['ledger_parent_id'],
                'amount' => $settleAmtSumByItem->sum(),
                'settle_amt' => $items->sum('settle_amt'),
                'voucher_id' => $first['voucher_id'],
                'item_id' => $first['item_id'],
                'items' => $items,
                'organization' => $first['organization'],
            ];
        })->values();

    $raw = $flattened->map(function ($item) {
        return [
            'ledger_id' => $item['ledger_id'],
            'voucher_id' => $item['voucher_id'],
            'item_id' => $item['item_id'],
            'settle_amt' => $item['settle_amt'],
        ];
    });
    $token = 'selectedRows_' . uniqid();
    Cache::put($token, [
        'grouped' => $grouped,
        'raw' => $raw,
    ], 3600);

    $route = $type == ConstantHelper::RECEIPTS_SERVICE_ALIAS
        ? route('receipts.create', ['token' => $token])
        : route('payments.create', ['token' => $token]);

    if ($request->ajax()) {
        return response()->json(['redirect' => $route]);
    }
    return redirect($route);
}

    

}
