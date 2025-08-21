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
    <form method="POST" data-completionFunction = "disableHeader" class="ajax-input-form sales_module_form material_issue" action = "{{route('pds.store')}}" data-redirect="{{ $redirect_url }}" id = "sale_invoice_form" enctype='multipart/form-data'>
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
				<div class="row">
                    @include('layouts.partials.breadcrumb-add-edit', [
                        'title' => 'PickUp DropOff Schedule', 
                        'menu' => 'Home', 
                        'menu_url' => url('home'),
                        'sub_menu' => 'Add New'
                    ])
                    <input type = "hidden" value = "{{isset($order) ? $order->document_status : "draft"}}" name = "document_status" id = "document_status" />
					<div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
						<div class="form-group breadcrumb-right" id = "buttonsDiv">   
                        @if(!isset(request() -> revisionNumber))
                        <button type = "button" onclick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>  
                        @endif
                            @if (isset($order))
                                @if($buttons['print'])
                                    <a type="button" href="{{ route('pds.generate-pdf', ['id' => $order -> id]) }}" target="_blank" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light dropdown-toggle" type="button"><i data-feather='printer'></i> Print</a>
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
                                @if($buttons['post'])
                                <button id = "postButton" onclick = "onPostVoucherOpen();" type = "button" class="btn btn-warning btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Post</button>
                                @endif
                                @if($buttons['revoke'])
                                    <button id = "revokeButton" type="button" onclick = "revokeDocument();" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='rotate-ccw'></i> Revoke</button>
                                @endif
                                @if($order->document_status != App\Helpers\ConstantHelper::DRAFT)

                                <button type = "button" data-target = "#sendMail" onclick = "sendMailTo();" data-toggle = "modal" class="btn btn-primary btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i data-feather="mail"></i> E-Mail</button>
                                @endif
                                @else

                                <button type = "button" name="action" value="draft" id = "save-draft-button" onclick = "submitForm('draft');" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0"><i data-feather='save'></i> Save as Draft</button>  
                                <button type = "button" name="action" value="submitted"  id = "submit-button" onclick = "submitForm('submitted');" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Submit</button> 
                                
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
                                            <input type = "hidden" name = "type" id = "type_hidden_input"></input>
                                            @if (isset($order))
                                                <input type = "hidden" value = "{{$order -> id}}" name = "pickup_header_id"></input>
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
                                                    <input type="date" value = "{{isset($order) ? $order -> document_date : Carbon\Carbon::now() -> format('Y-m-d')}}" class="form-control" name = "document_date" id = "order_date_input" oninput = "onDocDateChange();" min = "{{ $current_financial_year['start_date'] }}" max = "{{ $current_financial_year['end_date'] }}" required>
                                                </div> 
                                            </div>

                                            <div class="row align-items-center mb-1 lease-hidden">
                                                <div class="col-md-3"> 
                                                    <label class="form-label" id="from_location_header_label">Location<span class="text-danger">*</span></label>  
                                                </div>
                                                <div class="col-md-5">  
                                                    <select class="form-select disable_on_edit except_draft" name="store_id" id="store_id_input">
                                                        @if(isset($order) && $order->store_id && $order->document_status != App\Helpers\ConstantHelper::DRAFT)
                                                            <option value="{{ $order->store_id }}" selected> {{ $order->store_code }}</option>
                                                        @else
                                                            @foreach ($stores as $store)
                                                                <option value="{{$store->id}}" data-name="{{$store->store_name}}">{{$store->store_name}}</option> 
                                                            @endforeach
                                                        @endif    
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1 disable_on_edit" id = "selection_section" style = "display:none;"> 
                                                <div class="col-md-3"> 
                                                    <label class="form-label">Reference From</label>  
                                                </div>
                                                    <div class="col-md-4 action-button" id = "pi_order_selection"> 
                                                        <input type="hidden" id="pi_header_pull" value ="{{ App\Helpers\ConstantHelper::PI_SERVICE_ALIAS }}">
                                                        <button onclick = "openHeaderPullModal();" disabled type = "button" id = "select_pi_button" class="btn btn-outline-primary btn-sm mb-0"><i data-feather="plus-square"></i>
                                                            Purchase Indent
                                                        </button>
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
                            <div class="row">
                                <div class="col-md-12">
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
                                                        <label class="form-label">Trip No. <span class="text-danger">*</span></label> 
                                                        <input type="text" name='trip_no' value="{{ isset($order) ? $order->trip_no : '' }}" class="form-control" />
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Vehicle No. <span class="text-danger">*</span></label> 
                                                        <input type="text" name='vehicle' value="{{ isset($order) ? $order->vehicle_no : '' }}" class="form-control" />
                                                    </div>
                                                </div>  
                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Champ Name <span class="text-danger">*</span></label> 
                                                        <input type="text" name='champ' value="{{ isset($order) ? $order->champ : '' }}" class="form-control" />
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Total Items <span class="text-danger">*</span></label>
                                                        <input type="text" id='item_count' disabled name='item_count' value="{{ isset($order) ? $order->total_item_count : (isset($order) && isset($order->pickupItems) ? count($order->pickupItems) : '') }}" class="form-control" /> 
                                                    </div>
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
                                                    <h4 class="card-title text-theme">Item Detail</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-sm-end" id = "add_delete_item_section">
                                                <!-- <button type="button" id="importItem" class="mx-1 btn btn-sm btn-outline-primary importItem" onclick="openImportItemModal('create', '')"><i data-feather="upload"></i> Import Item</button>   -->
                                                <a href="#" onclick = "deleteItemRows();" class="btn btn-sm btn-outline-danger me-50"><i data-feather="x-circle"></i> Delete</a>
                                                <a href="#" onclick = "addItemRow();" id = "add_item_section" style = "display:none;" class="btn btn-sm btn-outline-primary"><i data-feather="plus"></i> Add Item</a>
                                            </div>
                                        </div> 
                                        @include('pds.items_table')
                                    </div>
                                    <div class="border-bottom mb-2 pb-25 d-none">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="newheader "> 
                                                    <h4 class="card-title text-theme">Instructions</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <textarea name="instructions" id="summernote" class="form-control" placeholder="Enter PDS Instruction" >{{ isset($order->instructions) ? $order->instructions : '' }}</textarea>
                                                @error('pickup_instructions')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div> 
                                    </div>

                                    <div class="row"> 
                                        <div class="col-md-12">
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
        
        <div class="modal fade text-start show" id="postvoucher" tabindex="-1" aria-labelledby="postVoucherModal" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="postVoucherModal"> Voucher Details</h4>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row"> 
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">Series <span class="text-danger">*</span></label>
                                    <input id = "voucher_book_code" class="form-control" disabled="" >
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">Voucher No <span class="text-danger">*</span></label>
                                    <input id = "voucher_doc_no" class="form-control" disabled="" value="">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">Voucher Date <span class="text-danger">*</span></label>
                                    <input id = "voucher_date" class="form-control" disabled="" value="">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">Currency <span class="text-danger">*</span></label>
                                    <input id = "voucher_currency" class="form-control" disabled="" value="">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="mt-1 table table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"> 
                                        <thead>
                                            <tr>
                                                <th>Type</th>  
                                                <th>Group</th>
                                                <th>Leadger Code</th>
                                                <th>Leadger Name</th>
                                                <th class="text-end">Debit</th>
                                                <th class="text-end">Credit</th>
                                            </tr>
                                            </thead>
                                            <tbody id = "posting-table">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer text-end">
                        <button onclick = "postVoucher(this);" id = "posting_button" type = "button" class="btn btn-primary btn-sm waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Submit</button>
                    </div>
                </div>
            </div>
        </div>


        <div class="modal fade" id="amendConfirmPopup" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Amend PickUp DropOff Schedule
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
      d              <button type="button" class="btn btn-primary" onclick = "submitAmend();">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </form>

<div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form class="ajax-submit-2" method="POST" action="{{ route('document.approval.pds') }}" data-redirect="{{ $redirect_url }}" enctype='multipart/form-data'>
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

<div class="modal fade" id="sendMail" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form class="ajax-submit-2" method="POST" action="{{ route('pds.mail') }}" data-redirect="{{ $redirect_url }}" enctype='multipart/form-data'>
            @csrf
            <input type="hidden" name="action_type" id="action_type_mail">
            <input type="hidden" name="id" value="{{isset($order) ? $order -> id : ''}}">
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
                            <input type='hidden' id='mailer_ids' value='' name="email_to_id" class="cannot_disable">
                            <select name="email_to[]" id="mailer_select" class="select2 form-control cannot_disable">
                                
                            </select>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="mb-1">
                            <label class="form-label">CC To</label>
                            <input type="text" id='cc_to' name="cc_to[]" class="form-control cannot_disable">
                        </div>
                    </div>
                    {{-- <div class="col-md-12">
                        <div class="mb-1">
                            <label class="form-label">BCC To</label>
                            
                        </div>
                    </div> --}}


                    <div class="col-md-12">
                        <div class="mb-1">
                            <label class="form-label">Remarks</label>
                            <textarea name="remarks" id="mail_remarks" class="form-control cannot_disable" placeholder = "Please Enter Required Remarks"></textarea>
                        </div>
                    </div>
                </div>
            <div class="modal-footer justify-content-center">  
                <button type="reset" class="btn btn-outline-secondary me-1" onclick = "closeModal('sendMail');">Cancel</button> 
                <button type="submit" id="mail_submit" class="btn btn-primary"><i class="fa-solid fa-paper-plane"></i> Send</button>
            </div>
        </form>
        </div>
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
              <p>Are you sure you want to <strong>Amend</strong> this <strong>Physical Stock Verification</strong>?</p>
              <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
              <button type="button" data-bs-dismiss="modal" onclick = "amendConfirm();" class="btn btn-primary">Confirm</button>
          </div> 
      </div>
  </div>
</div>
<!-- Import Item Modal -->
<div class="modal fade" id="importItemModal" tabindex="-1" aria-labelledby="importItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="importItemModalLabel">Import Items</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="importForm" action="" method="POST" enctype="multipart/form-data">
                {{-- <form class="importForm" action="{{ route('pds.import') }}" method="POST" enctype="multipart/form-data"> --}}
                    @csrf
                    <div class="mb-3">
                        <label for="fileUpload" class="form-label">Upload File</label>
                        <div class="drapdroparea border border-primary rounded p-4 text-center">
                            <p class="text-muted mb-2">Drag and drop your file here or click to upload</p>
                            <input type="file" id="fileUpload" name="file" class="form-control d-none">
                            <button type="button" class="btn btn-outline-primary" onclick="document.getElementById('fileUpload').click();">Choose File</button>
                        </div>
                        <div id="fileNameDisplay" class="mt-2 d-none">
                            <p class="text-success">Selected File: <span id="selectedFileName"></span></p>
                        </div>
                        <div id="upload-error" class="text-danger mt-2 d-none"></div>
                    </div>
                    <div class="progress mt-3 d-none" id="uploadProgress">
                        <div class="progress-bar" id="uploadProgressBar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>
                    <div class="mt-3 text-end">
                        <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="proceedBtn" style="display: none;">Proceed</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@include('pds.pull_pop_up_modal');

@section('scripts')
<script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/modules/pull-popup-datatable.js')}}"></script>

<script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
            $("select.select-2").select2({
                placeholder: "Select",
                allowClear: true,
                width: '100%',
            });
        })
        const order = @json(isset($order) ? $order : null);
        $('#issues').on('change', function() {
            var issue_id = $(this).val();
            var seriesSelect = $('#series');

            seriesSelect.empty(); // Clear any existing options
            seriesSelect.append('<option value="">Select</option>');

            if (issue_id) {
                $.ajax({
                    url: "{{ url('get-series') }}/" + issue_id,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $.each(data, function(key, value) {
                            seriesSelect.append('<option value="' + key + '">' + value + '</option>');
                        });
                    }
                });
            }
        });
        const startDate = "{{ $current_financial_year['start_date'] }}";
        const endDate = "{{ $current_financial_year['end_date'] }}";

        $('#order_date_input').on('blur', function() {
            checkDateRange(this);
        });

        function checkDateRange(element) {
            let date = element.value;
            console.log(date);

            if (date > endDate || date < startDate) {
                console.log("date Checkers");

                element.value = endDate; // Use .value not .val() for DOM input

                Swal.fire({
                    title: 'Error!',
                    text: `Date Should Range Between ${startDate} to ${endDate}`,
                    icon: 'error',
                });
            }
        }
        
        $('#series').on('change', function() {
            var book_id = $(this).val();
            var request = $('#requestno');

            request.val(''); // Clear any existing options
            
            if (book_id) {
                $.ajax({
                    url: "{{ url('get-request') }}/" + book_id,
                    type: "GET",
                    dataType: "json",
                    success: function(data) 
                        {
                            if (data.requestno) {
                            request.val(data.requestno);
                        }
                    }
                });
            }
        });

        initializeAutocompleteStores("new_rack_code_input", "new_rack_id_input", 'store_rack', 'rack_code');
        initializeAutocompleteStores("new_shelf_code_input", "new_shelf_id_input", 'rack_shelf', 'shelf_code');
        initializeAutocompleteStores("new_bin_code_input", "new_bin_id_input", 'shelf_bin', 'bin_code');

        function initializeAutocompleteStores(selector, siblingSelector, type, labelField) {
            $("#" + selector).autocomplete({
                source: function(request, response) {
                    let dataPayload = {
                        q:request.term,
                        type : type
                    };
                    if (type == "store_rack") {
                        dataPayload.store_id = $("#new_store_id_input").val()
                    }
                    if (type == "rack_shelf") {
                        dataPayload.rack_id = $("#new_rack_id_input").val()
                    }
                    if (type == "shelf_bin") {
                        dataPayload.shelf_id = $("#new_shelf_id_input").val()
                    }
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: dataPayload,
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: item[labelField],
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
                    var itemCode = ui.item.label;
                    var itemId = ui.item.id;
                    $input.val(itemCode);
                    $("#" + siblingSelector).val(itemId);
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
    }
    function initializeAutocompleteSearch(selector, siblingSelector, type, labelField) {
            $("#" + selector).autocomplete({
                source: function(request, response) {
                    let dataPayload = {
                        q:request.term,
                        type : type
                    };
                    if(type == "sub_type") {
                        dataPayload.category_id = $("#filter_category_id").val()
                    }
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: dataPayload,
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: item[labelField],
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
                    var itemCode = ui.item.label;
                    var itemId = ui.item.id;
                    $input.val(itemCode);
                    $("#" + siblingSelector).val(itemId);
                    $("#" + siblingSelector).trigger('change');
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#" + siblingSelector).val("");
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
    }
initializeAutocompleteSearch('filter_sub_type','filter_sub_type_id','sub_type','name');
initializeAutocompleteSearch('filter_category','filter_category_id','category','name');
initializeAutocompleteSearch('filter_sub_category','filter_sub_category_id','subcategory','name');
initializeAutocompleteSearch('filter_hsn','filter_hsn_id','hsn','code');
initializeAutocompleteSearch('filter_item_name_code','filter_item_name_code_id','inventory_items','item_name');
    function resetStoreFields()
    {
        $("#new_store_id_input").val("")
        $("#new_store_code_input").val("")

        $("#new_rack_id_input").val("")
        $("#new_rack_code_input").val("")

        $("#new_shelf_id_input").val("")
        $("#new_shelf_code_input").val("")

        $("#new_bin_id_input").val("")
        $("#new_bin_code_input").val("")

        $("#new_location_qty").val("")
    }


        function onChangeSeries(element)
        {
            document.getElementById("order_no_input").value = 12345;
        }

        function onChangeCustomer(selectElementId, index ,reset = false) 
        {
            const selectedOption = document.getElementById(selectElementId);
            
            //Get Addresses (Billing + Shipping)
        }

        function changeDropdownOptions(mainDropdownElement, dependentDropdownIds, dataKeyNames, routeUrl, resetDropdowns = null, resetDropdownIdsArray = [])
        {
            const mainDropdown = mainDropdownElement;
            const secondDropdowns = [];
            const dataKeysForApi = [];
            if (Array.isArray(dependentDropdownIds)) {
                dependentDropdownIds.forEach(elementId => {
                    if (elementId.type && elementId.type == "class") {
                        const multipleUiDropDowns = document.getElementsByClassName(elementId.value);
                        const secondDropdownInternal = [];
                        for (let idx = 0; idx < multipleUiDropDowns.length; idx++) {
                            secondDropdownInternal.push(document.getElementById(multipleUiDropDowns[idx].id));
                        }
                        secondDropdowns.push(secondDropdownInternal);
                    } else {
                        secondDropdowns.push(document.getElementById(elementId));
                    }
                });
            } else {
                secondDropdowns.push(document.getElementById(dependentDropdownIds))
            }

            if (Array.isArray(dataKeyNames)) {
                dataKeyNames.forEach(key => {
                    dataKeysForApi.push(key);
                })
            } else {
                dataKeysForApi.push(dataKeyNames);
            }

            if (dataKeysForApi.length !== secondDropdowns.length) {
                console.log("Dropdown function error");
                return;
            }

            if (resetDropdowns) {
                const resetDropdownsElement = document.getElementsByClassName(resetDropdowns);
                for (let index = 0; index < resetDropdownsElement.length; index++) {
                    resetDropdownsElement[index].innerHTML = `<option value = '0'>Select</option>`;
                }
            }

            if (resetDropdownIdsArray) {
                if (Array.isArray(resetDropdownIdsArray)) {
                    resetDropdownIdsArray.forEach(elementId => {
                        let currentResetElement = document.getElementById(elementId);
                        if (currentResetElement) {
                            currentResetElement.innerHTML = `<option value = '0'>Select</option>`;
                        }
                    });
                } else {
                    const singleResetElement = document.getElementById(resetDropdownIdsArray);
                    if (singleResetElement) {
                        singleResetElement.innerHTML = `<option value = '0'>Select</option>`;
                    }            
                }
            }

            const apiRequestValue = mainDropdown?.value;
            const apiUrl = routeUrl + apiRequestValue;
            fetch(apiUrl, {
                method : "GET",
                headers : {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            }).then(response => response.json()).then(data => {
                if (mainDropdownElement.id == "customer_id_input") {
                    if (data?.data?.currency_exchange?.status == false || data?.data?.error_message) {
                        Swal.fire({
                            title: 'Error!',
                            text: data?.data?.currency_exchange?.message ? data?.data?.currency_exchange?.message : data?.data?.error_message,
                            icon: 'error',
                        });
                        mainDropdownElement.value = "";
                        document.getElementById('currency_dropdown').innerHTML = "";
                        document.getElementById('currency_dropdown').value = "";
                        document.getElementById('payment_terms_dropdown').innerHTML = "";
                        document.getElementById('payment_terms_dropdown').value = "";
                        document.getElementById('current_billing_address_id').value = "";
                        document.getElementById('current_shipping_address_id').value = "";
                        document.getElementById('current_billing_address').textContent = "";
                        document.getElementById('current_shipping_address').textContent = "";
                        document.getElementById('customer_id_input').value = "";
                        document.getElementById('customer_code_input').value = "";
                        return;
                    }
                    
                }
                secondDropdowns.forEach((currentElement, idx) => {
                    if (Array.isArray(currentElement)) {
                        currentElement.forEach(currentElementInternal => {
                            currentElementInternal.innerHTML = `<option value = '0'>Select</option>`;
                            const response = data.data;
                            response?.[dataKeysForApi[idx]]?.forEach(item => {
                                const option = document.createElement('option');
                                option.value = item.value;
                                option.textContent = item.label;
                                currentElementInternal.appendChild(option);
                            })
                        });
                    } else {
                        
                        currentElement.innerHTML = `<option value = '0'>Select</option>`;
                        const response = data.data;
                        response?.[dataKeysForApi[idx]]?.forEach((item, idxx) => {
                            if (idxx == 0) {
                                if (currentElement.id == "billing_address_dropdown") {
                                    document.getElementById('current_billing_address').textContent = item.label;
                                    document.getElementById('current_billing_address_id').value = item.id;
                                    // $('#billing_country_id_input').val(item.country_id).trigger('change');
                                    // changeDropdownOptions(document.getElementById('billing_country_id_input'), ['billing_state_id_input'], ['states'], '/states/', null, ['billing_city_id_input']);
                                }
                                if (currentElement.id == "shipping_address_dropdown") {
                                    document.getElementById('current_shipping_address').textContent = item.label;
                                    document.getElementById('current_shipping_address_id').value = item.id;
                                    document.getElementById('current_shipping_country_id').value = item.country_id;
                                    document.getElementById('current_shipping_state_id').value = item.state_id;
                                    // $('#shipping_country_id_input').val(item.country_id).trigger('change');
                                    // changeDropdownOptions(document.getElementById('shipping_country_id_input'), ['shipping_state_id_input'], ['states'], '/states/', null, ['shipping_city_id_input']);
                                }
                                // if (currentElement.id == "billing_state_id_input") {
                                //     changeDropdownOptions(document.getElementById('billing_state_id_input'), ['billing_city_id_input'], ['cities'], '/cities/', null, []);
                                //     $('#billing_state_id_input').val(item.state_id).trigger('change');
                                //     console.log("STATEID", item);

                                // }
                                // if (currentElement.id == "shipping_state_id_input") {
                                //     changeDropdownOptions(document.getElementById('shipping_state_id_input'), ['shipping_city_id_input'], ['cities'], '/cities/', null, []);
                                //     $('#shipping_state_id_input').val(item.state_id).trigger('change');
                                //     console.log("STATEID", item);

                                // }
                            }
                            const option = document.createElement('option');
                            option.value = item.value;
                            option.textContent = item.label;
                            if (idxx == 0 && (currentElement.id == "billing_address_dropdown" || currentElement.id == "shipping_address_dropdown")) {
                                option.selected = true;
                            }
                            currentElement.appendChild(option);
                        })
                    }
                });
            }).catch(error => {
                mainDropdownElement.value = "";
                document.getElementById('currency_dropdown').innerHTML = "";
                document.getElementById('currency_dropdown').value = "";
                document.getElementById('payment_terms_dropdown').innerHTML = "";
                document.getElementById('payment_terms_dropdown').value = "";
                document.getElementById('current_billing_address_id').value = "";
                document.getElementById('current_shipping_address_id').value = "";
                document.getElementById('current_billing_address').textContent = "";
                document.getElementById('current_shipping_address').textContent = "";
                document.getElementById('customer_id_input').value = "";
                document.getElementById('customer_code_input').value = "";
                console.log("Error : ", error);
                return;
            })
        }

        function itemOnChange(selectedElementId, index, routeUrl) // Retrieve element and set item attiributes
        {
            const selectedElement = document.getElementById(selectedElementId);
            const ItemIdDocument = document.getElementById(selectedElementId + "_value");
            if (selectedElement && ItemIdDocument) {
                ItemIdDocument.value = selectedElement.dataset?.id;
                const apiRequestValue = selectedElement.dataset?.id;
                const apiUrl = routeUrl + apiRequestValue;
                fetch(apiUrl, {
                    method : "GET",
                    headers : {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                }).then(response => response.json()).then(data => {
                    const response = data.data;
                    selectedElement.setAttribute('attribute-array', JSON.stringify(response.attributes));
                    selectedElement.setAttribute('item-name', response.item.item_name);
                    document.getElementById('items_name_' + index).value = response.item.item_name;
                    selectedElement.setAttribute('hsn_code', (response.item_hsn));
                    setItemAttributes('items_dropdown_' + index, index,false);
                    let rateElement = document.getElementById('item_rate_' + index);
                    if (rateElement && response.item.sell_price) {
                        rateElement.value = parseFloat(response.item.sell_price);
                        setValue(index);
                    }
                    onItemClick(index);
                    
                }).catch(error => {
                    console.log("Error : ", error);
                })
            }
        }

        function setItemAttributes(elementId, index, disabled = false)
        {
            disabled = disabled || ($('#document_status').val() != "{{ App\Helpers\ConstantHelper::DRAFT }}");
            console.log("Disabled : ", disabled);
            document.getElementById('attributes_table_modal').setAttribute('item-index',index);
            var elementIdForDropdown = elementId;
            const dropdown = document.getElementById(elementId);
            const attributesTable = document.getElementById('attribute_table');
            if (dropdown) {
                const attributesJSON = JSON.parse(dropdown.getAttribute('attribute-array'));
                var innerHtml = ``;
                attributesJSON.forEach((element, index) => {
                    var optionsHtml = ``;
                    element.values_data.forEach(value => {
                        optionsHtml += `
                        <option value = '${value.id}' ${value.selected ? 'selected' : ''}>${value.value}</option>
                        `
                    });
                    innerHtml += `
                    <tr>
                    <td>
                    ${element.group_name}
                    </td>
                    <td>
                    <select ${disabled ? 'disabled' : ''} class="form-select select2" id = "attribute_val_${index}" style = "max-width:100% !important;" onchange = "changeAttributeVal(this, ${elementIdForDropdown}, ${index});">
                        <option>Select</option>
                        ${optionsHtml}
                    </select> 
                    </td>
                    </tr>
                    `
                });
                attributesTable.innerHTML = innerHtml;
                if (attributesJSON.length == 0) {
                    document.getElementById('item_physical_qty_' + index).focus();
                    document.getElementById('attribute_button_' + index).disabled = true;
                } else {
                    $("#attribute").modal("show");
                    document.getElementById('attribute_button_' + index).disabled = false;
                }
                const input = document.getElementById('item_qty_' + index);
                // if(!(order && !order.document_status=={{App\Helpers\ConstantHelper::DRAFT}}))
                // {
                //     getStoresData(index, input ? input.value ?? 0 : 0);
                // }
            }

        }

        function changeAttributeVal(selectedElement, elementId, index)
        {
            const attributesJSON = JSON.parse(elementId.getAttribute('attribute-array'));
            const selectedVal = selectedElement.value;
            attributesJSON.forEach((element, currIdx) => {
                if (currIdx == index) {
                    element.values_data.forEach(value => {
                    if (value.id == selectedVal) {
                        value.selected = true;
                    } else {
                        value.selected = false;
                    }
                });
                }
            });
            elementId.setAttribute('attribute-array', JSON.stringify(attributesJSON));
        }

        function addItemRow()
        {
            var docType = $("#service_id_input").val();
            var invoiceToFollow = $("#service_id_input").val() == "yes";
            const tableElementBody = document.getElementById('item_header');
            const previousElements = document.getElementsByClassName('item_header_rows');
            const newIndex = previousElements.length ? previousElements.length : 0;
            if (newIndex == 0) {
                let addRow = !!(
                    $('#series_id_input').val() &&
                    $('#order_no_input').val() &&
                    $('#order_date_input').val() &&
                    $('#store_id_input').val() 
                    // && $('#supplier_ids_input').val() && $('#supplier_ids_input').val().length > 0
                );
                if (!addRow) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please fill all the header details first',
                    icon: 'error',
                });
                return;
                }
            } else {
                let addRow = $('#items_dropdown_' + (newIndex - 1)).val();
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
            var headerFromStoreId = $("#store_id_input").val();
            var headerToStoreId = $("#store_to_id_input").val();
            var headerFromStoreCode = $("#store_id_input").attr("data-name");
            var headerToStoreCode = $("#store_to_id_input").attr("data-name");
            var stores = @json($stores);
            var storesHTML = ``;
            stores.forEach(store => {
                if (store.id == headerFromStoreId) {
                    storesHTML += `<option value = "${store.id}" selected>${store.store_name}</option>`
                } else {
                    storesHTML += `<option value = "${store.id}">${store.store_name}</option>`
                }
            });
            newItemRow.innerHTML = `
            <tr id="item_row_${newIndex}">
                <td class="customernewsection-form" style="width: 30px;">
                    <div class="form-check form-check-primary custom-checkbox">
                        <input type="checkbox" class="form-check-input item_row_checks" id="item_checkbox_${newIndex}" del-index="${newIndex}">
                        <label class="form-check-label" for="item_checkbox_${newIndex}"></label>
                    </div>
                </td>
                <td class="d-none">
                    <select id="item_type_${newIndex}" name="item_type[]" class="form-select mw-100">
                        <option value="Pickup" selected>Pickup</option>
                        // <option value="Dropoff">Dropoff</option>
                    </select>
                </td>
                <td>
                    <input type="text" id="items_dropdown_${newIndex}" name="item_code[]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" autocomplete="off" data-name="" data-code="" data-id="" hsn_code="" item-name="" specs="[]" attribute-array="[]" value="">
                    <input type="hidden" name="item_id[]" id="items_dropdown_${newIndex}_value">
                </td>
                <td>
                    <input type="text" id="items_name_${newIndex}" name="item_name[]" class="form-control mw-100" value="" readonly>
                </td>
                <td id="attribute_section_${newIndex}">
                    <button id="attribute_button_${newIndex}" type="button" data-bs-toggle="modal" onclick="setItemAttributes('items_dropdown_${newIndex}', '${newIndex}',true);" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                    <input type="hidden" name="attribute_value_${newIndex}">
                </td>
                <td>
                    <select class="form-select" name="uom_id[]" id="uom_dropdown_${newIndex}">
                    </select>
                </td>
                <td class="numeric-alignment">
                    <input type="text" id="item_qty_${newIndex}" name="item_qty[]" class="form-control mw-100 text-end"  placeholder="Qty" onblur="setFormattedNumericValue(this);">
                </td>
                <td>
                    <input type="text" id="item_uid_${newIndex}" name="item_uid[]" class="form-select mw-100" placeholder="UID">
                </td>
                <td> 
                    <div class="form-check form-check-primary custom-checkbox">
                        <input type="checkbox" class="form-check-input" name="item_delivery_cancelled[]" id="item_delivery_cancelled_${newIndex}" del-index="${newIndex}">
                        <label class="form-check-label" for="item_delivery_cancelled_${newIndex}"></label>
                    </div>
                </td>
                <td> 
                    <input type="text" id="item_customer_${newIndex}"  name="item_customer[]" class="form-control mw-100 ui-autocomplete-input" data-customer='' onblur = "onChangeCustomer('item_customer_${newIndex}',${newIndex}, true);" autocomplete="off" placeholder="Customer">
                    <input type="hidden" id="item_customer_id_${newIndex}"  name="item_customer_id[]" class="form-control mw-100">
                </td>
                <td> 
                    <input type="text" id="item_customer_name_${newIndex}"  name="item_customer_name[]" class="form-control mw-100 " placeholder="Customer Name">
                </td>
                <td>
                    <input type="text" id="item_mobile_${newIndex}" name="item_mobile[]" class="form-control mw-100 ui-autocomplete-input" placeholder="Mobile">
                </td>
                <td>
                    <input type="text" id="item_email_${newIndex}" name="item_email[]" class="form-control mw-100 ui-autocomplete-input" placeholder="Email">
                </td>
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
            initializeAutocompleteCustomer("item_customer_" + newIndex, newIndex);
            renderIcons();
            disableHeader();
            const allRowsNew = document.getElementsByClassName('item_row_checks');
            $("#item_count").val(allRowsNew.length);
            const qtyInput = document.getElementById('item_qty_' + newIndex);

            const itemCodeInput = document.getElementById('items_dropdown_' + newIndex);
            const uomCodeInput = document.getElementById('uom_dropdown_' + newIndex);
            const storeCodeInput = document.getElementById('item_store_from_' + newIndex);
        }

        function initializeAutocompleteCustomer(selector,index) {
            $("#" + selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type:'customer_list'
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: `${item.company_name} (${item.customer_code})`,
                                    name: `${item.company_name}`,
                                    code: item.customer_code || '',
                                    item_id: item.id,
                                    type : item?.customer_type,
                                    phone_no : item?.mobile,
                                    email : item?.email,
                                    gstin : item?.compliances?.gstin_no

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
                    console.log(ui.item);
                    var currencyCode = ui.item.currency_code;
                    var customerType = ui.item.type;
                    var phoneNo = ui.item.phone_no;
                    var email = ui.item.email;
                    var gstIn = ui.item.gstin;
                    console.log(customerType);
                    $(this).data('customer', ui.item);
                    if (customerType === 'Cash') {
                        console.log('init check');
                        initializeCashCustomerPhoneDropdown();
                        initializeCashCustomerEmailDropdown();
                    } else {
                        console.log(ui.item);
                        if(phoneNo || email || ui.item){
                            enableDisableCustomerFields(false);
                            $("#item_customer_" + index).val(ui.item.code);
                            $("#item_customer_id_" + index).val(ui.item.id);
                            $("#item_customer_name_" + index).val(ui.item.name);
                            $("#item_mobile_" + index).val(phoneNo ? phoneNo : '');
                            $("#item_email_" + index).val(email ? email : '');
                        }
                        else{
                            deInitializeCashCustomerFlow();
                        }
                    }
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#item_customer_" + index).val('');
                        $("#item_customer_id_" + index).val('');
                        $("#item_customer_name_" + index).val('');
                        $("#item_mobile_" + index).val('');
                        $("#item_email_" + index).val('');
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
        }
        function initializeCashCustomerPhoneDropdown(selector, index)
        {
            enableDisableCustomerFields(false);
            $(`#${selector}`).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: 'cash_customer_phone_no',
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: item.phone_no || '',
                                    email: item.email || '',
                                    gstin: item.gstin || '',
                                    name: item.name || ''
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
                    $(`#item_email_${index}`).val(ui.item.email || '');
                    $(`#item_customer_name_${index}`).val(ui.item.name || '');
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val('');
                        $(`#item_email_${index}`).val('');
                        $(`#item_customer_name_${index}`).val('');
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
        }

        function initializeCashCustomerEmailDropdown(selector, index)
        {
            $(`#${selector}`).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: 'cash_customer_email',
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: item.email || '',
                                    phone_no: item.phone_no || '',
                                    gstin: item.gstin || '',
                                    name: item.name || ''
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
                    $(`#item_mobile_${index}`).val(ui.item.phone_no || '');
                    $(`#item_customer_name_${index}`).val(ui.item.name || '');
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val('');
                        $(`#item_mobile_${index}`).val('');
                        $(`#item_customer_name_${index}`).val('');
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
        }

        function deInitializeCashCustomerFlow(index)
        {
            let customerdata = document.getElementById(`#item_customer_${index}`).getAttribute('data-customer');

            $(`#item_mobile_${index}`).val(customerdata.phone_no || '');
            $(`#item_email_${index}`).val(customerdata.email || '');
            $(`#item_customer_name_${index}`).val(customerdata.name || '');
            $(`#item_customer_${index}`).val(customerdata.customer_code || '');
            $(`#item_customer_id_${index}`).val(customerdata.id || '')
            enableDisableCustomerFields(index,true);

        }
        
        function enableDisableCustomerFields(index,disabled = false)
        {
            if (disabled) {
                $(`#item_customer_name_${index}`).attr('readonly', true);
                $(`#item_mobile_${index}`).attr('readonly', true);
                $(`#item_email_${index}`).attr('readonly', true);
            } else {
                $(`#item_mobile_${index}`).removeAttr('readonly');
                $(`#item_mobile_${index}`).val('');
                $(`#item_email_${index}`).removeAttr('readonly');
                $(`#item_email_${index}`).val('');
                $(`#item_customer_name_${index}`).removeAttr('readonly');
                $(`#item_customer_name_${index}`).val('');
            }
            
        }
        function deleteItemRows()
        {
            var deletedItemIds = JSON.parse(localStorage.getItem('deletedSiItemIds'));
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
            localStorage.setItem('deletedSiItemIds', JSON.stringify(deletedItemIds));
            const allRowsNew = document.getElementsByClassName('item_row_checks');
            $("#item_count").val(allRowsNew.length);
            if (allRowsNew.length > 0) {
                disableHeader();
            } else {
                $(".Item_Search_section").show();
                enableHeader();
            }
            
        }

        function setItemRemarks(elementId) {
            const currentRemarksValue = document.getElementById(elementId).value;
            const modalInput = document.getElementById('current_item_remarks_input');
            modalInput.value = currentRemarksValue;
            modalInput.setAttribute('current-item', elementId);
        }

        function changeItemRemarks(element)
        {
            var newVal = element.value;
            newVal = newVal.substring(0,255);
            element.value = newVal;
            const elementToBeChanged = document.getElementById(element.getAttribute('current-item'));
            if (elementToBeChanged) {
                elementToBeChanged.value = newVal;
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

        function changeAllItemsTotal() //All items total value
        {
            const elements = document.getElementsByClassName('item_values_input');
            var totalValue = 0;
            for (let index = 0; index < elements.length; index++) {
                totalValue += parseFloat(elements[index].value ? elements[index].value : 0);
            }
            document.getElementById('all_items_total_value').innerText = (totalValue).toFixed(2);
        }
        function changeAllItemsDiscount() //All items total discount
        {
            const elements = document.getElementsByClassName('item_discounts_input');
            var totalValue = 0;
            for (let index = 0; index < elements.length; index++) {
                totalValue += parseFloat(elements[index].value ? elements[index].value : 0);
            }
            document.getElementById('all_items_total_discount').innerText = (totalValue).toFixed(2);
            changeAllItemsTotalTotal();
        }
        function changeAllItemsTotalTotal() //All items total
        {
            const elements = document.getElementsByClassName('item_totals_input');
            var totalValue = 0;
            for (let index = 0; index < elements.length; index++) {
                totalValue += parseFloat(elements[index].value ? elements[index].value : 0);
            }
            const totalElements = document.getElementsByClassName('all_tems_total_common');
            for (let index = 0; index < totalElements.length; index++) {
                totalElements[index].innerText = (totalValue).toFixed(2);
            }
        }

        function changeItemRate(element, index)
        {
            var inputNumValue = parseFloat(element.value ? element.value  : 0);
            // if (element.hasAttribute('max'))
            // {
            //     var maxInputVal = parseFloat(element.getAttribute('max'));
            //     if (inputNumValue > maxInputVal) {
            //         Swal.fire({
            //             title: 'Error!',
            //             text: 'Amount cannot be greater than ' + maxInputVal,
            //             icon: 'error',
            //         });
            //         element.value = (parseFloat(maxInputVal ? maxInputVal  : 0)).toFixed(2);
            //         itemRowCalculation(index);
            //         return;
            //     }
            // } 
        }

        $("#store_id_input").on('change', function() {
            const storeId = $(this).val();
            $("#item_header").html('');
            const sub_store_id = "{{ isset($order) && $order->sub_store_id ? $order->sub_store_id : '' }}";
            if (storeId) {
                $.ajax({
                    url: "{{ route('subStore.get.from.stores') }}",
                    method: 'GET',
                    dataType: 'json',
                    data: {
                    store_id: storeId,
                    types : "{{ App\Helpers\ConstantHelper::STOCKK }}",
                    },
                    success: function(data) {
                    console.log('Sub-stores fetched successfully:', data);
                    if (data.data && data.data.length > 0) {
                        let options = '<option value="" disabled selected>Select</option>';
                        data.data.forEach(function(subStore) {
                            options += `<option value="${subStore.id}" ${subStore.id == sub_store_id ? 'selected' : ''}>${subStore.name}</option>`;
                        });
                        $('#sub_store_id_input').empty().html(options);
                    }
                    else{
                        $('#sub_store_id_input').empty();
                        Swal.fire({
                            title: 'Error!',
                            text: 'No Store Found On this Location.',
                            icon: 'warning',
                        });
                    }
                    // Handle the response data as needed
                    },
                    error: function(xhr) {
                    console.error('Error fetching sub-stores:', xhr.responseText);
                    }
                });
            }
        });

        function changeItemQty(element, index)
        {
            const docType = $("#service_id_input").val();
            const invoiceToFollow = $("#invoice_to_follow_input").val() == "yes";
            var inputNumValue = parseFloat(element.value ? element.value  : 0);
            if (element.hasAttribute('max'))
            {
                var maxInputVal = parseFloat(element.getAttribute('max'));
                if (inputNumValue > maxInputVal) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Quantity cannot be greater than ' + maxInputVal,
                        icon: 'error',
                    });
                    element.value = (parseFloat(maxInputVal ? maxInputVal  : 0)).toFixed(4)
                    // return;
                }
            }
            if (element.hasAttribute('max-stock'))
            {
                var maxInputVal = parseFloat(element.getAttribute('max-stock'));
                if (inputNumValue > maxInputVal) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Qty cannot be greater than confirmed stock',
                        icon: 'error',
                    });
                    element.value = (parseFloat(maxInputVal ? maxInputVal  : 0)).toFixed(4)
                    // return;
                }
            }
            const currentVal = $("#store_id_input").val() + "-" + $("#item_sub_store_from_" + index).val();
            const otherVal = $("#store_to_id_input").val() + "-" + $("#item_sub_store_to_" + index).val();

            if (currentVal == otherVal)
            {
                Swal.fire({
                    title: 'Error!',
                    text: 'To and From Location cannot be same',
                    icon: 'error',
                });
                element.value = 0;
                return;
            }

            // getStoresData(index, element.value);

            // assignDefaultToLocationArray(index);
        }

        
        function addHiddenInput(id, val, name, classname, docId, dataId = null)
        {
            const newHiddenInput = document.createElement("input");
            newHiddenInput.setAttribute("type", "hidden");
            newHiddenInput.setAttribute("name", name);
            newHiddenInput.setAttribute("id", id);
            newHiddenInput.setAttribute("value", val);
            newHiddenInput.setAttribute("class", classname);
            newHiddenInput.setAttribute('data-id', dataId ? dataId : '');
            document.getElementById(docId).appendChild(newHiddenInput);
        }

        function renderIcons()
        {
            feather.replace()
        }

        function onItemClick(itemRowId)
        {
            const docType = $("#service_id_input").val();
            const invoiceToFollowParam = $("invoice_to_follow_input").val() == "yes";

            const hsn_code = document.getElementById('items_dropdown_'+ itemRowId).getAttribute('hsn_code');
            const item_name = document.getElementById('items_dropdown_'+ itemRowId).getAttribute('item-name');
            const attributes = JSON.parse(document.getElementById('items_dropdown_'+ itemRowId).getAttribute('attribute-array'));
            const specs = JSON.parse(document.getElementById('items_dropdown_'+ itemRowId).getAttribute('specs'));
            // const locations = JSON.parse(decodeURIComponent(document.getElementById('data_stores_'+ itemRowId).getAttribute('data-stores')));

            const qtDetailsRow = document.getElementById('current_item_qt_no_row');
            const qtDetails = document.getElementById('current_item_qt_no');

            //Reference From 
            const referenceFromLabels = document.getElementsByClassName("reference_from_label_" + itemRowId);
            if (referenceFromLabels && referenceFromLabels.length > 0)
            {
                qtDetailsRow.style.display = "table-row";
                referenceFromLabelsHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Reference From</strong>`;
                for (let index = 0; index < referenceFromLabels.length; index++) {
                    referenceFromLabelsHTML += `<span class="badge rounded-pill badge-light-primary">${referenceFromLabels[index].value}</span>`
                }
                qtDetails.innerHTML = referenceFromLabelsHTML;
            }
            else 
            {
                qtDetailsRow.style.display = "none";
                qtDetails.innerHTML = ``;
            }
            

            let qtDocumentNo = document.getElementById('qt_document_no_'+ itemRowId);
            let qtBookCode = document.getElementById('qt_book_code_'+ itemRowId);
            let qtDocumentDate = document.getElementById('qt_document_date_'+ itemRowId);

            qtDocumentNo = qtDocumentNo?.value ? qtDocumentNo.value : '';
            qtBookCode = qtBookCode?.value ? qtBookCode.value : '';
            qtDocumentDate = qtDocumentDate?.value ? qtDocumentDate.value : '';

            var innerHTMLAttributes = ``;
            attributes.forEach(element => {
                var currentOption = '';
                element.values_data.forEach(subElement => {
                    if (subElement.selected) {
                        currentOption = subElement.value;
                    }
                });
                innerHTMLAttributes +=  `<span class="badge rounded-pill badge-light-primary"><strong>${element.group_name}</strong>: ${currentOption}</span>`;
            });
            var specsInnerHTML = ``;
            specs.forEach(spec => {
                    specsInnerHTML +=  `<span class="badge rounded-pill badge-light-primary "><strong>${spec.specification_name}</strong>: ${spec.value ?? " "}</span>`;
            
            });

            document.getElementById('current_item_attributes').innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Attributes</strong>:` + innerHTMLAttributes;
            if (innerHTMLAttributes) {
                document.getElementById('current_item_attribute_row').style.display = "table-row";
            } else {
                document.getElementById('current_item_attribute_row').style.display = "none";
            }
            document.getElementById('current_item_specs').innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Specifications</strong>:` + specsInnerHTML;
            if (specsInnerHTML) {
                document.getElementById('current_item_specs_row').style.display = "table-row";
            } else {
                document.getElementById('current_item_specs_row').style.display = "none";
            }
            const remarks = document.getElementById('item_remarks_' + itemRowId).value;
            if (specsInnerHTML) {
                document.getElementById('current_item_specs_row').style.display = "table-row";
            } else {
                document.getElementById('current_item_specs_row').style.display = "none";
            }
            document.getElementById('current_item_description').textContent = remarks;
            if (remarks) {
                document.getElementById('current_item_description_row').style.display = "table-row";
            } else {
                document.getElementById('current_item_description_row').style.display = "none";
            }
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
                    const itemId = document.getElementById('items_dropdown_'+ itemRowId + '_value').value;
                    const uomId = document.getElementById('uom_dropdown_'+ itemRowId ).value;
                    if (itemId && uomId) {
                        $.ajax({
                            url: "{{route('get_item_inventory_details')}}",
                            method: 'GET',
                            dataType: 'json',
                            data: {
                                quantity: document.getElementById('item_qty_' + itemRowId).value,
                                item_id: document.getElementById('items_dropdown_'+ itemRowId + '_value').value,
                                uom_id : document.getElementById('uom_dropdown_' + itemRowId).value,
                                selectedAttr : selectedItemAttr,
                                store_id: $("#store_id_input").val(),
                                sub_store_id : $("#sub_store_id_input").val(),
                                service_alias : 'pds',
                                header_id : "{{isset($order) ? $order -> id : ''}}",
                                detail_id : $("#item_row_" + itemRowId).attr('data-detail-id')
                            },
                            success: function(data) {
                                
                                if (data?.item && data?.item?.category && data?.item?.sub_category) {
                                    document.getElementById('current_item_cat_hsn').innerHTML = `
                                    <span class="badge rounded-pill badge-light-primary"><strong>Category</strong>: <span id = "item_category">${ data?.item?.category?.name}</span></span>
                                    <span class="badge rounded-pill badge-light-primary"><strong>Sub Category</strong>: <span id = "item_sub_category">${ data?.item?.sub_category?.name}</span></span>
                                    <span class="badge rounded-pill badge-light-primary"><strong>HSN</strong>: <span id = "current_item_hsn_code">${hsn_code}</span></span>
                                    `;
                                }
                                //Stocks
                                console.log(data?.stocks);
                                //     if (data?.stocks) {
                                //     // document.getElementById('current_item_stocks_row').style.display = "table-row";
                                //     // document.getElementById('current_item_stocks').innerHTML = `
                                //     // <span class="badge rounded-pill badge-light-primary"><strong>Confirmed Stocks</strong>: <span id = "item_sub_category">${data?.stocks?.confirmedStockAltUom}</span></span>
                                //     // <span class="badge rounded-pill badge-light-primary"><strong>Pending Stocks</strong>: <span id = "item_category">${data?.stocks?.pendingStockAltUom}</span></span>
                                //     // `;
                                //     if (({{ isset($order) && in_array($order->document_status, [App\Helpers\ConstantHelper::DRAFT]) ? 'true' : 'false' }} || {{ !isset($order) ? 'true' : 'false' }})) {
                                //         $(`#item_qty_${itemRowId}`).val(data?.stocks?.confirmedStockAltUom  ?? 0.00);
                                //         if(!order && order.document_status !={{App\Helpers\ConstantHelper::DRAFT}})
                                //         {
                                //             getStoresData(itemRowId,data?.stocks?.confirmedStockAltUom ?? 0.00,false);
                                //         }
                                //     }
                                //     if(!$(`#item_variance_qty_${itemRowId}`).val() || (!$(`#item_physical_qty_${itemRowId}`).val() || $(`#item_physical_qty_${itemRowId}`).val() == 0)) {
                                //         $(`#item_variance_qty_${itemRowId}`).val(data?.stocks?.confirmedStockAltUom);
                                //     }
                                //     if(data.stocks.confirmedStocks)
                                //     {
                                //         $(`#item_rate_${itemRowId}`).attr('disabled',true);
                                //     }
                                //     else
                                //     {
                                //         $(`#item_rate_${itemRowId}`).attr('disabled',false);
                                //     }
                                //     var inputQtyBox = document.getElementById('item_qty_' + itemRowId);

                                //     inputQtyBox.setAttribute('max-stock',data.stocks.confirmedStockAltUom);
                                //     } 
                                //  else {
                                //         // document.getElementById('current_item_stocks_row').style.display = "none";
                                //     }

                                //     if (data?.lot_details) {
                                //     document.getElementById('current_item_lot_no_row').style.display = "table-row";
                                //     let lotHTML = `<strong style="font-size:11px; color : #6a6a6a;">Lot Number</strong> : `;
                                //     let soHTML = `<strong style="font-size:11px; color : #6a6a6a;">SO Number</strong> : `;
                                //     const soNoGroups = {};
                                //     data?.lot_details.forEach(lot => {
                                //         if (lot.so_no) {
                                //             if (!soNoGroups[lot.so_no]) {
                                //                 soNoGroups[lot.so_no] = 0;
                                //             }
                                //             soNoGroups[lot.so_no] += Number(lot.quantity ?? 0);
                                //         }
                                //         lotHTML += `<span class="badge rounded-pill badge-light-primary"><strong>${lot?.lot_number}</strong>: <span>${lot?.quantity}</span></span>`
                                //     });

                                //     for (const [soNo, totalQty] of Object.entries(soNoGroups)) {
                                //         soHTML += `<span class="badge rounded-pill badge-light-primary"><strong>${soNo}</strong> : ${totalQty}</span>`;
                                //     }

                                //     document.getElementById('current_item_lot_no').innerHTML = lotHTML;
                                //     document.getElementById('current_item_so_no').innerHTML = soHTML;
                                //     } 
                                //  else {
                                //         document.getElementById('current_item_lot_no_row').style.display = "none";
                                //     }


                                    
                            },
                            error: function(xhr) {
                                console.error('Error fetching customer data:', xhr.responseText);
                            }
                        });
                    }
        }

        function setVariance(element,index)
        {
            const currentQty = parseFloat(element.value) || 0; // Get the current element's value
            const variance = $(`#item_variance_qty_${index}`); // Get the next <td> element
            const confirmedQty = $(`#item_qty_${index}`).val();
            if (variance) {
                if (currentQty) {
                    const varianceQty = parseFloat(variance.val()) || 0; // Get the next input's value
                    variance.val((currentQty - confirmedQty ).toFixed(4)); // Subtract and update the value
                    
                }
                else{
                    variance.val(confirmedQty);
                }
            }
        }
        function setValue(index)
        {
            const currentQty = $(`#item_physical_qty_${index}`).val();
            const currentRate = $(`#item_rate_${index}`).val();
            const variance = $(`#item_variance_qty_${index}`); // Get the next <td> element
            const value = currentQty * currentRate;
            $(`#item_value_${index}`).val(value);
            selectedName = $("#pickup_item_id_" + index).val();
            selectedValue = {'physical_qty': currentQty, 'rate': currentRate, 'variance': variance.val()};
            if (selectedValue) {
                changed_item[selectedName] = selectedValue;
            } else {
                delete changed_item[selectedName];
            }
            console.log(changed_item);
        }

        function getStoresData(itemRowId, qty = null, callOnClick = true)
        {
            const qtyElement = document.getElementById('item_qty_' + itemRowId);
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
                                store_id: $("#store_id_input").val(),
                                sub_store_id : null, //$("#sub_store_id_input").val()
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
                                        totalRate = parseFloat(totalValue) / parseFloat(qty ? qty : qtyElement.value); 
                                        rateInput.value = parseFloat(totalRate).toFixed(2);
                                    } else {
                                        rateInput.value = 0.00;
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
                                    rateInput.value = 0.00;
                                    valueInput.value = 0.00;
                                } else {
                                    // storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify([])));
                                    if (callOnClick) {
                                        onItemClick(itemRowId, callOnClick);
                                    }
                                    if(!rateInput.value){
                                        rateInput.value = 0.00;
                                        valueInput.value = 0.00;
                                    }
                                    rateInput.removeAttribute('disabled');
                                }
                                openStoreLocationModal(itemRowId);
                            },
                            error: function(xhr) {
                                console.error('Error fetching customer data:', xhr.responseText);
                                storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify([])));
                                rateInput.value = 0.00;
                                valueInput.value = 0.00;

                            }
                        });
        }

        function openStoreLocationModal(index)
        {
            const storeElement = document.getElementById('data_stores_' + index);
            const storeTable = document.getElementById('item_from_location_table');
            let storeFooter = `
            <tr> 
                <td colspan="3"></td>
                <td class="text-dark"><strong>Total</strong></td>
                <td class="text-dark" id = "total_item_store_qty"><strong>0.00</strong></td>                                   
            </tr>
            `;
            if (storeElement) {
                storeTable.setAttribute('current-item-index', index);
                let storesInnerHtml = ``;
                let totalStoreQty = 0;
                const storesData = JSON.parse(decodeURIComponent(storeElement.getAttribute('data-stores')));
                if (storesData && storesData.length > 0)
                {
                    storesData.forEach((store, storeIndex) => {
                        storesInnerHtml += `
                        <tr id = "item_store_${storeIndex}">
                            <td>${storeIndex + 1}</td> 
                            <td>${store.rack_code ? store.rack_code : "N/A"}</td>
                            <td>${store.shelf_code ? store.shelf_code : "N/A"}</td>
                            <td>${store.bin_code ? store.bin_code : "N/A"}</td>
                            <td>${store.qty}</td>
                        </tr>
                        `;
                        totalStoreQty += (parseFloat(store.qty ? store.qty : 0))
                    });

                    storeTable.innerHTML = storesInnerHtml + storeFooter;
                    document.getElementById('total_item_store_qty').textContent = totalStoreQty.toFixed(2);

                } else {
                    storeTable.innerHTML = storesInnerHtml + storeFooter;
                    document.getElementById('total_item_store_qty').textContent = "0.00";
                }
            } else {
                return;
            }
            renderIcons();
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
            changed_item= {};
            selectedValues = {};
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
    
    function disableHeader()
    {
        const disabledFields = document.getElementsByClassName('disable_on_edit');
        for (let disabledIndex = 0; disabledIndex < disabledFields.length; disabledIndex++) {
            disabledFields[disabledIndex].disabled = true;
        }
        const disabledField = document.getElementsByClassName('except_draft');
        if($("#document_status").val() == "{{App\Helpers\ConstantHelper::DRAFT}}") {
            for (let disabledIndex = 0; disabledIndex < disabledField.length; disabledIndex++) {
                disabledField[disabledIndex].disabled = false;
            }
        } 
    }

    function enableHeader()
    {
        const disabledFields = document.getElementsByClassName('disable_on_edit');
            for (let disabledIndex = 0; disabledIndex < disabledFields.length; disabledIndex++) {
                disabledFields[disabledIndex].disabled = false;
            }
        // let siButton = document.getElementById('select_si_button');
        // if (siButton) {
        //     siButton.disabled = false;
        // }
        let piButton = document.getElementById('select_pi_button');
        if (piButton) {
            piButton.disabled = false;
        }
        let leaseButton = document.getElementById('select_pwo_button');
        if (leaseButton) {
            leaseButton.disabled = false;
        }
        let orderButton = document.getElementById('select_mfg_button');
        if (orderButton) {
            orderButton.disabled = false;
        }
    }

    //Function to set values for edit form
    function editScript()
    {
        localStorage.setItem('deletedItemDiscTedIds', JSON.stringify([]));
        localStorage.setItem('deletedHeaderDiscTedIds', JSON.stringify([]));
        localStorage.setItem('deletedHeaderExpTedIds', JSON.stringify([]));
        localStorage.setItem('deletedSiItemIds', JSON.stringify([]));
        localStorage.setItem('deletedAttachmentIds', JSON.stringify([]));

        const items = @json(isset($items) ? $items : null);
        if (items) {
            //Disable header fields which cannot be changed
            disableHeader();
            // if ($("#store_id_input").length) {
            //     $("#store_id_input").trigger('change');
            // }
            //Item Discount
            items.forEach((item, itemIndex) => {
                itemUomsHTML = ``;
                if (item.item.uom && item.item.uom.id) {
                    itemUomsHTML += `<option selected value = '${item.item.uom.id}' ${item.item.uom.id == item.uom_id ? "selected" : ""}>${item.item.uom.alias}</option>`;
                }
                document.getElementById('uom_dropdown_' + itemIndex).innerHTML = itemUomsHTML;
                setAttributesUI(itemIndex);
                onItemClick(itemIndex);
            });
            //Disable header fields which cannot be changed
            disableHeader();
            //Set all documents
            // order.media_files.forEach((mediaFile, mediaIndex) => {
            //     appendFilePreviews(mediaFile.file_url, 'main_order_file_preview', mediaIndex, mediaFile.id, order.document_status == 'draft' ? false : true);
            // });
        }
        renderIcons();
        
        let finalAmendSubmitButton = document.getElementById("amend-submit-button");

        viewModeScript(finalAmendSubmitButton ? false : true);

    }
    editScript();
    document.addEventListener('DOMContentLoaded', function() {
        const order = @json(isset($order) ? $order : null);
        onServiceChange(document.getElementById('service_id_input'), order ? false : true);

        initializeAutocompleteAutoUser("user_id_dropdown");
    });

    function resetParametersDependentElements(reset = true)
    {
        var selectionSection = document.getElementById('selection_section');
        if (selectionSection) {
            selectionSection.style.display = "none";
        }
        let addItemSec = document.getElementById('add_item_section');
        if (addItemSec) addItemSec.style.display = "none";
        $("#order_date_input").attr('max', "{{$current_financial_year['end_date']}}");
        $("#order_date_input").attr('min', "{{$current_financial_year['start_date']}}");
        $("#order_date_input").off('input');
        if (reset) {
            $("#order_date_input").val(moment().format("YYYY-MM-DD"));
        }        
        $('#order_date_input').on('input', function() {
            restrictBothFutureAndPastDates(this);
        });
    }

    function getDocNumberByBookId(element, reset = true) 
    {
        resetParametersDependentElements(reset);
        let bookId = element.value;
        let actionUrl = '{{route("book.get.doc_no_and_parameters")}}'+'?book_id='+bookId + "&document_date=" + $("#order_date_input").val();
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                  $("#book_code_input").val(data.data.book_code);
                if(!data.data.doc.document_number) {
                    if (reset) {
                        $("#order_no_input").val('');
                    }
                }
                if (reset) {
                $("#order_no_input").val(data.data.doc.document_number);
                }
                if(data.data.doc.type == 'Manually') {
                    $("#order_no_input").attr('readonly', false);
                } else {
                    $("#order_no_input").attr('readonly', true);
                }
                enableDisableQtButton();
                if (data.data.parameters)
                {
                implementBookParameters(data.data.parameters);
                }
                if (reset) {
                    implementBookDynamicFields(data.data.dynamic_fields_html, data.data.dynamic_fields);
                }
                }
                if(data.status == 404) {
                    if (reset) {
                        $("#book_code_input").val("");
                        // alert(data.message);
                    }
                    enableDisableQtButton();
                }
                if(data.status == 500) {
                    if (reset) {
                        $("#book_code_input").val("");
                        $("#series_id_input").val("");
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                        });
                    }
                    enableDisableQtButton();
                }
                if (reset == false) {
                    viewModeScript();
                }
            });
        }); 
    }
    function implementBookDynamicFields(html, data)
    {
        let dynamicBookSection = document.getElementById('dynamic_fields_section');
        dynamicBookSection.innerHTML = html;
        if (data && data.length > 0) {
            dynamicBookSection.classList.remove('d-none');
        } else {
            dynamicBookSection.classList.add('d-none');
        }
    }
    function onDocDateChange()
    {
        let bookId = $("#series_id_input").val();
        let actionUrl = '{{route("book.get.doc_no_and_parameters")}}'+'?book_id='+bookId + "&document_date=" + $("#order_date_input").val();
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                  $("#book_code_input").val(data.data.book_code);
                  if(!data.data.doc.document_number) {
                     $("#order_no_input").val('');
                  }
                  $("#order_no_input").val(data.data.doc.document_number);
                  if(data.data.doc.type == 'Manually') {
                     $("#order_no_input").attr('readonly', false);
                  } else {
                     $("#order_no_input").attr('readonly', true);
                  }
                }
                if(data.status == 404) {
                    $("#book_code_input").val("");
                    alert(data.message);
                }
            });
        });
    }

    let requesterTypeParam = "{{isset($order) ? $order -> requester_type : 'Department'}}";

    function implementBookParameters(paramData)
    {
        var selectedRefFromServiceOption = paramData.reference_from_service;
        console.log("Selected Ref From Service Option: ", selectedRefFromServiceOption);
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
                    if (selectSingleVal == 'd') {
                        let addItemSection = document.getElementById('add_item_section');
                        if (addItemSection) {
                            addItemSection.style.display = "";
                        }
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
        console.log("Back Date Allow: ", backDateAllow);
        console.log("Future Date Allow: ", futureDateAllow);
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
            if (issueTypeInput) {
                var issueTypeHtml = ``;
                var firstIssueType = null;
                issueTypeParameters.forEach((issueType, issueTypeIndex) => {
                    if (issueTypeIndex == 0) {
                        firstIssueType = issueType;
                    }
                    issueTypeHtml += `<option value = '${issueType}'> ${issueType} </option>`
                });
                if ("{{isset($order)}}") {
                    firstIssueType = "{{isset($order) ? $order -> issue_type : ''}}";
                }
                issueTypeInput.innerHTML = issueTypeHtml;
                requesterTypeParam = paramData?.requester_type?.[0];
                $("#requester_type_input").val(requesterTypeParam);
                // $("#issue_type_input").val(firstIssueType).trigger('input');
                let editCase = "{{isset($order) ? 'false' : 'true'}}";
                onIssueTypeChange(document.getElementById('issue_type_input'), editCase == 'false' ? false : true);
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
        const otherField = $("#store_id_input").val();

        if (bookId && bookCode && documentDate && otherField) {
        //     let siButton = document.getElementById('select_si_button');
        //     if (siButton) {
        //         siButton.disabled = false;
        //     }
            let piButton = document.getElementById('select_pi_button');
            if (piButton) {
                piButton.disabled = false;
            }
            let leaseButton = document.getElementById('select_pwo_button');
            if (leaseButton) {
                leaseButton.disabled = false;
            }
            let orderButton = document.getElementById('select_mfg_button');
            if (orderButton) {
                orderButton.disabled = false;
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

    function openHeaderPullModal(type = 'pi')
    {
        console.log('openHeaderPullModal called with type:', type);
        if (type === 'mo') {
            openPullType = "mo";
            $("#rescdule").modal('show');
        } else if (type === 'pwo') {
            openPullType = "pwo";
            $("#rescdulePwo").modal('show');
        } else if (type === 'jo') {
            openPullType = "jo";
            $("#rescduleJo").modal('show');
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

    function getOrders(type = "pi")
    {
        let departmentOrStoreKey = 'store_location_code';
        let tableSelector = '#mo_orders_table';
        let docType = `#${type}_header_pull`;
        if (type === 'pwo') {
            tableSelector = '#pwo_orders_table';
        } else if (type === 'pi') {
            departmentOrStoreKey = 'department_code';
            tableSelector = '#pi_orders_table';
        } else if (type == "jo") {
            tableSelector = '#jo_orders_table';
        }

        // Build selected IDs
        const selectedIds = Array.from(document.querySelectorAll(".item_row_checks"))
            .filter(checkbox => checkbox.checked)
            .map(checkbox => {
                const index = checkbox.getAttribute("del-index");
                const idInput = document.getElementById(`${type}_id_${index}`);
                return idInput ? idInput.value : null;
            })
            .filter(id => id);


        const apiUrl = "{{ route('pds.get.items') }}";

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
                            pi-item-id="${row.id}"
                            balance_qty="${row.pickup_balance_qty}">
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
        } else if (type === 'mo') {
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
            { data: 'balance_qty', name: 'balance_qty', render: renderData, className: 'no-wrap numeric-alignment' }
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
            "PDS Items - " + type.toUpperCase(),
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
        const apiUrl = "{{route('pds.process.items')}}";
        // const apiUrl = "{{('pds.process.items')}}";
        let docId = [];
        let soItemsId = [];
        let qties = [];
        let documentDetails = [];
        for (let index = 0; index < allCheckBoxes.length; index++) {
            if (allCheckBoxes[index].checked) {
                docId.push(allCheckBoxes[index].getAttribute('document-id'));
                soItemsId.push(allCheckBoxes[index].getAttribute('pi-item-id'));
                qties.push(allCheckBoxes[index].getAttribute('balance_qty'));
                console.log(allCheckBoxes);
                documentDetails.push({
                    'pi_item_ids' : allCheckBoxes[index].getAttribute('document-id'),
                    'quantity' : allCheckBoxes[index].getAttribute('pickup_balance_qty'),
                    'item_id' : allCheckBoxes[index].getAttribute('pi-item-id')
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
                    store_id : $("#store_id_input").val()
                },
                success: function(data) {
                    const currentOrders = data.data;
                    let itemRowIndex = document.querySelectorAll('.item_header_rows').length;
                    const tbody = document.getElementById('item_header');
                    let currentOrderIndexVal = document.getElementsByClassName('item_header_rows').length;
                    currentOrders.forEach((currentOrder) => {
                        currentOrder.items.forEach((item) => {
                            console.log(item);
                            let itemAttrs = JSON.stringify(item.item_attributes_array || []);
                            let specs = JSON.stringify(item.item?.specifications || []);
                            tbody.innerHTML += `
                            <tr id="item_row_${itemRowIndex}" class="item_header_rows" onclick="onItemClick('${itemRowIndex}');">
                                <input type="hidden" id="${openPullType}_item_id_${itemRowIndex}" name="${openPullType}_item_ids[]" value="${JSON.stringify(item.item_ids)}">
                                <input type="hidden" id="${openPullType}_id_${itemRowIndex}" name="${openPullType}_ids[]" value="${JSON.stringify(item.header_ids)}">
                                <td class="customernewsection-form">
                                    <div class="form-check form-check-primary custom-checkbox">
                                        <input type="checkbox" class="form-check-input item_row_checks" id="item_checkbox_${itemRowIndex}" del-index="${itemRowIndex}">
                                        <label class="form-check-label" for="item_checkbox_${itemRowIndex}"></label>
                                    </div>
                                </td>
                                <td class="poprod-decpt">
                                    <input type="text" id="items_dropdown_${itemRowIndex}" name="item_code[]" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" autocomplete="off"
                                        data-name="${item.item?.item_name}" data-code="${item.item?.item_code}" data-id="${item.item_id}"
                                        hsn_code="${item.item?.hsn?.code}" item-name="${item.item?.item_name}" specs='${specs}' attribute-array='${itemAttrs}' 
                                        value="${item.item?.item_code}" readonly item-location="[]">
                                    <input type="hidden" name="item_id[]" id="items_dropdown_${itemRowIndex}_value" value="${item.item_id}">
                                </td>
                                <td class="poprod-decpt">
                                    <input type="text" id="items_name_${itemRowIndex}" class="form-control mw-100" value="${item.item?.item_name}" name="item_name[]" readonly>
                                </td>
                                <td class="poprod-decpt" id="attribute_section_${itemRowIndex}">
                                    <button id="attribute_button_${itemRowIndex}" ${item.item_attributes_array.length > 0 ? '' : 'disabled'}
                                        type="button" data-bs-toggle="modal" onclick="setItemAttributes('items_dropdown_${itemRowIndex}', '${itemRowIndex}', true);" data-bs-target="#attribute"
                                        class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                                    <input type="hidden" name="attribute_value_${itemRowIndex}">
                                </td>
                                <td>
                                    <select class="form-select" name="uom_id[]" id="uom_dropdown_${itemRowIndex}">
                                        <option value="${item.item.uom?.id}" selected>${item.item.uom?.alias}</option>
                                    </select>
                                </td>
                                <td class="numeric-alignment">
                                    <input type="text" id="item_qty_${itemRowIndex}" value="${item.pickup_balance_qty}" oninput='changeItemQty(this,'${itemRowIndex}')' name="item_qty[]"
                                        max="${item.pickup_balance_qty}" class="form-control mw-100 text-end">
                                </td>
                                <td>
                                    <input type="text" id="item_remarks_${itemRowIndex}" name="item_remarks[]" class="form-control mw-100" value="${item.remarks || ''}">
                                </td>
                            </tr>`;
                            initializeAutocomplete1("items_dropdown_" + itemRowIndex, itemRowIndex);
                            setAttributesUI(itemRowIndex);
                            itemRowIndex++;
                        });
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
    $("input[id^='item_qty_']").on("input", function () {
        const max = parseFloat($(this).attr("max"));
        const val = parseFloat($(this).val());
        console.log("max:", max, "val:", val); // Log for debugging
        if (!isNaN(val) && !isNaN(max) && val > max) {
            $(this).val(max);
            Swal.fire({
                title: 'Warning!',
                text: 'Please ensure that the quantity does not exceed the maximum limit.',
                icon: 'warning',
            });
        }
    });


    // editScript();
    
    function checkItemAddValidation()
    {
        let addRow = $('#series_id_input').val &&  $('#order_no_input').val && $('#order_date_input').val;
        return addRow;
    }

    function setApproval()
    {
        document.getElementById('action_type').value = "approve";
        document.getElementById('approve_reject_heading_label').textContent = "Approve " + "Request";

    }
    function setReject()
    {
        document.getElementById('action_type').value = "reject";
        document.getElementById('approve_reject_heading_label').textContent = "Reject " + "Request";
    }
    function setFormattedNumericValue(element)
    {
        element.value = (parseFloat(element.value ? element.value  : 0)).toFixed(4)
    }

    function initializeAutocompleteQt(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "") {
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
                                    label: `${item[labelKey1]} ${item[labelKey2] ? '(' + item[labelKey2] + ')' : ''}`,
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
                this.value = this.value.substring(0, this.value.indexOf('.') + 3);
            }
        });
    });

    
    $(document).on('click', '#amendmentSubmit', (e) => {
   let actionUrl = "{{ route('sale.order.amend', isset($order) ? $order -> id : 0) }}";
   fetch(actionUrl).then(response => {
      return response.json().then(data => {
         if (data.status == 200) {
            Swal.fire({
                title: 'Success!',
                text: data.message,
                icon: 'success'
            });
            location.reload();
         } else {
            Swal.fire({
                title: 'Error!',
                text: data.message,
                icon: 'error'
            });
        }
      });
   });
});

var currentRevNo = $("#revisionNumber").val();

// # Revision Number On Change
$(document).on('change', '#revisionNumber', (e) => {
    e.preventDefault();
    let actionUrl = location.pathname + '?type=' + "{{request() -> type ?? 'si'}}" + '&revisionNumber=' + e.target.value;
    $("#revisionNumber").val(currentRevNo);
    window.open(actionUrl, '_blank'); // Opens in a new tab
});

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



function viewModeScript(disable = true)
{
    const currentOrder = @json(isset($order) ? $order : null);
    const editOrder = "{{( isset($buttons) && ($buttons['draft'] || $buttons['submit'])) ? false : true}}";
    const revNoQuery = "{{ isset(request() -> revisionNumber) ? true : false }}";

    if ((editOrder || revNoQuery) && currentOrder) {
        document.querySelectorAll('input, textarea, select').forEach(element => {
            if (element.id !== 'revisionNumber' && element.type !== 'hidden' && !element.classList.contains('cannot_disable')) {
                // element.disabled = disable;
                element.style.pointerEvents = disable ? "none" : "auto";
                if (disable) {
                    element.setAttribute('readonly', true);
                } else {
                    element.removeAttribute('readonly');
                }
            }
        });
        //Disable all submit and cancel buttons
        document.querySelectorAll('.can_hide').forEach(element => {
            element.style.display = disable ? "none" : "";
        });
        //Remove add delete button
        document.getElementById('add_delete_item_section').style.display = disable ? "none" : "";
    } else {
        return;
    }
}

function amendConfirm()
{
    viewModeScript(false);
    disableHeader();
    const amendButton = document.getElementById('amendShowButton');
    if (amendButton) {
        amendButton.style.display = "none";
    }
    //disable other buttons
    var printButton = document.getElementById('dropdownMenuButton');
    if (printButton) {
        printButton.style.display = "none";
    }
    var postButton = document.getElementById('postButton');
    if (postButton) {
        postButton.style.display = "none";
    }
    const buttonParentDiv = document.getElementById('buttonsDiv');
    const newSubmitButton = document.createElement('button');
    newSubmitButton.type = "button";
    newSubmitButton.id = "amend-submit-button";
    newSubmitButton.className = "btn btn-primary btn-sm mb-50 mb-sm-0";
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

    reCheckEditScript();
}

function reCheckEditScript()
    {
        const currentOrder = @json(isset($order) ? $order : null);
        if (currentOrder) {
            currentOrder.items.forEach((item, index) => {
                document.getElementById('item_checkbox_' + index).disabled = item?.is_editable ? false : true;
                document.getElementById('items_dropdown_' + index).readonly = item?.is_editable ? false : true;
                document.getElementById('attribute_button_' + index).disabled = item?.is_editable ? false : true;
            });
        }
    }

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

let isProgrammaticChange = false; // Flag to prevent recursion

function onPostVoucherOpen(type = "not_posted")
{
    resetPostVoucher();
    const apiURL = "";
    // const apiURL = "{{('pds.posting.get')}}";
    $.ajax({
        url: apiURL + "?book_id=" + $("#series_id_input").val() + "&document_id=" + "{{isset($order) ? $order -> id : ''}}" + "&type=" + (type == "not_posted" ? 'get' : 'view'),
        type: "GET",
        dataType: "json",
        success: function(data) {
            if (!data.data.status) {
                Swal.fire({
                    title: 'Error!',
                    text: data.data.message,
                    icon: 'error',
                });
                return;
            }
            const voucherEntries = data.data.data;
            var voucherEntriesHTML = ``;
            Object.keys(voucherEntries.ledgers).forEach((voucher) => {
                voucherEntries.ledgers[voucher].forEach((voucherDetail, index) => {
                    voucherEntriesHTML += `
                    <tr>
                    <td>${voucher}</td>   
                    <td class="fw-bolder text-dark">${voucherDetail.ledger_group_code ? voucherDetail.ledger_group_code : ''}</td> 
                    <td>${voucherDetail.ledger_code ? voucherDetail.ledger_code : ''}</td>
                    <td>${voucherDetail.ledger_name ? voucherDetail.ledger_name : ''}</td>
                    <td class="text-end">${voucherDetail.debit_amount > 0 ? parseFloat(voucherDetail.debit_amount).toFixed(2) : ''}</td>
                    <td class="text-end">${voucherDetail.credit_amount > 0 ? parseFloat(voucherDetail.credit_amount).toFixed(2) : ''}</td>
					</tr>
                    `
                });
            });
            voucherEntriesHTML+= `
            <tr>
                <td colspan="4" class="fw-bolder text-dark text-end">Total</td>   
                <td class="fw-bolder text-dark text-end">${voucherEntries.total_debit.toFixed(2)}</td> 
                <td class="fw-bolder text-dark text-end">${voucherEntries.total_credit.toFixed(2)}</td>
			</tr>
            `;
            document.getElementById('posting-table').innerHTML = voucherEntriesHTML;
            document.getElementById('voucher_doc_no').value = voucherEntries.document_number;
            document.getElementById('voucher_date').value = moment(voucherEntries.document_date).format('D/M/Y');
            document.getElementById('voucher_book_code').value = voucherEntries.book_code;
            document.getElementById('voucher_currency').value = voucherEntries.currency_code;
            if (type === "posted") {
                document.getElementById('posting_button').style.display = 'none';
            } else {
                document.getElementById('posting_button').style.removeProperty('display');
            }
            $('#postvoucher').modal('show');
        }
    });
    
}

function resetPostVoucher()
{
    document.getElementById('voucher_doc_no').value = '';
    document.getElementById('voucher_date').value = '';
    document.getElementById('voucher_book_code').value = '';
    document.getElementById('voucher_currency').value = '';
    document.getElementById('posting-table').innerHTML = '';
    document.getElementById('posting_button').style.display = 'none';
}

function postVoucher(element)
{
    const bookId = "{{isset($order) ? $order -> book_id : ''}}";
    const documentId = "{{isset($order) ? $order -> id : ''}}";
    const postingApiUrl = "";
    // const postingApiUrl = "{{('pds.post')}}";
    if (bookId && documentId) {
        $.ajax({
            url: postingApiUrl,
            type: "POST",
            dataType: "json",
            contentType: "application/json", // Specifies the request payload type
            data: JSON.stringify({
                // Your JSON request data here
                book_id: bookId,
                document_id: documentId,
            }),
            success: function(data) {
                const response = data.data;
                if (response.status) {
                    Swal.fire({
                        title: 'Success!',
                        text: response.message,
                        icon: 'success',
                    });
                    location.reload();
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: response.message,
                        icon: 'error',
                    });
                }
            }, 
            error: function(jqXHR, textStatus, errorThrown) {
                let errorReponse = jqXHR.responseJSON;
                if (errorReponse?.data?.message) {
                    Swal.fire({
                        title: 'Error!',
                        text: errorReponse?.data?.message,
                        icon: 'error',
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Some internal error occured',
                        icon: 'error',
                    });
                }
                
            }
        });

    }
}

document.addEventListener('input', function (e) {
    if (e.target.classList.contains('text-end')) {
        if (isProgrammaticChange) {
            return; // Prevent recursion
        }
        let value = e.target.value;

        // Remove invalid characters (anything other than digits and a single decimal)
        value = value.replace(/[^0-9.]/g, '');

        // Prevent more than one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts[1];
        }

        // Prevent starting with a decimal (e.g., ".5" -> "0.5")
        if (value.startsWith('.')) {
            value = '0' + value;
        }

        // Limit to 2 decimal places
        if (parts[1]?.length > 2) {
            value = parts[0] + '.' + parts[1].substring(0, 2);
        }

        // Prevent exceeding the max limit
        const maxNumericLimit = 9999999; // Define your max limit here
        if (value && Number(value) > maxNumericLimit) {
            value = maxNumericLimit.toString();
        }
        isProgrammaticChange = true; // Set flag before making a programmatic change
        // Update the input's value
        e.target.value = value;

        // Manually trigger the change event
        const event = new Event('input', { bubbles: true });
        e.target.dispatchEvent(event);
        const event2 = new Event('change', { bubbles: true });
        e.target.dispatchEvent(event2);
        isProgrammaticChange = false; // Reset flag after programmatic change
    }
});

    document.addEventListener('keydown', function (e) {
        if (e.target.classList.contains('text-end')) {
            if ( e.key === 'Tab' ||
                ['Backspace', 'ArrowLeft', 'ArrowRight', 'Delete', '.'].includes(e.key) || 
                /^[0-9]$/.test(e.key)
            ) {
                // Allow numbers, navigation keys, and a single decimal point
                return;
            }
            e.preventDefault(); // Block everything else
        }
    });


    function resetSeries()
    {
        document.getElementById('series_id_input').innerHTML = '';
    }
    
    function onServiceChange(element, reset = true)
    {
        resetSeries();
        $.ajax({
            url: "{{route('book.service-series.get')}}",
            method: 'GET',
            dataType: 'json',
            data: {
                menu_alias: "{{request() -> segments()[0]}}",
                service_alias: element.value,
                book_id : reset ? null : "{{isset($order) ? $order -> book_id : null}}"
            },
            success: function(data) {
                if (data.status == 'success') {
                    let newSeriesHTML = ``;
                    data.data.forEach((book, bookIndex) => {
                        newSeriesHTML += `<option value = "${book.id}" ${bookIndex == 0 ? 'selected' : ''} >${book.book_code}</option>`;
                    });
                    document.getElementById('series_id_input').innerHTML = newSeriesHTML;
                    getDocNumberByBookId(document.getElementById('series_id_input'), reset);
                } else {
                    document.getElementById('series_id_input').innerHTML = '';
                }
            },
            error: function(xhr) {
                console.error('Error fetching customer data:', xhr.responseText);
                document.getElementById('series_id_input').innerHTML = '';
            }
        });
    }

    function revokeDocument()
    {
        const orderId = "{{isset($order) ? $order -> id : null}}";
        if (orderId) {
            $.ajax({
            url: "{{route('pds.revoke')}}",
            method: 'POST',
            dataType: 'json',
            data: {
                id : orderId
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
                    window.location.href = "{{$redirect_url}}";
                }
            },
            error: function(xhr) {
                console.error('Error fetching customer data:', xhr.responseText);
                Swal.fire({
                    title: 'Error!',
                    text: 'Some internal error occured',
                    icon: 'error',
                });
            }
        });
        }
    }

    function resetIssueTypeFields()
    {
        $("#store_to_id_input").val('');
        $("#vendor_id_input").val('');
        $("#vendor_store_id_input").val('');
        $("#department_id_input").val('');
        $("#user_id_dropdown").val('');
        $("#station_id_input").val('');
    }
    
    function checkAllMo(element)
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

    function submitAttr(id) {
        var item_index = $('#attributes_table_modal').attr('item-index');
        onItemClick(item_index);
        const input = document.getElementById('item_physical_qty_' + item_index);
        // if(!(order && !order.document_status=={{App\Helpers\ConstantHelper::DRAFT}}))
        // {
        //     getStoresData(item_index, input ? (input.value ?? 0) : 0);
        // }
        setAttributesUI(item_index);
        closeModal(id);
    }

    $('#attribute').on('hidden.bs.modal', function () {
    setAttributesUI();
    });
    var currentSelectedItemIndex = null ;
    function setAttributesUI(paramIndex = null) {
        let currentItemIndex = null;
        if (paramIndex != null || paramIndex != undefined) {
            currentItemIndex = paramIndex;
        } else {
            currentItemIndex = currentSelectedItemIndex;
        }
        //Attribute modal is closed
        let itemIdDoc = document.getElementById('items_dropdown_' + currentItemIndex);
        if (!itemIdDoc) {
            return;
        }
        //Item Doc is found
        let attributesArray = itemIdDoc.getAttribute('attribute-array');
        if (!attributesArray) {
            return;
        }
        attributesArray = typeof attributesArray === 'string' ? JSON.parse(decodeURIComponent(attributesArray)) : attributesArray;
        if (attributesArray.length == 0) {
            return;
        }
        let attributeUI = `<div data-bs-toggle="modal" id="attribute_button_${currentItemIndex}" onclick = "setItemAttributes('items_dropdown_${currentItemIndex}', ${currentItemIndex},{{ isset($order) && !$order->document_status==App\Helpers\ConstantHelper::DRAFT ? 'true' : '' }});" data-bs-target="#attribute" style = "white-space:nowrap; cursor:pointer;">`;
        let maxCharLimit = 15;
        let attrTotalChar = 0;
        let total_selected = 0;
        let total_atts = 0;
        let addMore = true;
        attributesArray.forEach(attrArr => {
            if (!addMore) {
                return;
            }
            let short = false;
            total_atts += 1;

            if(attrArr?.short_name?.length > 0)
            {
                short = true;
            }
            //Retrieve character length of attribute name
            let currentStringLength = short ? Number(attrArr?.short_name?.length) : Number(attrArr?.group_name?.length);
            let currentSelectedValue = '';
            attrArr.values_data.forEach((attrVal) => {
                if (attrVal.selected === true) {
                    total_selected += 1;
                    // Add character length with selected value
                    currentStringLength += Number(attrVal.value.length);
                    currentSelectedValue = attrVal.value;
                }
            });
            //Add the attribute in UI only if it falls within the range
            if ((attrTotalChar + Number(currentStringLength)) <= 50) {
                attributeUI += `
                <span class="badge rounded-pill badge-light-primary"><strong>${short ? attrArr?.short_name : attrArr?.group_name}</strong>: ${currentSelectedValue ? currentSelectedValue :''}</span>
                `;
            } else {
                //Get the remaining length
                let remainingLength =  15 - attrTotalChar;
                //Only show the data if remaining length is greater than 3
                if (remainingLength >= 3) {
                    attributeUI += `<span class="badge rounded-pill badge-light-primary"><strong>${short ? attrArr?.short_name?.substring(0, remainingLength - 1) : attrArr?.group_name?.substring(0, remainingLength - 1)}..</strong></span>`
                }
                else {
                    addMore = false;

                    attributeUI += `<i class="ml-2 fa-solid fa-ellipsis-vertical"></i>`;
                }
            }
            attrTotalChar += Number(currentStringLength);
        });
        let attributeSection = document.getElementById('attribute_section_' + currentItemIndex);
        if (attributeSection) {
            attributeSection.innerHTML = attributeUI + '</div>';
        }
        if(total_selected !== total_atts && total_selected !== 0){
            Swal.fire({
                title: 'Error!',
                text: 'Please select all the attribute value',
                icon: 'error',
            });
            $('#attribute').modal('show');
        }
        if(total_selected == 0){
            attributeSection.innerHTML = `
                <button id = "attribute_button_${currentItemIndex}" 
                    ${attributesArray.length > 0 ? '' : 'disabled'} 
                    type = "button" 
                    data-bs-toggle="modal" 
                    onclick = "setItemAttributes('items_dropdown_${currentItemIndex}', '${currentItemIndex}', true);" 
                    data-bs-target="#attribute" 
                    class="btn p-25 btn-sm btn-outline-secondary" 
                    style="font-size: 10px">Attributes</button>
                <input type = "hidden" name = "attribute_value_${currentItemIndex}" />
            `;
        }
        
    }

    let selectedValues = {};

let itemData = {};
let debounceTimer = null;

if ({{ isset($order) ? 'true' : 'false' }}) {
    $("#filter_sub_type_id, #filter_category_id, #filter_sub_category_id, #filter_hsn_id, #filter_item_name_code_id").on('change', function () {
        searchAjax();
    });
}
function createAttributeSearch(attribute_data) {
    console.log("attribute_data:", attribute_data); // Log for debugging

    let attributeSearchHTML = ''; // Start with an empty string

    if (attribute_data && attribute_data.length > 0) {
        attribute_data.forEach((attr, index) => {
            // Start new row every 6 attributes
            if (index % 6 === 0) {
                attributeSearchHTML += `<div class="row">`;
            }

            attributeSearchHTML += `
                <div class="col-md-2">
                    <label for="filter_${attr.group_name}" class="form-label attsearch">${attr.group_name}</label>
                    <select id="filter_${attr.group_name}" name="${attr.group_name}" oninput="onSearchAttributeChange(this)" class="form-select item_attribute_search">
                        <option value="">Select ${attr.group_name}</option>
                        ${attr.values_data.map(value => `<option value="${value.id}" ${value.id == selectedValues[attr.group_name] ? 'selected' : ''}>${value.value}</option>`).join('')}
                    </select>
                </div>
            `;

            // Close the row after every 6 items or at the end of the loop
            if ((index + 1) % 6 === 0 || index === attribute_data.length - 1) {
                attributeSearchHTML += `</div>`;
            }
        });


    }

    console.log("attributeSearchHTML:", attributeSearchHTML); // Log for debugging
    $('#filter_attribute').html(attributeSearchHTML); // Set HTML instead of appending
}
function populateDataTable(items) {
    const order = {!! isset($order) ? json_encode($order) : 'null' !!};
    let tableBody = '';

    items.forEach((item, index) => {
        tableBody += `
            <tr id="item_row_${index}" class="item_header_rows" onclick="onItemClick('${index}');" data-detail-id="${item.id}" data-id="${item.id}">
                <input type="hidden" id="pickup_item_id_${index}" name="pickup_item_id[]" value="${item.id}" ${item.is_editable ? '' : 'readonly'}>
                <td class="customernewsection-form">
                    <div class="form-check form-check-primary custom-checkbox">
                        <input type="checkbox" class="form-check-input item_row_checks" id="item_checkbox_${index}" del-index="${index}">
                        <label class="form-check-label" for="item_checkbox_${index}"></label>
                    </div>
                </td>
                <td class="poprod-decpt">
                    <input type="text" id="items_dropdown_${index}" name="item_code[]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" autocomplete="off"
                        data-name="${item.item?.item_name}" 
                        data-code="${item.item?.item_code}" 
                        data-id="${item.item?.id}" 
                        hsn_code="${item.item?.hsn?.code}" 
                        item-name="${item.item?.item_name}" 
                        specs='${JSON.stringify(item.item?.specifications ?? [])}' 
                        attribute-array='${JSON.stringify(item.attributes_array ?? [])}' 
                        value="${item.item?.item_code}" readonly>
                    <input type="hidden" name="item_id[]" id="items_dropdown_${index}_value" value="${item.item_id ?? item.item.id}">
                </td>
                <td class="poprod-decpt">
                    <input type="text" id="items_name_${index}" class="form-control mw-100" value="${item.item?.item_name}" name="item_name[]" readonly>
                </td>
                <td class="poprod-decpt" id="attribute_section_${index}">
                    <button id="attribute_button_${index}" type="button" data-bs-toggle="modal" onclick="setItemAttributes('items_dropdown_${index}', '${index}', 'true')" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                    <input type="hidden" name="attribute_value_${index}">
                </td>
                <td><select class="form-select" readonly name="uom_id[]" id="uom_dropdown_${index}">
                <option selected value="${item.uom_id}">${item.uom_code}</option></select></td>
                <td class="numeric-alignment">
                    <input type="text" id="item_qty_${index}" value="${item.verified_qty}" name="item_qty[]" oninput='setVariance(this,${index});setValue(${index});' class="form-control physical_qty mw-100 text-end">
                </td>
                <td>
                    <input type="text" id="item_remarks_${newIndex}" name="item_remarks[]" class="form-control mw-100" placeholder="Enter Remarks">
                </td>
            </tr>
        `;
    });

    // Inject table body
    $('#item_header').html(tableBody);
    $(".physical_qty, .rate").on('input', function() {
        const itemIndex = $(this).attr('item-index');
        if (changed_item[itemIndex]) {
            changed_item[itemIndex][this.name] = $(this).val();
            console.log(changed_item);
        }
    });
    // Call `setAttributesUi` for each item
    items.forEach((item, index) => {
        setAttributesUI(index);
        onItemClick(index);
    });
    renderIcons();
}

function clearOrders(type = "mi") {
    $("#location_code_input_qt").val("");
    $(`#book_code_input_${type}`).val("");
    $(`#document_no_input_${type}`).val("");
    $('#document_id_mi_val').val("");
    $(`#item_name_input_${type}`).val("");
    $("#location_id_qt_val").val("");
    $(`#book_id_${type}_val`).val("");
    getOrders();
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
    
$("#generateReport").on('click', function() {
    $('#generated').val(1);
    console.log('check for generate_click');
    $("#save-draft-button").trigger('click');
});
function onSearchAttributeChange(element)
{
    const selectedValue = element.value;
    const selectedName = element.name;
    if (selectedValue) {
        selectedValues[selectedName] = selectedValue;
    } else {
        delete selectedValues[selectedName];
    }
    searchAjax();
}
function searchAjax()
{
    if (debounceTimer) {
        clearTimeout(debounceTimer);
    }
    debounceTimer = setTimeout(function() {
        itemData = {
            sub_type: $("#filter_sub_type_id").val(),
            category: $("#filter_category_id").val(),
            sub_category: $("#filter_sub_category_id").val(),
            hsn: $("#filter_hsn_id").val(),
            item_name_code: $("#filter_item_name_code_id").val(),
            document_id: "{{ isset($order) ? $order->id : null }}",
            selected_attributes: selectedValues,
            changed_item: changed_item,
        };

        $.ajax({
            // url: "{{ ('pds.search.items') }}",
            url: "{{ route('pds.search.items') }}",
            type: "GET",
            dataType: "json",
            data: itemData,
            success: function (data) {
                if (Array.isArray(data.data) && data.data.length > 0) {
                    const items = data.data;
                    $('#item_header').html('');
                    console.log("items:", items); // Log for debugging
                    const attribute_data = items[0]?.attributes_array;
                    if($("#filter_item_name_code_id").val())
                    {
                        createAttributeSearch(attribute_data);
                    }
                    populateDataTable(items);
                    changed_item = {};
                } else {
                    Swal.fire({
                        title: 'No Items Found',
                        text: data.message || 'No matching items found.',
                        icon: 'info',
                    });
                }
            },
            error: function (xhr) {
                console.error('Error fetching item data:', xhr.responseText);
            }
        });
    }, 500); // Adjust the delay as needed
}
let changed_item = {};
window.addEventListener("beforeunload", function (e) {
  if (changed_item && Object.keys(changed_item).length > 0) {
    e.preventDefault(); // Most browsers ignore this
    
    e.returnValue = "There are Some Unsaved Changes"; // Required for the prompt to show in some browsers
  }
});
$(document).on('click', '#pagination-wrapper .pagination a', function (e) {
    e.preventDefault(); // Stop the default link behavior

    const url = $(this).attr('href');
    if (url) {
        customPaginate(url);
    }
});
function sendMailTo() {
    let user = '{!! isset($user) ? json_encode($user) : 'null' !!}';
    user = user ? JSON.parse(user) : null;
    const customerEmail = user ? user.email : "";
    const customerName = user ? user.name : "";
    console.log("user:", user); // Log for debugging
    console.log("customerEmail:", customerEmail); // Log for debugging
    console.log("customerName:", customerName); // Log for debugging
    const emailInput = document.getElementById('cust_mail');
    const header = document.getElementById('send_mail_heading_label');
    if (emailInput) {
        emailInput.value = customerEmail;
    }
    if(header)
    {
        header.innerHTML = "Send Mail";
    }
    $("#mail_remarks").val("");
    $('#sendMail').modal('show');
}
$("#mail_submit").on('click', function() {
    var selectedIds = [];
    $('#mailer_select option:selected').each(function() {
        console.log('Selected option:', $(this).text(), 'ID:', $(this).data('id'), 'Value:', $(this).val());

        var id = $(this).data('id');
        if (id) {
            selectedIds.push(id);
        }
    });
    console.log('Final selected IDs:', selectedIds);
    $('#mailer_ids').val(selectedIds.join(','));
});


</script>
@endsection
@endsection