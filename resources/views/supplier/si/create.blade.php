@extends('layouts.supplier')
@section('content')
<form class="ajax-input-form" method="POST" action="{{ route('supplier.invoice.store') }}" data-redirect="/supplier/invoice"  enctype="multipart/form-data">
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
                                <label class="form-label">Reference No </label>  
                            </div>  
                            <div class="col-md-5"> 
                                <input type="text" name="reference_number" class="form-control">
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

        <div class="row d-none" id="vendor_section">
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
                                    <input type="hidden" id="vendor_id" name="vendor_id" value="{{$vendor->id ?? null}}"/>
                                    <input type="hidden" id="vendor_code" name="vendor_code" value="{{$vendor->vendor_code ?? null}}" />
                                    <input type="hidden" id="shipping_id" name="shipping_id" value="{{$shipping->id ?? null}}"  />
                                    <input type="hidden" id="billing_id" name="billing_id" value="{{$billing->id ?? null}}"/>
                                    <input type="hidden" id="hidden_state_id" name="hidden_state_id" value="{{$shipping?->state?->id ?? null}}" />
                                    <input type="hidden" id="hidden_country_id" name="hidden_country_id" value="{{$shipping?->country?->id ?? null}}" />

                                </div>
                            </div> 
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">Currency <span class="text-danger">*</span></label>
                                    <input type="hidden" name="currency_id" value="{{$vendor?->currency?->id ?? null}}">
                                    <select class="form-select" name="currency_id">
                                    </select> 
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">Payment Terms <span class="text-danger">*</span></label>
                                    <input type="hidden" name="payment_term_id" value="{{$vendor?->paymentTerms?->id ?? null}}" >
                                    <select class="form-select" name="payment_term_id">
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
                                            <div class="mrnaddedd-prim shipping_detail">-</div>   
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
                                            <div class="mrnaddedd-prim billing_detail">-</div>   
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
@include('supplier.si.partials.summary-disc-modal')

{{-- Add expenses modal--}}
@include('supplier.si.partials.summary-exp-modal')

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
@include('supplier.si.partials.tax-detail-modal')
@include('supplier.si.partials.pr-modal')
@endsection
@section('scripts')
<script type="text/javascript">
    let actionUrlTax = '{{route("supplier.invoice.tax.calculation")}}';
</script>
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
          if(!data?.data?.doc?.document_number) {
             $("#document_number").val('');
         }
         $("#document_number").val(data?.data?.doc?.document_number);
         if(data?.data?.doc?.type == 'Manually') {
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
                location.href = '{{route('supplier.invoice.index')}}';
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
            let document_date = $("[name='document_date']").val();
            let actionUrl = "{{route('supplier.invoice.get.address')}}"+'?id='+itemId+'&document_date='+document_date;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if(data.data?.currency_exchange?.status == false) {
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

                    $("#hidden_state_id").val(data.data.shipping.state.id);
                    $("#hidden_country_id").val(data.data.shipping.country.id);
                    } else {
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
            console.log("changess!");
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
    } else {
        alert("Please first add & select row item.");
    }
    if(!$("tr[id*='row_']").length) {
        $("#itemTable > thead .form-check-input").prop('checked',false);
        $("select[name='currency_id']").prop('disabled', false);
        $("select[name='payment_term_id']").prop('disabled', false);
        $("select[name='department_id']").prop('disabled', false);
        $(".editAddressBtn").removeClass('d-none');
        $("#vendor_name").prop('readonly',false);    
        $(".prSelect").prop('disabled',false);    
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
        let rowCount = e.target.getAttribute('data-row-count');
        getItemAttribute(item_id, rowCount, selectedAttr, tr);
    } else {
        alert("Please select first item name.");
    }
});

/*For comp attr*/
function getItemAttribute(itemId, rowCount, selectedAttr, tr){
    let isPi = $(tr).find('[name*="pi_item_id"]').length ? 1 : 0;
    let actionUrl = '{{route("supplier.invoice.item.attr")}}'+'?item_id='+itemId+`&rowCount=${rowCount}&selectedAttr=${selectedAttr}&isPi=${isPi}`;
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
                qtyEnabledDisabled();
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
    let actionUrl = `{{route("supplier.invoice.edit.address")}}?type=${addressType}&vendor_id=${vendorId}&address_id=${addressId}&onChange=${onChange}`;
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
    let actionUrl = `{{route("supplier.invoice.edit.address")}}?type=${addressType}&vendor_id=${vendorId}&address_id=${addressId}&onChange=${onChange}`;
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
      let selectedDelivery = {};
      $(currentTr).find("[name*='attr_name']").each(function(index, item) {
         if($(item).val()) {
            selectedAttr.push($(item).val());
         }
      });

      $(currentTr).find("[name*='delivery']").each(function(index, item) {
        let dDate = $(item).closest('td').find('[name*="[d_date]"]').val();   
        let dQty = $(item).closest('td').find('[name*="[d_qty]"]').val();
           selectedDelivery.delivery = {"dDate" : dDate, dQty : dQty};
      });

      let uomId = $(currentTr).find("[name*='[uom_id]']").val() || '';
      let qty = $(currentTr).find("[name*='[qty]']").val() || '';

      let actionUrl = '{{route("supplier.invoice.get.itemdetail")}}'+'?item_id='+itemId+'&selectedAttr='+JSON.stringify(selectedAttr)+'&remark='+remark+'&uom_id='+uomId+'&qty='+qty+'&delivery='+JSON.stringify(selectedDelivery);
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
    var url = '{{ route("supplier.invoice.address.save") }}';
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
                $(".billing_detail").text(data.data.new_address.display_address);
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
    initializeAutocompleteQt("vendor_code_input_qt", "vendor_id_qt_val", "vendor_company_list", "alias", "name");
    initializeAutocompleteQt("book_code_input_qt", "book_id_qt_val", "book_pi", "book_code", "");
    initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "pi_document_qt", "document_number", "");
    initializeAutocompleteQt("item_name_input_qt", "item_id_qt_val", "comp_item", "item_code", "item_name");
    initializeAutocompleteQt("department_po", "department_id_po", "department", "name", "");
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
                    module_type : 'supplier-invoice'
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
        appendTo : '#prModal',
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


function getIndents() 
{
    let header_book_id = $("#book_id").val() || '';
    let series_id = $("#book_id_qt_val").val() || '';
    let document_number = $("#document_no_input_qt").val() || '';
    let item_id = $("#item_id_qt_val").val() || '';
    let vendor_id = $("#vendor_id_qt_val").val() || '';
    let actionUrl = '{{ route("supplier.invoice.get.pi") }}';
    let department_id = $("#department_id_po").val() || '';
    let fullUrl = `${actionUrl}?series_id=${encodeURIComponent(series_id)}&document_number=${encodeURIComponent(document_number)}&item_id=${encodeURIComponent(item_id)}&vendor_id=${encodeURIComponent(vendor_id)}&header_book_id=${encodeURIComponent(header_book_id)}&department_id=${encodeURIComponent(department_id)}`;
    fetch(fullUrl).then(response => {
        return response.json().then(data => {
            $(".po-order-detail #prDataTable").empty().append(data.data.pis);
        });
    });
}

/*Checkbox for pi item list*/
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
$(document).on('change','.po-order-detail > tbody .form-check-input',(e) => {
  if(!$(".po-order-detail > tbody .form-check-input:not(:checked)").length) {
      $('.po-order-detail > thead .form-check-input').prop('checked', true);
  } else {
      $('.po-order-detail > thead .form-check-input').prop('checked', false);
  }
});


function getSelectedPiIDS()
{
    let ids = [];
    $('.pi_item_checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

$(document).on('click', '.prProcess', (e) => {
    let ids = getSelectedPiIDS();
    if (!ids.length) {
        $("#prModal").modal('hide');
        Swal.fire({
            title: 'Error!',
            text: 'Please select at least one one quotation',
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
            setTimeout(() => {
                if(ui.item.is_attr) {
                    $input.closest('tr').find('.attributeBtn').trigger('click');
                } else {
                    $input.closest('tr').find('.attributeBtn').trigger('click');
                    $input.closest('tr').find('[name*="[qty]"]').val('').focus();
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
    ids = JSON.stringify(ids);
    let actionUrl = '{{ route("supplier.invoice.process.pi-item") }}'+ '?ids=' + ids;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if(data.status == 200) {
                let vendor = data?.data?.vendor || '';
                let finalDiscounts = data?.data?.finalDiscounts;
                let finalExpenses = data?.data?.finalExpenses;
                if(vendor) {
                $("#vendor_name").val(vendor.display_name).prop('readonly',true);
                $("#vendor_id").val(vendor.id);
                $("#vendor_code").val(vendor.vendor_code);

                let curOption = `<option value="${vendor.currency.id}">${vendor.currency.name}</option>`;
                let termOption = `<option value="${vendor.paymentTerm.id}">${vendor.paymentTerm.name}</option>`;
                $('[name="currency_id"]').empty().append(curOption).prop('readonly',true);
                $('[name="payment_term_id"]').empty().append(termOption).prop('readonly',true);
                $("#shipping_id").val(vendor.shipping.id);
                $("#billing_id").val(vendor.billing.id);
                $(".shipping_detail").text(vendor.shipping.display_address);
                $(".billing_detail").text(vendor.billing.display_address);
                $("#hidden_state_id").val(vendor.shipping.state.id);
                $("#hidden_country_id").val(vendor.shipping.country.id);
                $(".editAddressBtn").addClass('d-none');   
                }
                $("#itemTable .mrntableselectexcel").empty().append(data.data.pos);
                initializeAutocomplete2(".comp_item_code");
                $("#prModal").modal('hide');
                $(".prSelect").prop('disabled',true);
                $("select[name='currency_id']").prop('disabled', true);
                $("select[name='payment_term_id']").prop('disabled', true);
                $("select[name='department_id']").prop('disabled', true);
                $("#vendor_name").prop('readonly',true);
                $(".editAddressBtn").addClass('d-none');
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
                setTableCalculation();
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
</script>
@endsection