@extends('layouts.app')

@section('content')
<style>
        .middleinputerror {
        padding-bottom: 30px;
        }
        .middleinputerror span.text-danger {
            font-size: 12px;
            position: absolute;
            top: 38px;
        }
        .itemactive { position: absolute; left: 6px; font-size: 11px; top: 6px; color: #fff } 
        .iteminactive {  left: 24px; color: #999 } 
        .customernewsection-form .statusactiinactive .form-check-input { width: 80px; cursor: pointer}
        .customernewsection-form .statusactiinactive .form-check-input:checked + .itemactive { display: inline-block}
        .customernewsection-form .statusactiinactive .form-check-input:checked ~ .iteminactive { display: none }
        
        .customernewsection-form .statusactiinactive .form-check-input:not(:checked) + .itemactive { display: none}
        .customernewsection-form .statusactiinactive .form-check-input:not(:checked) ~ .iteminactive { display: inline-block }
    </style>
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">

            <form id="ledgerForm" action="{{ route('ledgers.store') }}" method="POST">
                @csrf
                <input type="hidden" name="ledger_code_type" value="{{ $itemCodeType }}">
                <input type="hidden" name="book_id" value="{{ $book_id }}">
                <input type="hidden" name="prefix" />
                                                       
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 col-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">New Ledger</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                            <li class="breadcrumb-item"><a href="{{ route('ledgers.index') }}">Ledger
                                                    List</a></li>
                                            <li class="breadcrumb-item active">Add New</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm"><i
                                        data-feather="arrow-left-circle"></i> Back</button>
                                <button type="submit" id="submitBtn" class="btn btn-primary btn-sm"><i
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

                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                                    <div>
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                  
                                                    <div>
                                                        <div class="d-flex align-items-center"> 
                                                            <div class="form-check form-check-primary form-switch statusactiinactive">
                                                                <input type="checkbox" name="status"  checked class="form-check-input" id="customSwitch3" />
                                                                <span class="itemactive">Active</span>
                                                                <span class="itemactive iteminactive">Inactive</span>
                                                            </div>
                                                        </div>
                                                    </div>
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
                                                                    {{ in_array($group->id, old('ledger_group_id', $selectedValues ?? [])) ? 'selected' : '' }}>
                                                                    {{ $group->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="col-md-3">
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
                                                            value="{{ old('code') }}" />
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
                                                            value="{{ old('name') }}" />
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
                                                                    @if (old('cost_center_id') && old('cost_center_id') == $costCenter->id) selected @endif>
                                                                    {{ $costCenter->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>



                                            </div>

                                            {{-- <div class="col-md-3 border-start">
                                                <div class="row align-items-center mb-2">
                                                    <div class="col-md-12">
                                                        <label
                                                            class="form-label text-primary"><strong>Status</strong></label>
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="customColorRadio3" value="1"
                                                                    name="status" class="form-check-input" checked>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio3">Active</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="customColorRadio4"
                                                                    value="0" name="status"
                                                                    class="form-check-input">
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio4">Inactive</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>


                                            </div> --}}

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
                                                                        @selected(old('tax_type') == $value)>
                                                                        {{ $label }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1" id="tax_percentage_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label"> % Calculation <span
                                                                    class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <input type="number" class="form-control"
                                                                id="tax_percentage" name="tax_percentage" step="0.01"
                                                            pattern="^\d+(\.\d{1,2})?$" />
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
                                                                        @selected(old('tds_section') == $value)>
                                                                        {{ $label }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1" id="tds_percentage_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label"> % TDS With PAN <span
                                                                    class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <input type="number" class="form-control"
                                                                id="tds_percentage" name="tds_percentage" step="0.01"
                                                            pattern="^\d+(\.\d{1,2})?$" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1" id="tds_percentage_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label"> % TDS Without PAN <span
                                                                    class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <input type="number" class="form-control"
                                                                id="tds_without_pan" name="tds_without_pan" step="0.01"
                                                            pattern="^\d+(\.\d{1,2})?$" />
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1" id="tds_capping_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label"> TDS Capping <span
                                                                    class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <input type="number" class="form-control"
                                                                id="tds_capping" name="tds_capping" step="any" />
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
                                                                        @selected(old('tcs_section') == $value)>
                                                                        {{ $label }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1" id="tcs_percentage_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label"> % TCS With PAN <span
                                                                    class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <input type="number" class="form-control"
                                                                id="tcs_percentage" name="tcs_percentage" step="0.01"
                                                            pattern="^\d+(\.\d{1,2})?$"/>
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1" id="tcs_percentage_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label"> % TCS Without PAN <span
                                                                    class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <input type="number" class="form-control"
                                                                id="tcs_without_pan" name="tcs_without_pan" step="0.01"
                                                            pattern="^\d+(\.\d{1,2})?$"/>
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1" id="tcs_capping_label">
                                                        <div class="col-md-2">
                                                            <label class="form-label"> TCS Capping <span
                                                                    class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-3">
                                                            <input type="number" class="form-control"
                                                                id="tcs_capping" name="tcs_capping"  step="any"/>
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
    <!-- END: Content-->
@endsection
@section('scripts')
    <script type="text/javascript" src="{{ asset('assets/js/preventkey.js') }}"></script>
    <script>
        $(document).ready(function() {
            //
            
            const Existingledgers = @json($Existingledgers); // Pass from controller
            const ExistingTdsSections = @json($ExistingTdsSections); // Pass existing TDS sections from controller
            const ExistingTcsSections = @json($ExistingTcsSections); // Pass existing TDS sections from controller
            const redirectUrl =
                "{{ route('ledgers.index') }}"; // Fix: was incorrectly routing to 'cost-center.index'

            $('#ledgerForm').on('submit', function(e) {
                e.preventDefault(); // Prevent full page reload
                $('.preloader').show();
                let form = $(this);
                let submitBtn = $('#submitBtn');
                let name = $('input[name="name"]').val()?.trim().toLowerCase();
                let code = $('input[name="code"]').val()?.trim().toLowerCase();

                // Check if code already exists
                if (Existingledgers.some(l => l.code.toLowerCase() === code)) {
                    $('.preloader').hide();
                    showToast('error', 'Ledger code already exists.', 'Duplicate Entry');
                    return;
                }

                // Check if name already exists
                if (Existingledgers.some(l => l.name.toLowerCase() === name)) {
                    $('.preloader').hide();
                    showToast('error', 'Ledger name already exists.', 'Duplicate Entry');
                    return;
                }

                // Check if TDS section already exists in selected TDS groups
                let selectedGroups = $('#ledger_group_id').val() || [];
                let selectedTdsSection = $('#tds_section').val();
                let selectedTcsSection = $('#tcs_section').val();
                
                if (selectedTdsSection && selectedGroups.length > 0) {
                    // Check if any of the selected groups have TDS in their name (indicating TDS group)
                    let hasTdsGroup = false;
                    selectedGroups.forEach(groupId => {
                        let groupOption = $('#ledger_group_id option[value="' + groupId + '"]');
                        if (groupOption.text().toLowerCase().includes('tds')) {
                            hasTdsGroup = true;
                        }
                    });
                    
                    if (hasTdsGroup) {
                        // Check if TDS section already exists in any of the selected groups
                        let duplicateTdsSection = ExistingTdsSections.some(existing => {
                            return existing.tds_section === selectedTdsSection && 
                                   existing.ledger_group_ids.some(existingGroupId => 
                                       selectedGroups.includes(existingGroupId.toString())
                                   );
                        });
                        
                        if (duplicateTdsSection) {
                            $('.preloader').hide();
                            showToast('error', 'This TDS section type already exists in the selected TDS group.', 'Duplicate TDS Section');
                            return;
                        }
                    }
                }


                if (selectedTcsSection && selectedGroups.length > 0) {
                    // Check if any of the selected groups have TDS in their name (indicating TDS group)
                    let hasTdsGroup = false;
                    selectedGroups.forEach(groupId => {
                        let groupOption = $('#ledger_group_id option[value="' + groupId + '"]');
                        if (groupOption.text().toLowerCase().includes('tcs')) {
                            hasTdsGroup = true;
                        }
                    });
                    
                    if (hasTdsGroup) {
                        // Check if TCS section already exists in any of the selected groups
                        let duplicateTcsSection = ExistingTcsSections.some(existing => {
                            return existing.tcs_section === selectedTcsSection && 
                                   existing.ledger_group_ids.some(existingGroupId => 
                                       selectedGroups.includes(existingGroupId.toString())
                                   );
                        });
                        
                        if (duplicateTcsSection) {
                            $('.preloader').hide();
                            showToast('error', 'This TCS section type already exists in the selected TCS group.', 'Duplicate TCS Section');
                            return;
                        }
                    }
                }

                // $('.preloader').show();
                submitBtn.prop('disabled', true);
                // Proceed with AJAX submission
                $.ajax({
                    url: form.attr('action'),
                    method: form.attr('method'),
                    data: form.serialize(),
                    success: function(response) {
                        console.log(response);
                        $('.preloader').hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Created!',
                            text: 'Ledger created successfully.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            form.trigger('reset');
                            location.href = redirectUrl;
                        });
                    },
                    error: function(xhr) {
                        $('.preloader').hide();
                        submitBtn.prop('disabled', false);

                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            $.each(errors, function(field, messages) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: messages[0],
                                });
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Something went wrong. Please try again.',
                            });
                        }
                    }
                });
            });
            //
            let originalOptions = $('#ledger_group_id option').clone();
            $('#ledger_group_id').select2();

            function toggleGstSection() {
                let selectedOptions = $('#ledger_group_id').val() || [];
                let showGst = false;

                // Hide all sections first
                $('#tax_type, #tax_percentage,#tax_type_label,#tax_percentage_label').attr('required', false)
                    .hide();
                $('#tds_section, #tds_percentage,#tds_section_label, #tds_percentage_label,#tds_capping_label').attr('required', false)
                    .hide();
                $('#tcs_section, #tcs_percentage,#tcs_section_label, #tcs_percentage_label,#tcs_capping_label').attr('required', false)
                    .hide();

                // Check which special group is selected (only one can be selected)
                if ({{ $gst_group_id }} != null && selectedOptions.includes("{{ $gst_group_id }}")) {
                    showGst = true;
                    $('#tax_type, #tax_percentage,#tax_type_label,#tax_percentage_label').attr('required', true)
                        .show();
                } else if ({{ $tds_group_id }} != null && selectedOptions.includes("{{ $tds_group_id }}")) {
                    showGst = true;
                    $('#tds_section, #tds_percentage,#tds_section_label, #tds_percentage_label,#tds_capping_label').attr('required',
                        true).show();
                } else if ({{ $tcs_group_id }} != null && selectedOptions.includes("{{ $tcs_group_id }}")) {
                    showGst = true;
                    $('#tcs_section, #tcs_percentage,#tcs_section_label, #tcs_percentage_label,#tcs_capping_label').attr('required',
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
            const itemCodeType = '{{ $itemCodeType }}';
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
                        group_id:groupId,
                    },
                    success: function(response) {
                        itemCodeInput.val((response.code || ''));
                        itemInitialInput.val(response.prefix ||'');
               
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
