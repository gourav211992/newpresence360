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
<form class="ajax-input-form" method="POST" action="{{ url(request()->route('type')) }}" data-redirect="/{{ request()->route('type') }}"  enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="tax_required" id="tax_required">
    <input type="hidden" name="pi_item_ids" id="pi_item_ids">
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
                        {{-- @if(request()->route("type") != 'supplier-invoice')
                            <div class="row align-items-center mb-1">
                                <div class="col-md-3"> 
                                    <label class="form-label">Department <span class="text-danger">*</span></label>  
                                </div>  
                                <div class="col-md-5"> 
                                    <select class="form-select" id="department_id" name="department_id">
                                        <option value="">Select</option>
                                    @foreach($departments as $department)
                                    <option value="{{$department->id}}" {{$selectedDepartmentId == $department->id ? 'selected' : ''}}>{{ucfirst($department->name)}}</option>
                                    @endforeach 
                                </select> 
                                </div> 
                            </div>
                        @endif --}}
                        {{-- <div class="row align-items-center mb-1">
                            <div class="col-md-3"> 
                                <label class="form-label">Reference No </label>  
                            </div>  
                            <div class="col-md-5"> 
                                <input type="text" name="reference_number" class="form-control">
                            </div> 
                        </div> --}}
                        
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
            <div class="border-bottom mb-2 pb-25">
               <div class="row">
                <div class="col-md-6">
                    <div class="newheader "> 
                        <h4 class="card-title text-theme">{{$short_title}} Item Wise Detail</h4>
                        <p class="card-text">Fill the details</p>
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
            <div class="col-md-6 mt-2">
                <div class="mb-1">
                    <label class="form-label">Terms & Conditions</label> 
                    <select class="form-select select2" name="term_id[]" multiple>
                        @foreach($termsAndConditions as $termsAndCondition)
                        <option value="{{$termsAndCondition->id}}">{{$termsAndCondition->term_name}}</option> 
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="row">
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
@include('procurement.po.partials.tax-detail-modal')
@include('procurement.po.partials.pr-modal')
@endsection
@section('scripts')
<script type="text/javascript">
 var type = '{{ request()->route("type") }}';
 var actionUrlTax = '{{route("po.tax.calculation",["type" => ":type"])}}'.replace(':type',type);
 var getLocationUrl = '{{ route("store.get") }}';
 var getAddressOnVendorChangeUrl = "{{ route('po.get.address', ['type' => ':type']) }}".replace(':type', type); 
</script>
<script type="text/javascript" src="{{asset('assets/js/modules/common-attr-ui.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/modules/po.js')}}"></script>
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

/*Add New Row*/
$(document).on('click','#addNewItemBtn', (e) => {
    if(!checkBasicFilledDetail()) {
        Swal.fire({
            title: 'Error!',
            text: 'Please fill all the header details first',
            icon: 'error',
        });
        return false;
    }
    if(!checkVendorFilledDetail()) {
        Swal.fire({
            title: 'Error!',
            text: 'Please fill all the header details first',
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
            $input.closest('tr').find('[name*=uom_id]').empty().append(uomOption);
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
            setTableCalculation();
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
    }).on("input", function () {
        if ($(this).val().trim() === "") {
            $(this).removeData("selected");
            $(this).closest('tr').find("input[name*='component_item_name']").val('');
            $(this).closest('tr').find("input[name*='item_name']").val('');
            $(this).closest('tr').find("td[id*='itemAttribute_']").html(defautAttrBtn);
            $(this).closest('tr').find("input[name*='item_id']").val('');
            $(this).closest('tr').find("input[name*='item_code']").val('');
            $(this).closest('tr').find("input[name*='attr_name']").remove();
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
            $("select[name='currency_id']").prop('disabled', true);
            $("select[name='payment_term_id']").prop('disabled', true);
            $("#vendor_name").prop('readonly',true);
            $(".editAddressBtn").addClass('d-none');
            let locationId = $("[name='store_id'] option:selected").val();
            getLocation(locationId);
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
            let piItemHiddenId = $(`#row_${item}`).find("input[name*='[pi_item_hidden_ids]']").val();

            if(piItemHiddenId) {
                let idsToRemove = piItemHiddenId.split(',');
                let selectedPiIds = localStorage.getItem('selectedPiIds');
                if(selectedPiIds) {
                    selectedPiIds = JSON.parse(selectedPiIds);
                    let updatedIds = selectedPiIds.filter(id => !idsToRemove.includes(id));
                    localStorage.setItem('selectedPiIds', JSON.stringify(updatedIds));
                }
            }

            $(`#row_${item}`).remove();
        });
    } else {
        alert("Please first add & select row item.");
    }
    if(!$("tr[id*='row_']").length) {
        $("#itemTable > thead .form-check-input").prop('checked',false);
        $("select[name='currency_id']").prop('disabled', false);
        $("select[name='payment_term_id']").prop('disabled', false);
        $(".editAddressBtn").removeClass('d-none');
        $("#vendor_name").prop('readonly',false);
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
function getItemAttribute(itemId, rowCount, selectedAttr, tr){
    let isPi = $(tr).find('[name*="pi_item_id"]').length ? 1 : 0;
    if(!isPi) {
        if($(tr).find('td[id*="itemAttribute_"]').data('disabled')) {
            isPi = 1;
        }
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
    let routeType = '{{ request()->route("type") }}';
    let actionUrl = `{{ route("po.edit.address", ["type" => ":type"]) }}`
    .replace(':type', routeType)
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

    let type = '{{ request()->route("type") }}';
    let actionUrl = `{{ route("po.edit.address", ["type" => ":type"]) }}`
    .replace(':type', type)
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
      let type = '{{ request()->route("type") }}';
      let pi_item_ids = $("[name='pi_item_ids']").val();
      let actionUrl = '{{ route("po.get.itemdetail", ["type" => ":type"]) }}'
    .replace(':type', type)
    + `?item_id=${itemId}&selectedAttr=${encodeURIComponent(JSON.stringify(selectedAttr))}&remark=${remark}&uom_id=${uomId}&qty=${qty}&delivery=${encodeURIComponent(JSON.stringify(selectedDelivery))}&pi_item_ids=${encodeURIComponent(JSON.stringify(pi_item_ids))}`;

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
    var type = '{{ request()->route("type") }}';
    let addressType = $("#address_type").val();
    var url = '{{ route("po.address.save", ["type" => ":type"]) }}'.replace(':type', type);
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

/*Open Pr model*/
$(document).on('click', '.prSelect', (e) => {
    $("#prModal").modal('show');
    openPurchaseRequest();
    getIndents();
});

/*searchPiBtn*/
$(document).on('click', '.searchPiBtn', (e) => {
    getIndents();
});

function openPurchaseRequest()
{
    initializeAutocompleteQt("vendor_code_input_qt", "vendor_id_qt_val", "vendor_list", "vendor_code", "company_name");
    initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "pi_document_qt", "book_code", "document_number");
    initializeAutocompleteQt("pi_so_no_input_qt", "pi_so_qt_val", "pi_so_qt", "book_code", "document_number");
}

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
                    vendor_id : $("#vendor_id_qt_val").val(),
                    header_book_id : $("#book_id").val(),
                    store_id : $("#store_id_po").val() || '',
                    module_type : '{{request()->route('type')}}'
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            id: item.id,
                            label: `${item[labelKey1]}${labelKey2 ? (item[labelKey2] ? '-' + item[labelKey2] : '') : ''}`,
                            // label: `${item[labelKey1]} ${labelKey2 ? (item[labelKey2] ? '(' + item[labelKey2] + ')' : '') : ''}`,
                            code: item[labelKey1] || '', 
                        };
                    }));
                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                }
            });
        },
        appendTo : '#prModal',
        minLength: 0,
        select: function(event, ui) {
            var $input = $(this);
            $input.val(ui.item.label);
            $("#" + selectorSibling).val(ui.item.id);
            getIndents();
            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val("");
                $("#" + selectorSibling).val("");
                getIndents();
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $("#" + selectorSibling).val("");
            getIndents();
            $(this).autocomplete("search", "");
        }
    }).blur(function() {
        if (this.value === "") {
            $("#" + selectorSibling).val("");
            getIndents();
        }
    })
}

window.onload = function () {
    localStorage.removeItem('selectedPiIds');
};

function getIndents() 
{
    let selectedPiIds = localStorage.getItem('selectedPiIds') ?? '[]';
    selectedPiIds = JSON.parse(selectedPiIds);
    selectedPiIds = encodeURIComponent(JSON.stringify(selectedPiIds));

    let document_date = $("[name='document_date']").val() || '';
    let header_book_id = $("#book_id").val() || '';
    let series_id = $("#book_id_qt_val").val() || '';
    // let document_number = $("#document_no_input_qt").val() || '';
    let document_number = $("#document_id_qt_val").val() || '';
    let item_id = $("#item_id_qt_val").val() || '';
    @if(request()->type == 'supplier-invoice')
        let vendor_id = $("#vendor_id_qt_val").val() || $("#vendor_id").val() || '';
    @else
        let vendor_id = $("#vendor_id_qt_val").val() || '';
    @endif
    let department_id = $("#department_id_po").val() || '';
    let store_id = $("#store_id").val() || '';
    let sub_store_id = $("#sub_store_id_po").val() || '';
    let so_id = $("#pi_so_qt_val").val() || '';
    let type = '{{ request()->route("type") }}';
    let actionUrl = '{{ route("po.get.pi", ["type" => ":type"]) }}'.replace(':type', type);
    let item_search = $("#item_name_search").val();
    let fullUrl = `${actionUrl}?series_id=${encodeURIComponent(series_id)}&document_number=${encodeURIComponent(document_number)}&item_id=${encodeURIComponent(item_id)}&vendor_id=${encodeURIComponent(vendor_id)}&header_book_id=${encodeURIComponent(header_book_id)}&department_id=${encodeURIComponent(department_id)}&store_id=${encodeURIComponent(store_id)}&sub_store_id=${encodeURIComponent(sub_store_id)}&selected_pi_ids=${selectedPiIds}&document_date=${document_date}&item_search=${item_search}&so_id=${so_id}`;
    fetch(fullUrl).then(response => {
        return response.json().then(data => {
            $(".po-order-detail #prDataTable").empty().append(data.data.pis);
            $('.select2').select2({
                dropdownParent: $('#prModal')
            });
        });
    });
}

$(document).on('keyup', '#item_name_search', (e) => {
    getIndents();
});

/*Checkbox for pi item list*/
@if($serviceAlias == 'po')
$(document).on('change','.po-order-detail > thead .form-check-input',(e) => {
  if (e.target.checked) {
      if($('.pi_item_checkbox').first().closest('tr').find("[name='vend_name']").length) {
            let selectedVendorId = $('.pi_item_checkbox:checked').first().closest('tr').find("[name='vend_name']").val() || '';
            $("[name='vend_name']").each(function(itemIndex, vendorItem){
                if(!selectedVendorId) {
                    let firstVendor = $(vendorItem).val();
                    if(firstVendor) {
                        selectedVendorId = firstVendor;
                    }
                }
                if(selectedVendorId && $(vendorItem).val() == selectedVendorId) {
                    $(vendorItem).closest('tr').find('.form-check-input').prop('checked',true);
                } else {
                    $(vendorItem).closest('tr').find('.form-check-input').prop('checked',false);
                }
            })
        } else {
            $(".po-order-detail > tbody .form-check-input").each(function(){
                $(this).prop('checked',true);
            });
        }
  } else {
      $(".po-order-detail > tbody .form-check-input").each(function(){
          $(this).prop('checked',false);
      });
      localStorage.removeItem('selectedVendorId');
  }
});
@else
$(document).on('change','.po-order-detail > tbody .form-check-input',(e) => {
  if(!$(".po-order-detail > tbody .form-check-input:not(:checked)").length) {
      $('.po-order-detail > thead .form-check-input').prop('checked', true);
  } else {
      $('.po-order-detail > thead .form-check-input').prop('checked', false);
  }
});
@endif


function getSelectedPiIDS()
{
    let ids = [];
    $('.pi_item_checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

$(document).ready(function () {
    localStorage.removeItem('selectedVendorId');
});
@if($serviceAlias == 'po')
$(document).on('change', '#prDataTable .pi_item_checkbox', function (e) {
    let selectedVendorId = localStorage.getItem('selectedVendorId') || null;
    let currentCheckedVendorId = $(this).closest('tr').find("[name='vend_name']").val();

    if (!currentCheckedVendorId) {
        this.checked = false;
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
        let remainingChecked = $('.pi_item_checkbox:checked').filter(function () {
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
        if(venId) {
            vendorIds.push(venId);
        }
    });
    uniqueVendor = [...new Set(vendorIds)];
    if(uniqueVendor.length > 1) {
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
            $input.closest('tr').find('[name*=uom_id]').empty().append(uomOption);
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
    }).on("input", function () {
        if ($(this).val().trim() === "") {
            $(this).removeData("selected");
            $(this).closest('tr').find("input[name*='component_item_name']").val('');
            $(this).closest('tr').find("input[name*='item_name']").val('');
            $(this).closest('tr').find("td[id*='itemAttribute_']").html(defautAttrBtn);
            $(this).closest('tr').find("input[name*='item_id']").val('');
            $(this).closest('tr').find("input[name*='item_code']").val('');
            $(this).closest('tr').find("input[name*='attr_name']").remove();
        }
    });
}
    let vendorId = uniqueVendor[0];
    let currencyId = $("select[name='currency_id']").val();
    let transactionDate = $("input[name='document_date']").val() || ''; 
    let groupItems = [];
    $('tr[data-group-item]').each(function () {
        let groupItemData = $(this).data('group-item'); 
        groupItems.push(groupItemData);
    });
    
    groupItems = JSON.stringify(groupItems);
    ids = JSON.stringify(ids);
    let current_row_count = $("tbody tr[id*='row_']").length;
    let type = '{{ request()->route("type") }}';
    let actionUrl = '{{ route("po.process.pi-item", ["type" => ":type"]) }}'
    .replace(':type', type) 
    + '?ids=' + encodeURIComponent(ids)
    + '&vendor_id=' + encodeURIComponent(vendorId)
    + '&currency_id=' + encodeURIComponent(currencyId)
    + '&d_date=' + encodeURIComponent(transactionDate)
    + '&groupItems=' + encodeURIComponent(groupItems)
    + '&current_row_count='+current_row_count;

    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if(data.status == 200) {
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
                    $("#itemTable .mrntableselectexcel tr[id*='row_']:last").after(data.data.pos);
                } else {
                    $("#itemTable .mrntableselectexcel").empty().append(data.data.pos);
                }

                setTimeout(() => {
                    $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
                        let currentIndex = index + 1;
                        setAttributesUIHelper(currentIndex,"#itemTable");
                    });
                },100);
                //Update Qnt
                if(data?.data?.updatedGroupItems?.length) {
                    $('tr[data-group-item]').each(function () {
                        let obj = {};
                        obj.item_id = Number($(this).find("input[name*='[item_id]']").val()) || '';
                        obj.uom_id = Number($(this).find("[name*='[uom_id]']").val()) || '';
                        obj.attributes = '';
                        if($(this).find("[name*='[attr_name]']").length) {
                            let attributesArray = [];
                            $(this).find("[name*='[attr_name]']").each(function() {
                                let n = $(this).attr('name').replace('attr_name','item_attr_id');
                                let item_attr_id = $(`input[name="${n}"]`).val() || '';
                                let attr_name = $(this).val() || '';
                                if (item_attr_id && attr_name) {
                                    attributesArray.push(`${item_attr_id}:${attr_name}`);
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
                                $(this).find("input[name*='[qty]']").val(rowItem.total_qty);
                                $(this).find("input[name*='[pi_item_hidden_ids]']").val(rowItem.pi_item_ids);
                            }
                        }
                    });
                } 

                initializeAutocomplete2(".comp_item_code");
                $("#prModal").modal('hide');
                $("select[name='currency_id']").prop('disabled', true);
                $("select[name='payment_term_id']").prop('disabled', true);
                $("#vendor_name").prop('readonly',true);
                $(".editAddressBtn").addClass('d-none');
                let locationId = $("[name='store_id'] option:selected").val();
                getLocation(locationId);
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
                setTimeout(() => {
                    setTableCalculation();
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
                $(".vendor_address").text('-');
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
    let modalId = '#'+$("#" + selector).closest('.modal').attr('id');
    $("#" + selector).autocomplete({
        source: function(request, response) {
            let ids = [];
            $('.modal.show').find("tbody tr").each(function(index,item){
            let tedId = $(item).find("input[name*='ted_']").val();
            if(tedId) {
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
                        };
                    }));
                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        appendTo : modalId,
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

// Get Item Rate
function getItemCostPrice(currentTr)
{
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
        item_qty : itemQty
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
    getIndents();
});

$(document).on("autocompletechange autocompleteselect", "#store_po", function (event, ui) {
    let storeId = ui?.item?.id || '';
    initializeAutocompleteQt("sub_store_po", "sub_store_id_po", "sub_store", "name", "");
});
</script>
@endsection