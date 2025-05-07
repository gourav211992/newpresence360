<?php

namespace App\Http\Controllers\CloseFy;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\AuthUser;
use App\Models\Group;
use Illuminate\Http\Request;


class CloseFyController extends Controller
{

    public function index(Request $request)
    {
        $fyearId = $request->fyear_id;
        $user = Helper::getAuthenticatedUser();
        $userId = $user->id;
        $organizationId = $user->organization_id;
        $companies = Helper::getAuthenticatedUser()->access_rights_org;
        $fyears = Helper::getAllFinancialYear();
        // dd($fyears);
        $employees = Helper::getOrgWiseUserAndEmployees($organizationId);
        // dd($employees->authUser);
        return view('close-fy.close-fy',compact('companies', 'organizationId','fyears','fyearId','employees'));
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
}
