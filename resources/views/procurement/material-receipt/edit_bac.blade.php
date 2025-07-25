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
                                        {{$servicesBooks['services'][0]->name ?? "Material Receipt"}}
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
                                @if(!intval(request('amendment') ?? 0) && $mrn->document_status != \App\Helpers\ConstantHelper::DRAFT && $mrn->document_status != \App\Helpers\ConstantHelper::SUBMITTED && $mrn->document_status != \App\Helpers\ConstantHelper::PARTIALLY_APPROVED)
                                    <a href="{{ route('material-receipt.generate-pdf', $mrn->id) }}" target="_blank" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer">
                                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                            <rect x="6" y="14" width="12" height="8"></rect>
                                        </svg>
                                        Print
                                    </a>
                                    <a href="{{ route('material-receipt.print-labels', $mrn->id) }}" target="_blank" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer">
                                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                            <rect x="6" y="14" width="12" height="8"></rect>
                                        </svg>
                                        Print Labels
                                    </a>
                                @endif
                                @if($buttons['draft'])
                                    <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" name="action" value="draft">
                                        <i data-feather='save'></i> Save as Draft
                                    </button>
                                @endif
                                @if($buttons['submit'])
                                    <button type="submit" class="btn btn-primary btn-sm submit-button" name="action" value="submitted">
                                        <i data-feather="check-circle"></i> Submit
                                    </button>
                                @endif
                                @if($buttons['approve'])
                                    <button type="button" class="btn btn-primary btn-sm" id="approved-button" name="action" value="approved"><i data-feather="check-circle"></i> Approve</button>
                                    <button type="button" id="reject-button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg> Reject</button>
                                @endif
                                @if(($mrn->is_inspection_completion === 1) && $buttons['post'])
                                    <button id="postButton" onclick="onPostVoucherOpen();" type="button" class="btn btn-warning btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-check-circle"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg> Post</button>
                                @endif
                                @if($buttons['voucher'])
                                    <button type="button" onclick="onPostVoucherOpen('posted');" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-file-text"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg> Voucher</button>
                                @endif
                                @if($buttons['amend'] && intval(request('amendment') ?? 0))
                                    <button type="button" class="btn btn-primary btn-sm" id="amendmentBtn"><i data-feather="check-circle"></i> Submit</button>
                                @else
                                    @if($buttons['amend'])
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#amendmentconfirm" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i> Amendment</button>
                                    @endif
                                @endif
                                @if($buttons['revoke'])
                                    <button id = "revokeButton" type="button" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='rotate-ccw'></i> Revoke</button>
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
                                                @if (
                                                        ($mrn->document_status == 'draft' || $mrn->document_status == 'rejected') &&
                                                        !is_null($mrn->reference_type) && !empty($mrn->reference_type)
                                                    )
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Reference From <span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5 action-button">
                                                            @if ($mrn->reference_type == 'po')
                                                                <button type="button"
                                                                    class="btn btn-outline-primary btn-sm mb-0 poSelect">
                                                                    <i data-feather="plus-square"></i>
                                                                    Outstanding PO
                                                                </button>
                                                            @elseif($mrn->reference_type == 'jo')
                                                                <button type="button"
                                                                    class="btn btn-outline-primary btn-sm mb-0 joSelect">
                                                                    <i data-feather="plus-square"></i>
                                                                    Outstanding JO
                                                                </button>
                                                            @elseif($mrn->reference_type == 'so')
                                                                <button type="button"
                                                                    class="btn btn-outline-primary btn-sm mb-0 soSelect">
                                                                    <i data-feather="plus-square"></i>
                                                                    Outstanding SO
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endif

                                                @if (!is_null($mrn->reference_type) && !empty($mrn->reference_type))
                                                    <div class="row align-items-center mb-1" id="referenceNoDiv">
                                                        <div class="col-md-3">
                                                        </div>
                                                        <div class="col-md-5">
                                                            <input type="hidden" name="reference_type"
                                                                class="form-control reference_type" id="reference_type_input"
                                                                value="{{ $mrn->reference_type }}" readonly>
                                                        </div>
                                                    </div>
                                                @endif
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
                                                <div class="col-md-6 text-sm-end">
                                                    <button type="button" id="importItem" class="btn btn-sm btn-outline-primary importItem" onclick="openImportItemModal('edit', {{$mrn->id}})">
                                                        <i data-feather="upload"></i> Import Item
                                                    </button>
                                                    <a href="javascript:;" id="deleteBtn" class="btn btn-sm btn-outline-danger me-50">
                                                        <i data-feather="x-circle"></i> Delete
                                                    </a>
                                                    <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary addNewItemBtn">
                                                        <i data-feather="plus"></i> Add New Item
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="table-responsive pomrnheadtffotsticky">
                                                    <table id="itemTable" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"
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
                                                                <th class="text-end">PO Qty</th>
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
@endsection
@section('scripts')
    <script type="text/javascript" src="{{asset('assets/js/modules/common-attr-ui.js')}}"></script>
    <script type="text/javascript">
        var actionUrlTax = '{{route("material-receipt.tax.calculation")}}';
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script type="text/javascript" src="{{asset('assets/js/modules/common-datatable.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/modules/mrn.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/modules/import-item.js')}}"></script>
    <script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
    <script>
        selectedCostCenterId = @json($mrn->cost_center_id);
        let currentProcessType = @json($mrn->reference_type);
        var qtyChangeUrl = '{{ route("material-receipt.get.validate-quantity") }}';


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

        @if($buttons['amend'] && intval(request('amendment') ?? 0))

        @else
            @if(($mrn->document_status != 'draft') && ($mrn->document_status != 'rejected'))
                $(':input').prop('readonly', true);
                $('textarea[name="amend_remark"], input[type="file"][name="amend_attachment[]"]').prop('readonly', false).prop('disabled', false);
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
                $(document).on('show.bs.modal', function (e) {
                    if(e.target.id != 'approveModal') {
                        if(e.target.id != 'shortCloseModal') {
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
                    let termOption = `<option value="${data.data.paymentTerm.id}">${data.data.paymentTerm.name}</option>`;
                    $('[name="currency_id"]').empty().append(curOption);
                    $('[name="payment_term_id"]').empty().append(termOption);
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
                    closestTr.find("td[id*='itemAttribute_']").html(defautAttrBtn);
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
                remark: getVal("[name*='[remark]']"),
                uom_id: getVal("[name*='[uom_id]']"),
                qty: getVal("[name*='[accepted_qty]']"),
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
            setTableCalculation(true);
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
            let store_id = Number($("#itemTable #row_" + rowCount).find("[name*='[store_id]']").val());
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
                                        <input type="hidden" value="${store_id}" name="components[${rowCount}][erp_store][1][erp_store_id]" data-id="1"/>
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
                loadStoreDropdowns(store_id, rowCount);
                // $('[name="components[1][erp_store][1][erp_store_id]"').trigger('change');
            } else {
                if($("#itemTable #row_"+rowCount).find("[name*=store_qty]").length) {
                    $(".display_delivery_row").remove(); // Remove all rows if present
                } else {
                    // Remove all rows except the first one, and reset the quantity
                    $('.display_delivery_row').not(':first').remove();
                    $(".display_delivery_row").find("[name*=store_qty]").val('');
                }
                let rackVal = '';
                let shelfVal = '';
                let binVal = '';
                // Iterate over each store_qty field to build dynamic rows
                $("#itemTable #row_" + rowCount).find("[name*=store_qty]").each(function(index, item) {
                    let storeVal = $(item).closest('td').find(`[name="components[${rowCount}][erp_store][${index+1}][erp_store_id]"]`).val();
                    let rackVal = $(item).closest('td').find(`[name="components[${rowCount}][erp_store][${index+1}][erp_rack_id]"]`).val();
                    let shelfVal = $(item).closest('td').find(`[name="components[${rowCount}][erp_store][${index+1}][erp_shelf_id]"]`).val();
                    let binVal = $(item).closest('td').find(`[name="components[${rowCount}][erp_store][${index+1}][erp_bin_id]"]`).val();
                    let storeQty = $(item).closest('td').find(`[name="components[${rowCount}][erp_store][${index+1}][store_qty]"]`).val();
                    // Trigger the change event after setting values to ensure racks, shelves, etc. are updated
                    // $(`#erp_store_id_${index+1}`).val(storeVal).trigger('change');
                    // $(`#erp_rack_id_${index+1}`).val(rackVal);
                    // $(`#erp_shelf_id_${index+1}`).val(shelfVal);
                    // $(`#erp_bin_id_${index+1}`).val(binVal);
                    $(`#erp_rack_id_${index+1}`).val(rackVal);
                    $(`#erp_shelf_id_${index+1}`).val(shelfVal);
                    $(`#erp_bin_id_${index+1}`).val(binVal);

                    // Generate HTML for the new row with dynamic data
                    rowHtml += `<tr class="display_delivery_row">
                                    <td>${index + 1}</td>
                                    <td>
                                        <input type="hidden" name="row_count" value="${rowCount}" id="row_count">
                                        <input type="hidden" value="${store_id}" name="components[${rowCount}][erp_store][${index+1}][erp_store_id]" data-id="${index+1}"/>
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
                    count = index + 1;
                    loadStoreDropdowns(store_id, count, rackVal, shelfVal, binVal);
                    // $(`#erp_store_id_${index+1}`).trigger('change');
                });
            }
            $("#deliveryScheduleTable").find('#deliveryFooter #total').attr('qty',qty);
            $("#deliveryScheduleModal").modal('show');
            totalScheduleQty();
        });

        function loadStoreDropdowns(store_id, rowCount, rackVal, shelfVal, binVal) {
            if (store_id) {
                let erp_rack_id = rackVal || $(`#erp_rack_id_${rowCount}`)
            .val();
                let erp_shelf_id = shelfVal || $(`#erp_shelf_id_${rowCount}`)
            .val();
                let erp_bin_id = binVal || $(`#erp_bin_id_${rowCount}`)
            .val();

                var data = {
                    store_code_id: store_id
                };

                $.ajax({
                    type: 'POST',
                    data: data,
                    url: '/material-receipts/get-store-racks',
                    success: function(data) {
                        $('#erp_rack_id_' + rowCount).empty();
                        $.each(data.storeRacks, function(key, value) {
                            $('#erp_rack_id_' + rowCount).append('<option value="' + key + '">' +
                                value + '</option>');
                            if (erp_rack_id && key == erp_rack_id) {
                                $(`#erp_rack_id_${rowCount}`).val(
                                erp_rack_id); // Set the selected rack value
                            } else {
                                erp_rack_id = key;
                            }
                        });

                        // Empty and populate the bins dropdown
                        $('#erp_bin_id_' + rowCount).empty();
                        $.each(data.storeBins, function(key, value) {
                            // Append bin options and maintain the selected value if it matches the provided or default value
                            $('#erp_bin_id_' + rowCount).append('<option value="' + key + '">' + value +
                                '</option>');
                            if (erp_bin_id && key == erp_bin_id) {
                                $(`#erp_bin_id_${rowCount}`).val(
                                erp_bin_id); // Set the selected bin value
                            }
                        });

                        // If a rack is selected, load shelves for the selected rack
                        if (erp_rack_id) {
                            loadShelvesForRack(rowCount, erp_rack_id, rackVal, shelfVal, binVal);
                        }
                    }
                });
            }
        }

        function loadShelvesForRack(rowKey, rack_code_id, rackVal, shelfVal, binVal) {
            let erp_shelf_id = shelfVal || $(`#erp_shelf_id_${rowKey}`).val(); // Use shelfVal if provided, else fallback to form value
            let erp_bin_id = binVal || $(`#erp_bin_id_${rowKey}`).val(); // Maintain the bin value as well

            var data = {
                rack_code_id: rack_code_id
            };

            $.ajax({
                type: 'POST',
                data: data,
                url: '/material-receipts/get-rack-shelfs',
                success: function(data) {
                    // Clear the shelf dropdown and populate it with new options
                    $('#erp_shelf_id_' + rowKey).empty();
                    $.each(data.storeShelfs, function(key, value) {
                        $('#erp_shelf_id_' + rowKey).append('<option value="' + key + '">' + value +
                            '</option>');
                        if (erp_shelf_id && key == erp_shelf_id) {
                            $(`#erp_shelf_id_${rowKey}`).val(
                            erp_shelf_id); // Set the selected shelf value
                        }
                    });

                    // Trigger change event for shelf dropdown after population
                    $('#erp_shelf_id_' + rowKey).trigger('change');

                    // After shelves are loaded, set the selected bin value as well
                    if (erp_bin_id) {
                        $(`#erp_bin_id_${rowKey}`).val(erp_bin_id); // Set the selected bin value correctly
                    }
                }
            });
        }

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
            let store_id = Number($("#itemTable #row_" + rowCount).find("[name*='[store_id]']").val());
            let store_code = $("#itemTable #row_" + rowCount).find("[name*='[erp_store_code]']").val();
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
                // let store_id = Number($("#itemTable #row_" + rowCount).find("[name*='[store_id]']").val());
                let rowHtml = `<tr class="display_delivery_row">
                                    <td>${tblRowCount}</td>
                                    <td>
                                        <input type="hidden" value="${store_id}" name="components[${rowCount}][erp_store][${tblRowCount}][erp_store_id]" data-id="${tblRowCount}"/>
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
                loadStoreDropdowns(store_id, tblRowCount);
                // $('#erp_store_id_'+tblRowCount).trigger('change');
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
            let qty = 0.00;
            $("#deliveryScheduleTable [name*='[store_qty]']").each(function(index, item) {
                qty = qty + Number($(item).val());
            });
            let itemQty = Number($('#deliveryScheduleModal #deliveryFooter #total').attr('qty'));
            if (qty < itemQty) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Store quantity can not be less than accepted quantity.',
                    icon: 'error',
                });
                return false;
            }
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
            let id = e.target.getAttribute('data-index');
            // let id = $(`.display_discount_row`).find(`.deleteItemDeliveryRow`).getAttribute('data-index');

            let dataRowId = Number(e.target.getAttribute('data-row-count'));
            if($(e.target).closest('tbody').find('.display_delivery_row').length ==1) {
                Swal.fire({
                    title: 'Error!',
                    text: 'You cannot first row.',
                    icon: 'error',
                });
                return false;
            }
            $(e.target).closest('tr').remove();
            let ids = JSON.parse(localStorage.getItem('deletedItemLocationIds')) || [];

            if (!ids.includes(id)) {
                ids.push(id);
            }
            localStorage.setItem('deletedItemLocationIds', JSON.stringify(ids));
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

        function openPurchaseRequest() {
            initializeAutocompleteQt("vendor_code_input_qt", "vendor_id_qt_val", "vendor_list", "vendor_code",
                "company_name");
            initializeAutocompleteQt("book_code_input_qt", "book_id_qt_val", "book_po", "book_code", "");
            initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "po_document_qt", "document_number", "");
            initializeAutocompleteQt("item_name_input_qt", "item_id_qt_val", "goods_item_list", "item_code", "item_name");
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
            let selectedPoIds = [];
            let selectedJoIds = [];
            let selectedSoIds = [];

            @if ($mrn->reference_type == 'po')
                selectedPoIds = @json(is_array($mrn->purchase_order_id) ? $mrn->purchase_order_id : [$mrn->purchase_order_id]);
            @elseif ($mrn->reference_type == 'jo')
                selectedJoIds = @json(is_array($mrn->job_order_id) ? $mrn->job_order_id : [$mrn->job_order_id]);
            @elseif ($mrn->reference_type == 'so')
                selectedSoIds = @json(is_array($mrn->sale_order_id) ? $mrn->sale_order_id : [$mrn->sale_order_id]);
            @endif

            localStorage.setItem('selectedPoIds', selectedPoIds);
            localStorage.setItem('selectedJoIds', selectedJoIds);
            localStorage.setItem('selectedSoIds', selectedSoIds);
        };


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
                item_id = $("#item_id_qt_val").val() || '',
                vendor_id = $("#vendor_id_qt_val").val(),
                store_id = $("#store_id").val() || '',
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
                item_id = $("#jo_item_id_qt_val").val() || '',
                vendor_id = $("#jo_vendor_id_qt_val").val(),
                store_id = $("#jo_store_id").val() || '',
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
                item_id = $("#so_item_id_qt_val").val() || '',
                vendor_id = $("#so_vendor_id_qt_val").val(),
                store_id = $("#so_store_id").val() || '',
                so_id = $("#so_so_qt_val").val() || '',
                item_search = $("#so_item_name_search").length ? $("#so_item_name_search").val() : '';
                selected_po_ids = encodeURIComponent(selectedSoIds)
            }
            return {
                    document_date: document_date,
                    header_book_id: header_book_id,
                    series_id: series_id,
                    document_number: document_number,
                    item_id: item_id,
                    vendor_id: vendor_id,
                    store_id: store_id,
                    so_id: so_id,
                    item_search: item_search,
                    selected_po_ids: selected_po_ids
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

        function getSelectedPoIDS() {
            let ids = [];
            let referenceNos = [];
            $('.po_item_checkbox:checked').each(function() {
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

        $(document).on('click', '.poProcess', (e) => {
            let result = getSelectedPoIDS();
            let ids = result.ids;
            let idsLength = ids.length;
            let referenceNo = result.referenceNos[0];
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

            $("[name='po_item_ids']").val(ids);
            let moduleTypes = getSelectedPoTypes();
            $(".joSelect").hide();
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
            moduleTypes = JSON.stringify(moduleTypes);
            ids = JSON.stringify(ids);
            let type = 'po'; // Dynamically fetch the `type` from the current route
            let actionUrl = '{{ route("material-receipt.process.po-item") }}'
            + '?ids=' + encodeURIComponent(ids)
            + '&type=' + type
            + '&moduleTypes=' + moduleTypes
            + '&tableRowCount=' + tableRowCount
            + '&currency_id=' + encodeURIComponent(currencyId)
            + '&d_date=' + encodeURIComponent(transactionDate)
            // + '&groupItems=' + encodeURIComponent(groupItems);
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        let poOrder = data?.data?.purchaseOrder;
                        vendorOnChange(data?.data?.vendor?.id, 'po', poOrder.id);
                        let result = getSelectedPoIDS();
                        let newIds = result.ids;
                        let existingIds = localStorage.getItem('selectedPoIds');
                        if (existingIds) {
                            existingIds = JSON.parse(existingIds);
                            const mergedIds = Array.from(new Set([...existingIds, ...newIds]));
                            localStorage.setItem('selectedPoIds', JSON.stringify(mergedIds));
                        } else {
                            localStorage.setItem('selectedPoIds', JSON.stringify(newIds));
                        }

                        let existingIdsUpdate = JSON.parse(localStorage.getItem('selectedPoIds'));
                        $("[name='po_item_ids']").val(existingIdsUpdate.join(','));

                        let vendor = data?.data?.vendor || '';
                        let finalDiscounts = data?.data?.finalDiscounts;
                        let finalExpenses = data?.data?.finalExpenses;

                        if ($("#itemTable .mrntableselectexcel").find("tr[id*='row_']").length) {
                            $("#itemTable .mrntableselectexcel tr[id*='row_']:last").after(data.data
                                .pos);
                        } else {
                            $("#itemTable .mrntableselectexcel").empty().append(data.data.pos);
                        }
                        initializeAutocomplete2(".comp_item_code");
                        $("#poModal").modal('hide');
                        $("select[name='currency_id']").prop('disabled', true);
                        $("select[name='payment_term_id']").prop('disabled', true);
                        $("#vendor_name").prop('readonly', true);
                        $(".editAddressBtn").addClass('d-none');
                        $("#vendor_name").prop('readonly', true);
                        if(poOrder.type == 'supplier-invoice'){
                            $("[name='supplier_invoice_no']").val(poOrder.document_number);
                            $("[name='supplier_invoice_date']").val(poOrder.document_date);
                        } else{
                            $("[name='supplier_invoice_no']").val();
                            $("[name='supplier_invoice_date']").val();
                        }
                        let locationId = $("[name='header_store_id']").val();
                        // getLocation(locationId);

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
                            setTableCalculation(true);
                            if(idsLength > 1)
                            {
                                $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
                                    if(tableRowCount>0)
                                    {
                                        currentIndex = tableRowCount + 1;
                                    }
                                    let currentIndex = index + 1;
                                    setAttributesUIHelper(currentIndex,"#itemTable");
                                });
                            }
                            currentIndex = tableRowCount + 1;
                            setAttributesUIHelper(currentIndex,"#itemTable");
                        }, 500);
                    }
                    if (data.status == 422) {
                        $(".editAddressBtn").removeClass('d-none');
                        $("#vendor_name").val('').prop('readonly', false);
                        $("#vendor_id").val('');
                        $("#vendor_code").val('');
                        $("#hidden_state_id").val('');
                        $("#hidden_country_id").val('');
                        $("select[name='currency_id']").empty().append(
                            '<option value="">Select</option>').prop('readonly', false);
                        $("select[name='payment_term_id']").empty().append(
                            '<option value="">Select</option>').prop('readonly', false);
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
            initializeAutocompleteJoQt("jo_vendor_code_input_qt", "jo_vendor_id_qt_val", "jo_vendor_list", "jo_vendor_code", "company_name");
            initializeAutocompleteJoQt("jo_document_no_input_qt", "jo_document_id_qt_val", "jo_document_qt", "jo_document_number", "");
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
                            resjonse($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: `${item[labelKey1]} ${labelKey2 ? (item[labelKey2] ? '(' + item[labelKey2] + ')' : '') : ''}`,
                                    code: item[labelKey1] || '',
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

        function getJoDynamicParams() {
            selectedJoIds = localStorage.getItem('selectedJoIds') ?? '[]';
            selectedJoIds = JSON.parse(selectedJoIds);
            selectedJoIds = encodeURIComponent(JSON.stringify(selectedJoIds));

            return {
                document_date: $("[name='document_date']").val() || '',
                header_book_id: $("#jo_book_id").val() || '',
                series_id: $("#jo_book_id_qt_val").val() || '',
                document_number: $("#jo_document_id_qt_val").val() || '',
                item_id: $("#jo_item_id_qt_val").val() || '',
                vendor_id: $("#jo_vendor_id_qt_val").val(),
                store_id: $("#jo_store_id").val() || '',
                so_id: $("#jo_so_qt_val").val() || '',
                item_search: $("#jo_item_name_search").val(),
                selected_jo_ids: encodeURIComponent(selectedJoIds)
            };
        }

        function getJobOrders()
        {
            const ajaxUrl = '{{ route("material-receipt.get.jo", ["type" => "edit"]) }}';
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
            ];
            initializeDataTableCustom('#joModal .jo-order-detail',
                ajaxUrl,
                columns
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
            let referenceNos = [];
            $('.jo_item_checkbox:checked').each(function() {
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

        $(document).on('click', '.joProcess', (e) => {
            let result = getSelectedJoIDS();
            let ids = result.ids;
            let idsLength = ids.length;
            let referenceNo = result.referenceNos[0];
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
                                $input.closest('tr').find('[name*="[accepted_qty]"]').val('')
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
            ids = JSON.stringify(ids);
            moduleTypes = JSON.stringify(moduleTypes);
            let type = 'jo'; // Dynamically fetch the `type` from the current route
            let actionUrl = '{{ route("material-receipt.process.jo-item") }}'
            + '?ids=' + encodeURIComponent(ids)
            + '&type=' + type
            + '&moduleTypes=' + moduleTypes
            + '&tableRowCount=' + tableRowCount
            + '&currency_id=' + encodeURIComponent(currencyId)
            + '&d_date=' + encodeURIComponent(transactionDate)
            // + '&groupItems=' + encodeURIComponent(groupItems);

            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        let joOrder = data?.data?.jobOrder;
                        vendorOnChange(data?.data?.vendor?.id, 'jo', joOrder.id);
                        let result = getSelectedJoIDS();
                        let newIds = result.ids;
                        let existingIds = localStorage.getItem('selectedJoIds');
                        if (existingIds) {
                            existingIds = JSON.parse(existingIds);
                            const mergedIds = Array.from(new Set([...existingIds, ...newIds]));
                            localStorage.setItem('selectedJoIds', JSON.stringify(mergedIds));
                        } else {
                            localStorage.setItem('selectedJoIds', JSON.stringify(newIds));
                        }

                        let existingIdsUpdate = JSON.parse(localStorage.getItem('selectedJoIds'));
                        $("[name='po_item_ids']").val(existingIdsUpdate.join(','));

                        let vendor = data?.data?.vendor || '';
                        let finalDiscounts = data?.data?.finalDiscounts;
                        let finalExpenses = data?.data?.finalExpenses;

                        if ($("#itemTable .mrntableselectexcel").find("tr[id*='row_']").length) {
                            $("#itemTable .mrntableselectexcel tr[id*='row_']:last").after(data.data
                                .pos);
                        } else {
                            $("#itemTable .mrntableselectexcel").empty().append(data.data.pos);
                        }
                        initializeAutocomplete2(".comp_item_code");
                        $("#joModal").modal('hide');
                        $("select[name='currency_id']").prop('disabled', true);
                        $("select[name='payment_term_id']").prop('disabled', true);
                        $("#vendor_name").prop('readonly', true);
                        $(".editAddressBtn").addClass('d-none');
                        $("#vendor_name").prop('readonly', true);
                        if(joOrder.type == 'supplier-invoice'){
                            $("[name='supplier_invoice_no']").val(joOrder.document_number);
                            $("[name='supplier_invoice_date']").val(joOrder.document_date);
                        } else{
                            $("[name='supplier_invoice_no']").val();
                            $("[name='supplier_invoice_date']").val();
                        }
                        let locationId = $("[name='header_store_id']").val();
                        // getLocation(locationId);

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

                            $("#summaryDiscountTable tbody").find('.display_summary_discount_row')
                                .remove();
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
                            setTableCalculation(true);
                            if(idsLength > 1)
                            {
                                $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
                                    if(tableRowCount>0)
                                    {
                                        currentIndex = tableRowCount + 1;
                                    }
                                    let currentIndex = index + 1;
                                    setAttributesUIHelper(currentIndex,"#itemTable");
                                });
                            }
                            currentIndex = tableRowCount + 1;
                            setAttributesUIHelper(currentIndex,"#itemTable");
                        }, 500);
                    }
                    if (data.status == 422) {
                        $(".editAddressBtn").removeClass('d-none');
                        $("#vendor_name").val('').prop('readonly', false);
                        $("#vendor_id").val('');
                        $("#vendor_code").val('');
                        $("#hidden_state_id").val('');
                        $("#hidden_country_id").val('');
                        $("select[name='currency_id']").empty().append(
                            '<option value="">Select</option>').prop('readonly', false);
                        $("select[name='payment_term_id']").empty().append(
                            '<option value="">Select</option>').prop('readonly', false);
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
            const ajaxUrl = '{{ route("material-receipt.get.so", ["type" => "edit"]) }}';
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
            $(".poSelect").hide();
            $(".joSelect").hide();
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
                            existingIds = Array.isArray(existingIds) ? existingIds : [existingIds];
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
                            setTableCalculation(true);
                            if(idsLength > 1)
                            {
                                $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
                                    if(tableRowCount>0)
                                    {
                                        currentIndex = tableRowCount + 1;
                                    }
                                    let currentIndex = index + 1;
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
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type:type,
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
                    $("#" + nameSelector).val(itemName);
                    $("#" + percentageVal).val(itemPercentage).trigger('keyup');
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#" + idSelector).val("");
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
    </script>
@endsection
