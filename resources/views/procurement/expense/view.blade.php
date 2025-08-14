@extends('layouts.app')
@section('content')
    <form class="ajax-input-form" method="POST" action="{{ route('expense.update', $mrn->id) }}" data-redirect="/expenses" enctype="multipart/form-data">
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
                                    <h2 class="content-header-title float-start mb-0">Expense</h2>
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
                                                        <input type="hidden" name="series_id" class="form-control" id="series_id" value="{{$mrn->series_id}}" readonly> 
                                                        <input readonly type="text" class="form-control" value="{{$mrn->book->book_code}}" id="book_code">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3"> 
                                                        <label class="form-label">Expense No <span class="text-danger">*</span></label>  
                                                    </div>
                                                    <div class="col-md-5"> 
                                                        <input type="text" class="form-control" readonly value="{{@$mrn->document_number}}" id="document_number">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3"> 
                                                        <label class="form-label">Expense Date <span class="text-danger">*</span></label>  
                                                    </div>
                                                    <div class="col-md-5"> 
                                                        <input type="date" class="form-control" value="{{date('Y-m-d')}}" >
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
                                                            <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" id="vendor_name" readonly name="vendor_name" {{(count(($mrn->items)) > 0 ? 'readonly' : '')}} value="{{@$mrn->vendor->company_name}}" />
                                                            <input type="hidden" value="{{@$mrn->vendor_id}}" id="vendor_id" name="vendor_id" />
                                                            <input type="hidden" value="{{@$mrn->vendor_code}}" id="vendor_code" name="vendor_code" />
                                                            <input type="hidden" value="{{@$mrn->ship_to}}" id="shipping_id" name="shipping_id" />
                                                            <input type="hidden" id="billing_id" value="{{@$mrn->billing_to}}" name="billing_id" />
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
                                                    <div class="col-md-2">
                                                        <div class="mb-1">
                                                            <label class="form-label">Payment Terms <span class="text-danger">*</span></label>
                                                            <select class="form-select" name="payment_term_id">
                                                                <option value="{{@$mrn->payment_term_id}}">{{@$mrn->paymentTerm->name}}</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                            Supplier Invoice No.
                                                            </label> 
                                                            <input type="text" name="supplier_invoice_no" value="{{@$mrn->supplier_invoice_no}}"
                                                                class="form-control bg-white"
                                                                placeholder="Enter Supplier Invoice No.">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Supplier Invoice Date 
                                                                <!-- <span class="text-danger">*</span> -->
                                                            </label>
                                                            <input type="date" name="supplier_invoice_date" value="{{date('Y-m-d', strtotime($mrn->supplier_invoice_date))}}"
                                                                class="form-control bg-white gate-entry" id="datepicker3"
                                                                placeholder="Enter Supplier Invoice Date">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="customer-billing-section">
                                                            <p>Shipping Details</p>
                                                            <div class="bilnbody">
                                                                <div class="genertedvariables genertedvariablesnone">
                                                                    <label class="form-label w-100" style="display: {{ count($mrn->items) > 0 ? 'none' : 'block' }};">
                                                                    Select Shipping Address <span class="text-danger">*</span> 
                                                                    <a href="javascript:;" class="float-end font-small-2 editAddressBtn" data-type="shipping">
                                                                    <i data-feather='edit-3'></i> Edit
                                                                    </a>
                                                                    </label>
                                                                    <div class="mrnaddedd-prim shipping_detail">
                                                                        {{@$mrn->shipping_address}}
                                                                    </div>
                                                                    <input type="hidden" name="shipping_address" id="shipping_address" value="{{@$mrn->shipping_address}}">   
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="customer-billing-section h-100">
                                                            <p>Billing Details</p>
                                                            <div class="bilnbody">
                                                                <div class="genertedvariables genertedvariablesnone">
                                                                    <label class="form-label w-100" style="display: {{ count($mrn->items) > 0 ? 'none' : 'block' }};">
                                                                    Select Billing Address <span class="text-danger">*</span> 
                                                                    <a href="javascript:;" class="float-end font-small-2 editAddressBtn" data-type="billing">
                                                                    <i data-feather='edit-3'></i> Edit
                                                                    </a>
                                                                    </label>
                                                                    <div class="mrnaddedd-prim billing_detail">
                                                                        {{@$mrn->billing_address}}
                                                                    </div>
                                                                    <input type="hidden" name="billing_address" id="billing_address" value="{{@$mrn->billing_address}}">   
                                                                </div>
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
                                                        <h4 class="card-title text-theme">Expense Item Wise Detail</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                @if($mrn->document_status == 'draft')
                                                    <div class="col-md-6 text-sm-end">
                                                        <a href="javascript:;" id="deleteBtn" class="btn btn-sm btn-outline-danger me-50">
                                                        <i data-feather="x-circle"></i> Delete</a>
                                                        <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary">
                                                        <i data-feather="plus"></i> Add New Item</a>
                                                    </div>
                                                @endif
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
                                                                <th>Qty</th>                                                          
                                                                <th class="text-end">Rate</th>
                                                                <th class="text-end">Value</th>
                                                                <th>Discount</th>
                                                                <th class="text-end">Total</th>
                                                                <th>Cost Center</th>
                                                                <th width="100px">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                            @include('procurement.expense.partials.item-row-edit')
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="totalsubheadpodetail">
                                                                <td colspan="6"></td>
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
                                                                <td colspan="8" rowspan="12">
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
                                                                <td colspan="3">
                                                                    <table class="table border mrnsummarynewsty">
                                                                        <tr>
                                                                            <td colspan="2" class="p-0">
                                                                                <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between">
                                                                                    <strong>Expense Summary</strong>
                                                                                    <div class="addmendisexpbtn">
                                                                                        <button type="button" class="btn p-25 btn-sm btn-outline-secondary summaryTaxBtn" >Tax</button>
                                                                                        <button class="btn p-25 btn-sm btn-outline-secondary summaryDisBtn"><i data-feather="plus"></i> Discount</button>
                                                                                        <button class="btn p-25 btn-sm btn-outline-secondary summaryExpBtn"><i data-feather="plus"></i> Expenses</button>
                                                                                    </div>
                                                                                </h6>
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="totalsubheadpodetail">
                                                                            <td width="55%"><strong>Sub Total</strong></td>
                                                                            <td class="text-end" id="f_sub_total">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Item Discount</strong></td>
                                                                            <td class="text-end" id="f_total_discount">
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
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Tax</strong></td>
                                                                            <td class="text-end" id="f_tax">
                                                                            </td>
                                                                        </tr>
                                                                        <tr class="totalsubheadpodetail">
                                                                            <td><strong>Total After Tax</strong></td>
                                                                            <td class="text-end" id="f_total_after_tax">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td><strong>Exp.</strong></td>
                                                                            <td class="text-end" id="f_exp">
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
                                                                <input type="file" name="attachment[]" class="form-control" multiple>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="mb-1">  
                                                            <label class="form-label">Final Remarks</label> 
                                                            <textarea type="text" rows="4" name="remarks" class="form-control" placeholder="Enter Remarks here...">{!! $mrn->final_remark !!}</textarea> 
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        {{-- Discount summary modal --}}
        @include('procurement.expense.partials.summary-disc-modal')
        {{-- Add expenses modal--}}
        @include('procurement.expense.partials.summary-exp-modal')
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
                    <div class="text-end"><a href="javascript:;" class="text-primary add-contactpeontxt mt-50 addDiscountItemRow">
                        <i data-feather='plus'></i> Add Discount</a>
                    </div>
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
    @include('procurement.expense.partials.approve-modal', ['id' => $mrn->id])

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
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>Expense</strong>? After Amendment this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
                </div> 
            </div>
        </div>
    </div>

    {{-- Taxes --}}
    @include('procurement.expense.partials.tax-detail-modal')
@endsection
@section('scripts')
    <script type="text/javascript" src="{{asset('assets/js/modules/expense.js')}}"></script>
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

        // Change BookId     
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

        // Get doc number by BookId
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
            var supplierName = $('#vendor_name').val();
            if(!supplierName){
                Swal.fire(
                    "Warning!",
                    "Please select vendor first!",
                    "warning"
                );
                return false;
            } else {
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
            
            let actionUrl = '{{route("expense.item.row")}}'+'?count='+rowsLength+'&component_item='+JSON.stringify(lastTrObj); 
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
            let actionUrl = '{{route("expense.tax.calculation")}}';
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
                    text: 'Please first add & select row item.',
                    icon: 'error',
                });
                return false;
            }
            if (editItemIds.length) {
                $("#deleteComponentModal").find("#deleteConfirm").attr('data-ids',JSON.stringify(editItemIds));
                $("#deleteComponentModal").modal('show');
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
        function getItemAttribute(itemId, rowCount, selectedAttr, tr){
            let actionUrl = '{{route("expense.item.attr")}}'+'?item_id='+itemId+`&rowCount=${rowCount}&selectedAttr=${selectedAttr}`;
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

        /*Add discount row*/
        $(document).on('click', '.addDiscountItemRow', (e) => {
            let disName = $("[name='itemDiscountName']").val();
            let disPerc = $("[name='itemDiscountPercentage']").val();
            let disAmount = $("[name='itemDiscountAmount']").val();
            if(disName && (disPerc || disAmount)) {
                let rowCount = $(e.target.closest('tbody')).find("#row_count").val();
                let tblRowCount = $("#eachRowDiscountTable").find('.display_discount_row').length;
                let actionUrl = '{{route("expense.item.discount.row")}}'+'?tbl_row_count='+tblRowCount+'&row_count='+rowCount+'&dis_name='+disName+'&dis_percentage='+disPerc+'&dis_amount='+disAmount;
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
        $(document).on('change focus', '#itemTable tr input ', function(e){
            let currentTr = e.target.closest('tr'); 
            getItemDetail(currentTr);
        });

        function getItemDetail(currentTr) {
            let pName = $(currentTr).find("[name*='component_item_name']").val();
            let itemId = $(currentTr).find("[name*='item_id']").val();
            let poHeaderId = $(currentTr).find("[name*='purchase_order_id']").val();
            let poDetailId = $(currentTr).find("[name*='po_detail_id']").val();
            let soHeaderId = $(currentTr).find("[name*='sale_order_id']").val();
            let soDetailId = $(currentTr).find("[name*='so_detail_id']").val();
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

                let headerId = $(currentTr).find("[name*='expense_header_id']").val() ?? '';
                let detailId = $(currentTr).find("[name*='expense_detail_id']").val() ?? '';

                let actionUrl = '{{route("expense.get.itemdetail")}}' + 
                    '?item_id=' + itemId +
                    '&purchase_order_id=' + poHeaderId + 
                    '&po_detail_id=' + poDetailId + 
                    '&sale_order_id=' + soHeaderId + 
                    '&so_detail_id=' + soDetailId + 
                    '&selectedAttr=' + JSON.stringify(selectedAttr) + 
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
                //    $('html, body').scrollTop($('.trselected').offset().top - 200); 
            }
        });
        
        $('#attribute').on('hidden.bs.modal', function () {
            let rowCount = $("[id*=row_].trselected").attr('data-index');
            // $(`[id*=row_${rowCount}]`).find('.addSectionItemBtn').trigger('click');
            $(`[name="components[${rowCount}][qty]"]`).trigger('focus');
        });
        
        /*Delete server side rows*/
        $(document).on('click','#deleteConfirm', (e) => {
            let dataIds = e.target.getAttribute('data-ids');
            let actionUrl = '{{route("expense.comp.delete")}}'+'?ids='+dataIds;
            fetch(actionUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                }
            }).then(response => {
                return response.json().then(data => {
                    // console.log('data', data);
                    if(data.status == 200) {
                        // alert(data.message);
                        $('#vendor_name').prop('readonly',false);
                        $("#editBillingAddressBtn").show();
                        $("#editShippingAddressBtn").show();
                        location.reload();
                    } else{
                        $('#vendor_name').prop('readonly',true);
                        $("#editBillingAddressBtn").hide();
                        $("#editShippingAddressBtn").hide();
                        Swal.fire({
                            title: 'Error!',
                            text:  data.message || 'Please first add & select row item.',
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
            let index = e.target.closest('a').getAttribute('data-index') || 0;
            let id = e.target.closest('a').getAttribute('data-id') || 0;
            if(id) {
                $("#deleteItemDiscModal").find("#deleteItemDiscConfirm").attr('data-id', id);
                $("#deleteItemDiscModal").modal('show');
            }
        });

        /*Delete server side rows*/
        $(document).on('click','#deleteItemDiscConfirm', (e) => {
            let dataId = e.target.getAttribute('data-id');
            let actionUrl = '{{route("expense.remove.item.dis")}}'+'?id='+dataId;
            fetch(actionUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                }
            }).then(response => {
                return response.json().then(data => {
                    if(data.status == 200) {
                        // alert(data.message);
                        location.reload();
                    } else{
                        Swal.fire({
                            title: 'Error!',
                            text:  data.message,
                            icon: 'error',
                        });
                        return false;
                    }
                });
            });
        });

        /*Remove item level discount*/
        $(document).on("click", ".deleteSummaryDiscountRow", (e) => {
            let id = $(e.target.closest('tr')).find('[name*="[d_id]"]').val();
            if(id) {
                $("#deleteHeaderDiscModal").find("#deleteHeaderDiscConfirm").attr('data-id', id);
                $("#deleteHeaderDiscModal").modal('show');
            }
        });

        /*Delete server side rows*/
        $(document).on('click','#deleteHeaderDiscConfirm', (e) => {
            let dataId = e.target.getAttribute('data-id');
            let actionUrl = '{{route("expense.remove.header.dis")}}'+'?id='+dataId;
            fetch(actionUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                }
            }).then(response => {
                return response.json().then(data => {
                    if(data.status == 200) {
                        // alert(data.message);
                        location.reload();
                    } else{
                        Swal.fire({
                            title: 'Error!',
                            text:  data.message,
                            icon: 'error',
                        });
                        return false;
                    }
                });
            });
        });


        /*Remove header level exp*/
        $(document).on("click", ".deleteExpRow", (e) => {
            let id = $(e.target.closest('tr')).find('[name*="[e_id]"]').val();
            if(id) {
                $("#deleteHeaderExpModal").find("#deleteHeaderExpConfirm").attr('data-id', id);
                $("#deleteHeaderExpModal").modal('show');
            }
        });

        /*Delete server side rows*/
        $(document).on('click','#deleteHeaderExpConfirm', (e) => {
            let dataId = e.target.getAttribute('data-id');
            let actionUrl = '{{route("expense.remove.header.exp")}}'+'?id='+dataId;
            fetch(actionUrl, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                }
            }).then(response => {
                return response.json().then(data => {
                    if(data.status == 200) {
                        // alert(data.message);
                        location.reload();
                    } else{
                        Swal.fire({
                            title: 'Error!',
                            text:  data.message,
                            icon: 'error',
                        });
                        return false;
                    }
                });
            });
        });

        /*Amendment modal open*/
        $(document).on('click', '.amendmentBtn', (e) => {
            $("#amendmentconfirm").modal('show');
        });

        $(document).on('click', '#amendmentSubmit', (e) => {
            let actionUrl = "{{ route('expense.amendment.submit', $mrn->id) }}";
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
            location.href = actionUrl;
            console.log(actionUrl);
        });
    </script>
@endsection
