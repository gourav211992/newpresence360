@extends('layouts.app')

@section('content')
<style>
    .itemactive { position: absolute; left: 6px; font-size: 11px; top: 6px; color: #fff } 
    .iteminactive {  left: 24px; color: #999 } 
    .customernewsection-form .statusactiinactive .form-check-input { width: 80px; cursor: pointer}
    .customernewsection-form .statusactiinactive .form-check-input:checked + .itemactive { display: inline-block}
    .customernewsection-form .statusactiinactive .form-check-input:checked ~ .iteminactive { display: none }
    
    .customernewsection-form .statusactiinactive .form-check-input:not(:checked) + .itemactive { display: none}
    .customernewsection-form .statusactiinactive .form-check-input:not(:checked) ~ .iteminactive { display: inline-block }
</style>
    <!-- BEGIN: Content-->
 <form class="ajax-input-form" method="POST" action="{{ route('vendor.update', $vendor->id) }}" data-redirect="{{ url('/vendors') }}" id="vendor_form" enctype="multipart/form-data">
 <input type="hidden" name="vendor_id" value="{{ $vendor->id ?? '' }}">
 <input type="hidden" name="vendor_code_type" value="{{ $vendorCodeType }}">
 <input type="hidden" id="documentStatus" name="document_status" value="{{ $vendor->documentStatus ?? '' }}">
   @csrf
    @method('PUT') 
    @php
        $isEditable = isset($vendor) && $vendor->status === 'draft';
        $statusValue = isset($vendor) && (strtolower($vendor->status) === 'active') && ($vendor->document_status == 'approval_not_required' || $vendor->document_status == 'approved') ? 'active' : 'inactive';
        $isChecked = $statusValue === 'active' ? 'checked' : '';
    @endphp
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
				<div class="row">
					<div class="content-header-left col-md-6 col-6 mb-2">
						<div class="row breadcrumbs-top">
							<div class="col-12">
								<h2 class="content-header-title float-start mb-0">Vendor</h2>
								<div class="breadcrumb-wrapper">
									<ol class="breadcrumb">
										<li class="breadcrumb-item"><a href="#">Home</a>
										</li>  
                                        <li class="breadcrumb-item"><a href="{{route('vendor.index')}}">Vendor</a>
										</li> 
										<li class="breadcrumb-item active">Edit</li>
									</ol>
								</div>
							</div>
						</div>
					</div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <div class="form-group breadcrumb-right" id="buttonsDiv">
                            <a href="{{ route('vendor.index') }}" class="btn btn-secondary btn-sm">
                                <i data-feather="arrow-left-circle"></i> Back
                            </a>
                            @if(!isset(request()->revisionNumber))
                                @if (isset($vendor))
                                   @if($buttons['delete'])
                                       <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                            data-url="{{ route('vendor.destroy', $vendor->id) }}"
                                            data-redirect="{{ route('vendor.index') }}"
                                            data-message="Are you sure you want to delete this record?">
                                           <i data-feather="trash-2" class="me-50"></i> Delete
                                        </button>
                                    @endif
                                     @if($buttons['amendDelete'])
                                     <button type="button" style="display:none;" id="btnAmendDelete" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                        data-url="{{ route('vendor.destroy', $vendor->id) }}"
                                        data-redirect="{{ route('vendor.index') }}"
                                        data-message="Are you sure you want to delete this record?">
                                        <i data-feather="trash-2" class="me-50"></i> Delete
                                    </button>
                                    @endif
                                    @if($buttons['draft'])
                                        <button type="submit" value="draft" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button">
                                            <i data-feather='save'></i> Save as Draft
                                        </button>
                                    @endif
                                    
                                    @if($buttons['submit'])
                                        <button type="submit" value="submitted" class="btn btn-primary btn-sm submit-button">
                                            <i data-feather="check-circle"></i> Submit
                                        </button>
                                    @endif
                                    @if($buttons['approve'])
                                        <button type="button" id="reject-button" data-bs-toggle="modal" data-bs-target="#approveModal" onclick="setReject();" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                                <line x1="9" y1="9" x2="15" y2="15"></line>
                                            </svg>
                                            Reject
                                        </button>
                                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal" onclick="setApproval();">
                                            <i data-feather="check-circle"></i> Approve
                                        </button>
                                    @endif
                                    @if($buttons['amend'])
                                        <button type="button" id="amendShowButton" onclick="openModal('amendmentconfirm')" class="btn btn-primary btn-sm mb-50 mb-sm-0">
                                            <i data-feather='edit'></i> Amendment
                                        </button>
                                    @endif
                                    @if($buttons['revoke'])
                                        <button type="button" id="revokeButton" onclick="revokeDocument();" class="btn btn-primary btn-sm mb-50 mb-sm-0">
                                            <i data-feather='rotate-ccw'></i> Revoke
                                        </button>
                                    @endif
                                @else
                                    <button type="submit" value="draft" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button"></i> Save as Draft</button>
                                    <button type="submit" value="submitted" class="btn btn-primary btn-sm submit-button"></i> Submit</button>
                                @endif
                            @endif
                            <input type="hidden" value="draft" name="current_status" id="document_status"/>
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
                                            <!--Start Vendor -->
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between"> 
                                                        <div>
                                                            <h4 class="card-title text-theme">Basic Information</h4>
                                                            <p class="card-text">Fill the details</p>
                                                        </div>
                                                        <div>
                                                            <div class="d-flex align-items-center">
                                                                <div class="form-check form-check-primary form-switch statusactiinactive me-1">
                                                                    <input type="hidden" name="status" id="status_hidden_input" value="{{ $statusValue ??''}}">
                                                                    <input type="checkbox" class="form-check-input" id="customSwitch3" {{ $isChecked }}>
                                                                    <span class="itemactive">Active</span>
                                                                    <span class="itemactive iteminactive">Inactive</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                      
                                                    </div> 
                                                </div>
                                                
                                                <div class="col-md-9">
                                                   <div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Vendor Name<span class="text-danger">*</span></label>  
                                                        </div>
                                                        <div class="col-md-9"> 
                                                            <input type="text" name="company_name" placeholder="Enter Vendor Name" class="form-control vendor-name-autocomplete" value="{{ $vendor->company_name ?? '' }}" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Vendor Type <span class="text-danger">*</span></label>  
                                                        </div>
                                                        <div class="col-md-4"> 
                                                            <div class="demo-inline-spacing">
                                                                @foreach ($vendorTypes as $type)
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input
                                                                            type="radio"
                                                                            id="{{ strtolower($type) }}"
                                                                            name="vendor_type"
                                                                            value="{{ $type }}"
                                                                            class="form-check-input"
                                                                            {{ $vendor->vendor_type == $type ? 'checked' : ($type === 'Regular' ? 'checked' : '') }}
                                                                        >
                                                                        <label class="form-check-label fw-bolder" for="{{ strtolower($type) }}">
                                                                            {{ $type }}
                                                                        </label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label class="form-label">
                                                                <span id="vendor_initial_label">Vendor Initials</span><span class="text-danger">*</span>
                                                            </label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="text" name="vendor_initial" class="form-control" placeholder="Enter Vendor Initials" value="{{ $vendor->vendor_initial ?? '' }}" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Sub Type</label>  
                                                        </div>
                                                        <div class="col-md-4"> 
                                                            <select name="vendor_sub_type" class="form-select select2">
                                                                @foreach ($vendorSubTypes as $type)
                                                                    <option value="{{ $type }}" 
                                                                        {{ $vendor->vendor_sub_type == $type ? 'selected' : ($type === 'Regular' && !$vendor->vendor_sub_type ? 'selected' : '') }}>
                                                                        {{ $type }}
                                                                    </option>
                                                                @endforeach
                                                            </select>  
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Vendor Code <span class="text-danger">*</span></label>  
                                                        </div>
                                                        <div class="col-md-4"> 
                                                            <input type="text" name="vendor_code" class="form-control" value="{{ $vendor->vendor_code }}" />
                                                        </div> 
                                                    </div>

                                                    <div class="row align-items-center mb-1"> 
                                                        <div class="col-md-3"> 
                                                            <label class="form-label">Organization Type</label>  
                                                        </div> 
                                                        <div class="col-md-4">  
                                                            <select name="organization_type_id" class="form-select select2">
                                                                @foreach ($organizationTypes as $type)
                                                                <option value="{{ $type->id }}" 
                                                                    @if($vendor->organization_type_id == $type->id) selected @elseif($type->name == 'Private Ltd') selected  @endif>
                                                                    {{ $type->name }}
                                                                </option>
                                                                @endforeach
                                                            </select>  
                                                        </div>
                                                    </div>

                                                    
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Group</label>
                                                        </div>
                                                        <div class="col-md-4 pe-sm-0 mb-1 mb-sm-0">
                                                            <input type="text" name="subcategory_name" class="form-control category-autocomplete" placeholder="Type to search group" value="{{ $vendor->subCategory->name ?? '' }}">
                                                            <input type="hidden" name="subcategory_id" class="category-id" value="{{ $vendor->subcategory_id ?? '' }}">
                                                            <input type="hidden" name="category_type" class="category-type" value="Vendor">
                                                            <input type="hidden" name="cat_initials" class="cat_initials-id"  value="{{ $vendor->subcategory->cat_initials ?? '' }}">
                                                        </div>
                                                    </div>
                                                    <p class="mb-0" style="color: red;"><b>Note*:</b> File must be 2MB max | Formats: pdf, jpg, jpeg, png</p>
                                                </div>

                                                <div class="col-md-3 border-start">
                                                    <div class="row align-items-center mb-2">
                                                        <div class="col-md-12">
                                                            <label class="form-label text-primary"><strong>Stop Purchasing</strong></label>
                                                            <div class="demo-inline-spacing">
                                                                @foreach ($options as $option)
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input
                                                                            type="radio"
                                                                            id="stop_purchasing_{{ strtolower($option) }}"
                                                                            name="stop_purchasing"
                                                                            value="{{ $option }}"
                                                                            class="form-check-input"
                                                                            {{ $vendor->stop_purchasing == $option ? 'checked' : '' }}
                                                                        >
                                                                        <label class="form-check-label fw-bolder" for="stop_purchasing_{{ strtolower($option) }}">
                                                                            {{ $option }}
                                                                        </label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-2">
                                                        <div class="col-md-12">
                                                            <label class="form-label text-primary"><strong>Stop Payment</strong></label>
                                                            <div class="demo-inline-spacing">
                                                                @foreach ($options as $option)
                                                                    <div class="form-check form-check-primary mt-25">
                                                                        <input
                                                                            type="radio"
                                                                            id="stop_payment_{{ strtolower($option) }}"
                                                                            name="stop_payment"
                                                                            value="{{ $option }}"
                                                                            class="form-check-input"
                                                                            {{ $vendor->stop_payment == $option ? 'checked' : '' }} >
                                                                        <label class="form-check-label fw-bolder" for="stop_payment_{{ strtolower($option) }}">
                                                                            {{ $option }}
                                                                        </label>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if(isset($vendor) && ($vendor->document_status !== "draft"))
                                                        @if((isset($approvalHistory) && count($approvalHistory) > 0) || isset($vendor->revision_number))
                                                            <div class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
                                                                <h5 class="mb-2 text-dark border-bottom pb-50 d-flex align-items-center justify-content-between">
                                                                    <strong><i data-feather="arrow-right-circle"></i> Approval History</strong>
                                                                    @if(!isset(request()->revisionNumber) && $vendor->document_status !== 'draft')
                                                                        <strong class="badge rounded-pill badge-light-secondary amendmentselect">Rev. No.
                                                                            <select class="form-select cannot_disable" id="revisionNumber">
                                                                                @for($i=$vendor->revision_number; $i >= 0; $i--)
                                                                                    <option value="{{$i}}" {{request('revisionNumber', $vendor->revision_number) == $i ? 'selected' : ''}}>{{$i}}</option>
                                                                                @endfor
                                                                            </select>
                                                                        </strong>
                                                                    @else
                                                                        @if ($vendor->document_status !== 'draft')
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
                                                        @endif
                                                    @endif

                                                 </div>
                                            </div>
                                             <!--End Vendor -->
											<div class="mt-1">
												<ul class="nav nav-tabs border-bottom mt-25" role="tablist">
													<li class="nav-item">
														<a class="nav-link active" data-bs-toggle="tab" href="#payment">General Details</a>
													</li>
                                                    <li class="nav-item">
														<a class="nav-link" data-bs-toggle="tab" href="#Shipping">Addresses</a>
													</li>
                                                    <li class="nav-item">
														<a class="nav-link" data-bs-toggle="tab" href="#Financial">Financial</a>
													</li>
													<li class="nav-item">
														<a class="nav-link" data-bs-toggle="tab" href="#amend">Contact Persons</a>
													</li>
													<li class="nav-item">
														<a class="nav-link" data-bs-toggle="tab" href="#schedule">Compliances</a>
													</li>
													<li class="nav-item">
														<a class="nav-link" data-bs-toggle="tab" href="#send">Bank Info</a>
													</li>
													<li class="nav-item">
														<a class="nav-link" data-bs-toggle="tab" href="#latestrates">Notes</a>
													</li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#Items">Items</a>
                                                    </li>
                                                    <li class="nav-item">
														<a class="nav-link" data-bs-toggle="tab" href="#supplierPortal">Vendor Portal</a>
													</li>

												</ul>

											   <div class="tab-content pb-1 px-1">
                                                        <!-- Vendor Detail Start -->
                                                        <div class="tab-pane active" id="payment">
                                                            
                                                           <div class="row align-items-center mb-1" id="reldVendorDropdown">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Parent Vendor</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="text" name="reld_vendor_name" class="form-control parent-vendor-autocomplete" placeholder="Type to search vendors" value="{{ $vendor->parentdVendor->company_name ?? ''}}">
                                                                    <input type="hidden" name="reld_vendor_id" class="reld_vendor_id" value=" {{ $vendor->reld_vendor_id ?? ''}}">
                                                                    <input type="hidden" class="vendor_id"  value="{{ $vendor->id ?? '' }}">
                                                                </div>
                                                            </div>

                                                            <!-- Related Party -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Related Party</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input" id="Related" name="related_party" {{ $vendor->related_party =='Yes' ? 'checked' : '' }}>
                                                                        <label class="form-check-label" for="Related">Yes/No</label>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1" id="contraLedger">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Contra Ledger</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="text" name="contra_ledger_name" class="form-control contra-ledger-autocomplete" placeholder="Type to search contra ledger" value="{{ $vendor->contraLedger->name ?? ''}}">
                                                                    <input type="hidden" name="contra_ledger_id" class="contra_ledger_id" value=" {{ $vendor->contra_ledger_id ?? ''}}">
                                                                </div>
                                                            </div>

                                                            <!-- Group Organizations -->
                                                            <div class="row align-items-center mb-1" id="groupOrganizationsDropdown" >
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Related Organizations</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <select class="form-select select2" name="enter_company_org_id" id="enter_company_org_id">
                                                                       <option value="">Select</option>
                                                                        @foreach ($groupOrganizations as $organization)
                                                                            <option value="{{ $organization->id }}" 
                                                                                {{ $vendor->enter_company_org_id == $organization->id ? 'selected' : '' }}>
                                                                                {{ $organization->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <!-- Email -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Email</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="input-group input-group-merge">
                                                                        <span class="input-group-text" id="basic-addon5"><i data-feather='mail'></i></span>
                                                                        <input type="email" class="form-control" name="email" value="{{ $vendor->email ?? '' }}" placeholder="">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Phone -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Phone</label>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <div class="input-group input-group-merge">
                                                                        <span class="input-group-text" id="basic-addon5"><i data-feather='phone'></i></span>
                                                                        <input type="text" class="form-control numberonly" name="phone" value="{{ $vendor->phone ?? '' }}" placeholder="Phone">
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-2">
                                                                    <div class="input-group input-group-merge">
                                                                        <span class="input-group-text" id="basic-addon5"><i data-feather='smartphone'></i></span>
                                                                        <input type="text" class="form-control numberonly" id="phone_mobile" name="mobile" value="{{ $vendor->mobile ?? '' }}" placeholder="Mobile">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Whatsapp Number -->
                                                            <div class="row mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Whatsapp Number</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="input-group input-group-merge">
                                                                        <span class="input-group-text" id="basic-addon5"><i data-feather='phone'></i></span>
                                                                        <input type="text" class="form-control numberonly" id="whatsapp_number" name="whatsapp_number" value="{{ $vendor->whatsapp_number ?? '' }}">
                                                                    </div>
                                                                    <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input" id="colorCheck1" name="whatsapp_same_as_mobile" {{ $vendor->whatsapp_same_as_mobile ? 'checked' : '' }}>
                                                                        <label class="form-check-label" for="whatsapp_same_as_mobile">Same as Mobile No.</label>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Notification -->
                                                            <div class="row align-items-center mb-3">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Notification</label>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <div class="demo-inline-spacing">
                                                                        <div class="form-check form-check-primary mt-2">
                                                                            <input type="checkbox" class="form-check-input" id="Email" name="notification[]" value="email" {{ in_array('email', $notifications ?? []) ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="Email">Email</label>
                                                                        </div>

                                                                        <div class="form-check form-check-primary mt-2">
                                                                            <input type="checkbox" class="form-check-input" id="SMS" name="notification[]" value="sms" {{ in_array('sms', $notifications ?? []) ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="SMS">SMS</label>
                                                                        </div>

                                                                        <div class="form-check form-check-primary mt-2">
                                                                            <input type="checkbox" class="form-check-input" id="Whatsapp" name="notification[]" value="whatsapp" {{ in_array('whatsapp', $notifications ?? []) ? 'checked' : '' }}>
                                                                            <label class="form-check-label" for="Whatsapp">Whatsapp</label>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- PAN -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">PAN</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control" name="pan_number" value="{{ $vendor->pan_number ?? '' }}">
                                                                </div>
                                                                <div class="col-md-3 d-flex align-items-center gap-1">
                                                                    <input type="file" class="form-control" name="pan_attachment" onchange="simpleFileValidation(this)">
                                                                    @if(!empty($vendor->pan_attachment))
                                                                        <div class="mt-0">
                                                                            <a href="{{ Storage::url($vendor->pan_attachment) }}" target="_blank" download class="d-block file-link">
                                                                                <i class="fas file-icon"></i>
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <!-- Tin No. -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Tin No.</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control" name="tin_number" value="{{ $vendor->tin_number ?? '' }}">
                                                                </div>
                                                                
                                                                <div class="col-md-3 d-flex align-items-center gap-1">
                                                                    <input type="file" class="form-control" name="tin_attachment" onchange="simpleFileValidation(this)">
                                                                    @if(!empty($vendor->tin_attachment))
                                                                        <div class="mt-0">
                                                                            <a href="{{ Storage::url($vendor->tin_attachment) }}" target="_blank" download class="d-block file-link">
                                                                                <i class="fas file-icon"></i>
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <!-- Aadhar No. -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Aadhar No.</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="text" class="form-control" name="aadhar_number" value="{{ $vendor->aadhar_number ?? '' }}">
                                                                </div>
                                                                <div class="col-md-3 d-flex align-items-center gap-1">
                                                                    <input type="file" class="form-control" name="aadhar_attachment" onchange="simpleFileValidation(this)">
                                                                    @if(!empty($vendor->aadhar_attachment))
                                                                        <div class="mt-0">
                                                                            <a href="{{ Storage::url($vendor->aadhar_attachment) }}" target="_blank" download class="d-block file-link">
                                                                                <i class="fas file-icon"></i>
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1" style="margin-top:24px">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Currency<span class="text-danger">*</span></label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <select class="form-select select2" id="currencySelect" name="currency_id">
                                                                        @foreach($currencies as $currency)
                                                                        <option value="{{ $currency->id }}" data-short-name="{{ $currency->short_name ?? '' }}"
                                                                            {{ (isset($vendor) && $vendor->currency_id == $currency->id) || 
                                                                            (isset($vendor) && !isset($vendor->currency_id) && isset($organization) && $organization->currency_id == $currency->id) ? 'selected' : '' }}>
                                                                            {{ $currency->name }}
                                                                        </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>

                                                                <!-- Org Currency -->
                                                                <div class="col-md-2" id="orgCurrencyRow" style="{{ $vendor->currency_id ? '' : 'display: none;' }} position: relative;">
                                                                    <label class="form-label" style="position: absolute;top:-20px">Org Currency</label>
                                                                    <div class="input-group" style="height: 38px;">
                                                                        <span class="input-group-text bg-light" id="orgCurrencySymbol">Code</span>
                                                                        <input type="text" class="form-control" id="orgCurrency" name="org_currency" value="{{ $customer->org_currency ?? '' }}" readonly>
                                                                    </div>
                                                                </div>

                                                                 <!-- Company Currency -->
                                                                 <div class="col-md-2" id="companyCurrencyRow" style="{{ $vendor->currency_id ? '' : 'display: none;' }} position: relative;">
                                                                    <label class="form-label" style="position: absolute;top:-20px">Company Currency</label>
                                                                    <div class="input-group" style="height: 38px;">
                                                                        <span class="input-group-text bg-light" id="companyCurrencySymbol">Code</span>
                                                                        <input type="text" class="form-control" id="companyCurrency" name="company_currency" value="{{ $customer->company_currency ?? '' }}" readonly>
                                                                    </div>
                                                                </div>

                                                                <!-- Group Currency -->
                                                                <div class="col-md-2" id="groupCurrencyRow" style="{{ $vendor->currency_id ? '' : 'display: none;' }} position: relative;">
                                                                    <label class="form-label" style="position: absolute;top:-20px">Group Currency</label>
                                                                    <div class="input-group" style="height: 38px;">
                                                                        <span class="input-group-text bg-light" id="groupCurrencySymbol">Code</span>
                                                                        <input type="text" class="form-control" id="groupCurrency" name="group_currency" value="{{ $customer->group_currency ?? '' }}" readonly>
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-1">
                                                                    <a href="{{ route('exchange-rates.index') }}" target="_blank" class="voucehrinvocetxt mt-0">Add Exchange Rate</a>
                                                                </div>
                                                                <input type="hidden" id="transactionDate" name="transaction_date">
                                                            </div>
                                                            <!-- Opening Balance -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Opening Balance</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="input-group">
                                                                        <span class="input-group-text bg-light" id="currencyShortName">{{ $vendor->currency->short_name ?? 'INR' }}</span>
                                                                        <input type="text" class="form-control" name="opening_balance" value="{{ $vendor->opening_balance ?? '' }}">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- Payment Terms -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Payment Term<span class="text-danger">*</span></label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <select class="form-select select2" name="payment_terms_id">
                                                                        <option value="">Select</option>
                                                                        @foreach($paymentTerms as $paymentTerm)
                                                                            <option value="{{ $paymentTerm->id }}" {{ $vendor->payment_terms_id == $paymentTerm->id ? 'selected' : '' }}>
                                                                                {{ $paymentTerm->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <!-- Upload Documents -->
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label for="document-upload" class="form-label">Upload Documents</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="file" id="document-upload" class="form-control" name="other_documents[]" multiple onchange="simpleFileValidation(this)">
                                                                    @if(!empty($vendor->other_documents))
                                                                        <div class="row mt-2">
                                                                            @if(is_array($vendor->other_documents))
                                                                                @foreach($vendor->other_documents as $document)
                                                                                    <div class="col-md-1 mb-2">
                                                                                        <a href="{{ Storage::url($document) }}" target="_blank" class="d-block file-link" download>
                                                                                          <i class="fas file-icon"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                @endforeach
                                                                            @else
                                                                                <div class="col-md-1 mb-2">
                                                                                    <a href="{{ Storage::url($vendor->other_documents) }}" target="_blank" class="d-block file-link" download>
                                                                                    <i class="fas file-icon"></i>
                                                                                    </a>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                            </div>
                                                        </div>
                                                         <!-- Vendor Detail End -->
                                                         <div class="tab-pane" id="Shipping">
                                                            <div class="table-responsive">
                                                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>S.NO</th>
                                                                            <th style="width:150px;">Country<span class="text-danger">*</span></th>
                                                                            <th style="width:150px;">State<span class="text-danger">*</span></th>
                                                                            <th style="width:150px;">City<span class="text-danger">*</span></th>
                                                                            <th>Pin Code<span class="text-danger">*</span></th>
                                                                            <th>Address<span class="text-danger">*</span></th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="address-table-body">
                                                                        @foreach(@$vendor->addresses as $index => $vendorAddress)
                                                                        @php
                                                                            $isGstAddress = $vendorAddress->state_id == $vendor->gst_state_id;
                                                                         
                                                                            $gstStateId = $isGstAddress ? ($gstState->id ?? '') : '';
                                                                            $gstStateName = $isGstAddress ? ($gstState->name ?? '') : '';
                                                                            $gstCountryId = $isGstAddress ? ($gstCountry->id ?? '') : '';
                                                                            $gstCountryName = $isGstAddress ? ($gstCountry->name ?? '') : '';
                                                                        @endphp
                                                                            <tr class="address-row" data-id="{{ $vendorAddress->id }}" data-index="{{ $index }}"
                                                                                data-country-id="{{ $vendorAddress->country_id ?? '' }}"
                                                                                data-state-id="{{ $vendorAddress->state_id ?? '' }}"
                                                                                data-city-id="{{ $vendorAddress->city_id ?? '' }}" data-type="{{ $vendorAddress->type ?? '' }}">
                                                                                <input type="hidden" name="addresses[{{ $index }}][id]" value="{{ $vendorAddress->id }}"> 
                                                                                <td class="index">{{ $index + 1 }}</td>
                                                                                <td>
                                                                                    <input type="text" class="form-control mw-100 country-input"  data-gst-country-id="{{ $gstCountryId }}" 
                                                                                    data-gst-country="{{ $gstCountryName }}"   name="addresses[{{ $index }}][country]"
                                                                                        placeholder="Search Country" value="{{ $vendorAddress->country->name ?? '' }}"> 
                                                                                    <input type="hidden" name="addresses[{{ $index }}][country_id]" class="country-id" value="{{ $vendorAddress->country_id ?? '' }}"> 
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" class="form-control mw-100 state-input" data-gst-state-id="{{ $gstStateId }}" 
                                                                                       data-gst-state="{{ $gstStateName }}" name="addresses[{{ $index }}][state]"
                                                                                        placeholder="Search State" value="{{ $vendorAddress->state->name ?? '' }}"> 
                                                                                    <input type="hidden" name="addresses[{{ $index }}][state_id]" class="state-id" value="{{ $vendorAddress->state_id ?? '' }}"> 
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" class="form-control mw-100 city-input" name="addresses[{{ $index }}][city]"
                                                                                        placeholder="Search City" value="{{ $vendorAddress->city->name ?? '' }}"> 
                                                                                    <input type="hidden" name="addresses[{{ $index }}][city_id]" class="city-id" value="{{ $vendorAddress->city_id ?? '' }}"> 
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" class="form-control mw-100 numberonly" name="addresses[{{ $index }}][pincode]"
                                                                                        placeholder="Pincode" value="{{ $vendorAddress->pincode ?? '' }}">
                                                                                    <input type="hidden" name="addresses[{{ $index }}][pincode_master_id]" class="pincode-id"
                                                                                        value="{{ $vendorAddress->pincode_master_id ?? '' }}"> 
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" class="form-control mw-100" name="addresses[{{ $index }}][address]"
                                                                                        value="{{ $vendorAddress->address ?? '' }}"> 
                                                                                </td>
                                                                                <td>
                                                                                    <a href="#" class="text-primary add-address"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                    <a href="#" class="text-danger delete-address"><i data-feather="trash-2" class="me-50"></i></a>
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                        @if($vendor->addresses->isEmpty()) <!-- Changed $customer to $vendor -->
                                                                            <tr class="address-row" data-index="0">
                                                                                <td class="index">1</td>
                                                                                <td>
                                                                                    <input type="text" class="form-control mw-100 country-input" name="addresses[0][country]" placeholder="Search Country">
                                                                                    <input type="hidden" name="addresses[0][country_id]" class="country-id">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" class="form-control mw-100 state-input" name="addresses[0][state]" placeholder="Search State">
                                                                                    <input type="hidden" name="addresses[0][state_id]" class="state-id">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" class="form-control mw-100 city-input" name="addresses[0][city]" placeholder="Search City">
                                                                                    <input type="hidden" name="addresses[0][city_id]" class="city-id">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" class="form-control mw-100 numberonly" name="addresses[0][pincode]" placeholder="Pincode">
                                                                                    <input type="hidden" name="addresses[0][pincode_master_id]" class="pincode-id">
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text" class="form-control mw-100" name="addresses[0][address]">
                                                                                </td>
                                                                                <td>
                                                                                    <a href="#" class="text-primary add-address"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                    <a href="#" class="text-danger delete-address"><i data-feather="trash-2" class="me-50"></i></a>
                                                                                </td>
                                                                            </tr>
                                                                        @endif
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                          <!-- Financial Start -->
                                                        <div class="tab-pane" id="Financial">
                                                           <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Create Ledger?</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="hidden" name="create_ledger" value="0"> 
                                                                        <input type="checkbox" class="form-check-input" id="create_vendor_ledger" name="create_ledger" value="1" {{ isset($vendor) && $vendor->create_ledger == 1 ? 'checked' : '' }}>
                                                                        <label class="form-check-label" for="create_vendor_ledger">Yes</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label for="ledger_name" class="form-label">Ledger</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <input type="text" id="ledger_name" name="ledger_name" class="form-control vendor-ladger-autocomplete"  value="{{ $vendor->ledger->name ?? '' }}" {{ !$isLedgerEditable ? 'readonly' : '' }}>
                                                                    <input type="hidden" id="ledger_id" name="ledger_id" class="ladger-id"  value="{{($vendor->ledger_id ?? '') }}">
                                                                </div>
                                                            </div>
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label for="ledger_group_name" class="form-label">Ledger Group</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <select id="ledger_group_name" name="ledger_group_id" {{ !$isLedgerEditable ? 'disabled' : '' }}  class="form-control ledger-group-select" >
                                                                        @foreach($ledgerGroups as $group)
                                                                            <option value="{{ $group->id }}" 
                                                                                {{ isset($vendor) && $vendor->ledger_group_id == $group->id ? 'selected' : '' }}>
                                                                                {{ $group->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                    <input type="hidden" id="ledger_group_hidden_id" class="ledger-group-id" value="{{($vendor->ledger_group_id ?? '') }}">
                                                                </div>
                                                            </div>
                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2"> 
                                                                    <label for="pricing_type" class="form-label">Pricing Type</label>  
                                                                </div>  
                                                                <div class="col-md-3"> 
                                                                    <select id="pricing_type" name="pricing_type" class="form-select select2">
                                                                        <option value="">Select</option>
                                                                        <option value="fixed" {{ isset($vendor->pricing_type) && $vendor->pricing_type == 'fixed' ? 'selected' : '' }}>Fixed</option>
                                                                        <option value="variable" {{ isset($vendor->pricing_type) && $vendor->pricing_type == 'variable' ? 'selected' : '' }}>Variable</option>
                                                                    </select>
                                                                </div> 
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2"> 
                                                                    <label for="credit_limit" class="form-label">Credit Limit</label>  
                                                                </div>  
                                                                <div class="col-md-3"> 
                                                                    <input type="number" id="credit_limit" name="credit_limit" value="{{ $vendor->credit_limit ?? '' }}" class="form-control" placeholder="Enter credit limit" />
                                                                </div> 
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-2"> 
                                                                    <label for="credit_days" class="form-label">Credit Days</label>  
                                                                </div>  
                                                                <div class="col-md-3"> 
                                                                    <input type="number" id="credit_days" name="credit_days" value="{{ $vendor->credit_days ?? '' }}" class="form-control" placeholder="Enter credit days" min="0" />
                                                                    <input type="hidden" name="credit_days_editable" value="0">
                                                                    <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input" id="credit_days_allowed_checkbox" name="credit_days_editable" value="1" {{ isset($vendor) && $vendor->credit_days_editable == 1 ? 'checked' : '' }}>
                                                                        <label class="form-check-label" for="credit_days_allowed_checkbox">Allowed to Change</label>
                                                                    </div>
                                                                </div> 
                                                            </div>
                            
                                                             <input type="hidden" id="hidden_ledger_vendor_name" name="hidden_ledger_vendor_name" value="">
                                                             <input type="hidden" id="hidden_ledger_vendor_code" name="hidden_ledger_vendor_code" value="">

                                                            <!-- <div class="row align-items-center mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">On Account Required?</label>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input" id="OnAccountRequired" name="on_account_required" {{ $vendor->on_account_required == 1 ? 'checked' : '' }}>
                                                                        <label class="form-check-label" for="OnAccountRequired">Yes/No</label>
                                                                    </div>
                                                                </div>
                                                            </div> -->

                                                        </div>
                                                        <!-- FinancialEnd -->

                                                    <!-- Start Contact -->
                                                    <div class="tab-pane" id="amend">
                                                        <div class="table-responsive">
                                                            <table class="table myrequesttablecbox table-striped" id="contactsTable">
                                                                <thead>
                                                                    <tr>
                                                                        <th>S.No</th>
                                                                        <th>Salutation</th>
                                                                        <th>Name</th>
                                                                        <th>Email</th>
                                                                        <th>Mobile</th>
                                                                        <th>Work Phone</th>
                                                                        <th>Primary</th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @forelse($vendor->contacts as $contact)
                                                                        <tr class="contact-info-row" data-id="{{ $contact->id }}">
                                                                           <input type="hidden" name="contacts[{{$loop->index}}][id]" value="{{ $contact->id }}">
                                                                            <td>{{ $loop->index + 1 }}</td>
                                                                            <td>
                                                                                <select class="form-select px-1" name="contacts[{{ $loop->index }}][salutation]">
                                                                                    <option value="">Select</option>
                                                                                    @foreach($titles as $title)
                                                                                        <option value="{{ $title }}" {{ $contact->salutation == $title ? 'selected' : '' }}>{{ $title }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </td>
                                                                            <td><input type="text" name="contacts[{{ $loop->index }}][name]" class="form-control" value="{{ $contact->name ?? '' }}"></td>
                                                                            <td><input type="email" name="contacts[{{ $loop->index }}][email]" class="form-control" value="{{ $contact->email ?? '' }}"></td>
                                                                            <td><input type="text" name="contacts[{{ $loop->index }}][mobile]" class="form-control numberonly" value="{{ $contact->mobile ?? '' }}"></td>
                                                                            <td><input type="text" name="contacts[{{ $loop->index }}][phone]" class="form-control numberonly" value="{{ $contact->phone ?? '' }}"></td>
                                                                            <td>
                                                                                <input type="radio" name="contacts[{{ $loop->index }}][primary]" value="1" {{ $contact->primary ? 'checked' : '' }} class="primary-radio">
                                                                            </td>
                                                                            <td>
                                                                                <a href="#" class="text-primary add-contact-row"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                <a href="#" class="text-danger delete-contact-row"><i data-feather="trash-2"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr class="contact-info-row" data-id="">
                                                                            <td>1</td>
                                                                            <td>
                                                                                <select class="form-select" name="contacts[0][salutation]">
                                                                                    <option value="">Select</option>
                                                                                    @foreach($titles as $title)
                                                                                        <option value="{{ $title }}">{{ $title }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </td>
                                                                            <td><input type="text" name="contacts[0][name]" class="form-control" value=""></td>
                                                                            <td><input type="email" name="contacts[0][email]" class="form-control" value=""></td>
                                                                            <td><input type="text" name="contacts[0][mobile]" class="form-control numberonly" value=""></td>
                                                                            <td><input type="text" name="contacts[0][phone]" class="form-control numberonly" value=""></td>
                                                                            <td>
                                                                                <input type="radio" name="contacts[0][primary]" value="0" class="primary-radio">
                                                                            </td>
                                                                            <td>
                                                                                <a href="#" class="text-primary add-contact-row"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                <a href="#" class="text-danger delete-contact-row"><i data-feather="trash-2"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                    @endforelse
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                    <!-- End Contact -->
                                                    <div class="tab-pane" id="schedule">
                                                        <div class="row">
                                                            <!-- TDS Details -->
                                                            <div class="col-md-6">
                                                                <h5 class="mt-1 mb-2 text-dark"><strong>TDS Details</strong></h5>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">TDS Applicable</label>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                            <input type="checkbox" name="compliance[tds_applicable]" id="tdsApplicableIndia" 
                                                                                class="form-check-input" 
                                                                                @if($vendor->compliances && $vendor->compliances->tds_applicable) checked @endif
                                                                            <label class="form-check-label" for="tdsApplicableIndia">Yes/No</label>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Wef Date</label>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="date" name="compliance[wef_date]" class="form-control" 
                                                                            value="{{ $vendor->compliances->wef_date ?? '' }}">
                                                                    </div>
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">TDS Certificate No.</label>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="text" name="compliance[tds_certificate_no]" class="form-control numberonly" 
                                                                            value="{{ $vendor->compliances->tds_certificate_no ?? '' }}">
                                                                    </div>
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">TDS Tax Percentage</label>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="text" name="compliance[tds_tax_percentage]" class="form-control" 
                                                                            value="{{ $vendor->compliances->tds_tax_percentage ?? '' }}">
                                                                    </div>
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">TDS Category</label>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="text" name="compliance[tds_category]" class="form-control" 
                                                                            value="{{ $vendor->compliances->tds_category ?? '' }}">
                                                                    </div>
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">TDS Value Cap</label>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="text" name="compliance[tds_value_cab]" class="form-control numberonly" 
                                                                            value="{{ $vendor->compliances->tds_value_cab ?? '' }}">
                                                                    </div>
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">TAN Number</label>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="text" name="compliance[tan_number]" class="form-control" 
                                                                            value="{{ $vendor->compliances->tan_number ?? '' }}">
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <!-- GST Info -->
                                                            <div class="col-md-6">
                                                                <h5 class="mt-1 mb-2 text-dark"><strong>GST Info</strong></h5>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">GST Applicable</label>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="demo-inline-spacing">
                                                                            <div class="form-check form-check-primary mt-25">
                                                                                <input type="radio" id="gstRegisteredIndia" name="compliance[gst_applicable]" value="1" 
                                                                                    class="form-check-input" 
                                                                                    @if($vendor->compliances && $vendor->compliances->gst_applicable == 1) checked @endif>
                                                                                <label class="form-check-label fw-bolder" for="gstRegisteredIndia">Registered</label>
                                                                            </div>
                                                                            <div class="form-check form-check-primary mt-25">
                                                                                <input type="radio" id="gstNonRegisteredIndia" name="compliance[gst_applicable]" value="0" 
                                                                                    class="form-check-input" 
                                                                                    @if($vendor->compliances && $vendor->compliances->gst_applicable == 0) checked @endif>
                                                                                <label class="form-check-label fw-bolder" for="gstNonRegisteredIndia">Non-Registered</label>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">GSTIN No.</label>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="text" name="compliance[gstin_no]" id="gstinNo" class="form-control" 
                                                                            value="{{ $vendor->compliances->gstin_no ?? '' }}">
                                                                    </div>
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Legal Name</label>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="text" name="compliance[gst_registered_name]" class="form-control" 
                                                                            value="{{ $vendor->compliances->gst_registered_name ?? '' }}">
                                                                    </div>
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">GSTIN Reg. Date</label>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="date" name="compliance[gstin_registration_date]" class="form-control" 
                                                                            value="{{ $vendor->compliances->gstin_registration_date ?? '' }}">
                                                                    </div>
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Upload Certificate</label>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="file" name="compliance[gst_certificate][]" multiple class="form-control" onchange="simpleFileValidation(this)">
                                                                        @if(!empty($vendor->compliances) && $vendor->compliances->gst_certificate)
                                                                            <div class="row mt-2">
                                                                                @if(is_array($vendor->compliances->gst_certificate))
                                                                                    <!-- Handle multiple files -->
                                                                                    @foreach($vendor->compliances->gst_certificate as $document)
                                                                                        <div class="col-md-1 mb-2">
                                                                                            <a href="{{ Storage::url($document) }}" target="_blank" rel="noopener noreferrer" class="d-block file-link" download>
                                                                                               <i class="fas file-icon"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                    @endforeach
                                                                                @else
                                                                                    <!-- Handle single file -->
                                                                                    <div class="col-md-1 mb-2">
                                                                                        <a href="{{ Storage::url($vendor->compliances->gst_certificate) }}" target="_blank" rel="noopener noreferrer" class="d-block file-link" download>
                                                                                           <i class="fas file-icon"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        @endif
                                                                    </div>

                                                                </div>

                                                            </div>

                                                            <!-- MSME Details -->
                                                            <div class="col-md-6">
                                                                <h5 class="mt-1 mb-2 text-dark"><strong>MSME Details</strong></h5>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">MSME Registered?</label>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                            <input type="checkbox" class="form-check-input" name="compliance[msme_registered]" id="msmeRegisteredIndia" 
                                                                                @if($vendor->compliances && $vendor->compliances->msme_registered) checked @endif>
                                                                            <label class="form-check-label" for="msmeRegisteredIndia">This vendor is MSME registered</label>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">MSME No.</label>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="text" name="compliance[msme_no]" class="form-control numberonly" 
                                                                            value="{{ $vendor->compliances->msme_no ?? '' }}">
                                                                    </div>
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">MSME Type</label>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <select class="form-select" name="compliance[msme_type]">
                                                                            <option value="">Select</option>
                                                                            <option value="Micro" @if($vendor->compliances && $vendor->compliances->msme_type == 'Micro') selected @endif>Micro</option>
                                                                            <option value="Small" @if($vendor->compliances && $vendor->compliances->msme_type == 'Small') selected @endif>Small</option>
                                                                            <option value="Medium" @if($vendor->compliances && $vendor->compliances->msme_type == 'Medium') selected @endif>Medium</option>
                                                                        </select>
                                                                    </div>
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-4">
                                                                        <label class="form-label">Upload Certificate</label>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <input type="file" name="compliance[msme_certificate][]" multiple class="form-control" onchange="simpleFileValidation(this)">
                                                                        @if(!empty($vendor->compliances) && $vendor->compliances->msme_certificate)
                                                                            <div class="row mt-2">
                                                                                @if(is_array($vendor->compliances->msme_certificate))
                                                                                    <!-- Handle multiple files -->
                                                                                    @foreach($vendor->compliances->msme_certificate as $document)
                                                                                        <div class="col-md-1 mb-2">
                                                                                            <a href="{{ Storage::url($document) }}" target="_blank" rel="noopener noreferrer" class="d-block file-link" download>
                                                                                              <i class="fas file-icon"></i>
                                                                                            </a>
                                                                                        </div>
                                                                                    @endforeach
                                                                                @else
                                                                                    <!-- Handle single file -->
                                                                                    <div class="col-md-1 mb-2">
                                                                                        <a href="{{ Storage::url($vendor->compliances->msme_certificate) }}" target="_blank" rel="noopener noreferrer" class="d-block file-link" download>
                                                                                           <i class="fas file-icon"></i>
                                                                                        </a>
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                       <!-- Bank Info Tab -->
                                                       <div class="tab-pane" id="send">
                                                            <div class="table-responsive-md">
                                                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>S.No</th>
                                                                            <th>Bank Name</th>
                                                                            <th>Beneficiary Name</th>
                                                                            <th>Account Number</th>
                                                                            <th>Re-enter Account No.</th>
                                                                            <th>IFSC Code</th>
                                                                            <th>Primary</th>
                                                                            <th>Cancel Cheque</th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="bank-info-container">
                                                                        @forelse($vendor->bankInfos as $index => $bankInfo)
                                                                            <tr data-id="{{ $bankInfo->id }}" class="bank-info-row" data-index="{{ $index }}">
                                                                            <input type="hidden" name="bank_info[{{ $index }}][id]" value="{{ $bankInfo->id }}">
                                                                               <td>{{ $loop->index + 1 }}</td>
                                                                                <td><input type="text" class="form-control mw-100 bank-name" name="bank_info[{{ $index }}][bank_name]" value="{{ $bankInfo->bank_name ??'' }}" /></td>
                                                                                <td><input type="text" class="form-control mw-100" name="bank_info[{{ $index }}][beneficiary_name]" value="{{ $bankInfo->beneficiary_name ??'' }}" /></td>
                                                                                <td><input type="text" class="form-control mw-100" name="bank_info[{{ $index }}][account_number]" value="{{ $bankInfo->account_number ??'' }}" /></td>
                                                                                <td><input type="text" class="form-control mw-100" name="bank_info[{{ $index }}][re_enter_account_number]" value="{{ $bankInfo->re_enter_account_number ??'' }}" /></td>
                                                                                <td><input type="text" class="form-control mw-100 ifsc-code" name="bank_info[{{ $index }}][ifsc_code]" value="{{ $bankInfo->ifsc_code ??'' }}" /></td>
                                                                                <td>
                                                                                    <input type="radio" name="bank_info[{{ $index }}][primary]" value="{{$bankInfo->primary}}" {{ $bankInfo->primary ? 'checked' : '' }} class="primary-radio">
                                                                                </td>

                                                                                <td>
                                                                                    <div><input type="file" class="form-control mw-100" name="bank_info[{{ $index }}][cancel_cheque][]" multiple onchange="simpleFileValidation(this)" /></div>
                                                                                    @if(!empty($bankInfo->cancel_cheque))
                                                                                      <input type="hidden" name="bank_info[{{ $index }}][existing_cancel_cheque]" value="{{ $bankInfo->cancel_cheque }}">
                                                                                        <div class="mt-2">
                                                                                                <a href="{{ Storage::url($bankInfo->cancel_cheque) }}" target="_blank" rel="noopener noreferrer" class="file-link" download>
                                                                                                    <i class="fas file-icon"></i>
                                                                                                </a>
                                                                                                <br />
                                                                                        </div>
                                                                                    @endif
                                                                                </td>

                                                                                <td>
                                                                                    <a href="#" class="text-primary add-bank-row"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                    <a href="#" class="text-danger delete-bank-row"><i data-feather="trash-2" class="me-50"></i></a>
                                                                                </td>
                                                                            </tr>
                                                                        @empty
                                                                            <tr class="bank-info-row" data-index="0">
                                                                                <td>1</td>
                                                                                <td><input type="text" class="form-control mw-100 bank-name" name="bank_info[0][bank_name]" /></td>
                                                                                <td><input type="text" class="form-control mw-100" name="bank_info[0][beneficiary_name]" /></td>
                                                                                <td><input type="text" class="form-control mw-100" name="bank_info[0][account_number]" /></td>
                                                                                <td><input type="text" class="form-control mw-100" name="bank_info[0][re_enter_account_number]" /></td>
                                                                                <td><input type="text" class="form-control mw-100 ifsc-code" name="bank_info[0][ifsc_code]" /></td>
                                                                                <td>
                                                                                    <input type="radio"  name="bank_info[0][primary]" value="0" class="primary-radio">
                                                                                </td>
                                                                                <td><div><input type="file" class="form-control mw-100" name="bank_info[0][cancel_cheque][]" multiple onchange="simpleFileValidation(this)" /></div></td>
                                                                                <td>
                                                                                    <a href="#" class="text-primary add-bank-row"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                    <a href="#" class="text-danger delete-bank-row"><i data-feather="trash-2" class="me-50"></i></a>
                                                                                </td>
                                                                            </tr>
                                                                        @endforelse
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>

                                                       <div class="tab-pane" id="latestrates">
                                                            <label class="form-label">Notes (For Internal Use)</label>  
                                                            <textarea class="form-control" name="notes[remark]" placeholder="Enter Notes...."></textarea>
                                                            <div class="table-responsive mt-1">
                                                                <table class="table myrequesttablecbox table-striped"> 
                                                                    <thead>
                                                                        <tr> 
                                                                            <th class="px-1">S.No</th>
                                                                            <th class="px-1">Name</th> 
                                                                            <th class="px-1">Date</th>
                                                                            <th class="px-1">Remarks</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($vendor->notes as $index => $note)
                                                                        <input type="hidden" name="notes[{{ $index }}][id]" value="{{ $note->id }}">
                                                                            <tr valign="top">
                                                                                <td>{{ $index + 1 }}</td>
                                                                                    <td class="px-1">
                                                                                    @if($note->created_by_type == 'employee')
                                                                                        <span>{{ isset($note->createdByEmployee->name) ? $note->createdByEmployee->name : 'N/A' }}</span>
                                                                                    @elseif($note->created_by_type == 'user')
                                                                                        <span>{{ isset($note->createdByUser ->name) ? $note->createdByUser->name : 'N/A' }}</span>
                                                                                    @else
                                                                                        <span>N/A</span>
                                                                                    @endif
                                                                                </td>
                                                                                <td class="px-1">{{ $note->created_at->format('d-m-Y') }}</td>
                                                                                <td class="px-1">{{ $note->remark }}</td> 
                                                                            </tr> 
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>

                                                        <!-- Items start -->
                                                        <div class="tab-pane" id="Items">
                                                            <div class="table-responsive-md">
                                                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border" id="vendorTable">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>S.NO</th>
                                                                            <th width="300px">Item</th>
                                                                            <th>Vendor Item Code</th>
                                                                            <th id="cost-price-header">Cost Price</th>
                                                                            <th>Purchase Uom</th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="vendorTableBody">
                                                                        @forelse ($vendor->approvedItems as $index => $item)
                                                                            <tr data-id="{{ $item->id }}" id="row-{{ $index }}">
                                                                               <input type="hidden" name="vendor_item[{{ $index }}][id]" value="{{ $item->id }}">
                                                                                <td>{{ $index + 1 }}</td>
                                                                                <td>
                                                                                    <input type="text" name="vendor_item[{{ $index }}][item_name]" class="form-control mw-100 vendor-autocomplete" data-id="{{ $index }}" value="{{$item->item->item_name ??''}}" placeholder="Search Item" autocomplete="off">
                                                                                    <input type="hidden" id="item-id_{{ $index }}" name="vendor_item[{{ $index }}][item_id]" class="item-id" value="{{ $item->item_id ?? '' }}">
                                                                                </td>
                                                                                <td><input type="text" name="vendor_item[{{ $index }}][item_code]" class="form-control mw-100" value="{{ $item->item_code ??''}}" id="item-code_0" readonly></td>
                                                                                <td><input type="text" name="vendor_item[{{ $index }}][cost_price]"  class="form-control cost-price-approved-vendor mw-100"  id="cost-price_{{ $index }}" value="{{ number_format($item->cost_price, 2) }}"></td>
                                                                                <td>
                                                                                    <select name="vendor_item[{{ $index }}][uom_id]" id="uom_{{ $index }}" class="form-select mw-100">
                                                                                        <option value="">Select</option>
                                                                                        <input type="hidden" id="uom-id_{{ $index }}" value="{{ $item->uom_id }}">
                                                                                    </select>
                                                                                </td>
                                                                                <td>
                                                                                    <a href="#" class="text-primary add-item"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                    <a href="#" class="text-danger delete-item"><i data-feather="trash-2" class="me-50"></i></a>
                                                                                </td>
                                                                            </tr>
                                                                        @empty
                                                                            <tr id="row-0">
                                                                                <td>1</td>
                                                                                <td>
                                                                                    <input type="text" name="vendor_item[0][item_name]" class="form-control mw-100 vendor-autocomplete" data-id="0" placeholder="Search Vendor" autocomplete="off">
                                                                                    <input type="hidden" id="item-id_0" name="vendor_item[0][item_id]" class="item-id">
                                                                                </td>
                                                                                <td><input type="text" name="vendor_item[0][item_code]"  class="form-control mw-100" id="item-code_0" readonly></td>
                                                                                <td><input type="text" name="vendor_item[0][cost_price]" id="cost-price_0" class="form-control cost-price-approved-vendor mw-100"></td>
                                                                                <td><select name="vendor_item[0][uom_id]"  id="uom_0" class="form-select mw-100" disabled></select></td>
                                                                                <td>
                                                                                    <a href="#" class="text-primary add-item"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                    <a href="#" class="text-danger delete-item"><i data-feather="trash-2" class="me-50"></i></a>
                                                                                </td>
                                                                            </tr>
                                                                        @endforelse
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>

                                                        {{-- Supplier Portal --}}
                                                    <div class="tab-pane" id="supplierPortal">
                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-2"> 
                                                                <label for="user" class="form-label">Portal Users</label>  
                                                            </div>  
                                                            <div class="col-md-5"> 
                                                                @php
                                                                 $selectedUsers = $vendor->supplier_users()->pluck('user_id')->toArray();
                                                                 $selectedBooks = $vendor->supplier_books()->pluck('book_id')->toArray();
                                                                @endphp
                                                                <select name="user_id[]" multiple class="form-select select2">
                                                                    @foreach($supplierUsers as $supplierUser)
                                                                        <option value="{{$supplierUser->id}}" {{ in_array($supplierUser->id, $selectedUsers) ? 'selected' : '' }}>{{$supplierUser->name}}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div> 
                                                        </div>

                                                         <div class="row align-items-center mb-1">
                                                             <div class="col-md-2"> 
                                                                 <label for="book" class="form-label">ASN Series</label>  
                                                             </div>  
                                                             <div class="col-md-5"> 
                                                                 <select name="book_id[]" multiple class="form-select select2">
                                                                     @foreach($books as $book)
                                                                        <option value="{{$book->id}}" {{ in_array($book->id, $selectedBooks) ? 'selected' : '' }}>{{$book->book_code}}</option>
                                                                     @endforeach
                                                                 </select>
                                                             </div> 
                                                         </div>

                                                         <div class="row align-items-center mb-1">
                                                            <div class="col-md-2"> 
                                                                <label for="user" class="form-label">Stores</label>  
                                                            </div>  
                                                            <div class="row align-items-center mb-1">
                                                         <div class="table-responsive"> 
                                                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border"> 
                                                                    <thead>
                                                                        <tr>
                                                                            <th>S.NO</th>
                                                                            <th>Organization<span class="text-danger">*</span></th>
                                                                            <th>Location<span class="text-danger">*</span></th>
                                                                            <th>Store<span class="text-danger">*</span></th>
                                                                            <th>Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="vendor-stores-table-body">
                                                                        @forelse ($vendorStores as $vendorIndex => $vendorStore)
                                                                        <tr class="stores-row" data-index="0">
                                                                            <td class="index">{{$vendorIndex + 1}}</td>
                                                                            <td>
                                                                                <input type="text" class="form-control mw-100 vendor-store-org-input" name="vendor_store[{{$vendorIndex}}][organization]" placeholder="Search Organization" value = "{{$vendorStore -> organization ?-> name}}">
                                                                                <input type="hidden" name="vendor_store[{{$vendorIndex}}][organization_id]" class="vendor-store-org-id" value = "{{$vendorStore -> organization_id}}">
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" class="form-control mw-100 vendor-store-location-input" name="vendor_store[{{$vendorIndex}}][location]" placeholder="Search Location" value = "{{$vendorStore -> store ?-> store_name}}">
                                                                                <input type="hidden" name="vendor_store[{{$vendorIndex}}][location_id]" class="vendor-store-location-id" value = "{{$vendorStore -> location_id}}">
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" class="form-control mw-100 vendor-store-store-input" name="vendor_store[{{$vendorIndex}}][store]" placeholder="Search Store" value = "{{$vendorStore -> sub_store ?-> name}}">
                                                                                <input type="hidden" name="vendor_store[{{$vendorIndex}}][store_id]" class="vendor-location-store-id" value = "{{$vendorStore -> store_id}}">
                                                                            </td>
                                                                            <td>
                                                                                <a href="#" class="text-primary add-vendor-store"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                <a href="#" class="text-danger delete-vendor-store"><i data-feather="trash-2" class="me-50"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                        @empty
                                                                        <tr class="stores-row" data-index="0">
                                                                            <td class="index">1</td>
                                                                            <td>
                                                                                <input type="text" class="form-control mw-100 vendor-store-org-input" name="vendor_store[0][organization]" placeholder="Search Organization">
                                                                                <input type="hidden" name="vendor_store[0][organization_id]" class="vendor-store-org-id">
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" class="form-control mw-100 vendor-store-location-input" name="vendor_store[0][location]" placeholder="Search Location">
                                                                                <input type="hidden" name="vendor_store[0][location_id]" class="vendor-store-location-id">
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" class="form-control mw-100 vendor-store-store-input" name="vendor_store[0][store]" placeholder="Search Store">
                                                                                <input type="hidden" name="vendor_store[0][store_id]" class="vendor-location-store-id">
                                                                            </td>
                                                                            <td>
                                                                                <a href="#" class="text-primary add-vendor-store"><i data-feather="plus-square" class="me-50"></i></a>
                                                                                <a href="#" class="text-danger delete-vendor-store"><i data-feather="trash-2" class="me-50"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                        @endforelse
                                                                        
                                                                    </tbody>
                                                                </table>
                                                            </div> 
                                                        </div>
                                                        </div>
                                                    </div>
                                                         <!-- Items End -->
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
    <div class="modal fade" id="amendConfirmPopup" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Amend
                        {{request() -> type === 'vendor' ? 'Vendor' : 'Vendor'}}
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
                    <button type="button" class="btn btn-outline-secondary me-1" onclick="closeModal('amendConfirmPopup')">Cancel</button> 
                    <button type="button" class="btn btn-primary" onclick = "submitAmend();">Submit</button>
                </div>
            </div>
        </div>
    </div>
 </form>
 <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form class="ajax-submit-2" method="POST" action="{{ route('document.approval.vendor') }}" data-redirect="{{ route('vendor.index') }}" enctype='multipart/form-data'>
          @csrf
          <input type="hidden" class = "cannot_disable" name="action_type" id="action_type">
          <input type="hidden" class = "cannot_disable" name="id" value="{{isset($vendor) ? $vendor -> id : ''}}">
          <input type="hidden" class = "cannot_disable" name="status" id="hidden_status" value="">
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
                            <input type="file" name = "attachment[]" multiple class="form-control cannot_disable" onchange = "addFiles(this, 'approval_files_preview');" max_file_count = "2"/>
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
            <button type="reset" class="btn btn-outline-secondary me-1" onclick="closeModal('approveModal')">Cancel</button> 
            <button type="submit" class="btn btn-primary">Submit</button>
         </div>
       </form>
      </div>
   </div>
</div>
<div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
  <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header p-0 bg-transparent">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body alertmsg text-center warning">
              <i data-feather='alert-circle'></i>
              <h2>Are you sure?</h2>
              <p>Are you sure you want to <strong>Amend</strong> this <strong>{{request() -> type == "vendor" ? "Vendor" : "Vendor"}}</strong>?</p>
              <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
              <button type="button" data-bs-dismiss="modal" onclick = "amendConfirm();" class="btn btn-primary">Confirm</button>
          </div> 
      </div>
  </div>
</div>
 <!-- END: Content-->
@endsection
@section('scripts')
<!-- for item -->
<script>
    $(document).ready(function() {
    var selectedItemIds = @json($vendor->approvedItems->pluck('item_id')->toArray());
    function initializeItemAutocomplete(selector) {
    $(selector).autocomplete({
        source: function(request, response) {
            $.ajax({
                url: "{{ url('/items/search') }}",
                method: 'GET',
                dataType: 'json',
                data: {
                    term: request.term
                },
                success: function(data) {
                    var filteredData = data.filter(function(item) {
                        return !selectedItemIds.includes(item.id); 
                    });
                    response($.map(filteredData, function(item) {
                        return {
                            id: item.id,
                            label: item.label,
                            value: item.value,
                            code: item.code || '', 
                            item_id: item.id
                        };
                    }));
                },
                error: function(xhr) {
                    console.error('Error fetching item data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        select: function(event, ui) {
            $(this).val(ui.item.label); 
            var rowId = $(this).data('id');
            $('#item-id_' + rowId).val(ui.item.id);
            $('#item-code_' + rowId).val(ui.item.code);

            if (!selectedItemIds.includes(ui.item.id)) {
                selectedItemIds.push(ui.item.id);
            }

            fetchUOMs(ui.item.id, rowId);
            return false;
        },
        change: function(event, ui) {
            var rowId = $(this).data('id');
            var currentItemId = $('#item-id_' + rowId).val();
            if (!ui.item) {
                $(this).val("");
                $('#item-id_' + rowId).val('');
                $('#item-code_' + rowId).val('');
                if (currentItemId && selectedItemIds.includes(parseInt(currentItemId))) {
                    selectedItemIds.splice(selectedItemIds.indexOf(parseInt(currentItemId)), 1);
                }
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
        }
    });
   }
    function fetchUOMs(itemId, rowId) {
        $.ajax({
            url: "{{ url('/vendors/get-uoms') }}", 
            method: 'POST',
            data: { item_id: itemId },
            success: function(data) {
                var uomSelect = $('#uom_' + rowId);
                uomSelect.empty();
                uomSelect.append('<option value="">Select</option>');
                data.alternate_uoms.forEach(function(uom) {
                    uomSelect.append('<option value="' + uom.id + '">' + uom.name + '</option>');
                });
                var selectedUomId = $('#uom-id_' + rowId).val();
                if (selectedUomId) {
                    uomSelect.val(selectedUomId); 
                }
                uomSelect.prop('disabled', false); 
            },
            error: function(xhr) {
                console.error('Error fetching UOM data:', xhr.responseText);
            }
        });
    }

    $('#vendorTable tr').each(function() {
            var rowId = $(this).attr('id');
            if (rowId) {
                var rowIndex = rowId.split('-')[1];  
                var itemId = $('#item-id_' + rowIndex).val();
                if (itemId) {
                    fetchUOMs(itemId, rowIndex);
                }
            }
        });

        $('#vendorTable').on('input', '.cost-price-approved-vendor', function () {
            var rowId = $(this).closest('tr').attr('id').split('-')[1]; 
            var costPrice = $('#cost-price_' + rowId).val(); 
            if (costPrice && !isNaN(costPrice)) {
                $('#uom_' + rowId).prop('disabled', false);
            } else {
                $('#uom_' + rowId).prop('disabled', true);
            }
        });

        function updateRowIndices() {
            var $rows = $('#vendorTable tbody tr');
            $('#vendorTable tbody tr').each(function(index) {
                var $row = $(this);
                $row.find('td:first').text(index + 1);
                $row.find('input').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                    }
                    var id = $(this).attr('id');
                    if (id) {
                        $(this).attr('id', id.replace(/\d+$/, index));
                    }
                    var dataId = $(this).data('id');
                    if (dataId !== undefined) {
                        $(this).data('id', index);
                    }
                });
                $row.attr('id', 'row-' + index); 
                if ($rows.length === 1) {
                    $(this).find('.delete-item').hide(); 
                    $(this).find('.add-item').show(); 
                } else {
                    $(this).find('.delete-item').show(); 
                    $(this).find('.add-item').toggle(index === 0); 
                }
            });
            
            initializeItemAutocomplete(".vendor-autocomplete");
        }

        $('#vendorTable').on('click', '.add-item', function(e) {
            e.preventDefault();
            
            var newRow = $('#vendorTable tbody tr:first').clone();
            var rowCount = $('#vendorTable tbody tr').length;

            newRow.find('td:first').text(rowCount + 1);
            newRow.attr('id', 'row-' + rowCount);

            newRow.find('input').each(function() {
                $(this).val('');
                var id = $(this).attr('id');
                if (id) {
                    $(this).attr('id', id.replace(/\d+$/, rowCount)); 
                }
                var dataId = $(this).data('id');
                if (dataId !== undefined) {
                    $(this).data('id', rowCount); 
                }
            });
            newRow.find('select').each(function() {
                var selectId = $(this).attr('id');
                if (selectId) {
                    $(this).attr('id', selectId.replace(/\d+$/, rowCount)); 
                }
                $(this).prop('disabled', true);
            });
            
            $('#vendorTable tbody').append(newRow);
            updateRowIndices();
            feather.replace(); 
        });
        $('#vendorTable').on('click', '.delete-item', function(e) {
            e.preventDefault();
            var $row = $(this).closest('tr');
            var itemId = $row.data('id');
            var itemRowId = $(this).closest('tr').find('input[data-id]').data('id');
            var itemIdToRemove = $('#item-id_' + itemRowId).val();
            if (itemIdToRemove && selectedItemIds.includes(parseInt(itemIdToRemove))) {
                selectedItemIds.splice(selectedItemIds.indexOf(parseInt(itemIdToRemove)), 1);
            }

            if (itemId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to delete this record?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, keep it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/vendors/vendor-items/' + itemId, 
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                            },
                            success: function(response) {
                                if (response.status) {
                                    $row.remove(); 
                                    Swal.fire('Deleted!', response.message, 'success');
                                    location.reload();
                                    updateRowIndices(); 
                                } else {
                                    Swal.fire('Error!', response.message || 'Could not delete the record.', 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON.message || 'An error occurred while deleting the record.', 'error');
                            }
                        });
                    }
                });
            } else {
                $row.remove(); 
                updateRowIndices();
            }
        });
        initializeItemAutocomplete(".vendor-autocomplete");
        updateRowIndices();
    });
</script>
 <!-- end item -->
 <script>
  $(document).ready(function() {
    function applyCapsLock() {
        $('input[type="text"], input[type="number"]').each(function() {
            $(this).val($(this).val().toUpperCase());
        });
        $('input[type="text"], input[type="number"]').on('input', function() {
            var value = $(this).val().toUpperCase();  
            $(this).val(value); 
        });
    }
    var titles = @json($titles);
    var $contactsTableBody = $('#contactsTable tbody');
    function updateDropdown($select) {
        var options = '<option value="">Select</option>' + titles.map(function(title) {
            return '<option>' + title + '</option>';
        }).join('');
        $select.html(options);
    }
    function updateIcons() {
        var rows = $contactsTableBody.find('tr');
        var $rows = $('#contactsTable tbody tr');
        rows.each(function(index) {
            var $row = $(this);
            $row.find('td:first').text(index + 1); 
            $row.find('[name]').each(function() {
                var name = $(this).attr('name');
                $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']')); 
            });
            if ($rows.length === 1) {
                $(this).find('.delete-contact-row').hide(); 
                $(this).find('.add-contact-row').show(); 
            } else {
                $(this).find('.delete-contact-row').show(); 
                $(this).find('.add-contact-row').toggle(index === 0); 
            }  
        });
    }
    function addContactRow() {
        var rowCount = $contactsTableBody.children().length;
        var $currentRow = $contactsTableBody.find('tr:last');
        var $newRow = $currentRow.clone();
        $newRow.find('[name]').each(function() {
            var name = $(this).attr('name');
            $(this).attr('name', name.replace(/\[\d+\]/, '[' + rowCount + ']')); 
            $(this).val('');
        });
        $newRow.attr('data-id', '');
        $newRow.find('input[type=radio]').prop('checked', false).val('0');
        updateDropdown($newRow.find('.form-select'));
        $contactsTableBody.append($newRow);
        feather.replace();
        updateIcons();
        applyCapsLock();
    }
    $(document).on('click', '.add-contact-row', function(e) {
        e.preventDefault();
        addContactRow();
    });
    $contactsTableBody.on('click', '.delete-contact-row', function(e) {
        e.preventDefault();
        var $row = $(this).closest('tr');
        var contactId = $row.data('id');
        if (contactId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Are you sure you want to delete this record?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/vendors/contacts/' + contactId,
                        type: 'DELETE',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                        },
                        success: function(response) {
                            if (response.status) {
                                $row.remove(); 
                                Swal.fire('Deleted!', response.message, 'success');
                                updateIcons();
                            } else {
                                Swal.fire('Error!', response.message || 'Could not delete the contact.', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON.message || 'An error occurred while deleting the contact.', 'error');
                        }
                    });
                }
            });
        } else {
            $row.remove();
            updateIcons();
        }
    });
    $contactsTableBody.on('change', 'input[type=radio]', function() {
        var $radioButtons = $contactsTableBody.find('input[type=radio]');
        $radioButtons.prop('checked', false).val('0');
        $(this).prop('checked', true).val('1');
    });
    if ($contactsTableBody.children().length === 0) {
        addContactRow();
    } else {
        updateIcons(); 
    }
    updateIcons();
  });
</script>

<script>
    $(document).ready(function() {
        function applyCapsLock() {
            $('input[type="text"], input[type="number"]').each(function() {
                $(this).val($(this).val().toUpperCase());
            });
            $('input[type="text"], input[type="number"]').on('input', function() {
                var value = $(this).val().toUpperCase();  
                $(this).val(value); 
            });
        }

        const countries = @json($countries); 
        const addressTypes = @json($addressTypes); 
        
        function initializeAutocomplete($row) {
            // Country Autocomplete
            $row.find('.country-input').autocomplete({
                source: function(request, response) {
                    $.get('/countries', { term: request.term }, function(data) {
                        response(data.data.countries.map(country => ({
                            label: country.label, 
                            value: country.value,
                            id: country.value
                        })));
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    $(this).val(ui.item.label);
                    $(this).closest('tr').find('.country-id').val(ui.item.id);  
                    const $stateInput = $(this).closest('tr').find('.state-input');
                    $stateInput.val('').removeAttr('data-state-id');
                    const $cityInput = $(this).closest('tr').find('.city-input');
                    $cityInput.val('').removeAttr('data-city-id');
                    const $pincodeInput = $(this).closest('tr').find('input[name*="[pincode]"]');
                    $pincodeInput.val('');
                    const $pincodeIdInput = $(this).closest('tr').find('input[name*="[pincode_master_id]"]');
                    $pincodeIdInput.val('');
                    return false;
                }
            }).focus(function() {
                $(this).autocomplete("search", "");
            });

            // State Autocomplete
            $row.find('.state-input').autocomplete({
                source: function(request, response) {
                    const countryId = $(this.element).closest('tr').find('.country-id').val();
                    if (!countryId) {
                        response([]);
                        return;
                    }
                    $.get(`/states/${countryId}`, { term: request.term }, function(data) {
                        response(data.data.states.map(state => ({
                            label: state.label,
                            value: state.value,
                            id: state.value
                        })));
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    $(this).val(ui.item.label);
                    $(this).closest('tr').find('.state-id').val(ui.item.id);  
                    const $cityInput = $(this).closest('tr').find('.city-input');
                    $cityInput.val('').removeAttr('data-city-id');
                    const $pincodeInput = $(this).closest('tr').find('input[name*="[pincode]"]');
                    $pincodeInput.val('');
                    const $pincodeIdInput = $(this).closest('tr').find('input[name*="[pincode_master_id]"]');
                    $pincodeIdInput.val('');
                    return false;
                }
            }).focus(function() {
                $(this).autocomplete("search", "");
            });

            // City Autocomplete
            $row.find('.city-input').autocomplete({
                source: function(request, response) {
                    const stateId = $(this.element).closest('tr').find('.state-id').val();
                    if (!stateId) {
                        response([]);
                        return;
                    }
                    $.get(`/cities/${stateId}`, { term: request.term }, function(data) {
                        response(data.data.cities.map(city => ({
                            label: city.label,
                            value: city.value,
                            id: city.value
                        })));
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    $(this).val(ui.item.label);
                    $(this).closest('tr').find('.city-id').val(ui.item.id); 
                    return false;
                }
            }).focus(function() {
                $(this).autocomplete("search", "");
            });

            // Pincode Autocomplete
            $row.find('input[name*="[pincode]"]').autocomplete({
                source: function(request, response) {
                    const stateId = $(this.element).closest('tr').find('.state-id').val();
                    if (!stateId) {
                        response([]);
                        return;
                    }
                    $.get(`/pincodes/${stateId}`, { term: request.term }, function(data) {
                        response(data.data.pincodes.map(pincode => ({
                            label: pincode.label,
                            value: pincode.value,
                            id: pincode.value
                        })));
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    $(this).val(ui.item.label);  
                    $(this).closest('tr').find('input[name*="[pincode_master_id]"]').val(ui.item.id);  
                    return false;
                }
            }).focus(function() {
                $(this).autocomplete("search", "");
            });

            //Vendor Org Autocomplete
            $row.find('.vendor-store-org-input').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: 'stock_orgs'
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: item['name'],
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching Organization data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    $(this).val(ui.item.label);  
                    $(this).closest('tr').find('input[name*="[organization_id]"]').val(ui.item.id);  
                    return false;
                }
            }).focus(function() {
                $(this).autocomplete("search", "");
            });

            $row.find('.vendor-store-location-input').autocomplete({
                source: function(request, response) {
                    const orgId = $(this.element).closest('tr').find('.vendor-store-org-id').val();
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: 'vendor_locations',
                            organization_id : orgId
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: item['store_name'],
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching Organization data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    $(this).val(ui.item.label);  
                    $(this).closest('tr').find('input[name*="[location_id]"]').val(ui.item.id);  
                    return false;
                }
            }).focus(function() {
                $(this).autocomplete("search", "");
            });

            $row.find('.vendor-store-store-input').autocomplete({
                source: function(request, response) {
                    const orgId = $(this.element).closest('tr').find('.vendor-store-org-id').val();
                    const locationId = $(this.element).closest('tr').find('.vendor-store-location-id').val();
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: 'vendor_sub_stores',
                            organization_id : orgId,
                            location_id : locationId,
                            store_types : ['Vendor']
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: item['name'],
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching Organization data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    $(this).val(ui.item.label);  
                    $(this).closest('tr').find('input[name*="[store_id]"]').val(ui.item.id);  
                    return false;
                }
            }).focus(function() {
                $(this).autocomplete("search", "");
            });
        }

        $('#address-table-body .address-row').each(function() {
            initializeAutocomplete($(this));
        });

        //Vendor Stores Initialization
        $('#vendor-stores-table-body .stores-row').each(function() {
            initializeAutocomplete($(this));
        });

        $(document).on('click', '.add-address', function(e) {
            e.preventDefault();
            const $lastRow = $('#address-table-body .address-row').last();
            const index = $lastRow.data('index') + 1;
            const $newRow = $lastRow.clone().attr('data-index', index);
            $newRow.find('input').val('');
            $newRow.find('input').removeAttr('data-country-id data-state-id data-city-id data-gst-state-id data-gst-country-id data-gst-state data-gst-country');
            $newRow.find('input[type="radio"]').prop('checked', false);
            $('#address-table-body').append($newRow);

            const $gstRows = $('#address-table-body .address-row').filter(function() {
                return $(this).find('input[name*="[state]"]').attr('data-gst-state-id') &&
                    $(this).find('input[name*="[state]"]').attr('data-gst-state') &&
                    $(this).find('input[name*="[country]"]').attr('data-gst-country-id') &&
                    $(this).find('input[name*="[country]"]').attr('data-gst-country');
            });

            const $gstRow = $gstRows.last();
            if ($gstRow.length) {
                const gstCountryId = $gstRow.find('input[name*="[country]"]').attr('data-gst-country-id');
                const gstCountryName = $gstRow.find('input[name*="[country]"]').val();
                const gstStateId = $gstRow.find('input[name*="[state]"]').attr('data-gst-state-id');
                const gstStateName = $gstRow.find('input[name*="[state]"]').val();
                $newRow.find('input[name*="[country_id]"]').val(gstCountryId);
                $newRow.find('input[name*="[country]"]').val(gstCountryName).prop('disabled', true);
                $newRow.find('input[name*="[state_id]"]').val(gstStateId);
                $newRow.find('input[name*="[state]"]').val(gstStateName).prop('disabled', true);
            }
            initializeAutocomplete($newRow);
            updateRowIndexes();
            applyCapsLock();
        });

        $(document).on('click', '.add-vendor-store', function(e) {
            e.preventDefault();
            const $lastRow = $('#vendor-stores-table-body .stores-row').last();
            const index = $lastRow.data('index') + 1;
            const $newRow = $lastRow.clone().attr('data-index', index);
            $newRow.find('input').val('');
            $('#vendor-stores-table-body').append($newRow);
            initializeAutocomplete($newRow);
            updateRowIndexesForVendorStores();
        });

         $(document).on('click', '.delete-address', function(e) {
            e.preventDefault();
            var $row = $(this).closest('.address-row');
            var addressId = $row.data('id');
            if (addressId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to delete this record?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, keep it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/vendors/address/' + addressId,
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                            },
                            success: function(response) {
                                if (response.status) {
                                    $row.remove();
                                    Swal.fire('Deleted!', response.message, 'success');
                                    updateRowIndexes();
                                } else {
                                    Swal.fire('Error!', response.message || 'Could not delete the address.', 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON.message || 'An error occurred while deleting the address.', 'error');
                            }
                        });
                    }
                });
            } else {
                $row.remove();
                updateRowIndexes();
            }
        });

        $(document).on('click', '.delete-vendor-store', function(e) {
            e.preventDefault();
            if ($('#vendor-stores-table-body .stores-row').length > 1) {
                $(this).closest('.stores-row').remove();
                updateRowIndexesForVendorStores();
            }
        });

        function updateRowIndexes() {
            var $rows = $('#address-table-body tr'); 
            $('#address-table-body .address-row').each(function(index) {
                $(this).find('.index').text(index + 1);
                $(this).find('input, select').each(function() {
                    $(this).attr('name', $(this).attr('name').replace(/\[\d+\]/, `[${index}]`));
                });
                if ($rows.length === 1) {
                    $(this).find('.delete-address').hide(); 
                    $(this).find('.add-address').show(); 
                } else {
                    $(this).find('.delete-address').show(); 
                    $(this).find('.add-address').toggle(index === 0); 
                }  
            });
        }

        function updateRowIndexesForVendorStores() {
            var $rows = $('#vendor-stores-table-body tr'); 
            $('#vendor-stores-table-body .stores-row').each(function(index) {
                $(this).find('.index').text(index + 1);
                $(this).find('input, select').each(function() {
                    $(this).attr('name', $(this).attr('name').replace(/\[\d+\]/, `[${index}]`));
                });
                if ($rows.length === 1) {
                    $(this).find('.delete-vendor-store').hide(); 
                    $(this).find('.add-vendor-store').show(); 
                } else {
                    $(this).find('.delete-vendor-store').show(); 
                    $(this).find('.add-vendor-store').toggle(index === 0); 
                }  
            });
        }

        updateRowIndexes();
        applyCapsLock();
    });
    
</script>

<script>
    $(document).ready(function() {
        function applyCapsLock() {
            $('input[type="text"], input[type="number"]').each(function() {
                $(this).val($(this).val().toUpperCase());
            });
            $('input[type="text"], input[type="number"]').on('input', function() {
                var value = $(this).val().toUpperCase();  
                $(this).val(value); 
            });
        }
        let $bankTableBody = $('#bank-info-container');
        let index = $bankTableBody.children('.bank-info-row').length;

        function updateRowIndices() {
            var $rows = $('#bank-info-container tr'); 
            $rows.each(function (index) {
                $(this).find('td:first').text(index + 1);
                $(this).find('input, select').each(function () {
                    var name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/\[\d+\]/, `[${index}]`));
                    }
                    var id = $(this).attr('id');
                    if (id) {
                        $(this).attr('id', id.replace(/\d+$/, index));
                    }
                });
                $(this).attr('id', `row-${index}`);
                if ($rows.length === 1) {
                    $(this).find('.delete-bank-row').hide(); 
                    $(this).find('.add-bank-row').show(); 
                } else {
                    $(this).find('.delete-bank-row').show(); 
                    $(this).find('.add-bank-row').toggle(index === 0); 
                }
            });
        }

        function addNewRow() {
            let $template = $bankTableBody.find('.bank-info-row:first').clone();
            $template.attr('data-index', index++);
            $template.find('input').each(function() {
                let name = $(this).attr('name');
                if ($(this).attr('type') !== 'file') {
                $(this).val('');
                } else {
                    $(this).val('');
                    $(this).attr('onchange', 'simpleFileValidation(this)'); 
                }
                $(this).attr('name', name.replace(/\d+/, index - 1)); 
            });
            $template.find('input[type=radio]').prop('checked', false).val('0');
            $template.find('input[type=file]').val('');
            $template.attr('data-id', '');
            $template.find('.file-link').parent().hide();
            $bankTableBody.append($template);
            updateRowIndices();
            feather.replace();
            applyCapsLock();
        }

        function fetchIfscDetails(ifscCode, $row) {
            if (!ifscCode) return;
            $.ajax({
                url: '/banks/ifsc/' + ifscCode,
                method: 'GET',
                success: function (data) {
                    if (data.status) {
                        $row.find('.bank-name').val(data.data.BANK); 
                    } else {
                        $row.find('.bank-name').val('');
                        console.warn('Invalid IFSC code');
                    }
                },
                error: function () {
                    $row.find('.bank-name').val('');
                    console.error('Error fetching IFSC info');
                }
            });
        }

        $bankTableBody.on('keyup', '.ifsc-code', function () {
            let $row = $(this).closest('tr');
            let ifscCode = $(this).val().trim();
            clearTimeout($.data(this, 'timer'));
            let wait = setTimeout(() => {
                fetchIfscDetails(ifscCode, $row);
            }, 300);
            $(this).data('timer', wait);
        });

        $bankTableBody.on('click', '.add-bank-row', function(e) {
            e.preventDefault();
            addNewRow();
        });

        $bankTableBody.on('click', '.delete-bank-row', function(e) {
            e.preventDefault();
            var $row = $(this).closest('.bank-info-row');
            var bankInfoId = $row.data('id');
            if (bankInfoId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to delete this record?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, keep it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/vendors/bank-info/' + bankInfoId, 
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                            },
                            success: function(response) {
                                if (response.status) {
                                    $row.remove();
                                    Swal.fire('Deleted!', response.message, 'success');
                                    updateRowIndices();
                                } else {
                                    Swal.fire('Error!', response.message || 'Could not delete the record.', 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON.message || 'An error occurred while deleting the record.', 'error');
                            }
                        });
                    }
                });
            } else {
                $row.remove();
                updateRowIndices();
            }
        });
        $bankTableBody.on('change', 'input[type=radio]', function() {
            $bankTableBody.find('input[type=radio]').prop('checked', false).val('0');
            $(this).prop('checked', true).val('1');
        });

        if ($bankTableBody.children('.bank-info-row').length === 0) {
            addNewRow();
        }
        updateRowIndices(); 
    });
</script>

<script>
$(document).ready(function() {
    var today = new Date().toISOString().split('T')[0];
    $('#transactionDate').val(today);
    function fetchExchangeRates() {
        var transactionDate = $('#transactionDate').val();
        var currencyId = $('#currencySelect').val();
        $('#orgCurrencyRow, #groupCurrencyRow, #companyCurrencyRow').hide();
        if (currencyId && transactionDate) {
            $.ajax({
                url: '/exchange-rates/get-currency-exchange-rate',
                type: 'POST',
                data: {
                    currency: currencyId,
                    date: transactionDate,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.status) {
                        $('#orgCurrencyRow, #groupCurrencyRow, #companyCurrencyRow').show();
                        $('#orgCurrencySymbol').text(response.data.org_currency_code);
                        $('#groupCurrencySymbol').text(response.data.group_currency_code);
                        $('#companyCurrencySymbol').text(response.data.comp_currency_code);
                        $('#orgCurrency').val(response.data.org_currency_exg_rate);
                        $('#groupCurrency').val(response.data.group_currency_exg_rate);
                        $('#companyCurrency').val(response.data.comp_currency_exg_rate);
                        $('#submit-button').prop('disabled', false);
                        $('#save-draft-button').prop('disabled', false);
                    } else {
                        alert(response.message);
                        $('#submit-button').prop('disabled', true);
                        $('#save-draft-button').prop('disabled', true);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error: ', error);
                    alert('An error occurred while fetching exchange rates.');
                    $('#submit-button').prop('disabled', true);
                    $('#save-draft-button').prop('disabled', true);
                }
            });
        } else {
            alert('Please select a currency and ensure the date is set.');
            $('#submit-button').prop('disabled', true);
            $('#save-draft-button').prop('disabled', true);
        }
    }
    $('#currencySelect').on('change', function() {
        fetchExchangeRates();
    });
    if ($('#currencySelect').val()) {
        fetchExchangeRates();
    }
});
</script>
<script>
    $(document).ready(function() {
        function applyCapsLock() {
            $('input[type="text"], input[type="number"]').each(function() {
                $(this).val($(this).val().toUpperCase());
            });
            $('input[type="text"], input[type="number"]').on('input', function() {
                var value = $(this).val().toUpperCase();  
                $(this).val(value); 
            });
        }
        applyCapsLock();
    });
 </script>
<script>
    $(document).ready(function() {
        const vendorCodeType = '{{ $vendorCodeType }}'; 
        const companyNameInput = $('input[name="company_name"]'); 
        const vendorTypeInput = $('input[name="vendor_type"]');
        const catInitialsInput = $('input[name="cat_initials"]');
        const subCatInitialsInput = $('input[name="sub_cat_initials"]');
        const vendorInitialInput = $('input[name="vendor_initial"]');
        const vendorCodeInput = $('input[name="vendor_code"]'); 
        const vendorIdInput = $('input[name="vendor_id"]'); 
        const isEditable = {{ isset($vendor) && $vendor->status === 'draft' ? 'true' : 'false' }};
        if (vendorCodeType === 'Manual' && isEditable) {
            vendorCodeInput.prop('readonly', false); 
        } else {
            vendorCodeInput.prop('readonly', true); 
        }

        function getVendorInitials(companyName) {
            const cleanedCompanyName = companyName.replace(/[^a-zA-Z0-9\s]/g, '');
            const words = cleanedCompanyName.split(/\s+/).filter(word => word.length > 0); 
            let initials = '';
            if (words.length === 1) {
                initials = words[0].substring(0, 3).toUpperCase();
            } else if (words.length === 2) {
                initials = words[0].substring(0, 2).toUpperCase() + words[1][0].toUpperCase();
            } else if (words.length >= 3) {
                initials = words[0][0].toUpperCase() + words[1][0].toUpperCase() + words[2][0].toUpperCase();
            }
            return initials.substring(0, 3); 
        }

        function generateVendorCode() {
            if (vendorCodeType === 'Manual' || !isEditable) {
                return; 
            }
            const companyName = companyNameInput.val().trim();
            const vendorInitials = vendorInitialInput.val().trim() || getVendorInitials(companyName); 
            vendorInitialInput.val(vendorInitials); 
            const categoryInitials = (catInitialsInput.val() || '').trim();
            const subCategoryInitials = (subCatInitialsInput.val() || '').trim();
            const selectedVendorType = vendorTypeInput.filter(':checked').val();  
            let vendorTypeCode = '';
            if (selectedVendorType === 'Regular') {
                vendorTypeCode = 'R'; 
            } else if (selectedVendorType === 'Cash') {
                vendorTypeCode = 'CA';   
            }

            $.ajax({
                url: '{{ route('generate-vendor-code') }}',  
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}', 
                    company_name: companyName,
                    vendor_type: vendorTypeCode,
                    vendor_initials: vendorInitials,
                    vendor_id: vendorIdInput.val() 
                },
                success: function(response) {
                    vendorCodeInput.val((response.vendor_code || '')); 
                },
                error: function() {
                    vendorCodeInput.val(''); 
                }
            });
        }

        companyNameInput.on('input change', function() {
            const companyName = $(this).val().trim();  
            vendorInitialInput.val(getVendorInitials(companyName)); 
            if (vendorCodeType === 'Auto') {
                generateVendorCode();
            }
        });

        vendorCodeInput.on('input', function() {
            $(this).val($(this).val().toUpperCase());
        });

        vendorTypeInput.on('change', generateVendorCode);

        const companyNamee = companyNameInput.val().trim();
        if (companyNamee.length > 0) {
            const vendorInitials = getVendorInitials(companyNamee);
            vendorInitialInput.val(vendorInitials); 
        }
    });
</script>
<script>
    //related-checkbox-start
    if ($('#Related').is(':checked')) {
        $('#groupOrganizationsDropdown').show();
        $('#contraLedger').show();
    } else {
        $('#groupOrganizationsDropdown').hide();
        $('#contraLedger').hide();
    }
    $('#Related').change(function() {
        if ($(this).is(':checked')) {
            $('#groupOrganizationsDropdown').show();
            $('#contraLedger').show();
        } else {
            $('#groupOrganizationsDropdown').hide();
            $('#contraLedger').hide();
            $('#enter_company_org_id').val('').trigger('change');
        }
    });
     //related-checkbox-end

    // file-validation-start
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
    // file-validation-end
    var currentRevNo = $("#revisionNumber").val();
     $(document).on('change', '#revisionNumber', function (e) {
        e.preventDefault();
        const selectedRev = e.target.value;
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('revisionNumber', selectedRev);
        $("#revisionNumber").val(currentRevNo);
        window.open(currentUrl.toString(), '_blank');
    });
    // approval-start
    $(document).on('submit', '.ajax-submit-2', function (e) {
        e.preventDefault();
        var submitButton = (e.originalEvent && e.originalEvent.submitter)
                            || $(this).find(':submit');
        var submitButtonHtml = submitButton.innerHTML;
        submitButton.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
        submitButton.disabled = true;
        var method = $(this).attr('method');
        var url = $(this).attr('action');
        var redirectUrl = $(this).data('redirect');
        var data = new FormData($(this)[0]);
        data.append('status', $('#status_hidden_input').val());
        data.append('ledger_group_id', $('#ledger_group_name').val());
        data.append('hidden_ledger_vendor_name', $('#hidden_ledger_vendor_name').val());
        data.append('hidden_ledger_vendor_code', $('#hidden_ledger_vendor_code').val());
        data.append('create_ledger', $('#create_vendor_ledger').is(':checked') ? 1 : 0);

        const $ledgerGroupId = $(".ledger-group-id");
        var formObj = $(this);
    
        $.ajax({
            url,
            type: method,
            data,
            contentType: false,
            processData: false,
            success: function (res) {
                submitButton.disabled = false;
                submitButton.innerHTML = submitButtonHtml;
                $('.ajax-validation-error-span').remove();
                $(".is-invalid").removeClass("is-invalid");
                $(".help-block").remove();
                $(".waves-ripple").remove();
                Swal.fire({
                    title: 'Success!',
                    text: res.message,
                    icon: 'success',
                });
                setTimeout(() => {
                    if (res.store_id) {
                        location.href = `/stores/${res.store_id}/edit`;
                    } else if (redirectUrl) {
                        location.href = redirectUrl;
                    } else {
                        location.reload();
                    }
                }, 1500);
            
            },
            error: function (error) {
                submitButton.disabled = false;
                submitButton.innerHTML = submitButtonHtml;
                $('.ajax-validation-error-span').remove();
                $(".is-invalid").removeClass("is-invalid");
                $(".help-block").remove();
                $(".waves-ripple").remove();
                let res = error.responseJSON || {};
                if (error.status === 422 && res.errors) {
                    if (
                        Object.size(res) > 0 &&
                        Object.size(res.errors) > 0
                    ) {
                        show_validation_error(res.errors);
                    }
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

    function setApproval()
    {
        document.getElementById('action_type').value = "approve";
        document.getElementById('approve_reject_heading_label').textContent = "Approve Item";

    }
    function setReject()
    {
        document.getElementById('action_type').value = "reject";
        document.getElementById('approve_reject_heading_label').textContent = "Reject Item";
    }
    function revokeDocument() {
        const vendorId = "{{ isset($vendor) ? $vendor->id : null }}"; 
        if (vendorId) {
            $.ajax({
                url: "{{ route('vendor.revoke') }}", 
                method: 'POST',
                dataType: 'json',
                data: {
                    id: vendorId 
                },
                success: function(data) {
                    if (data.status == 'success') {
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
                        window.location.href = "{{ route('vendor.index') }}"; 
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching vendor data:', xhr.responseText);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Some internal error occured',
                        icon: 'error',
                    });
                }
            });
        }
    }
    function closeModal(id)
    {
        $('#' + id).modal('hide');
    }
    function openModal(id)
    {
        $('#' + id).modal('show');
    }
      function disableAllFieldsAndTabs() {
        document.querySelectorAll('input, select, textarea').forEach(el => {
            if (el.classList.contains('cannot_disable')) {
                return; // Skip this element
            }
            el.disabled = true;
            if (el.type === 'checkbox' || el.type === 'radio') {
                el.disabled = true;
            }
        });
    }
    const status = document.getElementById('documentStatus').value;
    if (status === 'submitted' || status === 'approved' || status === 'approval_not_required') {
        disableAllFieldsAndTabs();
    }
    function enableAmendmentFields() {
      const isLedgerEditable = @json($isLedgerEditable);
       document.querySelectorAll('input, select, textarea').forEach(el => {
        if (el.getAttribute('name') !== 'vendor_code') {
            el.disabled = false;
                el.readOnly = false;
            }
        });
       //  GST Applicable condition check
        const gstApplicable = document.querySelector('input[name="compliance[gst_applicable]"]:checked');
        if (gstApplicable && gstApplicable.value == '0') {
            document.getElementById('gstinNo')?.setAttribute('disabled', true);
            document.querySelector('input[name="compliance[gst_registered_name]"]')?.setAttribute('disabled', true);
            document.querySelector('input[name="compliance[gstin_registration_date]"]')?.setAttribute('disabled', true);
            document.querySelector('input[name="compliance[gst_certificate][]"]')?.setAttribute('disabled', true);
        }
        const checkbox = document.getElementById('customSwitch3');
        if (checkbox) {
            checkbox.disabled = false;
        }
        const amendDeleteBtn = document.getElementById('btnAmendDelete');
        if (amendDeleteBtn) {
            amendDeleteBtn.style.setProperty('display', 'inline-block', 'important');
        }
        // Also check ledger existence and disable create ledger checkbox accordingly
        const ledgerId = document.getElementById('ledger_id')?.value || '';
        const createLedgerCheckbox = document.getElementById('create_vendor_ledger');

        if (createLedgerCheckbox) {
            if (ledgerId.trim() !== '') {
                createLedgerCheckbox.disabled = true;
                createLedgerCheckbox.checked = false;
            } else {
                createLedgerCheckbox.disabled = false;
            }
        }
         if (!isLedgerEditable) {
            document.getElementById('ledger_name')?.setAttribute('readonly', true);
            document.getElementById('ledger_group_name')?.setAttribute('disabled', true);
            if (createLedgerCheckbox) createLedgerCheckbox.disabled = true;
        }
    }
    function amendConfirm()
{
     enableAmendmentFields();
    const amendButton = document.getElementById('amendShowButton');
    if (amendButton) {
        amendButton.style.display = "none";
    }
    const buttonParentDiv = document.getElementById('buttonsDiv');
    const newSubmitButton = document.createElement('button');
    newSubmitButton.type = "button";
    newSubmitButton.id = "amend-submit-button";
    newSubmitButton.className = "btn btn-primary btn-sm mb-50 mb-sm-0 submit-button";
    newSubmitButton.value = "submitted"; 
    newSubmitButton.innerHTML = `<i data-feather="check-circle"></i> Submit`;
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

}

    function openAmendConfirmModal()
    {
        $("#amendConfirmPopup").modal("show");
    }

    function submitAmend()
    {
        let remark = $("#amendConfirmPopup").find('[name="amend_remarks"]').val();
        $("#action_type_main").val("amendment");
        $("#amendConfirmPopup").modal('hide');
        $("#vendor_form").submit();
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
    document.addEventListener('DOMContentLoaded', function () {
        const switchInput = document.getElementById('customSwitch3');
        const hiddenInput = document.getElementById('status_hidden_input');
        hiddenInput.value = switchInput.checked ? 'active' : 'inactive';
        switchInput.addEventListener('change', function () {
            hiddenInput.value = switchInput.checked ? 'active' : 'inactive';
        });
    });
// approval-end
    function initializeAutocomplete(selector, options) {
        const $input = $(selector);
        const hiddenFieldSelector = options.hiddenFieldSelector();
        const $ledgerGroupSelect = $(".ledger-group-select");
        const $ledgerGroupId = $(".ledger-group-id");
        const $createLedger = $('#create_vendor_ledger');
        $input.autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: options.url,
                    method: 'GET',
                    data: {
                        q: request.term,
                        type: options.type
                    },
                   success: function(data) {
                        let items = [];
                        if (data.status === false || !Array.isArray(data.data) || data.data.length === 0) {
                            items = [{
                                label: data.message || 'No record found.',
                                value: '',
                                id: null,
                                disabled: true
                            }];
                        } else {
                            items = data.data.map(item => ({
                                label: item[options.labelField],
                                value: item[options.labelField],
                                id: item.id,
                                ...item
                            }));
                        }

                        response(items);
                    },
                    error: function() {
                        response([]);
                    }
                });
            },
            minLength: options.minLength || 0,
            select: function(event, ui) {
                if (ui.item && ui.item.id) {
                    $(hiddenFieldSelector).val(ui.item.id);
                    if (typeof options.onSelect === 'function') {
                        options.onSelect(ui.item);
                    }
                }
            },
            change: function(event, ui) {
                if (!ui.item) {
                    $(hiddenFieldSelector).val('');
                    $ledgerGroupSelect.empty();
                    $ledgerGroupId.val('');
                    $createLedger.prop('disabled', false);
                }
            },
            search: function(event, ui) {
                if (event.originalEvent && event.originalEvent.type === 'focus') {
                    event.preventDefault();
                }
            }
        }).focus(function() {
            if (this.value === "") {
                $(this).autocomplete("search", "");
            }
        }).on('input', function() {
            if ($(this).val().trim() === '') {
                $(hiddenFieldSelector).val('');
                $ledgerGroupSelect.empty();
                $ledgerGroupId.val('');
                $createLedger.prop('disabled', false);
            }
        });

        if (typeof options.onInitialize === 'function') {
            options.onInitialize();
        }
    }
    function updateLedgerGroupDropdownWithType(ledgerId, type) {
        const $ledgerGroupSelect = $(".ledger-group-select");
        const $ledgerGroupId = $(".ledger-group-id");
        $ledgerGroupSelect.empty();

        let url = '';
        if (ledgerId) {
            url = `/vendors/ledger/${ledgerId}/groups?type=${encodeURIComponent(type)}`;
        } else {
            url = `/vendors/ledgers/group-by-type?type=${encodeURIComponent(type)}`;
        }

        $.ajax({
            url: url,
            method: 'GET',
            success: function(data) {
                if (Array.isArray(data) && data.length) {
                    data.forEach(function(group) {
                        const option = new Option(group.name, group.id);
                        $ledgerGroupSelect.append(option);
                    });

                    const preselectedGroupId = $ledgerGroupId.val();
                    if (preselectedGroupId) {
                        $ledgerGroupSelect.val(preselectedGroupId).trigger('change');
                    }
                } else {
                    console.warn('No groups found for type:', type);
                    $ledgerGroupId.val(''); 
                }
            },
            error: function() {
                $ledgerGroupId.val(''); 
            }
        });
    }

    $(document).ready(function () {
        const $createLedger = $('#create_vendor_ledger');
        const $ledgerId = $('#ledger_id');
        const $ledgerName = $('#ledger_name');
        const $ledgerGroup = $('.ledger-group-select');
        const $ledgerGroupId = $(".ledger-group-id");
        const $hiddenName = $('#hidden_ledger_vendor_name');
        const $hiddenCode = $('#hidden_ledger_vendor_code');
        function toggleLedgerOption() {
            const hasLedger = $('.ladger-id').val()?.trim() || $ledgerId.val()?.trim();
            $createLedger.prop('disabled', !!hasLedger);

            if (hasLedger) {
                $createLedger.prop('checked', false);
            }
        }
        
        function handleCreateLedgerChange() {
            if ($createLedger.is(':checked')) {
                $hiddenName.val($('input[name="company_name"]').val());
                $hiddenCode.val($('input[name="vendor_code"]').val());
                $ledgerName.prop('disabled', true).val('');
                updateLedgerGroupDropdownWithType(null, 'vendor');
            } else {
                $hiddenName.val('');
                $hiddenCode.val('');
                $ledgerName.prop('disabled', false);
                $ledgerGroup.empty();
                $ledgerGroupId.val('');
            }
        }

        $createLedger.change(handleCreateLedgerChange);

        $ledgerGroup.on('change', function () {
            $ledgerGroupId.val($(this).val());
        });

        initializeAutocomplete(".vendor-ladger-autocomplete", {
            url: '/search',
            type: 'vendorLadger',
            labelField: 'name',
            hiddenFieldSelector: () => '.ladger-id',
            minLength: 0,
            additionalFields: ['description'],
            onSelect: function (selectedItem) {
                if (selectedItem?.id) {
                    updateLedgerGroupDropdownWithType(selectedItem.id, 'vendor');
                    toggleLedgerOption();
                }
            },
            onInitialize: function () {
                const ledgerId = $('.ladger-id').val();
                if (ledgerId) {
                    updateLedgerGroupDropdownWithType(ledgerId, 'vendor');
                }
            }
        });

        if (status === 'submitted') {
            handleCreateLedgerChange();
        }
        
    });
</script>
@endsection
