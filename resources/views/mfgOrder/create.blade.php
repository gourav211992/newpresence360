@extends('layouts.app')
@section('content')
<form class="ajax-input-form" data-module="mo" method="POST" action="{{ route('mo.store') }}" data-redirect="{{ route('mo.index') }}" enctype='multipart/form-data'>
    @csrf
<input type="hidden" name="so_item_ids" id="so_item_ids">
<input type="hidden" name="station_wise_consumption" id="station_wise_consumption">
<div class="app-content content ">
   <div class="content-overlay"></div>
   <div class="header-navbar-shadow"></div>
   <div class="content-wrapper container-xxl p-0">
      <div class="content-header pocreate-sticky">
         <div class="row">
            @include('layouts.partials.breadcrumb-add-edit', [
                'title' => 'Manufacturing order',
                'menu' => 'Home',
                'menu_url' => url('home'),
                'sub_menu' => 'Add New'
             ])
            <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
               <div class="form-group breadcrumb-right">
                  <input type="hidden" name="document_status" id="document_status">
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
                           </div>
                        </div>
                        <div class="row">
                           <div class="col-md-8">
                              <div class="">
                                 <div class="row align-items-center mb-1">
                                    <div class="col-md-3">
                                        <label class="form-label">Series <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-5">
                                       <select class="form-select" id="book_id" name="book_id">
                                          @foreach($books as $book)
                                             <option value="{{$book->id}}">{{ucfirst($book->book_code)}}</option>
                                          @endforeach
                                       </select>
                                       <input type="hidden" name="book_code" id="book_code">
                                    </div>
                                 </div>

                                <div class="row align-items-center mb-1">
                                    <div class="col-md-3">
                                        <label class="form-label">Document No. <span class="text-danger">*</span></label>
                                    </div>

                                    <div class="col-md-5">
                                        <input type="text" name="document_number" class="form-control" id="document_number">
                                    </div>
                                 </div>
                                 <div class="row align-items-center mb-1">
                                     <div class="col-md-3">
                                         <label class="form-label">Document Date <span class="text-danger">*</span></label>
                                     </div>
                                     <div class="col-md-5">
                                         <input type="date" class="form-control" value="{{date('Y-m-d')}}"  min = "{{ $current_financial_year['start_date'] }}" max = "{{ $current_financial_year['end_date'] }}" name="document_date">
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
                                <div class="row align-items-center mb-1" id="sub_store_div">
                                    <div class="col-md-3">
                                        <label class="form-label">Sub Location <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-5">
                                        <select class="form-select" id="sub_store_id" name="sub_store_id">
                                            @foreach($locations as $location)
                                                <option value="{{$location->id}}">{{ $location?->store_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                {{-- <div class="row align-items-center mb-1" id="station_div">
                                    <div class="col-md-3">
                                        <label class="form-label">Station <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-5">
                                        <select class="form-select" id="station_id" name="station_id">
                                        <option value="" >Select</option>
                                        @foreach($stations as $station)
                                            <option value="{{$station->id}}">{{ $station?->name }}</option>
                                        @endforeach
                                    </select>
                                    </div>
                                </div>  --}}
                                <div class="row align-items-center mb-1">
                                    <div class="col-md-3">
                                        <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-5">
                                        <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" id="item_code" name="item_code" />
                                        <input type="hidden" placeholder="Select" class="form-control mw-100 ledgerselecct" id="item_id" name="item_id" />
                                    </div>
                                </div>
                                <div class="row align-items-center mb-1 d-none" id="machineDiv">
                                    <div class="col-md-3">
                                        <label class="form-label">Machine</label>
                                    </div>
                                    <div class="col-md-5">
                                        <select class="form-select" id="main_machine_id" name="main_machine_id">

                                        </select>
                                    </div>
                                </div>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>

                <div class="col-md-12">
                    {{-- Append Attribute here  --}}
                    <div class="card">
                        <div class="card-body customernewsection-form">
                            <div class="border-bottom mb-2 pb-25" id="componentSection">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="newheader ">
                                        <h4 class="card-title text-theme">PWO Details</h4>
                                        {{-- <p class="card-text">Fill the details</p> --}}
                                    </div>
                                </div>
                                {{-- <div class="col-md-6 text-sm-end">
                                    <a href="javascript:;" class="btn btn-sm btn-outline-danger me-50" id="deleteBtn">
                                    <i data-feather="x-circle"></i> Delete</a>
                                    <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary">
                                    <i data-feather="plus"></i> Add Items</a>
                                </div> --}}
                            </div>
                            <div class="row">
                                <div class="col">
                                    <div class="mb-1">
                                        <label class="form-label">PWO Series</label>
                                        <input type="text" id="pwo_book_code_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                        <input type = "hidden" id = "pwo_book_id_qt_val"></input>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="mb-1">
                                        <label class="form-label">PWO No.</label>
                                        <input type="text" id="pwo_document_no_input_qt" class="form-control mw-100">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="mb-1">
                                        <label class="form-label">SO Series</label>
                                        <input type="text" id="so_book_code_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                        <input type = "hidden" id = "so_book_id_qt_val"></input>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="mb-1">
                                        <label class="form-label">SO No.</label>
                                        <input type="text" id="so_document_no_input_qt" class="form-control mw-100">
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="mb-1">
                                        <label class="form-label">Customer</label>
                                        <input type="text" id="customer_code_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
                                        <input type = "hidden" id = "customer_id_qt_val"></input>
                                    </div>
                                </div>
                                {{-- <div class="col">
                                    <div class="mb-1">
                                        <label class="form-label">Location</label>
                                        <select class="form-select" id="filter_store_id" name="filter_store_id">
                                            <option value=""></option>
                                            @foreach($locations as $location)
                                                <option value="{{$location->id}}">{{ $location?->store_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div> --}}
                                <div class="col mb-1">
                                    <label class="form-label">&nbsp;</label><br/>
                                    {{-- <button type="button" class="btn btn-primary btn-sm searchPiBtn"><i data-feather="search"></i> Search</button> --}}
                                    <button type="button" class="btn btn-warning btn-sm clearPiFilter"><i data-feather="x-circle"></i> Clear</button>
                                </div>
                            </div>
                            </div>

                            <div class="table-responsive pomrnheadtffotsticky">
                                <table id="itemTable" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad" data-json-key="components_json" data-row-selector="tr[id^='row_']">
                                    <thead>
                                        <tr>
                                            <th>
                                            <div class="form-check form-check-primary custom-checkbox">
                                                <input type="checkbox" class="form-check-input" id="Email">
                                                <label class="form-check-label" for="Email"></label>
                                            </div>
                                            </th>
                                            <th width="200px">Series</th>
                                            <th width="150px">Doc No.</th>
                                            <th width="100px">Doc Date</th>
                                            <th width="100px">Location</th>
                                            <th width="150px">Product Code</th>
                                            <th width="300px">Product Name</th>
                                            <th max-width="180px">Attributes</th>
                                            <th >UOM</th>
                                            <th class="text-end">Qty</th>
                                            <th width="200px">Customer</th>
                                            <th width="150px">Order No.</th>
                                            <th width="150px" id="machine_name" class="d-none">Machine</th>
                                            <th width="150px" id="sheets" class="d-none">Sheets</th>
                                            {{-- <th>Order Date</th> --}}
                                            <th width="50px">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="mrntableselectexcel"></tbody>
                                    <tfoot>
                                        <tr valign="top">
                                            <td colspan="13" id="detailTableFooter">
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
                                    </tfoot>
                                </table>
                            </div>


                        </div>
                    </div>

                    {{-- Show MO Product Components --}}
                    <div id="componentDetails" style="display: none;">
                        {{-- ref: mo-product-components blade --}}
                    </div>

                    <div class="card">
                        <div class="card-body customernewsection-form">
                            <div class="border-bottom mb-2 pb-25" id="componentSection">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="newheader ">
                                        <h4 class="card-title text-theme">Remarks</h4>
                                        {{-- <p class="card-text">Fill the details</p> --}}
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
                                        <input type="file" name="attachment[]" class="form-control" onchange = "addFiles(this,'main_bom_file_preview')" multiple>
                                        <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                                    </div>
                                </div>
                                <div class = "col-md-6" style = "margin-top:19px;">
                                    <div class = "row" id = "main_bom_file_preview">
                                    </div>
                                </div>
                                    </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label">Final Remarks</label>
                                    <textarea maxlength="250" name="remarks" type="text" rows="4" class="form-control" placeholder="Enter Remarks here..."></textarea>
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
    let getMachineDetailUrl = "{{route('mo.get.machine.detail')}}";
</script>
<script type="text/javascript" src="{{asset('assets/js/modules/common-attr-ui.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/modules/mo.js')}}"></script>
<script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
<script type="text/javascript">
$(function(){
   setTimeout(() => {
        $("#book_id").trigger('change');
   },0);
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
                    $input.val(itemName);
                    $("#item_id").val(itemId);
                    setTimeout(() => {
                        getPwo();
                    },0);
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $("#item_id").val('');
                        $(this).val("");
                        $(this).attr('data-name', '');
                        $(this).attr('data-code', '');
                        setTimeout(() => {
                            getPwo();
                        },0);
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $("#item_id").val('');
                    $(this).val('');
                    $(this).autocomplete("search", "");
                    setTimeout(() => {
                        getPwo();
                    },0);
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
   }

});
/*Check attrubute*/
$(document).on('click', '.attributeBtn', (e) => {
    let tr = e.target.closest('tr');
    let item_name = tr.querySelector('[name*="[item_code]"]').value;
    let item_id = tr.querySelector('[name*="[item_id]"]').value;
    let selectedAttr = [];
    const attrElements = tr.querySelectorAll('[name*="[attr_name]"]');
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
                  $(tr).find('td:nth-child(2)').find("[name*='[attr_name]']").remove();
                  $(tr).find('td:nth-child(2)').append(data.data.hiddenHtml);
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
function fetchItemDetails(currentTr) {
    let pName = $(currentTr).find("[name*='component_item_name']").val();
    let itemId = $(currentTr).find("[name*='item_id']").val();
    let pwoMappingId = $(currentTr).find("[name*='pwo_mapping_id']").val();
    let storeId = $("#store_id option:selected").val();
    let subStoreId = $("#sub_store_id option:selected").val();

    if (itemId) {
        let selectedAttr = [];
        $(currentTr).find("[name*='attr_name']").each(function(index, item) {
            if ($(item).val()) {
                selectedAttr.push($(item).val());
            }
        });

        let sectionName = $(currentTr).find("[name*='[section_name]']").val() || '';
        let subSectionName = $(currentTr).find("[name*='[sub_section_name]']").val() || '';
        let stationName = $(currentTr).find("[name*='[station_name]']").val() || '';
        let remark = $(currentTr).find("[name*='remark']").val() || '';

        // Norms
        let qty_per_unit = $(currentTr).find("[name*='[qty_per_unit]']").val() || '';
        let total_qty = $(currentTr).find("[name*='[total_qty]']").val() || '';
        let std_qty = $(currentTr).find("[name*='[std_qty]']").val() || '';

        let actionUrl = `{{route("mo.get.itemdetail")}}?item_id=${itemId}&selectedAttr=${JSON.stringify(selectedAttr)}&remark=${remark}&section_name=${sectionName}&sub_section_name=${subSectionName}&station_name=${stationName}&qty_per_unit=${qty_per_unit}&total_qty=${total_qty}&pwo_papping_id=${pwoMappingId}&store_id=${storeId}&sub_store_id=${subStoreId}&std_qty=${std_qty}`;
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                    $(".item_detail_row").remove();
                    if ($("#itemDetailTable tbody tr").length > 2) {
                        $("#itemDetailTable tbody tr").slice(-2).remove();
                    }
                    $("#itemDetailTable tbody tr:first").after(data.data.html);

                    // Show Prodcut Component Details
                    $("#componentDetails").html(data.data.mo_product_component_html);
                    $("#componentDetails").show();

                }
            });
        });
    }
}

// Attach event listener
$(document).on('input change focus', '#itemTable tr td input', function(e) {
    let currentTr = e.target.closest('tr');
    fetchItemDetails(currentTr);
});

$(document).on('click', '.submit_attribute', (e) => {
    $("#attribute").modal('hide');
});

function openBomRequest()
{
    initializeAutocompleteQt("customer_code_input_qt", "customer_id_qt_val", "customer", "customer_code", "company_name");
    initializeAutocompleteQt("pwo_book_code_input_qt", "pwo_book_id_qt_val", "book_pwo", "book_code", "");
    initializeAutocompleteQt("so_book_code_input_qt", "so_book_id_qt_val", "document_book", "book_code", "");

}
openBomRequest();
function initializeAutocompleteQt(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "")
{
    let serviceAlias = '';
    if(typeVal == 'document_book') {
        serviceAlias = '{{\App\Helpers\ConstantHelper::SO_SERVICE_ALIAS}}';
    }
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
                    service_alias : serviceAlias,
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
            setTimeout(() => {
                getPwo();
            },0);
            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val("");
                $("#" + selectorSibling).val("");
            }
            setTimeout(() => {
                getPwo();
            },0);
        }
    })
    // .focus(function() {
    //     if (this.value === "") {
    //         $(this).autocomplete("search", "");
    //     }
    //     setTimeout(() => {
    //         getPwo();
    //     },0);
    // });
}

function getPwo()
{
    let itemId = $("#item_id").val() || '';
    // let storeId = $("#filter_store_id").val() || '';
    let storeId = $("#store_id").val() || '';
    let header_book_id = $("#book_id").val() || '';
    let stationId = $("#station_id").val() || '';
    let series_id = $("#pwo_book_id_qt_val").val() || ''; // pwo
    let document_number = $("#pwo_document_no_input_qt").val() || '';// pwo
    let so_series_id = $("#so_book_id_qt_val").val() || ''; // so
    let so_document_number = $("#so_document_no_input_qt").val() || '';// so
    let actionUrl = '{{ route("mo.get.pwo.create") }}';
    let customerId = $("#customer_id_qt_val").val() || '';
    let fullUrl = `${actionUrl}?series_id=${encodeURIComponent(series_id)}&document_number=${encodeURIComponent(document_number)}&so_series_id=${encodeURIComponent(so_series_id)}&so_document_number=${encodeURIComponent(so_document_number)}&header_book_id=${encodeURIComponent(header_book_id)}&customer_id=${customerId}&item_id=${itemId}&store_id=${storeId}`;
    fetch(fullUrl).then(response => {
        return response.json().then(data => {
            if(data.status == 422) {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || '',
                    icon: 'error',
                });
                $("#item_code").attr('data-name', '');
                $("#item_code").attr('data-code', '');
                $("#item_code").val('');
                $("#item_id").val('');
                return;
            }

            $("#itemTable .mrntableselectexcel").empty().append(data.data.pis);
            setTimeout(() => {
                $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
                    let currentIndex = index + 1;
                    setAttributesUIHelper(currentIndex,"#itemTable");
                });
            },100);
            if(data.data.is_machine) {
                $("#machineDiv").removeClass('d-none');
                let option = '<option value="">Select Machine</option>';
                data.data.machines.forEach((element) => {
                    option += `<option value="${element.id}">${element.name}</option>`;
                });
                $("#main_machine_id").empty().append(option);
                $("#machine_name").removeClass('d-none');
                $("#sheets").removeClass('d-none');
                $("#detailTableFooter").attr("colspan", "15");
            } else {
                $("#detailTableFooter").attr("colspan", "13");
                $("#machine_name").addClass('d-none');
                $("#sheets").addClass('d-none');
                $("#machineDiv").addClass('d-none');
                $("#main_machine_id").empty();
            }
        });
    });
}

getPwo();

$(document).on('change',"#store_id", (e) => {
    getPwo();
});
$(document).on('change',"#sub_store_id", (e) => {
    getPwo();
});
$(document).on('keyup',"#pwo_document_no_input_qt", (e) => {
    getPwo();
});
$(document).on('keyup',"#so_document_no_input_qt", (e) => {
    getPwo();
});
$(document).on('change',"#filter_store_id", (e) => {
    getPwo();
});

/*Checkbox for pi item list*/
$(document).on('change','.po-order-detail > thead .form-check-input',(e) => {
  if (e.target.checked) {
      $(".po-order-detail > tbody .form-check-input").each(function() {
          $(this).prop('checked',true);
      });
  } else {
      $(".po-order-detail > tbody .form-check-input").each(function() {
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

// Sub Store
function locationOnChange(storeId = '') {
    let actionUrl = '{{route("mo.get.sub.store")}}'+'?store_id='+storeId;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                if(data.data.length) {
                    let subStore = ``;
                    data.data.forEach(element => {
                        subStore += `<option value="${element.id}" data-station-wise-consumption="${element.station_wise_consumption}">${element.name}</option>`;
                    });
                    $("#sub_store_id").prop('disabled', false).empty().append(subStore);
                    const stationWise = getStationWiseConsBySubStoreId();
                    if(stationWise.includes('yes')) {
                        $("#station_column").removeClass('d-none');
                    } else {
                        $("#station_column").addClass('d-none');
                    }
                    // $("#sub_store_div").removeClass('d-none');
                } else {
                    $("#sub_store_id").prop('disabled', true).empty().append(`<option value="">No Sub Store</option>`);
                    // $("#sub_store_div").addClass('d-none');
                }
                // $("#sub_store_id").empty().append(data.data.html);
            }
        });
    });
}
function getStationWiseConsBySubStoreId()
{
    const swc = $('#sub_store_id').find('option:selected').attr('data-station-wise-consumption') || 'no';
    $("#station_wise_consumption").val(swc);
    return swc;
}
$(document).on('change',"#store_id", (e) => {
    let storeId = e.target.value || '';
    locationOnChange(storeId);
});

setTimeout(() => {
    let storeId = $("#store_id").val() || '';
    locationOnChange(storeId);
}, 0);

$(document).on('click', '.clearPiFilter', (e) => {
    $("#filter_store_id").val('').trigger('change');
    $("#pwo_book_code_input_qt").val('');
    $("#pwo_book_id_qt_val").val('');
    $("#pwo_document_no_input_qt").val('');
    $("#so_book_code_input_qt").val('');
    $("#so_book_id_qt_val").val('');
    $("#so_document_no_input_qt").val('');
    $("#customer_code_input_qt").val('');
    $("#customer_id_qt_val").val('');
    getPwo();
});

$(document).on("blur", "#item_code", function () {
    if ($(this).val().trim() === '') {
        $("#item_id").val('');
        $(this).val('');
        getPwo();
    }
});

function validateQty(input) {
    let min = parseFloat(input.getAttribute("min"));
    let max = parseFloat(input.getAttribute("max"));
    let value = parseFloat(input.value);

    if (isNaN(value)) {
        // input.value = min;
        input.style.border = "2px solid red";
        input.style.backgroundColor = "#ffe6e6";
        return;
    }

    if (value < min) {
        // input.value = min; 
        input.style.border = "2px solid red";
        input.style.backgroundColor = "#ffe6e6";
    }
     else if (value > max) {
        input.value = max; 
        input.style.border = "2px solid red";
        input.style.backgroundColor = "#ffe6e6";
    } else {
        // valid value
        input.style.border = "";
        input.style.backgroundColor = "";
    }
}

</script>
@endsection
