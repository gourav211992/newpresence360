@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">

            <div class="content-header row">
                <div class="content-header-left col-md-6 mb-1 mb-sm-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Kaizen List</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('kaizen.dashboard') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">Kaizens
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-6 ">
                    <div class="form-group breadcrumb-right">
                          <a href="{{route('kaizens.export')}}" target="_blank" class="btn btn-danger box-shadow-2 btn-sm"><i
                                        data-feather="download"></i> Export
                                </a>
                        <button class="btn btn-warning btn-sm mb-sm-0 mb-50" data-bs-target="#filter"
                            data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                        <a href="{{ route('kaizen.create') }}" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i
                                data-feather="plus-square"></i> Add New Kaizen</a>
                    </div>
                </div>
            </div>
            <div class="content-body dasboardnewbody">

                <!-- ChartJS section start -->
                <section id="chartjs-chart">
                    <div class="row">
                        <div class="col-md-12 col-12">
                            <div class="card  new-cardbox">

                                <div class="table-responsive candidates-tables border-0">
                                    @include('recruitment.partials.card-header')
                                    <table class="datatables-basic table table-striped">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Kaizen No</th>
                                                <th>Kaizen Date</th>
                                                <th>Kaizen Team</th>
                                                <th>Status</th>
                                                <th>Approver</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($kaizens as $kaizen)
                                                <tr>
                                                    <td>{{ $kaizens->firstItem() + $loop->index }}</td>
                                                    <td>{{ $kaizen->kaizen_no }}</td>
                                                    <td class="text-nowrap">
                                                        {{ $kaizen->kaizen_date ? App\Helpers\CommonHelper::dateFormat($kaizen->kaizen_date) : '' }}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $teams = @$kaizen->kaizenTeam;
                                                        @endphp
                                                        @if ($teams)
                                                            @foreach ($teams->take(2) as $team)
                                                                <span
                                                                    class="badge rounded-pill badge-light-secondary badgeborder-radius">
                                                                    {{ $team->name }}({{ $team->email }})
                                                                </span>
                                                            @endforeach
                                                        @endif

                                                        @if ($teams->count() > 2)
                                                            <a href="#" class="teamnum text-primary"
                                                                data-bs-toggle="modal" data-bs-target="#teamModal"
                                                                data-teams='@json($teams->pluck('name', 'email'))'>
                                                                <span class="teamnum">+{{ $teams->count() - 2 }}</span>
                                                            </a>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @php
                                                            if ($kaizen->status == 'approved') {
                                                                $className = 'badge-light-success';
                                                            } elseif ($kaizen->status == 'rejected') {
                                                                $className = 'badge-light-danger';
                                                            } else {
                                                                $className = 'badge-light-primary';
                                                            }
                                                        @endphp
                                                        <span
                                                            class="badge rounded-pill {{ $className }} badgeborder-radius">{{ ucfirst($kaizen->status) }}</span>
                                                    </td>
                                                    <td>{{ isset($kaizen->approver->name) ? $kaizen->approver->name : '-' }}
                                                    </td>
                                                    <td class="tableactionnew">
                                                        <div class="dropdown">
                                                            <button type="button"
                                                                class="btn btn-sm dropdown-toggle hide-arrow py-0"
                                                                data-bs-toggle="dropdown">
                                                                <i data-feather="more-vertical"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-end">
                                                                @if ($kaizen->status == App\Helpers\CommonHelper::PENDING && $kaizen->created_by == $user->id)
                                                                    <a class="dropdown-item"
                                                                        href="{{ route('kaizen.edit', ['id' => $kaizen->id]) }}">
                                                                        <i data-feather="edit-3" class="me-50"></i>
                                                                        <span>Edit</span>
                                                                    </a>

                                                                    <a class="dropdown-item" href="javascript:;"
                                                                        data-url="{{ route('kaizen.destroy', ['id' => $kaizen->id]) }}"
                                                                        data-request="remove">
                                                                        <i data-feather="trash-2" class="me-50"></i>
                                                                        <span>Delete</span>
                                                                    </a>
                                                                @endif

                                                                @if ($kaizen->status == App\Helpers\CommonHelper::PENDING && $kaizen->approver_id == $user->id)
                                                                    <a class="dropdown-item" href="javascript:;"
                                                                        data-bs-target="#status-modal"
                                                                        data-bs-toggle="modal"
                                                                        data-status="{{ App\Helpers\CommonHelper::REJECTED }}"
                                                                        data-title="Reject Kaizen Request"
                                                                        data-id="{{ $kaizen->id }}">
                                                                        <i data-feather="x" class="me-50"></i>
                                                                        <span>Reject</span>
                                                                    </a>

                                                                    <a class="dropdown-item" href="javascript:;"
                                                                        data-bs-target="#status-modal"
                                                                        data-bs-toggle="modal"
                                                                        data-status="{{ App\Helpers\CommonHelper::APPROVED }}"
                                                                        data-title="Approved Kaizen Request"
                                                                        data-id="{{ $kaizen->id }}">
                                                                        <i data-feather="check" class="me-50"></i>
                                                                        <span>Approved</span>
                                                                    </a>
                                                                @endif

                                                                {{-- @if ($kaizen->status == App\Helpers\CommonHelper::APPROVED) --}}
                                                                    <a class="dropdown-item"
                                                                        href="{{ url('kaizen/download-pdf/') . '/' . $kaizen->id }}">
                                                                        <i data-feather="download" class="me-50"></i>
                                                                        <span>Download</span>
                                                                    </a>
                                                                {{-- @endif --}}

                                                                <a class="dropdown-item"
                                                                    href="{{ route('kaizen.view', ['id' => $kaizen->id]) }}">
                                                                    <i data-feather="eye" class="me-50"></i>
                                                                    <span>View</span>
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td class="text-danger text-center" colspan="7">No record(s)
                                                        found.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                {{-- Pagination --}}
                                {{ $kaizens->appends(request()->input())->links('recruitment.partials.pagination') }}
                                {{-- Pagination End --}}
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    <!-- END: Content-->
    <!-- BEGIN: MODAL-->
    <div class="modal fade" id="teamModal" tabindex="-1" aria-labelledby="teamModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">All Teams</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="teamModalBody">
                    <!-- Teams will be injected here -->
                </div>
            </div>
        </div>
    </div>
    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <form class="add-new-record modal-content pt-0">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Select Date Range</label>
                        <input type="text" id="fp-range" class="form-control flatpickr-range"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" name="date_range"
                            value="{{ request('date_range') }}" />
                    </div>
                </div>
                <div class="modal-footer justify-content-start">
                    <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="status-modal" tabindex="-1" aria-labelledby="statusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="status-modal-title"></h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form role="post-data" method="POST" id="status-form" action=""
                    redirect="{{ route('kaizen.index') }}">
                    <div class="modal-body pb-2">
                        <div class="row mt-1">
                            <div class="col-md-12">
                                <div class="mb-1">
                                    <input type="hidden" name="status" class="form-control" value=""
                                        id="status-input">
                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="remarks"></textarea>
                                </div>

                            </div>

                        </div>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                        <button type="button" class="btn btn-primary" data-request="confirm-and-save"
                            data-target="[role=post-data]">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- END: MODAL-->
@endsection

@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const teamModal = document.getElementById('teamModal');
            teamModal.addEventListener('show.bs.modal', function(event) {
                const trigger = event.relatedTarget;
                const teams = JSON.parse(trigger.getAttribute('data-teams'));

                const body = teamModal.querySelector('#teamModalBody');
                body.innerHTML = ''; 

                Object.entries(teams).forEach(([email, name]) => {
                    const badge =
                        `<span class="badge rounded-pill badge-light-secondary badgeborder-radius me-1 mb-1">${name} (${email})</span>`;
                    body.innerHTML += badge;
                });
            });
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('[data-bs-toggle="modal"]');

            buttons.forEach(button => {
                button.addEventListener('click', function() {
                    const status = this.getAttribute('data-status');
                    const title = this.getAttribute('data-title');
                    const requestId = this.getAttribute('data-id');

                    const statusInput = document.getElementById('status-input');
                    statusInput.value = status;

                    const modalTitle = document.querySelector('#status-modal-title');
                    modalTitle.textContent = title;

                    const form = document.getElementById('status-form');
                    form.setAttribute('data-message', `Do you want to ${title}?`);
                    form.action = `{{ url('/kaizen/update-status/${requestId}') }}`;
                });
            });
        });
    </script>
@endsection
