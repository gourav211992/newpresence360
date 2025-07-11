<?php

namespace App\Http\Controllers\CostCenter;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\CostGroup;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Organization;
use Auth;

class CostGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $organization_id = Helper::getAuthenticatedUser()->organization_id;
        $parentGroup = CostGroup::
        whereNotNull("parent_cost_group_id")->with([
            'parent' => function ($q) {
                $q->select('id', 'name');
            }
        ])->get();
        $data = CostGroup::with([
            'parent' => function ($q) {
                $q->select('id', 'name');
            }
        ])->where('organization_id',$organization_id)->orderBy('id', 'desc')->get();
        return view('costCenter.groups.view', compact('data', 'parentGroup'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parent = CostGroup::where('organization_id',Helper::getAuthenticatedUser()->organization_id)->whereNotNull("parent_cost_group_id")->pluck("parent_cost_group_id")->toArray();
        $groups = CostGroup::where('organization_id',Helper::getAuthenticatedUser()->organization_id)->orderBy('id', 'desc')->whereNotIn("id", $parent)->get();
        $existingCostGroups = CostGroup::pluck('name')->toArray();
        return view('costCenter.groups.create', compact('groups','existingCostGroups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255 ',
        ]);

        $existingName = CostGroup::where('name', $request->name)
        ->first();

            if ($existingName) {
                return back()->withErrors(['name' => 'The name has already been taken.'])->withInput();
            }

        // Find the organization based on the user's organization_id
        $organization = Organization::where('id', Helper::getAuthenticatedUser()->organization_id)->first();

        // Create a new cost group record with organization details
        CostGroup::where('organization_id',Helper::getAuthenticatedUser()->organization_id)
        ->create(array_merge($request->all(), [
            'organization_id' => $organization->id,
            'group_id' => $organization->group_id,
            'company_id' => $organization->company_id,
        ]));

        // Redirect with a success message
        return redirect()->route('cost-group.index')->with('success', 'Cost Group created successfully');
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
        $data = CostGroup::find($id);
        $parent = CostGroup::whereNotNull("parent_cost_group_id")->pluck("parent_cost_group_id")->toArray();
        if (($key = array_search($data->parent_cost_group_id, $parent)) !== false) {
            unset($parent[$key]);
        }
        $existingCostGroups = CostGroup::where('id', '!=', $id)
        ->pluck('name')
        ->toArray();

        $groups = CostGroup::whereNot('id', $id)->orderBy('id', 'desc')->whereNotIn("id", $parent)->get();
        return view('costCenter.groups.edit', compact('groups', 'data','existingCostGroups'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', ],
        ]);

         $existingName = CostGroup::where('name', $request->name)
        ->where('id', '!=', $id)
        ->first();

        if ($existingName) {
            return back()->withErrors(['name' => 'The name has already been taken.'])->withInput();
        }
        $update = CostGroup::find($id);
        $update->name = $request->name;
        $update->parent_cost_group_id = $request->parent_cost_group_id;
        $update->status = $request->status;
        $update->save();

        return redirect()->route('cost-group.index')->with('success', 'Cost Group updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $record = CostGroup::findOrFail($id);
        $record->delete();
        return redirect()->route('cost-group.index')->with('success', 'Cost Group deleted successfully');
    }
}
