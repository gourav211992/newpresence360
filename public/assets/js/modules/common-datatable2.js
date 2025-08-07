// common_datatable.js

$.fn.dataTable.ext.type.order['formatted-date-pre'] = function(data) {
    if (!data) return 0;

    const [day, month, year] = data.split(' ');
    const monthMap = {
        Jan: '01', Feb: '02', Mar: '03', Apr: '04', May: '05', Jun: '06',
        Jul: '07', Aug: '08', Sep: '09', Oct: '10', Nov: '11', Dec: '12'
    };

    if (!monthMap[month]) return 0;

    return new Date(`${year}-${monthMap[month]}-${day.padStart(2, '0')}`).getTime();
};

function setupCustomColumnSearch(dataTable, columnIndex, data) {
    let input = document.createElement('input');
    input.type = "text";
    input.placeholder = "Search";
    $(data().header()).text("");
    $(data().header()).append(input);
    $(input).css('width', '100%');

    let searchFunction = function () {
        dataTable
            .column(columnIndex)
            .search(this.value)
            .draw();
    };
    $(input).on('keyup change', searchFunction);
}

function initializeDataTable({
    selector,
    ajaxUrl,
    columns,
    exportUrl,
    filenamePrefix = 'download',
    domLayout = '<"d-flex justify-content-between align-items-center mx-2 row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-3 dt-action-buttons text-end"B><"col-sm-12 col-md-3"f>>t<"d-flex justify-content-between mx-2 row"<"col-sm-12 col-md-6"i><"col-sm-12 col-md-6"p>>',
    buttonsEnabled = true,
    languageOptions = {
        paginate: {
            previous: '&nbsp;',
            next: '&nbsp;'
        }
    },
    searchOptions = { caseInsensitive: true },
    drawCallback = null,
    otherOptions = {},
    customColumnSearchIndices 

}) {
    var table = $(selector);

    if (table.length) {
        if (table[0] instanceof HTMLTableElement) {
            console.log("Valid TABLE element found:", table[0]);

            let dt = table.DataTable($.extend({
                processing: true,
                serverSide: true,
                ajax: ajaxUrl,
                columns: columns,
                dom: domLayout,
                buttons: buttonsEnabled ? [
                    {
                        extend: 'collection',
                        className: 'btn btn-outline-secondary dropdown-toggle',
                        text: feather.icons['share'].toSvg({ class: 'font-small-4 mr-50' }) + ' Export',
                        buttons: [
                            {
                                extend: 'print',
                                text: feather.icons['printer'].toSvg({ class: 'font-small-4 mr-50' }) + ' Print',
                                className: 'dropdown-item',
                                action: function(e, dt, node, config) {
                                    exportAllData('print', dt);
                                }
                            },
                            {
                                extend: 'csv',
                                text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + ' CSV',
                                className: 'dropdown-item',
                                action: function(e, dt, node, config) {
                                    exportAllData('csv', dt);
                                }
                            },
                            {
                                extend: 'excel',
                                text: feather.icons['file'].toSvg({ class: 'font-small-4 mr-50' }) + ' Excel',
                                className: 'dropdown-item',
                                action: function(e, dt, node, config) {
                                    exportAllData('excel', dt);
                                }
                            },
                            {
                                extend: 'pdf',
                                text: feather.icons['clipboard'].toSvg({ class: 'font-small-4 mr-50' }) + ' PDF',
                                className: 'dropdown-item',
                                action: function(e, dt, node, config) {
                                    exportAllData('pdf', dt);
                                }
                            },
                            {
                                extend: 'copy',
                                text: feather.icons['copy'].toSvg({ class: 'font-small-4 mr-50' }) + ' Copy',
                                className: 'dropdown-item',
                                action: function(e, dt, node, config) {
                                    exportAllData('copy', dt);
                                }
                            }
                        ],

                    }
                ] : [],
                drawCallback: function() {
                    feather.replace();
                    if (drawCallback) {
                        drawCallback.call(this);
                    }
                },
                language: languageOptions,
                search: searchOptions,
                initComplete: function () {
                    var dataTable = this.api();
                    if (customColumnSearchIndices !== undefined && Array.isArray(customColumnSearchIndices)) {
                        customColumnSearchIndices.forEach(function (columnIndex) {
                            setupCustomColumnSearch(dataTable, columnIndex, dataTable.column(columnIndex));
                        });
                    }
                }
            }, otherOptions));

            function exportAllData(type, dt_instance) {
                var search = $('.dataTables_filter input').val();
                var order = dt_instance.order();
                var url = exportUrl + "?export_type=" + type + "&search=" + encodeURIComponent(search) + "&order=" + JSON.stringify(order);
                var filenameMap = {
                    'csv': filenamePrefix + '.csv',
                    'excel': filenamePrefix + '.xlsx',
                    'pdf': filenamePrefix + '.pdf'
                };
                var filename = filenameMap[type] || 'download';
                exportData({ url: url, type: type, filename: filename });
            }

            function exportData({ url, type, filename }) {
                if (type === 'print') {
                    $.get(url, function(html) {
                        var printWindow = window.open('', '_blank');
                        printWindow.document.write(html);
                        printWindow.document.close();
                        printWindow.print();
                    });
                } else if (type === 'copy') {
                    $.ajax({
                        url: url,
                        method: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            var text = JSON.stringify(data, null, 2);
                            navigator.clipboard.writeText(text).then(function() {
                                alert('Copied to clipboard!');
                            });
                        }
                    });
                } else {
                    $.ajax({
                        url: url,
                        method: 'GET',
                        xhrFields: { responseType: 'blob' },
                        success: function(blob) {
                            var urlBlob = window.URL.createObjectURL(blob);
                            var a = document.createElement('a');
                            a.href = urlBlob;
                            a.download = filename || 'download';
                            document.body.appendChild(a);
                            a.click();
                            a.remove();
                            window.URL.revokeObjectURL(urlBlob);
                        }
                    });
                }
            }
            return dt;
        } else {
            console.error("Invalid TABLE element.  Selector returned:", table);
            return null;
        }
    } else {
        console.error("No table element found with selector:", selector);
        return null;
    }
}
