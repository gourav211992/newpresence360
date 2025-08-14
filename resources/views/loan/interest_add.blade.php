@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 col-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">New Interest Rate</h2>
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
                    <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                        <div>
                            <button onClick="javascript: history.go(-1)" class="btn btn-secondary btn-sm"><i
                                    data-feather="arrow-left-circle"></i> Back</button>
                            <button type="submit" form="interest-create" class="btn btn-primary btn-sm"><i
                                    data-feather="check-circle"></i> Create</button>
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

                                    <form id="interest-create" method="POST" action="{{ route('loan.interest-create') }}">
                                        @csrf
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="newheader  border-bottom mb-2 pb-25">
                                                    <h4 class="card-title text-theme">Basic Information</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                            </div>

                                            <div class="col-md-9">

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Base Rate % <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="number" value="{{ old('base_rate_val', $base_rate) }}"
                                                            name="base_rate_val" id="base_rate" class="form-control" />
                                                        @error('base_rate_val')
                                                            <span class="alert alert-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Effective from <span
                                                                class="text-danger">*</span></label>
                                                    </div>

                                                    <div class="col-md-5">
                                                        <input type="date" name="effective_from"
                                                            value="{{ old('effective_from') }}"
                                                            class="form-control effective_from" />
                                                        @error('effective_from')
                                                            <span class="alert alert-danger">{{ $message }}</span>
                                                        @enderror
                                                    </div>
                                                </div>


                                            </div>

                                            <div class="col-md-9">
                                                <div class="table-responsive">
                                                    <table
                                                        class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border">
                                                        <thead>
                                                            <tr>
                                                                <th>#</th>
                                                                <th width="70px">CIBIL Score Min Range</th>
                                                                <th width="70px">CIBIL Score Max Range</th>
                                                                <th width="70px">Risk Cover %</th>
                                                                <th width="100px">Base Rate %</th>
                                                                <th width="100px">Interst Rate % (Base Rate + Risk Cover)
                                                                </th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="table-body">
                                                            <tr>
                                                                <td class="row-number">1</td>
                                                                <td>
                                                                    <input type="text" name="cibil_score_min[]"
                                                                        id="cibil_score_min" class="form-control mw-100">
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="cibil_score_max[]"
                                                                        id="cibil_score_max" class="form-control mw-100">
                                                                </td>
                                                                <td>
                                                                    <input type="number" name="risk_cover[]"
                                                                        id="risk_cover_dis" class="form-control mw-100">
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="base_rate[]"
                                                                        value="{{ $base_rate }}" id="base_rate_dis"
                                                                        readonly class="form-control mw-100">
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="interest_rate[]"
                                                                        id="interest_rate" readonly
                                                                        class="form-control mw-100">
                                                                </td>
                                                                <td><a href="#" class="text-success add-row"><i
                                                                            data-feather="plus-square"></i></a></td>
                                                            </tr>

                                                        </tbody>


                                                    </table>
                                                </div>

                                            </div>

                                        </div>

                                    </form>





                                    <div class="row">



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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $("#base_rate").blur(function() {
                var value = $(this).val();
                var risk_cover_data = $("#risk_cover_dis").val();
                if (risk_cover_data !== "") {
                    var risk_cover_num = parseFloat(risk_cover_data) || 0;
                    var base_rate_num = parseFloat(value) || 0;
                    var interest_rate_data = risk_cover_num + base_rate_num;
                    $("#base_rate_dis").val(base_rate_num);
                    $("#interest_rate").val(interest_rate_data.toFixed(2).replace(/\.?0+$/, '') + '%');
                } else {
                    $("#base_rate_dis").val(value);
                }
            });

            $("#risk_cover_dis").blur(function() {
                var risk_cover_dis = parseFloat($(this).val()) || 0;
                var base_rate_val = $("#base_rate").val();

                if (base_rate_val !== "") {
                    var base_rate_num = parseFloat(base_rate_val) || 0;
                    var interest_rate = risk_cover_dis + base_rate_num;
                    $("#interest_rate").val(interest_rate.toFixed(2).replace(/\.?0+$/, '') + '%');
                }
            });

            $(".effective_from").blur(function() {
                var effective_from = $(this).val();
                $("#effective_from").val(effective_from);
            });

            feather.replace();


            let prevValues = []; // Array to store min and max values for each row

            function validateCibil(currentRow, currentRowIndex = null) {
                let min_cibil = parseInt($(currentRow).find('#cibil_score_min').val());
                let max_cibil = parseInt($(currentRow).find('#cibil_score_max').val());

                if (!isNaN(min_cibil) && !isNaN(max_cibil)) {
                    if ($('#table-body tr').length > 1) {

                        // Iterate over previous values
                        for (let i = 0; i < prevValues.length; i++) {
                            // Skip the current row (if editing an existing row)
                            if (currentRowIndex !== null && i === currentRowIndex) {
                                continue;
                            }

                            let prevMin = prevValues[i].min;
                            let prevMax = prevValues[i].max;
                            // Check if the current min/max overlaps with any previous range
                            if ((min_cibil >= prevMin && min_cibil <= prevMax) || (max_cibil >= prevMin &&
                                    max_cibil <=
                                    prevMax)) {
                                alert('CIBIL score range is overlapping with previous row data.');
                                return false; // Prevent adding if the range overlaps
                            }
                        }
                    }
                } else {
                    alert('Please enter valid CIBIL score range');
                    return false;
                }

                // If validation passed, either update or add the new row's min/max in the array
                if (currentRowIndex !== null) {
                    // Update existing row's min/max in the array
                    prevValues[currentRowIndex] = {
                        min: min_cibil,
                        max: max_cibil
                    };
                } else {
                    // Add new row's min/max in the array
                    prevValues.push({
                        min: min_cibil,
                        max: max_cibil
                    });
                }

                return true;
            }


            $('#table-body').on('click', '.add-row', function(e) {
                e.preventDefault();

                if ($("#base_rate").val() != "") {
                    $("#base_rate").prop('readonly', true);
                }

                if ($(".effective_from").val() !== "") {
                    $(".effective_from").prop('readonly', true);
                }
                let $currentRow = $(this).closest('tr');
                var $newRow = $currentRow.clone();
                $newRow.find('.add-row').removeClass('add-row').addClass('delete-item');

                var isValid = $currentRow.find('input').filter(function() {
                    return $(this).val().trim() !== '';
                }).length > 0;

                if (!isValid) {
                    alert('At least one field must be filled before adding a new row.');
                    return;
                }

                if (validateCibil($currentRow) == false) {
                    return;
                }

                $currentRow.find('input').val('');
                $currentRow.find('input#base_rate_dis').val($("#base_rate").val());
                $currentRow.find('input#base_rate_dis').prop('readonly', true);
                $currentRow.find('input#interest_rate').prop('readonly', true);
                $newRow.find('input').prop('readonly', true);
                var nextIndex = $('#table-body tr').length + 1;
                $newRow.find('.row-number').text(nextIndex);
                $newRow.find('.delete-item').removeClass('add-row').addClass('text-danger delete-item')
                    .html('<i data-feather="trash-2"></i>');

                $('#table-body').append($newRow);

                feather.replace();
            });

            // Delete row function
            function deleteRow(rowIndex) {
                prevValues.splice(rowIndex, 1); // Remove the corresponding value from the array
                $('#table-body tr').eq(rowIndex).remove(); // Remove the row from the table
                updateRowIndexes(); // Update indexes after deletion
            }

            // Function to handle updating the row indexes for delete buttons
            function updateRowIndexes() {
                $('#table-body tr').each(function(index) {
                    // Update the delete button with the correct index for each row
                    $(this).find('.delete-button').attr('data-index', index);
                });
            }

            $('#table-body').on('click', '.delete-item', function(e) {
                e.preventDefault();
                let rowIndex = $(this).data('index');
                deleteRow(rowIndex);
                $(this).closest('tr').remove();
                $('#table-body tr').each(function(index) {
                    $(this).find('.row-number').text(index + 1);
                });
                if ($('#table-body tr').length == 1) {
                    $("#base_rate").removeAttr('readonly');
                }
            });
        });
    </script>
@endsection
