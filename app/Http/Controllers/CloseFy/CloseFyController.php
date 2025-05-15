<?php

namespace App\Http\Controllers\CloseFy;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\ErpFinancialYear;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CloseFyController extends Controller
{
    public function index(Request $request)
    {
        $fyearId = $request->fyear;
        $user = Helper::getAuthenticatedUser();
        $organizationId = $request->organization_id;

        $companies = $user->access_rights_org;
        $past_fyears = Helper::getAllPastFinancialYear();
        $financialYear = $fyearId ? ErpFinancialYear::find($fyearId) : null;
        $financialYearAuthUsers = $fyearId ? null : Helper::getFyAuthorizedUsers(date('Y-m-d'));

        if ($financialYear) {
            $organizationId = $financialYear->organization_id;
            $financialYear->access_by = $this->setFinancialYearAccessBy(
                $organizationId,
                $financialYear->lock_fy,
                $financialYear->access_by
            );
            $financialYear->save();

            $startYear = Carbon::parse($financialYear['start_date'])->format('Y');
            $endYearShort = Carbon::parse($financialYear['end_date'])->format('y');
        } else {
            $now = Carbon::now();
            $startYear = $now->format('Y');
            $endYearShort = $now->copy()->addYear()->format('y');
        }

        $authorized_users = $financialYearAuthUsers['authorized_users'] ?? ($financialYear ? $financialYear->authorizedUsers() : null);
        $current_range = $startYear . '-' . $endYearShort;
        $employees = Helper::getOrgWiseUserAndEmployees($organizationId);

        return view('close-fy.close-fy', compact(
            'companies', 'organizationId', 'past_fyears', 'financialYear', 'fyearId',
            'employees', 'current_range', 'authorized_users'
        ));
    }

    public function getFyInitialGroups(Request $r)
    {
        $financialYear = $r->fyear
            ? Helper::getAllPastFinancialYear()?->firstWhere('id', $r->fyear)
            : Helper::getFinancialYear(date('Y-m-d'));

        $startDate = $financialYear['start_date'];
        $endDate = $financialYear['end_date'];

        $organizations = $r->organization_id && is_array($r->organization_id)
            ? $r->organization_id
            : [Helper::getAuthenticatedUser()->organization_id];

        $currency = $r->currency ?: 'org';

        $groups = Group::query()
            ->where(function ($query) use ($organizations) {
                $query->whereIn('organization_id', $organizations)
                      ->orWhereNull('organization_id');
            })
            ->when($r->group_id, fn($q) => $q->whereIn('id', [1, 2])->where('id', $r->group_id))
            ->when(!$r->group_id, fn($q) => $q->where('status', 'active')->whereNull('parent_group_id')->whereIn('id', [1, 2]))
            ->select('id', 'name')
            ->with('children.children')
            ->get();

        $profitLoss = Helper::getReservesSurplus($startDate, $endDate, $organizations, 'trialBalance', $currency, $r->cost_center_id);
        $data = Helper::getGroupsData($groups, $startDate, $endDate, $organizations, $currency, $r->cost_center_id);

        return response()->json([
            'currency' => $currency,
            'data' => $data,
            'type' => 'group',
            'startDate' => date('d-M-Y', strtotime($startDate)),
            'endDate' => date('d-M-Y', strtotime($endDate)),
            'profitLoss' => $profitLoss,
            'groups' => $groups
        ]);
    }

    public function closeFy(Request $request)
    {
        try {
            $financialYear = $request->fyear ? ErpFinancialYear::find($request->fyear) : Helper::getCurrentFy();

            $financialYear->fy_status = ConstantHelper::FY_PREVIOUS_STATUS;
            $financialYear->fy_close = true;
            $financialYear->save();

            if (!$request->fyear) {
                $today = Carbon::today();
                $nextFy = ErpFinancialYear::where('fy_status', ConstantHelper::FY_NEXT_STATUS)
                    ->whereDate('start_date', '>=', $today)
                    ->whereDate('end_date', '>', $today)
                    ->orderBy('start_date', 'asc')
                    ->first();

                if ($nextFy) {
                    $nextFy->fy_status = ConstantHelper::FY_CURRENT_STATUS;
                    $nextFy->save();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Financial year closed successfully.',
                'date_range' => $financialYear->start_date . ' to ' . $financialYear->end_date
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to close Financial Year.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function setFinancialYearAccessBy($organizationId, $lock, $existingAccessBy)
    {
        $employees = Helper::getOrgWiseUserAndEmployees($organizationId);
        $existingAccessMap = collect($existingAccessBy)->mapWithKeys(fn($item) => [
            $item['user_id'] . '|' . $item['authenticable_type'] => $item
        ]);

        $accessBy = [];
        foreach ($employees as $employee) {
            $authUser = $employee->authUser();
            if (!$authUser) continue;

            $key = $authUser->id . '|' . $authUser->authenticable_type;
            $existing = $existingAccessMap[$key] ?? null;

            $accessBy[] = [
                'user_id' => $authUser->id,
                'authenticable_type' => $authUser->authenticable_type ?? null,
                'authorized' => $existing['authorized'] ?? true,
                'locked' => $existing['locked'] ?? ($lock == 1),
            ];
        }

        return $accessBy;
    }

    public function lockUnlockFy(Request $request)
    {
        $request->validate(['lock_fy' => 'required']);

        try {
            $financialYear = $request->fyear
                ? ErpFinancialYear::find($request->fyear)
                : Helper::getCurrentFy();

            $existingAccess = collect($financialYear->access_by ?? []);
            $updatedAccess = $existingAccess->map(fn($entry) => [
                'user_id' => (int) $entry['user_id'],
                'authenticable_type' => $entry['authenticable_type'] ?? null,
                'authorized' => $entry['authorized'],
                'locked' => $request->lock_fy,
            ])->toArray();

            $financialYear->access_by = $updatedAccess;
            $financialYear->lock_fy = $request->lock_fy;
            $financialYear->save();

            return response()->json([
                'success' => true,
                'message' => 'Financial year locked successfully.',
                'date_range' => $financialYear->start_date . ' to ' . $financialYear->end_date
            ]);
        } catch (\Throwable $e) {
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
        ]);

        $selectedUsers = collect($request->users)->keyBy(fn($item) => (int) $item['user_id']);

        $financialYear = $request->fyear
            ? ErpFinancialYear::find($request->fyear)
            : ErpFinancialYear::where('fy_status', 'current')->first();

        if (!$financialYear) {
            return response()->json(['success' => false, 'message' => 'No financial year found.'], 404);
        }

        $existingAccess = collect($financialYear->access_by ?? []);
        $updatedAccess = $existingAccess->isEmpty()
            ? $selectedUsers->map(fn($data, $userId) => [
                'user_id' => $userId,
                'authenticable_type' => $data['authenticable_type'] ?? null,
                'authorized' => true,
                'locked' => $financialYear->lock_fy == 1
            ])->values()->toArray()
            : $existingAccess->map(function ($entry) use ($selectedUsers, $financialYear) {
                $userId = (int) $entry['user_id'];
                $isSelected = $selectedUsers->has($userId);

                return [
                    'user_id' => $userId,
                    'authenticable_type' => $entry['authenticable_type'] ?? $selectedUsers[$userId]['authenticable_type'] ?? null,
                    'authorized' => $isSelected,
                    'locked' => $financialYear->lock_fy == 1
                ];
            })->toArray();

        $financialYear->access_by = $updatedAccess;
        $financialYear->save();

        return response()->json(['success' => true, 'message' => 'Close FY Authoriz users saved successfully.']);
    }

    public function deleteFyAuthorizedUser(Request $request)
    {
        $financialYear = $request->fyear
            ? ErpFinancialYear::find($request->fyear)
            : ErpFinancialYear::where('fy_status', 'current')->where('fy_close', ConstantHelper::FY_NOT_CLOSED_STATUS)->first();

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

        $minutes = 10080; // 1 week

        return response()->json([
            'success' => true,
            'message' => 'Financial Year cookie set successfully.'
        ])->withCookie(cookie('fyear_start_date', $request->start_date, $minutes))
          ->withCookie(cookie('fyear_end_date', $request->end_date, $minutes));
    }
}
