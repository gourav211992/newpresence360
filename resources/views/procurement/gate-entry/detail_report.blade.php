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
                                    <h3>Gate Entry Report</h3>
                                    <p>Apply the Basic Filter</p>
                                </div>
                                <div
                                    class="col-md-8 text-sm-end pofilterboxcenter mb-0 d-flex flex-wrap align-items-center justify-content-sm-end">
                                    <div class="btn-group new-btn-group my-1 my-sm-0 ps-0">
                                        <input type="radio" class="btn-check" name="Period" id="CurrentMonth"
                                            value="this-month" />
                                        <label class="btn btn-outline-primary mb-0" for="CurrentMonth">Current
                                            Month</label>

                                        <input type="radio" class="btn-check" name="Period" id="LastMonth"
                                            value="last-month" />
                                        <label class="btn btn-outline-primary mb-0" for="LastMonth">Last Month</label>

                                        <input type="radio"
                                            class="btn-check form-control flatpickr-range flatpickr-input" name="Period"
                                            id="Custom" />
                                        <label class="btn btn-outline-primary mb-0" for="Custom">Custom</label>
                                    </div>
                                    <button data-bs-toggle="modal" data-bs-target="#addcoulmn"
                                        class="btn btn-primary btn-sm mb-0 waves-effect"><i data-feather="filter"></i>
                                        Advance Filter</button>
                                    <span> </span>
                                    <a class="btn btn-warning btn-sm mb-0 mx-1 waves-effect" href="/gate-entries/report">
                                        <i data-feather="trash"></i> Reset Filters
                                    </a>

                                </div>
                            </div>

                            <div class="customernewsection-form poreportlistview p-1">
                                <div class="row">
                                    <div class="col">
                                        <div class="mb-1 mb-sm-0">
                                            <label class="form-label">PO No</label>
                                            <select class="form-select select2" id="po_no" name="po_no">
                                                <option value="">Select</option>
                                                @foreach ($purchaseOrders as $po)
                                                    <option value="{{ $po->id }}">{{ $po->document_number }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mb-1 mb-sm-0">
                                            <label class="form-label">GE No</label>
                                            <input type="text" class="form-control" id="gate_entry_no" placeholder="Enter Gate Entry No">
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mb-1 mb-sm-0">
                                            <label class="form-label">SO No</label>
                                            <select class="form-select select2" id="so_no" name="so_no">
                                                <option value="">Select</option>
                                                @foreach ($so as $val)
                                                    <option value="{{ $val->id }}">{{ $val->document_number }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <label class="form-label">Item</label>
                                        <input type="text" placeholder="Select"
                                        class="form-control mw-100 ledgerselecct inventory_items" id="item"
                                        name="item" />
                                    </div>
                                    <div class="col">
                                        <div class="mb-1 mb-sm-0">
                                            <label class="form-label">Status</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="" selected>Select</option>
                                                <option value="draft">Draft</option>
                                                <option value="approval_not_required">Approved</option>
                                                <option value="submitted">Submitted</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="mb-1 mb-sm-0">
                                            <label class="form-label">Select Vendor</label>
                                            <select class="form-select select2" id="vendor" name="vendor">
                                                <option value="">Select</option>
                                                @foreach ($vendors as $vendor)
                                                    <option value="{{ $vendor->id }}">{{ $vendor->company_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12" style="min-height: 300px;">
                            <div class="trailbalnewdesfinance po-reportnewdesign my-class">
                                <table style="width:100%;"
                                    class="datatables-basic table table-responsive myrequesttablecbox tableistlastcolumnfixed">
                                    <thead style="z-index:11;">
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
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#Bank" role="tab"><i
                                    data-feather="bar-chart"></i> More Filter</a>
                        </li>

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
                                        <input type="checkbox" class="form-check-input" id="selectAll" checked="">
                                        <label class="form-check-label" for="selectAll">Select All Columns</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row sortable">
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="s-no" checked="">
                                        <label class="form-check-label" for="s-no">S.No.</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="series" checked="">
                                        <label class="form-check-label" for="series">Series</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="mrn-no" checked="">
                                        <label class="form-check-label" for="mrn-no">Gate Entry NO</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="mrn-date" checked="">
                                        <label class="form-check-label" for="mrn-date">Gate Entry Date</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="po-no" checked="">
                                        <label class="form-check-label" for="po-no">PO NO</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="so-no" checked="">
                                        <label class="form-check-label" for="so-no">SO No</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="vendor" checked="">
                                        <label class="form-check-label" for="vendor">Vendor</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="vendor-rating">
                                        <label class="form-check-label" for="vendor-rating">Vendor Rating</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="category">
                                        <label class="form-check-label" for="category">Category</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="sub-category">
                                        <label class="form-check-label" for="sub-category">Sub Category</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="item-type">
                                        <label class="form-check-label" for="item-type">Item Type</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="sub-type">
                                        <label class="form-check-label" for="sub-type">Sub Type</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="item" checked="">
                                        <label class="form-check-label" for="item">Item</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="item-code" checked="">
                                        <label class="form-check-label" for="item-code">Item Code</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="mrn-qty" checked="">
                                        <label class="form-check-label" for="mrn-qty">Receipt Qty</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="store" checked="">
                                        <label class="form-check-label" for="store">Location</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="rate" checked="">
                                        <label class="form-check-label" for="rate">Rate</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="basic-value" checked="">
                                        <label class="form-check-label" for="basic-value">Basic Value</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="item-discount" checked="">
                                        <label class="form-check-label" for="item-discount">Item Discount</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="header-discount" checked="">
                                        <label class="form-check-label" for="header-discount">Header Discount</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="mrn-amount" checked="">
                                        <label class="form-check-label" for="mrn-amount">Item Amount</label>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input" id="status" checked="">
                                        <label class="form-check-label" for="status">Status</label>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                    <div class="tab-pane" id="Bank">
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
                                    <select class="form-select select2" id="m_attribute" name="m_attribute"
                                        oninput="onAttributeChange(this);">
                                        <option value="">Select</option>
                                        @foreach ($attribute_groups as $attribute_group)
                                            <option value="{{ $attribute_group->id }}">{{ $attribute_group->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Select Attribute Value</label>
                                    <select class="form-select select2" id="m_attribute_value" name="m_attribute_value"
                                        disabled>
                                        <option value="">Select</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
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
                                    <div class="row camparboxnewcen">
                                        <div class="col-md-12 mb-1">
                                            <label class="form-label">Email To</label>
                                            <select name="email_to[]" class="select2-email form-control mail_modal cannot_disable"
                                                multiple="multiple" data-placeholder="Select or enter email(s)">
                                                @foreach ($users as $user)
                                                    <option value="{{ $user->email }}">{{ $user->name }} ({{ $user->email }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row camparboxnewcen">
                                        <div class="col-md-12">
                                            <div class="mb-1">
                                                <label class="form-label">CC To</label>
                                                <select name="email_cc[]" class="select2-cc form-control mail_modal cannot_disable"
                                                    multiple data-placeholder="Select or enter email(s)">
                                                    @foreach ($users as $user)
                                                        <option value="{{ $user->email }}">{{ $user->name }} ({{ $user->email }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-1">
                                                <label class="form-label">Remarks</label>
                                                <textarea name="remarks" id="mail_remarks" class="form-control mail_modal cannot_disable"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer ">
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary data-submit mr-1" id="applyBtn">Apply</button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<!-- BEGIN: Dashboard Custom Code JS-->
<script src="{{ asset('assets/js/custom/gate-entry.js') }}"></script>
<script src="https://unpkg.com/feather-icons"></script>

<!-- END: Dashboard Custom Code JS-->

<script>
    window.routes = {
        poReport: @json(route('gate-entry.report.filter')),
        addScheduler: @json(route('gate-entry.add.scheduler')),
        // reportSendMail: @json(route('po.send.report')),
    };
    const documentStatusCssList = @json($statusCss);

    $(function () {
        $(".sortable").sortable();
    });

    function onAttributeChange(element) {
        const attributeId = element.value; // Get the selected attribute ID
        const attributeValueDropdown = document.getElementById('m_attribute_value');
        // Clear and disable the attribute values dropdown initially
        attributeValueDropdown.innerHTML = '<option value="">Select</option>';
        attributeValueDropdown.disabled = true;

        if (attributeId) {
            // Make an AJAX request to fetch attribute values
            fetch(`/get-attribute-values/${attributeId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.length > 0) {
                        // Populate the dropdown with fetched values
                        data.forEach(item => {
                            const option = document.createElement('option');
                            option.value = item.id;
                            option.textContent = `${item.value}`;
                            attributeValueDropdown.appendChild(option);
                        });

                        attributeValueDropdown.disabled = false; // Enable the dropdown
                    }
                })
                .catch(error => console.error('Error fetching attribute values:', error));
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        feather.replace();
        let filterData = {};

        // Helper function to call fetch if there are changes
        function updateFilterAndFetch() {
            if (Object.keys(filterData).length > 0) {
                fetchPurchaseOrders(filterData);
            }
        }

        document.querySelectorAll('input[name="Period"]').forEach((radio) => {
            radio.addEventListener('change', function (event) {
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

        $('#po_no').on('change', function () {
            const poValue = $(this).val();
            filterData.poNo = poValue; // Set null if no value selected
            updateFilterAndFetch();
        });

        $('#gate_entry_no').on('change', function () {
            const gateEntryValue = $(this).val();
            filterData.gateEntryNo = gateEntryValue;
            updateFilterAndFetch();
        });

        $('#so_no').on('change', function () {
            const soValue = $(this).val();
            filterData.soNo = soValue;
            updateFilterAndFetch();
        });

        $('#status').on('change', function () {
            const statusValue = $(this).val();
            filterData.status = statusValue;
            updateFilterAndFetch();
        });


        $('#vendor').on('change', function () {
            const vendorValue = $(this).val();
            filterData.vendor = vendorValue;
            updateFilterAndFetch();
        });

        function getSelectedData() {
            let selectedData = [];

            $('select[name="to"] option:selected').each(function () {
                selectedData.push({
                    id: $(this).val(),
                    type: $(this).data('type')
                });
            });

            return selectedData;
        }

        // Trigger column order save when Apply button is clicked
        $('#applyBtn').on('click', function (e) {
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
                email_to: $('select[name="email_to[]"]').val(),
                email_cc: $('select[name="email_cc[]"]').val(),
                remarks: $('#mail_remarks').val(),
                po_no: $('select[name="po_no"]').val(),
                gate_entry_no: $('#gate_entry_no').val(),
                so_no: $('select[name="so_no"]').val(),
                m_category: $('select[name="m_category"]').val(),
                m_subCategory: $('select[name="m_sub_category"]').val(),
                m_attribute: $('select[name="m_attribute"]').val(),
                m_attributeValue: $('select[name="m_attribute_value"]').val(),
                item: $('input[name="item"]').data('id'),
                status: $('select[name="status"]').val(),
                vendor: $('select[name="vendor"]').val(),
                startDate: filterData.startDate ? filterData.startDate : '',
                endDate: filterData.endDate ? filterData.endDate : '',
                period: filterData.period ? filterData.period : ''
            };


            filterData.m_category = formData.m_category;
            filterData.m_subCategory = formData.m_subCategory;
            filterData.m_attribute = formData.m_attribute;
            filterData.m_attributeValue = formData.m_attributeValue;

            // Call updateFilterAndFetch once
            updateFilterAndFetch();

            if (formData.email_to && formData.email_to.length > 0) {
                let dataTableSelector = ".datatables-basic";
                const table = $(dataTableSelector).DataTable();
                // Get the current filtered data in the DataTable
                const displayedData = table.rows({ filter: 'applied' }).data();

                // Getting the values of datatable
                const displayedDataArray = [];
                displayedData.each(function(rowData) {
                    displayedDataArray.push(rowData);
                });

                // Getting the headers of datatable
                const displayedHeaders = [];
                $(table.columns().header()).each(function() {
                    displayedHeaders.push($(this).text().trim());
                });

                formData.displayedData = displayedDataArray;
                formData.displayedHeaders = displayedHeaders;

                if (filterModal) {
                    filterModal.hide();
                }

                $.ajax({
                    url: window.routes.addScheduler,
                    method: 'POST',
                    data: formData,
                    success: function (response) {
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
                        $('select[name="email_to[]"]').val([]).trigger('change');
                        $('select[name="email_cc[]"]').val([]).trigger('change');
                        $('textarea[name="remarks"]').val('');

                        // if (filterModal) {
                        //     filterModal.hide();
                        // }
                    },
                    error: function (xhr) {
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

        function initializeAutocomplete(selector, type) {
                $(selector).autocomplete({
                    minLength: 0,
                    source: function(request, response) {
                        $.ajax({
                            url: '/search',
                            method: 'GET',
                            dataType: 'json',
                            data: {
                                q: request.term,
                                type: type
                            },
                            success: function(data) {
                                response($.map(data, function(item) {
                                    return {
                                        id: item.id,
                                        label: item.item_name,
                                        code: item.item_code
                                    };
                                }));
                            },
                            error: function(xhr) {
                                console.error('Error fetching item data:', xhr
                                .responseText);
                            }
                        });
                    },
                    select: function(event, ui) {
                        var $input = $(this);
                        var itemName = ui.item.label;
                        var itemId = ui.item.id;
                        var itemCode = ui.item.code;

                        $input.val(itemName);
                        $input.attr('data-name', itemName);
                        $input.attr('data-code', itemCode);
                        $input.attr('data-id', itemId);
                        $input.attr('value', itemId);
                        filterData.item = itemId;
                        updateFilterAndFetch();
                    },
                    change: function(event, ui) {
                        if (!ui.item) {
                            $(this).val('');
                            $(this).attr('data-name', '');
                            $(this).attr('data-id', '');
                        }
                    }
                }).focus(function() {
                    if (this.value === "") {
                        $(this).autocomplete("search", "");
                    }
                });
            }
            initializeAutocomplete(".inventory_items", "inventory_items");

        $('.select2-email').select2({
            tags: true,
            tokenSeparators: [',', ' '],
            placeholder: "Select or enter email(s)",
            width: '100%',
            createTag: function(params) {
                var term = $.trim(params.term);
                // Basic email format validation
                if (term.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                    return {
                        id: term,
                        text: term,
                        newTag: true
                    };
                }
                return null;
            }
        });

        $('.select2-cc').select2({
            tags: true,
            tokenSeparators: [',', ' '],
            placeholder: "Select or enter email(s)",
            width: '100%',
            createTag: function(params) {
                var term = $.trim(params.term);
                // Basic email format validation
                if (term.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                    return {
                        id: term,
                        text: term,
                        newTag: true
                    };
                }
                return null;
            }
        });
    });
</script>
@endsection
