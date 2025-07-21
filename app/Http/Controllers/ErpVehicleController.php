<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\ConstantHelper;
use App\Http\Requests\VehicleRequest;
use App\Helpers\Helper; 
use App\Models\ErpVehicle;
use App\Models\ErpVehicleFitness;
use App\Models\ErpVehicleInsurance;
use App\Models\ErpVehiclePermit;
use App\Models\ErpVehiclePollution;
use App\Models\ErpVehicleRoadTax;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use App\Models\ErpVehicleMedia;
use Illuminate\Support\Facades\DB;
use Auth;
use App\Models\ErpVehicleType;
use App\Models\ErpDriver;
use App\Models\Organization;

class ErpVehicleController extends Controller
{
    public function index(Request $request)
    {
        $user = Helper::getAuthenticatedUser();
        $organization = Organization::find($user->organization_id);
        $organizationId = $organization?->id;
        $companyId = $organization?->company_id;

        $vehicleTypes = ErpVehicleType::withDefaultGroupCompanyOrg()->where('status', 'active')->get();
        $fuelTypes = ConstantHelper::FUEL_TYPES;
        $ownership = ConstantHelper::OWNERSHIP;
        $drivers = ErpDriver::where('organization_id',$user->organization_id)->where('status', 'active')->get();

        if ($request->ajax()) {
            $vehicles = ErpVehicle::with('driver', 'vehicleType', 'auth_user')
                ->withDefaultGroupCompanyOrg()
                ->orderByDesc('id');

            // Filters from request
        
            if ($request->filled('lorry_no')) {
                $vehicles->where('lorry_no', 'like', '%' . $request->lorry_no . '%');
            }

            if ($request->filled('vehicle_type')) {
                $vehicles->where('vehicle_type_id', $request->vehicle_type); 
            }

            if ($request->filled('chassis_no')) {
                $vehicles->where('chassis_no', 'like', '%' . $request->chassis_no . '%');
            }

            if ($request->filled('engine_no')) {
                $vehicles->where('engine_no', 'like', '%' . $request->engine_no . '%');
            }

            if ($request->filled('rc_no')) {
                $vehicles->where('rc_no', 'like', '%' . $request->rc_no . '%');
            }

            if ($request->filled('rto_no')) {
                $vehicles->where('rto_no', 'like', '%' . $request->rto_no . '%');
            }

            if ($request->filled('company_name')) {
                $vehicles->where('company_name', 'like', '%' . $request->company_name . '%');
            }

            if ($request->filled('model_name')) {
                $vehicles->where('model_name', 'like', '%' . $request->model_name . '%');
            }

            if ($request->filled('capacity_kg')) {
                $vehicles->where('capacity_kg', $request->capacity_kg);
            }

            if ($request->filled('fuel_type')) {
                $vehicles->where('fuel_type',  $request->fuel_type);
            }

            if ($request->filled('purchase_date')) {
                $vehicles->whereDate('purchase_date', $request->purchase_date);
            }

            if ($request->filled('ownership')) {
                $vehicles->where('ownership',  $request->ownership);
            }

           if ($request->filled('status')) {
                $vehicles->where('status', $request->status);
            }


            if ($request->filled('driver_name')) {
                $vehicles->where('driver_id', $request->driver_name);
            }

            return DataTables::of($vehicles)
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
                ->addColumn('driver_name', fn($row) => $row->driver->name ?? 'N/A')
                ->addColumn('vehicle_type', fn($row) => $row->vehicleType->name ?? 'N/A')


                ->addColumn('action', function ($row) {
                    return '
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm dropdown-toggle hide-arrow py-0" data-bs-toggle="dropdown">
                                <i data-feather="more-vertical"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a class="dropdown-item" href="' . route('logistics.vehicle.edit', $row->id) . '">
                                    <i data-feather="edit-3" class="me-50"></i>
                                    <span>Edit</span>
                                </a>
                            </div>
                        </div>';
                })

                ->rawColumns(['status', 'action'])
                ->make(true);
        }

        return view('logistics.vehicles.index',compact('drivers', 'fuelTypes', 'ownership', 'vehicleTypes'));
    }



    public function create()
    {
        $user = Helper::getAuthenticatedUser();
        $orgIds = $user->organizations()->pluck('organizations.id')->toArray();
            if ($user->organization_id) {
                $orgIds[] = $user->organization_id;
            }
        $groupOrganizations = Organization::whereIn('id', $orgIds)
            ->with('addresses')
            ->where('status', 'active')
            ->get();
       
        $status = ConstantHelper::STATUS;
        $fuelTypes = ConstantHelper::FUEL_TYPES;
        $ownership = ConstantHelper::OWNERSHIP;
        $drivers = ErpDriver::withDefaultGroupCompanyOrg()->where('status', 'active')->get();
        $vehicleTypes = ErpVehicleType::withDefaultGroupCompanyOrg()->where('status', 'active')->get();
       
        return view('logistics.vehicles.create', compact('status','drivers', 'fuelTypes', 'ownership', 'vehicleTypes', 'groupOrganizations'));
    }

       public function edit($id)
    {
        $vehicle = ErpVehicle::with('driver','fitness', 'pollution', 'permit', 'insurance', 'roadTax','attachment','vehicleAttachment', 'vehicleVideo')->findOrFail($id);
        $user = Helper::getAuthenticatedUser();
        $orgIds = $user->organizations()->pluck('organizations.id')->toArray();
            if ($user->organization_id) {
                $orgIds[] = $user->organization_id;
            }
        $groupOrganizations = Organization::whereIn('id', $orgIds)
            ->with('addresses')
            ->where('status', 'active')
            ->get();
        $status = ConstantHelper::STATUS;
        $fuelTypes = ConstantHelper::FUEL_TYPES;
        $ownership = ConstantHelper::OWNERSHIP;
        $drivers = ErpDriver::withDefaultGroupCompanyOrg()->where('status', 'active')->get();
        $vehicleTypes = ErpVehicleType::withDefaultGroupCompanyOrg()->where('status', 'active')->get();
        return view('logistics.vehicles.edit', compact('status','drivers', 'fuelTypes', 'ownership', 'vehicle', 'vehicleTypes', 'groupOrganizations'));
    }
    

    public function store(VehicleRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;

            $vehicle = ErpVehicle::create(array_merge(
                $request->only([
                    'transporter_id', 'lorry_no', 'vehicle_type_id', 'chassis_no', 'engine_no',
                    'rc_no', 'rto_no', 'company_name', 'model_name', 'capacity_kg',
                    'driver_id', 'fuel_type', 'purchase_date', 'ownership', 'status'
                ]),
                [
                    'organization_id' => $organization->id,
                    'group_id'        => $organization->group_id,
                    'company_id'      => $user->company_id ?? null,
                    'created_by'      => $user->auth_user_id ,
                ]
            ));

            $mediaIds = $this->handleVehicleMediaUploads($request, $vehicle);

            $vehicle->update([
            'attachment_id'      => $mediaIds['rc_attachment'] ?? $vehicle->attachment_id,
            'vehicle_attachment' => $mediaIds['vehicle_attachment'] ?? $vehicle->vehicle_attachment,
            'vehicle_video'      => $mediaIds['vehicle_video'] ?? $vehicle->vehicle_video,
        ]);

            $this->syncVehicleMetaModels($request, $vehicle->id, $mediaIds, false);


            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Record saved successfully.',
                'data'    => $vehicle
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Failed to store vehicle data.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function update(VehicleRequest $request, $id)
    {
            $user = Helper::getAuthenticatedUser();
            $organization = $user->organization;
          
     
        DB::beginTransaction();

        try {
            $vehicle = ErpVehicle::findOrFail($id);

            $vehicle->update([
                'transporter_id' => $request->transporter_id,
                'lorry_no'       => $request->lorry_no,
                'vehicle_type_id'=> $request->vehicle_type_id,
                'chassis_no'     => $request->chassis_no,
                'engine_no'      => $request->engine_no,
                'rc_no'          => $request->rc_no,
                'rto_no'         => $request->rto_no,
                'company_name'   => $request->company_name,
                'model_name'     => $request->model_name,
                'capacity_kg'    => $request->capacity_kg,
                'driver_id'      => $request->driver_id,
                'fuel_type'      => $request->fuel_type,
                'purchase_date'  => $request->purchase_date,
                'ownership'      => $request->ownership,
                'updated_by'     => $user->auth_user_id ,
                'status'         => $request->status,
            ]);

            $mediaIds = $this->handleVehicleMediaUploads($request, $vehicle);

          $vehicle->update([
            'attachment_id'      => $mediaIds['rc_attachment'] ?? $vehicle->attachment_id,
            'vehicle_attachment' => $mediaIds['vehicle_attachment'] ?? $vehicle->vehicle_attachment,
            'vehicle_video'      => $mediaIds['vehicle_video'] ?? $vehicle->vehicle_video,
        ]);


        $this->syncVehicleMetaModels($request, $vehicle->id, $mediaIds, true);


            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Record updated successfully.',
                'data'    => $vehicle
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'  => false,
                'message' => 'Failed to update vehicle data.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }



   protected function syncVehicleMetaModels($request, $vehicleId, $mediaIds, $isUpdate = false)
    {
        $models = [
            ErpVehicleFitness::class => [
                'fitness_date'          => 'fitness_date',
                'fitness_expiry_date'   => 'fitness_expiry_date',
                'fitness_no'            => 'fitness_no',
                'amount'                => 'fitness_amount',
                'attachment_key'        => 'fitness_attachment',
            ],
            ErpVehicleInsurance::class => [
                'policy_no'                => 'policy_no',
                'insurance_company'        => 'insurance_company',
                'insurance_date'           => 'insurance_date',
                'insurance_expiry_date'    => 'insurance_expiry_date',
                'amount'                   => 'insurance_amount',
                'attachment_key'           => 'insurance_attachment',
            ],
            ErpVehiclePermit::class => [
                'type'                   => 'type',
                'permit_no'              => 'permit_no',
                'permit_date'            => 'permit_date',
                'permit_expiry_date'     => 'permit_expiry_date',
                'amount'                 => 'permit_amount',
                'attachment_key'         => 'permit_attachment',
            ],
            ErpVehiclePollution::class => [
                'pollution_no'              => 'pollution_no',
                'pollution_date'            => 'pollution_date',
                'pollution_expiry_date'     => 'pollution_expiry_date',
                'amount'                    => 'pollution_amount',
                'attachment_key'            => 'pollution_attachment',
            ],
            ErpVehicleRoadTax::class => [
                'road_tax_from'       => 'road_tax_from',
                'road_tax_to'         => 'road_tax_to',
                'road_paid_on'        => 'road_paid_on',
                'road_tax_amount'     => 'road_tax_amount',
                'attachment_key'      => 'road_tax_attachment',
            ],
        ];

        foreach ($models as $modelClass => $fields) {
            $data = ['vehicle_id' => $vehicleId];

            foreach ($fields as $key => $value) {
                if ($key === 'attachment_key') {
                    if (array_key_exists($value, $mediaIds)) {
                        $data['attachment_id'] = $mediaIds[$value];
                    } elseif ($isUpdate) {
                        // preserve existing attachment_id if no new file uploaded
                        $existing = $modelClass::where('vehicle_id', $vehicleId)->first();
                        $data['attachment_id'] = $existing?->attachment_id;
                    }
                } else {
                    $data[$key] = $request->input($value);
                }
            }

            if ($isUpdate) {
                $modelClass::updateOrCreate(
                    ['vehicle_id' => $vehicleId],
                    $data
                );
            } else {
                $modelClass::create($data);
            }
        }
    }




    protected function handleVehicleMediaUploads(Request $request, ErpVehicle $vehicle)
    {
        $mediaIds = [];

        $mediaFields = [
            'vehicle_attachment',
            'vehicle_video',
            'rc_attachment',
            'fitness_attachment',
            'insurance_attachment',
            'permit_attachment',
            'pollution_attachment',
            'road_tax_attachment',
        ];

        foreach ($mediaFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $path = $file->store("vehicle_uploads/{$vehicle->id}", 'public');

                $media = ErpVehicleMedia::create([
                    'model_type'        => ErpVehicle::class,
                    'model_id'          => $vehicle->id,
                    'uuid'              => (string) Str::uuid(),
                    'model_name'        => 'ErpVehicle',
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

                $mediaIds[$field] = $media->id;
            }
        }

        return $mediaIds;
    }


    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $vehicle = ErpVehicle::with([
                'fitness',
                'permit',
                'insurance',
                'pollution',
                'roadTax'
            ])->findOrFail($id);

            $vehicleMedia = ErpVehicleMedia::where('model_type', ErpVehicle::class)
                ->where('model_id', $vehicle->id)
                ->get();

            foreach ($vehicleMedia as $media) {
                if ($media->file_name && $media->disk === 'public') {
                    Storage::disk('public')->delete("vehicle_uploads/{$vehicle->id}/{$media->file_name}");
                }
                $media->delete();
            }

            // Delete related model media
            $relatedModels = [
                'fitness'   => \App\Models\ErpVehicleFitness::class,
                'permit'    => \App\Models\ErpVehiclePermit::class,
                'insurance' => \App\Models\ErpVehicleInsurance::class,
                'pollution' => \App\Models\ErpVehiclePollution::class,
                'roadTax'   => \App\Models\ErpVehicleRoadTax::class,
            ];

            foreach ($relatedModels as $relation => $modelClass) {
                $related = $vehicle->$relation;

                if ($related) {
                    $mediaItems = ErpVehicleMedia::where('model_type', $modelClass)
                        ->where('model_id', $related->id)
                        ->get();

                    foreach ($mediaItems as $media) {
                        if ($media->file_name && $media->disk === 'public') {
                            Storage::disk('public')->delete("vehicle_uploads/{$vehicle->id}/{$media->file_name}");
                        }
                        $media->delete();
                    }

                    $related->delete();
                }
            }

        
            $vehicle->delete();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Record deleted successfully.'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the vehicle: ' . $e->getMessage()
            ], 500);
        }
    }

}
