@extends('layouts.app')

@section('content')

    <!-- BEGIN: Content-->
    <form method="POST" data-completionFunction = "disableHeader" class="ajax-input-form" action = "{{route('packingList.store')}}" data-redirect="{{$redirectUrl}}" id = "packing_list_form" enctype='multipart/form-data'>
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
				<div class="row">
                @include('layouts.partials.breadcrumb-add-edit', [
                    'title' => $moduleName,
                    'menu' => 'Home',
                    'menu_url' => url('home'),
                    'sub_menu' => 'Add New'
                ])
                    <input type = "hidden" value = "draft" name = "document_status" id = "document_status" />
					<div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
						<div class="form-group breadcrumb-right" id = "buttonsDiv">
                        @if(!isset(request() -> revisionNumber))
                        <button type = "button" onclick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
                            @if (isset($packingList))
                                @if($buttons['print'])
                                <!-- Print Button to be added !-->
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
                                            @if (isset($packingList) && isset($docStatusClass))
                                            <div class="col-md-6 text-sm-end">
                                                <span class="badge rounded-pill badge-light-secondary forminnerstatus">
                                                    Status : <span class="{{$docStatusClass}}">{{$packingList->display_status}}</span>
                                                </span>
                                            </div>

                                            @endif

                                            <div class="col-md-8 basic-information">
                                            @if (isset($packingList))
                                                <input type = "hidden" value = "{{$packingList -> id}}" name = "packing_list_id"></input>
                                            @endif

                                            <div class="row align-items-center mb-1 d-none">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Document Type <span class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <select class="form-select disable_on_edit" id = "service_id_input" {{isset($packingList) ? 'disabled' : ''}} onchange = "onSeriesChange(this);">
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
                                                            <select class="form-select disable_on_edit" onchange = "getDocNumberByBookId(this);" name = "book_id" id = "series_id_input">
                                                                @foreach ($series as $currentSeries)
                                                                    <option value = "{{$currentSeries -> id}}" {{isset($packingList) ? ($packingList -> book_id == $currentSeries -> id ? 'selected' : '') : ''}}>{{$currentSeries -> book_code}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <input type = "hidden" name = "book_code" id = "book_code_input" value = "{{isset($packingList) ? $packingList -> book_code : ''}}"></input>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Document No <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <input type="text" value = "{{isset($packingList) ? $packingList -> document_number : ''}}" class="form-control disable_on_edit" readonly id = "order_no_input" name = "document_no">
                                                        </div>
                                                     </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Document Date <span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <input type="date" value = "{{isset($packingList) ? $packingList -> document_date : Carbon\Carbon::now() -> format('Y-m-d')}}" class="form-control" name = "document_date" id = "order_date_input" oninput = "onDocDateChange();" min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" >
                                                        </div>
                                                     </div>

                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Location<span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <select class="form-select disable_on_edit" name = "store_id" id = "store_id_input" oninput = "getSubStores(this);">
                                                                @foreach ($stores as $store)
                                                                    <option display-address = "{{$store -> address ?-> display_address}}" value = "{{$store -> id}}" {{isset($packingList) ? ($packingList -> store_id == $store -> id ? 'selected' : '') : ''}}>{{$store -> store_name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Store<span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <select class="form-select disable_on_edit" name = "sub_store_id" id = "sub_store_id_input">

                                                            </select>
                                                        </div>
                                                    </div>

                                                        <div class="row align-items-center mb-1 can_hide d-none" id = "selection_section" style = "display:none;">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Reference From </label>
                                                            </div>

                                                            <div class="col-md-5 action-button">
                                                                <button onclick = "openPullableDocuments();" disabled type = "button" id = "select_qt_button" data-bs-toggle="modal" data-bs-target="#rescdule" class="btn btn-outline-primary btn-sm mb-0"><i data-feather="plus-square"></i> Sales Order</button>
                                                            </div>
                                                        </div>
                                            </div>
                            @if(isset($packingList) && ($packingList -> document_status !== "draft"))
                            @if((isset($approvalHistory) && count($approvalHistory) > 0) || isset($revision_number))
                           <div class="col-md-4">
                               <div class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
                                   <h5 class="mb-2 text-dark border-bottom pb-50 d-flex align-items-center justify-content-between">
                                       <strong><i data-feather="arrow-right-circle"></i> Approval History</strong>
                                       @if(!isset(request() -> revisionNumber) && $packingList -> document_status !== 'draft')
                                       <strong class="badge rounded-pill badge-light-secondary amendmentselect">Rev. No.
                                           <select class="form-select" id="revisionNumber">
                                            @for($i=$revision_number; $i >= 0; $i--)
                                               <option value="{{$i}}" {{request('revisionNumber',$packingList->revision_number) == $i ? 'selected' : ''}}>{{$i}}</option>
                                            @endfor
                                           </select>
                                       </strong>
                                       @else
                                       @if ($packingList -> document_status !== 'draft')
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
                            <div class="card">
								 <div class="card-body customernewsection-form">
                                            <div class="border-bottom mb-2 pb-25">
                                                     <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="newheader ">
                                                                <h4 class="card-title text-theme">Packet Detail</h4>
                                                                <p class="card-text">Fill the details</p>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6 text-sm-end" id = "add_delete_item_section">
                                                            <a href="#" onclick = "deleteItemRows();" class="btn btn-sm btn-outline-danger me-50">
                                                                <i data-feather="x-circle"></i> Delete</a>
                                                                <a id = "add_item_section" href="#" onclick = "addItemRow();" class="btn btn-sm btn-outline-primary">
                                                                <i data-feather="plus"></i> Add Packet</a>
                                                         </div>
                                                         </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                        <div class="table-responsive pomrnheadtffotsticky">
                                                                <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                                   <thead>
                                                                        <tr>
                                                                           <th class="customernewsection-form" width = "50px">
                                                                               <div class="form-check form-check-primary custom-checkbox">
                                                                               <input type="checkbox" class="form-check-input cannot_disable" id="select_all_items_checkbox" oninput = "checkOrRecheckAllItems(this);">
                                                                            <label class="form-check-label" for="select_all_items_checkbox" ></label>
                                                                               </div>
                                                                           </th>
                                                                           <th>Package No</th>
                                                                           <th>SO No.</th>
                                                                           <th width = "100px">SO Date</th>
                                                                           <th>Customer</th>
                                                                           <th style = "max-width:500px;">Items</th>
                                                                           <th class = "numeric-alignment" width = "150px">Total Quantity</th>
                                                                           <th width="50px">Action</th>
                                                                         </tr>
                                                                       </thead>
                                                                       <tbody class="mrntableselectexcel" id = "item_header">
                                                                       @if (isset($packingList))
                                                                           @foreach ($packingList -> details as $packingListDetailIndex => $packingListDetail)
                                                                               <tr id = "item_row_{{$packingListDetailIndex}}" class = "item_header_rows" data-id = "{{$packingListDetail -> id}}" data-index = "{{$packingListDetailIndex}}" onclick = "onItemClick({{$packingListDetailIndex}});">
                                                                               <input type = 'hidden' name = "pl_detail_id[]" value = "{{$packingListDetail -> id}}">
                                                                                <td class="customernewsection-form">
                                                                                <div class="form-check form-check-primary custom-checkbox">
                                                                                    <input type="checkbox" class="form-check-input item_row_checks cannot_disable" id="item_checkbox_{{$packingListDetailIndex}}" del-index = "{{$packingListDetailIndex}}">
                                                                                    <label class="form-check-label" for="item_checkbox_{{$packingListDetailIndex}}"></label>
                                                                                </div>
                                                                               </td>
                                                                               <td class="poprod-decpt">
                                                                                    <input type="text" id = "packet_name_input_{{$packingListDetailIndex}}" class="form-control mw-100" value = "{{$packingListDetail -> packing_number}}" disabled>
                                                                                    <input type="hidden" value = "{{$packingListDetail -> packing_number}}" name="items[{{$packingListDetailIndex}}][packet_name]" />
                                                                                </td>
                                                                                <td class="poprod-decpt">
                                                                                    <input type="text" id = "so_number_{{$packingListDetailIndex}}" class="form-control mw-100" value = "{{$packingListDetail -> sale_order ?-> display_document_number}}" disabled>
                                                                                    <input type = "hidden" name = "items[{{$packingListDetailIndex}}][sale_order_id]"  value = "{{$packingListDetail -> sale_order ?-> display_document_number}}" />
                                                                                </td>
                                                                                <td class="poprod-decpt">
                                                                                    <input type="text" id = "so_date_{{$packingListDetailIndex}}" class="form-control mw-100" value = "{{$packingListDetail -> sale_order ?-> document_date}}" disabled>
                                                                                </td>
                                                                                <td class="poprod-decpt">
                                                                                    <input type="text" id = "so_customer_{{$packingListDetailIndex}}" class="form-control mw-100" value = "{{$packingListDetail -> sale_order ?-> customer_code}}" disabled>
                                                                                </td>
                                                                                <td class="poprod-decpt" style = "cursor:pointer;">
                                                                                    {!! $packingListDetail -> items_ui !!}
                                                                                    <input id = "so_items_array_{{$packingListDetailIndex}}" type = "hidden" value = '{{json_encode($packingListDetail -> so_items_array)}}' name = "items[{{$packingListDetailIndex}}][so_items]" />
                                                                                </td>
                                                                                <td class="poprod-decpt">
                                                                                    <input type="text" id = "total_packing_qty_{{$packingListDetailIndex}}" class="form-control mw-100 text-end" value = "{{$packingListDetail -> total_item_qty}}" disabled>
                                                                                </td>
                                                                                <td>
                                                                                    <div class="d-flex">
                                                                                        <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick = "setItemRemarks('item_remarks_{{$packingListDetailIndex}}');">        <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span></div>
                                                                                    </div>
                                                                                </td>
                                                                                <input type="hidden" id = "item_remarks_{{$packingListDetailIndex}}" name = "items[{{$packingListDetailIndex}}][detail_remarks]" value = ""/>
                                                                             </tr>
                                                                           @endforeach
                                                                       @else
                                                                       @endif
                                                                    </tbody>

                                                                    <tfoot>

                                                                        <tr class="totalsubheadpodetail">
                                                                           <td colspan="6"></td>
                                                                           <td class="text-end" id = "all_items_total_qty">00.00</td>
                                                                           <td></td>
                                                                       </tr>

                                                                        <tr valign="top">
                                                                           <td colspan="12" rowspan="10">
                                                                               <table class="table border">
                                                                                   <tr>
                                                                                       <td class="p-0">
                                                                                           <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
                                                                                       </td>
                                                                                   </tr>

                                                                                   <tr id = "current_packet_description_row">
                                                                                       <td class="poprod-decpt">
                                                                                           <span style = "text-wrap:auto;" id = "current_packet_details"></span>
                                                                                        </td>
                                                                                   </tr>

                                                                                   <tr id = "current_item_description_row">
                                                                                       <td class="poprod-decpt">
                                                                                           <span class="badge rounded-pill badge-light-secondary"><strong>Remarks</strong>: <span style = "text-wrap:auto;" id = "current_item_description"></span></span>
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
                                                                       <textarea type="text" rows="4" class="form-control" placeholder="Enter Remarks here..." name = "final_remarks">{{isset($packingList) ? $packingList -> remarks : '' }}</textarea>
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

    <div class="modal fade text-start" id="rescdule" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1250px">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select Sales Order</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					 <div class="row">

                     <div class="col">
                            <div class="mb-1">
                            <label class="form-label">Customer Name</label>
                                <input type="text" id="customer_code_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "customer_id_qt_val"></input>
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Series</label>
                                <input type="text" id="book_code_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "book_id_qt_val"></input>
                            </div>
                        </div>


                         <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Quotation No.</label>
                                <input type="text" id="document_no_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "document_id_qt_val"></input>
                            </div>
                        </div>

                         <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Item Name</label>
                                <input type="text" id="item_name_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "item_id_qt_val"></input>
                            </div>
                        </div>

                         <div class="col  mb-1">
                              <label class="form-label">&nbsp;</label><br/>
                             <button onclick = "getDocuments();" type = "button" class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                         </div>

						 <div class="col-md-12">
							<div class="table-responsive" style="overflow-y: auto;max-height: 200px;">
								<table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
									<thead>
										 <tr>
											<th>
                                            <div class="form-check form-check-inline me-0">
                                                <input class="form-check-input" type="checkbox" id="check_all_docs"
                                                    oninput="checkAllDocs(this);">
                                            </div>
											</th>
											<th>Series</th>
											<th>Document No.</th>
											<th>Document Date</th>
                                            <th>Customer Currency</th>
                                            <th>Customer Name</th>
											<th>Item</th>
											<th>Attributes</th>
											<th>UOM</th>
											<th>Quantity</th>
											<th>Balance Qty</th>
											<th>Rate</th>
											<th>Avl Stock</th>
										  </tr>
										</thead>
										<tbody id = "qts_data_table">

									   </tbody>


								</table>
							</div>
						</div>


					 </div>
				</div>
				<div class="modal-footer text-end">
					<button type = "button" class="btn btn-outline-secondary btn-sm can_hide" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
					<button type = "button" class="btn btn-primary btn-sm can_hide" onclick = "processDocuments();" data-bs-dismiss="modal"><i data-feather="check-circle"></i> Process</button>
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

    <div class="modal fade" id="amendConfirmPopup" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
   <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
         <div class="modal-header">
            <div>
               <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Amend
                {{$moduleName}}
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
        <form class="ajax-submit-2" method="POST" action="{{ route('document.approval.packingList') }}" data-redirect="javascript: history.go(-1)" enctype='multipart/form-data'>
          @csrf
          <input type="hidden" name="action_type" id="action_type">
          <input type="hidden" name="id" value="{{isset($packingList) ? $packingList -> id : ''}}">
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

<div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
  <div class="modal-dialog">
      <div class="modal-content">
          <div class="modal-header p-0 bg-transparent">
              <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body alertmsg text-center warning">
              <i data-feather='alert-circle'></i>
              <h2>Are you sure?</h2>
              <p>Are you sure you want to <strong>Amend</strong> this <strong>{{$moduleName}}</strong>?</p>
              <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
              <button type="button" data-bs-dismiss="modal" onclick = "amendConfirm();" class="btn btn-primary">Confirm</button>
          </div>
      </div>
  </div>
</div>

<div class="modal fade text-start" id="packetsUI" tabindex="-1" aria-labelledby="packetsUIModal" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1250px">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="packetsUIModal">Add Packet</h4>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<div class="row">

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">SO No.</label>
                                <input type="text" id="selected_so" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input id = "selected_so_id" type = "hidden">
                                </input>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">SO Date</label>
                                <input type = "text" id = "selected_so_date" class = "form-control" disabled>
                                </input>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Customer</label>
                                <input type = "text" id = "selected_so_customer" class = "form-control" disabled>
                                </input>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Items</label>
                                <input type="text" id="selected_so_item" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="" readonly>
                                <input id = "selected_so_item_id" type = "hidden">
                                </input>
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Package No.</label>
                                <input type="text" id="new_packet_name" placeholder="Package No." class="form-control mw-100" value="">
                            </div>
                        </div>


                    </div>

                    <div class = "row">
						 <div class="col-md-12">
							<div class="table-responsive" style="overflow-y: auto;max-height: 200px;">
								<table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
									<thead>
										 <tr>
											<th>
                                            <div class="form-check form-check-inline me-0">
                                                <input class="form-check-input" type="checkbox" id="check_all_items"
                                                    oninput="checkAllItems(this);">
                                            </div>
											</th>
											<th>Item Code</th>
											<th>Item Name</th>
											<th>Attributes</th>
											<th>UOM</th>
                                            <th class = "text-end">Order Qty</th>
                                            <th class = "text-end">Balance Qty</th>
                                            <th class = "text-end">Avl Stock</th>
                                            <th class = "text-end">Packed Qty</th>
										  </tr>
										</thead>
										<tbody id = "items_selection_table">

									   </tbody>
								</table>
							</div>
						</div>
					 </div>
				</div>
				<div class="modal-footer text-end">
					<button type = "button" class="btn btn-primary btn-sm can_hide" onclick = "processSelectedItems()"><i data-feather="check-circle"></i> Submit</button>
				</div>
			</div>
		</div>
	</div>
@section('scripts')

<script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
<script type="text/javascript" src="{{asset('app-assets/js/scripts/sales/common.js')}}"></script>
<script>

    let pulledSoArray = [];
    let currentSelectionItemsArray = [];
    let currentPacketIndex = 0;

        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        });

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

        function resetAddPacketModal()
        {
            $("#selected_so").val("");
            $("#selected_so_id").val("");

            $("#selected_so_date").val("");

            $("#selected_so_customer").val("");

            $("#selected_so_item").val("");
            $("#selected_so_item_id").val("");

            $("#new_packet_name").val("");

            $("#items_selection_table").html('');

            document.getElementById('check_all_items').checked = false;
        }

        function addItemRow()
        {
            initializeAutoCompleteSo();
            const tableElementBody = document.getElementById('item_header');
            const previousElements = document.getElementsByClassName('item_header_rows');
            const newIndex = previousElements.length ? previousElements.length : 0;
            resetAddPacketModal();
            openModal('packetsUI');
            currentPacketIndex += newIndex;
        }

        function deleteItemRows()
        {

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
        function renderIcons()
        {
            feather.replace()
        }
        function closeModal(id)
        {
            $('#' + id).modal('hide');
        }
        function openModal(id)
        {
            $('#' + id).modal('show');
        }

        function submitForm(status) {
            // Create FormData object
            enableHeader();
        }

    function disableHeader()
    {
        const itemInputs = document.getElementsByClassName('comp_item_code');
        let itemsPresent = false;
        if (itemInputs.length > 0) {
            itemsPresent = true;
        }

        const disabledFields = document.getElementsByClassName('disable_on_edit');
            for (let disabledIndex = 0; disabledIndex < disabledFields.length; disabledIndex++) {
                if (disabledFields[disabledIndex].value && itemsPresent) {
                    disabledFields[disabledIndex].disabled = true;
                } else {
                    disabledFields[disabledIndex].disabled = false;

                }
            }
        const selectButton = document.getElementById('select_qt_button');
        if (selectButton && itemsPresent) {
            selectButton.disabled = true;
        }
    }



    function enableHeader()
    {
        const disabledFields = document.getElementsByClassName('disable_on_edit');
        for (let disabledIndex = 0; disabledIndex < disabledFields.length; disabledIndex++) {
            disabledFields[disabledIndex].disabled = false;
        }
        const selectButton = document.getElementById('select_qt_button');
        if (selectButton) {
            selectButton.disabled = false;
        }
    }

    //Function to set values for edit form
    function editScript()
    {
        localStorage.setItem('deletedPlistItemIds', JSON.stringify([]));
        localStorage.setItem('deletedAttachmentIds', JSON.stringify([]));

        const order = @json(isset($packingList) ? $packingList : null);
        if (order) {
            //Disable header fields which cannot be changed
            disableHeader();
            //Set all documents
            order.media_files.forEach((mediaFile, mediaIndex) => {
                appendFilePreviews(mediaFile.file_url, 'main_order_file_preview', mediaIndex, mediaFile.id, order.document_status == 'draft' ? false : true);
            });
            let totalItemQty = 0;
            order.details.forEach(orderDetails => {
                totalItemQty += Number(orderDetails.total_item_qty);
            });
            $("#all_items_total_qty").text(totalItemQty);
        }


        renderIcons();

        let finalAmendSubmitButton = document.getElementById("amend-submit-button");

        viewModeScript(finalAmendSubmitButton ? false : true);
    }

    function reCheckEditScript()
    {
        const currentOrder = @json(isset($packingList) ? $packingList : null);
        if (currentOrder) {
            currentOrder.items.forEach((item, index) => {
                document.getElementById('item_checkbox_' + index).disabled = item.is_editable ? false : true;
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const order = @json(isset($packingList) ? $packingList : null);
        onSeriesChange(document.getElementById('service_id_input'), order ? false : true);
        getSubStores(document.getElementById('store_id_input'));
    });

    function resetParametersDependentElements(reset = true)
    {
        var selectionSection = document.getElementById('selection_section');
        if (selectionSection) {
            selectionSection.style.display = "none";
        }
        // document.getElementById('add_item_section').style.display = "none";
        $("#order_date_input").attr('max', "<?php echo date('Y-m-d'); ?>");
        $("#order_date_input").attr('min', "<?php echo date('Y-m-d'); ?>");
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
                //   if (reset) {
                //       implementBookDynamicFields(data.data.dynamic_fields_html, data.data.dynamic_fields);
                //   }
                }
                if(data.status == 404) {
                    if (reset) {
                        $("#book_code_input").val("");
                        alert(data.message);
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

    function implementBookParameters(paramData)
    {
        var selectedRefFromServiceOption = paramData.reference_from_service;
        var selectedBackDateOption = paramData.back_date_allowed;
        var selectedFutureDateOption = paramData.future_date_allowed;
        //Reference From
        if (selectedRefFromServiceOption) {
            var selectVal = selectedRefFromServiceOption;
            if (selectVal && selectVal.length > 0) {
                selectVal.forEach(selectSingleVal => {
                    if (selectSingleVal == 'so') {
                        var selectionSectionElement = document.getElementById('selection_section');
                        if (selectionSectionElement) {
                            selectionSectionElement.style.display = "";
                        }
                    }
                    // if (selectSingleVal == 'd') {
                    //     document.getElementById('add_item_section').style.display = "";
                    //     document.getElementById('copy_item_section').style.display = "";
                    // }
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
            $("#order_date_input").removeAttr('max');
            $("#order_date_input").removeAttr('min');
            $("#order_date_input").off('input');
        }
        if (backDateAllow && !futureDateAllow) { // Allow only back date
            $("#order_date_input").removeAttr('min');
            $("#order_date_input").attr('max', "<?php echo date('Y-m-d'); ?>");
            $("#order_date_input").off('input');
            $('#order_date_input').on('input', function() {
                restrictFutureDates(this);
            });
        }
        if (!backDateAllow && futureDateAllow) { // Allow only future date
            $("#order_date_input").removeAttr('max');
            $("#order_date_input").attr('min', "<?php echo date('Y-m-d'); ?>");
            $("#order_date_input").off('input');
            $('#order_date_input').on('input', function() {
                restrictPastDates(this);
            });
        }
    }

    function enableDisableQtButton()
    {
        const bookId = document.getElementById('series_id_input').value;
        const bookCode = document.getElementById('book_code_input').value;
        const documentDate = document.getElementById('order_date_input').value;
        const storeId = document.getElementById('store_id_input').value;
        const subStoreId = document.getElementById('sub_store_id_input').value;

        if (bookId && bookCode && documentDate && storeId && subStoreId) {
            const selectButton = document.getElementById('select_qt_button');
            if (selectButton) {
                selectButton.disabled = false;
            };
        } else {
            const selectButton = document.getElementById('select_qt_button');
            if (selectButton) {
                selectButton.disabled = true;
            };
        }
    }

    editScript();

    function checkItemAddValidation()
    {
        let addRow = $('#series_id_input').val &&  $('#order_no_input').val && $('#order_date_input').val && $('#store_id_input').val;
        return addRow;
    }

    function setApproval()
    {
        document.getElementById('action_type').value = "approve";
        document.getElementById('approve_reject_heading_label').textContent = "Approve " + "{{request() -> type === 'sq' ? 'Sales Quotation' : 'Sales Order'}}";

    }
    function setReject()
    {
        document.getElementById('action_type').value = "reject";
        document.getElementById('approve_reject_heading_label').textContent = "Reject " + "{{request() -> type === 'sq' ? 'Sales Quotation' : 'Sales Order'}}";
    }
    function setFormattedNumericValue(element)
    {
        element.value = (parseFloat(element.value ? element.value  : 0)).toFixed(2)
    }
    function processDocuments()
    {
        let allSoDocs = document.getElementsByClassName('pull_checkbox');
        pulledSoArray = [];
        for (let index = 0; index < allSoDocs.length; index++) {
            if (allSoDocs[index].checked) {
                pulledSoArray.push({
                    value : allSoDocs[index].getAttribute('header-id'),
                    label : allSoDocs[index].getAttribute('label')
                });
            }
        }
        addItemRow();
    }
    function getDocuments()
    {
        var qtsHTML = ``;
        const header_book_id = $("#series_id_input").val();
        const targetTable = document.getElementById('qts_data_table');
        const customer_id = $("#customer_id_qt_val").val();
        const book_id = $("#book_id_qt_val").val();
        const document_id = $("#document_id_qt_val").val();
        const item_id = $("#item_id_qt_val").val();
        $.ajax({
            url: "{{route('packingList.pullable.docs')}}",
            method: 'GET',
            dataType: 'json',
            data : {
                customer_id : customer_id,
                book_id : book_id,
                document_id : document_id,
                item_id : item_id,
                header_book_id : header_book_id,
                store_id : $("#store_id_input").val(),
                sub_store_id : $("#sub_store_id_input").val(),
            },
            success: function(data) {
                if (Array.isArray(data.data) && data.data.length > 0) {
                    data.data.forEach((qt, qtIndex) => {
                        var attributesHTML = ``;
                            qt.attributes.forEach(attribute => {
                                attributesHTML += `<span class="badge rounded-pill badge-light-primary" > ${attribute.attribute_name} : ${attribute.attribute_value} </span>`;
                            });
                        let label = qt?.header?.book_code + "-" + qt?.header?.document_number;
                        qtsHTML += `
                            <tr>
                                <td>
                                    <div class="form-check form-check-inline me-0">
                                        <input ${qt?.avl_stock > 0 ? '' : 'disabled'} class="form-check-input pull_checkbox" type="checkbox" id="po_checkbox_${qtIndex}" oninput = "checkQuotation(this);" document-id = "${qt.id}" doc-id = "${qt.id}" current-doc-id = "0" so-item-id = "${qt.id}" header-id = "${qt.header.id}" label = "${label}">
                                    </div>
                                </td>
                                <td>${qt?.header?.book_code}</td>
                                <td>${qt?.header?.document_number}</td>
                                <td>${moment(qt?.header?.document_date).format('D/M/Y')}</td>
                                <td>${qt?.header?.currency_code}</td>
                                <td>${qt?.header?.customer?.company_name}</td>
                                <td>${qt?.item_code}</td>
                                <td>${attributesHTML}</td>
                                <td>${qt?.uom?.name}</td>
                                <td>${qt?.order_qty}</td>
                                <td>${qt?.quotation_balance_qty}</td>
                                <td>${qt?.rate}</td>
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
                                    label: `${item[labelKey1]} ${labelKey2 ? (item[labelKey2] ? '(' + item[labelKey2] + ')' : '') : ''}`,
                                    code: item[labelKey1] || '',
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });
                },
                appendTo: "#rescdule",
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

    function openPullableDocuments()
    {
        initializeAutocompleteQt("customer_code_input_qt", "customer_id_qt_val", "customer", "customer_code", "company_name");
        initializeAutocompleteQt("book_code_input_qt", "book_id_qt_val", "book_sq", "book_code", "");
        initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "sale_order_document_qt", "document_number", "");
        initializeAutocompleteQt("item_name_input_qt", "item_id_qt_val", "sale_module_items", "item_code", "item_name");
        getDocuments();
    }

    let current_doc_id = 0;

    function checkQuotation(element)
    {
        // const docId = element.getAttribute('doc-id');
        // if (current_doc_id != 0) {
        //     if (element.checked == true) {
        //         if (current_doc_id != docId) {
        //             element.checked = false;
        //         }
        //     } else {
        //         const otherElementsSameDoc = document.getElementsByClassName('po_checkbox');
        //         let resetFlag = true;
        //         for (let index = 0; index < otherElementsSameDoc.length; index++) {
        //             if (otherElementsSameDoc[index].getAttribute('doc-id') == current_doc_id && otherElementsSameDoc[index].checked) {
        //                 resetFlag = false;
        //                 break;
        //             }
        //         }
        //         if (resetFlag) {
        //             current_doc_id = 0;
        //         }
        //     }
        // } else {
        //     current_doc_id = element.getAttribute('doc-id');
        // }
        return true;
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

var currentRevNo = $("#revisionNumber").val();

// # Revision Number On Change
$(document).on('change', '#revisionNumber', (e) => {
    e.preventDefault();
    let actionUrl = location.pathname + '?type=' + "{{request() -> type ?? 'so'}}" + '&revisionNumber=' + e.target.value;
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
                // let errors = res.errors;
                // for (const [key, errorMessages] of Object.entries(errors)) {
                //     var name = key.replace(/\./g, "][").replace(/\]$/, "");
                //     formObj.find(`[name="${name}"]`).parent().append(
                //         `<span class="ajax-validation-error-span form-label text-danger" style="font-size:12px">${errorMessages[0]}</span>`
                //     );
                // }

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
    const currentOrder = @json(isset($packingList) ? $packingList : null);
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
    $("#sale_order_form").submit();
}

const maxNumericLimit = 9999999;
let isProgrammaticChange = false; // Flag to prevent recursion

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

    function checkOrRecheckAllItems(element)
    {
        const allRowsCheck = document.getElementsByClassName('item_row_checks');
        const checkedStatus = element.checked;
        for (let index = 0; index < allRowsCheck.length; index++) {
            allRowsCheck[index].checked = checkedStatus;
        }
    }

    function resetSeries()
    {
        document.getElementById('series_id_input').innerHTML = '';
    }

    function onSeriesChange(element, reset = true)
    {
        resetSeries();
        $.ajax({
            url: "{{route('book.service-series.get')}}",
            method: 'GET',
            dataType: 'json',
            data: {
                menu_alias: "{{request() -> segments()[0]}}",
                service_alias: element.value,
                book_id : reset ? null : "{{isset($packingList) ? $packingList -> book_id : null}}"
            },
            success: function(data) {
                if (data.status == 'success') {
                    let newSeriesHTML = ``;
                    data.data.forEach((book, bookIndex) => {
                        newSeriesHTML += `<option value = "${book.id}" ${bookIndex == 0 ? 'selected' : ''} >${book.book_code}</option>`;
                    });
                    document.getElementById('series_id_input').innerHTML = newSeriesHTML;
                    document.getElementById('series_id_input').value = (data.data[0]?.id);
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
        const orderId = "{{isset($packingList) ? $packingList -> id : null}}";
        if (orderId) {
            $.ajax({
            url: "{{route('packingList.revoke')}}",
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
                    window.location.href = "{{route('packingList.index')}}";
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
    function checkAllDocs(element)
    {
        const selectableElements = document.getElementsByClassName('pull_checkbox');
        for (let index = 0; index < selectableElements.length; index++) {
            if (!selectableElements[index].disabled) {
                selectableElements[index].checked = element.checked;
            }
        }
    }
    function checkAllItems(element)
    {
        const selectableElements = document.getElementsByClassName('selection_items');
        for (let index = 0; index < selectableElements.length; index++) {
            if (!selectableElements[index].disabled) {
                selectableElements[index].checked = element.checked;
            }
            $("#" + selectableElements[index].id).trigger('input');
        }
    }
    function getSubStores(element)
    {
        const storeId = element.value;
        const subStoreElement = document.getElementById('sub_store_id_input');
        const order = @json(isset($packingList) ? $packingList : null);
        let newSubStoreHTML = ``;
        $.ajax({
            url: "{{route('subStore.get.from.stores')}}",
            method: 'GET',
            dataType: 'json',
            data: {
                store_id : storeId,
                types : ['Stock']
            },
            success: function(data) {
                if (data.status === 200) {
                    data.data.forEach(subStore => {
                        if (order && order.sub_store_id == subStore.id) {
                            newSubStoreHTML += `<option selected value = '${subStore.id}'>${subStore.name}</option>`
                        } else {
                            newSubStoreHTML += `<option value = '${subStore.id}'>${subStore.name}</option>`
                        }
                    });
                    subStoreElement.innerHTML = newSubStoreHTML;
                } else {
                    subStoreElement.innerHTML = ``;
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                    });
                }
            },
            error: function(xhr) {
                subStoreElement.innerHTML = ``;
                Swal.fire({
                    title: 'Error!',
                    text: xhr?.responseJSON?.message,
                    icon: 'error',
                });
            }
        });
    }

    function onDocSelection(element)
    {
        const selectedHeaderId = element.value;
        const selectionItemsTable = document.getElementById('items_selection_table');
        selectionItemsTable.innerHTML = ``;
        $.ajax({
            url: "{{route('packingList.pullable.doc.items')}}",
            method: 'POST',
            header : {
                'X-CSRF-TOKEN': getCsrfToken()
            },
            dataType: 'json',
            data: {
                header_id : selectedHeaderId,
                store_id : $("#store_id_input").val(),
                sub_store_id : $("#sub_store_id_input").val(),
                selection_array : JSON.stringify(currentSelectionItemsArray)
            },
            success: function(data) {
                if (data.status === 200) {
                    let newItemsHTML = ``;
                    let attributesArray = [];
                    data.data.forEach(item => {
                        var attributesHTML = ``;
                        item.item_attributes.forEach(attribute => {
                            attributesHTML += `<span class="badge rounded-pill badge-light-primary" > ${attribute.attribute_name} : ${attribute.attribute_value} </span>`;
                            attributesArray.push({
                                label : attribute.attribute_name,
                                value : attribute.attribute_value
                            });
                        });
                        newItemsHTML += `
                        <tr class = "selection_items_row">
                        <td>
                        <div class="form-check form-check-inline me-0 ${item.avl_stock > 0 ? '' : 'disable_pointer'}" ${item.avl_stock > 0 ? "" : "data-bs-toggle='tooltip' title='Stock Not Available'"}>
                        <input so_item_id = "${item.id}" ${item.avl_stock > 0 ? "" : "disabled"}  class="form-check-input selection_items" type="checkbox" id="selection_item_${item.id}" oninput = "triggerSelectionItemQty(this, ${item.id}, ${item.avl_packing_qty});">
                        </div>
                        </td>
                        <td id = "selection_item_code_${item.id}">${item.item_code}</td>
                        <td id = "selection_item_name_${item.id}">${item.item_name}</td>
                        <td>
                        ${attributesHTML}
                        <input type = "hidden" value = '${JSON.stringify(attributesArray)}' id = "selection_item_attributes_${item.id}"/>
                        </td>
                        <td>${item.uom_code}</td>
                        <td class = "text-end">${item.order_qty}</td>
                        <td class = "text-end">${item.avl_packing_qty}</td>
                        <td class = "text-end">${item.avl_stock}</td>
                        <td class = "text-end">
                        <input so_item_id = "${item.id}" type = "text" class = "form-control mw-100 text-end total_qties" id = "selection_item_qty_${item.id}" value = "${item.avl_packing_qty}" max = "${item.avl_packing_qty}" disabled oninput = "changeSelectedItemQty(this);" />
                        </td>
                        </tr>
                        `;
                    });
                    selectionItemsTable.innerHTML = newItemsHTML;
                } else {
                    selectionItemsTable.innerHTML = ``;
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                    });
                }
            },
            error: function(xhr) {
                selectionItemsTable.innerHTML = ``;
                Swal.fire({
                    title: 'Error!',
                    text: xhr?.responseJSON?.message,
                    icon: 'error',
                });
            }
        });
    }

    function processSelectedItems()
    {
        let selectedSo = document.getElementById('selected_so').value;
        let selectedSoId = document.getElementById('selected_so_id').value;
        let packetName = document.getElementById('new_packet_name').value;
        let selectedSoDate = document.getElementById('selected_so_date').value;
        let selectedSoCust = document.getElementById('selected_so_customer').value;
        if (selectedSo && selectedSoId && packetName) {
            let selectedSoItems = [];
            let selectedTotalQty = 0;
            let soItems = document.getElementsByClassName('selection_items');
            for (let index = 0; index < soItems.length; index++) {
                let currSoId = soItems[index].getAttribute('so_item_id');
                if (soItems[index].checked) {
                    selectedSoItems.push({
                        'item_id' : currSoId,
                        'qty' : $("#selection_item_qty_" + currSoId).val(),
                        'item_code' : $("#selection_item_code_" + currSoId).text(),
                        'item_name' : $("#selection_item_name_" + currSoId).text(),
                        'attributes' : JSON.parse($("#selection_item_attributes_" + currSoId).val())
                    });
                    selectedTotalQty += parseFloat($("#selection_item_qty_" + currSoId).val());
                }
            }
            if (selectedSoItems.length > 0 && selectedTotalQty > 0) {
                addPacket(packetName, selectedSo, selectedSoId, selectedSoDate, selectedSoCust, selectedSoItems, selectedTotalQty);
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select atleast one item',
                    icon: 'error',
                });
            }
        } else {
            Swal.fire({
                title: 'Error!',
                text: 'Please select order items with packet name',
                icon: 'error',
            });
        }
    }

    function addPacket(packetName, saleOrderNo, saleOrderNoId, saleOrderDate, saleOrderCustomer, selectedSoItems, totalQty)
    {
        const tableElementBody = document.getElementById('item_header');
        const previousElements = document.getElementsByClassName('item_header_rows');
        const newIndex = previousElements.length ? previousElements.length : 0;
        const newItemRow = document.createElement('tr');
        newItemRow.className = 'item_header_rows';
        newItemRow.id = "item_row_" + newIndex;
        newItemRow.setAttribute('data-index', newIndex);
        newItemRow.onclick = function () {
            onItemClick(newIndex);
        };
        let itemsHTML = ``;
        let extraItemsCount = 0;
        selectedSoItems.forEach((soItem, soIndex) => {
            if (soIndex == 0) {
                let totalChar = Number(soItem.item_name.length);
                let attributes = soItem.attributes;
                let attributesHTML = ``;
                let maxChar = 70;
                attributes.forEach(attr => {
                    totalChar += (Number(attr.label.length) + Number(attr.value.length));
                    if (totalChar <= maxChar) {
                        attributesHTML += `<span class="badge rounded-pill badge-light-primary" > ${attr.label} : ${attr.value}</span>`;
                    } else {
                        attributesHTML += `..`;
                    }
                });

                itemsHTML += `<span class="badge rounded-pill badge-light-primary" > ${soItem.item_name}</span> ${attributesHTML}`;
            } else {
                extraItemsCount += 1;
            }
        });
        if (extraItemsCount > 0) {
            itemsHTML += `<span class="badge rounded-pill badge-light-secondary" > + ${extraItemsCount}</span>`
        }
        newItemRow.innerHTML = `
        <td class="customernewsection-form">
            <div class="form-check form-check-primary custom-checkbox">
                <input type="checkbox" class="form-check-input item_row_checks cannot_disable" id="item_checkbox_${newIndex}" del-index = "${newIndex}">
                <label class="form-check-label" for="item_checkbox_${newIndex}"></label>
            </div>
        </td>
        <td class="poprod-decpt">
            <input type="text" id = "packet_name_input_${newIndex}" class="form-control mw-100" value = "${packetName}" disabled>
            <input type="hidden" value = "${packetName}" name="items[${newIndex}][packet_name]" />
        </td>
        <td class="poprod-decpt">
            <input type="text" id = "so_number_${newIndex}" class="form-control mw-100" value = "${saleOrderNo}" disabled>
            <input type = "hidden" name = "items[${newIndex}][sale_order_id]" value = "${saleOrderNoId}" />
        </td>
        <td class="poprod-decpt">
            <input type="text" id = "so_date_${newIndex}" class="form-control mw-100" value = "${saleOrderDate}" disabled>
        </td>
        <td class="poprod-decpt">
            <input type="text" id = "so_customer_${newIndex}" class="form-control mw-100" value = "${saleOrderCustomer}" disabled>
        </td>
        <td class="poprod-decpt" style = "cursor:pointer;">
            ${itemsHTML}
            <input id = "so_items_array_${newIndex}" type = "hidden" value = '${JSON.stringify(selectedSoItems)}' name = "items[${newIndex}][so_items]" />
        </td>
        <td class="poprod-decpt">
            <input type="text" id = "total_packing_qty_${newIndex}" class="form-control mw-100 text-end" value = "${totalQty}" disabled>
        </td>
        <td>
            <div class="d-flex">
                <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick = "setItemRemarks('item_remarks_${newIndex}');">        <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span></div>
            </div>
        </td>
        <input type="hidden" id = "item_remarks_${newIndex}" name = "items[${newIndex}][detail_remarks]" value = ""/>
        `;
        tableElementBody.appendChild(newItemRow);
        renderIcons();
        closeModal("packetsUI");
        syncTotalQuantity();
    }

    function syncTotalQuantity()
    {
        let totalQties = document.getElementsByClassName('total_qties');
        let totalQty = 0;
        for (let index = 0; index < totalQties.length; index++) {
            if (totalQties[index].disabled) {
                continue;
            }
            totalQty += Number(totalQties[index].value);
        }
        $("#all_items_total_qty").text(totalQty);
    }

    function triggerSelectionItemQty(element, id, avlPackingQty = 0)
    {
        let selectionQtyElement = document.getElementById('selection_item_qty_' + id);
        let currentSelectionItemsArrayCopy = currentSelectionItemsArray;
        if (selectionQtyElement) {
            selectionQtyElement.value = avlPackingQty;
            if (element.checked) {
                selectionQtyElement.removeAttribute('disabled');
                //Add the element in the array or update if it does not exists
                const existingItem = currentSelectionItemsArrayCopy.find(item => item.so_item_id == id);
                if (existingItem) {
                    // Update qty if item exists
                    existingItem.qty = avlPackingQty;
                } else {
                    // Add new item if it doesn't exist
                    currentSelectionItemsArrayCopy.push({
                        so_item_id: id,
                        qty: Number(avlPackingQty),
                        index: currentPacketIndex
                    });
                }
            } else {
                //Remove the element from array if it exists
                currentSelectionItemsArrayCopy = currentSelectionItemsArrayCopy.filter(
                    item => item.so_item_id !== id
                );
                selectionQtyElement.disabled = true;
            }
        }
        currentSelectionItemsArray = currentSelectionItemsArrayCopy;
        console.log(currentSelectionItemsArray, "ITEMS ARRAY");
    }

    function initializeAutoCompleteSo()
    {
        $("#selected_so").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '/search',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        q: request.term,
                        type: 'packing_list_so',
                        header_book_id : $("#series_id_input").val()
                    },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                id: item.id,
                                label: `${item.book_code} - ${item.document_number}`,
                                customer : `${item.customer_code}`,
                                date : `${item.document_date}`
                            };
                        }));
                    },
                    error: function(xhr) {
                        console.error('Error fetching customer data:', xhr.responseText);
                    }
                });
            },
            appendTo: "#packetsUI",
            minLength: 0,
            select: function(event, ui) {
                var $input = $(this);
                $input.val(ui.item.label);
                $("#selected_so_id").val(ui.item.id);
                $("#selected_so_customer").val(ui.item.customer);
                $("#selected_so_date").val(ui.item.date);
                onDocSelection(document.getElementById('selected_so_id'));
                initializeAutoCompleteSoItems();
                return false;
            },
            change: function(event, ui) {
                if (!ui.item) {
                    $(this).val("");
                    $("#selected_so_id").val("");
                    $("#selected_so_customer").val("");
                $("#selected_so_date").val("");
                    deInitializeAutoCompleteSoItem();
                }
            }
        }).focus(function() {
            if (this.value === "") {
                $(this).autocomplete("search", "");
            }
        });
    }

    function initializeAutoCompleteSoItems()
    {
        $("#selected_so_item").removeAttr('readonly');
        $("#selected_so_item").autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: '/search',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        q: request.term,
                        type: 'packing_list_so_items',
                        header_book_id : $("#series_id_input").val(),
                        sale_order_id : $("#selected_so_id").val()
                    },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                id: item.id,
                                label: `${item.item_code} - ${item.item_name}`,
                            };
                        }));
                    },
                    error: function(xhr) {
                        console.error('Error fetching customer data:', xhr.responseText);
                    }
                });
            },
            appendTo: "#packetsUI",
            minLength: 0,
            select: function(event, ui) {
                var $input = $(this);
                $input.val(ui.item.label);
                $("#selected_so_item_id").val(ui.item.id);
                return false;
            },
            change: function(event, ui) {
                if (!ui.item) {
                    $(this).val("");
                    $("#selected_so_id").val("");
                }
            }
        }).focus(function() {
            if (this.value === "") {
                $(this).autocomplete("search", "");
            }
        });
    }

    function deInitializeAutoCompleteSo()
    {

    }

    function deInitializeAutoCompleteSoItem()
    {
        $("#selected_so_item_id").val("");
        $("#selected_so_item").attr("readonly", true);
    }

    function changeSelectedItemQty(element)
    {
        let numericValue = parseFloat(element.value);
        if (element.getAttribute('max')) {
            if (numericValue > parseFloat(element.getAttribute('max'))) {
                element.value = element.getAttribute('max');
                Swal.fire({
                    title: 'Error!',
                    text: 'Quantity cannot excced available qty',
                    icon: 'error',
                });
            }
        }
        if (numericValue <= 0) {
            element.value = element.getAttribute('max');
            Swal.fire({
                title: 'Error!',
                text: 'Quantity cannot be 0 or less',
                icon: 'error',
            });
        }
        let currentValue = element.value;
        let soItemId = element.getAttribute('so_item_id');
        let currentSelectionItemsArrayCopy = currentSelectionItemsArray;
        currentSelectionItemsArrayCopy.forEach(element => {
            if (element.so_item_id == soItemId && element.index == currentPacketIndex) {
                element.qty = Number(currentValue);
            }
        });
        currentSelectionItemsArray = currentSelectionItemsArrayCopy;
    }

    function onItemClick(index)
    {
        let soItemsArrayDoc = document.getElementById('so_items_array_' + index);
        let soItemsArray = JSON.parse(soItemsArrayDoc.value);
        let itemDetailsUI = ``;
        soItemsArray.forEach(soItem => {
            itemDetailsUI += `<span class="badge rounded-pill badge-light-primary"> ${soItem.item_name}</span>`;
            soItem.attributes.forEach(soAttr => {
                itemDetailsUI += `<span class="badge rounded-pill badge-light-primary"> ${soAttr.label} : ${soAttr.value}</span>`
            });
            itemDetailsUI += `<br/>`;
        });
        document.getElementById('current_packet_details').innerHTML = itemDetailsUI;
        document.getElementById('current_item_description').innerHTML = document.getElementById('item_remarks_'+index).value;
    }


    </script>
@endsection
@endsection
