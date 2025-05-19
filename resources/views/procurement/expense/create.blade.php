@extends('layouts.app')
@section('content')
    <form class="ajax-input-form" method="POST" action="{{ route('expense.store') }}" data-redirect="/expenses" enctype="multipart/form-data">
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
                                    <h2 class="content-header-title float-start mb-0">Expense</h2>
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
                                                        <label class="form-label">Reference No </label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="reference_number" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Outstanding PO/SO </label>
                                                    </div>
                                                    <div class="col-md-2 action-button">
                                                        <button data-bs-toggle="modal" type="button" data-bs-target="#rescdule"
                                                            class="btn btn-outline-primary btn-sm" id="outstanding">
                                                        <i data-feather="plus-square"></i> Outstanding PO
                                                        </button>
                                                        <div id="select_po"></div>
                                                    </div>
                                                    <div class="col-md-2 action-button">
                                                        <button data-bs-toggle="modal" type="button" data-bs-target="#rescdule-so"
                                                            class="btn btn-outline-primary btn-sm" id="outstanding-so">
                                                        <i data-feather="plus-square"></i> Outstanding SO
                                                        </button>
                                                        <div id="select_so"></div>
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
                                                    <div class="col-md-2">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                            Supplier Invoice No.
                                                            </label>
                                                            <input type="text" name="supplier_invoice_no"
                                                                class="form-control bg-white"
                                                                placeholder="Enter Supplier Invoice No.">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="mb-1">
                                                            <label class="form-label">
                                                                Supplier Invoice Date
                                                            </label>
                                                            <input type="date" name="supplier_invoice_date"
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
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="totalsubheadpodetail">
                                                                <td colspan="6"></td>
                                                                <td class="text-end" id="totalItemValue">0.00</td>
                                                                <td class="text-end" id="totalItemDiscount">0.00</td>
                                                                <td class="text-end" id="TotalEachRowAmount">0.00</td>
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
                                                                    </table>
                                                                </td>
                                                                <td colspan="3">
                                                                    <table class="table border mrnsummarynewsty">
                                                                        <tr>
                                                                            <td colspan="2" class="p-0">
                                                                                <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between">
                                                                                    <strong>Expense Summary</strong>
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
        @include('procurement.expense.partials.summary-disc-modal')
        {{-- Add expenses modal--}}
        @include('procurement.expense.partials.summary-exp-modal')
        {{-- Add Outstanding PO modal--}}
        @include('procurement.expense.partials.outstanding-po-modal')
        {{-- Add Outstanding SO modal--}}
        @include('procurement.expense.partials.outstanding-so-modal')
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
                    <div class="text-end">
                    </div>
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
    @include('procurement.expense.partials.tax-detail-modal')
@endsection
@section('scripts')
    <script type="text/javascript">
        let actionUrlTax = '{{route("po.tax.calculation")}}';
    </script>
    <script type="text/javascript" src="{{asset('assets/js/modules/expense.js')}}"></script>
    <script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
    <script>
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
                if(reference_from_service.includes(po)) {
                    $("#reference_from").removeClass('d-none');
                } else {
                    $("#reference_from").addClass('d-none');
                }
                if(reference_from_service.includes('d')) {
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
                    location.href = '{{route("expense.index")}}';
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
                    let document_date = $("[name='document_date']").val();
                    let actionUrl = "{{route('po.get.address')}}"+'?id='+itemId+'&document_date='+document_date;
                    fetch(actionUrl).then(response => {
                        return response.json().then(data => {
                            if(data.data?.currency_exchange?.status == false) {
                                $input.val('');
                                $("#vendor_id").val('');
                                $("#vendor_code").val('');
                                $("#hidden_state_id").val('');
                                $("#hidden_country_id").val('');
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

                                $("#hidden_state_id").val(data.data.shipping.state.id);
                                $("#hidden_country_id").val(data.data.shipping.country.id);
                            }  else {
                                if(data.data.error_message) {
                                    $input.val('');
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
                                        text: data.data.error_message,
                                        icon: 'error',
                                    });
                                    return false;
                                }
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
            } else{
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
                            // taxHidden(queryParams);
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

                if($("tr[id*='row_']:last").find("[name*='[attr_group_id]']").length == 0 && item_id) {
                    lastTrObj.attr_require = false;
                }
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
                        Swal.fire({
                            title: 'Error!',
                            text: 'Someting went wrong!',
                            icon: 'error',
                        });
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
                Swal.fire({
                    title: 'Error!',
                    text: "Please first add & select row item.",
                    icon: 'error',
                });
                return false;
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
                Swal.fire({
                    title: 'Error!',
                    text: "Please select first item name.",
                    icon: 'error',
                });
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
                let qty = $(currentTr).find("[name*='[accepted_qty]']").val() || '';
                let actionUrl = '{{route("expense.get.itemdetail")}}'+'?item_id='+itemId+'&purchase_order_id='+poHeaderId+'&po_detail_id='+poDetailId+'&sale_order_id='+soHeaderId+'&so_detail_id='+soDetailId+'&selectedAttr='+JSON.stringify(selectedAttr)+'&remark='+remark+'&uom_id='+uomId+'&qty='+qty;
                fetch(actionUrl).then(response => {
                    return response.json().then(data => {
                        if(data.status == 200) {
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
                    url: '/expenses/get-po-items-by-po-id',
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
                                    // Convert document_date to DD/MM/YYYY format if it's available
                                    let documentDate = val?.po?.document_date ?? 'NA';
                                    if (documentDate !== 'NA') {
                                        const [year, month, day] = documentDate.split('-');
                                        documentDate = `${day}/${month}/${year}`;
                                    }
                                    // Handle remarks to show "N/A" if empty, null, or undefined
                                    let remarks = val?.remarks?.trim() || 'N/A';
                                    html += '    <td>' + documentDate +
                                        '</td>';
                                    html += '    <td>' + (val?.item?.item_name + '(' + val
                                            ?.item?.item_code + ')' ?? 'N/A') +
                                        '</td>';
                                    html += '    <td>' + remarks +
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
                    url: '/expenses/get-items-by-vendor',
                    success: function(response) {
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
                                    // Convert document_date to DD/MM/YYYY format if it's available
                                    let documentDate = val?.po?.document_date ?? 'NA';
                                    if (documentDate !== 'NA') {
                                        const [year, month, day] = documentDate.split('-');
                                        documentDate = `${day}/${month}/${year}`;
                                    }
                                    // Handle remarks to show "N/A" if empty, null, or undefined
                                    let remarks = val?.remarks?.trim() || 'N/A';
                                    html += '    <td>' + documentDate +
                                        '</td>';
                                    html += '    <td>' + (val?.item?.item_name + '(' + val
                                            ?.item?.item_code + ')' + '(' + val?.item?.type + ')'  ?? 'N/A') +
                                        '</td>';
                                    html += '    <td>' + remarks +
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

        var selectedSoItemIds = $('#so-item-ids').val();
        var selectedSoItemIds = $('.so_detail_items:checked').map(function() {
            return $(this).val();
        }).get();

        $('#customer-select').change(function() {
            var val = $(this).val();
            if (val) {
                var data = {
                    customer_id: val
                }
                $.ajax({
                    type: 'POST',
                    data: data,
                    url: '/expenses/get-items-by-customer',
                    success: function(response) {
                        console.log('response.........', response.response.data);
                        if (response.success) {
                            html = '';
                            html1 = '';
                            html1 += '<option value="">-Select SO-</option>';
                            html = '<div id ="notSelect"></div>';
                            let selectedSoItemIds = [];
                            $.each(response.response.data, (key, val) => {
                                var bal_qty = (val?.order_qty) - (val?.invoice_qty);
                                bal_qty = parseFloat(bal_qty);
                                if(bal_qty > 0){
                                    selectedSoItemIds.push(val.id);
                                    html += '<tr>';
                                    html += '    <td>';
                                    html +=
                                        '        <div class="form-check form-check-inline me-0">';
                                    html += '<input class="form-check-input so_detail_items" type="checkbox" name="so_detail_item_' +
                                        key + '" value="' + val.id + '" data-item=' + "'" + JSON
                                        .stringify(val) + "'" + '>';
                                    html += '        </div>';
                                    html += '    </td>';
                                    html += '    <td>' + (val?.header?.document_number ?? 'N/A') +
                                        '</td>';
                                    // Convert document_date to DD/MM/YYYY format if it's available
                                    let documentDate = val?.header?.document_date ?? 'NA';
                                    if (documentDate !== 'NA') {
                                        const [year, month, day] = documentDate.split('-');
                                        documentDate = `${day}/${month}/${year}`;
                                    }
                                    // Handle remarks to show "N/A" if empty, null, or undefined
                                    let remarks = val?.remarks?.trim() || 'N/A';
                                    html += '    <td>' + documentDate +
                                        '</td>';
                                    html += '    <td>' + (val?.item?.item_name + '(' + val
                                            ?.item?.item_code + ')' + '(' + val?.item?.type + ')' ?? 'N/A') +
                                        '</td>';
                                    html += '    <td>' + remarks +
                                        '</td>';
                                    html += '    <td>' + val?.order_qty + '</td>';
                                    html += '    <td>' + (bal_qty.toFixed(2)) + '</td>';
                                    html += '</tr>';
                                }
                                html1 += '<option value="' + val?.header?.id + '">' + val
                                    ?.header?.document_number + '</option>';
                            });
                            $('#so-item-ids').val(selectedSoItemIds);
                            $('#so-modal-table-body').html(html);
                            $('#so_items').html(html1);
                        }
                    }
                });
            } else {
                $('#so-modal-table-body').html('');
            }
        });

        $('#so_items').change(function() {
            var val1 = $(this).val();

            if (val1) {
                var data1 = {
                    sale_order_id: val1
                }

                $.ajax({
                    type: 'POST',
                    data: data1,
                    url: '/expenses/get-so-items-by-so-id',
                    success: function(response) {
                        if (response.success) {
                            html = '';

                            $.each(response.response.data, (key, val) => {
                                var bal_qty = (val?.order_qty) - (val?.invoice_qty);
                                bal_qty = parseFloat(bal_qty);
                                if(bal_qty > 0){
                                    html += '<tr>';
                                    html += '    <td>';
                                    html +=
                                        '        <div class="form-check form-check-inline me-0">';
                                    html += '<input class="form-check-input so_detail_items" type="checkbox" name="so_detail_item_' +
                                        key + '" value="' + val.id + '" data-item=' + "'" + JSON
                                        .stringify(val) + "'" + '>';
                                    html += '        </div>';
                                    html += '    </td>';
                                    html += '    <td>' + (val?.header?.document_number ?? 'N/A') +
                                        '</td>';
                                    // Convert document_date to DD/MM/YYYY format if it's available
                                    let documentDate = val?.header?.document_date ?? 'NA';
                                    if (documentDate !== 'NA') {
                                        const [year, month, day] = documentDate.split('-');
                                        documentDate = `${day}/${month}/${year}`;
                                    }
                                    // Handle remarks to show "N/A" if empty, null, or undefined
                                    let remarks = val?.remarks?.trim() || 'N/A';
                                    html += '    <td>' + documentDate +
                                        '</td>';
                                    html += '    <td>' + (val?.item?.item_name + '(' + val
                                            ?.item?.item_code + ')' ?? 'N/A') +
                                        '</td>';
                                    html += '    <td>' + remarks +
                                        '</td>';
                                    html += '    <td>' + val?.order_qty + '</td>';
                                    html += '    <td>' + (bal_qty.toFixed(2)) + '</td>';
                                    html += '</tr>';
                                }
                            });
                            $('#so-modal-table-body').html(html);
                        }
                    }
                });
            } else {
                $('#modal-table-body').html('');
            }
        });

        $('#po-items-select-all').change(function() {
            if ($(this).is(":checked")) {
                $('.po_detail_items').prop('checked', true);
            } else {
                $('.po_detail_items').prop('checked', false);
            }
        })

        $('#so-items-select-all').change(function() {
            if ($(this).is(":checked")) {
                $('.so_detail_items').prop('checked', true);
            } else {
                $('.so_detail_items').prop('checked', false);
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
            let actionUrl = '{{route("expense.po-item.row")}}'+'?item_ids='+selectedPoItemIds+'&vendor_id='+vendorId;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        $("#itemTable > tbody").html(data.data.html);
                        $("[name*='component_item_name[1]']").trigger('focus');
                        $("[name*='component_item_name[1]']").trigger('blur');
                        $('#rescdule').modal('hide');
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
                        $("#hidden_state_id").val(data.data.shipping.state.id);
                        $("#hidden_country_id").val(data.data.shipping.country.id);
                        // console.log($("#shipping_address"), "shipping address");
                        $('#vendor_name').prop('readonly',true);
                        $('#outstanding-so').prop('disabled', true);
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
                        Swal.fire({
                            title: 'Error!',
                            text: 'Someting went wrong!',
                            icon: 'error',
                        });
                    }
                });
            });
        });

        $(document).on('click', "#so-process-btn", function(e) {
            e.preventDefault();
            var html = '';

            var checkVal = $('.so_detail_items:checked').val();
            console.log('check val', checkVal);
            var customerId = $('#customer-select').val();
            if(!customerId){
                $('#notSelect').html('');
                $('#customerNotSelect').html('<span style="color:red">Please select customer first</span>');
                return true;
            } else {
                $('#customerNotSelect').html('');
                $('#notSelect').html('');
                $('#customerNotSelect').hide();
            }

            if (checkVal == undefined) {
                $('#customerNotSelect').html('');
                $('#notSelect').html('<span style="color:red">Please select any SO</span>');
                return true;
            } else {
                $('#customerNotSelect').html('');
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
            var selectedSoItemIds = $('#so-item-ids').val();
            var selectedSoItemIds = $('.so_detail_items:checked').map(function() {
                return $(this).val();
            }).get();

            var customerId = $('#customer-select').val();
            let actionUrl = '{{route("expense.so-item.row")}}'+'?item_ids='+selectedSoItemIds+'&customer_id='+customerId;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        $("#itemTable > tbody").html(data.data.html);
                        $("[name*='component_item_name[1]']").trigger('focus');
                        $("[name*='component_item_name[1]']").trigger('blur');
                        $('#rescdule-so').modal('hide');
                        $('.so_detail_items').prop('checked', false);
                        $('#so-items-select-all').prop('checked', false);
                        $('#outstanding').prop('disabled', true);
                        setTableCalculation();
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

        /* Address Submit */
        $(document).on('click', '.submitAddress', function (e) {
            $('.ajax-validation-error-span').remove();
            e.preventDefault();
            var innerFormData = new FormData();
            $('#edit-address').find('input,textarea,select').each(function () {
                innerFormData.append($(this).attr('name'), $(this).val());
            });
            var method = "POST" ;
            var url = '{{route("expense.address.save")}}';
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
    </script>
@endsection
