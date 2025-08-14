@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <form class="ajax-input-form" method="POST" action="{{ route('payment-terms.store') }}" data-redirect="{{ url('/payment-terms') }}">
        @csrf
        <div class="app-content content">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 col-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title float-start mb-0">Payment Terms</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('payment-terms.index') }}">Home</a></li>
                                            <li class="breadcrumb-item active">Add New</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                            <a href="{{ route('payment-terms.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
                            <button type="submit" class="btn btn-primary btn-sm"><i data-feather="check-circle"></i> Create</button>
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
                                                <div class="newheader border-bottom mb-2 pb-25">
                                                    <h4 class="card-title text-theme">Basic Information</h4>
                                                    <p class="card-text">Fill the details</p>
                                                </div>
                                            </div>
                                            <div class="col-md-9">
                                                <!-- Title Section -->
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Title <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="name" class="form-control" placeholder="Enter Name" value="{{ old('name') }}" />
                                                        @error('name')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>


                                                <!-- Alias Section -->
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Alias</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="alias" class="form-control" placeholder="Enter Alias" value="{{ old('alias') }}" />
                                                        @error('alias')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <!-- Status Section -->
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Status</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="demo-inline-spacing">
                                                            @foreach ($status as $statusOption)
                                                                <div class="form-check form-check-primary mt-25">
                                                                    <input
                                                                        type="radio"
                                                                        id="status_{{ $statusOption }}"
                                                                        name="status"
                                                                        value="{{ $statusOption }}"
                                                                        class="form-check-input"
                                                                        {{ $statusOption == 'active' ? 'checked' : '' }}
                                                                    >
                                                                    <label class="form-check-label fw-bolder" for="status_{{ $statusOption }}">
                                                                        {{ ucfirst($statusOption) }}
                                                                    </label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        @error('status')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <!-- Term Details Section -->
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-9">
                                                        <div class="table-responsive-md">
                                                            <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Installation No</th>
                                                                        <th>Trigger Type</th> 
                                                                        <th>Payment Days</th> 
                                                                        <th>Payment %</th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody id="description-box">
                                                                    <tr>
                                                                        <td>
                                                                            <input type="hidden" name="term_details[0][installation_no]" class="installation-no-hidden text-end" value="1" />
                                                                            <span class="installation-no-display">1</span>
                                                                        </td>
                                                                         <td>
                                                                            <select name="term_details[0][trigger_type]" class="form-control trigger-type mw-100">
                                                                                <option value="" disabled>Select Trigger Type</option>
                                                                                @foreach ($triggerTypes as $type)
                                                                                    <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        
                                                                        <td><input type="number" name="term_details[0][term_days]" class="form-control term-days mw-100 text-end" placeholder="Enter Term Days" /></td>
                                                                        <td>
                                                                            <input type="number" name="term_details[0][percent]" class="form-control percent mw-100 text-end" step="0.01" placeholder="Enter Percent" />
                                                                        </td>
                                                                       
                                                                        <td>
                                                                            <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
                                                                            <a href="#" class="text-primary add-row"><i data-feather="plus-square"></i></a>
                                                                        </td>
                                                                    </tr>
                                                                     <tr id="total-row">
                                                                        <td colspan="3" class="text-end font-weight-bold">Total Percent:</td>
                                                                        <td style="text-align: -webkit-right;">
                                                                            <input type="text" id="total-percent" class="form-control text-end" readonly />
                                                                        </td>
                                                                        <td></td>
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
                    </section>
                </div>
            </div>
        </div>
    </form>
@endsection
@section('scripts')
<script>
$(document).ready(function() {
    var $tableBody = $('#description-box');
    var $totalPercentInput = $('#total-percent');
    function calculateTotalPercent() {
        var total = 0;
        $tableBody.find('.percent').each(function() {
            var value = parseFloat($(this).val());
            if (!isNaN(value)) {
                total += value;
            }
        });
        return total;
    }

    function updateTotalPercentDisplay() {
        var total = calculateTotalPercent();
        $totalPercentInput.val(total.toFixed(2));
    }

    function validateTotalPercent() {
        var total = calculateTotalPercent();
        if (total > 100) {
            alert('Total percent cannot exceed 100.');
            return false;
        }
        return true;
    }

    function updateRowIndices() {
        $tableBody.find('tr').not('#total-row').each(function(index) {
            var $row = $(this);
            $row.find('.installation-no-hidden').val(index + 1); 
            $row.find('.installation-no-display').text(index + 1);
            $row.find('input[name], select[name]').each(function() {
                var name = $(this).attr('name');
                $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
            });
            $row.find('.delete-row').show();
            $row.find('.add-row').toggle(index === 0);
        });
    }

    function updateTermDaysVisibility() {
        $tableBody.find('tr').not('#total-row').each(function() {
            var $row = $(this);
            var triggerType = $row.find('.trigger-type').val();
            var termDaysInput = $row.find('.term-days');
            if (triggerType === 'on delivery') {
                termDaysInput.val('0').prop('disabled', true);
            } else {
                termDaysInput.prop('disabled', false);
            }

        });
    }

    function updateInstallationNumbers() {
        $tableBody.find('tr').not('#total-row').each(function(index) {
            $(this).find('.installation-no-hidden').val(index + 1);
            $(this).find('.installation-no-display').text(index + 1);
        });
    }
    $('.add-row').on('click', function(e) {
        e.preventDefault();
        var $newRow = $(this).closest('tr').clone();
        var rowCount = $tableBody.find('tr').not('#total-row').length;
        $newRow.find('[name]').each(function() {
            var name = $(this).attr('name');
            $(this).attr('name', name.replace(/\[\d+\]/, '[' + rowCount + ']'));
        });
        $newRow.find('input').val('');
        $newRow.find('select').val('');
        $newRow.find('.percent').val('');
        $tableBody.find('#total-row').before($newRow);
        updateInstallationNumbers();
        updateTotalPercentDisplay();
        updateTermDaysVisibility();
        updateRowIndices();
        feather.replace(); 
    });

    $tableBody.on('input', '.percent', function() {
        var $input = $(this);
        var valid = validateTotalPercent();
        if (!valid) {
            $input.val('');
        }
        updateTotalPercentDisplay();
    });

    $tableBody.on('change', '.trigger-type', function() {
        updateTermDaysVisibility();
    });

    $tableBody.on('click', '.delete-row', function(e) {
        e.preventDefault();
        $(this).closest('tr').remove();
        updateInstallationNumbers();
        updateTotalPercentDisplay();
        updateRowIndices();
    });
    updateInstallationNumbers();
    updateTotalPercentDisplay();
    updateTermDaysVisibility();
});
</script>
<script>
    $(document).ready(function() {
        function applyCapsLock() {
            $('input[type="text"], input[type="number"]').each(function() {
                $(this).val($(this).val().toUpperCase());
            });
            $('input[type="text"], input[type="number"]').on('input', function() {
                var value = $(this).val().toUpperCase();  
                $(this).val(value); 
            });
        }
        applyCapsLock();
    });
 </script>
@endsection

