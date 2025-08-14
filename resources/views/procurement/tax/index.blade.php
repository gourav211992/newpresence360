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
                            <h2 class="content-header-title float-start mb-0">Tax Master</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>  
                                    <li class="breadcrumb-item active">Tax List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <a class="btn btn-primary btn-sm mb-50 mb-sm-0" href="{{ route('tax.create') }}"><i data-feather="plus-circle"></i> Add New</a> 
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="table-responsive">
                                    <table class="datatables-basic table myrequesttablecbox"> 
                                        <thead>
                                            <tr>
                                                <th>S.NO</th>
                                                <th>Tax Group</th>
                                                <th>Applicability</th>
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
        function renderData(data) {
            return data ? data : 'N/A'; 
        }
        var dt_tax_table = $('.datatables-basic');
        if (dt_tax_table.length) {
            var dt_tax = dt_tax_table.DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route("tax.index") }}', 
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'tax_group', render: renderData },
                    { data: 'applicability_type', render: renderData },
                    { data: 'description', render: renderData },
                    { data: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ],
                dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                buttons: [
                    {
                        extend: 'collection',
                        className: 'btn btn-outline-secondary dropdown-toggle',
                        text: feather.icons['share'].toSvg({ class: 'font-small-4 mr-50' }) + 'Export',
                        buttons: [
                            {
                                extend: 'print',
                                text: feather.icons['printer'].toSvg({ class: 'font-small-4 mr-50' }) + 'Print',
                                className: 'dropdown-item',
                                title: 'Tax Master',
                                exportOptions: { columns: [0, 1, 2,3,4] }
                            },
                            {
                                extend: 'csv',
                                text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + 'Csv',
                                className: 'dropdown-item',
                                title: 'Tax Master',
                                exportOptions: {columns: [0, 1, 2,3,4]}
                            },
                            {
                                extend: 'excel',
                                text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
                                className: 'dropdown-item',
                                title: 'Tax Master',
                                exportOptions: {columns: [0, 1, 2,3,4]}
                            },
                            {
                                extend: 'pdf',
                                text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 mr-50' }) + 'Pdf',
                                className: 'dropdown-item',
                                title: 'Tax Master',
                                exportOptions: { columns: [0, 1, 2,3,4] }
                            },
                            {
                                extend: 'copy',
                                text: feather.icons['copy'].toSvg({ class: 'font-small-4 mr-50' }) + 'Copy',
                                className: 'dropdown-item',
                                title: 'Tax Master',
                                exportOptions: { columns: [0, 1, 2,3,4]}
                            }
                        ],
                        init: function(api, node, config) {
                            $(node).removeClass('btn-secondary');
                            $(node).parent().removeClass('btn-group');
                            setTimeout(function() {
                                $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
                            }, 50);
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
</script>
@endsection
