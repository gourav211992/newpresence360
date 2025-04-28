<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use Auth;
use App\Models\ErpRack;
use App\Models\ErpShelf;
use App\Models\ErpStore;
use App\Models\Organization;
use App\Models\OrganizationService;
use App\Models\OrganizationCompany;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Validation\Rule;

class ErpShelfController extends Controller
{
    public function index()
    {
        $auth = Auth::guard('web')->user();
        $erpShelves = ErpShelf::all();
        return view('erp-shelf.index',compact('erpShelves','auth'));
    }

    public function create()
    {
        $user = Auth::guard('web')->user();
        $organization = Organization::where('id', $user->organization_id)->first();
        $erpShelves = ErpShelf::where('status','Active')->get();
        $erpStores = ErpStore::where('organization_id', $organization->id)
                    ->where('status', 'Active')
                    ->get();
        $erpRacks = ErpRack::where('organization_id',$organization->id)
                    ->where('status','Active')
                    ->get();
        return view('erp-shelf.create', compact("erpShelves",'user','organization','erpStores','erpRacks'));
    }

    public function getRacksData(Request $request)
    {
        $storeId = $request->input('erp_store_id');
        $racks = ErpRack::where('erp_store_id', $storeId)
            ->where('status', 'Active')
            ->get();

        return response()->json($racks);
    }

    public function getShelvesData(Request $request)
    {
        $rackId = $request->input('erp_rack_id');
        $shelves = ErpShelf::where('erp_rack_id', $rackId)
            ->where('status', 'Active')
            ->get();

        return response()->json($shelves);
    }

    public function store(Request $request)
    {
        $request->validate([
            'erp_store_id' => ['required'],
            'erp_rack_id' => ['required'],
            'shelf_code' => ['required', 'string', 'max:100', 'regex:/^(?!\d+$)[\pL\s\d]+$/u'],
            'shelf_name' => ['required', 'string', 'max:100', 'regex:/^(?!\d+$)[\pL\s\d]+$/u'],
            'status' => ['required', 'string', 'in:Active,Inactive']
        ]);
        $user = Auth::guard('web')->user();
        $organization = Organization::where('id', $user->organization_id)->first();

        $erpShelf = new ErpShelf();
        $erpShelf->fill($request->all());
        $erpShelf->organization_id = $organization->id;
        $erpShelf->group_id = $organization->group_id;
        $erpShelf->company_id = $organization->company_id;
        $erpShelf->erp_store_id = $request->input('erp_store_id');
        $erpShelf->erp_rack_id = $request->input('erp_rack_id');
        $erpShelf->save();

        return redirect()->route("shelves")->with('success', 'Shelf created successfully.');
    }

    public function edit($id)
    {
        $erpShelf = ErpShelf::find($id);
        $user = Auth::guard('web')->user();
        $organization = Organization::find($user->organization_id);
        $erpShelves = ErpShelf::where('status', 'Active')->get();

        $erpStores = ErpStore::where('organization_id', $organization->id)
                ->where('status', 'Active')
                ->get();
        $erpRacks = ErpRack::where('organization_id',$organization->id)
                ->where('status','Active')
                ->get();
        return view('erp-shelf.edit', compact('erpShelf','organization', 'erpRacks','erpStores','erpShelves'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'erp_store_id' => ['required'],
            'erp_rack_id' => ['required'],
            'shelf_code' => ['required', 'string', 'max:100', 'regex:/^(?!\d+$)[\pL\s\d]+$/u'],
            'shelf_name' => ['required', 'string', 'max:100', 'regex:/^(?!\d+$)[\pL\s\d]+$/u'],
            'status' => ['required', 'string', 'in:Active,Inactive']
        ]);

        $erpShelf = ErpShelf::find($id);
        $erpShelf->fill($request->all());

        $user = Auth::guard('web')->user();
        $organization = Organization::find($user->organization_id);
        $erpShelf->organization_id = $organization->id;
        $erpShelf->group_id = $organization->group_id;
        $erpShelf->company_id = $organization->company_id;
        $erpShelf->erp_store_id = $request->input('erp_store_id');
        $erpShelf->erp_rack_id = $request->input('erp_rack_id');
        $erpShelf->save();

        return redirect()->route("shelves")->with('success', 'Shelf updated successfully.');
    }

    public function delete($id)
    {
        $erpShelf = ErpShelf::find($id);
        $erpShelf->delete();

        return redirect()->route("shelves")->with('success', 'Shelf deleted successfully.');
    }
}
