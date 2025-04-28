@extends('layouts.app')

@section('content')



    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Edit Request</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('legal') }}">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">Edit</li>


                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <a href="{{ route('legal') }}"> <button class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</button> </a>
                            <button class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" type="button" id="save-draft-btn">
                                <i data-feather="save"></i> Save as Draft
                            </button>
                            <button type="submit" form="legal-form" class="btn btn-primary btn-sm" id="submit-btn">
                                <i data-feather="check-circle"></i> Submit
                            </button>
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
                                <form id="legal-form" method="POST" action="{{ route('legal.update', $legal->id) }}" enctype="multipart/form-data">
                                    @csrf
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

                                        <div class="row">
                                            <div class="col-md-12">

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Series <span class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <select class="form-select" name="series" id="series" disabled required>
                                                            <option value="" disabled selected>Select</option>
                                                            @foreach ($series as $ser)
                                                                <option value="{{ $ser->id }}" {{ $legal->series == $ser->id ? 'selected' : '' }}>{{ $ser->book_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>

                                                    </div>

                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Request No. <span class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="requestno" readonly id="requestno" value="{{ $legal->requestno }}">
                                                        <input type="hidden" name="status" id="status" value="">
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Issue Type <span class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-4">

                                                        <select class="form-select" name="issues" id="issues" required>
                                                            <option value="">Select</option>
                                                            @foreach ($issues as $issue)
                                                                <option value="{{ $issue->id }}" {{ $legal->issues == $issue->id ? 'selected' : '' }}>
                                                                    {{ $issue->name }}</option>
                                                            @endforeach
                                                        </select>

                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Party Type</label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <select class="form-select" name="party_type" id="party_type" required>
                                                            <option value="" disabled>Select</option>
                                                            <option value="Customer" {{ $legal->party_type == 'Customer' ? 'selected' : '' }}>Customer</option>
                                                            <option value="Vendor" {{ $legal->party_type == 'Vendor' ? 'selected' : '' }}>Vendor</option>
                                                            <option value="Loan" {{ $legal->party_type == 'Loan' ? 'selected' : '' }}>Loan</option>
                                                            <option value="Land" {{ $legal->party_type == 'Land' ? 'selected' : '' }}>Land</option>
                                                            <option value="Others" {{ $legal->party_type == 'Others' ? 'selected' : '' }}>Others</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Party Name</label>
                                                    </div>

                                                    <div class="col-md-4" id="party_name_container">
                                                        <input type="text" class="form-control" name="party_name" placeholder="Enter Party Name" required value="{{ $legal->party_name }}">
                                                    </div>

                                                    <div class="col-md-4" id="customersearch" style="display: none;">
                                                        <div class="action-button mt-50">
                                                            <button type="button" data-bs-toggle="modal" type="button" data-bs-target="#customermodal" class="btn btn-outline-primary btn-sm"><i
                                                                    data-feather="plus-square"></i> Find Party
                                                                Name</button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4" id="loansearch" style="display: none;">
                                                        <div class="action-button mt-50">
                                                            <button type="button" data-bs-toggle="modal" type="button" data-bs-target="#rescdule" class="btn btn-outline-primary btn-sm"><i
                                                                    data-feather="plus-square"></i> Find Party
                                                                Name</button>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4" id="landsearch" style="display: none;">
                                                        <div class="action-button mt-50">
                                                            <button type="button" data-bs-toggle="modal" type="button" data-bs-target="#landmodal" class="btn btn-outline-primary btn-sm"><i
                                                                    data-feather="plus-square"></i> Find Party
                                                                Name</button>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">File Number </label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="filenumber" id="filenumber" value="{{ $legal->filenumber }}">
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="name" required value="{{ $legal->name }}" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Mobile No.</label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="phone" value="{{ $legal->phone }}" id="phone" onchange="validatePhone()" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Email-Id</label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="email" value="{{ $legal->email }}" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Correspondence Address</label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <input type="text" class="form-control" name="address" id="address" value="{{ $legal->address }}">
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <input type="text" name="subject" required class="form-control" value="{{ $legal->subject }}">
                                                    </div>

                                                </div>



                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Upload Document</label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <input type="file" class="form-control" id="fileInput" name="files[]" multiple>
                                                        <div id="appenddata"></div>
                                                        <div id="removeappenddata">
                                                            <!-- Existing files will be dynamically inserted here -->
                                                            @if ($legal->file_path)
                                                                @foreach (explode(',', $legal->file_path) as $file)
                                                                    <div class="image-uplodasection">
                                                                        @if (strpos($file, '.pdf') !== false)
                                                                            <i data-feather="file-text" class="fileuploadicon" data-file="{{ $file }}"></i>
                                                                        @elseif(strpos($file, '.doc') !== false || strpos($file, '.docx') !== false)
                                                                            <i data-feather="file" class="fileuploadicon" data-file="{{ $file }}"></i>
                                                                        @else
                                                                            <img src="{{ asset('uploads/legal/' . $file) }}" class="preview-image" alt="{{ $file }}">
                                                                        @endif
                                                                        <div class="delete-img text-danger" onclick="removeExistingFile('{{ $file }}')">
                                                                            <i data-feather="x"></i>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            @endif
                                                        </div>

                                                        <input type="hidden" name="remove_files" id="remove-file-input" value="">
                                                    </div>


                                                </div>

                                                <div class="row  mb-1">
                                                    <div class="col-md-2">
                                                        <label class="form-label">Remarks</label>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <textarea type="text" rows="4" class="form-control" placeholder="Enter Remarks here..." name="remark" maxlength="250">{{ $legal->remark }}</textarea>
                                                    </div>

                                                </div>







                                            </div>


                                        </div>


                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- Modal to add new record -->

                </section>


            </div>
        </div>
    </div>
    <!-- END: Content-->


    <div class="modal fade text-start" id="rescdule" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Find Party
                            Name</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Customer Name</label>
                                <select class="form-select select2" name="filter_customer_name" id="filter_customer_name">
                                    <option value="">Select</option>
                                    @if (isset($loans))
                                        @foreach ($loans as $key => $val)
                                            <option value="{{ $val->id }}">{{ $val->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Application No.</label>
                                <select class="form-select select2" name="filter_appl_no" id="filter_appl_no">
                                    <option value="">Select</option>
                                    @if (isset($loans))
                                        @foreach ($loans as $key => $val)
                                            <option value="{{ $val->id }}">{{ $val->appli_no }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Application No.</th>
                                        <th>Customer Name</th>
                                        <th>Ref No</th>
                                        <th>Loan Amt.</th>
                                        <th>Email</th>
                                        <th>Mobile No.</th>
                                    </tr>
                                </thead>
                                <tbody id="pending_schedule"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
                    <button class="btn btn-primary btn-sm" data-bs-dismiss="modal" id="process_disbursal"><i data-feather="check-circle"></i> Process</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade text-start" id="landmodal" tabindex="-1" aria-labelledby="myModalLabel18" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel18">Find Party
                            Name</h4>
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
                                    @if (isset($leases))
                                        @foreach ($leases as $key => $val)
                                            <option value="{{ $val->id }}">{{ $val?->land?->document_no }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Customer Name</label>
                                <select class="form-select select2" name="filter_land_customer_name" id="filter_land_customer_name">
                                    <option value="">Select</option>
                                    @if (isset($leases))
                                        @foreach ($leases as $key => $val)
                                            <option value="{{ $val->id }}">
                                                @if (!empty($val->cust))
                                                    {{ $val->cust->company_name }}
                                                @else
                                                    N/A
                                                @endif
                                            </option>
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
                                    @if (isset($leases))
                                        @foreach ($leases as $key => $val)
                                            @if ($val->land?->plot_no != null)
                                                <option value="{{ $val->id }}">{{ $val->land?->plot_no }}
                                                </option>
                                            @endif
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
                                    @if (isset($leases))
                                        @foreach ($leases as $key => $val)
                                            <option value="{{ $val->id }}">{{ $val->khasara_no }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
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
                                    </tr>
                                </thead>
                                <tbody id="pending_schedule_land"></tbody>
                            </table>
                        </div>
                    </div>


                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
                    <button class="btn btn-primary btn-sm" data-bs-dismiss="modal" id="process_disbursal_land"><i data-feather="check-circle"></i> Process</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade text-start" id="customermodal" tabindex="-1" aria-labelledby="myModalLabel19" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel19">Find Party Name</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label" for="filter_customer_code">Customer Code</label>
                                <select class="form-select select2" name="filter_customer_code" id="filter_customer_code">
                                    <option value="">Select</option>
                                    @if (isset($customers))
                                        @foreach ($customers as $key => $val)
                                            <option value="{{ $val->id }}">{{ $val->customer_code }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Customer Name</label>
                                <select class="form-select select2" name="filter_customerName" id="filter_customerName">
                                    <option value="">Select</option>
                                    @if (isset($customers))
                                        @foreach ($customers as $key => $val)
                                            <option value="{{ $val->id }}">{{ $val->display_name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Email</label>
                                <select class="form-select select2" name="filter_email" id="filter_email">
                                    <option value="">Select</option>
                                    @if (isset($customers))
                                        @foreach ($customers as $key => $val)
                                            <option value="{{ $val->id }}">{{ $val->email }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Phone Number</label>
                                <select class="form-select select2" name="filter_phone" id="filter_phone">
                                    <option value="">Select</option>
                                    @if (isset($customers))
                                        @foreach ($customers as $key => $val)
                                            <option value="{{ $val->id }}">{{ $val->phone }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>code</th>
                                        <th>name</th>
                                        <th>email</th>
                                        <th>phone No</th>
                                    </tr>
                                </thead>
                                <tbody id="pending_customer"></tbody>
                            </table>
                        </div>
                    </div>


                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i> Cancel</button>
                    <button class="btn btn-primary btn-sm" data-bs-dismiss="modal" id="process_customer"><i data-feather="check-circle"></i> Process</button>
                </div>
            </div>
        </div>
    </div>
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

        document.getElementById('save-draft-btn').addEventListener('click', function() {
            document.getElementById('status').value = 'draft';
            document.getElementById('legal-form').submit();
        });

        document.getElementById('submit-btn').addEventListener('click', function() {
            document.getElementById('status').value = 'submitted';
        });

        $('#issues').on('change', function() {
            var issue_id = $(this).val();
            var seriesSelect = $('#series');

            seriesSelect.empty(); // Clear any existing options
            seriesSelect.append('<option value="">Select</option>');

            if (issue_id) {
                $.ajax({
                    url: "{{ url('get-series') }}/" + issue_id,
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        $.each(data, function(key, value) {
                            seriesSelect.append('<option value="' + key + '">' + value +
                                '</option>');
                        });
                    }
                });
            }
        });

        $('#series').on('change', function() {
            var book_id = $(this).val();
            var request = $('#requestno');

            request.val(''); // Clear any existing options

            if (book_id) {
                $.ajax({
                    url: "{{ url('get-request') }}/" + book_id,
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
    </script>
    <script type="text/javascript">
        let removedFiles = new Set(); // Set to keep track of removed files

        let filesArray = [];

        document.getElementById('fileInput').addEventListener('change', function(event) {
            const files = Array.from(event.target.files); // Convert FileList to Array
            filesArray = filesArray.concat(files); // Add new files to the array
            updateFilePreviews();
            updateFileInput();
            feather.replace(); // Initialize Feather icons after updating the DOM
        });

        function updateFilePreviews() {
            const appendData = document.getElementById('appenddata');
            appendData.innerHTML = ''; // Clear previous previews

            filesArray.forEach((file, index) => {
                const reader = new FileReader();

                // Create a div to hold the file preview
                const fileDiv = document.createElement('div');
                fileDiv.classList.add('image-uplodasection');

                // Create a preview element
                const filePreview = document.createElement('div');
                filePreview.classList.add('filepreview');

                // Create an icon or image for the file type
                const fileIcon = document.createElement('i');
                fileIcon.classList.add('fileuploadicon');

                if (file.type.startsWith('image/')) {
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.classList.add('preview-image');
                        filePreview.appendChild(img);
                    };
                    reader.readAsDataURL(file);
                } else {
                    // Assign the correct icon for non-image files (e.g., PDF, DOCX)
                    let iconType;
                    switch (file.type) {
                        case 'application/pdf':
                            iconType = 'file-text'; // Or any other suitable icon
                            break;
                        case 'application/msword':
                        case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                            iconType = 'file';
                            break;
                        default:
                            iconType = 'file';
                            break;
                    }
                    fileIcon.setAttribute('data-feather', iconType);
                    filePreview.appendChild(fileIcon);
                }

                // Add a delete button
                const deleteButton = document.createElement('div');
                deleteButton.classList.add('delete-img', 'text-danger');
                deleteButton.innerHTML = '<i data-feather="x"></i>';
                deleteButton.setAttribute('data-index', index); // Store the file index
                deleteButton.addEventListener('click', function() {
                    removeFile(this.getAttribute('data-index'));
                });
                filePreview.appendChild(deleteButton);

                fileDiv.appendChild(filePreview);
                appendData.appendChild(fileDiv);
            });

            // Replace feather icons
            feather.replace();
        }


        function removeFile(index) {
            filesArray.splice(index, 1); // Remove the file from the array
            updateFilePreviews(); // Update the previews
            updateFileInput(); // Update the file input
        }

        function updateFileInput() {
            const dataTransfer = new DataTransfer(); // Create a new DataTransfer object

            filesArray.forEach(file => {
                dataTransfer.items.add(file); // Add remaining files to the DataTransfer object
            });

            document.getElementById('fileInput').files = dataTransfer.files; // Update the file input with the new FileList
        }

        function removeExistingFile(fileName) {
            // Add the file to the removed files set
            removedFiles.add(fileName);

            // Update the hidden input with the removed files
            document.getElementById('remove-file-input').value = Array.from(removedFiles).join(',');

            console.log(document.getElementById('remove-file-input').value);
            // Remove the file preview from the UI
            const fileElements = document.querySelectorAll('#removeappenddata .image-uplodasection');
            fileElements.forEach(element => {
                const icon = element.querySelector('.fileuploadicon');
                if (icon && icon.dataset.file === fileName) {
                    element.remove();
                }
            });
        }

        function validatePhone() {
            var phoneInput = document.getElementById('phone').value;
            var pattern = /^[0-9]$/; // Regex for a valid 10-digit phone number

            if (pattern.test(phoneInput)) {
                // The input is valid; do nothing
                return;
            } else {
                // If the input is invalid, clean it
                var cleanedInput = phoneInput.replace(/[^0-9]/g, ''); // Remove non-digit characters

                // If the cleaned input is more than 10 digits, truncate it to 10 digits
                if (cleanedInput.length > 16) {
                    cleanedInput = cleanedInput.substring(0, 16);
                }

                alert('Please enter a valid phone number.');


                // Update the input field with the cleaned (and possibly corrected) phone number
                document.getElementById('phone').value = cleanedInput;
            }
        }
    </script>

    <script>
        $(document).ready(function() {
            $('#rescdule').on('show.bs.modal', function() {
                // Clear the tbody inside the modal when it opens
                $("#pending_schedule_land").html('');
                $("#pending_schedule").html('');
                $("#filter_appl_no").val('');
                $("#filter_customer_name").val('');
            });
            $('#landmodal').on('show.bs.modal', function() {
                // Clear the tbody inside the modal when it opens
                $("#pending_schedule").html('');
                $("#pending_schedule_land").html('');
                $("#filter_khasara_no").val('');
                $("#filter_plot_no").val('');
                $("#filter_land_customer_name").val('');
                $("#filter_land_no").val('');
            });
            $('#customermodal').on('show.bs.modal', function() {
                // Clear the tbody inside the modal when it opens
                $("#pending_customer").html('');
                $("#pending_schedule_land").html('');
                $("#pending_schedule").html('');
                $("#filter_customer_code").val('').trigger('change');
                $("#filter_customerName").val('').trigger('change');
                $("#filter_email").val('').trigger('change');
                $("#filter_phone").val('').trigger('change');
            });

            var initialPartyType = "{{ $legal->party_type }}"; // Get the initial party type
            var initialPartyName = "{{ $legal->party_name }}"; // Get the initial party name

            function updatePartyNameField(partyType, existingPartyName) {
                console.log('partytype', partyType, 'existing', existingPartyName);
                var partyNameContainer = $('#party_name_container');
                partyNameContainer.empty();

                if (partyType === 'Customer') {
                    var customerOptions = `<select class="form-select select2" name="party_name" required>
                <option value="" disabled>Select Customer</option>`;
                    @foreach ($customers as $customer)
                        customerOptions += `<option value="{{ $customer->id }}"
                    ${existingPartyName == "{{ $customer->id }}" ? 'selected' : ''}>
                    {{ $customer->company_name }}
                </option>`;
                    @endforeach
                    customerOptions += `</select>`;
                    partyNameContainer.append(customerOptions);
                    $("#customersearch").css('display', 'block');
                    $("#loansearch").css('display', 'none');
                    $("#landsearch").css('display', 'none');

                } else if (partyType === 'Vendor') {
                    var vendorOptions = `<select class="form-select select2" name="party_name" required>
                <option value="" disabled>Select Vendor</option>`;
                    @foreach ($vendors as $vendor)
                        vendorOptions += `<option value="{{ $vendor->id }}"
                    ${existingPartyName == "{{ $vendor->id }}" ? 'selected' : ''}>
                    {{ $vendor->company_name }}
                </option>`;
                    @endforeach
                    vendorOptions += `</select>`;
                    partyNameContainer.append(vendorOptions);
                    $("#customersearch").css('display', 'none');
                    $("#loansearch").css('display', 'none');
                    $("#landsearch").css('display', 'none');

                } else if (partyType === 'Loan') {
                    var loanOptions = `<select class="form-select select2" name="party_name" required>
                <option value="" ${existingPartyName == "" ? 'selected' : ''}>Select Loan No</option>`;
                    @foreach ($loans as $loan)
                        loanOptions += `<option value="{{ $loan->id }}"
                    ${existingPartyName == "{{ $loan->id }}" ? 'selected' : ''}>
                    {{ $loan->name }}
                </option>`;
                    @endforeach
                    loanOptions += `</select>`;
                    partyNameContainer.append(loanOptions);
                    $("#loansearch").css('display', 'block');
                    $("#landsearch").css('display', 'none');
                    $("#customersearch").css('display', 'none');

                } else if (partyType === 'Land') {
                    var landOptions = `<select class="form-select select2" name="party_name" required>
                        <option value="" ${existingPartyName == "" ? 'selected' : ''}>Select Land No</option>`;
                    @foreach ($leases as $land)
                        landOptions += `<option value="{{ $land->id }}"
                                ${existingPartyName == "{{ $land->id }}" ? 'selected' : ''}>
                                {{ $land?->land?->document_no }}
                            </option>`;
                    @endforeach
                    landOptions += `</select>`;
                    partyNameContainer.append(landOptions);
                    $("#loansearch").css('display', 'none');
                    $("#landsearch").css('display', 'block');
                    $("#customersearch").css('display', 'none');

                } else if (partyType === 'Others') {
                    var otherInput = `<input type="text" class="form-control" name="party_name" placeholder="Enter Party Name" required value="${existingPartyName}">`;
                    partyNameContainer.append(otherInput);
                    $("#loansearch").css('display', 'none');
                    $("#landsearch").css('display', 'none');
                    $("#customersearch").css('display', 'none');
                }

                $('.select2').select2();
            }

            // Initial load
            if (initialPartyType) {
                updatePartyNameField(initialPartyType, initialPartyName);
            }

            // Handle subsequent changes
            $('#party_type').change(function() {
                var partyType = $(this).val();
                updatePartyNameField(partyType, ''); // Empty string for new selections
            });
        });

        let isUpdating = false;

        $('#filter_appl_no').on('change', function() {
            if (!isUpdating) {
                isUpdating = true; // Prevent recursive event triggering
                var selectedNoId = $(this).val();

                console.log(selectedNoId);

                // Set the Customer Name dropdown based on selected Application No.
                $('#filter_customer_name').val('').trigger('change');

                // Fetch and update the table based on selected Application No.
                updateTable(selectedNoId);

                isUpdating = false; // Re-enable event triggering after the update
            }
        });


        $('#filter_customer_name').on('change', function() {
            if (!isUpdating) {
                isUpdating = true; // Prevent recursive event triggering
                var selectedNoId = $(this).val();

                console.log(selectedNoId);

                // Set the Application No. dropdown based on selected Customer Name
                $('#filter_appl_no').val('').trigger('change');

                // Fetch and update the table based on selected Customer
                updateTable(selectedNoId);

                isUpdating = false; // Re-enable event triggering after the update
            }
        });


        function updateTable(customerId) {
            var loans = @json($loans);
            $('#pending_schedule').html('');
            var rows = '';

            // Loop through the loans and filter by customerId
            loans.forEach(function(loan, index) {
                if (loan.id == customerId) {
                    rows += `<tr>
                    <td>
                        <input type="checkbox" name="loan_checkbox" class="loan-checkbox" value="${loan.id}">
                            <div class="hidden-inputs">
                                <input type="hidden" name="id" value="${loan.id}">
                            </div>
                        </td>
                    <td>${loan.appli_no}</td>
                    <td>${loan.name}</td>
                    <td>${loan.ref_no}</td>
                    <td>${loan.loan_amount}</td>
                    <td>${loan.email}</td>
                    <td>${loan.mobile}</td>
                </tr>`;
                }
            });

            // Update the table body with the new rows
            $('#pending_schedule').html(rows);
            // Initially disable the proceed button
            toggleProceedButton();
        }

        $(document).on('change', '.loan-checkbox', function() {
            toggleProceedButton();
        });

        // Function to toggle the 'Proceed' button based on checked checkboxes
        function toggleProceedButton() {
            // Check if any checkbox is checked
            var anyChecked = $('.loan-checkbox:checked').length > 0;

            // Enable or disable the button based on the checkbox state
            $('#process_disbursal').prop('disabled', !anyChecked);
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
                    appli_no: row.cells[1].textContent,
                    name: row.cells[2].textContent,
                    ref_no: row.cells[3].textContent,
                    loan_amount: row.cells[4].textContent,
                    email: row.cells[5].textContent,
                    mobile: row.cells[6].textContent
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
            console.log('loan', selectedRowsData);
            $("select[name='party_name']").val(selectedRowsData[0].id).trigger('change');
            $("input[name='filenumber']").val(selectedRowsData[0].appli_no);
            $("input[name='name']").val(selectedRowsData[0].name);
            $("input[name='email']").val(selectedRowsData[0].email);
            $("input[name='mobile']").val(selectedRowsData[0].mobile);
        });

        document.addEventListener("DOMContentLoaded", function() {
            //setupFilters();

            const processButton = document.getElementById('process_disbursal');

            // Initially disable the button
            processButton.disabled = true;

            processButton.addEventListener('click', () => {
                const selectedRowsData = handleProcessDisbursal();
            });
        });


        window.routes = {
            landOnleaseAddFilter: @json(route('legal.onLeaseAddFilter')),
        };

        let filterData = {};
        // Pending Disbursal
        function setupFilters() {

            const filters = [{
                    selector: $("#filter_land_no"),
                    key: "landNo",
                    type: "select"
                }, // select field
                {
                    selector: $("#filter_land_customer_name"),
                    key: "customerName",
                    type: "select",
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
                    console.log($(selector).val());
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

                const tbody = document.getElementById('pending_schedule_land'); // Get the table body

                updateTableLand(tbody, data.land_filter_list);
            } catch (error) {
                console.error("Error fetching purchase orders:", error);
            }
        }

        function updateTableLand(tbody, lands) {
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

            const hiddenInputs = document.createElement("div");
            hiddenInputs.style.display = "none";

            // Add hidden inputs for any additional data you need to store
            const hiddenData = {
                id: data.id ?? "", // Example hidden value
                area: data.area_sqft ?? "", // Example hidden value
                created_at: data.created_at ?? "", // Example hidden value
                document_no: data.land.documentno ?? "", // Example hidden value
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
            let companyName = data.cust ? data.cust.company_name : "N/A";

            const cellsData = [
                data.land.land_no ?? "N/A",
                companyName,
                data.land.plot_no ?? "N/A",
                data.khasara_no ?? "N/A",
                data.area_sqft ?? "N/A",
                data.cost ?? "N/A",
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
        function handleProcessDisbursalLand() {
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
        document.getElementById('process_disbursal_land').addEventListener('click', () => {
            const selectedRowsData = handleProcessDisbursalLand();
            console.log('land', selectedRowsData);
            $("select[name='party_name']").val(selectedRowsData[0].id).trigger('change');
            $("input[name='filenumber']").val(selectedRowsData[0].document_no);
            $("input[name='name']").val(selectedRowsData[0].customer);
        });

        function updateProcessButton() {
            const processButton = document.getElementById('process_disbursal_land');
            const checkedBoxes = document.querySelectorAll('input[type="checkbox"]:checked');

            // Enable button if at least one checkbox is checked, disable otherwise
            processButton.disabled = checkedBoxes.length === 0;
        }

        // ========================== //
        // Customer model filter data //
        // ========================== //
        function setupCustomerFilters() {
            let activeFilter = null;

            const filters = [{
                    selector: $("#filter_customer_code"),
                    key: "customer_code",
                    type: "select",
                    label: "Customer Code"
                },
                {
                    selector: $("#filter_customerName"),
                    key: "customerName",
                    type: "select",
                    label: "Customer Name"
                },
                {
                    selector: $("#filter_email"),
                    key: "email",
                    type: "select",
                    label: "Email"
                },
                {
                    selector: $("#filter_phone"),
                    key: "phone",
                    type: "select",
                    label: "Phone"
                }
            ];

            // Initialize Select2 for all filter dropdowns
            filters.forEach(filter => {
                if (filter.type === "select") {
                    filter.selector.select2({
                        placeholder: `Select ${filter.label}`,
                        allowClear: true
                    });
                }
            });

            function resetOtherFilters(currentSelector) {
                filters.forEach(filter => {
                    if (filter.selector[0] !== currentSelector[0]) {
                        filter.selector.val(null).trigger('change');
                    }
                });
            }

            // Setup event handlers
            filters.forEach(filter => {
                const selector = filter.selector;
                selector.on("change", function() {
                    const value = $(this).val();
                    if (value && value !== '') {
                        resetOtherFilters($(this));
                        activeFilter = {
                            key: filter.key,
                            value: value,
                            label: filter.label
                        };
                        updateFilterAndFetch();
                    }
                });
            });

            function updateFilterAndFetch() {
                // Reset all parameters
                let params = {
                    id: '',
                };

                // Apply active filter
                if (activeFilter) {
                    params['id'] = activeFilter.value;
                }

                updateTableCustomer(params);
            }
        }

        function updateTableCustomer(params) {
            const customers = @json($customers);
            const tbody = document.getElementById('pending_customer'); // Get the table body
            tbody.innerHTML = '';

            if (!Array.isArray(customers)) {
                console.error("Customer Data is not an array");
                return;
            }

            customers.forEach((data, index) => {
                if (typeof data === "object" && data !== null && data.id == params.id) {
                    const row = createCustomerTableRow(data, index);
                    tbody.appendChild(row);
                } else {
                    console.error(`Invalid data data at index ${index}:`, data);
                }
            });
        }

        function createCustomerTableRow(data, index) {
            const row = document.createElement("tr");

            // Create checkbox cell
            const checkboxCell = document.createElement("td");
            const checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.dataset.rowId = index; // Store the index as data attribute
            checkbox.addEventListener('change', updateProcessCustomerButton);
            checkboxCell.appendChild(checkbox);

            const hiddenInputs = document.createElement("div");
            hiddenInputs.style.display = "none";

            // Add hidden inputs for any additional data you need to store
            const hiddenData = {
                id: data.id ?? "", // Example hidden value
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
                data.customer_code ?? "N/A",
                data.display_name ?? "N/A",
                data.email ?? "N/A",
                data.phone ?? "N/A",
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
        function handleProcessCustomer() {
            const checkedRows = document.querySelectorAll('input[type="checkbox"]:checked');
            const selectedData = [];

            checkedRows.forEach(checkbox => {
                const row = checkbox.closest('tr');
                const hiddenInputs = row.querySelector('div').querySelectorAll('input[type="hidden"]');

                // Collect visible data
                const visibleData = {
                    customer_code: row.cells[1].textContent,
                    name: row.cells[2].textContent,
                    email: row.cells[3].textContent,
                    phone: row.cells[4].textContent
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
        document.getElementById('process_customer').addEventListener('click', () => {
            const selectedRowsData = handleProcessCustomer();
            //console.log(selectedRowsData[0].id);
            $("select[name='party_name']").val(selectedRowsData[0].id).trigger('change');
            $("input[name='filenumber']").val(selectedRowsData[0].customer_code);
            $("input[name='name']").val(selectedRowsData[0].name);
            $("input[name='email']").val(selectedRowsData[0].email);
            $("input[name='phone']").val(selectedRowsData[0].phone);
        });

        function updateProcessCustomerButton() {
            const processButton = document.getElementById('process_customer');
            const checkedBoxes = document.querySelectorAll('input[type="checkbox"]:checked');

            // Enable button if at least one checkbox is checked, disable otherwise
            processButton.disabled = checkedBoxes.length === 0;
        }

        document.addEventListener("DOMContentLoaded", function() {
            setupFilters();
            setupCustomerFilters();

            const processButton = document.getElementById('process_disbursal_land');

            // Initially disable the button
            processButton.disabled = true;

            processButton.addEventListener('click', () => {
                const selectedRowsData = handleProcessDisbursalLand();
            });

            // Customer model filter data get
            const processCustomerButton = document.getElementById('process_customer');

            // Initially disable the button
            processCustomerButton.disabled = true;

            processCustomerButton.addEventListener('click', () => {
                const selectedRowsData = handleProcessCustomer();
            });
        });
    </script>
@endsection
@endsection
