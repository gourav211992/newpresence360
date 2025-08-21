@extends('layouts.app')

@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <h2 class="content-header-title float-start mb-0">Designation Master</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                <li class="breadcrumb-item active">Designation Master</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="datatables-basic table">
                                            <thead>
                                                <tr>
                                                    <th>S.NO.</th>
                                                    <th>Name</th>
                                                    <th>Marks</th>
                                                    <th>Status</th>
                                                    <th>Actions</th>
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

    <!-- Modal -->
    <div class="modal fade" id="designationMasterModal" tabindex="-1" aria-labelledby="designationMasterModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-4 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="designationMasterModalLabel">Add Designation Master</h1>
                    <p class="text-center">Enter the details below.</p>

                    <form action="" class="ajax-input-form" method="POST" id="designationMasterForm">
                        @csrf
                        <input type="hidden" name="_method" id="method" value="POST">
                        <input type="hidden" name="id" id="masterId" value="">

                        <div class="row mt-2">
                            <div class="col-md-12 mb-1">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" readonly>
                            </div>
                            <div class="col-md-12 mb-1">
                                <label for="remarks" class="form-label">Marks <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="marks" name = "marks" min="0"
                                    max="10">
                            </div>
                            <div class="col-md-12 mb-1">
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-12">
                                        <label class="form-label text-primary"><strong>Status</strong></label>
                                        <div class="demo-inline-spacing">
                                            @foreach ($status as $option)
                                                <div class="form-check form-check-primary mt-25">
                                                    <input type="radio" id="status_{{ strtolower($option) }}"
                                                        name="status" value="{{ $option }}" class="form-check-input"
                                                        {{ $option == 'active' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bolder"
                                                        for="status_{{ strtolower($option) }}">
                                                        {{ ucfirst($option) }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn"
                        form="designationMasterForm">Submit</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {

            const baseUrl = getBaseUrl();
            document.getElementById('marks').addEventListener('input', function() {
                if (this.value > 10) {
                    this.value = 10;
                } else if (this.value < 0) {
                    this.value = 0;
                }
            });

            $(document).on('click', '.edit-btn', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const marks = $(this).data('marks');
                const status = $(this).data('status');

                $('#masterId').val(id);
                $('#name').val(name);
                $('#marks').val(marks);
                $('input[name="status"][value="' + status + '"]').prop('checked', true);

                $('#designationMasterModalLabel').text('Edit Designation Master');
                $('#submitBtn').text('Update Designation');
                $('#designationMasterForm').attr('action', '{{ route('designation.update', '') }}/' + id);
                $('#method').val('PUT');

                $('#designationMasterModal').modal('show');
            });



            var dt_basic_table = $('.datatables-basic');

            function renderData(data) {
                return data ? data : 'N/A';
            }
            if (dt_basic_table.length) {
                var dt_designation_master = dt_basic_table.DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('designation.index') }}',
                    columns: [{
                            data: 'DT_RowIndex',
                            orderable: false,
                            searchable: false
                        },
                        {
                            data: 'name',
                            render: renderData
                        },
                        {
                            data: 'marks',
                            render: renderData
                        },
                        {
                            data: 'status',
                            render: renderData
                        },
                        {
                            data: 'actions',
                            orderable: false,
                            searchable: false
                        }
                    ],
                    dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    buttons: [{
                        extend: 'collection',
                        className: 'btn btn-outline-secondary dropdown-toggle',
                        text: feather.icons['share'].toSvg({
                            class: 'font-small-4 mr-50'
                        }) + 'Export',
                        buttons: [{
                                extend: 'print',
                                text: feather.icons['printer'].toSvg({
                                    class: 'font-small-4 mr-50'
                                }) + 'Print',
                                className: 'dropdown-item',
                                title: 'Designation Masters',
                                exportOptions: {
                                    columns: [0, 1, 2]
                                }
                            },
                            {
                                extend: 'csv',
                                text: feather.icons['file-text'].toSvg({
                                    class: 'font-small-4 mr-50'
                                }) + 'Csv',
                                className: 'dropdown-item',
                                title: 'Designation Masters',
                                exportOptions: {
                                    columns: [0, 1, 2]
                                }
                            },
                            {
                                extend: 'excel',
                                text: feather.icons['file'].toSvg({
                                    class: 'font-small-4 mr-50'
                                }) + 'Excel',
                                className: 'dropdown-item',
                                title: 'Designation Masters',
                                exportOptions: {
                                    columns: [0, 1, 2]
                                }
                            },
                            {
                                extend: 'pdf',
                                text: feather.icons['clipboard'].toSvg({
                                    class: 'font-small-4 mr-50'
                                }) + 'Pdf',
                                className: 'dropdown-item',
                                title: 'Designation Masters',
                                exportOptions: {
                                    columns: [0, 1, 2]
                                }
                            },
                            {
                                extend: 'copy',
                                text: feather.icons['copy'].toSvg({
                                    class: 'font-small-4 mr-50'
                                }) + 'Copy',
                                className: 'dropdown-item',
                                title: 'Designation Masters',
                                exportOptions: {
                                    columns: [0, 1, 2]
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
    </script>
@endsection
