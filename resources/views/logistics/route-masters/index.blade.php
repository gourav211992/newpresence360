@extends('layouts.app')

@section('content')
<form action="{{ route('logistics.route-master.store') }}" method="POST" class="ajax-input-form">
    @csrf
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <h2 class="content-header-title float-start mb-0">Route Master</h2>
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
                                                <h4 class="card-title text-theme">Route Information</h4>
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
                                                    <th>Location <span class="text-danger">*</span></th>
                                                    <th>Country <span class="text-danger">*</span></th>
                                                    <th>State <span class="text-danger">*</span></th>
                                                    <th>City <span class="text-danger">*</span></th>
                                                    <th>Status <span class="text-danger">*</span></th>
                                                </tr>
                                            </thead>
                                           <tbody id="route-rows" class="mrntableselectexcel">
                                                @php $rowIndex = 0; @endphp
                                                @foreach($routeMasters as $route)
                                                <tr>
                                                    <td>
                                                         <div class="form-check form-check-primary">
                                                                <input type="checkbox" class="form-check-input rowCheckbox" name="selected_rows[]" value="{{ $rowIndex }}" id="row{{ $rowIndex }}">
                                                            </div>
                                                    </td>
                                                    <td>
                                                         <input type="hidden" name="route_master[{{ $rowIndex }}][id]" value="{{ $route->id }}">
                                                        <input type="text" name="route_master[{{ $rowIndex }}][name]" class="form-control mw-100" placeholder="Location" value="{{ old("route_master.$rowIndex.name", $route->name ?? '') }}">
                                                    </td>
                                                    <td >
                                                        <input type="text" name="route_master[{{ $rowIndex }}][country_name]" class="form-control mw-100 country-autocomplete" placeholder="Country"
                                                            value="{{ old("route_master.$rowIndex.country_name", optional($route->country)->name ?? '') }}">
                                                        <input type="hidden" name="route_master[{{ $rowIndex }}][country_id]" class="country-id"
                                                            value="{{ old("route_master.$rowIndex.country_id", $route->country_id ?? '') }}">
                                                    </td>
                                                    <td >
                                                        <input type="text" name="route_master[{{ $rowIndex }}][state_name]" class="form-control mw-100 state-autocomplete" placeholder="State"
                                                            value="{{ old("route_master.$rowIndex.state_name", optional($route->state)->name ?? '') }}">
                                                        <input type="hidden" name="route_master[{{ $rowIndex }}][state_id]" class="state-id"
                                                            value="{{ old("route_master.$rowIndex.state_id", $route->state_id ?? '') }}">
                                                    </td>
                                                    <td width="150px">
                                                        <input type="text" name="route_master[{{ $rowIndex }}][city_name]" class="form-control mw-100 city-autocomplete" placeholder="City"
                                                            value="{{ old("route_master.$rowIndex.city_name", optional($route->city)->name ?? '') }}">
                                                        <input type="hidden" name="route_master[{{ $rowIndex }}][city_id]" class="city-id"
                                                            value="{{ old("route_master.$rowIndex.city_id", $route->city_id ?? '') }}">
                                                    </td>
                                                    <td>
                                                          @php
                                                            $statusClass = $route->status === 'active' ? 'bg-success text-white' : 'bg-danger text-white';
                                                        @endphp

                                                        <select name="route_master[{{ $rowIndex }}][status]"
                                                                class="form-control mw-100 {{ $statusClass }}">
                                                            <option value="active" {{ $route->status == 'active' ? 'selected' : '' }}>Active</option>
                                                            <option value="inactive" {{ $route->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                                @php $rowIndex++; @endphp
                                                @endforeach

                                                @if($routeMasters->isEmpty())
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="form-check-input rowCheckbox" name="selected_rows[]" value="0">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="route_master[0][name]" class="form-control mw-100" placeholder="Location">
                                                    </td>
                                                    <td>
                                                        @php $selectedCountry = $countries->firstWhere('id', $selectedCountryId); @endphp
                                                        <input type="text" name="route_master[0][country_name]" class="form-control mw-100 country-autocomplete" placeholder="Country" value="{{ $selectedCountry->name ?? '' }}">
                                                        <input type="hidden" name="route_master[0][country_id]" class="country-id" value="{{ $selectedCountryId }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="route_master[0][state_name]" class="form-control mw-100 state-autocomplete" placeholder="State">
                                                        <input type="hidden" name="route_master[0][state_id]" class="state-id">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="route_master[0][city_name]" class="form-control mw-100 city-autocomplete" placeholder="City">
                                                        <input type="hidden" name="route_master[0][city_id]" class="city-id">
                                                    </td>
                                                    <td>
                                                        <select name="route_master[0][status]" class="form-control mw-100 status-dropdown">
                                                            <option value="active" data-color="success">Active</option>
                                                            <option value="inactive" data-color="danger">Inactive</option>
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
document.addEventListener('DOMContentLoaded', function () {
 
    document.getElementById('checkAll').addEventListener('change', function () {
        document.querySelectorAll('.rowCheckbox').forEach(cb => cb.checked = this.checked);
    });
    let rowIndex = {{ $rowIndex ?? 1 }};

    const selectedCountryId = {{ $selectedCountryId ?? 'null' }};
    const selectedCountryName = @json(optional($countries->firstWhere('id', $selectedCountryId))->name);

    // Add Row Button logic
    document.getElementById('addRowBtn').addEventListener('click', () => {
        const tbody = document.getElementById('route-rows');
        if (!tbody) return;

        // Check for incomplete rows
        let incomplete = false;
        tbody.querySelectorAll('tr').forEach(row => {
            const requiredFields = [
                row.querySelector('input[name*="[name]"]'),
                row.querySelector('input.country-id'),
                row.querySelector('input.state-id'),
                row.querySelector('input.city-id'),
                row.querySelector('select[name*="[status]"]')
            ];
            for (const field of requiredFields) {
                if (!field || field.value.trim() === '') {
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
                <input type="text" name="route_master[${rowIndex}][name]" class="form-control mw-100" placeholder="Location">
            </td>
            <td>
                <input type="text" name="route_master[${rowIndex}][country_name]" class="form-control mw-100 country-autocomplete" placeholder="Country" value="${selectedCountryName ?? ''}">
                <input type="hidden" name="route_master[${rowIndex}][country_id]" class="country-id" value="${selectedCountryId ?? ''}">
            </td>
            <td>
                <input type="text" name="route_master[${rowIndex}][state_name]" class="form-control mw-100 state-autocomplete" placeholder="State">
                <input type="hidden" name="route_master[${rowIndex}][state_id]" class="state-id">
            </td>
            <td>
                <input type="text" name="route_master[${rowIndex}][city_name]" class="form-control mw-100 city-autocomplete" placeholder="City">
                <input type="hidden" name="route_master[${rowIndex}][city_id]" class="city-id">
            </td>
            <td>
                <select name="route_master[${rowIndex}][status]" class="form-control mw-100 status-dropdown">
                    <option value="active" data-color="success">Active</option>
                    <option value="inactive" data-color="danger">Inactive</option>
                </select>
            </td>
        `;

    
       tbody.appendChild(newRow);
        const $row = $(newRow);
        

        if (selectedCountryId) {
            const $stateInput = $row.find('.state-autocomplete');
            loadStates(selectedCountryId, function () {
                applyStateAutocomplete(selectedCountryId, $stateInput);
            });
        }

     
       rowIndex++;
    });
});
</script>

<script>
    //multiple row deleting
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
        const hiddenId = row.querySelector('input[name^="route_master"][name$="[id]"]');
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
                fetch("{{ route('logistics.route-master.delete-multiple') }}", {
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
</script>
<script>
const countryCache = @json(
    $countries->map(fn($c) => [
        'label' => $c->name,
        'value' => $c->name,
        'id'    => $c->id
    ])->values()
);

const stateCache = {};
const cityCache = {};

$(document).ready(function () {

    // ——————— COUNTRY ———————
    $(document).on('focus', '.country-autocomplete', function () {
        const $input = $(this);
        const $row = $input.closest('tr');

        if (!$input.data('ui-autocomplete')) {
            $input.autocomplete({
                source: countryCache,
                minLength: 0,
                select: function (event, ui) {
                     $input.val(ui.item.label);

                    $row.find('.country-id').val(ui.item.id);
                

                    // Clear state & city
                    $row.find('.state-autocomplete').val('');
                    $row.find('input.state-id').val('');
                    $row.find('.city-autocomplete').val('');
                    $row.find('input.city-id').val('');

                     const $stateInput = $row.find('.state-autocomplete');
                    loadStates(ui.item.id, null, function () {
                        applyStateAutocomplete(ui.item.id, null, $stateInput);
                    });
                }
            });
        }

        // Always show suggestions on focus
        $input.autocomplete("search", "");
    });

    // ——————— STATE ———————
    $(document).on('focus', '.state-autocomplete', function () {
        const $input = $(this);
        const $row = $input.closest('tr');
        const countryId = $row.find('input.country-id').val();
      

        if (!countryId) {
            console.warn('Missing country ID for state dropdown.');
            return;
        }

        if (stateCache[countryId]) {
            applyStateAutocomplete(countryId, $input);
        } else {
            loadStates(countryId, function () {
                applyStateAutocomplete(countryId, $input);
            });
        }

        $input.autocomplete("search", "");
    });

    // ——————— CITY ———————
$(document).on('focus', '.city-autocomplete', function () {
    const $input = $(this);
    const $row = $input.closest('tr');
    const stateId = $row.find('input.state-id').val(); 

    if (!stateId) {
        console.warn('Missing state ID for city dropdown.');
        return;
    }

    if (cityCache[stateId]) {
        applyCityAutocomplete(stateId, $input);
    } else {
        loadCities(stateId, function () {
            applyCityAutocomplete(stateId, $input);
        });
    }
});




    // ——————— ON PAGE LOAD: Autofill country if already selected
    $('.country-autocomplete').each(function () {
        const $input = $(this);
        const $row = $input.closest('tr');
        const countryId = $row.find('input.country-id').val();

        if (countryId) {
            const selected = countryCache.find(c => c.id == countryId);
            if (selected) {
                $input.val(selected.label);
                loadStates(selected.id, function () {
                    applyStateAutocomplete(selected.id, $row.find('.state-autocomplete'));
                });
            }
        }
    });
});

// ——————————— FUNCTIONS
function loadStates(countryId, callback) {
    $.get("{{ route('logistics.route-master.get-states-by-country') }}", { country_id: countryId }, function (response) {
        if (response.status) {
            stateCache[countryId] = response.data.map(s => ({
                label: s.name,
                value: s.name,
                id: s.id
            }));
            if (callback) callback();
        } else {
            console.warn("Error:", response.message);
        }
    });
}

function loadCities(stateId, callback) {
    $.get("{{ route('logistics.route-master.get-cities-by-state') }}", { state_id: stateId }, function (response) {
        if (response.status) {
            cityCache[stateId] = response.data.map(c => ({
                label: c.name,
                value: c.name,
                id: c.id
            }));
            if (callback) callback();
        }
    });
}

function applyStateAutocomplete(countryId, $input) {
    const states = stateCache[countryId] || [];
    const $row = $input.closest('tr');

    $input.autocomplete({
        source: states,
        minLength: 0,
       select: function(event, ui) {
            $(this).val(ui.item.label);
            $(this).closest('tr').find('.state-id').val(ui.item.id); 

            $row.find('.city-autocomplete').val('');
            $row.find('input.city-id').val('');

           const $cityInput = $row.find('.city-autocomplete');
            loadCities(ui.item.id, null, function () {
                applyCityAutocomplete(ui.item.id, null, $cityInput);
            });
        }
    });
}

function applyCityAutocomplete(stateId, $input) {
    const cities = cityCache[stateId] || [];
    const $row = $input.closest('tr');

    $input.autocomplete({
        source: cities,
        minLength: 0,
        select: function(event, ui) {
            $(this).val(ui.item.label);
            $row.find('.city-id').val(ui.item.id); 
            return false;
        }
    }).autocomplete('search', ''); 
}

</script>

@endsection

