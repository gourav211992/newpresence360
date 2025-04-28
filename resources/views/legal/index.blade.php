@extends('layouts.app')


@section('title', 'Legal')
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
                            <h2 class="content-header-title float-start mb-0">Legal</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('legal') }}">Home</a></li>
                                    <li class="breadcrumb-item active">Legal List</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-end col-md-7 mb-2 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button class="btn btn-primary btn-sm mb-50 mb-sm-0" data-bs-target="#filter"
                            data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                        <a class="btn btn-dark btn-sm mb-50 mb-sm-0" href="{{ route('legal.legal_add') }}"><i
                                data-feather="file-text"></i> Raise Request</a>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <div class="row match-height">
                    <div class="col-md-12">
                        <div class="card card-statistics new-cardbox">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="taskboxassign">
                                            <div class="taslcatrdnum">
                                                <div><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-box"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg></div>
                                                <h4>{{ $Unassigned_leases }}</h4>
                                            </div>
                                            <p>Unassigned<br>Request</p>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="taskboxassign">
                                            <div class="taslcatrdnum">
                                                <div><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-box avatar-icon"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg></div>
                                                <h4>{{ $Assigned_leases }}</h4>
                                            </div>
                                            <p>Assigned<br>Request</p>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="taskboxassign">
                                            <div class="taslcatrdnum">
                                                <div><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-box avatar-icon"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg></div>
                                                <h4>{{ $Closed_leases }}</h4>
                                            </div>
                                            <p>Closed<br>Request</p>
                                        </div>
                                    </div>

                                </div>
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
                                                <th class="pe-0">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="checkbox"
                                                            id="inlineCheckbox1">
                                                    </div>
                                                </th>
                                                <th>Issue Type</th>
                                                <th>Series</th>
                                                <th>Request No.</th>
                                                <th>Date</th>
                                                <th>Raised by</th>
                                                <th>Mobile No.</th>
                                                <th>Subject</th>
                                                <th>Team</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>

                                        </thead>
                                        <tbody>
                                            @foreach ($data as $d)
                                            @php $buttons = \App\Helpers\Helper::actionButtonDisplayForLegal($d->series, $d->status, $d->id, $d->approvalLevel, $d->user_id, ($d->type == 2) ? 'employee' : 2); @endphp
                                                <tr>
                                                    <td class="pe-0">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="inlineCheckbox1">
                                                        </div>
                                                    </td>
                                                    <td>{{ $d->issues_detail ? $d->issues_detail->name : '-' }}</td>
                                                    <td class="fw-bolder text-dark">{{ $d->serie->book_name }}</td>
                                                    <td class="fw-bolder text-dark">{{ $d->requestno }}</td>
                                                    <td>{{ date('d-m-Y', strtotime($d->created_at)) }}</td>
                                                    <td>{{ $d->name }}</td>
                                                    <td>{{ $d->phone }}</td>
                                                    <td>{{ $d->subject }}</td>
                                                    <td>
                                                        @php
                                                            $teams_member = [];
                                                            $teams_member_name = [];
                                                        @endphp
                                                        @if (!empty($d->teams))
                                                            <div class="avatar-group">
                                                               @foreach ($d->teams as $team)
                                                                <div data-bs-toggle="tooltip" data-popup="tooltip-custom" data-bs-placement="top"
                                                                     @if (!empty($team->user)) title="{{ $team?->user?->name }}" data-bs-original-title="{{ $team?->user?->name }}" @endif
                                                                     class="avatar pull-up">

                                                                    @if (!empty($team?->user?->imagebase64code))
                                                                        <img src="data:image/png;base64,{{ $team?->user?->imagebase64code }}" alt="Avatar" height="32" width="32">
                                                                   @else
                                                                    @php
                                                                        echo $team?->user?->name? \App\Helpers\Helper::getInitials($team?->user?->name) :"";
                                                                    @endphp
                                                                @endif

                                                                </div>

                                                                @if (!empty($team) && !empty($team->user))
                                                                    @php
                                                                        $teams_member[] = $team?->user?->id;
                                                                        $teams_member_name[] = $team?->user?->name;
                                                                    @endphp
                                                                @endif
                                                            @endforeach

                                                                <span class="usernames"
                                                                    style="display: none;">{{ implode(',', $teams_member_name) }}</span>

                                                            </div>
                                                        @endif

                                                    </td>
                                                    <td style="text-transform: uppercase;">
                                                        @if($d->status == 'Close')
                                                            <span
                                                                class="badge rounded-pill badge-light-success badgeborder-radius">{{$d->status}}</span>
                                                        @elseif (!empty($d->emails) && count($d->emails) > 0)
                                                            <span
                                                                class="badge rounded-pill badge-light-info badgeborder-radius">Waiting</span>
                                                        @elseif(!empty($d->teams) && count($d->teams) > 0)
                                                            <span
                                                                class="badge rounded-pill badge-light-warning badgeborder-radius">Assigned</span>
                                                        @elseif($d->status == 'draft')
                                                            <span
                                                                class="badge rounded-pill badge-light-warning badgeborder-radius">{{$d->status}}</span>
                                                        @elseif($d->status == 'approved')
                                                            <span
                                                                class="badge rounded-pill badge-light-success badgeborder-radius">{{$d->status}}</span>

                                                        @elseif($d->status == 'rejected')
                                                            <span
                                                                class="badge rounded-pill badge-light-warning badgeborder-radius">{{$d->status}}</span>
                                                        @elseif($d->status == 'submitted')
                                                            <span
                                                                class="badge rounded-pill badge-light-success badgeborder-radius">{{$d->status}}</span>
                                                        @else
                                                            <span
                                                                class="badge rounded-pill badge-light-secondary badgeborder-radius">{{$d->status}}</span>
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
                                                                @if($buttons['edit'])
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('legal.edit', ['id' => $d->id]) }}">
                                                                        <i data-feather="plus-square" class="me-50"></i>
                                                                        <span>Edit</span>
                                                                    </a>
                                                                @endif
                                                                @if($buttons['view'])
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('legal.legal_view', ['id' => $d->id]) }}">
                                                                        <i data-feather="check-circle" class="me-50"></i>
                                                                        <span>Reply & Detail</span>
                                                                    </a>
                                                                @endif
                                                                    @if($buttons['assign'])
                                                                    <a class="dropdown-item"
                                                                        onclick="showAssign({{ $d->id }},{{ $users }},{{ json_encode($teams_member) }})">
                                                                        <i data-feather="users" class="me-50"></i>
                                                                        <span>Assign Team</span>
                                                                    </a>
                                                                    @endif
                                                                    @if($buttons['close'])
                                                                    <a class="dropdown-item"
                                                                        onclick="showclosepopup({{ $d->id }})">
                                                                        <i data-feather="users" class="me-50"></i>
                                                                        <span>Mark as Close</span>
                                                                    </a>
                                                                    @endif
                                                                    @if($buttons['approve'])
                                                                    <a class="dropdown-item"
                                                                            onclick="showapprovepopup({{ $d->id }})">
                                                                            <i data-feather="users" class="me-50"></i>
                                                                            <span>Approve</span>
                                                                        </a>
                                                                    @endif
                                                                    @if($buttons['reject'])
                                                                        <a class="dropdown-item"
                                                                            onclick="showrejectpopup({{ $d->id }})">
                                                                            <i data-feather="users" class="me-50"></i>
                                                                            <span>Reject</span>
                                                                        </a>
                                                                    @endif

                                                                    </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal to add new record -->
                    <div class="modal modal-slide-in fade" id="modals-slide-in">
                        <div class="modal-dialog sidebar-sm">
                            <form class="add-new-record modal-content pt-0">
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                    aria-label="Close">×</button>
                                <div class="modal-header mb-1">
                                    <h5 class="modal-title" id="exampleModalLabel">New Record</h5>
                                </div>
                                <div class="modal-body flex-grow-1">
                                    <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-fullname">Full Name</label>
                                        <input type="text" class="form-control dt-full-name"
                                            id="basic-icon-default-fullname" placeholder="John Doe"
                                            aria-label="John Doe" />
                                    </div>
                                    <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-post">Post</label>
                                        <input type="text" id="basic-icon-default-post" class="form-control dt-post"
                                            placeholder="Web Developer" aria-label="Web Developer" />
                                    </div>
                                    <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-email">Email</label>
                                        <input type="text" id="basic-icon-default-email" class="form-control dt-email"
                                            placeholder="john.doe@example.com" aria-label="john.doe@example.com" />
                                        <small class="form-text"> You can use letters, numbers & periods </small>
                                    </div>
                                    <div class="mb-1">
                                        <label class="form-label" for="basic-icon-default-date">Joining Date</label>
                                        <input type="text" class="form-control dt-date" id="basic-icon-default-date"
                                            placeholder="MM/DD/YYYY" aria-label="MM/DD/YYYY" />
                                    </div>
                                    <div class="mb-4">
                                        <label class="form-label" for="basic-icon-default-salary">Salary</label>
                                        <input type="text" id="basic-icon-default-salary"
                                            class="form-control dt-salary" placeholder="$12000" aria-label="$12000" />
                                    </div>
                                    <button type="button" class="btn btn-primary data-submit me-1">Submit</button>
                                    <button type="reset" class="btn btn-outline-secondary"
                                        data-bs-dismiss="modal">Cancel</button>
                                </div>
                            </form>
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
            <form class="add-new-record modal-content pt-0" method="GET" action="{{ route('legal.filter') }}">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Select Date Range</label>
                        <input type="text" id="fp-range" name="date_range" class="form-control flatpickr-range"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" value="{{ old('date_range', $selectedDateRange) }}" />
                    </div>

                    <div class="mb-1">
                        <label class="form-label" for="request-no">Request No.</label>
                        <select class="form-select select2" name="request_no">
                            <option value="">Select</option>
                            @foreach ($requests as $request)
                                <option value="{{ $request }}"
                                    {{ $request == old('request_no', $selectedRequestNo) ? 'selected' : '' }}>
                                    {{ $request }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label" for="raised-by">Raised By</label>
                        <select class="form-select" name="raised_by">
                            <option value="">Select</option>
                            @foreach ($raisedByOptions as $option)
                                <option value="{{ $option }}"
                                    {{ $option == old('raised_by', $selectedRaisedBy) ? 'selected' : '' }}>
                                    {{ $option }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- <div class="mb-1">
                        <label class="form-label" for="status">Status</label>
                        <select class="form-select" name="status">
                            <option value="">Select</option>
                            <option value="Waiting" {{ 'Waiting' == old('status', $selectedStatus) ? 'selected' : '' }}>
                                Waiting</option>
                            <option value="Assigned" {{ 'Assigned' == old('status', $selectedStatus) ? 'selected' : '' }}>
                                Assigned</option>
                            <option value="Pending" {{ 'Pending' == old('status', $selectedStatus) ? 'selected' : '' }}>
                                Pending</option>
                            <option value="Close" {{ 'Close' == old('status', $selectedStatus) ? 'selected' : '' }}>
                                Closed</option>

                        </select> -->
                    <!-- </div> -->
                </div>
                <div class="modal-footer justify-content-start">
                    <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>


        </div>
    </div>
    <div class="modal fade" id="approved" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Assign Team
                        </h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Fill the below details</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="assign-team-form" action="{{ route('legal.assignsubmit') }}" method="post">
                    @csrf
                    <div class="modal-body pb-2">
                        <div class="row mt-1">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label">Select Team <span class="text-danger">*</span></label>
                                    <select class="form-select select2" name="team[]" id="teams" multiple>
                                        <!-- Options should be dynamically populated -->
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <label class="form-label">Remarks</label>
                                    <textarea class="form-control" name="remarks" maxlength="250"></textarea>
                                </div>
                            </div>
                            <input type="hidden" name="assignid" id="assignid" />
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="reset" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal"
                            aria-label="Close">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="Closed" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Close Application</h4>
                        <!-- <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Nishu Garg | 20 Lkh | 29-07-2024</p> -->
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-2">
                    <input type="hidden" id="selected_ids" name="selected_ids">
                    <div class="row mt-1">
                        <div class="col-md-12">
                            <div class="mb-1">
                                <label class="form-label">Remarks</label>
                                <textarea class="form-control" id="remarks" name="remarks"></textarea>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="button" id="submit-approval" class="btn btn-primary" onclick="showclose()">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="Approvedpopup" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Approve Application</h4>
                        <!-- <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Nishu Garg | 20 Lkh | 29-07-2024</p> -->
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-2">
                    <input type="hidden" id="approveselected_ids" name="selected_ids">
                    <div class="row mt-1">
                        <div class="col-md-12">
                            <div class="mb-1">
                                <label class="form-label">Remarks</label>
                                <textarea class="form-control" id="approveremarks" name="remarks"></textarea>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="button" id="submit-approval" class="btn btn-primary" onclick="showapprove()">Submit</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="Rejected" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Reject Application</h4>
                        <!-- <p class="mb-0 fw-bold voucehrinvocetxt mt-0">Nishu Garg | 20 Lkh | 29-07-2024</p> -->
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pb-2">
                    <input type="hidden" id="rejectselected_ids" name="selected_ids">
                    <div class="row mt-1">
                        <div class="col-md-12">
                            <div class="mb-1">
                                <label class="form-label">Remarks</label>
                                <textarea class="form-control" id="rejectremarks" name="remarks"></textarea>
                            </div>
                        </div>
                    </div>
                </div>


                <div class="modal-footer justify-content-center">
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="button" id="submit-approval" class="btn btn-primary" onclick="showreject()">Submit</button>
                </div>
            </div>
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
            var dt_basic_table = $('.datatables-basic'),
                assetPath = '../../../app-assets/';

            if ($('body').attr('data-framework') === 'laravel') {
                assetPath = $('body').attr('data-asset-path');
            }

            if (dt_basic_table.length) {
                var dt_basic = dt_basic_table.DataTable({
                    order: [], // Disable default sorting
                    columnDefs: [{
                            orderable: false,
                            targets: [0, -1]
                        }, // Disable sorting on the first and last columns
                        {
                            targets: 8, // Adjust this index according to your column number
                            render: function(data, type, row, meta) {
                                if (type === 'export') {
                                    // Return usernames for export
                                    var $node = $('<div>').html(data);
                                    return $node.find('.usernames').text();
                                }
                                return data;
                            }
                        }
                    ],
                    dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                    displayLength: 7,
                    lengthMenu: [7, 10, 25, 50, 75, 100],
                    buttons: [{
                        extend: 'collection',
                        className: 'btn btn-outline-secondary dropdown-toggle',
                        text: feather.icons['share'].toSvg({
                            class: 'font-small-4 mr-50'
                        }) + 'Export',
                        buttons: [{
                                extend: 'csv',
                                text: feather.icons['file-text'].toSvg({
                                    class: 'font-small-4 mr-50'
                                }) + 'Csv',
                                className: 'dropdown-item',
                                filename: 'Legal_Report', // Set filename as needed
                                exportOptions: {
                                    columns: function(idx, data, node) {
                                        // Exclude the first and last columns from CSV export
                                        return idx !== 0 && idx !== 10;
                                    },
                                    format: {
                                        header: function(data, columnIdx) {
                                            // Customize headers for CSV export
                                            switch (columnIdx) {
                                                case 1:
                                                    return 'Issue Type';
                                                case 2:
                                                    return 'Series';
                                                case 3:
                                                    return 'Request No.';
                                                case 4:
                                                    return 'Date';
                                                case 5:
                                                    return 'Raised by';
                                                case 6:
                                                    return 'Mobile No.';
                                                case 7:
                                                    return 'Subject';
                                                case 8:
                                                    return 'Team';
                                                case 9:
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
                                    class: 'font-small-4 mr-50'
                                }) + 'Excel',
                                className: 'dropdown-item',
                                filename: 'Legal_Report', // Set filename as needed
                                exportOptions: {
                                    columns: function(idx, data, node) {
                                        // Exclude the first and last columns from Excel export
                                        return idx !== 0 && idx !== 10;
                                    },
                                    format: {
                                        header: function(data, columnIdx) {
                                            // Customize headers for Excel export
                                            switch (columnIdx) {
                                                case 1:
                                                    return 'Issue Type';
                                                case 2:
                                                    return 'Series';
                                                case 3:
                                                    return 'Request No.';
                                                case 4:
                                                    return 'Date';
                                                case 5:
                                                    return 'Raised by';
                                                case 6:
                                                    return 'Mobile No.';
                                                case 7:
                                                    return 'Subject';
                                                case 8:
                                                    return 'Team';
                                                case 9:
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
                $('div.head-label').html('<h6 class="mb-0">Legal Report</h6>');
            }

            // Flat Date picker
            if (dt_date_table.length) {
                dt_date_table.flatpickr({
                    monthSelectorType: 'static',
                    dateFormat: 'm/d/Y'
                });
            }

            // Delete Record
            $('.datatables-basic tbody').on('click', '.delete-record', function() {
                dt_basic.row($(this).parents('tr')).remove().draw();
            });
        });







        function showAssign(id, users, teams) {
            $("#assignid").val(id);
            $("#approved").modal('show');
            var team = $('#teams');
            team.empty(); // Clear any existing options
            team.append('<option value="" disabled>Select</option>');

            console.log(teams);
            console.log(users);

            $.each(users, function(key, value) {
                if (!teams.includes(value.id)) {
                    team.append('<option value="' + value.id + '">' + value.name + '</option>');
                }
            });
        }

        function showclosepopup(id)
        {
            $("#Closed").modal('show');
            $("#selected_ids").val(id);
        }

        function showapprovepopup(id)
        {
            $("#Approvedpopup").modal('show');
            $("#approveselected_ids").val(id);
        }

        function showrejectpopup(id)
        {
            $("#Rejected").modal('show');
            $("#rejectselected_ids").val(id);
        }

        function showclose()
        {
            var id = $("#selected_ids").val();
            var remark = $("#remarks").val();
           fetch("{{url('legal/close')}}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                id: id,
                remark:remark,
            })
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  alert('Status updated successfully!');
                  location.reload();
              } else {
                  alert('Failed to update status.');
              }
          });
        }

        function showapprove()
        {
            var id = $("#approveselected_ids").val();
            var remark = $("#approveremarks").val();
           fetch("{{url('/legal/appr-rej')}}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                appr_rej_land_id: id,
                remark:remark,
                page:'list',
                appr_rej_status:'approve'
            })
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  alert('Status updated successfully!');
                  location.reload();
              } else {
                  alert('Failed to update status.');
              }
          });
        }

        function showreject()
        {
            var id = $("#rejectselected_ids").val();
            var remark = $("#rejectremarks").val();
           fetch("{{url('/legal/appr-rej')}}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                appr_rej_land_id: id,
                remark:remark,
                page:'list',
                appr_rej_status:'reject'})
            
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  alert('Status updated successfully!');
                  location.reload();
              } else {
                  alert('Failed to update status.');
              }
          });
        }

        $('#assign-team-form').on('submit', function(e) {
            // Get the select element
            var teamSelect = $('#teams');

            // Check if at least one option is selected
            if (teamSelect.val().length === 0) {
                e.preventDefault(); // Prevent form submission
                alert('Please select at least one team member.');
                return false;
            }
        });
    </script>

@endsection
