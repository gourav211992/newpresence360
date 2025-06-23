{{-- Date Range, Series , Doc NO. , Customer/Vendor , Multi Select(Location , Store , Company , Organization)  --}}
<div class="modal modal-slide-in fade filterpopuplabel" id="filter">
    <div class="modal-dialog sidebar-sm">
        <form class="add-new-record modal-content pt-0">
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
            </div>
            <div class="modal-body flex-grow-1" id="auto-complete-filters-row">
            
            </div>
            <div class="modal-footer justify-content-start">
                <button type="button" class="btn btn-primary data-submit mr-1" onclick = "applyFilters();">Apply</button>
                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal" onclick = "closeModal('filter');">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
let filtersComponents = @json($filterArray);
let autoCompleteFiltersContainer = document.getElementById('auto-complete-filters-row');
filtersComponents.forEach(filterData => {
    if (filterData.type == 'auto_complete') {
        autoCompleteFiltersContainer.innerHTML += `
        <div class="mb-1">
        <label class="form-label">${filterData.label}</label>
        <input type="text" id = "${filterData.id}" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input reportFilter" autocomplete="off">
        <input class = "reportFilter" type='hidden' name="${filterData.requestName}" id = "${filterData.id + "_input"}"/>
        </div>
        `;
    } else if (filterData.type == 'input_text') {
        autoCompleteFiltersContainer.innerHTML += `
        <div class="mb-1">
        <label class="form-label">${filterData.label}</label>
        <input type="text" name = "${filterData.requestName}" id = "${filterData.id + "_input"}" placeholder="Search" class="form-control mw-100 reportFilter">
        </div>
        `;
    } else if (filterData.type == 'date_range') {
        autoCompleteFiltersContainer.innerHTML += `
        <div class="mb-1">
        <label class="form-label">${filterData.label}</label>
        <input type="text" class="form-control flatpickr-range flatpickr-input flatpickr-filter" name="${filterData.requestName}" id="${filterData.id + "_input"}" />
            </div>
        `;
    } else if (filterData.type == 'multi_select') 
    {
        autoCompleteFiltersContainer.innerHTML += `
        <div class="mb-1">
        <label class="form-label">${filterData.label}</label>
        <input type="text" id = "${filterData.id}" multiple placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input reportFilter" autocomplete="off">
        <input class = "reportFilter" type='hidden' name="${filterData.requestName}" id = "${filterData.id + "_input"}"/>
        </div>
        `;
    }
                
    });
    filtersComponents.forEach(filterData => {
        if (filterData.type == 'auto_complete') {
            initializeAutoCompleteFilter(filterData.id, filterData.term, filterData.value_key, filterData.label_key, filterData.dependent);
        }
    });

    function initializeAutoCompleteFilter(selector, type, valueKey, labelKey, dependentElements = []) {
        console.log('autocomplete me entry',selector);
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
                $("#" + selector).val("");
                $("#" + selector + "_input").val("");
            }
        }
    }).focus(function() {
        if (this.value === "") {
            $(this).autocomplete("search", "");
        }
    });
}
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
function openFiltersModal() {
    $("#filter").modal('show');
    flatpickr(`.flatpickr-filter`, {
        mode: "range",
        dateFormat: "d-m-Y",
        defaultDate: []
    });
}
function applyFilters()
{
    reloadTableAjax();
    $("#filter").modal('hide');
}
//Reload the Datatable
function reloadTableAjax()
{
    reportDataTableInstance.ajax.reload();
}
</script>