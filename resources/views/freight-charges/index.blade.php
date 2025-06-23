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
                                                                   <input type="text"
                                                                    name="freight_charges[{{ $rowIndex }}][source_state_name]"
                                                                    class="form-control mw-100 state-autocomplete"
                                                                    placeholder="Start typing source state..."
                                                                    data-type="source"
                                                                    value="{{ optional($charges->sourceState)->name ?? '' }}" />

                                                                <input type="hidden"
                                                                    name="freight_charges[{{ $rowIndex }}][source_state_id]"
                                                                    class="state-id"
                                                                    data-type="source"
                                                                    value="{{ $charges->source_state_id ?? '' }}" />
                                                                 </td>
                                                                <td width="150px">
                                                                    <input type="text"
                                                                        name="freight_charges[{{ $rowIndex }}][source_city_name]"
                                                                        class="form-control mw-100 city-autocomplete"
                                                                        placeholder="Start typing source city..."
                                                                        data-type="source"
                                                                        value="{{ optional($charges->sourceCity)->name ?? '' }}" />

                                                                    <input type="hidden"
                                                                        name="freight_charges[{{ $rowIndex }}][source_city_id]"
                                                                        class="city-id"
                                                                        data-type="source"
                                                                        value="{{ $charges->source_city_id ?? '' }}" />
                                                                </td>
                                                                <td width="150px">
                                                                <!-- Destination State Autocomplete -->
                                                                    <input type="text"
                                                                        name="freight_charges[{{ $rowIndex }}][destination_state_name]"
                                                                        class="form-control mw-100 state-autocomplete"
                                                                        placeholder="Start typing destination state..."
                                                                        data-type="destination"
                                                                        value="{{ optional($charges->destinationState)->name ?? '' }}" />

                                                                    <input type="hidden"
                                                                        name="freight_charges[{{ $rowIndex }}][destination_state_id]"
                                                                        class="state-id"
                                                                        data-type="destination"
                                                                        value="{{ $charges->destination_state_id ?? '' }}" />

                                                               </td>
                                                                <td width="150px">     
                                                                 <!-- Destination City Autocomplete -->
                                                                <input type="text"
                                                                    name="freight_charges[{{ $rowIndex }}][destination_city_name]"
                                                                    class="form-control mw-100 city-autocomplete"
                                                                    placeholder="Start typing destination city..."
                                                                    data-type="destination"
                                                                    value="{{ optional($charges->destinationCity)->name ?? '' }}" />

                                                                <input type="hidden"
                                                                    name="freight_charges[{{ $rowIndex }}][destination_city_id]"
                                                                    class="city-id"
                                                                    data-type="destination"
                                                                    value="{{ $charges->destination_city_id ?? '' }}" />

                                                                </td>
                                                                <td width="100px">
                                                                    <input type="text" name="freight_charges[{{ $rowIndex }}][distance]" class="form-control mw-100" value="{{ $charges->distance ?? 0 }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" 
                                                                        name="freight_charges[{{ $rowIndex }}][vehicle_type_name]" 
                                                                        class="form-control mw-100 vehicle-type-autocomplete" 
                                                                        placeholder="Start typing vehicle type..." 
                                                                        value="{{ optional($charges->vehicleType)->name }} ({{ optional($charges->vehicleType)->capacity }} {{ optional($charges->vehicleType->unit)->name }})">

                                                                    <input type="hidden" 
                                                                        name="freight_charges[{{ $rowIndex }}][vehicle_type_id]" 
                                                                        class="vehicle-type-id" 
                                                                        value="{{ $charges->vehicle_type_id }}">

                                                                </td>
                                                                <td>
                                                                    <input type="text" name="freight_charges[{{ $rowIndex }}][amount]" class="form-control mw-100" value="{{ $charges->amount ?? 0 }}">
                                                                </td>
                                                                <td>
                                                                   <input type="text"
                                                                    name="freight_charges[{{ $rowIndex }}][customer_name]"
                                                                    class="form-control mw-100 customer-autocomplete"
                                                                    placeholder="Start typing customer..."
                                                                    value="{{ optional($charges->customer)->company_name ?? '' }}" />

                                                                <input type="hidden"
                                                                    name="freight_charges[{{ $rowIndex }}][customer_id]"
                                                                    class="customer-id"
                                                                    value="{{ $charges->customer_id ?? '' }}" />

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

                                                                {{-- Source State --}}
                                                                <td width="150px">
                                                                    <input type="text"
                                                                        name="freight_charges[0][source_state_name]"
                                                                        class="form-control mw-100 state-autocomplete"
                                                                        placeholder="Start typing source state..."
                                                                        data-type="source" />
                                                                    <input type="hidden"
                                                                        name="freight_charges[0][source_state_id]"
                                                                        class="state-id"
                                                                        data-type="source" />
                                                                </td>

                                                                {{-- Source City --}}
                                                                <td width="150px">
                                                                    <input type="text"
                                                                        name="freight_charges[0][source_city_name]"
                                                                        class="form-control mw-100 city-autocomplete"
                                                                        placeholder="Start typing source city..."
                                                                        data-type="source" />
                                                                    <input type="hidden"
                                                                        name="freight_charges[0][source_city_id]"
                                                                        class="city-id"
                                                                        data-type="source" />
                                                                </td>

                                                                {{-- Destination State --}}
                                                                <td width="150px">
                                                                    <input type="text"
                                                                        name="freight_charges[0][destination_state_name]"
                                                                        class="form-control mw-100 state-autocomplete"
                                                                        placeholder="Start typing destination state..."
                                                                        data-type="destination" />
                                                                    <input type="hidden"
                                                                        name="freight_charges[0][destination_state_id]"
                                                                        class="state-id"
                                                                        data-type="destination" />
                                                                </td>

                                                                {{-- Destination City --}}
                                                                <td width="150px">
                                                                    <input type="text"
                                                                        name="freight_charges[0][destination_city_name]"
                                                                        class="form-control mw-100 city-autocomplete"
                                                                        placeholder="Start typing destination city..."
                                                                        data-type="destination" />
                                                                    <input type="hidden"
                                                                        name="freight_charges[0][destination_city_id]"
                                                                        class="city-id"
                                                                        data-type="destination" />
                                                                </td>

                                                                {{-- Distance --}}
                                                                <td width="100px">
                                                                    <input type="text"
                                                                        name="freight_charges[0][distance]"
                                                                        class="form-control mw-100"
                                                                        value="0">
                                                                </td>

                                                                {{-- Vehicle Type Autocomplete --}}
                                                                <td>
                                                                    <input type="text"
                                                                        name="freight_charges[0][vehicle_type_name]"
                                                                        class="form-control mw-100 vehicle-type-autocomplete"
                                                                        placeholder="Start typing Vehicle Type..." />
                                                                    <input type="hidden"
                                                                        name="freight_charges[0][vehicle_type_id]"
                                                                        class="vehicle-type-id" />
                                                                </td>

                                                                {{-- Amount --}}
                                                                <td>
                                                                    <input type="text"
                                                                        name="freight_charges[0][amount]"
                                                                        class="form-control mw-100"
                                                                        value="0">
                                                                </td>

                                                                {{-- Customer Select --}}
                                                                <td>
                                                                    <input type="text"
                                                                    name="freight_charges[0][customer_name]"
                                                                    class="form-control mw-100 customer-autocomplete"
                                                                    placeholder="Start typing customer..." />

                                                                <input type="hidden"
                                                                    name="freight_charges[0][customer_id]"
                                                                    class="customer-id" />
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

        // ✅ Check existing rows for empty required fields
        const existingRows = tbody.querySelectorAll('tr');
        let incomplete = false;

        existingRows.forEach(row => {
            const requiredFields = [
                row.querySelector('.state-autocomplete[data-type="source"]'),
                row.querySelector('.city-autocomplete[data-type="source"]'),
                row.querySelector('.state-autocomplete[data-type="destination"]'),
                row.querySelector('.city-autocomplete[data-type="destination"]'),
                row.querySelector('.vehicle-type-autocomplete'),
                row.querySelector('.customer-autocomplete'),
                row.querySelector('input[name*="[amount]"]')
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


        const newIndex = freightRowIndex++;
        const rowId = 'row' + newIndex;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="form-check form-check-primary custom-checkbox">
                    <input type="checkbox" class="form-check-input row-checkbox" name="row_checkbox[]" value="${newIndex}" id="${rowId}">
                    <label class="form-check-label"></label>
                </div>
            </td>

            <td width="150px">
                <input type="hidden" name="freight_charges[${newIndex}][id]" value="">
                <input type="text"
                    name="freight_charges[${newIndex}][source_state_name]"
                    class="form-control mw-100 state-autocomplete"
                    placeholder="Start typing source state..."
                    data-type="source" />
                <input type="hidden"
                    name="freight_charges[${newIndex}][source_state_id]"
                    class="state-id"
                    data-type="source" />
            </td>

            <td width="150px">
                <input type="text"
                    name="freight_charges[${newIndex}][source_city_name]"
                    class="form-control mw-100 city-autocomplete"
                    placeholder="Start typing source city..."
                    data-type="source" />
                <input type="hidden"
                    name="freight_charges[${newIndex}][source_city_id]"
                    class="city-id"
                    data-type="source" />
            </td>

            <td width="150px">
                <input type="text"
                    name="freight_charges[${newIndex}][destination_state_name]"
                    class="form-control mw-100 state-autocomplete"
                    placeholder="Start typing destination state..."
                    data-type="destination" />
                <input type="hidden"
                    name="freight_charges[${newIndex}][destination_state_id]"
                    class="state-id"
                    data-type="destination" />
            </td>

            <td width="150px">
                <input type="text"
                    name="freight_charges[${newIndex}][destination_city_name]"
                    class="form-control mw-100 city-autocomplete"
                    placeholder="Start typing destination city..."
                    data-type="destination" />
                <input type="hidden"
                    name="freight_charges[${newIndex}][destination_city_id]"
                    class="city-id"
                    data-type="destination" />
            </td>

            <td width="100px">
                <input type="text"
                    name="freight_charges[${newIndex}][distance]"
                    class="form-control mw-100"
                    value="0"
                    min="0" />
            </td>

            <td>
                <input type="text"
                    name="freight_charges[${newIndex}][vehicle_type_name]"
                    class="form-control mw-100 ledgerselect vehicle-type-autocomplete"
                    placeholder="Start typing Vehicle Type ..." />
                <input type="hidden"
                    name="freight_charges[${newIndex}][vehicle_type_id]"
                    class="vehicle-type-id" />
            </td>

            <td>
                <input type="text"
                    name="freight_charges[${newIndex}][amount]"
                    class="form-control mw-100"
                    value="0"
                    min="0"
                    step="0.01" />
            </td>

            <td>
                <div class="d-flex align-items-center gap-1">
                    <input type="text"
                        name="freight_charges[${newIndex}][customer_name]"
                        class="form-control mw-100 customer-autocomplete"
                        placeholder="Start typing customer..." />
                    <input type="hidden"
                        name="freight_charges[${newIndex}][customer_id]"
                        class="customer-id" />
                </div>
            </td>
        `;

        tbody.appendChild(row);

        // ✅ Reinitialize any needed JS (autocomplete etc.)
        if (typeof feather !== 'undefined') feather.replace();
    }
</script>


<script>
    document.getElementById('delete-selected').addEventListener('click', function () {
    const tableBody = document.querySelector('.mrntableselectexcel');
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

const vehicleTypes = [
    @foreach($vehicleTypes as $vt)
        {
            label: "{{ $vt->name }} ({{ $vt->capacity }} {{ $vt->unit->name }})",
            value: "{{ $vt->name }} ({{ $vt->capacity }} {{ $vt->unit->name }})",
            id: {{ $vt->id }}
        },
    @endforeach
];

   $(document).on('focus', '.vehicle-type-autocomplete', function () {
    if (!$(this).data('ui-autocomplete')) {
        $(this).autocomplete({
            source: vehicleTypes,
            minLength: 0,
            select: function (event, ui) {
                $(this).val(ui.item.label);
                $(this).closest('tr').find('.vehicle-type-id').val(ui.item.id);
                return false;
            }
        }).focus(function () {
            $(this).autocomplete('search', '');
        });
    }
});

</script>

<script>
const states = [
    @foreach($states as $state)
        { label: "{{ addslashes($state->name) }}", value: "{{ addslashes($state->name) }}", id: {{ $state->id }} },
    @endforeach
];

const customerList = [
    @foreach($customers as $customer)
        {
            label: "{{ addslashes($customer->company_name) }}",
            value: "{{ addslashes($customer->company_name) }}",
            id: {{ $customer->id }}
        },
    @endforeach
];

const cityCache = {};

$(document).ready(function () {
    // Initialize city autocomplete for existing state IDs
    $('.state-id').each(function () {
        const $this = $(this);
        const stateId = $this.val();
        const type = $this.data('type');
        const $row = $this.closest('tr');

        if (stateId) {
            loadCitiesForAutocomplete(stateId, $row, type);
        }
    });

    // Change on hidden state ID
    $(document).on('change', '.state-id', function () {
        const $this = $(this);
        const stateId = $this.val();
        const type = $this.data('type');
        const $row = $this.closest('tr');

        if (stateId) {
            loadCitiesForAutocomplete(stateId, $row, type);
        }
    });

    // State Autocomplete
    $(document).on('focus', '.state-autocomplete', function () {
        const $input = $(this);

        if (!$input.data('ui-autocomplete')) {
            $input.autocomplete({
                source: states,
                minLength: 0,
                select: function (event, ui) {
                    const $row = $input.closest('tr');
                    const type = $input.data('type');

                    $input.val(ui.item.label);
                    $row.find(`.state-id[data-type="${type}"]`).val(ui.item.id);

                    $row.find(`.city-autocomplete[data-type="${type}"]`).val('');
                    $row.find(`.city-id[data-type="${type}"]`).val('');

                    loadCitiesForAutocomplete(ui.item.id, $row, type);
                    return false;
                }
            }).focus(function () {
                $(this).autocomplete('search', '');
            });
        }
    });

    // Customer Autocomplete
    $(document).on('focus', '.customer-autocomplete', function () {
        const $input = $(this);
        if (!$input.data('ui-autocomplete')) {
            $input.autocomplete({
                source: customerList,
                minLength: 0,
                select: function (event, ui) {
                    const $row = $input.closest('tr');
                    $input.val(ui.item.label);
                    $row.find('.customer-id').val(ui.item.id);
                    return false;
                }
            }).focus(function () {
                $(this).autocomplete('search', '');
            });
        }
    });
});

// City loader based on state
function loadCitiesForAutocomplete(stateId, $row, type) {
    const cacheKey = `${type}_${stateId}`;

    if (cityCache[cacheKey]) {
        applyCityAutocomplete(cityCache[cacheKey], $row, type);
        return;
    }

    $.ajax({
        url: "{{ route('logistics.freight-charges.get-cities-by-state') }}",
        method: "GET",
        data: { state_id: stateId },
        dataType: "json",
        success: function (response) {
            if (response.status && Array.isArray(response.data)) {
                cityCache[cacheKey] = response.data;
                applyCityAutocomplete(response.data, $row, type);
            } else {
                console.warn("Invalid city response:", response);
            }
        },
        error: function () {
            console.error("City loading failed for state ID:", stateId);
        }
    });
}

// City autocomplete binder
function applyCityAutocomplete(cities, $row, type) {
    const cityList = cities.map(city => ({
        label: city.name,
        value: city.name,
        id: city.id
    }));

    const $input = $row.find(`.city-autocomplete[data-type="${type}"]`);
    const $hidden = $row.find(`.city-id[data-type="${type}"]`);

    if (!$input.length || !$hidden.length) {
        console.warn("City input or hidden field not found for type:", type);
        return;
    }

    if ($input.data("ui-autocomplete")) {
        $input.autocomplete("destroy");
    }

    $input.autocomplete({
        source: cityList,
        minLength: 0,
        select: function (event, ui) {
            $input.val(ui.item.label);
            $hidden.val(ui.item.id);
            return false;
        }
    }).focus(function () {
        $(this).autocomplete('search', '');
    });
}
</script>


@endsection
