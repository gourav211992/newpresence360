// Define custom sorting type for "formatted-date"
$.fn.dataTable.ext.type.order['formatted-date-pre'] = function(data) {
    if (!data) return 0; // If data is undefined, return 0 for safe sorting

    // Parse date in the format "04 Nov, 2024" to "YYYY-MM-DD" for sorting
    const [day, month, year] = data.split(' ');
    const monthMap = {
        Jan: '01', Feb: '02', Mar: '03', Apr: '04', May: '05', Jun: '06',
        Jul: '07', Aug: '08', Sep: '09', Oct: '10', Nov: '11', Dec: '12'
    };

    // Ensure month is mapped correctly
    if (!monthMap[month]) return 0;

    return new Date(`${year}-${monthMap[month]}-${day.padStart(2, '0')}`).getTime();
};

function initializeDataTable(selector, ajaxUrl, columns, filters = {}, exportTitle = 'Data', exportColumns = [], defaultOrder = [], pdfPageOrientation = 'portrait', ajaxRequestType = 'GET',showSearch = true,showButtons = true) {
    if ($('#datatable-loader').length === 0) {
        var loaderHtml = `
            <div id="datatable-loader" 
     style="position:fixed; top:0; left:0; width:100%; height:100%; 
            background: rgba(255,255,255,0.2); z-index:9999; 
            display:none; justify-content:center; align-items:center;">
    <div class="spinner-border text-primary" style="width:2rem; height:2rem;" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>
        `;
        $('body').append(loaderHtml);
    }
    var table = $(selector).on('processing.dt', function(e, settings, processing) {
        if (processing) {
            $('#datatable-loader').show();
            $('#datatable-loader').css('display', 'flex');
        } else {
            $('#datatable-loader').hide();
        }
    });
    if (table.length) {
        let dataTableInstance = table.DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            colReorder: true,
            scrollX: true,        // Enables horizontal scroll
            scrollCollapse: true, // Collapse scroll if not enough content
            fixedHeader: true, 
            searching: showSearch,
            lengthMenu: [[8, 10, 25, 50, 100, -1],[8, 10, 25, 50, 100, "ALL"]],
            ajax: {
                url: ajaxUrl,
                type: ajaxRequestType,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: function(d) {
                    // Loop through each filter key-value pair
                    $.each(filters, function(key, value) {
                        if (Array.isArray(value)) {
                            d[key]=[];
                            value.forEach((ky, val) => {
                                d[key].push(ky);  // Get the value from the HTML input
                            });
                        } else {
                            d[key] = $(value).val();  // Get the value from the HTML input
                        }
                    });
                }
            },
            order: defaultOrder,
            scrollY: '300px',
            columns: columns,
            columnDefs: [
                {
                    targets: '_all',
                    defaultContent: 'N/A' // Sets 'N/A' for all columns missing data
                },
            ],
            dom: `
                <"d-flex justify-content-between align-items-center mx-2 row"
                    <"col-sm-12 col-md-9 dt-action-buttons text-start"B>
                    <"col-sm-12 col-md-3 text-end"f>
                >
                r
                t
                <"d-flex justify-content-between align-items-top row"
                    <"col-sm-12 col-md-3"l>
                    <"col-sm-12 text-center col-md-6"i>
                    <"col-sm-12 col-md-3 text-end"p>
                >
            `,
            processing:true,
            buttons: showButtons ?[
                {
                    extend: 'collection',
                    className: 'btn btn-outline-secondary dropdown-toggle',
                    text: feather.icons['share'].toSvg({ class: 'font-small-4 mr-50' }) + ' Export',
                    buttons: [
                        { extend: 'print', text: feather.icons['printer'].toSvg({ class: 'font-small-4 mr-50' }) + ' Print', className: 'dropdown-item', title: exportTitle, exportOptions: { columns: exportColumns }},
                        { extend: 'csv', text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + ' CSV', className: 'dropdown-item', title: exportTitle, exportOptions: { columns: exportColumns }},
                        { extend: 'excel', text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + ' Excel', className: 'dropdown-item', title: exportTitle, exportOptions: { columns: exportColumns }},
                        { extend: 'pdf', text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 mr-50' }) + ' PDF', className: 'dropdown-item', title: exportTitle, exportOptions: { columns: exportColumns }, orientation: pdfPageOrientation},
                        { extend: 'copy', text: feather.icons['copy'].toSvg({ class: 'font-small-4 mr-50' }) + ' Copy', className: 'dropdown-item', title: exportTitle, exportOptions: { columns: exportColumns }},
                    ],
                    init: function(api, node, config) {
                        $(node).removeClass('btn-secondary').parent().removeClass('btn-group');
                        setTimeout(function() {
                            $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
                        }, 50);
                    }
                }
            ] : [],
            rowCallback: function(row, data) 
            {   
                console.log(filters.selected_ids);
                console.log(data.id);
                if (filters.selected_ids && filters.selected_ids.includes(String(data.id))) {
                    $(row).find('input[type="checkbox"]').prop('checked', true);
                    $(row).addClass('trselected'); // optional styling
                }
            },
            drawCallback: function() {
                feather.replace(); 
                $(document).on('click', '.myrequesttablecbox > tbody > tr', function () {
                    $('.myrequesttablecbox > tbody > tr').removeClass('trselected');
                    $(this).addClass('trselected');
                });

                $(document).on('keydown', function(e) { 
                 if (e.which == 38) {
                   $('.trselected').prev('tr').addClass('trselected').siblings().removeClass('trselected');
                 } else if (e.which == 40) {
                   $('.trselected').next('tr').addClass('trselected').siblings().removeClass('trselected');
                 } 
                 // $('html, body').scrollTop($('.trselected').offset().top - 100); 
                });
            },
            initComplete: function () {
                $('#DataTables_Table_0_length').appendTo('#custom_length');
                $('#DataTables_Table_0_paginate').appendTo('#custom_pagination');
                $('#DataTables_Table_0_info').appendTo('#custom_info');
                // $(".select2").select2();
            },
            language: {
                paginate: { previous: '&nbsp;', next: '&nbsp;' },
                processing: " " // a space, not empty string
            },
            search: { caseInsensitive: true }
        });
    
        return dataTableInstance;
    }
}
