@extends('layouts.app')
@section('content')
    <div class="app-content content">
        <div class="content-overlay"></div>
        <div class="header-navbar-shadow"></div>
        <div class="content-wrapper container-xxl p-0">


            <div class="content-body">

                <section id="basic-datatable">
                    <div class="card border  overflow-hidden">
                        <div class="row">
                            <div class="col-md-12 bg-light border-bottom mb-1 po-reportfileterBox">
                                <div class="row pofilterhead action-button align-items-center">
                                    <div class="col-md-4">
                                        <h3>TDS Report</h3>
                                        <p>{{$fy}}</p>
                                    </div>
                                    <div
                                        class="col-md-8 text-sm-end pofilterboxcenter mb-0 d-flex flex-wrap align-items-center justify-content-sm-end">
                                        <button class="btn btn-primary btn-sm mb-50 mb-sm-0 me-50" data-bs-target="#filter"
                                            data-bs-toggle="modal"><i data-feather="filter"></i> Filter</button>
                                    </div>
                                </div>


                            </div>
                            <div class="col-md-12">
                                <div
                                    class="table-responsive trailbalnewdesfinance po-reportnewdesign trailbalnewdesfinancerightpad gsttabreporttotal">
                                    <table class="datatables-basic table myrequesttablecbox">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Vendor Name</th>
                                                <th>PAN</th>
                                                <th>Section</th>
                                                <th>Type of<br />Deductee</th>
                                                <th>Voch. No.</th>
                                                <th class="text-end">Amount<br />Paid/Credited</th>
                                                <th>Paid/Credited<br />Date</th>
                                                <th>Cash With.<br />Exceed. Limit</th>
                                                <th>Deduction<br />Date</th>
                                                <th class="text-end">Deducted<br />Amt</th>
                                                <th>Deduction<br />Rate</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $serial = 1; @endphp
                                           @foreach($records as $i => $row)
                                                @php
                                                       $eh =  App\Models\Voucher::where('reference_doc_id',$row->expenseHeader?->id)->where('reference_service','expense-advice')->first();
                                                @endphp
                                                @if($row->expenseHeader?->vendor != null && $row->assesment_amount > 0 && $eh)
                                                        <tr class="trail-bal-tabl-none">
                                                            <td>{{ $serial++ }}</td>
                                                            <td>
                                                                <div style="width: 200px" class="fw-bolder text-dark">
                                                                    {{ $row->expenseHeader?->vendor?->company_name ?? 'N/A' }}
                                                                </div>
                                                            </td>
                                                            <td>{{ $row->expenseHeader?->vendor?->pan_number ?? 'N/A' }}</td>
                                                            <td>
                                                                <div style="width: 200px">
                                                                           {{ \App\Helpers\ConstantHelper::getTdsSections()[$row->taxDetail?->ledger?->tds_section] ?? 'N/A' }}
                                                                </div>
                                                            </td>

                                                            <td>
                                                                {{ $row->expenseHeader?->vendor ? 
                                                                    (in_array($row->expenseHeader?->vendor?->erpOrganizationType?->name, ['Private Limited', 'Public Limited']) ? 'Company' : 'Non-Company') 
                                                                    : 'N/A' }}
                                                            </td>
                                                            <td>
                                                                <span class="badge rounded-pill badge-light-secondary badgeborder-radius">
                                                                    {{ $eh->voucher_no }}
                                                                </span>
                                                            </td>
                                                            <td class="text-end">{{ number_format($row->assesment_amount, 2) }}</td>
                                                            <td>{{ $row->expenseHeader ? date('d/m/Y', strtotime($row->expenseHeader?->document_date)) : 'N/A' }}</td>
                                                            <td>No</td>
                                                            <td>{{ $row->expenseHeader ? date('d/m/Y', strtotime($row->expenseHeader?->document_date)) : 'N/A' }}</td>
                                                            <td class="text-end">{{ 
                                                                 number_format($row->ted_amount, 2) 
                                                            }}</td>
                                                            <td>{{ $row->ted_percentage ? number_format($row->ted_percentage,2) . '%' : '0.00%' }}</td>
                                                        </tr>
                                                        @endif
                                                @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="5" class="text-center">Total</td>
                                                <td class="text-end">&nbsp;</td>
                                                <td id="credited" class="text-end"></td>
                                                <td class="text-end">&nbsp;</td>
                                                <td class="text-end">&nbsp;</td>
                                                <td class="text-end"></td>
                                                <td id="deducted" class="text-end"></td>
                                                <td class="text-end">&nbsp;</td>
                                            </tr>
                                        </tfoot>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </section>
                <!-- ChartJS section end -->

            </div>
        </div>
    </div>

    <div class="modal modal-slide-in fade filterpopuplabel" id="filter">
        <div class="modal-dialog sidebar-sm">
            <form class="add-new-record modal-content pt-0" method="GET" action="{{ route('finance.tds') }}">
                <div class="modal-header mb-1">
                    <h5 class="modal-title" id="exampleModalLabel">Apply Filter</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                </div>
                <div class="modal-body flex-grow-1">
                    <div class="mb-1">
                        <label class="form-label" for="fp-range">Select Period</label>
                        <!--                        <input type="text" id="fp-default" class="form-control flatpickr-basic" placeholder="YYYY-MM-DD" />-->
                        <input type="text" id="fp-range" name="date" class="form-control flatpickr-range bg-white"
                            placeholder="YYYY-MM-DD to YYYY-MM-DD" value="{{$range}}" />
                    </div>



                    <div class="mb-1">
                        <label class="form-label">Organization</label>
                        <select name="organization_filter" id="organization_filter" class="form-select select2" multiple>
                            <option value="">Select</option>
                            @foreach ($mappings as $organization)
                        <option value="{{ $organization->organization->id }}"
                            {{ $organization->organization->id == $organization_id ? 'selected' : '' }}>
                            {{ $organization->organization->name }}
                        </option>
                    @endforeach

                        </select>
                    </div>
                     <div class="mb-1">
                        <label class="form-label">Location</label>
                        <select id="location_id" name="location_id" class="form-select select2">
                        </select>
                    </div>
                     <div class="mb-1">
                        <label class="form-label">Cost Group</label>
                        <select id="cost_group_id" name="cost_group_id" class="form-select select2">
                        </select>
                    </div>
                    <div class="mb-1">
                        <label class="form-label">Cost Center</label>
                        <select id="cost_center_id" class="form-select select2"
                            name="cost_center_id">
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">TDS Section</label>
                        <select class="form-select select2" name="tax_filter">
                            <option value="">Select</option>
                            @foreach($taxTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('tds_section') == $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                        </select>
                    </div>

                    <div class="mb-1">
                        <label class="form-label">Vendor Name</label>
                        <select class="form-select select2" name="vendor_filter">
                        <option value="">Select</option>
                        @foreach($vendors as $org)
                        <option value="{{$org->id}}" @if($org->id===$vendor_id) selected @endif>{{$org->company_name}}</option>
                        @endforeach
                        </select>
                    </div>



                </div>
                <div class="modal-footer justify-content-start">
                    <button type="submit" class="btn btn-primary data-submit mr-1">Apply</button>
                    <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
@endsection



@section('scripts')
<script>
    const locations = @json($locations);
    const costCenters = @json($cost_centers);
    const costGroups = @json($cost_groups);
</script>
<script>
    $(window).on('load', function() {
        $('.preloader').css('display', 'flex');[]
        $('.preloader').fadeOut();
        if (feather) {
            feather.replace({
                width: 14,
                height: 14
            });
        }
    })

    // Failsafe in case load never triggers (e.g. network failure or redirect)
        setTimeout(function () {
            $('.preloader').fadeOut();
        }, 15000);

    $(function() {
        $(".sortable").sortable();
    });
    function updateLocationsDropdown(selectedOrgId) {
        console.log(selectedOrgId,'selected')
        const filteredLocations = locations.filter(loc =>
            // String(loc.organization_id) === String(selectedOrgId)
            selectedOrgId.includes(String(loc.organization_id))

        );

        const $locationDropdown = $('#location_id');
        $locationDropdown.empty().append('<option value="">Select</option>');
        const selectedLocationId = "{{ $location_id }}";


        filteredLocations.forEach(loc => {
        // const isSelected = String(loc.id) === String(selectedLocationId) ? 'selected' : '';
        $locationDropdown.append(`<option value="${loc.id}" >${loc.store_name}</option>`);
        });

        $locationDropdown.trigger('change');
    }
    function loadCostGroupsByLocation(locationId) {
        const costCenter = $('#cost_center_id');
        costCenter.val(@json(request('cost_center_id')) || "");
        const filteredCenters = costCenters.filter(center => {
            if (!center.location) return false;
            const locationArray = Array.isArray(center.location)
                ? center.location.flatMap(loc => loc.split(','))
                : [];
            return locationArray.includes(String(locationId));
        });

        const costGroupIds = [...new Set(filteredCenters.map(center => center.cost_group_id))];
        
        const filteredGroups = costGroups.filter(group => costGroupIds.includes(group.id));
        console.log(filteredCenters,costGroupIds,filteredGroups);

        const $groupDropdown = $('#cost_group_id');
        $groupDropdown.empty().append('<option value="">Select Cost Group</option>');

        filteredGroups.forEach(group => {
            $groupDropdown.append(`<option value="${group.id}">${group.name}</option>`);
        });

        $('#cost_group_id').trigger('change');
    }

    function loadCostCentersByGroup(locationId, groupId) {
        const costCenter = $('#cost_center_id');
        costCenter.empty();

        const filteredCenters = costCenters.filter(center => {
            if (!center.location || center.cost_group_id !== groupId) return false;

            const locationArray = Array.isArray(center.location)
                ? center.location.flatMap(loc => loc.split(','))
                : [];

            return locationArray.includes(String(locationId));
        });

        if (filteredCenters.length === 0) {
            costCenter.prop('required', false);
            $('#cost_center_id').hide();
        } else {
            costCenter.append('<option value="">Select Cost Center</option>');
            $('#cost_center_id').show();

            filteredCenters.forEach(center => {
                costCenter.append(`<option value="${center.id}">${center.name}</option>`);
            });
        }
        costCenter.val(@json(request('cost_center_id')) || "");
        costCenter.trigger('change');
    }

    $(document).ready(function() {
    // On change of organization
    const preselectedOrgId = $('#organization_filter').val();
    const preselectedLocationId = "{{ $location_id }}";
    const preselectedGroupId = "{{ $cost_group_id }}";
    const preselectedCenterId = @json($cost_center_id);

    if (preselectedOrgId) {
        updateLocationsDropdown(preselectedOrgId);
    }

    if (preselectedLocationId) {
        // Load Cost Groups and then continue
        const filteredCenters = costCenters.filter(center => {
            if (!center.location) return false;
            const locationArray = Array.isArray(center.location)
                ? center.location.flatMap(loc => loc.split(','))
                : [];
            return locationArray.includes(String(preselectedLocationId));
        });

        const costGroupIds = [...new Set(filteredCenters.map(center => center.cost_group_id))];
        const filteredGroups = costGroups.filter(group => costGroupIds.includes(group.id));

        const $groupDropdown = $('#cost_group_id');
        $groupDropdown.empty().append('<option value="">Select Cost Group</option>');

        filteredGroups.forEach(group => {
            const selected = String(group.id) === String(preselectedGroupId) ? 'selected' : '';
            $groupDropdown.append(`<option value="${group.id}" ${selected}>${group.name}</option>`);
        });

        // Manually trigger change to load cost centers
        $groupDropdown.trigger('change');

        // Now load cost centers if group is selected
        if (preselectedGroupId) {
            const costCenter = $('#cost_center_id');
            costCenter.empty();

            const filteredCentersByGroup = costCenters.filter(center => {
                if (!center.location || center.cost_group_id !== parseInt(preselectedGroupId)) return false;

                const locationArray = Array.isArray(center.location)
                    ? center.location.flatMap(loc => loc.split(','))
                    : [];

                return locationArray.includes(String(preselectedLocationId));
            });

            if (filteredCentersByGroup.length === 0) {
                costCenter.prop('required', false);
                costCenter.hide();
            } else {
                costCenter.append('<option value="">Select Cost Center</option>');
                costCenter.show();

                filteredCentersByGroup.forEach(center => {
                    const selected = String(center.id) === String(preselectedCenterId) ? 'selected' : '';
                    costCenter.append(`<option value="${center.id}" ${selected}>${center.name}</option>`);
                });
            }

            costCenter.trigger('change');
        }
    }

    // Also trigger cost group and center change if needed
    $('#location_id').on('change', function () {
        loadCostGroupsByLocation($(this).val());
    });

    $('#cost_group_id').on('change', function () {
        const locationId = $('#location_id').val();
        const groupId = parseInt($(this).val());
        if (locationId && groupId) {
            loadCostCentersByGroup(locationId, groupId);
        }
    });
        $(".open-job-sectab").click(function() {
            $(this).parent().parent().next('tr').show();
            $(this).parent().find('.close-job-sectab').show();
            $(this).parent().find('.open-job-sectab').hide();
        });
         $('.add-new-record').on('submit', function () {
            $('.preloader').fadeIn(); // show preloader
        });
    });
     $('#cost_group_id').on('change', function () {
        const locationId = $('#location_id').val();
        const groupId = parseInt($(this).val());

        if (!locationId || !groupId) {
            $('#cost_center_id').empty().append('<option value="">Select Cost Center</option>');
            return;
        }

        loadCostCentersByGroup(locationId, groupId);
    });
    $(function() {
    var dt_basic_table = $('.datatables-basic'),
        assetPath = '../../../app-assets/';

    if ($('body').attr('data-framework') === 'laravel') {
        assetPath = $('body').attr('data-asset-path');
    }

    // DataTable with buttons
    if (dt_basic_table.length) {
        var dt_basic = dt_basic_table.DataTable({
            order: [
                [0, 'asc']
            ],
            dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-3"l><"col-sm-12 col-md-6 withoutheadbuttin dt-action-buttons text-end pe-0"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            displayLength: 8,
            lengthMenu: [8, 10, 25, 50, 75, 100],
            buttons: [{
                extend: 'collection',
                className: 'btn btn-outline-secondary dropdown-toggle',
                text: feather.icons['share'].toSvg({
                    class: 'font-small-3 me-50'
                }) + 'Export',
            buttons: [
    {
        extend: 'excelHtml5',
         title: null,
        text: feather.icons['file'].toSvg({
            class: 'font-small-4 me-50'
        }) + 'Excel',
        className: 'dropdown-item',
        filename: 'TDS Report',
       customize: function (xlsx) {
    const sheet = xlsx.xl.worksheets['sheet1.xml'];
    const sheetData = sheet.getElementsByTagName('sheetData')[0];

    // Step 1: Shift all rows down by 4
    const rows = sheetData.getElementsByTagName('row');
    for (let i = 0; i < rows.length; i++) {
        const r = rows[i];
        const currentRowNum = parseInt(r.getAttribute('r'));
        r.setAttribute('r', currentRowNum + 4);

        const cells = r.getElementsByTagName('c');
        for (let j = 0; j < cells.length; j++) {
            const cell = cells[j];
            const cellRef = cell.getAttribute('r');
            if (cellRef) {
                const col = cellRef.replace(/[0-9]/g, '');
                cell.setAttribute('r', col + (currentRowNum + 4));
            }
        }
    }

    const styles = xlsx.xl['styles.xml'];

    // Add bold font
    const fonts = styles.getElementsByTagName('fonts')[0];
    const font = styles.createElement('font');
    const bold = styles.createElement('b');
    font.appendChild(bold);
    fonts.appendChild(font);
    fonts.setAttribute('count', fonts.childNodes.length);

    // Create bold+right and bold+left alignment styles
    const cellXfs = styles.getElementsByTagName('cellXfs')[0];

    const rightXf = styles.createElement('xf');
    rightXf.setAttribute('fontId', fonts.childNodes.length - 1);
    rightXf.setAttribute('applyFont', '1');
    rightXf.setAttribute('applyAlignment', '1');
    rightXf.setAttribute('numFmtId', '0');
    rightXf.setAttribute('borderId', '0');
    rightXf.setAttribute('fillId', '0');
    const rightAlign = styles.createElement('alignment');
    rightAlign.setAttribute('horizontal', 'right');
    rightXf.appendChild(rightAlign);
    cellXfs.appendChild(rightXf);

    const leftXf = styles.createElement('xf');
    leftXf.setAttribute('fontId', fonts.childNodes.length - 1);
    leftXf.setAttribute('applyFont', '1');
    leftXf.setAttribute('applyAlignment', '1');
    leftXf.setAttribute('numFmtId', '0');
    leftXf.setAttribute('borderId', '0');
    leftXf.setAttribute('fillId', '0');
    const leftAlign = styles.createElement('alignment');
    leftAlign.setAttribute('horizontal', 'left');
    leftXf.appendChild(leftAlign);
    cellXfs.appendChild(leftXf);

    cellXfs.setAttribute('count', cellXfs.childNodes.length);

    const rightAlignBoldStyleIndex = cellXfs.childNodes.length - 2;
    const leftAlignBoldStyleIndex = cellXfs.childNodes.length - 1;

    function createRow(index, text, styleIndex = '2') {
        const row = sheet.createElement('row');
        row.setAttribute('r', index);

        const c = sheet.createElement('c');
        c.setAttribute('t', 'inlineStr');
        c.setAttribute('r', 'A' + index);
        c.setAttribute('s', styleIndex);

        const is = sheet.createElement('is');
        const t = sheet.createElement('t');
        t.textContent = text;

        is.appendChild(t);
        c.appendChild(is);
        row.appendChild(c);

        return row;
    }

    const org_names = $('#organization_filter option:selected').map(function () {
        return $(this).text().trim();
    }).get().join(', ');
    const date_range = $('#fp-range').val();

    const orgRow = createRow(1, org_names);
    const tdsTitleRow = createRow(2, 'TDS Report', '0');
    const dateRow = createRow(3, date_range);
    const emptyRow = sheet.createElement('row');
emptyRow.setAttribute('r', '4');

const emptyCell = sheet.createElement('c');
emptyCell.setAttribute('r', 'A4');
emptyCell.setAttribute('t', 'inlineStr');

const is = sheet.createElement('is');
const t = sheet.createElement('t');
t.textContent = ''; // empty string

is.appendChild(t);
emptyCell.appendChild(is);
emptyRow.appendChild(emptyCell)

    sheetData.insertBefore(emptyRow, sheetData.firstChild);       // Row 4
    sheetData.insertBefore(dateRow, sheetData.firstChild);        // Row 3
    sheetData.insertBefore(tdsTitleRow, sheetData.firstChild);    // Row 2
    sheetData.insertBefore(orgRow, sheetData.firstChild);         // Row 1

    // Step 3: Merge A1:L1, A2:L2, A3:L3
    const mergedColsCount = 12;
    const lastColLetter = String.fromCharCode('A'.charCodeAt(0) + mergedColsCount - 1);

    let mergeCells = sheet.getElementsByTagName('mergeCells')[0];
    if (!mergeCells) {
        mergeCells = sheet.createElement('mergeCells');
        sheet.documentElement.appendChild(mergeCells);
    }

    function createMergeRef(rowNum) {
        const mergeCell = sheet.createElement('mergeCell');
        mergeCell.setAttribute('ref', `A${rowNum}:${lastColLetter}${rowNum}`);
        return mergeCell;
    }

    mergeCells.appendChild(createMergeRef(1));
    mergeCells.appendChild(createMergeRef(2));
    mergeCells.appendChild(createMergeRef(3));
    mergeCells.setAttribute('count', mergeCells.childNodes.length);
    function normalizeHeader(header) {
        return header.replace(/\s+/g, '').trim();
    }

    // Step 4: Align header row
    const headerRow = sheetData.getElementsByTagName('row')[4]; // new header row
    const headerCells = headerRow.getElementsByTagName('c');
    const headerMap = {
        'VendorName': 'Vendor Name',
        'TypeofDeductee': 'Type  of  Deductee',
        'Type ofDeductee': 'Type of Deductee', // ✅ FIX for your case
        'AmountPaid/Credited': 'Amount Paid/Credited',
        'Paid/CreditedDate': 'Paid/Credited Date',
        'CashWith.Exceed.Limit': 'Cash withdrawal Exceeding Limit',
        'DeductionDate': 'Deduction Date',
        'DeductedAmt': 'Deducted Amt',
        'DeductionRate': 'Deduction Rate'
    };
    const rightAlignCols = ['G', 'K'];
    const alwaysLeftAlignCols = ['F', 'L'];
   for (let i = 0; i < headerCells.length; i++) {
    const cell = headerCells[i];
    const isNode = cell.getElementsByTagName('is')[0];
    const tNode = isNode?.getElementsByTagName('t')[0];
    const original = tNode?.textContent?.trim();

    if (original) {
        const normalized = normalizeHeader(original);
        if (headerMap[normalized]) {
            tNode.textContent = headerMap[normalized];
        }
    }

    const cellRef = cell.getAttribute('r');
    const colLetter = cellRef.replace(/[0-9]/g, '');

    if (rightAlignCols.includes(colLetter)) {
        cell.setAttribute('s', rightAlignBoldStyleIndex.toString());
    } else if (alwaysLeftAlignCols.includes(colLetter)) {
        cell.setAttribute('s', leftAlignBoldStyleIndex.toString());
    } else {
        cell.setAttribute('s', leftAlignBoldStyleIndex.toString());
    }
}

    // Step 5: Format DeductionDate column (e.g., M) to 'dd-mm-yyyy'
    const dataRows = sheetData.getElementsByTagName('row');

for (let i = 5; i < dataRows.length; i++) {
    const cells = dataRows[i].getElementsByTagName('c');

    for (let j = 0; j < cells.length; j++) {
        const cell = cells[j];
        const cellRef = cell.getAttribute('r');
        const colLetter = cellRef.replace(/[0-9]/g, '');

        // --- 1. Format DeductionDate in J and Paid/CreditedDate in H
        if (['H', 'J'].includes(colLetter)) {
            const isNode = cell.getElementsByTagName('is')[0];
            const tNode = isNode?.getElementsByTagName('t')[0];

            if (tNode && /^\d{2}\/\d{2}\/\d{4}$/.test(tNode.textContent)) {
                const [dd, mm, yyyy] = tNode.textContent.split('/');
                tNode.textContent = `${dd}-${mm}-${yyyy}`;
            }

            cell.setAttribute('t', 'inlineStr');
        }

        // --- 2. Format DeductionRate in column L from 0.18 → 18%
        if (colLetter === 'L') {
            let vNode = cell.getElementsByTagName('v')[0];
            if (vNode && !isNaN(vNode.textContent)) {
                const val = parseFloat(vNode.textContent);
                const percentage = `${(val * 100).toFixed(0)}%`;

                // Replace with inline string
                while (cell.firstChild) cell.removeChild(cell.firstChild);
                const is = sheet.createElement('is');
                const t = sheet.createElement('t');
                t.textContent = percentage;
                is.appendChild(t);
                cell.appendChild(is);
                cell.setAttribute('t', 'inlineStr');
            }
        }

        // --- 3. Align columns F and L (left), G and K (right) — remove bold style!
        if (['F', 'L'].includes(colLetter)) {
            cell.setAttribute('s', leftAlignBoldStyleIndex);
        }
        if (['G', 'K'].includes(colLetter)) {
            let vNode = cell.getElementsByTagName('v')[0];
            if (vNode && !isNaN(vNode.textContent)) {
                const val = parseFloat(vNode.textContent);
                const formatted = val.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

                // Replace with inline string
                while (cell.firstChild) cell.removeChild(cell.firstChild);
                const is = sheet.createElement('is');
                const t = sheet.createElement('t');
                t.textContent = formatted;
                is.appendChild(t);
                cell.appendChild(is);
                cell.setAttribute('t', 'inlineStr');
            }

            cell.setAttribute('s', rightAlignBoldStyleIndex);
        }
    }
}

}
    }
]

,
                init: function(api, node, config) {
                    $(node).removeClass('btn-secondary');
                    $(node).parent().removeClass('btn-group');
                    setTimeout(function() {
                        $(node).closest('.dt-buttons').removeClass('btn-group')
                            .addClass('d-inline-flex');
                    }, 50);
                }
            }],
            language: {
                search: '',
                searchPlaceholder: "Search...",
                paginate: {
                    previous: '&nbsp;',
                    next: '&nbsp;'
                }
            }
        });

        // Function to update the total values in the footer
                function updateFooterTotals() {
            var totalCredited = 0;
            var totalDebited = 0;

            // Loop through each row on the current page and calculate the totals
            dt_basic.rows({ page: 'current' }).every(function() {
                var data = this.data();
                var credited = parseFloat(data[6].replace(/,/g, '')) || 0; // Assuming the credited amount is in column 6
                var debited = parseFloat(data[10].replace(/,/g, '')) || 0; // Assuming the debited amount is in column 10

                totalCredited += credited;
                totalDebited += debited;
            });

            // Format the totals with commas and 2 decimal places
            var formattedCredited = totalCredited.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
            var formattedDebited = totalDebited.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');

            // Update the footer with the formatted totals
            $('#credited').text(formattedCredited); // Update the credited total in the footer
            $('#deducted').text(formattedDebited); // Update the debited total in the footer
        }

        // Update the footer totals on table draw (when a page change occurs)
        dt_basic.on('draw', function() {
            updateFooterTotals();
        });

        // Initial footer update (in case the page is already loaded with data)
        updateFooterTotals();

        $('div.head-label').html('<h6 class="mb-0">Event List</h6>');
    }
});
</script>

@endsection
