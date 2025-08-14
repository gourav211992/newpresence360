@extends('layouts.supplier')
@section('content')
<form class="ajax-input-form" action="{{ route('po.update', $po->id) }}" method="POST" data-redirect="/purchase-order" enctype="multipart/form-data">
@csrf
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
                                                        @if($po->latestShippingAddress() || $po->latestBillingAddress())
                                                        <input type="hidden" value="{{$po->latestShippingAddress()}}" id="shipping_id" name="shipping_id" />
                                                        <input type="hidden" id="billing_id" value="{{$po->latestBillingAddress()->id}}" name="billing_id" />
                                                        <input type="hidden" value="{{$po->latestShippingAddress()->state?->id}}" id="hidden_state_id" name="hidden_state_id" />
                                                        <input type="hidden" value="{{$po->latestShippingAddress()->country?->id}}" id="hidden_country_id" name="hidden_country_id" />
                                                        @else
                                                        <input type="hidden" value="{{$po->shipping_address}}" id="shipping_id" name="shipping_id" />
                                                        <input type="hidden" id="billing_id" value="{{$po->billing_address}}" name="billing_id" />
                                                        <input type="hidden" value="{{$po?->ship_address?->state?->id}}" id="hidden_state_id" name="hidden_state_id" />
                                                        <input type="hidden" value="{{$po?->ship_address?->country?->id}}" id="hidden_country_id" name="hidden_country_id" />
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="mb-1">
                                                        <label class="form-label">Currency <span class="text-danger">*</span></label>
                                                        <select class="form-select" name="currency_id">
                                                            <option id="{{$po->currency_id}}">{{$po->currency?->name}}</option>
                                                            {{-- <option value="">Select</option>
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
                                                            <option value="{{$po->payment_term_id}}">{{$po->paymentTerm->name}}</option>
                                                            {{-- <option value="">Select</option>
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
                                                                <label class="form-label w-100">Select Shipping Address <span class="text-danger">*</span> <a href="javascript:;" class="float-end font-small-2 editAddressBtn" data-type="shipping"><i data-feather='edit-3'></i> Edit</a></label>
                                                                <div class="mrnaddedd-prim shipping_detail">@if($po->latestShippingAddress())
                                                                     {{$po->latestShippingAddress()->display_address}}
                                                                    @else
                                                                     {{$po->ship_address?->display_address}}
                                                                    @endif</div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="customer-billing-section h-100">
                                                        <p>Billing Details</p>
                                                        <div class="bilnbody">
                                                            <div class="genertedvariables genertedvariablesnone">
                                                                <label class="form-label w-100">Select Billing Address <span class="text-danger">*</span> <a href="javascript:;" class="float-end font-small-2 editAddressBtn" data-type="billing"><i data-feather='edit-3'></i> Edit</a></label>
                                                                <div class="mrnaddedd-prim billing_detail">@if($po->latestBillingAddress())
                                                                    {{$po->latestBillingAddress()->display_address}}
                                                                    @else
                                                                    {{$po->bill_address?->display_address}}
                                                                    @endif</div>
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
                                <div class="col-md-6 text-sm-end">
                                    {{-- <a href="javascript:;" id="deleteBtn" class="btn btn-sm btn-outline-danger me-50">
                                        <i data-feather="x-circle"></i> Delete</a>
                                        <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary">
                                            <i data-feather="plus"></i> Add Item</a> --}}
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
                                                <th>Qty</th>
                                                <th>Rate</th>
                                                <th>Value</th>
                                                <th>Discount</th>
                                                <th>Total</th>
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
                                            <td colspan="7" rowspan="10">
                                                <table class="table border">
                                                    <tbody id="itemDetailDisplay">
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
                                                            <span class="badge rounded-pill badge-light-secondary text-wrap"><strong>Remarks</strong>: </span>
                                                        </td>
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
              <p>Are you sure you want to <strong>Amendment</strong> this <strong>PO</strong>? After Amendment this action cannot be undone.</p>
              <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
              <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
          </div> 
      </div>
  </div>
</div>

{{-- Purchase Model --}}
@include('procurement.po.partials.pr-modal')

@endsection
@section('scripts')
<script>
    var actionUrlTax = '{{route("po.tax.calculation")}}';
</script>
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
            let actionUrl = "{{route('po.get.address')}}"+'?id='+itemId;
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

let actionUrl = '{{route("po.item.row")}}'+'?count='+rowsLength+'&component_item='+JSON.stringify(lastTrObj);
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

    let actionUrl = '{{route("po.item.attr")}}'+'?item_id='+itemId+`&rowCount=${rowCount}&selectedAttr=${selectedAttr}`;
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
      let actionUrl = '{{route("po.get.itemdetail")}}'+'?item_id='+itemId+'&selectedAttr='+JSON.stringify(selectedAttr)+'&remark='+remark+'&uom_id='+uomId+'&qty='+qty+'&delivery='+JSON.stringify(selectedDelivery);;
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

function initializeAutocompleteQt(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "") 
{
    $("#" + selector).autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type: typeVal,
                    vendor_id : $("#vendor_id_qt_val").val()
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
        minLength: 0,
        select: function(event, ui) {
            var $input = $(this);
            $input.val(ui.item.label);
            $("#" + selectorSibling).val(ui.item.id);
            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val("");
                $("#" + selectorSibling).val("");
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
        }
    });
}
</script>
@endsection
