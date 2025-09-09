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
    <form method="POST" data-completionFunction = "disableHeader" class="ajax-input-form sales_module_form material_issue" action = "{{route('psv.store')}}" data-redirect="{{ $redirect_url }}" id = "sale_invoice_form" enctype='multipart/form-data'>
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
				<div class="row">
                    @include('layouts.partials.breadcrumb-add-edit', [
                        'title' => 'Physical Stock Verification', 
                        'menu' => 'Home', 
                        'menu_url' => url('home'),
                        'sub_menu' => 'Add New'
                    ])
                    <input type = "hidden" value = "draft" name = "document_status" id = "document_status" />
					<div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
						<div class="form-group breadcrumb-right" id = "buttonsDiv">   
                        @if(!isset(request() -> revisionNumber))
                        <button type = "button" onclick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>  
                        @endif
                            @if (isset($order))
                                @if($buttons['print'])
                                    <a type="button" href="{{ route('psv.generate-pdf', ['id' => $order -> id]) }}" target="_blank" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light dropdown-toggle" type="button"><i data-feather='printer'></i> Print</a>
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
                                            <input type = "hidden" value = "{{$order -> id}}" name = "psv_header_id"></input>
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
                                                <select class="form-select disable_on_edit" name="store_id" id="store_id_input">
                                                    @if(isset($order) && $order->store_id)
                                                        <option value="{{ $order->store_id }}" selected> {{ $order->store_code }}</option>
                                                    @else
                                                    <option value="">Select</option> 
                                                        @foreach ($stores as $store)
                                                            <option value="{{$store->id}}" {{isset($order) ? ($order->store_id == $store->id ? 'selected' : '') : ''}} data-name="{{$store->store_name}}">{{$store->store_name}}</option> 
                                                        @endforeach
                                                    @endif    
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="row align-items-center mb-1 sub_store">
                                            <div class="col-md-3"> 
                                                <label class="form-label">Store<span class="text-danger">*</span></label>  
                                            </div>  
                                            <div class="col-md-5">  
                                                <select class="form-select disable_on_edit" name="sub_store_id" id="sub_store_id_input">
                                                    @if(isset($order) && $order->sub_store_id)
                                                        <option value="{{ $order->sub_store_id }}" selected> {{ $order->sub_store_code }}</option>
                                                    @else
                                                    <option store-type="" value="" disabled selected>Select</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-1 {{isset($order) && $order -> station_id ? '' : 'd-none'}}" id = "station_id_header">
                                            <div class="col-md-3"> 
                                                <label class="form-label">Station</label>  
                                            </div>  
                                            <div class="col-md-5">  
                                                <select class="form-select disable_on_edit" name = "station_id" id = "station_id_input">
                                                <option value = '' >Select</option>
                                                @foreach ($stations as $station)
                                                    <option {{isset($order) && $order -> station_id == $station -> id ? 'selected' : ''}} value = "{{$station -> id}}">{{$station -> name}}</option>
                                                @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row align-items-center mb-1" id = "selection_section" style = "display:none;"> 
                                            <div class="col-md-3"> 
                                                <label class="form-label">Reference From</label>  
                                            </div>
                                                <div class="col-auto action-button" id = "mfg_order_selection"> 
                                                    <button onclick = "openHeaderPullModal();" disabled type = "button" id = "select_mfg_button" data-bs-toggle="modal" data-bs-target="#rescdule" class="btn btn-outline-primary btn-sm mb-0"><i data-feather="plus-square"></i>
                                                    MFG Order
                                                </button>
                                                </div>
                                                <div class="col-auto action-button" id = "pwo_order_selection"> 
                                                    <button onclick = "openHeaderPullModal('pwo');" disabled type = "button" id = "select_pwo_button" data-bs-toggle="modal" data-bs-target="#rescdulePwo" class="btn btn-outline-primary btn-sm mb-0"><i data-feather="plus-square"></i>
                                                    PWO
                                                </button>
                                                </div>
                                                <div class="col-auto action-button" id = "pi_order_selection"> 
                                                    <button onclick = "openHeaderPullModal('pi');" disabled type = "button" id = "select_pi_button" data-bs-toggle="modal" data-bs-target="#rescdulePi" class="btn btn-outline-primary btn-sm mb-0"><i data-feather="plus-square"></i>
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
                            <div class="card Item_Search_section">
                                <div class="card-body customernewsection-form"> 
                                    <div class="bo_Sear-bottom mb-1 pb-10">
                                        <div class="row">
                                            <div class="col-md-10">
                                                <div class="newheader "> 
                                                    <h4 class="card-title text-theme">Item {{!isset($order)?'Filter':'Search'}}</h4>
                                                </div>
                                            </div>
                                            
                                        </div> 
                                        <div class = "row">
                                            <div class="col-md-2">
                                                <label for="filter_sub_type" class="form-label">Item Type</label>
                                                <input type="text" id="filter_sub_type" name="sub_type_id" class="form-control ledgerselecct ui-autocomplete-input"  autocomplete="off" placeholder="Enter Item Type">
                                                <input type="hidden" id="filter_sub_type_id" name="filter_sub_type_id" value=''>
                                            </div>
                                            <div class="col-md-2">
                                                <label for="filter_category" class="form-label">Category</label>
                                                <input type="text" id="filter_category" name="category_id" class="form-control ledgerselecct ui-autocomplete-input"  autocomplete="off" placeholder="Enter Category">
                                                <input type="hidden" id="filter_category_id" name="filter_category_id" value=''>
                                            </div>
                                            <div class="col-md-2">
                                                <label for="filter_sub_category" class="form-label">Sub Category</label>
                                                <input type="text" id="filter_sub_category" name="subcategory_id" class="form-control ledgerselecct ui-autocomplete-input"  autocomplete="off" placeholder="Enter Sub Category">
                                                <input type="hidden" id="filter_sub_category_id" name="filter_sub_category_id" value=''>
                                            </div>
                                            <div class="col-md-2">
                                                <label for="filter_hsn" class="form-label">HSN</label>
                                                <input type="text" id="filter_hsn" name="hsn_id" class="form-control ledgerselecct ui-autocomplete-input"  autocomplete="off" placeholder="Enter HSN">
                                                <input type="hidden" id="filter_hsn_id" name="filter_hsn_id" value=''>
                                            </div>
                                            <div class="col-md-2">
                                                <label for="filter_item_name_code" class="form-label">Item Name/Code</label>
                                                <input type="text" id="filter_item_name_code" name="item" class="form-control ledgerselecct ui-autocomplete-input"  autocomplete="off" placeholder="Enter Item Name/Code">
                                                <input type="hidden" id="filter_item_name_code_id" name="filter_item_name_code_id" value=''>
                                            </div>
                                            <div class = "col-md-2">
                                                <button type="button" class="btn btn-warning btn-sm my-2 mx-3 clearPiFilter" onclick="clearFilters()"><i data-feather="x-circle"></i> Clear</button>
                                            </div>
                                        </div>
                                        <div id = 'filter_attribute'>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 d-flex justify-content-end">
                                            @if(!isset($order))
                                            <button type="button" name="action" class="btn btn-primary btn-sm mx-5 justify-content-end generateReport" id="generateReport"><i class="fa-solid fa-wand-magic-sparkles"></i> Generate Report</button>
                                            <input type="hidden" name="generated" id="generated" value='0'></input>
                                            @endif
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
                                                <button type="button" id="importItem" class="mx-1 btn btn-sm btn-outline-primary importItem" onclick="openImportItemModal('create', '')"><i data-feather="upload"></i> Import Item</button>  
                                                <a href="#" onclick = "deleteItemRows();" class="btn btn-sm btn-outline-danger me-50"><i data-feather="x-circle"></i> Delete</a>
                                                <a href="#" onclick = "addItemRow();" id = "add_item_section" style = "display:none;" class="btn btn-sm btn-outline-primary"><i data-feather="plus"></i> Add Item</a>
                                            </div>
                                            @include('PSV.items_table')
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
<!-- Import Item Modal (AJAX version, no form) -->
<div class="modal fade" id="importItemModal" tabindex="-1" aria-labelledby="importItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg rounded">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="importItemModalLabel">Import Items</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <!-- File Upload -->
                <div class="mb-3">
                    <label for="fileUpload" class="form-label fw-semibold">Upload File</label>
                    <div class="border border-dashed border-2 border-primary rounded p-4 text-center dragdrop-area">
                        <p class="text-muted mb-2">Drag and drop your file here or click to upload</p>
                        <input type="file" id="fileUpload" name="attachment" class="form-control d-none">
                        <button type="button" class="btn btn-outline-primary" onclick="$('#fileUpload').click();">Choose File</button>
                    </div>

                    <!-- Uploaded File Info -->
                    <div id="fileNameDisplay" class="mt-3 d-none d-flex align-items-center gap-2 text-success">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <span><strong>File uploaded:</strong> <span id="selectedFileName"></span></span>
                    </div>

                    <!-- Error Display -->
                    <div id="upload-error" class="text-danger mt-2 d-none"></div>
                </div>

                <!-- Progress Bar -->
                <div class="progress mt-3 d-none" id="uploadProgress">
                    <div class="progress-bar" id="uploadProgressBar" role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100">0%</div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-4 text-end">
                    <button type="button" class="btn btn-success" id="sampleBtn">Download Sample</button>
                    <button type="button" class="btn btn-warning" id="errorDownloadBtn">Download Invalid Rows</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelBtn">Cancel</button>
                    <button type="button" class="btn btn-primary" id="proceedBtn" style="display:none;">Proceed</button>
                </div>

                <!-- Parsed Preview Section -->
                <div id="parsedPreview" class="mt-5 d-none">
                    <ul class="nav nav-tabs" id="importTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="valid-tab" data-bs-toggle="tab" data-bs-target="#validTabPane" type="button" role="tab" aria-controls="validTabPane" aria-selected="true">
                                Valid Items <span id="valid-count"></span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="invalid-tab" data-bs-toggle="tab" data-bs-target="#invalidTabPane" type="button" role="tab" aria-controls="invalidTabPane" aria-selected="false">
                                Invalid Items <span id="invalid-count"></span>
                            </button>
                        </li>
                    </ul>

                    <button type="button" class="btn btn-primary mt-3 d-none" id="submitBtn">Import Items</button>

                    <div class="tab-content border border-top-0" id="importTabsContent">
                        <!-- Valid Items Tab -->
                        <div class="tab-pane fade show active" id="validTabPane" role="tabpanel" aria-labelledby="valid-tab">
                            <div class="table-responsive">
                                <table class="datatables-basic table myrequesttablecbox">
                                    <thead class="table-success">
                                        <tr id="valid-table-header"></tr>
                                    </thead>
                                    <tbody id="valid-table-body"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Invalid Items Tab -->
                        <div class="tab-pane fade" id="invalidTabPane" role="tabpanel" aria-labelledby="invalid-tab">
                            <div class="table-responsive">
                                <table class="datatables-basic table myrequesttablecbox">
                                    <thead class="table-danger">
                                        <tr id="invalid-table-header"></tr>
                                    </thead>
                                    <tbody id="invalid-table-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div> <!-- End parsedPreview -->

            </div>
        </div>
    </div>
</div>

@section('scripts')
<script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
{{-- 
$(document).ready(function () {
    // File selection and preview
    $('#fileUpload').on('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            $('#selectedFileName').text(file.name);
            $('#fileNameDisplay').removeClass('d-none');
        }
    });

    // Download sample button
    $('#sampleBtn').on('click', function () {
        const alias = 'psv'; // dynamic if needed
        window.location.href = `/import/sample/${alias}`;
    });

    // AJAX form submission
    $('#importForm').on('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        const url = $(this).attr('action');

        $('#upload-error').addClass('d-none').text('');
        $('#uploadProgress').removeClass('d-none');
        $('#uploadProgressBar').css('width', '0%').text('0%');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function () {
                let xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function (evt) {
                    if (evt.lengthComputable) {
                        const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                        $('#uploadProgressBar').css('width', percentComplete + '%').text(percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function (response) {
                console.log("Parsed Data:", response);
                $('#uploadProgressBar').addClass('bg-success').text('Uploaded');
                // TODO: Render data preview in a table or UI component here
                alert('File uploaded and parsed successfully');
            },
            error: function (xhr) {
                $('#upload-error').removeClass('d-none').text(xhr.responseJSON?.error || 'Upload failed');
                $('#uploadProgress').addClass('d-none');
                $('#uploadProgressBar').removeClass('bg-success').css('width', '0%').text('0%');
            }
        });
    });
});
--}}

<script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
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

        function onChangeCustomer(selectElementId, reset = false) 
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

                document.getElementById('customer_id_input').value = "";
                document.getElementById('customer_code_input_hidden').value = "";
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
                currencyDropdown.innerHTML = '';
                $("#currency_code_input").val("");
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
                paymentTermsDropdown.innerHTML = '';
                $("#payment_terms_code_input").val("");
            }
            //Get Addresses (Billing + Shipping)
            changeDropdownOptions(document.getElementById('customer_id_input'), ['billing_address_dropdown','shipping_address_dropdown'], ['billing_addresses', 'shipping_addresses'], '/customer/addresses/', 'vendor_dependent');
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
                    setItemAttributes('items_dropdown_' + index, index,true);
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
                    <select ${disabled ? '' : 'disabled'} class="form-select select2" id = "attribute_val_${index}" style = "max-width:100% !important;" onchange = "changeAttributeVal(this, ${elementIdForDropdown}, ${index});">
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
                const input = document.getElementById('item_confirmed_qty_' + index);
                if(!(order && !order.document_status=="{{App\Helpers\ConstantHelper::DRAFT}}"))
                {
                    getStoresData(index, input ? input.value ?? 0 : 0);
                }
                setAttributesUI(dropdown, index);
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
                let addRow = $('#series_id_input').val() && $("#order_no_input").val() && $('#order_date_input').val() && $("#store_id_input").val() && $("#sub_store_id_input").val();
                if (!addRow) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please fill all the header details first',
                    icon: 'error',
                });
                return;
                }
                $(".Item_Search_section").hide();
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
            var storesFrom = @json($stores);
            var storesTo = @json($stores);
            var storesFromHTML = ``;
            var storesToHTML = ``;
            storesFrom.forEach(store => {
                if (store.id == headerFromStoreId) {
                    storesFromHTML += `<option value = "${store.id}" selected>${store.store_name}</option>`
                } else {
                    storesFromHTML += `<option value = "${store.id}">${store.store_name}</option>`
                }
            });
            storesTo.forEach(store => {
                if (store.id == headerToStoreId) {
                    storesToHTML += `<option value = "${store.id}" selected>${store.store_name}</option>`
                } else {
                    storesToHTML += `<option value = "${store.id}">${store.store_name}</option>`
                }
            });
            const selectedUserId = $("#user_id_input").val();
            const selectedDeptId = $("#department_id_input").val();

            let fromSubStoresHTML = ``;
            currentFromSubStoreArray.forEach(fromSubStore => {
                fromSubStoresHTML += `<option value = ${fromSubStore.id}> ${fromSubStore.name} </option>`;
            });

            let toSubStoresHTML = ``;
            currentToSubStoreArray.forEach(toSubStore => {
                toSubStoresHTML += `<option value = ${toSubStore.id}> ${toSubStore.name} </option>`;
            });

            newItemRow.innerHTML = `
            <tr id = "item_row_${newIndex}">
                <td class="customernewsection-form">
                    <div class="form-check form-check-primary custom-checkbox">
                        <input type="checkbox" class="form-check-input item_row_checks" id="item_checkbox_${newIndex}" del-index="${newIndex}">
                        <label class="form-check-label" for="item_checkbox_${newIndex}"></label>
                    </div>
                </td>
                <td>
                    <input type="text" id="items_dropdown_${newIndex}" name="item_code[${newIndex}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" autocomplete="off" data-name="" data-code="" data-id="" hsn_code="" item-name="" specs="[]" attribute-array="[]" value="">
                    <input type="hidden" name="item_id[]" id="items_dropdown_${newIndex}_value">
                </td>
                <td>
                    <input type="text" id="items_name_${newIndex}" name="item_name[${newIndex}]" class="form-control mw-100" value="" readonly>
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
                    <input type="text" id="item_physical_qty_${newIndex}" name="item_physical_qty[${newIndex}]" class="form-control mw-100 text-end"  oninput="setVariance(this,${newIndex}); setValue(${newIndex});" onblur="setFormattedNumericValue(this);">
                </td>
                <td class="numeric-alignment">
                    <input type="text" id="item_confirmed_qty_${newIndex}" name="item_confirmed_qty[${newIndex}]" class="form-control mw-100 text-end"  readonly>
                </td>
                <td class="numeric-alignment">
                    <input type="text" id="item_unconfirmed_qty_${newIndex}" name="item_unconfirmed_qty[${newIndex}]" class="form-control mw-100 text-end" readonly>
                </td>
                <td class="numeric-alignment">
                    <input type="text" id="item_variance_qty_${newIndex}" name="item_balance_qty[${newIndex}]" class="form-control mw-100 text-end" readonly>
                </td>
                <td class="numeric-alignment">
                    <input type="text" id="item_rate_${newIndex}" name="item_rate[${newIndex}]" class="form-control mw-100 text-end" oninput="setValue(${newIndex});">
                </td>
                <td class="numeric-alignment">
                    <input type="text" id="item_value_${newIndex}" name="item_value[${newIndex}]" class="form-control mw-100 text-end" readonly>
                </td>
                <td>
                    <div class="d-flex">
                        <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick="setItemRemarks('item_remarks_${newIndex}');">
                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span>
                        </div>
                    </div>
                    <input type="hidden" id="item_remarks_${newIndex}" name="item_remarks[${newIndex}]">
                </td>
               
             </tr>
            `;
            tableElementBody.appendChild(newItemRow);
            initializeAutocomplete1("items_dropdown_" + newIndex, newIndex);
            renderIcons();
            disableHeader();

            const qtyInput = document.getElementById('item_confirmed_qty_' + newIndex);

            const itemCodeInput = document.getElementById('items_dropdown_' + newIndex);
            const uomCodeInput = document.getElementById('uom_dropdown_' + newIndex);
            const storeCodeInput = document.getElementById('item_store_from_' + newIndex);
            itemCodeInput.addEventListener('input', function() {
                checkStockData(newIndex);
            });
            uomCodeInput.addEventListener('input', function() {
                checkStockData(newIndex);
            });
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
                const currentQty = document.getElementById('item_confirmed_qty_' + index).value;
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
                    types : ['Stock', 'Shop floor','Vendor','Other'],
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

            getStoresData(index, element.value);

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
                                quantity: document.getElementById('item_confirmed_qty_' + itemRowId).value,
                                item_id: document.getElementById('items_dropdown_'+ itemRowId + '_value').value,
                                uom_id : document.getElementById('uom_dropdown_' + itemRowId).value,
                                selectedAttr : selectedItemAttr,
                                store_id: $("#store_id_input").val(),
                                sub_store_id : $("#sub_store_id_input").val(),
                                service_alias : 'psv',
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
                                    if (data?.stocks) {
                                    // document.getElementById('current_item_stocks_row').style.display = "table-row";
                                    // document.getElementById('current_item_stocks').innerHTML = `
                                    // <span class="badge rounded-pill badge-light-primary"><strong>Confirmed Stocks</strong>: <span id = "item_sub_category">${data?.stocks?.confirmedStockAltUom}</span></span>
                                    // <span class="badge rounded-pill badge-light-primary"><strong>Pending Stocks</strong>: <span id = "item_category">${data?.stocks?.pendingStockAltUom}</span></span>
                                    // `;
                                    if (({{ isset($order) && in_array($order->document_status, [App\Helpers\ConstantHelper::DRAFT]) ? 'true' : 'false' }} || {{ !isset($order) ? 'true' : 'false' }})) {
                                        $(`#item_confirmed_qty_${itemRowId}`).val(data?.stocks?.confirmedStockAltUom  ?? 0.00);
                                        $(`#item_unconfirmed_qty_${itemRowId}`).val(data?.stocks?.pendingStockAltUom ?? 0.00);
                                        if(order && order.document_status !="{{App\Helpers\ConstantHelper::DRAFT}}")
                                        {
                                            getStoresData(itemRowId,data?.stocks?.confirmedStockAltUom ?? 0.00,false);
                                        }
                                    }
                                    if(!$(`#item_variance_qty_${itemRowId}`).val() || (!$(`#item_physical_qty_${itemRowId}`).val() || $(`#item_physical_qty_${itemRowId}`).val() == 0)) {
                                        $(`#item_variance_qty_${itemRowId}`).val(data?.stocks?.confirmedStockAltUom);
                                    }
                                    else{
                                     setVariance(document.getElementById('item_physical_qty_' + itemRowId), itemRowId);
                                    }
                                    if(data.stocks.confirmedStocks)
                                    {
                                        $(`#item_rate_${itemRowId}`).attr('disabled',true);
                                    }
                                    else
                                    {
                                        $(`#item_rate_${itemRowId}`).attr('disabled',false);
                                    }
                                    var inputQtyBox = document.getElementById('item_confirmed_qty_' + itemRowId);

                                    inputQtyBox.setAttribute('max-stock',data.stocks.confirmedStockAltUom);
                                    } 
                                 else {
                                        // document.getElementById('current_item_stocks_row').style.display = "none";
                                    }

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
            const confirmedQty = $(`#item_confirmed_qty_${index}`).val();
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
            selectedName = $("#psv_item_id_" + index).val();
            selectedValue = {'physical_qty': currentQty, 'rate': currentRate, 'variance': variance.val()};
            if (selectedValue) {
                changed_item[selectedName] = selectedValue;
            } else {
                delete changed_item[selectedName];
            }
            console.log(changed_item);
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
                        quantity: document.getElementById('item_confirmed_qty_' + itemRowId).value,
                        item_id: currentItemId,
                        uom_id : currentUomId,
                        selectedAttr : selectedItemAttr,
                        store_id: $("#store_id_input").val(),
                        sub_store_id : $("#item_sub_store_from_" + itemRowId).val()
                    },
                    success: function(data) {
                        var inputQtyBox = document.getElementById('item_confirmed_qty_' + itemRowId);
                        var actualQty = inputQtyBox.value;
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
                        } 
                    },
                    error: function(xhr) {
                        console.error('Error fetching customer data:', xhr.responseText);
                    }
                });
            }
        }

        function getStoresData(itemRowId, qty = null, callOnClick = true)
        {
            const qtyElement = document.getElementById('item_confirmed_qty_' + itemRowId);
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
                                quantity : qty ? qty : document.getElementById('item_confirmed_qty_' + itemRowId).value,
                                is_edit : "{{isset($order) ? 1 : 0}}",
                                header_id : "{{isset($order) ? $order -> id : null}}",
                                detail_id : itemDetailId,
                                store_id: $("#store_id_input").val(),
                                sub_store_id : $("#item_sub_store_from_" + itemRowId).val()
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
                                    document.getElementById('item_confirmed_qty_' + itemRowId).value = 0.00;
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
            items.data.forEach((item, itemIndex) => {
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
        const otherField = ($("#store_to_id_input").val() || ($("#vendor_id_input").val() && $("#vendor_store_id_input").val()) || ($("#issue_type_input").val() == 'Consumption' ));

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

    function openHeaderPullModal(type = 'mo')
    {
        document.getElementById('qts_data_table').innerHTML = '';
        document.getElementById('qts_data_table_pwo').innerHTML = '';
        if (type == "pwo") {
            openPullType = "pwo";
            initializeAutocompleteQt("book_code_input_pwo", "book_id_pwo_val", "book_pwo", "book_code", "book_name");
            initializeAutocompleteQt("document_no_input_pwo", "document_id_pwo_val", "pwo_document", "document_number", "document_number");
            initializeAutocompleteQt("item_name_input_pwo", "item_id_pwo_val", "pwo_items", "item_code", "item_name");
            initializeAutocompleteQt("location_code_input_qt", "location_id_qt_val", "location", "store_name");
        } else if (type == 'mo') {
            openPullType = "mo";
            initializeAutocompleteQt("book_code_input_mo", "book_id_mo_val", "book_mo", "book_code", "book_name");
            initializeAutocompleteQt("document_no_input_qt", "document_id_mo_val", "mo_document", "document_number", "document_number");
            initializeAutocompleteQt("item_name_input_mo", "item_id_mo_val", "mo_items", "item_code", "item_name");
            initializeAutocompleteQt("location_code_input_qt", "location_id_qt_val", "location", "store_name");
        }  else if (type == 'pi') {
            openPullType = "pi";
            initializeAutocompleteQt("book_code_input_pi", "book_id_pi_val", "book_pi", "book_code", "book_name");
            initializeAutocompleteQt("document_no_input_qt", "document_id_pi_val", "pi_document", "document_number", "document_number");
            initializeAutocompleteQt("item_name_input_pi", "item_id_pi_val", "pi_items", "item_code", "item_name");
            initializeAutocompleteQt("department_code_input_qt", "department_id_qt_val", "department", "name");
        } 
        // // else if (type === "dnote") {
        // //     openPullType = "so";
        // //     initializeAutocompleteQt("book_code_input_qt", "book_id_qt_val", "book_so", "book_code", "book_name");
        // //     initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "sale_order_document", "document_number", "document_number");
        // // } 
        // else if (type === 'land-lease') {
        //     openPullType = "land-lease";
        //     initializeAutocompleteQt("book_code_input_qt_land", "book_id_qt_val_land", "book_land_lease", "book_code", "book_name");
        //     initializeAutocompleteQt("document_no_input_qt_land", "document_id_qt_val_land", "land_lease_document", "document_number", "document_number");
        //     initializeAutocompleteQt("land_parcel_input_qt_land", "land_parcel_id_qt_val_land", "land_lease_parcel", "name", "name");
        //     initializeAutocompleteQt("land_plot_input_qt_land", "land_plot_id_qt_val_land", "land_lease_plots", "plot_name", "plot_name");
        // } else {
        //     openPullType = "so";
        //     initializeAutocompleteQt("book_code_input_qt", "book_id_qt_val", "book_so", "book_code", "book_name");
        //     initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "sale_order_document", "document_number", "document_number");
        // initializeAutocompleteQt("book_code_input_mo", "book_id_mo_val", "book_mo", "book_code", "book_name");
        // initializeAutocompleteQt("document_no_input_mo", "document_id_mo_val", "mo_document", "document_number", "document_number");
        // if (type === 'land-lease') {
        //     getOrders('land-lease');
        // } else {
        getOrders(openPullType);
        // }
    }

    function getOrders(type = "mo")
    {
        var qtsHTML = ``;
        let departmentOrStoreKey = 'store_location_code';
        let targetTable = document.getElementById('qts_data_table');
        let requesterHTML = ``;
        let stationHTML = ``;
        if (type == 'pwo') {
            targetTable = document.getElementById('qts_data_table_pwo');
        } else if (type == "pi") {
            departmentOrStoreKey = 'department_code';
            targetTable = document.getElementById('qts_data_table_pi');
        }
        const location_id = $("#location_id_qt_val").val();
        const departmentId = $("#department_id_qt_val").val();
        const book_id = $(`#book_id_${type}_val`).val();
        const document_id = $(`#document_id_${type}_val`).val();
        const item_id = $(`#item_id_${type}_val`).val();
        const apiUrl = "{{route('psv.pull.items')}}";
        var selectedIds = [];
        
        var headerRows = document.getElementsByClassName("item_header_rows");
        for (let index = 0; index < headerRows.length; index++) {
            if (type == "mo") {
                var referedId = document.getElementById('mo_id_' + index).value;
            } else if (type == "pwo") {
                var referedId = document.getElementById('pwo_id_' + index).value;
            } else if (type == "pi") {
                var referedId = document.getElementById('pi_id_' + index).value;
            } else {
                var referedId = [];
            }
            selectedIds.push(referedId);
        }
        $.ajax({
            url: apiUrl,
            method: 'GET',
            dataType: 'json',
            data : {
                location_id : location_id,
                department_id : departmentId,
                book_id : book_id,
                document_id : document_id,
                item_id : item_id,
                doc_type : type,
                header_book_id : $("#series_id_input").val(),
                store_id: $("#store_to_id_input").val(),
                store_id_from: $("#store_id_input").val(),
                selected_ids : selectedIds,
                requester_type : $("#requester_type_input").val(),
                requester_department_id : $("#department_id_input").val(),
                requester_user_id : $("#user_id_input").val(),
                station_id : $("#station_id_input").val()
            },
            success: function(data) {
                if (Array.isArray(data.data) && data.data.length > 0) {
                        data.data.forEach((qt, qtIndex) => {
                            if (qt?.header?.requester_name) {
                                requesterHTML = `<td>${qt?.header?.requester_name}</td>`
                            }
                            if (qt?.station_name) {
                                stationHTML = `<td>${qt?.station_name}</td>`
                            }
                            var attributesHTML = ``;
                            qt.attributes.forEach(attribute => {
                                attributesHTML += `<span class="badge rounded-pill badge-light-primary" > ${attribute.attribute_name} : ${attribute.attribute_value} </span>`;
                            });
                            qtsHTML += `
                                <tr>
                                    <td>
                                        <div class="form-check form-check-inline me-0">
                                            <input ${qt?.avl_stock > 0 ? '' : 'disabled'} class="form-check-input pull_checkbox" type="checkbox" name="po_check" id="po_checkbox_${qtIndex}"  doc-id = "${qt?.header.id}" current-doc-id = "0" document-id = "${qt?.header?.id}" so-item-id = "${qt.id}" balance_qty = "${qt?.avl_stock}">
                                        </div> 
                                    </td>   
                                    <td>${qt?.header?.book_code}</td>
                                    <td>${qt?.header?.document_number}</td>
                                    <td>${qt?.header?.document_date}</td>
                                    <td>${qt?.[departmentOrStoreKey]}</td>
                                    ${requesterHTML}
                                    ${stationHTML}
                                    <td>${qt?.item_code}</td>
                                    <td>${attributesHTML}</td>
                                    <td>${qt?.uom?.name}</td>
                                    <td>${qt?.qty}</td>
                                    <td>${qt?.mi_balance_qty}</td>
                                    <td>${qt?.avl_stock}</td>
                                </tr>
                            `
                        });
                }
                targetTable.innerHTML = qtsHTML;
            },
            error: function(xhr) {
                console.error('Error fetching customer data:', xhr.responseText);
                targetTable.innerHTML = '';
            }
        });
     
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
        const apiUrl = "{{route('psv.process.items')}}";
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
                    store_id : $("#store_id_input").val()
                },
                success: function(data) {
                    const currentOrders = data.data;
                    let currentOrderIndexVal = document.getElementsByClassName('item_header_rows').length;
                    currentOrders.forEach((currentOrder, currentOrderIndex) => {
                        if (currentOrder) { //Set all data
                            //
                            if (currentOrderIndex == 0 && openPullType == 'mo') {
                                $("#station_id_input").val(currentOrder.station_id);
                            }
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
                                    // item.balance_qty = item.mi_balance_qty;
                                    if (item.avl_stock < item.mi_balance_qty){
                                        item.mi_balance_qty = item.avl_stock;
                                    }
                                    let itemIdKeyName = "mo_item_id";
                                    let itemIdKeyId = "mo_id";
                                    if (openPullType == "pwo") {
                                        itemIdKeyName = "pwo_item_id";
                                        itemIdKeyId = "pwo_id";
                                    }
                                    if (openPullType == "pi") {
                                        itemIdKeyName = "pi_item_id";
                                        itemIdKeyId = "pi_id";
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
                                // item.so_details.forEach((soDetail, index) => {
                                //     referenceLabelFields += `<input type = "hidden" class = "reference_from_label_${currentOrderIndexVal}" value = "${soDetail.book_code + "-" + soDetail.document_number + " : " + soDetail.balance_qty}"/>`; 
                                // });

                                // var soItemIds = [];
                                // item.so_details.forEach((soDetail) => {
                                //     soItemIds.push(soDetail.id);
                                // });
                                var headerFromStoreId = $("#store_id_input").val();
                                var headerToStoreId = $("#store_to_id_input").val();
                                var headerFromStoreCode = $("#store_id_input").attr("data-name");
                                var headerToStoreCode = $("#store_to_id_input").attr("data-name");
                                var storesFrom = @json($stores);
                                var storesTo = @json($stores);
                                var storesFromHTML = ``;
                                var storesToHTML = ``;
                                storesFrom.forEach(store => {
                                    if (store.id == headerFromStoreId) {
                                        storesFromHTML += `<option value = "${store.id}" selected>${store.store_name}</option>`
                                    } else {
                                        storesFromHTML += `<option value = "${store.id}">${store.store_name}</option>`
                                    }
                                });
                                storesTo.forEach(store => {
                                    if (store.id == headerToStoreId) {
                                        storesToHTML += `<option value = "${store.id}" selected>${store.store_name}</option>`
                                    } else {
                                        storesToHTML += `<option value = "${store.id}">${store.store_name}</option>`
                                    }
                                });

                                let fromSubStoresHTML = ``;
                                currentFromSubStoreArray.forEach(fromSubStore => {
                                    fromSubStoresHTML += `<option value = ${fromSubStore.id} ${fromSubStore.id == item?.sub_store_id ? 'selected' : ''}> ${fromSubStore.name} </option>`;
                                });

                                let toSubStoresHTML = ``;
                                currentToSubStoreArray.forEach(toSubStore => {
                                    toSubStoresHTML += `<option value = ${toSubStore.id}> ${toSubStore.name} </option>`;
                                });


                                // mainTableItem.innerHTML += `
                                // <tr id = "item_row_${currentOrderIndexVal}">
                                //     <td class="customernewsection-form">
                                //     <div class="form-check form-check-primary custom-checkbox">
                                //         <input type="checkbox" class="form-check-input item_row_checks" id="item_row_check_${currentOrderIndexVal}" del-index = "${currentOrderIndexVal}">
                                //         <label class="form-check-label" for="Email"></label>
                                //     </div> 
                                // </td>
                                //     <td class="poprod-decpt"> 
                                //     <input readonly type="text" id = "items_dropdown_${currentOrderIndexVal}" name="item_code[${currentOrderIndexVal}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" autocomplete="off" data-name="${item?.item?.item_name}" data-code="${item?.item?.item_code}" data-id="${item?.item?.id}" hsn_code = "${item?.item?.hsn?.code}" item-name = "${item?.item?.item_name}" specs = '${JSON.stringify(item?.item?.specifications)}' attribute-array = '${JSON.stringify(item?.item_attributes_array)}'  value = "${item?.item?.item_code}" item-locations = "[]">
                                //     <input type = "hidden" name = "item_id[]" id = "items_dropdown_${currentOrderIndexVal}_value" value = "${item?.item_id}"></input>
                                //     <input type = "hidden" value = "${item?.so_item_id}" name = "so_item_id[${currentOrderIndexVal}]"
                                // </td>
                                
                                // <td class="poprod-decpt">
                                //         <input type="text" id = "items_name_${currentOrderIndexVal}" name = "item_name[${currentOrderIndexVal}]" class="form-control mw-100"  value = "${item?.item?.item_name}" readonly>
                                //     </td>
                                // <td class="poprod-decpt"> 
                                //     <button id = "attribute_button_${currentOrderIndexVal}" type = "button" data-bs-toggle="modal"  ${item?.item_attributes_array?.length > 0 ? '' : 'disabled'} onclick = "setItemAttributes('items_dropdown_${currentOrderIndexVal}', ${currentOrderIndexVal});" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                                //     <input type = "hidden" name = "attribute_value_${currentOrderIndexVal}" />
                                //     </td>
                                // <td>
                                //     <select class="form-select" name = "uom_id[]" id = "uom_dropdown_${currentOrderIndexVal}">
                                //     </select> 
                                // </td>
                                // <td class = "location_transfer">
                                //     <div class="d-flex">
                                // <select class="form-select" name = "item_store_to[${currentOrderIndexVal}]" id = "item_store_to_${currentOrderIndexVal}" style = "min-width:85%;" >
                                //     ${storesHTML}
                                //     </select>
                                //     <div id = "data_stores_to_${currentOrderIndexVal}" class="me-50 cursor-pointer item_locations_to" style = "margin-top:5px;"   onclick = "openToLocationModal(${currentOrderIndexVal})">        <span data-bs-toggle="tooltip" data-bs-placement="top" title="Location" class="text-primary"><i data-feather="map-pin"></i></span></div>
                                //     </div>
                                //     </td>
                                
                                //     <td><input type="text" id = "item_confirmed_qty_${currentOrderIndexVal}" name = "item_qty[${currentOrderIndexVal}]" oninput = "changeItemQty(this, ${currentOrderIndexVal});" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" value = "${item.mi_balance_qty}" max = "${item.mi_balance_qty}"/></td>
                                //     <td><input type="text" id = "item_rate_${currentOrderIndexVal}" name = "item_rate[${currentOrderIndexVal}]" oninput = "changeItemRate(this, '${currentOrderIndexVal}');" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" value = "${item.mi_balance_qty /1}"/></td> 
                                //     <td><input type="text" id = "item_value_${currentOrderIndexVal}" disabled class="form-control mw-100 text-end item_values_input"  /></td>
                                //     <td>
                                //     <input type="text" id = "customers_dropdown_${currentOrderIndexVal}" name="customer_code[${currentOrderIndexVal}]" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input autocomplete="off" value = "${item?.so?.customer_code}" readonly>
                                //     <input type = "hidden" name = "customer_id[${currentOrderIndexVal}]" id = "customers_dropdown_${currentOrderIndexVal}_value" value = "${item?.mo?.customer_id}"></input>
                                //     </td>
                                //     <td>
                                //     <input class = "form-control mw-100" type = "text" name = "item_order_no[${currentOrderIndexVal}]" readonly value = "${item?.so?.document_number}" />
                                //     </td>
                                // <td>
                                // <div class="d-flex">
                                //         <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick = "setItemRemarks('item_remarks_${currentOrderIndexVal}');">        
                                //         <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span></div>
                                //         <div class="me-50 cursor-pointer item_bundles" onclick = "assignDefaultBundleInfoArray(${currentOrderIndexVal}, true)" id = "item_bundles_${currentOrderIndexVal}" style = "display:none;">    <span data-bs-toggle="tooltip" data-bs-placement="top" title="Details" class="text-primary"><i data-feather="info"></i></span>
                                //     </div>
                                // <input type = "hidden" id = "item_remarks_${currentOrderIndexVal}" name = "item_remarks[${currentOrderIndexVal}]" />
                                // </td>
                                
                                // </tr>
                                // `;
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

                                    </td>
                                    
                                    <td class="poprod-decpt">
                                            <input type="text" id = "items_name_${currentOrderIndexVal}" name = "item_name[${currentOrderIndexVal}]" class="form-control mw-100"   value = "${item?.item?.item_name}" readonly>
                                        </td>
                                    <td class="poprod-decpt" id='attribute_section_${currentOrderIndexVal}'> 
                                        <button id = "attribute_button_${currentOrderIndexVal}" type = "button" data-bs-toggle="modal" onclick = "setItemAttributes('items_dropdown_${currentOrderIndexVal}', ${currentOrderIndexVal}, true);" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                                        <input type = "hidden" name = "attribute_value_${currentOrderIndexVal}" />
                                        </td>
                                    <td>
                                        <select class="form-select" name = "uom_id[]" id = "uom_dropdown_${currentOrderIndexVal}">
                                        </select> 
                                    </td>
                                    <td class = "sub_location from_sub_store_dependent">

                                    <select class="form-select" style = "min-width:100%;" name = "item_sub_store_from[${currentOrderIndexVal}]" id = "item_sub_store_from_${currentOrderIndexVal}">
                                                                                    ${fromSubStoresHTML}
                                                                                </select>
                                    </td>
                                    <td class = "sub_location to_sub_store_dependent">

                                    <select class="form-select" style = "min-width:100%;" name = "item_sub_store_to[${currentOrderIndexVal}]" id = "item_sub_store_to_${currentOrderIndexVal}">
                                                                                    ${toSubStoresHTML}
                                                                                </select>
                                    </td>
                                    <input type = "hidden" value = "${$("#store_to_id_input").val()}" name = "item_store_to[${currentOrderIndexVal}]" />
                                    <input type = "hidden" value = "${$("#store_id_input").val()}" name = "item_store_from[${currentOrderIndexVal}]" />

                                        <td class = "consumption consumption_dept">
                                        <div class="d-flex">
                                        <select class="form-select" name = "item_department_id[${currentOrderIndexVal}]" id = "item_department_id_${currentOrderIndexVal}" style = "min-width:85%;">
                                        <option value = "${currentOrder?.department_id}">${currentOrder?.department_name}</option> 
                                        </select>
                                        </div>
                                        </td>
                                        <td class = "consumption consumption_user">
                                        <div class="d-flex">
                                        <select class="form-select" name = "item_user_id[${currentOrderIndexVal}]" id = "item_user_id_${currentOrderIndexVal}" style = "min-width:85%;">
                                            <option selected value = "${currentOrder?.user_id}">${currentOrder?.requester_name}</option> 
                                        </select>
                                        </div>
                                        </td>
                                    <td><input type="text" id = "item_confirmed_qty_${currentOrderIndexVal}" name = "item_qty[${currentOrderIndexVal}]" oninput = "changeItemQty(this, ${currentOrderIndexVal});" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" value = "${item?.mi_balance_qty}" max = "${item?.mi_balance_qty}"/></td>
                                    <td><input type="text" id = "item_rate_${currentOrderIndexVal}" readonly name = "item_rate[]" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" value = "${item?.rate}"/></td> 
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
                                document.getElementById('uom_dropdown_' + currentOrderIndexVal).innerHTML = itemUomsHTML;
                                const qtyInput = document.getElementById('item_confirmed_qty_' + currentOrderIndexVal);

                                const itemCodeInput = document.getElementById('items_dropdown_' + currentOrderIndexVal);
                                const uomCodeInput = document.getElementById('uom_dropdown_' + currentOrderIndexVal);
                                const storeCodeInput = document.getElementById('item_store_from_' + currentOrderIndexVal);
                                itemCodeInput.addEventListener('input', function() {
                                    checkStockData(currentOrderIndexVal);
                                });
                                uomCodeInput.addEventListener('input', function() {
                                    checkStockData(currentOrderIndexVal);
                                });
                                // storeCodeInput.addEventListener('input', function() {
                                //     checkStockData(currentOrderIndexVal);
                                // });
                                $("#issue_type_input").trigger("input");
                                // assignDefaultToLocationArray(currentOrderIndexVal);
                                currentOrderIndexVal += 1;
                                });
                            } 

                            // for (let index = 0; index < currentOrderIndexVal; index++) {
                            //     getStoresData(index, document.getElementById('item_confirmed_qty_' + index).value);
                                
                            // }
                            setAttributesUI(itemIndex);
                            
                            
                            

                        }
                    });

                    onIssueTypeChange(document.getElementById('issue_type_input'), false);
                    toSubStoreDependencyRender();
                    fromSubStoreDependencyRender();
                            
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
    const apiURL = "{{route('psv.posting.get')}}";
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
    const postingApiUrl = "{{route('psv.post')}}"
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
            url: "{{route('psv.revoke')}}",
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

    function onHeaderStoreChange(element, type)
    {
        const currentVal = element.value;
        const issueTypeVal = $("#issue_type_input").val();
        var otherVal = null;
        if (type === "from") {
            otherVal = $("#store_to_id_input").val();
        } else {
            otherVal = $("#store_id_input").val();
        }
        if (currentVal == otherVal && issueTypeVal != 'Sub Location Transfer') {
            Swal.fire({
                title: 'Error!',
                text: 'From and To Location cannot be same',
                icon: 'error',
            });
            element.value = "";
            return;
        }
        if (type === 'to') 
        {
            let typeAttribute = element.options[element.selectedIndex].getAttribute('store-type');
            if (typeAttribute == 'Shop floor')
            {
                $("#station_id_header").removeClass('d-none');
            }
            else
            {
                $("#station_id_header").addClass('d-none');
            }
            onHeaderToLocationChange(element);
        } else {
            onHeaderFromLocationChange(element);
        }
        enableDisableQtButton();
    }
    function onItemStoreChange(element, type, index)
    {
        const currentVal = element.value;
        var otherVal = null;
        if (type === "from") {
            otherVal = $("#item_store_to_" + index).val();
        } else {
            otherVal = $("#item_store_from_" + index).val();
        }
        if (currentVal == otherVal) {
            Swal.fire({
                title: 'Error!',
                text: 'From and To Location cannot be same',
                icon: 'error',
            });
            element.value = "";
            return;
        }
    }

    function assignDefaultToLocationArray(itemIndex)
    {
        const storeElement = document.getElementById('data_stores_to_' + itemIndex);
        var existingStoreArray = [];
        if (storeElement.getAttribute('data-stores')) {
            existingStoreArray = JSON.parse(decodeURIComponent(storeElement.getAttribute('data-stores')));
        }
        //Create
        if (!existingStoreArray.length) {
            const defaultStore = document.getElementById('item_store_to_' + itemIndex);
            const defaultStoreId = defaultStore.value;
            const defaultStoreCode = defaultStore.options[defaultStore.selectedIndex].text;
            const qtyInput = document.getElementById('item_confirmed_qty_' + itemIndex);
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
                            qty : qtyInput.value
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
        const qtyInput = document.getElementById('item_confirmed_qty_' + itemIndex);


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
        const qtyInput = document.getElementById('item_confirmed_qty_' + itemIndex);
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

    function resetIssueTypeFields()
    {
        $("#store_to_id_input").val('');
        $("#vendor_id_input").val('');
        $("#vendor_store_id_input").val('');
        $("#department_id_input").val('');
        $("#user_id_dropdown").val('');
        $("#station_id_input").val('');
    }

    function reloadLocationsDropdown(type)
    {
        let fromLocationHeader = document.getElementById("store_id_input");
        let toLocationHeader = document.getElementById("store_to_id_input");
        let locationHTML = ``;
        $.ajax({
            url: "{{ route('psv.multi-store-location') }}",
            type: "GET",
            data: {
                type : type
            },
            dataType: "json",
            success: function(data) {
                if (data.status == 200) {
                    data.data.forEach(loc => {
                        locationHTML += `<option store-type = "${loc.store_location_type}" value = "${loc.id}">${loc.store_name}</option>`
                    });
                } else {
                    locationHTML = ``;
                }
                fromLocationHeader.innerHTML = locationHTML;
                toLocationHeader.innerHTML = locationHTML;
                toLocationHeader.value = "";
                applyIssueTypeChange(type);
            },
            error : function(xhr){
                console.error('Error fetching customer data:', xhr.responseText);
                fromLocationHeader.innerHTML = ``;
                toLocationHeader.innerHTML = ``;
                toLocationHeader.value = "";
                applyIssueTypeChange(type);
                Swal.fire({
                    title: 'Error!',
                    text: 'No Stores found',
                    icon: 'error',
                });
                return;
            }
        });
    }

    function onIssueTypeChange(element, resetDropdown = true)
    {
        const selectedType = element.value;
        if (resetDropdown == false) {
            applyIssueTypeChange(selectedType)
        } else {
            resetIssueTypeFields();
            reloadLocationsDropdown(selectedType, selectedType, resetDropdown);
        }
    }

    function applyIssueTypeChange(selectedType)
    {
        if (selectedType == 'Location Transfer') {
            implementIssueTypeChange('.location_transfer, .sub_location','.sub_contracting, .consumption, .sub_loc_transfer');
        } else if (selectedType == 'Sub Location Transfer') {
            implementIssueTypeChange('.sub_loc_transfer, .sub_location','.location_transfer, .consumption, .sub_contracting');
        } else if (selectedType == 'Consumption') {
        } else if (selectedType == 'Sub Contracting') {
            implementIssueTypeChange('.sub_contracting, .sub_location','.location_transfer, .consumption, .sub_loc_transfer');
        } else if (selectedType == 'Consumption') {
            implementIssueTypeChange('.consumption','.location_transfer, .sub_contracting, .sub_loc_transfer');
        }
    }

    function implementIssueTypeChange(targetClasses, querySelectorOtherClasses)
    {
        var targetElements = document.querySelectorAll(targetClasses);
        for (let index = 0; index < targetElements.length; index++) {
            targetElements[index].style.removeProperty("display");
            targetElements[index].classList.remove("d-none");
        }
        var otherElements = document.querySelectorAll(querySelectorOtherClasses);
        for (let index = 0; index < otherElements.length; index++) {
            otherElements[index].style.display = "none";
            otherElements[index].classList.add("d-none");
        }
        $("#vendor_id_input").trigger('input');
        if (targetClasses.includes('consumption')) {
            if (requesterTypeParam == "Department") {
                $("#user_id_header").css("display", "none");
                $("#department_id_header").css("display", "");

                $(".consumption_user").css('display', 'none');
                $(".consumption_dept").css('display', '');
            } else {
                $("#user_id_header").css("display", "");
                $("#department_id_header").css("display", "none");

                $(".consumption_user").css('display', '');
                $(".consumption_dept").css('display', 'none');
            }
        } else {
            $("#user_id_header").css("display", "none");
            $("#department_id_header").css("display", "none");
            $(".consumption_user").css('display', 'none');
            $(".consumption_dept").css('display', 'none');
        }
        let fromLocationHeader = document.getElementById('from_location_header_label');
        if (targetClasses.includes('sub_loc_transfer')) {
            fromLocationHeader.innerHTML = `Location <span class="text-danger">*</span>`;
            $("#store_to_id_input").val($("#store_id_input").val()).trigger('input');
        } else {
            fromLocationHeader.innerHTML = `From Location <span class="text-danger">*</span>`;
        }
        enableDisableQtButton();
    }

    function onVendorChange(element)
    {
        const vendorId = element.value;
        const vendorInput = document.getElementById('vendor_store_id_input');
        let vendorIdInputHTML = ``;
        if (vendorId) {
            $.ajax({
                url: "{{ route('psv.vendor.stores') }}",
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
                                    vendorIdInputHTML += `<option selected value = '${store.id}'>${store.store_name}</option>`;
                                } else {
                                    vendorIdInputHTML += `<option value = '${store.id}'>${store.store_name}</option>`;
                                }
                            } else {
                                vendorIdInputHTML += `<option value = '${store.id}'>${store.store_name}</option>`;

                            }
                        });
                        vendorInput.innerHTML = vendorIdInputHTML;
                    } else {
                        vendorInput.innerHTML = vendorIdInputHTML;
                        element.value = "";
                        Swal.fire({
                            title: 'Error!',
                            text: 'No Stores found',
                            icon: 'error',
                        });
                        return;
                    }
                    
                },
                error : function(xhr){
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
    }

    function fromSubStoreDependencyRender() 
    {
        let dependentFields = document.querySelectorAll('.from_sub_store_dependent');
        for (let index = 0; index < dependentFields.length; index++) {
            if (currentFromSubStoreArray.length > 0) {
                dependentFields[index].classList.remove('d-none');
            } else {
                dependentFields[index].classList.add('d-none');
            }
        }
    }

    onHeaderFromLocationChange(document.getElementById('store_id_input'));

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
                type : ['Stock', 'Shop floor']
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

    // function renderStoreOnItemChange(type = 'from', index)
    // {
    //     let currentSubLocArray = [];
    //     let targetDocument = null;
    //     if (type === 'from') {
    //         currentSubLocArray = currentFromSubStoreArray;
    //         targetDocument = document.getElementById('item_sub_store_from_' + index);
    //     } else {
    //         currentSubLocArray = currentToSubStoreArray;
    //         targetDocument = document.getElementById('item_sub_store_to_' + index);
    //     }
    //     let newInnerHTML = ``;
    //     currentSubLocArray.forEach(subLoc => {
    //         newInnerHTML += `<option value = "${subLoc.id}">${subLoc.name}</option>`
    //     });
    //     targetDocument.innerHTML = newInnerHTML;
    // }

    // function onItemFromStoreChange(element, index)
    // {
    //     const currentVal = $("#store_id_input").val() + "-" + element.value;
    //     const otherVal = $("#store_to_id_input").val() + "-" + $("#item_sub_store_to_" + index).val();
    //     if (currentVal === otherVal)
    //     {
    //         element.value = "";
    //         Swal.fire({
    //             title: 'Error!',
    //             text: "From and to Store cannot be same",
    //             icon: 'error',
    //         });
    //         return;
    //     }
    // }

    // function onItemToStoreChange(element, index)
    // {
    //     const currentVal = $("#store_to_id_input").val() + "-" + element.value;
    //     const otherVal = $("#store_id_input").val() + "-" + $("#item_sub_store_from_" + index).val();
    //     if (currentVal === otherVal)
    //     {
    //         element.value = "";
    //         Swal.fire({
    //             title: 'Error!',
    //             text: "From and to Store cannot be same",
    //             icon: 'error',
    //         });
    //         return;
    //     }
    // }
    function submitAttr(id) {
        var item_index = $('#attributes_table_modal').attr('item-index');
        onItemClick(item_index);
        const input = document.getElementById('item_physical_qty_' + item_index);
        if(!(order && !order.document_status=="{{App\Helpers\ConstantHelper::DRAFT}}"))
        {
            getStoresData(item_index, input ? (input.value ?? 0) : 0);
        }
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
        let attributeUI = `<div data-bs-toggle="modal" id="attribute_button_${currentItemIndex}" onclick = "setItemAttributes('items_dropdown_${currentItemIndex}', ${currentItemIndex},{{ isset($order) && !$order->document_status==App\Helpers\ConstantHelper::DRAFT ? '' : 'true' }});" data-bs-target="#attribute" style = "white-space:nowrap; cursor:pointer;">`;
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
            if ((attrTotalChar + Number(currentStringLength)) <= 15) {
                attributeUI += `
                <span class="badge rounded-pill badge-light-primary"><strong>${short ? attrArr.short_name : attrArr.group_name}</strong>: ${currentSelectedValue ? currentSelectedValue :''}</span>
                `;
            } else {
                //Get the remaining length
                let remainingLength =  15 - attrTotalChar;
                console.log(attrArr);
                //Only show the data if remaining length is greater than 3
                if (remainingLength >= 3) {
                    attributeUI += `<span class="badge rounded-pill badge-light-primary"><strong>${short ? attrArr.short_name.substring(0, remainingLength - 1) : attrArr.group_name.substring(0, remainingLength - 1)}..</strong></span>`
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

    // Opens import modal with store/type/header context
    function openImportItemModal(type, psvHeaderId = null) {
        const storeId = $('#store_id_input').val();
        if (!storeId) {
            Swal.fire({
                title: 'Error!',
                text: 'Please select a store first.',
                icon: 'error',
            });
            return false;
        }

        // Reset file and modal state
        $('#fileUpload').val('');
        $('#fileNameDisplay').hide();
        $('#proceedBtn').hide();
        $('#upload-error').hide();
        $('#uploadProgress').addClass('d-none');
        $('#uploadProgressBar').css('width', '0%').text('0%');

        // Open modal and inject hidden fields
        $("#importItemModal").modal('show');
        const form = $('#importItemModal').find('form');
        form.find('input[name="store_id"], input[name="type"], input[name="psv_header_id"]').remove();
        form.append(`<input type="hidden" name="store_id" value="${storeId}">`);
        form.append(`<input type="hidden" name="type" value="${type}">`);
        form.append(`<input type="hidden" name="psv_header_id" value="${psvHeaderId ?? ''}">`);
    }

    $(function() {
        let parsedValidRows = [];

        // Handle file selection
        $(document).on('change', '#fileUpload', function (e) {
            const file = e.target.files[0];
            if (!file) return;
            handleFileSelected(file); // optional function for UI preview
            $('#fileNameDisplay').removeClass('d-none');
            $('#selectedFileName').text(file.name);
            $('#proceedBtn').show();
        });

        // Proceed button: Upload & parse file
        $(document).on('click', '#proceedBtn', function () {
            const fileInput = $('#fileUpload')[0];
            if (!fileInput.files.length) {
                Swal.fire('Error', 'Please select a file to upload.', 'error');
                return;
            }
            const file = fileInput.files[0];
            let formData = new FormData();
            formData.append('attachment', file);

            // Include hidden inputs if needed
            $('#importItemModal input[type=hidden]').each(function() {
                formData.append($(this).attr('name'), $(this).val());
            });

            $('#upload-error').hide().html('');
            $('#uploadProgress').removeClass('d-none');
            $('#uploadProgressBar').css('width', '0%').text('0%');

            $.ajax({
                url: "{{ route('generic.import.save', ['alias' => 'psv']) }}",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                xhr: function () {
                    let xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function (evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            $('#uploadProgressBar').css('width', percentComplete + '%').text(percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                success: function (response) {
                    $('#uploadProgressBar').addClass('bg-success').text('Uploaded');

                    const validRows = response.data.valid || [];
                    const invalidRows = response.data.invalid || [];
                    const headers = response.headers || {};

                    $('#valid-count').text(`(${validRows.length})`);
                    $('#invalid-count').text(`(${invalidRows.length})`);
                    $('#parsedPreview').removeClass('d-none');

                    // Build table headers
                    function buildHeaderRow(headersMap, target) {
                        let headerHtml = '';
                        for (const key in headersMap) {
                            headerHtml += `<th>${headersMap[key]}</th>`;
                        }
                        headerHtml += `<th>Row</th><th>Errors</th>`;
                        $(target).html(headerHtml);
                    }
                    buildHeaderRow(headers, '#valid-table-header');
                    buildHeaderRow(headers, '#invalid-table-header');

                    // Build table body
                    function buildTableRows(data, headersMap) {
                        return data.map(row => {
                            let rowHtml = '<tr>';
                            for (const key in headersMap) {
                                rowHtml += `<td>${row[key] ?? ''}</td>`;
                            }
                            rowHtml += `<td>${row.row_number ?? ''}</td>`;
                            if (row.errors?.length) {
                                const errors = row.errors.map(e => `<li>${e}</li>`).join('');
                                rowHtml += `<td><ul class="mb-0">${errors}</ul></td>`;
                            } else {
                                rowHtml += `<td>-</td>`;
                            }
                            rowHtml += '</tr>';
                            return rowHtml;
                        }).join('');
                    }

                    parsedValidRows = validRows;
                    $('#valid-table-body').html(buildTableRows(validRows, headers));
                    $('#invalid-table-body').html(buildTableRows(invalidRows, headers));
                    window.lastParsedImport = { valid: validRows, invalid: invalidRows, headers: headers };
                    $("#submitBtn").removeClass('d-none');

                    Swal.fire({
                        title: 'Success!',
                        text: response.message || 'File uploaded and parsed successfully.',
                        icon: 'success',
                    });
                },
                error: function (xhr) {
                    $('#upload-error').removeClass('d-none').text(xhr.responseJSON?.message || 'Upload failed');
                    $('#uploadProgress').addClass('d-none');
                    $('#uploadProgressBar').removeClass('bg-success').css('width', '0%').text('0%');
                }
            });
        });

        // Import valid rows into main table
        $('#submitBtn').on('click', function () {
            const validRows = window.lastParsedImport?.valid || [];
            const headers = window.lastParsedImport?.headers || {};
            const tbody = $('#item_header');
            console.log('table',tbody);
            tbody.empty(); // Clear existing rows
            let currentIndex = tbody.find('tr').length;
            console.log('validRows', validRows);
            validRows.forEach((row, i) => {
                console.log('Processing row:', row);
                const index = currentIndex + i;
                const itemId = row.item_id || '';
                const itemCode = row.item_code || '';
                const itemName = row.item_name || '';
                const uomId = row.uom_id || '';
                const uomName = row.uom_name || '';
                const rate = row.rate || 0;
                const physicalQty = row.physical_qty || 0;
                const remarks = row.remarks || '';
                const attributeValue = row.attribute_value || '';
                const attributeGroupId = row.attribute_group_id || '';
                const itemValue = (rate * physicalQty).toFixed(2);

                const rowHtml = `
                <tr id="item_row_${index}" class="item_header_rows" onclick="onItemClick('${index}');">
                    <input type="hidden" id="psv_item_id_${index}" name="psv_item_id[]" value="">
                    <td class="customernewsection-form">
                        <div class="form-check form-check-primary custom-checkbox">
                            <input type="checkbox" class="form-check-input item_row_checks" id="item_checkbox_${index}" del-index="${index}">
                            <label class="form-check-label" for="item_checkbox_${index}"></label>
                        </div>
                    </td>
                    <td class="poprod-decpt">
                        <input type="text" id="items_dropdown_${index}" name="item_code[${index}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" autocomplete="off" data-name="${row.item_name ?? ''}" data-code="${row.item_code ?? ''}" data-id="${row.item_id ?? ''}" hsn_code="${row.hsn_code ?? ''}" item-name="${row.item_name ?? ''}" specs='${JSON.stringify(row.specifications ?? [])}' attribute-array='${JSON.stringify(row.item_attribute_array ?? [])}' value="${row.item_code ?? ''}" readonly>
                        <input type="hidden" name="item_id[]" id="items_dropdown_${index}_value" value="${row.item_id ?? ''}">
                    </td>
                    <td>
                        <input type="text" id="items_name_${index}" class="form-control mw-100" value="${itemName}" name="item_name[${index}]" readonly>
                    </td>
                    <td id="attribute_section_${index}">
                        <button id="attribute_button_${index}" type="button" class="btn p-25 btn-sm btn-outline-secondary"" onclick="setItemAttributes('items_dropdown_${index}', '${index}', false);" data-bs-toggle="modal" data-bs-target="#attribute">Attributes</button>
                        <input type="hidden" name="attribute_value_${index}" value="${attributeValue}">
                    </td>
                    <td>
                        <select class="form-select" name="uom_id[]" id="uom_dropdown_${index}">
                            <option value="${uomId}" selected>${uomName}</option>
                        </select>
                    </td>
                    <td class="numeric-alignment">
                        <input type="text" id="item_physical_qty_${index}" value="${physicalQty}" name="item_physical_qty[${index}]" class="form-control mw-100 text-end" oninput="setVariance(this, ${index});setValue(${index});">
                    </td>
                    <td class="numeric-alignment">
                        <input type="text" id="item_confirmed_qty_${index}" value="0.00" name="item_confirmed_qty[${index}]" class="form-control mw-100 text-end" readonly>
                    </td>
                    <td class="numeric-alignment">
                        <input type="text" id="item_unconfirmed_qty_${index}" value="0.00" name="item_unconfirmed_qty[${index}]" class="form-control mw-100 text-end" readonly>
                    </td>
                    <td class="numeric-alignment">
                        <input type="text" id="item_variance_qty_${index}" value="${physicalQty}" name="item_balance_qty[${index}]" class="form-control mw-100 text-end" readonly>
                    </td>
                    <td class="numeric-alignment">
                        <input type="text" id="item_rate_${index}" value="${rate}" name="item_rate[${index}]" class="form-control mw-100 text-end" oninput="setValue(${index});">
                    </td>
                    <td class="numeric-alignment">
                        <input type="text" id="item_value_${index}" value="${itemValue}" name="item_value[${index}]" class="form-control mw-100 text-end" readonly>
                    </td>
                    <td>
                        <div class="d-flex">
                            <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick="setItemRemarks('item_remarks_${index}');">
                                <span data-bs-toggle="tooltip" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span>
                            </div>
                        </div>
                        <input type="hidden" id="item_remarks_${index}" name="item_remarks[${index}]" value="${remarks}">
                    </td>
                </tr>`;
                tbody.append(rowHtml);
                // Post-append initializations
                initializeAutocomplete1("items_dropdown_" + index, index);
                onItemClick(index);
                const itemCodeInput = document.getElementById('items_dropdown_' + index);
                const uomCodeInput = document.getElementById('uom_dropdown_' + index);
                itemCodeInput.addEventListener('input', () => checkStockData(index));
                uomCodeInput.addEventListener('input', () => checkStockData(index));
                setAttributesUI(index);
            });
            renderIcons()
            $(".Item_Search_section").hide();
            $('#importItemModal').modal('hide');
        });
        // Cancel button
        $('#cancelBtn').on('click', function () {
            $('#fileUpload').val('');
            $('#fileNameDisplay').addClass('d-none');
            $('#upload-error').hide();
            $('#proceedBtn').hide();
        });

        // Download invalid rows by re-parsing the file
        $('#errorDownloadBtn').on('click', function () {
            const fileInput = $('#fileUpload')[0];
            if (!fileInput.files.length) {
                Swal.fire('Error', 'Please upload a file first to re-parse.', 'error');
                return;
            }
            const file = fileInput.files[0];
            let formData = new FormData();
            formData.append('attachment', file);
            formData.append('alias', 'psv');

            fetch("{{ route('generic.import.invalid.download', ['alias' => 'psv']) }}", {
                method: 'POST',
                body: formData
            }).then(response => {
                if (!response.ok) throw new Error('Download failed');
                return response.blob();
            }).then(blob => {
                const link = document.createElement('a');
                link.href = window.URL.createObjectURL(blob);
                link.download = "invalid_rows.xlsx";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }).catch(err => {
                Swal.fire('Error', err.message, 'error');
            });
        });

        // Sample download
        $('#sampleBtn').on('click', function () {
            $.ajax({
                url: "{{ route('generic.import.sample.download', ['alias' => 'psv']) }}",
                type: "GET",
                xhrFields: { responseType: 'blob' },
                success: function (data, status, xhr) {
                    let disposition = xhr.getResponseHeader('Content-Disposition');
                    let filename = "sample_import.xlsx";
                    if (disposition && disposition.indexOf('filename=') !== -1) {
                        let matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                        if (matches?.[1]) filename = matches[1].replace(/['"]/g, '');
                    }
                    const blob = new Blob([data], { type: xhr.getResponseHeader('Content-Type') });
                    const link = document.createElement('a');
                    link.href = window.URL.createObjectURL(blob);
                    link.download = filename;
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                },
                error: function () {
                    Swal.fire('Error', 'Failed to download sample file.', 'error');
                }
            });
        });
    function handleFileSelected(file) {
        const fileName = file.name;
        const fileSize = file.size;
        const fileExtension = fileName.split('.').pop().toLowerCase();
        const ALLOWED_EXTENSIONS = ['xls', 'xlsx'];
        const MAX_FILE_SIZE = 30 * 1024 * 1024;

        $('#upload-error').hide().html('');

        if (!ALLOWED_EXTENSIONS.includes(fileExtension)) {
            displayError(`Invalid file type. Allowed: ${ALLOWED_EXTENSIONS.join(', ')}`);
            $('#fileUpload').val('');
            return;
        }

        if (fileSize > MAX_FILE_SIZE) {
            displayError(`File too large. Max allowed size is ${MAX_FILE_SIZE / (1024 * 1024)} MB.`);
            $('#fileUpload').val('');
            return;
        }

        $('#selectedFileName').text(fileName);
        $('#fileNameDisplay').removeClass('d-none').show();
        $('#proceedBtn').show();
    }

    function displayError(message) {
        $('#upload-error').html(message).removeClass('d-none').show();
        $('#fileNameDisplay').hide();
        $('#proceedBtn').hide();
    }
});

// function populateTable(tableBodySelector, items) {
//     const tableBody = $(tableBodySelector);
//     tableBody.empty();

//     if (items.length > 0) {
//         items.forEach((item, index) => {
//             const row = `
//                 <tr>
//                     <td>${index + 1}</td>
//                     <td>${item.item_code}</td>
//                     <td>${item.item_name}</td>
//                     <td>${item.uom_code}</td>
//                     <td>${item.hsn_code}</td>
//                     <td>${item.store_code}</td>
//                     <td>${item.order_qty}</td>
//                     <td>${item.rate}</td>
//                     <td class="${item.status === 'success' ? 'text-success' : 'text-danger'}">
//                         ${item.status === 'success' ? 'Success' : item.reason}
//                     </td>
//                 </tr>
//             `;
//             tableBody.append(row);
//         });
//     } else {
//         tableBody.append('<tr><td colspan="9" class="text-center">No records found</td></tr>');
//     }
// }
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
                <input type="hidden" id="psv_item_id_${index}" name="psv_item_id[]" value="${item.id}" ${item.is_editable ? '' : 'readonly'}>
                <td class="customernewsection-form">
                    <div class="form-check form-check-primary custom-checkbox">
                        <input type="checkbox" class="form-check-input item_row_checks" id="item_checkbox_${index}" del-index="${index}">
                        <label class="form-check-label" for="item_checkbox_${index}"></label>
                    </div>
                </td>
                <td class="poprod-decpt">
                    <input type="text" id="items_dropdown_${index}" name="item_code[${index}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" autocomplete="off"
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
                    <input type="text" id="items_name_${index}" class="form-control mw-100" value="${item.item?.item_name}" name="item_name[${index}]" readonly>
                </td>
                <td class="poprod-decpt" id="attribute_section_${index}">
                    <button id="attribute_button_${index}" type="button" data-bs-toggle="modal" onclick="setItemAttributes('items_dropdown_${index}', '${index}', 'true')" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                    <input type="hidden" name="attribute_value_${index}">
                </td>
                <td><select class="form-select" readonly name="uom_id[]" id="uom_dropdown_${index}">
                <option selected value="${item.uom_id}">${item.uom_code}</option></select></td>
                <td class="numeric-alignment">
                    <input type="text" id="item_physical_qty_${index}" value="${item.verified_qty}" name="item_physical_qty[${index}]" oninput='setVariance(this,${index});setValue(${index});' class="form-control physical_qty mw-100 text-end">
                </td>
                <td class="numeric-alignment">
                    <input type="text" id="item_confirmed_qty_${index}" 
                        value="${order.document_status !== 'approved' && order.document_status !== 'approval_not_required' ? 0.00 : item.confirmed_qty}" 
                        name="item_confirmed_qty[${index}]" 
                        class="form-control mw-100 text-end" readonly>
                </td>
                <td class="numeric-alignment">
                    <input type="text" id="item_unconfirmed_qty_${index}" 
                        value="${order.document_status !== 'approved' && order.document_status !== 'approval_not_required' ? 0.00 : item.unconfirmed_qty}" 
                        name="item_unconfirmed_qty[${index}]" 
                        class="form-control mw-100 text-end" readonly>
                </td>
                <td class="numeric-alignment">
                    <input type="text" id="item_variance_qty_${index}" value="${item.verified_qty - item.confirmed_qty}" name="item_balance_qty[${index}]" class="form-control mw-100 text-end" readonly>
                </td>
                <td class="numeric-alignment">
                    <input type="text" id="item_rate_${index}" value="${item.rate}" name="item_rate[${index}]" class="form-control mw-100 rate text-end" oninput="setValue(${index});">
                </td>
                <td class="numeric-alignment">
                    <input type="text" id="item_value_${index}" value="${item.total_amount}" name="item_value[${index}]" class="form-control mw-100 text-end" readonly>
                </td>
                <td>
                    <div class="d-flex">
                        <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick="setItemRemarks('item_remarks_${index}');">
                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span>
                        </div>
                    </div>
                    <input type="hidden" id="item_remarks_${index}" name="item_remarks[${index}]">
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


function clearFilters() {
        document.getElementById('filter_sub_type').value = '';
        document.getElementById('filter_category').value = '';
        document.getElementById('filter_sub_category').value = '';
        document.getElementById('filter_hsn').value = '';
        document.getElementById('filter_item_name_code').value = '';
        document.getElementById('filter_sub_type_id').value = '';
        document.getElementById('filter_category_id').value = '';
        document.getElementById('filter_sub_category_id').value = '';
        document.getElementById('filter_hsn_id').value = '';
        document.getElementById('filter_attribute').innerHTML = '';
        document.getElementById('filter_item_name_code_id').value = '';
        $("#filter_item_name_code_id").trigger('change');
        selectedValues={};
    }
$("#generateReport").on('click', function() {
    $('#generated').val(1);
    // Check if store_id_input and sub_store_id_input have values
    const storeId = $('#store_id_input').val();
    const subStoreId = $('#sub_store_id_input').val();

    if (!storeId) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select a Store before generating the report.',
            icon: 'error',
        });
        return;
    }
    if (!subStoreId) {
        Swal.fire({
            title: 'Error!',
            text: 'Please select a Sub Store before generating the report.',
            icon: 'error',
        });
        return;
    }
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
            url: "{{ route('psv.search.items') }}",
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
</script>
@endsection
@endsection