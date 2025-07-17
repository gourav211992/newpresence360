<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Deletable;
use App\Helpers\ConstantHelper;


class LedgerHistory extends Model
{
    protected $table = 'erp_ledgers_history';

    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg;

    protected $fillable = [
        'prefix',
        'ledger_group_id',
        'code',
        'name',
        'cost_center_id',
        'status',
        'group_id',
        'company_id',
        'book_id',
        'organization_id',
        'ledger_code_type',
        'tax_type',
        'tax_percentage',
        'tds_section',
        'tds_percentage',
        'tcs_section',
        'tcs_percentage',
        'approval_level',
        'document_status',
        'revision_number',
        'created_by'
    ];
    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }
    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }
    
    public function customer()
    {
        return $this->hasOne(Customer::class);
    }
    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }
    public function deleteWithReferences($referenceTables)
    {
        $referencedTables = [];

        // Loop through reference tables and check if the ledger is being used
        foreach ($referenceTables as $table => $columns) {
            foreach ($columns as $column) {
                $exists = DB::table($table)->where($column, $this->id)->exists();

                if ($exists) {
                    // If reference exists, prevent deletion and add table to list
                    $referencedTables[] = $table;
                }
            }
        }

        // If references exist, return status as false with message
        if (count($referencedTables) > 0) {
            return [
                'status' => false,
                'message' => 'Record cannot be deleted because it is already in use.',
                'referenced_tables' => $referencedTables
            ];
        }

        // If no references, proceed with soft delete
        $this->delete();

        return [
            'status' => true,
            'message' => 'Item deleted successfully.'
        ];
    }

    public function group()
    {
        $groupIds = json_decode($this->ledger_group_id, true);

        if (is_array($groupIds)) {
            return Group::whereIn('id', $groupIds)->get();
        }

        if (is_numeric($this->ledger_group_id)) {
            return $this->belongsTo(Group::class, 'ledger_group_id')->getResults();
        }
        return null;
    }

    public function groups()
    {
        $groupIds = json_decode($this->ledger_group_id, true);
        if (is_array($groupIds)) {
            return Group::whereIn('id', $groupIds)->get();
        }
        if (is_numeric($this->ledger_group_id)) {
            $group = Group::find($this->ledger_group_id);
            return $group ? collect([$group]) : collect();
        }
        return collect();
    }

    public function parent()
    {
        return $this->belongsTo(Ledger::class, 'parent_ledger_id')->where('status', 1);
    }
    public function getLedgerGroupIdArray()
{
    // Ensure ledger_group_id is always treated as an array
    $ledgerGroupId = is_array($this->ledger_group_id)
        ? $this->ledger_group_id  // Keep as is if it's already an array
        : [$this->ledger_group_id]; // Convert single value to array

    // Convert all elements to integers (in case they are strings)
    return array_map('intval', $ledgerGroupId);
}


    public function detatils0()
    {
    return $this->hasMany(ItemDetail::class, 'ledger_group_id', 'ledger_parent_id');

    }
    public function details_ledger()
{
    $groupIds = json_decode($this->ledger_group_id, true);


    // Ensure $groupIds is always an array
    if (!is_array($groupIds) || empty($groupIds)) {
        $groupIds = []; // Default to an empty array if null or invalid
    }

    $groupIds = array_map('intval', $groupIds); // Convert all values to integers

    return $this->hasMany(ItemDetail::class, 'ledger_id', 'id')
        ->whereIn('ledger_parent_id', $groupIds);
}

public function details()
{
    return $this->hasMany(ItemDetail::class, 'ledger_id')
                ->whereHas('voucher', function ($query) {
                    $query->whereIn('approvalStatus',ConstantHelper::DOCUMENT_STATUS_APPROVED);
                });
}

}
