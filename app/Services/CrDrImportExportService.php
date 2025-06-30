<?php

namespace App\Services;

use App\Helpers\ConstantHelper;
use App\Models\Ledger;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Models\Group;
use App\Models\Voucher;
use Exception;

class CrDrImportExportService
{
    public function checkLedger(string $ledgerName): Ledger
    {
        $ledger = Ledger::withDefaultGroupCompanyOrg()
            ->where('name', $ledgerName)
            ->first();

        if (!$ledger) {
            throw new \Exception("Ledger '{$ledgerName}' does not exist.");
        }

        return $ledger;
    }

    public function checkLedgerGroup(string $groupName): Group
    {
        $group = Group::withDefaultGroupCompanyOrg()
            ->where('name', $groupName)
            ->first();

        if (!$group) {
            throw new \Exception("Ledger group '{$groupName}' does not exist.");
        }

        return $group;
    }

    public function checkLedgerParentGroup(string $parentGroupName): bool
    {
        $group = Group::withDefaultGroupCompanyOrg()
            ->where('name', $parentGroupName)
            ->first();

        if (!$group) {
            throw new \Exception("Parent group '{$parentGroupName}' does not exist.");
        }

        $hasLedgers = Ledger::withDefaultGroupCompanyOrg()
            ->where('ledger_parent_id', $group->id)
            ->exists();

        if (!$hasLedgers) {
            throw new \Exception("No ledgers found under parent group '{$parentGroupName}'.");
        }

        return true;
    }

    public function checkVoucheNoWithLedger(array $ledgerGroupIds, Ledger $ledger, ?string $voucherNo = null)
    {
        $accessibleLocations = InventoryHelper::getAccessibleLocations();
        $locationIds = $accessibleLocations->pluck('id')->toArray();

        $voucherQuery = Voucher::withDefaultGroupCompanyOrg()
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->whereIn('location', $locationIds)
            ->withWhereHas('items', function ($query) use ($ledger, $ledgerGroupIds) {
                $query->where('ledger_id', $ledger->id)
                    ->whereIn('ledger_parent_id', $ledgerGroupIds);
            });

        if ($voucherNo) {
            $voucherQuery->where('voucher_no', $voucherNo);
        }

        $exists = $voucherQuery->exists();

        if (!$exists) {
            throw new \Exception("Voucher with ledger '{$ledger->name}'" . ($voucherNo ? " and Document no '{$voucherNo}'" : "") . " does not exist.");
        }

        return true;
    }


    public function checkLedgerUniqueness($field, $value, $user)
    {
        $organization = $user->organization;

        $groupId = $organization->group_id;
        $companyId = $organization->company_id;
        $organizationId = $organization->id;
        $existing = Ledger::withDefaultGroupCompanyOrg()->
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
