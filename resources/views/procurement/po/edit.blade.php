@extends('layouts.app')
@section('styles')
    <style>
        td .form-select {
            width: 100% !important;
            min-width: 100% !important;
            box-sizing: border-box;
        }
    </style>
@endsection
@section('content')
    <form id="poEditForm" data-module="po" class="ajax-input-form" action="{{ route('po.update', ['type' => request()->route('type'), 'id' => $po->id]) }}" method="POST" data-redirect="/{{ request()->route('type') }}" enctype="multipart/form-data">
        @csrf
        @php
            $pi_item_ids = $po->pi_item_mappings()->pluck('pi_item_id')->implode(',');
        @endphp
        <input type="hidden" name="tax_required" id="tax_required" value="">
        <input type="hidden" name="pi_item_ids" id="pi_item_ids" value="{{ $pi_item_ids }}">
        <input type="hidden" name="short_close_ids" id="short_close_ids">
        <input type="hidden" name="po_type" id="po_type" value="{{ $po->po_type }}">
        <div class="app-content content ">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        @include('layouts.partials.breadcrumb-add-edit', [
                            'title' => $title,
                            'menu' => $menu,
                            'menu_url' => $menu_url,
                            'sub_menu' => $sub_menu,
                        ])
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <input type="hidden" name="document_status" value="{{ $po->document_status }}" id="document_status">
                                <button type="button" onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i>
                                    Back</button>
                                @if ($buttons['draft'])
                                    <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" name="action" value="draft"><i data-feather='save'></i> Save as Draft</button>
                                @endif
                                @if (
                                    !intval(request('amendment') ?? 0) &&
                                        // $po->document_status != \App\Helpers\ConstantHelper::DRAFT &&
                                        // $po->document_status != \App\Helpers\ConstantHelper::SUBMITTED &&
                                        $po->document_status != \App\Helpers\ConstantHelper::PARTIALLY_APPROVED)
                                    <a href="{{ url(request()->route('type')) }}/{{ $po->id }}/pdf" target="_blank" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer">
                                            <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2">
                                            </path>
                                            <rect x="6" y="14" width="12" height="8"></rect>
                                        </svg> Print
                                    </a>
                                    <button type = "button" onclick = "sendMailTo();" class="btn btn-primary btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><i data-feather="mail"></i> E-Mail</button>
                                @endif
                                @if ($buttons['submit'])
                                    <button type="submit" class="btn btn-primary btn-sm submit-button" name="action" value="submitted"><i data-feather="check-circle"></i> Submit</button>
                                @endif
                                @if ($buttons['approve'])
                                    <button type="button" id="reject-button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="15" y1="9" x2="9" y2="15"></line>
                                            <line x1="9" y1="9" x2="15" y2="15"></line>
                                        </svg> Reject</button>
                                    <button type="button" class="btn btn-primary btn-sm" id="approved-button" name="action" value="approved"><i data-feather="check-circle"></i> Approve</button>
                                @endif

                                @if ($buttons['amend'] && intval(request('amendment') ?? 0))
                                    <button type="button" class="btn btn-primary btn-sm" id="amendmentBtn"><i data-feather="check-circle"></i> Submit</button>
                                @else
                                    @if ($buttons['amend'])
                                        <button type="button" data-bs-toggle="modal" data-bs-target="#amendmentconfirm" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i>
                                            Amendment</button>
                                    @endif
                                @endif

                                @if ($buttons['revoke'])
                                    <button id = "revokeButton" type="button" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='rotate-ccw'></i>
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
                                <div class="card" id="basic_section">
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
                                                    Status : <span class="{{ $docStatusClass }}">{{ $po->display_status }}</span>
                                                </span>
                                            </div>
                                            <div class="col-md-8 basic-information">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Series <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="hidden" name="book_id" value="{{ $po->book_id }}">
                                                        <select class="form-select" disabled id="book_id" name="book_id" readonly>
                                                            @foreach ($books as $book)
                                                                <option value="{{ $book->id }}" {{ $book->id == $po->book_id ? 'selected' : '' }}>
                                                                    {{ $book->book_code }}</option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" name="book_code" id="{{ $po->book->book_code }}" id="book_code">
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">{{ $short_title }} No <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" readonly name="document_number" id="document_number" value="{{ $po->document_number }}" class="form-control">
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">{{ $short_title }} Date <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" class="form-control" value="{{ $po->document_date }}" name="document_date">
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">{{ $short_title }} Procurement Type
                                                            <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" name="procurement_type" id="procurement_type">
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Location <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="store_id" name="store_id">
                                                            @foreach ($locations as $location)
                                                                <option value="{{ $location->id }}" {{ $po->store_id == $location->id ? 'selected' : '' }}>
                                                                    {{ $location?->store_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                @if ($saleOrders?->count())
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Sales Order</label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <input type="text" readonly class="form-control" value="{{ $saleOrders->map(fn($saleOrder) => strtoupper($saleOrder->book_code) . ' - ' . $saleOrder->document_number)->join(', ') }}">
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Reference from</label>
                                                    </div>
                                                    <div class="col-md-5 action-button">
                                                        <button type="button" @if (!$isEdit) disabled @endif class="btn btn-outline-primary btn-sm mb-0 prSelect"><i data-feather="plus-square"></i>
                                                            {{ $reference_from_title }}</button>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- Approval History Section --}}
                                            @include('partials.approval-history', [
                                                'document_status' => $po->document_status,
                                                'revision_number' => $revision_number,
                                            ])
                                        </div>
                                    </div>
                                </div>
                                <div class="row" id="vendor_section">
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
                                                            <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" id="vendor_name" name="vendor_name" readonly value="{{ $po->vendor->company_name }}" />
                                                            <input type="hidden" value="{{ $po->vendor_id }}" id="vendor_id" name="vendor_id" />
                                                            <input type="hidden" value="{{ $po->vendor_code }}" id="vendor_code" name="vendor_code" />

                                                            <input type="hidden" id="vendor_address_id" name="vendor_address_id" value="{{ $po->latestShippingAddress()?->id }}" />
                                                            <input type="hidden" id="billing_address_id" name="billing_address_id" value="{{ $po->latestBillingAddress()?->id }}" />
                                                            <input type="hidden" id="delivery_address_id" name="delivery_address_id" value="{{ $po->latestDeliveryAddress()?->id }}" />

                                                            <input type="hidden" value="{{ $fromCountry }}" id="country_id" name="country_id" />
                                                            <input type="hidden" value="{{ $fromState }}" id="state_id" name="state_id" />

                                                            <input type="hidden" value="" id="party_country_id" name="party_country_id" />
                                                            <input type="hidden" value="" id="party_state_id" name="party_state_id" />

                                                            <input type="hidden" value="{{ $po->latestShippingAddress()?->state?->id }}" id="hidden_state_id" name="hidden_state_id" />
                                                            <input type="hidden" value="{{ $po->latestShippingAddress()?->country?->id }}" id="hidden_country_id" name="hidden_country_id" />
                                                            <input type="hidden" id="delivery_country_id" name="delivery_country_id" />
                                                            <input type="hidden" id="delivery_state_id" name="delivery_state_id" />
                                                            <input type="hidden" id="delivery_city_id" name="delivery_city_id" />
                                                            <input type="hidden" id="delivery_pincode" name="delivery_pincode" />
                                                            <input type="hidden" id="delivery_address" name="delivery_address" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                                                            <select disabled class="form-select" name="currency_id">
                                                                <option value="{{ $po->currency_id }}">
                                                                    {{ $po->currency?->name }}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Payment Terms <span class="text-danger">*</span></label>
                                                            <select disabled class="form-select" name="payment_term_id">
                                                                <option value="{{ $po->payment_term_id }}">
                                                                    {{ $po->paymentTerm->name }}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Credit Days <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control mw-100" id="credit_days" name="credit_days" value="{{ $po->credit_days }}" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Exchange Rate <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control mw-100 {{ $isDifferentCurrency ? '' : 'disabled-input' }}" value="{{ $po->org_currency_exg_rate }}" id="exchange_rate" name="exchange_rate" />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3 gstin_no_div @if ($po?->vendor?->compliances?->gstin_no) d-block @else d-none @endif">
                                                        <div class="mb-1">
                                                            <label class="form-label">GSTIN No. </label>
                                                            <input type="text" class="form-control mw-100" id="gstin_no" value="{{ @$po?->vendor?->compliances->gstin_no }}" disabled />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Consignee Name </label>
                                                            {{-- <span class="text-danger">*</span> --}}
                                                            <input type="text" class="form-control mw-100 @if (!$buttons['submit'] || !$buttons['draft']) disabled-input @endif" id="consignee_name" name="consignee_name" value="{{ $po->consignee_name }}" />
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="customer-billing-section h-100">
                                                            <p>Vendor Address</p>
                                                            <div class="bilnbody">
                                                                <div class="genertedvariables genertedvariablesnone">
                                                                    <label class="form-label w-100">Vendor Address <span class="text-danger">*</span>
                                                                        <a href="javascript:;" class="float-end font-small-2 editAddressBtn d-none {{ $po->po_items->count() ? 'd-none' : '' }}" data-type="vendor_address"><i data-feather='edit-3'></i> Edit</a>
                                                                    </label>
                                                                    <div class="mrnaddedd-prim vendor_address">
                                                                        {{ $po->latestShippingAddress()->display_address }}
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
                                                                    </label>
                                                                    <div class="mrnaddedd-prim billing_address">
                                                                        {{ $po?->latestBillingAddress()?->display_address }}
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
                                                                    <label class="form-label w-100">Delivery Address <span class="text-danger">*</span>
                                                                        <a href="javascript:;" class="float-end font-small-2 editAddressBtn d-none" data-type="delivery_address"><i data-feather='edit-3'></i> Edit</a>
                                                                    </label>
                                                                    <div class="mrnaddedd-prim delivery_address">
                                                                        {{ $po?->latestDeliveryAddress()?->display_address }}
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
                                <div class="card" id="item_section">
                                    <div class="card-body customernewsection-form">
                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader ">
                                                        <h4 class="card-title text-theme">{{ $short_title }} Item Wise
                                                            Detail</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-sm-end">
                                                    @if ($shortClose && $buttons['amend'])
                                                        <a href="javascript:;" id="shortCloseBtn" class="btn btn-sm btn-outline-danger me-50">
                                                            <i data-feather="x-circle"></i> Short Close</a>
                                                    @else
                                                        <a href="javascript:;" id="deleteBtn" class="btn btn-sm btn-outline-danger me-50">
                                                            <i data-feather="x-circle"></i> Delete</a>
                                                    @endif
                                                    <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary d-none">
                                                        <i data-feather="plus"></i> Add Item</a>
                                                    @if ($buttons['submit'] || $buttons['draft'])
                                                        <a href="#" onclick = "copyItemRow();" id = "copy_item_section" style = "{{ isset($po->po_items) && count($po->po_items) ? '' : 'display:none;' }}" class="btn btn-sm btn-outline-primary">
                                                            <i data-feather="copy"></i> Copy Item</a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="table-responsive pomrnheadtffotsticky">
                                                    <table id="itemTable" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad" data-json-key="components_json" data-row-selector="tr[id^='row_']">
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
                                                                <th max-width="180px">Attributes</th>
                                                                <th>UOM</th>
                                                                <th>Qty</th>
                                                                <th>Rate</th>
                                                                <th>Value</th>
                                                                <th>Discount</th>
                                                                <th>Total</th>
                                                                <th>Delivery Date</th>
                                                                <th width="50px">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                            @include('procurement.po.partials.item-row-edit')
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="totalsubheadpodetail">
                                                                <td colspan="7"></td>
                                                                <td class="text-end" id="totalItemValue">0.00</td>
                                                                <td class="text-end" id="totalItemDiscount">0.00</td>
                                                                <td class="text-end" id="TotalEachRowAmount">0.00</td>
                                                                <td></td>
                                                            </tr>
                                                            <tr valign="top">
                                                                <td colspan="8" rowspan="10">
                                                                    <table class="table border">
                                                                        <tbody id="itemDetailDisplay">
                                                                            <tr>
                                                                                <td class="p-0">
                                                                                    <h6 class="text-dark mb-0 bg-light-primary py-1 px-50">
                                                                                        <strong>Item Details</strong>
                                                                                    </h6>
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
                                                                    </table>
                                                                </td>
                                                                <td colspan="4">
                                                                    <table class="table border mrnsummarynewsty">
                                                                        <tr>
                                                                            <td colspan="2" class="p-0">
                                                                                <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between">
                                                                                    <strong>{{ $short_title }}
                                                                                        Summary</strong>
                                                                                    <div class="addmendisexpbtn">
                                                                                        <button type="button" class="btn p-25 btn-sm btn-outline-secondary summaryTaxBtn">{{-- <i data-feather="plus"></i> --}}
                                                                                            Tax</button>
                                                                                        <button type="button" class="btn p-25 btn-sm btn-outline-secondary summaryDisBtn"><i data-feather="plus"></i>
                                                                                            Discount</button>
                                                                                        <button type="button" class="btn p-25 btn-sm btn-outline-secondary summaryExpBtn"><i data-feather="plus"></i>
                                                                                            Expenses</button>
                                                                                    </div>
                                                                                </h6>
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="totalsubheadpodetail">
                                                                            <td width="55%"><strong>Sub Total</strong>
                                                                            </td>
                                                                            <td class="text-end" id="f_sub_total">0.00
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Item Discount</strong></td>
                                                                            <td class="text-end" id="f_total_discount">
                                                                                0.00</td>
                                                                        </tr>
                                                                        @if ($po->headerDiscount())
                                                                            <tr id="f_header_discount_hidden">
                                                                                <td><strong>Header Discount</strong></td>
                                                                                <td class="text-end" id="f_header_discount">
                                                                                    {{ $po->headerDiscount()->sum('ted_amount') }}
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
                                                                            <td class="text-end" id="f_taxable_value" amount="">0.00</td>
                                                                        </tr>

                                                                        <tr>
                                                                            <td><strong>Tax</strong></td>
                                                                            <td class="text-end" id="f_tax">0.00</td>
                                                                        </tr>

                                                                        <tr class="totalsubheadpodetail">
                                                                            <td><strong>Total After Tax</strong></td>
                                                                            <td class="text-end" id="f_total_after_tax">
                                                                                0.00</td>
                                                                        </tr>

                                                                        <tr>
                                                                            <td><strong>Expense</strong></td>
                                                                            <td class="text-end" id="f_exp">0.00</td>
                                                                        </tr>
                                                                        <tr class="voucher-tab-foot">
                                                                            <td class="text-primary"><strong>Grand
                                                                                    Total</strong></td>
                                                                            <td>
                                                                                <div class="quottotal-bg justify-content-end">
                                                                                    <h5 id="f_total_after_exp">0.00</h5>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="voucher-tab-foot {{ $isDifferentCurrency ? '' : 'd-none' }}" id="exchangeDiv">
                                                                            <td class="text-primary"><strong>Grand Total
                                                                                    ({{ $currencyName }})</strong></td>
                                                                            <td>
                                                                                <div class="quottotal-bg justify-content-end">
                                                                                    <h5 id="f_total_after_exp_rate">0.00
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
                                                <div class="col-md-6 mt-2">
                                                    <div class="mb-1">
                                                        <label class="form-label">Terms & Conditions</label>
                                                        <select class="form-select select2" name="term_id[]" multiple>
                                                            @foreach ($termsAndConditions as $termsAndCondition)
                                                                <option value="{{ $termsAndCondition->id }}" {{ in_array($termsAndCondition->id, $po->terms->pluck('id')->toArray()) ? 'selected' : '' }} data-detail="{{ $termsAndCondition->term_detail }}">
                                                                    {{ $termsAndCondition->term_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <textarea name="terms_data" id="summernote" class="form-control " {{ $po->document_status != \App\Helpers\ConstantHelper::DRAFT ? 'disabled' : '' }} placeholder="Enter Terms" maxlength="250" oninput="if(this.value.length > 250) this.value = this.value.slice(0, 250);">{{ isset($po->tnc) ? $po->tnc : '' }}</textarea>
                                                    <small class="text-muted d-block text-end">
                                                        <span id="termsCharCount">0</span>/250 characters
                                                    </small>
                                                    <input type="hidden" name="tnc" id="tnc" value="{{ isset($po->tnc) ? $po->tnc : '' }}">
                                                    <input type="hidden" id="customer_terms_id" value="" name="terms_id" />
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="mb-1">
                                                                    <label class="form-label">Upload Document</label>
                                                                    <input type="file" name="attachment[]" class="form-control" onchange = "addFiles(this,'main_po_preview')" multiple>
                                                                    <span class = "text-primary small">{{ __('message.attachment_caption') }}</span>
                                                                </div>
                                                            </div>
                                                            @include('partials.document-preview', [
                                                                'documents' => $po->getDocuments(),
                                                                'document_status' => $po->document_status,
                                                                'elementKey' => 'main_po_preview',
                                                            ])
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Final Remarks</label>
                                                            <textarea maxlength="250" type="text" rows="4" name="remarks" class="form-control" placeholder="Enter Remarks here...">{!! $po->remarks !!}</textarea>

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
        @include('procurement.po.partials.summary-disc-modal')

        {{-- Add expenses modal --}}
        @include('procurement.po.partials.summary-exp-modal')

        {{-- Edit Address --}}
        <div class="modal fade" id="edit-address" tabindex="-1" aria-labelledby="one" aria-hidden="true">
            <div class="modal-dialog  modal-dialog-centered" style="max-width: 700px">

            </div>
        </div>

        @include('procurement.po.partials.amendment-modal', ['id' => $po->id])

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
                    <div class="text-end">
                    </div>
                    <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                        <thead>
                            <tr>
                                <td>#</td>
                                <td>
                                <td>
                                    <label class="form-label">Type<span class="text-danger">*</span></label>
                                    <input type="text" id="new_item_dis_name_select" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                    <input type = "hidden" id = "new_item_discount_id" />
                                    <input type = "hidden" id = "new_item_dis_name" />
                                </td>
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

    {{-- Delivery schedule --}}
    <div class="modal fade" id="deliveryScheduleModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Delivery Schedule</h1>
                    {{-- <p class="text-center">Enter the details below.</p> --}}

                    <div class="text-end"> <a href="javascript:;" class="text-primary add-contactpeontxt mt-50 addTaxItemRow"><i data-feather='plus'></i> Add
                            Schedule</a></div>

                    <div class="table-responsive-md customernewsection-form">
                        <table id="deliveryScheduleTable" class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                            <thead>
                                <tr>
                                    <th>S.No</th>
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
                    <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="button" class="btn btn-primary itemDeliveryScheduleSubmit">Submit</button>
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
                    <button type="button" id="deleteConfirm" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Short Close Confirm --}}
    {{-- <div class="modal fade text-start alertbackdropdisabled" id="shortCloseModal" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
    <div class="modal-dialog">
       <div class="modal-content">
          <div class="modal-header p-0 bg-transparent">
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body alertmsg text-center warning">
            <i data-feather='alert-circle'></i>
            <h2>Are you sure?</h2>
            <p>Are you sure you want to short close selected <strong>Components</strong>?</p>
            <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
            <button type="button" id="shortCloseConfirm" class="btn btn-primary" >Confirm</button>
          </div>
       </div>
    </div>
 </div> --}}

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
                    <button type="button" id="deleteItemDiscConfirm" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Item discount modal --}}
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
                    <button type="button" id="deleteHeaderDiscConfirm" class="btn btn-primary">Confirm</button>
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
                    <button type="button" id="deleteHeaderExpConfirm" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete item delivery modal --}}
    <div class="modal fade text-start alertbackdropdisabled" id="deleteDeliveryModal" tabindex="-1" aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
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
                    <button type="button" id="deleteDeliveryConfirm" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Approval Modal --}}
    @include('procurement.po.partials.approve-modal', ['id' => $po->id])

    {{-- Taxes --}}
    @include('procurement.po.partials.tax-detail-modal')

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
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>PO</strong>? After Amendment this
                        action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Send Mail Modal --}}
    <div class="modal fade" id="sendMail" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <input type="hidden" name="action_type" id="action_type">
                <input type="hidden" name="id" value="{{ isset($po) ? $po->id : '' }}">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="send_mail_heading_label">
                        </h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-2">
                    <div class="row mt-1">
                        {{-- <div class="col-md-12">
                    <div class="mb-1">
                        <label class="form-label">Email From</label>
                        <input type="text" id='cust_mail' name="email_from" class="form-control cannot_disable">
                    </div>
                </div> --}}
                        <div class="col-md-12">
                            <div class="mb-1">
                                <label class="form-label">Email To</label>
                                <input type="text" id='cust_mail' name="email_to" class="form-control mail_modal cannot_disable">
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-1">
                                <label class="form-label">CC To</label>
                                <select name="cc_to[]" class="select2 form-control mail_modal cannot_disable" multiple>
                                    @foreach ($users as $index => $user)
                                        <option value="{{ $user->email }}" {{ $user->id == $po->created_by ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="mb-1">
                                <label class="form-label">Remarks</label>
                                <textarea name="remarks" id="mail_remarks" class="form-control mail_modal cannot_disable"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="reset" class="btn btn-outline-secondary me-1" onclick = "closeModal('sendMail');">Cancel</button>
                        <button type="submit" id="mail_submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Purchase Model --}}
    @include('procurement.po.partials.pr-modal')
    @include('procurement.po.partials.short-close-modal')

@endsection
@section('scripts')
    <script type="text/javascript">
        let type = '{{ request()->route('type') }}';
        let taxCalUrl = '{{ route('tax.group.calculate') }}';
        let actionUrlTax = '{{ route('po.tax.calculation', ['type' => ':type']) }}'.replace(':type', type);
        var getLocationUrl = '{{ route('store.get') }}';
        var getAddressOnVendorChangeUrl = "{{ route('po.get.address', ['type' => ':type']) }}".replace(':type', type);
    </script>
    <script type="text/javascript" src="{{ asset('assets/js/modules/common-datatable.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/modules/common-attr-ui.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/modules/po.js') }}"></script>
    <script type="text/javascript" src="{{ asset('app-assets/js/file-uploader.js') }}"></script>
    <script>
        @if ($po->document_status != \App\Helpers\ConstantHelper::DRAFT)
            $('#summernote').summernote('disable');
            // Reflect selected option text from select2[name="term_id[]"] to #summernote1 textarea
            $(document).on('change', 'select[name="term_id[]"]', function() {
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
            $('#summernote').on('summernote.change', function(we, contents, $editable) {
                updateSummernoteData();
            });

            // Initialize Summernote (example)
            $('#summernote').summernote({
                height: 200
            });
        @endif
        /*Clear local storage*/
        @if ($pi_item_ids)
            let pi_item_ids = "{{ $pi_item_ids }}";
            pi_item_ids = pi_item_ids.split(',');
            localStorage.setItem('selectedPiIds', JSON.stringify(pi_item_ids));
        @else
            localStorage.removeItem('selectedPiIds');
        @endif
        setTimeout(() => {
            localStorage.removeItem('deletedItemDiscTedIds');
            localStorage.removeItem('deletedHeaderDiscTedIds');
            localStorage.removeItem('deletedHeaderExpTedIds');
            localStorage.removeItem('deletedPiItemIds');
            localStorage.removeItem('deletedDelivery');
        }, 0);

        @if ($buttons['amend'] && intval(request('amendment') ?? 0))
        @else
            @if ($po->document_status != 'draft' && $po->document_status != 'rejected')
                $(':input').prop('readonly', true);
                $('textarea[name="amend_remark"], input[type="file"][name="amend_attachment[]"]').prop('readonly', false)
                    .prop('disabled', false);
                $('select').not('.amendmentselect select').prop('disabled', true);
                $("#deleteBtn").remove();
                $("#addNewItemBtn").remove();
                $(".editAddressBtn").remove();
                $(document).on('show.bs.modal', '.modal:not(#sendMail)', function(e) {
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
        $("#mail_submit").on('click', function(e) {
            e.preventDefault();
            document.getElementById('erp-overlay-loader').style.display = "flex";
            $('#mail_submit').prop('disabled', true);
            let actionType = $("#action_type").val();
            let id = $("input[name='id']").val();
            let emailTo = $("#cust_mail").val();
            let ccTo = $('[name="cc_to[]"]').val();
            let remarks = $("#mail_remarks").val();
            if (!emailTo) {
                Swal.fire({
                    title: 'Error!',
                    text: "Please enter email to.",
                    icon: 'error',
                });
                document.getElementById('erp-overlay-loader').style.display = "none";
                $('#mail_submit').prop('disabled', false);
                return false;
            }
            if (!remarks) {
                Swal.fire({
                    title: 'Error!',
                    text: "Please enter remarks.",
                    icon: 'error',
                });
                document.getElementById('erp-overlay-loader').style.display = "none";
                $('#mail_submit').prop('disabled', false);
                return false;
            }
            $.ajax({
                url: '{{ route('po.poMail', ['type' => 'purchase-order']) }}',
                type: 'POST',
                data: {
                    action_type: actionType,
                    id: id,
                    email_to: emailTo,
                    cc_to: ccTo,
                    remarks: remarks,
                },
                success: function(response) {
                    console.log(response);
                    if (response.status == 'success') {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                        });
                        $('#sendMail').modal('hide');
                        setTimeout(() => {
                            history.go(-1);
                        }, 1000);
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: response.message,
                            icon: 'error',
                        });
                    }
                    document.getElementById('erp-overlay-loader').style.display = "none";
                    $('#mail_submit').prop('disabled', false);
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'Error!',
                        text: xhr.responseJSON.message || "Something went wrong!",
                        icon: 'error',
                    });
                    $('#mail_submit').prop('disabled', false);
                    document.getElementById('erp-overlay-loader').style.display = "none";
                }
            });
        });

        function getDocNumberByBookId(bookId) {
            let document_date = $("[name='document_date']").val();
            let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId + '&document_date=' +
                document_date;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    // console.log(data.data);
                    if (data.status == 200) {
                        $("#book_code").val(data.data.book_code);
                        if (!data.data.doc.document_number) {
                            $("#document_number").val('');
                        }
                        //  $("#document_number").val(data.data.doc.document_number);
                        if (data.data.doc.type == 'Manually') {
                            $("#document_number").attr('readonly', false);
                        } else {
                            $("#document_number").attr('readonly', true);
                        }
                        const parameters = data.data.parameters;
                        setServiceParameters(parameters);

                        if (parameters?.tax_required.some(val => val.toLowerCase() === 'yes')) {
                            $("#tax_required").val(parameters?.tax_required[0]);
                        } else {
                            $("#tax_required").val("");
                        }
                        let poType = parameters.goods_or_services || 'Goods';
                        $("#po_type").val(poType);
                        setTableCalculation();
                    }
                    if (data.status == 404) {
                        $("#book_code").val('');
                        $("#document_number").val('');
                        $("#tax_required").val("");
                        $("#po_type").val('Goods');
                        const docDateInput = $("[name='document_date']");
                        docDateInput.removeAttr('min');
                        docDateInput.removeAttr('max');
                        // docDateInput.val(new Date().toISOString().split('T')[0]);
                    }
                });
            });
        }

        /* Trigger Send Mail Modal With Data*/
        function sendMailTo() {
            $('.ajax-validation-error-span').remove();
            $('.reset_mail').removeClass('is-invalid');
            $('.mail_modal').prop('readonly', false);
            $('.mail_modal').prop('disabled', false);
            $('[name="cc_to[]"]').prop('disabled', false);
            $('[name="cc_to[]"]').prop('readonly', false);

            const vendorEmail = "{{ isset($po) ? $po->vendor->email : '' }}";
            const vendorName = "{{ isset($po) ? $po->vendor->company_name : '' }}";
            const emailInput = document.getElementById('cust_mail');
            const header = document.getElementById('send_mail_heading_label');
            if (emailInput) {
                emailInput.value = vendorEmail;
            }
            if (header) {
                header.innerHTML = "Send Mail";
            }
            $("#mail_remarks").val("Your Purchase Order has been successfully generated.");
            $('#sendMail').modal('show');
        }

        /*for trigger on edit cases*/
        setTimeout(() => {
            let bookId = $("#book_id").val();
            getDocNumberByBookId(bookId);
        }, 0);

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

            /* Procurement Type */
            const $procurementTypeSelect = $('#procurement_type');
            const poProcurementType = @json($po->procurement_type ?? '') || parameters?.po_procurement_type || '';
            const PO_PROCUREMENT_TYPE_VALUES = @json(\App\Helpers\CommonHelper::PO_PROCUREMENT_TYPE_VALUES);

            if (poProcurementType === 'All') {
                $procurementTypeSelect.empty();
                PO_PROCUREMENT_TYPE_VALUES.forEach(function(value) {

                    $procurementTypeSelect.append(
                        $('<option>', {
                            value: value,
                            text: value,
                            selected: value === poProcurementType,
                        })
                    );
                });
            } else {
                $procurementTypeSelect
                    .empty()
                    .append($('<option>', {
                        value: poProcurementType,
                        text: poProcurementType,
                        selected: true,
                    }));

            }

            /*Reference from*/
            let reference_from_service = parameters.reference_from_service;
            if (reference_from_service.length) {
                let pi = '{{ \App\Helpers\ConstantHelper::PI_SERVICE_ALIAS }}';
                if (reference_from_service.includes(pi)) {
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
                    location.href = '{{ url('purchase-order') }}';
                }, 1500);
            }
        }

        /*Add New Row*/
        // for component item code
        function initializeAutocomplete2(selector, type) {
            $(selector).autocomplete({
                minLength: 0,
                source: function(request, response) {
                    let selectedAllItemIds = [];
                    $("#itemTable tbody [id*='row_']").each(function(index, item) {
                        if (Number($(item).find('[name*="[item_id]"]').val())) {
                            selectedAllItemIds.push(Number($(item).find('[name*="[item_id]"]').val()));
                        }
                    });
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: 'po_item_list',
                            selectedAllItemIds: JSON.stringify(selectedAllItemIds),
                            po_type: $("#po_type").val(),
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
                    if (ui.item?.alternate_u_o_ms) {
                        for (let alterItem of ui.item.alternate_u_o_ms) {
                            uomOption +=
                                `<option value="${alterItem.uom_id}" ${alterItem.is_purchasing ? 'selected' : ''}>${alterItem.uom?.name}</option>`;
                        }
                    }
                    $input.closest('tr').find('[name*=uom_id]').empty().append(uomOption);
                    $input.closest('tr').find("input[name*='attr_group_id']").remove();
                    setTimeout(() => {
                        if (ui.item.is_attr) {
                            $input.closest('tr').find('.attributeBtn').trigger('click');
                        } else {
                            $input.closest('tr').find('.attributeBtn').trigger('click');
                            $input.closest('tr').find('[name*="[qty]"]').focus();
                        }
                    }, 100);

                    getItemCostPrice($input.closest('tr'));
                    validateItems($input, true);
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        // $('#itemId').val('');
                        $(this).attr('data-name', '');
                        $(this).attr('data-code', '');
                        $(this).closest('tr').find("input[name*='[rate]']").val('');
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            }).on("input", function() {
                if ($(this).val().trim() === "") {
                    $(this).removeData("selected");
                    $(this).closest('tr').find("input[name*='component_item_name']").val('');
                    $(this).closest('tr').find("input[name*='item_name']").val('');
                    $(this).closest('tr').find("td[id*='itemAttribute_']").html(defautAttrBtn);
                    $(this).closest('tr').find("input[name*='[item_id]']").val('');
                    $(this).closest('tr').find("input[name*='item_code']").val('');
                    $(this).closest('tr').find("input[name*='attr_name']").remove();
                }
            });
        }

        initializeAutocomplete2(".comp_item_code");
        $(document).on('click', '#addNewItemBtn', (e) => {
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
                let item_id = lastRow.find("[name*='[item_id]']").val();
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
            let type = '{{ request()->route('type') }}';
            let actionUrl = '{{ route('po.item.row', ['type' => ':type']) }}'
                .replace(':type', type) +
                '?count=' + rowsLength +
                '&is_edit=' + '{{ $shortClose }}' +
                '&component_item=' + encodeURIComponent(JSON.stringify(lastTrObj));
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        if (rowsLength) {
                            $("#itemTable > tbody > tr:last").after(data.data.html);
                        } else {
                            $("#itemTable > tbody").html(data.data.html);
                        }
                        initializeAutocomplete2(".comp_item_code");
                        $("select[name='currency_id']").prop('disabled', true);
                        $("select[name='payment_term_id']").prop('disabled', true);
                        $("#vendor_name").prop('readonly', true);
                        $("#book_id").prop('disabled', true);
                        $(".editAddressBtn").addClass('d-none');
                        document.getElementById('copy_item_section').style.display = "";
                        let locationId = $("[name='store_id'] option:selected").val();
                        getLocation(locationId);
                    } else if (data.status == 422) {
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

        /*Delete Row*/
        $(document).on('click', '#deleteBtn', (e) => {
            let itemIds = [];
            let editItemIds = [];
            let piItemIds = [];
            let grnQty = [];
            let geQty = [];
            let asnQty = [];

            $(".form-check-input:checked").each(function(index, item) {
                let tr = $(item).closest('tr');
                let trIndex = tr.index();
                let pi_item_id = Number($(tr).find('[name*="[pi_item_id]"]').val()) || 0;
                let po_item_id = Number($(tr).find('[name*="[po_item_id]"]').val()) || 0;
                let grn_qty = Number($(tr).find('[name*="[grn_qty]"]').val()) || 0;
                let ge_qty = Number($(tr).find('[name*="[ge_qty]"]').val()) || 0;
                let asn_qty = Number($(tr).find('[name*="[asn_qty]"]').val()) || 0;
                if (pi_item_id > 0 && po_item_id > 0) {
                    piItemIds.push({
                        index: trIndex + 1,
                        pi_item_id: pi_item_id
                    });
                }
                if (grn_qty > 0) {
                    grnQty.push({
                        index: trIndex + 1,
                        grn_qty: grn_qty
                    });
                }
                if (ge_qty > 0) {
                    geQty.push({
                        index: trIndex + 1,
                        ge_qty: ge_qty
                    });
                }
                if (asn_qty > 0) {
                    asnQty.push({
                        index: trIndex + 1,
                        asn_qty: asn_qty
                    });
                }
            });

            if (piItemIds.length) {
                e.preventDefault();
                let rowNumbers = piItemIds.map(item => item.index).join(", ");
                Swal.fire({
                    title: 'Error!',
                    text: `You cannot delete pi item(s) at row(s): ${rowNumbers}`,
                    icon: 'error',
                });
                return false;
            }

            if (grnQty.length) {
                e.preventDefault();
                let rowNumbers = grnQty.map(item => item.index).join(", ");
                Swal.fire({
                    title: 'Error!',
                    text: `Can not delete: Item used in GRN: ${rowNumbers}`,
                    icon: 'error',
                });
                return false;
            }
            if (geQty.length) {
                e.preventDefault();
                let rowNumbers = geQty.map(item => item.index).join(", ");
                Swal.fire({
                    title: 'Error!',
                    text: `Can not delete: Item used in Gate Entry: ${rowNumbers}`,
                    icon: 'error',
                });
                return false;
            }
            if (asnQty.length) {
                e.preventDefault();
                let rowNumbers = asnQty.map(item => item.index).join(", ");
                Swal.fire({
                    title: 'Error!',
                    text: `Can not delete: Item used in ASN: ${rowNumbers}`,
                    icon: 'error',
                });
                return false;
            }

            $('#itemTable > tbody .form-check-input').each(function() {
                if ($(this).is(":checked")) {
                    if ($(this).attr('data-id')) {
                        editItemIds.push($(this).attr('data-id'));
                    } else {
                        itemIds.push($(this).val());
                    }
                }
            });
            console.log("editItemIds", editItemIds);

            console.log("itemIds", itemIds);

            if (itemIds.length) {
                itemIds.forEach(function(item, index) {

                    let piItemHiddenId = $(`#row_${item}`).find("input[name*='[pi_item_hidden_ids]']")
                        .val();

                    if (piItemHiddenId) {
                        let idsToRemove = piItemHiddenId.split(',');
                        let selectedPiIds = localStorage.getItem('selectedPiIds');
                        if (selectedPiIds) {
                            selectedPiIds = JSON.parse(selectedPiIds);
                            let updatedIds = selectedPiIds.filter(id => !idsToRemove.includes(id));
                            localStorage.setItem('selectedPiIds', JSON.stringify(updatedIds));
                        }
                    }

                    console.log($(`#row_${item}`).length, $(`#row_${item}`));

                    $(`#row_${item}`).remove();
                });
            }

            if (editItemIds.length == 0 && itemIds.length == 0) {
                alert("Please first add & select row item.");
            }
            if (editItemIds.length) {
                $("#deleteComponentModal").find("#deleteConfirm").attr('data-ids', JSON.stringify(editItemIds));
                $("#deleteComponentModal").modal('show');
            }

            if (!$("tr[id*='row_']").length) {
                $("#itemTable > thead .form-check-input").prop('checked', false);
                $(".prSelect").prop('disabled', false);
                $("select[name='currency_id']").prop('disabled', false);
                $("select[name='payment_term_id']").prop('disabled', false);
                $(".editAddressBtn").removeClass('d-none');
                $("#vendor_name").prop('readonly', false);
                $("#book_id").prop('disabled', false);
                document.getElementById('copy_item_section').style.display = "none";
                getLocation();
            }
            setTableCalculation();
        });

        /*Check attrubute*/
        $(document).on('click', '.attributeBtn', (e) => {
            let tr = e.target.closest('tr');
            let item_name = tr.querySelector('[name*=item_code]').value;
            let item_id = tr.querySelector('[name*="[item_id]"]').value;
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
                alert("Please select first item name.");
            }
        });

        /*For comp attr*/
        function getItemAttribute(itemId, rowCount, selectedAttr, tr) {

            let isPi = 0;
            if ($(tr).find('[name*="pi_item_id"]').length) {
                isPi = Number($(tr).find('[name*="pi_item_id"]').val()) || 0;
            }
            if (!isPi) {
                if ($(tr).find('td[id*="itemAttribute_"]').data('disabled')) {
                    isPi = 1;
                }
            }
            let poItemId = 0;
            if ($(tr).find('[name*="po_item_id"]').length) {
                poItemId = Number($(tr).find('[name*="po_item_id"]').val()) || 0;
            }
            let type = '{{ request()->route('type') }}';
            let actionUrl = '{{ route('po.item.attr', ['type' => ':type']) }}'
                .replace(':type', type) +
                `?item_id=${itemId}&rowCount=${rowCount}&selectedAttr=${selectedAttr}&isPi=${isPi}`;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        $("#attribute tbody").empty();
                        $("#attribute table tbody").append(data.data.html)
                        $(tr).find('td:nth-child(2)').find("[name*='[attr_name]']").remove();
                        $(tr).find('td:nth-child(2)').append(data.data.hiddenHtml);
                        $(tr).find("td[id*='itemAttribute_']").attr('attribute-array', JSON.stringify(data
                            .data.itemAttributeArray));
                        if (data.data.attr) {
                            $("#attribute").modal('show');
                            $(".select2").select2();
                        }
                    }
                });
            });
        }

        // Event listener for Edit Address button click
        $(document).on('click', '.editAddressBtn', (e) => {
            let addressType = $(e.target).closest('a').attr('data-type');
            let vendorId = $("#vendor_id").val() || '';
            let addressId = '';
            if (addressType == 'vendor_address') {
                addressId = $("#vendor_address_id").val() || '';
            }
            if (addressType == 'delivery_address') {
                addressId = $("#delivery_address_id").val() || '';
            }
            let routeType = '{{ request()->route('type') }}';
            let actionUrl = `{{ route('po.edit.address', ['type' => ':type']) }}`
                .replace(':type', routeType) +
                `?vendor_id=${vendorId}&address_id=${addressId}&type=${addressType}`;

            fetch(actionUrl)
                .then(response => response.json())
                .then(data => {
                    if (!data.status) {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                        });
                        return false;
                    }
                    if (data.status === 200) {
                        if (data.data.html) {
                            $("#edit-address .modal-dialog").html(data.data.html);
                        }
                        $("#edit-address").modal('show');
                        initializeFormComponents(data.data.selectedAddress);
                        $("#address_type").val(addressType);
                        let v = $("#vendor_id").val();
                        $("#hidden_vendor_id").val(v);
                        if (addressType == 'vendor_address') {
                            $("#vendor_address_id").val(data.data.selectedAddress.id);
                        }
                        if (addressType == 'delivery_address') {
                            $("#delivery_address_id").val(data.data.selectedAddress.id);
                        }
                    } else {
                        console.error('Failed to fetch address data:', data.message);
                    }
                })
                .catch(error => console.error('Error fetching address data:', error));
        });

        $(document).on('change', "[name='address_id']", (e) => {
            const selectedValue = $(e.target).val();
            if (!selectedValue) {
                $("#city_id").removeClass('disabled-input');
                $("#pincode").removeClass('disabled-input');
                $("#address").removeClass('disabled-input');
                $("#city_id").val('');
                $("#pincode").val('');
                $("#address").val('');
                return false;
            } else {
                $form.find(":input").not(e.target).not("button, [type='button'], [type='submit']").addClass(
                    'disabled-input');
                $(this).removeClass('disabled-input');
            }
            let vendorId = $("#vendor_id").val();
            let addressType = $("#address_type").val();
            let addressId = selectedValue

            let type = '{{ request()->route('type') }}';
            let actionUrl = `{{ route('po.edit.address', ['type' => ':type']) }}`
                .replace(':type', type) +
                `?type=${addressType}&vendor_id=${vendorId}&address_id=${addressId}`;
            fetch(actionUrl)
                .then(response => response.json())
                .then(data => {
                    if (!data.status) {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                        });
                        return false;
                    }
                    if (data.status === 200) {
                        initializeFormComponents(data.data.selectedAddress);
                    } else {
                        console.error('Failed to fetch address data:', data.message);
                    }
                })
                .catch(error => console.error('Error fetching address data:', error));
        });

        function initializeFormComponents(selectedAddress) {
            const countrySelect = $('#country_id');
            fetch('/countries')
                .then(response => response.json())
                .then(data => {
                    countrySelect.empty();
                    countrySelect.append('<option value="">Select Country</option>');
                    data.data.countries.forEach(country => {
                        const isSelected = country.value == selectedAddress.country.id;
                        countrySelect.append(new Option(country.label, country.value, isSelected, isSelected));
                    });
                    if (selectedAddress.country.id) {
                        countrySelect.trigger('change');
                    }
                })
                .catch(error => console.error('Error fetching countries:', error));

            countrySelect.on('change', function() {
                let countryValue = $(this).val();
                let stateSelect = $('#state_id');
                stateSelect.empty().append('<option value="">Select State</option>');

                if (countryValue) {
                    fetch(`/states/${countryValue}`)
                        .then(response => response.json())
                        .then(data => {
                            data.data.states.forEach(state => {
                                const isSelected = state.value == selectedAddress.state.id;
                                stateSelect.append(new Option(state.label, state.value, isSelected,
                                    isSelected));
                            });
                            if (selectedAddress.state.id) {
                                stateSelect.trigger('change');
                            }
                        })
                        .catch(error => console.error('Error fetching states:', error));
                }
            });
            $('#state_id').on('change', function() {
                let stateValue = $(this).val();
                let citySelect = $('#city_id');
                citySelect.empty().append('<option value="">Select City</option>');
                if (stateValue) {
                    fetch(`/cities/${stateValue}`)
                        .then(response => response.json())
                        .then(data => {
                            data.data.cities.forEach(city => {
                                const isSelected = city.value == selectedAddress.city.id;
                                console.log(isSelected);
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

        /*Display item detail*/
        $(document).on('input change focus', '#itemTable tr input', (e) => {
            let currentTr = e.target.closest('tr');
            let rowCount = $(currentTr).attr('data-index');
            let pName = $(currentTr).find("[name*='component_item_name']").val();
            let itemId = $(currentTr).find("[name*='[item_id]']").val();
            let po_item_id = $(currentTr).find("[name*='[po_item_id]']").val();
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

                let selectedDelivery = {
                    delivery: []
                };
                $(currentTr).find("[name*='delivery'][name*='[d_qty]']").each(function(index, item) {
                    let $td = $(item).closest('td');
                    let dQty = $(item).val();
                    let dDate = $td.find('[name*="[d_date]"]').val();

                    selectedDelivery.delivery.push({
                        dDate: dDate,
                        dQty: dQty
                    });
                });

                let uomId = $(currentTr).find("[name*='[uom_id]']").val() || '';
                let qty = $(currentTr).find("[name*='[qty]']").val() || '';
                let type = '{{ request()->route('type') }}';
                let actionUrl = '{{ route('po.get.itemdetail', ['type' => ':type']) }}'
                    .replace(':type', type) +
                    `?item_id=${itemId}&selectedAttr=${encodeURIComponent(JSON.stringify(selectedAttr))}&remark=${remark}&uom_id=${uomId}&qty=${qty}&delivery=${encodeURIComponent(JSON.stringify(selectedDelivery))}&po_item_id=${po_item_id}`;
                fetch(actionUrl).then(response => {
                    return response.json().then(data => {
                        if (data.status == 200) {
                            selectedDelivery = [];
                            $("#itemDetailDisplay").html(data.data.html);
                        }
                    });
                });
            }
        });

        /* Address Submit */
        $(document).on('click', '.submitAddress', function(e) {
            $('.ajax-validation-error-span').remove();
            e.preventDefault();
            var innerFormData = new FormData();
            $('#edit-address').find('input,textarea,select').each(function() {
                innerFormData.append($(this).attr('name'), $(this).val());
            });

            var method = "POST";
            var type = '{{ request()->route('type') }}';
            let addressType = $("#address_type").val();
            var url = '{{ route('po.address.save', ['type' => ':type']) }}'.replace(':type', type);
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
                        let addressDisplay = data?.data?.new_address?.display_address || data?.data
                            ?.add_new_address || ''
                        if (addressType == 'vendor_address') {
                            $("#vendor_address_id").val(data?.data?.new_address?.id);
                            $(".vendor_address").text(addressDisplay);
                        }
                        if (addressType == 'delivery_address') {
                            $("#delivery_address_id").val(data?.data?.new_address?.id);
                            $(".delivery_address").text(addressDisplay);
                            if (data?.data?.add_new_address) {
                                $("#delivery_address_id").val('');
                                let country_id = $("#country_id").val() || '';
                                let state_id = $("#state_id").val() || '';
                                let city_id = $("#city_id").val() || '';
                                let pincode = $("#pincode").val() || '';
                                let address = $("#address").val() || '';

                                $("#delivery_country_id").val(country_id);
                                $("#delivery_state_id").val(state_id);
                                $("#delivery_city_id").val(city_id);
                                $("#delivery_pincode").val(pincode);
                                $("#delivery_address").val(address);
                            } else {
                                $("#delivery_country_id").val('');
                                $("#delivery_state_id").val('');
                                $("#delivery_city_id").val('');
                                $("#delivery_pincode").val('');
                                $("#delivery_address").val('');
                            }
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
            localStorage.setItem('deletedPiItemIds', JSON.stringify(ids));
            $("#deleteComponentModal").modal('hide');

            if (ids.length) {
                ids.forEach((id, index) => {

                    let piItemHiddenId = $(`.form-check-input[data-id='${id}']`).closest('tr').find(
                        "input[name*='[pi_item_hidden_ids]']").val();
                    if (piItemHiddenId) {
                        let idsToRemove = piItemHiddenId.split(',');
                        let selectedPiIds = localStorage.getItem('selectedPiIds');
                        if (selectedPiIds) {
                            selectedPiIds = JSON.parse(selectedPiIds);
                            let updatedIds = selectedPiIds.filter(id => !idsToRemove.includes(id));
                            localStorage.setItem('selectedPiIds', JSON.stringify(updatedIds));
                        }
                    }

                    $(`.form-check-input[data-id='${id}']`).closest('tr').remove();

                });
            }
            setTableCalculation();
            if (!$("#itemTable [id*=row_]").length) {
                $("th .form-check-input").prop('checked', false);
                $(".prSelect").prop('disabled', false);
                $("select[name='currency_id']").prop('disabled', false);
                $("select[name='payment_term_id']").prop('disabled', false);
                $(".editAddressBtn").removeClass('d-none');
                $("#vendor_name").prop('readonly', false);
                $("#book_id").prop('disabled', false);
                getLocation();

                let vendorName = $("#vendor_name").attr("data-name");
                let vendorId = $("#vendor_id").val() || '';
                if (vendorId) {
                    const item = {
                        label: vendorName,
                        value: vendorName,
                        id: vendorId
                    };
                    $('#vendor_name')
                        .val(item.label)
                        .data('ui-autocomplete')
                        ._trigger('select', null, {
                            item: item
                        });
                    $("#vendor_name").val(vendorId).trigger('change');
                }

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

        /*submit attribute*/
        $(document).on('click', '.submitAttributeBtn', (e) => {
            let rowCount = $("[id*=row_].trselected").attr('data-index');
            $(`[name="components[${rowCount}][qty]"]`).focus();
            $("#attribute").modal('hide');
        });

        /*Open Pr model*/
        let poOrderTable;

        $(document).on('click', '.prSelect', function(e) {
            $("#prModal").modal('show');
            $('#prModal').one('shown.bs.modal', function() {
                openPurchaseRequest();
                const tableSelector = '#prModal .po-order-detail';
                if ($(tableSelector).length) {
                    if ($.fn.DataTable.isDataTable(tableSelector)) {
                        poOrderTable = $(tableSelector).DataTable();
                        poOrderTable.ajax.reload();
                    } else {
                        getIndents();
                    }
                }
            });
        });

        function openPurchaseRequest() {
            initializeAutocompleteQt("vendor_code_input_qt", "vendor_id_qt_val", "vendor_list", "vendor_code",
                "company_name");
            // initializeAutocompleteQt("book_code_input_qt", "book_id_qt_val", "book_pi", "book_code", "");
            initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "pi_document_qt", "book_code",
                "document_number");
            initializeAutocompleteQt("pi_so_no_input_qt", "pi_so_qt_val", "pi_so_qt", "book_code", "document_number");
            // initializeAutocompleteQt("item_name_input_qt", "item_id_qt_val", "comp_item", "item_code", "item_name");
            // initializeAutocompleteQt("department_po", "department_id_po", "department", "name", "");
            // initializeAutocompleteQt("store_po", "store_id_po", "location", "store_name", "");

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
                            vendor_id: $("#vendor_id_qt_val").val(),
                            header_book_id: $("#book_id").val(),
                            store_id: $("#store_id_po").val() || '',
                            module_type: '{{ request()->route('type') }}'
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    // label: `${item[labelKey1]} ${labelKey2 ? (item[labelKey2] ? '(' + item[labelKey2] + ')' : '') : ''}`,
                                    label: `${item[labelKey1]}${labelKey2 ? (item[labelKey2] ? '-' + item[labelKey2] : '') : ''}`,
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
                appendTo: '#prModal',
                select: function(event, ui) {
                    var $input = $(this);
                    $input.val(ui.item.label);
                    $("#" + selectorSibling).val(ui.item.id);
                    $('#prModal .po-order-detail').DataTable().ajax.reload();
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#" + selectorSibling).val("");
                        $('#prModal .po-order-detail').DataTable().ajax.reload();
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                    $('#prModal .po-order-detail').DataTable().ajax.reload();
                }
            }).blur(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                    $('#prModal .po-order-detail').DataTable().ajax.reload();
                }
            });
        }


        function renderData(data) {
            return data ? data : '';
        }

        function getDynamicParams() {
            let selectedPiIds = localStorage.getItem('selectedPiIds') ?? '[]';
            selectedPiIds = JSON.parse(selectedPiIds);
            selectedPiIds = encodeURIComponent(JSON.stringify(selectedPiIds));
            return {
                document_date: $("[name='document_date']").val() || '',
                header_book_id: $("#book_id").val() || '',
                series_id: $("#book_id_qt_val").val() || '',
                document_number: $("#document_id_qt_val").val() || '',
                item_id: $("#item_id_qt_val").val() || '',
                vendor_id: $("#vendor_id_qt_val").val(),
                department_id: $("#department_id_po").val() || '',
                store_id: $("#store_id").val() || '',
                sub_store_id: $("#sub_store_id_po").val() || '',
                so_id: $("#pi_so_qt_val").val() || '',
                item_search: $("#item_name_search").val(),
                selected_pi_ids: encodeURIComponent(selectedPiIds)
            };
        }

        function getIndents() {
            const type = '{{ request()->route('type') }}';
            const ajaxUrl = '{{ route('po.get.pi', ['type' => ':type']) }}'.replace(':type', type);
            var columns = [];
            if (type == 'purchase-order') {
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
                        data: 'book_name',
                        name: 'book_name',
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
                        data: 'uom',
                        name: 'uom',
                        render: renderData,
                        orderable: false,
                        searchable: false
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
                    {
                        data: 'pending_po',
                        name: 'pending_po',
                        render: renderData,
                        orderable: false,
                        searchable: false,
                        createdCell: function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-end');
                        }
                    },
                    {
                        data: 'avl_stock',
                        name: 'avl_stock',
                        render: renderData,
                        orderable: false,
                        searchable: false,
                        createdCell: function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-end');
                        }
                    },
                    {
                        data: 'vendor_select',
                        name: 'vendor_select',
                        render: renderData,
                        orderable: true,
                        searchable: true
                    },
                    {
                        data: 'so_no',
                        name: 'so_no',
                        render: renderData,
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'location',
                        name: 'location',
                        render: renderData,
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'requester',
                        name: 'requester',
                        render: renderData,
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'remarks',
                        name: 'remarks',
                        render: renderData,
                        orderable: false,
                        searchable: false
                    },
                ];
            } else {
                var columns = [{
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
                        data: 'book_name',
                        name: 'book_name',
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
                        data: 'uom',
                        name: 'uom',
                        render: renderData,
                        orderable: false,
                        searchable: false
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
                    {
                        data: 'pending_po',
                        name: 'pending_po',
                        render: renderData,
                        orderable: false,
                        searchable: false,
                        createdCell: function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-end');
                        }
                    },
                    {
                        data: 'avl_stock',
                        name: 'avl_stock',
                        render: renderData,
                        orderable: false,
                        searchable: false,
                        createdCell: function(td, cellData, rowData, row, col) {
                            $(td).addClass('text-end');
                        }
                    },
                    {
                        data: 'vendor_select',
                        name: 'vendor_select',
                        render: renderData,
                        orderable: false,
                        searchable: true
                    },
                    {
                        data: 'location',
                        name: 'location',
                        render: renderData,
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'requester',
                        name: 'requester',
                        render: renderData,
                        orderable: false,
                        searchable: false
                    },
                ];
            }
            initializeDataTableCustom('#prModal .po-order-detail',
                ajaxUrl,
                columns,
            );
        }
        $(document).on('keyup', '#item_name_search', (e) => {
            $('#prModal .po-order-detail').DataTable().ajax.reload();
            $('#prModal .po-order-detail .vendor-select', this).trigger('change.select2');
        });

        /*Checkbox for pi item list*/
        @if ($serviceAlias == 'po')
            $(document).on('change', '#prModal .po-order-detail > thead .form-check-input', (e) => {
                if (e.target.checked) {
                    // in this add check that the only select the vendor from the
                    if ($('.pi_item_checkbox').first().closest('tr').find("[name='vend_name']").length) {
                        let selectedVendorId = $('.pi_item_checkbox:checked').first().closest('tr').find(
                            "[name='vend_name']").val() || '';
                        $("[name='vend_name']").each(function(itemIndex, vendorItem) {
                            if (!selectedVendorId) {
                                let firstVendor = $(vendorItem).val();
                                if (firstVendor) {
                                    selectedVendorId = firstVendor;
                                }
                            }
                            if (selectedVendorId && $(vendorItem).val() == selectedVendorId) {
                                $(vendorItem).closest('tr').find('.form-check-input').prop('checked', true);
                            } else {
                                $(vendorItem).closest('tr').find('.form-check-input').prop('checked',
                                    false);
                            }
                        })
                    } else {
                        $(".po-order-detail > tbody .form-check-input").each(function() {
                            $(this).prop('checked', true);
                        });
                    }
                } else {
                    $(".po-order-detail > tbody .form-check-input").each(function() {
                        $(this).prop('checked', false);
                    });
                    localStorage.removeItem('selectedVendorId');
                }
            });
        @else
            $(document).on('change', '.po-order-detail > tbody .form-check-input', (e) => {
                if (!$(".po-order-detail > tbody .form-check-input:not(:checked)").length) {
                    $('.po-order-detail > thead .form-check-input').prop('checked', true);
                } else {
                    $('.po-order-detail > thead .form-check-input').prop('checked', false);
                }
            });
        @endif

        function getSelectedPiIDS() {
            let ids = [];
            $('.pi_item_checkbox:checked').each(function() {
                ids.push($(this).val());
            });
            return ids;
        }

        $(document).ready(function() {
            localStorage.removeItem('selectedVendorId');
        });
        @if ($serviceAlias == 'po')
            $(document).on('change', '#prDataTable .pi_item_checkbox', function(e) {
                let currentPoVendorId = $('#vendor_id').val() || '';
                let selectedVendorId = localStorage.getItem('selectedVendorId') || null;
                let currentCheckedVendorId = $(this).closest('tr').find("[name='vend_name']").val();
                if (!currentCheckedVendorId) {
                    this.checked = false;
                    return;
                }
                if (currentPoVendorId && currentPoVendorId !== currentCheckedVendorId) {
                    this.checked = false;
                    console.log('Vendor mismatch:', currentPoVendorId, currentCheckedVendorId);
                    return;
                }
                if (this.checked) {
                    if (!selectedVendorId) {
                        localStorage.setItem('selectedVendorId', currentCheckedVendorId);
                    } else if (selectedVendorId !== currentCheckedVendorId) {
                        this.checked = false;
                        return;
                    }
                } else {
                    let remainingChecked = $('.pi_item_checkbox:checked').filter(function() {
                        return $(this).closest('tr').find("[name='vend_name']").val() === selectedVendorId;
                    }).length;

                    if (remainingChecked === 0) {
                        localStorage.removeItem('selectedVendorId');
                    }
                }
            });
        @endif
        $(document).on('click', '.prProcess', (e) => {
            let ids = getSelectedPiIDS();
            if (!ids.length) {
                $("[name='pi_item_ids']").val('');
                $("#prModal").modal('hide');
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select at least one one quotation',
                    icon: 'error',
                });
                return false;
            }
            $("[name='pi_item_ids']").val(ids);

            let vendorIds = [];
            $('.pi_item_checkbox:checked').each(function() {
                let venId = $(this).closest('tr').find("[name='vend_name']").val();
                if (venId) {
                    vendorIds.push(venId);
                }
            });
            uniqueVendor = [...new Set(vendorIds)];
            if (uniqueVendor.length > 1) {
                Swal.fire({
                    title: 'Error!',
                    text: "You can't process with different vendor.",
                    icon: 'error',
                });
                return false;
            }
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
                                type: 'po_item_list',
                                selectedAllItemIds: JSON.stringify(selectedAllItemIds),
                                po_type: $("#po_type").val(),
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
                        $input.val(itemCode);
                        let uomOption = `<option value=${uomId}>${uomName}</option>`;
                        if (ui.item?.alternate_u_o_ms) {
                            for (let alterItem of ui.item.alternate_u_o_ms) {
                                uomOption +=
                                    `<option value="${alterItem.uom_id}" ${alterItem.is_purchasing ? 'selected' : ''}>${alterItem.uom?.name}</option>`;
                            }
                        }
                        $input.closest('tr').find('[name*=uom_id]').empty().append(uomOption);
                        $input.closest('tr').find("input[name*='attr_group_id']").remove();
                        setTimeout(() => {
                            if (ui.item.is_attr) {
                                $input.closest('tr').find('.attributeBtn').trigger('click');
                            } else {
                                $input.closest('tr').find('.attributeBtn').trigger('click');
                                $input.closest('tr').find('[name*="[qty]"]').val('').focus();
                            }
                        }, 100);
                        getItemCostPrice($input.closest('tr'));
                        validateItems($input, true);
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
                }).on("input", function() {
                    if ($(this).val().trim() === "") {
                        $(this).removeData("selected");
                        $(this).closest('tr').find("input[name*='component_item_name']").val('');
                        $(this).closest('tr').find("input[name*='item_name']").val('');
                        $(this).closest('tr').find("td[id*='itemAttribute_']").html(defautAttrBtn);
                        $(this).closest('tr').find("input[name*='[item_id]']").val('');
                        $(this).closest('tr').find("input[name*='item_code']").val('');
                        $(this).closest('tr').find("input[name*='attr_name']").remove();
                    }
                });
            }

            let vendorId = uniqueVendor[0];
            let currencyId = $("select[name='currency_id']").val();
            let transactionDate = $("input[name='document_date']").val() || '';

            let groupItems = [];

            $('tr[data-group-item]').each(function() {
                let groupItemData = $(this).data('group-item');
                groupItems.push(groupItemData);
            });

            groupItems = JSON.stringify(groupItems);

            ids = JSON.stringify(ids);
            let current_row_count = $("#itemTable tbody tr[id*='row_']").length;
            let type = '{{ request()->route('type') }}'; // Dynamically fetch the `type` from the current route
            let actionUrl = '{{ route('po.process.pi-item', ['type' => ':type']) }}'
                .replace(':type', type) +
                '?ids=' + encodeURIComponent(ids) +
                '&vendor_id=' + encodeURIComponent(vendorId) +
                '&currency_id=' + encodeURIComponent(currencyId) +
                '&d_date=' + encodeURIComponent(transactionDate) +
                '&groupItems=' + encodeURIComponent(groupItems) +
                '&current_row_count=' + current_row_count;

            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        vendorOnChange(data?.data?.vendor?.id);
                        let newIds = getSelectedPiIDS();
                        let existingIds = localStorage.getItem('selectedPiIds');
                        if (existingIds) {
                            existingIds = JSON.parse(existingIds);
                            const mergedIds = Array.from(new Set([...existingIds, ...newIds]));
                            localStorage.setItem('selectedPiIds', JSON.stringify(mergedIds));
                        } else {
                            localStorage.setItem('selectedPiIds', JSON.stringify(newIds));
                        }

                        let existingIdsUpdate = JSON.parse(localStorage.getItem('selectedPiIds'));
                        $("[name='pi_item_ids']").val(existingIdsUpdate.join(','));

                        let vendor = data?.data?.vendor || '';
                        let finalDiscounts = data?.data?.finalDiscounts;
                        let finalExpenses = data?.data?.finalExpenses;

                        if ($("#itemTable .mrntableselectexcel").find("tr[id*='row_']").length) {
                            $("#itemTable .mrntableselectexcel tr[id*='row_']:last").after(data.data
                                .pos);
                        } else {
                            $("#itemTable .mrntableselectexcel").empty().append(data.data.pos);
                        }

                        setTimeout(() => {
                            $("#itemTable .mrntableselectexcel tr").each(function(index,
                                item) {
                                let currentIndex = index + 1;
                                setAttributesUIHelper(currentIndex, "#itemTable");
                            });
                        }, 100);

                        //Update Qnt
                        if (data?.data?.updatedGroupItems?.length) {
                            $('tr[data-group-item]').each(function() {
                                let obj = {};
                                obj.item_id = Number($(this).find(
                                    "input[name*='[item_id]']").val()) || '';
                                $(this).find("[name*='[uom_id]']").prop('disabled', false);
                                let n = $(this).find("[name*='[uom_id]']").attr('name');
                                obj.uom_id = Number($(`[name='${n}']`).val()) || '';
                                $(`[name='${n}']`).prop('disabled', true);
                                obj.attributes = '';
                                if ($(this).find("[name*='[attr_name]']").length) {
                                    let attributesArray = [];
                                    $(this).find("[name*='[attr_name]']").each(function() {
                                        let n = $(this).attr('name').replace(
                                            'attr_name', 'item_attr_id');
                                        let item_attr_id = $(`input[name="${n}"]`)
                                            .val() || '';
                                        let attr_name = $(this).val() || '';
                                        if (item_attr_id && attr_name) {
                                            attributesArray.push(
                                                `${item_attr_id}:${attr_name}`);
                                        }
                                    });
                                    obj.attributes = attributesArray.sort().join(', ');
                                }
                                for (let rowItem of data.data.updatedGroupItems) {
                                    let sortedRowAttributes = rowItem.attributes
                                        .split(', ')
                                        .sort()
                                        .join(', ');
                                    if (
                                        obj.attributes === sortedRowAttributes &&
                                        obj.uom_id === rowItem.uom_id &&
                                        obj.item_id === rowItem.item_id
                                    ) {
                                        $(this).find("input[name*='[qty]']").val(Number(
                                            rowItem.total_qty).toFixed(2));
                                        $(this).find("input[name*='[pi_item_hidden_ids]']")
                                            .val(rowItem.pi_item_ids);
                                    }
                                }
                            });
                        }


                        initializeAutocomplete2(".comp_item_code");
                        $("#prModal").modal('hide');
                        $("select[name='currency_id']").prop('disabled', true);
                        $("select[name='payment_term_id']").prop('disabled', true);
                        $("#vendor_name").prop('readonly', true);
                        $("#book_id").prop('disabled', true);
                        $(".editAddressBtn").addClass('d-none');
                        document.getElementById('copy_item_section').style.display = "";
                        let locationId = $("[name='store_id'] option:selected").val();
                        getLocation(locationId);
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
                        setTimeout(() => {
                            setTableCalculation();
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
                        $(".billing_address").text('-');
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

        /*Remove item level discount*/
        $(document).on("click", ".deleteItemDiscountRow", (e) => {
            let rowCount = e.target.closest('a').getAttribute('data-row-count') || 0;
            let rowIndex = $(e.target).closest('tr').index();
            let id = e.target.closest('a').getAttribute('data-id') || 0;
            if (Number(id)) {
                $("#deleteItemDiscModal").find("#deleteItemDiscConfirm").attr('data-id', id);
                $("#deleteItemDiscModal").find("#deleteItemDiscConfirm").attr('data-row-index', rowIndex);
                $("#deleteItemDiscModal").find("#deleteItemDiscConfirm").attr('data-row-count', rowCount);
                $("#deleteItemDiscModal").modal('show');
            }
        });

        /*Delete server side rows*/
        $(document).on('click', '#deleteItemDiscConfirm', (e) => {
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
            $("[name*='[item_d_amnt]']").each(function(index, item) {
                total_head_dis += Number($(item).val());
            });
            $("#disItemFooter #total").text(total_head_dis.toFixed(2));
            $(`[id*='row_${rowCount}']`).find("[name*='[discounts]'").remove();

            let hiddenDis = '';
            let totalAmnt = 0;
            $(".display_discount_row").find("[name*='[item_d_amnt]']").each(function(index, item) {
                let key = index + 1;
                let id = $(item).closest('tr').find(`[name*="[item_d_id]"]`).val();
                let name = $(item).closest('tr').find(`[name*="[item_d_name]"]`).val();
                let perc = $(item).closest('tr').find(`[name*="[item_d_perc]"]`).val();
                let amnt = $(item).val();
                totalAmnt += Number(amnt);
                hiddenDis += `<input type="hidden" value="${id}" name="components[${rowCount}][discounts][${index+1}][id]">
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
            if (id) {
                $("#deleteHeaderDiscModal").find("#deleteHeaderDiscConfirm").attr('data-id', id);
                $("#deleteHeaderDiscModal").find("#deleteHeaderDiscConfirm").attr('data-row-index', rowIndex);
                $("#deleteHeaderDiscModal").modal('show');
            }
        });

        /*Delete server side rows*/
        $(document).on('click', '#deleteHeaderDiscConfirm', (e) => {
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
                if (perc) {
                    let disAmount = itemValue * Number(perc) / 100;
                    $(item).closest('tr').find("[name*='[d_amnt]']").prop('readonly', true).val(disAmount
                        .toFixed(2));
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
            if (id) {
                $("#deleteHeaderExpModal").find("#deleteHeaderExpConfirm").attr('data-id', id);
                $("#deleteHeaderExpModal").find("#deleteHeaderExpConfirm").attr('data-row-index', rowIndex);
                $("#deleteHeaderExpModal").modal('show');
            }
        });

        /*Delete server side rows*/
        $(document).on('click', '#deleteHeaderExpConfirm', (e) => {
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
                if (perc) {
                    let total = itemValue * perc / 100;
                    $(item).closest('tr').find('[name*="e_amnt"]').prop('readonly', true).val(total.toFixed(
                        2));
                } else {
                    $(item).closest('tr').find('[name*="e_amnt"]').prop('readonly', false).val('');
                }
            });

            summaryExpTotal();
            setTableCalculation();
        });

        /*Remove delivery row*/
        $(document).on('click', '.deleteItemDeliveryRow', (e) => {
            let id = $(e.target).closest('a').attr('data-id') || 0;
            let rowCount = $(e.target).closest('a').attr('data-row-count') || 0;
            let rowIndex = $(e.target).closest('a').attr('data-index') || 0;
            if (Number(id)) {
                $("#deleteDeliveryModal").find("#deleteDeliveryConfirm").attr('data-id', id);
                $("#deleteDeliveryModal").find("#deleteDeliveryConfirm").attr('data-row-index', rowIndex);
                $("#deleteDeliveryModal").find("#deleteDeliveryConfirm").attr('data-row-count', rowCount);
                $("#deleteDeliveryModal").modal('show');

            }
        });

        $(document).on('click', '#deleteDeliveryConfirm', (e) => {
            let id = e.target.getAttribute('data-id');
            let rowIndex = e.target.getAttribute('data-row-index');
            let rowCount = e.target.getAttribute('data-row-count');
            $("#deleteDeliveryModal").modal('hide');
            $(`.display_delivery_row:nth-child(${rowIndex})`).remove();
            let ids = JSON.parse(localStorage.getItem('deletedDelivery')) || [];
            if (!ids.includes(id)) {
                ids.push(id);
            }
            localStorage.setItem('deletedDelivery', JSON.stringify(ids));
            $('.display_delivery_row').each(function(index, item) {
                let a = `components[${rowCount}][delivery][${index+1}][d_qty]`;
                let b = `components[${rowCount}][delivery][${index+1}][d_date]`;
                $(item).find("[name*='[d_qty]']").prop('name', a);
                $(item).find("td:first").text(index + 1);
            });
            $(`[name*='components[${rowCount}][delivery][${rowIndex}]']`).remove();
            totalScheduleQty();
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
                $("#poEditForm").submit();
            }
        });

        function initializeAutocompleteTED(selector, idSelector, nameSelector, type, percentageVal) {
            let modalId = '#' + $("#" + selector).closest('.modal').attr('id');
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
                            type: type,
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
                appendTo: modalId,
                select: function(event, ui) {
                    var $input = $(this);
                    var hsnId = ui?.item?.hsn_id;
                    var itemId = ui.item.id;
                    var itemName = ui.item.label;
                    var itemPercentage = ui.item.percentage;

                    console.log('Selected item:', ui.item);

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

        // Get Item Rate
        function getItemCostPrice(currentTr) {
            let vendorId = $("#vendor_id").val();
            let currencyId = $("select[name='currency_id']").val();
            let transactionDate = $("input[name='document_date']").val();
            let itemId = $(currentTr).find("input[name*='[item_id]']").val();
            let attributesRaw = $(currentTr).find('td[attribute-array]').attr('attribute-array');
            let parsedAttributes = attributesRaw ? JSON.parse(attributesRaw) : [];

            let formattedAttributes = parsedAttributes.map(attr => {
                let selectedValue = attr.values_data.find(val => val.selected);
                return {
                    id: attr.id,
                    group_name: attr.group_name,
                    attr_name: attr.attribute_group_id,
                    attr_value: selectedValue ? selectedValue.id : null
                };
            });

            let itemQty = $(currentTr).find("input[name*='[qty]']").val() ?? 0;
            let uomId = $(currentTr).find("select[name*='[uom_id]']").val();
            let queryParams = new URLSearchParams({
                vendor_id: vendorId,
                currency_id: currencyId,
                transaction_date: transactionDate,
                item_id: itemId,
                attr: JSON.stringify(formattedAttributes),
                uom_id: uomId,
                item_qty: itemQty
            });
            let actionUrl = '{{ route('items.get.cost') }}' + '?' + queryParams.toString();
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        let cost = data?.data?.cost || 0;
                        $(currentTr).find("input[name*='[rate]']").val(cost);
                        setTableCalculation();
                    }
                });
            });
        }

        // Revoke Document
        $(document).on('click', '#revokeButton', (e) => {
            let type = '{{ request()->route('type') }}';
            let actionUrl = '{{ route('po.revoke.document', ['type' => ':type']) }}'
                .replace(':type', type) + '?id=' + '{{ $po->id }}';
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


        // Short CLose
        $(document).on('click', '#shortCloseBtn', (e) => {
            let itemIds = [];
            $('#itemTable > tbody .form-check-input').each(function() {
                if ($(this).is(":checked")) {
                    if (Number($(this).closest('tr').find('[name*="po_item_id"]').val())) {
                        itemIds.push(Number($(this).closest('tr').find('[name*="po_item_id"]').val()));
                    }
                }
            });
            if (itemIds.length) {
                itemIds = itemIds.join(',');
                $("[name='short_close_ids']").val(itemIds);
                $("#shortCloseModal").modal('show');
                // $("#amendmentModal").modal('show');
            } else {
                alert("Please first select row item.");
            }
        });

        // Short Close Submit
        $(document).on('click', '#shortCloseBtnSubmit', (e) => {
            let remark = $("#shortCloseModal").find("[name='amend_remark']").val();
            let errorFlag = false;
            if (!remark) {
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
            let type = '{{ request()->route('type') }}';
            $.ajax({
                url: '{{ route('po.short.close.submit', ['type' => ':type']) }}'.replace(':type', type),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                beforeSend: function() {
                    $('#shortCloseBtnSubmit').prop('disabled', true).text('Submitting...');
                },
                success: function(response) {
                    setTimeout(() => {
                        Swal.fire({
                            title: 'Success!',
                            text: response.message,
                            icon: 'success',
                        });
                        window.location.href = '{{ route('po.index', ['type' => ':type']) }}'
                            .replace(':type', type);
                    }, 500);
                },
                error: function(xhr) {
                    alert('Something went wrong! Please try again.');
                },
                complete: function() {
                    $('#shortCloseBtnSubmit').prop('disabled', false).text('Submit');
                }
            });
        });

        $(document).on('click', '.clearPiFilter', (e) => {
            $("#item_name_input_qt").val('');
            $("#item_id_qt_val").val('');
            $("#store_po").val('');
            $("#store_id_po").val('');
            $("#sub_store_po").val('');
            $("#sub_store_id_po").val('');
            $("#vendor_code_input_qt").val('');
            $("#vendor_id_qt_val").val('');
            $("#book_code_input_qt").val('');
            $("#book_id_qt_val").val('');
            $("#document_no_input_qt").val('');
            $("#document_id_qt_val").val('');
            $("#pi_so_no_input_qt").val('');
            $("#pi_so_qt_val").val('');
            $("#item_name_search").val('');
            $('#prModal .po-order-detail').DataTable().ajax.reload();
        });

        $(document).on("autocompletechange autocompleteselect", "#store_po", function(event, ui) {
            let storeId = ui?.item?.id || '';
            initializeAutocompleteQt("sub_store_po", "sub_store_id_po", "sub_store", "name", "");
        });
        setTimeout(() => {
            $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
                let currentIndex = index + 1;
                setAttributesUIHelper(currentIndex, "#itemTable");
            });
        }, 100);
    </script>
@endsection
