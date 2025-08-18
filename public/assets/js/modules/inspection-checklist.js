// Inspection Checklist Btn

$(document).on("click", ".inspectionChecklistBtn", function () {
    const rawMasterData = $(this).attr("data-checklist");
    const rowCount = $(this).data("row-count");

    const existingInput = document.querySelector(
        `input[id="inspection_data_${rowCount}"]`
    );
    const rawExistingData = existingInput ? existingInput.value : "[]";

    // Simple escapeHTML if not globally provided
    if (typeof window.escapeHTML !== "function") {
        window.escapeHTML = function (s) {
            s = s == null ? "" : String(s);
            return s.replace(
                /[&<>"']/g,
                (c) =>
                    ({
                        "&": "&amp;",
                        "<": "&lt;",
                        ">": "&gt;",
                        '"': "&quot;",
                        "'": "&#39;",
                    }[c])
            );
        };
    }

    $(".checklist-modal-body").data("row-count", rowCount);

    let masterChecklist = {},
        existingChecklist = [];
    try {
        masterChecklist = JSON.parse(rawMasterData);
    } catch (e) {
        console.error("Invalid master checklist:", e);
        $(".checklist-modal-body").html(
            '<div class="text-danger">Unable to load checklist data.</div>'
        );
        return;
    }

    // Parse saved data; prefer main field, fall back to test field, then empty
    try {
        existingChecklist =
            JSON.parse(rawExistingData || rawTestExisting || "[]") || [];
        if (!Array.isArray(existingChecklist)) existingChecklist = [];
    } catch (e) {
        console.warn("Invalid saved checklist data:", e);
        existingChecklist = [];
    }

    // --- Build lookups with normalization ---
    const norm = (v) => (v == null ? "" : String(v).trim());
    const slug = (s) =>
        norm(s)
            .toLowerCase()
            .replace(/\s+/g, " ")
            .replace(/[^\w\s-]/g, "");

    const byChecklistDetail = Object.create(null); // key: `${cid}-${did}`
    const byDetail = Object.create(null); // key: `${did}`
    const byName = Object.create(null); // key: slug(parameter_name)

    existingChecklist.forEach((raw) => {
        if (!raw || typeof raw !== "object") return;

        const cid = norm(
            raw.checkList_id ?? raw.checklist_id ?? raw.checkListId
        );
        const did = norm(
            raw.detail_id ?? raw.checklist_detail_id ?? raw.parameter_item_id
        );
        const pname = norm(raw.parameter_name ?? raw.name);
        const pvalue = norm(raw.parameter_value ?? raw.value);
        const result = norm(raw.result).toLowerCase();
        const inspId = norm(raw.insp_checklist_id ?? raw.parameter_checkl_id);

        const item = {
            checklistId: cid,
            detailId: did,
            parameterName: pname,
            parameterValue: pvalue,
            result,
            inspId,
        };

        if (cid && did) byChecklistDetail[`${cid}-${did}`] = item; // last wins (fine)
        if (did && !byDetail[did]) byDetail[did] = item; // first wins (stable)
        if (pname) byName[slug(pname)] = item; // first wins
    });

    const checklists = masterChecklist.checkLists || [];
    if (!checklists.length) {
        $(".checklist-modal-body").html(
            '<div class="text-muted">No checklist available.</div>'
        );
        return;
    }

    let html = "";

    checklists.forEach((checklist) => {
        const checklistName = escapeHTML(checklist.name);
        const checklistId = norm(checklist.id);

        html += `
            <div class="mb-1 text-center fw-bold fs-5">${checklistName}</div>
            <!-- Note: duplicate IDs are bad; use data-attrs instead if multiple checklists exist -->
            <input type="hidden" id="checklist_id" value="${checklistId}">
            <input type="hidden" id="checklist_name" value="${checklistName}">
            <div class="table-responsive-md customernewsection-form">
                <table class="table table-bordered po-order-detail myrequesttablecbox nowrap w-100 text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="60%">Parameters</th>
                            <th>Values</th>
                            <th>Result</th>
                        </tr>
                    </thead>
                <tbody>`;

        (checklist.details || []).forEach((detail, index) => {
            const paramLabelRaw = detail.name || "";
            const paramLabel = escapeHTML(paramLabelRaw);
            const type = detail.data_type || "text";
            const requiredAttr = detail.mandatory ? "required" : "";
            const detailId = norm(detail.id);

            const namePrefix = `components[${rowCount}][checklist][${index}]`;
            const paramIdField = `${namePrefix}[parameter_item_id]`;
            const paramChecklistIdField = `${namePrefix}[parameter_checkl_id]`;
            const paramNameField = `${namePrefix}[parameter_name]`;
            const paramValueField = `${namePrefix}[parameter_value]`;
            const resultField = `${namePrefix}[parameter_result]`;

            const rowId = `check_${rowCount}_${detailId}`;

            // --- Prefill selection with graceful fallbacks ---
            // 1) exact match by current checklistId + detailId
            // 2) fallback by detailId only (handles wrong/missing checkList_id)
            // 3) fallback by parameter_name (slug)
            const saved =
                byChecklistDetail[`${checklistId}-${detailId}`] ||
                byDetail[detailId] ||
                byName[slug(paramLabelRaw)] ||
                {};

            const savedValue = norm(saved.parameterValue);
            const savedChecklistId = norm(saved.inspId);
            const savedResult = norm(saved.result);

            html += `<tr>
                <td class="text-start ps-3">
                    ${paramLabel} ${
                detail.mandatory ? '<span class="text-danger">*</span>' : ""
            }
                    <input type="hidden" name="${paramNameField}" value="${paramLabel}" />
                    <input type="hidden" name="${paramIdField}" value="${detailId}" />
                    <input type="hidden" name="${paramChecklistIdField}" value="${savedChecklistId}" />
                </td>`;

            html += `<td>`;
            switch (type) {
                case "number":
                case "text":
                default:
                    html += `<input type="${
                        type === "number" ? "number" : "text"
                    }" name="${paramValueField}" value="${escapeHTML(
                        savedValue
                    )}" class="form-control mw-100" ${requiredAttr} />
                        <div class="invalid-feedback">Required</div>`;
                    break;
                case "date":
                    html += `<input type="date" name="${paramValueField}" value="${escapeHTML(
                        savedValue
                    )}" class="form-control mw-100" ${requiredAttr} />
                        <div class="invalid-feedback">Required</div>`;
                    break;
                case "list":
                    html += `<select name="${paramValueField}" class="form-select mw-100" ${requiredAttr}>
                        <option value="">Select</option>`;
                    (detail.values || []).forEach((opt) => {
                        const ov = norm(opt.value);
                        const selected = ov === savedValue ? "selected" : "";
                        html += `<option value="${escapeHTML(
                            ov
                        )}" ${selected}>${escapeHTML(ov)}</option>`;
                    });
                    html += `</select>
                        <div class="invalid-feedback">Required</div>`;
                    break;
                case "boolean":
                    html += `<select name="${paramValueField}" class="form-select mw-100" ${requiredAttr}>
                        <option value="">Select</option>
                        <option value="yes" ${
                            savedValue === "yes" ? "selected" : ""
                        }>Yes</option>
                        <option value="no" ${
                            savedValue === "no" ? "selected" : ""
                        }>No</option>
                    </select>
                    <div class="invalid-feedback">Required</div>`;
                    break;
            }
            html += `</td>`;

            html += `
                <td>
                    <div class="d-flex justify-content-center gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                name="${resultField}" id="${rowId}_pass" value="pass"
                                ${savedResult === "pass" ? "checked" : ""}
                                ${detail.mandatory ? 'data-required="1"' : ""}>
                            <label class="form-check-label text-success" for="${rowId}_pass">Pass</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                name="${resultField}" id="${rowId}_fail" value="fail"
                                ${savedResult === "fail" ? "checked" : ""}
                                ${detail.mandatory ? 'data-required="1"' : ""}>
                            <label class="form-check-label text-danger" for="${rowId}_fail">Fail</label>
                        </div>
                    </div>
                </td>
            </tr>`;
        });

        html += `</tbody></table></div><hr/>`;
    });

    $(".checklist-modal-body").html(html);
});

$(document).on("change", ".toggle-pass-check", function () {
    const $label = $(this).closest("td").find(".pass-label");
    if ($(this).is(":checked")) {
        $label.removeClass("d-none");
    } else {
        $label.addClass("d-none");
    }
});

// Optional: escape user input if needed
function escapeHTML(str) {
    return String(str || "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

// Submit Checklist Button
$(document).on("click", ".submitChecklistBtn", function (e) {
    e.preventDefault();

    const $modal = $("#inspectionChecklistModal");
    const rowCount =
        $modal.find(".checklist-modal-body").data("row-count") ||
        $(".inspectionChecklistBtn").data("row-count");

    const $allRows = $modal.find(`table tbody tr`);
    let isValid = true;
    let data = [];

    const checkListId = $("#checklist_id").val();
    const checkListName = $("#checklist_name").val();

    // Reset previous validation states
    $modal.find(".is-invalid").removeClass("is-invalid");
    $modal.find(".text-danger.result-feedback").remove(); // remove old error messages

    $allRows.each(function () {
        const $row = $(this);

        const paramInspChckIdInput = $row.find(
            `[name*="[parameter_checkl_id]"]`
        );
        const paramItemIdInput = $row.find(`[name*="[parameter_item_id]"]`);
        const paramNameInput = $row.find(`[name*="[parameter_name]"]`);
        const paramValueInput = $row.find(`[name*="[parameter_value]"]`);
        const resultFieldName = $row
            .find(`[name*="[parameter_result]"]`)
            .attr("name");
        const resultValue = $row
            .find(`[name="${resultFieldName}"]:checked`)
            .val(); // ✅ radio selection

        const paramInspChckId = paramInspChckIdInput.val();
        const paramItemId = paramItemIdInput.val();
        const paramName = paramNameInput.val();
        const paramValue = paramValueInput.val();
        const isParamRequired = paramValueInput.prop("required");
        const isResultRequired =
            $row.find(`[name="${resultFieldName}"]`).data("required") === 1;

        // Validate parameter value
        if (isParamRequired && (!paramValue || paramValue.trim() === "")) {
            paramValueInput.addClass("is-invalid");
            isValid = false;
        }

        // Validate Pass/Fail selection (radio)
        if (isResultRequired && !resultValue) {
            $row.find("td:last").append(
                '<div class="text-danger result-feedback small mt-1">Please select Pass or Fail</div>'
            );
            isValid = false;
        }

        // Push structured data for saving
        data.push({
            insp_checklist_id: paramInspChckId,
            checkList_id: checkListId,
            checkList_name: checkListName,
            detail_id: paramItemId,
            parameter_name: paramName,
            parameter_value: paramValue,
            result: resultValue || "", // fallback if not selected
        });
    });

    if (!isValid) {
        Swal.fire({
            icon: "error",
            title: "Required Fields Missing",
            text: "Please fill all required fields and select Pass/Fail before submitting.",
        });
        return;
    }

    // Save to hidden input
    let hiddenFieldName = `components[${rowCount}][inspectionData]`;

    if (!document.querySelector(`[name="${hiddenFieldName}"]`)) {
        hiddenFieldName = `inspection_data[${rowCount}]`;
    }
    // const $targetRow = $(`#item_row_${rowCount}`);
    const $targetRow = $(`#item_row_${rowCount}, #row_${rowCount}`);

    let $hidden = $targetRow.find(`input[name="${hiddenFieldName}"]`);

    if ($hidden.length === 0) {
        $targetRow.append(`<input type="hidden" name="${hiddenFieldName}" />`);
        $hidden = $targetRow.find(`input[name="${hiddenFieldName}"]`);
    }

    $hidden.val(JSON.stringify(data));

    // Close modal and mark button
    $modal.modal("hide");
    $targetRow.find(".inspectionChecklistBtn").addClass("text-success");
});
