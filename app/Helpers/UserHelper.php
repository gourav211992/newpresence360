<?php

namespace App\Helpers;
use App\Helpers\Helper;
use App\Models\AuthUser;
use App\Models\Currency;
use App\Models\CurrencyExchange;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Group;
use App\Models\Organization;
use App\Models\OrganizationCompany;
use App\Models\OrganizationGroup;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UserHelper
{
    const AUTH_TYPE_USER = 'user';
    const AUTH_TYPE_EMPLOYEE = 'employee';
    const AUTH_TYPES = [self::AUTH_TYPE_USER, self::AUTH_TYPE_EMPLOYEE];
    /**/
    public static function getUserSubOrdinates(int $authUserId)
    {
        $data = new Collection();
        $authUser = AuthUser::select('id', 'name', 'authenticable_type', 'authenticable_id') -> find($authUserId);
        if (!($authUser)) {
            return array(
                'data' => $data,
                'status' => 'error',
                'message' => 'User not found'
            );
        }
        $data -> push($authUser);
        if ($authUser?->authenticable_type === self::AUTH_TYPE_EMPLOYEE) {
            $employeeIds = Employee::where('manager_id', $authUser -> authenticable_id)
                -> where('status', ConstantHelper::ACTIVE) -> get() -> pluck('id') -> toArray();
            $authEmployees = AuthUser::select('id', 'name') -> whereIn('authenticable_id', $employeeIds) -> get();
            foreach ($authEmployees as $authEmp) {
                $data -> push($authEmp);
            }
        }
        return array(
            'data' => $data,
            'status' => 'success',
            'message' => ''
        );
    }

    public static function getDepartments(int $authUserId)
    {
        $authUser = AuthUser::find($authUserId);
        $selectedDepartmentId = null;
        $departments = Department::where('organization_id', $authUser?->organization_id)
                        ->where('status', ConstantHelper::ACTIVE)->get();
        if ($authUser?->authenticable_type == self::AUTH_TYPE_EMPLOYEE) {
            $employee = Employee::find($authUser -> authenticable_id);
            $selectedDepartmentId = $employee ?-> department_id;
        }
        return array(
            'departments' => $departments,
            'selectedDepartmentId' => $selectedDepartmentId
        );
    }

}
