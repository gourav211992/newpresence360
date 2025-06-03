@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <form class="ajax-input-form" method="POST" action="{{ route('dynamic-fields.update', $dynamicField->id) }}" data-redirect="{{ url('/dynamic-fields') }}">
        @csrf
        @method('PUT')
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
                                            <li class="breadcrumb-item"><a href="{{ route('dynamic-fields.index') }}">Dynamic Fields</a></li>
                                            <li class="breadcrumb-item active">Edit</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                           <a href="{{ route('dynamic-fields.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
                            <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                    data-url="{{ route('dynamic-fields.destroy', $dynamicField->id) }}" 
                                    data-redirect="{{ route('dynamic-fields.index') }}"
                                    data-message="Are you sure you want to delete this item?">
                                <i data-feather="trash-2" class="me-50"></i> Delete
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm"><i data-feather="check-circle"></i> Update</button>
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
                                                    <h4 class="card-title text-theme">Dynamic Field</h4>
                                                    <p class="card-text">Update the details</p>
                                                </div>
                                            </div>
                                            <div class="col-md-9">
                                                <!-- Name Section -->
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Name <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="name" class="form-control" placeholder="Enter Name" value="{{ $dynamicField->name ?? '' }}" />
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
                                                        <textarea name="description" class="form-control" placeholder="Enter Description">{{ $dynamicField->description ?? '' }}</textarea>
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
                                                                        {{ $statusOption == old('status', $dynamicField->status) ? 'checked' : '' }}
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

                                                <!-- Field Details Section -->
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
                                                                        <th>List Value</th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="field-details-box">
                                                                    @forelse ($dynamicField->details as $index => $detail)
                                                                        <tr data-index="{{ $index }}" data-id="{{ $detail->id }}">
                                                                        <input type="hidden" name="field_details[{{ $index }}][id]" value="{{ $detail->id }}">
                                                                            <td>
                                                                                <input type="hidden" name="field_details[{{ $index }}][field_details_no]" class="field-details-no-hidden text-end" value="{{ $index + 1 }}" />
                                                                                <span class="field-details-no-display">{{ $index + 1 }}</span>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" name="field_details[{{ $index }}][name]" class="form-control name mw-100" placeholder="Enter Name" value="{{ $detail->name ?? '' }}" />
                                                                            </td>
                                                                            <td>
                                                                               <textarea name="field_details[{{ $index }}][description]" class="form-control description mw-100" rows="1" style="resize: none;" placeholder="Enter Description">{{ $detail->description ?? '' }}</textarea>
                                                                            </td>
                                                                            <td>
                                                                                <select name="field_details[{{ $index }}][data_type]" class="form-control mw-100 data-type-select ">
                                                                                    <option value="">Select Data Type</option>
                                                                                    @foreach($dataTypes as $dataType)
                                                                                        <option value="{{ $dataType['value'] }}" {{ $detail->data_type == $dataType['value'] ? 'selected' : '' }}>{{ $dataType['label'] }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" name="field_details[{{ $index }}][value]" class="form-control mw-100 list-value-input" placeholder="Enter Value" value=" @foreach($detail->values as $value) {{ $value->value }}@if(!$loop->last), @endif @endforeach" readonly />
                                                                                <input type="hidden" class="form-control mw-100 list-value-hidden-input"  value=" @foreach($detail->values as $value) {{ $value->value }}|{{ $value->id }}@if(!$loop->last), @endif @endforeach" readonly />
                                                                            </td>
                                                                            <td>
                                                                                <a href="#" class="text-primary add-row"><i data-feather="plus-square"></i></a>
                                                                                <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
                                                                            </td>
                                                                            
                                                                        </tr>
                                                                    @empty
                                                                        <tr data-index="0">
                                                                            <td>
                                                                                <input type="hidden" name="field_details[0][field_details_no]" class="field-details-no-hidden text-end" value="1" />
                                                                                <span class="field-details-no-display">1</span>
                                                                            </td>
                                                                            <td>
                                                                                <input type="text" name="field_details[0][name]" class="form-control name mw-100" rows="1" style="resize: none;" placeholder="Enter Name" />
                                                                            </td>
                                                                            <td>
                                                                               <textarea name="field_details[0][description]" class="form-control mw-100" placeholder="Enter Description"></textarea>
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
                                                                    @endforelse
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
                 <div class="modal fade" id="listValueModal" tabindex="-1" aria-labelledby="listValueModalLabel"
                     aria-hidden="true" inert>
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header p-0 bg-transparent">
                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                            </div>
                            <div class="modal-body px-sm-4 mx-50 pb-2">
                                <h1 class="text-center mb-1" id="listValueModalLabel">Add List Values</h1>
                                <p class="text-center">Enter the details below.</p>

                                <div class="row mt-2">
                                    <div class="col-md-12 mb-1">
                                        <label class="form-label w-100">Value
                                            <a href="#" id="add-value"
                                               class="float-end text-primary font-small-2"></a>
                                        </label>
                                        <div class="d-flex align-items-center">
                                            <input type="text" id="value_input" class="form-control list-value-input"
                                                   placeholder="Enter Value" aria-label="Value">
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive" style="max-height: 300px">
                                    <table
                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
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
                                <button type="button" data-bs-dismiss="modal"
                                        class="btn btn-outline-secondary me-1 waves-effect">Cancel
                                </button>
                                <button type="button"
                                        class="btn btn-primary submitListValuesBtn waves-effect waves-float waves-light"
                                        id="submitListValues">Save
                                </button>
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
    var $tableBody = $('#field-details-box');
    const DATA_TYPE_LIST = 'list';
    function initializeRowListValues($row) {
        $row.find('.data-type-select').off('change');
        $row.find('.list-value-input').off('click');

        $row.find('.data-type-select').on('change', function() {
            const selectedValue = $(this).val();
            const $listInput = $row.find('.list-value-input'); 

            if (selectedValue === DATA_TYPE_LIST) {
                $('#listValueTableBody').empty();
                $('#listValueModal').data('row', $row);
                populateListValueModal($row);
                $('#listValueModal').modal('show');
            } else {
                $row.find('.list-value-input').val('');
            }

        });
        // When the list value input is clicked
        $row.find('.list-value-input').on('click', function() {
            var selectedType = $row.find('.data-type-select').val();
            if (selectedType === DATA_TYPE_LIST) {
                $('#listValueTableBody').empty();
                $('#listValueModal').data('row', $row);  
                populateListValueModal($row); 
                $('#listValueModal').modal('show');
            }
        });
    }
    // Function to populate the List Value Modal
    function populateListValueModal($row) {
        var combinedValues = $row.find('.list-value-hidden-input').val().trim(); 
        $('#listValueTableBody').empty();

        if (combinedValues && combinedValues !== '') {
            var valuesArray = combinedValues.split(',');

            valuesArray.forEach(function(combinedValue, index) {
                var [value, id] = combinedValue.split('|'); 
                if (!id) {
                    id = ''; 
                }
                const newRow = `
                    <tr data-id="${id.trim()}">
                        <td>${index + 1}</td>
                        <td>${value.trim()}</td>
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

            $('#value_input').val('');

            updateRowNumbersAndValues(); 
        }
    }

    function updateRowNumbersAndValues() {
        let listValuesArr = [];
        $('#listValueTableBody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
            const val = $(this).find('td:nth-child(2)').text();
            if (val) {
                listValuesArr.push(val);
            }
        });
        const $row = $('#listValueModal').data('row');
        if ($row) {
            $row.find('.list-value-input').val(listValuesArr.join(','));
        }
        feather.replace();
    }

    $('#listValueTableBody').on('click', '.delete-list-value-row', function(e) {
        e.preventDefault(); 
        var $row = $(this).closest('tr'); 
        var listValueId = $row.data('id'); 
        if (listValueId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Are you sure you want to delete this record?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/dynamic-fields/detail-value/' + listValueId,
                        type: 'DELETE',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                        },
                        success: function(response) {
                            if (response.status) {
                                $row.remove(); 
                                Swal.fire('Deleted!', response.message, 'success').then(() => {
                                    window.location.reload(); 
                                });
                                updateRowNumbersAndValues(); 
                            } else {
                                Swal.fire('Error!', response.message || 'Could not delete list value.', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON.message || 'An error occurred while deleting the list value.', 'error');
                        }
                    });
                }
            });
        } else {
            $row.remove(); 
            updateRowNumbersAndValues(); 
        }
    });


    $('#submitListValues').on('click', function() {
        $('#listValueModal').modal('hide');
    });

    $('#listValueModal').on('show.bs.modal', function() {
        $(this).removeAttr('inert');
    });

    $('#listValueModal').on('hidden.bs.modal', function() {
        $(this).attr('inert', 'inert');
    })

    function applyCapsLock() {
        $('input[type="text"], input[type="number"]').each(function() {
            $(this).val($(this).val().toUpperCase());
        });
        $('input[type="text"], input[type="number"]').on('input', function() {
            var value = $(this).val().toUpperCase();
            $(this).val(value);
        });
    }

    function updateFieldDetailsNumbers() {
        $tableBody.find('tr').each(function(index) {
            $(this).find('.field-details-no-hidden').val(index + 1);
            $(this).find('.field-details-no-display').text(index + 1);
            $(this).find('[name]').each(function() {
                var name = $(this).attr('name');
                var newName = name.replace(/\[\d+\]/, '[' + index + ']');
                $(this).attr('name', newName);
            });
            if ($tableBody.find('tr').length === 1) {
                $(this).find('.delete-row').hide();
                $(this).find('.add-row').show();
            } else {
                $(this).find('.delete-row').show();
                 $(this).find('.add-row').toggle(index === 0);
            }
        });
    }

    $('.add-row').on('click', function(e) {
        e.preventDefault();
        var $currentRow = $(this).closest('tr');
        var $newRow = $currentRow.clone();
        $newRow.find('input').val('');
        $newRow.find('textarea').val('');
        $newRow.find('select').val('');
        $newRow.attr('data-id', ''); 
        $newRow.find('.ajax-validation-error-span').remove();
        var rowCount = $tableBody.find('tr').length;
        $newRow.find('[name]').each(function() {
            var name = $(this).attr('name');
            if (name) {
                $(this).attr('name', name.replace(/\[\d+\]/, '[' + rowCount + ']'));
            }
            $(this).removeClass('is-invalid');
        });

        $tableBody.append($newRow);
        updateFieldDetailsNumbers();
        applyCapsLock();
        initializeRowListValues($newRow);
        $tableBody.find('.delete-row').show();
    });

    $tableBody.on('click', '.delete-row', function(e) {
        e.preventDefault();
        var $row = $(this).closest('tr');
        var fieldDetailId = $row.data('id');

        if (fieldDetailId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Are you sure you want to delete this record?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/dynamic-fields/field-detail/' + fieldDetailId,
                        type: 'DELETE',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                        },
                        success: function(response) {
                            if (response.status) {
                                $row.remove();
                                Swal.fire('Deleted!', response.message, 'success');
                                updateFieldDetailsNumbers();
                            } else {
                                Swal.fire('Error!', response.message || 'Could not delete field detail.', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON.message || 'An error occurred while deleting the field detail.', 'error');
                        }
                    });
                }
            });
        } else {
            $row.remove();
            updateFieldDetailsNumbers();
        }

        if ($tableBody.find('tr').length === 1) {
            $tableBody.find('.delete-row').hide();
            $tableBody.find('.add-row').show();
        }
    });


    // Apply initial setup to existing rows
    $tableBody.find('tr').each(function() {
        initializeRowListValues($(this));
    });

    updateFieldDetailsNumbers();
    applyCapsLock();
});
</script>

@endsection