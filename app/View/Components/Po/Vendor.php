<?php

namespace App\View\Components\Po;

use App\Helpers\ConstantHelper;
use App\Helpers\ItemHelper;
use App\Models\Vendor as ModelsVendor;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Vendor extends Component
{
    public $row;
    public $vendors;
    public $firstVendorId;
    public bool $defaultOption;
    /**
     * Create a new component instance.
     */
    public function __construct($row, $documentDate, bool $defaultOption = false)
    {
        $approvedVendorIds = ItemHelper::getItemApprovedVendors($row?->item_id, $documentDate) ?? [];
        if (count($approvedVendorIds)) {
            $this->vendors = ModelsVendor::whereIn('id', $approvedVendorIds)->get();
            $this->firstVendorId = $row?->vendor_id ?? $this->vendors?->first()?->id;
            $this->defaultOption = false;
        } else {
            $this->vendors = ModelsVendor::withDefaultGroupCompanyOrg()
                ->where('status', ConstantHelper::ACTIVE)
                ->get();
            $this->firstVendorId = $row?->vendor_id;
            $this->defaultOption = true;
        }
        $this->row = $row;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.po.vendor',
        [
            'row' => $this->row,
            'vendors' => $this->vendors,
            'firstVendorId' => $this->firstVendorId,
            'defaultOption' => $this->defaultOption
        ]);
    }
}
