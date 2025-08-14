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
                            <a onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="arrow-left-circle"></i> Back</a>
                            <button form="setup" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="check-circle"></i> Update</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">

                <form id="setup" method="POST" action="{{ route('finance.fixed-asset.setup.update', $data->id) }}" enctype="multipart/form-data">
                    @method('PUT')
                    @csrf
                
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
																<label class="form-label">Act Type <span class="text-danger">*</span></label>  
															</div> 

															<div class="col-md-8"> 
														              <div class="demo-inline-spacing">
                                                                            <div class="form-check form-check-primary mt-25">
                                                                                <input type="radio" id="company" name="act_type" value="company" class="form-check-input" {{ $data->act_type == 'company'|| $data->act_type == null ? 'checked' : '' }}>
                                                                                <label class="form-check-label fw-bolder" for="company">Company</label>
                                                                            </div> 
                                                                            <div class="form-check form-check-primary mt-25">
                                                                                <input type="radio" id="income_tax" name="act_type" value="income_tax" class="form-check-input" {{ $data->act_type == 'income_tax' ? 'checked' : '' }}>
                                                                                <label class="form-check-label fw-bolder" for="income_tax">Income Tax</label>
                                                                            </div>  
                                                                        </div>
                                                                
															</div>
														</div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Asset Category <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="asset_category" id="asset_category_id" disabled required>
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
                                                 <div class="row align-items-center mb-1 company">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Prefix <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="prefix" required 
                                                            oninput="checkUnique()" class="form-control text-uppercase company-field"
                                                            maxlength="3" pattern="[A-Z]{1,3}"
                                                            title="Enter up to 3 uppercase letters"
                                                            value="{{ $data->prefix }}" required
                                                            oninput="this.value = this.value.toUpperCase()" />
                                                            @error('prefix')
                                                            <span class="alert alert-danger">{{ $message }}</span>
                                                        @enderror
                                                        <span id="prefix-feedback" class="text-danger small"></span>
                                                      
                                                    </div>
                                                </div>
                
                                                <div class="row align-items-center mb-1 company">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Ledger <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2 company-field" name="ledger_id" id="ledger" required>
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
                
                                                <div class="row align-items-center mb-1 company">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Ledger Group <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select company-field" name="ledger_group_id" id="ledger_group" required>
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
                                                <div class="row align-items-center mb-1 company">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Salvage % <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="number" class="form-control company-field"
                                                            name="salvage_percentage" required
                                                            value="{{ old('salvage_percentage',$data->salvage_percentage) }}" />
                                                    </div>
                                                </div>
                                                  <div class="row align-items-center mb-1 income_tax">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Dep % <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="number" class="form-control income-field"
                                                            name="dep_percentage" id="dep_percentage" value="{{$data->dep_percentage}}" required/>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1 company">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Expected Life in Yrs. <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="number" class="form-control company-field" name="expected_life_years" required 
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
                                                </div>
                 --}}
                                                <div class="row align-items-center mb-1 company">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Maintenance Schedule</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" name="maintenance_schedule">
                                                            <option value="weekly" {{ old('maintenance_schedule', $data->maintenance_schedule) == '' ? 'selected' : '' }}>Select</option>
                                                            <option value="weekly" {{ old('maintenance_schedule', $data->maintenance_schedule) == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                                            <option value="monthly" {{ old('maintenance_schedule', $data->maintenance_schedule) == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                                            <option value="quarterly" {{ old('maintenance_schedule', $data->maintenance_schedule) == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                                            <option value="semi-annually" {{ old('maintenance_schedule', $data->maintenance_schedule) == 'semi-annually' ? 'selected' : '' }}>Semi-Annually</option>
                                                            <option value="annually" {{ old('maintenance_schedule', $data->maintenance_schedule) == 'annually' ? 'selected' : '' }}>Annually</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            
                                            <div class="row align-items-center mb-1 company">
                                                <div class="col-md-3">
                                                    <label class="form-label">Dep. Ledger <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select select2 company-field" name="dep_ledger_id" id="dep_ledger" required>
                                                        <option value="" {{ old('dep_ledger_id', $data->dep_ledger_id) ? '' : 'selected' }}>Select</option>
                                                        @foreach ($dep_ledgers as $ledger)
                                                            <option value="{{ $ledger->id }}" 
                                                                {{ old('dep_ledger_id', $data->dep_ledger_id) == $ledger->id ? 'selected' : '' }}>
                                                                {{ $ledger->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
            
                                            <div class="row align-items-center mb-1 company">
                                                <div class="col-md-3">
                                                    <label class="form-label">Dep. Ledger Group <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select company-field" name="dep_ledger_group_id" id="dep_ledger_group" required>
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
                                                                                    <div class="row align-items-center mb-1 company">
                                                <div class="col-md-3">
                                                    <label class="form-label">Revaluation Ledger </label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select select2" name="rev_ledger_id"
                                                    id="rev_ledger">
                                                    <option value=""
                                                        {{ old('rev_ledger') ? '' : 'selected' }}>Select</option>
                                                    @foreach ($sur_ledgers as $rev)
                                                        <option value="{{ $rev->id }}"
                                                            {{ $data->rev_ledger_id == $rev->id ? 'selected' : '' }}>
                                                            {{ $rev->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                             </div>
                                            </div>
                                    
                                            <div class="row align-items-center mb-1 company">
                                                <div class="col-md-3">
                                                    <label class="form-label">Revaluation Ledger Group <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select" name="rev_ledger_group_id"
                                                    id="rev_ledger_group">
                                                    </select>
                                            
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1 company">
                                                <div class="col-md-3">
                                                    <label class="form-label">Impairement Ledger </label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select select2" name="imp_ledger_id"
                                                    id="imp_ledger">
                                                    <option value=""
                                                        {{ old('imp_ledger') ? '' : 'selected' }}>Select</option>
                                                    @foreach ($sales_exp_ledgers as $imp)
                                                        <option value="{{ $imp->id }}"
                                                            {{ $data->imp_ledger_id == $imp->id ? 'selected' : '' }}>
                                                            {{ $imp->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                             </div>
                                            </div>
                                    
                                            <div class="row align-items-center mb-1 d-none">
                                                <div class="col-md-3">
                                                    <label class="form-label">Rev. Ledger Group </label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select" name="imp_ledger_group_id"
                                                    id="imp_ledger_group">
                                                    </select>
                                            
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1 company">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Writeoff Ledger </label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="wri_ledger_id"
                                                            id="wri_ledger">
                                                            <option value="">Select</option>
                                                            @foreach ($sales_exp_ledgers as $wri)
                                                                <option value="{{ $wri->id }}"  {{ $data->wri_ledger_id == $wri->id ? 'selected' : '' }}>
                                                                    {{ $wri->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1 d-none">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Wri. Ledger Group <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" name="wri_ledger_group_id"
                                                            id="wri_ledger_group">
                                                        </select>

                                                    </div>
                                                </div>
                                                
                                            <div class="row align-items-center mb-1 company">
                                                <div class="col-md-3">
                                                    <label class="form-label">Sales Ledger </label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select class="form-select select2" name="sales_ledger_id"
                                                    id="sales_ledger">
                                                    <option value=""
                                                        {{ old('sales_ledger') ? '' : 'selected' }}>Select</option>
                                                    @foreach ($sales_exp_ledgers as $sales)
                                                        <option value="{{ $sales->id }}"
                                                            {{ $data->sales_ledger_id == $sales->id ? 'selected' : '' }}>
                                                            {{ $sales->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                             </div>
                                            </div>
                                    
                                            <div class="row align-items-center mb-1 d-none">
                                                <div class="col-md-3">
                                                    <label class="form-label">Rev. Ledger Group <span class="text-danger">*</span></label>
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
        const prefix = $('input[name="prefix"]');
        const name = $("#asset_category_id option:selected");
        prefix.trigger('input');
    $('#setup').on('submit', function(e) {
        e.preventDefault();
         if($('#income_tax').is(':checked'))
        $('input[name="prefix"]').val('');
        if (($('#prefix-feedback').text().trim()) != "" && $('#company').is(':checked')) {
    showToast('error', 'Prefix already taken');
    return;
}
        $('.preloader').show();
         
        this.submit();
    });
    function handleLedgerChange(ledgerSelector, groupSelector, selectedGroupId = null) {
    console.log('ledgerSelector', $(ledgerSelector).val());

    // Always bind the change event
    $(ledgerSelector).change(function() {
        const ledgerId = $(this).val();
        const groupDropdown = $(groupSelector);

        if (ledgerId === '') {
            groupDropdown.empty(); // Optional: Clear group dropdown if ledger is empty
            return;
        }

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
            }
        });
    });

    // Optional: Trigger change once on load if value exists
    if ($(ledgerSelector).val() !== '') {
        $(ledgerSelector).trigger('change');
    }
}

        handleLedgerChange('#ledger', '#ledger_group', "{{ $data->ledger_group_id }}");
        handleLedgerChange('#rev_ledger', '#rev_ledger_group', "{{ $data->rev_ledger_group_id }}");
        handleLedgerChange('#imp_ledger', '#imp_ledger_group', "{{ $data->imp_ledger_group_id }}");
        handleLedgerChange('#sales_ledger', '#sales_ledger_group', "{{ $data->sales_ledger_group_id }}");
        handleLedgerChange('#wri_ledger', '#wri_ledger_group', "{{ $data->wri_ledger_group_id }}");
        handleLedgerChange('#dep_ledger', '#dep_ledger_group', "{{ $data->dep_ledger_group_id }}");

    $('#dep_ledger_group').val("{{$data->dep_ledger_group_id}}");
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
         function toggleFields() {
            if ($('#income_tax').is(':checked')) {
                    $('.income_tax').removeClass('d-none');
                     $('.company').addClass('d-none');
                    $('.company-field').removeAttr('required').val('');
                    $('.income-field').attr('required', true);
                    $('#salvage_percentage').val('');
            } else {
                $('.income_tax').addClass('d-none');
                $('.company').removeClass('d-none');
                $('.company-field').attr('required', true);
                $('.income-field').removeAttr('required').val('');
                $('#salvage_percentage').val('{{$data->dep_percentage??null}}');
                if($('input[name="prefix"]').val().trim() === '')
                generatePrefix();
            }
        }
         
     $('input[name="act_type"]').on('change', toggleFields);
     toggleFields();
       

        function generatePrefix() {
   

            $.ajax({
                url: '{{ route('generate-setup-prefix') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    name: name.text().trim(),
                    id:'{{$data->id}}'
                },
                success: function(response) {
                    prefix.val((response.prefix || ''));
                },
                error: function() {
                    prefix.val('');
                }
            });
        }

        function checkUnique() {
            var feedback = $('#prefix-feedback');

            $.ajax({
                url: '{{ route('setup-check-prefix') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    prefix: prefix.val().trim(),
                    id:'{{$data->id}}',
                },
                success: function(response) {
                    if (response.is_unique) {
                        feedback.text('');
                    } else {
                        feedback.text('Prefix is already in use.');
                    }

                    // Optionally update the field with suggested unique prefix
                    if (response.prefix) {
                        prefix.val(response.prefix);
                    }
                },
                error: function() {
                    feedback.text('Error checking prefix.');
                }
            });
        }
      
          
    </script>
@endsection


@endsection
