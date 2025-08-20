@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">New Series</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('book') }}">Series</a></li>
                                        <li class="breadcrumb-item active">Add New</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <a href="javascript: history.go(-1)" class="btn btn-secondary btn-sm"><i
                                    data-feather="arrow-left-circle"></i> Back</a>
                            <button type="submit" form="book-form" class="btn btn-primary btn-sm"><i
                                    data-feather="check-circle"></i>Submit</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body customernewsection-form">
                                    <form class="ajax-input-form" data-redirect="{{ route('book') }}" id="book-form" method="POST" action="{{ route('books.store') }}"
                                        >
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="newheader border-bottom mb-2 pb-25">
                                                    <h4 class="card-title text-theme">Basic Information</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                            </div>

                                            <div class="col-md-9">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Service<span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" id = "org_service_dropdown" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input" autocomplete="off" >
                                                        <input type="hidden" id = "org_service_id_input" name="org_service_id">
                                                        <input type="hidden" id = "org_service_alias_input">

                                                    </div>
                                                    <!-- <div class="col-md-3">
                                                        <a href="{{ route('bookType.create') }}"
                                                            class="voucehrinvocetxt mt-0">Add Series
                                                            Type</a>
                                                    </div> -->
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Series Code <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input id = "book_code_input" oninput="allowOnlyUppercase(event)" type="text" name="book_code" class="form-control"
                                                              />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Series Name <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="book_name" class="form-control"
                                                              />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">

                                                    <div class="col-md-3">
                                                        <label class="form-label">Manual Entry Allowed?<span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-0 me-1">
                                                                <input type="radio" id="manual-entry-yes" name="manual_entry" class="form-check-input" value="yes" checked oninput = "setManualEntryOption(this);">
                                                                <label class="form-check-label fw-bolder" for="manual-entry-yes">Yes</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-0 me-0">
                                                                <input type="radio" id="manual-entry-no" name="manual_entry" class="form-check-input" value="no" oninput = "setManualEntryOption(this);">
                                                                <label class="form-check-label fw-bolder" for="manual-entry-no">No</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-3 border-start">
                                                <div class="row align-items-center mb-2">
                                                    <div class="col-md-12">
                                                        <label
                                                            class="form-label text-primary"><strong>Status</strong></label>
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="status-active" name="status"
                                                                    value="Active" class="form-check-input" checked />
                                                                <label class="form-check-label fw-bolder"
                                                                    for="status-active">Active</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="status-inactive" name="status"
                                                                    value="Inactive" class="form-check-input" />
                                                                <label class="form-check-label fw-bolder"
                                                                    for="status-inactive">Inactive</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-md-12" id = "manual_entry_details">
                                                <div class="mt-2">
                                                    <div class="step-custhomapp bg-light">
                                                        <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                                                            <li class="nav-item transaction_service_tab">
                                                                <a class="nav-link active" data-bs-toggle="tab"
                                                                    href="#Pattern">Numbering Pattern</a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" data-bs-toggle="tab"
                                                                    href="#Approval">Approval</a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" data-bs-toggle="tab"
                                                                    href="#Amendment">Amendment</a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" data-bs-toggle="tab"
                                                                    href="#Configuration">Common Parameters</a>
                                                            </li>
                                                            <li class="nav-item transaction_service_tab" id = "gl_param_tab_header" style = "display:none;">
                                                                <a class="nav-link" data-bs-toggle="tab"
                                                                    href="#gl_params">Financial Parameters</a>
                                                            </li>
                                                            <li class="nav-item" id = "dynamic_field_tab_header">
                                                                <a class="nav-link" data-bs-toggle="tab"
                                                                    href="#dynamic_field_section">Dynamic Fields</a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                    <div class="tab-content ">
                                                        <div class="tab-pane active transaction_service_tab" id="Pattern">
                                                            <div class="table-responsive-md">
                                                                <table
                                                                    class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                    <thead>
                                                                        <tr>
                                                                            <th width = "20px">#</th>
                                                                            <th width="300px"> Company Name<span
                                                                                    class="text-danger">*</span></th>
                                                                            <th width="300px">Unit Name<span
                                                                                    class="text-danger">*</span></th>
                                                                            <th width="150px">Numbering Type<span
                                                                                    class="text-danger">*</span></th>
                                                                            <th width="150px">Reset Pattern</th>
                                                                            <th >Prefix</th>
                                                                            <th >Suffix</th>
                                                                            <th >Starting No.</th>

                                                                            <th class = "center-align-content" width = "20px">Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="item-details-body">
                                                                        <tr>
                                                                            <td class="serial-number">1</td>
                                                                            <td>
                                                                                <select
                                                                                    class="form-select mw-100 select2 companySelect"
                                                                                    data-id="1" name="company_id[]"
                                                                                    id="company_id1"  >
                                                                                    <option disabled selected
                                                                                        value="">Select
                                                                                    </option>

                                                                                    @foreach ($companies as $company)
                                                                                        <option
                                                                                            value="{{ $company->id }}">
                                                                                            {{ $company->name }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <select
                                                                                    class="form-select mw-100 select2 organizations"
                                                                                    name="organization_id[]"
                                                                                    id="organization_id1"  >
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                            <div class="position-relative">
                                                                                <select class="form-select mw-100"
                                                                                    name="series_numbering[]" oninput = "onSeriesNumberingChange(this, 0)" >
                                                                                    <option disabled selected
                                                                                        value="">Select
                                                                                    </option>
                                                                                    <option>Auto</option>
                                                                                    <option>Manually</option>
                                                                                </select>
                                                                            </div>
                                                                            </td>
                                                                            <td>
                                                                                <div class="position-relative">
                                                                                <select class="form-select mw-100"
                                                                                    name="reset_pattern[]" id = "reset_pattern_0" oninput = "resetPatternChange(this, 0)" >
                                                                                    <option disabled selected
                                                                                        value="">Select</option>
                                                                                       <option>Never</option>
                                                                                    <option>Yearly</option>
                                                                                    <option>Quarterly</option>
                                                                                    <option>Monthly</option>
                                                                                </select>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text"
                                                                                    id = "prefix_0"
                                                                                    class="form-control mw-100"
                                                                                    name="prefix[]"
                                                                                    oninput="allowOnlyUppercase(event)"
                                                                                    >
                                                                                </td>
                                                                                    <td><input type="text"
                                                                                    id = "suffix_0"
                                                                                    class="form-control mw-100"
                                                                                    name="suffix[]"
                                                                                    oninput="allowOnlyUppercase(event)"
                                                                                    ></td>
                                                                            <td>
                                                                                <input type="number" id = "starting_no_0"
                                                                                    class="form-control mw-100"
                                                                                    name="starting_no[]" value = "1">
                                                                            </td>

                                                                            <td class = "center-align-content"><a href="#"
                                                                                    class="text-primary add_number_pattern"><i
                                                                                        data-feather="plus-square"></i></a>
                                                                            </td>
                                                                        </tr>

                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane" id="Approval">
                                                            <div class="table-responsive-md">
                                                                <table
                                                                    class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>#</th>
                                                                            <th width="225px">Company Name</th>
                                                                            <th width="225px">Unit Name</th>
                                                                            <th width="500px">Approver Name</th>
                                                                            <th width="150px" class = "min-value">Min Value</th>
                                                                            <!-- <th width="100px">Max Value</th> -->
                                                                            <th class = "center-align-content">Required by</th>
                                                                            <th class = "center-align-content">Action</th>
                                                                            <th></th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="workflow-body">
                                                                        <tr class="approvlevelflow level-row"
                                                                            data-level="1">
                                                                            <td class="levelNumber">1</td>
                                                                            <td colspan="6">
                                                                                <h6 class="mb-0 fw-bolder text-dark levelText">Level
                                                                                    1</h6>
                                                                            </td>
                                                                            <td class = "center-align-content"><a href="#"
                                                                                    class="text-primary add-level-row"><i
                                                                                        data-feather="plus-square"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <td>&nbsp; <input class="d-none"
                                                                                    type="text" value="1"
                                                                                    name="level[]"></td>
                                                                            <td>
                                                                                <select
                                                                                    class="form-select mw-100 select2 levelCompanySelect"
                                                                                    data-id="1"
                                                                                    id = "company_select_1"
                                                                                    name="level_company_id[]">
                                                                                    <option disabled selected
                                                                                        value="">Select Company
                                                                                    </option>
                                                                                    @foreach ($companies as $company)
                                                                                        <option
                                                                                            value="{{ $company->id }}">
                                                                                            {{ $company->name }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <select
                                                                                    class="form-select mw-100 select2 level_organizations"
                                                                                    data-id="1"
                                                                                    user-select-id = "0_1"
                                                                                    name="level_organization_id[]"
                                                                                    id="level_organization_id1">
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <select
                                                                                    class="form-control mw-100 select2 userSelect"
                                                                                    data-id="1" name="user[0][]"
                                                                                    id = "user_select_0_1"
                                                                                    multiple
                                                                                    >
                                                                                    <option disabled value="">Select
                                                                                        Approver
                                                                                    </option>
                                                                                    @foreach ($people as $user)
                                                                                        <option
                                                                                            value="{{ $user->id }}|{{ $user->type }}">
                                                                                            {{ $user->name }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" value="0"
                                                                                    name="min_value[]" data-id="1"
                                                                                    class="form-control mw-100 min-value">
                                                                            </td>
                                                                            <!-- <td>
                                                                                <input type="text" value="100000"
                                                                                    name="max_value[]" data-id="1"
                                                                                    class="form-control mw-100 max-value">
                                                                            </td> -->
                                                                            <td class = "center-align-content">
                                                                                <div class="customernewsection-form">
                                                                                    <div class="demo-inline-spacing">
                                                                                        <!-- Ensure the 'name' attribute is consistent for radio buttons in the same group -->
                                                                                        <input type="hidden"
                                                                                            name="rights[]"
                                                                                            class="rights-value"
                                                                                            value="all">
                                                                                        <div
                                                                                            class="form-check form-check-primary mt-0 me-1">
                                                                                            <input type="radio"
                                                                                                id="anyone-1"
                                                                                                name="rights[0]"
                                                                                                class="form-check-input"
                                                                                                value="anyone">
                                                                                            <label
                                                                                                class="form-check-label fw-bolder"
                                                                                                for="anyone-1">Any
                                                                                                One</label>
                                                                                        </div>
                                                                                        <div
                                                                                            class="form-check form-check-primary mt-0 me-0">
                                                                                            <input type="radio"
                                                                                                id="all-1"
                                                                                                name="rights[0]"
                                                                                                class="form-check-input"
                                                                                                value="all" checked>
                                                                                            <label
                                                                                                class="form-check-label fw-bolder"
                                                                                                for="all-1">All</label>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td class = "center-align-content"><a href="#"
                                                                                    class="text-primary add-row"><i
                                                                                        data-feather="plus-square"></i></a>
                                                                            </td>
                                                                        </tr>
                                                                        <!-- More rows can be added dynamically -->
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane" id="Amendment">
                                                            <div class="table-responsive-md">
                                                                <table
                                                                    class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                    <thead>
                                                                        <tr>
                                                                            <th >#</th>
                                                                            <th width="225px"> Company Name<span
                                                                                    class="text-danger">*</span></th>
                                                                            <th width="225px">Unit Name<span class="text-danger">*</span>
                                                                            </th>
                                                                            <th width = "500px">Authorized By<span
                                                                                    class="text-danger">*</span></th>
                                                                            <th width = "150px" class="min-value" >Min Value</th>
                                                                            <th class="center-align-content">Approval Required</th>
                                                                            <!-- <th width="100px">Max Value</th> -->
                                                                            <th class="center-align-content">Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="tableBody">
                                                                        <tr>
                                                                            <td class="amend-serial">1</td>
                                                                            <td>
                                                                            <select
                                                                                    class="form-select mw-100 select2 AmendmentCompanySelect"
                                                                                    data-id="1"
                                                                                    name="amendment_company_id[]">
                                                                                    <option disabled selected
                                                                                        value="">Select Company
                                                                                    </option>
                                                                                    @foreach ($companies as $company)
                                                                                        <option
                                                                                            value="{{ $company->id }}">
                                                                                            {{ $company->name }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </td>
                                                                            <td>

                                                                                <select
                                                                                    class="form-select mw-100 select2 amendment_organizations"
                                                                                    name="amendment_organization_id[]"
                                                                                    id="amendment_organization_id1">
                                                                                </select>

                                                                            </td>
                                                                            <td>
                                                                            <select
                                                                                id = "amend_users_1"
                                                                                    class="form-select mw-100 select2 amendmentUserSelect"
                                                                                    data-id="1" name="amendment_user[0][]"
                                                                                    multiple>
                                                                                    <option disabled value="">Select
                                                                                        Approver
                                                                                    </option>
                                                                                    @foreach ($people as $user)
                                                                                        <option
                                                                                            value="{{ $user->id }}|{{ $user->type }}">
                                                                                            {{ $user->name }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text"  value="0" name="amendment_min[]"
                                                                                    class="form-control mw-100 min-value">
                                                                            </td>
                                                                            <td class = "center-align-content">
                                                                                <div class="customernewsection-form">
                                                                                    <div class="demo-inline-spacing">
                                                                                        <input type="hidden" name="approval_req[]" class="rights-value" value="no">
                                                                                        <div class="form-check form-check-primary mt-0 me-1">
                                                                                            <input type="radio" name="approval_req[0]" class="form-check-input" value="yes" id = "app_req_yes_1">
                                                                                            <label class="form-check-label fw-bolder" for="app_req_yes_1">Yes</label>
                                                                                        </div>
                                                                                        <div class="form-check form-check-primary mt-0 me-0">
                                                                                            <input type="radio" id="app_req_no_1" name="approval_req[0]" class="form-check-input" value="no" checked>
                                                                                            <label class="form-check-label fw-bolder" for="app_req_no_1">No</label>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td class="center-align-content"><a href="#" class="text-primary amendment_plus"><i
                                                                                        data-feather="plus-square"></i></a>
                                                                            </td>

                                                                        </tr>

                                                                    </tbody>


                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane" id="Configuration">

                                                        </div>
                                                        <div class="tab-pane transaction_service_tab" style = "display:none;" id="gl_params">

                                                        </div>
                                                        <div class="tab-pane" id="dynamic_field_section">
                                                            <div class='row align-items-center mb-1'>
                                                                <div class='col-md-2'>
                                                                    <label class='form-label'>Dynamic Fields</label>
                                                                </div>
                                                                <div class='col-md-5'>
                                                                    <select
                                                                        class='form-select mw-100 select2'
                                                                        multiple
                                                                        name = 'dynamic_fields[]'
                                                                        id = 'dynamic_fields_input'
                                                                        oninput = "getDynamicFields(this);"
                                                                        >
                                                                        @foreach ($dynamicFields as $dynamicField)
                                                                            <option value = "{{$dynamicField -> id}}">{{$dynamicField -> name}}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class='row align-items-center mb-1' id = "dynamic_fields_value"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        var companies = {!! json_encode($companies) !!};
        const hasDuplicates = (arr) => arr.length !== new Set(arr).size;
        var users={!! json_encode($people) !!};

        function check_dup_org() {
            var orgs = [];
            $('.organizations').each(function() {
                var selectedOption = $(this).find(":selected");
                if (null != selectedOption.val() && selectedOption.val() != "") {
                    orgs.push($(this).val());
                }
            });

            if (hasDuplicates(orgs)) {
                alert('Duplicate entries for same location are not allowed!!');
                return false;
            } else {
                return true;
            }
        }

        function onReferenceServiceChange(element)
        {
            if (element.id != 'reference_from_service') {
                return;
            }
            const selectedServiceAlias  = $("#org_service_alias_input").val();
            const selectedServices = element.selectedOptions;
            var selectedIds = [];
            for (let index = 0; index < selectedServices.length; index++) {
                selectedIds.push(selectedServices[index].value);
            }
            const seriesElement = document.getElementById('reference_series_input');

            var innerSeriesHTMLVal = '';
            var selectedBooks = [];
            var selectedBookElements = document.getElementById('reference_series_input').selectedOptions;
            for (let index = 0; index < selectedBookElements.length; index++) {
                selectedBooks.push(selectedBookElements[index].value);
            }
            $.ajax({
                url: "{{route('book.reference-series.get')}}",
                method: 'GET',
                dataType: 'json',
                data: {
                    service_ids: selectedIds,
                    service_id: $("#org_service_id_input").val(),
                    selected_ids : selectedBooks
                },
                success: function(data) {
                    if (data.data && data.data.length > 0) {
                        console.log(data.data, "RES");
                        $.map(data.data, function(item) {
                            if (item.disabled) {
                                innerSeriesHTMLVal += `<option value='${item.value}' ${item.selected ? 'selected' : ''} disabled>${item.label}</option>`
                            } else {
                                innerSeriesHTMLVal += `<option value='${item.value}' ${item.selected ? 'selected' : ''}>${item.label}</option>`
                            }
                        });
                        console.log(innerSeriesHTMLVal);
                        if (seriesElement) {
                            seriesElement.innerHTML = innerSeriesHTMLVal;
                        }
                    } else {
                        if (seriesElement) {
                            seriesElement.innerHTML = ``;
                        }
                    }
                },
                error: function(xhr) {
                    if (seriesElement) {
                        seriesElement.innerHTML = ``;
                    }
                    console.error('Error fetching org services data:', xhr.responseText);
                }
            });

        }

        function disableSelectedElementsForBooks(element)
        {
            const elementId = element.id;
            const selectedLabels = [];
            $("#"+elementId).find('option:selected').each(function() {
                selectedLabels.push($(this).text());
            });

            // Enable all options first
            $('#' + elementId + ' option').prop('disabled', false);

            if ($("#org_service_alias_input").val() == "{{\App\Helpers\ConstantHelper::DELIVERY_CHALLAN_SERVICE_ALIAS}}")
            {
                if (selectedLabels.includes("SO")) {
                    $('#'+ elementId +' option').filter(function() {
                        return $(this).text() == "SI";
                    }).prop('disabled', true);
                } else if (selectedLabels.includes("SI")) {
                    $('#'+ elementId +' option').filter(function() {
                        return $(this).text() == "SO";
                    }).prop('disabled', true);
                }
            }
            // Additional logic to disable selected options themselves
            $("#" + elementId).find('option:selected').each(function() {
                $(this).prop('disabled', true);
            });

        }

        function onreferenceServiceChangeCheck(element)
        {
            if (element) {
                disableSelectedElementsForBooks(element);
                onReferenceServiceChange(element);
            }
        }

        $(document).on('input', '.referenceService', function() {
            onreferenceServiceChangeCheck(this);
        });

        $(function() {
            $('#book-form').on('submit', function(e) {
                // Enable only selected options for each select element
                $('.userSelect').find('option:selected').prop('disabled', false);
                $('.levelCompanySelect').prop('disabled', false);
                $('.amendmentUserSelect').find('option:selected').prop('disabled', false);
                $('.bookSelect').find('option:selected').prop('disabled', false);
                $('.referenceService').find('option:selected').prop('disabled', false);
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Add new item row
            document.querySelector('.add_number_pattern').addEventListener('click', function(e) {
                e.preventDefault();
                let rowCount = document.querySelectorAll('#item-details-body tr').length;
                rowCount++
                let newRow = `<tr>
                            <td class="serial-number"></td>
                            <td>
                            <div class="position-relative">
                                <select class="form-select mw-100 select2 companySelect" data-id="${rowCount}"
                                    name="company_id[]" id="company_id${rowCount}"  >
                                    <option disabled selected value="">Select Company</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            </td>
                            <td>
                            <div class="position-relative">
                                <select class="form-select mw-100 select2 organizations"
                                    name="organization_id[]" id="organization_id${rowCount}"  >
                                </select>
                            </div>
                            </td>
                            <td>
                                <div class="position-relative">
                                <select class="form-select mw-100" name="series_numbering[]" oninput = "onSeriesNumberingChange(this,${rowCount})"  >
                                    <option disabled selected value="">Select</option>
                                    <option>Auto</option>
                                    <option>Manually</option>
                                </select>
                                </div>
                            </td>
                            <td>
                                <div class="position-relative">
                                <select class="form-select mw-100" name="reset_pattern[]" id = "reset_pattern_${rowCount}" oninput = "resetPatternChange(this, ${rowCount})"  >
                                    <option disabled selected value="">Select</option>
                                    <option>Yearly</option>
                                    <option>Quarterly</option>
                                    <option>Monthly</option>
                                    <option>Never</option>
                                </select>
                                </div>
                            </td>
                            <td><input type="text"
                                    class="form-control mw-100" id = "prefix_${rowCount}" name="prefix[]" oninput="allowOnlyUppercase(event)"></td>
                                    <td><input type="text"
                                    class="form-control mw-100" id = "suffix_${rowCount}" name="suffix[]" oninput="allowOnlyUppercase(event)"></td>
                            <td>
                                <input type="number" id = "starting_no_${rowCount}" value = "1"
                                    class="form-control mw-100" name="starting_no[]">
                            </td>

                            <td class = "center-align-content"><a href="#" class="text-danger remove-item"><i
                                        data-feather="trash-2"></i></a></td>
                        </tr>`;
                document.querySelector('#item-details-body').insertAdjacentHTML('beforeend', newRow);

                feather.replace({
                    width: 14,
                    height: 14
                });

                $('.select2').select2();
                updateSerialNumbers();
            });
            initializeDynamicFieldDropdown();
        });

        function updateSerialNumbers() {
            $('#item-details-body tr:visible').each(function (index) {
                $(this).find('.serial-number').text(index + 1);
            });
        }

        $(document).on('click', '.remove-item', function () {
            const $row = $(this).closest('tr');
            const companyId = $row.find('.companySelect').val();
            resetHiddenRow($row)
            updateSerialNumbers();
            updateDisabledEnabledUserSelect('Pattern', companyId)
        });

        function resetHiddenRow($row) {
            $row.show();
            $row.find('input, select, textarea').prop('disabled', false);
            $row.find('input, select, textarea').each(function () {
                if (this.tagName === 'SELECT') {
                    $(this).val('').trigger('change');
                } else if ($(this).attr('type') === 'radio' || $(this).attr('type') === 'checkbox') {
                    $(this).val('').prop('checked', false);
                }  else {
                    $(this).val('');
                }
            });
            $row.hide();
        }


        $(document).on('change', '.companySelect', function() {
            company_id = $(this).val() || [];
            updateDisabledEnabledUserSelect('Pattern', company_id)
        });

        $(document).on('change', '.organizations', function() {
            $(document).ready(function () {
                $('.companySelect').each(function () {
                    const companyId = $(this).val();
                    if (companyId) {
                        updateDisabledEnabledUserSelect('Pattern', companyId);
                    }
                });
            });
        });

        function updateDisabledEnabledUserSelect(type, companyId = null) {
            if (type === 'Amendment') {
                $('.amendmentUserSelect option').prop('disabled', false);
                const orgUserMap = {};

                $('.amendmentUserSelect').each(function () {
                    const currentRow = $(this).closest('tr');
                    const orgId = currentRow.find('.amendment_organizations').val();
                    const selectedValues = $(this).val() || [];

                    if (orgId) {
                        if (!orgUserMap[orgId]) {
                            orgUserMap[orgId] = new Set();
                        }
                        selectedValues.forEach(val => orgUserMap[orgId].add(val));
                    }
                });

                $('.amendmentUserSelect').each(function () {
                    const currentRow = $(this).closest('tr');
                    const orgId = currentRow.find('.amendment_organizations').val();
                    const currentSelected = $(this).val() || [];

                    if (orgId && orgUserMap[orgId]) {
                        const selectedSet = orgUserMap[orgId];
                        selectedSet.forEach(val => {
                            if (!currentSelected.includes(val)) {
                                $(this).find('option[value="' + val + '"]').prop('disabled', true);
                            }
                        });
                    }
                });
            } else if(type === 'Approval') {
                $('.userSelect option').prop('disabled', false);
                const orgUserMap = {};

                $('.userSelect').each(function () {
                    const currentRow = $(this).closest('tr');
                    const orgId = currentRow.find('.level_organizations').val();
                    const selectedValues = $(this).val() || [];

                    if (orgId) {
                        if (!orgUserMap[orgId]) {
                            orgUserMap[orgId] = new Set();
                        }
                        selectedValues.forEach(val => orgUserMap[orgId].add(val));
                    }
                });

                $('.userSelect').each(function () {
                    const currentRow = $(this).closest('tr');
                    const orgId = currentRow.find('.level_organizations').val();
                    const currentSelected = $(this).val() || [];

                    if (orgId && orgUserMap[orgId]) {
                        const selectedSet = orgUserMap[orgId];
                        selectedSet.forEach(val => {
                            if (!currentSelected.includes(val)) {
                                $(this).find('option[value="' + val + '"]').prop('disabled', true);
                            }
                        });
                    }
                });
            }
            else{
               const selectedOrgIds = [];
                $('.organizations').each(function () {
                    const row = $(this).closest('tr');
                    const cmpId = row.find('.companySelect').val();
                    const orgId = $(this).val();

                    if (cmpId === companyId && orgId) {
                        selectedOrgIds.push({
                            id: $(this).attr('id'),
                            value: orgId
                        });
                    }
                });

            const companyData = companies.find(c => c.id == companyId);
            const orgs = companyData ? companyData.organizations : [];

            $('.organizations').each(function () {
                const $select = $(this);
                const row = $select.closest('tr');
                const cmpId = row.find('.companySelect').val();
                const selectId = $select.attr('id');
                const currentVal = $select.val();

                if (cmpId === companyId) {
                    $select.html("<option disabled selected value=''>Select Unit</option>");

                    orgs.forEach(org => {
                        const isDisabled = selectedOrgIds.some(sel => sel.value === org.id.toString() && sel.id !== selectId)
                            ? 'disabled' : '';
                        const isSelected = currentVal === org.id.toString() ? 'selected' : '';

                        $select.append(`<option value="${org.id}" ${isDisabled} ${isSelected}>${org.name}</option>`);
                    });
                }
            });

            }
        }


        $(document).on('change', '.levelCompanySelect', function() {
            var organizations = [];
            const id = $(this).attr('data-id');
            const level_company_id = $(this).val()
            $.each(companies, function(key, value) {
                if (value['id'] == level_company_id) {
                    organizations = value['organizations'];
                }
            });

            // Clear and repopulate ONLY the current row's organization dropdown
            const $orgDropdown = $(this).closest('tr').find('.level_organizations');
            $orgDropdown.html("");
            $orgDropdown.append("<option disabled selected value=''>Select Unit</option>");

            $.each(organizations, function(key, value) {
                $orgDropdown.append("<option value='" + value['id'] + "'>" + value['name'] + "</option>");
            });
        });

        $(document).on('change', '.level_organizations', function() {
            const level_org_id = $(this).val();
            const userElement = document.getElementById("user_select_" + $(this).attr('user-select-id'));
            if (userElement) {
                userElement.innerHTML = ``;
            }
            var innerHTMLVal = "<option disabled value=''>Select Approver</option>";
            $.ajax({
                url: "{{route('book.approval-employees.get')}}",
                method: 'GET',
                dataType: 'json',
                data: {
                    organization_id: level_org_id,
                },
                success: function(data) {
                    if (data.data && data.data.length > 0) {
                        $.map(data.data, function(item) {
                            innerHTMLVal += `<option value='${item.id}'>${item.name + " (" + item.email + ")"}</option>`
                        });
                        if (userElement) {
                            userElement.innerHTML = innerHTMLVal;
                        }
                    }
                    updateDisabledEnabledUserSelect('Approval')
                },
                error: function(xhr) {
                    console.error('Error fetching org services data:', xhr.responseText);
                }
            });

        });

        $(document).ready(function() {
            updateDisabledEnabledUserSelect('Approval')
            updateDisabledEnabledUserSelect('Amendment')
            $(document).ready(function () {
                $('.companySelect').each(function () {
                    const companyId = $(this).val();
                    if (companyId) {
                        updateDisabledEnabledUserSelect('Pattern', companyId);
                    }
                });
            });
            $(".select2").select2({
                placeholder: "Select an option"
            });
            function populateDropdowns(data) {
                let companies = data.map(item => ({
                    id: item.organization_id,
                    name: item.company
                }));
                let subLocations = new Set();

                data.forEach(item => {
                    Object.keys(item.sub_locations).forEach(key => {
                        subLocations.add({
                            id: key,
                            name: item.sub_locations[key]
                        });
                    });
                });

                subLocations = Array.from(subLocations);

                $('.company-name').each(function() {
                    $(this).empty().append('<option>Select</option>');
                    companies.forEach(company => {
                        $(this).append(`<option value="${company.id}">${company.name}</option>`);
                    });
                    $(this).select2();
                });

                $('.location').each(function() {
                    $(this).empty().append('<option>Select</option>');
                    subLocations.forEach(location => {
                        $(this).append(`<option value="${location.id}">${location.name}</option>`);
                    });
                    $(this).select2();
                });

                $('.approver-name').each(function() {
                    $(this).empty().append('<option>Select</option>');
                    data.approvers.forEach(approver => {
                        $(this).append(`<option value="${approver.id}">${approver.name}</option>`);
                    });
                    $(this).select2();
                });
            }

            function updateLevelNumbers() {
                $('#workflow-body .approvlevelflow').each(function(index) {
                    var level = index + 1;
                    $(this).find('td:first-child').text(level);
                    $(this).find('h6').text('Level ' + level);
                    $(this).data('level', level);
                    $(this).nextUntil('.approvlevelflow').find('input[type=radio]').each(function() {
                        var name = $(this).attr('name').split('-')[0];
                        $(this).attr('name', name + '-' + level);
                    });
                });
            }
        });

        $(document).on('change', '.userSelect', function() {
            updateDisabledEnabledUserSelect('Approval')
        });

        function disableSelectedMultiOptionsForBooks()
        {
                $('.bookSelect').find('option').prop('disabled', false);
                $('.bookSelect').each(function() {
                    var selectedValues= $(this).val();
                    $.each(selectedValues, function(index, value) {
                        $('.bookSelect').not(this).find('option[value="'+value+'"]').prop('disabled', true);
                    });
                });
        }

        $(document).on('change', '.bookSelect', function() {
            disableSelectedMultiOptionsForBooks();
        });

        $(document).on('change', '.amendmentUserSelect', function() {
            updateDisabledEnabledUserSelect('Amendment')
        });

        $(document).ready(function() {
            function initializeSelect2() {
                $('.select2').select2({
                    width: '100%',
                    placeholder : 'Select an option'
                });
            }

            // Initial Select2 initialization
            initializeSelect2();

            // Function to add a new level row
            function addLevelRow() {

                // Variable to keep track of the current level
                let levelCounter = $('.approvlevelflow').length + 1;

                // get unique users for approval
                let selectedUsers = $(".userSelect option:selected").map(function() {
                    return $(this).val();
                }).get();

                var usersHtml='';
                $.each(users, function( ukey, uValue ) {
                    const newVal=uValue['id']+"|"+uValue['type'];
                    if (selectedUsers.includes(newVal)) {
                        usersHtml+=`<option value="${newVal}" disabled>${uValue['name']}</option>`;
                    }else{
                        usersHtml+=`<option value="${newVal}">${uValue['name']}</option>`;
                    }
                });

                // levelCounter++;
                const newLevelRow = `
                    <tr class="approvlevelflow level-row" data-level="${levelCounter}" id="${levelCounter}">
                        <td class="levelNumber">${levelCounter}</td>
                        <td colspan="6">
                            <h6 class="mb-0 fw-bolder text-dark levelText">Level ${levelCounter}</h6>
                        </td>
                        <td class = "center-align-content"><a href="#" class="text-danger delete-level-row" data-id="${levelCounter}"><i data-feather="trash"></i></a></td>
                    </tr>
                    <tr class="${levelCounter}">
                        <td>&nbsp; <input class="d-none" type="text" value="${levelCounter}" name="level[]"></td>
                        <td>
                            <select class="form-select mw-100 select2 levelCompanySelect" id = "company_select_${levelCounter}" data-id="${levelCounter}" name="level_company_id[]" >
                                <option disabled selected value="">Select Company</option>
                                @foreach ($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <select class="form-select mw-100 select2 level_organizations" user-select-id = "${levelCounter - 1}_0" name="level_organization_id[]" data-id="${levelCounter}" id="level_organization_id${levelCounter}" >
                            </select>
                        </td>
                        <td>
                            <select class="form-select mw-100 select2 userSelect"
                                data-id="${levelCounter}" name="user[${levelCounter - 1}][]" id = "user_select_${levelCounter - 1}_0" multiple>
                                ${usersHtml}
                            </select>
                        </td>
                        <td>
                            <input type="text" value="0" name="min_value[]"  data-id="${levelCounter}" class="form-control mw-100 min-value">
                        </td>

                        <td class = "center-align-content">
                            <div class="customernewsection-form">
                                <div class="demo-inline-spacing">
                                    <input type="hidden" name="rights[]" class="rights-value" value="all">
                                    <div class="form-check form-check-primary mt-0 me-1">
                                        <input type="radio" id="anyone-${levelCounter}" name="rights[${levelCounter - 1}]" class="form-check-input" value="anyone">
                                        <label class="form-check-label fw-bolder" for="anyone-${levelCounter}">Any One</label>
                                    </div>
                                    <div class="form-check form-check-primary mt-0 me-0">
                                        <input type="radio" id="all-${levelCounter}" name="rights[${levelCounter - 1}]" class="form-check-input" value="all" checked>
                                        <label class="form-check-label fw-bolder" for="all-${levelCounter}">All</label>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class = "center-align-content"><a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a></td>
                    </tr>`;
                $('#workflow-body').append(newLevelRow);
                initializeSelect2();
                feather.replace(); // Reinitialize Feather icons
            }

            function renumberLevels() {
                $(".levelNumber").each(function(index) {
                    $(this).text(index + 1);
                });
                $(".levelText").each(function(index) {
                    let newLevel=index + 1;
                    $(this).text("Level "+ newLevel);
                });
            }

             // Add level row on click
             $(document).on('click', '.add-level-row', function(e) {
                e.preventDefault();
                addLevelRow();
                renumberLevels();
            });
            const people = @json($people);

            $('#workflow-body').on('click', '.add-row', function (e) {
                e.preventDefault();

                let $currentRow = $(this).closest('tr');
                let level = $currentRow.prevAll('.level-row').first().data('level');

                // Count all rows after the level-row till next level-row or end
                let rowsInLevel = $currentRow.prevAll('.level-row').first().nextUntil('.level-row');
                let rowCount = rowsInLevel.length;

                let companyOptions = `<option disabled selected value="">Select Company</option>`;
                companies.forEach(company => {
                    companyOptions += `<option value="${company.id}">${company.name}</option>`;
                });

                let userOptions = `<option disabled value="">Select Approver</option>`;
                people.forEach(user => {
                    userOptions += `<option value="${user.id}|${user.type}">${user.name}</option>`;
                });

                let newRow = `
                    <tr>
                        <td>&nbsp; <input class="d-none" type="text" value="${level}" name="level[]"></td>
                        <td>
                            <select class="form-select mw-100 select2 levelCompanySelect" id="company_select_${level}_${rowCount}"
                                data-id="${level}" name="level_company_id[]">
                                ${companyOptions}
                            </select>
                        </td>
                        <td>
                            <select class="form-select mw-100 select2 level_organizations" user-select-id="${rowCount}_${level}"
                                name="level_organization_id[]" id="level_organization_id${level}_${rowCount}">
                            </select>
                        </td>
                        <td>
                            <select class="form-select mw-100 select2 userSelect" data-id="${level}"
                                name="user[${level - 1}][${rowCount}][]" id="user_select_${rowCount}_${level}" multiple>
                                ${userOptions}
                            </select>
                        </td>
                        <td>
                            <input type="text" value="0" name="min_value[]" data-id="${level}" class="form-control mw-100 min-value">
                        </td>
                        <td class="center-align-content">
                            <div class="customernewsection-form">
                                <div class="demo-inline-spacing">
                                    <div class="form-check form-check-primary mt-0 me-1">
                                        <input type="radio" id="anyone-${level}-${rowCount}" name="rights[${level - 1}][${rowCount}]" class="form-check-input" value="anyone">
                                        <label class="form-check-label fw-bolder" for="anyone-${level}-${rowCount}">Any One</label>
                                    </div>
                                    <div class="form-check form-check-primary mt-0 me-0">
                                        <input type="radio" id="all-${level}-${rowCount}" name="rights[${level - 1}][${rowCount}]" class="form-check-input" value="all" checked>
                                        <label class="form-check-label fw-bolder" for="all-${level}-${rowCount}">All</label>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="center-align-content"><a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a></td>
                    </tr>
                `;

                $currentRow.after(newRow);

                initializeSelect2();
                feather.replace();
            });


            // delete level row on click
            $(document).on('click', '.delete-level-row', function(e) {
                e.preventDefault();
                let row = $(this).closest('tr');
                let levelRow = row.prevAll('.level-row').first();
                console.log(levelRow, "LEVEL ROW");
                $(this).closest('tr').remove();
                const level = $(this).closest('tr').data('level');
                $('.'+level).remove();
                renumberLevels();
            });

            // Delete a row
            $('#workflow-body').on('click', '.delete-row', function(e) {
                e.preventDefault();
                let row = $(this).closest('tr');
                let levelRow = row.prevAll('.level-row').first();
                row.remove();

                // Check if this was the last row in the level
                if (levelRow.nextAll('tr').not('.level-row').length === 0) {
                    levelRow.remove(); // Remove the level header row
                    levelCounter--;
                }
            });
        });
        let amendment_count = 1;

$(document).on('click', '.amendment_plus', function (e) {
    e.preventDefault();
    amendment_count++;

    // Define the HTML structure of the new row
    var newRow = `
        <tr>
            <td class="row-number">${amendment_count}</td>
            <td>
                <select class="form-select mw-100 select2 AmendmentCompanySelect" data-id="${amendment_count}" name="amendment_company_id[]">
                    <option disabled selected value="">Select Company</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->id }}">{{ $company->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select class="form-select mw-100 select2 amendment_organizations" name="amendment_organization_id[]" id="amendment_organization_id${amendment_count}">
                </select>
            </td>
            <td>
                <select id = "amend_users_${amendment_count}" class="form-select mw-100 select2 amendmentUserSelect" data-id="${amendment_count}" name="amendment_user[${amendment_count-1}][]" multiple>
                    <option disabled value="">Select Approver</option>
                    @foreach ($people as $user)
                        <option value="{{ $user->id }}|{{ $user->type }}">{{ $user->name }}</option>
                    @endforeach
                </select>
            </td>
          <td class="min-value">
            <input type="text" value="0" name="amendment_min[]"
             class="form-control mw-100">
            </td>
            <td class = "center-align-content">
            <div class="customernewsection-form">
                <div class="demo-inline-spacing">
                    <input type="hidden" name="approval_req[]" class="rights-value" value="no">
                    <div class="form-check form-check-primary mt-0 me-1">
                        <input type="radio" name="approval_req[${amendment_count-1}]" class="form-check-input" value="yes" id = "app_req_yes_${amendment_count}">
                        <label class="form-check-label fw-bolder" for="app_req_yes_${amendment_count}">Yes</label>
                    </div>
                    <div class="form-check form-check-primary mt-0 me-0">
                        <input type="radio" id="app_req_no_${amendment_count}" name="approval_req[${amendment_count-1}]" class="form-check-input" value="no" checked>
                        <label class="form-check-label fw-bolder" for="app_req_no_${amendment_count}">No</label>
                    </div>
                </div>
            </div>
            </td>
            <td class="center-align-content">
                <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
            </td>
        </tr>
    `;

    // Append the new row to the table body
    $('#tableBody').append(newRow);

    // Re-initialize Select2 (or any other plugin if needed)
    $(".select2").select2({
        placeholder: "Select an option"
    });
    // Reload Feather icons to ensure they appear
    feather.replace();

    // Update row numbers
    updateRowNumbers();
});

// Remove row on click of trash icon and update row numbers
$(document).on('click', '.delete-row', function (e) {
    e.preventDefault();
    const $row = $(this).closest('tr');
    resetHiddenRow($row)
    updateRowNumbers();
    updateDisabledEnabledUserSelect('Amendment')
    updateDisabledEnabledUserSelect('Approval')
    updateDisabledEnabledUserSelect('Pattern', companyId);
});

// Remove row on click of trash icon and update row numbers
$(document).on('input', '.amendment_organizations', function (e) {
    e.preventDefault();
    const userElement = document.getElementById('amend_users_' + amendment_count);
    if (userElement) {
        userElement.innerHTML = ``;
    }
    var innerHTMLVal = "<option disabled value=''>Select Approver</option>";
    $.ajax({
        url: "{{route('book.approval-employees.get')}}",
        method: 'GET',
        dataType: 'json',
        data: {
            organization_id: $(this).val(),
        },
        success: function(data) {
            if (data.data && data.data.length > 0) {
                $.map(data.data, function(item) {
                    innerHTMLVal += `<option value='${item.id}'>${item.name + " (" + item.email + ")"}</option>`
                });
                if (userElement) {
                    userElement.innerHTML = innerHTMLVal;
                }
            }
            updateDisabledEnabledUserSelect('Amendment')
        },
        error: function(xhr) {
            console.error('Error fetching org services data:', xhr.responseText);
        }
    });

});

// Function to update row numbers
function updateRowNumbers() {
    amendment_count = 0; // Reset the amendment count
    $('#tableBody tr').each(function(index) {
        amendment_count++;
        $(this).find('.row-number').text(amendment_count); // Update the row number
        $(this).find('.AmendmentCompanySelect, .amendmentUserSelect').attr('data-id', amendment_count); // Update data-id
        $(this).find('.amendment_organizations').attr('id', 'amendment_organization_id' + amendment_count); // Update ID for unique selectors
    });
}

$(document).on('change', '.AmendmentCompanySelect', function() {
            var organizations = [];
            const id = $(this).attr('data-id');
            const level_company_id = $(this).val();

            $.each(companies, function(key, value) {
                if (value['id'] == level_company_id) {
                    organizations = value['organizations'];
                }
            });

            $("#amendment_organization_id" + id).html("");
            $("#amendment_organization_id" + id).append("<option disabled selected value=''>Select Unit</option>");
            $.each(organizations, function(key, value) {
                $("#amendment_organization_id" + id).append("<option value='" + value['id'] + "'>" + value[
                    'name'] + "</option>");
            });
        });

        function onServiceChange(serviceId)
        {
            if (serviceId) {
                $.ajax({
                    url: "{{ url('get/service-params') }}/" + serviceId + "?book_code=" + $("#book_code_input").val(),
                    type: "GET",
                    dataType: "json",
                    success: function(data) {
                        if (data.status === 'success') {
                            if (data.data) {
                                //Parameters
                                const param = data.data;
                                // var paramsHTML = ``;
                                // param.forEach((paramData, paramIndex) => {
                                //     paramsHTML += paramData.param_array_html;
                                // });

                                document.getElementById('Configuration').innerHTML = param.common_parameters;
                                showOrDisableGlParamTab(param.gl_parameters ? true : false);
                                document.getElementById('gl_params').innerHTML = param.gl_parameters;
                                $('.bookSelect').select2({
                                    placeholder: 'Select Book',
                                    allowClear: true
                                });
                                $('.referenceService').select2({
                                    placeholder: 'Select Service',
                                    allowClear: true
                                });
                                $('.commonMultipleSelection').select2({
                                    placeholder: 'Select',
                                    allowClear: true
                                });
                                disableSelectedMultiOptionsForBooks();
                                associateOnChangeForGl();
                                onreferenceServiceChangeCheck(document.getElementById('reference_from_service'));
                                glPostingRequiredOnChange(document.getElementById('gl_posting_required'));
                                transactionTypeServiceTabChange(param.service_type);
                            }
                        } else if (data.status === 'error') {
                            document.getElementById('Configuration').innerHTML = param.common_parameters;
                            document.getElementById('gl_param_tab_header').style.display = "none";
                            document.getElementById('gl_params').innerHTML = param.gl_parameters;
                            showOrDisableGlParamTab(false);
                            associateOnChangeForGl();
                        } else {
                            document.getElementById('Configuration').innerHTML = param.common_parameters;
                            document.getElementById('gl_param_tab_header').style.display = "none";
                            document.getElementById('gl_params').innerHTML = param.gl_parameters;
                            showOrDisableGlParamTab(false);
                            associateOnChangeForGl();
                        }
                    },
                    error: function () {
                        document.getElementById('Configuration').innerHTML = param.common_parameters;
                        document.getElementById('gl_param_tab_header').style.display = "none";
                        document.getElementById('gl_params').innerHTML = param.gl_parameters;
                        showOrDisableGlParamTab(false);
                        associateOnChangeForGl();
                    }
                });
            }
        }

        initializeAutocomplete('org_service_dropdown');

        function associateOnChangeForGl()
        {
            const glPostingParamInput = document.getElementById('gl_posting_required');
            if (glPostingParamInput) {
                glPostingParamInput.oninput = null;
                glPostingParamInput.oninput = function () {
                    showOrDisableGlParamTab();
                };
            }
        }

        function initializeAutocomplete(selector)
        {
            $("#" + selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type:'org_services',
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: `${(item.name).toUpperCase()} (${(item.alias.toUpperCase())})`,
                                    alias : item.alias
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching org services data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    var $input = $(this);
                    var itemId = ui.item.id;
                    var itemName = ui.item.label;
                    $input.val(itemName);
                    onServiceChange(itemId);
                    $("#org_service_id_input").val(itemId);
                    $("#org_service_alias_input").val(ui.item.alias);
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $("#org_service_id_input").val("");
                        $("#org_service_alias_input").val("");
                        $(this).val("");
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
        }

        function showOrDisableGlParamTab(enable = true)
        {
            const tabHeader = document.getElementById('gl_param_tab_header');
            const tabContentElement = document.getElementById('gl_params');
                if (enable) {
                    tabHeader.style.removeProperty('display');
                    tabContentElement.style.removeProperty('display');
                } else {
                    tabHeader.style.display = "none";
                    tabContentElement.style.display = "none";
                }
        }

        function glPostingRequiredOnChange(element)
        {
            if (element) {
                const isGlPostingRequired = element.value;
                if (isGlPostingRequired === "yes") {
                    document.getElementById('post_on_approval_header').style.visibility = "visible";
                    document.getElementById('post_on_approval_header').style.pointerEvents = "auto";
                    document.getElementById('gl_posting_series_header').style.visibility = "visible";
                    document.getElementById('gl_posting_series_header').style.pointerEvents = "auto";
                    // document.getElementById('gl_seperate_discount_posting_header').style.visibility = "visible";
                    // document.getElementById('gl_seperate_discount_posting_header').style.pointerEvents = "auto";
                } else {
                    document.getElementById('post_on_approval_header').style.visibility = "hidden";
                    document.getElementById('post_on_approval_header').style.pointerEvents = "none";
                    document.getElementById('gl_posting_series_header').style.visibility = "hidden";
                    document.getElementById('gl_posting_series_header').style.pointerEvents = "none";
                    // document.getElementById('gl_seperate_discount_posting_header').style.visibility = "hidden";
                    // document.getElementById('gl_seperate_discount_posting_header').style.pointerEvents = "none";
                }
            }
        }

        function resetNumberParam(element, index)
        {
            $("#reset_pattern_" + index).val("");
            $("#prefix_" + index).val("");
            $("#suffix_" + index).val("");
            $("#starting_no_" + index).val("");
        }

        function onSeriesNumberingChange(element, index)
        {
            resetNumberParam(element, index);

            if (element.value == 'Auto') {
                $("#reset_pattern_" + index).removeAttr('disabled');
                $("#prefix_" + index).removeAttr('readonly');
                $("#suffix_" + index).removeAttr('readonly');
                $("#starting_no_" + index).removeAttr('readonly');
                $("#starting_no_" + index).val(1);
                $("#reset_pattern_" + index).val("Never");
            } else {
                $("#reset_pattern_" + index).attr('disabled', true);
                $("#prefix_" + index).attr('readonly', true);
                $("#suffix_" + index).attr('readonly', true);
                $("#starting_no_" + index).attr('readonly', true);
            }
        }

        function resetPatternChange(element, index)
        {
            if (element.value == 'Never') {
                $("#prefix_" + index).removeAttr('readonly');
                $("#suffix_" + index).removeAttr('readonly');
                $("#starting_no_" + index).removeAttr('readonly');
                $("#starting_no_" + index).val(1);
            } else {
                //Clear all values
                // $("#prefix_" + index).val("");
                // $("#suffix_" + index).val("");
                // $("#starting_no_" + index).val("");
                //Disable them
                $("#prefix_" + index).attr('readonly', true);
                $("#prefix_" + index).val('');
                // $("#suffix_" + index).attr('readonly', true);
                // $("#starting_no_" + index).attr('readonly', true);
            }
        }

        // document.addEventListener('click', function(event) {
        //     console.log("CALLED");
        //     $('.select2').select2({});
        // });

        function setManualEntryOption(element)
        {
            const selectedValue = element.value;
            const manualEntryDetailsElement = document.getElementById('manual_entry_details');
            if (selectedValue === "yes") {
                manualEntryDetailsElement.style.display = "";
            } else {
                manualEntryDetailsElement.style.display = "none";
            }
        }

        function getDynamicFields(element)
        {
            let dynamicFieldsValueSection = document.getElementById('dynamic_fields_value');
            dynamicFieldsValueSection.innerHTML = ``;
            let selectedDynamicFieldIds = $('#dynamic_fields_input').val();
            $.ajax({
                url: "{{route('dynamic-fields.detail')}}",
                type: "GET",
                dataType: "json",
                data : {
                    dynamic_field_ids : selectedDynamicFieldIds
                },
                success: function(data) {
                    if (data.status === 'success') {
                        let newHTML = ``;
                        data.data.forEach(field => {
                            newHTML += `
                            <div class = "col-md-auto mb-1">
                            <span class="badge rounded-pill badge-light-primary badgeborder-radius font-small-2"><strong>${field.name}</strong>: <span class="text-secondary">${capitalizeFirstLetter(field.data_type)}</span></span>
                            </div>
                            `;
                        });
                        dynamicFieldsValueSection.innerHTML = newHTML;
                    } else {
                        dynamicFieldsValueSection.innerHTML = ``;
                        Swal.fire({
                            title: 'Error!',
                            text: "Some internal error occured",
                            icon: 'error',
                        });
                    }

                },
                error: function () {
                    dynamicFieldsValueSection.innerHTML = ``;
                    Swal.fire({
                        title: 'Error!',
                        text: "Some internal error occured",
                        icon: 'error',
                    });
                }
            });
        }

        function initializeDynamicFieldDropdown()
        {
            $("#dynamic_fields_input").select2({
                placeholder: "Select an option",
                allowClear: true,
                closeOnSelect: false // Ensures dropdown stays open for multiple selection
            });
        }

        function transactionTypeServiceTabChange(type)
        {
            let nonMasterElements = document.querySelectorAll('.transaction_service_tab');
            if (type === "master") {
                for (let index = 0; index < nonMasterElements.length; index++) {
                    nonMasterElements[index].classList.add('d-none');
                }
            } else {
                for (let index = 0; index < nonMasterElements.length; index++) {
                    nonMasterElements[index].classList.remove('d-none');
                }
            }
        }
    </script>
@endsection
