<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Helper; 
use App\Helpers\EInvoiceHelper;  

class GstValidationController extends Controller
{
    public function validateGstNumber(Request $request)
    {
        $gstNumber = $request->input('gstNumber');
        $gstValidationResult = EInvoiceHelper::validateGstinName($gstNumber);
        return response()->json($gstValidationResult);
    }
}
