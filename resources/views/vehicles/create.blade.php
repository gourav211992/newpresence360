@extends('layouts.app')

@section('content')
 <form method="POST" action="{{ route('vehicle.store') }}" enctype="multipart/form-data" data-redirect="{{ url('/vehicle') }}" class="ajax-input-form">
    @csrf
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">New Vehicle</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>  
                                        <li class="breadcrumb-item active">Add New</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <div class="form-group breadcrumb-right">   
                            <button onClick="javascript:history.go(-1)" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</button>  
                            <button type="submit" class="btn btn-primary btn-sm" id="submit-button"><i data-feather="check-circle"></i> Create</button> 
                        </div>
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
                                                <div class="newheader border-bottom mb-2 pb-25"> 
                                                    <h4 class="card-title text-theme">Basic Information</h4>
                                                    <p class="card-text">Fill the details</p> 
                                                </div>
                                            </div> 
                                            
                                            <div class="col-md-9"> 
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2"> 
                                                        <label class="form-label">Transporter Name <span class="text-danger">*</span></label>  
                                                    </div>  
                                                    <div class="col-md-4"> 
                                                        <select name="transporter_id" id="transporter_id" class="form-select select2">
                                                            <option value="">Select</option>
                                                            <option value="1">Transport A</option>
                                                            <option value="2">Test</option>
                                                           
                                                        </select>
                                                    </div> 
                                                    <div class="col-md-2"> 
                                                        <label class="form-label">Lorry No. <span class="text-danger">*</span></label>  
                                                    </div>  
                                                    <div class="col-md-4"> 
                                                        <input type="text" class="form-control" name="lorry_no" placeholder="UP65AA123" />
                                                    </div> 
                                                </div>
                                                
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2"> 
                                                        <label class="form-label">Vehicle Type <span class="text-danger">*</span></label>  
                                                    </div>  
                                                    <div class="col-md-4"> 
                                                        <select name="vehicle_type" id="vehicle_type" class="form-select select2">
                                                             <option value="">Select</option>
                                                             @foreach($vehicleTypes as $value => $label)
                                                                <option value="{{ $value }}">
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div> 
                                                    <div class="col-md-2"> 
                                                        <label class="form-label">Chassis No. <span class="text-danger">*</span></label>  
                                                    </div>  
                                                    <div class="col-md-4"> 
                                                        <input type="text" class="form-control" name="chassis_no" placeholder="MA12EF34G5678" />
                                                    </div> 
                                                </div>
                                                
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2"> 
                                                        <label class="form-label">Engine No. <span class="text-danger">*</span></label>  
                                                    </div>  
                                                    <div class="col-md-4"> 
                                                        <input type="text" class="form-control" name="engine_no" placeholder="ABC1234567" />
                                                    </div> 
                                                    <div class="col-md-2"> 
                                                        <label class="form-label">RC No. <span class="text-danger">*</span></label>  
                                                    </div>  
                                                    <div class="col-md-4"> 
                                                        <input type="text" class="form-control" name="rc_no" placeholder="RC NO" />
                                                    </div> 
                                                </div>
                                                    
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2"> 
                                                        <label class="form-label">RTO No. <span class="text-danger">*</span></label>  
                                                    </div>  
                                                    <div class="col-md-4"> 
                                                        <input type="text" class="form-control" name="rto_no" placeholder="UP65" />
                                                    </div> 
                                                    <div class="col-md-2"> 
                                                        <label class="form-label">Vehicle Company <span class="text-danger">*</span></label>  
                                                    </div>  
                                                    <div class="col-md-4"> 
                                                        <input type="text" class="form-control" name="company_name" placeholder="COMPANY'S NAME" />
                                                    </div> 
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2"> 
                                                        <label class="form-label">Model Name <span class="text-danger">*</span></label>  
                                                    </div>  
                                                    <div class="col-md-4"> 
                                                        <input type="text" class="form-control" name="model_name" placeholder="ABC123-XY-z" />
                                                    </div> 
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-3 border-start">
                                                <div class="row align-items-center mb-2">
                                                    <div class="col-md-12"> 
                                                        <label class="form-label text-primary"><strong>Status</strong></label>   
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="status_active" name="status" class="form-check-input" value="1" checked>
                                                                <label class="form-check-label fw-bolder" for="status_active">Active</label>
                                                            </div> 
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="status_inactive" name="status" class="form-check-input" value="0">
                                                                <label class="form-check-label fw-bolder" for="status_inactive">Inactive</label>
                                                            </div> 
                                                        </div> 
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
                                                            <label class="form-label">Capacity (kg) <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="number" class="form-control" name="capacity_kg" placeholder="e.g. 5000" />
                                                        </div> 
                                                        </div>
                                                        <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Driver Name <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <select name="driver_id" id="driver_id" class="form-select select2">
                                                                <option value="">Select</option>
                                                                @foreach($drivers as $driver)
                                                                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div> 
                                                    </div>
                                                    
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Fuel Type <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                           <select name="fuel_type" id="fuel_type" class="form-select select2">
                                                            <option value="">Select</option>
                                                            @foreach($fuelTypes as $value => $label)
                                                                <option value="{{ $value }}">
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        </div> 
                                                          </div>
                                                        <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Purchase Date <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="purchase_date" />
                                                        </div> 
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Ownership <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                           <select name="ownership" id="ownership" class="form-select select2">
                                                            <option value="">Select</option>
                                                            @foreach($ownership as $value => $owner)
                                                                <option value="{{ $value }}">
                                                                    {{ $owner }}
                                                                </option>
                                                            @endforeach
                                                        </select>  
                                                        </div>  
                                                        </div> 
                                                        <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Vehicle Attachments <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="file" class="form-control" name="vehicle_attachment" />
                                                            <span class="text-danger">Jpg,Png,Jpeg,Svg</span>
                                                        </div> 
                                                    </div>
                                                    
                                                    <div class="row align-items-center mb-1">
                                                         <div class="col-md-2"> 
                                                            <label class="form-label">Vehicle Video <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="file" class="form-control" name="vehicle_video" />
                                                            <span class="text-danger">Mp4,Mkv</span>
                                                        </div> 
                                                        </div> 
                                                         <div class="row align-items-center mb-1">

                                                         <div class="col-md-2"> 
                                                            <label class="form-label">RC Attachments <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="file" class="form-control" name="rc_attachment" />
                                                            <span class="text-danger">Jpg,Png,Jpeg,Svg</span>
                                                        </div> 
                                                    </div>
                                                </div>
                                                
                                                <!-- Permit Info Tab -->
                                                <div class="tab-pane" id="permit_info">
                                                  <div class="row align-items-center mb-1">
                                                    <div class="col-md-2"> 
                                                        <label class="form-label">Type <span class="text-danger">*</span></label>  
                                                    </div>  
                                                    <div class="col-md-3"> 
                                                        <select name="type" id="type" class="form-select select2">
                                                            <option value="">Select</option>
                                                            <option value="1_year">1 Year</option>
                                                            <option value="5_year">5 Year</option>
                                                           
                                                        </select>
                                                    </div> 
                                                    </div> 
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Permit Number <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="text" class="form-control" name="permit_no" placeholder="Permit No." />
                                                        </div>
                                                         </div>  
                                                        <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Permit date <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="permit_date" placeholder="YYYY-MM-DD"/>
                                                        </div> 
                                                    </div>
                                                    
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Permit Expiry date <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="permit_expiry_date" placeholder="YYYY-MM-DD"/>
                                                        </div> 
                                                         </div> 
                                                         <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Amount</label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="text" class="form-control" name="permit_amount" placeholder="0" />
                                                        </div> 
                                                    </div>
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Attachment</label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="file" class="form-control" name="permit_attachment" />
                                                            <span class="text-danger">Jpg,Png,Jpeg,Svg</span>
                                                        </div>  
                                                    </div>
                                                </div>
                                                
                                                <!-- Fitness Info Tab -->
                                                <div class="tab-pane" id="fitness_info">
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Fitness Number <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="text" class="form-control" name="fitness_no" placeholder="Fitness No." />
                                                        </div> 
                                                         </div> 
                                                        <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Fitness Date <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="fitness_date" />
                                                        </div> 
                                                    </div>
                                                    
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Fitness Expiry Date <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="fitness_expiry_date" />
                                                        </div> 
                                                        </div> 
                                                        <div class="row align-items-center mb-1">
                                                         <div class="col-md-2"> 
                                                            <label class="form-label">Amount</label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="text" class="form-control" name="fitness_amount" placeholder="0" />
                                                        </div>
                                                    </div>
                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Attachment</label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="file" class="form-control" name="fitness_attachment" />
                                                            <span class="text-danger">Jpg,Png,Jpeg,Svg</span>
                                                        </div>  
                                                    </div>
                                                </div>
                                                
                                                <!-- Insurance Info Tab -->
                                                <div class="tab-pane" id="insurance_info">
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Policy Number <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="text" class="form-control" name="policy_no" placeholder="Policy No." />
                                                        </div> 
                                                         </div> 
                                                        <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Insurance Date <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="insurance_date" />
                                                        </div> 
                                                    </div>
                                                    
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Insurance Expiry Date <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="insurance_expiry_date" />
                                                        </div> 
                                                         </div> 
                                                         <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Insurance Company <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="text" class="form-control" name="insurance_company" placeholder="Company Name" />
                                                        </div> 
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Amount</label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="text" class="form-control" name="insurance_amount" placeholder="0" />
                                                        </div>
                                                         </div>
                                                         <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Attachment</label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="file" class="form-control" name="insurance_attachment" />
                                                            <span class="text-danger">Jpg,Png,Jpeg,Svg</span>
                                                        </div>  
                                                    </div>
                                                </div>
                                                
                                                <!-- Pollution Info Tab -->
                                                <div class="tab-pane" id="pollution_info">
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Pollution Number <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="text" class="form-control" name="pollution_no" placeholder="PUC No." />
                                                        </div> 
                                                          </div>  
                                                        <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Pollution Date <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="pollution_date" />
                                                        </div> 
                                                    </div>
                                                    
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Pollution Expiry Date <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="pollution_expiry_date" />
                                                        </div> 
                                                    </div>
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Amount</label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="text" class="form-control" name="pollution_amount" placeholder="0" />
                                                        </div>
                                                        </div>
                                                        <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Attachment</label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="file" class="form-control" name="pollution_attachment" />
                                                            <span class="text-danger">Jpg,Png,Jpeg,Svg</span>
                                                        </div>  
                                                    </div>
                                                </div>
                                                
                                                <!-- Road Tax Info Tab -->
                                                <div class="tab-pane" id="road_tax">
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Road Tax From <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="road_tax_from" placeholder="YYYY-MM-DD" />
                                                        </div> 
                                                        </div>
                                                         <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Road Tax To <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="road_tax_to" />
                                                        </div> 
                                                    </div>
                                                    
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Road Tax Paid On <span class="text-danger">*</span></label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="date" class="form-control" name="road_paid_on" />
                                                        </div> 
                                                         </div> 
                                                         <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Road tax amount</label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="number" class="form-control" name="road_tax_amount" placeholder="₹ Amount" />
                                                        </div> 
                                                    </div>
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-2"> 
                                                            <label class="form-label">Attachment</label>  
                                                        </div>  
                                                        <div class="col-md-3"> 
                                                            <input type="file" class="form-control" name="road_tax_attachment" />
                                                            <span class="text-danger">Jpg,Png,Jpeg,Svg</span>
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