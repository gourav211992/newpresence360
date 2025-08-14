@extends('layouts.crm')

@section('styles')
    {{-- <link href="{{ asset('app-assets/css/summernote.min.css') }}" rel="stylesheet"> --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote.min.css" rel="stylesheet">
@endsection

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content todo-application">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <form method="POST" id="notes_form" action="{{ route('notes.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header row">
                    <div class="content-header-left col-md-6  mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Add Notes</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ url('/crm/home') }}">Home</a>
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
                            <button type="button" onClick="javascript: history.go(-1)"
                                class="btn btn-secondary btn-sm mb-50 mb-sm-0">
                                <i data-feather="arrow-left-circle"></i> Back
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0">
                                <i data-feather="arrow-left-circle"></i> Submit
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body customernewsection-form">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                    <div>
                                        <h4 class="card-title text-theme">Basic Information</h4>
                                        <p class="card-text">Fill the details</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="row align-items-center mb-1">
                                    <div class="col-md-3">
                                        <label class="form-label">Customer Type <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="demo-inline-spacing">
                                            <div class="form-check form-check-primary mt-25">
                                                <input type="radio" id="customer_type1" name="customer_type"
                                                    value="New" class="form-check-input"
                                                    {{ Request::old('customer_type', 'New') == 'New' ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bolder" for="customer_type1">New</label>
                                            </div>
                                            <div class="form-check form-check-primary mt-25">
                                                <input type="radio" id="customer_type2" name="customer_type"
                                                    value="Existing" class="form-check-input"
                                                    {{ Request::old('customer_type') == 'Existing' ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bolder"
                                                    for="customer_type2">Existing</label>
                                                @if ($errors->has('customer_type'))
                                                    <span class="danger">{{ $errors->first('customer_type') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row align-items-center mb-1">
                                    <div class="col-md-3">
                                        <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                    </div>
                                    <div class="col-md-5" id="customer_name_container">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-body dasboardnewbody">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="card h-100 mb-0">
                                <div class="card-body customernewsection-form">
                                    <div class="border-bottom mb-2 pb-25">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="newheader ">
                                                    <h4 class="card-title text-theme">Customer Information</h4>
                                                    <p class="card-text">View the details</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="row loandetailview">
                                                <div class="col-md-6 mb-1">
                                                    <label class="form-label">Customer Name</label>
                                                    <input type="text" id="company_name" name="company_name"
                                                        value="{{ Request::old('company_name') }}"
                                                        class="form-control @error('company_name') is-invalid @enderror"
                                                        readonly />
                                                    @error('company_name')
                                                        <span class="text-danger" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-1">
                                                    <label class="form-label">Phone No.<span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" id="phone_no" name="phone_no"
                                                        value="{{ Request::old('phone_no') }}"
                                                        class="form-control numberonly-v2 @error('phone_no') is-invalid @enderror" />
                                                    @error('phone_no')
                                                        <span class="text-danger" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-1">
                                                    <label class="form-label">Contact Person<span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" id="contact_person" name="contact_person"
                                                        class="form-control @error('contact_person') is-invalid @enderror"
                                                        value="{{ Request::old('contact_person') }}" />
                                                    @error('contact_person')
                                                        <span class="text-danger" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-1">
                                                    <label class="form-label">Sales Representative</label>
                                                    <div id="sales_representative_container"></div>
                                                    @error('sales_representative')
                                                        <span class="text-danger" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-12 mb-1">
                                                    <label class="form-label">Email-ID</label>
                                                    <input type="text" id="email_id" name="email_id"
                                                        class="form-control @error('email_id') is-invalid @enderror"
                                                        value="{{ Request::old('email_id') }}" />
                                                    @error('email_id')
                                                        <span class="text-danger" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-12 mb-1">
                                                    <label class="form-label" id="address_label">Address</label>
                                                    <h6 class="fw-bolder text-dark" id="address_content"> </h6>
                                                    <input type="text" id="address" name="address"
                                                        class="form-control @error('address') is-invalid @enderror"
                                                        value="{{ Request::old('address') }}" />
                                                    @error('address')
                                                        <span class="text-danger" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-12 mb-1 note" style="display: none">
                                                    <label class="form-label" id="previous_note_label">Previous
                                                        Note</label>
                                                    <h6 class="fw-bolder text-dark" id="previous_note_content"> </h6>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="note-containeradd">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="notconhead">
                                            <h6 class="card-title text-theme">Meeting notes</h6>
                                            <p class="card-text">Fill the details below</p>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="m-2 p-2 rounded bg-white customernewsection-form">
                                            <div class="row align-items-center mb-1 stage" style="display: none">
                                                <div class="col-md-3">
                                                    <label class="form-label">Status <span
                                                            class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-6">
                                                    <select
                                                        class="form-control @error('meeting_status_id') is-invalid @enderror"
                                                        name="meeting_status_id">
                                                        <option value="" disabled
                                                            {{ Request::old('meeting_status_id') ? '' : 'selected' }}>
                                                            Select Status</option>
                                                        @foreach ($meetingStatus as $meetstatus)
                                                            <option value="{{ $meetstatus->id }}"
                                                                {{ Request::old('meeting_status_id') == $meetstatus->id ? 'selected' : '' }}>
                                                                {{ $meetstatus->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('meeting_status_id')
                                                        <span class="text-danger" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Meeting Objective <span
                                                            class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="hidden" name="meeting_objective"
                                                        id="meeting_objective_input" />
                                                    <select
                                                        class="form-control @error('meeting_objective') is-invalid @enderror"
                                                        name="meeting_objective_id" id="meeting_objective_select">
                                                        <option value="" disabled
                                                            {{ Request::old('meeting_objective_id') ? '' : 'selected' }}>
                                                            Select Status</option>
                                                        @foreach ($meetingObjectives as $objective)
                                                            <option value="{{ $objective->id }}"
                                                                {{ Request::old('meeting_objective_id') == $objective->id ? 'selected' : '' }}>
                                                                {{ $objective->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('meeting_objective_id')
                                                        <span class="text-danger" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row mb-5">
                                                <div class="col-md-3">
                                                    <label class="form-label">Notes</label>
                                                </div>
                                                <div class="col-md-6">
                                                    <textarea class="summernote @error('description') is-invalid @enderror" name="description"></textarea>
                                                    @error('description_id')
                                                        <span class="text-danger" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Attachment </label>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="file" name="attachment"
                                                        class="form-control @error('attachment') is-invalid @enderror"
                                                        id="attachment">
                                                    <div id="preview">
                                                    </div>
                                                    @error('attachment')
                                                        <span class="text-danger" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- ChartJS section end -->
                </div>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        if ("{{ session('success') }}") {
            Swal.fire({
                icon: 'success',
                text: "{{ session('success') }}",
            })
        }

        if ("{{ session('error') }}") {
            Swal.fire({
                icon: 'warning',
                text: "{{ session('error') }}",
            })
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#meeting_objective_select').on('change', function() {
                var selectedValue = $(this).find('option:selected').text()
            .trim(); // Get the selected option's text
                $('#meeting_objective_input').val(selectedValue); // Set that value to the input field
            });

            const customerTypeRadios = document.querySelectorAll('input[name="customer_type"]');
            const customerNameContainer = document.getElementById('customer_name_container');
            const salesRepresentativeContainer = document.getElementById('sales_representative_container');

            // Function to toggle Customer Name
            function toggleCustomerFields() {
                const selectedType = document.querySelector('input[name="customer_type"]:checked').value;
                if (selectedType === 'New') {
                    document.getElementById('address_label').hidden = false;
                    document.getElementById('address').hidden = false;
                    document.getElementById('address_content').hidden = true;

                    updateCompanyName();
                    resetFields();
                    $(".stage").show();
                    $(".note").hide();
                    customerNameContainer.innerHTML = `
                        <input type="text" class="form-control @error('customer_name') is-invalid @enderror" name="customer_name" value="{{ old('customer_name') }}" oninput="updateCompanyName(this.value)">
                        @error('customer_name')
                            <span class="text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    `;
                    salesRepresentativeContainer.innerHTML = `
                        <select class="form-select select2 @error('sales_representative') is-invalid @enderror" name="sales_representative" id="sales_representative">
                            <option value="" disabled {{ old('sales_representative') ? '' : 'selected' }}>Select Representative</option>
                            @foreach ($salePersons as $rep)
                                <option value="{{ $rep->id }}" {{ old('sales_representative') == $rep->id ? 'selected' : '' }}>
                                    {{ $rep->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('sales_representative')
                            <span class="text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    `;
                } else if (selectedType === 'Existing') {
                    resetFields();
                    $(".stage").hide();
                    $(".note").show();

                    customerNameContainer.innerHTML = `
                        <select id="customer_id" name="customer_code" class="form-select select2 @error('customer_code') is-invalid @enderror" onchange="fetchCustomers(this.value)">
                            <option value="" disabled {{ old('customer_code') ? '' : 'selected' }}>Select Customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->customer_code }}"
                                    {{ old('customer_code') == $customer->customer_code ? 'selected' : '' }}>
                                    ${cleanCompanyName('{{ $customer->company_name }}')}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_code')
                            <span class="text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    `;
                    salesRepresentativeContainer.innerHTML = `
                        <input type="text" id="sales_representative" class="form-control @error('sales_representative') is-invalid @enderror" name="sales_representative" value="{{ old('sales_representative') }}">
                        @error('sales_representative')
                            <span class="text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    `;

                    const customerIdSelect = document.getElementById('customer_id');
                    if (customerIdSelect) {
                        console.log(customerIdSelect.value +
                        "+++++++++"); // This will now only run if the element exists
                        fetchCustomers(customerIdSelect.value);
                    }
                    disabledFields();
                }
                $('.select2').select2();
            }

            toggleCustomerFields();

            customerTypeRadios.forEach(radio => {
                radio.addEventListener('change', toggleCustomerFields);
            });
        });

        function fetchCustomers(customerId) {
            $.ajax({
                url: `/crm/get-customers/${customerId}`,
                type: 'GET',
                success: function(customer) {
                    console.log(customer);
                    document.getElementById('company_name').value = customer['customer']['company_name'] || '';
                    document.getElementById('phone_no').value = customer['customer']['phone'] || '';
                    document.getElementById('email_id').value = customer['customer']['email'] || '';
                    document.getElementById('contact_person').value = customer['customer']['contact_person'] ||
                        '';
                    document.getElementById('sales_representative').value = customer['customer'][
                        'sales_representative'
                    ] ? customer['customer']['sales_representative']['name'] : '';
                    // document.getElementById('address').value = customer['customer']['address']['full_address'] || '';
                    // document.getElementById('previous_note').value = customer['diary'] ? customer['diary']['subject'] : '';
                    document.getElementById('address_label').hidden = true;
                    document.getElementById('address_content').hidden = true;
                    document.getElementById('previous_note_label').hidden = true;
                    document.getElementById('previous_note_content').hidden = true;
                    if (customer['customer']['customer_address']) {
                        document.getElementById('address_label').hidden = false;
                        document.getElementById('address_content').hidden = false;
                        document.getElementById('address_content').textContent = customer['customer'][
                            'customer_address'
                        ];
                    }

                    if (customer['diary']?.['subject']) {
                        document.getElementById('previous_note_content').hidden = false;
                        document.getElementById('previous_note_content').textContent = customer['diary'][
                            'subject'
                        ];
                        document.getElementById('previous_note_label').hidden = false;
                    }


                    enableEditableFields();
                },

            });
        }

        function updateCompanyName(value) {
            document.getElementById('company_name').value = value
            document.getElementById('company_name').disabled = true;
        }

        function enableEditableFields() {
            document.getElementById('company_name').disabled = true;
            document.getElementById('phone_no').disabled = true;
            document.getElementById('email_id').disabled = true;
            document.getElementById('contact_person').disabled = true;
            document.getElementById('sales_representative_container').classList.add('disabled');
            document.getElementById('address').disabled = true;
            // document.getElementById('previous_note').disabled = true;
        }

        function resetFields() {
            document.getElementById('company_name').value = '';
            document.getElementById('phone_no').value = '';
            document.getElementById('email_id').value = '';
            document.getElementById('contact_person').value = '';
            // document.getElementById('sales_representative').value = '';
            document.getElementById('address').value = '';
            // document.getElementById('previous_note').value = '';


            document.getElementById('company_name').disabled = false;
            document.getElementById('phone_no').disabled = false;
            document.getElementById('email_id').disabled = false;
            document.getElementById('contact_person').disabled = false;
            // document.getElementById('sales_representative').disabled = false;
            document.getElementById('address').disabled = false;
            // document.getElementById('previous_note').disabled = false;
        }

        function disabledFields() {
            document.getElementById('company_name').disabled = true;
            document.getElementById('phone_no').disabled = true;
            document.getElementById('email_id').disabled = true;
            document.getElementById('contact_person').disabled = true;
            document.getElementById('sales_representative').disabled = true;
            document.getElementById('address').hidden = true;
            document.getElementById('previous_note_label').hidden = true;
            document.getElementById('address_label').hidden = true;
            // document.getElementById('previous_note').disabled = true;
        }

        // Function to clean company name and remove quotes
        function cleanCompanyName(companyName) {
            // Remove double quotes (")
            companyName = companyName.replace(/"/g, '');
            // Remove single quotes (')
            companyName = companyName.replace(/'/g, '');

            return companyName;
        }
    </script>

    <script>
        document.getElementById('attachment').addEventListener('change', function(event) {
            handleFileChange(event, 'preview');
        });

        function handleFileChange(e, preview) {
            let totalSize = 0;
            let fileTypes = ["jpg", "jpeg", "png", "docx", "doc", "pdf", "xlsx"]; // acceptable file types
            let input = e.target;
            let files = input.files;

            if (files.length > 0) {
                document.getElementById(preview).style.display = 'block';

                // Loop through each file selected
                // Array.from(files).forEach(file => {
                let extension = files[0].name.split(".").pop().toLowerCase();
                let isSuccess = fileTypes.indexOf(extension) > -1;
                let size = files[0].size;
                totalSize += size / 1024 / 1024; // convert size to MB

                if (!isSuccess) {
                    $('#attachment').val('');
                    Swal.fire(
                        'Info',
                        "File format not supported (jpg,jpeg,png,docx,doc,pdf,xlsx only). Kindly select again.",
                        "warning"
                    );
                    return;
                }

                if (totalSize > 10) {
                    $('#attachment').val('');
                    Swal.fire(
                        'Info',
                        "You can upload a maximum of 10 MB files. Kindly select again.",
                        "warning"
                    );
                    return;
                }

                // Create a preview for each file
                let reader = new FileReader();
                reader.onload = (e) => {
                    let fileUrl = URL.createObjectURL(input.files[0]);
                    let previewHtml = '';

                    // If the file is an image
                    if (["jpg", "jpeg", "png"].indexOf(extension) > -1) {
                        previewHtml = `
                            <div class="image-uplodasection">
                                <a href="${fileUrl}" target="_blank">
                                    <i data-feather="image" class="fileuploadicon"></i>
                                </a>
                                <div class="delete-img text-danger">
                                    <i data-feather="x"></i>
                                </div>
                            </div>`;
                    } else {
                        // If the file is a document or other type
                        previewHtml = `
                            <div class="image-uplodasection">
                                <a href="${fileUrl}" target="_blank">
                                    <i data-feather="file-text" class="fileuploadicon"></i>
                                </a>
                                <div class="delete-img text-danger">
                                    <i data-feather="x"></i>
                                </div>
                            </div>`;
                    }

                    // Append the preview HTML to the preview section
                    document.getElementById(preview).insertAdjacentHTML('beforeend', previewHtml);

                    // Initialize Feather icons
                    feather.replace();
                };

                reader.readAsDataURL(files[0]);
                // });
            }
        }

        // Delete preview when the delete icon is clicked
        document.addEventListener('click', function(e) {
            if (e.target && e.target.closest('.delete-img')) {
                // Remove the parent div of the clicked delete icon
                e.target.closest('.image-uplodasection').remove();
                $('#attachment').val('');
            }
        });
    </script>
@endsection
