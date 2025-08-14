@extends('layouts.app')

@section('content')
<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <form class="ajax-input-form" method="POST" action="{{ route('production-route.store') }}" data-redirect="/production-routes" enctype="multipart/form-data">
        @csrf
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Production Route</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ route('/') }}">Home</a></li>
                                        <li class="breadcrumb-item"><a href="{{ route('production-route.index') }}">Production Route</a></li>
                                        <li class="breadcrumb-item active">Add New</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <input type="hidden" name="document_status" value="draft" id="document_status">
                            <a href="javascript: history.go(-1)" class="btn btn-secondary btn-sm"><i
                                    data-feather="arrow-left-circle"></i> Back</a>
                            <button type="button" class="btn btn-primary btn-sm submit-button" id="submit-button" name="action" value="submitted" disabled>
                                <i data-feather="check-circle"></i> Submit
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-body" data-select2-id="57">
                <section id="basic-datatable" data-select2-id="basic-datatable">
                    <div class="row" data-select2-id="103">
                        <div class="col-12" data-select2-id="102">
                            <div class="card" data-select2-id="101">
                                <div class="card-body customernewsection-form" data-select2-id="56">
                                    <div class="row" data-select2-id="100">
                                        <div class="col-md-12">
                                            <div class="newheader  border-bottom mb-2 pb-25">
                                                <h4 class="card-title text-theme">Basic Information</h4>
                                                <p class="card-text">Fill the details</p>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">
                                                        Name <span class="text-danger">*</span>
                                                    </label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="name" class="form-control" id="name">
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">
                                                        Description <span class="text-danger"></span>
                                                    </label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="description" class="form-control" id="description">
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">
                                                        Safety Buffer(%) <span class="text-danger"></span>
                                                    </label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="safety_buffer_perc" class="form-control" id="safety_buffer_perc">
                                                </div>
                                            </div>
                                            <div class="border p-2 rounded bg-light">
                                                <ol class="mb-0 small text-danger fw-number">
                                                    <li>All stations, except the last level, must have a parent.</li>
                                                    <li>Every parent must exist as a station in the subsequent level.</li>
                                                    <li>The last level must have only one station.</li>
                                                    <li>The last level should not have a parent.</li>
                                                </ol>
                                            </div>
                                        </div>
                                        <div class="col-md-3 border-start">
                                            <div class="row align-items-center mb-2">
                                                <div class="col-md-12">
                                                    <label class="form-label text-primary">
                                                        <strong>Status</strong>
                                                    </label>
                                                    <div class="demo-inline-spacing">
                                                        @foreach ($status as $statusOption)
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input
                                                                    type="radio"
                                                                    id="status_{{ $statusOption }}"
                                                                    name="status"
                                                                    value="{{ $statusOption }}"
                                                                    class="form-check-input"
                                                                    {{ $statusOption === 'active' ? 'checked' : '' }}
                                                                >
                                                                <label class="form-check-label fw-bolder" for="status_{{ $statusOption }}">
                                                                    {{ ucfirst($statusOption) }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12" data-select2-id="99">
                                            <div class="mt-2" data-select2-id="98">
                                                <div class="step-custhomapp bg-light">
                                                    <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                                                        <li class="nav-item">
                                                            <a class="nav-link active" data-bs-toggle="tab" href="#Approval">Process</a>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <div class="tab-content " data-select2-id="55">
                                                    {{-- <p class="fw-normal font-small-2 badge bg-light-danger">
                                                        <strong>Note:</strong> Add All level with station to mapping with parent Station
                                                    </p> --}}
                                                    <button type="button" id="addLevel" class="btn btn-sm btn-primary hover:bg-blue-700 text-white rounded text-right" style="float:right;">
                                                        Add Level
                                                    </button>
                                                    <div class="tab-pane active" id="Approval" data-select2-id="Approval">
                                                        <div class="table-responsive-md">
                                                            <table id="levelTable" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                                <thead>
                                                                    <tr>
                                                                        <th width="50px;">#</th>
                                                                        <th width="500px">Station<span class="text-danger">*</span></th>
                                                                        <th width="250px">Parent Station<span class="text-danger"></span></th>
                                                                        <th width="200px">Consumption<span class="text-danger"></span></th>
                                                                        <th width="200px">Q/A<span class="text-danger"></span></th>
                                                                        <th width="100px;">Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    <!-- Level 1 Fixed Row -->
                                                                </tbody>
                                                            </table>
                                                            <!-- <button id="addLevel" class="btn btn-primary mt-4 bg-blue-500 hover:bg-blue-700 text-white py-2 px-4 rounded">
                                                                Add New Level
                                                            </button> -->
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
                    </div>
                </section>
            </div>
        </div>
    </form>
</div>
@endsection
@section('scripts')
<script>
    let levelCounter = 1; // Start from Level 1
    let selectedStations = new Set();
    let levelParentStationMap = {};
    let levelSelectedStations = {};
    $(document).ready(function () {
        function initializeSelect2() {
            $('.select2').select2({
                placeholder: "Select options",
                allowClear: true
            });
        }
        initializeSelect2();
        // Function to add a new level
        function addNewLevel(afterLevel = null) {
            let levelId = afterLevel !== null ? afterLevel + 1 : levelCounter;
            $('#levelTable tbody tr.approvlevelflow').each(function () {
                let currentLevel = parseInt($(this).attr('data-index'));
                if (currentLevel >= levelId) {
                    let newIndex = currentLevel + 1;
                    $(this).attr('data-index', newIndex);
                    $(this).attr('data-detail-count', newIndex);
                    $(this).attr('data-level', newIndex);
                    $(this).find('td:first').text(newIndex);
                    $(this).find('h6').text(`Level ${newIndex}`);

                    // Update input names dynamically
                    $(this).find('input[name^="levels"]').each(function () {
                        // Update the 'name' attribute to reflect the new index
                        this.name = this.name.replace(/\[\d+\]/, `[${newIndex}]`);

                        if (this.name.includes('level')) {
                            this.value = `${newIndex}`;
                        }
                        else if (this.name.includes('name')) {
                            this.value = `Level ${newIndex}`;
                        }
                    });

                    // Update child rows for this level
                    $(this).nextUntil('.approvlevelflow').each(function () {
                        $(this).attr('data-index', newIndex);
                        $(this).attr('data-detail-id', newIndex);
                        $(this).find('select[name^="levels"]').each(function () {
                            this.name = this.name.replace(/\[\d+\]/, `[${newIndex}]`);
                        });

                        // Update data-level-id and data-detail-id
                        $(this).find('.consumption-checkbox').attr('data-level-id', newIndex);
                        $(this).find('input[name^="levels"]').each(function () {
                            this.name = this.name.replace(/\[\d+\]/, `[${newIndex}]`);
                        });
                    });
                }
            });

            const newLevel = `
                <tr class="approvlevelflow" data-index="${levelId}" data-detail-count="${levelId}">
                    <td>${levelId}</td>
                    <td colspan="2">
                        <h6 class="mb-0 fw-bolder text-dark">Level ${levelId}</h6>
                        <input type="hidden" name="levels[${levelId}][level]" value="${levelId}">
                        <input type="hidden" name="levels[${levelId}][name]" value="Level ${levelId}">
                    </td>
                    <td>
                    </td>
                    <td>
                    </td>
                    <td>
                        <a data-row-count="${levelId}" data-index="${levelId}" class="text-primary addLevel">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-square">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                        </a>
                        <a data-row-count="${levelId}" data-index="${levelId}" class="deleteLevel text-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </a>
                    </td>
                </tr>
                <tr class="child-row" data-index="${levelId}" data-detail-id="1">
                    <td>&nbsp;</td>
                    <td>
                        <select class="form-select mw-100 select2 station" name="levels[${levelId}][details][1][station_id]" onchange="updateStationDropdown()">
                            <option value="">Select</option>
                            @foreach($stations as $val)
                                <option value="{{ $val->id }}">{{ $val->name }}</option>
                            @endforeach
                            <input type="hidden" name="levels[${levelId}][details][1][hidden_station_id]" id="hidden_station_${levelId}_1" value="">
                        </select>
                    </td>
                    <td>
                        <select class="form-select mw-100 select2 parent" name="levels[${levelId}][details][1][parent_id]" onchange="updateStationDropdown()">
                            <option value="">Select</option>
                            @foreach($stations as $val)
                                <option value="{{ $val->id }}">{{ $val->name }}</option>
                            @endforeach
                            <input type="hidden" name="levels[${levelId}][details][1][hidden_parent_id]" id="hidden_parent_${levelId}_1" value="">
                        </select>
                    </td>
                    <td>
                        <input type="checkbox" class="form-check-input consumption-checkbox" data-level-id="${levelId}" data-detail-id="1">
                        <input type="hidden" name="levels[${levelId}][details][1][consumption]" value="no">
                    </td>
                    <td>
                        <input type="checkbox" class="form-check-input qa-checkbox" data-level-id="${levelId}" data-detail-id="1">
                        <input type="hidden" name="levels[${levelId}][details][1][qa]" value="no">
                    </td>
                    <td>
                        <a class="text-primary btn-add-child" data-index="${levelId}" data-detail-id="1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-square">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                        </a>
                        <a class="delete-child text-danger" data-index="${levelId}" data-detail-id="1">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </a>
                    </td>
                </tr>
            `;

            if (afterLevel !== null) {
                let $afterRow = $(`tr[data-index="${afterLevel}"]`).last();
                let $lastChildRow = $afterRow.nextUntil('.approvlevelflow').last();

                if ($lastChildRow.length) {
                    $lastChildRow.after(newLevel);
                } else {
                    $afterRow.after(newLevel);
                }
            } else {
                $('#levelTable tbody').append(newLevel);
            }

            levelCounter++;
            updateLevelNumbers();
            // initializeAutocomplete('.pr_items');
            initializeSelect2();
            updateStationDropdown();
        }

        $(document).on("change", ".parent", function () {
            const parentStationId = $(this).val() || null;;
            const levelId = $(this).closest('tr').attr('data-index');
            const detailId = $(this).closest('tr').data('detail-id');
            $(`#hidden_parent_${levelId}_${detailId}`).val(parentStationId);

            let prevParentId = $(this).data("prev-value");
            if (!levelParentStationMap[levelId]) {
                levelParentStationMap[levelId] = [];
            }
            // Update the stored previous value to the new one
            $(this).data("prev-value", parentStationId);
            let index = levelParentStationMap[levelId].indexOf(prevParentId);
            if (index !== -1) {
                levelParentStationMap[levelId].splice(index, 1);
            }
            if (parentStationId) {
                levelParentStationMap[levelId].push(parentStationId);
            }
            updateStationDropdown();
        });

        $(document).on("change", ".station", function () {
            const stationId = $(this).val() || null;;
            const levelId = $(this).closest('tr').attr('data-index');
            const detailId = $(this).closest('tr').data('detail-id');
            $(`#hidden_station_${levelId}_${detailId}`).val(stationId);

            let prevStationId = $(this).data("prev-value");
            // Update the stored previous value to the new one
            $(this).data("prev-value", stationId);

            // Check if stationId is valid
            if (!stationId){
                $(this).removeData("prev-value");
            }

            // Call API to fetch consumption value for the selected station
            $.ajax({
                url: `/production-routes/station`, // Replace with your actual API endpoint
                method: "GET",
                data: {
                    station_id: stationId
                },
                success: function(response) {

                    if (response && response[0] && response[0].hasOwnProperty('is_consumption')) {
                        // Update the consumption checkbox and hidden input based on API response
                        const consumptionValue = (response[0]['is_consumption'] === 'yes') ? 'yes' : 'no';

                        // Update the checkbox state
                        $(`input[data-level-id="${levelId}"][data-detail-id="${detailId}"].consumption-checkbox`)
                        .prop('checked', consumptionValue === 'yes' ? true : false);

                        // Update the hidden input value
                        $(`input[name="levels[${levelId}][details][${detailId}][consumption]"]`)
                            .val(consumptionValue);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Error fetching consumption value: ", error);
                    // You can handle the error (e.g., show a message to the user)
                }
            });
            let selectedStation = $(this).val();
            if (prevStationId) {
                selectedStations.delete(prevStationId);
                levelSelectedStations[levelId] = levelSelectedStations[levelId].filter(id => id !== prevStationId);
            }
            selectedStations.add(stationId);
            if (!levelSelectedStations[levelId]) {
                levelSelectedStations[levelId] = [];
            }
            levelSelectedStations[levelId].push(stationId);
            updateStationDropdown();
        });

        $(document).on("change", ".consumption-checkbox", function () {
            let hiddenInput = $(this).closest("td").find("input[type='hidden']");
            hiddenInput.val(this.checked ? "yes" : "no");
        });

        $(document).on("change", ".qa-checkbox", function () {
            let hiddenInput = $(this).closest("td").find("input[type='hidden']");
            hiddenInput.val(this.checked ? "yes" : "no");
        });

        // Add New Level when clicking outside button
        $('#addLevel').on('click', function () {
            addNewLevel();
            $(this).hide(); // Remove after first click
            const submitButton = document.getElementById('submit-button');
            submitButton.disabled = false;
            // $('#addLevel').show().prop('disabled', true); // Remove after first click
        });

        // Add New Level inside the table
        $('#levelTable').on('click', '.addLevel', function () {
            let currentLevel = $(this).data('index');
            addNewLevel(currentLevel);
        });

        // Add Child Row
        $('#levelTable').on('click', '.btn-add-child', function () {
            const levelId = $(this).data('index');
            let detailCount = $(`tr.child-row[data-index="${levelId}"]`).length + 1;

            const newChild = `
                <tr class="child-row" data-index="${levelId}" data-detail-id="${detailCount}">
                    <td>&nbsp;</td>
                    <td>
                        <select class="form-select mw-100 select2 station" name="levels[${levelId}][details][${detailCount}][station_id]" onchange="updateStationDropdown()">
                            <option value="">Select</option>
                            @foreach($stations as $val)
                                <option value="{{ $val->id }}">{{ $val->name }}</option>
                            @endforeach
                            <input type="hidden" name="levels[${levelId}][details][${detailCount}][hidden_station_id]" id="hidden_station_${levelId}_${detailCount}" value="">
                        </select>
                    </td>
                    <td>
                        <select class="form-select mw-100 select2 parent" name="levels[${levelId}][details][1][parent_id]" onchange="updateStationDropdown()">
                            <option value="">Select</option>
                            @foreach($stations as $val)
                                <option value="{{ $val->id }}">{{ $val->name }}</option>
                            @endforeach
                            <input type="hidden" name="levels[${levelId}][details][${detailCount}][hidden_parent_id]" id="hidden_parent_${levelId}_${detailCount}" value="">
                        </select>
                    </td>
                    <td>
                        <input type="checkbox" class="form-check-input consumption-checkbox" data-level-id="${levelId}" data-detail-id="${detailCount}">
                        <input type="hidden" name="levels[${levelId}][details][${detailCount}][consumption]" value="no">
                    </td>
                    <td>
                        <input type="checkbox" class="form-check-input qa-checkbox" data-level-id="${levelId}" data-detail-id="${detailCount}">
                        <input type="hidden" name="levels[${levelId}][details][${detailCount}][qa]" value="no">
                    </td>
                    <td>
                        <a class="text-primary btn-add-child" data-index="${levelId}" data-detail-id="${detailCount}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-square">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                <line x1="12" y1="8" x2="12" y2="16"></line>
                                <line x1="8" y1="12" x2="16" y2="12"></line>
                            </svg>
                        </a>
                        <a class="delete-child text-danger" data-index="${levelId}" data-detail-id="${detailCount}">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-trash-2">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                <line x1="10" y1="11" x2="10" y2="17"></line>
                                <line x1="14" y1="11" x2="14" y2="17"></line>
                            </svg>
                        </a>
                    </td>
                </tr>
            `;

            $(this).closest('tr').after(newChild);
            reindexChildRows(levelId);
            // initializeAutocomplete('.pr_items');
            initializeSelect2();
            updateStationDropdown();
        });

        // Delete Level (Parent + Children) with SweetAlert2 confirmation
        $('#levelTable').on('click', '.deleteLevel', function () {
            let levelId = $(this).data('index'); // Use 'data-index'

            Swal.fire({
                title: "Are you sure?",
                text: "This will delete the level and all its child rows!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    let removedStationIds = new Set();
                    $(`tr.child-row[data-index="${levelId}"]`).each(function () {
                        $(this).find('input[name^="levels"][name$="[hidden_station_id]"]').each(function () {
                            let stationId = $(this).val();
                            if (stationId) {
                                removedStationIds.add(stationId);
                            }
                        });
                    });

                    // Remove collected station IDs from selectedStations
                    removedStationIds.forEach(id => {
                        selectedStations.delete(id);
                    });

                    $(`tr[data-index="${levelId}"]`).remove();

                    // Update level numbers
                    updateLevelNumbers();
                    updateStationDropdown();

                    // Check if all levels are deleted, then show the Add button
                    if ($('#levelTable tbody tr').length === 0) {
                        // $('#addLevel').show().prop('disabled', false);
                        $('#addLevel').show();
                        const submitButton = document.getElementById('submit-button');
                        submitButton.disabled = true;
                    }

                    Swal.fire({
                        title: "Deleted!",
                        text: "The level and its child rows have been removed.",
                        icon: "success",
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        });

        // Delete Single Child Row
        $('#levelTable').on('click', '.delete-child', function () {
            const levelId = $(this).data('index');
            const detailId = $(this).data('detail-id');
            const row = $(this).closest('tr');

            const child_length = $(`#levelTable tbody tr.child-row[data-index='${levelId}']`).length;
            if (child_length === 1) {
                $(`tr[data-index="${levelId}"]`).find(".deleteLevel").trigger("click");
                return;
            }

            let stationId = row.find('.station').val();
            if (stationId) {
                selectedStations.delete(stationId);
            }

            Swal.fire({
                title: "Are you sure?",
                text: "This will delete the selected child row!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
                    let removedStationIds = new Set();
                    $(`tr.child-row[data-index="${levelId}"][data-detail-id="${detailId}"]`).each(function () {
                        $(this).find('input[name^="levels"][name$="[hidden_station_id]"]').each(function () {
                            let stationId = $(this).val();
                            if (stationId) {
                                removedStationIds.add(stationId);
                            }
                        });
                    });
                    // Remove collected station IDs from selectedStations
                    removedStationIds.forEach(id => {
                        selectedStations.delete(id);
                    });
                    row.remove();
                    reindexChildRows(levelId);
                    Swal.fire({
                        title: "Deleted!",
                        text: "The child row has been removed.",
                        icon: "success",
                        timer: 1500,
                        showConfirmButton: false
                    });
                    updateStationDropdown();
                }
            });
            // $(this).closest('tr').remove();
        });

        function updateLevelNumbers() {
            let newCounter = 1;
            let newLevelParentStationMap = {};

            $('#levelTable tbody tr.approvlevelflow').each(function () {
                let oldIndex = $(this).attr('data-index');
                $(this).attr('data-index', newCounter);
                $(this).attr('data-detail-count', newCounter);
                $(this).attr('data-level', newCounter);
                $(this).find('td:first').text(newCounter);
                $(this).find('h6').text(`Level ${newCounter}`);
                $(this).find('a.addLevel').attr('data-index', newCounter).attr('data-row-count', newCounter);
                $(this).find('a.deleteLevel').attr('data-index', newCounter).attr('data-row-count', newCounter);

                // // Update input names
                // $(this).find('input[name^="levels"]').each(function () {
                //     this.name = this.name.replace(/\[\d+\]/, `[${newCounter}]`);
                // });
                // Update inputs and selects
                $(this).find('input[name^="levels"]').each(function () {
                    this.name = this.name.replace(/\[\d+\]/, `[${newCounter}]`);
                    if (this.name.includes('level')) {
                        this.value = `${newCounter}`;
                    }
                    else if (this.name.includes('name')) {
                        this.value = `Level ${newCounter}`;
                    }
                });

                $(this).find('.station, .parent').each(function () {
                    this.name = this.name.replace(/\[\d+\]/, `[${newCounter}]`);
                    this.setAttribute('data-index', newCounter);
                });

                // // Update child rows
                // $(this).nextUntil('.approvlevelflow').each(function () {
                //     $(this).attr('data-index', newCounter);
                //     $(this).find('select[name^="levels"]').each(function () {
                //         this.name = this.name.replace(/\[\d+\]/, `[${newCounter}]`);
                //     });
                //     $(this).find('.btn-add-child').attr('data-index', newCounter);
                //     $(this).find('.delete-child').attr('data-index', newCounter);
                // });
                // Update child rows
                $(this).nextUntil('.approvlevelflow').each(function () {
                    $(this).attr('data-index', newCounter);

                    $(this).find('select[name^="levels"]').each(function () {
                        this.name = this.name.replace(/\[\d+\]/, `[${newCounter}]`);
                    });

                    $(this).find('input[name^="levels"]').each(function () {
                        this.name = this.name.replace(/\[\d+\]/, `[${newCounter}]`);
                    });

                    $(this).find('.btn-add-child').attr('data-index', newCounter);
                    $(this).find('.delete-child').attr('data-index', newCounter);

                    // Update hidden input names
                    $(this).find('input[name^="levels"][name$="[hidden_station_id]"]').each(function () {
                        this.id = `hidden_station_${newCounter}_${$(this).closest('tr').data('detail-id')}`;
                    });

                    $(this).find('input[name^="levels"][name$="[hidden_parent_id]"]').each(function () {
                        this.id = `hidden_parent_${newCounter}_${$(this).closest('tr').data('detail-id')}`;
                    });

                    // Update parent station map
                    if (levelParentStationMap[oldIndex]) {
                        newLevelParentStationMap[newCounter] = levelParentStationMap[oldIndex];
                    }
                });

                newCounter++;
            });

            levelCounter = newCounter;
            levelParentStationMap = newLevelParentStationMap;
            updateStationDropdown();
        }

        function reindexChildRows(levelId) {
            $(`tr.child-row[data-index="${levelId}"]`).each(function (index) {
                let newIndex = index + 1;
                $(this).attr('data-detail-id', newIndex);
                // Update input/select names
                $(this).find('select, input').each(function () {
                    this.name = this.name.replace(/\[details\]\[\d+\]/, `[details][${newIndex}]`);
                });
                // Update delete button
                $(this).find('.delete-child').attr('data-detail-id', newIndex);
            });
        }
    });

    function updateStationDropdown() {
        // Loop through all stations (both parent and child rows)
        $('.station').each(function () {
            let $select = $(this);
            let levelId = $(this).closest('tr').attr('data-index');
            let selectedStationId = $select.val();
            // Add the selected station id to the set to disable later
            if (selectedStationId) {
                selectedStations.add(selectedStationId);
            }

            $select.find('option').prop('disabled', false);

            // Disable options that are already selected in other station selects
            selectedStations.forEach(function (stationId) {
                if (stationId && stationId !== selectedStationId) {
                    $select.find(`option[value="${stationId}"]`).prop('disabled', true);
                }
            });
            $select.select2();

            // Disable options based on parent station map for the current level
            if (levelParentStationMap[levelId]) {
                levelParentStationMap[levelId].forEach(function (parentId) {
                    $select.find(`option[value="${parentId}"]`).prop('disabled', true);
                });
            }

            // Also disable the currently selected station in the same dropdown
            // if (selectedStationId) {
            //     $select.find(`option[value="${selectedStationId}"]`).prop('disabled', true);
            // }
        });

        $('.parent').each(function () {
            let $parentSelect = $(this);
            let levelId = parseInt($parentSelect.closest('tr').attr('data-index')); // Convert to number

            // Reset options
            $parentSelect.find('option').prop('disabled', false);

            // Collect all selected stations from the same and previous levels only
            let disabledStations = new Set();

            Object.keys(levelSelectedStations).forEach(lvl => {
                let lvlNum = parseInt(lvl); // Convert key to number
                if (lvlNum <= levelId) { // Only consider same and previous levels
                    levelSelectedStations[lvl].forEach(stationId => {
                        disabledStations.add(stationId);
                    });
                }
            });

            $parentSelect.find('option').each(function () {
                let $option = $(this);
                let optionValue = $option.val();

                if (optionValue !== "" && disabledStations.has(optionValue)) {
                    $option.prop('disabled', true);
                }
            });
            $parentSelect.select2();
        });
    }

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
                        type: 'pr_item'
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
                        console.error('Error fetching item data:', xhr.responseText);
                    }
                });
            },
            select: function(event, ui) {
                var $input = $(this);
                var itemName = ui.item.value;
                var itemId = ui.item.id;
                var itemCode = ui.item.code;
                $input.val(itemName);
                $input.attr('data-name', itemName);
                var parentRow = $input.closest('tr');
                var levelId = parentRow.data('index');
                var detailCount = parentRow.data('detail-id');
                $(`#semi_finished_item_id_${levelId}_${detailCount}`).val(itemId);
                $(`#semi_finished_item_code_${levelId}_${detailCount}`).val(itemCode);
                getItemAttribute(itemId, itemAttributes = [], levelId, detailCount, parentRow);
                return false;
            },
            change: function(event, ui) {
                if (!ui.item) {
                    $(this).val('');
                    $(this).attr('data-name', '');
                }
            }
        }).focus(function() {
            if (this.value === "") {
                $(this).autocomplete("search", "");
            }
        });
    }
    // initializeAutocomplete(".pr_items")

    function getItemAttribute(itemId, itemAttributes, levelId, detailCount, parentRow) {

        // Make an AJAX call to fetch the attributes for the selected item
        $.ajax({
            url: '{{route("production-route.get-items")}}' + '?item_id=' + itemId + '&itemAttributes=' + JSON.stringify(itemAttributes) + '&levelId=' + levelId + '&detailCount=' + detailCount ,
            method: 'GET',
            dataType: 'json',
            data: { item_id: itemId },
            success: function(response) {
                if (response && response.data) {
                    parentRow.find('.attributes-container').html(response.data.html);
                    parentRow.find('.select2').each(function() {
                        $(this).select2();
                    });
                }
            },
            error: function(xhr) {
                console.error('Error fetching attributes:', xhr.responseText);
            }
        });
    }

</script>
@endsection
