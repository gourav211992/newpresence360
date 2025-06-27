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
                                        <li class="breadcrumb-item active">View Details</li>


                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="arrow-left-circle"></i> Back</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

                <form id="setup">
                
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
                                                        <label class="form-label">Asset Category <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="asset_category_id" id="asset_category_id" disabled required>
                                                            <option value="" {{ old('asset_category_id', $data->asset_category_id) ? '' : 'selected' }}>Select</option>
                                                            @foreach ($categories as $category)
                                                                <option value="{{ $category->id }}" 
                                                                    {{ old('asset_category_id', $data->asset_category_id) == $category->id ? 'selected' : '' }}>
                                                                    {{ $category->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Ledger <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="ledger_id" id="ledger" required>
                                                            <option value="" {{ old('ledger_id', $data->ledger_id) ? '' : 'selected' }}>Select</option>
                                                            @foreach ($ledgers as $ledger)
                                                                <option value="{{ $ledger->id }}" 
                                                                    {{ old('ledger_id', $data->ledger_id) == $ledger->id ? 'selected' : '' }}>
                                                                    {{ $ledger->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Ledger Group <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="ledger_group_id" id="ledger_group" required>
                                                            <option value="" {{ old('ledger_group_id', $data->ledger_group_id) ? '' : 'selected' }}>Select</option>
                                                            @foreach ($ledgerGroups as $group)
                                                                <option value="{{ $group->id }}" 
                                                                    {{ old('ledger_group_id', $data->ledger_group_id) == $group->id ? 'selected' : '' }}>
                                                                    {{ $group->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Expected Life in Yrs. <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="number" class="form-control" name="expected_life_years" required 
                                                               value="{{ old('expected_life_years', $data->expected_life_years) }}" />
                                                    </div>
                                                </div>
                
                                                {{-- <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Dep. Method <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" name="depreciation_method" required>
                                                            <option value="SLM" {{ old('depreciation_method', $data->depreciation_method) == 'SLM' ? 'selected' : '' }}>SLM</option>
                                                            <option value="WDV" {{ old('depreciation_method', $data->depreciation_method) == 'WDV' ? 'selected' : '' }}>WDV</option>
                                                        </select>
                                                    </div>
                                                </div>
                
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Dep. % <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="number" class="form-control" name="depreciation_percentage" 
                                                               value="{{ old('depreciation_percentage', $data->depreciation_percentage) }}" required />
                                                    </div>
                                                </div> --}}
                
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Maintenance Schedule</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" name="maintenance_schedule">
                                                            <option value="weekly" {{ old('maintenance_schedule', $data->maintenance_schedule) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                                            <option value="monthly" {{ old('maintenance_schedule', $data->maintenance_schedule) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                            <option value="quarterly" {{ old('maintenance_schedule', $data->maintenance_schedule) == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                                            <option value="semi-annually" {{ old('maintenance_schedule', $data->maintenance_schedule) == 'semi-annually' ? 'selected' : '' }}>Semi-Annually</option>
                                                            <option value="annually" {{ old('maintenance_schedule', $data->maintenance_schedule) == 'annually' ? 'selected' : '' }}>Annually</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Dep. Ledger <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select select2" name="dep_ledger_id" id="dep_ledger" required>
                                                        <option value="" {{ old('ledger_id', $data->dep_ledger_id) ? '' : 'selected' }}>Select</option>
                                                        @foreach ($ledgers as $ledger)
                                                            <option value="{{ $ledger->id }}" 
                                                                {{ old('ledger_id', $data->dep_ledger_id) == $ledger->id ? 'selected' : '' }}>
                                                                {{ $ledger->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
            
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Dep. Ledger Group <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select select2" name="dep_ledger_group_id" id="dep_ledger_group" required>
                                                        <option value="" {{ old('dep_ledger_group_id', $data->dep_ledger_group_id) ? '' : 'selected' }}>Select</option>
                                                        @foreach ($ledgerGroupsDep as $group)
                                                            <option value="{{ $group->id }}" 
                                                                {{ old('ledger_group_id', $data->dep_ledger_group_id) == $group->id ? 'selected' : '' }}>
                                                                {{ $group->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                          
                
                                            <div class="col-md-3 border-start">
                                                <div class="row align-items-center mb-2">
                                                    <div class="col-md-12">
                                                        <label class="form-label text-primary"><strong>Status</strong></label>
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="customColorRadio3" name="status" value="active" 
                                                                       class="form-check-input" 
                                                                       {{ old('status', $data->status) == 'active' ? 'checked' : '' }} />
                                                                <label class="form-check-label fw-bolder" for="customColorRadio3">Active</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="customColorRadio4" name="status" value="inactive" 
                                                                       class="form-check-input" 
                                                                       {{ old('status', $data->status) == 'inactive' ? 'checked' : '' }} />
                                                                <label class="form-check-label fw-bolder" for="customColorRadio4">Inactive</label>
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

        $('#ledger').change(function() {
                let groupDropdown = $('#ledger_group');
                $.ajax({
                    url: '{{ route('finance.fixed-asset.getLedgerGroups') }}',
                    method: 'GET',
                    data: {
                        ledger_id: $(this).val(),
                        _token: $('meta[name="csrf-token"]').attr(
                            'content') // CSRF token
                    },
                    success: function(response) {
                        groupDropdown.empty(); // Clear previous options

                        response.forEach(item => {
                            groupDropdown.append(
                                `<option value="${item.id}">${item.name}</option>`
                            );
                        });

                    },
                    error: function() {
                        alert('Error fetching group items.');
                    }
                });

            })
            $(document).ready(function() {
    // Apply to a specific form by ID
    $('#setup input').attr('disabled', true);
    $('#setup select').attr('disabled', true);
    $('#setup radio').attr('disabled', true);
    $('#setup textarea').attr('readonly', true);
});
  function toggleFields() {
        if ($('#income_tax').is(':checked')) {
            $('.income_tax').removeClass('d-none');
        } else {
            $('.income_tax').addClass('d-none');
        }
    }
     $('input[name="act_type"]').on('change', toggleFields);
     toggleFields();

    </script>
@endsection


@endsection
