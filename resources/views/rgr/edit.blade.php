@extends('layouts.app')

@section('content')
        <!-- BEGIN: Content -->
    <form class="ajax-input-form" data-module="rgr" method="POST" action="{{ route('rgr.update', $rgr->id) }}" data-redirect="{{ route('rgr.index') }}" id="rgrForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="app-content content ">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <!-- Content Header (Breadcrumb and Buttons) -->
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        @include('layouts.partials.breadcrumb-add-edit', [
                            'title' => 'Return Goods Receipt',
                            'menu' => 'Home',
                            'menu_url' => url('home'),
                            'sub_menu' => 'Edit'
                            ])
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <input type="hidden" id="documentStatus" name="document_status" value="{{ $rgr->documentStatus ?? '' }}">
                                <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i> Back</button>
                                @if($buttons['draft'])
                                    <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" name="action" value="draft"><i data-feather='save'></i> Save as Draft</button>
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

                <!-- Content Body -->
                <div class="content-body">
                    <section id="basic-datatable">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <!-- Card Body (Basic Information) -->
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
                                                        Status : <span class="{{$docStatusClass}}">{{$rgr->display_status}}</span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Form Inputs (Series, Document No., Date, Location) -->
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="basic-information">
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Series <span class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <select class="form-select" id="book_id" name="book_id" disabled>
                                                                @foreach($books as $book)
                                                                    <option value="{{$book->id}}" {{$rgr->book_id == $book->id ? 'selected' : ''}}>{{ucfirst($book->book_code)}}</option>
                                                                @endforeach
                                                            </select>
                                                            <input type="hidden" name="book_code" value="{{$rgr->book_code}}" id="book_code">
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Document No.<span class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <input type="text" value="{{$rgr->document_number}}" name="document_number" class="form-control" id="document_number">
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Document Date <span class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <input type="date" class="form-control" value="{{$rgr->document_date ?? date('Y-m-d')}}" name="document_date">
                                                        </div>
                                                    </div>

                                                   <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Location <span class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <select class="form-select" id="store_id" name="store_id" onchange="updateStoreDetails()">
                                                                @foreach($locations as $location)
                                                                    <option value="{{$location->id}}" 
                                                                        {{$rgr->store_id == $location->id ? 'selected' : ''}} data-store-name="{{ $location->store_name }}" >
                                                                        {{ $location->store_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                            <input type="hidden" id="store_name" name="store_name" value="">
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1" id="reference_from">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Reference from</label>
                                                        </div>
                                                        <div class="col-md-5 action-button">
                                                            <button type="button" class="btn btn-outline-primary btn-sm mb-0 pickupSelect"><i data-feather="plus-square"></i>Pickup Schedul</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            {{-- History Code --}}
                                            @include('partials.approval-history', ['document_status' => $rgr->document_status, 'revision_number' => $revision_number])
                                        </div>
                                    </div>
                                </div>
                                <!-- General Information Card -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card quation-card">
                                            <div class="card-header newheader">
                                                <div>
                                                    <h4 class="card-title">General Information</h4>
                                                </div>
                                            </div>
                                            <!-- Card Body (Pickup Schedule, Trip, Vehicle, Champ) -->
                                            <div class="card-body">
                                                <div class="row">

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Pickup Schedule No. <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" name="pickup_schedule_no"
                                                                class="form-control" value="{{ $rgr->document_number }}" />
                                                            {{-- 🔹 Hidden ID field --}}
                                                            <input type="hidden" name="pickup_schedule_id"
                                                                id="pickup_schedule_id" value="{{ $rgr->pickup_schdule_id }}" />
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Trip No. <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" name="trip_no" class="form-control"  value="{{ $rgr->trip_no }}"/>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Vehicle No. <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" name="vehicle_no" class="form-control"  value="{{ $rgr->vehicle_no }}"/>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Champ Name <span
                                                                    class="text-danger">*</span></label>
                                                            <input type="text" name="champ_name" class="form-control"  value="{{ $rgr->champ_name }}"/>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <div class="mb-1">
                                                            <label class="form-label">Remark</label>
                                                            <input type="text" name="remark" class="form-control" value="{{ $rgr->remark }}" />
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Item Detail Card -->
                                <div class="card">
                                    <div class="card-body customernewsection-form">
                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader ">
                                                        <h4 class="card-title text-theme">Item Detail</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Item Detail Table -->
                                        <div class="table-responsive pomrnheadtffotsticky">
                                            <table
                                                class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"
                                                id="itemDetailTable">

                                                <thead>
                                                    <tr>
                                                        <th width="62" class="customernewsection-form">S.No</th>
                                                        <th>Item Code</th>
                                                        <th>Item Name</th>
                                                        <th>Attributes</th>
                                                        <th>UOM</th>
                                                        <th>Qty</th>
                                                        <th>UID</th>
                                                        <th>Customer</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="pickupItemBody">
                                                     @include('rgr.partials.pickup-item-row')
                                                </tbody>
                                                 <tfoot>
                                                    <tr valign="top">
                                                        <td colspan="8">
                                                            <table class="table border" id="itemDetailTable">
                                                                <tr>
                                                                    <td class="p-0">
                                                                        <h6 class="text-dark mb-0 bg-light-primary py-1 px-50">
                                                                            <strong>Item Details</strong>
                                                                        </h6>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="poprod-decpt" id="item_detail_name">
                                                                        <span class="poitemtxt mw-100"><strong>Name</strong>:
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="poprod-decpt" id="item_detail_hsn">
                                                                        <span class="badge rounded-pill badge-light-primary"><strong>HSN</strong>:
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="poprod-decpt" id="item_detail_uom_qty">
                                                                        <span class="badge rounded-pill badge-light-primary"><strong>Inv.
                                                                                UOM</strong>: </span>
                                                                        <span class="badge rounded-pill badge-light-primary"><strong>Qty.</strong>:
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                                <tr>
                                                                    <td class="poprod-decpt" id="item_detail_remarks">
                                                                        <span class="badge rounded-pill badge-light-secondary"><strong>Remarks</strong>:
                                                                        </span>
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                         <!-- Upload Document and Final Remarks -->
                                        <div class="row mt-2">
                                            <!-- Upload Document -->
                                            <div class="col-md-12">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <div class="mb-1">
                                                            <label class="form-label">Upload Document</label>
                                                            <input type="file" name="attachment[]" class="form-control"
                                                                onchange="addFiles(this,'main_rgr_file_preview')" multiple>
                                                            <span class="text-primary small">{{ __("message.attachment_caption") }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6" style="margin-top:19px;">
                                                        <div class="row" id="main_rgr_file_preview">
                                                            @include('partials.document-preview',['documents' => $rgr->getDocuments(), 'document_status' => $rgr->document_status,'elementKey' => 'main_rgr_file_preview'])
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Final Remarks -->
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">Final Remark</label>
                                                    <textarea maxlength="250" name="final_remark" rows="4" class="form-control"
                                                            placeholder="Enter Remarks here...">{{ $rgr->final_remark }}</textarea>
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
      @include('rgr.partials.amendment-modal', ['id' => $rgr->id])
      <!-- @include('rgr.partials.close-modal', ['id' => $rgr->id]) -->
    </form>
    <!-- END: Content -->
{{-- Approval Modal --}}
@include('rgr.partials.approve-modal', ['id' => $rgr->id])
@include('rgr.partials.pickup-modal')
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
              <p>Are you sure you want to <strong>Amendment</strong> this <strong>Rgr</strong>? After Amendment this action cannot be undone.</p>
              <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
              <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
          </div>
      </div>
  </div>
</div>
@endsection
@section('scripts')
<script type="text/javascript">
    var processPickupItemUrl = '{{ route("rgr.process.pickup-schdule-list") }}';
</script>
<script type="text/javascript" src="{{asset('assets/js/modules/common-attr-ui.js')}}"></script>
<script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
<script type="text/javascript">
$(function(){
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
                }
                if(data.status == 404) {
                    $("#book_code").val('');
                    $("#document_number").val('');
                    const docDateInput = $("[name='document_date']");
                    docDateInput.removeAttr('min');
                    docDateInput.removeAttr('max');
                    docDateInput.val(new Date().toISOString().split('T')[0]);
                    alert(data.message);
                }
            });
        }); 
    }

    /*Set Service Parameter*/
   function setServiceParameters(parameters) {
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

       let reference_from_service = parameters.reference_from_service;
        if(reference_from_service.length) {
            let c_bom = '{{\App\Helpers\ConstantHelper::PDS_SERVICE_ALIAS}}';
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
            //     // location.href = "{{route('pwo.index')}}";
            // },1500);
        }
   }
    function getPickupScheduleItems() {
        let book_id = $("#book_id").val() || ''; 
        let storeId = $("#store_id").val() || ''; 
        let vehicleNo = $("#vehicle_no_input_pu").val() || '';
        let tripNo = $("#trip_id_input_pu").val() || '';
        let actionUrl = '{{ route("rgr.get.pickup.item") }}'; 

        let fullUrl = `${actionUrl}?book_id=${encodeURIComponent(book_id)}&store_id=${encodeURIComponent(storeId)}&vehicle_no=${encodeURIComponent(vehicleNo)}&trip_no=${encodeURIComponent(tripNo)}`;

        fetch(fullUrl)
            .then(response => {
                return response.json();
            })
            .then(data => {
                $("#pickupDataTable").empty().append(data.data.pis);
            });
    }


    $(document).on('click', '.pickupSelect', (e) => {
    $("#pickupModal").modal('show');
    getPickupScheduleItems(); 
});

$(document).on('input', '#vehicle_no_input_pu, #trip_id_input_pu', function(){
    getPickupScheduleItems();
});

function getSelectedPiIDS() {
    let ids = [];
    $('.analyze_row:checked').each(function() {
        ids.push($(this).val());
    });
    return ids;
}

$(document).on('click', '.analyzeButton', function() {

    let missingFields = [];
    if (missingFields.length) {
        Swal.fire({
            icon: 'error',
            title: 'Missing Fields',
            text: "Please fill: " + missingFields.join(", ")
        });
        return false;
    }
    let selectedPiIDs = getSelectedPiIDS();
    if (!selectedPiIDs.length) {
        Swal.fire({
            icon: 'error',
            title: 'No Items Selected',
            text: "Please select at least one item."
        });
        return false;
    }

    let selectedItems = [];
    $('.analyze_row:checked').each(function() {
        let tr = $(this).closest('tr');
        let pickup_schedule_id = tr.data('pickup-schedule-id');
        if (!pickup_schedule_id) {
            tr.addClass('table-danger');
            Swal.fire({
                icon: 'error',
                title: 'Missing Pickup Schedule ID',
                text: 'Pickup Schedule ID missing in one of the selected items.'
            });
            return false;
        } else {
            tr.removeClass('table-danger');
        }

        let store_id = tr.find("select[name='store_id']").val() || null;
        
        selectedItems.push({
            main_item: true,
            item_ids: [$(this).val()],
            pickup_schedule_id: pickup_schedule_id,
            store_id: store_id
        });
    });

    let postData = {
        selected_items: JSON.stringify(selectedItems),
        pickup_schedule_no: $('input[name="pickup_schedule_no"]').val(),
        pickup_schedule_id: $('input[name="pickup_schedule_id"]').val(),
        trip_no: $('input[name="trip_no"]').val(),
        vehicle_no: $('input[name="vehicle_no"]').val(),
        champ_name: $('input[name="champ_name"]').val(),
        remark: $('input[name="remark"]').val(),
        book_id: $("#book_id").val(),
        store_id: $("#store_id").val(),
        is_attribute: $("#attributeCheck").is(':checked') ? 1 : 0,
        rowCount: $('#itemDetailTable tbody tr').length
    };

    // Submit via fetch
    fetch(processPickupItemUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        body: JSON.stringify(postData)
    })
    .then(response => response.json())
    .then(data => {
    if (data.status == 200) {
    $('#pickupItemBody').empty();
    $('#pickupItemBody').append(data.data.pos);
            let h = data.data.header;
            $('input[name="pickup_schedule_no"]').val(h.pickup_schedule_no || '');
            $('input[name="pickup_schedule_id"]').val(h.pickup_schedule_id || ''); 
            $('input[name="trip_no"]').val(h.trip_no || '');
            $('input[name="vehicle_no"]').val(h.vehicle_no || '');
            $('input[name="champ_name"]').val(h.champ_name || '');
            $('input[name="remark"]').val(h.remark || '');
            $("#pickupModal").modal('hide');
            $('.analyze_row:checked').closest('tr').removeClass('table-danger');
            $('#pickupItemBody tr.item_detail_row:last').click();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Failed to process request'
        });
    });
});
$(document).on("change", ".analyze_row", function () {
    if (this.checked) {
        $(".analyze_row").not(this).prop("checked", false);
    } else {
        if (!$(".analyze_row:checked").length) {
            Swal.fire({
                icon: 'warning',
                title: 'Selection Required',
                text: 'Please select at least one item.'
            });
            $(this).prop("checked", true); 
        }
    }
});
$(document).on('click', '.clearPiFilter', (e) => {
    $("#vehicle_no_input_pu").val('');
    $("#trip_id_input_pu").val('');
    getPickupScheduleItems();
});

// Handle item detail row click
   function updateItemDetails(row) {
        let itemName = row.find("td:nth-child(3)").text().trim();
        let hsnCode = row.find("input[name*='[hsn_code]']").val();
        let invUom = row.find("input[name*='[uom_name]']").val();
        let qty = row.find("input[name*='[qty]']").val();
        let remarks = row.find("input[name*='[item_remark]']").val();

        $("#item_detail_name .poitemtxt").html("<strong>Name</strong>: " + (itemName || '-'));
        $("#item_detail_hsn .badge").html("<strong>HSN</strong>: " + (hsnCode || '-'));

        $("#item_detail_uom_qty").html(
            `<span class="badge rounded-pill badge-light-primary"><strong>Inv. UOM</strong>: ${invUom || '-'}</span>
             <span class="badge rounded-pill badge-light-primary"><strong>Qty.</strong>: ${qty || '-'}</span>`
        );
        $("#item_detail_remarks .badge").html("<strong>Remarks</strong>: " + (remarks || '-'));
    }
    let itemRows = $("#pickupItemBody .item_detail_row"); 

    if (itemRows.length > 0) {
        updateItemDetails(itemRows.last()); 
    }

    $(document).on("click", ".item_detail_row", function() {
        updateItemDetails($(this));
        $('html, body').animate({
            scrollTop: $("#itemDetailTable").offset().top - 100
        }, 400);
    });
});
// Revoke Document
$(document).on('click', '#revokeButton', (e) => {
    let actionUrl = '{{ route("rgr.revoke") }}'+ '?id='+'{{$rgr->id}}';
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
  /*Approve modal*/
  $(document).on('click', '#approved-button', (e) => {
     let actionType = 'approve';
     $("#approveModal").find("# var currentActionType = $('#action_type').val();").val(actionType);
     $("#approveModal #popupTitle").text("Approve Application");
     $("#approveModal").modal('show');
  });
  $(document).on('click', '#reject-button', (e) => {
     let actionType = 'reject';
     $("#approveModal #popupTitle").text("Reject Application");
     $("#approveModal").find("#action_type").val(actionType);
     $("#approveModal").modal('show');
  });
  /*Amendment modal open*/
  $(document).on('click', '.amendmentBtn', (e) => {
    $("#amendmentconfirm").modal('show');
    });

    /*Open amendment popup*/
    $(document).on('click', '#amendmentSubmit', (e) => {
        $("#amendmentconfirm").modal('hide');
        $("#amendmentModal").modal('show');
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
            $("#rgrForm").submit();
        }
    });
     var currentRevNo = $("#revisionNumber").val();
     $(document).on('change', '#revisionNumber', function (e) {
        e.preventDefault();
        const selectedRev = e.target.value;
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('revisionNumber', selectedRev);
        $("#revisionNumber").val(currentRevNo);
        window.open(currentUrl.toString(), '_blank');
    });
</script> 
<script>
    function updateStoreDetails() {
        var select = document.getElementById('store_id');
        var selectedOption = select.options[select.selectedIndex];
        var storeName = selectedOption.getAttribute('data-store-name');
        document.getElementById('store_name').value = storeName;
    }
    document.addEventListener('DOMContentLoaded', function() {
        updateStoreDetails();
    });
</script>
@endsection


