@extends('layouts.app')

@section('content')

<form method="POST" data-completionFunction="disableHeader" 
class="ajax-input-form sales_module_form transport_invoice"
   action="{{ route('sale.transporterInvoice.store') }}" 
   data-redirect="{{ $redirect_url }}" id="transport_invoice_form"
   enctype='multipart/form-data'>
   <div class="app-content content ">
   <div class="content-overlay"></div>
   <div class="header-navbar-shadow"></div>
   <div class="content-wrapper container-xxl p-0">
   <div class="content-header pocreate-sticky">
    @if(!empty($order))
    <input type="hidden" name="transport_invoice_id" value="{{ request()->id }}">

    @endif
      <div class="row">
         @include('layouts.partials.breadcrumb-add-edit', [
         'title' => 'Transporter Invoice',
         'menu' => 'Home',
         'menu_url' => url('home'),
         'sub_menu' => 'Add New',
         ])
         <input type="hidden" value="draft" name="document_status" id="document_status" />
          <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
						<div class="form-group breadcrumb-right" id = "buttonsDiv">
                        @if(!isset(request() -> revisionNumber))
                        <button type = "button" onclick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
                            @if (isset($order))
                                @if($buttons['print'])
                                <!-- <button class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer">
                                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                        <rect x="6" y="14" width="12" height="8"></rect>
                                    </svg>
                                    Print  <i class="fa-regular fa-circle-down"></i>
                                </button> -->
                                  <a href="{{ route('sale.transporterInvoice.print', $order->id) }}" target="_blank">
                                      <button class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light dropdown-toggle" type="button">
                                          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" 
                                              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                                              class="feather feather-printer">
                                              <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                              <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                              <rect x="6" y="14" width="12" height="8"></rect>
                                          </svg>
                                          Print
                                      </button>
                                  </a>

                                  @if(isset($einvoice) && !$einvoice->ewb_no && $order -> total_amount > 50000)
                                      <a type="button" class="btn btn-primary btn-sm" id="eWayBillBtn" href="#" onclick = "generateEwayBill();">
                                          <i data-feather="check-circle"></i> Generate Eway Bill
                                      </a>
                                  @endif
                                  @if(isset($order) && $order -> document_type == App\Helpers\ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS && $order -> document_status != App\Helpers\ConstantHelper::DRAFT && $order -> document_status != App\Helpers\ConstantHelper::SUBMITTED)
                                      <a type="button" class="btn btn-primary btn-sm" id="eWayBillBtn" href="#" onclick = "generateEwayBill();">
                                          <i data-feather="check-circle"></i> Generate Eway Bill
                                      </a>
                                  @endif
                                  @if($order->document_status != App\Helpers\ConstantHelper::DRAFT)

                                  <button type = "button" data-target = "#sendMail" onclick = "sendMailTo();" data-toggle = "modal" class="btn btn-primary btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i data-feather="mail"></i> E-Mail</button>
                                  @endif
                                  <!-- @if(isset($order) && $order->delivery_status == 0)
                                  <button type = "button" data-bs-toggle="modal" data-bs-target="#podModal" onclick = "setPOD();" class="btn btn-success btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i class="fa-solid fa-truck-fast"></i> POD</button>
                                  @endif -->
                                @endif
                                  <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                      @php
                                          if($order->document_type == "lr"){
                                              $options=['Tax Invoice', 'Tax Invoice Attribute Grouped'];
                                          }
                                          elseif($order->document_type == "lr-dnote")
                                          {
                                              $options = [
                                                  'Tax Invoice',
                                                  'Tax Invoice Attribute Grouped',
                                                  'Delivery Note',
                                              ];
                                          }
                                          else if ($order->document_type == "dnote"){
                                              $options = ['Delivery Note'];
                                          } else if ($order -> document_type == 'lrinv') {
                                              $options = ['Tax Invoice'];
                                          }
                                          else if ($order->document_type == "ti"){
                                          
                                              $options = ['Lorry Receipt'];
                                          }
                                        
                                      @endphp
                                      @foreach ($options as $key)
                                          <li>
                                              <a class="dropdown-item" href="{{ route('sale.invoice.generate-pdf', [$order->id, $key, 'type' => str_contains($key, 'Attribute Grouped') ? 'grouped' : '']) }}" target="_blank">{{ $key }}</a>
                                          </li>
                                      @endforeach
                                  </ul>
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
                                <!-- @if($buttons['post'])
                                <button id = "postButton" onclick = "onPostVoucherOpen();" type = "button" class="btn btn-warning btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Post</button>
                                @endif -->
                                @if($buttons['voucher'])
                                <button type = "button" onclick = "onPostVoucherOpen('posted');" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg> Voucher</button>
                                @endif
                                @if($buttons['revoke'])
                                    <button id = "revokeButton" type="button" onclick = "revokeDocument();" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='rotate-ccw'></i> Revoke</button>
                                @endif

                                @if($enableEinvoice && !$einvoice && in_array($order -> document_status, ['approved', 'approval_not_required', 'posted']))
                                <a type="button" class="btn btn-primary btn-sm" id="eEInvoiceBtn" onclick = "generateEInvoice('{{$order -> id}}')">
                                    <i data-feather="check-circle"></i> Generate E-Invoice
                                </a>
                                @endif
                                @if( in_array($order -> document_status, ['approved', 'approval_not_required']) && $order->type == 1)
                                <a type="button" class="btn btn-primary btn-sm" id="eEInvoiceBtn" onclick = "openModal('generateinvoice')">
                                    <i data-feather="check-circle"></i> Generate Invoice
                                </a>
                                @endif
                                @if( in_array($order -> document_status, ['approved', 'approval_not_required']) && $order->type == 2)
                                <button id = "postButton" onclick = "onPostVoucherOpen();" type = "button" class="btn btn-warning btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Post</button>
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
                  <div class="card-body customernewsection-form" id="main_so_form">
                     <div class="row">
                        <div class="col-md-6">
                           <div
                              class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
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
                              <input type="hidden" name="type" id="type_hidden_input"></input>
                              <div class="row align-items-center mb-1">
                                  <div class="col-md-3">
                                    <label class="form-label">Series <span
                                        class="text-danger">*</span></label>
                                  </div>
                                  <div class="col-md-5">
                                    <select class="form-select disable_on_edit"
                                        onChange="getDocNumberByBookId(this,false);" name="book_id"
                                        id="series_id_input" disabled>
                                        @foreach ($series as $currentSeries)
                                        <option value="{{ $currentSeries->id }}" @if($currentSeries->id == $order->book_id) selected @endif>
                                          {{ $currentSeries->book_code }}
                                        </option>
                                        @endforeach
                                    </select>
                                    
                                  </div>
                                  <input type="hidden" name="book_code" id="book_code_input" value="{{$order->book_code}}"></input>
                              </div>
                              <div class="row align-items-center mb-1">
                                  <div class="col-md-3">
                                    <label class="form-label">Document No <span
                                        class="text-danger">*</span></label>
                                  </div>
                                  <div class="col-md-5">
                                    <input type="text" class="form-control disable_on_edit" readonly
                                         value="{{$order->document_number}}">
                                  </div>
                              </div>
                              <div class="row align-items-center mb-1">
                                  <div class="col-md-3">
                                    <label class="form-label">Document Date <span
                                        class="text-danger">*</span></label>
                                  </div>
                                  <div class="col-md-5">
                                    <input type="date" class="form-control" name="document_date"
                                        id="order_date_input" oninput="onDocDateChange();"
                                        value="{{$order->document_date}}"
                                        min="{{ $current_financial_year['start_date'] }}"
                                        max="{{ $current_financial_year['end_date'] }}" required>
                                  </div>
                              </div>
                              <div class="row align-items-center mb-1 lease-hidden">
                                  <div class="col-md-3">
                                    <label class="form-label">Location<span
                                        class="text-danger">*</span></label>
                                  </div>
                                  <div class="col-md-5">
                                    <select class="form-select disable_on_edit" name="store_id"
                                          id="store_id_input" oninput="onHeaderLocationChange(this);">
                                      @foreach ($stores as $store)
                                          <option value="{{ $store->id }}"
                                                  data-name="{{ $store->store_name }}"
                                                  display-address="{{ $store->address?->display_address }}"
                                                  @if($store->id == $order->store_id) selected @endif>
                                              {{ $store->store_name }}
                                          </option>
                                      @endforeach
                                  </select>

                                  </div>
                              </div>
                              <!-- <div class="row align-items-center mb-1">
                                  <div class="col-md-3">
                                    <label class="form-label">Reference No </label>
                                  </div>
                                  <div class="col-md-5">
                                    <input type="text" name="reference_no" class="form-control"
                                        id="reference_no_input" data-value="{{$order->reference_number}}" value="{{$order->reference_number}}">
                                  </div>
                              </div> -->
                             <div class="row align-items-center mb-1">
                              <div class="col-md-3">
                                 <label class="form-label">Type </label>
                              </div>
                              <div class="col-md-5">
                                 <select class="form-select disable_on_edit" name="type"
                                    id="type" readonly>
                                    <option
                                       value="1"
                                       @if($order->type == 1) selected @endif
                                       data-name
                                       = "Performa Invoice">
                                       Performa Invoice
                                    </option>
                                    <option
                                       value="1"
                                       @if($order->type == 2) selected @endif
                                       data-name
                                       = "Invoice">
                                       Invoice
                                    </option>
                                 </select>
                              </div>
                           </div>
                            
                              <div class="row align-items-center mb-1 can_hide" id="selection_section">
                                  <div class="col-md-3">
                                    <label class="form-label">Reference From</label>
                                  </div>
                                  <div class="col-md-3 action-button" id="lorry_receipt_selection">
                                    <button type="button" id="select_lorry_button"
                                        data-bs-toggle="modal" data-bs-target="#pullPopUpLr"
                                        class="btn btn-outline-primary btn-sm mb-0">
                                    <i data-feather="plus-square"></i> Lorry Receipt
                                    </button>
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
                                                   @elseif($approvalHist->approval_type == 'Delivered')
                                                   <span class="badge rounded-pill badge-light-success">Delivered</span>
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
                                    <label class="form-label">Customer <span
                                       class="text-danger">*</span></label>
                                    <input type="text" id="customer_code_input" disabled
                                       placeholder="Select"
                                       class="form-control mw-100 ledgerselecct ui-autocomplete-input disable_on_edit"
                                       autocomplete="off"
                                       onblur="onChangeCustomer('customer_code_input', true)" value="{{$order->customer->company_name}}">
                                    <input type="hidden" name="customer_id"
                                       id="customer_id_input" value="{{$order->customer_id}}"></input>
                                    <input type="hidden" name="customer_code"
                                       id="customer_code_input_hidden" value="{{$order->customer_code}}"></input>
                                 </div>
                              </div>
                              <div class="col-md-3">
                                 <div class="mb-1">
                                    <label class="form-label">Phone No.<span
                                       class="text-danger">*</span></label>
                                    <input type="text"
                                       class="form-control ledgerselecct ui-autocomplete-input"
                                       autocomplete="off" id="customer_phone_no_input"
                                       name="customer_phone_no" value="{{$order->customer_phone_no}}" />
                                 </div>
                              </div>
                              <div class="col-md-3">
                                 <div class="mb-1">
                                    <label class="form-label">Email<span
                                       class="text-danger">*</span></label>
                                    <input type="text"
                                       class="form-control ledgerselecct ui-autocomplete-input"
                                       autocomplete="off" id="customer_email_input"
                                       name="customer_email" value="{{$order->customer_email}}" />
                                 </div>
                              </div>
                              <!-- <div class="col-md-3">
                                 <div class="mb-1">
                                    <label class="form-label">Consignee Name</label>
                                    <input type="text"
                                       class="form-control ledgerselecct ui-autocomplete-input"
                                       autocomplete="off" id="consignee_name_input"
                                       name="consignee_name" value="{{$order->consignee_name}}" />
                                 </div>
                              </div> -->
                              <div class="col-md-3">
                                 <div class="mb-1">
                                    <label class="form-label">GSTIN No.</label>
                                    <input type="text"
                                       class="form-control ledgerselecct ui-autocomplete-input"
                                       autocomplete="off" id="customer_gstin_input"
                                       name="customer_gstin" value="{{$order->customer_gstin}}" />
                                 </div>
                              </div>
                              <div class="col-md-3">
                                 <div class="mb-1">
                                    <label class="form-label">Currency <span
                                       class="text-danger">*</span></label>
                                    <select class="form-select disable_on_edit"
                                       id="currency_dropdown" name="currency_id" readonly>
                                       <option value="">Select</option>
                                       <option value="{{$order->currency->id}}" selected>{{$order->currency->name}}</option>
                                    </select>
                                 </div>
                                 <input type="hidden" name="currency_code"
                                    id="currency_code_input" value="{{$order->currency_code}}"></input>
                              </div>
                              <div class="col-md-3">
                                 <div class="mb-1">
                                    <label class="form-label">Payment Terms <span
                                       class="text-danger">*</span></label>
                                    <select class="form-select disable_on_edit"
                                       id="payment_terms_dropdown" name="payment_terms_id"
                                       readonly>
                                       <option value="{{$order->payment_terms->id}}" selected>{{$order->payment_terms->name}}</option>
                                       <option value="">Select</option>
                                    </select>
                                 </div>
                                 <input type="hidden" name="payment_terms_code"
                                    id="payment_terms_code_input" value="{{$order->payment_terms_code}}"></input>
                              </div>
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-md-6">
                              <div class="customer-billing-section h-100">
                                 <p>Billing Address&nbsp;<span class="text-danger">*</span>
                                    <!-- <a href="javascript:;" id="billAddressEditBtn"
                                       class="float-end"><i data-feather='edit-3'></i></a> -->
                                 </p>
                                 <div class="bilnbody">
                                    <div class="genertedvariables genertedvariablesnone">
                                       <div class="mrnaddedd-prim" id="current_billing_address">{{$order->billing_address_details->display_address}}
                                       </div>
                                       <input type="hidden" id="current_billing_address_id"  value="{{$order->billing_address_details->id}}"></input>
                                       <input type="hidden" id="current_billing_country_id"
                                          name="billing_country_id" value="{{$order->billing_address_details->country_id}}"></input>
                                       <input type="hidden" id="current_billing_state_id"
                                          name="billing_state_id" value="{{$order->billing_address_details->state_id}}"></input>
                                       <input type="hidden" name="new_billing_country_id"
                                          id="new_billing_country_id" value="">
                                       <input type="hidden" name="new_billing_state_id"
                                          id="new_billing_state_id" value="">
                                       <input type="hidden" name="new_billing_city_id"
                                          id="new_billing_city_id" value="">
                                       <input type="hidden" name="new_billing_address"
                                          id="new_billing_address" value="">
                                       <input type="hidden" name="new_billing_type"
                                          id="new_billing_type" value="">
                                       <input type="hidden" name="new_billing_pincode"
                                          id="new_billing_pincode" value="">
                                       <input type="hidden" name="new_billing_phone"
                                          id="new_billing_phone" value="">
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <!-- <div class="col-md-4">
                              <div class="customer-billing-section">
                                 <p>Shipping Address&nbsp;<span class="text-danger">*</span><span
                                    id="same_checkbox_as_billing"
                                    style="margin-left:120px; font-weight:100;"></span>
                                    
                                 </p>
                                 <div class="bilnbody">
                                    <div class="genertedvariables genertedvariablesnone">
                                       <div class="mrnaddedd-prim" id="current_shipping_address">{{$order->shipping_address_details->display_address}}
                                       </div>
                                       <input type="hidden"
                                          id="current_shipping_address_id" value="{{$order->shipping_address_details->id}}"></input>
                                       <input type="hidden" id="current_shipping_country_id"
                                          name="shipping_country_id" value="{{$order->shipping_address_details->country_id}}"></input>
                                       <input type="hidden" id="current_shipping_state_id"
                                          name="shipping_state_id" value="{{$order->shipping_address_details->state_id}}"></input>
                                       <input type="hidden" name="new_shipping_country_id"
                                          id="new_shipping_country_id" value="">
                                       <input type="hidden" name="new_shipping_state_id"
                                          id="new_shipping_state_id" value="">
                                       <input type="hidden" name="new_shipping_city_id"
                                          id="new_shipping_city_id" value="">
                                       <input type="hidden" name="new_shipping_address"
                                          id="new_shipping_address" value="">
                                       <input type="hidden" name="new_shipping_type"
                                          id="new_shipping_type" value="">
                                       <input type="hidden" name="new_shipping_pincode"
                                          id="new_shipping_pincode" value="">
                                       <input type="hidden" name="new_shipping_phone"
                                          id="new_shipping_phone" value="">
                                    </div>
                                 </div>
                              </div>
                           </div> -->
                           <div class="col-md-6">
                              <div class="customer-billing-section">
                                 <p>Pickup Address&nbsp;<span class="text-danger">*</span>
                                 </p>
                                 <div class="bilnbody">
                                    <div class="genertedvariables genertedvariablesnone">
                                       <div class="mrnaddedd-prim" id="current_pickup_address">{{$order->location_address_details->display_address}}
                                       </div>
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
                                    <h4 class="card-title text-theme">LR Detail</h4>
                                    <p class="card-text">Fill the details</p>
                                 </div>
                              </div>
                              <div class="col-md-6 text-sm-end" id="add_delete_item_section">
                                 <a href="#" onclick="deleteItemRows();"
                                    class="btn btn-sm btn-outline-danger me-50">
                                 <i data-feather="x-circle"></i> Delete</a>
                                 <a href="#" onclick="addItemRow();" id="add_item_section"
                                    style="display:none;" class="btn btn-sm btn-outline-primary">
                                 <i data-feather="plus"></i> Add Item</a>
                              </div>
                           </div>
                           <div class="row">
                              <div class="col-md-12">
                                 <div class="table-responsive pomrnheadtffotsticky">
                                    <table
                                       class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                       <thead>
                                          <tr>
                                             <th class="customernewsection-form">
                                                <div
                                                   class="form-check form-check-primary custom-checkbox">
                                                   <input type="checkbox"
                                                      class="form-check-input"
                                                      id="select_all_items_checkbox"
                                                      oninput="checkOrRecheckAllItems(this);">
                                                   <label class="form-check-label"
                                                      for="select_all_items_checkbox"></label>
                                                </div>
                                             </th>
                                             <th width="150px">LR No.</th>
                                             <th width="200px">Source</th>
                                             <th width="200px">Destination</th>
                                             <th>Freight charges</th>
                                             <th>Points</th>
                                             <th>Point Charges</th>
                                             <th>No. of Articles</th>
                                             <th>Weight</th>
                                             <th>LR Charges</th>
                                             <th class="numeric-alignment">Discount</th>
                                             <th class="numeric-alignment" width="150px">Total
                                             </th>
                                             <th width="50px">Action</th>
                                          </tr>
                                       </thead>
                                       <tbody class="mrntableselectexcel" id="item_header">
                                        @php
                                        $total = 0;
                                        $totaldiscount = 0;
                                        @endphp
                                        @foreach($order->items as $index => $item)
                                            @php
                                                $currentOrder = $item;
                                                $discountAmtPrev = $item->item_discount_amount ?? 0;
                                            @endphp
                                            <tr id="item_row_{{ $index }}" class="item_header_rows" onclick="onItemClick('{{ $index }}');">
                                                <td class="customernewsection-form">
                                                    <div class="form-check form-check-primary custom-checkbox">
                                                        <input type="checkbox" class="form-check-input item_row_checks" id="item_row_check_{{ $index }}" del-index="{{ $index }}">
                                                        <label class="form-check-label" for="item_row_check_{{ $index }}"></label>
                                                    </div>
                                                </td>

                                                {{-- LR No --}}
                                                <td class="poprod-decpt">
    {{-- Hidden inputs --}}
    <input type="hidden" id="qt_id_{{ $index }}" value="{{ $item->id ?? 'undefined' }}" name="quotation_item_ids[]">
    <input type="hidden" id="qt_id_header_{{ $index }}" value="{{ $item->id == 0 ? $item->document_id ?? '' : '' }}" name="quotation_item_ids_header[]">

    <input type="hidden" id="qt_type_id_{{ $index }}" value="{{ $item->document_type ?? 'lr' }}" name="quotation_item_type[]">
    <input type="hidden" id="lr_id_{{ $index }}" value="{{ $item->lr_id ?? '' }}" name="lr_id[]">

    <input type="hidden" id="qt_book_id_{{ $index }}" value="{{ $order->book_id ?? '' }}">
    <input type="hidden" id="qt_book_code_{{ $index }}" value="{{ $item->lorry->doc_prefix ?? '' }}">

    <input type="hidden" id="qt_document_no_{{ $index }}" value="{{ $item->lorry->document_number ?? '' }}">
    <input type="hidden" id="qt_document_date_{{ $index }}" value="{{ $order->document_date ?? '' }}">

    {{-- Duplicate hidden id? Keeping as in JS --}}
    <input type="hidden" id="qt_id_{{ $index }}" value="{{$item->lorry->document_number ?? '' }}">

    {{-- Visible item dropdown --}}
    <input type="text" readonly
           id="items_dropdown_{{ $index }}"
           name="item_code[{{ $index }}]"
           placeholder="Select"
           class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input"
           autocomplete="off"
           data-name="{{ $item->item->item_name ?? '' }}"
           data-code="{{ $item->item->item_code ?? '' }}"
           data-id="{{ $item->item->id ?? '' }}"
           hsn_code="{{ $item->item->hsn->code ?? '' }}"
           item-name="{{ $item->item->item_name ?? '' }}"
           item-locations="{{ json_encode([]) }}"
           value="{{ $item->lorry->document_number ?? '' }}">

    <input type="hidden" name="item_id[]" id="items_dropdown_{{ $index }}_value" value="{{ $item->item_id ?? '' }}">
</td>


                                                {{-- Source --}}
                                                <td>
                                                    <input type="text" readonly name="source_name[{{ $index }}]" class="form-control mw-100" value="{{ $item->lorry->source->name ?? '' }}">
                                                </td>

                                                {{-- Destination --}}
                                                <td>
                                                    <input type="text" readonly name="destination_name[{{ $index }}]" class="form-control mw-100" value="{{ $item->lorry->destination->name ?? '' }}">
                                                </td>
                                                <td>
                                                    <input type="text" readonly name="freight_charge[{{ $index }}]" class="form-control mw-100" value="{{ $item->lorry->freight_charges ?? '' }}">
                                                </td>

                                                {{-- Points --}}
                                                <td>
                                                    <input type="text" readonly name="points[{{ $index }}]" class="form-control mw-100" value="{{ $item->lorry->locations->count() == 1 ? 0 : $item->lorry->locations->count()   }}">
                                                </td>
                                                <td>
                                                    <input type="text" readonly name="point_charge[{{ $index }}]" class="form-control mw-100" value="{{ $item->lorry->sub_total ?? '' }}">
                                                </td>

                                                {{-- No. of Articles --}}
                                                <td>
                                                    <input type="text" readonly name="articles[{{ $index }}]" class="form-control mw-100" value="{{ $item->lorry->locations->sum('no_of_articles') + $item->lorry->no_of_bundles ?? 0 }}">
                                                </td>

                                                {{-- Weight --}}
                                                <td>
                                                    <input type="text" readonly name="weight[{{ $index }}]" class="form-control mw-100" value="{{ $item->lorry->locations->sum('weight') + $item->lorry->weight ?? 0 }}">
                                                </td>
                                                 <td>
                                                    <input type="text" readonly name="lr_charges[{{ $index }}]" class="form-control mw-100" value="{{ $item->lorry->lr_charges ?? 0 }}">
                                                </td>

                                                {{-- Discount --}}
                                                <td>
                                                    <div class="position-relative d-flex align-items-center">
                                                        <input type="text" readonly id="item_discount_{{ $index }}" name="item_discount[{{ $index }}]" class="form-control mw-100 numeric-alignment" value="{{ $discountAmtPrev }}" style="width:70px"/>
                                                        <div class="ms-50">
                                                            <button type="button" onclick="onDiscountClick('item_value_{{ $index }}', '{{ $index }}')" data-bs-toggle="modal" data-bs-target="#discount" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Add</button>
                                                        </div>
                                                    </div>
                                                </td>

                                                {{-- Total after discount --}}
                                                <td>
                                                    <input type="text" readonly class="form-control mw-100 numeric-alignment" name="total_after_discount[{{ $index }}]" value="{{ ($currentOrder->lorry->freight_charges + $currentOrder->lorry->sub_total +$item->lorry->lr_charges ?? 0) - $discountAmtPrev }}">
                                                </td>

                                                {{-- Action --}}
                                                <td>
                                                    <div class="d-flex">
                                                        <div style="display:none;" class="me-50 cursor-pointer item_store_locations" data-bs-toggle="modal" data-bs-target="#location" onclick="openStoreLocationModal({{ $index }})" data-stores='[]' id='data_stores_{{ $index }}'>
                                                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="Store Location" class="text-primary"><i data-feather="map-pin"></i></span>
                                                        </div>
                                                        <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick="setItemRemarks('item_remarks_{{ $index }}');">
                                                            <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span>
                                                        </div>
                                                    </div>
                                                    <input type="hidden" id="item_remarks_{{ $index }}" name="item_remarks[]" value="{{ $currentOrder->lorry->remarks ?? '' }}">
                                                </td>
                                                <td hidden=""><input type="text" id="item_value_{{ $index }}" disabled="" class="form-control mw-100 text-end item_values_input" value="{{ ($currentOrder->lorry->freight_charges + $currentOrder->lorry->sub_total + $item->lorry->lr_charges ?? 0)}}"></td>
                                                <input type="hidden" id="value_after_header_discount_{{ $index }}" class="item_val_after_header_discounts_input" value="{{ ($currentOrder->lorry->freight_charges + $currentOrder->lorry->sub_total + $item->lorry->lr_charges ?? 0) - $discountAmtPrev }}">
                                                <input type="hidden" id="item_total_{{ $index }}" value="{{ ($currentOrder->lorry->freight_charges + $currentOrder->lorry->sub_total + $item->lorry->lr_charges ?? 0) - $discountAmtPrev }}" disabled="" class="form-control mw-100 text-end item_totals_input">
                                                <input type="hidden" id="value_after_discount_{{ $index }}"  disabled class="form-control mw-100 text-end item_val_after_discounts_input" value="{{ ($currentOrder->lorry->freight_charges + $currentOrder->lorry->sub_total + $item->lorry->lr_charges ?? 0) - $discountAmtPrev }}" />
                                                <input type="hidden" id = "item_tax_{{ $index }}" class="form-control mw-100 text-end item_taxes_input" style="width: 70px" />
                                                <td hidden>
                                                    <select class="form-select" name="uom_id[]" id = "uom_dropdown_{{ $index }}">
                                                    <option value="{{$currentOrder->uom_id}}" selected>{{$currentOrder->uom_code}}</option>
                                                    </select>
                                                </td>
                                                <td hidden><input type="text" id="item_qty_{{ $index }}" name = "item_qty[{{ $index }}]" value = "{{$currentOrder->balance_qty}}" class="form-control mw-100 text-end"  max = "{{$currentOrder->balance_qty}}"/></td>
                                                 <td hidden ><input readonly type="text" id = "item_rate_{{ $index }}" name="item_rate[]"  value = "{{($currentOrder->lorry->freight_charges + $currentOrder->lorry->sub_total + $item->lorry->lr_charges ?? 0)}}" class="form-control mw-100 text-end" /></td>
                                            </tr>
                                        @php
                                        $total += ($currentOrder->lorry->freight_charges + $currentOrder->lorry->sub_total + $item->lorry->lr_charges ?? 0) - $discountAmtPrev;
                                        $totaldiscount += $discountAmtPrev;
                                        @endphp
                                        @endforeach
                                    </tbody>

                                       </tbody>
                                       <tfoot>
                                          <tr class="totalsubheadpodetail">
                                             <td colspan="10" id="item_row_colspan"></td>
                                             <td class="text-end"
                                                id="all_items_total_discount">
                                                {{$totaldiscount}}
                                             </td>
                                             <input type="hidden"
                                                id="all_items_total_tax"></input>
                                             <td class="text-end all_tems_total_common"
                                                id="all_items_total_total">{{$total}}</td>
                                             <td></td>
                                          </tr>
                                          <tr valign="top">
                                             <td id="item_details_td" colspan="9"
                                                rowspan="10">
                                                <table class="table border">
                                                   <tr>
                                                      <td class="p-0">
                                                         <h6
                                                            class="text-dark mb-0 bg-light-primary py-1 px-50">
                                                            <strong>Item Details</strong>
                                                         </h6>
                                                      </td>
                                                   </tr>
                                                   <tr id="current_item_cust_details_header">
                                                      <td class="poprod-decpt">
                                                         <div
                                                            id="current_item_cust_details">
                                                         </div>
                                                      </td>
                                                   </tr>
                                                   <tr id="current_item_inventory_details">
                                                      <td class="poprod-decpt">
                                                         <div id="current_item_cat_hsn">
                                                         </div>
                                                      </td>
                                                   </tr>
                                                   <tr id="current_item_specs_row">
                                                      <td class="poprod-decpt">
                                                         <div id="current_item_specs">
                                                         </div>
                                                      </td>
                                                   </tr>
                                                   <tr id="current_item_attribute_row">
                                                      <td class="poprod-decpt">
                                                         <div id="current_item_attributes">
                                                         </div>
                                                      </td>
                                                   </tr>
                                                   <tr id="current_item_stocks_row">
                                                      <td class="poprod-decpt">
                                                         <div id="current_item_stocks">
                                                         </div>
                                                      </td>
                                                   </tr>
                                                   <tr id="current_item_lot_no_row">
                                                      <td class="poprod-decpt">
                                                         <div id="current_item_lot_no">
                                                         </div>
                                                      </td>
                                                   </tr>
                                                   <tr id="current_item_inventory">
                                                      <td class="poprod-decpt">
                                                         <div >
                                                         </div>
                                                      </td>
                                                   </tr>
                                                   <tr id="current_item_so_no">
                                                      <td class="poprod-decpt">
                                                         <div
                                                            id="current_item_so_no_details">
                                                         </div>
                                                      </td>
                                                   </tr>
                                                   <tr id="current_item_qt_no_row">
                                                      <td class="poprod-decpt">
                                                         <div id="current_item_qt_no">
                                                         </div>
                                                      </td>
                                                   </tr>
                                                   <tr id="current_item_store_location_row">
                                                      <td class="poprod-decpt">
                                                         <div
                                                            id="current_item_store_location">
                                                         </div>
                                                      </td>
                                                   </tr>
                                                   <tr id="current_item_description_row">
                                                      <td class="poprod-decpt">
                                                         <span
                                                            class="badge rounded-pill badge-light-secondary"><strong>Remarks</strong>:
                                                         <span style="text-wrap:auto;"
                                                            id="current_item_description"></span></span>
                                                      </td>
                                                   </tr>
                                                   <tr
                                                      id="current_item_land_lease_agreement_row">
                                                      <td class="poprod-decpt">
                                                         <div
                                                            id="current_item_land_lease_agreement">
                                                         </div>
                                                      </td>
                                                   </tr>
                                                </table>
                                             </td>
                                             <td colspan="4" id="invoice_summary_td">
                                                <table class="table border mrnsummarynewsty"
                                                   id="summary_table">
                                                   <tr>
                                                      <td colspan="2" class="p-0">
                                                         <h6
                                                            class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between">
                                                            <strong>Invoice Summary</strong>
                                                            <div class="addmendisexpbtn">
                                                               <button type="button"
                                                                  id="taxes_button"
                                                                  data-bs-toggle="modal"
                                                                  data-bs-target="#orderTaxes"
                                                                  class="btn p-25 btn-sm btn-outline-secondary"
                                                                  onclick="onOrderTaxClick();">Taxes</button>
                                                               <button type="button"
                                                                  id="order_discount_button"
                                                                  data-bs-toggle="modal"
                                                                  data-bs-target="#discountOrder"
                                                                  class="btn p-25 btn-sm btn-outline-secondary"
                                                                  onclick="onOrderDiscountModalOpen();"><i
                                                                  data-feather="plus"></i>
                                                               Discount</button>
                                                               
                                                            </div>
                                                         </h6>
                                                      </td>
                                                   </tr>
                                                   <tr class="totalsubheadpodetail">
                                                      <td width="55%"><strong>Item
                                                         Total</strong>
                                                      </td>
                                                      <td class="text-end"
                                                         id="all_items_total_value_summary">
                                                         {{$total}}
                                                      </td>
                                                   </tr>
                                                   <tr class="">
                                                      <td width="55%">Item Discount</td>
                                                      <td class="text-end"
                                                         id="all_items_total_discount_summary">
                                                         {{$totaldiscount}}
                                                      </td>
                                                   </tr>
                                                   <tr class="totalsubheadpodetail">
                                                      <td width="55%"><strong>Taxable
                                                         Value</strong>
                                                      </td>
                                                      <td class="text-end"
                                                         id="all_items_total_total_summary">
                                                         {{$total}}
                                                      </td>
                                                   </tr>
                                                   <tr class="">
                                                      <td width="55%">Taxes</td>
                                                      <td class="text-end"
                                                         id="all_items_total_tax_summary">
                                                         00.00
                                                      </td>
                                                   </tr>
                                                   <tr class="totalsubheadpodetail">
                                                      <td width="55%"><strong>Total After
                                                         Tax</strong>
                                                      </td>
                                                      <td class="text-end"
                                                         id="all_items_total_after_tax_summary">
                                                         {{$total}}
                                                      </td>
                                                   </tr>
                                                   <tr hidden class="">
                                                      <td width="55%">Expenses</td>
                                                      <td class="text-end"
                                                         id="all_items_total_expenses_summary">
                                                         00.00
                                                      </td>
                                                   </tr>
                                                   <input type="hidden" name="sub_total"
                                                      value="0.00"></input>
                                                   <input type="hidden" name="discount"
                                                      value="0.00"></input>
                                                   <input type="hidden"
                                                      name="discount_amount"
                                                      value="0.00"></input>
                                                   <input type="hidden"
                                                      name="other_expenses"
                                                      value="0.00"></input>
                                                   <input type="hidden" name="total_amount"
                                                      value="0.00"></input>
                                                   <tr class="voucher-tab-foot">
                                                      <td class="text-primary"><strong>Grand
                                                         Total</strong>
                                                      </td>
                                                      <td>
                                                         <div
                                                            class="quottotal-bg justify-content-end">
                                                            <h5 id="grand_total">{{$total}}</h5>
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
                                       <div class="row">
                                          <div class="col-md-4">
                                             <div class="mb-1">
                                                <label class="form-label">Upload
                                                Document</label>
                                                <input type="file" class="form-control"
                                                   name="attachments[]"
                                                   onchange="addFiles(this,'main_order_file_preview')"
                                                   max_file_count="{{ isset($maxFileCount) ? $maxFileCount : 10 }}"
                                                   multiple>
                                                <span
                                                   class="text-primary small">{{ __('message.attachment_caption') }}</span>
                                             </div>
                                          </div>
                                          <div class="col-md-6" style="margin-top:19px;">
                                             <div class="row" id="main_order_file_preview">
                                             </div>
                                          </div>
                                       </div>
                                    </div>
                                    <div class="col-md-12">
                                       <div class="mb-1">
                                          <label class="form-label">Final Remarks</label>
                                          <textarea type="text" rows="4" class="form-control" placeholder="Enter Remarks here..."
                                             name="final_remarks">{{ isset($order) ? $order->remarks : '' }}</textarea>
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
                                            <th>Due Date</th>
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
@include('transport-invoice.partials.modals')
@section('scripts')
<script type="text/javascript" src="{{ asset('app-assets/js/file-uploader.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/modules/pull-popup-datatable.js') }}"></script>
<script>
    const currentOrder = @json(isset($order) ? $order : null);
    var currentfy = JSON.stringify({!! isset($order) ? $order : " " !!});
    let requesterTypeParam = "{{isset($order) ? $order -> requester_type : 'Department'}}";
    let redirect = "{{$redirect_url}}";
</script>
@include('transport-invoice.common-js-route',["order" => isset($order) ? $order : null, "route_prefix" => "sale.transporterInvoice"])
<script src="{{ asset("assets\\js\\modules\\ti\\common-script.js") }}"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // Check if currentOrder exists
    if (currentOrder) {
        // Loop through all items in the order
        currentOrder.items.forEach((item, index) => {
            // Check if the item dropdown exists in the DOM
            if (document.getElementById(`items_dropdown_${index}_value`)) {
                // Delay slightly to ensure DOM is fully rendered
                setTimeout(() => {
                    getItemTax(index);
                    setAttributesUI(index); // if you have this function
                }, 500); // 0.5 sec delay
            }
        });
    }
});
</script>

<script>

    function resetParametersDependentElements()
    {
        var selectionSection = document.getElementById('selection_section');
        if (selectionSection) {
            selectionSection.style.display = "none";
        }
        var selectionSectionSO = document.getElementById('sales_order_selection');
        if (selectionSectionSO) {
            selectionSectionSO.style.display = "none";
        }
        var selectionSectionSI = document.getElementById('sales_order_selection');
        if (selectionSectionSI) {
            selectionSectionSI.style.display = "none";
        }
        var selectionSectionSR = document.getElementById('sales_return_selection');
        if (selectionSectionSR) {
            selectionSectionSR.style.display = "none";
        }
        var selectionSectionDN = document.getElementById('delivery_note_selection');
        if (selectionSectionDN) {
            selectionSectionDN.style.display = "none";
        }
        var selectionSectionLease = document.getElementById('land_lease_selection');
        if (selectionSectionLease) {
            selectionSectionLease.style.display = "none";
        }
        // document.getElementById('add_item_section').style.display = "none";
        $("#order_date_input").attr('max', "<?php echo date('Y-m-d'); ?>");
        $("#order_date_input").attr('min', "<?php echo date('Y-m-d'); ?>");
        $("#order_date_input").off('input');
        if ("{{!isset($order)}}") {
            $("#order_date_input").val(moment().format("YYYY-MM-DD"));

        }
        $('#order_date_input').on('input', function() {
            restrictBothFutureAndPastDates(this);
        });
    }
    //$('#series_id_input').trigger('change');
    function onDocDateChange() {
  let actionUrl = `${window.routes.docParams}?book_id=${$("#series_id_input").val()}&document_date=${
    $("#order_date_input").val()
  }`;
  $("#order_date_input").val();
  fetch(actionUrl).then(response => {
    return response.json().then(data => {
      if (data.status == 200) {
        $("#book_code_input").val(data.data.book_code);
        if (!data.data.doc.document_number) {
          $("#order_no_input").val("");
        }
        $("#order_no_input").val(data.data.doc.document_number);
        if (data.data.doc.type == "Manually") {
          $("#order_no_input").attr("readonly", false);
        } else {
          $("#order_no_input").attr("readonly", true);
        }
      }
      if (data.status == 404) {
        $("#book_code_input").val("");
        alert(data.message);
      }
    });
  });
}
function implementBookParameters(paramData) {
  var selectedRefFromServiceOption = paramData.reference_from_service;
  var selectedBackDateOption = paramData.back_date_allowed;
  var selectedFutureDateOption = paramData.future_date_allowed;
  var invoiceToFollowParam = paramData?.invoice_to_follow;
  var itemTypeParam = paramData?.goods_or_services;

  // Reference From
  if (selectedRefFromServiceOption) {
    var selectVal = selectedRefFromServiceOption;
    if (selectVal && selectVal.length > 0) {
      selectVal.forEach(selectSingleVal => {
        if (selectSingleVal == "lr") {
          var selectionSectionElement = document.getElementById("selection_section");
          if (selectionSectionElement) {
            selectionSectionElement.style.display = "";
          }
          var selectionPopupElement = document.getElementById("lorry_receipt_selection");
          if (selectionPopupElement) {
            selectionPopupElement.style.display = "";
          }
        }
      });
    }
  }

  var backDateAllow = false;
  var futureDateAllow = false;

  // Back Date Allow
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

  // Future Date Allow
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
    $("#order_date_input").attr("max", endDate);
    $("#order_date_input").attr("min", startDate);
    $("#order_date_input").off("input");
  }
  if (backDateAllow && !futureDateAllow) { // Allow only back date
    $("#order_date_input").removeAttr("min");
    $("#order_date_input").attr("max", endDate);
    $("#order_date_input").off("input");
    $("#order_date_input").on("input", function() {
      restrictFutureDates(this);
    });
  }
  if (!backDateAllow && futureDateAllow) { // Allow only future date
    $("#order_date_input").removeAttr("max");
    $("#order_date_input").attr("min", startDate);
    $("#order_date_input").off("input");
    $("#order_date_input").on("input", function() {
      restrictPastDates(this);
    });
  }
  requesterTypeParam = paramData?.requester_type?.[0];
  $("#requester_type_input").val(requesterTypeParam);
}

function getDocNumberByBookId(element, reset = true) {
  resetParametersDependentElements(reset);
  let bookId = element.value;
  let actionUrl = `${window.routes.docParams}?book_id=${$("#series_id_input").val()}&document_date=${
    $("#order_date_input").val()
  }`;
  fetch(actionUrl).then(response => {
    return response.json().then(data => {
      if (data.status == 200) {
        $("#book_code_input").val(data.data.book_code);
        if (!data.data.doc.document_number) {
          if (reset) {
            $("#order_no_input").val("");
          }
        }
        if (reset) {
          $("#order_no_input").val(data.data.doc.document_number);
        }
        if (data.data.doc.type == "Manually") {
          $("#order_no_input").attr("readonly", false);
        } else {
          $("#order_no_input").attr("readonly", true);
        }
        if (data.data.parameters) {
          implementBookParameters(data.data.parameters);
        }
        
        if (typeof locationChange === "function") {
          locationChange(document.getElementById("store_id_input"));
        }
      }
      if (data.status == 404) {
        if (reset) {
          $("#book_code_input").val("");
          // alert(data.message);
        }
      }
      if (data.status == 500) {
        if (reset) {
          $("#book_code_input").val("");
          $("#series_id_input").val("");
          Swal.fire({
            title: "Error!",
            text: data.message,
            icon: "error",
          });
        }
      }
    });
  });
}

// function submitForm(value) {
//   $(".preloader").show();
//   document.getElementById("document_status").value = value;
//   document.getElementById("transport_invoice_form").submit();
// }
$(window).on("load", function() {
  if (feather) {
    feather.replace({
      width: 14,
      height: 14,
    });
  }
});

function addItemRow() {
  var docType = $("#service_id_input").val();
  var invoiceToFollow = $("#service_id_input").val() == "yes";
  const tableElementBody = document.getElementById("item_header");
  const previousElements = document.getElementsByClassName("item_header_rows");
  const newIndex = previousElements.length ? previousElements.length : 0;
  if (newIndex == 0) {
    let addRow = $("#series_id_input").val() && $("#order_no_input").val() && $("#order_no_input").val() && $(
      "#order_date_input",
    ).val() && $("#customer_code_input").val();
    if (!addRow) {
      Swal.fire({
        title: "Error!",
        text: "Please fill all the header details first",
        icon: "error",
      });
      return;
    }
  } else {
    let addRow = $("#items_dropdown_" + (newIndex - 1)).val() && parseFloat(
          $("#item_qty_" + (newIndex - 1))
            .val(),
        ) > 0;
    if (!addRow) {
      Swal.fire({
        title: "Error!",
        text: "Please fill all the previous item details first",
        icon: "error",
      });
      return;
    }
  }
  const newItemRow = document.createElement("tr");
  newItemRow.className = "item_header_rows";
  newItemRow.id = "item_row_" + newIndex;
  newItemRow.onclick = function() {
    onItemClick(newIndex);
  };
  var headerStoreId = $("#store_id_input").val();
  var headerStoreCode = $("#store_id_input").attr("data-name");
  // var stores = @json($stores);
  var stores = null;

  var storesHTML = ``;
  stores.forEach(store => {
    if (store.id == headerStoreId) {
      storesHTML += `<option value = "${store.id}" selected>${store.store_name}</option>`;
    } else {
      storesHTML += `<option value = "${store.id}">${store.store_name}</option>`;
    }
  });
  let subStoresHTML = ``;
  currentSubStoreArray.forEach(subStore => {
    subStoresHTML += `<option value = ${subStore.id}> ${subStore.name} </option>`;
  });

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
               <input type="text" id = "items_name_${newIndex}" name = "item_name[${newIndex}]" 
               class="form-control mw-100"   value = "" readonly>
           </td>
          <td class="poprod-decpt" id='attribute_section_${newIndex}'>
              <button id = "attribute_button_${newIndex}" type = "button" data-bs-toggle="modal" onclick = "setItemAttributes('items_dropdown_${newIndex}', ${newIndex});" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
              <input type = "hidden" name = "attribute_value_${newIndex}" />
           </td>
          <td>
              <select class="form-select" name = "uom_id[]" id = "uom_dropdown_${newIndex}" onchange = "getStoresData(${newIndex}, '', true)">
   
              </select>
          </td>
          <td><input type="text" id = "item_qty_${newIndex}" name = "item_qty[${newIndex}]" oninput = "changeItemQty(this, ${newIndex});" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);"/></td>
         <td><input type="text" id = "item_rate_${newIndex}" name = "item_rate[]" oninput = "changeItemRate(this, ${newIndex});" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);"/></td>
          <td><input type="text" id = "item_value_${newIndex}" disabled class="form-control mw-100 text-end item_values_input" /></td>
          <input type = "hidden" id = "header_discount_${newIndex}" value = "0" ></input>
          <input type = "hidden" id = "header_expense_${newIndex}" ></input>
           <td>
              <div class="position-relative d-flex align-items-center">
                  <input type="text" id = "item_discount_${newIndex}" disabled class="form-control mw-100 text-end item_discounts_input" style="width: 70px" />
                  <div class="ms-50">
                      <button type = "button"  onclick = "onDiscountClick('item_value_${newIndex}', ${newIndex})" data-bs-toggle="modal" data-bs-target="#discount" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Add</button>
                  </div>
              </div>
          </td>
          <input type="hidden" id = "item_tax_${newIndex}" class="form-control mw-100 text-end item_taxes_input" style="width: 70px" />
          <td><input type="text" id = "value_after_discount_${newIndex}"  disabled class="form-control mw-100 text-end item_val_after_discounts_input" /></td>
          <input type = "hidden" id = "value_after_header_discount_${newIndex}" class = "item_val_after_header_discounts_input" ></input>
   
               <input type="hidden" id = "item_total_${newIndex}"  disabled class="form-control mw-100 text-end item_totals_input" />
               <td>
               <div class="d-flex">
                   <div style = "display:none;" class="me-50 cursor-pointer item_store_locations" data-bs-toggle="modal" data-bs-target="#location" onclick = "openStoreLocationModal(${newIndex})" data-stores = '[]' id = 'data_stores_${newIndex}'>    <span data-bs-toggle="tooltip" data-bs-placement="top" title="Store Location" class="text-primary"><i data-feather="map-pin"></i></span></div>
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

  const rateInput = document.getElementById("item_rate_" + newIndex);
  const qtyInput = document.getElementById("item_qty_" + newIndex);

  rateInput.addEventListener("input", function() {
    getStoresData(newIndex);
  });

  const itemCodeInput = document.getElementById("items_dropdown_" + newIndex);
  const uomCodeInput = document.getElementById("uom_dropdown_" + newIndex);
  const subStoreCodeInput = document.getElementById("item_sub_store_" + newIndex);
  itemCodeInput.addEventListener("input", function() {
    checkStockData(newIndex);
  });
  uomCodeInput.addEventListener("input", function() {
    checkStockData(newIndex);
  });
}

function deleteItemRows() {
  var deletedItemIds = JSON.parse(localStorage.getItem("deletedSiItemIds"));
  const allRowsCheck = document.getElementsByClassName("item_row_checks");
  let deleteableElementsId = [];
  for (let index = allRowsCheck.length - 1; index >= 0; index--) { // Loop in reverse order
    if (allRowsCheck[index].checked) {
      const currentRowIndex = allRowsCheck[index].getAttribute("del-index");
      const currentRow = document.getElementById("item_row_" + index);
      if (currentRow) {
        if (currentRow.getAttribute("data-id")) {
          deletedItemIds.push(currentRow.getAttribute("data-id"));
        }
        deleteableElementsId.push("item_row_" + currentRowIndex);
      }
    }
  }
  for (let index = 0; index < deleteableElementsId.length; index++) {
    document.getElementById(deleteableElementsId[index])?.remove();
  }
  localStorage.setItem("deletedSiItemIds", JSON.stringify(deletedItemIds));
  const allRowsNew = document.getElementsByClassName("item_row_checks");
  if (allRowsNew.length > 0) {
    for (let idx = 0; idx < allRowsNew.length; idx++) {
      const currentRowIndex = allRowsCheck[idx].getAttribute("del-index");
      if (document.getElementById("item_row_" + currentRowIndex)) {
        itemRowCalculation(currentRowIndex);
      }
    }
    disableHeader();
  } else {
    const allItemsHeaderDiscount = document.getElementsByClassName("order_discount_hidden_fields");
    const allItemsHeaderExpense = document.getElementsByClassName("order_expense_hidden_fields");
    console.log(allItemsHeaderExpense, "huhu");
    console.log(allItemsHeaderDiscount, "hehe");
    document.querySelectorAll(
      ".order_discount_hidden_fields, .order_expense_hidden_fields, .order_expenses, .order_discounts",
    )
      .forEach(el => el.remove());
    $("#order_discount_row").remove();
    $("#all_items_total_expenses_summary").val("0.00");

    document.getElementById("all_items_total_value").innerText = "0.00";
    document.getElementById("total_order_discount").innerText = "0.00";
    document.getElementById("total_order_expense").innerText = "0.00";
    document.getElementById("all_items_total_discount").innerText = "0.00";
    document.getElementById("all_items_total_expenses_summary").innerText = "0.00";

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

function changeItemValue(index) // Single Item Value
{
  const currentElement = document.getElementById("item_value_" + index);
  if (currentElement) {
    const currentQty = document.getElementById("item_qty_" + index).value;
    const currentRate = document.getElementById("item_rate_" + index).value;
    currentElement.value = (parseFloat(currentRate ? currentRate : 0) * parseFloat(currentQty ? currentQty : 0))
      .toFixed(2);
  }
  getItemTax(index);
  changeItemTotal(index);
  changeAllItemsTotal();
  changeAllItemsTotalTotal();
}

function changeItemTotal(index) // Single Item Total
{
  const currentElementValue = document.getElementById("item_value_" + index).value;
  const currentElementDiscount = document.getElementById("item_discount_" + index).value;
  const newItemTotal = (parseFloat(currentElementValue ? currentElementValue : 0) - parseFloat(
    currentElementDiscount ? currentElementDiscount : 0,
  )).toFixed(2);
  document.getElementById("item_total_" + index).value = newItemTotal;
}

function changeAllItemsValue() {
}

function addDiscount(render = true) {
  const discountName = document.getElementById("new_discount_name").value;
  const discountId = document.getElementById("new_discount_id").value;
  // const discountType = document.getElementById('new_discount_type').value;
  const discountPercentage = document.getElementById("new_discount_percentage").value;
  const discountValue = document.getElementById("new_discount_value").value;

  const itemRowIndex = document.getElementById("discount_main_table").getAttribute("item-row-index");

  // Check if newly added discount is greater than actual item value
  var existingItemDiscount = document.getElementById("item_discount_" + itemRowIndex).value;
  existingItemDiscount = parseFloat(existingItemDiscount ? existingItemDiscount : 0);
  var newDiscountVal = parseFloat(discountValue ? discountValue : 0);
  const newItemDiscountTotal = existingItemDiscount + newDiscountVal;
  var itemValueTotal = document.getElementById("item_value_" + itemRowIndex).value;
  itemValueTotal = parseFloat(itemValueTotal ? itemValueTotal : 0);
  if (newItemDiscountTotal > itemValueTotal) {
    Swal.fire({
      title: "Error!",
      text: "Discount cannot be greater than Item Value",
      icon: "error",
    });
    return;
  }

  if (discountName && discountId && (discountPercentage || discountValue)) { // All fields filled
    // const previousElements = document.getElementsByClassName('item_discounts');
    // const newIndex = previousElements.length ? previousElements.length: 0;
    const ItemRowIndexVal = document.getElementById("discount_main_table").getAttribute("item-row-index");

    const previousHiddenFields = document.getElementsByClassName("discount_names_hidden_" + ItemRowIndexVal);
    addDiscountHiddenInput(
      document.getElementById("discount_main_table").getAttribute("item-row"),
      ItemRowIndexVal,
      previousHiddenFields.length ? previousHiddenFields.length : 0,
      render,
    );
  } else {
    Swal.fire({
      title: "Warning!",
      text: "Please enter all the discount details",
      icon: "warning",
    });
  }
}

function addOrderDiscount(dataId = null, enableExceedCheck = true) {
  const discountName = document.getElementById("new_order_discount_name").value;
  const discountId = document.getElementById("new_order_discount_id").value;
  const discountPercentage = document.getElementById("new_order_discount_percentage").value;
  const discountValue = document.getElementById("new_order_discount_value").value;
  if (discountName && discountId && (discountPercentage || discountValue)) { // All fields filled
    var existingOrderDiscount = document.getElementById("order_discount_summary")
      ? document.getElementById(
        "order_discount_summary",
      ).textContent
      : 0;
    existingOrderDiscount = parseFloat(existingOrderDiscount ? existingOrderDiscount : 0);
    var newOrderDiscountVal = parseFloat(discountValue ? discountValue : 0);

    var actualNewOrderDiscount = existingOrderDiscount + newOrderDiscountVal;
    var totalItemsValue = document.getElementById("all_items_total_total")
      ? document.getElementById(
        "all_items_total_total",
      ).textContent
      : 0;
      console.log(totalItemsValue);
    totalItemsValue = parseFloat(totalItemsValue ? totalItemsValue : 0);
    console.log(totalItemsValue);
    if (actualNewOrderDiscount > totalItemsValue && enableExceedCheck) {
      Swal.fire({
        title: "Error!",
        text: "Discount cannot be greater than Total Item Value",
        icon: "error",
      });
      return;
    }
    const previousHiddenFields = document.getElementsByClassName("order_discount_value_hidden");
    addOrderDiscountHiddenInput(previousHiddenFields.length ? previousHiddenFields.length : 0, dataId);
  } else {
    Swal.fire({
      title: "Warning!",
      text: "Please enter all the discount details",
      icon: "warning",
    });
    return;
  }
}

function addOrderExpense(dataId = null, enableExceedCheck = true) {
  const expenseName = document.getElementById("order_expense_name").value;
  const expenseId = document.getElementById("order_expense_id").value;
  const expensePercentage = document.getElementById("order_expense_percentage").value;
  const expenseValue = document.getElementById("order_expense_value").value;

  if (expenseName && expenseId && (expensePercentage || expenseValue)) { // All fields filled
    var existingOrderExpense = document.getElementById("all_items_total_expenses_summary")
      ? document
        .getElementById("all_items_total_expenses_summary").textContent
      : 0;
    existingOrderExpense = parseFloat(existingOrderExpense ? existingOrderExpense : 0);
    var newOrderExpenseVal = parseFloat(expenseValue ? expenseValue : 0);

    var actualNewOrderExpense = existingOrderExpense + newOrderExpenseVal;
    var totalItemsValueAfterTax = document.getElementById("all_items_total_after_tax_summary")
      ? document
        .getElementById("all_items_total_after_tax_summary").textContent
      : 0;
    totalItemsValueAfterTax = parseFloat(totalItemsValueAfterTax ? totalItemsValueAfterTax : 0);

    const previousHiddenFields = document.getElementsByClassName("order_expense_value_hidden");
    addOrderExpenseHiddenInput(previousHiddenFields.length ? previousHiddenFields.length : 0, dataId);
  } else {
    Swal.fire({
      title: "Warning!",
      text: "Please enter all the expense details",
      icon: "warning",
    });
    return;
  }
}

function addDiscountInTable(ItemRowIndexVal, render = true) {
  const previousHiddenNameFields = document.getElementsByClassName("discount_names_hidden_" + ItemRowIndexVal);
  const previousHiddenPercentageFields = document.getElementsByClassName(
    "discount_percentages_hidden_"
      + ItemRowIndexVal,
  );
  const previousHiddenValuesFields = document.getElementsByClassName("discount_values_hidden_" + ItemRowIndexVal);

  const newIndex = previousHiddenNameFields.length ? previousHiddenNameFields.length : 0;

  var newData = ``;
  for (let index = newIndex - 1; index < previousHiddenNameFields.length; index++) {
    const newHTML = document.getElementById("discount_main_table").insertRow(index + 2);
    newHTML.className = "item_discounts";
    newHTML.id = "item_discount_modal_" + newIndex;
    newData = `
                   <td>${index + 1}</td>
                   <td>${previousHiddenNameFields[index].value}</td>
                   <td>${
      previousHiddenPercentageFields[index].value
        ? parseFloat(previousHiddenPercentageFields[index].value).toFixed(2)
        : ""
    }</td>
                   <td class = "dynamic_discount_val_${ItemRowIndexVal}">${
      parseFloat(previousHiddenValuesFields[index].value).toFixed(2)
    }</td>
                   <td>
                       <a href="#" class="text-danger" onclick = "removeDiscount(${index}, ${ItemRowIndexVal});"><i data-feather="trash-2"></i></a>
                   </td>
               `;
    newHTML.innerHTML = newData;
  }

  document.getElementById("new_discount_name").value = "";
  document.getElementById("new_discount_id").value = "";
  // document.getElementById('new_discount_type').value = "";
  document.getElementById("new_discount_percentage").value = "";
  document.getElementById("new_discount_percentage").disabled = false;
  document.getElementById("new_discount_value").value = "";
  document.getElementById("new_discount_value").disabled = false;
  if (render) {
    // setModalDiscountTotal('item_discount_' + ItemRowIndexVal, ItemRowIndexVal);
    itemRowCalculation(ItemRowIndexVal);
  }

  renderIcons();
}

function getTotalorderDiscounts(itemCalculation = true) {
  const values = document.getElementsByClassName("order_discount_value_hidden");
  let discount = 0;
  for (let index = 0; index < values.length; index++) {
    discount += parseFloat(values[index].value ? values[index].value : 0);
  }
  const summaryTable = document.getElementById("summary_table");
  if (discount > 0) { // Add in summary
    const discountRow = document.getElementById("order_discount_row");
    if (discountRow) {
      discountRow.innerHTML = `
                   <td width="55%">Header Discount</td>
                   <td class="text-end" id = "order_discount_summary" >${discount}</td>
               `;
    } else {
      const newRow = summaryTable.insertRow(3);
      newRow.id = "order_discount_row";
      newRow.innerHTML = `
               <td width="55%">Header Discount</td>
                   <td class="text-end" id = "order_discount_summary" >${discount}</td>
               `;
    }
  } else { // Remove from summary
    let lastDiscountRow = document.getElementById("order_discount_row");
    if (lastDiscountRow) {
      lastDiscountRow.remove();
    }
  }
  document.getElementById("total_order_discount").textContent = parseFloat(discount ? discount : 0);
  if (itemCalculation) {
    const itemData = document.getElementsByClassName("item_header_rows");
    for (let ix = 0; ix < itemData.length; ix++) {
      itemRowCalculation(ix);
    }
  }
}

function getTotalOrderExpenses() {
  const values = document.getElementsByClassName("order_expense_value_hidden");
  let expense = 0;
  for (let index = 0; index < values.length; index++) {
    expense += parseFloat(values[index].value ? values[index].value : 0);
  }
  document.getElementById("all_items_total_expenses_summary").textContent = parseFloat(expense ? expense : 0);
  document.getElementById("total_order_expense").textContent = parseFloat(expense ? expense : 0);
  setAllTotalFields();
}

function addOrderDiscountInTable(index) {
  const previousHiddenNameFields = document.getElementsByClassName("order_discount_name_hidden");
  const previousHiddenPercentageFields = document.getElementsByClassName("order_discount_percentage_hidden");
  const previousHiddenValuesFields = document.getElementsByClassName("order_discount_value_hidden");
  const previousHiddenIdFields = document.getElementsByClassName("order_discount_id_hidden");

  const newIndex = previousHiddenNameFields.length ? previousHiddenNameFields.length : 0;

  var newData = ``;
  var totalSummaryDiscount = 0;
  var total = parseFloat(
    document.getElementById("total_order_discount").textContent
      ? document.getElementById(
        "total_order_discount",
      ).textContent
      : 0,
  );
  for (let index = newIndex - 1; index < previousHiddenNameFields.length; index++) {
    const newHTML = document.getElementById("order_discount_main_table").insertRow(index + 2);
    newHTML.className = "order_discounts";
    newHTML.id = "order_discount_modal_" + (parseInt(newIndex - 1));
    if (previousHiddenIdFields[index]) {
      newHTML.setAttribute("data-id", previousHiddenIdFields[index].value);
    }
    newData = `
               <td>${index + 1}</td>
               <td>${previousHiddenNameFields[index].value}</td>
               <td>${
      previousHiddenPercentageFields[index].value
        ? parseFloat(previousHiddenPercentageFields[index].value).toFixed(2)
        : ""
    }</td>
               <td id = "order_discount_input_val_${index}">${
      parseFloat(previousHiddenValuesFields[index].value).toFixed(2)
    }</td>
               <td>
                   <a href="#" class="text-danger" onclick = "removeOrderDiscount(${
      newIndex - 1
    });"><i data-feather="trash-2"></i></a>
               </td>
           `;
    newHTML.innerHTML = newData;
    total += parseFloat(previousHiddenValuesFields[index].value ? previousHiddenValuesFields[index].value : 0);
    totalSummaryDiscount += parseFloat(
      previousHiddenValuesFields[index].value
        ? previousHiddenValuesFields[
          index
        ].value
        : 0,
    );
  }

  document.getElementById("new_order_discount_name").value = "";
  document.getElementById("new_order_discount_id").value = "";
  document.getElementById("new_order_discount_percentage").value = "";
  document.getElementById("new_order_discount_percentage").disabled = false;
  document.getElementById("new_order_discount_value").value = "";
  document.getElementById("new_order_discount_value").disabled = false;
  document.getElementById("total_order_discount").textContent = total;
  renderIcons();

  getTotalorderDiscounts();
}

function addOrderExpenseInTable(index) {
  const previousHiddenNameFields = document.getElementsByClassName("order_expense_name_hidden");
  const previousHiddenPercentageFields = document.getElementsByClassName("order_expense_percentage_hidden");
  const previousHiddenValuesFields = document.getElementsByClassName("order_expense_value_hidden");
  const previousHiddenIdFields = document.getElementsByClassName("order_expense_id_hidden");

  const newIndex = previousHiddenNameFields.length ? previousHiddenNameFields.length : 0;

  var newData = ``;
  var totalSummaryExpense = 0;
  var total = parseFloat(
    document.getElementById("total_order_expense").textContent
      ? document.getElementById(
        "total_order_expense",
      ).textContent
      : 0,
  );
  for (let index = newIndex - 1; index < previousHiddenNameFields.length; index++) {
    const newHTML = document.getElementById("order_expense_main_table").insertRow(index + 2);
    newHTML.className = "order_expenses";
    if (previousHiddenIdFields[index]) {
      newHTML.setAttribute("data-id", previousHiddenIdFields[index].value);
    }
    newHTML.id = "order_expense_modal_" + (parseInt(newIndex - 1));
    newData = `
               <td>${index + 1}</td>
               <td>${previousHiddenNameFields[index].value}</td>
               <td>${
      previousHiddenPercentageFields[index].value
        ? parseFloat(previousHiddenPercentageFields[index].value).toFixed(2)
        : ""
    }</td>
               <td id = "order_expense_input_val_${index}">${
      parseFloat(previousHiddenValuesFields[index].value).toFixed(2)
    }</td>
               <td>
                   <a href="#" class="text-danger" onclick = "removeOrderExpense(${
      newIndex - 1
    });"><i data-feather="trash-2"></i></a>
               </td>
           `;
    total += parseFloat(previousHiddenValuesFields[index].value ? previousHiddenValuesFields[index].value : 0);
    newHTML.innerHTML = newData;
    totalSummaryExpense += parseFloat(
      previousHiddenValuesFields[index].value
        ? previousHiddenValuesFields[
          index
        ].value
        : 0,
    );
  }

  document.getElementById("order_expense_name").value = "";
  document.getElementById("order_expense_id").value = "";
  document.getElementById("order_expense_percentage").value = "";
  document.getElementById("order_expense_percentage").disabled = false;
  document.getElementById("order_expense_value").value = "";
  document.getElementById("order_expense_value").disabled = false;
  document.getElementById("total_order_expense").textContent = total;

  renderIcons();

  getTotalOrderExpenses();
}

function removeDiscount(index, itemIndex) {
  let deletedDiscountTedIds = JSON.parse(localStorage.getItem("deletedItemDiscTedIds"));
  const removableElement = document.getElementById("item_discount_modal_" + index);
  if (removableElement) {
    if (removableElement.getAttribute("data-id")) {
      deletedDiscountTedIds.push(removableElement.getAttribute("data-id"));
    }
    removableElement.remove();
  }
  document.getElementById("item_discount_name_" + itemIndex + "_" + index)?.remove();
  document.getElementById("item_discount_percentage_" + itemIndex + "_" + index)?.remove();
  document.getElementById("item_discount_value_" + itemIndex + "_" + index)?.remove();
  document.getElementById("item_discount_master_id_" + itemIndex + "_" + index)?.remove();
  localStorage.setItem("deletedItemDiscTedIds", JSON.stringify(deletedDiscountTedIds));
  renderPreviousDiscount(itemIndex);
  // setModalDiscountTotal('item_discount_' + itemIndex, itemIndex);
  itemRowCalculation(itemIndex);
}

function removeOrderDiscount(index) {
  let deletedHeaderDiscTedIds = JSON.parse(localStorage.getItem("deletedHeaderDiscTedIds"));
  const removableElement = document.getElementById("order_discount_modal_" + index);
  if (removableElement) {
    removableElement.remove();
    if (removableElement.getAttribute("data-id")) {
      deletedHeaderDiscTedIds.push(removableElement.getAttribute("data-id"));
    }
  }
  document.getElementById("order_discount_name_" + index).remove();
  document.getElementById("order_discount_percentage_" + index).remove();
  document.getElementById("order_discount_value_" + index).remove();
  document.getElementById("order_discount_master_id_" + index)?.remove();
  localStorage.setItem("deletedHeaderDiscTedIds", JSON.stringify(deletedHeaderDiscTedIds));
  // renderPreviousDiscount(itemIndex);
  // setModalDiscountTotal('item_discount_' + itemIndex, itemIndex);
  getTotalorderDiscounts();
}

function removeOrderExpense(index) {
  let deletedHeaderExpTedIds = JSON.parse(localStorage.getItem("deletedHeaderExpTedIds"));
  const removableElement = document.getElementById("order_expense_modal_" + index);
  if (removableElement) {
    removableElement.remove();
    if (removableElement.getAttribute("data-id")) {
      deletedHeaderExpTedIds.push(removableElement.getAttribute("data-id"));
    }
  }

  document.getElementById("order_expense_name_" + index)?.remove();
  document.getElementById("order_expense_percentage_" + index)?.remove();
  document.getElementById("order_expense_value_" + index)?.remove();
  document.getElementById("order_expense_master_id_" + index)?.remove();
  localStorage.setItem("deletedHeaderExpTedIds", JSON.stringify(deletedHeaderExpTedIds));
  getTotalOrderExpenses();
  // renderPreviousDiscount(itemIndex);
  // setModalDiscountTotal('item_discount_' + itemIndex, itemIndex);
}

function changeDiscountType(element) {
  if (element.value === "Fixed") {
    document.getElementById("new_discount_percentage").disabled = true;
    document.getElementById("new_discount_percentage").value = "";
    document.getElementById("new_discount_value").disabled = false;
    document.getElementById("new_discount_value").value = "";
  } else {
    document.getElementById("new_discount_percentage").disabled = false;
    document.getElementById("new_discount_percentage").value = "";
    document.getElementById("new_discount_value").disabled = true;
    document.getElementById("new_discount_value").value = "";
  }
}

function onDiscountClick(elementId, itemRowIndex) {
  const totalValue = document.getElementById(elementId).value;
  document.getElementById("discount_main_table").setAttribute("total-value", totalValue);
  document.getElementById("discount_main_table").setAttribute("item-row", elementId);
  document.getElementById("discount_main_table").setAttribute("item-row-index", itemRowIndex);
  renderPreviousDiscount(itemRowIndex);
  initializeAutocompleteTed("new_discount_name", "new_discount_id", "sales_module_discount", "new_discount_percentage");
}

function renderPreviousDiscount(ItemRowIndexVal) {
  const previousHiddenNameFields = document.getElementsByClassName("discount_names_hidden_" + ItemRowIndexVal);
  const previousHiddenPercentageFields = document.getElementsByClassName(
    "discount_percentages_hidden_"
      + ItemRowIndexVal,
  );
  const previousHiddenValuesFields = document.getElementsByClassName("discount_values_hidden_" + ItemRowIndexVal);

  const oldDiscounts = document.getElementsByClassName("item_discounts");
  if (oldDiscounts && oldDiscounts.length > 0) {
    while (oldDiscounts.length > 0) {
      oldDiscounts[0].remove();
    }
  }

  var newData = ``;
  for (let index = 0; index < previousHiddenNameFields.length; index++) {
    const newHTML = document.getElementById("discount_main_table").insertRow(index + 2);
    newHTML.id = "item_discount_modal_" + index;
    newHTML.className = "item_discounts";
    newData = `
               <td>${index + 1}</td>
               <td>${previousHiddenNameFields[index].value}</td>
               <td>${
      previousHiddenPercentageFields[index].value
        ? parseFloat(previousHiddenPercentageFields[index].value).toFixed(2)
        : ""
    }</td>
               <td class = "dynamic_discount_val_${ItemRowIndexVal}">${
      parseFloat(previousHiddenValuesFields[index].value).toFixed(2)
    }</td>
               <td>
                   <a href="#" class="text-danger" onclick = "removeDiscount(${index}, ${ItemRowIndexVal});"><i data-feather="trash-2"></i></a>
               </td>
           `;
    newHTML.innerHTML = newData;
  }

  document.getElementById("new_discount_name").value = "";
  document.getElementById("new_discount_id").value = "";
  // document.getElementById('new_discount_type').value = "";
  document.getElementById("new_discount_percentage").value = "";
  document.getElementById("new_discount_percentage").disabled = false;
  document.getElementById("new_discount_value").value = "";
  document.getElementById("new_discount_value").disabled = false;
  setModalDiscountTotal("item_discount_" + ItemRowIndexVal, ItemRowIndexVal);
  itemRowCalculation(ItemRowIndexVal);

  renderIcons();
}

function onChangeDiscountPercentage(element) {
  document.getElementById("new_discount_value").disabled = element.value ? true : false;
  const totalValue = document.getElementById(
    "item_value_" + document.getElementById("discount_main_table")
      .getAttribute("item-row-index"),
  ).value;
  if (totalValue) {
    document.getElementById("new_discount_value").value = (parseFloat(totalValue) * parseFloat(
      element.value
        / 100,
    )).toFixed(2);
  }
}

function onChangeOrderDiscountPercentage(element) {
  document.getElementById("new_order_discount_value").disabled = element.value ? true : false;
  const totalValue = document.getElementById("all_items_total_value_summary").textContent;
  const totalItemDiscount = document.getElementById("all_items_total_discount_summary").textContent;
  let totalAfterItemDiscount = parseFloat(totalValue ? totalValue : 0) - parseFloat(
    totalItemDiscount
      ? totalItemDiscount
      : 0,
  );
  if (totalAfterItemDiscount) {
    document.getElementById("new_order_discount_value").value = (parseFloat(
      totalAfterItemDiscount
        ? totalAfterItemDiscount
        : 0,
    ) * parseFloat(element.value / 100)).toFixed(2);
  }
}

function onChangeOrderExpensePercentage(element) {
  document.getElementById("order_expense_value").disabled = element.value ? true : false;
  const totalValue = document.getElementById("all_items_total_after_tax_summary").textContent;
  if (totalValue) {
    document.getElementById("order_expense_value").value = (parseFloat(totalValue ? totalValue : 0)
      * parseFloat(element.value / 100)).toFixed(2);
  }
}

function onChangeDiscountValue(element) {
  document.getElementById("new_discount_percentage").disabled = element.value ? true : false;
}

function onChangeOrderDiscountValue(element) {
  document.getElementById("new_order_discount_percentage").disabled = element.value ? true : false;
}

function onChangeOrderExpenseValue(element) {
  document.getElementById("order_expense_percentage").disabled = element.value ? true : false;
}

function setModalDiscountTotal(elementId, index) {
  var totalDiscountModalVal = 0;
  const docs = document.getElementsByClassName("discount_values_hidden_" + index);
  for (let index = 0; index < docs.length; index++) {
    totalDiscountModalVal += parseFloat(docs[index].value ? docs[index].value : 0);
  }
  document.getElementById("total_item_discount").textContent = totalDiscountModalVal.toFixed(2);
  document.getElementById(elementId).value = totalDiscountModalVal.toFixed(2);
  // changeItemTotal(index);
  // changeAllItemsDiscount();
  // itemRowCalculation(index);
}

function addDiscountHiddenInput(itemRow, index, discountIndex, render = true) {
  addHiddenInput(
    "item_discount_name_" + index + "_" + discountIndex,
    document.getElementById("new_discount_name")
      .value,
    `item_discount_name[${index}][${discountIndex}]`,
    "discount_names_hidden_" + index,
    itemRow,
  );
  addHiddenInput(
    "item_discount_master_id_" + index + "_" + discountIndex,
    document.getElementById(
      "new_discount_id",
    ).value,
    `item_discount_master_id[${index}][${discountIndex}]`,
    "discount_master_id_hidden_" + index,
    itemRow,
  );
  // addHiddenInput("item_discount_type_" + index + "_" + discountIndex, document.getElementById('new_discount_type').value, `item_discount_type[${index}][${discountIndex}]`, 'discount_types_hidden_' + index, itemRow);
  addHiddenInput(
    "item_discount_percentage_" + index + "_" + discountIndex,
    document.getElementById(
      "new_discount_percentage",
    ).value,
    `item_discount_percentage[${index}][${discountIndex}]`,
    "discount_percentages_hidden_" + index,
    itemRow,
  );
  addHiddenInput(
    "item_discount_value_" + index + "_" + discountIndex,
    document.getElementById(
      "new_discount_value",
    ).value,
    `item_discount_value[${index}][${discountIndex}]`,
    "discount_values_hidden_" + index,
    itemRow,
  );
  addDiscountInTable(index, render);
}

function addOrderDiscountHiddenInput(index, dataId = null) {
  addHiddenInput(
    "order_discount_name_" + index,
    document.getElementById("new_order_discount_name").value,
    `order_discount_name[${index}]`,
    "order_discount_hidden_fields order_discount_name_hidden",
    "main_so_form",
    dataId,
  );
  addHiddenInput(
    "order_discount_master_id_" + index,
    document.getElementById("new_order_discount_id").value,
    `order_discount_master_id[${index}]`,
    "order_discount_hidden_fields order_discount_master_id_hidden",
    "main_so_form",
    dataId,
  );
  addHiddenInput(
    "order_discount_percentage_" + index,
    document.getElementById("new_order_discount_percentage")
      .value,
    `order_discount_percentage[${index}]`,
    "order_discount_hidden_fields order_discount_percentage_hidden",
    "main_so_form",
    dataId,
  );
  addHiddenInput(
    "order_discount_value_" + index,
    document.getElementById("new_order_discount_value").value,
    `order_discount_value[${index}]`,
    "order_discount_hidden_fields order_discount_value_hidden",
    "main_so_form",
    dataId,
  );
  if (dataId) {
    addHiddenInput(
      "order_discount_id_" + index,
      dataId,
      `order_discount_id[${index}]`,
      "order_discount_hidden_fields order_discount_id_hidden",
      "main_so_form",
    );
  }
  addOrderDiscountInTable(index);
}

function addOrderExpenseHiddenInput(index, dataId = null) {
  addHiddenInput(
    "order_expense_name_" + index,
    document.getElementById("order_expense_name").value,
    `order_expense_name[${index}]`,
    "order_expense_hidden_fields order_expense_name_hidden",
    "main_so_form",
    dataId,
  );
  addHiddenInput(
    "order_expense_master_id_" + index,
    document.getElementById("order_expense_id").value,
    `order_expense_master_id[${index}]`,
    "order_expense_hidden_fields order_expense_master_id_hidden",
    "main_so_form",
    dataId,
  );
  addHiddenInput(
    "order_expense_percentage_" + index,
    document.getElementById("order_expense_percentage").value,
    `order_expense_percentage[${index}]`,
    "order_expense_hidden_fields order_expense_percentage_hidden",
    "main_so_form",
    dataId,
  );
  addHiddenInput(
    "order_expense_value_" + index,
    document.getElementById("order_expense_value").value,
    `order_expense_value[${index}]`,
    "order_expense_hidden_fields order_expense_value_hidden",
    "main_so_form",
    dataId,
  );
  if (dataId) {
    addHiddenInput(
      "order_expense_id_" + index,
      dataId,
      `order_expense_id[${index}]`,
      "order_expense_hidden_fields order_expense_id_hidden",
      "main_so_form",
    );
  }
  addOrderExpenseInTable(index);
}

function initializeAutocomplete1(selector, index) {
  $("#" + selector).autocomplete({
    source: function(request, response) {
      $.ajax({
        url: "/search",
        method: "GET",
        dataType: "json",
        data: {
          q: request.term,
          type: "transport_module_items",
          customer_id: $("#customer_id_input").val(),
          header_book_id: $("#series_id_input").val(),
        },
        success: function(data) {
          response($.map(data, function(item) {
            return {
              id: item.id,
              label: `${item.item_name} (${item.item_code})`,
              code: item.item_code || "",
              item_id: item.id,
              uom: item.uom,
              alternateUoms: item.alternate_u_o_ms,
              specifications: item.specifications,
            };
          }));
        },
        error: function(xhr) {
          console.error("Error fetching customer data:", xhr.responseText);
        },
      });
    },
    minLength: 0,
    select: function(event, ui) {
      var $input = $(this);
      var itemCode = ui.item.code;
      var itemName = ui.item.value;
      var itemId = ui.item.item_id;

      $input.attr("data-name", itemName);
      $input.attr("data-code", itemCode);
      $input.attr("data-id", itemId);
      $input.attr("specs", JSON.stringify(ui.item.specifications));
      $input.val(itemCode);

      const uomDropdown = document.getElementById("uom_dropdown_" + index);
      var uomInnerHTML = ``;
      if (uomDropdown) {
        uomInnerHTML += `<option value = '${ui.item.uom.id}'>${ui.item.uom.alias}</option>`;
      }
      if (ui.item.alternateUoms && ui.item.alternateUoms.length > 0) {
        var selected = false;
        ui.item.alternateUoms.forEach((saleUom) => {
          if (saleUom.is_selling) {
            uomInnerHTML += `<option value = '${saleUom.uom?.id}' ${
              selected == false ? "selected" : ""
            }>${saleUom.uom?.alias}</option>`;
            selected = true;
          }
        });
      }
      uomDropdown.innerHTML = uomInnerHTML;
      itemOnChange(selector, index, "/item/attributes/");
      getItemTax(index);
      return false;
    },
    change: function(event, ui) {
      if (!ui.item) {
        $(this).val("");
        // $('#itemId').val('');
        $(this).attr("data-name", "");
        $(this).attr("data-code", "");
      }
    },
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
        url: "/search",
        method: "GET",
        dataType: "json",
        data: {
          q: request.term,
          type: "customer_list",
        },
        success: function(data) {
          response($.map(data, function(item) {
            return {
              id: item.id,
              label: `${item.company_name} (${item.customer_code})`,
              code: item.customer_code || "",
              item_id: item.id,
              payment_terms_id: item?.payment_terms?.id,
              payment_terms: item?.payment_terms?.name,
              payment_terms_code: item?.payment_terms?.name,
              currency_id: item?.currency?.id,
              currency: item?.currency?.name,
              currency_code: item?.currency?.short_name,
              type: item?.customer_type,
              phone_no: item?.mobile,
              email: item?.email,
              gstin: item?.compliances?.gstin_no,
            };
          }));
        },
        error: function(xhr) {
          console.error("Error fetching customer data:", xhr.responseText);
        },
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
      $input.attr("customer_type", customerType);
      $input.attr("phone_no", phoneNo);
      $input.attr("email", email);
      $input.attr("gstin", gstIn);
      $input.attr("payment_terms_id", paymentTermsId);
      $input.attr("payment_terms", paymentTerms);
      $input.attr("payment_terms_code", paymentTermsCode);
      $input.attr("currency_id", currencyId);
      $input.attr("currency", currency);
      $input.attr("currency_code", currencyCode);
      $input.val(ui.item.label);
      $("#customer_code_input_hidden").val(ui.item.code);
      document.getElementById("customer_id_input").value = ui.item.id;
      onChangeCustomer(selector);
      if (customerType === "Cash") {
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
    },
  }).focus(function() {
    if (this.value === "") {
      $(this).autocomplete("search", "");
    }
  });
}

initializeAutocompleteCustomer("customer_code_input");


function checkItemAddValidation() {
  let addRow = $("#series_id_input").val && $("#order_no_input").val && $("#order_date_input").val && $(
    "#customer_code_input",
  ).val;
  return addRow;
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

function onTaxClick(itemIndex) {
  const taxInput = document.getElementById("item_tax_" + itemIndex);
  const taxDetails = JSON.parse(taxInput.getAttribute("tax_details"));
  const taxTable = document.getElementById("tax_main_table");
  // Remove previous Taxes
  const oldTaxes = document.getElementsByClassName("item_taxes");
  if (oldTaxes && oldTaxes.length > 0) {
    while (oldTaxes.length > 0) {
      oldTaxes[0].remove();
    }
  }
  // Add New Tax
  let newHtml = ``;
  taxDetails.forEach((element, index) => {
    let newDoc = taxTable.insertRow(index + 1);
    newDoc.id = "item_tax_modal_" + itemIndex;
    newDoc.className = "item_taxes";
    newHtml = `
           <td>${index + 1}</td>
           <td>${element.tax_name}</td>
           <td>${element.tax_percentage}</td>
           <td class = "dynamic_tax_val_${itemIndex}">${element.tax_value}</td>
       `;
    newDoc.innerHTML = newHtml;
  });
}

function onOrderTaxClick() {
  const taxesHiddenFields = document.getElementsByClassName("item_taxes_input");
  orderTaxes = [];
  for (let index = 0; index < taxesHiddenFields.length; index++) {
    let itemLevelTaxes = JSON.parse(taxesHiddenFields[index].getAttribute("tax_details"));
    if (Array.isArray(itemLevelTaxes) && itemLevelTaxes.length > 0) {
      itemLevelTaxes.forEach(itemLevelTax => {
        let existingIndex = orderTaxes.findIndex(tax =>
          tax.tax_type == itemLevelTax.tax_type && tax
              .tax_percentage == itemLevelTax.tax_percentage
        );
        if (existingIndex > -1) { // Exists
          orderTaxes[existingIndex]["taxable_amount"] = parseFloat(
            orderTaxes[existingIndex][
              "taxable_amount"
            ],
          ) + parseFloat(itemLevelTax.taxable_value);
          orderTaxes[existingIndex]["tax_value"] = parseFloat(
            orderTaxes[existingIndex][
              "tax_value"
            ],
          ) + parseFloat(itemLevelTax.tax_value);
        } else { // Push
          orderTaxes.push({
            "index": orderTaxes.length ? orderTaxes.length : 0,
            "tax_type": itemLevelTax.tax_type,
            "taxable_amount": itemLevelTax.taxable_value,
            "tax_percentage": itemLevelTax.tax_percentage,
            "tax_value": itemLevelTax.tax_value,
          });
        }
      });
    }
  }
  const mainTableBody = document.getElementById("order_tax_details_table");
  let newTaxesHtml = ``;
  orderTaxes.forEach(taxDetail => {
    newTaxesHtml += `
                   <tr>
                   <td>${taxDetail.index + 1}</td>
                   <td>${taxDetail.tax_type}</td>
                   <td>${taxDetail.taxable_amount}</td>
                   <td>${taxDetail.tax_percentage}</td>
                   <td>${taxDetail.tax_value}</td>
                   </tr>
       `;
  });
  mainTableBody.innerHTML = newTaxesHtml;
}

function setPOD() {
  document.getElementById("action_type").value = "pod";
  document.getElementById("pod_heading_label").textContent = "Proof of Delivery ";
}

function reEnableSelectedPullType(type = "so") {
  if (type == "so") {
    document.getElementById("select_order_button").removeAttribute("disabled");
  } else if (type == "pl") {
    document.getElementById("pl_button").removeAttribute("disabled");
  } else if (type == "plist") {
    document.getElementById("pack_list_button").removeAttribute("disabled");
  } else if (type == "dnote") {
    document.getElementById("select_dn_button").removeAttribute("disabled");
  } else if (type == "lr") {
    document.getElementById("select_lorry_button").removeAttribute("disabled");
  }
}

function processOrder(type = "lr") {
  const allCheckBoxes = document.getElementsByClassName("po_checkbox");
  const docType = $("#service_id_input").val();
  const invoiceToFollowParam = $("#service_id_input").val() == "dnote";
  const apiUrl = "{{ route('sale.transporterInvoice.process.items') }}";
  let docId = [];
  let soItemsId = [];
  let lrIds = [];
  let qties = [];
  let documentDetails = [];
  let plistDetailIds = [];
  let plItemDetailIds = [];
  let itemIds = [];
  for (let index = 0; index < allCheckBoxes.length; index++) {
    if (allCheckBoxes[index].checked) {
      docId.push(allCheckBoxes[index].getAttribute("document-id"));
      soItemsId.push(allCheckBoxes[index].getAttribute("so-item-id"));
      lrIds.push(allCheckBoxes[index].getAttribute("lr-id"));
      itemIds.push(allCheckBoxes[index].getAttribute("item-id"));
      qties.push(allCheckBoxes[index].getAttribute("balance_qty"));
      documentDetails.push({
        "order_id": allCheckBoxes[index].getAttribute("document-id"),
        "quantity": allCheckBoxes[index].getAttribute("balance_qty"),
        "item_id": allCheckBoxes[index].getAttribute("so-item-id"),
      });
      if (type === "plist") {
        plistDetailIds.push(allCheckBoxes[index].getAttribute("detail-id"));
      }
      plItemDetailIds.push(allCheckBoxes[index].getAttribute("pl_item_detail_id"));
    }
  }
  if (docId && soItemsId.length > 0) {
    $.ajax({
      url: apiUrl,
      method: "GET",
      dataType: "json",
      data: {
        order_id: docId,
        quantities: qties,
        items_id: soItemsId,
        lr_ids: lrIds,
        header_book_id: $("#series_id_input").val(),
        item_ids: itemIds,
        doc_type: openPullType,
        document_details: JSON.stringify(documentDetails),
        store_id: $("#store_id_input").val(),
        plist_detail_ids: plistDetailIds,
        pl_item_detail_ids: plItemDetailIds,
      },
      success: function(data) {
        const currentOrders = data.data;
        let currentOrderIndexVal = document.getElementsByClassName("item_header_rows").length;
        const selectedIds = Array.from(document.getElementsByClassName("item_header_rows"))
    .map((_, i) => document.getElementById("lr_id_" + i))
    .filter(Boolean)
    .map(el => el.value);
        currentOrders.forEach((currentOrder) => {
            console.log(currentOrder);
          if (currentOrder) { // Set all data
            let subStoreId = currentOrder?.sub_store_id
              ? currentOrder
                ?.sub_store_id
              : "";
            // Disable Header
            // Basic Details
            // Disable Header
            // Basic Details
            $("#customer_code_input").val(currentOrder.customer_code);
            $("#customer_id_input").val(currentOrder.customer_id);
            $("#customer_code_input_hidden").val(currentOrder.customer_code);
            $("#consignee_name_input").val(currentOrder.consignee_name);
            $("#customer_phone_no_input").val(currentOrder.customer_phone_no);
            $("#customer_email_input").val(currentOrder.customer_email);
            $("#customer_gstin_input").val(currentOrder.customer_gstin);
            // First add options also

            $("#currency_dropdown").empty(); // Clear existing options
            $("#currency_dropdown").append(
              new Option(
                currentOrder.customer
                  ? currentOrder.customer.currency
                    ?.name || "Default Currency Name"
                  : "Default Currency Name",
                currentOrder.currency_id || 0,
              ),
            );
            $("#currency_code_input").val(currentOrder.currency_code);
            // First add options also
            $("#payment_terms_dropdown").empty(); // Clear existing options
            $("#payment_terms_dropdown").append(
              new Option(
                currentOrder.customer
                  ? currentOrder.customer.payment_terms
                    ?.name || "Default Payment Terms"
                  : "Default Payment Name",
                currentOrder.payment_term_id || 0,
              ),
            );
            $("#payment_terms_code_input").val(currentOrder.payment_term_code);
            // Address
            $("#current_billing_address").text(
              currentOrder.billing_address_details
                ?.display_address,
            );
            $("#current_shipping_address").text(
              currentOrder
                .shipping_address_details?.display_address,
            );
            $("#current_shipping_country_id").val(
              currentOrder
                .shipping_address_details?.country_id,
            );
            $("#current_billing_country_id").val(
              currentOrder
                .billing_address_details?.country_id,
            );
            $("#current_shipping_state_id").val(
              currentOrder
                .shipping_address_details?.state_id,
            );
            $("#current_billing_state_id").val(
              currentOrder.billing_address_details
                ?.state_id,
            );
            // General Detail
            // $("#transporter_name_input").val(currentOrder?.transporter_name);
            // $("#transporter_mode_input").val(currentOrder?.transportation_mode);
            $("#vehicle_no_input").val(currentOrder.vehicle_no);
            // $("#lr_number_input").val(currentOrder?.lr_number);

            const locationElement = document.getElementById("store_id_input");
            if (locationElement) {
              const displayAddress = locationElement.options[
                locationElement
                  .selectedIndex
              ].getAttribute("display-address");
              $("#current_pickup_address").text(displayAddress);
            }
            const mainTableItem = document.getElementById("item_header");
            // Remove previous items if any
            // const allRowsCheck = document.getElementsByClassName('item_row_checks');
            // for (let index = 0; index < allRowsCheck.length; index++) {
            //     allRowsCheck[index].checked = true;
            // }
            // deleteItemRows();
            if (true) {
              currentOrder.items.forEach((item, itemIndex) => {
                if (selectedIds.includes(String(currentOrder.lr_id))) {
                    return; // skip this item
                }
                console.log(item);
                item.balance_qty = item.actual_qty
                  ? item.actual_qty
                  : item.balance_qty;
                var avl_qty = item.balance_qty;
                if (docType != "si") {
                  avl_qty = Math.min(
                    item.balance_qty,
                    item
                        .stock_qty
                      ? item.stock_qty
                      : 0,
                  );
                }
                item.balance_qty = avl_qty;
                item.max_qty = avl_qty;
                const itemRemarks = item.remarks ? item.remarks : "";
                let amountMax = ``;
                if (type == "land-lease") {
                  amountMax = `max = '${item.rate}'`;
                }
                if (type == "plist") {
                  item.balance_qty = item.order_qty;
                }

                let agreement_no = "";
                let lease_end_date = "";
                let due_date = "";
                let repayment_period = "";

                let land_parcel = "";
                let land_plots = "";

                let landLeasePullHtml = "";
                let plistHTML = "";

                  landLeasePullHtml = "";
                
                var discountAmtPrev = 0;

                item.discount_ted.forEach((ted, tedIndex) => {
                  var percentage = ted.ted_percentage;
                  var itemValue = (item.rate * item
                    .balance_qty).toFixed(2);
                  if (!percentage) {
                    percentage = ted.ted_amount / (ted
                        .assessment_amount
                      ? ted
                        .assessment_amount
                      : itemValue)
                      * 100;
                  }
                  var itemDiscountValuePrev = ((itemValue
                    * percentage) / 100).toFixed(2);
                  discountAmtPrev += parseFloat(
                    itemDiscountValuePrev
                      ? itemDiscountValuePrev
                      : 0,
                  );
                });

                // Reference from labels
                var referenceLabelFields = ``;
                // item.so_details.forEach((soDetail, index) => {
                //     referenceLabelFields += `<input type = "hidden" class = "reference_from_label_${currentOrderIndexVal}" value = "${soDetail.book_code + "-" + soDetail.document_number + " : " + soDetail.balance_qty}"/>`;
                // });

                // var soItemIds = [];
                // item.so_details.forEach((soDetail) => {
                //     soItemIds.push(soDetail.id);
                // });
                var disableQty = "";
                var bundleInfoIcon = ``;
                if (openPullType == "so") {
                  bundleInfoIcon =
                    `<div class="me-50 cursor-pointer item_bundles" onclick = "getBundles(${currentOrderIndexVal}, ${item.id})" id = "item_bundles_${currentOrderIndexVal}">    <span data-bs-toggle="tooltip" data-bs-placement="top" title="Package" class="text-primary"><i data-feather="package"></i></span>`;
                  // disableQty = "readonly";
                }
                var headerStoreId = $("#store_id_input").val();
                var headerStoreCode = $("#store_id_input").attr(
                  "data-name",
                );

                var newProcessedRow = `
                           <tr id = "item_row_${currentOrderIndexVal}" class = "item_header_rows" onclick = "onItemClick('${currentOrderIndexVal}');">
                               <td class="customernewsection-form">
                               <div class="form-check form-check-primary custom-checkbox">
                                   <input type="checkbox" class="form-check-input item_row_checks" id="item_row_check_${currentOrderIndexVal}" del-index = "${currentOrderIndexVal}">
                                   <label class="form-check-label" for="item_row_check_${currentOrderIndexVal}"></label>
                               </div>
                                                                       </td>
                               <td class="poprod-decpt">
   
                                   <input type = "hidden" id = "qt_id_${currentOrderIndexVal}" value = "${item?.id}" name = "quotation_item_ids[]"/>
                                   <input type = "hidden" id = "qt_id_header_${currentOrderIndexVal}" value = "${
                  item?.id == 0 ? currentOrder?.id : ""
                }" name = "quotation_item_ids_header[]"/>
   
                                   <input type = "hidden" id = "qt_type_id_${currentOrderIndexVal}" value = "${currentOrder.document_type}" name = "quotation_item_type[]"/>
                                   <input type = "hidden" id = "lr_id_${currentOrderIndexVal}" value = "${currentOrder.lr_id}" name = "lr_id[]"/>
   
                                   <input type = "hidden" id = "qt_book_id_${currentOrderIndexVal}" value = "${currentOrder?.book_id}" />
                                   <input type = "hidden" id = "qt_book_code_${currentOrderIndexVal}" value = "${currentOrder?.book_code}" />
   
                                   <input type = "hidden" id = "qt_document_no_${currentOrderIndexVal}" value = "${currentOrder?.document_number}" />
                                   <input type = "hidden" id = "qt_document_date_${currentOrderIndexVal}" value = "${currentOrder?.document_date}" />
   
                                   <input type = "hidden" id = "qt_id_${currentOrderIndexVal}" value = "${currentOrder?.document_number}" />
   
   
                                   ${landLeasePullHtml}
                                   ${plistHTML}
   
                               <input type="text" readonly id = "items_dropdown_${currentOrderIndexVal}" 
                               name="item_code[${currentOrderIndexVal}]" placeholder="Select" 
                               class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" autocomplete="off" 
                               data-name="${item?.item?.item_name}" data-code="${item?.item?.item_code}" data-id="${item?.item?.id}" 
                               hsn_code = "${item?.item?.hsn?.code}" item-name = "${item?.item?.item_name}" 
                               item-locations = "${JSON.stringify([])}"
                               value = "${currentOrder?.document_number}"
                               >
                               <input type = "hidden" name = "item_id[]" id = "items_dropdown_${currentOrderIndexVal}_value" value = "${item?.item_id}"></input>
                                                                       </td>
                                                                       <td hidden class="poprod-decpt">
            <input type="text" id = "items_name_${currentOrderIndexVal}" name = "item_name[${currentOrderIndexVal}]" 
            class="form-control mw-100"  value = "${item?.item?.item_name}" readonly>
                                                                   </td>
                          <td class="poprod-decpt">
            <input type="text" id = "source_name_${currentOrderIndexVal}" name = "source_name[${currentOrderIndexVal}]" 
            class="form-control mw-100"  value = "${currentOrder?.source?.name}" readonly>
                                                                   </td>
                                                                    <td class="poprod-decpt">
            <input type="text" id = "desitnation_name_${currentOrderIndexVal}" name = "destination_name[${currentOrderIndexVal}]" 
            class="form-control mw-100"  value = "${currentOrder?.destination?.name}" readonly>
                                                                   </td>
                                                                    <td class="poprod-decpt">
            <input type="text" id = "points_${currentOrderIndexVal}" name = "points[${currentOrderIndexVal}]" 
            class="form-control mw-100"  value = "${currentOrder?.points}" readonly>
             <td class="poprod-decpt">
            <input type="text" id = "articles_${currentOrderIndexVal}" name = "articles[${currentOrderIndexVal}]" 
            class="form-control mw-100"  value = "${currentOrder?.articles}" readonly>
                                                                   </td>
                                                                   <td class="poprod-decpt">
            <input type="text" id = "weight_${currentOrderIndexVal}" name = "weight[${currentOrderIndexVal}]" 
            class="form-control mw-100"  value = "${currentOrder?.weight}" readonly>
                                                                   </td>
                                                                       <td hidden class="poprod-decpt" id='attribute_section_${currentOrderIndexVal}'>
                               <button id = "attribute_button_${currentOrderIndexVal}" ${
                  item?.item_attributes_array?.length > 0 ? "" : "disabled"
                } type = "button" data-bs-toggle="modal" onclick = "setItemAttributes('items_dropdown_${currentOrderIndexVal}', '${currentOrderIndexVal}', true);" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                               <input type = "hidden" name = "attribute_value_${currentOrderIndexVal}" />
   
                               </td>
                                                                       <td hidden>
                               <select class="form-select" name = "uom_id[]" id = "uom_dropdown_${currentOrderIndexVal}">
   
                               </select>
                                   </td>
                                   
                                   <td hidden><input ${
                  disableQty ? "readonly" : ""
                } type="text" id = "item_qty_${currentOrderIndexVal}" name = "item_qty[${currentOrderIndexVal}]" oninput = "changeItemQty(this, '${currentOrderIndexVal}');" value = "${item?.balance_qty}" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" max = "${item?.balance_qty}"/></td>
                                   <td hidden ><input readonly type="text" id = "item_rate_${currentOrderIndexVal}" name = "item_rate[]" oninput = "changeItemRate(this, '${currentOrderIndexVal}');" ${amountMax} value = "${item?.rate}" class="form-control mw-100 text-end" onblur = "setFormattedNumericValue(this);" /></td>
                                   <td hidden ><input type="text" id = "item_value_${currentOrderIndexVal}" disabled class="form-control mw-100 text-end item_values_input" 
                                    value = "${
                  (item?.balance_qty ? item?.balance_qty : 0) * (item?.rate ? item?.rate : 0)
                }" /></td>
                                   <input type = "hidden" id = "header_discount_${currentOrderIndexVal}" value = "${item?.header_discount_amount}" ></input>
                                   <input type = "hidden" id = "header_expense_${currentOrderIndexVal}" value = "${item?.header_expense_amount}"></input>
                               <td>
                               <div class="position-relative d-flex align-items-center">
                                   <input type="text" id = "item_discount_${currentOrderIndexVal}" disabled class="form-control mw-100 text-end item_discounts_input" style="width: 70px" value = "${discountAmtPrev}"/>
                                   <div class="ms-50">
                                       <button type = "button" ${
                  invoiceToFollowParam ? "disabled" : ""
                } onclick = "onDiscountClick('item_value_${currentOrderIndexVal}', '${currentOrderIndexVal}')" data-bs-toggle="modal" data-bs-target="#discount" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Add</button>
                                   </div>
                               </div>
                               </td>
                                   <input type="hidden" id = "item_tax_${currentOrderIndexVal}" value = "${item?.tax_amount}" class="form-control mw-100 text-end item_taxes_input" style="width: 70px" />
                                   <td><input type="text" id = "value_after_discount_${currentOrderIndexVal}" value = "${
                  (currentOrder?.total_freight_amount) - item?.item_discount_amount
                }" disabled class="form-control mw-100 text-end item_val_after_discounts_input" /></td>
                                   <input type = "hidden" id = "value_after_header_discount_${currentOrderIndexVal}" class = "item_val_after_header_discounts_input" value = "${
                  (currentOrder?.total_freight_amount) - item?.item_discount_amount - item?.header_discount_amount
                }" ></input>
                                   <input type="hidden" id = "item_total_${currentOrderIndexVal}" value = "${
                  (currentOrder?.total_freight_amount) - item?.item_discount_amount - item?.header_discount_amount
                  + (item?.tax_amount)
                }" disabled class="form-control mw-100 text-end item_totals_input" />
                               <td>
   
                                   <div class="d-flex">
                                       <div style = "display:none;" class="me-50 cursor-pointer item_store_locations" data-bs-toggle="modal" data-bs-target="#location" onclick = "openStoreLocationModal(${currentOrderIndexVal})" data-stores = '[]' id = 'data_stores_${currentOrderIndexVal}'>    <span data-bs-toggle="tooltip" data-bs-placement="top" title="Store Location" class="text-primary"><i data-feather="map-pin"></i></span></div>
                                       <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick = "setItemRemarks('item_remarks_${currentOrderIndexVal}');">        <span data-bs-toggle="tooltip" data-bs-placement="top" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span></div>
                                       ${bundleInfoIcon}
                                   </div>
                               </td>
                               <input type="hidden" id = "item_remarks_${currentOrderIndexVal}" name = "item_remarks[]" value = "${itemRemarks}"/>
   
                                                                   </tr>
                           `;
                mainTableItem.insertAdjacentHTML("beforeend", newProcessedRow);
                initializeAutocomplete1(
                  "items_dropdown_"
                    + currentOrderIndexVal,
                  currentOrderIndexVal,
                );
                renderIcons();
                const totalValue = item.item_discount_amount;
                document.getElementById("discount_main_table")
                  .setAttribute("total-value", totalValue);
                document.getElementById("discount_main_table")
                  .setAttribute(
                    "item-row",
                    "item_value_"
                      + currentOrderIndexVal,
                  );
                document.getElementById("discount_main_table")
                  .setAttribute("item-row-index", currentOrderIndexVal);

                item.discount_ted.forEach((ted, tedIndex) => {
                  addHiddenInput(
                    "item_discount_name_"
                      + currentOrderIndexVal + "_"
                      + tedIndex,
                    ted.ted_name,
                    `item_discount_name[${currentOrderIndexVal}][${tedIndex}]`,
                    "discount_names_hidden_"
                      + currentOrderIndexVal,
                    "item_row_"
                      + currentOrderIndexVal,
                  );
                  var percentage = ted.ted_percentage;
                  var itemValue = document.getElementById(
                    "item_value_" + currentOrderIndexVal,
                  ).value;
                  if (!percentage) {
                    percentage = ted.ted_amount
                      / itemValue * 100;
                  }
                  addHiddenInput(
                    "item_discount_percentage_"
                      + currentOrderIndexVal + "_"
                      + tedIndex,
                    percentage,
                    `item_discount_percentage[${currentOrderIndexVal}][${tedIndex}]`,
                    "discount_percentages_hidden_"
                      + currentOrderIndexVal,
                    "item_row_"
                      + currentOrderIndexVal,
                  );
                  var itemDiscountValue = ((itemValue
                    * percentage) / 100).toFixed(2);

                  addHiddenInput(
                    "item_discount_value_"
                      + currentOrderIndexVal + "_"
                      + tedIndex,
                    itemDiscountValue,
                    `item_discount_value[${currentOrderIndexVal}][${tedIndex}]`,
                    "discount_values_hidden_"
                      + currentOrderIndexVal,
                    "item_row_"
                      + currentOrderIndexVal,
                  );
                });
                // Item Delivery Schedule
                if (item.item_deliveries) {
                  item.item_deliveries.forEach((delivery, deliveryIndex) => {
                    addHiddenInput(
                      "item_delivery_schedule_qty_"
                        + currentOrderIndexVal + "_"
                        + deliveryIndex,
                      delivery.qty,
                      `item_delivery_schedule_qty[${currentOrderIndexVal}][${deliveryIndex}]`,
                      "delivery_schedule_qties_hidden_"
                        + currentOrderIndexVal,
                      "item_row_"
                        + currentOrderIndexVal,
                    );
                    addHiddenInput(
                      "item_delivery_schedule_date"
                        + currentOrderIndexVal + "_"
                        + deliveryIndex,
                      delivery
                        .delivery_date,
                      `item_delivery_schedule_date[${currentOrderIndexVal}][${deliveryIndex}]`,
                      "delivery_schedule_dates_hidden_"
                        + currentOrderIndexVal,
                      "item_row_"
                        + currentOrderIndexVal,
                    );
                  });
                }

                var itemUomsHTML = ``;
                if (item.item.uom && item.item.uom.id) {
                  itemUomsHTML += `<option value = '${item.item.uom.id}' ${
                    item.item.uom.id == item.uom_id ? "selected" : ""
                  }>${item.item.uom.alias}</option>`;
                }
                item.item.alternate_uoms.forEach(singleUom => {
                  if (singleUom.is_selling) {
                    itemUomsHTML += `<option value = '${singleUom.uom.id}' ${
                      singleUom.uom.id == item.uom_id ? "selected" : ""
                    } >${singleUom.uom?.alias}</option>`;
                  }
                });
                document.getElementById(
                  "uom_dropdown_"
                    + currentOrderIndexVal,
                ).innerHTML = itemUomsHTML;
                // getStoresData(currentOrderIndexVal,null,false);
                getItemTax(currentOrderIndexVal);
                setAttributesUI(currentOrderIndexVal);
                currentOrderIndexVal += 1;
              });
            }
            // Order Discount
            currentOrder.discount_ted.forEach((orderDiscount, orderDiscountIndex) => {
              document.getElementById("new_order_discount_name").value = orderDiscount.ted_name;
              document.getElementById("new_order_discount_id").value = orderDiscount.ted_id;
              var currentOrderDiscountPercentage = orderDiscount
                .ted_percentage;
              if (!currentOrderDiscountPercentage) {
                currentOrderDiscountPercentage = orderDiscount
                  .ted_amount / orderDiscount.assessment_amount * 100;
              }
              document.getElementById("new_order_discount_percentage")
                .value = currentOrderDiscountPercentage;
              document.getElementById("new_order_discount_value").value = orderDiscount.ted_amount;
              addOrderDiscount(null, false);
            });
            // Order Expense
            currentOrder.expense_ted.forEach((orderExpense, orderExpenseIndex) => {
              document.getElementById("order_expense_name").value = orderExpense.ted_name;
              document.getElementById("order_expense_id").value = orderExpense.ted_id;
              var currentOrderExpensePercentage = orderExpense
                .ted_percentage;
              if (!currentOrderExpensePercentage) {
                currentOrderExpensePercentage = orderExpense
                  .ted_amount / orderExpense.assessment_amount * 100;
              }
              document.getElementById("order_expense_percentage").value = currentOrderExpensePercentage;
              document.getElementById("order_expense_value").value = orderExpense.ted_amount;
              addOrderExpense(null, false);
            });

            setAllTotalFields();

            disableHeader();

            changeDropdownOptions(
              document.getElementById("customer_id_input"),
              [
                "billing_address_dropdown",
                "shipping_address_dropdown",
              ],
              ["billing_addresses", "shipping_addresses"],
              "/customer/addresses/",
              "vendor_dependent",
              [],
              [{
                key: "phone_no",
                value: currentOrder?.customer_phone_no,
              }],
            );

            // $("#shipping_address_dropdown").select2();
            // $("#shipping_address_dropdown").prop('disabled', false);
            // $("#shipping_address_dropdown").val(currentOrder.shipping_address_details.id).trigger('change');
            // $("#billing_address_dropdown").select2();
            // $("#billing_address_dropdown").prop('disabled', false);
            // $("#billing_address_dropdown").val(currentOrder.billing_address_details.id).trigger('change');

            // console.log(currentOrder.billing_address_details?.id || 0);
            // console.log($("#shipping_address_dropdown").val(), "AD1");
            // console.log($("#billing_address_dropdown").val(), "AD2");
          }
        });
        if (docType != "si") {
          for (let index = 0; index < currentOrderIndexVal; index++) {
            getStoresData(index, null, false);
          }
        }
        reEnableSelectedPullType(openPullType);
      },
      error: function(xhr) {
        console.error("Error fetching customer data:", xhr.responseText);
      },
    });
  } else {
    Swal.fire({
      title: "Error!",
      text: "Please select at least one one document",
      icon: "error",
    });
  }
}

function getOrders(type = "so") {
  const apiUrl = "{{ route('sale.transporterInvoice.pull.items') }}";
  const checkboxColumn = () => ({
    data: null,
    name: "checkbox",
    orderable: false,
    searchable: false,
    render: (row, _, __, meta) => {
      let docId = row?.header?.id;
      let mainDocId = row?.header?.id;
      if (type === "so") {
        docId = row?.header?.customer_id;
      }
      if (type === "lr") {
        docId = row?.id;
      }
      let custId =  row?.consignor?.id;
      const soItemId = JSON.stringify(row?.transport_order?.so_item_ids);
      const lrId = row?.id;
      const itemId = row?.id;
      const isEnabled = row?.stock_qty > 0 || ["land-lease", "plist", "lr"].includes(type);
      return `<div class="form-check form-check-inline me-0">
               <input class="form-check-input pull_checkbox po_checkbox" type="checkbox"
                   ${isEnabled ? "" : "disabled"}
                   name="po_check"
                   id="po_checkbox_${meta.row}"
                   oninput="checkQuotation(this, '', '${type}');"
                   doc-id="${docId}"
                   cust="${custId}"
                   current-doc-id="0"
                   document-id="${mainDocId}"
                   so-item-id="${soItemId}"
                   lr-id="${row.id}"
                   item-id="${itemId}"
                   balance_qty="${row.balance_qty || 0}"
                   pl_item_detail_id = "${row.id}"
                   detail-id="${row?.id}">
           </div>`;
    },
  });

  const getColumns = () => {
    if (type === "land-lease") {
      return [
        checkboxColumn(),
        {
          data: "header.series.book_code",
          name: "book_code",
        },
        {
          data: "header.document_no",
          name: "document_no",
        },
        {
          data: "header.document_date",
          name: "document_date",
          render: d => moment(d).format("D/M/Y"),
        },
        {
          data: "header.customer.company_name",
          name: "company_name",
        },
        {
          data: "header.plots[0].land.name",
          name: "land_name",
        },
        {
          data: "header.plots",
          name: "plot_names",
          render: plots => plots?.map(p => p?.plot?.plot_name).join(", ") || "N/A",
        },
        {
          data: "type",
          name: "type",
        },
        {
          data: "installment_cost",
          name: "installment_cost",
        },
        {
          data: "due_date",
          name: "due_date",
          render: d => moment(d).format("D/M/Y"),
        },
      ];
    } else if (type === "lr") {
      return [
        checkboxColumn(),
        {
          data: "series",
          name: "series",
        },
        {
          data: "doc_no",
          name: "doc_no",
        },
        {
          data: "doc_date",
          name: "doc_date",
        },
        {
          data: "currency_code",
          name: "currency_code",
        },
        {
          data: "customer_name",
          name: "customer_name",
        },
        {
          data: "source_name",
          name: "source_name",
        },
        {
          data: "destination_name",
          name: "destination_name",
        },
         {
          data: "total_charges",
          name: "total_charges",
        },
      ];
    } else if (type === "pl") {
      return [
        checkboxColumn(),
        {
          data: "transport_order",
          name: "transport_order",
          render: so => `${so?.book_code} - ${so?.document_number}`,
        },
        {
          data: "transport_order.document_date",
          name: "document_date",
        },
        {
          data: "transport_order.customer_code",
          name: "customer_code",
        },
        {
          data: "header.book_code",
          name: "book_code",
        },
        {
          data: "header.document_number",
          name: "document_number",
        },
        {
          data: "item_code",
          name: "item_code",
        },
        {
          data: "item_name",
          name: "item_name",
        },
        {
          data: "attributes_data",
          name: "attributes_data",
        },
        {
          data: "uom_name",
          name: "uom_name",
        },
        {
          data: "rate",
          name: "rate",
        },
        {
          data: "picked_qty",
          name: "picked_qty",
        },
        {
          data: "pl_avl_qty",
          name: "pl_avl_qty",
        },
        {
          data: "avl_stock",
          name: "avl_stock",
        },
      ];
    } else {
      return [
        checkboxColumn(),
        {
          data: "header.book_code",
          name: "book_code",
        },
        {
          data: "header.document_number",
          name: "document_number",
        },
        {
          data: "header.document_date",
          name: "document_date",
        },
        {
          data: "header.currency_code",
          name: "currency_code",
        },
        {
          data: "header.customer.company_name",
          name: "company_name",
        },
        {
          data: "item_code",
          name: "item_code",
        },
        {
          data: "item_name",
          name: "item_name",
        },
        {
          data: "attributes",
          name: "attributes",
          render: attrs =>
            attrs?.map(attr =>
              `<span class="badge rounded-pill badge-light-primary">${attr.attribute_name} : ${attr.attribute_value}</span>`
            ).join("") || "",
        },
        {
          data: "uom.name",
          name: "uom",
        },
        {
          data: "order_qty",
          name: "order_qty",
        },
        {
          data: "balance_qty",
          name: "balance_qty",
        },
        {
          data: "avl_stock",
          name: "avl_stock",
        },
        {
          data: "rate",
          name: "rate",
        },
      ];
    }
  };

  let tableSelector = "#so_invoice_table";
  if (type === "plist") {
    tableSelector = "#plist_invoice_table";
  } else if (type === "pl") {
    tableSelector = "#pl_invoice_table";
  } else if (type === "land-lease") {
    tableSelector = "#land_lease_invoice_table";
  } else if (type === "dnote") {
    tableSelector = "#dnote_invoice_table";
  } else if (type === "lr") {
    tableSelector = "#lorry_receipt_table";
  } else {
    tableSelector = "#so_invoice_table";
  }

  const selectedIds = Array.from(document.getElementsByClassName("item_header_rows"))
    .map((_, i) => document.getElementById("lr_id_" + i))
    .filter(Boolean)
    .map(el => el.value);
  const filters = {
    doc_type: $(tableSelector + "_value"),
    header_book_id: $("#series_id_input"),
    land_plot_id: $("#land_plot_id_qt_val_land"),
    land_parcel_id: $("#land_parcel_id_qt_val_land"),
    store_id: $("#store_id_input"),
    sub_store_id: $("#sub_store_id_input"),
    selected_ids: selectedIds,
  };

  if (type === "plist") {
    filters.customer_id = $("#customer_id_plist_val");
    filters.book_id = $("#book_id_plist_val");
    filters.item_id = $("#item_id_plist_val");
    filters.document_id = $("#document_id_plist_val");
  } else if (type === "land-lease") {
    filters.customer_id = $("#customer_id_qt_val_land");
    filters.book_id = $("#book_id_qt_val_land");
    filters.item_id = $("#item_id_qt_val");
    filters.document_id = $("#document_id_qt_val_land");
  } else if (type === "lr") {
    filters.customer_id = $("#customer_id_lr_val");
    filters.book_id = $("#book_id_lr_val");
    filters.item_id = $("#item_id_qt_val");
    filters.document_id = $("#document_id_lr_val");
  } else {
    filters.customer_id = $("#customer_id_qt_val");
    filters.book_id = $("#book_id_qt_val");
    filters.item_id = $("#item_id_qt_val");
    filters.document_id = $("#document_id_qt_val");
  }

  console.log("Table Selector", tableSelector);
  console.log("filters", filters);
  if ($.fn.DataTable.isDataTable(tableSelector)) {
    $(tableSelector).DataTable().destroy();
  }

  initializeDataTable(
    tableSelector,
    apiUrl,
    getColumns(),
    filters,
    "Sales Items - " + type.toUpperCase(),
    [], // Buttons
    [], // Order
    "landscape",
    "GET",
    false, // serverSide
    false, // processing
  );
}

function initializeAutocompleteQt(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "") {
  console.log(selector);
  $("#" + selector).autocomplete({
    source: function(request, response) {
      $.ajax({
        url: "/search",
        method: "GET",
        dataType: "json",
        data: {
          q: request.term,
          type: typeVal,
          customer_id: $("#customer_id_qt_val").val(),
          header_book_id: $("#series_id_input").val(),
        },
        success: function(data) {
          response($.map(data, function(item) {
            return {
              id: item.id,
              label: `${item[labelKey1]} ${labelKey2 ? (item[labelKey2] ? "(" + item[labelKey2] + ")" : "") : ""}`,
              code: item[labelKey1] || "",
            };
          }));
        },
        error: function(xhr) {
          console.error("Error fetching customer data:", xhr.responseText);
        },
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
    },
  }).focus(function() {
    if (this.value === "") {
      $(this).autocomplete("search", "");
    }
  });
}
$(document).on("click", "#select_lorry_button", function() {
  openHeaderPullModal("lr");
});

var openPullType = "so";

function openHeaderPullModal(type = "so") {
  const pslipHeader = document.getElementById("packing_slip_nos_header");
  if (pslipHeader) {
    pslipHeader.style.removeProperty("display");
  }
  const avlStock = document.getElementById("avl_stock_header");
  if (avlStock) {
    avlStock.style.removeProperty("display");
  }
  // document.getElementById('qts_data_table').innerHTML = '';
  // document.getElementById('qts_data_table_land').innerHTML = '';
  // document.getElementById('qts_data_table_plist').innerHTML = '';
  if (type == "si") {
    openPullType = "so";
    initializeAutocompleteQt("book_code_input_qt", "book_id_qt_val", "book_so", "book_code", "book_name");
    initializeAutocompleteQt(
      "document_no_input_qt",
      "document_id_qt_val",
      "transport_order_document",
      "document_number",
      "document_number",
    );
  } else if (type == "dnote") {
    if (pslipHeader) {
      pslipHeader.style.display = "none";
    }
    if (avlStock) {
      avlStock.style.display = "none";
    }
    openPullType = "dnote";
    initializeAutocompleteQt("book_code_input_qt", "book_id_qt_val", "book_din", "book_code", "book_name");
    initializeAutocompleteQt(
      "document_no_input_qt",
      "document_id_qt_val",
      "din_document",
      "document_number",
      "document_number",
    );
  } // else if (type === "dnote") {
  //     openPullType = "so";
  //     initializeAutocompleteQt("book_code_input_qt", "book_id_qt_val", "book_so", "book_code", "book_name");
  //     initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "transport_order_document", "document_number", "document_number");
  // }
  else if (type === "land-lease") {
    openPullType = "land-lease";
    initializeAutocompleteQt(
      "book_code_input_qt_land",
      "book_id_qt_val_land",
      "book_land_lease",
      "book_code",
      "book_name",
    );
    initializeAutocompleteQt(
      "document_no_input_qt_land",
      "document_id_qt_val_land",
      "land_lease_document",
      "document_number",
      "document_number",
    );
    initializeAutocompleteQt(
      "land_parcel_input_qt_land",
      "land_parcel_id_qt_val_land",
      "land_lease_parcel",
      "name",
      "name",
    );
    initializeAutocompleteQt(
      "land_plot_input_qt_land",
      "land_plot_id_qt_val_land",
      "land_lease_plots",
      "plot_name",
      "plot_name",
    );
  } else if (type === "lr") {
    openPullType = "lr";
    initializeAutocompleteQt("book_code_input_lr", "book_id_lr_val", "book_lr", "book_code", "book_name");
    initializeAutocompleteQt("document_no_input_lr", "document_id_lr_val", "lr_document", "document_number");
    initializeAutocompleteQt("source_input_lr", "source_id_lr_val", "lr_source", "name", "name");
  } else if (type === "plist") {
    openPullType = "plist";
    initializeAutocompleteQt("book_code_input_plist", "book_id_plist_val", "book_plist", "book_code", "book_name");
    initializeAutocompleteQt("document_no_input_plist", "document_id_plist_val", "plist_document", "document_number");
    initializeAutocompleteQt(
      "customer_code_input_plist",
      "customer_id_plist_val",
      "customer",
      "customer_code",
      "company_name",
    );
    initializeAutocompleteQt(
      "item_name_input_plist",
      "item_id_plist_val",
      "transport_module_items",
      "item_code",
      "item_name",
    );
  } else if (type === "pl") {
    openPullType = "pl";
    initializeAutocompleteQt("book_code_input_pl", "book_id_pl_val", "book_pl", "book_code", "book_name");
    initializeAutocompleteQt("document_no_input_pl", "document_id_pl_val", "pl_document", "document_number");
    initializeAutocompleteQt(
      "customer_code_input_pl",
      "customer_id_pl_val",
      "customer",
      "customer_code",
      "company_name",
    );
    initializeAutocompleteQt(
      "item_name_input_pl",
      "item_id_pl_val",
      "transport_module_items",
      "item_code",
      "item_name",
    );
  } else {
    openPullType = "so";
    initializeAutocompleteQt("book_code_input_qt", "book_id_qt_val", "book_so", "book_code", "book_name");
    initializeAutocompleteQt(
      "document_no_input_qt",
      "document_id_qt_val",
      "transport_order_document",
      "document_number",
      "document_number",
    );
  }
  initializeAutocompleteQt("customer_code_input_qt", "customer_id_qt_val", "customer", "customer_code", "company_name");
  initializeAutocompleteQt(
    "customer_code_input_qt_land",
    "customer_id_qt_val_land",
    "customer",
    "customer_code",
    "company_name",
  );
  initializeAutocompleteQt("item_name_input_qt", "item_id_qt_val", "transport_module_items", "item_code", "item_name");
  if (type === "land-lease") {
    getOrders("land-lease");
  } else {
    getOrders(type);
  }
}

let current_doc_id = 0;

function checkQuotation(checkbox, _, type) {
    let custId = checkbox.getAttribute('cust');
    let isChecked = checkbox.checked;

    let allCheckboxes = document.querySelectorAll('input[name="po_check"]');

    if (isChecked) {
        allCheckboxes.forEach(cb => {
            if (cb.getAttribute('cust') !== custId) {
                cb.disabled = true;
                cb.checked = false; // uncheck others
            } else {
                cb.disabled = false;
            }
        });
    } else {
        // Agar koi bhi checked na ho to sab enable karo
        let anyChecked = Array.from(allCheckboxes).some(cb => cb.checked);
        if (!anyChecked) {
            allCheckboxes.forEach(cb => cb.disabled = false);
        }
    }
}


// Disable form submit on enter button
document.querySelector("form").addEventListener("keydown", function(event) {
  if (event.key === "Enter") {
    event.preventDefault(); // Prevent form submission
  }
});
$("input[type='text']").on("keydown", function(event) {
  if (event.key === "Enter") {
    event.preventDefault(); // Prevent form submission
  }
});

initializeAutocompleteStores("new_store_code_input", "new_store_id_input", "store", "store_code");
initializeAutocompleteStores("new_rack_code_input", "new_rack_id_input", "store_rack", "rack_code");
initializeAutocompleteStores("new_shelf_code_input", "new_shelf_id_input", "rack_shelf", "shelf_code");
initializeAutocompleteStores("new_bin_code_input", "new_bin_id_input", "shelf_bin", "bin_code");

function openStoreLocationModal(index) {
  const storeElement = document.getElementById("data_stores_" + index);
  const storeTable = document.getElementById("item_location_table");
  let storeFooter = `
   <tr>
       <td colspan="3"></td>
       <td class="text-dark"><strong>Total</strong></td>
       <td class="text-dark" id = "total_item_store_qty"><strong>0.00</strong></td>
   </tr>
   `;
  if (storeElement) {
    storeTable.setAttribute("current-item-index", index);
    let storesInnerHtml = ``;
    let totalStoreQty = 0;
    const storesData = JSON.parse(decodeURIComponent(storeElement.getAttribute("data-stores")));
    if (storesData && storesData.length > 0) {
      storesData.forEach((store, storeIndex) => {
        storesInnerHtml += `
               <tr id = "item_store_${storeIndex}">
                   <td>${storeIndex + 1}</td>
                   <td>${store.rack_code}</td>
                   <td>${store.shelf_code}</td>
                   <td>${store.bin_code}</td>
                   <td>${store.qty}</td>
               </tr>
               `;
        totalStoreQty += parseFloat(store.qty ? store.qty : 0);
      });

      storeTable.innerHTML = storesInnerHtml + storeFooter;
      document.getElementById("total_item_store_qty").textContent = totalStoreQty.toFixed(2);
    } else {
      storeTable.innerHTML = storesInnerHtml + storeFooter;
      document.getElementById("total_item_store_qty").textContent = "0.00";
    }
  } else {
    return;
  }
  renderIcons();
}

function removeItemStore(index, itemIndex) {
  const storeElement = document.getElementById("data_stores_" + itemIndex);
  if (storeElement) {
    const storesData = JSON.parse(decodeURIComponent(storeElement.getAttribute("data-stores")));
    if (storesData && storesData.length > 0) {
      storesData.splice(index, 1);
      storeElement.setAttribute("data-stores", encodeURIComponent(JSON.stringify(storesData)));
      openStoreLocationModal(itemIndex);
    }
  }
}

function addItemStore() {
  const itemIndex = document.getElementById("item_location_table").getAttribute("current-item-index");

  const itemStoreId = $("#new_store_id_input").val();
  const itemStoreCode = $("#new_store_code_input").val();

  const itemRackId = $("#new_rack_id_input").val();
  const itemRackCode = $("#new_rack_code_input").val();

  const itemShelfId = $("#new_shelf_id_input").val();
  const itemShelfCode = $("#new_shelf_code_input").val();

  const itemBinId = $("#new_bin_id_input").val();
  const itemBinCode = $("#new_bin_code_input").val();

  const itemStoreQty = $("#new_location_qty").val();

  if (
    itemStoreId && itemStoreCode && itemRackId && itemRackCode && itemShelfId && itemShelfCode && itemBinId
    && itemBinCode && itemStoreQty
  ) {
    const newStoreItem = {
      store_id: itemStoreId,
      store_code: itemStoreCode,
      rack_id: itemRackId,
      rack_code: itemRackCode,
      shelf_id: itemShelfId,
      shelf_code: itemShelfCode,
      bin_id: itemBinId,
      bin_code: itemBinCode,
      qty: itemStoreQty,
    };
    const storeElement = document.getElementById("data_stores_" + itemIndex);
    if (storeElement) {
      const storesData = JSON.parse(decodeURIComponent(storeElement.getAttribute("data-stores")));
      if (storesData) {
        storesData.push(newStoreItem);
        storeElement.setAttribute("data-stores", encodeURIComponent(JSON.stringify(storesData)));
        openStoreLocationModal(itemIndex);
        resetStoreFields();
      }
    }
  }
}

function initializeAutocompleteStores(selector, siblingSelector, type, labelField) {
  $("#" + selector).autocomplete({
    source: function(request, response) {
      let dataPayload = {
        q: request.term,
        type: type,
      };
      if (type == "store_rack") {
        dataPayload.store_id = $("#new_store_id_input").val();
      }
      if (type == "rack_shelf") {
        dataPayload.rack_id = $("#new_rack_id_input").val();
      }
      if (type == "shelf_bin") {
        dataPayload.shelf_id = $("#new_shelf_id_input").val();
      }
      $.ajax({
        url: "/search",
        method: "GET",
        dataType: "json",
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
          console.error("Error fetching customer data:", xhr.responseText);
        },
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
    },
  }).focus(function() {
    if (this.value === "") {
      $(this).autocomplete("search", "");
    }
  });
}

function resetStoreFields() {
  $("#new_store_id_input").val("");
  $("#new_store_code_input").val("");

  $("#new_rack_id_input").val("");
  $("#new_rack_code_input").val("");

  $("#new_shelf_id_input").val("");
  $("#new_shelf_code_input").val("");

  $("#new_bin_id_input").val("");
  $("#new_bin_code_input").val("");

  $("#new_location_qty").val("");
}

function saveAddressShipping() {
  $.ajax({
    url: "{{ route('sales_order.add.address') }}",
    method: "POST",
    dataType: "json",
    data: {
      type: "shipping",
      country_id: $("#shipping_country_id_input").val(),
      state_id: $("#shipping_state_id_input").val(),
      city_id: $("#shipping_city_id_input").val(),
      address: $("#shipping_address_input").val(),
      pincode: $("#shipping_pincode_input").val(),
      phone: "",
      fax: "",
      customer_id: $("#customer_id_input").val(),
    },
    success: function(data) {
      if (data && data.data) {
        $("#edit-address-shipping").modal("hide");
        $("#current_shipping_address_id").val(data.data.id);
        $("#current_shipping_country_id").val(data.data.country_id);
        $("#current_shipping_state_id").val(data.data.state_id);
        $("#current_shipping_address").text(data.data.display_address);
        $("#new_shipping_country_id").val(data.data.country_id);
        $("#new_shipping_state_id ").val(data.data.state_id);
        $("#new_shipping_city_id").val(data.data.city_id);
        $("#new_shipping_type").val(data.data.type);
        $("#new_shipping_pincode").val(data.data.pincode);
        $("#new_shipping_phone").val(data.data.phone);
      }
    },
    error: function(xhr) {
      console.error("Error fetching customer data:", xhr.responseText);
    },
  });
}

function saveAddressBilling(type) {
  $.ajax({
    url: "{{ route('sales_order.add.address') }}",
    method: "POST",
    dataType: "json",
    data: {
      type: "billing",
      country_id: $("#billing_country_id_input").val(),
      state_id: $("#billing_state_id_input").val(),
      city_id: $("#billing_city_id_input").val(),
      address: $("#billing_address_input").val(),
      pincode: $("#billing_pincode_input").val(),
      phone: "",
      fax: "",
      customer_id: $("#customer_id_input").val(),
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
        $("#new_billing_state_id ").val(data.data.state_id);
        $("#new_billing_city_id").val(data.data.city_id);
        $("#new_billing_type").val(data.data.type);
        $("#new_billing_pincode").val(data.data.pincode);
        $("#new_billing_phone").val(data.data.phone);
      }
    },
    error: function(xhr) {
      console.error("Error fetching customer data:", xhr.responseText);
    },
  });
}

function onBillingAddressChange(element) {
  $("#current_billing_address_id").val(element.value);
  $("#billAddressEditBtn").click();
}

function onShippingAddressChange(element) {
  $("#current_shipping_address_id").val(element.value);
  $("#shipAddressEditBtn").click();
}
$(document).on("click", "#amendmentSubmit", (e) => {
  let actionUrl = "{{ route('sale.order.amend', isset($order) ? $order->id : 0) }}";
  fetch(actionUrl).then(response => {
    return response.json().then(data => {
      if (data.status == 200) {
        Swal.fire({
          title: "Success!",
          text: data.message,
          icon: "success",
        });
        location.reload();
      } else {
        Swal.fire({
          title: "Error!",
          text: data.message,
          icon: "error",
        });
      }
    });
  });
});

function resetPostVoucher() {
  document.getElementById("voucher_doc_no").value = "";
  document.getElementById("voucher_date").value = "";
  document.getElementById("voucher_book_code").value = "";
  document.getElementById("voucher_currency").value = "";
  document.getElementById("posting-table").innerHTML = "";
  document.getElementById("posting_button").style.display = "none";
}

function onPostVoucherOpen(type = "not_posted") {
  resetPostVoucher();
  const apiURL = "{{ route('transport.invoice.posting.get') }}";
  $.ajax({
    url: apiURL + "?book_id=" + $("#series_id_input").val() + "&document_id="
      + "{{ isset($order) ? $order->id : '' }}" + "&type=" + (type == "not_posted" ? "get" : "view"),
    type: "GET",
    dataType: "json",
    success: function(data) {
      if (!data.data.status) {
        Swal.fire({
          title: "Error!",
          text: data.data.message,
          icon: "error",
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
               <td class="fw-bolder text-dark">${
            voucherDetail.ledger_group_code ? voucherDetail.ledger_group_code : ""
          }</td>
               <td>${voucherDetail.ledger_code ? voucherDetail.ledger_code : ""}</td>
               <td>${voucherDetail.ledger_name ? voucherDetail.ledger_name : ""}</td>
               <td class="text-end">${
            voucherDetail.debit_amount > 0 ? parseFloat(voucherDetail.debit_amount).toFixed(2) : ""
          }</td>
               <td class="text-end">${
            voucherDetail.credit_amount > 0 ? parseFloat(voucherDetail.credit_amount).toFixed(2) : ""
          }</td>
               </tr>
               `;
        });
      });
      voucherEntriesHTML += `
       <tr>
           <td colspan="4" class="fw-bolder text-dark text-end">Total</td>
           <td class="fw-bolder text-dark text-end">${voucherEntries.total_debit.toFixed(2)}</td>
           <td class="fw-bolder text-dark text-end">${voucherEntries.total_credit.toFixed(2)}</td>
       </tr>
       `;
      document.getElementById("posting-table").innerHTML = voucherEntriesHTML;
      document.getElementById("voucher_doc_no").value = voucherEntries.document_number;
      document.getElementById("voucher_date").value = moment(voucherEntries.document_date).format(
        "D/M/Y",
      );
      document.getElementById("voucher_book_code").value = voucherEntries.book_code;
      document.getElementById("voucher_currency").value = voucherEntries.currency_code;
      if (type === "posted") {
        document.getElementById("posting_button").style.display = "none";
      } else {
        document.getElementById("posting_button").style.removeProperty("display");
      }
      $("#postvoucher").modal("show");
    },
  });
}

function postVoucher(element) {
  const bookId = "{{ isset($order) ? $order->book_id : '' }}";
  const documentId = "{{ isset($order) ? $order->id : '' }}";
  const postingApiUrl = "{{ route('transport.invoice.post') }}";
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
            title: "Success!",
            text: response.message,
            icon: "success",
          });
          location.reload();
        } else {
          Swal.fire({
            title: "Error!",
            text: response.message,
            icon: "error",
          });
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
        let errorReponse = jqXHR.responseJSON;
        if (errorReponse?.data?.message) {
          Swal.fire({
            title: "Error!",
            text: errorReponse?.data?.message,
            icon: "error",
          });
        } else {
          Swal.fire({
            title: "Error!",
            text: "Some internal error occured",
            icon: "error",
          });
        }
      },
    });
  }
}

function initializeAutocompleteTed(selector, idSelector, type, percentageVal) {
  $("#" + selector).autocomplete({
    source: function(request, response) {
      var selectedDiscountIds = [];
      if (type === "sales_module_discount") {
        if (selector == "new_order_discount_name") {
          var salesDiscountIdElement = document.getElementsByClassName(
            "order_discount_master_id_hidden",
          );
          for (let index = 0; index < salesDiscountIdElement.length; index++) {
            selectedDiscountIds.push(salesDiscountIdElement[index].value);
          }
        } else if (selector == "new_discount_name") {
          var itemIndex = document.getElementById("discount_main_table").getAttribute(
            "item-row-index",
          );
          var salesDiscountIdElement = document.getElementsByClassName(
            "discount_master_ids_hidden_" + itemIndex,
          );
          for (let index = 0; index < salesDiscountIdElement.length; index++) {
            selectedDiscountIds.push(salesDiscountIdElement[index].value);
          }
        }
      }
      $.ajax({
        url: "/search",
        method: "GET",
        dataType: "json",
        data: {
          q: request.term,
          type: type,
          selected_discount_ids: selectedDiscountIds,
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
          console.error("Error fetching customer data:", xhr.responseText);
        },
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
      $("#" + percentageVal).val(itemPercentage).trigger("input");
      return false;
    },
    change: function(event, ui) {
      if (!ui.item) {
        $(this).val("");
        $("#" + idSelector).val("");
      }
    },
  }).focus(function() {
    if (this.value === "") {
      $(this).autocomplete("search", "");
    }
  });
}

function resetDiscountOrExpense(element, percentageFieldId) {
  if (!element.value) {
    $("#" + percentageFieldId).val("").trigger("input");
  }
}

function onOrderDiscountModalOpen() {
  initializeAutocompleteTed(
    "new_order_discount_name",
    "new_order_discount_id",
    "sales_module_discount",
    "new_order_discount_percentage",
  );
}

function onOrderExpenseModalOpen() {
  initializeAutocompleteTed(
    "order_expense_name",
    "order_expense_id",
    "sales_module_expense",
    "order_expense_percentage",
  );
}

function checkOrRecheckAllItems(element) {
  const allRowsCheck = document.getElementsByClassName("item_row_checks");
  const checkedStatus = element.checked;
  for (let index = 0; index < allRowsCheck.length; index++) {
    allRowsCheck[index].checked = checkedStatus;
  }
}

function implementSeriesChange(val) {
  // COMMON CHANGES
  document.getElementById("type_hidden_input").value = val;
  // const generalInfoTab = document.getElementById('general_information_tab');
  const itemDetailTd = document.getElementById("item_details_td");
  const invoiceSummaryTd = document.getElementById("invoice_summary_td");
  const breadCrumbHeading = document.getElementById("breadcrumb-document-heading");
  var leaseHiddenFields = document.getElementsByClassName("lease-hidden");

  if (val === "si") { // SALES INVOICE
    // generalInfoTab.style.display = 'none';
    // itemDetailTd.setAttribute('colspan', 8);
    // invoiceSummaryTd.style.removeProperty('display');
    breadCrumbHeading.textContent = "Sales Invoice";
    for (let index = 0; index < leaseHiddenFields.length; index++) {
      leaseHiddenFields[index].style.display = "";
    }
  } else if (val === "dnote") { // DELIVERY NOTE
    // generalInfoTab.style.removeProperty('display');
    // itemDetailTd.setAttribute('colspan', 8);
    // invoiceSummaryTd.style.display = 'none';
    breadCrumbHeading.textContent = "Delivery Note";
    for (let index = 0; index < leaseHiddenFields.length; index++) {
      leaseHiddenFields[index].style.display = "";
    }
  } else if (val === "lease-invoice") { // LEASE INVOICE
    // generalInfoTab.style.display = 'none';
    // itemDetailTd.setAttribute('colspan', 8);
    // invoiceSummaryTd.style.removeProperty('display');
    breadCrumbHeading.textContent = "Lease Invoice";
    for (let index = 0; index < leaseHiddenFields.length; index++) {
      leaseHiddenFields[index].style.display = "none";
    }
  } // DEFAULT BEHAVIOUR
  else {
    // generalInfoTab.style.display = "none";
    itemDetailTd.setAttribute("colspan", 8);
    // invoiceSummaryTd.style.removeProperty('display');
    breadCrumbHeading.textContent = "Invoice";
    for (let index = 0; index < leaseHiddenFields.length; index++) {
      leaseHiddenFields[index].style.display = "";
    }
  }

  // onHeaderLocationChange(document.getElementById('store_id_input'));
}

function getBundles(itemIndex, soItemId, dnItemId = null, disabled = false) {
  const docElement = document.getElementById("item_bundles_" + itemIndex);
  const itemBundleTableId = document.getElementById("bundles_info_table");
  itemBundleTableId.setAttribute("current-item-index", itemIndex);
  itemBundleTableId.innerHTML = ``;
  let newBundleHTML = ``;
  let initialOpen = true;
  let currentBundleCheckedArray = [];
  if (docElement.getAttribute("checked-bundle")) {
    initialOpen = false;
    currentBundleCheckedArray = JSON.parse(decodeURIComponent(docElement.getAttribute("checked-bundle")));
  }
  if (dnItemId) {
    initialOpen = false;
  }
  let selectedBundleIds = [];
  let fixedBundleIds = [];
  currentBundleCheckedArray.forEach((checkedElement) => {
    if (checkedElement.checked) {
      selectedBundleIds.push(checkedElement.bundle_id);
    }
    fixedBundleIds.push(checkedElement.bundle_id);
  });
  let selectedQty = 0;
  $.ajax({
    url: "{{ route('sale.invoice.get.pslip.bundles.so') }}",
    method: "GET",
    dataType: "json",
    data: {
      so_item_id: soItemId,
      selected_bundles: selectedBundleIds,
      initial_open: initialOpen,
      dn_item_id: dnItemId,
      bundle_ids: disabled ? fixedBundleIds : [],
    },
    success: function(data) {
      if (data.data.bundles && data.data.bundles.length > 0) {
        let newBundleCheckedArray = [];
        data.data.bundles.forEach((dataBundle, dataBundleIndex) => {
          if (dataBundle.checked) {
            selectedQty += parseFloat(dataBundle.qty);
          }
          newBundleHTML += `
                   <tr>
                   <td>
                   <div class="form-check form-check-primary custom-checkbox">
                       <input ${
            disabled ? "disabled" : ""
          } type="checkbox" class="form-check-input item_bundles_check" id="item_row_check_${dataBundleIndex}" ${
            dataBundle.checked ? "checked" : ""
          } oninput = "updateBundleCheck(this, ${itemIndex}, ${dataBundleIndex}, ${dataBundle.id}, ${dataBundle.qty})">
                           <label class="form-check-label" for="item_row_bom_${dataBundleIndex}"></label>
                       </div>
                   </td>
                   <td>${dataBundle.bundle_no}</td>
                   <td class = "numeric-alignment">${dataBundle.qty}</td>
                   </tr>
                   `;
          newBundleCheckedArray.push({
            bundle_id: dataBundle.id,
            checked: dataBundle.checked,
            qty: dataBundle.qty,
          });
        });
        itemBundleTableId.innerHTML = newBundleHTML + `
               <tr>
               <td colspan = "2" class = "numeric-alignment"><strong>Total</strong></td>
               <td class = "numeric-alignment" id = "current_selected_bundle_qty_${itemIndex}">${selectedQty}</td>
               </tr>
               `;
        if (!docElement.getAttribute("checked-bundle")) {
          docElement.setAttribute(
            "checked-bundle",
            encodeURIComponent(JSON.stringify(
              newBundleCheckedArray,
            )),
          );
        }
        $("#BundleInfo").modal("show");
      } else {
        Swal.fire({
          title: "Not Found",
          text: "Bundles not available",
          icon: "info",
        });
      }
    },
    error: function(xhr) {
      console.error("Error fetching customer data:", xhr.responseText);
      Swal.fire({
        title: "Error!",
        text: "Some internal error occured",
        icon: "error",
      });
    },
  });
}

function getPackingLists(itemIndex, packingItemId) {
  const itemBundleTableId = document.getElementById("bundles_info_table");
  itemBundleTableId.setAttribute("current-item-index", itemIndex);
  itemBundleTableId.innerHTML = ``;
  let newBundleHTML = ``;
  let initialOpen = true;
  let currentBundleCheckedArray = [];
  if (docElement.getAttribute("checked-bundle")) {
    initialOpen = false;
    currentBundleCheckedArray = JSON.parse(decodeURIComponent(docElement.getAttribute("checked-bundle")));
  }
  if (dnItemId) {
    initialOpen = false;
  }
  let selectedBundleIds = [];
  let fixedBundleIds = [];
  currentBundleCheckedArray.forEach((checkedElement) => {
    if (checkedElement.checked) {
      selectedBundleIds.push(checkedElement.bundle_id);
    }
    fixedBundleIds.push(checkedElement.bundle_id);
  });
  let selectedQty = 0;
  $.ajax({
    url: "{{ route('sale.invoice.get.pslip.bundles.so') }}",
    method: "GET",
    dataType: "json",
    data: {
      so_item_id: soItemId,
      selected_bundles: selectedBundleIds,
      initial_open: initialOpen,
      dn_item_id: dnItemId,
      bundle_ids: disabled ? fixedBundleIds : [],
    },
    success: function(data) {
      if (data.data.bundles && data.data.bundles.length > 0) {
        let newBundleCheckedArray = [];
        data.data.bundles.forEach((dataBundle, dataBundleIndex) => {
          if (dataBundle.checked) {
            selectedQty += parseFloat(dataBundle.qty);
          }
          newBundleHTML += `
                   <tr>
                   <td>
                   <div class="form-check form-check-primary custom-checkbox">
                       <input ${
            disabled ? "disabled" : ""
          } type="checkbox" class="form-check-input item_bundles_check" id="item_row_check_${dataBundleIndex}" ${
            dataBundle.checked ? "checked" : ""
          } oninput = "updateBundleCheck(this, ${itemIndex}, ${dataBundleIndex}, ${dataBundle.id}, ${dataBundle.qty})">
                           <label class="form-check-label" for="item_row_bom_${dataBundleIndex}"></label>
                       </div>
                   </td>
                   <td>${dataBundle.bundle_no}</td>
                   <td class = "numeric-alignment">${dataBundle.qty}</td>
                   </tr>
                   `;
          newBundleCheckedArray.push({
            bundle_id: dataBundle.id,
            checked: dataBundle.checked,
            qty: dataBundle.qty,
          });
        });
        itemBundleTableId.innerHTML = newBundleHTML + `
               <tr>
               <td colspan = "2" class = "numeric-alignment"><strong>Total</strong></td>
               <td class = "numeric-alignment" id = "current_selected_bundle_qty_${itemIndex}">${selectedQty}</td>
               </tr>
               `;
        if (!docElement.getAttribute("checked-bundle")) {
          docElement.setAttribute(
            "checked-bundle",
            encodeURIComponent(JSON.stringify(
              newBundleCheckedArray,
            )),
          );
        }
        $("#BundleInfo").modal("show");
      }
    },
    error: function(xhr) {
      console.error("Error fetching customer data:", xhr.responseText);
      Swal.fire({
        title: "Error!",
        text: "Some internal error occured",
        icon: "error",
      });
    },
  });
}

function updateBundleCheck(element, itemIndex, bundleIndex, bundleId, bundleQty) {
  const docElement = document.getElementById("item_bundles_" + itemIndex);
  let checkedBundleArray = [];
  if (docElement.getAttribute("checked-bundle")) {
    checkedBundleArray = JSON.parse(decodeURIComponent(docElement.getAttribute("checked-bundle")));
  }
  if (checkedBundleArray.length > bundleIndex && checkedBundleArray[bundleIndex] !== undefined) {
    checkedBundleArray[bundleIndex]["checked"] = element.checked;
  } else {
    checkedBundleArray.push({
      bundle_id: bundleId,
      qty: bundleQty,
      checked: element.checked,
    });
  }
  let currentItemQty = 0;
  checkedBundleArray.forEach((checkedBundle) => {
    if (checkedBundle.checked) {
      currentItemQty += checkedBundle.qty;
    }
  });
  $("#current_selected_bundle_qty_" + itemIndex).text(parseFloat(currentItemQty));
  $("#item_qty_" + itemIndex).val(currentItemQty).trigger("input");
  docElement.setAttribute("checked-bundle", encodeURIComponent(JSON.stringify(checkedBundleArray)));
}

function onBundleSubmit() {
  const tableElement = document.getElementById("bundles_info_table");
  let itemIndex = 0;
  if (tableElement.getAttribute("current-item-index")) {
    itemIndex = tableElement.getAttribute("current-item-index");
  }
  const currentDocElement = document.getElementById("item_bundles_" + itemIndex);
  let bundlesArray = JSON.parse(decodeURIComponent(currentDocElement.getAttribute("checked-bundle")));
  let currentSelectedQty = 0;
  bundlesArray.forEach((bundleElement) => {
    if (bundleElement.checked) {
      currentSelectedQty += bundleElement.qty;
    }
  });
  if (currentSelectedQty <= 0) {
    Swal.fire({
      title: "Error!",
      text: "Please select at lease one bundle",
      icon: "error",
    });
    return;
  }
  closeModal("BundleInfo");
}

function openFreePslipsModal() {
  $.ajax({
    url: "{{ route('sale.invoice.get.free.pslips') }}",
    method: "GET",
    dataType: "json",
    data: {},
    success: function(data) {
      console.log(data, "DATA");
    },
    error: function(xhr) {
      console.error("Error fetching customer data:", xhr.responseText);
      Swal.fire({
        title: "Error!",
        text: "Some internal error occured",
        icon: "error",
      });
    },
  });
}

function generateEInvoice(id) {
  document.getElementById("erp-overlay-loader").style.display = "flex";
  $.ajax({
    url: "{{ route('sale.invoice.generate.einvoice') }}",
    method: "POST",
    dataType: "json",
    data: {
      id: id,
      transporter_mode: $("#transporter_mode_input").val(),
      transporter_name: $("#transporter_name_input").val(),
      vehicle_no: $("#vehicle_no_input").val(),
    },
    success: function(data) {
      document.getElementById("erp-overlay-loader").style.display = "none";
      Swal.fire({
        title: "Success!",
        text: data.message,
        icon: "success",
      });
      setTimeout(() => {
        window.location.reload();
      }, 1500);
    },
    error: function(xhr) {
      document.getElementById("erp-overlay-loader").style.display = "flex";
      console.error("Error fetching customer data:", xhr);
      Swal.fire({
        title: "Warning!",
        text: xhr?.responseJSON?.message,
        icon: xhr?.responseJSON?.redirect ? "warning" : "error",
      }).then((result) => {
        if (xhr?.responseJSON?.redirect) {
          window.location.reload();
        }
      });
    },
  });
}

let currentSubStoreArray = [];
let lastSelectedSubStore = null;

function setLocationStoreOnItem(itemId, itemIndex) {
  const locId = $("#store_id_input").val();
  $.ajax({
    url: "{{ route('subStore.get.from.stores') }}",
    method: "GET",
    dataType: "json",
    data: {
      store_id: locId,
      item_id: itemId,
    },
    success: function(data) {
      if (data.status === 200) {
        currentSubStoreArray = data.data;
        renderStoreOnItemChange(itemIndex);
      } else {
        currentSubStoreArray = [];
        Swal.fire({
          title: "Error!",
          text: data.message,
          icon: "error",
        });
      }
    },
    error: function(xhr) {
      console.error("Error fetching customer data:", xhr);
      currentSubStoreArray = [];
      Swal.fire({
        title: "Error!",
        text: xhr?.responseJSON?.message,
        icon: "error",
      });
    },
  });
}

function onChangeCustomer(selectElementId, reset = false) {
  const selectedOption = document.getElementById(selectElementId);
  const paymentTermsDropdown = document.getElementById("payment_terms_dropdown");
  const currencyDropdown = document.getElementById("currency_dropdown");
  if (reset && !selectedOption.value) {
    selectedOption.setAttribute("currency_id", "");
    selectedOption.setAttribute("currency", "");
    selectedOption.setAttribute("currency_code", "");

    selectedOption.setAttribute("payment_terms_id", "");
    selectedOption.setAttribute("payment_terms", "");
    selectedOption.setAttribute("payment_terms_code", "");

    document.getElementById("customer_id_input").value = "";
    document.getElementById("customer_code_input_hidden").value = "";
  }
  // Set Currency
  const currencyId = selectedOption.getAttribute("currency_id");
  const currency = selectedOption.getAttribute("currency");
  const currencyCode = selectedOption.getAttribute("currency_code");
  if (currencyId && currency) {
    const newCurrencyValues = `
       <option value = '${currencyId}' > ${currency} </option>
   `;
    currencyDropdown.innerHTML = newCurrencyValues;
    $("#currency_code_input").val(currencyCode);
  } else {
    currencyDropdown.innerHTML = "";
    $("#currency_code_input").val("");
  }
  // Set Payment Terms
  const paymentTermsId = selectedOption.getAttribute("payment_terms_id");
  const paymentTerms = selectedOption.getAttribute("payment_terms");
  const paymentTermsCode = selectedOption.getAttribute("payment_terms_code");
  if (paymentTermsId && paymentTerms) {
    const newPaymentTermsValues = `
       <option value = '${paymentTermsId}' > ${paymentTerms} </option>
   `;
    paymentTermsDropdown.innerHTML = newPaymentTermsValues;
    $("#payment_terms_code_input").val(paymentTermsCode);
  } else {
    paymentTermsDropdown.innerHTML = "";
    $("#payment_terms_code_input").val("");
  }
  // Set Location address
  const locationElement = document.getElementById("store_id_input");
  if (locationElement) {
    const displayAddress = locationElement.options[locationElement.selectedIndex].getAttribute(
      "display-address",
    );
    $("#current_pickup_address").text(displayAddress);
  }
  // Get Addresses (Billing + Shipping)
  changeDropdownOptions(
    document.getElementById("customer_id_input"),
    ["billing_address_dropdown", "shipping_address_dropdown"],
    ["billing_addresses", "shipping_addresses"],
    "/customer/addresses/",
    "vendor_dependent",
  );
}

function onHeaderLocationChange(element) {
  const storeId = element.value;
  let currentDocType = $("#service_id_input").val();
  currentSubStoreArray = [];
  let payloadData = {
    store_id: storeId,
  };
  let subStoreElement = document.getElementById("sub_store_id_input");
  if (subStoreElement) {
    subStoreElement.innerHTML = "";
  }
  $.ajax({
    url: "{{ route('subStore.get.from.stores') }}",
    method: "GET",
    dataType: "json",
    data: payloadData,
    success: function(data) {
      if (data.status === 200) {
        currentSubStoreArray = data.data;
        let newHTML = ``;
        currentSubStoreArray.forEach(element => {
          newHTML += `<option value = "${element.id}"> ${element.name} </option>`;
        });
        if (subStoreElement) {
          subStoreElement.innerHTML = newHTML;
        }
      } else {
        currentSubStoreArray = [];
        if (subStoreElement) {
          subStoreElement.innerHTML = "";
        }
        Swal.fire({
          title: "Error!",
          text: data.message,
          icon: "error",
        });
      }
    },
    error: function(xhr) {
      console.error("Error fetching customer data:", xhr);
      currentSubStoreArray = [];
      if (subStoreElement) {
        subStoreElement.innerHTML = ``;
      }
      Swal.fire({
        title: "Error!",
        text: xhr?.responseJSON?.message,
        icon: "error",
      });
    },
  });
}

function generateEwayBill() {
  document.getElementById("erp-overlay-loader").style.display = "flex";
  let orderId = "{{ isset($order) ? $order->id : '' }}";
  $.ajax({
    url: "{{ route('sale.invoice.generate.ewayBill') }}",
    method: "POST",
    dataType: "json",
    data: {
      id: orderId,
      transporter_mode: $("#transporter_mode_input").val(),
      transporter_name: $("#transporter_name_input").val(),
      vehicle_no: $("#vehicle_no_input").val(),
    },
    success: function(data) {
      document.getElementById("erp-overlay-loader").style.display = "none";
      if (data.status == "error") {
        Swal.fire({
          title: "Error!",
          text: data.message,
          icon: "error",
        });
        return false;
      } else {
        Swal.fire({
          title: "Success!",
          text: data.message,
          icon: "success",
        });
        location.reload();
      }
    },
    error: function(xhr) {
      document.getElementById("erp-overlay-loader").style.display = "none";
      console.error("Error fetching customer data:", xhr);
      Swal.fire({
        title: "Error!",
        text: xhr?.responseJSON?.message,
        icon: "error",
      });
    },
  });
}

function renderStoreOnItemChange(index) {
  let currentSubLocArray = [];
  let targetDocument = null;
  currentSubLocArray = currentSubStoreArray;
  targetDocument = document.getElementById("item_sub_store_" + index);
  let newInnerHTML = ``;
  currentSubLocArray.forEach(subLoc => {
    newInnerHTML += `<option value = "${subLoc.id}">${subLoc.name}</option>`;
  });
  targetDocument.innerHTML = newInnerHTML;
}

function setPackets(element) {
  let targetHTML = document.getElementById("packing_info_table");
  let qty = element.getAttribute("qty");
  let packet = element.getAttribute("packet");
  targetHTML.innerHTML = `
   <tr>
   <td>${packet}</td>
   <td class = "numeric-alignment">${qty}</td>
   </tr>
   `;
  $("#PacketInfo").modal("show");
}

function checkStockData(itemRowId) {
  let itemAttributes = JSON.parse(
    document.getElementById(`items_dropdown_${itemRowId}`).getAttribute(
      "attribute-array",
    ),
  );
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
    url: "{{ route('sale.transporterInvoice.details') }}",
    method: "GET",
    dataType: "json",
    data: {
      quantity: document.getElementById("item_qty_" + itemRowId).value,
      item_id: document.getElementById("items_dropdown_" + itemRowId + "_value").value,
      uom_id: document.getElementById("uom_dropdown_" + itemRowId).value,
      selectedAttr: selectedItemAttr,
      store_id: $("#store_id_input").val(),
      sub_store_id: $("#sub_store_id_input").val(),
    },
    success: function(data) {
      var inputQtyBox = document.getElementById("item_qty_" + itemRowId);
      var actualQty = inputQtyBox.value;
      inputQtyBox.setAttribute("max-stock", data.stocks.confirmedStockAltUom);
      if (inputQtyBox.getAttribute("max-stock")) {
        var maxStock = parseFloat(
          inputQtyBox.getAttribute("max-stock")
            ? inputQtyBox
              .getAttribute("max-stock")
            : 0,
        );
        if (maxStock <= 0) {
          inputQtyBox.value = 0;
          inputQtyBox.readOnly = true;
        } else {
          if (actualQty > maxStock) {
            inputQtyBox.value = maxStock;
            inputQtyBox.readOnly = false;
          } else {
            inputQtyBox.readOnly = false;
          }
        }
      }
    },
    error: function(xhr) {
      console.error("Error fetching customer data:", xhr.responseText);
    },
  });
}

function checkAllSO(element) {
  const selectableElements = document.getElementsByClassName("pull_checkbox");
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

function checkAllPlist(element) {
  const selectableElements = document.getElementsByClassName("pull_checkbox");
  for (let index = 0; index < selectableElements.length; index++) {
    if (!selectableElements[index].disabled) {
      selectableElements[index].checked = element.checked;
    }
  }
}

function checkAllIn(element) {
  const selectableElements = document.getElementsByClassName("pull_checkbox");
  for (let index = 0; index < selectableElements.length; index++) {
    if (!selectableElements[index].disabled) {
      selectableElements[index].checked = element.checked;
    }
  }
}

function checkAllLandLease(element) {
  const selectableElements = document.getElementsByClassName("pull_checkbox");
  for (let index = 0; index < selectableElements.length; index++) {
    if (!selectableElements[index].disabled) {
      selectableElements[index].checked = element.checked;
    }
  }
}

function clearFilters(type = "so") {
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
    `item_id_${type}_val`,
  ];

  fields.forEach(id => {
    const el = document.getElementById(id);
    if (el) {
      if ("value" in el) {
        el.value = "";
      } else {
        el.textContent = "";
      }
    }
  });

  selectedValues = {};
  getOrders(type);
}

document.addEventListener("DOMContentLoaded", function() {
  onHeaderLocationChange(document.getElementById("store_id_input"));
});






  

    </script>
@endsection
@endsection