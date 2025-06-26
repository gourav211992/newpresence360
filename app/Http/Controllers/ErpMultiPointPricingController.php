<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\Helper; 
use App\Http\Requests\MultiPointPricingRequest;
use App\Helpers\ConstantHelper;
use App\Models\Customer;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\ErpLogisticsMultiFixedLocation;
use App\Models\ErpLogisticsMultiFixedPricing;
use App\Models\ErpLogisticsMultiPointPricing;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Models\Organization;

class ErpMultiPointPricingController extends Controller
{
    
    public function index(Request $request)
{
    $user = Helper::getAuthenticatedUser();
    $organizationId = $user->organization_id;
    $organization = Organization::with('addresses')->find($organizationId);
    $countryId = optional($organization->addresses->first())->country_id;
    $states = State::where('country_id', $countryId)->get();
    $status = ConstantHelper::STATUS;
    $customers = Customer::withDefaultGroupCompanyOrg()->get();
    $multiPoints = ErpLogisticsMultiPointPricing::withDefaultGroupCompanyOrg()->get();

   if ($request->ajax()) {
    $query = ErpLogisticsMultiFixedPricing::with([
        'sourceState', 'sourceCity',
        'destinationState', 'destinationCity',
        'customer', 'locations.city'
    ])->withDefaultGroupCompanyOrg();

    // Filters
    if($request->filled('source_state_name')) {
    $query->whereHas('sourceState', function ($q) use ($request) {
        $q->on('mysql_master') 
          ->where('name', 'like', '%' . $request->source_state_name . '%');
    });
}

    if ($request->filled('source_city_name')) {
        $query->whereHas('sourceCity', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->source_city_name . '%');
        });
    }

    if ($request->filled('destination_state_name')) {
        $query->whereHas('destinationState', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->destination_state_name . '%');
        });
    }

    if ($request->filled('destination_city_name')) {
        $query->whereHas('destinationCity', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->destination_city_name . '%');
        });
    }

    if ($request->filled('customer_name')) {
        $query->whereHas('customer', function ($q) use ($request) {
            $q->where('company_name', 'like', '%' . $request->customer_name . '%');
        });
    }

    $query->orderByDesc('id');

    return DataTables::of($query)
        ->addIndexColumn()

        // Render custom columns
        ->addColumn('source', function ($row) {
            $state = optional($row->sourceState)->name;
            $city = optional($row->sourceCity)->name;
            return $city && $state ? "$city ($state)" : 'N/A';
        })
        ->addColumn('destination', function ($row) {
            $state = optional($row->destinationState)->name;
            $city = optional($row->destinationCity)->name;
            return $city && $state ? "$city ($state)" : 'N/A';
        })
        ->addColumn('customer', function ($row) {
            return optional($row->customer)->company_name ?? '-';
        })
        ->addColumn('locations', function ($row) {
            $html = '';
            foreach ($row->locations->take(2) as $location) {
                $html .= '<span class="badge rounded-pill badge-light-primary">'
                    . optional($location->city)->name . ': ' . number_format($location->amount, 2) . '</span> ';
            }
            if ($row->locations->count() > 2) {
                $html .= '<span class="badge rounded-pill badge-light-primary">+'
                    . ($row->locations->count() - 2) . '</span>';
            }
            return $html;
        })
        ->addColumn('status', function ($row) {
            $colors = [
                'active' => 'badge-light-success',
                'inactive' => 'badge-light-danger',
                'block' => 'badge-light-secondary',
                'transfer' => 'badge-light-warning',
                'blacklist' => 'badge-dark',
            ];
            $badge = $colors[$row->status] ?? 'badge-light-secondary';
            return '<span class="badge rounded-pill ' . $badge . ' badgeborder-radius">' . ucfirst($row->status) . '</span>';
        })
        ->addColumn('action', function ($row) {
            $editRoute = route('logistics.multi-point-fixed.edit', $row->id);
            return '<div class="dropdown">
                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                            <i data-feather="more-vertical"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end">
                            <a class="dropdown-item" href="' . $editRoute . '">
                                <i data-feather="edit-3" class="me-50"></i>
                                <span>Edit</span>
                            </a>
                        </div>
                    </div>';
        })

        // Filter global search for virtual columns
        ->filterColumn('source', function ($query, $keyword) {
            $query->whereHas('sourceState', fn($q) => $q->where('name', 'like', "%$keyword%"))
                ->orWhereHas('sourceCity', fn($q) => $q->where('name', 'like', "%$keyword%"));
        })
        ->filterColumn('destination', function ($query, $keyword) {
            $query->whereHas('destinationState', fn($q) => $q->where('name', 'like', "%$keyword%"))
                ->orWhereHas('destinationCity', fn($q) => $q->where('name', 'like', "%$keyword%"));
        })
        ->filterColumn('customer', function ($query, $keyword) {
            $query->whereHas('customer', fn($q) => $q->where('company_name', 'like', "%$keyword%"));
        })

        ->rawColumns(['locations', 'status', 'action'])
        ->make(true);
}



    return view('multi-point-pricing.index', compact('customers', 'states', 'multiPoints'));
  }

  public function store(MultiPointPricingRequest $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;

        $selectedIndexes = $request->input('row_checkbox', []);
        $insertAll = empty($selectedIndexes);
        $savedCount = 0;

    
        foreach ($request->multi_point as $index => $point) {
            if ($insertAll || in_array($index, $selectedIndexes)) {
            if (empty($point['source_state_id'])) {
                continue;
            }

                $data = [
                    'organization_id'       => $organization->id,
                    'group_id'              => $organization->group_id,
                    'company_id'            => $user->company_id ?? null,
                    'source_state_id'       => $point['source_state_id'],
                    'source_city_id'        => $point['source_city_id'],
                    'free_point'            => $point['free_point'],
                    'amount'                => $point['amount'],
                    'customer_id'           => $point['customer_id'] ?? null,
                ];

                try {
                    if (!empty($point['id'])) {
                        ErpLogisticsMultiPointPricing::where('id', $point['id'])->update($data);
                    } else {
                        ErpLogisticsMultiPointPricing::create($data);
                    }

                    $savedCount++;
                } catch (\Exception $e) {
                    \Log::error("Failed to save freight charge row {$index}: " . $e->getMessage());
                }
            }
        }

        if ($savedCount > 0) {
            return response()->json([
                'status' => true,
                'message' => "Records saved successfully.",
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No rows were saved. Please check your selections and input.',
            ], 422);
        }
    }

    public function deleteMultiple(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return response()->json([
                'status' => false,
                'message' => 'No records selected for deletion.'
            ], 400);
        }

        try {
            ErpLogisticsMultiPointPricing::whereIn('id', $ids)->delete();

            return response()->json([
                'status' => true,
                'message' => 'Records deleted successfully.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error deleting records: ' . $e->getMessage()
            ], 500);
        }
    }

}
