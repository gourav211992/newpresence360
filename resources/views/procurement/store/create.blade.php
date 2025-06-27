@extends('layouts.app')
@section('content')

<form class="ajax-input-form" method="POST" action="{{ route('store.store') }}" data-redirect="{{ url('/stores') }}">
    @csrf
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">New Location</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                                        <li class="breadcrumb-item active">Add New</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                           <a href="{{ route('store.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
                            <button type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0">
                                <i data-feather="check-circle"></i> Create
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
                                            <!-- Existing Fields -->
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Organization<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select name="organization_id" id="company" class="form-select select2">
                                                        <option value="">Select Organization</option>
                                                        @foreach($allOrganizations as $organization)
                                                            <option value="{{ $organization->id }}" data-address='@json($organization->addresses->first())'>
                                                                {{ $organization->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Location Code <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="store_code" id="store_code" class="form-control"  />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Location Name <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="store_name" id="store_name" class="form-control" />
                                                </div>
                                            </div>

                                            <!-- <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Store Location Type<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select name="store_location_type" class="form-select select2">
                                                        @foreach ($storeLocationType as $option)
                                                            <option value="{{ $option['value'] }}">
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
                                                    <input type="text" name="contact_person"  id="store_name" class="form-control"  />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Contact Phone No.</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="contact_phone_no" class="form-control" />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Contact Email-ID</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="email" name="contact_email" class="form-control" />
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
                                                                        {{ $option == 'active' ? 'checked' : '' }} >
                                                                        <label class="form-check-label fw-bolder" for="status_{{ strtolower($option) }}">
                                                                            {{ucfirst($option)}}
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
                                    </div>
                                    <div class="col-md-12">
                                        <div class="row align-items-center mb-1">
                                            <div class="col-md-2">
                                                <label class="form-label">Address<span class="text-danger">*</span></label>
                                            </div>
                                            <!-- Country -->
                                            <div class="col-md-2" style="margin-left: 25px;">
                                                <label class="form-label">Country</label>
                                                <input type="text" name="country_name" id="country_name" class="form-control" placeholder="Select Country" />
                                                <input type="hidden" name="country_id" id="country_id" />
                                            </div>

                                            <!-- State -->
                                            <div class="col-md-2">
                                                <label class="form-label">State</label>
                                                <input type="text" name="state_name" id="state_name" class="form-control" placeholder="Select State" />
                                                <input type="hidden" name="state_id" id="state_id" />
                                            </div>

                                            <!-- City -->
                                            <div class="col-md-2">
                                                <label class="form-label">City</label>
                                                <input type="text" name="city_name" id="city_name" class="form-control" placeholder="Select City" />
                                                <input type="hidden" name="city_id" id="city_id" />
                                            </div>

                                            <!-- Address -->
                                            <div class="col-md-2">
                                                <label class="form-label">Address</label>
                                                <input type="text" name="address" id="address" class="form-control" placeholder="Street" />
                                            </div>

                                            <!-- Pin Code -->
                                            <div class="col-md-1">
                                                <label class="form-label">Pin Code</label>
                                                <input type="text" name="pincode" id="pincode_display" class="form-control numberonly" placeholder="Select Pincode" />
                                                <input type="hidden" name="pincode_master_id" id="pincode" />
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

