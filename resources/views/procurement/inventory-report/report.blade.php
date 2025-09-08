@extends('layouts.app')
@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-body">
                {{-- <div id="message-area">
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
                </div> --}}
                <section id="basic-datatable">
                    <div class="card border  overflow-hidden">
                        <div class="row">
                            <div class="col-md-12 bg-light border-bottom mb-1 po-reportfileterBox">
                                <div class="row pofilterhead action-button align-items-center">
                                    <div class="col-md-4">
                                        <h3>Inventory Report</h3>
                                        <p>Apply the Filter</p>
                                    </div>
                                    <div
                                        class="col-md-8 text-sm-end pofilterboxcenter mb-0 d-flex flex-wrap align-items-center justify-content-sm-end">
                                        <button data-bs-toggle="modal" data-bs-target="#addcoulmn"
                                            class="btn btn-primary btn-sm mb-0 waves-effect"><i data-feather="filter"></i>
                                            Advance Filters</button>
                                    </div>
                                </div>
                                <div class="customernewsection-form poreportlistview p-1">
                                    <div class="row">
                                        <div class="col-md-2">
                                            <input type="text" placeholder="Select Item"
                                            class="form-control mw-100 ledgerselecct inventory_items" id="item"
                                            name="item" />
                                        </div>
                                        <div class="col-md-1" style="display:none;">
                                            <div class="mb-1 mb-sm-0">
                                                <button type="button" class="btn btn-primary btn-md mb-0 waves-effect attributeBtn" style="background:#fff !important;border:1px solid #6e6b7b59 !important;color:black  !important;">Attributes</button>
                                            </div>
                                        </div>
                                        <div class="col-md-2 location_id" style="display:none;">
                                            <div class="mb-1 mb-sm-0">
                                                <select class="form-select mw-100 select2 store_code" name="location_id" id="location_id">
                                                    <option value="">Select Location</option>
                                                    @foreach($erpStores as $val)
                                                        <option value="{{$val->id}}">
                                                            {{$val->store_name}}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2 store_id" style="display:none;">
                                            <div class="mb-1 mb-sm-0">
                                                <select class="form-select mw-100 select2 rack_code" name="store_id" id="store_id">
                                                    <option value="">Select Store</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2 station_id" style="display:none;">
                                            <div class="mb-1 mb-sm-0">
                                                <select class="form-select mw-100 select2 station_id" name="station_id" id="station_id">
                                                    <option value="">Select Station</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2 stock_type" style="display:none;">
                                            <div class="mb-1 mb-sm-0">
                                                <select class="form-select mw-100 select2 stock_type" name="stock_type" id="stock_type">
                                                    <option value="R">Regular</option>
                                                    <option value="W">WIP</option>
                                                    <option value="S">Sub Standard</option>
                                                    <option value="J">Rejected</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-1 bin_id" style="display:none;">
                                            <div class="mb-1 mb-sm-0">
                                                <select class="form-select mw-100 select2 bin_code" name="bin_id" id="bin_id">
                                                    <option value="">Select Bin</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="mb-1 mb-sm-0">
                                                <a href="/inventory-reports" type="button" class="btn btn-warning btn-md mb-0 waves-effect">Clear</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12" style="min-height: 300px">
                                <div class="table-responsive trailbalnewdesfinance po-reportnewdesign my-class">
                                    <table class="my-table datatables-basic table myrequesttablecbox">
                                        <thead>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </section>
                <!-- ChartJS section end -->

            </div>
        </div>
    </div>
    <!-- END: Content-->
    <!-- Advance Filter Modal   -->
    <div class="modal fade text-start filterpopuplabel " id="addcoulmn" tabindex="-1" aria-labelledby="myModalLabel17"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Advance
                            Filter</h4>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="step-custhomapp bg-light">
                        <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" data-bs-toggle="tab" href="#Employee" role="tab"><i
                                        data-feather="columns"></i> Columns</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#Bank" role="tab"><i
                                        data-feather="bar-chart"></i> More Filter</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#Location" role="tab"><i
                                        data-feather="calendar"></i> Scheduler</a>
                            </li>
                        </ul>
                    </div>
                    <div class="tab-content tablecomponentreport">
                        <div class="tab-pane active" id="Employee">
                            <div class="compoenentboxreport">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-check form-check-primary">
                                            <input type="checkbox" class="form-check-input" id="selectAll"
                                                checked="">
                                            <label class="form-check-label" for="selectAll">Select All Columns</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row sortable">
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="item" checked="">
                                            <label class="form-check-label" for="item">Item</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="item-code" checked="">
                                            <label class="form-check-label" for="item-code">Item Code</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="attributes" checked="">
                                            <label class="form-check-label" for="attributes">Attributes</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input autoTriggerChangeApply" id="store" checked="">
                                            <label class="form-check-label" for="store">Location</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input autoTriggerChangeApply" id="sub_location" checked="">
                                            <label class="form-check-label" for="sub_location">Store</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input autoTriggerChangeApply" id="station" checked="">
                                            <label class="form-check-label" for="station">Station</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="uom" checked="">
                                            <label class="form-check-label" for="uom">UOM</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input autoTriggerChangeApply" id="stock_types" checked="">
                                            <label class="form-check-label" for="stock_types">Stock Type</label>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="confirmed-stock"
                                                checked="">
                                            <label class="form-check-label" for="confirmed-stock">Confirmed Stock</label>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-check form-check-secondary">
                                            <input type="checkbox" class="form-check-input" id="unconfirmed-stock"
                                                checked="">
                                            <label class="form-check-label" for="unconfirmed-stock">Unconfirmed Stock</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="compoenentboxreport" style="margin-top:2%;">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-check form-check-primary">
                                            <input type="checkbox" onchange="checkAgingInputs()" class="form-check-input" id="selectAllInputAging">
                                            <label class="form-check-label" for="selectAllInputAging">Aging</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row sortable">
                                    <!-- New input fields for days -->
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" class="form-control aging-input" onchange="checkAgingInputs()" id="day1" name="days[]" value="30" min="0" placeholder="30 Days" disabled>
                                            <input type="hidden" class="form-control aging-visibility" id="day1_visibility" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" class="form-control aging-input" onchange="checkAgingInputs()" id="day2" name="days[]" value="60" min="0" placeholder="60 Days" disabled>
                                            <input type="hidden" class="form-control aging-visibility" id="day2_visibility" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" class="form-control aging-input" onchange="checkAgingInputs()" id="day3" name="days[]" value="90" min="0" placeholder="90 Days" disabled>
                                            <input type="hidden" class="form-control aging-visibility" id="day3_visibility" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" class="form-control aging-input" onchange="checkAgingInputs()" id="day4" name="days[]" value="120" min="0" placeholder="120 Days" disabled>
                                            <input type="hidden" class="form-control aging-visibility" id="day4_visibility" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <input type="number" class="form-control aging-input" onchange="checkAgingInputs()" id="day5" name="days[]" value="180" min="0" placeholder="180 Days" disabled>
                                            <input type="hidden" class="form-control aging-visibility" id="day5_visibility" value="0">
                                            <input type="hidden" class="form-control aging-visibility" id="day6_visibility" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="Bank">
                            <div class="compoenentboxreport advanced-filterpopup customernewsection-form">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-check ps-0">
                                            <label class="form-check-label">Add Filter</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Select Category</label>
                                        <select class="form-select select2" name="m_category">
                                            <option value="">Select</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label">Select Sub-Category</label>
                                        <select class="form-select select2" name="m_sub_category">
                                            <option value="">Select</option>
                                            @foreach ($sub_categories as $category)
                                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="Location">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="compoenentboxreport advanced-filterpopup customernewsection-form mb-1">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-check ps-0">
                                                    <label class="form-check-label">Add Scheduler</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row camparboxnewcen">
                                            <div class="mb-1">
                                                <label class="form-label">Email To</label>
                                                <select name="email_to[]" class="select2-email form-control mail_modal cannot_disable"
                                                    multiple data-placeholder="Select or enter email(s)">
                                                    @foreach ($users as $user)
                                                        <option value="{{ $user->email }}">{{ $user->name }} ({{ $user->email }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row camparboxnewcen">
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">CC To</label>
                                                    <select name="email_cc[]" class="select2-cc form-control mail_modal cannot_disable"
                                                        multiple data-placeholder="Select or enter email(s)">
                                                        @foreach ($users as $user)
                                                            <option value="{{ $user->email }}">{{ $user->name }} ({{ $user->email }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">Remarks</label>
                                                    <textarea name="remarks" id="mail_remarks" class="form-control mail_modal cannot_disable"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer ">
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary data-submit mr-1" id="applyBtn">Apply</button>
                </div>
            </div>
        </div>
    </div>
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
                    <button type="button" id="attribute-button" data-bs-dismiss="modal" class="btn btn-primary attribute-button">Select</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <!-- BEGIN: Dashboard Custom Code JS-->
    <script src="{{ asset('assets/js/custom/inventory-report.js') }}"></script>
    <script src="https://unpkg.com/feather-icons"></script>

    <!-- END: Dashboard Custom Code JS-->

    <script>
        window.routes = {
            poReport: @json(route('inventory-report.report.filter')),
            addScheduler: @json(route('inventory-report.add.scheduler')),
        };
        var subStoreLocType = @json($subStoreLocType);

        $(function() {
            $(".sortable").sortable();
        });

        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(function () {
                $('#applyBtn').trigger('click');
            }, 100);
            feather.replace();
            let filterData = {};
            $('.attributeBtn').hide();
            // Helper function to call fetch if there are changes
            function updateFilterAndFetch() {
                if (Object.keys(filterData).length > 0) {
                    fetchPurchaseOrders(filterData);
                }
            }

            document.querySelectorAll('input[name="Period"]').forEach((radio) => {
                radio.addEventListener('change', function(event) {
                    // Handle the selection
                    if (this.id === 'Custom') {
                        // Get the date range value
                        let dateRange = document.getElementById('Custom').value;

                        if (dateRange) {
                            // Split the date range into start and end dates
                            let dates = dateRange.split(' to ');
                            if (dates.length == 2) {
                                filterData.startDate = dates[0];
                                filterData.endDate = dates[1];
                            }
                        }
                        // Ensure period is not set when using custom date range
                        delete filterData.period;
                        updateFilterAndFetch();
                    } else {
                        filterData.period = event.target.value;
                        // Clear custom date range if a preset period is selected
                        delete filterData.startDate;
                        delete filterData.endDate;

                        updateFilterAndFetch();
                    }
                });
            });

            document.querySelectorAll('input[name="goodsservice"]').forEach((radio) => {
                radio.addEventListener('change', function() {
                    let filterData = {};
                    if (this.id === 'service') {
                        filterData.type = 'service';
                    } else if (this.id === 'goods') {
                        filterData.type = 'goods';
                    }

                    updateFilterAndFetch();
                });
            });

            $('#category').on('change', function() {
                const categoryValue = $(this).val();
                filterData.category = categoryValue; // Set null if no value selected
                updateFilterAndFetch();
            });

            $('#sub_category').on('change', function() {
                const subCategoryValue = $(this).val();
                filterData.subCategory = subCategoryValue;
                updateFilterAndFetch();
            });

            $('#attribute_name').on('change', function() {
                const attributeName = $('#attribute_name').getAttribute('data-attr-group-id');
                const attributeValue = $(this).attr('data-id');
                // Check the checkbox when a attributes is selected
                filterData.attribute_name = attributeName;
                filterData.attribute_value = attributeValue;
                // $('.attributeBtn').prop('disabled', 'false');
                updateFilterAndFetch();
            });

            $('#attribute-button').click(function(){
                let arr = [];
                $('.custnewpo-detail select, .custnewpo-detail input').each((key, item) => {
                    let groupId = $(item).data('attr-group-id');
                    let val = $(item).val();
                    arr.push({groupId, val})
                })
                // $('.attributeBtn').prop('disabled', 'false');
                filterData.attributes = arr;
                updateFilterAndFetch();
                // $('#attribute').hide();
            });

            $('#location_id').on('change', function() {
                const storeId = $(this).val();
                filterData.location_id = storeId;
                if (storeId) {
                    $('#store_id').val(storeId).select2();
                    var data = {
                        store_id: storeId,
                        types: subStoreLocType

                    };
                    $.ajax({
                        type: 'GET',
                        data: data,
                        url: '/sub-stores/store-wise',
                        success: function(data) {
                            $('#store_id').empty();
                            $('#store_id').append('<option value="">Select</option>');
                            $.each(data.data, function(index, item) {
                                $('#store_id').append('<option value="'+ item.id +'">'+ item.name +'</option>');
                            });
                            $('#store_id').trigger('change');
                        }
                    });
                } else {
                    $('#store_id').empty();
                    $('#store_id').append('<option value="">Select</option>');
                    $('#store_id').trigger('change');
                }
                updateFilterAndFetch();
            });

            // $('#store_id').on('change', function() {
            //     const subStoreId = $(this).val();
            //     filterData.store_id = subStoreId;
            //     updateFilterAndFetch();
            // });
            $('#store_id').on('change', function() {
                const stationId = $(this).val();
                filterData.store_id = stationId;
                if (stationId) {
                    $('#station_id').val(stationId).select2();
                    var data = {
                        sub_store_id: stationId,
                        // types: subStoreLocType

                    };
                    $.ajax({
                        type: 'GET',
                        data: data,
                        url: '/stations/stocking/get/by-sub-store',
                        success: function(data) {
                            $('#station_id').empty();
                            $('#station_id').append('<option value="">Select</option>');
                            $.each(data.data, function(index, item) {
                                $('#station_id').append('<option value="'+ item.id +'">'+ item.name +'</option>');
                            });
                            $('#station_id').trigger('change');
                        }
                    });
                } else {
                    $('#station_id').empty();
                    $('#station_id').append('<option value="">Select</option>');
                    $('#station_id').trigger('change');
                }
                updateFilterAndFetch();
            });

            $('#station_id').on('change', function() {
                const stationId = $(this).val();
                filterData.station_id = stationId;
                updateFilterAndFetch();
            });

            $('#stock_type').on('change', function() {
                const stockType = $(this).val();
                filterData.stock_type = stockType;
                updateFilterAndFetch();
            });

            $('#rack_id').on('change', function() {
                const rackId = $(this).val();
                filterData.rack_id = rackId;
                updateFilterAndFetch();
            });

            $('#shelf_id').on('change', function() {
                const shelfId = $(this).val();
                filterData.shelf_id = shelfId;
                updateFilterAndFetch();
            });

            $('#bin_id').on('change', function() {
                const binId = $(this).val();
                filterData.bin_id = binId;
                updateFilterAndFetch();
            });

            // Check Uncheck Attributes
            $('#attributes').on('change', function() {
                let attributes_check = 0;
                if ($(this).is(':checked')) {
                    // Send the parameter when the checkbox is checked
                    attributes_check = 1;
                    filterData.attributes_check = attributes_check;
                    updateFilterAndFetch();
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    attributes_check = 0;
                    filterData.attributes_check = attributes_check;
                    updateFilterAndFetch();
                }
            });

            // Check Uncheck Store
            $('#store').on('change', function() {
                let store_check = 0;
                if ($(this).is(':checked')) {
                    // Send the parameter when the checkbox is checked
                    store_check = 1;
                    filterData.store_check = store_check;
                    updateFilterAndFetch();
                    $('.location_id').css('display', 'block');
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    store_check = 0;
                    filterData.store_check = store_check;
                    updateFilterAndFetch();
                    $('.location_id').css('display', 'none');
                }
            });

            // Check Uncheck Rack
            $('#sub_location').on('change', function() {
                let sub_location_check = 0;
                if ($(this).is(':checked')) {
                    // Send the parameter when the checkbox is checked
                    sub_location_check = 1;
                    filterData.sub_location_check = sub_location_check;
                    sub_location_check = 1;
                    filterData.sub_location_check = sub_location_check;
                    updateFilterAndFetch();
                    $('.store_id').css('display', 'block');
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    sub_location_check = 0;
                    filterData.sub_location_check = sub_location_check;
                    updateFilterAndFetch();
                    $('.store_id').css('display', 'none');
                }
            });

            // Check Uncheck Station
            $('#station').on('change', function() {
                let station_check = 0;
                if ($(this).is(':checked')) {
                    // Send the parameter when the checkbox is checked
                    station_check = 1;
                    filterData.station_check = station_check;
                    station_check = 1;
                    filterData.station_check = station_check;
                    updateFilterAndFetch();
                    $('.station_id').css('display', 'block');
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    station_check = 0;
                    filterData.station_check = station_check;
                    updateFilterAndFetch();
                    $('.station_id').css('display', 'none');
                }
            });

            // Check Uncheck Bin
            $('#stock_types').on('change', function() {
                let stock_type_check = 0;
                if ($(this).is(':checked')) {
                    // Send the parameter when the checkbox is checked
                    stock_type_check = 1;
                    filterData.stock_type_check = stock_type_check;
                    filterData.stock_type = 'R';
                    updateFilterAndFetch();
                    $('.stock_type').css('display', 'block');
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    stock_type_check = 0;
                    filterData.stock_type_check = stock_type_check;
                    updateFilterAndFetch();
                    $('.stock_type').css('display', 'none');
                }
            });

            // Check Uncheck Shelf
            $('#shelf').on('change', function() {
                let shelf_check = 0;
                if ($(this).is(':checked')) {
                    // Send the parameter when the checkbox is checked
                    shelf_check = 1;
                    filterData.shelf_check = shelf_check;
                    updateFilterAndFetch();
                    $('.shelf_id').css('display', 'block');
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    shelf_check = 0;
                    filterData.shelf_check = shelf_check;
                    updateFilterAndFetch();
                    $('.shelf_id').css('display', 'none');
                }
            });

            // Check aging for last 1st input
            $('#selectAllInputAging').on('change', function() {
                if ($("#selectAllInputAging").is(':checked')) {
                    // Send the parameter when the aging checkbox is checked
                    filterData.day1_check = $('#day1').val();
                    filterData.day2_check = $('#day2').val();
                    filterData.day3_check = $('#day3').val();
                    filterData.day4_check = $('#day4').val();
                    filterData.day5_check = $('#day5').val();
                    // Update the visibility when the aging checkbox is checked

                    $('#day1_visibility').val(1);
                    $('#day2_visibility').val(1);
                    $('#day3_visibility').val(1);
                    $('#day4_visibility').val(1);
                    $('#day5_visibility').val(1);
                    $('#day6_visibility').val(1);

                    filterData.day1_visibility = 1;
                    filterData.day2_visibility = 1;
                    filterData.day3_visibility = 1;
                    filterData.day4_visibility = 1;
                    filterData.day5_visibility = 1;
                    filterData.day6_visibility = 1;
                    updateFilterAndFetch();
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    filterData.day1_check = 0;
                    filterData.day1_check = 0;
                    filterData.day1_check = 0;
                    filterData.day1_check = 0;
                    filterData.day1_check = 0;

                    $('#day1_visibility').val(0);
                    $('#day2_visibility').val(0);
                    $('#day3_visibility').val(0);
                    $('#day4_visibility').val(0);
                    $('#day5_visibility').val(0);
                    $('#day6_visibility').val(0);

                    filterData.day1_visibility = 0;
                    filterData.day2_visibility = 0;
                    filterData.day3_visibility = 0;
                    filterData.day4_visibility = 0;
                    filterData.day5_visibility = 0;
                    filterData.day6_visibility = 0;
                    updateFilterAndFetch();
                }
            });

            // Check aging for last 1st input
            $('#day1').on('change', function() {
                let day1 = 0;
                if ($("#selectAllInputAging").is(':checked')) {
                    // Send the parameter when the checkbox is checked
                    day1 = $('#day1').val();
                    // filterData.day1_check = 1;
                    filterData.day1_check = day1;
                    updateFilterAndFetch();
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    day1 = 0;
                    filterData.day1_check = day1;
                    updateFilterAndFetch();
                }
            });

            // Check aging for last 2nd input
            $('#day2').on('change', function() {
                let day2 = 0;
                if ($("#selectAllInputAging").is(':checked')) {
                    // Send the parameter when the checkbox is checked
                    day2 = $('#day2').val();
                    // filterData.day2_check = 1;
                    filterData.day2_check = day2;
                    updateFilterAndFetch();
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    day2 = 0;
                    filterData.day2_check = day2;
                    updateFilterAndFetch();
                }
            });

            // Check aging for last 3rd input
            $('#day3').on('change', function() {
                let day3 = 0;
                if ($("#selectAllInputAging").is(':checked')) {
                    // Send the parameter when the checkbox is checked
                    day3 = $('#day3').val();
                    filterData.day3_check = day3;
                    // filterData.day3_check = 1;
                    updateFilterAndFetch();
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    day3 = 0;
                    filterData.day3_check = day3;
                    updateFilterAndFetch();
                }
            });

            // Check aging for last 4rth input
            $('#day4').on('change', function() {
                let day4 = 0;
                if ($("#selectAllInputAging").is(':checked')) {
                    // Send the parameter when the checkbox is checked
                    day4 = $('#day4').val();
                    // filterData.day4_check = 1;
                    filterData.day4_check = day4;
                    updateFilterAndFetch();
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    day4 = 0;
                    filterData.day4_check = day4;
                    updateFilterAndFetch();
                }
            });

            // Check aging for last 5th input
            $('#day5').on('change', function() {
                let day5 = 0;
                if ($("#selectAllInputAging").is(':checked')) {
                    // Send the parameter when the checkbox is checked
                    day5 = $('#day5').val();
                    // filterData.day5_check = 1;
                    filterData.day5_check = day5;
                    updateFilterAndFetch();
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    day5 = 0;
                    filterData.day5_check = day5;
                    updateFilterAndFetch();
                }
            });

            function checkAttribute(itemValue){
                let attributes_check = 0;
                if ($('#attributes').is(':checked')) {
                    // Send the parameter when the checkbox is checked
                    attributes_check = 1;
                    filterData.attributes_check = attributes_check;
                    getItemAttribute(itemValue);
                    $('.attributeBtn').show();
                    updateFilterAndFetch();
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    attributes_check = 0;
                    $('.attributeBtn').hide();
                    // Swal.fire({
                    //     title: 'Error!',
                    //     text: "Please check attribute filter 1st from advance filter button.",
                    //     icon: 'error',
                    // });
                    filterData.attributes_check = attributes_check;
                    updateFilterAndFetch();
                }
            }

            // function getSelectedData() {
            //     let selectedData = [];
            //     $('select[name="to"] option:selected').each(function() {
            //         selectedData.push({
            //             id: $(this).val(),
            //             type: $(this).data('type')
            //         });
            //     });
            //     return selectedData;
            // }

            $('.attributeBtn').on('click', function() {
                let attributes_check = 0;

                let itemValue = $("#item").val();

                if ($('#attributes').is(':checked')) {
                    // Send the parameter when the checkbox is checked
                    attributes_check = 1;
                    filterData.attributes_check = attributes_check;
                    getItemAttribute(itemValue);
                    $('.attributeBtn').show();
                    updateFilterAndFetch();
                } else {
                    // Handle the case where the checkbox is unchecked if needed
                    attributes_check = 0;
                    $('.attributeBtn').hide();
                    filterData.attributes_check = attributes_check;
                    updateFilterAndFetch();
                }
            });

            /*For comp attr*/
            function getItemAttribute(itemId){
                let actionUrl = '{{route("inventory-report.item.attr")}}'+'?item_id='+itemId;
                fetch(actionUrl).then(response => {
                    return response.json().then(data => {
                        if (data.status == 200 && data.data.html) {
                            $("#attribute tbody").empty();
                            $("#attribute table tbody").append(data.data.html);
                            if ($('#attributes').is(':checked')) {
                                $("#attribute").modal('show');
                            } else{
                                $("#attribute").modal('hide');
                            }
                        }
                    });
                });
            }

            // Trigger column order save when Apply button is clicked
            $('#applyBtn').on('click', function(e) {
                checkAgingInputs();
                const columnOrder = getColumnVisibilitySettings();
                filterData.columnOrder = columnOrder;
                // Close the modal
                var filterModal = bootstrap.Modal.getInstance(document.getElementById('addcoulmn'));

                // Optionally handle the response here
                e.preventDefault();

                // Get the date value
                const dateValue = $('input[name="date"]').val();
                const today = new Date().toISOString().split('T')[0];
                // let selectedData = getSelectedData();
                // Gather form data
                var formData = {
                    email_to: $('select[name="email_to[]"]').val(),
                    email_cc: $('select[name="email_cc[]"]').val(),
                    remarks: $('#mail_remarks').val(),
                    m_category: $('select[name="m_category"]').val(),
                    m_subCategory: $('select[name="m_sub_category"]').val(),
                    m_attribute: $('select[name="m_attribute"]').val(),
                    m_attributeValue: $('select[name="m_attribute_value"]').val(),
                    report_type: 'report',
                };

                filterData.m_category = formData.m_category;
                filterData.m_subCategory = formData.m_subCategory;
                filterData.m_attribute = formData.m_attribute;
                filterData.m_attributeValue = formData.m_attributeValue;


                // Manually trigger change if already checked
                // #For Store
                if ($('.autoTriggerChangeApply').is(':checked')) {
                    $('.autoTriggerChangeApply').trigger('change');
                }

                // Call updateFilterAndFetch once
                updateFilterAndFetch();

                let activeTab = $('#addcoulmn .nav-tabs .nav-link.active').attr('href');
                if (activeTab == "#Location") {
                    let dataTableSelector = ".datatables-basic";
                    const table = $(dataTableSelector).DataTable();
                    // Get the current filtered data in the DataTable
                    const displayedData = table.rows({ filter: 'applied' }).data();

                    // Getting the values of datatable
                    const displayedDataArray = [];
                    displayedData.each(function(rowData) {
                        displayedDataArray.push(rowData);
                    });

                    // Getting the headers of datatable
                    const displayedHeaders = [];
                    $(table.columns().header()).each(function() {
                        displayedHeaders.push($(this).text().trim());
                    });

                    formData.displayedData = displayedDataArray;
                    formData.displayedHeaders = displayedHeaders;
                    formData.store_id = filterData.location_id;
                    formData.sub_store_id = filterData.store_id;
                    formData.station_id = filterData.station_id;
                    formData.stock_type = filterData.stock_type;

                    $.ajax({
                        url: window.routes.addScheduler,
                        method: 'POST',
                        data: formData,
                        success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: response.success || 'Email sent successfully!',
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        $('select[name="email_to[]"]').val([]).trigger('change');
                        $('select[name="email_cc[]"]').val(null).trigger('change');
                        $('textarea[name="remarks"]').val('');
                        filterModal.hide();
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error sending email',
                            text: xhr.responseJSON?.message || 'Something went wrong.'
                        });
                    }
                    });
                } else {
                    if (filterModal) {
                        filterModal.hide();
                    }
                }
            });

            function initializeAutocomplete(selector, type) {
                $(selector).autocomplete({
                    minLength: 0,
                    source: function(request, response) {
                        $.ajax({
                            url: '/search',
                            method: 'GET',
                            dataType: 'json',
                            data: {
                                q: request.term,
                                type: type
                            },
                            success: function(data) {
                                response($.map(data, function(item) {
                                    return {
                                        id: item.id,
                                        label: item.item_name,
                                        code: item.item_code
                                    };
                                }));
                            },
                            error: function(xhr) {
                                console.error('Error fetching item data:', xhr
                                .responseText);
                            }
                        });
                    },
                    select: function(event, ui) {
                        var $input = $(this);
                        var itemName = ui.item.label;
                        var itemId = ui.item.id;
                        var itemCode = ui.item.code;

                        $input.val(itemName);
                        $input.attr('data-name', itemName);
                        $input.attr('data-code', itemCode);
                        $input.attr('data-id', itemId);
                        $input.attr('value', itemId);
                        filterData.item = itemId;
                        checkAttribute(itemId);
                        getItemAttribute(itemId);
                        updateFilterAndFetch();
                    },
                    change: function(event, ui) {
                        if (!ui.item) {
                            $(this).val('');
                            $(this).attr('data-name', '');
                            $(this).attr('data-id', '');
                        }
                    }
                }).focus(function() {
                    if (this.value === "") {
                        $(this).autocomplete("search", "");
                    }
                });
            }
            initializeAutocomplete(".inventory_items", "inventory_items");
            $('.select2-email').select2({
                tags: true,
                tokenSeparators: [',', ' '],
                placeholder: "Select or enter email(s)",
                width: '100%',
                createTag: function(params) {
                    var term = $.trim(params.term);
                    // Basic email format validation
                    if (term.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                        return {
                            id: term,
                            text: term,
                            newTag: true
                        };
                    }
                    return null;
                }
            });

            $('.select2-cc').select2({
                tags: true,
                tokenSeparators: [',', ' '],
                placeholder: "Select or enter email(s)",
                width: '100%',
                createTag: function(params) {
                    var term = $.trim(params.term);
                    // Basic email format validation
                    if (term.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                        return {
                            id: term,
                            text: term,
                            newTag: true
                        };
                    }
                    return null;
                }
            });
        });
    </script>
    <script>
        /*For Aging Inputs*/
        function checkAgingInputs(){
            const agingCheckbox = $('#selectAllInputAging'); // The checkbox
            const inputs = $('.aging-input'); // All the aging input fields
            if ($("#selectAllInputAging").is(':checked')) {
                // Disable inputs by default
                inputs.prop('disabled', false);
            }else{
                // Disable inputs by default
                inputs.prop('disabled', true);
            }

            // Enable/Disable inputs based on checkbox
            agingCheckbox.on('change', function () {
                inputs.prop('disabled', !this.checked);
            });

            // Store old value before input changes
            inputs.on('focus', function () {
                $(this).data('old', $(this).val()); // Store current value as old
            });

            // Enforce input validation: greater than the previous and less than the next
            $('.aging-input').on('change', function () {
                const currentIndex = $('.aging-input').index(this);
                const currentValue = parseInt($(this).val()) || 0;
                const oldValue = $(this).data('old'); // Retrieve stored value

                // Validate against previous input
                if (currentIndex > 0) {
                    const prevValue = parseInt($('.aging-input').eq(currentIndex - 1).val()) || 0;
                    if (currentValue <= prevValue) {
                        Swal.fire({
                            title: 'Error!',
                            text: "Value must be greater than the previous input.",
                            icon: 'error',
                        });
                        $(this).val(oldValue);
                        return;
                    }
                }

                // Validate against next input
                if (currentIndex < $('.aging-input').length - 1) {
                    const nextValue = parseInt($('.aging-input').eq(currentIndex + 1).val()) || 0;
                    if (nextValue !== 0 && currentValue >= nextValue) {
                        Swal.fire({
                            title: 'Error!',
                            text: "Value must be less than the next input.",
                            icon: 'error',
                        });
                        $(this).val(oldValue);
                    }
                }
            });
        }
    </script>
@endsection
