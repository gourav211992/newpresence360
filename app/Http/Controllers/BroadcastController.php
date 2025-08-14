<?php
namespace App\Http\Controllers;

use Illuminate\Broadcasting\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Auth;

class BroadcastController extends Controller
{
    /**
     * Authenticate a user for a given channel.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function authenticate(Request $request)
    {
        return Broadcast::auth($request);
    }
}
