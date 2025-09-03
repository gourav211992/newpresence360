@extends('layouts.app')
@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <h2 class="content-header-title float-start mb-0">Production Tracking</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                <li class="breadcrumb-item active">Production Tracking</li>
                            </ol>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6">
                        <button class="btn btn-dark btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
                                    data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>

                        <a href="{{ route('productionTracking.download', request()->all()) }}" target="_blank" class="btn btn-danger box-shadow-2 btn-sm"><i
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
                                        <table class="datatables-basic table myrequesttablecbox tableistlastcolumnfixed">
                                            <thead>
                                                <tr>
                                                   
                                                    <th>Sr. No.</th>
                                                    <th>Date</th>
                                                    <th>PWO No.</th>                              
                                                    <th>Product Code</th>
                                                    <th>Product Name</th>
                                                    <th>Attributes</th>
                                                    <th>UOM</th>
                                                    <th>SO Qty</th>
                                                    <th>PWO Qty</th>
                                                    <th>Produced Qty</th>
                                                    <th>% Completion</th>
                                                    <th>Customer</th>
                                                    <th>SO No.</th>
                                                    <th>SO Date</th>
                                                    <th>Action</th>
                                                 
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
                        <label class="form-label" for="fp-range">Enter PWO No.</label>
                        <input type="text" id="pwo_number" class="form-control"
                            placeholder="Enter PWO No." name="pwo_number"
                            value="{{ request('pwo_number') }}" />
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
                        fixedColumns: {
                            rightColumns: 1 
                        },
                        columnDefs: [
                            { targets: "_all", className: "text-nowrap" },
                            { targets: -1, orderable: false, searchable: false }
                        ],
        
                        ajax: {
                            url: '{{ route('productionTracking.report') }}',
                            data: function(d) {
                                d.date_range          = $('#fp-range').val();
                                d.so_number  = $('#so_number').val();
                                d.pwo_number  = $('#pwo_number').val();
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
                                    data: 'pwo_document_date',
                                    name: 'pwo_document_date',
                                    render: function (data, type, row) {
                                        return formatDateDMY(row.pwo_document_date);
                                    }
                                },
                                {
                                    data: 'pwo_document_number',
                                    name: 'pwo_document_number'
                                },
                                {
                                    data: 'item_code',
                                    name: 'item_code'
                                },  
                                {
                                    data: 'item_name',
                                    name: 'item_name'
                                }, 
                                {
                                    data: 'attributes',
                                    name: 'attributes',
                                    orderable: false,
                                    searchable: false,
                                    render: function (data, type, row) {
                                        return row.attributes;
                                    }
                                },
                                {
                                    data: 'uom_code',
                                    name: 'uom_code'
                                },
                                {
                                    data: 'so_order_qty',
                                    name: 'so_order_qty'
                                }, 
                                {
                                    data: 'qty',
                                    name: 'qty'
                                },
                                {
                                    data: 'pslip_qty',
                                    name: 'pslip_qty',
                                },
                                {
                                    data: 'completion_percent',
                                    name: 'completion_percent',
                                     render: function (data, type, row) {
                                        return row.completion_percent+' %';
                                    }
                                },
                                {
                                    data: 'customer_name',
                                    name: 'customer_name'
                                }, 
                                {
                                    data: 'so_document_number',
                                    name: 'so_document_number'
                                }, 
                                {
                                    data: 'so_document_date',
                                    name: 'so_document_date',
                                    render: function (data, type, row) {
                                        return formatDateDMY(row.so_document_date);
                                    }
                                }, 
                                {
                                    data: 'a.id',
                                    name: 'a.id',
                                    orderable: false,
                                    searchable: false,
                                    render: function (data, type, row) {
                                         let baseUrl = "{{ route('productionTracking.details', ':id') }}"; 
                                         baseUrl = baseUrl.replace(':id', row.id);
                                    return '<a href="' + baseUrl + '" target="_blank" class="btn btn-sm btn-primary">' +
                                    '<i class="fa fa-external-link-alt"></i> ' +
                                    '</a>';
                                    }
                                }                          
                        ],
                      
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

        // Reusable function
        function formatDateDMY(dateStr) {
            if (!dateStr) return '';
            let date = new Date(dateStr);
            let day = String(date.getDate()).padStart(2, '0');
            let month = String(date.getMonth() + 1).padStart(2, '0');
            let year = date.getFullYear();
            return `${day}-${month}-${year}`;
        }
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
