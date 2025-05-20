@extends('layouts.app')
@section('content')
<form class="ajax-input-form" method="POST" action="{{ route('mo.update', $bom->id) }}" data-redirect="{{ route('mo.index') }}" enctype='multipart/form-data'>
    @csrf
    <div class="app-content content ">
      <div class="content-overlay"></div>
      <div class="header-navbar-shadow"></div>
      <div class="content-wrapper container-xxl p-0">
         <div class="content-header pocreate-sticky">
            <div class="row">
               @include('layouts.partials.breadcrumb-add-edit', [
                'title' => 'Production Work Orders',
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
                                           <label class="form-label">Document No. <span class="text-danger">*</span></label>  
                                       </div>  
   
                                       <div class="col-md-5"> 
                                           <input readonly type="text" name="document_number" class="form-control" id="document_number" value="{{$bom->document_number}}">
                                       </div> 
                                    </div>
   
                                    <div class="row align-items-center mb-1">
                                       <div class="col-md-3">
                                           <label class="form-label">Document Date <span class="text-danger">*</span></label>
                                       </div>
                                       <div class="col-md-5">
                                           <input type="date" class="form-control" value="{{ $bom->document_date }}" name="document_date">
                                       </div>
                                   </div>
                                   {{-- <div class="row align-items-center mb-1 d-none" id="reference_from"> 
                                       <div class="col-md-3"> 
                                           <label class="form-label">Reference from</label>  
                                       </div> 
                                       <div class="col-md-5 action-button"> 
                                           <button type="button" class="btn btn-outline-primary btn-sm mb-0 prSelect" {{$bom->moItems->count() ? 'disabled' : ''}}><i data-feather="plus-square"></i> Production Work Order</button>
                                       </div>
                                   </div>      --}}
                                 </div>
                              </div>
                              {{-- Approval History Section --}}
                              @include('partials.approval-history', ['document_status' => $bom->document_status, 'revision_number' => $revision_number]) 
    
                           </div>
                        </div>
                     </div>
   
                     {{-- Product Detail --}}
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
                                       
                                       @include('mfgOrder.partials.header-attribute-edit')
                                       <div class="col-md-3 quantity">
                                        <div class="mb-1">
                                            <label class="form-label">Quantity <span class="text-danger">*</span></label> 
                                            <input type="number" class="form-control mw-100" id="quantity" value="{{$bom->qty_produced}}" name="quantity" />
                                        </div>
                                    </div>  
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
                                       <h4 class="card-title text-theme">Item Details</h4>
                                       <p class="card-text">Fill the details</p>
                                    </div>
                                 </div>
                                 {{-- <div class="col-md-6 text-sm-end">
                                    <a href="javascript:;" class="btn btn-sm btn-outline-danger me-50" id="deleteBtn">
                                    <i data-feather="x-circle"></i> Delete</a>
                                    <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary">
                                    <i data-feather="plus"></i> Add Component</a>
                                 </div> --}}
                              </div>
                           </div>
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
                                       <th width="125px" id="section_required">Section</th>
                                       <th width="125px" id="sub_section_required">Sub Section</th>
                                       <th width="150px">Item Code</th>
                                       <th>Attributes</th>
                                       <th width="50px">UOM</th>
                                       <th>Consumption</th>
                                       <th>Cost</th>
                                       <th>Item Value</th>
                                       <th class="component_waste_required">Waste%</th>
                                       <th class="component_waste_required">Waste Amt</th>
                                       <th id="component_overhead_required">Overheads</th>
                                       <th>Total Cost</th>
                                       <th width="125px" id="station_required">Station</th>
                                       <th></th>
                                    </tr>
                                 </thead>
                                 <tbody class="mrntableselectexcel">
                                    @include('mfgOrder.partials.item-row-edit')
                                 </tbody>
                                 <tfoot>
                                    <tr class="totalsubheadpodetail">
                                        <td colspan="8"></td>
                                        <td class="text-end" id="totalItemValue">{{number_format($bom->total_item_value,6)}}</td>
                                        <td class="text-end" id="totalWastePercentage"></td>
                                        <td class="text-end" id="totalWasteAmtValue">{{number_format($bom->item_waste_amount,2)}}</td>
                                        <td class="text-end" id="totalOverheadAmountValue">{{number_format($bom->item_overhead_amount,2)}}</td>
                                        <td class="text-end" id="totalCostValue">{{number_format(($bom->total_item_value + $bom->item_waste_amount + $bom->item_overhead_amount),2)}}</td>
                                        <td></td>
                                        <td></td>
                                     </tr>
                                    <tr valign="top">
                                       <td colspan="16" rowspan="10">
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
                                       {{-- <td colspan="4">
                                          <table class="table border mrnsummarynewsty">
                                             <tr>
                                                <td colspan="2" class="p-0">
                                                   <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between">
                                                      <strong>BOM Summary</strong>
                                                      <div class="addmendisexpbtn">
                                                         <button type="button" class="btn p-25 btn-sm btn-outline-secondary addOverHeadSummaryBtn"><i data-feather="plus"></i> Overhead</button> 
                                                         <button type="button" class="btn p-25 btn-sm btn-outline-secondary wasteSummaryBtn" style="font-size: 10px"><i data-feather="plus"></i> Wastage</button>
                                                      </div>
                                                   </h6>
                                                </td>
                                             </tr>
                                             <tr class="totalsubheadpodetail">
                                                <td width="55%"><strong>Total Item Cost</strong></td>
                                                <td class="text-end" amount="{{$bom->total_item_value}}" id="footerSubTotal">{{number_format($bom->total_item_value)}}</td>
                                             </tr>
                                             <tr>
                                                <td><strong>Overheads</strong></td>
                                                <td class="text-end" amount="{{$bom->item_overhead_amount + $bom->header_overhead_amount}}" id="footerOverhead">{{number_format(($bom->item_overhead_amount + $bom->header_overhead_amount),2)}}</td>
                                             </tr>
                                             <tr>
                                                <td><strong>Wastage</strong></td>
                                                <td class="text-end" amount="{{$bom->item_waste_amount + $bom->header_waste_amount}}" id="footerWasteAmount">{{number_format(($bom->item_waste_amount + $bom->header_waste_amount),2)}}</td>
                                             </tr>
                                             <tr class="voucher-tab-foot">
                                                <td class="text-primary"><strong>Total Cost</strong></td>
                                                <td>
                                                   <div class="quottotal-bg justify-content-end">
                                                      <h5 id="footerTotalCost" amount="{{$bom->total_item_value + $bom->item_waste_amount + $bom->header_waste_amount + $bom->item_overhead_amount + $bom->header_overhead_amount}}">{{number_format(($bom->total_item_value + $bom->item_waste_amount + $bom->header_waste_amount + $bom->item_overhead_amount + $bom->header_overhead_amount),2)}}</h5>
                                                   </div>
                                                </td>
                                             </tr>
                                          </table>
                                       </td> --}}
                                    </tr>
                                 </tfoot>
                              </table>
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
               <!-- Modal to add new record -->
            </section>
         </div>
      </div>
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
            <button type="button" {{-- data-bs-dismiss="modal" --}} class="btn btn-primary submit_attribute">Select</button>
         </div>
      </div>
   </div>
</div>

{{-- Approval Modal --}}
@include('mfgOrder.partials.approve-modal', ['id' => $bom->id])

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
              <p>Are you sure you want to <strong>Amendment</strong> this <strong>BOM</strong>? After Amendment this action cannot be undone.</p>
              <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
              <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
          </div> 
      </div>
  </div>
</div>
@endsection
@section('scripts')
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
   /*Bind button value*/
   initializeAutocomplete2(".comp_item_code");
   // For product code
    function initializeAutocomplete1(selector, type) {
            $(selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type:'header_item'
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: `${item.item_name} (${item.item_code})`,
                                    code: item.item_code || '', 
                                    item_id: item.id
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
                    var itemCode = ui.item.code;
                    var itemName = ui.item.value;
                    var itemId = ui.item.item_id;
                    $input.attr('data-name', itemName);
                    $input.attr('data-code', itemCode);
                    $input.val(itemCode);
                    itemCodeChange(itemId);
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
    initializeAutocomplete1("#item_code");

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
                //   $("#document_number").val(data.data.doc.document_number);
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
//    setTimeout(() => {
//        let bookId = $("#book_id").val();
//        getDocNumberByBookId(bookId);
//    },0);
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

        let reference_from_service = parameters.reference_from_service;
        if(reference_from_service.length) {
            let c_bom = '{{\App\Helpers\ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS}}';
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
        } else {
            // Swal.fire({
            //     title: 'Error!',
            //     text: "Please update first reference from service param.",
            //     icon: 'error',
            // });
            // setTimeout(() => {
            //     // location.href = "{{route('mo.index')}}";
            // },1500);
        }
   }

   let bomParams = @json($bomParams);
   if(bomParams) {
        setServiceParameters(bomParams.data.parameters);
   }

    function itemCodeChange(itemId){
        let actionUrl = '{{route("mo.item.code")}}'+'?item_id='+itemId;
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                  let item_name = data.data.item.item_name; 
                  let item_id = data.data.item.id; 
                  let uom_id = data.data.item.uom_id; 
                  let uom_name = data.data.item.uom.name;
                  $("#head_item_name").val(item_name);
                  $("#head_item_id").val(item_id);
                  $("#head_uom_id").val(uom_id);
                  $("#head_uom_name").val(uom_name);
                  $(".heaer_item").remove();
                  $("#componentSection").before(data.data.html);
                  // $("#head_uom_name").closest('.row').after(data.data.html);
                }
            });
        });
    }
});

/*Add New Row*/
// for component item code
function initializeAutocomplete2(selector, type) {
   $(selector).autocomplete({
       source: function(request, response) {
         let headItemId = $("#head_item_id").val();
         let selectedAllItemIds = [];
         if(Number(headItemId)) {
            selectedAllItemIds.push(headItemId);
         }
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
                   type:'comp_item',
                   selectedAllItemIds : JSON.stringify(selectedAllItemIds)
               },
               success: function(data) {
                   response($.map(data, function(item) {
                       return {
                           id: item.id,
                           label: `${item.item_name} (${item.item_code})`,
                           code: item.item_code || '', 
                           item_id: item.id,
                           uom_name:item.uom?.name,
                           uom_id:item.uom_id,
                           is_attr:item.item_attributes_count,
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
           let itemId = ui.item.item_id;
           let uomId = ui.item.uom_id;
           let uomName = ui.item.uom_name;
           $input.attr('data-name', itemName);
           $input.attr('data-code', itemCode);
           $input.attr('data-id', itemId);
           $input.val(itemCode);
           $input.closest('tr').find('[name*=item_id]').val(itemId);
           $input.closest('tr').find('[name*=item_code]').val(itemCode);
           let uomOption = `<option value=${uomId}>${uomName}</option>`;
           $input.closest('tr').find('[name*=uom_id]').append(uomOption);
           setTimeout(() => {
                if(ui.item.is_attr) {
                    $input.closest('tr').find('.attributeBtn').trigger('click');
                } else {
                    $input.closest('tr').find('.addSectionItemBtn').trigger('click');
                    // $input.closest('tr').find('[name*="[qty]"]').focus();
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
    let actionUrl = '{{route("mo.item.attr")}}'+'?item_id='+itemId+`&rowCount=${rowCount}&selectedAttr=${selectedAttr}`;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                  $("#attribute tbody").empty();
                  $("#attribute table tbody").append(data.data.html);
                  $(tr).find('td:nth-child(6)').find("[name*='[attr_name]']").remove();
                  $(tr).find('td:nth-child(6)').append(data.data.hiddenHtml)
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
   let pName = $(currentTr).find("[name*='component_item_name']").val();
   let itemId = $(currentTr).find("[name*='item_id']").val();
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

            // Norms
    let qty_per_unit = $(currentTr).find("[name*='[qty_per_unit]']").val() || '';
    let total_qty = $(currentTr).find("[name*='[total_qty]']").val() || '';
    let std_qty = $(currentTr).find("[name*='[std_qty]']").val() || '';

      let actionUrl = '{{route("mo.get.itemdetail")}}'+'?item_id='+itemId+'&selectedAttr='+JSON.stringify(selectedAttr)+'&remark='+remark+'&section_name='+sectionName+'&sub_section_name='+subSectionName+'&station_name='+stationName+'&qty_per_unit='+qty_per_unit+'&total_qty='+total_qty+'&std_qty='+std_qty;
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

setTimeout(() => {
   initializeStationAutocomplete();
   initializeProductSectionAutocomplete();

   $("[name*='[section_id]']").each(function(index, item){
      subSection($(item).val(), item);
   });

},10);
// Function to initialize the product section autocomplete
function initializeProductSectionAutocomplete() {
    $("[name*='product_section']").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type: 'product_section'
                },
                success: function (data) {
                    const mappedData = $.map(data, function (item) {
                        return {
                            id: item.id,
                            label: item.name,
                        };
                    });
                    response(mappedData);

                },
                error: function (xhr) {
                    console.error('Error fetching data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        select: function (event, ui) {
            $(this).val(ui.item.label);
            $(this).closest('td').find("[name*='[section_id]']").val(ui.item.id);
            $(this).closest('td').find("[name*='[section_name]']").val(ui.item.label);
            subSection(ui.item.id, this);
            return false;
        },
        change: function (event, ui) {
            if (!ui.item) {
               $(this).val("");
               $(this).closest('td').find("[name*='[section_id]']").val("");
               $(this).closest('td').find("[name*='[section_name]']").val("");
               $(this).closest('tr').find("[name*='[sub_section_id]']").val('');
               $(this).closest('tr').find("[name*='[sub_section_name]']").val('');
            }
        },
        // appendTo: "#addSectionItemPopup"
    }).focus(function () {
        if (this.value === "") {
            $(this).val("");
            $(this).closest('td').find("[name*='[section_id]']").val("");
            $(this).closest('td').find("[name*='[section_name]']").val("");
            $(this).closest('tr').find("[name*='[sub_section_id]']").val('');
            $(this).closest('tr').find("[name*='[sub_section_name]']").val('');
            $(this).autocomplete("search", "");
        }
    });
}

// Function to initialize sub-section autocomplete
function subSection(id, thisObj) {
    $(thisObj).closest('tr').find("[name='product_sub_section']").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type: 'product_sub_section',
                    id: id
                },
                success: function (data) {
                  const mappedData = $.map(data, function (item) {
                        return {
                            id: item.id,
                            label: item.name,
                        };
                    });
                    response(mappedData);
                },
                error: function (xhr) {
                    console.error('Error fetching data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        select: function (event, ui) {
            $(this).val(ui.item.label);
            $(this).closest('td').find("[name*='[sub_section_id]']").val(ui.item.id);
            $(this).closest('td').find("[name*='[sub_section_name]']").val(ui.item.label);
            return false;
        },
        change: function (event, ui) {
            if (!ui.item) {
                $(this).val("");
                $(this).closest('td').find("[name*='[sub_section_id]']").val("");
                $(this).closest('td').find("[name*='[sub_section_name]']").val("");
            }
        },
        // appendTo: "#addSectionItemPopup"
    }).focus(function () {
        if (this.value === "") {
            $(this).val("");
            $(this).closest('td').find("[name*='[sub_section_id]']").val("");
            $(this).closest('td').find("[name*='[sub_section_name]']").val("");
            $(this).autocomplete("search", "");
        }
    });
}
// Function to initialize the product section autocomplete
function initializeStationAutocomplete() {
    $("[name*='product_station']").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type: 'station'
                },
                success: function (data) {
                    const mappedData = $.map(data, function (item) {
                        return {
                            id: item.id,
                            label: item.name,
                        };
                    });
                    response(mappedData);

                },
                error: function (xhr) {
                    console.error('Error fetching data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        select: function (event, ui) {
            $(this).val(ui.item.label);
            $(this).closest('td').find("[name*='[station_id]']").val(ui.item.id);
            $(this).closest('td').find("[name*='[station_name]']").val(ui.item.label);
            return false;
        },
        change: function (event, ui) {
            if (!ui.item) {
               $(this).val("");
               $(this).closest('td').find("[name*='[station_id]']").val("");
               $(this).closest('td').find("[name*='[station_name]']").val("");
            }
        },
        // appendTo: "#addSectionItemPopup"
    }).focus(function () {
        if (this.value === "") {
            $(this).closest('td').find("[name*='[station_id]']").val("");
            $(this).closest('td').find("[name*='[station_name]']").val("");
            $(this).autocomplete("search", "");
        }
    });
}

/*Get Item Cost*/
function getBomItemCost(itemId,itemAttributes)
{
   let actionUrl = '{{route("mo.get.item.cost")}}'+'?item_id='+itemId+'&itemAttributes='+JSON.stringify(itemAttributes);
   fetch(actionUrl).then(response => {
      return response.json().then(data => {
         if (data.status == 200) {
            if(data.data.cost) {
               $("tr.trselected").find("[name*='[item_cost]']").val((data.data.cost).toFixed(2));
               $("tr.trselected .linkAppend").removeClass('d-none');
               $("tr.trselected .linkAppend a").attr('href', data.data.route);
            } else {
               $("tr.trselected .linkAppend").addClass('d-none');
               $("tr.trselected").find("[name*='[item_cost]']").val(''); 
            }
         } else {
            $("tr.trselected .linkAppend").addClass('d-none');
            $("tr.trselected").find("[name*='[item_cost]']").val('');  
         }
         $("#attribute").modal("hide");
      });
   });
}

$(document).on('click', '.submit_attribute', (e) => {
   let itemId = $("#attribute tbody tr").find('[name*="[item_id]"]').val();
   let itemAttributes = [];
   $("#attribute tbody tr").each(function(index, item) {
      let attr_id = $(item).find('[name*="[attribute_id]"]').val();
      let attr_value = $(item).find('[name*="[attribute_value]"]').val();
      itemAttributes.push({
            'attr_id': attr_id,
            'attr_value': attr_value
        });
   });
   getBomItemCost(itemId,itemAttributes);
});
</script>
@endsection