@extends('layouts.app')
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <form class="form" role="post-data" method="POST"
                action="{{ route('recruitment.internal-jobs.store-referrals', ['jobId' => $jobId]) }}"
                redirect="{{ route('recruitment.internal-jobs') }}" autocomplete="off">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6  mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">New Candidate</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a
                                                    href="{{ route('recruitment.hr-dashboard') }}">Home</a></li>
                                            <li class="breadcrumb-item active">Add New</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <a href="{{ route('recruitment.internal-jobs') }}"
                                class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i>
                                Back</a>
                            <button class="btn btn-primary btn-sm mb-50 mb-sm-0" data-request="ajax-submit"
                                data-target="[role=post-data]"><i data-feather="check-circle"></i> Create</button>
                        </div>
                    </div>
                </div>
                <div class="content-body">
                    <section id="basic-datatable">
                        <div class="row">
                            <div class="col-12">

                                <div class="card">
                                    <div class="card-body travelexp-form">

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="newheader  border-bottom mb-2 pb-25">
                                                    <h4 class="card-title text-theme">Apply for the Job</h4>
                                                    <p class="card-text">Enter the details below.</p>
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="row align-items-center mb-2">
                                                    <div class="col-md-3">
                                                        <label class="form-label"><strong>Applied for<span
                                                                    class="text-danger"> *</span></strong></label>
                                                    </div>

                                                    <div class="col-md-5  customernewsection-form">
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="customColorRadio3"
                                                                    name="applied_for"
                                                                    value="{{ App\Helpers\CommonHelper::SELF }}"
                                                                    class="form-check-input" checked="">
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio3">Self</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="customColorRadio4"
                                                                    name="applied_for"
                                                                    value="{{ App\Helpers\CommonHelper::REFER }}"
                                                                    class="form-check-input">
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio4">Refer</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1 candidate-wrapper">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Candidate Name <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="name" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1 refered-by-wrapper"
                                                    style="display: none">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Candidate Name</label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select select2" id="refered_by"
                                                            name="candidate_id">
                                                            <option value="">Select</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Email-ID</label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="email"
                                                            readonly />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Mobile No. <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="mobile_no"
                                                            readonly />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Upload Resume</label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="file" class="form-control" name="resume" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Remarks</label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <textarea class="form-control" placeholder="Enter Remarks" name="remarks"></textarea>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </form>
        </div>
    </div>
    <!-- END: Content-->
@endsection
@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>

    <script>
        $(document).ready(function() {
            toggleAppliedFor();

            $('#refered_by').select2({
                placeholder: "Select or add candidate...",
                minimumInputLength: 2,
                tags: true,
                ajax: {
                    url: "{{ route('recruitment.fetch-candidates') }}",
                    dataType: 'json',
                    data: function(params) {
                        return {
                            search: $.trim(params.term),
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        return {
                            results: data.data.map(function(employee) {
                                return {
                                    id: employee.id,
                                    text: employee.name
                                };
                            }),
                            pagination: {
                                more: data.pagination.more
                            }
                        };
                    },
                    cache: true
                },
                createTag: function(params) {
                    return {
                        id: params.term,
                        text: params.term,
                        newOption: true
                    };
                },
                templateResult: function(data) {
                    var $result = $("<span></span>");
                    $result.text(data.text);
                    if (data.newOption) {
                        $result.append(" <em>(new)</em>");
                    }
                    return $result;
                }
            });
        });

        $('input[name="applied_for"]').on('change', function() {
            toggleAppliedFor();
        });

        function toggleAppliedFor() {
            const value = $('input[name="applied_for"]:checked').val();
            if (value === 'self') {
                setFields();
                $('.refered-by-wrapper').hide();
                $('.candidate-wrapper').show();
            } else {
                resetFields();
                $('.refered-by-wrapper').show();
                $('.candidate-wrapper').hide();
            }
        }

        function resetFields() {
            const user = "{{ $user }}";
            $('input[name="name"]').val("");
            $('input[name="email"]').val("");
            $('input[name="mobile_no"]').val("");
        }

        function setFields() {
            const user = @json($user);
            $('input[name="name"]').val(user.name);
            $('input[name="email"]').val(user.email);
            $('input[name="mobile_no"]').val(user.mobile);
        }

        $('#refered_by').on('change', function() {
            var selectedVal = $(this).val();
            if ($.isNumeric(selectedVal)) {
                $('input[name="email"], input[name="mobile_no"]').prop('readonly', true);
                $.ajax({
                    url: "{{ route('recruitment.fetch-candidates') }}", // Add this route in web.php
                    type: "GET",
                    data: {
                        id: selectedVal
                    },
                    success: function(data) {
                        if (data && data.data && data.data.length > 0) {
                            const emp = data.data[0];
                            $('input[name="email"]').val(emp.email);
                            $('input[name="mobile_no"]').val(emp.mobile_no);
                            $('input[name="name"]').val(emp.name);
                        }
                    },
                    error: function() {
                        $('input[name="email"]').val('');
                        $('input[name="mobile_no"]').val('');
                    }
                });
            } else {
                $('input[name="email"], input[name="mobile_no"]').prop('readonly', false).val('');
                $('input[name="name"]').val(selectedVal);
                $('input[name="email"]').val('');
                $('input[name="mobile_no"]').val('');
            }
        });
    </script>
@endsection
