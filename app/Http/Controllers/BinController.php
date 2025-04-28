<?php

namespace App\Http\Controllers;

use App\Models\ErpBin;
use Illuminate\Http\Request;
use App\Http\Requests\ErpBinRequest;
use App\Helpers\ConstantHelper;
use Auth;

class ErpBinController extends Controller
{
    public function index()
    {
        $organizationId = Auth::user()->organization->id;
        $bins = ErpBin::where('organization_id', $organizationId)->get();
        $status = ConstantHelper::STATUS;
        return view('procurement.erp_bin.index', compact('bins', 'status'));
    }

    public function create()
    {
        $status = ConstantHelper::STATUS;
        return view('procurement.erp_bin.create', compact('status'));
    }

    public function store(ErpBinRequest $request)
    {
        $organization = Auth::user()->organization;
        $validatedData = $request->validated();
        $validatedData['organization_id'] = $organization->id;
        $validatedData['group_id'] = $organization->group_id;
        $validatedData['company_id'] = $organization->company_id;

        try {
            $bin = ErpBin::create($validatedData);

            return response()->json([
                'status' => true,
                'message' => 'Record created successfully',
                'data' => $bin,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while creating the record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(ErpBin $bin)
    {
        return view('procurement.erp_bin.show', compact('bin'));
    }

    public function edit(ErpBin $bin)
    {
        $status = ConstantHelper::STATUS;
        return view('procurement.erp_bin.edit', compact('bin', 'status'));
    }

    public function update(ErpBinRequest $request, ErpBin $bin)
    {
        $organization = Auth::user()->organization;
        $validatedData = $request->validated();
        $validatedData['organization_id'] = $organization->id;
        $validatedData['group_id'] = $organization->group_id;
        $validatedData['company_id'] = $organization->company_id;

        try {
            $bin->update($validatedData);

            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
                'data' => $bin,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the record',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(ErpBin $bin)
    {
        try {
            if ($bin->items()->exists()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Record cannot be deleted because it has associated items.',
                ], 400);
            }

            $bin->delete();
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
