@extends('layouts.app')

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
                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="arrow-left-circle"></i> Back</button>
                            <button class="btn btn-outline-primary btn-sm mb-50 mb-sm-0"><i data-feather='save'></i> Save as
                                Draft</button>
                            <button data-bs-toggle="modal" data-bs-target="#amendmentconfirm"
                                class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather='edit'></i> Amendment</button>
                            <!-- <button class="btn btn-danger btn-sm mb-50 mb-sm-0" data-bs-target="#reject" data-bs-toggle="modal"><i data-feather="x-circle"></i> Reject</button>
                                    <button class="btn btn-success btn-sm mb-50 mb-sm-0" data-bs-target="#approved" data-bs-toggle="modal"><i data-feather="check-circle" ></i> Approve</button> -->
                            <button onClick="javascript: history.go(-1)" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i
                                    data-feather="check-circle"></i> Submit</button>
                        </div>
                    </div>
                </div>
            </div>
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
                                                    <select class="form-select" id="organization_id" name="organization_id">
                                                        <option value="">Select</option>

                                                        @foreach ($userOrganizations as $organization)
                                                            <option value="{{ $organization->id }}">
                                                                {{ $organization->name }}</option>
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
                                                        <option value="">Select</option>
                                                        {{-- Populated by JS --}}
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

                                        <div class="col-md-4">
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
                                                    <h4 class="card-title text-theme">Maintenance and Spare Part Detail
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

                                    <div class="step-custhomapp bg-light">
                                        <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-bs-toggle="tab"
                                                    href="#Maintenance">Maintenance</a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-bs-toggle="tab" href="#Spare">Spare Part</a>
                                            </li>
                                        </ul>
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
                                                                    <th width="285">Type</th>
                                                                    <th width="208">Frequency</th>
                                                                    <th width="269">Time</th>
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
                                        <!-- Spare Part Tab -->
                                        <div class="tab-pane" id="Spare">
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
                                                                    <th width="285">Item Code</th>
                                                                    <th width="208">Item Name</th>
                                                                    <th width="269">Attributes</th>
                                                                    <th width="329">UOM</th>
                                                                    <th>Qty</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="mrntableselectexcel" id="spareRows">
                                                            </tbody>

                                                            <tfoot>
                                                                <tr class="totalsubheadpodetail">
                                                                    <td colspan="6"></td>
                                                                </tr>
                                                                <tr valign="top">
                                                                    <td colspan="6">
                                                                        <table class="table border">
                                                                            <tr>
                                                                                <td class="p-0">
                                                                                    <h6
                                                                                        class="text-dark mb-0 bg-light-primary py-1 px-50">
                                                                                        <strong>Part Details</strong>
                                                                                    </h6>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="poprod-decpt">
                                                                                    <span
                                                                                        class="poitemtxt mw-100"><strong>Name</strong>:
                                                                                        Furniture for Reception in ground
                                                                                        flour...</span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="poprod-decpt">
                                                                                    <span
                                                                                        class="badge rounded-pill badge-light-primary"><strong>HSN</strong>:
                                                                                        8755</span>
                                                                                    <span
                                                                                        class="badge rounded-pill badge-light-primary"><strong>Color</strong>:
                                                                                        Black</span>
                                                                                    <span
                                                                                        class="badge rounded-pill badge-light-primary"><strong>Size</strong>:
                                                                                        5.11 Inch</span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="poprod-decpt">
                                                                                    <span
                                                                                        class="badge rounded-pill badge-light-primary"><strong>Inv.
                                                                                            UOM</strong>: KG</span>
                                                                                    <span
                                                                                        class="badge rounded-pill badge-light-primary"><strong>Qty.</strong>:
                                                                                        100</span>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <td class="poprod-decpt">
                                                                                    <span
                                                                                        class="badge rounded-pill badge-light-secondary">
                                                                                        <strong>Remarks</strong>:
                                                                                        Description will come here for
                                                                                        items...
                                                                                    </span>
                                                                                </td>
                                                                            </tr>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!-- End Spare Part Tab -->
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
                                                <textarea type="text" rows="4" class="form-control" placeholder="Enter Remarks here..."></textarea>
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
                                        <select class="form-select select2">
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
                                        <select class="form-select select2">
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
                    <button type="reset" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="reset" class="btn btn-primary">Select</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Modal for Attributes -->
    <!-- Modal for Checklist -->
    <div class="modal fade text-start" id="checklist" tabindex="-1" aria-labelledby="myModalLabel17"
        aria-hidden="true">
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
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-4">
                            <div class="mb-1">
                                <label class="form-label">Checklist <span class="text-danger">*</span></label>
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
                                        <tr class="trail-bal-tabl-none">
                                            <td class="customernewsection-form">
                                                <div class="form-check form-check-primary custom-checkbox">
                                                    <input type="checkbox" class="form-check-input" id="Email">
                                                    <label class="form-check-label" for="Email"></label>
                                                </div>
                                            </td>
                                            <td>Checklist 1</td>
                                            <td>Desription will come here</td>
                                            <td><span class="badge rounded-pill badge-light-secondary">Text</span></td>
                                        </tr>
                                        <tr class="trail-bal-tabl-none">
                                            <td class="customernewsection-form">
                                                <div class="form-check form-check-primary custom-checkbox">
                                                    <input type="checkbox" class="form-check-input" id="Email">
                                                    <label class="form-check-label" for="Email"></label>
                                                </div>
                                            </td>
                                            <td>Checklist 2</td>
                                            <td>Desription will come here</td>
                                            <td><span class="badge rounded-pill badge-light-secondary">Text</span></td>
                                        </tr>
                                        <tr class="trail-bal-tabl-none">
                                            <td class="customernewsection-form">
                                                <div class="form-check form-check-primary custom-checkbox">
                                                    <input type="checkbox" class="form-check-input" id="Email">
                                                    <label class="form-check-label" for="Email"></label>
                                                </div>
                                            </td>
                                            <td>Checklist 3</td>
                                            <td>Desription will come here</td>
                                            <td><span class="badge rounded-pill badge-light-secondary">Yes/No</span></td>
                                        </tr>
                                        <tr class="trail-bal-tabl-none">
                                            <td class="customernewsection-form">
                                                <div class="form-check form-check-primary custom-checkbox">
                                                    <input type="checkbox" class="form-check-input" id="Email">
                                                    <label class="form-check-label" for="Email"></label>
                                                </div>
                                            </td>
                                            <td>Checklist 4</td>
                                            <td>Desription will come here</td>
                                            <td><span class="badge rounded-pill badge-light-secondary">Text</span></td>
                                        </tr>
                                        <tr class="trail-bal-tabl-none">
                                            <td class="customernewsection-form">
                                                <div class="form-check form-check-primary custom-checkbox">
                                                    <input type="checkbox" class="form-check-input" id="Email">
                                                    <label class="form-check-label" for="Email"></label>
                                                </div>
                                            </td>
                                            <td>Checklist 5</td>
                                            <td>Desription will come here</td>
                                            <td><span class="badge rounded-pill badge-light-secondary">Yes/No</span></td>
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
                        Submit</button>
                </div>
            </div>
        </div>
    </div>
    <!-- END: Modal for Checklist -->
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {

            var allLocations = @json($locations);
            var allCategories = @json($categories);
            var maintenanceTypes = @json($maintenanceTypes);

            // On organization change, filter locations
            $('#organization_id').on('change', function() {
                var orgId = $(this).val();
                var locationSelect = $('#location_id');
                locationSelect.html('<option value="">Select</option>');
                $('#category_id').html('<option value="">Select</option>');
                console.log(allLocations, orgId);
                if (orgId) {
                    console.log(allLocations, orgId);
                    allLocations.forEach(function(loc) {
                        if (loc.organization_id == orgId) {
                            locationSelect.append('<option value="' + loc.id + '">' + loc
                                .store_name +
                                '</option>');
                        }
                    });
                }
            });

            // On location change, filter categories
            $('#location_id').on('change', function() {
                var locationId = $(this).val();
                var categorySelect = $('#category_id');
                categorySelect.html('<option value="">Select</option>');
                console.log(allCategories, locationId);
                if (locationId) {
                    allLocations.forEach(function(cat) {
                        if (cat.id == locationId) {
                            categorySelect.append('<option value="' + cat.id + '">' + cat
                                .store_name +
                                '</option>');
                        }
                    });
                }
            });
        });


        $(document).ready(function() {
            var maintenanceTypes = @json($maintenanceTypes);

            function getMaintenanceRow() {
                const rowId = 'row-' + Math.random().toString(36).substring(2, 10);

                // Build options from maintenanceTypes
                let typeOptions = `<option value="">Select</option>`;
                maintenanceTypes.forEach(function(type) {
                    typeOptions += `<option value="${type.id}">${type.name}</option>`;
                });

                let row = `<tr data-row-id="${rowId}">
                            <td class="customernewsection-form">
                                <div class="form-check form-check-primary custom-checkbox">
                                    <input type="checkbox" class="form-check-input row-checkbox">
                                    <label class="form-check-label"></label>
                                </div>
                            </td>
                            <td class="poprod-decpt">
                                <input type="text" name="maintenance_types[]" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25"  />
                            </td>
                            <td class="poprod-decpt">
                                <select class="form-select mw-100">
                                    <option>Select</option>
                                    <option>Daily</option>
                                    <option>Weekly</option>
                                    <option>Monthly</option>
                                    <option>Quarterly</option>
                                    <option>Semi-Annually</option>
                                    <option>Annually</option>
                                </select>
                            </td>
                            <td class="poprod-decpt">
                                <input type="time" placeholder="Enter Time" class="form-control mw-100 mb-25" />
                            </td>
                            <td class="poprod-decpt checklist-cell">
                                <span class="checklist-badges"></span>
                                <button type="button" class="btn p-25 btn-sm btn-outline-secondary open-checklist-modal" style="font-size: 10px">Add Checklist</button>
                                <input type="hidden" class="selected-checklists" value="" />
                            </td>
                        </tr>`;

                $(function() {
                    $(".ledgerselecct").autocomplete({
                        source: maintenanceTypes.map(item => item.name),
                        minLength: 0
                    }).focus(function() {
                        if (this.value == "") {
                            $(this).autocomplete("search");
                        }
                    });
                });

                return row;
            }

            let checklistRowRef = null; // Will point to the clicked row

            // Open modal, store the row reference, and pre-select existing selections if any
            $(document).on('click', '.open-checklist-modal', function() {
                checklistRowRef = $(this).closest('tr');

                // Get current selection from hidden input in the row
                let selected = checklistRowRef.find('.selected-checklists').val().split(',').filter(
                    Boolean);

                // Uncheck all first
                $('#checklist .modal-body input[type="checkbox"]').prop('checked', false);

                // Pre-check those which are already selected
                $('#checklist .modal-body input[type="checkbox"]').each(function() {
                    if (selected.includes($(this).val())) {
                        $(this).prop('checked', true);
                    }
                });

                $('#checklist').modal('show');
            });

            // When user submits modal (submit = .btn-primary with data-bs-dismiss in modal footer)
            $('#checklist').on('hide.bs.modal', function(e) {
                // Only proceed if we just clicked submit
                if ($(document.activeElement).hasClass('btn-primary')) {
                    let selectedIds = [];
                    let selectedNames = [];
                    $('#checklist .modal-body input[type="checkbox"]:checked').each(function() {
                        selectedIds.push($(this).val());
                        // The checklist name is in the next <td>
                        selectedNames.push($(this).closest('tr').find('td:nth-child(2)').text()
                            .trim());
                    });

                    // Store IDs in hidden field in row
                    if (checklistRowRef) {
                        checklistRowRef.find('.selected-checklists').val(selectedIds.join(','));

                        // Show badges (max 2, then +N)
                        let badgesHtml = '';
                        selectedNames.slice(0, 2).forEach(function(name) {
                            badgesHtml +=
                                `<span class="badge rounded-pill badge-light-primary">${name}</span> `;
                        });

                        if (selectedNames.length > 2) {
                            badgesHtml +=
                                `<span class="badge rounded-pill badge-light-primary">+${selectedNames.length-2}</span>`;
                        }

                        // Put badges and button back in the cell
                        checklistRowRef.find('.checklist-cell').html(
                            `<span class="checklist-badges">${badgesHtml}</span>
                            <button type="button" class="btn p-25 btn-sm btn-outline-secondary open-checklist-modal" style="font-size: 10px">Add Checklist</button>
                            <input type="hidden" class="selected-checklists" value="${selectedIds.join(',')}" />`
                        );
                    }
                }
            });


            // Template row for Spare Part
            function getSparePartRow() {
                return `<tr>
                            <td class="customernewsection-form">
                                <div class="form-check form-check-primary custom-checkbox">
                                    <input type="checkbox" class="form-check-input row-checkbox">
                                    <label class="form-check-label"></label>
                                </div>
                            </td>
                            <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25" /></td>
                            <td class="poprod-decpt"><input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct mb-25" /></td>
                            <td class="poprod-decpt">
                                <button data-bs-toggle="modal" data-bs-target="#attribute" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                            </td>
                            <td><select class="form-select"><option>Select</option><option selected>KG</option></select></td>
                            <td><input type="text" value="10" class="form-control mw-100" /></td>
                        </tr>`;
            }

            // Add row based on active tab
            $('#addRowBtn').on('click', function(e) {
                e.preventDefault();
                var activeTab = $('.tab-pane.active').attr('id');
                if (activeTab === 'Maintenance') {
                    $('#maintenanceRows').append(getMaintenanceRow());
                } else if (activeTab === 'Spare') {
                    $('#spareRows').append(getSparePartRow());
                }
            });

            // Delete selected rows from active tab
            $('#deleteRowBtn').on('click', function(e) {
                e.preventDefault();
                var activeTab = $('.tab-pane.active').attr('id');
                if (activeTab === 'Maintenance') {
                    $('#maintenanceRows').find('input.row-checkbox:checked').closest('tr').remove();
                } else if (activeTab === 'Spare') {
                    $('#spareRows').find('input.row-checkbox:checked').closest('tr').remove();
                }
            });

            // (Optional) "Select All" checkbox per table
            $('.myrequesttablecbox thead input[type="checkbox"]').on('change', function() {
                var tbody = $(this).closest('table').find('tbody');
                var checked = $(this).is(':checked');
                tbody.find('input.row-checkbox').prop('checked', checked);
            });
        });
    </script>
@endsection
