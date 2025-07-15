<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use App\Models\State;
use App\Models\Country;
use App\Models\City;
use App\Models\PincodeMaster;


class GstnHelper
{
   // Add these methods to your GstnHelper class

public function validateStateCode($stateId, $gstStateCode)
{
    try {
        $state = State::find($stateId);
        
        if (!$state) {
            return [
                'valid' => false,
                'message' => 'State not found.'
            ];
        }

        return [
            'valid' => $state->state_code == $gstStateCode,
            'message' => 'State does not match the registered state in GSTIN details.'
        ];
    } catch (\Exception $e) {
        return [
            'valid' => false,
            'message' => 'Error validating state'
        ];
    }
}

}
