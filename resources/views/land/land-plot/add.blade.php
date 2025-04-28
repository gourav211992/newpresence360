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
    </style>
    <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}&libraries=places" async defer>
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@endsection

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
                                <h2 class="content-header-title float-start mb-0">Land Plot</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a>
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
                            <button form="landplot-form" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submission_val"
                                data-val="draft"><i data-feather="save"></i> Save as draft</button>
                            <button form="landplot-form" class="btn btn-primary btn-sm mb-50 mb-sm-0 submission_val"
                                data-val="submitted"><i data-feather="check-circle"></i> Submit</button>


                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">


                <div class="content-body">
                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    <section id="basic-datatable">
                        <form id="landplot-form" method="POST" action="{{ route('land-plot.save') }}"
                            enctype='multipart/form-data'>
                            @csrf
                            <input type="hidden" name="doc_number_type" id="doc_number_type">
                            <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern">
                            <input type="hidden" name="doc_prefix" id="doc_prefix">
                            <input type="hidden" name="doc_suffix" id="doc_suffix">
                            <input type="hidden" name="doc_no" id="doc_no">

                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body customernewsection-form">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div
                                                        class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                                        <div>
                                                            <h4 class="card-title text-theme">Basic Information</h4>
                                                            <p class="card-text">Fill the details</p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <input type="hidden" name="status_val" id="status_val">


                                                <div class="col-md-8">

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Series <span
                                                                    class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <select class="form-select" name="series" required
                                                                id="series">
                                                                <option value="" selected>Select</option>
                                                                @foreach ($series as $key => $serie)
                                                                    <option value="{{ $serie->id }}">
                                                                        {{ $serie->book_name }}</option>
                                                                @endforeach

                                                            </select>

                                                            @error('series')
                                                                <div class="text-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>

                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Document No. <span
                                                                    class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <input type="text" class="form-control" name="document_no"
                                                                id="document_no" value="{{ old('document_no') }}" required
                                                                readonly>
                                                            @error('document_no')
                                                                <div class="text-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Plot Name <span
                                                                    class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <input type="text" class="form-control" name="plot_name"
                                                                id="plot_name" value="{{ old('plot_name') }}" required>
                                                            @error('plot_name')
                                                                <div class="text-danger">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Select Land <span
                                                                    class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <select class="form-select select2" id="landSelect"
                                                                name="land_id" required>
                                                                <option value="">Select</option>

                                                                @foreach ($lands as $land)
                                                                    <option value="{{ $land->id }}"
                                                                        data-size="{{ $land->plot_area }}"
                                                                        data-location="{{ $land->address }}">
                                                                        {{ $land->name }}
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 action-button">
                                                            <a data-bs-toggle="modal" data-bs-target="#rescdule"
                                                                class="btn btn-outline-primary btn-sm mb-0"><i
                                                                    data-feather="search"></i> Find Land</a>
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Size of Land <span
                                                                    class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <input type="text" id="landSize" name="land_size"
                                                                required readonly class="form-control">
                                                        </div>
                                                    </div>

                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Location of Land <span
                                                                    class="text-danger">*</span></label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <input type="text" id="landLocation" name="land_location"
                                                                required readonly class="form-control">
                                                        </div>
                                                    </div>



                                                    <div class="row align-items-center mb-1">
                                                        <div class="col-md-3">
                                                            <label class="form-label">Status</label>
                                                        </div>

                                                        <div class="col-md-5">
                                                            <div class="demo-inline-spacing">
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="customColorRadio3"
                                                                        name="status" class="form-check-input"
                                                                        value="1"
                                                                        @if (empty(old('status'))) checked @endif
                                                                        {{ old('status') == '1' ? 'checked' : '' }}
                                                                        required>
                                                                    <label class="form-check-label fw-bolder"
                                                                        for="customColorRadio3">Active</label>
                                                                </div>
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input type="radio" id="customColorRadio4"
                                                                        name="status" class="form-check-input"
                                                                        value="0"
                                                                        {{ old('status') == '0' ? 'checked' : '' }}
                                                                        required>
                                                                    <label class="form-check-label fw-bolder"
                                                                        for="customColorRadio4">Inactive</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>


                                                </div>

                                                <div class="col-md-4">

                                                    <!-- <div class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
                                                <h5 class="mb-2 text-dark border-bottom pb-50 d-flex align-items-center justify-content-between">
                                                                <strong><i data-feather="arrow-right-circle"></i> Approval History</strong>
                                                                <strong class="badge rounded-pill badge-light-secondary amendmentselect">Rev. No.
                                                                    <select class="form-select">
                                                                        <option>00</option>
                                                                        <option>01</option>
                                                                        <option>02</option>
                                                                        <option>03</option>
                                                                    </select>
                                                                </strong>

                                                            </h5>
                                                <ul class="timeline ms-50 newdashtimline ">
                                                    <li class="timeline-item">
                                                        <span class="timeline-point timeline-point-indicator"></span>
                                                        <div class="timeline-event">
                                                            <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                <h6>Deepak Kumar</h6>
                                                                <span class="badge rounded-pill badge-light-primary">Amendment</span>
                                                            </div>
                                                            <h5>(2 min ago)</h5>
                                                            <p>Description will come here</p>
                                                        </div>
                                                    </li>
                                                    <li class="timeline-item">
                                                        <span class="timeline-point timeline-point-indicator"></span>
                                                        <div class="timeline-event">
                                                            <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                <h6>Aniket Singh</h6>
                                                                <span class="badge rounded-pill badge-light-danger">Rejected</span>
                                                            </div>
                                                            <h5>(2 min ago)</h5>
                                                            <p>Description will come here</p>
                                                        </div>
                                                    </li>
                                                    <li class="timeline-item">
                                                        <span class="timeline-point timeline-point-warning timeline-point-indicator"></span>
                                                        <div class="timeline-event">
                                                            <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                <h6>Deewan Singh</h6>
                                                                <span class="badge rounded-pill badge-light-warning">Pending</span>
                                                            </div>
                                                            <h5>(5 min ago)</h5>
                                                            <p>Description will come here</p>
                                                        </div>
                                                    </li>
                                                    <li class="timeline-item">
                                                        <span class="timeline-point timeline-point-info timeline-point-indicator"></span>
                                                        <div class="timeline-event">
                                                            <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                <h6>Brijesh Kumar</h6>
                                                                <span class="badge rounded-pill badge-light-success">Approved</span>
                                                            </div>
                                                            <h5>(10 min ago)</h5>
                                                            <p>Description will come here</p>
                                                        </div>
                                                    </li>
                                                    <li class="timeline-item">
                                                        <span class="timeline-point timeline-point-danger timeline-point-indicator"></span>
                                                        <div class="timeline-event">
                                                            <div class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                <h6>Deepender Singh</h6>
                                                                <span class="badge rounded-pill badge-light-success">Approved</span>
                                                            </div>
                                                            <h5>(5 day ago)</h5>
                                                            <p><a href="#"><i data-feather="download"></i></a> Description will come here </p>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div> -->

                                                </div>

                                            </div>


                                            <div class="col-md-12">
                                                <div class="mt-2">
                                                    <div class="step-custhomapp bg-light">
                                                        <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                                                            <li class="nav-item">
                                                                <a class="nav-link active" data-bs-toggle="tab"
                                                                    href="#Pattern">Land Information</a>
                                                            </li>
                                                            <li class="nav-item">
                                                                <a class="nav-link" data-bs-toggle="tab"
                                                                    href="#Approval">Supporting Documents</a>
                                                            </li>

                                                        </ul>
                                                    </div>

                                                    <div class="tab-content ">
                                                        <div class="tab-pane active" id="Pattern">
                                                            <div class="row">
                                                                <div class="col-md-5">



                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label">Khasara No.</label>
                                                                        </div>

                                                                        <div class="col-md-8">
                                                                            <input type="text" class="form-control"
                                                                                name="khasara_no"
                                                                                value="{{ old('khasara_no') }}"
                                                                                onchange="cleanInputNumber(this)">
                                                                            @error('khasara_no')
                                                                                <div class="text-danger">{{ $message }}
                                                                                </div>
                                                                            @enderror
                                                                        </div>
                                                                    </div>
                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label">Area of Plot <span
                                                                                    class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <input type="text" class="form-control"
                                                                                name="plot_area"
                                                                                value="{{ old('plot_area') }}"
                                                                                onchange="cleanInputNumber(this)" required>
                                                                            @error('plot_area')
                                                                                <div class="text-danger">{{ $message }}
                                                                                </div>
                                                                            @enderror
                                                                        </div>

                                                                        <div class="col-md-4">
                                                                            <select class="form-select" name="area_unit"
                                                                                required>
                                                                                <option value="" selected disabled>
                                                                                    Unit</option>
                                                                                <option value="Acres">Acres</option>
                                                                                <option value="Hectares">Hectares</option>
                                                                                <option value="squarefeet">Square Feet
                                                                                </option>
                                                                                <option value="squaremeter">Square Meter
                                                                                </option>
                                                                                <option value="bigha">Bigha</option>

                                                                            </select>
                                                                        </div>

                                                                    </div>
                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label">Dimension <span
                                                                                    class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-8">
                                                                            <input type="text" class="form-control"
                                                                                name="dimension"
                                                                                value="{{ old('dimension') }}" required>
                                                                            {{-- onchange="cleanInputNumber(this)" --}}
                                                                            @error('dimension')
                                                                                <div class="text-danger">{{ $message }}
                                                                                </div>
                                                                            @enderror
                                                                        </div>
                                                                    </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label">Plot
                                                                                Valuation</label>
                                                                        </div>

                                                                        <div class="col-md-8">
                                                                            <input type="text" class="form-control"
                                                                                name="plot_valuation"
                                                                                value="{{ old('plot_valuation') }}"
                                                                                onchange="cleanInputNumber(this)">
                                                                        </div>

                                                                    </div>



                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label">Address <span
                                                                                    class="text-danger">*</span></label>
                                                                        </div>


                                                                        <div class="col-md-8">
                                                                            <input type="text" class="form-control"
                                                                                name="address" id="address"
                                                                                placeholder=" "
                                                                                value="{{ old('address') }}"
                                                                                onchange="cleanInput(this)" required>
                                                                            @error('address')
                                                                                <div class="text-danger">{{ $message }}
                                                                                </div>
                                                                            @enderror
                                                                        </div>

                                                                    </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-3">

                                                                        </div>

                                                                        <div class="col-md-8">
                                                                            <input type="text" class="form-control">
                                                                        </div>

                                                                    </div>


                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label">Pincode <span
                                                                                    class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-8">

                                                                            <input type="text" class="form-control"
                                                                                name="pincode" id="pincode"
                                                                                value="{{ old('pincode') }}" required>

                                                                            @error('pincode')
                                                                                <div class="text-danger">{{ $message }}
                                                                                </div>
                                                                            @enderror
                                                                        </div>

                                                                    </div>

                                                                    <div class="row align-items-center mb-1">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label">Type of Usage <span
                                                                                    class="text-danger">*</span></label>
                                                                        </div>

                                                                        <div class="col-md-8">
                                                                            <select class="form-select"
                                                                                name="type_of_usage" required>
                                                                                <option value="">Select</option>
                                                                                <option value="agricultural">Agricultural
                                                                                </option>
                                                                                <option value="commercial">Commercial
                                                                                </option>
                                                                                <option value="residential">Residential
                                                                                </option>
                                                                                <option value="industrial">Industrial
                                                                                </option>
                                                                                <option value="others">Others</option>
                                                                            </select>
                                                                        </div>

                                                                    </div>


                                                                    <div class="row  mb-1">
                                                                        <div class="col-md-3">
                                                                            <label class="form-label">Remarks</label>
                                                                        </div>

                                                                        <div class="col-md-8">
                                                                            <textarea type="text" rows="4" class="form-control" name="remarks" placeholder="Enter Remarks here..."></textarea>
                                                                            @error('remarks')
                                                                                <div class="text-danger">{{ $message }}
                                                                                </div>
                                                                            @enderror
                                                                        </div>
                                                                    </div>


                                                                </div>

                                                                <div class="col-md-7">

                                                                    <div class="row align-items-end mb-1">
                                                                        <div class="col-md-2">
                                                                            <label class="form-label">Latitude:</label>
                                                                            <h4><strong id="latitude">-</strong></h4>
                                                                        </div>
                                                                        <div class="col-md-3">
                                                                            <label class="form-label">Longitude :</label>
                                                                            <h4><strong id="longitude">-</strong></h4>
                                                                        </div>
                                                                        <div class="col-md-7 text-sm-end  action-button">
                                                                            <a href="{{ url('/assets/sample_land_locations.csv') }}"
                                                                                target="_blank"
                                                                                class="font-small-2 mb-1 me-1">
                                                                                <i data-feather="download"></i> Download
                                                                                Sample
                                                                            </a>
                                                                            <div class="image-uploadhide mt-50">
                                                                                <a href="attribute.html"
                                                                                    class="btn btn-outline-primary btn-sm">
                                                                                    <i data-feather="plus"></i> Upload
                                                                                    Geofence
                                                                                </a>
                                                                                <input type="file" name="geofence"
                                                                                    class="" id="uploadGeofence" />
                                                                                <input type="hidden" name="latitude"
                                                                                    id="latitudevalue" />
                                                                                <input type="hidden" name="longitude"
                                                                                    id="longitudevalue" />
                                                                            </div>

                                                                        </div>
                                                                    </div>
                                                                    <input id="pac-input" class="controls" type="text"
                                                                        placeholder="Search for a location">
                                                                    <div id="map"></div>
                                                                </div>


                                                            </div>
                                                        </div>

                                                        <div class="tab-pane" id="Approval">
                                                            <div class="table-responsive-md">
                                                                <table
                                                                    class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>#</th>
                                                                            <th>Document Name</th>
                                                                            <th>Upload File</th>
                                                                            <th>Attachments</th>
                                                                            <th width="40px">Action</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="tableDoc">
                                                                        <tr>
                                                                            <td>1</td>
                                                                            <td>
                                                                                <select class="form-select mw-100"
                                                                                    name="documentname[0]">
                                                                                    <option value="">Select</option>
                                                                                    @foreach ($doc_type as $document)
                                                                                        <option
                                                                                            value="{{ $document->name }}">
                                                                                            {{ ucwords(str_replace('-', ' ', $document->name)) }}
                                                                                        </option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </td>
                                                                            <td>
                                                                                <input type="file" multiple
                                                                                    class="form-control mw-100"
                                                                                    name="attachments[0][]"
                                                                                    id="attachments-0">
                                                                            </td>
                                                                            <td id="preview-0">
                                                                            </td>
                                                                            <td><a href="#"
                                                                                    class="text-primary addRow"><i
                                                                                        data-feather="plus-square"></i></a>
                                                                            </td>
                                                                        </tr>

                                                                    </tbody>


                                                                </table>
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
                        </form>
                    </section>


                </div>
            </div>
        </div>
        <div class="modal fade text-start" id="rescdule" tabindex="-1" aria-labelledby="myModalLabel17"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
                <div class="modal-content">
                    <div class="modal-header">
                        <div>
                            <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Find
                                Land</h4>
                            <p class="mb-0">Search from the below list</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">District</label>
                                    <select class="form-select" name="district_filter">
                                        <option value="">Select</option>
                                        @if (isset($lands))
                                            @foreach ($lands->unique('district') as $val)
                                                <!-- Ensure unique districts -->
                                                <option value="{{ $val->district }}">{{ $val->district }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">State</label>
                                    <select class="form-select select2" name="state_filter">
                                        <option value="">Select</option>
                                        @if (isset($lands))
                                            @foreach ($lands->unique('state') as $val)
                                                <!-- Ensure unique states -->
                                                <option value="{{ $val->state }}">{{ $val->state }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="mb-1">
                                    <label class="form-label">Country</label>
                                    <select class="form-select select2" name="country_filter">
                                        <option value="">Select</option>
                                        @if (isset($lands))
                                            @foreach ($lands->unique('country') as $val)
                                                <!-- Ensure unique countries -->
                                                <option value="{{ $val->country }}">{{ $val->country }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-3 mb-1">
                                <label class="form-label">&nbsp;</label><br />
                                <button class="btn btn-warning btn-sm" id="searchLand"><i data-feather="search"></i>
                                    Search</button>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Document No.</th>
                                                <th>Land Name</th>
                                                <th>Khasara No.</th>
                                                <th>District</th>
                                                <th>State</th>
                                                <th>Country</th>
                                                <th>Pincode</th>
                                            </tr>
                                        </thead>
                                        <tbody class="po-order-detail modalbody">
                                            @if (isset($lands))
                                                @foreach ($lands as $val)
                                                    <tr>
                                                        <td>
                                                            <div class="form-check form-check-primary">
                                                                <input type="radio" id="landSelect{{ $val->id }}"
                                                                    name="landSelect" class="form-check-input"
                                                                    value="{{ $val->id }}">
                                                            </div>
                                                        </td>
                                                        <td>{{ $val->document_no }}</td>
                                                        <td class="fw-bolder text-dark">{{ $val->name }}</td>
                                                        <td>{{ $val->khasara_no }}</td>
                                                        <td>{{ $val->district }}</td>
                                                        <td>{{ $val->state }}</td>
                                                        <td>{{ $val->country }}</td>
                                                        <td>{{ $val->pincode }}</td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer text-end">
                        <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                                data-feather="x-circle"></i> Cancel</button>
                        <button class="btn btn-primary btn-sm" data-bs-dismiss="modal"><i
                                data-feather="check-circle"></i> Process</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- END: Content-->
    @endsection
    @section('scripts')
        <script>
            $(document).ready(function() {
                $("#landplot-form").on("submit", function(e) {
                    if ($('#status_val').val() === "submitted") {

                    const addressLat = parseFloat($("#latitudevalue").val());
                    const addressLng = parseFloat($("#longitudevalue").val());
                    const fileInput = $("#uploadGeofence")[0].files;

                    if (!fileInput.length) {
                        toastr.error("Please upload a file.");
                        e.preventDefault();
                        return; // Exit if no file is uploaded
                    }

                    const file = fileInput[0];

                    // Read file content
                    readFileAsText(file).then(fileData => {
                        const uploadedCoordinates = parseUploadedFile(fileData);

                        let isValid = true;
                        uploadedCoordinates.forEach(coord => {
                            const distance = calculateDistance(addressLat, addressLng, coord
                                .lat, coord.lng);
                            if (distance > 1) { // Assuming 1 km radius
                                isValid = false;
                            }
                        });

                            $("#landplot-form")[0].submit(); // Submit the form manually if validation passes
                        
                    }).catch(err => {
                        e.preventDefault();
                        toastr.error("Error reading the file. Please try again.");
                        return;
                    });
                    }else{
                        $("#landplot-form")[0].submit();

                    }
                });

                // Function to read file as text
                function readFileAsText(file) {
                    const deferred = $.Deferred();
                    const reader = new FileReader();
                    reader.onload = function() {
                        deferred.resolve(reader.result);
                    };
                    reader.onerror = function() {
                        deferred.reject(reader.error);
                    };
                    reader.readAsText(file);
                    return deferred.promise();
                }

                // Function to parse uploaded file (CSV format)
                function parseUploadedFile(data) {
                    const rows = data.split("\n");
                    const coordinates = rows.map(row => {
                        const [lat, lng] = row.split(",");
                        return {
                            lat: parseFloat(lat),
                            lng: parseFloat(lng)
                        };
                    });
                    return coordinates.filter(coord => !isNaN(coord.lat) && !isNaN(coord.lng));
                }

                // Function to calculate distance (Haversine formula)
                function calculateDistance(lat1, lng1, lat2, lng2) {
                    const toRad = angle => (angle * Math.PI) / 180;

                    const R = 6371; // Radius of Earth in km
                    const dLat = toRad(lat2 - lat1);
                    const dLng = toRad(lng2 - lng1);

                    const a =
                        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
                        Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
                        Math.sin(dLng / 2) * Math.sin(dLng / 2);

                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
                    return R * c; // Distance in km
                }

                $('#searchLand').on('click', function() {
                    const district = $('select[name="district_filter"]').val();
                    const state = $('select[name="state_filter"]').val();
                    const country = $('select[name="country_filter"]').val();

                    $.ajax({
                        url: '{{ route('land.search') }}', // Update with your route name
                        method: 'GET',
                        data: {
                            district: district,
                            state: state,
                            country: country,
                        },
                        success: function(response) {
                            const tbody = $('.modalbody');
                            tbody.empty(); // Clear the previous results

                            // Append new results
                            response.lands.forEach(function(land) {
                                tbody.append(`
                            <tr>
                                <td>
                                    <div class="form-check form-check-primary">
                                        <input type="radio" id="landSelect${land.id}" name="landSelect" value="${land.id}" class="form-check-input">
                                    </div>
                                </td>
                                <td>${land.document_no}</td>
                                <td class="fw-bolder text-dark">${land.name}</td>
                                <td>${land.khasara_no}</td>
                                <td>${land.district}</td>
                                <td>${land.state}</td>
                                <td>${land.country}</td>
                                <td>${land.pincode}</td>
                            </tr>
                        `);
                            });
                        },
                        error: function(xhr) {
                            console.log(xhr.responseText);
                            // Optionally, display an error message to the user
                        }
                    });
                });
            });
            $('.modal-footer .btn-primary').on('click', function() {
                const selectedLandId = $('input[name="landSelect"]:checked').val(); // Get the selected land_id
                if (selectedLandId) {
                    $('#landSelect').val(selectedLandId); // Set the select box value to the selected land_id
                    $('#landSelect').change();
                } else {
                    alert('Please select a land parcel to process.');
                }
            });
        </script>

        <script>
            $('#series').on('change', function() {
                getDocNumberByBookId();
                /*var book_id = $(this).val();
                var request = $('#document_no');

                request.val(''); // Clear any existing options

                if (book_id) {
                    $.ajax({
                        url: "{{ url('get-land-request') }}/" + book_id,
                        type: "GET",
                        dataType: "json",
                        success: function(data)
                        {
                            console.log(data);
                                if (data.requestno) {
                                request.val(data.requestno);
                            }
                        }
                    });
                }*/
            });
        </script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
        <script>
            $('#landSelect').change(function() {
                var selectedOption = this.options[this.selectedIndex];

                // Get the size and location from the data attributes
                var landSize = selectedOption.getAttribute('data-size');
                var landLocation = selectedOption.getAttribute('data-location');
                console.log(landSize);

                // Update the size and location fields
                document.getElementById('landSize').value = landSize ? landSize + ' (Acr)' : '';
                document.getElementById('landLocation').value = landLocation ? landLocation : '';

            });

            $(".addRow").click(function() {
                var rowCount = $("#tableDoc").find('tr').length + 1; // Counter for row numbering, starting at 1

                var newRow = `
<tr>
    <td>${rowCount}</td>
    <td>
    <select class="form-select mw-100" name="documentname[${rowCount-1}]">
    <option value="">Select</option>
     @foreach ($doc_type as $document)
                                                                            <option value="{{ $document->name }}">{{ ucwords(str_replace('-', ' ', $document->name)) }}</option>
                                                                        @endforeach  </select>
                                                                           </td>
    <td>
        <input type="file" multiple class="form-control mw-100" name="attachments[${rowCount-1}][]" id="attachments-${rowCount-1}">
    </td>
    <td id="preview-${rowCount-1}">
        <!-- File preview icons will be inserted here -->
    </td>
    <td><a href="#" class="text-danger trash"><i data-feather="trash-2"></i></a></td>
</tr>`;

                $("#tableDoc").append(newRow);
                feather.replace();

            });
            $(document).on('change', 'input[type="file"]', function(e) {
                var rowIndex = $(this).attr('id').split('-')[1]; // Extract row index from the file input's id
                handleFileUpload(e, `#preview-${rowIndex}`);
            });

            // Function to handle file upload preview with delete icon
            function handleFileUpload(event, previewElement) {
                var files = event.target.files;
                var previewContainer = $(previewElement); // The container where previews will appear
                previewContainer.empty(); // Clear previous previews

                if (files.length > 0) {
                    // Loop through each selected file
                    for (var i = 0; i < files.length; i++) {
                        // Get the file extension
                        var fileName = files[i].name;
                        var fileExtension = fileName.split('.').pop().toLowerCase(); // Get file extension

                        // Set default icon
                        var fileIconType = 'file-text'; // Default icon for unknown types

                        // Map file extension to specific Feather icons
                        switch (fileExtension) {
                            case 'pdf':
                                fileIconType = 'file'; // Icon for PDF files
                                break;
                            case 'doc':
                            case 'docx':
                                fileIconType = 'file'; // Icon for Word documents
                                break;
                            case 'xls':
                            case 'xlsx':
                                fileIconType = 'file'; // Icon for Excel files
                                break;
                            case 'png':
                            case 'jpg':
                            case 'jpeg':
                            case 'gif':
                                fileIconType = 'image'; // Icon for image files
                                break;
                            case 'zip':
                            case 'rar':
                                fileIconType = 'archive'; // Icon for compressed files
                                break;
                            default:
                                fileIconType = 'file'; // Default icon
                                break;
                        }

                        // Generate the file preview div dynamically
                        var fileIcon = `
                        <div class="image-uplodasection expenseadd-sign" data-file-index="${i}">
                            <i data-feather="${fileIconType}" class="fileuploadicon"></i>
                            <div class="delete-img text-danger" data-file-index="${i}">
                                <i data-feather="x"></i>
                            </div>
                        </div>
                    `;

                        // Append the generated fileIcon div to the preview container
                        previewContainer.append(fileIcon);
                    }
                    // Replace icons with Feather icons after appending the new elements
                    feather.replace();
                }


                // Add event listener to delete the file preview when clicked
                previewContainer.find('.delete-img').click(function() {
                    var fileIndex = $(this).parent().data('file-index'); // Get the correct index from parent
                    removeFilePreview(fileIndex, previewContainer, event.target);
                });
            }

            // Function to remove a single file from the FileList
            function removeFilePreview(fileIndex, previewContainer, inputElement) {
                var dt = new DataTransfer(); // Create a new DataTransfer object to hold the remaining files
                var files = inputElement.files;

                // Loop through the files and add them to the DataTransfer object, except the one to delete
                for (var i = 0; i < files.length; i++) {
                    if (i !== fileIndex) {
                        dt.items.add(files[i]); // Add file to DataTransfer if it's not the one being deleted
                    }
                }

                // Update the input element with the new file list
                inputElement.files = dt.files;

                // Remove the preview of the deleted file
                previewContainer.children(`[data-file-index="${fileIndex}"]`).remove();

                // Now re-index the remaining file previews
                var remainingPreviews = previewContainer.children();
                remainingPreviews.each(function(index) {
                    $(this).attr('data-file-index', index); // Update data-file-index correctly
                    $(this).find('.delete-img').attr('data-file-index', index); // Also update delete button index
                });

                // Debugging logs
                console.log(`Remaining files after deletion: ${dt.files.length}`);
                console.log(`Remaining preview elements: ${remainingPreviews.length}`);

                // If no files are left after deleting, reset the file input
                if (dt.files.length === 0) { // Check the updated DataTransfer's files length
                    inputElement.value = ""; // Clear the input value to reset it
                }
            }


            $("#tableDoc").on("click", ".trash", function(event) {
                event.preventDefault(); // Prevent default action for <a> tag
                $(this).closest('tr').remove(); // Remove the closest <tr> element
            });

            function cleanInput(input) {
                // Remove negative numbers and special characters
                input.value = input.value.replace(/[^a-zA-Z0-9 ]/g, '');
            }

            function cleanInputNumber(input) {
                // Remove negative numbers and special characters
                input.value = input.value.replace(/[^0-9 ]/g, '');
            }

            window.addEventListener('load', function() {
                var map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 8,
                    center: {
                        lat: 28.501851443923478,
                        lng: 77.39757531317296
                    },
                });

                var formInput = document.getElementById('address');
                var formSearchBox = new google.maps.places.SearchBox(formInput);
                var geocoder = new google.maps.Geocoder();
                var marker;

                // Initial geocode and marker setting only if address is not blank
                if (formInput.value.trim() !== '') {
                    geocodeAddress(formInput.value.trim(), geocoder, map);
                }

                // Function to geocode address and place the marker
                function geocodeAddress(address, geocoder, map) {
                    geocoder.geocode({
                        address: address
                    }, function(results, status) {
                        if (status === 'OK') {
                            map.setCenter(results[0].geometry.location);
                            if (!marker) {
                                marker = new google.maps.Marker({
                                    map: map,
                                    position: results[0].geometry.location,
                                    draggable: true
                                });
                            } else {
                                marker.setPosition(results[0].geometry.location);
                            }
                            updateLatLngInputs(results[0].geometry.location.lat(), results[0].geometry.location
                                .lng());
                            geocodeLatLng(geocoder, results[0].geometry.location.lat(), results[0].geometry
                                .location.lng());
                            attachMarkerDragEvent();
                        } else {
                            console.log('Geocode was not successful for the following reason: ' + status);
                        }
                    });
                }

                formSearchBox.addListener('places_changed', function() {
                    var places = formSearchBox.getPlaces();
                    if (places.length === 0) return;

                    var place = places[0];
                    if (marker) marker.setMap(null);

                    marker = new google.maps.Marker({
                        map: map,
                        position: place.geometry.location,
                        draggable: true
                    });
                    map.setCenter(place.geometry.location);

                    updateLatLngInputs(place.geometry.location.lat(), place.geometry.location.lng());
                    geocodeLatLng(geocoder, place.geometry.location.lat(), place.geometry.location.lng());
                    attachMarkerDragEvent();
                });

                google.maps.event.addListener(map, 'click', function(event) {
                    var clickedLocation = event.latLng;
                    if (!marker) {
                        marker = new google.maps.Marker({
                            position: clickedLocation,
                            map: map,
                            draggable: true
                        });
                    } else {
                        marker.setPosition(clickedLocation);
                    }

                    updateLatLngInputs(clickedLocation.lat(), clickedLocation.lng());
                    geocodeLatLng(geocoder, clickedLocation.lat(), clickedLocation.lng());
                    attachMarkerDragEvent();
                });

                // Attach drag event listener to marker
                function attachMarkerDragEvent() {
                    google.maps.event.addListener(marker, 'dragend', function(event) {
                        updateLatLngInputs(event.latLng.lat(), event.latLng.lng());
                        geocodeLatLng(geocoder, event.latLng.lat(), event.latLng.lng());
                    });
                }

                // Function to update latitude and longitude inputs
                function updateLatLngInputs(lat, lng) {
                    document.getElementById('latitude').innerHTML = lat;
                    document.getElementById('longitude').innerHTML = lng;
                    document.getElementById('latitudevalue').value = lat;
                    document.getElementById('longitudevalue').value = lng;
                }

                // Function to reverse geocode the latitude and longitude
                function geocodeLatLng(geocoder, lat, lng) {
                    var latlng = {
                        lat: lat,
                        lng: lng
                    };
                    geocoder.geocode({
                        location: latlng
                    }, function(results, status) {
                        if (status === 'OK') {
                            if (results[0]) {
                                var address = results[0].formatted_address;
                                document.getElementById('address').value = address;

                                var addressComponents = results[0].address_components;
                                var country = '',
                                    state = '',
                                    district = '',
                                    postalCode = '';

                                // Loop through address components to extract relevant data
                                for (var i = 0; i < addressComponents.length; i++) {
                                    var component = addressComponents[i];

                                    if (component.types.includes("country")) {
                                        country = component.long_name;
                                    }
                                    if (component.types.includes("administrative_area_level_1")) {
                                        state = component.long_name;
                                    }
                                    if (component.types.includes("administrative_area_level_2")) {
                                        district = component.long_name;
                                    }
                                    if (component.types.includes("postal_code")) {
                                        postalCode = component.long_name;
                                    }
                                }

                                // Set the extracted values in the respective input fields
                                document.getElementById('pincode').value = postalCode || '';
                            } else {
                                console.log("No results found");
                            }
                        } else {
                            console.log("Geocoder failed due to: " + status);
                        }
                    });
                }
            });

            document.addEventListener('DOMContentLoaded', function() {
                $('.submission_val').click(function() {
                    let data_val = $(this).attr('data-val');
                    $("#status_val").val(data_val);
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                $('.submission_val').click(function() {
                    let data_val = $(this).attr('data-val');
                    $("#status_val").val(data_val);
                });
            });

            function getDocNumberByBookId() {
                let bookId = $('#series').val();
                let currentDate = new Date().toISOString().split('T')[0];
                let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId + "&document_date=" +
                    currentDate;
                fetch(actionUrl).then(response => {
                    return response.json().then(data => {
                        if (data.status == 200) {
                            $("#book_code_input").val(data.data.book_code);
                            if (!data.data.doc.document_number) {
                                $("#document_no").val('');
                                $('#doc_number_type').val('');
                                $('#doc_reset_pattern').val('');
                                $('#doc_prefix').val('');
                                $('#doc_suffix').val('');
                                $('#doc_no').val('');
                            } else {
                                $("#document_no").val(data.data.doc.document_number);
                                $('#doc_number_type').val(data.data.doc.type);
                                $('#doc_reset_pattern').val(data.data.doc.reset_pattern);
                                $('#doc_prefix').val(data.data.doc.prefix);
                                $('#doc_suffix').val(data.data.doc.suffix);
                                $('#doc_no').val(data.data.doc.doc_no);
                            }

                            if (data.data.doc.type == 'Manually') {
                                $("#document_no").attr('readonly', false);
                            } else {
                                $("#document_no").attr('readonly', true);
                            }

                        }
                        if (data.status == 404) {
                            $("#book_code_input").val("");

                            alert(data.message);
                        }
                    });
                });
            }

            // ******************
            // ******************
        </script>
    @endsection
