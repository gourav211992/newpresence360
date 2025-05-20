@extends('layouts.app')

@section('content')
<!-- BEGIN: Content-->
<div class="app-content content ">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <form action="{{ route('org-service.update', $service->id) }}" method="POST" id = "org-service-form">
        @csrf
        <div class="content-header pocreate-sticky">
            <div class="row">
                <div class="content-header-left col-md-6 col-6 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">Edit Service</h2>
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                    <li class="breadcrumb-item"><a href="{{ route('org-services.index') }}">Services</a></li>
                                    <li class="breadcrumb-item active">Edit</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                <button type="button" onclick="history.go(-1)"
                                    class="btn btn-secondary btn-sm">
                                <i data-feather="arrow-left-circle"></i> Back
                                </button>
                                <button type="submit" class="btn btn-primary btn-sm ms-1">
                                    <i data-feather="check-circle"></i> Update
                                </button>
                </div>
            </div>
        </div>
        <div class="content-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            <section id="basic-datatable">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body customernewsection-form">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="newheader border-bottom mb-2 pb-25">
                                            <h4 class="card-title text-theme">Basic Information</h4>
                                            <p class="card-text">Details</p>
                                        </div>
                                    </div>
                                    <div class="col-md-9">
                                        

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Service</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input class = "form-control" type = "text" disabled value = "{{$service -> service ?-> name}}" />
                                                </div>
                                            </div>

                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Alias<span
                                                            class="text-danger"></span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" class="form-control" disabled
                                                        value="{{ $service->alias }}" />
                                                    @error('alias')
                                                        <span class="alert alert-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <!-- <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Status</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="demo-inline-spacing">
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="status_active" name="status"
                                                                class="form-check-input" value="Active" {{strtolower($service->status) == 'active' ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-bolder"
                                                                for="status_active">Active</label>
                                                        </div>
                                                        <div class="form-check form-check-primary mt-25">
                                                            <input type="radio" id="status_inactive" name="status"
                                                                class="form-check-input" value="Inactive" {{ strtolower($service->status) == 'inactive' ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-bolder"
                                                                for="status_inactive">Inactive</label>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div> -->

                                            <div class="step-custhomapp bg-light">
                                                <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-bs-toggle="tab"
                                                            href="#config">Configuration</a>
                                                        </li>
                                                </ul>
                                            </div>

                                            <div class="tab-content">
                                                <div class="tab-pane active" id="config">
                                                        @forelse ($service -> parameters as $serviceParamKey => $serviceParam)
                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                <label class="form-label">{{\App\Helpers\ServiceParametersHelper::SERVICE_PARAMETERS[$serviceParam -> parameter_name]}}</label>
                                                            </div>
                                                            <div class="col-md-5">
                                                                <input type = "hidden" name = "param_names[]" value = "{{$serviceParam -> parameter_name}}" />
                                                                <input type = "hidden" name = "param_ids[]" value = "{{$serviceParam -> id}}" />
                                                                <select
                                                                    class="form-select mw-100 select2 {{$serviceParam -> is_multiple ? 'paramSelect' : ''}}"
                                                                    data-id="1" name="params[{{$serviceParamKey}}][]"
                                                                    {{$serviceParam -> is_multiple ? 'multiple' : ''}}>
                                                                @if (isset($serviceParam -> param_array))
                                                                    @foreach ($serviceParam -> param_array as $option)
                                                                        <option {{in_array($option['value'], $serviceParam -> parameter_value) ? 'selected' : ''}} value = "{{$option['value']}}">{{$option['label']}}</option>
                                                                    @endforeach
                                                                @endif
                                                                </select>
                                                                @error('params')
                                                                    <span class="alert alert-danger">{{ $message }}</span>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                        
                                                        @empty
                                                        <div class="row align-items-center mb-1">
                                                            <div class="col-md-3">
                                                                No Configurations Specified
                                                            </div>
                                                        </div>
                                                        @endforelse
                                                </div>
                                            <div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Modal to add new record -->
            </section>
        </div>
        </form>
    </div>
</div>
<!-- END: Content-->
@endsection

@section('scripts')
    <script>
        function disableSelectedMultiOptions()
        {
            $('.paramSelect').find('option').prop('disabled', false);
            $('.paramSelect').each(function() {
                var selectedValues= $(this).val();
                $.each(selectedValues, function(index, value) {
                    $('.paramSelect').not(this).find('option[value="'+value+'"]').prop('disabled', true);
                });
            });
        }

        disableSelectedMultiOptions();
        $(document).on('change', '.paramSelect', function() {
            disableSelectedMultiOptions();
        });

        $('#org-service-form').on('submit', function(e) {
            $('.paramSelect').find('option').prop('disabled', false);  // Enable all options
        });
    </script>
@endsection