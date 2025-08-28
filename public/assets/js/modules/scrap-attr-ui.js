const defaultAttrBtn = (index) => `
    <button id="attribute_button_${index}" type="button"
        class="btn p-25 btn-sm btn-outline-secondary"
        style="font-size: 10px">Attributes</button>
    <input type="hidden" name="attribute_value_${index}" />
`;

/**
 * Render Attributes UI
 * @param {number} rowIndex
 * @param {string} selectorTablePrefix
 */
function setAttributesUIHelper(
    rowIndex = null,
    selectorTablePrefix = "#scavengingItemsTable tbody"
) {
    const container = document.querySelector(selectorTablePrefix);

    const attrCell = container?.querySelector(`#itemAttribute_${rowIndex}`);

    if (!attrCell) return;

    let attributes = attrCell.getAttribute("attribute-array");

    if (!attributes) return;

    attributes = JSON.parse(attributes);

    if (!attributes.length) return;

    let attributeUI = `<div style="white-space:nowrap; cursor:pointer;">`;
    let maxCharLimit = 15,
        charUsed = 0,
        selectedCount = 0,
        stopAdding = false;

    for (const group of attributes) {
        if (stopAdding) break;

        const groupName = group.short_name || group.group_name;
        let selectedVal =
            group.values_data.find((v) => v.selected)?.value || "";

        if (selectedVal) selectedCount++;

        let groupText = `${groupName}: ${selectedVal}`;
        let length = groupText.length;

        if (charUsed + length <= maxCharLimit) {
            attributeUI += `<span class="badge rounded-pill badge-light-primary">
                                <strong>${groupName}</strong>: ${selectedVal}
                            </span>`;
            charUsed += length;
        } else {
            // truncate or add ellipsis
            let remain = maxCharLimit - charUsed;
            if (remain >= 3) {
                attributeUI += `<span class="badge rounded-pill badge-light-primary">
                                    <strong>${groupName.substring(
                                        0,
                                        remain - 1
                                    )}..</strong>
                                </span>`;
            } else {
                attributeUI += `<i class="ml-2 fa-solid fa-ellipsis-vertical"></i>`;
            }

            stopAdding = true;
        }
    }

    attrCell.innerHTML = selectedCount
        ? attributeUI + "</div>"
        : defaultAttrBtn(rowIndex);
}

/**
 * Sync selected attributes into attribute-array and re-render
 * @param {number} rowIndex
 */
function setSelectedAttribute(
    rowCount,
    selectorTablePrefix = "#scavengingItemsTable tbody"
) {
    let selectedAttr = [];
    let currentTr = $(`#scavengingItemsTr_${rowCount}`);

    currentTr.find("[name*='attr_name']").each(function () {
        const val = $(this).val();
        if (val) {
            selectedAttr.push(String(val));
        }
    });

    let attributesArray = currentTr
        .find(`td[id="itemAttribute_${rowCount}"]`)
        .attr("attribute-array");
    attributesArray = attributesArray ? JSON.parse(attributesArray) : [];
    if (attributesArray.length) {
        attributesArray.forEach((group) => {
            group.values_data.forEach((attr) => {
                attr.selected = selectedAttr.includes(String(attr.id));
            });
        });
        currentTr
            .find(`td[id="itemAttribute_${rowCount}"]`)
            .attr("attribute-array", JSON.stringify(attributesArray));
        setAttributesUIHelper(rowCount, selectorTablePrefix);
    }
}
