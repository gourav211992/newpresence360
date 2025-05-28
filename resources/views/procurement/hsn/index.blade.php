@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">HSN/SAC Master</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item active">HSN/SAC List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-end col-md-7 mb-2 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <a class="btn btn-primary btn-sm" href="{{ route('hsn.create') }}"><i data-feather="plus-circle"></i> Add New</a>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="table-responsive">
                                    <table class="datatables-basic table">
                                        <thead>
                                            <tr>
                                                <th>S.NO</th>
                                                <th>Code Type</th>
                                                <th>HSN/SAC Code</th>
                                                <th>Description</th>
                                                <th>Status</th>
                                                <th>Action</th>
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
    <!-- END: Content-->
@endsection
@section('scripts')
<script>
$(document).ready(function() {
    var dt_basic_table = $('.datatables-basic');
    function renderData(data) {
        return data ? data : 'N/A'; 
    }

    if (dt_basic_table.length) {
        dt_basic_table.DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('hsn.index') }}", 
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'type',render: renderData },
                { data: 'code',render: renderData },
                { data: 'description',render: renderData },
                { data: 'status'},
                { data: 'action', orderable: false, searchable: false }
            ],
            dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            buttons: [
                {
                    extend: 'collection',
                    className: 'btn btn-outline-secondary dropdown-toggle',
                    text: feather.icons['share'].toSvg({ class: 'font-small-4 mr-50' }) + ' Export',
                    buttons: [
                        {
                            extend: 'print',
                            text: feather.icons['printer'].toSvg({ class: 'font-small-4 mr-50' }) + ' Print',
                            className: 'dropdown-item',
                            title: 'HSN/SAC Master',
                            exportOptions: { 
                                columns: [0, 1, 2, 3, 4],
                                modifier: {
                                    search: 'applied'
                                }
                            } 
                        },
                        {
                            extend: 'csv',
                            text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + ' CSV',
                            className: 'dropdown-item',
                            title: 'HSN/SAC Master',
                            exportOptions: { 
                                columns: [0, 1, 2, 3, 4],
                                modifier: {
                                    search: 'applied'
                                }
                            } 
                        },
                        {
                            extend: 'excel',
                            text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + ' Excel',
                            className: 'dropdown-item',
                            title: 'HSN/SAC Master',
                            exportOptions: { 
                                columns: [0, 1, 2, 3, 4],
                                modifier: {
                                    search: 'applied'
                                }
                            } 
                        },
                        {
                            extend: 'pdf',
                            text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 mr-50' }) + ' PDF',
                            className: 'dropdown-item',
                            title: 'HSN/SAC Master',
                            exportOptions: { 
                                columns: [0, 1, 2, 3, 4],
                                modifier: {
                                    search: 'applied'
                                }
                            } 
                        },
                        {
                            extend: 'copy',
                            text: feather.icons['copy'].toSvg({ class: 'font-small-4 mr-50' }) + ' Copy',
                            className: 'dropdown-item',
                            title: 'HSN/SAC Master',
                            exportOptions: { 
                                columns: [0, 1, 2, 3, 4],
                                modifier: {
                                    search: 'applied'
                                }
                            } 
                        }
                    ]
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
            search: { caseInsensitive: true }
        });
    }
});
</script>

@endsection

