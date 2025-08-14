<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PurchaseOrderExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;
    
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function query()
    {
        if (!empty(Auth::guard('web')->user())) {
            $organization_id = Auth::guard('web')->user()->organization_id;
            $user_id = Auth::guard('web')->user()->id;
            $type = 1;
            $utype = 'user';
        }
        elseif (!empty(Auth::guard('web2')->user())) 
        {
            $organization_id = Auth::guard('web2')->user()->organization_id;
            $user_id = Auth::guard('web2')->user()->id;
            $type = 2;
            $utype ='employee';
        }
        else
        {
            $organization_id = 1;
            $user_id = 1;
            $type = 1;
            $utype = 'user';
        }
        return PurchaseOrder::query()
        ->with('po_items.item', 'po_items.item.category', 'po_items.item.subCategory', 'vendor')
        ->where('organization_id', $organization_id)
        // Legals created by the user
        ->where('user_id', $user_id)
        ->where('type', $type);
        
        // Apply the date filter only if both start and end dates are provided
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }
    }

    public function headings(): array
    {
        $headings = [
            'ID', // ID
            'PO No', // PO No
            'PO Date', // PO Date
            'Vendor', // Vendor
            //'Vendor Rating', // Vendor rating
            'Status', // Status
            'Po Amount',     // Po Amount
            'Created At', // Created At
            // Add more fields as needed
            'Category',      // Category
            'Sub Category',  // Sub Category
            'Item type',     // Item type
            //'Sub type',    // Sub type (optional)
            'Item',          // Item Name
            'Item Code',     // Item Code
            'Po Qty',        // Order Quantity
            'Rec Qty',     // Received Qty (optional)
            'Bal Qty',     // Balance Qty (optional)
            'Rate',          // Rate
        ];

        return $headings;
    }

    public function map($purchaseOrder): array
    {   
        $mappedData = [];
        // Loop through each po_item and append it to the mappedData array
        foreach ($purchaseOrder->po_items as $poItem) {

            $vendorName = $purchaseOrder->vendor ? $purchaseOrder->vendor->company_name : 'N/A';
            $categoryName = $poItem->item->category ? $poItem->item->category->name : 'N/A';
            $subCategoryName = $poItem->item->subCategory ? $poItem->item->subCategory->name : 'N/A';
            $balQty = $poItem->order_qty - $poItem->grn_qty;

            // Add the dynamic po_items fields for each item
            $mappedData[] = [
                $purchaseOrder->id,
                $purchaseOrder->document_number,
                $purchaseOrder->document_date,
                $vendorName,
                //$purchaseOrder->vendor->rating,
                $purchaseOrder->document_status,
                $purchaseOrder->total_item_value,
                $purchaseOrder->created_at->format('Y-m-d H:i:s'),
                $categoryName, // Category
                $subCategoryName, // Sub Category
                $poItem->item->type,
                //$poItem->item->subType, //subtype
                $poItem->item->item_name,
                $poItem->item->item_code,
                $poItem->order_qty,
                $poItem->grn_qty,
                $balQty,
                $poItem->rate,
            ];
        }
        //dd($mappedData);
        // Flatten the array in case po_items are nested within an array
        return $mappedData;
        
    }


}
