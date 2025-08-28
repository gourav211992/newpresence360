@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <form class="ajax-input-form" method="POST" action="{{ route('bank.store') }}" data-redirect="{{ url('/banks') }}">
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
                                    <h2 class="content-header-title float-start mb-0">Bank Details</h2>
                                    <div class="breadcrumb-wrapper">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a href="{{ route('bank.index') }}">Home</a></li>
                                            <li class="breadcrumb-item active">Add New</li>
                                        </ol>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-end col-md-6 col-6 mb-2 mb-sm-0">
                            <a href="{{ route('bank.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
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
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label for="organization_id" class="form-label">
                                                            Organization <span class="text-danger">*</span>
                                                        </label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select id="organization_id"  name="organization_id[]" class="form-control select select2" multiple data-placeholder="-- Select Organization(s) --">
                                                            @foreach($allOrganizations as $org)
                                                                <option value="{{ $org->id }}">
                                                                    {{ $org->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="bank_name" class="form-control" placeholder="Enter Bank Name" value="{{ old('bank_name') }}" />
                                                        @error('bank_name')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label class="form-label">Bank Code</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" name="bank_code" class="form-control" placeholder="Enter Bank Code" value="{{ old('bank_code') }}" />
                                                        @error('bank_code')
                                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                                        @enderror
                                                    </div>
                                                </div>
                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label for="ledger_name" class="form-label">Ledger</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <input type="text" id="ledger_name" name="ledger_name" class="form-control bank-ladger-autocomplete" placeholder="Type to search...">
                                                        <input type="hidden" id="ledger_id" name="ledger_id" class="ladger-id">
                                                    </div>
                                                </div>

                                                <div class="row align-items-center mb-1">
                                                    <div class="col-md-3">
                                                        <label for="ledger_group_name" class="form-label">Ledger Group</label>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <select id="ledger_group_name" name="ledger_group_id" class="form-control ledger-group-select">
                                                        </select>
                                                    
                                                    </div>
                                                </div>
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

                                                <div class="table-responsive-md">
                                                    <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                                                        <thead>
                                                            <tr>
                                                                <th>S.NO</th>
                                                                <th>Account Number <span class="text-danger">*</span></th>
                                                                <th>IFSC Code <span class="text-danger">*</span></th>
                                                                <th>Branch Name</th>
                                                                <th>Branch Address <span class="text-danger">*</span></th>
                                                                <th>Ledger Name</th>
                                                                <th>Ledger Group</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody id="sub-category-box">
                                                            <tr>
                                                                <td>1</td>
                                                                <td><input type="text" name="bank_details[0][account_number]" class="form-control mw-100" placeholder="Enter Account Number" /></td>
                                                                <td><input type="text" name="bank_details[0][ifsc_code]" class="form-control mw-100 ifsc-code" placeholder="Enter IFSC Code" /></td>
                                                                <td><input type="text" name="bank_details[0][branch_name]" class="form-control mw-100" placeholder="Enter Branch Name" /></td>
                                                                <td><input type="text" name="bank_details[0][branch_address]" class="form-control mw-100" placeholder="Enter Branch Name" /></td>
                                                                <td>
                                                                    <input type="text" class="autocomplete-ledgr form-control mw-100" data-id="ledger_id_0" placeholder="Enter Ledger ID" value="">
                                                                    <input type="hidden" class="ledger-id" id="ledger_id_0" name="bank_details[0][ledger_id]" value="">
                                                                </td>
                                                                <td>
                                                                    <select  id="ledger_group_id_0" name="bank_details[0][ledger_group_id]" class="form-control mw-100 ledger-group-select2">
                                                                        <option value="">Select Ledger Group</option>
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <a href="#" class="text-primary add-address"><i data-feather="plus-square"></i></a>
                                                                    <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
                                                                </td>
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
                    </section>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    var ledgerName, ledgerId, ledgerGroupName, ledgerGroupId;
    function updateLedgerValues() {
        ledgerName = $('#ledger_name').val(); 
        ledgerId = $('#ledger_id').val();  
        ledgerGroupName = $('#ledger_group_name').val(); 
        ledgerGroupId = $('#ledger_group_name option:selected').val();   
        $('#ledger_id').val(ledgerId);  
        
    }
    function updateLedgerValuesForRow($row) {
        if (!$row.find('.autocomplete-ledgr').val()) {
            $row.find('.autocomplete-ledgr').val(ledgerName);
        }
        if (!$row.find('.ledger-id').val()) {
            $row.find('.ledger-id').val(ledgerId);
        }
        if (!$row.find('.ledger-group-select2').val()) {
            $row.find('.ledger-group-select2').val(ledgerGroupId);
        }
    }
    $('#ledger_name').on("autocompleteselect", function(event, ui) {
        ledgerName = ui.item.label;  
        ledgerId = ui.item.id;  
        $('#ledger_name').val(ledgerName);
        $('#ledger_id').val(ledgerId);  
        updateLedgerValues();  

        var $firstRow = $('#sub-category-box tr:first');
        if (!$firstRow.find('.autocomplete-ledgr').val()) {
            updateLedgerValuesForRow($firstRow);
            updateLedgerGroupDropdown(ledgerId, $firstRow.find('.ledger-group-select2'));
        }
    });

    $('#ledger_group_name').on('change', function(event) {
        ledgerGroupName = $(this).find('option:selected').text(); 
        ledgerGroupId = $(this).val();  
        updateLedgerValues(); 
        updateLedgerValuesForRow($('#sub-category-box tr:first')); 
    });

    function addNewRow($tableBody) {
        var rowCount = $tableBody.children().length;  
        var $currentRow = $tableBody.find('tr:last'); 
        var $newRow = $currentRow.clone();          
          $newRow.find('input').each(function() {
            var name = $(this).attr('name');
            if (name) {
                $(this).attr('name', name.replace(/\[\d+\]/, '[' + rowCount + ']'));  
            }

            var id = $(this).attr('id');
            if (id) {
                $(this).attr('id', id.replace(/\d+$/, rowCount));  
            }
            if ($(this).hasClass('autocomplete-ledgr')) {
                $(this).attr('data-id', 'ledger_id_' + rowCount); 
            }

            $(this).val('');  
            
        });
         $newRow.find('.ledger-group-select2').empty();

        if (ledgerName && ledgerId) {
            $newRow.find('.autocomplete-ledgr').val(ledgerName); 
            $newRow.find('#ledger_id_' + rowCount).val(ledgerId); 
        }
        if (ledgerGroupName && ledgerGroupId) {
            $newRow.find('.ledger-group-select2').val(ledgerGroupId);  
        }

        if (ledgerId) {
            updateLedgerGroupDropdown(ledgerId, $newRow.find('.ledger-group-select2')); 
        }

        $tableBody.append($newRow);
        updateRowIndices($tableBody);  

        feather.replace();  
        initializeLedgerAutocomplete($newRow.find(".autocomplete-ledgr"), rowCount);
        applyCapsLock();
    }

    function fetchIfscDetails(ifscCode, $row) {
        if (!ifscCode) return;
        $.ajax({
            url: '/banks/ifsc/' + ifscCode,
            method: 'GET',
            success: function(data) {
                if (data.status) {
                    $row.find('input[name*="[branch_name]"]').val(data.data.BRANCH);
                    $row.find('input[name*="[branch_address]"]').val(data.data.ADDRESS);
                } else {
                    alert('Invalid IFSC code. Please try again.');
                    $row.find('input[name*="[branch_name]"]').val('');
                    $row.find('input[name*="[branch_address]"]').val('');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error fetching IFSC details:', textStatus, errorThrown);
                alert('An error occurred while fetching IFSC details. Please try again.');
                $row.find('input[name*="[branch_name]"]').val('');
                $row.find('input[name*="[branch_address]"]').val('');
            }
        });
    }
    var $tableBody = $('#sub-category-box');
     $tableBody.on('keyup', 'input.ifsc-code', function() {
        var ifscCode = $(this).val();
        var $row = $(this).closest('tr');
        clearTimeout($.data(this, 'timer'));
        var wait = setTimeout(function() {
            fetchIfscDetails(ifscCode, $row);
        }, 300);
        $(this).data('timer', wait);
    });

    function applyCapsLock() {
        $('input[type="text"], input[type="number"]').each(function() {
            $(this).val($(this).val().toUpperCase());
        });
        $('input[type="text"], input[type="number"]').on('input', function() {
            var value = $(this).val().toUpperCase();  
            $(this).val(value); 
        });
    }
    function updateRowIndices($tableBody) {
        var $rows = $('#sub-category-box tr');
        $tableBody.find('tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
            $(this).find('input, select').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                }
                var id = $(this).attr('id');
                if (id) {
                    $(this).attr('id', id.replace(/\d+$/, index));
                }
            });
            $(this).attr('id', 'row-' + index);
            if ($rows.length === 1) {
                $(this).find('.delete-row').hide(); 
                $(this).find('.add-address').show(); 
            } else {
                $(this).find('.delete-row').show(); 
                $(this).find('.add-address').toggle(index === 0); 
            }
            initializeLedgerAutocomplete($(this).find(".autocomplete-ledgr"), index);
        });
    }

    function initializeLedgerAutocomplete(selector, rowIndex) {
        $(selector).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: "{{ route('bank.ledger.search') }}",
                    method: 'GET',
                    dataType: 'json',
                    data: { q: request.term },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                id: item.id,
                                label: item.name,
                                value: item.name
                            };
                        }));
                    },
                    error: function(xhr) {
                        console.error('Error fetching data:', xhr.responseText);
                    }
                });
            },
            minLength: 0,
            select: function(event, ui) {
                $(this).val(ui.item.label);
                var rowId = $(this).data('id');
                $('#' + rowId).val(ui.item.id);
                var $row = $(this).closest('tr');
                updateLedgerGroupDropdown(ui.item.id, $row.find('.ledger-group-select2'));
                return false;
            }
        }).focus(function() {
            if (this.value === "") {
                $(this).autocomplete("search", "");
            }
        }); 
        $(selector).on('input', function() {
            var ledgerValue = $(this).val();
            var $row = $(this).closest('tr');
            if (!ledgerValue) {
                $(this).val('');
                var ledgerIdInput = $row.find('input[type="hidden"][id^="ledger_id_"]');
                ledgerIdInput.val(null);
                $row.find('.ledger-group-select2').val(null);
            }
        });
    }

    function updateLedgerGroupDropdown(ledgerId, $dropdown) {
        $.ajax({
            url: '/ledgers/' + ledgerId + '/groups',
            method: 'GET',
            success: function(data) {
                $dropdown.empty();  
                if (data && data.length > 0) {
                    data.forEach(function(item) {
                        $dropdown.append(new Option(item.name, item.id));
                    });
                }
            },
            error: function() {
                alert('An error occurred while fetching Ledger Groups.');
            }
        });
    }

    function initializeLedgerGroupsOnPageLoad() {
        $('#tax-details-body tr').each(function() {
            var ledgerId = $(this).find('input[name^="tax_details"][name$="[ledger_id]"]').val();
            if (ledgerId) {
                var ledgerGroupInput = $(this).find(".ledger-group-select2");
                updateLedgerGroupDropdown(ledgerId, ledgerGroupInput);
            }
        });
    }

    if ($tableBody.children().length === 0) addNewRow($tableBody);
    $tableBody.on('click', '.add-address', function(e) {
        e.preventDefault();
        addNewRow($tableBody);
    });

    $tableBody.on('click', '.delete-row', function(e) {
        e.preventDefault();
        $(this).closest('tr').remove(); 
        updateRowIndices($tableBody);
    });
    updateRowIndices($tableBody);
    initializeLedgerGroupsOnPageLoad();
    applyCapsLock();
});
</script>
@endsection
