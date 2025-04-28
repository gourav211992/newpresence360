<?php

namespace App\Http\Controllers\CRM;

use App\Helpers\ConstantHelper;
use App\Helpers\GeneralHelper;
use App\Helpers\Helper;
use Auth;
use App\Http\Controllers\Controller;
use App\Models\CRM\ErpDiary;
use App\Models\CRM\ErpSaleOrderSummary;
use App\Models\City;
use App\Models\Country;
use App\Models\CRM\ErpCustomerTarget;
use App\Models\Employee;
use App\Models\CRM\ErpCurrencyMaster;
use App\Models\ErpCustomer;
use App\Models\State;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IndexController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $teams = $this->getTeamMembers($user);
        $salesSummary = $this->getSalesSummary($user,$request);
        $meetingSummary = $this->meetingSummary($user,$request);
        $topCustomersData = $this->getTopCustomersData($user,$request);

        // $user = Helper::getAuthenticatedUser();
        $userType = GeneralHelper::loginUserType();

        $customers = ErpCustomer::where(function($query){
                            GeneralHelper::applyUserFilter($query,'ErpCustomer');
                    })
                    ->where('status',ConstantHelper::ACTIVE)
                    ->orderBy('company_name','ASC')
                    ->get();

        $teamsIds = GeneralHelper::getTeam($user);
        $salesTeam = Employee::where(function($query) use($user,$userType, $teamsIds){
                        if($userType == 'employee'){
                            $query->whereIn('id', $teamsIds);
                        }else{
                            $query->where('organization_id', $user->organization_id);
                        }
                    })->get();

        $currencyMaster = ErpCurrencyMaster::where('organization_id', $user->organization_id)
                            ->where('status',ConstantHelper::ACTIVE)
                            ->first();

        return view('crm.index', [
            'teams' => $teams,
            'salesSummary' => $salesSummary,
            'salesTeam' => $salesTeam,
            'customers' => $customers,
            'meetingSummary' => $meetingSummary,
            'topCustomersData' => $topCustomersData,
            'userType' => $userType,
            'currencyMaster' => $currencyMaster,
            'user' => $user,
        ] );
    }

    public function getTeamMembers($user)
    {
        $data = Employee::where('manager_id', $user->id )
        ->where('organization_id', $user->organization_id)
        ->get();

        return $data;
    }

    public function getSalesSummary($user, $request){
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
        
        $salesData = ErpSaleOrderSummary::where(function($query) use($request){
                            GeneralHelper::applyUserFilter($query);
                            GeneralHelper::applyDateFilter($query, $request, 'date');
                            $this->applyFilter($query, $request, 'ErpSaleOrderSummary');
                        });

        // Total Achieved
        $totalAchieved = $salesData->sum('total_sale_value');

        // Total sale value
        $totalSalesValue = Helper::currencyFormat($salesData->sum('total_sale_value'),'display');

        $totalProspectsValue = ErpCustomer::where(function($query) use($request){
                GeneralHelper::applyUserFilter($query,'ErpCustomer');
                GeneralHelper::applyDateFilter($query, $request, 'created_at');
                $this->applyFilter($query, $request,'ErpCustomer');
            })
            ->where('status',ConstantHelper::PENDING)
            ->where('is_prospect','1')
            ->sum('sales_figure');

        // Total prospect value
        $totalProspectsValue = Helper::currencyFormat($totalProspectsValue,'display');
        

        $currentDate = Carbon::now();
        $currentYear = Carbon::now()->year;
        $startOfFinancialYear = Carbon::create($currentYear, 4, 1);
        if (Carbon::now()->month < 4) {
            $startOfFinancialYear->subYear();
        }
        $startOfYear = $startOfFinancialYear->toDateString();
        $currentMonth = Carbon::now()->toDateString();
        $today = Carbon::now()->toDateString();

        $totalTarget = 0;
        $targetData = ErpCustomerTarget::where(function($query) use($request){
                            GeneralHelper::applyUserFilter($query);
                            $this->applyFilter($query, $request, 'ErpCustomerTarget');
                        });

        $achievementData = ErpSaleOrderSummary::where(function($query) use($request){
                            GeneralHelper::applyUserFilter($query);
                            $this->applyFilter($query, $request, 'ErpSaleOrderSummary');
                        });

        if(in_array($request->date_filter, ['today','month','week'])){
            $totalTarget = $targetData->sum(strtolower($currentDate->format('M')));

            $achievementData->whereYear('date', $currentDate->format('Y')) 
                        ->whereMonth('date', $currentDate->format('m'));
                        
        }else{
            // Current Financial Year
            $totalTarget = $targetData->where('year',$financialYear)->sum('total_target');

            $achievementData->whereDate('date', '>=', $startOfYear)
                            ->whereDate('date', '<=', $today);
        }

        // Total Target & toal achievement
        $totalTargetValue = Helper::currencyFormat($totalTarget,'display');
        $totalAchievementValue = Helper::currencyFormat($achievementData->sum('total_sale_value'),'display');

        $saleGraphData = [];
        if(in_array($request->date_filter, ['today','month','week'])){
            $saleGraphData['labels'][$currentDate->format('M')] = $currentDate->format('M');
    
            $val = ErpSaleOrderSummary::where(function($query) use($request){
                            GeneralHelper::applyUserFilter($query);
                            $this->applyFilter($query, $request, 'ErpSaleOrderSummary');
                        })
                        ->whereYear('date', $currentDate->format('Y')) 
                        ->whereMonth('date', $currentDate->format('m'))
                        ->sum('total_sale_value');

            $saleGraphData['salesOrderSummary'][$currentDate->format('M')] = Helper::currencyFormat($val);

            $val =  ErpCustomerTarget::where(function($query) use($request){
                            GeneralHelper::applyUserFilter($query);
                            $this->applyFilter($query, $request, 'ErpCustomerTarget');
                        })
                        ->where('year', $financialYear)
                        ->sum(strtolower($currentDate->format('M')));      

            $saleGraphData['customerTarget'][$currentDate->format('M')] = Helper::currencyFormat($val);
            
        }else{
            for ($date = Carbon::parse($startOfYear); $date->lte($currentMonth); $date->addMonth()) {
                $saleGraphData['labels'][$date->format('M Y')] = $date->format('M');

                $val = ErpSaleOrderSummary::where(function($query) use($request){
                            GeneralHelper::applyUserFilter($query);
                            $this->applyFilter($query, $request, 'ErpSaleOrderSummary');
                        })
                        ->whereYear('date', $date->year) 
                        ->whereMonth('date', $date->month)
                        ->sum('total_sale_value');

                $saleGraphData['salesOrderSummary'][$date->format('M Y')] = Helper::currencyFormat($val);

                $val =  ErpCustomerTarget::where(function($query) use($request){
                            GeneralHelper::applyUserFilter($query);
                            $this->applyFilter($query, $request, 'ErpCustomerTarget');
                        })
                        ->where('year', $financialYear)
                        ->sum(strtolower($date->format('M')));      

                $saleGraphData['customerTarget'][$date->format('M Y')] = Helper::currencyFormat($val);

                
            }
        }

        // Budget Progress
        $budgetProgress = ($totalTarget > 0) ? round(($totalAchieved / $totalTarget) * 100, 2) : 0;
        
        return [
            'totalSalesValue' => $totalSalesValue,
            'totalTargetValue' => $totalTargetValue,
            'saleGraphData' => $saleGraphData,
            'totalAchievementValue' => $totalAchievementValue,
            'budgetProgress' => $budgetProgress,
            'totalProspectsValue' => $totalProspectsValue,
        ];
    }

    public function meetingSummary($user, $request){
        // Reminders & To-Do’s
        $erpDiaries = ErpDiary::with(['customer','attachments','createdByEmployee' => function($q){
            $q->select('id','name');
        },'createdByUser' => function($q){
            $q->select('id','name');
        }])
            ->where(function($query) use($request){
                GeneralHelper::applyDiaryFilter($query);
                GeneralHelper::applyDateFilter($query, $request, 'created_at');
                $this->applyFilter($query, $request, 'ErpDiary');
            })
            ->orderBy('id','desc')
            ->limit(5)
            ->get();

        // Prospect chart
        $prospectsGraphData = [];

        if ($request->date_filter == 'today') {
            $date = Carbon::now()->toDateString();
            $totalProspectsValue = ErpCustomer::where(function($query) use($request){
                                    GeneralHelper::applyUserFilter($query,'ErpCustomer');
                                    $this->applyFilter($query, $request, 'ErpCustomer');
                                })
                                ->whereDate('updated_at',  $date)
                                ->where('lead_status',ConstantHelper::WON)
                                ->sum('sales_figure');
            $prospectsGraphData['data'][$date] = Helper::currencyFormat($totalProspectsValue);
            $prospectsGraphData['labels'][$date] = $date;
        }
        elseif ($request->date_filter == 'week') {
            $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
            $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
            for ($date = Carbon::parse($startOfWeek); $date->lte($endOfWeek); $date->addDay()) {
                $prospectsGraphData['labels'][$date->format('D')] = $date->format('D');
                $totalProspectsValue = ErpCustomer::where(function($query) use($request){
                                    GeneralHelper::applyUserFilter($query,'ErpCustomer');
                                    $this->applyFilter($query, $request, 'ErpCustomer');
                                })
                                ->whereDate('updated_at',  $date)
                                ->where('lead_status',ConstantHelper::WON)
                                ->sum('sales_figure');

                $prospectsGraphData['data'][$date->format('D')] = Helper::currencyFormat($totalProspectsValue);
            }
        }
        elseif ($request->date_filter == 'month') {
            $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
            $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
            for ($date = Carbon::parse($startOfMonth); $date->lte($endOfMonth); $date->addDay()) {
                $prospectsGraphData['labels'][$date->format('d M')] = $date->format('d');
                $totalProspectsValue = ErpCustomer::where(function($query) use($request){
                                    GeneralHelper::applyUserFilter($query,'ErpCustomer');
                                    $this->applyFilter($query, $request, 'ErpCustomer');
                                })
                                ->whereDate('updated_at',  $date->format('Y-m-d'))
                                ->where('lead_status',ConstantHelper::WON)
                                ->sum('sales_figure');
                $prospectsGraphData['data'][$date->format('d M')] = Helper::currencyFormat($totalProspectsValue);
            }
        }
        else{
            $currentYear = Carbon::now()->year;
            $startOfFinancialYear = Carbon::create($currentYear, 4, 1);
            if (Carbon::now()->month < 4) {
                $startOfFinancialYear->subYear();
            }

            $startOfYear = $startOfFinancialYear->toDateString();
            $currentMonth = Carbon::now()->toDateString();
            for ($date = Carbon::parse($startOfYear); $date->lte($currentMonth); $date->addMonth()) {
                $prospectsGraphData['labels'][$date->format('F Y')] = $date->format('M Y');
                $totalProspectsValue = ErpCustomer::where(function($query) use($request){
                                    GeneralHelper::applyUserFilter($query,'ErpCustomer');
                                    $this->applyFilter($query, $request, 'ErpCustomer');
                                })
                                ->whereYear('updated_at', $date->year)
                                ->whereMonth('updated_at', $date->month)
                                ->where('lead_status',ConstantHelper::WON)
                                ->sum('sales_figure');
                $prospectsGraphData['data'][$date->format('F Y')] = Helper::currencyFormat($totalProspectsValue);
            }
        }     

        return [
            'erpDiaries' => $erpDiaries,
            'prospectsGraphData' => $prospectsGraphData,
        ];
    }


    public function getTopCustomersData($user,$request)
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
                'sales_percentage' => round($sales_percentage, 2),
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
        ];
    }

    // public function getOrderSummary($user, $request){
    //     $endOfThisMonth = Carbon::now()->endOfMonth();

    //     $ordersGraphData = [];

    //     if ($request->date_filter == 'today') {
    //         $ordersGraphData['labels'][] = Carbon::today()->format('d-m-y');
    //         $totalOrderValue = ErpOrderHeader::where(function($query){
    //                                 GeneralHelper::applyUserFilter($query);
    //                             })
    //                             ->whereDate('order_date',date('Y-m-d'))
    //                             ->sum('total_order_value');

    //         $ordersGraphData['data'][] = Helper::currencyFormat($totalOrderValue);

    //     }elseif($request->date_filter == 'week'){

    //         $startOfWeek = Carbon::now()->startOfWeek();;
    //         $endOfWeek = Carbon::now()->endOfWeek();

    //         for ($date = $startOfWeek; $date->lte($endOfWeek); $date->addDay()) {
    //             $ordersGraphData['labels'][] = $date->format('D');
    //             $totalOrderValue = ErpOrderHeader::where(function($query){
    //                                             GeneralHelper::applyUserFilter($query);
    //                                         })
    //                                         ->whereDate('order_date', $date->toDateString())
    //                                         ->sum('total_order_value');
    //             $ordersGraphData['data'][] = Helper::currencyFormat($totalOrderValue);
    //         }

    //     }elseif($request->date_filter == 'month'){
    //         $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
    //         $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
    //         $ordersGraphData['labels'][] = Carbon::now()->format('F Y');
    //         $totalOrderValue = ErpOrderHeader::where(function($query){
    //                             GeneralHelper::applyUserFilter($query);
    //                         })
    //                         ->whereBetween('order_date', [$startOfMonth, $endOfMonth])
    //                         ->sum('total_order_value');
    //         $ordersGraphData['data'][] = Helper::currencyFormat($totalOrderValue);
            
    //     }else{
    //         foreach (Carbon::now()->subMonths(5)->monthsUntil($endOfThisMonth) as $month) {
    //             $ordersGraphData['labels'][$month->month] = $month->format('M');
    
    //             $totalOrderValue = ErpOrderHeader::where(function($query){
    //                                 GeneralHelper::applyUserFilter($query);
    //                             })
    //                             ->where(function($query) use($request){
    //                                 $this->applyFilter($query, $request, 'order_date');
    //                             })
    //                             ->whereYear('order_date', $month->year) 
    //                             ->whereMonth('order_date', $month->month)
    //                             ->sum('total_order_value');
    //             $ordersGraphData['data'][] = Helper::currencyFormat($totalOrderValue);
    //         }
    //     }
    //     return [
    //         'ordersGraphData' => $ordersGraphData 
    //     ];
    // }

    // function getTopCustomersData($user, $request)
    // {
    //     $limit = 10; // count for top customers for whom data to be fetched
    //     $currentMonthString = strtolower(Carbon::now()->format('M'));
    //     $currentMonth = Carbon::now()->month;
    //     $currentYears = Carbon::now()->year;

    //     if ($currentMonth < 4) {
    //         $financialYearStart = $currentYears - 1;
    //         $financialYearEnd = $currentYears;
    //     } else {
    //         $financialYearStart = $currentYears;
    //         $financialYearEnd = $currentYears + 1;
    //     }

    //     $financialYear = "{$financialYearStart}-{$financialYearEnd}";

    //     $topSalesData = ErpSaleOrderSummary::with(['customerTarget' => function($q) use($currentMonthString,$financialYear){
    //                     $q->select('customer_code', DB::raw("`{$currentMonthString}` as target_value"))
    //                     ->whereNotNull($currentMonthString)
    //                     ->where('year','=', $financialYear);
    //                 },'customer' => function($query){
    //                     $query->select('customer_code','company_name');
    //                 }])
    //                 ->where(function($query){
    //                     GeneralHelper::applyUserFilter($query);
    //                 })
    //                 ->select('customer_code', DB::raw('SUM(total_sale_value) as total_sale_value'))
    //                 ->where(function ($query) use($request) {
    //                     if ($request) {
    //                         $this->applyFilter($query, $request, 'date');
    //                     }
    //                 })
    //                 ->groupBy('customer_code')
    //                 ->orderBy('total_sale_value', 'desc')
    //                 ->limit($limit)
    //                 ->get();

    //     $totalTopSales = Helper::currencyFormat($topSalesData->sum('total_sale_value'),'display');
        
    //     return [
    //         'totalTopSales' => $totalTopSales,
    //         'topSalesData' => $topSalesData,
    //         'limit' => $limit,
    //     ];;
    // }

    private function applyFilter($query, $request, $model=null){

        // if ($request->date_filter == 'today') {
        //     $query->whereDate($dateColumn,date('Y-m-d'));
        // }
        
        // if ($request->date_filter == 'week') {
        //     $startOfWeek = Carbon::now()->startOfWeek()->toDateString();
        //     $endOfWeek = Carbon::now()->endOfWeek()->toDateString();
        //     $query->whereDate($dateColumn, '>=', $startOfWeek)
        //     ->whereDate($dateColumn, '<=', $endOfWeek);
        // }

        // if ($request->date_filter == 'month') {
        //     $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        //     $endOfMonth = Carbon::now()->endOfMonth()->toDateString();
        //     $query->whereDate($dateColumn, '>=', $startOfMonth)
        //     ->whereDate($dateColumn, '<=', $endOfMonth);
        // }

        // if ($request->date_filter == 'ytd') {
        //     $currentYear = Carbon::now()->year;
        //     $startOfFinancialYear = Carbon::create($currentYear, 4, 1);
        //     if (Carbon::now()->month < 4) {
        //         $startOfFinancialYear->subYear();
        //     }

        //     $startOfYear = $startOfFinancialYear->toDateString();
        //     $today = Carbon::now()->toDateString();
        //     $query->whereDate($dateColumn, '>=', $startOfYear)
        //     ->whereDate($dateColumn, '<=', $today);
        // }

        // if ($request->date_range) {
        //     $duration = explode(' to ', $request->date_range);
        //     $from_date = Carbon::parse($duration[0]);
        //     $to_date = isset($duration[1]) ? Carbon::parse($duration[1]) : Carbon::parse($duration[0]);

        //     $query->whereDate($dateColumn, '<=', $to_date)
        //     ->whereDate($dateColumn, '>=', $from_date);
        // }

        if ($request->customer_code) {
            $query->where('customer_code', $request->customer_code);
        }

        if ($request->sales_team_id) {
            if($model == 'ErpCustomer'){
                $query->where('sales_person_id',$request->sales_team_id);
            }else{
                $query->whereHas('customer', function($q) use($request){
                    $q->where('sales_person_id',$request->sales_team_id);
                });
            }
        }

        if ($request->type && $request->type_id) {
            if ($request->type == 'domestic') {
                $column = 'state_id';
            }

            elseif ($request->type == 'international') {
                $column = 'country_id';
            }

            if (isset($column)) {
                if ($model == 'ErpCustomer') {
                    $query->whereIn($column, $request->type_id);
                } else {
                    $query->whereHas('customer', function($q) use($column, $request) {
                        $q->whereIn($column, $request->type_id);
                    });
                }
            }
        }

        if ($request->type && !$request->type_id) {
            $country = Country::where('code','AU')->first();
            $countryCondition = ($request->type == 'domestic') ? '=' : '!=';
            
            if ($model == 'ErpCustomer') {
                $query->where('country_id', $countryCondition, $country->id);
            } else {
                $query->whereHas('customer', function ($q) use ($country, $countryCondition) {
                    $q->where('country_id', $countryCondition, $country->id);
                });
            }
        }

        return $query;
    }

    // private function applyDataFilter($query, $request){
    //     if ($request->customer_code) {
    //         $query->where('customer_code', $request->customer_code);
    //     }

    //     if ($request->sales_team_id) {
    //         $query->whereHas('customer', function($q) use($request){
    //             $q->where('sales_person_id',$request->sales_team_id);
    //         });
    //     }

    //     if ($request->type && $request->type_id) {
    //         if ($request->type == 'domestic') {
    //             $column = 'state_id';
    //         }

    //         elseif ($request->type == 'international') {
    //             $column = 'country_id';
    //         }

    //         if (isset($column)) {
    //             $query->whereHas('customer', function($q) use($column, $request) {
    //                 $q->whereIn($column, $request->type_id);
    //             });
    //         }
    //     }

    //     if ($request->type && !$request->type_id) {
    //         $country = Country::where('code','AU')->first();
    //         $countryCondition = ($request->type == 'domestic') ? '=' : '!=';
            
    //         $query->whereHas('customer', function ($q) use ($country, $countryCondition) {
    //             $q->where('country_id', $countryCondition, $country->id);
    //         });
    //     }
        
    //     return $query;
    // }

    public function view(){
        return view('crm.notes.view');
    }

    public function getStates(Country $country){
        $states = State::where('country_id',$country->id)->get();
        return [
            'data' => $states
        ];
    }

    public function getCities(State $state){
        $cities = City::where('state_id',$state->id)->get();
        return [
            'data' => $cities
        ];
    }

    public function getCountriesStates($type){
        $country = Country::where('code','AU')->first();
        if($type == 'international'){
            $data = Country::where('id','!=',$country->id)->get();
        }else{
            $data = State::where('country_id',$country->id)->get();
        }
        return [
            'data' => $data
        ];
    }

}


