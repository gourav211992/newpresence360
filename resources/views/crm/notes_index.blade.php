@extends('layouts.crm')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content todo-application">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">

            <div class="content-header row">
                <div class="content-header-left col-md-6 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">My Diary</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ url('/crm/home') }}">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">Dashboard
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <a href="{{ url('/crm/home') }}">
                            <button class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i>
                                Back</button>
                        </a>
                    </div>
                </div>
            </div>

            <div class="content-body dasboardnewbody">

                <section id="chartjs-chart">
                    <div class="row match-height">
                        <div class="col-md-12">
                            <div class="content-area-wrapper container-xxl p-0 h-100">
                                <div class="sidebar-left">
                                    <div class="sidebar">
                                        <div class="sidebar-content todo-sidebar">
                                            <div class="todo-app-menu">
                                                <div class="lefttask-added">
                                                    <a href={{ route('notes.create') }} class="btn btn-primary w-100">
                                                        Add Notes
                                                    </a>
                                                </div>
                                                <form action="">
                                                    <div class="mt-2 customernewsection-form seqarch-cleintdiary">
                                                        <div class="mb-2">
                                                            <label class="form-label">Customer</label>
                                                            <select class="form-select select2" name="customer_id">
                                                                <option value="" disabled
                                                                    {{ old('customer_id') ? '' : 'selected' }}>Select
                                                                </option>
                                                                @foreach ($customers as $customer)
                                                                    <option value="{{ $customer->id }}"
                                                                        {{ old('customer_id') == $customer->company_name ? 'selected' : '' }}>
                                                                        {{ $customer->company_name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <div class="mb-2">
                                                            <label class="form-label">Email-ID</label>
                                                            <select class="form-select select2" name="email">
                                                                <option value="" disabled
                                                                    {{ old('email') ? '' : 'selected' }}>Select</option>
                                                                @foreach ($emails as $email)
                                                                    <option value="{{ $email }}"
                                                                        {{ old('email') == $email ? 'selected' : '' }}>
                                                                        {{ $email }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>

                                                        <button type="submit"
                                                            class="btn btn-outline-primary btn-sm mb-0 waves-effect"><i
                                                                data-feather="search"></i> Search</button>
                                                    </div>

                                                    <div class="sidebar-menu-list filterdatediary">

                                                        <div class="mt-2 px-1 d-flex justify-content-between">
                                                            <h6 class="section-label mb-50">Filter</h6>
                                                        </div>
                                                        <div class="filseadiarlist">
                                                            <a href='{{ route('notes.index', ['date_range' => 'today']) }}'
                                                                class="">
                                                                <span class="bullet bullet-primary"></span>Today
                                                                ({{ $today_notes }})
                                                            </a>
                                                            <a href='{{ route('notes.index', ['date_range' => 'yesterday']) }}'
                                                                class="">
                                                                <span class="bullet bullet-success"></span>Yesterday
                                                                ({{ $yesterday_notes }})
                                                            </a>
                                                            <a href='{{ route('notes.index', ['date_range' => 'last_week']) }}'
                                                                class="">
                                                                <span class="bullet bullet-warning"></span>Last Week
                                                                ({{ $last_week_notes }})
                                                            </a>
                                                            <a href='{{ route('notes.index', ['date_range' => 'current_month']) }}'
                                                                class="">
                                                                <span class="bullet bullet-info"></span>Current Month
                                                                ({{ $current_month_notes }})
                                                            </a>
                                                            <a href='{{ route('notes.index', ['date_range' => 'last_month']) }}'
                                                                class="">
                                                                <span class="bullet bullet-danger"></span>Last Month
                                                                ({{ $last_month_notes }})
                                                            </a>
                                                        </div>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="content-right">
                                    <div class="card-body notesdiaryscroll">
                                        @foreach ($erp_diaries as $customerKey => $erpData)
                                            <div class="card mb-0 shadow-none rounded-0 overtimechart">
                                                @foreach ($erpData as $dateKey => $diaryDates)
                                                    <div
                                                        class="card-header newheader d-flex justify-content-between align-items-start">
                                                        <div class="header-left">
                                                            <h4 class="card-title">{{ $customerKey }} Notes</h4>
                                                            <p class="card-text">
                                                                {{ \Carbon\Carbon::parse($dateKey)->format('d-m-Y') }}</p>
                                                        </div>
                                                        <div class="sidebar-toggle d-block d-lg-none ms-1">
                                                            <i data-feather="menu" class="font-large-1"></i>
                                                        </div>
                                                    </div>

                                                    <div class="body-content-overlay"></div>
                                                    @foreach ($diaryDates as $data)
                                                        <ul class="timeline">
                                                            <li class="timeline-item">
                                                                <span class="timeline-point">
                                                                    <i data-feather="dollar-sign"></i>
                                                                </span>
                                                                <div class="timeline-event">
                                                                    <div
                                                                        class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                        <h6>{{ $data->subject }}</h6>
                                                                        <span class="timeline-event-time">
                                                                            @php
                                                                                $localTime = $data->created_at->setTimezone(
                                                                                    'Asia/Kolkata',
                                                                                );
                                                                            @endphp
                                                                            @if ($localTime->isToday())
                                                                                {{ $localTime->diffForHumans() }}
                                                                            @else
                                                                                {{ $localTime->format('g:i A') }}
                                                                            @endif
                                                                        </span>
                                                                    </div>
                                                                    <p>{{ $data->description }}</p>
                                                                    @if ($data->document_path)
                                                                        <div class="d-flex flex-row align-items-center">
                                                                            <a href="{{ asset($data->document_path) }}"
                                                                                target="_blank" class="me-1">
                                                                                <img src="{{ asset($data->document_path) }}"
                                                                                    alt="" height="23" />
                                                                            </a>
                                                                            @php
                                                                                $parts = explode(
                                                                                    'attachments/note_attchments/',
                                                                                    $data->document_path,
                                                                                );
                                                                                $filename = end($parts);
                                                                                $filenameParts = explode(
                                                                                    '-',
                                                                                    $filename,
                                                                                );
                                                                                $desiredPart =
                                                                                    $filenameParts[1] ?? 'N/A';
                                                                            @endphp
                                                                            <span>{{ $desiredPart }}</span>
                                                                        </div>
                                                                    @endif
                                                                    <div>
                                                                        <span class="text-muted">Participants</span>
                                                                        <div class="avatar-group mt-50">
                                                                            @if ($data->tagPeople)
                                                                                @foreach ($data->tagPeople as $tagPeople)
                                                                                    <div data-bs-toggle="tooltip"
                                                                                        data-popup="tooltip-custom"
                                                                                        data-bs-placement="bottom"
                                                                                        title="{{ $tagPeople->name }}"
                                                                                        class="avatar pull-up">
                                                                                        <img src="../../../app-assets/images/portrait/small/avatar-s-5.jpg"
                                                                                            alt="Avatar" height="30"
                                                                                            width="30" />
                                                                                    </div>
                                                                                @endforeach
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    @endforeach
                                                    {{-- <div class="d-flex justify-content-end mx-1 mt-50">
                                    <ul class="pagination">
                                        <li class="paginate_button page-item previous disabled">
                                            <a href="#" class="page-link">&nbsp;</a>
                                        </li>
                                        <li class="paginate_button page-item active">
                                            <a href="#" class="page-link">1</a>
                                        </li>
                                        <li class="paginate_button page-item ">
                                            <a href="#" class="page-link">2</a>
                                        </li>
                                        <li class="paginate_button page-item next">
                                            <a href="#" class="page-link">&nbsp;</a>
                                        </li>
                                    </ul>
                                </div> --}}
                                                @endforeach

                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                            </div>
                        </div>

                    </div>


            </div>
            </section>
            <!-- ChartJS section end -->

        </div>
    </div>
    </div>
    <!-- END: Content-->
@endsection
