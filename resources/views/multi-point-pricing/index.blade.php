@extends('layouts.app')
@section('content')
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
                  <h2 class="content-header-title float-start mb-0">Multi Point Pricing</h2>
                  <div class="breadcrumb-wrapper">
                     <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.html">Home</a>
                        </li>
                        <li class="breadcrumb-item active">Master</li>
                     </ol>
                  </div>
               </div>
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
                                 <a class="nav-link active" data-bs-toggle="tab" href="#Fixed">Fixed</a>
                              </li>
                              <li class="nav-item">
                                 <a class="nav-link" data-bs-toggle="tab" href="#Point">Point</a>
                              </li>
                           </ul>
                        </div>
                        <div class="tab-content pb-1">
                           <div class="tab-pane active" id="Fixed">
                              <div class="text-end mb-50">
                                 <a href="{{route('logistics.multi-point-fixed.create')}}" class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="plus-circle"></i> Add New</a>
                              </div>
                              <div class="row">
                                 <div class="col-md-12">
                                    <div class="table-responsive-md">
                                       <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad" id="multiFixedTable">
                                          <thead>
                                             <tr>
                                                <th>#</th>
                                                <th>Source</th>
                                                <th>Destination</th>
                                                <th>Customer</th>
                                                <th>Locations</th>
                                                <th>Action</th>
                                             </tr>
                                          </thead>
                                          
                                       </table>
                                    </div>
                                 </div>
                              </div>
                           </div>
                           <div class="tab-pane" id="Point">
                              <form class="ajax-input-form" method="POST" action="{{ route('logistics.multi-point.store') }}" data-redirect="{{ url('/logistics/multi-point-pricing') }}">
                              @csrf
                              <div class="text-end mb-50">
                                 <a class="btn btn-outline-danger btn-sm mb-50 mb-sm-0" id="delete-selected"><i data-feather="x-circle"></i> Delete</a>
                                 <a class="btn btn-outline-primary btn-sm mb-50 mb-sm-0 add-row"><i data-feather="plus-square"></i> Add New</a>
                                 <button class="btn btn-primary btn-sm mb-50 mb-sm-0"><i data-feather="check-circle" type="submit"></i> Save</button>
                              </div>
                              <div class="row">
                                 <div class="col-md-12">
                                    <div class="table-responsive-md">
                                       <table class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                                          <thead>
                                             <tr>
                                                <th width="50px" class="customernewsection-form">
                                                   <div class="form-check form-check-primary custom-checkbox">
                                                      <input type="checkbox" class="form-check-input" id="select-all">
                                                      <label class="form-check-label" for="select-all"></label>
                                                   </div>
                                                </th>
                                                <th colspan="2">Source <span class="text-danger">*</span></th>
                                                <th width="100px">Free Point <span class="text-danger">*</span></th>
                                                <th width="150px">Rate <span class="text-danger">*</span></th>
                                                <th width="250px">Customer</th>
                                             </tr>
                                          </thead>
                                          <tbody class="mrntableselectexcel">
                                             @php $rowIndex = 0; @endphp
                                             @foreach($multiPoints as $charges)
                                             <tr>
                                                <td>
                                                    <div class="form-check form-check-primary custom-checkbox">
                                                      <input type="checkbox" class="form-check-input row-checkbox" name="row_checkbox[]" value="{{ $rowIndex }}">
                                                      <label class="form-check-label"></label>
                                                   </div>
                                                </td>
                                                <td width="150px">
                                                   <input type="hidden" name="multi_point[{{ $rowIndex }}][id]" value="{{ old("multi_point.$rowIndex.id", $charges->id) }}">
                                                   <input type="text" name="multi_point[{{ $rowIndex }}][source_state_name]" class="form-control mw-100 state-autocomplete" placeholder="Start typing source state..." data-type="source" value="{{ old("multi_point.$rowIndex.source_state_name", optional($charges->sourceState)->name) }}">
                                                   <input type="hidden" name="multi_point[{{ $rowIndex }}][source_state_id]" class="state-id" data-type="source" value="{{ old("multi_point.$rowIndex.source_state_id", $charges->source_state_id) }}">
                                                </td>
                                                <td width="150px">
                                                   <input type="text" name="multi_point[{{ $rowIndex }}][source_city_name]" class="form-control mw-100 city-autocomplete" placeholder="Start typing source city..." data-type="source" value="{{ old("multi_point.$rowIndex.source_city_name", optional($charges->sourceCity)->name) }}">
                                                   <input type="hidden" name="multi_point[{{ $rowIndex }}][source_city_id]" class="city-id" data-type="source" value="{{ old("multi_point.$rowIndex.source_city_id", $charges->source_city_id) }}">
                                                </td>
                                                <td>
                                                   <input type="text" name="multi_point[{{ $rowIndex }}][free_point]" class="form-control mw-100" value="{{ old("multi_point.$rowIndex.free_point", $charges->free_point) }}">
                                                </td>
                                                <td>
                                                   <input type="text" name="multi_point[{{ $rowIndex }}][amount]" class="form-control mw-100" value="{{ old("multi_point.$rowIndex.amount", $charges->amount) }}">
                                                </td>
                                                <td>
                                                   <input type="text" name="multi_point[{{ $rowIndex }}][customer_name]" class="form-control mw-100 customer-autocomplete" placeholder="Start typing customer..." value="{{ old("multi_point.$rowIndex.customer_name", optional($charges->customer)->company_name) }}">
                                                   <input type="hidden" name="multi_point[{{ $rowIndex }}][customer_id]" class="customer-id" value="{{ old("multi_point.$rowIndex.customer_id", $charges->customer_id) }}">
                                                </td>
                                             </tr>
                                             @php $rowIndex++; @endphp
                                             @endforeach

                                             @if($multiPoints->isEmpty())
                                             <tr>
                                                <td>
                                                   <div class="form-check form-check-primary custom-checkbox">
                                                      <input type="checkbox" class="form-check-input row-checkbox" name="row_checkbox[]" value="0">
                                                      <label class="form-check-label"></label>
                                                   </div>
                                                </td>
                                                <td width="150px">
                                                   <input type="hidden" name="multi_point[0][id]" value="">
                                                   <input type="text" name="multi_point[0][source_state_name]" class="form-control mw-100 state-autocomplete" placeholder="Start typing source state..." data-type="source">
                                                   <input type="hidden" name="multi_point[0][source_state_id]" class="state-id" data-type="source">
                                                </td>
                                                <td width="150px">
                                                   <input type="text" name="multi_point[0][source_city_name]" class="form-control mw-100 city-autocomplete" placeholder="Start typing source city..." data-type="source">
                                                   <input type="hidden" name="multi_point[0][source_city_id]" class="city-id" data-type="source">
                                                </td>
                                                <td><input type="text" name="multi_point[0][free_point]" class="form-control mw-100" value="0"></td>
                                                <td><input type="text" name="multi_point[0][amount]" class="form-control mw-100" value="0"></td>
                                                <td>
                                                   <input type="text" name="multi_point[0][customer_name]" class="form-control mw-100 customer-autocomplete" placeholder="Start typing customer...">
                                                   <input type="hidden" name="multi_point[0][customer_id]" class="customer-id">
                                                </td>
                                             </tr>
                                             @php $rowIndex = 1; @endphp
                                             @endif

                                          </tbody>
                                       </table>
                                    </div>
                                 </div>
                              </div>
                              <form>
                           </div><!----point tab--->
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
@endsection
@section('scripts')
<script>
$(document).ready(function () {
    const dt_fixed_table = $('#multiFixedTable');
    let multiFixedDataTable;

    function renderOrDefault(value) {
        return value ?? 'N/A';
    }

    if (dt_fixed_table.length) {
        multiFixedDataTable = dt_fixed_table.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('logistics.multi-point-pricing.index') }}",
                error: function (xhr, status, error) {
                    console.error("DataTables AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    alert("Failed to load data. Check console for more info.");
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'source', name: 'source', render: renderOrDefault },
                { data: 'destination', name: 'destination', render: renderOrDefault },
                { data: 'customer', name: 'customer', render: renderOrDefault },
                { data: 'locations', name: 'locations', orderable: false, searchable: false },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            dom:
                '<"d-flex justify-content-between align-items-center mx-2 row"' +
                    '<"col-sm-12 col-md-6"l>' +
                    '<"col-sm-12 col-md-3 dt-action-buttons text-end"B>' +
                    '<"col-sm-12 col-md-3"f>>' +
                't' +
                '<"d-flex justify-content-between mx-2 row"' +
                    '<"col-sm-12 col-md-6"i>' +
                    '<"col-sm-12 col-md-6"p>>',
            buttons: [
                {
                    extend: 'collection',
                    className: 'btn btn-outline-secondary dropdown-toggle',
                    text: feather.icons['share'] ? feather.icons['share'].toSvg({ class: 'font-small-4 me-50' }) + ' Export' : 'Export',
                    buttons: [
                        {
                            extend: 'print',
                            text: feather.icons['printer'] ? feather.icons['printer'].toSvg({ class: 'font-small-4 me-50' }) + ' Print' : 'Print',
                            className: 'dropdown-item',
                            title: 'Multi Fixed Pricing',
                            exportOptions: { columns: [0, 1, 2, 3, 4] }
                        },
                        {
                            extend: 'csv',
                            text: feather.icons['file-text'] ? feather.icons['file-text'].toSvg({ class: 'font-small-4 me-50' }) + ' CSV' : 'CSV',
                            className: 'dropdown-item',
                            title: 'Multi Fixed Pricing',
                            exportOptions: { columns: [0, 1, 2, 3, 4] }
                        },
                        {
                            extend: 'excel',
                            text: feather.icons['file'] ? feather.icons['file'].toSvg({ class: 'font-small-4 me-50' }) + ' Excel' : 'Excel',
                            className: 'dropdown-item',
                            title: 'Multi Fixed Pricing',
                            exportOptions: { columns: [0, 1, 2, 3, 4] }
                        },
                        {
                            extend: 'pdf',
                            text: feather.icons['clipboard'] ? feather.icons['clipboard'].toSvg({ class: 'font-small-4 me-50' }) + ' PDF' : 'PDF',
                            className: 'dropdown-item',
                            title: 'Multi Fixed Pricing',
                            exportOptions: { columns: [0, 1, 2, 3, 4] }
                        },
                        {
                            extend: 'copy',
                            text: feather.icons['copy'] ? feather.icons['copy'].toSvg({ class: 'font-small-4 me-50' }) + ' Copy' : 'Copy',
                            className: 'dropdown-item',
                            title: 'Multi Fixed Pricing',
                            exportOptions: { columns: [0, 1, 2, 3, 4] }
                        }
                    ]
                }
            ],
            drawCallback: function () {
                feather.replace();
            },
            language: {
                paginate: {
                    previous: '&nbsp;',
                    next: '&nbsp;'
                }
            },
            search: { caseInsensitive: true }
        });
    }
});
</script>

<script>
    let multiPointRowIndex = {{ $rowIndex ?? 1 }};

const states = [
    @foreach($states as $state)
        { label: "{{ $state->name }}", value: "{{ $state->name }}", id: {{ $state->id }} },
    @endforeach
];

const cityCache = {}; 

$(document).ready(function () {

    // State Autocomplete
  $(document).on('focus', '.state-autocomplete', function () {
    const $input = $(this);

    if (!$input.data('ui-autocomplete')) {
        $input.autocomplete({
            source: states,
            minLength: 0,
            select: function (event, ui) {
                $input.val(ui.item.label);

                // Detect if inside a table row
                const $row = $input.closest('tr');

                if ($row.length) {
                    // Dynamic table row: update only within this row
                    $row.find('.state-id').val(ui.item.id);
                    $row.find('.city-autocomplete').val('');
                    $row.find('.city-id').val('');

                    const $cityInput = $row.find('.city-autocomplete');
                    loadCities(ui.item.id, null, function () {
                        applyCityAutocomplete(ui.item.id, null, $cityInput);
                    });

                } else {
                    // Static section (source/destination): use data-type
                    const type = $input.data('type');
                    $(`.state-id[data-type="${type}"]`).val(ui.item.id);
                    $(`.city-autocomplete[data-type="${type}"]`).val('');
                    $(`.city-id[data-type="${type}"]`).val('');

                    const $cityInput = $(`.city-autocomplete[data-type="${type}"]`);
                    loadCities(ui.item.id, type, function () {
                        applyCityAutocomplete(ui.item.id, type, $cityInput);
                    });
                }

                return false;
            }
        }).focus(function () {
            $(this).autocomplete("search", "");
        });
    }
});



    // City Autocomplete (dynamically binds based on selected state)
  $(document).on('focus', '.city-autocomplete', function () {
    const $input = $(this);
    const $row = $input.closest('tr');

    let stateId;

    if ($row.length) {
        // From dynamic row
        stateId = $row.find('.state-id').val();
    } else {
        // From static field using data-type
        const type = $input.data('type');
        stateId = $(`.state-id[data-type="${type}"]`).val();
    }

    if (!stateId) {
        $input.autocomplete({ source: [] });
        return;
    }

    if (cityCache[stateId]) {
        applyCityAutocomplete(stateId, null, $input);
    } else {
        loadCities(stateId, null, function () {
            applyCityAutocomplete(stateId, null, $input);
        });
    }
});


});

// Load cities via AJAX
function loadCities(stateId, type, callback = null) {
    $.ajax({
        url: "{{ route('logistics.multi-point-fixed.get-cities-by-state') }}",
        method: "GET",
        data: { state_id: stateId },
        success: function (response) {
            if (response.status) {
                cityCache[stateId] = response.data.map(city => ({
                    label: city.name,
                    value: city.name,
                    id: city.id
                }));
                if (callback) callback();
            }
        },
        error: function () {
            alert('Error loading cities');
        }
    });
}

function applyCityAutocomplete(stateId, type, $input) {
    const cities = cityCache[stateId] || [];

    $input.autocomplete({
        source: cities,
        minLength: 0,
        select: function (event, ui) {
            $input.val(ui.item.label);
            const $row = $input.closest('tr');

            if ($row.length) {
                $row.find('.city-id').val(ui.item.id);
            } else {
                const dataType = $input.data('type');
                $(`.city-id[data-type="${dataType}"]`).val(ui.item.id);
            }

            return false;
        }
    }).focus(function () {
        $(this).autocomplete('search', '');
    });
}

    document.addEventListener('DOMContentLoaded', function () {
        const selectAll = document.getElementById('select-all');
        if (selectAll) {
            selectAll.addEventListener('change', function () {
                document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = this.checked);
            });
        }

        document.addEventListener('click', function (e) {
            if (e.target.closest('.add-row')) {
                addNewRow();
            }
        });
    });

    function addNewRow() {
        const tbody = document.querySelector('.mrntableselectexcel');
        if (!tbody) return;

        let incomplete = false;

        tbody.querySelectorAll('tr').forEach(row => {
            const requiredFields = [
                row.querySelector('.state-autocomplete[data-type="source"]'),
                row.querySelector('.city-autocomplete[data-type="source"]'),
                row.querySelector('input[name*="[free_point]"]'),
                row.querySelector('input[name*="[amount]"]')
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

        const newIndex = multiPointRowIndex++;
        const rowId = 'row' + newIndex;

        const row = document.createElement('tr');
        row.innerHTML = `
            <td>
                <div class="form-check form-check-primary custom-checkbox">
                    <input type="checkbox" class="form-check-input row-checkbox" name="row_checkbox[]" value="${newIndex}" id="${rowId}">
                    <label class="form-check-label"></label>
                </div>
            </td>

            <td width="150px">
                <input type="hidden" name="multi_point[${newIndex}][id]" value="">
                <input type="text" name="multi_point[${newIndex}][source_state_name]" class="form-control mw-100 state-autocomplete" placeholder="Start typing source state..." data-type="source" />
                <input type="hidden" name="multi_point[${newIndex}][source_state_id]" class="state-id" data-type="source" />
            </td>

            <td width="150px">
                <input type="text" name="multi_point[${newIndex}][source_city_name]" class="form-control mw-100 city-autocomplete" placeholder="Start typing source city..." data-type="source" />
                <input type="hidden" name="multi_point[${newIndex}][source_city_id]" class="city-id" data-type="source" />
            </td>

            <td><input type="text" name="multi_point[${newIndex}][free_point]" class="form-control mw-100" placeholder="Enter Free Point" /></td>
            <td><input type="text" name="multi_point[${newIndex}][amount]" class="form-control mw-100" placeholder="Enter Amount" /></td>
            <td>
                <input type="text" name="multi_point[${newIndex}][customer_name]" class="form-control mw-100 customer-autocomplete" placeholder="Start typing customer..." />
                <input type="hidden" name="multi_point[${newIndex}][customer_id]" class="customer-id" />
            </td>
        `;

        tbody.appendChild(row);

        if (typeof feather !== 'undefined') feather.replace();
        if (typeof bindAutocomplete === 'function') {
            bindAutocomplete(row); 
        }
    }
</script>

<script>
    //customer autocomplete search code here

const customerList = [
    @foreach($customers as $customer)
        {
            label: "{{ addslashes($customer->company_name) }}",
            value: "{{ addslashes($customer->company_name) }}",
            id: {{ $customer->id }}
        },
    @endforeach
];

 $(document).on('focus', '.customer-autocomplete', function () {
    const $input = $(this);

    if (!$input.data('ui-autocomplete')) {
        $input.autocomplete({
            source: customerList,
            minLength: 0,
            select: function (event, ui) {
                $input.val(ui.item.label);

                const $row = $input.closest('tr');

                if ($row.length) {
                    $row.find('.customer-id').val(ui.item.id);
                } else {
                    $('.customer-id').val(ui.item.id); 
                }

                return false;
            }
        }).focus(function () {
            $(this).autocomplete('search', '');
        });
    }
});

</script>


<script>
    //multiple row deleting
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
        const hiddenId = row.querySelector('input[name^="multi_point"][name$="[id]"]');
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
                fetch("{{ route('logistics.multi-point.delete-multiple') }}", {
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
</script>

@endsection
