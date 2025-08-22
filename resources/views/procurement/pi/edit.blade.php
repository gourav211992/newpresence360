@extends('layouts.app')
@section('styles')
<style>
#soModal .table-responsive {
    overflow-y: auto;
    max-height: 300px; /* Set the height of the scrollable body */
    position: relative;
}

#soModal .po-order-detail {
    width: 100%;
    border-collapse: collapse;
}

#soModal .po-order-detail thead {
    position: sticky;
    top: 0; /* Stick the header to the top of the table container */
    background-color: white; /* Optional: Make sure header has a background */
    z-index: 1; /* Ensure the header stays above the body content */
}
#soModal .po-order-detail th {
    background-color: #f8f9fa; /* Optional: Background for the header */
    text-align: left;
    padding: 8px;
}

#soModal .po-order-detail td {
    padding: 8px;
}

</style>
@endsection
@section('content')
@if($buttons['approve'])
    <form id="piEditForm" data-module="pi" class="ajax-input-form" action="{{ route('pi.update.approve', $pi->id) }}" method="POST" data-redirect="/purchase-indent" enctype="multipart/form-data">
@else
    <form id="piEditForm" data-module="pi" class="ajax-input-form" action="{{ route('pi.update', $pi->id) }}" method="POST" data-redirect="/purchase-indent" enctype="multipart/form-data">
@endif
@csrf
<input type="hidden" name="procurement_type_param" id="procurement_type_param" value="all">
<input type="hidden" name="procurement_type" id="procurement_type" value="rm">
<input type="hidden" name="so_item_ids" id="so_item_ids">
<input type="hidden" name="item_ids" id="item_ids">
<input type="hidden" name="requester_type" id="requester_type" value="{{$pi->requester_type}}">
<input type="hidden" name="show_attribute" value="0" id="show_attribute">
<input type="hidden" name="so_tracking_required" value="{{$pi->so_tracking_required}}" id="so_tracking_required">
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
                    <input type="hidden" name="document_status" value="{{$pi->document_status}}" id="document_status">
                        <button type="button" onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
                        @if($buttons['draft'])
                            <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" name="action" value="draft"><i data-feather='save'></i> Save as Draft</button>
                        @endif
                        @if(!intval(request('amendment') ?? 0) && $pi->document_status != \App\Helpers\ConstantHelper::DRAFT && $pi->document_status != \App\Helpers\ConstantHelper::SUBMITTED)
                        <a href="{{ route('pi.generate-pdf', $pi->id) }}" target="_blank" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer"><polyline points="6 9 6 2 18 2 18 9"></polyline>
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                            <rect x="6" y="14" width="12" height="8"></rect></svg> Print
                        </a>
                        @endif

                        @if($buttons['submit'])
                            <button type="submit" class="btn btn-primary btn-sm submit-button" name="action" value="submitted"><i data-feather="check-circle"></i> Submit</button>
                        @endif
                        @if($buttons['approve'])
                            <button type="button" id="reject-button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg> Reject</button>
                            <button type="button" class="btn btn-primary btn-sm" id="approved-button" name="action" value="approved"><i data-feather="check-circle"></i> Approve</button>
                        @endif
                        @if($buttons['amend'] && intval(request('amendment') ?? 0))
                            <button type="button" class="btn btn-primary btn-sm" id="amendmentBtn"><i data-feather="check-circle"></i> Submit</button>
                        @else
                            @if($buttons['amend'])
                                <button type="button" data-bs-toggle="modal" data-bs-target="#amendmentconfirm" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i> Amendment</button>
                            @endif
                        @endif

                        @if($buttons['revoke'])
                            <button id = "revokeButton" type="button" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='rotate-ccw'></i> Revoke</button>
                        @endif
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
                                                <input type="hidden" name="book_id" id="book_id" value="{{$pi->book_id}}" />
                                                <select disabled class="form-select" id="book_id" name="book_id">
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
                                                <input readonly type="text" name="document_number" id="document_number" value="{{$pi->document_number}}" class="form-control">
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
                                                <label class="form-label">Location <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-md-5">
                                                <select class="form-select" disabled id="store_id" name="store_id">
                                                @foreach($locations as $location)
                                                <option value="{{$location->id}}" {{$location->id == $pi?->store_id ? 'selected' : ''}}>{{ $location?->store_name }}</option>
                                                @endforeach
                                            </select>
                                            </div>
                                        </div>
                                        <div class="row align-items-center mb-1 d-none" id = "department_id_header">
                                            <div class="col-md-3">
                                                <label class="form-label">Requester</label>
                                            </div>
                                            <div class="col-md-5">
                                                <select class="form-select" disabled id="sub_store_id" name="sub_store_id">
                                                    <option value="{{$pi?->sub_store_id}}">{{$pi?->sub_store?->name ?? $pi?->requester?->name}}</option>
                                                </select>
                                            </div>
                                        </div>
                                        @if ($pi->requester_type === "User")
                                        <div class="row align-items-center mb-1" id = "user_id_header">
                                            <div class="col-md-3">
                                                <label class="form-label">Requester <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-md-5">
                                                <select disabled class="form-select" id="user_id" name="user_id">
                                                    <option value="">Select</option>
                                                    @foreach($users as $user)
                                                    <option value="{{$user->id}}" {{$selecteduserId == $user->id ? 'selected' : ''}}>{{ucfirst($user->name)}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        @endif
                                        {{-- <div class="row align-items-center mb-1" id="department_id_header">
                                            <div class="col-md-3">
                                                <label class="form-label">Department <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-md-5">
                                                <select class="form-select" id="department_id" name="department_id">
                                                    <option value="">Select</option>
                                                    @foreach($departments as $department)
                                                    <option value="{{$department->id}}" {{$pi->department_id == $department->id ? 'selected' : ''}}>{{ucfirst($department->name)}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div> --}}
                                        <div class="row align-items-center mb-1 d-none" id="reference_from">
                                            <div class="col-md-3">
                                                <label class="form-label">Reference from</label>
                                            </div>
                                            <div class="col-md-5 action-button">
                                                <button type="button" @if(!$isEdit) disabled @endif class="btn btn-outline-primary btn-sm mb-0 soSelect"><i data-feather="plus-square"></i> Sale Order</button>
                                            </div>
                                        </div>
                                        @if($saleOrders?->count())
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-3">
                                                <label class="form-label">Sales Order</label>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" readonly class="form-control" value="{{ $saleOrders->map(fn($saleOrder) => strtoupper($saleOrder->book_code) . ' - ' . $saleOrder->document_number)->join(', ') }}">
                                            </div>
                                        </div>
                                        @endif
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
                                        <a href="#" onclick = "copyItemRow();" id = "copy_item_section"
                                        style = "{{ isset($pi->pi_items) && count($pi->pi_items) ? '' : 'display:none;' }}" class="btn btn-sm btn-outline-primary">
                                                        <i data-feather="copy"></i> Copy Item</a>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
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
                                            <th width="200px">Item Code</th>
                                            <th width="300px">Item Name</th>
                                            <th max-width="180px">Attributes</th>
                                            <th >UOM</th>
                                            <th class="text-end">Req Qty</th>
                                            <th class="text-end">Avl Stock</th>
                                            <th class="text-end">Pending PO</th>
                                            <th class="text-end">Adj Qty</th>
                                            <th class="text-end">Order Qty</th>
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
                                        <td colspan="13" rowspan="10">
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
                                            </table>
                                        </td>
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
                                        <input type="file" name="attachment[]" class="form-control" onchange = "addFiles(this,'main_pi_preview')" multiple>
                                        <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                                    </div>
                                </div>
                                @include('partials.document-preview',['documents' => $pi->getDocuments(), 'document_status' => $pi->document_status,'elementKey' => 'main_pi_preview'])
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
@include('procurement.pi.partials.amendment-modal', ['id' => $pi->id])
@include('procurement.pi.partials.approve-modal', ['id' => $pi->id])
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
              <p>Are you sure you want to <strong>Amendment</strong> this <strong>PI</strong>? After Amendment this action cannot be undone.</p>
              <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
              <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
          </div>
      </div>
  </div>
</div>


@include('procurement.pi.partials.so-modal')
@include('procurement.pi.partials.so-modal-submit')
@endsection
@section('scripts')
<script type="text/javascript" src="{{asset('assets/js/modules/common-attr-ui.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/modules/pi.js')}}"></script>
<script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
<script>
setTimeout(() => {
    localStorage.removeItem('deletedPiItemIds');
},0);
@if($buttons['amend'] && intval(request('amendment') ?? 0))

@else
    @if($pi->document_status != 'draft' && $pi->document_status != 'rejected')
    $(':input').prop('readonly', true);
    $('[name="attachment[]"]').not('#approveModal [name="attachment[]"]').prop('disabled', true);
    $('input[autocomplete], .ui-autocomplete-input').prop('disabled', true);
    $('select').not('.amendmentselect select').prop('disabled', true);
    @if($buttons['approve'])
        $("#itemTable").find('[name*="adj_qty"]').prop('readonly', false);
        $("#itemTable").find('[name*="adj_qty"]').prop('disabled', false);
        $('input[autocomplete], .ui-autocomplete-input').prop('disabled', false);
        $('input[autocomplete], .ui-autocomplete-input').prop('readonly', false);
    @endif
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
@endif

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
        //   if(!data.data.doc.document_number) {
        //      $("#document_number").val('');
        //  }
        //  $("#document_number").val(data.data.doc.document_number);
         if(data.data.doc.type == 'Manually') {
             $("#document_number").attr('readonly', false);
         } else {
             $("#document_number").attr('readonly', true);
         }
         const parameters = data.data.parameters;
        setServiceParameters(parameters);
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

    /*Reference from*/
    let reference_from_service = parameters.reference_from_service;
    $("#procurement_type_param").val(parameters.procurement_type);
    if(parameters.procurement_type.includes('all')) {
        $("#procurement_type").val('rm');
    }
    if(parameters.procurement_type.includes('Make to order')) {
        $("#procurement_type").val('rm');
    }
    if(parameters.procurement_type.includes('Buy to order')) {
        $("#procurement_type").val('fg');
    }
    if(reference_from_service.length) {
        let pi = '{{\App\Helpers\ConstantHelper::SO_SERVICE_ALIAS}}';
        if(reference_from_service.includes(pi)) {
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
            location.href = '{{route("pi.index")}}';
        },1500);
    }

    setTimeout(() => {
        @if($pi->pi_items->count())
            $("#reference_from").addClass('d-none');
        @endif
    },100);

    let requesterType = parameters?.requester_type || '';
    if (requesterType.includes('Department')) {
        $("#user_id_header").addClass('d-none');
        $("#department_id_header").removeClass('d-none');
        $("#requester_type").val('Department');
    } else {
        $("#user_id_header").removeClass('d-none');
        $("#department_id_header").addClass('d-none');
        $("#requester_type").val('User');
    }
    let soTrackingRequired = parameters?.so_tracking_required || '';
    $("#so_tracking_required").val(soTrackingRequired);
    if(soTrackingRequired.includes('yes')) {
        $("#soTrackingText").removeClass('d-none');
        $("#soTrackingNo").removeClass('d-none');
        $("#so_no").removeClass('d-none');
    } else {
        $("#soTrackingText").addClass('d-none');
        $("#soTrackingNo").addClass('d-none');
        $("#so_no").addClass('d-none');
    }
}
let selectedBookId = $("#book_id").val() || '';
getDocNumberByBookId(selectedBookId);
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
                type:'pi_comp_item',
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
        $(this).closest('tr').find("input[name*='[item_id]']").val('');
        $(this).closest('tr').find("input[name*='item_code']").val('');
        $(this).closest('tr').find("input[name*='attr_name']").remove();
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
let soTracking = $("#so_tracking_required").val() || '';
let actionUrl = '{{route("pi.item.row")}}'+'?count='+rowsLength+'&component_item='+JSON.stringify(lastTrObj)+`&so_tracking_required=${soTracking}`;
fetch(actionUrl).then(response => {
    return response.json().then(data => {
        if (data.status == 200) {
            if (rowsLength) {
                $("#itemTable > tbody > tr:last").after(data.data.html);
            } else {
                $("#itemTable > tbody").html(data.data.html);
            }
            initAutocompVendor("[name*='[vendor_code]']");
            initializeAutocomplete2('.comp_item_code');
            document.getElementById('copy_item_section').style.display = "";
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
    let editItemIds = [];
    let poItemIds = [];

    $(".form-check-input:checked").each(function(index, item) {
        let tr = $(item).closest('tr');
        let trIndex = tr.index();
        let po_item_id = Number($(tr).find('[name*="[po_item_id]"]').val()) || 0;
        if (po_item_id > 0) {
            poItemIds.push({ index: trIndex + 1, pi_item_id: po_item_id });
        }
    });

    if (poItemIds.length) {
        e.preventDefault();
        let rowNumbers = poItemIds.map(item => item.index).join(", ");
        Swal.fire({
            title: 'Error!',
            text: `You cannot delete pi(using in po) item(s) at row(s): ${rowNumbers}`,
            icon: 'error',
        });
        return false;
    }

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
      alert("Please first add & select row item.");
      document.getElementById('copy_item_section').style.display = "none";
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
function getItemAttribute(itemId, rowCount, selectedAttr, tr) {
    let piItemId = $(tr).find('[name*="[pi_item_id]"]').length ? $(tr).find('[name*="[pi_item_id]"]').val() : '';
    let isSo = $(tr).find('[name*="so_item_id"]').length ? 1 : 0;
    if(!isSo) {
        isSo = $(tr).find('[name*="so_pi_mapping_item_id"]').length ? 1 : 0;
    }
    if(!isSo) {
        if($(tr).find('td[id*="itemAttribute_"]').data('disabled')) {
            isSo = 1;
        }
    }

    let actionUrl = '{{route("pi.item.attr")}}'+'?item_id='+itemId+`&rowCount=${rowCount}&selectedAttr=${selectedAttr}&pi_item_id=${piItemId}&isSo=${isSo}`;
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
      let pi_item_id = $(currentTr).find("[name*='[pi_item_id]']").val() || '';
      let so_id = $(currentTr).find("[name*='[so_id]']").val() || '';
      let store_id = $("#store_id").val() || '';
      let sub_store_id = $("#sub_store_id").val() || '';
      let actionUrl = '{{route("pi.get.itemdetail")}}'+'?item_id='+itemId+'&selectedAttr='+JSON.stringify(selectedAttr)+'&remark='+remark+'&uom_id='+uomId+'&qty='+qty+'&pi_item_id='+pi_item_id+'&so_id='+so_id+'&store_id='+store_id+'&sub_store_id='+sub_store_id;
      fetch(actionUrl).then(response => {
         return response.json().then(data => {
            if(data.status == 200) {
               $("#itemDetailDisplay").html(data.data.html);
               let avlStock = data.data?.inventoryStock.confirmedStocks;
              $(`input[name="components[${rowCount}][avl_stock]"]`).val(Number(avlStock).toFixed(2));
              $(`input[name="components[${rowCount}][pending_po]"]`).val(Number(data.data.pendingPo).toFixed(2));
            }
         });
      });
   }
});

/*Delete server side rows*/
$(document).on('click','#deleteConfirm', (e) => {
    let ids = e.target.getAttribute('data-ids');
    ids = JSON.parse(ids);
    localStorage.setItem('deletedPiItemIds', JSON.stringify(ids));
    $("#deleteComponentModal").modal('hide');

    if(ids.length) {
        ids.forEach((id,index) => {
            $(`.form-check-input[data-id='${id}']`).closest('tr').remove();
        });
    }
    if(!$("#itemTable [id*=row_]").length) {
        $("th .form-check-input").prop('checked',false);
        $("#reference_from").removeClass('d-none');
        $("#orderTypeSelect").prop('disabled', false);
    }
});

// # Revision Number On Chage
$(document).on('change', '#revisionNumber', (e) => {
    let actionUrl = location.pathname + '?revisionNumber='+e.target.value;
    let revision_number = Number("{{$revision_number}}");
    let revisionNumber = Number(e.target.value);
    if(revision_number == revisionNumber) {
        location.href = actionUrl;
    } else {
        window.open(actionUrl, '_blank');
    }
});

/*Open amendment popup*/
$(document).on('click', '#amendmentBtn', (e) => {
    $("#amendmentModal").modal('show');
});

$(document).on('click', '#amendmentSubmit', (e) => {
let url = new URL(window.location.href);
url.search = '';
url.searchParams.set('amendment', 1);
let amendmentUrl = url.toString();
window.location.replace(amendmentUrl);
});


/*Amendment btn submit*/
$(document).on('click', '#amendmentBtnSubmit', (e) => {
    let remark = $("#amendmentModal").find('[name="amend_remarks"]').val();
    if(!remark) {
        e.preventDefault();
        $("#amendRemarkError").removeClass("d-none");
        return false;
    } else {
        $("#amendmentModal").modal('hide');
        $("#amendRemarkError").addClass("d-none");
        e.preventDefault();
        $("#piEditForm").submit();
    }
});


/*So modal*/
$(document).on('click', '.soSelect', (e) => {
    let paramValue = $("#procurement_type_param").val();
    let option = '';
    if(paramValue.includes('All')) {
        option+=`<option value="rm">Make to order</option><option value="fg">Buy to order</option>`;
    }
    if(paramValue.includes('Make to order')) {
        option+=`<option value="rm">Make to order</option>`;
    }
    if(paramValue.includes('Buy to order')) {
        option+=`<option value="fg">Buy to order</option>`;
    }
    $("#orderTypeSelect").empty().append(option);
    $("#soModal").modal('show');
    openSaleRequest();
    getSoItems();
});

/*searchPiBtn*/
$(document).on('click', '.searchSoBtn', (e) => {
    getSoItems();
});

function openSaleRequest()
{
    initializeAutocompleteQt("customer_code_input_qt", "customer_id_qt_val", "customer", "customer_code", "company_name");
    initializeAutocompleteQt("book_code_input_qt", "book_id_qt_val", "book_so", "book_code", "");
    initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "sale_order_document_qt_pi", "document_number", "");
    initializeAutocompleteQt("item_name_input_qt", "item_id_qt_val", "po_item_list", "item_code", "item_name");
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
                    cutomer_id : $("#cutomer_id_qt_val").val(),
                    header_book_id : $("#book_id").val(),
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
        appendTo : '#soModal',
        minLength: 0,
        select: function(event, ui) {
            var $input = $(this);
            $input.val(ui.item.label);
            $("#" + selectorSibling).val(ui.item.id);
            getSoItems();
            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val("");
                $("#" + selectorSibling).val("");
                getSoItems();
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
            $("#" + selectorSibling).val("");
            getSoItems();
        }
    }).blur(function() {
        if ($(this).val().trim() === "") {
            $("#" + selectorSibling).val("");
            getSoItems();
        }
    });
}

function getSoItems()
{
    let isAttribute = 0;
    if($("#attributeCheck").is(':checked')) {
        isAttribute = 1;
    } else {
        isAttribute = 0;
    }
    let header_book_id = $("#book_id").val() || '';
    let series_id = $("#book_id_qt_val").val() || '';
    let document_number = $("#document_no_input_qt").val() || '';
    let item_id = $("#item_id_qt_val").val() || '';
    let customer_id = $("#customer_id_qt_val").val() || '';
    let actionUrl = '{{ route("pi.get.so") }}';
    let item_search = $("#item_name_search").val();
    let fullUrl = `${actionUrl}?series_id=${encodeURIComponent(series_id)}&document_number=${encodeURIComponent(document_number)}&item_id=${encodeURIComponent(item_id)}&customer_id=${encodeURIComponent(customer_id)}&header_book_id=${encodeURIComponent(header_book_id)}&is_attribute=${isAttribute}&item_search=${item_search}`;
    fetch(fullUrl).then(response => {
        return response.json().then(data => {
            $(".po-order-detail #soDataTable").empty().append(data.data.pis);
            if(data.data.isAttribute) {
                $("#soHeaderAttribute").removeClass('d-none');
            } else {
                $("#soHeaderAttribute").addClass('d-none');
            }
        });
    });
}

$(document).on('keyup', '#item_name_search', (e) => {
    getSoItems();
});
$(document).on('change', '#attributeCheck', (e) => {
    if(e.target.checked) {
        $("#show_attribute").val(1);
    } else {
        $("#show_attribute").val(0);
    }
    getSoItems();
});
$(document).on('blur', '#customer_code_input_qt', (e) => {
    getSoItems();
});

/*Checkbox for pi item list*/
$(document).on('change','#soModal .po-order-detail > thead .form-check-input',(e) => {
  if (e.target.checked) {
      $("#soModal .po-order-detail > tbody .form-check-input").each(function(){
          $(this).prop('checked',true);
      });
  } else {
      $("#soModal .po-order-detail > tbody .form-check-input").each(function(){
          $(this).prop('checked',false);
      });
  }
});

$(document).on('change','#soModal .po-order-detail > tbody .form-check-input',(e) => {
  if(!$("#soModal .po-order-detail > tbody .form-check-input:not(:checked)").length) {
      $('#soModal .po-order-detail > thead .form-check-input').prop('checked', true);
  } else {
      $('#soModal .po-order-detail > thead .form-check-input').prop('checked', false);
  }
});

// asdasdas
$(document).on('change','#soSubmitModal .po-order-detail > thead .form-check-input',(e) => {
  if (e.target.checked) {
      $("#soSubmitModal .po-order-detail > tbody .form-check-input").each(function(){
          $(this).prop('checked',true);
      });
  } else {
      $("#soSubmitModal .po-order-detail > tbody .form-check-input").each(function(){
          $(this).prop('checked',false);
      });
  }
});
$(document).on('change','#soSubmitModal .po-order-detail > tbody .form-check-input',(e) => {
  if(!$("#soSubmitModal .po-order-detail > tbody .form-check-input:not(:checked)").length) {
      $('#soSubmitModal .po-order-detail > thead .form-check-input').prop('checked', true);
  } else {
      $('#soSubmitModal .po-order-detail > thead .form-check-input').prop('checked', false);
  }
});


function getSelectedSoIDS()
{
    let ids = [];
    $('#soModal .pi_item_checkbox:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

function getSelectedItemIDS()
{
    let itemOfIds = [];
    $('#soModal .pi_item_checkbox:checked').each(function() {
        if(Number($(this).data("item-id"))) {
            itemOfIds.push(Number($(this).data("item-id")));
        }
    });
    return itemOfIds;
}

$(document).on('click', '.soProcess', (e) => {
    $("#soSubmitModal th .form-check-input").prop('checked',false);
    let ids = getSelectedSoIDS();
    if (!ids.length) {
        $("[name='so_item_ids']").val('');
        $("[name='item_ids']").val('');
        $("#soModal").modal('hide');
        Swal.fire({
            title: 'Error!',
            text: 'Please select at least one one so item.',
            icon: 'error',
        });
        return false;
    }
    $("[name='so_item_ids']").val(ids);
    let itemIds = getSelectedItemIDS();
    $("[name='item_ids']").val(itemIds);

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
                    type:'pi_comp_item',
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
            $(this).closest('tr').find("input[name*='[item_id]']").val('');
            $(this).closest('tr').find("input[name*='item_code']").val('');
            $(this).closest('tr').find("input[name*='attr_name']").remove();
        }
    });
}

    let isAttribute = 0;
    if($("#attributeCheck").is(':checked')) {
        isAttribute = 1;
    } else {
        isAttribute = 0;
    }

    let procurementType = $("#orderTypeSelect").val() || 'rm';
    let selectedItems = [];
    if(!isAttribute) {
        $("#soModal .pi_item_checkbox:checked").each(function () {
            selectedItems.push({
                "sale_order_id": Number($(this).val()),
                "item_id": Number($(this).data("item-id"))
            });
        });
    }
    let storeId = $("#store_id").val() || '';
    let selectedItemsParam = encodeURIComponent(JSON.stringify(selectedItems));

    ids = JSON.stringify(ids);
    let soTracking = $("#so_tracking_required").val() || '';
    let actionUrl = `{{ route("pi.process.so-item") }}?ids=${ids}&is_attribute=${isAttribute}&selected_items=${selectedItemsParam}&so_tracking_required=${soTracking}&procurement_type=${procurementType}&store_id=${storeId}`;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if(data.status == 200) {
                // $("#itemTable .mrntableselectexcel").empty().append(data.data.pos);
                // initializeAutocomplete2(".comp_item_code");
                $("#soModal").modal('hide');
                // $(".soSelect").prop('disabled',true);

                if(data.data.procurement_type != 'fg') {
                    $("#soSubmitDataTable").empty().append(data.data.pos);
                    $("#soSubmitModal").modal('show');
                } else {
                    $("#itemTable .mrntableselectexcel").empty().append(data.data.pos);
                    setTimeout(() => {
                        $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
                            let currentIndex = index + 1;
                            setAttributesUIHelper(currentIndex, "#itemTable");
                        });
                    }, 100);
                }
            } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                    });
                }
        });
    });
});
/*So modal*/

/*Final process submit*/
$(document).on('click', '.soSubmitProcess', (e) => {
    if($('#soSubmitModal tbody .form-check-input:checked').length) {
        $("#soSubmitModal").modal('hide');
        let selectedData = [];
        $('#soSubmitModal tbody .form-check-input:checked').each(function(index,item){
            let dataItem = JSON.parse($(item).attr('data-item'));
            selectedData.push(dataItem);
        });

        if (selectedData.length) {
            let soTracking = $("#so_tracking_required").val() || '';
            let storeId = $("#store_id").val() || '';
            fetch('{{ route("pi.process.so-item.submit") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    selectedData: selectedData,
                    so_tracking_required: soTracking,
                    store_id: storeId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status == 200) {
                    $("#itemTable .mrntableselectexcel").empty().append(data.data.pos);
                    initAutocompVendor("[name*='[vendor_code]']");
                    initializeAutocomplete2(".comp_item_code");
                    $(".soSelect").prop('disabled', true);
                    $("#soSubmitModal").modal('hide');
                    setTimeout(() => {
                        $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
                            let currentIndex = index + 1;
                            setAttributesUIHelper(currentIndex, "#itemTable");
                        });
                    }, 100);
                }
            });
        }

    } else {
        // $("#soSubmitModal").modal('hide');
        Swal.fire({
            title: 'Error!',
            text: 'Please select at least one one so item.',
            icon: 'error',
        });
        return false;
    }
});

$(document).on('click', '#backBtn', (e) => {
    $("#soSubmitModal").modal('hide');
    setTimeout(() => {
        $("#soModal").modal('show');
    },0);
});

document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("search_filter");
    const tableBody = document.getElementById("soSubmitDataTable");
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase().trim();

        Array.from(tableBody.getElementsByTagName("tr")).forEach((row) => {
            const itemCodeCell = row.cells[1]?.innerText.toLowerCase() || "";
            const itemNameCell = row.cells[2]?.innerText.toLowerCase() || "";

            // Check if row matches the search term in either column
            const matchesItemCode = itemCodeCell.includes(searchTerm);
            const matchesItemName = itemNameCell.includes(searchTerm);
            const checkbox = row.querySelector("input[type='checkbox']");

            // Show row if it matches the search term in any column
            if (matchesItemCode || matchesItemName) {
                row.style.display = "";
            } else {
                row.style.display = "none";
                if (checkbox) {
                    checkbox.checked = false;
                }
            }
        });
    }
    searchInput.addEventListener("input", filterTable);
});

// Revoke Document
$(document).on('click', '#revokeButton', (e) => {
    let actionUrl = '{{ route("pi.revoke.document") }}'+ '?id='+'{{$pi->id}}';
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if(data.status == 'error') {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                });
            } else {
                Swal.fire({
                    title: 'Success!',
                    text: data.message,
                    icon: 'success',
                });
            }
            location.reload();
        });
    });
});

/*Final process submit*/
$(document).on('click', '.clearPiFilter', (e) => {
    $("#item_name_search").val('');
    $("#item_name_input_qt").val('');
    $("#item_id_qt_val").val('');
    $("#department_po").val('');
    $("#department_id_po").val('');
    $("#customer_code_input_qt").val('');
    $("#customer_id_qt_val").val('');
    $("#book_code_input_qt").val('');
    $("#book_id_qt_val").val('');
    $("#document_no_input_qt").val('');
    $("#document_id_qt_val").val('');
    getSoItems();
});

function updateDropdown(storeId) {
    if($("#requester_type").val().includes('Department')) {
        let selectedId = '{{$pi->sub_store_id}}' || '';
        let actionUrl = '{{route("subStore.get.from.stores")}}'+'?store_id='+storeId;
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                let option = '<option value="">Select</option>';
                if(data.data.length) {
                    data.data.forEach(function(item){
                        option+= `<option value="${item.id}" ${selectedId == item.id ? 'selected' : ''}>${item.name}</option>`;
                    })
                    $("#department_id_header").removeClass('d-none');
                } else {
                    $("#department_id_header").addClass('d-none');
                }
                $("#sub_store_id").empty().append(option);
            });
        });
    }
}

$(document).on('change', "[name='store_id']", function () {
    updateDropdown(this.value);
});

$(document).on('change', "[name='store_id']", (e) => {
    let storeId = e.target.value || '';
    updateDropdown(storeId);
});

setTimeout(() => {
    let storeId = $("#store_id").val() || '';
    if(storeId) {
        updateDropdown(storeId);
    }
},100);

setTimeout(() => {
    $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
        let currentIndex = index + 1;
        setAttributesUIHelper(currentIndex,"#itemTable");
    });

},100);
@if($pi->pi_items->count())
    $("#orderTypeSelect").prop('disabled', true);
@endif
</script>
@endsection
