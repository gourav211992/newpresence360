@extends('layouts.app')
@section('styles')
    <style>
        .tooltip-inner {
            text-align: left
        }
    </style>
@endsection
@section('content')
    <form id="mrnEditForm" data-module="insp" class="ajax-input-form" method="POST"
        action="{{ route('inspection.update', $mrn->id) }}" data-redirect="/inspection" enctype="multipart/form-data">
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
                                    <h2 class="content-header-title float-start mb-0">
                                        {{ $servicesBooks['services'][0]->name ?? 'Inspection' }}
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
                                <input type="hidden" name="document_status" value="{{ $mrn->document_status }}"
                                    id="document_status">
                                <button type="button" onClick="javascript: history.go(-1)"
                                    class="btn btn-secondary btn-sm mb-50 mb-sm-0">
                                    <i data-feather="arrow-left-circle"></i> Back
                                </button>
                                @if (
                                    !intval(request('amendment') ?? 0) &&
                                        $mrn->document_status != \App\Helpers\ConstantHelper::DRAFT &&
                                        $mrn->document_status != \App\Helpers\ConstantHelper::SUBMITTED &&
                                        $mrn->document_status != \App\Helpers\ConstantHelper::PARTIALLY_APPROVED)
                                    <a href="{{ route('inspection.generate-pdf', $mrn->id) }}" target="_blank"
                                        class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer">
                                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                            <path
                                                d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2">
                                            </path>
                                            <rect x="6" y="14" width="12" height="8"></rect>
                                        </svg>
                                        Print
                                    </a>
                                    <a href="{{ route('barcodes.page', $mrn->mrn_header_id) . '?module_type=' . $mrnServiceAlias . '&reference_id=' . $mrn->id }}"
                                        target="_blank"
                                        class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer">
                                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                            <path
                                                d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2">
                                            </path>
                                            <rect x="6" y="14" width="12" height="8"></rect>
                                        </svg>
                                        Print Labels
                                    </a>
                                @endif
                                @if ($buttons['draft'])
                                    <button type="submit"
                                        class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" name="action"
                                        value="draft">
                                        <i data-feather='save'></i> Save as Draft
                                    </button>
                                @endif
                                @if ($buttons['submit'])
                                    <button type="submit" class="btn btn-primary btn-sm submit-button" name="action"
                                        value="submitted">
                                        <i data-feather="check-circle"></i> Submit
                                    </button>
                                @endif
                                @if ($buttons['approve'])
                                    <button type="button" class="btn btn-primary btn-sm" id="approved-button"
                                        name="action" value="approved"><i data-feather="check-circle"></i> Approve</button>
                                    <button type="button" id="reject-button"
                                        class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg
                                            xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="15" y1="9" x2="9" y2="15"></line>
                                            <line x1="9" y1="9" x2="15" y2="15"></line>
                                        </svg> Reject</button>
                                @endif
                                @if ($buttons['amend'] && intval(request('amendment') ?? 0))
                                    <button type="button" class="btn btn-primary btn-sm" id="amendmentBtn"><i
                                            data-feather="check-circle"></i> Submit</button>
                                @else
                                    @if ($buttons['amend'])
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#amendmentconfirm"
                                            class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i>
                                            Amendment</button>
                                    @endif
                                @endif
                                @if ($buttons['revoke'])
                                    <button id="revokeButton" type="button"
                                        class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='rotate-ccw'></i>
                                        Revoke</button>
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
                                    <div class="card-body customernewsection-form">
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
                                            <div class="col-md-6 text-sm-end">
                                                <span
                                                    class="badge rounded-pill badge-light-{{ $mrn->display_status === 'Posted' ? 'info' : 'secondary' }} forminnerstatus">
                                                    <span class="text-dark">Status</span> : <span
                                                        class="{{ $docStatusClass }}">{{ $mrn->display_status }}</span>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Series <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="hidden" name="book_id" class="form-control"
                                                            id="book_id" value="{{ $mrn->book_id }}" readonly>
                                                        <input readonly type="text" class="form-control"
                                                            value="{{ $mrn->book->book_code }}" id="book_code">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Document No <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" readonly
                                                            value="{{ @$mrn->document_number }}" id="document_number">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Document Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" id="document_date" name="document_date"
                                                            class="form-control"
                                                            value="{{ date('Y-m-d', strtotime($mrn->document_date)) }}"
                                                            readonly>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Location <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select header_store_id" id="header_store_id"
                                                            name="header_store_id" readonly>
                                                            @foreach ($locations as $erpStore)
                                                                <option value="{{ $erpStore->id }}"
                                                                    {{ $mrn->store_id == $erpStore->id ? 'selected' : '' }}>
                                                                    {{ ucfirst($erpStore->store_name) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">
                                                            Main Store <span class="text-danger">*</span>
                                                        </label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select sub_store" id="sub_store_id"
                                                            name="sub_store_id" readonly>
                                                            <option value="{{ $mrn->sub_store_id }}">
                                                                {{ ucfirst($mrn?->erpSubStore?->name) }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Rejected Store</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select rejected_sub_store_id"
                                                            id="rejected_sub_store_id" name="rejected_sub_store_id"
                                                            readonly>
                                                            <option value="{{ $mrn->rejected_sub_store_id }}">
                                                                {{ ucfirst($mrn?->rejectedSubStore?->name) }}
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                                @if ($mrn->document_status == 'draft' || $mrn->document_status == 'rejected')
                                                    <div class="row align-items-center mb-1 d-none" id="reference_from">
                                                        <div class="col-md-3">
                                                            <label class="form-label">
                                                                Reference From
                                                            </label>
                                                        </div>
                                                        <div class="col-md-5 action-button">
                                                            <button type="button"
                                                                class="btn btn-outline-primary btn-sm mb-0 mrnSelect">
                                                                <i data-feather="plus-square"></i>
                                                                Outstanding GRN
                                                            </button>
                                                            <input type="hidden" name="module_type" id="module_type"
                                                                class="module_type" value="mrn">
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1" id="referenceNoDiv"
                                                        style="display: none;">
                                                        <div class="col-md-5">
                                                            <input type="hidden" name="reference_type"
                                                                class="form-control reference_type"
                                                                id="reference_type_input" readonly>
                                                            <input type="hidden" name="mrn_header_id"
                                                                class="form-control" value="{{ $mrn->mrn_header_id }}">
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                            {{-- Approval History Section --}}
                                            @include('partials.approval-history', [
                                                'document_status' => $mrn->document_status,
                                                'revision_number' => $revision_number,
                                            ])
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
                                                            <label class="form-label">Vendor <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" placeholder="Select"
                                                                class="form-control mw-100 ledgerselecct" id="vendor_name"
                                                                name="vendor_name"
                                                                {{ count($mrn->items) > 0 ? 'readonly' : '' }}
                                                                value="{{ @$mrn->vendor->company_name }}" />
                                                            <input type="hidden" value="{{ @$mrn->vendor_id }}"
                                                                id="vendor_id" name="vendor_id" />
                                                            <input type="hidden" value="{{ @$mrn->vendor_code }}"
                                                                id="vendor_code" name="vendor_code" />
                                                            @if ($mrn->latestBillingAddress() || $mrn->latestBillingAddress())
                                                                <input type="hidden"
                                                                    value="{{ $mrn->latestBillingAddress() }}"
                                                                    id="shipping_id" name="shipping_id" />
                                                                <input type="hidden" id="billing_id"
                                                                    value="{{ $mrn->latestBillingAddress()->id }}"
                                                                    name="billing_id" />
                                                                <input type="hidden"
                                                                    value="{{ $mrn->latestBillingAddress()->state?->id }}"
                                                                    id="hidden_state_id" name="hidden_state_id" />
                                                                <input type="hidden"
                                                                    value="{{ $mrn->latestBillingAddress()->country?->id }}"
                                                                    id="hidden_country_id" name="hidden_country_id" />
                                                            @else
                                                                <input type="hidden" value="{{ $mrn->ship_to }}"
                                                                    id="shipping_id" name="shipping_id" />
                                                                <input type="hidden" id="billing_id"
                                                                    value="{{ $mrn->billing_to }}" name="billing_id" />
                                                                <input type="hidden"
                                                                    value="{{ $mrn?->shippingAddress?->state?->id }}"
                                                                    id="hidden_state_id" name="hidden_state_id" />
                                                                <input type="hidden"
                                                                    value="{{ $mrn?->shippingAddress?->country?->id }}"
                                                                    id="hidden_country_id" name="hidden_country_id" />
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Currency <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select" name="currency_id">
                                                                <option value="{{ @$mrn->currency_id }}">
                                                                    {{ @$mrn->currency->name }}
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Payment Terms <span
                                                                    class="text-danger">*</span></label>
                                                            <select class="form-select" name="payment_term_id">
                                                                <option value="{{ @$mrn->payment_term_id }}">
                                                                    {{ @$mrn->paymentTerm->name }}
                                                                </option>
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
                                                                    <label class="form-label w-100">Vendor Address <span
                                                                            class="text-danger">*</span> <a
                                                                            href="javascript:;"
                                                                            class="float-end font-small-2 editAddressBtn d-none"
                                                                            data-type="billing"><i
                                                                                data-feather='edit-3'></i> Edit</a></label>
                                                                    <div class="mrnaddedd-prim billing_detail">
                                                                        @if ($mrn->latestBillingAddress())
                                                                            {{ $mrn->latestBillingAddress()->display_address }}
                                                                        @else
                                                                            {{ $mrn->bill_address?->display_address }}
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
                                                                    <label class="form-label w-100">Billing Address <span
                                                                            class="text-danger">*</span>
                                                                        {{-- <a href="javascript:;"
                                                                            class="float-end font-small-2 editAddressBtn"
                                                                            data-type="billing"><i
                                                                                data-feather='edit-3'></i> Edit</a> --}}
                                                                    </label>
                                                                    <div class="mrnaddedd-prim org_address">
                                                                        {{ $deliveryAddress }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="customer-billing-section h-100">
                                                            <p>Delivery Address</p>
                                                            <div class="bilnbody">
                                                                <div class="genertedvariables genertedvariablesnone">
                                                                    <label class="form-label w-100">Delivery Address <span
                                                                            class="text-danger">*</span>
                                                                        {{-- <a href="javascript:;"
                                                                            class="float-end font-small-2 editAddressBtn"
                                                                            data-type="billing"><i
                                                                                data-feather='edit-3'></i> Edit</a> --}}
                                                                    </label>
                                                                    <div class="mrnaddedd-prim delivery_address">
                                                                        {{ $deliveryAddress }}
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
                                                                Gate Entry No.
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="text" name="gate_entry_no"
                                                                class="form-control" value="{{ @$mrn->gate_entry_no }}"
                                                                placeholder="Enter Gate Entry no" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Gate Entry Date
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="date" name="gate_entry_date"
                                                                value="{{ $mrn->gate_entry_date ? date('Y-m-d', strtotime($mrn->gate_entry_date)) : '' }}"
                                                                class="form-control gate-entry" id="datepicker2"
                                                                placeholder="Enter Gate Entry Date" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                E-Way Bill No.
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="text" name="eway_bill_no"
                                                                value="{{ @$mrn->eway_bill_no }}" class="form-control"
                                                                placeholder="Enter Eway Bill No." readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Consignment No.
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="text" name="consignment_no"
                                                                value="{{ @$mrn->consignment_no }}" class="form-control"
                                                                placeholder="Enter Consignment No." readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Supplier Invoice No.
                                                            </label>
                                                            <input type="text" name="supplier_invoice_no"
                                                                value="{{ @$mrn->supplier_invoice_no }}"
                                                                class="form-control"
                                                                placeholder="Enter Supplier Invoice No." readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Supplier Invoice Date
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="date" name="supplier_invoice_date"
                                                                value="{{ date('Y-m-d', strtotime($mrn->supplier_invoice_date)) }}"
                                                                class="form-control gate-entry" id="datepicker3"
                                                                placeholder="Enter Supplier Invoice Date" readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Transporter Name
                                                            </label>
                                                            <input type="text" name="transporter_name"
                                                                value="{{ @$mrn->transporter_name }}"
                                                                class="form-control" placeholder="Enter Transporter Name"
                                                                readonly>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Vehicle No.
                                                                <i class="ml-2 fas fa-info-circle text-primary"
                                                                    data-bs-toggle="tooltip" data-bs-html="true"
                                                                    title="Format:<br>[A-Z]{2} – 2 uppercase letters (e.g., 'MH')<br>[0-9]{2} – 2 digits (e.g., '12')<br>[A-Z]{0,3} – 0 to 3 uppercase letters (e.g., 'AB', 'ABZ')<br>[0-9]{4} – 4 digits (e.g., '1234')"></i>
                                                            </label>
                                                            <input type="text" name="vehicle_no"
                                                                class="form-control vehicle_no"
                                                                value="{{ @$mrn->vehicle_no }}"
                                                                placeholder="Enter Vehicle No." readonly />
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
                                                <div class="col-md-6 text-sm-end">
                                                    <a href="javascript:;" id="deleteBtn"
                                                        class="btn btn-sm btn-outline-danger me-50">
                                                        <i data-feather="x-circle"></i> Delete
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="table-responsive pomrnheadtffotsticky">
                                                    <table id="itemTable"
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"
                                                        data-json-key="components_json"
                                                        data-row-selector="tr[id^='row_']">
                                                        <thead>
                                                            <tr>
                                                                <th class="customernewsection-form">
                                                                    <div
                                                                        class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input"
                                                                            id="Email">
                                                                        <label class="form-check-label"
                                                                            for="Email"></label>
                                                                    </div>
                                                                </th>
                                                                <th width="150px">Item Code</th>
                                                                <th width="240px">Item Name</th>
                                                                <th>Attributes</th>
                                                                <th>UOM</th>
                                                                <th>Batch</th>
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
                                                                <td rowspan="10" colspan="11">
                                                                    <table class="table border">
                                                                        <tbody id="itemDetailDisplay">
                                                                            <tr>
                                                                                <td class="p-0">
                                                                                    <h6
                                                                                        class="text-dark mb-0 bg-light-primary py-1 px-50">
                                                                                        <strong>Item Details</strong>
                                                                                    </h6>
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
                                                                <input type="file" name="attachment[]"
                                                                    class="form-control"
                                                                    onchange="addFiles(this,'main_mrn_preview')" multiple>
                                                                <span
                                                                    class="text-primary small">{{ __('message.attachment_caption') }}</span>
                                                            </div>
                                                        </div>
                                                        @include('partials.document-preview', [
                                                            'documents' => $mrn->getDocuments(),
                                                            'document_status' => $mrn->document_status,
                                                            'elementKey' => 'main_mrn_preview',
                                                        ])
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
        <!-- Outstanding MRN Modal  -->
        @include('procurement.inspection.partials.outstanding-mrn-modal')
        <!-- Inspection CHecklist Modal  -->
        @include('procurement.inspection.partials.inspection-checklist-modal')
        {{-- Edit Address --}}
        <div class="modal fade" id="edit-address" tabindex="-1" aria-labelledby="shareProjectTitle"
            aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">
            </div>
        </div>
        @include('procurement.inspection.partials.amendement-modal', ['id' => $mrn->id])
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

    {{-- Item Remark Modal --}}
    <div class="modal fade" id="itemRemarkModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered">
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
    <!-- @include('procurement.inspection.partials.item-location-modal') -->
    <!-- Item Locations Modal End -->

    {{-- Delete component modal --}}
    <div class="modal fade text-start alertbackdropdisabled" id="deleteComponentModal" tabindex="-1"
        aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
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
                    <button type="button" id="deleteConfirm" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve/Reject Modal -->
    @include('procurement.inspection.partials.approve-modal', ['id' => $mrn->id])
    <!-- Inspection CHecklist Modal  -->
    @include('procurement.inspection.partials.inspection-checklist-modal')
    <!-- Batch Detail Modal  -->
    @include('procurement.inspection.partials.item-batch-modal')

    {{-- Amendment Modal --}}
    <div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1"
        aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>Document</strong>? After Amendment
                        this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
    <script type="text/javascript">
        var actionUrlTax = '{{ route('inspection.tax.calculation') }}';
        var qtyChangeUrl = '{{ route('inspection.get.validate-quantity') }}';
    </script>
    <script type="text/javascript" src="{{ asset('assets/js/modules/common-datatable.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/modules/common-attr-ui.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/modules/inspection.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/modules/inspection-item-batch.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/modules/inspection-checklist.js') }}"></script>
    <script type="text/javascript" src="{{ asset('app-assets/js/file-uploader.js') }}"></script>
    <script>
        /*Clear local storage*/
        let tableRowCount = $('.mrntableselectexcel tr').length;
        let currentProcessType = @json($mrn->reference_type);
        window.onload = () => {
            let mrnHeaderId = @json($mrn->mrn_header_id);
            if (mrnHeaderId) {
                currentProcessType = 'mrn';
            }
            $("#reference_type_input").val(currentProcessType);
            if (currentProcessType === null) {
                $(".mrnSelect").hide();
            } else {
                if (currentProcessType === 'mrn') {
                    $(".mrnSelect").show();
                }
            }

            ['selectedMrnIds'].forEach(key => localStorage.removeItem(key));

            let ids = @json($detailsIds);

            if (['mrn'].includes(currentProcessType)) {
                localStorage.setItem(
                    `selected${currentProcessType.charAt(0).toUpperCase() + currentProcessType.slice(1)}Ids`, JSON
                    .stringify(ids));
            }
        };

        let header_id = @json($mrn->mrn_header_id);
        let details_ids = @json($detailsIds);
        /*Clear local storage*/
        setTimeout(() => {
            localStorage.removeItem('deletedMrnItemIds');
        }, 0);

        @if ($buttons['amend'] && intval(request('amendment') ?? 0))
        @else
            @if ($mrn->document_status != 'draft' && $mrn->document_status != 'rejected')
                $(':input').prop('readonly', true);
                $('textarea[name="amend_remark"], input[type="file"][name="amend_attachment[]"]').prop('readonly', false)
                    .prop('disabled', false);
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
                $(document).on('show.bs.modal', function(e) {
                    if (e.target.id != 'approveModal') {
                        if (e.target.id != 'shortCloseModal') {
                            $(e.target).find('.modal-footer').remove();
                        }
                        $('select').not('.amendmentselect select').prop('disabled', true);
                    }
                    if (e.target.id == 'approveModal') {
                        $(e.target).find(':input').prop('readonly', false);
                        $(e.target).find('select').prop('readonly', false);
                    }
                    $('.add-contactpeontxt').remove();
                    let text = $(e.target).find('thead tr:first th:last').text();
                    if (text.includes("Action")) {
                        $(e.target).find('thead tr').each(function() {
                            $(this).find('th:last').remove();
                        });
                        $(e.target).find('tbody tr').each(function() {
                            $(this).find('td:last').remove();
                        });
                    }
                });
            @endif
        @endif

        // Change BookId
        $(document).on('change', '#book_id', (e) => {
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
            let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId + '&document_date=' +
                document_date;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    // console.log(data.data);
                    if (data.status == 200) {
                        const parameters = data.data.parameters;
                        // console.log('parameters', parameters);
                        setServiceParameters(parameters);

                        if (parameters?.tax_required.some(val => val.toLowerCase() === 'yes')) {
                            $("#tax_required").val(parameters?.tax_required[0]);
                        } else {
                            $("#tax_required").val("");
                        }

                    }
                    if (data.status == 404) {
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
        }, 1000);

        /*Set Service Parameter*/
        function setServiceParameters(parameters) {
            /*Date Validation*/
            const docDateInput = $("[name='document_date']");
            let isFeature = false;
            let isPast = false;
            if (parameters.future_date_allowed && parameters.future_date_allowed.includes('yes')) {
                let futureDate = new Date();
                futureDate.setDate(futureDate.getDate() /*+ (parameters.future_date_days || 1)*/ );
                // docDateInput.val(futureDate.toISOString().split('T')[0]);
                docDateInput.attr("min", new Date().toISOString().split('T')[0]);
                isFeature = true;
            } else {
                isFeature = false;
                docDateInput.attr("max", new Date().toISOString().split('T')[0]);
            }
            if (parameters.back_date_allowed && parameters.back_date_allowed.includes('yes')) {
                let backDate = new Date();
                backDate.setDate(backDate.getDate() /*- (parameters.back_date_days || 1)*/ );
                // docDateInput.val(backDate.toISOString().split('T')[0]);
                // docDateInput.attr("max", "");
                isPast = true;
            } else {
                isPast = false;
                docDateInput.attr("min", new Date().toISOString().split('T')[0]);
            }
            /*Date Validation*/
            if (isFeature && isPast) {
                docDateInput.removeAttr('min');
                docDateInput.removeAttr('max');
            }

            /*Reference from*/
            let reference_from_service = parameters.reference_from_service;
            if (reference_from_service.length) {
                let mrn = '{{ \App\Helpers\ConstantHelper::MRN_SERVICE_ALIAS }}';
                if (reference_from_service.includes(mrn)) {
                    $("#reference_from").removeClass('d-none');
                } else {
                    $("#reference_from").addClass('d-none');
                }
                if (reference_from_service.includes('d')) {
                    $("#addNewItemBtn").removeClass('d-none');
                } else {
                    $("#addNewItemBtn").addClass('d-none');
                }
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: "Please update first reference from service param.",
                    icon: 'error',
                });
                setTimeout(() => {
                    location.href = '{{ route('inspection.index') }}';
                }, 1500);
            }
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
                            type: 'vendor_list'
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
            let actionUrl = "{{ route('inspection.get.address') }}" + '?id=' + vendorId + '&store_id=' + store_id;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.data?.currency_exchange?.status == false) {
                        $("#vendor_name").val('');
                        $("#vendor_id").val('');
                        $("#vendor_code").val('');
                        $("#hidden_state_id").val('');
                        $("#hidden_country_id").val('');
                        // $("#vendor_id").trigger('blur');
                        $("select[name='currency_id']").empty().append('<option value="">Select</option>');
                        $("select[name='payment_term_id']").empty().append(
                            '<option value="">Select</option>');
                        $(".shipping_detail").text('-');
                        $(".billing_detail").text('-');
                        Swal.fire({
                            title: 'Error!',
                            text: data.data?.currency_exchange.message,
                            icon: 'error',
                        });
                        return false;
                    }
                    if (data.status == 200) {
                        $("#vendor_name").val(data?.data?.vendor?.company_name);
                        $("#vendor_id").val(data?.data?.vendor?.id);
                        $("#vendor_code").val(data?.data?.vendor.vendor_code);
                        let curOption =
                            `<option value="${data.data.currency.id}">${data.data.currency.name}</option>`;
                        let termOption =
                            `<option value="${data.data.paymentTerm.id}">${data.data.paymentTerm.name}</option>`;
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
                        if (data.data.error_message) {
                            $("#vendor_name").val('');
                            $("#vendor_id").val('');
                            $("#vendor_code").val('');
                            $("#hidden_state_id").val('');
                            $("#hidden_country_id").val('');
                            // $("#vendor_id").trigger('blur');
                            $("select[name='currency_id']").empty().append(
                                '<option value="">Select</option>');
                            $("select[name='payment_term_id']").empty().append(
                                '<option value="">Select</option>');
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
                    $("#itemTable tbody [id*='row_']").each(function(index, item) {
                        if (Number($(item).find('[name*="item_id"]').val())) {
                            selectedAllItemIds.push(Number($(item).find('[name*="item_id"]').val()));
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
                    if (ui.item?.alternate_u_o_ms) {
                        for (let alterItem of ui.item.alternate_u_o_ms) {
                            uomOption +=
                                `<option value="${alterItem.uom_id}" ${alterItem.is_purchasing ? 'selected' : ''}>${alterItem.uom?.name}</option>`;
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
                        rowCount: rowCount
                    }).toString();
                    getItemDetail(closestTr);
                    setTimeout(() => {
                        if (ui.item.is_attr) {
                            $input.closest('tr').find('.attributeBtn').trigger('click');
                        } else {
                            $input.closest('tr').find('.attributeBtn').trigger('click');
                            $input.closest('tr').find('[name*="[order_qty]"]').focus();
                        }
                    }, 100);
                    initializeStationAutocomplete();
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
        $(document).on('click', '#addNewItemBtn', (e) => {
            // for component item code
            let storeLocation = $('.header_store_id').val();
            updateItemStores();
            getSubStores(storeLocation);
            var supplierName = $('#vendor_name').val();
            if (!supplierName) {
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
                item_id: "",
                attr_require: true,
                row_length: lastRow.length
            };

            if (lastRow.length == 0) {
                lastTrObj.attr_require = false;
                lastTrObj.item_id = "0";
            }

            if (lastRow.length > 0) {
                let item_id = lastRow.find("[name*='item_id']").val();
                if (lastRow.find("[name*='attr_name']").length) {
                    var emptyElements = lastRow.find("[name*='attr_name']").filter(function() {
                        return $(this).val().trim() === '';
                    });
                    attr_require = emptyElements?.length ? true : false;
                } else {
                    attr_require = true;
                }

                lastTrObj = {
                    item_id: item_id,
                    attr_require: attr_require,
                    row_length: lastRow.length
                };

                if ($("tr[id*='row_']:last").find("[name*='[attr_group_id]']").length == 0 && item_id) {
                    lastTrObj.attr_require = false;
                }
            }

            let actionUrl = '{{ route('inspection.item.row') }}' + '?count=' + rowsLength + '&component_item=' +
                JSON.stringify(lastTrObj);
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
                    } else if (data.status == 422) {
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
        $(document).on('click', '#deleteBtn', (e) => {
            let itemIds = [];
            let editItemIds = [];
            let poItemIds = [];
            $('.form-check-input:checked').each(function(index, item) {
                let tr = $(item).closest('tr');
                let trIndex = tr.index();
                let po_detail_id = Number($(tr).find('[name*="[po_detail_id]"]').val()) || 0;
                let mrn_detail_id = Number($(tr).find('[name*="[mrn_detail_id]"]').val()) || 0;
                if (po_detail_id > 0 && mrn_detail_id > 0) {
                    poItemIds.push({
                        index: trIndex + 1,
                        po_detail_id: po_detail_id
                    });
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
                    if ($(this).attr('data-id')) {
                        editItemIds.push($(this).attr('data-id'));
                    } else {
                        itemIds.push($(this).val());
                    }
                }
            });
            if (itemIds.length) {
                itemIds.forEach(function(item, index) {
                    $(`#row_${item}`).remove();
                });
            }
            if (editItemIds.length == 0 && itemIds.length == 0) {
                Swal.fire({
                    title: 'Error!',
                    text: "Please first add & select row item.",
                    icon: 'error',
                });
                return false;
            }
            if (editItemIds.length) {
                $("#deleteComponentModal").find("#deleteConfirm").attr('data-ids', JSON.stringify(editItemIds));
                $("#deleteComponentModal").modal('show');
            }
            if (!$("tr[id*='row_']").length) {
                $("#itemTable > thead .form-check-input").prop('checked', false);
                // $(".prSelect").prop('disabled',false);
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
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select first item name.',
                    icon: 'error',
                });
                return false;
            }
        });

        /*For comp attr*/
        function getItemAttribute(itemId, rowCount, selectedAttr, tr) {
            let actionUrl = '{{ route('inspection.item.attr') }}' + '?item_id=' + itemId +
                `&rowCount=${rowCount}&selectedAttr=${selectedAttr}`;
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
        $(document).on('change focus', '#itemTable tr input ', function(e) {
            let currentTr = e.target.closest('tr');
            getItemDetail(currentTr);
        });

        function getItemDetail(currentTr) {
            let pName = $(currentTr).find("[name*='component_item_name']").val() || '';
            let itemId = $(currentTr).find("[name*='item_id']").val() || '';
            let mrnHeaderId = $(currentTr).find("[name*='mrn_header_id']").val() || '';
            let mrnDetailId = $(currentTr).find("[name*='mrn_detail_id']").val() || '';
            let storeId = $(currentTr).find("[name*='header_store_id']").val() || '';
            let subStoreId = $(currentTr).find("[name*='sub_store_id']").val() || '';
            let remark = '';
            if ($(currentTr).find("[name*='remark']")) {
                remark = $(currentTr).find("[name*='remark']").val() || '';
            }

            if (itemId) {
                let selectedAttr = [];
                $(currentTr).find("[name*='attr_name']").each(function(index, item) {
                    if ($(item).val()) {
                        selectedAttr.push($(item).val());
                    }
                });
                let uomId = $(currentTr).find("[name*='[uom_id]']").val() || '';
                let qty = $(currentTr).find("[name*='[accepted_qty]']").val() || '';
                let headerId = $(currentTr).find("[name*='inspection_header_id']").val() ?? '';
                let detailId = $(currentTr).find("[name*='inspection_dtl_id']").val() ?? '';
                let actionUrl = '{{ route('inspection.get.itemdetail') }}' + '?item_id=' + itemId +
                    '&mrn_header_id=' + mrnHeaderId +
                    '&mrn_detail_id=' + mrnDetailId +
                    '&selectedAttr=' + JSON.stringify(selectedAttr) +
                    '&remark=' + remark +
                    '&uom_id=' + uomId +
                    '&qty=' + qty +
                    '&headerId=' + headerId +
                    '&detailId=' + detailId +
                    '&store_id=' + storeId +
                    '&sub_store_id=' + subStoreId;

                fetch(actionUrl).then(response => {
                    return response.json().then(data => {
                        if (data.status == 200) {
                            // Update the modal or display section
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
            let actionUrl =
                `{{ route('inspection.edit.address') }}?type=${addressType}&vendor_id=${vendorId}&address_id=${addressId}&onChange=${onChange}`;
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
            let actionUrl =
                `{{ route('inspection.edit.address') }}?type=${addressType}&vendor_id=${vendorId}&address_id=${addressId}&onChange=${onChange}`;
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

            countrySelect.on('change', function() {
                let countryValue = $(this).val();
                let stateSelect = $('#state_id');
                stateSelect.empty().append('<option value="">Select State</option>'); // Reset state dropdown

                if (countryValue) {
                    fetch(`/states/${countryValue}`)
                        .then(response => response.json())
                        .then(data => {
                            data.data.states.forEach(state => {
                                const isSelected = state.value === selectedAddress.state.id;
                                stateSelect.append(new Option(state.label, state.value, isSelected,
                                    isSelected));
                            });
                            if (selectedAddress.state.value) {
                                stateSelect.trigger('change');
                            }
                        })
                        .catch(error => console.error('Error fetching states:', error));
                }
            });
            $('#state_id').on('change', function() {
                let stateValue = $(this).val();
                let citySelect = $('#city');
                citySelect.empty().append('<option value="">Select City</option>');
                if (stateValue) {
                    fetch(`/cities/${stateValue}`)
                        .then(response => response.json())
                        .then(data => {
                            data.data.cities.forEach(city => {
                                const isSelected = city.value === selectedAddress.city.id;
                                citySelect.append(new Option(city.label, city.value, isSelected,
                                    isSelected));
                            });
                        })
                        .catch(error => console.error('Error fetching cities:', error));
                }
            });
            $("#pincode").val(selectedAddress.pincode);
            $("#address").val(selectedAddress.address);
        }

        /* Address Submit */
        $(document).on('click', '.submitAddress', function(e) {
            $('.ajax-validation-error-span').remove();
            e.preventDefault();
            var innerFormData = new FormData();
            $('#edit-address').find('input,textarea,select').each(function() {
                innerFormData.append($(this).attr('name'), $(this).val());
            });
            var method = "POST";
            var url = '{{ route('inspection.address.save') }}';
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
                    if (data.status == 200) {
                        let addressType = $("#address_type").val();
                        if (addressType == 'shipping') {
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
                });
            }

            if (!$("#itemTable [id*=row_]").length) {
                $("th .form-check-input").prop('checked', false);
                $('#vendor_name').prop('readonly', false);
                $("#editBillingAddressBtn").show();
                $("#editShippingAddressBtn").show();
            }
        });

        /*Amendment modal open*/
        $(document).on('click', '.amendmentBtn', (e) => {
            $("#amendmentconfirm").modal('show');
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
            let actionUrl = location.pathname + '?revisionNumber=' + e.target.value;
            let revision_number = Number("{{ $revision_number }}");
            let revisionNumber = Number(e.target.value);
            if (revision_number == revisionNumber) {
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
            if (!remark) {
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

        // Revoke Document
        $(document).on('click', '#revokeButton', (e) => {
            let actionUrl = '{{ route('inspection.revoke.document') }}' + '?id=' + '{{ $mrn->id }}';
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 'error') {
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

        /*Open GRN model*/
        let mrnOrderTable;
        $(document).on('click', '.mrnSelect', (e) => {
            tableRowCount = $('.mrntableselectexcel tr').length;
            $("#mrnModal").modal('show');
            currentProcessType = 'mrn';
            openMrnRequest();
            const tableSelector = '#mrnModal .mrn-order-detail';
            $(tableSelector).DataTable().clear().destroy();
            getMrn();
            if ($(tableSelector).length) {
                if ($.fn.DataTable.isDataTable(tableSelector)) {
                    mrnOrderTable = $(tableSelector).DataTable();
                    mrnOrderTable.ajax.reload();
                }
            }
        });

        function getSelectedMrnTypes() {
            let moduleTypes = [];
            $('.mrn_item_checkbox:checked').each(function() {
                moduleTypes.push($(this).attr(
                    'data-module')); // Corrected: Get attribute value instead of setting it
            });
            return moduleTypes;
        }

        function openMrnRequest() {
            initializeAutocompleteQt("vendor_code_input_qt", "vendor_id_qt_val", "vendor_list", "vendor_code",
                "company_name");
            initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "mrn_document_qt", "document_number",
                "");
            initializeAutocompleteQt("so_no_input_qt", "so_qt_val", "so_qt", "book_code", "document_number");
        }

        function initializeAutocompleteQt(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "") {
            let modalType = '#mrnModal';
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
                            store_id: $("#store_id").val() || '',
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
                                    label = `${item.book_code}-${item.document_number}`;
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
                    $('#mrnModal .mrn-order-detail').DataTable().ajax.reload();
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#" + selectorSibling).val("");
                        $('#mrnModal .mrn-order-detail').DataTable().ajax.reload();
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $("#" + selectorSibling).val("");
                    $('#mrnModal .mrn-order-detail').DataTable().ajax.reload();
                    $(this).autocomplete("search", "");
                }
            }).blur(function() {
                if (this.value === "") {
                    $("#" + selectorSibling).val("");
                    $('#mrnModal .mrn-order-detail').DataTable().ajax.reload();
                }
            })
        }

        function renderData(data) {
            return data ? data : '';
        }

        function getDynamicParams() {
            let document_date = '',
                header_book_id = '',
                series_id = '',
                document_number = '',
                item_id = '',
                vendor_id = '',
                store_id = '',
                so_id = '',
                item_search = '',
                selected_mrn_ids = '';

            if (currentProcessType === 'mrn') {
                let selectedMrnIds = localStorage.getItem('selectedMrnIds') ?? '[]';
                selectedMrnIds = JSON.parse(selectedMrnIds);
                selectedMrnIds = encodeURIComponent(JSON.stringify(selectedMrnIds));
                document_date = $("[name ='document_date']").val() || '',
                    header_book_id = $("#book_id").val() || '',
                    series_id = $("#book_id_qt_val").val() || '',
                    document_number = $("#document_id_qt_val").val() || '',
                    item_id = $("#item_id_qt_val").val() || '',
                    vendor_id = $("#vendor_id_qt_val").val(),
                    store_id = $(".header_store_id").val() || '',
                    sub_store_id = $(".sub_store").val() || '',
                    so_id = $("#po_so_qt_val").val() || '',
                    item_search = $("#item_name_search").length ? $("#item_name_search").val() : '';
                selected_mrn_ids = encodeURIComponent(selectedMrnIds)
            }
            return {
                so_id: so_id,
                type: 'edit',
                item_id: item_id,
                store_id: store_id,
                series_id: series_id,
                vendor_id: vendor_id,
                item_search: item_search,
                sub_store_id: sub_store_id,
                document_date: document_date,
                header_book_id: header_book_id,
                document_number: document_number,
                selected_mrn_ids: selected_mrn_ids,
                details_ids: encodeURIComponent(details_ids),
                header_id: encodeURIComponent(header_id),
            };
        }

        function getMrn() {
            const ajaxUrl = '{{ route('inspection.get.mrn', ['type' => 'edit']) }}';
            var columns = [];
            columns = [{
                    data: 'id',
                    visible: false,
                    orderable: true,
                    searchable: false
                },
                {
                    data: 'select_checkbox',
                    name: 'select_checkbox',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'vendor',
                    name: 'vendor',
                    render: renderData,
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'doc_no',
                    name: 'doc_no',
                    render: renderData,
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'doc_date',
                    name: 'doc_date',
                    render: renderData,
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'item_code',
                    name: 'item_code',
                    render: renderData,
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'item_name',
                    name: 'item_name',
                    render: renderData,
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'attributes',
                    name: 'attributes',
                    render: renderData,
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'order_qty',
                    name: 'order_qty',
                    render: renderData,
                    orderable: false,
                    searchable: false,
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                {
                    data: 'inspection_qty',
                    name: 'inspection_qty',
                    render: renderData,
                    orderable: false,
                    searchable: false,
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                {
                    data: 'balance_qty',
                    name: 'balance_qty',
                    render: renderData,
                    orderable: false,
                    searchable: false,
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
            ];
            initializeDataTableCustom('#mrnModal .mrn-order-detail',
                ajaxUrl,
                columns,
            );
        }

        $(document).on('keyup', '#item_name_search', (e) => {
            $('#mrnModal .mrn-order-detail').DataTable().ajax.reload();
        });

        /*Checkbox for po/si item list*/
        $(document).on('change', '.mrn-order-detail > thead .form-check-input', (e) => {
            if (e.target.checked) {
                $(".mrn-order-detail > tbody .form-check-input").each(function() {
                    $(this).prop('checked', true);
                });
            } else {
                $(".mrn-order-detail > tbody .form-check-input").each(function() {
                    $(this).prop('checked', false);
                });
            }
        });

        function getSelectedMrnIDS() {
            let ids = [];
            let referenceNos = [];

            $('.mrn_item_checkbox:checked').each(function() {
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

        $(document).on('click', '.mrnProcess', (e) => {
            let result = getSelectedMrnIDS();
            let ids = result.ids;
            let referenceNo = result.referenceNos[0];
            let idsLength = ids.length;
            currentProcessType = 'mrn';
            if (!ids.length) {
                $("#mrnModal").modal('hide');
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select at least one one mrn',
                    icon: 'error',
                });
                return false;
            }

            let moduleTypes = getSelectedMrnTypes();
            $("[name='mrn_item_ids']").val(ids);
            $("#addNewItemBtn").hide();
            if (referenceNo) {
                $("#referenceNoDiv").show();
                $("#reference_number_input").val(referenceNo);
            } else {
                $("#referenceNoDiv").hide();
                $("#reference_number_input").val('');
            }
            $("#reference_type_input").val('mrn');

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
                type: 'mrn',
                module_type: moduleTypes,
            };

            asnProcess(processData, 'mrn-process');
        });

        // Clear GRN Process
        $(document).on('click', '.clearMrnFilter', (e) => {
            $("#item_name_input_qt").val('');
            $("#item_id_qt_val").val('');
            $("#vendor_code_input_qt").val('');
            $("#vendor_id_qt_val").val('');
            $("#book_code_input_qt").val('');
            $("#book_id_qt_val").val('');
            $("#document_no_input_qt").val('');
            $("#document_id_qt_val").val('');
            $("#so_no_input_qt").val('');
            $("#so_qt_val").val('');
            $("#item_name_search").val('');
            $('#mrnModal .mrn-order-detail').DataTable().ajax.reload();
        });

        $(document).on("autocompletechange autocompleteselect", "#store", function(event, ui) {
            let storeId = ui?.item?.id || '';
            initializeAutocompleteQt("sub_store", "sub_store_id", "sub_store", "name", "");
        });

        setTimeout(() => {
            $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
                let currentIndex = index + 1;
                setAttributesUIHelper(currentIndex, "#itemTable");
            });
        }, 100);

        // GRN Process
        function asnProcess(asnData, moduleProcess) {
            const current_row_count = $("tbody tr[id*='row_']").length;

            const ids = JSON.stringify(asnData.ids);
            const moduleTypes = JSON.stringify(asnData.module_type);
            const moduleType = asnData.module_type?.[0] ?? 'mrn';
            const processType = asnData.type;
            const process_store_id = asnData.store_id;
            const process_sub_store_id = asnData.sub_store_id;
            const return_type = asnData.return_type;
            let idsLength = ids.length;

            const currencyId = $("[name='currency_id']").val();
            const transactionDate = $("[name='document_date']").val();
            const type = $("meta[name='route-type']").attr("content"); // blade->meta

            const baseRoute = '{{ route('inspection.process.mrn-item') }}';
            const actionUrl = baseRoute
                .replace(':type', type) +
                '?ids=' + encodeURIComponent(ids) +
                '&moduleTypes=' + moduleTypes +
                '&store_id=' + process_store_id +
                '&sub_store_id=' + process_sub_store_id +
                '&return_type=' + return_type +
                '&tableRowCount=' + tableRowCount +
                '&currency_id=' + encodeURIComponent(currencyId) +
                '&d_date=' + encodeURIComponent(transactionDate) +
                '&current_row_count=' + current_row_count;

            fetch(actionUrl)
                .then(res => res.json())
                .then(data => {
                    if (data.status !== 200) return handleProcessError(data.message);

                    const {
                        vendor,
                        pos,
                        mrnHeader,
                        moduleType,
                        finalDiscounts,
                        finalExpenses,
                        subStoreCount
                    } = data.data;

                    const modelType = 'mrn';
                    const order = data.data.mrnHeader;
                    $("#reference_type_input").val(modelType);
                    // console.log(vendor?.id, modelType, order.id);

                    vendorOnChange(vendor?.id, modelType, order.id);

                    const getSelectedIdsFn = getSelectedMrnIDS;
                    const hiddenFieldName = 'mrn_item_ids';
                    const localStorageKey = 'selectedMrnIds';

                    const newIds = getSelectedIdsFn().ids;
                    const existingIds = JSON.parse(localStorage.getItem(localStorageKey) || '[]');
                    const mergedIds = Array.from(new Set([...existingIds, ...newIds]));
                    localStorage.setItem(localStorageKey, JSON.stringify(mergedIds));
                    $(`[name='${hiddenFieldName}']`).val(mergedIds.join(','));

                    $(".module_type").val(modelType);

                    if ($("#itemTable .mrntableselectexcel").find("tr[id*='row_']").length) {
                        $("#itemTable .mrntableselectexcel tr[id*='row_']:last").after(pos);
                    } else {
                        $("#itemTable .mrntableselectexcel").empty().append(pos);
                    }
                    initializeAutocomplete2(".comp_item_code");
                    $("#mrnModal").modal('hide');

                    // UI Locks
                    $("select[name='currency_id'], select[name='payment_term_id']").prop('disabled', true);
                    $("#vendor_name").prop('readonly', true);
                    $("select[name='book_id'], select[name='return_type'], select[name='header_store_id'], select[name='sub_store_id']")
                        .prop('disabled', true);
                    $(".editAddressBtn").addClass('d-none');
                    let locationId = $("[name='header_store_id']").val();
                    getCostCenters(locationId, true);

                    if (finalDiscounts.length) {
                        let rows = '';
                        finalDiscounts.forEach(function(item, index) {
                            index = index + 1;
                            rows += `<tr class="display_summary_discount_row">
                                                        <td>${index}</td>
                                                        <td>${item.ted_name}
                                                            <input type="hidden" value="${item.ted_id}" name="disc_summary[${index}][ted_d_id]">
                                                            <input type="hidden" value="" name="disc_summary[${index}][d_id]">
                                                            <input type="hidden" value="${item.ted_name}" name="disc_summary[${index}][d_name]">
                                                        </td>
                                                        <td class="text-end">${typeof item.ted_percentage === "number" ? '0' : item.ted_percentage}
                                                            <input type="hidden" value="${typeof item.ted_percentage === "number" ? '0' : item.ted_percentage}" name="disc_summary[${index}][d_perc]">
                                                            <input type="hidden" value="${item.ted_percentage}" name="disc_summary[${index}][hidden_d_perc]">
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

                    if (finalExpenses.length) {
                        let rows = '';
                        finalExpenses.forEach(function(item, index) {
                            index = index + 1;
                            rows += `<tr class="display_summary_exp_row">
                                                        <td>${index}</td>
                                                        <td>${item.ted_name}
                                                            <input type="hidden" value="${item.ted_id}" name="exp_summary[${index}][ted_e_id]">
                                                            <input type="hidden" value="" name="exp_summary[${index}][e_id]">
                                                            <input type="hidden" value="${item.ted_name}" name="exp_summary[${index}][e_name]">
                                                        </td>
                                                        <td class="text-end">${typeof item.ted_percentage === "number" ? '0' : item.ted_percentage}
                                                            <input type="hidden" value="${typeof item.ted_percentage === "number" ? '0' : item.ted_percentage}" name="exp_summary[${index}][e_perc]">
                                                            <input type="hidden" value="${item.ted_percentage}" name="exp_summary[${index}][hidden_e_perc]">
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

                    // General details
                    if (mrnHeader) {
                        $("[name='supplier_invoice_no']").val(mrnHeader.supplier_invoice_no);
                        $("[name='supplier_invoice_date']").val(mrnHeader.supplier_invoice_date);
                        $("[name='eway_bill_no']").val(mrnHeader.eway_bill_no);
                        $("[name='transporter_name']").val(mrnHeader.transporter_name);
                        $("[name='vehicle_no']").val(mrnHeader.vehicle_no);
                    } else {
                        $("[name='supplier_invoice_no'], [name='supplier_invoice_date'], [name='eway_bill_no'], [name='transporter_name'], [name='vehicle_no']")
                            .val('');
                    }

                    $("#reference_type_input").val(modelType);

                    setTimeout(() => {
                        if (idsLength > 1) {
                            $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
                                if (tableRowCount > 0) {
                                    currentIndex = tableRowCount + 1;
                                }
                                let currentIndex = index + 1;
                                setAttributesUIHelper(currentIndex, "#itemTable");
                            });
                        }
                        currentIndex = tableRowCount + 1;
                        setAttributesUIHelper(currentIndex, "#itemTable");
                    }, 500);
                });
            // .catch(() => {
            //     Swal.fire({
            //         title: 'Error!',
            //         text: 'An unexpected error occurred while processing Purchase Return.',
            //         icon: 'error'
            //     });
            // });
        }

        // Handle Process Error
        function handleProcessError(message = 'Invalid data') {
            // console.log('message', message);
            $(".editAddressBtn").removeClass('d-none');
            $("#vendor_name").val('').prop('readonly', false);
            $("#vendor_id, #vendor_code, #hidden_state_id, #hidden_country_id").val('');
            $("select[name='book_id'], select[name='return_type'], select[name='header_store_id'], select[name='sub_store_id']")
                .prop('readonly', false);
            $("select[name='currency_id'], select[name='payment_term_id']").prop('readonly', false).html(
                '<option value="">Select</option>');
            $(".shipping_detail, .billing_detail").text('-');
            $("#reference_from").removeClass('d-none');
            $('.asn_process').prop('disabled', false);
            $("#reference_type_input").val('');
            Swal.fire({
                title: 'Error!',
                text: message,
                icon: 'error'
            });
        }
    </script>
@endsection
