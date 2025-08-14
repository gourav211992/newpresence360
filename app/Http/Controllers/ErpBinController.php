<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use Auth;
use App\Models\ErpRack;
use App\Models\ErpShelf;
use App\Models\ErpStore;
use App\Models\Organization;
use App\Models\ErpBin;
use App\Models\OrganizationService;
use App\Models\OrganizationCompany;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Validation\Rule;

class ErpBinController extends Controller
{
    public function index()
    {
        $auth = Auth::guard('web')->user();
        $erpBins = ErpBin::all();
        return view('erp-bin.index',compact('erpBins','auth'));
    }

    public function create()
    {
        $user = Auth::guard('web')->user();
        $organization = Organization::where('id', $user->organization_id)->first();
        $erpBins = ErpBin::where('status','Active')->get();
        $erpStores = ErpStore::where('organization_id', $organization->id)
                    ->where('status', 'Active')
                    ->get();
        $erpRacks = ErpRack::where('organization_id',$organization->id)
                    ->where('status','Active')
                    ->get();
        $erpShelves = ErpShelf::where('organization_id',$organization->id)
                    ->where('status','Active')
                    ->get();
        return view('erp-bin.create', compact("erpBins",'user','organization','erpStores','erpRacks','erpShelves'));
    }

    public function store(Request $request)
    {
        // $request->validate([
        //     'erp_store_id' => ['required'],
        //     'erp_rack_id' => ['required'],
        //     'erp_shelf_id' => ['required'],
        //     'shelf_code' => ['required', 'string', 'max:100', 'regex:/^(?!\d+$)[\pL\s\d]+$/u'],
        //     'shelf_name' => ['required', 'string', 'max:100', 'regex:/^(?!\d+$)[\pL\s\d]+$/u'],
        //     'status' => ['required', 'string', 'in:Active,Inactive']
        // ]);
        $user = Auth::guard('web')->user();
        $organization = Organization::where('id', $user->organization_id)->first();

        $erpBin = new ErpBin();
        $erpBin->fill($request->all());
        $erpBin->organization_id = $organization->id;
        $erpBin->group_id = $organization->group_id;
        $erpBin->company_id = $organization->company_id;
        $erpBin->erp_store_id = $request->input('erp_store_id');
        $erpBin->erp_rack_id = $request->input('erp_rack_id');
        $erpBin->erp_shelf_id = $request->input('erp_shelf_id');
        $erpBin->save();

        return redirect()->route("bins")->with('success', 'Bin created successfully.');
    }

    public function edit($id)
    {
        $erpBin = ErpBin::find($id);
        $user = Auth::guard('web')->user();
        $organization = Organization::find($user->organization_id);
        $erpBins = ErpBin::where('status', 'Active')->get();

        $erpStores = ErpStore::where('organization_id', $organization->id)
                ->where('status', 'Active')
                ->get();
        $erpRacks = ErpRack::where('organization_id',$organization->id)
                ->where('status','Active')
                ->get();
        $erpShelfs = ErpShelf::where('organization_id',$organization->id)
                    ->where('status','Active')
                    ->get();
        return view('erp-bin.edit', compact('erpBin','organization', 'erpRacks','erpStores','erpBins','erpShelfs'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'erp_store_id' => ['required'],
            'erp_rack_id' => ['required'],
            'erp_shelf_id' => ['required'],
            'shelf_code' => ['required', 'string', 'max:100', 'regex:/^(?!\d+$)[\pL\s\d]+$/u'],
            'shelf_name' => ['required', 'string', 'max:100', 'regex:/^(?!\d+$)[\pL\s\d]+$/u'],
            'status' => ['required', 'string', 'in:Active,Inactive']
        ]);
        $erpBin = ErpBin::find($id);
        $erpBin->fill($request->all());

        $user = Auth::guard('web')->user();
        $organization = Organization::find($user->organization_id);
        $erpBin->organization_id = $organization->id;
        $erpBin->group_id = $organization->group_id;
        $erpBin->company_id = $organization->company_id;
        $erpBin->erp_store_id = $request->input('erp_store_id');
        $erpBin->erp_rack_id = $request->input('erp_rack_id');
        $erpBin->erp_shelf_id = $request->input('erp_shelf_id');
        $erpBin->save();

        return redirect()->route("bins")->with('success', 'Bin updated successfully.');
    }

    public function delete($id)
    {
        $ErpBin = ErpBin::find($id);
        $ErpBin->delete();

        return redirect()->route("bins")->with('success', 'Bin deleted successfully.');
    }
}
