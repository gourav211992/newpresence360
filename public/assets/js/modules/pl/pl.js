let prevBalance = 0;
$('#store_id_input').on('change', function () {
    let selectedValue = $(this).val();
    const tableBody = $('#itemTable tbody');
    $('#so_book_code_input_qt').val('');
    $('#so_document_no_input_qt').val('');
    $('#document_date_filter').val('');
    $('#customer_code_input_qt').val('');
    tableBody.html('<tr><td colspan="15" class="text-center">Loading...</td></tr>');

    $.ajax({
        url: "/pick-list/so/get/items",
        type: 'GET',
        data: { store_id: selectedValue },
        success: function (response) {
            populateOrderTable(response.data);
        },
        error: function (xhr, status, error) {
            console.error('Error fetching orders:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Failed to fetch orders. Please try again.',
                icon: 'error',
            });
            tableBody.html('<tr><td colspan="12" class="text-center">Failed to load data.</td></tr>');
        }
    });
});

if(order && order.document_status=="draft" && order.store_id)
{
    $("#store_id_input").trigger('change');
}

function populateOrderTable(orders) {
    const tableBody = $('#itemTable tbody');
    tableBody.empty(); // Clear existing rows
    if (orders.length > 0) {
        const prevBalanceMap = new Map();
        orders.forEach((norder, index) => {
            const isCheckboxEnabled = norder.avl_stock > 0;
            const row = `
                <tr>
                    <td>
                        <div class="form-check form-check-primary custom-checkbox">
                            <input type="checkbox" name="selected_deliveries[]" class="form-check-input" id="order_checkbox_${index}" value="${norder.id}" ${isCheckboxEnabled ? '' : 'disabled'}>
                            <label class="form-check-label" for="order_checkbox_${index}"></label>
                        </div>
                    </td>
                    <td class='no-wrap'>${norder.item.header.book_code || 'N/A'}</td>
                    <td class='no-wrap'>${norder.item.header.document_number || 'N/A'}</td>
                    <td class='no-wrap'>${norder.item.header.document_date || 'N/A'}</td>
                    <td class='no-wrap'>${norder.delivery_date || 'N/A'}</td>
                    <td class='no-wrap'>${norder.item.item_code || 'N/A'}</td>
                    <td class='no-wrap'>${norder.item.item_name || 'N/A'}</td>
                    <td class='no-wrap'>${norder.item.header.currency_code || 'N/A'}</td>
                    <td class='no-wrap'>${norder.attributes || 'N/A'}</td>
                    <td class='no-wrap'>${norder.item.uom.name || 'N/A'}</td>
                    <td class="text-end">${norder.item.order_qty || '0.00'}</td>
                    <td class="text-end balance-qty-cell">${norder.item.balance_qty || '0.00'}</td>
                    <td class="text-end">${norder.avl_stock || '0.00'}</td>
                    <td>${norder.item.rate || 'N/A'}</td>
                    <td class='no-wrap'>${norder.item.header.customer_code || 'N/A'}</td>
                </tr>
            `;

            const $row = $(row);
            const $checkbox = $row.find(`#order_checkbox_${index}`);
            const $balanceQtyCell = $row.find('.balance-qty-cell');

            function createValidatedInput(norder, savedQty = null) {
                const currentQty = Math.min(norder.item.balance_qty, norder.avl_stock) || '0.00';

                const $input = $(`<input type="number" name="picked_qty[]" class="form-control" value="${savedQty ?? currentQty}" max="${currentQty}" />`);

                $input.on('input', function () {
                    const value = parseFloat($(this).val());
                    if (value < 0 || isNaN(value)) {
                        Swal.fire({
                            title: 'Invalid Input',
                            text: 'Quantity must be greater than zero.',
                            icon: 'warning',
                        });
                        $(this).val(0);
                    } else if (value > currentQty) {
                        const qtyLabel = norder.item.balance_qty < norder.avl_stock ? 'Balance Qty' : 'Available Stock';
                        Swal.fire({
                            title: 'Invalid Input',
                            text: `Quantity cannot be greater than ${qtyLabel}.`,
                            icon: 'warning',
                        });
                        $(this).val(currentQty);
                    }
                });

                return $input;
            }

            $checkbox.on('change', function () {
                if (this.checked) {
                    const savedQty = prevBalanceMap.get(norder.id) ?? (order ? order.picked_qty : null);
                    const $input = createValidatedInput(norder, savedQty);
                    $balanceQtyCell.html($input);
                } else {
                    const currentInput = $balanceQtyCell.find('input');
                    if (currentInput.length) {
                        prevBalanceMap.set(norder.id, currentInput.val());
                    }
                    $balanceQtyCell.text(norder.item.balance_qty || '0.00');
                }
            });

            if (order) {
                const matchedItem = order.items.find(item => item.order_item_delivery_id === norder.id);
                if (matchedItem) {
                    $checkbox.prop('checked', true);
                    $checkbox.trigger('change');
                    const $input = createValidatedInput(norder, matchedItem.picked_qty);
                    $balanceQtyCell.html($input);
                }
            }

            tableBody.append($row);
        });
        // Re-render Feather icons
        if (feather) {
            feather.replace();
        }
    } else {
        const noDataRow = `
            <tr>
                <td colspan="15" class="text-center">No orders found for the selected location.</td>
            </tr>
        `;
        tableBody.append(noDataRow);
    }
}

function viewOrderDetails(orderId) {
    Swal.fire({
        title: 'Order Details',
        text: `Details for Order ID: ${orderId}`,
        icon: 'info',
    });
}
let debounceTimer;
$('#delivery_date_filter, #so_document_no_input_qt, #document_date_filter, #customer_code_input_qt').on('input change', function () {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        const storeId = $('#store_id_input').val();
        const soBookCode = $('#delivery_date_filter').val();
        const soDocumentNo = $('#so_document_no_input_qt').val();
        const documentDate = $('#document_date_filter').val();
        const customerCode = $('#customer_code_input_qt').val();

        const tableBody = $('#itemTable tbody');
        tableBody.html('<tr><td colspan="15" class="text-center">Loading...</td></tr>');

        $.ajax({
            url: "/pick-list/so/get/items",
            type: 'GET',
            data: {
                store_id: storeId,
                delivery_date: soBookCode,
                so_document_no: soDocumentNo,
                document_date: documentDate,
                customer_code: customerCode
            },
            success: function (response) {
                populateOrderTable(response.data);
            },
            error: function (xhr, status, error) {
                console.error('Error fetching filtered orders:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Failed to fetch filtered orders. Please try again.',
                    icon: 'error',
                });
                tableBody.html('<tr><td colspan="12" class="text-center">Failed to load data.</td></tr>');
            }
        });
    }, 800);
});
$(".clearPiFilter").on('click',function(){
    
$('#delivery_date_filter, #so_document_no_input_qt, #document_date_filter, #customer_code_input_qt').val('');
$('#delivery_date_filter, #so_document_no_input_qt, #document_date_filter, #customer_code_input_qt').trigger('change');
});


