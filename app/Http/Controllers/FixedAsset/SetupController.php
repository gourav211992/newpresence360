<?php

namespace App\Http\Controllers\FixedAsset;

use App\Models\Ledger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FixedAssetSetup;
use App\Models\ErpAssetCategory;
use App\Helpers\Helper;
use Carbon\Carbon;
use App\Helpers\ConstantHelper;
use App\Models\Group;
use App\Helpers\InventoryHelper;
use App\Models\FixedAssetSub;

class SetupController extends Controller
{
    public function index(Request $request)
    {
        $parentURL = "fixed-asset_registration";


        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $query =  FixedAssetSetup::orderBy('id', 'desc');
        $categories = ErpAssetCategory::where('status', 'active')->get();


        // Apply filters based on the request
        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        if ($request->has('category') && $request->category !== null) {
            $query->where('category_id', $request->category);
        }
        // Apply date range filter if provided
        if ($request->filled('date_range') && $request->date_range !== null) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $start_date = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
                $end_date = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();
                $query->whereBetween('created_at', [$start_date, $end_date]);
            }
        }

        // Get the filtered data
        $data = $query->get();

        return view('fixed-asset.setup.index', compact('data', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentURL = "fixed-asset_registration";


        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        if (count($servicesBooks['services']) == 0) {
            return redirect()->route('/');
        }
        $categories = ErpAssetCategory::whereDoesntHave('setup')
            ->where('status', 'active')
            ->get();

        //depreciation ledgers
            $group_names = [ConstantHelper::INDIRECT_EXPENSES, ConstantHelper::DIRECT_EXPENSES];

        // Get all matching groups
        $groups = Helper::getGroupsQuery()->whereIn('name', $group_names)->get();

        $allChildIds = [];

        foreach ($groups as $group) {
            $childIds = $group->getAllChildIds(); // assumes it returns an array
            $childIds[] = $group->id; // include the group itself
            $allChildIds = array_merge($allChildIds, $childIds);
        }

        $allChildIds = array_unique($allChildIds); // optional, to avoid duplicates

        $dep_ledgers = Ledger::where(function ($query) use ($allChildIds) {
                $query->whereIn('ledger_group_id', $allChildIds)
                    ->orWhere(function ($subQuery) use ($allChildIds) {
                        foreach ($allChildIds as $child) {
                            $subQuery->orWhereJsonContains('ledger_group_id', (string) $child)->orWhereJsonContains('ledger_group_id',$child);
                        }
                    });
            })->get();

        //assets ledgers
        $group_name = ConstantHelper::FIXED_ASSETS;
        $group = Helper::getGroupsQuery()->where('name', $group_name)->first();
        $allChildIds = $group->getAllChildIds();
        $allChildIds[] = $group->id;
        $ledgers = Ledger::where(function ($query) use ($allChildIds) {
            $query->whereIn('ledger_group_id', $allChildIds)
                ->orWhere(function ($subQuery) use ($allChildIds) {
                    foreach ($allChildIds as $child) {
                        $subQuery->orWhereJsonContains('ledger_group_id', (string)$child)->orWhereJsonContains('ledger_group_id',$child);
                    }
                });
        })->get();

        //expense and sales ledgers
        $group_name = ConstantHelper::INDIRECT_EXPENSES;
        $group = Helper::getGroupsQuery()->where('name', $group_name)->first();
        $allChildIds = $group->getAllChildIds();
        $allChildIds[] = $group->id;
        $sales_exp_ledgers = Ledger::where(function ($query) use ($allChildIds) {
            $query->whereIn('ledger_group_id', $allChildIds)
                ->orWhere(function ($subQuery) use ($allChildIds) {
                    foreach ($allChildIds as $child) {
                        $subQuery->orWhereJsonContains('ledger_group_id', (string)$child)->orWhereJsonContains('ledger_group_id',$child);
                    }
                });
        })->get();

        //surplus ledgers
        $group_name = ConstantHelper::RESERVE_SURPLUS;
        $group = Helper::getGroupsQuery()->where('name', $group_name)->first();
        $allChildIds = $group->getAllChildIds();
        $allChildIds[] = $group->id;
        $sur_ledgers = Ledger::where(function ($query) use ($allChildIds) {
            $query->whereIn('ledger_group_id', $allChildIds)
                ->orWhere(function ($subQuery) use ($allChildIds) {
                    foreach ($allChildIds as $child) {
                        $subQuery->orWhereJsonContains('ledger_group_id', (string)$child)->orWhereJsonContains('ledger_group_id',$child);
                    }
                });
        })->get();
       $organization = Helper::getAuthenticatedUser()->organization;
        
        $dep_percentage = $organization->dep_percentage;
        $dep_method = $organization->dep_method;
       

        
        $dep_ledger_id = FixedAssetSetup::orderBy('updated_at', 'desc')->where('act_type','company')->first()?->dep_ledger_id;
        $dep_ledger_group_id = FixedAssetSetup::orderBy('updated_at', 'desc')->first()?->dep_ledger_group_id;
        return view('fixed-asset.setup.create', compact('dep_method','categories', 'ledgers', 'dep_ledger_id', 'dep_ledger_group_id', 'dep_ledgers', 'sur_ledgers', 'sales_exp_ledgers','dep_percentage'))
            ->with('services', $servicesBooks['services'])
            ->with('parentURL', $parentURL);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $asset_category_id = $request->asset_category_id;

        if ($asset_category_id == null) {
            $asset_category_id = ErpAssetCategory::where('name', $request->asset_category)->first();
            $validatedData = Helper::prepareValidatedDataWithPolicy();


            if (!$asset_category_id) {
                ErpAssetCategory::create([
                    'created_by' => $user->id,
                    'type' => get_class($user),
                    'name' => $request->asset_category,
                    'status' => ConstantHelper::ACTIVE,
                    'group_id' => $validatedData['group_id'],
                    'company_id' => $validatedData['company_id'],
                    'organization_id' => $validatedData['organization_id'],
                ]);
                $asset_category_id = ErpAssetCategory::where('name', $request->asset_category)->first()->id;
            }
        }
        $validatedData = Helper::prepareValidatedDataWithPolicy();

        $additionalData = [
            'created_by' => $user->id, // Assuming logged-in user
            'type' => get_class($user),
            'group_id' => $validatedData['group_id'],
            'company_id' => $validatedData['company_id'],
            'organization_id' => $validatedData['organization_id'],
            'asset_category_id' => $asset_category_id,
        ];




        $data = array_merge($request->all(), $additionalData);
        $check = FixedAssetSetup::where('asset_category_id', $asset_category_id)->first();
        if ($check)
            return redirect()->route("finance.fixed-asset.setup.create")->with('error', 'Setup already exists!');


        // Store the asset
        try {
            $asset = FixedAssetSetup::create($data);
            return redirect()->route("finance.fixed-asset.setup.index")->with('success', 'Setup created successfully!');
        } catch (\Exception $e) {
            // Set error message
            return redirect()->route("finance.fixed-asset.setup.create")->with('error', $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = FixedAssetSetup::findorFail($id);
        $categories = ErpAssetCategory::where('status', 'active')->get();
        $ledgers = Ledger::get();
        $ledgerGroups = json_decode(self::getLedgerGroups($data->ledger_id)->content());
        $ledgerGroupsDep = json_decode(self::getLedgerGroups($data->dep_ledger_id)->content());

        return view('fixed-asset.setup.show', compact('ledgerGroups', 'categories', 'data', 'ledgers', 'ledgerGroupsDep'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = FixedAssetSetup::findorFail($id);
        $categories = ErpAssetCategory::where('status', 'active')->get();
        $group_names = [ConstantHelper::INDIRECT_EXPENSES, ConstantHelper::DIRECT_EXPENSES];

// Get all matching groups
        $groups = Helper::getGroupsQuery()->whereIn('name', $group_names)->get();

        $allChildIds = [];

        foreach ($groups as $group) {
            $childIds = $group->getAllChildIds(); // assumes it returns an array
            $childIds[] = $group->id; // include the group itself
            $allChildIds = array_merge($allChildIds, $childIds);
        }

        $allChildIds = array_unique($allChildIds); // optional, to avoid duplicates

        $dep_ledgers = Ledger::where(function ($query) use ($allChildIds) {
                $query->whereIn('ledger_group_id', $allChildIds)
                    ->orWhere(function ($subQuery) use ($allChildIds) {
                        foreach ($allChildIds as $child) {
                            $subQuery->orWhereJsonContains('ledger_group_id', (string) $child)->orWhereJsonContains('ledger_group_id',$child);
                        }
                    });
            })->get();


        $group_name = ConstantHelper::FIXED_ASSETS;
        $group = Helper::getGroupsQuery()->where('name', $group_name)->first();
        $allChildIds = $group->getAllChildIds();
        $allChildIds[] = $group->id;
        $ledgers = Ledger::where(function ($query) use ($allChildIds) {
            $query->whereIn('ledger_group_id', $allChildIds)
                ->orWhere(function ($subQuery) use ($allChildIds) {
                    foreach ($allChildIds as $child) {
                        $subQuery->orWhereJsonContains('ledger_group_id', (string)$child)->orWhereJsonContains('ledger_group_id',$child);
                    }
                });
        })->get();

        
        //expense ledgers
        $group_name = ConstantHelper::INDIRECT_EXPENSES;
        $group = Helper::getGroupsQuery()->where('name', $group_name)->first();
        $allChildIds = $group->getAllChildIds();
        $allChildIds[] = $group->id;
        $sales_exp_ledgers = Ledger::where(function ($query) use ($allChildIds) {
            $query->whereIn('ledger_group_id', $allChildIds)
                ->orWhere(function ($subQuery) use ($allChildIds) {
                    foreach ($allChildIds as $child) {
                        $subQuery->orWhereJsonContains('ledger_group_id', (string)$child)->orWhereJsonContains('ledger_group_id',$child);
                    }
                });
        })->get();

        //surplus ledgers
        $group_name = ConstantHelper::RESERVE_SURPLUS;
        $group = Helper::getGroupsQuery()->where('name', $group_name)->first();
        $allChildIds = $group->getAllChildIds();
        $allChildIds[] = $group->id;
        $sur_ledgers = Ledger::where(function ($query) use ($allChildIds) {
            $query->whereIn('ledger_group_id', $allChildIds)
                ->orWhere(function ($subQuery) use ($allChildIds) {
                    foreach ($allChildIds as $child) {
                        $subQuery->orWhereJsonContains('ledger_group_id', (string)$child)->orWhereJsonContains('ledger_group_id',$child);
                    }
                });
        })->get();


        $ledgerGroups = json_decode(self::getLedgerGroups($data->ledger_id)->content());
        $ledgerGroupsDep = json_decode(self::getLedgerGroups($data->dep_ledger_id)->content());
        $organization = Helper::getAuthenticatedUser()->organization;
        $dep_method = $organization->dep_method;
        





        return view('fixed-asset.setup.edit', compact('dep_method','ledgerGroups', 'categories', 'data', 'ledgers', 'ledgerGroupsDep', 'dep_ledgers', 'sur_ledgers', 'sales_exp_ledgers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $asset = FixedAssetSetup::find($id);

        if (!$asset) {
            return redirect()
                ->route('finance.fixed-asset.setup.index')
                ->with('error', 'Setup not found.');
        }

        $data = $request->all();

        // Update the asset
        try {
            $asset->update($data);
            return redirect()->route("finance.fixed-asset.setup.index")->with('success', 'Setup updated successfully!');
        } catch (\Exception $e) {
            // Handle any exceptions
            return redirect()->route("finance.fixed-asset.setup.edit", $id)->with('error', $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {

        //
    }
    public function category(Request $request)
    {
        $categories = FixedAssetSetup::where('asset_category_id', $request->category_id)->first();
        return response()->json($categories);
    }
    public function getLedgerGroups($ledgerId)
    {
        $ledger = Ledger::find($ledgerId);

        if ($ledger) {
            $groups = $ledger->group();

            if ($groups && $groups instanceof \Illuminate\Database\Eloquent\Collection) {
                $groupItems = $groups->map(function ($group) {
                    return ['id' => $group->id, 'name' => $group->name];
                });
            } else if ($groups) {
                $groupItems = [
                    ['id' => $groups->id, 'name' => $groups->name],
                ];
            } else {
                $groupItems = [];
            }

            return response()->json($groupItems);
        }

        return response()->json([], 404);
    }
    public function generate_prefix(Request $req)
    {
        if($req->has('id')){
            $prefix = FixedAssetSetup::find($req->id)?->prefix;
            if(empty($prefix))
                $prefix = FixedAssetSetup::generateuniquePrefix($req->name);
        }
        else
        $prefix = FixedAssetSetup::generateuniquePrefix($req->name);
        return response()->json(['prefix' => $prefix]);
    }
    public function checkPrefix(Request $req)
    {
        $query = FixedAssetSetup::where('prefix', $req->prefix);

        if (!empty($req->id)) {
            $query->where('id', '!=', $req->id);
        }

        $is_not_unique = $query->exists();

        return response()->json(['is_unique' => !$is_not_unique]);
    }
}
