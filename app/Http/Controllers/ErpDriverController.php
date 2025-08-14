<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
use App\Http\Requests\DriverRequest;
use App\Helpers\Helper; 
use App\Models\ErpDriver;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use App\Models\ErpDriverMedia;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Models\Employee;
use App\Models\Organization;

class ErpDriverController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::where('id', $user->organization_id)->first(); 
        $organizationId = $organization?->id ?? null;
        $companyId = $organization?->company_id ?? null;

        if ($request->ajax()) {
            $drivers = ErpDriver::with('employee','auth_user')
                ->withDefaultGroupCompanyOrg()
                ->orderByDesc('id');

            // Apply filters from request
            if ($request->filled('name')) {
                $drivers->where('name', 'like', '%' . $request->name . '%');
            }

            if ($request->filled('employee_code')) {
                $drivers->whereHas('employee', function ($q) use ($request) {
                    $q->where('employee_code', 'like', '%' . $request->employee_code . '%');
                });
            }

            if ($request->filled('email')) {
                $drivers->where('email', 'like', '%' . $request->email . '%');
            }

            if ($request->filled('mobile_no')) {
                $drivers->where('mobile_no', 'like', '%' . $request->mobile_no . '%');
            }

            if ($request->filled('experience_years')) {
                $drivers->where('experience_years', $request->experience_years);
            }

            if ($request->filled('status')) {
                $drivers->where('status', $request->status);
            }

            return DataTables::of($drivers)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    $colors = [
                        'active'    => 'badge-light-success',
                        'inactive'  => 'badge-light-danger',
                        'block'     => 'badge-light-secondary',
                        'transfer'  => 'badge-light-warning',
                        'blacklist' => 'badge-dark',
                    ];
                    $badge = $colors[$row->status] ?? 'badge-light-secondary';
                    return '<span class="badge rounded-pill ' . $badge . ' badgeborder-radius">' . ucfirst($row->status) . '</span>';
                })
                ->editColumn('created_at', fn($row) => optional($row->created_at)->format('d-m-Y') ?? 'N/A')
   
                ->editColumn('created_by', function ($row) {
                    $createdBy = optional($row->auth_user)->name ?? 'N/A'; 
                    return $createdBy;
                })

                ->editColumn('employee_code', fn($row) => $row->employee->employee_code ?? 'N/A')
                ->addColumn('action', function ($row) {
                    return '
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                <i data-feather="more-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="' . route('logistics.driver.edit', $row->id) . '">
                                    <i data-feather="edit-3" class="me-50"></i>
                                    <span>Edit</span>
                                </a>
                            </div>
                        </div>';
                })
                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('logistics.drivers.index');
    }


    public function create()
    {
        $user = Helper::getAuthenticatedUser();
        $status = ConstantHelper::STATUS;
        $employees = Employee::where('organization_id',$user->organization_id)->where('status', 'active')->get();
       
        return view('logistics.drivers.create', compact('status','employees'));
    }
    
   public function store(DriverRequest $request)
{
    $user = Helper::getAuthenticatedUser();
    $organization = $user->organization;
    $validated = $request->validated();

    DB::beginTransaction();

    try {
        $driver = ErpDriver::create([
            'organization_id'     => $organization->id,
            'group_id'            => $organization->group_id,
            'company_id'          => $user->company_id ?? null,
            'user_id'             => $validated['user_id'],
            'name'                => $validated['name'],
            'email'               => $validated['email'] ?? null,
            'mobile_no'           => $validated['mobile_no'],
            'experience_years'    => $validated['experience_years'] ?? null,
            'license_no'          => $validated['license_no'],
            'license_expiry_date' => $validated['license_expiry_date'],
            'created_by'          => $user->auth_user_id ,
            'status'              => $request->status,
        ]);

        $this->handleDriverMediaUploads($request, $driver);

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Record created successfully',
            'data' => $driver->load('licenseFrontMedia', 'licenseBackMedia'), 
        ]);
    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => false,
            'message' => 'An error occurred while creating the driver',
            'error' => $e->getMessage(),
        ], 500);
    }
}



    protected function handleDriverMediaUploads(Request $request, ErpDriver $driver)
    {
        $mediaFields = ['license_front', 'license_back', 'id_proof_front', 'id_proof_back'];

        foreach ($mediaFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);

                $path = $file->store('driver_uploads', 'public'); // use 'public' disk

                $media = ErpDriverMedia::create([
                    'model_type'        => ErpDriver::class,
                    'model_id'          => $driver->id,
                    'uuid'              => (string) Str::uuid(),
                    'model_name'        => 'ErpDriver',
                    'collection_name'   => $field,
                    'name'              => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
                    'file_name'         => basename($path),
                    'mime_type'         => $file->getMimeType() ?? 'application/octet-stream',
                    'disk'              => 'public',
                    'conversions_disk'  => null,
                    'size'              => $file->getSize() ?? 0,
                    'manipulations'     => json_encode([]),
                    'custom_properties' => json_encode([]),
                    'generated_conversions' => json_encode([]),
                    'responsive_images' => json_encode([]),
                    'order_column'      => 1,
                ]);
                $driver->{$field} = $media->id;
            }
        }

        $driver->save();
    }

    

    public function edit($id)
    {
        $driver = ErpDriver::with('employee','licenseFrontMedia', 'licenseBackMedia', 'idProofFrontMedia', 'idProofBackMedia')->findOrFail($id);
        $user = Helper::getAuthenticatedUser();
        $status = ConstantHelper::STATUS;
        $employees = Employee::where('organization_id',$user->organization_id)->where('status', 'active')->get();
        return view('logistics.drivers.edit', compact('driver', 'status', 'employees'));
    }

    public function update(DriverRequest $request, $id)
    {
    
        $user = Helper::getAuthenticatedUser();
        $organization = $user->organization;
        $validated = $request->validated();

        DB::beginTransaction();

        try {
            $driver = ErpDriver::findOrFail($id);

            $driver->update([
                'organization_id'     => $organization->id,
                'group_id'            => $organization->group_id,
                'company_id'          => $user->company_id ?? null,
                'user_id'             => $validated['user_id'],
                'name'                => $validated['name'],
                'email'               => $validated['email'] ?? null,
                'mobile_no'           => $validated['mobile_no'],
                'experience_years'    => $validated['experience_years'] ?? null,
                'license_no'          => $validated['license_no'],
                'license_expiry_date' => $validated['license_expiry_date'],
                'updated_by'          => $user->auth_user_id ,
                'status'              => $request->status,
            ]);

            $this->handleDriverMediaUploads($request, $driver); 

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Record updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the driver',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $driver = ErpDriver::findOrFail($id);

            $mediaItems = ErpDriverMedia::where('model_type', ErpDriver::class)
                ->where('model_id', $driver->id)
                ->get();

            foreach ($mediaItems as $media) {
                if ($media->file_name && $media->disk === 'public') {
                    Storage::disk('public')->delete('driver_uploads/' . $media->file_name);
                }
                $media->delete(); 
            }

            $driver->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the driver: ' . $e->getMessage()
            ], 500);
        }
    }



}
