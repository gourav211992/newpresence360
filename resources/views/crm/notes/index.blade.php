@extends('layouts.app')

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
                                    <li class="breadcrumb-item"><a href="index.html">Home</a>
                                    </li>
                                    <li class="breadcrumb-item active">My Diary
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                data-feather="arrow-left-circle"></i> Back</button>
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
                                                    <a href="{{ route('notes.create') }}" class="btn btn-primary w-100">
                                                        Add Notes
                                                    </a>
                                                </div>
                                                <div class="mt-2 customernewsection-form seqarch-cleintdiary">

                                                    <div class="customernewsection-form ms-75 mb-2">
                                                        <div class="">
                                                            @foreach ($meetingObjectives as $objective)
                                                                <div class="form-check form-check-primary mb-75">
                                                                    <input type="radio" id="customColorRadio1"
                                                                        name="objective" value="{{ $objective->id }}"
                                                                        class="form-check-input"
                                                                        {{ Request::get('objective') == $objective->id ? 'checked' : '' }}>
                                                                    <label class="form-check-label fw-bolder"
                                                                        for="customColorRadio1">{{ $objective->title }}</label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>

                                                    <div class="mt-2 px-75">
                                                        <h6 class="text-dark mb-1"><strong>Date Range</strong></h6>
                                                    </div>

                                                    <div class="customernewsection-form ms-75 mb-2">
                                                        <div class="">
                                                            <div class="form-check form-check-primary mb-75">
                                                                <input type="radio" id="Today" name="daterange"
                                                                    class="form-check-input" value="today"
                                                                    {{ Request::get('daterange') == 'today' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bold"
                                                                    for="Today">Today</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mb-75">
                                                                <input type="radio" id="week" value="this week"
                                                                    name="daterange" class="form-check-input"
                                                                    {{ Request::get('daterange') == 'this week' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bold" for="week">This
                                                                    week</label>
                                                            </div>
                                                            <div class="form-check form-check-primary  mb-75">
                                                                <input type="radio" id="Month" name="daterange"
                                                                    value="this month" class="form-check-input"
                                                                    {{ Request::get('daterange') == 'this month' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bold" for="Month">This
                                                                    Month</label>
                                                            </div>
                                                            <div class="form-check form-check-primary  mb-75">
                                                                <input type="radio" id="year" name="daterange"
                                                                    value="this year" class="form-check-input"
                                                                    {{ Request::get('daterange') == 'this year' ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bold" for="year">This
                                                                    year</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-0">
                                                                <input type="radio" id="custom" name="daterange"
                                                                    value="custom"
                                                                    {{ Request::get('daterange') == 'custom' ? 'checked' : '' }}
                                                                    class="form-check-input">
                                                                <label class="form-check-label fw-bold"
                                                                    for="Custom">Custom</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-2" id="custom-date-container" style="display: none;">
                                                        <label class="form-label">Select Date</label>
                                                        <input type="date" name="date" class="form-control"
                                                            value="{{ Request::get('date') }}" />
                                                    </div>


                                                    <div class="mb-1">
                                                        <label class="form-label">Customer</label>
                                                        <select class="form-select select2" name="customer_id">
                                                            <option value=""
                                                                {{ Request::get('customer_id') == '' ? 'selected' : '' }}>
                                                                Select</option>
                                                            @foreach ($customers as $customer)
                                                                <option value="{{ $customer->id }}"
                                                                    {{ Request::get('customer_id') == $customer->id ? 'selected' : '' }}>
                                                                    {{ $customer->company_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="lefttask-added">
                                                    <button type="button" id="search-btn"
                                                        class="btn btn-primary w-45 mb-2" onclick="applyFilter()"><i
                                                            data-feather="search"></i> Search</button>
                                                    <a
                                                        href="{{ url('/crm/notes') }}"class="btn btn-outline-secondary  w-50 mb-2">Clear</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="content-right">
                                    <div class="card mb-0 bg-light shadow-none rounded-0 overtimechart">
                                        <div
                                            class="card-header newheader d-flex justify-content-between align-items-start">
                                            <div class="header-left text-center">
                                                <h4 class="card-title">
                                                    <a href="#" class="me-1" id="previous"><i
                                                            data-feather="arrow-left-circle"></i></a>
                                                    <span
                                                        id="current-date">{{ Request::get('date') ?? date('d-m-Y') }}</span>
                                                    <a href="#" class="ms-1" id="next"><i
                                                            data-feather="arrow-right-circle"></i></a>
                                                </h4>
                                            </div>
                                            <div class="sidebar-toggle d-block d-lg-none ms-1">
                                                <i data-feather="menu" class="font-large-1"></i>
                                            </div>
                                        </div>
                                        <div id="render-diaries">
                                            @include('crm.notes.diary-list', [
                                                'erpDiaries' => $erpDiaries,
                                            ])
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

@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const customRadio = document.getElementById('custom');
            const customDateContainer = document.getElementById('custom-date-container');
            const dateInput = document.querySelector('input[name="date"]');

            function toggleCustomDateInput() {
                if (customRadio.checked) {
                    customDateContainer.style.display = 'block';
                } else {
                    customDateContainer.style.display = 'none';
                    dateInput.value = '';
                }
            }

            customRadio.addEventListener('change', toggleCustomDateInput);

            const otherRadios = document.querySelectorAll('input[name="daterange"]');
            otherRadios.forEach(function(radio) {
                radio.addEventListener('change', function() {
                    if (this !== customRadio) {
                        customDateContainer.style.display = 'none';
                        dateInput.value = '';
                    }
                });
            });

            toggleCustomDateInput();
        });
    </script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const currentDateElement = document.getElementById('current-date');
            const previousButton = document.getElementById('previous');
            const nextButton = document.getElementById('next');

            function formatDateToInput(date) {
                let day = date.getDate();
                let month = date.getMonth() + 1;
                let year = date.getFullYear();
                return year + '-' + (month < 10 ? '0' + month : month) + '-' + (day < 10 ? '0' + day : day);
            }

            function formatDateForDisplay(date) {
                let day = date.getDate();
                let month = date.getMonth() + 1;
                let year = date.getFullYear();
                return (day < 10 ? '0' + day : day) + '-' + (month < 10 ? '0' + month : month) + '-' + year;
            }

            function updateDateDisplay(date) {
                currentDateElement.textContent = formatDateForDisplay(date);

                document.querySelector('input[name="date"]').value = formatDateToInput(date);
                document.querySelector('input[name="daterange"][value="custom"]').checked = true;
                document.getElementById('custom-date-container').style.display = 'block';
                applyFilter();
            }

            previousButton.addEventListener('click', function(e) {
                e.preventDefault();
                const currentDate = new Date(currentDateElement.textContent.split('-').reverse().join('-'));
                currentDate.setDate(currentDate.getDate() - 1);

                updateDateDisplay(currentDate);
            });

            nextButton.addEventListener('click', function(e) {
                e.preventDefault();
                const currentDate = new Date(currentDateElement.textContent.split('-').reverse().join('-'));
                currentDate.setDate(currentDate.getDate() + 1);

                updateDateDisplay(currentDate);
            });

        });

        function applyFilter() {
            $.ajax({
                headers: {
                    'X-CSRF-Token': $('meta[name=_token]').attr('content')
                },
                url: "{{ url('crm/notes/render-diaries') }}",
                type: 'POST',
                cache: false,
                data: prepaeData(),
                datatype: 'html',
                success: function(data) {
                    $('#render-diaries').html(data.data);
                },
                error: function(xhr, status, error) {
                    console.error("Error during AJAX request:", error);
                }
            });
        }

        function prepaeData() {
            var objective = $("input[name='objective']:checked").val();
            var daterange = $("input[name='daterange']:checked").val();
            var date = $("input[name='date']").val();
            var customer_id = $("select[name='customer_id']").val();

            var dataToSend = {
                objective: objective,
                daterange: daterange,
                date: date,
                customer_id: customer_id
            };

            return dataToSend;
        }
    </script>
@endsection
