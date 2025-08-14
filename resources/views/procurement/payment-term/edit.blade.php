@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <form class="ajax-input-form" method="POST" action="{{ route('payment-terms.update', $paymentTerm->id) }}" data-redirect="{{ url('/payment-terms') }}">
        @csrf
        @method('PUT')
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
                                            <li class="breadcrumb-item"><a href="{{ route('payment-terms.index') }}">Payment Terms</a></li>
                                            <li class="breadcrumb-item active">Edit</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                          <a href="{{ route('payment-terms.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
                            <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                                    data-url="{{ route('payment-terms.destroy', $paymentTerm->id) }}" 
                                    data-redirect="{{ route('payment-terms.index') }}"
                                    data-message="Are you sure you want to delete this item?">
                                <i data-feather="trash-2" class="me-50"></i> Delete
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm"><i data-feather="check-circle"></i> Update</button>
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
                                                    <h4 class="card-title text-theme">Edit Payment Term</h4>
                                                    <p class="card-text">Update the details</p>
                                                </div>
                                            </div>
                                            <div class="col-md-9">
                                                <!-- Title Section -->
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Title <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="name" class="form-control" placeholder="Enter Name" value="{{$paymentTerm->name ?? ''}}" />
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
                                                        <input type="text" name="alias" class="form-control" placeholder="Enter Alias" value="{{ $paymentTerm->alias ??''}}" />
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
                                                                        {{ $statusOption == old('status', $paymentTerm->status) ? 'checked' : '' }}
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
                                                                    @forelse ($paymentTerm->details as $index => $detail)
                                                                        <tr data-id="{{ $detail->id }}" data-index="{{ $index }}">
                                                                           <input type="hidden" name="term_details[{{ $index }}][id]" value="{{ $detail->id }}">
                                                                            <td>
                                                                                <input type="hidden" name="term_details[{{ $index }}][installation_no]" class="installation-no-hidden text-end" value="{{ $index + 1 }}" />
                                                                                <span class="installation-no-display">{{ $index + 1 }}</span>
                                                                            </td>
                                                                            <td>
                                                                                <select name="term_details[{{ $index }}][trigger_type]" class="form-control trigger-type mw-100">
                                                                                    <option value="" disabled>Select Trigger Type</option>
                                                                                    @foreach ($triggerTypes as $type)
                                                                                        <option value="{{ $type }}" {{ $type == old('term_details.' . $index . '.trigger_type', $detail->trigger_type) ? 'selected' : '' }}>{{ ucfirst($type) }}</option>
                                                                                    @endforeach
                                                                                </select>
                                                                            </td>
                                                                            <td><input type="number" name="term_details[{{ $index }}][term_days]" class="form-control term-days mw-100 text-end" placeholder="Enter Term Days" value="{{ $detail->term_days ?? ''}}" /></td>
                                                                            <td><input type="number" name="term_details[{{ $index }}][percent]" class="form-control percent mw-100 text-end" step="0.01" placeholder="Enter Percent" value="{{ number_format($detail->percent ?? 0, 2) }}" /></td> 
                                                                                <td>
                                                                                    <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
                                                                                    <a href="#" class="text-primary add-row"><i data-feather="plus-square"></i></a>
                                                                               </td>
                                                                        </tr>
                                                                    @empty
                                                                        <tr data-index="0">
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
                                                                            <td><input type="number" name="term_details[0][percent]" class="form-control percent mw-100 text-end" step="0.01" placeholder="Enter Percent" /></td>
                                                                           
                                                                            <td>
                                                                                <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
                                                                                <a href="#" class="text-primary add-row"><i data-feather="plus-square"></i></a>
                                                                           </td>
                                                                        </tr>
                                                                        
                                                                    @endforelse
                                                                    <tr id="total-row" >
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
        $tableBody.find('tr').each(function() {
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
    $(document).on('click', '.add-row', function(e) {
        e.preventDefault();
        var $newRow = $(this).closest('tr').clone();
        var rowCount = $tableBody.find('tr').not('#total-row').length; 
        $newRow.find('[name]').each(function() {
            var name = $(this).attr('name');
            $(this).attr('name', name.replace(/\[\d+\]/, '[' + rowCount + ']'));
        });
        $newRow.find('input').val('');
        $newRow.attr('data-id', '');
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
    $(document).on('change', '.trigger-type', function() {
        updateTermDaysVisibility();
    });
    $tableBody.on('click', '.delete-row', function(e) {
    e.preventDefault();
    var $row = $(this).closest('tr');
    var paymentTermDetailId = $row.data('id'); 
        if (paymentTermDetailId) {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Are you sure you want to delete this record?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/payment-terms/payment-term-detail/' + paymentTermDetailId,  
                        type: 'DELETE',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'), 
                        },
                        success: function(response) {
                            if (response.status) {
                                $row.remove();
                                Swal.fire('Deleted!', response.message, 'success');
                                updateInstallationNumbers();
                                updateTotalPercentDisplay();
                                updateRowIndices();
                            } else {
                                Swal.fire('Error!', response.message || 'Could not delete payment term detail.', 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON.message || 'An error occurred while deleting the payment term detail.', 'error');
                        }
                    });
                }
            });
        } else {
            $row.remove();
            updateRowIndices();
        }
    });
    updateRowIndices();
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
