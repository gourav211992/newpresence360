@extends('layouts.app')

@section('styles')
    <style type="text/css">
        .error-message {
            color: red;
            font-size: 12px;
            margin-bottom: 10px;
            display: none;
        }
    </style>
@endsection

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">New Land on Lease</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">Add New</li>


                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6  mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="arrow-left-circle"></i> Back</button>
                            <button form="lease-form" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="check-circle"></i> Submit</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

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

                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">

                            <div class="card">
                                <div class="card-body customernewsection-form">


                                    <div class="border-bottom mb-2 pb-25">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="newheader ">
                                                    <h4 class="card-title text-theme">Basic Information</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                            </div>


                                        </div>

                                    </div>


                                    <form action="{{ route('update.lease') }}" id="lease-form" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $data->id }}">
                                        <div class="row">
                                            <!-- Series Field -->
                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Series <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <select class="form-select" name="series" id="series" required>
                                                            <option value="" disabled selected>Select</option>
                                                            @foreach ($series as $key => $serie)
                                                                <option value="{{ $serie->id }}"
                                                                    @if ($serie->id == $data->series) selected @endif>
                                                                    {{ $serie->book_name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('series')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Lease No. Field -->
                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Document No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="text" class="form-control" id="leaseno"
                                                            name="lease_no" required value="{{ $data->lease_no }}"
                                                            onchange="cleanInput(this)">
                                                        @error('lease_no')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label"></label>
                                                    </div>
                                                    <div class="col-md-12 action-button mt-50">
                                                        <button data-bs-toggle="modal" type="button"
                                                            data-bs-target="#rescdule"
                                                            class="btn btn-outline-primary btn-sm">
                                                            <i data-feather="plus-square"></i>
                                                            Land Details
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- Land No. Field -->
                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Land No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <select class="form-select select2" id="land-no" name="land_no"
                                                            required>
                                                            <option value="" disabled>Select</option>
                                                            <option value="{{ $data->land_no }}" selected>
                                                                {{ $data->land->land_no }}</option>
                                                            @foreach ($lands as $key => $land)
                                                                <option value="{{ $land->id }}">{{ $land->land_no }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('land_no')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Khasara No. Field -->
                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Khasara No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="text" id="khasara-no" name="khasara_no"
                                                            class="form-control" readonly required
                                                            value="{{ $data->khasara_no }}">
                                                        @error('khasara_no')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Area in Sq ft Field -->
                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Area in Sq ft <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="text" id="area-sqft" name="area_sqft"
                                                            class="form-control" readonly required
                                                            value="{{ $data->area_sqft }}">
                                                        @error('area_sqft')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- Plot Details Field -->
                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Plot Details <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="text" id="plot-details" name="plot_details"
                                                            class="form-control" readonly required
                                                            value="{{ $data->plot_details }}">
                                                        @error('plot_details')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Pincode Field -->
                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Pincode <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="text" id="pincode" name="pincode"
                                                            class="form-control" readonly required
                                                            value="{{ $data->pincode }}">
                                                        @error('pincode')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Cost Field -->
                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Cost</label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="text" id="cost" name="cost"
                                                            class="form-control" required readonly
                                                            value="{{ $data->cost }}"
                                                            onchange="cleanInputNumber(this)">
                                                        @error('cost')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- Customer Field -->
                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Customer <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <select class="form-select select2" name="customer" required>
                                                            <option value="" disabled selected>Select</option>
                                                            @foreach ($customers as $key => $cus)
                                                                <option value="{{ $cus->id }}"
                                                                    @if ($data->customer == $cus->id) selected @endif>
                                                                    {{ $cus->company_name }}</option>
                                                            @endforeach
                                                            <!-- Add more options as needed -->
                                                        </select>
                                                        @error('customer')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Lease Time in yrs. Field -->
                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Lease Time in yrs. <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="text" name="lease_time" class="form-control"
                                                            required id="lease_time" value="{{ $data->lease_time }}"
                                                            onchange="cleanInputNumber(this)"
                                                            oninput="calculateInstallmentCost()" min="1"
                                                            max="99" maxlength="2">
                                                        @error('lease_time')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Lease Cost Field -->
                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Lease Cost <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="text" name="lease_cost" id="lease_cost"
                                                            class="form-control" required value="{{ $data->lease_cost }}"
                                                            onchange="cleanInputNumber(this)"
                                                            oninput="calculateInstallmentCost()">
                                                        @error('lease_cost')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Period Type <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <select class="form-select" name="period_type" id="period_type"
                                                            required onchange="calculateInstallmentCost()">
                                                            <option value="" disabled selected>Select</option>
                                                            <option value="Monthly"
                                                                @if ($data->period_type == 'Monthly') selected @endif>Monthly
                                                            </option>
                                                            <option value="Quarterly"
                                                                @if ($data->period_type == 'Quarterly') selected @endif>Quarterly
                                                            </option>
                                                            <option value="Yearly"
                                                                @if ($data->period_type == 'Yearly') selected @endif>Yearly
                                                            </option>
                                                        </select>
                                                        @error('period_type')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Repayment Period Field -->
                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Repayment Period <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="number" name="repayment_period"
                                                            id="repayment_period" class="form-control" readonly required
                                                            value="{{ $data->repayment_period }}"
                                                            onchange="cleanInputNumber(this)">
                                                        <div id="repayment-error" class="error-message">Repayment period
                                                            cannot exceed the maximum allowed based on lease duration.</div>

                                                        @error('repayment_period')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Installment Cost Field -->
                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Installment Cost <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="text" name="installment_cost"
                                                            id="installment_cost" value="{{ $data->installment_cost }}"
                                                            onchange="cleanInputNumber(this)" readonly
                                                            class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <!-- Upload Document Field -->
                                            <div class="col-md-3">
                                                <div class="row mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Agrrement No<span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="text" name="agreement_no" class="form-control"
                                                            maxlength="250" required value="{{ $data->agreement_no }}">
                                                        @error('agreement_no')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Remarks Field -->
                                            <div class="col-md-3">
                                                <div class="row mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Agreement Date<span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="date" name="date_of_agreement"
                                                            value="{{ $data->date_of_agreement }}" required
                                                            class="form-control">
                                                        @error('remarks')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <!-- Upload Document Field -->
                                            <div class="col-md-3">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Upload Document</label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="file" name="document" id="document"
                                                            class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                                        @error('document')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Remarks Field -->
                                            <div class="col-md-6">
                                                <div class="row mb-1">
                                                    <div class="col-md-12">
                                                        <label class="form-label">Remarks</label>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <input type="text" name="remarks" class="form-control"
                                                            maxlength="250" value="{{ $data->remarks }}">
                                                        @error('remarks')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal to add new record -->

                </section>


            </div>
        </div>
    </div>
    <!-- END: Content-->

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>

    <div class="modal fade text-start" id="rescdule" tabindex="-1" aria-labelledby="myModalLabel17"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select
                            Pending Disbursal</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Land No</label>
                                <select class="form-select" name="filter_land_no" id="filter_land_no">
                                    <option value="">Select</option>
                                    @if (isset($lands))
                                        @foreach ($lands as $key => $val)
                                            <option value="{{ $val->land_no }}">{{ $val->land_no }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Customer Name</label>
                                <select class="form-select select2" name="filter_customer_name"
                                    id="filter_customer_name">
                                    <option value="">Select</option>
                                    @if (isset($customers))
                                        @foreach ($customers as $key => $val)
                                            <option value="{{ $val->name }}">{{ $val->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Plot Number</label>
                                <select class="form-select select2" name="filter_plot_no" id="filter_plot_no">
                                    <option value="">Select</option>
                                    @if (isset($lands))
                                        @foreach ($lands as $key => $val)
                                            <option value="{{ $val->plot_no }}">{{ $val->plot_no }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Khasara Number</label>
                                <select class="form-select select2" name="filter_khasara_no" id="filter_khasara_no">
                                    <option value="">Select</option>
                                    @if (isset($lands))
                                        @foreach ($lands as $key => $val)
                                            <option value="{{ $val->khasara_no }}">{{ $val->khasara_no }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Land No</th>
                                            <th>Customer</th>
                                            <th>Plot No</th>
                                            <th>Khasra Number</th>
                                            <th>Area (sq ft)</th>
                                            <th>Land Cost</th>
                                            <th>Ploat Details</th>
                                        </tr>
                                    </thead>
                                    <tbody id="pending_schedule"></tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                            data-feather="x-circle"></i> Cancel</button>
                    <button class="btn btn-primary btn-sm" data-bs-dismiss="modal" id="process_disbursal"><i
                            data-feather="check-circle"></i> Process</button>
                </div>
            </div>
        </div>
    </div>
@section('scripts')
    <script>
        window.routes = {
            landOnleaseAddFilter: @json(route('land.onleaseadd.filter-land')),
        };

        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })

        $(document).ready(function() {
            $('#series').on('change', function() {
                var book_id = $(this).val();
                var request = $('#leaseno');

                request.val(''); // Clear any existing options

                if (book_id) {
                    $.ajax({
                        url: "{{ url('get-land-request') }}/" + book_id,
                        type: "GET",
                        dataType: "json",
                        success: function(data) {
                            console.log(data);
                            if (data.requestno) {
                                request.val(data.requestno);
                            }
                        }
                    });
                }
            });
            $('#land-no').on('change', function() {
                var landId = $(this).val();

                if (landId) {
                    $.ajax({
                        url: "{{ url('/get-land-details') }}" + '/' + landId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#khasara-no').val(data.khasara_no);
                            $('#area-sqft').val(data.area_sqft);
                            $('#plot-details').val(data.plot_details);
                            $('#pincode').val(data.pincode);
                            $('#cost').val(data.cost);
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX Error:', status, error);
                        }
                    });
                }
            });
        });

        document.getElementById('document').addEventListener('change', function(event) {
            const file = event.target.files[0];

            if (file) {
                const allowedTypes = ['application/pdf', 'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg',
                    'image/png'
                ];
                const maxSize = 2 * 1024 * 1024; // 2 MB in bytes

                if (!allowedTypes.includes(file.type)) {
                    errorMessage = 'Invalid file type. Only PDF, DOC, DOCX, JPG, JPEG, PNG are allowed.';
                } else if (file.size > maxSize) {
                    errorMessage = 'File size exceeds the 2 MB limit.';
                }

                if (errorMessage) {
                    alert(errorMessage);

                } else {}
            } else {}
        });
    </script>



    <script>
        document.getElementById('lease_time').addEventListener('input', function() {
            if (this.value.length > 2) {
                this.value = this.value.slice(0, 2); // Limits input to 2 digits
            }
        });

        function cleanInput(input) {
            // Remove negative numbers and special characters
            input.value = input.value.replace(/[^a-zA-Z0-9 ]/g, '');
        }

        function cleanInputNumber(input) {
            // Remove negative numbers and special characters
            input.value = input.value.replace(/[^0-9 ]/g, '');
        }

        function validateRepaymentPeriod() {
            var leaseTime = document.getElementById('lease_time').value;
            var periodType = document.getElementById('period_type').value;
            var repaymentPeriod = document.getElementById('repayment_period').value;
            var maxRepaymentPeriod;

            if (periodType === 'Monthly') {
                maxRepaymentPeriod = leaseTime * 12;
            } else if (periodType === 'Quarterly') {
                maxRepaymentPeriod = leaseTime * 4;
            } else if (periodType === 'Yearly') {
                maxRepaymentPeriod = leaseTime;
            }

            if (repaymentPeriod > maxRepaymentPeriod) {
                document.getElementById('repayment-error').style.display = 'block';
                document.getElementById('repayment_period').value = maxRepaymentPeriod;
            } else {
                document.getElementById('repayment-error').style.display = 'none';
            }

            calculateInstallmentCost();
        }

        function calculateInstallmentCost() {
            // Get the values from the form inputs
            var leaseTime = document.getElementById('lease_time').value;
            var leaseCost = document.getElementById('lease_cost').value;
            var periodType = document.getElementById('period_type').value;
            var repaymentPeriod = document.getElementById('repayment_period').value;

            if (leaseTime && leaseCost && periodType) {
                var totalInstallments;

                // Determine the total number of installments based on the period type
                if (periodType === 'Monthly') {
                    document.getElementById('repayment_period').value = leaseTime * 12;
                    totalInstallments = leaseTime * 12;
                } else if (periodType === 'Quarterly') {
                    document.getElementById('repayment_period').value = leaseTime * 4;
                    totalInstallments = leaseTime * 4;
                } else if (periodType === 'Yearly') {
                    document.getElementById('repayment_period').value = leaseTime * 1;
                    totalInstallments = leaseTime * 1;
                }

                // Calculate the installment cost
                var installmentCost = leaseCost / totalInstallments;

                // Display the calculated installment cost
                document.getElementById('installment_cost').value = installmentCost.toFixed(2);
            } else {
                document.getElementById('installment_cost').value = '';
            }
        }
    </script>

    <script>
        let filterData = {};
        // Pending Disbursal
        function setupFilters() {

            const filters = [{
                    selector: $("#filter_land_no"),
                    key: "landNo",
                    type: "select"
                }, // select field
                {
                    selector: $("#filter_customer_name"),
                    key: "customerName",
                    type: "input",
                }, // input field
                {
                    selector: $("#filter_plot_no"),
                    key: "plotNo",
                    type: "select"
                }, // select field
                {
                    selector: $("#filter_khasara_no"),
                    key: "khasaraNo",
                    type: "select"
                }, // select field
            ];

            filters.forEach(({
                selector,
                key,
                type
            }) => {
                // Attach 'change' event for select elements and 'input' event for input elements
                const eventType = type === "select" ? "change" : "input";

                $(selector).on("change", () => {
                    filterData[key] = $(selector).val();
                    updateFilterAndFetch();
                });
            });

        }

        function updateFilterAndFetch() {
            if (Object.keys(filterData).length > 0) {
                fetchPurchaseOrders(filterData);
            }
        }

        async function fetchPurchaseOrders(filterData = {}) {
            try {
                const ROUTES = window.routes.landOnleaseAddFilter;
                const params = new URLSearchParams(filterData);
                const url = `${ROUTES}?${params}`;
                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }

                const data = await response.json();

                const tbody = document.getElementById('pending_schedule'); // Get the table body
                tbody.innerHTML = '';

                updateTable(tbody, data.land_filter_list);
            } catch (error) {
                console.error("Error fetching purchase orders:", error);
            }
        }

        function updateTable(tbody, lands) {
            if (!Array.isArray(lands)) {
                console.error("Land Data is not an array");
                return;
            }

            lands.forEach((data, index) => {
                if (typeof data === "object" && data !== null) {
                    const row = createTableRow(data, index);
                    tbody.appendChild(row);
                } else {
                    console.error(`Invalid data data at index ${index}:`, data);
                }
            });
        }

        function createTableRow(data, index) {
            const row = document.createElement("tr");

            // Create checkbox cell
            const checkboxCell = document.createElement("td");
            const checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.dataset.rowId = index; // Store the index as data attribute
            checkbox.addEventListener('change', updateProcessButton);
            checkboxCell.appendChild(checkbox);
            checkboxCell.appendChild(checkbox);

            const hiddenInputs = document.createElement("div");
            hiddenInputs.style.display = "none";

            // Add hidden inputs for any additional data you need to store
            const hiddenData = {
                id: data.id ?? "", // Example hidden value
                area: data.area ?? "", // Example hidden value
                created_at: data.created_at ?? "", // Example hidden value
                // Add any other hidden values you need
            };

            // Create hidden input elements
            Object.entries(hiddenData).forEach(([key, value]) => {
                const hiddenInput = document.createElement("input");
                hiddenInput.type = "hidden";
                hiddenInput.name = `hidden_${key}`;
                hiddenInput.value = value;
                hiddenInputs.appendChild(hiddenInput);
            });

            checkboxCell.appendChild(hiddenInputs);

            const cellsData = [
                data.land_no ?? "N/A",
                data.customer ?? "N/A",
                data.plot_no ?? "N/A",
                data.khasara_no ?? "N/A",
                data.area ?? "N/A",
                data.cost ?? "N/A",
                data.address ?? "N/A",
            ];

            // Add checkbox cell first
            row.appendChild(checkboxCell);

            // Add other cells
            cellsData.forEach(cellValue => {
                const cell = document.createElement("td");
                cell.textContent = cellValue;
                row.appendChild(cell);
            });

            return row;
        }

        // Function to handle process_disbursal button click
        function handleProcessDisbursal() {
            const checkedRows = document.querySelectorAll('input[type="checkbox"]:checked');
            const selectedData = [];

            checkedRows.forEach(checkbox => {
                const row = checkbox.closest('tr');
                const hiddenInputs = row.querySelector('div').querySelectorAll('input[type="hidden"]');

                // Collect visible data
                const visibleData = {
                    land_no: row.cells[1].textContent,
                    customer: row.cells[2].textContent,
                    plot_no: row.cells[3].textContent,
                    khasara_no: row.cells[4].textContent
                };

                // Collect hidden data
                const hiddenData = {};
                hiddenInputs.forEach(input => {
                    const key = input.name.replace('hidden_', '');
                    hiddenData[key] = input.value;
                });

                // Combine visible and hidden data
                selectedData.push({
                    ...visibleData,
                    ...hiddenData
                });
            });

            return selectedData; // You can process this data further as needed
        }

        // Example usage
        document.getElementById('process_disbursal').addEventListener('click', () => {
            const selectedRowsData = handleProcessDisbursal();

            $("#land-no").val(selectedRowsData[0].id).trigger('change');
            // $("#khasara_no").val(selectedRowsData.land_no);
            // $("#khasara_no").val(selectedRowsData.land_no);
        });

        function updateProcessButton() {
            const processButton = document.getElementById('process_disbursal');
            const checkedBoxes = document.querySelectorAll('input[type="checkbox"]:checked');

            // Enable button if at least one checkbox is checked, disable otherwise
            processButton.disabled = checkedBoxes.length === 0;
        }

        document.addEventListener("DOMContentLoaded", function() {
            setupFilters();

            const processButton = document.getElementById('process_disbursal');

            // Initially disable the button
            processButton.disabled = true;

            processButton.addEventListener('click', () => {
                const selectedRowsData = handleProcessDisbursal();
            });
        });
    </script>
@endsection
@endsection
