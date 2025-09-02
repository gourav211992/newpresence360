@extends('layouts.app')

@section('content')

    <!-- BEGIN: Content-->
    <form method="POST" data-completionFunction = "disableHeader" class="ajax-input-form sales_module_form rate_contract" action = "{{route('rate.contract.store')}}" data-redirect="{{ $redirect_url }}" id = "rate_contract_form" enctype='multipart/form-data'>

    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
				<div class="row">
                    @include('layouts.partials.breadcrumb-add-edit', [
                        'title' => 'Rate Contract',
                        'menu' => 'Home',
                        'menu_url' => url('home'),
                        'sub_menu' => 'Add New'
                    ])
                    <input type = "hidden" value = "draft" name = "document_status" id = "document_status" />
					<div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
						<div class="form-group breadcrumb-right" id = "buttonsDiv">
                        @if(!isset(request() -> revisionNumber))
                        <button type = "button" onclick="javascript: history.go(-1)" class="btn action_button btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
                            @if (isset($order) )
                            <!-- @if($buttons['print'])
                            <button class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer">
                                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                        <rect x="6" y="14" width="12" height="8"></rect>
                                    </svg>
                                    Print  <i class="fa-regular fa-circle-down"></i>
                                </button>
                                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                </ul>
                                @endif -->
                                @if($buttons['draft'])
                                    <button type="button" onclick = "submitForm('draft');" name="action" value="draft" class="btn action_button btn-outline-primary btn-sm mb-50 mb-sm-0" id="save-draft-button" name="action" value="draft"><i data-feather='save'></i>     <span class="button-text">Save as Draft</span>
                                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                    </button>
                                @endif
                                @if($buttons['submit'])
                                    <button type="button" onclick = "submitForm('submitted');" name="action" value="submitted" class="btn action_button btn-primary btn-sm" id="submit-button" name="action" value="submitted"><i data-feather="check-circle"></i> Submit</button>
                                @endif
                                @if($buttons['approve'])
                                    <button type="button" id="reject-button" data-bs-toggle="modal" data-bs-target="#approveModal" onclick = "setReject();" class="btn btn-danger action_button btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg> Reject</button>
                                    <button type="button" class="btn action_button btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal" onclick = "setApproval();" ><i data-feather="check-circle"></i> Approve</button>
                                @endif
                                @if($buttons['amend'])
                                    <button id = "amendShowButton" type="button" onclick = "openModal('amendmentconfirm')" class="btn action_button btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i> Amendment</button>
                                @endif
                                @if($buttons['post'])
                                <button id = "postButton" onclick = "onPostVoucherOpen();" type = "button" class="btn action_button btn-warning btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Post</button>
                                @endif
                                @if($buttons['voucher'])
                                <button type = "button" onclick = "onPostVoucherOpen('posted');" class="btn action_button btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg> Voucher</button>
                                @endif
                                @if($buttons['revoke'])
                                    <button id = "revokeButton" type="button" onclick = "revokeDocument();" class="btn action_button btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='rotate-ccw'></i> Revoke</button>
                                @endif
                            @else
                                <button type = "button" name="action" value="draft" id = "save-draft-button" onclick = "submitForm('draft');" class="btn action_button btn-outline-primary btn-sm mb-50 mb-sm-0"><i data-feather='save'></i> Save as Draft</button>
                                <button type = "button" name="action" value="submitted"  id = "submit-button" onclick = "submitForm('submitted');" class="btn btn-primary action_button btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Submit</button>
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
                                                    <input type = "hidden" value = "{{$order -> id}}" name = "rate_contract_id"></input>
                                                @endif
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
                                                            <input type = "hidden" name = "requester_type" value = "{{isset($order) ? $order -> requester_type : 'Department'}}" id = "requester_type_input" />
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
                                                            <input type="hidden" class="form-control disable_on_edit" readonly id = "issue_type" name = "issue_type" value = "{{ isset($order) ? $order->return_type : ""}}">

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

                                                    {{--
                                                    <div class="row align-items-center mb-1 lease-hidden">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Return Type<span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <select class="form-select disable_on_edit" name = "return_type" id = "return_type_input" oninput = "onReturnTypeChange(this);">
                                                            </select>
                                                        </div>
                                                    </div>
                                                    --}}

                                                </div>
                                                    {{--<div class="row align-items-center mb-1 lease-hidden user_field sub_contracting {{ isset($order) && $order->user_id ? '' : 'd-none' }}">
                                                        <div class="col-md-3">
                                                            <label class="form-label">User<span class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <select class="form-select disable_on_edit" name = "user_id" id = "user_input">
                                                            <option value = "" disabled selected>Select</option>
                                                            @if(isset($order) && $order->user_id)
                                                                <option value = "{{ $order->user_id }}" selected>{{ $order->user_name }}</option>
                                                            @else
                                                            @foreach ($users as $user)
                                                                <option value = "{{ $user->id }}">{{ $user->name }}</option>
                                                            @endforeach
                                                            @endif
                                                            </select>
                                                        </div>
                                                    </div>--}}

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
                            <div class="row">
                                <div class="col-md-12">
                                        <div class="card quation-card">
                                            <div class="card-header newheader">
                                                <div>
                                                    <h4 class="card-title">Party Details</h4>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                            <label class="form-label">Party Type<span class="text-danger">*</span></label>
                                                            <select class="form-select disable_on_edit" id="party_type" name="party_type">
                                                                <option value="customer" {{ (isset($order) && $order->customer_id) ? 'selected' : '' }}>Customer</option>
                                                                <option value="vendor" {{ (isset($order) && $order->vendor_id) ? 'selected' : '' }}>Vendor</option>
                                                            </select>
                                                    </div>
                                                    <div class="col-md-3">

                                                        <div class="mb-1">
                                                            <label class="form-label">Party<span class="text-danger">*</span></label>
                                                            <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct disable_on_edit" id="vendor_name" name="vendor_name" value = "{{ isset($order) && $order->vendor ? $order->vendor_code : (isset($order) && $order->customer ? $order->customer_code : "")}}" />
                                                        </div>
                                                        <input type="hidden" id="customer_id" name="customer_id" value = "{{ isset($order) ? $order?->customer_id : '' }}"/>
                                                        <input type="hidden" id="customer_code" name="customer_code" value ="{{ isset($order) ? $order?->customer_code : '' }}"/>
                                                        <input type="hidden" id="vendor_id" name="vendor_id" value = "{{ isset($order) ? $order?->vendor_id : '' }}"/>
                                                        <input type="hidden" id="vendor_code" name="vendor_code" value ="{{ isset($order) ? $order?->vendor_code : '' }}"/>
                                                        <input type="hidden" id="hidden_currency_id" name="hidden_currency_id[]" value="{{ isset($order) ? $order?->items?->first()?->currency_id : '' }}"/>
                                                        <input type="hidden" id="hidden_currency_name" name="hidden_currency_name[]" value="{{ isset($order) ? $order?->items?->first()?->currency_code : '' }}"/>
                                                        <input type="hidden" id="hidden_payment_terms" name="hidden_payment_terms[]" />
                                                        <input type="hidden" id="hidden_payment_terms_ids" name="hidden_payment_terms_ids[]" />
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                                                             <select class="form-select disable_on_edit" id = "currency_dropdown" name = "currency_id" readonly>
                                                                @if (isset($order) && isset($order -> vendor))
                                                                 <option value = "{{$order -> vendor -> currency_id}}">{{$order -> vendor ?-> currency ?-> name}}</option>
                                                                @elseif (isset($order) && isset($order -> customer))
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
                                                                @if (isset($order) && isset($order->payment_term_id))
                                                                        <option value="{{ $payment_term->id }}" {{ $payment_term->id == $order->payment_term_id ? 'selected' : '' }}>
                                                                            {{ $payment_term->name }}
                                                                        </option>
                                                                @else
                                                                    <option value="">Select</option>
                                                                @endif
                                                            </select>
                                                        </div>
                                                        <input type = "hidden" name = "payment_terms_code" value = "{{isset($order) ? $order -> payment_terms_id : ''}}" id = "payment_terms_code_input"></input>
                                                    </div>
                                                 </div>

                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="mb-1">
                                                            <label class="form-label">Applicable Organizations<span class="text-danger">*</span></label>
                                                            <select class="form-select select2 disable_on_edit" multiple name = "organization_id[]" id = "organization_id_input">
                                                                <option value = "" disabled>Select</option>
                                                                    @foreach ($all_orgs as $org) <!-- Updated variable name -->
                                                                        <option value="{{ $org->id }}"
                                                                            {{ isset($order) && in_array($org->id, $order->applicable_organizations ? json_decode($order->applicable_organizations) : []) ? 'selected' : '' }}
                                                                            {{ !isset($order) && $user->organization_id == $org->id ? 'selected' : '' }}>
                                                                            {{ $org->name }}
                                                                        </option>
                                                                    @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">

                                                        <label class="form-label">Start Date<span class="text-danger">*</span></label>
                                                        <input type="date" class="form-control" name="start_date" id="start_date_input" value="{{ isset($order) ? $order->start_date : Carbon\Carbon::now() -> format('Y-m-d') }}" onchange="this.setAttribute('value', this.value); setDateDefaults(); checkVendorContracts();">
                                                    </div>

                                                    <div class="col-md-4">

                                                        <div class="mb-1">
                                                            <label class="form-label">End Date</label>
                                                            <input type="date" class="form-control" name="end_date" id="end_date_input" value="{{ isset($order) ? $order->end_date : '' }}"
                                                            onchange="this.setAttribute('value', this.value); setDateDefaults(); checkVendorContracts();">
                                                        </div></div>
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
                                                            <button type="button" id="importItem" class="mx-1 btn btn-sm btn-outline-primary importItem" onclick="openImportItemModal('create', '')"><i data-feather="upload"></i> Import Item</button>  
                                                            <a href="#" onclick = "deleteItemRows();" class="btn btn-sm btn-outline-danger me-50">
                                                                <i data-feather="x-circle"></i> Delete</a>
                                                            <a href="#" onclick = "addItemRow();" id = "add_item_section" style = "display:none;" class="btn btn-sm btn-outline-primary">
                                                                <i data-feather="plus"></i> Add Item</a>
                                                            {{--<a href="#" onclick = "copyItemRow();" id = "copy_item_section" style = "display:none;" class="btn btn-sm btn-outline-primary">
                                                                <i data-feather="copy"></i> Copy Item</a>--}}
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
                                                                    <th max-width='180px'>Attributes</th>
                                                                    <th>UOM</th>
                                                                    <th width="80px" >MOQ</th>
                                                                    <th class = "numeric-alignment">From Qty</th>
                                                                    <th class = "numeric-alignment">To Qty</th>
                                                                    <th class = "numeric-alignment">Rate</th>
                                                                    <th class = "numeric-alignment">Lead Time</th>
                                                                    <th class = "numeric-alignment">Effective From</th>
                                                                    <th class = "numeric-alignment">Effective Upto</th>
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
                                                                        <input type = 'hidden' name = "rc_item_id[]" value = "{{$orderItem -> id}}" {{$orderItem -> is_editable ? '' : 'readonly'}}>
                                                                         <td class="customernewsection-form">
                                                                            <div class="form-check form-check-primary custom-checkbox">
                                                                                <input type="checkbox" class="form-check-input item_row_checks" id="item_checkbox_{{$orderItemIndex}}" del-index = "{{$orderItemIndex}}">
                                                                                <label class="form-check-label" for="item_checkbox_{{$orderItemIndex}}"></label>
                                                                            </div>
                                                                        </td>
                                                                        <td class="poprod-decpt">
                                                                            <input type="text" id = "items_dropdown_{{$orderItemIndex}}" name="item_code[{{$orderItemIndex}}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input {{$orderItem -> is_editable ? '' : 'restrict'}}" autocomplete="off" data-name="{{$orderItem -> item ?-> item_name}}" data-code="{{$orderItem -> item ?-> item_code}}" data-id="{{$orderItem -> item ?-> id}}" hsn_code = "{{$orderItem -> item ?-> hsn ?-> code}}" item-name = "{{$orderItem -> item ?-> item_name}}" specs = "{{$orderItem -> item ?-> specifications}}" attribute-array = "{{$orderItem -> item_attributes_array()}}"  value = "{{$orderItem -> item ?-> item_code}}" {{$orderItem -> is_editable ? '' : 'readonly'}} item-location = "[]">
                                                                            <input type = "hidden" name = "item_id[]" id = "items_dropdown_{{$orderItemIndex}}_value" value = "{{$orderItem -> item_id}}"></input>
                                                                            @if ($orderItem -> mi_item_id)
                                                                                <input type = "hidden" name = "mi_item_id[{{$orderItemIndex}}]" id = "mi_item_id_{{$orderItemIndex}}" value = "{{$orderItem -> mi_item_id}}"></input>
                                                                            @endif
                                                                        </td>
                                                                        <td class="poprod-decpt">
                                                                            <input type="text" id = "items_name_{{$orderItemIndex}}" class="form-control mw-100"   value = "{{$orderItem -> item ?-> item_name}}" name = "item_name[{{$orderItemIndex}}]" readonly>
                                                                        </td>
                                                                        <td class="poprod-decpt" id='attribute_section_{{$orderItemIndex}}'>

                                                                            @if($order->document_status != App\Helpers\ConstantHelper::DRAFT && $orderItem->item_attributes->isEmpty())

                                                                            @else
                                                                            <button id = "attribute_button_{{$orderItemIndex}}" {{ $order->document_status == App\Helpers\ConstantHelper::DRAFT ? '' : 'disabled' }} {{count($orderItem -> item_attributes_array()) > 0 ? '' : 'disabled'}} type = "button" data-bs-toggle="modal" onclick = "setItemAttributes('items_dropdown_{{$orderItemIndex}}', '{{$orderItemIndex}}', {{ $order->document_status == App\Helpers\ConstantHelper::DRAFT ? '' : json_encode(!$orderItem->is_editable) }});" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary " style="font-size: 10px">Attributes</button>
                                                                            <input type = "hidden" name = "attribute_value_{{$orderItemIndex}}" />
                                                                            @endif
                                                                         </td>
                                                                        <td>
                                                                            <select class="form-select" name = "uom_id[]" id = "uom_dropdown_{{$orderItemIndex}}">

                                                                            </select>
                                                                        </td>
                                                                        <td><input type="text" id = "MOQ_{{$orderItemIndex}}" value = "{{$orderItem -> moq}}" name = "MOQ[{{$orderItemIndex}}]"  class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);"/></td>
                                                                        <td><input type="text" id = "from_item_qty_{{$orderItemIndex}}" value = "{{$orderItem -> from_qty}}" name = "from_item_qty[{{$orderItemIndex}}]"  class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" max = "{{$orderItem -> to_qty - 0.01}}"/></td>
                                                                        <td><input type="text" id = "to_item_qty_{{$orderItemIndex}}" value = "{{$orderItem -> to_qty}}" name = "to_item_qty[{{$orderItemIndex}}]"  class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" max = "{{$orderItem -> max_qty_attribute}}"/></td>
                                                                        <td><input type="text" id = "item_rate_{{$orderItemIndex}}" value = "{{$orderItem -> rate}}" {{isset($orderItem->mi_item_id)?"readonly" : ""}} name = "item_rate[{{$orderItemIndex}}]" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);"/></td>
                                                                        <td><input type="text" id = "item_lead_{{$orderItemIndex}}" value = "{{$orderItem -> lead_time}}" {{isset($orderItem->mi_item_id)?"readonly" : ""}} name = "item_lead[{{$orderItemIndex}}]" class="form-control mw-100 text-end" /></td>
                                                                        <td class = "d-none">
                                                                            <select class="form-select" name="item_currency_id[{{$orderItemIndex}}]" id="item_currency_{{$orderItemIndex}}">
                                                                                    <option value="{{ $orderItem->currency_id }}">
                                                                                        {{ $orderItem->currency_code }}
                                                                                    </option>

                                                                            </select>
                                                                        </td>
                                                                        <td><input type="date" id="effective_from_{{$orderItemIndex}}" name="effective_from[{{$orderItemIndex}}]" class="form-control mw-100" value="{{$orderItem->from_date}}" /></td>
                                                                        <td><input type="date" id="effective_to_{{$orderItemIndex}}" name="effective_to[{{$orderItemIndex}}]" class="form-control mw-100" value="{{$orderItem->to_date}}" /></td>
                                                                        <td>
                                                                        <div class="d-flex">
                                                                                <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick = "setItemRemarks('item_remarks_{{$orderItemIndex}}');">
                                                                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span></div>
                                                                                @if(isset($order) && $orderItem->erpMrItemLot)
                                                                                <div class="me-50 cursor-pointer" lot-data="{{ $orderItem->erpMrItemLot }}" onclick = "setItemLot(this);"><span data-bs-toggle="tooltip" data-bs-placement="top" title="Lot" class="text-primary"><i data-feather="package"></i></span></div>
                                                                                @endif
                                                                            </div>
                                                                        <input type = "hidden" id = "item_remarks_{{$orderItemIndex}}" name = "item_remarks[{{$orderItemIndex}}]" />
                                                                        <input type = "hidden" id = "item_lots_{{$orderItemIndex}}" name = "item_lots[{{$orderItemIndex}}]" value="{{ $orderItem->erpMrItemLot }}" />
                                                                        </td>

                                                                      </tr>
                                                                    @endforeach
                                                                @else
                                                                @endif
                                                             </tbody>

                                                             <tfoot>

                                                                 <tr class="totalsubheadpodetail">
                                                                    <td class='con-css' colspan="13"></td>
                                                                </tr>

                                                                 <tr valign="top">
                                                                    <td id = "item_details_td" colspan="13" rowspan="10">
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



                                                    <div class="col-md-6 mt-2">
                                                        <div class="mb-1">
                                                            <label class="form-label">Terms & Conditions</label>
                                                            <select class="form-select select2" name="term_id" >
                                                                <option value="">Select</option>
                                                                @foreach($termsAndConditions as $termsAndCondition)
                                                                <option value="{{$termsAndCondition->id}}" {{isset($order) && in_array($termsAndCondition->id,$order?->terms?->pluck('id')?->toArray() ?? []) ? "selected" : ""}} data-detail="{{ $termsAndCondition->term_detail }}">{{$termsAndCondition->term_name}}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <textarea name="terms_data" id="summernote" class="form-control" {{isset($order) &&  $order->document_status !=\App\Helpers\ConstantHelper::DRAFT ? "disabled" : ''}}  placeholder="Enter Terms" maxlength="1000" oninput="if(this.value.length > 1000) this.value = this.value.slice(0, 1000);">{{ isset($order->tnc) ? $order->tnc : "" }}</textarea>
                                                        <small class="text-muted d-block text-end">
                                                            <span id="termsCharCount">0</span>/1000 characters
                                                        </small>
                                                        <input type="hidden" name="tnc" id="tnc" value="{{ isset($order->tnc) ? $order->tnc : "" }}">
                                                        <input type="hidden" id="customer_terms_id" value="" name="terms_id" />
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
                    <!-- Modal to add new record -->
                        </section>


                    </div>
                </div>
            </div>

            <div class="modal fade text-start" id="rescdule" tabindex="-1" aria-labelledby="header_pull_label" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1250px">
                    <div class="modal-content">
                        <div class="modal-header">
                            <div>
                                <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="header_pull_label">Select Document</h4>
                                <p class="mb-0">Select from the below list</p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row gx-2"> <!-- gx-2 reduces horizontal gutter -->

                                <div class="col">
                                    <label class="form-label">Location</label>
                                    <input type="text" id="location_code_input_qt" placeholder="Select" class="form-control ledgerselecct ui-autocomplete-input" autocomplete="off">
                                    <input type="hidden" id="location_id_qt_val">
                                    <input type="hidden" id="mi_item_ids" >
                                </div>


                                <!-- <div class="col">
                                    <label class="form-label">Vendor</label>
                                    <input type="text" id="vendor_input_mi" placeholder="Search" class="form-control ledgerselecct ui-autocomplete-input">
                                    <input type="hidden" id="vendor_id_mi_val">
                                </div> -->

                                <div class="col">
                                    <label class="form-label">Department</label>
                                    <input type="text" id="department_input_mi" placeholder="Search" class="form-control ledgerselecct ui-autocomplete-input">
                                    <input type="hidden" id="department_id_mi_val">
                                </div>

                                <div class="col">
                                    <label class="form-label">Requester</label>
                                    <input type="text" id="requester_input_mi" placeholder="Search" class="form-control ledgerselecct ui-autocomplete-input">
                                    <input type="hidden" id="requester_id_mi_val">
                                </div>

                                <div class="col">
                                    <label class="form-label">Document No.</label>
                                    <input type="text" id="document_no_input_mi" placeholder="Search" class="form-control ledgerselecct ui-autocomplete-input">
                                    <input type="hidden" id="document_id_mi_val">
                                </div>

                                <div class="col">
                                    <label class="form-label">Item</label>
                                    <input type="text" id="item_name_input_mi" placeholder="Search by Name/Code" class="form-control">
                                    <input type="hidden" id="item_id_mi_val">
                                </div>
                                <div class="col  mt-1">
                                    <button onclick="getOrders();" type="button" style="margin: 2% 0.5%;" class="btn btn-warning w-60 btn-sm">
                                        <i data-feather="search"></i> Search
                                    </button>
                                    <button onclick="clearOrders();" type="button" style="margin: 2%;" class=" btn btn-danger w-60 btn-sm">
                                        <i data-feather="trash"></i> Clear
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                        <thead>
                                            <tr>
                                                <th>
                                                    <div class="form-check form-check-inline me-0">
                                                        <input class="form-check-input" type="checkbox" id="checkAllMiElement" onchange = "checkAllMi(this);">
                                                    </div>
                                                </th>
                                                <th>Series</th>
                                                <th>Doc No.</th>
                                                <th>Date</th>
                                                <th>Issue Type</th>
                                                <th>Location</th>
                                                <th>Vendor</th>
                                                <th>Requester/Department</th>
                                                <th>Item Code</th>
                                                <th>Item Name</th>
                                                <th>Attributes</th>
                                                <th>UOM</th>
                                                <th>Quantity</th>
                                                <th>Balance Qty</th>
                                                <th>Available Stock</th>
                                            </tr>
                                        </thead>
                                        <tbody id = "qts_data_table">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer text-end">
                            <button type = "button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
                            <button type = "button" class="btn btn-primary btn-sm" onclick = "processOrder();" data-bs-dismiss="modal"><i data-feather="check-circle"></i> Process</button>
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
                            <button type="button" class="btn btn-outline-secondary me-1">Cancel</button>
                            <button type="button" onclick = "saveAddressShipping();" class="btn btn-primary">Submit</button>
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
                            <button type="button" class="btn btn-outline-secondary me-1">Cancel</button>
                            <button type="button" onclick = "saveAddressBilling();" class="btn btn-primary">Submit</button>
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


        <div class="modal fade" id="FromLocation" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered" style="max-width: 900px">
                <div class="modal-content">
                    <div class="modal-header p-0 bg-transparent">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-sm-2 mx-50 pb-2">
                        <h1 class="text-center mb-1" id="shareProjectTitle">From Location</h1>
                        <div class="table-responsive-md customernewsection-form">
                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                                <thead>
                                    <tr>
                                        <th width="80px">S.No</th>
                                        <th>Rack</th>
                                        <th>Shelf</th>
                                        <th>Bin</th>
                                        <th width="50px">Qty</th>
                                    </tr>
                                </thead>
                                <tbody id = "item_from_location_table" current-item-index = '0'>
                                </tbody>
                            </table>
                        </div>
				    </div>

                    <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-outline-secondary me-1" onclick="closeModal('FromLocation');">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="closeModal('FromLocation');">Submit</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="ToLocation" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered" style="max-width: 900px">
                <div class="modal-content">
                    <div class="modal-header p-0 bg-transparent">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body px-sm-2 mx-50 pb-2">
                        <h1 class="text-center mb-1" id="shareProjectTitle">To Location</h1>
                        <a href="#" class="text-primary add-contactpeontxt mt-50 text-end" onclick = "addToLocationRow();">
                            <i data-feather='plus'></i> Add Location
                        </a>
                        <div class="table-responsive-md customernewsection-form">
                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                                <thead>
                                    <tr>
                                        <th width="50px">S.No</th>
                                        <th>Rack</th>
                                        <th>Shelf</th>
                                        <th>Bin</th>
                                        <th width="80px">Qty</th>
                                    </tr>
                                </thead>
                                <tbody id = "item_to_location_table" current-item-index = '0'>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary me-1" onclick="closeModal('ToLocation');">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="closeModal('ToLocation');">Submit</button>
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
                        <button type="button" class="btn btn-primary" onclick = "submitAttr('attribute')">Select</button>
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
                            Rate Contract
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
                <form class="ajax-submit-2" method="POST" action="{{ route('document.approval.rateContract') }}" data-redirect="{{ $redirect_url }}" enctype='multipart/form-data'>
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
              <p>Are you sure you want to <strong>Amend</strong> this <strong>Rate Contract</strong>?</p>
              <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
              <button type="button" data-bs-dismiss="modal" onclick = "amendConfirm();" class="btn btn-primary">Confirm</button>
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
    <div class="modal fade" id="lot" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog  modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-2 mx-50 pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">Lot Info</h1>
					<div class="table-responsive-md customernewsection-form">
                        <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                            <thead>
                                    <tr>
                                    <th width="50px">S.No</th>
                                    <th>Lot No.</th>
                                    <th>Lot Qty</th>
                                    <th>Return Qty</th>
                                    </tr>
                                </thead>
                                <tbody id = "bundle_schedule_table" current-item-index = '0'>
                                </tbody>
                        </table>
                    </div>
				</div>
				<div class="modal-footer justify-content-center">
						<button type="button" class="btn btn-outline-secondary me-1" onclick="closeModal('lot');">Cancel</button>
					<button type="button" class="btn btn-primary" onclick="saveLotData();">Submit</button>
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

<script>
    
        // Reflect selected option text from select2[name="term_id"] to #summernote1 textarea
        $(document).on('change', 'select[name="term_id"]', function () {
            let selectedText = $(this).find('option:selected').data('detail') || '';
            $('#summernote').summernote('code', selectedText);
            updateSummernoteData();
        });

        // Function to update char count & hidden input
        function updateSummernoteData() {
            let content = $('#summernote').summernote('code');
            let plainText = $('<div>').html(content).text(); // remove HTML tags for char count
            $('#termsCharCount').text(plainText.length);
            $('#tnc').val(content); // store HTML content in hidden input
        }

        // Bind Summernote change events
        $('#summernote').on('summernote.change', function (we, contents, $editable) {
            updateSummernoteData();
        });

        // Initialize Summernote (example)
        $('#summernote').summernote({
            height: 200
        });

        function safeParseAttributes(attr) {
            if (!attr) return []; // null, undefined, '', 0

            if (typeof attr === "string") {
                try {
                    return JSON.parse(attr);
                } catch (e) {
                    // not valid JSON, return raw string
                    return attr;
                }
            }

            // already array/object
            return attr;
        }

        var currentSelectedItemIndex = null ;
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })

        $('.action_button').on('click', function() {
            var $btn = $(this);
            var text = $(this).text();
            $btn.prop('disabled', true);
            $btn.find('.button-text').text('Processing...');
            $btn.find('.spinner-border').removeClass('d-none');

            // Simulate an AJAX request
            setTimeout(function() {
                $btn.prop('disabled', false); // Re-enable button
                $btn.find('.button-text').text(text); // Restore text
                $btn.find('.spinner-border').addClass('d-none'); // Hide loader
            }, 3000); // 3-second delay (Replace with actual AJAX request)
        });
        $('#returns').on('change', function() {
            var return_id = $(this).val();
            var seriesSelect = $('#series');

            seriesSelect.empty(); // Clear any existing options
            seriesSelect.append('<option value="">Select</option>');

            if (return_id) {
                $.ajax({
                    url: "{{ url('get-series') }}/" + return_id,
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
                // console.clear();
                // console.log(data);
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

                    setItemAttributes('items_dropdown_' + index, index);
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
                console.log('disabled',disabled);

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
                    document.getElementById('from_item_qty_' + index).focus();
                    document.getElementById('attribute_button_' + index).disabled = true;
                } else {
                    $("#attribute").modal("show");
                    document.getElementById('attribute_button_' + index).disabled = false;
                }
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

        function addItemRow(type=null)
        {
            var docType = $("#service_id_input").val();
            var invoiceToFollow = $("#service_id_input").val() == "yes";
            const tableElementBody = document.getElementById('item_header');
            const previousElements = document.getElementsByClassName('item_header_rows');
            const newIndex = previousElements.length ? previousElements.length : 0;
            if (newIndex == 0) {
                if (!$('#series_id_input').val()) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select a series.',
                        icon: 'error',
                    });
                    return;
                }
                if (!$("#order_no_input").val()) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please enter the order number.',
                        icon: 'error',
                    });
                    return;
                }
                if (!$('#order_date_input').val()) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select the order date.',
                        icon: 'error',
                    });
                    return;
                }
                if (!$('#vendor_name').val()) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select a vendor.',
                        icon: 'error',
                    });
                    return;
                }
                if (!$('#currency_dropdown').val()) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select a currency.',
                        icon: 'error',
                    });
                    return;
                }
                if (!$('#payment_terms_dropdown').val()) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select payment terms.',
                        icon: 'error',
                    });
                    return;
                }
                if (!$('#organization_id_input').val() || $('#organization_id_input').val().length === 0) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select applicable organizations.',
                        icon: 'error',
                    });
                    return;
                }
                if (!$('#start_date_input').val()) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select a start date.',
                        icon: 'error',
                    });
                    return;
                }
            } else {
                if (!$('#items_dropdown_' + (newIndex - 1)).val()) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select an item from the previous row.',
                        icon: 'error',
                    });
                    return;
                }
                if (!(parseFloat($('#from_item_qty_' + (newIndex - 1)).val()) >= 0)) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please enter a valid "From Quantity" for the previous row.',
                        icon: 'error',
                    });
                    return;
                }
                if (!(parseFloat($('#item_lead_' + (newIndex - 1)).val()) >= 0)) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please enter a valid lead time.',
                        icon: 'error',
                    });
                    return;
                }
                if (!(parseFloat($('#to_item_qty_' + (newIndex - 1)).val()) >= 0)) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please enter a valid "To Quantity" for the previous row.',
                        icon: 'error',
                    });
                    return;
                }
                if (!(parseFloat($('#item_rate_' + (newIndex - 1)).val()) > 0)) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please enter a valid rate for the previous row.',
                        icon: 'error',
                    });
                    return;
                }
                if (!$('#item_currency_' + (newIndex - 1)).val()) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select a currency for the previous row.',
                        icon: 'error',
                    });
                    return;
                }
                if (!$('#effective_from_' + (newIndex - 1)).val()) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select an effective "From Date" for the previous row.',
                        icon: 'error',
                    });
                    return;
                }
                if (!$('#uom_dropdown_' + (newIndex - 1)).val()) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please select a UOM for the previous row.',
                        icon: 'error',
                    });
                    return;
                }
                if (!$('#MOQ_' + (newIndex - 1)).val()) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Please enter the MOQ for the previous row.',
                        icon: 'error',
                    });
                    return;
                }

            }
            const newItemRow = document.createElement('tr');
            newItemRow.className = 'item_header_rows';
            newItemRow.id = "item_row_" + newIndex;
            currency_ids = $('#hidden_currency_id').val();
            currency_ids = currency_ids.split(',');
            currency_names = $('#hidden_currency_name').val();
            currency_names = currency_names.split(',');
            var currency = ``;
            for (let index = 0; index < currency_ids.length; index++) {
                if (currency_ids[index] && currency_names[index]) {
                    currency += `<option value = '${currency_ids[index]}'>${currency_names[index]}</option>`
                }
            }

            newItemRow.onclick = function () {
                onItemClick(newIndex);
            };
            // storesTo.forEach(store => {
            //     if (store.id == headerToStoreId) {
            //         storesToHTML += `<option value = "${store.id}" selected>${store.store_name}</option>`
            //     } else {
            //         storesToHTML += `<option value = "${store.id}">${store.store_name}</option>`
            //     }
            // });

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
                   <input type = "hidden" name = "item_id[${newIndex}]" id = "items_dropdown_${newIndex}_value"></input>
                </td>

                <td class="poprod-decpt">
                    <input type="text" id = "items_name_${newIndex}" name = "item_name[${newIndex}]" class="form-control mw-100" value = "" readonly>
                </td>
                <td class="poprod-decpt" id='attribute_section_${newIndex}'>
                    <button id = "attribute_button_${newIndex}" type = "button" data-bs-toggle="modal"  onclick = "setItemAttributes('items_dropdown_${newIndex}', ${newIndex});" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                    <input type = "hidden" name = "attribute_value_${newIndex}" />
                </td>
                <td>
                    <select class="form-select" name = "uom_id[]" id = "uom_dropdown_${newIndex}">
                    </select>
                </td>
                <td><input type="text" id = "MOQ_${newIndex}" name = "MOQ[]" class="form-control mw-100 text-end"  value = ""></td>
                <td><input type="text" id = "from_item_qty_${newIndex}" name = "from_item_qty[${newIndex}]" oninput = "changeItemQty(this, ${newIndex},'from');" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" value="0"/></td>
                <td><input type="text" id = "to_item_qty_${newIndex}" name = "to_item_qty[${newIndex}]" oninput = "changeItemQty(this, ${newIndex},'to');" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" value="0"/></td>
                <td><input type="text" id = "item_rate_${newIndex}" name = "item_rate[${newIndex}]" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);"/></td>
                <td><input type="text" id = "item_lead_${newIndex}" name = "item_lead[${newIndex}]" class="form-control mw-100 text-end" /></td>
                <td class='d-none'>
                    <select class="form-select" name = "item_currency_id[]" id = "item_currency_${newIndex}">
                    ${currency}
                    </select>
                </td>
                <td>
                    <input type="date" id="effective_from_${newIndex}" name="effective_from[]"
                        class="form-control mw-100 item_values_input" />
                </td>
                <td>
                    <input type="date" id="effective_to_${newIndex}" name="effective_to[]"
                        class="form-control mw-100 item_values_input" />
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
            renderIcons();
            disableHeader();

            const qtyInput = document.getElementById('from_item_qty_' + newIndex);

            const itemCodeInput = document.getElementById('items_dropdown_' + newIndex);
            const uomCodeInput = document.getElementById('uom_dropdown_' + newIndex);
            const requesterCodeInput = document.getElementById(`${type}_dropdown_` + newIndex);
            itemCodeInput.addEventListener('input', function() {
                checkStockData(newIndex);
            });
            uomCodeInput.addEventListener('input', function() {
                checkStockData(newIndex);
            });
            setDateDefaults(newIndex);
            $("#return_type_input").trigger("input");

        }

        function setDateDefaults(index=null) {
            const startDate = document.getElementById('start_date_input');
            const endDate = document.getElementById('end_date_input');
            console.log("index", index);
            if(index!=null)
            {
                const fromInput = document.getElementById(`effective_from_${index}`);
                const toInput = document.getElementById(`effective_to_${index}`);
                console.log("fromInput", fromInput);
                console.log("toInput", toInput);
                if (startDate.value) {
                    fromInput.min = startDate.value;
                    fromInput.value = startDate.value;
                    toInput.min = startDate.value;
                }

                if (endDate.value) {
                    fromInput.max = endDate.value;
                    toInput.max = endDate.value;
                    toInput.value = endDate.value;
                }

                // When 'from' date changes, update 'to' min accordingly
                fromInput.addEventListener('change', function () {
                    toInput.min = this.value;
                    if (toInput.value < this.value) {
                        toInput.value = this.value; // Optional: auto-correct invalid value
                    }
                });
            }
            else{
                if(startDate) {
                    endDate.min= startDate.value;
                }
                if(endDate) {
                    startDate.max= endDate.value;
                }
            }
        }
        setDateDefaults();
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
            const currentItemDropdown = document.getElementById('items_dropdown_' + currentSelectedItemIndex);
            const currentItemIdElement = document.getElementById('items_dropdown_' + currentSelectedItemIndex + '_value');
            console.log(currentItemIdElement.value);
            if (!currentItemIdElement.value) {
                Swal.fire({
                    title: 'Warning!',
                    text: 'No Item selected',
                    icon: 'warning',
                });
                return;
            }
            const currentItemNameElement = document.getElementById('items_name_' + currentSelectedItemIndex);
            const currentItemUomElement = document.getElementById('uom_dropdown_' + currentSelectedItemIndex);
            const currentItemRequesterElement = document.getElementsByClassName(`requester_name_${currentSelectedItemIndex}`);
            const miItemElement = document.getElementById(`mi_id_${currentSelectedItemIndex}`);
            const mi_item_id = miItemElement ? miItemElement.value : null;
            console.log(currentItemRequesterElement);
            const miRequesterElement = document.getElementById("mi_requester_type_"+currentSelectedItemIndex);
            const currentRequesterType = miRequesterElement ? miRequesterElement.value.toLowerCase() : ($("#requester_type_input").length ? $("#requester_type_input").val().toLowerCase() : null);
            const currentItemQtyElement = document.getElementById('from_item_qty_' + currentSelectedItemIndex);
            if(currentItemQtyElement.value == "" || currentItemQtyElement.value == 0)
            {
                Swal.fire({
                    title: 'Error!',
                    text: 'Item Qty is Empty , Can\'t Copy',
                    icon: 'warning',
                });
                return;
            }
            const currentItemRateElement = document.getElementById('item_rate_' + currentSelectedItemIndex);
            const itemMaxQty = $("#to_item_qty_" + currentSelectedItemIndex).attr('max');
            if(Number(itemMaxQty) <= Number(currentItemQtyElement.value))
            {
                Swal.fire({
                    title: 'Error!',
                    text: 'Item Max Achived , Can\'t Copy',
                    icon: 'warning',
                });
                return;
            }

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
                requester_name : miRequesterElement ? currentItemRequesterElement[0].value : currentItemRequesterElement[0].text ,
                requester_id : currentItemRequesterElement[1] ? currentItemRequesterElement[1].value : null,
                itemQty : currentItemQtyElement.value,
                itemRate : currentItemRateElement.value,
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
            let options = "";
            console.log(Array.isArray(currentItemObj.additemRequester) && !mi_item_id);
            // Ensure requesterData exists and is an array
            // Get selected value **before** mapping options
            const selectedValue = $("#"+currentRequesterType+"_id_"+currentSelectedItemIndex).val();
            if (Array.isArray(currentItemObj.additemRequester) && !mi_item_id) {
                options = currentItemObj.additemRequester.map(item => `
                    <option value="${item.id}" ${String(item.id) === String(selectedValue) ? "selected" : ""}>${item.name}</option>
                `).join("");  // Join all options into a single string
            }
            else
            {
                options = `<option value="${currentItemObj.requester_id}" selected>${currentItemObj.requester_name}</option>`;
            }

            console.log(options);

            // Default select and input fields
            let content = `
                <td><select id="${currentRequesterType}_id_${newIndex}" name="${currentRequesterType}_id[${newIndex}]" class="form-select requester_name_${newIndex} mw-100" ${Array.isArray(currentItemObj.additemRequester) && !mi_item_id && currentItemObj.additemRequester.length > 1 ? "" : "readonly"}>${options}  <!-- Dynamically inserted options --></select>
                <input type="hidden" id="${currentRequesterType}_id_${newIndex}" readonly name="${currentRequesterType}_id[]" class="form-control requester_name_${newIndex} mw-100 text-begin" value="${currentItemObj.requester_id ?? ""}" /></td>`;

            // Wrap in <td> only if mi_item_id exists
            const finalHtml = currentRequesterType ? content : "";

            console.log(finalHtml);


            newItemRow.innerHTML = `
            <tr id = "item_row_${newIndex}">
                <td class="customernewsection-form">
                    <div class="form-check form-check-primary custom-checkbox">
                        <input type="checkbox" class="form-check-input item_row_checks" id="item_row_check_${newIndex}" del-index = "${newIndex}">
                        <label class="form-check-label" for="item_row_check_${newIndex}"></label>
                    </div>
                </td>
                <td class="poprod-decpt">
                    <input type="text" id = "items_dropdown_${newIndex}" name="item_code[${newIndex}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" ${Array.isArray(currentItemObj.additemRequester) && !mi_item_id && currentItemObj.additemRequester.length > 1 ? "" : "readonly"} value = "${currentItemObj.itemDataCode}" autocomplete="off" data-name="${currentItemObj.itemDataName}" data-code="${currentItemObj.itemDataCode}" data-id="${currentItemObj.itemDataId}" hsn_code = "${currentItemObj.itemDataHsnCode}" item_name = "${currentItemObj.itemDataName}" attribute-array = '${JSON.stringify(currentItemObj.itemDataAttributeArray)}' specs = '${currentItemObj.itemDataSpecs}'>
                    <input type = "hidden" name = "item_id[]" id = "items_dropdown_${newIndex}_value" value = "${currentItemObj.itemDataId}"></input>
                    ${mi_item_id ? `<input type="hidden" id="mi_id_${newIndex}" value="${mi_item_id}" name = "mi_item_id[${newIndex}]">` : ""}
                    ${currentRequesterType ? `<input type = "hidden" value = "${currentRequesterType.toLowerCase()}" id = "mi_requester_type_${newIndex}" name = "${currentRequesterType.toLowerCase()}[${newIndex}]">`: ""}

                </td>

                <td class="poprod-decpt">
                    <input type="text" id = "items_name_${newIndex}" name = "item_name[${newIndex}]" class="form-control mw-100"   value = "${currentItemObj.itemDataName}" readonly>

                </td>
                <td class="poprod-decpt">
                    <button id = "attribute_button_${newIndex}" type = "button" data-bs-toggle="modal" onclick = "setItemAttributes('items_dropdown_${newIndex}', ${newIndex});" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                    <input type = "hidden" name = "attribute_value_${newIndex}" />

                </td>
                <td>
                    <select class="form-select" name = "uom_id[]" id = "uom_dropdown_${newIndex}">
                        ${currentItemObj.itemUomHTML}
                    </select>
                </td>
                ${finalHtml}
                <td><input type="text" id = "item_qty_${newIndex}" data-index = '${newIndex}' name = "item_qty[${newIndex}]" oninput = "changeItemQty(this, ${newIndex});" onchange = "itemQtyChange(this, ${newIndex})" class="form-control mw-100 text-end item_qty_input" onblur = "setFormattedNumericValue(this);" value = "${Number(itemMaxQty) && Number(itemMaxQty) < 9999999 ? Number(itemMaxQty) - Number(currentItemObj.itemQty) : Number(currentItemObj.itemQty) }" /></td>
                <td><input type="text" ${Array.isArray(currentItemObj.additemRequester) && !mi_item_id && currentItemObj.additemRequester.length > 1 ? "" : "readonly"} id = "item_rate_${newIndex}" name = "item_rate[${newIndex}]" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" value = "${Number(currentItemObj.itemRate)}"/></td>
                <td><input type="text" id = "item_value_${newIndex}" disabled class="form-control mw-100 text-end item_values_input" /></td>
                    <td>
                        <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick = "setItemRemarks('item_remarks_${newIndex}');">        <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span></div>
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
            renderIcons();
            disableHeader();
            setItemAttributes('items_dropdown_' + newIndex, newIndex);
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
                current_doc_id = 0;
                $('#issue_type').val("");
                // $('.requester').addClass('d-none');
                enableHeader();
            }
            $('#select_all_items_checkbox').prop('checked', false);

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
                const currentQty = document.getElementById('to_item_qty_' + index).value;
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
            //         return;
            //     }
            // }
        }

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
                    element.value = (parseFloat(maxInputVal ? maxInputVal  : 0)).toFixed(2)
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
                    element.value = (parseFloat(maxInputVal ? maxInputVal  : 0)).toFixed(2)
                    // return;
                }
            }

            // getStoresData(index, element.value);
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
            currentSelectedItemIndex = itemRowId;
            const docType = $("#service_id_input").val();

            const hsn_code = document.getElementById('items_dropdown_'+ itemRowId).getAttribute('hsn_code');
            const item_name = document.getElementById('items_dropdown_'+ itemRowId).getAttribute('item-name');
            const attributes = JSON.parse(document.getElementById('items_dropdown_'+ itemRowId).getAttribute('attribute-array'));
            const specs = JSON.parse(document.getElementById('items_dropdown_'+ itemRowId).getAttribute('specs'));
            const qtDetailsRow = document.getElementById('current_item_qt_no_row');
            const qtDetails = document.getElementById('current_item_qt_no');
            // const QtyChecker = document.getElementById('from_item_qty_'+itemRowId);
            // const itemQty = QtyChecker.value;
            // const maxQty = QtyChecker.getAttribute('max');
            // if(maxQty && Number(maxQty) <= Number(itemQty) && Number(maxQty)>0){
            //     $("#copy_item_section").css('pointer-events','none');
            // }
            // else{
            //     $("#copy_item_section").css('pointer-events','auto');
            // }
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


            const leaseAgreementDetailsRow = document.getElementById('current_item_land_lease_agreement_row');
            const leaseAgreementDetails = document.getElementById('current_item_land_lease_agreement');
            //assign agreement details
            let agreementNo = document.getElementById('land_lease_agreement_no_' + itemRowId)?.value;
            let leaseEndDate = document.getElementById('land_lease_end_date_' + itemRowId)?.value;
            let leaseDueDate = document.getElementById('land_lease_due_date_' + itemRowId)?.value;
            let repaymentPeriodType = document.getElementById('land_lease_repayment_period_' + itemRowId)?.value;

            if (agreementNo && leaseEndDate && leaseDueDate && repaymentPeriodType) {
                leaseAgreementDetails.style.display = "table-row";
                leaseAgreementDetails.innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Agreement Details</strong>:<span class="badge rounded-pill badge-light-primary"><strong>Agreement No</strong>: ${agreementNo}</span><span class="badge rounded-pill badge-light-primary"><strong>Lease End Date</strong>: ${leaseEndDate}</span><span class="badge rounded-pill badge-light-primary"><strong>Repayment Schedule</strong>: ${repaymentPeriodType}</span><span class="badge rounded-pill badge-light-primary"><strong>Due Date</strong>: ${leaseDueDate}</span>`;
            } else {
                leaseAgreementDetails.style.display = "none";
                leaseAgreementDetails.innerHTML = "";
            }
            //assign land plot details
            let parcelName = document.getElementById('land_lease_land_parcel_' + itemRowId)?.value;
            let plotsName = document.getElementById('land_lease_land_plots_' + itemRowId)?.value;

            let qtDocumentNo = document.getElementById('qt_document_no_'+ itemRowId);
            let qtBookCode = document.getElementById('qt_book_code_'+ itemRowId);
            let qtDocumentDate = document.getElementById('qt_document_date_'+ itemRowId);

            qtDocumentNo = qtDocumentNo?.value ? qtDocumentNo.value : '';
            qtBookCode = qtBookCode?.value ? qtBookCode.value : '';
            qtDocumentDate = qtDocumentDate?.value ? qtDocumentDate.value : '';

            // if (qtDocumentNo && qtBookCode && qtDocumentDate) {
            //     qtDetailsRow.style.display = "table-row";
            //     qtDetails.innerHTML = `<strong style = "font-size:11px; color : #6a6a6a;">Reference From</strong>:<span class="badge rounded-pill badge-light-primary"><strong>Document No: </strong>: ${qtBookCode + "-" + qtDocumentNo}</span><span class="badge rounded-pill badge-light-primary"><strong>Document Date: </strong>: ${qtDocumentDate}</span>`;

            //     if (parcelName && plotsName) {
            //         qtDetails.innerHTML =  qtDetails.innerHTML + `<span class="badge rounded-pill badge-light-primary"><strong>Land Parcel</strong>: ${parcelName}</span><span class="badge rounded-pill badge-light-primary"><strong>Plots</strong>: ${plotsName}</span>`;
            //     }
            // } else {
            //     qtDetailsRow.style.display = "none";
            //     qtDetails.innerHTML = ``;
            // }
            // document.getElementById('current_item_hsn_code').innerText = hsn_code;
            var innerHTMLAttributes = ``;
            var show_check = false;
            attributes.forEach(element => {
                var currentOption = '';
                element.values_data.forEach(subElement => {
                    if (subElement.selected) {
                        show_check=true;
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
            if (innerHTMLAttributes && show_check) {
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
                        $.ajax({
                            url: "{{route('get_item_inventory_details')}}",
                            method: 'GET',
                            dataType: 'json',
                            data: {
                                quantity: document.getElementById('to_item_qty_' + itemRowId).value ?? 0,
                                item_id: document.getElementById('items_dropdown_'+ itemRowId + '_value').value,
                                uom_id : document.getElementById('uom_dropdown_' + itemRowId).value,
                                selectedAttr : selectedItemAttr,
                            },
                            success: function(data) {
                                if (data.inv_qty && data.inv_uom) {
                                    document.getElementById('current_item_inventory').style.display = 'table-row';
                                    document.getElementById('current_item_inventory_details').innerHTML = `
                                    <span class="badge rounded-pill badge-light-primary"><strong>Inv. UOM</strong>: ${data.inv_uom}</span>
                                    <span class="badge rounded-pill badge-light-primary"><strong>Qty in ${data.inv_uom}</strong>: ${data.inv_qty}</span>
                                    `;
                                } else {
                                    document.getElementById('current_item_inventory').style.display = 'none';
                                    document.getElementById('current_item_inventory_details').innerHTML = ``;
                                }

                                if (data?.item && data?.item?.category && data?.item?.sub_category) {
                                    document.getElementById('current_item_cat_hsn').innerHTML = `
                                    <span class="badge rounded-pill badge-light-primary"><strong>Category</strong>: <span id = "item_category">${ data?.item?.category?.name}</span></span>
                                    <span class="badge rounded-pill badge-light-primary"><strong>Sub Category</strong>: <span id = "item_sub_category">${ data?.item?.sub_category?.name}</span></span>
                                    <span class="badge rounded-pill badge-light-primary"><strong>HSN</strong>: <span id = "current_item_hsn_code">${hsn_code}</span></span>
                                    `;
                                }
                                //Stocks
                                //     if (data?.stocks) {
                                //     document.getElementById('current_item_stocks_row').style.display = "table-row";
                                //     document.getElementById('current_item_stocks').innerHTML = `
                                //     <span class="badge rounded-pill badge-light-primary"><strong>Confirmed Stocks</strong>: <span id = "item_sub_category">${data?.stocks?.confirmedStockAltUom}</span></span>
                                //     <span class="badge rounded-pill badge-light-primary"><strong>Pending Stocks</strong>: <span id = "item_category">${data?.stocks?.pendingStockAltUom}</span></span>
                                //     `;
                                //     var inputQtyBox = document.getElementById('item_qty_' + itemRowId);
                                //     }
                                //  else {
                                        document.getElementById('current_item_stocks_row').style.display = "none";
                                    // }
                            },
                            error: function(xhr) {
                                console.error('Error fetching customer data:', xhr.responseText);
                            }
                        });

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
                                store_id: $("#store_from_id").val()
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
                                            qty : parseFloat(storeData.allocated_quantity_alt_uom).toFixed(2),
                                            inventory_uom_qty : parseFloat(storeData.allocated_quantity).toFixed(2)
                                        })
                                        totalValue+= parseFloat(storeData.cost_per_unit) * parseFloat(storeData.allocated_quantity_alt_uom);
                                    });
                                    var actualQty = qtyElement.value;
                                    if (actualQty > 0 && !$(`#mi_id_${itemRowId}`).val()) {
                                        valueInput.value = totalValue.toFixed(2);
                                        totalRate = parseFloat(totalValue) / parseFloat(qty ? qty : qtyElement.value);
                                        rateInput.value = parseFloat(totalRate).toFixed(2);
                                    }
                                    else if(actualQty > 0 && $(`#mi_id_${itemRowId}`).val()){

                                    }
                                    else {
                                        rateInput.value = 0;
                                        valueInput.value = 0;
                                    }
                                    // storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(storesArray)));
                                    if (callOnClick) {
                                        onItemClick(itemRowId, callOnClick);
                                    }
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
                                    rateInput.value = 0.00;
                                    valueInput.value = 0.00;
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
                    document.getElementById('total_item_store_qty').textContent = "0";
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
        function submitAttr(id) {
            var item_index = $('#attributes_table_modal').attr('item-index');
            onItemClick(item_index);
            closeModal(id);
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
                            type:'rate_contract_items',
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
                                    item_moq: item.minimum_order_qty,
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
                    var itemMoq = ui.item.item_moq;
                    console.log('Selected Item:', itemName, itemCode, itemId, itemMoq);
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
                            if (saleUom.is_purchase) {
                                console.log('check purchase');
                                uomInnerHTML += `<option value = '${saleUom.uom?.id}' ${selected == false ? "selected" : ""}>${saleUom.uom?.alias}</option>`;
                                selected = true;
                            }
                            else{
                                uomInnerHTML += `<option value = '${saleUom.uom?.id}' >${saleUom.uom?.alias}</option>`;
                            }
                        });
                    }
                    uomDropdown.innerHTML = uomInnerHTML;
                    $("#MOQ_" + index).val(itemMoq);
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
        let orderButton = document.getElementById('select_mi_button');
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

        const order = @json(isset($order) ? $order : null);
        if (order) {
            //Disable header fields which cannot be changed
            disableHeader();
            //Item Discount
            order.items.forEach((item, itemIndex) => {
                itemUomsHTML = ``;
                if (item.item.uom && item.item.uom.id) {
                    itemUomsHTML += `<option value = '${item.item.uom.id}' ${item.item.uom.id == item.uom_id ? "selected" : ""}>${item.item.uom.alias}</option>`;
                }
                item.item.alternate_uoms.forEach(singleUom => {
                    if (singleUom.is_selling) {
                        itemUomsHTML += `<option value = '${singleUom.uom.id}' ${singleUom.uom.id == item.uom_id ? "selected" : ""} >${singleUom.uom?.alias}</option>`;
                    }
                    else{
                        itemUomsHTML += `<option value = '${singleUom.uom.id}' >${singleUom.uom?.alias}</option>`;
                    }
                });
                document.getElementById('uom_dropdown_' + itemIndex).innerHTML = itemUomsHTML;
                if (itemIndex==0){
                    onItemClick(itemIndex);
                }
                if(item.item_attributes.length) {
                    setAttributesUI(itemIndex);
                }
            });
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

    document.addEventListener('DOMContentLoaded', function() {
        const order = @json(isset($order) ? $order : null);
        onSeriesChange(document.getElementById('service_id_input'), order ? false : true);
    });

    function resetParametersDependentElements(reset = true)
    {
        var selectionSection = document.getElementById('selection_section');
        if (selectionSection) {
            selectionSection.style.display = "none";
        }
        var selectionSectionSO = document.getElementById('mi_order_selection');
        if (selectionSectionSO) {
            selectionSectionSO.style.display = "none";
        }
        var selectionSectionSI = document.getElementById('pwo_order_selection');
        if (selectionSectionSI) {
            selectionSectionSI.style.display = "none";
        }
        var selectionSectionPI = document.getElementById('pi_order_selection');
        if (selectionSectionPI) {
            selectionSectionPI.style.display = "none";
        }
        // var selectionSectionDN = document.getElementById('delivery_note_selection');
        // if (selectionSectionDN) {
        //     selectionSectionDN.style.display = "none";
        // }
        // var selectionSectionLease = document.getElementById('land_lease_selection');
        // if (selectionSectionLease) {
        //     selectionSectionLease.style.display = "none";
        // }
        document.getElementById('add_item_section').style.display = "none";
        // document.getElementById('copy_item_section').style.display = "none";
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

    function implementBookParameters(paramData)
    {
        var selectedRefFromServiceOption = paramData.reference_from_service;
        var selectedBackDateOption = paramData.back_date_allowed;
        var selectedFutureDateOption = paramData.future_date_allowed;
        var invoiceToFollowParam = paramData?.invoice_to_follow;
        var returnTypeParameters = paramData?.return_type;
        var requesterTypeParameters = paramData?.requester_type;
        var order = {!! isset($order) ? json_encode($order) : "null" !!};
        // console.log(requesterTypeParameters[0]);
        // Reference From
        if (selectedRefFromServiceOption) {
            var selectVal = selectedRefFromServiceOption;
            if (selectVal && selectVal.length > 0) {
                selectVal.forEach(selectSingleVal => {
                    if(!order){
                        $('.requester').addClass('d-none');
                        $('#requester_type').val("");
                    }

                    if (selectSingleVal == 'mi') {
                        // document.getElementById('copy_item_section').style.display = "";
                        var selectionSectionElement = document.getElementById('selection_section');
                        if (selectionSectionElement) {
                            selectionSectionElement.style.display = "";
                        }
                        var selectionPopupElement = document.getElementById('mi_order_selection');
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
                        // document.getElementById('copy_item_section').style.display = "";
                        document.getElementById('add_item_section').style.display = "";
                        $("#requester_type_input").val(requesterTypeParameters);
                        $(".requester").removeClass('d-none');
                        $(".con-css").attr('colspan','13');
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

        //Return Type
        if (returnTypeParameters && returnTypeParameters.length > 0) {
            const returnTypeInput = document.getElementById('return_type_input');
            if (returnTypeInput) {
                var returnTypeHtml = ``;
                var firstReturnType = null;
                returnTypeParameters.forEach((returnType, returnTypeIndex) => {
                    if (returnTypeIndex == 0) {
                        firstReturnType = returnType;
                    }
                    returnTypeHtml += `<option value = '${returnType}'> ${returnType} </option>`
                });
                if ("{{isset($order)}}") {
                    firstReturnType = "{{isset($order) ? $order -> return_type : ''}}";
                }
                returnTypeInput.innerHTML = returnTypeHtml;

                requesterTypeParam = paramData?.requester_type?.[0];
                $("#requester_type_input").val(requesterTypeParam);
                $("#return_type_input").val(firstReturnType).trigger('input');
            }
        }
        requesterTypeParam = paramData?.requester_type?.[0];
        $("#requester_type_input").val(requesterTypeParam);
            if (order) {
            //Disable header fields which cannot be changed
            disableHeader();
            if(order.return_type != "Consumption"){
                $(".requester").addClass('d-none');
                $('.requester').prop('disabled',true);
            }
            else{
                $(".requester").removeClass('d-none');
                $('.requester').prop('disabled',false);
            }
        }
    }

    function enableDisableQtButton()
    {
        const bookId = document.getElementById('series_id_input').value;
        const bookCode = document.getElementById('book_code_input').value;
        const documentDate = document.getElementById('order_date_input').value;

        // if (bookId && bookCode && documentDate) {
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
            let orderButton = document.getElementById('select_mi_button');
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
        // }
    }

    let openPullType = 'mi';

    function openHeaderPullModal(type = 'mi')
    {
        document.getElementById('qts_data_table').innerHTML = '';
        // document.getElementById('qts_data_table_pwo').innerHTML = '';
        if (type == "mi") {
            openPullType = "mi";
            // initializeAutocompleteQt("vendor_input_mi", "vendor_id_mi_val", "vendor_mi", "company_name");
            initializeAutocompleteQt("requester_input_mi", "requester_id_mi_val", "requester_mi", "name");
            initializeAutocompleteQt("department_input_mi", "department_id_mi_val", "department_mi", "name");
            initializeAutocompleteQt("document_no_input_mi", "document_id_mi_val", "mi_document", "book_code", "document_number");
            initializeAutocompleteQt("item_name_input_mi", "item_id_mi_val", "material_issue_items", "item_code", "item_name");
        }//  else if (type == 'mo') {
        //     openPullType = "mo";
        //     initializeAutocompleteQt("book_code_input_mo", "book_id_mo_val", "book_mo", "book_code", "book_name");
        //     initializeAutocompleteQt("document_no_input_qt", "document_id_mo_val", "mo_document", "document_number", "document_number");
        //     initializeAutocompleteQt("item_name_input_mo", "item_id_mo_val", "mo_items", "item_code", "item_name");
        // }  else if (type == 'pi') {
        //     openPullType = "pi";
        //     initializeAutocompleteQt("book_code_input_pi", "book_id_pi_val", "book_pi", "book_code", "book_name");
        //     initializeAutocompleteQt("document_no_input_qt", "document_id_pi_val", "pi_document", "document_number", "document_number");
        //     initializeAutocompleteQt("item_name_input_pi", "item_id_pi_val", "pi_items", "item_code", "item_name");
        // }
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
        initializeAutocompleteQt("location_code_input_qt", "location_id_qt_val", "location", "store_name");
        // if (type === 'land-lease') {
        //     getOrders('land-lease');
        // } else {
        getOrders(openPullType);
        // }
    }

    function getOrders(type = "mi")
    {
        var qtsHTML = ``;
        let targetTable = document.getElementById('qts_data_table');
        // if (type == 'mi') {
        //     targetTable = document.getElementById('qts_data_table_mi');
        // } else if (type == "pi") {
        //     targetTable = document.getElementById('qts_data_table_pi');
        // }
        const location_id = $("#location_id_qt_val").val();
        const book_id = $(`#book_id_${type}_val`).val();
        const department_id = $(`#department_id_${type}_val`).val();
        const requester_id = $(`#requester_id_${type}_val`).val();
        const document_id = $(`#document_id_mi_val`).val();
        const item_id = $(`#item_name_input_mi`).val();
        const apiUrl = "{{route('material.return.pull.items')}}";
        var selectedIds = [];
        var mi_item = [];
        var headerRows = document.getElementsByClassName("item_header_rows");
        for (let index = 0; index < headerRows.length; index++) {
            if (type == "mo") {
                var referedId = document.getElementById('mo_id_' + index).value;
            } else if (type == "mi") {
                var referedId = document.getElementById('mi_id_' + index).value;
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
                book_id : book_id,
                document_id : document_id,
                department_id : department_id,
                requester_id : requester_id,
                item_id : item_id,
                doc_type : type,
                header_book_id : $("#series_id_input").val(),
                requester_type : $("#requester_type_input").val(),
                store_id: $("#store_to_id_input").val(),
                store_id_from: $("#store_id_input").val(),
                selected_ids : selectedIds
            },
            success: function(data) {
                if (Array.isArray(data.data) && data.data.length > 0) {
                        data.data.forEach((qt, qtIndex) => {
                            mi_item.push(qt?.id);
                            var attributesHTML = ``;
                            qt.attributes.forEach(attribute => {
                                attributesHTML += `<span class="badge rounded-pill badge-light-primary" > ${attribute.attribute_name} : ${attribute.attribute_value} </span>`;
                            });
                            qtsHTML += `
                                <tr>
                                    <td>
                                        <div class="form-check form-check-inline me-0">
                                            <input class="form-check-input pull_checkbox" type="checkbox" name="po_check" ${qt?.header?.issue_type!='Consumption' ? parseFloat(qt?.avl_stock)<=0 ? 'disabled': "" : ''} id="po_checkbox_${qtIndex}" oninput="checkQuotation(this);" doc-id = "${qt?.header.id}" current-doc-id = "0" document-id = "${qt?.header?.id}" so-item-id = "${qt.id}" balance_qty = "${qt?.avl_stock}">
                                        </div>
                                    </td>
                                    <td>${qt?.header?.book_code}</td>
                                    <td class='no-wrap'>${qt?.header?.document_number}</td>
                                    <td class='no-wrap'>${qt?.header?.document_date}</td>
                                    <td class='no-wrap'>${qt?.header?.issue_type}</td>
                                    <td class='no-wrap'>${qt?.to_store_code}</td>
                                    <td class='no-wrap'>${qt?.header?.vendor_code ? qt?.header?.vendor_code : ""}</td>
                                    <td class='no-wrap'>${qt?.header?.issue_type === "Consumption" ? (qt?.department?.name && qt?.header?.requester_type == 'Department' ? qt.department.name : qt?.user?.name && qt?.header?.requester_type == 'User' ? qt.user.name : "") : ""}</td>
                                    <td class='no-wrap'>${qt?.item_code}</td>
                                    <td>${qt?.item_name}</td>
                                    <td>${attributesHTML}</td>
                                    <td>${qt?.uom?.name}</td>
                                    <td class = 'text-align-right'>${qt?.issue_qty}</td>
                                    <td>${parseFloat(qt?.issue_qty - qt?.mr_qty).toFixed(6)}</td>
                                    <td>${parseFloat(qt?.avl_stock).toFixed(6)}</td>
                                </tr>
                            `
                        });
                }
                targetTable.innerHTML = qtsHTML;
                $("#mi_item_ids").val(JSON.stringify(mi_item));
            },
            error: function(xhr) {
                console.error('Error fetching customer data:', xhr.responseText);
                targetTable.innerHTML = '';
            }
        });

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
        const apiUrl = "{{route('material.return.process.items')}}";
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
                    currentOrders.forEach((currentOrder) => {
                        if (currentOrder) { //Set all data
                        //Disable Header
                        console.log(currentOrder);
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
                                    console.log(item);
                                    item.mr_balance_qty = parseFloat(item.issue_qty) - parseFloat(item.mr_qty);
                                    if (currentOrder.issue_type !== 'Consumption') {
                                        item.mr_balance_qty = Math.min(parseFloat(item.mr_balance_qty), parseFloat(item.avl_stock));
                                    }
                                    let itemIdKeyName = "mi_item_id";
                                    let itemIdKeyId = "mi_id";
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
                                var headerFromStoreId = currentOrder.to_store_id;
                                var headerFromStoreCode = currentOrder.to_store_code;
                                var storesFrom = @json($stores);
                                var storesTo = @json($stores);
                                var storesFromHTML = ``;
                                $("#return_from_address").text(data.return_address);
                                // var storesToHTML = ``;
                                storesFrom.forEach(store => {
                                    if (store.id == headerFromStoreId) {
                                        storesFromHTML += `<option value = "${store.id}" selected>${store.store_name}</option>`
                                    }
                                });
                                // storesTo.forEach(store => {
                                //     if (store.id == headerToStoreId) {
                                //         storesToHTML += `<option value = "${store.id}" selected>${store.store_name}</option>`
                                //     } else {
                                //         storesToHTML += `<option value = "${store.id}">${store.store_name}</option>`
                                //     }
                                // });


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

                                //     <td><input type="text" id = "item_qty_${currentOrderIndexVal}" name = "item_qty[${currentOrderIndexVal}]" oninput = "changeItemQty(this, ${currentOrderIndexVal});" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" value = "${item.mi_balance_qty}" max = "${item.mi_balance_qty}"/></td>
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
                                // headerHtml += `
                                // <div class="row align-items-center mb-1 lease-hidden sub_contracting" style = "display:none;">
                                //     <div class="col-md-3">
                                //         <label class="form-label">${data.extra_header}<span class="text-danger">*</span></label>
                                //     </div>
                                //     <div class="col-md-5">
                                //         <input type="text" value = "${data.extra_field}" class="form-control disable_on_edit" readonly id = "extra_field" name = "extra_field">
                                //     </div>
                                // </div>
                                // `;
                                mainTableItem.innerHTML += `
                                <tr id = "item_row_${currentOrderIndexVal}" class = "item_header_rows" onclick = "onItemClick(${currentOrderIndexVal});" >
                                        <td class="customernewsection-form">
                                        <div class="form-check form-check-primary custom-checkbox">
                                            <input type="checkbox" class="form-check-input item_row_checks" id="item_row_check_${currentOrderIndexVal}" del-index = "${currentOrderIndexVal}">
                                            <label class="form-check-label" for="Email"></label>
                                        </div>
                                    </td>
                                        <td class="poprod-decpt">
                                        <input readonly type="text" id = "items_dropdown_${currentOrderIndexVal}" name="item_code[${currentOrderIndexVal}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" autocomplete="off" data-name="${item?.item?.item_name}" data-code="${item?.item?.item_code}" data-id="${item?.item?.id}" hsn_code = "${item?.item?.hsn?.code}" item-name = "${item?.item?.item_name}" specs = '${JSON.stringify(item?.item?.specifications)}' attribute-array = '${JSON.stringify(item?.item_attributes_array)}'  value = "${item?.item?.item_code}" item-locations = "[]">
                                        <input type = "hidden" name = "item_id[]" id = "items_dropdown_${currentOrderIndexVal}_value" value = "${item?.item_id}"></input>
                                        <input type = "hidden" id = "mi_id_${currentOrderIndexVal}" value = "${item?.id}">
                                        <input type = "hidden" value = "${item?.id}" id = "${itemIdKeyId}_${currentOrderIndexVal}" name = "${itemIdKeyName}[${currentOrderIndexVal}]">
                                        <input type = "hidden" value = "${currentOrder?.requester_type.toLowerCase()}" id = "mi_requester_type_${currentOrderIndexVal}" name = "${currentOrder?.requester_type.toLowerCase()}[${currentOrderIndexVal}]">

                                    </td>

                                    <td class="poprod-decpt">
                                            <input type="text" id = "items_name_${currentOrderIndexVal}" name = "item_name[${currentOrderIndexVal}]" class="form-control mw-100"   value = "${item?.item?.item_name}" readonly>
                                        </td>
                                    <td class="poprod-decpt">
                                        <button id = "attribute_button_${currentOrderIndexVal}" type = "button" data-bs-toggle="modal" onclick = "setItemAttributes('items_dropdown_${currentOrderIndexVal}', ${currentOrderIndexVal});" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                                        <input type = "hidden" name = "attribute_value_${currentOrderIndexVal}" />
                                        </td>
                                    <td>
                                        <select class="form-select" readonly name = "uom_id[]" id = "uom_dropdown_${currentOrderIndexVal}">
                                        </select>
                                    </td>
                                    <td class='requester'>
                                        <div class="requester d-flex">
                                            ${
                                                currentOrder?.requester_type == "Department"
                                                ? `
                                                    <input type="text" id="department_name_${currentOrderIndexVal}" readonly name="department_name[]" class="form-control requester_name_${currentOrderIndexVal} mw-100 text-begin" value="${item?.department?.name}" />
                                                    <input type="hidden" id="department_id_${currentOrderIndexVal}" readonly name="department_id[]" class="form-control requester_name_${currentOrderIndexVal} mw-100 text-begin" value="${item?.department_id}" />
                                                `
                                                : currentOrder?.requester_type == "User"
                                                ? `
                                                <input type="text" id="user_name_${currentOrderIndexVal}" readonly name="user_name[]" class="form-control mw-100 text-begin requester_name_${currentOrderIndexVal}" value="${item?.user?.name}" />
                                                <input type="hidden" id="user_id_${currentOrderIndexVal}" readonly name="user_id[]" class="form-control mw-100 requester_name_${currentOrderIndexVal} text-begin" value="${item?.user_id}" />
                                                `
                                                : ``
                                            }
                                        </div>
                                    </td>

                                    <td><input type="text" id = "item_qty_${currentOrderIndexVal}" name = "item_qty[${currentOrderIndexVal}]" oninput = "changeItemQty(this, ${currentOrderIndexVal});" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" value = "${item?.mr_balance_qty}" max = "${item?.mr_balance_qty}"/></td>
                                    <td><input type="text" id = "item_rate_${currentOrderIndexVal}" readonly name = "item_rate[]" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" value = "${item?.rate}"/></td>
                                    <td><input type="text" id = "item_value_${currentOrderIndexVal}" readonly class="form-control mw-100 text-end item_values_input" value = "${item?.mr_balance_qty * item?.rate}" /></td>
                                    <td>
                                    <div class="d-flex">
                                        <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick = "setItemRemarks('item_remarks_${currentOrderIndexVal}');">
                                        <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span></div>
                                        ${currentOrder && currentOrder.items[currentOrderIndexVal].lotdata ?
                                            `<div class="me-50 cursor-pointer" lot-data=${ JSON.stringify(currentOrder.items[currentOrderIndexVal].lotdata)} data-bs-toggle="modal" data-bs-target="#Lot" onclick = "setItemLot(this)"><span data-bs-toggle="tooltip" data-bs-placement="top" title="Lot" class="text-primary"><i data-feather="package"></i></span></div>`
                                        : ``}
                                    </div>
                                    <input type = "hidden" id = "item_remarks_${currentOrderIndexVal}" name = "item_remarks[${currentOrderIndexVal}]" />
                                    <input type = "hidden" id = "item_lots_${currentOrderIndexVal}" name = "item_lots[${currentOrderIndexVal}]" />
                                    </td>

                                    </tr>
                                `;
                                initializeAutocomplete1("items_dropdown_" + currentOrderIndexVal, currentOrderIndexVal);

                                if(currentOrder?.requester_type && currentOrder?.issue_type == "Consumption"){
                                    $(".requester").removeClass('d-none');
                                }
                                else{
                                    $(".requester").addClass('d-none');

                                }

                                renderIcons();
                                onProcessOrder(currentOrder.issue_type,currentOrder);
                                var itemUomsHTML = ``;
                                if (item.item.uom && item.item.uom.id) {
                                    itemUomsHTML += `<option value = '${item.item.uom.id}' ${item.item.uom.id == item.uom_id ? "selected" : ""}>${item.item.uom.alias}</option>`;
                                }
                                item.item.alternate_uoms.forEach(singleUom => {
                                    if (singleUom.is_selling) {
                                        itemUomsHTML += `<option value = '${singleUom.uom.id}' ${singleUom.uom.id == item.uom_id ? "selected" : ""} >${singleUom.uom?.alias}</option>`;
                                    }
                                    else{
                                        itemUomsHTML += `<option value = '${singleUom.uom.id}' >${singleUom.uom?.alias}</option>`;
                                    }
                                });
                                document.getElementById('uom_dropdown_' + currentOrderIndexVal).innerHTML = itemUomsHTML;
                                const qtyInput = document.getElementById('item_qty_' + currentOrderIndexVal);

                                const itemCodeInput = document.getElementById('items_dropdown_' + currentOrderIndexVal);
                                const uomCodeInput = document.getElementById('uom_dropdown_' + currentOrderIndexVal);
                                const storeCodeInput = document.getElementById('item_store_' + currentOrderIndexVal);
                                itemCodeInput.addEventListener('input', function() {
                                    checkStockData(currentOrderIndexVal);
                                });
                                uomCodeInput.addEventListener('input', function() {
                                    checkStockData(currentOrderIndexVal);
                                });
                                $("#return_type_input").trigger("input");
                                currentOrderIndexVal += 1;
                                });
                            }
                            // for (let index = 0; index < currentOrderIndexVal; index++) {
                            //     getStoresData(index, document.getElementById('item_qty_' + index).value);
                            // }
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

    function setApproval()
    {
        document.getElementById('action_type').value = "approve";
        document.getElementById('approve_reject_heading_label').textContent = "Approve " + "Rate Contract";

    }
    function setReject()
    {
        document.getElementById('action_type').value = "reject";
        document.getElementById('approve_reject_heading_label').textContent = "Reject " + "Rate Contract";
    }
    function setFormattedNumericValue(element)
    {
        if(Number(element.value)>0){
            element.value = (parseFloat(element.value ? element.value  : 0)).toFixed(2)
        }
        else{
            element.value = 0;
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
   let actionUrl = "{{ route('rate.contract.amend', isset($order) ? $order -> id : 0) }}";
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
    $("#rate_contract_form").submit();
}

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
function initializeAutocompleteV(selector, type) {
    $(selector).autocomplete({
        minLength: 0,
        source: function(request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type: type+'_list'
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        console.log(item);
                        return {
                            id: item.id,
                            label: item.company_name.trim() + ' - ' + (item.vendor_code ? item.vendor_code.trim() : item.customer_code.trim()),
                            code: item.vendor_code ? item.vendor_code : item.customer_code,
                            addresses: item.addresses,
                            currency_name: item?.currency_name ? item?.currency_name : (item?.currency?.name ?? ''),
                            currency_id: item?.currency_id ? item?.currency_id : (item?.currency?.id ?? ''),
                            payment_terms_id : item?.payment_terms_id ? item?.payment_terms_id : (item?.payment_terms?.id ?? ''),
                            payment_terms_name : item?.payment_terms_name ? item?.payment_terms_name : (item?.payment_terms?.name ?? ''),
                        };
                    }));
                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                }
            });
        },
        select: function(event, ui) {
            console.log(ui , 'ui');
            var $input = $(this);
            var itemName = ui.item.value;
            var itemId = ui.item.id;
            var itemCode = ui.item.code;
            var currency_id = ui.item.currency_id;
            var currency_name = ui.item.currency_name;
            var payment_terms_id = ui.item.payment_terms_id;
            var payment_terms = ui.item.payment_terms_name;

            $input.attr('data-name', itemName);
            $input.val(itemName);
            $("#"+type+"_id").val(itemId);
            $("#"+type+"_code").val(itemCode);
            $("#hidden_currency_name").val(currency_name);
            $("#hidden_currency_id").val(currency_id);
            $("#hidden_payment_terms").val(payment_terms);
            $("#hidden_payment_terms_ids").val(payment_terms_id);

            vendorOnChange(currency_id,currency_name,payment_terms_id,payment_terms);
            return false;
        },
        change: function(event, ui) {
            console.log("changess!");
            if (!ui.item) {
                $(this).val("");
                $(this).attr('data-name', '');
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
        }
    });
}

initializeAutocompleteV("#vendor_name",$("#party_type").val());
$("#party_type").on('change',function(){
    $('#vendor_name').val('');
    setCurrencyAndPaymentTerms(null, null, null, null);
    $("#customer_id").val('');
    $("#customer_code").val('');
    $("#vendor_id").val('');
    $("#vendor_name").val('');
    $("#hidden_currency_name").val('');
    $("#hidden_currency_id").val('');
    $("#hidden_payment_terms").val('');
    $("#hidden_payment_terms_ids").val('');
    initializeAutocompleteV("#vendor_name",$("#party_type").val());
});

function setCurrencyAndPaymentTerms(currency_id, currency_name, payment_terms_id, payment_terms_name) {
    // Add currency in select currency_dropdown
    var currencyDropdown = document.getElementById('currency_dropdown');
    currencyDropdown.innerHTML = ''; // Clear previous options
    if (currency_id) {
        var option = document.createElement('option');
        option.value = currency_id;
        option.text = currency_name;
        currencyDropdown.appendChild(option);
    }

    // Add payment terms in select payment_terms_dropdown
    var paymentTermsDropdown = document.getElementById('payment_terms_dropdown');
    paymentTermsDropdown.innerHTML = ''; // Clear previous options
    if (payment_terms_id) {
        var option = document.createElement('option');
        option.value = payment_terms_id;
        option.text = payment_terms_name;
        paymentTermsDropdown.appendChild(option);
    }
}
function vendorOnChange(currency_id, currency_name, payment_terms_id, payment_terms_name)
{
    type = $("#party_type").val();
    console.log('type' , type);
    vendorId = $("#"+type+"_id").val();
    setCurrencyAndPaymentTerms(currency_id, currency_name, payment_terms_id, payment_terms_name);

    if (vendorId) {
        checkVendorContracts(document.getElementById(type+'_name'));
    } else {
        setCurrencyAndPaymentTerms('', '', '', '');
    }
}
function checkVendorContracts(elementToReset = null) {
    $type = $("#party_type").val();
    $.ajax({
        url: "{{ route('rate.contract.check') }}",
        method: 'GET',
        dataType: 'json',
        data: {
            vendor_id: $("#"+type+"_id").val(),
            start_date: $("#start_date_input").val(),
            end_date: $("#end_date_input").val(),
            type : type,

        },
        success: function(response) {
            if (response.status === 'error') {
                Swal.fire({
                    title: 'Error!',
                    text: response.message,
                    icon: 'error',
                });

                // Reset the specified element if provided
                if (elementToReset) {
                    if (elementToReset && elementToReset.id === 'vendor_name') {
                        $(elementToReset).val('');
                        // Clear currency and payment terms dropdowns
                        setCurrencyAndPaymentTerms(null, null, null, null);

                        $("#hidden_currency_name").val('');
                        $("#hidden_currency_id").val('');
                        $("#hidden_payment_terms").val('');
                        $("#hidden_payment_terms_ids").val('');
                    }
                    $(elementToReset).val('');
                }
            }
        },
        error: function(xhr) {
            console.error('Error checking vendor contracts:', xhr.responseText);
            Swal.fire({
                title: 'Error!',
                text: 'An error occurred while checking vendor contracts.',
                icon: 'error',
            });
        }
    });
}

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
            url: "{{route('rate.contract.revoke')}}",
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

    //     const currentVal = element.value;
    //     var otherVal = null;
    //     if (type === "from") {
    //         otherVal = $("#store_to_id_input").val();
    //     } else {
    //         otherVal = $("#store_id_input").val();
    //     }
    //     if (currentVal == otherVal) {
    //         Swal.fire({
    //             title: 'Error!',
    //             text: 'From and To Location cannot be same',
    //             icon: 'error',
    //         });
    //         element.value = "";
    //         return;
    //     }
    // function onItemStoreChange(element, type, index)
    // {
    //     const currentVal = element.value;
    //     var otherVal = null;
    //     if (type === "from") {
    //         otherVal = $("#item_store_to_" + index).val();
    //     } else {
    //         otherVal = $("#item_store_" + index).val();
    //     }
    //     if (currentVal == otherVal) {
    //         Swal.fire({
    //             title: 'Error!',
    //             text: 'From and To Location cannot be same',
    //             icon: 'error',
    //         });
    //         element.value = "";
    //         return;
    //     }
    // }

    // function renderToLocationInTablePopup(itemIndex, openModalFlag = false)
    // {
    //     const storeElement = document.getElementById('data_stores_to_' + itemIndex);
    //     var storesArray = [];
    //     if (storeElement.getAttribute('data-stores')) {
    //         storesArray = JSON.parse(decodeURIComponent(storeElement.getAttribute('data-stores')));
    //     } else {
    //         Swal.fire({
    //             title: 'Warning!',
    //             text: 'Please enter quantity first',
    //             icon: 'warning',
    //         });
    //         return;
    //     }
    //     if (openModalFlag) {
    //         openModal("ToLocation");
    //     }
    //     if (storesArray.length > 0) {
    //         const toLocationTable = document.getElementById('item_to_location_table');
    //         var toLocationInnerHTML = ``;
    //         var totalQty = 0;
    //         storesArray.forEach((toStore, toStoreIndex) => {
    //             toLocationInnerHTML+= `
    //             <tr>
    //             <td>${toStoreIndex+1}</td>
    //             <td>
    //             <select id = "to_location_rack_input_${itemIndex}_${toStoreIndex}" class = "form-select occupy-width"  oninput = "modifyHTMLArrayForToLocation(this,${itemIndex},${toStoreIndex}, 'rack_id');" onchange = "onFromLocationRackChange(this, ${toStoreIndex}, ${itemIndex})">
    //             ${toStore.rack_html}
    //             </select>
    //             </td>
    //             <td>
    //             <select class = "form-select occupy-width" id = "to_location_shelf_input_${itemIndex}_${toStoreIndex}" oninput = "modifyHTMLArrayForToLocation(this,${itemIndex},${toStoreIndex}, 'shelf_id');" >
    //             ${toStore.shelf_html}
    //             </select>
    //             </td>
    //             <td>
    //             <select id = "to_location_bin_input_${itemIndex}_${toStoreIndex}" class = "form-select occupy-width" oninput = "modifyHTMLArrayForToLocation(this,${itemIndex},${toStoreIndex}, 'bin_id');">
    //             ${toStore.bin_html}
    //             </select>
    //             </td>
    //             <td>
    //             <input type="text" id = "to_location_qty_${itemIndex}_${toStoreIndex}" value = "${toStore.qty}" class="form-control mw-100 text-end to_location_qty_input_${itemIndex}" oninput = "toLocationQtyChange(this, ${itemIndex}, ${toStoreIndex})"/>
    //             </td>
    //             </tr>
    //             `;
    //             totalQty += parseFloat(toStore.qty);
    //         });
    //         toLocationTable.innerHTML = toLocationInnerHTML + `
    //         <tr>
    //             <td class="text-dark text-end" colspan = "4"><strong>Total</strong></td>
    //             <td class="text-dark text-end"><strong id = "to_location_total_qty">${totalQty}</strong></td>
	// 		</tr>
    //         `;
    //         storesArray.forEach((toStore, toStoreIndex) => {
    //             $("#to_location_rack_input_" + itemIndex + "_" + toStoreIndex).val(toStore.rack_id);
    //             $("#to_location_shelf_input_" + itemIndex + "_" + toStoreIndex).val(toStore.shelf_id);
    //             $("#to_location_bin_input_" + itemIndex + "_" + toStoreIndex).val(toStore.bin_id);
    //         });
    //     }
    //     updateToLocationsTotalQty(itemIndex);
    // }

    // function onFromLocationRackChange(element, index, itemRowIndex)
    // {
    //     const storeElement = document.getElementById('data_stores_to_' + itemRowIndex);
    //     var existingStoreArray = [];
    //     if (storeElement.getAttribute('data-stores')) {
    //         existingStoreArray = JSON.parse(decodeURIComponent(storeElement.getAttribute('data-stores')));
    //     }

    //     modifyHTMLArrayForToLocation(element, itemRowIndex, index, 'rack_id');


    //     const rackId = element.value;
    //     let shelfsHTML = `<option value = "" disabled selected>Select</option>`;
    //     const relativeShelfDropdownElement = document.getElementById('to_location_shelf_input_' + itemRowIndex + "_" + index);
    //     if (rackId && relativeShelfDropdownElement) {
    //         $.ajax({
    //             url: "{{ route('store.rack.shelfs') }}",
    //             type: "GET",
    //             dataType: "json",
    //             data: {
    //                 rack_id : rackId
    //             },
    //             success: function(data) {
    //                 if (data.data.shelfs) { // RACKS DATA IS PRESENT
    //                     data.data.shelfs.forEach(shelf => {
    //                         shelfsHTML+= `<option value = '${shelf.id}'>${shelf.shelf_code}</option>`;
    //                     });
    //                 }
    //                 relativeShelfDropdownElement.innerHTML = shelfsHTML;
    //                 if (existingStoreArray[index]) {
    //                     existingStoreArray[index].shelf_html = shelfsHTML;
    //                     storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(existingStoreArray)));
    //                 }
    //             },
    //             error : function(xhr){
    //                 relativeShelfDropdownElement.innerHTML = shelfsHTML;
    //                 existingStoreArray[index].shelf_html = shelfsHTML;
    //                 storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(existingStoreArray)));
    //             }
    //         });
    //     }
    //     //Also update the array
    // }

    // function addToLocationRow()
    // {
    //     const tableInput = document.getElementById('item_to_location_table');
    //     const itemIndex = tableInput ? tableInput.getAttribute('current-item-index') : 0;
    //     const qtyInput = document.getElementById('item_qty_' + itemIndex);


    //     const itemQtysInput = document.getElementsByClassName('to_location_qty_input_' + itemIndex);
    //     var existingQty = 0;
    //     for (let index = 0; index < itemQtysInput.length; index++) {
    //         existingQty += parseFloat(itemQtysInput[index].value);
    //     }

    //     if (existingQty >= parseFloat(qtyInput ? qtyInput.value : 0)) {
    //         Swal.fire({
    //             title: 'Warning!',
    //             text: 'Cannot exceed quantity',
    //             icon: 'warning',
    //         });
    //         return;
    //     }

    //     const newQty = parseFloat(qtyInput ? qtyInput.value : 0) - existingQty;

    //     const storeElement = document.getElementById('data_stores_to_' + itemIndex);
    //     var existingStoreArray = [];
    //     if (storeElement.getAttribute('data-stores')) {
    //         existingStoreArray = JSON.parse(decodeURIComponent(storeElement.getAttribute('data-stores')));
    //     }
    //     const defaultStore = document.getElementById('item_store_to_' + itemIndex);
    //     const defaultStoreId = defaultStore.value;
    //     const defaultStoreCode = defaultStore.options[defaultStore.selectedIndex].text;
    //     let racksHTML = `<option value = "" disabled selected>Select</option>`;
    //     let binsHTML = `<option value = "" disabled selected>Select</option>`;
    //     let shelfsHTML = `<option value = "" disabled selected>Select</option>`;

    //     if (qtyInput && qtyInput.value > 0) { //Only add if qty is greater than 0
    //         $.ajax({
    //             url: "{{ route('store.racksAndBins') }}",
    //             type: "GET",
    //             dataType: "json",
    //             data: {
    //                 store_id : defaultStoreId
    //             },
    //             success: function(data) {
    //                 if (data.data.racks) { // RACKS DATA IS PRESENT
    //                     data.data.racks.forEach(rack => {
    //                         racksHTML+= `<option value = '${rack.id}'>${rack.rack_code}</option>`;
    //                     });
    //                 }
    //                 if (data.data.bins) { //BINS DATA IS PRESENT
    //                     data.data.bins.forEach(bin => {
    //                         binsHTML+= `<option value = '${bin.id}'>${bin.bin_code}</option>`;
    //                     });
    //                 }
    //                 existingStoreArray.push({
    //                     store_id : defaultStoreId,
    //                     store_code : defaultStoreCode,
    //                     rack_id : null,
    //                     rack_code : '',
    //                     rack_html : racksHTML,
    //                     shelf_id : null,
    //                     shelf_code : '',
    //                     shelf_html : shelfsHTML,
    //                     bin_id : null,
    //                     bin_code : '',
    //                     bin_html : binsHTML,
    //                     qty : newQty
    //                 });
    //                 storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(existingStoreArray)));
    //                 renderToLocationInTablePopup(itemIndex);
    //             },
    //             error : function(xhr){
    //                 console.error('Error fetching customer data:', xhr.responseText);
    //                 storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(existingStoreArray)));
    //                 renderToLocationInTablePopup(itemIndex);
    //             }
    //         });
    //     }
    // }

    // function toLocationQtyChange(element, itemIndex, index)
    // {
    //     const qtyInput = document.getElementById('item_qty_' + itemIndex);
    //     const itemQtysInput = document.getElementsByClassName('to_location_qty_input_' + itemIndex);

    //     var existingQty = 0;
    //     for (let storeIndex = 0; storeIndex < itemQtysInput.length; storeIndex++) {
    //         existingQty += parseFloat(itemQtysInput[storeIndex].value);
    //     }

    //     if (existingQty > parseFloat(qtyInput ? qtyInput.value : 0)) {
    //         Swal.fire({
    //             title: 'Warning!',
    //             text: 'Cannot exceed quantity',
    //             icon: 'warning',
    //         });
    //         element.value = 0;
    //         return;
    //     }
    //     modifyHTMLArrayForToLocation(element, itemIndex, index, 'qty');
    //     updateToLocationsTotalQty(itemIndex);
    // }

    // function openToLocationModal(index) {
    //     const tableInput = document.getElementById('item_to_location_table');
    //     if (tableInput) {
    //         tableInput.setAttribute('item_to_location_table', index);
    //     }
    //     renderToLocationInTablePopup(index, true);
    // }

    // function modifyHTMLArrayForToLocation(element, itemIndex, index, key)
    // {
    //     const storeElement = document.getElementById('data_stores_to_' + itemIndex);
    //     var existingStoreArray = [];
    //     if (storeElement.getAttribute('data-stores')) {
    //         existingStoreArray = JSON.parse(decodeURIComponent(storeElement.getAttribute('data-stores')));
    //     }
    //     if (existingStoreArray[index]) {
    //         existingStoreArray[index][key] = element.value;
    //     }
    //     storeElement.setAttribute('data-stores', encodeURIComponent(JSON.stringify(existingStoreArray)));
    // }

    // function updateToLocationsTotalQty(itemIndex)
    // {
    //     const toLocationTotalQtyDiv = document.getElementById('to_location_total_qty');
    //     const itemQtysInput = document.getElementsByClassName('to_location_qty_input_' + itemIndex);
    //     var existingQty = 0;
    //     for (let storeIndex = 0; storeIndex < itemQtysInput.length; storeIndex++) {
    //         existingQty += parseFloat(itemQtysInput[storeIndex].value);
    //     }
    //     if (toLocationTotalQtyDiv) {
    //         toLocationTotalQtyDiv.textContent = existingQty;
    //     }
    // }

    // function onReturnTypeChange(element)
    // {
    //     const selectedType = element.value;
    //     if (selectedType == 'Location Transfer') {
    //         implementReturnTypeChange('location_transfer','.sub_contracting');
    //     } else if (selectedType == 'Sub Contracting') {
    //         implementReturnTypeChange('sub_contracting','.location_transfer');
    //     }
    // }

    // function implementReturnTypeChange(targetClass, querySelectorOtherClasses)
    // {
    //     var targetElements = document.getElementsByClassName(targetClass);
    //     for (let index = 0; index < targetElements.length; index++) {
    //         targetElements[index].style.removeProperty("display");
    //     }
    //     var otherElements = document.querySelectorAll(querySelectorOtherClasses);
    //     for (let index = 0; index < otherElements.length; index++) {
    //         otherElements[index].style.display = "none";
    //     }
    //     $("#vendor_id_input").trigger('input');
    // }

    // function onVendorChange(element)
    // {
    //     const vendorId = element.value;
    //     const vendorInput = document.getElementById('vendor_address_id_input');
    //     let vendorIdInputHTML = ``;
    //     if (vendorId) {
    //         $.ajax({
    //             url: "{{ route('material.return.vendor.addresses') }}",
    //             type: "GET",
    //             dataType: "json",
    //             data: {
    //                 vendor_id : vendorId
    //             },
    //             success: function(data) {
    //                 if (data.data && (data.data.length > 0)) { // RACKS DATA IS PRESENT
    //                     data.data.forEach((address, index) => {
    //                         if ("{{isset($order) && isset($order -> vendor_shipping_address)}}") {
    //                             const vendorAddressId = "{{isset($order) ? $order -> vendor_shipping_address ?-> id : ''}}";
    //                             if (vendorAddressId == address.id) {
    //                                 vendorIdInputHTML += `<option selected value = '${address.id}'>${address.display_address}</option>`;
    //                             } else {
    //                                 vendorIdInputHTML += `<option value = '${address.id}'>${address.display_address}</option>`;
    //                             }
    //                         } else {
    //                             vendorIdInputHTML += `<option value = '${address.id}'>${address.display_address}</option>`;

    //                         }
    //                     });
    //                     vendorInput.innerHTML = vendorIdInputHTML;
    //                 } else {
    //                     vendorInput.innerHTML = vendorIdInputHTML;
    //                     element.value = "";
    //                     Swal.fire({
    //                         title: 'Error!',
    //                         text: 'No Shipping address found',
    //                         icon: 'error',
    //                     });
    //                     return;
    //                 }

    //             },
    //             error : function(xhr){
    //                 console.error('Error fetching customer data:', xhr.responseText);
    //                 vendorInput.innerHTML = vendorIdInputHTML;
    //                 element.value = "";
    //                 Swal.fire({
    //                     title: 'Error!',
    //                     text: 'No Shipping address found',
    //                     icon: 'error',
    //                 });
    //                 return;
    //             }
    //         });
    //     }
    // }

    function checkAllMi(element)
    {
        const selectableElements = document.getElementsByClassName('pull_checkbox');
        for (let index = 0; index < selectableElements.length; index++) {
            if (!selectableElements[index].disabled) {
                selectableElements[index].checked = element.checked;
                if (element.checked) {
                    checkQuotation(selectableElements[index]);
                }
            }
        }
    }
    function setFromLocationStoreOnItem(itemId, itemIndex)
    {
        const fromLocId = $("#store_from_id_input").val();
        $.ajax({
            url: "{{route('subStore.get.from.stores')}}",
            method: 'GET',
            dataType: 'json',
            data: {
                store_id : fromLocId,
                item_id : itemId,
                type : ['Stock', 'Shop floor']
            },
            success: function(data) {
                if (data.status === 200) {
                    currentFromSubStoreArray = data.data;
                    fromSubStoreDependencyRender();
                    renderStoreOnItemChange('from', itemIndex);
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
    function setToLocationStoreOnItem(itemId, itemIndex)
    {
        let toLocId = $("#store_to_id_input").val();
        if ($("#issue_type_input").val() == 'Sub Contracting')
        {
            toLocId = $("#vendor_store_id_input").val();
        }
        $.ajax({
            url: "{{route('subStore.get.from.stores')}}",
            method: 'GET',
            dataType: 'json',
            data: {
                store_id : toLocId,
                item_id : itemId,
                type : ['Stock', 'Shop floor', 'Other']
            },
            success: function(data) {
                if (data.status === 200) {
                    currentToSubStoreArray = data.data;
                    toSubStoreDependencyRender();
                    renderStoreOnItemChange('to', itemIndex);
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
    function renderStoreOnItemChange(type = 'from', index)
    {
        let currentSubLocArray = [];
        let targetDocument = null;
        if (type === 'from') {
            currentSubLocArray = currentFromSubStoreArray;
            targetDocument = document.getElementById('item_sub_store_from_' + index);
        } else {
            currentSubLocArray = currentToSubStoreArray;
            targetDocument = document.getElementById('item_sub_store_to_' + index);
        }
        let newInnerHTML = ``;
        currentSubLocArray.forEach(subLoc => {
            newInnerHTML += `<option value = "${subLoc.id}">${subLoc.name}</option>`
        });
        targetDocument.innerHTML = newInnerHTML;
    }

    function onItemFromStoreChange(element, index)
    {
        const currentVal = $("#store_from_id_input").val() + "-" + element.value;
        const otherVal = $("#store_to_id_input").val() + "-" + $("#item_sub_store_to_" + index).val();
        if (currentVal === otherVal)
        {
            element.value = "";
            Swal.fire({
                title: 'Error!',
                text: "From and to Store cannot be same",
                icon: 'error',
            });
            return;
        }
    }

    function onItemToStoreChange(element, index)
    {
        const currentVal = $("#store_to_id_input").val() + "-" + element.value;
        const otherVal = $("#store_from_id_input").val() + "-" + $("#item_sub_store_from_" + index).val();
        if (currentVal === otherVal)
        {
            element.value = "";
            Swal.fire({
                title: 'Error!',
                text: "From and to Store cannot be same",
                icon: 'error',
            });
            return;
        }
    }
    //Attribute UI function
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
        attributesArray = safeParseAttributes(attributesArray);
        if (attributesArray.length == 0) {
            return;
        }
        let attributeUI = `<div id="attribute_button_${currentItemIndex}" data-bs-toggle="modal" onclick = "setItemAttributes('items_dropdown_${currentItemIndex}', ${currentItemIndex});" data-bs-target="#attribute" style = "white-space:nowrap; cursor:pointer;">`;
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
            console.log(attrArr);
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
                    currentStringLength += Number(attrVal?.value?.length);
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
                    onclick = "setItemAttributes('items_dropdown_${currentItemIndex}', '${currentItemIndex}', false);"
                    data-bs-target="#attribute"
                    class="btn p-25 btn-sm btn-outline-secondary"
                    style="font-size: 10px">Attributes</button>
                <input type = "hidden" name = "attribute_value_${currentItemIndex}" />
            `;
        }

    }
    // Opens import modal with store/type/header context
    function openImportItemModal(type, psvHeaderId = null) {
        const vendorId = $('#vendor_id').val();
        const customerId = $('#customer_id').val();
        if (!vendorId && !customerId) {
            Swal.fire({
                title: 'Error!',
                text: 'Please select a Vendor/Customer first.',
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
        form.append(`<input type="hidden" name="type" value="${type}">`);
        form.append(`<input type="hidden" name="psv_header_id" value="${psvHeaderId ?? ''}">`);
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

        // Add any extra data if needed (store_id/type/psv_header_id)
        $('#importItemModal input[type=hidden]').each(function() {
            formData.append($(this).attr('name'), $(this).val());
        });
        $('#upload-error').hide().html('');
        $('#uploadProgress').removeClass('d-none');
        $('#uploadProgressBar').css('width', '0%').text('0%');

        $.ajax({
            url: "{{ route('generic.import.save', ['alias' => App\Helpers\ConstantHelper::RC_SERVICE_ALIAS ]) }}",
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
        const tbody = $('#item_header');
        tbody.empty(); // Clear table before appending

        let currentIndex = tbody.find('tr').length;

        validRows.forEach((row, i) => {
            const newIndex = currentIndex + i;

            const itemId        = row.item_id        || '';
            const itemCode      = row.item_code      || '';
            const itemName      = row.item_name      || '';
            const attribute     = row.attribute      || '';
            console.log(row.item_attribute_array);
            const attrArray = row.item_attribute_array 
                ? JSON.stringify(row.item_attribute_array) 
                : '[]';
            const uomId         = row.uom_id         || '';
            const uomName       = row.uom_name       || '';
            const moq           = row.MOQ            || '';
            const fromQty       = row.from_qty       || "0";
            const toQty         = row.to_qty         || "0";
            const rate          = row.rate           || "0"; 
            const leadTime      = row.lead_time      || '';
            const effFrom       = row.effective_from || '';
            const effUpto       = row.effective_upto || '';
            const specifications= row.specifications || '';
            const hsnCode       = row.hsn_code       || "0";
            const remarks       = row.remarks        || '';

            const currency = document.getElementById('currency_dropdown') ?
                document.getElementById('currency_dropdown').innerHTML : '<option value="1" selected>INR</option>';
            // Build row element
            const newItemRow = document.createElement('tr');
            newItemRow.id = `item_row_${newIndex}`;
            newItemRow.innerHTML = `
                <td class="customernewsection-form">
                    <div class="form-check form-check-primary custom-checkbox">
                        <input type="checkbox" class="form-check-input item_row_checks" 
                            id="item_row_check_${newIndex}" del-index="${newIndex}">
                        <label class="form-check-label" for="item_row_check_${newIndex}"></label>
                    </div>
                </td>
                <td class="poprod-decpt">
                    <input type="text" id="items_dropdown_${newIndex}" 
                        name="item_code[${newIndex}]" 
                        class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input"
                        value="${itemCode}" autocomplete="off"
                        data-name="${itemName}" data-code="${itemCode}" data-id="${itemId}"
                        hsn_code="${hsnCode}" item_name="${itemName}" attribute-array='${attrArray}' specs="${specifications}" item-locations="[]">
                    <input type="hidden" name="item_id[${newIndex}]" id="items_dropdown_${newIndex}_value" value="${itemId}">
                </td>
                <td class="poprod-decpt">
                    <input type="text" id="items_name_${newIndex}" name="item_name[${newIndex}]"
                        class="form-control mw-100" value="${itemName}" readonly>
                </td>
                <td class="poprod-decpt" id="attribute_section_${newIndex}">
                    <button id="attribute_button_${newIndex}" type="button"
                            data-bs-toggle="modal" data-bs-target="#attribute"
                            onclick="setItemAttributes('items_dropdown_${newIndex}', ${newIndex});"
                            class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">
                            Attributes
                    </button>
                    <input type="hidden" name="attribute_value_${newIndex}" value="${attribute}">
                </td>
                <td>
                    <select class="form-select" name="uom_id[]" id="uom_dropdown_${newIndex}">
                        <option value="${uomId}" selected>${uomName}</option>
                    </select>
                </td>
                <td><input type="text" id="MOQ_${newIndex}" name="MOQ[]" 
                        class="form-control mw-100 text-end" value="${moq}"></td>
                <td><input type="text" id="from_item_qty_${newIndex}" name="from_item_qty[${newIndex}]"
                        class="form-control mw-100 text-end"
                        oninput="changeItemQty(this, ${newIndex}, 'from');" 
                        onblur="setFormattedNumericValue(this);" value="${fromQty}"></td>
                <td><input type="text" id="to_item_qty_${newIndex}" name="to_item_qty[${newIndex}]"
                        class="form-control mw-100 text-end"
                        oninput="changeItemQty(this, ${newIndex}, 'to');" 
                        onblur="setFormattedNumericValue(this);" value="${toQty}"></td>
                <td><input type="text" id="item_rate_${newIndex}" name="item_rate[${newIndex}]"
                        class="form-control mw-100 text-end" 
                        onblur="setFormattedNumericValue(this);" value="${rate}"></td>
                <td><input type="text" id="item_lead_${newIndex}" name="item_lead[${newIndex}]"
                        class="form-control mw-100 text-end" value="${leadTime}"></td>
                <td class="d-none">
                    <select class="form-select" name="item_currency_id[]" id="item_currency_${newIndex}">
                        ${currency}
                    </select>
                </td>
                <td><input type="date" id="effective_from_${newIndex}" name="effective_from[]"
                        class="form-control mw-100 item_values_input" value="${effFrom}"></td>
                <td><input type="date" id="effective_to_${newIndex}" name="effective_to[]"
                        class="form-control mw-100 item_values_input" value="${effUpto}"></td>
                <td>
                    <div class="d-flex">
                        <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks"
                            onclick="setItemRemarks('item_remarks_${newIndex}');">
                            <span data-bs-toggle="tooltip" title="Remarks" class="text-primary">
                                <i data-feather="file-text"></i>
                            </span>
                        </div>
                    </div>
                    <input type="hidden" id="item_remarks_${newIndex}" 
                        name="item_remarks[${newIndex}]" value="${remarks}">
                </td>
            `;

            tbody.append(newItemRow);

            // Post-append initializations
            initializeAutocomplete1("items_dropdown_" + newIndex, newIndex);
            renderIcons();
            disableHeader();

            const itemCodeInput = document.getElementById('items_dropdown_' + newIndex);
            const uomCodeInput = document.getElementById('uom_dropdown_' + newIndex);

            itemCodeInput.addEventListener('input', () => checkStockData(newIndex));
            uomCodeInput.addEventListener('input', () => checkStockData(newIndex));
            setAttributesUI(newIndex);
            setDateDefaults(newIndex);
            $("#return_type_input").trigger("input");
        });

        $('#importItemModal').modal('hide');
    });
    function excelDateToJSDate(serial) {
        if (!serial || isNaN(serial)) return "";
        // Excel's epoch starts 1899-12-30
        const utc_days = Math.floor(serial - 25569); 
        const utc_value = utc_days * 86400; 
        const date_info = new Date(utc_value * 1000);
        // Return yyyy-mm-dd
        return date_info.toISOString().split('T')[0];
    }

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
            url: "{{ route('generic.import.sample.download', ['alias' => App\Helpers\ConstantHelper::RC_SERVICE_ALIAS]) }}",
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
