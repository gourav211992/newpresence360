@extends('layouts.app')
@section('styles')
    <style>
        #soModal .table-responsive {
            overflow-y: auto;
            max-height: 300px;
            /* Set the height of the scrollable body */
            position: relative;
        }

        #soModal .po-order-detail {
            width: 100%;
            border-collapse: collapse;
        }

        #soModal .po-order-detail thead {
            position: sticky;
            top: 0;
            /* Stick the header to the top of the table container */
            background-color: white;
            /* Optional: Make sure header has a background */
            z-index: 1;
            /* Ensure the header stays above the body content */
        }

        #soModal .po-order-detail th {
            background-color: #f8f9fa;
            /* Optional: Background for the header */
            text-align: left;
            padding: 8px;
        }

        #soModal .po-order-detail td {
            padding: 8px;
        }
    </style>
@endsection
@section('content')
    <form class="ajax-input-form" data-module="pi" method="POST" action="{{ route('pi.store') }}" data-redirect="/purchase-indent" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="procurement_type_param" id="procurement_type_param" value="all">
        <input type="hidden" name="procurement_type" id="procurement_type" value="rm">
        <input type="hidden" name="show_attribute" value="0" id="show_attribute">
        <input type="hidden" name="so_item_ids" id="so_item_ids">
        <input type="hidden" name="item_ids" id="item_ids">
        <input type="hidden" name="requester_type" id="requester_type">
        <input type="hidden" name="so_tracking_required" id="so_tracking_required">
        <div class="app-content content">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Purchase Indent</h2>
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
                                <input type="hidden" name="document_status" id="document_status">
                                <button type="button" onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm mb-50 mb-sm-0"><i data-feather="arrow-left-circle"></i>
                                    Back</button>
                                <button type="submit" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 submit-button" name="action" value="draft"><i data-feather='save'></i> Save as Draft</button>
                                <button type="submit" class="btn btn-primary btn-sm submit-button" name="action" value="submitted"><i data-feather="check-circle"></i> Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-body">
                    <section id="basic-datatable">
                        <div class="row">
                            <div class="col-12">
                                <div class="card" id="basic_section">
                                    <div class="card-body customernewsection-form">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="newheader border-bottom mb-2 pb-25 d-flex flex-wrap justify-content-between">
                                                    <div>
                                                        <h4 class="card-title text-theme">Basic Information</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Series <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="book_id" name="book_id">
                                                            @foreach ($books as $book)
                                                                <option value="{{ $book->id }}">
                                                                    {{ ucfirst($book->book_code) }}</option>
                                                            @endforeach
                                                        </select>
                                                        <input type="hidden" name="book_code" id="book_code">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Indent No <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="document_number" class="form-control" id="document_number">
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Indent Date <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" class="form-control" value="{{ date('Y-m-d') }}" name="document_date" min = "{{ $current_financial_year['start_date'] }}" max = "{{ $current_financial_year['end_date'] }}">
                                                    </div>
                                                </div>
                                                {{-- <div class="row align-items-center mb-1">
                            <div class="col-md-3">
                                <label class="form-label">Reference No </label>
                            </div>
                            <div class="col-md-5">
                                <input type="text" name="reference_number" class="form-control">
                            </div>
                        </div> --}}
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Location <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="store_id" name="store_id">
                                                            @foreach ($locations as $location)
                                                                <option value="{{ $location->id }}">
                                                                    {{ $location?->store_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1 d-none" id = "department_id_header">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Requester</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="sub_store_id" name="sub_store_id">
                                                            <option value="">Select</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1 d-none" id = "user_id_header">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Requester <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="user_id" name="user_id" oninput = "setSelectedDepartment();">
                                                            <option value="">Select</option>
                                                            @foreach ($users as $user)
                                                                <option value="{{ $user->id }}" {{ $selecteduserId == $user->id ? 'selected' : '' }}>
                                                                    {{ ucfirst($user->name) }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1 d-none" id="reference_from">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Reference from</label>
                                                    </div>
                                                    <div class="col-md-5 action-button">
                                                        <button type="button" class="btn btn-outline-primary btn-sm mb-0 soSelect"><i data-feather="plus-square"></i> Sale Order</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card" id="item_section">
                                    <div class="card-body customernewsection-form">
                                        <div class="border-bottom mb-2 pb-25">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="newheader ">
                                                        <h4 class="card-title text-theme">Indent Item Wise Detail</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-sm-end">
                                                    <button type="button" id="importItem" class="mx-1 btn btn-sm btn-outline-primary importItem" onclick="openImportItemModal('create')">
                                                        <i data-feather="upload"></i>
                                                        Import Item
                                                    </button>
                                                    <a href="javascript:;" id="deleteBtn" class="btn btn-sm btn-outline-danger me-50">
                                                        <i data-feather="x-circle"></i> Delete</a>
                                                    <a href="javascript:;" id="addNewItemBtn" class="btn btn-sm btn-outline-primary">
                                                        <i data-feather="plus"></i> Add Item</a>
                                                    <a href="#" onclick = "copyItemRow();" id = "copy_item_section" style = "display:none;" class="btn btn-sm btn-outline-primary">
                                                        <i data-feather="copy"></i> Copy Item</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="table-responsive pomrnheadtffotsticky">
                                                    <table id="itemTable" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad" data-json-key="components_json" data-row-selector="tr[id^='row_']">
                                                        <thead>
                                                            <tr>
                                                                <th class="customernewsection-form">
                                                                    <div class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input" id="Email">
                                                                        <label class="form-check-label" for="Email"></label>
                                                                    </div>
                                                                </th>
                                                                <th width="200px">Item Code</th>
                                                                <th width="300px">Item Name</th>
                                                                <th max-width="180px">Attributes</th>
                                                                <th>UOM</th>
                                                                <th class="text-end">Req Qty</th>
                                                                <th class="text-end">Avl Stock</th>
                                                                <th class="text-end">Pending PO</th>
                                                                <th class="text-end">Adj Qty</th>
                                                                <th class="text-end">Order Qty</th>
                                                                <th width="240px">Vendor Name</th>
                                                                <th width="100px" id="so_no">SO No.</th>
                                                                <th width="350px">Remarks</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel" id="item_header">
                                                        </tbody>
                                                        <tfoot>
                                                            <tr valign="top">
                                                                <td colspan="13" rowspan="10">
                                                                    <table class="table border">
                                                                        <tbody id="itemDetailDisplay">
                                                                            <tr>
                                                                                <td class="p-0">
                                                                                    <h6 class="text-dark mb-0 bg-light-primary py-1 px-50">
                                                                                        <strong>Item Details</strong>
                                                                                    </h6>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                            </tr>
                                                                            <tr>
                                                                            </tr>
                                                                            <tr>
                                                                            </tr>
                                                                            <tr>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                                <div class="row mt-2">
                                                    <div class="col-md-12">
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <div class="mb-1">
                                                                    <label class="form-label">Upload Document</label>
                                                                    <input type="file" name="attachment[]" class="form-control" onchange = "addFiles(this,'main_order_file_preview')" multiple>
                                                                    <span class = "text-primary small">{{ __('message.attachment_caption') }}</span>
                                                                </div>
                                                            </div>
                                                            <div class = "col-md-6" style = "margin-top:19px;">
                                                                <div class = "row" id = "main_order_file_preview">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="mb-1">
                                                            <label class="form-label">Final Remarks</label>
                                                            <textarea maxlength="250" type="text" rows="4" name="remarks" class="form-control" placeholder="Enter Remarks here..."></textarea>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

    </form>

    {{-- Attribute popup --}}
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

                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="button" {{-- data-bs-dismiss="modal" --}} class="btn btn-primary submitAttributeBtn">Select</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Import Item Modal (AJAX version, no form) -->
    <div class="modal fade" id="importItemModal" tabindex="-1" aria-labelledby="importItemModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow-lg rounded">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="importItemModalLabel">Import Items</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- File Upload -->
                    <div class="mb-3">
                        <label for="fileUpload" class="form-label fw-semibold">Upload File</label>
                        <div class="border border-dashed border-2 border-primary rounded p-4 text-center dragdrop-area">
                            <p class="text-muted mb-2">Drag and drop your file here or click to upload</p>
                            <input type="file" id="fileUpload" name="attachment" class="form-control d-none">
                            <button type="button" class="btn btn-outline-primary" onclick="$('#fileUpload').click();">Choose File</button>
                        </div>

                        <!-- Uploaded File Info -->
                        <div id="fileNameDisplay" class="mt-3 d-none d-flex align-items-center gap-2 text-success">
                            <i class="bi bi-check-circle-fill fs-5"></i>
                            <span><strong>File uploaded:</strong> <span id="selectedFileName"></span></span>
                        </div>

                        <!-- Error Display -->
                        <div id="upload-error" class="text-danger mt-2 d-none"></div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="progress mt-3 d-none" id="uploadProgress">
                        <div class="progress-bar" id="uploadProgressBar" role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100">0%</div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-4 text-end">
                        <button type="button" class="btn btn-success" id="sampleBtn">Download Sample</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelBtn">Cancel</button>
                        <button type="button" class="btn btn-primary" id="proceedBtn" style="display:none;">Proceed</button>
                    </div>

                    <!-- Parsed Preview Section -->
                    <div id="parsedPreview" class="mt-5 d-none">
                        <ul class="nav nav-tabs" id="importTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="valid-tab" data-bs-toggle="tab" data-bs-target="#validTabPane" type="button" role="tab" aria-controls="validTabPane" aria-selected="true">
                                    Valid Items <span id="valid-count"></span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="invalid-tab" data-bs-toggle="tab" data-bs-target="#invalidTabPane" type="button" role="tab" aria-controls="invalidTabPane" aria-selected="false">
                                    Invalid Items <span id="invalid-count"></span>
                                </button>
                            </li>
                        </ul>
                        <button type="button" class="btn btn-primary mt-3 d-none" id="submitBtn">Import Items</button>
                        <div class="tab-content border border-top-0" id="importTabsContent">
                            <div class="tab-pane fade show active" id="validTabPane" role="tabpanel" aria-labelledby="valid-tab">
                                <div class="table-responsive">
                                    <table class="datatables-basic table myrequesttablecbox">
                                        <thead class="table-success">
                                            <tr id="valid-table-header"></tr>
                                        </thead>
                                        <tbody id="valid-table-body"></tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="invalidTabPane" role="tabpanel" aria-labelledby="invalid-tab">
                                <div class="table-responsive">
                                    <table class="datatables-basic table myrequesttablecbox">
                                        <thead class="table-danger">
                                            <tr id="invalid-table-header"></tr>
                                        </thead>
                                        <tbody id="invalid-table-body"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Item Remark Modal --}}
    <div class="modal fade" id="itemRemarkModal" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-2 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="shareProjectTitle">Remarks</h1>
                    {{-- <p class="text-center">Enter the details below.</p> --}}
                    <div class="row mt-2">
                        <div class="col-md-12 mb-1">
                            <label class="form-label">Remarks <span class="text-danger">*</span></label>
                            <input type="hidden" name="row_count" id="row_count">
                            <textarea maxlength="250" class="form-control" placeholder="Enter Remarks"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" data-bs-dismiss="modal" class="btn btn-outline-secondary me-1">Cancel</button>
                    <button type="button" class="btn btn-primary itemRemarkSubmit">Submit</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Taxes --}}
    @include('procurement.pi.partials.so-modal')
    @include('procurement.pi.partials.analyze-modal')
    @include('procurement.pi.partials.so-modal-submit')
@endsection
@section('scripts')
    <script type="text/javascript" src="{{ asset('assets/js/modules/common-attr-ui.js') }}"></script>
    <script type="text/javascript" src="{{ asset('assets/js/modules/pi.js') }}"></script>
    <script type="text/javascript" src="{{ asset('app-assets/js/file-uploader.js') }}"></script>
    <script>
        var analyzeSoItemUrl = '{{ route('pi.analyze.so-item') }}';
        let processSoActionUrl = '{{ route('pi.process.so-item.submit') }}';
    </script>
    <script>
        setTimeout(() => {
            $("#book_id").trigger('change');
        }, 0);
        $(document).on('change', '#book_id', (e) => {
            let bookId = e.target.value;
            if (bookId) {
                getDocNumberByBookId(bookId);
            } else {
                $("#document_number").val('');
                $("#book_id").val('');
                $("#document_number").attr('readonly', false);
            }
        });

        function getDocNumberByBookId(bookId) {
            let document_date = $("[name='document_date']").val();
            let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId + '&document_date=' +
                document_date;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        $("#book_code").val(data.data.book_code);
                        if (!data.data.doc.document_number) {
                            $("#document_number").val('');
                        }
                        $("#document_number").val(data.data.doc.document_number);
                        if (data.data.doc.type == 'Manually') {
                            $("#document_number").attr('readonly', false);
                        } else {
                            $("#document_number").attr('readonly', true);
                        }
                        const parameters = data.data.parameters;
                        setServiceParameters(parameters);
                        //set department
                        setSelectedDepartment();
                    }
                    if (data.status == 404) {
                        $("#book_code").val('');
                        $("#document_number").val('');
                        const docDateInput = $("[name='document_date']");
                        docDateInput.attr('min', "{{ $current_financial_year['start_date'] }}");
                        docDateInput.attr('max', "{{ $current_financial_year['end_date'] }}");
                        docDateInput.val(new Date().toISOString().split('T')[0]);
                        alert(data.message);
                    }
                });
            });
        }

        function setSelectedDepartment() {
            let userId = $("#user_id").val();
            let actionUrl = '{{ route('pi.get.selected.department') }}' + '?user_id=' + userId;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.selectedDepartmentId == 200) {
                        const departmentId = data.selectedDepartmentId;
                        if (departmentId) {
                            $("#department_id").val(departmentId);
                        }
                    }
                });
            });
        }

        /*Set Service Parameter*/
        function setServiceParameters(parameters) {
            /*Date Validation*/
            const docDateInput = $("[name='document_date']");
            let isFeature = false;
            let isPast = false;
            if (parameters.future_date_allowed && parameters.future_date_allowed.includes('yes')) {
                let futureDate = new Date();
                futureDate.setDate(futureDate.getDate() /*+ (parameters.future_date_days || 1)*/ );
                docDateInput.val(futureDate.toISOString().split('T')[0]);
                docDateInput.attr("min", new Date().toISOString().split('T')[0]);
                isFeature = true;
            } else {
                isFeature = false;
                docDateInput.attr("max", new Date().toISOString().split('T')[0]);
            }
            if (parameters.back_date_allowed && parameters.back_date_allowed.includes('yes')) {
                let backDate = new Date();
                backDate.setDate(backDate.getDate() /*- (parameters.back_date_days || 1)*/ );
                docDateInput.val(backDate.toISOString().split('T')[0]);
                // docDateInput.attr("max", "");
                isPast = true;
            } else {
                isPast = false;
                docDateInput.attr("min", new Date().toISOString().split('T')[0]);
            }
            /*Date Validation*/
            if (isFeature && isPast) {
                docDateInput.removeAttr('min');
                docDateInput.removeAttr('max');
            }
            /*Reference from*/
            let reference_from_service = parameters.reference_from_service;
            $("#procurement_type_param").val(parameters.procurement_type);
            if (parameters.procurement_type.includes('all')) {
                $("#procurement_type").val('rm');
            }
            if (parameters.procurement_type.includes('Make to order')) {
                $("#procurement_type").val('rm');
            }
            if (parameters.procurement_type.includes('Buy to order')) {
                $("#procurement_type").val('fg');
            }

            if (reference_from_service.length) {
                let pi = '{{ \App\Helpers\ConstantHelper::SO_SERVICE_ALIAS }}';
                if (reference_from_service.includes(pi)) {
                    $("#reference_from").removeClass('d-none');
                } else {
                    $("#reference_from").addClass('d-none');
                }
                if (reference_from_service.includes('d')) {
                    $("#addNewItemBtn").removeClass('d-none');
                } else {
                    $("#addNewItemBtn").addClass('d-none');
                }
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: "Please update first reference from service param.",
                    icon: 'error',
                });
                setTimeout(() => {
                    location.href = '{{ route('pi.index') }}';
                }, 1500);
            }

            //Requester Type
            let requesterType = parameters?.requester_type || '';
            if (requesterType.includes('Department')) {
                $("#user_id_header").addClass('d-none');
                $("#department_id_header").removeClass('d-none');
                $("#requester_type").val('Department');
            } else {
                $("#user_id_header").removeClass('d-none');
                $("#department_id_header").addClass('d-none');
                $("#requester_type").val('User');
            }

            let soTrackingRequired = parameters?.so_tracking_required || '';
            $("#so_tracking_required").val(soTrackingRequired);
            if (soTrackingRequired.includes('yes')) {
                $("#soTrackingText").removeClass('d-none');
                $("#soTrackingNo").removeClass('d-none');
                $("#so_no").removeClass('d-none');
            } else {
                $("#soTrackingText").addClass('d-none');
                $("#soTrackingNo").addClass('d-none');
                $("#so_no").addClass('d-none');
            }

        }

        // for component item code
        function initializeAutocomplete2(selector, type) {
            $(selector).autocomplete({
                minLength: 0,
                source: function(request, response) {
                    let selectedAllItemIds = [];
                    $("#itemTable tbody [id*='row_']").each(function(index, item) {
                        if (Number($(item).find('[name*="[item_id]"]').val())) {
                            selectedAllItemIds.push(Number($(item).find('[name*="[item_id]"]').val()));
                        }
                    });
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: 'pi_comp_item',
                            selectedAllItemIds: JSON.stringify(selectedAllItemIds)
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: `${item.item_name} (${item.item_code})`,
                                    code: item.item_code || '',
                                    item_id: item.id,
                                    item_name: item.item_name,
                                    uom_name: item.uom?.name,
                                    uom_id: item.uom_id,
                                    hsn_id: item.hsn?.id,
                                    hsn_code: item.hsn?.code,
                                    alternate_u_o_ms: item.alternate_u_o_ms,
                                    is_attr: item.item_attributes_count,

                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });
                },
                select: function(event, ui) {
                    let $input = $(this);
                    let itemCode = ui.item.code;
                    let itemName = ui.item.value;
                    let itemN = ui.item.item_name;
                    let itemId = ui.item.item_id;
                    let uomId = ui.item.uom_id;
                    let uomName = ui.item.uom_name;
                    let hsnId = ui.item.hsn_id;
                    let hsnCode = ui.item.hsn_code;
                    $input.attr('data-name', itemName);
                    $input.attr('data-code', itemCode);
                    $input.attr('data-id', itemId);
                    $input.closest('tr').find('[name*="[item_id]"]').val(itemId);
                    $input.closest('tr').find('[name*=item_code]').val(itemCode);
                    $input.closest('tr').find('[name*=item_name]').val(itemN);
                    $input.closest('tr').find('[name*=hsn_id]').val(hsnId);
                    $input.closest('tr').find('[name*=hsn_code]').val(hsnCode);
                    $input.val(itemCode);
                    let uomOption = `<option value=${uomId}>${uomName}</option>`;
                    if (ui.item?.alternate_u_o_ms) {
                        for (let alterItem of ui.item.alternate_u_o_ms) {
                            uomOption +=
                                `<option value="${alterItem.uom_id}" ${alterItem.is_purchasing ? 'selected' : ''}>${alterItem.uom?.name}</option>`;
                        }
                    }
                    $input.closest('tr').find('[name*=uom_id]').empty().append(uomOption);
                    $input.closest('tr').find('[name*=attr_group_id]').remove();

                    setTimeout(() => {
                        if (ui.item.is_attr) {
                            $input.closest('tr').find('.attributeBtn').trigger('click');
                        } else {
                            $input.closest('tr').find('.attributeBtn').trigger('click');
                            $input.closest('tr').find('[name*="[qty]"]').val('').focus();
                        }
                    }, 100);
                    validateItems($input, true);
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $(this).attr('data-name', '');
                        $(this).attr('data-code', '');
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            }).on("input", function() {
                if ($(this).val().trim() === "") {
                    $(this).removeData("selected");
                    $(this).closest('tr').find("input[name*='component_item_name']").val('');
                    $(this).closest('tr').find("input[name*='item_name']").val('');
                    $(this).closest('tr').find("td[id*='itemAttribute_']").html(defautAttrBtn);
                    $(this).closest('tr').find("input[name*='item_id']").val('');
                    $(this).closest('tr').find("input[name*='item_code']").val('');
                    $(this).closest('tr').find("input[name*='attr_name']").remove();
                }
            });
        }

        initializeAutocomplete2(".comp_item_code");

        $(document).on('click', '#addNewItemBtn', (e) => {

            let rowsLength = $("#itemTable > tbody > tr").length;
            /*Check last tr data shoud be required*/
            let lastRow = $('#itemTable .mrntableselectexcel tr:last');
            let lastTrObj = {
                item_id: "",
                attr_require: true,
                row_length: lastRow.length
            };

            if (lastRow.length == 0) {
                lastTrObj.attr_require = false;
                lastTrObj.item_id = "0";
            }

            if (lastRow.length > 0) {
                let item_id = lastRow.find("[name*='[item_id]']").val();
                if (lastRow.find("[name*='attr_name']").length) {
                    var emptyElements = lastRow.find("[name*='attr_name']").filter(function() {
                        return $(this).val().trim() === '';
                    });
                    attr_require = emptyElements?.length ? true : false;
                } else {
                    attr_require = true;
                }

                lastTrObj = {
                    item_id: item_id,
                    attr_require: attr_require,
                    row_length: lastRow.length
                };

                if ($("tr[id*='row_']:last").find("[name*='[attr_group_id]']").length == 0 && item_id) {
                    lastTrObj.attr_require = false;
                }
            }
            let soTracking = $("#so_tracking_required").val() || '';
            let actionUrl = '{{ route('pi.item.row') }}' + '?count=' + rowsLength + '&component_item=' + JSON
                .stringify(lastTrObj) + `&so_tracking_required=${soTracking}`;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        if (rowsLength) {
                            so
                            $("#itemTable > tbody > tr:last").after(data.data.html);
                        } else {
                            $("#itemTable > tbody").html(data.data.html);
                        }
                        initializeAutocomplete2('.comp_item_code');
                        $(".soSelect").prop('disabled', true);
                        initAutocompVendor("[name*='[vendor_code]']");
                        document.getElementById('copy_item_section').style.display = "";
                    } else if (data.status == 422) {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message || 'An unexpected error occurred.',
                            icon: 'error',
                        });
                    } else {
                        console.log("Someting went wrong!");
                    }
                });
            });
        });

        /*Delete Row*/
        $(document).on('click', '#deleteBtn', (e) => {
            let itemIds = [];
            $('#itemTable > tbody .form-check-input').each(function() {
                if ($(this).is(":checked")) {
                    itemIds.push($(this).val());
                }
            });
            if (itemIds.length) {
                itemIds.forEach(function(item, index) {
                    $(`#row_${item}`).remove();
                });
            } else {
                alert("Please first add & select row item.");
            }
            if (!$("tr[id*='row_']").length) {
                $("#itemTable > thead .form-check-input").prop('checked', false);
                $(".soSelect").prop('disabled', false);
                document.getElementById('copy_item_section').style.display = "none";
            }
        });

        /*Check attrubute*/
        $(document).on('click', '.attributeBtn', (e) => {
            let tr = e.target.closest('tr');
            let item_name = tr.querySelector('[name*=item_code]').value;
            let item_id = tr.querySelector('[name*="[item_id]"]').value;
            let selectedAttr = [];
            const attrElements = tr.querySelectorAll('[name*=attr_name]');
            if (attrElements.length > 0) {
                selectedAttr = Array.from(attrElements).map(element => element.value);
                selectedAttr = JSON.stringify(selectedAttr);
            }
            if (item_name && item_id) {
                let rowCount = tr.getAttribute('data-index');
                getItemAttribute(item_id, rowCount, selectedAttr, tr);
            } else {
                alert("Please select first item name.");
            }
        });

        /*For comp attr*/
        function getItemAttribute(itemId, rowCount, selectedAttr, tr) {
            let isSo = $(tr).find('[name*="so_item_id"]').length ? 1 : 0;
            if (!isSo) {
                isSo = $(tr).find('[name*="so_pi_mapping_item_id"]').length ? 1 : 0;
            }
            if (!isSo) {
                if ($(tr).find('td[id*="itemAttribute_"]').data('disabled')) {
                    isSo = 1;
                }
            }
            let actionUrl = '{{ route('pi.item.attr') }}' + '?item_id=' + itemId +
                `&rowCount=${rowCount}&selectedAttr=${selectedAttr}&isSo=${isSo}`;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        $("#attribute tbody").empty();
                        $("#attribute table tbody").append(data.data.html)
                        $(tr).find('td:nth-child(2)').find("[name*='[attr_name]']").remove();
                        $(tr).find('td:nth-child(2)').append(data.data.hiddenHtml);
                        $(tr).find("td[id*='itemAttribute_']").attr('attribute-array', JSON.stringify(data
                            .data.itemAttributeArray));
                        if (data.data.attr) {
                            $("#attribute").modal('show');
                            $(".select2").select2();
                        }
                        qtyEnabledDisabled();
                    }
                });
            });
        }


        /*Display item detail*/
        $(document).on('input change focus', '#itemTable tr input', (e) => {
            let currentTr = e.target.closest('tr');
            let rowCount = $(currentTr).attr('data-index');
            let pName = $(currentTr).find("[name*='component_item_name']").val();
            let itemId = $(currentTr).find("[name*='[item_id]']").val();
            let remark = '';
            if ($(currentTr).find("[name*='remark']")) {
                remark = $(currentTr).find("[name*='remark']").val() || '';
            }
            if (itemId) {
                let selectedAttr = [];
                $(currentTr).find("[name*='attr_name']").each(function(index, item) {
                    if ($(item).val()) {
                        selectedAttr.push($(item).val());
                    }
                });

                let uomId = $(currentTr).find("[name*='[uom_id]']").val() || '';
                let qty = $(currentTr).find("[name*='[qty]']").val() || '';
                let pi_item_id = '';
                let store_id = $("#store_id").val() || '';
                let sub_store_id = $("#sub_store_id").val() || '';
                let actionUrl = '{{ route('pi.get.itemdetail') }}' + '?item_id=' + itemId + '&selectedAttr=' + JSON
                    .stringify(selectedAttr) + '&remark=' + remark + '&uom_id=' + uomId + '&qty=' + qty +
                    '&store_id=' + store_id + '&sub_store_id=' + sub_store_id;
                fetch(actionUrl).then(response => {
                    return response.json().then(data => {
                        if (data.status == 200) {
                            console.log(data.data.pendingPo);
                            $("#itemDetailDisplay").html(data.data.html);
                            let avlStock = data.data?.inventoryStock.confirmedStocks;
                            $(`input[name="components[${rowCount}][avl_stock]"]`).val(Number(
                                avlStock).toFixed(2));
                            $(`input[name="components[${rowCount}][pending_po]"]`).val(Number(data
                                .data.pendingPo).toFixed(2));
                        }
                    });
                });
            }
        });

        /*submit attribute*/
        $(document).on('click', '.submitAttributeBtn', (e) => {
            let rowCount = $("[id*=row_].trselected").attr('data-index');
            $(`[name="components[${rowCount}][qty]"]`).focus();
            $("#attribute").modal('hide');
        });

        /*So modal*/
        $(document).on('click', '.soSelect', (e) => {
            let paramValue = $("#procurement_type_param").val();
            let option = '';
            if (paramValue.includes('All')) {
                option += `<option value="rm">Make to order</option><option value="fg">Buy to order</option>`;
            }
            if (paramValue.includes('Make to order')) {
                option += `<option value="rm">Make to order</option>`;
            }
            if (paramValue.includes('Buy to order')) {
                option += `<option value="fg">Buy to order</option>`;
            }
            $("#orderTypeSelect").empty().append(option);
            $("#soModal").modal('show');
            openSaleRequest();
            getSoItems();
        });

        /*searchPiBtn*/
        $(document).on('click', '.searchSoBtn', (e) => {
            getSoItems();
        });

        function openSaleRequest() {
            initializeAutocompleteQt("customer_code_input_qt", "customer_id_qt_val", "customer", "customer_code",
                "company_name");
            initializeAutocompleteQt("book_code_input_qt", "book_id_qt_val", "book_so", "book_code", "");
            initializeAutocompleteQt("document_no_input_qt", "document_id_qt_val", "sale_order_document_qt_pi",
                "document_number", "");
            initializeAutocompleteQt("item_name_input_qt", "item_id_qt_val", "po_item_list", "item_code", "item_name");
        }

        function initializeAutocompleteQt(selector, selectorSibling, typeVal, labelKey1, labelKey2 = "") {
            $("#" + selector).autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: '/search',
                        method: 'GET',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            type: typeVal,
                            cutomer_id: $("#cutomer_id_qt_val").val(),
                            header_book_id: $("#book_id").val(),
                        },
                        success: function(data) {
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: `${item[labelKey1]} ${labelKey2 ? (item[labelKey2] ? '(' + item[labelKey2] + ')' : '') : ''}`,
                                    code: item[labelKey1] || '',
                                };
                            }));
                        },
                        error: function(xhr) {
                            console.error('Error fetching customer data:', xhr.responseText);
                        }
                    });
                },
                appendTo: '#soModal',
                minLength: 0,
                select: function(event, ui) {
                    var $input = $(this);
                    $input.val(ui.item.label);
                    $("#" + selectorSibling).val(ui.item.id);
                    getSoItems();
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $("#" + selectorSibling).val("");
                        getSoItems();
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $("#" + selectorSibling).val("");
                    $(this).autocomplete("search", "");
                    getSoItems();
                }
            }).blur(function() {
                if ($(this).val().trim() === "") {
                    $("#" + selectorSibling).val("");
                    getSoItems();
                }
            });
        }

        function getSoItems() {
            let isAttribute = 0;
            if ($("#attributeCheck").is(':checked')) {
                isAttribute = 1;
            } else {
                isAttribute = 0;
            }
            let header_book_id = $("#book_id").val() || '';
            let series_id = $("#book_id_qt_val").val() || '';
            let document_number = $("#document_no_input_qt").val() || '';
            let item_id = $("#item_id_qt_val").val() || '';
            let customer_id = $("#customer_id_qt_val").val() || '';
            let actionUrl = '{{ route('pi.get.so') }}';
            let item_search = $("#item_name_search").val();
            let fullUrl =
                `${actionUrl}?series_id=${encodeURIComponent(series_id)}&document_number=${encodeURIComponent(document_number)}&item_id=${encodeURIComponent(item_id)}&customer_id=${encodeURIComponent(customer_id)}&header_book_id=${encodeURIComponent(header_book_id)}&is_attribute=${isAttribute}&item_search=${item_search}`;
            fetch(fullUrl).then(response => {
                return response.json().then(data => {
                    $(".po-order-detail #soDataTable").empty().append(data.data.pis);
                    if (data.data.isAttribute) {
                        $("#soHeaderAttribute").removeClass('d-none');
                    } else {
                        $("#soHeaderAttribute").addClass('d-none');
                    }
                });
            });
        }

        $(document).on('keyup', '#item_name_search', (e) => {
            getSoItems();
        });

        $(document).on('change', '#attributeCheck', (e) => {
            if (e.target.checked) {
                $("#show_attribute").val(1);
            } else {
                $("#show_attribute").val(0);
            }
            getSoItems();
        });
        $(document).on('blur', '#customer_code_input_qt', (e) => {
            getSoItems();
        });

        /*Checkbox for pi item list*/
        $(document).on('change', '#soModal .po-order-detail > thead .form-check-input', (e) => {
            if (e.target.checked) {
                $("#soModal .po-order-detail > tbody .form-check-input").each(function() {
                    $(this).prop('checked', true);
                });
            } else {
                $("#soModal .po-order-detail > tbody .form-check-input").each(function() {
                    $(this).prop('checked', false);
                });
            }
        });

        $(document).on('change', '#soModal .po-order-detail > tbody .form-check-input', (e) => {
            if (!$("#soModal .po-order-detail > tbody .form-check-input:not(:checked)").length) {
                $('#soModal .po-order-detail > thead .form-check-input').prop('checked', true);
            } else {
                $('#soModal .po-order-detail > thead .form-check-input').prop('checked', false);
            }
        });

        // asdasdas
        $(document).on('change', '#soSubmitModal .po-order-detail > thead .form-check-input', (e) => {
            if (e.target.checked) {
                $("#soSubmitModal .po-order-detail > tbody .form-check-input").each(function() {
                    $(this).prop('checked', true);
                });
            } else {
                $("#soSubmitModal .po-order-detail > tbody .form-check-input").each(function() {
                    $(this).prop('checked', false);
                });
            }
        });
        $(document).on('change', '#soSubmitModal .po-order-detail > tbody .form-check-input', (e) => {
            if (!$("#soSubmitModal .po-order-detail > tbody .form-check-input:not(:checked)").length) {
                $('#soSubmitModal .po-order-detail > thead .form-check-input').prop('checked', true);
            } else {
                $('#soSubmitModal .po-order-detail > thead .form-check-input').prop('checked', false);
            }
        });


        function getSelectedSoIDS() {
            let ids = [];
            $('#soModal .pi_item_checkbox:checked').each(function() {
                ids.push($(this).val());
            });
            return ids;
        }

        function getSelectedItemIDS() {
            let ids = [];
            $('#soModal .pi_item_checkbox:checked').each(function() {
                if (Number($(this).data("item-id"))) {
                    ids.push(Number($(this).data("item-id")));
                }
            });
            return ids;
        }

        $(document).on('click', '.soProcess', (e) => {
            e.preventDefault();
            $("#soSubmitModal th .form-check-input").prop('checked', false);
            let ids = getSelectedSoIDS();
            if (!ids.length) {
                $("[name='so_item_ids']").val('');
                $("[name='item_ids']").val('');
                $("#soModal").modal('hide');
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select at least one one so item.',
                    icon: 'error',
                });
                return false;
            }
            $("[name='so_item_ids']").val(ids);
            let itemIds = getSelectedItemIDS();
            $("[name='item_ids']").val(itemIds);

            // for component item code
            function initializeAutocomplete2(selector, type) {
                $(selector).autocomplete({
                    minLength: 0,
                    source: function(request, response) {
                        let selectedAllItemIds = [];
                        $("#itemTable tbody [id*='row_']").each(function(index, item) {
                            if (Number($(item).find('[name*="[item_id]"]').val())) {
                                selectedAllItemIds.push(Number($(item).find(
                                    '[name*="[item_id]"]').val()));
                            }
                        });
                        $.ajax({
                            url: '/search',
                            method: 'GET',
                            dataType: 'json',
                            data: {
                                q: request.term,
                                type: 'pi_comp_item',
                                selectedAllItemIds: JSON.stringify(selectedAllItemIds)
                            },
                            success: function(data) {
                                response($.map(data, function(item) {
                                    return {
                                        id: item.id,
                                        label: `${item.item_name} (${item.item_code})`,
                                        code: item.item_code || '',
                                        item_id: item.id,
                                        item_name: item.item_name,
                                        uom_name: item.uom?.name,
                                        uom_id: item.uom_id,
                                        hsn_id: item.hsn?.id,
                                        hsn_code: item.hsn?.code,
                                        alternate_u_o_ms: item.alternate_u_o_ms,
                                        is_attr: item.item_attributes_count,
                                    };
                                }));
                            },
                            error: function(xhr) {
                                console.error('Error fetching customer data:', xhr
                                    .responseText);
                            }
                        });
                    },
                    select: function(event, ui) {
                        let $input = $(this);
                        let itemCode = ui.item.code;
                        let itemName = ui.item.value;
                        let itemN = ui.item.item_name;
                        let itemId = ui.item.item_id;
                        let uomId = ui.item.uom_id;
                        let uomName = ui.item.uom_name;
                        let hsnId = ui.item.hsn_id;
                        let hsnCode = ui.item.hsn_code;
                        $input.attr('data-name', itemName);
                        $input.attr('data-code', itemCode);
                        $input.attr('data-id', itemId);
                        $input.closest('tr').find('[name*="[item_id]"]').val(itemId);
                        $input.closest('tr').find('[name*=item_code]').val(itemCode);
                        $input.closest('tr').find('[name*=item_name]').val(itemN);
                        $input.closest('tr').find('[name*=hsn_id]').val(hsnId);
                        $input.closest('tr').find('[name*=hsn_code]').val(hsnCode);
                        $input.val(itemCode);
                        let uomOption = `<option value=${uomId}>${uomName}</option>`;
                        if (ui.item?.alternate_u_o_ms) {
                            for (let alterItem of ui.item.alternate_u_o_ms) {
                                uomOption +=
                                    `<option value="${alterItem.uom_id}" ${alterItem.is_purchasing ? 'selected' : ''}>${alterItem.uom?.name}</option>`;
                            }
                        }
                        $input.closest('tr').find('[name*=uom_id]').empty().append(uomOption);
                        $input.closest('tr').find('[name*=attr_group_id]').remove();
                        setTimeout(() => {
                            if (ui.item.is_attr) {
                                $input.closest('tr').find('.attributeBtn').trigger('click');
                            } else {
                                $input.closest('tr').find('.attributeBtn').trigger('click');
                                $input.closest('tr').find('[name*="[qty]"]').val('').focus();
                            }
                        }, 100);
                        validateItems($input, true);
                        return false;
                    },
                    change: function(event, ui) {
                        if (!ui.item) {
                            $(this).val("");
                            // $('#itemId').val('');
                            $(this).attr('data-name', '');
                            $(this).attr('data-code', '');
                        }
                    }
                }).focus(function() {
                    if (this.value === "") {
                        $(this).autocomplete("search", "");
                    }
                }).on("input", function() {
                    if ($(this).val().trim() === "") {
                        $(this).removeData("selected");
                        $(this).closest('tr').find("input[name*='component_item_name']").val('');
                        $(this).closest('tr').find("input[name*='item_name']").val('');
                        $(this).closest('tr').find("td[id*='itemAttribute_']").html(defautAttrBtn);
                        $(this).closest('tr').find("input[name*='item_id']").val('');
                        $(this).closest('tr').find("input[name*='item_code']").val('');
                        $(this).closest('tr').find("input[name*='attr_name']").remove();
                    }
                });
            }

            let isAttribute = 0;
            if ($("#attributeCheck").is(':checked')) {
                isAttribute = 1;
            } else {
                isAttribute = 0;
            }
            let procurementType = $("#orderTypeSelect").val() || 'rm';

            ids = JSON.stringify(ids);

            let selectedItems = [];
            if (!isAttribute) {
                $("#soModal .pi_item_checkbox:checked").each(function() {
                    selectedItems.push({
                        "sale_order_id": Number($(this).val()),
                        "item_id": Number($(this).data("item-id"))
                    });
                });
            }
            let storeId = $("#store_id").val() || '';
            let selectedItemsParam = encodeURIComponent(JSON.stringify(selectedItems));
            let soTracking = $("#so_tracking_required").val();
            let actionUrl =
                `{{ route('pi.process.so-item') }}?ids=${ids}&is_attribute=${isAttribute}&selected_items=${selectedItemsParam}&so_tracking_required=${soTracking}&procurement_type=${procurementType}&store_id=${storeId}`;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        // $("#itemTable .mrntableselectexcel").empty().append(data.data.pos);
                        // initializeAutocomplete2(".comp_item_code");
                        $("#soModal").modal('hide');
                        // $(".soSelect").prop('disabled',true);
                        if (data.data.procurement_type != 'fg') {
                            $("#soSubmitDataTable").empty().append(data.data.pos);
                            $("#soSubmitModal").modal('show');
                        } else {
                            $("#itemTable .mrntableselectexcel").empty().append(data.data.pos);
                            setTimeout(() => {
                                $("#itemTable .mrntableselectexcel tr").each(function(index,
                                    item) {
                                    let currentIndex = index + 1;
                                    setAttributesUIHelper(currentIndex,
                                        "#itemTable");
                                });
                            }, 100);
                        }
                    } else {
                        Swal.fire({
                            title: 'Error!',
                            text: data.message,
                            icon: 'error',
                        });
                    }
                });
            });
        });
        /*So modal*/

        /*Final process submit*/
        $(document).on('click', '.soSubmitProcess', (e) => {
            if ($('#soSubmitModal tbody .form-check-input:checked').length) {
                $("#soSubmitModal").modal('hide');
                let selectedData = [];
                $('#soSubmitModal tbody .form-check-input:checked').each(function(index, item) {
                    let dataItem = JSON.parse($(item).attr('data-item'));
                    selectedData.push(dataItem);
                });

                if (selectedData.length) {
                    let soTracking = $("#so_tracking_required").val() || '';
                    let storeId = $("#store_id").val() || '';
                    fetch(processSoActionUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                selectedData: selectedData,
                                so_tracking_required: soTracking,
                                store_id: storeId
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status == 200) {
                                $("#itemTable .mrntableselectexcel").empty().append(data.data.pos);
                                initAutocompVendor("[name*='[vendor_code]']");
                                initializeAutocomplete2(".comp_item_code");
                                $(".soSelect").prop('disabled', true);
                                $("#soSubmitModal").modal('hide');
                                document.getElementById('copy_item_section').style.display = "";
                                setTimeout(() => {
                                    $("#itemTable .mrntableselectexcel tr").each(function(index, item) {
                                        let currentIndex = index + 1;
                                        setAttributesUIHelper(currentIndex, "#itemTable");
                                    });
                                }, 100);
                            }
                        });
                }
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select at least one one so item.',
                    icon: 'error',
                });
                return false;
            }
        });

        $(document).on('click', '#backBtn', (e) => {
            $("#soSubmitModal").modal('hide');
            setTimeout(() => {
                $("#soModal").modal('show');
            }, 0);
        });

        document.addEventListener("DOMContentLoaded", function() {
            const searchInput = document.getElementById("search_filter");
            const tableBody = document.getElementById("soSubmitDataTable");

            function filterTable() {
                const searchTerm = searchInput.value.toLowerCase().trim();

                Array.from(tableBody.getElementsByTagName("tr")).forEach((row) => {
                    const itemCodeCell = row.cells[1]?.innerText.toLowerCase() || "";
                    const itemNameCell = row.cells[2]?.innerText.toLowerCase() || "";

                    // Check if row matches the search term in either column
                    const matchesItemCode = itemCodeCell.includes(searchTerm);
                    const matchesItemName = itemNameCell.includes(searchTerm);
                    const checkbox = row.querySelector("input[type='checkbox']");

                    // Show row if it matches the search term in any column
                    if (matchesItemCode || matchesItemName) {
                        row.style.display = "";
                    } else {
                        row.style.display = "none";
                        if (checkbox) {
                            checkbox.checked = false;
                        }
                    }
                });
            }
            searchInput.addEventListener("input", filterTable);
        });
        /*Final process submit*/

        $(document).on('click', '.clearPiFilter', (e) => {
            $("#item_name_search").val('');
            $("#item_name_input_qt").val('');
            $("#item_id_qt_val").val('');
            $("#department_po").val('');
            $("#department_id_po").val('');
            $("#customer_code_input_qt").val('');
            $("#customer_id_qt_val").val('');
            $("#book_code_input_qt").val('');
            $("#book_id_qt_val").val('');
            $("#document_no_input_qt").val('');
            $("#document_id_qt_val").val('');
            getSoItems();
        });

        function updateDropdown(storeId) {
            if ($("#requester_type").val().includes('Department')) {
                let actionUrl = '{{ route('subStore.get.from.stores') }}' + '?store_id=' + storeId;
                fetch(actionUrl).then(response => {
                    return response.json().then(data => {
                        let option = '<option value="">Select</option>';
                        if (data.data.length) {
                            data.data.forEach(function(item) {
                                option += `<option value="${item.id}">${item.name}</option>`;
                            })
                            $("#department_id_header").removeClass('d-none');
                        } else {
                            $("#department_id_header").addClass('d-none');
                        }
                        $("#sub_store_id").empty().append(option);
                    });
                });
            } else {
                $("#department_id_header").addClass('d-none');
                $("#user_id_header").removeClass('d-none');
            }
        }

        $(document).on('change', "[name='store_id']", function() {
            updateDropdown(this.value);
        });

        $(document).on('change', "[name='store_id']", (e) => {
            let storeId = e.target.value || '';
            updateDropdown(storeId);
        });

        setTimeout(() => {
            let storeId = $("#store_id").val() || '';
            if (storeId) {
                updateDropdown(storeId);
            }
        }, 100);
        // Opens import modal with store/type/header context
        function openImportItemModal(type) {
            const storeId = $('#store_id').val();
            if (!storeId) {
                Swal.fire({
                    title: 'Error!',
                    text: 'Please select a store first.',
                    icon: 'error',
                });
                return false;
            }

            // Reset file and modal state
            $('#fileUpload').val('');
            $('#fileNameDisplay').hide();
            $('#proceedBtn').hide();
            $('#upload-error').hide();
            $('#uploadProgress').addClass('d-none');
            $('#uploadProgressBar').css('width', '0%').text('0%');

            // Open modal and inject hidden fields
            $("#importItemModal").modal('show');
            const form = $('#importItemModal').find('form');
            form.find('input[name="store_id"], input[name="type"], input[name="po_header_id"]').remove();
            form.append(`<input type="hidden" name="store_id" value="${storeId}">`);
            form.append(`<input type="hidden" name="type" value="${type}">`);
        }

        $(function() {
            // Handle file selection
            $(document).on('change', '#fileUpload', function(e) {
                const file = e.target.files[0];
                if (!file) return;
                handleFileSelected(file);
            });
            let parsedValidRows = [];
            // Proceed button AJAX upload
            $(document).on('click', '#proceedBtn', function() {
                const fileInput = $('#fileUpload')[0];
                if (!fileInput.files.length) {
                    displayError('Please select a file to upload.');
                    return;
                }
                const file = fileInput.files[0];
                let formData = new FormData();
                formData.append('attachment', file);

                // Add any extra data if needed (store_id/type/po_header_id)
                $('#importItemModal input[type=hidden]').each(function() {
                    formData.append($(this).attr('name'), $(this).val());
                });
                $('#upload-error').hide().html('');
                $('#uploadProgress').removeClass('d-none');
                $('#uploadProgressBar').css('width', '0%').text('0%');

                $.ajax({
                    url: "{{ route('generic.import.save', ['alias' => 'purchase-indent']) }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        let xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                                $('#uploadProgressBar').css('width', percentComplete + '%').text(percentComplete + '%');
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        $('#uploadProgressBar').addClass('bg-success').text('Uploaded');

                        const validRows = response.data.valid || [];
                        const invalidRows = response.data.invalid || [];
                        const headers = response.headers || {};

                        // Update valid count
                        $('#valid-count').text(`(${validRows.length})`);
                        $('#invalid-count').text(`(${invalidRows.length})`);

                        // Show preview section
                        $('#parsedPreview').removeClass('d-none').show();

                        // Build table headers dynamically
                        function buildHeaderRow(headersMap, target) {
                            let headerHtml = '';
                            for (const key in headersMap) {
                                headerHtml += `<th>${headersMap[key]}</th>`;
                            }
                            headerHtml += `<th>Row</th><th>Errors</th>`;
                            $(target).html(headerHtml);
                        }

                        buildHeaderRow(headers, '#valid-table-header');
                        buildHeaderRow(headers, '#invalid-table-header');

                        // Build table body
                        function buildTableRows(data, headersMap) {
                            return data.map(row => {
                                let rowHtml = '<tr>';
                                for (const key in headersMap) {
                                    rowHtml += `<td>${row[key] ?? ''}</td>`;
                                }
                                rowHtml += `<td>${row.row_number ?? ''}</td>`;
                                if (row.errors?.length) {
                                    const errors = row.errors.map(e => `<li>${e}</li>`).join('');
                                    rowHtml += `<td><ul class="mb-0">${errors}</ul></td>`;
                                } else {
                                    rowHtml += `<td>-</td>`;
                                }
                                rowHtml += '</tr>';
                                return rowHtml;
                            }).join('');
                        }
                        parsedValidRows = validRows;
                        $('#valid-table-body').html(buildTableRows(validRows, headers));
                        $('#invalid-table-body').html(buildTableRows(invalidRows, headers));
                        $("#submitBtn").removeClass('d-none');
                        window.lastParsedImport = {
                            valid: validRows,
                            invalid: invalidRows,
                            headers: headers
                        };
                        Swal.fire({
                            title: 'Success!',
                            text: response.message || 'File uploaded and parsed successfully.',
                            icon: 'success',
                        });
                    },
                    error: function(xhr) {
                        $('#upload-error').removeClass('d-none').text(xhr.responseJSON?.message || 'Upload failed');
                        $('#uploadProgress').addClass('d-none');
                        $('#uploadProgressBar').removeClass('bg-success').css('width', '0%').text('0%');
                    }
                });
            });


            // $('#submitBtn').on('click', function () {
            //     const validRows = window.lastParsedImport?.valid || [];
            //     const headers = window.lastParsedImport?.headers || {};
            //     const tbody = $('#item_header');
            //     console.log('table',tbody);
            //     tbody.empty(); // Clear existing rows
            //     let currentIndex = tbody.find('tr').length;
            //     console.log('validRows', validRows);
            //     validRows.forEach((row, i) => {
            //         console.log('Processing row:', row);
            //         const index = currentIndex + i;
            //         const itemId = row.item_id || '';
            //         const itemCode = row.item_code || '';
            //         const itemName = row.item_name || '';
            //         const uomId = row.uom_id || '';
            //         const uomName = row.uom_name || '';
            //         const rate = row.rate || 0;
            //         const physicalQty = row.qty || 0;
            //         const remarks = row.remarks || '';
            //         const attributeValue = row.attribute_value || '';
            //         const attributeGroupId = row.attribute_group_id || '';
            //         const itemValue = (rate * physicalQty).toFixed(2);

            //         const rowHtml = `
        //         <tr id="item_row_${index}" class="item_header_rows" onclick="onItemClick('${index}');">
        //             <input type="hidden" id="po_item_id_${index}" name="po_item_id[]" value="">
        //             <td class="customernewsection-form">
        //                 <div class="form-check form-check-primary custom-checkbox">
        //                     <input type="checkbox" class="form-check-input item_row_checks" id="item_checkbox_${index}" del-index="${index}">
        //                     <label class="form-check-label" for="item_checkbox_${index}"></label>
        //                 </div>
        //             </td>
        //             <td class="poprod-decpt">
        //                 <input type="text" id="items_dropdown_${index}" name="item_code[${index}]" placeholder="Select" class="form-control mw-100 ledgerselecct comp_item_code ui-autocomplete-input" autocomplete="off" data-name="${row.item_name ?? ''}" data-code="${row.item_code ?? ''}" data-id="${row.item_id ?? ''}" hsn_code="${row.hsn_code ?? ''}" item-name="${row.item_name ?? ''}" specs='${JSON.stringify(row.specifications ?? [])}' attribute-array='${JSON.stringify(row.item_attribute_array ?? [])}' value="${row.item_code ?? ''}" readonly>
        //                 <input type="hidden" name="item_id[]" id="items_dropdown_${index}_value" value="${row.item_id ?? ''}">
        //             </td>
        //             <td>
        //                 <input type="text" id="items_name_${index}" class="form-control mw-100" value="${itemName}" name="item_name[${index}]" readonly>
        //             </td>
        //             <td id="attribute_section_${index}">
        //                 <button id="attribute_button_${index}" type="button" class="btn p-25 btn-sm btn-outline-secondary"" onclick="setItemAttributes('items_dropdown_${index}', '${index}', false);" data-bs-toggle="modal" data-bs-target="#attribute">Attributes</button>
        //                 <input type="hidden" name="attribute_value_${index}" value="${attributeValue}">
        //             </td>
        //             <td>
        //                 <select class="form-select" name="uom_id[]" id="uom_dropdown_${index}">
        //                     <option value="${uomId}" selected>${uomName}</option>
        //                 </select>
        //             </td>
        //             <td class="numeric-alignment">
        //                 <input type="text" id="item_physical_qty_${index}" value="${physicalQty}" name="item_physical_qty[${index}]" class="form-control mw-100 text-end" oninput="setVariance(this, ${index});setValue(${index});">
        //             </td>
        //             <td class="numeric-alignment">
        //                 <input type="text" id="item_confirmed_qty_${index}" value="0.00" name="item_confirmed_qty[${index}]" class="form-control mw-100 text-end" readonly>
        //             </td>
        //             <td class="numeric-alignment">
        //                 <input type="text" id="item_unconfirmed_qty_${index}" value="0.00" name="item_unconfirmed_qty[${index}]" class="form-control mw-100 text-end" readonly>
        //             </td>
        //             <td class="numeric-alignment">
        //                 <input type="text" id="item_variance_qty_${index}" value="${physicalQty}" name="item_balance_qty[${index}]" class="form-control mw-100 text-end" readonly>
        //             </td>
        //             <td class="numeric-alignment">
        //                 <input type="text" id="item_rate_${index}" value="${rate}" name="item_rate[${index}]" class="form-control mw-100 text-end" oninput="setValue(${index});">
        //             </td>
        //             <td class="numeric-alignment">
        //                 <input type="text" id="item_value_${index}" value="${itemValue}" name="item_value[${index}]" class="form-control mw-100 text-end" readonly>
        //             </td>
        //             <td>
        //                 <div class="d-flex">
        //                     <div class="me-50 cursor-pointer" data-bs-toggle="modal" data-bs-target="#Remarks" onclick="setItemRemarks('item_remarks_${index}');">
        //                         <span data-bs-toggle="tooltip" title="Remarks" class="text-primary"><i data-feather="file-text"></i></span>
        //                     </div>
        //                 </div>
        //                 <input type="hidden" id="item_remarks_${index}" name="item_remarks[${index}]" value="${remarks}">
        //             </td>
        //         </tr>`;
            //         tbody.append(rowHtml);
            //         // setItemAttributes(`items_dropdown_${index}`, index, false);
            //         setAttributesUI(index);
            //         onItemClick(index);
            //         console.log('Row added:', rowHtml);
            //     });
            //     console.log('Parsed valid rows:', parsedValidRows);
            //     renderIcons()
            //     $('#importItemModal').modal('hide');
            // });

            $('#submitBtn').on('click', function() {
                console.log('Submit button clicked');
                const validRows = window.lastParsedImport?.valid || [];
                const headers = window.lastParsedImport?.headers || {};
                const tbody = $('#item_header');

                let currentIndex = tbody.find('tr').length;

                validRows.forEach((row, i) => {
                    console.log('Processing row:', row);
                    const index = currentIndex + i;

                    const itemId = row.item_id || '';
                    const itemCode = row.item_code || '';
                    const itemName = row.item_name || '';
                    const uomId = row.uom_id || '';
                    const uomName = row.uom_name || '';
                    const rate = row.rate || 0;
                    const requiredQty = row.required_qty || 0;
                    const remarks = row.remarks || '';
                    const vendorId = row.vendor || '';
                    const vendorName = row.vendor_name || '';

                    // normalize attributes (can be object or array)
                    let attributes = [];
                    if (Array.isArray(row.item_attribute_array)) {
                        attributes = row.item_attribute_array;
                    } else if (row.item_attribute_array && typeof row.item_attribute_array === 'object') {
                        attributes = [row.item_attribute_array];
                    }

                    const itemValue = (rate * requiredQty).toFixed(2);

                    const attributesHtml = attributes.map(attr => {
                        const groupId = attr.attribute_group_id;
                        const groupName = attr.group_name;
                        const selected = (attr.values_data || []).find(v => v.selected);

                        if (!selected) return ''; // skip if no selected value

                        return `
                        <input type="hidden"
                            name="components[${index}][attr_group_id][${groupId}][${selected.id}][attr_name]"
                            value="${selected.id}"
                            data-attr-group-id="${groupId}"
                            class="comp_attribute">
                    `;
                    }).join('');

                    const rowHtml = `
                    <tr id="row_${index}" data-index="${index}">
                        <td class="customernewsection-form">
                            <div class="form-check form-check-primary custom-checkbox">
                                <input type="checkbox" class="form-check-input" id="Email_${index}" value="${index}" data-id="">
                                <label class="form-check-label" for="Email_${index}"></label>
                            </div>
                        </td>
                        <td class="poprod-decpt">
                            <input type="text" name="component_item_name[${index}]" placeholder="Select" class="form-control mw-100 mb-25 ledgerselecct comp_item_code" value="${itemCode}" />
                            <input type="hidden" name="components[${index}][item_id]" value="${itemId}"/>
                            <input type="hidden" name="components[${index}][item_code]" value="${itemCode}"/>
                            <input type="hidden" name="components[${index}][hsn_id]" value="${row.hsn_id || ''}"/>
                            <input type="hidden" name="components[${index}][hsn_code]" value="${row.hsn_code || ''}"/>
                        </td>
                        <td>
                            <input type="text" name="components[${index}][item_name]" class="form-control mw-100 mb-25" value="${itemName}" readonly/>
                        </td>
                        <td class="poprod-decpt attributeBtn" id="itemAttribute_${index}" data-count="${index}" attribute-array='${JSON.stringify(row.item_attribute_array) || ''}'>
                            <button type="button" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>
                            ${attributesHtml}
                        </td>
                        <td>
                            <select class="form-select mw-100" name="components[${index}][uom_id]">
                                <option value="${uomId}" selected>${uomName}</option>
                            </select>
                        </td>
                        <td>
                            <input readonly type="number" step="any" class="form-control mw-100 text-end" name="components[${index}][qty]" value="${requiredQty}">
                        </td>
                        <td>
                            <input type="number" step="any" class="form-control mw-100 text-end disabled-input" name="components[${index}][avl_stock]" value="">
                        </td>
                        <td>
                            <input type="number" step="any" class="form-control mw-100 text-end disabled-input" name="components[${index}][pending_po]" value="">
                        </td>
                        <td>
                            <input type="number" step="any" class="form-control mw-100 text-end" name="components[${index}][adj_qty]" value="">
                        </td>
                        <td>
                            <input readonly type="number" step="any" class="form-control mw-100 text-end" name="components[${index}][indent_qty]" value="">
                        </td>
                        <td>
                            <input type="text" placeholder="Select" class="form-control mw-100 ledgerselecct" name="components[${index}][vendor_code]" value="${vendorName}" />
                            <input type="hidden" name="components[${index}][vendor_id]" value="${vendorId}" />
                        </td>
                        <td>
                            ${typeof soTrackingRequired !== 'undefined' && soTrackingRequired ? `
                                    <input readonly type="text" name="components[${index}][so_no]" class="form-control mw-100 mb-25" value="${row.so_no || ''}" />
                                ` : ''}
                        </td>
                        <td>
                            <input type="text" name="components[${index}][remark]" class="form-control mw-100 mb-25" value="${remarks}"/>
                        </td>
                    </tr>
                `;
                    tbody.append(rowHtml);

                    // auto-select newly added row & unselect others
                    const newRow = tbody.find(`tr[data-index="${index}"]`);
                    newRow.addClass('trselected').siblings().removeClass('trselected');
                    initializeAutocomplete2('.comp_item_code');
                    initAutocompVendor("[name*='[vendor_code]']");
                    newRow.find(`input[name="components[${index}][qty]"]`).trigger('change');
                    // also trigger the click event if needed
                    newRow.trigger('click');
                    setAttributesUIHelper(index, "#itemTable");

                });
                $("#importItemModal").modal('hide');
            });

            // Cancel button
            $('#cancelBtn').on('click', function() {
                $('#fileUpload').val('');
                $('#fileNameDisplay').hide();
                $('#upload-error').hide();
                $('#proceedBtn').hide();
            });

            // Sample download button
            $('#sampleBtn').on('click', function() {
                $.ajax({
                    url: "{{ route('generic.import.sample.download', ['alias' => 'purchase-indent']) }}",
                    type: "GET",
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(data, status, xhr) {
                        let disposition = xhr.getResponseHeader('Content-Disposition');
                        let filename = "sample_import.xlsx";
                        if (disposition && disposition.indexOf('filename=') !== -1) {
                            let matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                            if (matches?.[1]) {
                                filename = matches[1].replace(/['"]/g, '');
                            }
                        }
                        const blob = new Blob([data], {
                            type: xhr.getResponseHeader('Content-Type')
                        });
                        const link = document.createElement('a');
                        link.href = window.URL.createObjectURL(blob);
                        link.download = filename;
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Failed to download sample file.',
                            icon: 'error',
                        });
                    }
                });
            });

            function handleFileSelected(file) {
                const fileName = file.name;
                const fileSize = file.size;
                const fileExtension = fileName.split('.').pop().toLowerCase();
                const ALLOWED_EXTENSIONS = ['xls', 'xlsx'];
                const MAX_FILE_SIZE = 30 * 1024 * 1024;

                $('#upload-error').hide().html('');

                if (!ALLOWED_EXTENSIONS.includes(fileExtension)) {
                    displayError(`Invalid file type. Allowed: ${ALLOWED_EXTENSIONS.join(', ')}`);
                    $('#fileUpload').val('');
                    return;
                }

                if (fileSize > MAX_FILE_SIZE) {
                    displayError(`File too large. Max allowed size is ${MAX_FILE_SIZE / (1024 * 1024)} MB.`);
                    $('#fileUpload').val('');
                    return;
                }

                $('#selectedFileName').text(fileName);
                $('#fileNameDisplay').removeClass('d-none').show();
                $('#proceedBtn').show();
            }

            function displayError(message) {
                $('#upload-error').html(message).removeClass('d-none').show();
                $('#fileNameDisplay').hide();
                $('#proceedBtn').hide();
            }
        });
    </script>
@endsection
