@extends('layouts.app')
@section('content')
<form class="ajax-input-form" method="POST" action="{{ route('logistics.freight-charges.store') }}" data-redirect="{{ url('/logistics/freight-charges') }}">
    @csrf
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Freight Master</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>  
                                        <li class="breadcrumb-item active">Master</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">   
                            <button type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Submit</button> 
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
                                    <div class="newheader border-bottom mb-2 pb-25">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h4 class="card-title text-theme">Basic Information</h4>
                                                <p class="card-text">Fill the details</p> 
                                            </div>
                                            <div class="col-md-6 mt-sm-0 mt-50 text-sm-end"> 
                                                <button type="button" class="btn btn-outline-danger btn-sm mb-50 mb-sm-0" id="delete-selected"><i data-feather="x-circle"></i> Delete</button>
                                                <button type="button" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 add-row"><i data-feather="plus"></i> Add New</button> 
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12"> 
                                            <div class="table-responsive-md">
                                                <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"> 
                                                    <thead>
                                                        <tr>
                                                            <th class="customernewsection-form">
                                                                <div class="form-check form-check-primary custom-checkbox">
                                                                    <input type="checkbox" class="form-check-input" id="select-all">
                                                                    <label class="form-check-label" for="select-all"></label>
                                                                </div> 
                                                            </th>
                                                            <th colspan="2">Source <span class="text-danger">*</span></th>
                                                            <th colspan="2">Destination <span class="text-danger">*</span></th>
                                                            <th width="100px">Distance (KM)</th>  
                                                            <th width="300px">Vehicle Type <span class="text-danger">*</span></th>
                                                            <th width="100px">Freight (Rs) <span class="text-danger">*</span></th>  
                                                            <th width="300px">Customer</th>  
                                                        </tr>
                                                    </thead>
                                                   <tbody class="mrntableselectexcel">
                                                      @php $rowIndex = count($freightCharges);  @endphp
                                                        @foreach($freightCharges as  $charges)
                                                            <tr>
                                                                <td>
                                                                    <div class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input row-checkbox" name="row_checkbox[]" value="{{ $rowIndex }}">
                                                                        <label class="form-check-label"></label>
                                                                    </div>

                                                                </td>
                                                                <td width="150px">
                                                                    <input type="hidden" name="freight_charges[{{ $rowIndex }}][id]" value="{{ $charges->id ?? '' }}">
                                                                    <select name="freight_charges[{{ $rowIndex }}][source_state_id]" class="form-control source-state state-select select2" data-type="source">
                                                                        <option value="">Select State</option>
                                                                        @foreach($states as $state)
                                                                            <option value="{{ $state->id }}" {{ $charges->source_state_id == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td width="150px">
                                                                     <select name="freight_charges[{{ $rowIndex }}][source_city_id]" 
                                                                        class="form-control source-city city-select select2" 
                                                                        data-selected="{{ $charges->source_city_id }}" 
                                                                        {{ $charges->source_city_id ? '' : 'disabled' }}>
                                                                    <option value="">Select City</option>
                                                                </select>
                                                                </td>
                                                                <td width="150px">
                                                                   <select name="freight_charges[{{ $rowIndex }}][destination_state_id]" 
                                                                            class="form-control destination-state state-select select2" 
                                                                            data-type="destination">
                                                                        <option value="">Select State</option>
                                                                        @foreach($states as $state)
                                                                            <option value="{{ $state->id }}" {{ $charges->destination_state_id == $state->id ? 'selected' : '' }}>{{ $state->name }}</option>
                                                                        @endforeach
                                                                    </select>

                                                                </td>
                                                                <td width="150px">
                                                                    
                                                                   <select name="freight_charges[{{ $rowIndex }}][destination_city_id]" 
                                                                        class="form-control destination-city city-select select2" 
                                                                        data-selected="{{ $charges->destination_city_id }}" 
                                                                        {{ $charges->destination_city_id ? '' : 'disabled' }}>
                                                                    <option value="">Select City</option>
                                                                </select>

                                                                </td>
                                                                <td width="100px">
                                                                    <input type="text" name="freight_charges[{{ $rowIndex }}][distance]" class="form-control mw-100" value="{{ $charges->distance ?? 0 }}">
                                                                </td>
                                                                <td>
                                                                    <select name="freight_charges[{{ $rowIndex }}][vehicle_type_id]" class="form-control select2">
                                                                        <option value="">Select Vehicle Type</option>
                                                                        @foreach($vehicleTypes as $vehicleType)
                                                                            <option value="{{ $vehicleType->id }}" {{ $charges->vehicle_type_id == $vehicleType->id ? 'selected' : '' }}>{{ $vehicleType->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="freight_charges[{{ $rowIndex }}][amount]" class="form-control mw-100" value="{{ $charges->amount ?? 0 }}">
                                                                </td>
                                                                <td>
                                                                    <select name="freight_charges[{{ $rowIndex }}][customer_id]" class="form-control mw-100 select2">
                                                                        <option value="">Select Customer</option>
                                                                        @foreach($customers as $customer)
                                                                            <option value="{{ $customer->id }}" {{ $charges->customer_id == $customer->id ? 'selected' : '' }}>
                                                                                {{ $customer->company_name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                       @php $rowIndex++; @endphp
                                                       @endforeach
                                                       @if($freightCharges->isEmpty())
                                                            <tr>
                                                                <td>
                                                                    <div class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input row-checkbox" name="row_checkbox[]" value="0">
                                                                        <label class="form-check-label"></label>
                                                                    </div>
                                                                </td>
                                                                <td width="150px">
                                                                    <select name="freight_charges[0][source_state_id]" class="form-control source-state state-select select2" data-type="source">
                                                                        <option value="">Select State</option>
                                                                        @foreach($states as $state)
                                                                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td width="150px">
                                                                     <select name="freight_charges[0][source_city_id]" class="form-control source-city city-select select2">
                                                                        <option value="">Select City</option>
                                                                    </select>
                                                                </td>
                                                                <td width="150px">
                                                                    <select name="freight_charges[0][destination_state_id]" class="form-control destination-state state-select select2" data-type="destination">
                                                                        <option value="">Select State</option>
                                                                        @foreach($states as $state)
                                                                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td width="150px">
                                                                     <select name="freight_charges[0][destination_city_id]" class="form-control destination-city city-select select2">
                                                                        <option value="">Select City</option>
                                                                       
                                                                    </select>

                                                                </td>
                                                                <td width="100px">
                                                                    <input type="text" name="freight_charges[0][distance]" class="form-control mw-100" value="0">
                                                                </td>
                                                                <td>
                                                                    <select name="freight_charges[0][vehicle_type_id]" class="form-control select2">
                                                                        <option value="">Select Vehicle Type</option>
                                                                        @foreach($vehicleTypes as $vehicleType)
                                                                            <option value="{{ $vehicleType->id }}">{{ $vehicleType->name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="freight_charges[0][amount]" class="form-control mw-100" value="0">
                                                                </td>
                                                                <td>
                                                                    <select name="freight_charges[0][customer_id]" class="form-control select2">
                                                                        <option value="">Select Customer</option>
                                                                        @foreach($customers as $customer)
                                                                            <option value="{{ $customer->id }}">{{ $customer->company_name }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                            </tr>
                                                           @php $rowIndex = 1; @endphp
                                                          @endif
                                                    </tbody>

                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
    <!-- END: Content-->
</form>
@endsection

@section('scripts')
<script>
    let freightRowIndex = {{ $rowIndex }};

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('select-all').addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
        });

        document.querySelector('.add-row').addEventListener('click', addNewRow);

    });

    function addNewRow() {
        const tbody = document.querySelector('.mrntableselectexcel');
        const newIndex = freightRowIndex++;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="form-check form-check-primary custom-checkbox">
                    <input type="checkbox" class="form-check-input row-checkbox" name="row_checkbox[]">
                    <label class="form-check-label"></label>
                </div> 
            </td>
            <td width="150px">
            <input type="hidden" name="freight_charges[${newIndex}][id]" value="">
                <select name="freight_charges[${newIndex}][source_state_id]" class="form-control source-state state-select select2" data-type="source">
                    <option value="">Select State</option>
                    @foreach($states as $state)
                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                    @endforeach
                </select>
            </td>
            <td width="150px">
                <select name="freight_charges[${newIndex}][source_city_id]" class="form-control source-city city-select select2" disabled>
                    <option value="">Select City</option>
                </select>
            </td>
            <td width="150px">
                <select name="freight_charges[${newIndex}][destination_state_id]" class="form-control destination-state state-select select2" data-type="destination">
                    <option value="">Select State</option>
                    @foreach($states as $state)
                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                    @endforeach
                </select>
            </td>
            <td width="150px">
                <select name="freight_charges[${newIndex}][destination_city_id]" class="form-control destination-city city-select select2" disabled>
                    <option value="">Select City</option>
                </select>
            </td>
            <td width="100px">
                <input type="text" name="freight_charges[${newIndex}][distance]" class="form-control mw-100" value="0" min="0">
            </td>
            <td>
                <select name="freight_charges[${newIndex}][vehicle_type_id]" class="form-control select2">
                    <option value="">Select Vehicle Type</option>
                    @foreach($vehicleTypes as $vehicleType)
                        <option value="{{ $vehicleType->id }}">{{ $vehicleType->name }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="text" name="freight_charges[${newIndex}][amount]" class="form-control mw-100" value="0" min="0" step="0.01">
            </td>
            <td>
                <div class="d-flex align-items-center gap-1">
                    <select name="freight_charges[${newIndex}][customer_id]" class="form-control select2 me-1">
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->company_name }}</option>
                        @endforeach
                    </select>
                   
                </div>
            </td>
        `;

        tbody.appendChild(row);

        // Re-initialize Select2 and icons
        $('.select2').select2();
        if (typeof feather !== 'undefined') feather.replace();
    }
</script>

<script>
    function loadCitiesByState($stateSelect) {
        const stateId = $stateSelect.val();
        const type = $stateSelect.data('type'); 
        const row = $stateSelect.closest('tr');
        const $citySelect = row.find(`.${type}-city`);
        const selectedCityId = $citySelect.data('selected');

        if (stateId) {
            $.ajax({
                url: "{{ route('logistics.freight-charges.get-cities-by-state') }}",
                type: "GET",
                data: { state_id: stateId },
                dataType: "json",
                success: function (response) {
                    if (response.status && response.data.length) {
                        let options = '<option value="">Select City</option>';
                        response.data.forEach(city => {
                            const selected = selectedCityId == city.id ? 'selected' : '';
                            options += `<option value="${city.id}" ${selected}>${city.name}</option>`;
                        });
                        $citySelect.html(options).prop('disabled', false).trigger('change');
                    } else {
                        $citySelect.html('<option value="">Select City</option>').prop('disabled', false);
                    }
                }
            });
        } else {
            $citySelect.html('<option value="">Select City</option>').prop('disabled', true);
        }
    }

    $(document).ready(function () {
        $('.state-select').each(function () {
            if ($(this).val()) {
                loadCitiesByState($(this));
            }
        });

        $(document).on('change', '.state-select', function () {
            loadCitiesByState($(this));
        });
    });
</script>


<script>
    document.getElementById('delete-selected').addEventListener('click', function () {
    const tableBody = document.querySelector('.mrntableselectexcel');
    // CORRECTED SELECTOR (hyphen instead of camelCase)
    const checkedRows = tableBody.querySelectorAll('.row-checkbox:checked');

    if (checkedRows.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Selection',
            text: 'Please select at least one row to delete.'
        });
        return;
    }

    const idsToDelete = [];
    checkedRows.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const hiddenId = row.querySelector('input[name^="freight_charges"][name$="[id]"]');
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
    }).then((result) => {
        if (result.isConfirmed) {
            if (idsToDelete.length > 0) {
                fetch("{{ route('logistics.freight-charges.delete-multiple') }}", {
                    method: "DELETE",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({ ids: idsToDelete })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        checkedRows.forEach(cb => cb.closest('tr').remove());

                      Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Record deleted successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Error deleting records.'
                        });
                    }
                })
                .catch(error => {
                    console.error("Delete failed:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An unexpected error occurred.'
                    });
                });
            } else {
                // Just remove UI rows with no DB id
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
</script>
@endsection
