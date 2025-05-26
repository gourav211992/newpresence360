@extends('layouts.app')

@section('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.18/summernote.min.css" rel="stylesheet">
@endsection

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content todo-application">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <form class="form" role="post-data" method="POST" action="{{ route('notes.store') }}"
            redirect="{{ route('notes.create') }}" autocomplete="off">
            @csrf
            <div class="content-wrapper container-xxl p-0">

                <div class="content-header row">
                    <div class="content-header-left col-md-6  mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Add Notes</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('crm.home') }}">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active"><a href="{{ route('notes.index') }}">My Diary</a>
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
                            <button type="button" class="btn btn-primary btn-sm mb-50 mb-sm-0" data-request="ajax-submit"
                                data-target="[role=post-data]">
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
                                                <input type="radio" name="customer_type" value="Existing"
                                                    class="form-check-input"
                                                    {{ old('customer_type') == 'Existing' ? 'checked' : 'checked' }}>
                                                <label class="form-check-label fw-bolder" for="Existing">Existing</label>
                                            </div>
                                            <div class="form-check form-check-primary mt-25">
                                                <input type="radio" name="customer_type" value="New"
                                                    class="form-check-input"
                                                    {{ old('customer_type') == 'New' ? 'checked' : '' }}>
                                                <label class="form-check-label fw-bolder" for="New">New/
                                                    Prospect</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row align-items-center mb-1 exisitng">
                                    <div class="col-md-3">
                                        <label class="form-label">Customer Name <span class="text-danger">*</span></label>
                                    </div>

                                    <div class="col-md-5">
                                        <select class="form-select select2" name="customer_code" id="customer_code"
                                            onchange="fetchCustomers(this.value)">
                                            <option value="" {{ old('customer_code') == '' ? 'selected' : '' }}>Select
                                            </option>
                                            @foreach ($customers as $customer)
                                                <option value="{{ $customer->customer_code }}"
                                                    {{ old('customer_code') == $customer->customer_code ? 'selected' : '' }}>
                                                    {{ $customer->company_name }}
                                                </option>
                                            @endforeach
                                        </select>
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
                                                <div class="col-md-4 mb-1">
                                                    <label class="form-label">Customer Name <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" class="form-control p-50 text-dark"
                                                        name="company_name" value="{{ old('company_name') }}" disabled />
                                                </div>
                                                <div class="col-md-4 mb-1">
                                                    <label class="form-label">Phone No. <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text"
                                                        class="form-control p-50 text-dark numberonly-v2 disabledField"
                                                        name="phone_no" value="{{ old('phone_no') }}" />
                                                </div>

                                                <div class="col-md-4 mb-1">
                                                    <label class="form-label">Email-ID</label>
                                                    <input type="text"
                                                        class="form-control p-50 text-dark disabledField" name="email_id"
                                                        value="{{ old('email_id') }}" />
                                                </div>

                                                <div class="col-md-4 mb-1">
                                                    <label class="form-label">Contact Person <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text"
                                                        class="form-control p-50 text-dark disabledField"
                                                        name="contact_person" value="{{ old('contact_person') }}" />
                                                </div>

                                                <div class="col-md-4 mb-1" id="newSalesRepresentativeDiv"
                                                    style="display:none">
                                                    <label class="form-label">Sales Representative<span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-select p-50 text-dark select2"
                                                        name="sales_representative_id" id="sales_representative_id">
                                                        <option value="" disabled
                                                            {{ old('sales_representative_id') ? '' : 'selected' }}>
                                                            Select Representative</option>
                                                        @foreach ($salePersons as $rep)
                                                            <option value="{{ $rep->id }}"
                                                                {{ old('sales_representative_id') == $rep->id ? 'selected' : '' }}>
                                                                {{ $rep->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-4 mb-1" id="existingSalesRepresentativeDiv">
                                                    <label class="form-label">Sales Representative</label>
                                                    <input type="text"
                                                        class="form-control p-50 text-dark disabledField"
                                                        name="sales_representative"
                                                        value="{{ old('sales_representative') }}" />
                                                </div>

                                                <div class="col-md-4 mb-1" id="newIndustryDiv" style="display:none">
                                                    <label class="form-label">Industry <span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-select p-50 text-dark select2" name="industry_id"
                                                        id="industry_id">
                                                        <option value="" disabled
                                                            {{ old('industry_id') ? '' : 'selected' }}>
                                                            Select Industry</option>
                                                        @foreach ($industries as $industry)
                                                            <option value="{{ $industry->id }}"
                                                                {{ old('industry_id') == $industry->id ? 'selected' : '' }}>
                                                                {{ $industry->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-4 mb-1" id="existingIndustryDiv">
                                                    <label class="form-label">Industry</label>
                                                    <input type="text"
                                                        class="form-control p-50 text-dark disabledField"
                                                        name="industry_name" value="{{ old('industry_name') }}" />
                                                </div>

                                                <div class="col-md-4 mb-1" id="newProductCategoryDiv"
                                                    style="display:none">
                                                    <label class="form-label">Product Category</label>
                                                    <select class="form-select p-50 text-dark select2"
                                                        name="product_category_id" id="product_category_id">
                                                        <option value="" disabled
                                                            {{ old('product_category_id') ? '' : 'selected' }}>
                                                            Select Category</option>
                                                        @foreach ($categories as $category)
                                                            <option value="{{ $category->id }}"
                                                                {{ old('product_category_id') == $category->id ? 'selected' : '' }}>
                                                                {{ $category->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-4 mb-1" id="existingProductCategoryDiv">
                                                    <label class="form-label">Product Category</label>
                                                    <input type="text"
                                                        class="form-control p-50 text-dark disabledField"
                                                        name="product_category_name"
                                                        value="{{ old('product_category_name') }}" />
                                                </div>

                                                <div class="col-md-4 mb-1" id="newLeadSourceDiv" style="display:none">
                                                    <label class="form-label">Source</label>
                                                    <select class="form-select p-50 text-dark select2"
                                                        name="lead_source_id" id="lead_source_id">
                                                        <option value="" disabled
                                                            {{ old('lead_source_id') ? '' : 'selected' }}>
                                                            Select Source</option>
                                                        @foreach ($leadSources as $leadSource)
                                                            <option value="{{ $leadSource->id }}"
                                                                {{ old('lead_source_id') == $leadSource->id ? 'selected' : '' }}>
                                                                {{ $leadSource->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-4 mb-1" id="existingLeadSourceDiv">
                                                    <label class="form-label">Lead Source</label>
                                                    <input type="text"
                                                        class="form-control p-50 text-dark disabledField"
                                                        name="lead_source_name" value="{{ old('lead_source_name') }}" />
                                                </div>

                                                <div class="col-md-4 mb-1" id="newCurrencyDiv" style="display:none">
                                                    <label class="form-label">Currency<span
                                                            class="text-danger">*</span></label>
                                                    <select class="form-select p-50 text-dark select2" name="currency_id"
                                                        id="currency_id">
                                                        <option value="" disabled
                                                            {{ old('currency_id') ? '' : 'selected' }}>
                                                            Select Currency</option>
                                                        @foreach ($currencies as $currency)
                                                            <option value="{{ $currency->id }}"
                                                                {{ old('currency_id') == $currency->id ? 'selected' : '' }}>
                                                                {{ $currency->short_name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-md-4 mb-1" id="existingCurrencyDiv">
                                                    <label class="form-label">Currency</label>
                                                    <input type="text"
                                                        class="form-control p-50 text-dark disabledField" name="currency"
                                                        value="{{ old('currency') }}" />
                                                </div>

                                                <div class="col-md-4 mb-1">
                                                    <label class="form-label">Annual Sales Figure <span
                                                            class="text-danger">*</span></label>
                                                    <input type="text"
                                                        class="form-control p-50 text-dark numberonly-v2 disabledField"
                                                        name="sales_figure" value="{{ old('sales_figure') }}"
                                                        id="salesFigure" />
                                                </div>

                                                <div class="col-md-4 mb-1 newAddressDiv" style="display: none">
                                                    <label class="form-label">Country</label>
                                                    <select class="form-select select2" name="country_id"
                                                        onchange="dropdown('{{ url('crm/get-states') }}/'+this.value, 'state_id', '');">
                                                        <option value=""
                                                            {{ old('country_id') == '' ? 'selected' : '' }}>Select</option>
                                                        @foreach ($countries as $country)
                                                            <option value="{{ $country->id }}"
                                                                {{ old('country_id') == $country->id ? 'selected' : '' }}>
                                                                {{ $country->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-4 mb-1 newAddressDiv" style="display: none">
                                                    <label class="form-label">State</label>
                                                    <select class="form-select select2" name="state_id" id="state_id"
                                                        onchange="dropdown('{{ url('crm/get-cities') }}/'+this.value, 'city_id', '');">
                                                        <option value=""
                                                            {{ old('state_id') == '' ? 'selected' : '' }}>Select</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-4 mb-1 newAddressDiv" style="display: none">
                                                    <label class="form-label">City</label>
                                                    <select class="form-select select2" name="city_id" id="city_id">
                                                        <option value=""
                                                            {{ old('city_id') == '' ? 'selected' : '' }}>Select</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-4 mb-1 newAddressDiv" style="display: none">
                                                    <label class="form-label">Zipcode</label>
                                                    <input type="text" name="zip_code"
                                                        class="form-control numberonly-v2"
                                                        value="{{ old('zip_code') }}" />
                                                </div>

                                                <div class="col-md-12 mb-1 newAddressDiv" id="newAddressDiv"
                                                    style="display: none">
                                                    <label class="form-label">Address</label>
                                                    <input type="text" id="address" name="address"
                                                        class="form-control" value="{{ old('address') }}" />
                                                </div>

                                                <div class="col-md-12 mb-1" id="existingAddressDiv"
                                                    style="display: none">
                                                    <label class="form-label">Address</label>
                                                    <h6 class="fw-bolder text-dark" id="addressContent"></h6>
                                                </div>

                                                <div class="col-md-12 mb-1" style="display: none"
                                                    id="existingPreviousNoteDiv">
                                                    <label class="form-label">Previous Note </label>
                                                    <h6 class="fw-bolder text-dark" id="previousNoteContent"></h6>
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

                                            <div class="row align-items-center mb-1" id="newStatusDiv">
                                                <div class="col-md-3">
                                                    <label class="form-label">Status <span
                                                            class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-9">
                                                    <select class="form-select select2" name="meeting_status_id">
                                                        <option value=""
                                                            {{ old('meeting_status_id') ? '' : 'selected' }}>
                                                            Select Status</option>
                                                        @foreach ($meetingStatus as $meetstatus)
                                                            <option value="{{ $meetstatus->id }}"
                                                                {{ old('meeting_status_id') == $meetstatus->id ? 'selected' : '' }}>
                                                                {{ $meetstatus->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Meeting Objective <span
                                                            class="text-danger">*</span></label>
                                                </div>

                                                <div class="col-md-9">
                                                    <input type="hidden" name="meeting_objective" />
                                                    <select class="form-select select2" name="meeting_objective_id"
                                                        onchange="meetingObjectiveName()">
                                                        <option value=""
                                                            {{ old('meeting_objective_id') == '' ? 'selected' : '' }}>
                                                            Select Objective</option>
                                                        @foreach ($meetingObjectives as $objective)
                                                            <option value="{{ $objective->id }}"
                                                                {{ old('meeting_objective_id') == $objective->id ? 'selected' : '' }}>
                                                                {{ $objective->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row mb-5">
                                                <div class="col-md-3">
                                                    <label class="form-label">Notes</label>
                                                </div>
                                                <div class="col-md-9">
                                                    <textarea class="summernote" name="description">{{ old('description') }}</textarea>
                                                </div>
                                            </div>

                                            <div class="row mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Attachment </label>
                                                </div>
                                                <div class="col-md-9 attachment-container">
                                                    <input type="file" name="attachment[0]"
                                                        class="form-control attachment-input" id="attachment-0"
                                                        onchange="handleFileChange(event, 0)">
                                                    <div id="preview">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row mb-1">
                                                <div class="col-md-3 lead-contacts">
                                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                        data-bs-target="#leadContacts">Add Contacts</button>
                                                </div>
                                                <div id="render-lead-contacts"></div>
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
    <!-- END: Content-->
@endsection

@section('modals')
    @include('crm.modals.lead-contacts-modal')
@endsection

@section('scripts')
    <script src="{{ asset('app-assets/js/common-script-v2.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            toggleCustomerType();

            function toggleCustomerType() {
                const selectedValue = document.querySelector('input[name="customer_type"]:checked').value;
                customerDropDown(selectedValue);

                if (selectedValue == 'Existing') {
                    disableExistingFields();
                }

                if (selectedValue == 'New') {
                    disableNewFields();
                }

            }

            const radios = document.querySelectorAll('input[name="customer_type"]');
            radios.forEach(function(radio) {
                radio.addEventListener('change', resetFields);
                radio.addEventListener('change', toggleCustomerType);
            });

        });

        function customerDropDown(selectedValue) {
            $.ajax({
                beforeSend: function(xhr) {},
                url: `/crm/notes/get-customers`,
                method: "GET",
                dataType: "json",
                data: {
                    'customer_type': selectedValue
                },
                success: function(response) {
                    const customerCodeSelect = $("#customer_code");
                    customerCodeSelect.empty(); // Clear existing options
                    let options = '<option value="">Select</option>';
                    if (response.status === 200 && response.customers) {
                        const optionList = response.customers;

                        // Add options dynamically
                        $.each(optionList, function(index, value) {
                            const selected = value.customer_code === parseInt(selectedValue) ?
                                "selected" : "";
                            options +=
                                `<option value="${value.customer_code}" ${selected}>${value.company_name}</option>`;
                        });

                        // Append options to the select element
                        customerCodeSelect.append(options);

                        // Destroy any existing Select2 instance before reinitializing
                        if (customerCodeSelect.hasClass('select2-hidden-accessible')) {
                            customerCodeSelect.select2('destroy');
                        }

                        // Reinitialize Select2 if it's a 'New' customer
                        if (selectedValue === 'New') {
                            customerCodeSelect.select2({
                                tags: true,
                                placeholder: 'Search or create a customer',
                                width: '100%'
                            });
                        } else {
                            customerCodeSelect.select2({
                                placeholder: 'Search for an existing customer',
                                width: '100%',
                                tags: false, // Disable tags (creation of new options)
                            });
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error fetching customers:', error);
                }
            });
        }

        function disableNewFields() {
            $('#existingCustomerNameDiv').hide();
            $('#newCustomerNameDiv').show();
            $('#existingSalesRepresentativeDiv').hide();
            $('#newSalesRepresentativeDiv').show();
            $('#existingCurrencyDiv').hide();
            $('#newCurrencyDiv').show();
            $('#existingIndustryDiv').hide();
            $('#newIndustryDiv').show();
            $('#existingAddressDiv').hide();
            $('.newAddressDiv').show();
            $('#existingPreviousNoteDiv').hide();
            $('#newStatusDiv').show();
            $('#existingProductCategoryDiv').hide();
            $('#newProductCategoryDiv').show();
            $('#existingLeadSourceDiv').hide();
            $('#newLeadSourceDiv').show();
            $('.disabledField').prop('disabled', false);
        }

        function disableExistingFields() {
            $('#existingSalesRepresentativeDiv').show();
            $('#existingCurrencyDiv').show();
            $('#newCurrencyDiv').hide();
            $('#newIndustryDiv').hide();
            $('#existingIndustryDiv').show();
            $('#newProductCategoryDiv').hide();
            $('#existingProductCategoryDiv').show();
            $('#newLeadSourceDiv').hide();
            $('#existingLeadSourceDiv').show();
            $('#newSalesRepresentativeDiv').hide();
            $('.newAddressDiv').hide();
            $('#newStatusDiv').hide();
            $('.disabledField').prop('disabled', true);
        }

        function resetFields() {
            $('input[name=company_name]').val('');
            $('input[name=phone_no]').val('');
            $('input[name=contact_person]').val('');
            $('input[name=sales_figure]').val('');
            $('select[name=sales_representative_id]').val(null).trigger('change');
            $('select[name=currency_id]').val(null).trigger('change');
            $('input[name=currency]').val('');
            $('input[name=email_id]').val('');
            $('input[name=address]').val('');
            $('select[name=country_id]').val(null).trigger('change');
            $('select[name=state_id]').val(null).trigger('change');
            $('select[name=city_id]').val(null).trigger('change');
            $('input[name=zip_code]').val('');
            $('select[name=meeting_status_id]').val('');
            $('select[name=meeting_objective_id]').val('');
            $('textarea[name=description]').val('');

            $('.summernote').summernote('reset');
            $('.help-block').text('');

            $("select[name=state_id]").empty("");
            $("select[name=city_id]").empty("");
        }

        function fetchCustomers(customerId) {
            $.ajax({
                url: `/crm/notes/get-customer/${customerId}`,
                type: 'GET',
                success: function(customer) {
                    $('input[name=company_name]').val(customer?.customer?.company_name ?? customerId);

                    if (customer?.diary?.subject) {
                        $('#previousNoteContent').text(customer['diary']['subject']);
                        $('#existingPreviousNoteDiv').show();
                    }

                    if (customer?.customer?.phone) {
                        $('input[name=phone_no]').prop('disabled', true);
                        $('input[name=phone_no]').val(customer?.customer?.phone ?? '');
                    }

                    if (customer?.customer?.contact_person) {
                        $('input[name=contact_person]').prop('disabled', true);
                        $('input[name=contact_person]').val(customer?.customer?.contact_person ?? '');
                    }

                    if (customer?.customer?.email) {
                        $('input[name=email_id]').prop('disabled', true);
                        $('input[name=email_id]').val(customer?.customer?.email ?? '');
                    }

                    if (customer?.diary?.sales_figure) {
                        $('input[name=sales_figure]').prop('disabled', true);
                        $('input[name=sales_figure]').val(customer?.diary?.sales_figure ?? '');
                    }

                    if (customer?.customer?.currency?.short_name) {
                        $('#existingCurrencyDiv').show();
                        $('#newCurrencyDiv').hide();
                        $('input[name=currency]').prop('disabled', true);
                        $('input[name=currency]').val(customer?.customer?.currency?.short_name ?? '');
                    } else {
                        $('#existingCurrencyDiv').hide();
                        $('#newCurrencyDiv').show();
                    }

                    if (customer?.customer?.sales_representative?.name) {
                        $('#existingSalesRepresentativeDiv').show();
                        $('#newSalesRepresentativeDiv').hide();
                        $('input[name=sales_representative]').prop('disabled', true);
                        $('input[name=sales_representative]').val(customer?.customer?.sales_representative
                            ?.name ?? '');
                    } else {
                        $('#existingSalesRepresentativeDiv').hide();
                        $('#newSalesRepresentativeDiv').show();
                    }

                    if (customer?.customer?.product_category?.name) {
                        $('#existingProductCategoryDiv').show();
                        $('#newProductCategoryDiv').hide();
                        $('input[name=product_category_name]').prop('disabled', true);
                        $('input[name=product_category_name]').val(customer?.customer?.product_category?.name ??
                            '');
                    } else {
                        $('#existingProductCategoryDiv').hide();
                        $('#newProductCategoryDiv').show();
                    }

                    if (customer?.customer?.lead_source?.name) {
                        $('#existingLeadSourceDiv').show();
                        $('#newLeadSourceDiv').hide();
                        $('input[name=lead_source_name]').prop('disabled', true);
                        $('input[name=lead_source_name]').val(customer?.customer?.lead_source?.name ?? '');
                    } else {
                        $('#existingLeadSourceDiv').hide();
                        $('#newLeadSourceDiv').show();
                    }

                    if (customer?.diary?.industry?.name) {
                        $('#existingIndustryDiv').show();
                        $('#newIndustryDiv').hide();
                        $('input[name=industry_name]').prop('disabled', true);
                        $('input[name=industry_name]').val(customer?.diary?.industry?.name ?? '');
                    } else {
                        $('#existingLeadSourceDiv').hide();
                        $('#newIndustryDiv').show();
                    }

                    if (customer?.customer?.full_address) {
                        $('#addressContent').text(customer['customer']['full_address']);
                        $('#existingAddressDiv').show();
                        $('.newAddressDiv').hide();
                    }

                    if (customer?.customer?.lead_contacts) {
                        let contacts = customer?.customer?.lead_contacts;
                        fetchLeadContacts(contacts);
                    }
                },


            });
        }

        function meetingObjectiveName() {
            var selectedValue = $('select[name=meeting_objective_id]').find('option:selected').text().trim();
            if (selectedValue == "Select Objective") {
                $('input[name=meeting_objective]').val('');
            } else {
                $('input[name=meeting_objective]').val(selectedValue);
            }

        }


        function fetchLeadContacts(contacts) {
            if (contacts.length > 0) {
                let html = '';
                $.each(contacts, function(index, contact) {
                    html += '<div class="row existing-contact-' + contact.id + '">';
                    html += '<div class="col-3"><div class="mb-1 form-control">' + contact.contact_name +
                        '</div></div>';
                    html += '<div class="col-3"><div class="mb-1 form-control">' + contact.contact_number +
                        '</div></div>';
                    html += '<div class="col-3"><div class="mb-1 form-control">' + contact.contact_email +
                        '</div></div>';
                    html += '<div class="col-3"><a data-url="{{ url('crm/notes/remove-lead-contacts') }}/' +
                        contact.id +
                        '" data-request="remove-lead-contact"><img src="{{ asset('/app-assets/images/icons/trash.svg') }}"></a></div>';
                    html += '</div>';
                });

                $('#render-existing-lead-contacts').html(html);
            }
        }
    </script>
    <script>
        var add_attachment_field;
        var totalSize = 0;

        function handleFileChange(e, index) {
            let totalSize = 0;
            let fileTypes = ["jpg", "jpeg", "png", "docx", "doc", "pdf", "xlsx"]; // acceptable file types
            let input = e.target;
            let file = input.files[0];

            if (file) {
                document.getElementById('preview').style.display = 'block';
                let extension = file.name.split(".").pop().toLowerCase();
                let isSuccess = fileTypes.indexOf(extension) > -1;
                let size = file.size;
                totalSize += size / 1024 / 1024; // convert size to MB

                if (!isSuccess) {
                    e.target.value = "";
                    Swal.fire(
                        'Info',
                        "File format not supported (jpg,jpeg,png,docx,doc,pdf,xlsx only). Kindly select again.",
                        "warning"
                    );
                    return;
                }

                if (totalSize > 10) {
                    e.target.value = "";
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
                    let html = previewFile(file, extension, index);
                    document.getElementById('preview').insertAdjacentHTML('beforeend', html);
                    feather.replace();
                }
                reader.readAsDataURL(file);
            }

            addNewAttachmentField(index + 1);

        }

        function previewFile(file, extension, index) {
            let fileUrl = URL.createObjectURL(file);
            let previewHtml =
                `<div class="image-uplodasection" id="upload-section-${index}"><a href="${fileUrl}" target="_blank">`;
            if (["jpg", "jpeg", "png"].indexOf(extension) > -1) {
                previewHtml += `<i data-feather="image" class="fileuploadicon"></i>`;
            } else {
                previewHtml += `<i data-feather="file-text" class="fileuploadicon"></i>`;
            }
            previewHtml +=
                `</a><div class="delete-img text-danger" data-id="${index}" onclick="removeImage(${index})"><i data-feather="x"></i></div></div>`;

            return previewHtml;
        }

        function addNewAttachmentField(add_attachment_field) {
            var previousField = document.querySelector('#attachment-' + (add_attachment_field - 1));
            if (previousField) {
                previousField.style.display = 'none';
            }

            var newField = document.createElement('input');
            newField.type = 'file';
            newField.name = 'attachment[' + add_attachment_field + ']';
            newField.classList.add('form-control', 'attachment-input');
            newField.id = 'attachment-' + add_attachment_field;
            newField.setAttribute('onchange', 'handleFileChange(event, ' + add_attachment_field + ')');

            var attachmentFieldsContainer = document.querySelector('.attachment-container');
            var nextSibling = previousField.nextElementSibling;
            attachmentFieldsContainer.insertBefore(newField, nextSibling);
        }

        function removeImage(index) {
            let attachmentRow = document.getElementById('upload-section-' + index);

            if (attachmentRow) {
                attachmentRow.remove();
            }

            let attachmentInput = document.querySelector('#attachment-' + index);
            if (attachmentInput) {
                attachmentInput.remove();
            }
        }
    </script>

    <script>
        let lead_row_count = 1;
        let lead_max_fields = 5;
        $(document).on("click", '[data-request="lead-contacts"]', function() {
            /*REMOVING PREVIOUS ALERT AND ERROR CLASS*/
            $(".is-invalid").removeClass("is-invalid");
            $(".help-block").remove();
            var $this = $(this);
            var $target = $this.data("target");
            var $url = $(this).data("action") ? $(this).data("action") : $($target).attr("action");
            var $method = $($target).attr("method") ? $($target).attr("method") : "POST";
            var $data = new FormData($($target)[0]);
            $this.prop('disabled', true);
            $.ajax({
                url: $url,
                data: $data,
                cache: false,
                type: $method,
                dataType: "JSON",
                contentType: false,
                processData: false,
                beforeSend: function() {
                    $('#loaderDiv').show();
                },
                success: function($response) {
                    $('#loaderDiv').hide();
                    if ($response.status === 200) {
                        let html = '';
                        $.each($response.data, function(index, contact) {
                            html += '<div class="contact-card" id="contact-card-' + index +
                                '">';
                            html += '<input type="hidden" name="leads[' + index +
                                '][contact_name]" value="' + contact.contact_name + '"/>';
                            html += '<input type="hidden" name="leads[' + index +
                                '][contact_email]" value="' + contact.contact_email + '"/>';
                            html += '<input type="hidden" name="leads[' + index +
                                '][contact_number]" value="' + contact.contact_number + '"/>';
                            html += '</div>';
                        });

                        $('#render-lead-contacts').html(html);
                        $('#leadContacts').modal('hide');
                        $this.prop('disabled', false);
                    }
                },
                error: function($response) {
                    $('#loaderDiv').hide();
                    $this.prop('disabled', false);
                    if ($response.status === 422) {
                        console.log($response.responseJSON.errors);
                        if (
                            Object.size($response.responseJSON) > 0 &&
                            Object.size($response.responseJSON.errors) > 0
                        ) {
                            show_validation_error($response.responseJSON.errors);
                        }
                    } else {
                        Swal.fire(
                            "Info!",
                            $response.responseJSON.message,
                            "warning"
                        );
                        setTimeout(function() {}, 1200);
                    }
                },
            });
        });

        function addMoreLeadContacts() {
            $(".help-block").remove();

            var html = $(".add-more-row").first().clone();
            let currentRowCount = $(".add-more-row").length;
            if (currentRowCount < lead_max_fields) {
                $(html).find("#lead_contact_name").attr('name', "data[" + lead_row_count + "][contact_name]");
                $(html).find("#lead_contact_name").val('');
                $(html).find("#lead_contact_number").attr('name', "data[" + lead_row_count + "][contact_number]");
                $(html).find("#lead_contact_number").val('');
                $(html).find("#lead_contact_email").attr('name', "data[" + lead_row_count + "][contact_email]");
                $(html).find("#lead_contact_email").val('');

                var uniqueClass = 'lead-contact-row-' + lead_row_count;
                $(html).addClass(uniqueClass);

                var trashUrl = $("#add-more-contact").data("trash-url");
                $(html).find("#change-to-remove").html('<a id="remove-lead-contact"><img src="' + trashUrl +
                    '" onclick="removeLeadContact(event, ' + lead_row_count + ')"></a>');
                $(".add-more-row").last().after(html);
                lead_row_count++;
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Alert! ',
                    text: 'You can add only ' + max_fields + ' contacts'
                })
            }

        }

        function removeLeadContact(event, rowCount) {
            var rowClass = '.lead-contact-row-' + rowCount; // Create the class based on the rowCount
            var contactCard = '#contact-card-' + rowCount; // Create the class based on the rowCount
            $(rowClass).remove(); // Remove the specific row with that class
            $(contactCard).remove(); // Remove the specific row with that class
        }

        $(document).on("click", '[data-request="remove-lead-contact"]', function() {
            var $this = $(this);
            var $url = $this.attr("data-url");
            Swal.fire({
                title: "Alert! ",
                text: "Are you sure you want to delete contacts?",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, please!",
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        url: $url,
                        type: "DELETE",
                        beforeSend: function() {
                            $('#loaderDiv').show();
                        },
                        success: function(data) {
                            if (data.status == 200) {
                                $('#loaderDiv').hide();
                                let id = data.data.id;
                                Swal.fire("Success!", data.message, "success");

                                $('.existing-contact-' + id).remove();
                            }
                        },
                        error: function(data) {
                            $('#loaderDiv').hide();
                            Swal.fire(
                                "Info!",
                                data.responseJSON.message,
                                "warning"
                            );
                            setTimeout(function() {}, 1200);
                        },
                    });
                }
            });
        });
    </script>
@endsection
