<?php
namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Http\Requests\MaterialReceiptRequest;
use App\Http\Requests\EditMaterialReceiptRequest;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\MrnHeader;
use App\Models\MrnDetail;
use App\Models\ErpAddress;

class ExpenseService
{
    public function createMrnHeader(MaterialReceiptRequest $request)
    {
        
    }

    public function updateMrnHeader($id, EditMaterialReceiptRequest $request)
    {
        
    }
}
