<?php

namespace App\Http\Controllers\Ledger;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Organization;
use App\Models\Ledger;
use Auth;
use App\Helpers\ConstantHelper;
use Illuminate\Validation\Rule;
use App\Models\PaymentVoucherDetails;
use App\Models\ItemDetail;
use Illuminate\Support\Facades\Log;



class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $parentGroup = Helper::getGroupsQuery()->whereNull("parent_group_id")->with([
            'parent' => function ($q) {
                $q->select('id', 'name');
            }
        ])->get();

        $data = Helper::getGroupsQuery([], ['active', 'inactive'])
            ->with([
                'parent' => function ($q) {
                    $q->select('id', 'name');
                }
            ])->orderBy('id', 'desc')
            ->get();
        Log::info('Group data result:', $data->toArray());

        $non_editable = ConstantHelper::LEDGER_ACCOUNT_NON_EDITABLE;

        return view('ledgers.groups.view_groups', compact('data', 'parentGroup', 'non_editable'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $ledgers = Ledger::get();

        $allLedgerParentIds = [];
        $allIds = [];


        foreach ($ledgers as $ledger) {
            $group = array_map('intval', (array) json_decode($ledger->ledger_group_id, true) ?: [$ledger->ledger_group_id]);
            $allLedgerParentIds = array_merge($allLedgerParentIds, $group);
        }

        $allLedgerParentIds = array_unique($allLedgerParentIds);
        foreach ($allLedgerParentIds as $ledg) {
            $parent = Group::find($ledg)?->parent_group_id;
            $chk_og = Group::find($parent);
            if (!isset($chk_og->organization_id) || $chk_og->organization_id == Helper::getAuthenticatedUser()->organization_id) {
                $allIds[] = $ledg;
            }
        }
        $allIds = array_unique($allIds);

        $parents = Helper::getGroupsQuery()->whereNotIn('id', $allIds)
            ->get();


        $existingGroupname = Helper::getGroupsQuery([], ['active', 'inactive'])
            ->pluck('name')->map(function ($name) {
                return strtolower($name);
            })->values();


        return view('ledgers.groups.group-create', compact('parents', 'existingGroupname'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $existingName = Helper::getGroupsQuery([], ['active', 'inactive'])
            ->where('name', $request->name)
            ->first();



        if ($existingName) {
            return back()->withErrors(['name' => 'The name has already been taken.'])->withInput();
        }
        $existingPrefix = Helper::getGroupsQuery([], ['active', 'inactive'])->where('prefix', $request->prefix)
            ->first();



        if ($existingPrefix) {
            return back()->withErrors(['prefix' => 'The prefix has already been taken.'])->withInput();
        }


        $groups = Helper::getGroupsQuery()->where('name', $request->name)->count();

        // Find the organization based on the user's organization_id
        // $organization = Organization::where('id', Helper::getAuthenticatedUser()->organization_id)->first();
        if ($groups == 0) {
            // Create a new group record with organization details
            $parentUrl = ConstantHelper::LEDGER_GROUP_SERVICE_ALIAS;
            $validatedData = Helper::prepareValidatedDataWithPolicy($parentUrl);
            Group::create(array_merge($request->all(), $validatedData));

            // Redirect with a success message
            return redirect()->route('ledger-groups.index')->with('success', 'Group created successfully');
        } else {
            return redirect()
                ->route('ledger-groups.create')
                ->withErrors(['error' => 'Group Name already taken.'])->withInput();
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $update = true;
        $data = Group::find($id);
        $data_parent = $data->parent_group_id;

        $ledgers = Ledger::get();

        $allLedgerParentIds = [];
        $allIds = [];


        foreach ($ledgers as $ledger) {
            $group = array_map('intval', (array) json_decode($ledger->ledger_group_id, true) ?: [$ledger->ledger_group_id]);
            $allLedgerParentIds = array_merge($allLedgerParentIds, $group);
        }

        $allLedgerParentIds = array_unique($allLedgerParentIds);
        foreach ($allLedgerParentIds as $ledg) {
            $parent = Group::find($ledg)?->parent_group_id;
            $chk_og = Group::find($parent);
            if (!isset($chk_og->organization_id) || $chk_og->organization_id == Helper::getAuthenticatedUser()->organization_id) {
                $allIds[] = $ledg;
            }
        }
        $allIds = array_unique($allIds);

        $parents = Helper::getGroupsQuery()->whereNotIn('id', $allIds)
            ->get();


        if ($data_parent)
            $parents[] = Group::find($data_parent);

        $update = $data->edit;

        $existingGroupname = Helper::getGroupsQuery([], ['active', 'inactive'])->where('id', '!=', $id) // exclude the one being edited
            ->pluck('name')
            ->map(function ($name) {
                return strtolower($name);
            })
            ->values();


        return view('ledgers.groups.edit_group', compact('data', 'parents', 'update', 'existingGroupname'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

        $existingName = Helper::getGroupsQuery([], ['active', 'inactive'])
            ->where('id', '!=', (int) $id)
            ->where('name', $request->name)->exists();

        if ($existingName) {
            return back()->withErrors(['name' => 'The name has already been taken.'])->withInput();
        }

        $existingPrefix = Helper::getGroupsQuery([], ['active', 'inactive'])
            ->where('id', '!=', (int) $id)
            ->where('prefix', $request->prefix)->exists();

        if ($existingPrefix) {
            return back()->withErrors(['prefix' => 'The prefix has already been taken.'])->withInput();
        }

        $groups = Helper::getGroupsQuery()->where('name', $request->name)
            ->where('id', '!=', $id) // Correcting 'whereNot' to 'where'
            ->count();



        if ($groups == 0) {
            $update = Group::find($id);
            $update->name = $request->name;
            $update->parent_group_id = $request->parent_group_id;
            $update->status = $request->status;
            $update->prefix = $request->prefix;
            $update->save();

            return redirect()->route('ledger-groups.index')->with('success', 'Group updated successfully.');
        } else {
            return redirect()
                ->route('ledger-groups.edit', $id)
                ->withErrors(['error' => 'Group Name already taken.'])->withInput();
        }
    }

    public function getLedgerGroup(Request $request)
    {
        $searchTerm = $request->input('q', '');

        $query = Group::where('status', 1);

        if (!empty($searchTerm)) {
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%$searchTerm%");
            });
        }
        $results = $query->limit(10)->get(['id', 'name']);

        return response()->json($results);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $grp = [];
        $ledgers = Ledger::get();
        foreach ($ledgers as $ledger) {
            foreach ($ledger->groups() as $group) {
                $grp[] = $group->id;
            }
        }
        $mapped = array_values(array_unique($grp));

        try {
            $group = Group::findOrFail($id);
            $parent = Group::where('parent_group_id',$id)->exists();
            if(in_array($id,$mapped) || $parent){
                return response()->json([
                'status' => false,
                'message' => "Record cannot be deleted because it is already in use.",
            ], 500);
            }
            else{
                $group->delete();
                return response()->json([
                'status' => true,
                'message' => "Group Deleted"
            ], 200);
            }

            
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the group: ' . $e->getMessage()
            ], 500);
        }
    }

    public function delete_group($id)
    {
        $record = Group::findOrFail($id);
        $record->delete();
        return redirect()->route('groups.view_groups')->with('success', 'Group deleted successfully');
    }
    public function generate_prefix(Request $req)
    {
        $prefix = Group::generateuniquePrefix($req->name);
        return response()->json(['prefix' => $prefix]);
    }
    public function checkPrefix(Request $req)
    {
        $query = Helper::getGroupsQuery()->where('prefix', $req->prefix);

        if (!empty($req->id)) {
            $query->where('id', '!=', $req->id);
        }

        $is_not_unique = $query->exists();

        return response()->json(['is_unique' => !$is_not_unique]);
    }
}
