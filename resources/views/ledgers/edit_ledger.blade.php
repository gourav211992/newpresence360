@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">

            <form id="formUpdate" action="{{ route('ledgers.update', $data->id) }}" method="POST">
                @csrf
                @method('PUT')
                <input type="hidden" name="updated_groups" id="updated_groups">
                <input type="hidden" name="ledger_code_type" value="{{ $data->ledger_code_type }}">
                <input type="hidden" name="prefix" value="{{$data->prefix}}" />


                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 col-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Edit/View Ledger</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                            <li class="breadcrumb-item"><a href="{{ route('ledgers.index') }}">Ledger
                                                    List</a></li>
                                            <li class="breadcrumb-item active">View Ledger</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <a href="{{ route('ledgers.index') }}" class="btn btn-secondary btn-sm">
                                    <i data-feather="arrow-left-circle"></i> Back
                                </a>
                                <a href="javascript:void(0);"
                                    id="checkAndOpenModal"class="btn btn-primary btn-sm mb-50 mb-sm-0">
                                    <i data-feather="check-circle"></i> Update
                                </a>
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
                                                        <label class="form-label">Group <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" multiple id="ledger_group_id"
                                                            name="ledger_group_id[]" required>
                                                            @foreach ($groups as $group)
                                                                <option value="{{ $group->id }}"
                                                                    data-ledgergroup="{{ $group->parent_group_id }}"
                                                                    {{ in_array($group->id, old('ledger_group_id', json_decode($data->ledger_group_id, true) ?? [])) ? 'selected' : '' }}>
                                                                    {{ $group->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div hidden class="col-md-3">
                                                        <a href="{{ route('ledger-groups.create') }}"
                                                            class="voucehrinvocetxt mt-0">Add Group</a>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Ledger Code <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="code" class="form-control" required
                                                            value="{{ old('code', $data->code) }}" />
                                                        @error('code')
                                                            <span class="alert alert-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Ledger Name <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="name" class="form-control" required
                                                            value="{{ old('name', $data->name) }}" />
                                                        @error('name')
                                                            <span class="alert alert-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                

                                                <div hidden class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Cost Center</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select name="cost_center_id" class="form-select select2">
                                                            <option value="">Select</option>
                                                            @foreach ($costCenters as $costCenter)
                                                                <option value="{{ $costCenter->id }}"
                                                                    {{ old('cost_center_id', $data->cost_center_id) == $costCenter->id ? 'selected' : '' }}>
                                                                    {{ $costCenter->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
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
                                                                <input type="radio" id="customColorRadio3" value="1"
                                                                    name="status" class="form-check-input"
                                                                    {{ $data->status == 1 ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio3">Active</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="customColorRadio4"
                                                                    value="0" name="status"
                                                                    class="form-check-input"
                                                                    {{ $data->status == 0 ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio4">Inactive</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-2" id="gst" style="display: none">
                                            <div class="step-custhomapp bg-light">
                                                <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-bs-toggle="tab"
                                                            href="#UOM">Applicability</a>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="tab-content pb-1 px-1">
                                                <div class="tab-pane active" id="UOM">
                                                    <div class="row align-items-center mb-1" id="tax_type_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label">Tax Type <span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <select class="form-select" id="tax_type" name="tax_type">
                                                                <option value="">Select</option>
                                                                @foreach (App\Helpers\ConstantHelper::getTaxTypes() as $value => $label)
                                                                    <option value="{{ $value }}"
                                                                        {{ $data->tax_type == $value ? 'selected' : '' }}>
                                                                        {{ $label }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1" id="tax_percentage_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label">% Calculation <span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="number" class="form-control"
                                                                id="tax_percentage" name="tax_percentage"
                                                                value="{{ $data->tax_percentage }}" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1" id="tds_section_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label">TDS Section Type<span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <select class="form-select select2" name="tds_section"
                                                                id="tds_section">
                                                                <option value="">Select</option>
                                                                @foreach (App\Helpers\ConstantHelper::getTdsSections() as $value => $label)
                                                                    <option value="{{ $value }}"
                                                                        {{ $data->tds_section == $value ? 'selected' : '' }}>
                                                                        {{ $label }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1" id="tds_percentage_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label">% TDS Calculation <span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="number" class="form-control"
                                                                id="tds_percentage" name="tds_percentage"
                                                                value="{{ $data->tds_percentage }}" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1" id="tcs_section_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label">TCS Section Type<span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <select class="form-select select2" name="tcs_section"
                                                                id="tcs_section">
                                                                <option value="">Select</option>
                                                                @foreach (App\Helpers\ConstantHelper::getTcsSections() as $value => $label)
                                                                    <option value="{{ $value }}"
                                                                        {{ $data->tcs_section == $value ? 'selected' : '' }}>
                                                                        {{ $label }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1" id="tcs_percentage_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label">% TCS Calculation <span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <input type="number" class="form-control"
                                                                id="tcs_percentage" name="tcs_percentage"
                                                                value="{{ $data->tcs_percentage }}" />
                                                        </div>
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

    <!-- Modal for group updates -->
    <div class="modal fade text-start" id="postvoucher" tabindex="-1" aria-labelledby="myModalLabel17"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Update
                            Remove Group to New Group</h4>
                        <p class="mb-0">For all the Submitted Voucher</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table
                                    class="mt-1 table table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Remove Group</th>
                                            <th>New Group</th>
                                        </tr>
                                    </thead>
                                    <tbody id="group-table">
                                        @foreach ($groupsModal as $index => $group)
                                            @isset($group->id)
                                                <tr id="{{ $index }}">
                                                    <input type="hidden" name="removeGroup[]" value="{{ $group->id }}">
                                                    <input type="hidden" name="removeGroupName[]"
                                                        value="{{ $group->name }}">
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ $group->name }}</td>
                                                    <td>
                                                        <select disabled class="form-select group-select mw-100"
                                                            data-index="{{ $index }}" name="updatedGroup[]">
                                                            <option value="">Select Group</option>
                                                            @foreach ($groups as $grp)
                                                                <option value="{{ $grp->id }}">{{ $grp->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                            @endisset
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">
                        <i data-feather="x-circle"></i> Cancel
                    </button>
                    <button class="btn btn-primary btn-sm" onclick="SubmitUpdate()">
                        <i data-feather="check-circle"></i> Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Content-->
@endsection

@section('scripts')
    <script type="text/javascript" src="{{ asset('assets/js/preventkey.js') }}"></script>
    <script>
        const existingLedgers = @json($existingLedgers);
        $(document).ready(function() {
            $('#checkAndOpenModal').on('click', function() {
                const currentCode = $('input[name="code"]').val()?.trim().toLowerCase();
                const currentName = $('input[name="name"]').val()?.trim().toLowerCase();

                const originalCode = $('input[name="code"]').attr('value')?.trim().toLowerCase();
                const originalName = $('input[name="name"]').attr('value')?.trim().toLowerCase();
                $('.preloader').show();
                if (currentCode !== originalCode) {
                    if (existingLedgers.some(l => l.code.toLowerCase() === currentCode)) {
                        $('.preloader').hide();
                        showToast('error', 'Ledger code already exists.', 'Duplicate Entry');
                        return;
                    }
                }

                if (currentName !== originalName) {
                    if (existingLedgers.some(l => l.name.toLowerCase() === currentName)) {
                        $('.preloader').hide();
                        showToast('error', 'Ledger name already exists.', 'Duplicate Entry');
                        return;
                    }
                }

                // Passed all checks, show modal
                const modal = new bootstrap.Modal(document.getElementById('postvoucher'));
                $('.preloader').hide();
                modal.show();
            });


            let originalOptions = $('#ledger_group_id option').clone();
            $('#ledger_group_id').select2();
            $('#tds_section').select2();
            $('#tcs_section').select2();

            function toggleGstSection() {
                let selectedOptions = $('#ledger_group_id').val() || [];
                let showGst = false;

                // Hide all sections first
                $('#tax_type, #tax_percentage,#tax_type_label,#tax_percentage_label').attr('required', false)
            .hide();
                $('#tds_section, #tds_percentage,#tds_section_label, #tds_percentage_label').attr('required', false)
                    .hide();
                $('#tcs_section, #tcs_percentage,#tcs_section_label, #tcs_percentage_label').attr('required', false)
                    .hide();

                // Check which special group is selected (only one can be selected)
                if ({{ $gst_group_id }} != null && selectedOptions.includes("{{ $gst_group_id }}")) {
                    showGst = true;
                    $('#tax_type, #tax_percentage,#tax_type_label,#tax_percentage_label').attr('required', true)
                        .show();
                } else if ({{ $tds_group_id }} != null && selectedOptions.includes("{{ $tds_group_id }}")) {
                    showGst = true;
                    $('#tds_section, #tds_percentage,#tds_section_label, #tds_percentage_label').attr('required',
                        true).show();
                } else if ({{ $tcs_group_id }} != null && selectedOptions.includes("{{ $tcs_group_id }}")) {
                    showGst = true;
                    $('#tcs_section, #tcs_percentage,#tcs_section_label, #tcs_percentage_label').attr('required',
                        true).show();
                }

                // Toggle the GST section visibility
                if (showGst) {
                    $('#gst').show();
                } else {
                    $('#gst').hide();
                }
            }

            function validateSpecialGroups(selectedOptions, newlySelected) {
                let gstSelected = {{ $gst_group_id }} != null && selectedOptions.includes("{{ $gst_group_id }}");
                let tdsSelected = {{ $tds_group_id }} != null && selectedOptions.includes("{{ $tds_group_id }}");
                let tcsSelected = {{ $tcs_group_id }} != null && selectedOptions.includes("{{ $tcs_group_id }}");

                // Count how many special groups are selected
                let specialGroupsSelected = [gstSelected, tdsSelected, tcsSelected].filter(Boolean).length;

                // Check if newly selected option is a special group
                let isNewlySelectedSpecial = (
                    newlySelected == "{{ $gst_group_id }}" ||
                    newlySelected == "{{ $tds_group_id }}" ||
                    newlySelected == "{{ $tcs_group_id }}"
                );

                // If trying to select more than one special group
                if (specialGroupsSelected > 1 && isNewlySelectedSpecial) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Selection',
                        text: 'You can only select one of GST, TDS or TCS groups at a time. Please deselect other groups first.',
                        confirmButtonText: 'OK'
                    });
                    return false;
                }
                return true;
            }
            $('#ledger_group_id').on('change', function(e) {
                generateItemCode();
            });

            $('#ledger_group_id').on('select2:select', function(e) {
                generateItemCode();
                let selectedOptions = $(this).val();

                let newlySelected = e.params.data.id;

                // First validate the selection
                if (!validateSpecialGroups(selectedOptions, newlySelected)) {
                    // If invalid, remove the last selected option
                    let newOptions = selectedOptions.filter(option => option != newlySelected);
                    $(this).val(newOptions).trigger('change');

                    return;
                }

                // Toggle GST section based on selections
                toggleGstSection();

                // Handle parent-child relationship logic
                selectedOptions.forEach(function(selectedOption) {
                    let selectedOptionElement = $('#ledger_group_id option[value="' +
                        selectedOption + '"]');
                    let selectedLedgerGroupId = selectedOptionElement.attr('data-ledgergroup');

                    $("#ledger_group_id option").each(function() {
                        let optionValue = $(this).val();
                        let ledgerGroupId = $(this).data('ledgergroup');
                        if ((optionValue == selectedLedgerGroupId ||
                                selectedLedgerGroupId == ledgerGroupId) && !selectedOptions
                            .includes(optionValue)) {
                            $(this).remove();
                        }
                    });
                });

                $(this).trigger('change.select2');
            });

            $('#ledger_group_id').on('select2:unselect', function(e) {
                generateItemCode();
                let selectedOptions = $(this).val() || [];

                // Restore original options and re-select the remaining selections
                $('#ledger_group_id').html(originalOptions).trigger('change.select2');
                selectedOptions.forEach(function(value) {
                    $('#ledger_group_id option[value="' + value + '"]').prop('selected', true);
                });

                // Toggle GST section based on remaining selections
                toggleGstSection();

                // Handle parent-child relationship logic
                selectedOptions.forEach(function(selectedOption) {
                    let selectedOptionElement = $('#ledger_group_id option[value="' +
                        selectedOption + '"]');
                    let selectedLedgerGroupId = selectedOptionElement.attr('data-ledgergroup');

                    $("#ledger_group_id option").each(function() {
                        let optionValue = $(this).val();
                        let ledgerGroupId = $(this).data('ledgergroup');
                        if ((optionValue == selectedLedgerGroupId ||
                                selectedLedgerGroupId == ledgerGroupId) &&
                            selectedOptionElement.val() != optionValue &&
                            !selectedOptions.includes(optionValue)) {
                            $(this).remove();
                        }
                    });
                });
            });

            // Initialize the view on page load
            toggleGstSection();
        });

        function updateTableDropdowns() {
            let selectedValues = $('#ledger_group_id').val() || [];
            let $tableBody = $('#group-table');
            let existingRows = $tableBody.find('tr').length;
            let groups = Object.values({!! json_encode($groups) !!});

            let removeGroupValues = $('input[name="removeGroup[]"]').map(function() {
                return $(this).val();
            }).get();

            // Add rows if selected values exceed current rows
            while (selectedValues.length > existingRows) {
                let newRowIndex = existingRows;
                let newRow = `
            <tr>
                <input type="hidden" name="removeGroup[]" value="0">
                <input type="hidden" name="removeGroupName[]" value="0">
                <td>${newRowIndex + 1}</td>
                <td>New Group</td>
                <td>
                    <select disabled class="form-select group-select mw-100" data-index="${newRowIndex}" name="updatedGroup[]">
                        <option value="">Select Group</option>
                        ${groups.map(grp => `<option value="${grp.id}">${grp.name}</option>`).join('')}
                    </select>
                </td>
            </tr>
            `;
                $tableBody.append(newRow);
                existingRows++;
            }

            let assignedGroups = [];
            $tableBody.find('tr').each(function(index) {
                let row = $(this);
                let removeGroupValue = row.find('input[name="removeGroup[]"]').val();
                let updatedGroupDropdown = row.find('select[name="updatedGroup[]"]');

                if (selectedValues.includes(removeGroupValue)) {
                    updatedGroupDropdown.val(removeGroupValue);
                    assignedGroups.push(removeGroupValue);
                }
            });

            // Assign remaining values to unfilled rows
            $tableBody.find('tr').each(function() {
                let row = $(this);
                let updatedGroupDropdown = row.find('select[name="updatedGroup[]"]');

                if (!updatedGroupDropdown.val()) {
                    let remainingValue = selectedValues.find(value => !assignedGroups.includes(value));
                    if (remainingValue) {
                        updatedGroupDropdown.val(remainingValue);
                        assignedGroups.push(remainingValue);
                    }
                }
            });
        }

        $('#postvoucher').on('shown.bs.modal', function() {
            updateTableDropdowns();
        });

        function SubmitUpdate() {
            Swal.fire({
                title: "Are you sure?",
                text: "This change will reflect on all your voucher entry with same group and updated in all report",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, update it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('.preloader').show();
                    let groupsData = [];
                    let updatedGroupValues = new Set();
                    let hasDuplicate = false;

                    $('#group-table tr').each(function() {
                        let removeGroup = $(this).find('input[name="removeGroup[]"]').val();
                        let removeGroupName = $(this).find('input[name="removeGroupName[]"]').val();
                        let updatedGroup = $(this).find('select[name="updatedGroup[]"]').val();

                        if (updatedGroupValues.has(updatedGroup)) {
                            hasDuplicate = true;
                        } else {
                            updatedGroupValues.add(updatedGroup);
                        }

                        groupsData.push({
                            removeGroup: removeGroup,
                            removeGroupName: removeGroupName,
                            updatedGroup: updatedGroup
                        });
                    });

                    if (hasDuplicate) {
                        $('.preloader').hide();
                        showToast('error', 'Duplicate updated groups are not allowed!');
                        return;
                    }



                    $('#updated_groups').val(JSON.stringify(groupsData));
                    $('#formUpdate').submit();
                }
            });
        }

        function showToast(type, message, title) {
            Swal.fire({
                icon: type,
                text: message,
                title: title,
                allowOutsideClick: false,
                allowEscapeKey: false,
                confirmButtonText: 'OK'
            });
        }
        const itemInitialInput = $('input[name="prefix"]');
        const itemCodeType = '{{ $data->ledger_code_type }}';
        console.log(itemCodeType, "ITEM TYPE");
        const itemCodeInput = $('input[name="code"]');
        if (itemCodeType === 'Manual') {
            itemCodeInput.prop('readonly', false);
        } else {
            itemCodeInput.prop('readonly', true);
        }


        function generateItemCode() {
            const selectedData = $('#ledger_group_id').select2('data');
            const itemName = selectedData.length > 0 ? selectedData[0].text : "";
            const groupId = selectedData.length > 0 ? $('#ledger_group_id').val()[0] : "";
            if (itemCodeType === 'Manual') {
                return;
            }
            $.ajax({
                url: '{{ route('generate-ledger-code') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    group_id: groupId,
                    ledger_id: '{{ $data->id }}',

                },
                success: function(response) {
                    itemCodeInput.val((response.code || ''));
                    itemInitialInput.val(response.prefix || '');

                },
                error: function() {
                    itemCodeInput.val('');
                    itemInitialInput.val('')
                }
            });
        }
        if (itemCodeType === 'Auto') {

            generateItemCode();
        }
    </script>
@endsection
