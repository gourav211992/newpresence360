@extends('layouts.app')

@section('title', 'File Tracking')
@section('content')

<style>
    .btnact {
    background: rgba(255, 255, 255, 0.8); /* Light blur effect */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow for emphasis */
    backdrop-filter: blur(5px); /* Apply blur effect */
    border-radius: 8px; /* Rounded corners */
    transition: all 0.3s ease-in-out; /* Smooth transition for hover effect */
}

.btn_search.active:hover {
    background: rgba(255, 255, 255, 0.9); /* Slightly lighter background on hover */
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.15); /* Enhance shadow on hover */
}

</style>
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header row">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0"></h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('file-tracking.index') }}">Home</a></li>
                                    <li class="breadcrumb-item active">File Tracking List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-end col-md-7 mb-2 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <a href="{{route('file-tracking.index')}}" id="clear_btn" class="btn btn-danger btn-sm mb-50 mb-sm-0" style="display:none;"><i data-feather="x"></i> Clear</a>

                        <button class="btn btn-primary btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
                            data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                        <a class="btn btn-dark btn-sm mb-50 mb-sm-0" href="{{ route('file-tracking.create') }}"><i
                                data-feather="file-text"></i> Add New</a>

                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row match-height">
                    <div class="col-md-12">
                        <div class="card card-statistics new-cardbox">
                            <div class="card-body">
                                <form action="{{ route('file-tracking.index') }}" method="GET" id="filter-form">
                                    <input type="hidden" name="search" value="">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="taskboxassign {{ request('search') === 'overdue' ? '' : 'btnact' }}">
                                                <a data-search="overdue" class="btn_search">
                                                <div class="taslcatrdnum">
                                                    <div>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                            class="feather feather-box">
                                                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                                        </svg>
                                                    </div>
                                                    <h4>{{ $overdue_count ?? '0' }}</h4>
                                                </div>
                                                <p>Overdue<br>Documents</p>
                                                <!-- Hidden input to trigger filter on "overdue" -->

                                            </a>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="taskboxassign {{ request('search') === 'signed' ? '' : 'btnact' }}">
                                                <a data-search="signed" class="btn_search">
                                                    <div class="taslcatrdnum">
                                                        <div>
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-box">
                                                                <path
                                                                    d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                                            </svg>
                                                        </div>
                                                        <h4>{{ $signed_count ?? '0' }}</h4>
                                                    </div>
                                                    <p>Signed<br>Documents</p>
                                                    <!-- Hidden input to trigger filter on "signed" -->
                                                </a>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="taskboxassign {{ request('search') === 'unsigned' ? '' : 'btnact' }}">
                                                <a data-search="unsigned" class="btn_search active">
                                                <div class="taslcatrdnum" >
                                                    <div>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-box avatar-icon">
                                                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                                        </svg>
                                                    </div>
                                                    <h4>{{ $unsigned_count ?? '0' }}</h4>
                                                </div>
                                                <p>Un Signed<br>Documents</p>
                                                <!-- Hidden input to trigger filter on "unsigned" -->
                                            </a>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="taskboxassign {{ request('search') === 'pending' ? '' : 'btnact' }}">
                                                <a data-search="pending" class="btn_search">
                                                <div class="taslcatrdnum">
                                                    <div>
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                            stroke-linecap="round" stroke-linejoin="round" class="feather feather-box">
                                                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                                        </svg>
                                                    </div>
                                                    <h4>{{ $pending_count ?? '0' }}</h4>
                                                </div>
                                                <p>Pending<br>Documents</p>
                                                <!-- Hidden input to trigger filter on "pending" -->
                                            </a>
                                            </div>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                </div>
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="table-responsive">
                                    <table class="datatables-basic table myrequesttablecbox loanapplicationlist">
                                        <thead>
                                            <tr>
                                                <th class="pe-0 fw-bolder text-dark">#</th>
                                                <th>Doc No.</th>
                                                <th>Date</th>
                                                <th>File Name</th>
                                                <th>Document</th>
                                                <th>Review At</th>
                                                <th>Last Signed By</th>
                                                <th>Pending At</th>
                                                <th>Expected Completion Date</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>

                                        </thead>
                                        <tbody>
                                            @isset($data)
                                                @forelse ($data as $index=>$d)
                                                    @php
                                                        $teams_member = [];
                                                        $teams_member_name = [];
                                                        $pending_member = [];
                                                        $pending_member_name = [];
                                                        $teams = empty($d->approval_teams)
                                                            ? null
                                                            : json_decode($d->approval_teams);
                                                        $pending = empty($d->pending_signer)
                                                            ? null
                                                            : json_decode($d->pending_signer);
                                                        $last_signed_by = empty($d->signed_by)
                                                            ? null
                                                            : json_decode($d->signed_by);
                                                        $count =
                                                            $last_signed_by != null ? count($last_signed_by) : null;
                                                        $last_signed_by =
                                                            $last_signed_by != null
                                                                ? $last_signed_by[$count - 1]
                                                                : null;

                                                    @endphp

                                                    <tr>
                                                        <td class="pe-0 fw-bolder text-dark">{{ $index + 1 }} </td>
                                                        <td>{{ $d->document_number ?? '-' }}</td>
                                                        <td class="fw-bolder text-dark">
                                                            {{ date('d-m-Y', strtotime($d->document_date)) }}</td>
                                                        <td class="fw-bolder text-dark">{{ $d->file_name ?? '-' }}</td>
                                                        <td class="fw-bolder text-dark">
                                                        @if($d->signed_file)
                                                            <a class="dropdown-item" href="{{route('file-tracking.showSignFile',$d->id)}}" target="_blank">
                                                            <i data-feather="file-text" class="fileuploadicon"></i>
                                                        </a>
                                                        @else
                                                        <a class="dropdown-item" href="{{route('file-tracking.showFile',$d->id)}}" target="_blank">
                                                            <i data-feather="file-text" class="fileuploadicon"></i>
                                                        </a>

                                                        @endif
                                                    </td>
                                                        <td>
                                                            @if ($teams != null)
                                                                <div class="avatar-group">

                                                                    @foreach ($teams as $team)
                                                                        @isset($team->user_name)
                                                                            <div data-bs-toggle="tooltip"
                                                                                data-popup="tooltip-custom"
                                                                                data-bs-placement="top"
                                                                                @if (!empty($team->user_id)) title="{{ $team->user_name ?? '' }}" data-bs-original-title="{{ $team->user_name ?? '' }}" @endif
                                                                                class="avatar pull-up">

                                                                                @php
                                                                                    if (isset($team->user_name)) {
                                                                                        echo \App\Helpers\Helper::getInitials(
                                                                                            $team->user_name,
                                                                                        );
                                                                                    }
                                                                                @endphp


                                                                            </div>

                                                                            @if (!empty($team) && !empty($team->user_id))
                                                                                @php
                                                                                    $teams_member[] = $team->user_id;
                                                                                    $teams_member_name[] =
                                                                                        $team->user_name ?? '';
                                                                                @endphp
                                                                            @endif
                                                                        @endisset
                                                                    @endforeach

                                                                    <span class="usernames"
                                                                        style="display: none;">{{ implode(',', $teams_member_name) }}</span>

                                                                </div>
                                                            @endif

                                                        </td>
                                                        <td>
                                                            @if ($last_signed_by != null)
                                                                <div class="avatar-group">
                                                                    @isset($last_signed_by->user_name)
                                                                        <div data-bs-toggle="tooltip" data-popup="tooltip-custom"
                                                                            data-bs-placement="top"
                                                                            @if (!empty($last_signed_by->user_id)) title="{{ $last_signed_by->user_name ?? '' }}" data-bs-original-title="{{ $last_signed_by->user_name ?? '' }}" @endif
                                                                            class="avatar pull-up">

                                                                            @php
                                                                                if (isset($last_signed_by->user_name)) {
                                                                                    echo \App\Helpers\Helper::getInitials(
                                                                                        $last_signed_by->user_name,
                                                                                    );
                                                                                }
                                                                            @endphp
                                                                        @endisset

                                                                    </div>




                                                                </div>
                                                            @endif

                                                        </td>
                                                        <td>
                                                            @if ($pending != null)
                                                                <div class="avatar-group">

                                                                    @foreach ($pending as $pen)
                                                                        @isset($pen->user_name)
                                                                            <div data-bs-toggle="tooltip"
                                                                                data-popup="tooltip-custom"
                                                                                data-bs-placement="top"
                                                                                @if (!empty($pen->user_id)) title="{{ $pen->user_name ?? '' }}" data-bs-original-title="{{ $pen->user_name ?? '' }}" @endif
                                                                                class="avatar pull-up">

                                                                                @php
                                                                                    if (isset($pen->user_name)) {
                                                                                        echo \App\Helpers\Helper::getInitials(
                                                                                            $pen->user_name,
                                                                                        );
                                                                                    }
                                                                                @endphp


                                                                            </div>

                                                                            @if (!empty($pen) && !empty($pen->user_id))
                                                                                @php
                                                                                    $pending_member[] = $pen->user_id;
                                                                                    $pending_member_name[] =
                                                                                        $pen->user_name ?? '';
                                                                                @endphp
                                                                            @endif
                                                                        @endisset
                                                                    @endforeach

                                                                    <span class="usernames"
                                                                        style="display: none;">{{ implode(',', $pending_member_name) }}</span>

                                                                </div>
                                                            @endif

                                                        </td>

                                                        <td class="fw-bolder text-dark">
                                                            {{ date('d-m-Y', strtotime($d->expected_completion_date)) }}
                                                        </td>

                                                        <td style="text-transform: uppercase;">
                                                            @if ($d->document_status != 'draft')
                                                                <span
                                                                    class="badge rounded-pill
                                                            {{ $d->document_status == 'submitted'
                                                                ? 'badge-light-info'
                                                                : ($d->document_status == 'approved'
                                                                    ? 'badge-light-success'
                                                                    : 'badge-light-warning') }}
                                                            badgeborder-radius">
                                                                    {{ $d->document_status }}
                                                            @endif
                                                            </span>
                                                        </td>

                                                        <td class="tableactionnew">
                                                            <div class="dropdown">
                                                                <button type="button"
                                                                    class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                                                    data-bs-toggle="dropdown">
                                                                    <i data-feather="more-vertical"></i>
                                                                </button>
                                                                <div class="dropdown-menu dropdown-menu-end">
                                                                    @if ($d->document_status === 'draft')
                                                                        <a class="dropdown-item"
                                                                            href="{{ route('file-tracking.edit', $d->id) }}">
                                                                            <i data-feather="plus-square" class="me-50"></i>
                                                                            <span>Edit</span>
                                                                        </a>
                                                                    @endif
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('file-tracking.show', $d->id) }}">
                                                                        <i data-feather="check-circle" class="me-50"></i>
                                                                        <span>Sign & Detail</span>
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
            <form class="add-new-record modal-content pt-0" method="GET" action="{{ route('file-tracking.index') }}">
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
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Select</option>
                            @foreach ($data->unique('document_status') as $item)
                                <option value="{{ $item->document_status }}"
                                    {{ $item->document_status == old('status') ? 'selected' : '' }}>
                                    {{ ucfirst($item->document_status) }}
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

        $(function() {
            var dt_basic_table = $('.datatables-basic');

            if (dt_basic_table.length) {
                var dt_basic = dt_basic_table.DataTable({
                    order: [], // Disable default sorting
                    columnDefs: [{
                        targets: 7, // This is the "Expected Completion Date" column (index 7)
                        render: function(data, type, row) {
                            var status = $(row[8]).text()
                                .trim();; // Assuming 'Status' is in the 9th column (index 8)

                            // Log status to check if it's 'submitted' or something else
                            console.log('Status:', status);

                            if (status!='approved' && status!='draft' && status!='') {
                                var expectedDate = moment(data,
                                    'DD-MM-YYYY'); // Convert to moment
                                var today = moment(); // Current date
                                var dateColorClass = expectedDate.isBefore(today, 'day') ?
                                    'text-danger' : ''; // Check if the date is before today

                                // Return the formatted date with conditional styling
                                return `<span class="${dateColorClass}">${expectedDate.format('DD-MM-YYYY')}</span>`;
                            }
                            return data; // Return the raw data if not "submitted"
                        }
                    }],
                    dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    displayLength: 7,
                    lengthMenu: [7, 10, 25, 50, 75, 100],
                    buttons: [{
                        extend: 'collection',
                        className: 'btn btn-outline-secondary dropdown-toggle',
                        text: feather.icons['share'].toSvg({
                            class: 'font-small-4 me-50'
                        }) + 'Export',
                        buttons: [{
                                extend: 'csv',
                                text: feather.icons['file-text'].toSvg({
                                    class: 'font-small-4 me-50'
                                }) + 'Csv',
                                className: 'dropdown-item',
                                filename: 'File_TrackingReport', // Set filename as needed
                                exportOptions: {
                                    columns: function(idx, data, node) {
                                        // Exclude the first and last columns from CSV export
                                        return idx !== 0 && idx !== 9;
                                    },
                                    format: {
                                        header: function(data, columnIdx) {
                                            // Customize headers for CSV export
                                            switch (columnIdx) {
                                                case 1:
                                                    return 'Doc No.';
                                                case 2:
                                                    return 'Date';
                                                case 3:
                                                    return 'File Name';
                                                case 4:
                                                    return 'Review At';
                                                case 5:
                                                    return 'Last Signed By';
                                                case 6:
                                                    return 'Pending At';
                                                case 7:
                                                    return 'Expected Completion Date';
                                                case 8:
                                                    return 'Status';
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
                                    class: 'font-small-4 me-50'
                                }) + 'Excel',
                                className: 'dropdown-item',
                                filename: 'File_TrackingReport', // Set filename as needed
                                exportOptions: {
                                    columns: function(idx, data, node) {
                                        // Exclude the first and last columns from Excel export
                                        return idx !== 0 && idx !== 9;
                                    },
                                    format: {
                                        header: function(data, columnIdx) {
                                            // Customize headers for Excel export
                                            switch (columnIdx) {
                                                case 1:
                                                    return 'Doc No.';
                                                case 2:
                                                    return 'Date';
                                                case 3:
                                                    return 'File Name';
                                                case 4:
                                                    return 'Review At';
                                                case 5:
                                                    return 'Last Signed By';
                                                case 6:
                                                    return 'Pending At';
                                                case 7:
                                                    return 'Expected Completion Date';
                                                case 8:
                                                    return 'Status';
                                                default:
                                                    return data;
                                            }
                                        }
                                    }
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
                    language: {
                        paginate: {
                            previous: '&nbsp;',
                            next: '&nbsp;'
                        }
                    }
                });

                // Update the label for the table
                $('div.head-label').html('<h6 class="mb-0">File Tracking Report</h6>');
            }

            // Delete Record
            $('.datatables-basic tbody').on('click', '.delete-record', function() {
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

        function checkUrlParams() {
            const urlParams = new URLSearchParams(window.location.search);

            // Show button if any parameter exists
            if (urlParams.has('search')) {
                document.getElementById('clear_btn').style.display = '';
            } else {
                document.getElementById('clear_btn').style.display = 'none';

            }
        }

        // Initialize Feather Icons and check URL parameters
        feather.replace();
        checkUrlParams();
        $('.btn_search').on('click', function(e) {
            e.preventDefault(); // Prevent the default anchor behavior

            var filterValue = $(this).data('search');
            $('input[name="search"]').val(filterValue); // Set the value of the hidden search field

            // Trigger form submission
            $('#filter-form').submit();
        });
    </script>

@endsection
