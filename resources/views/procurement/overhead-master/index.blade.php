@extends('layouts.app')
@section('content')
<div class="app-content content">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header pocreate-sticky">
            <div class="row">
                <div class="content-header-left col-md-6 mb-2">
                    <h2 class="content-header-title float-start mb-0">Overhead Masters</h2>
                    <div class="breadcrumb-wrapper">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                            <li class="breadcrumb-item active">Overhead Masters</li>
                        </ol>
                    </div>
                </div>
                <div class="content-header-right text-end col-md-6">
                    <button class="btn btn-primary" id="addOverheadMasterBtn">
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
                                                <th>Percentage</th>
                                                <th>Ledger</th>
                                                <th>Ledger Group</th>
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
<div class="modal fade" id="overheadMasterModal" tabindex="-1" aria-labelledby="OverheadMasterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header p-0 bg-transparent">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-sm-4 mx-50 pb-2">
                <h1 class="text-center mb-1" id="OverheadMasterModalLabel">Add Overhead Master</h1>
                <p class="text-center">Enter the details below.</p>

                <form action="{{ route('overhead-masters.store') }}" class="ajax-input-form" method="POST" id="overheadMasterForm">
                    @csrf
                    <input type="hidden" name="_method" id="method" value="POST">
                    <input type="hidden" name="id" id="masterId" value="">

                    <div class="row mt-2">
                        <div class="col-md-12 ">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name">
                        </div>
                        <div class="col-md-12 ">
                            <label for="alias" class="form-label">Alias</label>
                            <input type="text" class="form-control" id="alias" name="alias">
                        </div>
                        <div class="col-md-6 ">
                            <label for="percentage" class="form-label">Percentage </label>
                            <input type="number" class="form-control" id="percentage" name="perc" step="0.01">
                        </div>
                        <div class="col-md-6  d-flex align-items-center">
                            <div class="form-check" style="margin-top: 25px;">
                                <input type="checkbox" class="form-check-input" id="is_waste" name="is_waste" value="yes">
                                <label for="is_waste" class="form-check-label" style="margin-left: 6px;">Is Wastage</label>
                            </div>
                        </div>
                        <div class="col-md-12 ">
                            <label for="ledger_id_0" class="form-label">Ledger</label>
                            <div class="d-flex">
                                <input type="text" class="form-control autocomplete-ledger mw-100" id="ledger_name_0" name="ledger_name_0" data-id="ledger_name_0" placeholder="Start typing to search for a ledger">
                                <input type="hidden" id="ledger_id_0" name="ledger_id" class="ladger-id">
                            </div>
                        </div>

                        <div class="col-md-12 ">
                            <label for="ledger_group_id_0" class="form-label">Ledger Group</label>
                            <div class="d-flex">
                                <select class="form-select" id="ledger_group_name_0" name="ledger_group_name_0">
                                    <option value=""></option>
                                </select>
                                <input type="hidden" id="ledger_group_id_0" name="ledger_group_id" class="ledger-group-id">
                            </div>
                        </div>
                        
                        <div class="col-md-12 ">
                            <div class="row align-items-center mb-2">
                                <div class="col-md-12">
                                    <label class="form-label text-primary"><strong>Status</strong></label>
                                    <div class="demo-inline-spacing">
                                        @foreach ($status as $option)
                                            <div class="form-check form-check-primary mt-25">
                                                <input
                                                    type="radio"
                                                    id="status_{{ strtolower($option) }}"
                                                    name="status"
                                                    value="{{ $option }}"
                                                    class="form-check-input"
                                                    {{ $option == 'active' ? 'checked' : '' }} >
                                                <label class="form-check-label fw-bolder" for="status_{{ strtolower($option) }}">
                                                    {{ ucfirst($option) }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-outline-secondary me-1" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
$(document).ready(function() {
const baseUrl = getBaseUrl();
$(".autocomplete-ledger").on("autocompleteselect", function(event, ui) {
    var ledgerId = ui.item.id;
    if (ledgerId) {
        $(".autocomplete-ledger-group").val("");
        $(".ledger-group-id").val(""); 
        updateLedgerGroupAutocomplete(ledgerId);  
    }
});

$(".autocomplete-ledger").on("input", function() {
    $(".autocomplete-ledger-group").val("");
    $(".ledger-group-id").val("");
    $(".autocomplete-ledger-group").autocomplete("option", "source", []);  
});

$(".autocomplete-ledger").each(function(index) {
    initializeAutocomplete(this, "{{ url('/search/ledger') }}", "#ledger_id_" + index);
});

function updateLedgerGroupAutocomplete(ledgerId) {
    $.ajax({
        url: baseUrl + '/ledgers/' + ledgerId + '/groups',
        method: 'GET',
        success: function(data) {
            var autocompleteData = [];
            if (Array.isArray(data)) {
                autocompleteData = data.map(function(group) {
                    return {
                        label: group.name,
                        value: group.name,
                        id: group.id
                    };
                });
            } 
            else if (data && typeof data === 'object') {
                autocompleteData = [{
                    label: data.name,
                    value: data.name,
                    id: data.id
                }];
            } 
            else {
                $('#ledger_group_name_0').html('<option value=""></option>');
                $('#ledger_group_id_0').val('');
                console.error("Unexpected data format:", data);
                return; 
            }
            // make normal select drop down
            let options = '';
            $.each(autocompleteData, function(index, item) {
                options += '<option value="' + item.id + '">' + item.label + '</option>';
            });
            $('#ledger_group_name_0').html(options);
            $('#ledger_group_id_0').val(autocompleteData[0].id);
        },
        error: function() {
            $('#ledger_group_name_0').html('<option value=""></option>');
            $('#ledger_group_id_0').val('');
            // alert('An error occurred while fetching Ledger Groups.');
        }
    });
}


function resetLedgerGroupAutocomplete() {
    $(".autocomplete-ledger-group").autocomplete("option", "source", function(request, response) {
        $.ajax({
            url: "{{ url('/search/group') }}",
            method: 'GET',
            data: {
                term: request.term
            },
            success: function(data) {
                if (typeof data === 'object') {
                    response($.map(data, function(item) {
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
    });
}

$(".autocomplete-ledger").on("change", function() {
    if ($(this).val() === "") {
        resetLedgerGroupAutocomplete(); 
    }
});

function initializeAutocomplete(selector, url, hiddenFieldSelector) {
    if ($(selector).length) {
        $(selector).autocomplete({
            source: function(request, response) {
                $.ajax({
                    url: url, 
                    method: 'GET',
                    data: { q: request.term },
                    success: function(data) {
                        if (Array.isArray(data) && data.length > 0) {
                            response($.map(data, function(item) {
                                return {
                                    label: item.name,
                                    value: item.name,
                                    id: item.id
                                };
                            }));
                        } else {
                            console.error("Unexpected or empty data format:", data);
                        }
                    },
                    error: function() {
                        alert('An error occurred while fetching data.');
                    }
                });
            },
            minLength: 0, 
            select: function(event, ui) {
                console.clear();
                console.log(hiddenFieldSelector);
                $(hiddenFieldSelector).val(ui.item.id);  
                $(selector).val(ui.item.label); 

            },
            change: function(event, ui) {
                if (!ui.item) {
                    $(this).val('');
                    $(hiddenFieldSelector).val('');
                }
            }
        });
        $(selector).focus(function() {
            if (this.value === "") {
                $(this).autocomplete("search", "");  
            }
        }).on("input", function () {
            if ($(this).val().trim() === "") {
                $(this).removeData("selected");
                $(hiddenFieldSelector).val("");
                updateLedgerGroupAutocomplete("");
            }
        });
    }
}

function initializeLedgerGroupsOnPageLoad() {
    var ledgerId = $('input[name="discount_ledger_id"]').val();
    if (ledgerId) {
        updateLedgerGroupAutocomplete(ledgerId); 
    }
}

$(document).on('click', '.edit-btn', function(e) {
    e.preventDefault();
    const id = $(this).data('id');
    const name = $(this).data('name');
    const alias = $(this).data('alias');
    const percentage = $(this).data('perc');
    const ledger_id = $(this).data('ledger_id');
    const ledger_name = $(this).data('ledger_name');
    const ledger_group_id = $(this).data('ledger_group_id');
    const ledger_group_name = $(this).data('ledger_group_name');
    const is_waste = Number($(this).data('is_waste')) ? 'yes' : 'no';
    $('#masterId').val(id);
    $('#name').val(name);
    $('#alias').val(alias);
    $('#percentage').val(percentage);
    $('#ledger_name_0').val(ledger_name); 
    $('#ledger_id_0').val(ledger_id);
    $('#ledger_group_name_0').empty().append('<option value="' + ledger_group_id + '">' + ledger_group_name + '</option>'); 
    $('#ledger_group_id_0').val(ledger_group_id); 
    $('input[name="status"][value="' + status + '"]').prop('checked', true);
    $('input[name="is_waste"]').prop('checked', false);
    $(`input[name="is_waste"][value="${is_waste}"]`).prop('checked', true);
    $('#OverheadMasterModalLabel').text('Edit Overhead Master');
    $('#submitBtn').text('Update Overhead');
    let updateUrl = '{{ route('overhead-masters.update', ':id') }}'.replace(':id', id);
    $('#method').val('PUT');
    $('#overheadMasterModal').modal('show').on('shown.bs.modal', function() {
        $('form').attr('action', updateUrl);
        initializeLedgerGroupsOnPageLoad(); 
    });
});

$('#addOverheadMasterBtn').on('click', function(e) {
    e.preventDefault();
    $('#masterId').val('');
    $('#name').val('');
    $('#alias').val('');
    $('#percentage').val('');
    $('#ledger_id_0').val('');
    $('#overheadt_ledger_group_id_0').val('');
    $('input[name="status"][value="active"]').prop('checked', true);
    $('#OverheadMasterModalLabel').text('Add Overhead Master');
    $('#submitBtn').text('Add Overhead');
    $('#OverheadMasterForm').attr('action', '{{ route('overhead-masters.store') }}');
    $('#method').val('POST');
    $('#overheadMasterModal').modal('show');
});

    var dt_basic_table = $('.datatables-basic');
    function renderData(data) {
        return data ? data : 'N/A';
    }
    if (dt_basic_table.length) {
        var dt_discount_master = dt_basic_table.DataTable({ 
            processing: true,
            serverSide: true,
            ajax: '{{ route('overhead-masters.index') }}',
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'name', render: renderData },
                { data: 'alias', render: renderData },
                { data: 'perc', render: renderData },
                { data: 'ledger_id', render: renderData },
                { data: 'ledger_group_id', render: renderData },
                { data: 'status', render: renderData },
                { data: 'actions', orderable: false, searchable: false }
            ],
            dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            buttons: [
                {
                    extend: 'collection',
                    className: 'btn btn-outline-secondary dropdown-toggle',
                    text: feather.icons['share'].toSvg({ class: 'font-small-4 mr-50' }) + 'Export',
                    buttons: [
                        {
                            extend: 'print',
                            text: feather.icons['printer'].toSvg({ class: 'font-small-4 mr-50' }) + 'Print',
                            className: 'dropdown-item',
                            title: 'Discount Masters',
                            exportOptions: { columns: [0, 1, 2, 3, 4, 5,6] }
                        },
                        {
                            extend: 'csv',
                            text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + 'Csv',
                            className: 'dropdown-item',
                            title: 'Discount Masters',
                            exportOptions: { columns: [0, 1, 2, 3, 4, 5,6] }
                        },
                        {
                            extend: 'excel',
                            text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
                            className: 'dropdown-item',
                            title: 'Discount Masters',
                            exportOptions: { columns: [0, 1, 2, 3, 4, 5,6] }
                        },
                        {
                            extend: 'pdf',
                            text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 mr-50' }) + 'Pdf',
                            className: 'dropdown-item',
                            title: 'Discount Masters',
                            exportOptions: { columns: [0, 1, 2, 3, 4, 5,6] }
                        },
                        {
                            extend: 'copy',
                            text: feather.icons['copy'].toSvg({ class: 'font-small-4 mr-50' }) + 'Copy',
                            className: 'dropdown-item',
                            title: 'Discount Masters',
                            exportOptions: { columns: [0, 1, 2, 3, 4, 5,6] }
                        }
                    ],
                    init: function(api, node, config) {
                        $(node).removeClass('btn-secondary');
                        $(node).parent().removeClass('btn-group');
                        setTimeout(function() {
                            $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
                        }, 50);
                    }
                }
            ],
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

// ledger_group_name_0 on change bind id
$(document).on('change', '#ledger_group_name_0', function() {
    var selectedOption = $(this).find('option:selected');
    var groupId = selectedOption.val();
    $('#ledger_group_id_0').val(groupId);
});
</script>
@endsection
