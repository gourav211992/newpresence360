function handleRowSelection(tableSelector) {
    $(tableSelector).on('click', 'tbody tr', function () {
        // Remove from all, then add to clicked row
        $(tableSelector).find('tr').removeClass('trselected');
        $(this).addClass('trselected');
    });

    $(document).on('keydown', function (e) {
        const $selected = $(tableSelector).find('.trselected');
        if (e.which == 38) {  // Up arrow key
            $selected.prev('tr').addClass('trselected').siblings().removeClass('trselected');
        } else if (e.which == 40) {  // Down arrow key
            $selected.next('tr').addClass('trselected').siblings().removeClass('trselected');
        }
    });
}
function initializeBasicDataTable(selector, exportFileName = 'Data_Export', link = null) {
    var dt_table = $(selector),
        assetPath = '../../../app-assets/';

    if ($('body').attr('data-framework') === 'laravel') {
        assetPath = $('body').attr('data-asset-path');
    }

    if (dt_table.length) {
        var dt = dt_table.DataTable({
            order: [],
            columnDefs: [
                {
                    orderable: false,
                    targets: [0, -1]
                },
                {
                    targets: '_all', 
                    render: function (data, type, row, meta) {
                        if (type === 'export') {
                            var $node = $('<div>').html(data);
                            return $node.find('.usernames').text();
                        }
                        return data;
                    }
                }
            ],
            dom: '<"d-flex justify-content-between align-items-center mx-2 row"' +
                '<"col-sm-12 col-md-6"l>' +
                '<"col-sm-12 col-md-3 withoutheadbuttin dt-action-buttons text-end"B>' +
                '<"col-sm-12 col-md-3"f>>t' +
                '<"d-flex justify-content-between mx-2 row"' +
                '<"col-sm-12 col-md-6"i>' +
                '<"col-sm-12 col-md-6"p>>',
            scrollX: true,
            displayLength: 7,
            lengthMenu: [7, 10, 25, 50, 75, 100],
            buttons: [
                {
                    extend: 'excel',
                    className: 'btn btn-outline-secondary',
                    text: feather.icons['file-text'].toSvg({ class: 'font-small-4 mr-50' }) + 'Excel',
                    filename: exportFileName,
                    exportOptions: {
                        columns: ':not(:last-child)' // exclude last column
                    },
                    init: function (api, node, config) {
                        $(node).removeClass('btn-secondary');
                        setTimeout(function () {
                            $(node).closest('.dt-buttons').removeClass('btn-group').addClass('d-inline-flex');
                        }, 50);
                    },
                    action: function (e, dt, button, config) {
                        if (link) {
                            // If a link is provided, redirect to it or fetch data from it
                            window.open(link);
                        } else {
                            // Default excel export action
                            $.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, button, config);
                        }
                    }
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
            }
        });

        // Optional: flatpickr for date inputs inside table
        dt_table.find('input.flatpickr').flatpickr({
            monthSelectorType: 'static',
            dateFormat: 'm/d/Y'
        });

        // Delete row action
        dt_table.find('tbody').on('click', '.delete-record', function () {
            dt.row($(this).parents('tr')).remove().draw();
        });

        return dt;
    }

    return null;
}
