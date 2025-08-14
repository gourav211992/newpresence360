@extends('layouts.app')
@section('content')
<form class="ajax-input-form" action="{{ route('pi.update', $pi->id) }}" method="POST" data-redirect="/purchase-indent" enctype="multipart/form-data">
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
                                <h2 class="content-header-title float-start mb-0">Purchase Indent</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">Edit</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
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

                                                Status : <span class="{{$docStatusClass}}">{{$pi->display_status}}</span>
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
                                                      <option value="{{$book->id}}" {{$book->id == $pi->book_id ? 'selected' : ''}}>{{ucfirst($book->book_code)}}</option>
                                                    @endforeach
                                                    </select>
                                                    <input type="hidden" name="book_code" id="{{$pi->book->book_code}}" id="book_code">
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Indent No <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="document_number" id="document_number" value="{{$pi->document_number}}" class="form-control">
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Indent Date <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="date" class="form-control" value="{{ $pi->document_date }}" name="document_date">
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3"> 
                                                    <label class="form-label">Department <span class="text-danger">*</span></label>  
                                                </div>  
                                                <div class="col-md-5">  
                                                  <input type="hidden" value="{{$pi->department_id}}" name="department_id">
                                                    <select class="form-select" id="department_id" disabled name="department_id">
                                                        <option value="">Select</option>
                                                      @foreach($departments as $department)
                                                      <option value="{{$department->id}}" {{$pi->department_id == $department->id ? 'selected' : ''}}>{{ucfirst($department->name)}}</option>
                                                      @endforeach 
                                                  </select>  
                                              </div>
                                          </div>

                                        </div>
                                        
                                        {{-- Approval History Section --}}
                                        @include('partials.approval-history', ['document_status' => $pi->document_status, 'revision_number' => $revision_number]) 
                                    </div>
                                </div>
                            </div>

                           <div class="card" id="item_section">
                           <div class="card-body customernewsection-form"> 
                            <div class="border-bottom mb-2 pb-25">
                               <div class="row">
                                <div class="col-md-6">
                                    <div class="newheader ">
                                        <h4 class="card-title text-theme">Indent Item Wise Detail</h4>
                                        <p class="card-text">Fill the details</p>
                                    </div>
                                </div>
                                <div class="col-md-6 text-sm-end">
                                    <a href="javascript:;" id="deleteBtn" class="btn btn-sm btn-outline-danger me-50">
                                        <i data-feather="x-circle"></i> Delete</a>
                                        <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary">
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
                                                    <th width="200px">Item Code</th>
                                                    <th width="300px">Item Name</th>
                                                    <th max-width="180px">Attributes</th>
                                                    <th >UOM</th>
                                                    <th class="text-end">Quantity</th>
                                                    <th width="240px">Vendor Name</th>
                                                    <th width="100px" id="so_no">SO No.</th>
                                                    <th width="350px">Remarks</th>
                                                </tr>
                                        </thead>
                                        <tbody class="mrntableselectexcel">
                                            @include('procurement.pi.partials.item-row-edit')
                                        </tbody>
                                        <tfoot>
                                        <tr valign="top">
                                            <td colspan="9" rowspan="10">
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
                                                            <span class="badge rounded-pill badge-light-secondary text-wrap"><strong>Remarks</strong>: </span>
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
                                    <textarea maxlength="250" type="text" rows="4" name="remarks" class="form-control" placeholder="Enter Remarks here...">{!! $pi->remarks !!}</textarea>

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

@endsection
@section('scripts')
<script type="text/javascript" src="{{asset('assets/js/modules/common-attr-ui.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/modules/pi.js')}}"></script>
<script>

@if($pi->document_status != 'draft')
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
    let piItemId = $(tr).find('[name*="[pi_item_id]"]').length ? $(tr).find('[name*="[pi_item_id]"]').val() : '';
    let isSo = $(tr).find('[name*="so_item_id"]').length ? 1 : 0;
    if(!isSo) {
        isSo = $(tr).find('[name*="so_pi_mapping_item_id"]').length ? 1 : 0;
    }
    let actionUrl = '{{route("pi.item.attr")}}'+'?item_id='+itemId+`&rowCount=${rowCount}&selectedAttr=${selectedAttr}&pi_item_id=${piItemId}&isSo=${isSo}`;
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

      let uomId = $(currentTr).find("[name*='[uom_id]']").val() || '';
      let qty = $(currentTr).find("[name*='[qty]']").val() || '';
      let actionUrl = '{{route("pi.get.itemdetail")}}'+'?item_id='+itemId+'&selectedAttr='+JSON.stringify(selectedAttr)+'&remark='+remark+'&uom_id='+uomId+'&qty='+qty;
      fetch(actionUrl).then(response => {
         return response.json().then(data => {
            if(data.status == 200) {
               $("#itemDetailDisplay").html(data.data.html);
            }
         });
      });
   }
});
setTimeout(() => {
    $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
        let currentIndex = index + 1;
        setAttributesUIHelper(currentIndex,"#itemTable");
    });
},100);
</script>
@endsection
