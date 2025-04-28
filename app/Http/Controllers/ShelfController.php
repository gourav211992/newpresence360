<?php

namespace App\Http\Controllers;

use App\Models\ErpShelf;
use Illuminate\Http\Request;
use App\Http\Requests\ShelfRequest;
use App\Helpers\ConstantHelper;
use Auth;

class ShelfController extends Controller
{
    public function index()
    {
        $organizationId = Auth::user()->organization->id;
        $shelfs = ErpShelf::where('organization_id', $organizationId)->get();
        $status = ConstantHelper::STATUS;
        return view('procurement.shelf.index', compact('shelfs', 'status'));
    }

    public function create()
    {
        $status = ConstantHelper::STATUS;
        $racks = ErpRack::all(); 
        return view('procurement.shelf.create', compact('status', 'racks'));
    }

    public function store(ShelfRequest $request)
    {
        $organization = Auth::user()->organization;
        $validatedData = $request->validated();
        $validatedData['organization_id'] = $organization->id;
        $validatedData['group_id'] = $organization->group_id;
        $validatedData['company_id'] = $organization->company_id;
    
        try {
            $shelf = ErpShelf::create($validatedData);
    
            return response()->json([
                'status' => true,
                'message' => 'Record created successfully',
                'data' => $shelf,
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(ErpShelf $shelf)
    {
        return view('procurement.shelf.show', compact('shelf'));
    }

    public function edit(ErpShelf $shelf)
    {
        $status = ConstantHelper::STATUS;
        $racks = ErpRack::all(); 
        return view('procurement.shelf.edit', compact('shelf', 'status', 'racks'));
    }
    
    public function update(ShelfRequest $request, ErpShelf $shelf)
    {
        $organization = Auth::user()->organization;
        $validatedData = $request->validated();
        $validatedData['organization_id'] = $organization->id;
        $validatedData['group_id'] = $organization->group_id;
        $validatedData['company_id'] = $organization->company_id;

        try {
            $shelf->update($validatedData);
    
            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
                'data' => $shelf,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(ErpShelf $shelf)
    {
        try {
            if ($shelf->items()->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Shelf cannot be deleted because it has items.',
                ], 400);
            }
    
            $shelf->delete();
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
