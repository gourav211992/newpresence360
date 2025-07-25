@extends('layouts.app')
@section('content')
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                   <div class="content-header-left col-md-6 mb-2">
                      <div class="row breadcrumbs-top">
                         <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Material Receipt</h2>
                            <div class="breadcrumb-wrapper">
                               <ol class="breadcrumb">
                                  <li class="breadcrumb-item"><a href="#">Home</a>
                                  </li>  
                                  <li class="breadcrumb-item active">View</li>
                              </ol>
                          </div>
                      </div>
                  </div>
                </div>
                <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right"> 
                                <button type="button" onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
                            </div>
                        </div>
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
                    <div class="row">
                    <div class="col-md-6">
                        <div class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                            <div>
                                <h4 class="card-title text-theme">Basic Information</h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 text-sm-end">
                        <span class="badge rounded-pill badge-light-secondary forminnerstatus">
                            Status : <span class="{{$docStatusClass}}">{{ucfirst($mrn->document_status)}}</span>
                        </span>
                    </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8"> 
                            <div class="row align-items-center mb-1">
                                <div class="col-md-3"> 
                                    <label class="form-label">Series <span class="text-danger">*</span></label>  
                                </div>  
                                <div class="col-md-5">  
                                    <input type="hidden" name="series_id" class="form-control" id="series_id" value="{{$mrn->series_id}}" readonly> 
                                    <input readonly type="text" class="form-control" value="{{$mrn->book->book_code}}" id="book_code">
                                </div>
                            </div>
                            <div class="row align-items-center mb-1">
                                <div class="col-md-3"> 
                                    <label class="form-label">GRN No <span class="text-danger">*</span></label>  
                                </div>  
                                <div class="col-md-5"> 
                                    <input type="text" class="form-control" name="document_number" value="{{@$mrn->document_number}}" id="document_number">
                                </div> 
                            </div>  
                            <div class="row align-items-center mb-1">
                                <div class="col-md-3"> 
                                    <label class="form-label">GRN Date <span class="text-danger">*</span></label>  
                                </div>  
                                <div class="col-md-5"> 
                                    <input type="date" class="form-control" readonly value="{{date('Y-m-d')}}" >
                                </div> 
                            </div>  
                            <div class="row align-items-center mb-1">
                                <div class="col-md-3"> 
                                    <label class="form-label">Reference No </label>  
                                </div>  
                                <div class="col-md-5"> 
                                    <input type="text" name="reference_number" value="{{@$mrn->reference_number}}" class="form-control">
                                </div> 
                            </div>
                            <!-- <div class="row align-items-center mb-1"> 
                                <div class="col-md-3"> 
                                    <label class="form-label">Outstanding PO </label>  
                                </div>
                                <div class="col-md-5 action-button"> 
                                    <button data-bs-toggle="modal" type="button" data-bs-target="#rescdule"
                                        class="btn btn-outline-primary btn-sm" id="outstanding">
                                        <i data-feather="plus-square"></i> Outstanding PO
                                    </button>
                                    <div id="select_po"></div>
                                </div>
                            </div> -->
                        </div>
                        @if(isset($approvalHistory) && count($approvalHistory) > 0)
                            <div class="col-md-4"> 
                                <div class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
                                    <h5 class="mb-2 text-dark border-bottom pb-50 d-flex align-items-center justify-content-between">
                                        <strong>
                                            <a href="{{ route('material-receipt.logs', $mrn->id) }}">
                                                <i data-feather="arrow-right-circle"></i> Approval History
                                            </a>    
                                        </strong>
                                        <strong class="badge rounded-pill badge-light-secondary amendmentselect">Rev. No. 
                                            <form action="" method="GET">
                                                <select class="form-select" name="revision_number" onchange="this.form.submit()">
                                                    @foreach($mrnRevisionNumbers as $revisionNumber)
                                                        <option value="{{$revisionNumber->revision_number}}" 
                                                        @if(isset($currentRevisionNumber) && $currentRevisionNumber == $revisionNumber->revision_number) 
                                                            selected 
                                                        @endif>
                                                            {{$revisionNumber->revision_number}}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </form>
                                        </strong>
                                    </h5>
                                    <ul class="timeline ms-50 newdashtimline ">
                                            @foreach($approvalHistory as $approvalHist)
                                            <li class="timeline-item">
                                            <span class="timeline-point timeline-point-indicator"></span>
                                            <div class="timeline-event">
                                                <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                    <h6>{{ucfirst($approvalHist->user->name)}}</h6> 
                                                    @if($approvalHist->approval_type == 'approve')
                                                    <span class="badge rounded-pill badge-light-success">{{ucfirst($approvalHist->approval_type)}}</span>
                                                    @elseif($approvalHist->approval_type == 'submit')
                                                    <span class="badge rounded-pill badge-light-primary">{{ucfirst($approvalHist->approval_type)}}</span>
                                                    @elseif($approvalHist->approval_type == 'reject')
                                                    <span class="badge rounded-pill badge-light-danger">{{ucfirst($approvalHist->approval_type)}}</span>
                                                    @endif
                                                </div>
                                                <h5>{{ \Carbon\Carbon::parse($approvalHist->created_at)->format('d-m-Y H:i A') }}</h5>
                                                <p>{!! $approvalHist->remarks !!}</p>
                                                {{-- <p><a href="#"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg></a> Description will come here </p>  --}}
                                            </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif 
                </div> 
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="card quation-card">
                    <div class="card-header newheader">
                        <div>
                            <h4 class="card-title">Vendor Details</h4> 
                        </div>
                    </div>
                    <div class="card-body"> 
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">Vendor <span class="text-danger">*</span></label> 
                                    <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" id="vendor_name" readonly name="vendor_name" value="{{@$mrn->vendor->company_name}}" />
                                    <input type="hidden" value="{{@$mrn->vendor_id}}" id="vendor_id" name="vendor_id" />
                                    <input type="hidden" value="{{@$mrn->vendor_code}}" id="vendor_code" name="vendor_code" />
                                    <input type="hidden" value="{{@$mrn->shipping_address}}" id="shipping_id" name="shipping_id" />
                                    <input type="hidden" id="billing_id" value="{{@$mrn->billing_address}}" name="billing_id" />
                                </div>
                            </div> 
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">Currency <span class="text-danger">*</span></label>
                                    <select class="form-select" name="currency_id">
                                        <option id="{{@$mrn->currency_id}}">{{@$mrn->currency->name}}</option>
                                    </select> 
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">Payment Terms <span class="text-danger">*</span></label>
                                    <select class="form-select" name="payment_term_id">
                                        <option value="{{@$mrn->payment_term_id}}">{{@$mrn->paymentTerm->name}}</option>
                                    </select>  
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="customer-billing-section">
                                    <p>Shipping Details</p>
                                    <div class="bilnbody">
                                        <div class="genertedvariables genertedvariablesnone">
                                            <label class="form-label w-100" style="display:none;">
                                                Select Shipping Address <span class="text-danger">*</span> 
                                                <a href="javascript:;" class="float-end font-small-2 editAddressBtn" data-type="shipping">
                                                    <i data-feather='edit-3'></i> Edit
                                                </a>
                                            </label>
                                            <div class="mrnaddedd-prim shipping_detail">
                                                {{@$mrn->shipping_address}}
                                            </div>   
                                        </div> 
                                    </div>

                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="customer-billing-section h-100">
                                    <p>Billing Details</p>
                                    <div class="bilnbody">  
                                        <div class="genertedvariables genertedvariablesnone">
                                            <label class="form-label w-100" style="display:none">
                                                Select Billing Address <span class="text-danger">*</span> 
                                                <a href="javascript:;" class="float-end font-small-2 editAddressBtn" data-type="billing">
                                                    <i data-feather='edit-3'></i> Edit
                                                </a>
                                            </label>
                                            <div class="mrnaddedd-prim billing_detail">
                                                {{@$mrn->billing_address}}
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
        <div class="row">
            <div class="col-md-12">
                <div class="card quation-card">
                    <div class="card-header newheader">
                        <div>
                            <h4 class="card-title">General Information</h4>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">
                                        Gate Entry No. <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="gate_entry_no"
                                    class="form-control bg-white" value="{{@$mrn->gate_entry_no}}"
                                    placeholder="Enter Gate Entry no">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">
                                        Gate Entry Date <span class="text-danger">*</span>
                                    </label> 
                                    <input type="date" name="gate_entry_date" value="{{date('Y-m-d', strtotime($mrn->gate_entry_date))}}"
                                    class="form-control bg-white gate-entry" id="datepicker2"
                                    placeholder="Enter Gate Entry Date">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">
                                        E-Way Bill No. <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="eway_bill_no" value="{{@$mrn->eway_bill_no}}"
                                    class="form-control bg-white"
                                    placeholder="Enter Eway Bill No.">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">
                                        Consignment No. <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="consignment_no" value="{{@$mrn->consignment_no}}"
                                    class="form-control bg-white"
                                    placeholder="Enter Consignment No.">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">
                                        Supplier Invoice No.
                                    </label> 
                                    <input type="text" name="supplier_invoice_no" value="{{@$mrn->supplier_invoice_no}}"
                                    class="form-control bg-white"
                                    placeholder="Enter Supplier Invoice No.">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">
                                        Supplier Invoice Date <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" name="supplier_invoice_date" value="{{date('Y-m-d', strtotime($mrn->supplier_invoice_date))}}"
                                    class="form-control bg-white gate-entry" id="datepicker3"
                                    placeholder="Enter Supplier Invoice Date">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">
                                        Transporter Name
                                    </label> 
                                    <input type="text" name="transporter_name" value="{{@$mrn->transporter_name}}"
                                    class="form-control bg-white"
                                    placeholder="Enter Transporter Name">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">
                                        Vehicle No. <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" name="vehicle_no" value="{{@$mrn->vehicle_no}}"
                                    class="form-control bg-white"
                                    placeholder="Enter Vehicle No.">
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
                        <h4 class="card-title text-theme">PO Item Wise Detail</h4>
                        <!-- <p class="card-text">Fill the details</p> -->
                    </div>
                </div>
                <div class="col-md-6 text-sm-end">
                    <!-- <a href="javascript:;" id="deleteBtn" class="btn btn-sm btn-outline-danger me-50">
                        <i data-feather="x-circle"></i> Delete</a>
                        <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary">
                            <i data-feather="plus"></i> Add New Item</a> -->
                        </div>
                    </div> 
                </div>
                <div class="row"> 
                   <div class="col-md-12">
                       <div class="table-responsive pomrnheadtffotsticky">
                           <table id="itemTable" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"> 
                                <thead>
                                    <tr>
                                        <th class="customernewsection-form">
                                            <div class="form-check form-check-primary custom-checkbox">
                                                <input type="checkbox" class="form-check-input" id="Email">
                                                <label class="form-check-label" for="Email"></label>
                                            </div> 
                                        </th>
                                        <th width="200px">Item</th>
                                        <th>Attributes</th>
                                        <th>UOM</th>
                                        <th>Ordered Qty</th>
                                        <th>Recpt Qty</th>
                                        <th>Acpt. Qty</th>
                                        <th>Rej. Qty</th>
                                        <th>Rate</th>
                                        <th>Value</th> 
                                        <th>Discount</th>
                                        <th>Total</th> 
                                        <th width="100px">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="mrntableselectexcel">
                                @foreach($mrn->items as $key => $item)
                                    @php
                                        $rowCount = $key + 1;
                                    @endphp
                                    <tr id="row_{{$rowCount}}" data-index="{{$rowCount}}" @if($rowCount < 2 ) class="trselected" @endif>
                                        <input type="hidden" name="components[{{$rowCount}}][mrn_header_id]" value="{{@@$item->mrn_header_id}}">
                                        <input type="hidden" name="components[{{$rowCount}}][mrn_detail_id]" value="{{@@$item->id}}">
                                        <td class="customernewsection-form">
                                            <div class="form-check form-check-primary custom-checkbox">
                                                <input type="checkbox" class="form-check-input" id="Email_{{$rowCount}}" data-id="{{@@$item->id}}" value="{{$rowCount}}">
                                                <label class="form-check-label" for="Email_{{$rowCount}}"></label>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" name="component_item_name[{{$rowCount}}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code" value="{{@$item->item_code}}" />
                                            <input type="hidden" name="components[{{$rowCount}}][item_id]" value="{{@$item->item_id}}" />
                                            <input type="hidden" name="components[{{$rowCount}}][item_code]" value="{{@$item->item_code}}" />
                                            <input type="hidden" name="components[{{$rowCount}}][item_name]" value="{{@$item->item->name}}" />
                                            <input type="hidden" name="components[{{$rowCount}}][hsn_id]" value="{{@$item->hsn_id}}" /> 
                                            <input type="hidden" name="components[{{$rowCount}}][hsn_code]" value="{{@$item->hsn_code}}" />
                                        </td>
                                        <td class="poprod-decpt"> 
                                            <button type="button" class="btn p-25 btn-sm btn-outline-secondary attributeBtn" data-row-count="{{$rowCount}}" style="font-size: 10px">Attributes</button>
                                        </td>
                                        <td>
                                            <select class="form-select mw-100 " name="components[{{$rowCount}}][uom_id]">
                                                <option value="{{@@$item->uom->id}}">{{ucfirst(@@$item->uom->name)}}</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control mw-100 order_qty" name="components[{{$rowCount}}][order_qty]" value="{{@$item->order_qty}}"  />
                                        </td>
                                        <td>
                                            <input type="text" class="form-control mw-100 receipt_qty" name="components[{{$rowCount}}][receipt_qty]" value="{{@$item->receipt_qty}}" />
                                        </td>
                                        <td>
                                            <input type="text" class="form-control mw-100 accepted_qty" name="components[{{$rowCount}}][accepted_qty]" value="{{@$item->accepted_qty}}" />
                                        </td>
                                        <td>
                                            <input type="text" class="form-control mw-100 rejected_qty" name="components[{{$rowCount}}][rejected_qty]" value="{{@$item->rejected_qty}}" />
                                        </td>
                                        <td><input type="text" name="components[{{$rowCount}}][rate]" value="{{@$item->rate}}" class="form-control mw-100 text-end rate" /></td>
                                        <td>
                                            <input type="text" name="components[{{$rowCount}}][basic_value]" value="{{@$item->basic_value}}" class="form-control text-end mw-100 basic_value" readonly />
                                            <input type="hidden" name="components[{{$rowCount}}][basic_value1]" class="form-control mw-100 basic_value1" readonly />
                                        </td>
                                        <td>
                                            <div class="position-relative d-flex align-items-center">
                                                <input type="text" value="{{@$item->discount_amount ?? ''}}" name="components[{{$rowCount}}][discount_amount]" readonly class="form-control mw-100 text-end" style="width: 70px" />
                                                <div class="ms-50">
                                                <button data-bs-toggle="modal" type="button" data-row-count="{{$rowCount}}" data-bs-target="#discount-modal" class="btn p-25 btn-sm btn-outline-secondary waves-effect discount-modal">
                                                    Discount
                                                </button>
                                                </div>
                                            </div>
                                            @foreach(@$item->extraAmounts()->where('ted_type','Discount')->where('ted_level','Item')->get() as $over_key => $overhead)
                                                <input type="hidden" name="components[{{$rowCount}}][overhead][{{$over_key+1}}][id]" value="{{$overhead->id}}">
                                                <input type="hidden" name="components[{{$rowCount}}][overhead][{{$over_key+1}}][ted_code]" value="{{$overhead->ted_code}}">
                                                <input type="hidden" value="{{$overhead->ted_percentage}}" name="components[{{$rowCount}}][overhead][{{$over_key+1}}][ted_percentage]">
                                                <input type="hidden" value="{{$overhead->ted_amount}}" name="components[{{$rowCount}}][overhead][{{$over_key+1}}][ted_amount]">
                                            @endforeach
                                        </td>
                                        <td>
                                            <input type="text" id="item_total_cost_{{$rowCount}}" name="components[{{$rowCount}}][item_total_cost]" value="{{@$item->net_value}}" readonly class="form-control mw-100 text-end item_total_cost" />
                                        </td>
                                        <td>
                                            <div class="d-flex">
                                                <input type="hidden" id="components_stores_data_{{ $rowCount }}" name="components[{{$rowCount}}][store_data]" value=""/>
                                                @foreach(@$item->storeLocations()->get() as $over_key => $overhead)
                                                <input type="hidden" name="components[{{$rowCount}}][store_location][{{$over_key+1}}][id]" value="{{$overhead->id}}">
                                                <input type="hidden" name="components[{{$rowCount}}][store_location][{{$over_key+1}}][erp_store_id]" value="{{@$overhead->erpStore->store_id}}">
                                                <input type="hidden" name="components[{{$rowCount}}][store_location][{{$over_key+1}}][erp_rack_id]" value="{{@$overhead->erpStore->rack_id}}">
                                                <input type="hidden" name="components[{{$rowCount}}][store_location][{{$over_key+1}}][erp_shelf_id]" value="{{@$overhead->erpStore->shelf_id}}">
                                                <input type="hidden" name="components[{{$rowCount}}][store_location][{{$over_key+1}}][erp_bin_id]" value="{{@$overhead->erpStore->bin_id}}">
                                                <input type="hidden" name="components[{{$rowCount}}][store_location][{{$over_key+1}}][erp_store_qty]" value="{{@$overhead->erpStore->quantity}}">
                                                @endforeach
                                                <div class="me-50 cursor-pointer store-modal" data-bs-toggle="modal" data-row-count="{{$rowCount}}" data-bs-target="#store-modal">    
                                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary" 
                                                data-bs-original-title="Store Location" aria-label="Store Location">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" 
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                                                    class="feather feather-map-pin">
                                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></span>
                                                </div>
                                                <input type="hidden" id="components_remark_{{ $rowCount }}" name="components[{{$rowCount}}][remark]" value="{{@$item->remark}}"/>
                                                <div class="me-50 cursor-pointer remark-modal" data-bs-toggle="modal" data-row-count="{{$rowCount}}" data-bs-target="#remark-modal" onclick="setItemIdInRemarkModal('{{$rowCount}}');">        
                                                <span data-bs-toggle="tooltip" data-bs-placement="top" title="" class="text-primary" 
                                                data-bs-original-title="Remarks" aria-label="Remarks">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" 
                                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" 
                                                    class="feather feather-file-text">
                                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                        <polyline points="14 2 14 8 20 8"></polyline>
                                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                                        <polyline points="10 9 9 9 8 9"></polyline>
                                                    </svg>
                                                </span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="totalsubheadpodetail"> 
                                        <td colspan="9"></td>
                                        <td class="text-end" id="totalItemValue">
                                            {{@$mrn->items->sum('basic_value')}}
                                        </td>
                                        <td class="text-end" id="totalItemDiscount">
                                            {{@$mrn->items->sum('discount_amount')}}
                                        </td>
                                        <td class="text-end" id="TotalEachRowAmount">
                                            {{@$mrn->items->sum('net_value')}}
                                        </td>
                                    </tr>
                                    <tr valign="top">
                                        <td colspan="9" rowspan="10">
                                            <table class="table border" id="itemDetailDisplay">
                                                <tr>
                                                    <td class="p-0">
                                                        <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="poprod-decpt">
                                                        <span class="poitemtxt mw-100"><strong>Name</strong>:</span>
                                                    </td> 
                                                </tr>
                                                <tr> 
                                                    <td class="poprod-decpt">
                                                        <span class="badge rounded-pill badge-light-primary"><strong>HSN</strong>:</span>
                                                        <span class="badge rounded-pill badge-light-primary"><strong>Color</strong>:</span>
                                                        <span class="badge rounded-pill badge-light-primary"><strong>Size</strong>:</span>
                                                    </td> 
                                                </tr> 
                                                <tr>
                                                    <td class="poprod-decpt">
                                                        <span class="badge rounded-pill badge-light-primary"><strong>Inv. UOM</strong>: </span>
                                                        <span class="badge rounded-pill badge-light-primary"><strong>Qty.</strong>:</span>
                                                        <span class="badge rounded-pill badge-light-primary"><strong>Exp. Date</strong>: </span> 
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="poprod-decpt">
                                                        <span class="badge rounded-pill badge-light-primary"><strong>Ava. Stock</strong>: </span>
                                                    </td> 
                                                </tr>
                                                <tr>
                                                    <td class="poprod-decpt">
                                                        <span class="badge rounded-pill badge-light-secondary"><strong>Remarks</strong>: </span>
                                                    </td>
                                                </tr>
                                            </table> 
                                        </td>
                                        <td colspan="4">
                                            <table class="table border mrnsummarynewsty">
                                                <tr>
                                                    <td colspan="2" class="p-0">
                                                        <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between"><strong>PO Summary</strong>
                                                            <div class="addmendisexpbtn">
                                                                <button class="btn p-25 btn-sm btn-outline-secondary summaryDisBtn"><i data-feather="plus"></i> Discount</button>
                                                                <button class="btn p-25 btn-sm btn-outline-secondary summaryExpBtn"><i data-feather="plus"></i> Expenses</button>
                                                            </div>                                   
                                                        </h6>
                                                    </td>
                                                </tr>
                                                <tr class="totalsubheadpodetail"> 
                                                    <td width="55%"><strong>Sub Total</strong></td>  
                                                    <td class="text-end" id="f_sub_total">
                                                        {{@$mrn->items->sum('basic_value')}}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Item Discount</strong></td>
                                                    <td class="text-end" id="f_total_discount">
                                                        {{@$mrn->items->sum('discount_amount')}}
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Header Discount</strong></td>
                                                    <td class="text-end" id="f_header_discount">
                                                        {{@$mrn->discount_amount}}
                                                    </td>
                                                </tr>
                                                <tr class="totalsubheadpodetail">
                                                    <td><strong>Taxable Value</strong></td>  
                                                    <td class="text-end" id="f_taxable_value" amount="">
                                                        {{@$mrn->taxable_amount}}
                                                    </td>
                                                </tr>
                                                <tr> 
                                                    <td><strong>Tax</strong></td>  
                                                    <td class="text-end" id="f_tax">
                                                        {{@$mrn->items->sum('tax_value')}}
                                                    </td>
                                                </tr>
                                                <!-- <tr class="totalsubheadpodetail"> 
                                                    <td><strong>Total After Tax</strong></td>  
                                                    <td class="text-end" id="f_total_after_tax">
                                                        {{@$mrn->taxable_value}}
                                                    </td>
                                                </tr> -->
                                                <tr> 
                                                    <td><strong>Exp.</strong></td>  
                                                    <td class="text-end" id="f_exp">
                                                        {{@$mrn->expense_amount}}
                                                    </td>
                                                </tr>
                                                <tr class="voucher-tab-foot">
                                                    <td class="text-primary"><strong>Total After Exp.</strong></td>  
                                                    <td>
                                                        <div class="quottotal-bg justify-content-end"> 
                                                            <h5 id="f_total_after_exp">
                                                                {{@$mrn->total_amount}}
                                                            </h5>
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
                                <div class="col-md-4">
                                    <div class="mb-1">
                                        <label class="form-label">Upload Document</label>
                                        <input type="file" name="attachment[]" class="form-control" multiple>
                                    </div>
                                </div> 
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">  
                                    <label class="form-label">Final Remarks</label> 
                                    <textarea type="text" rows="4" name="remarks" value="{{@$mrn->final_remarks}}" class="form-control" placeholder="Enter Remarks here..."></textarea> 
                                </div>
                            </div>
                        </div> 
                    </div> 
                </div> 
            </div>
        </div>
    <div>
    <!-- <div class="card quation-card">
        <div class="card-header newheader">
            <div>
                <h4 class="card-title">Terms & Conditions</h4> 
            </div>
        </div>
        <div class="card-body"> 
            <div class="row"> 
                <div class="col-md-6">
                    <div class="mb-1">
                        <label class="form-label">Select Templates</label> 
                        <select class="form-select" name="term_id">
                            <option value="">Select</option> 
                            <option value="1">Term One</option> 
                        </select>
                    </div>
                </div>
                <div class="col-md-12">
                    <div class="mb-1">
                        <label class="form-label">Description <span class="text-danger">*</span></label> 
                        <textarea class="form-control" name="description" rows="8" placeholder="Enter Description here..."></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div> -->
</div>
</div>
</div>
</section>
</div>
</div>
</div>

{{-- Discount summary modal --}}
@include('procurement.material-receipt.partials.summary-disc-modal')

{{-- Add expenses modal--}}
@include('procurement.material-receipt.partials.summary-exp-modal')

{{-- Edit Address --}}
<div class="modal fade" id="edit-address" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
        
    </div>
</div>



{{-- Attribute popup --}}
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
<tbody>

</tbody>
</table>
</div>
</div>
<div class="modal-footer justify-content-center">  
    <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button> 
    <button type="button" data-bs-dismiss="modal" class="btn btn-primary">Select</button>
</div>
</div>
</div>
</div>

{{-- Add each row discount popup --}}
<div class="modal fade" id="itemRowDiscountModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1" id="shareProjectTitle">Discount</h1>
                {{-- <p class="text-center">Enter the details below.</p> --}}
                <div class="text-end"><a href="javascript:;" class="text-primary add-contactpeontxt mt-50 addDiscountItemRow"><i data-feather='plus'></i> Add Discount</a></div>
                <div class="table-responsive-md customernewsection-form">
                    <table id="eachRowDiscountTable" class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"> 
                        <thead>
                           <tr>
                            <th>#</th>
                            <th width="150px">Discount Name</th>
                            <th>Discount %</th>
                            <th>Discount Value</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                     <tr class="display_discount_row">
                        <td>1</td>
                        <td>
                            <input type="hidden" name="row_count" id="row_count">
                            <input type="text" name="itemDiscountName" class="form-control mw-100">
                        </td>
                        <td>
                            <input type="number" name="itemDiscountPercentage" class="form-control mw-100" />
                        </td>
                        <td>
                            <input type="number" name="itemDiscountAmount" class="form-control mw-100" /></td>
                        <td>
                            <a href="javascript:;" class="text-danger deleteItemDiscountRow"><i data-feather="trash-2"></i></a>
                       </td>
                    </tr>   
                   <tr id="disItemFooter">
                       <td colspan="2"></td>
                       <td class="text-dark"><strong>Total</strong></td>
                       <td class="text-dark"><strong id="total">0.00</strong></td>
                       <td></td>
                   </tr>
           </tbody>
       </table>
   </div>
</div>
<div class="modal-footer justify-content-center">  
    <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button> 
    <button type="button" class="btn btn-primary itemDiscountSubmit">Submit</button>
</div>
</div>
</div>
</div>

{{-- Delivery schedule --}}
<div class="modal fade" id="deliveryScheduleModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" >
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1" id="shareProjectTitle">Delivery Schedule</h1>
                {{-- <p class="text-center">Enter the details below.</p> --}}
                
                <div class="text-end"> <a href="javascript:;" class="text-primary add-contactpeontxt mt-50 addTaxItemRow"><i data-feather='plus'></i> Add Schedule</a></div>

                <div class="table-responsive-md customernewsection-form">
                    <table id="deliveryScheduleTable" class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail"> 
                        <thead>
                           <tr>
                            <th>#</th>
                            <th width="150px">Quantity</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        
                        <tr id="deliveryFooter">
                           <td class="text-dark"><strong>Total</strong></td>
                           <td class="text-dark"><strong id="total">0.00</strong></td>
                           <td></td>
                           <td></td>
                        </tr>
                    </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer justify-content-center">  
                <button type="button" data-bs-dismiss="modal"  class="btn btn-outline-secondary me-1">Cancel</button> 
                <button type="button" class="btn btn-primary itemDeliveryScheduleSubmit">Submit</button>
            </div>
        </div>
    </div>
</div>

{{-- Item Remark Modal --}}
<div class="modal fade" id="itemRemarkModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" >
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-2 mx-50 pb-2">
                <h1 class="text-center mb-1" id="shareProjectTitle">Remarks</h1>
                {{-- <p class="text-center">Enter the details below.</p> --}}
                <div class="row mt-2">
                    <div class="col-md-12 mb-1">
                        <label class="form-label">Remarks <span class="text-danger">*</span></label>
                        <input type="hidden" name="row_count" id="row_count">
                        <textarea class="form-control" placeholder="Enter Remarks"></textarea>
                    </div> 
                </div>              
            </div>
            <div class="modal-footer justify-content-center">  
                <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button> 
                <button type="button" class="btn btn-primary itemRemarkSubmit">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- Store Item Modal Start -->
<div class="modal fade" id="store-modal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered" style="max-width: 900px">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" id="store-item-form">
                <input type="hidden" id="store_row_id" name="store_row_id">
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Store Location</h1>
                    <p class="text-center">Enter the details below.</p>
                    <div class="text-end">
                        <a href="#" class="text-primary add-contactpeontxt mt-50" id="add-store-row">
                            <i data-feather='plus'></i> Add Store
                        </a>
                    </div>
                    <div class="table-responsive-md customernewsection-form">
                        <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                            <thead>
                                <tr>
                                    <th width="80px">#</th>
                                    <th>Store</th>
                                    <th>Rack</th>
                                    <th>Shelf</th>
                                    <th>Bin</th>
                                    <th width="50px">Qty</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="item-store-locations">
                                
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">  
                    <button type="reset" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button> 
                    <button type="button" id="store-item-form-submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Store Item Modal End -->

<!-- Approve/Reject Modal -->
@include('procurement.material-receipt.partials.approve-modal', ['id' => $mrn->id])
<!-- Amendement Modal -->
@include('procurement.material-receipt.partials.amendement-modal', ['id' => $mrn->id])

@endsection
@section('scripts')
<script>
/*Bind button value*/
$(document).on('click','#submit-button',(e) => {
  $("#document_status").val(e.target.value);
});
/*Bind button value*/
$(document).on('click','#submit-button',(e) => {
  $("#document_status").val(e.target.value);
});
 $(document).on('change','#book_id',(e) => {
  let bookId = e.target.value;
  if (bookId) {
   getDocNumberByBookId(bookId); 
} else {
   $("#document_number").val('');
   $("#book_id").val('');
   $("#document_number").attr('readonly', false);
}
});

function getDocNumberByBookId(bookId) {
  let actionUrl = '{{route("bill.of.material.doc.no")}}'+'?book_id='+bookId;
  fetch(actionUrl).then(response => {
    return response.json().then(data => {
        if (data.status == 200) {
          $("#book_code").val(data.data.book_code);
          if(!data.data.doc.voucher_no) {
             $("#document_number").val('');
         }
         $("#document_number").val(data.data.doc.voucher_no);
         if(data.data.doc.type == 'Manually') {
             $("#document_number").attr('readonly', false);
         } else {
             $("#document_number").attr('readonly', true);
         }
     }
     if(data.status == 404) {
      alert(data.message);
  }
});
}); 
}

    /*Vendor drop down*/
function initializeAutocomplete1(selector, type) {
    $(selector).autocomplete({
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
                    response($.map(data, function(item) {
                        return {
                            id: item.id,
                            label: item.company_name,
                            code: item.vendor_code,
                            addresses: item.addresses
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
            console.log(ui.item);
            var $input = $(this);
            var itemName = ui.item.value;
            var itemId = ui.item.id;
            var itemCode = ui.item.code;
            $input.attr('data-name', itemName);
            $input.val(itemName);
            $("#vendor_id").val(itemId);
            $("#vendor_code").val(itemCode);
            let actionUrl = "{{route('po.get.address')}}"+'?id='+itemId;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if(data.status == 200) {
                    let curOption = `<option value="${data.data.currency.id}">${data.data.currency.name}</option>`;
                    let termOption = `<option value="${data.data.paymentTerm.id}">${data.data.paymentTerm.name}</option>`;
                    $('[name="currency_id"]').empty().append(curOption);
                    $('[name="payment_term_id"]').empty().append(termOption);
                    $("#shipping_id").val(data.data.shipping.id);
                    $("#billing_id").val(data.data.billing.id);
                    $(".shipping_detail").text(data.data.shipping.display_address);
                    $(".billing_detail").text(data.data.billing.display_address);
                    }
                });
            });
            return false;
        },
        change: function(event, ui) {
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
initializeAutocomplete1("#vendor_name");

/*Add New Row*/
$(document).on('click','#addNewItemBtn', (e) => {
     // for component item code
    function initializeAutocomplete2(selector, type) {
        $(selector).autocomplete({
            source: function(request, response) {
              let selectedAllItemIds = [];
              $("#itemTable tbody [id*='row_']").each(function(index,item) {
                 if(Number($(item).find('[name*="item_id"]').val())) {
                    selectedAllItemIds.push(Number($(item).find('[name*="item_id"]').val()));
                }
            });
              $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type:'po_item_list',
                    selectedAllItemIds : JSON.stringify(selectedAllItemIds)
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            id: item.id,
                            label: `${item.item_name} (${item.item_code})`,
                            code: item.item_code || '', 
                            item_id: item.id,
                            item_name:item.item_name,
                            uom_name:item.uom?.name,
                            uom_id:item.uom_id,
                            hsn_id:item.hsn?.id,
                            hsn_code:item.hsn?.code,
                            alternate_u_o_ms:item.alternate_u_o_ms,

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
            let $input = $(this);
            let itemCode = ui.item.code;
            let itemName = ui.item.value;
            let itemN = ui.item.item_name;
            let itemId = ui.item.item_id;
            let uomId = ui.item.uom_id;
            let uomName = ui.item.uom_name;
            let hsnId = ui.item.hsn_id;
            let hsnCode = ui.item.hsn_code;
            $input.attr('data-name', itemName);
            $input.attr('data-code', itemCode);
            $input.attr('data-id', itemId);
            $input.val(itemCode);
            $input.closest('tr').find('[name*=item_id]').val(itemId);
            $input.closest('tr').find('[name*=item_code]').val(itemCode);
            $input.closest('tr').find('[name*=item_name]').val(itemN);
            $input.closest('tr').find('[name*=hsn_id]').val(hsnId);
            $input.closest('tr').find('[name*=hsn_code]').val(hsnCode);
            let uomOption = `<option value=${uomId}>${uomName}</option>`;
            if(ui.item?.alternate_u_o_ms) {
                for(let alterItem of ui.item.alternate_u_o_ms) {
                uomOption += `<option value="${alterItem.uom_id}" ${alterItem.is_purchasing ? 'selected' : ''}>${alterItem.uom?.name}</option>`;
                }
            }
            $input.closest('tr').find('[name*=uom_id]').append(uomOption);
            $input.closest('tr').find('.attributeBtn').trigger('click');
            let price = 0;
            let transactionType = 'collection';
            let partyCountryId = 101;
            let partyStateId = 36;
            let rowCount = Number($($input).closest('tr').attr('data-index'));
            let queryParams = new URLSearchParams({
                price: price,
                item_id: itemId,
                transaction_type: transactionType,
                party_country_id: partyCountryId,
                party_state_id: partyStateId,
                rowCount : rowCount
            }).toString();
            taxHidden(queryParams);
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
let rowsLength = $("#itemTable > tbody > tr").length;
/*Check last tr data shoud be required*/
let lastRow = $('#itemTable .mrntableselectexcel tr:last');
let lastTrObj = {
  item_id : "",
  attr_require : true,
  row_length : lastRow.length
};

if(lastRow.length == 0) {
  lastTrObj.attr_require = false;
  lastTrObj.item_id = "0";
}

if(lastRow.length > 0) {
   let item_id = lastRow.find("[name*='item_id']").val();
   if(lastRow.find("[name*='attr_name']").length) {
      var emptyElements = lastRow.find("[name*='attr_name']").filter(function() {
          return $(this).val().trim() === '';
      });
      attr_require = emptyElements?.length ? true : false;
  } else {
     attr_require = true;
 }

 lastTrObj = {
     item_id : item_id,
     attr_require : attr_require,
     row_length : lastRow.length
 };
}

let actionUrl = '{{route("material-receipt.item.row")}}'+'?count='+rowsLength+'&component_item='+JSON.stringify(lastTrObj); 
fetch(actionUrl).then(response => {
    return response.json().then(data => {
        if (data.status == 200) {
                   // $("#submit-button").click();
            if (rowsLength) {
                $("#itemTable > tbody > tr:last").after(data.data.html);
            } else {
                $("#itemTable > tbody").html(data.data.html);
            }
            initializeAutocomplete2(".comp_item_code");
        } else if(data.status == 422) {
           Swal.fire({
            title: 'Error!',
            text: data.message || 'An unexpected error occurred.',
            icon: 'error',
        });
       } else {
           console.log("Someting went wrong!");
       }
   });
});
});


function taxHidden(queryParams)
{
    let actionUrl = '{{route("material-receipt.tax.calculation")}}';
    let urlWithParams = `${actionUrl}?${queryParams}`;
    fetch(urlWithParams).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                $(`#itemTable #row_${data.data.rowCount}`).find("[name*='t_type']").remove();
                $(`#itemTable #row_${data.data.rowCount}`).find("[name*='t_perc']").remove();
                $(`#itemTable #row_${data.data.rowCount}`).find("[name*='t_value']").remove();
                $(`#itemTable #row_${data.data.rowCount}`).find("[name*='item_total_cost']").after(data.data.html);
                setTableCalculation();

            } else {
                 Swal.fire({
                    title: 'Error!',
                    text: data.error || 'An unexpected error occurred.',
                    icon: 'error',
                });
                 return false;
            }
        });
    });
}

/*Delete Row*/
$(document).on('click','#deleteBtn', (e) => {
    let itemIds = [];
    $('#itemTable > tbody .form-check-input').each(function() {
        if ($(this).is(":checked")) {
         itemIds.push($(this).val()); 
     }
 });
    if (itemIds.length) {
        itemIds.forEach(function(item,index) {
            $(`#row_${item}`).remove();
        });
        setTableCalculation();
    } else {
        alert("Please first add & select row item.");
    }
    if(!$("[id*='row_']").length) {
        $("#itemTable > thead .form-check-input").prop('checked',false);
    }
});

/*Check box check and uncheck*/
$(document).on('change','#itemTable > thead .form-check-input',(e) => {
    if (e.target.checked) {
        $("#itemTable > tbody .form-check-input").each(function(){
            $(this).prop('checked',true);
        });
    } else {
        $("#itemTable > tbody .form-check-input").each(function(){
            $(this).prop('checked',false);
        });
    }
});
$(document).on('change','#itemTable > tbody .form-check-input',(e) => {
    if(!$("#itemTable > tbody .form-check-input:not(:checked)").length) {
        $('#itemTable > thead .form-check-input').prop('checked', true);
    } else {
        $('#itemTable > thead .form-check-input').prop('checked', false);
    }
});

/*Check attrubute*/
$(document).on('click', '.attributeBtn', (e) => {
    let tr = e.target.closest('tr');
    let item_name = tr.querySelector('[name*=item_code]').value;
    let item_id = tr.querySelector('[name*=item_id]').value;
    let selectedAttr = [];
    const attrElements = tr.querySelectorAll('[name*=attr_name]');
    if (attrElements.length > 0) {
        selectedAttr = Array.from(attrElements).map(element => element.value);
        selectedAttr = JSON.stringify(selectedAttr);
    }
    if (item_name && item_id) {
        let rowCount = e.target.getAttribute('data-row-count');
        getItemAttribute(item_id, rowCount, selectedAttr, tr);
    } else {
        alert("Please select first item name.");
    }
});

/*For comp attr*/
function getItemAttribute(itemId, rowCount, selectedAttr, tr){
    let actionUrl = '{{route("material-receipt.item.attr")}}'+'?item_id='+itemId+`&rowCount=${rowCount}&selectedAttr=${selectedAttr}`;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                $("#attribute tbody").empty();
                $("#attribute table tbody").append(data.data.html)
                $("#attribute").modal('show');
                $(tr).find('td:nth-child(2)').find("[name*=attr_name]").remove();
                $(tr).find('td:nth-child(2)').append(data.data.hiddenHtml)
            }
        });
    });
}

/*Attribute on change*/
$(document).on('change', '[name*="comp_attribute"]', (e) => {
    let rowCount = e.target.closest('tr').querySelector('[name*="row_count"]').value;
    let attrGroupId = e.target.getAttribute('data-attr-group-id');
    $(`[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`).val(e.target.value)
});

/*Discount bind on input*/
$(document).on('change input', "[name='itemDiscountPercentage']", (e) => {
    if(e.target.value) {
        let rowCount = Number($(e.target).closest('tbody').find('#row_count').val());
        $(e.target).closest('tr').find("[name='itemDiscountAmount']").prop('readonly', true);
        let itemValue = Number($("#itemTable #row_"+rowCount).find("[name*='item_value']").val());
        let disAmount = itemValue * Number(e.target.value) / 100;  
        $(e.target).closest('tr').find("[name='itemDiscountAmount']").prop('readonly', true).val(disAmount);
    } else {
        $(e.target).closest('tr').find("[name='itemDiscountAmount']").prop('readonly', false).val('');
    }
    totalItemDiscountAmount();
});
$(document).on('change input', "[name='itemDiscountAmount']", (e) => {
    if(e.target.value) {
        $(e.target).prop('readonly', false);
        $(e.target).closest('tr').find("[name='itemDiscountPercentage']").prop('readonly', true).val('');
    } else {
        $(e.target).closest('tr').find("[name='itemDiscountPercentage']").prop('readonly', false).val('');
    }
    totalItemDiscountAmount();
});

/*Add discount row*/
$(document).on('click', '.addDiscountItemRow', (e) => {
    let disName = $("[name='itemDiscountName']").val();
    let disPerc = $("[name='itemDiscountPercentage']").val();
    let disAmount = $("[name='itemDiscountAmount']").val();
    if(disName && (disPerc || disAmount)) {
        let rowCount = $(e.target.closest('tbody')).find("#row_count").val();
        let tblRowCount = $("#eachRowDiscountTable").find('.display_discount_row').length;
        let actionUrl = '{{route("material-receipt.item.discount.row")}}'+'?tbl_row_count='+tblRowCount+'&row_count='+rowCount+'&dis_name='+disName+'&dis_perc='+disPerc+'&dis_amount='+disAmount;
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                $("#disItemFooter").before(data.data.html);
            });
        });
    } else {
        Swal.fire({
            title: 'Error!',
            text: 'Please first fill mandatory data.',
            icon: 'error'
        });
    }
});

/*Calculate total amount of discount rows for the item*/
function totalItemDiscountAmount()
{
    let total = 0;
    $("#eachRowDiscountTable .display_discount_row").each(function(index,item){
        total = total + Number($(item).find('[name="itemDiscountAmount"]').val());
    });
    $("#disItemFooter #total").text(total.toFixed(2));
}

/*Each row addDiscountBtn*/
$(document).on('click', '.addDiscountBtn', (e) => {
    let rowCount = e.target.closest('button').getAttribute('data-row-count');
    let disRows = '';
    let total = 0.00;
    if (!$("#itemTable #row_"+rowCount).find("[name*=discounts]").length) {
        let itemValue = Number($("#itemTable #row_"+rowCount).find("[name*=item_value]").val());
        if(!itemValue) {
            $("#itemRowDiscountModal").find('[name=itemDiscountPercentage]').prop('readonly',true);
        } else {
            $("#itemRowDiscountModal").find('[name=itemDiscountPercentage]').prop('readonly',false);
        }
        $("#itemRowDiscountModal").find('#row_count').val(rowCount);
    } else {
        let itemValue = Number($("#itemTable #row_"+rowCount).find("[name*=item_value]").val());
        $(".display_discount_row").remove();
        $("#itemTable #row_"+rowCount).find("[name*=dis_name]").each(function(index,item) {
        let disName =  $(item).closest('td').find(`[name='components[${rowCount}][discounts][${index+1}][dis_name]']`).val();
        let disPerc = $(item).closest('td').find(`[name='components[${rowCount}][discounts][${index+1}][dis_perc]']`).val();
        let disAmount = $(item).closest('td').find(`[name='components[${rowCount}][discounts][${index+1}][dis_amount]']`).val();
        total = total + Number(disAmount); 

        disRows+=`<tr class="display_discount_row">
                    <td>${index+1}</td>
                    <td>
                        <input type="hidden" value="${rowCount}" name="row_count" id="row_count" class="form-control mw-100">
                        <input type="text" value="${disName}" name="itemDiscountName" class="form-control mw-100">
                    </td>
                    <td>
                        <input type="number" ${itemValue ? '' : 'readonly'} value="${disPerc}" name="itemDiscountPercentage" class="form-control mw-100" />
                    </td>
                    <td>
                        <input type="number" value="${disAmount}" name="itemDiscountAmount" class="form-control mw-100" /></td>
                    <td>
                        <a data-row-count="${rowCount}" data-index="${index+1}" href="javascript:;" class="text-danger deleteItemDiscountRow"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></a>
                   </td>
                </tr>`;
        });
    }
    $("#disItemFooter").before(disRows);
    $("#disItemFooter #total").text(total.toFixed(2));
    $('#itemRowDiscountModal').modal('show');
});

/*itemDiscountSubmit*/
$(document).on('click', '.itemDiscountSubmit', (e) => {
    let rowCount = $('#eachRowDiscountTable').find('#row_count').val();
    let hiddenHtml = '';
    let total = 0.00;
   $("#eachRowDiscountTable .display_discount_row").each(function(index,item){
        let disName =  $(item).find("[name='itemDiscountName']").val();
        let disPerc = $(item).find("[name='itemDiscountPercentage']").val();
        let disAmount = $(item).find("[name='itemDiscountAmount']").val();
        total = total + Number(disAmount); 
        hiddenHtml+=`<input type="hidden" value="${disName}" name="components[${rowCount}][discounts][${index+1}][dis_name]"/>
                     <input type="hidden" value="${disPerc}" name="components[${rowCount}][discounts][${index+1}][dis_perc]" />
                     <input type="hidden" value="${disAmount}" name="components[${rowCount}][discounts][${index+1}][dis_amount]" />`;
    }); 
    $("#itemTable #row_"+rowCount).find("[name*='dis_name']").remove();
    $("#itemTable #row_"+rowCount).find("[name*='dis_perc']").remove();
    $("#itemTable #row_"+rowCount).find("[name*='dis_amount']").remove();
   $("#itemTable #row_"+rowCount).find("[name*='[discount_amount]']").after(hiddenHtml);
   $("#itemTable #row_"+rowCount).find("[name*='[discount_amount]']").val(total);
   $("#itemRowDiscountModal").modal('hide');
   setTableCalculation();
});

/*Delete deleteItemDiscountRow*/
$(document).on('click', '.deleteItemDiscountRow', (e) => {
    let rowCount = e.target.closest('a').getAttribute('data-row-count') || 0;
    let index = e.target.closest('a').getAttribute('data-index') || 0;
    let total = 0.00;
    // if(rowCount && index) {
    // $("#itemTable #row_"+rowCount).find(`[name='components[${rowCount}][discounts][${index}][dis_name]']`).remove();
    // $("#itemTable #row_"+rowCount).find(`[name='components[${rowCount}][discounts][${index}][dis_perc]']`).remove();
    // $("#itemTable #row_"+rowCount).find(`[name='components[${rowCount}][discounts][${index}][dis_amount]']`).remove();
    // }
    // $("#itemTable #row_"+rowCount).find(`[name*=dis_amount]`).each(function(index,item){
    //     total = total + Number($(item).val());
    // }); 
    e.target.closest('tr').remove();
    $("#eachRowDiscountTable .display_discount_row").each(function(index,item){
        let disAmount = $(item).find("[name='itemDiscountAmount']").val();
        total += Number(disAmount);
    });
    $("#disItemFooter #total").text(total.toFixed(2));
});

// Store Racks Model
$(document).on('click','.store-modal', (e) => {
    console.log('store');
    var dataRowId = $(e.currentTarget).attr('data-row-count');
    $("#store_row_id").val(dataRowId);
    let itemStoreData = JSON.parse($("#components_stores_data_"+dataRowId).val() || "[]");
    console.log(itemStoreData);

    storeSrNo = 1;
    let html = "";
    itemStoreData.forEach((arr, key) => {
        html += '<tr class="add-more-store-locations">';
        html += '<td>' + storeSrNo +'</td>';
        html += '<td>';
        html += '<select class="form-select mw-100 select2 item_store_code" id="erp_store_id_' + storeSrNo +'" data-id="' + storeSrNo +
            '" name="erp_store[' + storeSrNo +'][erp_store_id]">';
        html += '<option value="">Select</option> ';
        html += '@foreach ($erpStores as $val)<option value="{{ $val->id }}">{{ $val->store_code }}</option>@endforeach';
        html += '</select></td>';
        html += '<td>';
        html += '<select class="form-select mw-100 select2 item_rack_code" id="erp_rack_id_' + storeSrNo +'" data-id="' + storeSrNo +
            '" name="erp_store[' + storeSrNo +'][erp_rack_id]">';
        html += '<option value="">Select</option> ';
        html += '</select></td>';
        html += '<td>';
        html += '<select class="form-select mw-100 select2 item_shelf_code" id="erp_shelf_id_' + storeSrNo +'" data-id="' + storeSrNo +
            '" name="erp_store[' + storeSrNo +'][erp_shelf_id]">';
        html += '<option value="">Select</option> ';
        html += '</select></td>';
        html += '<td>';
        html += '<select class="form-select mw-100 select2 item_bin_code" id="erp_bin_id_' + storeSrNo +'" data-id="' + storeSrNo +
            '" name="erp_store[' + storeSrNo +'][erp_bin_id]">';
        html += '<option value="">Select</option> ';
        html += '</select></td>';
        html += '<td><input type="number" class="form-control mw-100 item_store_qty" data-id="' + storeSrNo +
            '" id="erp_store_qty_' + storeSrNo +'" name="erp_store[' + storeSrNo +'][erp_store_qty]" value="'+arr.value+'" /></td>';
        html += '<td><a href="#" class="text-danger remove-store" style="color:red;">Remove</a></td>';
        html += '</tr>';

        storeSrNo++;
    });

    if (feather) {
        feather.replace({
            width: 14,
            height: 14
        });
    }

    $("#item-store-locations").html(html);
});

var storeSrNo = 1;
$(document).on('click', "#add-store-row", function(e) {
    var html = '';
    html += '<tr class="add-more-store-locations">';
    html += '<td>' + storeSrNo +'</td>';
    html += '<td>';
        html += '<select class="form-select mw-100 select2 item_store_code" data-id="' + storeSrNo +
            '" id="erp_store_id_' + storeSrNo +'" name="erp_store[' + storeSrNo +'][erp_store_id]">';
        html += '<option value="">Select</option> ';
        html += '@foreach ($erpStores as $val)<option value="{{ $val->id }}">{{ $val->store_code }}</option>@endforeach';
        html += '</select></td>';
        html += '<td>';
        html += '<select class="form-select mw-100 select2 item_rack_code" data-id="' + storeSrNo +
            '" id="erp_rack_id_' + storeSrNo +'" name="erp_store[' + storeSrNo +'][erp_rack_id]">';
        html += '<option value="">Select</option> ';
        html += '</select></td>';
        html += '<td>';
        html += '<select class="form-select mw-100 select2 item_shelf_code" data-id="' + storeSrNo +
            '" id="erp_shelf_id_' + storeSrNo +'" name="erp_store[' + storeSrNo +'][erp_shelf_id]">';
        html += '<option value="">Select</option> ';
        html += '</select></td>';
        html += '<td>';
        html += '<select class="form-select mw-100 select2 item_bin_code" data-id="' + storeSrNo +
            '" id="erp_bin_id_' + storeSrNo +'" name="erp_store[' + storeSrNo +'][erp_bin_id]">';
        html += '<option value="">Select</option> ';
        html += '</select></td>';
        html += '<td><input type="number" class="form-control mw-100 item_store_qty" data-id="' + storeSrNo +
            '" id="erp_store_qty_' + storeSrNo +'" name="erp_store[' + storeSrNo +'][erp_store_qty]" /></td>';
    html += '<td><a href="#" class="text-danger remove-store" style="color:red;">Remove</a></td>';
    html += '</tr>';

    storeSrNo++;

    if (feather) {
        feather.replace({
            width: 14,
            height: 14
        });
    }

    $("#item-store-locations").append(html);
});

$(document).on('click', '.remove-store', function(e) {
    e.preventDefault();
    if ($('#item-store-locations .add-more-store-locations').length > 1) {
        $(this).closest('tr').remove();
    }
});

$(document).on('click','#store-item-form-submit', (e) => {
    var storeForm = new FormData($('#store-item-form')[0]);
    // Convert FormData to a JSON object
    
    // Convert FormData to a JSON object
    var storeFormData = {};
    var storeArray = [];
    var store_qty = 0;
    // Iterate over formData directly
    storeForm.forEach(function(value, key) {
        console.log(value, key);
        // Check if the key matches the pattern for header_store[X][name]
        var match = key.match(/erp_store\[(\d+)\]\[erp_store_id\]/);
        
        if (match) {
            var index = match[1]; // Extract the index number from the key
            var nameValue = value; // The name value
            var erpStoreKey = 'erp_store[' + index + '][erp_store_id]';
            var erpRackKey = 'erp_store[' + index + '][erp_rack_id]';
            var erpShelfKey = 'erp_store[' + index + '][erp_shelf_id]';
            var erpBinKey = 'erp_store[' + index + '][erp_bin_id]';
            var storeQty = 'erp_store[' + index + '][erp_store_qty]';
            // var storeQty = Number(storeForm.get(valueKey) || 0);
            // Push the name and value as an object into the array
            storeArray.push({
                erp_store_id: storeForm.get(erpStoreKey) || '',
                erp_rack_id: storeForm.get(erpRackKey) || '',
                erp_shelf_id: storeForm.get(erpShelfKey) || '',
                erp_bin_id: storeForm.get(erpBinKey) || '',
                erp_store_qty: Number(storeForm.get(storeQty) || 0),
            });
            store_qty += Number(storeForm.get(storeQty) || 0);
        }
    });
    console.log('storeArray', storeArray);
    var dataRowId = $("#store_row_id").val();
    var orderQty = $("input[name='components["+dataRowId+"][accepted_qty]']").val();
    if(orderQty < store_qty){
        alert('store quantity can not be greater than Accepted quantity');
    } else{
        $("#components_stores_data_"+dataRowId).val(JSON.stringify(storeArray));
        $("#store-modal").modal('hide');
    }
});

$(document).on('change', '.item_store_code', function() {
    var rowKey = $(this).data('id');
    var store_code_id = $(this).val();
    console.log('rowKey', rowKey);
    $('#erp_store_id_'+rowKey).val(store_code_id).select2();

    var data = {
        store_code_id: store_code_id
    };

    $.ajax({
        type: 'POST',
        data: data,
        url: '/mrns/get-store-racks',
        success: function(data) {
            $('#erp_rack_id_'+rowKey).empty();
            $('#erp_rack_id_'+rowKey).append('<option value="">Select</option>');
            $.each(data.storeRacks, function(key, value) {
                $('#erp_rack_id_'+rowKey).append('<option value="'+ key +'">'+ value +'</option>');
            });
        }
    });
});

$(document).on('change', '.item_rack_code', function() {
    var rowKey = $(this).data('id');
    var rack_code_id = $(this).val();
    $('#erp_rack_id_' + rowKey).val(rack_code_id).select2();

    var data = {
        rack_code_id: rack_code_id
    };

    $.ajax({
        type: 'POST',
        data: data,
        url: '/mrns/get-rack-shelfs',
        success: function(data) {
            $('#erp_shelf_id_'+rowKey).empty();
            $('#erp_shelf_id_'+rowKey).append('<option value="">Select</option>');
            $.each(data.storeShelfs, function(key, value) {
                $('#erp_shelf_id_'+rowKey).append('<option value="'+ key +'">'+ value +'</option>');
            });
        }
    });
});

$(document).on('change', '.item_shelf_code', function() {
    var rowKey = $(this).data('id');
    var shelf_code_id = $(this).val();
    $('#erp_shelf_id_' + rowKey).val(shelf_code_id).select2();

    var data = {
        shelf_code_id: shelf_code_id
    };

    $.ajax({
        type: 'POST',
        data: data,
        url: '/mrns/get-shelf-bins',
        success: function(data) {
            $('#erp_bin_id_'+rowKey).empty();
            $('#erp_bin_id_'+rowKey).append('<option value="">Select</option>');
            $.each(data.storeBins, function(key, value) {
                $('#erp_bin_id_'+rowKey).append('<option value="'+ key +'">'+ value +'</option>');
            });
        }
    });
});

/*qty on change*/
$(document).on('change input',"[name*='accepted_qty']",(e) => {
    let tr = e.target.closest('tr');
    let qty = e.target;
    let dataIndex = $(e.target).closest('tr').attr('data-index');
    let itemId = $(e.target).closest('tr').find('[name*=item_id]').val();
    let acceptedQuantity = $(e.target).closest('tr').find("[name*='accepted_qty']");
    let receiptQuantity = $(e.target).closest('tr').find("[name*='receipt_qty']");
    let rejectedQuantity = $(e.target).closest('tr').find("[name*='rejected_qty']");
    let itemCost = $(e.target).closest('tr').find("[name*='rate']");
    // let superceededCost = $(e.target).closest('tr').find("[name*='superceeded_cost']"); 
    let itemValue = $(e.target).closest('tr').find("[name*='basic_value']");
    if(Number(acceptedQuantity.val()) > Number(receiptQuantity.val())) {
        acceptedQuantity.val(receiptQuantity.val());
        alert("Accepted Quantity can not be greater than receipt quantity");
    } else{
        let rq = (Number(receiptQuantity.val()) - Number(acceptedQuantity.val()));
        rejectedQuantity.val(rq);

        if (Number(itemCost.val())) {
            let totalItemValue = Number(acceptedQuantity.val()) * Number(itemCost.val());
            itemValue.val(totalItemValue);
        } else {
            itemValue.val('');
        }
    }
});

/*rate on change*/
$(document).on('change',"[name*='rate']",(e) => {
    let tr = e.target.closest('tr');
    let rate = e.target;
    let dataIndex = $(e.target).closest('tr').attr('data-index');
    let itemId = $(e.target).closest('tr').find('[name*=item_id]').val();
    let orderQuantity = $(e.target).closest('tr').find("[name*='order_qty']");
    let acceptedQuantity = $(e.target).closest('tr').find("[name*='accepted_qty']");
    // let orderRate = $(e.target).closest('tr').find("[name*='rate']");
    let itemValue = $(e.target).closest('tr').find("[name*='basic_value']");
    if (Number(acceptedQuantity.val())) {
        let totalItemValue = Number(rate.value) * Number(acceptedQuantity.val());
        itemValue.val(totalItemValue);
    } else {
        itemValue.val('');
    }
});

/*Open item remark modal*/
$(document).on('click', '.addRemarkBtn', (e) => {
    let rowCount = e.target.closest('div').getAttribute('data-row-count');
    $("#itemRemarkModal #row_count").val(rowCount);
    let remarkValue = $("#itemTable #row_"+rowCount).find("[name*='remark']");

    if(!remarkValue.length) {
        $("#itemRemarkModal textarea").val('');
    } else {
        $("#itemRemarkModal textarea").val(remarkValue.val());
    }
    $("#itemRemarkModal").modal('show');
});

/*Submit item remark modal*/
$(document).on('click', '.itemRemarkSubmit', (e) => {
    let rowCount = $("#itemRemarkModal #row_count").val();
    let remarkValue = $("#itemTable #row_"+rowCount).find("[name*='remark']");
     let textValue = $("#itemRemarkModal").find("textarea").val();
    if(!remarkValue.length) {
        rowHidden = `<input type="hidden" value="${textValue}" name="components[${rowCount}][remark]" />`;
        $("#itemTable #row_"+rowCount).find('.addRemarkBtn').after(rowHidden);
        
    } else{
        $("#itemTable #row_"+rowCount).find("[name*='remark']").val(textValue);
    }
    $("#itemRemarkModal").modal('hide');
});

/*on change discount summary*/
$(document).on('input change', '#summaryDiscountModal [name*="d_perc"]', (e) => {
    let perc = Number(e.target.value);
    if(perc > 100) {
        $(e.target).val(100);
    }
    summaryDisTotal();
    if(e.target.value) {
        let itemValue = Number($("#totalItemValue").attr('amount'));
        let disAmount = itemValue * Number(e.target.value) / 100;  
        $(e.target).closest('tr').find("[name*='[d_amnt]']").prop('readonly', true).val(disAmount);
    } else {
        $(e.target).closest('tr').find("[name*='[d_amnt]']").prop('readonly', false).val('');
    }
});

/*on change discount summary*/
$(document).on('input change', '#summaryDiscountModal [name*="d_amnt"]', (e) => {
    summaryDisTotal();
});


/*Open summary discount modal*/
$(document).on('click', '.summaryDisBtn', (e) => {
    e.stopPropagation();
    $("#summaryDiscountModal").modal('show');
    return false;
});

/*summaryDiscountSubmit*/
$(document).on('click', '.summaryDiscountSubmit', (e) => {
    $("#summaryDiscountModal").modal('hide');
    let total = Number($("#disSummaryFooter #total").attr('amount')); 
    if(total) {
        $("#f_header_discount_hidden").removeClass('d-none');
        $("#f_header_discount").text(total.toFixed(2));
    } else {
        $("#f_header_discount_hidden").addClass('d-none');
    }
    setTaxAfterHeaderDiscount();
    return false;
});

function setTaxAfterHeaderDiscount()
{
let f1 = Number($("#f_taxable_value").attr('amount'));
let g9 = Number($("#disSummaryFooter #total").attr('amount'));
if(f1 && g9) {
    $("#itemTable [id*='row_']").each(function (index, item) {
        let e3 = Number($(item).find("[name*='[basic_value]']").val());
        let f3 = Number($(item).find("[name*='[discount_amount]']").val());
        let headerDis = (e3-f3) / f1 * g9;
        if(headerDis) {
            $(item).find("[name*='[discount_amount_header]']").val(headerDis);
        }
    });
    setTableCalculation();
}
}

/*Add summary discount row*/
$(document).on('click', '.addDiscountSummary', (e) => {
    let rowCount = $(".display_summary_discount_row").length + 1;
    let row = `<tr class="display_summary_discount_row">
                <td>${rowCount}</td>
                <td>
                    <input type="text" name="disc_summary[${rowCount}][d_name]" class="form-control mw-100">
                </td>
                <td>
                    <input type="number" name="disc_summary[${rowCount}][d_perc]" class="form-control mw-100" />
                </td>
                <td>
                    <input type="number" name="disc_summary[${rowCount}][d_amnt]" class="form-control mw-100" /></td>
                    <td>
                        <a href="javascript:;" class="text-danger deleteSummaryDiscountRow"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></a>
                    </td>
                </tr>`;
    if(!$(".display_summary_discount_row").length) {
        $("#summaryDiscountTable #disSummaryFooter").before(row);
    } else {
        $(".display_summary_discount_row:last").after(row);
    }
});

/*delete summary discount row*/
$(document).on('click', '.deleteSummaryDiscountRow', (e) => {
    $(e.target).closest('tr').remove();
    summaryDisTotal();
});

function summaryDisTotal()
{
    let total = 0.00;
    $(".display_summary_discount_row [name*='[d_amnt]']").each(function(index, item) {
        total = total + Number($(item).val());
    });
    $("#disSummaryFooter #total").attr('amount', total);
    $("#disSummaryFooter #total").text(total.toFixed(2));
}

/*Open summary expen modal*/
$(document).on('click', '.summaryExpBtn', (e) => {
    e.stopPropagation();
    $("#summaryExpenModal").modal('show');
    return false;
});

/*Add summary exp row*/
$(document).on('click', '.addExpSummary', (e) => {
    let rowCount = $(".display_summary_exp_row").length + 1;
    let row = `<tr class="display_summary_exp_row">
                <td>${rowCount}</td>
                <td>
                    <input type="text" name="exp_summary[${rowCount}][e_name]" class="form-control mw-100">
                </td>
                <td><input type="number" name="exp_summary[${rowCount}][e_perc]" class="form-control mw-100" /></td>
                <td><input type="number" name="exp_summary[${rowCount}][e_amnt]" class="form-control mw-100" /></td>
                <td>
                    <a href="javascript:;" class="text-danger deleteExpRow"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></a>
               </td>
            </tr>`;
    if(!$(".display_summary_exp_row").length) {
        $("#summaryExpTable #expSummaryFooter").before(row);
    } else {
        $(".display_summary_exp_row:last").after(row);
    }
});

/*delete summary exp row*/
$(document).on('click', '.deleteExpRow', (e) => {
    $(e.target).closest('tr').remove();
    summaryExpTotal();
});

// summaryExpSubmit
$(document).on('click', '.summaryExpSubmit', (e) => {
    $("#summaryExpenModal").modal('hide');
    setTableCalculation();
});

/*on change exp summary*/
$(document).on('input change', '#summaryExpenModal [name*="e_perc"]', (e) => {
    let perc = Number(e.target.value);
    if(perc > 100) {
        $(e.target).val(100);
    }
    summaryExpTotal();
});

/*on change exp summary*/
$(document).on('input change', '#summaryExpenModal [name*="e_amnt"]', (e) => {
    summaryExpTotal();
});

function summaryExpTotal()
{
    let total = 0.00;
    $(".display_summary_exp_row [name*='e_amnt']").each(function(index, item) {
        total = total + Number($(item).val());
    });
    $("#expSummaryFooter #total").attr('amount', total);
    $("#expSummaryFooter #total").text(total.toFixed(2));
}

function setTableCalculation() {
    let totalItemValue = 0.00;
    let totalItemDis = 0.00;
    let totalItemCost = 0.00;
    let totalTax = 0.00;
    let totalAfterTax = 0.00;
    let totalExp = 0.00;
    let totalHeadDiscAmt = 0.00;
    let rowCount = 0;
    $("#itemTable [id*='row_']").each(function (index, item) {
        rowCount = Number($(item).attr('data-index'));
        let qtyRow = $(item).find("[name*='[accepted_qty]']").val() || 0;
        let rateRow = $(item).find("[name*='[rate]']").val() || 0;
        let itemValueAmountRow = (Number(qtyRow) * Number(rateRow)) || 0;
        totalItemValue = totalItemValue + itemValueAmountRow;
        $(item).find("[name*='[basic_value]']").val(itemValueAmountRow);
        let disAmountRow = $(item).find("[name*='[discount_amount]']").val() || 0;
        let headDiscAmt = $(item).find("[name*='[discount_amount_header]']").val() || 0;
        totalHeadDiscAmt = totalHeadDiscAmt + Number(headDiscAmt);
        totalItemDis = totalItemDis + Number(disAmountRow);
        let itemTotalCostRow = itemValueAmountRow - Number(disAmountRow);
        totalItemCost = totalItemCost + itemTotalCostRow;
        $(item).find("[name*='[item_total_cost]']").val(itemTotalCostRow);

        if($(item).find("[name*='[t_perc]']").length && itemTotalCostRow) {
            let taxAmountRow = 0.00;
            $(item).find("[name*='[t_perc]']").each(function(index,eachItem) {
                let eachTaxTypePrice = 0;
                let taxPercTax = $(eachItem).val();
                if(Number(headDiscAmt)) {
                    eachTaxTypePrice = ((itemTotalCostRow - Number(headDiscAmt)) * taxPercTax) / 100; 
                    taxAmountRow += eachTaxTypePrice;
                $(item).find(`[name="components[${rowCount}][taxes][${index+1}][t_value]"]`).val(eachTaxTypePrice);
                } else {
                    eachTaxTypePrice = (itemTotalCostRow * taxPercTax) / 100; 
                    taxAmountRow += eachTaxTypePrice;
                    $(item).find(`[name="components[${rowCount}][taxes][${index+1}][t_value]"]`).val(eachTaxTypePrice);
                }
            });
            totalTax = totalTax + taxAmountRow;
        }

    });

    totalAfterTax = (totalItemValue-totalItemDis) + totalTax;
    $("#totalItemValue").attr('amount',totalItemValue);
    $("#totalItemValue").text(totalItemValue.toFixed(2));
    $("#totalItemDiscount").text(totalItemDis.toFixed(2));
    $("#TotalEachRowAmount").text(totalItemCost.toFixed(2));
    $("#f_sub_total").text(totalItemValue.toFixed(2));
    $("#f_total_discount").text(totalItemDis.toFixed(2));
    $("#f_taxable_value").attr('amount',(totalItemValue-totalItemDis));
    $("#f_taxable_value").text((totalItemValue-totalItemDis-totalHeadDiscAmt).toFixed(2));
    $("#f_tax").text(totalTax.toFixed(2));
    $("#f_total_after_tax").text((totalAfterTax - totalHeadDiscAmt).toFixed(2));
    totalExp = Number($("#expSummaryFooter #total").attr('amount'));
    $("#f_exp").text(totalExp.toFixed(2));
    $("#f_total_after_exp").text(((totalAfterTax -totalHeadDiscAmt) + totalExp).toFixed(2));
}

$(document).on('input change', '#itemTable input', (e) => {
    setTableCalculation();
});

// Event listener for Edit Address button click
$(document).on('click', '.editAddressBtn', (e) => {
    let addressType = $(e.target).closest('a').attr('data-type');
    let vendorId = $("#vendor_id").val();
    let onChange = 0;
    let addressId = addressType === 'shipping' ? $("#shipping_id").val() : $("#billing_id").val();
    let actionUrl = `{{route("po.edit.address")}}?type=${addressType}&vendor_id=${vendorId}&address_id=${addressId}&onChange=${onChange}`;
    fetch(actionUrl)
        .then(response => response.json())
        .then(data => {
            if (data.status === 200) {
                $("#edit-address .modal-dialog").html(data.data.html);
                $("#address_type").val(addressType);
                $("#edit-address").modal('show');
                initializeFormComponents(data.data.selectedAddress);
            } else {
                console.error('Failed to fetch address data:', data.message);
            }
        })
        .catch(error => console.error('Error fetching address data:', error));
});

$(document).on('change', "[name='address_id']", (e) => {
    let vendorId = $("#vendor_id").val();
    let addressType = $("#address_type").val();
    let addressId = e.target.value;
    let onChange = 1;
    let actionUrl = `{{route("po.edit.address")}}?type=${addressType}&vendor_id=${vendorId}&address_id=${addressId}&onChange=${onChange}`;
    fetch(actionUrl)
        .then(response => response.json())
        .then(data => {
            if (data.status === 200) {
                initializeFormComponents(data.data.selectedAddress);
            } else {
                console.error('Failed to fetch address data:', data.message);
            }
        })
        .catch(error => console.error('Error fetching address data:', error));
});

function initializeFormComponents(selectedAddress) {
    const countrySelect = $('#country');
    fetch('/countries')
        .then(response => response.json())
        .then(data => {
            countrySelect.empty();
            countrySelect.append('<option value="">Select Country</option>');
            data.data.countries.forEach(country => {
                const isSelected = country.value === selectedAddress.country.id;
                countrySelect.append(new Option(country.label, country.value, isSelected, isSelected));
            });
            if (selectedAddress.country.value) {
                countrySelect.trigger('change');
            }
        })
        .catch(error => console.error('Error fetching countries:', error));

    countrySelect.on('change', function () {
        let countryValue = $(this).val();
        let stateSelect = $('#state_id');
        stateSelect.empty().append('<option value="">Select State</option>'); // Reset state dropdown

        if (countryValue) {
            fetch(`/states/${countryValue}`)
                .then(response => response.json())
                .then(data => {
                    data.data.states.forEach(state => {
                        const isSelected = state.value === selectedAddress.state.id;
                        stateSelect.append(new Option(state.label, state.value, isSelected, isSelected));
                    });
                    if (selectedAddress.state.value) {
                        stateSelect.trigger('change');
                    }
                })
                .catch(error => console.error('Error fetching states:', error));
        }
    });
    $('#state_id').on('change', function () {
        let stateValue = $(this).val();
        let citySelect = $('#city');
        citySelect.empty().append('<option value="">Select City</option>');
        if (stateValue) {
            fetch(`/cities/${stateValue}`)
                .then(response => response.json())
                .then(data => {
                    data.data.cities.forEach(city => {
                        const isSelected = city.value === selectedAddress.city.id;
                        citySelect.append(new Option(city.label, city.value, isSelected, isSelected));
                    });
                })
                .catch(error => console.error('Error fetching cities:', error));
        }
    });
}


/*Display item detail*/
$(document).on('input change focus', '#itemTable tr input', (e) => {
   let currentTr = e.target.closest('tr'); 
   let pName = $(currentTr).find("[name*='component_item_name']").val();
   let itemId = $(currentTr).find("[name*='item_id']").val();
   let remark = '';
   if($(currentTr).find("[name*='remark']")) {
    remark = $(currentTr).find("[name*='remark']").val() || '';
   }
   if (itemId) {
      let selectedAttr = [];
      $(currentTr).find("[name*='attr_name']").each(function(index, item) {
         if($(item).val()) {
            selectedAttr.push($(item).val());
         }
      });
      let actionUrl = '{{route("po.get.itemdetail")}}'+'?item_id='+itemId+'&selectedAttr='+JSON.stringify(selectedAttr)+'&remark='+remark;
      fetch(actionUrl).then(response => {
         return response.json().then(data => {
            if(data.status == 200) {
               $("#itemDetailDisplay").html(data.data.html);
            }
         });
      });
   }
});


/*Tbl row highlight*/
$(document).on('click', '.mrntableselectexcel tr', (e) => {
   $(e.target.closest('tr')).addClass('trselected').siblings().removeClass('trselected');
});
$(document).on('keydown', function(e) {
 if (e.which == 38) {
   /*bottom to top*/
   $('.trselected').prev('tr').addClass('trselected').siblings().removeClass('trselected');
 } else if (e.which == 40) {
   /*top to bottom*/
   $('.trselected').next('tr').addClass('trselected').siblings().removeClass('trselected');
 }
 if($('.trselected').length) {
   $('html, body').scrollTop($('.trselected').offset().top - 200); 
 }

});

/*Approve modal*/
$(document).on('click', '#approved-button', (e) => {
   let actionType = 'approve';
   $("#approveModal").find("#action_type").val(actionType);
   $("#approveModal").modal('show');
});
$(document).on('click', '#reject-button', (e) => {
   let actionType = 'reject';
   $("#approveModal").find("#action_type").val(actionType);
   $("#approveModal").modal('show');
});
/*Amendment modal*/
$(document).on('click', '#amendement-button', (e) => {
    let actionType = 'amendement';
    $("#amendementModal").find("#amendement_type").val(actionType);
    $("#amendementModal").modal('show');
});
</script>
@endsection