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
                                        <li class="breadcrumb-item active">Edit Details</li>


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

                                        action="{{ route('finance.fixed-asset.insurance.update',$data->id) }}"
                                        enctype="multipart/form-data">
                                        @csrf
                                        @method('PUT')
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
                                                        <select class="form-select select2" name="old_category_id"
                                                            id="old_category" disabled required>
                                                            @foreach ($categories as $category)
                                                                <option value="{{ $category->id }}"
                                                                    {{ $data->category_id == $category->id ? 'selected' : '' }}>
                                                                    {{ $category->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                {{-- location --}}
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Location <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        {{-- {{ dd($data->asset) }} --}}
                                                        <select id="location" disabled class="form-select"
                                                            name="location_id" required>
                                                             <option value="">Select</option>
                                                            @foreach ($locations as $location)
                                                                <option value="{{ $location->id }}" {{ $data->location_id == $location->id ? 'selected' : '' }}>
                                                                    {{ $location->store_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                </div>
                                                {{-- costcenter & categories --}}
                                                <div class="row align-items-center mb-1 cost_center">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Cost Center <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select id="cost_center" disabled class="form-select"
                                                            name="cost_center_id" required>
                                                        </select>
                                                    </div>

                                                </div>
                                                {{-- <div class="row align-items-center mb-1">
                                                     <div class="col-md-3">

                                                            <label class="form-label">Category <span
                                                                    class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <select class="form-select select2" disabled required name="category_id"
                                                                id="category" required>
                                                               </select>
                                                        </div>
                                                    </div> --}}
                                                <!-- Asset Code & Name -->
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label for="asset_id" class="form-label">Asset Code & Name <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select name="asset_id" id="asset_id" disabled class="form-select select2"
                                                            required>
                                                            <option value=""
                                                                {{ $data->asset_id == '' ? 'selected' : '' }}>Select
                                                            </option>
                                                            @foreach ($assets as $asset)
                                                                <option value="{{ $asset->id }}"
                                                                    {{ $data->asset_id == $asset->id ? 'selected' : '' }}>
                                                                    {{ $asset->asset_code }} ({{ $asset->asset_name }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <input type="hidden" id="selectedSubAssets" name="sub_asset" value="{{$data->sub_asset}}">

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
                                                        <input type="date" required id="renewal_date" name="renewal_date" class="form-control" value="{{ $data->renewal_date }}" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="insured_value">Insured Value <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" required id="insured_value" name="insured_value" class="form-control" value="{{ $data->insured_value }}" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="expiry_date">Expiry Date <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" required id="expiry_date" name="expiry_date" class="form-control" value="{{ $data->expiry_date }}" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="policy_no">Policy No. <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" required id="policy_no" name="policy_no" class="form-control" value="{{ $data->policy_no }}" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="lien_security_details">Lien / Security Details <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" required id="lien_security_details" name="lien_security_details" class="form-control" value="{{ $data->lien_security_details }}" />
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
                                <select class="form-select filter" name="asset_code" id="asset_code" disabled>
                                    <option value="" {{ $data->asset_id == '' ? 'selected' : '' }}>Select</option>
                                    @foreach($assets as $asset)
                                        <option value="{{ $asset->id }}" {{ $data->asset_id == $asset->id ? 'selected' : '' }}>
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
    <!-- END: Content-->
@section('scripts')

    <script type="text/javascript">
    $(document).ready(function() {
    edit_page();
    loadCategoriesOnSelection();
});
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

        function loadCategoriesOnSelection() {
            const locationId = $("#location").val();
            const selectedCostCenterId = '{{ $data->cost_center_id ?? '' }}';
            if (locationId) {
                const url = '{{ route("cost-center.get-cost-center", ":id") }}'.replace(':id', locationId);

                $.ajax({
                    url: url,
                    type: 'GET',
                    dataType: 'json',
                    success: function (data) {
                        const $costCenter = $('#cost_center');
                        $costCenter.empty();

                        if (data.length === 0) {
                            $costCenter.prop('required', false);
                            $('.cost_center').hide();
                        } else {
                            $costCenter.prop('required', true).append('<option value="">Select Cost Center</option>');
                            $('.cost_center').show();

                            $.each(data, function (key, value) {
                                const selected = (value.id == selectedCostCenterId) ? 'selected' : '';
                                $costCenter.append('<option value="' + value.id + '" ' + selected + '>' + value.name + '</option>');
                            });
                        }

                        // Now get the updated costCenterId value
                        const costCenterId = selectedCostCenterId || $('#cost_center').val();
                    },
                    error: function () {
                        $('#cost_center').empty();
                    }
                });
            } else {
                $('#cost_center').empty();
            }
        }
    </script>
        <script src="{{ asset('assets/js/subasset.js') }}"></script>

@endsection

@endsection
