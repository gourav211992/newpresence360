@extends('layouts.app')
@section('styles')
    <style type="text/css">
        #map {
            width: 100%;
            height: 550px;
            border: 10px solid #fff;
            box-shadow: 0 0px 20px rgba(0, 0, 0, 0.1);
        }
    </style>

    <style type="text/css">
        #pac-input {
            margin-top: 10px;
            padding: 10px;
            width: 95% !important;
            font-size: 16px;
            position: relative !important;
            left: 0 !important;
            top: 51px !important;
            border: #eee thin solid;
            font-size: 14px;
            border-radius: 6px;
            margin-left: 11px;
        }

        .image-uplodasection {
            position: relative;
            margin-bottom: 10px;
        }

        .fileuploadicon {
            font-size: 24px;
        }



        .delete-img {
            position: absolute;
            top: 5px;
            right: 5px;
            cursor: pointer;
        }

        .preview-image {
            max-width: 100px;
            max-height: 100px;
            display: block;
            margin-top: 10px;
        }
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places" async defer>
    </script>
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6  mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Insurance Asset</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a
                                                href="{{ route('finance.fixed-asset.insurance.index') }}l">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">Add New</li>


                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 ">

                    </div>
                </div>
            </div>
            <div class="content-body">



                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">

                            <div class="card">
                                <div class="card-body customernewsection-form">
                                    <form id="fixed-asset-insurance-form" method="POST"
                                    onsubmit="return validateForm()"
                                        action="{{ route('finance.fixed-asset.insurance.store') }}"
                                        enctype="multipart/form-data">
                                        @csrf
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
                                                        <label class="form-label">Category <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="category_id"
                                                            id="old_category" required>
                                                            @foreach ($categories as $category)
                                                                <option value="{{ $category->id }}"
                                                                    {{ old('category') == $category->id ? 'selected' : '' }}>
                                                                    {{ $category->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Location <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select id="location" class="form-select" name="location_id"
                                                            required>
                                                          
                                                        </select>
                                                    </div>

                                                </div>
                                                <div class="row align-items-center mb-1 cost_center">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Cost Center <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select id="cost_center" class="form-select"
                                                            name="cost_center_id" required>
                                                        </select>
                                                    </div>

                                                </div>
                                                <!-- Asset Code & Name -->
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label for="asset_id" class="form-label">Asset Code & Name <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select name="asset_id" id="asset_id" class="form-select select2"
                                                            required>
                                                            <option value=""
                                                                {{ old('asset_id') == '' ? 'selected' : '' }}>Select
                                                            </option>
                                                            {{-- @foreach ($assets as $asset)
                                                                <option value="{{ $asset->id }}"
                                                                    {{ old('asset_id') == $asset->id ? 'selected' : '' }}>
                                                                    {{ $asset->asset_code }} ({{ $asset->asset_name }})
                                                                </option>
                                                            @endforeach --}}
                                                        </select>
                                                    </div>
                                                </div>
                                                <input type="hidden" id="selectedSubAssets" name="sub_asset" value="">

                                                <div class="row mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Sub-Asset Code <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5 action-button">
                                                        <a type="button" id="modal_asset" data-bs-toggle="modal"
                                                            data-bs-target="#sub_asset_modal"
                                                            class="btn btn-outline-primary btn-sm mb-0"><i
                                                                data-feather="plus-square"></i> Select Sub-Assets</a>

                                                        <div class="d-flex align-items-center my-1"
                                                            id="subAssetBadgeContainer">
                                                        </div>
                                                    </div>
                                                </div>



                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="renewal_date">Renewal Date <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" required id="renewal_date" name="renewal_date" class="form-control" value="{{ old('renewal_date') }}" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="insured_value">Insured Value <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" required id="insured_value" name="insured_value" class="form-control" value="{{ old('insured_value') }}" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="expiry_date">Expiry Date <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" required id="expiry_date" name="expiry_date" class="form-control" value="{{ old('expiry_date') }}" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="policy_no">Policy No. <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" required id="policy_no" name="policy_no" class="form-control" value="{{ old('policy_no') }}" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="lien_security_details">Lien / Security Details <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" required id="lien_security_details" name="lien_security_details" class="form-control" value="{{ old('lien_security_details') }}" />
                                                    </div>
                                                </div>

                                                <!-- Buttons -->
                                                <div class="mt-3">
                                                    <a onClick="javascript: history.go(-1)"
                                                        class="btn btn-secondary btn-sm">
                                                        <i data-feather="arrow-left-circle"></i> Back
                                                    </a>
                                                    <button type="submit" class="btn btn-primary btn-sm ms-1">
                                                        <i data-feather="check-circle"></i> Submit
                                                    </button>
                                                </div>
                                            </div>
                                        </div>


                                    </form>



                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Modal to add new record -->

                </section>


            </div>
        </div>
    </div>
    <!-- END: Content-->
    <div class="modal fade" id="pickuplocation" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalLabel">Selected Sub Assets</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul id="fullSubAssetList"></ul>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade text-start" id="sub_asset_modal" tabindex="-1" aria-labelledby="myModalLabel17"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select Item
                        </h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Asset Code. <span class="text-danger">*</span></label>
                                <select class="form-select filter" name="asset_code" id="asset_code" >
                                    <option value="">Select</option>
                                    @foreach($assets as $asset)
                                        <option value="{{ $asset->id }}">
                                            {{ $asset->asset_code }} ({{ $asset->asset_name }})
                                        </option>
                                    @endforeach

                                </select>
                            </div>
                        </div>
                        <div class="col">
                            <div class="mb-1">
                                <label class="form-label">Sub Asset Code. <span class="text-danger">*</span></label>
                                <select class="form-select filter select2" name="sub_asset_code" id="sub_asset_code">
                                </select>
                            </div>
                        </div>

                        <div class="col  mb-1">
                            <label class="form-label">&nbsp;</label><br />
                        </div>

                        <div class="col-md-12">


                            <div class="table-responsive">
                                <table id="grn_table" class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" id="selectAll"></th>
                                            <th>Asset Code.</th>
                                            <th>Sub Asset Code.</th>
                                            <th>Current Value</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sub_asset">

                                    </tbody>


                                </table>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                            data-feather="x-circle"></i> Cancel</button>
                    <button id="submit_grns" onclick="updateSelectedSubAssets()" class="btn btn-primary btn-sm"><i
                            data-feather="check-circle"></i>
                        Process</button>
                </div>
            </div>
        </div>
    </div>
@section('scripts')

    <script type="text/javascript">
        function showToast(icon, title) {
            const Toast = Swal.mixin({
                toast: true,
                position: "top-end",
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                },
            });
            Toast.fire({
                icon,
                title
            });
        }

        @if (session('success'))
        $('.preloader').hide();
            showToast("success", "{{ session('success') }}");
        @endif

        @if (session('error'))
        $('.preloader').hide();
            showToast("error", "{{ session('error') }}");
        @endif

        @if ($errors->any())
        $('.preloader').hide();
            showToast('error',
                "@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach"
            );
        @endif
        //

         $('#old_category').on('change', function() {
            loadLocation();  
            updateAssetOptions();    
        });
        function loadLocation(selectlocation = null) {
            $('#cost_center').empty();
            $('#cost_center').prop('required', false);
            $('.cost_center').hide();
            if(!$('#old_category').val()) {
                return;
            }
            const url = '{{ route('finance.fixed-asset.get-locations') }}';

            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    category_id: $('#old_category').val(),
                },
                dataType: 'json',
                success: function(data) {
                    const $category = $('#location');
                    $category.empty();

                    $.each(data, function(key, value) {
                        const isSelected = selectlocation == value.id ? ' selected' : '';
                        $category.append('<option value="' + value.id + '"' + isSelected + '>' + value
                            .name + '</option>');
                    });
                    $('#location').trigger('change');
                },
                error: function() {
                    $('#location').empty();
                }
            });
        }
        loadLocation();
        
        // location ,cost center, categories
        $('#location').on('change', function() {
            var locationId = $(this).val();
             var selectedCostCenterId = '{{ $data->cost_center_id ?? '' }}'; 

            if (locationId) {
                // Build the route manually
                var url = '{{ route('finance.fixed-asset.get-cost-centers') }}';

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        location_id: locationId,
                        category_id: $('#old_category').val(),
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.length == 0) {
                            $('#cost_center').empty();
                            $('#cost_center').prop('required', false);
                            $('.cost_center').hide();
                           // loadCategories();
                        } else {
                            $('.cost_center').show();
                            $('#cost_center').prop('required', true);
                            $('#cost_center').empty(); // Clear previous options
                              $.each(data, function (key, value) {
                                let selected = (value.id == selectedCostCenterId) ? 'selected' : '';
                                $('#cost_center').append('<option value="' + value.id + '" ' + selected + '>' + value.name + '</option>');
                            });
                            $('#cost_center').trigger('change');
                            // updateAssetOptions();
                        }
                    },
                    error: function() {
                        $('#cost_center').empty();
                    }
                });
            } else {
                $('#cost_center').empty();
            }
            updateAssetOptions();
        });

        $('#cost_center').on('change', function () {
            updateAssetOptions();
        });

        function getAllAssetIds() {
            let assetIds = [];

            $('#asset_id').each(function () {
                let val = $(this).val();
                if (val) {
                    assetIds.push(parseFloat(val));
                }
            });

            return assetIds;
        }
        function updateAssetOptions() {
            const category = $('#old_category').val();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route("finance.fixed-asset.asset-search") }}',
                type: 'POST',
                dataType: 'json',
                data: {
                    q: '', // optional, if your API needs a search term
                    ids: getAllAssetIds(), // your existing helper
                    category: category,
                    location: $('#location').val(),
                    cost_center: $('#cost_center').val(),
                    category_id: $('#old_category').val(),
                },
                success: function (data) {
                    const $assetSelect = $('#asset_id');
                    $assetSelect.empty(); // clear old options

                    // Add default "Select" option
                    $assetSelect.append(
                        $('<option>', {
                            value: '',
                            text: 'Select'
                        })
                    );

                    // Loop through returned assets
                    if (Array.isArray(data)) {
                        data.forEach(asset => {
                            $assetSelect.append(
                                $('<option>', {
                                    value: asset.id,
                                    text: `${asset.asset_code} (${asset.asset_name})`
                                })
                            );
                        });
                    }
                },
                error: function () {
                    $('#asset_id').empty().append(
                        $('<option>', {
                            value: '',
                            text: 'Select'
                        })
                    );
                }
            });
        }

    </script>
        <script src="{{ asset('assets/js/subasset.js') }}"></script>

@endsection

@endsection
