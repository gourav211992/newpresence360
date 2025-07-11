@extends('layouts.app')
@section('content')
<form class="ajax-input-form" method="POST" action="{{ route('logistics.lorry-receipt.update', $lr->id) }}"  data-redirect="{{ route('logistics.lorry-receipt.index') }}" id="lorry_receipt_form" enctype='multipart/form-data'>
    @csrf
    @method('PUT')
   <!-- BEGIN: Content-->
   <div class="app-content content ">
      <div class="content-overlay"></div>
      <div class="header-navbar-shadow"></div>
      <div class="content-wrapper container-xxl p-0">
         <div class="content-header pocreate-sticky">
            <div class="row">
               <div class="content-header-left col-md-6 mb-2">
                  <div class="row breadcrumbs-top">
                     <div class="col-12">
                        <h2 class="content-header-title float-start mb-0">Edit Lorry Receipt</h2>
                        <div class="breadcrumb-wrapper">
                           <ol class="breadcrumb">
                              <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                              <li class="breadcrumb-item active">Edit</li>
                           </ol>
                        </div>
                     </div>
                  </div>
               </div>
               <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                  <div class="form-group breadcrumb-right">
                    <a href="{{ route('logistics.lorry-receipt.index') }}" class="btn btn-secondary btn-sm">
                        <i data-feather="arrow-left-circle"></i> Back
                        </a>
                        @if(auth()->check() && $lr->created_by == optional(auth()->user())->auth_user_id)
                            <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                    data-url="{{ route('logistics.lorry-receipt.destroy', $lr->id) }}" 
                                    data-redirect="{{ route('logistics.lorry-receipt.index') }}"
                                    data-message="Are you sure you want to delete this record?">
                                <i data-feather="trash-2" class="me-50"></i> Delete
                            </button>
                        @endif
                           <!-- Save as Draft Button -->
                     @if(!isset(request()->revisionNumber))
                        @if(isset($buttons) && is_array($buttons) && isset($lr))
                           @if($buttons['draft'] ?? false)
                               <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" onclick="setStatusAndSubmit('draft')">
                                <i data-feather='save'></i> Save as Draft
                            </button>
                           @endif

                           @if($buttons['submit'] ?? false)
                              <button type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0" onclick="setStatusAndSubmit('submitted')">
                                <i data-feather="check-circle"></i> Submit
                            </button>
                           @endif

                           @if($buttons['approve'] ?? false)
                              <button type="button" id="reject-button" data-bs-toggle="modal" data-bs-target="#approveModal" onclick="setReject();" class="btn btn-danger btn-sm mb-50 mb-sm-0">
                                    <i data-feather="x-circle"></i> Reject
                              </button>
                              <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal" onclick="setApproval();">
                                    <i data-feather="check-circle"></i> Approve
                              </button>
                           @endif

                           @if($buttons['amend'] ?? false)
                              <button type="button" class="btn btn-primary btn-sm mb-50 mb-sm-0" id="amendShowButton">
                              <i data-feather="edit"></i> Amendment
                           </button>
                           @endif

                           @if($buttons['revoke'] ?? false)
                              <button type="button" class="btn btn-primary btn-sm mb-50 mb-sm-0" id="revokeButton">
                                    <i data-feather="rotate-ccw"></i> Revoke
                              </button>
                           @endif

                        @else
                           <button type="submit" onclick="submitForm('draft');" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" id="save-draft-button">
                              <i data-feather="save"></i> Save as Draft
                           </button>
                           <button type="submit" onclick="submitForm('submitted');" class="btn btn-primary btn-sm mb-50 mb-sm-0" id="submit-button">
                              <i data-feather="check-circle"></i> Submit
                           </button>
                        @endif
                        @endif     
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
                              <div class="col-md-6">
                                 <div class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                    <div>
                                       <h4 class="card-title text-theme">Basic Information</h4>
                                       <p class="card-text">Fill the details</p>
                                    </div>
                                 </div>
                              </div>
                                 @if (isset($lr) && isset($docStatusClass))
                                       <div class="col-md-6 text-sm-end">
                                          <span class="badge rounded-pill badge-light-secondary forminnerstatus">
                                                Status : <span class="{{$docStatusClass}}">{{$lr->document_status}}</span>
                                          </span>
                                       </div>
                                          
                                    @endif
                              
                              <div class="col-md-8">
                                 <div class="row align-items-center mb-1">
                                    <div class="col-md-3"> 
                                       <label class="form-label">Series <span class="text-danger">*</span></label>  
                                    </div>
                                    <div class="col-md-5">  
                                      
                                       <input type="hidden" name="status" id="statusInput" value="{{ old('status', $lr->status ?? 'draft') }}">
                                       <select class="form-select disable_on_edit" onchange = "getDocNumberByBookId(this);" name = "book_id" id = "series_id_input" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif>
                                       @foreach ($series as $currentSeries)
                                       <option value="{{ $currentSeries->id }}" {{ old('book_id', $lr->book_id) == $currentSeries->id ? 'selected' : '' }}>{{ $currentSeries->book_code }}</option>
                                       @endforeach
                                       </select>
                                    </div>
                                 </div>
                                 <div class="row align-items-center mb-1">
                                    <div class="col-md-3"> 
                                       <label class="form-label">Doc No <span class="text-danger">*</span></label>  
                                    </div>
                                    <div class="col-md-5"> 
                                       <input type="text" class="form-control" id="document_number" name="document_number" value="{{ old('document_number', $lr->document_number) }}" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif>
                                    </div>
                                 </div>
                                 <div class="row align-items-center mb-1">
                                    <div class="col-md-3"> 
                                       <label class="form-label">Doc Date <span class="text-danger">*</span></label>  
                                    </div>
                                    <div class="col-md-5"> 
                                       <input type="date" class="form-control" id="document_date" name="document_date" value="{{ old('document_date', $lr->document_date ? \Carbon\Carbon::parse($lr->document_date)->format('Y-m-d') : now()->format('Y-m-d')) }}">
                                    </div>
                                 </div>
                                 <div class="row align-items-center mb-1">
                                    <div class="col-md-3"> 
                                       <label class="form-label">Location <span class="text-danger">*</span></label>  
                                    </div>
                                    <div class="col-md-5">
                                       <select class="form-select select2" name="location" id="locationId" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif>
                                          <option value="">Select Location</option>
                                          @foreach($locations as $location)
                                          <option value="{{ $location->id }}" {{ old('location', $lr->location_id) == $location->id ? 'selected' : '' }}>{{ $location->store_name }}</option>
                                          @endforeach
                                       </select>
                                    </div>
                                 </div>
                                 <div class="row align-items-center mb-1">
                                    <div class="col-md-3"> 
                                       <label class="form-label">Cost Center <span class="text-danger">*</span></label>  
                                    </div>
                                    <div class="col-md-5">
                                       <select name="cost_center_id" id="cost_center_id" class="form-select select2" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif>
                                          <option value="">Select Cost Center</option>
                                       </select>
                                    </div>
                                 </div>
                              </div>
                              <div class="col-md-4">
                                  
                               @if(isset($lr) && ($lr->document_status !== "draft"))
                                                @if((isset($approvalHistory) && count($approvalHistory) > 0) || isset($lr->revision_number))
                                                        <div class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
                                                            <h5 class="mb-2 text-dark border-bottom pb-50 d-flex align-items-center justify-content-between">
                                                                <strong><i data-feather="arrow-right-circle"></i> Approval History</strong>
                                                                @if(!isset(request()->revisionNumber) && $lr->document_status !== 'draft')
                                                                    <strong class="badge rounded-pill badge-light-secondary amendmentselect">Rev. No.
                                                                        <select class="form-select" id="revisionNumber">
                                                                            @for($i=$lr->revision_number; $i >= 0; $i--)
                                                                                <option value="{{$i}}" {{request('revisionNumber', $lr->revision_number) == $i ? 'selected' : ''}}>{{$i}}</option>
                                                                            @endfor
                                                                        </select>
                                                                    </strong>
                                                                @else
                                                                    @if ($lr->document_status !== 'draft')
                                                                        <strong class="badge rounded-pill badge-light-secondary amendmentselect">
                                                                            Rev. No. {{ request()->revisionNumber }}
                                                                        </strong>
                                                                    @endif

                                                                @endif
                                                            </h5>
                                                            <ul class="timeline ms-50 newdashtimline ">
                                                                @foreach($approvalHistory as $approvalHist)
                                                                    <li class="timeline-item">
                                                                        <span class="timeline-point timeline-point-indicator"></span>
                                                                        <div class="timeline-event">
                                                                            <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                                <h6>{{ ucfirst($approvalHist->name ?? $approvalHist?->user?->name ?? 'NA') }}</h6>
                                                                                @if($approvalHist->approval_type == 'approve')
                                                                                    <span class="badge rounded-pill badge-light-success">{{ ucfirst($approvalHist->approval_type) }}</span>
                                                                                @elseif($approvalHist->approval_type == 'submit')
                                                                                    <span class="badge rounded-pill badge-light-primary">{{ ucfirst($approvalHist->approval_type) }}</span>
                                                                                @elseif($approvalHist->approval_type == 'reject')
                                                                                    <span class="badge rounded-pill badge-light-danger">{{ ucfirst($approvalHist->approval_type) }}</span>
                                                                                @else
                                                                                    <span class="badge rounded-pill badge-light-danger">{{ ucfirst($approvalHist->approval_type) }}</span>
                                                                                @endif
                                                                            </div>
                                                                            @if($approvalHist->approval_date)
                                                                                <h6>
                                                                                    {{ \Carbon\Carbon::parse($approvalHist->approval_date)->format('d-m-Y') }}
                                                                                </h6>
                                                                            @endif
                                                                            @if($approvalHist->remarks)
                                                                                <p>{!! $approvalHist->remarks !!}</p>
                                                                            @endif
                                                                            @if ($approvalHist->media && count($approvalHist->media) > 0)
                                                                                @foreach ($approvalHist->media as $mediaFile)
                                                                                    <p><a href="{{ $mediaFile->file_url }}" target="_blank">
                                                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download">
                                                                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                                                                <polyline points="7 10 12 15 17 10"></polyline>
                                                                                                <line x1="12" y1="15" x2="12" y2="3"></line>
                                                                                            </svg>
                                                                                        </a></p>
                                                                                @endforeach
                                                                            @endif
                                                                        </div>
                                                                    </li>
                                                                @endforeach

                                                            </ul>
                                                        </div>
                                                @endif
                                            @endif
                                            {{-- Approval History Section --}}
                              </div>
                           </div>
                        </div>
                     </div>
                     <div class="row">
                        <div class="col-md-12">
                           <div class="card quation-card">
                              <div class="card-header newheader">
                                 <div>
                                    <h4 class="card-title">General Information</h4>
                                 </div>
                              </div>
                              <div class="card-body">
                                 <div class="row">
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="source">Source <span class="text-danger">*</span></label>
                                          <input type="text" name="source_name" class="form-control mw-100 route-master-autocomplete"
                                             placeholder="Start typing  locations..." data-type="source"
                                             value="{{ old('source_name', $lr->source->name ?? '') }}" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif/>
                                          <input type="hidden" name="source_id" class="route-master-id" data-type="source"
                                             value="{{ old('source_id', $lr->source_id) }}" id="sourceIdInput" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif/>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="destination">Destination <span class="text-danger">*</span></label>
                                          <input type="text" name="destination_name" class="form-control mw-100 route-master-autocomplete"
                                             placeholder="Start typing  locations." data-type="destination"
                                             value="{{ old('destination_name', $lr->destination->name ?? '') }}" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif />
                                          <input type="hidden" name="destination_id" class="route-master-id" data-type="destination"
                                             value="{{ old('destination_id', $lr->destination_id) }}" id="destinationIdInput" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif/>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="consignor">Consignor <span class="text-danger">*</span></label>
                                          <input type="text" name="customer_name" class="form-control mw-100 customer-autocomplete"
                                             data-type="consignor" placeholder="Start typing customer..."
                                             value="{{ old('customer_name', $lr->consignor->company_name ?? '') }}"  @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif/>
                                          <input type="hidden" name="customer_id" class="customer-id" data-type="consignor"
                                             value="{{ old('customer_id', $lr->consignor_id) }}" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif/>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="consignee">Consignee <span class="text-danger">*</span></label>
                                          <input type="text" name="consignee_name" class="form-control mw-100 customer-autocomplete"
                                             data-type="consignee" placeholder="Start typing consignee..."
                                             value="{{ old('consignee_name', $lr->consignee->company_name ?? '') }}" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif/>
                                          <input type="hidden" name="consignee_id" class="customer-id" data-type="consignee"
                                             value="{{ old('consignee_id', $lr->consignee_id) }}" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif/>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="vehicle">Vehicle <span class="text-danger">*</span></label>
                                          <input type="text" name="vehicle_type_name" class="form-control mw-100 vehicle-type-autocomplete"
                                             placeholder="Select Vehicle" id="vehicle_type_name"
                                             value="{{ old('vehicle_type_name', $lr->vehicleType->name ?? '') }}" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif/>
                                          <input type="hidden" name="vehicle_type_id" class="vehicle-type-id"
                                             value="{{ old('vehicle_type_id', $lr->vehicle_type_id) }}" />
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="distance">Distance (Km) <span class="text-danger">*</span></label>
                                          <input type="text" class="form-control" id="distance" name="distance"
                                             placeholder="Enter Distance (Km)" value="{{ old('distance', $lr->distance) }}" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif/>
                                          <input type="hidden" class="form-control" id="distanceInput" name="distance"
                                             value="{{ old('distance', $lr->distance) }}" />
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="freight_charges">Freight Charges (Rs) <span class="text-danger">*</span></label>
                                          <input type="number" class="form-control" id="freight_charges" name="freight_charges"
                                             placeholder="Enter Freight Charges (Rs)" value="{{ old('freight_charges', $lr->freight_charges) }}" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif>
                                          <input type="hidden" class="form-control" id="freightCharges" name="freight_charges"
                                             value="{{ old('freight_charges', $lr->freight_charges) }}" />
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="driver">Driver <span class="text-danger">*</span></label>
                                          <input type="text" name="driver_name" class="form-control mw-100 driver-autocomplete"
                                             placeholder="Select Driver" data-type="driver"
                                             value="{{ old('driver_name', $lr->driver->name ?? '') }}" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif/>
                                          <input type="hidden" name="driver_id" class="driver-id" data-type="driver"
                                             value="{{ old('driver_id', $lr->driver_id) }}" />
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="driver_cash">Driver Cash (Rs)</label>
                                          <input type="number" class="form-control" id="driver_cash" name="driver_cash"
                                             placeholder="Enter Driver Cash (Rs)" value="{{ old('driver_cash', $lr->driver_cash) }}"  @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif/>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="fuel_price">Fuel Price (Rs)</label>
                                          <input type="number" class="form-control" id="fuel_price" name="fuel_price"
                                             placeholder="Enter Fuel Price (Rs)" value="{{ old('fuel_price', $lr->fuel_price) }}" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif/>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="invoice_no">Invoice No.</label>
                                          <input type="text" class="form-control" id="invoice_no" name="invoice_no"
                                             placeholder="Enter Invoice No." value="{{ old('invoice_no', $lr->invoice_no) }}" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif/>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="invoice_value">Invoice Value</label>
                                          <input type="text" class="form-control" id="invoice_value" name="invoice_value"
                                             placeholder="Enter Invoice Value" value="{{ old('invoice_value', $lr->invoice_value) }}" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif/>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="no_of_bundles">No of Article/Bundles <span class="text-danger">*</span></label>
                                          <input type="number" class="form-control" id="no_of_bundles" name="no_of_bundles"
                                             placeholder="Enter No of Article/Bundles" value="{{ old('no_of_bundles', $lr->no_of_bundles) }}" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif/>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="weight">Weight (kg) <span class="text-danger">*</span></label>
                                          <input type="number" class="form-control" id="weight" name="weight"
                                             placeholder="Enter Weight (kg)" value="{{ old('weight', $lr->weight) }}" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif/>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="ewaybill_no">E-Waybill No. <span class="text-danger">*</span></label>
                                          <input type="text" class="form-control" id="ewaybill_no" name="ewaybill_no"
                                             placeholder="Enter E-Waybill No." value="{{ old('ewaybill_no', $lr->ewaybill_no) }}" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif/>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="gst_paid_by">GST Paid By <span class="text-danger">*</span></label>
                                          <select class="form-select select2" id="gst_paid_by" name="gst_paid_by" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif>
                                             <option value="">Select</option>
                                             <option value="Consignor" {{ old('gst_paid_by', $lr->gst_paid_by) == 'Consignor' ? 'selected' : '' }}>Consignor</option>
                                             <option value="Consignee" {{ old('gst_paid_by', $lr->gst_paid_by) == 'Consignee' ? 'selected' : '' }}>Consignee</option>
                                             <option value="Transporter" {{ old('gst_paid_by', $lr->gst_paid_by) == 'Transporter' ? 'selected' : '' }}>Transporter</option>
                                          </select>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="lr_type">LR Type <span class="text-danger">*</span></label>
                                          <select class="form-select select2" id="lr_type" name="lr_type" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif>
                                             <option value="">Select</option>
                                             <option value="Inward" {{ old('lr_type', $lr->lr_type) == 'Inward' ? 'selected' : '' }}>Inward</option>
                                             <option value="Outward" {{ old('lr_type', $lr->lr_type) == 'Outward' ? 'selected' : '' }}>Outward</option>
                                          </select>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="billing_type">Billed or Pay <span class="text-danger">*</span></label>
                                          <select class="form-select select2" id="billing_type" name="billing_type" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif>
                                             <option value="">Select</option>
                                             <option value="To be Billed" {{ old('billing_type', $lr->billing_type) == 'To be Billed' ? 'selected' : '' }}>To be Billed</option>
                                             <option value="To Pay" {{ old('billing_type', $lr->billing_type) == 'To Pay' ? 'selected' : '' }}>To Pay</option>
                                          </select>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="load_type">Load Type</label>
                                          <select class="form-select" id="load_type" name="load_type" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif>
                                             <option value="">Select</option>
                                             @foreach(['FTL','Bulk','CEP','FCL','LCP','LTL'] as $type)
                                             <option value="{{ $type }}" {{ old('load_type', $lr->load_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                                             @endforeach
                                          </select>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="lr_charges">LR Charges</label>
                                          <select class="form-select" id="lr_charges" name="lr_charges" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif>
                                             <option value="">Select</option>
                                             @foreach($lorryCharges as $value)
                                             <option value="{{ $value }}" {{ old('lr_charges', $lr->lr_charges) == $value ? 'selected' : '' }}>{{ $value }}</option>
                                             @endforeach
                                          </select>
                                       </div>
                                    </div>
                                    <div class="col-md-3 mb-1"></div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <div class="card">
                     <div class="card-body customernewsection-form">
                        <div class="border-bottom mb-2 pb-25">
                           <div class="row">
                              <div class="col-md-6">
                                 <div class="newheader ">
                                    <h4 class="card-title text-theme">Multi Point Detail</h4>
                                    <p class="card-text">Fill the details</p>
                                 </div>
                              </div>
                              <div class="col-md-6 text-sm-end">
                                 @if($lr->document_status == 'submitted' || $lr->document_status == 'approved')

                                 <a href="#" class="btn btn-sm btn-outline-danger me-50" >
                                 <i data-feather="x-circle"></i> Delete
                                 </a>
                                 <a href="#"  class="btn btn-sm btn-outline-primary">
                                 <i data-feather="plus"></i> Add New Item
                                 </a>
                                 @else
                                 <a href="#" class="btn btn-sm btn-outline-danger me-50" id="deleteSelected">
                                 <i data-feather="x-circle"></i> Delete
                                 </a>
                                 <a href="#" id="addRowBtn" class="btn btn-sm btn-outline-primary">
                                 <i data-feather="plus"></i> Add New Item
                                 </a>
                                 @endif
                              </div>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-md-12">
                              <div class="table-responsive pomrnheadtffotsticky">
                                 <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                    <thead>
                                       <tr>
                                          <th width="53" class="customernewsection-form">
                                            <div class="form-check form-check-primary">
                                                <input type="checkbox" class="form-check-input" id="checkAll">
                                            </div>
                                          </th>
                                          <th width="271">Location <span class="text-danger">*</span></th>
                                          <th width="185">Type <span class="text-danger">*</span></th>
                                          <th width="197">No of Articles <span class="text-danger">*</span></th>
                                          <th width="197">Weight <span class="text-danger">*</span></th>
                                          <th width="246" class=" text-end">Freight(Rs) <span class="text-danger">*</span></th>
                                       </tr>
                                    </thead>
                                    <tbody class="mrntableselectexcel" id="item-table-body">
                                        @php 
                                        $total_weight = 0;
                                        $total_articles = 0;
                                        @endphp
                                       @forelse($lr->locations as $index => $location)
                                       @php 
                                       $total_weight += $location->weight;
                                       $total_articles += $location->no_of_articles;
                                       @endphp
                                       <tr>
                                          <td class="customernewsection-form">
                                             <div class="form-check form-check-primary custom-checkbox">
                                                <input type="checkbox" class="form-check-input rowCheckbox" name="locations[{{ $index }}][selected]" id="row_{{ $index }}">
                                                <label class="form-check-label" for="row_{{ $index }}"></label>
                                             </div>
                                          </td>
                                          <td class="poprod-decpt">
                                            <input type="hidden" name="locations[{{ $index }}][id]" value="{{ old("locations.$index.id", $location->id ?? '') }}">
                                             <input type="text" name="locations[{{ $index }}][location_name]" value="{{ old("locations.$index.location_name", optional($location->route)->name) }}" 
                                                placeholder="Select" class="form-control mw-100 location-update route-master-autocomplete"
                                                data-type="source" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif/>
                                             <input type="hidden" name="locations[{{ $index }}][location_id]" value="{{ $location->location_id ?? '' }}"
                                                class="route-master-id" data-type="source" />
                                          </td>
                                          <td>
                                             <select class="form-select mw-100" name="locations[{{ $index }}][type]" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif>
                                                <option value="">Select</option>
                                                <option value="Pick Up" {{ $location->type == 'Pick Up' ? 'selected' : '' }}>Pick Up</option>
                                                <option value="Drop Off" {{ $location->type == 'Drop Off' ? 'selected' : '' }}>Drop Off</option>
                                             </select>
                                          </td>
                                          <td><input type="text" name="locations[{{ $index }}][no_of_articles]" value="{{ $location->no_of_articles ?? '' }}" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif class="form-control mw-100" /></td>
                                          <td><input type="text" name="locations[{{ $index }}][weight]" value="{{ $location->weight ?? '' }}" @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif class="form-control mw-100" /></td>
                                          <td><input type="text" name="locations[{{ $index }}][freight]" value="{{ $location->amount ?? '' }}"  @if($lr->document_status == 'submitted' || $lr->document_status == 'approved') disabled @endif class="form-control mw-100 text-end" /></td>
                                       </tr>
                                       @empty
                                       <!-- If no existing points, render one empty row -->
                                       <tr>
                                          <td class="customernewsection-form">
                                             <div class="form-check form-check-primary custom-checkbox">
                                                <input type="checkbox" class="form-check-input rowCheckbox" name="locations[0][selected]" id="row_0">
                                                <label class="form-check-label" for="row_0"></label>
                                             </div>
                                          </td>
                                          <td class="poprod-decpt">
                                             <input type="text" name="locations[0][location_name]" placeholder="Select"
                                                class="form-control mw-100 location-update route-master-autocomplete" data-type="source" />
                                             <input type="hidden" name="locations[0][location_id]" class="route-master-id" data-type="source" />
                                          </td>
                                          <td>
                                             <select class="form-select mw-100" name="locations[0][type]">
                                                <option value="">Select</option>
                                                <option value="Pick Up">Pick Up</option>
                                                <option value="Drop Off">Drop Off</option>
                                             </select>
                                          </td>
                                          <td><input type="text" name="locations[0][no_of_articles]" class="form-control mw-100" /></td>
                                          <td><input type="text" name="locations[0][weight]" class="form-control mw-100" /></td>
                                          <td><input type="text" name="locations[0][freight]" class="form-control mw-100 text-end" /></td>
                                       </tr>
                                       @endforelse
                                    </tbody>
                                    <tfoot>
                                       <tr class="totalsubheadpodetail">
                                          <td colspan="5"></td>
                                          <td class="text-end" id="freightAmount">{{ number_format($lr->locations->sum('freight'), 2) }}</td>
                                       </tr>
                                       <tr valign="top">
                                          <td colspan="4" rowspan="10">
                                             <table class="table border" id="routeDetailsBox">
                                                <tr>
                                                   <td class="p-0">
                                                      <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Route Details</strong></h6>
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td class="poprod-decpt">
                                                      <span class="poitemtxt mw-100"><strong>Source</strong>: <span id="routeSource">{{ $lr->source->name ?? '-' }}</span></span>
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td class="poprod-decpt">
                                                      <span class="poitemtxt mw-100"><strong>Destination</strong>: <span id="routeDestination">{{ $lr->destination->name ?? '-' }}</span></span>
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td class="poprod-decpt">
                                                      <span class="badge rounded-pill badge-light-primary"><strong>Weight</strong>: <span id="routeWeight">{{ $total_weight ?? 0 }}</span></span>
                                                      <span class="badge rounded-pill badge-light-primary"><strong>No of Article</strong>: <span id="routeArticles">{{ $total_articles ?? 0 }}</span></span>
                                                      <span class="badge rounded-pill badge-light-primary"><strong>Points</strong>:<span id="routePoints"> {{ $lr->locations->count() }}</span></span>
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td class="poprod-decpt">
                                                      <span class="badge rounded-pill badge-light-primary"><strong>Vehicle</strong>: <span id="routeVehicle">{{ $lr->vehicleType->name ?? '-' }}</span></span>
                                                      <span class="badge rounded-pill badge-light-primary"><strong>Capacity</strong>: <span id="routeCapacity">{{ number_format($lr->vehicleType->capacity, 2) }} {{ $lr->vehicleType->unit->name ?? ''}}</span></span> 
                                                   </td>
                                                </tr>
                                             </table>
                                          </td>
                                          <td colspan="3">
                                             <table class="table border mrnsummarynewsty">
                                                <tr>
                                                   <td colspan="2" class="p-0">
                                                      <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between">
                                                         <strong>LR Summary</strong>
                                                      </h6>
                                                   </td>
                                                </tr>
                                                <tr class="totalsubheadpodetail">
                                                   <td width="55%"><strong>Sub Total</strong></td>
                                                   <td class="text-end" id="subTotalAmount">{{ number_format($lr->locations->sum('freight'), 2) }}</td>
                                                </tr>
                                                <tr>
                                                   <td><strong>LR Charges</strong></td>
                                                   <td class="text-end" id="lrCharges">{{ number_format($lr->lr_charges, 2) }}</td>
                                                </tr>
                                                <tr>
                                                   <td><strong>Freight Charges</strong></td>
                                                   <td id="FreightCharges" class="text-end">{{ number_format($lr->freight_charges, 2) }}</td>
                                                </tr>
                                                <tr class="voucher-tab-foot">
                                                   <td class="text-primary"><strong>Total Freight Charges</strong></td>
                                                   <td>
                                                      <div class="quottotal-bg justify-content-end">
                                                         <h5 id="totalFreightAmount">
                                                            {{ number_format(
                                                            $lr->locations->sum('freight') +
                                                            $lr->lr_charges +
                                                            $lr->freight_charges, 2) }}
                                                         </h5>
                                                      </div>
                                                   </td>
                                                </tr>
                                             </table>
                                             <!-- Hidden inputs -->
                                             <input type="hidden" name="sub_total" id="subTotalInput" value="{{ number_format($lr->locations->sum('freight'), 2) }}">
                                             <input type="hidden" name="total_freight" id="totalFreightInput" value="{{ number_format($lr->locations->sum('freight') + $lr->lr_charges + $lr->freight_charges, 2) }}">
                                             <input type="hidden" id="fixedAmountGlobal" value="0">
                                             <input type="hidden" id="activeFreePointGlobal" value="0">
                                             <input type="hidden" id="freeAmountGlobal" value="0">
                                          </td>
                                       </tr>
                                    </tfoot>
                                 </table>
                              </div>
                               <div class="row mt-2">
                                <div class="col-md-12">
                                    <div class = "row">
                                    <div class="col-md-4">
                                        <div class="mb-1">
                                            <label class="form-label">Upload Document</label>
                                            <input type="file" class="form-control" name = "attachments[]" onchange = "addFiles(this,'main_lorry_file_preview')" max_file_count = "{{isset($maxFileCount) ? $maxFileCount : 10}}" multiple >
                                            <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                                        </div>
                                    </div> 
                                    <div class = "col-md-6" style = "margin-top:19px;">
                                        <div class = "row" id = "main_lorry_file_preview">
                                        </div>
                                    </div>
                                    </div>
                                    @if($lr->mediaAttachments && $lr->mediaAttachments->count())
                                    <div class="row">
                                        @foreach($lr->mediaAttachments as $media)
                                            <div class="col-md-3 mb-2">
                                                @php
                                                    $url = asset('storage/' . $media->file_name);
                                                    $extension = pathinfo($media->file_name, PATHINFO_EXTENSION);
                                                @endphp

                                                @if(in_array($extension, ['jpg', 'jpeg', 'png']))
                                                    <img src="{{ $url }}" alt="Attachment" class="img-fluid border rounded" style="max-height: 150px;">
                                                @elseif(in_array($extension, ['pdf']))
                                                    <a href="{{ $url }}" target="_blank" class="btn btn-outline-primary w-100">
                                                        View PDF
                                                    </a>
                                                @else
                                                    <a href="{{ $url }}" target="_blank" class="btn btn-outline-secondary w-100">
                                                        {{ $media->file_name }}
                                                    </a>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                            </div>
                                 <div class="col-md-12">
                                    <div class="mb-1">  
                                       <label class="form-label">Final Remarks</label> 
                                       <textarea type="text" rows="4" class="form-control" placeholder="Enter Remarks here..." name="remarks">{{$lr->remarks ?? '' }}</textarea> 
                                    </div>
                                 </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
             </div>
         <!-- Modal to add new record -->
         </section>
      </div>
   </div>
   </div>
   <!-- END: Content-->
     <div class="modal fade" id="amendConfirmPopup" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Amend
                    {{request() -> type === 'lorry-receipt' ? 'Lorry Receipt' : 'Lorry Receipt'}}
                </h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <input type="hidden" name="action_type" id="action_type_main">
            </div>
            <div class="modal-body pb-2">
                <div class="row mt-1">
                <div class="col-md-12">
                    <div class="mb-1">
                        <label class="form-label">Remarks</label>
                        <textarea name="amend_remarks" class="form-control cannot_disable"></textarea>
                    </div>
                    <div class = "row">
                        <div class = "col-md-8">
                            <div class="mb-1">
                                <label class="form-label">Upload Document</label>
                                <input name = "amend_attachments[]" onchange = "addFiles(this, 'amend_files_preview')" type="file" class="form-control cannot_disable" max_file_count = "2" multiple/>
                            </div>
                        </div>
                        <div class = "col-md-4" style = "margin-top:19px;">
                            <div class="row" id = "amend_files_preview">
                            </div>
                        </div>
                    </div>
                    <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">  
                <button type="button" class="btn btn-outline-secondary me-1">Cancel</button> 
                <button type="button" class="btn btn-primary" onclick = "submitAmend();">Submit</button>
            </div>
        </div>
    </div>
    </div>
</form>
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form class="ajax-submit-2" method="POST" action="{{ route('document.approval.lorryReceipt') }}" data-redirect="{{ route('logistics.lorry-receipt.index') }}" enctype='multipart/form-data'>
          @csrf
          <input type="hidden" name="action_type" id="action_type">
          <input type="hidden" name="id" value="{{isset($lr) ? $lr -> id : ''}}">
         <div class="modal-header">
            <div>
               <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="approve_reject_heading_label">
               </h4>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body pb-2">
            <div class="row mt-1">
               <div class="col-md-12">
                  <div class="mb-1">
                     <label class="form-label">Remarks</label>
                     <textarea name="remarks" class="form-control cannot_disable"></textarea>
                  </div>
                  <div class="row">
                    <div class = "col-md-8">
                        <div class="mb-1">
                            <label class="form-label">Upload Document</label>
                            <input type="file" name = "attachments[]" multiple class="form-control cannot_disable" onchange = "addFiles(this, 'approval_files_preview');" max_file_count = "2"/>
                        </div>
                    </div>
                    <div class = "col-md-4" style = "margin-top:19px;">
                        <div class = "row" id = "approval_files_preview">

                        </div>
                    </div>
                  </div>
                  <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                  
               </div>
            </div>
         </div>
         <div class="modal-footer justify-content-center">  
            <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button> 
            <button type="submit" class="btn btn-primary">Submit</button>
         </div>
       </form>
      </div>
   </div>
</div>
@endsection
@section('scripts')

<script>

   $(document).on('change', '#lr_charges', function () {
    calculateTotals();
   });

   function setStatusAndSubmit(status) {
    document.getElementById('statusInput').value = status;
}
      // Select/Deselect All
    document.getElementById('checkAll').addEventListener('change', function () {
        document.querySelectorAll('.rowCheckbox').forEach(cb => cb.checked = this.checked);
    });

</script>

<script>

    function calculateTotals() {
    let subTotal = 0;

    $('input[name*="[freight]"]').each(function () {
        const val = parseFloat($(this).val()) || 0;
        subTotal += val;
    });

    const lr = parseFloat($('#lr_charges').val()) || 0;
    const freightCharge = parseFloat($('#freightCharges').val()) || 0;

    const total = subTotal + lr + freightCharge;

    $('#freightAmount').text(subTotal.toFixed(2));
    $('#subTotalAmount').text(subTotal.toFixed(2));
    $('#lrCharges').text(lr.toFixed(2));
    $('#FreightCharges').text(freightCharge.toFixed(2));
    $('#totalFreightAmount').text(total.toFixed(2));

    // Update hidden fields
    $('#subTotalInput').val(subTotal.toFixed(2));
    $('#totalFreightInput').val(total.toFixed(2));
}

function updateRouteDetailsUI() {
    const $rows = $('#item-table-body').find('tr');

    // Only update if not already set
    if (!$('#routeSource').text() || $('#routeSource').text() === 'Not selected') {
        const source = $('#sourceText').text() || 'Not selected';
        $('#routeSource').text(source);
    }

    if (!$('#routeDestination').text() || $('#routeDestination').text() === 'Not selected') {
        const destination = $('#destinationText').text() || 'Not selected';
        $('#routeDestination').text(destination);
    }
    let totalWeight = 0;
    let totalArticles = 0;

    $rows.each(function () {
        const weight = parseFloat($(this).find('input[name*="[weight]"]').val()) || 0;
        const articles = parseInt($(this).find('input[name*="[no_of_articles]"]').val()) || 0;
        totalWeight += weight;
        totalArticles += articles;
    });

    $('#routeWeight').text(totalWeight);
    $('#routeArticles').text(totalArticles);

    // Set vehicle text only if not already set
    if (!$('#routeVehicle').text() || $('#routeVehicle').text() === 'Not selected') {
        const vehicleText = $('#vehicle_id option:selected').text() || 'Not selected';
        $('#routeVehicle').text(vehicleText);
    }

    if (!$('#routeCapacity').text() || $('#routeCapacity').text() === '--') {
        const vehicleCapacity = $('#vehicle_id option:selected').data('capacity') || '--';
        $('#routeCapacity').text(vehicleCapacity);
    }

   const activePoints = parseInt($('#activeFreePointGlobal').val() || 0);
    const existingPoints = parseInt($('#routePoints').text() || 0);
    $('#routePoints').text(existingPoints + activePoints);
}


 let rowIndex = 1;

$('#addRowBtn').on('click', function () {
    const tbody = $('.mrntableselectexcel');
    let incomplete = false;

    // Check required fields in all existing rows
    tbody.find('tr').each(function () {
        const requiredFields = [
            $(this).find('.route-master-autocomplete[data-type="location"]'),
            $(this).find('select[name*="[type]"]'),
            $(this).find('input[name*="[no_of_articles]"]'),
            $(this).find('input[name*="[weight]"]'),
            $(this).find('input[name*="[freight]"]')
        ];

        for (const field of requiredFields) {
            if (field.length && field.val().trim() === '') {
                incomplete = true;
                return false;
            }
        }
    });

    if (incomplete) {
        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Row',
            text: 'Please fill all required fields before adding a new row.',
            confirmButtonText: 'OK'
        });
        return;
    }

    const rowId = 'row_' + rowIndex;

    const newRow = $(`
        <tr>
            <td>
                <div class="form-check form-check-primary custom-checkbox">
                    <input type="checkbox" class="form-check-input rowCheckbox" name="locations[${rowIndex}][selected]" id="${rowId}" value="${rowIndex}">
                    <label class="form-check-label" for="${rowId}"></label>
                </div>
            </td>
            <td>
                <input type="text" name="locations[${rowIndex}][location_name]" class="form-control mw-100 route-master-autocomplete location-update" placeholder="Start typing locations..." data-type="location">
                <input type="hidden" name="locations[${rowIndex}][location_id]" class="route-master-id" data-type="location">
            </td>
            <td>
                <select class="form-select mw-100" name="locations[${rowIndex}][type]">
                    <option value="">Select</option>
                    <option value="Pick Up">Pick Up</option>
                    <option value="Drop Off">Drop Off</option>
                </select>
            </td>
            <td><input type="text" name="locations[${rowIndex}][no_of_articles]" class="form-control mw-100" /></td>
            <td><input type="text" name="locations[${rowIndex}][weight]" class="form-control mw-100" /></td>
            <td><input type="text" name="locations[${rowIndex}][freight]" class="form-control mw-100 text-end freight-input" /></td>
        </tr>
    `);

    tbody.append(newRow);

    const activeFreePoint = parseInt($('#activeFreePointGlobal').val() || 0);
    const fixedAmountGlobal = parseInt($('#fixedAmountGlobal').val() || 0);
    const freeAmountGlobal = parseInt($('#freeAmountGlobal').val() || 0);

    setTimeout(() => {
    const $rows = $('#item-table-body').find('tr');

    $rows.each(function(index) {
        const $row = $(this);
        const freightInput = $row.find('input[name*="[freight]"]');
        const currentVal = freightInput.val();

        // Only set freight if it's empty or 0
        if (!currentVal || parseFloat(currentVal) === 0) {
            if (index < activeFreePoint) {
                freightInput.val(0);
            } else {
                if (fixedAmountGlobal) {
                    freightInput.val(fixedAmountGlobal);
                } else {
                    freightInput.val(freeAmountGlobal);
                }
            }
        }

        // Re-bind on input
        freightInput.off('input').on('input', calculateTotals);
    });

    calculateTotals();
}, 300);


    rowIndex++;
});


 $(document).ready(function () {
        calculateTotals();
        $('#lrCharges, #freightCharges').on('input', calculateTotals);
        $(document).on('input', 'input[name*="[freight]"]', calculateTotals);
    });
    $(document).on('click', '#deleteSelected', function (e) {
        e.preventDefault();

    const selectedRows = $('.rowCheckbox:checked').closest('tr');

    if (selectedRows.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Rows Selected',
            text: 'Please select at least one row to delete.',
            confirmButtonText: 'OK'
        });
        return;
    }

    // Optional: Confirmation
    Swal.fire({
        icon: 'question',
        title: 'Are you sure?',
        text: 'Do you want to delete the selected row(s)?',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            selectedRows.remove();
            calculateTotals(); 
        }
    });
});

</script>

<script>
   function getDocNumberByBookId(element = null, reset = true) {
    let bookId = element ? element.value : $('#series_id_input').val();

    // Exit early if no bookId
    if (!bookId) return;

    let documentDate = $("#document_date").val() || '';
    let actionUrl = '{{ route("book.get.doc_no_and_parameters") }}?book_id=' + bookId + '&document_date=' + documentDate;

    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status === 200) {
                $("#book_code_input").val(data.data.book_code);

                if (reset) {
                    $("#document_number").val(data.data.doc.document_number || '');
                }

                $("#document_number").attr('readonly', data.data.doc.type !== 'Manually');
            }

            if (data.status === 404 && reset) {
                $("#book_code_input").val("");
                alert(data.message);
            }

            if (data.status === 500 && reset) {
                $("#book_code_input").val("");
                $("#series_id_input").val("");
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                });
            }
        });
    });
}

$(document).ready(function () {
    getDocNumberByBookId();
});
    </script>
    <script>
        const routeMasters = [
      @if($routeMasters->isNotEmpty())
    @foreach($routeMasters as $rm)
        {
            label: "{{ $rm->name }}",
            value: "{{ $rm->name }}",
            id: {{ $rm->id }}
        }@if(!$loop->last),@endif
    @endforeach
@else
    null
@endif
];

$(document).on('focus', '.route-master-autocomplete', function () {
    const $input = $(this);

    if (!$input.data('ui-autocomplete')) {
      $input.autocomplete({
    source: routeMasters,
    minLength: 0,
    select: function (event, ui) {
        $input.val(ui.item.label);

        const type = $input.data('type');
        if (type === 'source' || type === 'destination') {
            $(`#${type}IdInput`).val(ui.item.id);
        }

        if (type === 'location') {
            const nameAttr = $input.attr('name');
            const match = nameAttr.match(/locations\[(\d+)\]\[location_name\]/);
            if (match) {
                const index = match[1];
                const $hiddenInput = $(`input[name="locations[${index}][location_id]"]`);

                $hiddenInput.val(ui.item.id);
                calculateTotals(); // always fire
            }
        }

        return false;
    },
    change: function (event, ui) {
        const type = $input.data('type');

        if (ui.item) {
            if (type === 'location') {
                const nameAttr = $input.attr('name');
                const match = nameAttr.match(/locations\[(\d+)\]\[location_name\]/);
                if (match) {
                    const index = match[1];
                    const $hiddenInput = $(`input[name="locations[${index}][location_id]"]`);

                    $hiddenInput.val(ui.item.id);
                    calculateTotals(); 
                }
            }
        } else {
            // If user cleared the field
            $input.val('');
            if (type === 'location') {
                const nameAttr = $input.attr('name');
                const match = nameAttr.match(/locations\[(\d+)\]\[location_name\]/);
                if (match) {
                    const index = match[1];
                    $(`input[name="locations[${index}][location_id]"]`).val('');
                    calculateTotals(); 
                }
            }
        }
    }
    }).focus(function () {
        $(this).autocomplete('search', '');
    });

    }
});


//customer autocomplete
const customerList = [
   @if($customers->isNotEmpty())
    @foreach($customers as $customer)
        {
            label: "{{ addslashes($customer->company_name) }}",
            value: "{{ addslashes($customer->company_name) }}",
            id: {{ $customer->id }}
        },
    @endforeach
    @else
    null
    @endif
];

$(document).on('focus', '.customer-autocomplete', function () {
    const $input = $(this);

    if (!$input.data('ui-autocomplete')) {
        $input.autocomplete({
            source: customerList,
            minLength: 0,
            select: function (event, ui) {
                const type = $input.data('type'); 
                $input.val(ui.item.label);
                $(`.customer-id[data-type="${type}"]`).val(ui.item.id);
                return false;
            }
        }).focus(function () {
            $(this).autocomplete('search', '');
        });
    }
});
    //drivers autocomplete
const driverList = [
     @if($drivers->isNotEmpty())
    @foreach($drivers as $driver)
        {
            label: "{{ addslashes($driver->name) }}",
            value: "{{ addslashes($driver->name) }}",
            id: {{ $driver->id }}
        },
    @endforeach
    @else
    null
@endif
];

$(document).on('focus', '.driver-autocomplete', function () {
    const $input = $(this);

    if (!$input.data('ui-autocomplete')) {
        $input.autocomplete({
            source: driverList,
            minLength: 0,
            select: function (event, ui) {
                $input.val(ui.item.label);
                $input.closest('div').find('.driver-id').val(ui.item.id);
                return false;
            }
        }).focus(function () {
            $(this).autocomplete('search', '');
        });
    }
});

    
    //vehicle types
    const vehicleTypes = [
   @if($vehicleTypes->isNotEmpty())
    @foreach($vehicleTypes as $vt)
        {
            label: "{{ $vt->name }} ({{ $vt->capacity }} {{ $vt->unit->name ?? '' }})",
            value: "{{ $vt->name }} ({{ $vt->capacity }} {{ $vt->unit->name ?? '' }})",
            id: {{ $vt->id }}
        }@if(!$loop->last),@endif
    @endforeach
@else
    null
@endif
];

   $(document).on('focus', '.vehicle-type-autocomplete', function () {
    if (!$(this).data('ui-autocomplete')) {
        $(this).autocomplete({
            source: vehicleTypes,
            minLength: 0,
            select: function (event, ui) {
                $(this).val(ui.item.label);
                $(this).closest('tr').find('.vehicle-type-id').val(ui.item.id);
                return false;
            }
        }).focus(function () {
            $(this).autocomplete('search', '');
        });
    }
});
</script>
<script>

  // Make it globally accessible

    function fetchFreightCharge() {
        const sourceId = $('input[name="source_id"]').val();
        const destinationId = $('input[name="destination_id"]').val();

        if (!sourceId || !destinationId) return;

        $.ajax({
            url: '/freight-charge-details',
            method: 'GET',
            data: {
                source_id: sourceId,
                destination_id: destinationId
            },
            success: function (response) {
                $('#vehicle_type_name').val(response.vehicle_type_name).prop('disabled', true);
                $('.vehicle-type-id').val(response.vehicle_type_id);
                $('#distance').val(response.distance).prop('disabled', true);
                $('#freight_charges').val(response.freight_charges).prop('disabled', true);
                $('#distanceInput').val(response.distance);
                $('#freightCharges').val(response.freight_charges);

                // ✅ Set text content for display
                $('#routeVehicle').text(response.vehicle_type_name);
                $('#routeCapacity').text(response.vehicle_type_capacity + ' ' + response.vehicle_type_unit_name);
                $('#routeSource').text(response.source_name);
                $('#routeDestination').text(response.destination_name);
            },
            error: function () {
                $('#vehicle_type_name, #vehicle_type_id, #distance, #freight_charges').val('').prop('disabled', false);
            }
        });
    }

    $(document).ready(function () {
        $('input[name="source_name"], input[name="destination_name"]').on('blur', function () {
            const sourceId = $('input[name="source_id"]').val();
            const destId = $('input[name="destination_id"]').val();

            if (sourceId && destId) {
                fetchFreightCharge();
            }
        });
    });

    // ✅ This will now work globally:
    $(document).on('change', 'input[name*="[weight]"], input[name*="[no_of_articles]"]', function () {
        updateRouteDetailsUI(); 
        fetchFreightCharge();   
    });
</script>
<script>
$(document).ready(function () {
    const selectedLocationId = $('#locationId').val();
    const selectedCostCenterId = "{{ old('cost_center_id', $lr->cost_center_id ?? '') }}";

    // When location changes
    $('#locationId').on('change', function () {
        const locationId = $(this).val();
        $('#cost_center_id').html('<option value="">Select Cost Center</option>');

        if (locationId) {
            $.ajax({
                url: '/get-cost-centers-by-location/' + locationId,
                type: 'GET',
                success: function (response) {
                    if (response.success) {
                        $.each(response.data, function (key, center) {
                            const isSelected = center.id == selectedCostCenterId ? 'selected' : '';
                            $('#cost_center_id').append(
                                `<option value="${center.id}" ${isSelected}>${center.name}</option>`
                            );
                        });
                        $('#cost_center_id').trigger('change'); 
                    }
                },
                error: function () {
                    alert('Unable to fetch cost centers.');
                }
            });
        }
    });

    if (selectedLocationId) {
        $('#locationId').trigger('change');
    }
});


// location on focus
let activeFreePoint = 0;
let fixedAmount = null;
let sourceRouteId = null;
let freeAmount = null;

let pricingCache = {}

  function checkFreePoint(locationId = null, sourceId = null, $targetRow = null) {
    if (!locationId || !sourceId) return;

    $.ajax({
        url: '/get-location-pricing',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            location_id: locationId,
            source_id: sourceId,
        },
        success: function (res) {
            $('#fixedAmountDisplay').empty();

            const sourceAmount = parseInt(res.source_amount || 0);
            $('#sourceDefaultAmountGlobal').val(sourceAmount);

            if (res.status === 'both_exist') {
                $('#activeFreePointGlobal').val(parseInt(res.free_point));
                $('#fixedAmountGlobal').val(parseInt(res.amount));
                $('#freeAmountGlobal').val(parseInt(res.free_amount));

                pricingCache[locationId] = {
                    type: 'both',
                    free_point: parseInt(res.free_point),
                    amount: parseInt(res.amount),
                    freeAmount: parseInt(res.free_amount),
                };
            }

            if (res.status === 'exists_in_fixed') {
                $('#activeFreePointGlobal').val(0);
                $('#fixedAmountGlobal').val(parseInt(res.amount));

                pricingCache[locationId] = {
                    type: 'fixed',
                    amount: parseInt(res.amount),
                };
            }

          if (res.status === 'free_point') {
            $('#activeFreePointGlobal').val(parseInt(res.free_point));
            $('#fixedAmountGlobal').val(0);
            $('#freeAmountGlobal').val(parseInt(res.free_amount));

            pricingCache[locationId] = {
                type: 'free',
                free_point: parseInt(res.free_point),
                amount: 0,
                freeAmount: parseInt(res.free_amount),
            };

            // ✅ Optional direct bind if this row is within free_point range
            if ($targetRow && $targetRow.length) {
                const currentIndex = $targetRow.index(); 
                if (currentIndex < parseInt(res.free_point)) {
                    $targetRow.find('input[name*="[freight]"]').val(0);
                } else {
                    $targetRow.find('input[name*="[freight]"]').val(res.free_amount);
                }
            }
        }


            if (res.status === 'not_exist') {
                $('#activeFreePointGlobal').val(0);
                $('#fixedAmountGlobal').val(0);

                pricingCache[locationId] = {
                    type: 'none',
                    amount: 0,
                };
            }

            // 🔥 Only apply freight to the selected row
            applyFreightToRows($targetRow);
        }
    });
}

function applyFreightToRows() {
    const $rows = $('#item-table-body').find('tr');
    const activeFreePoint = parseInt($('#activeFreePointGlobal').val() || 0);
    const sourceDefaultAmount = parseInt($('#sourceDefaultAmountGlobal').val() || 0);

    $rows.each(function (index) {
        const $row = $(this);
        const locationId = $row.find('input[name*="[location_id]"]').val()?.trim();
        const $freightInput = $row.find('input[name*="[freight]"]');

        if (!locationId) return;

        const pricing = pricingCache[locationId];
        if (pricing) {
            const currentValue = $freightInput.val();
            // ✅ Only overwrite if empty or zero
            if (currentValue === '' || parseFloat(currentValue) === 0) {
                if ((pricing.type === 'both' || pricing.type === 'free') && index < activeFreePoint) {
                    $freightInput.val(0);
                } else if ((pricing.type === 'both' || pricing.type === 'fixed') && index >= activeFreePoint) {
                    if (pricing.amount && parseFloat(pricing.amount) > 0) {
                        $freightInput.val(pricing.amount);
                    } else {
                        $freightInput.val(pricing.freeAmount);
                    }
                } else {
                    $freightInput.val(sourceDefaultAmount > 0 ? sourceDefaultAmount : '');
                }
            }
        } else {
            const currentValue = $freightInput.val();
            if (currentValue === '' || parseFloat(currentValue) === 0) {
                $freightInput.val(sourceDefaultAmount > 0 ? sourceDefaultAmount : '');
            }
        }

        calculateTotals(); 
    });
}



function handleLocationUpdate($input) {
    const $row = $input.closest('tr');
    const locationId = $row.find('input[name*="[location_id]"]').val();
    const sourceId = $('#sourceIdInput').val();

    if (locationId && sourceId) {
        checkFreePoint(locationId, sourceId, $row); // ✅ pass row
    }
}



$(document).on('autocompleteselect autocompletechange', '.location-update', function () {
    handleLocationUpdate($(this));
    calculateTotals(); 
});

$(document).on('change', 'input[name*="[location_id]"]', function () {
    const $row = $(this).closest('tr');
    const $input = $row.find('.location-update');
    handleLocationUpdate($input);
});

</script>

<script>
   //approval-start
  $(document).on('submit', '.ajax-submit-2', function (e) {
    e.preventDefault();

    var submitButton = (e.originalEvent && e.originalEvent.submitter) || $(this).find(':submit')[0];
    var submitButtonHtml = submitButton?.innerHTML;
    if (submitButton) {
        submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        submitButton.disabled = true;
    }

    var method = $(this).attr('method');
    var url = $(this).attr('action');
    var redirectUrl = $(this).data('redirect');
    var data = new FormData(this);
    data.append('status', $('#status_hidden_input').val());

    $('.ajax-validation-error-span').remove();
    $(".is-invalid").removeClass("is-invalid");
    $(".help-block").remove();
    $(".waves-ripple").remove();

    $.ajax({
        url: url,
        type: method,
        data: data,
        contentType: false,
        processData: false,
        success: function (res) {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = submitButtonHtml;
            }

            console.log("Success response:", res); 
            Swal.fire({
                title: 'Success!',
                text: res.message || 'Submitted successfully!',
                icon: 'success',
                timer: 2000,
                showConfirmButton: false,
            });

            setTimeout(() => {
                if (res.store_id) {
                    location.href = `/logistics/${res.store_id}/edit`;
                } else if (redirectUrl) {
                    location.href = redirectUrl;
                } else {
                    location.reload();
                }
            }, 1500);
        },
        error: function (error) {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = submitButtonHtml;
            }

            let res = error.responseJSON || {};
            console.error("Error response:", res);

            $('.ajax-validation-error-span').remove();
            $(".is-invalid").removeClass("is-invalid");
            $(".help-block").remove();
            $(".waves-ripple").remove();

            if (error.status === 422 && res.errors) {
                show_validation_error(res.errors);
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: res.message || 'An unexpected error occurred.',
                    icon: 'error',
                });
            }
        }
    });
});
</script>
<script>
    $(document).ready(function () {
        $('#revokeButton').on('click', function () {
            const lrId = "{{ isset($lr) ? $lr->id : null }}";

            if (lrId) {
                $.ajax({
                    url: "{{ route('logistics.lorry-receipt.revoke') }}",
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        id: lrId,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (data) {
                        if (data.status === 'success') {
                            Swal.fire({
                                title: 'Success!',
                                text: data.message,
                                icon: 'success',
                            });
                            location.reload();
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message,
                                icon: 'error',
                            });
                            window.location.href = "{{ route('logistics.lorry-receipt.index') }}";
                        }
                    },
                    error: function (xhr) {
                        console.error('Error:', xhr.responseText);
                        Swal.fire({
                            title: 'Error!',
                            text: 'Some internal error occurred',
                            icon: 'error',
                        });
                    }
                });
            }
        });
    });

     function setApproval() {
        document.getElementById('action_type').value = "approve";
        document.getElementById('approve_reject_heading_label').textContent = "Approve Item";
    }

    function setReject() {
        document.getElementById('action_type').value = "reject";
        document.getElementById('approve_reject_heading_label').textContent = "Reject Item";
    }

      // Show modal by ID
    function openModal(id) {
        $('#' + id).modal('show');
    }

    // Bind click on Amendment button
    $(document).on('click', '#amendShowButton', function () {
        openModal('amendConfirmPopup');
    });

    // Confirm amendment logic
    window.amendConfirm = function () {
        const amendButton = document.getElementById('amendShowButton');
        if (amendButton) {
            amendButton.style.display = "none";
        }

        const buttonParentDiv = document.getElementById('buttonsDiv');
        const newSubmitButton = document.createElement('button');
        newSubmitButton.type = "button";
        newSubmitButton.id = "amend-submit-button";
        newSubmitButton.className = "btn btn-primary btn-sm mb-50 mb-sm-0 submit-button";
        newSubmitButton.innerHTML = `<i data-feather="check-circle"></i> Submit`;
        newSubmitButton.value = "submitted";

        newSubmitButton.onclick = function () {
            openAmendConfirmModal();
        };

        if (buttonParentDiv) {
            buttonParentDiv.appendChild(newSubmitButton);
        }

        if (window.feather) {
            feather.replace({
                width: 14,
                height: 14
            });
        }
    };

    // Show confirmation modal for amendment
    function openAmendConfirmModal() {
        $('#amendConfirmPopup').modal('show');
    }

    // Submit amendment form
    window.submitAmend = function () {
        let remark = $("#amendConfirmPopup").find('[name="amend_remarks"]').val();
        $("#action_type_main").val("amendment");
        $("#amendConfirmPopup").modal('hide');
        $("#lorry_receipt_form").submit();
    };

       //inspection-end
     var currentRevNo = $("#revisionNumber").val();
     $(document).on('change', '#revisionNumber', function (e) {
        e.preventDefault();
        const selectedRev = e.target.value;
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('revisionNumber', selectedRev);
        $("#revisionNumber").val(currentRevNo);
        window.open(currentUrl.toString(), '_blank');
    });
//File related js code here

      let fileInputData = {};
      const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx'];
    const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    const MAX_FILE_SIZE = 5120; // in KB (5MB)
    function appendFilePreviews(fileUrl, previewElementId, index, fileId = null) {
    const previewContainer = document.getElementById(previewElementId);
    if (!previewContainer) return;

    const fileName = fileUrl.split('/').pop();

    const previewHtml = `
        <div class="col-4 file-preview-item" data-index="${index}" data-file-id="${fileId ?? ''}">
            <div class="card border">
                <div class="card-body p-2 d-flex justify-content-between align-items-center">
                    <div class="text-truncate small" title="${fileName}">
                        <i class="fa fa-paperclip me-1 text-secondary"></i> ${fileName}
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFilePreview(this, '${previewElementId}', '${fileName}')">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

    previewContainer.insertAdjacentHTML('beforeend', previewHtml);
}



        function addFiles(element, previewElementId) {
        const input = element;
        const allowedMaxFilesCount = Number(element.getAttribute('max_file_count') ? element.getAttribute('max_file_count') : 1);
        const files = Array.from(input.files); // Convert new FileList to array
        const dt = new DataTransfer();
        const inputId = input.name.replace('[]','');
        // Initialize storage for this input if not already initialized
        if (!fileInputData[inputId]) {
            fileInputData[inputId] = [];
            addedFilesCount = 0;
        } else {
            addedFilesCount = fileInputData[inputId].length;
        }

        if ((files.length + fileInputData[inputId].length) > allowedMaxFilesCount) 
        {
            Swal.fire({
                title: 'Error!',
                text: "Maximum " + allowedMaxFilesCount + " files are allowed",
                icon: 'error',
            });
            let prevAllFiles = fileInputData[inputId] ? fileInputData[inputId] : [];
            let tempDt = new DataTransfer();
            prevAllFiles.forEach((fileElement) => {
                tempDt.items.add(fileElement);
            });
            input.files = tempDt.files;
            return;
        }

        // Combine old and new files
        let allFiles = [...fileInputData[inputId], ...files];
        var invalidFile = {};

        // Validate files
        for (let i = 0; i < allFiles.length; i++) {
            const file = allFiles[i];
            const fileExtension = file.name.split('.').pop().toLowerCase();

            if (!ALLOWED_EXTENSIONS.includes(fileExtension) || !ALLOWED_MIME_TYPES.includes(file.type)) {
                invalidFile.message = 'Please select valid files';
                break;
            }
            const fileSize = (file.size / 1024).toFixed(2);
            if (fileSize > MAX_FILE_SIZE) {
                invalidFile.message = 'Please select files with size not more than 5MB';
                break;
            }
        }

        // Stop if there's an invalid file
        if (invalidFile && invalidFile.message) {
            Swal.fire({
                title: 'Error!',
                text: invalidFile.message,
                icon: 'error',
            });
            element.value = ''; // Reset file input
            return;
        } else {
            // Add all files to DataTransfer and rebuild the preview
            allFiles.forEach((file, i) => {
                dt.items.add(file);
                if (!fileInputData[inputId].some(f => f.name === file.name && f.size === file.size)) {
                    const fileUrl = URL.createObjectURL(file);
                    appendFilePreviews(fileUrl, previewElementId, i);
                }
            });

            // Update the global object for this input
            fileInputData[inputId] = allFiles.reduce((unique, file) => {
                if (!unique.some(f => f.name === file.name && f.size === file.size)) {
                    unique.push(file);
                }
                return unique;
            }, []);

            // Update the file input's FileList
            input.files = dt.files;

            // Reset and re-render SVG icons (if applicable)
            feather.replace({
                width: 20,
                height: 20,
            });
        }
    }
</script>

@endsection