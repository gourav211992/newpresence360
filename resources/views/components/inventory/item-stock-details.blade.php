<style>
    .skeleton {
        background: linear-gradient(90deg, #eee, #ddd, #eee);
        background-size: 200% 100%;
        animation: skeleton-loading 1.2s infinite linear;
        border-radius: 4px;
    }

    @keyframes skeleton-loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    .skeleton-td {
        height: 14px;
        width: 40px; /* adjust based on expected number length */
        margin: auto;
    }
</style>
<div class="modal fade" id="inventoryItemStockDetails" tabindex="-1" aria-labelledby="shareProjectTitle" aria-hidden="true">
		<div class="modal-dialog  modal-dialog-centered" style = "min-width:85%;">
			<div class="modal-content">
				<div class="modal-header p-0 bg-transparent">
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body px-sm-2 mx-50 pb-2">
                    <div class = "row">
                        <div class = "col-md text-center">
                            <h2 class="text-center mt-2" id="shareProjectTitle">{{$title}}</h2>
                        </div>
                    </div>
                    <div class = "row">
                        <div class = "col-md">
                            <div class = "row">
                                <div class = "col mb-1">
                                    <label class = "form-label">Organization</label>
                                    <input type="text" id="stock_org_name_input_main" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input cannot_disable" autocomplete="off">
                                    <input type = "hidden" id = "stock_org_name_input_main_id" ></input>
                                </div>
                                <div class = "col mb-1">
                                <label class = "form-label">Location</label>
                                    <input type="text" id="stock_location_name_input_main" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input cannot_disable" autocomplete="off">
                                    <input type = "hidden" id = "stock_location_name_input_main_id" ></input>
                                </div>
                                <div class = "col mb-1">
                                    <label class = "form-label">Store</label>
                                    <input type="text" id="stock_sub_store_name_input_main" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input cannot_disable" autocomplete="off">
                                    <input type = "hidden" id = "stock_sub_store_name_input_main_id" ></input>
                                </div>
                                <div class = "col mb-1">
                                    <label class = "form-label">Item</label>
                                    <input type="text" id="stock_item_name_input_main" placeholder="Select" class="form-control mw-100 ledgerselecct ui-autocomplete-input cannot_disable" autocomplete="off">
                                    <input type = "hidden" id = "stock_item_name_input_main_id" ></input>
                                </div>
                            </div>
                        
                        </div>
                    </div>

                    <div class="table-responsive-md" style = "max-height: 400px !important; overflow-y: scroll">
                        <table
                        class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad">
                            <thead style = "position:sticky; top:0;">
                                <tr>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Attributes</th>
                                    <th>UOM</th>
                                    <th>Organization</th>
                                    <th>Location</th>
                                    <th>Store</th>
                                    <th class = "numeric-alignment">Confirmed Stock</th>
                                    <th class = "numeric-alignment">Unconfirmed Stock</th>
                                </tr>
                            </thead>
                            <tbody id="item-stock-details">
                                
                            </tbody>
                        </table>
                    </div>  
				</div>
			</div>
		</div>
	</div>

    <script>

    function viewDetailedStocks(itemId, itemAttributes)
    {
        const modalId = "inventoryItemStockDetails";
        const modalTableId = "item-stock-details";
        const modal = new bootstrap.Modal(document.getElementById(modalId));
        modal.show();
        let modalBodyElement = document.getElementById(modalTableId);
        modalBodyElement.innerHTML = `
        <tr>
        <td colspan = "9" class="text-center p-3">
            <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2">Loading...</p>
        </td>
        </tr>
        `;
        modalBodyElement.setAttribute('item-id', itemId);
        modalBodyElement.setAttribute('item-attributes', JSON.stringify(itemAttributes));
        // const itemId = document.getElementById('items_dropdown_' + itemIndex + '_value').value;
        // const locationId = document.getElementById('store_id_input').value;
        // const uomId = document.getElementById('uom_dropdown_' + itemIndex).value;

        let attributesArray = [];
        // let itemAttributes = JSON.parse(document.getElementById(`items_dropdown_${itemIndex}`).getAttribute('attribute-array'));
        itemAttributes.forEach(attr => {
            attr.values_data.forEach(attrVal => {
                if (attrVal.selected) {
                    attributesArray.push({
                        attribute_group_id : attr.attribute_group_id,
                        attribute_name : attr.group_name,
                        attribute_value_id : attrVal.id,
                        attribute_value : attrVal.value
                    });
                }
            });
        });
        $.ajax({
            url: "{{ route('item.stock.details') }}",
            type: 'POST',
            data : {
                item_id : itemId,
                item_attributes : attributesArray
            },
            beforeSend: function () {
                //Loader
                // document.getElementById('erp-overlay-loader').style.display = "flex";
            },
            success: function (response) {
                if (response) {
                    document.getElementById(modalTableId).innerHTML = response;

                    let itemsElement = document.getElementsByClassName('stock_items');
                    let selectedItemId = [];
                    for (let index = 0; index < itemsElement.length; index++) {
                        selectedItemId.push(itemsElement[index].value);
                    }
                    modalBodyElement.setAttribute('all-item-ids', JSON.stringify(selectedItemId));

                    $("#stock_org_name_input_main").val($("#stock_org_name_input_0").val());
                    $("#stock_org_name_input_main_id").val($("#stock_org_name_input_id_0").val());

                    $("#stock_location_name_input_main").val($("#stock_location_name_input_0").val());
                    $("#stock_location_name_input_main_id").val($("#stock_location_name_input_id_0").val());

                    $("#stock_sub_store_name_input_main").val($("#stock_sub_store_name_input_0").val());
                    $("#stock_sub_store_name_input_main_id").val($("#stock_sub_store_name_input_id_0").val());

                    //Auto Load the data
                    initializeAutoCompleteStock("stock_org_name_input", "stock_orgs", "name"); 
                    initializeAutoCompleteStock("stock_location_name_input", "stock_locations", "store_name"); 
                    initializeAutoCompleteStock("stock_sub_store_name_input", "stock_sub_locations", "name");
                    //Auto Load the Main Data
                    initializeAutoCompleteStockMain("stock_org_name_input_main", "stock_orgs", "name"); 
                    initializeAutoCompleteStockMain("stock_location_name_input_main", "stock_locations", "store_name"); 
                    initializeAutoCompleteStockMain("stock_sub_store_name_input_main", "stock_sub_locations", "name");
                    initializeAutoCompleteStockMain("stock_item_name_input_main", "stock_items", "item_name");

                } else {
                    document.getElementById(modalTableId).innerHTML = "";
                    Swal.fire({
                        title: 'Error!',
                        text: response.message ? response.message : 'Some internal error occured, Please try again after some time.',
                        icon: 'error',
                    });
                }
            },
            error: function (xhr) {
                let errorResponse = xhr.responseJSON;
                document.getElementById(modalTableId).innerHTML = "";
                Swal.fire({
                    title: 'Error!',
                    text: errorResponse?.message ? errorResponse?.message : 'Some internal error occured, Please try again after some time.',
                    icon: 'error',
                });
            },
            complete: function () {
                //LOG
            }
        });
    }

    function initializeAutoCompleteStock(selector, type, labelKeyName)
    {
        $("."+selector).autocomplete({
            source: function(request, response) {
                let indexVal = this.element.attr('index');
                $.ajax({
                    url: '/search',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        q: request.term,
                        type: type,
                        organization_id : $("#stock_org_name_input_id_" + indexVal).val(),
                        location_id : $("#stock_location_name_input_id_" + indexVal).val(),
                        sub_store_id : $("#stock_sub_store_name_input_id_" + indexVal).val(),
                    },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                id: item.id,
                                label: item[labelKeyName],
                            };
                        }));
                    },
                    error: function(xhr) {
                        console.error('Error fetching customer data:', xhr.responseText);
                    }
                });
            },
            appendTo: "#inventoryItemStockDetails",
            minLength: 0,
            select: function(event, ui) {
                var currentInput = $(this);
                let selectorSibling = $("#" + selector + "_id_" + currentInput.attr('index'));
                selectorSibling.val(ui.item.id);
                currentInput.val(ui.item.label);
                //Update other dropdowns
                let selectorSiblingLoc = $("#" + "stock_location_name_input" + "_id_" + currentInput.attr('index'));
                let selectorSiblingLocName = $("#" + "stock_location_name_input_" + currentInput.attr('index'));
                let selectorSiblingStore = $("#" + "stock_sub_store_name_input" + "_id_" + currentInput.attr('index'));
                let selectorSiblingStoreName = $("#" + "stock_sub_store_name_input_" + currentInput.attr('index'));
                if (selector == 'stock_org_name_input') {
                    selectorSiblingLoc.val("");
                    selectorSiblingLocName.val("");
                    selectorSiblingStore.val("");
                    selectorSiblingStoreName.val("");
                } else if (selector == 'stock_location_name_input') {
                    selectorSiblingStore.val("");
                    selectorSiblingStoreName.val("");
                } else {
                    //Nothing
                }
                updateStocks(currentInput.attr('index'));
                return false;
            },
            change: function(event, ui) {
                if (!ui.item) {
                    var currentInput = $(this);
                    currentInput.val("");
                    let selectorSibling = $("#" + selector + "_id_" + currentInput.attr('index'));
                    selectorSibling.val("");
                    //Update other fields
                    let selectorSiblingLoc = $("#" + "stock_location_name_input" + "_id_" + currentInput.attr('index'));
                    let selectorSiblingLocName = $("#" + "stock_location_name_input_" + currentInput.attr('index'));
                    let selectorSiblingStore = $("#" + "stock_sub_store_name_input" + "_id_" + currentInput.attr('index'));
                    let selectorSiblingStoreName = $("#" + "stock_sub_store_name_input_" + currentInput.attr('index'));
                    if (selector == 'stock_org_name_input') {
                        selectorSiblingLoc.val("");
                        selectorSiblingLocName.val("");
                        selectorSiblingStore.val("");
                        selectorSiblingStoreName.val("");
                    } else if (selector == 'stock_location_name_input') {
                        selectorSiblingStore.val("");
                        selectorSiblingStoreName.val("");
                    } else {
                        //Nothing
                    }
                    updateStocks(currentInput.attr('index'));
                }
            }
        }).focus(function() {
            if (this.value === "") {
                $(this).autocomplete("search", "");
            }
        });
    }

    function initializeAutoCompleteStockMain(selector, type, labelKeyName)
    {
        $("#"+selector).autocomplete({
            source: function(request, response) {
                let indexVal = this.element.attr('index');
                $.ajax({
                    url: '/search',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        q: request.term,
                        type: type,
                        organization_id : $("#stock_org_name_input_main_id").val(),
                        location_id : $("#stock_location_name_input_main_id").val(),
                        sub_store_id : $("#stock_sub_store_name_input_main_id").val(),
                        item_ids : JSON.parse($("#item-stock-details").attr('all-item-ids'))
                    },
                    success: function(data) {
                        response($.map(data, function(item) {
                            return {
                                id: item.id,
                                label: item[labelKeyName],
                            };
                        }));
                    },
                    error: function(xhr) {
                        console.error('Error fetching customer data:', xhr.responseText);
                    }
                });
            },
            appendTo: "#inventoryItemStockDetails",
            minLength: 0,
            select: function(event, ui) {
                var currentInput = $(this);
                let selectorSibling = $("#" + selector + "_id");
                selectorSibling.val(ui.item.id);
                currentInput.val(ui.item.label);
                //Update other dropdowns
                if (selector == "stock_org_name_input_main") {
                    $('.stock_org_name_id').val(ui.item.id);
                    $('.stock_org_name_input').val(ui.item.label);

                    $('.stock_location_name_id').val("");
                    $('.stock_location_name_input').val("");

                    $('.stock_sub_store_name_id').val("");
                    $('.stock_sub_store_name_input').val("");

                    $("#stock_location_name_input_main").val("");
                    $("#stock_location_name_input_main_id").val("");
                    $("#stock_sub_store_name_input_main").val("");
                    $("#stock_sub_store_name_input_main_id").val("");
                } else if (selector == "stock_location_name_input_main") {
                    $('.stock_location_name_id').val(ui.item.id);
                    $('.stock_location_name_input').val(ui.item.label);

                    $('.stock_sub_store_name_id').val("");
                    $('.stock_sub_store_name_input').val("");

                    $("#stock_sub_store_name_input_main").val("");
                    $("#stock_sub_store_name_input_main_id").val("");
                } else if (selector == "stock_sub_store_name_input_main") {
                    $('.stock_sub_store_name_id').val(ui.item.id);
                    $('.stock_sub_store_name_input').val(ui.item.label);
                }
                // let totalOrgsElement = document.getElementsByClassName('stock_org_name_input');
                // for (let index = 0; index < totalOrgsElement.length; index++) {
                //     updateStocks(totalOrgsElement[index].getAttribute('index'));
                // }
                reRenderStocks($("#stock_org_name_input_main_id").val(), $("#stock_location_name_input_main_id").val(), $("#stock_sub_store_name_input_main_id").val())
                return false;
            },
            change: function(event, ui) {
                if (!ui.item) {
                    var currentInput = $(this);
                    currentInput.val("");
                    let selectorSibling = $("#" + selector + "_id");
                    selectorSibling.val("");
                    //Update other fields
                    if (selector == "stock_org_name_input_main") {
                        $('.stock_org_name_id').val("");
                        $('.stock_org_name_input').val("");

                        $('.stock_location_name_id').val("");
                        $('.stock_location_name_input').val("");

                        $('.stock_sub_store_name_id').val("");
                        $('.stock_sub_store_name_input').val("");
                        
                        $("#stock_location_name_input_main").val("");
                        $("#stock_location_name_input_main_id").val("");
                        $("#stock_sub_store_name_input_main").val("");
                        $("#stock_sub_store_name_input_main_id").val("");
                    } else if (selector == "stock_location_name_input_main") {
                        $('.stock_location_name_id').val("");
                        $('.stock_location_name_input').val("");

                        $('.stock_sub_store_name_id').val("");
                        $('.stock_sub_store_name_input').val("");

                        $("#stock_sub_store_name_input_main").val("");
                        $("#stock_sub_store_name_input_main_id").val("");
                    } else if (selector == "stock_sub_store_name_input_main") {
                        $('.stock_sub_store_name_id').val("");
                        $('.stock_sub_store_name_input').val("");
                    }
                    reRenderStocks($("#stock_org_name_input_main_id").val(), $("#stock_location_name_input_main_id").val(), $("#stock_sub_store_name_input_main_id").val())

                }
            }
        }).focus(function() {
            if (this.value === "") {
                $(this).autocomplete("search", "");
            }
        });
    }

    function updateStocks(itemIndex)
    {
        const currentConfirmedStock = document.getElementById('stock_confirmed_qty_' + itemIndex);
        const currentUnconfirmedStock = document.getElementById('stock_unconfirmed_qty_' + itemIndex);
        const selectedAttributes = JSON.parse(document.getElementById('stock_attributes_' + itemIndex).value);
        const itemId = document.getElementById('stock_item_id_' + itemIndex).value;
        const locationId = document.getElementById('stock_location_name_input_id_' + itemIndex).value;
        const subStoreId = document.getElementById('stock_sub_store_name_input_id_' + itemIndex).value;
        const orgId = document.getElementById('stock_org_name_input_id_' + itemIndex).value;
        document.getElementById('stock_confirmed_qty_' + itemIndex).innerHTML = `<div class="skeleton skeleton-td"></div>`;
        document.getElementById('stock_unconfirmed_qty_' + itemIndex).innerHTML = `<div class="skeleton skeleton-td"></div>`;
        $.ajax({
            url: "{{route('current.item.stock.details')}}",
            type: 'POST',
            data : {
                item_id : itemId,
                location_id : locationId,
                sub_store_id : subStoreId,
                item_attributes : selectedAttributes,
                organization_id : orgId
            },
            beforeSend: function () {
                //Loader
                // document.getElementById('erp-overlay-loader').style.display = "flex";
            },
            success: function (response) {
                if (response.data) {
                    currentConfirmedStock.textContent = response.data.confirmed;
                    if (response.data.confirmed <= 0) {
                        currentConfirmedStock.style.color = "red";
                    } else {
                        currentConfirmedStock.style.color = "";
                    }
                    currentUnconfirmedStock.textContent = response.data.unconfirmed;
                    if (response.data.unconfirmed <= 0) {
                        currentUnconfirmedStock.style.color = "red";
                    } else {
                        currentUnconfirmedStock.style.color = "";
                    }
                } else {
                    currentConfirmedStock.textContent = "0.00";
                    currentConfirmedStock.style.color = "red";
                    currentUnconfirmedStock.textContent = "0.00";
                    currentUnconfirmedStock.style.color = "red";
                }
            },
            error: function (xhr) {
                let errorResponse = xhr.responseJSON;
                currentConfirmedStock.textContent = "0.00";
                currentConfirmedStock.style.color = "red";
                currentUnconfirmedStock.textContent = "0.00";
                currentUnconfirmedStock.style.color = "red";
                console.log(errorResponse?.message ? errorResponse?.message : 'Some internal error occured, Please try again after some time.');
            },
            complete: function () {
                //LOG
            }
        });
    }

    function reRenderStocks(organizationId, locationId, subStoreId)
    {
        const modalId = "inventoryItemStockDetails";
        const modalTableId = "item-stock-details";
        let modalBodyElement = document.getElementById(modalTableId);
        modalBodyElement.innerHTML = `
        <tr>
        <td colspan = "9" class="text-center p-3">
            <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2">Loading...</p>
        </td>
        </tr>
        `;
        let itemId = modalBodyElement.getAttribute('item-id');
        let itemAttributes = JSON.parse(modalBodyElement.getAttribute('item-attributes') ? modalBodyElement.getAttribute('item-attributes') : '[]');
        let attributesArray = [];
        itemAttributes.forEach(attr => {
            attr.values_data.forEach(attrVal => {
                if (attrVal.selected) {
                    attributesArray.push({
                        attribute_group_id : attr.attribute_group_id,
                        attribute_name : attr.group_name,
                        attribute_value_id : attrVal.id,
                        attribute_value : attrVal.value
                    });
                }
            });
        });
        $.ajax({
            url: "{{ route('item.stock.details') }}",
            type: 'POST',
            data : {
                item_id : itemId,
                org_id : organizationId,
                loc_id : locationId,
                sub_store_id : subStoreId,
                item_attributes : attributesArray,
                filter_item_ids : $("#stock_item_name_input_main_id").val() ? [$("#stock_item_name_input_main_id").val()] : []
            },
            beforeSend: function () {
                //Loader
                // document.getElementById('erp-overlay-loader').style.display = "flex";
            },
            success: function (response) {
                if (response) {
                    document.getElementById(modalTableId).innerHTML = response;
                } else {
                    document.getElementById(modalTableId).innerHTML = "";
                    Swal.fire({
                        title: 'Error!',
                        text: response.message ? response.message : 'Some internal error occured, Please try again after some time.',
                        icon: 'error',
                    });
                }
            },
            error: function (xhr) {
                let errorResponse = xhr.responseJSON;
                document.getElementById(modalTableId).innerHTML = "";
                Swal.fire({
                    title: 'Error!',
                    text: errorResponse?.message ? errorResponse?.message : 'Some internal error occured, Please try again after some time.',
                    icon: 'error',
                });
            },
            complete: function () {
                //LOG
            }
        });
    }
    </script>