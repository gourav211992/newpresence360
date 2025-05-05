<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Helper;

class SpliteHandlerController extends Controller
{
    public function index(){
        return view('splite-merger.index');
    }

    public function addSplite(){
        return view('splite-merger.add_splite');
    }
}
