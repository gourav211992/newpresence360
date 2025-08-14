<?php 
namespace App\Services;

use Illuminate\Http\Request;

use App\Models\MrnHeader;
use App\Models\MrnDetail;
use App\Models\MrnExtraAmount;
use App\Models\MrnItemLocation;

use App\Models\Item;
use App\Models\PoItem;
use App\Models\VendorAsnItem;
use App\Models\GateEntryDetail;
use App\Models\ErpSoJobWorkItem;
use App\Models\JobOrder\JoProduct;

use App\Helpers\ItemHelper;
use App\Helpers\ConstantHelper;
use App\Helpers\InventoryHelper;
use App\Helpers\InventoryHelperV2;

class MrnCreateService
{
    

    private static function errorResponse($message, $inputQty)
    {
        return [
            "code" => "500",
            "status" => "error",
            "order_qty" => $inputQty,
            "message" => $message,
        ];

    }

    private static function successResponse($response, $inputQty)
    {
        return [
            "code" => "200",
            "status" => "success",
            "order_qty" => $inputQty,
            "message" => $response,
        ];
    }
}