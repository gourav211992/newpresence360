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
                        <h2 class="content-header-title float-start mb-0">Pending Requests</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">List</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="">
                    <i data-feather="filter"></i> Filter
                </button>
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
                                            <th>S.No</th>
                                            <th>Doc Type</th>
                                            <th>Date</th>
                                            <th>Series</th>
                                            <th>Doc No.</th>
                                            <th>Rev No</th>
                                            <th>Party Name</th>
                                            <th>Currency</th>
                                            <th class="text-end">Total Amt</th>
                                            <th class="text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Apply Filter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-1">
                    <label class="form-label">Select Date</label>
                    <input type="text" id="fp-range" class="form-control flatpickr-range" placeholder="YYYY-MM-DD to YYYY-MM-DD" />
                </div>
                <div class="mb-1">
                    <label class="form-label">Order No.</label>
                    <select class="form-select">
                        <option>Select</option>
                    </select>
                </div>
                <div class="mb-1">
                    <label class="form-label">Customer Name</label>
                    <select class="form-select select2">
                        <option>Select</option>
                    </select>
                </div>
                <div class="mb-1">
                    <label class="form-label">Status</label>
                    <select class="form-select">
                        <option>Select</option>
                        <option>Open</option>
                        <option>Close</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary apply-filter">Apply</button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script src="{{ asset('assets/js/modules/common-datatable.js') }}"></script>
<script>
$(document).ready(function() {
    function renderData(data) {
    return data ? data.toString() : 'N/A';
    }
    var columns = [
        { data: 'DT_RowIndex', orderable: false, searchable: false },
        { data: 'document_type', name: 'document_type', render: renderData, createdCell: (td) => $(td).addClass('no-wrap') },
        { data: 'document_date', name: 'document_date', render: renderData, createdCell: (td) => $(td).addClass('no-wrap') },
        { data: 'book_name', name: 'book_name', render: renderData, createdCell: (td) => $(td).addClass('no-wrap') },
        { data: 'document_number', name: 'document_number', render: renderData, createdCell: (td) => $(td).addClass('no-wrap') },
        { data: 'revision_number', name: 'revision_number', render: renderData, createdCell: (td) => $(td).addClass('no-wrap') },
        { data: 'party_name', name: 'party_name', render: renderData, createdCell: (td) => $(td).addClass('no-wrap') },
        { data: 'currency', name: 'currency', render: renderData, createdCell: (td) => $(td).addClass('text-end') },
        { data: 'total_amount', name: 'total_amount', render: renderData, createdCell: (td) => $(td).addClass('text-end') },
        { data: 'document_status', name: 'document_status', render: renderData, createdCell: (td) => $(td).addClass('no-wrap text-center') },
    ];
    initializeDataTable('.datatables-basic',"{{ route('riv.requests') }}" ,columns, {},'Pending Requests',[0, 1, 2, 3, 4, 5, 6]);
});
</script>
@endsection
