<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ErpMultiPointPricingController extends Controller
{
    
    public function index(){

        return view('multi-point-pricing.fixed.index');
    }
}
