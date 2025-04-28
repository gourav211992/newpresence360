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



class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $parentGroup = Group::whereNull("parent_group_id")->where(function($q){
            $q->whereNull('organization_id');
            $q->OrWhere('organization_id', Helper::getAuthenticatedUser()->organization_id);
        })->with([
            'parent' => function ($q) {
                $q->select('id', 'name');
            }
        ])->get();

        $data = Group::where(function($q){
            $q->whereNull('organization_id');
            $q->OrWhere('organization_id', Helper::getAuthenticatedUser()->organization_id);
        })->with([
            'parent' => function ($q) {
                $q->select('id', 'name');
            }
        ])->orderBy('id', 'desc')->get();
        $non_editable = ConstantHelper::LEDGER_ACCOUNT_NON_EDITABLE;

        return view('ledgers.groups.view_groups', compact('data', 'parentGroup','non_editable'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {

        $ledgers = Ledger::withDefaultGroupCompanyOrg()->get();
        
        $allLedgerParentIds = []; 
        $allIds=[];


        foreach ($ledgers as $ledger) {
            $group = array_map('intval', (array) json_decode($ledger->ledger_group_id, true) ?: [$ledger->ledger_group_id]);
            $allLedgerParentIds = array_merge($allLedgerParentIds, $group);
        }
        
        $allLedgerParentIds = array_unique($allLedgerParentIds);
        foreach($allLedgerParentIds as $ledg){
            $parent = Group::find($ledg)->parent_group_id;
            $chk_og = Group::find($parent);
                if(!isset($chk_og->organization_id) || $chk_og->organization_id == Helper::getAuthenticatedUser()->organization_id){
                    $allIds[]=$ledg;
                }
            
        }
        $allIds = array_unique($allIds);
        
        $parents = Group::where(function($q){
            $q->whereNull('organization_id');
            $q->OrWhere('organization_id', Helper::getAuthenticatedUser()->organization_id);
        })->whereNotIn('id',$allIds)
        ->get();




            
        return view('ledgers.groups.group-create', compact('parents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $groups = Group::where('name', $request->name)
    ->where(function ($query) {
        $query->whereNull('organization_id')
              ->orWhere('organization_id', Helper::getAuthenticatedUser()->organization_id);
    })
    ->count();

        

        // Find the organization based on the user's organization_id
        $organization = Organization::where('id', Helper::getAuthenticatedUser()->organization_id)->first();
        if($groups==0){
        // Create a new group record with organization details
        Group::create(array_merge($request->all(), [
            'organization_id' => $organization->id,
            'group_id' => $organization->group_id,
            'company_id' => $organization->company_id,
        ]));

        // Redirect with a success message
        return redirect()->route('ledger-groups.index')->with('success', 'Group created successfully');
}
else{
    return redirect()
        ->route('ledger-groups.create')
        ->withErrors(['error'=> 'Group Name already taken.'])->withInput();
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

        $ledgers = Ledger::withDefaultGroupCompanyOrg()->get();
        
        $allLedgerParentIds = []; 
        $allIds=[];


        foreach ($ledgers as $ledger) {
            $group = array_map('intval', (array) json_decode($ledger->ledger_group_id, true) ?: [$ledger->ledger_group_id]);
            $allLedgerParentIds = array_merge($allLedgerParentIds, $group);
        }
        
        $allLedgerParentIds = array_unique($allLedgerParentIds);
        foreach($allLedgerParentIds as $ledg){
            $parent = Group::find($ledg)->parent_group_id;
            $chk_og = Group::find($parent);
                if(!isset($chk_og->organization_id) || $chk_og->organization_id == Helper::getAuthenticatedUser()->organization_id){
                    $allIds[]=$ledg;
                }
            
        }
        $allIds = array_unique($allIds);
        
        $parents = Group::where(function($q){
            $q->whereNull('organization_id');
            $q->OrWhere('organization_id', Helper::getAuthenticatedUser()->organization_id);
        })->whereNotIn('id',$allIds)
        ->get();


        if($data_parent)
        $parents[] = Group::find($data_parent);

        $update = $data->edit;


        return view('ledgers.groups.edit_group', compact('data', 'parents','update'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);
     $groups = Group::where('name', $request->name)
    ->where('id', '!=', $id) // Correcting 'whereNot' to 'where'
    ->where(function ($query) {
        $query->whereNull('organization_id')
              ->orWhere('organization_id', Helper::getAuthenticatedUser()->organization_id);
    })
    ->count();


    
    if($groups==0){
        $update = Group::find($id);
        $update->name = $request->name;
        $update->parent_group_id = $request->parent_group_id;
        $update->status = $request->status;
        $update->save();

        return redirect()->route('ledger-groups.index')->with('success', 'Group updated successfully.');
    }
    else{
        return redirect()
            ->route('ledger-groups.edit',$id)
            ->withErrors(['error'=> 'Group Name already taken.'])->withInput();
    }
    }

    public function getLedgerGroup(Request $request)
    {
        $searchTerm = $request->input('q', '');
        
        $query = Group::where('status', 1);
        
        if (!empty($searchTerm)) {
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%$searchTerm%");
            });
        }
        $results = $query->limit(10)->get(['id', 'name']);
        
        return response()->json($results);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function delete_group($id)
    {
        $record = Group::findOrFail($id);
        $record->delete();
        return redirect()->route('groups.view_groups')->with('success', 'Group deleted successfully');
    }
    

}
