@extends('layouts.app')
@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <h2 class="content-header-title float-start mb-0">Production Report</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                <li class="breadcrumb-item active">Production Report</li>
                            </ol>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6">

                        <a href="{{ route('productionTracking.download') }}" target="_blank" class="btn btn-danger box-shadow-2 btn-sm"><i
                                data-feather="download"></i> Export CSV
                        </a>
                    </div>
                </div>
            </div>
            <div class="content-body">

                <div class="card">
                    <div class="card-body customernewsection-form">
                        <!-- Location Info -->
                        <p>{{$details->store_name}}</p>
                        </br>

                        <!-- Product & PWO Info Side by Side -->
                        <table border="0" width="100%" cellspacing="3" cellpadding="3">
                            <tr>
                                <td><b>Product Name</b></td>
                                <td>{{$details->item_code}} - {{$details->item_name}}</td>
                                <td><b>PWO#</b></td>
                                <td>{{$details->pwo_book_code}} - {{$details->pwo_document_number}}</td>
                            </tr>
                            <tr>
                                <td><b>Attributes</b></td>
                                <td>{{$details->pwo_document_date}}</td>
                                <td><b>Date</b></td>
                                <td>{{$details->pwo_document_date}}</td>
                            </tr>
                            <tr>
                                <td><b>Customer</b></td>
                                <td>{{$details->customer_name}}</td>
                                <td><b>PWO Qty</b></td>
                                <td>{{$details->qty}}</td>
                            </tr>
                            <tr>
                                <td><b>SO#</b></td>
                                <td>{{$details->so_document_number}}</td>
                                <td><b>Produced Qty</b></td>
                                <td>{{$details->pslip_qty}}</td>
                            </tr>
                            <tr>
                                <td><b>Date</b></td>
                                <td>{{$details->so_document_date}}</td>
                                <td><b>% Completion</b></td>
                                <td>{{$details->completion_percent}}%</td>
                            </tr>
                        </table>
                        <br>
                    </div>
                </div>
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="datatables-basic table">
                                            <thead>
                                                <tr>
                                                   
                                                    <th>Sr. No.</th>
                                                    <th>Date</th>
                                                    <th>PSLIP No.</th>                              
                                                    <th>Station</th>
                                                    <th>MO No.</th>
                                                    <th>MO Date</th>
                                                    <th>TYPE</th>
                                                    <th>Produced Qty</th>
                                                    <th>Accepted (A)</th>
                                                    <th>Sub Standard (B)</th>
                                                    <th>Rejected (C)</th>
                                                 
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
   
@endsection

@section('scripts')
    {{-- <script>
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
                                d.so_document_number  = $('#so_number').val();
                                d.pwo_document_number  = $('#pwo_number').val();
                                d.consumed_item_code  = $('#consumed_item_code').val();
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
                                        return row.pwo_document_date;
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
                                    name: 'so_document_date'
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
                                        columns: [0,1,2,3,4,5,6,7,8,9,10,11,12,13,14]
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
    </script> --}}
@endsection
