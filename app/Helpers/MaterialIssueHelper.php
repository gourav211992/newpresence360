<?php

namespace App\Helpers;
use App\Models\Bom;
use App\Models\Book;
use App\Models\Compliance;
use App\Models\Customer;
use App\Models\ErpAddress;
use App\Models\ErpAttribute;
use App\Models\ErpInvoiceItem;
use App\Models\ErpItemAttribute;
use App\Models\ErpProductionSlip;
use App\Models\ErpPslipItem;
use App\Models\ErpPslipItemDetail;
use App\Models\ErpSaleInvoice;
use App\Models\ErpSaleOrder;
use App\Models\ErpSoItem;
use App\Models\ErpStore;
use App\Models\Item;
use App\Models\ItemAttribute;
use App\Models\Organization;
use App\Models\OrganizationBookParameter;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class SaleModuleHelper  
{ 
    const STOCK_TRANSFER_ISSUE_TYPE = "Stock Transfer";
    const SUB_CONTRACTING_ISSUE_TYPE = "Sub Contracting";
    const CONSUMPTION_ISSUE_TYPE = "Consumption";
    const ISSUE_TYPES = [
        self::STOCK_TRANSFER_ISSUE_TYPE,
        self::SUB_CONTRACTING_ISSUE_TYPE,
        self::CONSUMPTION_ISSUE_TYPE
    ];

    

}