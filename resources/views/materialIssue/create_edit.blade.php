@extends('layouts.app')
@section('styles')
<style>

.item-name-wrapper {
            min-width: 100px;
            max-width: 150px;
            display: block;
            white-space: normal;
        }

        .wrap-if-too-long {
            display: inline-block;
            white-space: normal;
            word-break: break-word;
            overflow-wrap: break-word;
        }
</style>

@endsection
@section('content')
    <!-- BEGIN: Content-->
    <form method="POST" data-completionFunction = "disableHeader" class="ajax-input-form sales_module_form material_issue" action = "{{route('material.issue.store')}}" data-redirect="{{ $redirect_url }}" id = "sale_invoice_form" enctype='multipart/form-data'>

    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
				<div class="row">
                    @include('layouts.partials.breadcrumb-add-edit', [
                        'title' => 'Material Issue',
                        'menu' => 'Home',
                        'menu_url' => url('home'),
                        'sub_menu' => 'Add New'
                    ])
                    <input type = "hidden" value = "draft" name = "document_status" id = "document_status" />
					<div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
						<div class="form-group breadcrumb-right" id = "buttonsDiv">
                        @if(!isset(request() -> revisionNumber))
                        <button type = "button" onclick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
                            @if (isset($order))
                                @php
                                    $printOption = 'Material Issue';
                                    if ($order -> issue_type === 'Location Transfer' || $order -> issue_type === 'Sub Contracting' || $order -> issue_type === 'Job Work')
                                    {
                                        $printOption = 'Delivery Challan';
                                    }
                                @endphp
                                @if($buttons['print'])
                                    <a href="{{ route('material.issue.generate-pdf', [$order->id, $printOption]) }}" target="_blank" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light dropdown-toggle" id="dropdownMenuButton" aria-expanded="false">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer">
                                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                            <rect x="6" y="14" width="12" height="8"></rect>
                                        </svg>
                                        Print
                                    </a>
                                @endif
                                @if($buttons['draft'])
                                    <button type="button" onclick = "submitForm('draft');" name="action" value="draft" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" id="save-draft-button" name="action" value="draft"><i data-feather='save'></i> Save as Draft</button>
                                @endif
                                @if($buttons['submit'])
                                    <button type="button" onclick = "submitForm('submitted');" name="action" value="submitted" class="btn btn-primary btn-sm" id="submit-button" name="action" value="submitted"><i data-feather="check-circle"></i> Submit</button>
                                @endif
                                @if($buttons['approve'])
                                    <button type="button" id="reject-button" data-bs-toggle="modal" data-bs-target="#approveModal" onclick = "setReject();" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg> Reject</button>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal" onclick = "setApproval();" ><i data-feather="check-circle"></i> Approve</button>
                                @endif
                                @if($buttons['amend'])
                                    <button id = "amendShowButton" type="button" onclick = "openModal('amendmentconfirm')" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i> Amendment</button>
                                @endif
                                @if($buttons['voucher'])
                                <button type = "button" onclick = "onPostVoucherOpen('posted');" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg> Voucher</button>
                                @endif
                                @if($buttons['revoke'])
                                    <button id = "revokeButton" type="button" onclick = "revokeDocument();" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='rotate-ccw'></i> Revoke</button>
                                @endif
                            @else
                                <button type = "button" name="action" value="draft" id = "save-draft-button" onclick = "submitForm('draft');" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0"><i data-feather='save'></i> Save as Draft</button>
                                <button type = "button" name="action" value="submitted"  id = "submit-button" onclick = "submitForm('submitted');" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Submit</button>
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
								 <div class="card-body customernewsection-form" id ="main_so_form">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                                    <div>
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                            </div>
                                            @if (isset($order) && isset($docStatusClass))
                                            <div class="col-md-6 text-sm-end">
                                                <span class="badge rounded-pill badge-light-{{$order->display_status === 'Posted' ? 'info' : 'secondary'}} forminnerstatus">
                                                    <span class = "text-dark" >Status</span> : <span class="{{$docStatusClass}}">{{$order->display_status}}</span>
                                                </span>
                                            </div>

                                            @endif
                                            <div class="col-md-8">
                                                <input type = "hidden" name = "type" id = "type_hidden_input"></input>
                                            @if (isset($order))
                                                <input type = "hidden" value = "{{$order -> id}}" name = "material_issue_id"></input>
                                            @endif

                                                    <div class="row align-items-center mb-1 d-none">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Document Type <span class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <select class="form-select disable_on_edit" id = "service_id_input" {{isset($order) ? 'disabled' : ''}} onchange = "onServiceChange(this);">
                                                                @foreach ($services as $currentService)
                                                                    <option value = "{{$currentService -> alias}}" {{isset($selectedService) ? ($selectedService == $currentService -> alias ? 'selected' : '') : ''}}>{{$currentService -> name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>


                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Series <span class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <select class="form-select disable_on_edit" onChange = "getDocNumberByBookId(this);" name = "book_id" id = "series_id_input">
                                                                @foreach ($series as $currentSeries)
                                                                    <option value = "{{$currentSeries -> id}}" {{isset($order) ? ($order -> book_id == $currentSeries -> id ? 'selected' : '') : ''}}>{{$currentSeries -> book_code}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <input type = "hidden" name = "book_code" id = "book_code_input" value = "{{isset($order) ? $order -> book_code : ''}}"></input>
                                                     </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Document No <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <input type="text" value = "{{isset($order) ? $order -> document_number : ''}}" class="form-control disable_on_edit" readonly id = "order_no_input" name = "document_no">
                                                        </div>
                                                     </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Document Date <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <input type="date" value = "{{isset($order) ? $order -> document_date : min([Carbon\Carbon::now() -> format('Y-m-d'),$current_financial_year['end_date']])}}" class="form-control" name = "document_date" id = "order_date_input" oninput = "onDocDateChange();">
                                                        </div>
                                                     </div>

                                                     <div class="row align-items-center lease-hidden">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Issue Type<span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <select class="form-select disable_on_edit" name = "issue_type" id = "issue_type_input" oninput = "onIssueTypeChange(this);">
                                                            </select>
                                                        </div>
                                                    </div>
                                        </div>
                                        @if(isset($order) && ($order -> document_status !== "draft"))
                            @if((isset($approvalHistory) && count($approvalHistory) > 0) || isset($revision_number))
                           <div class="col-md-4">
                               <div class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
                                   <h5 class="mb-2 text-dark border-bottom pb-50 d-flex align-items-center justify-content-between">
                                       <strong><i data-feather="arrow-right-circle"></i> Approval History</strong>
                                       @if(!isset(request() -> revisionNumber) && $order -> document_status !== 'draft')
                                       <strong class="badge rounded-pill badge-light-secondary amendmentselect">Rev. No.
                                           <select class="form-select" id="revisionNumber">
                                            @for($i=$revision_number; $i >= 0; $i--)
                                               <option value="{{$i}}" {{request('revisionNumber',$order->revision_number) == $i ? 'selected' : ''}}>{{$i}}</option>
                                            @endfor
                                           </select>
                                       </strong>
                                       @else
                                       @if ($order -> document_status !== 'draft')
                                       <strong class="badge rounded-pill badge-light-secondary amendmentselect">
                                        Rev. No.{{request() -> revisionNumber}}
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
                                                   <h6>{{ucfirst($approvalHist->name ?? $approvalHist?->user?->name ?? 'NA')}}</h6>
                                                   @if($approvalHist->approval_type == 'approve')
                                                   <span class="badge rounded-pill badge-light-success">{{ucfirst($approvalHist->approval_type)}}</span>
                                                   @elseif($approvalHist->approval_type == 'submit')
                                                   <span class="badge rounded-pill badge-light-primary">{{ucfirst($approvalHist->approval_type)}}</span>
                                                   @elseif($approvalHist->approval_type == 'reject')
                                                   <span class="badge rounded-pill badge-light-danger">{{ucfirst($approvalHist->approval_type)}}</span>
                                                   @elseif($approvalHist->approval_type == 'posted')
                                                   <span class="badge rounded-pill badge-light-info">{{ucfirst($approvalHist->approval_type)}}</span>
                                                   @else
                                                   <span class="badge rounded-pill badge-light-danger">{{ucfirst($approvalHist->approval_type)}}</span>
                                                   @endif
                                               </div>
                                                @if($approvalHist->created_at)
                                                    <h6>
                                                        {{ \Carbon\Carbon::parse($approvalHist->created_at)->timezone('Asia/Kolkata')->format('d/m/Y | h.iA') }}
                                                    </h6>
                                                @endif
                                                @if($approvalHist->remarks)
                                                <p>{!! $approvalHist->remarks !!}</p>
                                                @endif
                                                @if ($approvalHist -> media && count($approvalHist -> media) > 0)
                                                    @foreach ($approvalHist -> media as $mediaFile)
                                                        <p><a href="{{$mediaFile -> file_url}}" target = "_blank"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg></a></p>
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

                                </div>
                            </div>


                            </div>
                            <div class="col-md-12" id = "general_information_tab">
									<div class="card">

                                    <div class="card-body">

                                    @if (!isset($order))
                                    <div class="row align-items-center flex-wrap">
    <!-- Requester Type (Hidden Input) -->
    <input type="hidden" name="requester_type" value="{{ isset($order) ? $order->requester_type : 'Department' }}" id="requester_type_input" />

    <!-- From Location -->
    <div class="col-md-2 mb-2">
        <label class="form-label" id="from_location_header_label">From Location<span class="text-danger">*</span></label>
        <select class="form-select disable_on_edit" name="store_from_id" id="store_from_id_input" oninput="onHeaderStoreChange(this, 'from');">
            @foreach ($stores as $store)
                <option value="{{ $store->id }}" {{ isset($order) ? ($order->from_store_id == $store->id ? 'selected' : '') : '' }} data-name="{{ $store->store_name }}">{{ $store->store_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2 mb-2 from_sub_store_dependent">
        <label class="form-label" id="from_store_header_label">From Store<span class="text-danger">*</span></label>
        <select class="form-select disable_on_edit" name="sub_store_from_id" id="sub_store_from_id_input" oninput = "headerSubStoreChange(this, 'from')">

        </select>
    </div>

    <div class="col-md-2 mb-2 d-none" id = "from_station_header_label">
        <label class="form-label" >From Station<span class="text-danger">*</span></label>
        <select class="form-select disable_on_edit" name="station_from_id" id="station_from_id_input">

        </select>
    </div>

    <!-- To Location -->
    <div class="col-md-2 mb-2 location_transfer" style="display:none;">
        <label class="form-label">To Location<span class="text-danger">*</span></label>
        <select class="form-select disable_on_edit" name="store_to_id" id="store_to_id_input" oninput="onHeaderStoreChange(this, 'to');">
            <option value="" disabled selected>Select</option>
            @foreach ($stores as $store)
                <option store-type="{{ $store->store_location_type }}" value="{{ $store->id }}" {{ isset($order) ? ($order->to_store_id == $store->id ? 'selected' : '') : '' }} data-name="{{ $store->store_name }}">{{ $store->store_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2 mb-2 sub_loc_transfer location_transfer to_sub_store_dependent d-none">
        <label class="form-label" id="to_store_header_label">To Store<span class="text-danger">*</span></label>
        <select class="form-select disable_on_edit" name="sub_store_to_id" id="sub_store_to_id_input" oninput = "headerSubStoreChange(this, 'to')">

        </select>
    </div>

    <div class="col-md-2 mb-2 d-none" id="to_station_header_label">
        <label class="form-label" >To Station<span class="text-danger">*</span></label>
        <select class="form-select disable_on_edit" name="station_to_id" id="station_to_id_input">

        </select>
    </div>

    <!-- Vendor -->
    <div class="col-md-2 mb-2 sub_contracting d-none">
        <label class="form-label">Vendor<span class="text-danger">*</span></label>
        <select class="form-select disable_on_edit" name="vendor_id" id="vendor_id_input" oninput="onVendorChange(this);">
            <option value="" disabled selected>Select</option>
            @foreach ($vendors as $vendor)
                <option value="{{ $vendor->id }}" {{ isset($order) ? ($order->vendor_id == $vendor->id ? 'selected' : '') : '' }}>{{ $vendor->company_name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Vendor Location -->
    <div class="col-md-2 mb-2 sub_contracting d-none">
        <label class="form-label">Vendor Store<span class="text-danger">*</span></label>
        <select class="form-select disable_on_edit" name="vendor_store_id" id="vendor_store_id_input" oninput = "enableDisableQtButton();">
            <option value="" disabled selected>Select</option>
        </select>
    </div>


    <!-- Department -->
    <div class="col-md-2 mb-2 consumption consumption_dept d-none" id="department_id_header">
        <label class="form-label">Department</label>
        <select class="form-select disable_on_edit" name="department_id" id="department_id_input">
            @foreach ($departments as $department)
                <option {{ $selectedDepartmentId == $department->id ? 'selected' : '' }} value="{{ $department->id }}">{{ $department->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Requester -->
    <div class="col-md-2 mb-2 consumption consumption_user d-none" id="user_id_header">
        <label class="form-label">Requester</label>
        <input type="text" class="form-control mw-100 ledgerselecct ui-autocomplete-input" id="user_id_dropdown" placeholder="Select">
        <input type="hidden" value="" name="user_id" id="user_id_input">
    </div>

</div>

                                    @else
                                    <div class="row align-items-center flex-wrap">
    <!-- Requester Type (Hidden Input) -->
    <input type="hidden" name="requester_type" value="{{ isset($order) ? $order->requester_type : 'Department' }}" id="requester_type_input" />

    <!-- From Location -->
    <div class="col-md-2 mb-2">
        <label class="form-label" id="from_location_header_label">From Location<span class="text-danger">*</span></label>
        <select class="form-select disable_on_edit" name="store_from_id" id="store_from_id_input" oninput="onHeaderStoreChange(this, 'from');">
            @foreach ($stores as $store)
                <option value="{{ $store->id }}" {{ isset($order) ? ($order->from_store_id == $store->id ? 'selected' : '') : '' }} data-name="{{ $store->store_name }}">{{ $store->store_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2 mb-2 from_sub_store_dependent">
        <label class="form-label" id="from_store_header_label">From Store<span class="text-danger">*</span></label>
        <select class="form-select disable_on_edit" name="sub_store_from_id" id="sub_store_from_id_input" oninput = "headerSubStoreChange(this, 'from')">
            <option value = "{{$order -> from_sub_store_id}}" >{{$order ?-> from_sub_store ?-> name}}</option>
        </select>
    </div>

    <div class="col-md-2 mb-2 {{$order -> from_station_id ? '' : 'd-none'}}" id = "from_station_header_label">
        <label class="form-label" >From Station<span class="text-danger">*</span></label>
        <select class="form-select disable_on_edit" name="station_from_id" id="station_from_id_input">
        <option value = "{{$order -> from_station_id}}" >{{$order ?-> from_station ?-> name}}</option>
        </select>
    </div>

    <!-- To Location -->
    <div class="col-md-2 mb-2 location_transfer" style="{{$order -> to_store_id ? '' : 'display:none;'}}">
        <label class="form-label">To Location<span class="text-danger">*</span></label>
        <select class="form-select disable_on_edit" name="store_to_id" id="store_to_id_input" oninput="onHeaderStoreChange(this, 'to');">
            <option value="" disabled selected>Select</option>
            @foreach ($stores as $store)
                <option store-type="{{ $store->store_location_type }}" value="{{ $store->id }}" {{ isset($order) ? ($order->to_store_id == $store->id ? 'selected' : '') : '' }} data-name="{{ $store->store_name }}">{{ $store->store_name }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-2 mb-2 sub_loc_transfer location_transfer to_sub_store_dependent {{$order -> to_sub_store_id ? '' : 'd-none'}}">
        <label class="form-label" id="to_store_header_label">To Store<span class="text-danger">*</span></label>
        <select class="form-select disable_on_edit" name="sub_store_to_id" id="sub_store_to_id_input" oninput = "headerSubStoreChange(this, 'to')">
            <option value = "{{$order -> to_sub_store_id}}" >{{$order -> to_sub_store ?-> name}}</option>
        </select>
    </div>

    <div class="col-md-2 mb-2 {{$order -> to_station_id ? '' : 'd-none'}}" id="to_station_header_label">
        <label class="form-label" >To Station<span class="text-danger">*</span></label>
        <select class="form-select disable_on_edit" name="station_to_id" id="station_to_id_input">
            <option value = "{{$order -> to_station_id}}">{{$order -> to_station ?-> name}}</option>
        </select>
    </div>

    <!-- Vendor -->
    <div class="col-md-2 mb-2 sub_contracting {{$order -> issue_type === 'Sub Contracting' ? '' : 'd-none'}}">
        <label class="form-label">Vendor<span class="text-danger">*</span></label>
        <select class="form-select disable_on_edit" name="vendor_id" id="vendor_id_input" oninput="onVendorChange(this);">
            <option value="" disabled selected>Select</option>
            @foreach ($vendors as $vendor)
                <option value="{{ $vendor->id }}" {{ isset($order) ? ($order->vendor_id == $vendor->id ? 'selected' : '') : '' }}>{{ $vendor->company_name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Vendor Location -->
    <div class="col-md-2 mb-2 sub_contracting {{$order -> issue_type === 'Sub Contracting' ? '' : 'd-none'}}">
        <label class="form-label">Vendor Store<span class="text-danger">*</span></label>
        <select class="form-select disable_on_edit" name="vendor_store_id" id="vendor_store_id_input" oninput = "enableDisableQtButton();">
            <option value="{{$order -> to_sub_store_id}}" >{{$order -> to_sub_store ?-> name}}</option>
        </select>
    </div>


    <!-- Department -->
    <div class="col-md-2 mb-2 consumption consumption_dept{{$order -> issue_type === 'Consumption' && $order -> requester_type === 'Department' ? '' : 'd-none'}}" id="department_id_header">
        <label class="form-label">Department</label>
        <select class="form-select disable_on_edit" name="department_id" id="department_id_input">
            @foreach ($departments as $department)
                <option {{ $selectedDepartmentId == $department->id ? 'selected' : '' }} value="{{ $department->id }}">{{ $department->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Requester -->
    <div class="col-md-2 mb-2 consumption consumption_user {{$order -> issue_type === 'Consumption' && $order -> requester_type === 'User' ? '' : 'd-none'}}" id="user_id_header">
        <label class="form-label">Requester</label>
        <input type="text" class="form-control mw-100 ledgerselecct ui-autocomplete-input" id="user_id_dropdown" placeholder="Select" value = "{{$order -> requester ?-> name}}">
        <input type="hidden" value="{{$order -> user_id}}" name="user_id" id="user_id_input">
    </div>

</div>

                                    @endif

<!-- Buttons Row -->
<div class="row align-items-center" id="selection_section">
    <div class="col-auto action-button" id="mfg_order_selection">
        <input type="hidden" id="mo_header_pull" value ="{{ App\Helpers\ConstantHelper::MO_SERVICE_ALIAS }}">
        <button onclick="openHeaderPullModal();" disabled type="button" id="select_mfg_button" class="btn btn-outline-primary btn-sm mb-0">
            <i data-feather="plus-square"></i> MFG Order
        </button>
    </div>
    <div class="col-auto action-button" id="jo_selection">
        <input type="hidden" id="jo_header_pull" value ="{{ App\Helpers\ConstantHelper::JO_SERVICE_ALIAS }}">
        <button onclick="openHeaderPullModal('jo');" disabled type="button" id="select_jo_button" class="btn btn-outline-primary btn-sm mb-0">
            <i data-feather="plus-square"></i> Job Order
        </button>
    </div>
    <div class="col-auto action-button d-none" id="pwo_order_selection">
        <input type="hidden" id="pwo_header_pull" value ="{{ App\Helpers\ConstantHelper::PWO_SERVICE_ALIAS }}">
        <button onclick="openHeaderPullModal('pwo');" disabled type="button" id="select_pwo_button" class="btn btn-outline-primary btn-sm mb-0">
            <i data-feather="plus-square"></i> PWO
        </button>
    </div>
    <div class="col-auto action-button" id="pi_order_selection">
        <input type="hidden" id="pi_header_pull" value ="{{ App\Helpers\ConstantHelper::PI_SERVICE_ALIAS }}">
        <button onclick="openHeaderPullModal('pi');" disabled type="button" id="select_pi_button" class="btn btn-outline-primary btn-sm mb-0">
            <i data-feather="plus-square"></i> Purchase Indent
        </button>
    </div>
    <div class="col-auto action-button" id="pslip_order_selection">
        <input type="hidden" id="pslip_header_pull" value ="{{ App\Helpers\ConstantHelper::PRODUCTION_SLIP_SERVICE_ALIAS }}">
        <button onclick="openHeaderPullModal('pslip');" disabled type="button" id="select_pslip_button" class="btn btn-outline-primary btn-sm mb-0">
            <i data-feather="plus-square"></i> Production Slip
        </button>
    </div>
</div>

</div>

</div>
<div class="col-md-12 {{(isset($order) && count($order -> dynamic_fields)) > 0 ? '' : 'd-none'}}" id = "dynamic_fields_section">
    @if (isset($dynamicFieldsUi))
        {!! $dynamicFieldsUi !!}
    @endif
</div>


                            <div class="card">
								 <div class="card-body customernewsection-form">
                                            <div class="border-bottom mb-2 pb-25">
                                                     <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="newheader ">
                                                                <h4 class="card-title text-theme">Item Detail</h4>
                                                                <p class="card-text">Fill the details</p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 text-sm-end" id = "add_delete_item_section">
                                                            <a href="#" onclick = "deleteItemRows();" class="btn btn-sm btn-outline-danger me-50">
                                                                <i data-feather="x-circle"></i> Delete</a>
                                                            <a href="#" onclick = "addItemRow();" id = "add_item_section" style = "display:none;" class="btn btn-sm btn-outline-primary">
                                                                <i data-feather="plus"></i> Add Item</a>
                                                         </div>
                                                    </div>
                                             </div>

											<div class="row">
                                                 <div class="col-md-12">
                                                 <div class="table-responsive pomrnheadtffotsticky">
                                                         <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                            <thead>
                                                                 <tr>
                                                                    <th class="customernewsection-form">
                                                                        <div class="form-check form-check-primary custom-checkbox">
                                                                            <input type="checkbox" class="form-check-input" id="select_all_items_checkbox" oninput = "checkOrRecheckAllItems(this);">
                                                                            <label class="form-check-label" for="select_all_items_checkbox" ></label>
                                                                        </div>
                                                                    </th>
                                                                    <th width="150px">Item Code</th>
                                                                    <th width="240px">Item Name</th>
                                                                    <th>Attributes</th>
                                                                    <th>UOM</th>
                                                                    <th>Stock Type</th>
                                                                    <th class = "numeric-alignment">Qty</th>
                                                                    <th class = "numeric-alignment">Rate</th>
                                                                    <th class = "numeric-alignment">Value</th>
                                                                    <th>Action</th>
                                                                  </tr>
                                                                </thead>
                                                                <tbody class="mrntableselectexcel" id = "item_header">
                                                                @if (isset($order))
                                                                    @php
                                                                        $docType = $order -> document_type;
                                                                    @endphp
                                                                    @foreach ($order -> items as $orderItemIndex => $orderItem)
                                                                        <tr id = "item_row_{{$orderItemIndex}}" class = "item_header_rows" onclick = "onItemClick('{{$orderItemIndex}}');" data-detail-id = "{{$orderItem -> id}}" data-id = "{{$orderItem -> id}}">
                                                                        <input type = 'hidden' name = "mi_item_id[]" value = "{{$orderItem -> id}}" {{$orderItem -> is_editable ? '' : 'readonly'}}>
                                                                         <td class="customernewsection-form">
                                                                            <div class="form-check form-check-primary custom-checkbox">
                                                                                <input type="checkbox" class="form-check-input item_row_checks" id="item_checkbox_{{$orderItemIndex}}" del-index = "{{$orderItemIndex}}">
                                                                                <label class="form-check-label" for="item_checkbox_{{$orderItemIndex}}"></label>
                                                                            </div>
                                                                        </td>
                                                                         <td class="poprod-decpt">
                                                                            <input type="text" id = "items_dropdown_{{$orderItemIndex}}" name="item_code[{{$orderItemIndex}}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input {{$orderItem -> is_editable ? '' : 'restrict'}}" autocomplete="off" data-name="{{$orderItem -> item ?-> item_name}}" data-code="{{$orderItem -> item ?-> item_code}}" data-id="{{$orderItem -> item ?-> id}}" hsn_code = "{{$orderItem -> item ?-> hsn ?-> code}}" item-name = "{{$orderItem -> item ?-> item_name}}" specs = "{{$orderItem -> item ?-> specifications}}" attribute-array = "{{$orderItem -> item_attributes_array()}}"  value = "{{$orderItem -> item ?-> item_code}}" {{$orderItem -> is_editable ? '' : 'readonly'}} item-location = "[]">
                                                                            <input type = "hidden" name = "item_id[]" id = "items_dropdown_{{$orderItemIndex}}_value" value = "{{$orderItem -> item_id}}"></input>
                                                                            @if ($orderItem -> mo_item_id)
                                                                                <input type = "hidden" name = "mo_item_id[{{$orderItemIndex}}]" id = "mo_id_{{$orderItemIndex}}" value = "{{$orderItem -> mo_item_id}}"></input>
                                                                            @endif
                                                                            @if ($orderItem -> pwo_item_id)
                                                                                <input type = "hidden" name = "pwo_item_id[{{$orderItemIndex}}]" id = "pwo_id_{{$orderItemIndex}}" value = "{{$orderItem -> pwo_item_id}}"></input>
                                                                            @endif
                                                                        </td>
                                                                        <td class="poprod-decpt">
                                                                            <input type="text" id = "items_name_{{$orderItemIndex}}" class="form-control mw-100"   value = "{{$orderItem -> item ?-> item_name}}" name = "item_name[{{$orderItemIndex}}]" readonly>
                                                                        </td>
                                                                        <td class="poprod-decpt" id='attribute_section_{{$orderItemIndex}}'>
                                                                            <button id = "attribute_button_{{$orderItemIndex}}" {{count($orderItem -> item_attributes_array()) > 0 ? '' : 'disabled'}} type = "button" data-bs-toggle="modal" onclick = "setItemAttributes('items_dropdown_{{$orderItemIndex}}', '{{$orderItemIndex}}', {{ json_encode(!$orderItem->is_editable) }});" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                                                                            <input type = "hidden" name = "attribute_value_{{$orderItemIndex}}" />
                                                                         </td>
                                                                        <td>
                                                                            <select class="form-select" name = "uom_id[]" id = "uom_dropdown_{{$orderItemIndex}}">
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <select class="form-select" name = "stock_type[]" id = "stock_type_{{$orderItemIndex}}" oninput = "getStoresData({{$orderItemIndex}})">
                                                                                @foreach ($stockTypes as $stockType)
                                                                                    <option value = "{{$stockType -> value}}" {{$orderItem -> stock_type === $stockType -> value ? 'selected' : ''}} >{{$stockType -> label}}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        @if ($orderItem -> stock_type === 'W' && isset($orderItem->wip_station_id))
                                                                            <input type = 'hidden' name = 'wip_station_id[{{$orderItemIndex}}]' value = '{{$orderItem->wip_station_id}}' id = "wip_station_id_{{$orderItemIndex}}"/>
                                                                        @endif
                                                                        <input type="hidden" name = "item_sub_store_from[{{$orderItemIndex}}]" id = "item_sub_store_from_{{$orderItemIndex}}" value = "{{$orderItem -> from_sub_store_id}}"/>
                                                                        <input type="hidden" name = "item_sub_store_to[{{$orderItemIndex}}]" id = "item_sub_store_to_{{$orderItemIndex}}" value = "{{$orderItem -> to_sub_store_id}}"/>

                                                                        <input type="hidden" value = "{{$order -> from_station_id}}" name = "item_station_from[{{$orderItemIndex}}]" id = "item_station_from_{{$orderItemIndex}}" />
                                                                        <input type="hidden" value = "{{$order -> to_station_id}}" name = "item_station_to[{{$orderItemIndex}}]" id = "item_station_to_{{$orderItemIndex}}" />

                                                                        <input type = "hidden" value = "{{$orderItem -> to_store_id}}" name = "item_store_to[{{$orderItemIndex}}]" />
                                                                        <input type = "hidden" value = "{{$orderItem -> from_store_id}}" name = "item_store_from[{{$orderItemIndex}}]" />


                                                                        <td><input type="text" id = "item_qty_{{$orderItemIndex}}" value = "{{$orderItem -> issue_qty}}" name = "item_qty[{{$orderItemIndex}}]" oninput = "changeItemQty(this, {{$orderItemIndex}});" class="form-control mw-100 text-end decimal-6" onblur = "setFormattedNumericValue(this);" max = "{{$orderItem -> max_qty_attribute}}"/></td>
                                                                        <td><input type="text" id = "item_rate_{{$orderItemIndex}}" value = "{{$orderItem -> rate}}" name = "item_rate[{{$orderItemIndex}}]" class="form-control mw-100 text-end decimal-6" onblur = "setFormattedNumericValue(this);" oninput = "changeItemRate(this, {{$orderItemIndex}});"/></td>
                                                                        <td><input type="text" id = "item_value_{{$orderItemIndex}}" value = "{{$orderItem -> total_item_amount}}" readonly class="form-control mw-100 text-end item_values_input" /></td>
                                                                        <td>
                                                                        <div class="d-flex">
                                                                                <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick = "setItemRemarks('item_remarks_{{$orderItemIndex}}');">
                                                                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span></div>
                                                                            </div>
                                                                        <input type = "hidden" id = "item_remarks_{{$orderItemIndex}}" name = "item_remarks[{{$orderItemIndex}}]" />
                                                                        </td>
                                                                      </tr>
                                                                    @endforeach
                                                                @else
                                                                @endif
                                                             </tbody>
                                                             <tfoot>
                                                                 <tr class="totalsubheadpodetail">
                                                                    <td colspan="9"></td>
                                                                </tr>

                                                                 <tr valign="top">
                                                                    <td id = "item_details_td" colspan="12" rowspan="10">
                                                                        <table class="table border">
                                                                            <tr>
                                                                                <td class="p-0">
                                                                                    <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="poprod-decpt">
                                                                                    <div id ="current_item_cat_hsn">

                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                            <tr id = "current_item_specs_row">
                                                                                <td class="poprod-decpt">
                                                                                    <div id ="current_item_specs">

                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                            <tr id = "current_item_attribute_row">
                                                                                <td class="poprod-decpt">
                                                                                    <div id ="current_item_attributes">

                                                                                    </div>
                                                                                </td>
                                                                            </tr>
                                                                            <tr id = "current_item_stocks_row">
                                                                                <td class="poprod-decpt">
                                                                                    <div id ="current_item_stocks">

                                                                                    </div>
                                                                                </td>
                                                                            </tr>

                                                                            <tr id = "current_item_inventory">
                                                                                <td class="poprod-decpt">
                                                                                    <div id ="current_item_inventory_details">

                                                                                    </div>
                                                                                </td>
                                                                            </tr>

                                                                            <tr id = "current_item_lot_no_row">
                                                                                <td class="poprod-decpt">
                                                                                    <div id ="current_item_lot_no">

                                                                                    </div>
                                                                                 </td>
                                                                            </tr>
                                                                            <tr id = "current_item_so_no_row">
                                                                                <td class="poprod-decpt">
                                                                                    <div id ="current_item_so_no">

                                                                                    </div>
                                                                                 </td>
                                                                            </tr>


                                                                            <tr id = "current_item_qt_no_row">
                                                                                <td class="poprod-decpt">
                                                                                    <div id ="current_item_qt_no">

                                                                                    </div>
                                                                                </td>
                                                                            </tr>

                                                                            <tr id = "current_item_store_location_row">
                                                                                <td class="poprod-decpt">
                                                                                    <div id ="current_item_store_location">

                                                                                    </div>
                                                                                </td>
                                                                            </tr>

                                                                            <tr id = "current_item_description_row">
                                                                                <td class="poprod-decpt">
                                                                                    <span class="badge rounded-pill badge-light-secondary"><strong>Remarks</strong>: <span style = "text-wrap:auto;" id = "current_item_description"></span></span>
                                                                                 </td>
                                                                            </tr>

                                                                            <tr id = "current_item_land_lease_agreement_row">
                                                                                <td class="poprod-decpt">
                                                                                    <div id ="current_item_land_lease_agreement">

                                                                                    </div>
                                                                                 </td>
                                                                            </tr>
                                                                        </table>
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
                                                                    <input type="file" class="form-control" name = "attachments[]" onchange = "addFiles(this,'main_order_file_preview')" max_file_count = "{{isset($maxFileCount) ? $maxFileCount : 10}}" multiple >
                                                                    <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                                                                </div>
                                                            </div>
                                                            <div class = "col-md-6" style = "margin-top:19px;">
                                                                <div class = "row" id = "main_order_file_preview">
                                                                </div>
                                                            </div>
                                                            </div>
                                                     </div>
                                                        <div class="col-md-12">
                                                            <div class="mb-1">
                                                                <label class="form-label">Final Remarks</label>
                                                                <textarea type="text" rows="4" class="form-control" placeholder="Enter Remarks here..." name = "final_remarks">{{isset($order) ? $order -> remarks : '' }}</textarea>
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

    <div class="modal fade" id="Remarks" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog  modal-dialog-centered" >
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-2 mx-50 pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">Add/Edit Remarks</h1>
					<p class="text-center">Enter the details below.</p>
                     <div class="row mt-2">
						<div class="col-md-12 mb-1">
							<label class="form-label">Remarks</label>
							<textarea class="form-control" current-item = "item_remarks_0" onchange = "changeItemRemarks(this);" id ="current_item_remarks_input" placeholder="Enter Remarks"></textarea>
						</div>
                    </div>
				</div>
				<div class="modal-footer justify-content-center">
						<button type="button" class="btn btn-outline-secondary me-1" onclick="closeModal('Remarks');">Cancel</button>
					<button type="button" class="btn btn-primary" onclick="closeModal('Remarks');">Submit</button>
				</div>
			</div>
		</div>
	</div>

    <div class="modal fade" id="attribute" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog  modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-2 mx-50 pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">Select Attribute</h1>
					<p class="text-center">Enter the details below.</p>

					<div class="table-responsive-md customernewsection-form">
								<table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail" id = "attributes_table_modal" item-index = "">
									<thead>
										 <tr>
											<th>Attribute Name</th>
											<th>Attribute Value</th>
										  </tr>
										</thead>
										<tbody id = "attribute_table">

									   </tbody>


								</table>
							</div>
				</div>

				<div class="modal-footer justify-content-center">
						<button type="button" class="btn btn-outline-secondary me-1" onclick = "closeModal('attribute');">Cancel</button>
					    <button type="button" class="btn btn-primary" onclick = "submitAttr('attribute');">Select</button>
				</div>
			</div>
		</div>
	</div>

    <div class="modal fade" id="amendConfirmPopup" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <div>
               <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Amend
               Material Issue
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
            <button type="button" class="btn btn-outline-secondary me-1" onclick = "closeModal('amendConfirmPopup');">Cancel</button>
            <button type="button" class="btn btn-primary" onclick = "submitAmend();">Submit</button>
         </div>
      </div>
   </div>
</div>
</form>

<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form class="ajax-submit-2" method="POST" action="{{ route('document.approval.materialIssue') }}" data-redirect="{{ $redirect_url }}" enctype='multipart/form-data'>
          @csrf
          <input type="hidden" name="action_type" id="action_type">
          <input type="hidden" name="id" value="{{isset($order) ? $order -> id : ''}}">
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
            <button type="reset" class="btn btn-outline-secondary me-1" onclick = "closeModal('approveModal');">Cancel</button>
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
              <p>Are you sure you want to <strong>Amend</strong> this <strong>Material Issue</strong>?</p>
              <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
              <button type="button" data-bs-dismiss="modal" onclick = "amendConfirm();" class="btn btn-primary">Confirm</button>
          </div>
      </div>
  </div>
</div>

@include('materialIssue.partials.mo_pull_modal');
@include('materialIssue.partials.jo_pull_modal');
@include('materialIssue.partials.pwo_pull_modal');
@include('materialIssue.partials.pi_pull_modal');
@include('materialIssue.partials.pslip_pull_modal');

@section('scripts')
<script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
<script>
    var currentfy = JSON.stringify({!! isset($order) ? $order : " " !!});
    let requesterTypeParam = "{{isset($order) ? $order -> requester_type : 'Department'}}";
    let redirect = "{{$redirect_url}}";
</script>
@include('PL.common-js-route',["order" => isset($order) ? $order : null, "route_prefix" => "material.issue", 'buttons' => isset($buttons) ? $buttons : null])
<script src="{{ asset("assets\\js\\modules\\pl\\common-script.js") }}"></script>
<script type="text/javascript" src="{{asset('assets/js/modules/pull-popup-datatable.js')}}"></script>
<script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        });

        function addItemRow()
        {
            let checkLoc = checkSameLocationCondition();
            if (!checkLoc) {
                Swal.fire({
                    title: 'Error!',
                    text: 'From and To Location cannot be same',
                    icon: 'error',
                });
                return;
            }
            var docType = $("#service_id_input").val();
            var invoiceToFollow = $("#service_id_input").val() == "yes";
            const tableElementBody = document.getElementById('item_header');
            const previousElements = document.getElementsByClassName('item_header_rows');
            const newIndex = previousElements.length ? previousElements.length : 0;
            if (newIndex == 0) {
                let addRow = $('#series_id_input').val() && $("#order_no_input").val() && $('#order_no_input').val() && $('#order_date_input').val() && $("#issue_type_input").val() &&
                $("#store_from_id_input").val() && $("#sub_store_from_id_input") && ($("#store_to_id_input").val() || ($("#vendor_id_input").val() && $("#vendor_store_id_input").val()) || $("#issue_type_input").val() == 'Consumption' || $("#issue_type_input").val() == 'Sub Location Transfer' );
                if (!addRow) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please fill all the header details first',
                    icon: 'error',
                });
                return;
                }
            } else {
                let addRow = $('#items_dropdown_' + (newIndex - 1)).val() &&  parseFloat($('#item_qty_' + (newIndex - 1)).val()) > 0;
                if (!addRow) {
                    Swal.fire({
                    title: 'Error!',
                    text: 'Please fill all the previous item details first',
                    icon: 'error',
                });
                return;
                }
            }
            const newItemRow = document.createElement('tr');
            newItemRow.className = 'item_header_rows';
            newItemRow.id = "item_row_" + newIndex;
            newItemRow.onclick = function () {
                onItemClick(newIndex);
            };
            const selectedUserId = $("#user_id_input").val() ? $("#user_id_input").val() : '';
            const selectedDeptId = $("#department_id_input").val() ? $("#department_id_input").val() : '';

            const storeIdTo = $("#store_to_id_input").val() ? $("#store_to_id_input").val() : '';
            const storeIdFrom = $("#store_from_id_input").val() ? $("#store_from_id_input").val() : '';

            const subStoreFromId = $("#sub_store_from_id_input").val() ? $("#sub_store_from_id_input").val() : '';
            const subStoreToId = $("#sub_store_to_id_input").val() ? $("#sub_store_to_id_input").val() : '';

            const stationFromId = $("#station_from_id_input").val() ? $("#station_from_id_input").val() : '';
            const stationToId = $("#station_to_id_input").val() ? $("#station_to_id_input").val() : '';

            newItemRow.innerHTML = `
            <tr id = "item_row_${newIndex}">
                <td class="customernewsection-form">
                   <div class="form-check form-check-primary custom-checkbox">
                       <input type="checkbox" class="form-check-input item_row_checks" id="item_row_check_${newIndex}" del-index = "${newIndex}">
                       <label class="form-check-label" for="Email"></label>
                   </div>
               </td>
                <td class="poprod-decpt">
                   <input type="text" id = "items_dropdown_${newIndex}" name="item_code[${newIndex}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" autocomplete="off" data-name="" data-code="" data-id="" hsn_code = "" item_name = "" attribute-array = "[]" specs = "[]" item-locations = "[]">
                   <input type = "hidden" name = "item_id[]" id = "items_dropdown_${newIndex}_value"></input>
               </td>

               <td class="poprod-decpt">
                    <input type="text" id = "items_name_${newIndex}" name = "item_name[${newIndex}]" class="form-control mw-100"   value = "" readonly>
                </td>
               <td class="poprod-decpt" id='attribute_section_${newIndex}'>
                   <button id = "attribute_button_${newIndex}" type = "button" data-bs-toggle="modal" onclick = "setItemAttributes('items_dropdown_${newIndex}', ${newIndex});" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                   <input type = "hidden" name = "attribute_value_${newIndex}" />
                </td>
               <td>
                   <select class="form-select" name = "uom_id[]" id = "uom_dropdown_${newIndex}">
                   </select>
               </td>
               <td>
                    <select class="form-select" name = "stock_type[]" id = "stock_type_${newIndex}" oninput = "getStoresData(${newIndex})">
                        @foreach ($stockTypes as $stockType)
                            <option value = "{{$stockType -> value}}" >{{$stockType -> label}}</option>
                        @endforeach
                    </select>
                </td>
               <input type = "hidden" name = "item_sub_store_from[${newIndex}]" id = "item_sub_store_from_${newIndex}" value = "${subStoreFromId}" />
               <input type = "hidden" name = "item_sub_store_to[${newIndex}]" id = "item_sub_store_from_${newIndex}" value = "${subStoreToId}" />


                <input type = "hidden" value = "${storeIdTo}" name = "item_store_to[${newIndex}]" />
                <input type = "hidden" value = "${storeIdFrom}" name = "item_store_from[${newIndex}]" />

                <input type="hidden" value = "${stationFromId}" name = "item_station_from[${newIndex}]" id = "item_station_from_${newIndex}" />
                <input type="hidden" value = "${stationToId}" name = "item_station_to[${newIndex}]" id = "item_station_to_${newIndex}" />

                <input type = "hidden" value = "${selectedDeptId}" name = "item_department_id[${newIndex}]" id = "item_user_id_input_${newIndex}" />
                <input type = "hidden" value = "${selectedUserId}" name = "item_user_id[${newIndex}]" id = "item_department_id_input_${newIndex}" />

               <td><input type="text" id = "item_qty_${newIndex}" name = "item_qty[${newIndex}]" oninput = "changeItemQty(this, ${newIndex});" class="form-control mw-100 text-end decimal-6" onblur = "setFormattedNumericValue(this);"/></td>
               <td><input type="text" id = "item_rate_${newIndex}" name = "item_rate[]" class="form-control mw-100 text-end decimal-6" onblur = "setFormattedNumericValue(this);" oninput = "changeItemRate(this, ${newIndex});"/></td>
               <td><input type="text" id = "item_value_${newIndex}" readonly class="form-control mw-100 text-end item_values_input" /></td>
               <td>
               <div class="d-flex">
                    <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick = "setItemRemarks('item_remarks_${newIndex}');">
                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span></div>
                </div>
               <input type = "hidden" id = "item_remarks_${newIndex}" name = "item_remarks[${newIndex}]" />
               </td>

             </tr>
            `;
            tableElementBody.appendChild(newItemRow);
            initializeAutocomplete1("items_dropdown_" + newIndex, newIndex);
            renderIcons();
            disableHeader();

            const qtyInput = document.getElementById('item_qty_' + newIndex);

            const itemCodeInput = document.getElementById('items_dropdown_' + newIndex);
            const uomCodeInput = document.getElementById('uom_dropdown_' + newIndex);
            const storeCodeInput = document.getElementById('item_store_from_' + newIndex);
            itemCodeInput.addEventListener('input', function() {
                checkStockData(newIndex);
            });
            uomCodeInput.addEventListener('input', function() {
                checkStockData(newIndex);
            });
            qtyInput.addEventListener('input', function() {
                checkStockData(newIndex);
            });

        }

        function deleteItemRows()
        {
            var deletedItemIds = JSON.parse(localStorage.getItem('deletedItemIds') ? localStorage.getItem('deletedItemIds') : '[]');
            const allRowsCheck = document.getElementsByClassName('item_row_checks');
            let deleteableElementsId = [];
            for (let index = allRowsCheck.length - 1; index >= 0; index--) {  // Loop in reverse order
                if (allRowsCheck[index].checked) {
                    const currentRowIndex = allRowsCheck[index].getAttribute('del-index');
                    const currentRow = document.getElementById('item_row_' + index);
                    if (currentRow) {
                        if (currentRow.getAttribute('data-id')) {
                            deletedItemIds.push(currentRow.getAttribute('data-id'));
                        }
                        deleteableElementsId.push('item_row_' + currentRowIndex);
                    }
                }
            }
            for (let index = 0; index < deleteableElementsId.length; index++) {
                document.getElementById(deleteableElementsId[index])?.remove();
            }
            localStorage.setItem('deletedItemIds', JSON.stringify(deletedItemIds));
            const allRowsNew = document.getElementsByClassName('item_row_checks');
            if (allRowsNew.length > 0) {
                disableHeader();
            } else {
                enableHeader();
            }

        }

        function changeItemValue(index) // Single Item Value
        {
            const currentElement = document.getElementById('item_value_' + index);
            if (currentElement) {
                const currentQty = document.getElementById('item_qty_' + index).value;
                const currentRate = document.getElementById('item_rate_' + index).value;
                currentElement.value = (parseFloat(currentRate ? currentRate : 0) * parseFloat(currentQty ? currentQty : 0)).toFixed(2);
            }
            getItemTax(index);
            changeItemTotal(index);
            changeAllItemsTotal();
            changeAllItemsTotalTotal();
        }

        function changeItemTotal(index) //Single Item Total
        {
            const currentElementValue = document.getElementById('item_value_' + index).value;
            const currentElementDiscount = document.getElementById('item_discount_' + index).value;
            const newItemTotal = (parseFloat(currentElementValue ? currentElementValue : 0) - parseFloat(currentElementDiscount ? currentElementDiscount : 0)).toFixed(2);
            document.getElementById('item_total_' + index).value = newItemTotal;

        }

        function changeAllItemsValue()
        {

        }



        function checkStockData(itemRowId)
        {
            let itemAttributes = JSON.parse(document.getElementById(`items_dropdown_${itemRowId}`).getAttribute('attribute-array'));
                    let selectedItemAttr = [];
                    if (itemAttributes && itemAttributes.length > 0) {
                        itemAttributes.forEach(element => {
                        element.values_data.forEach(subElement => {
                            if (subElement.selected) {
                                selectedItemAttr.push(subElement.id);
                            }
                        });
                    });
                    }
            let currentItemId = document.getElementById('items_dropdown_'+ itemRowId + '_value').value;
            let currentUomId = document.getElementById('uom_dropdown_' + itemRowId).value;
            if (currentItemId && currentUomId)
            {
                $.ajax({
                    url: "{{route('get_item_inventory_details')}}",
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        quantity: document.getElementById('item_qty_' + itemRowId).value,
                        item_id: currentItemId,
                        uom_id : currentUomId,
                        selectedAttr : selectedItemAttr,
                        store_id: $("#store_from_id_input").val(),
                        sub_store_id : $("#item_sub_store_from_" + itemRowId).val()
                    },
                    success: function(data) {
                        var inputQtyBox = document.getElementById('item_qty_' + itemRowId);
                        var actualQty = inputQtyBox.value;
                        console.log(actualQty, "ACTUAL");
                        inputQtyBox.setAttribute('max-stock',data.stocks.confirmedStockAltUom);
                        if (inputQtyBox.getAttribute('max-stock')) {
                            var maxStock = parseFloat(inputQtyBox.getAttribute('max-stock') ? inputQtyBox.getAttribute('max-stock') : 0);
                            if (maxStock <= 0) {
                                inputQtyBox.value = 0;
                                inputQtyBox.readOnly = true;
                            } else {
                                if (actualQty > maxStock) {
                                    inputQtyBox.value = maxStock;
                                    inputQtyBox.readOnly  = false;
                                } else {
                                    inputQtyBox.readOnly  = false;
                                }
                            }
                        changeItemQtyMi(inputQtyBox, itemRowId);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching customer data:', xhr.responseText);
                    }
                });
            }
        }


        function openModal(id)
        {
            $('#' + id).modal('show');
        }

        function closeModal(id)
        {
            $('#' + id).modal('hide');
        }

        function submitForm(status) {
            // Create FormData object
            enableHeader();
        }

        function initializeAutocomplete1(selector, index) {
            $("#" + selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type:'material_issue_items',
                            customer_id : null,
                            header_book_id : $("#series_id_input").val()
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: `${item.item_name} (${item.item_code})`,
                                    code: item.item_code || '',
                                    item_id: item.id,
                                    uom : item.uom,
                                    alternateUoms : item.alternate_u_o_ms,
                                    specifications : item.specifications
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    var $input = $(this);
                    var itemCode = ui.item.code;
                    var itemName = ui.item.value;
                    var itemId = ui.item.item_id;

                    $input.attr('data-name', itemName);
                    $input.attr('data-code', itemCode);
                    $input.attr('data-id', itemId);
                    $input.attr('specs', JSON.stringify(ui.item.specifications));
                    $input.val(itemCode);

                    const uomDropdown = document.getElementById('uom_dropdown_' + index);
                    var uomInnerHTML = ``;
                    if (uomDropdown) {
                        uomInnerHTML += `<option selected value = '${ui.item.uom.id}'>${ui.item.uom.alias}</option>`;
                    }
                    if (ui.item.alternateUoms && ui.item.alternateUoms.length > 0) {
                        var selected = false;
                        ui.item.alternateUoms.forEach((saleUom) => {
                            uomInnerHTML += `<option value = '${saleUom.uom?.id}' ${selected == false ? "selected" : ""}>${saleUom.uom?.alias}</option>`;
                        });
                    }
                    uomDropdown.innerHTML = uomInnerHTML;
                    itemOnChange(selector, index, '/item/attributes/');
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        // $('#itemId').val('');
                        $(this).attr('data-name', '');
                        $(this).attr('data-code', '');
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
    }

    function initializeAutocompleteAutoUser(selector) {
            $("#" + selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type:'all_user_list',
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: `${item.name}`,
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    var $input = $(this);
                    $input.val(ui.item.label);
                    $("#user_id_input").val(ui.item.id);
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#user_id_input").val("");
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
    }


    function implementBookParameters(paramData)
    {
        var selectedRefFromServiceOption = paramData.reference_from_service;
        var selectedBackDateOption = paramData.back_date_allowed;
        var selectedFutureDateOption = paramData.future_date_allowed;
        var invoiceToFollowParam = paramData?.invoice_to_follow;
        var issueTypeParameters = paramData?.issue_type;

        // Reference From
        if (selectedRefFromServiceOption) {
            var selectVal = selectedRefFromServiceOption;
            if (selectVal && selectVal.length > 0) {
                selectVal.forEach(selectSingleVal => {
                    if (selectSingleVal == 'mo') {
                        var selectionSectionElement = document.getElementById('selection_section');
                        if (selectionSectionElement) {
                            selectionSectionElement.style.display = "";
                        }
                        var selectionPopupElement = document.getElementById('mfg_order_selection');
                        if (selectionPopupElement)
                        {
                            selectionPopupElement.style.display = ""
                        }
                    }
                    if (selectSingleVal == 'jo') {
                        var selectionSectionElement = document.getElementById('selection_section');
                        if (selectionSectionElement) {
                            selectionSectionElement.style.display = "";
                        }
                        var selectionPopupElement = document.getElementById('jo_selection');
                        if (selectionPopupElement)
                        {
                            selectionPopupElement.style.display = ""
                        }
                    }
                    if (selectSingleVal == 'pwo') {
                        var selectionSectionElement = document.getElementById('selection_section');
                        if (selectionSectionElement) {
                            selectionSectionElement.style.display = "";
                        }
                        var selectionPopupElement = document.getElementById('pwo_order_selection');
                        if (selectionPopupElement)
                        {
                            selectionPopupElement.style.display = ""
                        }
                    }
                    if (selectSingleVal == 'purchase-indent') {
                        var selectionSectionElement = document.getElementById('selection_section');
                        if (selectionSectionElement) {
                            selectionSectionElement.style.display = "";
                        }
                        var selectionPopupElement = document.getElementById('pi_order_selection');
                        if (selectionPopupElement)
                        {
                            selectionPopupElement.style.display = ""
                        }
                    }
                    if (selectSingleVal == 'pslip') {
                        var selectionSectionElement = document.getElementById('selection_section');
                        if (selectionSectionElement) {
                            selectionSectionElement.style.display = "";
                        }
                        var selectionPopupElement = document.getElementById('pslip_order_selection');
                        if (selectionPopupElement)
                        {
                            selectionPopupElement.style.display = ""
                        }
                    }
                    if (selectSingleVal == 'd') {
                        document.getElementById('add_item_section').style.display = "";
                    }
                });
            }
        }

        var backDateAllow = false;
        var futureDateAllow = false;

        //Back Date Allow
        if (selectedBackDateOption) {
            var selectVal = selectedBackDateOption;
            if (selectVal && selectVal.length > 0) {
                if (selectVal[0] == "yes") {
                    backDateAllow = true;
                } else {
                    backDateAllow = false;
                }
            }
        }

        //Future Date Allow
        if (selectedFutureDateOption) {
            var selectVal = selectedFutureDateOption;
            if (selectVal && selectVal.length > 0) {
                if (selectVal[0] == "yes") {
                    futureDateAllow = true;
                } else {
                    futureDateAllow = false;
                }
            }
        }

        if (backDateAllow && futureDateAllow) { // Allow both ways (future and past)
            $("#order_date_input").attr('max',"{{ $current_financial_year['end_date'] }}");
            $("#order_date_input").attr('min',"{{ $current_financial_year['start_date'] }}");
            $("#order_date_input").off('input');
        }
        if (backDateAllow && !futureDateAllow) { // Allow only back date
            $("#order_date_input").removeAttr('min');
            $("#order_date_input").attr('max', "{{ min($current_financial_year['end_date'],Carbon\Carbon::now()) }}");
            $("#order_date_input").off('input');
            $('#order_date_input').on('input', function() {
                restrictFutureDates(this);
            });
        }
        if (!backDateAllow && futureDateAllow) { // Allow only future date
            $("#order_date_input").removeAttr('max');
            $("#order_date_input").attr('min', "{{ max($current_financial_year['start_date'],Carbon\Carbon::now()) }}");
            $("#order_date_input").off('input');
            $('#order_date_input').on('input', function() {
                restrictPastDates(this);
            });
        }

        //Issue Type
        if (issueTypeParameters && issueTypeParameters.length > 0) {
            const issueTypeInput = document.getElementById('issue_type_input');
            let selectedVal = "{{isset($order) ? $order -> issue_type : ''}}";

            if (issueTypeInput) {
                var issueTypeHtml = ``;
                var firstIssueType = null;
                issueTypeParameters.forEach((issueType, issueTypeIndex) => {
                    if (issueTypeIndex == 0) {
                        firstIssueType = issueType;
                    }
                    issueTypeHtml += `<option value = '${issueType}' ${selectedVal == issueType ? 'selected' : ''}> ${issueType} </option>`
                });
                if ("{{isset($order)}}") {
                    firstIssueType = "{{isset($order) ? $order -> issue_type : ''}}";
                }
                issueTypeInput.innerHTML = issueTypeHtml;
                requesterTypeParam = paramData?.requester_type?.[0];
                $("#requester_type_input").val(requesterTypeParam);
                // $("#issue_type_input").val(firstIssueType).trigger('input');
                let editCase = "{{isset($order) ? 'false' : 'true'}}";
                if (editCase != 'false')  {
                    onIssueTypeChange(document.getElementById('issue_type_input'), editCase == 'false' ? false : true);
                }
            }
        }
        requesterTypeParam = paramData?.requester_type?.[0];
        $("#requester_type_input").val(requesterTypeParam);
    }

    function enableDisableQtButton()
    {
        const bookId = document.getElementById('series_id_input').value;
        const bookCode = document.getElementById('book_code_input').value;
        const documentDate = document.getElementById('order_date_input').value;
        const fromLocation = document.getElementById('store_from_id_input').value;
        const fromLocationStore = document.getElementById('sub_store_from_id_input').value;
        let issueType = $("#issue_type_input").val();
        const otherField = (($("#vendor_id_input").val() && $("#vendor_store_id_input").val()) || ($("#issue_type_input").val() == 'Consumption') || ($("#issue_type_input").val() == 'Location Transfer') || ($("#issue_type_input").val() == 'Sub Location Transfer'));
        if (bookId && bookCode && documentDate && fromLocation && fromLocationStore && otherField) {

            let piButton = document.getElementById('select_pi_button');
            if (piButton) {
                if (issueType == "Sub Contracting" || issueType == "Job Work") {
                    piButton.disabled = true;
                } else {
                    piButton.disabled = false;
                }
            }
            let pslipButton = document.getElementById('select_pslip_button');
            if (pslipButton) {
                if (issueType == "Sub Contracting" || issueType == "Job Work") {
                    pslipButton.disabled = true;
                } else {
                    pslipButton.disabled = false;
                }
            }
            let leaseButton = document.getElementById('select_pwo_button');
            if (leaseButton) {
                leaseButton.disabled = false;
            }
            let orderButton = document.getElementById('select_mfg_button');
            if (orderButton) {
                if (issueType == "Sub Contracting" || issueType == "Job Work") {
                    orderButton.disabled = true;
                } else {
                    orderButton.disabled = false;
                }
            }
            let joButton = document.getElementById('select_jo_button');
            if (joButton) {
                if (issueType == "Sub Contracting" || issueType == "Job Work") {
                    joButton.disabled = false;
                } else {
                    joButton.disabled = true;
                }
            }
        // } else {
        //     let siButton = document.getElementById('select_si_button');
        //     if (siButton) {
        //         siButton.disabled = true;
        //     }
        //     let dnButton = document.getElementById('select_dn_button');
        //     if (dnButton) {
        //         dnButton.disabled = true;
        //     }
        //     let leaseButton = document.getElementById('select_lease_button');
        //     if (leaseButton) {
        //         leaseButton.disabled = true;
        //     }
        }
    }

    let openPullType = 'mo';

    function openHeaderPullModal(type = 'mo')
    {
        let checkLoc = checkSameLocationCondition();
        if (!checkLoc) {
            Swal.fire({
                title: 'Error!',
                text: 'From and To Location cannot be same',
                icon: 'error',
            });
            return;
        }
        if (type === 'mo') {
            openPullType = "mo";
            $("#rescdule").modal('show');
        } else if (type === 'pwo') {
            openPullType = "pwo";
            $("#rescdulePwo").modal('show');
        } else if (type === 'jo') {
            openPullType = "jo";
            $("#rescduleJo").modal('show');
        }else if (type === 'pslip') {
            openPullType = "pslip";
            $("#rescdulePslip").modal('show');
        }else {
            openPullType = "pi";
            $("#rescdulePi").modal('show');
        }
        // document.getElementById('qts_data_table').innerHTML = '';
        // document.getElementById('qts_data_table_pwo').innerHTML = '';
            initializeAutocompleteQt(`book_code_input_${type}`, `book_id_${type}_val`, `book_${type}`, "book_code", "book_name",type);
            initializeAutocompleteQt(`document_no_input_${type}`, `document_id_${type}_val`, `${type}_document`, "book_code", "document_number",type);
            initializeAutocompleteQt(`so_no_input_${type}`, `so_id_${type}_val`, `sale_order_document_${type}`, "book_code" ,"document_number",type);
            initializeAutocompleteQt(`item_name_input_${type}`, `item_id_${type}_val`, `inventory_items`, "item_code", "item_name",type);
            initializeAutocompleteQt(`location_code_input_${type}`, `location_id_${type}_val`, `location`, "store_name","",type);
            initializeAutocompleteQt(`sub_location_code_input_${type}`, `sub_location_id_${type}_val`, `sub_store`, "name","",type);
            initializeAutocompleteQt(`department_code_input_${type}`, `department_id_${type}_val`, `department_${type}`, "name","",type);
        getOrders(type);
    }

    // function getOrders(type = "mo")
    // {
    //     var qtsHTML = ``;
    //     let departmentOrStoreKey = 'store_location_code';
    //     let targetTable = document.getElementById('qts_data_table');
    //     let requesterHTML = ``;
    //     let stationHTML = ``;
    //     let soNoHTML = ``;
    //     let subStoreHTML = ``;
    //     if (type == 'pwo') {
    //         targetTable = document.getElementById('qts_data_table_pwo');
    //     } else if (type == "pi") {
    //         departmentOrStoreKey = 'department_code';
    //         targetTable = document.getElementById('qts_data_table_pi');
    //     }
    //     const location_id = $("#location_id_qt_val").val();
    //     const departmentId = $("#department_id_qt_val").val();
    //     const book_id = $(`#book_id_${type}_val`).val();
    //     const document_id = $(`#document_id_${type}_val`).val();
    //     const item_id = $(`#item_id_${type}_val`).val();
    //     const apiUrl = "{{route('material.issue.pull.items')}}";
    //     var selectedIds = [];

    //     var headerRows = document.getElementsByClassName("item_header_rows");
    //     for (let index = 0; index < headerRows.length; index++) {
    //         if (type == "mo") {
    //             var referedId = document.getElementById('mo_id_' + index).value;
    //         } else if (type == "pwo") {
    //             var referedId = document.getElementById('pwo_id_' + index).value;
    //         } else if (type == "pi") {
    //             var referedId = document.getElementById('pi_id_' + index).value;
    //         } else {
    //             var referedId = [];
    //         }
    //         selectedIds.push(referedId);
    //     }
    //     $.ajax({
    //         url: apiUrl,
    //         method: 'GET',
    //         dataType: 'json',
    //         data : {
    //             location_id : location_id,
    //             department_id : departmentId,
    //             book_id : book_id,
    //             document_id : document_id,
    //             item_id : item_id,
    //             doc_type : type,
    //             header_book_id : $("#series_id_input").val(),
    //             store_id: $("#store_to_id_input").val(),
    //             sub_store_id : $("#sub_store_to_id_input").val(),
    //             store_id_from: $("#store_from_id_input").val(),
    //             sub_store_id_from: $("#sub_store_from_id_input").val(),
    //             selected_ids : selectedIds,
    //             requester_type : $("#requester_type_input").val(),
    //             requester_department_id : $("#department_id_input").val(),
    //             requester_user_id : $("#user_id_input").val(),
    //             station_id : $("#station_to_id_input").val()
    //         },
    //         success: function(data) {
    //             if (Array.isArray(data.data) && data.data.length > 0) {
    //                     data.data.forEach((qt, qtIndex) => {
    //                         if (qt?.header?.requester_name) {
    //                             requesterHTML = `<td>${qt?.header?.requester_name}</td>`;
    //                         }
    //                         if (qt?.station_name || type == 'mo') {
    //                             stationHTML = `<td>${qt?.station_name ? qt?.station_name : ''}</td>`;
    //                         }
    //                         if (qt?.sub_store_code) {
    //                             subStoreHTML = `<td>${qt?.sub_store_code}</td>`;
    //                         }

    //                         soNoHTML = `<td>${qt?.so_no}</td>`;

    //                         var attributesHTML = ``;
    //                         qt.attributes.forEach(attribute => {
    //                             attributesHTML += `<span class="badge rounded-pill badge-light-primary" > ${attribute.attribute_name} : ${attribute.attribute_value} </span>`;
    //                         });
    //                         qtsHTML += `
    //                             <tr>
    //                                 <td>
    //                                     <div class="form-check form-check-inline me-0">
    //                                         <input ${qt?.avl_stock > 0 ? '' : 'disabled'} class="form-check-input pull_checkbox" type="checkbox" name="po_check" id="po_checkbox_${qtIndex}"  doc-id = "${qt?.header.id}" current-doc-id = "0" document-id = "${qt?.header?.id}" so-item-id = "${qt.id}" balance_qty = "${qt?.avl_stock}">
    //                                     </div>
    //                                 </td>
    //                                 <td>${qt?.header?.book_code}</td>
    //                                 <td>${qt?.header?.document_number}</td>
    //                                 <td>${qt?.header?.document_date}</td>
    //                                 <td>${qt?.[departmentOrStoreKey]}</td>
    //                                 ${subStoreHTML}
    //                                 ${requesterHTML}
    //                                 ${stationHTML}
    //                                 <td>${qt?.so_no ? qt?.so_no : ''}</td>
    //                                 <td>${qt?.item_code}</td>
    //                                 <td>${qt?.item_name}</td>
    //                                 <td>${attributesHTML}</td>
    //                                 <td>${qt?.uom?.name}</td>
    //                                 <td>${qt?.qty}</td>
    //                                 <td>${qt?.mi_balance_qty}</td>
    //                                 <td>${qt?.avl_stock}</td>
    //                             </tr>
    //                         `
    //                     });
    //             }
    //             targetTable.innerHTML = qtsHTML;
    //         },
    //         error: function(xhr) {
    //             console.error('Error fetching customer data:', xhr.responseText);
    //             targetTable.innerHTML = '';
    //         }
    //     });

    // }

    function showTableLoadingRow(tableSelector, colspan = 10, message = "Loading...") {
        const loadingRow = `
            <tr class="loading-row">
                <td colspan="${colspan}" class="text-center">
                    <div class="spinner-border text-primary" role="status" style="margin-right: 10px;"></div>
                    ${message}
                </td>
            </tr>`;
        $(tableSelector + ' tbody').fadeOut(150, function () {
            $(this).html(loadingRow).fadeIn(150);
        });
    }

    function getOrders(type = "mo") {
        let departmentOrStoreKey = 'store_location_code';
        let tableSelector = '#mo_orders_table';
        let docType = `#${type}_header_pull`;
        if (type === 'pwo') {
            tableSelector = '#pwo_orders_table';
        } else if (type === 'pi') {
            departmentOrStoreKey = 'department_code';
            tableSelector = '#pi_orders_table';
        } else if (type === 'pslip') {
            tableSelector = '#pslip_orders_table';
        } else if (type == "jo") {
            tableSelector = '#jo_orders_table';
        }

        // Build selected IDs
        const selectedIds = Array.from(document.getElementsByClassName("item_header_rows")).map((_, index) => {
            let idInput = document.getElementById(`${type}_id_${index}`);
            return idInput ? idInput.value : '';
        });

        const apiUrl = "{{ route('material.issue.pull.items') }}";

        const renderData = data => data || 'N/A';

        const columns = [
            {
                data: null,
                name: 'checkbox',
                orderable: false,
                searchable: false,
                render: (data, type, row) => {
                    let numericAvlStock = parseFloat(row.avl_stock.replace(/,/g, ''));
                    return `<div class="form-check form-check-inline me-0">
                        <input class="form-check-input pull_checkbox" type="checkbox"
                            ${numericAvlStock > 0 ? '' : 'disabled'}
                            doc-id="${row.id}"
                            current-doc-id="0"
                            document-id="${row.id}"
                            so-item-id="${row.id}"
                            balance_qty="${row.avl_stock}">
                    </div>`;
                }
            },
            { data: 'book_code', name: 'book_code', render: renderData, className: 'no-wrap' },
            { data: 'document_number', name: 'document_number', render: renderData, className: 'no-wrap' },
            { data: 'document_date', name: 'document_date', render: renderData, className: 'no-wrap' }
        ];

        // Conditional columns
        if (type === 'pi') {
            columns.push(
                { data: 'department_code', name: 'department_code', render: renderData, className: 'no-wrap' },
                { data: 'requester_name', name: 'requester_name', render: renderData, className: 'no-wrap' }
            );
        } else if (type === 'mo' || type == 'pslip') {
            columns.push(
                { data: 'store_location_code', name: 'store_location_code', render: renderData, className: 'no-wrap' },
                { data: 'sub_store_code', name: 'sub_store_code', render: renderData, className: 'no-wrap' },
                { data: 'station_name', name: 'station_name', render: renderData, className: 'no-wrap' }
            );
        } else if (type === 'jo') {
            columns.push(
                { data: 'store_location_code', name: 'store_location_code', render: renderData, className: 'no-wrap' }
            );
        }

        columns.push(
            { data: 'so_no', name: 'so_no', render: renderData, className: 'no-wrap' },
            { data: 'item_code', name: 'item_code', render: renderData, className: 'no-wrap' },
            {
                data: 'item_name',
                name: 'item_name',
                render: renderData => {
                    return `<div class="item-name-cell text-wrap" style="width: 150px; word-break: break-word">${renderData}</div>`;
                }
            },
            {
                data: 'attributes_array',
                name: 'attributes_array',
                render: attributes_array => {
                    if (!attributes_array) return '';
                    attributes_array = typeof attributes_array === 'string' ? JSON.parse(attributes_array) : attributes_array;
                    return `
                        <div class="item-name-wrapper" style="display: flex; flex-wrap: wrap; gap: 4px; width: 200px;">
                            ${attributes_array.map(attr =>
                                `<span class="badge wrap-if-too-long rounded-pill badge-light-primary" style="margin-bottom: 4px; flex: 0 0 auto;">
                                    ${attr.attribute_name} : ${attr.attribute_value}
                                </span>`).join('')}
                        </div>`;
                }
            },
            { data: 'uom.name', name: 'uom.name', render: renderData, className: 'no-wrap' },
            { data: 'qty', name: 'qty', render: renderData, className: 'no-wrap numeric-alignment' },
            { data: 'mi_balance_qty', name: 'mi_balance_qty', render: renderData, className: 'no-wrap numeric-alignment' },
            { data: 'avl_stock', name: 'avl_stock', render: renderData, className: 'no-wrap numeric-alignment' }
        );

        const filters = {
            location_id:`#location_id_${type}_val`,
            department_id:`#department_id_${type}_val`,
            book_id:`#book_id_${type}_val`,
            document_id:`#document_id_${type}_val`,
            so_id:`#so_id_${type}_val`,
            item_id:`#item_id_${type}_val`,
            doc_type: docType,
            mi_type: $("#issue_type_input"),
            header_book_id: "#series_id_input",
            store_id: "#store_to_id_input",
            sub_store_id: "#sub_store_to_id_input",
            store_id_from: "#store_from_id_input",
            sub_store_id_from: "#sub_store_from_id_input",
            selected_ids: selectedIds,
            requester_type: "#requester_type_input",
            requester_department_id: "#department_id_input",
            requester_user_id: "#user_id_input",
            station_id: "#station_to_id_input",
            vendor_id : $("#vendor_id_input"),
            pslip_pull_type : $("#pslip_issue_type")
        };

        // Destroy existing table if any
        if ($.fn.DataTable.isDataTable(tableSelector)) {
            $(tableSelector).DataTable().clear().destroy();
        }

        // Initialize DataTable
        initializeDataTable(
            tableSelector,
            apiUrl,
            columns,
            filters,
            "Order Items - " + type.toUpperCase(),
            [],
            [], // default order
            'landscape',
            'POST',
            false,
            false
        );
    }


    let current_doc_id = 0;

    function checkQuotation(element, message = '')
    {
        if (element.getAttribute('can-check-message')) {
            Swal.fire({
                title: 'Error!',
                text: element.getAttribute('can-check-message'),
                icon: 'error',
            });
            element.checked = false;
            return;
        }
        const docId = element.getAttribute('doc-id');
        if (current_doc_id != 0) {
            if (element.checked == true) {
                if (current_doc_id != docId) {
                    element.checked = false;
                }
            } else {
                const otherElementsSameDoc = document.getElementsByClassName('po_checkbox');
                let resetFlag = true;
                for (let index = 0; index < otherElementsSameDoc.length; index++) {
                    if (otherElementsSameDoc[index].getAttribute('doc-id') == current_doc_id && otherElementsSameDoc[index].checked) {
                        resetFlag = false;
                        break;
                    }
                }
                if (resetFlag) {
                    current_doc_id = 0;
                }
            }
        } else {
            current_doc_id = element.getAttribute('doc-id');
        }

    }

    function processOrder()
    {
        const allCheckBoxes = document.getElementsByClassName('pull_checkbox');
        const docType = $("#service_id_input").val();
        const apiUrl = "{{route('material.issue.process.items')}}";
        let docId = [];
        let soItemsId = [];
        let qties = [];
        let documentDetails = [];
        for (let index = 0; index < allCheckBoxes.length; index++) {
            if (allCheckBoxes[index].checked) {
                docId.push(allCheckBoxes[index].getAttribute('document-id'));
                soItemsId.push(allCheckBoxes[index].getAttribute('so-item-id'));
                qties.push(allCheckBoxes[index].getAttribute('balance_qty'));
                documentDetails.push({
                    'order_id' : allCheckBoxes[index].getAttribute('document-id'),
                    'quantity' : allCheckBoxes[index].getAttribute('balance_qty'),
                    'item_id' : allCheckBoxes[index].getAttribute('so-item-id')
                });
            }
        }
        if (docId && soItemsId.length > 0) {
            $.ajax({
                url: apiUrl,
                method: 'GET',
                dataType: 'json',
                data: {
                    order_id: docId,
                    quantities : qties,
                    items_id: soItemsId,
                    doc_type: openPullType,
                    document_details : JSON.stringify(documentDetails),
                    store_id : $("#store_from_id_input").val(),
                    sub_store_id : $("#sub_store_from_id_input").val(),
                    station_id : $("#station_from_id_input").val(),
                    mi_type : $("#issue_type_input").val(),
                    pslip_issue_type : $("#pslip_issue_type").val()
                },
                success: function(data) {
                    const currentOrders = data.data;
                    let currentOrderIndexVal = document.getElementsByClassName('item_header_rows').length;

                    currentOrders.forEach((currentOrder, currentOrderIndex) => {
                        if (currentOrder) { //Set all data
                            //set the location and sub store
                            if (!$("#store_to_id_input").val()) {
                                $("#store_to_id_input").val(currentOrder.store_id);
                            }
                            if (!$("#sub_store_to_id_input").val()) {
                                $("#sub_store_to_id_input").val(currentOrder?.sub_store_id);
                            }
                            if (!$("#station_to_id_input").val() && currentOrder?.station_id && currentOrder?.station_name) {
                                // Clear previous options if needed
                                $("#station_to_id_input").empty().append(
                                    $("<option>", {
                                        value: currentOrder?.station_id,
                                        text: currentOrder?.station_name
                                    })
                                );

                                // Now set the value
                                $("#station_to_id_input").val(currentOrder?.station_id);
                            }
                            //

                            const storeIdTo = $("#store_to_id_input").val() ? $("#store_to_id_input").val() : '';
                            const storeIdFrom = $("#store_from_id_input").val() ? $("#store_from_id_input").val() : '';

                            const subStoreFromId = $("#sub_store_from_id_input").val() ? $("#sub_store_from_id_input").val() : '';
                            const subStoreToId = $("#sub_store_to_id_input").val() ? $("#sub_store_to_id_input").val() : '';

                            const stationFromId = $("#station_from_id_input").val() ? $("#station_from_id_input").val() : '';
                            const stationToId = $("#station_to_id_input").val() ? $("#station_to_id_input").val() : '';


                        //Disable Header
                            disableHeader();
                            //Basic Details
                            const mainTableItem = document.getElementById('item_header');
                            //Remove previous items if any
                            // const allRowsCheck = document.getElementsByClassName('item_row_checks');
                            // for (let index = 0; index < allRowsCheck.length; index++) {
                            //     allRowsCheck[index].checked = true;
                            // }
                            // deleteItemRows();
                            if (true) {
                                currentOrder.items.forEach((item, itemIndex) => {
                                    let selectedStockType = 'R';
                                    if (openPullType == 'mo') {
                                        if (item?.rm_type && item?.rm_type === 'rm') {
                                            selectedStockType = 'R';
                                        } else if (item?.rm_type && item?.rm_type === 'sf') {
                                            selectedStockType = 'W';
                                        }
                                    }
                                    let pslipIssueTypeElement = ``;
                                    if (item?.pslip_issue_type) {
                                        pslipIssueTypeElement = `<input type = "hidden" value = "${item.pslip_issue_type}" name = "pslip_issue_type[${currentOrderIndexVal}]" />`;
                                    }
                                    // item.balance_qty = item.mi_balance_qty;
                                    if (Number(item.avl_stock) < Number(item.mi_balance_qty)){
                                        item.mi_balance_qty = item.avl_stock;
                                    }
                                    let itemIdKeyName = "mo_item_id";
                                    let itemIdKeyId = "mo_id";
                                    if (openPullType == "pwo") {
                                        itemIdKeyName = "pwo_item_id";
                                        itemIdKeyId = "pwo_id";
                                    }
                                    if (openPullType == "pslip") {
                                        itemIdKeyName = "pslip_item_id";
                                        itemIdKeyId = "pslip_id";
                                    }
                                    if (openPullType == "pi") {
                                        itemIdKeyName = "pi_item_id";
                                        itemIdKeyId = "pi_id";
                                    }
                                    if (openPullType == "jo") {
                                        if ($("#issue_type_input").val() == "Sub Contracting") {
                                            itemIdKeyName = "jo_item_id";
                                            itemIdKeyId = "jo_id";
                                        } else if ($("#issue_type_input").val() == "Job Work") {
                                            itemIdKeyName = "jo_product_id";
                                            itemIdKeyId = "jo_id";
                                        }
                                    }
                                    // var avl_qty = item.balance_qty;
                                    // item.balance_qty = avl_qty;
                                    // item.max_qty = avl_qty;
                                    const itemRemarks = item.remarks ? item.remarks : '';
                                    let amountMax = ``;


                                    let agreement_no = '';
                                    let lease_end_date = '';
                                    let due_date = '';
                                    let repayment_period = '';

                                    let land_parcel = '';
                                    let land_plots = '';

                                    let landLeasePullHtml = '';

                                    //Reference from labels
                                    var referenceLabelFields = ``;

                                    let stockTypes = @json($stockTypes);
                                    let stockTypeHTML = ``;
                                    stockTypes.forEach(stockType => {
                                        stockTypeHTML += `<option value = '${stockType.value}' ${selectedStockType == stockType.value ? 'selected' : ''}>${stockType.label}</option>`;
                                    });

                                    let wipStationIdHiddenInput = ``;
                                    if (openPullType == 'mo') {
                                        if (item?.station_id && item?.rm_type == 'sf') {
                                            wipStationIdHiddenInput = `<input type = 'hidden' name = 'wip_station_id[${currentOrderIndexVal}]' value = '${item.station_id}' id = "wip_station_id_${currentOrderIndexVal}"/>`;
                                        }
                                    }

                                    mainTableItem.innerHTML += `
                                    <tr id = "item_row_${currentOrderIndexVal}" onclick = "onItemClick(${currentOrderIndexVal});" class = "item_header_rows" >
                                            <td class="customernewsection-form">
                                            <div class="form-check form-check-primary custom-checkbox">
                                                <input type="checkbox" class="form-check-input item_row_checks" id="item_row_check_${currentOrderIndexVal}" del-index = "${currentOrderIndexVal}">
                                                <label class="form-check-label" for="Email"></label>
                                            </div>
                                        </td>
                                            <td class="poprod-decpt">
                                            <input readonly type="text" id = "items_dropdown_${currentOrderIndexVal}" name="item_code[${currentOrderIndexVal}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" autocomplete="off" data-name="${item?.item?.item_name}" data-code="${item?.item?.item_code}" data-id="${item?.item?.id}" hsn_code = "${item?.item?.hsn?.code}" item-name = "${item?.item?.item_name}" specs = '${JSON.stringify(item?.item?.specifications)}' attribute-array = '${JSON.stringify(item?.item_attributes_array)}'  value = "${item?.item?.item_code}" item-locations = "[]">
                                            <input type = "hidden" name = "item_id[]" id = "items_dropdown_${currentOrderIndexVal}_value" value = "${item?.item_id}"></input>
                                            <input type = "hidden" value = "${item?.id}" id = "${itemIdKeyId}_${currentOrderIndexVal}" name = "${itemIdKeyName}[${currentOrderIndexVal}]">
                                            ${pslipIssueTypeElement}
                                        </td>

                                        <td class="poprod-decpt">
                                                <input type="text" id = "items_name_${currentOrderIndexVal}" name = "item_name[${currentOrderIndexVal}]" class="form-control mw-100"   value = "${item?.item?.item_name}" readonly>
                                            </td>
                                        <td class="poprod-decpt" id='attribute_section_${currentOrderIndexVal}'>
                                            <button id = "attribute_button_${currentOrderIndexVal}" type = "button" data-bs-toggle="modal" onclick = "setItemAttributes('items_dropdown_${currentOrderIndexVal}', ${currentOrderIndexVal}, true);" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                                            <input type = "hidden" name = "attribute_value_${currentOrderIndexVal}" />
                                            </td>
                                        <td>
                                            <select class="form-select" name = "uom_id[]" id = "uom_dropdown_${currentOrderIndexVal}" style = "pointer-events:none;">
                                            </select>
                                        </td>
                                        <td>
                                            <select class="form-select" name = "stock_type[]" id = "stock_type_${currentOrderIndexVal}" style = "pointer-events:none;">
                                                ${stockTypeHTML}
                                            </select>
                                        </td>
                                        ${wipStationIdHiddenInput}
                                        <input type="hidden" name = "item_sub_store_from[${currentOrderIndexVal}]" id = "item_sub_store_from_${currentOrderIndexVal}" value = "${subStoreFromId}" />
                                        <input type="hidden" name = "item_sub_store_to[${currentOrderIndexVal}]" id = "item_sub_store_to_${currentOrderIndexVal}" value = "${subStoreToId}" />

                                        <input type="hidden" value = "${stationFromId}" name = "item_station_from[${currentOrderIndexVal}]" id = "item_station_from_${currentOrderIndexVal}" />
                                        <input type="hidden" value = "${stationToId}" name = "item_station_to[${currentOrderIndexVal}]" id = "item_station_to_${currentOrderIndexVal}" />

                                        <input type = "hidden" value = "${storeIdTo}" name = "item_store_to[${currentOrderIndexVal}]" />
                                        <input type = "hidden" value = "${storeIdFrom}" name = "item_store_from[${currentOrderIndexVal}]" />
                                        <input type = "hidden" value = "${currentOrder?.user_id}" name = "item_user_id[${currentOrderIndexVal}]" id = "item_user_id_input_${currentOrderIndexVal}" />
                                        <input type = "hidden" value = "${currentOrder?.department_id}" name = "item_user_id[${currentOrderIndexVal}]" id = "item_department_id_input_${currentOrderIndexVal}" />

                                        <td><input type="text" id = "item_qty_${currentOrderIndexVal}" name = "item_qty[${currentOrderIndexVal}]" oninput = "changeItemQty(this, ${currentOrderIndexVal});" class="form-control mw-100 text-end decimal-6" onblur = "setFormattedNumericValue(this);" value = "${item?.mi_balance_qty}" max = "${item?.mi_balance_qty}"/></td>
                                        <td><input type="text" id = "item_rate_${currentOrderIndexVal}" readonly name = "item_rate[]" class="form-control mw-100 text-end decimal-6" onblur = "setFormattedNumericValue(this);" value = "${item?.rate}" oninput = "changeItemRate(this, ${currentOrderIndexVal});" /></td>
                                        <td><input type="text" id = "item_value_${currentOrderIndexVal}" readonly class="form-control mw-100 text-end item_values_input" value = "${item?.mi_balance_qty * item?.rate}" /></td>
                                        <td>
                                        <div class="d-flex">
                                                <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick = "setItemRemarks('item_remarks_${currentOrderIndexVal}');">
                                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span></div>
                                            </div>
                                        <input type = "hidden" id = "item_remarks_${currentOrderIndexVal}" name = "item_remarks[${currentOrderIndexVal}]" />
                                        </td>

                                        </tr>
                                    `;
                                    initializeAutocomplete1("items_dropdown_" + currentOrderIndexVal, currentOrderIndexVal);
                                    renderIcons();

                                    var itemUomsHTML = ``;
                                    if (item.item.uom && item.item.uom.id) {
                                        itemUomsHTML += `<option value = '${item.item.uom.id}' ${item.item.uom.id == item.uom_id ? "selected" : ""}>${item.item.uom.alias}</option>`;
                                    }
                                    item.item.alternate_uoms.forEach(singleUom => {
                                        itemUomsHTML += `<option value = '${singleUom.uom.id}' ${singleUom.uom.id == item.uom_id ? "selected" : ""} >${singleUom.uom?.alias}</option>`;
                                    });
                                    document.getElementById('uom_dropdown_' + currentOrderIndexVal).innerHTML = itemUomsHTML;
                                    const qtyInput = document.getElementById('item_qty_' + currentOrderIndexVal);

                                    const itemCodeInput = document.getElementById('items_dropdown_' + currentOrderIndexVal);
                                    const uomCodeInput = document.getElementById('uom_dropdown_' + currentOrderIndexVal);
                                    const storeCodeInput = document.getElementById('item_store_from_' + currentOrderIndexVal);
                                    itemCodeInput.addEventListener('input', function() {
                                        checkStockData(currentOrderIndexVal);
                                    });
                                    uomCodeInput.addEventListener('input', function() {
                                        checkStockData(currentOrderIndexVal);
                                    });
                                    currentOrderIndexVal += 1;
                                    });
                                }
                                for (let index = 0; index < currentOrderIndexVal; index++) {
                                    getStoresData(index, document.getElementById('item_qty_' + index).value);

                                    setAttributesUI(index);
                                }
                        }
                    });
                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                }
            });
        } else {
            Swal.fire({
                title: 'Error!',
                text: 'Please select at least one document',
                icon: 'error',
            });
        }
    }



    editScript();


    function checkItemAddValidation()
    {
        let addRow = $('#series_id_input').val &&  $('#order_no_input').val && $('#order_date_input').val;
        return addRow;
    }


    function initializeAutocompleteQt(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "",type='mo') {
            $("#" + selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: typeVal,
                            customer_id : $("#customer_id_qt_val").val(),
                            header_book_id : $("#series_id_input").val()
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: `${item[labelKey1]} ${item[labelKey2] ? '-' + item[labelKey2]  : ''}`,
                                    code: item[labelKey1] || '',
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    var $input = $(this);
                    $input.val(ui.item.label);
                    $("#" + selectorSibling).val(ui.item.id);
                    getOrders(type);
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#" + selectorSibling).val("");
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
    }

    //Disable form submit on enter button
    document.querySelector("form").addEventListener("keydown", function(event) {
        if (event.key === "Enter") {
            event.preventDefault();  // Prevent form submission
        }
    });
    $("input[type='text']").on("keydown", function(event) {
        if (event.key === "Enter") {
            event.preventDefault();  // Prevent form submission
        }
    });

    $(document).ready(function() {
        // Event delegation to handle dynamically added input fields
        $(document).on('input', '.decimal-input', function() {
            // Allow only numbers and a single decimal point
            this.value = this.value.replace(/[^0-9.]/g, ''); // Remove non-numeric characters

            // Prevent more than one decimal point
            if ((this.value.match(/\./g) || []).length > 1) {
                this.value = this.value.substring(0, this.value.length - 1);
            }

            // Optional: limit decimal places to 2
            if (this.value.indexOf('.') !== -1) {
                this.value = this.value.substring(0, this.value.indexOf('.') + 7);
            }
        });
    });


function openAmendConfirmModal()
{
    $("#amendConfirmPopup").modal("show");
}

function submitAmend()
{
    enableHeader();
    let remark = $("#amendConfirmPopup").find('[name="amend_remarks"]').val();
    $("#action_type_main").val("amendment");
    $("#amendConfirmPopup").modal('hide');
    $("#sale_invoice_form").submit();
}
function onHeaderStoreChange(element, type)
{
    let currentSelectedType = document.getElementById('issue_type_input');
    if (currentSelectedType.value == 'Location Transfer') {
        if (type === 'to')
        {
            onHeaderToLocationChange(element);
        } else {
            onHeaderFromLocationChange(element);
        }
    } else {
        onHeaderToLocationChange(element);
        onHeaderFromLocationChange(element);
    }
    
    enableDisableQtButton();
}



    function renderToLocationInTablePopup(itemIndex, openModalFlag = false)
    {
        const storeElement = document.getElementById('data_stores_to_' + itemIndex);
        var storesArray = [];
        if (storeElement.getAttribute('data-stores')) {
            storesArray = JSON.parse(decodeURIComponent(storeElement.getAttribute('data-stores')));
        } else {
            Swal.fire({
                title: 'Warning!',
                text: 'Please enter quantity first',
                icon: 'warning',
            });
            return;
        }
        if (openModalFlag) {
            openModal("ToLocation");
        }
        if (storesArray.length > 0) {
            const toLocationTable = document.getElementById('item_to_location_table');
            var toLocationInnerHTML = ``;
            var totalQty = 0;
            storesArray.forEach((toStore, toStoreIndex) => {
                toLocationInnerHTML+= `
                <tr>
                <td>${toStoreIndex+1}</td>
                <td>
                <select id = "to_location_rack_input_${itemIndex}_${toStoreIndex}" class = "form-select occupy-width"  oninput = "modifyHTMLArrayForToLocation(this,${itemIndex},${toStoreIndex}, 'rack_id');" onchange = "onFromLocationRackChange(this, ${toStoreIndex}, ${itemIndex})">
                ${toStore.rack_html}
                </select>
                </td>
                <td>
                <select class = "form-select occupy-width" id = "to_location_shelf_input_${itemIndex}_${toStoreIndex}" oninput = "modifyHTMLArrayForToLocation(this,${itemIndex},${toStoreIndex}, 'shelf_id');" >
                ${toStore.shelf_html}
                </select>
                </td>
                <td>
                <select id = "to_location_bin_input_${itemIndex}_${toStoreIndex}" class = "form-select occupy-width" oninput = "modifyHTMLArrayForToLocation(this,${itemIndex},${toStoreIndex}, 'bin_id');">
                ${toStore.bin_html}
                </select>
                </td>
                <td>
                <input type="text" id = "to_location_qty_${itemIndex}_${toStoreIndex}" value = "${toStore.qty}" class="form-control mw-100 text-end to_location_qty_input_${itemIndex}" oninput = "toLocationQtyChange(this, ${itemIndex}, ${toStoreIndex})"/>
                </td>
                </tr>
                `;
                totalQty += parseFloat(toStore.qty);
            });
            toLocationTable.innerHTML = toLocationInnerHTML + `
            <tr>
                <td class="text-dark text-end" colspan = "4"><strong>Total</strong></td>
                <td class="text-dark text-end"><strong id = "to_location_total_qty">${totalQty}</strong></td>
			</tr>
            `;
            storesArray.forEach((toStore, toStoreIndex) => {
                $("#to_location_rack_input_" + itemIndex + "_" + toStoreIndex).val(toStore.rack_id);
                $("#to_location_shelf_input_" + itemIndex + "_" + toStoreIndex).val(toStore.shelf_id);
                $("#to_location_bin_input_" + itemIndex + "_" + toStoreIndex).val(toStore.bin_id);
            });
        }
        updateToLocationsTotalQty(itemIndex);
    }

    function onFromLocationRackChange(element, index, itemRowIndex)
    {
        const storeElement = document.getElementById('data_stores_to_' + itemRowIndex);
        var existingStoreArray = [];
        if (storeElement.getAttribute('data-stores')) {
            existingStoreArray = JSON.parse(decodeURIComponent(storeElement.getAttribute('data-stores')));
        }

        modifyHTMLArrayForToLocation(element, itemRowIndex, index, 'rack_id');


        const rackId = element.value;
        let shelfsHTML = `<option value = "" disabled selected>Select</option>`;
        const relativeShelfDropdownElement = document.getElementById('to_location_shelf_input_' + itemRowIndex + "_" + index);
        if (rackId && relativeShelfDropdownElement) {
            $.ajax({
                url: "{{ route('store.rack.shelfs') }}",
                type: "GET",
                dataType: "json",
                data: {
                    rack_id : rackId
                },
                success: function(data) {
                    if (data.data.shelfs) { // RACKS DATA IS PRESENT
                        data.data.shelfs.forEach(shelf => {
                            shelfsHTML+= `<option value = '${shelf.id}'>${shelf.shelf_code}</option>`;
                        });
                    }
                    relativeShelfDropdownElement.innerHTML = shelfsHTML;
                    if (existingStoreArray[index]) {
                        existingStoreArray[index].shelf_html = shelfsHTML;
                        storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(existingStoreArray)));
                    }
                },
                error : function(xhr){
                    relativeShelfDropdownElement.innerHTML = shelfsHTML;
                    existingStoreArray[index].shelf_html = shelfsHTML;
                    storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(existingStoreArray)));
                }
            });
        }
        //Also update the array
    }

    function addToLocationRow()
    {
        const tableInput = document.getElementById('item_to_location_table');
        const itemIndex = tableInput ? tableInput.getAttribute('current-item-index') : 0;
        const qtyInput = document.getElementById('item_qty_' + itemIndex);


        const itemQtysInput = document.getElementsByClassName('to_location_qty_input_' + itemIndex);
        var existingQty = 0;
        for (let index = 0; index < itemQtysInput.length; index++) {
            existingQty += parseFloat(itemQtysInput[index].value);
        }

        if (existingQty >= parseFloat(qtyInput ? qtyInput.value : 0)) {
            Swal.fire({
                title: 'Warning!',
                text: 'Cannot exceed quantity',
                icon: 'warning',
            });
            return;
        }

        const newQty = parseFloat(qtyInput ? qtyInput.value : 0) - existingQty;

        const storeElement = document.getElementById('data_stores_to_' + itemIndex);
        var existingStoreArray = [];
        if (storeElement.getAttribute('data-stores')) {
            existingStoreArray = JSON.parse(decodeURIComponent(storeElement.getAttribute('data-stores')));
        }
        const defaultStore = document.getElementById('item_store_to_' + itemIndex);
        const defaultStoreId = defaultStore.value;
        const defaultStoreCode = defaultStore.options[defaultStore.selectedIndex].text;
        let racksHTML = `<option value = "" disabled selected>Select</option>`;
        let binsHTML = `<option value = "" disabled selected>Select</option>`;
        let shelfsHTML = `<option value = "" disabled selected>Select</option>`;

        if (qtyInput && qtyInput.value > 0) { //Only add if qty is greater than 0
            $.ajax({
                url: "{{ route('store.racksAndBins') }}",
                type: "GET",
                dataType: "json",
                data: {
                    store_id : defaultStoreId
                },
                success: function(data) {
                    if (data.data.racks) { // RACKS DATA IS PRESENT
                        data.data.racks.forEach(rack => {
                            racksHTML+= `<option value = '${rack.id}'>${rack.rack_code}</option>`;
                        });
                    }
                    if (data.data.bins) { //BINS DATA IS PRESENT
                        data.data.bins.forEach(bin => {
                            binsHTML+= `<option value = '${bin.id}'>${bin.bin_code}</option>`;
                        });
                    }
                    existingStoreArray.push({
                        store_id : defaultStoreId,
                        store_code : defaultStoreCode,
                        rack_id : null,
                        rack_code : '',
                        rack_html : racksHTML,
                        shelf_id : null,
                        shelf_code : '',
                        shelf_html : shelfsHTML,
                        bin_id : null,
                        bin_code : '',
                        bin_html : binsHTML,
                        qty : newQty
                    });
                    storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(existingStoreArray)));
                    renderToLocationInTablePopup(itemIndex);
                },
                error : function(xhr){
                    console.error('Error fetching customer data:', xhr.responseText);
                    storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(existingStoreArray)));
                    renderToLocationInTablePopup(itemIndex);
                }
            });
        }
    }

    function toLocationQtyChange(element, itemIndex, index)
    {
        const qtyInput = document.getElementById('item_qty_' + itemIndex);
        const itemQtysInput = document.getElementsByClassName('to_location_qty_input_' + itemIndex);

        var existingQty = 0;
        for (let storeIndex = 0; storeIndex < itemQtysInput.length; storeIndex++) {
            existingQty += parseFloat(itemQtysInput[storeIndex].value);
        }

        if (existingQty > parseFloat(qtyInput ? qtyInput.value : 0)) {
            Swal.fire({
                title: 'Warning!',
                text: 'Cannot exceed quantity',
                icon: 'warning',
            });
            element.value = 0;
            return;
        }
        modifyHTMLArrayForToLocation(element, itemIndex, index, 'qty');
        updateToLocationsTotalQty(itemIndex);
    }

    function openToLocationModal(index) {
        const tableInput = document.getElementById('item_to_location_table');
        if (tableInput) {
            tableInput.setAttribute('item_to_location_table', index);
        }
        renderToLocationInTablePopup(index, true);
    }

    function modifyHTMLArrayForToLocation(element, itemIndex, index, key)
    {
        const storeElement = document.getElementById('data_stores_to_' + itemIndex);
        var existingStoreArray = [];
        if (storeElement.getAttribute('data-stores')) {
            existingStoreArray = JSON.parse(decodeURIComponent(storeElement.getAttribute('data-stores')));
        }
        if (existingStoreArray[index]) {
            existingStoreArray[index][key] = element.value;
        }
        storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(existingStoreArray)));
    }

    function updateToLocationsTotalQty(itemIndex)
    {
        const toLocationTotalQtyDiv = document.getElementById('to_location_total_qty');
        const itemQtysInput = document.getElementsByClassName('to_location_qty_input_' + itemIndex);
        var existingQty = 0;
        for (let storeIndex = 0; storeIndex < itemQtysInput.length; storeIndex++) {
            existingQty += parseFloat(itemQtysInput[storeIndex].value);
        }
        if (toLocationTotalQtyDiv) {
            toLocationTotalQtyDiv.textContent = existingQty;
        }
    }


    function onIssueTypeChange(element, resetDropdown = true)
    {
        const selectedType = element.value;
        if (resetDropdown == false) {
            //Do Nothing
        } else {
            // Reset all the fields of general tab
            resetIssueTypeFields();
            applyIssueTypeChange(selectedType);
        }
    }

    function applyIssueTypeChange(selectedType)
    {
        console.log("Selected Type: ", selectedType);
        if (selectedType == 'Location Transfer') {
            implementIssueTypeChange('.location_transfer','.sub_contracting, .consumption, .sub_loc_transfer');
        } else if (selectedType == 'Sub Location Transfer') {
            implementIssueTypeChange('.sub_loc_transfer, .sub_location','.location_transfer, .consumption, .sub_contracting');
        } else if (selectedType == 'Sub Contracting' || selectedType == 'Job Work') {
            implementIssueTypeChange('.sub_contracting, .sub_location','.location_transfer, .consumption, .sub_loc_transfer');
        } else if (selectedType == 'Consumption') {
            implementIssueTypeChange('.consumption','.location_transfer, .sub_location, .sub_contracting, .sub_loc_transfer');
        }
    }

    function implementIssueTypeChange(targetClasses, querySelectorOtherClasses)
    {
        console.log("Target Classes: ", targetClasses);
        var otherElements = document.querySelectorAll(querySelectorOtherClasses);
        for (let index = 0; index < otherElements.length; index++) {
            otherElements[index].style.display = "none";
            otherElements[index].classList.add("d-none");
        }
        var targetElements = document.querySelectorAll(targetClasses);
        for (let index = 0; index < targetElements.length; index++) {
            targetElements[index].style.removeProperty("display");
            targetElements[index].classList.remove("d-none");
        }

        $("#vendor_id_input").trigger(' ');

        let userIdHeaderField = document.getElementById('user_id_header');
        let departmentIdHeader = document.getElementById('department_id_header');

        if (targetClasses.includes('consumption')) {
            console.log("Target Classes: ", targetClasses);
            if (requesterTypeParam == "Department") {

                userIdHeaderField.classList.add('d-none');
                departmentIdHeader.classList.remove('d-none');

                $(".consumption_user").css('display', 'none');
                $(".consumption_dept").css('display', '');
            } else {

                userIdHeaderField.classList.remove('d-none');
                departmentIdHeader.classList.add('d-none');

                $(".consumption_user").css('display', '');
                $(".consumption_dept").css('display', 'none');
            }
        } else {
            userIdHeaderField.classList.add('d-none');
            departmentIdHeader.classList.add('d-none');

            $(".consumption_user").css('display', 'none');
            $(".consumption_dept").css('display', 'none');
        }
        let fromLocationHeader = document.getElementById('from_location_header_label');
        if (targetClasses.includes('sub_loc_transfer') ||targetClasses.includes('sub_contracting') ) {
            fromLocationHeader.innerHTML = `Location <span class="text-danger">*</span>`;
            $("#store_to_id_input").val($("#store_from_id_input").val()).trigger('input');
        } else {
            fromLocationHeader.innerHTML = `From Location <span class="text-danger">*</span>`;
        }

        $("#store_from_id_input").trigger('input');
        $("#store_to_id_input").trigger('input');

        enableDisableQtButton();
    }

    function onVendorChange(element)
    {
        const vendorId = element.value;
        const vendorInput = document.getElementById('vendor_store_id_input');
        let vendorIdInputHTML = ``;
        if (vendorId) {
            $.ajax({
                url: "{{ route('material.issue.vendor.stores') }}",
                type: "GET",
                dataType: "json",
                data: {
                    vendor_id : vendorId
                },
                success: function(data) {
                    if (data.data && (data.data.length > 0)) { // RACKS DATA IS PRESENT
                        data.data.forEach((store, index) => {
                            if ("{{isset($order) && isset($order -> to_store_id)}}") {
                                const vendorStoreId = "{{isset($order) ? $order -> to_store_id: ''}}";
                                if (vendorStoreId == store.id) {
                                    vendorIdInputHTML += `<option selected value = '${store.id}'>${store.name}</option>`;
                                } else {
                                    vendorIdInputHTML += `<option value = '${store.id}'>${store.name}</option>`;
                                }
                            } else {
                                vendorIdInputHTML += `<option value = '${store.id}'>${store.name}</option>`;

                            }
                        });
                        vendorInput.innerHTML = vendorIdInputHTML;
                        enableDisableQtButton();
                    } else {
                        vendorInput.innerHTML = vendorIdInputHTML;
                        element.value = "";
                        enableDisableQtButton();
                        Swal.fire({
                            title: 'Error!',
                            text: 'No Stores found',
                            icon: 'error',
                        });
                        return;
                    }

                },
                error : function(xhr){
                    enableDisableQtButton();
                    console.error('Error fetching customer data:', xhr.responseText);
                    vendorInput.innerHTML = vendorIdInputHTML;
                    element.value = "";
                    Swal.fire({
                        title: 'Error!',
                        text: 'No Stores found',
                        icon: 'error',
                    });
                    return;
                }
            });
        }
    }

    function checkAllMo(element)
    {
        const selectableElements = document.getElementsByClassName('pull_checkbox');
        for (let index = 0; index < selectableElements.length; index++) {
            if (!selectableElements[index].disabled) {
                selectableElements[index].checked = element.checked;
                // if (openPull)
                // if (element.checked) {
                //     checkQuotation(selectableElements[index]);
                // }
            }
        }
    }
    function checkAllJo(element)
    {
        const selectableElements = document.getElementsByClassName('pull_checkbox');
        for (let index = 0; index < selectableElements.length; index++) {
            if (!selectableElements[index].disabled) {
                selectableElements[index].checked = element.checked;
            }
        }
    }

    function checkOrRecheckAllItems(element)
    {
        const allRowsCheck = document.getElementsByClassName('item_row_checks');
        const checkedStatus = element.checked;
        for (let index = 0; index < allRowsCheck.length; index++) {
            allRowsCheck[index].checked = checkedStatus;
        }
    }

    let currentFromSubStoreArray = [];
    let lastSelectedfromSubStore = null;

    let currentToSubStoreArray = [];
    let lastSelectedToSubStore = null;

    function toSubStoreDependencyRender()
    {
        let dependentFields = document.querySelectorAll('.to_sub_store_dependent');
        for (let index = 0; index < dependentFields.length; index++) {
            if (currentToSubStoreArray.length > 0) {
                dependentFields[index].classList.remove('d-none');
            } else {
                dependentFields[index].classList.add('d-none');
            }
        }
        let headerSubStoreElement = document.getElementById('sub_store_to_id_input');
        let headerSubStoreNewHTML = ``;
        currentToSubStoreArray.forEach(subStore => {
            headerSubStoreNewHTML += `<option value = "${subStore.id}"> ${subStore.name} </option>`
        });
        headerSubStoreElement.innerHTML = headerSubStoreNewHTML;
        $("#" + headerSubStoreElement.id).val('').trigger('input');
    }

    function fromSubStoreDependencyRender()
    {
        // console.log("IS THIS HERE ?");
        // let dependentFields = document.querySelectorAll('.from_sub_store_dependent');
        // for (let index = 0; index < dependentFields.length; index++) {
        //     if (currentFromSubStoreArray.length > 0) {
        //         dependentFields[index].classList.remove('d-none');
        //     } else {
        //         dependentFields[index].classList.add('d-none');
        //     }
        // }
        let headerSubStoreElement = document.getElementById('sub_store_from_id_input');
        let headerSubStoreNewHTML = ``;
        currentFromSubStoreArray.forEach(subStore => {
            headerSubStoreNewHTML += `<option value = "${subStore.id}"> ${subStore.name} </option>`
        });
        headerSubStoreElement.innerHTML = headerSubStoreNewHTML;
        $("#" + headerSubStoreElement.id).val('').trigger('input');
    }


    @if (!isset($order))
        onHeaderFromLocationChange(document.getElementById('store_from_id_input'));
    @endif

    function onHeaderFromLocationChange(element)
    {
        fromSubStoreDependencyRender();
        const storeId = element.value;
        $.ajax({
            url: "{{route('subStore.get.from.stores')}}",
            method: 'GET',
            dataType: 'json',
            data: {
                store_id : storeId,
                types : ['Stock', 'Shop floor']
            },
            success: function(data) {
                if (data.status === 200) {
                    currentFromSubStoreArray = data.data;
                    fromSubStoreDependencyRender();
                } else {
                    currentFromSubStoreArray = [];
                    fromSubStoreDependencyRender();
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                    });
                }
            },
            error: function(xhr) {
                console.error('Error fetching customer data:', xhr);
                currentFromSubStoreArray = [];
                fromSubStoreDependencyRender();
                Swal.fire({
                    title: 'Error!',
                    text: xhr?.responseJSON?.message,
                    icon: 'error',
                });
            }
        });
    }

    function onHeaderToLocationChange(element)
    {
        toSubStoreDependencyRender();
        const storeId = element.value;
        $.ajax({
            url: "{{route('subStore.get.from.stores')}}",
            method: 'GET',
            dataType: 'json',
            data: {
                store_id : storeId,
                types : ['Stock', 'Shop floor', 'Other']
            },
            success: function(data) {
                if (data.status === 200) {
                    currentToSubStoreArray = data.data;
                    toSubStoreDependencyRender();
                } else {
                    currentToSubStoreArray = [];
                    toSubStoreDependencyRender();
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                    });
                }
            },
            error: function(xhr) {
                console.error('Error fetching customer data:', xhr);
                currentToSubStoreArray = [];
                toSubStoreDependencyRender();
                Swal.fire({
                    title: 'Error!',
                    text: xhr?.responseJSON?.message,
                    icon: 'error',
                });
            }
        });
    }


    function headerSubStoreChange(element, type = 'from')
    {
        let currentOrder = @json(isset($order) ? $order : null);
        let currentVal = element.value;
        let selected_id = '';
        let only_id = '';
        let newTargetHTML = ``;
        let targetElement = document.getElementById('station_from_id_input');
        let targetElementHeader = document.getElementById('from_station_header_label');
        if (type === 'to') {
            targetElement = document.getElementById('station_to_id_input');
            targetElementHeader = document.getElementById('to_station_header_label');
        }
        $.ajax({
            url: "{{route('stations.stocking.get.subStore')}}",
            method: 'GET',
            dataType: 'json',
            data: {
                sub_store_id : currentVal,
                selected_id : selected_id,
                only_id : only_id
            },
            success: function(data) {
                if (data.status === 'success' && data.data.length > 0) {
                    let newTargetHTML = ``;
                    data.data.forEach(station => {
                        newTargetHTML += `<option value = "${station.id}">${station.name}</option>`
                    });
                    targetElement.innerHTML = newTargetHTML;
                    targetElementHeader.classList.remove('d-none');
                    enableDisableQtButton();
                } else {
                    targetElement.innerHTML = newTargetHTML;
                    targetElementHeader.classList.add('d-none');
                    enableDisableQtButton();
                }
            },
            error: function(xhr) {
                targetElement.innerHTML = newTargetHTML;
                targetElementHeader.classList.add('d-none');
                enableDisableQtButton();
            }
        });
    }

    function checkSameLocationCondition()
    {
        const currentFromLocation = $("#store_from_id_input").val();
        const currentToLocation = $("#store_to_id_input").val();

        const currentFromStore = $("#sub_store_from_id_input").val();
        const currentToStore = $("#sub_store_to_id_input").val();

        const currentFromStation = $("#station_from_id_input").val();
        const currentToStation = $("#station_to_id_input").val();

        const fromPoint = currentFromLocation + "-" + currentFromStore + "-" + currentFromStation;
        const toPoint = currentToLocation + "-" + currentToStore + "-" + currentToStation;

        if (fromPoint == toPoint) {
            return false;
        } else {
            return true;
        }
    }

    function setSubStoreForSubContracting()
    {
        const issueType = $("#issue_type_input").val();
        if (issueType === 'Sub Contracting') {
            $("#sub_store_to_id_input").val($("#vendor_store_id_input").val());
        }
    }
    function clearFilters(type = 'mo') {
        const fields = [
            `location_code_input_${type}`,
            `location_id_${type}_val`,
            `department_code_input_${type}`,
            `department_id_${type}_val`,
            `book_code_input_${type}`,
            `book_id_${type}_val`,
            `document_no_input_${type}`,
            `document_id_${type}_val`,
            `so_no_input_${type}`,
            `so_id_${type}_val`,
            `item_name_input_${type}`,
            `item_id_${type}_val`
        ];

        fields.forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                if ('value' in el) {
                    el.value = '';
                } else {
                    el.textContent = '';
                }
            }
        });

        selectedValues = {};
        getOrders(type);
    }
    function getStoresData(itemRowId, qty = null, callOnClick = true)
        {
            const qtyElement = document.getElementById('item_qty_' + itemRowId);
            if (qtyElement && qtyElement.value > 0) {
            const itemDetailId = document.getElementById('item_row_' + itemRowId).getAttribute('data-detail-id');
            const itemId = document.getElementById('items_dropdown_'+ itemRowId).getAttribute('data-id');
            let itemAttributes = JSON.parse(document.getElementById(`items_dropdown_${itemRowId}`).getAttribute('attribute-array'));
                    let selectedItemAttr = [];
                    if (itemAttributes && itemAttributes.length > 0) {
                        itemAttributes.forEach(element => {
                        element.values_data.forEach(subElement => {
                            if (subElement.selected) {
                                selectedItemAttr.push(subElement.id);
                            }
                        });
                    });
                    }
                        const storeElement = document.getElementById('data_stores_' + itemRowId);

                        const rateInput = document.getElementById('item_rate_' + itemRowId);
                        const valueInput = document.getElementById('item_value_' + itemRowId);

                        $.ajax({
                        url: "{{route('get_item_store_details')}}",
                            method: 'GET',
                            dataType: 'json',
                            data : {
                                item_id : itemId,
                                uom_id : $("#uom_dropdown_" + itemRowId).val(),
                                selectedAttr : selectedItemAttr,
                                quantity : qty ? qty : document.getElementById('item_qty_' + itemRowId).value,
                                is_edit : "{{isset($order) ? 1 : 0}}",
                                header_id : "{{isset($order) ? $order -> id : null}}",
                                detail_id : itemDetailId,
                                store_id: $("#store_from_id_input").val(),
                                sub_store_id : $("#item_sub_store_from_" + itemRowId).val(),
                                station_id : $("item_station_from_" + itemRowId).val(),
                                stock_type : $("#stock_type_" + itemRowId).val(),
                                wip_station_id : $("#wip_station_id_"+ itemRowId).length ? $("#wip_station_id_"+ itemRowId).val() : ''
                            },
                            success: function(data) {
                                if (data?.stores && data?.stores?.records && data?.stores?.records?.length > 0 && data.stores.code == 200) {
                                    var storesArray = [];
                                    var dataRecords = data?.stores?.records;
                                    var totalValue = 0;
                                    var totalRate = 0;
                                    dataRecords.forEach(storeData => {
                                        storesArray.push({
                                            store_id : storeData.store_id,
                                            store_code : storeData.store,
                                            rack_id : storeData.rack_id,
                                            rack_code : storeData.rack ? storeData.rack : '',
                                            shelf_id : storeData.shelf_id,
                                            shelf_code : storeData.shelf ? storeData.shelf : '',
                                            bin_id : storeData.bin_id,
                                            bin_code : storeData.bin ? storeData.bin : '',
                                            qty : parseFloat(storeData.allocated_quantity_alt_uom).toFixed(4),
                                            inventory_uom_qty : parseFloat(storeData.allocated_quantity).toFixed(4)
                                        })
                                        totalValue+= parseFloat(storeData.cost_per_unit) * parseFloat(storeData.allocated_quantity_alt_uom);
                                    });
                                    var actualQty = qtyElement.value;
                                    if (actualQty > 0) {
                                        valueInput.value = totalValue.toFixed(2);
                                        totalRate = parseFloat(totalValue) / parseFloat(qty ? qty : qtyElement.value);
                                        // rateInput.value = parseFloat(totalRate).toFixed(2);
                                    } else {
                                        // rateInput.value = 0.00;
                                        valueInput.value = 0.00;
                                    }
                                    // storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(storesArray)));
                                    // if (callOnClick) {
                                    //     onItemClick(itemRowId, callOnClick);
                                    // }
                                } else if (data?.stores?.code == 202) {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: data?.stores?.message,
                                        icon: 'error',
                                    });
                                    // storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify([])));
                                    document.getElementById('item_qty_' + itemRowId).value = 0.00;
                                    if (callOnClick) {
                                        onItemClick(itemRowId, callOnClick);
                                    }
                                    // rateInput.value = 0.00;
                                    valueInput.value = 0.00;
                                } else {
                                    // storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify([])));
                                    if (callOnClick) {
                                        onItemClick(itemRowId, callOnClick);
                                    }
                                    // rateInput.value = 0.00;
                                    valueInput.value = 0.00;
                                }
                                openStoreLocationModal(itemRowId);
                            },
                            error: function(xhr) {
                                console.error('Error fetching customer data:', xhr.responseText);
                                storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify([])));
                                // rateInput.value = 0.00;
                                valueInput.value = 0.00;

                            }
                        });
            }
        }

        function getAndSetItemRate(itemIndex, type)
    {
        let rateInput = document.getElementById('item_rate_' + itemIndex);
        let itemElement = document.getElementById('items_dropdown_' + itemIndex);
        if (!itemElement) {
            return;
        }
        let payloadAttributes = [];
        let attributes = JSON.parse(itemElement.getAttribute('attribute-array'));
        attributes.forEach(element => {
            element.values_data.forEach(val => {
                if (val.selected) {
                    payloadAttributes.push({
                        attr_name : element.attribute_group_id,
                        attr_value : val.id
                    });
                }
            });
        });
        let payloadUomId = document.getElementById('uom_dropdown_' + itemIndex).value;
        let itemId = document.getElementById('items_dropdown_' + itemIndex + '_value').value;

        $.ajax({
            url: "{{route('current.item.getItemSalePrice')}}",
            method: 'POST',
            dataType: 'json',
            data: {
                quantity: document.getElementById('item_qty_' + itemIndex).value,
                item_id: itemId,
                uom_id : payloadUomId,
                attributes_data : payloadAttributes,
                customer_id: $("#customer_id_input").val(),
                currency_id: $("#currency_dropdown").val(),
                item_qty : $("#item_qty_" + itemIndex).val(),
                document_date : $("#order_date_input").val(),
                price_type : type
            },
            success: function(data) {
                    if (data && data.status == "success") {
                        rateInput.value = data.data;
                    }
            },
            error: function(xhr) {
                console.error('Error fetching customer data:', xhr.responseText);
            }
        });

    }

    function changeItemRateMi(element, index)
    {
        let itemRate = element.value;
        let itemQty = document.getElementById('item_qty_' + index).value;
        let itemVal = Number(itemRate) * Number(itemQty);
        let itemValElement = document.getElementById('item_value_' + index);
        itemValElement.value = itemVal.toFixed(2);
    }
    function changeItemQtyMi(element, index)
    {
        let itemQty = element.value;
        let itemRate = document.getElementById('item_rate_' + index).value;
        let itemVal = Number(itemRate) * Number(itemQty);
        let itemValElement = document.getElementById('item_value_' + index);
        itemValElement.value = itemVal.toFixed(2);
    }

</script>
@endsection
@endsection
