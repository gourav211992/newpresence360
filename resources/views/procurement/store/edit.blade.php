
@extends('layouts.app')
@section('content')

<form class="ajax-input-form" method="POST" action="{{ route('store.update', $store->id) }}" data-redirect="{{ url('/stores') }}">
<input type="hidden" name="store_id" value="{{ $store->id }}">
   @csrf
    @method('PUT')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Edit Location</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                                        <li class="breadcrumb-item active">Edit Location</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <a href="{{ route('store.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
                            <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                data-url="{{ route('store.destroy', $store->id) }}" 
                                data-redirect="{{ route('store.index') }}"
                                data-message="Are you sure you want to delete this record?">
                                <i data-feather="trash-2" class="me-50"></i> Delete
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0">
                                <i data-feather="check-circle"></i> Update
                            </button>
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
                                            <div class="newheader border-bottom mb-2 pb-25">
                                                <h4 class="card-title text-theme">Basic Information</h4>
                                                <p class="card-text">Fill the details</p>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Organization<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                 <select name="organization_id" id="company" class="form-select select2"  {{ $isStoreReferenced ? 'disabled' : '' }} >
                                                        <option value="" disabled>Select Organization</option>
                                                        @foreach($allOrganizations as $organization)
                                                            <option value="{{ $organization->id }}" data-address='@json($organization->addresses->first())' {{ $store->organization_id == $organization->id ? 'selected' : '' }}>
                                                                {{ $organization->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @if($isStoreReferenced)
                                                        <input type="hidden" name="organization_id" value="{{ $store->organization_id }}">
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Location Code <span class="text-danger">*</span></label>
                                                </div>
                                             
                                                <div class="col-md-5">
                                                    <input type="text" name="store_code" id="store_code" class="form-control" value="{{ $store->store_code }}" {{ $isStoreReferenced ? 'readonly' : '' }}/>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Location Name <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="store_name" id="store_name"  class="form-control" value="{{ $store->store_name }}" {{ $isStoreReferenced ? 'readonly' : '' }} />
                                                </div>
                                            </div>
                                            <!-- <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Location Type<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select name="store_location_type" class="form-select select2" id="store-location-type">
                                                        @foreach ($storeLocationType as $option)
                                                            <option value="{{ $option['value'] }}" 
                                                                {{$store->store_location_type == $option['value'] ? 'selected' : '' }}>
                                                                {{ ucfirst($option['label']) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('store_location_type')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div> -->
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Contact Person</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="contact_person" id="contact_person"  class="form-control" value="{{ $store->contact_person }}"/>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Contact Phone No.</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="contact_phone_no" class="form-control" value="{{ $store->contact_phone_no}}" />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Contact Email-ID</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="email" name="contact_email" class="form-control" value="{{ old('contact_email', $store->contact_email) }}" />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-3 border-start">
                                            <div class="row align-items-center mb-2">
                                                <div class="col-md-12"> 
                                                    <label class="form-label text-primary"><strong>Status</strong></label>   
                                                    <div class="demo-inline-spacing">
                                                        @foreach ($status as $option)
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input
                                                                    type="radio"
                                                                    id="status_{{ strtolower($option) }}"
                                                                    name="status"
                                                                    value="{{ $option }}"
                                                                    class="form-check-input"
                                                                    {{ $store->status == $option ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder" for="status_{{ strtolower($option) }}">
                                                                    {{ ucfirst($option) }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    @error('status')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div> 
                                            </div> 
                                        </div>
                                        <!-- Address Fields in a single row -->
                                        <div class="col-md-12">
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-2">
                                                    <label class="form-label">Address<span class="text-danger">*</span></label>
                                                </div>
                                                <!-- Country -->
                                                <div class="col-md-2" style="margin-left: 25px;">
                                                    <label class="form-label">Country</label>
                                                    <input type="text" name="country_name" id="country_name" class="form-control" placeholder="Select Country" value="{{ old('country_name', $store->address->country->name ?? '') }}" />
                                                    <input type="hidden" name="country_id" id="country_id" value="{{ old('country_id', $store->address->country_id ?? '') }}" />
                                                </div>
                                               <!-- State -->
                                                <div class="col-md-2">
                                                    <label class="form-label">State</label>
                                                    <input type="text" name="state_name" id="state_name" class="form-control" placeholder="Select State" value="{{ old('state_name', $store->address->state->name ?? '') }}" />
                                                    <input type="hidden" name="state_id" id="state_id" value="{{ old('state_id', $store->address->state_id ?? '') }}" />
                                                </div>
                                              <!-- City -->
                                                <div class="col-md-2">
                                                    <label class="form-label">City</label>
                                                    <input type="text" name="city_name" id="city_name" class="form-control" placeholder="Select City" value="{{ old('city_name', $store->address->city->name ?? '') }}" />
                                                    <input type="hidden" name="city_id" id="city_id" value="{{ old('city_id', $store->address->city_id ?? '') }}" />
                                                </div>
                                               <!-- Address -->
                                                <div class="col-md-2">
                                                    <label class="form-label">Address</label>
                                                    <input type="text" name="address" id="address" class="form-control" placeholder="Street" value="{{ old('address', $store->address->address ?? '') }}" />
                                                </div>
                                                <!-- Pin Code -->
                                                <div class="col-md-1">
                                                    <label class="form-label">Pin Code</label>
                                                    <input type="text" name="pincode" id="pincode_display" class="form-control numberonly" placeholder="Select Pincode" value="{{ old('pincode', $store->address->pincode ?? '') }}" />
                                                    <input type="hidden" name="pincode_master_id" id="pincode" value="{{ old('pincode_master_id', $store->address->pincode_master_id ?? '') }}" />
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="mt-2" id="mapping-content" style="opacity: 0.5; pointer-events: none;" data-select2-id="mapping-content">
                                    <div class="step-custhomapp bg-light">
                                        <ul class="nav nav-tabs my-25 custapploannav mw-100" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-bs-toggle="tab" href="#UOM">Mapping with Rack and Shelf</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#Details">Mapping with Bin</a>
                                            </li>
                                            <li class="nav-item ms-auto me-1 mt-25">
                                                <a class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" href="#addaccess">
                                                    <i data-feather="plus-square"></i> Add Rack
                                                </a>
                                            </li>
                                            <li class="nav-item me-1 mt-25">
                                                <a class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" href="#addshelf">
                                                    <i data-feather="plus-square"></i> Add Shelf
                                                </a>
                                            </li>
                                            <li class="nav-item me-1 mt-25">
                                                <a class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" href="#addbin">
                                                    <i data-feather="plus-square"></i> Add Bin
                                                </a>
                                            </li>
                                        </ul>
                                    </div>

                                    <div class="tab-content pb-1 px-1">
                                        <div class="tab-pane active" id="UOM">
                                            <div class="table-responsive-md">
                                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                    <thead>
                                                        <tr>
                                                            <th width="50px">S.NO.</th>
                                                            <th width="150px">Rack Name</th>
                                                            <th width="300px">Shelf</th>
                                                            <th width="70px">Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="rack-shelf-table-body">
                                                        @foreach ($shelfRackMappings as $rackIndex => $rackshelf)
                                                            <tr>
                                                                <td>{{ $rackIndex + 1 }}</td>
                                                                <td>
                                                                    <select class="form-control mw-100 rack-select" placeholder="Select Rack">
                                                                        <option value="{{ $rackshelf->id }}" selected>{{ $rackshelf->rack_code }}</option>
                                                                    </select>
                                                                    <input type="hidden" name="rackshelfmapping[{{ $rackIndex }}][rack_id]" class="rack-id" value="{{ $rackshelf->id }}">
                                                                </td>
                                                                <td>
                                                                    <select class="form-control mw-100 shelf-select" name="rackshelfmapping[{{ $rackIndex }}][shelf_ids][]" multiple placeholder="Select Shelf">
                                                                        @foreach ($rackshelf->shelfs as $shelf)
                                                                            <option value="{{ $shelf->id }}" selected>{{ $shelf->shelf_code }}</option>
                                                                        @endforeach
                                                                      </select>
                                                                </td>
                                                                <td>
                                                                    <a href="#" class="text-danger remove-row">
                                                                        <i data-feather="trash-2" class="me-50"></i>
                                                                    </a>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            <a href="#" class="text-primary" id="add-row">
                                                <i data-feather='plus'></i> Add New
                                            </a>
                                        </div>
                                        <div class="tab-pane" id="Details">
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-1">
                                                    <label class="form-label">BIN No.</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="mb-1">
                                                        @if ($store->bins->count() > 0)
                                                            <select class="form-control mw-100 bin-select" name="storebinmapping[bin_ids][]" multiple>
                                                                @foreach ($store->bins as $bin)
                                                                    <option value="{{ $bin->id }}" {{ in_array($bin->id, old('storebinmapping.bin_ids', $store->bins->pluck('id')->toArray())) ? 'selected' : '' }}>
                                                                        {{ $bin->bin_code }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        @else
                                                            <select class="form-control mw-100 bin-select" name="storebinmapping[bin_ids][]" multiple>
                                                                <option value="">No Bins Available</option>
                                                            </select>
                                                        @endif
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
        </div>
    </div>
    @include('procurement.store.rack-modal')
    @include('procurement.store.shelf-modal')
    @include('procurement.store.bin-modal')
</form>
@endsection
@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const csrfToken = $('meta[name="csrf-token"]').attr('content');
        const storeId = document.querySelector('input[name="store_id"]').value;
        let racks = [];
        let shelfs = [];
        let bins = [];

        function setupAutocomplete() {
            $('#rack_code').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '{{ route("store.searchRacks") }}',
                        method: 'GET',
                        data: { query: request.term, store_id: storeId },
                        success: function(data) {
                            response(data.map(item => item.rack_code));  
                        }
                    });
                }
            });

            $('#shelf_code').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '{{ route("store.searchShelves") }}',
                        method: 'GET',
                        data: { query: request.term, store_id: storeId },
                        success: function(data) {
                            response(data.map(item => item.shelf_code));
                        }
                    });
                }
            });

            $('#bin_code').autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '{{ route("store.searchBins") }}',
                        method: 'GET',
                        data: { query: request.term, store_id: storeId },
                        success: function(data) {
                            response(data.map(item => item.bin_code));
                        }
                    });
                }
            });
        }

        function handleSearchClick(type) {
            $(`#search-${type}`).on('click', function(e) {
                e.preventDefault(); 
                triggerSearch(type);
            });

            $(`#${type}_code`).on('input', function() {
                triggerSearch(type); 
            });
            function triggerSearch(type) {
                const code = $(`#${type}_code`).val().trim();

                let url;
                if (type === 'rack') url = '{{ route("store.searchRacks") }}';
                if (type === 'shelf') url = '{{ route("store.searchShelves") }}';
                if (type === 'bin') url = '{{ route("store.searchBins") }}';
                if (code) {
                    $.ajax({
                        url: url,
                        method: 'GET',
                        data: { query: code, store_id: storeId },
                        success: function(data) {
                            populateList(data, type);
                        },
                        error: function() {
                            alert(`An error occurred while searching for ${type}s.`);
                        }
                    });
                } else {
                    $.ajax({
                        url: url,
                        method: 'GET',
                        data: { store_id: storeId },
                        success: function(data) {
                            populateList(data, type);
                        },
                        error: function() {
                            alert(`An error occurred while fetching all ${type}s.`);
                        }
                    });
                }
            }
        }

        function populateList(items, type) {
            const list = $(`#${type}-list`);
            list.empty(); 
            if (items.length === 0) {
                const noRecordsRow = `<tr><td colspan="3" class="text-center">Record not found</td></tr>`;
                list.append(noRecordsRow);
            } else {
                items.forEach((item, index) => {
                    const row = createRow(type, item[`${type}_code`], item.id, index + 1);
                    list.append(row);
                });
            }
            feather.replace(); 
        }

        function addItem(type, code, url, itemArray) {
            const isDuplicate = itemArray.some(item => item[`${type}_code`] === code);
            if (isDuplicate) {
                alert(`Duplicate ${type} creation not allowed.`);
                return;
            }
            const newRow = createRow(type, code, null, itemArray.length + 1);
            document.getElementById(`${type}-list`).appendChild(newRow);
            itemArray.push({ [`${type}_code`]: code });
            feather.replace();

            $.ajax({
                url: url,
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    [`${type}s`]: [{ [`${type}_code`]: code }], 
                    store_id: storeId
                }),
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function (data) {
                    if (data.message && data.message.trim() !== "") {
                        alert(data.message);
                        document.getElementById(`${type}_code`).value = ""; 
                        feather.replace(); 
                        updateIndices(type);
                        let fetchUrl = '';
                        if (type === 'rack') {
                            fetchUrl = "{{ route('store.getRacks') }}";
                        } else if (type === 'shelf') {
                            fetchUrl = "{{ route('store.getShelves') }}";
                        } else if (type === 'bin') {
                            fetchUrl = "{{ route('store.getBins') }}";
                        }
                        fetchItems(fetchUrl, type);
                        console.log(`New ${type} added:`, { [`${type}_code`]: code });
                    } else {
                        alert("An Error Occured.");
                    }
                },
                error: function (jqXHR) {
                    alert(`An error occurred while saving the ${type}: ${jqXHR.responseText}`);
                }
            });
        }


        function createRow(type, code, id, index) {
            const newRow = document.createElement("tr");
            newRow.setAttribute('data-type', type);
            newRow.innerHTML = `
                <td>${index}</td>  
                <td>${code || 'N/A'}</td>
                <input type="hidden" name="${type}s[${index}][${type}_code]" value="${code}">
                <input type="hidden" name="${type}s[${index}][${type}_id]" value="${id || ''}">
                <td>
                    <a href="#" class="text-danger remove-${type}">
                        <i data-feather="trash-2" class="me-50"></i>
                    </a>
                </td>
            `;
            return newRow;
        }

        function deleteItem(type, id) {
            const url = `/stores/${type}s/${id}`;
            $.ajax({
                url: url,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                success: function (data) {
                    if (data.status) {
                        alert(data.message); 
                        feather.replace(); 
                        let fetchUrl = '';
                        if (type === 'rack') {
                            fetchUrl = "{{ route('store.getRacks') }}";
                        } else if (type === 'shelf') {
                            fetchUrl = "{{ route('store.getShelves') }}";
                        } else if (type === 'bin') {
                            fetchUrl = "{{ route('store.getBins') }}";
                        }
                        fetchItems(fetchUrl, type);
                    } else {
                        alert('Record cannot be deleted because it is already in use.'); 
                    }
                },
                error: function (jqXHR) {
                    alert(`An error occurred while deleting the ${type}: ${jqXHR.responseText}`);
                }
            });
        }

        function updateIndices(type) {
            const list = document.getElementById(`${type}-list`);
            const rows = list.querySelectorAll('tr');
            rows.forEach((row, index) => {
                const indexCell = row.querySelector('td');
                if (indexCell) {
                    indexCell.textContent = index + 1;
                }
            });
        }

        ['rack', 'shelf', 'bin'].forEach(type => {
            document.getElementById(`${type}-list`).addEventListener('click', function(event) {
                if (event.target.closest(`.remove-${type}`)) {
                    const row = event.target.closest('tr');
                    const idInput = row.querySelector(`input[type="hidden"][name$="[${type}_id]"]`);
                    const id = idInput.value;
                    deleteItem(type, id);
                    row.remove();
                }
            });
        });

        function fetchItems(url, type) {
            $.ajax({
                url: url,
                method: 'GET',
                data: { store_id: storeId },
                success: function (data) {
                    populateList(data, type);
                    if (type === 'rack') {
                        racks = data;
                    } else if (type === 'shelf') {
                        shelfs = data;
                    } else if (type === 'bin') {
                        bins = data;
                    }
                },
                error: function (jqXHR) {
                    alert(`An error occurred while fetching items: ${jqXHR.responseText}`);
                }
            });
        }

        fetchItems("{{ route('store.getRacks') }}", 'rack');
        fetchItems("{{ route('store.getShelves') }}", 'shelf');
        fetchItems("{{ route('store.getBins') }}", 'bin');

        setupAutocomplete();
        ['rack', 'shelf', 'bin'].forEach(type => handleSearchClick(type));

        function addRack() {
            const rackCode = document.getElementById('rack_code').value.trim();
            if (rackCode) {
                addItem('rack', rackCode, '/stores/rack', racks);
            } else {
                alert('Please enter a rack code.');
            }
        }

        function addShelf() {
            const shelfCode = document.getElementById('shelf_code').value.trim();
            if (shelfCode) {
                addItem('shelf', shelfCode, '/stores/shelf', shelfs);
            } else {
                alert('Please enter a shelf code.');
            }
        }

        function addBin() {
            const binCode = document.getElementById('bin_code').value.trim();
            if (binCode) {
                addItem('bin', binCode, '/stores/bin', bins);
            } else {
                alert('Please enter a bin code.');
            }
        }

        ['rack', 'shelf', 'bin'].forEach(type => {
            document.getElementById(`${type}_code`).addEventListener('keypress', function(event) {
                if (event.key === 'Enter') {
                    event.preventDefault(); 
                    if (type === 'rack') {
                        addRack();
                    } else if (type === 'shelf') {
                        addShelf();
                    } else if (type === 'bin') {
                        addBin();
                    }
                }
            });
        });
    });
</script>

<script>
$(document).ready(function() {
    function initializeSelect2(selectElement, url, hiddenFieldClass, isMultiple = false) {
        selectElement.select2({
            placeholder: 'Select Option',
            multiple: isMultiple,
            ajax: {
                url: url,
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        term: params.term || '',
                        limit: 10,
                        store_id: $('input[name="store_id"]').val()
                    };
                },
                processResults: function(data) {
                    return {
                    results: data.map(item => ({
                        id: item.id,
                        text: item.rack_code || item.shelf_code || item.bin_code,
                    }))
                    };
                },
                cache: true
            }
        });
    }
    initializeSelect2($('.rack-select'), '{{ route("store.getMappedRacks") }}', 'rack-id');
    initializeSelect2($('.shelf-select'), '{{ route("store.getMappedShelves") }}', 'shelf-id', true);
    initializeSelect2($('.bin-select'), '{{ route("store.getMappedBins") }}', 'bin-id', true);
    $('#add-row').on('click', function(e) {
        e.preventDefault();
        let rowCount = $('#rack-shelf-table-body tr').length;
        let newRow = `<tr>
                          <td>${rowCount + 1}</td>
                          <td>
                              <select class="form-control mw-100 rack-select" name="rackshelfmapping[${rowCount}][rack_id]" placeholder="Select Rack"></select>
                          </td>
                          <td>
                              <select class="form-control mw-100 shelf-select" name="rackshelfmapping[${rowCount}][shelf_ids][]" multiple placeholder="Select Shelf"></select>
                          </td>
                          <td>
                              <a href="#" class="text-danger remove-row"><i data-feather="trash-2" class="me-50"></i></a>
                          </td>
                      </tr>`;
        $('#rack-shelf-table-body').append(newRow);
        initializeSelect2($('#rack-shelf-table-body .rack-select').last(), '{{ route("store.getMappedRacks") }}', 'rack-id');
        initializeSelect2($('#rack-shelf-table-body .shelf-select').last(), '{{ route("store.getMappedShelves") }}', 'shelf-id', true);
        initializeSelect2($('#rack-shelf-table-body .bin-select').last(), '{{ route("store.getMappedBins") }}', 'bin-id', true);
        feather.replace(); 
    });
    $(document).on('click', '.remove-row', function(e) {
        e.preventDefault();
        $(this).closest('tr').remove();
        updateRowNumbers();
        feather.replace(); 
    });
    function updateRowNumbers() {
        $('#rack-shelf-table-body tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }
});
</script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const mappingContent = document.getElementById('mapping-content');
        const storeLocationSelect = document.getElementById('store-location-type')
        const companySelect = document.getElementById('company');
        const storeCodeInput = document.getElementById('store_code');
        const storeNameInput = document.getElementById('store_name');

        function checkFields() {
            const isCompanySelected = companySelect.value !== '';
            const isStoreCodeFilled = storeCodeInput.value.trim() !== '';
            const isStoreNameFilled = storeNameInput.value.trim() !== '';
            const isStoreLocationStock = storeLocationSelect.value === 'Stock'; 

            if (isCompanySelected && isStoreCodeFilled && isStoreNameFilled && isStoreLocationStock) {
                mappingContent.style.opacity = '1'; 
                mappingContent.style.pointerEvents = 'auto';
                const selects = mappingContent.querySelectorAll('select');
                selects.forEach(select => select.removeAttribute('disabled'));
            } else {
                mappingContent.style.opacity = '0.5'; 
                mappingContent.style.pointerEvents = 'none'; 
                const selects = mappingContent.querySelectorAll('select');
                selects.forEach(select => select.setAttribute('disabled', 'true'));
            }
        }
        companySelect.addEventListener('change', checkFields);
        storeCodeInput.addEventListener('input', checkFields);
        storeNameInput.addEventListener('input', checkFields);
        storeLocationSelect.addEventListener('change', checkFields);
        checkFields();
    });
</script>

<script>
$(document).ready(function() {
    const $companySelect = $('#company');
    const $countryInput = $('#country_name');
    const $countryId = $('#country_id');
    const $stateInput = $('#state_name');
    const $stateId = $('#state_id');
    const $cityInput = $('#city_name');
    const $cityId = $('#city_id');
    const $addressInput = $('#address');
    const $pincodeDisplay = $('#pincode_display');
    const $pincode = $('#pincode');

    // Initialize autocomplete for country
    $countryInput.autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '/countries',
                dataType: "json",
                data: { term: request.term },
                success: function(data) {
                    response($.map(data.data.countries, function(item) {
                        return {
                            label: item.label,
                            value: item.value
                        };
                    }));
                }
            });
        },
        minLength: 0,
        select: function(event, ui) {
            $(this).val(ui.item.label);
            $countryId.val(ui.item.value);
            resetStateAndCity();
            fetchStates(ui.item.value, null, null);
            return false;
        }
    }).focus(function() {
        $(this).autocomplete("search", "");  
    });

    // Initialize autocomplete for state
    $stateInput.autocomplete({
        source: function(request, response) {
            const countryId = $countryId.val();
            if (countryId) {
                $.ajax({
                    url: '/states/' + countryId,
                    dataType: "json",
                    data: { term: request.term },
                    success: function(data) {
                        response($.map(data.data.states, function(item) {
                            return {
                                label: item.label,
                                value: item.value
                            };
                        }));
                    }
                });
            } else {
                response([]);
            }
        },
        minLength: 0,
        select: function(event, ui) {
            $(this).val(ui.item.label);
            $stateId.val(ui.item.value);
            resetCity();
            resetPinCode();
            fetchCities(ui.item.value, null);
            return false;
        }
    }).focus(function() {
        $(this).autocomplete("search", "");  
    });

    // Initialize autocomplete for city
    $cityInput.autocomplete({
        source: function(request, response) {
            const stateId = $stateId.val();
            if (stateId) {
                $.ajax({
                    url: '/cities/' + stateId,
                    dataType: "json",
                    data: { term: request.term },
                    success: function(data) {
                        response($.map(data.data.cities, function(item) {
                            return {
                                label: item.label,
                                value: item.value
                            };
                        }));
                    }
                });
            } else {
                response([]);
            }
        },
        minLength: 0,
        select: function(event, ui) {
            $(this).val(ui.item.label);
            $cityId.val(ui.item.value);
            return false;
        }
    }).focus(function() {
        $(this).autocomplete("search", ""); 
    });

     // Initialize autocomplete for pincode
     $pincodeDisplay.autocomplete({
        source: function(request, response) {
            const stateId = $stateId.val();
            if (stateId) {
                $.ajax({
                    url: '/pincodes/' + stateId,
                    dataType: "json",
                    data: { term: request.term },
                    success: function(data) {
                        response($.map(data.data.pincodes, function(item) {
                            return {
                                label: item.label,
                                value: item.value
                            };
                        })); // <-- FIX: Added closing parenthesis here
                    }
                });
            } else {
                response([]);
            }
        },
        minLength: 0,
        select: function(event, ui) {
            $(this).val(ui.item.label);
            $pincode.val(ui.item.value);
            return false;
        }
    }).focus(function() {
        $(this).autocomplete("search", ""); 
    });
    // Rest of your existing functionality
    initializeLocationSelectors();
    
    $companySelect.change(function() {
        const selectedOption = $companySelect.find('option:selected');
        const addressData = selectedOption.data('address');
        resetAddressFields();

        if (addressData) {
            $addressInput.val(addressData.line_1 || ''); 
            $pincodeDisplay.val(addressData.postal_code || ''); 
            $pincode.val(addressData.pincode_master_id || '');
            if (addressData.country_id) {
                fetchCountries(addressData.country_id, addressData.state_id, addressData.city_id, addressData.pincode_master_id);
            }
        } else {
            maintainSelectedValues();
        }
    });

    function resetAddressFields() {
        $countryInput.val('');
        $countryId.val('');
        $stateInput.val('');
        $stateId.val('');
        $cityInput.val('');
        $cityId.val('');
        $addressInput.val(''); 
        $pincodeDisplay.val(''); 
        $pincode.val('');
    }

    function resetStateAndCity() {
        $stateInput.val('');
        $stateId.val('');
        resetCity();
        resetPinCode();
    }

    function resetCity() {
        $cityInput.val('');
        $cityId.val('');
    }

    function resetPinCode() {
        $pincodeDisplay.val('');
        $pincode.val('');
    }

    function maintainSelectedValues() {
        const selectedCountryId = $countryId.val();
        const selectedStateId = $stateId.val();
        const selectedCityId = $cityId.val();
        const selectedPincodeId = $pincode.val();

        if (selectedCountryId) {
            fetchCountries(selectedCountryId, selectedStateId, selectedCityId, selectedPincodeId);
        }
    }

    function isCustomAddress() {
        return $addressInput.val() !== '' || $pincode.val() !== '' || $countryId.val() !== '' || $stateId.val() !== '' || $cityId.val() !== '';
    }

    function fetchCountries(selectedCountryId, selectedStateId, selectedCityId, selectedPincodeId) {
        $.ajax({
            url: '/countries', 
            method: 'GET',
            success: function(data) {
                const country = data.data.countries.find(c => c.value == selectedCountryId);
                if (country) {
                    $countryInput.val(country.label);
                    $countryId.val(country.value);
                    fetchStates(selectedCountryId, selectedStateId, selectedCityId, selectedPincodeId);
                }
            },
            error: function() {
                console.error('Error fetching countries');
            }
        });
    }

    function fetchStates(countryId, selectedStateId, selectedCityId, selectedPincodeId) {
        $.ajax({
            url: '/states/' + countryId, 
            method: 'GET',
            success: function(data) {
                if (selectedStateId) {
                    const state = data.data.states.find(s => s.value == selectedStateId);
                    if (state) {
                        $stateInput.val(state.label);
                        $stateId.val(state.value);
                        fetchCities(selectedStateId, selectedCityId);
                        fetchPincodes(selectedStateId, selectedPincodeId);
                    }
                }
            },
            error: function() {
                console.error('Error fetching states');
            }
        });
    }

    function fetchCities(stateId, selectedCityId) {
        $.ajax({
            url: '/cities/' + stateId, 
            method: 'GET',
            success: function(data) {
                if (selectedCityId) {
                    const city = data.data.cities.find(c => c.value == selectedCityId);
                    if (city) {
                        $cityInput.val(city.label);
                        $cityId.val(city.value);
                    }
                }
            },
            error: function() {
                console.error('Error fetching cities');
            }
        });
    }

    function fetchPincodes(stateId, selectedPincodeId) {

        $.ajax({
            url: '/pincodes/' + stateId, 
            method: 'GET',
            success: function(data) {
                if (selectedPincodeId) {
                    const pincode = data.data.pincodes.find(p => p.label == selectedPincodeId);
                    if (pincode) {
                        $pincodeDisplay.val(pincode.label);
                        $pincode.val(pincode.value);
                    }
                }
            },
            error: function() {
                console.error('Error fetching pincodes');
            }
        });
    }

    function initializeLocationSelectors() {
        const selectedOrganization = $("#company option:selected");
        const addressData = selectedOrganization.data("address");
        if (addressData && !isCustomAddress()) {
            console.log(addressData);
            $addressInput.val(addressData.line_1 || '');
            $pincodeDisplay.val(addressData.postal_code || ''); 
            $pincode.val(addressData.postal_code || '');
            const selectedCountryId = addressData.country_id;
            const selectedStateId = addressData.state_id;
            const selectedCityId = addressData.city_id;
            const selectedPincodeId = addressData.postal_code;
            fetchCountries(selectedCountryId, selectedStateId, selectedCityId, selectedPincodeId); 
        } else {
            maintainSelectedValues();
        }
    }
});
</script>

<script>
    $(document).ready(function() {
        function applyCapsLock() {
            $('input[type="text"], input[type="number"]').each(function() {
                $(this).val($(this).val().toUpperCase());
            });
            $('input[type="text"], input[type="number"]').on('input', function() {
                var value = $(this).val().toUpperCase();  
                $(this).val(value); 
            });
        }
        applyCapsLock();
    });
 </script>
@endsection
