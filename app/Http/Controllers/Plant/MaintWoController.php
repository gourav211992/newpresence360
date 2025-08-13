<?php

namespace App\Http\Controllers\Plant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Helpers\Helper;

class MaintWoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('plant.maint_wo.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parentURL = "plant_maint-wo";
        $series = [];
        $servicesBooks = Helper::getAccessibleServicesFromMenuAlias($parentURL);
        
        if (count($servicesBooks['services']) > 0) {
            $firstService = $servicesBooks['services'][0];
            $series = Helper::getBookSeriesNew($firstService->alias, $parentURL)->get();
        }
        
        // Get locations for the dropdown
        $locations = [];
        
        return view('plant.maint_wo.create', compact('series', 'locations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'document_number' => 'required|string|max:100|unique:erp_plant_maint_wo,document_number',
            'document_date' => 'required|date',
            'doc_prefix' => 'nullable|string',
            'doc_suffix' => 'nullable|string',
            'doc_no' => 'nullable|integer',
            'document_status' => 'required|string|in:Draft,Submitted,Approved,Rejected,Completed',
            
            // Location and Equipment
            'location_id' => 'required|exists:locations,id',
            'equipment_id' => 'required|exists:equipments,id',
            'defect_notification_id' => 'nullable|exists:defect_notifications,id',
            
            // Maintenance Details
            'maintenance_type' => 'required|in:Preventive,Corrective,Predictive,Breakdown',
            'priority' => 'required|in:Low,Medium,High,Critical',
            'detailed_observations' => 'nullable|string',
            'work_description' => 'nullable|string',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'completion_date' => 'nullable|date|after_or_equal:document_date',
            'doc_number_type' => 'required|string',
            'doc_reset_pattern' => 'nullable|string',
            'doc_prefix' => 'nullable|string',
            'doc_suffix' => 'nullable|string',
            'doc_no' => 'nullable|integer',
            'document_status' => 'required|string',
        ]);

        try {
            // Set the organization, group, and company from the authenticated user
            $user = auth()->user();
            $validated['organization_id'] = $user->organization_id;
            $validated['group_id'] = $user->group_id;
            $validated['company_id'] = $user->company_id;
            $validated['created_by'] = $user->id;
            $validated['document_status'] = $request->document_status ?? 'Draft';
            $validated['approval_level'] = 1; // Initial approval level
            $validated['revision_number'] = 0; // Initial revision

           
            $workOrder = \App\Models\PlantMaintWo::create($validated);
            return redirect()
                ->route('maint-wo.show', $workOrder->id)
                ->with('success', 'Maintenance work order created successfully!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create maintenance work order. ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        return view('plant.maint_wo.show', compact('id'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('plant.maint_wo.edit', compact('id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Add your update logic here
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // Add your delete logic here
    }
}
