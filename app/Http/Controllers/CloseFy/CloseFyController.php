<?php

namespace App\Http\Controllers\CloseFy;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\AuthUser;
use App\Models\CloseCurrentFy;
use App\Models\ErpFinancialYear;
use App\Models\Group;
use App\Models\Organization;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CloseFyController extends Controller
{

    public function index(Request $request)
    { 
        $start = null;
        $end = null;
        if ($request->fyear) {
            // dd($request->all());
            $dates = explode(' to ', $request->date);
            $start = date('Y-m-d', strtotime($dates[0]));
            $end = date('Y-m-d', strtotime($dates[1]));
        }
        $fyearId = $request->fyear_id;
        $user = Helper::getAuthenticatedUser();
        $userId = $user->id;
        $organizationId = $user->organization_id;
        $companies = Helper::getAuthenticatedUser()->access_rights_org;
        $fyears = Helper::getAllFinancialYear();
        // dd($fyears);
        $financialYear = Helper::getFinancialYear(date('Y-m-d'));
        if ($financialYear) {
            $startYear = \Carbon\Carbon::parse($financialYear['start_date'])->format('Y');
            $endYearShort = \Carbon\Carbon::parse($financialYear['end_date'])->format('y');
        } else {
            $now = \Carbon\Carbon::now();
            $startYear = $now->format('Y');
            $endYearShort = $now->copy()->addYear()->format('y'); // next year in 2-digit
        }
        
        $current_range = $startYear . '-' . $endYearShort;
        $employees = Helper::getOrgWiseUserAndEmployees($organizationId);
        // dd($employees->authUser);
        return view('close-fy.close-fy',compact('companies', 'organizationId','fyears','fyearId','employees','current_range'));
    }

    public function getFyInitialGroups(Request $r)
    {
        if ($r->date == "") {
            $financialYear = Helper::getFinancialYear(date('Y-m-d'));
            $startDate = $financialYear['start_date'];
            $endDate = $financialYear['end_date'];
        } else {
            $dates = explode(' to ', $r->date);
            $startDate = date('Y-m-d', strtotime($dates[0]));
            $endDate = date('Y-m-d', strtotime($dates[1]));
        }

        $organizations = [];
        if ($r->organization_id && is_array($r->organization_id)) {
            $organizations = $r->organization_id;
        };
        if (count($organizations) == 0) {
            $organizations[] = Helper::getAuthenticatedUser()->organization_id;
        }
        $currency = "org";
        if ($r->currency != "") {
            $currency = $r->currency;
        };

        if ($r->group_id) {
            $groups = Group::where(function ($query) use ($organizations, $r) {
                    $query->whereIn('organization_id', $organizations)
                          ->orWhereNull('organization_id');
                })
                ->whereIn('id', [1, 2]) // Filter for only group_id 1 or 2
                ->where('id', $r->group_id)
                ->select('id', 'name')
                ->with('children.children')
                ->get();
        } else {
            $groups = Group::where('status', 'active')
                ->whereNull('parent_group_id')
                ->where(function ($query) use ($organizations) {
                    $query->whereIn('organization_id', $organizations)
                          ->orWhereNull('organization_id');
                })
                ->whereIn('id', [1, 2]) // Filter for only group_id 1 or 2
                ->select('id', 'name')
                ->with('children.children')
                ->get();
        }        

        // Get Reserves & Surplus
        $profitLoss = Helper::getReservesSurplus($startDate, $endDate, $organizations, 'trialBalance', $currency, $r->cost_center_id);
// dd($groups);
        $data = Helper::getGroupsData($groups, $startDate, $endDate, $organizations, $currency, $r->cost_center_id);
        // dd($data);
        return response()->json(['currency' => $currency, 'data' => $data, 'type' => 'group', 'startDate' => date('d-M-Y', strtotime($startDate)), 'endDate' => date('d-M-Y', strtotime($endDate)), 'profitLoss' => $profitLoss, 'groups' => $groups]);
    }

    public function closeFy(Request $request)
    {
        DB::beginTransaction();

        try {
            // 1. Close the selected FY
            $financialYear = ErpFinancialYear::where('fy_status',ConstantHelper::FY_CURRENT_STATUS)->first();
            $financialYear->fy_status = ConstantHelper::FY_PREVIOUS_STATUS;
            $financialYear->fy_close = true;
            $financialYear->save();

            // 2. Set 'next' FY to 'current' (if exists)
            $currentYear = Carbon::now()->year;

            $nextFy = ErpFinancialYear::where('fy_status', ConstantHelper::FY_NEXT_STATUS)
                ->whereYear('start_date', $currentYear + 1)
                ->first();
            
            if ($nextFy) {
                $nextFy->fy_status = ConstantHelper::FY_CURRENT_STATUS;
                $nextFy->save();
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Financial year closed successfully.',
                'date_range' => $financialYear->start_date . ' to ' . $financialYear->end_date
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to close Financial Year.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'access_by' => 'required'
        ]);
    
        // $user = Helper::getAuthenticatedUser();
        // $organization = Organization::where('id', $user->organization_id)->first();
        // $organizationId = $organization?->id;
        // $companyId = $organization?->company_id;
        // $groupId = $organization?->group_id;
        $financialYear = ErpFinancialYear::where('fy_status', 'current')->first();
    
        if (!$financialYear) {
            return response()->json(['success' => false, 'message' => 'No current financial year found.'], 404);
        }
    
        // CloseCurrentFy::updateOrCreate(
        //     ['financial_year_id' => $financialYear->id], // condition
        //     [
        //         'access_by' => $request->access_by,
        //         'created_by' => Auth::id(),
        //         'organization_id' => $organizationId,
        //         'group_id' => $groupId,
        //         'company_id' => $companyId,
        //         // 'fy_close' => true // You can set this to true or 1 as needed
        //     ]
        // );
        $financialYear->access_by = $request->access_by;
        $financialYear->save();    
        return response()->json(['success' => true, 'message' => 'Close FY record saved successfully.']);
    }    

}
