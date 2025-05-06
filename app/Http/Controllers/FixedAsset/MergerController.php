<?php

namespace App\Http\Controllers\FixedAsset;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;
use App\Helpers\ConstantHelper;
use App\Models\FixedAssetRegistration;
use App\Models\ErpAssetCategory;
use App\Models\Group;
use App\Models\Ledger;
use App\Models\FixedAssetMerger;


class MergerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentURL = "fixed-asset_merger";
        $series = [];

        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $firstService = $servicesBooks['services'][0];
        $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        $assets = FixedAssetRegistration::withDefaultGroupCompanyOrg()->whereIn('document_status',ConstantHelper::DOCUMENT_STATUS_APPROVED)->get();
        $categories = ErpAssetCategory::where('status', 1)->whereHas('setup')->where('organization_id', Helper::getAuthenticatedUser()->organization_id)->select('id', 'name')->get();
        $group_name = ConstantHelper::FIXED_ASSETS;
        $group = Group::where('organization_id', Helper::getAuthenticatedUser()->organization_id)->where('name', $group_name)->first() ?: Group::whereNull('organization_id')->where('name', $group_name)->first();
        $allChildIds = $group->getAllChildIds();
        $allChildIds[] = $group->id;
        $ledgers = Ledger::withDefaultGroupCompanyOrg()->where(function ($query) use ($allChildIds) {
            $query->whereIn('ledger_group_id', $allChildIds)
                ->orWhere(function ($subQuery) use ($allChildIds) {
                    foreach ($allChildIds as $child) {
                        $subQuery->orWhereJsonContains('ledger_group_id',(string)$child);
                    }
                });
                })->get(); 
                $financialEndDate = Helper::getFinancialYear(\Carbon\Carbon::parse(date('Y-m-d'))->subYear()->format('Y-m-d'))['end_date'];
                $financialStartDate = Helper::getFinancialYear(\Carbon\Carbon::parse(date('Y-m-d'))->subYear()->format('Y-m-d'))['start_date'];
                $organization = Helper::getAuthenticatedUser()->organization;
                $dep_percentage = $organization->dep_percentage;
                $dep_type = $organization->dep_type;
                $dep_method = $organization->dep_method;
        return view('fixed-asset.merger.create', compact('series','assets', 'categories','ledgers','financialEndDate','financialStartDate','dep_percentage','dep_type','dep_method'));
       
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
