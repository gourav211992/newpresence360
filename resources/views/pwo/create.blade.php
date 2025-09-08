@extends('layouts.app')
@section('styles')
<style>
#analyzeModal .table-responsive {
    overflow-y: auto;
    max-height: 380px;
    position: relative;
}
#analyzeModal .po-order-detail {
    width: 100%;
    border-collapse: collapse;
}
#analyzeModal .po-order-detail thead {
    position: sticky;
    top: 0;
    background-color: white;
    z-index: 1;
}
#analyzeModal .po-order-detail th {
    background-color: #f8f9fa;
    text-align: left;
    padding: 8px;
}
#analyzeModal .po-order-detail td {
    padding: 8px;
}
</style>
@endsection
@section('content')
<form class="ajax-input-form" data-module="pwo" method="POST" action="{{ route('pwo.store') }}" data-redirect="{{ route('pwo.index') }}" enctype='multipart/form-data'>
    @csrf
<input type="hidden" name="show_attribute" value="0" id="show_attribute">
<input type="hidden" name="so_item_ids" id="so_item_ids">
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
                                 <div class="row align-items-center mb-1 d-none" id="reference_from"> 
                                    <div class="col-md-3"> 
                                        <label class="form-label">Reference from</label>  
                                    </div> 
                                    <div class="col-md-5 action-button"> 
                                        <button type="button" class="btn btn-outline-primary btn-sm mb-0 prSelect"><i data-feather="plus-square"></i> Sale Order</button>
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
                                        <h4 class="card-title text-theme">Product Details</h4>
                                        <p class="card-text">Fill the details</p>
                                    </div>
                                </div>
                                <div class="col-md-6 text-sm-end">
                                    <a href="javascript:;" class="btn btn-sm btn-outline-danger me-50" id="deleteBtn">
                                    <i data-feather="x-circle"></i> Delete</a>
                                    <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary">
                                    <i data-feather="plus"></i> Add Items</a>
                                </div>
                            </div>
                            </div>
                            <div class="table-responsive pomrnheadtffotsticky">
                            <table id="itemTable" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"
                            data-json-key="components_json"
                            data-row-selector="tr[id^='row_']">
                                <thead>
                                    <tr>
                                        <th>
                                        <div class="form-check form-check-primary custom-checkbox">
                                            <input type="checkbox" class="form-check-input" id="Email">
                                            <label class="form-check-label" for="Email"></label>
                                        </div>
                                        </th>
                                        <th width="150px">Item Code</th>
                                        <th width="300px">Item Name</th>
                                        <th>Attributes</th>
                                        <th >UOM</th>
                                        <th class="text-end">Quantity</th>
                                        <th width="200px">Customer</th>
                                        <th width="150px">SO No.</th>
                                        <th width="150px">Location</th>
                                    </tr>
                                </thead>
                                <tbody class="mrntableselectexcel">
                                </tbody>
                                <tfoot>
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
                                </tfoot>
                            </table>
                            </div>
                        </div>
                    </div>
                    {{-- Remark Sectionf --}}
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
            <button type="button" class="btn btn-primary submit_attribute">Select</button>
         </div>
      </div>
   </div>
</div>

@include('pwo.partials.so-modal')
@include('pwo.partials.analyze-modal')
@endsection
@section('scripts')
<script type="text/javascript">
    var getStockUrl = '{{route("pwo.get.stock")}}';
    var analyzeSoItemUrl = '{{ route("pwo.analyze.so-item") }}';
    var processSoItemUrl = '{{ route("pwo.process.so-item") }}';
</script>
<script type="text/javascript" src="{{asset('assets/js/modules/common-attr-ui.js')}}"></script>
<script type="text/javascript" src="{{asset('assets/js/modules/pwo.js')}}"></script>
<script type="text/javascript" src="{{asset('app-assets/js/file-uploader.js')}}"></script>
<script type="text/javascript">
$(function(){
   setTimeout(() => {
        $("#book_id").trigger('change');
   },0);
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

       let reference_from_service = parameters.reference_from_service;
        if(reference_from_service.length) {
            let c_bom = '{{\App\Helpers\ConstantHelper::SO_SERVICE_ALIAS}}';
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
   
});

/*Add New Row*/
function initializeAutocomplete2(selector, type) {
    $(selector).autocomplete({
        source: function(request, response) {
            let selectedAllItemIds = [];
            $("#itemTable tbody [id*='row_']").each(function(index, item) {
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
                    type:'header_item',
                    selectedAllItemIds : JSON.stringify(selectedAllItemIds)
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            id: item.id,
                            label: `${item.item_name} (${item.item_code})`,
                            code: item.item_code || '', 
                            name: item.item_name || '', 
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
            let itemName = ui.item.name;
            let itemId = ui.item.id;
            let uomId = ui.item.uom_id;
            let uomName = ui.item.uom_name;
            $input.val(itemCode);
            $input.closest('tr').find('[name*="[item_id]"]').val(itemId);
            $input.closest('tr').find('[name*="[item_code]"]').val(itemCode);
            $input.closest('tr').find('[name*="[item_name]"]').val(itemName);
            let uomOption = `<option value=${uomId}>${uomName}</option>`;
            $input.closest('tr').find('[name*="[uom_id]"]').empty().append(uomOption);
            $input.closest('tr').find('[name*="[attr_group_id]"]').remove();
            setTimeout(() => {
                if(ui.item.is_attr) {
                    $input.closest('tr').find('.attributeBtn').trigger('click');
                } else {
                    $input.closest('tr').find('[name*="[qty]"]').val('').focus();
                }
                }, 50);
            checkBomExist(itemId, $input.closest('tr'));
            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val("");
                $(this).closest('tr').find('[name*="[item_id]"]').val("");
                $(this).closest('tr').find('[name*="[item_code]"]').val("");
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
            $(this).closest('tr').find('[name*="[item_id]"]').val("");
            $(this).closest('tr').find('[name*="[item_code]"]').val("");
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
// CHeck bom exist
function checkBomExist(itemId, currentTr)
{
    let actionUrl = '{{route("bill.of.material.check.bom.exist")}}'+'?item_id='+itemId;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if(data.status == 404 || data.status == 422) {
                if(currentTr.length) {
                    currentTr.find("input[name*='component_item_name']").val('');
                    currentTr.find("input[name*='item_name']").val('');
                    $(this).closest('tr').find("td[id*='itemAttribute_']").html(defautAttrBtn);
                    currentTr.find("input[name*='item_id']").val('');
                    currentTr.find("input[name*='item_code']").val('');
                    currentTr.find("input[name*='attr_name']").remove();
                }
                Swal.fire({
                    title: 'Error!',
                    text: data.message,
                    icon: 'error',
                });
            }
        });
    })
}

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
       if(lastRow.find("[name*='[attr_name]']").length) {
          var emptyElements = lastRow.find("[name*='[attr_name]']").filter(function() {
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
    let componentAttr = [];
    if($("tr input[type='hidden'][name*='[attr_group_id]']").length) {
        $("tr input[type='hidden'][name*='[attr_group_id]']").each(function () {
            const nameAttr = $(this).attr("name");
            const value = $(this).val();
            const attributeIdMatch = nameAttr.match(/\[attr_group_id]\[(\d+)]/);
            const attributeId = attributeIdMatch ? attributeIdMatch[1] : null;
            componentAttr.push({
                'attr_name' : attributeId,
                'attr_value' : value
            });
        });
    }
    let store_id = $("#store_id").val() || '';
    let actionUrl = '{{route("pwo.item.row")}}'+'?count='+rowsLength+'&component_item='+JSON.stringify(lastTrObj)+'&comp_attr='+JSON.stringify(componentAttr)+`&store_id=${store_id}`; 
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                if (rowsLength) {
                    $("#itemTable > tbody > tr:last").after(data.data.html);
                } else {
                    $("#itemTable > tbody").html(data.data.html);
                }
                updateRowIndex(false);
                initializeAutocomplete2(".comp_item_code");
                initializeAutocompleteCustomer("[name*='[customer_code]']");
                initStoreAutocomplete("#itemTable");
               // $(".prSelect").prop('disabled',true);
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

function initializeAutocompleteCustomer(selector) {
    $(selector).autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type:'customer'
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            id: item.id,
                            label: `${item.company_name} (${item.customer_code})`,
                            code: item.customer_code || '',
                            name:item.display_name || item.company_name,
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
            var customerCode = ui.item.code;
            var customerName = ui.item.name;
            var customerId = ui.item.id;
            $input.val(customerCode);
            $input.closest('tr').find("[name*='[customer_id]']").val(customerId)
            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val("");
                $(this).closest('tr').find("[name*='[customer_id]']").val("");
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
            $(this).val("");
            $(this).closest('tr').find("[name*='[customer_id]']").val("");
        }
    });
}

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
        let rowCount = tr.getAttribute('data-index');
        getItemAttribute(item_id, rowCount, selectedAttr, tr);
    } else {
        alert("Please select first item name.");
    }
});

/*For comp attr*/
function getItemAttribute(itemId, rowCount, selectedAttr, tr) {
    let pwo_so_mapping_id = "";
    let soItemId = $(tr).find("input[name*='so_item_id']").val() || '';
    if(soItemId) {
        pwo_so_mapping_id = "1";
    }
    let actionUrl = '{{route("pwo.item.attr")}}'+'?item_id='+itemId+'&pwo_so_mapping_id='+pwo_so_mapping_id+`&rowCount=${rowCount}&selectedAttr=${selectedAttr}`;
    fetch(actionUrl).then(response => {
        return response.json().then(data => {
            if (data.status == 200) {
                  $("#attribute tbody").empty();
                  $("#attribute table tbody").append(data.data.html);
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
function fetchItemDetails(currentTr) {
    let pName = $(currentTr).find("[name*='component_item_name']").val();
    let itemId = $(currentTr).find("[name*='item_id']").val();

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

        let actionUrl = `{{route("pwo.get.itemdetail")}}?item_id=${itemId}&selectedAttr=${JSON.stringify(selectedAttr)}&remark=${remark}&section_name=${sectionName}&sub_section_name=${subSectionName}&station_name=${stationName}&qty_per_unit=${qty_per_unit}&total_qty=${total_qty}&std_qty=${std_qty}`;
        fetch(actionUrl).then(response => {
            return response.json().then(data => {
                if (data.status == 200) {
                    $(".item_detail_row").remove();
                    if ($("#itemDetailTable tbody tr").length > 2) {
                        $("#itemDetailTable tbody tr").slice(-2).remove();
                    }
                    $("#itemDetailTable tbody tr:first").after(data.data.html);
                }
            });
        });
    }
}

// Attach event listener
$(document).on('input change focus', '#itemTable tr input', function(e) {
    let currentTr = e.target.closest('tr'); 
    fetchItemDetails(currentTr);
});

$(document).on('click', '.submit_attribute', (e) => {
    $("#attribute").modal('hide');
});

window.onload = function () {
    localStorage.removeItem('selectedSoItemIds');
};

/*Open Pr model*/
$(document).on('click', '.prSelect', (e) => {
    $("#prModal").modal('show');
    openBomRequest();
    getPwo();
});

$(document).on('change', '#attributeCheck', (e) => {
    if(e.target.checked) {
        $("#show_attribute").val(1);
    } else {
        $("#show_attribute").val(0);
    }
    getPwo();
});

$(document).on('click', '.clearPiFilter', (e) => {
    $("#item_name_search").val('');
    $("#book_code_input_qt").val('');
    $("#book_id_qt_val").val('');
    $("#document_no_input_qt").val('');
    $("#document_id_qt_val").val('');
    $("#customer_code_input_qt").val('');
    $("#customer_id_qt_val").val('');
    getPwo();
});

function openBomRequest()
{
    initializeAutocompleteQt("customer_code_input_qt", "customer_id_qt_val", "customer", "customer_code", "company_name");
    initializeAutocompleteQt("book_code_input_qt", "book_id_qt_val", "book_so", "book_code", "");
    initializeAutocompleteQt("item_name_input_qt", "item_id_qt_val", "header_item", "item_code", "item_name");

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
                    header_book_id : $("#book_id").val()
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
            getPwo();
            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $(this).val("");
                $("#" + selectorSibling).val("");
            }
            getPwo();
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
        }
        getPwo();
    });
}

$(document).on('keyup', '#item_name_search', (e) => {
    getPwo();
});
$(document).on('keyup', '#document_no_input_qt', (e) => {
    getPwo();
});

function getPwo() 
{
    let isAttribute = 0;
    if($("#attributeCheck").is(':checked')) {
        isAttribute = 1;
    } else {
        isAttribute = 0;
    }
    let selectedPiIds = localStorage.getItem('selectedSoItemIds') ?? '[]';
    selectedPiIds = JSON.parse(selectedPiIds);
    selectedPiIds = encodeURIComponent(JSON.stringify(selectedPiIds));
    let header_book_id = $("#book_id").val() || '';
    let series_id = $("#book_id_qt_val").val() || '';
    let document_number = $("#document_no_input_qt").val() || '';
    let item_id = $("#item_id_qt_val").val() || '';
    let actionUrl = '{{ route("pwo.get.so.item") }}';
    let customerId = $("#customer_id_qt_val").val() || '';
    let item_search = $("#item_name_search").val() || '';
    let storeId = $("#store_id").val() || '';
    let fullUrl = `${actionUrl}?series_id=${encodeURIComponent(series_id)}&document_number=${encodeURIComponent(document_number)}&item_id=${encodeURIComponent(item_id)}&header_book_id=${encodeURIComponent(header_book_id)}&customer_id=${customerId}&item_search=${item_search}+&selected_so_item_ids=${selectedPiIds}&is_attribute=${isAttribute}&store_id=${storeId}`;
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
</script>
@endsection