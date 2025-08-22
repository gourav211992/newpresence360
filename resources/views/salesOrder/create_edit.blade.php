@extends('layouts.app')

@section('content')

    <!-- BEGIN: Content-->
    <form method="POST" data-completionFunction = "disableHeader" class="ajax-input-form sales_module_form sales_order" action = "{{route('sale.order.store')}}" data-redirect="{{$redirectUrl}}" id = "sale_order_form" enctype='multipart/form-data'>
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
				<div class="row">
                @include('layouts.partials.breadcrumb-add-edit', [
                    'title' => request() -> type == "so" ? 'Order' : 'Quotation',
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
                                @if($order->document_status == \App\Helpers\ConstantHelper::APPROVED || $order->document_status == \App\Helpers\ConstantHelper::APPROVAL_NOT_REQUIRED)
                                <button href="{{route('sale.order.generate-pdf', $order->id)}}" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer">
                                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                        <rect x="6" y="14" width="12" height="8"></rect>
                                    </svg>
                                    Print
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('sale.order.generate-pdf', ['id' => $order -> id, 'type' => 'default']) }}" target="_blank"> Sales Order </a>
                                            <a class="dropdown-item" href="{{ route('sale.order.generate-pdf', ['id' => $order -> id, 'type' => 'grouped']) }}" target="_blank"> Grouped by Attributes </a>
                                        </li>
                                </ul>
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
                                            @if (isset($order) && isset($docStatusClass))
                                            <div class="col-md-6 text-sm-end">
                                                <span class="badge rounded-pill badge-light-secondary forminnerstatus">
                                                    Status : <span class="{{$docStatusClass}}">{{$order->display_status}}</span>
                                                </span>
                                            </div>

                                            @endif

                                            <div class="col-md-8 basic-information">
                                            @if (isset($order))
                                                <input type = "hidden" value = "{{$order -> id}}" name = "sale_order_id"></input>
                                            @endif

                                            <input type = "hidden" value = "{{$type}}" name = "type"></input>

                                            <div class="row align-items-center mb-1" style = "display:none;">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Document Type <span class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <select class="form-select disable_on_edit" id = "service_id_input" {{isset($order) ? 'disabled' : ''}} onchange = "onSeriesChange(this);">
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
                                                            <input type="date" value = "{{isset($order) ? $order -> document_date : Carbon\Carbon::now() -> format('Y-m-d')}}" class="form-control" name = "document_date" id = "order_date_input" oninput = "onDocDateChange();" min="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>" >
                                                        </div>
                                                     </div>

                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Type <span class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <select class="form-select disable_on_edit" id = "order_type_input" name = "sale_order_type">
                                                                @foreach ($orderTypes as $orderType)
                                                                    <option value = "{{$orderType}}" {{isset($order) ? ($order -> order_type == $orderType ? 'selected' : '') : ''}}>{{$orderType}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                     <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Location<span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <select class="form-select disable_on_edit" name = "store_id" id = "store_id_input">
                                                                @foreach ($stores as $store)
                                                                    <option display-address = "{{$store -> address ?-> display_address}}" value = "{{$store -> id}}" {{isset($order) ? ($order -> store_id == $store -> id ? 'selected' : '') : ''}}>{{$store -> store_name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <!-- <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Department<span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <select class="form-select" name = "department_id" id = "department_id_input">
                                                                @foreach ($departments as $department)
                                                                    <option value = "{{$department -> id}}" {{isset($order) ? ($order -> department_id == $department -> id ? 'selected' : '') : ''}}>{{$department -> name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div> -->


                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Reference No </label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <input type="text" value = "{{isset($order) ? $order -> reference_number : ''}}" name = "reference_no" class="form-control" id = "reference_no_input">
                                                        </div>
                                                     </div>

                                                        @if (request() -> type == "so")
                                                        <div class="row align-items-center mb-1 can_hide" id = "selection_section" style = "display:none;">
                                                            <div class="col-md-3">
                                                                <label class="form-label">Reference From </label>
                                                            </div>

                                                            <div class="col-md-auto action-button">
                                                                <button onclick = "openQuotation('sq');" disabled type = "button" id = "select_qt_button" data-bs-toggle="modal" data-bs-target="#rescdule" class="btn btn-outline-primary btn-sm mb-0"><i data-feather="plus-square"></i> Quotation</button>
                                                                <button onclick = "openQuotation('po');" disabled type = "button" id = "select_po_button" data-bs-toggle="modal" data-bs-target="#rescdulePo" class="btn btn-outline-primary btn-sm mb-0"><i data-feather="plus-square"></i> Inter-Company PO</button>
                                                                <button onclick = "openQuotation('jo');" disabled type = "button" id = "select_jo_button" data-bs-toggle="modal" data-bs-target="#rescduleJo" class="btn btn-outline-primary btn-sm mb-0"><i data-feather="plus-square"></i> Inter-Company JO</button>
                                                            </div>
                                                        </div>
                                                        @endif
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
                            <div class="row">
                                <div class="col-md-12">
                                        <div class="card quation-card">
                                            <div class="card-header newheader">
                                                <div>
                                                    <h4 class="card-title">Customer Details</h4>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                                                        <input type="text" id = "customer_code_input" disabled placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input disable_on_edit" autocomplete="off" value = "{{isset($order) ? $order -> customer_code : ''}}" onblur = "onChangeCustomer('customer_code_input', true);">
                                                        <input type = "hidden" name = "customer_id" id = "customer_id_input" value = "{{isset($order) ? $order -> customer_id : ''}}"></input>
                                                        <input type = "hidden" name = "customer_code" id = "customer_code_input_hidden" value = "{{isset($order) ? $order -> customer_code : ''}}"></input>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Phone No.<span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control ledgerselecct ui-autocomplete-input" autocomplete="off" id = "customer_phone_no_input" name = "customer_phone_no" value = "{{isset($order) ? $order -> customer_phone_no : ''}}" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Email<span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control ledgerselecct ui-autocomplete-input" autocomplete="off"  id = "customer_email_input" name = "customer_email" value = "{{isset($order) ? $order -> customer_email : ''}}" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Consignee Name</label>
                                                            <input type="text" class="form-control ledgerselecct ui-autocomplete-input" autocomplete="off"  id = "consignee_name_input" name = "consignee_name" value = "{{isset($order) ? $order -> consignee_name : ''}}" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">GSTIN No.</label>
                                                            <input type="text" class="form-control ledgerselecct ui-autocomplete-input" autocomplete="off"  id = "customer_gstin_input" name = "customer_gstin" value = "{{isset($order) ? $order -> customer_gstin : ''}}" />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                                                             <select class="form-select disable_on_edit" id = "currency_dropdown" name = "currency_id" readonly>
                                                                @if (isset($order) && isset($order -> customer))
                                                                    <option value = "{{$order -> customer -> currency_id}}">{{$order -> customer ?-> currency ?-> name}}</option>
                                                                @else
                                                                    <option value = "">Select</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <input type = "hidden" name = "currency_code" value = "{{isset($order) ? $order -> currency_code : ''}}" id = "currency_code_input"></input>
                                                    </div>


                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Payment Terms <span class="text-danger">*</span></label>
                                                            <select class="form-select disable_on_edit" id = "payment_terms_dropdown" name = "payment_terms_id" readonly>
                                                                @if (isset($order) && isset($order -> customer))
                                                                    <option value = "{{$order -> customer -> payment_terms_id}}">{{$order -> customer ?-> payment_terms ?-> name}}</option>
                                                                @else
                                                                    <option value = "">Select</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <input type = "hidden" name = "payment_terms_code" value = "{{isset($order) ? $order -> payment_terms_code : ''}}" id = "payment_terms_code_input"></input>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Credit Days <span class="text-danger"></span></label>
                                                            <input type="number" value = "{{isset($order) ? $order -> credit_days : 0}}" name = "credit_days" class="form-control disable_on_edit" id = "credit_days_input" readonly>
                                                        </div>
                                                    </div>

                                                 </div>

                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="customer-billing-section h-100">
                                                            <p>Billing Address&nbsp;<span class="text-danger">*</span>
                                                            <a href="javascript:;" id="billAddressEditBtn" class="float-end"><i data-feather='edit-3'></i></a>
                                                        </p>
                                                            <div class="bilnbody">
                                                                <div class="genertedvariables genertedvariablesnone">
                                                                    <div class="mrnaddedd-prim" id = "current_billing_address">{{isset($order) ? $order -> billing_address_details ?-> display_address : ''}}</div>
                                                                    <input type = "hidden" id = "current_billing_address_id"></input>

                                                                    <input type = "hidden" id = "current_billing_country_id" name = "billing_country_id" value = "{{isset($order) && isset($order -> billing_address_details) ? $order -> billing_address_details -> country_id : ''}}"></input>
                                                                    <input type = "hidden" id = "current_billing_state_id" name = "billing_state_id" value = "{{isset($order) && isset($order -> billing_address_details) ? $order -> billing_address_details -> state_id : ''}}"></input>

                                                                    <input type="hidden" name="new_billing_country_id" id="new_billing_country_id" value="">
                                                                    <input type="hidden" name="new_billing_state_id" id="new_billing_state_id" value="">
                                                                    <input type="hidden" name="new_billing_city_id" id="new_billing_city_id" value="">
                                                                    <input type="hidden" name="new_billing_address" id="new_billing_address" value="">
                                                                    <input type="hidden" name="new_billing_type" id="new_billing_type" value="">
                                                                    <input type="hidden" name="new_billing_pincode" id="new_billing_pincode" value="">
                                                                    <input type="hidden" name="new_billing_phone" id="new_billing_phone" value="">
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="customer-billing-section">
                                                            <p>Shipping Address&nbsp;<span class="text-danger">*</span><span id = "same_checkbox_as_billing" style = "margin-left:120px; font-weight:100;"></span>
                                                                <a href="javascript:;" id="shipAddressEditBtn" data-bs-toggle="modal" class="float-end"><i data-feather='edit-3'></i></a>
                                                            </p>
                                                            <div class="bilnbody">

                                                                <div class="genertedvariables genertedvariablesnone">
                                                                    <div class="mrnaddedd-prim" id = "current_shipping_address">{{isset($order) ? $order -> shipping_address_details ?-> display_address : ''}}</div>
                                                                    <input type = "hidden" id = "current_shipping_address_id"></input>
                                                                    
                                                                    <input type = "hidden" id = "current_shipping_country_id" name = "shipping_country_id" value = "{{isset($order) && isset($order -> shipping_address_details) ? $order -> shipping_address_details -> country_id : ''}}"></input>
                                                                    <input type = "hidden" id = "current_shipping_state_id" name = "shipping_state_id" value = "{{isset($order) && isset($order -> shipping_address_details) ? $order -> shipping_address_details -> state_id : ''}}"></input>

                                                                    <input type="hidden" name="new_shipping_country_id" id="new_shipping_country_id" value="">
                                                                    <input type="hidden" name="new_shipping_state_id" id="new_shipping_state_id" value="">
                                                                    <input type="hidden" name="new_shipping_city_id" id="new_shipping_city_id" value="">
                                                                    <input type="hidden" name="new_shipping_address" id="new_shipping_address" value="">
                                                                    <input type="hidden" name="new_shipping_type" id="new_shipping_type" value="">
                                                                    <input type="hidden" name="new_shipping_pincode" id="new_shipping_pincode" value="">
                                                                    <input type="hidden" name="new_shipping_phone" id="new_shipping_phone" value="">
                                                                </div>
                                                            </div>
                                                    </div>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <div class="customer-billing-section">
                                                            <p>Pickup Address&nbsp;<span class="text-danger">*</span>
                                                        </p>
                                                            <div class="bilnbody">

                                                                <div class="genertedvariables genertedvariablesnone">
                                                                    <div class="mrnaddedd-prim" id = "current_pickup_address">{{isset($order) ? $order -> location_address_details ?-> display_address : ''}}</div>
                                                                </div>
                                                            </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12 {{(isset($order) && count($order -> dynamic_fields)) > 0 ? '' : 'd-none'}}" id = "dynamic_fields_section">
                                        @if (isset($dynamicFieldsUi))
                                            {!! $dynamicFieldsUi !!}
                                        @endif
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
                                                            <button type="button" id="importItem" class="mx-1 btn btn-sm btn-outline-primary importItem"
                                                                onclick="openImportItemModal('create')">
                                                                <i data-feather="upload"></i>
                                                                Import Item
                                                            </button>  
                                                            <a href="#" onclick = "deleteItemRows();" class="btn btn-sm btn-outline-danger me-50">
                                                                <i data-feather="x-circle"></i> Delete</a>
                                                                <a style = "display:none;" id = "add_item_section" href="#" onclick = "addItemRow();" class="btn btn-sm btn-outline-primary">
                                                                <i data-feather="plus"></i> Add Item</a>
                                                                <a href="#" id = "import_item_section" onclick = "uploadItems();" style = "display:none;" class="btn btn-sm btn-outline-primary d-none">
                                                                <i data-feather="upload-cloud"></i> Upload Item</a>
                                                                <a href="#" onclick = "copyItemRow();" id = "copy_item_section" style = "display:none;" class="btn btn-sm btn-outline-primary">
                                                                <i data-feather="copy"></i> Copy Item</a>
                                                         </div>
                                                         @if (isset($order) && isset($shortClose) && $shortClose && $buttons['amend'] && !isset(request() -> revisionNumber))
                                                    <div class="col-md-6 text-sm-end" id = "short_close_section">
                                                    <a href="javascript:;" id="shortCloseBtn" class="btn btn-sm btn-outline-danger me-50">
                                                            <i data-feather="x-circle"></i> Short Close</a>
                                                    </div>
                                                    @endif




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
                                                                               <input type="checkbox" class="form-check-input cannot_disable" id="select_all_items_checkbox" oninput = "checkOrRecheckAllItems(this);">
                                                                            <label class="form-check-label" for="select_all_items_checkbox" ></label>
                                                                               </div>
                                                                           </th>
                                                                           <th width="150px">Item Code</th>
                                                                           <th width="240px">Item Name</th>
                                                                           <th max-width = "180px">Attributes</th>
                                                                           <th>UOM</th>
                                                                           <th class = "numeric-alignment">Qty</th>
                                                                           <th class = "numeric-alignment">Rate</th>
                                                                           <th class = "numeric-alignment">Value</th>
                                                                           <th class = "numeric-alignment">Discount</th>
                                                                           <th class = "numeric-alignment" width = "150px">Total</th>
                                                                           <th style = "{{request() -> type === 'so' ? '' : 'display:none;'}}">Delivery Date</th>
                                                                           <th width="75px">Action</th>
                                                                         </tr>
                                                                       </thead>
                                                                       <tbody class="mrntableselectexcel" id = "item_header">
                                                                       @if (isset($order))
                                                                           @foreach ($order -> items as $orderItemIndex => $orderItem)
                                                                               <tr id = "item_row_{{$orderItemIndex}}" class = "item_header_rows" data-id = "{{$orderItem -> id}}" onclick = "onItemClick('{{$orderItemIndex}}');" data-index = "{{$orderItemIndex}}">
                                                                               <input type = 'hidden' name = "so_item_id[]" value = "{{$orderItem -> id}}" {{$orderItem -> is_editable ? '' : 'readonly'}}>
                                                                                <td class="customernewsection-form">
                                                                                   <div class="form-check form-check-primary custom-checkbox">
                                                                                       <input {{$orderItem -> restrict_delete ? 'disabled' : ''}} type="checkbox" class="form-check-input item_row_checks cannot_disable" id="item_checkbox_{{$orderItemIndex}}" del-index = "{{$orderItemIndex}}">
                                                                                       <label class="form-check-label" for="item_checkbox_{{$orderItemIndex}}"></label>
                                                                                   </div>
                                                                               </td>
                                                                                <td class="poprod-decpt">

                                                                                @if (isset($orderItem -> order_quotation_id))

                                                                                   <input type = "hidden" id = "qt_id_{{$orderItemIndex}}" value = "{{$orderItem -> sq_item_id}}" name = "quotation_item_ids[]"/>

                                                                                   <input type = "hidden" id = "qt_book_id_{{$orderItemIndex}}" value = "{{$orderItem -> quotation ?-> book_id}}" />
                                                                                   <input type = "hidden" id = "qt_book_code_{{$orderItemIndex}}" value = "{{$orderItem -> quotation ?-> book_code}}" />

                                                                                   <input type = "hidden" id = "qt_document_no_{{$orderItemIndex}}" value = "{{$orderItem -> quotation ?-> document_number}}" />
                                                                                   <input type = "hidden" id = "qt_document_date_{{$orderItemIndex}}" value = "{{$orderItem -> quotation ?-> document_date}}" />

                                                                                   <input type = "hidden" id = "qt_id_{{$orderItemIndex}}" value = "{{$orderItem -> quotation ?-> document_number}}" />

                                                                                   @endif

                                                                                   @if (isset($orderItem -> order_quotation_id))
                                                                                   <input type = "hidden" id = "po_item_ids_{{$orderItemIndex}}" value = "{{$orderItem -> po_item_id}}" name = "po_item_ids[]"/>
                                                                                   @endif

                                                                                   <input type="text" id = "items_dropdown_{{$orderItemIndex}}" name="item_code[{{$orderItemIndex}}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input {{$orderItem -> is_editable ? '' : 'restrict'}}" autocomplete="off" data-name="{{$orderItem -> item ?-> item_name}}" data-code="{{$orderItem -> item ?-> item_code}}" data-id="{{$orderItem -> item ?-> id}}" hsn_code = "{{$orderItem -> item ?-> hsn ?-> code}}" item-name = "{{$orderItem -> item ?-> item_name}}" specs = "{{$orderItem -> item ?-> specifications}}" attribute-array = "{{$orderItem -> item_attributes_array()}}"  value = "{{$orderItem -> item ?-> item_code}}" readonly>
                                                                                   <input type = "hidden" name = "item_id[]" id = "items_dropdown_{{$orderItemIndex}}_value" value = "{{$orderItem -> item_id}}"></input>
                                                                               </td>
                                                                               <td class="poprod-decpt">
                                                                                   <input type="text" id = "items_name_{{$orderItemIndex}}" name = "item_name[{{$orderItemIndex}}]" class="form-control mw-100"   value = "{{$orderItem -> item ?-> item_name}}" readonly>
                                                                               </td>
                                                                               <td class="poprod-decpt" id = "attribute_section_{{$orderItemIndex}}">
                                                                                   <button id = "attribute_button_{{$orderItemIndex}}" type = "button" data-bs-toggle="modal" onclick="setItemAttributes('items_dropdown_{{$orderItemIndex}}', '{{$orderItemIndex}}', {{ json_encode(!$orderItem->is_editable) }});" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                                                                                   <input type = "hidden" name = "attribute_value_{{$orderItemIndex}}" />
                                                                                </td>
                                                                               <td>
                                                                                   <select class="form-select" name = "uom_id[]" id = "uom_dropdown_{{$orderItemIndex}}">

                                                                                   </select>
                                                                               </td>
                                                                               <td><input type="text" id = "item_qty_{{$orderItemIndex}}" name = "item_qty[{{$orderItemIndex}}]" data-index = '{{$orderItemIndex}}' oninput = "changeItemQty(this, '{{$orderItemIndex}}');" onchange = "itemQtyChange(this, '{{$orderItemIndex}}')" value = "{{$orderItem -> order_qty}}" class="form-control mw-100 text-end item_qty_input" onblur = "setFormattedNumericValue(this);" min = "{{$orderItem -> min_attribute}}" max = "{{$orderItem -> max_attribute}}"/></td>
                                                                              <td><input type="text" id = "item_rate_{{$orderItemIndex}}" onkeydown = "openDeliveryScheduleFromTab('{{$orderItemIndex}}');" name = "item_rate[{{$orderItemIndex}}]" oninput = "changeItemRate(this, '{{$orderItemIndex}}');" value = "{{$orderItem -> rate}}" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" /></td>
                                                                               <td><input type="text" id = "item_value_{{$orderItemIndex}}" disabled class="form-control mw-100 text-end item_values_input" value = "{{$orderItem -> order_qty * $orderItem -> rate}}" /></td>
                                                                               <input type = "hidden" id = "header_discount_{{$orderItemIndex}}" value = "{{$orderItem -> header_discount_amount}}" ></input>
                                                                               <input type = "hidden" id = "header_expense_{{$orderItemIndex}}" value = "{{$orderItem -> header_expense_amount}}"></input>
                                                                                <td>
                                                                                   <div class="position-relative d-flex align-items-center">
                                                                                       <input type="text" id = "item_discount_{{$orderItemIndex}}" disabled class="form-control mw-100 text-end item_discounts_input" style="width: 70px" value = "{{$orderItem -> item_discount_amount}}"/>
                                                                                       <div class="ms-50">
                                                                                           <button type = "button" onclick = "onDiscountClick('item_value_{{$orderItemIndex}}', '{{$orderItemIndex}}')" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Add</button>
                                                                                       </div>
                                                                                   </div>
                                                                               </td>
                                                                                       <input type="hidden" id = "item_tax_{{$orderItemIndex}}" value = "{{$orderItem -> tax_amount}}" class="form-control mw-100 text-end item_taxes_input" style="width: 70px" />

                                                                               <td><input type="text" id = "value_after_discount_{{$orderItemIndex}}" value = "{{($orderItem -> order_qty * $orderItem -> rate) - $orderItem -> item_discount_amount}}" disabled class="form-control mw-100 text-end item_val_after_discounts_input" /></td>
                                                                               <td style = "{{request() -> type === 'so' ? '' : 'display:none;'}}"><input type="date" id = "delivery_date_{{$orderItemIndex}}" name = "delivery_date[{{$orderItemIndex}}]" value = "{{$orderItem -> delivery_date}}" class="form-control mw-100" /></td>
                                                                               <input type = "hidden" id = "value_after_header_discount_{{$orderItemIndex}}" class = "item_val_after_header_discounts_input" value = "{{($orderItem -> order_qty * $orderItem -> rate) - $orderItem -> item_discount_amount - $orderItem -> header_discount_amount}}" ></input>
                                                                               <input type="hidden" id = "item_total_{{$orderItemIndex}}" value = "{{($orderItem -> order_qty * $orderItem -> rate) - $orderItem -> item_discount_amount - $orderItem -> header_discount_amount + ($orderItem -> tax_amount)}}" disabled class="form-control mw-100 text-end item_totals_input" />
                                                                                <td>
                                                                                    <div class="d-flex">
                                                                                       @if (request() -> type === 'so')
                                                                                           <div class="me-50 cursor-pointer" onclick = "openDeliverySchedule('{{$orderItemIndex}}');">    <span data-bs-toggle="tooltip" data-bs-placement="top" title="Delivery Schedule" class="text-primary"><i data-feather="calendar"></i></span></div>
                                                                                           <div class="me-50 cursor-pointer dynamic_bom_div" id = "dynamic_bom_div_{{$orderItemIndex}}" style = "display:none;" onclick = "getCustomizableBOM({{$orderItemIndex}}, {{ json_encode(!$orderItem->is_editable) }})"> <span data-bs-toggle="tooltip" data-bs-placement="top" title="BOM" class="text-primary"><i data-feather="table"></i></span></div>
                                                                                       @endif
                                                                                       <div class="me-50 cursor-pointer" onclick = "setViewDetailedStocks('{{$orderItemIndex}}');"> <span data-bs-toggle="tooltip" data-bs-placement="top" title="Stocks" class="text-primary"><i data-feather="layers"></i></span></div>
                                                                                        <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick = "setItemRemarks('item_remarks_{{$orderItemIndex}}');">        <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span></div>
                                                                                   </div>
                                                                                </td>
                                                                                <input type="hidden" id = "item_remarks_{{$orderItemIndex}}" name = "item_remarks[]" value = "{{$orderItem -> remarks}}"/>

                                                                             </tr>
                                                                           @endforeach
                                                                       @else
                                                                       @endif
                                                                    </tbody>

                                                                    <tfoot>

                                                                        <tr class="totalsubheadpodetail">
                                                                           <td colspan="5"></td>
                                                                           <td class="text-end" id = "all_items_total_qty">00.00</td>
                                                                           <td></td>
                                                                           <td class="text-end" id = "all_items_total_value">00.00</td>
                                                                           <td class="text-end" id = "all_items_total_discount">00.00</td>
                                                                           <input type = "hidden" id = "all_items_total_tax"></input>
                                                                           <td class="text-end all_tems_total_common" id = "all_items_total_total">00.00</td>
                                                                           <td></td>
                                                                       </tr>

                                                                        <tr valign="top">
                                                                           <td colspan="{{request() -> type === 'so' ? 8 : 7}}" rowspan="10">
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
                                                                                   <tr>
                                                                                       <td class="poprod-decpt">
                                                                                           <div id ="current_item_inventory_details">

                                                                                           </div>
                                                                                       </td>
                                                                                   </tr>

                                                                                   <tr id = "current_item_delivery_schedule_row">
                                                                                       <td class="poprod-decpt">
                                                                                           <div id ="current_item_delivery_schedule">

                                                                                           </div>
                                                                                       </td>
                                                                                   </tr>

                                                                                   <tr id = "current_item_qt_no_row">
                                                                                       <td class="poprod-decpt">
                                                                                           <div id ="current_item_qt_no">

                                                                                           </div>
                                                                                       </td>
                                                                                   </tr>



                                                                                   <tr id = "current_item_description_row">
                                                                                       <td class="poprod-decpt">
                                                                                           <span class="badge rounded-pill badge-light-secondary"><strong>Remarks</strong>: <span style = "text-wrap:auto;" id = "current_item_description"></span></span>
                                                                                        </td>
                                                                                   </tr>
                                                                               </table>
                                                                           </td>
                                                                           <td colspan="4">
                                                                               <table class="table border mrnsummarynewsty" id = "summary_table">
                                                                                   <tr>
                                                                                       <td colspan="2" class="p-0">
                                                                                           <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between"><strong>Order Summary</strong>
                                                                                               <div class="addmendisexpbtn">
                                                                                                   <button type = "button" data-bs-toggle="modal" data-bs-target="#orderTaxes" class="btn p-25 btn-sm btn-outline-secondary" onclick = "onOrderTaxClick();">Taxes</button>
                                                                                                   <button type = "button" data-bs-toggle="modal" data-bs-target="#discountOrder" class="btn p-25 btn-sm btn-outline-secondary" onclick = "onOrderDiscountModalOpen();"><i data-feather="plus"></i> Discount</button>
                                                                                                   <button type = "button" data-bs-toggle="modal" data-bs-target="#expenses" class="btn p-25 btn-sm btn-outline-secondary" onclick = "onOrderExpenseModalOpen();"><i data-feather="plus"></i> Expenses</button>
                                                                                               </div>
                                                                                           </h6>
                                                                                       </td>
                                                                                   </tr>
                                                                                   <tr class="totalsubheadpodetail">
                                                                                       <td width="55%"><strong>Item Total</strong></td>
                                                                                       <td class="text-end" id = "all_items_total_value_summary">00.00</td>
                                                                                   </tr>
                                                                                   <tr class="">
                                                                                       <td width="55%">Item Discount</td>
                                                                                       <td class="text-end" id = "all_items_total_discount_summary">00.00</td>
                                                                                   </tr>
                                                                                   <tr class="totalsubheadpodetail">
                                                                                       <td width="55%"><strong>Taxable Value</strong></td>
                                                                                       <td class="text-end" id = "all_items_total_total_summary">00.00</td>
                                                                                   </tr>
                                                                                   <tr class="">
                                                                                       <td width="55%">Taxes</td>
                                                                                       <td class="text-end" id = "all_items_total_tax_summary">00.00</td>
                                                                                   </tr>
                                                                                   <tr class="totalsubheadpodetail">
                                                                                       <td width="55%"><strong>Total After Tax</strong></td>
                                                                                       <td class="text-end" id = "all_items_total_after_tax_summary">00.00</td>
                                                                                   </tr>
                                                                                   <tr class="">
                                                                                       <td width="55%">Expenses</td>
                                                                                       <td class="text-end" id = "all_items_total_expenses_summary">00.00</td>
                                                                                   </tr>
                                                                                   <input type = "hidden" name = "sub_total" value = "0.00"></input>
                                                                                   <input type = "hidden" name = "discount" value = "0.00"></input>
                                                                                   <input type = "hidden" name = "discount_amount" value = "0.00"></input>
                                                                                   <input type = "hidden" name = "other_expenses" value = "0.00"></input>
                                                                                   <input type = "hidden" name = "total_amount" value = "0.00"></input>
                                                                                   <!-- <tr>
                                                                                       <td><strong>Discount 1</strong></td>
                                                                                       <td class="text-end">1,000.00</td>
                                                                                   </tr> -->
                                                                                   <!-- <tr class="totalsubheadpodetail">
                                                                                       <td><strong>Taxable Value</strong></td>
                                                                                       <td class="text-end">38,000.00</td>
                                                                                   </tr>
                                                                                   <tr>
                                                                                       <td><strong>6% SGST</strong></td>
                                                                                       <td class="text-end">2,280.00</td>
                                                                                   </tr>
                                                                                   <tr>
                                                                                       <td><strong>6% CGST</strong></td>
                                                                                       <td class="text-end">2,280.00</td>
                                                                                   </tr> -->

                                                                                   <!-- <tr class="totalsubheadpodetail">
                                                                                       <td><strong>Total After Tax</strong></td>
                                                                                       <td class="text-end">42,560.00</td>
                                                                                   </tr> -->

                                                                                   <!-- <tr>
                                                                                       <td><strong>Parking Exp.</strong></td>
                                                                                       <td class="text-end">240.00</td>
                                                                                   </tr> -->
                                                                                   <tr class="voucher-tab-foot">
                                                                                       <td class="text-primary"><strong>Grand Total</strong></td>
                                                                                       <td>
                                                                                           <div class="quottotal-bg justify-content-end">
                                                                                               <h5 id = "grand_total">00.00</h5>
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
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select Quotation</h4>
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
                             <button onclick = "getQuotations('sq');" type = "button" class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                         </div>

						 <div class="col-md-12">
							<div class="table-responsive" style="overflow-y: auto;max-height: 200px;">
								<table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
									<thead>
										 <tr>
											<th>
												<!-- <div class="form-check form-check-inline me-0">
													<input class="form-check-input" type="checkbox" name="podetail" id="inlineCheckbox1">
												</div>  -->
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
					<button type = "button" class="btn btn-primary btn-sm can_hide" onclick = "processQuotation('sq');" data-bs-dismiss="modal"><i data-feather="check-circle"></i> Process</button>
				</div>
			</div>
		</div>
	</div>

    <div class="modal fade text-start" id="rescdulePo" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1250px">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select PO</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					 <div class="row">

                     <div class="col">
                            <div class="mb-1">
                            <label class="form-label">Customer Name</label>
                                <input type="text" id="customer_code_input_po" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "customer_id_po_val"></input>
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Series</label>
                                <input type="text" id="book_code_input_po" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "book_id_po_val"></input>
                            </div>
                        </div>


                         <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Quotation No.</label>
                                <input type="text" id="document_no_input_po" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "document_id_po_val"></input>
                            </div>
                        </div>

                         <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Item Name</label>
                                <input type="text" id="item_name_input_po" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "item_id_po_val"></input>
                            </div>
                        </div>

                         <div class="col  mb-1">
                              <label class="form-label">&nbsp;</label><br/>
                             <button onclick = "getQuotations('po');" type = "button" class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                         </div>

						 <div class="col-md-12">
							<div class="table-responsive" style="overflow-y: auto;max-height: 200px;">
								<table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
									<thead>
										 <tr>
											<th>
												<!-- <div class="form-check form-check-inline me-0">
													<input class="form-check-input" type="checkbox" name="podetail" id="inlineCheckbox1">
												</div>  -->
											</th>
											<th>Series</th>
											<th>Document No.</th>
											<th>Document Date</th>
                                            <th>Currency</th>
                                            <th>Customer Name</th>
											<th>Item</th>
											<th>Attributes</th>
											<th>UOM</th>
											<th>Quantity</th>
											<th>Balance Qty</th>
											<th>Rate</th>
										  </tr>
										</thead>
										<tbody id = "qts_data_table_po">

									   </tbody>


								</table>
							</div>
						</div>


					 </div>
				</div>
				<div class="modal-footer text-end">
					<button type = "button" class="btn btn-outline-secondary btn-sm can_hide" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
					<button type = "button" class="btn btn-primary btn-sm can_hide" onclick = "processQuotation('po');" data-bs-dismiss="modal"><i data-feather="check-circle"></i> Process</button>
				</div>
			</div>
		</div>
	</div>

    <div class="modal fade text-start" id="rescduleJo" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1250px">
			<div class="modal-content">
				<div class="modal-header">
					<div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select JO</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					 <div class="row">

                     <div class="col">
                            <div class="mb-1">
                            <label class="form-label">Customer Name</label>
                                <input type="text" id="customer_code_input_jo" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "customer_id_jo_val"></input>
                            </div>
                        </div>

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Series</label>
                                <input type="text" id="book_code_input_jo" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "book_id_jo_val"></input>
                            </div>
                        </div>


                         <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Quotation No.</label>
                                <input type="text" id="document_no_input_jo" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "document_id_jo_val"></input>
                            </div>
                        </div>

                         <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Item Name</label>
                                <input type="text" id="item_name_input_jo" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                <input type = "hidden" id = "item_id_jo_val"></input>
                            </div>
                        </div>

                         <div class="col  mb-1">
                              <label class="form-label">&nbsp;</label><br/>
                             <button onclick = "getQuotations('jo');" type = "button" class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                         </div>

						 <div class="col-md-12">
							<div class="table-responsive" style="overflow-y: auto;max-height: 200px;">
								<table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
									<thead>
										 <tr>
											<th>
												<!-- <div class="form-check form-check-inline me-0">
													<input class="form-check-input" type="checkbox" name="podetail" id="inlineCheckbox1">
												</div>  -->
											</th>
											<th>Series</th>
											<th>Document No.</th>
											<th>Document Date</th>
                                            <th>Currency</th>
                                            <th>Customer Name</th>
											<th>Item</th>
											<th>Attributes</th>
											<th>UOM</th>
											<th>Quantity</th>
											<th>Balance Qty</th>
											<th>Rate</th>
										  </tr>
										</thead>
										<tbody id = "qts_data_table_jo">

									   </tbody>


								</table>
							</div>
						</div>


					 </div>
				</div>
				<div class="modal-footer text-end">
					<button type = "button" class="btn btn-outline-secondary btn-sm can_hide" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
					<button type = "button" class="btn btn-primary btn-sm can_hide" onclick = "processQuotation('jo');" data-bs-dismiss="modal"><i data-feather="check-circle"></i> Process</button>
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

					<div class="table-responsive-md customernewsection-form">
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
											 <tr>

											</tr>

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


                    <!-- <div class="text-end"><a href="#" class="text-primary add-contactpeontxt mt-50"><i data-feather='plus'></i> Add Discount</a></div> -->

					<div class="table-responsive-md customernewsection-form">
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



					<div class="table-responsive-md customernewsection-form">
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
											 <tr>

											</tr>

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
					<div class="table-responsive-md customernewsection-form">
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

    <div class="modal fade" id="edit-address-shipping" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
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
                        <select class="select2 form-select vendor_dependent" id = "shipping_address_dropdown" name = "shipping_address" oninput = "onShippingAddressChange(this);">
                                                                        @if (isset($order) && isset($shipping_addresses))
                                                                            @foreach ($shipping_addresses as $shipping_address)
                                                                                <option value = "{{$shipping_address -> value}}" {{$order -> shipping_to === $shipping_address -> id}}>{{$shipping_address -> label}}</option>
                                                                            @endforeach
                                                                        @else
                                                                            <option value = "">Select</option>
                                                                        @endif
                                                                    </select>
                        </div>
                       <div class="col-md-6 mb-1">
							<label class="form-label">Country <span class="text-danger">*</span></label>
							<select class="select2 form-select" id = "shipping_country_id_input"  onchange = "changeDropdownOptions(this, ['shipping_state_id_input'], ['states'], '/states/', null, ['shipping_city_id_input'])">
								@foreach ($countries as $country)
                                    <option value = "{{$country -> value}}">{{$country -> label}}</option>
                                @endforeach
							</select>
						</div>


						<div class="col-md-6 mb-1">
							<label class="form-label">State <span class="text-danger">*</span></label>
							<select class="select2 form-select" id = "shipping_state_id_input"  onchange = "changeDropdownOptions(this, ['shipping_city_id_input'], ['cities'], '/cities/', null, [])">
							</select>
						</div>

                         <div class="col-md-6 mb-1">
							<label class="form-label">City <span class="text-danger">*</span></label>
							<select class="select2 form-select" name = "shipping_city_id" id = "shipping_city_id_input">
							</select>
						</div>


						<div class="col-md-6 mb-1">
							<label class="form-label w-100">Pincode <span class="text-danger">*</span></label>
							<input type="text" class="form-control" value="" placeholder="Enter Pincode" name ="shipping_pincode" id = "shipping_pincode_input"/>
						</div>

						<div class="col-md-12 mb-1">
							<label class="form-label">Address <span class="text-danger">*</span></label>
							<textarea class="form-control" placeholder="Enter Address" name = "shipping_address_text" id = "shipping_address_input"></textarea>
						</div>

                    </div>



				</div>

				<div class="modal-footer justify-content-center">
						<button type="button" class="btn btn-outline-secondary me-1 can_hide" onclick = "closeModal('edit-address-shipping');">Cancel</button>
					<button type="button" onclick = "saveAddressShipping();" class="btn btn-primary can_hide">Submit</button>
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
							<select class="select2 form-select" name = "billing_state_id" id = "billing_state_id_input" onchange = "changeDropdownOptions(this, ['billing_city_id_input'], ['cities'], '/cities/', null, [])">
							</select>
						</div>

                         <div class="col-md-6 mb-1">
							<label class="form-label">City <span class="text-danger">*</span></label>
							<select class="select2 form-select" name = "billing_city_id" id = "billing_city_id_input">
							</select>
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
											 <tr>

											</tr>


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

    <div class="modal fade" id="delivery" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog  modal-dialog-centered" >
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-2 mx-50 pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">Delivery Schedule</h1>


                    <div class = "row mt-2">
                        <div class="col-md-6" style = "padding-right:0px">
                            <div class="">
                                <label class="form-label">Delivery Quantity<span class="text-danger">*</span></label>
                                <input type="text" id="new_item_delivery_qty_input"  class="form-control mw-100 text-end">
                            </div>
                        </div>
                        <div class="col-md-4" style = "padding-right:0px">
                            <div class="">
                                <label class="form-label">Delivery Date <span class="text-danger">*</span></label>
                                <td><input type="date" id = "new_item_delivery_date_input" value="{{Carbon\Carbon::now() -> format('Y-m-d')}}" class="form-control mw-100" /></td>
                            </div>
                        </div>
                        <div class="col-md-auto mt-1 d-flex align-items-center justify-content-center" style = "padding-right:0px">
                            <div>
                            <a href="#" onclick = "addDeliveryScheduleRow();" class="text-primary can_hide"><i data-feather="plus-square"></i></a>
                            </div>
                        </div>
                    </div>


					<div class="table-responsive-md customernewsection-form">
								<table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail" id = "delivery_schedule_main_table">
									<thead>
										 <tr>
                                            <th>S.No</th>
											<th width="150px">Quantity</th>
											<th>Date</th>
											<th>Action</th>
										  </tr>
										</thead>
										<tbody>
											 <tr>

											</tr>
                                            <tr>
                                                 <td class="text-dark"><strong>Total</strong></td>
                                                 <td class="text-dark"><strong id = "item_delivery_qty">00.00</strong></td>
                                                 <td></td>
											</tr>


									   </tbody>


								</table>
							</div>

				</div>

				<div class="modal-footer justify-content-center">
						<button type="button" class="btn btn-outline-secondary me-1 can_hide" onclick="closeModal('delivery');">Cancel</button>
					<button type="button" class="btn btn-primary can_hide" onclick="closeModal('delivery');">Submit</button>
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
								<table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
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
						<button type="button" class="btn btn-outline-secondary me-1 can_hide" onclick = "closeModal('attribute');">Cancel</button>
					    <button type="button" class="btn btn-primary can_hide" onclick = "closeModal('attribute');">Select</button>
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
                {{request() -> type === 'sq' ? 'Sales Quotation' : 'Sales Order'}}
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
        <form class="ajax-submit-2" method="POST" action="{{ route('document.approval.so') }}" data-redirect="javascript: history.go(-1)" enctype='multipart/form-data'>
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
              <p>Are you sure you want to <strong>Amend</strong> this <strong>{{request() -> type == "sq" ? "Sales Quotation" : "Sales Order"}}</strong>?</p>
              <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
              <button type="button" data-bs-dismiss="modal" onclick = "amendConfirm();" class="btn btn-primary">Confirm</button>
          </div>
      </div>
  </div>
</div>

<x-inventory.item-stock-details title="Stock Details" />

<div class="modal fade" id="BOM" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog  modal-dialog-centered" style = "min-width:80%;">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-2 mx-50 pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">Bill of Material</h1>

                    <div class="table-responsive-md">
                        <table
                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                            <thead>
                                <tr>
                                    <th style = "width:180px;">Item Code</th>
                                    <th>Item Name</th>
                                    <th>UOM</th>
                                    <th>Attributes</th>
                                    <th>Qty</th>
                                    <th>Remark</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="bom-details">

                            </tbody>
                        </table>
                    </div>
				</div>
			</div>
		</div>
	</div>

    <div class="modal fade" id="shortCloseModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content">
          <div class="modal-header">
             <div>
                <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="popupTitle">Short Close</h4>
             </div>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body pb-2">
             <div class="row mt-1">
                <div class="col-md-12">
                   <div class="mb-2">
                      <label class="form-label">Reason</label>
                      <textarea maxlength="250" name="amend_remark" class="form-control cannot_disable"></textarea>
                      <span id="amendRemarkError"  class="ajax-validation-error-span form-label text-danger d-none" style="font-size:12px" role="alert">*Required</span>
                   </div>
                   <div class="mb-2">
                      <label class="form-label">Upload Document</label>
                      <input type="file" onchange="addFiles(this, 'so_popup_file_preview')" name="amend_attachment[]" multiple class="form-control cannot_disable" />
                      <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                   </div>
                   <input type="hidden" name="short_close_ids" id="short_close_ids">
                   <div class="mt-2">
                       <div class="row" id="so_popup_file_preview">
                       </div>
                   </div>
                </div>
             </div>
          </div>
          <div class="modal-footer justify-content-center">
             <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button>
             <button type="button" id="shortCloseBtnSubmit" class="btn btn-primary">Submit</button>
          </div>
       </div>
    </div>
 </div>

 <div class="modal fade" id="item_upload" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style = "min-width:90%;">
       <div class="modal-content">
          <div class="modal-header">
             <div>
                <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="uploadItemTitle">Import item</h4>
             </div>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body pb-2">
             <div class="row mt-1">
                <div class="col-md-4">
                   <div class="mb-2">
                      <label class="form-label">Upload Document</label>
                      <input type="file"  id = "import_attachment_input" class="form-control" />
                      <span class="text-primary small">{{__("(Allowed formats: .xlsx, .xls, .csv)")}}</span>
                   </div>
                </div>
                <div class="col-md-8 d-flex align-items-center justify-content-end mb-2">
                    <a download href="{{$itemImportFile}}" class="btn btn-outline-primary">
                        <i class="fas fa-download me-1"></i> Download Sample
                    </a>
                </div>
                <div class="col-md-12 col-12 d-none" id = "upload-status-section">
                    <div class="card  new-cardbox">
                        <ul class="nav nav-tabs border-bottom" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#Succeded">Valid Records &nbsp;<span id="success-count">(0)</span></a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#Failed">Invalid Records &nbsp;<span id="failed-count">(0)</span></a>
                            </li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="Succeded">
                                <div class="table-responsive">
                                    <table class="datatables-basic table myrequesttablecbox">
                                        <thead>
                                            <tr>
                                            <th class = "no-wrap">Item Code</th>
                                            <th class = "no-wrap">UOM</th>
                                            <th class = "no-wrap">Attributes</th>
                                            <th class = "numeric-alignment">Qty</th>
                                            <th class = "numeric-alignment">Rate</th>
                                            <th class = "no-wrap">Delivery Date</th>
                                            </tr>
                                        </thead>
                                        <tbody id="success-table-body">
                                            <tr>
                                                <td colspan = "9">No records found</td>
                                            <tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane" id="Failed">
                                <div class="table-responsive">
                                    <table class="datatables-basic table myrequesttablecbox">
                                        <thead>
                                            <tr>
                                                <th class = "no-wrap">Item Code</th>
                                                <th class = "no-wrap">UOM</th>
                                                <th class = "no-wrap">Attributes</th>
                                                <th class = "numeric-alignment">Qty</th>
                                                <th class = "numeric-alignment">Rate</th>
                                                <th class = "no-wrap">Delivery Date</th>
                                                <th class = "no-wrap">Errors</th>
                                            </tr>
                                        </thead>
                                        <tbody id="failed-table-body">
                                            <tr>
                                                <td colspan = "10">No records found</td>
                                            <tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
             </div>
          </div>
          <div class="modal-footer justify-content-center">
             <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button>
             <button type="button" onclick = "uploadItemImportFile();" class="btn btn-outline-primary"
                        name="action" ><i data-feather='upload-cloud'></i> Upload</button>
             <button type="button" id="item_upload_submit" class="btn btn-primary d-none" onclick = "uploadAndRenderItems();" >Submit</button>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
@section('scripts')

<script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
<script type="text/javascript" src="{{asset('app-assets/js/scripts/sales/common.js')}}"></script>
<script>
        let currentSelectedItemIndex = null;

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
                Swal.fire({
                    title: 'Error!',
                    text: "Customer Currency not found",
                    icon: 'error',
                });
                $("#customer_code_input").val("");
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
                    text: "Customer Payment Terms not found",
                    icon: 'error',
                });
                $("#customer_code_input").val("");
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
            //Get Addresses (Billing + Shipping)
            changeDropdownOptions(document.getElementById('customer_id_input'), ['billing_address_dropdown','shipping_address_dropdown'], ['billing_addresses', 'shipping_addresses'], '/customer/addresses/', 'vendor_dependent');
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
            //Append Extra Key for Data
            if (extraKeysForRequest && extraKeysForRequest.length > 0) {
                extraKeysForRequest.forEach((extraData, index) => {
                    apiRequestValue += ((index == 0 ? "?" : "&") + extraData.key) + "=" + (extraData.value);
                });
            }
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
                                    document.getElementById('current_billing_country_id').value = item.country_id;
                                    document.getElementById('current_billing_state_id').value = item.state_id;
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
                document.getElementById('current_shipping_address_id').value = "";
                document.getElementById('current_billing_address').textContent = "";
                document.getElementById('current_shipping_address').textContent = "";
                document.getElementById('customer_id_input').value = "";
                document.getElementById('customer_code_input').value = "";
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
                    let rateElement = document.getElementById('item_rate_' + index);
                    if (rateElement && response.item.sell_price) {
                        rateElement.value = parseFloat(response.item.sell_price);
                        itemRowCalculation(index);
                    }
                    checkBomCondition(index, false, true);
                }).catch(error => {
                    console.log("Error : ", error);
                })
            }
        }

        function setItemAttributes(elementId, index, readOnly = false)
        {
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
                    <select ${readOnly ? 'disabled' : ''} class="form-select select2" id = "attribute_val_${index}" style = "max-width:100% !important;" onchange = "changeAttributeVal(this, ${elementIdForDropdown}, ${index});">
                        <option>Select</option>
                        ${optionsHtml}
                    </select>
                    </td>
                    </tr>
                    `
                });
                attributesTable.innerHTML = innerHtml;
                let attributeButton = document.getElementById('attribute_button_' + index);
                if (attributesJSON.length == 0) {
                    document.getElementById('item_qty_' + index).focus();
                    if (attributeButton) {
                        attributeButton.disabled = true;
                    }
                    document.getElementById('attribute_button_' + index).disabled = true;
                } else {
                    $("#attribute").modal("show");
                    if (attributeButton) {
                        attributeButton.disabled = false;
                    }
                }
            }
            let finalAmendSubmitButton = document.getElementById("amend-submit-button");

            viewModeScript(finalAmendSubmitButton ? false : true);

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
            const tableElementBody = document.getElementById('item_header');
            const previousElements = document.getElementsByClassName('item_header_rows');
            const newIndex = previousElements.length ? previousElements.length : 0;
            if (newIndex == 0) {
                let addRow = $('#series_id_input').val() &&  $('#order_no_input').val() && $('#order_date_input').val() && $('#customer_code_input').val();
                if (!addRow) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please fill all the header details first',
                    icon: 'error',
                });
                return;
                }
            } else {
                let addRow = $('#items_dropdown_' + (newIndex - 1)).val() &&  $('#item_qty_' + (newIndex - 1)).val() && $('#item_rate_' + (newIndex - 1)).val();
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
            newItemRow.setAttribute('data-index', newIndex);
            newItemRow.onclick = function () {
                onItemClick(newIndex);
            };
            newItemRow.innerHTML = `
            <tr id = "item_row_${newIndex}">
                <td class="customernewsection-form">
                   <div class="form-check form-check-primary custom-checkbox">
                       <input type="checkbox" class="form-check-input item_row_checks" id="item_row_check_${newIndex}" del-index = "${newIndex}">
                       <label class="form-check-label" for="item_row_check_${newIndex}"></label>
                   </div>
               </td>
                <td class="poprod-decpt">

                   <input type="text" id = "items_dropdown_${newIndex}" name="item_code[${newIndex}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" autocomplete="off" data-name="" data-code="" data-id="" hsn_code = "" item_name = "" attribute-array = "[]" specs = "[]">
                   <input type = "hidden" name = "item_id[]" id = "items_dropdown_${newIndex}_value"></input>

               </td>

               <td class="poprod-decpt">
                    <input type="text" id = "items_name_${newIndex}" name = "item_name[${newIndex}]" class="form-control mw-100"   value = "" readonly>
                </td>
               <td class="poprod-decpt" id = "attribute_section_${newIndex}">
                   <button id = "attribute_button_${newIndex}" type = "button" data-bs-toggle="modal" onclick = "setItemAttributes('items_dropdown_${newIndex}', ${newIndex});" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                   <input type = "hidden" name = "attribute_value_${newIndex}" />

                </td>
               <td>
                   <select class="form-select" name = "uom_id[]" id = "uom_dropdown_${newIndex}">

                   </select>
               </td>
               <td><input type="text" id = "item_qty_${newIndex}" data-index = '${newIndex}' name = "item_qty[${newIndex}]" oninput = "changeItemQty(this, ${newIndex});" onchange = "itemQtyChange(this, ${newIndex})" class="form-control mw-100 text-end item_qty_input" onblur = "setFormattedNumericValue(this);"/></td>
              <td><input type="text" id = "item_rate_${newIndex}" onkeydown = "openDeliveryScheduleFromTab(${newIndex});" name = "item_rate[${newIndex}]" oninput = "changeItemRate(this, ${newIndex});" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);"/></td>
               <td><input type="text" id = "item_value_${newIndex}" disabled class="form-control mw-100 text-end item_values_input" /></td>
               <input type = "hidden" id = "header_discount_${newIndex}" value = "0" ></input>
               <input type = "hidden" id = "header_expense_${newIndex}" ></input>
                <td>
                   <div class="position-relative d-flex align-items-center">
                       <input type="text" id = "item_discount_${newIndex}" disabled class="form-control mw-100 text-end item_discounts_input" style="width: 70px" />
                       <div class="ms-50">
                           <button type = "button" onclick = "onDiscountClick('item_value_${newIndex}', ${newIndex})" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Add</button>
                       </div>
                   </div>
               </td>
               <input type="hidden" id = "item_tax_${newIndex}" class="form-control mw-100 text-end item_taxes_input" style="width: 70px" />
               <td><input type="text" id = "value_after_discount_${newIndex}"  disabled class="form-control mw-100 text-end item_val_after_discounts_input" /></td>
               <td style = "{{request() -> type === 'so' ? '' : 'display:none;'}}"><input type="date" name = "delivery_date[${newIndex}]" id = "delivery_date_${newIndex}" class="form-control mw-100" value = "{{Carbon\Carbon::now() -> format('Y-m-d')}}" /></td>
               <input type = "hidden" id = "value_after_header_discount_${newIndex}" class = "item_val_after_header_discounts_input" ></input>

                    <input type="hidden" id = "item_total_${newIndex}"  disabled class="form-control mw-100 text-end item_totals_input" />
                    <td>
                    <div class="d-flex">
                        @if(request() -> type === 'so')
                            <div class="me-50 cursor-pointer" onclick = "openDeliverySchedule(${newIndex});">    <span data-bs-toggle="tooltip" data-bs-placement="top" title="Delivery Schedule" class="text-primary"><i data-feather="calendar"></i></span></div>
                            <div class="me-50 cursor-pointer dynamic_bom_div" id = "dynamic_bom_div_${newIndex}" onclick = "getCustomizableBOM(${newIndex})" style = "display:none;"> <span data-bs-toggle="tooltip" data-bs-placement="top" title="BOM" class="text-primary"><i data-feather="table"></i></span></div>
                        @endif
                        <div class="me-50 cursor-pointer" onclick = "setViewDetailedStocks('${newIndex}');"> <span data-bs-toggle="tooltip" data-bs-placement="top" title="Stocks" class="text-primary"><i data-feather="layers"></i></span></div>
                        <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick = "setItemRemarks('item_remarks_${newIndex}');">        <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span></div>
                   </div>
                </td>
                <input type="hidden" id = "item_remarks_${newIndex}" name = "item_remarks[]"/>
             </tr>
            `;
            tableElementBody.appendChild(newItemRow);
            initializeAutocomplete1("items_dropdown_" + newIndex, newIndex);
            renderIcons();
            disableHeader();
            assignBomConditions(newIndex);
        }

        function uploadItems()
        {
            const tableElementBody = document.getElementById('item_header');
            const previousElements = document.getElementsByClassName('item_header_rows');
            const newIndex = previousElements.length ? previousElements.length : 0;
            if (newIndex == 0) {
                let addRow = $('#series_id_input').val() &&  $('#order_no_input').val() && $('#order_date_input').val() && $('#customer_code_input').val();
                if (!addRow) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please fill all the header details first',
                    icon: 'error',
                });
                return;
                }
            } else {
                let addRow = $('#items_dropdown_' + (newIndex - 1)).val() &&  $('#item_qty_' + (newIndex - 1)).val() && $('#item_rate_' + (newIndex - 1)).val();
                if (!addRow) {
                    Swal.fire({
                    title: 'Error!',
                    text: 'Please fill all the previous item details first',
                    icon: 'error',
                });
                return;
                }
            }
            $("#item_upload").modal("show");
        }

        function deleteItemRows()
        {
            var deletedItemIds = JSON.parse(localStorage.getItem('deletedSoItemIds'));
            const allRowsCheck = document.getElementsByClassName('item_row_checks');
            let deleteableElementsId = [];
            for (let index = 0; index < allRowsCheck.length; index++) {
                if (allRowsCheck[index].checked) {
                    const currentRowIndex = allRowsCheck[index].getAttribute('del-index');
                    const currentRow = document.getElementById('item_row_' + currentRowIndex);
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
            localStorage.setItem('deletedSoItemIds', JSON.stringify(deletedItemIds));
            const allRowsNew = document.getElementsByClassName('item_row_checks');
            if (allRowsNew.length > 0) {
                for (let idx = 0; idx < allRowsNew.length; idx++) {
                    const currentRowIndex = allRowsCheck[idx].getAttribute('del-index');
                    if (document.getElementById('item_row_' + currentRowIndex)) {
                        itemRowCalculation(currentRowIndex);
                    }
                }
                disableHeader();
            }  else {
                const allItemsHeaderDiscount = document.getElementsByClassName('order_discount_hidden_fields');
                const allItemsHeaderExpense = document.getElementsByClassName('order_expense_hidden_fields');
                document.querySelectorAll('.order_discount_hidden_fields, .order_expense_hidden_fields, .order_expenses, .order_discounts').forEach(el => el.remove());
                $('#order_discount_row').remove();
                $('#all_items_total_expenses_summary').val('0.00');


                document.getElementById('all_items_total_value').innerText = '0.00';
                document.getElementById('total_order_discount').innerText = '0.00';
                document.getElementById('total_order_expense').innerText = '0.00';
                document.getElementById('all_items_total_discount').innerText = '0.00';
                document.getElementById('all_items_total_expenses_summary').innerText = '0.00';

                // for (let i = 0; i < allItemsHeaderDiscount.length; i++) {
                //     allItemsHeaderDiscount[i].remove();
                // }
                // for (let j = 0; j < allItemsHeaderExpense.length; j++) {
                //     allItemsHeaderExpense[j].remove();
                // }
                onItemClick(-1);
                enableHeader();
                setAllTotalFields();
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
                if (index != allItemRows[itemIndex].getAttribute('data-index')) {
                    var currentItemCodeElement = document.getElementById('items_dropdown_' + itemIndex);
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

        function itemQtyChange(element, index)
        {
            var inputNumValue = parseFloat(element.value ? element.value  : 0);
            if (element.hasAttribute('min'))
            {
                var minInputVal = parseFloat(element.getAttribute('min'));
                if (inputNumValue < minInputVal) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Quantity cannot be less than ' + minInputVal,
                        icon: 'error',
                    });
                    element.value = (parseFloat(minInputVal ? minInputVal  : 0)).toFixed(2);
                    itemRowCalculation(index);
                    return;
                }
            }
        }

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
            if (element.hasAttribute('max'))
            {
                var maxInputVal = parseFloat(element.getAttribute('max'));
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
            if(itemRowId != -1)
            {

            currentSelectedItemIndex = itemRowId;
            const hsn_code = document.getElementById('items_dropdown_'+ itemRowId).getAttribute('hsn_code');
            const item_name = document.getElementById('items_dropdown_'+ itemRowId).getAttribute('item-name');
            const attributes = JSON.parse(document.getElementById('items_dropdown_'+ itemRowId).getAttribute('attribute-array'));
            const specs = JSON.parse(document.getElementById('items_dropdown_'+ itemRowId).getAttribute('specs'));

            const qtDetailsRow = document.getElementById('current_item_qt_no_row');
            const qtDetails = document.getElementById('current_item_qt_no');

            let qtDocumentNo = document.getElementById('qt_document_no_'+ itemRowId);
            let qtBookCode = document.getElementById('qt_book_code_'+ itemRowId);
            let qtDocumentDate = document.getElementById('qt_document_date_'+ itemRowId);

            qtDocumentNo = qtDocumentNo?.value ? qtDocumentNo.value : '';
            qtBookCode = qtBookCode?.value ? qtBookCode.value : '';
            qtDocumentDate = qtDocumentDate?.value ? qtDocumentDate.value : '';

            if (qtDocumentNo && qtBookCode && qtDocumentDate) {
                qtDetailsRow.style.display = "table-row";
                qtDetails.innerHTML = `<span class="badge rounded-pill badge-light-primary"><strong>Quotation No: </strong>: ${qtBookCode + "-" + qtDocumentNo}</span><span class="badge rounded-pill badge-light-primary"><strong>Quotation Date: </strong>: ${qtDocumentDate}</span>`;
            } else {
                qtDetailsRow.style.display = "none";
                qtDetails.innerHTML = ``;
            }

            // document.getElementById('current_item_hsn_code').innerText = hsn_code;
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
                specsInnerHTML +=  `<span class="badge rounded-pill badge-light-primary "><strong>${spec.specification_name}</strong>: ${spec.value}</span>`;
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
            //Delivery Schedule
            const deliveriesQty = document.getElementsByClassName('delivery_schedule_qties_hidden_' + itemRowId);
            const deliveriesDate = document.getElementsByClassName('delivery_schedule_dates_hidden_' + itemRowId);
            deliveriesHTML = ``;
            for (let delvIndex = 0; delvIndex < deliveriesQty.length; delvIndex++) {
                deliveriesHTML += `<span class="badge rounded-pill badge-light-primary "><strong>${moment(deliveriesDate[delvIndex].value).format('D/M/Y')}</strong>: ${deliveriesQty[delvIndex].value}</span>`
            }
            if (deliveriesHTML) {
                document.getElementById('current_item_delivery_schedule_row').style.display = "table-row";
            } else {
                document.getElementById('current_item_delivery_schedule_row').style.display = "none";
            }
            document.getElementById('current_item_delivery_schedule').innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Delivery Schedule</strong>:` + deliveriesHTML;
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
                if (itemId && uomId)
                {
                    $.ajax({
                        url: "{{route('get_item_inventory_details')}}",
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            quantity: document.getElementById('item_qty_' + itemRowId).value,
                            item_id: document.getElementById('items_dropdown_'+ itemRowId + '_value').value,
                            uom_id : document.getElementById('uom_dropdown_' + itemRowId).value,
                            selectedAttr : selectedItemAttr
                        },
                        success: function(data) {
                            if (data.inv_qty && data.inv_uom)
                            document.getElementById('current_item_inventory_details').innerHTML = `
                            <span class="badge rounded-pill badge-light-primary"><strong>Inv. UOM</strong>: ${data.inv_uom}</span>
                            <span class="badge rounded-pill badge-light-primary"><strong>Qty in ${data.inv_uom}</strong>: ${data.inv_qty}</span>
                            `;
                            if (data?.item && data?.item?.category && data?.item?.sub_category) {
                                document.getElementById('current_item_cat_hsn').innerHTML = `
                                <span class="badge rounded-pill badge-light-primary"><strong>Category</strong>: <span id = "item_category">${ data?.item?.category?.name}</span></span>
                                <span class="badge rounded-pill badge-light-primary"><strong>Sub Category</strong>: <span id = "item_sub_category">${ data?.item?.sub_category?.name}</span></span>
                                <span class="badge rounded-pill badge-light-primary"><strong>HSN</strong>: <span id = "current_item_hsn_code">${hsn_code}</span></span>

                                `;
                            }
                            if (data?.stocks) {
                                document.getElementById('current_item_stocks_row').style.display = "table-row";
                                document.getElementById('current_item_stocks').innerHTML = `
                                <span class="badge rounded-pill badge-light-primary"><strong>Confirmed Stock</strong>: <span id = "item_sub_category">${data?.stocks?.confirmedStockAltUom}</span></span>
                                <span class="badge rounded-pill badge-light-primary"><strong>Unconfirmed Stock</strong>: <span id = "item_category">${data?.stocks?.pendingStockAltUom}</span></span>
                                `;
                            }
                        },
                        error: function(xhr) {
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });
                }
            }else{
                    // current_item_cat_hsn,current_item_attributes,current_item_stocks,current_item_inventory_details,current_item_qt_no_row,current_item_description_row clear these id values
                    document.getElementById('current_item_cat_hsn').innerHTML = '';
                    document.getElementById('current_item_attributes').innerHTML = '';
                    document.getElementById('current_item_stocks').innerHTML = '';
                    document.getElementById('current_item_inventory_details').innerHTML = '';
                    document.getElementById('current_item_qt_no_row').style.display = 'none';
                    document.getElementById('current_item_description_row').style.display = 'none';document.getElementById('current_item_cat_hsn').innerHTML = ``;

                }

        }

        function openDeliveryScheduleFromTab(itemRowIndex)
        {
            if (event.key === 'Tab') {
                document.getElementById('delivery_date_' + itemRowIndex).focus();
                // openDeliverySchedule(itemRowIndex);
            }
        }

        function openDeliverySchedule(itemRowIndex)
        {
            const docType = "{{$type}}";
            if (docType == "sq") {
                return;
            }
            var itemAndAttrCheck = checkSelectedItemAndAttributes(itemRowIndex);
            if (itemAndAttrCheck) {
                Swal.fire({
                    title: 'Error!',
                    text: itemAndAttrCheck,
                    icon: 'error',
                });
                $('#delivery').modal('hide');
                return;
            }
            $('#delivery').modal('show');
            document.getElementById('delivery_schedule_main_table').setAttribute('item-row-index', itemRowIndex);
            renderPreviousDeliverySchedule(itemRowIndex);
            let finalAmendSubmitButton = document.getElementById("amend-submit-button");

            viewModeScript(finalAmendSubmitButton ? false : true);
            document.getElementById('new_item_delivery_qty_input').focus();
        }

        function renderPreviousDeliverySchedule(itemRowIndex)
        {
                const previousHiddenQtyFields = document.getElementsByClassName('delivery_schedule_qties_hidden_' + itemRowIndex);
                const previousHiddenDateFields = document.getElementsByClassName('delivery_schedule_dates_hidden_' + itemRowIndex);

                var totalDeliveryQty = 0;

                    const oldDelivery = document.getElementsByClassName('item_deliveries');
                    if (oldDelivery && oldDelivery.length > 0)
                    {
                        while (oldDelivery.length > 0) {
                            oldDelivery[0].remove();
                        }
                    }
                var isNew = true;
                var newData = ``;
                for (let index = 0; index < previousHiddenQtyFields.length; index++) {
                    const newHTML = document.getElementById('delivery_schedule_main_table').insertRow(index + 2);
                    newHTML.id = "item_delivery_schedule_modal_" + index;
                    newHTML.className = "item_deliveries";
                    newData = `
                        <td>${index+1}</td>
                        <td>${previousHiddenQtyFields[index].value}</td>
                        <td>${moment(previousHiddenDateFields[index].value).format('D/M/Y')}</td>
                        <td>
                            <a data-id = "${previousHiddenQtyFields[index].getAttribute('data-id')}" href="#" class="text-danger can_hide" onclick = "removeDeliverySchedule(${index}, ${itemRowIndex});"><i data-feather="trash-2"></i></a>
                        </td>
                    `;
                    newHTML.innerHTML = newData;
                    isNew = false;
                    totalDeliveryQty += parseFloat(previousHiddenQtyFields[index].value ? previousHiddenQtyFields[index].value : 0);
                }

                document.getElementById('new_item_delivery_date_input').value = "{{Carbon\Carbon::now() -> format('Y-m-d')}}";

                if (isNew) {
                    document.getElementById('new_item_delivery_qty_input').value = document.getElementById("item_qty_"+itemRowIndex).value;
                } else {
                    document.getElementById('new_item_delivery_qty_input').value = "";
                }
                document.getElementById('item_delivery_qty').textContent = totalDeliveryQty.toFixed(2);
                renderIcons();
        }

        function removeDeliverySchedule(index, itemIndex)
        {
            let deletedDelivery = JSON.parse(localStorage.getItem('deletedDelivery'));

            const removableElement = document.getElementById('item_delivery_schedule_modal_' + index);
            if (removableElement) {

                removableElement.remove();
            }
            var qtyHiddenInput = document.getElementById("item_delivery_schedule_qty_"  + itemIndex + "_" + index);
            if (qtyHiddenInput && qtyHiddenInput.getAttribute('data-id')) {
                deletedDelivery.push(qtyHiddenInput.getAttribute('data-id'));
            }
            qtyHiddenInput?.remove();
            document.getElementById("item_delivery_schedule_date_" + itemIndex + "_" + index)?.remove();
            localStorage.setItem('deletedDelivery', JSON.stringify(deletedDelivery));
            renderPreviousDeliverySchedule(itemIndex);
        }

        function addDeliveryScheduleRow()
        {
            const ItemRowIndexVal = document.getElementById('delivery_schedule_main_table').getAttribute('item-row-index');

            const previousHiddenQtiesInput = document.getElementsByClassName('delivery_schedule_qties_hidden_' + ItemRowIndexVal);
            let totalDeliveryQty = 0;
            for (let index = 0; index < previousHiddenQtiesInput.length; index++) {
                totalDeliveryQty+= parseFloat(previousHiddenQtiesInput[index].value ? previousHiddenQtiesInput[index].value : 0);
            }

            const deliveryQty = document.getElementById('new_item_delivery_qty_input').value;
            const deliverySchedule = document.getElementById('new_item_delivery_date_input').value;

            const updatedQty = totalDeliveryQty + parseFloat(deliveryQty? deliveryQty : 0);
            const itemQty = parseFloat(document.getElementById('item_qty_' + ItemRowIndexVal).value ? document.getElementById('item_qty_' + ItemRowIndexVal).value : 0);

            const currentDeliveryDate = new Date(deliverySchedule);
            const todayDate = new Date();
            todayDate.setHours(0, 0, 0, 0);

            if (currentDeliveryDate < todayDate) {
                Swal.fire({
                    title: 'Error!',
                    text: "Past Delivery Schedule Date is not allowed",
                    icon: 'error',
                });
                return;
            }

            if (updatedQty > itemQty) {
                Swal.fire({
                    title: 'Error!',
                    text: "Delivery Schedule Quantity cannot exceed item quantity",
                    icon: 'error',
                });
                return;
            }

            if (deliveryQty && deliverySchedule) //All fields filled
            {

                const previousHiddenFields = document.getElementsByClassName('delivery_schedule_qties_hidden_' + ItemRowIndexVal);

                addDeliveryHiddenInput(ItemRowIndexVal, previousHiddenFields.length ? previousHiddenFields.length : 0);


            }
        }

        function addDeliveryHiddenInput(itemRow, deliveryIndex)
        {
            addHiddenInput("item_delivery_schedule_qty_" + itemRow + "_" + deliveryIndex, document.getElementById('new_item_delivery_qty_input').value, `item_delivery_schedule_qty[${itemRow}][${deliveryIndex}]`, 'delivery_schedule_qties_hidden_' + itemRow, "item_row_" + itemRow);
            addHiddenInput("item_delivery_schedule_date_" + itemRow + "_" + deliveryIndex, document.getElementById('new_item_delivery_date_input').value, `item_delivery_schedule_date[${itemRow}][${deliveryIndex}]`, 'delivery_schedule_dates_hidden_' + itemRow, "item_row_" + itemRow);
            if (deliveryIndex == 0) {
                $("#delivery_date_" + itemRow).val(document.getElementById('new_item_delivery_date_input').value);
            }
            addDeliveryScheduleInTable(itemRow);
        }

        function addDeliveryScheduleInTable(itemRowIndex)
        {
                const previousHiddenQtyFields = document.getElementsByClassName('delivery_schedule_qties_hidden_' + itemRowIndex);
                const previousHiddenDateFields = document.getElementsByClassName('delivery_schedule_dates_hidden_' + itemRowIndex);

                const newIndex = previousHiddenQtyFields.length ? previousHiddenQtyFields.length : 0;
                var totalScheduleQty = parseFloat(document.getElementById('item_delivery_qty').textContent ? document.getElementById('item_delivery_qty').textContent : 0);

                var newData = ``;
                for (let index = newIndex- 1; index < previousHiddenQtyFields.length; index++) {
                    const newHTML = document.getElementById('delivery_schedule_main_table').insertRow(index + 2);
                    newHTML.className = "item_deliveries";
                    newHTML.id = "item_delivery_schedule_modal_" + newIndex;
                    newData = `
                        <td>${index+1}</td>
                        <td>${previousHiddenQtyFields[index].value}</td>
                        <td>${moment(previousHiddenDateFields[index].value).format('D/M/Y')}</td>
                        <td>
                            <a href="#" class="text-danger" onclick = "removeDeliverySchedule(${newIndex - 1}, ${itemRowIndex});"><i data-feather="trash-2"></i></a>
                        </td>
                    `;
                    newHTML.innerHTML = newData;
                    totalScheduleQty += parseFloat(previousHiddenQtyFields[index].value ? previousHiddenQtyFields[index].value : 0);
                }

                document.getElementById('new_item_delivery_date_input').value = "{{Carbon\Carbon::now() -> format('Y-m-d')}}";
                document.getElementById('new_item_delivery_qty_input').value = "";
                document.getElementById('item_delivery_qty').innerText = totalScheduleQty.toFixed(2);
                renderIcons();
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
            // const form = document.getElementById('sale_order_form');
            // const formData = new FormData(form);

            // // Append a new key-value pair to the form data
            // const items = document.getElementsByClassName('comp_item_code');
            // for (let index = 0; index < items.length; index++) {
            //     formData.append(`item_attributes[${index}]`, items[index].getAttribute('attribute-array'));
            // }
            // formData.append('_token', "{{csrf_token()}}");
            // formData.append('document_status', status);

            // Submit the form using Fetch API or XMLHttpRequest
            // fetch('/sales-order/store', {
            //     method: 'POST',
            //     body: formData
            // })
            // .then(response => response.json())
            // .then(data => {
            //     if (data.redirect_url) {
            //         Swal.fire({
            //         title: 'Success!',
            //         text: data.message,
            //         icon: 'success',
            //     });
            //         window.location.href = data.redirect_url;
            //     }
            // })
            // .catch(error => console.error(error));
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
                            type:'sale_module_items',
                            customer_id : $("#customer_id_input").val(),
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
                        uomInnerHTML += `<option value = '${ui.item.uom.id}'>${ui.item.uom.alias}</option>`;
                    }
                    if (ui.item.alternateUoms && ui.item.alternateUoms.length > 0) {
                        var selected = false;
                        ui.item.alternateUoms.forEach((saleUom) => {
                            if (saleUom.is_selling) {
                                selected = true;
                            }
                            uomInnerHTML += `<option value = '${saleUom.uom?.id}' ${selected == true ? "selected" : ""}>${saleUom.uom?.alias}</option>`;
                        });
                    }
                    uomDropdown.innerHTML = uomInnerHTML;
                    document.getElementById('')

                    itemOnChange(selector, index, '/item/attributes/');
                    getItemTax(index);
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
    initializeAutocomplete1("items_dropdown_0", 0);


    function initializeAutocompleteCustomer(selector) {
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
                                    code: item.customer_code || '',
                                    item_id: item.id,
                                    payment_terms_id : item?.payment_terms?.id,
                                    payment_terms : item?.payment_terms?.name,
                                    payment_terms_code : item?.payment_terms?.name,
                                    currency_id : item?.currency?.id,
                                    currency : item?.currency?.name,
                                    currency_code : item?.currency?.short_name,
                                    type : item?.customer_type,
                                    phone_no : item?.mobile,
                                    email : item?.email,
                                    gstin : item?.compliances?.gstin_no,
                                    credit_days : item?.credit_days,
                                    credit_days_editable : item?.credit_days_editable

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
                    var paymentTermsId = ui.item.payment_terms_id;
                    var paymentTerms = ui.item.payment_terms;
                    var paymentTermsCode = ui.item.payment_terms_code;
                    var currencyId = ui.item.currency_id;
                    var currency = ui.item.currency;
                    var currencyCode = ui.item.currency_code;
                    var customerType = ui.item.type;
                    var phoneNo = ui.item.phone_no;
                    var email = ui.item.email;
                    var gstIn = ui.item.gstin;
                    var creditDays = ui.item.credit_days ? ui.item.credit_days : 0;
                    var creditDaysEdit = ui.item.credit_days_editable;
                    $input.attr('customer_type', customerType);
                    $input.attr('phone_no', phoneNo);
                    $input.attr('email', email);
                    $input.attr('gstin', gstIn);
                    $input.attr('payment_terms_id', paymentTermsId);
                    $input.attr('payment_terms', paymentTerms);
                    $input.attr('payment_terms_code', paymentTermsCode);
                    $input.attr('currency_id', currencyId);
                    $input.attr('currency', currency);
                    $input.attr('currency_code', currencyCode);
                    //Set Credit Days
                    $("#credit_days_input").val(creditDays);
                    if (creditDaysEdit) {
                        $("#credit_days_input").removeAttr("readonly");
                    } else {
                        $("#credit_days_input").attr("readonly", true);
                    }
                    $input.val(ui.item.label);
                    $("#customer_code_input_hidden").val(ui.item.code);
                    document.getElementById('customer_id_input').value = ui.item.id;
                    onChangeCustomer(selector);
                    if (customerType === 'Cash') {
                        initializeCashCustomerPhoneDropdown();
                    } else {
                        deInitializeCashCustomerFlow();
                    }
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#customer_code_input_hidden").val("");
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
    }

    initializeAutocompleteCustomer('customer_code_input');

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

        const editBillButton = document.getElementById('billAddressEditBtn');
        if (editBillButton && itemsPresent) {
            editBillButton.style.display = "none"
        }
        const editShipButton = document.getElementById('shipAddressEditBtn');
        if (editShipButton && itemsPresent) {
            editShipButton.style.display = "none";
        }
        const selectButton = document.getElementById('select_qt_button');
        if (selectButton && itemsPresent) {
            selectButton.disabled = true;
        }
        const selectPoButton = document.getElementById('select_po_button');
        if (selectPoButton && itemsPresent) {
            selectPoButton.disabled = true;
        }
        const selectJoButton = document.getElementById('select_jo_button');
        if (selectJoButton && itemsPresent) {
            selectJoButton.disabled = true;
        }
        const custCodeInput = document.getElementById('customer_code_input');
        if (custCodeInput.value && itemsPresent) {
            custCodeInput.disabled = true;
        } else {
            custCodeInput.disabled = false;
        }

    }



    function enableHeader()
    {
        const disabledFields = document.getElementsByClassName('disable_on_edit');
            for (let disabledIndex = 0; disabledIndex < disabledFields.length; disabledIndex++) {
                disabledFields[disabledIndex].disabled = false;
            }
        const editBillButton = document.getElementById('billAddressEditBtn');
        if (editBillButton) {
            editBillButton.style.display = "block"
        }
        const editShipButton = document.getElementById('shipAddressEditBtn');
        if (editShipButton) {
            editShipButton.style.display = "block";
        }
        const selectButton = document.getElementById('select_qt_button');
        if (selectButton) {
            selectButton.disabled = false;
        }
        const selectPoButton = document.getElementById('select_po_button');
        if (selectPoButton) {
            selectPoButton.disabled = false;
        }
        const selectJoButton = document.getElementById('select_jo_button');
        if (selectJoButton) {
            selectJoButton.disabled = false;
        }
        document.getElementById('customer_code_input').disabled = false;
    }

    //Function to set values for edit form
    function editScript()
    {
        localStorage.setItem('deletedItemDiscTedIds', JSON.stringify([]));
        localStorage.setItem('deletedHeaderDiscTedIds', JSON.stringify([]));
        localStorage.setItem('deletedHeaderExpTedIds', JSON.stringify([]));
        localStorage.setItem('deletedSoItemIds', JSON.stringify([]));
        localStorage.setItem('deletedDelivery', JSON.stringify([]));
        localStorage.setItem('deletedAttachmentIds', JSON.stringify([]));

        const order = @json(isset($order) ? $order : null);
        if (order) {

            //Item Discount
            order.items.forEach((item, itemIndex) => {
                const totalValue = item.item_discount_amount;
                document.getElementById('discount_main_table').setAttribute('total-value', totalValue);
                document.getElementById('discount_main_table').setAttribute('item-row', 'item_value_' + itemIndex);
                document.getElementById('discount_main_table').setAttribute('item-row-index', itemIndex);

                item.discount_ted.forEach((ted, tedIndex) => {
                    addHiddenInput("item_discount_name_" + itemIndex + "_" + tedIndex, ted.ted_name, `item_discount_name[${itemIndex}][${tedIndex}]`, 'discount_names_hidden_' + itemIndex, 'item_value_' + itemIndex, ted.id);
                    addHiddenInput("item_discount_master_id_" + itemIndex + "_" + tedIndex, ted.ted_name, `item_discount_master_id[${itemIndex}][${tedIndex}]`, 'discount_names_hidden_' + itemIndex, 'item_value_' + itemIndex, ted.id);
                    addHiddenInput("item_discount_percentage_" + itemIndex + "_" + tedIndex, ted.ted_percentage ? ted.ted_percentage : '', `item_discount_percentage[${itemIndex}][${tedIndex}]`, 'discount_percentages_hidden_' + itemIndex,  'item_value_' + itemIndex, ted.id);
                    addHiddenInput("item_discount_value_" + itemIndex + "_" + tedIndex, ted.ted_amount, `item_discount_value[${itemIndex}][${tedIndex}]`, 'discount_values_hidden_' + itemIndex, 'item_value_' + itemIndex, ted.id);
                    addHiddenInput("item_discount_id_" + itemIndex + "_" + tedIndex, ted.id, `item_discount_id[${itemIndex}][${tedIndex}]`, 'discount_ids_hidden_' + itemIndex, 'item_value_' + itemIndex);
                });
                //Item Delivery Schedule
                item.item_deliveries.forEach((delivery, deliveryIndex) => {
                    addHiddenInput("item_delivery_schedule_qty_" + itemIndex + "_" + deliveryIndex, delivery.qty, `item_delivery_schedule_qty[${itemIndex}][${deliveryIndex}]`, 'delivery_schedule_qties_hidden_' + itemIndex, "item_row_" + itemIndex, delivery.id);
                    addHiddenInput("item_delivery_schedule_date_" + itemIndex + "_" + deliveryIndex, delivery.delivery_date, `item_delivery_schedule_date[${itemIndex}][${deliveryIndex}]`, 'delivery_schedule_dates_hidden_' + itemIndex, "item_row_" + itemIndex, delivery.id);
                    addHiddenInput("item_delivery_schedule_id_" + itemIndex + "_" + deliveryIndex, delivery.id, `item_delivery_schedule_id[${itemIndex}][${deliveryIndex}]`, 'delivery_schedule_ids_hidden_' + itemIndex, "item_row_" + itemIndex);
                });
                itemUomsHTML = ``;
                if (item.item.uom && item.item.uom.id) {
                    itemUomsHTML += `<option value = '${item.item.uom.id}' ${item.item.uom.id == item.uom_id ? "selected" : ""}>${item.item.uom.alias}</option>`;
                }
                item.item.alternate_uoms.forEach(singleUom => {
                    itemUomsHTML += `<option value = '${singleUom.uom.id}' ${singleUom.uom.id == item.uom_id ? "selected" : ""} >${singleUom.uom?.alias}</option>`;
                });
                document.getElementById('uom_dropdown_' + itemIndex).innerHTML = itemUomsHTML;
                getItemTax(itemIndex);
                const currentDocId = document.getElementById('dynamic_bom_div_' + itemIndex);
                currentDocId.style.removeProperty('display');
                if (item.custom_bom_details && item.custom_bom_details.length > 0) { // Custom Bom is set
                    let bomDetailAttribute = [];
                    item.custom_bom_details.forEach((bomDetail) => {
                        bomDetailAttribute.push({
                            id : bomDetail.id,
                            bom_detail_id : bomDetail.bom_detail_id,
                            item_code : bomDetail.item_code,
                            item_id : bomDetail.item_id,
                            item_name : bomDetail.item_name,
                            uom_id : bomDetail.uom_id,
                            uom_name : bomDetail.uom_name,
                            qty : bomDetail.qty,
                            station_id : bomDetail.station_id,
                            station_name : bomDetail.station_name,
                            bom_attributes : (bomDetail.item_attributes),
                            remark : bomDetail.remark ? bomDetail.remark : ""
                        });
                    });
                    currentDocId.setAttribute('bom_details', JSON.stringify(bomDetailAttribute));
                }
                if (itemIndex==0){
                    onItemClick(itemIndex);
                }
                setAttributesUI(itemIndex);
            });
            //Order Discount
            order.discount_ted.forEach((orderDiscount, orderDiscountIndex) => {
                document.getElementById('new_order_discount_name').value = orderDiscount.ted_name;
                document.getElementById('new_order_discount_id').value = orderDiscount.ted_id;
                document.getElementById('new_order_discount_percentage').value = (orderDiscount.ted_percentage ? orderDiscount.ted_percentage : '');
                document.getElementById('new_order_discount_value').value = parseFloat(orderDiscount.ted_amount);
                addOrderDiscount(orderDiscount.id, false);
            });
            //Order Expense
            order.expense_ted.forEach((orderExpense, orderExpenseIndex) => {
                document.getElementById('order_expense_name').value = orderExpense.ted_name;
                document.getElementById('order_expense_id').value = orderExpense.ted_id;
                document.getElementById('order_expense_percentage').value = orderExpense.ted_percentage ? orderExpense.ted_percentage : '';
                document.getElementById('order_expense_value').value = parseFloat(orderExpense.ted_amount);
                addOrderExpense(orderExpense.id, false);
            });
            setAllTotalFields();
            //Disable header fields which cannot be changed
            disableHeader();
            //Set all documents
            order.media_files.forEach((mediaFile, mediaIndex) => {
                appendFilePreviews(mediaFile.file_url, 'main_order_file_preview', mediaIndex, mediaFile.id, order.document_status == 'draft' ? false : true);
            });
        }

        renderIcons();

        let finalAmendSubmitButton = document.getElementById("amend-submit-button");

        viewModeScript(finalAmendSubmitButton ? false : true);
    }

    function reCheckEditScript()
    {
        const currentOrder = @json(isset($order) ? $order : null);
        if (currentOrder) {
            currentOrder.items.forEach((item, index) => {
                document.getElementById('item_checkbox_' + index).disabled = item.is_editable ? false : true;
                document.getElementById('items_dropdown_' + index).readonly = item.is_editable ? false : true;
                let currentAttributeButton = document.getElementById('attribute_button_' + index);
                if (currentAttributeButton) {
                    currentAttributeButton.disabled = item.is_editable ? false : true;
                }
            });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const order = @json(isset($order) ? $order : null);
        // if (order == null) {
            // getDocNumberByBookId(document.getElementById('series_id_input'), order ? false : true);
            onSeriesChange(document.getElementById('service_id_input'), order ? false : true);
        // }
    });

    function resetParametersDependentElements(reset = true)
    {
        var selectionSection = document.getElementById('selection_section');
        if (selectionSection) {
            selectionSection.style.display = "none";
        }
        document.getElementById('add_item_section').style.display = "none";
        document.getElementById('import_item_section').style.display = "none";
        document.getElementById('copy_item_section').style.display = "none";
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
                  if (reset) {
                      implementBookDynamicFields(data.data.dynamic_fields_html, data.data.dynamic_fields);
                  }
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
                    if (selectSingleVal == 'sq') {
                        var selectionSectionElement = document.getElementById('selection_section');
                        if (selectionSectionElement) {
                            selectionSectionElement.style.display = "";
                        }
                    }
                    if (selectSingleVal == 'po') {
                        var selectionSectionElement = document.getElementById('selection_section');
                        if (selectionSectionElement) {
                            selectionSectionElement.style.display = "";
                        }
                    }
                    if (selectSingleVal == 'd') {
                        document.getElementById('add_item_section').style.display = "";
                        document.getElementById('copy_item_section').style.display = "";
                        document.getElementById('import_item_section').style.display = "";
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

        if (bookId && bookCode && documentDate) {
            const selectButton = document.getElementById('select_qt_button');
            if (selectButton) {
                selectButton.disabled = false;
            };
            const selectPoButton = document.getElementById('select_po_button');
            if (selectPoButton) {
                selectPoButton.disabled = false;
            }
            const selectJoButton = document.getElementById('select_jo_button');
            if (selectJoButton) {
                selectJoButton.disabled = false;
            };
            if (!document.getElementById('customer_code_input').value) {
                document.getElementById('customer_code_input').disabled = false;
            }
        } else {
            const selectButton = document.getElementById('select_qt_button');
            if (selectButton) {
                selectButton.disabled = true;
            };
            const selectPoButton = document.getElementById('select_po_button');
            if (selectPoButton) {
                selectPoButton.disabled = true;
            };
            const selectJoButton = document.getElementById('select_jo_button');
            if (selectJoButton) {
                selectJoButton.disabled = true;
            };
            document.getElementById('customer_code_input').disabled = true;

        }
    }

    editScript();

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
                })
        $("#edit-address-billing").modal('show');
    });

    $(document).on('click','#shipAddressEditBtn',(e) => {
                const addressId = document.getElementById('current_shipping_address_id').value;
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
                        $('#shipping_country_id_input').val(data.address.country_id).trigger('change');
                        $("#current_shipping_address_id").val(data.address.id);
                        $("#current_shipping_country_id").val(data.address.country_id);
                        $("#current_shipping_state_id").val(data.address.state_id);
                        $("#current_shipping_address").text(data.address.display_address);
                        setTimeout(() => {

                            $('#shipping_state_id_input').val(data.address.state_id).trigger('change');

                            setTimeout(() => {

                                $('#shipping_city_id_input').val(data.address.city_id).trigger('change');
                            }, 1000);
                        }, 1000);
                        $('#shipping_pincode_input').val(data.address.pincode)
                        $('#shipping_address_input').val(data.address.address);

                    }

                }).catch(error => {
                    console.log("Error : ", error);
                })
        $("#edit-address-shipping").modal('show');
    });

    function checkItemAddValidation()
    {
        let addRow = $('#series_id_input').val &&  $('#order_no_input').val && $('#order_date_input').val && $('#customer_code_input').val;
        return addRow;
    }

    var taxInputs = [];

    function getItemTax(itemIndex)
    {
        const itemId = document.getElementById(`items_dropdown_${itemIndex}_value`).value;
        const itemQty = document.getElementById('item_qty_' + itemIndex).value;
        const itemValue = document.getElementById('item_value_' + itemIndex).value;
        const discountAmount = document.getElementById('item_discount_' + itemIndex).value;
        const headerDiscountAmount = document.getElementById('header_discount_' + itemIndex).value;
        const totalItemDiscount = parseFloat(discountAmount ? discountAmount : 0) + parseFloat(headerDiscountAmount ? headerDiscountAmount : 0);

        // const totalItemDiscount = parseFloat(discountAmount ? discountAmount : 0);
        const billToCountryId = $("#current_billing_country_id").val();
        const billToStateId = $("#current_billing_state_id").val();
        let itemPrice = 0;
        if (itemQty > 0) {
            itemPrice = (parseFloat(itemValue ? itemValue : 0) + parseFloat(totalItemDiscount ? totalItemDiscount : 0)) / parseFloat(itemQty);
        }
        $.ajax({
                        url: "{{route('tax.calculate.sales', ['alias' => 'so'])}}",
                        method: 'GET',
                        dataType: 'json',
                        data : {
                            item_id : itemId,
                            price : itemPrice,
                            transaction_type : 'sale',
                            party_country_id : billToCountryId,
                            party_state_id : billToStateId,
                            customer_id : $("#customer_id_input").val(),
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
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });

    }

    function itemRowCalculation(itemRowIndex)
    {
        const itemQtyInput = document.getElementById('item_qty_' + itemRowIndex);
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

    // function changeAllItemsTotal() //All items total
    // {
    //     const itemTotalInputs = document.getElementsByClassName('item_totals_input');
    //     let itemTotal = 0;
    //     for (let index = 0; index < itemTotalInputs.length; index++) {
    //         itemTotal += parseFloat(itemTotalInputs[index].value ? itemTotalInputs[index].value : 0);
    //     }
    //     //Item Total
    //     const totalTextContainers = document.getElementsByClassName('all_tems_total_common');
    //     for (let indx = 0; indx < itemTotalInputs.length; indx++) {
    //         totalTextContainers.textContent = itemTotal.toFixed(2);
    //     }
    //     //Item value
    //     const itemTotalInputs = document.getElementsByClassName('item_totals_input');
    //     let itemTotal = 0;
    //     for (let index = 0; index < itemTotalInputs.length; index++) {
    //         itemTotal += parseFloat(itemTotalInputs[index].value ? itemTotalInputs[index].value : 0);
    //     }


    // }

    function onTaxClick(itemIndex)
    {
        const taxInput = document.getElementById('item_tax_' + itemIndex);
        const taxDetails = JSON.parse(taxInput.getAttribute('tax_details'));
        const taxTable = document.getElementById('tax_main_table');
        //Remove previous Taxes
        const oldTaxes = document.getElementsByClassName('item_taxes');
        if (oldTaxes && oldTaxes.length > 0)
        {
            while (oldTaxes.length > 0) {
                oldTaxes[0].remove();
            }
        }
        //Add New Tax
        let newHtml = ``;
        taxDetails.forEach((element, index) => {
            let newDoc = taxTable.insertRow(index + 1);
            newDoc.id = "item_tax_modal_" + itemIndex;
            newDoc.className = "item_taxes";
            newHtml = `
                <td>${index+1}</td>
                <td>${element.tax_name}</td>
                <td>${element.tax_percentage}</td>
                <td class = "dynamic_tax_val_${itemIndex}">${element.tax_value}</td>
            `;
            newDoc.innerHTML = newHtml;
        });

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
    function processQuotation(docType = "sq")
    {
        const allCheckBoxes = document.getElementsByClassName('po_checkbox');
        let docId = [];
        let soItemsId = [];
        for (let index = 0; index < allCheckBoxes.length; index++) {
            if (allCheckBoxes[index].checked) {
                docId.push(allCheckBoxes[index].getAttribute('document-id'));
                soItemsId.push(allCheckBoxes[index].getAttribute('so-item-id'));
            }
        }
        if (docId && soItemsId.length > 0) {
            $.ajax({
                url: "{{route('sale.order.quotation.get')}}",
                method: 'GET',
                dataType: 'json',
                data: {
                    quotation_id : docId,
                    items_id: soItemsId,
                    doc_type: docType
                },
                success: function(data) {
                    const currentOrders = data.data;
                    let currentOrderIndexVal = 0;
                    currentOrders.forEach(currentOrder => {
                        if (currentOrder) { //Set all data
                        //Disable Header
                        //Basic Details
                        $("#customer_code_input").val(currentOrder.customer_code);
                        $("#customer_id_input").val(currentOrder.customer_id);
                        $("#customer_code_input_hidden").val(currentOrder.customer_code);
                        $("#consignee_name_input").val(currentOrder.consignee_name);
                        $("#customer_phone_no_input").val(currentOrder.customer_phone_no);
                        $("#customer_email_input").val(currentOrder.customer_email);
                        $("#customer_gstin_input").val(currentOrder.customer_gstin);
                        //First add options also
                        $("#currency_dropdown").empty(); // Clear existing options
                        $("#currency_dropdown").append(new Option(
                            currentOrder.customer ? currentOrder.customer.currency?.name || 'Default Currency Name' : 'Default Currency Name',
                            currentOrder.currency_id || 0
                        ));
                        $("#currency_code_input").val(currentOrder.currency_code);
                        //First add options also
                        $("#payment_terms_dropdown").empty(); // Clear existing options
                        $("#payment_terms_dropdown").append(new Option(
                            currentOrder.customer ? currentOrder.customer.payment_terms?.name || 'Default Payment Terms' : 'Default Payment Name',
                            currentOrder.payment_term_id || 0
                        ));
                        $("#payment_terms_code_input").val(currentOrder.payment_term_code);
                        //Address
                        $("#current_billing_address").text(currentOrder.billing_address_details?.display_address);
                        $("#current_shipping_address").text(currentOrder.shipping_address_details?.display_address);
                        $("#current_shipping_country_id").val(currentOrder.shipping_address_details?.country_id);
                        $("#current_billing_country_id").val(currentOrder.billing_address_details?.country_id);
                        $("#current_shipping_state_id").val(currentOrder.shipping_address_details?.state_id);
                        $("#current_billing_state_id").val(currentOrder.billing_address_details?.state_id);
                        //ID
                        $("#current_shipping_address_id").val(currentOrder.shipping_address_details?.id);
                        $("#current_billing_address_id").val(currentOrder.billing_address_details?.id);
                        //Main IDs
                        var newOptionBilling = new Option(currentOrder.billing_address_details?.address, currentOrder.billing_address_details?.id, false, false);
                        $('#billing_address_dropdown').append(newOptionBilling);
                        $("#billing_address_dropdown").val(currentOrder.billing_address_details?.id);

                        var newOptionShipping = new Option(currentOrder.shipping_address_details?.address, currentOrder.shipping_address_details?.id, false, false);
                        $('#shipping_address_dropdown').append(newOptionShipping);
                        $("#shipping_address_dropdown").val(currentOrder.shipping_address_details?.id);

                        const locationElement = document.getElementById('store_id_input');
                            if (locationElement) {
                                const displayAddress = locationElement.options[locationElement.selectedIndex].getAttribute('display-address');
                                $("#current_pickup_address").text(displayAddress);
                            }

                        const mainTableItem = document.getElementById('item_header');
                        let fullLock = false;
                        if (docType === 'jo' || docType === 'po')
                        {
                            fullLock = true;
                        }

                        currentOrder.items.forEach((item, itemIndex) => {
                            const itemRemarks = item.remarks ? item.remarks : '';
                            var discountAmtPrev = 0;
                            item.discount_ted.forEach((ted, tedIndex) => {

                                var percentage = ted.ted_percentage;
                                var itemValue = (item.rate * item.balance_qty).toFixed(2);
                                if (!percentage) {
                                    percentage = ted.ted_amount/(ted.assessment_amount ? ted.assessment_amount : itemValue) * 100;
                                }
                                var itemDiscountValuePrev = ((itemValue * percentage)/100).toFixed(2);
                                discountAmtPrev += parseFloat(itemDiscountValuePrev ? itemDiscountValuePrev : 0);
                            });

                            let pullUiTag = `<input type = "hidden" id = "qt_id_${currentOrderIndexVal}" value = "${item?.id}" name = "quotation_item_ids[]"/>`;
                            if (docType == "po") {
                                pullUiTag = `<input type = "hidden" id = "qt_id_${currentOrderIndexVal}" value = "${item?.id}" name = "po_item_ids[]"/>`;
                            } else if (docType == 'jo') {
                                pullUiTag = `<input type = "hidden" id = "qt_id_${currentOrderIndexVal}" value = "${item?.id}" name = "jo_product_ids[]"/>`;
                            }
                            mainTableItem.innerHTML += `
                            <tr id = "item_row_${currentOrderIndexVal}" class = "item_header_rows" onclick = "onItemClick('${currentOrderIndexVal}');" data-index = "${currentOrderIndexVal}">
                                <td class="customernewsection-form">
                                   <div class="form-check form-check-primary custom-checkbox">
                                       <input type="checkbox" class="form-check-input item_row_checks" id="item_row_check_${currentOrderIndexVal}" del-index = "${currentOrderIndexVal}" ${fullLock ? 'disabled' : ''}>
                                       <label class="form-check-label" for="item_row_check_${currentOrderIndexVal}"></label>
                                   </div>
                                </td>
                                <td class="poprod-decpt">
                                    ${pullUiTag}
                                    <input type = "hidden" id = "qt_book_id_${currentOrderIndexVal}" value = "${currentOrder?.book_id}" />
                                    <input type = "hidden" id = "qt_book_code_${currentOrderIndexVal}" value = "${currentOrder?.book_code}" />

                                    <input type = "hidden" id = "qt_document_no_${currentOrderIndexVal}" value = "${currentOrder?.document_number}" />
                                    <input type = "hidden" id = "qt_document_date_${currentOrderIndexVal}" value = "${currentOrder?.document_date}" />

                                    <input type = "hidden" id = "qt_id_${currentOrderIndexVal}" value = "${currentOrder?.document_number}" />

                                   <input type="text" id = "items_dropdown_${currentOrderIndexVal}" name="item_code[${currentOrderIndexVal}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" autocomplete="off" data-name="${item?.item?.item_name}" data-code="${item?.item?.item_code}" data-id="${item?.item?.id}" hsn_code = "${item?.item?.hsn?.code}" item-name = "${item?.item?.item_name}" specs = '${JSON.stringify(item?.item?.specifications)}' attribute-array = '${JSON.stringify(item?.item_attributes_array)}'  value = "${item?.item?.item_code}" readonly>
                                   <input type = "hidden" name = "item_id[]" id = "items_dropdown_${currentOrderIndexVal}_value" value = "${item?.item_id}"></input>
                                                                        </td>
                                                                        <td class="poprod-decpt">
                                                                            <input type="text" id = "items_name_${currentOrderIndexVal}" class="form-control mw-100" name = "item_name[${currentOrderIndexVal}]" value = "${item?.item?.item_name}" readonly>
                                                                        </td>
                                                                        <td class="poprod-decpt" id = "attribute_section_${currentOrderIndexVal}">
                                   <button id = "attribute_button_${currentOrderIndexVal}" type = "button" data-bs-toggle="modal" onclick = "setItemAttributes('items_dropdown_${currentOrderIndexVal}', '${currentOrderIndexVal}', true);" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px" >Attributes</button>
                                   <input type = "hidden" name = "attribute_value_${currentOrderIndexVal}" />
                                </td>

                                                                        <td>
                                   <select class="form-select" name = "uom_id[]" id = "uom_dropdown_${currentOrderIndexVal}">

                                   </select>
                                    </td>
                                    <td><input type="text" data-index = '${currentOrderIndexVal}' id = "item_qty_${currentOrderIndexVal}" name = "item_qty[${currentOrderIndexVal}]" oninput = "changeItemQty(this, '${currentOrderIndexVal}');" onchange = "itemQtyChange(this, '${currentOrderIndexVal}')" value = "${item?.quotation_balance_qty}" class="form-control mw-100 text-end item_qty_input" onblur = "setFormattedNumericValue(this);" max = "${item?.quotation_balance_qty}" ${fullLock ? 'readonly' : ''} /></td>
                                    <td><input type="text" id = "item_rate_${currentOrderIndexVal}" onkeydown = "openDeliveryScheduleFromTab(${currentOrderIndexVal})" name = "item_rate[${currentOrderIndexVal}]" oninput = "changeItemRate(this, '${currentOrderIndexVal}');" value = "${item?.rate}" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" ${fullLock ? 'readonly' : ''} /></td>
                                    <td><input type="text" id = "item_value_${currentOrderIndexVal}" disabled class="form-control mw-100 text-end item_values_input" value = "${(item?.quotation_balance_qty ? item?.quotation_balance_qty : 0) * (item?.rate ? item?.rate : 0)}" /></td>
                                    <input type = "hidden" id = "header_discount_${currentOrderIndexVal}" value = "${item?.header_discount_amount}" ></input>
                                    <input type = "hidden" id = "header_expense_${currentOrderIndexVal}" value = "${item?.header_expense_amount}"></input>
                                <td>
                                   <div class="position-relative d-flex align-items-center">
                                       <input type="text" id = "item_discount_${currentOrderIndexVal}" disabled class="form-control mw-100 text-end item_discounts_input" style="width: 70px" value = "${discountAmtPrev}"/>
                                       <div class="ms-50">
                                           <button ${fullLock ? 'disabled' : ''} type = "button" onclick = "onDiscountClick('item_value_${currentOrderIndexVal}', '${currentOrderIndexVal}')" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Add</button>
                                       </div>
                                   </div>
                                </td>
                                    <input type="hidden" id = "item_tax_${currentOrderIndexVal}" value = "${item?.tax_amount}" class="form-control mw-100 text-end item_taxes_input" style="width: 70px" />
                                    <td><input type="text" id = "value_after_discount_${currentOrderIndexVal}" value = "${(item?.quotation_balance_qty * item?.rate) - item?.item_discount_amount}" disabled class="form-control mw-100 text-end item_val_after_discounts_input" /></td>
                                    <td style = "{{request() -> type === 'so' ? '' : 'display:none;'}}"><input type="date" id = "delivery_date_${currentOrderIndexVal}" name = "delivery_date[${currentOrderIndexVal}]" class="form-control mw-100" value = "{{Carbon\Carbon::now() -> format('Y-m-d')}}"/></td>
                                    <input type = "hidden" id = "value_after_header_discount_${currentOrderIndexVal}" class = "item_val_after_header_discounts_input" value = "${(item?.quotation_balance_qty * item?.rate) - item?.item_discount_amount - item?.header_discount_amount}" ></input>
                                    <input type="hidden" id = "item_total_${currentOrderIndexVal}" value = "${(item?.quotation_balance_qty * item?.rate) - item?.item_discount_amount - item?.header_discount_amount + (item?.tax_amount)}" disabled class="form-control mw-100 text-end item_totals_input" />
                                <td>
                                    <div class="d-flex">
                                        @if(request() -> type === 'so')
                                            <div class="me-50 cursor-pointer" onclick = "openDeliverySchedule('${currentOrderIndexVal}');">    <span data-bs-toggle="tooltip" data-bs-placement="top" title="Delivery Schedule" class="text-primary"><i data-feather="calendar"></i></span></div>
                                            <div class="me-50 cursor-pointer dynamic_bom_div" id = "dynamic_bom_div_${currentOrderIndexVal}" onclick = "getCustomizableBOM(${currentOrderIndexVal})" style = "display:none;"> <span data-bs-toggle="tooltip" data-bs-placement="top" title="BOM" class="text-primary"><i data-feather="table"></i></span></div>
                                        @endif
                                        <div class="me-50 cursor-pointer" onclick = "setViewDetailedStocks('${currentOrderIndexVal}');"> <span data-bs-toggle="tooltip" data-bs-placement="top" title="Stocks" class="text-primary"><i data-feather="layers"></i></span></div>
                                        <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick = "setItemRemarks('item_remarks_${currentOrderIndexVal}');">        <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span></div>
                                   </div>
                                </td>
                                <input type="hidden" id = "item_remarks_${currentOrderIndexVal}" name = "item_remarks[]" value = "${itemRemarks}"/>

                                                                      </tr>
                            `;
                            initializeAutocomplete1("items_dropdown_" + currentOrderIndexVal, currentOrderIndexVal);
                            renderIcons();
                            const totalValue = item.item_discount_amount;
                            document.getElementById('discount_main_table').setAttribute('total-value', totalValue);
                            document.getElementById('discount_main_table').setAttribute('item-row', 'item_value_' + currentOrderIndexVal);
                            document.getElementById('discount_main_table').setAttribute('item-row-index', currentOrderIndexVal);

                            item.discount_ted.forEach((ted, tedIndex) => {
                                    addHiddenInput("item_discount_name_" + currentOrderIndexVal + "_" + tedIndex, ted.ted_name, `item_discount_name[${currentOrderIndexVal}][${tedIndex}]`, 'discount_names_hidden_' + currentOrderIndexVal, 'item_row_' + currentOrderIndexVal);
                                    var percentage = ted.ted_percentage;
                                    var itemValue = document.getElementById('item_value_' + currentOrderIndexVal).value;
                                    if (!percentage) {
                                        percentage = ted.ted_amount/itemValue*100;
                                    }
                                    addHiddenInput("item_discount_percentage_" + currentOrderIndexVal + "_" + tedIndex, percentage, `item_discount_percentage[${currentOrderIndexVal}][${tedIndex}]`, 'discount_percentages_hidden_' + currentOrderIndexVal,  'item_row_' + currentOrderIndexVal);
                                    var itemDiscountValue = ((itemValue * percentage)/100).toFixed(2);
                                    addHiddenInput("item_discount_value_" + currentOrderIndexVal + "_" + tedIndex, itemDiscountValue, `item_discount_value[${currentOrderIndexVal}][${tedIndex}]`, 'discount_values_hidden_' + currentOrderIndexVal, 'item_row_' + currentOrderIndexVal);
                                });
                            //Item Delivery Schedule
                            if (docType == "sq") {
                                item.item_deliveries.forEach((delivery, deliveryIndex) => {
                                    addHiddenInput("item_delivery_schedule_qty_" + currentOrderIndexVal + "_" + deliveryIndex, delivery.qty, `item_delivery_schedule_qty[${currentOrderIndexVal}][${deliveryIndex}]`, 'delivery_schedule_qties_hidden_' + currentOrderIndexVal, "item_row_" + currentOrderIndexVal);
                                    addHiddenInput("item_delivery_schedule_date" + currentOrderIndexVal + "_" + deliveryIndex, delivery.delivery_date, `item_delivery_schedule_date[${currentOrderIndexVal}][${deliveryIndex}]`, 'delivery_schedule_dates_hidden_' + currentOrderIndexVal, "item_row_" + currentOrderIndexVal);
                                });
                            }
                            itemUomsHTML = ``;
                            if (item.item.uom && item.item.uom.id) {
                                if (item.item.uom.id == item.uom_id) {
                                    itemUomsHTML += `<option value = '${item.item.uom.id}' selected >${item.item.uom.alias}</option>`;
                                }
                            }
                            item.item.alternate_uoms.forEach(singleUom => {
                                if (singleUom.uom.id == item.uom_id) {
                                    itemUomsHTML += `<option value = '${singleUom.uom.id}' selected >${singleUom.uom?.alias}</option>`;
                                }
                            });
                            document.getElementById('uom_dropdown_' + currentOrderIndexVal).innerHTML = itemUomsHTML;
                            getItemTax(currentOrderIndexVal);
                            setAttributesUI(currentOrderIndexVal);
                            currentOrderIndexVal += 1;
                        });

                        //Order Discount
                        currentOrder.discount_ted.forEach((orderDiscount, orderDiscountIndex) => {
                            document.getElementById('new_order_discount_name').value = orderDiscount.ted_name;
                            document.getElementById('new_order_discount_id').value = orderDiscount.ted_id;
                            var currentOrderDiscountPercentage = orderDiscount.ted_percentage;
                            if (!currentOrderDiscountPercentage) {
                                currentOrderDiscountPercentage = orderDiscount.ted_amount/ orderDiscount.assessment_amount * 100;
                            }
                            document.getElementById('new_order_discount_percentage').value = currentOrderDiscountPercentage;
                            document.getElementById('new_order_discount_value').value = orderDiscount.ted_amount;
                            addOrderDiscount(null, false);
                        });
                        //Order Expense
                        currentOrder.expense_ted.forEach((orderExpense, orderExpenseIndex) => {
                            document.getElementById('order_expense_name').value = orderExpense.ted_name;
                            document.getElementById('order_expense_id').value = orderExpense.ted_id;
                            var currentOrderExpensePercentage = orderExpense.ted_percentage;
                            if (!currentOrderExpensePercentage) {
                                currentOrderExpensePercentage = orderExpense.ted_amount/ orderExpense.assessment_amount * 100;
                            }
                            document.getElementById('order_expense_percentage').value = currentOrderExpensePercentage;
                            document.getElementById('order_expense_value').value = orderExpense.ted_amount;
                            addOrderExpense(null, false);
                        });
                        setAllTotalFields();
                        disableHeader();
                        // changeDropdownOptions(document.getElementById('customer_id_input'), ['billing_address_dropdown','shipping_address_dropdown'], ['billing_addresses', 'shipping_addresses'], '/customer/addresses/', 'vendor_dependent');

                    }
                    let itemIdsDoc = document.getElementsByClassName('item_header_rows');
                    for (let index = 0; index < itemIdsDoc.length; index++) {
                        checkBomConditionRaw(itemIdsDoc[index].getAttribute('data-index'), false);
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
                text: 'Please select at least one one quotation',
                icon: 'error',
            });
        }
    }
    function getQuotations(type = 'sq')
    {
        var qtsHTML = ``;
        let header_book_id = $("#series_id_input").val();
        let targetTable = document.getElementById('qts_data_table');
        let customer_id = $("#customer_id_qt_val").val();
        let book_id = $("#book_id_qt_val").val();
        let document_id = $("#document_id_qt_val").val();
        let item_id = $("#item_id_qt_val").val();
        if (type == 'po') {
            targetTable = document.getElementById('qts_data_table_po');
            customer_id = $("#customer_id_po_val").val();
            book_id = $("#book_id_po_val").val();
            document_id = $("#document_id_po_val").val();
            item_id = $("#item_id_po_val").val();
        } else if (type == 'jo') {
            targetTable = document.getElementById('qts_data_table_jo');
            customer_id = $("#customer_id_jo_val").val();
            book_id = $("#book_id_jo_val").val();
            document_id = $("#document_id_jo_val").val();
            item_id = $("#item_id_jo_val").val();
        }
        $.ajax({
            url: "{{route('sale.order.quotation.get.all')}}",
            method: 'GET',
            dataType: 'json',
            data : {
                customer_id : customer_id,
                book_id : book_id,
                document_id : document_id,
                item_id : item_id,
                header_book_id : header_book_id,
                doc_type : type,
                order_type : $("#order_type_input").val()
            },
            success: function(data) {
                if (Array.isArray(data.data) && data.data.length > 0) {
                    data.data.forEach((qt, qtIndex) => {
                        var attributesHTML = ``;
                            qt.attributes.forEach(attribute => {
                                attributesHTML += `<span class="badge rounded-pill badge-light-primary" > ${attribute.header_attribute?.name} : ${attribute.header_attribute_value?.value} </span>`;
                            });
                        qtsHTML += `
                            <tr>
                                <td>
                                    <div class="form-check form-check-inline me-0">
                                        <input class="form-check-input po_checkbox" type="checkbox" name="po_check" id="po_checkbox_${qtIndex}" oninput = "checkQuotation(this);" document-id = "${qt.header.id}" doc-id = "${qt?.header.id}" current-doc-id = "0" so-item-id = "${qt.id}">
                                    </div>
                                </td>
                                <td>${qt?.header?.book_code}</td>
                                <td>${qt?.header?.document_number}</td>
                                <td>${moment(qt?.header?.document_date).format('D/M/Y')}</td>
                                <td>${qt?.customer?.currency?.short_name}</td>
                                <td>${qt?.customer?.company_name}</td>
                                <td>${qt?.item_code}</td>
                                <td>${attributesHTML}</td>
                                <td>${qt?.uom?.name}</td>
                                <td>${qt?.order_qty}</td>
                                <td>${qt?.inter_org_so_bal_qty}</td>
                                <td>${qt?.rate}</td>
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

    let pullType = "sq";

    function openQuotation(type = 'sq')
    {
        if (type == 'sq') {
            pullType = "sq";
            initializeAutocompleteQt("customer_code_input_qt", "customer_id_qt_val", "customer", "customer_code", "company_name");
            initializeAutocompleteQt("book_code_input_qt", "book_id_qt_val", "book_sq", "book_code", "");
            initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "sale_order_document_qt", "document_number", "");
            initializeAutocompleteQt("item_name_input_qt", "item_id_qt_val", "sale_module_items", "item_code", "item_name");
            getQuotations("sq");
        } else if (type == 'po') {
            pullType = "po";
            initializeAutocompleteQt("customer_code_input_po", "customer_id_po_val", "customer", "customer_code", "company_name");
            initializeAutocompleteQt("book_code_input_po", "book_id_po_val", "book_sq", "book_code", "");
            initializeAutocompleteQt("document_no_input_po", "document_id_po_val", "sale_order_document_po", "document_number", "");
            initializeAutocompleteQt("item_name_input_po", "item_id_po_val", "sale_module_items", "item_code", "item_name");
            getQuotations("po");
        } else {
            pullType = "jo";
            initializeAutocompleteQt("customer_code_input_jo", "customer_id_jo_val", "customer", "customer_code", "company_name");
            initializeAutocompleteQt("book_code_input_jo", "book_id_jo_val", "book_sq", "book_code", "");
            initializeAutocompleteQt("document_no_input_jo", "document_id_jo_val", "sale_order_document_jo", "document_number", "");
            initializeAutocompleteQt("item_name_input_jo", "item_id_jo_val", "sale_module_items", "item_code", "item_name");
            getQuotations("jo");
        }

    }

    let current_doc_id = 0;

    function checkQuotation(element)
    {
        // const docId = element.getAttribute('doc-id');
        // if (current_doc_id > 0) {
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

    function saveAddressShipping()
    {
        $.ajax({
            url: "{{route('sales_order.add.address')}}",
            method: 'POST',
            dataType: 'json',
            data: {
                type: 'shipping',
                country_id: $("#shipping_country_id_input").val(),
                state_id: $("#shipping_state_id_input").val(),
                city_id: $("#shipping_city_id_input").val(),
                address: $("#shipping_address_input").val(),
                pincode: $("#shipping_pincode_input").val(),
                phone: '',
                fax: '',
                customer_id : $("#customer_id_input").val()
            },
            success: function(data) {
                if (data && data.data) {
                    $("#edit-address-shipping").modal("hide");
                    $("#current_shipping_address_id").val(data.data.id);
                    $("#current_shipping_country_id").val(data.data.country_id);
                    $("#current_shipping_state_id").val(data.data.state_id);
                    $("#current_shipping_address").text(data.data.display_address);
                    // if (data.data?.display_address &&  data.data?.id) {
                    //     var newOption = new Option(data.data.display_address, data.data.id, false, false);
                    //     $('#shipping_address_dropdown').append(newOption).trigger('change');
                    //     $("#shipping_address_dropdown").val(data.data.id).trigger('change');
                    // }
                    $("#new_shipping_country_id").val(data.data.country_id);
                    $("#new_shipping_state_id ").val(data.data.state_id );
                    $("#new_shipping_city_id").val(data.data.city_id);
                    $("#new_shipping_type").val(data.data.type);
                    $("#new_shipping_pincode").val(data.data.pincode);
                    $("#new_shipping_phone").val(data.data.phone);

                }
            },
            error: function(xhr) {
                console.error('Error fetching customer data:', xhr.responseText);
            }
        });
    }

    function saveAddressBilling(type)
    {
        $.ajax({
            url: "{{route('sales_order.add.address')}}",
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
                customer_id : $("#customer_id_input").val()
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
                }
            },
            error: function(xhr) {
                console.error('Error fetching customer data:', xhr.responseText);
            }
        });
    }

    function onBillingAddressChange(element)
    {
        $("#current_billing_address_id").val(element.value);
        $('#billAddressEditBtn').click();
    }

    function onShippingAddressChange(element)
    {
        $("#current_shipping_address_id").val(element.value);
        $('#shipAddressEditBtn').click();
    }


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

    async function checkBomConditionRaw(itemIndex, openPopUp = true, openAttributes = false) {
                // let itemAttributes = JSON.parse(document.getElementById(`items_dropdown_${itemIndex}`).getAttribute('attribute-array'));
                // let selectedItemAttr = [];
                // if (itemAttributes && itemAttributes.length > 0) {
                //     itemAttributes.forEach(element => {
                //         var selectedAttrValue = null;
                //         element.values_data.forEach(subElement => {
                //             if (subElement.selected) {
                //                 selectedAttrValue = (subElement.id);
                //             }
                //         });
                //         selectedItemAttr.push({
                //             attribute_value : selectedAttrValue,
                //             attribute_id : element.id
                //         })
                //     });
                // }
                let bomContentDiv = document.getElementById('dynamic_bom_div_' + itemIndex);
                if (!bomContentDiv) {
                    return;
                }
                $.ajax({
                url: "{{route('sale.order.bom.check')}}",
                method: 'GET',
                dataType: 'json',
                data: {
                    item_id: document.getElementById('items_dropdown_'+ itemIndex + '_value').value,
                    item_attributes : [],
                },
                success: function(data) {
                    // if (data.data.customizable == "yes") {
                        bomContentDiv.style.removeProperty('display');
                        getCustomizableBOM(itemIndex, true, openPopUp);
                    // } else {
                    //     bomContentDiv.style.display = 'none';
                    // }
                    if (data.data.status == 'item_not_found' || data.data.status == 'bom_not_exists') {
                        document.getElementById('item_qty_' + itemIndex).value = 0;
                        itemRowCalculation(itemIndex);
                        Swal.fire({
                            title: 'Error!',
                            text: data.data.message,
                            icon: 'error',
                        });
                        return;
                    } else {
                        if (openAttributes) {
                            setItemAttributes('items_dropdown_' + itemIndex, itemIndex);
                        }
                    }
                    },
                    error: function(xhr) {
                        console.error('Error fetching customer data:', xhr.responseText);
                        itemRowCalculation(itemIndex);
                        getCustomizableBOM(itemIndex, true, openPopUp);
                    }
                });
            }

    const checkBomCondition = debounce(checkBomConditionRaw, 800);

    function assignBomConditions(index, openPopUp = false)
    {
        // checkBomCondition(index, openPopUp)
        // Usage: Pass parameters when invoking the debounced function
        document.querySelectorAll('.item_qty_input').forEach((input) => {
            input.addEventListener('input', function (event) {
                const id = event.target.getAttribute('data-index');
                checkBomCondition(id, openPopUp);
            });
        });
    }


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
                            console.error('Error fetching customer data:', xhr.responseText);
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

    function resetSeries()
    {
        document.getElementById('series_id_input').innerHTML = '';
    }

    function implementSeriesChange(val)
    {

    }


    function onSeriesChange(element, reset = true)
    {
        resetSeries();
        implementSeriesChange(element.value);
        $.ajax({
            url: "{{route('book.service-series.get')}}",
            method: 'GET',
            dataType: 'json',
            data: {
                menu_alias: "{{request() -> segments()[0]}}",
                service_alias: "so",
                book_id : "{{ isset($order) ? $order -> book_id : '' }}"
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
        const orderId = "{{isset($order) ? $order -> id : null}}";
        if (orderId) {
            $.ajax({
            url: "{{route('sale.order.revoke')}}",
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
                    window.location.href = "{{request() -> type == 'sq' ? route('sale.quotation.index') : route('sale.order.index')}}";
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

    function copyItemRow()
    {
        if (currentSelectedItemIndex == null) {
            Swal.fire({
                title: 'Warning!',
                text: 'No Item selected',
                icon: 'warning',
            });
            return;
        }
        const itemRowElement = document.getElementById('item_row_' + currentSelectedItemIndex);
        if (!itemRowElement) {
            Swal.fire({
                title: 'Warning!',
                text: 'No Item selected',
                icon: 'warning',
            });
            return;
        }
        const currentItemDropdown = document.getElementById('items_dropdown_' + currentSelectedItemIndex);
        const currentItemIdElement = document.getElementById('items_dropdown_' + currentSelectedItemIndex + '_value');
        const currentItemNameElement = document.getElementById('items_name_' + currentSelectedItemIndex);
        const currentItemUomElement = document.getElementById('uom_dropdown_' + currentSelectedItemIndex);
        const currentItemQtyElement = document.getElementById('item_qty_' + currentSelectedItemIndex);
        const currentItemRateElement = document.getElementById('item_rate_' + currentSelectedItemIndex);

        if (!currentItemDropdown || !currentItemIdElement || !currentItemNameElement || !currentItemUomElement || !currentItemQtyElement || !currentItemRateElement) {
            Swal.fire({
                title: 'Warning!',
                text: 'No Item selected',
                icon: 'warning',
            });
            return;
        }
        let previousItemAttrArray = JSON.parse(currentItemDropdown.getAttribute('attribute-array'));
        previousItemAttrArray.forEach(previousItemAttr => {
            previousItemAttr.values_data.forEach(valData => {
                valData.selected = false;
            });
        });

        const currentItemObj = {
            itemDataName : currentItemNameElement.value,
            itemDataCode : currentItemDropdown.value,
            itemDataId : currentItemDropdown.getAttribute('data-id'),
            itemDataHsnCode : currentItemDropdown.getAttribute('hsn_code'),
            itemDataAttributeArray : previousItemAttrArray,
            itemDataSpecs : currentItemDropdown.getAttribute('specs'),
            itemId : currentItemIdElement.value,
            itemUomHTML : currentItemUomElement.innerHTML,
            itemQty : currentItemQtyElement.value,
            itemRate : currentItemRateElement.value,
            deliveryDate : $("#delivery_date_" + currentSelectedItemIndex).val(),
        };
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
        newItemRow.innerHTML = `
        <tr id = "item_row_${newIndex}">
            <td class="customernewsection-form">
                <div class="form-check form-check-primary custom-checkbox">
                    <input type="checkbox" class="form-check-input item_row_checks" id="item_row_check_${newIndex}" del-index = "${newIndex}">
                    <label class="form-check-label" for="item_row_check_${newIndex}"></label>
                </div>
            </td>
            <td class="poprod-decpt">

                <input type="text" id = "items_dropdown_${newIndex}" name="item_code[${newIndex}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" value = "${currentItemObj.itemDataCode}" autocomplete="off" data-name="${currentItemObj.itemDataName}" data-code="${currentItemObj.itemDataCode}" data-id="${currentItemObj.itemDataId}" hsn_code = "${currentItemObj.itemDataHsnCode}" item_name = "${currentItemObj.itemDataName}" attribute-array = '${JSON.stringify(currentItemObj.itemDataAttributeArray)}' specs = "${currentItemObj.itemDataSpecs}">
                <input type = "hidden" name = "item_id[]" id = "items_dropdown_${newIndex}_value" value = "${currentItemObj.itemDataId}"></input>

            </td>

            <td class="poprod-decpt">
                <input type="text" id = "items_name_${newIndex}" name = "item_name[${newIndex}]" class="form-control mw-100"   value = "${currentItemObj.itemDataName}" readonly>
            </td>
            <td class="poprod-decpt" id = "attribute_section_${newIndex}">
                <button id = "attribute_button_${newIndex}" type = "button" data-bs-toggle="modal" onclick = "setItemAttributes('items_dropdown_${newIndex}', ${newIndex});" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                <input type = "hidden" name = "attribute_value_${newIndex}" />

            </td>
            <td>
                <select class="form-select" name = "uom_id[]" id = "uom_dropdown_${newIndex}">
                    ${currentItemObj.itemUomHTML}
                </select>
            </td>
            <td><input type="text" id = "item_qty_${newIndex}" data-index = '${newIndex}' name = "item_qty[${newIndex}]" oninput = "changeItemQty(this, ${newIndex});" onchange = "itemQtyChange(this, ${newIndex})" class="form-control mw-100 text-end item_qty_input" onblur = "setFormattedNumericValue(this);" value = "${currentItemObj.itemQty}"/></td>
            <td><input type="text" id = "item_rate_${newIndex}" onkeydown = "openDeliveryScheduleFromTab(${newIndex});" name = "item_rate[${newIndex}]" oninput = "changeItemRate(this, ${newIndex});" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" value = "${currentItemObj.itemRate}"/></td>
            <td><input type="text" id = "item_value_${newIndex}" disabled class="form-control mw-100 text-end item_values_input" /></td>
            <input type = "hidden" id = "header_discount_${newIndex}" value = "0" ></input>
            <input type = "hidden" id = "header_expense_${newIndex}" ></input>
            <td>
                <div class="position-relative d-flex align-items-center">
                    <input type="text" id = "item_discount_${newIndex}" disabled class="form-control mw-100 text-end item_discounts_input" style="width: 70px" />
                    <div class="ms-50">
                        <button type = "button" onclick = "onDiscountClick('item_value_${newIndex}', ${newIndex})" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Add</button>
                    </div>
                </div>
            </td>
            <input type="hidden" id = "item_tax_${newIndex}" class="form-control mw-100 text-end item_taxes_input" style="width: 70px" />
            <td><input type="text" id = "value_after_discount_${newIndex}"  disabled class="form-control mw-100 text-end item_val_after_discounts_input" /></td>
            <td style = "{{request() -> type === 'so' ? '' : 'display:none;'}}"><input type="date" name = "delivery_date[${newIndex}]" id = "delivery_date_${newIndex}" class="form-control mw-100" value = "${currentItemObj.deliveryDate}" /></td>
            <input type = "hidden" id = "value_after_header_discount_${newIndex}" class = "item_val_after_header_discounts_input" ></input>

                <input type="hidden" id = "item_total_${newIndex}"  disabled class="form-control mw-100 text-end item_totals_input" />
                <td>
                <div class="d-flex">
                    @if(request() -> type === 'so')
                        <div class="me-50 cursor-pointer" onclick = "openDeliverySchedule(${newIndex});">    <span data-bs-toggle="tooltip" data-bs-placement="top" title="Delivery Schedule" class="text-primary"><i data-feather="calendar"></i></span></div>
                        <div class="me-50 cursor-pointer dynamic_bom_div" id = "dynamic_bom_div_${newIndex}" onclick = "getCustomizableBOM(${newIndex})" style = "display:none;"> <span data-bs-toggle="tooltip" data-bs-placement="top" title="BOM" class="text-primary"><i data-feather="table"></i></span></div>
                    @endif
                    <div class="me-50 cursor-pointer" onclick = "setViewDetailedStocks('${newIndex}');"> <span data-bs-toggle="tooltip" data-bs-placement="top" title="Stocks" class="text-primary"><i data-feather="layers"></i></span></div>
                    <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick = "setItemRemarks('item_remarks_${newIndex}');">        <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span></div>
                </div>
            </td>
            <input type="hidden" id = "item_remarks_${newIndex}" name = "item_remarks[]"/>
            </tr>
        `;
        tableElementBody.appendChild(newItemRow);
        //Delivery Schedule
        const previousHiddenQtiesInput = document.getElementsByClassName('delivery_schedule_qties_hidden_' + currentSelectedItemIndex);
        const previousHiddenDatesInput = document.getElementsByClassName('delivery_schedule_dates_hidden_' + currentSelectedItemIndex);
        for (let index = 0; index < previousHiddenQtiesInput.length; index++) {
            addHiddenInput("item_delivery_schedule_qty_" + newIndex + "_" + index, previousHiddenQtiesInput[index].value , `item_delivery_schedule_qty[${newIndex}][${index}]`, 'delivery_schedule_qties_hidden_' + newIndex, "item_row_" + newIndex);
            addHiddenInput("item_delivery_schedule_date" + newIndex + "_" + index, previousHiddenDatesInput[index].value, `item_delivery_schedule_date[${newIndex}][${index}]`, 'delivery_schedule_dates_hidden_' + newIndex, "item_row_" + newIndex);
        }
        initializeAutocomplete1("items_dropdown_" + newIndex, newIndex);
        itemRowCalculation(newIndex);
        renderIcons();
        disableHeader();
        setItemAttributes('items_dropdown_' + newIndex, newIndex);
        let itemQtyElement = document.getElementById('item_qty_' + newIndex);
        if (itemQtyElement) {
        let dataIndexForBom = itemQtyElement.getAttribute('data-index');
        checkBomCondition(dataIndexForBom, false);
        }
        currentSelectedItemIndex = newIndex;
    }

    function getCustomizableBOM(itemIndex, isEditable = true, openPopUp = true)
    {
        let finalAmendSubmitButton = document.getElementById("amend-submit-button");
        const bomIcon = document.getElementById('dynamic_bom_div_' + itemIndex);
        var bomAttributeIds = [];
        var soItemBomIds = [];
        var associatedBomId = null;
        if (bomIcon.getAttribute('bom_details')) {
            var bomDetails = JSON.parse(bomIcon.getAttribute('bom_details'));
            bomDetails.forEach((bomDetail) => {
                bomAttributeIds.push({
                    bom_detail_id : bomDetail.bom_detail_id,
                    bom_attributes : bomDetail.bom_attributes
                });
                if (bomDetail.id) {
                    soItemBomIds.push(bomDetail.id);
                }
            });
        }
        var bomDetailsAttribute = [];
        const currentItemQty = document.getElementById('item_qty_' + itemIndex)?.value;
        let itemAttributes = JSON.parse(document.getElementById(`items_dropdown_${itemIndex}`).getAttribute('attribute-array'));
                let selectedItemAttr = [];
                if (itemAttributes && itemAttributes.length > 0) {
                    itemAttributes.forEach(element => {
                        var selectedAttrValue = null;
                        element.values_data.forEach(subElement => {
                            if (subElement.selected) {
                                selectedAttrValue = (subElement.id);
                            }
                        });
                        selectedItemAttr.push({
                            attribute_value : selectedAttrValue,
                            attribute_id : element.id
                        })
                    });
                }
        $.ajax({
            url: "{{route('sale.order.get.production.bom')}}",
            method: 'POST',
            dataType: 'json',
            header : {
                'X-CSRF-TOKEN': getCsrfToken()
            },
            data: {
                item_id: document.getElementById('items_dropdown_'+ itemIndex + '_value').value,
                item_attributes : selectedItemAttr,
                bom_id : associatedBomId,
                bom_attributes : JSON.stringify(bomAttributeIds),
                so_item_bom_ids : JSON.stringify()
            },
            success: function(data) {
                if (data.data.levels && data.data.levels.length > 0) {
                    let letActualEditable = data.data.customizable ? (isEditable) : false;
                    const datatable = document.getElementById('bom-details');
                    let dataTableHTML = ``;
                    data.data.levels.forEach((dataLevel, index) => {
                        let currentBomItems = ``;
                        dataLevel.bom_details.forEach((bomItem, bomItemIndex) => {
                            let bomDocAttributes = [];
                            let currentBomAttributes = `<div class = "row">`;
                            bomItem.item_attributes_array.forEach((bomAttribute) => {
                                let bomAttributeSelectionHTML = ``;
                                bomAttribute.values_data.forEach((attrVal) => {
                                    if (attrVal.selected) {
                                        bomDocAttributes.push({
                                            attribute_group_id : bomAttribute.attribute_group_id,
                                            attribute_name : bomAttribute.group_name,
                                            attribute_value : attrVal.value,
                                            attribute_value_id : attrVal.id,
                                            attribute_id : bomAttribute.id
                                        });
                                    }
                                    bomAttributeSelectionHTML += `
                                    <option value = "${attrVal.id}" ${attrVal.selected ? 'selected' : ''}> ${attrVal.value} </option>
                                    `;
                                });
                                currentBomAttributes += `
                                <div class = "col-md-auto">
                                <div class = "row">
                                <div class = "col-auto" style = "margin-top:3%;">
                                ${bomAttribute.group_name}
                                </div>
                                <div class = "col">
                                <select ${letActualEditable || finalAmendSubmitButton ? '' : 'disabled' } class="form-select select2" style="max-width:100% !important;" oninput = "bomAttributeChange(this, ${itemIndex}, ${bomItem.id}, ${bomAttribute.id});">
                                    ${bomAttributeSelectionHTML}
                                </select>
                                </div>
                                </div>
                                </div>
                                `
                            });
                            bomDetailsAttribute.push({
                                bom_detail_id : bomItem.id,
                                item_code : bomItem.item_code,
                                item_id : bomItem.item_id,
                                item_name : bomItem.item_name,
                                uom_id : bomItem.uom_id,
                                uom_name : bomItem.uom_name,
                                qty : bomItem.qty,
                                station_id : bomItem.station_id,
                                station_name : bomItem.station_name,
                                bom_attributes : bomDocAttributes,
                                remark : bomItem.remark ?bomItem.remark : ""
                            });
                            currentBomItems += `
                            <tr>
                            <td>${bomItem.item_code}</td>
                            <td>${bomItem.item_name}</td>
                            <td>${bomItem.uom_name}</td>
                            <td>
                            ${currentBomAttributes}
                            </td>
                            <td>${bomItem.qty}</td>
                            <td>${bomItem.remark}</td>
                            <td><span class = "remove_bom_item text-danger"><i data-feather="trash-2"></i> </span></td>
                            </div>
                            </td>
                            </tr>
                            `;
                        });
                        dataTableHTML += `
                        <tr class="approvlevelflow level-row">
                            <td colspan="6">
                                <h6 class="mb-0 fw-bolder text-dark levelText">${dataLevel.name}</h6>
                            </td>
                            <td colspan="1">
                                <span class = "add_bom_item text-primary"><i data-feather="plus-square"></i> </span>
                            </td>
                        </tr>
                        ${currentBomItems}
                        `
                    });
                    datatable.innerHTML = dataTableHTML;
                    if (!bomIcon.getAttribute('bom_details') && data.data.customizable) {
                        bomIcon.setAttribute('bom_details', JSON.stringify(bomDetailsAttribute));
                    }
                    if (openPopUp) {
                        $("#BOM").modal("show");
                    }
                    renderIcons();
                } else {
                    // Swal.fire({
                    //     title: 'Error!',
                    //     text: data.message,
                    //     icon: 'error',
                    // });
                }
                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                    Swal.fire({
                        title: 'Error!',
                        text: "Some internal error occured",
                        icon: 'error',
                    });
                }
            });
    }

    function bomAttributeChange(element, itemIndex, bomDetailId, bomAttributeId)
    {
        const bomIcon = document.getElementById('dynamic_bom_div_' + itemIndex);
        var bomDetails = [];
        if (bomIcon.getAttribute('bom_details')) {
            bomDetails = JSON.parse(bomIcon.getAttribute('bom_details'));
        }
        bomDetails.forEach((bomDetail) => {
            if (bomDetail.bom_detail_id == bomDetailId) {
                bomDetail.bom_attributes.forEach((bomAttr) => {
                    if (bomAttr.attribute_id == bomAttributeId) {
                        bomAttr.attribute_value_id = element.value;
                        bomAttr.attribute_value = element.selectedOptions[0].text;
                    }
                });
            }
        });
        bomIcon.setAttribute('bom_details', JSON.stringify(bomDetails));
    }

    // Short Close
    $(document).on('click', '#shortCloseBtn', (e) => {
        let itemIds = [];
        const allRowsCheck = document.getElementsByClassName('item_row_checks');
        for (let index = 0; index < allRowsCheck.length; index++) {
            if (allRowsCheck[index].checked) {
                const currentRowIndex = allRowsCheck[index].getAttribute('del-index');
                const currentRow = document.getElementById('item_row_' + currentRowIndex);
                if (currentRow) {
                    if (currentRow.getAttribute('data-id')) {
                        itemIds.push(currentRow.getAttribute('data-id'));
                    }
                }
            }
        }
        if (itemIds.length) {
            itemIds = itemIds.join(',');
            $("[name='short_close_ids']").val(itemIds);
            $("#shortCloseModal").modal('show');
        } else {
            Swal.fire({
                title: 'Error!',
                text: "Please select at least one item",
                icon: 'error',
            });
        }
    });

    // Short Close Submit
$(document).on('click', '#shortCloseBtnSubmit', (e) => {
    let remark = $("#shortCloseModal").find("[name='amend_remark']").val();
    let errorFlag = false;
    if(!remark) {
        $("#shortCloseModal").find("#amendRemarkError").removeClass("d-none");
        errorFlag = true;
    } else {
        $("#shortCloseModal").find("#amendRemarkError").addClass("d-none");
    }
    if (errorFlag) return;
    let formData = new FormData();
    let files = $("#shortCloseModal").find('input[name="amend_attachment[]"]')[0].files;
    let short_close_ids = $("#short_close_ids").val() || '';
    formData.append('short_close_ids', short_close_ids);
    formData.append('amend_remark', remark);
    for (let i = 0; i < files.length; i++) {
        formData.append('amend_attachment[]', files[i]);
    }
    $.ajax({
        url: '{{route("sale.order.get.shortClose.submit")}}',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        beforeSend: function () {
            $('#shortCloseBtnSubmit').prop('disabled', true).text('Submitting...');
        },
        success: function (response) {
            setTimeout(() => {
                Swal.fire({
                    title: 'Success!',
                    text: response.message,
                    icon: 'success',
                });
                window.location.href = '{{route("sale.order.index")}}';
            },500);
        },
        error: function (xhr) {
            alert('Something went wrong! Please try again.');
        },
        complete: function () {
            $('#shortCloseBtnSubmit').prop('disabled', false).text('Submit');
        }
    });
});

$('#attribute').on('hidden.bs.modal', function () {
    setAttributesUI();
});

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
        attributesArray = JSON.parse(attributesArray);
        if (attributesArray.length == 0) {
            return;
        }
        let attributeUI = `<div data-bs-toggle="modal" onclick = "setItemAttributes('items_dropdown_${currentItemIndex}', ${currentItemIndex});" data-bs-target="#attribute" style = "white-space:nowrap; cursor:pointer;">`;
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

            if(attrArr && attrArr.short_name && attrArr.short_name.length > 0)
            {
                short = true;
            }
            //Retrieve character length of attribute name
            let currentStringLength = short ? Number(attrArr.short_name.length) : Number(attrArr.group_name.length);
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
                //Only show the data if remaining length is greater than 3
                if (remainingLength >= 3) {
                    attributeUI += `<span class="badge rounded-pill badge-light-primary"><strong>${attrArr.group_name.substring(0, remainingLength - 1)}..</strong></span>`
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
                    onclick = "setItemAttributes('items_dropdown_${currentItemIndex}', '${currentItemIndex}', false);"
                    data-bs-target="#attribute"
                    class="btn p-25 btn-sm btn-outline-secondary"
                    style="font-size: 10px">Attributes</button>
                <input type = "hidden" name = "attribute_value_${currentItemIndex}" />
            `;
        }

    }

    function uploadItemImportFile()
    {
        //Build Form Data
        let formData = new FormData();
        let file = $("#import_attachment_input");
        //Table Ids
        let successTable = document.getElementById('success-table-body');
        let failTable = document.getElementById('failed-table-body');
        let importSection = document.getElementById('upload-status-section');
        let successSectionCount = document.getElementById('success-count');
        let failCountSection = document.getElementById('failed-count');
        let submitButton = document.getElementById('item_upload_submit');
        let successCount = 0;
        let errorCount = 0;
        //Check for Attachment
        if (!file || file.length <= 0) {
            Swal.fire({
                title: 'Error!',
                text: 'Please select a file first',
                icon: 'error',
            });
            return;
        }
        //Check if atlease one attachment is attached
        if (!file[0]?.files || file[0]?.files.length <= 0) {
            Swal.fire({
                title: 'Error!',
                text: 'Please select a file first',
                icon: 'error',
            });
            return;
        }
        formData.append('attachment', file[0].files[0]);
        //Hit the AJAX
        $.ajax({
            url: "{{route('salesOrder.import.item.save')}}",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                //Loader
                document.getElementById('erp-overlay-loader').style.display = "flex";
            },
            success: function (response) {
                let data = response.data;
                let dataSuccessHTML = ``;
                let dataErrorHTML = ``;
                if (data && (data.valid_records > 0 || data.invalid_records > 0)) {
                    successTable.innerHTML = data.validUI;
                    failTable.innerHTML = data.invalidUI;
                    successSectionCount.innerHTML = `(${data.valid_records})`;
                    failCountSection.innerHTML = `(${data.invalid_records})`;
                    if (data.valid_records > 0) {
                        submitButton.classList.remove('d-none');
                    }
                    importSection.classList.remove('d-none');
                } else {
                    successTable.innerHTML = `
                    <tr>
                        <td colspan = "9">No records found</td>
                    <tr>
                    `;
                    failTable.innerHTML = `
                    <tr>
                        <td colspan = "10">No records found</td>
                    <tr>
                    `;
                    successSectionCount.innerHTML = `(0)`;
                    failCountSection.innerHTML = `(0)`;
                    submitButton.classList.add('d-none');
                    importSection.classList.add('d-none');
                    Swal.fire({
                        title: 'Error!',
                        text: 'No items found from excel',
                        icon: 'error',
                    });
                }
            },
            error: function (xhr) {
                let errorResponse = xhr.responseJSON;
                document.getElementById('erp-overlay-loader').style.display = "none";
                successTable.innerHTML = `
                <tr>
                    <td colspan = "9">No records found</td>
                <tr>
                `;
                failTable.innerHTML = `
                <tr>
                    <td colspan = "10">No records found</td>
                <tr>
                `;
                successSectionCount.innerHTML = `(0)`;
                failCountSection.innerHTML = `(0)`;
                submitButton.classList.add('d-none');
                importSection.classList.add('d-none');
                Swal.fire({
                    title: 'Error!',
                    text: errorResponse?.message ? errorResponse?.message : 'Some internal error occured. Please try again later.',
                    icon: 'error',
                });
            },
            complete: function () {
                document.getElementById('erp-overlay-loader').style.display = "none";
            }
        });
    }

    function uploadAndRenderItems()
    {
        //Hit the AJAX
        $.ajax({
            url: "{{route('salesOrder.import.item.store')}}",
            type: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            beforeSend: function () {
                //Loader
                document.getElementById('erp-overlay-loader').style.display = "flex";
            },
            success: function (response) {
                if (response.data) {
                    addUploadedItemsToUI(response.data);
                    $("#item_upload").modal("hide");
                    document.getElementById('erp-overlay-loader').style.display = "none";
                } else {
                    document.getElementById('erp-overlay-loader').style.display = "none";
                    Swal.fire({
                        title: 'Error!',
                        text: response.message ? response.message : 'Some internal error occured, Please try again after some time.',
                        icon: 'error',
                    });
                }
            },
            error: function (xhr) {
                let errorResponse = xhr.responseJSON;
                document.getElementById('erp-overlay-loader').style.display = "none";
                Swal.fire({
                        title: 'Error!',
                        text: errorResponse?.message ? errorResponse?.message : 'Some internal error occured, Please try again after some time.',
                        icon: 'error',
                });
            },
            complete: function () {
                document.getElementById('erp-overlay-loader').style.display = "none";
            }
        });
    }

    function addUploadedItemsToUI(data)
    {
        const mainTableItem = document.getElementById('item_header');
        const tableElementBody = document.getElementById('item_header');
        const previousElements = document.getElementsByClassName('item_header_rows');
        let newIndex = previousElements.length ? previousElements.length : 0;
        data.forEach(item => {
            mainTableItem.innerHTML += `
                <tr id = "item_row_${newIndex}" class = "item_header_rows" onclick = "onItemClick('${newIndex}');" data-index = "${newIndex}">
                    <td class="customernewsection-form">
                        <div class="form-check form-check-primary custom-checkbox">
                            <input type="checkbox" class="form-check-input item_row_checks" id="item_row_check_${newIndex}" del-index = "${newIndex}">
                            <label class="form-check-label" for="item_row_check_${newIndex}"></label>
                        </div>
                    </td>
                    <td class="poprod-decpt">

                        <input type="text" id = "items_dropdown_${newIndex}" name="item_code[${newIndex}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" autocomplete="off" data-name="${item?.item?.item_name}" data-code="${item?.item?.item_code}" data-id="${item?.item?.id}" hsn_code = "${item?.item?.hsn?.code}" item-name = "${item?.item?.item_name}" specs = '${JSON.stringify(item?.item?.specifications)}' attribute-array = '${JSON.stringify([])}'  value = "${item?.item?.item_code}" readonly>
                        <input type = "hidden" name = "item_id[]" id = "items_dropdown_${newIndex}_value" value = "${item?.item_id}"></input>
                                                            </td>
                                                            <td class="poprod-decpt">
                                                                <input type="text" id = "items_name_${newIndex}" class="form-control mw-100" name = "item_name[${newIndex}]" value = "${item?.item?.item_name}" readonly>
                                                            </td>
                                                            <td class="poprod-decpt" id = "attribute_section_${newIndex}">
                        <button id = "attribute_button_${newIndex}" type = "button" data-bs-toggle="modal" onclick = "setItemAttributes('items_dropdown_${newIndex}', '${newIndex}', true);" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px" >Attributes</button>
                        <input type = "hidden" name = "attribute_value_${newIndex}" />
                    </td>

                                                            <td>
                        <select class="form-select" name = "uom_id[]" id = "uom_dropdown_${newIndex}">

                        </select>
                        </td>
                        <td><input type="text" data-index = '${newIndex}' id = "item_qty_${newIndex}" name = "item_qty[${newIndex}]" oninput = "changeItemQty(this, '${newIndex}');" onchange = "itemQtyChange(this, '${newIndex}')" value = "${item?.qty}" class="form-control mw-100 text-end item_qty_input" onblur = "setFormattedNumericValue(this);" max = "${item?.qty}" /></td>
                        <td><input type="text" id = "item_rate_${newIndex}" onkeydown = "openDeliveryScheduleFromTab(${newIndex})" name = "item_rate[${newIndex}]" oninput = "changeItemRate(this, '${newIndex}');" value = "${item?.rate}" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" /></td>
                        <td><input type="text" id = "item_value_${newIndex}" disabled class="form-control mw-100 text-end item_values_input" value = "${(item?.qty ) * (item?.rate)}" /></td>
                        <input type = "hidden" id = "header_discount_${newIndex}" value = "0" ></input>
                        <input type = "hidden" id = "header_expense_${newIndex}" value = "0"></input>
                    <td>
                        <div class="position-relative d-flex align-items-center">
                            <input type="text" id = "item_discount_${newIndex}" disabled class="form-control mw-100 text-end item_discounts_input" style="width: 70px" value = "0"/>
                            <div class="ms-50">
                                <button type = "button" onclick = "onDiscountClick('item_value_${newIndex}', '${newIndex}')" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Add</button>
                            </div>
                        </div>
                    </td>
                        <input type="hidden" id = "item_tax_${newIndex}" value = "0" class="form-control mw-100 text-end item_taxes_input" style="width: 70px" />
                        <td><input type="text" id = "value_after_discount_${newIndex}" value = "${(item?.qty * item?.rate)}" disabled class="form-control mw-100 text-end item_val_after_discounts_input" /></td>
                        <td style = "{{request() -> type === 'so' ? '' : 'display:none;'}}"><input type="date" id = "delivery_date_${newIndex}" name = "delivery_date[${newIndex}]" class="form-control mw-100" value = "{{Carbon\Carbon::now() -> format('Y-m-d')}}"/></td>
                        <input type = "hidden" id = "value_after_header_discount_${newIndex}" class = "item_val_after_header_discounts_input" value = "${(item?.qty * item?.rate)}" ></input>
                        <input type="hidden" id = "item_total_${newIndex}" value = "${(item?.qty * item?.rate)}" disabled class="form-control mw-100 text-end item_totals_input" />
                    <td>
                        <div class="d-flex">
                            @if(request() -> type === 'so')
                                <div class="me-50 cursor-pointer" onclick = "openDeliverySchedule('${newIndex}');">    <span data-bs-toggle="tooltip" data-bs-placement="top" title="Delivery Schedule" class="text-primary"><i data-feather="calendar"></i></span></div>
                                <div class="me-50 cursor-pointer dynamic_bom_div" id = "dynamic_bom_div_${newIndex}" onclick = "getCustomizableBOM(${newIndex})" style = "display:none;"> <span data-bs-toggle="tooltip" data-bs-placement="top" title="BOM" class="text-primary"><i data-feather="table"></i></span></div>
                            @endif
                            <div class="me-50 cursor-pointer" onclick = "setViewDetailedStocks('${newIndex}');"> <span data-bs-toggle="tooltip" data-bs-placement="top" title="Stocks" class="text-primary"><i data-feather="layers"></i></span></div>
                            <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick = "setItemRemarks('item_remarks_${newIndex}');">        <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span></div>
                        </div>
                    </td>
                    <input type="hidden" id = "item_remarks_${newIndex}" name = "item_remarks[]" value = ""/>

                </tr>
                            `;
                            renderIcons();
                            const totalValue = item.qty * item.rate;
                            document.getElementById('discount_main_table').setAttribute('total-value', totalValue);
                            document.getElementById('discount_main_table').setAttribute('item-row', 'item_value_' + newIndex);
                            document.getElementById('discount_main_table').setAttribute('item-row-index', newIndex);

                            itemUomsHTML = ``;
                            if (item.item.uom && item.item.uom.id) {
                                itemUomsHTML += `<option value = '${item.item.uom.id}' ${item.item.uom.id == item.uom_id ? "selected" : ""}>${item.item.uom.alias}</option>`;
                            }
                            item.item.alternate_u_o_ms.forEach(singleUom => {
                                    itemUomsHTML += `<option value = '${singleUom.uom.id}' ${singleUom.uom.id == item.uom_id ? "selected" : ""} >${singleUom.uom?.alias}</option>`;
                            });
                            document.getElementById('uom_dropdown_' + newIndex).innerHTML = itemUomsHTML;
                            getItemTax(newIndex);
                            setAttributesUI(newIndex);
                            newIndex += 1;
                        });
    }

    function setViewDetailedStocks(itemIndex)
    {
        const itemId = document.getElementById('items_dropdown_' + itemIndex + '_value').value;
        const locationId = document.getElementById('store_id_input').value;
        let itemAttributes = JSON.parse(document.getElementById(`items_dropdown_${itemIndex}`).getAttribute('attribute-array'));
        viewDetailedStocks(itemId, itemAttributes);
    }
        // Opens import modal with store/type/header context
    function openImportItemModal(type) {
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
        form.find('input[name="store_id"], input[name="type"]').remove();
        form.append(`<input type="hidden" name="store_id" value="${storeId}">`);
        form.append(`<input type="hidden" name="type" value="${type}">`);
    }

    $(function() {
        // Handle file selection
        $(document).on('change', '#fileUpload', function (e) {
            const file = e.target.files[0];
            if (!file) return;
            handleFileSelected(file);
        });
        let parsedValidRows = [];
        // Proceed button AJAX upload
        $(document).on('click', '#proceedBtn', function () {
            const fileInput = $('#fileUpload')[0];
            if (!fileInput.files.length) {
                displayError('Please select a file to upload.');
                return;
            }
            const file = fileInput.files[0];
            let formData = new FormData();
            formData.append('attachment', file);

            // Add any extra data if needed (store_id/type/po_header_id)
            $('#importItemModal input[type=hidden]').each(function() {
                formData.append($(this).attr('name'), $(this).val());
            });
            $('#upload-error').hide().html('');
            $('#uploadProgress').removeClass('d-none');
            $('#uploadProgressBar').css('width', '0%').text('0%');

            $.ajax({
                url: "{{ route('generic.import.save', ['alias' => 'so']) }}",
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
                    console.log('Parsed Data:', response.data , "parsed HEader", response.headers);
                    // Update valid count
                    $('#valid-count').text(`(${validRows.length})`);
                    $('#invalid-count').text(`(${invalidRows.length})`);

                    // Show preview section
                    $('#parsedPreview').removeClass('d-none').show();

                    // Build table headers dynamically
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
                    $("#submitBtn").removeClass('d-none');
                    console.log(parsedValidRows);
                    window.lastParsedImport = {
                        valid: validRows,
                        invalid: invalidRows,
                        headers: headers
                    };
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
        

        $('#submitBtn').on('click', function () {
            const validRows = window.lastParsedImport?.valid || [];
            const headers = window.lastParsedImport?.headers || {};
            // console.log(validRows, headers);
            const tbody = $('#item_header');
            // console.log('table',tbody);
            tbody.empty(); // Clear existing rows
            let currentIndex = tbody.find('tr').length;
            // console.log('validRows', validRows);
            validRows.forEach((row, i) => {
                console.log('Processing row:', row);
                const index = currentIndex + i;
                const itemId = row.item_id || '';
                const itemCode = row.item_code || '';
                const itemName = row.item_name || '';
                const uomId = row.uom_id || '';
                const uomName = row.uom_name || '';
                const rate = row.rate || 0;
                const physicalQty = row.order_qty || 0;
                const deliveryDate = row.delivery_date || 0;
                const remarks = row.remarks || '';
                const attributeValue = row.attribute_value || '';
                const attributeGroupId = row.attribute_group_id || '';
                const itemValue = (rate * physicalQty).toFixed(2);
                // console.log(index);
                const rowHtml = `
                <tr id="item_row_${index}">
                    <td class="customernewsection-form">
                        <div class="form-check form-check-primary custom-checkbox">
                            <input type="checkbox" class="form-check-input item_row_checks" id="item_row_check_${index}" del-index="${index}">
                            <label class="form-check-label" for="item_row_check_${index}"></label>
                        </div>
                    </td>
                    <td class="poprod-decpt">
                        <input type="text" id="items_dropdown_${index}" name="item_code[${index}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" autocomplete="off"
                            data-name="${row.item_name ?? ''}" data-code="${row.item_code ?? ''}" data-id="${row.item_id ?? ''}" hsn_code="${row.hsn_code ?? ''}" item_name="${row.item_name ?? ''}"
                            attribute-array='${JSON.stringify(row.item_attribute_array ?? [])}' specs='${JSON.stringify(row.specifications ?? [])}' value="${row.item_code ?? ''}" readonly>
                        <input type="hidden" name="item_id[]" id="items_dropdown_${index}_value" value="${row.item_id ?? ''}">
                    </td>
                    <td class="poprod-decpt">
                        <input type="text" id="items_name_${index}" name="item_name[${index}]" class="form-control mw-100" value="${row.item_name ?? ''}" readonly>
                    </td>
                    <td class="poprod-decpt" id="attribute_section_${index}">
                        <button id="attribute_button_${index}" type="button" data-bs-toggle="modal" onclick="setItemAttributes('items_dropdown_${index}', ${index});" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                        <input type="hidden" name="attribute_value_${index}" value="${row.attribute_value ?? ''}">
                    </td>
                    <td>
                        <select class="form-select" name="uom_id[]" id="uom_dropdown_${index}">
                            <option value="${row.uom_id ?? ''}" selected>${row.uom_name ?? ''}</option>
                        </select>
                    </td>
                    <td>
                        <input type="text" id="item_qty_${index}" data-index="${index}" name="item_qty[${index}]" value="${row.order_qty ?? 0}" oninput="changeItemQty(this, ${index});" onchange="itemQtyChange(this, ${index})" class="form-control mw-100 text-end item_qty_input" onblur="setFormattedNumericValue(this);"/>
                    </td>
                    <td>
                        <input type="text" id="item_rate_${index}" onkeydown="openDeliveryScheduleFromTab(${index});" name="item_rate[${index}]" value="${row.rate ?? 0}" oninput="changeItemRate(this, ${index});" class="form-control mw-100 text-end" onblur="setFormattedNumericValue(this);"/>
                    </td>
                    <td>
                        <input type="text" id="item_value_${index}" disabled class="form-control mw-100 text-end item_values_input" value="${row.rate * row.order_qty ?? 0}" />
                    </td>
                    <input type="hidden" id="header_discount_${index}" value="0"></input>
                    <input type="hidden" id="header_expense_${index}"></input>
                    <td>
                        <div class="position-relative d-flex align-items-center">
                            <input type="text" id="item_discount_${index}" disabled class="form-control mw-100 text-end item_discounts_input" style="width: 70px" />
                            <div class="ms-50">
                                <button type="button" onclick="onDiscountClick('item_value_${index}', ${index})" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Add</button>
                            </div>
                        </div>
                    </td>
                    <input type="hidden" id="item_tax_${index}" class="form-control mw-100 text-end item_taxes_input" style="width: 70px" />
                    <td>
                        <input type="text" id="value_after_discount_${index}" disabled class="form-control mw-100 text-end item_val_after_discounts_input" />
                    </td>
                    <td style="{{request() -> type === 'so' ? '' : 'display:none;'}}">
                        <input type="date" name="delivery_date[${index}]" id="delivery_date_${index}" class="form-control mw-100" value="{{Carbon\Carbon::now() -> format('Y-m-d')}}" />
                    </td>
                    <input type="hidden" id="value_after_header_discount_${index}" class="item_val_after_header_discounts_input"></input>
                    <input type="hidden" id="item_total_${index}" disabled class="form-control mw-100 text-end item_totals_input" />
                    <td>
                        <div class="d-flex">
                            @if(request() -> type === 'so')
                                <div class="me-50 cursor-pointer" onclick="openDeliverySchedule(${index});">
                                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="Delivery Schedule" class="text-primary"><i data-feather="calendar"></i></span>
                                </div>
                                <div class="me-50 cursor-pointer dynamic_bom_div" id="dynamic_bom_div_${index}" onclick="getCustomizableBOM(${index})" style="display:none;">
                                    <span data-bs-toggle="tooltip" data-bs-placement="top" title="BOM" class="text-primary"><i data-feather="table"></i></span>
                                </div>
                            @endif
                            <div class="me-50 cursor-pointer" onclick="setViewDetailedStocks('${index}');">
                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="Stocks" class="text-primary"><i data-feather="layers"></i></span>
                            </div>
                            <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick="setItemRemarks('item_remarks_${index}');">
                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span>
                            </div>
                        </div>
                    </td>
                    <input type="hidden" id="item_remarks_${index}" name="item_remarks[]" value="${row.remarks ?? ''}"/>
                </tr>`;
                tbody.append(rowHtml);
                // setItemAttributes(`items_dropdown_${index}`, index, false);
                setAttributesUI(index);
                itemRowCalculation(index);
                onItemClick(index);
                // console.log('Row added:', rowHtml);
            });
            console.log('Parsed valid rows:', parsedValidRows);
            renderIcons()
            $('#importItemModal').modal('hide');
        });

        // Cancel button
        $('#cancelBtn').on('click', function () {
            $('#fileUpload').val('');
            $('#fileNameDisplay').hide();
            $('#upload-error').hide();
            $('#proceedBtn').hide();
        });

        // Sample download button
        $('#sampleBtn').on('click', function () {
            $.ajax({
                url: "{{ route('generic.import.sample.download', ['alias' => 'so']) }}",
                type: "GET",
                xhrFields: { responseType: 'blob' },
                success: function (data, status, xhr) {
                    let disposition = xhr.getResponseHeader('Content-Disposition');
                    let filename = "sample_import.xlsx";
                    if (disposition && disposition.indexOf('filename=') !== -1) {
                        let matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                        if (matches?.[1]) {
                            filename = matches[1].replace(/['"]/g, '');
                        }
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
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to download sample file.',
                        icon: 'error',
                    });
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

    </script>
@endsection
@endsection
