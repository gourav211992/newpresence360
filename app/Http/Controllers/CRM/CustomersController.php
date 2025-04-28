<?php

namespace App\Http\Controllers\CRM;

use App\Exports\crm\csv\CustomerOrderDetailExport;
use App\Exports\crm\csv\CustomerOrderExport;
use App\Helpers\ConstantHelper;
use App\Helpers\GeneralHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\CRM\ErpCreditIssue;
use App\Models\CRM\ErpCustomerTarget;
use App\Models\CRM\ErpDiary;
use App\Models\CRM\ErpSaleOrderSummary;
use App\Models\CRM\ErpCurrencyMaster;
use App\Models\ErpCustomer;
use App\Models\CRM\ErpFeedback;
use App\Models\ErpOrderHeader;
use App\Models\ErpOrderItem;
use App\Models\CRM\ErpQuestion;
use App\Models\CRM\ErpSupplyPartner;
use App\Models\CRM\ErpSupplySplit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;

class CustomersController extends Controller
{
    public function index(Request $request){
        $pageLengths = ConstantHelper::PAGE_LENGTHS;
        $length = $request->length ? $request->length : ConstantHelper::PAGE_LENGTH_10;
        $type = GeneralHelper::loginUserType();
        $user = Helper::getAuthenticatedUser();

        $customers = ErpCustomer::select('erp_customers.id','erp_customers.customer_code','erp_customers.company_name','country_id','state_id','city_id','customer_address')
            ->selectRaw('COUNT(DISTINCT erp_order_headers.order_number) as distinct_order_count')
            ->selectRaw('SUM(erp_order_headers.total_order_value) as total_order_value_sum')
            ->leftJoin('erp_order_headers', 'erp_order_headers.customer_code', '=', 'erp_customers.customer_code')
            ->where(function($query) use($type, $user){
                if($type == 'employee'){
                    $query->where('erp_customers.sales_person_id',$user->id);
                }else{
                    $query->where('erp_customers.organization_id', $user->organization_id);
                }
            })
            ->where(function($query) use($request){
                $this->applyFilter($query,$request);
            });

        if ($request->search) {
            $customers->where(function($q) use ($request) {
                $q->where('erp_customers.customer_code', 'like', '%' . $request->search . '%')
                    ->orWhere('erp_customers.company_name', 'like', '%' . $request->search . '%');
            });
        }

        $customers = $customers->where('status',ConstantHelper::ACTIVE)
            ->groupBy('erp_customers.customer_code')
            ->orderBy('distinct_order_count', 'DESC')
            ->paginate($length);


        return view('crm.customers.index',[
            'customers' => $customers,
            'pageLengths' => $pageLengths,
        ]);
    }


    public function getOrders($customerCode,Request $request){
        $pageLengths = ConstantHelper::PAGE_LENGTHS;
        $length = $request->length ? $request->length : ConstantHelper::PAGE_LENGTH_10;
        $type = GeneralHelper::loginUserType();
        $user = Helper::getAuthenticatedUser();

        $customer = ErpCustomer::where('customer_code',$customerCode)->select('customer_code','company_name')->first();

        $orderItems = ErpOrderItem::leftJoin('erp_order_headers', 'erp_order_headers.order_number', '=', 'erp_order_items.order_number')
                     ->leftJoin('erp_customers', 'erp_customers.customer_code', '=', 'erp_order_headers.customer_code')
                     ->select('erp_order_headers.order_date as order_date',
                                'erp_order_items.order_number',
                                'erp_order_items.total_order_value',
                                'erp_order_items.store_type',
                                'erp_order_items.item_code',
                                'erp_order_items.item_name',
                                'erp_order_items.uom',
                                'erp_order_items.delivery_date',
                                'erp_order_items.order_quantity',
                                'erp_order_items.delivered_quantity',
                                'erp_order_headers.order_number',
                                'erp_customers.customer_code',
                                'erp_customers.sales_person_id',
                                'erp_customers.state_id',
                                'erp_customers.country_id',
                                'erp_customers.company_name',
                                'erp_customers.city_id',
                                'erp_customers.customer_address'
                     )
                     ->where('erp_order_headers.customer_code',$customerCode)
                     ->where(function($query) use($type, $user){
                        if($type == 'employee'){
                            $query->where('erp_customers.sales_person_id',$user->id);
                        }else{
                            $query->where('erp_customers.organization_id', $user->organization_id);
                        }
                     })
                     ->where(function($query) use($request){
                        $this->applyFilter($query,$request);
                     });

        if($request->search){
            $orderItems->where('erp_order_items.order_number',$request->search);
        }

        $orderItems = $orderItems->paginate($length);

        $currencyMaster = ErpCurrencyMaster::where('organization_id', $user->organization_id)
                            ->where('status',ConstantHelper::ACTIVE)
                            ->first();

        
        return view('crm.customers.orders',[
            'customer' => $customer,
            'orderItems' => $orderItems,
            'pageLengths' => $pageLengths,
            'currencyMaster' => $currencyMaster,
        ]);
    }

    private function applyFilter($query,$request){
        if ($request->date_filter == 'today') {
            $query->whereDate('erp_order_headers.order_date',date('Y-m-d'));
        }
        
        if ($request->date_filter == 'week') {
            $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
            $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
            $query->whereDate('erp_order_headers.order_date', '>=', $startOfWeek)
                    ->whereDate('erp_order_headers.order_date', '<=', $endOfWeek);
        }

        if ($request->date_filter == 'month') {
            $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
            $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
            $query->whereDate('erp_order_headers.order_date', '>=', $startOfMonth)
                    ->whereDate('erp_order_headers.order_date', '<=', $endOfMonth);
        }

        if ($request->date_filter == 'ytd') {
            // $startOfYear = Carbon::now()->startOfYear()->toDateString();
            $currentYear = Carbon::now()->year;
            $startOfFinancialYear = Carbon::create($currentYear, 4, 1);
            if (Carbon::now()->month < 4) {
                $startOfFinancialYear->subYear();
            }

            $startOfYear = $startOfFinancialYear->toDateString();
            $today = Carbon::now()->toDateString();
            $query->whereDate('erp_order_headers.order_date', '>=', $startOfYear)
                    ->whereDate('erp_order_headers.order_date', '<=', $today);
        }

        if ($request->date_range) {
            $duration = explode(' to ', $request->date_range);
            if (count($duration) == 2) {
                $from_date = Carbon::parse($duration[0]);
                $to_date = Carbon::parse($duration[1]);
                $query->whereDate('erp_order_headers.order_date', '<=', $to_date)
                    ->whereDate('erp_order_headers.order_date', '>=', $from_date);
            }else if (count($duration) == 1) {
                $query->whereDate('erp_order_headers.order_date', '=', Carbon::parse($duration[0])->toDateString());
            } 
        }

        if ($request->customer_code) {
            $query->where('erp_customers.customer_code', $request->customer_code);
        }

        if ($request->sales_team_id) {
            $query->where('erp_customers.sales_person_id',$request->sales_team_id);
        }

        if ($request->type && $request->type_id) {
            if ($request->type == 'domestic') {
                $column = 'erp_customers.state_id';
            }

            elseif ($request->type == 'international') {
                $column = 'erp_customers.country_id';
            }

            if (isset($column)) {
                $query->whereIn($column, $request->type_id);
            }
        }

        if ($request->type && !$request->type_id) {
            $country = Country::where('code','AU')->first();
            $countryCondition = ($request->type == 'domestic') ? '=' : '!=';
            
            $query->where('erp_customers.country_id', $countryCondition, $country->id);
        }

        return $query;

    }

    public function orderCsv(Request $request)
    {
        $type = GeneralHelper::loginUserType();
        $user = Helper::getAuthenticatedUser();
        $customers = ErpCustomer::select('erp_customers.id','erp_customers.customer_code','erp_customers.company_name','country_id','state_id','city_id','customer_address')
            ->selectRaw('COUNT(DISTINCT erp_order_headers.order_number) as distinct_order_count')
            ->selectRaw('SUM(erp_order_headers.total_order_value) as total_order_value_sum')
            ->leftJoin('erp_order_headers', 'erp_order_headers.customer_code', '=', 'erp_customers.customer_code')
            ->where(function($query) use($type, $user){
                if($type == 'employee'){
                    $query->where('erp_customers.sales_person_id',$user->id);
                }else{
                    $query->where('erp_customers.organization_id', $user->organization_id);
                }
            })
            ->where(function($query) use($request){
                $this->applyFilter($query,$request);
            });

        if ($request->search) {
            $customers->where(function($q) use ($request) {
                $q->where('erp_customers.customer_code', 'like', '%' . $request->search . '%')
                    ->orWhere('erp_customers.company_name', 'like', '%' . $request->search . '%');
            });
        }

        $customers = $customers->groupBy('erp_customers.customer_code')
            ->orderBy('distinct_order_count', 'DESC');

        $orderCsv = new CustomerOrderExport();
        $fileName = "temp/crm/csv/customer-order.csv";
        $orderCsv->export($fileName,$customers);

        return redirect($fileName);
    }

    public function orderDetailCsv($customerCode,Request $request)
    {
        $type = GeneralHelper::loginUserType();
        $user = Helper::getAuthenticatedUser();
        $currencyMaster = ErpCurrencyMaster::where('organization_id', $user->organization_id)
                            ->where('status',ConstantHelper::ACTIVE)
                            ->first();

        $orderItems = ErpOrderItem::leftJoin('erp_order_headers', 'erp_order_headers.order_number', '=', 'erp_order_items.order_number')
                     ->leftJoin('erp_customers', 'erp_customers.customer_code', '=', 'erp_order_headers.customer_code')
                     ->select('erp_order_headers.order_date as order_date',
                                'erp_order_items.order_number',
                                'erp_order_items.total_order_value',
                                'erp_order_items.store_type',
                                'erp_order_items.item_code',
                                'erp_order_items.item_name',
                                'erp_order_items.uom',
                                'erp_order_items.delivery_date',
                                'erp_order_items.order_quantity',
                                'erp_order_items.delivered_quantity',
                                'erp_order_headers.order_number',
                                'erp_customers.customer_code',
                                'erp_customers.sales_person_id',
                                'erp_customers.state_id',
                                'erp_customers.country_id',
                                'erp_customers.company_name',
                                'erp_customers.city_id',
                                'erp_customers.customer_address'
                     )
                     ->where('erp_order_headers.customer_code',$customerCode)
                     ->where(function($query) use($type, $user){
                        if($type == 'employee'){
                            $query->where('erp_customers.sales_person_id',$user->id);
                        }else{
                            $query->where('erp_customers.organization_id', $user->organization_id);
                        }
                     })
                     ->where(function($query) use($request){
                        $this->applyFilter($query,$request);
                     });

        if($request->search){
            $orderItems->where('erp_order_items.order_number',$request->search);
        }

        $orderCsv = new CustomerOrderDetailExport();
        $fileName = "temp/crm/csv/customer-order-detail.csv";
        $orderCsv->export($fileName, $orderItems, $currencyMaster);

        return redirect($fileName);
    }


    public function dashbaord(Request $request){
        $user = Helper::getAuthenticatedUser();
        $topCustomersData = $this->getTopCustomersData($user,$request);
        $bottomCustomersData = $this->getBottomCustomersData($user,$request);
        $salesSummary = $this->getSalesSummary($request);
        $top5CustomersData = $this->getTop5CustomersGraphData($user,$request);

        $customerCount = ErpCustomer::where(function($query){
                            GeneralHelper::applyUserFilter($query,'ErpCustomer');
                        })
                        ->where(function($query) use($request){
                            GeneralHelper::applyDateFilter($query, $request, 'created_at','ErpCustomer');
                        })
                        ->where('status',ConstantHelper::ACTIVE)
                        ->count();
                        
        return view('crm.customers.dashboard',[
            'topCustomersData' => $topCustomersData,
            'bottomCustomersData' => $bottomCustomersData,
            'customerCount' => $customerCount,
            'date' => date('M-d'),
            'salesSummary' => $salesSummary,
            'top5CustomersData' => $top5CustomersData,
        ]);
    }

    public function getSalesSummary($request){
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

        $financialYear = "{$financialYearStart}-{$financialYearEnd}";
        
        $salesData = ErpSaleOrderSummary::where(function($query){
                            GeneralHelper::applyUserFilter($query);
                        })
                        ->where(function($query) use($request){
                            if ($request->has('customer_code')) {
                                $query->where('customer_code', $request->customer_code);
                            }
                            GeneralHelper::applyDateFilter($query, $request, 'date');
                        });
        $saleValueSum = $salesData->sum('total_sale_value');
        $totalSalesValue = Helper::currencyFormat($salesData->sum('total_sale_value'),'display');

        $currentDate = Carbon::now();
        $currentYear = Carbon::now()->year;
        $startOfFinancialYear = Carbon::create($currentYear, 4, 1);
        if (Carbon::now()->month < 4) {
            $startOfFinancialYear->subYear();
        }
        $startOfYear = $startOfFinancialYear->toDateString();
        $currentMonth = Carbon::now()->toDateString();
        $today = Carbon::now()->toDateString();

        $targetData = ErpCustomerTarget::where(function($query){
                            GeneralHelper::applyUserFilter($query);
                        });

        $achievementData = ErpSaleOrderSummary::where(function($query){
                            GeneralHelper::applyUserFilter($query);
                        });

        if(in_array($request->date_filter, ['today','month','week'])){
            $totalTarget = $targetData->sum(strtolower($currentDate->format('M')));

            $achievementData->whereYear('date', $currentDate->format('Y')) 
                        ->whereMonth('date', $currentDate->format('m'));
                        
        }else{
            $totalTarget = $targetData->where('year',$financialYear)->sum('total_target');

            $achievementData->whereDate('date', '>=', $startOfYear)
                            ->whereDate('date', '<=', $today);
        }

        $totalTargetValue = Helper::currencyFormat($totalTarget,'display');
        $totalAchievementValue = Helper::currencyFormat($achievementData->sum('total_sale_value'),'display');

        if(in_array($request->date_filter, ['today','month','week'])){
            $saleGraphData['labels'][$currentDate->format('M')] = $currentDate->format('M');
    
            $val = ErpSaleOrderSummary::where(function($query) use($request){
                            GeneralHelper::applyUserFilter($query);
                        })
                        ->whereYear('date', $currentDate->format('Y')) 
                        ->whereMonth('date', $currentDate->format('m'))
                        ->sum('total_sale_value');

            $saleGraphData['salesOrderSummary'][$currentDate->format('M')] = Helper::currencyFormat($val);

            $val =  ErpCustomerTarget::where(function($query) use($request){
                            GeneralHelper::applyUserFilter($query);
                        })
                        ->where('year', $financialYear)
                        ->sum(strtolower($currentDate->format('M')));      

            $saleGraphData['customerTarget'][$currentDate->format('M')] = Helper::currencyFormat($val);
            
        }else{
            for ($date = Carbon::parse($startOfYear); $date->lte($currentMonth); $date->addMonth()) {
                $saleGraphData['labels'][$date->format('M Y')] = $date->format('M Y');

                $val = ErpSaleOrderSummary::where(function($query) use($request){
                            GeneralHelper::applyUserFilter($query);
                        })
                        ->whereYear('date', $date->year) 
                        ->whereMonth('date', $date->month)
                        ->sum('total_sale_value');

                $saleGraphData['salesOrderSummary'][$date->format('M Y')] = Helper::currencyFormat($val);

                $val =  ErpCustomerTarget::where(function($query) use($request){
                            GeneralHelper::applyUserFilter($query);
                        })
                        ->where('year', $financialYear)
                        ->sum(strtolower($date->format('M')));      

                $saleGraphData['customerTarget'][$date->format('M Y')] = Helper::currencyFormat($val);

                
            }
        }
        
        $budgetProgress = ($totalTarget > 0) ? round(($saleValueSum / $totalTarget) * 100, 2) : 0;
        return [
            'totalSalesValue' => $totalSalesValue,
            'totalTargetValue' => $totalTargetValue,
            'saleGraphData' => $saleGraphData,
            'totalAchievementValue' => $totalAchievementValue,
            'budgetProgress' => $budgetProgress,
        ];
    }

    function getTopCustomersData($user, $request)
    {
        $limit = 10; // count for top customers for whom data to be fetched
        $currentMonthString = strtolower(Carbon::now()->format('M'));
        $currentMonth = Carbon::now()->month;
        $currentYears = Carbon::now()->year;

        if ($currentMonth < 4) {
            $financialYearStart = $currentYears - 1;
            $financialYearEnd = $currentYears;
        } else {
            $financialYearStart = $currentYears;
            $financialYearEnd = $currentYears + 1;
        }

        $financialYear = "{$financialYearStart}-{$financialYearEnd}";

        $topSalesData = ErpSaleOrderSummary::with(['customerTarget' => function($q) use($currentMonthString,$financialYear){
                        $q->select('customer_code', DB::raw("`{$currentMonthString}` as target_value"))
                        ->whereNotNull($currentMonthString)
                        ->where('year','=', $financialYear);
                    },'customer' => function($query){
                        $query->select('customer_code','company_name');
                    }])
                    ->where(function($query) use($request){
                        GeneralHelper::applyUserFilter($query);
                        GeneralHelper::applyDateFilter($query, $request, 'date');
                    })
                    ->select('customer_code', DB::raw('SUM(total_sale_value) as total_sale_value'))
                    ->groupBy('customer_code')
                    ->orderBy('total_sale_value', 'desc')
                    ->limit($limit)
                    ->get();

        $totalTopSales = Helper::currencyFormat($topSalesData->sum('total_sale_value'),'display');

        $customerCodes = $topSalesData->pluck('customer_code');
        $salesData = ErpSaleOrderSummary::where(function($query) use($request){
                            GeneralHelper::applyUserFilter($query);
                            GeneralHelper::applyDateFilter($query, $request, 'date');
                        })
                        ->whereIn('customer_code',$customerCodes);

        $totalSalesValue = Helper::currencyFormat($salesData->sum('total_sale_value'),'display');

        
        return [
            'totalTopSales' => $totalTopSales,
            'topSalesData' => $topSalesData,
            'totalSalesValue' => $totalSalesValue,
            'limit' => $limit,
        ];;
    }

    function getBottomCustomersData($user, $request)
    {
        $limit = 10; // count for top customers for whom data to be fetched
        $currentMonthString = strtolower(Carbon::now()->format('M'));
        $currentMonth = Carbon::now()->month;
        $currentYears = Carbon::now()->year;

        if ($currentMonth < 4) {
            $financialYearStart = $currentYears - 1;
            $financialYearEnd = $currentYears;
        } else {
            $financialYearStart = $currentYears;
            $financialYearEnd = $currentYears + 1;
        }

        $financialYear = "{$financialYearStart}-{$financialYearEnd}";

        $bottomSalesData = ErpSaleOrderSummary::with(['customerTarget' => function($q) use($currentMonthString,$financialYear){
                        $q->select('customer_code', DB::raw("`{$currentMonthString}` as target_value"))
                        ->whereNotNull($currentMonthString)
                        ->where('year','=', $financialYear);
                    },'customer' => function($query){
                        $query->select('customer_code','company_name');
                    }])
                    ->where(function($query) use($request){
                        GeneralHelper::applyUserFilter($query);
                        GeneralHelper::applyDateFilter($query, $request, 'date');
                    })
                    ->select('customer_code', DB::raw('SUM(total_sale_value) as total_sale_value'))
                    ->groupBy('customer_code')
                    ->orderBy('total_sale_value', 'asc')
                    ->limit($limit)
                    ->get();

        $totalBottomSales = Helper::currencyFormat($bottomSalesData->sum('total_sale_value'),'display');

        $customerCodes = $bottomSalesData->pluck('customer_code');
        $salesData = ErpSaleOrderSummary::where(function($query) use($request){
                            GeneralHelper::applyUserFilter($query);
                            GeneralHelper::applyDateFilter($query, $request, 'date');
                        })
                        ->whereIn('customer_code',$customerCodes);

        $totalSalesValue = Helper::currencyFormat($salesData->sum('total_sale_value'),'display');
        
        return [
            'totalBottomSales' => $totalBottomSales,
            'bottomSalesData' => $bottomSalesData,
            'bottomCustomerlimit' => $limit,
            'totalSalesValue' => $totalSalesValue,
        ];;
    }

    public function getProspectsData($request){
        // Prospect chart
        $prospectsGraphData = [];

        if ($request->date_filter == 'today') {
            $date = Carbon::now()->toDateString();
            $totalProspectsValue = ErpDiary::where(function($query)use($request){
                                    GeneralHelper::applyDiaryFilter($query);
                                    if ($request->has('customer_code')) {
                                        $query->where('customer_code', $request->customer_code);
                                    }
                                })
                                ->whereDate('created_at',  $date)
                                ->sum('sales_figure');
            $prospectsGraphData['data'][$date] = Helper::currencyFormat($totalProspectsValue);
            $prospectsGraphData['labels'][$date] = $date;
        }
        
        elseif ($request->date_filter == 'week') {
            $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
            $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
            for ($date = Carbon::parse($startOfWeek); $date->lte($endOfWeek); $date->addDay()) {
                $prospectsGraphData['labels'][$date->format('D')] = $date->format('D');
                $totalProspectsValue = ErpDiary::where(function($query)use($request){
                                    GeneralHelper::applyDiaryFilter($query);
                                    if ($request->has('customer_code')) {
                                        $query->where('customer_code', $request->customer_code);
                                    }
                                })
                                ->whereDate('created_at',  $date)
                                ->sum('sales_figure');

                $prospectsGraphData['data'][$date->format('D')] = Helper::currencyFormat($totalProspectsValue);
            }
        }

        elseif ($request->date_filter == 'month') {
            $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
            $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
            for ($date = Carbon::parse($startOfMonth); $date->lte($endOfMonth); $date->addDay()) {
                $prospectsGraphData['labels'][$date->format('F Y')] = $date->format('F Y');
                $totalProspectsValue = ErpDiary::where(function($query) use($request){
                                    GeneralHelper::applyDiaryFilter($query);
                                    if ($request->has('customer_code')) {
                                        $query->where('customer_code', $request->customer_code);
                                    }
                                })
                                ->whereDate('created_at',  $date)
                                ->sum('sales_figure');
                $prospectsGraphData['data'][$date->format('F Y')] = Helper::currencyFormat($totalProspectsValue);
            }
        }

        elseif ($request->date_filter == 'ytd') {
            // $startOfYear = Carbon::now()->startOfYear()->toDateString();
            $currentYear = Carbon::now()->year;
            $startOfFinancialYear = Carbon::create($currentYear, 4, 1);
            if (Carbon::now()->month < 4) {
                $startOfFinancialYear->subYear();
            }

            $startOfYear = $startOfFinancialYear->toDateString();
            $currentMonth = Carbon::now()->toDateString();
            for ($date = Carbon::parse($startOfYear); $date->lte($currentMonth); $date->addMonth()) {
                $prospectsGraphData['labels'][$date->format('F Y')] = $date->format('F Y');
                $totalProspectsValue = ErpDiary::where(function($query) use($request){
                                    GeneralHelper::applyDiaryFilter($query);
                                    if ($request->has('customer_code')) {
                                        $query->where('customer_code', $request->customer_code);
                                    }
                                })
                                ->whereDate('created_at',  $date)
                                ->sum('sales_figure');
                $prospectsGraphData['data'][$date->format('F Y')] = Helper::currencyFormat($totalProspectsValue);
            }
        }

        else{
            $endOfThisMonth = Carbon::now()->endOfMonth();

            foreach (Carbon::now()->subMonths(5)->monthsUntil($endOfThisMonth) as $month) {
                $prospectsGraphData['labels'][$month->format('M Y')] = $month->format('M Y');
                $totalProspectsValue = ErpDiary::where(function($query) use($request){
                                    if ($request->has('customer_code')) {
                                        $query->where('customer_code', $request->customer_code);
                                    }
                                    GeneralHelper::applyDiaryFilter($query);
                                })
                                ->whereMonth('created_at', $month)
                                ->sum('sales_figure');
                // dd($totalProspectsValue->toSql(),$totalProspectsValue->getBindings());
                // $prospectsGraphData['data'][$month->format('M Y')] = Helper::currencyFormat($totalProspectsValue);
                $prospectsGraphData['data'][$month->format('M Y')] = $totalProspectsValue;
            }
        }       

        return [
            'prospectsGraphData' => $prospectsGraphData,
        ];
    }

    function getTop5CustomersGraphData($user,$request)
    {
        $limit = 5; 

        $erpSaleOrderSummary = ErpSaleOrderSummary::join('erp_customers', 'erp_sale_order_summaries.customer_code', 'erp_customers.customer_code')
                ->join('erp_industries', 'erp_industries.id','erp_customers.industry_id')
                ->where(function($query) use($user) {
                    if (Auth::guard('web')->check()) {
                        $query->where('erp_sale_order_summaries.organization_id', $user->organization_id);
                    } elseif (Auth::guard('web2')->check()) {
                        $teamIds = GeneralHelper::getTeam($user);
                        $query->whereIn('erp_customers.sales_person_id', $teamIds);
                    }
                })
                ->where(function($query) use($request) {
                    if ($request->customer_code) {
                        $query->where('erp_sale_order_summaries.customer_code', $request->customer_code);
                    }
                })
                ->where(function($query) use($request) {
                    if ($request->date_filter == 'today') {
                        $query->whereDate('erp_sale_order_summaries.date', date('Y-m-d'));
                    }

                    elseif ($request->date_filter == 'week') {
                        $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
                        $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
                        $query->whereDate('erp_sale_order_summaries.date', '>=', $startOfWeek)
                        ->whereDate('erp_sale_order_summaries.date', '<=', $endOfWeek);
                    }

                    elseif ($request->date_filter == 'month') {
                        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
                        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
                        $query->whereDate('erp_sale_order_summaries.date', '>=', $startOfMonth)
                        ->whereDate('erp_sale_order_summaries.date', '<=', $endOfMonth);
                    }

                    else{
                        // $startOfYear = Carbon::now()->startOfYear()->toDateString();
                        $currentYear = Carbon::now()->year;
                        $startOfFinancialYear = Carbon::create($currentYear, 4, 1);
                        if (Carbon::now()->month < 4) {
                            $startOfFinancialYear->subYear();
                        }

                        $startOfYear = $startOfFinancialYear->toDateString();
                        $today = Carbon::now()->toDateString();
                        $query->whereDate('erp_sale_order_summaries.date', '>=', $startOfYear)
                        ->whereDate('erp_sale_order_summaries.date', '<=', $today);
                    }
                });

        $topCustomerData = $erpSaleOrderSummary->clone()->select('erp_industries.name as industry_name','erp_customers.industry_id', DB::raw('SUM(total_sale_value) as total_sale_value'))
                            ->whereNotNull('industry_id') 
                            ->groupBy('erp_customers.industry_id') 
                            ->orderBy('total_sale_value', 'desc') 
                            ->limit($limit)
                            ->get();

        $top5TotalSales = $topCustomerData->sum('total_sale_value');
        $totalSales = $erpSaleOrderSummary->clone()->sum('total_sale_value');

        $topProspectsSplitByIndustry = [];
        
        foreach($topCustomerData as $key => $value){
            $sales_percentage = (($value->total_sale_value)/$totalSales)*100;
            $topProspectsSplitByIndustry[] = [
                'industry' => $value->industry_name,
                'total_sale_value' => Helper::currencyFormat($value->total_sale_value,'display'),
                'sales_percentage' => round($sales_percentage),
                'color_code' => sprintf('#%06X', mt_rand(0, 0xFFFFFF))
            ];
        }

        $top5TotalSales = round($top5TotalSales, 2);
        $totalSales = round($totalSales, 2);
        $otherSales = $totalSales > $top5TotalSales  ? $totalSales - $top5TotalSales : 0;
        $otherSalesPrc = ($totalSales > 0) ? min(round(($otherSales / $totalSales) * 100, 2), 100) : 0;     
        
        if($otherSales > 0){
            $topProspectsSplitByIndustry[] = [
                'industry' => 'All Other',
                'total_sale_value' => Helper::currencyFormat($otherSales,'display'),
                'sales_percentage' => $otherSalesPrc,
                'color_code' => sprintf('#%06X', mt_rand(0, 0xFFFFFF)),
            ];
        }
        
        return [
            'topProspectsSplitByIndustry' => $topProspectsSplitByIndustry,
        ];;
    }

    public function purchaseTrendsByMonth($request){
        // Prospect chart
        $purchaseTrends = [];

        if ($request->date_filter == 'today') {
            $date = Carbon::now()->toDateString();
                $totalSaleValue = ErpSaleOrderSummary::where(function($query){
                            GeneralHelper::applyUserFilter($query);
                        })
                    ->where('customer_code', $request->customer_code)
                    ->whereDate('date',  $date)
                    ->sum('total_sale_value');

            $purchaseTrends['data'][$date] = Helper::currencyFormat($totalSaleValue);
            $purchaseTrends['labels'][$date] = $date;
        }
        
        elseif ($request->date_filter == 'week') {
            $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
            $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
            for ($date = Carbon::parse($startOfWeek); $date->lte($endOfWeek); $date->addDay()) {
                $totalSaleValue = ErpSaleOrderSummary::where(function($query){
                            GeneralHelper::applyUserFilter($query);
                        })
                    ->where('customer_code', $request->customer_code)
                    ->whereDate('date',  $date)
                    ->sum('total_sale_value');

                $purchaseTrends['labels'][$date->format('D')] = $date->format('D');
                $purchaseTrends['data'][$date->format('D')] = Helper::currencyFormat($totalSaleValue);
            }
        }

        elseif ($request->date_filter == 'month') {
            $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
            $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
            for ($date = Carbon::parse($startOfMonth); $date->lte($endOfMonth); $date->addDay()) {
                $totalSaleValue = ErpSaleOrderSummary::where(function($query){
                            GeneralHelper::applyUserFilter($query);
                        })
                    ->where('customer_code', $request->customer_code)
                    ->whereDate('date',  $date->format('Y-m-d'))
                    ->sum('total_sale_value');

                $purchaseTrends['labels'][$date->format('d M')] = $date->format('d');
                $purchaseTrends['data'][$date->format('d M')] = Helper::currencyFormat($totalSaleValue);
            }
        }

        else {
            $currentYear = Carbon::now()->year;
            $startOfFinancialYear = Carbon::create($currentYear, 4, 1);
            if (Carbon::now()->month < 4) {
                $startOfFinancialYear->subYear();
            }

            $startOfYear = $startOfFinancialYear->toDateString();
            $currentMonth = Carbon::now()->toDateString();
            for ($date = Carbon::parse($startOfYear); $date->lte($currentMonth); $date->addMonth()) {
                $purchaseTrends['labels'][$date->format('F Y')] = $date->format('M Y');
                $totalSaleValue = ErpSaleOrderSummary::where(function($query){
                                    GeneralHelper::applyUserFilter($query);
                                })
                            ->where('customer_code', $request->customer_code)
                            ->whereYear('date', $date->year) 
                            ->whereMonth('date', $date->month)
                            ->sum('total_sale_value');

                $purchaseTrends['data'][$date->format('F Y')] = Helper::currencyFormat($totalSaleValue);
                
            }
        }    
        // dd($purchaseTrends);
        return [
            'purchaseTrendsGraphData' => $purchaseTrends,
        ];
    }

    public function view($customerCode,Request $request){
        $user = Helper::getAuthenticatedUser();
        $request->merge(['customer_code' => $customerCode]);
        $customer = ErpCustomer::where('customer_code',$customerCode)->select('id','organization_id','customer_code','company_name')->first();
        $salesSummary = $this->getSalesSummary($request);
        $purchaseTrends = $this->purchaseTrendsByMonth($request);
        $creditAmount = ErpCreditIssue::where('customer_code',$customerCode)
                        ->where('organization_id',$user->organization_id)
                        ->where('type',ConstantHelper::CREDIT)
                        ->where(function($query) use($request){
                            GeneralHelper::applyDateFilter($query, $request, 'book_date');
                        })
                        ->sum('amount');
        $debitAmount = ErpCreditIssue::where('customer_code',$customerCode)
                        ->where('organization_id',$user->organization_id)
                        ->where('type',ConstantHelper::DEBIT)
                        ->where(function($query) use($request){
                            GeneralHelper::applyDateFilter($query, $request, 'book_date');
                        })
                        ->sum('amount');
        $creditLimit = $creditAmount - $debitAmount;
        $diaries = ErpDiary::where('customer_code',$customerCode)
                        ->where('organization_id',$user->organization_id)
                        ->orderBy('id','desc')
                        ->get()
                        ->take(5);
        $partners = ErpSupplyPartner::where('status',ConstantHelper::ACTIVE)->where('organization_id', $user->organization_id)->get();
        $splitData = ErpSupplySplit::where('customer_code',$customerCode)->where('organization_id', $user->organization_id)->get();
        
        $questions = ErpQuestion::with(['feedback' => function($query) use($customerCode,$user){
                        $query->where('customer_code',$customerCode)
                        ->where('organization_id', $user->organization_id)
                        ->select('question_id','feedback','organization_id','customer_code');
                    }])
                    ->where('status',ConstantHelper::ACTIVE)
                    ->where('organization_id', $user->organization_id)
                    ->select('id','question','organization_id','status','sequence')
                    ->orderBy('sequence','asc')
                    ->get();

        $feedbacks = ErpFeedback::with('question')
                    ->where('customer_code',$customerCode)
                    ->where('organization_id', $user->organization_id)
                    ->get();
    
        $supplySplitData = $this->supplySplitData($user,$request);
        
        return view('crm.customers.view',[
            'customer' => $customer,
            'salesSummary' => $salesSummary,
            'purchaseTrends' => $purchaseTrends,
            'creditLimit' => round($creditLimit,2),
            'diaries' => $diaries,
            'partners' => $partners,
            'splitData' => $splitData,
            'supplySplitData' => $supplySplitData,
            'feedbacks' => $feedbacks,
            'questions' => $questions,
        ]);

    }

    private function supplySplitData($user, $request){
        $supplyPartners = ErpSupplyPartner::select('id','name','organization_id')
                        ->where('organization_id', $user->organization_id)
                        ->where('status',ConstantHelper::ACTIVE)
                        ->get();
        
        $supplySplit = ErpSupplySplit::where('organization_id', $user->organization_id)
                        ->where('customer_code',$request->customer_code)
                        ->orderBy('id','DESC')
                        ->get();

        $totalSupplySplitPerc = $supplySplit->sum('supply_percentage');

        foreach ($supplyPartners as $supplyPartner) {
            $supplySplitPerc = $supplySplit->where('supply_partner_id',$supplyPartner->id)->sum('supply_percentage');
            $supplyPartner->percentage = $totalSupplySplitPerc > 0 ?  (($supplySplitPerc/$totalSupplySplitPerc)*100) : 0;
            $supplyPartner->color_code = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
           
        }

        return [
            // 'supplySplit' => $supplySplit,
            'supplyPartners' => $supplyPartners,
        ];
    }


}
