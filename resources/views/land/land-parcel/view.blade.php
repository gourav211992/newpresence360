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
                                        <li class="breadcrumb-item active">View Detail</li>


                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="arrow-left-circle"></i> Back</button>


                            @if ($buttons['approve'])
                                <button class="btn btn-danger btn-sm" data-bs-target="#reject" data-bs-toggle="modal"><i
                                        data-feather="x-circle"></i> Reject</button>
                                <button data-bs-toggle="modal" data-bs-target="#approved" class="btn btn-success btn-sm"><i
                                        data-feather="check-circle"></i> Approve</button>
                            @endif

                            @if ($buttons['amend'])
                                <button type="button" data-bs-toggle="modal" data-bs-target="#amendmentconfirm"
                                    class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i>
                                    Amendment</button>
                            @endif
                       
                      
                    

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
                    <form id="landparcel-form" method="POST" action="{{ route('land-parcel.update') }}"
                        enctype='multipart/form-data'>
                        @csrf
                        <input type="hidden" name="id"
                            value="@if (request()->has('revisionNumber')) {{ $data->source_id }}@else{{ $data->id }} @endif">
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

                                                    @if (isset($view_detail))
                                                        @php
                                                            // Determine the badge class based on approval status
                                                            $mainBadgeClass = match ($data->document_status) {
                                                                'approved', 'approval_not_required' => 'success',
                                                                'draft', 'partially_approved' => 'warning',
                                                                'submitted' => 'info',
                                                                default => 'danger',
                                                            };

                                                            // Generate the status badge HTML
                                                            $statusClassMap = [
                                                                'warning' => 'badge-light-warning',
                                                                'success' => 'badge-light-success',
                                                                'danger' => 'badge-light-danger',
                                                                'info' => 'badge-light-info',
                                                            ];

                                                            $badgeClass =
                                                                $statusClassMap[$mainBadgeClass] ??
                                                                'badge-light-secondary';
                                                            $statusText = str_replace('_', ' ', $data->document_status);
                                                        @endphp

                                                        <div class="col text-right d-flex justify-content-end">
                                                            <span
                                                                class="badge rounded-pill {{ $badgeClass }} badgeborder-radius mt-2">
                                                                {{ $statusText }}
                                                            </span>

                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <input type="hidden" name="status_val" id=status_val>
                                            <div class="col-md-8">

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Series <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select class="form-select" name="series" required id="series"
                                                            disabled>
                                                            <option value="" disabled selected>Select</option>
                                                            @foreach ($series as $key => $serie)
                                                                <option value="{{ $serie->id }}"
                                                                    @if ($serie->id == $data->book_id) selected @endif>
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
                                                            id="document_no" value="{{ $data->document_no }}" required
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
                                                            id="name" value="{{ $data->name }}" readonly>
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
                                                        <textarea class="form-control" rows="1" name="description" readonly>{{ $data->description }}</textarea>
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
                                                            id="surveyno" value="{{ $data->surveyno }}" readonly>
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
                                                                    @if (empty($data->status)) checked @endif
                                                                    {{ $data->status == '1' ? 'checked' : '' }} required
                                                                    readonly>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio3">Active</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="customColorRadio4"
                                                                    name="status" class="form-check-input"
                                                                    value="0"
                                                                    {{ $data->status == '0' ? 'checked' : '' }} required
                                                                    readonly>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="customColorRadio4">Inactive</label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                </div>


                                            </div>

                                            <div class="col-md-4">
                                                <div
                                                    class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
                                                    <h5
                                                        class="mb-2 text-dark border-bottom pb-50 d-flex align-items-center justify-content-between">
                                                        <strong><i data-feather="arrow-right-circle"></i> Approval History
                                                            {{ $currNumber }}</strong>
                                                        <strong
                                                            class="badge rounded-pill badge-light-secondary amendmentselect">Rev.
                                                            No.
                                                            <select class="form-select revisionNumber">

                                                                <option value=""
                                                                    @if ($currNumber == '') selected @endif>None
                                                                </option>
                                                                @foreach ($revisionNumbers as $revisionNumber)
                                                                    @if ($revisionNumber != 0)
                                                                        <option
                                                                            @if ($currNumber == $revisionNumber) selected @endif
                                                                            value="{{ $revisionNumber }}">
                                                                            {{ $revisionNumber }}</option>
                                                                    @endif
                                                                @endforeach
                                                            </select>
                                                        </strong>
                                                    </h5>
                                                    <ul class="timeline ms-50 newdashtimline ">
                                                        @foreach ($history as $his)
                                                            <?php
                                                            $badgeClass = match ($his->approval_type) {
                                                                'approve' => 'success',
                                                                'approval_not_required' => 'success',
                                                                'draft' => 'warning',
                                                                'submitted' => 'info',
                                                                'partially_approved' => 'warning',
                                                                default => 'danger',
                                                            };
                                                            ?>
                                                            <li class="timeline-item">
                                                                <span
                                                                    class="timeline-point timeline-point-indicator timeline-point-{{ $badgeClass }}"></span>
                                                                <div class="timeline-event">
                                                                    <div
                                                                        class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                        <h6>{{ucfirst($his->name ?? $his?->user?->name ?? 'NA')}}</h6>
                                                                        <span
                                                                            class="badge rounded-pill badge-light-{{ $badgeClass }}">{{ ucfirst($his->approval_type) }}</span>
                                                                    </div>
                                                                    <h5>({{ $his->approval_date }})</h5>
                                                                    <p>{{ $his->remarks }}</p>
                                                                </div>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
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
                                                                            value="{{ $data->khasara_no }}" readonly
                                                                            onchange="cleanInput(this)">
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
                                                                    <input type="text" class="form-control" name="plot_area" value="{{ $data->plot_area }}" readonly onchange="cleanInput(this)" required>
                                                                    @error('plot_area')
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>

                                                                <div class="col-md-4">
                                                                    <select class="form-select" name="area_unit" disabled>
                                                                        <option value="" {{ $data->area_unit == '' ? 'selected' : '' }}> Unit</option>
                                                                        <option value="Acres" {{ $data->area_unit == 'Acres' ? 'selected' : '' }}>Acres</option>
                                                                        <option value="Hectares" {{ $data->area_unit == 'Hectares' ? 'selected' : '' }}>Hectares</option>
                                                                        <option value="squarefeet" {{ $data->area_unit == 'squarefeet' ? 'selected' : '' }}>Square Feet</option>
                                                                        <option value="squaremeter" {{ $data->area_unit == 'squaremeter' ? 'selected' : '' }}>Square Meter</option>
                                                                        <option value="bigha" {{ $data->area_unit == 'bigha' ? 'selected' : '' }}>Bigha</option>
                                                                    </select>
                                                                </div>

                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Dimension <span class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" name="dimension" value="{{ $data->dimension }}" onchange="cleanInput(this)" required readonly>
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
                                                                    <input type="text" class="form-control" name="land_valuation" value="{{ $data->land_valuation }}" onchange="cleanInput(this)" readonly>
                                                                </div>

                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Handover Date <span class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-8">
                                                                    <input type="date" class="form-control" name="handoverdate" value="{{ $data->handoverdate }}" required readonly>
                                                                    @error('handoverdate')
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Address <span class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" name="address" id="address" placeholder=" " value="{{ $data->address }}"
                                                                        onchange="cleanInput(this)" required readonly>
                                                                    @error('address')
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>

                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">District <span class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" name="district" value="{{ $data->district }}" required readonly>
                                                                    @error('district')
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">State <span class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" name="state" value="{{ $data->state }}" required readonly>
                                                                    @error('state')
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>


                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Country <span class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" name="country" value="{{ $data->country }}" required readonly>
                                                                    @error('country')
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>

                                                            <div class="row align-items-center mb-1">
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Pincode <span class="text-danger">*</span></label>
                                                                </div>

                                                                <div class="col-md-8">
                                                                    <input type="text" class="form-control" name="pincode" value="{{ $data->pincode }}" required readonly>
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
                                                                    <textarea type="text" rows="4" class="form-control" name="remarks" placeholder="Enter Remarks here..." readonly>{{ $data->remarks }}</textarea>
                                                                    @error('remarks')
                                                                        <div class="text-danger">{{ $message }}</div>
                                                                    @enderror
                                                                </div>
                                                            </div>


                                                        </div>

                                                        <div class="col-md-7">

                                                            <div class="row align-items-end mb-1">
                                                                <div class="col-md-2">
                                                                    <label class="form-label">Latitude:</label>
                                                                    <h4><strong id="latitude">{{ $data->latitude }}</strong></h4>
                                                                </div>
                                                                <div class="col-md-3">
                                                                    <label class="form-label">Longitude :</label>
                                                                    <h4><strong id="longitude">{{ $data->longitude }}</strong></h4>
                                                                </div>
                                                                <div class="col-md-7 text-sm-end  action-button">
                                                                    <!-- <a href="{{ url('/assets/sample_land_locations.csv') }}" target="_blank" class="font-small-2 mb-1 me-1">
                                                                        <i data-feather="download"></i> Download Sample
                                                                    </a> -->
                                                                    <div class="image-uploadhide mt-50">
                                                                        <!-- <a href="attribute.html" class="btn btn-outline-primary btn-sm">
                                                                            <i data-feather="plus"></i> Upload Geofence
                                                                        </a>
                                                                        <input type="file" name="geofence" class="" /> -->
                                                                        <input type="hidden" name="latitude" id="latitudevalue" />
                                                                        <input type="hidden" name="longitude" id="longitudevalue" />
                                                                    </div>

                                                                </div>
                                                                <!-- <input id="pac-input" class="controls" type="text" placeholder="Search for a location"> -->
                                                                <div id="map"></div>
                                                            </div>
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
                                                                        <th width="200px">Service Code</th>
                                                                        <th width="100px">Attributes</th>
                                                                        <th width="200px">Service Name</th>
                                                                        <th width="200px">Ledger Code</th>
                                                                        <th width="200px">Ledger Group</th>
                                                                        <th width="40px">Action</th>
                                                                    </tr>
                                                                </thead>
                                                                @php

                                                                    // Original JSON string from your input
                                                                    $jsonString = $data->service_item;
                                                                    // Step 1: Remove the single quotes around keys using a regular expression
                                                                    $fixedJsonString = str_replace(
                                                                        "'",
                                                                        '',
                                                                        $jsonString,
                                                                    );

                                                                    // Step 2: Decode the JSON string into a PHP array
                                                                    $serviceItems = json_decode($fixedJsonString, true);
                                                                    $serviceItems = array_filter(
                                                                        $serviceItems,
                                                                        function ($item) {
                                                                            return isset(
                                                                                $item['servicetype'],
                                                                                $item['servicecode'],
                                                                                $item['servicename'],
                                                                            ) &&
                                                                                $item['servicetype'] !== 'Select' &&
                                                                                !is_null($item['servicetype']) &&
                                                                                !is_null($item['servicecode']) &&
                                                                                $item['servicecode'] !== 'Select' &&
                                                                                !is_null(
                                                                                    $item['servicename'] &&
                                                                                        $item['servicename'] !==
                                                                                            'Select',
                                                                                );
                                                                        },
                                                                    );

                                                                    // Optional: Debug to see the output

                                                                @endphp

                                                                <tbody id="tableBody">
                                                                    @php $index=0;@endphp
                                                                    @if ($serviceItems)
                                                                    @foreach ($serviceItems as $item)
                                                                    <tr>
                                                                        <td>{{ $index+1 }}</td>

                                                                        <td>
                                                                            <select disabled
                                                                                id="service-item-select-{{ $index }}"
                                                                                class="loadcodech form-select mw-100 service-item-select"
                                                                                name="service_item[{{ $index }}]['servicetype']">
                                                                                <option>Select Category</option>
                                                                                @foreach ($categories as $categoryItem)
                                                                                    <!-- Use collect() to ensure it's treated as a collection -->
                                                                                    <option
                                                                                        value="{{ $categoryItem->category_name }}"
                                                                                        @if ($categoryItem->category_name == $item['servicetype']) selected @endif>
                                                                                        {{ $categoryItem->category_name }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>

                                                                        </td>
                                                                        <input type="hidden"
                                                                            name="code[{{ $index }}]"
                                                                            value="{{ $item['servicecode'] }}">
                                                                        <!-- Item Code Dropdown -->
                                                                        <td>
                                                                            <select disabled
                                                                                id="item_code-{{ $index }}"
                                                                                class="form-select mw-100"
                                                                                onchange="updateItemName({{ $index }})"
                                                                                name="service_item[{{ $index }}]['servicecode']">
                                                                                <option value="">Select Code
                                                                                </option>
                                                                                @foreach ($items as $itemlist)
                                                                                    <!-- Unique categories -->
                                                                                    @if ($itemlist->item_code)
                                                                                        <!-- Check if category exists -->
                                                                                        <option
                                                                                            value="{{ $itemlist->item_code }}"
                                                                                            data-item-name="{{ $itemlist->item_name }}"
                                                                                            attribute-array="{{ json_encode($itemlist->attributes)}}"

                                                                                            @if ($itemlist->item_code == $item['servicecode']) selected @endif>
                                                                                            {{ $itemlist->item_code }}
                                                                                        </option>
                                                                                    @endif
                                                                                @endforeach


                                                                            </select>
                                                                        </td>
                                                                        <td class="poprod-decpt">
                                                                            <button id = "attribute_button_{{$index}}" type = "button" onclick = "setItemAttributes('item_code-{{$index}}', '{{$index}}', true);" data-bs-toggle="modal"  data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                                                                            <input type = "hidden" @isset($item['attributes']) value="{{$item['attributes']}}" @endisset id="item_array_{{$index}}" name = "service_item[{{$index}}]['attributes']"  />

                                                                         </td>



                                                                        <!-- Item Name Input -->
                                                                        <td>
                                                                            <input readonly type="text"
                                                                                id="item_name-{{ $index }}"
                                                                                readonly
                                                                                class="form-control mw-100 item-name"
                                                                                value="{{ $item['servicename'] }}"
                                                                                name="service_item[{{ $index }}]['servicename']">
                                                                        </td>
                                                                        <td class="poprod-decpt">
                                                                            <input type="text" readonly id="ledger_{{$index}}" data-index = "{{$index}}" value="{{ $item['ledger_code']??"" }}" name="service_item[{{ $index }}]['ledger_code']" class="form-control mw-100 ledgerselecct ui-autocomplete-input" placeholder="Type to search...">
                                                                            <input type="hidden" id="ledger_id_{{$index}}"  name="service_item[{{ $index }}]['ledger_id']" value="{{ $item['ledger_id'] ??"" }}" class="ladger-id">

                                                                         </td>
                                                                        <td class="poprod-decpt">
                                                                        <select id="ledger-group-{{ $index }}" disabled
                                                                                class="form-select mw-100 ledger-group"
                                                                                name="service_item[{{$index}}]['ledger_group_id']">

                                                                                @foreach($groups as $group)
                                                                                @if(isset($item['ledger_group_id']) && $group->id."" === $item['ledger_group_id'] )
                                                                                <option value="{{$group->id}}">{{$group->name}}</option>
                                                                                @endif
                                                                                @endforeach
                                                                            </select>
                                                                         </td>
                                                                        
                                                                        <td>

                                                                        </td>
                                                                    </tr>
                                                                    @php $index++; @endphp
                                                                @endforeach
                                                          @endif

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
                                                                    @php
                                                                        $documents = $data->attachments
                                                                            ? json_decode($data->attachments, true)
                                                                            : [];
                                                                        $i = 0;
                                                                    @endphp

                                                                    @foreach ($documents as $key => $file)
                                                                        @isset($file['name'])
                                                                            @php
                                                                                $documentName = $file['name'];
                                                                                $i++;
                                                                            @endphp
                                                                            <tr>
                                                                                <td>{{ $i }}</td>
                                                                                <td>
                                                                                    <select class="form-select mw-100"
                                                                                        name="documentname[{{ $i }}]"
                                                                                        disabled>
                                                                                        <option value="">Select</option>
                                                                                        @foreach ($doc_type as $doc)
                                                                                            <option
                                                                                                value="{{ $doc->name }}"
                                                                                                {{ $doc->name == $documentName ? 'selected' : '' }}>
                                                                                                {{ ucwords(str_replace('-', ' ', $doc->name)) }}
                                                                                            </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </td>
                                                                                <td>
                                                                                    <input type="file" multiple
                                                                                        class="form-control mw-100"
                                                                                        name="attachments[{{ $i }}][]"
                                                                                        id="attachments-{{ $i }}"
                                                                                        readonly>
                                                                                </td>
                                                                                <td id="preview-{{ $i }}">
                                                                                    @isset($file['files'])
                                                                                        @foreach ($file['files'] as $key1 => $fileGroup)
                                                                                            <div class="image-uplodasection"
                                                                                                data-file-index="{{ $key1 }}">
                                                                                                <i data-feather="file-text"
                                                                                                    class="fileuploadicon"></i>
                                                                                                <!-- <span class="filename">{{ $fileGroup }}</span> -->
                                                                                                <input type="hidden"
                                                                                                    name="oldattachments[{{ $i }}][]"
                                                                                                    value="{{ $fileGroup }}">
                                                                                                <div class="delete-img text-danger"
                                                                                                    data-file-index="{{ $i }}"
                                                                                                    data-old-file="{{ $fileGroup }}">
                                                                                                    <i data-feather="x"></i>
                                                                                                </div>
                                                                                            </div>
                                                                                        @endforeach
                                                                                    @endisset
                                                                                </td>
                                                                                <td>
                                                                                    <a href="#"
                                                                                        class="text-danger removeRow"><i
                                                                                            data-feather="minus-square"></i></a>
                                                                                </td>
                                                                            </tr>
                                                                        @endisset
                                                                    @endforeach

                                                                    <!-- Row for adding new files -->
                                                                    <tr>
                                                                        <td>{{ count($documents) + 1 }}</td>
                                                                        <td>
                                                                            <select class="form-select mw-100"
                                                                                name="documentname[{{ count($documents) + 1 }}]"
                                                                                disabled>
                                                                                <option value="">Select</option>
                                                                                @foreach ($doc_type as $document)
                                                                                    <option value="{{ $document->name }}">
                                                                                        {{ ucwords(str_replace('-', ' ', $document->name)) }}
                                                                                    </option>
                                                                                @endforeach
                                                                                <!-- Other options... -->
                                                                            </select>
                                                                        </td>
                                                                        <td>
                                                                            <input type="file" multiple
                                                                                class="form-control mw-100"
                                                                                name="attachments[{{ count($documents) + 1 }}][]"
                                                                                id="attachments-{{ count($documents) + 1 }}"
                                                                                disabled>
                                                                        </td>
                                                                        <td id="preview-{{ count($documents) + 1 }}">
                                                                        </td>
                                                                        <td>
                                                                            <a href="#"
                                                                                class="text-primary addRow"><i
                                                                                    data-feather="plus-square"></i></a>
                                                                        </td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>

                                                        <!-- Hidden field to store deleted old attachments -->
                                                        <input type="hidden" name="old_attachments_delete[]"
                                                            id="old_attachments_delete">


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
    {{-- Amendment Modal --}}
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
    <div class="modal fade text-start alertbackdropdisabled" id="amendmentconfirm" tabindex="-1"
        aria-labelledby="myModalLabel1" aria-hidden="true" data-bs-backdrop="false">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body alertmsg text-center warning">
                    <i data-feather='alert-circle'></i>
                    <h2>Are you sure?</h2>
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>Land</strong>? After Amendment this
                        action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="amendmentSubmit" class="btn btn-primary">Confirm</button>
                </div>
            </div>
        </div>
    </div>
@endsection
<div class="modal fade" id="approved" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">

                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Approve Land
                        Parcel Application</h4>
                    <p class="mb-0 fw-bold voucehrinvocetxt mt-0">{{ $data->name ?? '' }} |
                        {{ $data->plot_area ?? '' }} | {{ $data->handoverdate ?? '' }}</p>
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
                                        @if (isset($data->id))
                                            <input type="hidden" name="appr_rej_status" value="approve">
                                            <input type="hidden" name="appr_rej_land_id"
                                                value="{{ $data->id }}">
                                        @endif
                                        <label class="form-label">Land Area <span class="text-danger">*</span></label>
                                        <input type="number" disabled value="{{ $data->plot_area ?? '' }}"
                                            class="form-control" />
                                    </div>
                                </div>


                            </div>

                            <div class="mb-1">
                                <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                @if (isset($data) && $data->status == 2)
                                    <textarea class="form-control" name="appr_rej_remarks">{{ $data->appr_rej_recom_remark ?? '' }}</textarea>
                                @else
                                    <textarea class="form-control" name="appr_rej_remarks"></textarea>
                                @endif
                            </div>

                            <div class="mb-1">
                                @if (isset($data) && $data->status == 2)
                                    @if (isset($data->id))
                                        <input type="hidden" name="stored_appr_rej_doc"
                                            value="{{ $data->appr_rej_doc ?? '' }}">
                                    @endif
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" name="appr_rej_doc" class="form-control" />
                                    @if (isset($data) && !empty($data->appr_rej_doc))
                                        <div class="col-md-3 mt-1">
                                            <p><i data-feather='folder' class="me-50"></i><a
                                                    href="{{ asset('storage/' . $data->appr_rej_doc) }}"
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
                                    isset($data) && $data->appr_rej_behalf_of
                                        ? json_decode($data->appr_rej_behalf_of, true)
                                        : [];
                            @endphp
                            <div class="mb-1">
                                <label class="form-label">On Behalf of <span class="text-danger">*</span></label>
                                @if (isset($data) && $data->status == 2)
                                <select class="form-select select2" multiple name="appr_rej_behalf_of[]">
                                    <option value="">Select</option>
                                    @foreach ($approvers as $approver)
                                        <option value="{{ $approver->id }}" 
                                            {{ isset($selectedValues) && in_array($approver->id, $selectedValues) ? 'selected' : '' }}>
                                            {{ $approver->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <select class="form-select select2" multiple name="appr_rej_behalf_of[]">
                                    <option value="">Select</option>
                                    @foreach ($approvers as $approver)
                                        <option value="{{ $approver->id }}">{{ $approver->name }}</option>
                                    @endforeach
                                </select>
                            @endif 
                        </div>
                    </div>
                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary me-1 cancelButton"
                        data-bs-dismiss="modal" aria-label="Close">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </form>
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
                    <p class="mb-0 fw-bold voucehrinvocetxt mt-0">{{ $data->name ?? '' }} |
                        {{ $data->plot_area ?? '' }} | {{ $data->handoverdate ?? '' }}</p>
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
                                        @if (isset($data->id))
                                            <input type="hidden" name="appr_rej_status" value="reject">
                                            <input type="hidden" name="appr_rej_land_id"
                                                value="{{ $data->id }}">
                                        @endif
                                        <label class="form-label">Land Area <span class="text-danger">*</span></label>
                                        <input type="number" disabled value="{{ $data->plot_area ?? '' }}"
                                            class="form-control" />
                                    </div>
                                </div>

                            </div>

                            <div class="mb-1">
                                <label class="form-label">Remarks <span class="text-danger">*</span></label>
                                @if (isset($data) && $data->status == 3)
                                    <textarea class="form-control" name="appr_rej_remarks">{{ $data->appr_rej_recom_remark ?? '' }}</textarea>
                                @else
                                    <textarea class="form-control" name="appr_rej_remarks"></textarea>
                                @endif
                            </div>

                            <div class="mb-1">
                                @if (isset($data) && $data->status == 3)
                                    @if (isset($data->id))
                                        <input type="hidden" name="stored_appr_rej_doc"
                                            value="{{ $data->appr_rej_doc ?? '' }}">
                                    @endif
                                    <label class="form-label">Upload Document</label>
                                    <input type="file" name="appr_rej_doc" class="form-control" />
                                    @if (isset($data) && !empty($data->appr_rej_doc))
                                        <div class="col-md-3 mt-1">
                                            <p><i data-feather='folder' class="me-50"></i><a
                                                    href="{{ asset('storage/' . $data->appr_rej_doc) }}"
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
                                    isset($data) && $data->appr_rej_behalf_of
                                        ? json_decode($data->appr_rej_behalf_of, true)
                                        : [];
                            @endphp
                            <div class="mb-1">
                                <label class="form-label">On Behalf of <span class="text-danger">*</span></label>
                                @if (isset($data) && $data->status == 3)
                                <select class="form-select select2" multiple name="appr_rej_behalf_of[]">
                                    <option value="">Select</option>
                                    @foreach ($approvers as $approver)
                                        <option value="{{ $approver->id }}" 
                                            {{ isset($selectedValues) && in_array($approver->id, $selectedValues) ? 'selected' : '' }}>
                                            {{ $approver->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @else
                                <select class="form-select select2" multiple name="appr_rej_behalf_of[]">
                                    <option value="">Select</option>
                                    @foreach ($approvers as $approver)
                                        <option value="{{ $approver->id }}">{{ $approver->name }}</option>
                                    @endforeach
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

@section('scripts')
    <script>
        $(document).ready(function() {
            // Handle delete functionality for old attachments
            $(document).on('click', '.delete-img', function() {
                var fileGroup = $(this).data('old-file'); // Get the old file name
                var fileIndex = $(this).data('file-index'); // Get the file index

                // Check if this is an old file
                if (fileGroup) {
                    // Mark the file for deletion (append to hidden input)
                    var deletedFiles = $('#old_attachments_delete').val() ? $('#old_attachments_delete')
                        .val().split(',') : [];
                    deletedFiles.push(fileGroup);
                    $('#old_attachments_delete').val(deletedFiles.join(
                    ',')); // Update the hidden input with files to delete
                }

                // Remove the preview
                $(this).closest('.image-uplodasection').remove();
            });

            // Handle delete functionality for new file uploads (dynamic)
            $(document).on('click', '.delete-img', function() {
                var fileIndex = $(this).data('file-index'); // Get the file index
                var inputElement = $(this).closest('td').find(
                'input[type="file"]'); // Get the file input element
                var previewContainer = $(this).closest('.image-uplodasection')
            .parent(); // Get the preview container

                // Use DataTransfer to update file input
                if (inputElement[0].files) {
                    var dt = new DataTransfer();
                    var files = inputElement[0].files;

                    // Add all files except the one to delete
                    for (var i = 0; i < files.length; i++) {
                        if (i !== fileIndex) {
                            dt.items.add(files[i]);
                        }
                    }

                    // Update the file input with the new file list
                    inputElement[0].files = dt.files;
                }

                // Remove the preview
                previewContainer.children(`[data-file-index="${fileIndex}"]`).remove();

                // Reindex the remaining previews
                var remainingPreviews = previewContainer.children();
                remainingPreviews.each(function(index) {
                    $(this).attr('data-file-index', index);
                    $(this).find('.delete-img').attr('data-file-index', index);
                });

                // If no files left, clear the input value
                if (dt.files.length === 0) {
                    inputElement.val('');
                }
            });
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
                                                                            @endforeach   </select>
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
                                                                             <select id="service-item-select" class="form-select mw-100" name="service_item[${rowCount-1}]['servicetype']" onchange="updateItemCode(this)" required>
                                                                                   <option>Select</option>
                                                                                   @foreach ($items as $item)
                                                                                   <option value="{{ $item->category->id }}"  data-item-code="{{ $item->item_code }}" data-item-name="{{ $item->item_name }}">
                                                                                       {{ $item->category->name }}
                                                                                   </option>
                                                                               @endforeach
                                                                               </select>
                                                                           </td>
                                                                           <td>
                                                                               <input type="text" id="item_code" name="service_item[${rowCount-1}]['servicecode']" placeholder="Select" readonly class="form-control mw-100 ledgerselecct item-code" />
                                                                            </td>
                                                                                         <td>
                        <input type="text" readonly class="form-control mw-100 item-name" name="service_item[${rowCount-1}]['servicename']">
                    </td>
                    <td><a href="#" class="text-danger trash"><i data-feather="trash-2"></i></a></td>
    </tr>`;

            $("#tableBody").append(newRow);
            feather.replace();

        });


        // Use event delegation to handle dynamically added file inputs
        $(document).on('change', 'input[type="file"]', function(e) {
            var rowIndex = $(this).attr('id').split('-')[1]; // Extract row index from the file input's id
            handleFileUpload(e, `#preview-${rowIndex}`);
        });

        // Function to handle file upload preview without clearing old previews
        function handleFileUpload(event, previewElement) {
            var files = event.target.files;
            var previewContainer = $(previewElement); // The container where previews will appear

            // Check if there are new files
            if (files.length > 0) {
                // Loop through each selected file
                for (var i = 0; i < files.length; i++) {
                    // Generate the file preview div dynamically for new files
                    var fileIcon = `
                <div class="image-uplodasection expenseadd-sign new-file-preview" data-file-index="${i}">
                    <i data-feather="file-text" class="fileuploadicon"></i>
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

            // Add event listener to delete the new file preview when clicked
            previewContainer.find('.delete-img').off('click').on('click', function() {
                var fileIndex = $(this).parent().data('file-index'); // Get the correct index from parent
                removeNewFilePreview(fileIndex, previewContainer, event.target);
            });
        }

        // Function to remove a new file preview from the FileList
        function removeNewFilePreview(fileIndex, previewContainer, inputElement) {
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

            // Now re-index the remaining file previews for new files
            var remainingPreviews = previewContainer.children('.new-file-preview'); // Only for new files
            remainingPreviews.each(function(index) {
                $(this).attr('data-file-index', index); // Update data-file-index correctly
                $(this).find('.delete-img').attr('data-file-index', index); // Also update delete button index
            });

            // Debugging logs
            console.log(`Remaining files after deletion: ${dt.files.length}`);
            console.log(`Remaining new file previews: ${remainingPreviews.length}`);

            // If no files are left after deleting, reset the file input
            if (dt.files.length === 0) {
                inputElement.value = ""; // Clear the input value to reset it
            }
        }

        // Event listener for deleting old files
        $(document).on('click', '.delete-old-file', function() {
            var fileIndex = $(this).data('file-index'); // Get the index of the old file to be deleted
            var previewContainer = $(this).closest('.image-uplodasection'); // Find the correct preview container

            // Remove the old file preview
            previewContainer.remove();

            // Optionally, add logic here to mark the old file for deletion in the backend
        });






        // Remove row functionality
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
        window.addEventListener('load', function() {
            // Declare marker variable outside the condition
            var marker = null;
            var map;
            @if (isset($locations) && count($locations) > 0)
                console.log("{{ $locations }}");
                map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 16,
                    center: {
                        lat: {{ $locations[0]->latitude }},
                        lng: {{ $locations[0]->longitude }}
                    }
                });

                var lineSymbol = {
                    path: 'M 0,-1 0,1',
                    strokeOpacity: 1,
                    scale: 4
                };

                // Array to hold coordinates for the polyline
                var lineCoordinates = [];
                @foreach ($locations as $location)
                    lineCoordinates.push({
                        lat: {{ $location->latitude }},
                        lng: {{ $location->longitude }}
                    });
                @endforeach

                // Create polyline for the path
                var polyline = new google.maps.Polyline({
                    path: lineCoordinates,
                    geodesic: false,
                    strokeColor: '#000000', // Line color
                    strokeOpacity: 1.0,
                    strokeWeight: 0,
                    icons: [{
                        icon: lineSymbol,
                        offset: '0',
                        repeat: '20px'
                    }],
                });

                polyline.setMap(map); // Add the polyline to the map
                marker = new google.maps.Marker({
                    position: {
                        lat: {{ $locations[0]->latitude }},
                        lng: {{ $locations[0]->longitude }}
                    },
                    map: map, // Add the marker to the map
                    title: 'Center Location'
                });
            @else
                console.log({{ $data->latitude }});
                // Default map setup when no locations exist
                map = new google.maps.Map(document.getElementById('map'), {
                    zoom: 8,
                    center: {
                        lat: {{ $data->latitude ? $data->latitude : '28.501851443923478' }},
                        lng: {{ $data->longitude ? $data->longitude : '77.39757531317296' }}
                    }
                });

                marker = new google.maps.Marker({
                    position: {
                        lat: {{ $data->latitude ? $data->latitude : '28.501851443923478' }},
                        lng: {{ $data->longitude ? $data->longitude : '77.39757531317296' }}
                    },
                    map: map,
                    draggable: true
                });

                geocodeLatLng(geocoder, marker.getPosition().lat(), marker.getPosition().lng());
                attachMarkerDragEvent(marker, geocoder, map);
            @endif

            // Attach SearchBox for place input
            var formInput = document.getElementById('address');
            var formSearchBox = new google.maps.places.SearchBox(formInput);

            formSearchBox.addListener('places_changed', function() {
                handlePlacesChange(formSearchBox, map);
                mapInput.value = formInput.value;
            });

            // Function to handle the change of places in the search input
            function handlePlacesChange(searchBox, map) {
                var places = searchBox.getPlaces();
                if (places.length === 0) return;

                var bounds = new google.maps.LatLngBounds();
                places.forEach(function(place) {
                    if (!place.geometry || !place.geometry.location) return;

                    if (marker) {
                        marker.setPosition(place.geometry.location);
                    } else {
                        marker = new google.maps.Marker({
                            position: place.geometry.location,
                            map: map,
                            draggable: true
                        });
                        attachMarkerDragEvent(marker, geocoder, map);
                    }

                    map.fitBounds(bounds.extend(place.geometry.location));
                    updateLatLngInputs(place.geometry.location.lat(), place.geometry.location.lng());
                    geocodeLatLng(geocoder, place.geometry.location.lat(), place.geometry.location.lng());
                });
            }

            // Function to attach marker drag event
            function attachMarkerDragEvent(marker, geocoder, map) {
                google.maps.event.addListener(marker, 'dragend', function(event) {
                    updateLatLngInputs(event.latLng.lat(), event.latLng.lng());
                    geocodeLatLng(geocoder, event.latLng.lat(), event.latLng.lng());
                });
            }

            // Function to update lat/lng inputs
            function updateLatLngInputs(lat, lng) {
                document.getElementById('latitude').innerHTML = lat;
                document.getElementById('longitude').innerHTML = lng;
                document.getElementById('latitudevalue').value = lat;
                document.getElementById('longitudevalue').value = lng;
            }

            // Function to reverse geocode lat/lng to an address
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

                            // Update other address fields
                            var addressComponents = results[0].address_components;
                            var country = '',
                                state = '',
                                district = '',
                                postalCode = '';
                            addressComponents.forEach(function(component) {
                                if (component.types.includes("country")) country = component
                                    .long_name;
                                if (component.types.includes("administrative_area_level_1")) state =
                                    component.long_name;
                                if (component.types.includes("administrative_area_level_2"))
                                    district = component.long_name;
                                if (component.types.includes("postal_code")) postalCode = component
                                    .long_name;
                            });

                            document.getElementById('country').value = country || '';
                            document.getElementById('state').value = state || '';
                            document.getElementById('district').value = district || '';
                            document.getElementById('pincode').value = postalCode || '';
                        }
                    } else {
                        console.log("Geocoder failed: " + status);
                    }
                });
            }
        });




        // Example usage: call removePolyline() when needed


        $('#series').on('change', function() {
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
        });
    </script>
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


        document.addEventListener('DOMContentLoaded', function() {
            // submit button
            $('.submission_val').click(function() {
                let data_val = $(this).attr('data-val');
                if (data_val == 'draft') {
                    $("#status_val").val(data_val);
                } else {
                    $("#status_val").val(data_val);
                }
            });
        });
        var inputs = document.querySelectorAll('input, select, textarea');


        $(function() {

            @php $source_id = request()->has('revisionNumber')&&request()->input('revision_number')!=""?$data->source_id:$data->id; @endphp
            $(".revisionNumber").change(function() {
                window.location.href = "{{ route('land-parcel.view', $source_id) }}?revisionNumber=" + $(
                    this).val();
            });
        });


        $(document).on('click', '#amendmentSubmit', (e) => {
            let actionUrl = "{{ route('land-parcel.amendment', $data->id) }}";
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        Swal.fire({
                            title: 'Success!',
                            text: data.message,
                            icon: 'success'
                        });
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error'
                        });
                    }
                    location.reload();
                });
            });
        });
        // On page load, call loadCodes for each row to initialize the data
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
            <select class="form-select select2" disabled id="attribute_val_${index}" style="max-width:100% !important;" onchange="changeAttributeVal(this, ${elementIdForDropdown}, ${rowindex});">
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
           function closeModal(id)
        {
            $('#' + id).modal('hide');
        }
    </script>
@endsection
