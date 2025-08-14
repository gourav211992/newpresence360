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
    .radio-as-checkbox {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        width: 16px;
        height: 16px;
        border: 1.5px solid #666;
        border-radius: 3px;
        margin: 0;
        cursor: pointer;
        position: relative;
    }

    .radio-as-checkbox:checked::before {
        content: '';
        display: block;
        width: 10px;
        height: 10px;
        background-color: #007bff;
        position: absolute;
        top: 3px;
        left: 3px;
        border-radius: 2px;
    }

</style>
    <!-- BEGIN: Content-->
    <form method="POST" data-completionFunction = "disableHeader" class="ajax-input-form sales_module_form material_issue" action = "{{route('pqc.store')}}" data-redirect="{{ $redirect_url }}" id = "sale_invoice_form" enctype='multipart/form-data'>
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
				<div class="row">
                    @include('layouts.partials.breadcrumb-add-edit', [
                        'title' => 'Purchase Quotation Comparison', 
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
                                    <a type="button" href="{{ route('pq.generate-pdf', ['id' => $order -> id]) }}" target="_blank" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light dropdown-toggle" type="button"><i data-feather='printer'></i> Print</a>
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
                                <div class="card-body vendornewsection-form" id ="main_so_form">  
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
                                                <input type = "hidden" value = "{{$order -> id}}" name = "pqc_header_id"></input>
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
                                                            <option display-address = "{{$order->store -> address ?-> display_address}}" value="{{ $order->store_id }}" selected> {{ $order->store_code }}</option>
                                                        @else
                                                            @foreach ($stores as $store)
                                                                <option display-address = "{{$store -> address ?-> display_address}}" value="{{$store->id}}" data-name="{{$store->store_name}}">{{$store->store_name}}</option> 
                                                            @endforeach
                                                        @endif    
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1 disable_on_edit" id = "selection_section" style = "display:none;"> 
                                                <div class="col-md-3"> 
                                                    <label class="form-label">Reference From</label>  
                                                </div>
                                                    <div class="col-md-4 action-button" id = "rfq_order_selection"> 
                                                        <input type="hidden" id="rfq_header_pull" value ="{{ App\Helpers\ConstantHelper::RFQ_SERVICE_ALIAS }}">
                                                        <button onclick = "openHeaderPullModal();" disabled type = "button" id = "select_rfq_button" class="btn btn-outline-primary btn-sm mb-0"><i data-feather="plus-square"></i>
                                                            Request for Quotation
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            @if(isset($order))
                                                @include('partials.approval-history', ['document_status' => $order->document_status, 'revision_number' => $order->revision_number]) 
                                            @endif
                                        </div> 
                                </div>
                                <div class="col-md-12 {{(isset($order) && count($order -> dynamic_fields)) > 0 ? '' : 'd-none'}}" id = "dynamic_fields_section">
                                    @if (isset($dynamicFieldsUi))
                                        {!! $dynamicFieldsUi !!}
                                    @endif
                                </div>
                            </div>
                            <div class="card Item_Detail_section">
                                <div class="card-body vendornewsection-form"> 
                                    <div class="bo_Sear-bottom mb-1 pb-10">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="newheader "> 
                                                    <h4 class="card-title text-theme">Quotation Detail</h4>
                                                    <p class="card-text">Select the confirmed Vendor</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-sm-end" id = "add_delete_item_section">
                                                <!-- <button type="button" id="importItem" class="mx-1 btn btn-sm btn-outline-primary importItem" onclick="openImportItemModal('create', '')"><i data-feather="upload"></i> Import Item</button>   -->
                                                <a href="#" onclick = "deleteItemRows();" class="btn btn-sm btn-outline-danger me-50"><i data-feather="x-circle"></i> Delete</a>
                                                <!--<a href="#" onclick = "addItemRow();" id = "add_item_section" style = "display:none;" class="btn btn-sm btn-outline-primary"><i data-feather="plus"></i> Add Item</a> -->
                                            </div>
                                            @include('pqc.items_table')
                                        </div> 
                                    </div>
                                </div>
                            </div>
                            <div class="card">
                                <div class="card-body vendornewsection-form"> 
                                    
                                    <div class="border-bottom mb-2 pb-25">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="newheader "> 
                                                    <h4 class="card-title text-theme">Instructions</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <textarea name="instructions" id="summernote" class="form-control" placeholder="Enter PQ Instruction" >{{ isset($order->instructions) ? $order->instructions : '' }}</textarea>
                                                @error('pq_instructions')
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

                        <div class="table-responsive-md vendornewsection-form">
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
                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Amend Physical Stock Verification
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

<div class="modal fade" id="sendMail" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form class="ajax-submit-2" method="POST" action="{{ route('pq.mail') }}" data-redirect="{{ $redirect_url }}" enctype='multipart/form-data'>
            @csrf
            <input type="hidden" name="action_type" id="action_type">
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
                                @if(isset($order) && $order->suppliers)

                                    <option value="{{ $order->suppliers->email }}" selected data-id="{{ $order->suppliers->id }}">{{ $order->suppliers->company_name }}</option>
                                @else
                                @foreach ($suppliers as $index => $sup)
                                    <option value="{{ $sup->email }}" data-id="{{ $sup->id }}" {{ isset($order) && $sup->id == $order->created_by ? 'selected' : '' }}>
                                        {{ $sup->company_name }}
                                    </option>
                                @endforeach
                                @endif
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
                {{-- <form class="importForm" action="{{ route('pq.import') }}" method="POST" enctype="multipart/form-data"> --}}
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
<div class="modal fade" id="discount" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1" id="shareProjectTitle">Discount</h1>
                <div class = "row">
                    <div class="col-md-4" style = "padding-right:0px">
                        <div class="">
                            <label class="form-label">Type<span class="text-danger">*</span></label> 
                            <input type="text" id="new_discount_name" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value=""  onblur = "resetDiscountOrExpense(this,'new_discount_percentage')">
                            <input type = "hidden" id = "new_discount_id" />
                        </div>
                    </div>
                    <div class="col-md-2" style = "padding-right:0px">
                        <div class="">
                            <label class="form-label">Percentage <span class="text-danger">*</span></label> 
                            <input id = "new_discount_percentage" oninput = "onChangeDiscountPercentage(this);" type="text" class="form-control mw-100 text-end" placeholder = "Discount Percentage"/>
                        </div>
                    </div>
                    <div class="col-md-4" style = "padding-right:0px">
                        <div class="">
                            <label class="form-label">Value <span class="text-danger">*</span></label> 
                            <input id = "new_discount_value" type="text" class="form-control mw-100 text-end" oninput = "onChangeDiscountValue(this);" placeholder = "Discount Value"/>
                        </div>
                    </div>
                    <div class="col-md-auto mt-1 d-flex align-items-center justify-content-center" style = "padding-right:0px">
                        <div>
                        <a href="#" onclick = "addDiscount();" class="text-primary can_hide"><i data-feather="plus-square"></i></a>
                        </div>
                    </div>
                </div>
                <div class="table-responsive-md vendornewsection-form">
                    <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail" id = "discount_main_table" total-value = "0"> 
                        <thead>
                            <tr>
                            <th>S.No</th>
                            <th width="150px">Discount Name</th>
                            <th>Discount %</th>
                            <th>Discount Value</th>
                            <th>Action</th>
                            </tr>
                        </thead>
                        <tbody >
                            <tr></tr>
                            <tr>
                                <td colspan="2"></td>
                                <td class="text-dark"><strong>Total</strong></td>
                                <td class="text-dark" ><strong id = "total_item_discount">0.00</strong></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-center">  
                <button type="button" class="btn btn-outline-secondary me-1 can_hide" onclick = "closeModal('discount');">Cancel</button> 
                <button type="button" class="btn btn-primary can_hide" onclick = "closeModal('discount');">Submit</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="tax" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1" id="shareProjectTitle">Taxes</h1>
                <div class="table-responsive-md vendornewsection-form">
                    <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail" id = "tax_main_table"> 
                        <thead>
                            <tr>
                            <th>S.No</th>
                            <th width="150px">Tax Name</th>
                            <th>Tax %</th>
                            <th>Tax Value</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
                
            </div>
            
            <div class="modal-footer justify-content-center">  
                <button type="button" class="btn btn-outline-secondary me-1 can_hide" onclick = "closeModal('tax');">Cancel</button> 
                <button type="button" class="btn btn-primary can_hide" onclick = "closeModal('tax');">Submit</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="discountOrder" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1" id="shareProjectTitle">Discount</h1>

                <div class = "row">
                    <div class="col-md-4" style = "padding-right:0px">
                        <div class="">
                            <label class="form-label">Type<span class="text-danger">*</span></label> 
                            <input type="text" id="new_order_discount_name" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value=""  onblur = "resetDiscountOrExpense(this, 'new_order_discount_percentage')">
                            <input type = "hidden" id = "new_order_discount_id" />
                        </div>
                    </div>
                    <div class="col-md-2" style = "padding-right:0px">
                        <div class="">
                            <label class="form-label">Percentage <span class="text-danger">*</span></label> 
                            <input id = "new_order_discount_percentage" oninput = "onChangeOrderDiscountPercentage(this);" type="text" class="form-control mw-100 text-end" />
                        </div>
                    </div>
                    <div class="col-md-4" style = "padding-right:0px">
                        <div class="">
                            <label class="form-label">Value <span class="text-danger">*</span></label> 
                            <input id = "new_order_discount_value" type="text" class="form-control mw-100 text-end" oninput = "onChangeOrderDiscountValue(this);"/>
                        </div>
                    </div>
                    <div class="col-md-auto mt-1 d-flex align-items-center justify-content-center" style = "padding-right:0px">
                        <div>
                            <a  href="#" onclick = "addOrderDiscount();" class="text-primary can_hide"><i data-feather="plus-square"></i></a>
                        </div>
                    </div>
                </div>

                <div class="table-responsive-md vendornewsection-form">
                    <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail" id = "order_discount_main_table"> 
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th width="150px">Discount Name</th>
                                <th>Discount %</th>
                                <th>Discount Value</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody >
                            <tr></tr>
                            <tr>
                                <td colspan="2"></td>
                                <td class="text-dark"><strong>Total</strong></td>
                                <td class="text-dark"><strong id = "total_order_discount">0.00</strong></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-center">  
                <button type="button" class="btn btn-outline-secondary me-1 can_hide" onclick = "closeModal('discountOrder');">Cancel</button> 
                <button type="button" class="btn btn-primary can_hide" onclick = "closeModal('discountOrder');">Submit</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="orderTaxes" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1" id="shareProjectTitle">Taxes</h1>                    
                <div class="table-responsive-md vendornewsection-form">
                    <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail" id = "order_tax_main_table"> 
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th width="150px">Tax</th>
                                <th>Taxable Amount</th>
                                <th>Tax %</th>
                                <th>Tax Value</th>
                            </tr>
                        </thead>
                        <tbody id = "order_tax_details_table">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="expenses" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1" id="shareProjectTitle">Add Expenses</h1>
                <div class = "row">
                    <div class="col-md-4" style = "padding-right:0px">
                        <div class="">
                            <label class="form-label">Type<span class="text-danger">*</span></label> 
                            <input type="text" id="order_expense_name" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value=""  onblur = "resetDiscountOrExpense(this, 'order_expense_percentage')">
                            <input type = "hidden" id = "order_expense_id" />
                        </div>
                    </div>
                    <div class="col-md-2" style = "padding-right:0px">
                        <div class="">
                            <label class="form-label">Percentage <span class="text-danger">*</span></label> 
                            <input type="text" id = "order_expense_percentage" oninput = "onChangeOrderExpensePercentage(this);" class="form-control mw-100 text-end" />
                        </div>
                    </div>
                    <div class="col-md-4" style = "padding-right:0px">
                        <div class="">
                            <label class="form-label">Value <span class="text-danger">*</span></label> 
                            <input type="text" id = "order_expense_value" oninput = "onChangeOrderExpenseValue(this);" class="form-control mw-100 text-end" />
                        </div>
                    </div>
                    <div class="col-md-auto mt-1 d-flex align-items-center justify-content-center" style = "padding-right:0px">
                        <div>
                            <a href="#" onclick = "addOrderExpense();" class="text-primary can_hide"><i data-feather="plus-square"></i></a>
                        </div>
                    </div>
                </div>
                
                <div class="table-responsive-md customernewsection-form">
                    <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail" id = "order_expense_main_table"> 
                        <thead>
                            <tr>
                                <th>S.No</th>
                                <th width="150px">Expense Name</th>
                                <th>Expense %</th>
                                <th>Expense Value</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr></tr>
                            <tr>
                                <td colspan="2"></td>
                                <td class="text-dark"><strong>Total</strong></td>
                                <td class="text-dark"><strong  id = "total_order_expense">00.00</strong></td>
                                <td></td>
                            </tr>    
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer justify-content-center">  
                <button type="button" class="btn btn-outline-secondary me-1 can_hide" onclick="closeModal('expenses');">Cancel</button> 
                <button type="button" class="btn btn-primary can_hide" onclick="closeModal('expenses');">Submit</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="edit-address-billing" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1" id="shareProjectTitle">Edit Address</h1>
                <p class="text-center">Enter the details below.</p>
                <div class="row mt-2">
                    <div class = "col-md-12 mb-1">
                        <select class="select2 form-select vendor_dependent" id = "billing_address_dropdown" name = "billing_address" oninput = "onBillingAddressChange(this);"> 
                            @if (isset($order) && isset($billing_addresses))
                                @foreach ($billing_addresses as $billing_address)
                                    <option value = "{{$billing_address -> value}}" {{$order -> billing_to === $billing_address -> id}}>{{$billing_address -> label}}</option>
                                @endforeach
                            @else
                                <option value = "">Select</option>
                            @endif
                        </select>
                    </div>

                    <div class="col-md-6 mb-1">
                        <label class="form-label">Country <span class="text-danger">*</span></label>
                        <select class="select2 form-select" name = "billing_country_id" id = "billing_country_id_input" onchange = "changeDropdownOptions(this, ['billing_state_id_input'], ['states'], '/states/', null, ['billing_city_id_input'])">
                            @foreach ($countries as $country)
                                <option value = "{{$country -> value}}">{{$country -> label}}</option>
                            @endforeach                                
                        </select>
                    </div>

                    <div class="col-md-6 mb-1">
                        <label class="form-label">State <span class="text-danger">*</span></label>
                        <select class="select2 form-select" name = "billing_state_id" id = "billing_state_id_input" onchange = "changeDropdownOptions(this, ['billing_city_id_input'], ['cities'], '/cities/', null, [])"></select>
                    </div>
                        
                    <div class="col-md-6 mb-1">
                        <label class="form-label">City <span class="text-danger">*</span></label>
                        <select class="select2 form-select" name = "billing_city_id" id = "billing_city_id_input"></select>
                    </div>

                    <div class="col-md-6 mb-1">
                        <label class="form-label w-100">Pincode <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" value="" placeholder="Enter Pincode" name ="billing_pincode" id = "billing_pincode_input"/>
                    </div> 

                    <div class="col-md-12 mb-1">
                        <label class="form-label">Address <span class="text-danger">*</span></label>
                        <textarea class="form-control" placeholder="Enter Address" name = "billing_address_text" id = "billing_address_input"></textarea>
                    </div> 
                </div>    
            </div>
            
            <div class="modal-footer justify-content-center">  
                <button type="button" class="btn btn-outline-secondary me-1 can_hide" onclick = "closeModal('edit-address-billing');">Cancel</button> 
                <button type="button" onclick = "saveAddressBilling();" class="btn btn-primary can_hide">Submit</button>
            </div>
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
                        <textarea class="form-control" current-item = "item_remarks_0" oninput = "changeItemRemarks(this);" id ="current_item_remarks_input" placeholder="Enter Remarks"></textarea>
                    </div> 
                </div>
            </div>

            <div class="modal-footer justify-content-center">  
                <button type="button" class="btn btn-outline-secondary me-1 can_hide" onclick="closeModal('Remarks');">Cancel</button> 
                <button type="button" class="btn btn-primary can_hide" onclick="closeModal('Remarks');">Submit</button>
            </div>
        </div>
    </div>
</div>



@include('pq.pull_pop_up_modal');

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
                            console.error('Error fetching vendor data:', xhr.responseText);
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
                            console.error('Error fetching vendor data:', xhr.responseText);
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

        function onChangeVendor(selectElementId, reset = false) 
        {
            const selectedOption = document.getElementById(selectElementId);
            const paymentTermsDropdown = document.getElementById('payment_terms_dropdown');
            const currencyDropdown = document.getElementById('currency_dropdown');
            if (reset && !selectedOption.value) {
                selectedOption.setAttribute('currency_id', '');
                selectedOption.setAttribute('currency', '');
                selectedOption.setAttribute('currency_code', '');

                selectedOption.setAttribute('payment_terms_id', '');
                selectedOption.setAttribute('payment_terms', '');
                selectedOption.setAttribute('payment_terms_code', '');

                document.getElementById('vendor_id_input').value = "";
                document.getElementById('vendor_code_input_hidden').value = "";
            }
            //Set Currency
            const currencyId = selectedOption.getAttribute('currency_id');
            const currency = selectedOption.getAttribute('currency');
            const currencyCode = selectedOption.getAttribute('currency_code');
            if (currencyId && currency) {
                const newCurrencyValues = `
                    <option value = '${currencyId}' > ${currency} </option>
                `;
                currencyDropdown.innerHTML = newCurrencyValues;
                $("#currency_code_input").val(currencyCode);
            }
            else {
                Swal.fire({
                    title: 'Error!',
                    text: "Vendor Currency not found",
                    icon: 'error',
                });
                $("#vendor_code_input").val("");
                currencyDropdown.innerHTML = '';
                $("#currency_code_input").val("");
                return;
                
            }
            //Set Payment Terms
            const paymentTermsId = selectedOption.getAttribute('payment_terms_id');
            const paymentTerms = selectedOption.getAttribute('payment_terms');
            const paymentTermsCode = selectedOption.getAttribute('payment_terms_code');
            if (paymentTermsId && paymentTerms) {
                const newPaymentTermsValues = `
                    <option value = '${paymentTermsId}' > ${paymentTerms} </option>
                `;
                paymentTermsDropdown.innerHTML = newPaymentTermsValues;
                $("#payment_terms_code_input").val(paymentTermsCode);
            }
            else {
                Swal.fire({
                    title: 'Error!',
                    text: "Vendor Payment Terms not found",
                    icon: 'error',
                });
                $("#vendor_code_input").val("");
                paymentTermsDropdown.innerHTML = '';
                $("#payment_terms_code_input").val("");
                return;
            }
            //Set Location address
            const locationElement = document.getElementById('store_id_input');
            if (locationElement) {
                const displayAddress = locationElement.options[locationElement.selectedIndex].getAttribute('display-address');
                $("#current_pickup_address").text(displayAddress);
            }
            getTotalorderDiscounts(true);
            //Get Addresses (Billing + Shipping)
            changeDropdownOptions(document.getElementById('vendor_id_input'), ['billing_address_dropdown'], ['billing_addresses'], '/vendor/addresses/', 'vendor_dependent');
        }

        function changeDropdownOptions(mainDropdownElement, dependentDropdownIds, dataKeyNames, routeUrl, resetDropdowns = null, resetDropdownIdsArray = [], extraKeysForRequest = [])
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
            let apiRequestValue = mainDropdown?.value;
            console.log("API Request Value : ", apiRequestValue);
            
            //Append Extra Key for Data
            if (extraKeysForRequest && extraKeysForRequest.length > 0) {
                extraKeysForRequest.forEach((extraData, index) => {
                    apiRequestValue += ((index == 0 ? "?" : "&") + extraData.key) + "=" + (extraData.value);
                });
            }
            const apiUrl = routeUrl + apiRequestValue;
            console.log("API URL : ", apiUrl);
            fetch(apiUrl, {
                method : "GET",
                headers : {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            }).then(response => response.json()).then(data => {
                if (mainDropdownElement.id == "vendor_id_input") {
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
                        // document.getElementById('current_shipping_address_id').value = "";
                        document.getElementById('current_billing_address').textContent = "";
                        // document.getElementById('current_shipping_address').textContent = "";
                        document.getElementById('vendor_id_input').value = "";
                        document.getElementById('vendor_code_input').value = "";
                        return;
                    }
                    
                }
                // return false;
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
                                // if (currentElement.id == "shipping_address_dropdown") {
                                //     document.getElementById('current_shipping_address').textContent = item.label;
                                //     document.getElementById('current_shipping_address_id').value = item.id;
                                //     document.getElementById('current_shipping_country_id').value = item.country_id;
                                //     document.getElementById('current_shipping_state_id').value = item.state_id;
                                //     // $('#shipping_country_id_input').val(item.country_id).trigger('change');
                                //     // changeDropdownOptions(document.getElementById('shipping_country_id_input'), ['shipping_state_id_input'], ['states'], '/states/', null, ['shipping_city_id_input']);
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
                    $("#" + mainDropdownElement.id).trigger('ApiCompleted');
                });
            }).catch(error => {
                mainDropdownElement.value = "";
                document.getElementById('currency_dropdown').innerHTML = "";
                document.getElementById('currency_dropdown').value = "";
                document.getElementById('payment_terms_dropdown').innerHTML = "";
                document.getElementById('payment_terms_dropdown').value = "";
                document.getElementById('current_billing_address_id').value = "";
                // document.getElementById('current_shipping_address_id').value = "";
                document.getElementById('current_billing_address').textContent = "";
                // document.getElementById('current_shipping_address').textContent = "";
                document.getElementById('vendor_id_input').value = "";
                document.getElementById('vendor_code_input').value = "";
                $("#" + mainDropdownElement.id).trigger('ApiCompleted');
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
                const input = document.getElementById('item_req_qty_' + index);
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
            <tr id = "item_row_${newIndex}">
                <td class="vendornewsection-form" style="width: 30px;">
                    <div class="form-check form-check-primary custom-checkbox">
                        <input type="checkbox" class="form-check-input item_row_checks" id="item_checkbox_${newIndex}" del-index="${newIndex}">
                        <label class="form-check-label" for="item_checkbox_${newIndex}"></label>
                    </div>
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
                    <input type="text" id="item_req_qty_${newIndex}" name="item_req_qty[]" class="form-control mw-100 text-end" oninput="changeItemQty(this, ${newIndex});" onblur="setFormattedNumericValue(this);">
                </td>
                <td>
                    <input type="text" id="item_rate_${newIndex}" name="item_rate[${newIndex}]" oninput="changeItemRate(this, ${newIndex});" class="form-control mw-100 text-end" onblur="setFormattedNumericValue(this);" />
                </td>
                <td>
                    <input type="text" id="item_value_${newIndex}" disabled class="form-control mw-100 text-end item_values_input" />
                </td>
                <input type="hidden" id="header_discount_${newIndex}" value="0" />
                <input type="hidden" id="header_expense_${newIndex}" />
                <td>
                    <div class="position-relative d-flex align-items-center">
                        <input type="text" id="item_discount_${newIndex}" disabled class="form-control mw-100 text-end item_discounts_input" style="width: 70px" />
                        <div class="ms-50">
                            <button type="button" onclick="onDiscountClick('item_value_${newIndex}', ${newIndex})" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Add</button>
                        </div>
                    </div>
                </td>
                <input type="hidden" id="item_tax_${newIndex}" class="form-control mw-100 text-end item_taxes_input" style="width: 70px" />
                <td><input type="text" id = "value_after_discount_${newIndex}"  disabled class="form-control mw-100 text-end item_val_after_discounts_input" /></td>
                <input type="hidden" id="value_after_header_discount_${newIndex}" class="item_val_after_header_discounts_input" />
                <input type="hidden" id="item_total_${newIndex}" class="form-control mw-100 text-end item_totals_input" />
                <td>
                    <input type="text" id="item_remarks_${newIndex}" name="item_remarks[]" class="form-control mw-100" placeholder="Enter Remarks">
                </td>
             </tr>
            `;
            tableElementBody.appendChild(newItemRow);
            initializeAutocomplete1("items_dropdown_" + newIndex, newIndex);
            renderIcons();
            disableHeader();

            const qtyInput = document.getElementById('item_req_qty_' + newIndex);

            const itemCodeInput = document.getElementById('items_dropdown_' + newIndex);
            const uomCodeInput = document.getElementById('uom_dropdown_' + newIndex);
            const storeCodeInput = document.getElementById('item_store_from_' + newIndex);
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
            if (allRowsNew.length > 0) {
                disableHeader();
            } else {
                $(".Item_Search_section").show();
                document.querySelectorAll(".dynamic-vendor-th").forEach(el => el.remove());
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
                const currentQty = document.getElementById('item_req_qty_' + index).value;
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
            var itemAndAttrCheck = checkSelectedItemAndAttributes(index);
            if (itemAndAttrCheck) {
                Swal.fire({
                    title: 'Error!',
                    text: itemAndAttrCheck,
                    icon: 'error',
                });
                element.value = 0;
                return;
            }
            itemRowCalculation(index);
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

        function checkSelectedItemAndAttributes(index)
        {
            var itemCode = document.getElementById('items_dropdown_' + index).value;
            if (!itemCode) {
                return "Please select an item first";
            }
            var itemAttributes = JSON.parse(document.getElementById('items_dropdown_' + index).getAttribute('attribute-array'));
            var itemAttributesString = "";
            if (itemAttributes.length > 0) {
                var allAttributesSelected = true;
                itemAttributes.forEach((itemAttr, itemAttrIndex) => {
                    var currentItemSelected = false;
                    itemAttr.values_data.forEach(valData => {
                        if (valData.selected) {
                            currentItemSelected = true;
                            itemAttributesString += (itemAttrIndex == 0 ? '' : ',')  + itemAttr.id + ":" + valData.id;
                        }
                    });
                    if (!currentItemSelected) {
                        allAttributesSelected = false;
                    }
                });
                if (!allAttributesSelected) {
                    return "Please select item attributes first";
                }
            }
            //Check if same item with same attributes already exists
            var allItemRows = document.getElementsByClassName('item_header_rows');
            var sameItemExists = false;
            for (let itemIndex = 0; itemIndex < allItemRows.length; itemIndex++) {
                // Ensure data-index is set and compare as integer
                var rowDataIndex = allItemRows[itemIndex].getAttribute('data-index');
                if (rowDataIndex !== null && parseInt(index) !== parseInt(rowDataIndex)) {
                    var currentItemCodeElement = document.getElementById('items_dropdown_' + rowDataIndex);
                    if (currentItemCodeElement) {
                        if (currentItemCodeElement.value == itemCode) { //Item Code matched
                            //Check same attributes
                            var currentItemAttributes = JSON.parse(currentItemCodeElement.getAttribute("attribute-array"));
                            var currentItemAttributesString = '';
                            currentItemAttributes.forEach((currentItemAttribute, currentItemAttributeIndex) => {
                                currentItemAttribute.values_data.forEach(valData => {
                                    if (valData.selected) {
                                        currentItemAttributesString += (currentItemAttributeIndex == 0 ? '' : ',') + currentItemAttribute.id + ":" + valData.id;
                                    }
                                });
                            });
                            if (itemAttributesString == currentItemAttributesString) {
                                sameItemExists = true;
                            }
                        }
                    }
                }
            }
            if (sameItemExists) {
                return "Item with same attributes already exists";
            }
            //Item Type
            return null;
        }
        function initializeAutocompleteVendor(selector) {
            $("#" + selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type:'vendor_list'
                        },
                        success: function(data) {
                            console.log('Vendor data fetched successfully:', data);
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: `${item.company_name} (${item.vendor_code})`,
                                    code: item.vendor_code || '', 
                                    item_id: item.id,
                                    payment_terms_id : item?.payment_terms?.id,
                                    payment_terms : item?.payment_terms?.name,
                                    payment_terms_code : item?.payment_terms?.name,
                                    currency_id : item?.currency_id,
                                    country_id : item?.country_id,
                                    state_id : item?.state_id,
                                    currency : item?.currency?.short_name,
                                    currency_code : item?.currency_code,
                                    type : item?.vendor_type,
                                    phone_no : item?.phone,
                                    email : item?.email,
                                    gstin : item?.gstin

                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching vendor data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    console.log("Selected Vendor: ", ui.item,"this : ", this);
                    var $input = $(this);
                    var paymentTermsId = ui.item.payment_terms_id;
                    var paymentTerms = ui.item.payment_terms;
                    var paymentTermsCode = ui.item.payment_terms_code;
                    var currencyId = ui.item.currency_id;
                    var countryId = ui.item.country_id;
                    var stateId = ui.item.state_id;
                    var currency = ui.item.currency;
                    var currencyCode = ui.item.currency_code;
                    var vendorType = ui.item.type;
                    var phoneNo = ui.item.phone_no;
                    var email = ui.item.email;
                    var gstIn = ui.item.gstin;
                    console.log("Vendor Type : ", vendorType, "Phone No : ", phoneNo, "Email : ", email, "GSTIN : ", gstIn);
                    $input.attr('vendor_type', vendorType);
                    $input.attr('phone_no', phoneNo);
                    $input.attr('email', email);
                    $input.attr('gstin', gstIn);
                    $input.attr('payment_terms_id', paymentTermsId);
                    $input.attr('payment_terms', paymentTerms);
                    $input.attr('payment_terms_code', paymentTermsCode);
                    $input.attr('currency_id', currencyId);
                    $input.attr('country_id', countryId);
                    $input.attr('state_id', stateId);
                    $input.attr('currency', currency);
                    $input.attr('currency_code', currencyCode);
                    $input.val(ui.item.label);
                    $('#phone_no_input').val(phoneNo);
                    $('#email_input').val(email);
                    $('#vendor_gstin_input').val(gstIn);
                    $("#vendor_code_input_hidden").val(ui.item.code);
                    $("#country_id_input_hidden").val(countryId);
                    $("#state_id_input_hidden").val(stateId);
                    document.getElementById('vendor_id_input').value = ui.item.id;
                    onChangeVendor(selector);
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#vendor_code_input_hidden").val("");
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
        }

        initializeAutocompleteVendor('vendor_code_input');
        $(document).on('click','#billAddressEditBtn',(e) => {
            const addressId = document.getElementById('current_billing_address_id').value;
            const apiRequestValue = addressId;
            const apiUrl = "/customer/address/" + apiRequestValue;
            fetch(apiUrl, {
                method : "GET",
                headers : {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
            }).then(response => response.json()).then(data => {
                if (data) {
                    $('#billing_country_id_input').val(data.address.country_id).trigger('change');
                    $("#current_billing_address_id").val(data.address.id);
                    $("#current_billing_country_id").val(data.address.country_id);
                    $("#current_billing_state_id").val(data.address.state_id);
                    $("#current_billing_address").text(data.address.display_address);
                    setTimeout(() => {
                        
                        $('#billing_state_id_input').val(data.address.state_id).trigger('change');

                        setTimeout(() => {
                        
                            $('#billing_city_id_input').val(data.address.city_id).trigger('change');
                        }, 1000);
                    }, 1000);
                    $('#billing_pincode_input').val(data.address.pincode)
                    $('#billing_address_input').val(data.address.address);

                }

            }).catch(error => {
                console.log("Error : ", error);
            });
            $("#edit-address-billing").modal('show');
        });

        
        function initializeAutocompleteTed(selector, idSelector, type, percentageVal) {
            $("#" + selector).autocomplete({
                source: function(request, response) {
                    var selectedDiscountIds = [];
                    if (type === "sales_module_discount") {
                        if (selector == "new_order_discount_name") {
                            var salesDiscountIdElement = document.getElementsByClassName('order_discount_master_id_hidden');
                            for (let index = 0; index < salesDiscountIdElement.length; index++) {
                                selectedDiscountIds.push(salesDiscountIdElement[index].value);
                            }
                        } else if (selector == "new_discount_name") {
                            var itemIndex = document.getElementById('discount_main_table').getAttribute('item-row-index');
                            var salesDiscountIdElement = document.getElementsByClassName('discount_master_ids_hidden_' + itemIndex);
                            for (let index = 0; index < salesDiscountIdElement.length; index++) {
                                selectedDiscountIds.push(salesDiscountIdElement[index].value);
                            }
                        }
                    }
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type:type,
                            selected_discount_ids : selectedDiscountIds
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: `${item.name}`,
                                    percentage: `${item.percentage}`,
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching vendor data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    var $input = $(this);
                    var itemName = ui.item.label;
                    var itemId = ui.item.id;
                    var itemPercentage = ui.item.percentage;

                    $input.val(itemName);
                    $("#" + idSelector).val(itemId);
                    $("#" + percentageVal).val(itemPercentage).trigger('input');
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#" + idSelector).val("");
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
        } 
        initializeAutocompleteTed("new_discount_name", "new_discount_id", 'sales_module_discount', 'new_discount_percentage');

        function changeItemQty(element, index)
        {
            var inputNumValue = parseFloat(element.value ? element.value  : 0);
            var itemAndAttrCheck = checkSelectedItemAndAttributes(index);
            if (itemAndAttrCheck) {
                Swal.fire({
                    title: 'Error!',
                    text: itemAndAttrCheck,
                    icon: 'error',
                });
                element.value = 0;
                itemRowCalculation(index);
                return;
            }
            console.log("Input Num Value : ", inputNumValue);
            if (element.hasAttribute('max'))
            {
                console.log("Max Attribute : ", element.getAttribute('max'));
                var maxInputVal = parseFloat(element.getAttribute('max'));
                console.log("Max Input Val : ", maxInputVal);
                if (inputNumValue > maxInputVal) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Quantity cannot be greater than ' + maxInputVal,
                        icon: 'error',
                    });
                    element.value = (parseFloat(maxInputVal ? maxInputVal  : 0)).toFixed(2);
                    itemRowCalculation(index);
                    return;
                }
            }
            
            itemRowCalculation(index);
        }

        function addDiscount(render = true)
        {
            const discountName = document.getElementById('new_discount_name').value;
            const discountId = document.getElementById('new_discount_id').value;
            // const discountType = document.getElementById('new_discount_type').value;
            const discountPercentage = document.getElementById('new_discount_percentage').value;
            const discountValue = document.getElementById('new_discount_value').value;

            const itemRowIndex = document.getElementById('discount_main_table').getAttribute('item-row-index');

            //Check if newly added discount is greater than actual item value
            var existingItemDiscount = document.getElementById('item_discount_' + itemRowIndex).value;
            existingItemDiscount = parseFloat(existingItemDiscount ? existingItemDiscount : 0);
            var newDiscountVal = parseFloat(discountValue ? discountValue : 0);
            const newItemDiscountTotal = existingItemDiscount + newDiscountVal;
            var itemValueTotal = document.getElementById('item_value_' + itemRowIndex).value;
            itemValueTotal = parseFloat(itemValueTotal ? itemValueTotal : 0);
            if (newItemDiscountTotal > itemValueTotal) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Discount cannot be greater than Item Value',
                    icon: 'error',
                });
                return;
            }

            if (discountName && discountId && (discountPercentage || discountValue)) //All fields filled
            {
                // const previousElements = document.getElementsByClassName('item_discounts');
                // const newIndex = previousElements.length ? previousElements.length: 0;
                const ItemRowIndexVal = document.getElementById('discount_main_table').getAttribute('item-row-index');

                const previousHiddenFields = document.getElementsByClassName('discount_names_hidden_' + ItemRowIndexVal);
                addDiscountHiddenInput(document.getElementById('discount_main_table').getAttribute('item-row'), ItemRowIndexVal, previousHiddenFields.length ? previousHiddenFields.length : 0, render);
            }
            else
            {
                Swal.fire({
                    title: 'Warning!',
                    text: 'Please enter all the discount details',
                    icon: 'warning',
                });
            }
            
        }

        function addOrderDiscount(dataId = null, enableExceedCheck = true)
        {
            const discountName = document.getElementById('new_order_discount_name').value;
            const discountId = document.getElementById('new_order_discount_id').value;
            const discountPercentage = document.getElementById('new_order_discount_percentage').value;
            const discountValue = document.getElementById('new_order_discount_value').value;
            if (discountName && discountId && (discountPercentage || discountValue)) //All fields filled
            {
                var existingOrderDiscount = document.getElementById('order_discount_summary') ? document.getElementById('order_discount_summary').textContent : 0;
                existingOrderDiscount = parseFloat(existingOrderDiscount ? existingOrderDiscount : 0);
                var newOrderDiscountVal = parseFloat(discountValue ? discountValue : 0);

                var actualNewOrderDiscount = existingOrderDiscount + newOrderDiscountVal;
                var totalItemsValue = document.getElementById('all_items_total_total') ? document.getElementById('all_items_total_total').textContent : 0;
                totalItemsValue = parseFloat(totalItemsValue ? totalItemsValue : 0);
                if (actualNewOrderDiscount > totalItemsValue && enableExceedCheck) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Discount cannot be greater than Total Item Value',
                        icon: 'error',
                    });
                    return;
                }
                const previousHiddenFields = document.getElementsByClassName('order_discount_value_hidden');
                addOrderDiscountHiddenInput(previousHiddenFields.length ? previousHiddenFields.length : 0, dataId);
            }
            
        }
        function addOrderExpense(dataId = null, enableExceedCheck = true)
        {
            const expenseName = document.getElementById('order_expense_name').value;
            const expenseId = document.getElementById('order_expense_id').value;
            const expensePercentage = document.getElementById('order_expense_percentage').value;
            const expenseValue = document.getElementById('order_expense_value').value;
            if (expenseName && expenseId && (expensePercentage || expenseValue)) //All fields filled
            {
                var existingOrderExpense = document.getElementById('all_items_total_expenses_summary') ? document.getElementById('all_items_total_expenses_summary').textContent : 0;
                existingOrderExpense = parseFloat(existingOrderExpense ? existingOrderExpense : 0);
                var newOrderExpenseVal = parseFloat(expenseValue ? expenseValue : 0);

                var actualNewOrderExpense = existingOrderExpense + newOrderExpenseVal;
                var totalItemsValueAfterTax = document.getElementById('all_items_total_after_tax_summary') ? document.getElementById('all_items_total_after_tax_summary').textContent : 0;
                totalItemsValueAfterTax = parseFloat(totalItemsValueAfterTax ? totalItemsValueAfterTax : 0);
                // if (actualNewOrderExpense > totalItemsValueAfterTax && enableExceedCheck) {
                //     Swal.fire({
                //         title: 'Error!',
                //         text: 'Expense cannot exceed 100%',
                //         icon: 'error',
                //     });
                //     return;
                // }

                const previousHiddenFields = document.getElementsByClassName('order_expense_value_hidden');
                addOrderExpenseHiddenInput(previousHiddenFields.length ? previousHiddenFields.length : 0, dataId);
            } else {
                Swal.fire({
                    title: 'Warning!',
                    text: 'Please enter all the discount details',
                    icon: 'warning',
                });
                return;
            }
            
        }

        function addDiscountInTable(ItemRowIndexVal, render = true)
        {
                const previousHiddenNameFields = document.getElementsByClassName('discount_names_hidden_' + ItemRowIndexVal);
                const previousHiddenPercentageFields = document.getElementsByClassName('discount_percentages_hidden_' + ItemRowIndexVal);
                const previousHiddenValuesFields = document.getElementsByClassName('discount_values_hidden_' + ItemRowIndexVal);

                const newIndex = previousHiddenNameFields.length ? previousHiddenNameFields.length : 0;

                var newData = ``;
                for (let index = newIndex- 1; index < previousHiddenNameFields.length; index++) {
                    const newHTML = document.getElementById('discount_main_table').insertRow(index + 2);
                    newHTML.className = "item_discounts";
                    newHTML.id = "item_discount_modal_" + newIndex;
                    newData = `
                        <td>${index+1}</td>
                        <td>${previousHiddenNameFields[index].value}</td>
                        <td>${previousHiddenPercentageFields[index].value}</td>
                        <td class = "dynamic_discount_val_${ItemRowIndexVal}">${previousHiddenValuesFields[index].value}</td>
                        <td>
                            <a href="#" class="text-danger can_hide" onclick = "removeDiscount(${index}, ${ItemRowIndexVal}, this);"><i data-feather="trash-2"></i></a>
                        </td>
                    `;
                    newHTML.innerHTML = newData;
                }
                
                document.getElementById('new_discount_name').value = "";
                document.getElementById('new_discount_id').value = "";
                document.getElementById('new_discount_percentage').value = "";
                document.getElementById('new_discount_percentage').disabled = false;
                document.getElementById('new_discount_value').value = "";
                document.getElementById('new_discount_value').disabled = false;
                if (render) {
                    // setModalDiscountTotal('item_discount_' + ItemRowIndexVal, ItemRowIndexVal);
                    itemRowCalculation(ItemRowIndexVal);
                }
                renderIcons();
        }

        function getTotalorderDiscounts(itemCalculation = true)
        {
            const values = document.getElementsByClassName('order_discount_value_hidden');
            let discount = 0;
            for (let index = 0; index < values.length; index++) {
                discount += parseFloat(values[index].value ? values[index].value : 0);
            }
            const summaryTable = document.getElementById('summary_table');
            discount = discount.toFixed(2);
            if (discount > 0) { //Add in summary
                const discountRow = document.getElementById('order_discount_row');
                if (discountRow) {
                    discountRow.innerHTML = `
                        <td width="55%">Order Discount</td>  
                        <td class="text-end" id = "order_discount_summary" >${discount}</td>
                    `
                } else {
                    const newRow = summaryTable.insertRow(3);
                    newRow.id = "order_discount_row";
                    newRow.innerHTML = `
                    <td width="55%">Order Discount</td>  
                        <td class="text-end" id = "order_discount_summary" >${discount}</td>
                    `;
                }
            } else {// Remove from summary
                let lastDiscountRow = document.getElementById('order_discount_row');
                if (lastDiscountRow) {
                    lastDiscountRow.remove();
                }
            }
            document.getElementById('total_order_discount').textContent = parseFloat(discount ? discount : 0);
            if (itemCalculation)
            {
                const itemData = document.getElementsByClassName('item_header_rows');
                for (let ix = 0; ix < itemData.length; ix++) {
                    itemRowCalculation(ix);
                }
            }
        }

        function getTotalOrderExpenses()
        {
            const values = document.getElementsByClassName('order_expense_value_hidden');
            let expense = 0;
            for (let index = 0; index < values.length; index++) {
                expense += parseFloat(values[index].value ? values[index].value : 0);
            }            
            document.getElementById('all_items_total_expenses_summary').textContent = parseFloat(expense ? expense : 0);
            setAllTotalFields();
        }

        function addOrderDiscountInTable(index)
        {
                const previousHiddenNameFields = document.getElementsByClassName('order_discount_name_hidden');
                const previousHiddenPercentageFields = document.getElementsByClassName('order_discount_percentage_hidden');
                const previousHiddenValuesFields = document.getElementsByClassName('order_discount_value_hidden');

                const newIndex = previousHiddenNameFields.length ? previousHiddenNameFields.length : 0;
                
                var newData = ``;
                var totalSummaryDiscount = 0;
                var total = parseFloat(document.getElementById('total_order_discount').textContent ? document.getElementById('total_order_discount').textContent : 0);
                for (let index = newIndex - 1; index < previousHiddenNameFields.length; index++) {
                    const newHTML = document.getElementById('order_discount_main_table').insertRow(index+2);
                    newHTML.className = "order_discounts";
                    newHTML.id = "order_discount_modal_" + (parseInt(newIndex - 1));
                    newData = `
                        <td>${index+1}</td>
                        <td>${previousHiddenNameFields[index].value}</td>
                        <td>${previousHiddenPercentageFields[index].value ? parseFloat(previousHiddenPercentageFields[index].value).toFixed(2) : ''}</td>
                        <td id = "order_discount_input_val_${index}" class = "">${(previousHiddenValuesFields[index].value)}</td>
                        <td>
                            <a href="#" data-id = "${previousHiddenValuesFields[index].getAttribute('data-id')}" class="text-danger can_hide" onclick = "removeOrderDiscount(${newIndex - 1},this);"><i data-feather="trash-2"></i></a>
                        </td>
                    `;
                    newHTML.innerHTML = newData;
                    total+= parseFloat(previousHiddenValuesFields[index].value ? previousHiddenValuesFields[index].value : 0);
                    totalSummaryDiscount += parseFloat(previousHiddenValuesFields[index].value ? previousHiddenValuesFields[index].value : 0);
                }

                document.getElementById('new_order_discount_name').value = "";
                document.getElementById('new_order_discount_id').value = "";
                document.getElementById('new_order_discount_percentage').value = "";
                document.getElementById('new_order_discount_percentage').disabled = false;
                document.getElementById('new_order_discount_value').value = "";
                document.getElementById('new_order_discount_value').disabled = false;
                document.getElementById('total_order_discount').textContent = total.toFixed(2);
                renderIcons();

                getTotalorderDiscounts();
        }


        function addOrderExpenseInTable(index)
        {
                const previousHiddenNameFields = document.getElementsByClassName('order_expense_name_hidden');
                const previousHiddenPercentageFields = document.getElementsByClassName('order_expense_percentage_hidden');
                const previousHiddenValuesFields = document.getElementsByClassName('order_expense_value_hidden');

                const newIndex = previousHiddenNameFields.length ? previousHiddenNameFields.length : 0;
                
                var newData = ``;
                var totalSummaryExpense = 0;
                var total = parseFloat(document.getElementById('total_order_expense').textContent ? document.getElementById('total_order_expense').textContent : 0);
                for (let index = newIndex - 1; index < previousHiddenNameFields.length; index++) {
                    const newHTML = document.getElementById('order_expense_main_table').insertRow(index+2);
                    newHTML.className = "order_expenses";
                    newHTML.id = "order_expense_modal_" + (parseInt(newIndex - 1));
                    newData = `
                        <td>${index+1}</td>
                        <td>${previousHiddenNameFields[index].value}</td>
                        <td>${previousHiddenPercentageFields[index].value}</td>
                        <td id = "order_expense_input_val_${index}">${previousHiddenValuesFields[index].value}</td>
                        <td>
                            <a href="#" data-id="${previousHiddenValuesFields[index].getAttribute('data-id')}" class="text-danger can_hide" onclick = "removeOrderExpense(${newIndex - 1}, this);"><i data-feather="trash-2"></i></a>
                        </td>
                    `;
                    total+= parseFloat(previousHiddenValuesFields[index].value ? previousHiddenValuesFields[index].value : 0);
                    newHTML.innerHTML = newData;
                    totalSummaryExpense += parseFloat(previousHiddenValuesFields[index].value ? previousHiddenValuesFields[index].value : 0);
                }
                
                document.getElementById('order_expense_name').value = "";
                document.getElementById('order_expense_id').value = "";
                document.getElementById('order_expense_percentage').value = "";
                document.getElementById('order_expense_percentage').disabled = false;
                document.getElementById('order_expense_value').value = "";
                document.getElementById('order_expense_value').disabled = false;
                document.getElementById('total_order_expense').textContent = total;

                renderIcons();

                getTotalOrderExpenses();
        }

        function removeDiscount(index, itemIndex, element)
        {
            let deletedDiscountTedIds = JSON.parse(localStorage.getItem('deletedItemDiscTedIds'));
            const removableElement = document.getElementById('item_discount_modal_' + index);
            if (removableElement) {
                removableElement.remove();
                if (removableElement.getAttribute('data-id')) {
                    deletedDiscountTedIds.push(removableElement.getAttribute('data-id'));
                }
            }
            document.getElementById("item_discount_name_" + itemIndex + "_" + index)?.remove();
            document.getElementById("item_discount_percentage_" + itemIndex + "_" + index)?.remove();
            document.getElementById("item_discount_value_" + itemIndex + "_" + index)?.remove();
            localStorage.setItem('deletedItemDiscTedIds', JSON.stringify(deletedDiscountTedIds));
            renderPreviousDiscount(itemIndex);
            // setModalDiscountTotal('item_discount_' + itemIndex, itemIndex);
            itemRowCalculation(itemIndex);
        }
        function removeOrderDiscount(index, element)
        {
            let deletedHeaderDiscTedIds = JSON.parse(localStorage.getItem('deletedHeaderDiscTedIds'));
            const removableElement = document.getElementById('order_discount_modal_' + index);
            if (removableElement) {
                removableElement.remove();
                if (removableElement.getAttribute('data-id')) {
                    deletedHeaderDiscTedIds.push(removableElement.getAttribute('data-id'));
                }
            }
            
            document.getElementById("order_discount_name_" + index).remove();
            document.getElementById("order_discount_percentage_" + index).remove();
            document.getElementById("order_discount_value_" + index).remove();
            localStorage.setItem('deletedHeaderDiscTedIds', JSON.stringify(deletedHeaderDiscTedIds));
            // renderPreviousDiscount(itemIndex);
            // setModalDiscountTotal('item_discount_' + itemIndex, itemIndex);
            getTotalorderDiscounts();
        }
        function removeOrderExpense(index, element)
        {
            let deletedHeaderExpTedIds = JSON.parse(localStorage.getItem('deletedHeaderExpTedIds'));
            const removableElement = document.getElementById('order_expense_modal_' + index);
            if (removableElement) {
                removableElement.remove();
            }
            if (element.getAttribute('data-id')) {
                deletedHeaderExpTedIds.push(element.getAttribute('data-id'));
            }
            document.getElementById("order_expense_name_" + index)?.remove();
            document.getElementById("order_expense_percentage_" + index)?.remove();
            document.getElementById("order_expense_value_" + index)?.remove();
            localStorage.setItem('deletedHeaderExpTedIds', JSON.stringify(deletedHeaderExpTedIds));
            getTotalOrderExpenses();
            // renderPreviousDiscount(itemIndex);
            // setModalDiscountTotal('item_discount_' + itemIndex, itemIndex);
        }

        function changeDiscountType(element)
        {
            if (element.value === "Fixed") {
                document.getElementById('new_discount_percentage').disabled = true;
                document.getElementById('new_discount_percentage').value = "";
                document.getElementById('new_discount_value').disabled = false;
                document.getElementById('new_discount_value').value = "";
            } else {
                document.getElementById('new_discount_percentage').disabled = false;
                document.getElementById('new_discount_percentage').value = "";
                document.getElementById('new_discount_value').disabled = true;
                document.getElementById('new_discount_value').value = "";
            }
        }

        function onDiscountClick(elementId, itemRowIndex)
        {
            var itemAndAttrCheck = checkSelectedItemAndAttributes(itemRowIndex);
            if (itemAndAttrCheck) {
                Swal.fire({
                    title: 'Error!',
                    text: itemAndAttrCheck,
                    icon: 'error',
                });
                $('#discount').modal('hide');
                return;
            }
            $('#discount').modal('show');
            const totalValue = document.getElementById(elementId).value;
            document.getElementById('discount_main_table').setAttribute('total-value', totalValue);
            document.getElementById('discount_main_table').setAttribute('item-row', elementId);
            document.getElementById('discount_main_table').setAttribute('item-row-index', itemRowIndex);
            renderPreviousDiscount(itemRowIndex);
            let finalAmendSubmitButton = document.getElementById("amend-submit-button");
            viewModeScript(finalAmendSubmitButton ? false : true);
            initializeAutocompleteTed("new_discount_name", "new_discount_id", 'sales_module_discount', 'new_discount_percentage');
        }

        function renderPreviousDiscount(ItemRowIndexVal)
        {
                const previousHiddenNameFields = document.getElementsByClassName('discount_names_hidden_' + ItemRowIndexVal);
                const previousHiddenPercentageFields = document.getElementsByClassName('discount_percentages_hidden_' + ItemRowIndexVal);
                const previousHiddenValuesFields = document.getElementsByClassName('discount_values_hidden_' + ItemRowIndexVal);
                                    
                    const oldDiscounts = document.getElementsByClassName('item_discounts');
                    if (oldDiscounts && oldDiscounts.length > 0)
                    {
                        while (oldDiscounts.length > 0) {
                            oldDiscounts[0].remove();
                        }
                    }

                var newData = ``;
                for (let index = 0; index < previousHiddenNameFields.length; index++) {
                    const newHTML = document.getElementById('discount_main_table').insertRow(index + 2);
                    newHTML.id = "item_discount_modal_" + index;
                    newHTML.className = "item_discounts";
                    newData = `
                        <td>${index+1}</td>
                        <td>${previousHiddenNameFields[index].value}</td>
                        <td>${previousHiddenPercentageFields[index].value}</td>
                        <td class = "dynamic_discount_val_${ItemRowIndexVal}">${previousHiddenValuesFields[index].value}</td>
                        <td>
                            <a data-id = "${previousHiddenValuesFields[index].getAttribute('data-id')}" href="#" class="text-danger can_hide" onclick = "removeDiscount(${index}, ${ItemRowIndexVal}, this);"><i data-feather="trash-2"></i></a>
                        </td>
                    `;
                    newHTML.innerHTML = newData;
                }
                
                document.getElementById('new_discount_name').value = "";
                document.getElementById('new_discount_id').value = "";
                document.getElementById('new_discount_percentage').value = "";
                document.getElementById('new_discount_percentage').disabled = false;
                document.getElementById('new_discount_value').value = "";
                document.getElementById('new_discount_value').disabled = false;
                setModalDiscountTotal('item_discount_' + ItemRowIndexVal, ItemRowIndexVal);
                itemRowCalculation(ItemRowIndexVal);

                renderIcons();
        }

        function onChangeDiscountPercentage(element)
        {
            document.getElementById('new_discount_value').disabled = element.value ? true : false;
            const totalValue = document.getElementById('item_value_' + document.getElementById('discount_main_table').getAttribute('item-row-index')).value;
            if (totalValue) {
                document.getElementById('new_discount_value').value = (parseFloat(totalValue) * parseFloat(element.value/100)).toFixed(2);
            }
        }
        function onChangeOrderDiscountPercentage(element)
        {
            document.getElementById('new_order_discount_value').disabled = element.value ? true : false;
            const totalValue = document.getElementById('all_items_total_value_summary').textContent;
            const totalItemDiscount = document.getElementById('all_items_total_discount_summary').textContent;
            let totalAfterItemDiscount = parseFloat(totalValue ? totalValue : 0) - parseFloat(totalItemDiscount ? totalItemDiscount : 0);
            if (totalAfterItemDiscount) {
                document.getElementById('new_order_discount_value').value = (parseFloat(totalAfterItemDiscount ? totalAfterItemDiscount : 0) * parseFloat(element.value/100)).toFixed(2);
            }
        }
        function onChangeOrderExpensePercentage(element)
        {
            document.getElementById('order_expense_value').disabled = element.value ? true : false;
            const totalValue = document.getElementById('all_items_total_after_tax_summary').textContent;
            if (totalValue) {
                document.getElementById('order_expense_value').value = (parseFloat(totalValue ? totalValue : 0) * parseFloat(element.value/100)).toFixed(2);
            }
        }
        function onChangeDiscountValue(element)
        {
            document.getElementById('new_discount_percentage').disabled = element.value ? true : false;
            // //Calculate percentage
            // const assessmentAmount = document.getElementById('item_value_' + document.getElementById('discount_main_table').getAttribute('item-row-index')).value;
            // if (assessmentAmount) {
            //     document.getElementById('new_discount_percentage').value = parseFloat(element.value/assessmentAmount * 100);
            // }
        }

        function onChangeOrderDiscountValue(element)
        {
            document.getElementById('new_order_discount_percentage').disabled = element.value ? true : false;
            // var assessmentAmount = document.getElementById('all_items_total_value_summary').textContent;
            // const totalItemDiscount = document.getElementById('all_items_total_discount_summary').textContent;
            // assessmentAmount = parseFloat(assessmentAmount - totalItemDiscount);
            // if (assessmentAmount) {
            //     document.getElementById('new_order_discount_percentage').value = parseFloat(element.value/assessmentAmount * 100);
            // }
        }
        function onChangeOrderExpenseValue(element)
        {
            document.getElementById('order_expense_percentage').disabled = element.value ? true : false;
            // const assessmentAmount = document.getElementById('all_items_total_after_tax_summary').textContent;
            // if (assessmentAmount) {
            //     document.getElementById('order_expense_percentage').value = parseFloat(element.value/assessmentAmount * 100);
            // }
        }

        function setModalDiscountTotal(elementId, index)
        {
            var totalDiscountModalVal = 0;
            const docs = document.getElementsByClassName('discount_values_hidden_' + index);
            for (let index = 0; index < docs.length; index++) {
                totalDiscountModalVal += parseFloat(docs[index].value ? docs[index].value : 0);
            }
            document.getElementById('total_item_discount').textContent = totalDiscountModalVal.toFixed(2);
            document.getElementById(elementId).value = totalDiscountModalVal.toFixed(2);
            // changeItemTotal(index);
            // changeAllItemsDiscount();
            // itemRowCalculation(index);
        }

        function addDiscountHiddenInput(itemRow, index, discountIndex, render = true)
        {
            addHiddenInput("item_discount_name_" + index + "_" + discountIndex, document.getElementById('new_discount_name').value, `item_discount_name[${index}][${discountIndex}]`, 'discount_names_hidden_' + index, itemRow);
            addHiddenInput("item_discount_master_id_" + index + "_" + discountIndex, document.getElementById('new_discount_id').value, `item_discount_master_id[${index}][${discountIndex}]`, 'discount_master_ids_hidden_' + index, itemRow);
            addHiddenInput("item_discount_percentage_" + index + "_" + discountIndex, document.getElementById('new_discount_percentage').value, `item_discount_percentage[${index}][${discountIndex}]`, 'discount_percentages_hidden_' + index,  itemRow);
            addHiddenInput("item_discount_value_" + index + "_" + discountIndex, document.getElementById('new_discount_value').value, `item_discount_value[${index}][${discountIndex}]`, 'discount_values_hidden_' + index, itemRow);
            addDiscountInTable(index, render);
        }

        function addOrderDiscountHiddenInput(index, dataId = null)
        {
            addHiddenInput("order_discount_name_" + index, document.getElementById('new_order_discount_name').value, `order_discount_name[${index}]`, 'order_discount_hidden_fields order_discount_name_hidden', 'main_so_form', dataId);
            addHiddenInput("order_discount_master_id_" + index, document.getElementById('new_order_discount_id').value, `order_discount_master_id[${index}]`, 'order_discount_hidden_fields order_discount_master_id_hidden', 'main_so_form', dataId);
            addHiddenInput("order_discount_percentage_" + index, document.getElementById('new_order_discount_percentage').value, `order_discount_percentage[${index}]`, 'order_discount_hidden_fields order_discount_percentage_hidden', 'main_so_form', dataId);
            addHiddenInput("order_discount_value_" + index, document.getElementById('new_order_discount_value').value, `order_discount_value[${index}]`, 'order_discount_hidden_fields order_discount_value_hidden', 'main_so_form', dataId);
            if (dataId) {
                addHiddenInput("order_discount_id_" + index, dataId, `order_discount_id[${index}]`, 'order_discount_hidden_fields order_discount_id_hidden', 'main_so_form');
            }
            addOrderDiscountInTable(index);
        }

        function addOrderExpenseHiddenInput(index, dataId = null)
        {
            addHiddenInput("order_expense_name_" + index, document.getElementById('order_expense_name').value, `order_expense_name[${index}]`, 'order_expense_hidden_fields order_expense_name_hidden', 'main_so_form', dataId);
            addHiddenInput("order_expense_master_id_" + index, document.getElementById('order_expense_id').value, `order_expense_master_id[${index}]`, 'order_expense_hidden_fields order_expense_master_id_hidden', 'main_so_form', dataId);
            addHiddenInput("order_expense_percentage_" + index, document.getElementById('order_expense_percentage').value, `order_expense_percentage[${index}]`, 'order_expense_hidden_fields order_expense_percentage_hidden', 'main_so_form', dataId);
            addHiddenInput("order_expense_value_" + index, document.getElementById('order_expense_value').value, `order_expense_value[${index}]`, 'order_expense_hidden_fields order_expense_value_hidden', 'main_so_form', dataId);
            if (dataId) {
                addHiddenInput("order_expense_id_" + index, dataId, `order_expense_id[${index}]`, 'order_expense_hidden_fields order_expense_id_hidden', 'main_so_form');
            }
            addOrderExpenseInTable(index);
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
            if (specsInnerHTML) {
                document.getElementById('current_item_specs_row').style.display = "table-row";
            } else {
                document.getElementById('current_item_specs_row').style.display = "none";
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
                                quantity: document.getElementById('item_req_qty_' + itemRowId).value,
                                item_id: document.getElementById('items_dropdown_'+ itemRowId + '_value').value,
                                uom_id : document.getElementById('uom_dropdown_' + itemRowId).value,
                                selectedAttr : selectedItemAttr,
                                store_id: $("#store_id_input").val(),
                                sub_store_id : $("#sub_store_id_input").val(),
                                service_alias : 'pq',
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
                                //         $(`#item_req_qty_${itemRowId}`).val(data?.stocks?.confirmedStockAltUom  ?? 0.00);
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
                                //     var inputQtyBox = document.getElementById('item_req_qty_' + itemRowId);

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
                                console.error('Error fetching vendor data:', xhr.responseText);
                            }
                        });
                    }
        }

        function setVariance(element,index)
        {
            const currentQty = parseFloat(element.value) || 0; // Get the current element's value
            const variance = $(`#item_variance_qty_${index}`); // Get the next <td> element
            const confirmedQty = $(`#item_req_qty_${index}`).val();
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
            selectedName = $("#pq_item_id_" + index).val();
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
            const qtyElement = document.getElementById('item_req_qty_' + itemRowId);
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
                                quantity : qty ? qty : document.getElementById('item_req_qty_' + itemRowId).value,
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
                                    document.getElementById('item_req_qty_' + itemRowId).value = 0.00;
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
                                console.error('Error fetching vendor data:', xhr.responseText);
                                storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify([])));
                                rateInput.value = 0.00;
                                valueInput.value = 0.00;

                            }
                        });
        }
        function itemRowCalculation(itemRowIndex)
        {
            const itemQtyInput = document.getElementById('item_req_qty_' + itemRowIndex);
            const itemRateInput = document.getElementById('item_rate_' + itemRowIndex);
            const itemValueInput = document.getElementById('item_value_' + itemRowIndex);
            const itemDiscountInput = document.getElementById('item_discount_' + itemRowIndex);
            const itemTotalInput = document.getElementById('item_total_' + itemRowIndex);
            //ItemValue
            const itemValue = parseFloat(itemQtyInput.value ? itemQtyInput.value : 0) * parseFloat(itemRateInput.value ? itemRateInput.value : 0);
            itemValueInput.value = (itemValue).toFixed(2);
            //Discount
            let discountAmount = 0;
            const discountHiddenPercentageFields = document.getElementsByClassName('discount_percentages_hidden_' + itemRowIndex);
            const discountHiddenValuesFields = document.getElementsByClassName('discount_values_hidden_' + itemRowIndex);
            const mainDiscountInput = document.getElementsByClassName('item_discount_' + itemRowIndex);
            //Multiple Discount
            for (let index = 0; index < discountHiddenPercentageFields.length; index++) {
                if (discountHiddenPercentageFields[index].value) 
                {
                    let currentDiscountVal = parseFloat(itemValue ? itemValue : 0) * (parseFloat(discountHiddenPercentageFields[index].value ? discountHiddenPercentageFields[index].value : 0)/100);
                    discountHiddenValuesFields[index].value = currentDiscountVal.toFixed(2);
                    discountAmount+= currentDiscountVal;
                }
                else 
                {
                    discountAmount+= parseFloat(discountHiddenValuesFields[index].value ? discountHiddenValuesFields[index].value : 0);
                }
            }
            mainDiscountInput.value = discountAmount;
            //Value after discount
            const valueAfterDiscount = document.getElementById('value_after_discount_' + itemRowIndex);
            const valueAfterDiscountValue = (itemValue - mainDiscountInput.value).toFixed(2);
            console.log('valueAfterDiscountValue', valueAfterDiscountValue,' itemValue', itemValue, 'mainDiscountInput.value', mainDiscountInput.value);
            valueAfterDiscount.value = valueAfterDiscountValue;  

            //Get total for calculating header discount for each item
            const itemTotalValueAfterDiscount = document.getElementsByClassName('item_val_after_discounts_input');
            let totalValueAfterDiscount = 0;
            for (let index = 0; index < itemTotalValueAfterDiscount.length; index++) {
                totalValueAfterDiscount += parseFloat(itemTotalValueAfterDiscount[index].value ? itemTotalValueAfterDiscount[index].value : 0);
            }

            setModalDiscountTotal('item_discount_' + itemRowIndex, itemRowIndex);

            //Set Header Discount
            updateHeaderDiscounts();
            updateHeaderExpenses();

            //Get exact discount amount from order
            let totalHeaderDiscountAmount = 0;
            const orderDiscountSummary = document.getElementById('order_discount_summary');
            if (orderDiscountSummary) {
                totalHeaderDiscountAmount = parseFloat(orderDiscountSummary.textContent ? orderDiscountSummary.textContent : 0);
            }
            let itemHeaderDiscount = (parseFloat(valueAfterDiscountValue ? valueAfterDiscountValue : 0)/ totalValueAfterDiscount) * totalHeaderDiscountAmount;
            itemHeaderDiscount = (parseFloat(itemHeaderDiscount ? itemHeaderDiscount : 0)).toFixed(2);
            //Done
            const headerDiscountInput = document.getElementById('header_discount_' + itemRowIndex);
            headerDiscountInput.value = itemHeaderDiscount;

            const valueAfterHeaderDiscount = document.getElementById('value_after_header_discount_' + itemRowIndex);
            valueAfterHeaderDiscount.value = parseFloat(valueAfterDiscountValue ? valueAfterDiscountValue : 0) - itemHeaderDiscount;

            setModalDiscountTotal('item_discount_' + itemRowIndex, itemRowIndex);

            //Set Header Discount
            updateHeaderDiscounts();

            //Tax
            getItemTax(itemRowIndex);

        }

        function getItemTax(itemIndex)
        {
            console.log('getItemTax', itemIndex);
            const itemId = document.getElementById(`items_dropdown_${itemIndex}_value`).value;
            const itemQty = document.getElementById('item_req_qty_' + itemIndex).value;
            const itemValue = document.getElementById('item_value_' + itemIndex).value;
            const discountAmount = document.getElementById('item_discount_' + itemIndex).value;
            const headerDiscountAmount = document.getElementById('header_discount_' + itemIndex).value;
            const totalItemDiscount = parseFloat(discountAmount ? discountAmount : 0) + parseFloat(headerDiscountAmount ? headerDiscountAmount : 0);

            // const totalItemDiscount = parseFloat(discountAmount ? discountAmount : 0);
            const billToCountryId = $("#country_id_input_hidden").val();
            const billToStateId = $("#state_id_input_hidden").val();
            console.log('billToCountryId', billToCountryId, 'billToStateId', billToStateId,'item qty', itemQty, 'itemValue', itemValue, 'totalItemDiscount', totalItemDiscount);
            let itemPrice = 0;
            if (itemQty > 0) {
                itemPrice = (parseFloat(itemValue ? itemValue : 0) + parseFloat(totalItemDiscount ? totalItemDiscount : 0)) / parseFloat(itemQty);
            }
            $.ajax({
                url: "{{route('tax.calculate.sales', ['alias' => 'pq'])}}",
                method: 'GET',
                dataType: 'json',
                data : {
                    item_id : itemId,
                    price : itemPrice,
                    transaction_type : 'purchase',
                    party_country_id : billToCountryId,
                    party_state_id : billToStateId,
                    vendor_id : $("#vendor_id_input").val(),
                    header_book_id : $("#series_id_input").val() ? $("#series_id_input").val() : "{{isset($order) ? $order -> book_id : ''}}",
                    store_id : $("#store_id_input").val(),
                    document_id : "{{isset($order) ? $order -> id : ''}}",
                    document_type : "{{isset(request() -> revisionNumber) ? 'history' : ''}}"
                },
                success: function(data) {
                    const taxInput = document.getElementById('item_tax_' + itemIndex);
                    const valueAfterDiscount = document.getElementById('value_after_discount_' + itemIndex).value;
                    // const valueAfterHeaderDiscount = parseFloat(valueAfterDiscount ? valueAfterDiscount : 0) - parseFloat(headerDiscountAmount ? headerDiscountAmount : 0);
                    const valueAfterHeaderDiscount = document.getElementById('value_after_header_discount_' + itemIndex).value;
                    let TotalItemTax = 0;
                    let taxDetails = [];
                    data.forEach((tax, taxIndex) => {
                        let currentTaxValue = ((parseFloat(tax.tax_percentage ? tax.tax_percentage : 0)/100) * parseFloat(valueAfterHeaderDiscount ? valueAfterHeaderDiscount : 0));
                        // console.log(tax.applicabilty_type);
                        // if(tax.applicability_type == 'collection')
                        // {
                        //     console.log('check pass');
                        //     currentTaxValue = -currentTaxValue;
                        // }
                        TotalItemTax = TotalItemTax + currentTaxValue;
                        taxDetails.push({
                            'tax_index' : taxIndex,
                            'tax_name' : tax.tax_type,
                            'tax_group' : tax.tax_group,
                            'tax_type' : tax.tax_type,
                            'taxable_value' : valueAfterHeaderDiscount,
                            'tax_percentage' : tax.tax_percentage,
                            'tax_value' : (currentTaxValue).toFixed(2),
                            'tax_applicability_type' : tax.applicability_type,
                        });
                    });

                    taxInput.setAttribute('tax_details', JSON.stringify(taxDetails))
                    taxInput.value = (TotalItemTax).toFixed(2);
                    //Total
                    // let valueAfterDiscountInput = document.getElementById('value_after_discount_' + itemIndex);
                    // const itemTotalInput = document.getElementById('item_total_' + itemIndex);
                    // console.log(valueAfterDiscountInput.value, TotalItemTax, "TAX");
                    // itemTotalInput.value = parseFloat(valueAfterDiscountInput.value ? valueAfterDiscountInput.value : 0) +  parseFloat(TotalItemTax ? TotalItemTax : 0);

                    //
                    const itemTotalInput = document.getElementById('item_total_' + itemIndex);
                    itemTotalInput.value = parseFloat(valueAfterHeaderDiscount ? valueAfterHeaderDiscount : 0) +  parseFloat(TotalItemTax ? TotalItemTax : 0);

                //    if (parseFloat(valueAfterDiscountInput.value ? valueAfterDiscountInput.value : 0) !== parseFloat(valueAfterDiscountInput ? valueAfterDiscountInput : 0)) {
                //     getItemTax(itemIndex);
                //    }
                    //Get All Total Values
                    setAllTotalFields();
                    updateHeaderExpenses();
                },
                error: function(xhr) {
                    console.error('Error fetching vendor data:', xhr.responseText);
                }
            });

        }


        function updateHeaderDiscounts()
        {
            const headerPercentages = document.getElementsByClassName('order_discount_percentage_hidden');
            const headerValues = document.getElementsByClassName('order_discount_value_hidden');
            var allItemTotalValue = 0;
            var allItemTotalValueInputs = document.getElementsByClassName('item_values_input');
            for (let idx1 = 0; idx1 < allItemTotalValueInputs.length; idx1++) {
                allItemTotalValue += parseFloat(allItemTotalValueInputs[idx1].value ? allItemTotalValueInputs[idx1].value : 0);
            }
            var totalItemDiscount = 0;
            var totalItemDiscountInputs = document.getElementsByClassName('item_discounts_input');
            for (let idx1 = 0; idx1 < totalItemDiscountInputs.length; idx1++) {
                totalItemDiscount += parseFloat(totalItemDiscountInputs[idx1].value ? totalItemDiscountInputs[idx1].value : 0);
            }
            let totalAfterItemDiscount = parseFloat(allItemTotalValue ? allItemTotalValue : 0) - parseFloat(totalItemDiscount ? totalItemDiscount : 0);

            let discountAmount = 0;
            
            for (let index = 0; index < headerValues.length; index++) {
                if (headerPercentages[index].value) {
                    let currentDiscountVal = totalAfterItemDiscount * (parseFloat(headerPercentages[index].value ? headerPercentages[index].value : 0)/100);
                    headerValues[index].value = currentDiscountVal.toFixed(2);
                    const tableOrderDiscountValue = document.getElementById('order_discount_input_val_' + index);
                    if (tableOrderDiscountValue) {
                        tableOrderDiscountValue.textContent = (currentDiscountVal).toFixed(2);
                    }
                    discountAmount+= currentDiscountVal;
                } else {
                    discountAmount+= parseFloat(headerValues[index].value ? headerValues[index].value : 0);
                }
            }
            getTotalorderDiscounts(false);

        }

        function updateHeaderExpenses()
        {
            const headerPercentages = document.getElementsByClassName('order_expense_percentage_hidden');
            const headerValues = document.getElementsByClassName('order_expense_value_hidden');
            var totalAfterTax = parseFloat(document.getElementById('all_items_total_after_tax_summary').textContent);

            let expenseAmount = 0;
            
            for (let index = 0; index < headerValues.length; index++) {
                if (headerPercentages[index].value) {
                    let currentExpenseVal = totalAfterTax * (parseFloat(headerPercentages[index].value ? headerPercentages[index].value : 0)/100);
                    headerValues[index].value = currentExpenseVal.toFixed(2);
                    const tableOrderExpenseValue = document.getElementById('order_expense_input_val_' + index);
                    if (tableOrderExpenseValue) {
                        tableOrderExpenseValue.textContent = (currentExpenseVal).toFixed(2);
                    }
                    expenseAmount+= currentExpenseVal;
                } else {
                    expenseAmount+= parseFloat(headerValues[index].value ? headerValues[index].value : 0);
                }
            }
            getTotalOrderExpenses();

        }


        function setAllTotalFields()
        {
            //Item value
            const itemTotalInputs = document.getElementsByClassName('item_values_input');
            let totalValue = 0;
            for (let index = 0; index < itemTotalInputs.length; index++) {
                totalValue += parseFloat(itemTotalInputs[index].value ? itemTotalInputs[index].value : 0);
            }
            document.getElementById('all_items_total_value').textContent = (totalValue).toFixed(2);
            //Qty 
            const itemQtyInputs = document.getElementsByClassName('item_qty_input');
            let totalQty = 0;
            for (let index = 0; index < itemQtyInputs.length; index++) {
                totalQty += parseFloat(itemQtyInputs[index].value ? itemQtyInputs[index].value : 0);
            }
            document.getElementById('all_items_total_qty').textContent = (totalQty).toFixed(2);
            //value
            document.getElementById('all_items_total_value_summary').textContent = (totalValue).toFixed(2);
            if (totalValue < 0) {
                document.getElementById('all_items_total_value_summary').setAttribute('style', 'color : red !important;');
            } else {
                document.getElementById('all_items_total_value_summary').setAttribute('style', '');
            }

            //Item Discount
            const itemTotalDiscounts = document.getElementsByClassName('item_discounts_input');
            let totalDiscount = 0;
            for (let index = 0; index < itemTotalDiscounts.length; index++) {
                totalDiscount += parseFloat(itemTotalDiscounts[index].value ? itemTotalDiscounts[index].value : 0);
            }
            document.getElementById('all_items_total_discount').textContent = (totalDiscount).toFixed(2);
            document.getElementById('all_items_total_discount_summary').textContent = (totalDiscount).toFixed(2);
            if (totalDiscount < 0) {
                document.getElementById('all_items_total_discount_summary').setAttribute('style', 'color : red !important;');
            } else {
                document.getElementById('all_items_total_discount_summary').setAttribute('style', '');
            }
            //Item Tax
            const itemTotalTaxes = document.getElementsByClassName('item_taxes_input');
            let totalTaxes = 0;
            for (let index = 0; index < itemTotalTaxes.length; index++) {
                let tax_detail = itemTotalTaxes[index].getAttribute('tax_details') ? JSON.parse(itemTotalTaxes[index].getAttribute('tax_details')) : null;
                if(tax_detail)
                {
                    for(let i = 0; i < tax_detail.length; i++)
                    {
                        if(tax_detail[i].tax_applicability_type == "collection")
                        {
                            totalTaxes += parseFloat(tax_detail[i].tax_value ? tax_detail[i].tax_value : 0);
                        }
                        else
                        {
                            totalTaxes -= parseFloat(tax_detail[i].tax_value ? tax_detail[i].tax_value : 0);
                        }
                    }
                }
                else{
                    totalTaxes += parseFloat(itemTotalTaxes[index].value ? itemTotalTaxes[index].value : 0);
                }
            }
            document.getElementById('all_items_total_tax').value = (totalTaxes).toFixed(2);
            document.getElementById('all_items_total_tax_summary').textContent = Math.abs((totalTaxes).toFixed(2));
            // if (totalTaxes < 0) {
            //     document.getElementById('all_items_total_tax_summary').setAttribute('style', 'color : red !important;')
            // } else {
                document.getElementById('all_items_total_tax_summary').setAttribute('style', '');
            // }
            //Item Total Value After Discount
            const itemDiscountTotal = document.getElementsByClassName('item_val_after_header_discounts_input');
            let itemDiscountTotalValue = 0;
            for (let index = 0; index < itemDiscountTotal.length; index++) {
                itemDiscountTotalValue += parseFloat(itemDiscountTotal[index].value ? itemDiscountTotal[index].value : 0);
            }
            //Item Total Value 
            const itemValueAfterDiscount = document.getElementsByClassName('item_val_after_discounts_input');
            let itemValueAfterDiscountValue = 0;
            for (let index = 0; index < itemValueAfterDiscount.length; index++) {
                itemValueAfterDiscountValue += parseFloat(itemValueAfterDiscount[index].value ? itemValueAfterDiscount[index].value : 0);
            }
            //Order Discount
            const orderDiscountContainer = document.getElementById('order_discount_summary');
            let orderDiscount = orderDiscountContainer ? orderDiscountContainer.textContent : null;
            orderDiscount = parseFloat(orderDiscount ? orderDiscount : 0);
            let taxableValue = itemValueAfterDiscountValue - orderDiscount;
            document.getElementById('all_items_total_total').textContent = (itemValueAfterDiscountValue).toFixed(2);
            document.getElementById('all_items_total_total_summary').textContent = (taxableValue).toFixed(2);
            if (taxableValue < 0) {
                document.getElementById('all_items_total_total_summary').setAttribute('style', 'color : red !important;')
            } else {
                document.getElementById('all_items_total_total_summary').setAttribute('style', '');
            }
            //Taxable total value 
            const totalAfterTax = (totalTaxes + itemDiscountTotalValue).toFixed(2);
            document.getElementById('all_items_total_after_tax_summary').textContent = totalAfterTax;
            if (totalAfterTax < 0) {
                document.getElementById('all_items_total_after_tax_summary').setAttribute('style', 'color : red !important;')
            } else {
                document.getElementById('all_items_total_after_tax_summary').setAttribute('style', '')
            }
            //Expenses
            const expensesInput = document.getElementById('all_items_total_expenses_summary');
            const expense = parseFloat(expensesInput.textContent ? expensesInput.textContent : 0);
            //Grand Total
            const grandTotalContainer = document.getElementById('grand_total');
            grandTotalContainer.textContent = (parseFloat(totalAfterTax) + parseFloat(expense)).toFixed(2);
            if (grandTotalContainer.textContent < 0) {
                document.getElementById('grand_total').setAttribute('style', 'color : red !important;')
            } else {
                document.getElementById('grand_total').setAttribute('style', '')
            }
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
                            vendor_id : null,
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
                            console.error('Error fetching vendor data:', xhr.responseText);
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
                            console.error('Error fetching vendor data:', xhr.responseText);
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
        let piButton = document.getElementById('select_rfq_button');
        if (piButton) {
            piButton.disabled = true;
        }
        let leaseButton = document.getElementById('select_pwo_button');
        if (leaseButton) {
            leaseButton.disabled = true;
        }
        let orderButton = document.getElementById('select_mfg_button');
        if (orderButton) {
            orderButton.disabled = true;
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
        let piButton = document.getElementById('select_rfq_button');
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
                // item.discount_ted.forEach((ted, tedIndex) => {
                //     addHiddenInput("item_discount_name_" + itemIndex + "_" + tedIndex, ted.ted_name, `item_discount_name[${itemIndex}][${tedIndex}]`, 'discount_names_hidden_' + itemIndex, 'item_value_' + itemIndex, ted.id);
                //     addHiddenInput("item_discount_master_id_" + itemIndex + "_" + tedIndex, ted.ted_name, `item_discount_master_id[${itemIndex}][${tedIndex}]`, 'discount_names_hidden_' + itemIndex, 'item_value_' + itemIndex, ted.id);
                //     addHiddenInput("item_discount_percentage_" + itemIndex + "_" + tedIndex, ted.ted_percentage ? ted.ted_percentage : '', `item_discount_percentage[${itemIndex}][${tedIndex}]`, 'discount_percentages_hidden_' + itemIndex,  'item_value_' + itemIndex, ted.id);
                //     addHiddenInput("item_discount_value_" + itemIndex + "_" + tedIndex, ted.ted_amount, `item_discount_value[${itemIndex}][${tedIndex}]`, 'discount_values_hidden_' + itemIndex, 'item_value_' + itemIndex, ted.id);
                //     addHiddenInput("item_discount_id_" + itemIndex + "_" + tedIndex, ted.id, `item_discount_id[${itemIndex}][${tedIndex}]`, 'discount_ids_hidden_' + itemIndex, 'item_value_' + itemIndex);
                // });
                itemUomsHTML = ``;
                if (item.item.uom && item.item.uom.id) {
                    itemUomsHTML += `<option selected value = '${item.item.uom.id}' ${item.item.uom.id == item.uom_id ? "selected" : ""}>${item.item.uom.alias}</option>`;
                }
                item.item.alternate_uoms.forEach(singleUom => {
                    itemUomsHTML += `<option value = '${singleUom.uom.id}' ${singleUom.uom.id == item.uom_id ? "selected" : ""} >${singleUom.uom?.alias}</option>`;
                });
                document.getElementById('uom_dropdown_' + itemIndex).innerHTML = itemUomsHTML;
                setAttributesUI(itemIndex);
                if (itemIndex==0){
                    onItemClick(itemIndex);
                }
            });
            //Disable header fields which cannot be changeddisableHeader();
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
                    if (selectSingleVal == 'rfq') {
                        var selectionSectionElement = document.getElementById('selection_section');
                        if (selectionSectionElement) {
                            selectionSectionElement.style.display = "";
                        }
                        var selectionPopupElement = document.getElementById('rfq_order_selection');
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
            let piButton = document.getElementById('select_rfq_button');
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

    function openHeaderPullModal(type = 'rfq')
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
            openPullType = "rfq";
            $("#rescduleRfq").modal('show');
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

    function getOrders(type = "rfq")
    {
        let departmentOrStoreKey = 'store_location_code';
        let tableSelector = '#mo_orders_table';
        let docType = `#${type}_header_pull`;
        if (type === 'pwo') {
            tableSelector = '#pwo_orders_table';
        } else if (type === 'rfq') {
            departmentOrStoreKey = 'department_code';
            tableSelector = '#rfq_orders_table';
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


        const apiUrl = "{{ route('pqc.get.items') }}";

        const renderData = data => data || 'N/A';

        const columns = [
            {
                data: null,
                name: 'checkbox',
                orderable: false,
                searchable: false,
                render: (data, type, row) => {
                    return `<div class="form-check form-check-inline me-0">
                        <input class="form-check-input pull_checkbox" type="checkbox"
                            doc-id="${row.id}"
                            current-doc-id="0"
                            document-id="${row.id}"
                            rfq-item-id="${row.id}"
                            balance_qty="${row.rfq_balance_qty}">
                    </div>`;
                }
            },
            { data: 'book_code', name: 'book_code', render: renderData, className: 'no-wrap' },
            { data: 'document_number', name: 'document_number', render: renderData, className: 'no-wrap' },
            { data: 'document_date', name: 'document_date', render: renderData, className: 'no-wrap' }
        ];

        columns.push(
            { data: 'rfq_no', name: 'rfq_no', render: renderData, className: 'no-wrap' },
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
            "PQ Items - " + type.toUpperCase(),
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
        const apiUrl = "{{route('pqc.process.items')}}";
        // const apiUrl = "{{('pqc.process.items')}}";
        let docId = [];
        let soItemsId = [];
        let qties = [];
        let documentDetails = [];
        for (let index = 0; index < allCheckBoxes.length; index++) {
            if (allCheckBoxes[index].checked) {
                docId.push(allCheckBoxes[index].getAttribute('document-id'));
                soItemsId.push(allCheckBoxes[index].getAttribute('rfq-item-id'));
                qties.push(allCheckBoxes[index].getAttribute('balance_qty'));
                console.log(allCheckBoxes);
                documentDetails.push({
                    'rfq_item_ids' : allCheckBoxes[index].getAttribute('document-id'),
                    'quantity' : allCheckBoxes[index].getAttribute('pq_balance_qty'),
                    'item_id' : allCheckBoxes[index].getAttribute('rfq-item-id')
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
                    //add vendors in table header
                    const table = document.getElementById("quote_data");
                    const headerRow = table.querySelector("thead tr");

                    let vendors = [];

                    // Step 1: Collect vendors
                    currentOrders.forEach(order => {
                        order?.items?.forEach(orderItem => {
                            orderItem?.vendor_data?.forEach(element => {
                                vendors.push(element?.vendor); // full object
                            });
                        });
                    });

                    // Step 2: Remove duplicates (optional but recommended)
                    vendors = vendors.filter((v, i, arr) => arr.findIndex(x => x.id === v.id) === i);

                    // Step 3: Remove previously added dynamic headers
                    document.querySelectorAll(".dynamic-vendor-th").forEach(el => el.remove());
                    console.log(vendors);
                    // Step 4: Add new headers
                    vendors.forEach(vendor => {
                        const th = document.createElement("th");
                        th.className = "dynamic-vendor-th";
                        th.style.width = "100px";
                        th.style.wordBreak = "break-word";
                        th.style.whiteSpace = "normal";
                        th.style.lineHeight = "1";
                        th.style.textAlign = "center";
                        th.style.verticalAlign = "middle";

                        // Wrapper for form-check
                        const div = document.createElement("div");
                        div.className = "form-check form-check-primary custom-radio";
                        div.style.display = "flex";
                        div.style.flexDirection = "column";
                        div.style.alignItems = "center";
                        console.log(vendor);
                        const input = document.createElement("input");
                        input.type = "checkbox";
                        input.className = "form-check-input vendor_radio item_row_checks";
                        input.name = "vendor_radio";
                        input.value = vendor.id;
                        input.id = `vendor_radio_${vendor.id}`;
                        
                        const hidden = document.createElement("input");
                        hidden.type = "hidden";
                        hidden.className = "form-check-input";
                        hidden.name = "pq_id";
                        hidden.value = vendor.pq_id;
                        hidden.id = `pq_id_${vendor.id}`;

                        const label = document.createElement("label");
                        label.className = "form-check-label";
                        label.htmlFor = `vendor_radio_${vendor.id}`;

                        const name = (vendor.company_name || "")
                            .toLowerCase()
                            .replace(/\b\w/g, c => c.toUpperCase());

                        // Name should be on new line under checkbox
                        const nameSpan = document.createElement("span");
                        nameSpan.textContent = name;
                        nameSpan.style.display = "block";
                        nameSpan.style.fontSize = "11px";
                        nameSpan.style.marginTop = "2px";

                        label.appendChild(nameSpan);

                        div.appendChild(input);
                        div.appendChild(label);
                        div.appendChild(hidden);

                        th.appendChild(div);
                        headerRow.appendChild(th);
                    });


                    let itemRowIndex = document.querySelectorAll('.item_header_rows').length;
                    const tbody = document.getElementById('item_header');
                    let currentOrderIndexVal = document.getElementsByClassName('item_header_rows').length;
                    currentOrders.forEach((currentOrder) => {
                        currentOrder.items.forEach((item) => {
                            console.log(item);
                            const locationElement = document.getElementById('store_id_input');
                            if (locationElement) {
                                const displayAddress = locationElement.options[locationElement.selectedIndex].getAttribute('display-address');
                                $("#current_pickup_address").text(displayAddress);
                            }
                            let vendorTds = '';
                            console.log(vendors);
                            let vendor_data  = item.vendor_data || [];
                            vendor_data.forEach((vendor) => {
                                console.log(vendor);
                                vendorTds += `
                                    <td class="poprod-decpt vendor-cell text-center">
                                        ${vendor.vendor.rate}
                                    </td>`;
                            });
                            let itemAttrs = JSON.stringify(item.item?.item_attributes_array || []);
                            let specs = JSON.stringify(item.item?.specifications || []);
                            tbody.innerHTML += `
                            <tr id="item_row_${itemRowIndex}" class="item_header_rows" onclick="onItemClick('${itemRowIndex}');">
                                <input type="hidden" id="${openPullType}_item_id_${itemRowIndex}" name="${openPullType}_item_ids[]" value="${JSON.stringify(item.id)}">
                                <input type="hidden" id="${openPullType}_id_${itemRowIndex}" name="${openPullType}_ids" value="${JSON.stringify(item.item_id)}">
                                <td class="vendornewsection-form">
                                    <div class="form-check form-check-primary custom-checkbox">
                                        <input type="checkbox" class="form-check-input item_row_checks" id="item_checkbox_${itemRowIndex}" del-index="${itemRowIndex}">
                                        <label class="form-check-label" for="item_checkbox_${itemRowIndex}"></label>
                                    </div>
                                </td>
                                <td class="poprod-decpt">
                                    <input type="text" id="items_dropdown_${itemRowIndex}" name="item_code[]" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" autocomplete="off"
                                        data-name="${item.item?.item_name}" data-code="${item.item?.item_code}" data-id="${item.item.id}"
                                        hsn_code="${item.item?.hsn?.code}" item-name="${item.item?.item_name}" specs='${specs}' attribute-array='${itemAttrs}' 
                                        value="${item.item?.item_code}" readonly item-location="[]">
                                    <input type="hidden" name="item_id[]" id="items_dropdown_${itemRowIndex}_value" value="${item.item.id}">
                                </td>
                                <td class="poprod-decpt">
                                    <input type="text" id="items_name_${itemRowIndex}" class="form-control mw-100" value="${item.item?.item_name}" name="item_name[]" readonly>
                                </td>
                                <td class="poprod-decpt" id="attribute_section_${itemRowIndex}">
                                    <button id="attribute_button_${itemRowIndex}" ${item.item?.item_attributes_array.length > 0 ? '' : 'disabled'}
                                        type="button" data-bs-toggle="modal" onclick="setItemAttributes('items_dropdown_${itemRowIndex}', '${itemRowIndex}', false);" data-bs-target="#attribute"
                                        class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                                    <input type="hidden" name="attribute_value_${itemRowIndex}">
                                </td>
                                <td>
                                    <select class="form-select" name="uom_id[]" id="uom_dropdown_${itemRowIndex}">
                                        <option value="${item.item.uom?.id}" selected>${item.item.uom?.alias}</option>
                                    </select>
                                </td>
                                <td class="numeric-alignment">
                                    <input type="text" id="item_req_qty_${itemRowIndex}" value="${item.pq_balance_qty}" oninput="changeItemQty(this,'${itemRowIndex}')" name="item_req_qty[]"
                                        max="${item.pq_balance_qty}" class="form-control mw-100 text-end">
                                </td>
                                ${vendorTds}
                            </tr>`;
                            initializeAutocomplete1("items_dropdown_" + itemRowIndex, itemRowIndex);
                            setAttributesUI(itemRowIndex);
                            itemRowIndex++;
                        });
                    });
                    $("#vendor_bottom").attr("colspan", vendors.length);
                    $("#vendor_bottom").removeClass("d-none");

                    disableHeader();                           
                },
                error: function(xhr) {
                    console.error('Error fetching vendor data:', xhr.responseText);
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
    $("input[id^='item_req_qty_']").on("input", function () {
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
        document.getElementById('approve_reject_heading_label').textContent = "Approve " + "Invoice";

    }
    function setReject()
    {
        document.getElementById('action_type').value = "reject";
        document.getElementById('approve_reject_heading_label').textContent = "Reject " + "Invoice";
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
                            vendor_id : $("#vendor_id_qt_val").val(),
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
                            console.error('Error fetching vendor data:', xhr.responseText);
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

$(document).on('change', '.vendor_radio', function () {
    // Uncheck all radios
    $(".vendor_radio").prop('checked', false);

    // Check only the clicked one
    $(this).prop('checked', true);
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
    // const apiURL = "{{('pq.posting.get')}}";
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
    // const postingApiUrl = "{{('pq.post')}}";
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
                console.error('Error fetching vendor data:', xhr.responseText);
                document.getElementById('series_id_input').innerHTML = '';
            }
        });
    }

    function revokeDocument()
    {
        const orderId = "{{isset($order) ? $order -> id : null}}";
        if (orderId) {
            $.ajax({
            url: "{{route('pqc.revoke')}}",
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
                <input type="hidden" id="pq_item_id_${index}" name="pq_item_id[]" value="${item.id}" ${item.is_editable ? '' : 'readonly'}>
                <td class="vendornewsection-form">
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
                    <input type="text" id="item_req_qty_${index}" value="${item.verified_qty}" name="item_req_qty[]" oninput='setVariance(this,${index});setValue(${index});' class="form-control physical_qty mw-100 text-end">
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
            // url: "{{ ('pq.search.items') }}",
            url: "{{ route('pq.search.items') }}",
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
    const vendorEmail = user ? user.email : "";
    const vendorName = user ? user.name : "";
    console.log("user:", user); // Log for debugging
    console.log("vendorEmail:", vendorEmail); // Log for debugging
    console.log("vendorName:", vendorName); // Log for debugging
    const emailInput = document.getElementById('cust_mail');
    const header = document.getElementById('send_mail_heading_label');
    if (emailInput) {
        emailInput.value = vendorEmail;
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

function resetDiscountOrExpense(element, percentageFieldId)
{
    if(!element.value) {
        $("#" + percentageFieldId).val('').trigger('input');
    }
}
function onOrderDiscountModalOpen()
{
    initializeAutocompleteTed("new_order_discount_name", "new_order_discount_id", 'sales_module_discount', 'new_order_discount_percentage');
}

function onOrderExpenseModalOpen()
{
    initializeAutocompleteTed("order_expense_name", "order_expense_id", 'sales_module_expense', 'order_expense_percentage');
}

function checkOrRecheckAllItems(element)
{
    const allRowsCheck = document.getElementsByClassName('item_row_checks');
    const checkedStatus = element.checked;
    for (let index = 0; index < allRowsCheck.length; index++) {
        allRowsCheck[index].checked = checkedStatus;
    }
}
function onOrderTaxClick()
{
    const taxesHiddenFields = document.getElementsByClassName('item_taxes_input');
    orderTaxes = [];
    for (let index = 0; index < taxesHiddenFields.length; index++) {
        let itemLevelTaxes = JSON.parse(taxesHiddenFields[index].getAttribute('tax_details'));
        if (Array.isArray(itemLevelTaxes) && itemLevelTaxes.length > 0) {
            itemLevelTaxes.forEach(itemLevelTax => {
                let existingIndex = orderTaxes.findIndex(tax => tax.tax_type == itemLevelTax.tax_type && tax.tax_percentage == itemLevelTax.tax_percentage);
                if (existingIndex > -1) { //Exists
                    orderTaxes[existingIndex]['taxable_amount'] = parseFloat(orderTaxes[existingIndex]['taxable_amount']) + parseFloat(itemLevelTax.taxable_value);
                    orderTaxes[existingIndex]['tax_value'] = parseFloat(orderTaxes[existingIndex]['tax_value']) + parseFloat(itemLevelTax.tax_value);
                } else { //Push
                    orderTaxes.push({
                        'index' : orderTaxes.length ? orderTaxes.length : 0,
                        'tax_type' : itemLevelTax.tax_type,
                        'taxable_amount' : parseFloat(itemLevelTax.taxable_value).toFixed(2),
                        'tax_percentage' : parseFloat(itemLevelTax.tax_percentage).toFixed(2),
                        'tax_value' : parseFloat(itemLevelTax.tax_value).toFixed(2)
                    });
                }
            });
        }
    }
    const mainTableBody = document.getElementById('order_tax_details_table');
    let newTaxesHtml = ``;
    orderTaxes.forEach(taxDetail => {
        newTaxesHtml += `
                    <tr>
                    <td>${taxDetail.index+1}</td>
                    <td>${taxDetail.tax_type}</td>
                    <td>${taxDetail.taxable_amount}</td>
                    <td>${taxDetail.tax_percentage}</td>
                    <td>${taxDetail.tax_value}</td>
                    </tr>
        `
    });
    mainTableBody.innerHTML = newTaxesHtml;
}

function saveAddressBilling(type)
    {
        $.ajax({
            url: "{{route('pq.add.address')}}",
            method: 'POST',
            dataType: 'json',
            data: {
                type: 'billing',
                country_id: $("#billing_country_id_input").val(),
                state_id: $("#billing_state_id_input").val(),
                city_id: $("#billing_city_id_input").val(),
                address: $("#billing_address_input").val(),
                pincode: $("#billing_pincode_input").val(),
                phone: '',
                fax: '',
                vendor_id : $("#vendor_id_input").val()
            },
            success: function(data) {
                if (data && data.data) {
                    $("#edit-address-billing").modal("hide");
                    $("#current_billing_address_id").val(data.data.id);
                    $("#current_billing_country_id").val(data.data.country_id);
                    $("#current_billing_state_id").val(data.data.state_id);
                    $("#current_billing_address").text(data.data.display_address);
                    // var newOption = new Option(data.data.display_address, data.data.id, false, false);
                    // $('#billing_address_dropdown').append(newOption).trigger('change');
                    // $("#billing_address_dropdown").val(data.data.id).trigger('change');
                    $("#new_billing_country_id").val(data.data.country_id);
                    $("#new_billing_state_id ").val(data.data.state_id );
                    $("#new_billing_city_id").val(data.data.city_id);
                    $("#new_billing_type").val(data.data.type);
                    $("#new_billing_pincode").val(data.data.pincode);
                    $("#new_billing_phone").val(data.data.phone);
                    const allRowsNew = document.getElementsByClassName('item_row_checks');
                    const itemData = document.getElementsByClassName('item_header_rows');
                    for (let ix = 0; ix < itemData.length; ix++) {
                        itemRowCalculation(ix);
                    }
                }
            },
            error: function(xhr) {
                console.error('Error fetching vendor data:', xhr.responseText);
            }

        });
    }

    function onBillingAddressChange(element)
    {
        $("#current_billing_address_id").val(element.value);
        $('#billAddressEditBtn').click();
    }


</script>
@endsection
@endsection