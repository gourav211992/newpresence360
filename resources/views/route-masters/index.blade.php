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
                                                        <input type="checkbox" id="checkAll">
                                                    </th>
                                                    <th>Location</th>
                                                    <th>Country</th>
                                                    <th>State</th>
                                                    <th>City</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody id="route-rows">
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" class="rowCheckbox" name="selected_rows[]" value="0">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="route_master[0][name]" class="form-control mw-100" placeholder="Location">
                                                    </td>
                                                    <td>
                                                    <input type="text" name="route_master[0][country_name]" class="form-control mw-100 country-autocomplete" placeholder="Country">
                                                    <input type="hidden" name="route_master[0][country_id]" class="country-id">
                                                    </td>
                                                    <td>
                                                    <input type="text" name="route_master[0][state_name]" class="form-control mw-100 state-autocomplete" placeholder="State">
                                                    <input type="hidden" name="route_master[0][state_id]" class="country-id">
                                                        
                                                    </td>
                                                    <td>
                                                    <input type="text" name="route_master[0][city_name]" class="form-control mw-100 city-autocomplete" placeholder="City">
                                                    <input type="hidden" name="route_master[0][city_id]" class="country-id">
                                                    </td>
                                                    <td>
                                                        <select name="route_master[0][status]" class="form-control mw-100 status-dropdown">
                                                            <option value="active" data-color="success">Active</option>
                                                            <option value="inactive" data-color="danger">Inactive</option>
                                                        </select>
                                                    </td>
                                                </tr>
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
    let rowIndex = 1; // next row for dynamic addition

    // ——————————————————————————————————————————————
    // 1) Status color
    function updateStatusColor(select) {
        const color = select.selectedOptions[0].dataset.color;
        const bgMap = { success: '#e8f9e5', danger: '#f8d7da' };
        select.style.backgroundColor = bgMap[color] || '';
    }
    document.querySelectorAll('.status-dropdown').forEach(updateStatusColor);
    document.addEventListener('change', e => {
        if (e.target.matches('.status-dropdown')) {
            updateStatusColor(e.target);
        }
    });


const countryCache = @json($countries->map(fn($c) => ['label' => $c->name, 'value' => $c->name, 'id' => $c->id])->values());
const stateCache = {};
const cityCache = {};

$(document).ready(function () {

    // Country Autocomplete
    $(document).on('focus', '.country-autocomplete', function () {
        const $input = $(this);
        const $row = $input.closest('tr');

        if (!$input.data('ui-autocomplete')) {
            $input.autocomplete({
                source: countryCache,
                minLength: 0,
                select: function (event, ui) {
                    $input.val(ui.label);
                    $row.find('.country-id').val(ui.id);

                    // Reset State & City
                    $row.find('.state-autocomplete').val('');
                    $row.find('.state-id').val('');
                    $row.find('.city-autocomplete').val('');
                    $row.find('.city-id').val('');

                    loadStates(ui.id, function () {
                        applyStateAutocomplete(ui.id, $row.find('.state-autocomplete'));
                    });

                    return false;
                }
            }).focus(function () {
                $(this).autocomplete("search", "");
            });
        }
    });

    // State Autocomplete
    $(document).on('focus', '.state-autocomplete', function () {
        const $input = $(this);
        const $row = $input.closest('tr');
        const countryId = $row.find('.country-id').val();

        if (!countryId) return;

        if (stateCache[countryId]) {
            applyStateAutocomplete(countryId, $input);
        } else {
            loadStates(countryId, function () {
                applyStateAutocomplete(countryId, $input);
            });
        }
    });

    // City Autocomplete
    $(document).on('focus', '.city-autocomplete', function () {
        const $input = $(this);
        const $row = $input.closest('tr');
        const stateId = $row.find('.state-id').val();

        if (!stateId) return;

        if (cityCache[stateId]) {
            applyCityAutocomplete(stateId, $input);
        } else {
            loadCities(stateId, function () {
                applyCityAutocomplete(stateId, $input);
            });
        }
    });

});

function loadStates(countryId, callback) {
    $.ajax({
        url: "{{ route('logistics.route-master.get-states-by-country') }}",
        method: "GET",
        data: { country_id: countryId },
        success: function (response) {
            if (response.status) {
                stateCache[countryId] = response.data.map(state => ({
                    label: state.name,
                    value: state.name,
                    id: state.id
                }));
                if (callback) callback();
            }
        },
        error: function () {
            alert('Error loading states');
        }
    });
}

function loadCities(stateId, callback) {
    $.ajax({
        url: "{{ route('logistics.route-master.get-cities-by-state') }}",
        method: "GET",
        data: { state_id: stateId },
        success: function (response) {
            if (response.status) {
                cityCache[stateId] = response.data.map(city => ({
                    label: city.name,
                    value: city.name,
                    id: city.id
                }));
                if (callback) callback();
            }
        },
        error: function () {
            alert('Error loading cities');
        }
    });
}

function applyStateAutocomplete(countryId, $input) {
    const states = stateCache[countryId] || [];
    const $row = $input.closest('tr');

    $input.autocomplete({
        source: states,
        minLength: 0,
        select: function (e, ui) {
            $input.val(ui.label);
            $row.find('.state-id').val(ui.id);

            $row.find('.city-autocomplete').val('');
            $row.find('.city-id').val('');

            loadCities(ui.id, function () {
                applyCityAutocomplete(ui.id, $row.find('.city-autocomplete'));
            });

            return false;
        }
    }).focus(function () {
        $(this).autocomplete("search", "");
    });
}

function applyCityAutocomplete(stateId, $input) {
    const cities = cityCache[stateId] || [];
    const $row = $input.closest('tr');

    $input.autocomplete({
        source: cities,
        minLength: 0,
        select: function (e, ui) {
            $input.val(ui.label);
            $row.find('.city-id').val(ui.id);
            return false;
        }
    }).focus(function () {
        $(this).autocomplete("search", "");
    });
}


    // ——————————————————————————————————————————————
    // 3) Add Row
    document.getElementById('addRowBtn').addEventListener('click', () => {
        const tbody = document.getElementById('route-rows');
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td><input type="checkbox" class="rowCheckbox" name="selected_rows[]" value="${rowIndex}"></td>
            <td><input type="text" name="route_master[${rowIndex}][name]" class="form-control mw-100" placeholder="Location"></td>
            <td>
              <input type="text" name="route_master[${rowIndex}][country_name]" class="form-control mw-100 country-autocomplete" placeholder="Country">
              <input type="hidden" name="route_master[${rowIndex}][country_id]" class="country-id">
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
        tbody.appendChild(tr);

        // bind newly added row
        bindCountry(tr);
        bindState(tr);
        bindCity(tr);
        updateStatusColor(tr.querySelector('.status-dropdown'));

        rowIndex++;
    });

    // ——————————————————————————————————————————————
    // 4) Delete Selected
    document.getElementById('delete-selected').addEventListener('click', function () {
        const checked = document.querySelectorAll('.rowCheckbox:checked');
        if (!checked.length) {
            Swal.fire({ icon:'warning', title:'No Selection', text:'Select at least one row.' });
            return;
        }
        Swal.fire({
            title: 'Delete rows?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then(res => {
            if (res.isConfirmed) checked.forEach(cb => cb.closest('tr').remove());
        });
    });

    // ——————————————————————————————————————————————
    // 5) Select All
    document.getElementById('checkAll').addEventListener('change', function () {
        document.querySelectorAll('.rowCheckbox').forEach(cb => cb.checked = this.checked);
    });
});
</script>
@endsection

