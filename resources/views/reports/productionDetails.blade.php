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

                        <a href="{{ route('productionTracking.details',['id'=> request()->route('id')]) }}?pdf='true'" target="_blank" class="btn btn-danger box-shadow-2 btn-sm"><i
                                data-feather="download"></i> Print
                        </a>
                    </div>
                </div>
            </div>
            <div class="content-body">

                <div class="card">
                    <div class="card-body customernewsection-form">
                        <!-- Location Info -->
                        <p>{{isset($details->store_name)?$details->store_name:''}}</p>
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
                                @php
                                    $attributes = explode(',', $details->attributes); // assuming it's comma separated
                                @endphp

                                <td>
                                    @foreach($attributes as $attr)
                                        <span class="badge bg-primary me-1">{{ trim($attr) }}</span>
                                    @endforeach
                                </td>
                                <td><b>Date</b></td>
                                <td>{{date('d-m-Y',strtotime($details->pwo_document_date))}}</td>
                            </tr>
                            <tr>
                                <td><b>Customer</b></td>
                                <td>{{$details->customer_name}}</td>
                                <td><b>PWO Qty</b></td>
                                <td>{{$details->qty}}</td>
                            </tr>
                            <tr>
                                <td><b>SO#</b></td>
                                <td>{{$details->so_book_code}}-{{$details->so_document_number}}</td>
                                <td><b>Produced Qty</b></td>
                                <td>{{$details->pslip_qty}}</td>
                            </tr>
                            <tr>
                                <td><b>Date</b></td>
                                <td>{{date('d-m-Y',strtotime($details->so_document_date))}}</td>
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
                                                    <th>MO No.</th>
                                                    <th>MO Date</th>
                                                    <th>MO Qty</th>
                                                    <th>Sub Store</th>
                                                    <th>Station</th>
                                                    <th>TYPE</th>
                                                    <th>ITEM CODE</th>
                                                    <th>ITEM NAME</th>
                                                    <th>PSLIP No.</th>  
                                                    <th>PSLIP Date</th>
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
    <script>
        $(document).ready(function() {  
            var id = "{{ request()->route('id') }}";  
   
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
                       
                        columnDefs: [
                            { targets: "_all", className: "text-nowrap" },
                        ],
        
                        ajax: {
                            url: "{{ url('report/production-tracking/details') }}/" + id,      
                        },
                        columns: [
                                {
                                    data: 'DT_RowIndex',
                                    orderable: false,
                                    searchable: false,
                                    className: "text-center text-nowrap"
                                }, 
                                      {
                                    data: 'document_number',
                                    name: 'b.document_number',
                                    render: function (data, type, row) {
                                        return row.book_code+' - '+row.document_number;
                                    }
                                },
                                {
                                    data: 'document_date',
                                    name: 'b.document_date',
                                    render: function (data, type, row) {
                                        let raw = row.document_date;
                                        let formatted = formatDateDMY(raw);

                                        if (type === 'display') {
                                            return formatted;
                                        }
                                        if (type === 'filter') {
                                            return raw + ' ' + formatted;
                                        }
                                        return raw;
                                    }
                                },
                                {
                                    data: 'mo_product_qty',
                                    name: 'a.mo_product_qty',
                                    render: function (data, type, row) {
                                        return row.mo_product_qty;
                                    }
                                }, 
                                {
                                    data: 'sub_store_name',
                                    name: 'f.name',
                                    render: function (data, type, row) {
                                        return row.sub_store_name;
                                    }
                                },          
                                {
                                    data: 'station_name',
                                    name: 'e.name',
                                    render: function (data, type, row) {
                                        return row.station_name;
                                    }
                                },            
                                {
                                    data: 'type',
                                    name: 'type',
                                    orderable: false,
                                    searchable: false,
                                }, 
                                {
                                    data: 'item_code',
                                    name: 'd.item_code',
                                    render: function (data, type, row) {
                                        return row.item_code;
                                    }
                                }, 
                                {
                                    data: 'item_name',
                                    name: 'd.item_name',
                                    render: function (data, type, row) {
                                        return row.item_name;
                                    }
                                }, 
                                {
                                    data: 'pslip_document_number',
                                    name: 'c.document_number',
                                    render: function (data, type, row) {
                                        return row.pslip_book_code+' - '+row.pslip_document_number;
                                    }
                                }, 
                                {
                                    data: 'pslip_document_date',
                                    name: 'c.document_date',
                                    render: function (data, type, row) {
                                        let raw = row.pslip_document_date;              // e.g. 2025-05-27
                                        let formatted = formatDateDMY(raw);            // e.g. 27-05-2025

                                        if (type === 'display') {
                                            return formatted;
                                        }
                                        if (type === 'filter') {
                                            return raw + ' ' + formatted;
                                        }
                                        return raw; 
                                    }
                                },   
                                {
                                    data: 'qty',
                                    name: 'd.qty'
                                }, 
                                {
                                    data: 'accepted_qty',
                                    name: 'accepted_qty',
                                    orderable: false,
                                    searchable: false,
                                    render: function (data, type, row) {
                                        return row.accepted_qty;
                                    }
                                },
                                {
                                    data: 'subprime_qty',
                                    name: 'd.subprime_qty',
                                },
                                {
                                    data: 'rejected_qty',
                                    name: 'd.rejected_qty',
                                     render: function (data, type, row) {
                                        return row.rejected_qty;
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
                                        columns: [0,1,2,3,4,5,6,7,8,9,10,11,12]
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

        function formatDateDMY(dateStr) {
            if (!dateStr) return '';
            let date = new Date(dateStr);
            let day = String(date.getDate()).padStart(2, '0');
            let month = String(date.getMonth() + 1).padStart(2, '0');
            let year = date.getFullYear();
            return `${day}-${month}-${year}`;
        }
    </script>
@endsection
