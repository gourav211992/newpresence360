@extends('layouts.app')
@section('styles')
<style>
    #prModal .table-responsive {
        overflow-y: auto;
        max-height: 300px; /* Set the height of the scrollable body */
        position: relative;
    }

    #prModal .po-order-detail {
        width: 100%;
        border-collapse: collapse;
    }

    #prModal .po-order-detail thead {
        position: sticky;
        top: 0; /* Stick the header to the top of the table container */
        background-color: white; /* Optional: Make sure header has a background */
        z-index: 1; /* Ensure the header stays above the body content */
    }
    #prModal .po-order-detail th {
        background-color: #f8f9fa; /* Optional: Background for the header */
        text-align: left;
        padding: 8px;
    }

    #prModal .po-order-detail td {
        padding: 8px;
    }

    </style>
@endsection
@section('content')
<form class="ajax-input-form" action="{{ route('po.update', ['type' => request()->route('type'), 'id' => $po->id]) }}" method="POST" data-redirect="/{{ request()->route('type') }}" enctype="multipart/form-data">
@csrf
<input type="hidden" name="tax_required" id="tax_required" value="">
<input type="hidden" name="pi_item_ids" id="pi_item_ids">
<input type="hidden" name="po_type" id="po_type" value="{{$po->po_type}}">

<div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    @include('layouts.partials.breadcrumb-add-edit',['title' => $title, 'menu' => $menu, 'menu_url' => $menu_url, 'sub_menu' => $sub_menu])
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                        <input type="hidden" name="document_status" id="document_status">
                            <button type="button" onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
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

                                                Status : <span class="{{$docStatusClass}}">{{$po->display_status}}</span>
                                            </span>
                                        </div>
                                        <div class="col-md-8 basic-information">
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Series <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select" id="book_id" name="book_id">
                                                    <option value="">Select</option>
                                                    @foreach($books as $book)
                                                      <option value="{{$book->id}}" {{$book->id == $po->book_id ? 'selected' : ''}}>{{$book->book_code}}</option>
                                                    @endforeach
                                                    </select>
                                                    <input type="hidden" name="book_code" id="{{$po->book->book_code}}" id="book_code">
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">{{$short_title}} No <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="document_number" id="document_number" value="{{$po->document_number}}" class="form-control">
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">{{$short_title}} Date <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="date" class="form-control" value="{{ $po->document_date }}" name="document_date">
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">{{ $short_title }} Procurement Type <span
                                                            class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select" name="procurement_type"
                                                        id="procurement_type">
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Location <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="hidden" name="store_id" value="{{$po->store_id}}">
                                                    <input disabled type="text" value="{{$po?->store_location->store_name}}" placeholder="Select" class="form-control mw-100 ledgerselecct" name="store" />
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Reference No </label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" value="{{$po->reference_number}}" name="reference_number" class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        {{-- Approval History Section --}}
                                        @include('partials.approval-history', ['document_status' => $po->document_status, 'revision_number' => $revision_number])
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
                                                        <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" id="vendor_name" name="vendor_name" value="{{$po->vendor->company_name}}" />
                                                        <input type="hidden" value="{{$po->vendor_id}}" id="vendor_id" name="vendor_id" />
                                                        <input type="hidden" value="{{$po->vendor_code}}" id="vendor_code" name="vendor_code" />

                                                        <input type="hidden" value="{{$po->latestShippingAddress()}}" id="shipping_id" name="shipping_id" />
                                                        <input type="hidden" id="billing_id" value="{{$po->latestBillingAddress()->id}}" name="billing_id" />
                                                        <input type="hidden" value="{{$po->latestShippingAddress()->state?->id}}" id="hidden_state_id" name="hidden_state_id" />
                                                        <input type="hidden" value="{{$po->latestShippingAddress()->country?->id}}" id="hidden_country_id" name="hidden_country_id" />

                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Currency <span class="text-danger">*</span></label>
                                                        <select class="form-select" name="currency_id">
                                                            <option value="{{$po->currency_id}}">{{$po->currency?->name}}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Payment Terms <span class="text-danger">*</span></label>
                                                        <select class="form-select" name="payment_term_id">
                                                            <option value="{{$po->payment_term_id}}">{{$po->paymentTerm->name}}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Exchange Rate <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control mw-100 {{$isDifferentCurrency ? '' : 'disabled-input'}}" value="{{$po->org_currency_exg_rate}}" id="exchange_rate" name="exchange_rate" />
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
                                                                    {{$po?->latestBillingAddress()?->display_address}}
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
                                                                <div class="mrnaddedd-prim org_address">
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
                                                                </label>
                                                                <div class="mrnaddedd-prim delivery_address">{{$po?->latestDeliveryAddress()?->display_address}}</div>
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
                                        <h4 class="card-title text-theme">{{$short_title}} Item Wise Detail</h4>
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
                                                <th max-width="180px">Attributes</th>
                                                <th>UOM</th>
                                                <th>Qty</th>
                                                <th>Rate</th>
                                                <th>Value</th>
                                                <th>Discount</th>
                                                <th>Total</th>
                                                <th>Delivery Date</th>
                                                <th width="5px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody class="mrntableselectexcel">
                                            @include('procurement.po.partials.item-row-edit')
                                        </tbody>
                                        <tfoot>
                                            <tr class="totalsubheadpodetail">
                                                <td colspan="8"></td>
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
                                                </table>
                                            </td>
                                            <td colspan="4">
                                                <table class="table border mrnsummarynewsty">
                                                    <tr>
                                                        <td colspan="2" class="p-0">
                                                            <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between"><strong>{{$short_title}} Summary</strong>
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
                                                        <td class="text-end" id="f_sub_total">0.00</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Item Discount</strong></td>
                                                        <td class="text-end" id="f_total_discount">0.00</td>
                                                    </tr>
                                                    @if($po->headerDiscount())
                                                    <tr id="f_header_discount_hidden">
                                                        <td><strong>Header Discount</strong></td>
                                                        <td class="text-end" id="f_header_discount">{{$po->headerDiscount()->sum('ted_amount')}}</td>
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
                                                        <td class="text-end" id="f_total_after_tax">0.00</td>
                                                    </tr>

                                                    <tr>
                                                        <td><strong>Expense</strong></td>
                                                        <td class="text-end" id="f_exp">0.00</td>
                                                    </tr>
                                                    <tr class="voucher-tab-foot">
                                                        <td class="text-primary"><strong>Grand Total</strong></td>
                                                        <td>
                                                            <div class="quottotal-bg justify-content-end">
                                                                <h5 id="f_total_after_exp">0.00</h5>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <tr class="voucher-tab-foot {{$isDifferentCurrency ? '' : 'd-none'}}" id="exchangeDiv">
                                                        <td class="text-primary"><strong>Grand Total ({{$currencyName}})</strong></td>
                                                        <td>
                                                            <div class="quottotal-bg justify-content-end">
                                                                <h5 id="f_total_after_exp_rate">0.00</h5>
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
                                        <option value="">Select</option>
                                        @foreach($termsAndConditions as $termsAndCondition)
                                            @if(in_array($termsAndCondition->id, $po->TermsConditions->pluck('term_id')->toArray()))
                                            <option value="{{$termsAndCondition->id}}" selected>{{$termsAndCondition->term_name}}</option>
                                            @else
                                            <option value="{{$termsAndCondition->id}}">{{$termsAndCondition->term_name}}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
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

{{-- Add expenses modal--}}
@include('procurement.po.partials.summary-exp-modal')

{{-- Edit Address --}}
<div class="modal fade" id="edit-address" tabindex="-1" aria-labelledby="one" aria-hidden="true">
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
                {{-- <p class="text-center">Enter the details below.</p> --}}
                <div class="text-end"><a href="javascript:;" class="text-primary add-contactpeontxt mt-50 addDiscountItemRow"><i data-feather='plus'></i> Add Discount</a></div>
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
                            <input type="number" step="any" name="itemDiscountPercentage" class="form-control mw-100" />
                        </td>
                        <td>
                            <input type="number" step="any" name="itemDiscountAmount" class="form-control mw-100" /></td>
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
                        <textarea maxlength="250" class="form-control" placeholder="Enter Remarks"></textarea>
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
@include('procurement.po.partials.tax-detail-modal')
@endsection
@section('scripts')
<script>
    let type = '{{ request()->route("type") }}';
    let actionUrlTax = '{{route("po.tax.calculation",["type" => ":type"])}}'.replace(':type',type);
</script>
<script type="text/javascript" src="{{asset('assets/js/modules/common-attr-ui.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/modules/po.js')}}"></script>
<script>

@if($po->document_status != 'draft')
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

function getDocNumberByBookId(bookId) {
  let document_date = $("[name='document_date']").val();
  let actionUrl = '{{route("book.get.doc_no_and_parameters")}}'+'?book_id='+bookId+'&document_date='+document_date;
  fetch(actionUrl).then(response => {
    return response.json().then(data => {
        // console.log(data.data);
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
        let poType = parameters.goods_or_services || 'Goods';
        $("#po_type").val(poType);
        setTableCalculation();
     }
     if(data.status == 404) {
        $("#book_code").val('');
        $("#document_number").val('');
        $("#tax_required").val("");
        $("#po_type").val("Goods");
        const docDateInput = $("[name='document_date']");
        docDateInput.removeAttr('min');
        docDateInput.removeAttr('max');
        // docDateInput.val(new Date().toISOString().split('T')[0]);
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

    const $procurementTypeSelect = $('#procurement_type');
    const poProcurementType = @json($po->procurement_type ?? '') || parameters?.po_procurement_type || '';
    const PO_PROCUREMENT_TYPE_VALUES = @json(\App\Helpers\CommonHelper::PO_PROCUREMENT_TYPE_VALUES);

    if (poProcurementType === 'All') {
        $procurementTypeSelect.empty();
        PO_PROCUREMENT_TYPE_VALUES.forEach(function(value) {
            console.log(true);

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
                disabled: true
            }));

    }

    /*Reference from*/
    let reference_from_service = parameters.reference_from_service;
    if(reference_from_service.length) {
        let pi = '{{\App\Helpers\ConstantHelper::PI_SERVICE_ALIAS}}';
        let po = '{{\App\Helpers\ConstantHelper::PO_SERVICE_ALIAS}}';
        if(reference_from_service.includes(pi) || reference_from_service.includes(po)) {
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
            @if(request()->type == 'supplier-invoice')
                location.href = '{{url('supplier-invoice')}}';
            @else
                location.href = '{{url("purchase-order")}}';
            @endif
        },1500);
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
            let type = '{{ request()->route("type") }}';
            let actionUrl = "{{ route('po.get.address', ['type' => ':type']) }}".replace(':type', type)
    + '?id=' + itemId;
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
                    type:'po_item_list',
                    selectedAllItemIds : JSON.stringify(selectedAllItemIds),
                    po_type: $("#po_type").val(),
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
            $input.closest('tr').find('[name*=uom_id]').empty().append(uomOption);
            $input.closest('tr').find("input[name*='attr_group_id']").remove();
            let price = 0;
            let transactionType = 'collection';
            let partyCountryId = 101;
            let partyStateId = 36;
            let rowCount = Number($($input).closest('tr').attr('data-index'));
            setTimeout(() => {
                if(ui.item.is_attr) {
                    $input.closest('tr').find('.attributeBtn').trigger('click');
                } else {
                    $input.closest('tr').find('[name*="[qty]"]').focus();
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
   let item_id = lastRow.find("[name*='[item_id]']").val();
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

let type = '{{ request()->route("type") }}';
let actionUrl = '{{ route("po.item.row", ["type" => ":type"]) }}'
    .replace(':type', type)
    + '?count=' + rowsLength
    + '&component_item=' + encodeURIComponent(JSON.stringify(lastTrObj));
fetch(actionUrl).then(response => {
    return response.json().then(data => {
        if (data.status == 200) {
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
        let rowCount = e.target.getAttribute('data-row-count');
        getItemAttribute(item_id, rowCount, selectedAttr, tr);
    } else {
        alert("Please select first item name.");
    }
});

/*For comp attr*/
function getItemAttribute(itemId, rowCount, selectedAttr, tr){

let isPi = 0;
if($(tr).find('[name*="pi_item_id"]').length) {
    isPi = Number($(tr).find('[name*="pi_item_id"]').val()) || 0;
}
let poItemId = 0;
if($(tr).find('[name*="po_item_id"]').length) {
    poItemId = Number($(tr).find('[name*="po_item_id"]').val()) || 0;
}
let type = '{{ request()->route("type") }}';
let actionUrl = '{{ route("po.item.attr", ["type" => ":type"]) }}'
.replace(':type', type)
+ `?item_id=${itemId}&rowCount=${rowCount}&selectedAttr=${selectedAttr}&isPi=${isPi}`;
fetch(actionUrl).then(response => {
    return response.json().then(data => {
        if (data.status == 200) {
            $("#attribute tbody").empty();
            $("#attribute table tbody").append(data.data.html)
            $(tr).find('td:nth-child(2)').find("[name*='[attr_name]']").remove();
            $(tr).find('td:nth-child(2)').append(data.data.hiddenHtml)
            if (data.data.attr) {
                $("#attribute").modal('show');
                $(".select2").select2();
            }
        }
    });
});
}

/*Display item detail*/
$(document).on('input change focus', '#itemTable tr input', (e) => {
   let currentTr = e.target.closest('tr');
   let rowCount = $(currentTr).attr('data-index');
   let pName = $(currentTr).find("[name*='component_item_name']").val();
   let itemId = $(currentTr).find("[name*='[item_id]']").val();
   let po_item_id = $(currentTr).find("[name*='[po_item_id]']").val();
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

      let selectedDelivery = [];
      $(currentTr).find("[name*='[d_qty]']").each(function(index, item) {
        let dDate = $(item).closest('td').find(`[name*="components[${rowCount}][delivery][${index+1}][d_date]"]`).val();
        let dQty = $(item).closest('td').find(`[name*="components[${rowCount}][delivery][${index+1}][d_qty]"]`).val();
           selectedDelivery.push({"dDate": dDate, "dQty": dQty});
      });

      let uomId = $(currentTr).find("[name*='[uom_id]']").val() || '';
      let qty = $(currentTr).find("[name*='[qty]']").val() || '';
      let type = '{{ request()->route("type") }}';
      let actionUrl = '{{ route("po.get.itemdetail", ["type" => ":type"]) }}'
    .replace(':type', type)
    + `?item_id=${itemId}&selectedAttr=${encodeURIComponent(JSON.stringify(selectedAttr))}&remark=${remark}&uom_id=${uomId}&qty=${qty}&delivery=${encodeURIComponent(JSON.stringify(selectedDelivery))}&po_item_id=${po_item_id}`;
      fetch(actionUrl).then(response => {
         return response.json().then(data => {
            if(data.status == 200) {
                selectedDelivery = [];
               $("#itemDetailDisplay").html(data.data.html);
            }
         });
      });
   }
});

/*submit attribute*/
$(document).on('click', '.submitAttributeBtn', (e) => {
    let rowCount = $("[id*=row_].trselected").attr('data-index');
    $(`[name="components[${rowCount}][qty]"]`).focus();
    $("#attribute").modal('hide');
});

setTimeout(() => {
    $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
        let currentIndex = index + 1;
        setAttributesUIHelper(currentIndex,"#itemTable");
    });
},100);
</script>
@endsection
