@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <form id="equipmentForm" action="{{ route('equipment.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                      <input type ="hidden" name="book_code" id ="book_code_input">
                                <input type="hidden" name="doc_number_type" id="doc_number_type">
                                <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern">
                                <input type="hidden" name="doc_prefix" id="doc_prefix">
                                <input type="hidden" name="doc_suffix" id="doc_suffix">
                                <input type="hidden" name="doc_no" id="doc_no">
                                
                                <!-- Hidden inputs for checklist data -->
                                <div id="checklistDataInputs"></div>
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Equipment</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="index.html">Home</a>
                                            </li>
                                            <li class="breadcrumb-item active">Add New</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <button onClick="javascript: history.go(-1)"
                                    class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i>
                                    Back</button>
                                {{-- <button class="btn btn-outline-primary btn-sm mb-50 mb-sm-0"><i
                                        data-feather='save'></i> Save as
                                    Draft</button> --}}
                                <button data-bs-toggle="modal" data-bs-target="#amendmentconfirm"
                                    class="btn btn-primary btn-sm mb-50 mb-sm-0" style="display: none;"><i
                                        data-feather='edit'></i> Amendment</button>
                                <!-- <button class="btn btn-danger btn-sm mb-50 mb-sm-0" data-bs-target="#reject" data-bs-toggle="modal"><i data-feather="x-circle"></i> Reject</button>
                                            <button class="btn btn-success btn-sm mb-50 mb-sm-0" data-bs-target="#approved" data-bs-toggle="modal"><i data-feather="check-circle" ></i> Approve</button> -->
                                <button type="button" onclick="submitForm('draft');" id="draft"
                                    class="btn btn-outline-primary btn-sm mb-50 mb-sm-0"><i data-feather='save'></i> Save as
                                    Draft</button>
                                <button type="button" onclick="submitForm('submitted');"
                                    class="btn btn-primary btn-sm mb-50 mb-sm-0" id="submitted"><i
                                        data-feather="check-circle"></i>
                                    Submit</button>
                                <input id="submitButton" type="submit" value="Submit" class="hidden" />
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="status" id="status">
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

                <div class="content-body">
                    <section id="basic-datatable">
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
                                            <div class="col-md-8">
                                               
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Organization <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="organization_id"
                                                            name="organization_id">
                                                            <option value="">Select</option>

                                                            @foreach ($userOrganizations as $organization)
                                                                <option value="{{ $organization->organization->id }}" {{ $organization->organization->id == $organizationId ? 'selected' : '' }}>
                                                                    {{ $organization->organization->name }}
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
                                                        <select class="form-select" id="location_id" name="location_id">
                                                            {{-- Populated by JS --}}
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Asset Code</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="asset_code_id" name="asset_code_id">
                                                            <option value="">Select Asset Code</option>
                                                            @foreach($fixedAssetRegistration as $asset)
                                                                <option value="{{ $asset->id }}">{{ $asset->asset_code }} - {{ $asset->asset_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Category <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="category_id" name="category_id">
                                                            <option value="">Select</option>
                                                            @foreach($categories as $category)
                                                                <option value="{{ $category->id}}">{{ $category->name }}
                                                                </option>
                                                            @endforeach
                                                            {{-- Populated by JS --}}
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Name <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="name">
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Alias</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="alias">
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Description</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" name="description">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4" style="display: none;">
                                                <div
                                                    class="step-custhomapp bg-light p-1 customerapptimelines customerapptimelinesapprovalpo">
                                                    <h5
                                                        class="mb-2 text-dark border-bottom pb-50 d-flex align-items-center justify-content-between">
                                                        <strong><i data-feather="arrow-right-circle"></i> Approval
                                                            History</strong>
                                                        <strong
                                                            class="badge rounded-pill badge-light-secondary amendmentselect">Rev.
                                                            No.
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
                                                                <div
                                                                    class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                    <h6>Deepak Kumar</h6>
                                                                    <span
                                                                        class="badge rounded-pill badge-light-primary">Amendment</span>
                                                                </div>
                                                                <h5>(2 min ago)</h5>
                                                                <p>Description will come here</p>
                                                            </div>
                                                        </li>
                                                        <li class="timeline-item">
                                                            <span class="timeline-point timeline-point-indicator"></span>
                                                            <div class="timeline-event">
                                                                <div
                                                                    class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                    <h6>Aniket Singh</h6>
                                                                    <span
                                                                        class="badge rounded-pill badge-light-danger">Rejected</span>
                                                                </div>
                                                                <h5>(2 min ago)</h5>
                                                                <p>Description will come here</p>
                                                            </div>
                                                        </li>
                                                        <li class="timeline-item">
                                                            <span
                                                                class="timeline-point timeline-point-warning timeline-point-indicator"></span>
                                                            <div class="timeline-event">
                                                                <div
                                                                    class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                    <h6>Deewan Singh</h6>
                                                                    <span
                                                                        class="badge rounded-pill badge-light-warning">Pending</span>
                                                                </div>
                                                                <h5>(5 min ago)</h5>
                                                                <p>Description will come here</p>
                                                            </div>
                                                        </li>
                                                        <li class="timeline-item">
                                                            <span
                                                                class="timeline-point timeline-point-info timeline-point-indicator"></span>
                                                            <div class="timeline-event">
                                                                <div
                                                                    class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                    <h6>Brijesh Kumar</h6>
                                                                    <span
                                                                        class="badge rounded-pill badge-light-success">Approved</span>
                                                                </div>
                                                                <h5>(10 min ago)</h5>
                                                                <p>Description will come here</p>
                                                            </div>
                                                        </li>
                                                        <li class="timeline-item">
                                                            <span
                                                                class="timeline-point timeline-point-danger timeline-point-indicator"></span>
                                                            <div class="timeline-event">
                                                                <div
                                                                    class="d-flex justify-content-between flex-sm-row flex-column mb-sm-0 mb-1">
                                                                    <h6>Deepender Singh</h6>
                                                                    <span
                                                                        class="badge rounded-pill badge-light-success">Approved</span>
                                                                </div>
                                                                <h5>(5 day ago)</h5>
                                                                <p><a href="#"><i data-feather="download"></i></a>
                                                                    Description will come here </p>
                                                            </div>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body customernewsection-form">

                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader ">
                                                        <h4 class="card-title text-theme">Maintenance Detail
                                                        </h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-sm-end">
                                                    <a href="javascript:void(0);" id="deleteRowBtn"
                                                        class="btn btn-sm btn-outline-danger me-50">
                                                        <i data-feather="x-circle"></i> Delete</a>
                                                    <a href="javascript:void(0);" id="addRowBtn"
                                                        class="btn btn-sm btn-outline-primary">
                                                        <i data-feather="plus"></i> Add New Item</a>

                                                </div>
                                            </div>
                                        </div>

                                        

                                        <div class="tab-content pb-1">
                                            <div class="tab-pane active" id="Maintenance">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="table-responsive pomrnheadtffotsticky">
                                                            <table
                                                                class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                                <thead>
                                                                    <tr>
                                                                        <th width="62" class="customernewsection-form">
                                                                            <div
                                                                                class="form-check form-check-primary custom-checkbox">
                                                                                <input type="checkbox"
                                                                                    class="form-check-input" id="Email">
                                                                                <label class="form-check-label"
                                                                                    for="Email"></label>
                                                                            </div>
                                                                        </th>
                                                                        <th width="285">Maint Type</th>
                                                                        <th width="208">Frequency</th>
                                                                        <th width="208">Start Date</th>
                                                                        <th width="269">Time</th>
                                                                        <th width="208">Maintenance BOM</th>
                                                                        <th width="329">Checklist</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody class="mrntableselectexcel" id="maintenanceRows">

                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                           
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="col-md-4">
                                                    <div class="mb-1">
                                                        <label class="form-label">Upload Document</label>
                                                        <input type="file" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">Final Remarks</label>
                                                    <textarea type="text" rows="4" class="form-control"
                                                        placeholder="Enter Remarks here..."></textarea>
                                                </div>
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
    <!-- Modal for Attributes -->
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
                        <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail">
                            <thead>
                                <tr>
                                    <th>Attribute Name</th>
                                    <th>Attribute Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Color</td>
                                    <td>
                                        <select class="form-select select2 attribute-select" data-attribute="color">
                                            <option>Select</option>
                                            <option>Black</option>
                                            <option>White</option>
                                            <option>Red</option>
                                            <option>Golden</option>
                                            <option>Silver</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Size</td>
                                    <td>
                                        <select class="form-select select2 attribute-select" data-attribute="size">
                                            <option>Select</option>
                                            <option>5.11"</option>
                                            <option>5.10"</option>
                                            <option>5.09"</option>
                                            <option>5.00"</option>
                                            <option>6.20"</option>
                                        </select>
                                    </td>
                                </tr>





                            </tbody>


                        </table>
                    </div>
                </div>

                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Select</button>
                </div>
            </div>
        </div>
    </div>

    {{-- <div class="modal fade" id="attribute" tabindex="-1" aria-labelledby="attributeModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title fw-bolder text-dark" id="attributeModalTitle">Item Attributes</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-center">Enter the attribute details for this item.</p>
                    <input type="hidden" id="attribute-row-id" value="">

                    <div class="table-responsive customernewsection-form">
                        <table class="mt-1 table myrequesttablecbox table-striped">
                            <thead>
                                <tr>
                                    <th>Attribute Name</th>
                                    <th>Attribute Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Color</td>
                                    <td>
                                        <select class="form-select attribute-select" data-attribute="color">
                                            <option value="">Select</option>
                                            <option value="Black">Black</option>
                                            <option value="White">White</option>
                                            <option value="Red">Red</option>
                                            <option value="Blue">Blue</option>
                                            <option value="Green">Green</option>
                                            <option value="Golden">Golden</option>
                                            <option value="Silver">Silver</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Size</td>
                                    <td>
                                        <select class="form-select attribute-select" data-attribute="size">
                                            <option value="">Select</option>
                                            <option value="5.11 Inch">5.11 Inch</option>
                                            <option value="5.10 Inch">5.10 Inch</option>
                                            <option value="5.9 Inch">5.9 Inch</option>
                                            <option value="5.8 Inch">5.8 Inch</option>
                                            <option value="5.7 Inch">5.7 Inch</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Weight</td>
                                    <td>
                                        <select class="form-select attribute-select" data-attribute="weight">
                                            <option value="">Select</option>
                                            <option value="100 gm">100 gm</option>
                                            <option value="200 gm">200 gm</option>
                                            <option value="300 gm">300 gm</option>
                                            <option value="400 gm">400 gm</option>
                                            <option value="500 gm">500 gm</option>
                                        </select>
                                    </td>
                                </tr>

                                <tr>
                                    <td>Material</td>
                                    <td>
                                        <select class="form-select attribute-select" data-attribute="material">
                                            <option value="">Select</option>
                                            <option value="Metal">Metal</option>
                                            <option value="Plastic">Plastic</option>
                                            <option value="Wood">Wood</option>
                                            <option value="Glass">Glass</option>
                                            <option value="Ceramic">Ceramic</option>
                                        </select>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex mt-2 justify-content-end">
                        <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal"><i
                                data-feather="x-circle"></i> Cancel</button>
                        <button type="button" class="btn btn-primary" id="save-attributes"><i
                                data-feather="check-circle"></i> Save</button>
                    </div>
                </div>
            </div>
        </div>
    </div> --}}
    <!-- END: Modal for Attributes -->
    <!-- Modal for Checklist -->
    <div class="modal fade text-start" id="checklist" tabindex="-1" aria-labelledby="myModalLabel17" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Select
                            Checklist</h4>
                        <p class="mb-0">Select from the below list</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="checkListPortion">
                    <div class="row">

                        <div class="col-md-4">
                            <div class="mb-1">
                                <label class="form-label">Checklist <span class="text-danger">*</span></label>
                                <select class="form-select select2">
                                    <option>Select</option>
                                    @foreach($checklists as $checklist)
                                    <option value="{{ $checklist->id }}">{{ $checklist->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3  mb-1">
                            <label class="form-label">&nbsp;</label><br />
                            <button class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                        </div>

                        <div class="col-md-12">
                            <div class="table-responsive">
                                Select Checklist
                                <div class="text-end" style="margin-top: -30px"><a href="#" class="text-primary add-contactpeontxt mt-50" onclick="addPortion()"><i data-feather='plus'></i> Add Checklist</a></div>
                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                    <thead>
                                        <tr>
                                            <th width="40px" class="customernewsection-form">
                                                <div class="form-check form-check-primary custom-checkbox">
                                                    <input type="checkbox" class="form-check-input" id="Email">
                                                    <label class="form-check-label" for="Email"></label>
                                                </div>
                                            </th>
                                            <th>Name</th>
                                            <th>Description</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                       
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-end">
                    <button class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal"><i data-feather="x-circle"></i>
                        Cancel</button>
                    <button class="btn btn-primary btn-sm" data-bs-dismiss="modal"><i data-feather="check-circle"></i>
                        Submit</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Modal for Checklist -->
@endsection

@section('scripts')
    <style>
        .is-invalid {
            border-color: #ea5455 !important;
            padding-right: calc(1.45em + 0.876rem);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23ea5455'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23ea5455' stroke='none'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right calc(0.3625em + 0.219rem) center;
            background-size: calc(0.725em + 0.438rem) calc(0.725em + 0.438rem);
        }

        .hidden {
            display: none;
        }
    </style>
    <script>
        $(document).ready(function () {

            var allLocations = @json($locations);
            // var allCategories = @json($categories);
            var maintenanceTypes = @json($maintenanceTypes);
            var maintenanceBOM = @json($maintenanceBOM);
            let items = @json($items);


            $('#maintenanceRows').append(getMaintenanceRow());
            $('#spareRows').append(getSparePartRow());

            // On organization change, filter locations
            $('#organization_id').on('change', function () {
                var orgId = $(this).val();
                var locationSelect = $('#location_id');
                console.log(allLocations, orgId);
                if (orgId) {
                    console.log(allLocations, orgId);
                    allLocations.forEach(function (loc) {
                        if (loc.organization_id == orgId) {
                            locationSelect.append('<option value="' + loc.id + '">' + loc
                                .store_name +
                                '</option>');
                        }
                    });
                }
                 
              });
            $('#organization_id').trigger('change');




        


        function getMaintenanceRow() {
            const rowId = 'row-' + Math.random().toString(36).substring(2, 10);

            // Build options from maintenanceTypes
            let typeOptions = `<option value="">Select</option>`;
            maintenanceTypes.forEach(function (type) {
                typeOptions += `<option value="${type.id}">${type.name}</option>`;
            });

            // Build options from maintenanceTypes
            let bomOptions = `<option value="">Select</option>`;
            maintenanceBOM.forEach(function (type) {
                bomOptions += `<option value="${type.id}">${type.name}</option>`;
            });

            let row = `<tr data-row-id="${rowId}">
                                <td class="customernewsection-form">
                                    <div class="form-check form-check-primary custom-checkbox">
                                        <input type="checkbox" class="form-check-input row-checkbox">
                                        <label class="form-check-label"></label>
                                    </div>
                                </td>
                                <td class="poprod-decpt">
                                    <select name="maintenance[${rowId}][type]" required class="form-select mw-100 maintenance-type">
                                        ${typeOptions}
                                    </select>
                                </td>
                                <td class="poprod-decpt">
                                    <select name="maintenance[${rowId}][frequency]" required class="form-select mw-100">
                                        <option value="">Select</option>
                                        <option value="Daily">Daily</option>
                                        <option value="Weekly">Weekly</option>
                                        <option value="Monthly">Monthly</option>
                                        <option value="Quarterly">Quarterly</option>
                                        <option value="Semi-Annually">Semi-Annually</option>
                                        <option value="Annually">Annually</option>
                                    </select>
                                </td>
                                 <td class="poprod-decpt">
                                    <input type="date" name="maintenance[${rowId}][date]" required class="form-control mw-100 mb-25" />
                                </td>
                                <td class="poprod-decpt">
                                    <input type="time" name="maintenance[${rowId}][time]" required placeholder="Enter Time" class="form-control mw-100 mb-25" />
                                </td>
                                 <td class="poprod-decpt">
                                    <select name="maintenance[${rowId}][bom]" required  class="form-select mw-100 maintenance-bom">
                                        ${bomOptions}
                                    </select>
                                </td>
                                <td class="poprod-decpt checklist-cell">
                                    <span class="checklist-badges"></span>
                                    <button type="button" class="btn p-25 btn-sm btn-outline-secondary open-checklist-modal" style="font-size: 10px">Add Checklist</button>
                                    <input type="hidden" name="maintenance[${rowId}][checklists]" class="selected-checklists" value="" />
                                </td>
                            </tr>`;

            // $(function () {
            //     $(".ledgerselecct").autocomplete({
            //         source: maintenanceTypes.map(item => item.name),
            //         minLength: 0
            //     }).focus(function () {
            //         if (this.value == "") {
            //             $(this).autocomplete("search");
            //         }
            //     });
            // });
            //  $(function () {
            //     $(".ledgerselecct2").autocomplete({
            //         source: maintenanceBOM.map(item => item.name),
            //         minLength: 0
            //     }).focus(function () {
            //         if (this.value == "") {
            //             $(this).autocomplete("search");
            //         }
            //     });
            // });

            return row;
        }

        let checklistRowRef = null;

        $(document).on('click', '.open-checklist-modal', function () {
            checklistRowRef = $(this).closest('tr');

            // Get current selection from hidden input in the row
            let selected = checklistRowRef.find('.selected-checklists').val().split(',').filter(Boolean);

            console.log(selected)
            // Uncheck all first
            $('#checklist .modal-body input[type="checkbox"]').prop('checked', false);

            // Pre-check those which are already selected
            $('#checklist .modal-body input[type="checkbox"]').each(function () {
                if (selected.includes($(this).val())) {
                    $(this).prop('checked', true);
                }
            });

            $('#checklist').modal('show');

            $('.myrequesttablecbox thead input[type="checkbox"]').on('change', function () {
                console.log('erer');
                var tbody = $(this).closest('table').find('tbody');
                var checked = $(this).is(':checked');
                tbody.find('input.form-check-input').prop('checked', checked);
            });
        });

        $('#checklist').on('hide.bs.modal', function (e) {
            // Only proceed if we just clicked submit
            if ($(document.activeElement).hasClass('btn-primary')) {
                let selectedIds = [];
                let selectedNames = [];
                let selectedData = [];

                $('#checklist .modal-body input[type="checkbox"]:checked').each(function () {
                    const checklistId = $(this).val();
                    const checklistName = $(this).closest('tr').find('td:nth-child(2)').text().trim();
                    const checklistDesc = $(this).closest('tr').find('td:nth-child(3)').text().trim() || null;
                    const checklistType = $(this).closest('tr').find('td:nth-child(4)').text().trim() || null;

                    selectedIds.push(checklistId);
                    selectedNames.push(checklistName);
                    selectedData.push({
                        id: checklistId,
                        name: checklistName,
                        description: checklistDesc,
                        type: checklistType
                    });
                });

                // Store data in hidden field in row
                if (checklistRowRef) {
                    const rowId = checklistRowRef.data('row-id');

                    let badgesHtml = '';
                    selectedNames.slice(0, 2).forEach(function (name) {
                        badgesHtml +=
                            `<span class="badge rounded-pill badge-light-primary">${name}</span> `;
                    });

                    if (selectedNames.length > 2) {
                        badgesHtml +=
                            `<span class="badge rounded-pill badge-light-primary">+${selectedNames.length - 2}</span>`;
                    }

                    // Create hidden inputs for each checklist
                    let hiddenInputs = '';
                    selectedData.forEach(function (checklist, index) {
                        hiddenInputs += `
                                    <input type="hidden" name="maintenance[${rowId}][checklists][${index}][id]" value="${checklist.id}">
                                    <input type="hidden" name="maintenance[${rowId}][checklists][${index}][name]" value="${checklist.name}">
                                    <input type="hidden" name="maintenance[${rowId}][checklists][${index}][description]" value="${checklist.description || ''}">
                                    <input type="hidden" name="maintenance[${rowId}][checklists][${index}][type]" value="${checklist.type || ''}">
                                `;
                    });

                    console.log(selectedIds)
                    // Put badges and button back in the cell
                    checklistRowRef.find('.checklist-cell').html(
                        `<span class="checklist-badges">${badgesHtml}</span>
                                <button type="button" class="btn p-25 btn-sm btn-outline-secondary open-checklist-modal" style="font-size: 10px">Add Checklist</button>
                                <input type="hidden" class="selected-checklists" value="${selectedIds.join(',')}" />
                                ${hiddenInputs}`
                    );
                }
            }
        });

        // Track selected checklist IDs across all sections
        let selectedChecklistIds = [];

        // Handle dropdown change to track selected checklist IDs
        $(document).on('change', '#checkListPortion select', function() {
            const $dropdown = $(this);
            const selectedValue = $dropdown.val();
            const previousValue = $dropdown.data('previous-value') || '';
            
            // Remove previous value from selectedChecklistIds if it exists
            if (previousValue && previousValue !== 'Select') {
                const prevIndex = selectedChecklistIds.indexOf(previousValue);
                if (prevIndex > -1) {
                    selectedChecklistIds.splice(prevIndex, 1);
                }
            }
            
            // Add new value to selectedChecklistIds if it's not 'Select'
            if (selectedValue && selectedValue !== 'Select') {
                // Check if this checklist is already selected in another section
                if (selectedChecklistIds.includes(selectedValue)) {
                    alert('This checklist is already selected in another section. Please choose a different checklist.');
                    // Reset to previous value or 'Select'
                    $dropdown.val(previousValue || 'Select');
                    return;
                }
                selectedChecklistIds.push(selectedValue);
            }
            
            // Store current value as previous value for next change
            $dropdown.data('previous-value', selectedValue);
            
            // Update all dropdowns to exclude selected checklists
            updateAllDropdowns();
            
            console.log('Updated selectedChecklistIds:', selectedChecklistIds);
        });

        // Handle search button click for checklist details
        $(document).on('click', '.btn-warning', function(e) {
            e.preventDefault();
            
            // Find the dropdown in the same row
            const dropdown = $(this).closest('.row').find('select');
            const checklistId = dropdown.val();
            
            if (!checklistId || checklistId === 'Select') {
                alert('Please select a checklist first');
                return;
            }
            
            console.log('Selected Checklist ID:', checklistId);
            
            // AJAX call to get checklist details
            $.ajax({
                url: '{{ route("equipment.get-checklist-details") }}',
                method: 'POST',
                data: {
                    checklist_id: checklistId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        const tbody = $(this).closest('.row').find('tbody');
                        tbody.empty();

                        const checklists = response.data.checklist || [];
                        const details = response.data.details || [];

                        if (checklists.length > 0) {
                            checklists.forEach(checklist => {
                                // Match detail for this checklist
                                const detail = details.find(d => d.checklist_id === checklist.id);

                                const tableRow = `
                                    <tr>
                                        <td class="customernewsection-form">
                                            <div class="form-check form-check-primary custom-checkbox">
                                                <input type="checkbox" class="form-check-input" value="${checklist.id}">
                                                <label class="form-check-label"></label>
                                            </div>
                                        </td>
                                        <td>${checklist.name || ''}</td>
                                        <td>${checklist.description || ''}</td>
                                        <td>
                                            <span class="badge rounded-pill badge-light-info">
                                                ${checklist.data_type || ''}
                                            </span>
                                        </td>
                                    </tr>
                                `;
                                tbody.append(tableRow);
                            });

                            updateAllDropdowns();
                        } else {
                            tbody.html('<tr><td colspan="5" class="text-center text-muted">No checklist data found</td></tr>');
                        }
                    } else {
                        const tbody = $(this).closest('.row').find('tbody');
                        tbody.html(`<tr><td colspan="5" class="text-center text-danger">${response.message}</td></tr>`);
                    }
                }.bind(this),
                error: function(xhr, status, error) {
                    console.log('AJAX Error:', error);
                    console.log('Status:', status);
                    console.log('Response:', xhr.responseText);
                }
            });
        });

        // Update all dropdowns to exclude selected checklists
        function updateAllDropdowns() {
            // Get all currently selected checklist IDs from all portions
            let allSelectedIds = [];
            
            // Collect selected values from all dropdowns
            $('#checkListPortion select').each(function() {
                const val = $(this).val();
                if (val && val !== 'Select') {
                    allSelectedIds.push(val);
                }
            });
            
            console.log('Currently selected in all portions:', allSelectedIds);
            
            // Update each dropdown
            $('#checkListPortion select').each(function() {
                const currentDropdown = $(this);
                const currentValue = currentDropdown.val();
                
                // Show all options first
                currentDropdown.find('option').show();
                
                // Hide selected options (except current selection)
                allSelectedIds.forEach(function(selectedId) {
                    if (selectedId !== currentValue) {
                        currentDropdown.find(`option[value="${selectedId}"]`).hide();
                    }
                });
            });
            
            // Update the global selectedChecklistIds array to stay in sync
            selectedChecklistIds = [...allSelectedIds];
        }

        // Add new checklist portion function
        function addPortion(){
            const portionId = 'portion_' + Date.now(); // Unique ID for each portion
            const newPortionHtml = `
                <div class="row checklist-portion" id="${portionId}">
                    <div class="col-md-4">
                        <div class="mb-1">
                            <label class="form-label">Checklist <span class="text-danger">*</span></label>
                            <select class="form-select select2">
                                <option>Select</option>
                                @foreach($checklists as $checklist)
                                <option value="{{ $checklist->id }}">{{ $checklist->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-3 mb-1">
                        <label class="form-label">&nbsp;</label><br />
                        <button class="btn btn-warning btn-sm"><i data-feather="search"></i> Search</button>
                    </div>

                    <div class="col-md-12">
                        <div class="table-responsive">
                            Select Checklist
                            <div class="text-end" style="margin-top: -30px">
                                <a href="#" class="text-primary add-contactpeontxt mt-50 me-2" onclick="addPortion()"><i data-feather='plus'></i> Add Checklist</a>
                                <a href="#" class="text-danger remove-contactpeontxt mt-50" onclick="removePortion('${portionId}')"><i data-feather='minus'></i> Remove Checklist</a>
                            </div>
                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail">
                                <thead>
                                    <tr>
                                        <th width="40px" class="customernewsection-form">
                                            <div class="form-check form-check-primary custom-checkbox">
                                                <input type="checkbox" class="form-check-input">
                                                <label class="form-check-label"></label>
                                            </div>
                                        </th>
                                        <th>Name</th>
                                        <th>Description</th>
                                        <th>Type</th>
                                    </tr>
                                </thead>
                                <tbody>
                                   
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
            
            // Add the new portion to checkListPortion container
            $('#checkListPortion').append(newPortionHtml);
            
            // Re-initialize feather icons for new elements
            feather.replace();
            
            // Update all dropdowns to hide already selected checklists
            updateAllDropdowns();
        }

        // Remove checklist portion function
        function removePortion(portionId) {
            // Get the selected checklist ID from this portion before removing
            const portion = $('#' + portionId);
            const selectedValue = portion.find('select').val();
            
            // Remove the selected checklist ID from selectedChecklistIds array
            if (selectedValue && selectedValue !== 'Select') {
                const index = selectedChecklistIds.indexOf(selectedValue);
                if (index > -1) {
                    selectedChecklistIds.splice(index, 1);
                }
            }
            
            // Remove the portion from DOM
            portion.remove();
            
            // Update all remaining dropdowns to show the previously selected option
            updateAllDropdowns();
        }

        // Collect checklist data and associate with maintenance rows
        function collectChecklistData() {
            let checklistData = [];
            let detailIdCounter = 1;
            
            // Loop through all checklist portions
            $('#checkListPortion .row').each(function(index) {
                const portion = $(this);
                const checklistId = portion.find('select').val();
                const checklistName = portion.find('select option:selected').text();
                
                // Only include if a checklist is selected
                if (checklistId && checklistId !== 'Select') {
                    // Add all items from this checklist
                    portion.find('tbody tr').each(function(itemIndex) {
                        const row = $(this);
                        checklistData.push({
                            checklist_id: checklistId,
                            checklist_detail_id: detailIdCounter,
                            name: row.find('td:nth-child(2)').text().trim(),
                            description: row.find('td:nth-child(3)').text().trim(),
                            type: row.find('td:nth-child(4)').text().trim()
                        });
                        detailIdCounter++;
                    });
                }
            });
            
            return checklistData;
        }

        // Update maintenance rows with checklist data
        function updateChecklistInputs() {
            const checklistData = collectChecklistData();
            console.log('Collected Checklist Data:', checklistData);
            
            // Find the first maintenance row (assuming checklists belong to first maintenance)
            const firstMaintenanceRow = $('#maintenanceRows tr').first();
            console.log('First Maintenance Row Found:', firstMaintenanceRow.length > 0);
            
            if (firstMaintenanceRow.length > 0) {
                // Try different ways to find row ID
                let rowId = firstMaintenanceRow.find('input').first().attr('name');
                if (!rowId) {
                    rowId = firstMaintenanceRow.find('select').first().attr('name');
                }
                if (!rowId) {
                    rowId = firstMaintenanceRow.find('input, select').first().attr('name');
                }
                
                console.log('Row ID:', rowId);
                console.log('All inputs in row:', firstMaintenanceRow.find('input, select').length);
                
                // If still no rowId, let's try to find it from any element with name attribute
                if (!rowId) {
                    firstMaintenanceRow.find('[name]').each(function() {
                        const elementName = $(this).attr('name');
                        console.log('Found element with name:', elementName);
                        if (elementName && elementName.includes('maintenance[')) {
                            rowId = elementName;
                            return false; // Break the loop
                        }
                    });
                }
                
                if (rowId) {
                    const rowKeyMatch = rowId.match(/maintenance\[(.*?)\]/);
                    if (rowKeyMatch) {
                        const rowKey = rowKeyMatch[1];
                        console.log('Row Key:', rowKey);
                        
                        // Clear existing checklist inputs for this maintenance row
                        $(`input[name^="maintenance[${rowKey}][checklists]"]`).remove();
                        
                        // Add checklist data to this maintenance row
                        checklistData.forEach(function(item, index) {
                            const checklistIdInput = `<input type="hidden" name="maintenance[${rowKey}][checklists][${index}][checklist_id]" value="${item.checklist_id}">`;
                            const checklistDetailIdInput = `<input type="hidden" name="maintenance[${rowKey}][checklists][${index}][checklist_detail_id]" value="${item.checklist_detail_id}">`;
                            const nameInput = `<input type="hidden" name="maintenance[${rowKey}][checklists][${index}][name]" value="${item.name}">`;
                            const descriptionInput = `<input type="hidden" name="maintenance[${rowKey}][checklists][${index}][description]" value="${item.description}">`;
                            const typeInput = `<input type="hidden" name="maintenance[${rowKey}][checklists][${index}][type]" value="${item.type}">`;
                            
                            firstMaintenanceRow.append(checklistIdInput);
                            firstMaintenanceRow.append(checklistDetailIdInput);
                            firstMaintenanceRow.append(nameInput);
                            firstMaintenanceRow.append(descriptionInput);
                            firstMaintenanceRow.append(typeInput);
                            
                            console.log(`Added checklist item ${index}:`, item);
                        });
                        
                        console.log('Total checklist items added:', checklistData.length);
                    } else {
                        console.log('No valid maintenance row key found in:', rowId);
                    }
                } else {
                    console.log('No row ID found in maintenance row');
                }
            } else {
                console.log('No maintenance rows found');
            }
        }

        // Make functions globally accessible
        window.addPortion = addPortion;
        window.removePortion = removePortion;
        window.collectChecklistData = collectChecklistData;
        window.updateChecklistInputs = updateChecklistInputs;

        // Template row for Spare Part
        // function getSparePartRow() {
        //     return `<tr>
        //                 <td class="customernewsection-form">
        //                     <div class="form-check form-check-primary custom-checkbox">
        //                         <input type="checkbox" class="form-check-input row-checkbox">
        //                         <label class="form-check-label"></label>
        //                     </div>
        //                 </td>
        //                 <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25" /></td>
        //                 <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25" /></td>
        //                 <td class="poprod-decpt">
        //                     <button data-bs-toggle="modal" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
        //                 </td>
        //                 <td><select class="form-select"><option>Select</option><option selected>KG</option></select></td>
        //                 <td><input type="text" value="10" class="form-control mw-100" /></td>
        //             </tr>`;
        // }

        function getSparePartRow() {
            let itemOptions = `<option value="">Select</option>`;
            items.forEach(function (item) {
                itemOptions += `<option value="${item.id}" data-name="${item.item_name}" data-code="${item.item_code}">${item.item_code}</option>`;
            });

            const rowId = 'spare-' + Math.random().toString(36).substring(2, 10);
            return `<tr data-row-id="${rowId}">
                        <td class="customernewsection-form">
                            <div class="form-check form-check-primary custom-checkbox">
                                <input type="checkbox" class="form-check-input row-checkbox">
                                <label class="form-check-label"></label>
                            </div>
                        </td>
                        <td class="poprod-decpt">
                            <select class="form-select mw-100 item-code-dropdown" name="spareparts[${rowId}][item_code]">
                                ${itemOptions}
                            </select>
                        </td>
                        <td class="poprod-decpt">
                            <input type="text" class="form-control mw-100 item-name-input" name="spareparts[${rowId}][item_name]" />
                        </td>
                        <td class="poprod-decpt">
                            <button type="button" data-row-id="${rowId}" class="btn p-25 btn-sm btn-outline-secondary open-attribute-modal" style="font-size: 10px">Attributes</button>
                            <input type="hidden" name="spareparts[${rowId}][attributes]" class="attributes-input" value="{}" />
                        </td>
                        <td>
                            <select class="form-select" name="spareparts[${rowId}][uom]">
                                <option value="">Select</option>
                                <option value="KG">KG</option>
                                <option value="PCS">PCS</option>
                                <option value="BOX">BOX</option>
                                <option value="UNIT">UNIT</option>
                            </select>
                        </td>
                        <td>
                            <input type="number" name="spareparts[${rowId}][qty]" value="1" min="0" step="0.01" class="form-control mw-100" />
                        </td>
                    </tr>`;
        }

        let attributeRowRef = null;
        $(document).on('click', '.open-attribute-modal', function () {
            attributeRowRef = $(this).closest('tr');

            const rowId = attributeRowRef.data('row-id');

            $('.attribute-select').val('');

            // Load existing attributes if any
            const input = $(`input[name="spareparts[${rowId}][attributes]"]`);
            if (input.length && input.val()) {
                try {
                    const attributes = JSON.parse(input.val());
                    Object.entries(attributes).forEach(([key, value]) => {
                        $(`.attribute-select[data-attribute="${key}"]`).val(value);
                    });
                } catch (e) {
                    console.error('Invalid JSON in attributes input:', e);
                }
            }


            $('#attribute').modal('show');

        });

        $('#attribute').on('hide.bs.modal', function (e) {
            if ($(document.activeElement).hasClass('btn-primary')) {
                if (!attributeRowRef) return;

                const attributes = {};

                // Collect all selected attributes
                $('.attribute-select').each(function () {
                    const attrName = $(this).data('attribute');
                    const attrValue = $(this).val();

                    if (attrValue) {
                        attributes[attrName] = attrValue;
                    }
                });

                const rowId = attributeRowRef.data('row-id');

                // Store as JSON in the hidden input
                const input = $(`input[name="spareparts[${rowId}][attributes]"]`);
                input.val(JSON.stringify(attributes));

                // Display selected attributes in the same row (column 4)
                let badgeHtml = '';
                Object.entries(attributes).forEach(([key, value]) => {
                    badgeHtml += `<span class="badge rounded-pill badge-light-primary">${key}: ${value}</span> `;
                });

                const cellHtml = `
                            ${badgeHtml}
                            <button type="button" data-row-id="${rowId}" class="btn p-25 btn-sm btn-outline-primary open-attribute-modal" style="font-size: 10px">Attributes </button>
                            <input type="hidden" name="spareparts[${rowId}][attributes]" class="attributes-input" value='${JSON.stringify(attributes)}' />
                        `;

                $(`tr[data-row-id="${rowId}"]`).find('td').eq(3).html(cellHtml);

            }
        });

        $(document).on('change', '.item-code-dropdown', function () {
            let selectedOption = $(this).find('option:selected');
            let itemName = selectedOption.data('name') || '';
            $(this).closest('tr').find('.item-name-input').val(itemName);

            let selectedItem = items.find(item => item.id === parseInt(selectedOption.val()));
            console.log(selectedItem, selectedOption.val())
            if (selectedItem) {
                $('#part-detail-name').html(selectedItem.item_name);
                $('#part-detail-hsn').html(selectedItem.hsn_id);
                $('#part-detail-color').html(selectedItem.color ?? 'N/A');
                $('#part-detail-size').html(selectedItem.size ?? 'N/A');
                $('#part-detail-uom').html(selectedItem.uom_id ?? 'N/A');
                $('#part-detail-qty').html(selectedItem.qty ?? 'N/A');
                $('#part-detail-remarks').html(selectedItem.item_remark ?? 'N/A');
            }
        });

        $(document).on('click', '.open-attribute-modal', function () {
            const rowId = $(this).data('row-id');
            $('#attribute-row-id').val(rowId);

            // Reset all attribute selects
            $('.attribute-select').val('');

            // Load existing attributes if any
            const attributesInput = $(`input[name="spareparts[${rowId}][attributes]"]`);
            if (attributesInput.length && attributesInput.val()) {
                try {
                    const attributes = JSON.parse(attributesInput.val());

                    // Set values in the modal
                    for (const [key, value] of Object.entries(attributes)) {
                        $(`.attribute-select[data-attribute="${key}"]`).val(value);
                    }
                } catch (e) {
                    console.error('Error parsing attributes:', e);
                }
            }

            $('#attribute').modal('show');
        });

        $('#save-attributes').on('click', function () {
            const rowId = $('#attribute-row-id').val();
            if (!rowId) return;

            const attributes = {};

            // Collect all selected attributes
            $('.attribute-select').each(function () {
                const attrName = $(this).data('attribute');
                const attrValue = $(this).val();

                if (attrValue) {
                    attributes[attrName] = attrValue;
                }
            });

            // Store as JSON in the hidden input
            $(`input[name="spareparts[${rowId}][attributes]"]`).val(JSON.stringify(attributes));

            // Show a visual indicator that attributes are set
            const attributeCount = Object.keys(attributes).length;
            const attributeBtn = $(`.open-attribute-modal[data-row-id="${rowId}"]`);

            if (attributeCount > 0) {
                attributeBtn.removeClass('btn-outline-secondary').addClass('btn-outline-primary');
                attributeBtn.html(`Attributes (${attributeCount})`);
            } else {
                attributeBtn.removeClass('btn-outline-primary').addClass('btn-outline-secondary');
                attributeBtn.html('Attributes');
            }

            // Close the modal
            $('#attribute').modal('hide');
        });


        // Add row based on active tab
        $('#addRowBtn').on('click', function (e) {
            e.preventDefault();
            var activeTab = $('.tab-pane.active').attr('id');
            if (activeTab === 'Maintenance') {
                $('#maintenanceRows').append(getMaintenanceRow());
            } else if (activeTab === 'Spare') {
                $('#spareRows').append(getSparePartRow());
            }
        });

        // Delete selected rows from active tab
        $('#deleteRowBtn').on('click', function (e) {
            e.preventDefault();

            let activeTab = $('.tab-pane.active').attr('id');
            let checkboxes, table;

            if (activeTab === 'Maintenance') {
                checkboxes = $('#maintenanceRows').find('input.row-checkbox:checked');
                table = $('#maintenanceRows').closest('table');
            } else if (activeTab === 'Spare') {
                checkboxes = $('#spareRows').find('input.row-checkbox:checked');
                table = $('#spareRows').closest('table');
            }

            if (checkboxes.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No rows selected',
                    text: 'Please select at least one row to delete.',
                });
                return;
            }

            checkboxes.closest('tr').remove();
            table.find('thead input[type="checkbox"]').prop('checked', false);
        });

        // (Optional) "Select All" checkbox per table
        $('.myrequesttablecbox thead input[type="checkbox"]').on('change', function () {
            var tbody = $(this).closest('table').find('tbody');
            var checked = $(this).is(':checked');
            tbody.find('input.row-checkbox').prop('checked', checked);
        });
    });

        function submitForm(status) {
            $('#status').val(status);

            let isValid = true;
            let errorMessage = '';

            // Basic Information validation
            if ($('#organization_id').val() === '') {
                isValid = false;
                errorMessage += 'Organization is required.<br>';
            }

            if ($('#location_id').val() === '' && isValid) {
                isValid = false;
                errorMessage += 'Location is required.<br>';
            }

            if ($('#category_id').val() === '' && isValid) {
                isValid = false;
                errorMessage += 'Category is required.<br>';
            }

            if ($('input[name="name"]').val() === '' && isValid) {
                isValid = false;
                errorMessage += 'Name is required.<br>';
            }

            // Validate maintenance rows if any exist
            $('#maintenanceRows tr').each(function () {
                const typeSelect = $(this).find('select[name^="maintenance"][name$="[type]"]');
                const frequencyInput = $(this).find('input[name^="maintenance"][name$="[frequency]"]');

                if (typeSelect.val() !== '' || frequencyInput.val() !== '' && isValid) {
                    if (typeSelect.val() === '') {
                        isValid = false;
                        errorMessage += 'Maintenance type is required for all maintenance rows.<br>';
                    }

                    if (frequencyInput.val() === '') {
                        isValid = false;
                        errorMessage += 'Frequency is required for all maintenance rows.<br>';
                    }
                }
            });

            // Validate spare parts rows if any exist
            $('#spareRows tr').each(function () {
                const itemCodeSelect = $(this).find('select[name^="spareparts"][name$="[item_code]"]');
                const itemNameInput = $(this).find('input[name^="spareparts"][name$="[item_name]"]');
                const uomInput = $(this).find('input[name^="spareparts"][name$="[uom]"]');
                const qtyInput = $(this).find('input[name^="spareparts"][name$="[qty]"]');

                if (itemCodeSelect.val() !== '' || itemNameInput.val() !== '' && isValid) {
                    if (itemCodeSelect.val() === '') {
                        isValid = false;
                        errorMessage += 'Item code is required for all spare part rows.<br>';
                    }

                    if (itemNameInput.val() === '') {
                        isValid = false;
                        errorMessage += 'Item name is required for all spare part rows.<br>';
                    }

                    if (uomInput.val() === '') {
                        isValid = false;
                        errorMessage += 'UOM is required for all spare part rows.<br>';
                    }

                    if (qtyInput.val() === '' || parseFloat(qtyInput.val()) < 0) {
                        isValid = false;
                        errorMessage += 'Valid quantity is required for all spare part rows.<br>';
                    }
                }
            });

            if (!isValid) {
                Swal.fire({
                    title: 'Validation Error',
                    html: errorMessage,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return false;
            }

            // Collect and update checklist data before submission
            updateChecklistInputs();

            // If draft, confirm with user
            if (status === 'draft') {
                Swal.fire({
                    title: 'Save as Draft',
                    text: 'Are you sure you want to save this equipment as draft?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, save it!',
                    cancelButtonText: 'No, cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#submitButton').click();
                    }
                });
            } else {
                // If submitting, confirm with user
                Swal.fire({
                    title: 'Submit Equipment',
                    text: 'Are you sure you want to submit this equipment?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, submit it!',
                    cancelButtonText: 'No, cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#submitButton').click();
                    }
                });
            }
        }

        function check_amount() {

            $('#draft').attr('disabled', true);
            $('#submitted').attr('disabled', true);
            $('.preloader').show();
        }

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
            showToast('error', "@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach");
        @endif
           
    </script>
@endsection