<?php

namespace App\Http\Controllers;

use App\Helpers\Helper;
use Illuminate\Http\Request;

use App\Models\Book;
use App\Models\BookType;
use App\Models\Service;
use App\Models\Organization;
use App\Models\OrganizationService;
use Auth;
use Illuminate\Validation\Rule;

class BookTypeController extends Controller
{
    public function index()
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first(); 
        $organizationId = $organization ?-> id ?? null;
        $companyId =  $organization?->company_id ?? null;

        $bookTypes = BookType::selectRaw('*, COALESCE(company_id, ?) as company_id, COALESCE(organization_id, ?) as organization_id', [$companyId, $organizationId])
            ->with('books')
            ->where('group_id',$organization->group_id)
            ->with(['service'])->get();

        return view('bookType.index', compact('bookTypes'));
    }

    public function create_bookType()
    {
        $services= OrganizationService::where('organization_id',Helper::getAuthenticatedUser()->organization_id)->where('flag',1)->orderBy('name','ASC')->get();
        return view('bookType.create-bookType',compact('services'));
    }

    public function edit_bookType($id)
    {
        $bookType = BookType::findOrFail($id);
        $services= OrganizationService::where('organization_id',Helper::getAuthenticatedUser()->organization_id)->where('flag',1)->orderBy('name','ASC')->get();
        return view('bookType.edit-bookType', compact('bookType','services'));
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
        BookType::create([
            'name' => $request->name,
            'status' => $request->status,
            'service_id' => $request->service_id,
            'group_id' => $organization->group_id,
            'company_id' => $organization->company_id,
            'organization_id' => $organization->id,
        ]);

        return redirect()->route("book-type.index")->with('success', __('message.created',['module'=>'Book Type']));
    }

    public function update_bookType(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('erp_book_types')->ignore($id)],
        ]);

        // Find the book type to update
        $bookType = BookType::findOrFail($id);

        // Update the book type with the new data
        $bookType->update([
            'name' => $request->input('name'),
            'status' => $request->input('status'),
            'service_id' => $request->service_id,
        ]);

        return redirect()->route("book-type.index")->with('success', __('message.updated',['module'=>'Book Type']));
    }

    public function destroy_bookType($id)
    {
        // Find the book type to delete
        $bookType = BookType::findOrFail($id);

        // Delete the book type
        $bookType->delete();

        // Redirect with success message
        return redirect()->route("book-type.index")->with('success', __('message.deleted',['module'=>'Book Type']));
    }

}
