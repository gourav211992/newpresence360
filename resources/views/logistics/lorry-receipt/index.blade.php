@extends('layouts.app')

@section('content')
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row">
            <div class="content-header-left col-md-5 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-start mb-0">Lorry Receipts</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">Lorry Receipt List</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right text-end col-md-7 mb-2 mb-sm-0">
                <div class="form-group breadcrumb-right">
                    <button class="btn btn-warning btn-sm me-1 mb-20 mb-sm-0" data-bs-toggle="modal" data-bs-target="#filter">
                        <i data-feather="filter"></i> Filter
                    </button>
                    <a class="btn btn-primary btn-sm" href="{{ route('logistics.lorry-receipt.create') }}">
                        <i data-feather="plus-circle"></i> Add New
                    </a>
                </div>
            </div>
        </div>

        <div class="content-body">
            <section id="basic-datatable">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="table-responsive">
                                <table class="datatables-basic table table-striped">
                                    <thead>
                                        <tr>
                                            <th>S.NO</th>
                                            <th>Series</th>
                                            <th>Document No</th>
                                            <th>Document Date</th>
                                            <th>Source</th>
                                            <th>Destination</th>
                                            <th>Driver</th>
                                            <th>Vehicle No.</th>
                                            <th>Total Charges</th>
                                            <th>Status</th>
                                            <th>Created By</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Filter Modal -->
            <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
                <div class="modal-dialog sidebar-sm">
                    <form class="add-new-record modal-content pt-0" id="filter-form">
                        <div class="modal-header mb-1">
                            <h5 class="modal-title">Apply Lorry Receipt Filter</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                        </div>
                        <div class="modal-body flex-grow-1">
                            <div class="mb-1">
                                <label class="form-label">LR No</label>
                                <input type="text" id="filter-lr-no" name="lr_no" class="form-control">
                            </div>
                            <div class="mb-1">
                                <label class="form-label">Source</label>
                                <input type="text" id="filter-source-id" name="source_id" class="form-control">
                            </div>
                            <div class="mb-1">
                                <label class="form-label">Destination</label>
                                <input type="text" id="filter-destination-id" name="destination_id" class="form-control">
                            </div>
                            <div class="mb-1">
                                <label class="form-label">Driver</label>
                                <select id="filter-driver-id" name="driver_id" class="form-select select2">
                                    <option value="">Select</option>
                                    @foreach($drivers as $driver)
                                        <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-1">
                                <label class="form-label">Document Date</label>
                                <input type="date" id="filter-document-date" name="document_date" class="form-control">
                            </div>
                            <div class="mb-1">
                                <label class="form-label">Status</label>
                                <select id="filter-status" name="status" class="form-select">
                                    <option value="">Select Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
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
            <!-- End Filter Modal -->
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function () {
    let dataTableInstance;

    if ($('.datatables-basic').length) {
        dataTableInstance = $('.datatables-basic').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('logistics.lorry-receipt.index') }}",
                data: function(d) {
                    d.lr_no = $('#filter-lr-no').val();
                    d.source_id = $('#filter-source-id').val();
                    d.destination_id = $('#filter-destination-id').val();
                    d.driver_id = $('#filter-driver-id').val();
                    d.status = $('#filter-status').val();
                    d.document_date = $('#filter-document-date').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'series' },
                { data: 'document_number' },
                { data: 'document_date' },
                { data: 'source_name' },
                { data: 'destination_name' },
                { data: 'driver_name' },
                { data: 'vehicle_no' },
                { data: 'total_charges' },
                {
                data: 'document_status',
                render: function(data, type, row) {
                    switch(data?.toLowerCase()) {
                        case 'approval_not_required':
                        case 'approved':
                            return '<span class="badge rounded-pill badge-light-success">Approved</span>';
                        case 'draft':
                            return '<span class="badge rounded-pill badge-light-warning">Draft</span>';
                        case 'rejected':
                            return '<span class="badge rounded-pill badge-light-danger">Rejected</span>';
                        case 'partially_approved':
                            return '<span class="badge rounded-pill badge-light-warning">Partially Approved</span>';
                        case 'submitted':
                            return '<span class="badge rounded-pill badge-light-primary">Submitted</span>';
                        default:
                            return row.document_status_html || data; 
                    }
                 }
              },
                {
                    data: 'created_by', 
                    name: 'created_by', 
                    render: function(data, type, row) {
                        return row.auth_user ? row.auth_user.name : 'N/A';
                    }
                },
                { data: 'action', orderable: false, searchable: false }
            ],
            dom: '<"d-flex justify-content-between align-items-center mx-2 row"' +
                    '<"col-sm-12 col-md-6"l>' +
                    '<"col-sm-12 col-md-6"f>>' +
                't' +
                '<"d-flex justify-content-between mx-2 row"' +
                    '<"col-sm-12 col-md-6"i>' +
                    '<"col-sm-12 col-md-6"p>>',
            drawCallback: function () {
                feather.replace();
            },
            language: {
                paginate: {
                    previous: '&nbsp;',
                    next: '&nbsp;'
                }
            }
        });
    }

    $('.apply-filter').on('click', function () {
        if (dataTableInstance) {
            dataTableInstance.ajax.reload();
        }
        $('#filter').modal('hide');
    });

    $('#reset-filters').on('click', function () {
        $('#filter-form')[0].reset();
        $('.select2').val(null).trigger('change');
        if (dataTableInstance) {
            dataTableInstance.ajax.reload();
        }
    });
});
</script>
@endsection
