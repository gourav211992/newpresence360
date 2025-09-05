@extends('layouts.app')

@section('content')
    @php
        $unauthorizedMonths = [];
        foreach ($fy_months as $month) {
            if (!$month['authorized']) {
                $unauthorizedMonths[] = $month['fy_month'];
            }
        }
    @endphp
    <script>
        const unauthorizedMonths = @json($unauthorizedMonths);
    </script>
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Revaluation / Impairement</h2>
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
                            <a href="{{ route('finance.fixed-asset.revaluation-impairement.index') }}"> <button
                                    class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</button>
                            </a>
                            <button class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" type="button" id="save-draft-btn">
                                <i data-feather="save"></i> Save as Draft
                            </button>

                            <button type="submit" form="fixed-asset-revaluation-impairement-form"
                                class="btn btn-primary btn-sm" id="submit-btn">
                                <i data-feather="check-circle"></i> Submit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body">



                <section id="basic-datatable">
                    <div class="row">
                        <form id="fixed-asset-revaluation-impairement-form" method="POST"
                            action="{{ route('finance.fixed-asset.revaluation-impairement.store') }}"
                            enctype="multipart/form-data">

                            @csrf
                            <input type="hidden" name="sub_assets" id="sub_assets">
                            <input type="hidden" name="asset_details" id="asset_details">
                            <input type="hidden" name="doc_number_type" id="doc_number_type">
                            <input type="hidden" name="doc_reset_pattern" id="doc_reset_pattern">
                            <input type="hidden" name="doc_prefix" id="doc_prefix">
                            <input type="hidden" name="doc_suffix" id="doc_suffix">
                            <input type="hidden" name="doc_no" id="doc_no">
                            <input type="hidden" name="document_status" id="document_status" value="">
                            <input type="hidden" name="dep_type" id="depreciation_type" value="{{ $dep_type }}">
                            <div class="col-12">


                                <div class="card">
                                    <div class="card-body customernewsection-form">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="newheader border-bottom mb-2 pb-25  ">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <h4 class="card-title text-theme">Basic Information</h4>
                                                            <p class="card-text">Fill the details</p>
                                                        </div>


                                                        <div class="col-md-6 text-sm-end" hidden>
                                                            <span
                                                                class="badge rounded-pill badge-light-secondary forminnerstatus">
                                                                Status : <span class="text-success">Approved</span>
                                                            </span>
                                                        </div>

                                                    </div>
                                                </div>

                                            </div>




                                            <div class="col-md-8">
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Type <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-8">
                                                        <div class="demo-inline-spacing">
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Revaluation" name="document_type"
                                                                    value="revaluation" class="form-check-input" checked>
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Revaluation">Revaluation</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Impairement" name="document_type"
                                                                    value="impairement" class="form-check-input">
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Impairement">Impairement</label>
                                                            </div>
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="Writeoff" name="document_type"
                                                                    value="writeoff" class="form-check-input">
                                                                <label class="form-check-label fw-bolder"
                                                                    for="Writeoff">Writeoff</label>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="book_id">Series <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select" id="book_id" name="book_id"
                                                            required>
                                                            @foreach ($series as $book)
                                                                <option value="{{ $book->id }}">{{ $book->book_code }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="document_number">Doc No <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" class="form-control" id="document_number"
                                                            name="document_number" required>
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label" for="document_date">Doc Date <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="date" class="form-control" id="document_date"
                                                            name="document_date" value="{{ date('Y-m-d') }}" required>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Category <span
                                                                class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select class="form-select select2" name="category_id"
                                                            id="category" required>
                                                            @foreach ($categories as $category)
                                                                <option value="{{ $category->id }}"
                                                                    {{ old('category') == $category->id ? 'selected' : '' }}>
                                                                    {{ $category->name }}
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
                                                        <select id="location" class="form-select" name="location_id"
                                                            required>
                                                            @foreach ($locations as $location)
                                                                <option value="{{ $location->id }}">
                                                                    {{ $location->store_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                </div>
                                                <div class="row align-items-center mb-1 cost_center">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Cost Center <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <select id="cost_center" class="form-select"
                                                            name="cost_center_id" required>
                                                        </select>
                                                    </div>

                                                </div>


                                            </div>


                                            <div class="col-md-4">

                                                {{-- History Code --}}

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
                                                        <h4 class="card-title text-theme">Select Assets</h4>
                                                        <p class="card-text">Fill the details</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-sm-end">
                                                    <a href="#" class="btn btn-sm btn-outline-danger me-50"
                                                        id="delete">
                                                        <i data-feather="x-circle"></i> Delete</a>
                                                    <a id="addNewRowBtn" class="btn btn-sm btn-outline-primary">
                                                        <i data-feather="plus"></i> Add New</a>
                                                </div>
                                            </div>
                                        </div>





                                        <div class="row">

                                            <div class="col-md-12">


                                                <div class="table-responsive pomrnheadtffotsticky">
                                                    <table
                                                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                        <thead>
                                                            <tr>
                                                                <th class="customernewsection-form">
                                                                    <div
                                                                        class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input"
                                                                            id="checkAll">
                                                                        <label class="form-check-label"
                                                                            for="Email"></label>
                                                                    </div>
                                                                </th>
                                                                <th width="200px">Asset Name & Code</th>
                                                                <th width="500px">Sub Assets & Code</th>
                                                                <th width="100px">Quantity</th>
                                                                <th class="text-end">Current Value</th>
                                                                <th width="200px">Last Dep. Date</th>
                                                                <th class="text-end"><span
                                                                        id="selectedRadioText">Revaluation</span> Amount
                                                                </th>

                                                            </tr>
                                                        </thead>
                                                        <tbody class="mrntableselectexcel">
                                                            <tr>
                                                                <td class="customernewsection-form">
                                                                    <div
                                                                        class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox"
                                                                            class="form-check-input row-check"
                                                                            id="Email">
                                                                        <label class="form-check-label"
                                                                            for="Email"></label>
                                                                    </div>
                                                                </td>
                                                                <td class="poprod-decpt">
                                                                    <input type="text" required
                                                                        class="form-control asset-search-input mw-100" />
                                                                    <input type="hidden" name="asset_id[]"
                                                                        class="asset_id" data-id="1"
                                                                        id="asset_id_1" />

                                                                </td>

                                                                <td class="poprod-decpt">
                                                                    <input type="text" required
                                                                        class="form-control subasset-search-input mw-100" />
                                                                    <input type="hidden" name="sub_asset_id[]"
                                                                        class="sub_asset_id" data-id="1"
                                                                        id="sub_asset_id_1" />
                                                                </td>
                                                                <td><input type="number" name="quantity[]"
                                                                        id="quantity_1" readonly data-id="1"
                                                                        class="form-control mw-100 quantity" /></td>
                                                                <td class="text-end"><input type="text"
                                                                        name="currentvalue[]" id="currentvalue_1"
                                                                        data-id="1"
                                                                        class="form-control mw-100 text-end currentvalue"
                                                                        readonly />
                                                                    <input type="hidden" class="salvage" />
                                                                </td>

                                                                <td><input type="date" name="last_dep_date[]"
                                                                        id="last_dep_date_1" data-id="1"
                                                                        class="form-control mw-100 last_dep_date"
                                                                        readonly />
                                                                </td>
                                                                <td><input type="number" step="2" required
                                                                        name="revaluate_amount[]" id="revaluate_amount_1"
                                                                        data-id="1"
                                                                        class="form-control mw-100 text-end revaluate_amount" />
                                                                </td>
                                                            </tr>



                                                        </tbody>


                                                    </table>
                                                </div>
                                            </div>

                                        </div>
                                        <div class="row mt-2">

                                            <div class="col-md-4">
                                                <label class="form-label">Document</label>

                                                <div class="d-flex align-items-center gap-2">
                                                    {{-- File input --}}
                                                    <input type="file" name="document" class="form-control"
                                                        id="documentInput" style="max-width: 85%;" />

                                                    {{-- Preview selected file or existing one --}}
                                                    <div id="filePreview">
                                                        @if (!empty($data->document))
                                                            {{-- Existing file icon --}}
                                                            <div id="existingFilePreview">
                                                                <a href="{{ asset('documents/' . $data->document) }}"
                                                                    target="_blank">
                                                                    <i data-feather="file-text" class="text-success"></i>
                                                                </a>
                                                            </div>
                                                        @endif

                                                        {{-- New file preview icon --}}
                                                        <div id="newFilePreview" style="display: none;">
                                                            <i data-feather="file" class="text-primary"></i>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">Final Remarks</label>
                                                    <textarea type="text" rows="4" name="remarks" class="form-control" placeholder="Enter Remarks here..."></textarea>

                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>




                        </form>


                    </div>
            </div>
            <!-- Modal to add new record -->

            </section>


        </div>
    </div>
    </div>
    <!-- END: Content-->

    <div class="sidenav-overlay"></div>
    <div class="drag-target"></div>



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
                    <p>Are you sure you want to <strong>Amendment</strong> this <strong>MRN</strong>? After Amendment
                        this action cannot be undone.</p>
                    <button type="button" class="btn btn-secondary me-25" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Confirm</button>
                </div>
            </div>
        </div>
    </div>
@endsection




@section('scripts')
<script>
            document.getElementById('document_date').addEventListener('input', function() {
                if (!isDateAuthorized(this.value)) {
                    this.value = '';
                    this.focus();
                }
            });

            function getMonthName(ym) {
                // ym = '2024-07'
                const [year, month] = ym.split('-');
                const d = new Date(year, parseInt(month) - 1);
                return d.toLocaleString('default', {
                    month: 'long',
                    year: 'numeric'
                });
            }

            function isDateAuthorized(dateValue) {
                if (!dateValue) return true; // allow empty, you can tweak this logic if needed
                var selectedMonth = dateValue.substring(0, 7);
                if (unauthorizedMonths.includes(selectedMonth)) {
                    var monthLabel = getMonthName(selectedMonth);

                    Swal.fire({
                        icon: 'error',
                        title: 'Unauthorized Month',
                        text: 'You are not authorized to select dates from ' + monthLabel +
                            '. Please select another month.',
                        confirmButtonText: 'OK'
                    });

                    return false;
                }
                return true;
            }
    </script>
    <script>
        $(window).on('load', function() {
            if (feather) {
                feather.replace({
                    width: 14,
                    height: 14
                });
            }
        })



        $(document).on('click', '.mrntableselectexcel tr', function() {
            $(this).addClass('trselected').siblings().removeClass('trselected');
        });

        // Keyboard navigation for up/down arrow keys
        $(document).on('keydown', function(e) {
            var $selected = $('.trselected');

            if (e.which === 38) { // Up arrow
                $selected.prev('tr').addClass('trselected').siblings().removeClass('trselected');
            } else if (e.which === 40) { // Down arrow
                $selected.next('tr').addClass('trselected').siblings().removeClass('trselected');
            }

            // Scroll to the selected row inside scrollable container
            var $container = $('.mrntableselectexcel');
            var $newSelected = $('.trselected');

            if ($newSelected.length && $container.length && $newSelected.offset()) {
                var containerOffset = $container.offset().top;
                var selectedOffset = $newSelected.offset().top;
                $container.scrollTop($container.scrollTop() + (selectedOffset - containerOffset - 40));
            }
        });


        $('#add_new_sub_asset').on('click', function() {
            const subAssetCode = $('#sub_asset_id').val();
            genereateSubAssetRow(subAssetCode);
        });


        function resetParametersDependentElements(data) {
            let backDateAllowed = false;
            let futureDateAllowed = false;

            if (data != null) {
                console.log(data.parameters.back_date_allowed);
                if (Array.isArray(data?.parameters?.back_date_allowed)) {
                    for (let i = 0; i < data.parameters.back_date_allowed.length; i++) {
                        if (data.parameters.back_date_allowed[i].trim().toLowerCase() === "yes") {
                            backDateAllowed = true;
                            break; // Exit the loop once we find "yes"
                        }
                    }
                }
                if (Array.isArray(data?.parameters?.future_date_allowed)) {
                    for (let i = 0; i < data.parameters.future_date_allowed.length; i++) {
                        if (data.parameters.future_date_allowed[i].trim().toLowerCase() === "yes") {
                            futureDateAllowed = true;
                            break; // Exit the loop once we find "yes"
                        }
                    }
                }
                //console.log(backDateAllowed, futureDateAllowed);

            }

            const dateInput = document.getElementById("document_date");

            // Determine the max and min values for the date input
            const today = moment().format("YYYY-MM-DD");

            if (backDateAllowed && futureDateAllowed) {
                dateInput.setAttribute("min", "{{ $financialStartDate }}");
                dateInput.setAttribute("max", "{{ $financialEndDate }}");
            } else if (backDateAllowed) {
                dateInput.setAttribute("max", today);
                dateInput.setAttribute("min", "{{ $financialStartDate }}");
            } else if (futureDateAllowed) {
                dateInput.setAttribute("min", today);
                dateInput.setAttribute("max", "{{ $financialEndDate }}");
            } else {
                dateInput.setAttribute("min", today);
                dateInput.setAttribute("max", today);

            }
        }

        $('#book_id').on('change', function() {
            resetParametersDependentElements(null);
            let currentDate = new Date().toISOString().split('T')[0];
            let document_date = $('#document_date').val();
            let bookId = $('#book_id').val();
            let actionUrl = '{{ route('book.get.doc_no_and_parameters') }}' + '?book_id=' + bookId +
                "&document_date=" + document_date;
            fetch(actionUrl).then(response => {
                return response.json().then(data => {
                    if (data.status == 200) {
                        resetParametersDependentElements(data.data);
                        $("#book_code_input").val(data.data.book_code);
                        if (!data.data.doc.document_number) {
                            $("#document_number").val('');
                            $('#doc_number_type').val('');
                            $('#doc_reset_pattern').val('');
                            $('#doc_prefix').val('');
                            $('#doc_suffix').val('');
                            $('#doc_no').val('');
                        } else {
                            $("#document_number").val(data.data.doc.document_number);
                            $('#doc_number_type').val(data.data.doc.type);
                            $('#doc_reset_pattern').val(data.data.doc.reset_pattern);
                            $('#doc_prefix').val(data.data.doc.prefix);
                            $('#doc_suffix').val(data.data.doc.suffix);
                            $('#doc_no').val(data.data.doc.doc_no);
                        }
                        if (data.data.doc.type == 'Manually') {
                            $("#document_number").attr('readonly', false);
                        } else {
                            $("#document_number").attr('readonly', true);
                        }

                    }
                    if (data.status == 404) {
                        $("#document_number").val('');
                        $('#doc_number_type').val('');
                        $('#doc_reset_pattern').val('');
                        $('#doc_prefix').val('');
                        $('#doc_suffix').val('');
                        $('#doc_no').val('');
                        showToast('error', data.message);
                    }
                });
            });
        });
        $('#book_id').trigger('change');
        document.getElementById('save-draft-btn').addEventListener('click', function() {
            $('.preloader').show();
            document.getElementById('document_status').value = 'draft';
            updateJsonData();
            if (validateRevaluationAmounts()) {
                document.getElementById('fixed-asset-revaluation-impairement-form').submit();
            }
        });


        $('#fixed-asset-revaluation-impairement-form').on('submit', function(e) {
            $('.preloader').show();
            document.getElementById('document_status').value = 'submitted';
            e.preventDefault(); // Always prevent default first
            updateJsonData();
            if (validateRevaluationAmounts()) {
                this.submit();
            }
        });

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

        function initializeAssetAutocomplete(selector) {
            $(selector).autocomplete({
                source: function(request, response) {
                    const category = $('#category').val();

                    if (!category) {
                        response([]); // Return an empty list to autocomplete
                        return;
                    }

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{ route('finance.fixed-asset.asset-search') }}',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            q: request.term,
                            ids: getAllAssetIds(),
                            location_id: $('#location').val(),
                            cost_center_id: $('#cost_center').val(),
                            category: category,
                        },
                        success: function(data) {
                            response(data.map(function(item) {
                                return {
                                    label: item.asset_code + ' (' + item.asset_name +
                                        ')',
                                    value: item.id,
                                    asset: item
                                };
                            }));
                        },
                        error: function() {
                            response([]);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    const row = $(this).closest('tr');

                    row.find('.sub_asset_id').val();
                    row.find('.subasset-search-input').val('');
                    row.find('.quantity').val('');
                    row.find('.salvage').val('');
                    row.find('.currentvalue').val('');
                    row.find('.last_dep_date').val('');

                    const asset = ui.item.asset;
                    const rowId = row.data('id'); // assuming you set `data-id` on the <tr>

                    // Set visible label and hidden ID
                    $(this).val(ui.item.label);
                    row.find('.asset_id').val(ui.item.value);
                    updateSelectedRadioLabel();

                    return false;
                },
                change: function(event, ui) {
                    const row = $(this).closest('tr');
                    if (!ui.item) {

                        $(this).val('');
                        row.find('.asset_id').val('');

                        row.find('.sub_asset_id').val();
                        row.find('.subasset-search-input').val('');
                        row.find('.quantity').val('');
                        row.find('.salvage').val('');
                        row.find('.currentvalue').val('');
                        row.find('.last_dep_date').val('');
                        refreshAssetSelects();
                    }
                }
            }).focus(function() {
                if (this.value === '') {
                    $(this).autocomplete('search');
                }
            });
        }

        function initializeSubAssetAutocomplete(selector) {
            $(selector).autocomplete({
                source: function(request, response) {
                    let row = $(this.element).closest('tr');
                    let assetId = row.find('.asset_id').val();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{ route('finance.fixed-asset.sub_asset_search') }}',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id: assetId,
                            q: request.term
                        },
                        success: function(data) {
                            response(data.map(function(item) {
                                return {
                                    label: item.sub_asset_code,
                                    value: item.id,
                                    asset: item.asset,
                                    sub_asset: item
                                };
                            }));
                        },
                        error: function() {
                            response([]);
                        }
                    });
                },
                minLength: 0,
                select: function(event, ui) {
                    let row = $(this).closest('tr');
                    let subAssetId = row.find('.sub_asset_id');
                    let lastdep = row.find('.last_dep_date');

                    const asset = ui.item.asset;
                    const sub_asset = ui.item.sub_asset;

                    $(this).val(ui.item.label);
                    subAssetId.val(ui.item.value);
                    row.find('.quantity').val('');
                    row.find('.salvage').val('');
                    row.find('.currentvalue').val('');
                    row.find('.last_dep_date').val('');
                    row.find('.revaluate_amount').val('');

                    if (asset.last_dep_date !== asset.capitalize_date) {
                        let lastDepDate = new Date(asset.last_dep_date);
                        lastDepDate.setDate(lastDepDate.getDate() - 1);
                        let formattedDate = lastDepDate.toISOString().split('T')[0];
                        lastdep.val(formattedDate);
                    }
                    row.find('.quantity').val(1);
                    row.find('.currentvalue').val(sub_asset.current_value_after_dep);
                    row.find('.salvage').val(sub_asset.salvage_value);
                    updateSelectedRadioLabel();

                    return false;
                },
                change: function(event, ui) {
                    let row = $(this).closest('tr');
                    let subAssetId = row.find('.sub_asset_id');
                    let lastdep = row.find('.last_dep_date');

                    if (!ui.item) {
                        $(this).val('');
                        row.find('.sub_asset_id').val();
                        row.find('.subasset-search-input').val('');
                        row.find('.quantity').val('');
                        row.find('.salvage').val('');
                        row.find('.currentvalue').val('');
                        row.find('.last_dep_date').val('');
                        row.find('.revaluate_amount').val('');
                        row.find('.salvage').val('');
                    }
                },
                focus: function() {
                    return false;
                }
            }).focus(function() {
                if (this.value === '') {
                    $(this).autocomplete('search');
                }
            });
        }



        initializeAssetAutocomplete('.asset-search-input');
        initializeSubAssetAutocomplete('.subasset-search-input');

        $('.select2').select2();



        let rowCount = 1;

        $('#addNewRowBtn').on('click', function() {
            rowCount++;
            let newRow = `
    <tr>
        <td class="customernewsection-form">
            <div class="form-check form-check-primary custom-checkbox">
                <input type="checkbox" class="form-check-input row-check" id="Email_${rowCount}">
                <label class="form-check-label" for="Email_${rowCount}"></label>
            </div>
        </td>
        <td class="poprod-decpt">   
            <input type="text" class="form-control asset-search-input mw-100" required />
            <input type="hidden" name="asset_id[]" class="asset_id" data-id="${rowCount}" id="asset_id_${rowCount}"/> 
         </td>
        <td class="poprod-decpt">
            <input type="text" required class="form-control subasset-search-input mw-100"/>
            <input type="hidden" name="sub_asset_id[]" class="sub_asset_id" data-id="${rowCount}" id="sub_asset_id_${rowCount}"/> 
        </td>
        <td><input type="number" name="quantity[]" id="quantity_${rowCount}" readonly data-id="${rowCount}"
            class="form-control mw-100 quantity" /></td>
        <td><input type="text" name="currentvalue[]" id="currentvalue_${rowCount}" data-id="${rowCount}"
            class="form-control mw-100 text-end currentvalue" readonly />
             <input type="hidden" class="salvage"/>
             </td>
          
        <td><input type="date" name="last_dep_date[]" id="last_dep_date_${rowCount}" data-id="${rowCount}"
            class="form-control mw-100 last_dep_date" readonly /></td>
             <td><input type="number" step="2" required name="revaluate_amount[]" id="revaluate_amount_${rowCount}" data-id="${rowCount}"
            class="form-control mw-100 text-end revaluate_amount"/></td>
    </tr>
    `;

            $('.mrntableselectexcel').append(newRow);
            $(".select2").select2();
            refreshAssetSelects();
            initializeAssetAutocomplete('.asset-search-input');
            initializeSubAssetAutocomplete('.subasset-search-input');
        });

        function refreshAssetSelects() {
            let selectedAssets = [];

            // Collect all selected asset values
            $('.asset_id').each(function() {
                let val = $(this).val();
                if (val) {
                    selectedAssets.push(val);
                }
            });

            // Disable already selected options in other selects
            $('.asset_id').each(function() {
                let currentSelect = $(this);
                let currentVal = currentSelect.val();
                currentSelect.find('option').each(function() {
                    let optionVal = $(this).val();
                    if (optionVal === "") return; // skip placeholder
                    if (selectedAssets.includes(optionVal) && optionVal !== currentVal) {
                        $(this).prop('disabled', true);
                    } else {
                        $(this).prop('disabled', false);
                    }
                });
            });

        }

        $('#delete').on('click', function() {
            let $rows = $('.mrntableselectexcel tr');
            let $checked = $rows.find('.row-check:checked');

            // Prevent deletion if only one row exists
            if ($rows.length <= 1) {
                showToast('error', 'At least one row is required.');
                return;
            }

            // Prevent deletion if checked rows would remove all
            if ($rows.length - $checked.length < 1) {
                showToast('error', 'You must keep at least one row.');
                return;
            }

            // Remove only the checked rows
            $checked.closest('tr').remove();

        });
        $('#checkAll').on('change', function() {
            let isChecked = $(this).is(':checked');
            $('.mrntableselectexcel .row-check').prop('checked', isChecked);
        });

        $('#location').on('change', function() {
            var locationId = $(this).val();
            var selectedCostCenterId = '{{ $data->cost_center_id ?? '' }}';

            if (locationId) {
                // Build the route manually
                var url = '{{ route('finance.fixed-asset.get-cost-centers') }}';

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: {
                        location_id: locationId,
                        category_id: $('#category').val(),
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.length == 0) {
                            $('#cost_center').empty();
                            $('#cost_center').prop('required', false);
                            $('.cost_center').hide();
                            // loadCategories();
                        } else {
                            $('.cost_center').show();
                            $('#cost_center').prop('required', true);
                            $('#cost_center').empty(); // Clear previous options
                            $.each(data, function(key, value) {
                                let selected = (value.id == selectedCostCenterId) ? 'selected' :
                                    '';
                                $('#cost_center').append('<option value="' + value.id + '" ' +
                                    selected + '>' + value.name + '</option>');
                            });
                            $('#cost_center').trigger('change');
                        }
                    },
                    error: function() {
                        $('#cost_center').empty();
                    }
                });
            } else {
                $('#cost_center').empty();
            }
        });


        function getAllAssetIds() {
            let assetIds = [];

            $('.asset_id').each(function() {
                let val = $(this).val();
                if (val) {
                    assetIds.push(parseFloat(val));
                }
            });

            return assetIds;
        }

        function updateSelectedRadioLabel() {
            const selected = document.querySelector('input[name="document_type"]:checked');
            if (selected) {
                const label = document.querySelector(`label[for="${selected.id}"]`);
                if (label) {
                    document.getElementById("selectedRadioText").textContent = label.textContent.trim();
                    if (label.textContent.trim() == "Writeoff") {
                        $('.revaluate_amount').each(function() {
                            $(this).val(0).prop('readonly', true);
                        });
                    } else {
                        $('.revaluate_amount').each(function() {
                            $(this).val(0).prop('readonly', false);
                        });
                    }
                }
            }
        }

        // On radio change
        document.querySelectorAll('input[name="document_type"]').forEach(radio => {
            radio.addEventListener('change', updateSelectedRadioLabel);
        });

        // Initial update on page load
        document.addEventListener("DOMContentLoaded", updateSelectedRadioLabel);

        function getSelectedDocumentType() {
            const selected = document.querySelector('input[name="document_type"]:checked');
            return selected ? selected.value : null;
        }

        function validateRevaluationAmounts(showErrors = true) {
            const documentType = getSelectedDocumentType();
            let isValid = true;

            document.querySelectorAll('.revaluate_amount').forEach(input => {
                const row = input.closest('tr');
                const currentValueInput = row.querySelector('.currentvalue');
                const salvageValueInput = row.querySelector('.salvage');


                if (currentValueInput.value.trim() === "" && input.value.trim() === "" && salvageValueInput.value
                    .trim() === "") return;

                const currentVal = parseFloat(currentValueInput.value) || 0;
                const revalVal = parseFloat(input.value) || 0;
                const salVal = parseFloat(salvageValueInput.value) || 0;


                if (documentType === 'revaluation' && revalVal <= currentVal) {
                    isValid = false;

                } else if (documentType === 'impairement' && (revalVal >= currentVal || revalVal <= salVal)) {
                    isValid = false;
                }
                else if (documentType === 'writeoff' && (revalVal != 0)) {
                    isValid = false;
                }
            });
            if (!isValid) {
                $('.preloader').hide();
                if (documentType === 'revaluation')
                    showToast('error', 'Revaluation amount must be greater than current value.');
                else if (documentType === 'revaluation')
                    showToast('error', 'Impairement amount must be less than current value and grater than salvage value.');
                  else if (documentType === 'Writeoff')
                    showToast('error', 'WriteOff amount must be 0.');
                  
            }

            return isValid;
        }


        function updateJsonData() {
            const allRows = [];

            $('.mrntableselectexcel tr').each(function() {
                const row = $(this);
                const rowData = {
                    asset_id: row.find('.asset_id').val(),
                    asset_code: row.find('.asset-search-input').val(), // assuming it's a text input
                    sub_asset_id: row.find('.sub_asset_id').val(), // array if select2 multi-select
                    sub_asset_code: row.find('.subasset-search-input').val(),
                    quantity: row.find('.quantity').val(),
                    salvage: row.find('.salvage').val(),
                    currentvalue: row.find('.currentvalue').val(),
                    revaluate: row.find('.revaluate_amount').val(),
                    last_dep_date: row.find('.last_dep_date').val(),
                };

                allRows.push(rowData);
            });

            $('#asset_details').val(JSON.stringify(allRows));
        }
        $('#category').on('change', function() {
            loadLocation();
            add_blank();
        });

        function add_blank() {
            $('.mrntableselectexcel').empty();
            let blank_row = `<tr class="trselected" data-id="${rowCount}">
        <td class="customernewsection-form">
            <div class="form-check form-check-primary custom-checkbox">
                <input type="checkbox" class="form-check-input row-check" id="Email_${rowCount}">
                <label class="form-check-label" for="Email_${rowCount}"></label>
            </div>
        </td>
        <td class="poprod-decpt">   
            <input type="text" class="form-control asset-search-input mw-100" required />
            <input type="hidden" name="asset_id[]" class="asset_id" data-id="${rowCount}" id="asset_id_${rowCount}"/> 
         </td>
        <td class="poprod-decpt">
            <input type="text" required class="form-control subasset-search-input mw-100"/>
            <input type="hidden" name="sub_asset_id[]" class="sub_asset_id" data-id="${rowCount}" id="sub_asset_id_${rowCount}"/> 
        </td>
        <td><input type="number" name="quantity[]" id="quantity_${rowCount}" readonly data-id="${rowCount}"
            class="form-control mw-100 quantity" /></td>
        <td><input type="text" name="currentvalue[]" id="currentvalue_${rowCount}" data-id="${rowCount}"
            class="form-control mw-100 text-end currentvalue" readonly />
             <input type="hidden" class="salvage"/>
            </td>
  
        <td><input type="date" name="last_dep_date[]" id="last_dep_date_${rowCount}" data-id="${rowCount}"
            class="form-control mw-100 last_dep_date" readonly /></td>
             <td><input type="number" step="2" required name="revaluate_amount[]" id="revaluate_amount_${rowCount}" data-id="${rowCount}"
            class="form-control mw-100 text-end revaluate_amount"/></td>
    </tr>`;
            $('.mrntableselectexcel').append(blank_row);
            initializeAssetAutocomplete('.asset-search-input');
            initializeSubAssetAutocomplete('.subasset-search-input');

        }
        $(document).on('change', '.revaluate_amount', function() {
            validateRevaluationAmounts();
        });
        $(document).on('change', '[name="document_type"]', function() {
            validateRevaluationAmounts();
        });
        $(document).on('change', '.currentvalue', function() {
            validateRevaluationAmounts();
        });
        document.getElementById('documentInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const newPreview = document.getElementById('newFilePreview');
            const existingPreview = document.getElementById('existingFilePreview');

            if (file) {
                newPreview.style.display = 'block';
                if (existingPreview) existingPreview.style.display = 'none';
            } else {
                newPreview.style.display = 'none';
                if (existingPreview) existingPreview.style.display = 'block';
            }

            feather.replace(); // Update feather icons dynamically
        });

        // Initialize feather icons on load
        feather.replace();

        function loadLocation(selectlocation = null) {
            $('#cost_center').empty();
            $('#cost_center').prop('required', false);
            $('.cost_center').hide();
            if (!$('#category').val()) {
                return;
            }
            const url = '{{ route('finance.fixed-asset.get-locations') }}';

            $.ajax({
                url: url,
                type: 'GET',
                data: {
                    category_id: $('#category').val(),
                },
                dataType: 'json',
                success: function(data) {
                    const $category = $('#location');
                    $category.empty();

                    $.each(data, function(key, value) {
                        const isSelected = selectlocation == value.id ? ' selected' : '';
                        $category.append('<option value="' + value.id + '"' + isSelected + '>' + value
                            .name + '</option>');
                    });
                    $('#location').trigger('change');
                },
                error: function() {
                    $('#location').empty();
                }
            });
        }
        loadLocation();
    </script>
    <!-- END: Content-->
@endsection
