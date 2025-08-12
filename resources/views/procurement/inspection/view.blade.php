@extends('layouts.app')
@section('content')
    <form id="mrnEditForm" class="ajax-input-form" method="POST" action="{{ route('inspection.update', $mrn->id) }}" data-redirect="/material-receipts" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="tax_required" id="tax_required" value="">
        <div class="app-content content ">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Inspection</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a href="/">Home</a>
                                            </li>
                                            <li class="breadcrumb-item active">View</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <button type="button" onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0">
                                    <i data-feather="arrow-left-circle"></i> Back
                                </button>
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
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-sm-end">
                                                <span class="badge rounded-pill badge-light-secondary forminnerstatus">
                                                Status : <span class="{{$docStatusClass}}">{{ucfirst($mrn->display_status)}}</span>
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
                                                        <input type="hidden" name="book_id" class="form-control" id="book_id" value="{{$mrn->book_id}}" readonly>
                                                        <input readonly type="text" class="form-control" value="{{$mrn->book->book_code}}" id="book_code">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Document No <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" readonly value="{{@$mrn->document_number}}" id="document_number">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Document Date <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" id="document_date" name="document_date" class="form-control" value="{{ date('Y-m-d', strtotime($mrn->document_date)) }}" >
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Location <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select header_store_id" id="header_store_id" name="header_store_id">
                                                            @foreach($locations as $erpStore)
                                                                <option value="{{$erpStore->id}}"
                                                                    {{ $mrn->store_id == $erpStore->id ? 'selected' : '' }}>
                                                                    {{ ucfirst($erpStore->store_name) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label"> Main Store <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select sub_store" id="sub_store_id" name="sub_store_id">
                                                            <option value="{{$mrn->sub_store_id}}">
                                                                    {{ ucfirst($mrn?->erpSubStore->store_name) }}
                                                                </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Rejected Store</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select rejected_sub_store_id" id="rejected_sub_store_id" name="rejected_sub_store_id" readonly>
                                                            <option value="{{$mrn->rejected_sub_store_id}}">
                                                                {{ ucfirst($mrn?->rejectedSubStore?->name) }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <!-- <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Reference No </label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="reference_number" value="{{@$mrn->reference_number}}" class="form-control">
                                                    </div>
                                                </div> -->
                                                {{-- <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">LOT No </label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="lot_number" value="{{@$mrn->lot_number}}" class="form-control" readonly>
                                                    </div>
                                                </div> --}}
                                            </div>
                                            {{-- Approval History Section --}}
                                            @include('partials.approval-history', ['document_status' => $mrn->document_status, 'revision_number' => $revision_number])
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
                                                            <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" id="vendor_name" name="vendor_name" {{(count(($mrn->items)) > 0 ? 'readonly' : '')}} value="{{@$mrn->vendor->company_name}}" />
                                                            <input type="hidden" value="{{@$mrn->vendor_id}}" id="vendor_id" name="vendor_id" />
                                                            <input type="hidden" value="{{@$mrn->vendor_code}}" id="vendor_code" name="vendor_code" />
                                                            @if($mrn->latestShippingAddress() || $mrn->latestBillingAddress())
                                                                <input type="hidden" value="{{$mrn->latestShippingAddress()}}" id="shipping_id" name="shipping_id" />
                                                                <input type="hidden" id="billing_id" value="{{$mrn->latestBillingAddress()->id}}" name="billing_id" />
                                                                <input type="hidden" value="{{$mrn->latestShippingAddress()->state?->id}}" id="hidden_state_id" name="hidden_state_id" />
                                                                <input type="hidden" value="{{$mrn->latestShippingAddress()->country?->id}}" id="hidden_country_id" name="hidden_country_id" />
                                                            @else
                                                                <input type="hidden" value="{{$mrn->ship_to}}" id="shipping_id" name="shipping_id" />
                                                                <input type="hidden" id="billing_id" value="{{$mrn->billing_to}}" name="billing_id" />
                                                                <input type="hidden" value="{{$mrn?->shippingAddress?->state?->id}}" id="hidden_state_id" name="hidden_state_id" />
                                                                <input type="hidden" value="{{$mrn?->shippingAddress?->country?->id}}" id="hidden_country_id" name="hidden_country_id" />
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="currency_id">
                                                                <option value="{{@$mrn->currency_id}}">{{@$mrn->currency->name}}</option>
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
                                                    <div class="col-md-4">
                                                        <div class="customer-billing-section h-100">
                                                            <p>Vendor Address</p>
                                                            <div class="bilnbody">
                                                                <div class="genertedvariables genertedvariablesnone">
                                                                    <label class="form-label w-100">Vendor Address <span class="text-danger">*</span> <a href="javascript:;" class="float-end font-small-2 editAddressBtn d-none" data-type="billing"><i data-feather='edit-3'></i> Edit</a></label>
                                                                    <div class="mrnaddedd-prim billing_detail">
                                                                        @if($mrn->latestBillingAddress())
                                                                            {{$mrn->latestBillingAddress()->display_address}}
                                                                        @else
                                                                            {{$mrn->bill_address?->display_address}}
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="customer-billing-section h-100">
                                                            <p>Billing Address</p>
                                                            <div class="bilnbody">
                                                                <div class="genertedvariables genertedvariablesnone">
                                                                    <label class="form-label w-100">Billing Address <span class="text-danger">*</span>
                                                                        {{-- <a href="javascript:;" class="float-end font-small-2 editAddressBtn" data-type="billing"><i data-feather='edit-3'></i> Edit</a> --}}
                                                                    </label>
                                                                    <div class="mrnaddedd-prim org_address">{{$deliveryAddress}}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="customer-billing-section h-100">
                                                            <p>Delivery Address</p>
                                                            <div class="bilnbody">
                                                                <div class="genertedvariables genertedvariablesnone">
                                                                    <label class="form-label w-100">Delivery Address <span class="text-danger">*</span>
                                                                        {{-- <a href="javascript:;" class="float-end font-small-2 editAddressBtn" data-type="billing"><i data-feather='edit-3'></i> Edit</a> --}}
                                                                    </label>
                                                                    <div class="mrnaddedd-prim delivery_address">{{$deliveryAddress}}</div>
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
                                                                Gate Entry No.
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="text" name="gate_entry_no"
                                                                class="form-control" value="{{@$mrn->gate_entry_no}}"
                                                                placeholder="Enter Gate Entry no">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Gate Entry Date
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="date" name="gate_entry_date" value="{{date('Y-m-d', strtotime($mrn->gate_entry_date))}}"
                                                                class="form-control gate-entry" id="datepicker2"
                                                                placeholder="Enter Gate Entry Date">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                E-Way Bill No.
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="text" name="eway_bill_no" value="{{@$mrn->eway_bill_no}}"
                                                                class="form-control"
                                                                placeholder="Enter Eway Bill No.">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Consignment No.
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="text" name="consignment_no" value="{{@$mrn->consignment_no}}"
                                                                class="form-control"
                                                                placeholder="Enter Consignment No.">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                            Supplier Invoice No.
                                                            </label>
                                                            <input type="text" name="supplier_invoice_no" value="{{@$mrn->supplier_invoice_no}}"
                                                                class="form-control"
                                                                placeholder="Enter Supplier Invoice No.">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Supplier Invoice Date
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="date" name="supplier_invoice_date" value="{{date('Y-m-d', strtotime($mrn->supplier_invoice_date))}}"
                                                                class="form-control gate-entry" id="datepicker3"
                                                                placeholder="Enter Supplier Invoice Date">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                            Transporter Name
                                                            </label>
                                                            <input type="text" name="transporter_name" value="{{@$mrn->transporter_name}}"
                                                                class="form-control"
                                                                placeholder="Enter Transporter Name">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Vehicle No.
                                                                <i class="ml-2 fas fa-info-circle text-primary"
                                                                data-bs-toggle="tooltip"
                                                                data-bs-html="true"
                                                                title="Format:<br>[A-Z]{2} – 2 uppercase letters (e.g., 'MH')<br>[0-9]{2} – 2 digits (e.g., '12')<br>[A-Z]{0,3} – 0 to 3 uppercase letters (e.g., 'AB', 'ABZ')<br>[0-9]{4} – 4 digits (e.g., '1234')"></i>
                                                            </label>
                                                            <input type="text" name="vehicle_no"
                                                            class="form-control vehicle_no"
                                                            value="{{@$mrn->vehicle_no}}"
                                                            placeholder="Enter Vehicle No." />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card" id="item_section">
                                    <div class="card-body customernewsection-form">
                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader ">
                                                        <h4 class="card-title text-theme">Item Wise Detail</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
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
                                                                <th width="150px">Item Code</th>
                                                                <th width="240px">Item Name</th>
                                                                <th>Attributes</th>
                                                                <th>UOM</th>
                                                                <th class="text-end">GRN Qty</th>
                                                                <th class="text-end">Inspected Qty</th>
                                                                <th class="text-end">Acpt. Qty</th>
                                                                <th class="text-end">Rej. Qty</th>
                                                                <th width="50px">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                            @include('procurement.inspection.partials.item-row-edit')
                                                        </tbody>
                                                        <tfoot>
                                                            <tr valign="top">
                                                                <td rowspan="10" colspan="10">
                                                                    <table class="table border">
                                                                        <tbody id="itemDetailDisplay">
                                                                            <tr>
                                                                                <td class="p-0">
                                                                                    <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
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
                                                                <input type="file" name="attachment[]" class="form-control" onchange = "addFiles(this,'main_mrn_preview')" multiple>
                                                                <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                                                            </div>
                                                        </div>
                                                        @include('partials.document-preview',['documents' => $mrn->getDocuments(), 'document_status' => $mrn->document_status,'elementKey' => 'main_mrn_preview'])
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Final Remarks</label>
                                                            <textarea type="text" rows="4" name="remarks" class="form-control" placeholder="Enter Remarks here...">{!! $mrn->final_remarks !!}</textarea>
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
        {{-- Discount summary modal --}}
        @include('procurement.inspection.partials.summary-disc-modal')

        <!-- Inspection CHecklist Modal  -->
        @include('procurement.inspection.partials.inspection-checklist-modal')

        {{-- Add expenses modal--}}
        @include('procurement.inspection.partials.summary-exp-modal')
        {{-- Edit Address --}}
        <div class="modal fade" id="edit-address" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
            </div>
        </div>
    </form>

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
                    <div class="text-end"></div>
                    <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                        <thead>
                            <tr>
                                <td>#</td>
                                <td>
                                    <input type="text" id="new_item_dis_name" class="form-control mw-100" />
                                </td>
                                <td>
                                    <input step="any" type="number" id="new_item_dis_perc" class="form-control mw-100" />
                                </td>
                                <td>
                                    <input step="any" type="number" id="new_item_dis_value" class="form-control mw-100" />
                                </td>
                                <td>
                                    <a href="javascript:;" id="add_new_item_dis" class="text-primary can_hide">
                                        <i data-feather="plus-square"></i>
                                    </a>
                                </td>
                            </tr>
                        </thead>
                    </table>
                    <div class="table-responsive-md customernewsection-form">
                        <table id="eachRowDiscountTable" class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                            <thead>
                                <tr>
                                    <th>S.No</th>
                                    <th width="150px">Discount Name</th>
                                    <th>Discount %</th>
                                    <th>Discount Value</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr id="disItemFooter">
                                    <input type="hidden" name="row_count" id="row_count" value="1">
                                    <td colspan="2"></td>
                                    <td class="text-dark"><strong>Total</strong></td>
                                    <td class="text-dark text-end"><strong id="total">0.00</strong></td>
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

    {{-- Item Remark Modal --}}
    <div class="modal fade" id="itemRemarkModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered" >
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Remarks</h1>
                    <div class="row mt-2">
                        <div class="col-md-12 mb-1">
                            <label class="form-label">Remarks</label>
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
    {{-- Item Locations --}}
    @include('procurement.inspection.partials.item-location-modal')
    <!-- Item Locations Modal End -->

    {{-- Delete component modal --}}
    <div class="modal fade text-start alertbackdropdisabled" id="deleteComponentModal" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to delete selected <strong>Components</strong>?</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="deleteConfirm" class="btn btn-primary" >Confirm</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Item discount modal --}}
    <div class="modal fade text-start alertbackdropdisabled" id="deleteItemDiscModal" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to delete selected <strong>Components</strong>?</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="deleteItemDiscConfirm" class="btn btn-primary" >Confirm</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Header discount modal --}}
    <div class="modal fade text-start alertbackdropdisabled" id="deleteHeaderDiscModal" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to delete selected <strong>Components</strong>?</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="deleteHeaderDiscConfirm" class="btn btn-primary" >Confirm</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete header exp modal --}}
    <div class="modal fade text-start alertbackdropdisabled" id="deleteHeaderExpModal" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to delete selected <strong>Components</strong>?</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="deleteHeaderExpConfirm" class="btn btn-primary" >Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve/Reject Modal -->
    @include('procurement.inspection.partials.approve-modal', ['id' => $mrn->id])

    {{-- Taxes --}}
    @include('procurement.inspection.partials.tax-detail-modal')

    {{-- Amendment Modal --}}
    <div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>GRN</strong>? After Amendment this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <script type="text/javascript">
        var actionUrlTax = '{{route("inspection.tax.calculation")}}';
    </script>
    <script type="text/javascript" src="{{asset('assets/js/modules/common-attr-ui.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/modules/inspection.js')}}"></script>
    <script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
    <script>
        @if($mrn->document_status != 'draft')
            $(':input').prop('readonly', true);
            $('select').not('.amendmentselect select').prop('disabled', true);
            $("#deleteBtn").remove();
            $("#addNewItemBtn").remove();
            $(".editAddressBtn").remove();
            $(document).on('show.bs.modal', function (e) {
                if(e.target.id != 'approveModal') {
                    $(e.target).find('.modal-footer').remove();
                    $('select').not('.amendmentselect select').prop('disabled', true);
                }
                if(e.target.id == 'approveModal') {
                    $(e.target).find(':input').prop('readonly', false);
                    $(e.target).find('select').prop('readonly', false);
                }
                $('.add-contactpeontxt').remove();
                let text = $(e.target).find('thead tr:first th:last').text();
                if(text.includes("Action")){
                    $(e.target).find('thead tr').each(function() {
                        $(this).find('th:last').remove();
                    });
                    $(e.target).find('tbody tr').each(function() {
                        $(this).find('td:last').remove();
                    });
                }
            });
        @endif


        /*Vendor drop down*/
        function initializeAutocomplete1(selector, type) {
            $(selector).autocomplete({
                minLength: 0,
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
                select: function(event, ui) {
                    var $input = $(this);
                    var itemName = ui.item.value;
                    var itemId = ui.item.id;
                    var itemCode = ui.item.code;
                    $input.attr('data-name', itemName);
                    $input.val(itemName);
                    $("#vendor_id").val(itemId);
                    $("#vendor_code").val(itemCode);
                    vendorOnChange(itemId);
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
        function vendorOnChange(vendorId) {
            let store_id = $("[name='header_store_id']").val() || '';
            let actionUrl = "{{route('inspection.get.address')}}"+'?id=' + vendorId+'&store_id='+store_id;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if(data.data?.currency_exchange?.status == false) {
                        $("#vendor_name").val('');
                        $("#vendor_id").val('');
                        $("#vendor_code").val('');
                        $("#hidden_state_id").val('');
                        $("#hidden_country_id").val('');
                        // $("#vendor_id").trigger('blur');
                        $("select[name='currency_id']").empty().append('<option value="">Select</option>');
                        $("select[name='payment_term_id']").empty().append('<option value="">Select</option>');
                        $(".shipping_detail").text('-');
                        $(".billing_detail").text('-');
                        Swal.fire({
                            title: 'Error!',
                            text: data.data?.currency_exchange.message,
                            icon: 'error',
                        });
                        return false;
                    }
                    if(data.status == 200) {
                    $("#vendor_name").val(data?.data?.vendor?.company_name);
                    $("#vendor_id").val(data?.data?.vendor?.id);
                    $("#vendor_code").val(data?.data?.vendor.vendor_code);
                    let curOption = `<option value="${data.data.currency.id}">${data.data.currency.name}</option>`;
                    let termOption = `<option value="${data.data.paymentTerm.id}">${data.data.paymentTerm.name}</option>`;
                    $('[name="currency_id"]').empty().append(curOption);
                    $('[name="payment_term_id"]').empty().append(termOption);
                    $("#shipping_id").val(data.data.shipping.id);
                    $("#billing_id").val(data.data.billing.id);
                    // $(".shipping_detail").text(data.data.shipping.display_address);
                    $(".billing_detail").text(data.data.billing.display_address);
                    $(".delivery_address").text(data.data.delivery_address);
                    $(".org_address").text(data.data.org_address);

                    $("#hidden_state_id").val(data.data.shipping.state.id);
                    $("#hidden_country_id").val(data.data.shipping.country.id);
                    } else {
                        if(data.data.error_message) {
                            $("#vendor_name").val('');
                            $("#vendor_id").val('');
                            $("#vendor_code").val('');
                            $("#hidden_state_id").val('');
                            $("#hidden_country_id").val('');
                            // $("#vendor_id").trigger('blur');
                            $("select[name='currency_id']").empty().append('<option value="">Select</option>');
                            $("select[name='payment_term_id']").empty().append('<option value="">Select</option>');
                            // $(".shipping_detail").text('-');
                            $(".billing_detail").text('-');
                            Swal.fire({
                                title: 'Error!',
                                text: data.data.error_message,
                                icon: 'error',
                            });
                            return false;
                        }
                    }
                });
            });
        }

        /*Add New Row*/
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
                            type:'goods_item_list',
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
                                    is_attr:item.item_attributes_count,
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
                    let closestTr = $input.closest('tr');
                    closestTr.find('[name*=item_id]').val(itemId);
                    closestTr.find('[name*=item_code]').val(itemCode);
                    closestTr.find('[name*=item_name]').val(itemN);
                    closestTr.find('[name*=hsn_id]').val(hsnId);
                    closestTr.find('[name*=hsn_code]').val(hsnCode);
                    let uomOption = `<option value=${uomId}>${uomName}</option>`;
                    if(ui.item?.alternate_u_o_ms) {
                        for(let alterItem of ui.item.alternate_u_o_ms) {
                        uomOption += `<option value="${alterItem.uom_id}" ${alterItem.is_purchasing ? 'selected' : ''}>${alterItem.uom?.name}</option>`;
                        }
                    }
                    closestTr.find('[name*=uom_id]').append(uomOption);
                    closestTr.find('.attributeBtn').trigger('click');
                    let price = 0;
                    let transactionType = 'collection';
                    let partyCountryId = $("#hidden_country_id").val();
                    let partyStateId = $("#hidden_state_id").val();
                    let rowCount = Number(closestTr.attr('data-index'));
                    let queryParams = new URLSearchParams({
                        price: price,
                        item_id: itemId,
                        transaction_type: transactionType,
                        party_country_id: partyCountryId,
                        party_state_id: partyStateId,
                        rowCount : rowCount
                    }).toString();
                    getItemDetail(closestTr);
                    // taxHidden(queryParams);
                    setTimeout(() => {
                        if(ui.item.is_attr) {
                            $input.closest('tr').find('.attributeBtn').trigger('click');
                        } else {
                            $input.closest('tr').find('[name*="[accepted_qty]"]').focus();
                        }
                    }, 100);
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
        initializeAutocomplete2(".comp_item_code");
        $(document).on('click','#addNewItemBtn', (e) => {
            // for component item code
            var supplierName = $('#vendor_name').val();
            if(!supplierName){
                Swal.fire(
                    "Warning!",
                    "Please select vendor first!",
                    "warning"
                );
                return false;
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

                if($("tr[id*='row_']:last").find("[name*='[attr_group_id]']").length == 0 && item_id) {
                    lastTrObj.attr_require = false;
                }
            }

            let actionUrl = '{{route("inspection.item.row")}}'+'?count='+rowsLength+'&component_item='+JSON.stringify(lastTrObj);
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
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'Someting went wrong!',
                            icon: 'error',
                        });
                    }
                });
            });
        });

        function taxHidden(queryParams)
        {
            let actionUrl = '{{route("inspection.tax.calculation")}}';
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
            let editItemIds = [];
            let poItemIds = [];
            $('.form-check-input:checked').each(function(index, item) {
                let tr = $(item).closest('tr');
                let trIndex = tr.index();
                let po_detail_id = Number($(tr).find('[name*="[po_detail_id]"]').val()) || 0;
                let mrn_detail_id = Number($(tr).find('[name*="[mrn_detail_id]"]').val()) || 0;
                if (po_detail_id > 0 && mrn_detail_id > 0) {
                    poItemIds.push({ index: trIndex + 1, po_detail_id: po_detail_id });
                }
            });
            // if (poItemIds.length) {
            //     e.preventDefault();
            //     let rowNumbers = poItemIds.map(item => item.index).join(", ");
            //     Swal.fire({
            //         title: 'Error!',
            //         text: `You cannot delete mrn item(s) at row(s): ${rowNumbers}`,
            //         icon: 'error',
            //     });
            //     return false;
            // }

            $('#itemTable > tbody .form-check-input').each(function() {
                if ($(this).is(":checked")) {
                    if($(this).attr('data-id')) {
                        editItemIds.push($(this).attr('data-id'));
                    } else {
                        itemIds.push($(this).val());
                    }
                }
            });
            if (itemIds.length) {
                itemIds.forEach(function(item,index) {
                    $(`#row_${item}`).remove();
                });
            }
            if(editItemIds.length == 0 && itemIds.length == 0) {
                Swal.fire({
                    title: 'Error!',
                    text: "Please first add & select row item.",
                    icon: 'error',
                });
                return false;
            }
            if (editItemIds.length) {
                $("#deleteComponentModal").find("#deleteConfirm").attr('data-ids',JSON.stringify(editItemIds));
                $("#deleteComponentModal").modal('show');
            }
            if(!$("tr[id*='row_']").length) {
                $("#itemTable > thead .form-check-input").prop('checked',false);
                // $(".prSelect").prop('disabled',false);
            }
            setTableCalculation();
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
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select first item name.',
                    icon: 'error',
                });
                return false;
            }
        });

        /*For comp attr*/
        function getItemAttribute(itemId, rowCount, selectedAttr, tr){
            let actionUrl = '{{route("inspection.item.attr")}}'+'?item_id='+itemId+`&rowCount=${rowCount}&selectedAttr=${selectedAttr}`;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        $("#attribute tbody").empty();
                        $("#attribute table tbody").append(data.data.html)
                        $(tr).find('td:nth-child(2)').find("[name*=attr_name]").remove();
                        $(tr).find('td:nth-child(2)').append(data.data.hiddenHtml);
                        if (data.data.attr) {
                            $("#attribute").modal('show');
                            $(".select2").select2();
                        }
                    }
                });
            });
        }

        /*Display item detail*/
        $(document).on('change focus', '#itemTable tr input ', function(e){
            let currentTr = e.target.closest('tr');
            getItemDetail(currentTr);
        });

        function getItemDetail(currentTr) {
            let pName = $(currentTr).find("[name*='component_item_name']").val();
            let itemId = $(currentTr).find("[name*='item_id']").val();
            let poHeaderId = $(currentTr).find("[name*='purchase_order_id']").val();
            let poDetailId = $(currentTr).find("[name*='po_detail_id']").val();
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
                let uomId = $(currentTr).find("[name*='[uom_id]']").val() || '';
                let qtyElement = $(currentTr).find("[name*='[accepted_qty]']");  // Get the jQuery object, not the value
                let qty = qtyElement.val() || '';  // Get the value of the accepted_qty input

                let itemStoreData = JSON.parse($(currentTr).find("[id*='components_stores_data']").val() || "[]");
                let headerId = $(currentTr).find("[name*='mrn_header_id']").val() ?? '';
                let detailId = $(currentTr).find("[name*='mrn_detail_id']").val() ?? '';

                let actionUrl = '{{route("inspection.get.itemdetail")}}' +
                    '?item_id=' + itemId +
                    '&purchase_order_id=' + poHeaderId +
                    '&po_detail_id=' + poDetailId +
                    '&selectedAttr=' + JSON.stringify(selectedAttr) +
                    '&itemStoreData=' + JSON.stringify(itemStoreData) +
                    '&remark=' + remark +
                    '&uom_id=' + uomId +
                    '&qty=' + qty +
                    '&headerId=' + headerId +
                    '&detailId=' + detailId;

                fetch(actionUrl).then(response => {
                    return response.json().then(data => {
                        if (data.status == 200) {
                            $("#itemDetailDisplay").html(data.data.html);
                        }
                    });
                });
            }
        }

        // Event listener for Edit Address button click
        $(document).on('click', '.editAddressBtn', (e) => {
            let addressType = $(e.target).closest('a').attr('data-type');
            let vendorId = $("#vendor_id").val();
            let onChange = 0;
            let addressId = addressType === 'shipping' ? $("#shipping_id").val() : $("#billing_id").val();
            let actionUrl = `{{route("inspection.edit.address")}}?type=${addressType}&vendor_id=${vendorId}&address_id=${addressId}&onChange=${onChange}`;
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
            if (!addressId) {
                $("#country_id").val('').trigger('change');
                $("#state_id").val('').trigger('change');
                $("#city_id").val('').trigger('change');
                $("#pincode").val('');
                $("#address").val('');
                return false;
            }
            let onChange = 1;
            let actionUrl = `{{route("inspection.edit.address")}}?type=${addressType}&vendor_id=${vendorId}&address_id=${addressId}&onChange=${onChange}`;
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
            $("#pincode").val(selectedAddress.pincode);
            $("#address").val(selectedAddress.address);
        }

        /* Address Submit */
        $(document).on('click', '.submitAddress', function (e) {
            $('.ajax-validation-error-span').remove();
            e.preventDefault();
            var innerFormData = new FormData();
            $('#edit-address').find('input,textarea,select').each(function () {
                innerFormData.append($(this).attr('name'), $(this).val());
            });
            var method = "POST" ;
            var url = '{{route("inspection.address.save")}}';
            fetch(url, {
                method: method,
                body: innerFormData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                return response.json();
            })
            .then(data => {
                if(data.status == 200) {
                    let addressType = $("#address_type").val();
                    if(addressType == 'shipping') {
                        $("#shipping_id").val(data.data.new_address.id);
                        $(".shipping_detail").text(data.data.new_address.display_address);
                    } else {
                        $("#billing_id").val(data.data.new_address.id);
                        $(".billing_detail").text(data.data.new_address.display_address);
                    }
                    $("#edit-address").modal('hide');
                } else {
                    let formObj = $("#edit-address");
                    let errors = data.errors;
                    for (const [key, errorMessages] of Object.entries(errors)) {
                        var name = key.replace(/\./g, "][").replace(/\]$/, "");
                        formObj.find(`[name="${name}"]`).parent().append(
                            `<span class="ajax-validation-error-span form-label text-danger" style="font-size:12px">${errorMessages[0]}</span>`
                        );
                    }
                }
            })
            .catch(error => {
                console.error('Form submission error:', error);
            });

        });

        /*Delete server side rows*/
        $(document).on('click','#deleteConfirm', (e) => {
            let ids = e.target.getAttribute('data-ids');
            ids = JSON.parse(ids);
            localStorage.setItem('deletedMrnItemIds', JSON.stringify(ids));
            $("#deleteComponentModal").modal('hide');

            if(ids.length) {
                ids.forEach((id,index) => {
                    $(`.form-check-input[data-id='${id}']`).closest('tr').remove();
                });
            }
            setTableCalculation();
            if(!$("#itemTable [id*=row_]").length) {
                $("th .form-check-input").prop('checked',false);
                $('#vendor_name').prop('readonly',false);
                $("#editBillingAddressBtn").show();
                $("#editShippingAddressBtn").show();
            }
        });

        // addDeliveryScheduleBtn
        $(document).on('click', '.addDeliveryScheduleBtn', (e) => {
            let rowCount = e.target.closest('div').getAttribute('data-row-count');
            $('#store-row-id').val(rowCount);
            let qty = Number($("#itemTable #row_"+rowCount).find("[name*='[accepted_qty]']").val());
            if(!qty) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please enter quanity then you can add store location.',
                    icon: 'error',
                });
                return false;
            }

            $("#deliveryScheduleModal").find("#row_count").val(rowCount);
            let rowHtml = '';
            let curDate = new Date().toISOString().split('T')[0];
            if(!$("#itemTable #row_"+rowCount).find("[name*='[store_qty]']").length) {

                let rowHtml = `<tr class="display_delivery_row">
                                    <td>1</td>
                                    <td>
                                        <input type="hidden" name="row_count" value="${rowCount}" id="row_count">
                                        <select class="form-select mw-100 select2 item_store_code" id="erp_store_id_1" name="components[${rowCount}][erp_store][1][erp_store_id]" data-id="1">
                                        <option value="">Select</option>
                                        @foreach ($erpStores as $key => $val)<option value="{{ $val->id }}" {{ $key === 0 ? 'selected' : '' }}>{{ $val->store_code }}</option>@endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100 select2 item_rack_code" id="erp_rack_id_1" name="components[${rowCount}][erp_store][1][erp_rack_id]" data-id="1">
                                        <option value="">Select</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100 select2 item_shelf_code" id="erp_shelf_id_1" name="components[${rowCount}][erp_store][1][erp_shelf_id]" data-id="1">
                                        <option value="">Select</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100 select2 item_bin_code" id="erp_bin_id_1" name="components[${rowCount}][erp_store][1][erp_bin_id]" data-id="1">
                                        <option value="">Select</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="components[${rowCount}][erp_store][1][store_qty]" id="store_qty_1" class="form-control mw-100" value="${qty}"  data-id="1" />
                                    <td>
                                    <a data-row-count="${rowCount}" data-index="1" href="javascript:;" class="text-danger deleteItemDeliveryRow"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></a>
                                </td>
                                </tr>`;
                $("#deliveryScheduleModal").find('.display_delivery_row').remove();
                $("#deliveryScheduleModal").find('#deliveryFooter').before(rowHtml);
                $('[name="components[1][erp_store][1][erp_store_id]"').trigger('change');
            } else {
                if($("#itemTable #row_"+rowCount).find("[name*=store_qty]").length) {
                    $(".display_delivery_row").remove(); // Remove all rows if present
                } else {
                    // Remove all rows except the first one, and reset the quantity
                    $('.display_delivery_row').not(':first').remove();
                    $(".display_delivery_row").find("[name*=store_qty]").val('');
                }

                // Iterate over each store_qty field to build dynamic rows
                $("#itemTable #row_" + rowCount).find("[name*=store_qty]").each(function(index, item) {
                    let storeVal = $(item).closest('td').find(`[name="components[${rowCount}][erp_store][${index+1}][erp_store_id]"]`).val();
                    let rackVal = $(item).closest('td').find(`[name="components[${rowCount}][erp_store][${index+1}][erp_rack_id]"]`).val();
                    let shelfVal = $(item).closest('td').find(`[name="components[${rowCount}][erp_store][${index+1}][erp_shelf_id]"]`).val();
                    let binVal = $(item).closest('td').find(`[name="components[${rowCount}][erp_store][${index+1}][erp_bin_id]"]`).val();
                    let storeQty = $(item).closest('td').find(`[name="components[${rowCount}][erp_store][${index+1}][store_qty]"]`).val();
                    // console.log('store rack shelf', storeVal, rackVal, shelfVal, binVal);
                    // Trigger the change event after setting values to ensure racks, shelves, etc. are updated
                    $(`#erp_store_id_${index+1}`).val(storeVal).trigger('change');
                    $(`#erp_rack_id_${index+1}`).val(rackVal);
                    $(`#erp_shelf_id_${index+1}`).val(shelfVal);
                    $(`#erp_bin_id_${index+1}`).val(binVal);

                    // Generate HTML for the new row with dynamic data
                    rowHtml += `<tr class="display_delivery_row">
                                    <td>${index + 1}</td>
                                    <td>
                                        <input type="hidden" name="row_count" value="${rowCount}" id="row_count">
                                        <select class="form-select mw-100 select2 item_store_code" id="erp_store_id_${index+1}" name="components[${rowCount}][erp_store][${index+1}][erp_store_id]" data-id="${index+1}">
                                            @foreach ($erpStores as $key => $val)<option value="{{ $val->id }}" ${storeVal == '{{ $val->id }}' ? 'selected' : ''}>{{ $val->store_code }}</option>@endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100 select2 item_rack_code" id="erp_rack_id_${index+1}" name="components[${rowCount}][erp_store][${index+1}][erp_rack_id]" data-id="${index+1}">
                                            <!-- Dynamically populated racks -->
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100 select2 item_shelf_code" id="erp_shelf_id_${index+1}" name="components[${rowCount}][erp_store][${index+1}][erp_shelf_id]" data-id="${index+1}">
                                            <!-- Dynamically populated shelves -->
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100 select2 item_bin_code" id="erp_bin_id_${index+1}" name="components[${rowCount}][erp_store][${index+1}][erp_bin_id]" data-id="${index+1}">
                                            <!-- Dynamically populated bins -->
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="components[${rowCount}][erp_store][${index+1}][store_qty]" id="store_qty_${index+1}" class="form-control mw-100" value="${storeQty}" data-id="${index+1}" />
                                    </td>
                                    <td>
                                        <a data-row-count="${rowCount}" data-index="${index+1}" href="javascript:;" class="text-danger deleteItemDeliveryRow">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2">
                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                                <line x1="14" y1="11" x2="14" y2="17"></line>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>`;
                });

                // Append the dynamically created rows
                $("#deliveryScheduleTable").find('#deliveryFooter').before(rowHtml);

                // Trigger change event to re-populate dependent dropdowns after the rows are added
                $("#itemTable #row_" + rowCount).find("[name*=store_qty]").each(function(index, item) {
                    $(`#erp_store_id_${index+1}`).trigger('change');
                });
            }
            $("#deliveryScheduleTable").find('#deliveryFooter #total').attr('qty',qty);
            $("#deliveryScheduleModal").modal('show');
            totalScheduleQty();
        });

        /*Total delivery schedule qty*/
        function totalScheduleQty()
        {
            let total = 0.00;
            $("#deliveryScheduleTable [name*='[store_qty]']").each(function(index, item) {
                total = total + Number($(item).val());
            });
            $("#deliveryFooter #total").text(total.toFixed(2));
        }

        // addTaxItemRow add row
        $(document).on('click', '.addTaxItemRow', (e) => {
            let rowCount = $('#deliveryScheduleModal .display_delivery_row').find('#row_count').val();
            let qty = 0.00;
            $("#deliveryScheduleTable [name*='[store_qty]']").each(function(index, item) {
                qty = qty + Number($(item).val());
            });
            if(!qty) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please enter quanity then you can add new row.',
                    icon: 'error',
                });
                return false;
            }

            if(!$("#deliveryScheduleTable [name*='[store_qty]']:last").val()) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please enter quanity then you can add new row.',
                    icon: 'error',
                });
                return false;
            }

            let itemQty = Number($('#deliveryScheduleModal #deliveryFooter #total').attr('qty'));
            if (qty > itemQty) {
                Swal.fire({
                    title: 'Error!',
                    text: 'You cannot add more than the available item quantity.',
                    icon: 'error',
                });
                return false;
            }
            if(qty != itemQty) {
                let tblRowCount = $('#deliveryScheduleModal .display_delivery_row').length + 1;
                let rowHtml = `<tr class="display_delivery_row">
                                    <td>${tblRowCount}</td>
                                    <td>
                                        <input type="hidden" name="row_count" value="${rowCount}" id="row_count">
                                        <select class="form-select mw-100 select2 item_store_code" id="erp_store_id_${tblRowCount}" name="components[${rowCount}][erp_store][${tblRowCount}][erp_store_id]"  data-id="${tblRowCount}">
                                        @foreach ($erpStores as $key => $val)<option value="{{ $val->id }}" {{ $key === 0 ? 'selected' : '' }}>{{ $val->store_code }}</option>@endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100 select2 item_rack_code" id="erp_rack_id_${tblRowCount}" name="components[${rowCount}][erp_store][${tblRowCount}][erp_rack_id]"  data-id="${tblRowCount}">
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100 select2 item_shelf_code" id="erp_shelf_id_${tblRowCount}" name="components[${rowCount}][erp_store][${tblRowCount}][erp_shelf_id]"  data-id="${tblRowCount}">
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-select mw-100 select2 item_bin_code" id="erp_bin_id_${tblRowCount}" name="components[${rowCount}][erp_store][${tblRowCount}][erp_bin_id]"  data-id="${tblRowCount}">
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" name="components[${rowCount}][erp_store][${tblRowCount}][store_qty]" id="store_qty_${tblRowCount}" class="form-control mw-100" data-id="${tblRowCount}" />
                                    <td>
                                    <a data-row-count="${rowCount}" data-index="${tblRowCount}" href="javascript:;" class="text-danger deleteItemDeliveryRow"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg></a>
                                </td>
                                </tr>`;
                $("#deliveryScheduleModal").find('.display_delivery_row:last').after(rowHtml);
                $('#erp_store_id_'+tblRowCount).trigger('change');
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: 'Qunatity not available.',
                    icon: 'error',
                });
                return false;
            }
            totalScheduleQty();
        });

        /*itemDeliveryScheduleSubmit */
        $(document).on('click', '.itemDeliveryScheduleSubmit', (e) => {
            let rowCount = $('#deliveryScheduleModal .display_delivery_row').find('#row_count').val();
            // console.log(rowCount);
            let hiddenHtml = '';
            $("#deliveryScheduleTable .display_delivery_row").each(function(index,item){
                let storeId = $(item).find("[name*='erp_store_id']").val();
                let rackId = $(item).find("[name*='erp_rack_id']").val();
                let shelfId = $(item).find("[name*='erp_shelf_id']").val();
                let binId = $(item).find("[name*='erp_bin_id']").val();
                let dQty =  $(item).find("[name*='store_qty']").val();
                hiddenHtml +=   `<input type="hidden" value="${storeId}" name="components[${rowCount}][erp_store][${index+1}][erp_store_id]"/>
                                <input type="hidden" value="${rackId}" name="components[${rowCount}][erp_store][${index+1}][erp_rack_id]"/>
                                <input type="hidden" value="${shelfId}" name="components[${rowCount}][erp_store][${index+1}][erp_shelf_id]"/>
                                <input type="hidden" value="${binId}" name="components[${rowCount}][erp_store][${index+1}][erp_bin_id]"/>
                                <input type="hidden" value="${dQty}" name="components[${rowCount}][erp_store][${index+1}][store_qty]"/>`;

            });
            $("#itemTable #row_"+rowCount).find("[name*='erp_store_id']").remove();
            $("#itemTable #row_"+rowCount).find("[name*='erp_rack_id']").remove();
            $("#itemTable #row_"+rowCount).find("[name*='erp_shelf_id']").remove();
            $("#itemTable #row_"+rowCount).find("[name*='erp_bin_id']").remove();
            $("#itemTable #row_"+rowCount).find("[name*='store_qty']").remove();
            $("#itemTable #row_"+rowCount).find(".addDeliveryScheduleBtn").before(hiddenHtml);
            $("#deliveryScheduleModal").modal('hide');
        });

        /*Remove delivery row*/
        $(document).on('click', '.deleteItemDeliveryRow', (e) => {
            if($(e.target).closest('tbody').find('.display_delivery_row').length ==1) {
                Swal.fire({
                    title: 'Error!',
                    text: 'You cannot first row.',
                    icon: 'error',
                });
                return false;
            }
            $(e.target).closest('tr').remove();
            totalScheduleQty();
        });

        /*Delivery qty on input*/
        $(document).on('change input', '.display_delivery_row [name*="store_qty"]', (e) => {
            let itemQty = Number($('#deliveryScheduleModal #deliveryFooter #total').attr('qty'));
            let inputQty = 0;
            let remainingQty = itemQty;
            $('.display_delivery_row [name*="store_qty"]').each(function(index, item) {
                inputQty = inputQty + Number($(item).val());
                if (inputQty > itemQty) {
                    Swal.fire({
                        title: 'Error!',
                        text: 'You cannot add more than the available item quantity.',
                        icon: 'error',
                    });
                    $(item).val(remainingQty);
                    return false;
                }
                remainingQty = remainingQty - Number($(item).val());
            });
            totalScheduleQty();
        });

        $(document).on('change', '.item_store_code', function() {
            var rowKey = $(this).data('id');
            var store_code_id = $(this).val();
            // console.log('rowKey', rowKey);
            $('#erp_store_id_'+rowKey).val(store_code_id).select2();

            var data = {
                store_code_id: store_code_id
            };

            $.ajax({
                type: 'POST',
                data: data,
                url: '/material-receipts/get-store-racks',
                success: function(data) {
                    $('#erp_rack_id_'+rowKey).empty();
                    // $('#erp_rack_id_'+rowKey).append('<option value="">Select</option>');
                    $.each(data.storeRacks, function(key, value) {
                        $('#erp_rack_id_'+rowKey).append('<option value="'+ key +'">'+ value +'</option>');
                    });
                    $('#erp_rack_id_'+rowKey).trigger('change');

                    $('#erp_bin_id_'+rowKey).empty();
                    // $('#erp_bin_id_'+rowKey).append('<option value="">Select</option>');
                    $.each(data.storeBins, function(key, value) {
                        $('#erp_bin_id_'+rowKey).append('<option value="'+ key +'">'+ value +'</option>');
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
                url: '/material-receipts/get-rack-shelfs',
                success: function(data) {
                    $('#erp_shelf_id_'+rowKey).empty();
                    // $('#erp_shelf_id_'+rowKey).append('<option value="">Select</option>');
                    $.each(data.storeShelfs, function(key, value) {
                        $('#erp_shelf_id_'+rowKey).append('<option value="'+ key +'">'+ value +'</option>');
                    });

                    $('#erp_shelf_id_'+rowKey).trigger('change');
                }
            });
        });

        /*Remove item level discount*/
        $(document).on("click", ".deleteItemDiscountRow", (e) => {
            let rowCount = e.target.closest('a').getAttribute('data-row-count') || 0;
            let rowIndex = $(e.target).closest('tr').index();
            let id = e.target.closest('a').getAttribute('data-id') || 0;
            if(Number(id)) {
                $("#deleteItemDiscModal").find("#deleteItemDiscConfirm").attr('data-id', id);
                $("#deleteItemDiscModal").find("#deleteItemDiscConfirm").attr('data-row-index', rowIndex);
                $("#deleteItemDiscModal").find("#deleteItemDiscConfirm").attr('data-row-count', rowCount);
                $("#deleteItemDiscModal").modal('show');
            }
        });

        /*Delete server side rows*/
        $(document).on('click','#deleteItemDiscConfirm', (e) => {
            let rowCount = e.target.getAttribute('data-row-count') || $("#disItemFooter #row_count").val();
            let id = e.target.getAttribute('data-id');
            let dataRowId = Number(e.target.getAttribute('data-row-index'));
            $("#deleteItemDiscModal").modal('hide');
            $(`.display_discount_row`).find(`[value="${id}"]`).closest('tr').remove();
            let ids = JSON.parse(localStorage.getItem('deletedItemDiscTedIds')) || [];
            if (!ids.includes(id)) {
                ids.push(id);
            }
            localStorage.setItem('deletedItemDiscTedIds', JSON.stringify(ids));

            let total_head_dis = 0;
            $("[name*='[item_d_amnt]']").each(function(index,item) {
                total_head_dis+=Number($(item).val());
            });
            $("#disItemFooter #total").text(total_head_dis.toFixed(2));
            $(`[id*='row_${rowCount}']`).find("[name*='[discounts]'").remove();

            let hiddenDis = '';
            let totalAmnt = 0;
            $(".display_discount_row").find("[name*='[item_d_amnt]']").each(function(index,item) {
                let key = index + 1;
                let id = $(item).closest('tr').find(`[name*="[item_d_id]"]`).val();
                let name = $(item).closest('tr').find(`[name*="[item_d_name]"]`).val();
                let perc = $(item).closest('tr').find(`[name*="[item_d_perc]"]`).val();
                let amnt = $(item).val();
                totalAmnt+=Number(amnt);
                hiddenDis+= `<input type="hidden" value="${id}" name="components[${rowCount}][discounts][${index+1}][id]">
                <input type="hidden" value="${name}" name="components[${rowCount}][discounts][${index+1}][dis_name]">
                <input type="hidden" value="${perc}" name="components[${rowCount}][discounts][${index+1}][dis_perc]">
                <input type="hidden" value="${amnt}" name="components[${rowCount}][discounts][${index+1}][dis_amount]">`;
            });
            $(`[name*="components[${rowCount}][discount_amount]"]`).val(totalAmnt);
            $(`[name*="components[${rowCount}][discount_amount]"]`).after(hiddenDis);
            setTableCalculation();
        });

        /*Remove item level discount*/
        $(document).on("click", ".deleteSummaryDiscountRow", (e) => {
            let id = $(e.target.closest('tr')).find('[name*="[d_id]"]').val();
            const rowIndex = $(e.target).closest('tr').index();
            if(id) {
                $("#deleteHeaderDiscModal").find("#deleteHeaderDiscConfirm").attr('data-id', id);
                $("#deleteHeaderDiscModal").find("#deleteHeaderDiscConfirm").attr('data-row-index', rowIndex);
                $("#deleteHeaderDiscModal").modal('show');
            }
        });

        /*Delete server side rows*/
        $(document).on('click','#deleteHeaderDiscConfirm', (e) => {
            let id = e.target.getAttribute('data-id');
            let dataRowId = e.target.getAttribute('data-row-index');
            $("#deleteHeaderDiscModal").modal('hide');
            $(`.display_summary_discount_row:eq(${dataRowId})`).remove();
            let ids = JSON.parse(localStorage.getItem('deletedHeaderDiscTedIds')) || [];
            if (!ids.includes(id)) {
                ids.push(id);
            }
            localStorage.setItem('deletedHeaderDiscTedIds', JSON.stringify(ids));
            let itemValue = Number($("#TotalEachRowAmount").attr('amount'));
            $('.display_summary_discount_row [name*="[d_perc]"]').each(function(index, item) {
                let perc = Number($(item).val());
                if(perc) {
                    let disAmount = itemValue * Number(perc) / 100;
                    $(item).closest('tr').find("[name*='[d_amnt]']").prop('readonly', true).val(disAmount.toFixed(2));
                } else {
                    $(item).closest('tr').find("[name*='[d_amnt]']").prop('readonly', false).val('');
                }
            });
            summaryDisTotal();
            setTableCalculation();
        });


        /*Remove header level exp*/
        $(document).on("click", ".deleteExpRow", (e) => {
            let id = $(e.target.closest('tr')).find('[name*="[e_id]"]').val();
            const rowIndex = $(e.target).closest('tr').index();
            if(id) {
                $("#deleteHeaderExpModal").find("#deleteHeaderExpConfirm").attr('data-id', id);
                $("#deleteHeaderExpModal").find("#deleteHeaderExpConfirm").attr('data-row-index', rowIndex);
                $("#deleteHeaderExpModal").modal('show');
            }
        });

        /*Delete server side rows*/
        $(document).on('click','#deleteHeaderExpConfirm', (e) => {
            let id = e.target.getAttribute('data-id');
            let dataRowId = e.target.getAttribute('data-row-index');
            $("#deleteHeaderExpModal").modal('hide');
            $(`.display_summary_exp_row:eq(${dataRowId})`).remove();

            let ids = JSON.parse(localStorage.getItem('deletedHeaderExpTedIds')) || [];
            if (!ids.includes(id)) {
                ids.push(id);
            }
            localStorage.setItem('deletedHeaderExpTedIds', JSON.stringify(ids));

            let totalPerc = 100;
            let itemValue = Number($("#f_total_after_tax").attr('amount'));
            $('.display_summary_exp_row [name*="[e_perc]"]').each(function(index, item) {
                let perc = Number($(item).val());
                if(perc) {
                    let total = itemValue * perc / 100;
                    $(item).closest('tr').find('[name*="e_amnt"]').prop('readonly',true).val(total.toFixed(2));
                } else {
                    $(item).closest('tr').find('[name*="e_amnt"]').prop('readonly',false).val('');
                }
            });

            summaryExpTotal();
            setTableCalculation();
        });

        /*Amendment modal open*/
        $(document).on('click', '.amendmentBtn', (e) => {
            $("#amendmentconfirm").modal('show');
        });

        $(document).on('click', '#amendmentSubmit', (e) => {
            let actionUrl = "{{ route('inspection.amendment.submit', $mrn->id) }}";
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error'
                        });
                    }
                    location.reload();
                });
            });
        });

        // # Revision Number On Chage
        $(document).on('change', '#revisionNumber', (e) => {
            let actionUrl = location.pathname + '?revisionNumber='+e.target.value;
            let revision_number = Number("{{$revision_number}}");
            let revisionNumber = Number(e.target.value);
            if(revision_number == revisionNumber) {
                location.href = actionUrl;
            } else {
                window.open(actionUrl, '_blank');
            }
        });

        /*Open amendment popup*/
        $(document).on('click', '#amendmentBtn', (e) => {
            $("#amendmentModal").modal('show');
        });

        /*Amendment btn submit*/
        $(document).on('click', '#amendmentBtnSubmit', (e) => {
            let remark = $("#amendmentModal").find('[name="amend_remarks"]').val();
            if(!remark) {
                e.preventDefault();
                $("#amendRemarkError").removeClass("d-none");
                return false;
            } else {
                $("#amendmentModal").modal('hide');
                $("#amendRemarkError").addClass("d-none");
                e.preventDefault();
                $("#mrnEditForm").submit();
            }
        });
        setTimeout(() => {
            $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
                let currentIndex = index + 1;
                setAttributesUIHelper(currentIndex,"#itemTable");
            });
        },100);
    </script>
@endsection
