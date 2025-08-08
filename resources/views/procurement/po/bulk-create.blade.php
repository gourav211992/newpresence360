@extends('layouts.app')
@section('content')
<form class="ajax-input-form" data-module="po" method="POST" action="{{ url(request()->route('type')) }}/bulk-store"
    data-redirect="/{{ request()->route('type') }}" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="pi_item_ids" id="pi_item_ids">
    <input type="hidden" name="po_type" id="po_type">
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    @include('layouts.partials.breadcrumb-add-edit', [
                        'title' => $title,
                        'menu' => $menu,
                        'menu_url' => $menu_url,
                        'sub_menu' => $sub_menu,
                    ])
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <input type="hidden" name="document_status" value="draft" id="document_status">
                            <button type="button" onClick="javascript: history.go(-1)"
                                class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i>
                                Back</button>
                            <button type="submit" class="btn btn-primary btn-sm submit-button" name="action"
                                value="draft"><i data-feather="check-circle"></i> Process</button>
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
                                                            <option value="{{ $book->id }}">{{ $book->book_code }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <input type="hidden" name="book_code" id="book_code">
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">{{ $short_title }} Date <span
                                                            class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="date" class="form-control"
                                                        value="{{ date('Y-m-d') }}" name="document_date">
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Location <span
                                                            class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select" id="store_id" name="store_id">
                                                        @foreach ($locations as $location)
                                                            <option value="{{ $location->id }}">
                                                                {{ $location?->store_name }}</option>
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
                                                    <h4 class="card-title text-theme">Purchase Indent</h4>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col" id="subLocation">
                                                <div class="mb-1">
                                                    <label class="form-label">Sub Location</label>
                                                    <input type="text" id="sub_store_po" placeholder="Select"
                                                        class="form-control mw-100 ledgerselecct ui-autocomplete-input"
                                                        autocomplete="off" value="">
                                                    <input type="hidden" id="sub_store_id_po"></input>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="mb-1">
                                                    <label class="form-label">Requester</label>
                                                    <input type="text" id="requester_po" placeholder="Select"
                                                        class="form-control mw-100 ledgerselecct ui-autocomplete-input"
                                                        autocomplete="off" value="">
                                                    <input type="hidden" id="requester_id_po"></input>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="mb-1">
                                                    <label class="form-label">Vendor</label>
                                                    <input type="text" id="vendor_code_input_qt"
                                                        placeholder="Select"
                                                        class="form-control mw-100 ledgerselecct ui-autocomplete-input"
                                                        autocomplete="off" value="">
                                                    <input type="hidden" id="vendor_id_qt_val"></input>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="mb-1">
                                                    <label class="form-label">Indent No.</label>
                                                    <input type="text" id="document_no_input_qt"
                                                        placeholder="Select"
                                                        class="form-control mw-100 ledgerselecct ui-autocomplete-input"
                                                        autocomplete="off" value="">
                                                    <input type="hidden" id="document_id_qt_val"></input>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="mb-1">
                                                    <label class="form-label">Sales Order</label>
                                                    <input type="text" id="pi_so_no_input_qt" placeholder="Select"
                                                        class="form-control mw-100 ledgerselecct ui-autocomplete-input"
                                                        autocomplete="off" value="">
                                                    <input type="hidden" id="pi_so_qt_val"></input>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <div class="mb-1">
                                                    <label class="form-label">Item</label>
                                                    <input type="text" name="item_name_search"
                                                        id="item_name_search" placeholder="Item Name/Code"
                                                        class="form-control mw-100" autocomplete="off"
                                                        value="">
                                                </div>
                                            </div>
                                            <div class="col mb-1">
                                                <label class="form-label">&nbsp;</label><br />
                                                <button type="button" class="btn btn-warning btn-sm clearPiFilter"><i
                                                        data-feather="x-circle"></i> Clear</button>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="table-responsive">
                                                    <table id="itemTable"  class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad" 
                                                    data-json-key="components_json"
                                                    data-row-selector="tr[id^='row_']"> 
                                                        <thead class="table-light header">
                                                            <tr>
                                                                <th class="d-none">Id</th>
                                                                <th class="customernewsection-form" >
                                                                    <div class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input" id="allCheck">
                                                                        <label class="form-check-label" for="allCheck"></label>
                                                                    </div>
                                                                </th>
                                                                <th>Indent No.</th>
                                                                <th>Indent Date</th>
                                                                <th>Item Code</th>
                                                                <th>Item Name</th>
                                                                <th>Attributes</th>
                                                                <th>UOM</th>
                                                                <th>Pending PO</th>
                                                                <th>Avl Stock</th>
                                                                <th>Qty</th>
                                                                <th>Rate</th>
                                                                <th>Vendor</th>
                                                                <th>Delivery Date</th>
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
<script type="text/javascript" src="{{asset('assets/js/modules/common-datatable.js')}}"></script>
    <script>
        $(document).on('change', '#book_id', (e) => {
            let bookId = e.target.value;
            if (bookId) {
                getDocNumberByBookId(bookId, true);
            } else {
                $("#document_number").val('');
                $("#book_id").val('');
                $("#document_number").attr('readonly', false);
            }
            
        });

        function getDocNumberByBookId(bookId, reloadPiQuery = false) {
            let document_date = $("[name='document_date']").val();
            let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId + '&document_date=' +
                document_date;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        $("#book_code").val(data.data.book_code);
                        if (!data.data.doc.document_number) {
                            $("#document_number").val('');
                        }
                        $("#document_number").val(data.data.doc.document_number);
                        if (data.data.doc.type == 'Manually') {
                            $("#document_number").attr('readonly', false);
                        } else {
                            $("#document_number").attr('readonly', true);
                        }
                        const parameters = data.data.parameters;

                        let poType = parameters.goods_or_services || 'Goods';
                        
                        $("#po_type").val(poType);
                        setServiceParameters(parameters);
                        if (reloadPiQuery) {
                            $('#itemTable').DataTable().ajax.reload();
                        }
                    }
                    if (data.status == 404) {
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
        }, 0);
        /*Set Service Parameter*/
        function setServiceParameters(parameters) {
            /*Date Validation*/
            const docDateInput = $("[name='document_date']");
            let isFeature = false;
            let isPast = false;
            if (parameters.future_date_allowed && parameters.future_date_allowed.includes('yes')) {
                let futureDate = new Date();
                futureDate.setDate(futureDate.getDate() /*+ (parameters.future_date_days || 1)*/ );
                docDateInput.val(futureDate.toISOString().split('T')[0]);
                docDateInput.attr("min", new Date().toISOString().split('T')[0]);
                isFeature = true;
            } else {
                isFeature = false;
                docDateInput.attr("max", new Date().toISOString().split('T')[0]);
            }
            if (parameters.back_date_allowed && parameters.back_date_allowed.includes('yes')) {
                let backDate = new Date();
                backDate.setDate(backDate.getDate() /*- (parameters.back_date_days || 1)*/ );
                docDateInput.val(backDate.toISOString().split('T')[0]);
                // docDateInput.attr("max", "");
                isPast = true;
            } else {
                isPast = false;
                docDateInput.attr("min", new Date().toISOString().split('T')[0]);
            }
            /*Date Validation*/
            if (isFeature && isPast) {
                docDateInput.removeAttr('min');
                docDateInput.removeAttr('max');
            }

            /*Reference from*/
            let reference_from_service = parameters.reference_from_service;
            if (reference_from_service.length) {
                let pi = '{{ \App\Helpers\ConstantHelper::PI_SERVICE_ALIAS }}';
                if (reference_from_service.includes(pi)) {
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
                    location.href = '{{ url('purchase-order') }}';
                }, 1500);
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
                            type: 'vendor_list'
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
        openPurchaseRequest();
        function openPurchaseRequest() {
            initializeAutocompleteQt("vendor_code_input_qt", "vendor_id_qt_val", "vendor_list", "vendor_code",
                "company_name");
            initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "pi_document_qt", "book_code",
                "document_number");
            initializeAutocompleteQt("item_name_input_qt", "item_id_qt_val", "comp_item", "item_code", "item_name");
            initializeAutocompleteQt("pi_so_no_input_qt", "pi_so_qt_val", "pi_so_qt", "book_code", "document_number");
            initializeAutocompleteQt("requester_po", "requester_id_po", "all_user_list", "name", "");

        }

        function initializeAutocompleteQt(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "") {
            $("#" + selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: typeVal,
                            vendor_id: $("#vendor_id_qt_val").val(),
                            header_book_id: $("#book_id").val(),
                            store_id: $("#store_id").val() || '',
                            module_type: '{{ request()->route('type') }}'
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: `${item[labelKey1]}${labelKey2 ? (item[labelKey2] ? '-' + item[labelKey2] : '') : ''}`,
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
                    $('#itemTable').DataTable().ajax.reload();
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#" + selectorSibling).val("");
                        $('#itemTable').DataTable().ajax.reload();
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                    $('#itemTable').DataTable().ajax.reload();
                }
            });
        }

        function getLocation(locationId = '') {
            let actionUrl = '{{ route('store.get') }}' + '?location_id=' + locationId;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        let options = '';
                        data.data.locations.forEach(function(location) {
                            options +=
                                `<option value="${location.id}">${location.store_name}</option>`;
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
            $("#department_id_po").val('');
            $("#store_po").val('');
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
            $('#itemTable').DataTable().ajax.reload();
        });
        
        setTimeout(() => {
            getIndents();
        }, 100);

        function renderData(data) {
            return data ? data : ''; 
        }

        function getDynamicParams() {
            return {
                document_date: $("[name='document_date']").val() || '',
                header_book_id: $("#book_id").val() || '',
                series_id: $("#book_id_qt_val").val() || '',
                document_number: $("#document_id_qt_val").val() || '',
                item_id: $("#item_id_qt_val").val() || '',
                vendor_id: $("#vendor_id_qt_val").val() || '',
                department_id: $("#department_id_po").val() || '',
                store_id: $("#store_id").val() || '',
                sub_store_id: $("#sub_store_id_po").val() || '',
                requester_id: $("#requester_id_po").val() || '',
                item_search: $("#item_name_search").val() || '',
                so_id: $("#pi_so_qt_val").val() || '',
                po_type: $("#po_type").val() || '',
            };
        }

        function getIndents() {
            const type = '{{ request()->route("type") }}';
            const actionUrl = '{{ route("po.get.pi.bulk", ["type" => ":type"]) }}'.replace(':type', type);
            var columns = [
                { data: 'id',visible: false, orderable: true, searchable: false},
                { data: 'select_checkbox', name: 'select_checkbox'},
                { data: 'doc_number', name: 'pi.book.doc_number' },
                { data: 'doc_date', name: 'pi.document_date' },
                { data: 'item_code', name: 'item_code' },
                { data: 'item_name', name: 'item.item_name' },
                { data: 'attributes', name: 'attributes'},
                { data: 'uom', name: 'uom.name' },
                { data: 'pending_po', name: 'pending_po', render: renderData, orderable: false, searchable: false, 
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    } 
                },
                { data: 'avl_stock', name: 'avl_stock', render: renderData, orderable: false, searchable: false, 
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    } 
                },
                { data: 'qty', name: 'qty' ,
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    }
                },
                { data: 'rate', name: 'rate', 
                    createdCell: function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-end');
                    } 
                },
                { data: 'vendor_id', name: 'vendor_id'},
                { data: 'delivery_date', name: 'delivery_date' },
                { data: 'so_doc', name: 'so_doc' },
                { data: 'store', name: 'store' },
                { data: 'department', name: 'pi.sub_store.name' },
                { data: 'requester', name: 'pi.requester.name' },
                { data: 'remark', name: 'remark' },
            ];
            initializeDataTableCustom('#itemTable', 
                actionUrl,
                columns,
            );
        }

        $(document).on('keyup', '#item_name_search', (e) => {
            $('#itemTable').DataTable().ajax.reload();
        });

        // Checkbox code
        $(document).on('change', '#allCheck', (e) => {
            if (e.target.checked) {
                $("#itemTable tbody tr").each(function() {
                    if ($(this).find("[name*='[vendor_id]']").val()) {
                        $(this).find(".form-check-input").prop('checked', e.target.checked);
                    }
                });
                if (!$("#itemTable tbody .form-check-input:checked").length) {
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
            if (!isVendorSelected) {
                e.target.checked = false;
                Swal.fire({
                    title: 'Error!',
                    text: "Please select vendor first.",
                    icon: 'error',
                });
            }
            if (!isQtySelected) {
                e.target.checked = false;
                Swal.fire({
                    title: 'Error!',
                    text: "Please update qty first.",
                    icon: 'error',
                });
            }
            if (!isRateSelected) {
                e.target.checked = false;
                Swal.fire({
                    title: 'Error!',
                    text: "Please update rate first.",
                    icon: 'error',
                });
            }
        });

        $(document).on("change", "#store_id", function(event, ui) {
            let storeId = ui?.item?.id || '';
            initializeAutocompleteQt("sub_store_po", "sub_store_id_po", "sub_store", "name", "");
        });
        if ($("#store_id").length) {
            initializeAutocompleteQt("sub_store_po", "sub_store_id_po", "sub_store", "name", "");
        }
    </script>
@endsection
