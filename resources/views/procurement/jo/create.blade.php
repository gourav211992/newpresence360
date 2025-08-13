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
<form class="ajax-input-form" data-module="jo" method="POST" action="{{ route('jo.store') }}" data-redirect="/job-order"  enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="tax_required" id="tax_required">
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                   @include('layouts.partials.breadcrumb-add-edit',['title' => $title, 'menu' => $menu, 'menu_url' => $menu_url, 'sub_menu' => $sub_menu])
              <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
               <div class="form-group breadcrumb-right">
                  <input type="hidden" name="document_status" value="draft" id="document_status">
                  <button type="button" onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button> 
                  <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" name="action" value="draft"><i data-feather='save'></i> Save as Draft</button>
                  <button type="submit" class="btn btn-primary btn-sm submit-button" name="action" value="submitted"><i data-feather="check-circle"></i> Submit</button>
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
                            <div class="col-md-12">
                                <div class="newheader border-bottom d-flex flex-wrap justify-content-between"> 
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
                                        @foreach($books as $book)
                                        <option value="{{$book->id}}">{{$book->book_code}}</option>
                                        @endforeach 
                                    </select>  
                                    <input type="hidden" name="book_code" id="book_code">
                                </div>
                            </div>
                            <div class="row align-items-center mb-1">
                                <div class="col-md-3"> 
                                    <label class="form-label">{{$short_title}} No <span class="text-danger">*</span></label>  
                                </div>  
                                <div class="col-md-5"> 
                                    <input type="text" name="document_number" class="form-control" id="document_number">
                                </div> 
                            </div>  
                            <div class="row align-items-center mb-1">
                                <div class="col-md-3"> 
                                    <label class="form-label">{{$short_title}} Date <span class="text-danger">*</span></label>  
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
                                    <select class="form-select" id="store_id" name="store_id">
                                    @foreach($locations as $location)
                                    <option value="{{$location->id}}">{{ $location?->store_name }}</option>
                                    @endforeach 
                                </select> 
                                </div> 
                            </div> 
                            <div class="row align-items-center mb-1">
                                <div class="col-md-3"> 
                                    <label class="form-label">Type <span class="text-danger">*</span></label>  
                                </div>  
                                <div class="col-md-5"> 
                                    <select class="form-select" id="job_order_type" name="job_order_type">
                                    @foreach($jobOrderTypes as $jobOrderType)
                                    <option value="{{$jobOrderType}}">{{ucfirst($jobOrderType)}}</option>
                                    @endforeach 
                                </select> 
                                </div> 
                            </div>
                            <div class="row align-items-center mb-1 d-none" id="reference_from"> 
                                <div class="col-md-3"> 
                                    <label class="form-label">Reference from</label>  
                                </div> 
                                <div class="col-md-5 action-button"> 
                                    <button type="button" class="btn btn-outline-primary btn-sm mb-0 prSelect"><i data-feather="plus-square"></i> {{$reference_from_title}}</button>
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
                                            <input type="hidden" id="vendor_address_id" name="vendor_address_id" />
                                            <input type="hidden" id="billing_address_id" name="billing_address_id" />
                                            <input type="hidden" id="delivery_address_id" name="delivery_address_id" />
                                            <input type="hidden" id="hidden_state_id" name="hidden_state_id" />
                                            <input type="hidden" id="hidden_country_id" name="hidden_country_id" />
                                            
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
                                            <select class="form-select" name="currency_id">
                                            </select> 
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-1">
                                            <label class="form-label">Payment Terms <span class="text-danger">*</span></label>
                                            <select class="form-select" name="payment_term_id">
                                            </select>  
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-1">
                                            <label class="form-label">Exchange Rate <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control mw-100 disabled-input" id="exchange_rate" name="exchange_rate" />
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
                                                        <a href="javascript:;" class="float-end font-small-2 editAddressBtn d-none" data-type="vendor_address"><i data-feather='edit-3'></i> Edit</a>
                                                    </label>
                                                    <div class="mrnaddedd-prim vendor_address">-</div>   
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
                                                    <div class="mrnaddedd-prim billing_address">-</div>   
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
                                                        <a href="javascript:;" class="float-end font-small-2 editAddressBtn d-done" data-type="delivery_address"><i data-feather='edit-3'></i> Edit</a>
                                                    </label>
                                                    <div class="mrnaddedd-prim delivery_address">-</div>   
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
                        <div class="border-bottom">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="newheader "> 
                                        <ul class="nav nav-tabs" id="productTabs" role="tablist">
                                            <li class="nav-item" role="presentation">
                                                <button class="nav-link active fs-5" id="product-details-tab" data-bs-toggle="tab" data-bs-target="#product-details" type="button" role="tab" aria-controls="product-details" aria-selected="true">
                                                    Product Details
                                                </button>
                                            </li>
                                            {{-- <li class="nav-item" role="presentation">
                                                <button class="nav-link fs-5" id="raw-materials-tab" data-bs-toggle="tab" data-bs-target="#raw-materials" type="button" role="tab" aria-controls="raw-materials" aria-selected="false">
                                                    Raw Materials
                                                </button>
                                            </li> --}}
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6 text-sm-end">
                                    <a href="javascript:;" id="deleteBtn" class="btn btn-sm btn-outline-danger me-50">
                                        <i data-feather="x-circle"></i> Delete</a>
                                        <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary d-none">
                                            <i data-feather="plus"></i> Add Item</a>
                                </div>
                            </div> 
                        </div>
                    </div>

                    <div class="tab-content" id="productTabsContent">
                        <div class="tab-pane fade show active" id="product-details" role="tabpanel" aria-labelledby="product-details-tab">
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
                                        <th width="150px">Product Code</th>
                                        <th width="240px">Product Name</th>
                                        <th max-width="180px">Attributes</th>
                                        <th>UOM</th>
                                        <th>Qty</th>
                                        <th  width="240px">Service Item (SOW)</th>
                                        <th>Service Charge</th>
                                        <th>Value</th> 
                                        {{--  <th>Discount</th>
                                        <th>Total</th>--}} 
                                        <th>Delivery Date</th> 
                                        <th width="50px">Action</th>
                                    </tr>
                                    </thead>
                                    <tbody class="mrntableselectexcel">
                                    </tbody>
                                    <tfoot>
                                        <tr class="totalsubheadpodetail"> 
                                            <td colspan="8"></td>
                                            <td class="text-end" id="totalItemValue">0.00</td>
                                            {{--  <td class="text-end" id="totalItemDiscount">0.00</td>
                                            <td class="text-end" id="TotalEachRowAmount">0.00</td>--}}
                                            <td></td>
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
                                                </tbody>
                                                </table> 
                                            </td>
                                            <td colspan="4">
                                                <table class="table border mrnsummarynewsty">
                                                    <tr>
                                                        <td colspan="2" class="p-0">
                                                            <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between"><strong>{{$short_title}} Summary</strong>
                                                                <div class="addmendisexpbtn">
                                                                    <button type="button" class="btn p-25 btn-sm btn-outline-secondary summaryTaxBtn">{{-- <i data-feather="plus"></i> --}} Tax</button>
                                                                    {{--<button type="button" class="btn p-25 btn-sm btn-outline-secondary summaryDisBtn"><i data-feather="plus"></i> Discount</button>--}}
                                                                    <button type="button" class="btn p-25 btn-sm btn-outline-secondary summaryExpBtn"><i data-feather="plus"></i> Expenses</button>
                                                                </div>                                   
                                                            </h6>
                                                        </td>
                                                    </tr>
                                                    {{--<tr class="totalsubheadpodetail"> 
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
                                                    </tr>--}}
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
                                                    <tr class="voucher-tab-foot d-none" id="exchangeDiv">
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
                        </div>
                        {{-- <div class="tab-pane fade" id="raw-materials" role="tabpanel" aria-labelledby="raw-materials-tab">
                        </div> --}}
                    </div>
                </div>

                {{-- Remark section --}}
                <div class="card">
                    <div class="card-body customernewsection-form">
                        <div class="row">
                            {{--<div class="col-md-6 mt-2">
                                <div class="mb-1">
                                    <label class="form-label">Terms & Conditions</label> 
                                    <select class="form-select select2" name="term_id[]" multiple>
                                        @foreach($termsAndConditions as $termsAndCondition)
                                        <option value="{{$termsAndCondition->id}}">{{$termsAndCondition->term_name}}</option> 
                                        @endforeach
                                    </select>
                                </div>
                            </div>--}}
                            <div class="col-md-6 mt-2">
                                <div class="mb-1">
                                    <label class="form-label">Terms & Conditions</label>
                                    <select class="form-select select2" name="term_id[]" >
                                        <option value="">Select</option>
                                        @foreach($termsAndConditions as $termsAndCondition)
                                        <option value="{{$termsAndCondition->id}}" data-detail="{{ $termsAndCondition->term_detail }}">{{$termsAndCondition->term_name}}</option> 
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <textarea name="terms_data" id="summernote" class="form-control" placeholder="Enter Terms" maxlength="250" oninput="if(this.value.length > 250) this.value = this.value.slice(0, 250);">{{ $po->tnc ?? "" }}</textarea>
                                <small class="text-muted d-block text-end">
                                    <span id="termsCharCount">0</span>/250 characters
                                </small>
                                <input type="hidden" name="tnc" id="tnc">
                                <input type="hidden" id="customer_terms_id" value="" name="terms_id" />
                            </div>
                            <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-4">
                                <div class="mb-1">
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" name="attachment[]" class="form-control" onchange = "addFiles(this,'main_po_file_preview')" multiple>
                                    <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                                </div>
                            </div>
                            <div class = "col-md-6" style = "margin-top:19px;">
                                <div class = "row" id = "main_po_file_preview">
                                </div>
                            </div> 
                            </div> 
                        </div>
                        <div class="col-md-12">
                            <div class="mb-1">  
                                <label class="form-label">Final Remarks</label> 
                                <textarea maxlength="250" type="text" rows="4" name="remarks" class="form-control" placeholder="Enter Remarks here..."></textarea> 
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
@include('procurement.jo.partials.summary-disc-modal')
{{-- Add expenses modal--}}
@include('procurement.jo.partials.summary-exp-modal')
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
    <button type="button" {{-- data-bs-dismiss="modal" --}} class="btn btn-primary submitAttributeBtn">Select</button>
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
@include('procurement.jo.partials.tax-detail-modal')
@include('procurement.jo.partials.pr-modal')
@endsection
@section('scripts')
<script type="text/javascript">
 var type = '{{ request()->route("type") }}';
 var actionUrlTax = '{{route("jo.tax.calculation")}}';
 var getLocationUrl = '{{ route("store.get") }}';
 var getAddressOnVendorChangeUrl = "{{ route('jo.get.address') }}"; 
 var getPwoUrl = '{{ route("jo.get.pi") }}';
 var soServiceAlias = '{{\App\Helpers\ConstantHelper::SO_SERVICE_ALIAS}}';
 var getItemCostUrl = '{{ route("items.get.cost") }}';
 var newNewRowUrl = '{{ route("jo.item.row") }}';
 var pwoProcessUrl = '{{ route("jo.process.pi-item") }}';
 var checkBomJobUrl = '{{ route("jo.check.bom.job") }}';
</script>
<script type="text/javascript" src="{{asset('assets/js/modules/common-attr-ui.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/modules/jo.js')}}"></script>
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

// Reflect selected option text from select2[name="term_id[]"] to #summernote1 textarea
$(document).on('change', 'select[name="term_id[]"]', function () {
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
$('#summernote').on('summernote.change', function (we, contents, $editable) {
    updateSummernoteData();
});

// Initialize Summernote (example)
$('#summernote').summernote({
    height: 200
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
        setTableCalculation();
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
        let jo = '{{\App\Helpers\ConstantHelper::PWO_SERVICE_ALIAS}}';
        if(reference_from_service.includes(jo)) {
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
            location.href = '{{route("jo.index")}}';
        },1500);
    }
}

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
        // alert("Please select first item name.");
    }
});

/*For comp attr*/
function getItemAttribute(itemId, rowCount, selectedAttr, tr){
    let isPi = $(tr).find('[name*="pi_item_id"]').length ? 1 : 0;
    if(!isPi) {
        if($(tr).find('td[id*="itemAttribute_"]').data('disabled')) {
            isPi = 1;
        }
    }
    let actionUrl = '{{ route("jo.item.attr") }}'
    + `?item_id=${itemId}&rowCount=${rowCount}&selectedAttr=${selectedAttr}&isPi=${isPi}`;

    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                $("#attribute tbody").empty();
                $("#attribute table tbody").append(data.data.html)
                $(tr).find('td:nth-child(2)').find("[name*='[attr_name]']").remove();
                $(tr).find('td:nth-child(2)').append(data.data.hiddenHtml);
                $(tr).find("td[id*='itemAttribute_']").attr('attribute-array', JSON.stringify(data.data.itemAttributeArray));
                if (data.data.attr) {
                    $("#attribute").modal('show');
                    $(".select2").select2();
                }
                qtyEnabledDisabled();
            }
        });
    });
}

$(document).on('click', '.editAddressBtn', (e) => {
    let addressType = $(e.target).closest('a').attr('data-type');
    let vendorId = $("#vendor_id").val() || '';
    let addressId =  '';
    if(addressType == 'vendor_address') 
    {
        addressId = $("#vendor_address_id").val() || '';
    }
    if(addressType == 'delivery_address') 
    {
        addressId = $("#delivery_address_id").val() || '';
    }
    let actionUrl = `{{ route("jo.edit.address") }}`
    + `?vendor_id=${vendorId}&address_id=${addressId}&type=${addressType}`;

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
                if(data.data.html) {
                    $("#edit-address .modal-dialog").html(data.data.html);
                }
                $("#edit-address").modal('show');
                initializeFormComponents(data.data.selectedAddress);
                $("#address_type").val(addressType);
                let v = $("#vendor_id").val();
                $("#hidden_vendor_id").val(v);
                if(addressType == 'vendor_address') 
                {
                    $("#vendor_address_id").val(data.data.selectedAddress.id);
                }
                if(addressType == 'delivery_address') 
                {
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
        $form.find(":input").not(e.target).not("button, [type='button'], [type='submit']").addClass('disabled-input');
        $(this).removeClass('disabled-input');
    }
    let vendorId = $("#vendor_id").val();
    let addressType = $("#address_type").val();
    let addressId = selectedValue

    let actionUrl = `{{ route("jo.edit.address") }}`
    + `?type=${addressType}&vendor_id=${vendorId}&address_id=${addressId}`;
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
$(document).on('input change focus', '#itemTable tr input', (e) => {
   let currentTr = e.target.closest('tr'); 
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
      
    let selectedDelivery = { delivery: [] };
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
      let pi_item_ids = $("[name='pi_item_ids']").val();
      let sow_id = $(currentTr).find("[name*='[sow_id]']").val();
      let actionUrl = '{{ route("jo.get.itemdetail") }}'
    + `?item_id=${itemId}&selectedAttr=${encodeURIComponent(JSON.stringify(selectedAttr))}&remark=${remark}&uom_id=${uomId}&qty=${qty}&sow_id=${sow_id}&delivery=${encodeURIComponent(JSON.stringify(selectedDelivery))}&pi_item_ids=${encodeURIComponent(JSON.stringify(pi_item_ids))}`;

      fetch(actionUrl).then(response => {
         return response.json().then(data => {
            if(data.status == 200) {
               $("#itemDetailDisplay").html(data.data.html);
            }
         });
      });
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
    let addressType = $("#address_type").val();
    var url = '{{ route("jo.address.save") }}';
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
            let addressDisplay = data?.data?.new_address?.display_address || data?.data?.add_new_address || ''
            if(addressType == 'vendor_address') {
                $("#vendor_address_id").val(data?.data?.new_address?.id);
                $(".vendor_address").text(addressDisplay);
            } 
            if(addressType == 'delivery_address') {
                $("#delivery_address_id").val(data?.data?.new_address?.id);
                $(".delivery_address").text(addressDisplay);
                if(data?.data?.add_new_address) {
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
            setTimeout(() => {
                if(data?.data?.add_new_address) {
                    $("#delivery_address_id").val('');
                }
            },0);
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
    $(`[name="components[${rowCount}][qty]"]`).focus();
    $("#attribute").modal('hide');
});
</script>
@endsection