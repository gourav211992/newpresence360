<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Helper;
use App\Traits\DefaultGroupCompanyOrg;


class Group extends Model
{
    protected $table = 'erp_groups';

    use HasFactory, DefaultGroupCompanyOrg;

    protected $fillable = [
        'name',
        'parent_group_id',
        'status',
        'group_id',
        'company_id',
        'organization_id'
    ];

    public function ledgers()
    {
        return $this->hasMany(Ledger::class, 'ledger_group_id', 'id')->where('status', 1);
    }

    // Relationship to get the parent group
    public function parent()
    {
        return $this->belongsTo(Group::class, 'id', 'parent_group_id')
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
}
