<?php
namespace App\View\Composers;

use Illuminate\View\View;
use App\Models\Vendor;
use App\Helpers\ConstantHelper;

class VendorMenuComposer
{
    public function compose(View $view)
    {
        $vendorId = request()->cookie('vendor_id');
        $vendor = Vendor::find($vendorId);
        $vendorSubType = $vendor?->vendor_sub_type ?? ConstantHelper::REGULAR;
        
        $view->with([
            'vendorSubType'   => $vendorSubType,
            'is_regular'      => $vendorSubType === ConstantHelper::REGULAR,
            'is_transporter'  => $vendorSubType === ConstantHelper::TRANSPORTER,
        ]);
    }
}
