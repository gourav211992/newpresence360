@extends('layouts.app')

@section('content')
<!-- BEGIN: Content -->
<form class="ajax-input-form" method="POST" action="{{ route('tax.update', $tax->id) }}" data-redirect="{{ url('/taxes') }}">
    @csrf
    @method('PUT')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Edit Tax</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                        <li class="breadcrumb-item active">Edit</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                    <a href="{{ route('tax.index') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left-circle"></i> Back</a>
                        <button type="button" class="btn btn-danger btn-sm mb-50 mb-sm-0 waves-effect waves-float waves-light delete-btn"
                            data-url="{{ route('tax.destroy', $tax->id) }}" 
                            data-redirect="{{ route('tax.index') }}"
                            data-message="Are you sure you want to delete this tax?">
                            <i data-feather="trash-2" class="me-50"></i> Delete
                        </button>
                        <button type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Update</button>
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
                                                <p class="card-text">Edit the details</p>
                                            </div>
                                        </div>
                                        <div class="col-md-9">
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Tax Group <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <input type="text" name="tax_group" class="form-control" value="{{ $tax->tax_group ??'' }}" />
                                                </div>
                                            </div>
                                            <div class="row mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Description</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <textarea name="description" class="form-control">{{ $tax->description ??'' }}</textarea>
                                                </div>
                                            </div>

                                           {{-- Tax Category --}}
                                            <div class="row mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Tax Category <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                    <select name="tax_category" id="tax_category" class="form-select select2">
                                                        <option value="">Select Tax Category</option>
                                                        @foreach ($taxCategories as $key => $types)
                                                            <option value="{{ $types}}" {{ (isset($tax) && $tax->tax_category === $types) ? 'selected' : '' }}>
                                                                {{ $types }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            {{-- Upper Tax Type --}}
                                            <div class="row mb-1" id="upper-tax-type-row" style="display: none;">
                                                <div class="col-md-3">
                                                    <label class="form-label">Tax Type<span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-md-5">
                                                 
                                                    <select name="tax_type" id="upper_tax_type" class="form-select select2">
                                                        <option value="">Select Tax Type</option>
                                                        @if ($tax->tax_category === 'TDS')
                                                            @foreach ($tdsSections as $key => $value)
                                                                <option value="{{ $key }}" {{ $tax->tax_type == $key ? 'selected' : '' }}>{{ $value }}</option>
                                                            @endforeach
                                                        @elseif ($tax->tax_category === 'TCS')
                                                            @foreach ($tcsSections as $key => $value)
                                                                <option value="{{ $key }}" {{ $tax->tax_type == $key ? 'selected' : '' }}>{{ $value }}</option>
                                                            @endforeach
                                                        @endif
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="row align-items-center mb-1">
                                                <div class="col-md-3">
                                                    <label class="form-label">Status</label>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="demo-inline-spacing">
                                                        @foreach ($statuses as $status)
                                                            <div class="form-check form-check-primary mt-25">
                                                                <input type="radio" id="status_{{ $loop->index }}" name="status" value="{{ $status }}" class="form-check-input"
                                                                {{ $tax->status === $status ? 'checked' : '' }}>
                                                                <label class="form-check-label fw-bolder" for="status_{{ $loop->index }}">{{ ucfirst($status) }}</label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="newheader d-flex justify-content-between align-items-end mt-2 border-top pt-2">
                                                <div class="header-left">
                                                    <h4 class="card-title text-theme">Edit Tax Details</h4>
                                                    <p class="card-text">Edit the details</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="table-responsive-md">
                                                <table class="mt-1 table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable">
                                                    <thead>
                                                        <tr>
                                                            <th>S.NO</th>
                                                            <th>Tax Type <span class="text-danger">*</span></th>
                                                            <th>Tax %age <span class="text-danger">*</span></th>
                                                            <th>Place of Supply <span class="text-danger">*</span></th>
                                                            <th width="200px">Transaction Type</th>
                                                            <th width="200px">Ledger Name</th>
                                                            <th width="200px">Ledger Group</th>
                                                            <th>Applicability Type <span class="text-danger">*</span></th>
                                                            <th>Action</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="tax-details-body">
                                                        @if($tax->taxDetails->isEmpty())
                                                            <tr data-index="0">
                                                                <td>1</td>
                                                                <td>
                                                                    <select name="tax_details[0][tax_type]" id="tax_type_0" class="form-select mw-100 tax-type">
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="tax_details[0][tax_percentage]" step="any" class="form-control mw-100 tax-percentage" value="">
                                                                </td>
                                                                <td>
                                                                    <select name="tax_details[0][place_of_supply]" class="form-select mw-100">
                                                                        <option value="">Select</option>
                                                                        @foreach ($supplyTypes as $type)
                                                                            <option value="{{ $type }}">{{ $type }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <div class="demo-inline-spacing">
                                                                        <div class="form-check form-check-primary mt-25">
                                                                            <input type="checkbox" name="tax_details[0][is_purchase]" value="1" class="form-check-input">
                                                                            <label class="form-check-label fw-bolder">Purchase</label>
                                                                        </div>
                                                                        <div class="form-check form-check-primary mt-25">
                                                                            <input type="checkbox" name="tax_details[0][is_sale]" value="1" class="form-check-input">
                                                                            <label class="form-check-label fw-bolder">Sale</label>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <input type="text" class="autocomplete-ledgr form-control mw-100" data-id="ledger_id_0" value="">
                                                                    <input type="hidden" id="ledger_id_0" name="tax_details[0][ledger_id]" value="">
                                                                </td>
                                                                <td>
                                                                    <select id="ledger_group_id_0" name="tax_details[0][ledger_group_id]" class="form-control mw-100 ledger-group-select">
                                                                    </select>
                                                                </td>
                                                                <td>
                                                                    <div class="demo-inline-spacingg">
                                                                        @foreach ($applicationTypes as $type)
                                                                            <div class="form-check form-check-primary mt-25">
                                                                                <input type="radio" 
                                                                                    id="application_type_{{ $loop->index }}_0" 
                                                                                    name="tax_details[0][applicability_type]" 
                                                                                    class="form-check-input" 
                                                                                    value="{{ $type }}"
                                                                                    {{ $type === 'collection' ? 'checked' : '' }}>
                                                                                <label class="form-check-label fw-bolder" for="application_type_{{ $loop->index }}_0">
                                                                                    {{ ucfirst($type) }}
                                                                                </label>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </td>
                                                                <td>
                                                                    <a href="#" class="text-primary add-row"><i data-feather="plus-square"></i></a>
                                                                    <a href="#" class="text-danger delete-row"><i data-feather="trash-2"></i></a>
                                                                </td>
                                                            </tr>
                                                        @else
                                                            @foreach ($tax->taxDetails as $index => $detail)
                                                                <tr data-index="{{ $index }}" data-id="{{ $detail->id }}">
                                                                 <input type="hidden" name="tax_details[{{ $index }}][id]" value="{{ $detail->id }}">
                                                                    <td>{{ $index + 1 }}</td>
                                                                    <td>
                                                                        <select name="tax_details[{{ $index }}][tax_type]"
                                                                                id="tax_type_{{ $index }}"
                                                                                class="form-select mw-100 tax-type"
                                                                                data-tax-type="{{ $detail->tax_type }}">

                                                                            @if ($tax->tax_category === 'GST')
                                                                                @foreach ($gstSections as $value)
                                                                                    <option value="{{ $value }}" {{ $detail->tax_type == $value ? 'selected' : '' }}>{{ $value }}</option>
                                                                                @endforeach
                                                                            @elseif ($tax->tax_category === 'TDS' || $tax->tax_category === 'TCS')
                                                                                @foreach ($matchedSection as $key => $value)
                                                                                    <option value="{{ $key }}" selected>{{ $value }}</option>
                                                                                @endforeach
                                                                            @endif

                                                                        </select>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" name="tax_details[{{ $index }}][tax_percentage]" class="form-control mw-100 tax-percentage" value="{{ $detail->tax_percentage ?? '' }}">
                                                                    </td>
                                                                    <td>
                                                                        <select name="tax_details[{{ $index }}][place_of_supply]"  id="place_of_supply_{{ $index }}" class="form-select mw-100"  @if ($tax->tax_category === 'TDS' || $tax->tax_category === 'TCS') disabled @endif>
                                                                           
                                                                            <option value="">Select</option>
                                                                            @foreach ($supplyTypes as $type)
                                                                                <option value="{{ $type }}" {{ $detail->place_of_supply === $type ? 'selected' : '' }}>{{ $type ?? '' }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                    </td>
                                                                    <td>
                                                                        <div class="demo-inline-spacing">
                                                                            <div class="form-check form-check-primary mt-25">
                                                                                <input type="checkbox" name="tax_details[{{ $index }}][is_purchase]" value="1" class="form-check-input" {{ $detail->is_purchase ? 'checked' : '' }}>
                                                                                <label class="form-check-label fw-bolder">Purchase</label>
                                                                            </div>
                                                                            <div class="form-check form-check-primary mt-25">
                                                                                <input type="checkbox" name="tax_details[{{ $index }}][is_sale]" value="1" class="form-check-input" {{ $detail->is_sale ? 'checked' : '' }}>
                                                                                <label class="form-check-label fw-bolder">Sale</label>
                                                                            </div>
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <input type="text" class="autocomplete-ledgr form-control mw-100" data-id="ledger_id_{{ $index }}" value="{{ $detail->ledger->name ?? '' }}">
                                                                        <input type="hidden" id="ledger_id_{{ $index }}" name="tax_details[{{ $index }}][ledger_id]" value="{{ $detail->ledger_id ?? '' }}">
                                                                    </td>
                                                                
                                                                    <td>
                                                                        <select id="ledger_group_id_{{ $index }}" name="tax_details[{{ $index }}][ledger_group_id]" class="form-control mw-100 ledger-group-select">
                                                                                @foreach($ledgerGroups as $group)
                                                                                    <option value="{{ $group->id }}" 
                                                                                            {{ isset($detail) && $detail->ledger_group_id == $group->id ? 'selected' : '' }}>
                                                                                        {{ $group->name }}
                                                                                    </option>
                                                                                @endforeach
                                                                        </select>
                                                                        
                                                                        <input type="hidden" id="hidden_ledger_group_id_{{ $index }}" value="{{ $detail->ledger_group_id ?? '' }}">
                                                                    </td>

                                                                    <td>
                                                                        <div class="demo-inline-spacingg">
                                                                            @foreach ($applicationTypes as $type)
                                                                                <div class="form-check form-check-primary mt-25">
                                                                                    <input type="radio" id="application_type_{{ $loop->index }}_{{ $index }}" name="tax_details[{{ $index }}][applicability_type]" class="form-check-input" value="{{ $type }}" {{ $detail->applicability_type === $type ? 'checked' : '' }}>
                                                                                    <label class="form-check-label fw-bolder" for="application_type_{{ $loop->index }}_{{ $index }}">{{ ucfirst($type) }}</label>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    </td>
                                                                    <td>
                                                                        <a href="#" class="text-primary add-row"><i data-feather="plus-square"></i></a>
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
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        var taxCategories = @json($taxCategories);
        var gstSections = @json($gstSections);
        var tdsSections = @json($tdsSections);
        var tcsSections = @json($tcsSections);
        const selectedCategory = $('#tax_category').val();
        const selectedUpperType = "{{ old('tax_type', $tax->tax_type ?? '') }}";
        function ucfirst(str) {
            if (!str) return str; 
            return str.charAt(0).toUpperCase() + str.slice(1);
        }
        var applicationTypes = @json($applicationTypes);
        function initializeAutocomplete(selector, url, hiddenInputId) {
            $(selector).autocomplete({
                source: function(request, response) {
                    var rowIndex = $(this.element).closest('tr').data('index') || $(this.element).closest('tr').index();
                    var taxCategory = $('#tax_category').val();
                    var taxType = $(`#tax_type_${rowIndex}`).val();
                    var taxPercentage = $(`[name='tax_details[${rowIndex}][tax_percentage]']`).val();
                    var isPurchase = $(`[name='tax_details[${rowIndex}][is_purchase]']`).is(':checked');
                    var isSale = $(`[name='tax_details[${rowIndex}][is_sale]']`).is(':checked');
                    var transactionType = '';
                    if (isPurchase) {
                        transactionType = 'purchase';
                    } else if (isSale) {
                        transactionType = 'sale';
                    }
                    $.ajax({
                        url: url,
                        method: 'GET',
                        dataType: 'json',
                        data: { 
                            q: request.term,
                            tax_type: taxType, 
                            tax_category:taxCategory,
                            tax_percentage: taxPercentage,
                            transaction_type: transactionType ,
                            
                        },
                        success: function(data) {
                            if (data.length === 0) {
                            $(selector).attr('placeholder', 'Ledger Not Found');
                            } else {
                                $(selector).removeAttr('placeholder');
                            }
                            response($.map(data, function(item) {
                                return {
                                    id: item.id,
                                    label: item.name,
                                    value: item.name,
                                    code: item.code
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
                    $('#' + hiddenInputId).val(ui.item.id); 
                    updateLedgerGroupDropdown(ui.item.id, $(this).closest('tr')); 
                    return false;
                },
                change: function(event, ui) {
                    if (!ui.item) {
                        $(this).val("");
                        $('#' + hiddenInputId).val(''); 
                    }
                }
            }).focus(function() {
                if (this.value === "") {
                    $(this).autocomplete("search", "");
                }
            });
        }
        function updateLedgerGroupDropdown(ledgerId, $row) {
            var selectedGroupId = $row.find("#hidden_ledger_group_id_" + $row.index()).val(); 
            if (ledgerId) {
                $.ajax({
                    url: '/ledgers/' + ledgerId + '/groups', 
                    method: 'GET',
                    success: function(data) {
                        var ledgerGroupSelect = $row.find(".ledger-group-select");
                        ledgerGroupSelect.empty();
                        
                        if (data && Array.isArray(data)) {
                            data.forEach(function(group) {
                                var isSelected = (String(group.id) === String(selectedGroupId)) ? 'selected' : '';
                                ledgerGroupSelect.append('<option value="' + group.id + '" ' + isSelected + '>' + group.name + '</option>');
                            });
                        } else {
                            console.error("No groups found for this ledger.");
                        }
                    },
                    error: function(xhr) {
                        console.error('Error fetching Ledger Groups:', xhr.responseText);
                        alert('An error occurred while fetching the ledger groups.');
                    }
                });
            }
        }

        function initializeLedgerAutocompleteForRow(selector, rowIndex) {
            initializeAutocomplete(selector, "{{ url('/taxes/search/ledger') }}", "ledger_id_" + rowIndex);
        }
        initializeLedgerAutocompleteForRow(".autocomplete-ledgr", 0);
        function fetchTaxPercentage($row) {
            var taxType = $row.find('select[name^="tax_details"][name*="[tax_type]"]').val(); 
            var taxCategory = $('#tax_category').val(); 

            $.ajax({
                url: '{{ url('/taxes/getTaxPercentage') }}',
                method: 'GET',
                dataType: 'json',
                data: {
                    tax_category: taxCategory,
                    tax_type: taxType 
                },
                success: function(data) {
                    if (data.tax_percentage !== null) {
                        $row.find('input[name^="tax_details"][name*="[tax_percentage]"]').val(data.tax_percentage);
                    } else {
                        $row.find('input[name^="tax_details"][name*="[tax_percentage]"]').val('');
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching tax percentage:', xhr.responseText);
                }
            });
        }

        function initializeLedgerGroupsOnPageLoad() {
            $('#tax-details-body tr').each(function() {
                var ledgerId = $(this).find('input[name^="tax_details"][name$="[ledger_id]"]').val();
                if (ledgerId) {
                    updateLedgerGroupDropdown(ledgerId, $(this)); 
                }
                var rowIndex = $(this).index();
                initializeLedgerAutocompleteForRow($(this).find(".autocomplete-ledgr"), rowIndex);
            });
        }

        function updateTaxTypeDropdown(category, rowIndex, selectedUpperType = '',selectedUpperTypeLabel='') {
            const taxTypeDropdown = $('#tax_type_' + rowIndex);
            const placeOfSupplyInput = $('#place_of_supply_' + rowIndex);
            const selectedValue = taxTypeDropdown.data('selected');
            taxTypeDropdown.empty();
            let sectionList = [];

            if (category === 'GST') {
                sectionList = Array.isArray(gstSections) ? gstSections : Object.values(gstSections);
                placeOfSupplyInput.prop('disabled', false);
                sectionList.forEach(function (type) {
                    taxTypeDropdown.append(
                        $('<option></option>')
                            .val(type)
                            .text(type)
                            .prop('selected', type === selectedValue)
                    );
                });

            } else {
                if (category === 'TDS') {
                    sectionList = Array.isArray(tdsSections) ? tdsSections : Object.values(tdsSections);
                } else if (category === 'TCS') {
                    sectionList = Array.isArray(tcsSections) ? tcsSections : Object.values(tcsSections);
                }

                placeOfSupplyInput.prop('disabled', true);

                if (selectedUpperType) {
                    taxTypeDropdown.append(
                        $('<option></option>')
                            .val(selectedUpperType)
                            .text(selectedUpperTypeLabel)
                            .prop('selected', true)
                    );
                }
                var taxType = taxTypeDropdown.val(); 
                var $row = $('#tax-details-body tr').eq(rowIndex);
                fetchTaxPercentage($row);
            }
        }
        $(document).on('change', '#tax_category', function () {
            const selectedCategory = $(this).val();
            const $upperTypeRow = $('#upper-tax-type-row');
            const $upperTypeSelect = $('#upper_tax_type');

            $upperTypeSelect.empty().append('<option value="">Select Tax Type</option>');

            if (selectedCategory === 'GST') {
                $upperTypeRow.hide();
            } else {
                $upperTypeRow.show();

                let sectionList = [];
                if (selectedCategory === 'TDS') {
                    sectionList = Array.isArray(tdsSections) ? tdsSections : Object.entries(tdsSections);
                } else if (selectedCategory === 'TCS') {
                    sectionList = Array.isArray(tcsSections) ? tcsSections : Object.entries(tcsSections);
                }

                sectionList.forEach(function ([key, label]) {
                    $upperTypeSelect.append(
                        $('<option></option>').val(key).text(label)
                    );
                });
            }

            const selectedUpperType = $upperTypeSelect.val();  
            const selectedUpperTypeLabel = $upperTypeSelect.find('option:selected').text();

            $('#tax-details-body tr').each(function (index) {
                updateTaxTypeDropdown(selectedCategory, index, selectedUpperType,selectedUpperTypeLabel);
            });   
          handleTaxCategory();
        });

        function handleTaxCategory() {
            var selectedCategory = $('#tax_category').val();
            var $rows = $('#tax-details-body tr');

            $rows.each(function() {
                var $row = $(this);
                var deductionRadio = $row.find('input[value="deduction"].form-check-input');
                var collectionRadio = $row.find('input[value="collection"].form-check-input');

                deductionRadio.prop('checked', false).prop('disabled', false);
                collectionRadio.prop('checked', false).prop('disabled', false);

                if (selectedCategory === 'TDS') {
                    deductionRadio.prop('checked', true).prop('disabled', true);
                    collectionRadio.prop('disabled', true);
                } else if (selectedCategory === 'TCS') {
                    collectionRadio.prop('checked', true).prop('disabled', true);
                    deductionRadio.prop('disabled', true);
                } else if (selectedCategory === 'GST') {
                    collectionRadio.prop('checked', true).prop('disabled', true);
                    deductionRadio.prop('disabled', true);
                } else {
                    collectionRadio.prop('checked', true); 
                }
            });
        }

        function validateTaxPercentageConsistency($currentRow) {
            const $rows = $('#tax-details-body tr');
            let firstPercentage = null;
            let percentagesConsistent = true;
            $rows.each(function (index) {
                const currentPercentage = $(this).find('input[name*="[tax_percentage]"]').val();
                if (currentPercentage) {
                    if (firstPercentage === null) {
                        firstPercentage = currentPercentage;
                    } else if (currentPercentage !== firstPercentage) {
                        percentagesConsistent = false;
                        return false;
                    }
                }
            });

            if ($currentRow.is($rows.first())) {
                const newPercentage = $currentRow.find('input[name*="[tax_percentage]"]').val();
                $rows.each(function () {
                    $(this).find('input[name*="[tax_percentage]"]').val(newPercentage);
                });
                percentagesConsistent = true; 
            }
            if (!percentagesConsistent) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Tax Percentages',
                    text: 'All TDS/TCS rows must have the same tax percentage value.',
                }).then(() => {
                    $currentRow.find('input[name*="[tax_percentage]"]').val(firstPercentage);
                });
                return false; 
            }

            return true; 
        }

        function validateTdsTcsRows() {
            const selectedCategory = $('#tax_category').val();
            var rowCount = $('#tax-details-body tr').length;

            if (selectedCategory === 'TDS' || selectedCategory === 'TCS') {
                const $rows = $('#tax-details-body tr'); 
                const rowCount = $rows.length; 

                if (rowCount > 1) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Limit Exceeded',
                        text: 'Only two rows for TDS/TCS are allowed: one for Sale and one for Purchase.',
                    });
                    return false;
                }

                let saleCount = 0;
                let purchaseCount = 0;
                let hasInvalidRow = false;

                $rows.each(function () {
                    const isSale = $(this).find('input[name$="[is_sale]"]').is(':checked');
                    const isPurchase = $(this).find('input[name$="[is_purchase]"]').is(':checked');

                    if (isSale && isPurchase) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Selection',
                            text: 'Both Sale and Purchase cannot be selected in the same row.',
                        });
                        hasInvalidRow = true;
                        return false; 
                    }

                    if (isSale) saleCount++;
                    if (isPurchase) purchaseCount++;
                });

                if (hasInvalidRow) {
                    return false;
                }

                if (saleCount > 1 || purchaseCount > 1) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Too Many Entries',
                        text: 'Only one Sale and one Purchase row are allowed.',
                    });
                    return false;
                }
            }

            return true; 
        }
        $(document).on('change', '#upper_tax_type', function () {
            const selectedCategory = $('#tax_category').val();
            const $upperTypeSelect = $('#upper_tax_type');
            const selectedUpperType = $(this).val();
            const selectedUpperTypeLabel = $upperTypeSelect.find('option:selected').text();
            $('#tax-details-body tr').each(function (index) {
                updateTaxTypeDropdown(selectedCategory, index, selectedUpperType,selectedUpperTypeLabel);
            });
        });

        $(document).on('change', 'input[type="checkbox"][name$="[is_sale]"], input[type="checkbox"][name$="[is_purchase]"]', function () {
            const selectedCategory = $('#tax_category').val();
            if (selectedCategory !== 'TDS' && selectedCategory !== 'TCS') return;

            const $row = $(this).closest('tr');
            const ledgerInput = $row.find('.autocomplete-ledgr');
            const ledgerIdInput = $row.find('input[name*="[ledger_id]"]');
            const ledgerGroupSelect = $row.find('.ledger-group-select');
            ledgerInput.val('');
            ledgerIdInput.val('');
            ledgerGroupSelect.empty();

            const $rows = $('#tax-details-body tr');
            const rowIndex = $rows.index($row);
            initializeLedgerAutocompleteForRow(ledgerInput, rowIndex);

            const isSale = $row.find('input[name$="[is_sale]"]').is(':checked');
            const isPurchase = $row.find('input[name$="[is_purchase]"]').is(':checked');

            // Same row validation
            if (isSale && isPurchase) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Selection',
                    text: 'For TDS/TCS, both Sale and Purchase cannot be selected in the same row.'
                }).then(() => {
                    $(this).prop('checked', false);
                });
                return;
            }

            // Cross-row validation
            let anotherSaleExists = false;
            let anotherPurchaseExists = false;

            $rows.each(function (i, tr) {
                if (tr !== $row[0]) {
                    const saleChecked = $(tr).find('input[name$="[is_sale]"]').is(':checked');
                    const purchaseChecked = $(tr).find('input[name$="[is_purchase]"]').is(':checked');

                    if (saleChecked) anotherSaleExists = true;
                    if (purchaseChecked) anotherPurchaseExists = true;
                }
            });

            if (anotherPurchaseExists && isPurchase) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Selection',
                    text: 'Purchase already selected in another row. Please select Sale in this row.'
                }).then(() => {
                    $(this).prop('checked', false);
                });
                return;
            }

            if (anotherSaleExists && isSale) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid Selection',
                    text: 'Sale already selected in another row. Please select Purchase in this row.'
                }).then(() => {
                    $(this).prop('checked', false);
                });
                return;
            }
        });
        $(document).on('change', '#upper_tax_type', function () {
            const selectedCategory = $('#tax_category').val();
            const $upperTypeSelect = $('#upper_tax_type');
            const selectedUpperType = $(this).val();
            const selectedUpperTypeLabel = $upperTypeSelect.find('option:selected').text();
            $('#tax-details-body tr').each(function (index) {
                updateTaxTypeDropdown(selectedCategory, index, selectedUpperType,selectedUpperTypeLabel);
            });
      });
      $(document).ready(function () {
            const selectedCategory = $('#tax_category').val();
            const selectedUpperType = "{{ old('tax_type', $tax->tax_type ?? '') }}";

            if (selectedCategory === 'TDS' || selectedCategory === 'TCS') {
                $('#upper-tax-type-row').show();
            } else {
                $('#upper-tax-type-row').hide(); 
            }
        });

        $('#tax-details-body').on('input change', '[name*="[tax_type]"], [name*="[tax_percentage]"]', function() {
            var $row = $(this).closest('tr');
            var rowIndex = $row.index();
            $row.find('.autocomplete-ledgr').val('');
            $row.find('[name="tax_details[' + rowIndex + '][ledger_id]"]').val('');
            $row.find('.ledger-group-select').empty();
            initializeLedgerAutocompleteForRow($row.find(".autocomplete-ledgr"), rowIndex);
        });

        $('#tax-details-body').on('blur', 'input[name*="[tax_percentage]"]', function () {
            const selectedCategory = $('#tax_category').val();
            if (selectedCategory === 'TDS' || selectedCategory === 'TCS') {
                const $currentRow = $(this).closest('tr');
                validateTaxPercentageConsistency($currentRow);
            }
        });

        function addRow() {
            if (!validateTdsTcsRows()) return; 
            var newRow = $('#tax-details-body tr:first').clone();
            var rowCount = $('#tax-details-body tr').length;
            newRow.find('td:first').text(rowCount + 1);
            newRow.attr('id', 'row-' + rowCount);

            newRow.find('input[type="checkbox"]').prop('checked', false); 
            newRow.find('input[type="radio"]').prop('checked', false);
            newRow.find('input, select').each(function() {
                $(this).val(''); 
                var id = $(this).attr('id');
                if (id) {
                    $(this).attr('id', id.replace(/\d+$/, rowCount)); 
                }
            });
            newRow.attr('data-id', '');
            newRow.find('.demo-inline-spacingg').empty();
            applicationTypes.forEach(function(type, index) {
                var radioId = 'application_type_' + index + '_' + rowCount; 
                var radioName = 'tax_details[' + rowCount + '][applicability_type]';  
            
                var radioButtonHtml = `
                <div class="form-check form-check-primary mt-25">
                    <input type="radio" id="${radioId}" name="${radioName}" class="form-check-input" value="${type}" ${type === 'collection' ? 'checked' : ''}>
                    <label class="form-check-label fw-bolder" for="${radioId}">${ucfirst(type)}</label>
                </div>
            `;
                newRow.find('.demo-inline-spacingg').append(radioButtonHtml);
            });
            $('#tax-details-body').append(newRow);
            let lastRow = $('#tax-details-body tr').last();
            let selectedCategory =  $('#tax_category').val();  
            let $selectedUpperType = $('#upper_tax_type');  
            let selectedUpperTypeValue = $selectedUpperType.val();  
            let selectedUpperTypeLabel = $selectedUpperType.find('option:selected').text();  
            updateTaxTypeDropdown(selectedCategory, rowCount, selectedUpperTypeValue,selectedUpperTypeLabel);
            initializeLedgerAutocompleteForRow(newRow.find(".autocomplete-ledgr"), rowCount);
            updateRowIndices();
            handleTaxCategory();
        }

        function updateRowIndices() {
            var $rows = $('#tax-details-body tr'); 
            var selectedCategory = $('#tax_category').val();
            $('#tax-details-body tr').each(function(index) {
                $(this).find('td:first').text(index + 1);
                $(this).attr('data-index', index); 
                $(this).find('input, select').each(function() {
                    var name = $(this).attr('name');
                    if (name) {
                        $(this).attr('name', name.replace(/\[\d+\]/, '[' + index + ']'));
                    }
                    var id = $(this).attr('id');
                    if (id) {
                        $(this).attr('id', id.replace(/\d+$/, index));
                    }
                    var dataId = $(this).attr('data-id');
                    if (dataId) {
                        
                        $(this).attr('data-id', dataId.replace(/\d+$/, index)); 
                    }
                });
                $(this).attr('id', 'row-' + index);
                if ($rows.length === 1) {
                    $(this).find('.delete-row').hide(); 
                    $(this).find('.add-row').show(); 
                } else {
                    $(this).find('.delete-row').show(); 
                    $(this).find('.add-row').toggle(index === 0); 
                }
                let $selectedUpperType = $('#upper_tax_type');  
                let selectedUpperTypeValue = $selectedUpperType.val();  
                let selectedUpperTypeLabel = $selectedUpperType.find('option:selected').text();  
                var currentTaxType = $rows.find('select[name^="tax_details"][name$="[tax_type]"]').val();
                if (!currentTaxType) {
                    updateTaxTypeDropdown(selectedCategory, index,selectedUpperTypeValue,selectedUpperTypeLabel);
                }
            });
            handleTaxCategory(); 
        }
        $('#tax-details-body').on('click', '.add-row', function(e) {
            e.preventDefault();
            addRow();
        });
        $('#tax-details-body').on('click', '.delete-row', function(e) {
            e.preventDefault();
            var $row = $(this).closest('tr');
            var taxDetailId = $row.data('id');
            if (taxDetailId) {
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
                            url: '/taxes/tax-detail/' + taxDetailId,
                            type: 'DELETE',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                            },
                            success: function(response) {
                                if (response.status) {
                                    $row.remove();
                                    Swal.fire('Deleted!', response.message, 'success');
                                    updateRowIndices();
                                } else {
                                    Swal.fire('Error!', response.message || 'Could not delete record.', 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON.message || 'An error occurred while deleting the record.', 'error');
                            }
                        });
                    }
                });
            } else {
                $row.remove();
                updateRowIndices();
            }
        });
        initializeLedgerGroupsOnPageLoad();

        function handleCheckboxes() {
            $('#tax-details-body').on('change', 'input[type="checkbox"]', function() {
                if ($(this).is(':checked')) {
                    $(this).val('1');
                } else {
                    $(this).removeAttr('value');
                }
            });
        }

        handleCheckboxes();
        handleTaxCategory();
        updateRowIndices();
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
