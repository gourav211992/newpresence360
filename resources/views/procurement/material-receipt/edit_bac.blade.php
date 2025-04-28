@extends('layouts.app')
@section('content')
<form class="ajax-input-form" method="POST" action="{{ route('material-receipt.store') }}" data-redirect="/material-receipts" enctype="multipart/form-data">
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
                                <h2 class="content-header-title float-start mb-0">Material Receipt</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a>
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
                            <button type="button" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" id="save-draft-button" name="action" value="draft">
                                <i data-feather='save'></i> Save as Draft
                            </button>
                            <button type="button" class="btn btn-primary btn-sm submit-button" id="submit-button" name="action" value="submitted">
                                <i data-feather="check-circle"></i> Submit
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
                                                    <select class="form-select" id="book_id" name="series_id">
                                                        <option value="">Select</option>
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
                                                    <label class="form-label">MRN No <span class="text-danger">*</span></label>  
                                                </div>
                                                <div class="col-md-5"> 
                                                    <input type="text" name="document_number" class="form-control" id="document_number">
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3"> 
                                                    <label class="form-label">MRN Date <span class="text-danger">*</span></label>  
                                                </div>
                                                <div class="col-md-5"> 
                                                    <input type="date" class="form-control" value="{{date('Y-m-d')}}" name="document_date">
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3"> 
                                                    <label class="form-label">Reference No </label>  
                                                </div>
                                                <div class="col-md-5"> 
                                                    <input type="text" name="reference_number" class="form-control">
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
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
                                                    <div class="mb-1">
                                                        <label class="form-label">Vendor <span class="text-danger">*</span></label> 
                                                        <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" id="vendor_name" name="vendor_name" />
                                                        <input type="hidden" id="vendor_id" name="vendor_id" />
                                                        <input type="hidden" id="vendor_code" name="vendor_code" />
                                                        <input type="hidden" id="shipping_id" name="shipping_id" />
                                                        <input type="hidden" id="billing_id" name="billing_id" />
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Currency <span class="text-danger">*</span></label>
                                                        <select class="form-select" name="currency_id">
                                                            {{-- 
                                                            <option value="">Select</option>
                                                            @foreach($currencies as $currency)
                                                            <option value="{{$currency->id}}">{{$currency->name}}</option>
                                                            @endforeach  --}}
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Payment Terms <span class="text-danger">*</span></label>
                                                        <select class="form-select" name="payment_term_id">
                                                            {{-- 
                                                            <option value="">Select</option>
                                                            @foreach($paymentTerms as $paymentTerm)
                                                            <option value="{{$paymentTerm->id}}">{{$paymentTerm->name}}</option>
                                                            @endforeach  --}}
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
                                                                <label class="form-label w-100">
                                                                Select Shipping Address <span class="text-danger">*</span> 
                                                                <a href="javascript:;" class="float-end font-small-2 editAddressBtn" id="editShippingAddressBtn" data-type="shipping">
                                                                <i data-feather='edit-3'></i> Edit
                                                                </a>
                                                                </label>
                                                                <div class="mrnaddedd-prim shipping_detail">-</div>
                                                                <input type="hidden" name="shipping_address" id="shipping_address">   
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
                                                                <a href="javascript:;" class="float-end font-small-2 editAddressBtn" id="editBillingAddressBtn" data-type="billing">
                                                                <i data-feather='edit-3'></i> Edit
                                                                </a>
                                                                </label>
                                                                <div class="mrnaddedd-prim billing_detail">-</div>
                                                                <input type="hidden" name="billing_address" id="billing_address"> 
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
                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">
                                                            Gate Entry No. 
                                                            <!-- <span class="text-danger">*</span> -->
                                                        </label>
                                                        <input type="text" name="gate_entry_no"
                                                            class="form-control bg-white"
                                                            placeholder="Enter Gate Entry no">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">
                                                            Gate Entry Date 
                                                            <!-- <span class="text-danger">*</span> -->
                                                        </label>
                                                        <input type="date" name="gate_entry_date"
                                                            class="form-control bg-white gate-entry" id="datepicker2"
                                                            placeholder="Enter Gate Entry Date">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">
                                                            E-Way Bill No. 
                                                            <!-- <span class="text-danger">*</span> -->
                                                        </label>
                                                        <input type="text" name="eway_bill_no"
                                                            class="form-control bg-white"
                                                            placeholder="Enter Eway Bill No.">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">
                                                            Consignment No. 
                                                            <!-- <span class="text-danger">*</span> -->
                                                        </label>
                                                        <input type="text" name="consignment_no"
                                                            class="form-control bg-white"
                                                            placeholder="Enter Consignment No.">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">
                                                        Supplier Invoice No.
                                                        </label> 
                                                        <input type="text" name="supplier_invoice_no"
                                                            class="form-control bg-white"
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
                                                            class="form-control bg-white gate-entry" id="datepicker3"
                                                            placeholder="Enter Supplier Invoice Date">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">
                                                        Transporter Name
                                                        </label> 
                                                        <input type="text" name="transporter_name"
                                                            class="form-control bg-white"
                                                            placeholder="Enter Transporter Name">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">
                                                            Vehicle No.
                                                            <!-- <span class="text-danger">*</span> -->
                                                        </label>
                                                        <input type="text" name="vehicle_no"
                                                            class="form-control bg-white"
                                                            placeholder="Enter Vehicle No.">
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
                                                    <h4 class="card-title text-theme">MRN Item Wise Detail</h4>
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
                                                            <th width="200px">Item</th>
                                                            <th>Attributes</th>
                                                            <th>UOM</th>
                                                            <th>Recpt Qty</th>
                                                            <th>Acpt. Qty</th>
                                                            <th>Rej. Qty</th>
                                                            <th class="text-end">Rate</th>
                                                            <th class="text-end">Value</th>
                                                            <th>Discount</th>
                                                            <th class="text-end">Total</th>
                                                            <th width="100px">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="mrntableselectexcel">
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="totalsubheadpodetail">
                                                            <td colspan="8"></td>
                                                            <td class="text-end" id="totalItemValue">0.00</td>
                                                            <td class="text-end" id="totalItemDiscount">0.00</td>
                                                            {{-- 
                                                            <td class="text-end" id="TotalEachRowTax">0.00</td>
                                                            --}}
                                                            <td class="text-end" id="TotalEachRowAmount">0.00</td>
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
                                                                            <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between">
                                                                                <strong>MRN Summary</strong>
                                                                                <div class="addmendisexpbtn">
                                                                                    <button type="button" class="btn p-25 btn-sm btn-outline-secondary summaryTaxBtn">{{-- <i data-feather="plus"></i> --}} Tax</button>
                                                                                    <button class="btn p-25 btn-sm btn-outline-secondary summaryDisBtn"><i data-feather="plus"></i> Discount</button>
                                                                                    <button class="btn p-25 btn-sm btn-outline-secondary summaryExpBtn"><i data-feather="plus"></i> Expenses</button>
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
                                                            <input type="file" name="attachment[]" class="form-control" multiple>
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
                            <div></div>
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
    {{-- Add Outstanding PO modal--}}
    @include('procurement.material-receipt.partials.outstanding-po-modal')
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
                {{-- 
                <p class="text-center">Enter the details below.</p>
                --}}
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
                                    <input type="number" name="itemDiscountAmount" class="form-control mw-100" />
                                </td>
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
{{-- Item Locations --}}
@include('procurement.material-receipt.partials.item-location-modal')
{{-- Taxes --}}
@include('procurement.material-receipt.partials.tax-detail-modal')
@endsection
@section('scripts')
<script type="text/javascript" src="{{asset('assets/js/modules/mrn.js')}}"></script>
<script>
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
                let document_date = $("[name='document_date']").val();
                let actionUrl = "{{route('po.get.address')}}"+'?id='+itemId+'&document_date='+document_date;
                fetch(actionUrl).then(response => {
                    return response.json().then(data => {
                        if(data.data?.currency_exchange?.status == false) {
                            $input.val('');
                            $("#vendor_id").val('');
                            $("#vendor_code").val('');
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
                            let curOption = `<option value="${data.data.currency.id}">${data.data.currency.name}</option>`;
                            let termOption = `<option value="${data.data.paymentTerm.id}">${data.data.paymentTerm.name}</option>`;
                            $('[name="currency_id"]').empty().append(curOption);
                            $('[name="payment_term_id"]').empty().append(termOption);
                            $("#shipping_id").val(data.data.shipping.id);
                            $("#billing_id").val(data.data.billing.id);
                            $(".shipping_detail").text(data.data.shipping.display_address);
                            $(".billing_detail").text(data.data.billing.display_address);
                            $("#shipping_address").val(data.data.shipping.display_address);
                            $("#billing_address").val(data.data.billing.display_address);
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
        var supplierName = $('#vendor_name').val();
        if(!supplierName){
            Swal.fire(
                "Warning!",
                "Please select vendor first!",
                "warning"
            );
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
                getItemDetail(closestTr);
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
              $('#vendor_name').prop('readonly',true);
              $("#editBillingAddressBtn").hide();
              $("#editShippingAddressBtn").hide();
              initializeAutocomplete2(".comp_item_code");
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
          $('#vendor_name').prop('readonly',false);
          $("#editBillingAddressBtn").show();
          $("#editShippingAddressBtn").show();
      } else {
          $('#vendor_name').prop('readonly',true);
          $("#editBillingAddressBtn").hide();
          $("#editShippingAddressBtn").hide();
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
      let closestTr = e.target.closest('tr');
      let rowCount = closestTr.querySelector('[name*="row_count"]').value;
      let attrGroupId = e.target.getAttribute('data-attr-group-id');
      $(`[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`).val(e.target.value);

      closestTr = $(`[name="components[${rowCount}][attr_group_id][${attrGroupId}][attr_name]"]`).closest('tr');
      getItemDetail(closestTr);

    });
    
    /*Discount bind on input*/
    $(document).on('change input', "[name='itemDiscountPercentage']", (e) => {
      if(e.target.value) {
          let rowCount = Number($(e.target).closest('tbody').find('#row_count').val());
          $(e.target).closest('tr').find("[name='itemDiscountAmount']").prop('readonly', true);
          let itemValue = Number($("#itemTable #row_"+rowCount).find("[name*='basic_value']").val());
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
            let actionUrl = '{{route("po.item.discount.row")}}'+'?tbl_row_count='+tblRowCount+'&row_count='+rowCount+'&dis_name='+disName+'&dis_perc='+disPerc+'&dis_amount='+disAmount;
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
        $("#eachRowDiscountTable [name='itemDiscountName']").val('');
        $("#eachRowDiscountTable [name='itemDiscountPercentage']").val('');
        $("#eachRowDiscountTable [name='itemDiscountAmount']").val('');
        let rowCount = e.target.closest('button').getAttribute('data-row-count');
        let disRows = '';
        
        let total = 0.00;
        if (!$("#itemTable #row_"+rowCount).find("[name*=discounts]").length) {
            let itemValue = Number($("#itemTable #row_"+rowCount).find("[name*=basic_value]").val());
        
            if(!itemValue) {
                $("#itemRowDiscountModal").find('[name=itemDiscountPercentage]').prop('readonly',true);
            } else {
                $("#itemRowDiscountModal").find('[name=itemDiscountPercentage]').prop('readonly',false);
            }
            $("#itemRowDiscountModal").find('#row_count').val(rowCount);
        } else {
            let itemValue = Number($("#itemTable #row_"+rowCount).find("[name*=basic_value]").val());
        
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
        
        let itemValue = Number($("#itemTable #row_" + rowCount).find("[name*=basic_value]").val());
        
        $("#eachRowDiscountTable .display_discount_row").each(function (index, item) {
            let disName = $(item).find("[name='itemDiscountName']").val();
            let disPerc = $(item).find("[name='itemDiscountPercentage']").val();
            let disAmount = $(item).find("[name='itemDiscountAmount']").val();
            let discountAmount = Number(disAmount);
            total += discountAmount;
            // Check if the discount exceeds the item value
            if (total > itemValue) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Total discount amount cannot exceed the item value of ' + itemValue,
                    icon: 'error',
                });
                // Remove the current discount row if it causes the total to exceed
                $(item).remove();
                total -= discountAmount; // Revert the total back
                return false; // Stop the loop if the limit is exceeded
            }
            hiddenHtml += `<input type="hidden" value="${disName}" name="components[${rowCount}][discounts][${index + 1}][dis_name]"/>
                            <input type="hidden" value="${disPerc}" name="components[${rowCount}][discounts][${index + 1}][dis_perc]" />
                            <input type="hidden" value="${disAmount}" name="components[${rowCount}][discounts][${index + 1}][dis_amount]" />`;
        });
        
        // Remove any previously added discount inputs
        $("#itemTable #row_" + rowCount).find("[name*='dis_name']").remove();
        $("#itemTable #row_" + rowCount).find("[name*='dis_perc']").remove();
        $("#itemTable #row_" + rowCount).find("[name*='dis_amount']").remove();
        
        // Add the new discount details
        $("#itemTable #row_" + rowCount).find("[name*='[discount_amount]']").after(hiddenHtml);
        // Update the discount total
        $("#itemTable #row_" + rowCount).find("[name*='[discount_amount]']").val(total);
        
        // Close the modal and recalculate table
        $("#itemRowDiscountModal").modal('hide');
        setTableCalculation();
    });
    
    
    /*Delete deleteItemDiscountRow*/
    $(document).on('click', '.deleteItemDiscountRow', (e) => {
      let rowCount = e.target.closest('a').getAttribute('data-row-count') || 0;
      let index = e.target.closest('a').getAttribute('data-index') || 0;
      let total = 0.00;
      e.target.closest('tr').remove();
      $("#eachRowDiscountTable .display_discount_row").each(function(index,item){
          let disAmount = $(item).find("[name='itemDiscountAmount']").val();
          total += Number(disAmount);
      });
      $("#disItemFooter #total").text(total.toFixed(2));
    });
    
    /*Order qty on change*/
    $(document).on('change',"[name*='order_qty']",(e) => {
        let tr = e.target.closest('tr');
        let qty = e.target;
        console.log('qty', qty.value);
        let dataIndex = $(e.target).closest('tr').attr('data-index');
        let orderQuantity = $(e.target).closest('tr').find("[name*='order_qty']");
        let acceptedQuantity = $(e.target).closest('tr').find("[name*='accepted_qty']");
        let rejectedQuantity = $(e.target).closest('tr').find("[name*='rejected_qty']");
        let orderQty = parseFloat(qty.value);
        orderQuantity.val(orderQty.toFixed(2));
        acceptedQuantity.val(orderQty.toFixed(2));
        rejectedQuantity.val('0.00');
    });
    
    /*qty on change*/
    $(document).on('change',"[name*='accepted_qty']",(e) => {
        let tr = e.target.closest('tr');
        let qty = e.target;
        let dataIndex = $(e.target).closest('tr').attr('data-index');
        let itemId = $(e.target).closest('tr').find('[name*=item_id]').val();
        let acceptedQuantity = $(e.target).closest('tr').find("[name*='accepted_qty']");
        let receiptQuantity = $(e.target).closest('tr').find("[name*='order_qty']");
        let rejectedQuantity = $(e.target).closest('tr').find("[name*='rejected_qty']");
        let itemCost = $(e.target).closest('tr').find("[name*='rate']");
        // let superceededCost = $(e.target).closest('tr').find("[name*='superceeded_cost']"); 
        let itemValue = $(e.target).closest('tr').find("[name*='basic_value']");
        if(Number(acceptedQuantity.val()) > Number(receiptQuantity.val())) {
            acceptedQuantity.val(receiptQuantity.val());
            Swal.fire({
                title: 'Error!',
                text: 'Accepted Quantity can not be greater than receipt quantity.',
                icon: 'error',
            });
            return false;
        } else{
            let aq = parseFloat(acceptedQuantity.val());
            let rq = parseFloat(receiptQuantity.val()) - parseFloat(acceptedQuantity.val());
            acceptedQuantity.val(aq.toFixed(2));
            rejectedQuantity.val(rq.toFixed(2));
        
            if (Number(itemCost.val())) {
                let totalItemValue = parseFloat(acceptedQuantity.val()) * parseFloat(itemCost.val());
                itemValue.val(totalItemValue.toFixed(2));
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
        let orderRate = $(e.target).closest('tr').find("[name*='rate']");
        let itemValue = $(e.target).closest('tr').find("[name*='basic_value']");
        if (Number(acceptedQuantity.val())) {
            let itemRate = parseFloat(rate.value);
            let totalItemValue = (itemRate) * (parseFloat(acceptedQuantity.val()));
            console.log('totalItemValue', totalItemValue);
            totalItemValue = parseFloat(totalItemValue);
            orderRate.val(itemRate.toFixed(2));
            itemValue.val(totalItemValue.toFixed(2));
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
    $(document).on('input change', '#summaryDiscountModal [name*="[d_perc]"]', (e) => {
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
    $(document).on('input change', '#summaryDiscountModal [name*="[d_amnt]"]', (e) => {
        if(e.target.value) {
            $(e.target).closest('tr').find("[name*='[d_perc]']").prop('readonly', true);
        } else {
            $(e.target).closest('tr').find("[name*='[d_perc]']").prop('readonly', false);
        }
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
                headerDis = parseFloat(headerDis).toFixed(2);
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
                    <td><input type="number" name="exp_summary[${rowCount}][e_perc]" class="form-control mw-100 exp_row_percetage" /></td>
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
        setExpenseHeader();
    });
    
    /*on change exp summary*/
    $(document).on('input change', '#summaryExpenModal [name*="e_perc"]', (e) => {
        console.log('here perc chang', $(e.target).val())
        
        let perc = Number(e.target.value);
        if(perc > 100) {
            $(e.target).val(100);
            perc = 100;
        }
        
        var amount = Number($("#f_total_after_tax").attr('amount'));
        
        var disAmount = amount * Number(perc) / 100;  
        $(e.target).closest('#summaryExpenModal').find("[name*='[e_amnt]']").prop('readonly', true).val(disAmount);
        
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

    function setExpenseHeader()
    {

        let f_total_after_tax = Number($("#f_total_after_tax").attr('amount'));
        let total_exp = Number($("#expSummaryFooter #total").attr('amount'));
        if(f_total_after_tax && total_exp) {
            $("#itemTable [id*='row_']").each(function (index, item) {
                let itemValue = Number($(item).find("[name*='[basic_value]']").val());
                let itemDisc = Number($(item).find("[name*='[discount_amount]']").val());
                let itemHeadDisc = Number($(item).find("[name*='[discount_amount_header]']").val());

                let totalTax = 0;
                $(item).find("[name*='[t_value]']").each(function(indexTax,itemTax) {
                    totalTax = totalTax + Number($(itemTax).val());
                })
                let totalAfterTax = itemValue - itemDisc - itemHeadDisc + totalTax;

                let headerDis = totalAfterTax/f_total_after_tax*total_exp;
                headerDis = parseFloat(headerDis).toFixed(2);
                if(headerDis) {
                    $(item).find("[name*='[exp_amount_header]']").val(headerDis);
                }
            });
            setTableCalculation();
        }
    }
    
    function setTableCalculation() {
        let totalItemValue = 0.00;
        let totalItemDis = 0.00;
        let totalItemCost = 0.00;
        let totalTax = 0.00;
        let totalTaxableVal = 0.00;
        let totalAfterTax = 0.00;
        let totalExp = 0.00;
        let totalHeadDiscAmt = 0.00;
        let totalHeadExpAmt = 0.00;
        let rowCount = 0;

        $("#itemTable [id*='row_']").each(function (index, item) {
            rowCount = Number($(item).attr('data-index'));
            let qtyRow = $(item).find("[name*='[accepted_qty]']").val() || 0;
            let rateRow = $(item).find("[name*='[rate]']").val() || 0;
            let itemValueAmountRow = (Number(qtyRow) * Number(rateRow)) || 0;

            /*Bind Item Discount*/
            let disItemAmountRow = 0;
            if($(item).find("[name*='[dis_perc]']").length && itemValueAmountRow) {
                $(item).find("[name*='[dis_perc]']").each(function(index,eachItem) {
                    let eachDiscTypePrice = 0;
                    let discPerc = Number($(eachItem).val());
                    if(discPerc) {
                        eachDiscTypePrice = (itemValueAmountRow * discPerc) / 100; 
                        disItemAmountRow = disItemAmountRow + eachDiscTypePrice;
                        $(`[name="components[${rowCount}][discounts][${index+1}][dis_amount]"]`).val(eachDiscTypePrice);
                    } else {
                        eachDiscTypePrice = $(`[name="components[${rowCount}][discounts][${index+1}][dis_amount]"]`).val(); 
                        disItemAmountRow = disItemAmountRow + Number(eachDiscTypePrice);
                    }
                });
                $(item).find("[name*='[discount_amount]']").val((disItemAmountRow).toFixed(2));
            }
            if(!itemValueAmountRow) {
                $(item).find("[name*='[discount_amount]']").val((0).toFixed(2));
            }

            totalItemValue = totalItemValue + itemValueAmountRow;
            $(item).find("[name*='[item_value]']").val(itemValueAmountRow.toFixed(2));
            let disAmountRow = $(item).find("[name*='[discount_amount]']").val() || 0;

            let headDiscAmt = $(item).find("[name*='[discount_amount_header]']").val() || 0;

            /*Bind Disc Header*/
            let f1 = Number($("#totalItemValue").attr('amount'))-Number($("#totalItemDiscount").attr('amount'));
            let g9 = Number($("#disSummaryFooter #total").attr('amount'));
            if(f1 && g9) {
                let _headerDis = (itemValueAmountRow-disAmountRow) / f1 * g9;
                if(_headerDis) {
                    $(item).find("[name*='[discount_amount_header]']").val(_headerDis);
                }
                if(!itemValueAmountRow) {
                    $(item).find("[name*='[discount_amount_header]']").val(0.00);
                }
            }

            let headExpAmt = $(item).find("[name*='[exp_amount_header]']").val() || 0;
            totalHeadExpAmt = totalHeadExpAmt + Number(headExpAmt);
            totalHeadDiscAmt = totalHeadDiscAmt + Number(headDiscAmt);
            totalItemDis = totalItemDis + Number(disAmountRow);
            let itemTotalCostRow = itemValueAmountRow - Number(disAmountRow);
            totalItemCost = totalItemCost + itemTotalCostRow;
            $(item).find("[name*='[item_total_cost]']").val(itemTotalCostRow);

            if($(item).find("[name*='[t_perc]']").length && itemTotalCostRow) {
                let taxAmountRow = 0.00;
                $(item).find("[name*='[t_perc]']").each(function(index,eachItem) {
                    let eachTaxTypePrice = 0;
                    let taxPercTax = Number($(eachItem).val());
                    if(headDiscAmt) {
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
        $("#totalItemDiscount").attr('amount',totalItemDis);
        $("#totalItemDiscount").text(totalItemDis.toFixed(2));
        $("#TotalEachRowAmount").attr('amount',totalItemCost);
        $("#TotalEachRowAmount").text(totalItemCost.toFixed(2));

        /*Bind Item Discount*/
        let disHeaderAmountRow = 0;
        let totalEachRowAmountAfterDis = totalItemCost; 
        if($(".display_summary_discount_row").find("[name*='[d_perc]']").length && totalEachRowAmountAfterDis) {
            $(".display_summary_discount_row").find("[name*='[d_perc]']").each(function(index,eachItem) {
                let eachDiscTypePrice = 0;
                let itemDiscPerc = Number($(eachItem).val());
                if(itemDiscPerc) {
                    eachDiscTypePrice = (totalEachRowAmountAfterDis * itemDiscPerc) / 100; 
                    disHeaderAmountRow = disHeaderAmountRow + eachDiscTypePrice;
                    $(`[name="disc_summary[${index+1}][d_amnt]"]`).val(eachDiscTypePrice.toFixed(2));
                } else {
                    eachDiscTypePrice = $(`[name="disc_summary[${index+1}][d_amnt]"]`).val() || 0; 
                    disHeaderAmountRow = disHeaderAmountRow + Number(eachDiscTypePrice);
                }
            });
            $("#disSummaryFooter #total").attr('amount',disHeaderAmountRow.toFixed(2));
            $("#disSummaryFooter #total").text(disHeaderAmountRow.toFixed(2));
            $("#f_header_discount").text(disHeaderAmountRow.toFixed(2));
        }

        $("#f_sub_total").text(totalItemValue.toFixed(2));
        $("#f_total_discount").text(totalItemDis.toFixed(2));
        $("#f_taxable_value").attr('amount',(totalItemValue-totalItemDis));
        $("#f_taxable_value").text((totalItemValue-totalItemDis-totalHeadDiscAmt).toFixed(2));
        $("#f_tax").text(totalTax.toFixed(2));

        /*Bind header Exp*/
        let expHeaderAmountRow = 0;
        let totalAmountAfterTax = Number(totalAfterTax - totalHeadDiscAmt); 
        if($(".display_summary_exp_row").find("[name*='[e_perc]']").length && totalEachRowAmountAfterDis) {
            $(".display_summary_exp_row").find("[name*='[e_perc]']").each(function(index,eachItem) {
                let eachExpTypePrice = 0;
                let expDiscPerc = Number($(eachItem).val());
                if(expDiscPerc) {
                    eachExpTypePrice = (totalAmountAfterTax * expDiscPerc) / 100; 
                    expHeaderAmountRow = expHeaderAmountRow + eachExpTypePrice;
                    $(`[name="exp_summary[${index+1}][e_amnt]"]`).val(eachExpTypePrice.toFixed(2));
                } else {
                    eachExpTypePrice = $(`[name="exp_summary[${index+1}][e_amnt]"]`).val() || 0; 
                    expHeaderAmountRow = Number(expHeaderAmountRow) + Number(eachExpTypePrice);
                }
            });
            $("#expSummaryFooter #total").attr('amount',expHeaderAmountRow.toFixed(2));
            $("#expSummaryFooter #total").text(expHeaderAmountRow.toFixed(2));
            $("#f_exp").text(expHeaderAmountRow.toFixed(2));
        }


        $("#f_total_after_tax").attr('amount',(totalAfterTax - totalHeadDiscAmt));
        $("#f_total_after_tax").text((totalAfterTax - totalHeadDiscAmt).toFixed(2));
        totalExp = Number($("#expSummaryFooter #total").attr('amount'));
        $("#f_exp").attr('amount',totalExp);
        $("#f_exp").text(totalExp.toFixed(2));
        $("#f_total_after_exp").text(((totalAfterTax -totalHeadDiscAmt) + totalExp).toFixed(2));
        // $("#f_exp").text(totalHeadExpAmt.toFixed(2));
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

        console.log('itemitemId', itemId);
        if (itemId) {
            let selectedAttr = [];
            $(currentTr).find("[name*='attr_name']").each(function(index, item) {
                if($(item).val()) {
                    selectedAttr.push($(item).val());
                }
            });
            let uomId = $(currentTr).find("[name*='[uom_id]']").val() || '';
            let qty = $(currentTr).find("[name*='[accepted_qty]']").val() || '';
            let headerId = $(currentTr).find("[name*='mrn_header_id']").val() ?? '';
            let detailId = $(currentTr).find("[name*='mrn_detail_id']").val() ?? '';
            let itemStoreData = JSON.parse($(currentTr).find("[id*='components_stores_data']").val() || "[]");
            let actionUrl = '{{route("material-receipt.get.itemdetail")}}'+'?item_id='+itemId+'&purchase_order_id='+poHeaderId+'&po_detail_id='+poDetailId+'&selectedAttr='+JSON.stringify(selectedAttr)+'&itemStoreData='+JSON.stringify(itemStoreData)+'&remark='+remark+'&uom_id='+uomId+'&qty='+qty+'&headerId='+headerId+'&detailId='+detailId;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if(data.status == 200) {
                        // let itemStoreData = JSON.parse($(currentTr).find("[id*='components_stores_data']").val() || "[]");
                        // console.log('itemStoreData....', itemStoreData);
                        // ledgerStock(currentTr, itemId, selectedAttr, itemStoreData);
                        $("#itemDetailDisplay").html(data.data.html);
                    }
                });
            });
        }
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
    
    $('#po_items').change(function() {
        var val1 = $(this).val();
        
        if (val1) {
            var data1 = {
                purchase_order_id: val1
            }
        
            $.ajax({
                type: 'POST',
                data: data1,
                url: '/material-receipts/get-po-items-by-po-id',
                success: function(response) {
                    if (response.success) {
                        html = '';
        
                        $.each(response.response.data, (key, val) => {
                            var bal_qty = (val?.order_qty) - (val?.grn_qty);
                            bal_qty = parseFloat(bal_qty);
                            if(bal_qty > 0){
                                html += '<tr>';
                                html += '    <td>';
                                html +=
                                    '        <div class="form-check form-check-inline me-0">';
                                html += '<input class="form-check-input po_detail_items" type="checkbox" name="po_detail_item_' +
                                    key + '" value="' + val.id + '" data-item=' + "'" + JSON
                                    .stringify(val) + "'" + '>';
                                html += '        </div>';
                                html += '    </td>';
                                html += '    <td>' + (val?.po?.document_number ?? 'N/A') +
                                    '</td>';
                                html += '    <td>' + (val?.po?.document_date ?? 'N/A') +
                                    '</td>';
                                html += '    <td>' + (val?.item?.item_name + '(' + val
                                        ?.item?.item_code + ')' ?? 'N/A') +
                                    '</td>';
                                html += '    <td>' + val?.remarks ?? 'N/A' +
                                    '</td>';
                                html += '    <td>' + val?.order_qty + '</td>';
                                html += '    <td>' + (bal_qty.toFixed(2)) + '</td>';
                                html += '</tr>';
                            }
                        });
                        $('#po-modal-table-body').html(html);
        
                    }
                }
            });
        } else {
            $('#modal-table-body').html('');
        
        }
    });
    
    $('#vendor-select').change(function() {
        var val = $(this).val();
        if (val) {
            var data = {
                vendor_id: val
            }
            $.ajax({
                type: 'POST',
                data: data,
                url: '/material-receipts/get-items-by-vendor',
                success: function(response) {
                    console.log('response.........', response.response.data);
                    if (response.success) {
                        html = '';
                        html1 = '';
                        html1 += '<option value="">-Select PO-</option>';
                        html = '<div id ="notSelect"></div>';
                        let selectedPoItemIds = [];
                        $.each(response.response.data, (key, val) => {
                            var bal_qty = (val?.order_qty) - (val?.grn_qty);
                            bal_qty = parseFloat(bal_qty);
                            if(bal_qty > 0){
                                selectedPoItemIds.push(val.id);
                                html += '<tr>';
                                html += '    <td>';
                                html +=
                                    '        <div class="form-check form-check-inline me-0">';
                                html += '<input class="form-check-input po_detail_items" type="checkbox" name="po_detail_item_' +
                                    key + '" value="' + val.id + '" data-item=' + "'" + JSON
                                    .stringify(val) + "'" + '>';
                                html += '        </div>';
                                html += '    </td>';
                                html += '    <td>' + (val?.po?.document_number ?? 'N/A') +
                                    '</td>';
                                html += '    <td>' + (val?.po?.document_date ?? 'N/A') +
                                    '</td>';
                                html += '    <td>' + (val?.item?.item_name + '(' + val
                                        ?.item?.item_code + ')' ?? 'N/A') +
                                    '</td>';
                                html += '    <td>' + val?.remarks ?? 'N/A' +
                                    '</td>';
                                html += '    <td>' + val?.order_qty + '</td>';
                                html += '    <td>' + (bal_qty.toFixed(2)) + '</td>';
                                html += '</tr>';
                            }
                            html1 += '<option value="' + val?.po?.id + '">' + val
                                ?.po?.document_number + '</option>';
                                
                        });
                        $('#po-item-ids').val(selectedPoItemIds);
                        $('#po-modal-table-body').html(html);
                        $('#po_items').html(html1);
                    }
                }
            });
        } else {
            $('#po-modal-table-body').html('');
        
        }
    });
    
    $('#po-items-select-all').change(function() {
        if ($(this).is(":checked")) {
            $('.po_detail_items').prop('checked', true);
        } else {
            $('.po_detail_items').prop('checked', false);
        }
    })
    
    $(document).on('change', '.po_detail_items', function() {
        var totalItems = $('.po_detail_items').length;
        var checkedItems = $('.po_detail_items:checked').length;
        
        if (totalItems == checkedItems) {
            $('#po-items-select-all').prop('checked', true);
        } else {
            $('#po-items-select-all').prop('checked', false);
        }
    });
    
    $(document).on('click', "#process-btn", function(e) {
        e.preventDefault();
        var html = '';
        
        var checkVal = $('.po_detail_items:checked').val();
        console.log('check val', checkVal);
        var vendorId = $('#vendor-select').val();
        if(!vendorId){
            $('#notSelect').html('');
            $('#vendorNotSelect').html('<span style="color:red">Please select vendor first</span>');
            return true;
        } else {
            $('#vendorNotSelect').html('');
            $('#notSelect').html('');
            $('#vendorNotSelect').hide();
        }
        
        if (checkVal == undefined) {
            $('#vendorNotSelect').html('');
            $('#notSelect').html('<span style="color:red">Please select any PO</span>');
            return true;
        } else {
            $('#vendorNotSelect').html('');
            $('#notSelect').html('');
            $('#notSelect').hide();
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
        var selectedPoItemIds = $('#po-item-ids').val();
        var selectedPoItemIds = $('.po_detail_items:checked').map(function() {
            return $(this).val();
        }).get();
        
        var vendorId = $('#vendor-select').val();
        let actionUrl = '{{route("material-receipt.po-item.row")}}'+'?item_ids='+selectedPoItemIds+'&vendor_id='+vendorId; 
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                    // console.log('data.data.totalItemBasicValue', data);
                        // $("#submit-button").click();
                    $("#itemTable > tbody").html(data.data.html);
                    $("[name*='component_item_name[1]']").trigger('focus');
                    $("[name*='component_item_name[1]']").trigger('blur');
                    $('#rescdule').modal('hide');
                    $('#so_rescdule').modal('hide');
                    $('.po_detail_items').prop('checked', false);
                    $('#po-items-select-all').prop('checked', false);
                    
                    $('#vendor_id').val(data.data.vendor?.id);
                    $('#vendor_name').val(data.data.vendor?.company_name);
                    $('#vendor_code').val(data.data.vendor?.vendor_code);
                    let curOption = `<option value="${data.data.currency.id}">${data.data.currency.name}</option>`;
                    let termOption = `<option value="${data.data.paymentTerm.id}">${data.data.paymentTerm.name}</option>`;
                    $('[name="currency_id"]').empty().append(curOption);
                    $('[name="payment_term_id"]').empty().append(termOption);
                    $("#shipping_id").val(data.data.shipping.id);
                    $("#billing_id").val(data.data.billing.id);
                    $(".shipping_detail").text(data.data.shipping.display_address);
                    $(".billing_detail").text(data.data.billing.display_address);
                    $("#shipping_address").val(data.data.shipping.display_address);
                    $("#billing_address").val(data.data.billing.display_address);
                    // console.log($("#shipping_address"), "shipping address");
                    $('#vendor_name').prop('readonly',true);
                    $("#editBillingAddressBtn").hide();
                    $("#editShippingAddressBtn").hide();
        
                    setTableCalculation();
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
                    console.log("Someting went wrong!");
                }
            });
        });
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
        console.log(rowCount);
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
        console.log('rowKey', rowKey);
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
                $('#erp_rack_id_'+rowKey).append('<option value="">Select</option>');
                $.each(data.storeRacks, function(key, value) {
                    $('#erp_rack_id_'+rowKey).append('<option value="'+ key +'">'+ value +'</option>');
                });
                $('#erp_rack_id_'+rowKey).trigger('change');
                
                $('#erp_bin_id_'+rowKey).empty();
                $('#erp_bin_id_'+rowKey).append('<option value="">Select</option>');
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
                $('#erp_shelf_id_'+rowKey).append('<option value="">Select</option>');
                $.each(data.storeShelfs, function(key, value) {
                    $('#erp_shelf_id_'+rowKey).append('<option value="'+ key +'">'+ value +'</option>');
                });

                $('#erp_shelf_id_'+rowKey).trigger('change');
            }
        });
    });

    // $(document).on('click', '.amendmentBtn', (e) => {
        //     $("#amendmentconfirm").modal('show');
        // });

        // $(document).on('click', '#amendmentSubmit', (e) => {
        //     let actionUrl = "{{ route('material-receipt.amendment.submit', $mrn->id) }}";
        //     fetch(actionUrl).then(response => {
        //         return response.json().then(data => {
        //             if (data.status == 200) {
        //                 Swal.fire({
        //                     title: 'Success!',
        //                     text: data.message,
        //                     icon: 'success'
        //                 });
        //             } else {
        //                 Swal.fire({
        //                     title: 'Error!',
        //                     text: data.message,
        //                     icon: 'error'
        //                 });
        //             }
        //             location.reload();
        //         });
        //     });
        // });
</script>
@endsection
