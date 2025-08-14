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
                                <h2 class="content-header-title float-start mb-0">Land Parcel</h2>
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
                            {{-- <button form="landparcel-form" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Submit</button> --}}

                            <button form="landparcel-form"
                                class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submission_val" data-val="draft"><i
                                    data-feather="save"></i> Save as draft</button>
                            <button form="landparcel-form" class="btn btn-primary btn-sm mb-50 mb-sm-0 submission_val"
                                data-val="submitted"><i data-feather="check-circle"></i> Submit</button>
                        </div>
                    </div>
                </div>
            </div>
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
                    <form id="landparcel-form" method="POST" action="{{ route('land-parcel.save') }}"
                        enctype='multipart/form-data'>
                        @csrf
                        <input type="hidden" name="book_code" id ="book_code_input">
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
                                                        <select class="form-select" name="series" required id="series"
                                                            onchange="getDocNumberByBookId()">
                                                            <option value="" disabled selected>Select</option>
                                                            @foreach ($series as $key => $serie)
                                                                <option value="{{ $serie->id }}">{{ $serie->book_name }}
                                                                </option>
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
                                                        <label class="form-label">Name <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="name"
                                                            id="name" value="{{ old('name') }}" required>
                                                        @error('name')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Description</label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <textarea class="form-control" rows="1" name="description"></textarea>
                                                        @error('description')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Survey No.</label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="surveyno"
                                                            id="surveyno" value="{{ old('surveyno') }}">
                                                        @error('name')
                                                            <div class="text-danger">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Status<span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="customColorRadio3"
                                                                    name="status" class="form-check-input"
                                                                    value="1"
                                                                    @if (empty(old('status'))) checked @endif
                                                                    {{ old('status') == '1' ? 'checked' : '' }} required>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio3">Active</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="customColorRadio4"
                                                                    name="status" class="form-check-input"
                                                                    value="0"
                                                                    {{ old('status') == '0' ? 'checked' : '' }} required>
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
                                                                href="#Amendment">Service Item</a>
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
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-3">
                                                                        <label class="form-label">Size of Land <span
                                                                                class="text-danger">*</span></label>
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <input type="text" class="form-control"
                                                                            name="plot_area"
                                                                            value="{{ old('plot_area') }}" required
                                                                            onchange="cleanInputNumber(this)">
                                                                        @error('plot_area')
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>

                                                                    <div class="col-md-4">
                                                                        <select class="form-select" name="area_unit"
                                                                            required>
                                                                            <option value="" selected disabled>Select
                                                                                Unit</option>
                                                                            <option value="Acres">Acres</option>
                                                                            <option value="Hectares">Hectares</option>
                                                                            <option value="squarefeet">Square Feet</option>
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
                                                                            value="{{ old('dimension') }}">
                                                                        {{-- onchange="cleanInputNumber(this)" --}}
                                                                        @error('dimension')
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-3">
                                                                        <label class="form-label">Land Valuation</label>
                                                                    </div>

                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control"
                                                                            name="land_valuation"
                                                                            value="{{ old('land_valuation') }}"
                                                                            onchange="cleanInputNumber(this)">
                                                                    </div>

                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-3">
                                                                        <label class="form-label">Handover Date <span
                                                                                class="text-danger">*</span></label>
                                                                    </div>

                                                                    <div class="col-md-8">
                                                                        <input type="date" class="form-control"
                                                                            name="handoverdate"
                                                                            value="{{ old('handoverdate') }}">
                                                                        @error('handoverdate')
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-3">
                                                                        <label class="form-label">Address <span
                                                                                class="text-danger">*</span></label>
                                                                    </div>

                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control"
                                                                            name="address" id="address" placeholder=" "
                                                                            value="{{ old('address') }}"
                                                                            onchange="cleanInputNumber(this)">
                                                                        @error('address')
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>

                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-3">
                                                                        <label class="form-label">District <span
                                                                                class="text-danger">*</span></label>
                                                                    </div>

                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control"
                                                                            name="district" id="district"
                                                                            value="{{ old('district') }}">
                                                                        @error('district')
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                </div>

                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-3">
                                                                        <label class="form-label">State <span
                                                                                class="text-danger">*</span></label>
                                                                    </div>

                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control"
                                                                            name="state" id="state"
                                                                            value="{{ old('state') }}">
                                                                        @error('state')
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                </div>


                                                                <div class="row align-items-center mb-1">
                                                                    <div class="col-md-3">
                                                                        <label class="form-label">Country <span
                                                                                class="text-danger">*</span></label>
                                                                    </div>

                                                                    <div class="col-md-8">
                                                                        <input type="text" class="form-control"
                                                                            name="country" id="country"
                                                                            value="{{ old('country') }}">
                                                                        @error('country')
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
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
                                                                            value="{{ old('pincode') }}">
                                                                        @error('pincode')
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>

                                                                </div>




                                                                <div class="row  mb-1">
                                                                    <div class="col-md-3">
                                                                        <label class="form-label">Remarks</label>
                                                                    </div>

                                                                    <div class="col-md-8">
                                                                        <textarea type="text" rows="4" class="form-control" name="remarks" placeholder="Enter Remarks here..."></textarea>
                                                                        @error('remarks')
                                                                            <div class="text-danger">{{ $message }}</div>
                                                                        @enderror
                                                                    </div>
                                                                </div>


                                                            </div>

                                                            <div class="col-md-7">

                                                                <div class="row align-items-end mb-1">
                                                                    <div class="col-md-3">
                                                                        <label class="form-label">Latitude:</label>
                                                                        <h4><strong id="latitude">
                                                                                @if (!empty(old('latitude')))
                                                                                    {{ old('latitude') }}
                                                                                @else
                                                                                    -
                                                                                @endif
                                                                            </strong></h4>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <label class="form-label">Longitude :</label>
                                                                        <h4><strong id="longitude">
                                                                                @if (!empty(old('longitude')))
                                                                                    {{ old('longitude') }}
                                                                                @else
                                                                                    -
                                                                                @endif
                                                                            </strong></h4>
                                                                    </div>
                                                                    <div class="col-md-6 text-sm-end  action-button">
                                                                        <a href="{{ url('/assets/sample_land_locations.csv') }}"
                                                                            target="_blank"
                                                                            class="font-small-2 mb-1 me-1">
                                                                            <i data-feather="download"></i> Download Sample
                                                                        </a>
                                                                        <div class="image-uploadhide mt-50">
                                                                            <a href="attribute.html"
                                                                                class="btn btn-outline-primary btn-sm">
                                                                                <i data-feather="plus"></i> Upload Geofence
                                                                            </a>
                                                                            <input type="file" name="geofence"
                                                                                class="" id="uploadGeofence" />
                                                                            <input type="hidden" name="latitude"
                                                                                id="latitudevalue"
                                                                                value="{{ old('latitude') }}" />
                                                                            <input type="hidden" name="longitude"
                                                                                id="longitudevalue"
                                                                                value="{{ old('longitude') }}" />
                                                                        </div>

                                                                    </div>
                                                                </div>
                                                                <!-- <input id="pac-input" class="controls" type="text" placeholder="Search for a location"> -->
                                                                <div id="map"></div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="tab-pane" id="Amendment">
                                                        <div class="table-responsive-md">
                                                            <table
                                                                class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                <thead>
                                                                    <tr>
                                                                        <th width="40px">#</th>
                                                                        <th width="100px">Service Type</th>
                                                                        <th width="100px">Service Code</th>
                                                                        <th width="40px">Attributes</th>
                                                                        <th width="200px">Service Name</th>
                                                                        <th width="100px">Ledger</th>
                                                                        <th width="100px">Group</th>
                                                                        <th width="40px">Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="tableBody">
                                                                    @php $index=0 @endphp
                                                                    <tr>
                                                                        <td>1</td>
                                                                        <td>
                                                                            <select
                                                                                id="service-item-select-{{ $index }}"
                                                                                class="form-select mw-100 service-item-select"
                                                                                name="service_item[{{ $index }}]['servicetype']">
                                                                                <option>Select Category</option>
                                                                                @foreach ($categories as $categoryItem)
                                                                                    <!-- Unique categories -->
                                                                                    @if ($categoryItem->category_name)
                                                                                        <!-- Check if category exists -->
                                                                                        <option
                                                                                            value="{{ $categoryItem->category_name }}">
                                                                                            {{ $categoryItem->category_name }}
                                                                                        </option>
                                                                                    @endif
                                                                                @endforeach

                                                                            </select>
                                                                        </td>

                                                                        <!-- Item Code Dropdown -->
                                                                        <td>
                                                                            <select id="item_code-{{ $index }}"
                                                                                class="form-select mw-100 item_code"
                                                                                onchange="updateItemName({{ $index }})"
                                                                                name="service_item[{{ $index }}]['servicecode']">
                                                                                <option value="">Select Code</option>
                                                                                @foreach ($items as $item)
                                                                                    <!-- Unique categories -->
                                                                                    @if ($item->item_code)
                                                                                        <!-- Check if category exists -->
                                                                                        <option
                                                                                            value="{{ $item->item_code }}"
                                                                                            data-item-name="{{ $item->item_name }}"
                                                                                            attribute-array="{{ json_encode($item->attributes)}}">
                                                                                            {{ $item->item_code }}
                                                                                        </option>
                                                                                    @endif
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td class="poprod-decpt">
                                                                            <button id = "attribute_button_0" type = "button" data-bs-toggle="modal"  data-bs-target="#attribute" onclick = "setItemAttributes('item_code-0', '0', true);"  class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                                                                            <input type ="hidden" id="item_array_0" name = "service_item[{{ $index }}]['attributes']" />

                                                                         </td>

                                                                        <!-- Item Name Input -->
                                                                        <td>
                                                                            <input type="text"
                                                                                id="item_name-{{ $index }}"
                                                                                readonly
                                                                                class="form-control mw-100 item-name"
                                                                                name="service_item[{{ $index }}]['servicename']">
                                                                        </td>
                                                                        <td class="poprod-decpt">
                                                                            <input type="text" id="ledger_{{$index}}" data-index = "{{$index}}" name="service_item[{{ $index }}]['ledger_code']" class="form-control mw-100 ledgerselecct ui-autocomplete-input" placeholder="Type to search...">
                                                                            <input type="hidden" id="ledger_id_{{$index}}" name="service_item[{{ $index }}]['ledger_id']" class="ladger-id">

                                                                         </td>
                                                                        <td class="poprod-decpt">
                                                                        <select id="ledger-group-{{ $index }}"
                                                                                class="form-select mw-100 ledger-group"
                                                                                name="service_item[{{$index}}]['ledger_group_id']">
                                                                                <option value="">Select Group</option>
                                                                            </select>
                                                                         </td>
                                                                        <td><a href="#"
                                                                                class="text-primary addRowItem"><i
                                                                                    data-feather="plus-square"></i></a>
                                                                        </td>
                                                                    </tr>



                                                                </tbody>


                                                            </table>

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
                                                                                    <option value="{{ $document->name }}">
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
                    </form>
                    <!-- Modal to add new record -->

                </section>


            </div>
        </div>
    </div>
    <!-- END: Content-->
    <div class="modal fade" id="reject" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">
                            Reject Land Parcel Application
                        </h4>
                        <p class="mb-0 fw-bold voucehrinvocetxt mt-0">{{ $land->name ?? '' }} | {{ $land->area ?? '' }} |
                            {{ $land->submission_date ?? '' }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('land.appr_rej') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body pb-2">
                        <div class="row mt-1">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            @if (isset($land->id))
                                                <input type="hidden" name="appr_rej_status" value="reject">
                                                <input type="hidden" name="appr_rej_land_id"
                                                    value="{{ $land->id }}">
                                            @endif
                                            <label class="form-label">Land Area <span class="text-danger">*</span></label>
                                            <input type="number" disabled value="{{ $land->area ?? '' }}"
                                                class="form-control" />
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="mb-1">
                                            <label class="form-label">Recommended Land Parcel Size <span
                                                    class="text-danger">*</span></label>
                                            @if (isset($land) && $land->status == 3)
                                                <input type="number" name="appr_rej_recommended_size"
                                                    value="{{ $land->appr_rej_recom_size ?? '' }}"
                                                    class="form-control" />
                                            @else
                                                <input type="number" name="appr_rej_recommended_size"
                                                    class="form-control" />
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-1">
                                    <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                    @if (isset($land) && $land->status == 3)
                                        <textarea class="form-control" name="appr_rej_remarks">{{ $land->appr_rej_recom_remark ?? '' }}</textarea>
                                    @else
                                        <textarea class="form-control" name="appr_rej_remarks"></textarea>
                                    @endif
                                </div>

                                <div class="mb-1">
                                    @if (isset($land) && $land->status == 3)
                                        @if (isset($land->id))
                                            <input type="hidden" name="stored_appr_rej_doc"
                                                value="{{ $land->appr_rej_doc ?? '' }}">
                                        @endif
                                        <label class="form-label">Upload Document</label>
                                        <input type="file" name="appr_rej_doc" class="form-control" />
                                        @if (isset($land) && !empty($land->appr_rej_doc))
                                            <div class="col-md-3 mt-1">
                                                <p><i data-feather='folder' class="me-50"></i><a
                                                        href="{{ asset('storage/' . $land->appr_rej_doc) }}"
                                                        style="color:green; font-size:12px;" target="_blank"
                                                        download>Approved Doc</a></p>
                                            </div>
                                        @endif
                                    @else
                                        <label class="form-label">Upload Document</label>
                                        <input type="file" name="appr_rej_doc" class="form-control" />
                                    @endif
                                </div>

                                @php
                                    $selectedValues =
                                        isset($land) && $land->appr_rej_behalf_of
                                            ? json_decode($land->appr_rej_behalf_of, true)
                                            : [];
                                @endphp
                                <div class="mb-1">
                                    <label class="form-label">On Behalf of <span class="text-danger">*</span></label>
                                    @if (isset($land) && $land->status == 3)
                                        <select class="form-select select2" multiple name="appr_rej_behalf_of[]">
                                            <option value="">Select</option>
                                            <option value="nishu"
                                                {{ in_array('nishu', $selectedValues) ? 'selected' : '' }}>Nishu Garg
                                            </option>
                                            <option value="mahesh"
                                                {{ in_array('mahesh', $selectedValues) ? 'selected' : '' }}>Mahesh Bhatt
                                            </option>
                                            <option value="inder"
                                                {{ in_array('inder', $selectedValues) ? 'selected' : '' }}>Inder Singh
                                            </option>
                                            <option value="shivangi"
                                                {{ in_array('shivangi', $selectedValues) ? 'selected' : '' }}>Shivangi
                                            </option>
                                        </select>
                                    @else
                                        <select class="form-select select2" multiple name="appr_rej_behalf_of[]">
                                            <option value="">Select</option>
                                            <option value="nishu">Nishu Garg</option>
                                            <option value="mahesh">Mahesh Bhatt</option>
                                            <option value="inder">Inder Singh</option>
                                            <option value="shivangi">Shivangi</option>
                                        </select>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-outline-secondary me-1 cancelButton">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade text-start" id="rescdule" tabindex="-1" aria-labelledby="myModalLabel17"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select
                            Pending Disbursal</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Loan Type</label>
                                <select class="form-select">
                                    <option>Select</option>
                                    <option>Home Loan</option>
                                    <option>Vehicle Loan</option>
                                    <option>Term Loan</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Customer Name</label>
                                <select class="form-select select2">
                                    <option>Select</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-1">
                                <label class="form-label">Application No.</label>
                                <select class="form-select select2">
                                    <option>Select</option>
                                </select>
                            </div>
                        </div>


                        <div class="col-md-3  mb-1">
                            <label class="form-label">&nbsp;</label><br />
                            <button class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                        </div>

                        <div class="col-md-12">


                            <div class="table-responsive">
                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Application No.</th>
                                            <th>Date</th>
                                            <th>Customer Name</th>
                                            <th>Loan Type</th>
                                            <th>Disbursal Milestone</th>
                                            <th>Disbursal Amt.</th>
                                            <th>Mobile No.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <div class="form-check form-check-primary">
                                                    <input type="radio" id="customColorRadio3" name="customColorRadio3"
                                                        class="form-check-input" checked="">
                                                </div>
                                            </td>
                                            <td>HL/2024/001</td>
                                            <td>20-07-2024</td>
                                            <td class="fw-bolder text-dark">Kundan Kumar</td>
                                            <td>Term</td>
                                            <td>1st floor completed</td>
                                            <td>200000</td>
                                            <td>9876787656</td>
                                        </tr>

                                        <tr>
                                            <td>
                                                <div class="form-check form-check-primary">
                                                    <input type="radio" id="customColorRadio3" name="customColorRadio3"
                                                        class="form-check-input" checked="">
                                                </div>
                                            </td>
                                            <td>HL/2024/001</td>
                                            <td>20-07-2024</td>
                                            <td class="fw-bolder text-dark">Kundan Kumar</td>
                                            <td>Term</td>
                                            <td>2nd floor completed</td>
                                            <td>200000</td>
                                            <td>nishu@gmail.com</td>
                                        </tr>





                                    </tbody>


                                </table>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i
                            data-feather="x-circle"></i> Cancel</button>
                    <button class="btn btn-primary btn-sm" data-bs-dismiss="modal"><i data-feather="check-circle"></i>
                        Process</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="attribute" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog  modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-2 mx-50 pb-2">
					<h1 class="text-center mb-1" id="shareProjectTitle">Select Attribute</h1>
					<p class="text-center">Enter the details below.</p>

					<div class="table-responsive-md customernewsection-form">
								<table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail" id = "attributes_table_modal" item-index = "">
									<thead>
										 <tr>
											<th>Attribute Name</th>
											<th>Attribute Value</th>
										  </tr>
										</thead>
										<tbody id = "attribute_table">

									   </tbody>


								</table>
							</div>
				</div>

				<div class="modal-footer justify-content-center">
						<button type="button" class="btn btn-outline-secondary me-1" onclick = "closeModal('attribute');">Cancel</button>
					    <button type="button" class="btn btn-primary" onclick = "closeModal('attribute');">Select</button>
				</div>
			</div>
		</div>
	</div>
@endsection
@section('scripts')
    <script>
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
        $(document).ready(function() {
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
        $(".addRowItem").click(function() {
            var rowCount = $("#tableBody").find('tr').length + 1; // Counter for row numbering, starting at 1

            var newRow = `
    <tr>
        <td>${rowCount}</td>
          <td>
                                                                            <select id="service-item-select-${rowCount-1}" class="form-select mw-100 service-item-select"
                                                                                name="service_item[${rowCount-1}]['servicetype']">
                                                                                <option>Select Category</option>
                                                                                @foreach ($categories as $categoryItem) <!-- Unique categories -->
                                                                                @if ($categoryItem->category_name) <!-- Check if category exists -->
                                                                                    <option value="{{ $categoryItem->category_name }}">
                                                                                        {{ $categoryItem->category_name }}
                                                                                    </option>
                                                                                @endif
                                                                            @endforeach

                                                                            </select>
                                                                        </td>

                                                                        <!-- Item Code Dropdown -->
                                                                        <td>
                                                                            <select id="item_code-${rowCount-1}" class="form-select mw-100 item_code" onchange="updateItemName(${rowCount-1})"
                                                                                name="service_item[${rowCount-1}]['servicecode']">
                                                                                <option value="">Select Code</option>
                                                                                @foreach ($items as $item) <!-- Unique categories -->
                                                                                @if ($item->item_code) <!-- Check if category exists -->
                                                                                    <option value="{{ $item->item_code }}" data-item-name="{{ $item->item_name }}" attribute-array="{{ json_encode($item->attributes)}}">
                                                                                        {{ $item->item_code }}
                                                                                    </option>
                                                                                @endif
                                                                            @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td class="poprod-decpt">
                                                                            <button id= "attribute_button_${rowCount-1}" type = "button" onclick = "setItemAttributes('item_code-${rowCount-1}', '${rowCount-1}', true);" data-bs-toggle="modal"  data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                                                                            <input type="hidden" id="item_array_${rowCount-1}" name = "service_item[${rowCount-1}]['attributes']" />

                                                                         </td>

                                                                        <!-- Item Name Input -->
                                                                        <td>
                                                                            <input type="text" id="item_name-${rowCount-1}" readonly class="form-control mw-100 item-name"
                                                                                name="service_item[${rowCount-1}]['servicename']">
                                                                        </td>

                                                                        <td class="poprod-decpt">
                                                                            <input type="text" id="ledger_${rowCount - 1}" data-index = "${rowCount - 1}" name="service_item[${rowCount - 1}]['ledger_code']" class="form-control mw-100 ledgerselecct ui-autocomplete-input" placeholder="Type to search...">
                                                                            <input type="hidden" id="ledger_id_${rowCount - 1}" name="service_item[${rowCount - 1}]['ledger_id']" class="ledger-id">

                                                                         </td>
                                                                        <td class="poprod-decpt">
                                                                            <select id="ledger-group-{{ $index }}"
                                                                                class="form-select mw-100 ledger-group"
                                                                                name="service_item[${rowCount - 1}]['ledger_group_id']">
                                                                                <option value="">Select Group</option>
                                                                            </select>
                                                                         </td>

                    <td><a href="#" class="text-danger trash"><i data-feather="trash-2"></i></a></td>
    </tr>`;

            $("#tableBody").append(newRow);
            feather.replace();
            initializeAutocomplete1('ledger_' + (rowCount - 1), rowCount - 1);

        });


        // Use event delegation to handle dynamically added file inputs
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


        // Remove row functionality
        $("#tableBody").on("click", ".trash", function(event) {
            event.preventDefault(); // Prevent default action for <a> tag
            $(this).closest('tr').remove(); // Remove the closest <tr> element
        });
        $("#tableDoc").on("click", ".trash", function(event) {
            event.preventDefault(); // Prevent default action for <a> tag
            $(this).closest('tr').remove(); // Remove the closest <tr> element
        });
    </script>


    <script>
        /*$('#series').on('change', function() {
                                        var book_id = $(this).val();
                                        var request = $('#document_no');

                                        request.val(''); // Clear any existing options

                                        if (book_id) {
                                            $.ajax({
                                                url: "{{ url('get-land-request') }}/" + book_id,
                                                type: "GET",
                                                dataType: "json",
                                                success: function(data) {
                                                    console.log(data);
                                                    if (data.requestno) {
                                                        request.val(data.requestno);
                                                    }
                                                }
                                            });
                                        }
                                    });*/
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        function updateItemCode(selectElement) {
            const selectedOption = selectElement.options[selectElement.selectedIndex]; // Get the selected option

            // Ensure selectedOption is valid before trying to access its attributes
            if (selectedOption) {
                // Get the item code and service code from the data attributes
                const itemCode = selectedOption.getAttribute('data-item-code');

                const itemName = selectedOption.getAttribute('data-item-name');

                // Get the row where the dropdown is located
                const row = selectElement.closest('tr');

                // Set the value of the item code textbox in the same row
                const itemCodeInput = row.querySelector('.item-code');
                const itemNameInput = row.querySelector('.item-name');
                if (itemCodeInput) {
                    itemCodeInput.value = itemCode || ''; // Set to empty if no selection
                }
                if (itemNameInput) {
                    itemNameInput.value = itemName || ''; // Set to empty if no selection
                }


            }
        }

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
                            document.getElementById('country').value = country || '';
                            document.getElementById('state').value = state || '';
                            document.getElementById('district').value = district || '';
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

        var items = @json($items); // Pass the items from your backend

        // Function to load item codes based on the selected category for a specific row
        function loadCodes(rowIndex) {
            var categorySelect = document.getElementById('service-item-select-' + rowIndex);
            var selectedOption = categorySelect.options[categorySelect.selectedIndex];
            var categoryId = selectedOption.value;

            // Get the item code dropdown for the specific row
            var codeSelect = document.getElementById('item_code-' + rowIndex);
            codeSelect.innerHTML = '<option>Select Code</option>';

            // Filter the items by selected category and populate the item codes
            var filteredItems = items.filter(item => item.category.id == categoryId);
            filteredItems.forEach(function(item) {
                var option = document.createElement('option');
                option.value = item.item_code;
                option.setAttribute('data-item-name', item.item_name);
                option.text = item.item_code;
                codeSelect.appendChild(option);
            });

            // Clear the item name input for the specific row
            document.getElementById('item_name-' + rowIndex).value = '';
        }

        // Function to update item name when a code is selected for a specific row
        function updateItemName(rowIndex) {
            var codeSelect = document.getElementById('item_code-' + rowIndex);
            var selectedOption = codeSelect.options[codeSelect.selectedIndex];
            var itemName = selectedOption.getAttribute('data-item-name');

            // Update the item name input for the specific row
            document.getElementById('item_name-' + rowIndex).value = itemName;
        }

        function getDocNumberByBookId() {
            let currentDate = new Date().toISOString().split('T')[0];
            let bookId = $('#series').val();
            let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId + "&document_date=" +
                currentDate;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        $("#book_code_input").val(data.data.book_code);
                        if (!data.data.doc.document_number) {
                            $("#document_no").val('');
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

        function setItemAttributes(elementId, rowindex, disabled = true) {
    document.getElementById('attributes_table_modal').setAttribute('item-index', rowindex);
    var elementIdForDropdown = "item_array_" + rowindex;
    const selectElement = document.getElementById("item_code-" + rowindex);
    const attributesTable = document.getElementById('attribute_table');

    console.log("item_code-" + rowindex);

    if (selectElement.value !== "") {
        const selectedOption = selectElement.options[selectElement.selectedIndex];

        // Retrieve the 'attribute-array' from the selected option
        const attributesJSON = JSON.parse(selectedOption.getAttribute('attribute-array') || '[]');

        // Retrieve existing attributes from the hidden input field
        const hiddenInput = document.getElementById(elementIdForDropdown);
        const existingAttributes = hiddenInput && hiddenInput.getAttribute('value')
            ? JSON.parse(hiddenInput.getAttribute('value'))
            : [];

        // Check if attributesJSON is empty or null
        if (!attributesJSON || attributesJSON.length === 0) {
            attributesTable.innerHTML = `
            <tr>
                <td colspan="2" class="text-center">No attributes available</td>
            </tr>
            `;
            document.getElementById('attribute_button_' + rowindex).disabled = true;
            return;
        }

        let innerHtml = ``;

        attributesJSON.forEach((element, index) => {
            let optionsHtml = ``;

            element.values_data.forEach(value => {
                // Check if the value is selected in the existing attributes
                const isSelected = existingAttributes.some(attr =>
                    attr.item_attribute_id === element.id && attr.value_id === value.id
                );

                optionsHtml += `
                <option value='${value.id}' ${isSelected ? 'selected' : ''}>${value.value}</option>
                `;
            });

            innerHtml += `
            <tr>
            <td>
            ${element.group_name}
            <input type="hidden" name="id" value="${element.id}">
            </td>
            <td>
            <select class="form-select select2" id="attribute_val_${index}" style="max-width:100% !important;" onchange="changeAttributeVal(this, ${elementIdForDropdown}, ${rowindex});">
                <option value="">Select</option>
                ${optionsHtml}
            </select>
            </td>
            </tr>
            `;
        });

        attributesTable.innerHTML = innerHtml;

        if (attributesJSON.length === 0) {
            document.getElementById('attribute_button_' + rowindex).disabled = true;
        } else {
            $("#attribute").modal("show");
            document.getElementById('attribute_button_' + rowindex).disabled = false;
        }
    } else {
        // If the selected value is null, show an empty table
        attributesTable.innerHTML = `
        <tr>
            <td colspan="2" class="text-center">No attributes available</td>
        </tr>
        `;

    }
}



            function changeAttributeVal(selectedElement, elementId, index) {
    // Get the table containing the attributes
    const attributesTable = document.getElementById("attributes_table_modal");
    const tbody = attributesTable.querySelector("tbody");

    // Prepare an array to store selected attributes
    let selectedAttributes = [];

    // Loop through each row in the table body
    Array.from(tbody.rows).forEach((row) => {
        // Find the hidden input and the select element in the current row
        const hiddenInput = row.querySelector('input[type="hidden"][name="id"]');
        const selectElement = row.querySelector("select");

        if (hiddenInput && selectElement) {
            // Get the attribute ID from the hidden input
            const attributeId = parseInt(hiddenInput.value, 10);

            // Get the selected value from the dropdown
            const selectedVal = parseInt(selectElement.value, 10);

            // Ensure valid values
            if (!isNaN(attributeId) && !isNaN(selectedVal) && selectedVal > 0) {
                selectedAttributes.push({
                    item_attribute_id: attributeId, // The attribute ID from the hidden input
                    value_id: selectedVal          // The selected value from the dropdown
                });
            }
        }
    });

    // Update the element with the new attributes in the required format
    elementId.setAttribute('value', JSON.stringify(selectedAttributes));
}

        // ******************

        $(document).ready(function() {
            $("#landparcel-form").on("submit", function(e) {
                console.log($('#status_val').val());
                if ($('#status_val').val() === "submitted") {
                
                    // Stop form submission

                    // Check if any .service-item-select or .item_code are empty
                    let hasEmptyFields = false;

                    $('.service-item-select').each(function() {
                        if ($(this).val() === "") {
                            hasEmptyFields = true;
                        }
                    });

                    $('.item_code').each(function() {
                        if ($(this).val() === "") {
                            hasEmptyFields = true;
                        }
                    });

                    // If any required field is empty, show toastr error
                    if (hasEmptyFields) {
                        toastr.error("Please fill all required fields (Service Item Select & Item Code).");
                        e.preventDefault();
                        return;
                    }
                    const addressLat = parseFloat($("#latitudevalue").val());
                    const addressLng = parseFloat($("#longitudevalue").val());

                    // const addressLat = parseFloat(25.5787726);
                    // const addressLng = parseFloat(91.8932535);
                    const fileInput = $("#uploadGeofence")[0].files;

                    return;

                    // if (!fileInput.length) {
                    //     toastr.error("Please upload a file.");
                    //     e.preventDefault();
                    //     return;
                    // }

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

                        if (!isValid) {
                            toastr.error(
                                "One or more uploaded coordinates are outside the allowed region."
                                );
                            e.preventDefault();
                            return;
                        } else {
                            $('#landparcel-form')[0].submit();
                        }
                    }).catch(err => {
                        toastr.error("Error reading the file. Please try again.");
                        e.preventDefault();
                        return;
                    });

                } else {
                    $('#landparcel-form')[0].submit();

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


        });

        
    initializeAutocomplete1("ledger_0", 0);

    function initializeAutocomplete1(selector, index) {
            $("#" + selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type:'ladger',
                            categoryId : null
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: `${item.name}`,
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    var $input = $(this);
                    var itemId = ui.item.id;
                    var itemName = ui.item.label;

                    $input.val(itemName);
                    $("#ledger_id_" + index).val(itemId);

                    onLedgerSelect(index, itemId);

                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#ledger_id_" + index).val("");
                        $("#ledger-group-" + index).val("");
                        document.getElementById('ledger-group-' + index).innerHTML = ``;
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
    }

    function onLedgerSelect(index, ledgerId)
    {
        const ledgerGroupElement = document.getElementById('ledger-group-' + index);
        if (ledgerGroupElement) {
            $.ajax({
                        url: '/ledgers/'+ ledgerId +'/groups',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            ledger_id: ledgerId,
                        },
                        success: function(data) {
                            if (Array.isArray(data)) {
                                var newGroupsHtml = ``;
                                data.forEach(group => {
                                    newGroupsHtml += `<option value = '${group.id}'>${group.name}</option>`
                                });
                                ledgerGroupElement.innerHTML = newGroupsHtml;
                            } else {
                                ledgerGroupElement.value = '';
                                ledgerGroupElement.innerHTML = '';
                            }
                        },
                        error: function(xhr) {
                            ledgerGroupElement.value = '';
                            ledgerGroupElement.innerHTML = '';
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });
        }
    }
    function closeModal(id)
        {
            $('#' + id).modal('hide');
        }

    </script>
@endsection
