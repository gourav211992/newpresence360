@extends('layouts.app')

@section('content')
<style>
    .drapdroparea {
    background-color: #f8f9fa;
    border: 2px dashed #0d6efd;
    border-radius: 5px;
    padding: 20px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    }

    .drapdroparea.dragging {
        background-color: #e9ecef;
    }

    #uploadProgressBar {
        transition: width 0.4s ease;
    }
</style>
    <!-- BEGIN: Content-->
    <form method="POST" data-completionFunction = "disableHeader" class="ajax-input-form pick-list-form pick-list" action = "{{route('trip-plan.store')}}" data-redirect="{{ $redirect_url }}" id = "pick_list_form" enctype='multipart/form-data'>
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
				<div class="row">
                    @include('layouts.partials.breadcrumb-add-edit', [
                        'title' => "Trip Planning", 
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
                                {{--@if($buttons['print'])
                                @php
                                    $printOption = '{{$typeName}}';
                                    if ($order -> issue_type === 'Location Transfer')
                                    {
                                        $printOption = 'Delivery Challan';
                                    }
                                @endphp
                                <a href="{{ route('psv.generate-pdf', [$order->id, $printOption]) }}" target="_blank" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light dropdown-toggle" id="dropdownMenuButton" aria-expanded="false">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer">
                                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                        <rect x="6" y="14" width="12" height="8"></rect>
                                    </svg>
                                    Print  <i class="fa-regular fa-circle-down"></i>
                                </a>
                                @endif--}}
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
                                @if($buttons['post'])
                                <button id = "postButton" onclick = "onPostVoucherOpen();" type = "button" class="btn btn-warning btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Post</button>
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
                                        <div class="col-md-12">
                                            <div class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between"> 
                                                <div>
                                                    <h4 class="card-title text-theme">Basic Information</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div> 
                                                @if (isset($order) && isset($docStatusClass))
                                                <div class="col-md-6 text-sm-end">
                                                    <span class="badge rounded-pill badge-light-{{$order->display_status === 'Posted' ? 'info' : 'secondary'}} forminnerstatus">
                                                        <span class = "text-dark" >Status</span> : <span class="{{$docStatusClass}}">{{$order->display_status}}</span>
                                                    </span>
                                                </div>
                                                @endif
                                            </div> 
                                        </div> 
                                            
                                        <div class="col-md-8"> 
                                        @if (isset($order))
                                            <input type = "hidden" value = "{{$order -> id}}" name = "trip_header_id"></input>
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
                                                <input type="date" value = "{{isset($order) ? $order -> document_date : Carbon\Carbon::now() -> format('Y-m-d')}}" class="form-control" name = "document_date" id = "order_date_input" oninput = "onDocDateChange();">
                                            </div> 
                                            </div>

                                        <div class="row align-items-center mb-1 lease-hidden">
                                            <div class="col-md-3"> 
                                                <label class="form-label" id="from_location_header_label">Location<span class="text-danger">*</span></label>  
                                            </div>
                                            <div class="col-md-5">  
                                                <select class="form-select disable_on_edit" name="store_id" id="store_id_input" oninput = "locationChange(this);">
                                                    @if(isset($order) && $order->store_id && $order->document_status != App\Helpers\ConstantHelper::DRAFT)
                                                        <option value="{{ $order->store_id }}" selected> {{ $order->store_code }}</option>
                                                    @else
                                                        @foreach ($stores as $store)
                                                            <option value="{{$store->id}}" {{isset($order) ? ($order->store_id == $store->id ? 'selected' : '') : ''}} data-name="{{$store->store_name}}">{{$store->store_name}}</option> 
                                                        @endforeach
                                                    @endif    
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row align-items-center d-none mb-1">
                                            <div class="col-md-3"> 
                                                <label class="form-label" id="from_store_header_label">Main Store<span class="text-danger">*</span></label>  
                                            </div>
                                            <div class="col-md-5">  
                                                <select class="form-select disable_on_edit" name="main_sub_store_id" id="main_sub_store_id_input" oninput = "subStoreIdOnchange(this);">
                                                    @if(isset($order) && $order->main_sub_store_id && $order->document_status != App\Helpers\ConstantHelper::DRAFT)
                                                        <option value="{{ $order->main_sub_store_id }}" selected> {{ $order->main_sub_store_code }}</option>
                                                    @else
                                                    <option value="">Select</option>  
                                                    @endif    
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row align-items-center d-none mb-1">
                                            <div class="col-md-3"> 
                                                <label class="form-label" id="staging_store_header_label">Staging Store<span class="text-danger">*</span></label>  
                                            </div>
                                            <div class="col-md-5">  
                                                <select class="form-select disable_on_edit" name="staging_sub_store_id" id="staging_sub_store_id_input">
                                                    @if(isset($order) && $order->staging_sub_store_id && $order->document_status != App\Helpers\ConstantHelper::DRAFT)
                                                        <option value="{{ $order->staging_sub_store_id }}" selected> {{ $order->staging_sub_store_code }}</option>
                                                    @else
                                                    <option value="">Select</option> 
                                                        
                                                    @endif    
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    @if(isset($order))
                                    @include('partials.approval-history', ['document_status' => $order->document_status, 'revision_number' => $order->revision_number]) 
                              @endif
                                </div> 
                            </div>
                        </div>
                        <div class="col-md-12 {{(isset($order) && count($order -> dynamic_fields)) > 0 ? '' : 'd-none'}}" id = "dynamic_fields_section">
                            @if (isset($dynamicFieldsUi))
                                {!! $dynamicFieldsUi !!}
                            @endif
                        </div>    
                        <div class = "col-md-12" id = "trip_plan_section">
                            <div class="card quation-card">
                                <div class="card-header newheader">
                                    <div>
                                        <h4 class="card-title">Trip Details</h4>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label">Transport Mode<span class="text-danger">*</span></label>
                                                <select class="form-control {{isset($editTransporterFields) && $editTransporterFields ? 'cannot_disable' : ''}}" id = "transport_mode" name = "transport_mode" value = "{{isset($order) ? $order -> transport_mode : ''}}" >
                                                    @foreach($transportationModes as $transportationMode)
                                                        <option {{isset($order) && $order -> transport_mode_id == $transportationMode -> id ? 'selected' : ''}} value="{{$transportationMode->id}}">
                                                            {{ucfirst($transportationMode->description)}}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label">Transporter Name<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control ledgerselecct ui-autocomplete-input" autocomplete="off" id = "transporter_name" name = "transporter_name" value = "{{isset($order) ? $order -> transporter_name : ''}}" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label">Vehicle Number</label>
                                                <input type="text" class="form-control ledgerselecct ui-autocomplete-input" autocomplete="off"  id = "vehicle_number" name = "vehicle_number" value = "{{isset($order) ? $order -> vehicle_number : ''}}" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label">Driver Name<span class="text-danger">*</span></label>
                                                <input type="text" class="form-control ledgerselecct ui-autocomplete-input" autocomplete="off"  id = "challan_number" name = "challan_number" value = "{{isset($order) ? $order -> challan_number : ''}}" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                            @if(isset($order) && $order->document_status != App\Helpers\ConstantHelper::DRAFT)
                                @include('trip-plan.edit')
                            @else
                                @include('trip-plan.create')
                            @endif
                            </div>
                            <div class="col-md-12">
                                <div class="card">
                                    <div class="card-body customernewsection-form">
                                        <div class="border-bottom mb-2 pb-25" id="componentSection">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader ">
                                                        <h4 class="card-title text-theme">Remarks</h4>
                                                        {{-- <p class="card-text">Fill the details</p> --}}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="mb-1">
                                                            <label class="form-label">Upload Document</label>
                                                            <input type="file" name="attachment[]" class="form-control" onchange = "addFiles(this,'main_bom_file_preview')" multiple>
                                                            <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                                                        </div>
                                                    </div>
                                                    <div class = "col-md-6" style = "margin-top:19px;">
                                                        <div class = "row" id = "main_bom_file_preview">
                                                        </div>
                                                    </div>  
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-1">  
                                                    <label class="form-label">Final Remarks</label> 
                                                    <textarea maxlength="250" name="remarks" type="text" rows="4" class="form-control" placeholder="Enter Remarks here..."></textarea> 
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
    @section('modals')
    @include('trip-plan.modals')
    @endsection
    @section('scripts')
    <script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
    <script>
    var currentfy = JSON.stringify({!! isset($order) ? $order : " " !!});
    let requesterTypeParam = "{{isset($order) ? $order -> requester_type : 'Department'}}";
    let redirect = "{{$redirect_url}}";   
    </script>
    @include('trip-plan.common-js-route',["order" => isset($order) ? $order : null, "route_prefix" => "trip"])
    <script src="{{ asset("assets\\js\\modules\\pl\\common-script.js") }}"></script>
    <script src="{{ asset("assets\\js\\modules\\trip\\trip.js") }}"></script>

    @endsection