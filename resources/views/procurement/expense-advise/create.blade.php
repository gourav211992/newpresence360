@extends('layouts.app')
@section('styles')
    <style>
        #poModal .table-responsive {
            overflow-y: auto;
            max-height: 300px; /* Set the height of the scrollable body */
            position: relative;
        }

        #poModal .po-order-detail {
            width: 100%;
            border-collapse: collapse;
        }

        #poModal .po-order-detail thead {
            position: sticky;
            top: 0; /* Stick the header to the top of the table container */
            background-color: white; /* Optional: Make sure header has a background */
            z-index: 1; /* Ensure the header stays above the body content */
        }
        #poModal .po-order-detail th {
            background-color: #f8f9fa; /* Optional: Background for the header */
            text-align: left;
            padding: 8px;
        }
        #poModal .po-order-detail td {
            padding: 8px;
        }
    </style>
@endsection
@section('content')
    <form class="ajax-input-form" data-module="exp" method="POST" action="{{ route('expense-adv.store') }}" data-redirect="/expense-advice" enctype="multipart/form-data">
        <input type="hidden" name="tax_required" id="tax_required" value="">
        @csrf
        <div class="app-content content ">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Expense Advise</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a href="/">Home</a>
                                            </li>
                                            <li class="breadcrumb-item active">Add New</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                            <input type="hidden" name="document_status" value="draft" id="document_status">
                                <button type="button" onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0">
                                    <i data-feather="arrow-left-circle"></i> Back
                                </button>
                                <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button"
                                    name="action" value="draft"><i data-feather='save'></i> Save as Draft</button>
                                <button type="submit" class="btn btn-primary btn-sm submit-button" name="action"
                                    value="submitted"><i data-feather="check-circle"></i> Submit</button>
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
                                            <div class="col-md-12">
                                                <div class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                                    <div>
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Series <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="book_id" name="book_id">
                                                            <!-- <option value="">Select</option> -->
                                                            @foreach($books as $book)
                                                                <option value="{{$book->id}}">{{ucfirst($book->book_code)}}</option>
                                                            @endforeach
                                                        </select>
                                                        <!-- <input type="hidden" name="mrn_no" id="book_code"> -->
                                                        <input type="hidden" name="book_code" id="book_code">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Expense No <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="document_number" class="form-control" id="document_number">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Expense Date <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" class="form-control" value="{{date('Y-m-d')}}" name="document_date">
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
                                                                    {{ old('header_store_id', $selectedStoreId ?? '') == $erpStore->id ? 'selected' : '' }}>
                                                                    {{ ucfirst($erpStore->store_name) }}
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
                                                        <input type="text" name="reference_number" class="form-control">
                                                    </div>
                                                </div> -->
                                                <div class="row align-items-center mb-1 d-none" id="reference_from">
                                                    <div class="col-md-3">
                                                        <label class="form-label">
                                                            Reference From
                                                        </label>
                                                    </div>
                                                    <div class="col-md-5 action-button">
                                                        <button type="button" class="btn btn-outline-primary btn-sm mb-0 poSelect">
                                                            <i data-feather="plus-square"></i>
                                                            Outstanding PO
                                                        </button>
                                                        <button type="button" class="btn btn-outline-primary btn-sm mb-0 joSelect">
                                                            <i data-feather="plus-square"></i>
                                                            Outstanding JO
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1" id="referenceNoDiv" style="display: none;">
                                                    <div class="col-md-5">
                                                        <input type="hidden" name="reference_type" class="form-control reference_type" id="reference_type_input" readonly>
                                                    </div>
                                                </div>
                                            </div>
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
                                                        <div class="mb-1 vendor-part">
                                                            <label class="form-label">Vendor <span class="text-danger">*</span></label>
                                                            <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" id="vendor_name" name="vendor_name" />
                                                            <input type="hidden" id="vendor_id" name="vendor_id" />
                                                            <input type="hidden" id="vendor_code" name="vendor_code" />
                                                            <input type="hidden" id="shipping_id" name="shipping_id" />
                                                            <input type="hidden" id="billing_id" name="billing_id" />
                                                            <input type="hidden" id="hidden_state_id" name="hidden_state_id" />
                                                            <input type="hidden" id="hidden_country_id" name="hidden_country_id" />
                                                        </div>
                                                        <div class="mb-1 customer-part" style="display: none;">
                                                            <label class="form-label">Customer <span class="text-danger">*</span></label>
                                                            <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" id="customer_name" name="customer_name" />
                                                            <input type="hidden" id="customer_id" name="customer_id" />
                                                            <input type="hidden" id="customer_code" name="customer_code" />
                                                            <input type="hidden" id="cust_shipping_id" name="cust_shipping_id" />
                                                            <input type="hidden" id="cust_billing_id" name="cust_billing_id" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Currency <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="currency_id"></select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="mb-1">
                                                            <label class="form-label">Payment Terms <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="payment_term_id"></select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="customer-billing-section">
                                                            <p>Vendor Details</p>
                                                            <div class="bilnbody">
                                                                <div class="genertedvariables genertedvariablesnone">
                                                                    <label class="form-label w-100">
                                                                        Select Vendor Address <span class="text-danger">*</span>
                                                                        <a href="javascript:;" class="float-end font-small-2 editAddressBtn" id="editShippingAddressBtn" data-type="shipping">
                                                                            <i data-feather='edit-3'></i> Edit
                                                                        </a>
                                                                    </label>
                                                                    <div class="mrnaddedd-prim billing_detail">-</div>
                                                                    <input type="hidden" name="billing_address" id="billing_address">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="customer-billing-section h-100">
                                                            <p>Billing Details</p>
                                                            <div class="bilnbody">
                                                                <div class="genertedvariables genertedvariablesnone">
                                                                    <label class="form-label w-100">
                                                                        Select Billing Address <span class="text-danger">*</span>
                                                                        {{-- <a href="javascript:;" class="float-end font-small-2 editAddressBtn" id="editBillingAddressBtn" data-type="billing">
                                                                            <i data-feather='edit-3'></i> Edit
                                                                        </a> --}}
                                                                    </label>
                                                                    <div class="mrnaddedd-prim delivery_address">-</div>
                                                                    {{-- <input type="hidden" name="delivery_address" id="delivery_address"> --}}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" id="general_section">
                                    <div class="col-md-12">
                                        <div class="card quation-card">
                                            <div class="card-header newheader">
                                                <div>
                                                    <h4 class="card-title">General Information</h4>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3" id="cost_center_div" style="display:none;">
                                                        <div class="mb-1">
                                                        <label class="form-label">Cost Center</label>
                                                        <select class="form-select cost_center" id="cost_center_id" name="cost_center_id">
                                                            <!-- Options will be populated here by the AJAX request -->
                                                        </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                            Supplier Invoice No.
                                                            </label>
                                                            <input type="text" name="supplier_invoice_no"
                                                                class="form-control bg-white supplier_invoice_no"
                                                                placeholder="Enter Supplier Invoice No.">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Supplier Invoice Date
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="date" name="supplier_invoice_date"
                                                                class="form-control bg-white gate-entry supplier_invoice_date" id="datepicker3"
                                                                placeholder="Enter Supplier Invoice Date">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12 " id = "dynamic_fields_section">
                                </div>
                                <div class="card" id="item_section">
                                    <div class="card-body customernewsection-form">
                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader ">
                                                        <h4 class="card-title text-theme">Expense Item Wise Detail</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-sm-end">
                                                    <a href="javascript:;" id="deleteBtn" class="btn btn-sm btn-outline-danger me-50">
                                                    <i data-feather="x-circle"></i> Delete</a>
                                                    <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary">
                                                    <i data-feather="plus"></i> Add New Item</a>
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
                                                                <th class="text-end">Qty</th>
                                                                <th class="text-end">Rate</th>
                                                                <th class="text-end">Value</th>
                                                                <th>Discount</th>
                                                                <th class="text-end">Total</th>
                                                                <th width="50px">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="totalsubheadpodetail">
                                                                <td colspan="7"></td>
                                                                <td class="text-end" id="totalItemValue">0.00</td>
                                                                <td class="text-end" id="totalItemDiscount">0.00</td>
                                                                <td class="text-end" id="TotalEachRowAmount">0.00</td>
                                                            </tr>
                                                            <tr valign="top">
                                                                <td colspan="7" rowspan="12">
                                                                    <table class="table border" id="itemDetailDisplay">
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
                                                                    </table>
                                                                </td>
                                                                <td colspan="4">
                                                                    <table class="table border mrnsummarynewsty">
                                                                        <tr>
                                                                            <td colspan="2" class="p-0">
                                                                                <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between">
                                                                                    <strong>Expense Summary</strong>
                                                                                    <div class="addmendisexpbtn">
                                                                                        <button type="button" class="btn p-25 btn-sm btn-outline-secondary summaryTaxBtn">{{-- <i data-feather="plus"></i> --}} Tax</button>
                                                                                        <button type="button" class="btn p-25 btn-sm btn-outline-secondary summaryDisBtn"><i data-feather="plus"></i> Discount</button>
                                                                                        <button  type="button" class="btn p-25 btn-sm btn-outline-secondary summaryExpBtn"><i data-feather="plus"></i> Expenses</button>
                                                                                    </div>
                                                                                </h6>
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="totalsubheadpodetail">
                                                                            <td width="55%"><strong>Sub Total</strong></td>
                                                                            <td class="text-end" id="f_sub_total">0.00</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Item Discount</strong></td>
                                                                            <td class="text-end" id="f_total_discount">0.00</td>
                                                                        </tr>
                                                                        <tr class="d-none" id="f_header_discount_hidden">
                                                                            <td><strong>Header Discount</strong></td>
                                                                            <td class="text-end" id="f_header_discount">0.00</td>
                                                                        </tr>
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
                                                                            <td class="text-end" id="f_total_after_tax">0.00</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Exp.</strong></td>
                                                                            <td class="text-end" id="f_exp">0.00</td>
                                                                            <input type="hidden" name="expense_amount" class="text-end" id="expense_amount">
                                                                        </tr>
                                                                        <tr class="voucher-tab-foot">
                                                                            <td class="text-primary"><strong>Total After Exp.</strong></td>
                                                                            <td>
                                                                                <div class="quottotal-bg justify-content-end">
                                                                                    <h5 id="f_total_after_exp">0.00</h5>
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
                                                                <input type="file" name="attachment[]" class="form-control" onchange = "addFiles(this,'main_expense_file_preview')" multiple>
                                                                <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                                                            </div>
                                                        </div>
                                                        <div class = "col-md-6" style = "margin-top:19px;">
                                                            <div class = "row" id = "main_expense_file_preview">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Final Remarks</label>
                                                            <textarea type="text" rows="4" name="remarks" class="form-control" placeholder="Enter Remarks here..."></textarea>
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
        @include('procurement.expense-advise.partials.summary-disc-modal')
        {{-- Add expenses modal--}}
        @include('procurement.expense-advise.partials.summary-exp-modal')
        {{-- Add Outstanding PO modal--}}
        @include('procurement.expense-advise.partials.outstanding-po-modal')
        {{-- Add Outstanding JO modal--}}
        @include('procurement.expense-advise.partials.outstanding-jo-modal')
        {{-- Add Outstanding SO modal--}}
        <!-- @include('procurement.expense-advise.partials.outstanding-so-modal') -->
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
                    <button type="button" data-bs-dismiss="modal" class="btn btn-primary submitAttributeBtn">Select</button>
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
    {{-- Taxes --}}
    @include('procurement.expense-advise.partials.tax-detail-modal')
@endsection
@section('scripts')
    <script type="text/javascript">
        let actionUrlTax = '{{route("expense-adv.tax.calculation")}}';
        var qtyChangeUrl = '{{ route("expense-adv.get.validate-quantity") }}';
        let taxCalUrl = '{{ route('tax.group.calculate') }}';
    </script>
    <script type="text/javascript" src="{{asset('assets/js/modules/common-datatable.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/modules/common-attr-ui.js')}}"></script>
    <script type="text/javascript" src="{{asset('assets/js/modules/expense-advise.js')}}"></script>
    <script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
    <script>
        let currentProcessType = null;
        let tableRowCount = 0;
        window.onload = function () {
            localStorage.removeItem('selectedPoIds');
            localStorage.removeItem('selectedJoIds');
            localStorage.removeItem('selectedSoIds');
            currentProcessType = null;
            // $("#add_new_item_dis").remove();
            // $(".deleteItemDiscountRow").remove();
            // $("#add_new_head_dis").remove();
            // $("#add_new_head_exp").remove();
            // $(".deleteExpRow").remove();
        };
        const selectedCostCenterId = "";
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
            let document_date = $("[name='document_date']").val();
            let actionUrl = '{{route("book.get.doc_no_and_parameters")}}'+'?book_id='+bookId+'&document_date='+document_date;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        $("#book_code").val(data.data.book_code);
                        if(!data.data.doc.document_number) {
                            $("#document_number").val('');
                        }
                        $("#document_number").val(data.data.doc.document_number);
                        if(data.data.doc.type == 'Manually') {
                            $("#document_number").attr('readonly', false);
                        } else {
                            $("#document_number").attr('readonly', true);
                        }
                        const parameters = data.data.parameters;
                        setServiceParameters(parameters);
                        if(parameters?.tax_required.some(val => val.toLowerCase() === 'yes')) {
                            $("#tax_required").val(parameters?.tax_required[0]);
                        } else {
                            $("#tax_required").val("");
                        }
                        implementBookDynamicFields(data.data.dynamic_fields_html, data.data.dynamic_fields);
                    }
                    if(data.status == 404) {
                        $("#book_code").val('');
                        $("#document_number").val('');
                        $("#tax_required").val("");
                        const docDateInput = $("[name='document_date']");
                        docDateInput.removeAttr('min');
                        docDateInput.removeAttr('max');
                        docDateInput.val(new Date().toISOString().split('T')[0]);
                        alert(data.message);
                    }
                });
            });
        }
        /*for trigger on edit cases*/
        setTimeout(() => {
            let bookId = $("#book_id").val();
            getDocNumberByBookId(bookId);
        },0);

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
        /*Set Service Parameter*/
        function setServiceParameters(parameters) {
            /*Date Validation*/
            const docDateInput = $("[name='document_date']");
            let isFeature = false;
            let isPast = false;
            if (parameters.future_date_allowed && parameters.future_date_allowed.includes('yes')) {
                let futureDate = new Date();
                futureDate.setDate(futureDate.getDate() /*+ (parameters.future_date_days || 1)*/);
                docDateInput.val(futureDate.toISOString().split('T')[0]);
                docDateInput.attr("min", new Date().toISOString().split('T')[0]);
                isFeature = true;
            } else {
                isFeature = false;
                docDateInput.attr("max", new Date().toISOString().split('T')[0]);
            }
            if (parameters.back_date_allowed && parameters.back_date_allowed.includes('yes')) {
                let backDate = new Date();
                backDate.setDate(backDate.getDate() /*- (parameters.back_date_days || 1)*/);
                docDateInput.val(backDate.toISOString().split('T')[0]);
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
            let reference_from_service = parameters.reference_from_service;

            if(reference_from_service.length) {
                let po = '{{\App\Helpers\ConstantHelper::PO_SERVICE_ALIAS}}';
                let jo = '{{\App\Helpers\ConstantHelper::JO_SERVICE_ALIAS}}';
                let so = '{{\App\Helpers\ConstantHelper::SO_SERVICE_ALIAS}}';
                if((reference_from_service.includes('po')) || (reference_from_service.includes('jo')) || (reference_from_service.includes('so'))) {
                    $("#reference_from").removeClass('d-none');
                    if (reference_from_service.includes('po'))
                    {
                        $(".poSelect").removeClass('d-none');
                    }
                    if (reference_from_service.includes('jo'))
                    {
                        $(".joSelect").removeClass('d-none');
                    }
                    if (reference_from_service.includes('so'))
                    {
                        $(".soSelect").removeClass('d-none');
                    }
                } else {
                    $("#reference_from").addClass('d-none');
                }
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: "Please update first reference from service param.",
                    icon: 'error',
                });
                setTimeout(() => {
                    location.href = '{{route("expense-adv.index")}}';
                },1500);
            }
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

        function vendorOnChange(vendorId, type=null, typeId=null) {
            let store_id = $("[name='header_store_id']").val() || '';
            let actionUrl = "{{route('expense-adv.get.address')}}"
            +'?id='+vendorId+
            '&store_id='+store_id+
            '&type='+type+
            '&typeId='+typeId;
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
                        // $(".shipping_detail").text('-');
                        $(".billing_detail").text('-');
                        Swal.fire({
                            title: 'Error!',
                            text: data.data?.currency_exchange.message,
                            icon: 'error',
                        });
                        return false;
                    }
                    if(data.data.status == 200) {
                        $("#vendor_name").val(data?.data?.vendor?.company_name);
                        $("#vendor_id").val(data?.data?.vendor?.id);
                        $("#vendor_code").val(data?.data?.vendor.vendor_code);
                        let curOption = `<option value="${data.data.currency.id}">${data.data.currency.name}</option>`;
                        let termOption = `<option value="${data.data.paymentTerm.id}">${data.data.paymentTerm.name}</option>`;
                        $('[name="currency_id"]').empty().append(curOption);
                        $('[name="payment_term_id"]').empty().append(termOption);
                        $("#billing_id").val(data.data.vendor_address.id);
                        $(".billing_detail").text(data.data.vendor_address.display_address);
                        $(".delivery_address").text(data.delivery_address.display_address);
                        $(".org_address").text(data.delivery_address.display_address);

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
                                    type:'service_item_list',
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
                            let closestTr = $input.closest('tr');
                            closestTr.find('[name*=item_id]').val(itemId);
                            closestTr.find('[name*=item_code]').val(itemCode);
                            closestTr.find('[name*=item_name]').val(itemN);
                            closestTr.find('[name*=hsn_id]').val(hsnId);
                            closestTr.find('[name*=hsn_code]').val(hsnCode);
                            closestTr.find("td[id*='itemAttribute_']").html(defautAttrBtn);
                            $input.val(itemCode);
                            let uomOption = `<option value=${uomId}>${uomName}</option>`;
                            if(ui.item?.alternate_u_o_ms) {
                                for(let alterItem of ui.item.alternate_u_o_ms) {
                                uomOption += `<option value="${alterItem.uom_id}" ${alterItem.is_purchasing ? 'selected' : ''}>${alterItem.uom?.name}</option>`;
                                }
                            }
                            closestTr.find('[name*=uom_id]').append(uomOption);
                            closestTr.find('.attributeBtn').trigger('click');
                            setTimeout(() => {
                                if(ui.item.is_attr) {
                                    $input.closest('tr').find('.attributeBtn').trigger('click');
                                } else {
                                    $input.closest('tr').find('.attributeBtn').trigger('click');
                                    $input.closest('tr').find('[name*="[order_qty]"]').val('').focus();
                                }
                            }, 100);
                            getItemDetail(closestTr);
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

        /*Add New Row*/
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
            else{
                initializeAutocomplete2()
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

            let actionUrl = '{{route("expense-adv.item.row")}}'+'?count='+rowsLength+'&component_item='+JSON.stringify(lastTrObj);
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                                // $("#submit-button").click();
                        if (rowsLength) {
                            $("#itemTable > tbody > tr:last").after(data.data.html);
                        } else {
                            $("#itemTable > tbody").html(data.data.html);
                        }
                        $('#vendor_name').prop('readonly',true);
                        $("#editBillingAddressBtn").hide();
                        $("#editShippingAddressBtn").hide();
                        initializeAutocomplete2(".comp_item_code");
                        $(".poSelect").addClass('d-none');
                        $(".joSelect").addClass('d-none');
                        $("#vendor_name").prop('readonly',true);
                        $(".editAddressBtn").addClass('d-none');
                        $(".module_type").val('direct');
                        // focusAndScrollToLastRowInput();
                    } else if(data.status == 422) {
                        $('#vendor_name').prop('readonly',false);
                        $("#editBillingAddressBtn").show();
                        $("#editShippingAddressBtn").show();
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'An unexpected error occurred.',
                            icon: 'error',
                        });
                    } else {
                        $('#vendor_name').prop('readonly',false);
                        $("#editBillingAddressBtn").show();
                        $("#editShippingAddressBtn").show();
                        Swal.fire({
                            title: 'Error!',
                            text: 'Someting went wrong!',
                            icon: 'error',
                        });
                    }
                });
            });
        });

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
                    let poItemHiddenId = $(`#row_${item}`).find("input[name*='[po_item_hidden_ids]']").val();

                    if(poItemHiddenId) {
                        let idsToRemove = poItemHiddenId.split(',');
                        let selectedPoIds = localStorage.getItem('selectedPoIds');
                        if(selectedPoIds) {
                            selectedPoIds = JSON.parse(selectedPoIds);
                            let updatedIds = selectedPoIds.filter(id => !idsToRemove.includes(id));
                            localStorage.setItem('selectedPoIds', JSON.stringify(updatedIds));
                        }
                    }
                    $(`#row_${item}`).remove();
                });
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: "Please first add & select row item.",
                    icon: 'error',
                });
            }

            if(!$("#itemTable .mrntableselectexcel").find("tr[id*='row_']").length) {
                $(".poSelect").removeClass('d-none');
                $(".joSelect").removeClass('d-none');
                $("#referenceNoDiv").hide();
                $("#addNewItemBtn").show();
                $("#itemTable > thead .form-check-input").prop('checked',false);
                $("select[name='currency_id']").prop('disabled', false);
                $("select[name='payment_term_id']").prop('disabled', false);
                $(".editAddressBtn").removeClass('d-none');
                $("#vendor_name").prop('readonly',false);
                $("#reference_type_input").val('');
                getLocation();
            }
            setTableCalculation();
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
                let rowCount = tr.getAttribute('data-index');
                getItemAttribute(item_id, rowCount, selectedAttr, tr);
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: "Please select first item name.",
                    icon: 'error',
                });
            }
        });

        /*For comp attr*/
        function getItemAttribute(itemId, rowCount, selectedAttr, tr){
            if(currentProcessType && currentProcessType != null)
            {
                rowCount = tableRowCount;
            }
            let expense_detail_id = "";
            let actionUrl = '{{route("expense-adv.item.attr")}}'+'?item_id='+itemId+'&expense_detail_id='+expense_detail_id+`&rowCount=${rowCount}&selectedAttr=${selectedAttr}`;
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
                        qtyEnabledDisabled();
                        initAttributeAutocomplete();
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
            let actionUrl = `{{route("expense-adv.edit.address")}}?type=${addressType}&vendor_id=${vendorId}&address_id=${addressId}&onChange=${onChange}`;
            fetch(actionUrl)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 200) {
                        $("#edit-address .modal-dialog").html(data.data.html);
                        $("#address_type").val(addressType);
                        $("#hidden_vendor_id").val(vendorId);
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
            let actionUrl = `{{route("expense-adv.edit.address")}}?type=${addressType}&vendor_id=${vendorId}&address_id=${addressId}&onChange=${onChange}`;
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

            countrySelect.on('change', function () {
                let countryValue = $(this).val();
                let stateSelect = $('#state_id');
                stateSelect.empty().append('<option value="">Select State</option>'); // Reset state dropdown

                if (countryValue) {
                    fetch(`/states/${countryValue}`)
                        .then(response => response.json())
                        .then(data => {
                            data.data.states.forEach(state => {
                                const isSelected = state.value == selectedAddress.state.id;
                                stateSelect.append(new Option(state.label, state.value, isSelected, isSelected));
                            });
                            if (selectedAddress.state.id) {
                                stateSelect.trigger('change');
                            }
                        })
                        .catch(error => console.error('Error fetching states:', error));
                }
            });
            $('#state_id').on('change', function () {
                let stateValue = $(this).val();
                let citySelect = $('#city_id');
                citySelect.empty().append('<option value="">Select City</option>');
                if (stateValue) {
                    fetch(`/cities/${stateValue}`)
                        .then(response => response.json())
                        .then(data => {
                            data.data.cities.forEach(city => {
                                const isSelected = city.value == selectedAddress.city.id;
                                citySelect.append(new Option(city.label, city.value, isSelected, isSelected));
                            });
                        })
                        .catch(error => console.error('Error fetching cities:', error));
                }
            });
            $("#pincode").val(selectedAddress.pincode);
            $("#address").val(selectedAddress.address);
        }

        /*Display item detail*/
        $(document).on('input change focus', '#itemTable tr input ', function(e){
            let currentTr = e.target.closest('tr');
            getItemDetail(currentTr, currentProcessType);
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
                headerId: getVal("[name*='[header_id]']"),
                detailId: getVal("[name*='[detail_id]']"),
                selectedAttr: JSON.stringify(selectedAttr),
                itemStoreData: JSON.parse(getVal("[id*='components_stores_data']") || "[]"),
                type: currentProcessType
            };

            let actionUrl = '{{ route('expense-adv.get.itemdetail') }}?' + new URLSearchParams(data).toString();

            fetch(actionUrl)
                .then(res => res.json())
                .then(data => {
                    if (data.status == 200) {
                        $("#itemDetailDisplay").html(data.data.html);
                    }
                });
        }

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
                // $('html, body').scrollTop($('.trselected').offset().top - 200);
            }
        });

        /* Address Submit */
        $(document).on('click', '.submitAddress', function (e) {
            $('.ajax-validation-error-span').remove();
            e.preventDefault();
            var innerFormData = new FormData();
            $('#edit-address').find('input,textarea,select').each(function () {
                innerFormData.append($(this).attr('name'), $(this).val());
            });
            var method = "POST" ;
            var url = '{{route("expense-adv.address.save")}}';
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
                    } else {
                        $("#billing_id").val(data.data.new_address.id);
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
        /*submit attribute*/
        $(document).on('click', '.submitAttributeBtn', (e) => {
            let rowCount = $("[id*=row_].trselected").attr('data-index');
            $("#attribute").modal('hide');
            $('#attribute').one('hidden.bs.modal', () => {
                const $input = $(`[name="components[${rowCount}][accepted_qty]"]`);
                $input.focus();
                getItemDetail(rowCount);
            });
        });

        /*Open Po model*/
        let poOrderTable;
        $(document).on('click', '.poSelect', (e) => {
            tableRowCount = $('.mrntableselectexcel tr').length;
            $("#poModal").modal('show');
            currentProcessType = 'po';
            openPurchaseRequest();
            const tableSelector = '#poModal .po-order-detail';
            $(tableSelector).DataTable().clear().destroy();
            getPurchaseOrders();
            if ($(tableSelector).length) {
                if ($.fn.DataTable.isDataTable(tableSelector)) {
                    poOrderTable = $(tableSelector).DataTable();
                    poOrderTable.ajax.reload();
                }
                // Re-initialize DataTable
            }
        });

        function getSelectedPoTypes()
        {
            let moduleTypes = [];
            $('.po_item_checkbox:checked').each(function() {
                moduleTypes.push($(this).attr('data-module')); // Corrected: Get attribute value instead of setting it
            });
            return moduleTypes;
        }

        function openPurchaseRequest()
        {
            initializeAutocompleteQt("vendor_code_input_qt", "vendor_id_qt_val", "vendor_list", "vendor_code", "company_name");
            initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "po_document_qt", "document_number", "");
        }

        function initializeAutocompleteQt(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "")
        {
            let modalType = '#poModal';
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
                            store_id : $("#store_id_po").val() || '',
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
                    $("#" + selectorSibling).val("");
                    $('#poModal .po-order-detail').DataTable().ajax.reload();
                    $(this).autocomplete("search", "");
                }
            }).blur(function() {
                if (this.value === "") {
                    $("#" + selectorSibling).val("");
                    $('#poModal .po-order-detail').DataTable().ajax.reload();
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
                asn_number = '',
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
                item_id = $("#item_id_qt_val").val() || '',
                vendor_id = $("#vendor_id_qt_val").val(),
                store_id = $(".header_store_id").val() || '',
                so_id = $("#po_so_qt_val").val() || '',
                item_search = $("#item_name_search").length ? $("#item_name_search").val() : '',
                selected_po_ids = selectedPoIds
                selected_po_ids = encodeURIComponent(selectedPoIds)
            }
            if(currentProcessType === 'jo')
            {
                let selectedJoIds = localStorage.getItem('selectedJoIds') ?? '[]';
                selectedJoIds = JSON.parse(selectedJoIds);
                selectedJoIds = encodeURIComponent(JSON.stringify(selectedJoIds));
                document_date = $("[name ='document_date']").val() || '',
                header_book_id = $("#book_id").val() || '',
                series_id = $("#book_id_qt_val").val() || '',
                document_number = $("#document_id_qt_val").val() || '',
                asn_number = $("#asn_id_qt_val").val() || '',
                item_id = $("#item_id_qt_val").val() || '',
                vendor_id = $("#vendor_id_qt_val").val(),
                store_id = $(".header_store_id").val() || '',
                so_id = $("#po_so_qt_val").val() || '',
                item_search = $("#item_name_search").length ? $("#item_name_search").val() : '',
                selected_jo_ids = selectedJoIds
                selected_po_ids = encodeURIComponent(selectedJoIds)
            }
            return {
                document_date: document_date,
                header_book_id: header_book_id,
                series_id: series_id,
                document_number: document_number,
                asn_number: asn_number,
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
            const ajaxUrl = '{{ route("expense-adv.get.po", ["type" => "create"]) }}';
            var columns = [];
            columns = [
                { data: 'id',visible: false, orderable: true, searchable: false},
                { data: 'select_checkbox', name: 'select_checkbox', orderable: false, searchable: false},
                { data: 'vendor', name: 'vendor', render: renderData, orderable: false, searchable: false},
                { data: 'po_doc', name: 'po_doc', render: renderData, orderable: false, searchable: false },
                { data: 'po_date', name: 'po_date', render: renderData, orderable: false, searchable: false },
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
                { data: 'expense_advise_qty', name: 'expense_advise_qty', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
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
        $(document).on('change','.po-order-detail > thead .form-check-input',(e) => {
            if (e.target.checked) {
                $(".po-order-detail > tbody .form-check-input").each(function(){
                    $(this).prop('checked',true);
                });
            } else {
                $(".po-order-detail > tbody .form-check-input").each(function(){
                    $(this).prop('checked',false);
                });
            }
        });

        function getSelectedPoIDS()
        {
            let ids = [];
            let asnIds = [];
            let asnItemIds = [];
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
            let referenceNo = result.referenceNos[0];
            currentProcessType = 'po';
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

            $("[name='po_item_ids']").val(ids);
            $("#addNewItemBtn").hide();
            if (referenceNo) {
                $("#referenceNoDiv").show();
                // $("#reference_number_input").val(referenceNo);
            } else {
                $("#referenceNoDiv").hide();
                // $("#reference_number_input").val('');
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
                module_type: moduleTypes,
            };
            $("#reference_type_input").val('po');
            asnProcess(processData, 'po-process');
        });


        /*Open Jo model*/
        let joOrderTable;
        $(document).on('click', '.joSelect', (e) => {
            tableRowCount = $('.mrntableselectexcel tr').length;
            $("#joModal").modal('show');
            currentProcessType = 'jo';
            openJobRequest();
            const tableSelector = '#joModal .jo-order-detail';
            $(tableSelector).DataTable().clear().destroy();
            getJobOrders();
            if ($(tableSelector).length) {
                if ($.fn.DataTable.isDataTable(tableSelector)) {
                    joOrderTable = $(tableSelector).DataTable();
                    joOrderTable.ajax.reload();
                }
                // Re-initialize DataTable
            }
        });

        function getSelectedJoTypes()
        {
            let moduleTypes = [];
            $('.jo_item_checkbox:checked').each(function() {
                moduleTypes.push($(this).attr('data-module'));
            });
            return moduleTypes;
        }

        function openJobRequest()
        {
            initializeAutocompleteJoQt("jo_vendor_code_input_qt", "jo_vendor_id_qt_val", "vendor_list", "vendor_code", "company_name");
            initializeAutocompleteJoQt("jo_document_no_input_qt", "jo_document_id_qt_val", "jo_document_qt", "document_number", "");
        }

        function initializeAutocompleteJoQt(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "")
        {
            let modalType = '#joModal';
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
                            store_id : $("#store_id_po").val() || '',
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

        function renderData(data) {
            return data ? data : '';
        }

        function getJobOrders()
        {
            const ajaxUrl = '{{ route("expense-adv.get.jo", ["type" => "create"]) }}';
            var columns = [];
            columns = [
                { data: 'id',visible: false, orderable: true, searchable: false},
                { data: 'select_checkbox', name: 'select_checkbox', orderable: false, searchable: false},
                { data: 'vendor', name: 'vendor', render: renderData, orderable: false, searchable: false},
                { data: 'jo_doc', name: 'jo_doc', render: renderData, orderable: false, searchable: false },
                { data: 'jo_date', name: 'jo_date', render: renderData, orderable: false, searchable: false },
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
                { data: 'expense_advise_qty', name: 'expense_advise_qty', render: renderData, orderable: false, searchable: false, createdCell: function(td, cellData, rowData, row, col) {
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

        $(document).on('keyup', '#item_name_search', (e) => {
            $('#joModal .jo-order-detail').DataTable().ajax.reload();
        });

        /*Checkbox for po/jo item list*/
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
            let referenceNo = result.referenceNos[0];
            currentProcessType = 'jo';
            if (!ids.length) {
                $("#joModal").modal('hide');
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select at least one one jo',
                    icon: 'error',
                });
                return false;
            }

            let moduleTypes = getSelectedJoTypes();

            $("[name='jo_item_ids']").val(ids);
            $("#addNewItemBtn").hide();
            if (referenceNo) {
                $("#referenceNoDiv").show();
                // $("#reference_number_input").val(referenceNo);
            } else {
                $("#referenceNoDiv").hide();
                // $("#reference_number_input").val('');
            }
            $("#reference_type_input").val('jo');

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
                type: 'jo',
                module_type: moduleTypes,
            };
            $("#reference_type_input").val('jo');
            asnProcess(processData, 'jo-process');
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
                                    label: `${item.name}`,
                                    percentage: `${item.percentage}`,
                                    hsn_id: item.hsn_id,
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
                        setTableCalculation();
                    }
                });
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

        $(document).on('click', '.clearPoFilter', (e) => {
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
            $("#po_so_no_input_qt").val('');
            $("#po_so_qt_val").val('');
            $("#item_name_search").val('');
            $('#poModal .po-order-detail').DataTable().ajax.reload();
        });

        $(document).on('click', '.clearJoFilter', (e) => {
            $("#item_name_input_qt").val('');
            $("#item_id_qt_val").val('');
            $("#store_jo").val('');
            $("#store_id_jo").val('');
            $("#sub_store_jo").val('');
            $("#sub_store_id_jo").val('');
            $("#vendor_code_input_qt").val('');
            $("#vendor_id_qt_val").val('');
            $("#book_code_input_qt").val('');
            $("#book_id_qt_val").val('');
            $("#document_no_input_qt").val('');
            $("#document_id_qt_val").val('');
            $("#jo_so_no_input_qt").val('');
            $("#jo_so_qt_val").val('');
            $("#item_name_search").val('');
            $('#joModal .jo-order-detail').DataTable().ajax.reload();
        });

        $(document).on("autocompletechange autocompleteselect", "#store_po", function (event, ui) {
            let storeId = ui?.item?.id || '';
            initializeAutocompleteQt("sub_store_po", "sub_store_id_po", "sub_store", "name", "");
        });

        function asnProcess(asnData, moduleProcess) {
            const current_row_count = $("tbody tr[id*='row_']").length;
            const processType = asnData.type;
            let selectedIds = processType === 'jo'
                ?  localStorage.getItem('selectedJoIds') ?? []
                : localStorage.getItem('selectedPoIds') ?? [];

            // selectedIds = JSON.stringify(selectedIds);

            const ids = JSON.stringify(asnData.ids);
            const moduleTypes = JSON.stringify(asnData.module_type);
            const moduleType = asnData.module_type?.[0] ?? 'po';


            const currencyId = $("[name='currency_id']").val();
            const transactionDate = $("[name='document_date']").val();
            const type = $("meta[name='route-type']").attr("content"); // blade->meta

            const baseRoute = processType === 'jo'
                ? '{{ route("expense-adv.process.jo-item") }}'
                : '{{ route("expense-adv.process.po-item") }}';

            const actionUrl = baseRoute
                .replace(':type', type)
                + '?ids=' + encodeURIComponent(ids)
                + '&moduleTypes=' + moduleTypes
                + '&tableRowCount=' + tableRowCount
                + '&currency_id=' + encodeURIComponent(currencyId)
                + '&d_date=' + encodeURIComponent(transactionDate)
                + '&selected_po_ids=' + encodeURIComponent(selectedIds)
                + '&type=' + 'create'
                + '&current_row_count=' + current_row_count;

            fetch(actionUrl)
                .then(res => res.json())
                .then(data => {
                    if (data.status !== 200) return handleAsnError(data.message);

                    const {
                        vendor,
                        finalDiscounts,
                        finalExpenses,
                        pos,
                        moduleType,
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

                    switch (moduleProcess) {
                        case 'po-process':
                            $(".poSelect").removeClass('d-none');
                            $(".joSelect").addClass('d-none');
                            $("#reference_type_input").val('po');
                            break;
                        case 'jo-process':
                            $(".joSelect").removeClass('d-none');
                            $(".poSelect").addClass('d-none');
                            $("#reference_type_input").val('jo');
                            break;
                        default:
                            $(".poSelect").addClass('d-none');
                            $(".joSelect").addClass('d-none');
                            $("#reference_type_input").val('');
                            break;
                    }

                    // UI Locks
                    $("select[name='currency_id'], select[name='payment_term_id']").prop('disabled', true);
                    $("#vendor_name").prop('readonly', true);
                    $(".editAddressBtn").addClass('d-none');

                    // Supplier details
                    $("[name='supplier_invoice_no'], [name='supplier_invoice_date'], [name='consignment_no'], [name='eway_bill_no'], [name='transporter_name'], [name='vehicle_no']").val('');

                    // Expenses
                    const $expBody = $("#summaryExpTable tbody");
                    $expBody.find('.display_summary_exp_row').remove();

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
                            $("#summaryExpTable #expSummaryFooter").before(rows);
                        } else {
                            $(".display_summary_exp_row:last").after(rows);
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
                    }

                    setTimeout(() => {
                        setTableCalculation();
                        $("#itemTable .mrntableselectexcel tr").each((index, item) => {
                            setAttributesUIHelper(index + 1, "#itemTable");
                        });
                    }, 3000);
                })
                // .catch(() => {
                //     Swal.fire({
                //         title: 'Error!',
                //         text: 'An unexpected error occurred while processing ASN.',
                //         icon: 'error'
                //     });
                // });
        }

        function handleAsnError(message = 'Invalid data') {
            $(".editAddressBtn").removeClass('d-none');
            $("#vendor_name").val('').prop('readonly', false);
            $("#vendor_id, #vendor_code, #hidden_state_id, #hidden_country_id").val('');
            $("select[name='currency_id'], select[name='payment_term_id']").prop('readonly', false).html('<option value="">Select</option>');
            $(".shipping_detail, .billing_detail").text('-');
            $("#reference_from").removeClass('d-none');

            Swal.fire({
                title: 'Error!',
                text: message,
                icon: 'error'
            });
        }
    </script>
@endsection
