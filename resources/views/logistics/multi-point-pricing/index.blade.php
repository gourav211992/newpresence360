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
                                <!-- <button class="btn btn-warning btn-sm me-1 mb-20 mb-sm-0" data-bs-target="#filter" data-bs-toggle="modal">
                                 <i data-feather="filter"></i> Filter
                               </button> -->
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
                                                <th>Created At</th>
                                                <th>Created By</th>
                                                <th>Status</th>
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
                                                <th >Source <span class="text-danger">*</span></th>
                                                <th>Free Point <span class="text-danger">*</span></th>
                                                <th >Rate per point <span class="text-danger">*</span></th>
                                                <th>Customer</th>
                                             </tr>
                                          </thead>
                                          <tbody class="mrntableselectexcel">
                                             @php $rowIndex = 0; @endphp
                                             @foreach($multiPoints as $point)
                                             <tr>
                                                <td>
                                                    <div class="form-check form-check-primary custom-checkbox">
                                                      <input type="checkbox" class="form-check-input row-checkbox" name="row_checkbox[]" value="{{ $rowIndex }}">
                                                      <label class="form-check-label"></label>
                                                   </div>
                                                </td>
                                                 <td>
                                                <input type="hidden" name="multi_point[{{ $rowIndex }}][id]" value="{{ $point->id ?? '' }}">
                                                <input type="text"
                                                name="multi_point[{{ $rowIndex }}][source_route_name]"
                                                class="form-control mw-100 route-master-autocomplete"
                                                placeholder="Start typing locations"
                                                data-type="source"
                                                value="{{ optional($point->sourceRoute)->name ?? '' }}" />

                                            <input type="hidden"
                                                name="multi_point[{{ $rowIndex }}][source_route_id]"
                                                class="route-master-id"
                                                data-type="source"
                                                value="{{ $point->source_route_id ?? '' }}" />
                                                </td>
                                                <td>
                                                   <input type="number" name="multi_point[{{ $rowIndex }}][free_point]" class="form-control mw-100" value="{{ old("multi_point.$rowIndex.free_point", $point->free_point) }}" >
                                                </td>
                                                <td>
                                                   <input type="text" name="multi_point[{{ $rowIndex }}][amount]" class="form-control mw-100" value="{{ old("multi_point.$rowIndex.amount", $point->amount) }}">
                                                </td>
                                                <td>
                                                   <input type="text" name="multi_point[{{ $rowIndex }}][customer_name]" class="form-control mw-100 customer-autocomplete" placeholder="Start typing customer..." value="{{ old("multi_point.$rowIndex.customer_name", optional($point->customer)->company_name) }}">
                                                   <input type="hidden" name="multi_point[{{ $rowIndex }}][customer_id]" class="customer-id" value="{{ old("multi_point.$rowIndex.customer_id", $point->customer_id) }}">
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
                                              <td>
                                            <input type="text"
                                                name="multi_point[0][source_route_name]"
                                                class="form-control mw-100 route-master-autocomplete"
                                                placeholder="Start typing  locations..."
                                                data-type="source" />
                                            <input type="hidden"
                                                name="multi_point[0][source_route_id]"
                                                class="route-master-id"
                                                data-type="source" />
                                             </td>             
                                                <td><input type="number" name="multi_point[0][free_point]" class="form-control mw-100" placeholder="Enter Free point" ></td>
                                                <td><input type="text" name="multi_point[0][amount]" class="form-control mw-100" placeholder="Enter Amount."></td>
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
         <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
            <div class="modal-dialog sidebar-sm">
                <form class="add-new-record modal-content pt-0" id="multi-fixed-filter-form">
                    <div class="modal-header mb-1">
                        <h5 class="modal-title">Apply Filters</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                    </div>
                    <div class="modal-body flex-grow-1">
                        <div class="mb-1">
                            <label class="form-label">Source State</label>
                            <input type="text" id="source_state_name" name="source_state_name" class="form-control" placeholder="Enter source state">
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Source City</label>
                            <input type="text" id="source_city_name" name="source_city_name" class="form-control" placeholder="Enter source city">
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Destination State</label>
                            <input type="text" id="destination_state_name" name="destination_state_name" class="form-control" placeholder="Enter destination state">
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Destination City</label>
                            <input type="text" id="destination_city_name" name="destination_city_name" class="form-control" placeholder="Enter destination city">
                        </div>
                        <div class="mb-1">
                            <label class="form-label">Customer Name</label>
                            <input type="text" id="customer_name" name="customer_name" class="form-control" placeholder="Enter customer name">
                        </div>
                    </div>

                    <div class="modal-footer justify-content-start">
                        <button type="button" class="btn btn-primary apply-filter me-1">Apply</button>
                        <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="reset" class="btn btn-outline-secondary" id="reset-filters">Reset</button>
                    </div>
                </form>
            </div>
        </div>   

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
                data: function (d) {
                    d.source_state_name = $('#source_state_name').val();
                    d.source_city_name = $('#source_city_name').val();
                    d.destination_state_name = $('#destination_state_name').val();
                    d.destination_city_name = $('#destination_city_name').val();
                    d.customer_name = $('#customer_name').val();
                },
                error: function (xhr, status, error) {
                    console.error("DataTables AJAX Error:", error);
                    console.error("Response Text:", xhr.responseText);
                    alert("Failed to load data. Check console for more info.");
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'source', name: 'source' },
                { data: 'destination', name: 'destination' },
                { data: 'customer', name: 'customer' },
                { data: 'locations', name: 'locations', orderable: false, searchable: false },
               
                { data: 'created_at', name: 'created_at' },
                { data: 'created_by', name: 'created_by' },

                { data: 'status', name: 'status', orderable: false, searchable: false },
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
                    buttons: ['print', 'csv', 'excel', 'pdf', 'copy']
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

    // Reload on input change
    $('#source_state_name, #source_city_name, #destination_state_name, #destination_city_name, #customer_name')
        .on('keyup change', function () {
            multiFixedDataTable.ajax.reload();
        });

    // Reset filters
    $('#reset-filters').on('click', function () {
        $('#multi-fixed-filter-form')[0].reset();
        multiFixedDataTable.ajax.reload();
    });

    // Apply filter
    $('.apply-filter').on('click', function () {
        multiFixedDataTable.ajax.reload();
        $('#filter').modal('hide');
    });
});
</script>

<script>
 let multiPointRowIndex = {{ $rowIndex ?? 1 }};

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
                row.querySelector('.route-master-autocomplete[data-type="source"]'),
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
            <td>
                <input type="hidden" name="multi_point[${newIndex}][id]" value="">
                <input type="text"
                    name="multi_point[${newIndex}][source_route_name]"
                    class="form-control mw-100 route-master-autocomplete"
                    placeholder="Start typing  locations..."
                    data-type="source" />
                 <input type="hidden"
                    name="multi_point[${newIndex}][source_route_id]"
                    class="route-master-id"
                    data-type="source" />
            </td>

            <td><input type="number" name="multi_point[${newIndex}][free_point]" class="form-control mw-100" placeholder="Enter Free Point" /></td>
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
document.addEventListener('input', function (e) {
    if (e.target.matches('input[name^="multi_point"][name$="[free_point]"]')) {
        const value = parseInt(e.target.value);

        if (value < 1 || value > 99) {
            e.target.value = ''; 
        }
    }
});


</script>

<script>

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
            source: function (request, response) {
                const results = $.ui.autocomplete.filter(routeMasters, request.term);
                if (!results.length) {
                    results.push({
                        label: 'No matching route found',
                        value: '',
                        id: ''
                    });
                }
                response(results);
            },
            minLength: 0,
            select: function (event, ui) {
                if (ui.item.id === '') {
                    event.preventDefault(); // Prevent selection
                    $input.val('');
                    $input.closest('tr').find('.route-master-id[data-type="' + $input.data('type') + '"]').val('');
                    return false;
                }

                $input.val(ui.item.label);
                $input.closest('tr').find('.route-master-id[data-type="' + $input.data('type') + '"]').val(ui.item.id);
                return false;
            },
            change: function (event, ui) {
                if (!ui.item || ui.item.id === '') {
                    $input.closest('tr').find('.route-master-id[data-type="' + $input.data('type') + '"]').val('');
                }
            }
        }).focus(function () {
            $(this).autocomplete('search', '');
        });
    }
});

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
