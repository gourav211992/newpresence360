@extends('layouts.app')
@section('content')
    {{-- BEGIN: Content --}}
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Scraps</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                    <li class="breadcrumb-item active">Scrap List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                        @if (count($servicesBooks['services']))
                            <a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{ route('scrap.create') }}"><i data-feather="plus-circle"></i> Create Scrap</a>
                        @endif
                        <a class="btn btn-dark btn-sm mb-50 mb-sm-0" href="{{ route('transactions.report', ['serviceAlias' => 'scrap']) }}"><i data-feather="bar-chart-2"></i>Report</a>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="table-responsive">
                                    <table class="datatables-basic table myrequesttablecbox tableistlastcolumnfixed">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Date</th>
                                                <th>Series</th>
                                                <th>Doc No.</th>
                                                <th>Location</th>
                                                <th>Sub Store</th>
                                                <th>Items</th>
                                                <th>Total Qty</th>
                                                <th>Total Cost</th>
                                                <th>Reference From.</th>
                                                <th>Total Reference Qty.</th>
                                                <th style="width:100px">Status</th>
                                            </tr>
                                        </thead>
                                    </table>
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
    <script type="text/javascript" src="{{ asset('assets/js/modules/common-datatable.js') }}"></script>
    <script>
        $(document).ready(function() {
            function renderData(data) {
                return data ? data : '';
            }
            var columns = [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'document_date',
                    name: 'document_date',
                    render: renderData
                },
                {
                    data: 'book_name',
                    name: 'book_name',
                    render: renderData,
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('no-wrap');
                    }
                },
                {
                    data: 'document_number',
                    name: 'document_number',
                    render: renderData,
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('no-wrap');
                    }
                },
                {
                    data: 'store',
                    name: 'store',
                    render: renderData,
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('no-wrap');
                    }
                },
                {
                    data: 'sub_store',
                    name: 'sub_store',
                    render: renderData,
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('no-wrap');
                    }
                },
                {
                    data: 'components',
                    name: 'components',
                    render: renderData
                },
                {
                    data: 'total_qty',
                    name: 'total_qty',
                    render: renderData,
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('no-wrap');
                    }
                },
                {
                    data: 'total_cost',
                    name: 'total_cost',
                    render: renderData,
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('no-wrap');
                    }
                },
                {
                    data: 'reference_from',
                    name: 'reference_from',
                    render: renderData,
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('no-wrap');
                    }
                },
                {
                    data: 'total_reference_qty',
                    name: 'total_reference_qty',
                    render: renderData,
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('no-wrap');
                    }
                },
                {
                    data: 'document_status',
                    name: 'document_status',
                    render: renderData,
                    createdCell: function(td, cellData, rowData, row, col) {
                        $(td).addClass('no-wrap');
                    }
                }
            ];

            var filters = {
                status: '#filter-status', // Status filter (dropdown)
                category: '#filter-category', // Category filter (dropdown)
                item_code: '#filter-item-code' // Item code filter (input text field)
            };
            var exportColumns = [0, 1, 2, 3, 4, 5, 6]; // Columns to export
            initializeDataTable('.datatables-basic',
                "{{ route('scrap.index') }}",
                columns,
                filters, // Apply filters
                'Scrap  ', // Export title
                exportColumns, // Export columns
                // [[1, "desc"]] // default order

            );
        });
    </script>
@endsection
