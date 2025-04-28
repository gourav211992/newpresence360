<?php

namespace App\Traits;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Models\EmployeeBookMapping;
use App\Models\Organization;
use App\Models\OrganizationMenu;

trait DefaultGroupCompanyOrg
{
    /* Use carefully -> Only use for model containing following columns ->
        group_id INT
        organization_id INT
        company_id INT 
    */

    public function getCompanyIdAttribute() 
    {
        if (isset($this -> attributes['company_id'])) {
            return $this -> attributes['company_id'];
        } else {
            $authUser = Helper::getAuthenticatedUser();
            $organization = Organization::find($authUser -> organization_id);
            return $organization ?-> company_id;
        }
    }

    public function getOrganizationIdAttribute() 
    {
        if (isset($this -> attributes['organization_id'])) {
            return $this -> attributes['organization_id'];
        } else {
            $authUser = Helper::getAuthenticatedUser();
            $organization = Organization::find($authUser -> organization_id);
            return $organization ?-> id;
        }
    }

    public function scopeWithDefaultGroupCompanyOrg($query)
    {
        $authUser = Helper::getAuthenticatedUser();
        $authOrganization = Organization::find($authUser -> organization_id);
        $companyId = $authOrganization ?-> company_id;
        $groupId = $authOrganization ?-> group_id;
        $organizationId = $authOrganization ?-> id;
        $query -> where('group_id', $groupId) // Always compare group ID 
        ->where(function ($q) use ($companyId) {
            // Only compare company_id if it is not null in the database
            $q->whereNull('company_id')
              ->orWhere('company_id', $companyId);
        }) ->where(function ($q) use ($organizationId) {
            // Only compare organization_id if it is not null in the database
            $q->whereNull('organization_id')
              ->orWhere('organization_id', $organizationId);
        });
    }

    public function scopeBookViewAccess($query, $menuAlias, $bookIdColumn = "book_id")
    {
        $organizationMenu = OrganizationMenu::withDefaultGroupCompanyOrg() -> where([
            ['alias', $menuAlias]
        ]) -> first(); //Retrieve the corresponding org menu
        $authUser = Helper::getAuthenticatedUser();
        $employeeBookMapping = EmployeeBookMapping::where('service_menu_id', $organizationMenu ?-> serviceMenu -> id) 
        -> where('employee_id', $authUser -> id) -> first();
        //If Book mapping is specified and data is present
        if (isset($employeeBookMapping) && isset($employeeBookMapping -> other_book_ids)) {
            $createBookIds = isset($employeeBookMapping -> book_ids) ? $employeeBookMapping -> book_ids : [];
            $viewBookIds = isset($employeeBookMapping -> other_book_ids) ? $employeeBookMapping -> other_book_ids : [];
            //Concat both view and create access Ids
            $accessibleBookIds = array_merge($createBookIds, $viewBookIds);
            $query -> whereIn($bookIdColumn, $accessibleBookIds);
        }
    }

    public function scopeWithDraftListingLogic($query) 
    {
        $currentUser = Helper::getAuthenticatedUser();
        $query->where(function ($query) use ($currentUser) {
            $query->where('document_status', "!=", ConstantHelper::DRAFT) // Approved orders are visible to all
                  ->orWhere(function ($query) use ($currentUser) {
                      // Draft orders visible only to their creator
                      $query->where('created_by', $currentUser -> auth_user_id);
                  });
        });
    }
}
