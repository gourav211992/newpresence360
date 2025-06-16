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

function initializeDataTable(selector, ajaxUrl, columns, filters = {}, exportTitle = 'Data', exportColumns = [], defaultOrder = [], pdfPageOrientation = 'portrait', ajaxRequestType = 'GET') {
    var table = $(selector);
    if (table.length) {
        let dataTableInstance = table.DataTable({
            processing: true,
            serverSide: true,
            scrollX: true,
            colReorder: true,
            ajax: {
                url: ajaxUrl,
                type: ajaxRequestType,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: function(d) {
                    // Loop through each filter key-value pair
                    $.each(filters, function(key, value) {
                        d[key] = $(value).val();  // Get the value from the HTML input
                    });
                }
            },
            order: defaultOrder,
            columns: columns,
            columnDefs: [{
                targets: '_all',
                defaultContent: 'N/A' // Set default content for missing data
            }],
            dom: '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
            buttons: [
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
            ],
            drawCallback: function() {
                feather.replace(); 
                $(document).on('click', '.myrequesttablecbox tbody tr', (e) => {
                    $('tr').removeClass('trselected');
                    $(e.target).closest('tr').addClass('trselected');
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
            language: {
                paginate: { previous: ' ', next: ' ' }
            },
            search: { caseInsensitive: true }
        });
        return dataTableInstance;
    }
}

function initializeDataTableCustom(selector, ajaxUrl, columns, filters = {}, exportTitle = 'Data', exportColumns = [], defaultOrder = [], enableExport = true, enableSearch = true, pdfPageOrientation = 'portrait', ajaxRequestType = 'GET') {
    var table = $(selector);
    if (table.length) {
        let buttonsConfig = [];
        if (enableExport) {
            buttonsConfig = [
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
            ];
        }
        let dataTableInstance = table.DataTable({
            processing: true,
            serverSide: true,
            colReorder: true,
            scrollY: '300px',     // Fixed height for table body
            scrollX: true,        // Enables horizontal scroll
            scrollCollapse: true, // Collapse scroll if not enough content
            fixedHeader: true,  
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'All']],
            ajax: {
                url: ajaxUrl,
                type: ajaxRequestType,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: function(d) {
                    // Loop through each filter key-value pair
                    $.each(filters, function(key, value) {
                        d[key] = $(value).val();  // Get the value from the HTML input
                    });
                    let dynamicParams = typeof getDynamicParams === 'function' ? getDynamicParams() : {};
                    Object.assign(d, dynamicParams);
                }
            },
            order: defaultOrder,
            columns: columns,
            columnDefs: [{
                targets: '_all',
                defaultContent: 'N/A' // Set default content for missing data
            }],
            searching: false,
            // dom: getDataTableDOM(enableExport, enableSearch),
            buttons: buttonsConfig,
            drawCallback: function() {
                feather.replace(); 
                $(document).on('click', '.myrequesttablecbox tbody tr', (e) => {
                    $('tr').removeClass('trselected');
                    $(e.target).closest('tr').addClass('trselected');
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
                paginate: { previous: ' ', next: ' ' }
            },
            search: { caseInsensitive: true }
        });
        return dataTableInstance;
    }
}

function getDataTableDOM() {
    return `<"d-flex justify-content-between align-items-center mx-2 row"
                <"col-sm-12 col-md-9 dt-action-buttons text-start"B>
                <"col-sm-12 col-md-3 text-end"f>
            >
            t
            <"d-flex justify-content-between align-items-top row"
            <"col-sm-12 col-md-3"l>
                <"col-sm-12 text-center col-md-6"i>
                <"col-sm-12 col-md-3 text-end"p>
            >`;
}