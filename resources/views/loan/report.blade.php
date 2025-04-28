@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">

            <div class="content-body">

                <div id="message-area">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <section id="basic-datatable">
                    <div class="card border  overflow-hidden">
                        <div class="row">
                            <div class="col-md-12 bg-light border-bottom mb-1 po-reportfileterBox">
                                <div class="row pofilterhead action-button align-items-center">
                                    <div class="col-md-4">
                                        <h3>Loan Report</h3>
                                        <p>Apply the Basic Filter</p>
                                    </div>
                                    <div
                                        class="col-md-8 text-sm-end pofilterboxcenter mb-0 d-flex flex-wrap align-items-center justify-content-sm-end">
                                        {{-- <div class="customernewsection-form">
                                            <div class="demo-inline-spacing">
                                                <div class="form-check form-check-primary mt-0">
                                                    <input type="radio" id="goods" name="goodsservice"
                                                        class="form-check-input" checked="">
                                                    <label class="form-check-label fw-bolder" for="goods">Goods</label>
                                                </div>
                                                <div class="form-check form-check-primary mt-0">
                                                    <input type="radio" id="service" name="goodsservice"
                                                        class="form-check-input">
                                                    <label class="form-check-label fw-bolder" for="service">Service</label>
                                                </div>
                                            </div>
                                        </div> --}}
                                        <div class="btn-group new-btn-group my-1 my-sm-0 ps-0">
                                            <input type="radio" class="btn-check" name="Period" id="CurrentMonth"
                                                value="this-month" />
                                            <label class="btn btn-outline-primary mb-0" for="CurrentMonth">Current
                                                Month</label>

                                            <input type="radio" class="btn-check" name="Period" id="LastMonth"
                                                value="last-month" />
                                            <label class="btn btn-outline-primary mb-0" for="LastMonth">Last Month</label>

                                            <input type="radio"
                                                class="btn-check form-control flatpickr-range flatpickr-input"
                                                name="Period" id="Custom" />
                                            <label class="btn btn-outline-primary mb-0" for="Custom">Custom</label>
                                        </div>
                                        <button data-bs-toggle="modal" data-bs-target="#addcoulmn"
                                            class="btn btn-primary btn-sm mb-0 waves-effect"><i data-feather="filter"></i>
                                            Advance Filter
                                        </button>
                                    </div>
                                </div>

                                <div class="customernewsection-form poreportlistview p-1">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <div class="mb-1 mb-sm-0">
                                                <label class="form-label">Loan Type</label>
                                                <select class="form-select" id="f-loan-type">
                                                    <option value="">Select</option>
                                                    <option value="1">Home Loan</option>
                                                    <option value="2">Vehicle Loan</option>
                                                    <option value="3">Term Loan</option>

                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1 mb-sm-0">
                                                <label class="form-label">Select Customer</label>
                                                <select class="form-select select2" id="f-customer">
                                                    <option value="">Select</option>
                                                    @foreach ($loans as $customer)
                                                        <option value="{{ $customer->id }}">{{ $customer->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-1 mb-sm-0">
                                                <label class="form-label">Status</label>
                                                <select class="form-select" id="f-status">
                                                    <option value="" selected>Select</option>
                                                    <option value="completed">Completed</option>
                                                    <option value="Disbursed">Disbursed</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12" style="min-height: 300px">
                                <div class="table-responsive trailbalnewdesfinance po-reportnewdesign my-class">
                                    <table class="my-table datatables-basic table myrequesttablecbox">
                                        <thead>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
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

    <div class="modal fade text-start filterpopuplabel " id="addcoulmn" tabindex="-1" aria-labelledby="myModalLabel17"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Advance
                            Filter</h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="step-custhomapp bg-light">
                        <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#Employee" role="tab"><i
                                        data-feather="columns"></i> Columns</a>
                            </li>
                            {{-- <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#Bank" role="tab"><i
                                        data-feather="bar-chart"></i> More Filter</a>
                            </li> --}}

                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#Location" role="tab"><i
                                        data-feather="calendar"></i> Scheduler</a>
                            </li>

                        </ul>
                    </div>

                    <div class="tab-content tablecomponentreport">
                        <div class="tab-pane active" id="Employee">
                            <div class="compoenentboxreport">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-check form-check-primary">
                                            <input type="checkbox" class="form-check-input" id="selectAll"
                                                checked="">
                                            <label class="form-check-label" for="selectAll">Select All Columns</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row sortable">
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="loan-id"
                                                checked="">
                                            <label class="form-check-label" for="loan-id">Loan Id</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="loan-type"
                                                checked="">
                                            <label class="form-check-label" for="loan-type">Loan Type</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="customer"
                                                checked="">
                                            <label class="form-check-label" for="customer">Customer</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="loan-amount"
                                                checked="">
                                            <label class="form-check-label" for="loan-amount">Loan Amount</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="recommended-amount"
                                                checked="">
                                            <label class="form-check-label" for="recommended-amount">Recovered Amount</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="interest-rate"
                                                checked="">
                                            <label class="form-check-label" for="interest-rate">Interest Received</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="tenure"
                                                checked="">
                                            <label class="form-check-label" for="tenure">Tenure</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="total-dis"
                                                checked="">
                                            <label class="form-check-label" for="total_dis">Total Disbursement</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="total-settle"
                                                checked="">
                                            <label class="form-check-label" for="total_settle">Total Settled</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="out-standing-balance"
                                                checked="">
                                            <label class="form-check-label" for="out-standing-balance">Out Standing
                                                Balance</label>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="loan-status"
                                                checked="">
                                            <label class="form-check-label" for="loan-status">Loan Status</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- <div class="tab-pane" id="Bank">
                            <div class="compoenentboxreport advanced-filterpopup customernewsection-form">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-check ps-0">
                                            <label class="form-check-label">Add Filter</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Select Category</label>
                                        <select class="form-select select2" name="m_category">
                                            <option value="">Select</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Select Sub-Category</label>
                                        <select class="form-select select2" name="m_sub_category">
                                            <option value="">Select</option>
                                            @foreach ($sub_categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Select Attribute</label>
                                        <select class="form-select select2" name="m_attribute">
                                            <option value="">Select</option>
                                            @foreach ($attribute_groups as $attribute_group)
                                                <option value="{{ $attribute_group->id }}">{{ $attribute_group->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Select Attribute Value</label>
                                        <select class="form-select select2" name="m_attribute_value">
                                            <option value="">Select</option>
                                            @foreach ($attributes as $attribute)
                                                <option value="{{ $attribute->id }}">{{ $attribute->value }}</option>
                                            @endforeach
                                        </select>
                                    </div>


                                </div>

                            </div>
                        </div> --}}
                        <div class="tab-pane" id="Location">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="compoenentboxreport advanced-filterpopup customernewsection-form mb-1">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-check ps-0">
                                                    <label class="form-check-label">Add Scheduler</label>
                                                </div>
                                            </div>
                                        </div>
                                        @php
                                            $firstScheduler = $loanReportSchedulers->first();
                                        @endphp
                                        <div class="row camparboxnewcen">
                                            <div class="col-md-8">
                                                <label class="form-label">To</label>
                                                <div class="select2-wrapper" name="to-wrapper">
                                                    <select class="form-select select2" name="to" multiple>
                                                        <option value="" disabled>Select</option>

                                                            @foreach ($users as $user)
                                                                <option value="{{ $user->id }}"
                                                                    data-type="App\Models\User" {{-- @if (isset($loanReportSchedulers['App\Models\User']) && $loanReportSchedulers['App\Models\User'] == $user->id) selected="selected" @endif --}}
                                                                    @if (
                                                                        $loanReportSchedulers->contains(function ($scheduler) use ($user) {
                                                                            return $scheduler->toable_type == 'App\Models\User' && $scheduler->toable_id == $user->id;
                                                                        })) selected="selected" @endif>
                                                                    {{ $user->name }}
                                                                </option>
                                                            @endforeach
                                                            @foreach ($employees as $employee)
                                                                <option value="{{ $employee->id }}"
                                                                    data-type="App\Models\Employee" {{-- @if (isset($loanReportSchedulers['App\Models\Employee']) && $loanReportSchedulers['App\Models\Employee'] == $employee->id) selected="selected" @endif --}}
                                                                    @if (
                                                                        $loanReportSchedulers->contains(function ($scheduler) use ($employee) {
                                                                            return $scheduler->toable_type == 'App\Models\Employee' && $scheduler->toable_id == $employee->id;
                                                                        })) selected="selected" @endif>
                                                                    {{ $employee->name }}
                                                                </option>
                                                            @endforeach

                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row camparboxnewcen">
                                            <div class="col-md-4">
                                                <label class="form-label">Type</label>
                                                <select class="form-select" name="type">
                                                    <option value="">Select</option>
                                                    <option value="daily"
                                                        @if (
                                                            $loanReportSchedulers->contains(function ($scheduler) use ($employee) {
                                                                return $scheduler->type == 'daily';
                                                            })) selected="selected" @endif>Daily
                                                    </option>
                                                    <option value="weekly"
                                                        @if (
                                                            $loanReportSchedulers->contains(function ($scheduler) use ($employee) {
                                                                return $scheduler->type == 'weekly';
                                                            })) selected="selected" @endif>Weekly
                                                    </option>
                                                    <option value="monthly"
                                                        @if (
                                                            $loanReportSchedulers->contains(function ($scheduler) use ($employee) {
                                                                return $scheduler->type == 'monthly';
                                                            })) selected="selected" @endif>Monthly
                                                    </option>
                                                </select>
                                            </div>

                                            @php
                                                $scheduler = $loanReportSchedulers->first();
                                                if ($scheduler) {
                                                    $schedulerData = [
                                                        'date' => \Carbon\Carbon::parse($scheduler->date)->format(
                                                            'Y-m-d\TH:i',
                                                        ),
                                                        'remarks' => $scheduler->remarks,
                                                    ];
                                                    //dd($schedulerData);
                                                } else {
                                                    $schedulerData = [
                                                        'date' => '',
                                                        'remarks' => '',
                                                    ];
                                                }
                                            @endphp

                                            <div class="col-md-4">
                                                <label class="form-label">Select Date</label>
                                                <input type="datetime-local" class="form-select" name="date"
                                                    id="dateInput" value="{{ $schedulerData['date'] }}" />
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label">Remarks</label>
                                                <textarea class="form-control" placeholder="Enter Remarks" name="remarks">{{ $schedulerData['remarks'] }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer ">
                    <button type="reset" class="btn btn-outline-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary data-submit mr-1" id="applyBtn">Apply</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <!-- BEGIN: Dashboard Custom Code JS-->
    <script src="{{ asset('assets/js/custom/pages/loan/loan-report.js') }}"></script>
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- END: Dashboard Custom Code JS-->

    <script>
        window.routes = {
            loanReport: @json(route('loan.report.filter')),
            addScheduler: @json(route('loan.add.scheduler')),
            reportSendMail: @json(route('loan.send.report')),
            loanViewAllDetail: @json(route('loan.view_all_detail', ['id' => ':id'])),
            loanViewVehicleDetail: @json(route('loan.view_vehicle_detail', ['id' => ':id'])),
            loanViewTermDetail: @json(route('loan.view_term_detail', ['id' => ':id'])),
        };


        $(function() {
            $(".sortable").sortable();
        });

        document.addEventListener('DOMContentLoaded', function() {
            feather.replace();
            let filterData = {};

            // Helper function to call fetch if there are changes
            function updateFilterAndFetch() {
                if (Object.keys(filterData).length > 0) {
                    fetchLoanReports(filterData);
                }
            }

            document.querySelectorAll('input[name="Period"]').forEach((radio) => {
                radio.addEventListener('change', function(event) {
                    // Handle the selection
                    if (this.id === 'Custom') {
                        // Get the date range value
                        let dateRange = document.getElementById('Custom').value;

                        if (dateRange) {
                            // Split the date range into start and end dates
                            let dates = dateRange.split(' to ');
                            if (dates.length == 2) {
                                filterData.startDate = dates[0];
                                filterData.endDate = dates[1];
                            }
                        }
                        // Ensure period is not set when using custom date range
                        delete filterData.period;
                        updateFilterAndFetch();
                    } else {
                        filterData.period = event.target.value;
                        // Clear custom date range if a preset period is selected
                        delete filterData.startDate;
                        delete filterData.endDate;

                        updateFilterAndFetch();
                    }
                });
            });

            // document.querySelectorAll('input[name="goodsservice"]').forEach((radio) => {
            //     radio.addEventListener('change', function() {
            //         let filterData = {};
            //         if (this.id === 'service') {
            //             filterData.type = 'service';
            //         } else if (this.id === 'goods') {
            //             filterData.type = 'goods';
            //         }

            //         updateFilterAndFetch();
            //     });
            // });

            $('#f-loan-type').on('change', function() {
                const loanType = $(this).val();
                filterData.loanType = loanType; // Set null if no value selected
                updateFilterAndFetch();
            });

            $('#f-customer').on('change', function() {
                const customer = $(this).val();
                filterData.customer = customer;
                updateFilterAndFetch();
            });

            $('#f-status').on('change', function() {
                const statusValue = $(this).val();
                filterData.status = statusValue;
                updateFilterAndFetch();
            });

            function getSelectedData() {
                let selectedData = [];

                $('select[name="to"] option:selected').each(function() {
                    selectedData.push({
                        id: $(this).val(),
                        type: $(this).data('type')
                    });
                });

                return selectedData;
            }

            // Trigger column order save when Apply button is clicked
            $('#applyBtn').on('click', function(e) {
                const columnOrder = getColumnVisibilitySettings();
                filterData.columnOrder = columnOrder;

                // Close the modal
                var filterModal = bootstrap.Modal.getInstance(document.getElementById('addcoulmn'));

                // Optionally handle the response here
                e.preventDefault();

                // Get the date value
                const dateValue = $('input[name="date"]').val();
                const today = new Date().toISOString().split('T')[0];

                let selectedData = getSelectedData();
                // Gather form data
                var formData = {
                    to: selectedData,
                    type: $('select[name="type"]').val(),
                    date: $('input[name="date"]').val(),
                    remarks: $('textarea[name="remarks"]').val(),
                    m_category: $('select[name="m_category"]').val(),
                    m_subCategory: $('select[name="m_sub_category"]').val(),
                    m_attribute: $('select[name="m_attribute"]').val(),
                    m_attributeValue: $('select[name="m_attribute_value"]').val()
                };

                filterData.m_category = formData.m_category;
                filterData.m_subCategory = formData.m_subCategory;
                filterData.m_attribute = formData.m_attribute;
                filterData.m_attributeValue = formData.m_attributeValue;

                // Call updateFilterAndFetch once
                updateFilterAndFetch();

                if (formData.to && formData.to.length > 0 || formData.type || formData.date) {
                    if (dateValue < today) {
                        var inputField = $('[name="date"]');

                        // For normal inputs, remove previous error and append new one
                        inputField.removeClass('is-invalid').addClass(
                            'is-invalid');
                        inputField.next('.invalid-feedback')
                            .remove(); // Remove any previous error
                        inputField.after(
                            '<div class="invalid-feedback">Please select a future date.</div>');
                        return; // Stop form submission
                    }

                    // AJAX request
                    const fields = ['to', 'type', 'date']
                    fields.forEach(field => {
                        var inputField = $('[name="' + field + '"]');

                        if (inputField.hasClass('select2')) {
                            // Remove any previous error messages
                            inputField.closest('.select2-wrapper').find(
                                '.invalid-feedback').remove();
                        } else {
                            inputField.removeClass('is-invalid')
                        }
                    })
                    $.ajax({
                        url: window.routes.addScheduler,
                        method: 'POST',
                        data: formData,
                        success: function(response) {
                            // Show success message
                            const Toast = Swal.mixin({
                                toast: true,
                                position: "top-end",
                                showConfirmButton: false,
                                timer: 3000,
                                timerProgressBar: true,
                                didOpen: (toast) => {
                                    toast.onmouseenter = Swal.stopTimer;
                                    toast.onmouseleave = Swal.resumeTimer;
                                }
                            });
                            Toast.fire({
                                icon: "success",
                                title: response.success
                            });

                            // Optionally reset the form
                            // $('select[name="to"]').val([]).trigger('change');
                            // $('select[name="type"]').val(null).trigger('change');
                            // $('input[name="date"]').val('');
                            // $('textarea[name="remarks"]').val('');

                            if (filterModal) {
                                filterModal.hide();
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                var errors = xhr.responseJSON.errors;

                                // Handle and display validation errors
                                for (var field in errors) {
                                    if (errors.hasOwnProperty(field)) {
                                        var errorMessages = errors[field];

                                        // Find the input field
                                        var inputField = $('[name="' + field + '"]');

                                        // If the field has the select2 class
                                        if (inputField.hasClass('select2')) {
                                            // Remove any previous error messages
                                            inputField.closest('.select2-wrapper').find(
                                                '.invalid-feedback').remove();

                                            // Append the error message after the select2 container
                                            inputField.closest('.select2-wrapper').append(
                                                '<div class="invalid-feedback d-block">' +
                                                errorMessages.join(', ') + '</div>');

                                            // Add is-invalid class to highlight the error
                                            inputField.next('.select2-container').addClass(
                                                'is-invalid');
                                        } else {
                                            // For normal inputs, remove previous error and append new one
                                            inputField.removeClass('is-invalid').addClass(
                                                'is-invalid');
                                            inputField.next('.invalid-feedback')
                                                .remove(); // Remove any previous error
                                            inputField.after(
                                                '<div class="invalid-feedback">' +
                                                errorMessages.join(', ') + '</div>');
                                        }
                                    }
                                }
                            }


                        }
                    });
                } else {
                    if (filterModal) {
                        filterModal.hide();
                    }
                }
            });
        });
    </script>
@endsection
