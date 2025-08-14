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
                        <h2 class="content-header-title float-start mb-0">Pending Approvals</h2>
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
                <button class="btn btn-primary btn-sm mb-50 mb-sm-0" onclick ='BulkAction("approve");' ><i data-feather = 'layers'></i> Bulk Approval</button> 
                <button class="btn btn-danger btn-sm mb-50 mb-sm-0" onclick ='BulkAction("reject");' ><i data-feather = 'x-circle'></i> Bulk Reject</button> 
                <button class="btn btn-warning btn-sm mb-50 mb-sm-0" onclick ='openFiltersModal();' ><i data-feather="filter"></i> Filter</button> 
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
                                            <th>
                                                <div class="form-check form-check-primary custom-checkbox">
                                                    <input type="checkbox" class="form-check-input" id="select-all-checkbox" value="all" data-id="">
                                                    <label class="form-check-label" for="select-all-checkbox"></label>
                                                </div>
                                            </th>
                                            <th>Doc Type</th>
                                            <th>Date</th>
                                            <th>Series</th>
                                            <th>Doc No.</th>
                                            <th>Rev No</th>
                                            <th>Party Name</th>
                                            <th class="text-end">Currency</th>
                                            <th class="text-end">Total Amt</th>
                                            <th class="text-end">Submitted BY</th>
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

<!-- Bulk Result Modal -->
<div class="modal fade" id="bulkResultModal" tabindex="-1" aria-labelledby="bulkResultModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkResultModalLabel">Bulk Action Result</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="bulkResultTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="success-tab" data-bs-toggle="tab" data-bs-target="#success" type="button" role="tab" aria-controls="success" aria-selected="true">
                            Success
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="failure-tab" data-bs-toggle="tab" data-bs-target="#failure" type="button" role="tab" aria-controls="failure" aria-selected="false">
                            Failure
                        </button>
                    </li>
                </ul>
                <div class="tab-content mt-2" id="bulkResultTabsContent">
                    <div class="tab-pane fade show active" id="success" role="tabpanel" aria-labelledby="success-tab">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Document ID</th>
                                    <th>Status</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody id="bulkResultBodySuccess"></tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade" id="failure" role="tabpanel" aria-labelledby="failure-tab">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Document ID</th>
                                    <th>Status</th>
                                    <th>Message</th>
                                </tr>
                            </thead>
                            <tbody id="bulkResultBodyFailure"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
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
        { data: 'checkbox', orderable: false, searchable: false },
        { data: 'document_type', name: 'document_type', render: renderData, createdCell: (td) => $(td).addClass('no-wrap') },
        { data: 'document_date', name: 'document_date', render: renderData, createdCell: (td) => $(td).addClass('no-wrap') },
        { data: 'book_name', name: 'book_name', render: renderData, createdCell: (td) => $(td).addClass('no-wrap') },
        { data: 'document_number', name: 'document_number', render: renderData, createdCell: (td) => $(td).addClass('no-wrap') },
        { data: 'revision_number', name: 'revision_number', render: renderData, createdCell: (td) => $(td).addClass('no-wrap') },
        { data: 'party_name', name: 'party_name', render: renderData, createdCell: (td) => $(td).addClass('no-wrap') },
        { data: 'currency', name: 'currency', render: renderData, createdCell: (td) => $(td).addClass('text-end') },
        { data: 'total_amount', name: 'total_amount', render: renderData, createdCell: (td) => $(td).addClass('text-end') },
        { data: 'submitted_by', name: 'submitted_by', render: renderData, createdCell: (td) => $(td).addClass('text-center') },
        { data: 'document_status', name: 'document_status', render: renderData, createdCell: (td) => $(td).addClass('no-wrap text-center') },
    ];
    initializeDataTable('.datatables-basic', "{{ route('riv.approvals') }}", columns, {}, 'Pending Approvals', [0, 1, 2, 3, 4, 5, 6]);
});

$('#select-all-checkbox').on('change', function() {
    var isChecked = $(this).is(':checked');
    $('.myrequesttablecbox input[type="checkbox"]').prop('checked', isChecked);
});

function BulkAction(actionType) {
    var selectedIds = [];
    $('.myrequesttablecbox input.transaction-select-checkbox:checked').each(function() {
        selectedIds.push({
            document_id: $(this).attr('id'),
            alias: $(this).attr('alias')
        });
    });
    if (selectedIds.length === 0) {
        alert("Please select at least one item for bulk action.");
        return;
    }

    $.ajax({
        url: "{{ route('bulk.approvals') }}",
        type: "POST",
        data: {
            ids: selectedIds,
            actionType: actionType,
            _token: "{{ csrf_token() }}"
        },
        success: function(response) {
            let successRows = '';
            let failureRows = '';
            let successCount = 0;
            let failureCount = 0;

            response.data.forEach(item => {
            if (item.status === 'success') {
                successRows += `<tr>
                <td>${item.document_id}</td>
                <td>✅ Success</td>
                <td>${item.message}</td>
                </tr>`;
                successCount++;
            } else {
                failureRows += `<tr>
                <td>${item.document_id}</td>
                <td>❌ Failed</td>
                <td>${item.message}</td>
                </tr>`;
                failureCount++;
            }
            });

            // Fill the modal tab bodies
            $('#bulkResultBodySuccess').html(successRows || '<tr><td colspan="3" class="text-center">No Success Records</td></tr>');
            $('#bulkResultBodyFailure').html(failureRows || '<tr><td colspan="3" class="text-center">No Failure Records</td></tr>');

            // Optionally update tab titles with counts
            $('#success-tab').html(`Success (${successCount})`);
            $('#failure-tab').html(`Failure (${failureCount})`);

            $('#select-all-checkbox').prop('checked', false);
            $('#bulkResultModal').modal('show');
            $('.datatables-basic').DataTable().ajax.reload();
        },error: function(xhr) {
            alert('Bulk ' + actionType + ' failed!');
        }
    });
}
</script>
@include('partials.index-filter',$filterArray)
@endsection
