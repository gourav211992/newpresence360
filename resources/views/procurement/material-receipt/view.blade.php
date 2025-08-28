@extends('layouts.app')
@section('styles')
    <style>
        .tooltip-inner { text-align: left}
    </style>
@endsection
@section('content')
    @php
        $routeName = $servicesBooks['services'][0]->alias ??  "material-receipt";
        $routeAlias = ($routeName && ($routeName == 'mrn')) ? 'material-receipt' : $routeName;
        $routeRedirect = ($routeAlias && ($routeAlias == 'material-receipt')) ? 'material-receipts' : $routeAlias;
    @endphp
    <form id="mrnEditForm" data-module="mrn" class="ajax-input-form" method="POST" action="{{ route('material-receipt.update', $mrn->id) }}" data-redirect="/{{$routeRedirect}}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="tax_required" id="tax_required" value="">
        <input type="hidden" name="inspection_required" id="inspection_required" class="inspection_required" value="">
        <div class="app-content content ">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">
                                        {{$servicesBooks['services'][0]->name ?? "GRN"}}
                                    </h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="/">Home</a>
                                            </li>
                                            <li class="breadcrumb-item active">Edit</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <input type="hidden" name="document_status" value="{{$mrn->document_status}}" id="document_status">
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
                                                <span class="badge rounded-pill badge-light-{{$mrn->display_status === 'Posted' ? 'info' : 'secondary'}} forminnerstatus">
                                                    <span class = "text-dark" >Status</span> : <span class="{{$docStatusClass}}">{{$mrn->display_status}}</span>
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
                                                        <input type="text" class="form-control document_number" readonly value="{{@$mrn->document_number}}" id="document_number">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Document Date <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" id="document_date" name="document_date" class="form-control document_date" value="{{ date('Y-m-d', strtotime($mrn->document_date)) }}" >
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
                                                        <label class="form-label">Store <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select sub_store" id="sub_store_id" name="sub_store_id">
                                                            <option value="{{$mrn->sub_store_id}}" data-warehouse-required="{{$mrn?->is_warehouse_required}}">
                                                                {{ ucfirst($mrn?->erpSubStore?->name) }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                    <input type="hidden" class="is_warehouse_required" name="is_warehouse_required" id="is_warehouse_required" value="{{$mrn?->is_warehouse_required}}">
                                                </div>
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
                                                            <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct vendor_name" id="vendor_name" name="vendor_name" {{(count(($mrn->items)) > 0 ? 'readonly' : '')}} value="{{@$mrn->vendor->company_name}}" />
                                                            <input type="hidden" value="{{@$mrn->vendor_id}}" id="vendor_id" name="vendor_id" />
                                                            <input type="hidden" value="{{@$mrn->vendor_code}}" id="vendor_code" name="vendor_code" />
                                                            @if($mrn->latestShippingAddress() || $mrn->latestBillingAddress())
                                                                <input type="hidden" value="{{$mrn->latestShippingAddress()}}" id="shipping_id" name="shipping_id" />
                                                                <input type="hidden" id="billing_id" value="{{$mrn->latestBillingAddress()->id}}" name="billing_id" />
                                                                <input type="hidden" value="{{$mrn->latestBillingAddress()->state?->id}}" id="hidden_state_id" name="hidden_state_id" />
                                                                <input type="hidden" value="{{$mrn->latestBillingAddress()->country?->id}}" id="hidden_country_id" name="hidden_country_id" />
                                                            @else
                                                                <input type="hidden" value="{{$mrn->ship_to}}" id="shipping_id" name="shipping_id" />
                                                                <input type="hidden" id="billing_id" value="{{$mrn->billing_to}}" name="billing_id" />
                                                                <input type="hidden" value="{{$mrn?->billingAddress?->state?->id}}" id="hidden_state_id" name="hidden_state_id" />
                                                                <input type="hidden" value="{{$mrn?->billingAddress?->country?->id}}" id="hidden_country_id" name="hidden_country_id" />
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="currency_id" disabled>
                                                                <option value="{{@$mrn->currency_id}}">{{@$mrn->currency->name}}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Payment Terms <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="payment_term_id" disabled>
                                                                <option value="{{@$mrn->payment_term_id}}">{{@$mrn->paymentTerm->name}}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Credit Days <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" class="form-control mw-100"
                                                                id="credit_days" name="credit_days"
                                                                value="{{ @$mrn->credit_days }}" readonly/>
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
                                                    @if($mrn->cost_center_id !== null)
                                                        <div class="col-md-3" id="cost_center_div" style="display:none;">
                                                            <div class="mb-1">
                                                                <label class="form-label">Cost Center <span class="text-danger">*</span></label>
                                                                <select class="form-select cost_center" id="cost_center_id" name="cost_center_id">
                                                                </select>
                                                            </div>
                                                        </div>
                                                    @endif
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">LOT No </label>
                                                            <input type="text" name="lot_number" value="{{@$mrn->lot_number}}" class="form-control" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Gate Entry No.
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="text" name="gate_entry_no"
                                                                class="form-control gate_entry_no" value="{{@$mrn->gate_entry_no}}"
                                                                placeholder="Enter Gate Entry no" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Gate Entry Date
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="date" name="gate_entry_date" value="{{date('Y-m-d', strtotime($mrn->gate_entry_date))}}"
                                                                class="form-control gate-entry gate_entry_date" id="datepicker2"
                                                                placeholder="Enter Gate Entry Date" readonly>
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
                                                                class="form-control supplier_invoice_no"
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
                                                                class="form-control gate-entry supplier_invoice_date" id="datepicker3"
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
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Manual Entry No.
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="text" name="manual_entry_no"
                                                                class="form-control" value="{{@$mrn->manual_entry_no}}"
                                                                placeholder="Enter Manual Entry no">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 {{(isset($mrn) && count($mrn -> dynamic_fields)) > 0 ? '' : 'd-none'}}">
                                    @if (isset($dynamicFieldsUI))
                                        {!! $dynamicFieldsUI !!}
                                    @endif
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
                                                    <table id="itemTable" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad itemTable"
                                                        data-json-key="components_json"
                                                        data-row-selector="tr[id^='row_']">
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
                                                                <th class="text-end">
                                                                    {{ $mrn->reference_type == 'po' ? 'PO Qty' :
                                                                    ($mrn->reference_type == 'jo' ? 'JO Qty' : 'Qty') }}
                                                                </th>
                                                                <th class="text-end">Recpt Qty</th>
                                                                <th class="text-end">Acpt. Qty</th>
                                                                <th class="text-end">Rej. Qty</th>
                                                                <th class="text-end" id="rateHeader">Rate</th>
                                                                <th class="text-end">Value</th>
                                                                <th>Discount</th>
                                                                <th class="text-end">Total</th>
                                                                <th width="50px">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                            @include('procurement.material-receipt.partials.item-row-edit')
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="totalsubheadpodetail">
                                                                <td colspan="10"></td>
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
                                                                <td rowspan="10" colspan="9">
                                                                    <table class="table border">
                                                                        <tbody id="itemDetailDisplay">
                                                                            <tr>
                                                                                <td class="p-0">
                                                                                    <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                            </tr>
                                                                            <tr>
                                                                            </tr>
                                                                            <tr>
                                                                            </tr>
                                                                            <tr>
                                                                            </tr>
                                                                            <tr>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                                <td colspan="5">
                                                                    <table class="table border mrnsummarynewsty">
                                                                        <tr>
                                                                            <td colspan="2" class="p-0">
                                                                                <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between">
                                                                                    <strong>Document Summary</strong>
                                                                                    <div class="addmendisexpbtn">
                                                                                        <button type="button" class="btn p-25 btn-sm btn-outline-secondary summaryTaxBtn">{{-- <i data-feather="plus"></i> --}} Tax</button>
                                                                                        <button type="button" class="btn p-25 btn-sm btn-outline-secondary summaryDisBtn"><i data-feather="plus"></i> Discount</button>
                                                                                        <button type="button" class="btn p-25 btn-sm btn-outline-secondary summaryExpBtn"><i data-feather="plus"></i> Expenses</button>
                                                                                    </div>
                                                                                </h6>
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="totalsubheadpodetail">
                                                                            <td width="55%"><strong>Sub Total</strong></td>
                                                                            <td class="text-end" id="f_sub_total">
                                                                                <!-- {{ number_format(@$mrn->total_item_amount, 2) }} -->
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Item Discount</strong></td>
                                                                            <td class="text-end" id="f_total_discount">
                                                                                <!-- {{ number_format(@$mrn->item_discount, 2) }} -->
                                                                            </td>
                                                                        </tr>
                                                                        @if($mrn->headerDiscount)
                                                                            <tr id="f_header_discount_hidden">
                                                                                <td><strong>Header Discount</strong></td>
                                                                                <td class="text-end" id="f_header_discount">
                                                                                    {{$mrn->headerDiscount()->sum('ted_amount')}}
                                                                                </td>
                                                                            </tr>
                                                                        @else
                                                                            <tr class="d-none" id="f_header_discount_hidden">
                                                                                <td><strong>Header Discount</strong></td>
                                                                                <td class="text-end" id="f_header_discount">0.00</td>
                                                                            </tr>
                                                                        @endif
                                                                        <tr class="totalsubheadpodetail">
                                                                            <td><strong>Taxable Value</strong></td>
                                                                            <td class="text-end" id="f_taxable_value" amount="">
                                                                                <!-- {{ number_format(@$mrn->taxable_amount, 2) }} -->
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Tax</strong></td>
                                                                            <td class="text-end" id="f_tax">
                                                                                <!-- {{ number_format(@$mrn->total_taxes, 2) }}         -->
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="totalsubheadpodetail">
                                                                            <td><strong>Total After Tax</strong></td>
                                                                            <td class="text-end" id="f_total_after_tax">
                                                                                <!-- {{ number_format(@$mrn->total_after_tax_amount, 2) }} -->
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Exp.</strong></td>
                                                                            <td class="text-end" id="f_exp">
                                                                                <!-- {{ number_format(@$mrn->expense_amount, 2) }} -->
                                                                            </td>
                                                                            <input type="hidden" name="expense_amount" class="text-end" id="expense_amount" value="{{$mrn->expense_amount}}">
                                                                        </tr>
                                                                        <tr class="voucher-tab-foot">
                                                                            <td class="text-primary"><strong>Total After Exp.</strong></td>
                                                                            <td>
                                                                                <div class="quottotal-bg justify-content-end">
                                                                                    <h5 id="f_total_after_exp">
                                                                                        <!-- {{ number_format(@$mrn->total_amount, 2) }} -->
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
        @include('procurement.material-receipt.partials.summary-disc-modal')
        {{-- Add expenses modal--}}
        @include('procurement.material-receipt.partials.summary-exp-modal')
        {{-- Asset Detail Modal --}}
        @include('procurement.material-receipt.partials.asset-detail-modal')
        {{-- Item Batch --}}
        @include('procurement.material-receipt.partials.item-batch-modal')
        {{-- Edit Address --}}
        <div class="modal fade" id="edit-address" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
            </div>
        </div>
        @include('procurement.material-receipt.partials.amendement-modal', ['id' => $mrn->id])
    </form>
    {{-- Item upload modal --}}
    @include('partials.import-item-modal')
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
                                    <label class="form-label">Type<span class="text-danger">*</span></label>
                                    <input type="text" id="new_item_dis_name_select" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                    <input type = "hidden" id = "new_item_discount_id" />
                                    <input type = "hidden" id = "new_item_dis_name" />
                                </td>
                                <td>
                                    <label class="form-label">Percentage <span class="text-danger">*</span></label>
                                    <input step="any" type="number" id="new_item_dis_perc" class="form-control mw-100" />
                                </td>
                                <td>
                                    <label class="form-label">Value <span class="text-danger">*</span></label>
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
                    {{--
                    <p class="text-center">Enter the details below.</p>
                    --}}
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
    @include('procurement.material-receipt.partials.item-location-modal')
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
    @include('procurement.material-receipt.partials.approve-modal', ['id' => $mrn->id])

    {{-- Taxes --}}
    @include('procurement.material-receipt.partials.tax-detail-modal')

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
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>Document</strong>? After Amendment this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- GL Posting Modal -->
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
									<tbody id="posting-table"></tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
				<div class="text-end">
					<button style="margin: 1%;" onclick = "postVoucher(this);" id="posting_button" type = "button" class="btn btn-primary btn-sm waves-effect waves-float waves-light">Submit</button>
				</div>
			</div>
		</div>
	</div>
    {{-- Storage Points --}}
    @include('procurement.material-receipt.partials.storage-point-modal')
    {{-- Add Outstanding PO modal --}}
    @include('procurement.material-receipt.partials.outstanding-po-modal')
    {{-- Add Outstanding JO modal --}}
    @include('procurement.material-receipt.partials.outstanding-jo-modal')
    {{-- Add Outstanding JO modal --}}
    @include('procurement.material-receipt.partials.outstanding-so-modal')
    <!-- Close Deviation Modal -->
    <div class="modal fade" id="deviateModal" tabindex="-1" aria-labelledby="deviateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <form class="ajax-input-form"
                    method="POST"
                    action="{{ route('document.approval.material-receipt') }}"
                    data-redirect="{{ route('material-receipt.index') }}"
                    enctype="multipart/form-data">

                    @csrf
                    <input type="hidden" name="action_type" id="action_type">
                    <input type="hidden" name="closing_job_id" id="closing_job_id" value="{{ $mrn->deviationJob?->id ?? '' }}">
                    <input type="hidden" name="id" value="{{ $mrn->id ?? '' }}">

                    <!-- Modal Header -->
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-bold" id="deviateModalLabel">
                            <i class="bi bi-exclamation-triangle me-2"></i>Putaway Deviation
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <!-- Modal Body -->
                    <div class="modal-body px-4 py-3">
                        <div class="row text-center mb-4">
                            <div class="col">
                                <div class="bg-light rounded p-1 border">
                                    <h6 class="mb-1 text-secondary">Total Packets</h6>
                                    <h5 class="mb-0 fw-bold text-dark">{{ $itemUniqueCodes['total_unique_codes'] }}</h5>
                                </div>
                            </div>
                            <div class="col">
                                <div class="bg-light rounded p-1 border">
                                    <h6 class="mb-1 text-secondary">Scanned Packets</h6>
                                    <h5 class="mb-0 fw-bold text-dark">{{ $itemUniqueCodes['scanned_unique_codes'] }}</h5>
                                </div>
                            </div>
                            <div class="col">
                                <div class="bg-light rounded p-1 border">
                                    <h6 class="mb-1 text-secondary">Deviation</h6>
                                    <h5 class="mb-0 fw-bold {{ ($itemUniqueCodes['pending_unique_codes'] > 0) ? 'text-danger' : 'text-dark' }}">{{ $itemUniqueCodes['pending_unique_codes'] }}</h5>
                                </div>
                            </div>
                            <!-- <div id="deviation-batch-table-wrap" class="mt-3"></div>
                            <input type="hidden" name="deviation_breakup_json" id="deviation_breakup_json"> -->
                        </div>
                        <div class="mb-3">
                            <label for="remarks" class="form-label fw-semibold text-dark">Remarks</label>
                            <textarea maxlength="250" name="closing_remarks" id="remarks" class="form-control" rows="4"
                                    placeholder="Enter your remarks here..."></textarea>
                            <!-- <div class="form-text text-muted">Max 250 characters</div> -->
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="modal-footer border-0 justify-content-center pb-4">
                        <button type="button" class="btn btn-outline-secondary px-4" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary px-5">
                            Close Deviation
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script type="text/javascript" src="{{asset('assets/js/modules/common-attr-ui.js')}}"></script>
    <script type="text/javascript">
        var actionUrlTax = '{{route("material-receipt.tax.calculation")}}';
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script type="text/javascript" src="{{asset('assets/js/modules/common-datatable.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/modules/mrn.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/modules/asset-registration.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/modules/item-batch.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/modules/import-item.js')}}"></script>
    <script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
    <script>
        selectedCostCenterId = @json($mrn->cost_center_id);
        let currentProcessType = @json($mrn->reference_type);
        var qtyChangeUrl = '{{ route("material-receipt.get.validate-quantity") }}';
        let taxCalUrl = '{{ route('tax.group.calculate') }}';

        let currentIndex = '';

        if(currentProcessType == 'jo')
        {
            document.getElementById('rateHeader').textContent = 'Service Charge';
            $("#addNewItemBtn").hide();
            $("#importItem").hide();
        }
        if(currentProcessType == 'po')
        {
            document.getElementById('rateHeader').textContent = 'Rate';
            $("#addNewItemBtn").hide();
            $("#importItem").hide();
        }
        if(currentProcessType == 'so')
        {
            document.getElementById('rateHeader').textContent = 'Rate';
            $("#addNewItemBtn").hide();
            $("#importItem").hide();
        }

        let tableRowCount = 0;
        /*Clear local storage*/
        setTimeout(() => {
            localStorage.removeItem('deletedItemDiscTedIds');
            localStorage.removeItem('deletedHeaderDiscTedIds');
            localStorage.removeItem('deletedHeaderExpTedIds');
            localStorage.removeItem('deletedItemLocationIds');
            localStorage.removeItem('deletedMrnItemIds');
        },0);
        @if($subStoreCount > 0)
            // Set colspan to 9
            $("td.dynamic-colspan").attr("colspan", 10);
            $("td.dynamic-summary-colspan").attr("colspan", 10);
        @else
            // Set colspan to 8
            $("td.dynamic-colspan").attr("colspan", 9);
            $("td.dynamic-summary-colspan").attr("colspan", 9);
        @endif

        let header_ids = @json($headerIds);
        let details_ids = @json($detailsIds);
        let asn_header_ids = @json($asnHeaderIds);
        let asn_details_ids = @json($asnDetailsIds);
        let ge_header_ids = @json($geHeaderIds);
        let ge_details_ids = @json($geDetailsIds);
        let exist_payment_term_id = @json($existPaymentTermId);
        let exist_credit_days = @json($existCreditDays);

        @if($buttons['amend'] && intval(request('amendment') ?? 0))

        @else
            @if(($mrn->document_status != 'draft') && ($mrn->document_status != 'rejected'))
                $(':input').prop('readonly', true);
                $('textarea[name="amend_remark"], textarea[name="closing_remarks"], input[type="file"][name="amend_attachment[]"]').prop('readonly', false).prop('disabled', false);
                $('select').not('.amendmentselect select').prop('disabled', true);
                $("#deleteBtn").remove();
                $("#addNewItemBtn").remove();
                $(".editAddressBtn").remove();
                $("#add_new_item_dis").remove();
                $(".deleteItemDiscountRow").remove();
                $("#add_new_head_dis").remove();
                $(".deleteSummaryDiscountRow").remove();
                $("#add_new_head_exp").remove();
                $(".deleteExpRow").remove();
                $('a.add-batch-row-header, button.add-batch-row-header').remove();
                $('a.remove-batch-row, button.remove-batch-row, .delete-batch-row-header').remove();

                $(document).on('show.bs.modal', function (e) {
                    if(e.target.id != 'approveModal') {
                        if(e.target.id != 'deviateModal') {
                            $(e.target).find('.modal-footer').remove();
                        }
                        $('select').not('.amendmentselect select').prop('disabled', true);
                    }
                    if(e.target.id == 'approveModal') {
                        $(e.target).find(':input').prop('readonly', false);
                        $(e.target).find('select').prop('readonly', false);
                    }
                    // $('.add-contactpeontxt').remove();
                    // let text = $(e.target).find('thead tr:first th:last').text();
                    // if(text.includes("Action")){
                    //     $(e.target).find('thead tr').each(function() {
                    //         $(this).find('th:last').remove();
                    //     });
                    //     $(e.target).find('tbody tr').each(function() {
                    //         $(this).find('td:last').remove();
                    //     });
                    // }
                });
            @endif
        @endif

        // Change BookId
        $(document).on('change','#book_id', (e) => {
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
            let document_date = $("[name='document_date']").val();
            let storeId = $("[name='header_store_id']").val();
            let subStoreId = $("[name='sub_store_id']").val();
            let actionUrl = '{{route("book.get.doc_no_and_parameters")}}'+'?book_id='+bookId+'&document_date='+document_date;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        const parameters = data.data.parameters;
                        setServiceParameters(parameters);

                        if(parameters?.tax_required.some(val => val.toLowerCase() === 'yes')) {
                            $("#tax_required").val(parameters?.tax_required[0]);
                        } else {
                            $("#tax_required").val("");
                        }
                        setTableCalculation(true);
                        // checkWarehouseSetup(storeId, subStoreId);
                        $(".inspection_required").val(parameters?.inspection_required[0]);
                    }
                    if(data.status == 404) {
                        $("#book_code").val('');
                        $("#document_number").val('');
                        $("#tax_required").val("");
                        const docDateInput = $("[name='document_date']");
                        docDateInput.removeAttr('min');
                        docDateInput.removeAttr('max');
                        docDateInput.val(new Date().toISOString().split('T')[0]);
                    }
                });
            });
        }

        /*for trigger on edit cases*/
        setTimeout(() => {
            let bookId = $("#book_id").val();
            getDocNumberByBookId(bookId);
        },1000);

        /*Set Service Parameter*/
        function setServiceParameters(parameters) {
            /*Date Validation*/
            const docDateInput = $("[name='document_date']");
            let isFeature = false;
            let isPast = false;
            if (parameters.future_date_allowed && parameters.future_date_allowed.includes('yes')) {
                let futureDate = new Date();
                futureDate.setDate(futureDate.getDate() /*+ (parameters.future_date_days || 1)*/);
                // docDateInput.val(futureDate.toISOString().split('T')[0]);
                docDateInput.attr("min", new Date().toISOString().split('T')[0]);
                isFeature = true;
            } else {
                isFeature = false;
                docDateInput.attr("max", new Date().toISOString().split('T')[0]);
            }
            if (parameters.back_date_allowed && parameters.back_date_allowed.includes('yes')) {
                let backDate = new Date();
                backDate.setDate(backDate.getDate() /*- (parameters.back_date_days || 1)*/);
                // docDateInput.val(backDate.toISOString().split('T')[0]);
                // docDateInput.attr("max", "");
                isPast = true;
            } else {
                isPast = false;
                docDateInput.attr("min", new Date().toISOString().split('T')[0]);
            }
            /*Date Validation*/
            if(isFeature && isPast) {
                docDateInput.removeAttr('min');
                docDateInput.removeAttr('max');
            }

        /*Reference from*/
        }

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
            let actionUrl = "{{route('material-receipt.get.address')}}"+'?id=' + vendorId+'&store_id='+store_id;
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
                    // let termOption = `<option value="${data.data.paymentTerm.id}">${data.data.paymentTerm.name}</option>`;
                    $('[name="currency_id"]').empty().append(curOption);
                    // $('[name="payment_term_id"]').empty().append(termOption);
                    $("#shipping_id").val(data.data.shipping.id);
                    $("#billing_id").val(data.data.billing.id);
                    // $(".shipping_detail").text(data.data.shipping.display_address);
                    $(".billing_detail").text(data.data.billing.display_address);
                    $(".delivery_address").text(data.data.delivery_address);
                    $(".org_address").text(data.data.org_address);

                    $("#hidden_state_id").val(data.data.vendor_address.state.id);
                    $("#hidden_country_id").val(data.data.vendor_address.country.id);
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
                                    is_inspection:item.is_inspection,
                                    uom_name:item.uom?.name,
                                    uom_id:item.uom_id,
                                    hsn_id:item.hsn?.id,
                                    hsn_code:item.hsn?.code,
                                    alternate_u_o_ms:item.alternate_u_o_ms,
                                    is_attr:item.item_attributes_count,
                                    is_asset: item.is_asset,
                                    asset_name:item.item_name,
                                    asset_category_id: item.asset_category_id,
                                    asset_category_name: item.asset_category?.name,
                                    brand_name: item.brand_name,
                                    model_no: item.model_no,
                                    estimated_life: item.expected_life,
                                    salvage_percentage: item.getSalvagePercentage ?? 0,
                                    procurement_type: 'BUY',
                                    is_batch_number: item.is_batch_no,
                                    is_expiry : item.is_expiry,
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
                    let isInspection = ui.item.is_inspection;
                    let batchNumber = ui.item.is_batch_number;
                    let expiry = ui.item.is_expiry;
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
                    closestTr.find("td[id*='itemAttribute_']").html(defautAttrBtn);
                    let uomOption = `<option value=${uomId}>${uomName}</option>`;
                    if(ui.item?.alternate_u_o_ms) {
                        for(let alterItem of ui.item.alternate_u_o_ms) {
                        uomOption += `<option value="${alterItem.uom_id}" ${alterItem.is_purchasing ? 'selected' : ''}>${alterItem.uom?.name}</option>`;
                        }
                    }
                    closestTr.find('[name*=uom_id]').append(uomOption);
                    closestTr.find('.attributeBtn').trigger('click');
                    if (ui.item.is_asset === 1) {
                        const assetPayload = {
                            asset_id: null,
                            asset_name: ui.item.asset_name ?? '',
                            asset_category_id: ui.item.asset_category_id ?? null,
                            asset_category_name: ui.item.asset_category_name ?? '',
                            asset_code: null,
                            brand_name: ui.item.brand_name ?? '',
                            model_no: ui.item.model_no ?? '',
                            estimated_life: ui.item.estimated_life ?? '',
                            salvage_percentage: ui.item.salvage_percentage ?? 0,
                            salvage_value: ui.item.salvage_percentage ?? 0,
                            procurement_type: ui.item.procurement_type ?? null,
                            capitalization_date: new Date().toISOString().split('T')[0]
                        };


                        closestTr.find('[name*="[assetDetailData]"]').val(JSON.stringify(assetPayload));
                        closestTr.find('.assetDetailBtn')
                            .removeClass('d-none')
                            .attr('data-asset', JSON.stringify(assetPayload));
                    } else {
                        closestTr.find('[name*="[assetDetailData]"]').val('');
                        closestTr.find('.assetDetailBtn')
                            .addClass('d-none')
                            .removeAttr('data-asset');
                    }

                    closestTr.find('.addBatchBtn').attr('data-is-batch-number', batchNumber);
                    closestTr.find('.addBatchBtn').attr('data-is-expiry', expiry);
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
                    let storeLocation = $('.header_store_id').val();
                    getSubStores(storeLocation, itemId);
                    setTimeout(() => {
                        if(ui.item.is_attr) {
                            $input.closest('tr').find('.attributeBtn').trigger('click');
                        } else {
                            $input.closest('tr').find('.attributeBtn').trigger('click');
                            $input.closest('tr').find('[name*="[order_qty]"]').focus();
                        }
                    }, 100);
                    initializeStationAutocomplete();
                    getItemCostPrice($input.closest('tr'));
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
            let storeLocation = $('.header_store_id').val();
            getSubStores(storeLocation);
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
                        focusAndScrollToLastRowInput();
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
            setTableCalculation(true);
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
                let rowCount = tr.getAttribute('data-index');
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
            let checkAttr = 0;
            if(currentProcessType && currentProcessType != null)
            {
                rowCount = tableRowCount;
                let isPo = $(tr).find('[name*="purchase_order_item_id"]').val() ? 1 : 0;
                let isJo = $(tr).find('[name*="job_order_item_id"]').val() ? 1 : 0;
                if((!isPo) || (!isJo)) {
                    if($(tr).find('td[id*="itemAttribute_"]').data('disabled')) {
                        checkAttr = 1;
                    }
                }
            }
            let mrn_detail_id = $(tr).find("input[name*='[mrn_detail_id]']").val() || '';
            let actionUrl = '{{route("material-receipt.item.attr")}}'+'?item_id='+itemId+'&mrn_detail_id='+mrn_detail_id+`&rowCount=${rowCount}&selectedAttr=${selectedAttr}&checkAttr=${checkAttr}`;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        $("#attribute tbody").empty();
                        $("#attribute table tbody").append(data.data.html)
                        $(tr).find('td:nth-child(2)').find("[name*=attr_name]").remove();
                        $(tr).find('td:nth-child(2)').append(data.data.hiddenHtml);
                        $(tr).find("td[id*='itemAttribute_']").attr('attribute-array', JSON.stringify(data.data.itemAttributeArray));
                        if (data.data.attr) {
                            $("#attribute").modal('show');
                            $(".select2").select2();
                        }
                        initAttributeAutocomplete();
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
            const getVal = (selector) => {
                let el = $(currentTr).find(selector);
                return el.length ? el.val() : '';
            };

            let itemId = getVal("[name*='[item_id]']");
            if (!itemId) return;

            let selectedAttr = [];
            $(currentTr).find("[name*='[attr_name]']").each(function () {
                const val = $(this).val();
                if (val) selectedAttr.push(val);
            });

            let data = {
                item_id: itemId,
                purchase_order_id: getVal("[name*='[purchase_order_id]']"),
                po_detail_id: getVal("[name*='[po_detail_id]']"),
                job_order_id: getVal("[name*='[job_order_id]']"),
                jo_detail_id: getVal("[name*='[jo_detail_id]']"),
                store_id : $('.header_store_id').val(),
                sub_store_id : $('.sub_store').val(),
                remark: getVal("[name*='[remark]']"),
                uom_id: getVal("[name*='[uom_id]']"),
                qty: getVal("[name*='[order_qty]']"),
                headerId: getVal("[name*='[mrn_header_id]']"),
                detailId: getVal("[name*='[mrn_detail_id]']"),
                selectedAttr: JSON.stringify(selectedAttr),
                itemStoreData: JSON.parse(getVal("[id*='components_stores_data']") || "[]"),
                type: currentProcessType,
            };

            let actionUrl = '{{ route('material-receipt.get.itemdetail') }}?' + new URLSearchParams(data).toString();

            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        // Update the modal or display section
                        $("#itemDetailDisplay").html(data.data.html);
                        applyInspectionState();

                        var approvedStockLedger = data.data.checkApprovedQuantity;
                        if(approvedStockLedger)
                        {
                            if ((approvedStockLedger['code'] == 200) && (approvedStockLedger['status'] == 'error')) {
                            let approved_stock = approvedStockLedger['approvedStock'];
                            let receipt_qty = $(currentTr).find("[name*='[order_qty]']").val() || '';
                            let rejQtyElement = $(currentTr).find("[name*='[rejected_qty]']");  // Get the jQuery object, not the value
                            let rejQty = rejQtyElement.val() || '';  // Get the value of the rejected_qty input
                            if (qty < approved_stock) {
                                if (qtyElement.length > 0) {  // Ensure the element was found
                                    qtyElement.val(receipt_qty);  // Set the value of qtyElement (jQuery object)
                                    rejQtyElement.val(0.00);  // Set the value of qtyElement (jQuery object)
                                } else {
                                    Swal.fire({
                                        title: 'Error!',
                                        text: "Accepted quantity input not found",
                                        icon: 'error',
                                    });
                                }
                            } else {
                                Swal.fire({
                                    title: 'Error!',
                                    text: "Accepted quantity is higher than the approved stock.",
                                    icon: 'error',
                                });
                            }
                            }
                        }

                    }
                });
            });
        }

        // Event listener for Edit Address button click
        $(document).on('click', '.editAddressBtn', (e) => {
            let addressType = $(e.target).closest('a').attr('data-type');
            let vendorId = $("#vendor_id").val();
            let onChange = 0;
            let addressId = addressType === 'shipping' ? $("#shipping_id").val() : $("#billing_id").val();
            let actionUrl = `{{route("material-receipt.edit.address")}}?type=${addressType}&vendor_id=${vendorId}&address_id=${addressId}&onChange=${onChange}`;
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
            let actionUrl = `{{route("material-receipt.edit.address")}}?type=${addressType}&vendor_id=${vendorId}&address_id=${addressId}&onChange=${onChange}`;
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
            var url = '{{route("material-receipt.address.save")}}';
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
        $(document).on('click', '#deleteConfirm', (e) => {
            let ids = e.target.getAttribute('data-ids');
            ids = JSON.parse(ids);
            localStorage.setItem('deletedMrnItemIds', JSON.stringify(ids));
            $("#deleteComponentModal").modal('hide');

            if (ids.length) {
                ids.forEach((id, index) => {
                    $(`.form-check-input[data-id='${id}']`).closest('tr').remove();
                    // if (['po', 'jo', 'so'].includes(currentProcessType)) {
                    //     localStorage.removeItem(`selected${currentProcessType.charAt(0).toUpperCase() + currentProcessType.slice(1)}Ids`, JSON.stringify(id));
                    // }
                });
            }

            setTableCalculation(true);

            if (!$("#itemTable [id*=row_]").length) {
                $(".joSelect").show();
                $(".poSelect").show();
                $(".soSelect").show();
                $(".supplier_invoice_no").prop('readonly', false);
                $(".supplier_invoice_date").prop('readonly', false);
                $("#reference_type_input").val('');
                $("th .form-check-input").prop('checked', false);
                $('#vendor_name').prop('readonly', false);
                $("#editBillingAddressBtn").show();
                $("#editShippingAddressBtn").show();
            }
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
            setTableCalculation(true);
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
            setTableCalculation(true);
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
            setTableCalculation(true);
        });

        /*Amendment modal open*/
        $(document).on('click', '.amendmentBtn', (e) => {
            $("#amendmentconfirm").modal('show');
        });

        $(document).on('click', '.deviation-button', (e) => {
            $("#deviateModal").modal('show');
        });

        $(document).on('click', '#amendmentSubmit', (e) => {
            let url = new URL(window.location.href);
            url.search = '';
            url.searchParams.set('amendment', 1);
            let amendmentUrl = url.toString();
            window.location.replace(amendmentUrl);
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

        function resetPostVoucher()
        {
            document.getElementById('voucher_doc_no').value = '';
            document.getElementById('voucher_date').value = '';
            document.getElementById('voucher_book_code').value = '';
            document.getElementById('voucher_currency').value = '';
            document.getElementById('posting-table').innerHTML = '';
            document.getElementById('posting_button').style.display = 'none';
        }

        function onPostVoucherOpen(type = "not_posted")
        {
            // resetPostVoucher();
            const apiURL = "{{route('material-receipt.posting.get')}}";
            let urlType = '';
            if(type == "not_posted"){
                urlType = "get";
            } else{
                urlType = "view";
            }
            $.ajax({
                url: apiURL + "?book_id=" + $("#book_id").val() + "&document_id=" + "{{isset($mrn) ? $mrn -> id : ''}}"  + "&type=" + urlType,
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

        function postVoucher(element)
        {
            Swal.fire({
            title: 'Are you sure?',
            text: "Note: you want to submit the details, after that you can not make any changes to that",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, post it!',
            cancelButtonText: 'Cancel'
            }).then((result) => {
            if (result.isConfirmed) {
            const bookId = "{{isset($mrn) ? $mrn -> book_id : ''}}";
            const documentId = "{{isset($mrn) ? $mrn -> id : ''}}";
            const postingApiUrl = "{{route('material-receipt.post')}}"
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
                        Swal.fire({
                            title: 'Error!',
                            text: 'Some internal error occured',
                            icon: 'error',
                        });
                    }
                });
            }
            }
            else{
                $('#postvoucher').modal('hide');
            }
            })
        }

        // Function to initialize the product section autocomplete
        function initializeStationAutocomplete() {
            $("[name*='store_section']").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: 'store'
                        },
                        success: function (data) {
                            const mappedData = $.map(data, function (item) {
                                return {
                                    id: item.id,
                                    label: item.store_code,
                                };
                            });
                            response(mappedData);

                        },
                        error: function (xhr) {
                            console.error('Error fetching data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                select: function (event, ui) {
                    $(this).val(ui.item.label);
                    $(this).closest('td').find("[name*='[store_id]']").val(ui.item.id);
                    $(this).closest('td').find("[name*='[erp_store_code]']").val(ui.item.label);
                    return false;
                },
                change: function (event, ui) {
                    if (!ui.item) {
                    $(this).val("");
                    $(this).closest('td').find("[name*='[store_id]']").val("");
                    $(this).closest('td').find("[name*='[erp_store_code]']").val("");
                    }
                },
                // appendTo: "#addSectionItemPopup"
            }).focus(function () {
                if (this.value === "") {
                    $(this).closest('td').find("[name*='[store_id]']").val("");
                    $(this).closest('td').find("[name*='[erp_store_code]']").val("");
                    $(this).autocomplete("search", "");
                }
            });
        }

        // Get Item Rate
        function getItemCostPrice(currentTr)
        {
            let vendorId = $("#vendor_id").val();
            let currencyId = $("select[name='currency_id']").val();
            let transactionDate = $("input[name='document_date']").val();
            let itemId = $(currentTr).find("input[name*='[item_id]']").val();
            let attributes = '';
            let uomId = $(currentTr).find("select[name*='[uom_id]']").val();
            let queryParams = new URLSearchParams({
                vendor_id: vendorId,
                currency_id: currencyId,
                transaction_date: transactionDate,
                item_id: itemId,
                attributes: attributes,
                uom_id: uomId
            });
            let actionUrl = '{{ route("items.get.cost") }}'+'?'+queryParams.toString();
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if(data.status == 200) {
                        let cost = data?.data?.cost || 0;
                        $(currentTr).find("input[name*='[rate]']").val(cost);
                    }
                });
            });
        }

        // Revoke Document
        $(document).on('click', '#revokeButton', (e) => {
            let actionUrl = '{{ route("material-receipt.revoke.document") }}'+ '?id='+'{{$mrn->id}}';
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if(data.status == 'error') {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                        });
                    } else {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success',
                        });
                    }
                    location.reload();
                });
            });
        });

        $(document).on('click', '.processImportedBtn', (e) => {
            function initializeAutocomplete2(selector, type) {
                $(selector).autocomplete({
                    minLength: 0,
                    source: function(request, response) {
                        let selectedAllItemIds = [];
                        $("#itemTable tbody [id*='row_']").each(function(index,item) {
                            if(Number($(item).find('[name*="[item_id]"]').val())) {
                                selectedAllItemIds.push(Number($(item).find('[name*="[item_id]"]').val()));
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
                        $input.closest('tr').find('[name*="[item_id]"]').val(itemId);
                        $input.closest('tr').find('[name*=item_code]').val(itemCode);
                        $input.closest('tr').find('[name*=item_name]').val(itemN);
                        $input.closest('tr').find('[name*=hsn_id]').val(hsnId);
                        $input.closest('tr').find('[name*=hsn_code]').val(hsnCode);
                        $input.val(itemCode);
                        let uomOption = `<option value=${uomId}>${uomName}</option>`;
                        if(ui.item?.alternate_u_o_ms) {
                            for(let alterItem of ui.item.alternate_u_o_ms) {
                            uomOption += `<option value="${alterItem.uom_id}" ${alterItem.is_purchasing ? 'selected' : ''}>${alterItem.uom?.name}</option>`;
                            }
                        }
                        $input.closest('tr').find('[name*=uom_id]').append(uomOption);
                        $input.closest('tr').find("input[name*='attr_group_id']").remove();
                        setTimeout(() => {
                            if(ui.item.is_attr) {
                                $input.closest('tr').find('.attributeBtn').trigger('click');
                            } else {
                                $input.closest('tr').find('.attributeBtn').trigger('click');
                                $input.closest('tr').find('[name*="[qty]"]').val('').focus();
                            }
                        }, 100);
                        getItemCostPrice($input.closest('tr'));
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

            let currencyId = $("select[name='currency_id']").val();
            let transactionDate = $("input[name='document_date']").val() || '';
            let actionUrl = '{{ route("material-receipt.process.import-item") }}';

            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if(data.status == 200) {
                        $(".header_store_id").prop('disabled', true);
                        initializeAutocomplete2(".comp_item_code");
                        $("#importItemModal").modal('hide');
                        $(".importItem").prop('disabled',true);
                        $(".poSelect").prop('disabled',true);
                        $("select[name='currency_id']").prop('disabled', true);
                        $("select[name='payment_term_id']").prop('disabled', true);
                        $("#vendor_name").prop('readonly',true);
                        $(".editAddressBtn").addClass('d-none');
                        if ($("#itemTable .mrntableselectexcel").find("tr[id*='row_']").length) {
                            $("#itemTable .mrntableselectexcel tr[id*='row_']:last").after(data.data.pos);
                        } else {
                            $("#itemTable .mrntableselectexcel").empty().append(data.data.pos);
                        }
                        let locationId = $("[name='header_store_id']").val();
                        // getLocation(locationId);
                        getSubStores(locationId, item='');
                        updateImportItemData(data.status);
                        setTimeout(() => {
                            setTableCalculation(true);
                        },500);

                    }
                    if(data.status == 422) {
                        updateImportItemData(data.status);
                        $(".editAddressBtn").removeClass('d-none');
                        $("#vendor_name").val('').prop('readonly',false);
                        $("#vendor_id").val('');
                        $("#vendor_code").val('');
                        $("#hidden_state_id").val('');
                        $("#hidden_country_id").val('');
                        $("select[name='currency_id']").empty().append('<option value="">Select</option>').prop('readonly',false);
                        $("select[name='payment_term_id']").empty().append('<option value="">Select</option>').prop('readonly',false);
                        $(".shipping_detail").text('-');
                        $(".billing_detail").text('-');
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                        });
                        return false;
                    }
                });
            });
        });

        $(document).on('click','td[id*="itemAttribute_"]', (e) => {
            let dataAttributes = $(e.target).attr('data-attributes');
            // dataAttributes = JSON.parse(dataAttributes);
            // dataAttributes.
        });

        /*Open Po model*/
        let poOrderTable;
        $(document).on('click', '.poSelect', (e) => {
            tableRowCount = $('.mrntableselectexcel tr').length;
            if(tableRowCount)
            {
                let poDetailIds = [];
                $(".mrntableselectexcel").find("tr[id^='row_']").each(function() {
                    let poDetailId = $(this).find("input[name*='[purchase_order_id]']").val();
                    if (poDetailId !== undefined) {
                        poDetailIds.push(poDetailId);
                    }
                });
                localStorage.setItem('selectedPoIds', JSON.stringify(poDetailIds));
            }
            $("#poModal").modal('show');
            currentProcessType='po';
            const tableSelector = '#poModal .po-order-detail';
            $(tableSelector).DataTable().clear().destroy();
            openPurchaseRequest();
            getPurchaseOrders();
            if ($(tableSelector).length) {
                if ($.fn.DataTable.isDataTable(tableSelector)) {
                    poOrderTable = $(tableSelector).DataTable();
                    poOrderTable.ajax.reload();
                    $('#poModal').off('change', '.po_item_checkbox').on('change', '.po_item_checkbox', function () {
                        const currentAsn = $(this).attr('data-current-asn');
                        const currentGe = $(this).attr('data-current-ge');
                        const isChecked = $(this).is(':checked');

                        // If data-current-asn is valid
                        if (currentAsn && currentAsn !== 'null') {
                            $(`.po_item_checkbox[data-current-asn="${currentAsn}"]`).prop('checked', isChecked);
                        }

                        // If data-current-ge is valid
                        if (currentGe && currentGe !== 'null') {
                            $(`.po_item_checkbox[data-current-ge="${currentGe}"]`).prop('checked', isChecked);
                        }
                    });
                }
            }
        });

        /*searchPiBtn*/
        $(document).on('click', '.searchPoBtn', (e) => {
            getPurchaseOrders();
        });

        $(document).on('click', '.searchJoBtn', (e) => {
            getJobOrders();
        });

        function getSelectedPoTypes()
        {
            let moduleTypes = [];
            $('.po_item_checkbox:checked').each(function() {
                moduleTypes.push($(this).attr('data-module')); // Corrected: Get attribute value instead of setting it
            });
            return moduleTypes;
        }

        function getSelectedPaymentTerms()
        {
            let paymentIds = [];
            let paymentTerms = [];
            let CreditDays = [];

            $('.po_item_checkbox:checked').each(function() {
                paymentIds.push($(this).attr('data-payment-id'));
                paymentTerms.push($(this).attr('data-payment-term'));
                CreditDays.push($(this).attr('data-credit-days'));
            });
            return {
                paymentIds: paymentIds,
                paymentTerms: paymentTerms,
                creditDays: CreditDays,
            };
        }

        function openPurchaseRequest()
        {
            initializeAutocompleteQt("vendor_code_input_qt", "vendor_id_qt_val", "vendor_list", "vendor_code", "company_name");
            initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "po_document_qt", "document_number", "");
            initializeAutocompleteQt("asn_no_input_qt", "asn_id_qt_val", "po_asn_document_qt", "document_number", "");
            initializeAutocompleteQt("ge_no_input_qt", "ge_id_qt_val", "po_ge_document_qt", "document_number", "");
            initializeAutocompleteQt("po_so_no_input_qt", "po_so_qt_val", "po_so_qt", "book_code", "document_number");
        }

        function initializeAutocompleteQt(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "") {
            let modalType = '#poModal';
            // if (currentProcessType == 'jo')
            //     modalType = '#joModal';
            $("#" + selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: typeVal,
                            vendor_id: $("#vendor_id_qt_val").val(),
                            header_book_id: $("#book_id").val(),
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                // return {
                                //     id: item.id,
                                //     label: `${item[labelKey1]} ${labelKey2 ? (item[labelKey2] ? '(' + item[labelKey2] + ')' : '') : ''}`,
                                //     code: item[labelKey1] || '',
                                // };
                                let label = '';

                                if ('document_number' in item && 'book_code' in item) {
                                    label = `${item.document_number}`;
                                } else if ('company_name' in item) {
                                    label = item.company_name;
                                }

                                return {
                                    id: item.id,
                                    label: label,
                                    code: item.book_code || item.vendor_code || '',
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });
                },
                appendTo: modalType,
                minLength: 0,
                select: function(event, ui) {
                    var $input = $(this);
                    $input.val(ui.item.label);
                    $("#" + selectorSibling).val(ui.item.id);
                    $('#poModal .po-order-detail').DataTable().ajax.reload();
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#" + selectorSibling).val("");
                        $('#poModal .po-order-detail').DataTable().ajax.reload();
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $('#poModal .po-order-detail').DataTable().ajax.reload();
                    $(this).autocomplete("search", "");
                }
            }).blur(function() {
                if (this.value === "") {
                    $("#" + selectorSibling).val("");
                    $('#poModal .po-order-detail').DataTable().ajax.reload();
                }
            });
        }

        window.onload = function () {
            currentProcessType = @json($mrn->reference_type);
            asnHeaderIds = @json($asnHeaderIds);
            asnDetailsIds = @json($asnDetailsIds);
            geHeaderIds = @json($geHeaderIds);
            geDetailsIds = @json($geDetailsIds);
            $("#reference_type_input").val(currentProcessType);
            $(".supplier_invoice_no").prop('readonly', false);
            $(".supplier_invoice_date").prop('readonly', false);
            if(asnHeaderIds.length || asnDetailsIds.length)
            {
                $(".supplier_invoice_no").prop('readonly', true);
                $(".supplier_invoice_date").prop('readonly', true);
            }
            if(currentProcessType === null)
            {
                $(".joSelect").hide();
                $(".poSelect").hide();
                $(".soSelect").hide();
            }
            else{
                if (currentProcessType === 'po') {
                    $(".joSelect").hide();
                    $(".poSelect").show();
                    $(".soSelect").hide();
                } else if (currentProcessType === 'jo') {
                    $(".joSelect").show();
                    $(".poSelect").hide();
                    $(".soSelect").hide();
                } else if (currentProcessType === 'so') {
                    $(".joSelect").hide();
                    $(".poSelect").hide();
                    $(".soSelect").show();
                }
            }

            ['selectedPoIds', 'selectedJoIds', 'selectedSoIds'].forEach(key => localStorage.removeItem(key));

            const ids = @json($detailsIds);

            if (['po', 'jo', 'so'].includes(currentProcessType)) {
                localStorage.setItem(`selected${currentProcessType.charAt(0).toUpperCase() + currentProcessType.slice(1)}Ids`, JSON.stringify(ids));
            }
        };


        function renderData(data) {
            return data ? data : '';
        }

        function getDynamicParams() {
            let document_date = '',
                header_book_id = '',
                series_id = '',
                document_number = '',
                asn_number = '',
                ge_number = '',
                item_id = '',
                vendor_id = '',
                store_id = '',
                so_id = '',
                item_search = '',
                selected_po_ids = '';
            if(currentProcessType === 'po')
            {
                let selectedPoIds = localStorage.getItem('selectedPoIds') ?? '[]';
                selectedPoIds = JSON.parse(selectedPoIds);
                selectedPoIds = encodeURIComponent(JSON.stringify(selectedPoIds));
                document_date = $("[name ='document_date']").val() || '',
                header_book_id = $("#book_id").val() || '',
                series_id = $("#book_id_qt_val").val() || '',
                document_number = $("#document_id_qt_val").val() || '',
                asn_number = $("#asn_id_qt_val").val() || '',
                ge_number = $("#ge_id_qt_val").val() || '',
                item_id = $("#item_id_qt_val").val() || '',
                vendor_id = $("#vendor_id_qt_val").val(),
                store_id = $(".header_store_id").val() || '',
                so_id = $("#po_so_qt_val").val() || '',
                item_search = $("#item_name_search").length ? $("#item_name_search").val() : '';
                selected_po_ids = encodeURIComponent(selectedPoIds)
            }
            if(currentProcessType === 'jo')
            {
                let selectedJoIds = localStorage.getItem('selectedJoIds') ?? '[]';
                selectedJoIds = JSON.parse(selectedJoIds);
                selectedJoIds = encodeURIComponent(JSON.stringify(selectedJoIds));
                document_date = $("[name='document_date']").val() || '',
                header_book_id = $("#book_id").val() || '',
                series_id = $("#book_id_qt_val").val() || '',
                document_number = $("#jo_document_id_qt_val").val() || '',
                asn_number = $("#jo_asn_id_qt_val").val() || '',
                ge_number = $("#jo_ge_id_qt_val").val() || '',
                item_id = $("#jo_item_id_qt_val").val() || '',
                vendor_id = $("#jo_vendor_id_qt_val").val(),
                store_id = $(".header_store_id").val() || '',
                so_id = $("#jo_so_qt_val").val() || '',
                item_search = $("#jo_item_name_search").length ? $("#jo_item_name_search").val() : '';
                selected_po_ids = encodeURIComponent(selectedJoIds)
            }
            if(currentProcessType === 'so')
            {
                let selectedSoIds = localStorage.getItem('selectedSoIds') ?? '[]';
                selectedSoIds = JSON.parse(selectedSoIds);
                selectedSoIds = encodeURIComponent(JSON.stringify(selectedSoIds));
                document_date = $("[name='document_date']").val() || '',
                header_book_id = $("#book_id").val() || '',
                series_id = $("#book_id_qt_val").val() || '',
                document_number = $("#so_document_id_qt_val").val() || '',
                asn_number = $("#so_asn_id_qt_val").val() || '',
                ge_number = $("#so_ge_id_qt_val").val() || '',
                item_id = $("#so_item_id_qt_val").val() || '',
                vendor_id = $("#so_vendor_id_qt_val").val(),
                store_id = $(".header_store_id").val() || '',
                so_id = $("#so_so_qt_val").val() || '',
                item_search = $("#so_item_name_search").length ? $("#so_item_name_search").val() : '';
                selected_po_ids = encodeURIComponent(selectedSoIds)
            }

            return {
                    document_date: document_date,
                    header_book_id: header_book_id,
                    series_id: series_id,
                    document_number: document_number,
                    asn_number: asn_number,
                    ge_number: ge_number,
                    item_id: item_id,
                    vendor_id: vendor_id,
                    store_id: store_id,
                    so_id: so_id,
                    item_search: item_search,
                    selected_po_ids: selected_po_ids,
                    header_ids: encodeURIComponent(header_ids),
                    details_ids: encodeURIComponent(details_ids),
                    asn_header_ids: encodeURIComponent(asn_header_ids),
                    asn_details_ids: encodeURIComponent(asn_details_ids),
                    ge_header_ids: encodeURIComponent(ge_header_ids),
                    ge_details_ids: encodeURIComponent(ge_details_ids)
                };
        }

        function getPurchaseOrders()
        {
            const ajaxUrl = '{{ route("material-receipt.get.po", ["type" => "edit"]) }}';
            var columns = [];
            columns = [
                { data: 'id',visible: false, orderable: true, searchable: false},
                { data: 'select_checkbox', name: 'select_checkbox', orderable: false, searchable: false},
                { data: 'vendor', name: 'vendor', render: renderData, orderable: false, searchable: false},
                { data: 'po_doc', name: 'po_doc', render: renderData, orderable: false, searchable: false },
                { data: 'po_date', name: 'po_date', render: renderData, orderable: false, searchable: false },
                { data: 'si_doc', name: 'si_doc', render: renderData, orderable: false, searchable: false },
                { data: 'si_date', name: 'si_date', render: renderData, orderable: false, searchable: false },
                { data: 'ge_doc', name: 'ge_doc', render: renderData, orderable: false, searchable: false },
                { data: 'ge_date', name: 'ge_date', render: renderData, orderable: false, searchable: false },
                { data: 'item_code', name: 'item_code', render: renderData, orderable: false, searchable: false },
                { data: 'item_name', name: 'item_name', render: renderData, orderable: false, searchable: false },
                { data: 'attributes', name: 'attributes', render: renderData, orderable: false, searchable: false },
                { data: 'order_qty', name: 'order_qty', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'inv_order_qty', name: 'inv_order_qty', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'ge_qty', name: 'ge_qty', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'grn_qty', name: 'grn_qty', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'balance_qty', name: 'balance_qty', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'rate', name: 'rate', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'total_amount', name: 'total_amount', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'payment_term', name: 'payment_term', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'credit_days', name: 'credit_days', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
            ];
            initializeDataTableCustom('#poModal .po-order-detail',
                ajaxUrl,
                columns
            );
        }

        $(document).on('keyup', '#item_name_search', (e) => {
            $('#poModal .po-order-detail').DataTable().ajax.reload();
        });

        /*Checkbox for po/si item list*/
        $(document).on('change', '.po-order-detail > thead .form-check-input', (e) => {
            if (e.target.checked) {
                $(".po-order-detail > tbody .form-check-input").each(function() {
                    $(this).prop('checked', true);
                });
            } else {
                $(".po-order-detail > tbody .form-check-input").each(function() {
                    $(this).prop('checked', false);
                });
            }
        });

        function getSelectedPoIDS()
        {
            let ids = [];
            let asnIds = [];
            let asnItemIds = [];
            let geIds = [];
            let geItemIds = [];
            let referenceNos = [];

            $('.po_item_checkbox:checked').each(function() {
                ids.push($(this).val());
                asnIds.push($(this).attr('data-current-asn'));
                asnItemIds.push($(this).attr('data-current-asn-item'));
                geIds.push($(this).attr('data-current-ge'));
                geItemIds.push($(this).attr('data-current-ge-item'));
                referenceNo = $(this).siblings("input[type='hidden'][name='reference_no']").val();
                if (referenceNo) {
                    referenceNos.push(referenceNo);
                }
            });
            return {
                ids: ids,
                geIds: geIds,
                asnIds: asnIds,
                geItemIds: geItemIds,
                asnItemIds: asnItemIds,
                referenceNos: referenceNos
            };
        }

        $(document).on('click', '.poProcess', (e) => {
            let result = getSelectedPoIDS();
            let ids = result.ids;
            let geIds = result.geIds;
            let asnIds = result.asnIds;
            let geItemIds = result.geItemIds;
            let asnItemIds = result.asnItemIds;
            let referenceNo = result.referenceNos[0];
            let idsLength = ids.length;
            currentProcessType = 'po';
            rateHeader.textContent = 'Rate';
            if (!ids.length) {
                $("#poModal").modal('hide');
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select at least one one po',
                    icon: 'error',
                });
                return false;
            }

            let moduleTypes = getSelectedPoTypes();
            let paymentTerms = getSelectedPaymentTerms();

            $("[name='po_item_ids']").val(ids);
            $(".joSelect").addClass('d-none');
            $(".soSelect").addClass('d-none');
            $("#importItem ").hide();
            $("#addNewItemBtn").hide();
            if (referenceNo) {
                $("#referenceNoDiv").show();
                $("#reference_number_input").val(referenceNo);
            } else {
                $("#referenceNoDiv").hide();
                $("#reference_number_input").val('');
            }
            $("#reference_type_input").val('po');

            // for component item code
            function initializeAutocomplete2(selector, type) {
                $(selector).autocomplete({
                    minLength: 0,
                    source: function(request, response) {
                        let selectedAllItemIds = [];
                        $("#itemTable tbody [id*='row_']").each(function(index,item) {
                            if(Number($(item).find('[name*="[item_id]"]').val())) {
                                selectedAllItemIds.push(Number($(item).find('[name*="[item_id]"]').val()));
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
                        $input.closest('tr').find('[name*="[item_id]"]').val(itemId);
                        $input.closest('tr').find('[name*=item_code]').val(itemCode);
                        $input.closest('tr').find('[name*=item_name]').val(itemN);
                        $input.closest('tr').find('[name*=hsn_id]').val(hsnId);
                        $input.closest('tr').find('[name*=hsn_code]').val(hsnCode);
                        $input.closest('tr').find("td[id*='itemAttribute_']").html(defautAttrBtn);
                        $input.val(itemCode);
                        let uomOption = `<option value=${uomId}>${uomName}</option>`;
                        if(ui.item?.alternate_u_o_ms) {
                            for(let alterItem of ui.item.alternate_u_o_ms) {
                            uomOption += `<option value="${alterItem.uom_id}" ${alterItem.is_purchasing ? 'selected' : ''}>${alterItem.uom?.name}</option>`;
                            }
                        }
                        $input.closest('tr').find('[name*=uom_id]').append(uomOption);
                        $input.closest('tr').find("input[name*='attr_group_id']").remove();
                        setTimeout(() => {
                            if(ui.item.is_attr) {
                                $input.closest('tr').find('.attributeBtn').trigger('click');
                            } else {
                                $input.closest('tr').find('.attributeBtn').trigger('click');
                                $input.closest('tr').find('[name*="[accepted_qty]"]').val('').focus();
                            }
                        }, 100);
                        getItemDetail($input.closest('tr'), currentProcessType);
                        getItemCostPrice($input.closest('tr'));
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

            let currencyId = $("select[name='currency_id']").val();
            let transactionDate = $("input[name='document_date']").val() || '';
            let groupItems = [];
            $('tr[data-group-item]').each(function () {
                let groupItemData = $(this).data('group-item');
                groupItems.push(groupItemData);
            });

            groupItems = JSON.stringify(groupItems);
            let current_row_count = $("tbody tr[id*='row_']").length;
            let processData = {
                ids: ids,
                type: 'po',
                geIds: geIds,
                asnIds: asnIds,
                geItemIds: geItemIds,
                asnItemIds: asnItemIds,
                module_type: moduleTypes,
                payment_ids: paymentTerms.paymentIds,
                payment_terms: paymentTerms.paymentTerms,
                credit_days: paymentTerms.creditDays,
            };

            asnProcess(processData, 'po-process');
        });

        /*Open Jo model*/
        let joOrderTable;
        $(document).on('click', '.joSelect', (e) => {
            tableRowCount = $('.mrntableselectexcel tr').length;
            if(tableRowCount)
            {
                let poDetailIds = [];
                $(".mrntableselectexcel").find("tr[id^='row_']").each(function() {
                    let poDetailId = $(this).find("input[name*='[purchase_order_id]']").val();
                    if (poDetailId !== undefined) {
                        poDetailIds.push(poDetailId);
                    }
                });
                localStorage.setItem('selectedPoIds', JSON.stringify(poDetailIds));
            }

            $("#joModal").modal('show');
            currentProcessType='jo';
            openJoRequest();
            const tableSelector = '#joModal .jo-order-detail';
            $(tableSelector).DataTable().clear().destroy();
            getJobOrders();
            if ($(tableSelector).length) {
                if ($.fn.DataTable.isDataTable(tableSelector)) {
                    joOrderTable = $(tableSelector).DataTable();
                    joOrderTable.ajax.reload();
                    $('#joModal').off('change', '.jo_item_checkbox').on('change', '.jo_item_checkbox', function () {
                        const currentAsn = $(this).attr('data-current-asn');
                        const currentGe = $(this).attr('data-current-ge');
                        const isChecked = $(this).is(':checked');

                        // If data-current-asn is valid
                        if (currentAsn && currentAsn !== 'null') {
                            $(`.jo_item_checkbox[data-current-asn="${currentAsn}"]`).prop('checked', isChecked);
                        }

                        // If data-current-ge is valid
                        if (currentGe && currentGe !== 'null') {
                            $(`.jo_item_checkbox[data-current-ge="${currentGe}"]`).prop('checked', isChecked);
                        }
                    });
                }
            }
        });

        function getSelectedJoTypes()
        {
            let moduleTypes = [];
            $('.jo_item_checkbox:checked').each(function() {
                moduleTypes.push($(this).attr('data-module')); // Corrected: Get attribute value instead of setting it
            });
            return moduleTypes;
        }

        function openJoRequest()
        {
            initializeAutocompleteJoQt("jo_vendor_code_input_qt", "jo_vendor_id_qt_val", "vendor_list", "jo_vendor_code", "company_name");
            initializeAutocompleteJoQt("jo_document_no_input_qt", "jo_document_id_qt_val", "jo_document_qt", "jo_document_number", "");
            initializeAutocompleteJoQt("jo_asn_no_input_qt", "jo_asn_id_qt_val", "jo_asn_document_qt", "document_number", "");
            initializeAutocompleteJoQt("jo_ge_no_input_qt", "jo_ge_id_qt_val", "jo_ge_document_qt", "document_number", "");
            initializeAutocompleteJoQt("jo_so_no_input_qt", "jo_so_qt_val", "jo_so_qt", "jo_book_code", "jo_document_number");
        }

        function initializeAutocompleteJoQt(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "")
        {
            let modalType = '#joModal';
            if (currentProcessType == 'jo')
                modalType = '#joModal';

            $("#" + selector).autocomplete({
                source: function(request, resjonse) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: typeVal,
                            vendor_id : $("#jo_vendor_id_qt_val").val(),
                            header_book_id : $("#jo_book_id").val(),
                            store_id : $("#store_id_jo").val() || '',
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                // return {
                                //     id: item.id,
                                //     label: `${item[labelKey1]} ${labelKey2 ? (item[labelKey2] ? '(' + item[labelKey2] + ')' : '') : ''}`,
                                //     code: item[labelKey1] || '',
                                // };
                                let label = '';

                                if ('document_number' in item && 'book_code' in item) {
                                    label = `${item.document_number}`;
                                } else if ('company_name' in item) {
                                    label = item.company_name;
                                }

                                return {
                                    id: item.id,
                                    label: label,
                                    code: item.book_code || item.vendor_code || '',
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching customer data:', xhr.resjonseText);
                        }
                    });
                },
                appendTo : modalType,
                minLength: 0,
                select: function(event, ui) {
                    var $input = $(this);
                    $input.val(ui.item.label);
                    $("#" + selectorSibling).val(ui.item.id);
                    $('#joModal .jo-order-detail').DataTable().ajax.reload();
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#" + selectorSibling).val("");
                        $('#joModal .jo-order-detail').DataTable().ajax.reload();
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $("#" + selectorSibling).val("");
                    $('#joModal .jo-order-detail').DataTable().ajax.reload();
                    $(this).autocomplete("search", "");
                }
            }).blur(function() {
                if (this.value === "") {
                    $("#" + selectorSibling).val("");
                    $('#joModal .jo-order-detail').DataTable().ajax.reload();
                }
            })
        }

        function renderJoData(data) {
            return data ? data : '';
        }


        function getJobOrders()
        {
            const ajaxUrl = '{{ route("material-receipt.get.jo", ["type" => "create"]) }}';
            var columns = [];
            columns = [
                { data: 'id',visible: false, orderable: true, searchable: false},
                { data: 'select_checkbox', name: 'select_checkbox', orderable: false, searchable: false},
                { data: 'vendor', name: 'vendor', render: renderData, orderable: false, searchable: false},
                { data: 'jo_doc', name: 'jo_doc', render: renderData, orderable: false, searchable: false },
                { data: 'jo_date', name: 'jo_date', render: renderData, orderable: false, searchable: false },
                { data: 'si_doc', name: 'si_doc', render: renderData, orderable: false, searchable: false },
                { data: 'si_date', name: 'si_date', render: renderData, orderable: false, searchable: false },
                { data: 'ge_doc', name: 'ge_doc', render: renderData, orderable: false, searchable: false },
                { data: 'ge_date', name: 'ge_date', render: renderData, orderable: false, searchable: false },
                { data: 'item_code', name: 'item_code', render: renderData, orderable: false, searchable: false },
                { data: 'item_name', name: 'item_name', render: renderData, orderable: false, searchable: false },
                { data: 'attributes', name: 'attributes', render: renderData, orderable: false, searchable: false },
                { data: 'order_qty', name: 'order_qty', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'inv_order_qty', name: 'inv_order_qty', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'ge_qty', name: 'ge_qty', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'grn_qty', name: 'grn_qty', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'balance_qty', name: 'balance_qty', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'rate', name: 'rate', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'total_amount', name: 'total_amount', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'payment_term', name: 'payment_term', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'credit_days', name: 'credit_days', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
            ];
            initializeDataTableCustom('#joModal .jo-order-detail',
                ajaxUrl,
                columns,
            );
        }

        $(document).on('keyup', '#jo_item_name_search', (e) => {
            $('#joModal .jo-order-detail').DataTable().ajax.reload();
        });

        /*Checkbox for jo/si item list*/
        $(document).on('change','.jo-order-detail > thead .form-check-input',(e) => {
            if (e.target.checked) {
                $(".jo-order-detail > tbody .form-check-input").each(function(){
                    $(this).prop('checked',true);
                });
            } else {
                $(".jo-order-detail > tbody .form-check-input").each(function(){
                    $(this).prop('checked',false);
                });
            }
        });

        function getSelectedJoIDS()
        {
            let ids = [];
            let asnIds = [];
            let asnItemIds = [];
            let geIds = [];
            let geItemIds = [];
            let referenceNos = [];
            $('.jo_item_checkbox:checked').each(function() {
                ids.push($(this).val());
                asnIds.push($(this).attr('data-current-asn'));
                asnItemIds.push($(this).attr('data-current-asn-item'));
                geIds.push($(this).attr('data-current-ge'));
                geItemIds.push($(this).attr('data-current-ge-item'));
                referenceNo = $(this).siblings("input[type='hidden'][name='reference_no']").val();
                if (referenceNo) {
                    referenceNos.push(referenceNo);
                }
            });
            return {
                ids: ids,
                geIds: geIds,
                asnIds: asnIds,
                geItemIds: geItemIds,
                asnItemIds: asnItemIds,
                referenceNos: referenceNos
            };
        }

        $(document).on('click', '.joProcess', (e) => {
            let result = getSelectedJoIDS();
            let ids = result.ids;
            let geIds = result.geIds;
            let asnIds = result.asnIds;
            let geItemIds = result.geItemIds;
            let asnItemIds = result.asnItemIds;
            let referenceNo = result.referenceNos[0];
            let idsLength = ids.length;
            currentProcessType = 'jo';
            rateHeader.textContent = 'Service Charge';
            if (!ids.length) {
                $("#joModal").modal('hide');
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select at least one one jo',
                    icon: 'error',
                });
                return false;
            }
            $(".poSelect").hide();
            $("#addNewItemBtn").hide();
            $("[name='jo_item_ids']").val(ids);
            let moduleTypes = getSelectedJoTypes();
            let paymentTerms = getSelectedPaymentTerms();

            if (referenceNo) {
                $("#referenceNoDiv").show();
                $("#reference_number_input").val(referenceNo);
            } else {
                $("#referenceNoDiv").hide();
                $("#reference_number_input").val('');
            }
            $("#reference_type_input").val('jo');

            // for component item code
            function initializeAutocomplete2(selector, type) {
                $(selector).autocomplete({
                    minLength: 0,
                    source: function(request, response) {
                        let selectedAllItemIds = [];
                        $("#itemTable tbody [id*='row_']").each(function(index, item) {
                            if (Number($(item).find('[name*="[item_id]"]').val())) {
                                selectedAllItemIds.push(Number($(item).find(
                                    '[name*="[item_id]"]').val()));
                            }
                        });
                        $.ajax({
                            url: '/search',
                            method: 'GET',
                            dataType: 'json',
                            data: {
                                q: request.term,
                                type: 'goods_item_list',
                                selectedAllItemIds: JSON.stringify(selectedAllItemIds)
                            },
                            success: function(data) {
                                response($.map(data, function(item) {
                                    return {
                                        id: item.id,
                                        label: `${item.item_name} (${item.item_code})`,
                                        code: item.item_code || '',
                                        item_id: item.id,
                                        item_name: item.item_name,
                                        uom_name: item.uom?.name,
                                        uom_id: item.uom_id,
                                        hsn_id: item.hsn?.id,
                                        hsn_code: item.hsn?.code,
                                        alternate_u_o_ms: item.alternate_u_o_ms,
                                        is_attr: item.item_attributes_count,
                                    };
                                }));
                            },
                            error: function(xhr) {
                                console.error('Error fetching customer data:', xhr
                                    .responseText);
                            }
                        });
                    },
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
                        $input.closest('tr').find('[name*="[item_id]"]').val(itemId);
                        $input.closest('tr').find('[name*=item_code]').val(itemCode);
                        $input.closest('tr').find('[name*=item_name]').val(itemN);
                        $input.closest('tr').find('[name*=hsn_id]').val(hsnId);
                        $input.closest('tr').find('[name*=hsn_code]').val(hsnCode);
                        $input.closest('tr').find("td[id*='itemAttribute_']").html(defautAttrBtn);
                        $input.val(itemCode);
                        let uomOption = `<option value=${uomId}>${uomName}</option>`;
                        if (ui.item?.alternate_u_o_ms) {
                            for (let alterItem of ui.item.alternate_u_o_ms) {
                                uomOption +=
                                    `<option value="${alterItem.uom_id}" ${alterItem.is_purchasing ? 'selected' : ''}>${alterItem.uom?.name}</option>`;
                            }
                        }
                        $input.closest('tr').find('[name*=uom_id]').append(uomOption);
                        $input.closest('tr').find("input[name*='attr_group_id']").remove();
                        setTimeout(() => {
                            if (ui.item.is_attr) {
                                $input.closest('tr').find('.attributeBtn').trigger('click');
                            } else {
                                $input.closest('tr').find('.attributeBtn').trigger('click');
                                $input.closest('tr').find('[name*="[order_qty]"]').val('')
                                    .focus();
                            }
                        }, 100);

                        getItemDetail($input.closest('tr'), currentProcessType);
                        getItemCostPrice($input.closest('tr'));
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

            let currencyId = $("select[name='currency_id']").val();
            let transactionDate = $("input[name='document_date']").val() || '';
            let groupItems = [];
            $('tr[data-group-item]').each(function() {
                let groupItemData = $(this).data('group-item');
                groupItems.push(groupItemData);
            });

            groupItems = JSON.stringify(groupItems);
            let current_row_count = $("tbody tr[id*='row_']").length;
            let processData = {
                ids: ids,
                asnIds: asnIds,
                asnItemIds: asnItemIds,
                geIds: geIds,
                geItemIds: geItemIds,
                type: 'jo',
                module_type: moduleTypes,
                payment_ids: paymentTerms.paymentIds,
                payment_terms: paymentTerms.paymentTerms,
                credit_days: paymentTerms.creditDays,
            };
            asnProcess(processData, 'jo-process');
        });

         /*Open So model*/
        let soOrderTable;
        $(document).on('click', '.soSelect', (e) => {
            tableRowCount = $('.mrntableselectexcel tr').length;
            $("#soModal").modal('show');
            currentProcessType = 'so';
            openSaleRequest();
            const tableSelector = '#soModal .so-order-detail';
            $(tableSelector).DataTable().clear().destroy();
            getSaleOrders();
            if ($(tableSelector).length) {
                if ($.fn.DataTable.isDataTable(tableSelector)) {
                    soOrderTable = $(tableSelector).DataTable();
                    soOrderTable.ajax.reload();
                }
                // Re-initialize DataTable
            }
        });

        function getSelectedSoTypes()
        {
            let moduleTypes = [];
            $('.so_item_checkbox:checked').each(function() {
                moduleTypes.push($(this).attr('data-module'));
            });
            return moduleTypes;
        }

        function openSaleRequest()
        {
            initializeAutocompleteSQt("so_vendor_code_input_qt", "so_vendor_id_qt_val", "vendor_list", "vendor_code", "company_name");
            initializeAutocompleteSQt("so_document_no_input_qt", "so_document_id_qt_val", "so_document_qt", "document_number", "");
            initializeAutocompleteSQt("po_so_no_input_qt", "po_so_qt_val", "po_so_qt", "book_code", "document_number");
        }

        function initializeAutocompleteSQt(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "")
        {
            let modalType = '#soModal';

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
                            header_book_id : $("#book_id").val(),
                            item_search : $("#item_search_id_qt_val").val() || '',
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                // return {
                                //     id: item.id,
                                //     label: `${item[labelKey1]} ${labelKey2 ? (item[labelKey2] ? '(' + item[labelKey2] + ')' : '') : ''}`,
                                //     code: item[labelKey1] || '',
                                // };
                                let label = '';
                                if ('document_number' in item && 'book_code' in item) {
                                    label = `${item.document_number}`;
                                } else if ('company_name' in item) {
                                    label = item.company_name;
                                }
                                return {
                                    id: item.id,
                                    label: label,
                                    code: item.book_code || item.vendor_code || '',
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });
                },
                appendTo : modalType,
                minLength: 0,
                select: function(event, ui) {
                    var $input = $(this);
                    $input.val(ui.item.label);
                    $("#" + selectorSibling).val(ui.item.id);
                    $('#soModal .so-order-detail').DataTable().ajax.reload();
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#" + selectorSibling).val("");
                        $('#soModal .so-order-detail').DataTable().ajax.reload();
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $("#" + selectorSibling).val("");
                    $('#soModal .so-order-detail').DataTable().ajax.reload();
                    $(this).autocomplete("search", "");
                }
            }).blur(function() {
                if (this.value === "") {
                    $("#" + selectorSibling).val("");
                    $('#soModal .so-order-detail').DataTable().ajax.reload();
                }
            })
        }

        function renderData(data) {
            return data ? data : '';
        }

        function getSaleOrders()
        {
            const ajaxUrl = '{{ route("material-receipt.get.so", ["type" => "create"]) }}';
            var columns = [];
            columns = [
                { data: 'id',visible: false, orderable: true, searchable: false},
                { data: 'select_checkbox', name: 'select_checkbox', orderable: false, searchable: false},
                { data: 'vendor', name: 'vendor', render: renderData, orderable: false, searchable: false},
                { data: 'so_doc', name: 'so_doc', render: renderData, orderable: false, searchable: false },
                { data: 'so_date', name: 'so_date', render: renderData, orderable: false, searchable: false },
                { data: 'ge_doc', name: 'ge_doc', render: renderData, orderable: false, searchable: false },
                { data: 'ge_date', name: 'ge_date', render: renderData, orderable: false, searchable: false },
                { data: 'item_code', name: 'item_code', render: renderData, orderable: false, searchable: false },
                { data: 'item_name', name: 'item_name', render: renderData, orderable: false, searchable: false },
                { data: 'attributes', name: 'attributes', render: renderData, orderable: false, searchable: false },
                { data: 'order_qty', name: 'order_qty', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'inv_order_qty', name: 'inv_order_qty', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'ge_qty', name: 'ge_qty', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'grn_qty', name: 'grn_qty', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'balance_qty', name: 'balance_qty', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'rate', name: 'rate', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'total_amount', name: 'total_amount', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
            ];
            initializeDataTableCustom('#soModal .so-order-detail',
                ajaxUrl,
                columns
            );
        }

        $(document).on('keyup', '#so_item_name_search', (e) => {
            $('#soModal .so-order-detail').DataTable().ajax.reload();
        });

        /*Checkbox for po/si item list*/
        $(document).on('change','.so-order-detail > thead .form-check-input',(e) => {
            if (e.target.checked) {
                $(".so-order-detail > tbody .form-check-input").each(function(){
                    $(this).prop('checked',true);
                });
            } else {
                $(".so-order-detail > tbody .form-check-input").each(function(){
                    $(this).prop('checked',false);
                });
            }
        });

        function getSelectedSoIDS()
        {
            let ids = [];
            let referenceNos = [];
            $('.so_item_checkbox:checked').each(function() {
                ids.push($(this).val());
                referenceNo = $(this).siblings("input[type='hidden'][name='reference_no']").val();
                if (referenceNo) {
                    referenceNos.push(referenceNo);
                }
            });
            return {
                ids: ids,
                referenceNos: referenceNos
            };
        }

        $(document).on('click', '.soProcess', (e) => {
            let result = getSelectedSoIDS();
            let ids = result.ids;
            let referenceNo = result.referenceNos[0];
            let idsLength = ids.length;
            currentProcessType = 'so';
            rateHeader.textContent = 'Rate';
            if (!ids.length) {
                $("#soModal").modal('hide');
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select at least one one so',
                    icon: 'error',
                });
                return false;
            }

            let moduleTypes = getSelectedSoTypes();

            $("[name='so_item_ids']").val(ids);
            $(".poSelect").addClass('d-none');
            $(".joSelect").addClass('d-none');
            $("#importItem ").hide();
            $("#addNewItemBtn").hide();
            if (referenceNo) {
                $("#referenceNoDiv").show();
                $("#reference_number_input").val(referenceNo);
            } else {
                $("#referenceNoDiv").hide();
                $("#reference_number_input").val('');
            }
            $("#reference_type_input").val('so');

            // for component item code
            function initializeAutocomplete2(selector, type) {
                $(selector).autocomplete({
                    minLength: 0,
                    source: function(request, response) {
                        let selectedAllItemIds = [];
                        $("#itemTable tbody [id*='row_']").each(function(index,item) {
                            if(Number($(item).find('[name*="[item_id]"]').val())) {
                                selectedAllItemIds.push(Number($(item).find('[name*="[item_id]"]').val()));
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
                        $input.closest('tr').find('[name*="[item_id]"]').val(itemId);
                        $input.closest('tr').find('[name*=item_code]').val(itemCode);
                        $input.closest('tr').find('[name*=item_name]').val(itemN);
                        $input.closest('tr').find('[name*=hsn_id]').val(hsnId);
                        $input.closest('tr').find('[name*=hsn_code]').val(hsnCode);
                        $input.closest('tr').find("td[id*='itemAttribute_']").html(defautAttrBtn);
                        $input.val(itemCode);
                        let uomOption = `<option value=${uomId}>${uomName}</option>`;
                        if(ui.item?.alternate_u_o_ms) {
                            for(let alterItem of ui.item.alternate_u_o_ms) {
                            uomOption += `<option value="${alterItem.uom_id}" ${alterItem.is_purchasing ? 'selected' : ''}>${alterItem.uom?.name}</option>`;
                            }
                        }
                        $input.closest('tr').find('[name*=uom_id]').append(uomOption);
                        $input.closest('tr').find("input[name*='attr_group_id']").remove();
                        setTimeout(() => {
                            if(ui.item.is_attr) {
                                $input.closest('tr').find('.attributeBtn').trigger('click');
                            } else {
                                $input.closest('tr').find('.attributeBtn').trigger('click');
                                $input.closest('tr').find('[name*="[order_qty]"]').val('').focus();
                            }
                        }, 100);
                        getItemDetail($input.closest('tr'), currentProcessType);
                        getItemCostPrice($input.closest('tr'));
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

            let currencyId = $("select[name='currency_id']").val();
            let transactionDate = $("input[name='document_date']").val() || '';
            let groupItems = [];
            $('tr[data-group-item]').each(function () {
                let groupItemData = $(this).data('group-item');
                groupItems.push(groupItemData);
            });

            groupItems = JSON.stringify(groupItems);
            let current_row_count = $("tbody tr[id*='row_']").length;
            ids = JSON.stringify(ids);
            moduleTypes = JSON.stringify(moduleTypes);
            let type = 'so'; // Dynamically fetch the `type` from the current route
            let actionUrl = '{{ route("material-receipt.process.so-item") }}'
            + '?ids=' + encodeURIComponent(ids)
            + '&type=' + type
            + '&moduleTypes=' + moduleTypes
            + '&tableRowCount=' + tableRowCount
            + '&currency_id=' + encodeURIComponent(currencyId)
            + '&d_date=' + encodeURIComponent(transactionDate)
            // + '&groupItems=' + encodeURIComponent(groupItems);
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if(data.status == 200) {
                        let soOrder = data?.data?.saleOrder;
                        vendorOnChange(data?.data?.vendor?.id, 'so', soOrder.id);
                        let result = getSelectedSoIDS();
                        let newIds = result.ids;
                        let existingIds = localStorage.getItem('selectedSoIds');
                        if (existingIds) {
                            existingIds = JSON.parse(existingIds);
                            const mergedIds = Array.from(new Set([...existingIds, ...newIds]));
                            localStorage.setItem('selectedSoIds', JSON.stringify(mergedIds));
                        } else {
                            localStorage.setItem('selectedSoIds', JSON.stringify(newIds));
                        }

                        let existingIdsUpdate = JSON.parse(localStorage.getItem('selectedSoIds'));
                        $("[name='so_item_ids']").val(existingIdsUpdate.join(','));

                        let module_type = data?.data?.moduleType || '';
                        let vendor = data?.data?.vendor || '';
                        let finalDiscounts = data?.data?.finalDiscounts;
                        let finalExpenses = data?.data?.finalExpenses;

                        if ($("#itemTable .mrntableselectexcel").find("tr[id*='row_']").length) {
                            $("#itemTable .mrntableselectexcel tr[id*='row_']:last").after(data.data.pos);
                        } else {
                            $("#itemTable .mrntableselectexcel").empty().append(data.data.pos);
                        }
                        initializeAutocomplete2(".comp_item_code");
                        $("#soModal").modal('hide');
                        $("select[name='currency_id']").prop('disabled', true);
                        $("select[name='payment_term_id']").prop('disabled', true);
                        $("#vendor_name").prop('readonly',true);
                        $(".editAddressBtn").addClass('d-none');
                        $("#vendor_name").prop('readonly',true);
                        if(soOrder.type == 'supplier-invoice'){
                            $("[name='supplier_invoice_no']").val(soOrder.document_number);
                            $("[name='supplier_invoice_date']").val(soOrder.document_date);
                        } else{
                            $("[name='supplier_invoice_no']").val();
                            $("[name='supplier_invoice_date']").val();
                        }

                        $(".module_type").val(module_type);
                        let locationId = $("[name='header_store_id']").val();
                        // getLocation(locationId);

                        if(finalDiscounts.length) {
                            let rows = '';
                            finalDiscounts.forEach(function(item,index) {
                                index = index + 1;
                                rows+= `<tr class="display_summary_discount_row">
                                        <td>${index}</td>
                                        <td>${item.ted_name}
                                            <input type="hidden" value="${item.ted_id}" name="disc_summary[${index}][ted_d_id]">
                                            <input type="hidden" value="" name="disc_summary[${index}][d_id]">
                                            <input type="hidden" value="${item.ted_name}" name="disc_summary[${index}][d_name]">
                                        </td>
                                        <td class="text-end">${typeof item.ted_perc === "number" ? '0' : item.ted_perc}
                                            <input type="hidden" value="${typeof item.ted_perc === "number" ? '0' : item.ted_perc}" name="disc_summary[${index}][d_perc]">
                                            <input type="hidden" value="${item.ted_perc}" name="disc_summary[${index}][hidden_d_perc]">
                                        </td>
                                        <td class="text-end">
                                            <input type="hidden" value="" name="disc_summary[${index}][d_amnt]">
                                        </td>
                                        <td>
                                            <a href="javascript:;" class="text-danger deleteSummaryDiscountRow">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                            </a>
                                        </td>
                                    </tr>`
                            });

                            $("#summaryDiscountTable tbody").find('.display_summary_discount_row').remove();
                            $("#summaryDiscountTable tbody").find('#disSummaryFooter').before(rows);
                            $("#f_header_discount_hidden").removeClass('d-none');
                        } else {
                            $("#f_header_discount_hidden").addClass('d-none');
                        }

                        if(finalExpenses.length) {
                            let rows = '';
                            finalExpenses.forEach(function(item,index) {
                                index = index + 1;
                                rows+=`<tr class="display_summary_exp_row">
                                        <td>${index}</td>
                                        <td>${item.ted_name}
                                            <input type="hidden" value="${item.ted_id}" name="exp_summary[${index}][ted_e_id]">
                                            <input type="hidden" value="" name="exp_summary[${index}][e_id]">
                                            <input type="hidden" value="${item.ted_name}" name="exp_summary[${index}][e_name]">
                                        </td>
                                        <td class="text-end">${typeof item.ted_perc === "number" ? '0' : item.ted_perc}
                                            <input type="hidden" value="${typeof item.ted_perc === "number" ? '0' : item.ted_perc}" name="exp_summary[${index}][e_perc]">
                                            <input type="hidden" value="${item.ted_perc}" name="exp_summary[${index}][hidden_e_perc]">
                                        </td>
                                        <td class="text-end">
                                        <input type="hidden" value="" name="exp_summary[${index}][e_amnt]">
                                        </td>
                                        <td>
                                            <a href="javascript:;" class="text-danger deleteExpRow">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                                            </a>
                                        </td>
                                    </tr>`;

                            });
                            $("#summaryExpTable tbody").find('.display_summary_exp_row').remove();
                            $("#summaryExpTable tbody").find('#expSummaryFooter').before(rows);
                        }
                        initializeAutocomplete2(".comp_item_code");
                        focusAndScrollToLastRowInput();
                        setTimeout(() => {
                            setTableCalculation();
                            if(idsLength > 1)
                            {
                                $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
                                    currentIndex = index + 1;
                                    if(tableRowCount>0)
                                    {
                                        currentIndex = tableRowCount + 1;
                                    }
                                    setAttributesUIHelper(currentIndex,"#itemTable");
                                });
                            }
                            currentIndex = tableRowCount + 1;
                            setAttributesUIHelper(currentIndex,"#itemTable");
                        },500);
                    }
                    if(data.status == 422) {
                        $(".editAddressBtn").removeClass('d-none');
                        $("#vendor_name").val('').prop('readonly',false);
                        $("#vendor_id").val('');
                        $("#vendor_code").val('');
                        $("#hidden_state_id").val('');
                        $("#hidden_country_id").val('');
                        $("select[name='currency_id']").empty().append('<option value="">Select</option>').prop('readonly',false);
                        $("select[name='payment_term_id']").empty().append('<option value="">Select</option>').prop('readonly',false);
                        $(".shipping_detail").text('-');
                        $(".billing_detail").text('-');
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                        });
                        return false;
                    }
                });
            });
        });

        function initializeAutocompleteTED(selector, idSelector, nameSelector, type, percentageVal) {
            $("#" + selector).autocomplete({
                source: function(request, response) {
                    let ids = [];
                    $('.modal.show').find("tbody tr").each(function(index, item) {
                        let tedId = $(item).find("input[name*='ted_']").val();
                        if (tedId) {
                            ids.push(tedId);
                        }
                    });
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type:type,
                            ids: JSON.stringify(ids)
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    hsn_id: item.hsn_id,
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
                    var itemId = ui.item.id;
                    var hsnId = ui?.item?.hsn_id;
                    var itemName = ui.item.label;
                    var itemPercentage = ui.item.percentage;

                    $input.val(itemName);
                    $("#" + idSelector).val(itemId).attr("data-hsn-id", hsnId);
                    $("#" + nameSelector).val(itemName);
                    $("#" + percentageVal).val(itemPercentage).trigger('keyup');
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#" + idSelector).val("").attr("data-hsn-id", "");
                        $("#" + nameSelector).val("");
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
        }

        function getLocation(locationId = '')
        {
            let actionUrl = '{{ route("store.get") }}'+'?location_id='+locationId;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if(data.status == 200) {
                        let options = '';
                        data.data.locations.forEach(function(location) {
                            options+= `<option value="${location.id}">${location.store_code}</option>`;
                        });
                        $("[name='header_store_id']").empty().append(options);
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                        });
                    }
                });
            });
        }

        setTimeout(() => {
            $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
                let currentIndex = index + 1;
                setAttributesUIHelper(currentIndex,"#itemTable");
            });
        },100);
        // ASN Process
        function asnProcess(asnData, moduleProcess) {
            const current_row_count = $("tbody tr[id*='row_']").length;

            const ids = JSON.stringify(asnData.ids);
            const asnIds = JSON.stringify(asnData.asnIds);
            const asnItemIds = JSON.stringify(asnData.asnItemIds);
            const geIds = JSON.stringify(asnData.geIds);
            const geItemIds = JSON.stringify(asnData.geItemIds);
            const moduleTypes = JSON.stringify(asnData.module_type);
            const moduleType = asnData.module_type?.[0] ?? 'po';
            const processType = asnData.type;
            const paymentIds = JSON.stringify(asnData.payment_ids);
            const paymentTerms = JSON.stringify(asnData.payment_ids);
            const creditDays = JSON.stringify(asnData.credit_days);
            let existPaymentTermId = JSON.stringify(exist_payment_term_id);
            let existCreditDays = JSON.stringify(exist_credit_days);

            const currencyId = $("[name='currency_id']").val();
            const transactionDate = $("[name='document_date']").val();
            const type = $("meta[name='route-type']").attr("content"); // blade->meta

            const baseRoute = processType === 'jo'
                ? '{{ route("material-receipt.process.jo-item") }}'
                : '{{ route("material-receipt.process.po-item") }}';

            const actionUrl = baseRoute
                .replace(':type', type)
                + '?ids=' + encodeURIComponent(ids)
                + '&asnIds=' + encodeURIComponent(asnIds)
                + '&asnItemIds=' + encodeURIComponent(asnItemIds)
                + '&geIds=' + encodeURIComponent(geIds)
                + '&geItemIds=' + encodeURIComponent(geItemIds)
                + '&moduleTypes=' + moduleTypes
                + '&paymentTerms=' + paymentTerms
                + '&creditDays=' + creditDays
                + '&tableRowCount=' + tableRowCount
                + '&currency_id=' + encodeURIComponent(currencyId)
                + '&d_date=' + encodeURIComponent(transactionDate)
                + '&existPaymentTermId=' + encodeURIComponent(existPaymentTermId)
                + '&existCreditDays=' + encodeURIComponent(existCreditDays)
                + '&current_row_count=' + current_row_count;

            fetch(actionUrl)
                .then(res => res.json())
                .then(data => {
                    if (data.status !== 200) return handleAsnError(data.message);

                    const {
                        vendor,
                        finalExpenses,
                        pos,
                        moduleType,
                        vendorAsn
                    } = data.data;

                    const modelType = processType === 'jo' ? 'jo' : 'po';
                    const order = modelType === 'jo' ? data.data.jobOrder : data.data.purchaseOrder;

                    vendorOnChange(vendor?.id, modelType, order.id);

                    const getSelectedIdsFn = modelType === 'jo' ? getSelectedJoIDS : getSelectedPoIDS;
                    const hiddenFieldName = modelType === 'jo' ? 'jo_item_ids' : 'po_item_ids';
                    const localStorageKey = modelType === 'jo' ? 'selectedJoIds' : 'selectedPoIds';

                    const newIds = getSelectedIdsFn().ids;
                    const existingIds = JSON.parse(localStorage.getItem(localStorageKey) || '[]');
                    const mergedIds = Array.from(new Set([...existingIds, ...newIds]));

                    localStorage.setItem(localStorageKey, JSON.stringify(mergedIds));
                    $(`[name='${hiddenFieldName}']`).val(mergedIds.join(','));

                    $(".module_type").val(modelType);
                    $("#itemTable .mrntableselectexcel").append(pos);
                    initializeAutocomplete2(".comp_item_code");

                    $("#poModal, #joModal").modal('hide');
                    $('.asn_process').prop('disabled', true);
                    $(".supplier_invoice_no").prop('readonly', false);
                    $(".supplier_invoice_date").prop('readonly', false);
                    applyInspectionState();
                    seedAllLockedRows();

                    switch (moduleProcess) {
                        case 'asn-process':
                            $("#reference_from").addClass('d-none');
                            $(".asn-container").removeClass('d-none');
                            // $('.asn_process').prop('disabled', true);
                            $(".supplier_invoice_no").prop('readonly', true);
                            $(".supplier_invoice_date").prop('readonly', true);
                            break;

                        case 'po-process':
                            $(".joSelect").addClass('d-none');
                            $(".poSelect").removeClass('d-none');
                            $(".asn-container").addClass('d-none');
                            break;

                        default:
                            $(".poSelect").addClass('d-none');
                            $(".joSelect").removeClass('d-none');
                            $(".asn-container").addClass('d-none');
                            break;
                    }

                    // UI Locks
                    $("select[name='currency_id'], select[name='payment_term_id']").prop('disabled', true);
                    $("#vendor_name").prop('readonly', true);
                    $(".editAddressBtn").addClass('d-none');

                    // Supplier details
                    if (moduleType === 'gate-entry' && geHeader) {
                        $("[name='gate_entry_no']").val(geHeader.gate_entry_no);
                        $("[name='gate_entry_date']").val(geHeader.gate_entry_date);
                        $("[name='supplier_invoice_no']").val(geHeader.supplier_invoice_no);
                        $("[name='supplier_invoice_date']").val(geHeader.supplier_invoice_date);
                        $("[name='consignment_no']").val(geHeader.consignment_no);
                        $("[name='eway_bill_no']").val(geHeader.eway_bill_no);
                        $("[name='transporter_name']").val(geHeader.transporter_name);
                        $("[name='vehicle_no']").val(geHeader.vehicle_no);
                    } else if (moduleType === 'suppl-inv' && vendorAsn) {
                        $("[name='supplier_invoice_no']").val(vendorAsn.suppl_invoice_no);
                        $("[name='supplier_invoice_date']").val(vendorAsn.suppl_invoice_date);
                        $("[name='consignment_no']").val(vendorAsn.consignment_no);
                        $("[name='eway_bill_no']").val(vendorAsn.eway_bill_no);
                        $("[name='transporter_name']").val(vendorAsn.transporter_name);
                        $("[name='vehicle_no']").val(vendorAsn.vehicle_no);
                    } else {
                        $("[name='supplier_invoice_no'], [name='supplier_invoice_date'], [name='consignment_no'], [name='eway_bill_no'], [name='transporter_name'], [name='vehicle_no']").val('');
                    }

                    // Expenses
                    const $expBody = $("#summaryExpTable tbody");
                    $expBody.find('.display_summary_exp_row').remove();

                    if (finalExpenses.length) {
                        let tr = '';
                        finalExpenses.forEach((item, i) => {
                            const index = i + 1;
                            tr += `
                                <tr class="display_summary_exp_row">
                                    <td>${index}</td>
                                    <td class="text-right">
                                        ${item.ted_name}
                                        <input type="hidden" name="exp_summary[${index}][hsn_id]" value="${item.hsn_id}">
                                        <input type="hidden" name="exp_summary[${index}][ted_e_id]" value="${item.ted_id}">
                                        <input type="hidden" name="exp_summary[${index}][e_id]" value="${item.id}">
                                        <input type="hidden" name="exp_summary[${index}][e_name]" value="${item.ted_name}">
                                    </td>
                                    <td class="text-end">
                                        ${parseFloat((item.ted_amount ?? "0").toString().replace(/,/g, '')).toFixed(2)}
                                        <input type="hidden"
                                            name="exp_summary[${index}][e_amnt]"
                                            value="${(item.ted_amount ?? "0").toString().replace(/,/g, '')}">
                                    </td>
                                    <td class="text-end">
                                        ${parseFloat((item.tax_amount ?? "0").toString().replace(/,/g, '')).toFixed(2)}
                                        <input type="hidden"
                                            name="exp_summary[${index}][tax_amount]"
                                            value="${parseFloat((item.tax_amount ?? "0").toString().replace(/,/g, '')).toFixed(2)}">
                                    </td>
                                    <td class="text-end">
                                        ${(parseFloat((item.ted_amount ?? "0").toString().replace(/,/g, '')) +
                                        parseFloat((item.tax_amount ?? "0").toString().replace(/,/g, ''))).toFixed(2)}
                                        <input type="hidden"
                                            name="exp_summary[${index}][total]"
                                            value="${(parseFloat((item.ted_amount ?? "0").toString().replace(/,/g, '')) +
                                                        parseFloat((item.tax_amount ?? "0").toString().replace(/,/g, ''))).toFixed(2)}">
                                    </td>
                                    <td>
                                        ${item.tax_breakup ? formatTaxBreakup(item.tax_breakup) : ''}
                                        <input type="hidden" name="exp_summary[${index}][tax_breakup]" value='${item.tax_breakup ?? ''}'>
                                    </td>
                                    <td>
                                        <!-- <a href="javascript:;" class="text-danger deleteExpRow">
                                            <i class="fa fa-trash"></i>
                                        </a> -->
                                    </td>
                                </tr>`;
                        });
                        if (!$(".display_summary_exp_row").length) {
                            $("#summaryExpTable #expSummaryFooter").before(tr);
                        } else {
                            $(".display_summary_exp_row:last").after(tr);
                        }
                        $("#f_header_expense_hidden").removeClass('d-none');
                        $("#new_exp_name_select").val("");
                        $("#new_exp_id").val("");
                        $("#new_exp_name").val("");
                        $("#new_exp_perc").val("").prop("readonly", false);
                        $("#new_exp_value").val("").prop("readonly", false);
                        let total_head_exp = 0;
                        $("[name*='[total]']").each(function (index, item) {
                            total_head_exp += Number($(item).val());
                        });

                        $("#expSummaryFooter #total").text(total_head_exp.toFixed(2));
                        summaryExpTotal();
                    } else {
                        $("#f_header_expense_hidden").addClass('d-none');
                    }

                    setTimeout(() => {
                        setTableCalculation();
                        $("#itemTable .mrntableselectexcel tr").each((index, item) => {
                            setAttributesUIHelper(index + 1, "#itemTable");
                        });
                    }, 500);
                    const firstPaymentId   = Array.isArray(asnData.payment_ids) && asnData.payment_ids.length > 0
                        ? asnData.payment_ids[0]
                        : '';
                    const firstPaymentTerm = Array.isArray(asnData.payment_terms) && asnData.payment_terms.length > 0
                        ? asnData.payment_terms[0]
                        : '';
                    const firstCreditDays  = Array.isArray(asnData.credit_days) && asnData.credit_days.length > 0
                        ? asnData.credit_days[0]
                        : 0;
                    let payOption = '';
                    if (firstPaymentId && firstPaymentTerm) {
                        payOption = `<option value="${firstPaymentId}">${firstPaymentTerm}</option>`;
                    }
                    $('[name="payment_term_id"]').empty().append(payOption);
                    $('[name="credit_days"]').val(firstCreditDays);

                })
                .catch(() => {
                    Swal.fire({
                        title: 'Error!',
                        text: 'An unexpected error occurred while processing ASN.',
                        icon: 'error'
                    });
                });
        }

        function handleAsnError(message = 'Invalid data') {
            $(".editAddressBtn").removeClass('d-none');
            $("#vendor_name").val('').prop('readonly', false);
            $("#vendor_id, #vendor_code, #hidden_state_id, #hidden_country_id").val('');
            $("select[name='currency_id'], select[name='payment_term_id']").prop('readonly', false).html('<option value="">Select</option>');
            $(".shipping_detail, .billing_detail").text('-');
            $("#reference_from").removeClass('d-none');
            $('.asn_process').prop('disabled', false);

            Swal.fire({
                title: 'Error!',
                text: message,
                icon: 'error'
            });
        }

        // Call once on load, and whenever the inspection_required control changes
        function applyInspectionState() {
            const inspectionRequired = $('.inspection_required').val() === 'yes';

            $('tr[id^="row_"]').each(function () {
                const $row = $(this);
                const $order   = $row.find('input[name*="[order_qty]"]');
                const $accepted = $row.find('input.accepted_qty');
                const $rejected = $row.find('input.rejected_qty');

                const orderQty = parseFloat($order.val()) || 0;

                if (inspectionRequired) {
                // Required: accepted = 0; lock both fields
                $accepted.val(0).prop('readonly', true);
                $rejected.prop('readonly', true);
                // (Optional) also zero rejected if you want:
                // $rejected.val(0);
                } else {
                // Not required: accepted = order qty; unlock both fields
                $accepted.val(orderQty).prop('readonly', true);
                $rejected.prop('readonly', true);
                }
            });
        }

        // When page loads
        $(document).ready(function () {
            applyInspectionState();
        });
    </script>
@endsection
