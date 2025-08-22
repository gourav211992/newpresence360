<?php

namespace App\Http\Controllers\CloseFy;

use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\ErpFinancialYear;
use App\Models\ErpFyMonth;
use App\Models\Group;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CloseFyController extends Controller
{
    public function index(Request $request)
    {
        $fyearId = $request->fyear;
        $user = Helper::getAuthenticatedUser();
        $organizationId = $request->organization_id;
        $companies = Helper::access_org();
        $past_fyears = Helper::getAllPastFinancialYear($organizationId);
        $financialYear = $fyearId ? ErpFinancialYear::where('organization_id',$organizationId)->find($fyearId) : null;
        // $financialYearAuthUsers = $fyearId ? null : Helper::getFyAuthorizedUsers(date('Y-m-d'));

        if ($financialYear) {
            // $organizationId = $financialYear->organization_id;
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

        $authorized_users = ($financialYear ? $financialYear->authorizedUsers() : null);
        $current_range = $startYear . '-' . $endYearShort;
        $employees = Helper::getOrgWiseUserAndEmployees($organizationId);
        // for testing
        Log::info('Authenticated User ID: ' . Helper::getAuthenticatedUser()->auth_user_id);
        Log::info('Financial Year: ' .$financialYear);

        return view('close-fy.close-fy', compact(
            'companies', 'organizationId', 'past_fyears', 'financialYear', 'fyearId',
            'employees', 'current_range', 'authorized_users'
        ));
    }

    public function monthFyIndex(Request $request)
{
    $fmonthId = $request->fmonth;
    $financialYearMonth = null;
    $user = Helper::getAuthenticatedUser();
    $organization = $user->organization;
    $group_id = $organization->group_id;
    $company_id = $organization->company_id;
    $organizationId = $request->organization_id;
    $companies =Helper::access_org();

    $current_fyear = Helper::getFinancialYear(date('Y-m-d'));
    $months = $this->showMonths($current_fyear['start_date'], $current_fyear['end_date']);

    if ($fmonthId) {
        // ----- Single Month Processing -----
        $fmonthDate = Carbon::parse($fmonthId)->startOfMonth();
        $startDate = $fmonthDate->toDateString();
        $endDate = $fmonthDate->copy()->endOfMonth()->toDateString();

        $financialYear = ErpFinancialYear::where('organization_id', $organizationId)
            ->where('start_date', '<=', $fmonthDate)
            ->where('end_date', '>=', $fmonthDate)
            ->first();

        $financialYearMonth = ErpFyMonth::where('fy_month', $fmonthId)
            ->where('fy_id', $financialYear?->id)
            ->first();

        $fyMonthData = [
            'access_by' => $this->setFinancialYearAccessBy(
                $organizationId,
                $financialYearMonth?->lock_fy ?? false,
                $financialYearMonth?->access_by ?? null
            ),
            'fy_month' => $fmonthId,
            'lock_fy' => $financialYearMonth?->lock_fy ?? false,
            'fy_id' => $financialYear?->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'organization_id' => $organizationId,
            'company_id' => $company_id,
            'group_id' => $group_id,
        ];

        if ($financialYearMonth) {
            $financialYearMonth->update($fyMonthData);
        } else {
            $financialYearMonth = ErpFyMonth::create($fyMonthData);
        }

        $startYear = Carbon::parse($financialYearMonth['start_date'])->format('Y');
        $endYearShort = Carbon::parse($financialYearMonth['end_date'])->format('y');
    } else {
        // ----- All Months in Current FY -----
        $financialYear = ErpFinancialYear::where('organization_id', $organizationId)
            ->where('start_date', '<=', $current_fyear['start_date'])
            ->where('end_date', '>=', $current_fyear['end_date'])
            ->first();

        if (!empty($months) && $financialYear) {
            foreach ($months as $month) {
                $monthValue = $month['value'];
                $fmonthDate = Carbon::parse($monthValue)->startOfMonth();
                $startDate = $fmonthDate->toDateString();
                $endDate = $fmonthDate->copy()->endOfMonth()->toDateString();

                $financialYearMonth = ErpFyMonth::where('fy_month', $monthValue)
                    ->where('fy_id', $financialYear->id)
                    ->first();

                $fyMonthData = [
                    'access_by' => $this->setFinancialYearAccessBy(
                        $organizationId,
                        $financialYearMonth?->lock_fy ?? false,
                        $financialYearMonth?->access_by ?? null
                    ),
                    'fy_month' => $monthValue,
                    'lock_fy' => $financialYearMonth?->lock_fy ?? false,
                    'fy_id' => $financialYear->id,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'organization_id' => $organizationId,
                    'company_id' => $company_id,
                    'group_id' => $group_id,
                ];

                if ($financialYearMonth) {
                    $financialYearMonth->update($fyMonthData);
                } else {
                    $financialYearMonth = ErpFyMonth::create($fyMonthData);
                }
            }
            // Use the last month for display
            $startYear = Carbon::parse($financialYearMonth['start_date'])->format('Y');
            $endYearShort = Carbon::parse($financialYearMonth['end_date'])->format('y');
        } else {
            $now = Carbon::now();
            $startYear = $now->format('Y');
            $endYearShort = $now->copy()->addYear()->format('y');
        }
    }

    $authorized_users = ($financialYearMonth ? $financialYearMonth->authorizedUsers() : null);
    $current_range = $startYear . '-' . $endYearShort;
    $employees = Helper::getOrgWiseUserAndEmployees($organizationId);

    return view('close-fy.close-month-fy', compact(
        'companies', 'organizationId', 'financialYear', 'fmonthId',
        'employees', 'current_range', 'authorized_users', 'current_fyear', 'months', 'financialYearMonth'
    ));
}


    private function showMonths($start, $end)
{
    $startDate = Carbon::parse($start);
    $endDate = Carbon::parse($end);
    $today = Carbon::now();

    // Always set end_date to the start of this month (so last month is the max included)
    $endDate = $today->copy()->startOfMonth()->subMonth();

    $months = [];
    $current = $endDate->copy();

    while ($current >= $startDate) {
        $months[] = [
            'value' => $current->format('Y-m'),
            'label' => $current->format('F Y'),
        ];
        $current->subMonth();
    }
    return $months;
}


    public function getFyInitialGroups(Request $r)
    {
        $financialSpan = null;
        $organizationId = $r->organization_id;
        if($r->fyear){
           $allFyears = Helper::getFinancialYears($organizationId);
            $financialSpan = $allFyears->firstWhere('id', $r->fyear);

            // if ($currentFy && isset($currentFy['range'])) {
            //     [$start, $end] = explode('-', $currentFy['range']);
            //     $nextStart = (int)$start + 1;
            //     $nextEnd = (int)$end + 1;
            //     $nextRange = $nextStart . '-' . str_pad($nextEnd % 100, 2, '0', STR_PAD_LEFT);

            //     $financialYear = $allFyears->first(function ($item) use ($nextRange) {
            //         return trim($item['range']) === trim($nextRange);
            //     });
            // }
        }elseif($r->fmonth){
            $financialSpan = [
                'start_date' => Carbon::parse($r->fmonth)->startOfMonth()->toDateString(),
                'end_date' => Carbon::parse($r->fmonth)->endOfMonth()->toDateString()
            ];         
        }
         $organizations = $r->organization_id && is_array($r->organization_id)
            ? $r->organization_id
            : [Helper::getAuthenticatedUser()->organization_id];

        $currency = $r->currency ?: 'org';
        $groups = Helper::getGroupsQuery($organizations)
            ->when($r->group_id, fn($q) => $q->whereIn('id', [1, 2])->where('id', $r->group_id))
            ->when(!$r->group_id, fn($q) => $q->whereNull('parent_group_id')->whereIn('id', [1, 2]))
            ->select('id', 'name')
            ->with('children.children')
            ->get();
        if ($financialSpan === null) {
            return response()->json([
                 'currency' => $currency,
                'data' => null,
                'type' => 'group',
                'startDate' => null,
                'endDate' => null,
                'profitLoss' => null,
                'groups' => $groups,
                'message' => 'No Data found related to financial year.'
            ]);
        }
        $startDate = $financialSpan['start_date'] ?? null;
        $endDate = $financialSpan['end_date'] ?? null;

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
            // $authUser = $employee->authUser();
            // if (!$authUser) continue;

            $key = $employee->id . '|' . $employee->authenticable_type;
            $existing = $existingAccessMap[$key] ?? null;

            $accessBy[] = [
                'user_id' => $employee->id,
                'authenticable_type' => $employee->authenticable_type ?? null,
                'authorized' => $existing['authorized'] ?? true,
                'locked' => $existing['locked'] ?? ($lock == 1),
            ];
        }

        return $accessBy;
    }

    public function lockUnlockFy(Request $request)
    {
        $request->validate(['lock_fy' => 'required']);

            // Determine target model (FY or FMonth)
            if ($request->fmonth_id) {
            $model = ErpFyMonth::find($request->fmonth_id);
            if (!$model) {
                return response()->json([
                    'success' => false,
                    'message' => 'No financial year month found.'
                ], 404);
            }
            if ($model && $request->lock_fy) { // Only check when locking (not unlocking)
                $selectedMonthValue = $model->fy_month;

                // Get all previous months based on fy_month string comparison
                $previousMonths = \App\Models\ErpFyMonth::where('fy_id', $model->fy_id)
                    ->where('fy_month', '<', $selectedMonthValue) // string comparison works for YYYY-MM
                    ->orderBy('fy_month', 'asc')
                    ->get();
                    $allPreviousLocked = $previousMonths->every(function($month) {
                        return $month->lock_fy == true;
                    });
                    if (!$allPreviousLocked) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Please lock all previous financial year months before locking the current one.'
                        ], 400);
                    }
                }
            } else {
                $model = $request->fyear
                    ? ErpFinancialYear::find($request->fyear)
                    : Helper::getCurrentFy();
                    
                if (!$model) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No financial year found.' 
                    ], 404);
                }
            }

            $dateRange = $model->start_date . ' to ' . $model->end_date;
            try {
                $existingAccess = collect($model->access_by ?? []);
                $updatedAccess = $existingAccess->map(fn($entry) => [
                    'user_id' => (int) $entry['user_id'],
                    'authenticable_type' => $entry['authenticable_type'] ?? null,
                    'authorized' => $entry['authorized'],
                    'locked' => $request->lock_fy,
                ])->toArray();

                $model->access_by = $updatedAccess;
                $model->lock_fy = $request->lock_fy;
                $model->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Financial year locked successfully.',
                    'date_range' => $dateRange
                ]);
            } catch (\Throwable $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to lock.',
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

        // Determine the target model
        if ($request->fmonth_id) {
            $model = ErpFyMonth::find($request->fmonth_id);
        } else {
            $model = $request->fyear
                ? \App\Models\ErpFinancialYear::find($request->fyear)
                : \App\Models\ErpFinancialYear::where('fy_status', 'current')->first();
        }

        if (!$model) {
            return response()->json(['success' => false, 'message' => $request->fmonth_id ? 'No financial year month found.' : 'No financial year found.'], 404);
        }

        $existingAccess = collect($model->access_by ?? []);
        $updatedAccess = $existingAccess->isEmpty()
            ? $selectedUsers->map(fn($data, $userId) => [
                'user_id' => $userId,
                'authenticable_type' => $data['authenticable_type'] ?? null,
                'authorized' => true,
                'locked' => $model->lock_fy == 1
            ])->values()->toArray()
            : $existingAccess->map(function ($entry) use ($selectedUsers, $model) {
                $userId = (int) $entry['user_id'];
                $isSelected = $selectedUsers->has($userId);

                return [
                    'user_id' => $userId,
                    'authenticable_type' => $entry['authenticable_type'] ?? $selectedUsers[$userId]['authenticable_type'] ?? null,
                    'authorized' => $isSelected,
                    'locked' => $model->lock_fy == 1
                ];
            })->toArray();

        $model->access_by = $updatedAccess;
        $model->save();

        return response()->json(['success' => true, 'message' => 'Authorization users saved successfully.']);
    }

    // not in use
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
