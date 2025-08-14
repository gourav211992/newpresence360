<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use Auth;
use App\Models\ErpRack;
use App\Models\ErpStore;
use App\Models\Organization;
use Illuminate\Support\Facades\Log;
use App\Models\OrganizationService;
use App\Models\OrganizationCompany;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class ErpRackController extends Controller
{
    public function index()
    {
        $auth = Auth::guard('web')->user();
        $erpRacks = ErpRack::all();
        return view('erp-rack.index',compact('erpRacks','auth'));
    }

    public function create()
    {
        $user = Auth::guard('web')->user();
        $organization = Organization::where('id', $user->organization_id)->first();
        $erpRacks = ErpRack::where('status','Active')->get();
        $erpStores = ErpStore::where('organization_id', $organization->id)
                    ->where('status', 'Active')
                    ->get();

        return view('erp-rack.create', compact("erpRacks",'user','organization','erpStores'));
    }

    public function store(Request $request)
    {
        //Validate the request
        $validatedData = $request->validate([
            'erp_store_id' => ['required',Rule::exists('erp_stores','id')],
            'shelf_code' => ['required', 'string', 'max:100', 'regex:/^(?!\d+$)[\pL\s\d]+$/u'],
            'shelf_name' => ['required', 'string', 'max:100', 'regex:/^(?!\d+$)[\pL\s\d]+$/u'],
            'status' => ['required', 'string', 'in:Active,Inactive']
        ]);
        $user = Auth::guard('web')->user();
        $organization = Organization::where('id', $user->organization_id)->first();

        $erpRack = new ErpRack();
        $erpRack->fill($validatedData);
        $erpRack->fill($request->all());
        $erpRack->organization_id = $organization->id;
        $erpRack->group_id = $organization->group_id;
        $erpRack->company_id = $organization->company_id;
        $erpRack->erp_store_id = $request->input('erp_store_id');
        $erpRack->save();

        return redirect()->route("racks")->with('success', 'Rack created successfully.');
    }

    public function edit($id)
    {
        $erpRack = ErpRack::findOrFail($id);
        $user = Auth::guard('web')->user();
        $organization = Organization::find($user->organization_id);
        $erpRacks = ErpRack::where('status', 'Active')->get();

        $erpStores = ErpStore::where('organization_id', $organization->id)
                    ->where('status', 'Active')
                    ->get();

        return view('erp-rack.edit', compact('erpRack', 'organization', 'erpRacks','erpStores'));
    }

    public function update(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            //'erp_store_id' => ['required',Rule::exists('erp_stores','id')],
            'shelf_code' => ['required', 'string', 'max:100', 'regex:/^(?!\d+$)[\pL\s\d]+$/u'],
            'shelf_name' => ['required', 'string', 'max:100', 'regex:/^(?!\d+$)[\pL\s\d]+$/u'],
            'status' => ['required', 'string', 'in:Active,Inactive']
        ]);

        $erpRack = ErpRack::findOrFail($id);
        $erpRack->fill($request->all());

        // Optionally update organization details if needed
        $user = Auth::guard('web')->user();
        $organization = Organization::find($user->organization_id);
        $erpRack->organization_id = $organization->id;
        $erpRack->group_id = $organization->group_id;
        $erpRack->company_id = $organization->company_id;
        $erpRack->erp_store_id = $request->input('erp_store_id');
        $erpRack->save();

        return redirect()->route("racks")->with('success', 'Rack updated successfully.');
    }

    public function delete($id)
    {
        $erpRack = ErpRack::findOrFail($id);
        $erpRack->delete();

        return redirect()->route("racks")->with('success', 'Rack deleted successfully.');
    }
}
