@extends('layouts.app')

@section('content')
<form method="POST" action="{{ route('logistics.vehicle.update', $vehicle->id) }}" enctype="multipart/form-data" data-redirect="{{ url('/logistics/vehicle') }}" class="ajax-input-form">
    @csrf
   
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <h2 class="content-header-title float-start mb-0">Edit Vehicle</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>  
                                <li class="breadcrumb-item active">Edit</li>
                            </ol>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2">
                        <button onClick="javascript:history.go(-1)" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</button>
                         <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                    data-url="{{ route('logistics.vehicle.destroy', $vehicle->id) }}" 
                                    data-redirect="{{ route('logistics.vehicle.index') }}"
                                    data-message="Are you sure you want to delete this vehicle?">
                                <i data-feather="trash-2" class="me-50"></i> Delete
                            </button>  
                        <button type="submit" class="btn btn-primary btn-sm" id="submit-button"><i data-feather="check-circle"></i> Update</button> 
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">  
                            <div class="card">
                                <div class="card-body customernewsection-form">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h4 class="card-title text-theme">Basic Information</h4>
                                        </div> 

                                        <div class="col-md-9"> 
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2"> 
                                                    <label class="form-label">Organization Name <span class="text-danger">*</span></label>  
                                                </div>  
                                                <div class="col-md-4"> 
                                                    <select name="transporter_id" class="form-select select2">
                                                        <option value="">Select</option>
                                                         @foreach ($groupOrganizations as $org)
                                                            <option value="{{ $org->id }}" {{ old('transporter_id', $vehicle->transporter_id) == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                                                        @endforeach
                                                        
                                                    </select>
                                                </div> 
                                              
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                  <div class="col-md-2"> 
                                                    <label class="form-label">Vehicle No. <span class="text-danger">*</span> <i class="ml-2 fas fa-info-circle text-primary" data-bs-toggle="tooltip" data-bs-html="true" title="" data-bs-original-title="Format:&lt;br&gt;[A-Z]{2} – 2 uppercase letters (e.g., 'MH')&lt;br&gt;[0-9]{2} – 2 digits (e.g., '12')&lt;br&gt;[A-Z]{0,3} – 0 to 3 uppercase letters (e.g., 'AB', 'ABZ')&lt;br&gt;[0-9]{4} – 4 digits (e.g., '1234')" aria-label="Format:&lt;br&gt;[A-Z]{2} – 2 uppercase letters (e.g., 'MH')&lt;br&gt;[0-9]{2} – 2 digits (e.g., '12')&lt;br&gt;[A-Z]{0,3} – 0 to 3 uppercase letters (e.g., 'AB', 'ABZ')&lt;br&gt;[0-9]{4} – 4 digits (e.g., '1234')"></i></label>  
                                                </div>  
                                                <div class="col-md-4"> 
                                                    <input type="text" class="form-control" name="lorry_no" value="{{ old('lorry_no', $vehicle->lorry_no) }}" placeholder="UP65AA123" />
                                                </div> 
                                                <div class="col-md-2"> 
                                                    <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>  
                                                </div>  
                                                <div class="col-md-4"> 
                                                <select name="vehicle_type_id" id="vehicle_type_id" class="form-select select2">
                                                    <option value="">Select</option>
                                                    @foreach($vehicleTypes as $type)
                                                        <option value="{{ $type->id }}" 
                                                           data-capacity="{{ optional($type)->capacity }}"
                                                           data-unit="{{ optional(optional($type)->unit)->name }}"
                                                            {{ old('vehicle_type_id', $vehicle->vehicle_type_id ?? '') == $type->id ? 'selected' : '' }}>
                                                            {{ $type->name }} 
                                                        </option>
                                                    @endforeach
                                                </select>
                                                </div> 
                                                
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2"> 
                                                    <label class="form-label">Chassis No. <span class="text-danger">*</span></label>  
                                                </div>  
                                                <div class="col-md-4"> 
                                                    <input type="text" class="form-control" name="chassis_no" value="{{ old('chassis_no', $vehicle->chassis_no) }}" placeholder="MA12EF34G5678" />
                                                </div> 

                                                <div class="col-md-2"> 
                                                    <label class="form-label">Engine No. <span class="text-danger">*</span></label>  
                                                </div>  
                                                <div class="col-md-4"> 
                                                    <input type="text" class="form-control" name="engine_no" value="{{ old('engine_no', $vehicle->engine_no) }}" placeholder="ABC1234567" />
                                                </div> 
                                                 
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2"> 
                                                    <label class="form-label">RC No. </label>  
                                                </div>  
                                                <div class="col-md-4"> 
                                                    <input type="text" class="form-control" name="rc_no" value="{{ old('rc_no', $vehicle->rc_no) }}" placeholder="RC NO" />
                                                </div>

                                                <div class="col-md-2"> 
                                                    <label class="form-label">RTO No. </label>  
                                                </div>  
                                                <div class="col-md-4"> 
                                                    <input type="text" class="form-control" name="rto_no" value="{{ old('rto_no', $vehicle->rto_no) }}" placeholder="UP65" />
                                                </div> 
                                                
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2"> 
                                                    <label class="form-label">Vehicle Company </label>  
                                                </div>  
                                                <div class="col-md-4"> 
                                                    <input type="text" class="form-control" name="company_name" value="{{ old('company_name', $vehicle->company_name) }}" placeholder="COMPANY'S NAME" />
                                                </div> 
                                                <div class="col-md-2"> 
                                                    <label class="form-label">Model Name </label>  
                                                </div>  
                                                <div class="col-md-4"> 
                                                    <input type="text" class="form-control" name="model_name" value="{{ old('model_name', $vehicle->model_name) }}" placeholder="ABC123-XY-z" />
                                                </div> 
                                            </div>
                                        </div>

                                     <div class="col-md-3 border-start">
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-12">
                                                    <label class="form-label">Status</label>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="demo-inline-spacing">
                                                        @foreach ($status as $statusOption)
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="status_{{ $statusOption }}" 
                                                                    name="status" 
                                                                    value="{{ $statusOption }}" 
                                                                    class="form-check-input"
                                                                    {{ old('status', $vehicle->status) === $statusOption ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder" for="status_{{ $statusOption }}">
                                                                    {{ ucfirst($statusOption) }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                    </div>
                                    <div class="mt-1">
                                            <ul class="nav nav-tabs border-bottom mt-25" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" data-bs-toggle="tab" href="#other_details">Other Details</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#permit_info">Permit Info</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#fitness_info">Fitness Info</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#insurance_info">Insurance Info</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#pollution_info">Pollution Info</a>
                                                </li>
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#road_tax">Road Tax Info</a>
                                                </li>
                                            </ul>

                                            <div class="tab-content pb-1 px-1">
                                    <!-- Other Details Tab -->
                                    <div class="tab-pane active" id="other_details">
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-2">
                                                <label class="form-label">Capacity</label>
                                            </div>
                                            <div class="col-md-3">
                                            <input type="text" class="form-control" id="display_capacity" placeholder="e.g. 5000 kg" disabled>
                                            <input type="number" name="capacity_kg" id="capacity_kg" value="{{ old('capacity_kg', $vehicle->capacity_kg ?? '') }}" hidden>
                                        </div>
                                    </div>
                                            <div class="row align-items-center mb-1">
                                            <div class="col-md-2">
                                                <label class="form-label">Driver Name </label>
                                            </div>
                                            <div class="col-md-3">
                                                <select name="driver_id" id="driver_id" class="form-select select2">
                                                    <option value="">Select</option>
                                                    @foreach($drivers as $driver)
                                                        <option value="{{ $driver->id }}"
                                                            {{ (old('driver_id', $vehicle->driver_id ?? '') == $driver->id) ? 'selected' : '' }}>
                                                            {{ $driver->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-2">
                                                <label class="form-label">Fuel Type </label>
                                            </div>
                                            <div class="col-md-3">
                                                <select name="fuel_type" id="fuel_type" class="form-select select2">
                                                    <option value="">Select</option>
                                                    @foreach($fuelTypes as $value => $label)
                                                        <option value="{{ $value }}"
                                                            {{ (old('fuel_type', $vehicle->fuel_type ?? '') == $value) ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                            <div class="col-md-2">
                                                <label class="form-label">Purchase Date ></label>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="date" class="form-control" name="purchase_date"
                                                    value="{{ old('purchase_date', $vehicle->purchase_date ?? '') }}" />
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-2">
                                                <label class="form-label">Ownership </label>
                                            </div>
                                            <div class="col-md-3">
                                                <select name="ownership" id="ownership" class="form-select select2">
                                                    <option value="">Select</option>
                                                    @foreach($ownership as $value => $owner)
                                                        <option value="{{ $value }}"
                                                            {{ (old('ownership', $vehicle->ownership ?? '') == $value) ? 'selected' : '' }}>
                                                            {{ $owner }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                             </div>

                                            <div class="row  mb-1">
                                            <div class="col-md-2">
                                                <label class="form-label">Vehicle Photo</label>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="file" class="form-control" name="vehicle_attachment" onchange="simpleFileValidation(this)">
                                                 <span class="text-danger font-small-2">File size should be Min: 10KB and Max: 2MB (JPG, JPEG, PNG)</span>
                                                @if(isset($vehicle->vehicleAttachment) && $vehicle->vehicleAttachment->url)
                                                    <div class="mt-1">
                                                        <img src="{{ $vehicle->vehicleAttachment->url }}" alt="Vehicle Attachment" style="max-width: 200px; height: auto; border: 1px solid #ccc; border-radius: 4px;">
                                                    </div>
                                                @endif


                                            </div>
                                        </div>

                                        <div class="row  mb-1">
                                            <div class="col-md-2">
                                                <label class="form-label">Vehicle Video</label>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="file" class="form-control" name="vehicle_video" onchange="simpleVideoFileValidation(this)"/>
                                                 <span class="text-danger font-small-2">File size should be Min: 10KB and Max: 20MB (Mp4, Mkv)</span>
                                              @if(isset($vehicle->vehicleVideo) && $vehicle->vehicleVideo->url)
                                                <div class="mt-1">
                                                    <video width="320" height="240" controls>
                                                        <source src="{{ $vehicle->vehicleVideo->url }}" type="video/mp4">
                                                        Your browser does not support the video tag.
                                                    </video>
                                                </div>
                                            @endif

                                            </div>
                                             </div>

                                             <div class="row  mb-1">
                                            <div class="col-md-2">
                                                <label class="form-label">RC Attachments</label>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="file" class="form-control" name="rc_attachment" onchange="simpleFileValidation(this)"/>
                                               <span class="text-danger font-small-2">File size should be Min: 10KB and Max: 2MB (JPG, JPEG, PNG)</span>
                                               @if(isset($vehicle->attachment) && $vehicle->attachment->url)
                                                <div class="mt-1">
                                                    <img src="{{ $vehicle->attachment->url }}" alt="RC Image" style="max-width: 200px; height: auto; border: 1px solid #ccc; border-radius: 4px;">
                                                </div>
                                            @endif

                                            </div>
                                        </div>
                                    </div>

                                               <!-- Permit Info Tab -->
                                                <div class="tab-pane" id="permit_info">
                                                      <div class="row align-items-center mb-1">
                                                    <div class="col-md-2"> 
                                                        <label class="form-label">Type </label>  
                                                    </div>  
                                                    <div class="col-md-3"> 
                                                       <select name="type" id="type" class="form-select select2">
                                                        <option value="">Select</option>
                                                        <option value="1_year" {{ old('type', $vehicle->permit->type ?? '') == '1_year' ? 'selected' : '' }}>1 Year</option>
                                                        <option value="5_year" {{ old('type', $vehicle->permit->type ?? '') == '5_year' ? 'selected' : '' }}>5 Year</option>
                                                    </select>

                                                    </div> 
                                                    </div> 
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Permit Number </label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="text" class="form-control" name="permit_no" value="{{ old('permit_no', $vehicle->permit->permit_no) }}" placeholder="Permit No." />
                                                        </div> 
                                                         </div> 
                                                          <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Permit date </label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="permit_date" value="{{ old('permit_date', $vehicle->permit->permit_date) }}" placeholder="YYYY-MM-DD"/>
                                                        </div> 
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Permit Expiry date </label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="permit_expiry_date" value="{{ old('permit_expiry_date', $vehicle->permit->permit_expiry_date) }}" placeholder="YYYY-MM-DD"/>
                                                        </div> 
                                                         </div> 
                                                          <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Amount</label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="text" class="form-control" name="permit_amount" value="{{ old('permit_amount', $vehicle->permit->amount) }}" placeholder="0" />
                                                        </div> 
                                                    </div>
                                                    <div class="row  mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Attachment</label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="file" class="form-control" name="permit_attachment" onchange="simpleFileValidation(this)"/>
                                                             <span class="text-danger font-small-2">File size should be Min: 10KB and Max: 2MB (JPG, JPEG, PNG, PDF)</span>
                                                           @if(isset($vehicle->permit->permitAttachment) && $vehicle->permit->permitAttachment->url)
                                                                <div class="mt-1">
                                                                    <img src="{{ $vehicle->permit->permitAttachment->url }}" alt="Permit Attachment" style="max-width: 200px; height: auto;">
                                                                </div>
                                                            @endif


                                                        </div>  
                                                    </div>
                                                </div>
                                                
                                                <!-- Fitness Info Tab -->
                                                <div class="tab-pane" id="fitness_info">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2"> 
                                                        <label class="form-label">Fitness Number </label>  
                                                    </div>  
                                                    <div class="col-md-3"> 
                                                        <input type="text" class="form-control" name="fitness_no" placeholder="Fitness No." 
                                                            value="{{ old('fitness_no', $vehicle->fitness->fitness_no ?? '') }}" />
                                                    </div> 
                                                    </div>
                                                    <div class="row align-items-center mb-1">
                                                    <div class="col-md-2"> 
                                                        <label class="form-label">Fitness Date </label>  
                                                    </div>  
                                                    <div class="col-md-3"> 
                                                        <input type="date" class="form-control" name="fitness_date" 
                                                            value="{{ old('fitness_date', isset($vehicle->fitness->fitness_date) ? \Carbon\Carbon::parse($vehicle->fitness->fitness_date)->format('Y-m-d') : '') }}" />
                                                    </div> 
                                                </div>
                                                
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2"> 
                                                        <label class="form-label">Fitness Expiry Date </label>  
                                                    </div>  
                                                    <div class="col-md-3"> 
                                                        <input type="date" class="form-control" name="fitness_expiry_date" 
                                                            value="{{ old('fitness_expiry_date', isset($vehicle->fitness->fitness_expiry_date) ? \Carbon\Carbon::parse($vehicle->fitness->fitness_expiry_date)->format('Y-m-d') : '') }}" />
                                                    </div>
                                                     </div>
                                                    <div class="row align-items-center mb-1"> 
                                                    <div class="col-md-2"> 
                                                        <label class="form-label">Amount</label>  
                                                    </div>  
                                                    <div class="col-md-3"> 
                                                        <input type="text" class="form-control" name="fitness_amount" placeholder="0" 
                                                            value="{{ old('fitness_amount', $vehicle->fitness->amount ?? '') }}" />
                                                    </div>
                                                </div>

                                                <div class="row  mb-1">
                                                    <div class="col-md-2"> 
                                                        <label class="form-label">Attachment</label>  
                                                    </div>  
                                                    <div class="col-md-3"> 
                                                        <input type="file" class="form-control" name="fitness_attachment" onchange="simpleFileValidation(this)"/>
                                                         <span class="text-danger font-small-2">File size should be Min: 10KB and Max: 2MB (JPG, JPEG, PNG, PDF)</span>
                                                        @if(isset($vehicle->fitness->fitnessAttachment) && $vehicle->fitness->fitnessAttachment->url)
                                                        <div class="mt-1">
                                                            <img src="{{ $vehicle->fitness->fitnessAttachment->url }}" alt="Fitness Attachment" style="max-width: 200px; height: auto;">
                                                        </div>
                                                    @endif

                                                    </div>  
                                                </div>
                                            </div>

                                                
                                               <!-- Insurance Info Tab -->
                                                <div class="tab-pane" id="insurance_info">
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Policy Number </label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="text" class="form-control" name="policy_no" placeholder="Policy No."
                                                                value="{{ old('policy_no', $vehicle->insurance->policy_no ?? '') }}" />
                                                        </div>
                                                        </div>  
                                                         <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Insurance Date </label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="insurance_date"
                                                                value="{{ old('insurance_date', isset($vehicle->insurance->insurance_date) ? \Carbon\Carbon::parse($vehicle->insurance->insurance_date)->format('Y-m-d') : '') }}" />
                                                        </div> 
                                                    </div>
                                                    
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Insurance Expiry Date </label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="insurance_expiry_date"
                                                                value="{{ old('insurance_expiry_date', isset($vehicle->insurance->insurance_expiry_date) ? \Carbon\Carbon::parse($vehicle->insurance->insurance_expiry_date)->format('Y-m-d') : '') }}" />
                                                        </div> 
                                                        </div> 
                                                        <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Insurance Company </label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="text" class="form-control" name="insurance_company" placeholder="Company Name"
                                                                value="{{ old('insurance_company', $vehicle->insurance->insurance_company ?? '') }}" />
                                                        </div> 
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Amount</label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="text" class="form-control" name="insurance_amount" placeholder="0"
                                                                value="{{ old('insurance_amount', $vehicle->insurance->amount ?? '') }}" />
                                                        </div>
                                                        </div>
                                                        <div class="row  mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Attachment</label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="file" class="form-control" name="insurance_attachment" onchange="simpleFileValidation(this)"/>
                                                             <span class="text-danger font-small-2">File size should be Min: 10KB and Max: 2MB (JPG, JPEG, PNG, PDF)</span>
                                                          @if(isset($vehicle->insurance->insuranceAttachment) && $vehicle->insurance->insuranceAttachment->url)
                                                                <div class="mt-1">
                                                                    <img src="{{ $vehicle->insurance->insuranceAttachment->url }}" alt="Insurance Attachment" style="max-width: 200px; height: auto;">
                                                                </div>
                                                            @endif

                                                        </div>  
                                                    </div>
                                                </div>

                                                
                                               <!-- Pollution Info Tab -->
                                                <div class="tab-pane" id="pollution_info">
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Pollution Number </label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="text" class="form-control" name="pollution_no" placeholder="PUC No."
                                                                value="{{ old('pollution_no', $vehicle->pollution->pollution_no ?? '') }}" />
                                                        </div> 
                                                        </div>
                                                         <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Pollution Date </label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="pollution_date"
                                                                value="{{ old('pollution_date', isset($vehicle->pollution->pollution_date) ? \Carbon\Carbon::parse($vehicle->pollution_date)->format('Y-m-d') : '') }}" />
                                                        </div> 
                                                    </div>
                                                    
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Pollution Expiry Date </label>  
                                                        </div>  
                                                        <div class="col-md-4"> 
                                                            <input type="date" class="form-control" name="pollution_expiry_date"
                                                                value="{{ old('pollution_expiry_date', isset($vehicle->pollution->pollution_expiry_date) ? \Carbon\Carbon::parse($vehicle->pollution->pollution_expiry_date)->format('Y-m-d') : '') }}" />
                                                        </div> 
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Amount</label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="text" class="form-control" name="pollution_amount" placeholder="0"
                                                                value="{{ old('pollution_amount', $vehicle->pollution->amount ?? '') }}" />
                                                        </div>
                                                        </div>
                                                        <div class="row  mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Attachment</label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="file" class="form-control" name="pollution_attachment" onchange="simpleFileValidation(this)"/>
                                                            <span class="text-danger font-small-2">File size should be Min: 10KB and Max: 2MB (JPG, JPEG, PNG, PDF)</span>
                                                             @if(isset($vehicle->pollution->pollutionAttachment) && $vehicle->pollution->pollutionAttachment->url)
                                                                <div class="mt-1">
                                                                    <img src="{{ $vehicle->pollution->pollutionAttachment->url }}" alt="Pollution Attachment" style="max-width: 200px; height: auto;">
                                                                </div>
                                                            @endif

                                                          
                                                        </div>  
                                                    </div>
                                                </div>

                                                
                                               <!-- Road Tax Info Tab -->
                                                <div class="tab-pane" id="road_tax">
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Road Tax From </label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="road_tax_from"
                                                                value="{{ old('road_tax_from', isset($vehicle->roadTax->road_tax_from) ? \Carbon\Carbon::parse($vehicle->roadTax->road_tax_from)->format('Y-m-d') : '') }}" />
                                                        </div> 
                                                         </div> 
                                                        <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Road Tax To </label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="road_tax_to"
                                                                value="{{ old('road_tax_to', isset($vehicle->roadTax->road_tax_to) ? \Carbon\Carbon::parse($vehicle->roadTax->road_tax_to)->format('Y-m-d') : '') }}" />
                                                        </div> 
                                                    </div>
                                                    
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Road Tax Paid On </label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="road_paid_on"
                                                                value="{{ old('road_paid_on', isset($vehicle->roadTax->road_paid_on) ? \Carbon\Carbon::parse($vehicle->roadTax->road_paid_on)->format('Y-m-d') : '') }}" />
                                                        </div> 
                                                        </div> 
                                                        <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Road Tax Amount</label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="number" class="form-control" name="road_tax_amount" placeholder="₹ Amount"
                                                                value="{{ old('road_tax_amount', $vehicle->roadTax->road_tax_amount ?? '') }}" />
                                                        </div> 
                                                    </div>

                                                    <div class="row  mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Attachment</label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="file" class="form-control" name="road_tax_attachment" onchange="simpleFileValidation(this)">
                                                            <span class="text-danger font-small-2">File size should be Min: 10KB and Max: 2MB (JPG, JPEG, PNG, PDF)</span>
                                                            @if(isset($vehicle->roadTax->roadTaxAttachment) && $vehicle->roadTax->roadTaxAttachment->url)
                                                                <div class="mt-1">
                                                                    <img src="{{ $vehicle->roadTax->roadTaxAttachment->url }}" alt="Road Tax Attachment" style="max-width: 200px; height: auto;">
                                                                </div>
                                                            @endif

                                                         
                                                        </div>  
                                                    </div>
                                                </div>

                                            </div> 
                                        </div>
                                 
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</form>
    <!-- END: Content-->
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        $('#vehicle_type_id').on('change', function () {
            var selected = $(this).find('option:selected');

            var capacity = selected.data('capacity') || '';
            var unit = selected.data('unit') || '';

            $('#display_capacity').val(capacity + ' ' + unit); 
            $('#capacity_kg').val(capacity); 
        });
       
        $('#vehicle_type_id').trigger('change');
    });
</script>
<script>
    const ALLOWED_EXTENSIONS_SIMPLE = ['pdf', 'jpg', 'jpeg', 'png'];
    const ALLOWED_MIME_TYPES_SIMPLE = ['application/pdf', 'image/jpeg', 'image/png'];
    const MAX_FILE_SIZE_SIMPLE = 2048; 

    function simpleFileValidation(element) {
        const input = element;
        const files = Array.from(input.files);
        const dt = new DataTransfer();

        for (let i = 0; i < files.length; i++) {
            const file = files[i];
            const fileExtension = file.name.split('.').pop().toLowerCase();
            const fileSize = (file.size / 1024).toFixed(2); 

            if (!ALLOWED_EXTENSIONS_SIMPLE.includes(fileExtension) || !ALLOWED_MIME_TYPES_SIMPLE.includes(file.type)) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Only PDF, JPG, JPEG, PNG files are allowed.',
                    icon: 'error',
                });
                input.value = '';
                return;
            }

            if (fileSize > MAX_FILE_SIZE_SIMPLE) {
                Swal.fire({
                    title: 'Error!',
                    text: 'File size must not exceed 2MB.',
                    icon: 'error',
                });
                input.value = '';
                return;
            }

            dt.items.add(file);
        }

        input.files = dt.files;
    }
</script>
<script>
    const VIDEO_ALLOWED_EXTENSIONS = ['mp4', 'mkv'];
    const VIDEO_ALLOWED_MIME_TYPES = ['video/mp4', 'video/x-matroska'];
    const VIDEO_MAX_FILE_SIZE = 20480; 


    function simpleVideoFileValidation(element) {
    const input = element;
    const files = Array.from(input.files);
    const dt = new DataTransfer();

    for (let i = 0; i < files.length; i++) {
        const file = files[i];
        const fileExtension = file.name.split('.').pop().toLowerCase();
        const fileSize = file.size / 1024;

        if (
            !VIDEO_ALLOWED_EXTENSIONS.includes(fileExtension) ||
            !VIDEO_ALLOWED_MIME_TYPES.includes(file.type)
        ) {
            Swal.fire('Error!', 'Only MP4 and MKV video files are allowed.', 'error');
            input.value = '';
            return;
        }

        if (fileSize > VIDEO_MAX_FILE_SIZE) {
            Swal.fire('Error!', 'File size must not exceed 20MB.', 'error');
            input.value = '';
            return;
        }

        dt.items.add(file);
    }

    input.files = dt.files;
}

</script>
@endsection