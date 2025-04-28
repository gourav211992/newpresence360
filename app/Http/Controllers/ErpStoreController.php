<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use Auth;
use App\Models\ErpStore;
use App\Models\Organization;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Validation\Rule;

class ErpStoreController extends Controller
{
    public function index()
    {
        $auth = Auth::guard('web')->user();
        $erpStores = ErpStore::all();
        return view('erp-store.index',compact('erpStores','auth'));
    }

    public function create()
    {
        $user = Auth::guard('web')->user();
        $organization = Organization::where('id', $user->organization_id)->first();
        $erpStores = ErpStore::where('status','Active')->get();

        return view('erp-store.create', compact("erpStores",'user','organization'));
    }

    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'store_code' => ['required', 'string', 'max:100', 'regex:/^(?!\d+$)[\pL\s\d]+$/u'],
            'store_name' => ['required', 'string', 'max:100', 'regex:/^(?!\d+$)[\pL\s\d]+$/u'],
            'status' => ['required', 'string', 'in:Active,Inactive']
        ]);

        $user = Auth::guard('web')->user();
        $organization = Organization::where('id', $user->organization_id)->first();

            $erpStore = new ErpStore();
            $erpStore->fill($request->all());
            $erpStore->organization_id = $organization->id;
            $erpStore->group_id = $organization->group_id;
            $erpStore->company_id = $organization->company_id;

            $erpStore->save();

        return redirect()->route("stock")->with('success', 'Store created successfully.');
    }

    public function edit($id)
    {
        $erpStore = ErpStore::findOrFail($id);
        $user = Auth::guard('web')->user();
        $organization = Organization::find($user->organization_id);
        $erpStores = ErpStore::where('status', 'Active')->get();

        return view('erp-store.edit', compact('erpStore', 'organization', 'erpStores'));
    }

    public function update(Request $request, $id)
    {
        // Validate the request
        $request->validate([
            'store_code' => ['required', 'string', 'max:100', 'regex:/^(?!\d+$)[\pL\s\d]+$/u'],
            'store_name' => ['required', 'string', 'max:100', 'regex:/^(?!\d+$)[\pL\s\d]+$/u'],
            'status' => ['required', 'string', 'in:Active,Inactive']
        ]);

        $erpStore = ErpStore::findOrFail($id);
        $erpStore->fill($request->all());

        // Optionally update organization details if needed
        $user = Auth::guard('web')->user();
        $organization = Organization::find($user->organization_id);
        $erpStore->organization_id = $organization->id;
        $erpStore->group_id = $organization->group_id;
        $erpStore->company_id = $organization->company_id;

        $erpStore->save();

        return redirect()->route("stock")->with('success', 'Store updated successfully.');
    }

    public function delete($id)
    {
        $erpStore = ErpStore::findOrFail($id);
        $erpStore->delete();

        return redirect()->route("stock")->with('success', 'Store deleted successfully.');
    }
}
