<?php

namespace App\Http\Controllers;

use App\Http\Requests\ErpEquipmentRequest;
use App\Helpers\Helper;
use App\Helpers\InventoryHelper;
use App\Models\ErpMaintenanceType;
use App\Models\ErpEquipment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ErpEquipmentController extends Controller
{
    public function create()
    {
        $userOrganizations = Helper::getAuthenticatedUser()->organizations;
        $locations = InventoryHelper::getAccessibleLocations();
        $maintenanceTypes = ErpMaintenanceType::all(['id', 'name']);
        $categories = InventoryHelper::getAccessibleLocations();
        $approval_history = [
            [
                'name' => 'Deepak Kumar',
                'status' => 'Amendment',
                'badge' => 'primary',
                'desc' => 'Description will come here',
                'time' => '2 min ago',
            ],
            [
                'name' => 'Aniket Singh',
                'status' => 'Rejected',
                'badge' => 'danger',
                'desc' => 'Description will come here',
                'time' => '2 min ago',
            ],
            [
                'name' => 'Deewan Singh',
                'status' => 'Pending',
                'badge' => 'warning',
                'desc' => 'Description will come here',
                'time' => '4 min ago',
            ],
        ];

        // dd($organizations, $locations);

        // You can fetch dropdowns via AJAX or here (for demo, keeping empty)
        return view('equipment.create', compact('userOrganizations', 'locations', 'categories', 'maintenanceTypes', 'approval_history'));
    }

    public function store(ErpEquipmentRequest $request)
    {
       
    }

}
