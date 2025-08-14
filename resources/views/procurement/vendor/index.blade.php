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
                        <h2 class="content-header-title float-start mb-0">Vendor Master</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                                <li class="breadcrumb-item active">Vendor List</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right text-end col-md-7 mb-2 mb-sm-0">
                <div class="form-group breadcrumb-right">
                    <button class="btn btn-warning btn-sm mb-50 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                    <a href="{{ route('vendors.import') }}" class="btn btn-secondary btn-sm mb-50 mb-sm-0">
                        <i data-feather="upload"></i> Import
                    </a> 
                    <a class="btn btn-primary btn-sm" href="{{ route('vendor.create') }}"><i data-feather="plus-circle"></i> Add New</a>
                    <a class="btn btn-dark btn-sm mb-50 mb-sm-0" id="verify-gst-btn">
                        <i data-feather="check-circle"></i> Verify GST
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
                                <table class="datatables-basic table myrequesttablecbox tableistlastcolumnfixed">
                                    <thead>
                                        <tr>
                                            <th>S.NO</th>
                                            <th>Vendor Code</th>
                                            <th>Vendor Name</th>
                                            <th>Type</th>
                                            <th>Phone</th>
                                            <th>Email</th>
                                            <th>Group</th>
                                            <th>Gst Status</th>
                                            <th>Created At</th>
                                            <th>Created By</th>
                                            <th>Updated At</th>
                                            <th class="text-end">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
                <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
                    <div class="modal-dialog sidebar-sm">
                        <form class="add-new-record modal-content pt-0">
                            <div class="modal-header mb-1">
                                <h5 class="modal-title" id="exampleModalLabel">Apply Vendor Filter</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                            </div>
                            <div class="modal-body flex-grow-1">

                            <div class="mb-1">
                                    <label class="form-label">Group</label>
                                    <select id="filter-category" name="subcategory_id" class="form-select">
                                        <option value="">Select Group</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Vendor Type -->
                                <div class="mb-1">
                                    <label class="form-label">Vendor Type</label>
                                    <select id="filter-vendor-type" class="form-select">
                                        <option value="">Select Vendor Type</option>
                                        <option value="Individual">Individual</option>
                                        <option value="Organisation">Organisation</option>
                                    </select>
                                </div>
                                <!-- Gst Status -->
                                <div class="mb-1">
                                    <label class="form-label">Gst Status</label>
                                    <select id="filter-gst-status" class="form-select">
                                        <option value="">Select Status</option>
                                        <option value="ACT">Active</option>
                                        <option value="INACT">Inactive</option>
                                    </select>
                                </div>
                                <!-- Status -->
                                <div class="mb-1">
                                    <label class="form-label">Status</label>
                                    <select id="filter-status" class="form-select">
                                        <option value="">Select Status</option>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                        <option value="draft">Draft</option>
                                        <option value="submitted">Submitted</option>
                                        <option value="rejected">Rejected</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-start">
                                <button type="button" class="btn btn-primary apply-filter mr-1">Apply</button>
                                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
        </div>
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

    var dt_basic = dt_basic_table.DataTable({
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: "{{ route('vendor.index') }}",
            type: 'GET',
            data: function(d) {
                d.vendor_type = $('#filter-vendor-type').val();
                d.subcategory_id = $('#filter-category').val(); 
                d.gst_status = $('#filter-gst-status').val();
                d.status = $('#filter-status').val();
            }
        },
        "createdRow": function( row, data, dataIndex ) {
            $(row).find('td').addClass('text-nowrap');
         },
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'vendor_code', name: 'vendor_code', render: renderData },
            { data: 'company_name', name: 'company_name', render: renderData },
            { data: 'vendor_type', name: 'vendor_type', render: renderData },
            { data: 'phone', name: 'phone', render: renderData },
            { data: 'email', name: 'email', render: renderData },
            { data: 'subcategory.name', name: 'subcategory.name', render: renderData }, 
            {data: 'gst_status', name: 'gst_status', render: renderData,orderable: false},
            { data: 'created_at', name: 'created_at', render: function(data) {
                 return data ? data : 'N/A'; 
                }},
                {
                    data: 'created_by', 
                    name: 'created_by', 
                    render: function(data, type, row) {
                        return row.auth_user ? row.auth_user.name : 'N/A';
                    }
                },
                { data: 'updated_at', name: 'updated_at', render: function(data) {
                    return data ?data  : 'N/A'; 
                }},
                {
                    data: 'status',
                    name: 'status',
                    orderable: false,
                    searchable: false
                }
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
                        title: 'Vendor Master',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6,7,8,9,10,11] }
                    },
                    {
                        extend: 'csv',
                        text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + 'Csv',
                        className: 'dropdown-item',
                        title: 'Vendor Master',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6,7,8,9,10,11] }
                    },
                    {
                        extend: 'excel',
                        text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
                        className: 'dropdown-item',
                        title: 'Vendor Master',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6,7,8,9,10,11] }
                    },
                    {
                        extend: 'pdf',
                        text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 mr-50' }) + 'Pdf',
                        className: 'dropdown-item',
                        title: 'Vendor Master',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6,7,8,9,10,11] }
                    },
                    {
                        extend: 'copy',
                        text: feather.icons['copy'].toSvg({ class: 'font-small-4 mr-50' }) + 'Copy',
                        className: 'dropdown-item',
                        title: 'Vendor Master',
                        exportOptions: { columns: [0, 1, 2, 3, 4, 5, 6,7,8,9,10,11] }
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

    // Apply filter button click event
    $('.apply-filter').on('click', function() {
        dt_basic.ajax.reload(); 
        $('#filter').modal('hide'); 
    });
});
function handleRowSelection(tableSelector) {
    $(tableSelector).on('click', 'tbody tr', function () {
        $(tableSelector).find('tr').removeClass('trselected');
        $(this).addClass('trselected');
    });

    $(document).on('keydown', function (e) {
        const $selected = $(tableSelector).find('.trselected');
        if (e.which == 38) {  
            $selected.prev('tr').addClass('trselected').siblings().removeClass('trselected');
        } else if (e.which == 40) { 
            $selected.next('tr').addClass('trselected').siblings().removeClass('trselected');
        }
    });
}
handleRowSelection('.datatables-basic');
</script>
<script>
    $('#verify-gst-btn').click(function () {
        $.ajax({
            url: "{{ route('check-gst') }}",
            type: 'GET',
            beforeSend: function () {
                Swal.fire({
                    title: 'Verifying GST...',
                    html: 'Please wait while we verify the GST number.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            },
            error: function (xhr) {
                let errorMessage = xhr.responseJSON ? xhr.responseJSON.message : 'Something went wrong while verifying GST.';
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    timer: 4000,
                    timerProgressBar: true,
                    showConfirmButton: false
                });
            },
            complete: function () {
                $('#verify-gst-btn').html('<i data-feather="check-circle"></i> Verify GST');
                feather.replace();
            }
        });
    });
</script>
@endsection
