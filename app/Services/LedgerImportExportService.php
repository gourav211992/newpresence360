<?php

namespace App\Services;

use App\Helpers\ConstantHelper;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\ItemSpecification;
use App\Models\AlternateUOM;
use App\Models\AttributeGroup;
use App\Models\Attribute;
use App\Models\ProductSpecification;
use App\Models\ProductSpecificationDetail;
use App\Models\Category;
use App\Models\Ledger;
use App\Models\Group;
use App\Models\Unit;
use App\Models\Hsn;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Currency;
use App\Models\Employee;
use App\Models\PaymentTerm;
use App\Models\SubType;
use App\Models\UploadItemMaster;
use App\Models\OrganizationType;
use App\Helpers\EInvoiceHelper;
use App\Helpers\GstnHelper;
use App\Helpers\Helper;
use Illuminate\Support\Facades\Log;
use Exception;

class LedgerImportExportService
{
    public function checkRequiredFields($code, $name, $group)
    {
        if (!$code || !$name || !$group) {
            throw new Exception("Code, Name & group are required.");
        }
        return true;
    }

    public function checkLedgerUniqueness($field, $value)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;

        $groupId = $organization->group_id;
        $companyId = $organization->company_id;
        $organizationId = $organization->id;

        $existing = Ledger::where($field, $value)
            ->where('organization_id', $organizationId)
            ->where('company_id', $companyId)
            ->where('group_id', $groupId)
            ->first();

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

            $groupIds = Helper::getGroupsQuery()
                ->whereIn('name', $groupParts) // use trimmed names
                ->pluck('id')
                ->toArray();
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
        return null; // fallback in case of invalid value
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
            ->values() // reset array keys
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
