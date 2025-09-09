<?php

namespace App\Lib\Services;

use App\Models\OrganizationMenu;

class AccessRightsService
{
    private $user;
    public function fetchAuthData()
    {
        $this->user = request()->user();

        $cacheKey = 'oauth_data_'. $this->user->auth_user_id;
        return [];

        // return cache()->remember($cacheKey, 20 * 60, function () {
        //     $this->user->load([
        //         'organizations:id,name,alias,group_id,company_id',
        //         'organization:id,name,alias,group_id,company_id'
        //     ]);

        //     return [
        //         'oauthMenu' => $this->getMenus(), 
        //         'oauthPermissions' => $this->getPermissions(), 
        //         'oauthRoles' => $this->getRoles(), 
        //         'oauthUser' => $this->user,
        //         'oauthOrganizations' => $this->user->organizations
        //     ];
        // });
    }

    public function getRoles()
    {
        
        return $this->user->roles->pluck('id')->toArray();
    }


    private function getMenus()
    {
        $authMenus = [];

        if($this->user->user_type !== 'IAM-ROOT') {
            $groupId = $this->user->organization->group_id;
	        $authMenus = OrganizationMenu::with(['childMenus' => function ($query) use($groupId)  {
                $query->where('group_id', $groupId)
                    ->orderBy('sequence');

                $query->with(['childMenus' => function ($q) use ($groupId) {
                        $q->where('group_id', $groupId)
                            ->orderBy('sequence');
                	}]);
            	}])
                ->where('group_id', '=', $groupId)
                ->whereNull('parent_id')
                ->orderBy('sequence')->get();
        }

        return $authMenus;
    }

    private function getPermissions($authUser = null)
    {

        $authPermissions = [];

        if($this->user->user_type !== 'IAM-ROOT') {
            $authPermissions = $this->user->roles->flatMap(function ($role) {
                return $role->menuPermissions->pluck('alias');
            })->unique()->values()->all();
        }

        return $authPermissions;
    }
}
