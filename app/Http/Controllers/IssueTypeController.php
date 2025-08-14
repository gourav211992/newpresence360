<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Models\IssueType;
use App\Models\Service;
use App\Models\Organization;
use App\Models\OrganizationService;
use Auth;
use Illuminate\Validation\Rule;

class IssueTypeController extends Controller
{
    public function index()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first(); 
        $organizationId = $organization ?-> id ?? null;
        $companyId =  $organization?->company_id ?? null;

        $issueTypes = IssueType::selectRaw('*, COALESCE(company_id, ?) as company_id, COALESCE(organization_id, ?) as organization_id', [$companyId, $organizationId])
            ->where('group_id',$organization->group_id)
            ->get();

        return view('issueType.index', compact('issueTypes'));
    }

    public function create_issueType()
    {
        return view('issueType.create-issueType');
    }

    public function edit_issueType($id)
    {
        $issueType = IssueType::findOrFail($id);
        return view('issueType.edit-issueType', compact('issueType'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:erp_book_types,name',
        ]);

        // Get the authenticated user
        $user = Helper::getAuthenticatedUser();

        // Find the organization based on the user
        $organization = Organization::where('id', $user->organization_id)->first();

        // Create the book type with the organization details
        IssueType::create([
            'name' => $request->name,
            'status' => $request->status,
            'group_id' => $organization->group_id,
            'company_id' => $organization->company_id,
            'organization_id' => $organization->id,
        ]);

        return redirect()->route("issue-type.index")->with('success', __('message.created',['module'=>'Issue Type']));
    }

    public function update_issueType(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('erp_book_types')->ignore($id)],
        ]);

        // Find the book type to update
        $issueType = IssueType::findOrFail($id);

        // Update the book type with the new data
        $issueType->update([
            'name' => $request->input('name'),
            'status' => $request->input('status'),
        ]);

        return redirect()->route("issue-type.index")->with('success', __('message.updated',['module'=>'Issue Type']));
    }

    public function destroy_issueType($id)
    {
        // Find the book type to delete
        $issueType = IssueType::findOrFail($id);

        // Delete the book type
        $issueType->delete();

        // Redirect with success message
        return redirect()->route("issue-type.index")->with('success', __('message.deleted',['module'=>'Issue Type']));
    }

}
