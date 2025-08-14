<?php

namespace App\Http\Controllers;

use App\Models\ErpRack;
use Illuminate\Http\Request;
use App\Http\Requests\RackRequest;
use App\Helpers\ConstantHelper;
use Auth;

class RackController extends Controller
{
    public function index()
    {
        $organizationId = Auth::user()->organization->id;
        $racks = ErpRack::where('organization_id', $organizationId)->get();
        $status = ConstantHelper::STATUS;
        return view('procurement.rack.index', compact('racks', 'status'));
    }

    public function create()
    {
        $status = ConstantHelper::STATUS;
        $stores = ErpStore::all(); 
        return view('procurement.rack.create', compact('status', 'stores'));
    }

    public function store(RackRequest $request)
    {
        $organization = Auth::user()->organization;
        $validatedData = $request->validated();
        $validatedData['organization_id'] = $organization->id;
        $validatedData['group_id'] = $organization->group_id;
        $validatedData['company_id'] = $organization->company_id;
    
        try {
            $rack = ErpRack::create($validatedData);
    
            return response()->json([
                'status' => true,
                'message' => 'Record created successfully',
                'data' => $rack,
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(ErpRack $rack)
    {
        return view('procurement.rack.show', compact('rack'));
    }

    public function edit(ErpRack $rack)
    {
        $status = ConstantHelper::STATUS;
        $stores = ErpStore::all(); 
        return view('procurement.rack.edit', compact('rack', 'status', 'stores'));
    }
    
    public function update(RackRequest $request, ErpRack $rack)
    {
        $organization = Auth::user()->organization;
        $validatedData = $request->validated();
        $validatedData['organization_id'] = $organization->id;
        $validatedData['group_id'] = $organization->group_id;
        $validatedData['company_id'] = $organization->company_id;

        try {
            $rack->update($validatedData);
    
            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
                'data' => $rack,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(ErpRack $rack)
    {
        try {
            if ($rack->shelfs()->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Rack cannot be deleted because it has shelfs.',
                ], 400);
            }
    
            $rack->delete();
            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully',
            ], 200);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
