@extends('layouts.crm')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content todo-application">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <form method="POST" id="notes_form" action="{{ route('notes.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="content-wrapper container-xxl p-0">

                <!-- Content Header -->
                <div class="content-header row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Add Notes</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ url('/crm/home') }}">Home</a></li>
                                        <li class="breadcrumb-item active">My Diary</li>
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

                <!-- Success and Error Messages -->
                @if (session('success'))
                    <div class="alert alert-success" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger" role="alert">
                        {{ session('error') }}
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

                <!-- Content Body -->
                <div class="content-body dasboardnewbody">
                    <div class="card">
                        <div class="card-body customernewsection-form">
                            <div class="row">
                                <div class="col-md-12">
                                    <div
                                        class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                        <div>
                                            <h4 class="card-title text-theme">Basic Information</h4>
                                            <p class="card-text">Fill the details</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="row align-items-center mb-1">
                                        <div class="col-md-3">
                                            <label class="form-label">Customer Type</label>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="demo-inline-spacing">
                                                <div class="form-check form-check-primary mt-25">
                                                    <input type="radio" id="customer_type1" name="customer_type"
                                                        value="New" class="form-check-input"
                                                        {{ old('customer_type', 'New') == 'New' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bolder"
                                                        for="customer_type1">New</label>
                                                </div>
                                                <div class="form-check form-check-primary mt-25">
                                                    <input type="radio" id="customer_type2" name="customer_type"
                                                        value="Existing" class="form-check-input"
                                                        {{ old('customer_type') == 'Existing' ? 'checked' : '' }}>
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
                                            <label class="form-label">Customer Name <span
                                                    class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-md-5" id="customer_name_container">
                                        </div>
                                    </div>
                                    <div class="row align-items-center mb-1">
                                        <div class="col-md-3">
                                            <label class="form-label">Contact Person<span
                                                    class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-md-5 action-button">
                                            <input type="text" class="form-control" name="contact_person"
                                                value="{{ old('contact_person') }}">
                                            @if ($errors->has('contact_person'))
                                                <span class="text-danger">{{ $errors->first('contact_person') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row align-items-center mb-1">
                                        <div class="col-md-3">
                                            <label class="form-label">Email-ID<span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-md-5 action-button">
                                            <input type="text" class="form-control" name="email"
                                                value="{{ old('email') }}">
                                            @if ($errors->has('email'))
                                                <span class="text-danger">{{ $errors->first('email') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row align-items-center mb-1">
                                        <div class="col-md-3">
                                            <label class="form-label">Location <span id="location_required"
                                                    class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-md-5 action-button" id="location_container">
                                        </div>
                                    </div>
                                    <div class="row align-items-center mb-1">
                                        <div class="col-md-3">
                                            <label class="form-label">Tag People</label>
                                        </div>
                                        <div class="col-md-5">
                                            <select id="tag_people_id" name="tag_people_id[]" class="form-select select2"
                                                multiple>
                                                @foreach ($tagPeoples as $tagPeople)
                                                    <option value="{{ $tagPeople->id }}"
                                                        {{ old('tag_people_id') && in_array($tagPeople->id, old('tag_people_id', [])) ? 'selected' : '' }}>
                                                        {{ $tagPeople->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @if ($errors->has('tag_people_id'))
                                                <span class="text-danger">{{ $errors->first('tag_people_id') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row align-items-center mb-1">
                                        <div class="col-md-3">
                                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-md-5 action-button">
                                            <input type="text" class="form-control" name="subject"
                                                value="{{ old('subject') }}">
                                            @if ($errors->has('subject'))
                                                <span class="text-danger">{{ $errors->first('subject') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row align-items-center mb-1">
                                        <div class="col-md-3">
                                            <label class="form-label">Attachment</label>
                                        </div>
                                        <div class="col-md-5 action-button">
                                            <input type="file" name="attachment" class="form-control">
                                            @if ($errors->has('attachment'))
                                                <span class="text-danger">{{ $errors->first('attachment') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="row mb-4">
                                        <div class="col-md-3">
                                            <label class="form-label">Description</label>
                                        </div>
                                        <div class="col-md-5 action-button">
                                            <textarea id="task-desc" name="description" class="form-control" rows="5"
                                                placeholder="Write Your Description"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const customerTypeRadios = document.querySelectorAll('input[name="customer_type"]');
            const customerNameContainer = document.getElementById('customer_name_container');
            const locationContainer = document.getElementById('location_container');
            const locationRequired = document.getElementById('location_required');

            // Function to toggle Customer Name and Location fields
            function toggleCustomerFields() {
                const selectedType = document.querySelector('input[name="customer_type"]:checked').value;

                if (selectedType === 'New') {
                    customerNameContainer.innerHTML = `
                        <input type="text" class="form-control" name="customer_name" value="{{ old('customer_name') }}">
                    `;
                    locationContainer.innerHTML = `
                        <input type="text" class="form-control" name="location" value="{{ old('location') }}">
                    `;
                    locationRequired.style.display = 'none';
                } else if (selectedType === 'Existing') {
                    customerNameContainer.innerHTML = `
                        <select id="customer_id" name="customer_code" class="form-select select2" onchange="fetchLocations(this.value)">
                            <option value="" disabled {{ old('customer_code') ? '' : 'selected' }}>Select Customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->customer_code }}"
                                    {{ old('customer_code') == $customer->customer_code ? 'selected' : '' }}>
                                    {{ str_replace("`", '', $customer->company_name) }}
                                </option>
                            @endforeach
                        </select>
                    `;
                    locationContainer.innerHTML = `
                        <select id="location" name="location" class="form-select">
                            <option value="" disabled>Select Location</option>
                        </select>
                    `;
                    locationRequired.style.display = 'inline';
                }

                $('.select2').select2();
            }
            toggleCustomerFields();
            customerTypeRadios.forEach(radio => {
                radio.addEventListener('change', toggleCustomerFields);
            });
        });

        function fetchLocations(customerId) {
            $.ajax({
                url: `/crm/get-locations/${customerId}`,
                type: 'GET',
                success: function(data) {
                    let options = '<option value="" selected disabled>Select Location</option>';
                    data.forEach(location => {
                        options +=
                            `<option value="${location.display_address}">${location.display_address}</option>`;
                    });
                    document.getElementById('location').innerHTML = options;
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching locations:', error);
                }
            });
        }
    </script>
@endsection
