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
        $fyearId = $request->fyear;
        $user = Helper::getAuthenticatedUser();

        $userId = $user->id;
        $organizationId = $user->organization_id;

        $companies = $user->access_rights_org;
        $past_fyears = Helper::getAllPastFinancialYear();
        $financialYear = Helper::getFinancialYear(date('Y-m-d'));
        $financialYearAuthUsers = null;
        $currentFy = null;

        if ($fyearId == "") {
            $financialYearAuthUsers = Helper::getFyAuthorizedUsers(date('Y-m-d'));
            $financialYear = Helper::getFinancialYear(date('Y-m-d'));
        } else {
            $currentFy = $past_fyears?->firstWhere('id', $fyearId);
            $financialYear = $currentFy; // ✅ Assign selected FY to $financialYear
        }

        $authorized_users = $financialYearAuthUsers['authorized_users'] ?? $currentFy['authorized_users'] ?? null;
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
        return view('close-fy.close-fy',compact('companies', 'organizationId','past_fyears','currentFy','fyearId','employees','current_range','authorized_users','financialYear'));
    }

    public function getFyInitialGroups(Request $r)
    {
        if ($r->fyear == "") {
            $financialYear = Helper::getFinancialYear(date('Y-m-d'));
            // dd($financialYear)
            $startDate = $financialYear['start_date'];
            $endDate = $financialYear['end_date'];
        }else {
            $fyears = Helper::getAllPastFinancialYear();
            $currentFy = $fyears?->firstWhere('id', $r->fyear);
            $startDate = $currentFy['start_date'];
            $endDate = $currentFy['end_date'];

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
        $data = Helper::getGroupsData($groups, $startDate, $endDate, $organizations, $currency, $r->cost_center_id);
        return response()->json(['currency' => $currency, 'data' => $data, 'type' => 'group', 'startDate' => date('d-M-Y', strtotime($startDate)), 'endDate' => date('d-M-Y', strtotime($endDate)), 'profitLoss' => $profitLoss, 'groups' => $groups]);
    }

    public function closeFy(Request $request)
    {
        DB::beginTransaction();

        try {

            if($request->fyear)
            {
                $financialYear = ErpFinancialYear::find($request->fyear);
                $organizationId = $financialYear->organization_id;
                $employees = Helper::getOrgWiseUserAndEmployees($organizationId);

                $accessBy = [];

                foreach ($employees as $employee) {
                    $authUser = $employee->authUser();
                    if ($authUser) {
                        $accessBy[] = [
                            'user_id' => $authUser->id,
                            'authenticable_type' => $authUser->authenticable_type?? null,
                            'authorized' => true,
                        ];
                    }
                }
                $financialYear->fy_status = ConstantHelper::FY_PREVIOUS_STATUS;
                $financialYear->fy_close = true;
                $financialYear->access_by = $accessBy;
                $financialYear->save();
            }
            else{
                    // 1. Close the selected FY
                $financialYear = Helper::getCurrentFy();
                // dd($financialYear);
                $financialYear->fy_status = ConstantHelper::FY_PREVIOUS_STATUS;
                $financialYear->fy_close = true;
                $financialYear->save();
                // 2. Set next FY to current if conditions are met
                $today = Carbon::today();
                $currentYear = $today->year;
                $nextYear = $currentYear + 1;

                $nextFy = ErpFinancialYear::where('fy_status', ConstantHelper::FY_NEXT_STATUS)
                // ->where('fy_close', ConstantHelper::FY_NOT_CLOSED_STATUS)
                ->whereDate('start_date', '>=', $today)
                ->whereDate('end_date', '>', $today)
                ->orderBy('start_date', 'asc') // optional: get the nearest future FY
                ->first();

                if ($nextFy) {
                    $nextFy->fy_status = ConstantHelper::FY_CURRENT_STATUS;
                    $nextFy->save();
                }
            }
            // dd($request->all(), $request->fyear,                $financialYear);

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

    public function lockUnlockFy(Request $request)
    {
        $request->validate([
            'lock_fy' => 'required'
        ]);

        DB::beginTransaction();

        try {
            // 1. Close the selected FY
            $financialYear = Helper::getCurrentFy();
            if($request->fyear) // need confirmation
            {
                $financialYear = ErpFinancialYear::find($request->fyear);
            }
            // $financialYear->fy_status = ConstantHelper::FY_PREVIOUS_STATUS;
            $financialYear->lock_fy = $request->lock_fy;
            $financialYear->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Financial year locked successfully.',
                'date_range' => $financialYear->start_date . ' to ' . $financialYear->end_date
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to Lock Financial Year.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateFyAuthorizedUser(Request $request)
    {
        $request->validate([
            'users' => 'required|array',
            'users.*.user_id' => 'required',
            // 'users.*.authenticable_type' => 'required',
        ]);

        $selectedUsers = collect($request->users)
        ->keyBy(fn($item) => (int) $item['user_id']);

        $financialYear = ErpFinancialYear::where('fy_status', 'current')
            ->first();

        if ($request->fyear) {
            $financialYear = ErpFinancialYear::find($request->fyear);
        }

        if (!$financialYear) {
            return response()->json(['success' => false, 'message' => 'No financial year found.'], 404);
        }
        $existingAccess = collect($financialYear->access_by ?? []);

        $updatedAccess = $existingAccess->map(function ($entry) use ($selectedUsers) {
            $userId = (int) $entry['user_id'];
            $isSelected = $selectedUsers->has($userId);

            return [
                'user_id' => $userId,
                'authenticable_type' => $entry['authenticable_type'] ?? $selectedUsers[$userId]['authenticable_type'] ?? null,
                'authorized' => $isSelected,
            ];
        })->toArray();

        $financialYear->access_by = $updatedAccess;

        $financialYear->save();
        return response()->json(['success' => true, 'message' => 'Close FY Authoriz users saved successfully.']);
    }

    public function deleteFyAuthorizedUser(Request $request)
    {

        $financialYear = ErpFinancialYear::where('fy_status', 'current')
        ->where('fy_close', ConstantHelper::FY_NOT_CLOSED_STATUS)
        ->first();

        if($request->fyear)
        {
            $financialYear = ErpFinancialYear::find($request->fyear);
        }

        if (!$financialYear) {
            return response()->json(['success' => false, 'message' => 'No current financial year found.'], 404);
        }

        $financialYear->access_by = null;
        $financialYear->save();
        return response()->json(['success' => true, 'message' => 'Authorized user removed successfully.']);
    }

    public function storeFySession(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'fyearId' => 'required'
        ]);

        session([
            'fyear_start_date' => $request->start_date,
            'fyear_end_date' => $request->end_date,
            'fyear_id' => $request->fyearId,
        ]);
        return response()->json(['success' => true, 'message' => 'Financial Year session set successfully.']);
    }

}
