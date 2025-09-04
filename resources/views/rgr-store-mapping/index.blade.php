@extends('layouts.app')

@section('content')
    <!-- BEGIN: Content-->
    <form class="ajax-input-form" method="POST" action="{{ route('store-mappings.store') }}" data-redirect="{{ url('/rgr-store-mappings') }}" id="wipStoreMappingForm">
        @csrf
        <div class="app-content content ">
            <div class="content-overlay"></div>
            <div class="header-navbar-shadow"></div>
            <div class="content-wrapper container-xxl p-0">
                <div class="content-header pocreate-sticky">
                    <div class="row">
                        <div class="content-header-left col-md-6 mb-2">
                            <div class="row breadcrumbs-top">
                                <div class="col-12">
                                    <h2 class="content-header-title border-0 float-start mb-0">RGR Store Mapping</h2>
                                </div>
                            </div>
                        </div>
                        <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                            <div class="form-group breadcrumb-right">
                                <button type="button" class="btn btn-outline-danger btn-sm mb-50 mb-sm-0" id="deleteRows"><i data-feather="x-circle"></i> Delete</button>
                                <button type="button" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0" id="addRow"><i data-feather="plus"></i> Add New</button>
                                <button type="submit" form="wipStoreMappingForm" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Save</button>
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
                                        <div>
                                            <div class="step-custhomapp bg-light">
                                                <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" data-bs-toggle="tab" href="#RGR">WIP</a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="nav-link" data-bs-toggle="tab" href="#Damage">Damage</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="tab-content pb-1">
                                                <div class="tab-pane active" id="RGR">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="table-responsive pomrnheadtffotsticky">
                                                                <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad" id="wipTable">
                                                                    <thead>
                                                                        <tr>
                                                                            <th class="customernewsection-form">
                                                                                <div class="form-check form-check-primary custom-checkbox">
                                                                                    <input type="checkbox" class="form-check-input" id="selectAll">
                                                                                    <label class="form-check-label" for="selectAll"></label>
                                                                                </div>
                                                                            </th>
                                                                            <th>Category<span class="text-danger">*</span></th>
                                                                            <th>Location<span class="text-danger">*</span></th>
                                                                            <th>RGR Store<span class="text-danger">*</span></th>
                                                                            <th>QC Store</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="wipRows">
                                                                        @foreach($storeMappings as $index => $mapping)
                                                                           <input type="hidden" name="store_mappings[{{ $index }}][id]" value="{{ $mapping->id }}">
                                                                            <tr data-id="{{ $mapping->id ?? '' }}">
                                                                                <td class="customernewsection-form">
                                                                                    <div class="form-check form-check-primary custom-checkbox">
                                                                                        <input type="checkbox" class="form-check-input row-checkbox">
                                                                                    </div>
                                                                                </td>
                                                                                <td class="poprod-decpt">
                                                                                    <input type="text" placeholder="Select Category"class="form-control mw-100 ledgerselecct mb-25 item-category-autocomplete" value="{{ $mapping->category?->name }}" data-id="{{ $mapping->category_id }}">
                                                                                    <input type="hidden" name="store_mappings[{{ $index }}][category_id]" value="{{ $mapping->category_id }}" class="category-id-hidden">
                                                                                </td>
                                                                               <td class="poprod-decpt">
                                                                                    <input type="text" placeholder="Select Location" class="form-control mw-100 ledgerselecct mb-25 store-autocomplete"
                                                                                        value="{{ $mapping->store?->store_name ?? '' }}" data-id="{{ $mapping->store_id }}">
                                                                                    <input type="hidden" name="store_mappings[{{ $index }}][store_id]" value="{{ $mapping->store_id }}" class="store-id-hidden">
                                                                                </td>
                                                                                <td class="poprod-decpt">
                                                                                    <input type="text" placeholder="Select RGR Store" class="form-control mw-100 ledgerselecct mb-25 substore-autocomplete"
                                                                                        value="{{ $mapping->subStore?->name ?? '' }}" data-id="{{ $mapping->sub_store_id }}">
                                                                                    <input type="hidden" name="store_mappings[{{ $index }}][sub_store_id]" value="{{ $mapping->sub_store_id }}" class="substore-id-hidden">
                                                                                </td>
                                                                                <td class="poprod-decpt">
                                                                                    <input type="text" placeholder="Select QC Store" class="form-control mw-100 ledgerselecct mb-25 qcsubstore-autocomplete"
                                                                                        value="{{ $mapping->qcSubStore?->name ?? '' }}" data-id="{{ $mapping->qc_sub_store_id }}">
                                                                                    <input type="hidden" name="store_mappings[{{ $index }}][qc_sub_store_id]" value="{{ $mapping->qc_sub_store_id }}" class="qcsubstore-id-hidden">
                                                                                </td>
                                                                                <input type="hidden" name="store_mappings[{{ $index }}][id]" value="{{ $mapping->id }}">
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <!-- Damage tab as original -->
                                                 {{-- Damage Tab --}}
                                                <div class="tab-pane" id="Damage">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div class="table-responsive pomrnheadtffotsticky">
                                                                <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>S.No</th>
                                                                            <th>Damage Type</th>
                                                                            <th>Location</th>
                                                                            <th>Store</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody class="mrntableselectexcel">
                                                                        @foreach($damageNatures as $index => $nature)
                                                                            @php
                                                                                $existing = $damageMappings->firstWhere('damage_type', $nature['value']);
                                                                            @endphp
                                                                            <tr>
                                                                                <td class="fw-bolder text-dark">{{ $index + 1 }}</td>
                                                                                <input type="hidden" name="damage_mappings[{{ $index }}][id]" value="{{ $existing?->id }}">
                                                                                <td class="poprod-decpt">
                                                                                    <input type="text" value="{{ $nature['label'] }}" disabled class="form-control mw-100 ledgerselecct mb-25" />
                                                                                    <input type="hidden" name="damage_mappings[{{ $index }}][damage_type]"  value="{{ $nature['value'] }}">
                                                                                </td>
                                                                               <td class="poprod-decpt">
                                                                                    <input type="text" placeholder="Select Location" class="form-control mw-100 ledgerselecct mb-25 damage-location-autocomplete" value="{{ $existing?->store?->store_name ?? '' }}" data-id="{{ $existing?->store_id }}">
                                                                                    <input type="hidden" name="damage_mappings[{{ $index }}][store_id]" value="{{ $existing?->store_id }}" class="damage-store-id-hidden">
                                                                                </td>

                                                                                <td class="poprod-decpt">
                                                                                    <input type="text" placeholder="Select Store" class="form-control mw-100 ledgerselecct mb-25 damage-substore-autocomplete" value="{{ $existing?->subStore?->name ?? '' }}" data-id="{{ $existing?->sub_store_id }}">
                                                                                    <input type="hidden" name="damage_mappings[{{ $index }}][sub_store_id]"  value="{{ $existing?->sub_store_id }}" class="damage-substore-id-hidden">
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
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
        </div>
    </form>
    <!-- END: Content-->
@endsection

@section('scripts')

<script>
let wipRowIdx = {{ count($storeMappings) ?? 0 }};

$('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
    let target = $(e.target).attr("href"); 
    if (target === "#Damage") {
        $("#addRow").prop("disabled", true).addClass("d-none");
        $("#deleteRows").prop("disabled", true).addClass("d-none");
    } else {
        $("#addRow").prop("disabled", false).removeClass("d-none");
        $("#deleteRows").prop("disabled", false).removeClass("d-none");
    }
});
// ------------------ COMMON AUTOCOMPLETE FUNCTION ------------------
function initAutocomplete(selector, url, extraDataFunc = null) {
    $(document).on('focus', selector, function () {
        let $input = $(this);
        if ($input.hasClass('ui-autocomplete-input')) return;

        $input.autocomplete({
            source: function (request, response) {
                let data = { term: request.term };
                if (extraDataFunc) {
                    Object.assign(data, extraDataFunc($input));
                }
                $.get(url, data, function (res) {
                    if (res.status) {
                        response(res.data.map(opt => ({
                            label: opt.label,
                            value: opt.label,
                            id: opt.value
                        })));
                    } else {
                        response([{
                            label: res.message || "No record found",
                            value: "",
                            disabled: true  
                        }]);
                    }
                });
            },
            minLength: 0,
            select: function (event, ui) {
                if (ui.item.disabled) { 
                    event.preventDefault();
                    return false;
                }
                $input.val(ui.item.label);
                $input.attr('data-id', ui.item.id);
                let hiddenInput = $input.closest('td').find('input[type="hidden"]');
                hiddenInput.val(ui.item.id);

                if ($input.hasClass('store-autocomplete')) {
                    let $row = $input.closest('tr');
                    $row.find('.substore-autocomplete, .qcsubstore-autocomplete').val('').attr('data-id', '');
                    $row.find('.substore-id-hidden, .qcsubstore-id-hidden').val('');
                }

                return false;
            }
        })
        .data("ui-autocomplete")._renderItem = function (ul, item) {
            let $li = $("<li>");
            if (item.disabled) {
                $li.addClass("ui-state-disabled");
            }
            return $li.append("<div>" + item.label + "</div>").appendTo(ul);
        };

        $input.on('focus', function () { 
            $(this).autocomplete('search', ''); 
        });
    });
}

// ------------------ WIP AUTOCOMPLETE ------------------
initAutocomplete('.item-category-autocomplete', "{{ route('autocomplete.categories') }}");
initAutocomplete('.store-autocomplete', "{{ route('autocomplete.stores') }}");
initAutocomplete('.substore-autocomplete', "{{ route('autocomplete.substores') }}", function($input){
    return {store_id: $input.closest('tr').find('.store-autocomplete').attr('data-id')};
});
initAutocomplete('.qcsubstore-autocomplete', "{{ route('autocomplete.substores') }}", function($input){
    return {store_id: $input.closest('tr').find('.store-autocomplete').attr('data-id')};
});

// ------------------ DAMAGE AUTOCOMPLETE ------------------
initAutocomplete('.damage-location-autocomplete', "{{ route('autocomplete.stores') }}");
initAutocomplete('.damage-substore-autocomplete', "{{ route('autocomplete.substores') }}", function($input){
    return {store_id: $input.closest('tr').find('.damage-location-autocomplete').attr('data-id')};
});

// ------------------ ADD NEW ROW ------------------
$('#addRow').on('click', function () {
    let row = `
    <tr>
        <td class="customernewsection-form">
            <div class="form-check form-check-primary custom-checkbox">
                <input type="checkbox" class="form-check-input row-checkbox">
            </div>
        </td>
        <td class="poprod-decpt">
            <input type="text" placeholder="Select Category" class="form-control mw-100 ledgerselecct mb-25 item-category-autocomplete" data-id="">
            <input type="hidden" name="store_mappings[${wipRowIdx}][category_id]" value="" class="category-id-hidden">
        </td>
        <td class="poprod-decpt">
            <input type="text" placeholder="Select Location" class="form-control mw-100 ledgerselecct mb-25 store-autocomplete" data-id="">
            <input type="hidden" name="store_mappings[${wipRowIdx}][store_id]" value="" class="store-id-hidden">
        </td>
        <td class="poprod-decpt">
            <input type="text" placeholder="Select RGR Store" class="form-control mw-100 ledgerselecct mb-25 substore-autocomplete" data-id="">
            <input type="hidden" name="store_mappings[${wipRowIdx}][sub_store_id]" value="" class="substore-id-hidden">
        </td>
        <td class="poprod-decpt">
            <input type="text" placeholder="Select QC Store" class="form-control mw-100 ledgerselecct mb-25 qcsubstore-autocomplete" data-id="">
            <input type="hidden" name="store_mappings[${wipRowIdx}][qc_sub_store_id]" value="" class="qcsubstore-id-hidden">
        </td>
        <input type="hidden" name="store_mappings[${wipRowIdx}][id]" value="">
    </tr>`;
    $('#wipRows').append(row);
    wipRowIdx++;
});

// ------------------ BULK DELETE ------------------
$('#deleteRows').on('click', function () {
    let $checkedRows = $('#wipRows').find('.row-checkbox:checked');

    if ($checkedRows.length === 0) {
        Swal.fire('No rows selected!', 'Please select at least one row.', 'info');
        return;
    }

    $checkedRows.each(function () {
        let $tr = $(this).closest('tr');
        let mappingId = $tr.find('input[name*="[id]"]').val();

        if (!mappingId) {
            $tr.remove();
        }
    });

    let idsToDelete = $checkedRows.map(function () {
        let mappingId = $(this).closest('tr').find('input[name*="[id]"]').val();
        return mappingId ? mappingId : null;
    }).get();

    if (idsToDelete.length === 0) {
        return; 
    }

    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you really want to delete selected mappings?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete!',
        cancelButtonText: 'No, keep it'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("store-mappings.destroy") }}',
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}',
                    ids: idsToDelete
                },
                success: function (res) {
                    if (res.status) {
                        $checkedRows.each(function () {
                            let $tr = $(this).closest('tr');
                            let mappingId = $tr.find('input[name*="[id]"]').val();
                            if (idsToDelete.includes(mappingId)) {
                                $tr.remove();
                            }
                        });

                        Swal.fire('Deleted!', res.message || 'Deleted successfully.', 'success')
                            .then(() => {
                                location.reload();  
                            });
                    } else {
                        Swal.fire('Failed!', res.message || 'Delete failed!', 'error');
                    }
                },
                error: function () {
                    Swal.fire('Failed!', 'Server error occurred.', 'error');
                }
            });
        }
    });
});

// ------------------ SELECT ALL ------------------
$('#selectAll').on('change', function(){
    let checked = $(this).is(':checked');
    $('#wipRows').find('.row-checkbox').prop('checked', checked);
});

// ------------------ RESET SUBSTORE FIELDS ON STORE CHANGE ------------------
$(document).on('change keyup', '.store-autocomplete', function () {
    let $row = $(this).closest('tr');
    $(this).val('').attr('data-id', '');
    $row.find('.store-id-hidden').val('');

    $row.find('.substore-autocomplete').val('').attr('data-id', '');
    $row.find('.substore-id-hidden').val('');

    $row.find('.qcsubstore-autocomplete').val('').attr('data-id', '');
    $row.find('.qcsubstore-id-hidden').val('');
});
// ------------------ RESET DAMAGE FIELDS ON LOCATION CHANGE ------------------
$(document).on('change keyup', '.damage-location-autocomplete', function () {
    let $row = $(this).closest('tr');
    $(this).val('').attr('data-id', '');
    $row.find('.damage-store-id-hidden').val('');
    $row.find('.damage-substore-autocomplete').val('').attr('data-id', '');
    $row.find('.damage-substore-id-hidden').val('');
});
</script>
@endsection