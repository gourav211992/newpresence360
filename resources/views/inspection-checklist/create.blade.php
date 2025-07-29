@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <form class="ajax-input-form" method="POST" action="{{ route('inspection-checklists.store') }}" data-redirect="@if ($currentUrlSegment === 'maintenance-inspection-checklists'){{ route('maintenance-inspection-checklists.index') }} @else {{ route('inspection-checklists.index') }} @endif">
        @csrf
        <input type="hidden" name="current_url_segment" value="{{ $currentUrlSegment }}">
        <div class="app-content content">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 col-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Inspection Checklists</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('inspection-checklists.index') }}">Home</a></li>
                                            <li class="breadcrumb-item active">Add New</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                            <a href="{{ route('inspection-checklists.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
                            <button type="submit" class="btn btn-primary btn-sm"><i data-feather="check-circle"></i> Create</button>
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
                                                <!-- Name Section -->
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Name<span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="name" class="form-control" placeholder="Enter Name" />
                                                        @error('name')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Description Section -->
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Description</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <textarea name="description" class="form-control" placeholder="Enter Description"></textarea>
                                                        @error('description')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                
                                                <!-- Status Section -->
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Status</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="demo-inline-spacing">
                                                            @foreach (['active' => 'Active', 'inactive' => 'Inactive'] as $statusOption => $statusLabel)
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input
                                                                        type="radio"
                                                                        id="status_{{ $statusOption }}"
                                                                        name="status"
                                                                        value="{{ $statusOption }}"
                                                                        class="form-check-input"
                                                                        {{ old('status', 'active') == $statusOption ? 'checked' : '' }}
                                                                    >
                                                                    <label class="form-check-label fw-bolder" for="status_{{ $statusOption }}">
                                                                        {{ ucfirst($statusLabel) }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        @error('status')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Details Section -->
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-9">
                                                        <div class="table-responsive-md">
                                                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                                                                <thead>
                                                                    <tr>
                                                                        <th>S.NO</th>
                                                                        <th>Name<span class="text-danger">*</span></th>
                                                                        <th>Description</th>
                                                                        <th>Data Type</th>
                                                                        <th>Value</th>
                                                                        <th>Mandatory</th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="details-box">
                                                                    <tr data-index="0">
                                                                        <td>
                                                                            <span class="dynamic-field-no-display">1</span>
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" name="checklist_details[0][name]" class="form-control mw-100" placeholder="Enter Name" />
                                                                        </td>
                                                                        <td>
                                                                            <textarea name="checklist_details[0][description]" class="form-control mw-100" rows="1" style="resize: none;" placeholder="Enter Description"></textarea>
                                                                        </td>
                                                                        <td>
                                                                            <select name="checklist_details[0][data_type]" class="form-control mw-100 data-type-select">
                                                                                <option value="">Select Data Type</option>
                                                                                @foreach($dataTypes as $dataType)
                                                                                    <option value="{{ $dataType['value'] }}">{{ $dataType['label'] }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td class="poprod-decpt">
                                                                            <div class="badge-container">
                                                                            </div>
                                                                            <input type="hidden" name="checklist_details[0][value]" class="list-value-input" value="">
                                                                            <a href="javascript:void(0);" class="btn p-25 btn-sm btn-outline-secondary add-value-btn" style="font-size: 10px">
                                                                                Add Value
                                                                            </a>
                                                                        </td>
                                                                        <td>
                                                                            <div class="form-check form-check-primary mt-25 custom-checkbox">
                                                                                <input type="hidden" name="checklist_details[0][mandatory]" value="0">
                                                                                <input type="checkbox" class="form-check-input mandatory-checkbox" name="checklist_details[0][mandatory]" value="1">
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <a href="#" class="text-primary add-row"><i data-feather="plus-square"></i></a>
                                                                            <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
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
                <div class="modal fade" id="addaccess" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header p-0 bg-transparent">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body px-sm-4 mx-50 pb-2">
                                <h1 class="text-center mb-1" id="shareProjectTitle">Add List Values</h1>
                                <p class="text-center">Enter the details below.</p>

                                <div class="row mt-2 align-items-end">
                                    <div class="col-md-10 mb-1">
                                        <label class="form-label">Value<span class="text-danger">*</span></label>
                                        <input type="text" id="value_input" class="form-control" placeholder="Enter Value" />
                                    </div>
                                    <div class="col-md-2 mb-1">
                                        <label class="form-label">&nbsp;</label>
                                        <a href="javascript:void(0);" class="btn btn-sm btn-primary" id="add-value">Add</a>
                                    </div>
                                </div>

                                <div class="table-responsive" style="max-height: 300px">
                                    <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                                        <thead>
                                            <tr>
                                                <th>S.No</th>
                                                <th>Value</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="listValueTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer justify-content-center">
                                <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1 waves-effect">Cancel</button>
                                <button type="button" class="btn btn-primary submitListValuesBtn waves-effect waves-float waves-light" id="submitListValues">Save</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        var $tableBody = $('#details-box');
        const DATA_TYPE_LIST = 'list';
        function updateBadges($row) {
            const badgeContainer = $row.find('.badge-container');
            badgeContainer.empty();

            const valuesStr = $row.find('.list-value-input').val().trim();
            const values = valuesStr ? valuesStr.split(',').map(v => v.trim()).filter(v => v) : [];
            values.forEach((value, index) => {
                if (index < 3) {
                    badgeContainer.append(`<span class="badge rounded-pill badge-light-primary">${value}</span>`);
                } else if (index === 3) {
                    const remaining = values.length - 3;
                    badgeContainer.append(`<span class="badge rounded-pill badge-light-primary plus-badge">+${remaining}</span>`);
                }
            });
        }

        function saveListValues($row) {
            let values = [];
            $('#listValueTableBody tr').each(function() {
                const value = $(this).find('td:nth-child(2)').text().trim();
                if (value) {
                    values.push(value);
                }
            });

            $row.find('.list-value-input').val(values.join(','));

            updateBadges($row);
        }

        $(document).on('click', '.add-value-btn', function() {
            var $row = $(this).closest('tr');
            var $dataTypeSelect = $row.find('.data-type-select');
            var selectedType = $dataTypeSelect.val();

            if (selectedType === DATA_TYPE_LIST) {
                $('#addaccess').data('row', $row); 
                populateModal($row);
                $('#addaccess').modal('show');
            } else {
                alert('Please select "List" as the data type to add values.');
            }
        });

        function addValueAndSave() {
            const value = $('#value_input').val().trim();
            if (value) {
                let exists = false;
                $('#listValueTableBody tr').each(function() {
                    const rowVal = $(this).find('td:nth-child(2)').text().trim();
                    if (rowVal === value) {
                        exists = true;
                        return false;
                    }
                });

                if (exists) {
                    alert('This value already exists.');
                    $('#value_input').val('');
                    return;
                }

                const rowCount = $('#listValueTableBody tr').length + 1;
                const newRow = `
                    <tr>
                        <td>${rowCount}</td>
                        <td>${value}</td>
                        <td>
                            <a href="#" class="text-danger delete-row delete-list-value-row"><i data-feather="trash-2"></i></a>
                        </td>
                    </tr>
                `;
                $('#listValueTableBody').append(newRow);
                $('#value_input').val('');
                const $row = $('#addaccess').data('row');
                saveListValues($row);
                updateRowNumbersAndValues();
            }
        }

        $('#add-value').on('click', function() {
            addValueAndSave();
        });

        $('#value_input').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                addValueAndSave();
            }
        });

        $('#submitListValues').on('click', function() {
            $('#addaccess').modal('hide');
        });

        function populateModal($row) {
            const combinedValuesStr = $row.find('.list-value-input').val().trim();
            $('#listValueTableBody').empty();

            if (combinedValuesStr && combinedValuesStr !== '') {
                const values = combinedValuesStr.split(',');
                values.forEach((value, index) => {
                    const newRow = `
                        <tr>
                            <td>${index + 1}</td>
                            <td>${value}</td>
                            <td>
                                <a href="#" class="text-danger delete-row delete-list-value-row"><i data-feather="trash-2"></i></a>
                            </td>
                        </tr>
                    `;
                    $('#listValueTableBody').append(newRow);
                });
            }

            updateRowNumbersAndValues();
        }

        function updateRowNumbersAndValues() {
            $('#listValueTableBody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
            });
            feather.replace();
        }

        $('#listValueTableBody').on('click', '.delete-list-value-row', function(e) {
            e.preventDefault();
            $(this).closest('tr').remove();
            updateRowNumbersAndValues();
            const $row = $('#addaccess').data('row');
            saveListValues($row);
        });

      
        $('#submitListValues').on('click', function() {
            $('#addaccess').modal('hide');
        });

        $('#addaccess').on('show.bs.modal', function() {
            $(this).removeAttr('inert');
        });

        $('#addaccess').on('hidden.bs.modal', function() {
            $(this).attr('inert', 'inert');
        });

        function applyCapsLock() {
            $('input[type="text"], input[type="number"]').each(function() {
                $(this).val($(this).val().toUpperCase());
            });
            $('input[type="text"], input[type="number"]').on('input', function() {
                var value = $(this).val().toUpperCase();
                $(this).val(value);
            });
        }

        function updateDynamicFieldNumbers() {
            var $rows = $('#details-box tr');
            $tableBody.find('tr').each(function(index) {
                $(this).find('.dynamic-field-no-hidden').val(index + 1);
                $(this).find('.dynamic-field-no-display').text(index + 1);
                $(this).find('input[name^="checklist_details"]').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                    }
                });
                $(this).find('textarea[name^="checklist_details"]').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                    }
                });
                $(this).find('select[name^="checklist_details"]').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                    }
                });
                if ($rows.length === 1) {
                    $(this).find('.delete-row').hide();
                    $(this).find('.add-row').show();
                } else {
                    $(this).find('.delete-row').show();
                    $(this).find('.add-row').toggle(index === 0);
                }
            });
        }

        // Add new row
        function addRow() {
            var $currentRow = $tableBody.find('tr').first();
            var $newRow = $currentRow.clone();
            var rowCount = $tableBody.find('tr').length;
            $newRow.find('input').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/\[\d+\]/, '[' + rowCount + ']'));
                }
                $(this).val('');
                $(this).removeClass('is-invalid');
            });

            $newRow.find('textarea').val('');
            $newRow.find('input[type="checkbox"]').prop('checked', false);
            $newRow.find('input[type="hidden"][name$="[mandatory]"]').val('0');
            $newRow.find('.ajax-validation-error-span').remove();
            $newRow.find('.badge-container').empty(); 
            $tableBody.append($newRow);
            attachEventListeners($newRow);
            updateDynamicFieldNumbers();
            feather.replace();
            applyCapsLock();
        }

        // Delete row
        function deleteRow() {
            $(this).closest('tr').remove();
            updateDynamicFieldNumbers();
        }
        function attachEventListeners($row) {
            $row.find('.add-row').on('click', function(e) {
                e.preventDefault();
                addRow();
            });

            $row.find('.delete-row').on('click', function(e) {
                e.preventDefault();
                deleteRow.call(this);
            });
             $row.find('.mandatory-checkbox').off('change').on('change', function() {
                var isChecked = $(this).is(':checked');
                $(this).closest('tr').find('input[name$="[mandatory]"]').val(isChecked ? 1 : 0);
            });
        }
        attachEventListeners($tableBody.find('tr'));

        updateDynamicFieldNumbers();
        applyCapsLock();
    });
</script>
@endsection