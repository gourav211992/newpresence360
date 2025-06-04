@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <form class="ajax-input-form" method="POST" action="{{ route('dynamic-fields.store') }}" data-redirect="{{ route('dynamic-fields.index') }}">
        @csrf
        <div class="app-content content">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 col-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Dynamic Fields</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('dynamic-fields.index') }}">Home</a></li>
                                            <li class="breadcrumb-item active">Add New</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                            <a href="{{ route('dynamic-fields.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
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
                                                            @foreach ($status as $statusOption)
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
                                                                        {{ ucfirst($statusOption) }}
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
                                                                        <th>Dynamic Field Name<span class="text-danger">*</span></th>
                                                                        <th>Dynamic Field Description</th>
                                                                        <th>Data Type</th>
                                                                        <th>List Value</th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="details-box">
                                                                    <tr data-index="0">
                                                                        <td>
                                                                            <input type="hidden" name="field_details[0][dynamic_field_no]" class="dynamic-field-no-hidden text-end" value="1" />
                                                                            <span class="dynamic-field-no-display">1</span>
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" name="field_details[0][name]" class="form-control mw-100" placeholder="Enter Name" />
                                                                        </td>
                                                                        <td>
                                                                            <textarea name="field_details[0][description]" class="form-control mw-100" rows="1" style="resize: none;" placeholder="Enter Description"></textarea>
                                                                        </td>
                                                                        <td>
                                                                            <select name="field_details[0][data_type]" class="form-control mw-100 data-type-select">
                                                                                <option value="">Select Data Type</option>
                                                                                @foreach($dataTypes as $dataType)
                                                                                    <option value="{{ $dataType['value'] }}">{{ $dataType['label'] }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <input type="text" name="field_details[0][value]" class="form-control mw-100 list-value-input" placeholder="Enter Value" readonly />
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
                <!-- Add/Edit List Values Modal -->
                <div class="modal fade" id="listValueModal" tabindex="-1" aria-labelledby="listValueModalLabel" aria-hidden="true" inert>
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header p-0 bg-transparent">
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body px-sm-4 mx-50 pb-2">
                                <h1 class="text-center mb-1" id="listValueModalLabel">Add List Values</h1>
                                <p class="text-center">Enter the details below.</p>

                                <div class="row mt-2">
                                    <div class="col-md-12 mb-1">
                                        <label class="form-label w-100">Value
                                            <a href="#" id="add-value" class="float-end text-primary font-small-2"></a>
                                        </label>
                                        <div class="d-flex align-items-center">
                                            <input type="text" id="value_input" class="form-control list-value-input" placeholder="Enter Value" aria-label="Value">
                                            <!-- Removed Add button -->
                                        </div>
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
        let listValues = [];

        $(document).on('change', '.data-type-select', function() {
            if ($(this).val() === DATA_TYPE_LIST) {
                $('#listValueModal').modal('show');
            }
        });
        $(document).on('click', '.list-value-input', function() {
            var $row = $(this).closest('tr'); 
            var $dataTypeSelect = $row.find('.data-type-select'); 
            var selectedType = $dataTypeSelect.val();

            if (selectedType === DATA_TYPE_LIST) {
                $('#listValueModal').modal('show');
            }
        });

        $('#value_input').on('keypress', function(e) {
            if (e.which === 13) { 
                e.preventDefault();
                addValueAndSave(); 
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
                listValues.push(value);
                $('#value_input').val('');
                saveListValues();
                updateRowNumbersAndValues();
            }
        }

        function updateRowNumbersAndValues() {
            var listValuesArr = [];
            $('#listValueTableBody tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
                const val = $(this).find('td:nth-child(2)').text();
                if (val) {
                    listValuesArr.push(val);
                }
            });
            $('.data-type-select').each(function() {
                if ($(this).val() === DATA_TYPE_LIST) {
                    $(this).closest('tr').find('.list-value-input').val(listValuesArr.join(','));
                }
            });
            listValues = listValuesArr;
            feather.replace();
        }
        $('#listValueTableBody').on('click', '.delete-list-value-row', function() {
            $(this).closest('tr').remove();
            updateRowNumbersAndValues();
        });
        function saveListValues() {
            listValues = [];
            $('#listValueTableBody tr').each(function() {
                const value = $(this).find('td:nth-child(2)').text(); 
                if (value) {
                    listValues.push(value);
                }
            });
            $('.data-type-select').each(function() {
                if ($(this).val() === DATA_TYPE_LIST) {
                    $(this).closest('tr').find('.list-value-input').val(listValues.join(',')); 
                }
            });
        }

        $('#submitListValues').on('click', function() {
            $('#listValueModal').modal('hide');
        });
        
        $('#listValueModal').on('show.bs.modal', function() {
            $(this).removeAttr('inert');
        });

        $('#listValueModal').on('hidden.bs.modal', function() {
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
                $(this).find('input[name^="field_details"]').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                    }
                });
                $(this).find('textarea[name^="field_details"]').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                    }
                });
                $(this).find('select[name^="field_details"]').each(function() {
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
            $newRow.find('.ajax-validation-error-span').remove();
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
        }
        attachEventListeners($tableBody.find('tr'));

        updateDynamicFieldNumbers();
        applyCapsLock();
    });
</script>
@endsection