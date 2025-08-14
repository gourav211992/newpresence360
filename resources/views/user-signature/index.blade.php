@extends('layouts.app')


@section('title', 'User Signature')
@section('content')

    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">User Signature</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('user-signature.index') }}">Home</a></li>
                                    <li class="breadcrumb-item active">User Signature List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-end col-md-7 mb-2 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-primary btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
                            data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                        <a class="btn btn-dark btn-sm mb-50 mb-sm-0" href="{{ route('user-signature.create') }}"><i
                                data-feather="file-text"></i> Add New</a>
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
                                                <th class="pe-0 fw-bolder text-dark">#</th>
                                                <th>Name.</th>
                                                <th>Designation</th>
                                                <th>Upload Sign</th>
                                                <th>Action</th>
                                            </tr>

                                        </thead>
                                        <tbody>
                                            @isset($data)
                                                @forelse ($data as $index=>$d)
                                                    <tr>
                                                        <td class="pe-0 fw-bolder text-dark">{{ $index + 1 }} </td>
                                                        <td>{{ $d->user->name ?? '-' }}</td>
                                                        <td>{{ $d->designation ?? '-' }}</td>
                                                        <td class="fw-bolder text-dark">
                                                            @if ($d->sign_upload_file)
                                                                <a class="dropdown-item"
                                                                    href="{{ route('user-signature.showFile', $d->id) }}"
                                                                    target="_blank">
                                                                    <img src="{{ route('user-signature.showFile', $d->id) }}"  class="fileuploadicon" height="50" width="200" alt="file">
                                                                </a>
                                                            @endif
                                                        </td>
                                                        <td class="tableactionnew">
                                                            <div class="dropdown">
                                                                <button type="button"
                                                                    class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                                                    data-bs-toggle="dropdown">
                                                                    <i data-feather="more-vertical"></i>
                                                                </button>
                                                                <div class="dropdown-menu dropdown-menu-end">
                                                                     <a class="dropdown-item"
                                                                            href="{{ route('user-signature.edit', $d->id) }}">
                                                                            <i data-feather="plus-square" class="me-50"></i>
                                                                            <span>Edit</span>
                                                                        </a>
                                                                    </div>

                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center">No data found</td>
                                                    </tr>
                                                @endforelse
                                            @endisset

                                        </tbody>
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

    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <!-- Assuming the form is in the same Blade view -->
            <form class="add-new-record modal-content pt-0" method="GET" action="{{ route('user-signature.index') }}">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Select Date Range</label>
                        <input type="text" id="fp-range" name="date_range" class="form-control flatpickr-range"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" value="{{ old('date_range') }}" />
                    </div>


                    <div class="mb-1">
                        <label class="form-label" for="designation">Designation</label>
                        <select class="form-select" name="designation">
                            <option value="">Select</option>
                            @foreach ($data->unique('designation') as $item)
                                <option value="{{ $item->designation }}" {{ $item->designation == old('designation') ? 'selected' : '' }}>
                                    {{ ucfirst($item->designation) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer justify-content-start">
                        <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                        <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    </div>
            </form>


        </div>
    </div>

    </div>
@endsection
@section('scripts')
    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })

        $(function () {
    var dt_basic_table = $('.datatables-basic'),
        assetPath = '../../../app-assets/';

    if ($('body').attr('data-framework') === 'laravel') {
        assetPath = $('body').attr('data-asset-path');
    }

    if (dt_basic_table.length) {
        var dt_basic = dt_basic_table.DataTable({
            order: [], // Disable default sorting
            columnDefs: [
                {
                    orderable: false,
                    targets: [0, -1] // Disable sorting on the first and last columns
                }
            ],
            dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            displayLength: 10, // Default number of rows per page
            lengthMenu: [10, 25, 50, 75, 100],
            buttons: [
                {
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
                            }) + 'CSV',
                            className: 'dropdown-item',
                            filename: 'User_Signature_Report', // Set filename
                            exportOptions: {
                                columns: [1, 2, 3], // Export only Name, Designation, and Upload Sign
                                format: {
                                    header: function (data, columnIdx) {
                                        switch (columnIdx) {
                                            case 1:
                                                return 'Name';
                                            case 2:
                                                return 'Designation';
                                            case 3:
                                                return 'Sign';
                                            default:
                                                return data;
                                        }
                                    }
                                }
                            }
                        },
                        {
                            extend: 'excel',
                            text: feather.icons['file'].toSvg({
                                class: 'font-small-4 mr-50'
                            }) + 'Excel',
                            className: 'dropdown-item',
                            filename: 'User_Signature_Report', // Set filename
                            exportOptions: {
                                columns: [1, 2, 3], // Export only Name, Designation, and Upload Sign
                                format: {
                                    header: function (data, columnIdx) {
                                        switch (columnIdx) {
                                            case 1:
                                                return 'Name';
                                            case 2:
                                                return 'Designation';
                                            case 3:
                                                return 'Sign';
                                            default:
                                                return data;
                                        }
                                    }
                                }
                            }
                        }
                    ],
                    init: function (api, node, config) {
                        $(node).removeClass('btn-secondary');
                        $(node).parent().removeClass('btn-group');
                        setTimeout(function () {
                            $(node).closest('.dt-buttons').removeClass('btn-group')
                                .addClass('d-inline-flex');
                        }, 50);
                    }
                }
            ],
            language: {
                paginate: {
                    previous: '&nbsp;',
                    next: '&nbsp;'
                },
                search: "Search:", // Custom search label
                lengthMenu: "Show _MENU_ entries", // Custom entries label
                info: "Showing _START_ to _END_ of _TOTAL_ entries"
            }
        });

        // Update the label for the table
        $('div.head-label').html('<h6 class="mb-0">User Signature Report</h6>');
    }

    // Delete Record
    $('.datatables-basic tbody').on('click', '.delete-record', function () {
        dt_basic.row($(this).parents('tr')).remove().draw();
    });
});


        function showToast(icon, title) {
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                },
            });
            Toast.fire({
                icon,
                title
            });
        }

        @if (session('success'))
            showToast("success", "{{ session('success') }}");
        @endif

        @if (session('error'))
            showToast("error", "{{ session('error') }}");
        @endif

        @if ($errors->any())
            showToast('error',
                "@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach"
            );
        @endif
    </script>

@endsection
