@extends('layouts.app')

@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-header pocreate-sticky">
                <div class="row">
                    <div class="content-header-left col-md-6 mb-2">
                        <h2 class="content-header-title float-start mb-0">Expense Masters</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                                <li class="breadcrumb-item active">Expense Masters</li>
                            </ol>
                        </div>
                    </div>
                    <div class="content-header-right text-end col-md-6">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#expenseMasterModal"
                            id="addExpenseMasterBtn">
                            <i data-feather="plus-circle"></i> Add New
                        </button>
                    </div>
                </div>
            </div>
            <div class="content-body">
                <section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="datatables-basic table">
                                            <thead>
                                                <tr>
                                                    <th>S.NO.</th>
                                                    <th>Name</th>
                                                    <th>Alias</th>
                                                    {{-- <th>Percentage</th> --}}
                                                    <th>Expense Ledger</th>
                                                    {{-- <th>Service Provider Ledger</th> --}}
                                                    <th>Status</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="expenseMasterModal" tabindex="-1" aria-labelledby="expenseMasterModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header p-0 bg-transparent">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-sm-4 mx-50 pb-2">
                    <h1 class="text-center mb-1" id="expenseMasterModalLabel">Add Expense Master</h1>
                    <p class="text-center">Enter the details below.</p>

                    <form action="{{ route('expense-masters.store') }}" class="ajax-input-form" method="POST"
                        id="expenseMasterForm">
                        @csrf
                        <input type="hidden" name="_method" id="method" value="POST">
                        <input type="hidden" name="id" id="masterId" value="">

                        <div class="row mt-2">
                            <div class="col-md-12 mb-1">
                                <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name">
                            </div>
                            <div class="col-md-12 mb-1">
                                <label for="alias" class="form-label">Alias</label>
                                <input type="text" class="form-control" id="alias" name="alias">
                            </div>
                            <!-- HSN -->
                            <div class="col-md-12 mb-1">
                                <label for="hsn_id" class="form-label">SAC Code</label>
                                {{-- <span class="text-danger">*</span> --}}
                                <div class="d-flex">
                                    <input type="text" class="form-control autocomplete-hsn mw-100" id="hsn_id"
                                        name="hsn_name" placeholder="Start typing to search for a hsn" autocomplete="off">
                                    <input type="hidden" id="id_hsn" name="hsn_id" class="id-hsn">
                                </div>
                            </div>

                            <!-- Expense Ledger -->
                            <div class="col-md-6 mb-1">
                                <label for="expense_ledger_id" class="form-label">Expense Ledger</label>
                                <div class="d-flex">
                                    <input type="text" class="form-control autocomplete-ledger-expense mw-100"
                                        id="expense_ledger_id" name="expense_ledger_name"
                                        placeholder="Start typing to search for a ledger" autocomplete="off">
                                    <input type="hidden" id="ledger_id_expense" name="expense_ledger_id"
                                        class="ledger-id-expense">
                                </div>
                            </div>

                            <!-- Expense Ledger Group -->
                            <div class="col-md-6 mb-1">
                                <label for="expense_ledger_group_id" class="form-label">Expense Ledger Group</label>
                                <div class="d-flex">
                                    <input type="text" class="form-control autocomplete-ledger-group-expense mw-100"
                                        id="expense_ledger_group_id" name="expense_ledger_group_name"
                                        placeholder="Start typing to search for a ledger group" autocomplete="off">
                                    <input type="hidden" id="ledger_group_id_expense" name="expense_ledger_group_id"
                                        class="ledger-group-id-expense">
                                </div>
                            </div>

                            {{-- <!-- Service Ledger -->
                            <div class="col-md-6 mb-1">
                                <label for="service_provider_ledger_id" class="form-label">Service Ledger</label>
                                <div class="d-flex">
                                    <input type="text" class="form-control autocomplete-ledger-service mw-100"
                                        id="service_provider_ledger_id" name="service_provider_ledger_name"
                                        placeholder="Start typing to search for a ledger" autocomplete="off">
                                    <input type="hidden" id="ledger_id_service_provider"
                                        name="service_provider_ledger_id" class="ledger-id-service">
                                </div>
                            </div>

                            <!-- Service Ledger Group -->
                            <div class="col-md-6 mb-1">
                                <label for="service_provider_ledger_group_id" class="form-label">Service Ledger
                                    Group</label>
                                <div class="d-flex">
                                    <input type="text" class="form-control autocomplete-ledger-group-service mw-100"
                                        id="service_provider_ledger_group_id" name="service_provider_ledger_group_name"
                                        placeholder="Start typing to search for a ledger group" autocomplete="off">
                                    <input type="hidden" id="ledger_group_id_service_provider"
                                        name="service_provider_ledger_group_id" class="ledger-group-id-service">
                                </div>
                            </div>

                            <div class="col-md-12 mb-1">
                                <label for="percentage" class="form-label">Percentage<span
                                        class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="percentage" name="percentage"
                                    step="0.01">
                            </div> --}}

                            <div class="mb-1"
                                style="border: 1px solid #ddd; padding: 8px; border-radius: 8px; max-width: 400px; margin: 0 auto;">
                                <label class="form-label"
                                    style="font-weight: 600; font-size: 14px; margin-bottom: 6px;">Applicable On</label>
                                <div class="row">
                                    <div class="col-6 mb-2">
                                        <label for="is_purchase" class="form-label"
                                            style="font-weight: 500; font-size: 14px;">Is Purchase</label>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input custom-checkbox"
                                                id="is_purchase" name="is_purchase" value="1">
                                            <input type="hidden" name="is_purchase" value="0">
                                        </div>
                                    </div>
                                    <div class="col-6 mb-2">
                                        <label for="is_sale" class="form-label"
                                            style="font-weight: 500; font-size: 14px;">Is Sale</label>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input custom-checkbox"
                                                id="is_sale" name="is_sale" value="1">
                                            <input type="hidden" name="is_sale" value="0">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mb-1">
                                <div class="row align-items-center mb-2">
                                    <div class="col-md-12">
                                        <label class="form-label text-primary"><strong>Status</strong></label>
                                        <div class="demo-inline-spacing">
                                            @foreach ($status as $option)
                                                <div class="form-check form-check-primary mt-25">
                                                    <input type="radio" id="status_{{ strtolower($option) }}"
                                                        name="status" value="{{ $option }}"
                                                        class="form-check-input"
                                                        {{ $option == 'active' ? 'checked' : '' }}>
                                                    <label class="form-check-label fw-bolder"
                                                        for="status_{{ strtolower($option) }}">
                                                        {{ ucfirst($option) }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn"
                        form="expenseMasterForm">Submit</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
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
            const baseUrl = getBaseUrl();
            const sac = "{{ $sac ?? 'Sac' }}";

            function initializeAutocomplete(selector, url, hiddenFieldSelector, groupSelector = null) {
                if ($(selector).length) {
                    $(selector).autocomplete({
                        source: function(request, response) {
                            $.ajax({
                                url: url,
                                method: 'GET',
                                data: {
                                    q: request.term
                                },
                                success: function(data) {
                                    if (Array.isArray(data)) {
                                        if (hiddenFieldSelector == '#id_hsn') {
                                            response($.map(data, function(item) {
                                                return {
                                                    label: item.code,
                                                    value: item.code,
                                                    id: item.id
                                                };
                                            }));
                                        } else {
                                            response($.map(data, function(item) {
                                                return {
                                                    label: item.name,
                                                    value: item.name,
                                                    id: item.id
                                                };
                                            }));
                                        }

                                    } else {
                                        console.error("Unexpected data format:", data);
                                    }
                                },
                                error: function() {
                                    alert('An error occurred while fetching data.');
                                }
                            });
                        },
                        minLength: 0,
                        select: function(event, ui) {
                            $(hiddenFieldSelector).val(ui.item.id);
                            $(selector).val(ui.item.label);
                            if (groupSelector) {
                                updateLedgerGroupAutocomplete(ui.item.id, groupSelector);
                            }
                        },
                        change: function(event, ui) {
                            if (!ui.item) {
                                $(this).val('');
                                $(hiddenFieldSelector).val('');
                                if (groupSelector) {
                                    showAllGroups(groupSelector);
                                }
                            }
                        }
                    });

                    const preselectedId = $(hiddenFieldSelector).val();
                    const preselectedName = $(selector).val();
                    if (preselectedId && preselectedName) {
                        $(selector).val(preselectedName);
                        $(hiddenFieldSelector).val(preselectedId);
                        if (groupSelector && preselectedId) {
                            updateLedgerGroupAutocomplete(preselectedId, groupSelector);
                        }
                    }

                    $(selector).on('focus', function() {
                        $(this).autocomplete('search', '');
                    });

                    $(selector).on('keydown', function(e) {
                        if (e.key === 'Enter') {
                            $(this).autocomplete('search', '');
                        }
                    });
                }
            }

            function updateLedgerGroupAutocomplete(ledgerId, groupSelector, selectedGroupId = '') {
                $(groupSelector).val('');
                $(groupSelector + "-id").val('');
                $.ajax({
                    url: baseUrl + '/ledgers/' + ledgerId + '/groups',
                    method: 'GET',
                    success: function(data) {
                        if (Array.isArray(data)) {
                            $(groupSelector).autocomplete("option", "source", $.map(data, function(
                                group) {
                                return {
                                    label: group.name,
                                    value: group.name,
                                    id: group.id
                                };
                            }));

                            if (selectedGroupId) {
                                var selectedGroup = data.find(group => group.id == selectedGroupId);
                                if (selectedGroup) {
                                    $(groupSelector).val(selectedGroup.name);
                                    $(groupSelector + "-id").val(selectedGroup.id);
                                }
                            }
                        } else if (typeof data === 'object') {
                            $(groupSelector).autocomplete("option", "source", [{
                                label: data.name,
                                value: data.name,
                                id: data.id
                            }]);

                            if (selectedGroupId && data.id == selectedGroupId) {
                                $(groupSelector).val(data.name);
                                $(groupSelector + "-id").val(data.id);
                            }
                        } else {
                            console.error("Unexpected data format:", data);
                        }
                    },
                    error: function() {
                        alert('An error occurred while fetching Ledger Groups.');
                    }
                });
            }

            function showAllGroups(groupSelector) {
                $.ajax({
                    url: baseUrl + '/search/group',
                    method: 'GET',
                    success: function(data) {
                        if (Array.isArray(data)) {
                            $(groupSelector).autocomplete("option", "source", $.map(data, function(
                                item) {
                                return {
                                    label: item.name,
                                    value: item.name,
                                    id: item.id
                                };
                            }));
                        } else {
                            console.error("Unexpected data format:", data);
                        }
                    },
                    error: function() {
                        alert('An error occurred while fetching groups.');
                    }
                });
            }

            function resetLedgerGroupAutocomplete(groupSelector) {
                $(groupSelector).val('');
                $(groupSelector + "-id").val('');
                $(groupSelector).autocomplete("option", "source", []);
            }

            $(".autocomplete-ledger-expense").each(function() {
                initializeAutocomplete(this, baseUrl + "/search/ledger", "#ledger_id_expense",
                    '.autocomplete-ledger-group-expense');
            });

            $(".autocomplete-hsn").each(function() {
                initializeAutocomplete(this, baseUrl + `/search?type=hsn&hsn_type=${sac}`, "#id_hsn",
                    '.autocomplete-hsn');
            });

            $(".autocomplete-ledger-group-expense").each(function() {
                initializeAutocomplete(this, baseUrl + "/search/group", "#ledger_group_id_expense");
            });

            // $(".autocomplete-ledger-service").each(function() {
            //     initializeAutocomplete(this, baseUrl + "/search/ledger", "#ledger_id_service_provider",
            //         '.autocomplete-ledger-group-service');
            // });

            // $(".autocomplete-ledger-group-service").each(function() {
            //     initializeAutocomplete(this, baseUrl + "/search/group",
            //         "#ledger_group_id_service_provider");
            // });

            $(".autocomplete-ledger-expense, .autocomplete-ledger-service").on("input", function() {
                let groupSelector = '';
                if ($(this).hasClass('autocomplete-ledger-expense')) {
                    groupSelector = '.autocomplete-ledger-group-expense';
                } else if ($(this).hasClass('autocomplete-ledger-service')) {
                    groupSelector = '.autocomplete-ledger-group-service';
                }
                resetLedgerGroupAutocomplete(groupSelector);
            });

            function initializeLedgerGroupsOnPageLoad() {
                var expenseLedgerId = $('input[name="expense_ledger_id"]').val();
                var expenseLedgerGroupId = $('input[name="expense_ledger_group_id"]').val();
                if (expenseLedgerId) {
                    updateLedgerGroupAutocomplete(expenseLedgerId, '.autocomplete-ledger-group-expense',
                        expenseLedgerGroupId);
                }
                // var serviceLedgerId = $('input[name="service_provider_ledger_id"]').val();
                // var serviceLedgerGroupId = $('input[name="service_provider_ledger_group_id"]').val();
                // if (serviceLedgerId) {
                //     updateLedgerGroupAutocomplete(serviceLedgerId, '.autocomplete-ledger-group-service',
                //         serviceLedgerGroupId);
                // }
            }
            $(document).on('click', '.edit-btn', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const alias = $(this).data('alias');
                const hsn_id = $(this).data('hsn_id');
                const hsn_name = $(this).data('hsn_name');
                const expense_ledger_id = $(this).data('expense_ledger_id');
                const expense_ledger_name = $(this).data('expense_ledger_name');
                const expense_ledger_group_id = $(this).data('expense_ledger_group_id');
                const expense_ledger_group_name = $(this).data('expense_ledger_group_name');
                // const service_provider_ledger_id = $(this).data('service_provider_ledger_id');
                // const service_provider_ledger_name = $(this).data('service_provider_ledger_name');
                // const service_provider_ledger_group_id = $(this).data('service_provider_ledger_group_id');
                // const service_provider_ledger_group_name = $(this).data(
                //     'service_provider_ledger_group_name');
                const percentage = $(this).data('percentage');
                const is_purchase = $(this).data('is_purchase');
                const is_sale = $(this).data('is_sale');
                const status = $(this).data('status');
                $('#masterId').val(id);
                $('#name').val(name);
                $('#alias').val(alias);
                $('#id_hsn').val(hsn_id);
                $('#hsn_id').val(hsn_name);
                $('#expense_ledger_id').val(expense_ledger_name);
                $('#ledger_id_expense').val(expense_ledger_id);
                $('#expense_ledger_group_id').val(expense_ledger_group_name);
                $('#ledger_group_id_expense').val(expense_ledger_group_id);
                // $('#service_provider_ledger_id').val(service_provider_ledger_name);
                // $('#ledger_id_service_provider').val(service_provider_ledger_id);
                // $('#service_provider_ledger_group_id').val(service_provider_ledger_group_name);
                // $('#ledger_group_id_service_provider').val(service_provider_ledger_group_id);
                $('#percentage').val(percentage);
                $('#is_purchase').prop('checked', is_purchase == 1);
                $('#is_sale').prop('checked', is_sale == 1);
                $('#is_purchase').siblings('input[type="hidden"]').val($('#is_purchase').prop('checked') ?
                    '1' : '0');
                $('#is_sale').siblings('input[type="hidden"]').val($('#is_sale').prop('checked') ? '1' :
                    '0');
                $('input[name="status"][value="' + status + '"]').prop('checked', true);
                $('#expenseMasterModalLabel').text('Edit Expense Master');
                $('#submitBtn').text('Update Expense');
                $('#expenseMasterForm').attr('action', '{{ route('expense-masters.update', '') }}/' + id);
                $('#method').val('PUT');
                $('#expenseMasterModal').modal('show').on('shown.bs.modal', function() {
                    initializeLedgerGroupsOnPageLoad();
                    applyCapsLock();
                });
            });

            $('#addExpenseMasterBtn').on('click', function() {
                $('#masterId').val('');
                $('#name').val('');
                $('#alias').val('');
                $('#hsn_id').val('');
                $('#hsn_name').val('');
                $('#expense_ledger_id').val('');
                // $('#service_provider_ledger_id').val('');
                $('#expense_ledger_group_id').val('');
                // $('#service_provider_ledger_group_id').val('');
                $('#percentage').val('');
                $('#is_purchase').prop('checked', false);
                $('#is_sale').prop('checked', false);
                $('#is_purchase').siblings('input[type="hidden"]').val('0');
                $('#is_sale').siblings('input[type="hidden"]').val('0');
                $('input[name="status"][value="active"]').prop('checked', true);
                $('#expenseMasterModalLabel').text('Add Expense Master');
                $('#submitBtn').text('Add Expense');
                $('#expenseMasterForm').attr('action', '{{ route('expense-masters.store') }}');
                $('#method').val('POST');
                $('#expenseMasterModal').modal('show');
            });
            $('#is_purchase, #is_sale').on('change', function() {
                $(this).siblings('input[type="hidden"]').val(this.checked ? '1' : '0');
            });

            $(document).ready(function() {
                var dt_basic_table = $('.datatables-basic');

                function renderData(data) {
                    return data ? data : 'N/A';
                }
                if (dt_basic_table.length) {
                    var dt_discount_master = dt_basic_table.DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '{{ route('expense-masters.index') }}',
                        columns: [{
                                data: 'DT_RowIndex',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'name',
                                render: renderData
                            },
                            {
                                data: 'alias',
                                render: renderData
                            },
                            // {
                            //     data: 'percentage',
                            //     render: renderData
                            // },
                            {
                                data: 'expense_ledger_id',
                                render: renderData
                            },
                            // {
                            //     data: 'service_provider_ledger_id',
                            //     render: renderData
                            // },
                            {
                                data: 'status',
                                render: renderData
                            },
                            {
                                data: 'actions',
                                orderable: false,
                                searchable: false
                            }
                        ],
                        dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
                        buttons: [{
                            extend: 'collection',
                            className: 'btn btn-outline-secondary dropdown-toggle',
                            text: feather.icons['share'].toSvg({
                                class: 'font-small-4 mr-50'
                            }) + 'Export',
                            buttons: [{
                                    extend: 'print',
                                    text: feather.icons['printer'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Print',
                                    className: 'dropdown-item',
                                    title: 'Expense Masters',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5]
                                    }
                                },
                                {
                                    extend: 'csv',
                                    text: feather.icons['file-text'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Csv',
                                    className: 'dropdown-item',
                                    title: 'Expense Masters',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5]
                                    }
                                },
                                {
                                    extend: 'excel',
                                    text: feather.icons['file'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Excel',
                                    className: 'dropdown-item',
                                    title: 'Expense Masters',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5]
                                    }
                                },
                                {
                                    extend: 'pdf',
                                    text: feather.icons['clipboard'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Pdf',
                                    className: 'dropdown-item',
                                    title: 'Expense Masters',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5]
                                    }
                                },
                                {
                                    extend: 'copy',
                                    text: feather.icons['copy'].toSvg({
                                        class: 'font-small-4 mr-50'
                                    }) + 'Copy',
                                    className: 'dropdown-item',
                                    title: 'Expense Masters',
                                    exportOptions: {
                                        columns: [0, 1, 2, 3, 4, 5]
                                    }
                                }
                            ],
                            init: function(api, node, config) {
                                $(node).removeClass('btn-secondary');
                                $(node).parent().removeClass('btn-group');
                                setTimeout(function() {
                                    $(node).closest('.dt-buttons').removeClass(
                                        'btn-group').addClass(
                                        'd-inline-flex');
                                }, 50);
                            }
                        }],
                        drawCallback: function() {
                            feather.replace();
                        },
                        language: {
                            paginate: {
                                previous: '&nbsp;',
                                next: '&nbsp;'
                            }
                        },
                        search: {
                            caseInsensitive: true
                        }
                    });
                }

            });
        });
    </script>
@endsection
