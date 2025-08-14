@extends('layouts.app')
@section('styles')
    <style>
        .tooltip-inner { text-align: left}
    </style>
@endsection
@section('content')
    <form id="mrnEditForm" class="ajax-input-form" method="POST" action="{{ route('put-away.update', $mrn->id) }}" data-redirect="/put-away" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="tax_required" id="tax_required" value="">
        <div class="app-content content ">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">
                                        Put Away
                                    </h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="/">Home</a>
                                            </li>
                                            <li class="breadcrumb-item active">Edit</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <input type="hidden" name="document_status" value="{{$mrn->document_status}}" id="document_status">
                                <button type="button" onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0">
                                    <i data-feather="arrow-left-circle"></i> Back
                                </button>
                                <a href="{{ route('material-receipt.print-labels', $mrn->id) }}" target="_blank" class="btn btn-dark btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" class="feather feather-printer">
                                        <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                        <rect x="6" y="14" width="12" height="8"></rect>
                                    </svg>
                                    Print Labels
                                </a>
                                <button type="submit" class="btn btn-primary btn-sm submit-button" name="action" value="submitted">
                                    <i data-feather="check-circle"></i> Submit
                                </button>
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
                                                <span class="badge rounded-pill badge-light-{{$mrn->display_status === 'Posted' ? 'info' : 'secondary'}} forminnerstatus">
                                                    <span class = "text-dark" >Status</span> : <span class="{{$docStatusClass}}">{{$mrn->display_status}}</span>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Series <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="hidden" name="book_id" class="form-control" id="book_id" value="{{$mrn->book_id}}" readonly>
                                                        <input readonly type="text" class="form-control" value="{{$mrn->book->book_code}}" id="book_code">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Document No <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" readonly value="{{@$mrn->document_number}}" id="document_number">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Document Date <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" id="document_date" name="document_date" class="form-control" value="{{ date('Y-m-d', strtotime($mrn->document_date)) }}" >
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Location <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" readonly value="{{@$mrn->erpStore->store_name}}">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Store <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" readonly value="{{$mrn?->erpSubStore?->name}}">
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
                                                        <h4 class="card-title text-theme">Item Wise Detail</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-sm-end">
                                                    <a href="javascript:;" id="deleteBtn" class="btn btn-sm btn-outline-danger me-50">
                                                        <i data-feather="x-circle"></i> Delete
                                                    </a>
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
                                                                <th class="text-end">GRN Qty</th>
                                                                <th class="text-end">Acpt. Qty</th>
                                                                <th width="50px">Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                            @include('procurement.put-away.partials.item-row-edit')
                                                        </tbody>
                                                        <tfoot>
                                                            <tr valign="top">
                                                                <td rowspan="10" colspan="8">
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
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-md-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Final Remarks</label>
                                                            <textarea type="text" rows="4" name="remarks" class="form-control" placeholder="Enter Remarks here...">{!! $mrn->final_remarks !!}</textarea>
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
        <div class="modal fade" id="edit-address" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
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
                    {{--
                    <p class="text-center">Enter the details below.</p>
                    --}}
                    <div class="row mt-2">
                        <div class="col-md-12 mb-1">
                            <label class="form-label">Remarks</label>
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

    {{-- Add Storage Point modal--}}
    @include('procurement.put-away.partials.storage-point-modal')

@endsection
@section('scripts')
    <script type="text/javascript" src="{{asset('assets/js/modules/putaway.js')}}"></script>
    <script>
        /*Clear local storage*/
        setTimeout(() => {
            localStorage.removeItem('deletedItemLocationIds');
            localStorage.removeItem('deletedMrnItemIds');
        },0);
        @if(($mrn->document_status != 'draft') && ($mrn->document_status != 'rejected'))
            $(':input').prop('readonly', true);
            $('textarea[name="amend_remark"], input[type="file"][name="amend_attachment[]"]').prop('readonly', false).prop('disabled', false);
            $('select').not('.amendmentselect select').prop('disabled', true);
            $("#deleteBtn").remove();
            $(".editAddressBtn").remove();
            $(document).on('show.bs.modal', function (e) {
                if(e.target.id != 'approveModal') {
                    if(e.target.id != 'shortCloseModal') {
                        $(e.target).find('.modal-footer').remove();
                    }
                    $('select').not('.amendmentselect select').prop('disabled', true);
                }
                if(e.target.id == 'approveModal') {
                    $(e.target).find(':input').prop('readonly', false);
                    $(e.target).find('select').prop('readonly', false);
                }
                $('.add-contactpeontxt').remove();
                
            });
        @endif
        // Change BookId
        $(document).on('change','#book_id', (e) => {
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
                    // console.log(data.data);
                    if (data.status == 200) {
                        const parameters = data.data.parameters;
                        // console.log('parameters', parameters);
                        setServiceParameters(parameters);

                        if(parameters?.tax_required.some(val => val.toLowerCase() === 'yes')) {
                            $("#tax_required").val(parameters?.tax_required[0]);
                        } else {
                            $("#tax_required").val("");
                        }
                        
                    }
                    if(data.status == 404) {
                        $("#book_code").val('');
                        $("#document_number").val('');
                        $("#tax_required").val("");
                        const docDateInput = $("[name='document_date']");
                        docDateInput.removeAttr('min');
                        docDateInput.removeAttr('max');
                        docDateInput.val(new Date().toISOString().split('T')[0]);
                    }
                });
            });
        }

        /*for trigger on edit cases*/
        setTimeout(() => {
            let bookId = $("#book_id").val();
            getDocNumberByBookId(bookId);
        },1000);

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
        }

        /*Add New Row*/
        // for component item code
        function initializeAutocomplete2(selector, type) {
            $(selector).autocomplete({
                source: function(request, response) {
                    let selectedAllItemIds = [];
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
                            type:'goods_item_list',
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
                minLength: 0,
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
                    $input.val(itemCode);
                    let closestTr = $input.closest('tr');
                    closestTr.find('[name*=item_id]').val(itemId);
                    closestTr.find('[name*=item_code]').val(itemCode);
                    closestTr.find('[name*=item_name]').val(itemN);
                    closestTr.find('[name*=hsn_id]').val(hsnId);
                    closestTr.find('[name*=hsn_code]').val(hsnCode);
                    let uomOption = `<option value=${uomId}>${uomName}</option>`;
                    if(ui.item?.alternate_u_o_ms) {
                        for(let alterItem of ui.item.alternate_u_o_ms) {
                        uomOption += `<option value="${alterItem.uom_id}" ${alterItem.is_purchasing ? 'selected' : ''}>${alterItem.uom?.name}</option>`;
                        }
                    }
                    closestTr.find('[name*=uom_id]').append(uomOption);
                    closestTr.find('.attributeBtn').trigger('click');
                    let price = 0;
                    let transactionType = 'collection';
                    let partyCountryId = $("#hidden_country_id").val();
                    let partyStateId = $("#hidden_state_id").val();
                    let rowCount = Number(closestTr.attr('data-index'));
                    let queryParams = new URLSearchParams({
                        price: price,
                        item_id: itemId,
                        transaction_type: transactionType,
                        party_country_id: partyCountryId,
                        party_state_id: partyStateId,
                        rowCount : rowCount
                    }).toString();
                    getItemDetail(closestTr);
                    let storeLocation = $('.header_store_id').val();
                    updateItemStores();
                    getSubStores(storeLocation, itemId);
                    setTimeout(() => {
                        if(ui.item.is_attr) {
                            $input.closest('tr').find('.attributeBtn').trigger('click');
                        } else {
                            $input.closest('tr').find('.attributeBtn').trigger('click');
                            $input.closest('tr').find('[name*="[order_qty]"]').focus();
                        }
                    }, 100);
                    initializeStationAutocomplete();
                    getItemCostPrice($input.closest('tr'));
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
        initializeAutocomplete2(".comp_item_code");

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
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select first item name.',
                    icon: 'error',
                });
                return false;
            }
        });

        /*For comp attr*/
        function getItemAttribute(itemId, rowCount, selectedAttr, tr){
            let actionUrl = '{{route("put-away.item.attr")}}'+'?item_id='+itemId+`&rowCount=${rowCount}&selectedAttr=${selectedAttr}`;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        $("#attribute tbody").empty();
                        $("#attribute table tbody").append(data.data.html)
                        $(tr).find('td:nth-child(2)').find("[name*=attr_name]").remove();
                        $(tr).find('td:nth-child(2)').append(data.data.hiddenHtml);
                        if (data.data.attr) {
                            $("#attribute").modal('show');
                            $(".select2").select2();
                        }
                    }
                });
            });
        }

        /*Display item detail*/
        $(document).on('change focus', '#itemTable tr input ', function(e){
            let currentTr = e.target.closest('tr');
            getItemDetail(currentTr);
        });

        function getItemDetail(currentTr) {
            let pName = $(currentTr).find("[name*='component_item_name']").val() || '';
            let itemId = $(currentTr).find("[name*='item_id']").val() || '';
            let mrnHeaderId = $(currentTr).find("[name*='mrn_header_id']").val() || '';
            let mrnDetailId = $(currentTr).find("[name*='mrn_detail_id']").val() || '';
            let storeId = $("[name='header_store_id']").val() || '';
            let subStoreId = $("[name='sub_store_id']").val() || '';
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
                let qty = $(currentTr).find("[name*='[accepted_qty]']").val() || '';
                let headerId = $(currentTr).find("[name*='putaway_header_id']").val() ?? '';
                let detailId = $(currentTr).find("[name*='putaway_detail_id']").val() ?? '';
                let actionUrl = '{{route("put-away.get.itemdetail")}}'+'?item_id='+itemId+
                    '&mrn_header_id='+mrnHeaderId+
                    '&mrn_detail_id='+mrnDetailId+
                    '&selectedAttr='+JSON.stringify(selectedAttr)+
                    '&remark='+remark+
                    '&uom_id='+uomId+
                    '&qty='+qty+
                    '&headerId='+headerId+
                    '&detailId='+detailId+
                    '&store_id='+storeId+
                    '&sub_store_id='+subStoreId;

                fetch(actionUrl).then(response => {
                    return response.json().then(data => {
                        if (data.status == 200) {
                            const storagePoints = data.storagePoints?.data || [];
                            console.log('storagePoints', storagePoints);
                            // Store in global map (if needed for other logic)
                            itemStorageMap[itemId] = storagePoints;

                            // ✅ Store globally for dropdown population
                            allStoragePointsList = storagePoints;
                            console.log('allStoragePointsList', allStoragePointsList);
                            // Update the modal or display section
                            $("#itemDetailDisplay").html(data.data.html);

                            // ✅ Fill storage_points hidden input
                            const hiddenInput = $(currentTr).find("input[name*='[storage_points]']");
                            if (hiddenInput.length) {
                                hiddenInput.val(JSON.stringify(storagePoints));
                            }
                        }
                    });
                });
            }
        }
    </script>
@endsection
