@extends('layouts.app')
@section('content')
<form class="ajax-input-form" method="POST" action="{{ route('logistics.freight-charges.store') }}" data-redirect="{{ url('/logistics/freight-charges') }}">
    @csrf
    <!-- BEGIN: Content-->
    <div class="app-content content ">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <div class="row breadcrumbs-top">
                            <div class="col-12">
                                <h2 class="content-header-title float-start mb-0">Freight Master</h2>
                                <div class="breadcrumb-wrapper">
                                    <ol class="breadcrumb">
                                        <li class="breadcrumb-item"><a href="index.html">Home</a></li>  
                                        <li class="breadcrumb-item active">Master</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="content-header-right text-sm-end col-md-6 mb-50 mb-sm-0">
                        <div class="form-group breadcrumb-right">   
                            <button type="submit" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle"></i> Submit</button> 
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
                                    <div class="newheader border-bottom mb-2 pb-25">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h4 class="card-title text-theme">Basic Information </h4>
                                                <p class="card-text">Fill the details</p> 
                                            </div>
                                            <div class="col-md-6 mt-sm-0 mt-50 text-sm-end"> 
                                                <button type="button" class="btn btn-outline-danger btn-sm mb-50 mb-sm-0" id="delete-selected"><i data-feather="x-circle"></i> Delete</button>
                                                <button type="button" class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 add-row"><i data-feather="plus"></i> Add New</button> 
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12"> 
                                            <div class="table-responsive-md">
                                                <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad"> 
                                                    <thead>
                                                        <tr>
                                                            <th class="customernewsection-form">
                                                                <div class="form-check form-check-primary custom-checkbox">
                                                                    <input type="checkbox" class="form-check-input" id="select-all">
                                                                    <label class="form-check-label" for="select-all"></label>
                                                                </div> 
                                                            </th>
                                                            <th >Source <span class="text-danger">*</span></th>
                                                            <th >Destination <span class="text-danger">*</span></th>
                                                            <th width="100px">Distance (KM) <span class="text-danger">*</span></th>  
                                                            <th width="300px">Vehicle Type <span class="text-danger">*</span></th>
                                                            <th width="100px">Bundle (No) <span class="text-danger">*</span></th>
                                                            <th width="100px">Freight (Rs) <span class="text-danger">*</span></th>
                                                            <th width="100px">Per Bundle (Rs)<span class="text-danger">*</span></th>
                                                            <th width="300px">Customer</th>  
                                                        </tr>
                                                    </thead>
                                                   <tbody class="mrntableselectexcel" id="freight-charges-table tbody tr">
                                                      @php $rowIndex = count($freightCharges);  @endphp
                                                        @foreach($freightCharges as  $charges)
                                                            <tr>
                                                                <td>
                                                                    <div class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input row-checkbox" name="row_checkbox[]" value="{{ $rowIndex }}">
                                                                        <label class="form-check-label"></label>
                                                                    </div>

                                                                </td>
                                                                <td>
                                                                    <input type="hidden" name="freight_charges[{{ $rowIndex }}][id]" value="{{ $charges->id ?? '' }}">
                                                                   <input type="text"
                                                                    name="freight_charges[{{ $rowIndex }}][source_route_name]"
                                                                    class="form-control mw-100 route-master-autocomplete"
                                                                    placeholder="Search locations"
                                                                    data-type="source"
                                                                    value="{{ optional($charges->sourceRoute)->name ?? '' }}" />

                                                                <input type="hidden"
                                                                    name="freight_charges[{{ $rowIndex }}][source_route_id]"
                                                                    class="route-master-id"
                                                                    data-type="source"
                                                                    value="{{ $charges->source_route_id ?? '' }}" />
                                                                 </td>
                                                               
                                                                <td>
                                                                <!-- Destination State Autocomplete -->
                                                                    <input type="text"
                                                                        name="freight_charges[{{ $rowIndex }}][destination_route_name]"
                                                                        class="form-control mw-100 route-master-autocomplete"
                                                                        placeholder="Search locations"
                                                                        data-type="destination"
                                                                        value="{{ optional($charges->destinationRoute)->name ?? '' }}" />

                                                                    <input type="hidden"
                                                                        name="freight_charges[{{ $rowIndex }}][destination_route_id]"
                                                                        class="route-master-id"
                                                                        data-type="destination"
                                                                        value="{{ $charges->destination_route_id ?? '' }}" />

                                                               </td>
                                                               
                                                                <td>
                                                                    <input type="text" name="freight_charges[{{ $rowIndex }}][distance]" class="form-control mw-100" value="{{ $charges->distance ?? 0 }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" 
                                                                        name="freight_charges[{{ $rowIndex }}][vehicle_type_name]" 
                                                                        class="form-control mw-100 vehicle-type-autocomplete" 
                                                                        placeholder="Start typing vehicle type..." 
                                                                        value="{{ optional($charges->vehicleType)->name ?? '' }} ({{ optional($charges->vehicleType)->capacity ?? '' }} {{ optional($charges->vehicleType->unit)->name ?? '' }})">

                                                                    <input type="hidden" 
                                                                        name="freight_charges[{{ $rowIndex }}][vehicle_type_id]" 
                                                                        class="vehicle-type-id" 
                                                                        value="{{ $charges->vehicle_type_id }}">

                                                                </td>
                                                                <td>
                                                                    <input type="text" name="freight_charges[{{ $rowIndex }}][no_bundle]" class="form-control mw-100" value="{{ $charges->no_bundle ?? 0 }}">
                                                                </td>
                                                                <td>
                                                                    <input type="text" name="freight_charges[{{ $rowIndex }}][amount]" class="form-control mw-100" value="{{ $charges->amount ?? 0 }}">
                                                                </td>
                                                                 <td>
                                                                    <input type="text" name="freight_charges[{{ $rowIndex }}][per_bundle]" class="form-control mw-100" value="{{ $charges->per_bundle ?? 0 }}">
                                                                </td>
                                                                <td>
                                                                   <input type="text"
                                                                    name="freight_charges[{{ $rowIndex }}][customer_name]"
                                                                    class="form-control mw-100 customer-autocomplete"
                                                                    placeholder="Start typing customer..."
                                                                    value="{{ optional($charges->customer)->company_name ?? '' }}" />

                                                                <input type="hidden"
                                                                    name="freight_charges[{{ $rowIndex }}][customer_id]"
                                                                    class="customer-id"
                                                                    value="{{ $charges->customer_id ?? '' }}" />

                                                              </td>
                                                            </tr>
                                                       @php $rowIndex++; @endphp
                                                       @endforeach
                                                       @if($freightCharges->isEmpty())
                                                           <tr>
                                                                <td>
                                                                    <div class="form-check form-check-primary custom-checkbox">
                                                                        <input type="checkbox" class="form-check-input row-checkbox" name="row_checkbox[]" value="0">
                                                                        <label class="form-check-label"></label>
                                                                    </div>
                                                                </td>

                                                                {{-- Source State --}}
                                                                <td>
                                                                    <input type="text"
                                                                        name="freight_charges[0][source_route_name]"
                                                                        class="form-control mw-100 route-master-autocomplete"
                                                                        placeholder="Search  locations..."
                                                                        data-type="source" />
                                                                    <input type="hidden"
                                                                        name="freight_charges[0][source_route_id]"
                                                                        class="route-master-id"
                                                                        data-type="source" />
                                                                </td>

                                                                {{-- Destination State --}}
                                                                <td>
                                                                    <input type="text"
                                                                        name="freight_charges[0][destination_route_name]"
                                                                        class="form-control mw-100 route-master-autocomplete"
                                                                        placeholder="Search locations."
                                                                        data-type="destination" />
                                                                    <input type="hidden"
                                                                        name="freight_charges[0][destination_route_id]"
                                                                        class="route-master-id"
                                                                        data-type="destination" />
                                                                </td>


                                                                {{-- Distance --}}
                                                                <td>
                                                                    <input type="text"
                                                                        name="freight_charges[0][distance]"
                                                                        class="form-control mw-100"
                                                                       placeholder="Enter Distance">
                                                                </td>

                                                                {{-- Vehicle Type Autocomplete --}}
                                                                <td>
                                                                    <input type="text"
                                                                        name="freight_charges[0][vehicle_type_name]"
                                                                        class="form-control mw-100 vehicle-type-autocomplete"
                                                                        placeholder="Start typing Vehicle Type..." />
                                                                    <input type="hidden"
                                                                        name="freight_charges[0][vehicle_type_id]"
                                                                        class="vehicle-type-id" />
                                                                </td>

                                                                {{-- Bundle --}}
                                                                <td>
                                                                    <input type="text"
                                                                        name="freight_charges[0][no_bundle]"
                                                                        class="form-control mw-100"
                                                                        placeholder="Enter No of Bundle">
                                                                </td>

                                                                {{-- Amount --}}
                                                                <td>
                                                                    <input type="text"
                                                                        name="freight_charges[0][amount]"
                                                                        class="form-control mw-100"
                                                                        placeholder="Enter Amount">
                                                                </td>

                                                                {{-- Per Bundle --}}
                                                                <td>
                                                                    <input type="text"
                                                                        name="freight_charges[0][per_bundle]"
                                                                        class="form-control mw-100"
                                                                        placeholder="Enter Per Bundle Amount">
                                                                </td>

                                                                {{-- Customer Select --}}
                                                                <td>
                                                                    <input type="text"
                                                                    name="freight_charges[0][customer_name]"
                                                                    class="form-control mw-100 customer-autocomplete"
                                                                    placeholder="Start typing customer..." />

                                                                <input type="hidden"
                                                                    name="freight_charges[0][customer_id]"
                                                                    class="customer-id" />
                                                                 </td>
                                                            </tr>

                                                           @php $rowIndex = 1; @endphp
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
    <!-- END: Content-->
</form>
@endsection

@section('scripts')
<script>
    let freightRowIndex = {{ $rowIndex }};

    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('select-all').addEventListener('change', function () {
            document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
        });

        document.querySelector('.add-row').addEventListener('click', addNewRow);
    });

    function addNewRow() {
        const tbody = document.querySelector('.mrntableselectexcel');

        // ✅ Check existing rows for empty required fields
        const existingRows = tbody.querySelectorAll('tr');
        let incomplete = false;

        existingRows.forEach(row => {
            const requiredFields = [
                row.querySelector('.route-master-autocomplete[data-type="source"]'),
                row.querySelector('.route-master-autocomplete[data-type="destination"]'),
                row.querySelector('.vehicle-type-autocomplete'),
                row.querySelector('input[name*="[distance]"]'),
                row.querySelector('input[name*="[amount]"]'),
                row.querySelector('input[name*="[no_bundle]"]'),
                row.querySelector('input[name*="[per_bundle]"]')
            ];

            for (const field of requiredFields) {
                if (field && field.value.trim() === '') {
                    incomplete = true;
                    break;
                }
            }
        });

      if (incomplete) {
            Swal.fire({
                icon: 'warning',
                title: 'Incomplete Row',
                text: 'Please fill all required fields in the existing row(s) before adding a new one.',
                confirmButtonText: 'OK'
            });
            return;
        }


        const newIndex = freightRowIndex++;
        const rowId = 'row' + newIndex;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="form-check form-check-primary custom-checkbox">
                    <input type="checkbox" class="form-check-input row-checkbox" name="row_checkbox[]" value="${newIndex}" id="${rowId}">
                    <label class="form-check-label"></label>
                </div>
            </td>

            <td>
                <input type="hidden" name="freight_charges[${newIndex}][id]" value="">
                <input type="text"
                    name="freight_charges[${newIndex}][source_route_name]"
                    class="form-control mw-100 route-master-autocomplete"
                    placeholder="Search  locations..."
                    data-type="source" />
                <input type="hidden"
                    name="freight_charges[${newIndex}][source_route_id]"
                    class="route-master-id"
                    data-type="source" />
            </td>


            <td>
                <input type="text"
                    name="freight_charges[${newIndex}][destination_route_name]"
                    class="form-control mw-100 route-master-autocomplete"
                    placeholder="Start typing  locations..."
                    data-type="destination" />
                <input type="hidden"
                    name="freight_charges[${newIndex}][destination_route_id]"
                    class="route-master-id"
                    data-type="destination" />
            </td>

            <td width="100px">
                <input type="text"
                    name="freight_charges[${newIndex}][distance]"
                    class="form-control mw-100"
                    placeholder="Enter Distance" />
            </td>

            <td>
                <input type="text"
                    name="freight_charges[${newIndex}][vehicle_type_name]"
                    class="form-control mw-100 ledgerselect vehicle-type-autocomplete"
                    placeholder="Start typing Vehicle Type ..." />
                <input type="hidden"
                    name="freight_charges[${newIndex}][vehicle_type_id]"
                    class="vehicle-type-id" />
            </td>

            <td>
                <input type="text"
                    name="freight_charges[${newIndex}][no_bundle]"
                    class="form-control mw-100"
                    placeholder="Enter No of Bundle" />
            </td>

            <td>
                <input type="text"
                    name="freight_charges[${newIndex}][amount]"
                    class="form-control mw-100"
                    placeholder="Enter Amount" />
            </td>

            <td>
                <input type="text"
                    name="freight_charges[${newIndex}][per_bundle]"
                    class="form-control mw-100"
                    placeholder="Enter Per Bundle Amount" />
            </td>

            <td>
                <div class="d-flex align-items-center gap-1">
                    <input type="text"
                        name="freight_charges[${newIndex}][customer_name]"
                        class="form-control mw-100 customer-autocomplete"
                        placeholder="Start typing customer..." />
                    <input type="hidden"
                        name="freight_charges[${newIndex}][customer_id]"
                        class="customer-id" />
                </div>
            </td>
        `;

        tbody.appendChild(row);

        // ✅ Reinitialize any needed JS (autocomplete etc.)
        if (typeof feather !== 'undefined') feather.replace();
    }
document.addEventListener('input', function (e) {
    if (e.target.matches('input[name^="freight_charges"][name$="[distance]"]')) {
        const value = e.target.value;

        // Allow only digits and max 4 characters
        if (!/^\d{0,4}$/.test(value)) {
            e.target.value = value.slice(0, 4).replace(/\D/g, ''); 
            return;
        }

        const numericValue = parseInt(value);

        // Clear if not in range 1–9999
        if (numericValue < 1 || numericValue > 9999) {
            e.target.value = '';
        }
    }
});

</script>


<script>
    document.getElementById('delete-selected').addEventListener('click', function () {
    const tableBody = document.querySelector('.mrntableselectexcel');
    const checkedRows = tableBody.querySelectorAll('.row-checkbox:checked');

    if (checkedRows.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Selection',
            text: 'Please select at least one row to delete.'
        });
        return;
    }

    const idsToDelete = [];
    checkedRows.forEach(checkbox => {
        const row = checkbox.closest('tr');
        const hiddenId = row.querySelector('input[name^="freight_charges"][name$="[id]"]');
        if (hiddenId && hiddenId.value) {
            idsToDelete.push(hiddenId.value);
        }
    });

    Swal.fire({
        title: 'Are you sure?',
        text: 'Selected records will be permanently deleted!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            if (idsToDelete.length > 0) {
                fetch("{{ route('logistics.freight-charges.delete-multiple') }}", {
                    method: "DELETE",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                    },
                    body: JSON.stringify({ ids: idsToDelete })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        checkedRows.forEach(cb => cb.closest('tr').remove());

                      Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: 'Record deleted successfully.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });

                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Error deleting records.'
                        });
                    }
                })
                .catch(error => {
                    console.error("Delete failed:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An unexpected error occurred.'
                    });
                });
            } else {
                // Just remove UI rows with no DB id
                checkedRows.forEach(cb => cb.closest('tr').remove());

                Swal.fire({
                    icon: 'success',
                    title: 'Deleted!',
                    text: 'Row(s) deleted from the UI.',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        }
    });
});

 const vehicleTypes = [
    @if($vehicleTypes->isNotEmpty())
        @foreach($vehicleTypes as $vt)
            {
                label: "{{ $vt->name }} ({{ $vt->capacity }} {{ $vt->unit->name ?? '' }})",
                value: "{{ $vt->name }} ({{ $vt->capacity }} {{ $vt->unit->name ?? '' }})",
                id: {{ $vt->id }}
            }@if(!$loop->last),@endif
        @endforeach
    @endif
];

$(document).on('focus', '.vehicle-type-autocomplete', function () {
    if (!$(this).data('ui-autocomplete')) {
        $(this).autocomplete({
            source: function (request, response) {
                const results = $.ui.autocomplete.filter(vehicleTypes, request.term);
                if (results.length === 0) {
                    results.push({
                        label: 'No vehicle type found',
                        value: '',
                        id: null
                    });
                }
                response(results);
            },
            minLength: 0,
            select: function (event, ui) {
                if (ui.item.id === null) {
                    event.preventDefault(); // Prevent selecting 'No vehicle type found'
                    return false;
                }
                $(this).val(ui.item.label);
                $(this).closest('tr').find('.vehicle-type-id').val(ui.item.id);
                return false;
            }
        }).focus(function () {
            $(this).autocomplete('search', '');
        });
    }
});


const routeMasters = [
    @foreach($routeMasters as $rm)
        {
            label: "{{ $rm->name }}",
            value: "{{ $rm->name }}",
            id: {{ $rm->id }}
        },
    @endforeach
];

$(document).on('focus', '.route-master-autocomplete', function () {
    const $input = $(this);

    if (!$input.data('ui-autocomplete')) {
        $input.autocomplete({
            minLength: 0,
            source: function (request, response) {
                const term = $.ui.autocomplete.escapeRegex(request.term);
                const matcher = new RegExp(term, "i");

                const matches = $.grep(routeMasters, function (item) {
                    return matcher.test(item.label);
                });

                if (matches.length) {
                    response(matches);
                } else {
                    response([{
                        label: "No matching location found",
                        value: "",
                        id: ""
                    }]);
                }
            },
            select: function (event, ui) {
                if (ui.item.id === "") {
                    event.preventDefault(); // Prevent selection
                    $input.val('');
                    $input.closest('tr').find('.route-master-id[data-type="' + $input.data('type') + '"]').val('');
                } else {
                    $input.val(ui.item.label);
                    $input.closest('tr').find('.route-master-id[data-type="' + $input.data('type') + '"]').val(ui.item.id);
                }
                return false;
            },
            change: function (event, ui) {
                if (!ui.item || ui.item.id === "") {
                    $input.closest('tr').find('.route-master-id[data-type="' + $input.data('type') + '"]').val('');
                }
            }
        }).focus(function () {
            $(this).autocomplete('search', '');
        });
    }
});

</script>

<script>

  const customerList = [
    @foreach($customers as $customer)
        {
            label: "{{ addslashes($customer->company_name) }}",
            value: "{{ addslashes($customer->company_name) }}",
            id: {{ $customer->id }}
        },
    @endforeach
];

$(document).ready(function () {

    // Initialize autocomplete on focus
    $(document).on('focus', '.customer-autocomplete', function () {
        const $input = $(this);

        // Only initialize once
        if (!$input.data('ui-autocomplete')) {
            $input.autocomplete({
                source: function (request, response) {
                    const results = $.ui.autocomplete.filter(customerList, request.term);

                    if (!results.length) {
                        results.push({
                            label: 'No matching customer found',
                            value: '',
                            id: null
                        });
                    }

                    response(results);
                },
                minLength: 0,
                select: function (event, ui) {
                    const $row = $input.closest('tr');

                    if (!ui.item.id) {
                        // Prevent selection if it's the "Not Found" item
                        event.preventDefault();
                        return false;
                    }

                    $input.val(ui.item.label);
                    $row.find('.customer-id').val(ui.item.id);
                    return false;
                }
            }).focus(function () {
                $(this).autocomplete('search', '');
            });
        }
    });

    // Clear customer ID if input is changed manually
    $(document).on('input', '.customer-autocomplete', function () {
        const $input = $(this);
        const $row = $input.closest('tr');
        const currentVal = $input.val().trim();

        const matchedCustomer = customerList.find(c => c.label === currentVal);

        if (!matchedCustomer) {
            $row.find('.customer-id').val('');
        }
    });

});


function checkDuplicateFreight() {
    let rows = [];
    let hasDuplicate = false;

    $('#freight-charges-table tbody tr').each(function () {
        let source = $(this).find('.route-master-id[data-type="source"]').val();
        let destination = $(this).find('.route-master-id[data-type="destination"]').val();
        let vehicleType = $(this).find('.vehicle-type-id').val();
        let customerId = $(this).find('.customer-id').val() || null;

        if (!source || !destination || !vehicleType) return;

        let key = `${source}-${destination}-${vehicleType}-${customerId}`;

        if (rows.includes(key)) {
            $(this).find('.customer-id').addClass('is-invalid');
            $(this).find('.duplicate-error').remove();
            $(this).find('.customer-id').after('<span class="text-danger duplicate-error">Duplicate freight charge entry.</span>');
            hasDuplicate = true;
        } else {
            rows.push(key);
            $(this).find('.customer-id').removeClass('is-invalid');
            $(this).find('.duplicate-error').remove();
        }
    });

    return !hasDuplicate;
}

// 🔹 Trigger on blur/change (manual typing)
$(document).on('blur change', '.route-master-id, .vehicle-type-id, .customer-id', checkDuplicateFreight);

// 🔹 Trigger after autocomplete selection
$(document).on('autocompleteselect', '.route-master-id, .vehicle-type-id, .customer-id', function () {
    setTimeout(checkDuplicateFreight, 50); // delay ensures value is set
});



</script>

@endsection
