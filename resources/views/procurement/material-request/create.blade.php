@extends('layouts.app')
@section('content')
<form class="ajax-input-form" method="POST" action="{{ route('material-request.store') }}" data-redirect="/material-request" enctype="multipart/form-data">
    <input type="hidden" name="tax_required" id="tax_required" value="">
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
                                <h2 class="content-header-title float-start mb-0">Material Request</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item">
                                            <a href="/">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">Add New</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                        <input type="hidden" name="document_status" value="draft" id="document_status">
                            <button type="button" onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0">
                                <i data-feather="arrow-left-circle"></i> Back
                            </button>
                            <button type="button" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" id="save-draft-button" name="action" value="draft">
                                <i data-feather='save'></i> Save as Draft
                            </button>
                            <button type="button" class="btn btn-primary btn-sm submit-button" id="submit-button" name="action" value="submitted">
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
                                                        <option value="{{$book->id}}">{{ucfirst($book->book_code)}}</option>
                                                        @endforeach
                                                    </select>
                                                    <!-- <input type="hidden" name="mrn_no" id="book_code"> -->
                                                    <input type="hidden" name="book_code" id="book_code">
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Material Request No <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="document_number" class="form-control" id="document_number">
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Material Request Date <span class="text-danger">*</span></label>
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
                                            {{-- <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Store<span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="store_id" name="store_id">
                                                            <option value="">Select</option>
                                                            @foreach ($stores as $store)
                                                                <option value="{{ $store->id }}">
                                                                    {{ ucfirst($store->store_code) }}</option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" name="store_code" id="store_code">
                                                    </div>
                                            </div> --}}
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
                                                    <h4 class="card-title text-theme">Material Request Item Wise Detail</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                            </div>
                                            <div class="col-md-6 text-sm-end">
                                                <a href="javascript:;" id="deleteBtn" class="btn btn-sm btn-outline-danger me-50">
                                                <i data-feather="x-circle"></i> Delete</a>
                                                <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary">
                                                <i data-feather="plus"></i> Add New Item</a>
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
                                                            <th width="200px">Item</th>
                                                            <th>Attributes</th>
                                                            <th>UOM</th>
                                                            <th>Qty</th>
                                                            <th>Rate</th>
                                                            <th class="text-end">Value</th>
                                                            <th class="text-end">Discount</th>
                                                            <th class="text-end">Total</th>
                                                            <th width="100px">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody class="mrntableselectexcel">
                                                    </tbody>
                                                    <tfoot>
                                                        <tr class="totalsubheadpodetail">
                                                            <td colspan="6"></td>
                                                            <td class="text-end" id="totalItemValue">0.00</td>
                                                            <td class="text-end" id="totalItemDiscount">0.00</td>
                                                            <td class="text-end" id="TotalEachRowAmount">0.00</td>
                                                        </tr>
                                                        <tr valign="top">
                                                            <td colspan="8" rowspan="12">
                                                                <table class="table border" id="itemDetailDisplay">
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
                                                                </table>
                                                            </td>
                                                            <td colspan="2">
                                                                <table class="table border mrnsummarynewsty">
                                                                    <tr>
                                                                        <td colspan="2" class="p-0">
                                                                            <h6 class="text-dark mb-0 bg-light-primary py-1 px-50 d-flex justify-content-between">
                                                                                <strong>Material Request Summary</strong>
                                                                                <div class="addmendisexpbtn">
                                                                                    <button class="btn p-25 btn-sm btn-outline-secondary summaryDisBtn"><i data-feather="plus"></i> Discount</button>
                                                                                    <button class="btn p-25 btn-sm btn-outline-secondary summaryExpBtn"><i data-feather="plus"></i> Expenses</button>
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
                                                                        <td><strong>Exp.</strong></td>
                                                                        <td class="text-end" id="f_exp">0.00</td>
                                                                        <input type="hidden" name="expense_amount" class="text-end" id="expense_amount">
                                                                    </tr>
                                                                    <tr class="voucher-tab-foot">
                                                                        <td class="text-primary"><strong>Total After Exp.</strong></td>
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
                                            <div class="row mt-2">
                                                <div class="col-md-12">
                                                    <div class="col-md-4">
                                                        <div class="mb-1">
                                                            <label class="form-label">Upload Document</label>
                                                            <input type="file" name="attachment[]" class="form-control" multiple>
                                                            <span class = "text-primary small">{{__("message.attachment_caption")}}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-12">
                                                    <div class="mb-1">
                                                        <label class="form-label">Final Remarks</label>
                                                        <textarea type="text" rows="4" name="remarks" class="form-control" placeholder="Enter Remarks here..."></textarea>
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
    @include('procurement.material-request.partials.summary-disc-modal')
    {{-- Add expenses modal--}}
    @include('procurement.material-request.partials.summary-exp-modal')
    {{-- Edit Address --}}
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
                                <input type="text" id="new_item_dis_name" class="form-control mw-100" />
                            </td>
                            <td>
                                <input step="any" type="number" id="new_item_dis_perc" class="form-control mw-100" />
                            </td>
                            <td>
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
{{-- Taxes --}}
@include('procurement.material-request.partials.tax-detail-modal')
@endsection
@section('scripts')
    <script type="text/javascript">
        let actionUrlTax = '{{route("material-request.tax.calculation")}}';
    </script>
    <script type="text/javascript" src="{{asset('assets/js/modules/mr.js')}}"></script>
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
        }

        /*Add New Row*/
        $(document).on('click','#addNewItemBtn', (e) => {
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
                        let rowCount = Number($($input).closest('tr').attr('data-index'));
                        let queryParams = new URLSearchParams({
                            price: price,
                            item_id: itemId,
                            transaction_type: transactionType,
                            rowCount : rowCount
                        }).toString();
                        getItemDetail(closestTr);
                        // initializeStationAutocomplete();
                        // taxHidden(queryParams);
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
                let item_id = lastRow.find("[name*='item_id']").val();
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

            let actionUrl = '{{route("material-request.item.row")}}'+'?count='+rowsLength+'&component_item='+JSON.stringify(lastTrObj);
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                                // $("#submit-button").click();
                        if (rowsLength) {
                            $("#itemTable > tbody > tr:last").after(data.data.html);
                        } else {
                            $("#itemTable > tbody").html(data.data.html);
                        }
                        initializeAutocomplete2(".comp_item_code");
                        // initializeStationAutocomplete();
                    } else if(data.status == 422) {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'An unexpected error occurred.',
                            icon: 'error',
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Someting went wrong!',
                            icon: 'error',
                        });
                    }
                });
            });
        });


        function taxHidden(queryParams)
        {
            let actionUrl = '{{route("material-request.tax.calculation")}}';
            let urlWithParams = `${actionUrl}?${queryParams}`;
            fetch(urlWithParams).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        $(`#itemTable #row_${data.data.rowCount}`).find("[name*='t_type']").remove();
                        $(`#itemTable #row_${data.data.rowCount}`).find("[name*='t_perc']").remove();
                        $(`#itemTable #row_${data.data.rowCount}`).find("[name*='t_value']").remove();
                        $(`#itemTable #row_${data.data.rowCount}`).find("[name*='item_total_cost']").after(data.data.html);
                        setTableCalculation();

                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.error || 'An unexpected error occurred.',
                            icon: 'error',
                        });
                        return false;
                    }
                });
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
                    $(`#row_${item}`).remove();
                });
                setTableCalculation();
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: "Please first add & select row item.",
                    icon: 'error',
                });
                return false;
            }
            if(!$("[id*='row_']").length) {
                $("#itemTable > thead .form-check-input").prop('checked',false);
            }
        });

        /*Check box check and uncheck*/
        $(document).on('change','#itemTable > thead .form-check-input',(e) => {
            if (e.target.checked) {
                $("#itemTable > tbody .form-check-input").each(function(){
                    $(this).prop('checked',true);
                });
            } else {
                $("#itemTable > tbody .form-check-input").each(function(){
                    $(this).prop('checked',false);
                });
            }
        });
        $(document).on('change','#itemTable > tbody .form-check-input',(e) => {
            if(!$("#itemTable > tbody .form-check-input:not(:checked)").length) {
                $('#itemTable > thead .form-check-input').prop('checked', true);
            } else {
                $('#itemTable > thead .form-check-input').prop('checked', false);
            }
        });

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
                    text: "Please select first item name.",
                    icon: 'error',
                });
            }
        });

        /*For comp attr*/
        function getItemAttribute(itemId, rowCount, selectedAttr, tr){
            let actionUrl = '{{route("material-request.item.attr")}}'+'?item_id='+itemId+`&rowCount=${rowCount}&selectedAttr=${selectedAttr}`;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        $("#attribute tbody").empty();
                        $("#attribute table tbody").append(data.data.html)
                        $("#attribute").modal('show');
                        $(tr).find('td:nth-child(2)').find("[name*=attr_name]").remove();
                        $(tr).find('td:nth-child(2)').append(data.data.hiddenHtml)
                    }
                });
            });
        }

        /*Display item detail*/
        $(document).on('input change focus', '#itemTable tr input ', function(e){
            let currentTr = e.target.closest('tr');
            getItemDetail(currentTr);
        });

        function getItemDetail(currentTr) {
            let pName = $(currentTr).find("[name*='component_item_name']").val();
            let itemId = $(currentTr).find("[name*='item_id']").val();
            let poHeaderId = $(currentTr).find("[name*='purchase_order_id']").val();
            let poDetailId = $(currentTr).find("[name*='po_detail_id']").val();
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
                let qty = $(currentTr).find("[name*='[quantity]']").val() || '';
                let headerId = $(currentTr).find("[name*='mr_header_id']").val() ?? '';
                let detailId = $(currentTr).find("[name*='mr_detail_id']").val() ?? '';
                let actionUrl = '{{route("material-request.get.itemdetail")}}'+'?item_id='+itemId+'&selectedAttr='+JSON.stringify(selectedAttr)+'&remark='+remark+'&uom_id='+uomId+'&qty='+qty+'&headerId='+headerId+'&detailId='+detailId;
                fetch(actionUrl).then(response => {
                    return response.json().then(data => {
                        if(data.status == 200) {
                            // let itemStoreData = JSON.parse($(currentTr).find("[id*='components_stores_data']").val() || "[]");
                            // console.log('itemStoreData....', itemStoreData);
                            // ledgerStock(currentTr, itemId, selectedAttr, itemStoreData);
                            $("#itemDetailDisplay").html(data.data.html);
                            // initializeStationAutocomplete();
                        }
                    });
                });
            }
        }

        /*Tbl row highlight*/
        $(document).on('click', '.mrntableselectexcel tr', (e) => {
            $(e.target.closest('tr')).addClass('trselected').siblings().removeClass('trselected');
        });
        $(document).on('keydown', function(e) {
            if (e.which == 38) {
                /*bottom to top*/
                $('.trselected').prev('tr').addClass('trselected').siblings().removeClass('trselected');
            } else if (e.which == 40) {
                /*top to bottom*/
                $('.trselected').next('tr').addClass('trselected').siblings().removeClass('trselected');
            }
            if($('.trselected').length) {
                // $('html, body').scrollTop($('.trselected').offset().top - 200);
            }
        });
    </script>
@endsection
