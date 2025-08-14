<?php

namespace App\Http\Controllers\FixedAsset;

use App\Http\Controllers\Controller;
use App\Models\FixedAssetRegistration;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Models\Employee;
use App\Models\ErpStore;
use App\Models\FixedAssetInsurance;
use Carbon\Carbon;
use App\Models\ErpAssetCategory;
use App\Helpers\InventoryHelper;

class InsuranceController extends Controller
{
    public function index(Request $request)
    {
        $parentURL = "fixed-asset_registration";


         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
         if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $query = FixedAssetInsurance::orderBy('id', 'desc');
        $assets = FixedAssetRegistration::whereNotNull('asset_code')
            ->whereNotNull('asset_name')
            ->get();

        // Apply filters based on the request
        if ($request->has('status') && $request->status!==null) {
            if($request->status=='expired')
            {
                $query->where('expiry_date','<',Carbon::now()->toDateString());
            }
            else
            {
                $query->where('expiry_date','>',Carbon::now()->toDateString());
            }
        }

        if ($request->has('asset') && $request->asset!==null) {
            $query->where('asset_id', $request->asset);
        }
        // Apply date range filter if provided
        if ($request->filled('date_range') && $request->date_range !==null) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $start_date = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
                $end_date = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();
                $query->whereBetween('created_at', [$start_date, $end_date]);
            }
        }


        // Get the filtered data
        $data = $query->get();
        return view('fixed-asset.insurance.index', compact('data','assets'));
    }

    public function create()
    {
        $parentURL = "fixed-asset_registration";


         $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
         if (count($servicesBooks['services']) == 0) {
            return redirect() -> route('/');
        }
        $assets = FixedAssetRegistration::whereNotNull('asset_code')
            ->whereNotNull('asset_name')
            ->get();
        $locations = InventoryHelper::getAccessibleLocations();
        $categories = ErpAssetCategory::where('status', 1)->whereHas('setup')->select('id', 'name')->get();
        return view('fixed-asset.insurance.create', compact('assets','locations','categories'));
    }

    public function store(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $additionalData = [
            'created_by' => $user->id,
            'type' => get_class($user),
            'organization_id' => $user->organization->id,
            'group_id' => $user->organization->group_id,
            'company_id' => $user->organization->company_id,
        ];
        $data = array_merge($request->all(), $additionalData);

        try {
            FixedAssetInsurance::create($data);
            return redirect()->route("finance.fixed-asset.insurance.index")->with('success', 'Insurance created successfully!');
        } catch (\Exception $e) {
            return redirect()->route("finance.fixed-asset.insurance.create")->with('error', $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $data = FixedAssetInsurance::findorFail($id);

        $assets = FixedAssetRegistration::whereNotNull('asset_code')
            ->whereNotNull('asset_name')
            ->get();
        $locations = InventoryHelper::getAccessibleLocations();
        $categories = ErpAssetCategory::where('status', 1)->whereHas('setup')->select('id', 'name')->get();
        return view('fixed-asset.insurance.show', compact('assets', 'data','locations','categories'));
    }

    public function edit(string $id)
    {
        $data = FixedAssetInsurance::findorFail($id);
        $assets = FixedAssetRegistration::whereNotNull('asset_code')
            ->whereNotNull('asset_name')
            ->get();
        $locations = InventoryHelper::getAccessibleLocations();
        $categories = ErpAssetCategory::where('status', 1)->whereHas('setup')->select('id', 'name')->get();
        return view('fixed-asset.insurance.edit', compact('assets', 'data','locations','categories'));
    }

    public function update(Request $request, $id)
    {
        $asset = FixedAssetInsurance::find($id);

        if (!$asset) {
            return redirect()
                ->route('finance.fixed-asset.insurance.index')
                ->with('error', 'Insurance not found.');
        }

        $data = $request->all();

        try {
            $asset->update($data);
            return redirect()->route("finance.fixed-asset.insurance.index")->with('success', 'Insurance updated successfully!');
        } catch (\Exception $e) {
            return redirect()->route("finance.fixed-asset.insurance.edit", $id)->with('error', $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
    }
}
