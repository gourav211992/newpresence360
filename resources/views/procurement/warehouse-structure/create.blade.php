@extends('layouts.app')

@section('content')
<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <form class="ajax-input-form" method="POST" action="{{ route('warehouse-structure.store') }}" data-redirect="/warehouse-structures" enctype="multipart/form-data">
        @csrf
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Warehouse Structure</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('/') }}">Home</a>
                                        </li>
                                        <li class="breadcrumb-item">
                                            <a href="{{ route('warehouse-structure.index') }}">
                                                Warehouse Structure
                                            </a>
                                        </li>
                                        <li class="breadcrumb-item active">
                                            Add New
                                        </li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <input type="hidden" name="document_status" value="draft" id="document_status">
                            <a href="javascript: history.go(-1)" class="btn btn-secondary btn-sm">
                                <i data-feather="arrow-left-circle"></i> Back
                            </a>
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
                        <div class="col-3" data-select2-id="102"></div>
                        <div class="col-6" data-select2-id="102">
                            <div class="card" data-select2-id="101">
                                <div class="card-body customernewsection-form" data-select2-id="56">
                                    <div class="row" data-select2-id="100">
                                        <div class="col-md-12">
                                            <div class="newheader  border-bottom mb-2 pb-25">
                                                <h4 class="card-title text-theme">Basic Information</h4>
                                                <p class="card-text">Fill the details</p>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-4">
                                                    <label class="form-label">
                                                        Name <span class="text-danger">*</span>
                                                    </label>
                                                </div>
                                                <div class="col-md-8">
                                                    <input type="text" name="name" class="form-control" id="name">
                                                </div>
                                            </div>    
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
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mt-2">
                                                <!-- <div class="step-custhomapp bg-light">
                                                    <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                                                        <li class="nav-item">
                                                            <a class="nav-link active" data-bs-toggle="tab" href="#Approval">Process</a>
                                                        </li>
                                                    </ul>
                                                </div> -->
                                                <div class="tab-content">
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
                                                                        <th>#</th>
                                                                        <th>Level<span class="text-danger">*</span></th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
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
                    $(this).find('input[name^="level-index"]').each(function () {
                        // Update the 'name' attribute to reflect the new index
                        this.name = this.name.replace(/\[\d+\]/, `[${newIndex}]`);

                        if (this.name.includes('level')) {
                            this.value = `${newIndex}`;
                        }
                    });

                    // Update child rows for this level
                    $(this).nextUntil('.approvlevelflow').each(function () {
                        $(this).attr('data-index', newIndex);
                        $(this).attr('data-detail-id', newIndex);
                        $(this).find('select[name^="level-index"]').each(function () {
                            this.name = this.name.replace(/\[\d+\]/, `[${newIndex}]`);
                        });

                        // Update data-level-id and data-detail-id
                        $(this).find('.consumption-checkbox').attr('data-level-id', newIndex);
                        $(this).find('input[name^="level-index"]').each(function () {
                            this.name = this.name.replace(/\[\d+\]/, `[${newIndex}]`);
                        });
                    });
                }
            });

            const newLevel = `
                <tr class="approvlevelflow" data-index="${levelId}" data-detail-count="${levelId}">
                    <td>
                        ${levelId}
                    </td>
                    <td>
                        <input type="text" class="form-control mw-100" name="levels[${levelId}][name]">
                        <input type="hidden" name="levels[${levelId}][level-index]" value="${levelId}">
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
        }

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

        // Delete Level (Parent + Children) with SweetAlert2 confirmation
        $('#levelTable').on('click', '.deleteLevel', function () {
            let levelId = $(this).data('index'); // Use 'data-index'

            Swal.fire({
                title: "Are you sure?",
                text: "This will delete the level !",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!",
                cancelButtonText: "Cancel"
            }).then((result) => {
                if (result.isConfirmed) {
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
                        text: "The level have been removed.",
                        icon: "success",
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
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

                $(this).find('input[name^="level-index"]').each(function () {
                    this.name = this.name.replace(/\[\d+\]/, `[${newCounter}]`);
                    if (this.name.includes('level')) {
                        this.value = `${newCounter}`;
                    }
                });
                
                newCounter++;
            });

            levelCounter = newCounter;
        }
    });

</script>
@endsection
