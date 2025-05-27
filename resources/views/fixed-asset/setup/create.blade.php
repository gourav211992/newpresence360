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
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Setup</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a
                                                href="{{ route('finance.fixed-asset.setup.index') }}">Home</a>
                                        </li>
                                        <li class="breadcrumb-item active">Add New</li>


                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="arrow-left-circle"></i> Back</button>
                            <button form="setup" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="check-circle"></i> Create</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

                <form id="setup" method="POST" action="{{ route('finance.fixed-asset.setup.store') }}"
                    enctype="multipart/form-data">


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
                                            <input type="hidden" name="asset_category_id" id="asset_category_id">
                                            <div class="col-md-9">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Asset Category <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" id="asset_category" name="asset_category"
                                                            class="form-control" placeholder="Enter Category Name"
                                                            value="{{ old('asset_category') }}" required />
                                                        @error('asset_category')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Ledger <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="ledger_id" id="ledger"
                                                            required>
                                                            <option value="" {{ old('ledger') ? '' : 'selected' }}>
                                                                Select</option>
                                                            @foreach ($ledgers as $ledger)
                                                                <option value="{{ $ledger->id }}"
                                                                    {{ old('ledger') == $ledger->id ? 'selected' : '' }}>
                                                                    {{ $ledger->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Ledger Group <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" name="ledger_group_id" id="ledger_group"
                                                            required>
                                                        </select>

                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Expected Life in Yrs. <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="number" class="form-control"
                                                            name="expected_life_years" required
                                                            value="{{ old('expected_life_years') }}" />
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Maintenance Schedule</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" name="maintenance_schedule">
                                                            <option value=""
                                                                {{ old('maintenance_schedule') == '' ? 'selected' : '' }}>
                                                                Select</option>
                                                            <option value="weekly"
                                                                {{ old('maintenance_schedule') == 'Weekly' ? 'selected' : '' }}>
                                                                Weekly</option>
                                                            <option value="monthly"
                                                                {{ old('maintenance_schedule') == 'Monthly' ? 'selected' : '' }}>
                                                                Monthly</option>
                                                            <option value="quarterly"
                                                                {{ old('maintenance_schedule') == 'Quarterly' ? 'selected' : '' }}>
                                                                Quarterly</option>
                                                            <option value="semi-annually"
                                                                {{ old('maintenance_schedule') == 'Semi-Annually' ? 'selected' : '' }}>
                                                                Semi-Annually</option>
                                                            <option value="annually"
                                                                {{ old('maintenance_schedule') == 'Annually' ? 'selected' : '' }}>
                                                                Annually</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Dep. Ledger <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="dep_ledger_id"
                                                            id="dep_ledger" required>
                                                            <option value="" {{ old('ledger') ? '' : 'selected' }}>
                                                                Select</option>
                                                            @foreach ($dep_ledgers as $ledgeri)
                                                                <option value="{{ $ledgeri->id }}"
                                                                    {{ $dep_ledger_id == $ledgeri->id ? 'selected' : '' }}>
                                                                    {{ $ledgeri->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Dep. Ledger Group <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" name="dep_ledger_group_id"
                                                            id="dep_ledger_group" required>
                                                        </select>

                                                    </div>
                                                </div>


                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Rev. Ledger </label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="rev_ledger_id"
                                                            id="rev_ledger">
                                                            <option value=""
                                                                {{ old('rev_ledger') ? '' : 'selected' }}>Select</option>
                                                            @foreach ($sales_exp_ledgers as $rev)
                                                                <option value="{{ $rev->id }}">
                                                                    {{ $rev->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1" hidden>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Rev. Ledger Group <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" name="rev_ledger_group_id"
                                                            id="rev_ledger_group">
                                                        </select>

                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Imp. Ledger </label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="imp_ledger_id"
                                                            id="imp_ledger">
                                                            <option value="">Select</option>
                                                            @foreach ($sur_ledgers as $imp)
                                                                <option value="{{ $imp->id }}">
                                                                    {{ $imp->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1" hidden>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Imp. Ledger Group <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" name="imp_ledger_group_id"
                                                            id="imp_ledger_group">
                                                        </select>

                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Sales Ledger </label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="sales_ledger_id"
                                                            id="sales_ledger">
                                                            <option value="">Select</option>
                                                            @foreach ($sales_exp_ledgers as $sales)
                                                                <option value="{{ $sales->id }}">
                                                                    {{ $sales->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1" hidden>
                                                    <div class="col-md-3">
                                                        <label class="form-label">Sales Ledger Group <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" name="sales_ledger_group_id"
                                                            id="sales_ledger_group">
                                                        </select>

                                                    </div>
                                                </div>
                                            </div>





                                            <div class="col-md-3 border-start">
                                                <div class="row align-items-center mb-2">
                                                    <div class="col-md-12">
                                                        <label
                                                            class="form-label text-primary"><strong>Status</strong></label>
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="customColorRadio3"
                                                                    name="status" value="active"
                                                                    class="form-check-input"
                                                                    {{ old('status', 'active') == 'active' ? 'checked' : '' }} />
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio3">Active</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="customColorRadio4"
                                                                    name="status" value="inactive"
                                                                    class="form-check-input"
                                                                    {{ old('status') == 'inactive' ? 'checked' : '' }} />
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio4">Inactive</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Modal to add new record -->

                    </section>
                </form>


            </div>
        </div>
    </div>
    <!-- END: Content-->
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
            showToast("success", "{{ session('success') }}");
        @endif

        @if (session('error'))
            showToast("error", "{{ session('error') }}");
        @endif

        @if ($errors->any())
            showToast('error',
                "@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach"
            );
        @endif
        
        function handleLedgerChange(ledgerSelector, groupSelector, selectedGroupId = null) {
            $(ledgerSelector).change(function() {
                const ledgerId = $(this).val();
                const groupDropdown = $(groupSelector);
                $.ajax({
                    url: '{{ route('finance.fixed-asset.getLedgerGroups') }}',
                    method: 'GET',
                    data: {
                        ledger_id: ledgerId,
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        groupDropdown.empty();
                        response.forEach(item => {
                            const selected = (selectedGroupId == item.id) ? 'selected' : '';
                            groupDropdown.append(
                                `<option value="${item.id}" ${selected}>${item.name}</option>`
                            );
                        });
                    },
                    error: function() {
                        alert('Error fetching group items.');
                    }
                });
            });
        }
        handleLedgerChange('#ledger', '#ledger_group');
        handleLedgerChange('#rev_ledger', '#rev_ledger_group');
        handleLedgerChange('#imp_ledger', '#imp_ledger_group');
        handleLedgerChange('#sales_ledger', '#sales_ledger_group');
        handleLedgerChange('#dep_ledger', '#dep_ledger_group', "{{ $dep_ledger_group_id }}");


        var categories = [
            @foreach ($categories as $category)
                {
                    id: {{ $category->id }},
                    label: "{{ $category->name }}"
                },
            @endforeach
        ];

        $("#asset_category").autocomplete({
            source: categories,
            minLength: 1,
            select: function(event, ui) {
                $("#asset_category").val(ui.item.label); // Set textbox value
                $("#asset_category_id").val(ui.item.id); // Set hidden category_id
                return false;
            }
        });
        $("#asset_category").on("input", function() {
            $("#asset_category_id").val(''); // Set to empty string or null
        });
        $('#dep_ledger').trigger('change');
    </script>
@endsection

@endsection
