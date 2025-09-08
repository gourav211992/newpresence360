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
 */ function setSelectedAttribute(
    rowCount,
    selectorTablePrefix = "#scavengingItemsTable tbody"
) {
    const currentTr = $(`#scavengingItemsTr_${rowCount}`);
    let selectedAttr = [];
    console.log(currentTr.find("[name*='attr_name']"));

    currentTr.find("[name*='attr_name']").each(function () {
        const v = $(this).val();
        if (v != null && v !== "") selectedAttr.push(String(v).trim());
    });
    selectedAttr = [...new Set(selectedAttr)];

    console.log(selectedAttr);

    const $cell = currentTr.find(`td#itemAttribute_${rowCount}`);
    let attributesArray = $cell.attr("attribute-array");

    console.log(attributesArray);

    try {
        attributesArray = attributesArray ? JSON.parse(attributesArray) : [];
    } catch (e) {
        try {
            attributesArray = JSON.parse(
                attributesArray.replace(/&quot;/g, '"')
            );
        } catch (_) {
            attributesArray = [];
        }
    }

    if (!Array.isArray(attributesArray) || !attributesArray.length) return;

    attributesArray.forEach((group) => {
        const list = group.values_data || group.values || [];
        list.forEach((attr) => {
            const idStr = String(attr.id);
            attr.selected = selectedAttr.includes(idStr);
        });
    });

    const json = JSON.stringify(attributesArray);
    $cell.attr("attribute-array", json);
    $cell.data("attribute-array", attributesArray);

    setAttributesUIHelper(rowCount, selectorTablePrefix);
}
