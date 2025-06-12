<?php

namespace App\Http\Controllers\Supplier;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    # Supplier Dashboard
    public function index()
    {
        return view('supplier.dashboard');
    }

    # Vendor On Change
    public function onChangeVendor(Request $request)
    {
        $vendorId = $request->vendor_id 
        ?? $request->cookie('vendor_id') 
        ?? auth()->user()->auth_user?->vendor_portals[0]->vendor_id 
        ?? null;
        if ($vendorId) {
            return response()->json([
                'data' => ['vendor_id' => $vendorId],
                'status' => 200,
                'message' => 'updated!'
            ])->cookie('vendor_id', $vendorId, 60 * 24 * 30); // Cookie valid for 30 days
        }
        return response()->json([
            'data' => ['vendor_id' => null],
            'status' => 400,
            'message' => 'Vendor ID not found!'
        ]);
    }
}
