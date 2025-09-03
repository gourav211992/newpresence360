@extends('layouts.app')
@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <h2 class="content-header-title float-start mb-0">BOM vs Actual Report</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                <li class="breadcrumb-item active">BOM vs Actual Report</li>
                            </ol>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6">
                        <button class="btn btn-dark btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
                                    data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>

                        <a href="{{ route('bomVsActual.download', request()->all()) }}" target="_blank" class="btn btn-danger box-shadow-2 btn-sm"><i
                                data-feather="download"></i> Export CSV
                        </a>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="datatables-basic table">
                                            <thead>
                                                <tr>
                                                    <th>S.NO.</th>
                                                    <th>Series</th>
                                                    <th>Doc No.</th>
                                                    <th>Doc Date.</th>
                                                    <th>MO No.</th>
                                                    <th>MO Date.</th> 
                                                    <th>SO No.</th>
                                                    <th>SO Date.</th>
                                                    
                                                    <th>Store Name</th>
                                                    <th>Substore Name</th>
                                                    <th>Product code</th>
                                                    <th>Product Name</th>
                                                    <th>Attributes</th>
                                                    <th>Item Code</th>
                                                    <th>Item Name</th>
                                                    <th>Attributes</th>
                                                    <th>UOM</th>

                                                    <th>Planned Qty</th>
                                                    <th>Planned Cost</th>
                                                    <th>Actual Qty</th>
                                                    <th>Actual Cost</th>
                                                    <th>Variance Qty</th>
                                                    <th>Variance Cost</th>
                                                    <th>Variance Qty(%)</th>
                                                    <th>Variance Cost(%)</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    <!-- END: Content-->
    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <form class="add-new-record modal-content pt-0">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Select Date Range</label>
                        <input type="text" id="fp-range" class="form-control flatpickr-range"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" name="date_range"
                            value="{{ request('date_range') }}" />
                    </div>  
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Enter Mo No.</label>
                        <input type="text" id="mo_number" class="form-control"
                            placeholder="Enter Mo No." name="mo_number"
                            value="{{ request('mo_number') }}" />
                    </div>  
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Enter SO No.</label>
                        <input type="text" id="so_number" class="form-control"
                            placeholder="Enter SO No." name="so_number"
                            value="{{ request('so_number') }}" />
                    </div>    
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Product</label>
                        <input type="text" id="item_name_input" placeholder="Select" value="{{ request('item') }}" class="form-control mw-100 ledgerselecct comp_item_code readonlyrestrict" autocomplete="off">
                        <input type="hidden" id="item_id_val" name="item" value="{{ request('item') }}">
                    </div>

                </div>
                <div class="modal-footer justify-content-start">
                    <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                    <a href="" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {      
            var dt_basic_table = $('.datatables-basic');

            function renderData(data) {
                return data ? data : 'N/A';
            }
            if (dt_basic_table.length) {
                    var dt_discount_master = dt_basic_table.DataTable({
                        processing: true,
                        serverSide: true,
                        scrollX: true,
                        scrollY: "500px",       
                        scrollCollapse: true,
                        autoWidth: false,
                        fixedHeader: true,  
                        columnDefs: [
                            { targets: "_all", className: "text-nowrap" }
                        ],
        
                        ajax: {
                            url: '{{ route('bomVsActual.report') }}',
                            data: function(d) {
                                d.date_range          = $('#fp-range').val();
                                d.so_document_number  = $('#so_number').val();
                                d.mo_document_number  = $('#mo_number').val();
                                d.item_code  = $('#item_id_val').val();
                            }
                        },
                        columns: [
                                {
                                    data: 'DT_RowIndex',
                                    orderable: false,
                                    searchable: false,
                                    className: "text-center text-nowrap"
                                },
                                {
                                    data: 'pslip_book_code',
                                    name: 'pslip_book_code',
                                    render: function (data, type, row) {
                                        return row.pslip_book_code;
                                    }
                                },
                                {
                                    data: 'pslip_document_number',
                                    name: 'pslip_document_number'
                                },
                                {
                                    data: 'pslip_document_date',
                                    name: 'pslip_document_date'
                                },
                                {
                                    data: 'mo_document_number',
                                    name: 'mo_document_number',
                                    render: function (data, type, row) {
                                        return row.mo_book_code + '-' + row.mo_document_number;
                                    }
                                },
                                {
                                    data: 'mo_document_date',
                                    name: 'mo_document_date'
                                },
                                {
                                    data: 'so_document_number',
                                    name: 'so_document_number'
                                },
                                {
                                    data: 'so_document_date',
                                    name: 'so_document_date'
                                },
                                {
                                    data: 'store_name',
                                    name: 'store_name'
                                },
                                {
                                    data: 'sub_store_name',
                                    name: 'sub_store_name'
                                },
                                {
                                    data: 'pslip_item_code',
                                    name: 'pslip_item_code'
                                },
                                {
                                    data: 'pslip_item_name',
                                    name: 'pslip_item_name'
                                },
                                {
                                    data: 'attributes',
                                    name: 'attributes',
                                    orderable: false,
                                    searchable: false
                                },
                                {
                                    data: 'consumed_item_code',
                                    name: 'consumed_item_code'
                                },
                                {
                                    data: 'consumed_item_name',
                                    name: 'consumed_item_name'
                                },
                                {
                                    data: 'cons_attributes',
                                    name: 'cons_attributes',
                                    orderable: false,
                                    searchable: false,
                                    render: function (data, type, row) {
                                        return row.cons_attributes;
                                    }
                                },
                                {
                                    data: 'uom_code',
                                    name: 'uom_code'
                                },
                                {
                                    data: 'required_qty',
                                    name: 'required_qty',
                                    render: function (data) {
                                        return parseFloat(data || 0).toFixed(2);
                                    }
                                },
                                {
                                    data: 'required_total',
                                    orderable: false,
                                    searchable: false,
                                    render: function (data, type, row) {
                                        let total = parseFloat(row.required_qty || 0) * parseFloat(row.rate || 0);
                                        return total.toFixed(2);
                                    }
                                },
                                {
                                    data: 'consumption_qty',
                                    name: 'consumption_qty',
                                    render: function (data) {
                                        return parseFloat(data || 0).toFixed(2);
                                    }
                                },
                                {
                                    data: 'consumed_total',
                                    orderable: false,
                                    searchable: false,
                                    render: function (data, type, row) {
                                        let total = parseFloat(row.consumption_qty || 0) * parseFloat(row.rate || 0);
                                        return total.toFixed(2);
                                    }
                                },
                                {
                                    data: 'remaining_qty',
                                    orderable: false,
                                    searchable: false,
                                    render: function (data, type, row) {
                                        let required = parseFloat(row.required_qty || 0);
                                        let consumed = parseFloat(row.consumption_qty || 0);
                                        let remainingQty = required - consumed;
                                        return remainingQty.toFixed(2);
                                    }
                                },
                                {
                                    data: 'remaining_total',
                                    orderable: false,
                                    searchable: false,
                                    render: function (data, type, row) {
                                        let total = (parseFloat(row.required_qty || 0) * parseFloat(row.rate || 0)) -
                                                    (parseFloat(row.consumption_qty || 0) * parseFloat(row.rate || 0));
                                        return total.toFixed(2);
                                    }
                                },
                                {
                                    data: 'remaining_qty',
                                    orderable: false,
                                    searchable: false,
                                    render: function (data, type, row) {
                                        let required = parseFloat(row.required_qty || 0);
                                        let consumed = parseFloat(row.consumption_qty || 0);
                                        let remainingQty = required - consumed;
                                        let percentage = required > 0 ? (remainingQty / required) * 100 : 0;
                                        return percentage.toFixed(2) + "%";
                                    }
                                },
                                {
                                    data: 'remaining_total',
                                    orderable: false,
                                    searchable: false,
                                    render: function (data, type, row) {
                                        let required = parseFloat(row.required_qty || 0);
                                        let consumed = parseFloat(row.consumption_qty || 0);
                                        let rate = parseFloat(row.rate || 0);
                                        let totalRequired = required * rate;
                                        let remainingTotal = (required - consumed) * rate;
                                        let percentage = totalRequired > 0 ? (remainingTotal / totalRequired) * 100 : 0;
                                        return percentage.toFixed(2) + "%";
                                    }
                                }
                        ],
                        dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                        buttons: [{
                            extend: 'collection',
                            className: 'btn btn-outline-secondary dropdown-toggle',
                            text: feather.icons['share'].toSvg({
                                class: 'font-small-4 mr-50'
                            }) + 'Export',
                            buttons: [
                                {
                                    extend: 'csv',
                                    text: feather.icons['file-text'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Csv',
                                    className: 'dropdown-item',
                                    title: 'pSlipReport',
                                    exportOptions: {
                                        columns: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23]
                                    }
                                }
                            ],
                            init: function(api, node, config) {
                                $(node).removeClass('btn-secondary');
                                $(node).parent().removeClass('btn-group');
                                setTimeout(function() {
                                    $(node).closest('.dt-buttons').removeClass('btn-group')
                                        .addClass('d-inline-flex');
                                }, 50);
                            }
                        }],
                        drawCallback: function() {
                            feather.replace();
                        },
                        language: {
                            paginate: {
                                previous: '&nbsp;',
                                next: '&nbsp;'
                            }
                        },
                        search: {
                            caseInsensitive: true
                        }
                    });
            }
        });

        initializeAutocomplete("item_name_input", "item_id_val", "header_item", "item_code", "item_name");

        function initializeAutocomplete(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "") {
            $("#" + selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: typeVal,
                            customer_id: $("#customer_id_qt_val").val(),
                            header_book_id: $("#series_id_input").val()
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: item[labelKey2] 
                                        ? `${item[labelKey1]} (${item[labelKey2]})` 
                                        : item[labelKey1],
                                    value: item[labelKey1], // ensures correct value in input
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    $("#" + selector).val(ui.item.label);         // visible field
                    $("#" + selectorSibling).val(ui.item.value);     // hidden id field
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $("#" + selector).val("");
                        $("#" + selectorSibling).val("");
                    }
                }
            }).focus(function() {
                // open dropdown on focus if empty
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
        }

    </script>
@endsection
