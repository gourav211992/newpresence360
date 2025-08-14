<?php

namespace App\Services;

use App\Helpers\ConstantHelper;
use App\Models\Ledger;
use App\Helpers\Helper;
use Exception;

class LedgerImportExportService
{
    public function checkRequiredFields($code, $name, $group,$code_type)
    {
        if($code_type=="Manual"){
        if (!$code || !$name || !$group) {
            throw new Exception("Code, Name & group are required.");
        }
    }else{
        if (!$code || !$name || !$group) {
            throw new Exception("Name & group are required.");
        }

    }
        return true;
    }

    public function checkLedgerUniqueness($field, $value, $user)
    {
        $organization = $user->organization;

        $groupId = $organization->group_id;
        $companyId = $organization->company_id;
        $organizationId = $organization->id;
        $existing = Ledger::
        where($field, $value)
        ->first();
        // $existing = Ledger::where($field, $value)
        //     ->where('organization_id', $organizationId)
        //     ->where('company_id', $companyId)
        //     ->where('group_id', $groupId)
        //     ->first();

        if ($existing) {
            throw new \Exception(ucfirst($field) . " already exists: {$value}");
        }

        return true;
    }

    public function processGroupData($group)
    {
        $groupIds = [];
        $groupLower = [];

        if (!empty($group)) {
            $groupParts = array_map('trim', explode(',', $group));
            $groupLower = array_map('strtolower', $groupParts);

            $existingGroups = Helper::getGroupsQuery()
                ->whereIn('name', $groupParts)
                ->pluck('name', 'id')
            ->toArray();

            $groupIds = array_keys($existingGroups);
            $foundNames = array_map('strtolower', array_values($existingGroups));
            $missingGroups = array_diff($groupLower, $foundNames);

            if (!empty($missingGroups)) {
                throw new \Exception("Group(s) not found");
            }
        }
        return [
            'groupIds' => $groupIds,
            'groupLower' => $groupLower,
        ];
    }

    public function mapStatus($status)
    {
        $normalized = strtolower(trim($status));
        if ($normalized == 'active') {
            return 1;
        } elseif ($normalized == 'in active' || $normalized == 'inactive') {
            return 0;
        }
        return null;
    }

    public function getGroupNamesByIds($groupIds)
    {
        if (empty($groupIds)) {
            return [];
        }

        if (!is_array($groupIds)) {
            $groupIds = json_decode($groupIds, true);
        }

        if (!is_array($groupIds)) {
            return [];
        }

        return Helper::getGroupsQuery()
            ->whereIn('id', $groupIds)
            ->pluck('name')
            ->unique()
            ->values()
            ->toArray();
    }


    public function mapStatusToBoolean($status)
    {
        $status = strtolower(trim($status ?? ''));

        if ($status == 'active') {
            return 1;
        } elseif ($status == 'in active' || $status == 'inactive') {
            return 0;
        }

        return null;
    }

    function getTdsSectionKeyFromLabel(string $label): ?string
    {
        $normalizedInput = strtolower(trim($label));

        $matched = array_filter(ConstantHelper::getTdsSections(), function ($v) use ($normalizedInput) {
            return strtolower(trim($v)) === $normalizedInput;
        });

        return $matched ? array_key_first($matched) : null;
    }

    function getTcsSectionKeyFromLabel(string $label): ?string
    {
        $normalizedInput = strtolower(trim($label));

        $matched = array_filter(ConstantHelper::getTcsSections(), function ($v) use ($normalizedInput) {
            return strtolower(trim($v)) === $normalizedInput;
        });

        return $matched ? array_key_first($matched) : null;
    }

    function getTaxTypeSectionKeyFromLabel(string $label): ?string
    {
        $normalizedInput = strtolower(trim($label));

        $matched = array_filter(ConstantHelper::getTaxTypes(), function ($v) use ($normalizedInput) {
            return strtolower(trim($v)) === $normalizedInput;
        });

        return $matched ? array_key_first($matched) : null;
    }
}
