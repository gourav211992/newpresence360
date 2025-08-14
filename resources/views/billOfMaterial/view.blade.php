@extends('layouts.app')
@section('content')
@use(App\Helpers\ConstantHelper)
@php
$routeAlias = $servicesBooks['services'][0]?->alias ?? null;
if($routeAlias == ConstantHelper::BOM_SERVICE_ALIAS) 
{
   $routeAlias = 'bill-of-material';
} else {
   $routeAlias = 'quotation-bom';
}
@endphp
<form class="ajax-input-form" method="POST" action="{{ route('bill.of.material.update', $bom->id) }}" data-redirect="{{ route('bill.of.material.index') }}" enctype='multipart/form-data'>
    @csrf
    <div class="app-content content">
      <div class="content-overlay"></div>
      <div class="header-navbar-shadow"></div>
      <div class="content-wrapper container-xxl p-0">
         <div class="content-header pocreate-sticky">
            <div class="row">
               @include('layouts.partials.breadcrumb-add-edit', [
                'title' => $routeAlias == 'quotation-bom' ? 'Quotation BOM' : 'Production BOM',
                'menu' => 'Home', 
                'menu_url' => url('home'),
                'sub_menu' => 'Edit'
                ])
               <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                  <div class="form-group breadcrumb-right">
                     <input type="hidden" name="document_status" value="{{$bom->document_status}}" id="document_status">
                     <button type="button" onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
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
                           <div class="border-bottom mb-2 pb-25">
                              <div class="row">
                                   <div class="col-md-6">
                                       <div class="newheader "> 
                                           <h4 class="card-title text-theme">Basic Information</h4>
                                           <p class="card-text">Fill the details</p> 
                                       </div>
                                   </div>
                                   <div class="col-md-6 text-sm-end">
                                       <span class="badge rounded-pill badge-light-secondary forminnerstatus">
   
                                           Status : <span class="{{$docStatusClass}}">{{$bom->display_status}}</span>
                                       </span>
                                   </div>
                               </div>
                           </div>
                           <div class="row">
                              <div class="col-md-8">
                                 <div class="basic-information">
                                    <div class="row align-items-center mb-1">
                                       <div class="col-md-3"> 
                                           <label class="form-label">Series <span class="text-danger">*</span></label>  
                                       </div>  
   
                                       <div class="col-md-5">
                                          <input type="hidden" name="book_id" class="form-control" id="book_id" value="{{$bom->book_id}}" readonly> 
                                          <input readonly type="text" name="book_code" class="form-control" value="{{$bom->book_code}}" id="book_code">
                                       </div>
                                    </div>
   
                                   <div class="row align-items-center mb-1">
                                       <div class="col-md-3"> 
                                           <label class="form-label">BOM No <span class="text-danger">*</span></label>  
                                       </div>  
   
                                       <div class="col-md-5"> 
                                           <input readonly type="text" name="document_number" class="form-control" id="document_number" value="{{$bom->document_number}}">
                                       </div> 
                                    </div>
   
                                    <div class="row align-items-center mb-1">
                                       <div class="col-md-3">
                                           <label class="form-label">BOM Date <span class="text-danger">*</span></label>
                                       </div>
                                       <div class="col-md-5">
                                           <input type="date" class="form-control" value="{{ $bom->document_date }}" name="document_date">
                                       </div>
                                   </div>   
                                 </div>
                              </div>
                              @include('partials.approval-history', ['document_status' => $bom->document_status, 'revision_number' => $revision_number]) 
                           </div>
                        </div>
                     </div>
                  </div>
                       <div class="col-md-12" id="vendor_section">
                           <div class="card quation-card">
                               <div class="card-header newheader">
                                   <div>
                                       <h4 class="card-title">Product Details</h4> 
                                   </div>
                               </div>
                               <div class="card-body"> 
                                   <div class="row">
                                       <div class="col-md-3">
                                           <div class="mb-1">
                                               <label class="form-label">Product Code <span class="text-danger">*</span></label> 
                                               <input type="text" value="{{$bom->item?->item_code}}" placeholder="Select" class="form-control mw-100 ledgerselecct" id="item_code" name="item_code" data-name="{{$bom->item?->item_name ?? ''}}" data-code="{{$bom->item?->item_code ?? ''}}"/> 
                                           </div>
                                       </div> 
                                       <div class="col-md-3">
                                           <div class="mb-1">
                                               <label class="form-label">Product Name <span class="text-danger">*</span></label> 
                                               <input type="hidden" value="{{$bom->item?->id}}" name="item_id" id="head_item_id">
                                               <input type="text" value="{{$bom->item?->item_name}}" id="head_item_name" placeholder="Select" class="form-control mw-100 ledgerselecct" name="item_name" readonly />
                                           </div>
                                       </div> 
                                       <div class="col-md-3">
                                           <div class="mb-1">
                                               <label class="form-label">UOM <span class="text-danger">*</span></label>
                                               <input type="hidden" id="head_uom_id" class="form-control" name="uom_id" value="{{$bom->uom?->id}}" readonly  />
                                               <input type="text" id="head_uom_name" class="form-control" name="uom_name" value="{{$bom->uom?->name}}" readonly  />
                                           </div>
                                       </div>
                                       @include('billOfMaterial.partials.header-attribute-edit')
                                       @if($servicesBooks['services'][0]?->alias != ConstantHelper::BOM_SERVICE_ALIAS)
                                       <div class="col-md-3 customer_div">
                                           <div class="mb-1">
                                               <label class="form-label">Customer <span class="text-danger">*</span></label>
                                               <input type="hidden" name="customer_id" id="customer_id" value="{{$bom?->customer?->id}}">
                                               <input type="text" id="customer" placeholder="Select" value="{{$bom?->customer?->customer_code}}" class="form-control mw-100 ledgerselecct" name="customer" readonly/> 
                                           </div>
                                       </div>
                                       <div class="col-md-3">
                                           <div class="mb-1">
                                               <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                               <input type="text" id="customer_name" value="{{$bom?->customer?->company_name}}" placeholder="Select" class="form-control mw-100 ledgerselecct" name="customer_name" readonly />  
                                           </div>
                                       </div>
                                       @endif
                                       @if($servicesBooks['services'][0]?->alias == ConstantHelper::BOM_SERVICE_ALIAS)
                                       <div class="col-md-3 production_type_div">
                                           <div class="mb-1">
                                               <label class="form-label">Production Type <span class="text-danger">*</span></label>
                                               <select class="form-select" id="production_type" name="production_type">
                                                   @foreach($productionTypes as $productionType)
                                                   <option value="{{$productionType}}" {{$bom->production_type == $productionType ? 'selected' : ''}}>{{ucfirst($productionType)}}</option>
                                                   @endforeach 
                                               </select>   
                                           </div>
                                       </div>
                                       @endif
                                       <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label">Production Route <span class="text-danger">*</span></label>
                                                <select class="form-select" id="production_route_id" name="production_route_id">
                                                    <option value="">Select</option>
                                                    @foreach($productionRoutes as $productionRoute)
                                                        @if($bom->production_route_id)
                                                            <option value="{{$productionRoute->id}}" {{$bom->production_route_id == $productionRoute->id ? 'selected' :  ''}}>{{ucfirst($productionRoute->name)}}</option>
                                                        @endif
                                                    @endforeach 
                                                </select>   
                                            </div>
                                        </div>
                                        @if($servicesBooks['services'][0]?->alias == ConstantHelper::BOM_SERVICE_ALIAS)
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label">Customizable <span class="text-danger">*</span></label>
                                                <select class="form-select" id="customizable" name="customizable">
                                                    @foreach($customizables as $customizable)
                                                        <option value="{{$customizable}}" {{$customizable == $bom->customizable ? 'selected' : ''}}>{{ucfirst($customizable)}}</option>
                                                    @endforeach 
                                                 </select>   
                                            </div>
                                        </div>
                                        @endif
                                   </div>
                               </div>
                           </div>                                    
                       </div>  
                       <div class="col-md-12">
                           <div class="card">
                               <div class="card-body customernewsection-form">
                                <div class="border-bottom mb-2 pb-25" id="componentSection">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="newheader ">
                                                <ul class="nav nav-tabs" id="productTabs" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link active fs-5" id="raw-materials-tab" data-bs-toggle="tab" data-bs-target="#raw-materials" type="button" role="tab" aria-controls="raw-materials" aria-selected="true">
                                                            Consumption
                                                        </button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link fs-5" id="instruction-items-tab" data-bs-toggle="tab" data-bs-target="#instruction-items" type="button" role="tab" aria-controls="instruction-items" aria-selected="false">
                                                            Instruction
                                                        </button>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="tab-content mt-1" id="productTabsContent">
                                    <div class="tab-pane fade show active" id="raw-materials" role="tabpanel" aria-labelledby="raw-materials-tab">
                                        <div class="table-responsive pomrnheadtffotsticky">
                                            <table id="itemTable" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                <thead>
                                                    <tr>
                                                        <th>
                                                        <div class="form-check form-check-primary custom-checkbox">
                                                            <input type="checkbox" class="form-check-input" id="Email">
                                                            <label class="form-check-label" for="Email"></label>
                                                        </div>
                                                        </th>
                                                        <th style="width: 100px;" id="section_required">Section</th>
                                                        <th style="width: 100px;" id="sub_section_required">Sub Section</th>
                                                        <th style="min-width: 110px;">Item Code</th>
                                                        <th style="min-width: 150px;">Item Name</th>
                                                        <th>Attributes</th>
                                                        <th style="width: 30px;">UOM</th>
                                                        <th>Consumption</th>
                                                        <th class="{{$canView ? '' : 'd-none'}}">Cost</th>
                                                        <th class="{{$canView ? '' : 'd-none'}}">Item Value</th>
                                                        <th class="{{$canView ? '' : 'd-none'}}" id="component_overhead_required">Overheads</th>
                                                        <th class="{{$canView ? '' : 'd-none'}}">Total Cost</th>
                                                        <th style="min-width: 100px;" id="station_required">Station</th>
                                                        <th style="min-width: 100px;">Vendor</th>
                                                        <th style="width: 20px;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody class="mrntableselectexcel">
                                                    @include('billOfMaterial.partials.item-row-edit')
                                                </tbody>
                                                <tfoot>
                                                    <tr class="totalsubheadpodetail {{$canView ? '' : 'd-none'}}">
                                                        <td colspan="9"></td>
                                                        <td class="text-end" id="totalItemValue">{{number_format($bom->total_item_value,6)}}</td>
                                                        <td class="text-end" id="totalOverheadAmountValue">{{number_format($bom->item_overhead_amount,2)}}</td>
                                                        <td class="text-end" id="totalCostValue">{{number_format(($bom->total_item_value + $bom->item_overhead_amount),2)}}</td>
                                                        <td></td>
                                                        <td></td>
                                                    </tr>
                                                    <tr valign="top">
                                                        <td @if($canView) colspan="11" @else colspan="15" @endif rowspan="10">
                                                            <table class="table border" id="itemDetailTable">
                                                                <tr>
                                                                    <td class="p-0">
                                                                    <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                </tr>
                                                                <tr>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                        @if($canView)
                                                        <td colspan="4">
                                                            <table class="table border mrnsummarynewsty">
                                                                <tr>
                                                                    <td colspan="2" class="p-0">
                                                                    <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between">
                                                                        <strong>BOM Summary</strong>
                                                                        @if($canView)
                                                                        <div class="addmendisexpbtn">
                                                                            <button type="button" class="btn p-25 btn-sm btn-outline-secondary addOverHeadSummaryBtn"><i data-feather="plus"></i> Overhead</button> 
                                                                        </div>
                                                                        @endif
                                                                    </h6>
                                                                    </td>
                                                                </tr>
                                                                <tr class="voucher-tab-foot">
                                                                    <td class="text-primary"><strong>Item Total</strong></td>
                                                                    <td>
                                                                        <div class="justify-content-end text-end">
                                                                        <h5 id="footerTotalCost" amount="{{$bom->total_item_value  + $bom->item_overhead_amount + $bom->header_overhead_amount}}">{{number_format(($bom->total_item_value + $bom->item_overhead_amount + $bom->header_overhead_amount),2)}}</h5>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td><strong>Header Overheads</strong></td>
                                                                    <td class="text-end" id="footerOverheadHeader">0.00</td>
                                                                </tr>
                                                                <tr class="voucher-tab-foot">
                                                                    <td class="text-primary"><strong>Grand Total</strong></td>
                                                                    <td>
                                                                        <div class="quottotal-bg justify-content-end">
                                                                        <h5 id="footerGrandTotal">0.00</h5>
                                                                        </div>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                        @endif
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="instruction-items" role="tabpanel" aria-labelledby="product-details-tab">
                                        <div class="table-responsive pomrnheadtffotsticky">
                                        <table id="itemTable3" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                            <thead>
                                                <tr>
                                                    <th width="20px">
                                                        <div class="form-check form-check-primary custom-checkbox">
                                                            <input type="checkbox" class="form-check-input" id="Email">
                                                            <label class="form-check-label" for="Email"></label>
                                                        </div>
                                                    </th>
                                                    <th width="160px">Station</th>
                                                    @if(isset($sectionRequired) && $sectionRequired)
                                                        <th width="160px" id="section_required2">Section</th>
                                                    @endif
                                                    @if(isset($subSectionRequired) && $subSectionRequired)
                                                        <th width="160px" id="sub_section_required2">Sub Section</th>
                                                    @endif
                                                    <th>Instructions</th>
                                                    <th width="150px">Attachment</th>
                                                </tr>
                                            </thead>
                                            <tbody class="mrntableselectexcel">
                                                @include('billOfMaterial.partials.instruction-row-edit')
                                            </tbody>
                                            {{-- <tfoot>
                                                <tr valign="top">
                                                    <td colspan="10">
                                                    <table class="table border" id="itemDetailTable">
                                                        <tr>
                                                            <td class="p-0">
                                                                <h6 class="text-dark mb-0 bg-light-primary py-1 px-50"><strong>Item Details</strong></h6>
                                                            </td>
                                                        </tr>
                                                        <tr class="item_detail_row">
                                                            
                                                        </tr>
                                                        <tr class="item_detail_row">
                                                            
                                                        </tr>
                                                    </table>
                                                    </td>
                                                </tr>
                                            </tfoot> --}}
                                        </table>
                                        </div>
                                    </div>
                                </div>
                        </div>
                        </div>

                        <div class="card">
                            <div class="card-body customernewsection-form">
                                <div class="border-bottom mb-2 pb-25" id="componentSection">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="newheader ">
                                            <h4 class="card-title text-theme">Remarks</h4>
                                        </div>
                                    </div>
                                </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                         <div class="row">
                                            <div class="col-md-4">
                                          <div class="mb-1">
                                              <label class="form-label">Upload Document</label>
                                              <input type="file" name="attachment[]" class="form-control" onchange = "addFiles(this,'main_bom_preview')" multiple>
                                              <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                                          </div>
                                      </div>
                                      @include('partials.document-preview',['documents' => $bom->getDocuments(), 'document_status' => $bom->document_status,'elementKey' => 'main_bom_preview'])
                                         </div>
                                    </div>
                                    <div class="col-md-12">
                                       <div class="mb-1">  
                                          <label class="form-label">Final Remarks</label> 
                                          <textarea maxlength="250" name="remarks" type="text" rows="4" class="form-control" placeholder="Enter Remarks here...">{!! $bom->remarks !!}</textarea> 
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
</div>
</form>
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

@endsection
@section('scripts')
<script>
    var canView = {{ $canView ? 'true' : 'false' }};
</script>
<script type="text/javascript" src="{{asset('assets/js/modules/bom.js')}}"></script>
<script type="text/javascript">
@if($bom->document_status != 'draft')
$(':input').prop('readonly', true);
$('select').not('.amendmentselect select').prop('disabled', true);
$("#deleteBtn").remove();
$("#addNewItemBtn").remove();
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
$(function(){
    function getDocNumberByBookId(bookId) {
      let document_date = $("[name='document_date']").val();
      let actionUrl = '{{route("book.get.doc_no_and_parameters")}}'+'?book_id='+bookId+'&document_date='+document_date;
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                  if(data.data.doc.type == 'Manually') {
                     $("#document_number").attr('readonly', false);
                  } else {
                     $("#document_number").attr('readonly', true);
                  }
                  const parameters = data.data.parameters;
                  setServiceParameters(parameters);
                  setTableCalculation();
                }
                if(data.status == 404) {
                    $("#book_code").val('');
                    $("#document_number").val('');
                    const docDateInput = $("[name='document_date']");
                    docDateInput.removeAttr('min');
                    docDateInput.removeAttr('max');
                    // docDateInput.val(new Date().toISOString().split('T')[0]);
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
        //    docDateInput.val(futureDate.toISOString().split('T')[0]);
           docDateInput.attr("min", new Date().toISOString().split('T')[0]);
           isFeature = true;
       } else {
           isFeature = false;
           docDateInput.attr("max", new Date().toISOString().split('T')[0]);
       }
       if (parameters.back_date_allowed && parameters.back_date_allowed.includes('yes')) {
           let backDate = new Date();
           backDate.setDate(backDate.getDate() /*- (parameters.back_date_days || 1)*/);
        //    docDateInput.val(backDate.toISOString().split('T')[0]);
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
        // Cunsumption Method
        if(parameters.consumption_method.includes('manual')) {
            $("#consumption_method").val('manual');
            $(".consumption_btn").addClass('d-none');
        }
        if(parameters.consumption_method.includes('norms')) {
            $("#consumption_method").val('norms');
            $(".consumption_btn").removeClass('d-none');
       }

       if (parameters.station_required && !parameters.station_required.includes('yes')) {
           let thIndex = $("#station_required").index();
           $('tfoot .totalsubheadpodetail:eq(0)').find(`td:last-child`).remove();
           $("#station_required").remove();
       }

       if (parameters.section_required && !parameters.section_required.includes('yes')) {
           let td = $('tfoot .totalsubheadpodetail:eq(0)').find("td[colspan]");
           if (td.length > 0) {
                let colspanValue = parseInt(td.attr("colspan"));
                let newColspanValue = colspanValue - 1;
                td.attr("colspan", newColspanValue);
            }
           if($("#section_required").length) {
               $("#section_required").remove();
           }
           if($("#section_required2").length) {
               $("#section_required2").remove();
           }
           let td2 = $("tfoot").find("tr[valign]").find('td[rowspan]');
           if (td2.length > 0) {
                let colspanValue = parseInt(td2.attr("colspan"));
                if(colspanValue > 6) {
                    let newColspanValue = colspanValue - 1;
                    td2.attr("colspan", newColspanValue);
                }
            }
       }
       if (parameters.sub_section_required && !parameters.sub_section_required.includes('yes')) {
           let td = $('tfoot .totalsubheadpodetail:eq(0)').find("td[colspan]");
           if (td.length > 0) {
                let colspanValue = parseInt(td.attr("colspan"));
                let newColspanValue = colspanValue - 1;
                td.attr("colspan", newColspanValue);
            }
            if($("#sub_section_required").length) {
               $("#sub_section_required").remove();
           }
           if($("#sub_section_required2").length) {
               $("#sub_section_required2").remove();
           }
           let td2 = $("tfoot").find("tr[valign]").find('td[rowspan]');
           if (td2.length > 0) {
                let colspanValue = parseInt(td2.attr("colspan"));
                if(colspanValue > 6) {
                    let newColspanValue = colspanValue - 1;
                    td2.attr("colspan", newColspanValue);
                }
            }
       }
       if (parameters.component_overhead_required && !parameters.component_overhead_required.includes('yes')) {
           $("#component_overhead_required").remove();
           $("#totalOverheadAmountValue").remove();
           let td2 = $("tfoot").find("tr[valign]").find('td[rowspan]');
           if (td2.length > 0) {
                let colspanValue = parseInt(td2.attr("colspan"));
                if(colspanValue > 6) {
                    let newColspanValue = colspanValue - 1;
                    td2.attr("colspan", newColspanValue);
                }
            }
       }
        let reference_from_service = parameters.reference_from_service;
        if(reference_from_service.length) {
            let c_bom = '{{ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS}}';
            if(reference_from_service.includes(c_bom)) {
                $("#reference_from").removeClass('d-none');
            } else {
                $("#reference_from").addClass('d-none');
            }
            if(reference_from_service.includes('d')) {
                $("#addNewItemBtn").removeClass('d-none');
            } else {
                $("#addNewItemBtn").addClass('d-none');
            }
        }
   }
});

/*Display item detail*/
$(document).on('input change focus', '#itemTable tr input', (e) => {
   let currentTr = e.target.closest('tr'); 
   let pName = $(currentTr).find("[name*='component_item_name']").val();
   let itemId = $(currentTr).find("[name*='item_id']").val();
   let bomDetailId = $(currentTr).find("[name*='bom_detail_id']").val();
   if (itemId) {
      let selectedAttr = [];
      $(currentTr).find("[name*='attr_name']").each(function(index, item) {
         if($(item).val()) {
            selectedAttr.push($(item).val());
         }
      });

      let sectionName = $(currentTr).find("[name*='[section_name]']").val() || '';
      let subSectionName = $(currentTr).find("[name*='[sub_section_name]']").val() || '';
      let stationName = $(currentTr).find("[name*='[station_name]']").val() || '';
      
      let remark = '';
      if($(currentTr).find("[name*='remark']")) {
       remark = $(currentTr).find("[name*='remark']").val() || '';
      }

    let qty_per_unit = $(currentTr).find("[name*='[qty_per_unit]']").val() || '';
    let total_qty = $(currentTr).find("[name*='[total_qty]']").val() || '';
    let std_qty = $(currentTr).find("[name*='[std_qty]']").val() || '';
    let actionUrl = '{{route("bill.of.material.get.itemdetail")}}'+'?item_id='+itemId+'&selectedAttr='+JSON.stringify(selectedAttr)+'&remark='+remark+'&section_name='+sectionName+'&sub_section_name='+subSectionName+'&station_name='+stationName+'&qty_per_unit='+qty_per_unit+'&total_qty='+total_qty+'&std_qty='+std_qty+'&bom_detail_id='+bomDetailId;
      fetch(actionUrl).then(response => {
         return response.json().then(data => {
            if(data.status == 200) {
               $(".item_detail_row").remove();
               if($("#itemDetailTable tbody tr").length > 2) {
                  $("#itemDetailTable tbody tr").slice(-2).remove();
               }
               $("#itemDetailTable tbody tr:first").after(data.data.html);
            }
         });
      });
   }
});
</script>
@endsection