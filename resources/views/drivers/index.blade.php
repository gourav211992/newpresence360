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
                            <h2 class="content-header-title float-start mb-0">Drivers</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="#">Home</a></li>
                                    <li class="breadcrumb-item active">Driver List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-end col-md-7 mb-2 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                         <button class="btn btn-warning btn-sm me-1 mb-20 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal">
                            <i data-feather="filter"></i> Filter
                        </button>
                        <a class="btn btn-primary btn-sm" href="{{ route('logistics.driver.create') }}"><i data-feather="plus-circle"></i> Add New</a>
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
                                                <th>S.NO</th>
                                                <th>Date</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Mobile No.</th>
                                                <th>Exp(Yr)</th>
                                                <th>Created At</th>
                                                <th>Created By</th>
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
                <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
                    <div class="modal-dialog sidebar-sm">
                        <form class="add-new-record modal-content pt-0" id="item-filter-form">
                            <div class="modal-header mb-1">
                                <h5 class="modal-title" id="exampleModalLabel">Apply Driver Filter</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                            </div>
                            <div class="modal-body flex-grow-1">
                                <div class="mb-1">
                                    <label class="form-label">Name</label>
                                    <input type="text" id="filter-name"  name="name" class="form-control">
                                </div>
                                <div class="mb-1">
                                    <label class="form-label">Email</label>
                                    <input type="text" id="filter-email"  name="email" class="form-control">
                                </div>
                                <div class="mb-1">
                                    <label class="form-label">Mobile No.</label>
                                   <input type="text" id="filter-mobile_no"  name="mobile_no" class="form-control">
                                </div>
                                <div class="mb-1">
                                    <label class="form-label">Experience Years</label>
                                      <input type="text" id="filter-experience_years"  name="experience_years" class="form-control">
                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Status</label>
                                    <select id="filter-status" name="status" class="form-select">
                                        <option value="">Select Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="draft">Draft</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-start">
                                <button type="button" class="btn btn-primary apply-filter mr-1">Apply</button>
                                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="reset" class="btn btn-outline-secondary" id="reset-filters">Reset</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Content-->
@endsection
@section('scripts')
<script>
$(document).ready(function () {
    var dt_basic_table = $('.datatables-basic');
    var dataTableInstance;

    function renderData(data) {
        return data ? data : 'N/A';
    }

    function renderImage(data) {
        return data 
            ? '<img src="' + data + '" alt="Driver Image" width="50" height="50" class="rounded-circle" />' 
            : 'N/A';
    }

    if (dt_basic_table.length) {
        dataTableInstance = dt_basic_table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('logistics.driver.index') }}",
                data: function(d) {
                    d.name = $('#filter-name').val(); 
                    d.email = $('#filter-email').val(); 
                    d.mobile_no = $('#filter-mobile_no').val();
                    d.experience_years = $('#filter-experience_years').val(); 
                    d.status = $('#filter-status').val(); 
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'created_at', name: 'created_at', render: renderData },
                { data: 'name', name: 'name', render: renderData },
                { data: 'email', name: 'email', render: renderData },
                { data: 'mobile_no', name: 'mobile_no', render: renderData },
                { data: 'experience_years', name: 'experience_years', render: renderData },
                { data: 'created_at', name: 'created_at' , },
                { data: 'created_by', name: 'created_by' },
                { data: 'status', name: 'status', orderable: false, searchable: false  },
                { data: 'action', orderable: false, searchable: false }
            ],
            dom: '<"d-flex justify-content-between align-items-center mx-2 row"' +
                    '<"col-sm-12 col-md-6"l>' +
                    '<"col-sm-12 col-md-3 dt-action-buttons text-end"B>' +
                    '<"col-sm-12 col-md-3"f>>' +
                't' +
                '<"d-flex justify-content-between mx-2 row"' +
                    '<"col-sm-12 col-md-6"i>' +
                    '<"col-sm-12 col-md-6"p>>',
            buttons: [
                {
                    extend: 'collection',
                    className: 'btn btn-outline-secondary dropdown-toggle',
                    text: feather.icons['share'].toSvg({ class: 'font-small-4 me-50' }) + ' Export',
                    buttons: [
                        {
                            extend: 'print',
                            text: feather.icons['printer'].toSvg({ class: 'font-small-4 me-50' }) + ' Print',
                            className: 'dropdown-item',
                            title: 'Driver Master',
                            exportOptions: {
                                columns: [0, 2, 3, 4, 5, 6, 7, 8],
                                modifier: { search: 'applied' }
                            }
                        },
                        {
                            extend: 'csv',
                            text: feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) + ' CSV',
                            className: 'dropdown-item',
                            title: 'Driver Master',
                            exportOptions: {
                                columns: [0, 2, 3, 4, 5, 6, 7, 8],
                                modifier: { search: 'applied' }
                            }
                        },
                        {
                            extend: 'excel',
                            text: feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + ' Excel',
                            className: 'dropdown-item',
                            title: 'Driver Master',
                            exportOptions: {
                                columns: [0, 2, 3, 4, 5, 6, 7, 8],
                                modifier: { search: 'applied' }
                            }
                        },
                        {
                            extend: 'pdf',
                            text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 me-50' }) + ' PDF',
                            className: 'dropdown-item',
                            title: 'Driver Master',
                            exportOptions: {
                                columns: [0, 2, 3, 4, 5, 6, 7, 8],
                                modifier: { search: 'applied' }
                            }
                        },
                        {
                            extend: 'copy',
                            text: feather.icons['copy'].toSvg({ class: 'font-small-4 me-50' }) + ' Copy',
                            className: 'dropdown-item',
                            title: 'Driver Master',
                            exportOptions: {
                                columns: [0, 2, 3, 4, 5, 6, 7, 8],
                                modifier: { search: 'applied' }
                            }
                        }
                    ]
                }
            ],
            drawCallback: function () {
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

    // Reset filters
    $('#reset-filters').on('click', function () {
        $('#filter-name').val('');
        $('#filter-email').val('');
        $('#filter-mobile_no').val('');
        $('#filter-experience_years').val('');
        $('#filter-status').val('');
        if (dataTableInstance) {
            dataTableInstance.ajax.reload();
        }
    });

    // Apply filters
    $('.apply-filter').on('click', function () {
        if (dataTableInstance) {
            dataTableInstance.ajax.reload();
        }
        $('#filter').modal('hide');
    });
});
</script>

@endsection


