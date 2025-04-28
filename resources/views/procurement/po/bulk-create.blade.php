@extends('layouts.app')
@section('styles')
<style>
.pomrnheadtffotsticky {
    overflow-x: auto;
    white-space: nowrap;
    max-width: 100%;
}

.pomrnheadtffotsticky table {
    width: max-content;
    min-width: 100%;
    border-collapse: collapse;
}

.no-data-row td {
    padding: 15px;
    font-size: 14px;
}

</style>
@endsection
@section('content')
<form class="ajax-input-form" method="POST" action="{{ url(request()->route('type')) }}/bulk-store" data-redirect="/{{ request()->route('type') }}"  enctype="multipart/form-data">
    @csrf
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
                  {{-- <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" name="action" value="draft"><i data-feather='save'></i> Save as Draft</button> --}}
                  <button type="submit" class="btn btn-primary btn-sm submit-button" name="action" value="draft"><i data-feather="check-circle"></i> Process</button>
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
                          {{-- <div class="row align-items-center mb-1">
                            <div class="col-md-3"> 
                                <label class="form-label">{{$short_title}} No <span class="text-danger">*</span></label>  
                            </div>  
                            <div class="col-md-5"> 
                                <input type="text" name="document_number" class="form-control" id="document_number">
                            </div> 
                        </div>   --}}
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
                    </div> 
                </div> 
                </div>
        </div>

        <div class="card" id="item_section">
           <div class="card-body customernewsection-form"> 
            <div class="border-bottom mb-2 pb-25">
               <div class="row">
                <div class="col-md-6">
                    <div class="newheader"> 
                        {{-- <h4 class="card-title text-theme">{{$short_title}} Item Wise Detail</h4> --}}
                        <h4 class="card-title text-theme">Purchase Indent</h4>
                        {{-- <p class="card-text">Fill the details</p> --}}
                    </div>
                </div>
                {{-- <div class="col-md-6 text-sm-end">
                    <a href="javascript:;" id="deleteBtn" class="btn btn-sm btn-outline-danger me-50">
                        <i data-feather="x-circle"></i> Delete</a>
                </div>  --}}
                </div>
                <div class="row"> 
                    {{-- <div class="col">
						<div class="mb-1">
							<label class="form-label">Location</label>
							<input type="text" id="store_po" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type="hidden" id="store_id_po"></input>
						</div>
					</div> --}}
                    <div class="col" id="subLocation">
						<div class="mb-1">
							<label class="form-label">Sub Location</label>
							<input type="text" id="sub_store_po" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type="hidden" id="sub_store_id_po"></input>
						</div>
					</div>
                    <div class="col">
						<div class="mb-1">
							<label class="form-label">Requester</label>
							<input type="text" id="requester_po" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type="hidden" id="requester_id_po"></input>
						</div>
					</div>
					<div class="col">
						<div class="mb-1">
							<label class="form-label">Vendor</label>
							<input type="text" id="vendor_code_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type="hidden" id="vendor_id_qt_val"></input>
						</div>
					</div>
					{{-- <div class="col">
						<div class="mb-1">
							<label class="form-label">Series</label>
							<input type="text" id="book_code_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type="hidden" id="book_id_qt_val"></input>
						</div>
					</div> --}}
					<div class="col">
						<div class="mb-1">
							<label class="form-label">@if(request()->type == 'supplier-invoice') Doc @else Indent @endif No.</label>
							<input type="text" id="document_no_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type="hidden" id="document_id_qt_val"></input>
						</div>
					</div>
                    <div class="col">
						<div class="mb-1">
							<label class="form-label">Sales Order</label>
							<input type="text" id="pi_so_no_input_qt" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" value="">
							<input type="hidden" id="pi_so_qt_val"></input>
						</div>
					</div>
                    <div class="col">
						<div class="mb-1">
							<label class="form-label">Item</label>
							<input type="text" name="item_name_search" id="item_name_search" placeholder="Item Name/Code" class="form-control mw-100" autocomplete="off" value="">
						</div>
					</div>
					<div class="col mb-1">
						<label class="form-label">&nbsp;</label><br/>
						{{-- <button type="button" class="btn btn-primary btn-sm searchPiBtn"><i data-feather="search"></i> Search</button> --}}
						<button type="button" class="btn btn-warning btn-sm clearPiFilter"><i data-feather="x-circle"></i> Clear</button>
					</div>
                    
                   <div class="col-md-12">
                       <div class="table-responsive pomrnheadtffotsticky" style="height: 450px">
                           <table id="itemTable"  class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"> 
                            <thead>
                                <tr>
                                    <th class="customernewsection-form">
                                        <div class="form-check form-check-primary custom-checkbox">
                                            <input type="checkbox" class="form-check-input" id="Email">
                                            <label class="form-check-label" for="Email"></label>
                                        </div> 
                                    </th>
                                    <th>Indent No.</th>
                                    <th style="width:90px">Indent Date</th>
                                    <th style="width:120px">Item Code</th>
                                    <th style="max-width:300px">Item Name</th>
                                    <th>Attributes</th>
                                    <th>UOM</th>
                                    <th style="width:250px">Vendor</th>
                                    <th class="text-end" style="width:70px">Qty</th>
                                    <th class="text-end" style="width:70px">Rate</th>
                                    <th>Sales Order</th>
                                    <th>Location</th>
                                    <th>Sub Location</th>
                                    <th>Requester</th>
                                    <th>Remark</th> 
                                </tr>
                            </thead>
                            <tbody class="mrntableselectexcel">

                            </tbody>
                            </table>
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

@endsection
@section('scripts')
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
            vendorOnChange(itemId);
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
});

openPurchaseRequest();
function openPurchaseRequest()
{
    initializeAutocompleteQt("vendor_code_input_qt", "vendor_id_qt_val", "vendor_list", "vendor_code", "company_name");
    // initializeAutocompleteQt("book_code_input_qt", "book_id_qt_val", "book_pi", "book_code", "");
    initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "pi_document_qt", "book_code", "document_number");
    initializeAutocompleteQt("item_name_input_qt", "item_id_qt_val", "comp_item", "item_code", "item_name");
    // initializeAutocompleteQt("department_po", "department_id_po", "department", "name", "");
    // initializeAutocompleteQt("store_po", "store_id_po", "location", "store_name", "");
    initializeAutocompleteQt("pi_so_no_input_qt", "pi_so_qt_val", "pi_so_qt", "book_code", "document_number");
    initializeAutocompleteQt("requester_po", "requester_id_po", "all_user_list", "name", "");

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
                    store_id : $("#store_id").val() || '',
                    module_type : '{{request()->route('type')}}'
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            id: item.id,
                            label: `${item[labelKey1]}${labelKey2 ? (item[labelKey2] ? '-' + item[labelKey2] : '') : ''}`,
                            // label: `${item[labelKey1]} ${labelKey2 ? (item[labelKey2] ? '(' + item[labelKey2] + ')' : '') : ''}`,
                            code: item[labelKey1] || '', 
                            // is_sub_store : item?.sub_stores_count ? true:false,
                            // sub_stores_count : item?.sub_stores_count
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
            // if(ui.item.is_sub_store) {
            //     $("#subLocation").removeClass('d-none');
            // } else {
            //     $("#subLocation").addClass('d-none');
            // }
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
            getIndents();
            $(this).autocomplete("search", "");
        }
    });
}

function getLocation(locationId = '')
{
    let actionUrl = '{{ route("store.get") }}'+'?location_id='+locationId;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if(data.status == 200) {
                let options = '';
                data.data.locations.forEach(function(location) {
                    options+= `<option value="${location.id}">${location.store_name}</option>`;
                });
                $("[name='store_id']").empty().append(options);
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                });
            }
        });
    });
}

$(document).on('click', '.clearPiFilter', (e) => {
    $("#item_name_search").val('');
    $("#item_name_input_qt").val('');
    $("#item_id_qt_val").val('');
    // $("#department_po").val('');
    $("#department_id_po").val('');
    $("#store_po").val('');
    // $("#store_id_po").val('');
    $("#requester_po").val('');
    $("#requester_id_po").val('');
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
    getIndents();
});

// $(document).on('click', '.searchPiBtn', (e) => {
//     getIndents();
// });
setTimeout(() => {
    getIndents();
},100);

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
    let vendor_id = $("#vendor_id_qt_val").val() || '';
    let department_id = $("#department_id_po").val() || '';
    let store_id = $("#store_id").val() || '';
    // let store_id = $("#store_id_po").val() || '';
    let sub_store_id = $("#sub_store_id_po").val() || '';
    let requester_id = $("#requester_id_po").val() || '';
    let type = '{{ request()->route("type") }}';
    let actionUrl = '{{ route("po.get.pi.bulk", ["type" => ":type"]) }}'.replace(':type', type);
    let item_search = $("#item_name_search").val();
    let so_id = $("#pi_so_qt_val").val() || '';
    let fullUrl = `${actionUrl}?series_id=${encodeURIComponent(series_id)}&document_number=${encodeURIComponent(document_number)}&item_id=${encodeURIComponent(item_id)}&vendor_id=${encodeURIComponent(vendor_id)}&header_book_id=${encodeURIComponent(header_book_id)}&department_id=${encodeURIComponent(department_id)}&store_id=${encodeURIComponent(store_id)}&sub_store_id=${encodeURIComponent(sub_store_id)}&requester_id=${encodeURIComponent(requester_id)}&selected_pi_ids=${selectedPiIds}&document_date=${document_date}&item_search=${item_search}&so_id=${so_id}`;
    fetch(fullUrl).then(response => {
        return response.json().then(data => {
            $("#itemTable tbody").empty().append(data.data.pis);
            $(".select2").select2();
        });
    });
}

$(document).on('keyup', '#item_name_search', (e) => {
    getIndents();
});

// Checkbox code
$(document).on('change', '#itemTable th .form-check-input', (e) => {
    if(e.target.checked) {
        $("#itemTable tbody tr").each(function() {
            if ($(this).find("[name*='[vendor_id]']").val()) {
                $(this).find(".form-check-input").prop('checked', e.target.checked);
            }
        });
        if(!$("#itemTable tbody .form-check-input:checked").length) {
            e.target.checked = false;
            Swal.fire({
                title: 'Error!',
                text: "Please select vendor first.",
                icon: 'error',
            });
        }
    } else {
        $("#itemTable tbody .form-check-input").prop('checked', false);
    }
});

$(document).on('change', '#itemTable tbody .form-check-input', (e) => {
    let totalCheckboxes = $("#itemTable tbody .form-check-input").length;
    let checkedCheckboxes = $("#itemTable tbody .form-check-input:checked").length;
    if (checkedCheckboxes === totalCheckboxes) {
        $("#itemTable th .form-check-input").prop('checked', true);
    } else {
        $("#itemTable th .form-check-input").prop('checked', false);
    }
    let isVendorSelected = $(e.target).closest('tr').find("[name*='[vendor_id]']").val() || '';
    let isQtySelected = Number($(e.target).closest('tr').find("[name*='[qty]']").val()) || 0;
    let isRateSelected = Number($(e.target).closest('tr').find("[name*='[rate]']").val()) || 0;
    if(!isVendorSelected) {
        e.target.checked = false;
        Swal.fire({
            title: 'Error!',
            text: "Please select vendor first.",
            icon: 'error',
        });
    }
    if(!isQtySelected) {
        e.target.checked = false;
        Swal.fire({
            title: 'Error!',
            text: "Please update qty first.",
            icon: 'error',
        });
    }
    if(!isRateSelected) {
        e.target.checked = false;
        Swal.fire({
            title: 'Error!',
            text: "Please update rate first.",
            icon: 'error',
        });
    }
});

$(document).on("change", "#store_id", function (event, ui) {
    let storeId = ui?.item?.id || '';
    initializeAutocompleteQt("sub_store_po", "sub_store_id_po", "sub_store", "name", "");
});
if($("#store_id").length) {
    initializeAutocompleteQt("sub_store_po", "sub_store_id_po", "sub_store", "name", "");
}
</script>
@endsection