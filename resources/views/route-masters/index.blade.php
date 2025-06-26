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

    // ——————————————————————————————————————————————
    // 2) Autocomplete wiring
    function bindCountry($row) {
        $row.querySelector('.country-autocomplete').addEventListener('input', debounce(function () {
            fetch(`{{ route('logistics.route-master.countries') }}?term=${this.value}`)
              .then(r => r.json()).then(list => showSuggestions(this, list));
        }, 300));
        $row.querySelector('.country-autocomplete').addEventListener('change', function () {
            // clear downstream
            $row.querySelector('.country-id').value = this.dataset.selectedId || '';
            $row.querySelector('.state-autocomplete, .state-id').forEach(el => el.value = '');
            $row.querySelector('.city-autocomplete, .city-id').forEach(el => el.value = '');
        });
    }
    function bindState($row) {
        $row.querySelector('.state-autocomplete').addEventListener('input', debounce(function () {
            const cid = $row.querySelector('.country-id').value;
            if (!cid) return;
            fetch(`/logistics/route-master/states/${cid}?term=${this.value}`)
              .then(r => r.json()).then(list => showSuggestions(this, list));
        }, 300));
        $row.querySelector('.state-autocomplete').addEventListener('change', function () {
            $row.querySelector('.state-id').value = this.dataset.selectedId || '';
            $row.querySelector('.city-autocomplete, .city-id').forEach(el => el.value = '');
        });
    }
    function bindCity($row) {
        $row.querySelector('.city-autocomplete').addEventListener('input', debounce(function () {
            const sid = $row.querySelector('.state-id').value;
            if (!sid) return;
            fetch(`/logistics/route-master/cities/${sid}?term=${this.value}`)
              .then(r => r.json()).then(list => showSuggestions(this, list));
        }, 300));
        $row.querySelector('.city-autocomplete').addEventListener('change', function () {
            $row.querySelector('.city-id').value = this.dataset.selectedId || '';
        });
    }

    // Shared helpers
    function showSuggestions(input, list) {
        // remove old list
        let dd = input.nextElementSibling;
        if (dd && dd.classList.contains('autocomplete-list')) dd.remove();

        // build new dropdown
        dd = document.createElement('ul');
        dd.className = 'autocomplete-list list-group position-absolute';
        list.forEach(item => {
            const li = document.createElement('li');
            li.className = 'list-group-item list-group-item-action';
            li.textContent = item.label;
            li.addEventListener('click', () => {
                input.value = item.label;
                input.dataset.selectedId = item.id;
                dd.remove();
                input.dispatchEvent(new Event('change'));
            });
            dd.append(li);
        });
        input.after(dd);
    }
    function debounce(fn, delay) {
        let t;
        return function() {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, arguments), delay);
        };
    }

    // Bind existing row
    document.querySelectorAll('#route-rows tr').forEach(tr => {
        bindCountry(tr);
        bindState(tr);
        bindCity(tr);
    });

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

