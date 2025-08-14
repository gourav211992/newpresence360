<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use App\Helpers\Helper;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Support\Str;

class Group extends Model
{
    protected $table = 'erp_groups';

    use HasFactory;

    protected $fillable = [
        'name',
        'parent_group_id',
        'status',
        'group_id',
        'company_id',
        'organization_id',
        'prefix',
    ];
    
    public function scopeWithDefaultGroupCompanyOrg(Builder $query)
    {
        $authUser = Helper::getAuthenticatedUser();
        $authOrganization = Organization::find($authUser -> organization_id);
        $companyId = $authOrganization ?-> company_id;
        $groupId = $authOrganization ?-> group_id;
        $organizationId = $authOrganization ?-> id;
         return $query->where('group_id', $groupId) // Always compare group ID 
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

    public function ledgers()
    {
        return $this->hasMany(Ledger::class, 'ledger_group_id', 'id')->where('status', 1);
    }

    // Relationship to get the parent group
    public function parent()
    {
        return $this->belongsTo(Group::class, 'parent_group_id', 'id')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->withDefaultGroupCompanyOrg();
                })->orWhere('edit', 0);
            });
    }

    // Relationship to get child groups
    public function children()
    {
        return $this->hasMany(Group::class, 'parent_group_id', 'id')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->withDefaultGroupCompanyOrg();
                })->orWhere('edit', 0);
            });
    }

    public function getAllLastLevelGroupIds(&$lastLevelIds = [])
    {
        if ($this->children->isEmpty()) {
            $lastLevelIds[] = $this->id;
        } else {
            foreach ($this->children as $child) {
                $child->getAllLastLevelGroupIds($lastLevelIds);
            }
        }

        return $lastLevelIds;
    }



    // Optionally, if you want to get all item details related to this group
    public function itemDetails()
    {
        return $this->hasManyThrough(ItemDetail::class, Ledger::class, 'ledger_group_id', 'ledger_id', 'id', 'id');
    }

    public function getAllChildIds(&$ids = [])
    {
        foreach ($this->children as $child) {
            $ids[] = $child->id;
            $child->getAllChildIds($ids);
        }
        return $ids;
    }

    public function getAllParentIds()
    {
        $parentIds = [];
        $parent = $this->parent;

        while ($parent) {
            $parentIds[] = $parent->id;
            $parent = $parent->parent;
        }

        return $parentIds;
    }


    public function getGroupLedgerSummary()
    {
        $ledgers = $this->ledgers;

        $totalCredit = $ledgers->sum('credit_amt');
        $totalDebit = $ledgers->sum('debit_amt');

        // Fetch all item details related to the ledgers in this group
        $itemDetails = ItemDetail::whereIn('ledger_id', $ledgers->pluck('id'))->get();

        // Calculate total credits and debits from item details
        $totalItemCredit = $itemDetails->sum('credit_amt');
        $totalItemDebit = $itemDetails->sum('debit_amt');

        // Assuming first closing is the opening balance
        $firstClosing = $ledgers->first()->created_at ?? null;

        // Calculate opening balance (if needed, based on your logic)
        $openingBalance = $this->calculateOpeningBalance($firstClosing);

        // Closing balance calculation
        $closingBalance = $openingBalance + $totalCredit + $totalItemCredit - $totalDebit - $totalItemDebit;

        return [
            'total_credit' => $totalCredit + $totalItemCredit,
            'total_debit' => $totalDebit + $totalItemDebit,
            'first_closing' => $firstClosing,
            'opening_balance' => $openingBalance,
            'closing_balance' => $closingBalance,
            'ledgers' => $ledgers,
            'item_details' => $itemDetails,
        ];
    }
    public static function generateuniquePrefix($name)
{
    // Clean and prepare name
    $cleanedName = preg_replace('/[^a-zA-Z\s]/', '', $name); // Keep only letters and spaces
    $cleanedName = strtoupper(trim($cleanedName));
    $words = preg_split('/\s+/', $cleanedName); // Split by spaces

    if (count($words) === 0) return null;

    $used = Helper::getGroupsQuery()->pluck('prefix')->map(function ($p) {
        return strtoupper($p);
    })->toArray();

    $candidates = [];

    // 1. If 2+ words, try 2-letter (first letters of first two words)
    if (count($words) >= 2) {
        $prefix2 = substr($words[0], 0, 1) . substr($words[1], 0, 1);
        $candidates[] = $prefix2;

        // 2. If 3+ words, try 3-letter (first letters of all three)
        if (count($words) >= 3) {
            $prefix3 = substr($words[0], 0, 1) . substr($words[1], 0, 1) . substr($words[2], 0, 1);
            $candidates[] = $prefix3;
        }
    }

    // 3. If only one word or more variations needed
    $base = str_replace(' ', '', $cleanedName); // Combine name into single string
    $baseLength = strlen($base);

    // Try all 2 and 3 letter combinations starting with first letter
    for ($i = 1; $i < $baseLength; $i++) {
        $prefix2 = $base[0] . $base[$i];
        if (strlen($prefix2) === 2) {
            $candidates[] = $prefix2;
        }

        for ($j = $i + 1; $j < $baseLength; $j++) {
            $prefix3 = $base[0] . $base[$i] . $base[$j];
            if (strlen($prefix3) === 3) {
                $candidates[] = $prefix3;
            }
        }
    }

    // Remove duplicates and check against used
    $candidates = array_unique($candidates);

    foreach ($candidates as $candidate) {
        if (!in_array($candidate, $used)) {
            return $candidate;
        }
    }

    return null; // No available prefix
}



    public static function getPrefix($id)
    {
        $group = Group::find($id);
        if (!isset($group->prefix)) {
            $name = $group->name;
            return Group::generateuniquePrefix($name);
        } else return $group->prefix;
    }
    public static function updatePrefix($ledger_id, $prefix)
    {
        $ledger = Ledger::find($ledger_id);

        if (!$ledger || empty($ledger->ledger_group_id)) {
            return;
        }

        $groups = json_decode($ledger->ledger_group_id, true);

        if (!is_array($groups) || count($groups) === 0) {
            return;
        }

        $group_id = (int) $groups[0];
        $group = Group::find($group_id);


        if ($group->prefix == null) {
            $group->prefix = $prefix;
            $group->save();
        }
    }
}
