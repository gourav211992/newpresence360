<?php

namespace App\Http\Controllers\CRM;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use Auth;
use App\Http\Controllers\Controller;
use App\Models\CRM\ErpDiary;
use App\Models\CRM\LmsLead;
use App\Models\CRM\LmsRating;
use App\Models\CRM\ErpSaleOrderSummary;
use App\Lib\Validation\ErpDiary as Validator;
use App\Models\CRM\ErpCustomerTarget;
use App\Models\CRM\ErpMeetingStatus;
use App\Models\CRM\ErpOrderSummary;
use App\Models\Employee;
use App\Models\ErpCustomer;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $customers = ErpCustomer::where('organization_id', $user->organization_id)
                    ->orderBy('company_name','ASC')
                    ->get();
        $notes = $this->getNotesSummary($request);
        $teamMembers = $this->getTeamMembers($user);
        $sales_summary_data = $this->getSalesSummary($request);
        $orders_data = $this->getOrderData($request);
        $leads_data = $this->getLeadData($request);
        $top_customers_data = $this->getTopCustomersData($request);
        $diariesData = $this->getErpDiaries($user,$request);
        $salesData = $this->getSalesData($request);
        $salePersons = Employee::where('organization_id', $user->organization_id)->get();

        return view('crm.index', [
            'notes' => $notes,
            'teamMembers' => $teamMembers,
            'customers' => $customers,
            'sales_summary_data' => $sales_summary_data,
            'orders_data'=> $orders_data,
            'leads_data' => $leads_data,
            'top_customers_data' => $top_customers_data,
            'diariesData' => $diariesData,
            'salesData' => $salesData,
            'salePersons' => $salePersons,
        ] );
    }

    function getErpDiaries($user,$request){
        $erpDiaries = ErpDiary::with('customer')
            ->filter($request)
            ->where('organization_id', $user->organization_id)
            ->where('created_by',$user->id)
            ->orderBy('id','desc')
            ->limit(2)
            ->get();

        $totalExistingDiaries = ErpDiary::filter($request)
            ->where('organization_id', $user->organization_id)
            ->where('created_by',$user->id)
            ->where('customer_type','Existing')
            ->count();

        $totalNewDiaries = ErpDiary::filter($request)
            ->where('organization_id', $user->organization_id)
            ->where('created_by',$user->id)
            ->where('customer_type','New')
            ->count();

        $meetinStatus = ErpMeetingStatus::where('status',ConstantHelper::ACTIVE)->get();
        $erpDiariesStatusCount = [];
        $chartData= [];
        $total = 0;
        foreach($meetinStatus as $status){
            $erpDiariesStatusCount[$status->id] = ErpDiary::filter($request)
            ->where('organization_id', $user->organization_id)
            ->where('created_by',$user->id)
            ->where('meeting_status_id',$status->id)
            ->count();

            $chartData['status'][$status->id] = $status->title;
            $chartData['count'][$status->id] = $erpDiariesStatusCount[$status->id];
            $chartData['colors'][$status->id] = $status->color_code;
            $total = $total + $chartData['count'][$status->id];

        }

       
        return [
            'erpDiariesStatusCount' => $erpDiariesStatusCount,
            'erpDiaries' => $erpDiaries,
            'totalExistingDiaries' => $totalExistingDiaries,
            'totalNewDiaries' => $totalNewDiaries,
            'chartData' => $chartData,
            'meetinStatus' => $meetinStatus,
            'total' => $total,
        ]; 

    }

    function getNotesSummary($date_filter)
    {
        $user = Helper::getAuthenticatedUser();

        function applyNDateFilter($query, $request)
        {
            if ($request['date_filter'] == 'today') {
                $query->whereDate('created_at', Carbon::today());
            }
            if ($request['date_filter'] == 'this_week') {
                $startOfCurrentWeek = Carbon::now()->startOfWeek();
                $endOfCurrentWeek = Carbon::now()->endOfWeek();
                $query->whereBetween('created_at', [$startOfCurrentWeek, $endOfCurrentWeek]);
            }
            if ($request['date_filter'] == 'this_month') {
                $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
            }
        }

        if (is_object($date_filter) && (isset($date_filter->all()['fp_range']) || isset($date_filter->all()['customer_id']) || isset($date_filter->all()['sales_person_id']))) {
            $from_date = null;
            $to_date = null;
            $duration = $date_filter->fp_range;
            if ($duration != null) {
                $duration = explode(' to ', $duration);
                if(count($duration)== 1)
                {
                    $from_date = Carbon::parse($duration[0]);
                }
                else{
                    $from_date = Carbon::parse($duration[0]);
                    $to_date = Carbon::parse($duration[1]);
                }
            }

            $erpData = ErpDiary::where('organization_id', $user->organization_id);
            if ($from_date && $to_date) {
                $erpData->whereBetween(DB::raw('DATE(created_at)'), [$from_date, $to_date]);
            }
            else if ($from_date) {
                $erpData->where(DB::raw('DATE(created_at)'), [$from_date]);
            }
            else if ($to_date) {
                $erpData->where(DB::raw('DATE(created_at)'), [$to_date]);
            }
            $erpData->when($date_filter->all()['customer_id'], function ($query) use ($date_filter) {
                return $query->where('customer_id', $date_filter->all()['customer_id']);
            });

            $erpData->when($date_filter->all()['sales_person_id'], function ($query) use ($date_filter) {
                return $query->whereHas('customer', function($q) use($date_filter){
                    $q->where('sales_person_id',$date_filter->all()['sales_person_id']);
                });
            });

            $erpData = $erpData->orderBy('id', 'desc')->paginate(3);
            return $erpData;
        }
        else {

            $erpData = ErpDiary::where('organization_id', $user->organization_id)
                ->where(function ($query) use ($date_filter) {
                    if ($date_filter) {
                        applyNDateFilter($query, $date_filter);
                    }
                })->orderBy('id', 'desc')->paginate(3);
                return $erpData;
        }

    }

    function getSalesData($request)
    {
        $user = Helper::getAuthenticatedUser();
        
        $totalSales= ErpSaleOrderSummary::where('organization_id', $user->organization_id)
        ->where(function ($query) use($request) {
            if ($request) {
                $this->applyFilter($query, $request, 'sales');
            }

            if ($request->sales_person_id) {
                $query->whereHas('customer', function($q) use($request){
                    $q->where('sales_person_id',$request->sales_person_id);
                });
            }
        })
        ->sum('total_sale_value');
        $totalSales = Helper::formatNumber($totalSales);

        $totalTarget = ErpCustomerTarget::where('organization_id', $user->organization_id)
        ->where(function ($query) use($request) {
            if ($request) {
                $this->applyFilter($query, $request, 'default');
            }

            if ($request->sales_person_id) {
                $query->whereHas('customer', function($q) use($request){
                    $q->where('sales_person_id',$request->sales_person_id);
                });
            }
            
        })->sum('total_target');

        $endOfThisMonth = Carbon::now()->endOfMonth();
        $currentMonth = Carbon::now()->month;
        $currentYears = Carbon::now()->year;

        if ($currentMonth < 4) {
            $financialYearStart = $currentYears - 1;
            $financialYearEnd = $currentYears;
        } else {
            $financialYearStart = $currentYears;
            $financialYearEnd = $currentYears + 1;
        }

        $currentYear = "{$financialYearStart}-{$financialYearEnd}";

        $labels = [];
        $saleOrderData = [];
        $targetData = [];

        foreach (Carbon::now()->subMonths(5)->monthsUntil($endOfThisMonth) as $month) {
            $labels[] = $month->format('M');
            
            $orderquery = ErpSaleOrderSummary::where('organization_id', $user->organization_id);
                if ($request->customer_id) {
                    $orderquery->where('customer_id', $request->customer_id);
                }

                if ($request->sales_person_id) {
                    $orderquery->whereHas('customer', function($q) use($request){
                        $q->where('sales_person_id',$request->sales_person_id);
                    });
                }

                $saleOrderData[] = $orderquery->whereYear('date', $month->year) 
                    ->whereMonth('date', $month->month)
                    ->sum('total_sale_value');

            $targetquery = ErpCustomerTarget::where('organization_id', $user->organization_id);

                if ($request->customer_id) {
                    $targetquery->where('customer_id', $request->customer_id);
                }

                if ($request->sales_person_id) {
                    $orderquery->whereHas('customer', function($q) use($request){
                        $q->where('sales_person_id',$request->sales_person_id);
                    });
                }

            $targetData[] = $targetquery->where('year', $currentYear)
                ->sum(strtolower($month->format('M')));      
        }

        $data = [
            'totalSales'=> $totalSales,
            'totalTarget'=> $totalTarget,
            'labels'=> $labels,
            'saleOrderData'=> $saleOrderData,
            'targetData'=> $targetData,
        ];
        return (Object) $data;
    }

    function applyFilter($query,$request, $type){
        $var = 'date';
        if($type == 'default')
        {
            $var = 'created_at';
        }

        if ($request->date_filter == 'today') {
            $query->whereDate($var,date('Y-m-d'));
        }
        
        if ($request->date_filter == 'this_week') {
            $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
            $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
            $query->whereBetween($var, [$startOfWeek, $endOfWeek]);
        }

        if ($request->date_filter == 'this_month') {
            $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
            $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
            $query->whereBetween($var, [$startOfMonth, $endOfMonth]);
        }

        if ($request->fp_range) {
            $duration = explode(' to ', $request->fp_range);
            $from_date = Carbon::parse($duration[0]);
            $to_date = isset($duration[1]) ? Carbon::parse($duration[1]) : Carbon::parse($duration[0]);

            $query->whereDate($var, '<=', $to_date)
            ->whereDate($var, '>=', $from_date);
        }

        if ($request->customer_id) {
            $erpCustomer = ErpCustomer::find($request->customer_id);
            $query->where('customer_code', @$erpCustomer->customer_code);
        }

        return $query;
    }

    function getSalesSummary($date_filter)
    {
        $user = Helper::getAuthenticatedUser();
        
        $customerId = @$date_filter->all()['customer_id'];
        $erpCustomer = ErpCustomer::find($customerId);
        
        function applyDateFilter($query, $request, $type) {

            $var = 'date';
            if($type == 'default')
            {
                $var = 'created_at';
            }

            if ($request['date_filter'] == null) {
                $query->whereDate($var, Carbon::today());
            }
            if ($request['date_filter'] == 'today') {
                $query->whereDate($var, Carbon::today());
            }
            if ($request['date_filter'] == 'this_week') {
                $startOfCurrentWeek = Carbon::now()->startOfWeek();
                $endOfCurrentWeek = Carbon::now()->endOfWeek();
                $query->whereBetween($var, [$startOfCurrentWeek, $endOfCurrentWeek]);
            }
            if ($request['date_filter'] == 'this_month') {
                $query->whereMonth($var, Carbon::now()->month)
                      ->whereYear($var, Carbon::now()->year);
            }
        }
        $today = Carbon::now()->format('d-m-Y');
        if(is_object($date_filter) && (isset($date_filter->all()['fp_range'])||isset($date_filter->all()['customer_id'])||isset($date_filter->all()['sales_person_id'])))
        {
            $from_date = null;
            $to_date = null;
            $duration = $date_filter->fp_range;
            if($duration != null)
            {
                $duration = explode(' to ', $duration);
                if(count($duration)== 1)
                {
                    $from_date = Carbon::parse($duration[0]);
                }
                else{
                    $from_date = Carbon::parse($duration[0]);
                    $to_date = Carbon::parse($duration[1]);
                }
            }

            $totalSalesdata = ErpSaleOrderSummary::where('organization_id', $user->organization_id);
            if ($from_date && $to_date) {
                $totalSalesdata->whereBetween(DB::raw('DATE(created_at)'), [$from_date, $to_date]);
            }
            else if ($from_date) {
                $totalSalesdata->where(DB::raw('DATE(created_at)'), [$from_date]);
            }
            else if ($to_date) {
                $totalSalesdata->where(DB::raw('DATE(created_at)'), [$to_date]);
            }
            $totalSalesdata->when($date_filter->all()['customer_id'], function ($query) use ($date_filter,$erpCustomer) {
                return $query->where('customer_code', @$erpCustomer->customer_code);
            });

            $totalSalesdata->when($date_filter->all()['sales_person_id'], function ($query) use ($date_filter) {
                return $query->whereHas('customer', function($q) use($date_filter){
                    $q->where('sales_person_id',$date_filter->all()['sales_person_id']);
                });
            });

            $totalSalesCount = $totalSalesdata->sum('total_sale_value');
            $totalSalesCount = Helper::formatNumber($totalSalesCount);

            // $orderValuesData = ErpOrderSummary::where('organization_id', $user->organization_id);

            // if ($from_date && $to_date) {
            //     $orderValuesData->whereBetween(DB::raw('DATE(created_at)'), [$from_date, $to_date]);
            // }
            // else if ($from_date) {
            //     $orderValuesData->where(DB::raw('DATE(created_at)'), [$from_date]);
            // }
            // else if ($to_date) {
            //     $orderValuesData->where(DB::raw('DATE(created_at)'), [$to_date]);
            // }

            // $orderValuesData->when($date_filter->all()['customer_id'], function ($query) use ($date_filter,$erpCustomer) {
            //     return $query->where('customer_code', @$erpCustomer->customer_code);
            // });

            // $orderValuesData->when($date_filter->all()['sales_person_id'], function ($query) use ($date_filter) {
            //     return $query->whereHas('customer', function($q) use($date_filter){
            //         $q->where('sales_person_id',$date_filter->all()['sales_person_id']);
            //     });
            // });

            // $orderValuesCount = $orderValuesData->sum('total_order_value');
            // $orderValuesCount = Helper::formatNumber($orderValuesCount);

            $erpCustomersData = ErpCustomer::where('organization_id', $user->organization_id);

            if ($from_date && $to_date) {
                $erpCustomersData->whereBetween(DB::raw('DATE(created_at)'), [$from_date, $to_date]);
            }
            else if ($from_date) {
                $erpCustomersData->where(DB::raw('DATE(created_at)'), [$from_date]);
            }
            else if ($to_date) {
                $erpCustomersData->where(DB::raw('DATE(created_at)'), [$to_date]);
            }
            $erpCustomersData->when($date_filter->all()['customer_id'], function ($query) use ($date_filter,$erpCustomer) {
                return $query->where('id', @$erpCustomer);
            });

            $erpCustomersData->when($date_filter->all()['sales_person_id'], function ($query) use ($date_filter) {
                return $query->where('sales_person_id', $date_filter->all()['sales_person_id']);
            });

            $erpCustomersCount = $erpCustomersData->count();

            $erpLeadData = LmsLead::where('organization_id', $user->organization_id);

            if ($from_date && $to_date) {
                $erpLeadData->whereBetween(DB::raw('DATE(created_at)'), [$from_date, $to_date]);
            }
            else if ($from_date) {
                $erpLeadData->where(DB::raw('DATE(created_at)'), [$from_date]);
            }
            else if ($to_date) {
                $erpLeadData->where(DB::raw('DATE(created_at)'), [$to_date]);
            }
            $erpLead = $erpLeadData->count();
        }
        else{
            $totalSalesCount= ErpSaleOrderSummary::where('organization_id', $user->organization_id)
            ->where(function ($query) use($date_filter) {
                if ($date_filter) {
                    applyDateFilter($query, $date_filter, 'sales');
                }
            })
            ->sum('total_sale_value');
            $totalSalesCount = Helper::formatNumber($totalSalesCount);
            // $orderValuesCount = ErpOrderSummary::where('organization_id', $user->organization_id)
            // ->where(function ($query) use($date_filter) {
            //     if ($date_filter) {
            //         $this->applyFilter($query, $date_filter, 'order');
            //     }
            // })
            // ->sum('total_order_value');
            // $orderValuesCount = Helper::formatNumber($orderValuesCount);
            $erpCustomersCount = ErpCustomer::where('organization_id', $user->organization_id)
            ->where(function ($query) use($date_filter) {
                if ($date_filter) {
                    applyDateFilter($query, $date_filter, 'default');
                }
            })
            ->count();
            $erpLead = LmsLead::where('organization_id', $user->organization_id)
            ->where(function ($query) use($date_filter) {
                if ($date_filter) {
                    applyDateFilter($query, $date_filter, 'default');
                }
            })->count();
        }

        $orderValuesCount = ErpOrderSummary::where('organization_id', $user->organization_id)
            ->where(function ($query) use($date_filter) {
                if ($date_filter) {
                    $this->applyFilter($query, $date_filter, 'order');
                }
            })
            ->sum('total_order_value');
            $orderValuesCount = Helper::formatNumber($orderValuesCount);

        $data = [
            'today'=> $today,
            'totalSalesCount'=> $totalSalesCount,
            'orderValuesCount'=> $orderValuesCount,
            'erpCustomersCount'=> $erpCustomersCount,
            'erpLead'=> $erpLead
        ];
        return (Object) $data;
    }

    // function getAccountStatement($date_filter)
    // {
    //     $user = Helper::getAuthenticatedUser();
    //     // $totalSalesCount= ErpCustomer::where('organization_id', $user->organization_id)->count();
    //     $totalSalesCount = 250;
    //     // $orderValuesCount = ErpCustomer::where('organization_id', $user->organization_id)->count();
    //     $orderValuesCount = 100;
    //     $erpCustomersCount = ErpCustomer::where('organization_id', $user->organization_id)->count();
    //     // $erpLead = ErpCustomer::where('organization_id', $user->organization_id)->count();
    //     $erpLead = 25;
    //     $data = [
    //         'totalSalesCount'=> $totalSalesCount,
    //         'orderValuesCount'=> $orderValuesCount,
    //         'erpCustomersCount'=> $erpCustomersCount,
    //         'erpLead'=> $erpLead
    //     ];

    //     return $data;
    // }

    // function getSalesData($request)
    // {
    //     $user = Helper::getAuthenticatedUser();
    //     // $totalSalesCount= ErpCustomer::where('organization_id', $user->organization_id)->count();
    //     $totalSalesCount = 250;
    //     // $orderValuesCount = ErpCustomer::where('organization_id', $user->organization_id)->count();
    //     $orderValuesCount = 100;
    //     $erpCustomersCount = ErpCustomer::where('organization_id', $user->organization_id)->count();
    //     // $erpLead = ErpCustomer::where('organization_id', $user->organization_id)->count();
    //     $erpLead = 25;
    //     $data = [
    //         'totalSalesCount'=> $totalSalesCount,
    //         'orderValuesCount'=> $orderValuesCount,
    //         'erpCustomersCount'=> $erpCustomersCount,
    //         'erpLead'=> $erpLead
    //     ];

    //     return $data;
    // }

    function getOrderData($date_filter)
    {
        $user = Helper::getAuthenticatedUser();
        function applyODateFilter($query, $request)
        {
            if ($request['date_filter'] == 'today') {
                $query->whereDate('date', Carbon::today());
            }
            if ($request['date_filter'] == 'this_week') {
                $startOfCurrentWeek = Carbon::now()->startOfWeek();
                $endOfCurrentWeek = Carbon::now()->endOfWeek();
                $query->whereBetween('date', [$startOfCurrentWeek, $endOfCurrentWeek]);
            }
            if ($request['date_filter'] == 'this_month') {
                $query->whereMonth('date', Carbon::now()->month)
                    ->whereYear('date', Carbon::now()->year);
            }
        }
        $erpOrders = ErpOrderSummary::where('organization_id', $user->organization_id)
            ->where(function ($query) use ($date_filter) {
                if ($date_filter) {
                    applyODateFilter($query, $date_filter);
                }
                if($date_filter['sales_person_id']){
                    $query->whereHas('customer',function($q) use($date_filter){
                        $q->where('sales_person_id',$date_filter['sales_person_id']);
                    });
                }
            })->get();
            

        $labels = [];
        $data = [];

        if ($date_filter['date_filter'] == 'today') {
            $labels[] = Carbon::today()->format('d-m-y');
            $data[] = round($erpOrders->sum('total_order_value'), 2);
        } elseif ($date_filter['date_filter'] == 'this_week') {
            $startOfCurrentWeek = Carbon::now()->startOfWeek();
            $endOfCurrentWeek = Carbon::now()->endOfWeek();

            $currentWeekOrders = $erpOrders->groupBy(function ($order) {
                return Carbon::parse($order->date)->format('Y-m-d');
            });
            for ($date = $startOfCurrentWeek; $date->lte($endOfCurrentWeek); $date->addDay()) {
                $labels[] = $date->format('D');
                $data[] = round($currentWeekOrders->get($date->toDateString(), collect())->sum('total_order_value'), 2);
            }
        } elseif ($date_filter['date_filter'] == 'this_month') {
            $currentMonthOrders = $erpOrders->groupBy(function ($order) {
                return Carbon::parse($order->date)->weekOfMonth;
            });
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();
            $weeksInCurrentMonth = $startOfMonth->diffInWeeks($endOfMonth) + 1;

            foreach (range(1, $weeksInCurrentMonth) as $week) {
                $labels[] = "Week $week";
                $data[] = round($currentMonthOrders->get($week, collect())->sum('total_order_value'), 2);
            }
        } else {
            $endOfThisMonth = Carbon::now()->endOfMonth();

            $sixMonthsOrders = $erpOrders->groupBy(function ($order) {
                return Carbon::parse($order->date)->format('Y-m');
            });

            foreach (Carbon::now()->subMonths(5)->monthsUntil($endOfThisMonth) as $month) {
                $labels[] = $month->format('M');
                $data[] = round($sixMonthsOrders->get($month->format('Y-m'), collect())->sum('total_order_value'), 2);
            }
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    function getLeadData($date_filter)
    {
        $user = Helper::getAuthenticatedUser();
        $ratings = LmsRating::where('organization_id', $user->organization_id)
        ->where('status', 'active')
        ->distinct()
        ->pluck('id');

        $lmsData = LmsLead::selectRaw('lms_ratings.name as lead_rating_name, lms_ratings.color_code, COUNT(lms_leads.id) as lead_count')
        ->join('lms_ratings', 'lms_leads.lead_rating_id', '=', 'lms_ratings.id')
        ->whereIn('lead_rating_id', $ratings)
        ->where('lms_leads.status', 'active')
        ->groupBy('lms_ratings.name', 'lms_ratings.color_code')
        ->get();

        function applyLDateFilter($query, $request) {
            if ($request['date_filter'] == null) {
                $query->whereDate('lms_leads.created_at', Carbon::today());
            }
            if ($request['date_filter'] == 'today') {
                $query->whereDate('lms_leads.created_at', Carbon::today());
            }
            if ($request['date_filter'] == 'this_week') {
                $startOfCurrentWeek = Carbon::now()->startOfWeek();
                $endOfCurrentWeek = Carbon::now()->endOfWeek();
                $query->whereBetween('lms_leads.created_at', [$startOfCurrentWeek, $endOfCurrentWeek]);
            }
            if ($request['date_filter'] == 'this_month') {
                $query->whereMonth('lms_leads.created_at', Carbon::now()->month)
                      ->whereYear('lms_leads.created_at', Carbon::now()->year);
            }
        }
        if(is_object($date_filter) && (isset($date_filter->all()['fp_range'])||isset($date_filter->all()['customer_code'])))
        {
            $from_date = null;
            $to_date = null;
            $duration = $date_filter->fp_range;
            if($duration != null)
            {
                $duration = explode(' to ', $duration);
                if(count($duration)== 1)
                {
                    $from_date = Carbon::parse($duration[0]);
                }
                else{
                    $from_date = Carbon::parse($duration[0]);
                    $to_date = Carbon::parse($duration[1]);
                }
            }

            $lmsData = LmsLead::where('lms_leads.organization_id', $user->organization_id);
            if ($from_date && $to_date) {
                $lmsData->whereBetween(DB::raw('DATE(lms_leads.created_at)'), [$from_date, $to_date]);
            }
            else if ($from_date) {
                $lmsData->where(DB::raw('DATE(lms_leads.created_at)'), [$from_date]);
            }
            else if ($to_date) {
                $lmsData->where(DB::raw('DATE(lms_leads.created_at)'), [$to_date]);
            }

            $lmsData = $lmsData->selectRaw('lms_ratings.name as lead_rating_name, lms_ratings.color_code, COUNT(lms_leads.id) as lead_count')
            ->join('lms_ratings', 'lms_leads.lead_rating_id', '=', 'lms_ratings.id')
            ->whereIn('lead_rating_id', $ratings)
            ->where('lms_leads.status', 'active')
            ->groupBy('lms_ratings.name', 'lms_ratings.color_code')
            ->get();
        }
        else{
            $lmsData = LmsLead::where(function ($query) use ($date_filter) {
                if ($date_filter) {
                    applyLDateFilter($query, $date_filter);
                }
            })
            ->selectRaw('lms_ratings.name as lead_rating_name, lms_ratings.color_code, COUNT(lms_leads.id) as lead_count')
            ->join('lms_ratings', 'lms_leads.lead_rating_id', '=', 'lms_ratings.id')
            ->whereIn('lms_leads.lead_rating_id', $ratings)
            ->where('lms_leads.status', 'active')
            ->groupBy('lms_ratings.name', 'lms_ratings.color_code')
            ->get();
        }

        $result = $lmsData->map(function ($item) {
            return [
                'lead_rating_id' => $item->lead_rating_name,
                'lead_count' => $item->lead_count,
                'color_code' => $item->color_code,
            ];
        });

        $resultArray = $result->toArray();
        return $resultArray;
    }

    function getTopCustomersData($request)
    {
        $user = Helper::getAuthenticatedUser();
        $user_count = 5; // count for top customers for whom data to be fetched
        $currentMonthString = strtolower(Carbon::now()->format('M'));
        $currentMonth = Carbon::now()->month;
        $currentYears = Carbon::now()->year;
        $erpCustomer = ErpCustomer::find($request->customer_id);
        // dd($erpCustomer->customer_code);

        if ($currentMonth < 4) {
            $financialYearStart = $currentYears - 1;
            $financialYearEnd = $currentYears;
        } else {
            $financialYearStart = $currentYears;
            $financialYearEnd = $currentYears + 1;
        }

        $currentYear = "{$financialYearStart}-{$financialYearEnd}";

        $topSales = ErpSaleOrderSummary::where('organization_id', $user->organization_id)
        ->select('customer_code', DB::raw('MAX(total_sale_value) as total_sale_value'));

        if ($request->sales_person_id) {
            $topSales->whereHas('customer', function($q) use($request){
                $q->where('sales_person_id',$request->sales_person_id);
            });
        }

        $topSales->where(function ($query) use($request,$currentYears,$currentMonth,$erpCustomer) {
            if ($request) {
                $this->applyFilter($query, $request, 'sales');
            }

        });

        $topSales = $topSales->groupBy('customer_code')
        ->orderBy('total_sale_value', 'desc')
        ->limit($user_count);

        $topSalesData = $topSales->get();

        $totalSalesData= ErpSaleOrderSummary::where('organization_id', $user->organization_id);

        if ($request->sales_person_id) {
            $totalSalesData->whereHas('customer', function($q) use($request){
                $q->where('sales_person_id',$request->sales_person_id);
            });
        }

        $totalSalesData->where(function ($query) use($request,$currentYears,$currentMonth,$erpCustomer) {
            if ($request) {
                $this->applyFilter($query, $request, 'sales');
            }

        });

        $totalSalesData = $totalSalesData->sum('total_sale_value');
        $totalSales = Helper::formatNumber($totalSalesData);



        $customerIds = $topSalesData->pluck('customer_code')->toArray();
        $topSales = Helper::formatNumber($topSalesData->sum('total_sale_value'));
        $totalSales = Helper::formatNumber($totalSalesData);

        $final_customer_sale_data = $topSalesData->toArray();

        foreach ($customerIds as $customerCode) {
            $targetData = ErpCustomerTarget::where('customer_code', $customerCode)
            ->whereNotNull($currentMonthString)
            ->where('year', $currentYear)
            ->first();
            $customerData = ErpCustomer::where('customer_code', $customerCode)->first();
            $customerName = $customerData ? $customerData->company_name : 'Unknown';

            $targetDatas[] = [
                'customerCode' => $customerCode,
                'customerName' => $customerName,
                'targetData' => $targetData ? $targetData->$currentMonthString : null
            ];
        };

        $customerAchievementData = [];

        foreach ($final_customer_sale_data as $saleData) {
            $matchingTarget = collect($targetDatas)->firstWhere('customerCode', $saleData['customer_code']);

            if ($matchingTarget) {
                $customerAchievementData[] = [
                    'customerName' => $matchingTarget['customerName'],
                    'total_sale_value' => $saleData['total_sale_value'],
                    'targetData' => $matchingTarget['targetData']
                ];
            }
        }

        $data = [
            'user_count' => $user_count,
            'topSales' => $topSales,
            'totalSales' => $totalSales,
            'customerAchievementData' => $customerAchievementData,
            'currentMonthString' => strtoupper($currentMonthString),
            'currentYear' => $currentYear
        ];
        return (Object) $data;
    }

    public function getTeamMembers($user)
    {
        $data = Employee::where('manager_id', $user->id )
        ->where('organization_id', $user->organization_id)
        ->get();

        return $data;
    }

    public function view(){
        return view('crm.notes.view');
    }

}


