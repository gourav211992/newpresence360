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
                  <div class="form-group breadcrumb-right" id="buttonsDiv">
                    <a href="{{ route('logistics.lorry-receipt.index') }}" class="btn btn-secondary btn-sm" >
                        <i data-feather="arrow-left-circle"></i> Back
                        </a>
                        <!-- @if(auth()->check() && $lr->created_by == optional(auth()->user())->auth_user_id)
                            <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                    data-url="{{ route('logistics.lorry-receipt.destroy', $lr->id) }}" 
                                    data-redirect="{{ route('logistics.lorry-receipt.index') }}"
                                    data-message="Are you sure you want to delete this record?">
                                <i data-feather="trash-2" class="me-50"></i> Delete
                            </button>
                        @endif -->
                         
                           <!-- Save as Draft Button -->
                     @if(!isset(request()->revisionNumber))
                       @if($lr->document_status == \App\Helpers\ConstantHelper::APPROVED || $lr->document_status == \App\Helpers\ConstantHelper::APPROVAL_NOT_REQUIRED)
                           
                          <a href="{{route('logistics.lorry-receipt.generate-pdf', $lr->id)}}" target="_blank" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer"><polyline points="6 9 6 2 18 2 18 9"></polyline>
                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                <rect x="6" y="14" width="12" height="8"></rect></svg> Print
                            </a>
                             <button type = "button" onclick = "sendMailTo();"  class="btn btn-primary btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i data-feather="mail"></i> E-Mail</button>
                              @if(auth()->check() && $lr->consignee_id == optional(auth()->user())->auth_user_id)
                              <button type = "button" onclick = "sendMailTo();"  class="btn btn-primary btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i data-feather="mail"></i>Consignee Approve</button>
                            @endif
                        @endif
                        @if(isset($buttons) && is_array($buttons) && isset($lr))
                           @if($buttons['draft'] ?? false)
                               <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" onclick="setStatusAndSubmit('draft')">
                                <i data-feather='save'></i> Save as Draft
                            </button>
                           @endif

                           @if($buttons['submit'] ?? false)
                              <button type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0 submit-button" onclick="setStatusAndSubmit('submitted')">
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
                           <button type="button" id="amendShowButton" onclick="openModal('amendmentconfirm')" class="btn btn-primary btn-sm mb-50 mb-sm-0">
                                                <i data-feather='edit'></i> Amendment
                         </button>
                           @endif

                           @if($buttons['revoke'] ?? false)
                              <button type="button" class="btn btn-primary btn-sm mb-50 mb-sm-0" id="revokeButton">
                                    <i data-feather="rotate-ccw"></i> Revoke
                              </button>
                           @endif
                         @else
                           <button type="submit" onclick="submitForm('draft');" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" id="save-draft-button">
                              <i data-feather="save"></i> Save as Draft
                           </button>
                           <button type="submit" onclick="submitForm('submitted');" class="btn btn-primary btn-sm mb-50 mb-sm-0 submit-button" id="submit-button">
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
                                                Status : <span class="{{ $docStatusClass }} {{ $lr->document_status == 'approval_not_required' ? 'text-success' : '' }}">
    {{ $lr->document_status == 'approval_not_required' ? 'Approved' : ucfirst($lr->document_status) }}
</span>

 
                                          </span>
                                       </div>
                                          
                                    @endif
                              
                              <div class="col-md-8">
                                 <div class="row align-items-center mb-1">
                                    <div class="col-md-3"> 
                                       <label class="form-label">Series <span class="text-danger">*</span></label>  
                                    </div>
                                    <div class="col-md-5">  
                                      
                                       <input type="hidden" name="document_status" id="statusInput" value="{{ old('status', @$lr->document_status ?? 'draft') }}" >
                                       <select class="form-select disable_on_edit " onchange = "getDocNumberByBookId(this);" name = "book_id" id = "series_id_input" disabled>
                                       @foreach ($series as $currentSeries)
                                       <option value="{{ $currentSeries->id }}" {{ old('book_id', @$lr->book_id) == $currentSeries->id ? 'selected' : '' }}>{{ $currentSeries->book_code }}</option>
                                       @endforeach
                                       </select>
                                    </div>
                                    <input type = "hidden" name = "book_code" id = "book_code_input" value = "{{isset($lr) ? @$lr -> book_code : ''}}"></input>
                                 </div>
                                 <div class="row align-items-center mb-1">
                                    <div class="col-md-3"> 
                                       <label class="form-label">Doc No <span class="text-danger">*</span></label>  
                                    </div>
                                    <div class="col-md-5"> 
                                       <input type="text" class="form-control " id="document_number" name="document_number" value="{{ old('document_number', @$lr->document_number) }}" disabled>
                                    </div>
                                 </div>
                                 <div class="row align-items-center mb-1">
                                    <div class="col-md-3"> 
                                       <label class="form-label">Doc Date <span class="text-danger">*</span></label>  
                                    </div>
                                    <div class="col-md-5"> 
                                       <input type="date" class="form-control editable-field" id="document_date" name="document_date" value="{{ old('document_date', @$lr->document_date ? \Carbon\Carbon::parse(@$lr->document_date)->format('Y-m-d') : now()->format('Y-m-d')) }}">
                                    </div>
                                 </div>
                                 <div class="row align-items-center mb-1">
                                    <div class="col-md-3"> 
                                       <label class="form-label">Location <span class="text-danger">*</span></label>  
                                    </div>
                                    <div class="col-md-5">
                                       <select class="form-select select2 editable-field" name="location" id="locationId" >
                                          <option value="">Select Location</option>
                                          @foreach($locations as $location)
                                          <option value="{{ $location->id }}" {{ old('location', @$lr->location_id) == $location->id ? 'selected' : '' }}>{{ $location->store_name }}</option>
                                          @endforeach
                                       </select>
                                    </div>
                                 </div>
                                 <div class="row align-items-center mb-1">
                                    <div class="col-md-3"> 
                                       <label class="form-label">Cost Center <span class="text-danger">*</span></label>  
                                    </div>
                                    <div class="col-md-5">
                                       <select name="cost_center_id" id="cost_center_id" class="form-select select2 editable-field" >
                                          <option value="">Select Cost Center</option>
                                       </select>
                                    </div>
                                 </div>
                              </div>
                             
                                  
                             @if(isset($lr) && ($lr->document_status !== "draft"))
                                                        @if((isset($approvalHistory) && count($approvalHistory) > 0) || isset($lr->revision_number))
                                                        <div class="col-md-4">
                                                            <div class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
                                                                <h5 class="mb-2 text-dark border-bottom pb-50 d-flex align-items-center justify-content-between">
                                                                    <strong><i data-feather="arrow-right-circle"></i> Approval History</strong>
                                                                    @if(!isset(request()->revisionNumber) && $lr->document_status !== 'draft')
                                                                        <strong class="badge rounded-pill badge-light-secondary amendmentselect">Rev. No.
                                                                            <select class="form-select cannot_disable" id="revisionNumber">
                                                                                @for($i=$lr->revision_number; $i >= 0; $i--)
                                                                                    <option value="{{$i}}" {{request('revisionNumber', $lr->revision_number) == $i ? 'selected' : ''}}>{{$i}}</option>
                                                                                @endfor
                                                                            </select>
                                                                        </strong>
                                                                    @else
                                                                        @if ($lr->document_status !== 'draft')
                                                                            <strong class="badge rounded-pill badge-light-secondary amendmentselect cannot_disable">
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
                                                            </div>
                                                        @endif
                                                    @endif
                                            {{-- Approval History Section --}}
                             
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
                                          <input type="text" name="source_name" class="form-control mw-100 route-master-autocomplete editable-field"
                                             placeholder="Start typing  locations..." data-type="source"
                                             value="{{ old('source_name', @$lr->source->name ?? '') }}" />
                                          <input type="hidden" name="source_id" class="route-master-id editable-field" data-type="source"
                                             value="{{ old('source_id', @$lr->origin_id) }}" id="sourceIdInput" />
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="destination">Destination <span class="text-danger">*</span></label>
                                          <input type="text" name="destination_name" class="form-control mw-100 route-master-autocomplete editable-field"
                                             placeholder="Start typing  locations." data-type="destination"
                                             value="{{ old('destination_name', @$lr->destination->name ?? '') }}" />
                                          <input type="hidden" name="destination_id" class="route-master-id editable-field" data-type="destination"
                                             value="{{ old('destination_id', @$lr->destination_id) }}" id="destinationIdInput" />
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="consignor">Consignor <span class="text-danger">*</span></label>
                                          <input type="text" name="customer_name" class="form-control mw-100 customer-autocomplete editable-field"
                                             data-type="consignor" placeholder="Start typing customer..."
                                             value="{{ old('customer_name', @$lr->consignor->company_name ?? '') }}"  />
                                          <input type="hidden" name="customer_id" class="customer-id editable-field" data-type="consignor"
                                             value="{{ old('customer_id', @$lr->consignor_id) }}" id="customer_id"/>
                                            
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="consignee">Consignee <span class="text-danger">*</span></label>
                                          <input type="text" name="consignee_name" class="form-control mw-100 customer-autocomplete editable-field"
                                             data-type="consignee" placeholder="Start typing consignee..."
                                             value="{{ old('consignee_name', @$lr->consignee->company_name ?? '') }}" />
                                          <input type="hidden" name="consignee_id" class="customer-id editable-field" data-type="consignee"
                                             value="{{ old('consignee_id', @$lr->consignee_id) }}" id="consignee_id"/>
                                             <input type="hidden" name="consignee_email" data-type="consignee"
                                             value="{{ old('consignee_email', @$lr->consignee->email) }}" id="consigneeemail"/>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="vehicle">Vehicle No.<span class="text-danger">*</span></label>
                                          <input type="text" name="vehicle_type_name" class="form-control mw-100 vehicle-number-autocomplete editable-field"
                                             placeholder="Select Vehicle" id="vehicle_number"
                                             value="{{ old('vehicle_number', (@$lr->vehicle->lorry_no ?? '') . (isset($lr->vehicle->vehicleType->name) ? ' (' . $lr->vehicle->vehicleType->name . ')' : '')) }}" />
                                          <input type="hidden" name="vehicle_number_id" class="vehicle-number-id editable-field"
                                             value="{{ old('vehicle_number_id', @$lr->vehicle_id) }}" id="vehicle_number_id"/>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="distance">Distance (Km) <span class="text-danger">*</span></label>
                                          <input type="text" class="form-control editable-field" id="distance" name="distances"
                                             placeholder="Enter Distance (Km)" value="{{ old('distances', @$lr->distance) }}" />
                                          <input type="hidden" class="form-control editable-field" id="distanceInput" name="distance"
                                             value="{{ old('distance', @$lr->distance) }}" />
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="freight_charges">Freight Charges (Rs) <span class="text-danger">*</span></label>
                                          <input type="number" class="form-control editable-field" id="freight_charges" name="freight_charge"
                                             placeholder="Enter Freight Charges (Rs)" value="{{ old('freight_charge', @$lr->freight_charges) }}" >
                                          <input type="hidden" class="form-control editable-field" id="freightCharges" name="freight_charges"
                                             value="{{ old('freight_charges', @$lr->freight_charges) }}" />
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="driver">Driver <span class="text-danger">*</span></label>
                                          <input type="text" name="driver_name" class="form-control mw-100 driver-autocomplete editable-field"
                                             placeholder="Select Driver" data-type="driver"
                                             value="{{ old('driver_name', @$lr->driver->name ?? '') }}" />
                                          <input type="hidden" name="driver_id" class="driver-id editable-field" data-type="driver"
                                             value="{{ old('driver_id', @$lr->driver_id) }}" />
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="driver_cash">Driver Cash (Rs)</label>
                                          <input type="number" class="form-control editable-field" id="driver_cash" name="driver_cash"
                                             placeholder="Enter Driver Cash (Rs)" value="{{ old('driver_cash', @$lr->driver_cash) }}"  />
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="fuel_price">Fuel Price (Rs)</label>
                                          <input type="number" class="form-control editable-field" id="fuel_price" name="fuel_price"
                                             placeholder="Enter Fuel Price (Rs)" value="{{ old('fuel_price', @$lr->fuel_price) }}" />
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="invoice_no">Invoice No.</label>
                                          <input type="text" class="form-control editable-field" id="invoice_no" name="invoice_no"
                                             placeholder="Enter Invoice No." value="{{ old('invoice_no', @$lr->invoice_no) }}" />
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="invoice_value">Invoice Value</label>
                                          <input type="text" class="form-control editable-field" id="invoice_value" name="invoice_value"
                                             placeholder="Enter Invoice Value" value="{{ old('invoice_value', @$lr->invoice_value) }}" />
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="no_of_bundles">No of Article/Bundles <span class="text-danger">*</span></label>
                                          <input type="number" class="form-control editable-field" id="no_of_bundles" name="no_of_bundles"
                                             placeholder="Enter No of Article/Bundles" value="{{ old('no_of_bundles', @$lr->no_of_bundles) }}" />
                                            <input type="hidden" class="form-control" id="per_bundles"  placeholder="Enter No of Article/Bundles" />
                                            <input type="hidden" class="form-control" id="no_bundles"  placeholder="Enter No of Article/Bundles" />
                                            <input type="hidden" class="form-control" id="no_bundles_amount"  placeholder="Enter No of Article/Bundles" />
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="weight">Weight (kg) <span class="text-danger">*</span></label>
                                          <input type="number" class="form-control editable-field" id="weight" name="weight"
                                             placeholder="Enter Weight (kg)" value="{{ old('weight', @$lr->weight) }}" />
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="ewaybill_no">E-Waybill No. <span class="text-danger">*</span></label>
                                          <input type="text" class="form-control editable-field" id="ewaybill_no" name="ewaybill_no"
                                             placeholder="Enter E-Waybill No." value="{{ old('ewaybill_no', @$lr->ewaybill_no) }}" />
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="gst_paid_by">GST Paid By <span class="text-danger">*</span></label>
                                          <select class="form-select editable-field" id="gst_paid_by" name="gst_paid_by" >
                                             <option value="">Select</option>
                                             <option value="Consignor" {{ old('gst_paid_by', @$lr->gst_paid_by) == 'Consignor' ? 'selected' : '' }}>Consignor</option>
                                             <option value="Consignee" {{ old('gst_paid_by', @$lr->gst_paid_by) == 'Consignee' ? 'selected' : '' }}>Consignee</option>
                                             <option value="Transporter" {{ old('gst_paid_by', @$lr->gst_paid_by) == 'Transporter' ? 'selected' : '' }}>Transporter</option>
                                          </select>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="lr_type">LR Type <span class="text-danger">*</span></label>
                                          <select class="form-select editable-field" id="lr_type" name="lr_type" >
                                             <option value="">Select</option>
                                             <option value="Inward" {{ old('lr_type', @$lr->lr_type) == 'Inward' ? 'selected' : '' }}>Inward</option>
                                             <option value="Outward" {{ old('lr_type', @$lr->lr_type) == 'Outward' ? 'selected' : '' }}>Outward</option>
                                          </select>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="billing_type">Billed or Pay <span class="text-danger">*</span></label>
                                          <select class="form-select editable-field" id="billing_type" name="billing_type" >
                                             <option value="">Select</option>
                                             <option value="To be Billed" {{ old('billing_type', @$lr->billing_type) == 'To be Billed' ? 'selected' : '' }}>To be Billed</option>
                                             <option value="To Pay" {{ old('billing_type', @$lr->billing_type) == 'To Pay' ? 'selected' : '' }}>To Pay</option>
                                          </select>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="load_type">Load Type</label>
                                          <select class="form-select editable-field" id="load_type" name="load_type" >
                                             <option value="">Select</option>
                                             @foreach(['FTL','Bulk','CEP','FCL','LCP','LTL'] as $type)
                                             <option value="{{ $type }}" {{ old('load_type', @$lr->load_type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                                             @endforeach
                                          </select>
                                       </div>
                                    </div>
                                    <div class="col-md-3">
                                       <div class="mb-1">
                                          <label class="form-label" for="lr_charges">LR Charges</label>
                                          <select class="form-select editable-field" id="lr_charges" name="lr_charges">
                                             <option value="">Select</option>
                                             @foreach($lorryCharges as $value)
                                             <option value="{{ @$value }}" {{ old('lr_charges', @$lr->lr_charges) == @$value ? 'selected' : '' }}>{{ @$value }}</option>
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
                               @if($lr->document_status === 'submitted' || $lr->document_status === 'approved')
                                    <button type="button" class="btn btn-sm btn-outline-danger me-50" id="delete">
                                        <i data-feather="x-circle"></i> Delete
                                    </button>

                                    <a href="#" id="ad" class="btn btn-sm btn-outline-primary">
                                        <i data-feather="plus"></i> Add New Item
                                    </a>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-danger me-50" id="deleteSelected">
                                        <i data-feather="x-circle"></i> Delete
                                    </button>

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
                                    $rowIndex = 0;
                                    @endphp

                                    @forelse($lr->locations as $location)
                                    @php 
                                    $total_weight += $location->weight;
                                    $total_articles += $location->no_of_articles;
                                    @endphp
                                    <tr>
                                    <td class="customernewsection-form">
                                        <div class="form-check form-check-primary custom-checkbox">
                                            <input type="checkbox" class="form-check-input rowCheckbox" name="locations[{{ @$rowIndex }}][selected]" id="row_{{ @$rowIndex }}">
                                            <label class="form-check-label" for="row_{{ @$rowIndex }}"></label>
                                        </div>
                                    </td>
                                    <td class="poprod-decpt">
                                        <input type="hidden" name="locations[{{ @$rowIndex }}][id]" value="{{ old("locations.@$rowIndex.id", @$location->id ?? '') }}">
                                        <input type="text" name="locations[{{ @$rowIndex }}][location_name]" value="{{ old("locations.$rowIndex.location_name", optional(@$location->route)->name) }}" 
                                            placeholder="Select" class="form-control mw-100 location-update route-master-autocomplete editable-field"
                                            data-type="source"  />
                                        <input type="hidden" name="locations[{{ @$rowIndex }}][location_id]" value="{{ @$location->location_id ?? '' }}"
                                            class="route-master-id" data-type="source" />
                                    </td>
                                    <td>
                                        <select class="form-select mw-100 editable-field" name="locations[{{ @$rowIndex }}][type]" >
                                            <option value="">Select</option>
                                            <option value="Pick Up" {{ @$location->type == 'Pick Up' ? 'selected' : '' }}>Pick Up</option>
                                            <option value="Drop Off" {{ @$location->type == 'Drop Off' ? 'selected' : '' }}>Drop Off</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="text" name="locations[{{ @$rowIndex }}][no_of_articles]" value="{{ @$location->no_of_articles ?? '' }}" class="form-control mw-100 editable-field" />
                                    </td>
                                    <td>
                                        <input type="text" name="locations[{{ @$rowIndex }}][weight]" value="{{ @$location->weight ?? '' }}" class="form-control mw-100 editable-field"  />
                                    </td>
                                    <td>
                                        <input type="text" name="locations[{{ @$rowIndex }}][freight]" value="{{ @$location->amount ?? '' }}" class="form-control mw-100 text-end editable-field" />
                                    </td>
                                    </tr>
                                    @php $rowIndex++; @endphp
                                    @empty
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
                                          <td class="text-end" id="freightAmount">{{ number_format(@$lr->locations->sum('freight'), 2) }}</td>
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
                                                      <span class="poitemtxt mw-100"><strong>Source</strong>: <span id="routeSource">{{ @$lr->source->name ?? '-' }}</span></span>
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td class="poprod-decpt">
                                                      <span class="poitemtxt mw-100"><strong>Destination</strong>: <span id="routeDestination">{{ @$lr->destination->name ?? '-' }}</span></span>
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td class="poprod-decpt">
                                                      <span class="badge rounded-pill badge-light-primary"><strong>Weight</strong>: <span id="routeWeight">{{ @$total_weight ?? 0 }}</span></span>
                                                      <span class="badge rounded-pill badge-light-primary"><strong>No of Article</strong>: <span id="routeArticles">{{ $total_articles ?? 0 }}</span></span>
                                                      <span class="badge rounded-pill badge-light-primary"><strong>Points</strong>:<span id="routePoints"> {{ @$lr->locations->count() > 1 ? $lr->locations->count() : 0 }}</span></span>
                                                   </td>
                                                </tr>
                                                <tr>
                                                   <td class="poprod-decpt">
                                                      <span class="badge rounded-pill badge-light-primary"><strong>Vehicle Type</strong>: <span id="routeVehicle">{{ @$lr->vehicle->vehicleType->name ?? '-' }}</span></span>
                                                      <span class="badge rounded-pill badge-light-primary"><strong>Capacity</strong>: <span id="routeCapacity">{{ number_format(@$lr->vehicle->vehicleType->capacity, 2) }}</span></span> 
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
                                                   <td class="text-end" id="subTotalAmount">{{ number_format(@$lr->locations->sum('freight'), 2) }}</td>
                                                </tr>
                                                <tr>
                                                   <td><strong>LR Charges</strong></td>
                                                   <td class="text-end" id="lrCharges">{{ number_format(@$lr->lr_charges, 2) }}</td>
                                                </tr>
                                                <tr>
                                                   <td><strong>Freight Charges</strong></td>
                                                   <td id="FreightChargeshtml" class="text-end">{{ number_format(@$lr->freight_charges, 2) }}</td>
                                                </tr>
                                                <tr class="voucher-tab-foot">
                                                   <td class="text-primary"><strong>Total Freight Charges</strong></td>
                                                   <td>
                                                      <div class="quottotal-bg justify-content-end">
                                                         <h5 id="totalFreightAmount">
                                                            {{ number_format(
                                                            @$lr->locations->sum('freight') +
                                                            @$lr->lr_charges +
                                                            @$lr->freight_charges, 2) }}
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
                                    <div class="row">
                                        {{-- File Upload --}}
                                        <div class="col-md-4">
                                            <div class="mb-1">
                                                <label class="form-label">Upload Document</label>
                                                <input type="file" class="form-control editable-field" name="attachments[]" 
                                                    onchange="addFiles(this, 'main_lorry_file_preview')" 
                                                    max_file_count="{{ isset($maxFileCount) ? $maxFileCount : 10 }}" 
                                                    multiple >
                                                <span class="text-primary small">{{ __("message.attachment_caption") }}</span>
                                            </div>
                                        </div>

                                        {{-- Preview for newly added files --}}
                                        <div class="col-md-6" style="margin-top:19px;">
                                            <div class="row" id="main_lorry_file_preview">
                                            @if(@$lr->mediaAttachments && @$lr->mediaAttachments->count())
                                                @foreach(@$lr->mediaAttachments as $key => $media)
                                                    @php
                                                        $url = $media->file_url;
                                                        $extension = strtolower(pathinfo($media->file_name, PATHINFO_EXTENSION));
                                                        $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'webp']);
                                                    @endphp

                                                    <div class="col-md-2 mb-2 d-flex  image-uplodasection expenseadd-sign">
                                                        <a href="{{ $url }}" target="_blank" class="d-flex align-items-center text-decoration-none me-2">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                                    class="feather feather-file-text">
                                                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                                    <polyline points="14 2 14 8 20 8"/>
                                                                    <line x1="16" y1="13" x2="8" y2="13"/>
                                                                    <line x1="16" y1="17" x2="8" y2="17"/>
                                                                    <polyline points="10 9 9 9 8 9"/>
                                                                </svg>
                                                             </a>

                                                        @if($lr->document_status == \App\Helpers\ConstantHelper::DRAFT || intval(request('amendment')))
                                                            <div class="delete-img text-danger"
                                                                data-edit-flag="true"
                                                                data-index="{{ $key + 1 }}"
                                                                data-id="{{ $media->id }}">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                                    class="feather feather-x">
                                                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                                                </svg>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            @endif

                                            </div>
                                        </div>
                                    </div>

                                  <input type="hidden" name="removed_media_ids" value="">

                                    {{-- Remarks --}}
                                    <div class="col-md-12">
                                        <div class="mb-1">  
                                            <label class="form-label">Final Remarks</label> 
                                           <textarea rows="4" class="form-control editable-field text-start" placeholder="Enter Remarks here..." name="remarks">
                                                {{ @$lr->remarks ?? '' }}
                                            </textarea>

                                        </div>
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
    <div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
  <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header p-0 bg-transparent">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body alertmsg text-center warning">
              <i data-feather='alert-circle'></i>
              <h2>Are you sure?</h2>
              <p>Are you sure you want to <strong>Amend</strong> this <strong>{{request() -> type == "Lorry Receipt" ? "Lorry Receipt" : "Lorry Receipt"}}</strong>?</p>
              <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
              <button type="button" data-bs-dismiss="modal" onclick = "amendConfirm();" class="btn btn-primary">Confirm</button>
          </div> 
      </div>
  </div>
</div>
        {{-- Amendment Modal --}}

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
                        <div class = "col-md-12">
                            <div class="mb-1">
                                <label class="form-label">Upload Document</label>
                                <input name = "amend_attachments[]" onchange = "addFiles(this, 'amend_files_preview')" type="file" class="form-control cannot_disable" max_file_count = "2" multiple/>
                            </div>
                        </div>
                        <div class = "col-md-12" style = "margin-top:19px;">
                            <!-- <div class="row" id = "amend_files_preview">
                            </div> -->
                        </div>
                    </div>
                    <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                </div>
                </div>
            </div>
            <div class="modal-footer justify-content-center">  
                 <button type="reset" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button> 
                <button type="button" class="btn btn-primary" onclick = "submitAmend();">Submit</button>
            </div>
        </div>
    </div>
    </div>
</form>
<!-----------Approval Modal---------->
<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <form class="ajax-input-form" method="POST" action="{{ route('document.approval.lorryReceipt') }}" data-redirect="{{ route('logistics.lorry-receipt.index') }}" enctype='multipart/form-data'>
            @csrf
            <input type="hidden" name="action_type" id="action_type">
            <input type="hidden" name="id" value="{{$lr->id ?? ''}}">
            <div class="modal-header">
               <div>
                  <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="popupTitle">Approve Application</h4>
               </div>
               <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pb-2">
               <div class="row mt-1">
                  <div class="col-md-12">
                     <div class="mb-2">
                        <label class="form-label">Remarks {{-- <span class="text-danger">*</span> --}}</label>
                        <textarea maxlength="250" name="remarks" class="form-control"></textarea>
                     </div>
                     <div class="mb-2" id="fileUploadSection">
                        <label class="form-label">Upload Document</label>
                        <input id="attachments" type="file" name="attachment[]" class="form-control" onchange="addFiles(this, 'lr_popup_file_preview')" multiple>
                        <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                     </div>
                     <div class="mt-2">
                        <!-- <div class="row" id="lr_popup_file_preview">
                        </div> -->
                     </div>
                  </div>
               </div>
            </div>
            <div class="modal-footer justify-content-center">  
               <button type="reset" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button> 
               <button type="submit" class="btn btn-primary">Submit</button>
            </div>
         </form>
      </div>
   </div>
</div>

 {{-- Amendment Modal --}}
    <div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>Purchase Bill</strong>? After Amendment this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>
    {{-- Send Mail Modal --}}
<div class="modal fade" id="sendMail" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
          <input type="hidden" name="action_type" id="action_type">
          <input type="hidden" name="id" value="{{isset($lr) ? $lr -> id : ''}}">
         <div class="modal-header">
            <div>
               <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="send_mail_heading_label">
               </h4>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body pb-2">
            <div class="row mt-1">
                {{--<div class="col-md-12">
                    <div class="mb-1">
                        <label class="form-label">Email From</label>
                        <input type="text" id='cust_mail' name="email_from" class="form-control cannot_disable">
                    </div>
                </div>--}}
                <div class="col-md-12">
                    <div class="mb-1">
                        <label class="form-label">Email To</label>
                        <input type="text" id='cust_mail' name="email_to" class="form-control mail_modal cannot_disable" placeholder="Enter Consignee Email">
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-1">
                        <label class="form-label">CC To</label>
                        <select name="cc_to[]" class="select2 form-control mail_modal cannot_disable" multiple>
                            @foreach ($users as $index => $user)
                                <option value="{{ $user->email }}" {{ $user->id == $lr->created_by ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="mb-1">
                        <label class="form-label">Remarks</label>
                        <textarea name="remarks" id="mail_remarks" class="form-control mail_modal cannot_disable"></textarea>
                    </div>
                </div>
            </div>
         <div class="modal-footer justify-content-center">  
            <button type="reset" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button> 
            <button type="submit" id="mail_submit" class="btn btn-primary">Submit</button>
         </div>
      </div>
   </div>
</div>
</div>

@endsection
@section('scripts')
<script>
function handleFormStatusControl(status = null) {
    const formStatus = status || document.querySelector("#statusInput")?.value || "draft";

    // Enable/Disable fields
    document.querySelectorAll('.editable-field').forEach(function (el) {
        if (formStatus === 'submitted' || formStatus === 'approved' || formStatus === 'approval_not_required') {
            el.setAttribute('disabled', 'disabled');
        } else {
            el.removeAttribute('disabled');
        }
    });

    // Show/Hide buttons
    const deleteButton = document.querySelector("#deleteButton");
    const addItemButton = document.querySelector("#addItemButton");

    if (formStatus === 'submitted' || formStatus === 'approved' || formStatus === 'approval_not_required') {
        deleteButton?.classList.add('d-none');
        addItemButton?.classList.add('d-none');
    } else {
        deleteButton?.classList.remove('d-none');
        addItemButton?.classList.remove('d-none');
    }
}

// Call on page load
document.addEventListener("DOMContentLoaded", function () {
    handleFormStatusControl();
});
</script>


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
    const freightCharge = parseFloat($('#freight_charges').val()) || 0;

    const total = subTotal + lr + freightCharge;

    $('#freightAmount').text(subTotal.toFixed(2));
    $('#subTotalAmount').text(subTotal.toFixed(2));
    $('#lrCharges').text(lr.toFixed(2));
    $('#FreightChargeshtml').text(freightCharge.toFixed(2));
    $('#totalFreightAmount').text(total.toFixed(2));

    // Update hidden fields
    $('#subTotalInput').val(subTotal.toFixed(2));
    $('#totalFreightInput').val(total.toFixed(2));
}

// show location details here
function updateRouteDetailsUI() {
    const $rows = $('#item-table-body').find('tr');

    const source = $('#sourceText').text() || 'Not selected';
    const destination = $('#destinationText').text() || 'Not selected';
    let totalWeight = 0;
    let totalArticles = 0;
    let totalPoints = 0;

    $rows.each(function () {
        const weight = parseFloat($(this).find('input[name*="[weight]"]').val()) || 0;
        const articles = parseInt($(this).find('input[name*="[no_of_articles]"]').val()) || 0;
        totalWeight += weight;
        totalArticles += articles;
        totalPoints += 1;
    });

    const $vehicle = $('#vehicle_id option:selected');
    const vehicleText = $vehicle.length ? $vehicle.text() : $('#routeVehicle').text() || 'Not selected';
    const vehicleCapacity = $vehicle.length ? $vehicle.data('capacity') : $('#routeCapacity').text() || '--';

    $('#routeSource').text(source);
    $('#routeDestination').text(destination);
    $('#routeWeight').text(totalWeight);
    $('#routeArticles').text(totalArticles);
    $('#routePoints').text(totalPoints);
    $('#routeVehicle').text(vehicleText);
    $('#routeCapacity').text(vehicleCapacity);
}



 let rowIndex = {{ $rowIndex ?? 1 }};

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
        // if (!currentVal || parseFloat(currentVal) === 0) {
        //     if (index < activeFreePoint) {
        //         freightInput.val(0);
        //     } else {
        //         if (fixedAmountGlobal) {
        //             freightInput.val(fixedAmountGlobal);
        //         } else {
        //             freightInput.val(freeAmountGlobal);
        //         }
        //     }
        // }

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
    
    // delete row script
$(document).on('click', '#deleteSelected', function (e) {
    e.preventDefault(); 
    e.stopImmediatePropagation(); 

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

    Swal.fire({
        icon: 'question',
        title: 'Are you sure?',
        text: 'Do you want to delete the selected row(s)?',
        showCancelButton: true,
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if (result.isConfirmed) {
            selectedRows.remove();
            applyFreightToRows(null, null, selectedRows)
            calculateTotals();
            updateRouteDetailsUI();
        }
    });
});

// $(document).ready(function () {
//     $('#item-table-body tr').each(function () {
//         const $row = $(this);
//         const locationId = $row.find('input[name*="[location_id]"]').val();
//         if (locationId && globalSourceId) {
//             checkFreePoint(locationId, globalSourceId, $row, true); 
//         }
//     });
// });

</script>

<script>
    $(document).on('input', '#no_of_bundles', function () {
    var perbundle = parseFloat($("#per_bundles").val()) || 0;
    var nobundle = parseFloat($("#no_bundles").val()) || 0;
    var changenobundle = parseFloat($(this).val()) || 0;
    var nobundleamount = parseFloat($("#no_bundles_amount").val()) || 0;

    console.log("Per bundle:", perbundle);
    console.log("No of bundles:", nobundle);
    console.log("change bundles:", changenobundle);
    console.log("No bundle amount:", nobundleamount);

    if (nobundle > changenobundle) {
        console.log("👉 IF chal gaya");
        $('#freight_charges').val(nobundleamount);
    } else {
        console.log("👉 ELSE chal gaya");
        var valuecal = changenobundle - nobundle;
        console.log(valuecal);
        var bundleamount = (perbundle * valuecal) + nobundleamount;
         console.log(bundleamount);
        $('#freight_charges').val(bundleamount);
    }
    calculateTotals();
});

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

// $(document).ready(function () {
//     getDocNumberByBookId();
// });
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
            minLength: 0,

            source: function (request, response) {
                const type = $input.data('type');
                const searchTerm = request.term.toLowerCase();
                const sourceId = $('#sourceIdInput').val();
                const destinationId = $('#destinationIdInput').val();

                let filtered = $.grep(routeMasters, function (item) {
                    const match = item.label.toLowerCase().includes(searchTerm);

                    if (!match) return false;

                    // 🔽 Exclude source & destination only for location field
                    if (type === 'location') {
                        if (item.id == sourceId || item.id == destinationId) {
                            return false;
                        }
                    }

                    return true;
                });

                // 🔄 Optional: show 'No results found' if nothing matches
                if (filtered.length === 0) {
                    filtered = [{
                        label: 'No results found',
                        value: '',
                        id: null,
                        isDummy: true
                    }];
                }

                response(filtered);
            },

            select: function (event, ui) {
                if (ui.item.isDummy) {
                    event.preventDefault();
                    return false;
                }

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
                        calculateTotals();
                    }
                }

                return false;
            },

            change: function (event, ui) {
                const type = $input.data('type');

                if (ui.item && !ui.item.isDummy) {
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
    @foreach($customers as $customer)
        {
            label: "{{ addslashes($customer->company_name) }}",
            value: "{{ addslashes($customer->company_name) }}",
            id: {{ $customer->id }}
        },
    @endforeach
  
];

$(document).on('focus', '.customer-autocomplete', function () {
    const $input = $(this);

    if (!$input.data('ui-autocomplete')) {
        $input.autocomplete({
            minLength: 0,
            source: function (request, response) {
                const results = $.ui.autocomplete.filter(customerList, request.term);

                if (!results.length) {
                    results.push({
                        label: 'No results found',
                        value: '',
                        id: ''
                    });
                }

                response(results);
            },
            select: function (event, ui) {
                if (ui.item.value === '') {
                    event.preventDefault(); 
                    return false;
                }

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
    @foreach($drivers as $driver)
        {
            label: "{{ addslashes($driver->name) }}",
            value: "{{ addslashes($driver->name) }}",
            id: {{ $driver->id }}
        },
    @endforeach

];

$(document).on('focus', '.driver-autocomplete', function () {
    const $input = $(this);

    if (!$input.data('ui-autocomplete')) {
        $input.autocomplete({
            minLength: 0,
            source: function (request, response) {
                const term = $.ui.autocomplete.escapeRegex(request.term);
                const matcher = new RegExp(term, "i");

                const matches = $.grep(driverList, function (item) {
                    return matcher.test(item.label);
                });

                if (matches.length) {
                    response(matches);
                } else {
                    response([{ label: "No results found", value: "", id: "" }]);
                }
            },
            select: function (event, ui) {
                if (ui.item.label === "No results found") {
                    event.preventDefault(); 
                    return false;
                }

                $input.val(ui.item.label);
                $input.closest('div').find('.driver-id').val(ui.item.id);
                return false;
            }
        }).focus(function () {
            $(this).autocomplete('search', '');
        });
    }
});


</script>   

@if($vehicleNumbers->isNotEmpty())
<script>
const vehicleNumbers = [
    @foreach($vehicleNumbers as $vn)
        {
            label: "{{ $vn->lorry_no }} ({{ $vn->vehicleType->name ?? '' }})",
            value: "{{ $vn->lorry_no }} ({{ $vn->vehicleType->name ?? '' }})",
            id: {{ $vn->id }}
        }{{ !$loop->last ? ',' : '' }}
    @endforeach
];
</script>
@endif

<script>

$('.vehicle-number-autocomplete').each(function () {
    $(this).autocomplete({
        source: function (request, response) {
            const results = $.ui.autocomplete.filter(vehicleNumbers, request.term);
            if (!results.length) {
                results.push({
                    label: 'No results found',
                    value: '',
                    id: ''
                });
            }
            response(results);
        },
        minLength: 0,
        select: function (event, ui) {
            if (ui.item.value === '') {
                return false;
            }
            $(this).val(ui.item.label);
            $(this).closest('div').find('.vehicle-number-id').val(ui.item.id);
            return false;
        }
    }).focus(function () {
        $(this).autocomplete('search', '');
    });
});




</script>
<script>

  // Make it globally accessible

 function fetchFreightCharge(sourceId,destId,vehicleId,custId) {
        sourceId    = sourceId   || $('input[name="source_id"]').val();
        const destinationId      = destId     || $('input[name="destination_id"]').val();
        vehicleId = vehicleId  || $('input[name="vehicle_number_id"]').val();
        const customerId      = custId     || $('input[name="customer_id"]').val();

        console.log("Final values =>", sourceId, destId, vehicleId, custId);

        if (!sourceId || !destinationId) return;

        $.ajax({
            url: '/freight-charge-details',
            method: 'GET',
            data: {
                source_id: sourceId,
                destination_id: destinationId,
                vehicle_id:vehicleId,
                customer_id:customerId
            },
            success: function (response) {
                if(response.message == 'Get freight charge data'){
                    $('#distance').val(response.distance).prop('disabled', true);
                    $('#freight_charges').val(response.freight_charges).prop('disabled', true);
                    $('#distanceInput').val(response.distance);
                    $('#freightCharges').val(response.freight_charges);
                    $('#FreightChargeshtml').text(
                        parseFloat(response.freight_charges ?? 0)
                    );
                    $('#no_of_bundles').val(response.no_bundle);
                    $('#per_bundles').val(response.per_bundle);
                    $('#no_bundles').val(response.no_bundle);
                    $('#no_bundles_amount').val(response.freight_charges);

                    // ✅ Set text content for display
                    $('#routeVehicle').text(response.vehicle_type_name);
                    $('#routeCapacity').text(response.vehicle_type_capacity + ' ' + response.vehicle_type_unit_name);
                    $('#routeSource').text(response.source_name);
                    $('#routeDestination').text(response.destination_name);
                    calculateTotals();
                }
                else if(response.message && response.message.includes('No freight charge found'))
                {
                    if (lastFreightChargeMessage !== response.message) 
                    {
                        console.log('Resetting fields because of new "No freight charge found" message');
                        console.log('else wala part');
                        $('#distance').val('').prop('disabled', false);
                        $('#freight_charges').val('').prop('disabled', false);
                        $('#distanceInput').val('');
                        $('#freightCharges').val('');
                        $('#FreightChargeshtml').text('0.00');
                    

                        // ✅ Set text content for display
                        $('#routePoints').html('');
                        $('#routeWeight').html('');
                        $('#routeArticles').html('');
                        $('#routeVehicle').html('');
                        $('#routeCapacity').html('');
                        $('#routeSource').html('');
                        $('#routeDestination').html('');
                    }
                    lastFreightChargeMessage = response.message;  // Update last message
                    calculateTotals();
                }
                

            },
            error: function (response) {
                let message = response.responseJSON?.message ?? '';

                if (message.includes('No freight charge found')) 
                {
                    if (lastFreightChargeMessage !== message) 
                    {
                        console.log('Resetting fields due to new "No freight charge found" message.');

                        $('#distance').val('').prop('disabled', false);
                        $('#freight_charges').val('').prop('disabled', false);
                        $('#distanceInput').val('');
                        $('#freightCharges').val('');
                        $('#FreightChargeshtml').text('0.00');
                
                        // ✅ Set text content for display
                        $('#routePoints').html('');
                        $('#routeWeight').html('');
                        $('#routeArticles').html('');
                        $('#routeVehicle').html('');
                        $('#routeCapacity').html('');
                        $('#routeSource').html('');
                        $('#routeDestination').html('');
                    }
                }
                lastFreightChargeMessage = message;
                calculateTotals();
            }
        });
    }


let oldSource = $('input[name="source_name"]').val();
let oldDestination = $('input[name="destination_name"]').val();

$('input[name="source_name"], input[name="destination_name"], input[name="vehicle_number"], input[name="customer_name"]')
    .on('autocompleteselect', function (event, ui) {
    
        const type = $(this).attr("name");

        let sourceId = $('input[name="source_id"]').val();
        let destId   = $('input[name="destination_id"]').val();
        let vehicleId= $('input[name="vehicle_number_id"]').val();
        let custId   = $('input[name="customer_id"]').val();
        console.log(type, ui);
        // Agar current field source/dest/vehicle/customer hai to ui.item.id ka use karo
        if (type === "source_name") sourceId = ui.item.id;
        if (type === "destination_name") destId = ui.item.id;
        if (type === "vehicle_number") vehicleId = ui.item.id;
        if (type === "customer_name") custId = ui.item.id;

        console.log('sourceId:', sourceId);
        console.log('destId:', destId);
        console.log('vehicleId:', vehicleId);
        console.log('custId:', custId);

        if (oldSource !== sourceId || oldDestination !== destId) {
            
            let defaultRow = `
                <tr>
                    <td class="customernewsection-form">
                        <div class="form-check form-check-primary custom-checkbox">
                            <input type="checkbox" class="form-check-input rowCheckbox" name="locations[0][selected]" id="row_0">
                            <label class="form-check-label" for="row_0"></label>
                        </div> 
                    </td>
                    <td class="poprod-decpt">
                        <input type="text" name="locations[0][location_name]" placeholder="Select" 
                               class="form-control mw-100 location-update route-master-autocomplete" 
                               data-type="location" />
                        <input type="hidden" name="locations[0][location_id]" class="route-master-id" data-type="location" />
                    </td>
                    <td>
                        <select class="form-select mw-100" name="locations[0][type]">
                            <option value="">Select</option>
                            <option value="Pick Up">Pick Up</option>
                            <option value="Drop Off" selected>Drop Off</option>
                        </select> 
                    </td>
                    <td><input type="text" name="locations[0][no_of_articles]"  class="form-control mw-100" /></td>
                    <td><input type="text" name="locations[0][weight]"  class="form-control mw-100" /></td>
                    <td><input type="text" name="locations[0][freight]"  class="form-control mw-100 text-end" /></td>
                </tr>
            `;

            $("#item-table-body").html(defaultRow);

            // पुरानी values update कर दो
            oldSource = sourceId;
            oldDestination = destId;
        }

    if (sourceId && destId && vehicleId) {
            fetchFreightCharge(sourceId,destId,vehicleId,custId);
    }
});

    $(document).on('change', 'input[name*="[weight]"], input[name*="[no_of_articles]"]', function () {
        updateRouteDetailsUI(); 
        fetchFreightCharge();   
    });

    $(document).ready(function () {
  
    // Distance: sync on keyup
    $(document).on('keyup', '#distance', function () {
        const distance = $(this).val();
        $('#distanceInput').val(distance);
    });

    // Freight Charges: sync on keyup
    $(document).on('keyup', '#freight_charges', function () {
        const charges = $(this).val();
        $('#freightCharges').val(charges);
         calculateTotals(); 
    });
    fetchFreightCharge(); 
});

</script>
<script>
$(document).ready(function () {
    const selectedLocationId = $('#locationId').val();
    const selectedCostCenterId = "{{ old('cost_center_id', $lr->cost_center_id ?? '') }}";


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
let globalSourceId = $('#sourceIdInput').val();
let globalVehicleId = $('#vehicle_number_id').val();
let globalCustomerId = $('#customer_id').val();

let pricingCache = {}

  function checkFreePoint(locationId = null, sourceId = null, vehicleId = null, customerId = null, $targetRow = null, isEditLoad = false) {
    if (!locationId || !sourceId) return;

    $.ajax({
        url: '/get-location-pricing',
        type: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            location_id: locationId,
            source_id: sourceId,
            vehicle_id: vehicleId,
            customer_id: customerId,
        },
        success: function (res) {
            $('#fixedAmountDisplay').empty();

            const sourceAmount = parseInt(res.source_amount || 0);
            $('#sourceDefaultAmountGlobal').val(sourceAmount);

            // Store response to cache
            pricingCache[locationId] = {
                type: res.status,
                free_point: parseInt(res.free_point || 0),
                amount: parseInt(res.amount || 0),
                freeAmount: parseInt(res.free_amount || 0),
            };

            // Set globals
            $('#activeFreePointGlobal').val(pricingCache[locationId].free_point || 0);
            $('#fixedAmountGlobal').val(pricingCache[locationId].amount || 0);
            $('#freeAmountGlobal').val(pricingCache[locationId].freeAmount || 0);

            applyFreightToRows(pricingCache[locationId], $targetRow); 
        }
    });
}


function applyFreightToRows($specificRow = null,$row, deletedRow = null) {

    const activeFreePoint = parseInt($('#activeFreePointGlobal').val() || 0);
    const sourceDefaultAmount = parseFloat($('#sourceDefaultAmountGlobal').val() || 0);
    if (deletedRow !== null) {
        const $rows = $('#item-table-body').find('tr');
        //$rows.eq(0).find('input[name*="[freight]"]').val(0);

        if (deletedRow.length <= activeFreePoint) {
            for (let i = 0; i < activeFreePoint && i < $rows.length; i++) {
                $rows.eq(i).find('input[name*="[freight]"]').val(0);
            }
        }

        return;
    }
   
    const freightAmount = parseFloat($row.find('input[name*="[freight]"]').val()) || 0;

    const locationId = $row.find('input[name*="[location_id]"]').val()?.trim();
    const $freightInput = $row.find('input[name*="[freight]"]');

    if (!locationId) return;
       const pricing = $specificRow;

    if (!pricing) {
        $freightInput.val(sourceDefaultAmount > 0 ? sourceDefaultAmount : '');
        return;
    }
    $index = $row.index();

     if (pricing) {
            if (pricing.type === 'both_exist') {
              if ($index < activeFreePoint) {

                $freightInput.val(0);
            } else {
                if(pricing.amount){
                  $freightInput.val(pricing.amount && parseFloat(pricing.amount) > 0 ? parseFloat(pricing.amount) : 0);
                }else{
                    $freightInput.val(pricing.freeAmount && parseFloat(pricing.freeAmount) > 0 ? parseFloat(pricing.freeAmount) : 0);
                }
                
            }

            }else if (pricing.type === 'free_point') {
                if (index < activeFreePoint) {
                    $freightInput.val(0);
                } else {
                    $freightInput.val(parseFloat(pricing.freeAmount));
                }
            } else if (pricing.type === 'exists_in_fixed') {
                $freightInput.val(parseFloat(pricing.amount));
            } else {
                $freightInput.val(sourceDefaultAmount > 0 ? sourceDefaultAmount : '');
            }
        } else {
            $freightInput.val(sourceDefaultAmount > 0 ? sourceDefaultAmount : '');
        }


        if (deletedRow !== null) {
        const $targetRow = $rows.eq(deletedRow);
        const locationId = $targetRow.find('input[name*="[location_id]"]').val()?.trim();
        const $freightInput = $targetRow.find('input[name*="[freight]"]');

        const pricing = pricingCache[locationId]; 

        if (deletedRow <= activeFreePoint) {
            $freightInput.val(0);
        } else {
            $freightInput.val(pricing?.amount && parseFloat(pricing.amount) > 0 ? parseFloat(pricing.amount) : 0); 
        }
    }

    
    calculateTotals();
}



function handleLocationUpdate($input) {
    const $row = $input.closest('tr');
    const locationId = $row.find('input[name*="[location_id]"]').val();
    const sourceId = $('#sourceIdInput').val();
    const vehicleId = $('#vehicle_number_id').val();
    const customerId = $('#customer_id').val();



    if (locationId && sourceId && vehicleId && customerId) {
        checkFreePoint(locationId, sourceId, vehicleId, customerId, $row); 
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
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you really want to revoke this Lorry Receipt?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Revoke it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
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
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: data.message,
                                    icon: 'error',
                                }).then(() => {
                                    window.location.href = "{{ route('logistics.lorry-receipt.index') }}";
                                });
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
        }
    });
    });

      function setApproval() {
        document.getElementById('popupTitle').innerText = 'Approve Lorry Receipt';
        document.getElementById('action_type').value = 'approve';
    }

    function setReject() {
        document.getElementById('popupTitle').innerText = 'Reject Lorry Receipt';
        document.getElementById('action_type').value = 'reject';
    }

    function openModal(id) {
        $('#' + id).modal('show');
    }

function amendConfirm() {
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
        newSubmitButton.onclick = function() {
            openAmendConfirmModal();
        };

        if (buttonParentDiv) {
            buttonParentDiv.appendChild(newSubmitButton);
        }

        if (feather) {
            feather.replace({
                width: 14,
                height: 14
            });
        }

    handleFormStatusControl("draft");
}

    function openAmendConfirmModal() {
        $('#amendConfirmPopup').modal('show');
    }

    // Submit amendment form
    window.submitAmend = function () {
        handleFormStatusControl("draft");
        let remark = $("#amendConfirmPopup").find('[name="amend_remarks"]').val();
        $("#action_type_main").val("amendment");
        $("#amendConfirmPopup").modal('hide');
        $("#lorry_receipt_form").submit();
    };


     var currentRevNo = $("#revisionNumber").val();
     $(document).on('change', '#revisionNumber', function (e) {
        e.preventDefault();
        const selectedRev = e.target.value;
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('revisionNumber', selectedRev);
        $("#revisionNumber").val(currentRevNo);
        window.open(currentUrl.toString(), '_blank');
    });


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
    function appendFilePreviews(fileUrl, previewElementId, index, fileId = null, inputName = '') {
    const previewContainer = document.getElementById(previewElementId);
    if (!previewContainer) return;

    const fileName = fileUrl.split('/').pop();

    const previewHtml = `
        <div class="col-1 file-preview-item image-uplodasection expenseadd-sign" data-index="${index}" data-file-id="${fileId ?? ''}">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="feather feather-file-text me-2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="16" y1="13" x2="8" y2="13"/>
                <line x1="16" y1="17" x2="8" y2="17"/>
                <polyline points="10 9 9 9 8 9"/>
            </svg>
             <div class="delete-img text-danger" data-edit-flag="true" data-index="${index}" data-input-id="${previewElementId}" data-input-name="${inputName}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </div>
        </div>
    `;

     previewContainer.insertAdjacentHTML('beforeend', previewHtml);
    feather.replace({ width: 20, height: 20 });
}


function addFiles(element, previewElementId) {
    const input = element;
    const allowedMaxFilesCount = Number(input.getAttribute('max_file_count') || 1);
    const inputId = input.name.replace('[]', '');
    const files = Array.from(input.files);

    if (!fileInputData[inputId]) {
        fileInputData[inputId] = [];
    }

    const currentCount = fileInputData[inputId].length;

    if ((files.length + currentCount) > allowedMaxFilesCount) {
        Swal.fire({
            title: 'Error!',
            text: `Maximum ${allowedMaxFilesCount} files are allowed.`,
            icon: 'error'
        });
        resetInput(inputId, input);
        return;
    }

    let allFiles = [...fileInputData[inputId], ...files];
    let invalidMessage = '';

    for (const file of files) {
        const ext = file.name.split('.').pop().toLowerCase();
        const sizeKB = file.size / 1024;

        if (!ALLOWED_EXTENSIONS.includes(ext) || !ALLOWED_MIME_TYPES.includes(file.type)) {
            invalidMessage = 'Please select valid file types.';
            break;
        }

        if (sizeKB > MAX_FILE_SIZE) {
            invalidMessage = 'Each file must be less than 5MB.';
            break;
        }
    }

    if (invalidMessage) {
        Swal.fire({
            title: 'Error!',
            text: invalidMessage,
            icon: 'error'
        });
        resetInput(inputId, input);
        return;
    }

    fileInputData[inputId] = allFiles.reduce((unique, file) => {
        if (!unique.some(f => f.name === file.name && f.size === file.size)) {
            unique.push(file);
        }
        return unique;
    }, []);


    const newDt = new DataTransfer();
    fileInputData[inputId].forEach(file => newDt.items.add(file));
    input.files = newDt.files;
    refreshPreviews(previewElementId, inputId);
    feather.replace({ width: 20, height: 20 });
}

function refreshPreviews(previewElementId, inputId) {
    const previewWrapper = document.getElementById(previewElementId);
    if (!previewWrapper) return;

    const jsPreviews = previewWrapper.querySelectorAll('.file-preview-item');
    jsPreviews.forEach(el => el.remove());

    fileInputData[inputId].forEach((file, index) => {
        const fileUrl = URL.createObjectURL(file);
        appendFilePreviews(fileUrl, previewElementId, index, null, inputId);
    });
}


function resetInput(inputId, input) {
    const tempDt = new DataTransfer();
    fileInputData[inputId].forEach(file => tempDt.items.add(file));
    input.files = tempDt.files;
}

document.addEventListener('click', function (e) {
    const deleteIcon = e.target.closest('.delete-img');
    if (!deleteIcon) return;

    const isEditMode = deleteIcon.getAttribute('data-edit-flag') === 'true';

    if (isEditMode && deleteIcon.hasAttribute('data-id')) {
        // 🔴 Existing file: remove DOM + store ID in hidden input
        const mediaId = deleteIcon.getAttribute('data-id');
        const removeInput = document.querySelector('input[name="removed_media_ids"]');

        if (removeInput) {
            let existing = removeInput.value ? removeInput.value.split(',') : [];
            if (!existing.includes(mediaId)) {
                existing.push(mediaId);
                removeInput.value = existing.join(',');
            }
        } else {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'removed_media_ids';
            input.value = mediaId;
            document.querySelector('form').appendChild(input);
        }

      
        deleteIcon.closest('.image-uplodasection').remove();
        return;
    }


    const index = parseInt(deleteIcon.getAttribute('data-index'));
    const previewElementId = deleteIcon.getAttribute('data-input-id');
    const inputId = deleteIcon.getAttribute('data-input-name');
    const input = document.querySelector(`input[name="${inputId}[]"]`);

    if (!fileInputData[inputId]) return;
    fileInputData[inputId].splice(index, 1);

    const dt = new DataTransfer();
    fileInputData[inputId].forEach(file => dt.items.add(file));
    input.files = dt.files;
    deleteIcon.closest('.image-uplodasection').remove();

    feather.replace({ width: 20, height: 20 });
});



 /*Amendment modal open*/
        $(document).on('click', '.amendmentBtn', (e) => {
            $("#amendmentconfirm").modal('show');
        });
      $(document).on('click', '#amendmentSubmit', (e) => {
            let url = new URL(window.location.href);
            url.search = '';
            url.searchParams.set('amendment', 1);
            let amendmentUrl = url.toString();
            window.location.replace(amendmentUrl);
        });

        /* Trigger Send Mail Modal With Data*/
function sendMailTo() {
        $('.ajax-validation-error-span').remove();
        $('.reset_mail').removeClass('is-invalid');
        $('.mail_modal').prop('readonly', false);
        $('.mail_modal').prop('disabled', false);
        $('[name="cc_to[]"]').prop('disabled', false);
        $('[name="cc_to[]"]').prop('readonly', false);
        $('[name="email_to"]').val($('#consigneeemail').val());

        const vendorEmail = $('#consigneeemail').val();
        const vendorName = "{{ isset($po) ? $po->vendor->company_name : '' }}";
        const emailInput = document.getElementById('cust_mail');
        const header = document.getElementById('send_mail_heading_label');
        if (emailInput) {
            emailInput.value = vendorEmail;
        }
        if(header)
        {
            header.innerHTML = "Send Mail";
        }
        $("#mail_remarks").val("Your Lorry Receipt has been successfully generated.");
        $('#sendMail').modal('show');
    }

    $("#mail_submit").on('click', function(e) {
    e.preventDefault();
    document.getElementById('erp-overlay-loader').style.display = "flex";
    $('#mail_submit').prop('disabled', true);
    let actionType = $("#action_type").val();
    let id = $("input[name='id']").val();
    let emailTo = $("#cust_mail").val();
    let ccTo = $('[name="cc_to[]"]').val();
    let remarks = $("#mail_remarks").val();
    if(!emailTo) {
        Swal.fire({
            title: 'Error!',
            text: "Please enter email to.",
            icon: 'error',
        });
        document.getElementById('erp-overlay-loader').style.display = "none";
        $('#mail_submit').prop('disabled', false);
        return false;
    }
    if(!remarks) {
        Swal.fire({
            title: 'Error!',
            text: "Please enter remarks.",
            icon: 'error',
        });
        document.getElementById('erp-overlay-loader').style.display = "none";
        $('#mail_submit').prop('disabled', false);
        return false;
    }
    $.ajax({
        url: '{{route("logistics.lorry-receipt.lorryMail",['type' => 'lorry-receipt'])}}',
        type: 'POST',
        data: {
            action_type: actionType,
            id: id,
            email_to: emailTo,
            cc_to: ccTo,
            remarks: remarks,
        },
        success: function(response) {
            console.log(response);
            if (response.status == 'success') {
                Swal.fire({
                    title: 'Success!',
                    text: response.message,
                    icon: 'success',
                });
                $('#sendMail').modal('hide');
                setTimeout(() => { 
                    history.go(-1);
                },1000);
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: response.message,
                    icon: 'error',
                });
            }
            document.getElementById('erp-overlay-loader').style.display = "none";
            $('#mail_submit').prop('disabled', false);
        },
        error: function(xhr, status, error) {
            Swal.fire({
                title: 'Error!',
                text: xhr.responseJSON.message || "Something went wrong!",
                icon: 'error',
            });
            $('#mail_submit').prop('disabled', false);
            document.getElementById('erp-overlay-loader').style.display = "none";
        }
    });
});
</script>

@endsection