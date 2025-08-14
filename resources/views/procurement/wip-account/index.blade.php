@extends('layouts.app')

@section('content')
<style>
    .setup-td-width{
      width:200px;  
    }
    .select2-selection__clear {
        display: none;
    }
</style>
<!-- BEGIN: Content -->
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
         <form class="ajax-input-form" method="POST" action="{{ route('wip-accounts.store') }}" data-redirect="{{ url('/wip-accounts') }}" id="wipAccountForm">
          @csrf
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">WIP Account Setup</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                                        <li class="breadcrumb-item active">Setup</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">
                            <button type="submit" class="btn btn-primary btn-sm"><i data-feather="check-circle"></i> Submit</button>
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
                                            <div class="newheader border-bottom mb-2 pb-25">
                                                <h4 class="card-title text-theme">Basic Information</h4>
                                                <p class="card-text">Fill the details</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="table-responsive">
                                                <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                    <thead>
                                                        <tr>
                                                            <th>S.No</th>
                                                            <th>Company<span class="text-danger">*</span></th>
                                                            <th>Organization<span class="text-danger">*</span></th>
                                                            <th>Books</th>
                                                            <th>Ledger<span class="text-danger">*</span></th>
                                                            <th>Ledger Group<span class="text-danger">*</span></th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="wipAccountsTable">
                                                        @if($wipAccount->isEmpty())
                                                            <tr data-index="0" data-id="">
                                                                
                                                                <td>1</td>
                                                                <td>
                                                                   <div class="setup-td-width">
                                                                       <select class="form-select select2" name="wip_accounts[0][company_id]">
                                                                          <option value="">Select Company</option>
                                                                            @foreach($companies as $company)
                                                                                <option value="{{ $company->id }}">{{ $company->name }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="setup-td-width">
                                                                        <select class="form-select select2" name="wip_accounts[0][organization_id]">
                                                                            <option value="">Select Organization</option>
                                                                        </select>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="setup-td-width">
                                                                        <select class="form-select select2" name="wip_accounts[0][book_id][]" multiple>
                                                                            <option value="">Select Book</option>
                                                                        </select>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <div class="setup-td-width">
                                                                        <input type="text" class="form-control autocomplete mw-100 ledger_name" name="wip_accounts[0][ledger_name]" placeholder="Enter Ledger">
                                                                        <input type="hidden" name="wip_accounts[0][ledger_id]" />
                                                                    </div> 
                                                                </td>
                                                                <td>
                                                                    <div class="setup-td-width">
                                                                        <select class="form-select select2 ledger_group_name" 
                                                                                name="wip_accounts[0][ledger_group_id]" 
                                                                                placeholder="Select Ledger Group">
                                                                        </select>
                                                                    </div>
                                                                </td>

                                                                <td>
                                                                    <a href="#" class="text-primary me-50 add-row"><i data-feather="plus-square"></i></a>
                                                                    <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
                                                                </td>
                                                            </tr>
                                                        @else
                                                            @foreach($wipAccount as $index => $item)
                                                                <tr data-index="{{ $index }}" data-id="{{ $item->id }}">
                                                                    <td>{{ $index + 1 }}</td>
                                                                    <input type="hidden" name="wip_accounts[{{ $index }}][id]" value="{{ $item->id }}">
                                                                    <td>
                                                                       <div class="setup-td-width">
                                                                            <select class="form-select select2 company-select" name="wip_accounts[{{ $index }}][company_id]" data-row="{{ $index }}">
                                                                                <option value="">Select Company</option>
                                                                                @foreach($companies as $company)
                                                                                    <option value="{{ $company->id }}" {{ $company->id == $item->company_id ? 'selected' : '' }}>
                                                                                        {{ $company->name }}
                                                                                    </option>
                                                                                @endforeach
                                                                            </select>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <div class="setup-td-width">
                                                                            <select class="form-select select2 organization-select" name="wip_accounts[{{ $index }}][organization_id]" data-row="{{ $index }}">
                                                                                    <option value="">Select Organization</option>
                                                                                    @if($item->company && $item->company->organizations) 
                                                                                        @foreach($item->company->organizations as $organization)
                                                                                          @if (in_array($organization->id, $orgIds))
                                                                                            <option value="{{ $organization->id }}" {{ $organization->id == $item->organization_id ? 'selected' : '' }}>
                                                                                                {{ $organization->name }}
                                                                                            </option>
                                                                                          @endif
                                                                                        @endforeach
                                                                                    @else
                                                                                        <option value="">No Organizations Available</option>
                                                                                    @endif
                                                                            </select>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                    <div class="setup-td-width">
                                                                            <select class="form-select select2 book-select" name="wip_accounts[{{ $index }}][book_id][]" multiple data-row="{{ $index }}">
                                                                                    <option value="">Select Book</option>
                                                                                    @if($item->organization && $item->organization->books->isNotEmpty())
                                                                                        @foreach($item->organization->books as $book)
                                                                                            <option value="{{ $book->id }}" {{ in_array($book->id, is_array($item->book_id) ? $item->book_id : json_decode($item->book_id, true) ?? []) ? 'selected' : '' }}>
                                                                                                {{ $book->book_code }}
                                                                                            </option>
                                                                                        @endforeach
                                                                                    @elseif($item->organization && $item->organization->books->isEmpty())
                                                                                        @foreach($erpBooks as $book) 
                                                                                            <option value="{{ $book->id }}" {{ in_array($book->id, is_array($item->book_id) ? $item->book_id : json_decode($item->book_id, true) ?? []) ? 'selected' : '' }}>
                                                                                                {{ $book->book_code }}
                                                                                            </option>
                                                                                        @endforeach
                                                                                    @else
                                                                                        <option value="">No Books Available</option>
                                                                                    @endif
                                                                                </select>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="setup-td-width">
                                                                                <input type="text" class="form-control autocomplete mw-100 ledger_name"  name="wip_accounts[{{ $index }}][ledger_name]"  value="{{ $item->ledger->name ?? '' }}" placeholder="Enter Ledger">
                                                                                <input type="hidden" name="wip_accounts[{{ $index }}][ledger_id]" value="{{ $item->ledger_id ?? '' }}" />
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <div class="setup-td-width">
                                                                                <select class="form-select select2 ledger_group_name" 
                                                                                            name="wip_accounts[{{ $index }}][ledger_group_id]" 
                                                                                            placeholder="Select Ledger Group">
                                                                                        @if($item->ledger) 
                                                                                            @php
                                                                                                $groups = $item->ledger->groups(); 
                                                                                            @endphp

                                                                                            @if($groups->isNotEmpty())
                                                                                                @foreach($groups as $ledgerGroup)
                                                                                                    <option value="{{ $ledgerGroup->id }}" 
                                                                                                            {{ $ledgerGroup->id == $item->ledger_group_id ? 'selected' : '' }}>
                                                                                                        {{ $ledgerGroup->name }}
                                                                                                    </option>
                                                                                                @endforeach
                                                                                            @else
                                                                                                <option value="">No Ledger Groups Available</option>
                                                                                            @endif
                                                                                        @else
                                                                                            <option value="">No Ledger Available</option>
                                                                                        @endif
                                                                                </select>
                                                                            </div>
                                                                        </td>
                                                                        <td>
                                                                            <a href="#" class="text-primary me-50 add-row"><i data-feather="plus-square"></i></a>
                                                                            <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
                                                                        </td>
                                                                </tr>
                                                            @endforeach
                                                        @endif
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
          </form>
        </div>
    </div>

@endsection
@section('scripts')
<script>
 document.addEventListener("DOMContentLoaded", function () {
   function initializeSelect2() {
        $("select").select2({
            allowClear: true
        }).on("select2:unselecting", function(e) {
            $(this).data('state', 'unselected');
        }).on("select2:open", function(e) {
            if ($(this).data('state') === 'unselected') {
                $(this).removeData('state'); 

                var self = $(this);
                setTimeout(function() {
                    self.select2('close');
                }, 1);
            }    
        });
    }
    initializeSelect2();
    feather.replace();
    document.querySelector('tbody').addEventListener('click', function (e) {
        if (e.target.closest('.add-row')) {
            e.preventDefault();
            const row = e.target.closest('tr');
            addRow(row);
        }
        if (e.target.closest('.delete-row')) {
            e.preventDefault();
            const row = e.target.closest('tr');
            const wipAccountId = row.dataset.id;
            if (wipAccountId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: 'Are you sure you want to delete this Record?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, keep it'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/wip-accounts/${wipAccountId}`, {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                        })
                        .then(response => response.json())
                        .then(response => {
                            if (response.status) {
                                row.remove();
                                Swal.fire('Deleted!', response.message, 'success');
                                updateRowIndexes();
                            } else {
                                Swal.fire('Error!', response.message || 'Could not delete wip account.', 'error');
                            }
                        })
                        .catch(() => {
                            Swal.fire('Error!', 'An error occurred while deleting the wip account.', 'error');
                        });
                    }
                });
            } else {
                row.remove();
                updateRowIndexes();
            }
        }
    });
    function addRow(clickedRow) {
        const tableBody = document.querySelector('#wipAccountsTable');
        const rows = tableBody.querySelectorAll('tr');
        const rowIndex = Array.from(rows).indexOf(clickedRow);
        const newRow = document.createElement('tr');
        newRow.dataset.id = '';
        newRow.innerHTML = `
            <td>${rowIndex + 1}</td> <!-- Using index as the row number -->
            <td>
                <div class="setup-td-width">
                    <select class="form-select select2" name="wip_accounts[${rowIndex}][company_id]">
                        <option value="">Select Company</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}">{{ $company->name }}</option>
                        @endforeach
                    </select>
                </div>
            </td>
            <td>
               <div class="setup-td-width">
                    <select class="form-select select2" name="wip_accounts[${rowIndex}][organization_id]">
                        <option value="">Select Organization</option>
                    </select>
                </div>
            </td>
            <td>
               <div class="setup-td-width">
                    <select class="form-select select2" name="wip_accounts[${rowIndex}][book_id][]" multiple>
                        <option value="">Select Book</option>
                    </select>
                </div>
            </td>
            <td>
              <div class="setup-td-width">
                <input type="text" class="form-control autocomplete  mw-100 ledger_name" name="wip_accounts[${rowIndex}][ledger_name]" placeholder="Enter Ledger">
                <input type="hidden" name="wip_accounts[${rowIndex}][ledger_id]" />
              </div>
            </td>
             <td>
               <div class="setup-td-width">
                    <select class="form-control select2  mw-100 ledger_group_name" name="wip_accounts[${rowIndex}][ledger_group_id]" placeholder="Select Ledger Group">
                    </select>
                </div>
            </td>
            <td>
                <a href="#" class="text-primary me-50 add-row"><i data-feather="plus-square"></i></a>
                <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
            </td>
        `;
        clickedRow.insertAdjacentElement('afterend', newRow);
         initializeSelect2();
        feather.replace();
        const allRows = tableBody.querySelectorAll('tr');
        updateRowIndexes();
    }
    function updateRowIndexes() {
        const tableBody = document.querySelector('#wipAccountsTable');
        const rows = tableBody.querySelectorAll('tr');
        rows.forEach((row, index) => {
            const rowIndexCell = row.querySelector('td');
            const deleteButton = row.querySelector('.delete-row');
            if (rowIndexCell) {
                rowIndexCell.textContent = index + 1;
            }
            row.querySelectorAll('input[name], select[name]').forEach(field => {
                const name = field.getAttribute('name');
                const updatedName = name.replace(/\[\d+\]/, `[${index}]`);
                field.setAttribute('name', updatedName);
            });
            if (rows.length === 1) {
                deleteButton.style.display = 'none';
            } else {
                deleteButton.style.display = 'inline-block';
            }
            $(row).find('.item-select').select2({
            templateSelection: function (selectedOption) {
                return $(selectedOption.element).data('item-code');
            },
            templateResult: function (option) {
                var itemName = $(option.element).text();
                return itemName;
            }
           });
        });
    }
    updateRowIndexes();
    $(document).on('change', '[name^="wip_accounts"][name$="[company_id]"]', function () {
        var companyId = $(this).val();
        var $row = $(this).closest('tr');
        var organizationSelect = $row.find('[name$="[organization_id]"]');
        
        organizationSelect.empty().append('<option value="">Select Organization</option>');
        
        if (companyId) {
            $.get(`/stock-accounts/organizations/${companyId}`, function (data) {
                if (data && data.organizations) {
                    data.organizations.forEach(function (org) {
                        organizationSelect.append(`<option value="${org.id}">${org.name}</option>`);
                    });
                    organizationSelect.select2();
                } else {
                    Swal.fire('Error!', 'No organizations found for the selected company.', 'error');
                }
            }).fail(function () {
                Swal.fire('Error!', 'An error occurred while loading organizations.', 'error');
            });
        }
    });
    $(document).on('change', '[name$="[organization_id]"]', function () {
        var organizationId = $(this).val();
        var $row = $(this).closest('tr');
        var categoryInput = $row.find('[name$="[category_name]"]');
        var categoryIdInput = $row.find('[name$="[category_id]"]');
        var ledgerInput = $row.find('[name$="[ledger_name]"]');
        var ledgerIdInput = $row.find('[name$="[ledger_id]"]');
        var itemSelect = $row.find('[name$="[item_id][]"]');
        var bookSelect = $row.find('[name$="[book_id][]"]');
        categoryInput.val('');
        categoryIdInput.val('');
        ledgerInput.val('');
        ledgerIdInput.val('');
        itemSelect.empty().append('<option value="">Select Item</option>');
        bookSelect.empty().append('<option value="">Select Book</option>');
        $.get(`/stock-accounts/data-by-organization/${organizationId}`,{organizationId:organizationId}, function (data) {
            categoryInput.autocomplete({
                source: function (request, response) {
                    if (data.categories.length === 0) {
                        response([{ label: "No records found", value: "" }]);
                    } else {
                        response($.map(data.categories, function (category) {
                            return {
                                label: category.name,
                                value: category.id
                            };
                        }));
                    }
                },
                minLength: 2,
                select: function (event, ui) {
                    if (ui.item.value === "") return false;
                    categoryInput.val(ui.item.label);
                    categoryIdInput.val(ui.item.value);
                    categoryIdInput.trigger('change');
                    return false;
                }
            });
            ledgerInput.autocomplete({
                source: function (request, response) {
                    if (data.ledgers.length === 0) {
                        response([{ label: "No records found", value: "" }]);
                    } else {
                        response($.map(data.ledgers, function (ledger) {
                            return {
                                label: ledger.name + " (" + ledger.code + ")",
                                value: ledger.name,
                                id: ledger.id 
                            };
                        }));
                    }
                },
                minLength: 2,
                select: function (event, ui) {
                    if (ui.item.value === "") return false;
                    ledgerInput.val(ui.item.value);
                    ledgerIdInput.val(ui.item.id);
                    ledgerIdInput.trigger('change');
                    return false;
                }
            });
            bookSelect.empty().append('<option value="">Select Book</option>');
            data.erpBooks.forEach(function (book) {
                bookSelect.append(`<option value="${book.id}">${book.book_code}</option>`);
            });
            
            itemSelect.empty().append('<option value="">Select Item</option>');
            data.items.forEach(function (item) {
                itemSelect.append(`<option value="${item.id}" data-item-code="${item.item_code}">${item.item_name} (${item.item_code})</option>`);
            });
            itemSelect.select2({
                templateSelection: function (selectedOption) {
                    return $(selectedOption.element).data('item-code');
                }
            });
            subCategorySelect.empty().append('<option value="">Select Subcategory</option>');
        }).fail(function () {
            Swal.fire('Error!', 'An error occurred while loading data for the selected organization.', 'error');
        });
    });
    
    $(document).on('focus input', '[name$="[ledger_name]"]', function () {
        var $input = $(this);
        var $row = $input.closest('tr');
        var ledgerIdInput = $row.find('[name$="[ledger_id]"]');
        var ledgerGroupSelect = $row.find('[name$="[ledger_group_id]"]');
        var organizationId = $row.find('[name$="[organization_id]"]').val();
        var searchTerm = $input.val();  
        ledgerIdInput.val(''); 
        ledgerGroupSelect.empty();
        $input.next('.no-records').remove(); 
        if (!organizationId) {
            return;
        }
        $.get(`/stock-accounts/ledgers-by-organization/${organizationId}`, { search: searchTerm,organizationId:organizationId }, function (data) {
            var results = data.ledgers && data.ledgers.length > 0 ? 
                $.map(data.ledgers, function (ledger) {
                    return {
                        label: ledger.name + " (" + ledger.code + ")",
                        value: ledger.name,
                        id: ledger.id 
                    };
                }) : 
                [{ label: "No records found", value: "" }];
            
            $input.autocomplete({
                source: function(request, response) {
                    response(results);  
                },
                minLength: 0, 
                select: function(event, ui) {
                    if (ui.item.value === "") return false;  
                    $input.val(ui.item.value); 
                    ledgerIdInput.val(ui.item.id); 
                    ledgerIdInput.trigger('change');
                    return false; 
                }
            }).autocomplete("search", searchTerm);

        }).fail(function () {
            $input.autocomplete({
                source: function(request, response) {
                    response([{ label: "No records found", value: "" }]);
                },
                minLength: 0, 
                select: function(event, ui) {
                    if (ui.item.value === "") return false;  
                    $input.val(ui.item.label); 
                    ledgerIdInput.val(ui.item.value); 
                    ledgerIdInput.trigger('change');
                    return false;
                }
            });
            Swal.fire('Error!', 'An error occurred while fetching ledgers for the organization.', 'error');
        });
    });

    $(document).on('change input', '[name$="[ledger_id]"]', function () {
        var selectedLedgerId = $(this).val(); 
        var $row = $(this).closest('tr');
        var ledgerGroupSelect = $row.find('[name$="[ledger_group_id]"]'); 
        var ledgerGroupNameInput = $row.find('[name$="[ledger_group_name]"]');
        ledgerGroupSelect.empty();
        ledgerGroupNameInput.val('');
        
        if (selectedLedgerId) {
            $.get('/stock-accounts/ledgers-by-group', {
                ledger_id: selectedLedgerId
            }, function (data) {
                if (data && data.ledgerGroupData && data.ledgerGroupData.length > 0) {
                    data.ledgerGroupData.forEach(function (ledgerGroup) {
                        ledgerGroupSelect.append(`<option value="${ledgerGroup.id}">${ledgerGroup.name}</option>`);
                    });
                } else {
                    Swal.fire('No Ledger Groups Found', 'No ledger groups available for the selected ledger.', 'warning');
                }
            }).fail(function () {
                Swal.fire('Error!', 'An error occurred while fetching the ledger groups for the selected ledger.', 'error');
            });
        }
    });

    $('.item-select').select2({
        templateSelection: function (selectedOption) {
            return $(selectedOption.element).data('item-code');
        },
        templateResult: function (option) {
            var itemName = $(option.element).text();
            return itemName;
        }
    });
    
 });
</script>
@endsection
