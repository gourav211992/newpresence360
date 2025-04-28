<?php

namespace App\Rules;

use App\Models\ErpSubStore;
use Closure;
use Illuminate\Contracts\Validation\Rule;

class UniqueSubStoreCode implements Rule
{
   protected $storeId;
    protected $groupId;

    public function __construct($subStoreId, $groupId)
    {
        $this->storeId = $subStoreId;
        $this->groupId = $groupId;
    }

    public function passes($attribute, $value)
    {
        return !ErpSubStore::where('code', $value)
            ->whereHas('parents', function ($query) {
                $query->where('group_id', $this->groupId);
            })
            ->when($this->storeId, function ($query) {
                $query->where('id', '!=', $this->storeId); // Ignore current record on update
            })
            ->exists();
    }

    public function message()
    {
        return 'The sub location alias must be unique.';
    }
}
