@extends('layouts.app')

@section('content')

<style>

    .dataTables_scrollBody { height: 400px; }
    .pe-2_5 {
    padding-right: 30px !important;
    }

</style>

<div class="app-content content">
        <div class="content-overlay"></div>
        <div class="content-header row pocreate-sticky">
                <div class="content-header-left col-md-5 mb-2">
                    <div class="row breadcrumbs-top">
                        <div class="col-12">
                            <h2 class="content-header-title float-start mb-0">{{$reportName}}</h2>
                            <input type = 'hidden' value = "{{ $reportName }}" name = 'folder_name' id ="folder_name" />
                            <div class="breadcrumb-wrapper">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a onclick="javascript: history.go(-1)" href="#">Home</a></li>
                                    <li class="breadcrumb-item active"> Report</li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="content-header-right text-sm-end col-md-7 mb-50 mb-sm-0">
                    <div class="form-group breadcrumb-right">
                        <div class="btn-group new-btn-group my-1 my-sm-0 ps-0">
                            <select class="form-select" oninput = "onDateFilterChange(this);" id = "date_filter_dropdown">
                                <option value = "current_month">Current Month</option>
                                <option value = "last_month">Last Month</option>
                                <option value = "custom">Custom</option>
                            </select>
                            <input type="date" class="bg-white form-control flatpickr-range flatpickr-input" name="Period" id="document_date_filter" style = "width:250px;" />
                        </div>
                        <button onclick = "openFiltersModal();"  class="btn btn-warning btn-sm mb-0 waves-effect"><i data-feather="filter"></i>
                            Filters
                        </button>
                        <button data-bs-toggle="modal" data-bs-target="#settings" class="btn btn-primary btn-sm mb-0 waves-effect"><i data-feather="settings"></i>
                            Settings
                        </button>
                        <button class="btn btn-danger btn-sm mb-0 waves-effect" onclick="resetFilters()"><i data-feather="trash"></i>
                            Reset Filters
                        </button>
                    </div>
                </div>
            </div>
        <div class="content-wrapper container-xxl p-0">
            <div class="content-body">
				<section id="basic-datatable">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="trailbalnewdesfinancelonger po-reportnewdesign my-class">
                                    <div class="table-responsive">
                                        <table class="pomrnheadtffotsticky datatables-basic table myrequesttablecbox tableistlastcolumnfixed">
                                            <thead>
                                                <tr>
                                                    @foreach ($tableHeaders as $tableHeader)
                                                    <th class = "{{$tableHeader['header_class']}}" style = "{{$tableHeader['header_style']}}">{{$tableHeader['name']}}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                            </tbody>
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
    <!-- Pop up for all filters -->
    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
		<div class="modal-dialog sidebar-sm">
			<form class="add-new-record modal-content pt-0">
				<div class="modal-header mb-1">
					<h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
				</div>
				<div class="modal-body flex-grow-1" id = "auto-complete-filters-row">
					 <!-- Multiple Dynamic filters -->
				</div>
				<div class="modal-footer justify-content-start">
					<button type="button" class="btn btn-primary data-submit mr-1" onclick = "applyFilters();">Apply</button>
					<button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal" onclick = "applyFilters();">Cancel</button>
				</div>
			</form>
		</div>
	</div>

    <!-- Popup for Customizing columns selection -->
    <div class="modal fade text-start filterpopuplabel " id="settings" tabindex="-1" aria-labelledby="myModalLabel17"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h4 class="modal-title fw-bolder text-dark namefont-sizenewmodal" id="myModalLabel17">Settings</h4>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="step-custhomapp bg-light">
                    <ul class="nav nav-tabs my-25 custapploannav" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#Employee" role="tab"><i
                                    data-feather="columns"></i> Columns</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#Email" role="tab"><i
                                    data-feather="columns"></i> Report E-Mail</a>
                        </li>
                    </ul>
                </div>

                <div class="tab-content tablecomponentreport">
                    <div class="tab-pane active" id="Employee">
                        <div class="compoenentboxreport">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-check form-check-primary">
                                        <input type="checkbox" class="form-check-input" id="selectAll" checked="">
                                        <label class="form-check-label" for="selectAll">Select All Columns</label>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                @foreach ($tableHeaders as $tableHeaderIndex => $tableHeader)
                                <div class="col-md-4">
                                    <div class="form-check form-check-secondary">
                                        <input type="checkbox" class="form-check-input column-toggle" data-column = "{{$tableHeaderIndex}}" id="{{$tableHeader['field']}}" checked>
                                        <label class="form-check-label" for="{{$tableHeader['field']}}">{{$tableHeader['name']}}</label>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane" id="Email">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="compoenentboxreport advanced-filterpopup customernewsection-form mb-1">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-check ps-0">
                                                    <label class="form-check-label">Add Scheduler</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row camparboxnewcen">
                                            <div class="mb-1">
                                                <label class="form-label">Email To</label>
                                                <select id="email_to" class="select2-email form-control mail_modal cannot_disable"
                                                    multiple data-placeholder="Select or enter email(s)">
                                                    @foreach ($users as $user)
                                                        <option value="{{ $user->email }}">{{ $user->name }} ({{ $user->email }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row camparboxnewcen">
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">CC To</label>
                                                    <select id="email_cc" class="select2-cc form-control mail_modal cannot_disable"
                                                        multiple data-placeholder="Select or enter email(s)">
                                                        @foreach ($users as $user)
                                                            <option value="{{ $user->email }}">{{ $user->name }} ({{ $user->email }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label">Remarks</label>
                                                    <textarea name="remarks" id="mail_remarks" class="form-control mail_modal cannot_disable"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                </div>
            </div>
            <div class="modal-footer ">
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary data-submit mr-1" onclick = "applyColumnSelection();">Apply</button>
            </div>
        </div>
    </div>
</div>

@endsection
@section('scripts')
<script type="text/javascript" src="{{asset('assets/js/modules/common-datatable.js')}}"></script>
<script type="text/javascript" src="{{asset('app-assets/vendors/js/tables/datatable/dataTables.reorder.min.js')}}"></script>

<script>
    //Instances for relatime refereshing
    let reportDataTableInstance = null;
    let reportCustomDatePicker = null;
    let customDatePickrElement = null;
    $(document).ready(function() {
    function renderData(data) {
        return data !== undefined && data !== null ? data : '';
    }
    //Assign dynamic Table headers
    let dataTableCols = @json($tableHeaders);
    var columns = [];
    dataTableCols.forEach((colData) => {
        columns.push({
            data : colData.field,
            name : colData.field,
            orderable : colData.orderable,
            searchable : colData.searchable,
            render : renderData,
            createdCell : function(td, cellData, rowData, row, col) {
               $(td).addClass(colData.column_class);
            }
        })
    });
    let filtersComponents = @json($autoCompleteFilters);
    // Define your dynamic filters
    let filters = {
        'date_range' : '#document_date_filter'
    };
    filtersComponents.forEach(filter => {
        filters[filter.requestName] = "#" + (filter.id + "_input");
    });
    var exportColumns = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24]; // Columns to export
    reportDataTableInstance = initializeDataTable('.datatables-basic',
        "{{ $filterRoute }}",
        columns,
        filters,  // Apply filters
        "{{request() -> type === 'sq' ? 'Sales Quotations' : 'Sales Orders'}}",  // Export title
        ':visible',  // Export columns
        [],// default order
        'landscape',
        "{{$reportName == 'Sales Order' ? 'POST' : 'GET'}}"
    );

    //Add the filters in UI
    let autoCompleteFiltersContainer = document.getElementById('auto-complete-filters-row');
    filtersComponents.forEach(filterData => {
        if (filterData.type === 'auto_complete') {
            autoCompleteFiltersContainer.innerHTML += `
                <div class="mb-1">
                        <label class="form-label">${filterData.label}</label>
                        <input type="text" id = "${filterData.id}" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input reportFilter" autocomplete="off">
                        <input class = "reportFilter" type='hidden' name="${filterData.requestName}" id = "${filterData.id + "_input"}"/>
                </div>
            `;
        } else if (filterData.type === 'input_text') {
            autoCompleteFiltersContainer.innerHTML += `
                <div class="mb-1">
                        <label class="form-label">${filterData.label}</label>
                        <input type="text" name = "${filterData.requestName}" id = "${filterData.id + "_input"}" placeholder="Search" class="form-control mw-100 reportFilter">
                </div>
            `;
        } else if (filterData.type === 'date_range') {
            autoCompleteFiltersContainer.innerHTML += `
                <div class="mb-1">
                    <label class="form-label">${filterData.label}</label>
                    <input type="text" class="form-control flatpickr-range flatpickr-input flatpickr-filter" name="${filterData.requestName}" id="${filterData.id + "_input"}" />
                </div>
            `;
        }

    });
    filtersComponents.forEach(filterData => {
        if (filterData.type === 'auto_complete') {
            initializeAutoCompleteFilter(filterData.id, filterData.term, filterData.value_key, filterData.label_key, filterData.dependent);
        }
    });

    reportCustomDatePicker = flatpickr("#document_date_filter", {
        mode: "range",
        dateFormat: "d-m-Y",
        defaultDate: [
            "{{ \Carbon\Carbon::now()->startOfMonth()->format('d-m-Y') }}",
            "{{ \Carbon\Carbon::now()->endOfMonth()->format('d-m-Y') }}"
        ]
    });
    customDatePickrElement = document.getElementById('document_date_filter');
    customDatePickrElement.classList.add('reportFilter');
});
//Initialize the datatable
function initializeAutoCompleteFilter(selector, type, valueKey, labelKey, dependentElements = []) {
    $("#" + selector).autocomplete({
        source: function(request, response) {
            $.ajax({
                url: '/search',
                method: 'GET',
                dataType: 'json',
                data: {
                    q: request.term,
                    type: type,
                },
                success: function(data) {
                    response($.map(data, function(item) {
                        return {
                            id: item[valueKey],
                            label: item[labelKey],
                        };
                    }));
                },
                error: function(xhr) {
                    console.error('Error fetching customer data:', xhr.responseText);
                }
            });
        },
        minLength: 0,
        select: function(event, ui) {
            var $input = $(this);
            var itemCode = ui.item.label;
            var itemId = ui.item.id;

            $input.val(itemCode);
            $("#" + selector + "_input").val(itemId);
            //Reset the dependent elements
            dependentElements.forEach(elementId => {
                let dependentElement = document.getElementById(elementId);
                let dependentElementInput = document.getElementById(elementId + "_input");
                if (dependentElement) {
                    dependentElement.value = "";
                }
                if (dependentElementInput) {
                    dependentElementInput.value = "";
                }
            });
            return false;
        },
        change: function(event, ui) {
            if (!ui.item) {
                $("#" + selector + "_input").val("");
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
        }
    });
}
//Reload the Datatable
function reloadTableAjax()
{
    reportDataTableInstance.ajax.reload();
}
//Clear the filters and reload the ajax
function resetFilters()
{
    let filterInputs = document.querySelectorAll('.reportFilter');
    for (let index = 0; index < filterInputs.length; index++) {
        filterInputs[index].value = "";
        if (filterInputs.id == "document_date_filter") {
            flatpickr("#document_date_filter", {
                mode: "range",
                dateFormat: "d-m-Y",
                defaultDate: [
                    "{{ \Carbon\Carbon::now()->startOfMonth()->format('d-m-Y') }}",
                    "{{ \Carbon\Carbon::now()->endOfMonth()->format('d-m-Y') }}"
                ]
            });
            $("#document_date_filter").trigger('change');
        }
    }
    reloadTableAjax();
}
//Set t
function onDateFilterChange(element)
{
    let dateArray = [];
    if (element.value == 'current_month') {
        dateArray = [
            "{{ \Carbon\Carbon::now()->startOfMonth()->format('d-m-Y') }}",
            "{{ \Carbon\Carbon::now()->endOfMonth()->format('d-m-Y') }}"
        ];
    } else if (element.value == 'last_month') {
        dateArray = [
            "{{ \Carbon\Carbon::now()->subMonthNoOverflow()->startOfMonth()->format('d-m-Y') }}",
            "{{ \Carbon\Carbon::now()->subMonthNoOverflow()->endOfMonth()->format('d-m-Y') }}"
        ];
    } else if (element.value === 'custom') {
      dateArray = [];
      let customDatePickr = document.getElementById("document_date_filter").focus();
      if (customDatePickr) {
        customDatePickr.focus();
        reportCustomDatePicker.open();
      }
      return;
    }
    flatpickr("#document_date_filter", {
        mode: "range",
        dateFormat: "d-m-Y",
        defaultDate: dateArray
    });
    $("#document_date_filter").trigger('change');
}
//Custom on change of flatpickr
$("#document_date_filter").on('change', function (element) {
    $("#date_filter_dropdown").val('custom');
    //Reload the table data
    reloadTableAjax();
});
function applyFilters()
{
    reloadTableAjax();
    $("#filter").modal('hide');
}
function openFiltersModal() {
    $("#filter").modal('show');
    flatpickr(`.flatpickr-filter`, {
        mode: "range",
        dateFormat: "d-m-Y",
        defaultDate: []
    });
}
function applyColumnSelection()
{
    let activeTab = $('#settings .nav-tabs .nav-link.active').attr('href');
    if (activeTab == "#Employee") {
        $("#settings").modal('hide');
        let allColumns = document.getElementsByClassName('column-toggle');
        // for (let index = 0; index < allColumns.length; index++) {
        //     let colIndex = document.getElementById(allColumns[index].id).getAttribute('data-column');
        //     reportDataTableInstance.column(colIndex).visible(allColumns[index].checked);
        // }
        for (let index = 0; index < allColumns.length; index++) {
        let colElement = allColumns[index];
        let colIndex = parseInt(colElement.getAttribute('data-column'));

        if (!isNaN(colIndex) && colIndex < reportDataTableInstance.columns().count()) {
            reportDataTableInstance.column(colIndex).visible(colElement.checked);
        } else {
            console.log('Invalid column index:', colIndex);
        }
    }
    } else {
        sendEmailReport();
    }
}
function initializeEmailFields()
{
    $('.select2-email').select2({
        tags: true,
        tokenSeparators: [',', ' '],
        placeholder: "Select or enter email(s)",
        width: '100%',
        createTag: function(params) {
            var term = $.trim(params.term);
            // Basic email format validation
            if (term.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                return {
                    id: term,
                    text: term,
                    newTag: true
                };
            }
            return null;
        }
    });

    $('.select2-cc').select2({
        tags: true,
        tokenSeparators: [',', ' '],
        placeholder: "Select or enter email(s)",
        width: '100%',
        createTag: function(params) {
            var term = $.trim(params.term);
            // Basic email format validation
            if (term.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                return {
                    id: term,
                    text: term,
                    newTag: true
                };
            }
            return null;
        }
    });
}

function sendEmailReport() {

    document.getElementById('erp-overlay-loader').style.display = "flex";

    let visibleColumns = reportDataTableInstance.columns(':visible');
    let visibleDataKeys = visibleColumns.dataSrc().toArray();
    let visibleHeaders = visibleColumns.header().toArray().map(th => th.innerText);

    let visibleData = reportDataTableInstance.rows({ search: 'applied' }).data().toArray().map(row => {
        return visibleDataKeys.map(key => row[key]);
    });

    let filters = ['Filters'];
    let applicableFilters = @json($autoCompleteFilters);

    applicableFilters.forEach(filterComp => {
        filters.push(filterComp.label + ": " + ($("#" + filterComp.id).val() ? $("#" + filterComp.id).val() : $("#" + filterComp.id + "_input").val()));
    });

    fetch("{{ route('transactions.report.email') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            displayedData: visibleData,
            displayedHeaders: visibleHeaders,
            email_to : $("#email_to").val(),
            email_cc : $("#email_cc").val(),
            remarks : $("#mail_remarks").val(),
            filters : filters,
            folder_name : $("#folder_name").val()
        })
    })
    .then(response => {
        if (!response.ok) throw response;
        return response.json();
    })
    .then(data => {
        document.getElementById('erp-overlay-loader').style.display = "none";
        $('select[name="email_to[]"]').val([]).trigger('change');
        $('select[name="email_cc[]"]').val(null).trigger('change');
        $('textarea[name="remarks"]').val('');

        $("#settings").modal('hide');
        Swal.fire({
            title: 'Success!',
            text: 'Report Sent successfully',
            icon: 'success',
        });
    })
    .catch(async (error) => {
        document.getElementById('erp-overlay-loader').style.display = "none";
        try {
            const errorResponse = await error.json();
            if (errorResponse.message) {
                Swal.fire({
                    title: 'Error!',
                    text: errorResponse.message,
                    icon: 'error',
                });
            }
        } catch (e) {
            Swal.fire({
                title: 'Error!',
                text: 'Some internal error occured',
                icon: 'error',
            });
        }
    });
}

initializeEmailFields();
</script>
@endsection
