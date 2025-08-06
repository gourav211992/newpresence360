<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use App\Helpers\Helper;
use App\Models\Organization;

class DefaultGroupCompanyOrgScope implements Scope
{


    public function apply(Builder $builder, Model $model)
    {
        // 🔒 Check model property flag (per model class)
        if (property_exists($model, 'disableDefaultGroupCompanyOrgScope') && $model->disableDefaultGroupCompanyOrgScope === true) {
            return;
        }
        // Skip if flag set (e.g., per-query disable or scope applied manually)
        if (isset($builder->withoutDefaultGroupCompanyOrgScope) && $builder->withoutDefaultGroupCompanyOrgScope === true) {
            return;
        }
        
        // Apply default scope
        $tableName = $model->getTable();
        $authUser = Helper::getAuthenticatedUser();
        $authOrganization = Organization::find($authUser -> organization_id);
        $companyId = $authOrganization ?-> company_id;
        $groupId = $authOrganization ?-> group_id;
        $organizationId = $authOrganization ?-> id;
        $builder->where($tableName.'.group_id', $groupId) // Always compare group ID 
        ->where(function ($q) use ($companyId, $tableName) {
            // Only compare company_id if it is not null in the database
            $q->whereNull($tableName.'.company_id')
              ->orWhere($tableName.'.company_id', $companyId);
        }) ->where(function ($q) use ($organizationId, $tableName) {
            // Only compare organization_id if it is not null in the database
            $q->whereNull($tableName.'.organization_id')
              ->orWhere($tableName.'.organization_id', $organizationId);
        });
    }
}
