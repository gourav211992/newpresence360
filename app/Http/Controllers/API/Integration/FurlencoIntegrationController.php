<?php

namespace App\Http\Controllers\API\Integration;

use App\Http\Controllers\Controller;
use App\Services\Integration\ConsigneeService;
use App\Http\Requests\Integration\ConsigneeRequest;

class FurlencoController extends Controller
{
    public function __construct(private ConsigneeService $service) {}

    public function consigneeStoreOrUpdate(ConsigneeRequest $request)
    {
        $results = $this->service->storeOrUpdate(
            $request->organization_id,
            $request->consignees
        );

        return [
            "message" => "Consignees created successfully.",
            "data" => $results
        ];
    }
}
