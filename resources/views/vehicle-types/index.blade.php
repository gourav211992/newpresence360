@extends('layouts.app')

@section('content')
<form action="{{ route('logistics.vehicle-type.store') }}" method="POST" class="ajax-input-form">
    @csrf
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <h2 class="content-header-title float-start mb-0">Vehicle Type Master</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                <li class="breadcrumb-item active">Master</li>
                            </ol>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-2">
                        <button type="submit" class="btn btn-primary btn-sm" id="submit-button">
                            <i data-feather="check-circle"></i> Submit
                        </button>
                    </div>
                </div>
            </div>

            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body customernewsection-form">
                                    <div class="newheader border-bottom mb-2 pb-25">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h4 class="card-title text-theme">Basic Information</h4>
                                                <p class="card-text">Fill the details</p>
                                            </div>
                                            <div class="col-md-6 text-sm-end">
                                                <button type="button" class="btn btn-outline-danger btn-sm" id="delete-selected">
                                                    <i data-feather="x-circle"></i> Delete
                                                </button>
                                                <button type="button" id="addRowBtn" class="btn btn-outline-primary btn-sm">
                                                    <i data-feather="plus"></i> Add New
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive-md">
                                         <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                            <thead>
                                                <tr>
                                                    <th>
                                                        <div class="form-check form-check-primary">
                                                            <input type="checkbox" class="form-check-input" id="checkAll">
                                                        </div>
                                                    </th>
                                                    <th>Vehicle Type <span class="text-danger">*</span></th>
                                                    <th>Capacity <span class="text-danger">*</span></th>
                                                    <th>Uom <span class="text-danger">*</span></th>
                                                    <th>Description</th>
                                                    <th>Status <span class="text-danger">*</span></th>
                                                </tr>
                                            </thead>
                                            <tbody class="mrntableselectexcel">
                                                @php $rowIndex = 0; @endphp
                                                @foreach($vehicleTypes as $type)
                                                    <tr>
                                                        <td>
                                                            <div class="form-check form-check-primary">
                                                                <input type="checkbox" class="form-check-input rowCheckbox" name="selected_rows[]" value="{{ $rowIndex }}" id="row{{ $rowIndex }}">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="hidden" name="vehicle_type[{{ $rowIndex }}][id]" value="{{ $type->id }}">
                                                            <input type="text" name="vehicle_type[{{ $rowIndex }}][name]" value="{{ $type->name }}" class="form-control mw-100" />
                                                        </td>
                                                        <td>
                                                            <input type="text" name="vehicle_type[{{ $rowIndex }}][capacity]" value="{{ $type->capacity }}" class="form-control mw-100 " />
                                                        </td>
                                                        <td>
                                                       <input type="text"
                                                            name="vehicle_type[{{ $rowIndex }}][uom_name]"
                                                            class="form-control ledgerselect uom-autocomplete"
                                                            placeholder="Start typing UOM..."
                                                            value="{{ $type->unit->name ?? '' }}" />

                                                        <input type="hidden"
                                                            name="vehicle_type[{{ $rowIndex }}][uom_id]"
                                                            class="uom-id"
                                                            value="{{ $type->uom_id ?? '' }}" />

                                                    </td>

                                                        <td>
                                                            <input name="vehicle_type[{{ $rowIndex }}][description]" class="form-control mw-100" value="{{$type->description}}">
                                                        </td>
                                                        <td>
                                                            <select name="vehicle_type[{{ $rowIndex }}][status]" 
                                                                class="form-control mw-100 ledgerselecct status-dropdown">
                                                            <option value="active" data-color="success" {{ $type->status == 'active' ? 'selected' : '' }}>Active</option>
                                                            <option value="inactive" data-color="danger" {{ $type->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                        </select>

                                                        </td>
                                                    </tr>
                                                    @php $rowIndex++; @endphp
                                                @endforeach

                                                @if($vehicleTypes->isEmpty())
                                                    <tr>
                                                        <td>
                                                            <div class="form-check form-check-primary">
                                                                <input type="checkbox" class="form-check-input rowCheckbox" name="selected_rows[]" value="0" id="row0">
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <input type="text" name="vehicle_type[0][name]" placeholder="Enter Vehicle Type" class="form-control mw-100" />
                                                        </td>
                                                         <td>
                                                            <input type="text" name="vehicle_type[0][capacity]"  class="form-control mw-100" />
                                                        </td>
                                                        <td>
                                                        <input type="text" name="vehicle_type[0][uom_name]" 
                                                            class="form-control ledgerselect uom-autocomplete" 
                                                            placeholder="Start typing UOM..." />
                                                        <input type="hidden" name="vehicle_type[0][uom_id]" class="uom-id" />
                                                      </td>
                                                        <td>
                                                            <input name="vehicle_type[0][description]" placeholder="Enter Description" class="form-control mw-100" />
                                                        </td>
                                                        <td>
                                                            <select name="vehicle_type[0][status]" class="form-control mw-100 ledgerselecct">
                                                                <option value="active">Active</option>
                                                                <option value="inactive">Inactive</option>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                    @php $rowIndex = 1; @endphp
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div> <!-- card-body -->
                            </div> <!-- card -->
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
let rowIndex = {{ $rowIndex ?? 1 }};

// Select/Deselect All
document.getElementById('checkAll').addEventListener('change', function () {
    document.querySelectorAll('.rowCheckbox').forEach(cb => cb.checked = this.checked);
});

// Add New Row
document.getElementById('addRowBtn').addEventListener('click', function () {
    const tbody = document.querySelector('.mrntableselectexcel');

    // ✅ Check for incomplete required fields
    let incomplete = false;
    tbody.querySelectorAll('tr').forEach(row => {
        const requiredFields = [
            row.querySelector('input[name*="[name]"]'),
            row.querySelector('input[name*="[capacity]"]'),
            row.querySelector('.uom-autocomplete'),
            row.querySelector('select[name*="[status]"]')
        ];

        for (const field of requiredFields) {
            if (field && field.value.trim() === '') {
                incomplete = true;
                break;
            }
        }
    });

    if (incomplete) {
        Swal.fire({
            icon: 'warning',
            title: 'Incomplete Row',
            text: 'Please fill all required fields in the existing row(s) before adding a new one.',
            confirmButtonText: 'OK'
        });
        return;
    }

    const rowId = 'row' + rowIndex;

    const newRow = document.createElement('tr');
    newRow.innerHTML = `
        <td>
            <div class="form-check form-check-primary">
                <input type="checkbox" class="form-check-input rowCheckbox" name="selected_rows[]" value="${rowIndex}" id="${rowId}">
            </div>
        </td>
        <td>
            <input type="text" name="vehicle_type[${rowIndex}][name]" placeholder="Enter Vehicle Type" class="form-control mw-100" />
        </td>
        <td>
            <input type="text" name="vehicle_type[${rowIndex}][capacity]" class="form-control mw-100" />
        </td>
        <td>
            <input type="text" name="vehicle_type[${rowIndex}][uom_name]" class="form-control ledgerselect uom-autocomplete" placeholder="Start typing UOM..." />
            <input type="hidden" name="vehicle_type[${rowIndex}][uom_id]" class="uom-id" />
        </td>
        <td>
            <input name="vehicle_type[${rowIndex}][description]" placeholder="Enter Description" class="form-control mw-100" />
        </td>
        <td>
            <select name="vehicle_type[${rowIndex}][status]" class="form-control mw-100">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </td>
    `;

    tbody.appendChild(newRow);
    rowIndex++;
});

// Delete Selected Rows
document.getElementById('delete-selected').addEventListener('click', function () {
    const tableBody = document.querySelector('.mrntableselectexcel');
    const checkedRows = tableBody.querySelectorAll('.rowCheckbox:checked');

    if (checkedRows.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Selection',
            text: 'Please select at least one row to delete.'
        });
        return;
    }

    const idsToDelete = [];
    checkedRows.forEach(cb => {
        const row = cb.closest('tr');
        const hiddenId = row.querySelector('input[name^="vehicle_type"][name$="[id]"]');
        if (hiddenId && hiddenId.value) {
            idsToDelete.push(hiddenId.value);
        }
    });

    Swal.fire({
        title: 'Are you sure?',
        text: 'Selected records will be permanently deleted!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then(result => {
        if (result.isConfirmed) {
            if (idsToDelete.length > 0) {
                fetch("{{ route('logistics.vehicle-type.delete-multiple') }}", {
                    method: "DELETE",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({ ids: idsToDelete })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.status) {
                        checkedRows.forEach(cb => cb.closest('tr').remove());
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Records deleted successfully.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Error deleting records.'
                        });
                    }
                })
                .catch(err => {
                    console.error("Delete failed:", err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An unexpected error occurred.'
                    });
                });
            } else {
                checkedRows.forEach(cb => cb.closest('tr').remove());
                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: 'Row(s) deleted from the UI.',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        }
    });
});

// UOM Autocomplete
const uoms = [
    @foreach($uoms as $uom)
        { label: "{{ $uom->name }}", value: "{{ $uom->name }}", id: {{ $uom->id }} },
    @endforeach
];

$(document).on('focus', '.uom-autocomplete', function () {
    if (!$(this).data('ui-autocomplete')) {
        $(this).autocomplete({
            source: uoms,
            minLength: 0,
            select: function (event, ui) {
                $(this).val(ui.item.label);
                $(this).closest('tr').find('.uom-id').val(ui.item.id);
                return false;
            }
        }).focus(function () {
            $(this).autocomplete('search', '');
        });
    }
});
</script>

@endsection
