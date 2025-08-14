<?php

namespace App\Http\Controllers;

use App\Models\UserSignature;
use App\Helpers\Helper;
use Carbon\Carbon;
use App\Helpers\ConstantHelper;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;
use App\Models\AuthUser;

class UserSignatureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = UserSignature::withDefaultGroupCompanyOrg()->orderBy('id', 'desc');

        // Apply filters based on the request
        if ($request->has('status') && $request->status!==null) {
            $query->where('document_status', $request->status);
        }

        if ($request->has('designation') && $request->designation!==null) {
            $query->where('designation', $request->designation);
        }
        // Apply date range filter if provided
        if ($request->filled('date_range') && $request->date_range !==null) {
            $dates = explode(' to ', $request->date_range);
            if (count($dates) == 2) {
                $start_date = Carbon::createFromFormat('Y-m-d', $dates[0])->startOfDay();
                $end_date = Carbon::createFromFormat('Y-m-d', $dates[1])->endOfDay();
                $query->whereBetween('created_at', [$start_date, $end_date]);
            }
        }

        // Get the filtered data
        $data = $query->get();
        return view('user-signature.index', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $organization_id=Helper::getAuthenticatedUser()->organization->id;
        $excludedUserIds = UserSignature::pluck('employee_id');

$employees = AuthUser::where('organization_id', $organization_id)
                     ->whereNotIn('id', $excludedUserIds)
                     ->get();

        return view('user-signature.create',compact('employees'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $userData = Helper::getAuthenticatedUser();
        $organization_id = $userData->organization->id;
        $group_id = $userData->organization->group_id;
        $company_id = $userData->organization->company_id;
        $user_id = $userData->id;
        $type = get_class($userData);
        $employee_id=$request->employee_id;

        $data = UserSignature::where('employee_id',$employee_id)->first();

        if(!isset($data->id)){
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'], // Must include the word "sign"
                'designation' => ['required', 'string', 'max:255'], // Must include the word "sign"
                'sign_upload_file' => ['required', 'file', 'mimes:jpg,png,pdf', 'max:2048'], // File name must include "sign"
            ], [
                'name.required' => 'The name field is required.',
                'name.string' => 'The name must be a valid string.',
                'name.max' => 'The name cannot exceed 255 characters.',

                'designation.required' => 'The designation field is required.',
                'designation.string' => 'The designation must be a valid string.',
                'designation.max' => 'The designation cannot exceed 255 characters.',

                'sign_upload_file.required' => 'The signature file is required.',
                'sign_upload_file.file' => 'The uploaded file must be a valid file.',
                'sign_upload_file.mimes' => 'The file must be of type jpg, png, or pdf.',
                'sign_upload_file.max' => 'The file size cannot exceed 2MB.',
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->route('user-signature.create')
                    ->withInput()
                    ->withErrors($validator);
            }

            // Handle file upload if present
            $filePath = null;
            if ($request->hasFile('sign_upload_file')) {
                $filePath = $request->file('sign_upload_file')->store('user_signature', 'public');
            }

            // Save data to the database
            $filetrack = UserSignature::create([
                'group_id' => $group_id,
                'company_id' => $company_id,
                'organization_id' => $organization_id,
                'employee_id'=>$request->input('employee_id'),
                'name' => $request->input('name'),
                'designation' => $request->input('designation'),
                'sign_upload_file' => $filePath,
                'created_by' => $user_id,
                'type' => $type
            ]);

            return redirect()
                ->route('user-signature.index')
                ->with('success', 'Signature record created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating Signature record: ' . $e->getMessage());
            return redirect()
                ->route('user-signature.create')
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }else{
        return redirect()
        ->route('user-signature.index')
        ->withInput()
        ->withErrors(['error' => 'Sign already exist']);
    }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $data = UserSignature::withDefaultGroupCompanyOrg()->findorFail($id);
        $organization_id=Helper::getAuthenticatedUser()->organization->id;
        $employees = AuthUser::where('organization_id', $organization_id)->get();

        return view('user-signature.show', compact('data','employees'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = UserSignature::withDefaultGroupCompanyOrg()->findorFail($id);
        $organization_id=Helper::getAuthenticatedUser()->organization->id;
        $employees = AuthUser::where('organization_id', $organization_id)->get();


        return view('user-signature.edit', compact('data','employees'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'string', 'max:255'], // Must include the word "sign"
                'designation' => ['required', 'string', 'max:255'], // Must include the word "sign"
                'sign_upload_file' => ['nullable', 'file', 'mimes:jpg,png,pdf', 'max:2048'], // File name must include "sign"
            ], [
                'name.required' => 'The name field is required.',
                'name.string' => 'The name must be a valid string.',
                'name.max' => 'The name cannot exceed 255 characters.',

                'designation.required' => 'The designation field is required.',
                'designation.string' => 'The designation must be a valid string.',
                'designation.max' => 'The designation cannot exceed 255 characters.',

                'sign_upload_file.file' => 'The uploaded file must be a valid file.',
                'sign_upload_file.mimes' => 'The file must be of type jpg, png, or pdf.',
                'sign_upload_file.max' => 'The file size cannot exceed 2MB.',
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->route('user-signature.edit', $id)
                    ->withInput()
                    ->withErrors($validator);
            }

            // Find the record
            $userSignature = UserSignature::findOrFail($id);

            // Handle file upload if present
            $filePath = $userSignature->sign_upload_file;
            if ($request->hasFile('sign_upload_file')) {
                // Delete the old file if it exists
                if ($filePath && Storage::exists('public/' . $filePath)) {
                    Storage::delete('public/' . $filePath);
                }

                // Upload the new file
                $filePath = $request->file('sign_upload_file')->store('user_signature', 'public');
            }
            $userSignature->designation = $request->designation;
            $userSignature->sign_upload_file = $filePath;
            $userSignature->save();

            return redirect()
                ->route('user-signature.index')
                ->with('success', 'Signature record updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating Signature record: ' . $e->getMessage());
            return redirect()
                ->route('user-signature.index')
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
    public function showFile($file)
    {
        // Retrieve the file record
        $data = UserSignature::findOrFail($file);
        $file = $data->sign_upload_file; // Assuming the `file` column contains the file path
        $filePath = storage_path('app/public/' . $file);

        // Check if the file exists
        if (!file_exists($filePath)) {
            abort(404, 'File not found.');
        }

        // Ensure the response serves the file with proper headers
        return response()->file($filePath, [
            'Content-Type' => mime_content_type($filePath),
            'Content-Disposition' => 'inline; filename="' . $data->document_number . '.' . pathinfo($filePath, PATHINFO_EXTENSION) . '"',
        ]);

    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
