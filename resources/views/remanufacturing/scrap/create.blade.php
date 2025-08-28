@extends('layouts.app')
@section('styles')
    <style>
        #psModal .table-responsive {
            overflow-y: auto;
            max-height: 300px;
            /* Set the height of the scrollable body */
            position: relative;
        }

        #psModal .ps-order-detail {
            width: 100%;
            border-collapse: collapse;
        }

        #psModal .ps-order-detail thead {
            position: sticky;
            top: 0;
            /* Stick the header to the top of the table container */
            background-color: white;
            /* Optional: Make sure header has a background */
            z-index: 1;
            /* Ensure the header stays above the body content */
        }

        #psModal .ps-order-detail th {
            background-color: #f8f9fa;
            /* Optional: Background for the header */
            text-align: left;
            padding: 8px;
        }

        #psModal .ps-order-detail td {
            padding: 8px;
        }
    </style>
@endsection
@section('content')
    <form class="ajax-input-form" data-module="scrap" method="POST" action="{{ route('scrap.store') }}"
        data-redirect="{{ route('scrap.index') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="show_attribute" value="0" id="show_attribute">
        <input type="hidden" name="pslip_id" id="pslip_id">
        <input type="hidden" name="ps_item_ids" id="ps_item_ids">

        <input type="hidden" name="pull_item_type" id="pull_item_type">
        <input type="hidden" name="ro_id" id="ro_id">
        <input type="hidden" name="ro_item_ids" id="ro_item_ids">

        <input type="hidden" name="item_ids" id="item_ids">
        <div class="app-content content">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Scrap</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="index.html">Home</a>
                                            </li>
                                            <li class="breadcrumb-item active">Add New</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <input type="hidden" name="document_status" id="document_status">
                                <button type="button" onClick="javascript: history.go(-1)"
                                    class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i>
                                    Back</button>
                                <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button"
                                    name="action" value="draft"><i data-feather='save'></i> Save as Draft</button>
                                <button type="submit" class="btn btn-primary btn-sm submit-button" name="action"
                                    value="submitted"><i data-feather="check-circle"></i> Submit</button>
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
                                                <div
                                                    class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                                    <div>
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Series <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="book_id" name="book_id">
                                                            @foreach ($books as $book)
                                                                <option value="{{ $book->id }}">
                                                                    {{ ucfirst($book->book_code) }}</option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" name="book_code" id="book_code">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Scrap No <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="document_number" class="form-control"
                                                            id="document_number">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Scrap Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" class="form-control"
                                                            value="{{ date('Y-m-d') }}" name="document_date"
                                                            min = "{{ $current_financial_year['start_date'] }}"
                                                            max = "{{ $current_financial_year['end_date'] }}">
                                                    </div>
                                                </div>
                                                {{-- <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Reference No </label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="reference_number" class="form-control">
                                                    </div>
                                                </div> --}}


                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Location <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="store_id"
                                                            id="store_id" onchange="getSubStores(this.value)">
                                                            <option value="">Select Location</option>
                                                            @foreach ($locations as $location)
                                                                <option value="{{ $location->id }}">
                                                                    {{ $location->store_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1 sub-store-row d-none">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Sub Store <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2 sub_store" id="sub_store_id"
                                                            name="sub_store_id"></select>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1 d-none" id="reference_from">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Reference from</label>
                                                    </div>
                                                    <div class="col-md-5 action-button">
                                                        <button type="button"
                                                            class="btn btn-outline-primary btn-sm mb-0 psSelect"><i
                                                                data-feather="plus-square"></i> Production Slip</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body customernewsection-form">
                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader">
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="step-custhomapp bg-light">
                                            <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                                                <li class="nav-item">
                                                    <a class="nav-link active" data-bs-toggle="tab"
                                                        href="#scavengingItems">
                                                        Scrap Items
                                                    </a>
                                                </li>
                                                {{-- <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#repairOrderTab">
                                                        Repair Orders
                                                    </a>
                                                </li> --}}
                                                <li class="nav-item">
                                                    <a class="nav-link" data-bs-toggle="tab" href="#productionSlip">
                                                        Production Slips
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="tab-content pb-1">
                                            <div class="tab-pane active" id="scavengingItems">
                                                <div class="text-end mb-50">
                                                    <a href="javascript:;" id="deleteBtn"
                                                        class="btn btn-sm btn-outline-danger me-50">
                                                        <i data-feather="x-circle"></i> Delete</a>
                                                    <a href="javascript:;" id="addNewItemBtn"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i data-feather="plus"></i> Add Item</a>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="table-responsive pomrnheadtffotsticky">
                                                            <table id="scavengingItemsTable"
                                                                class="ItemsTable table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"
                                                                data-json-key="components_json"
                                                                data-row-selector="tr[id^='row_']">
                                                                <thead id="scavengingItemsThead">
                                                                    <tr>
                                                                        <th width="62"
                                                                            class="customernewsection-form">
                                                                            <div
                                                                                class="form-check form-check-primary custom-checkbox">
                                                                                <input type="checkbox"
                                                                                    class="form-check-input"
                                                                                    id="Email" />
                                                                                <label class="form-check-label"
                                                                                    for="Email"></label>
                                                                            </div>
                                                                        </th>
                                                                        <th width="285">Item Code</th>
                                                                        <th width="208">Item Name</th>
                                                                        <th>Attributes</th>
                                                                        <th>UOM</th>
                                                                        <th>Qty</th>
                                                                        <th>Cost Center</th>
                                                                        <th>Remark</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="mrntableselectexcel"
                                                                    id="scavengingItemsTbody">
                                                                </tbody>
                                                                <tfoot id="scavengingItemsTfoot">
                                                                    <tr valign="top">
                                                                        <td colspan="8" rowspan="10">
                                                                            <table class="table border">
                                                                                <tr>
                                                                                    <td class="p-0">
                                                                                        <h6
                                                                                            class="text-dark mb-0 bg-light-primary py-1 px-50">
                                                                                            <strong>Item Details</strong>
                                                                                        </h6>
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
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="tab-pane" id="productionSlip">
                                                <div class="table-responsive pomrnheadtffotsticky">
                                                    <table id="productionSlipsTable"
                                                        class="ItemsTable table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th>Slip Date</th>
                                                                <th>Slip No.</th>
                                                                <th>Item Code</th>
                                                                <th>Item Name</th>
                                                                <th>Attributes</th>
                                                                <th>UOM</th>
                                                                <th>Qty</th>
                                                                <th id="uidTh">UID</th>
                                                                <th>Remark</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                        </tbody>
                                                        <tfoot id="productionSlipsTfoot">
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mt-2">
                                            <div class="col-md-12">
                                                <div class="col-md-4">
                                                    <div class="mb-1">
                                                        <label class="form-label">Upload Document</label>
                                                        <input type="file" class="form-control" name="attachment" id="document-upload" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">Final Remarks</label>
                                                    <textarea type="text" rows="4" class="form-control" name="document_remarks" id="document_remarks" placeholder="Enter Remarks here..."></textarea>
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
                        <table class="mt-1 table myrequesttablecbox table-striped ps-order-detail custnewpo-detail">
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
                    <button type="button" {{-- data-bs-dismiss="modal" --}}
                        class="btn btn-primary submitAttributeBtn">Select</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Item Remark Modal --}}
    <div class="modal fade" id="itemRemarkModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered">
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

    {{-- Referencing From Item Modals --}}
    {{-- PI Items --}}
    @include('remanufacturing.scrap.partials.ps-modal')
    {{-- PI Items --}}
@endsection
@section('scripts')
    <script type="text/javascript" src="{{ asset('assets/js/modules/common-datatable.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/modules/scrap-attr-ui.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/modules/scrap.js') }}"></script>
    <script type="text/javascript" src="{{ asset('app-assets/js/file-uploader.js') }}"></script>
    <script>
        const type = '{{ request()->route('type') }}';
        const getPsRoute = '{{ route('scrap.get.ps') }}';
        const scrapIndexRoute = '{{ route('scrap.index') }}';
        const scrapItemRowRoute = '{{ route('scrap.item.row') }}';
        const scrapItemAttrRoute = '{{ route('scrap.item.attr') }}';
        const scrapItemDetailsRoute = '{{ route('scrap.get.itemdetail') }}';
        const getDocNumberByBookIdUrl = '{{ route('book.get.doc_no_and_parameters') }}';
    </script>
    <script>
        setTimeout(() => {
            $("#book_id").trigger('change');
        }, 0);

        $(window).on("load", function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14,
                });
            }
        });

        $(function() {
            $(".ledgerselecct")
                .autocomplete({
                    source: [
                        "Indian Oil Corporation Ltd.",
                        "Airports Authority of India",
                        "Bharat Heavy Electricals Ltd.",
                        "Bharat Petroleum Corpn. Ltd.",
                        "NTPC Ltd.",
                        "Gail (India) Ltd.",
                        "Hindustan Petroleum Corpn. Ltd.",
                        "Steel Authority of India Ltd.",
                        "Indian Railway Stations Devpt. Corporation Ltd.",
                        "Oil & Natural Gas Corporation Ltd.",
                        "Oil & Natural Gas Corporation Ltd.",
                        "Hindustan Aeronautics Ltd.",
                    ],
                    minLength: 0,
                })
                .focus(function() {
                    if (this.value == "") {
                        $(this).autocomplete("search");
                    }
                });
        });

        $(".mrntableselectexcel tr").click(function() {
            $(this).addClass("trselected").siblings().removeClass("trselected");
            value = $(this).find("td:first").html();
        });
    </script>
    <script>
        $(document).on('change', '#sub_store_id', (e) => {
            let value = $(e.target).val();
            if (value === '' || value === null || value === '0') {
                $("#reference_from").addClass("d-none");
                return false;
            }
            $("#reference_from").removeClass("d-none");
        });

        $(document).on('change', '#book_id', (e) => {
            let bookId = e.target.value;
            if (bookId) {
                getDocNumberByBookId(bookId);
            } else {
                $("#document_number").val('');
                $("#book_id").val('');
                $("#document_number").attr('readonly', false);
            }
        });

        let psTable;
        $(document).on('click', '.psSelect', (e) => {
            $("#psModal").modal('show');
            const tableSelector = '#psModal .ps-order-detail';
            if ($.fn.DataTable.isDataTable(tableSelector)) {
                psTable = $(tableSelector).DataTable();
                psTable.ajax.reload();
            } else {
                getProductionSlips();
            }
        });

        $(document).on("click", ".psProcess", function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            $("#psModal th .form-check-input").prop("checked", false);

            let ids = getSelectedItemIDs();

            if (!ids.length) {
                setHiddenInput("ps_item_ids", "");
                setHiddenInput("item_ids", "");
                setHiddenInput("pull_item_type", "");

                $("#psModal").modal("hide");
                Swal.fire({
                    title: "Error!",
                    text: "Please select at least one SO item.",
                    icon: "error",
                });

                return false;
            }

            setHiddenInput("ps_item_ids", ids);

            let itemIds = [];
            let selectedItems = [];

            $("#psModal .ps_item_checkbox:checked").each(function() {
                const itemId = Number($(this).data("item-id"));
                itemIds.push(itemId);
                selectedItems.push({
                    item_id: itemId
                });
            });

            setHiddenInput("item_ids", itemIds);
            setHiddenInput("pull_item_type", 'pslip');

            const storeId = $("#store_id").val() || "";
            const subStoreId = $("#sub_store_id").val() || "";
            const currentRowCount = $("#productionSlipsTable .mrntableselectexcel tr").length;
            const selectedItemsParam = encodeURIComponent(JSON.stringify(selectedItems));

            const actionUrl = `{{ route('scrap.process.item') }}?` +
                `type=pslip&ids=${encodeURIComponent(JSON.stringify(ids))}` +
                `&selected_items=${selectedItemsParam}` +
                `&store_id=${storeId}&sub_store_id=${subStoreId}&current_row_count=${currentRowCount}`;

            fetch(actionUrl)
                .then((response) => response.json())
                .then((data) => {
                    if (data.status === 200) {
                        $("#uidTh").hide();
                        $("#psModal").modal("hide");
                        $("#productionSlipsTable .mrntableselectexcel")
                            .empty()
                            .append(data.data.pos);

                        setTimeout(() => {
                            $("#productionSlipsTable .mrntableselectexcel tr").each(
                                function(index) {
                                    let currentIndex = index + 1;
                                    setAttributesUIHelper(currentIndex, "#productionSlipsTable");
                                }
                            );
                        }, 100);

                        $('a[href="#productionSlip"]').tab("show");
                        const $lastRow = $("#productionSlipsTable .mrntableselectexcel tr").last();
                        if ($lastRow.length) {
                            $lastRow.attr("tabindex", "-1").focus();
                        }

                    } else {
                        setHiddenInput("ps_item_ids", "");
                        setHiddenInput("item_ids", "");
                        setHiddenInput("pull_item_type", "");

                        Swal.fire({
                            title: "Error!",
                            text: data.message,
                            icon: "error",
                        });
                    }
                })
                .catch((e) => {
                    setHiddenInput("ps_item_ids", "");
                    setHiddenInput("item_ids", "");
                    setHiddenInput("pull_item_type", "");

                    Swal.fire({
                        title: "Error!",
                        text: "Something went wrong while processing the request.",
                        icon: "error",
                    });
                });
        });

        $(document).on('click', '#backBtn', (e) => {
            $("#psModal").modal('hide');
            setTimeout(() => {
                $("#psModal").modal('show');
            }, 0);
        });

        /* Common Item Detail Fetcher */
        $(document).on('input change focus', 'table[class$="ItemsTable"] tr input', (e) => {
            let currentTr = e.target.closest('tr');
            let $row = $(currentTr);
            let tab = $row.closest(".tab-pane").attr(
                "id"); // find which tab (scavenging, repairOrder, productionSlip)
            let itemId = $row.find("[name*='[item_id]']").val();
            let remark = $row.find("[name*='remark']").val() || '';
            let uomId = $row.find("[name*='[uom_id]']").val() || '';
            let qty = $row.find("[name*='[qty]']").val() || '';

            if (!itemId) return;

            let selectedAttr = [];
            $row.find("[name*='attr_name']").each(function() {
                if ($(this).val()) selectedAttr.push($(this).val());
            });

            let type = '{{ request()->route('type') }}';
            let actionUrl = '{{ route('scrap.get.itemdetail', ['type' => ':type']) }}'.replace(':type', type) +
                `?tab=${tab}&item_id=${itemId}` +
                `&selectedAttr=${encodeURIComponent(JSON.stringify(selectedAttr))}` +
                `&remark=${remark}&uom_id=${uomId}&qty=${qty}`;

            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        $(`#${tab} #${tab}sTfoot`).html(data.data.html);
                    }
                });
            });
        });

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
            getProductionSlips();
        });

        /*searchPiBtn*/
        $(document).on('click', '.searchPiBtn', (e) => {
            getProductionSlips();
        });

        $(document).on('keyup', '#item_name_search', (e) => {
            getProductionSlips();
        });

        $(document).on('change', '#attributeCheck', (e) => {
            if (e.target.checked) {
                $("#show_attribute").val(1);
            } else {
                $("#show_attribute").val(0);
            }
            getProductionSlips();
        });

        /*Checkbox for pi item list*/
        $(document).on('change', '#psModal .ps-order-detail > thead .form-check-input', (e) => {
            if (e.target.checked) {
                $("#psModal .ps-order-detail > tbody .form-check-input").each(function() {
                    $(this).prop('checked', true);
                });
            } else {
                $("#psModal .ps-order-detail > tbody .form-check-input").each(function() {
                    $(this).prop('checked', false);
                });
            }
        });

        $(document).on('change', '#psModal .ps-order-detail > tbody .form-check-input', (e) => {
            if (!$("#psModal .ps-order-detail > tbody .form-check-input:not(:checked)").length) {
                $('#psModal .ps-order-detail > thead .form-check-input').prop('checked', true);
            } else {
                $('#psModal .ps-order-detail > thead .form-check-input').prop('checked', false);
            }
        });
    </script>
@endsection
