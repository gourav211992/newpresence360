var defautAttrBtn = `<button id="attribute_button_1" type="button" class="btn p-25 btn-sm btn-outline-secondary" style="font-size: 10px">Attributes</button>`;
// Render Attribute UI
function setAttributesUIHelper(paramIndex = null, selectorPrifix = '')
{
    let currentItemIndex = paramIndex;
    const container = document.querySelector(selectorPrifix) || document.querySelector("#itemTable tbody");
    let itemIdDoc = container.querySelector('#itemAttribute_' + currentItemIndex);
    if (!itemIdDoc) {
        return;
    }
    let attributesArray = itemIdDoc.getAttribute('attribute-array');
    if (!attributesArray) {
        return;
    }
    attributesArray = JSON.parse(attributesArray);
    if (attributesArray.length == 0) {
        return;
    }
    let attributeUI = `<div style = "white-space:nowrap; cursor:pointer;">`;
    let maxCharLimit = 15;
    let attrTotalChar = 0;
    let total_selected = 0;
    let total_atts = 0;
    let addMore = true;
    attributesArray.forEach(attrArr => {
        if (!addMore) {
            return;
        }
        let short = false;
        total_atts += 1;
        if(attrArr?.short_name)
        {
            short = true;
        }
        let currentStringLength = short ? Number(attrArr.short_name.length) : Number(attrArr.group_name.length);
        let currentSelectedValue = '';
        attrArr.values_data.forEach((attrVal) => {
            if (attrVal.selected === true) {
                total_selected += 1;
                currentStringLength += Number(attrVal.value.length);
                currentSelectedValue = attrVal.value;
            }
        });
        if ((attrTotalChar + Number(currentStringLength)) <= 15) {
            attributeUI += `
            <span class="badge rounded-pill badge-light-primary"><strong>${short ? attrArr.short_name : attrArr.group_name}</strong>: ${currentSelectedValue ? currentSelectedValue :''}</span>
            `;
        } else {
            let remainingLength =  15 - attrTotalChar;
            if (remainingLength >= 3) {
                attributeUI += `<span class="badge rounded-pill badge-light-primary"><strong>${attrArr.group_name.substring(0, remainingLength - 1)}..</strong></span>`
            }
            else {
                addMore = false;
                attributeUI += `<i class="ml-2 fa-solid fa-ellipsis-vertical"></i>`;
            }
        }
        attrTotalChar += Number(currentStringLength);
    });
    let attributeSection = container.querySelector(`[id="itemAttribute_${currentItemIndex}"]`);
    if (attributeSection) {
        attributeSection.innerHTML = attributeUI + '</div>';
    }
    if(total_selected == 0){
        attributeSection.innerHTML = `
            <button id = "attribute_button_${currentItemIndex}"
                ${attributesArray.length > 0 ? '' : 'disabled'}
                type = "button"
                class="btn p-25 btn-sm btn-outline-secondary"
                style="font-size: 10px">Attributes</button>
            <input type = "hidden" name = "attribute_value_${currentItemIndex}" />
        `;
    }
}

function setSelectedAttribute(rowCount) {
    let selectedAttr = [];
    let currentTr = $(`tr[id="row_${rowCount}"]`);
    currentTr.find("[name*='attr_name']").each(function() {
        const val = $(this).val();
        if (val) {
            selectedAttr.push(String(val));
        }
    });
    let attributesArray = currentTr.find(`td[id="itemAttribute_${rowCount}"]`).attr('attribute-array');
    attributesArray = attributesArray ? JSON.parse(attributesArray) : [];
    if (attributesArray.length) {
        attributesArray.forEach(group => {
            group.values_data.forEach(attr => {
                attr.selected = selectedAttr.includes(String(attr.id));
            });
        });
        currentTr.find(`td[id="itemAttribute_${rowCount}"]`)
            .attr('attribute-array', JSON.stringify(attributesArray));
        setAttributesUIHelper(rowCount, '#itemTable tbody');
    }
}
