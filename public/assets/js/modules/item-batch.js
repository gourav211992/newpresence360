/* =========================
   Item Batches Modal Logic
   ========================= */

let activeBatchRowIndex = null;
let expectedBatchQty = 0;
let canEditBatch = true; // computed per-open
let isExpiry = 0;
// ---- helpers ------------------------------------------------

// Helper: robustly read data-is-batch-number
function getIsBatchEnable($el) {
    // Try the correct name first (camelCase for jQuery .data())
    let raw = $el.data("isBatchNumber");

    // Fallback to dashed (rarely works with .data())
    if (raw === undefined) raw = $el.data("is-batch-number");

    // Fallback to the TYPO currently in your HTML
    if (raw === undefined) raw = $el.attr("data-is-batch-nnumber");

    // Final fallback to the correct dashed attr
    if (raw === undefined) raw = $el.attr("data-is-batch-number");

    // Normalize
    return /^(1|true|yes)$/i.test(String(raw));
}

// Get LotNumber For Locked
function getLotNumberForLocked() {
    const documentDate =
        $(".document_date").val() || new Date().toISOString().split("T")[0];
    const documentNumber = $(".document_number").val() || "";
    const bookCode = $(".book_code").val() || "";
    return `${documentDate}/${bookCode}/${documentNumber}`;
}

function updateBatchTotal() {
    let total = 0;
    $("#itemBatchTable tbody .batch-qty").each(function () {
        total += parseFloat($(this).val()) || 0;
    });
    // Optionally show total somewhere
    // $('#batchTotal').text(total);
    return total;
}

function reindexBatchRows() {
    const $tbody = $("#itemBatchTable tbody");
    $tbody.find("tr").each(function (i) {
        $(this).attr("data-index", i);
        $(this)
            .find(".batch-row-check")
            .attr("data-index", i)
            .prop("disabled", i === 0);

        $(this)
            .find("input[name]")
            .each(function () {
                const name = $(this).attr("name");
                // components[ROW][batches][OLD]
                const newName = name.replace(
                    /\[batches]\[\d+]/,
                    `[batches][${i}]`
                );
                $(this).attr("name", newName);
            });
    });
}

function generateBatchRow(index, opts = {}, allowEdit = true) {
    const {
        batch_number = "",
        manufacturing_year = "",
        expiry_date = "",
        quantity = "",
    } = opts;

    const isFirst = index === 0;
    const ro = allowEdit ? "" : "readonly";
    const dis = allowEdit ? "" : "disabled";

    return `
       <tr data-index="${index}">
         <td>
           <input type="checkbox" class="form-check-input batch-row-check"
                  data-index="${index}" ${isFirst ? "disabled" : ""} ${dis} />
         </td>
         <td>
           <input type="text" class="form-control mw-100 batch-number"
                  name="components[${activeBatchRowIndex}][batches][${index}][batch_number]"
                  value="${batch_number}" required ${ro} ${dis} />
         </td>
         <td>
           <input type="number" class="form-control mw-100 manufacturing-year"
                  name="components[${activeBatchRowIndex}][batches][${index}][manufacturing_year]"
                  min="1900" max="2099" value="${manufacturing_year}" ${ro} ${dis} />
         </td>
         <td>
           <input type="date" class="form-control mw-100 expiry-date"
                  name="components[${activeBatchRowIndex}][batches][${index}][expiry_date]"
                  value="${expiry_date}" ${ro} ${dis} />
         </td>
         <td>
           <input type="number" step="any" class="form-control mw-100 batch-qty"
                  name="components[${activeBatchRowIndex}][batches][${index}][quantity]"
                  value="${quantity}" ${ro} ${dis} />
         </td>
         <td>
           ${
               allowEdit
                   ? `
                <a href="#" class="text-primary add-batch-row-header" data-index="${index}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-plus-circle">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="12" y1="8" x2="12" y2="16"></line>
                        <line x1="8" y1="12" x2="16" y2="12"></line>
                    </svg>
                </a>
             ${
                 isFirst
                     ? ""
                     : `
                <a href="#" class="text-danger remove-batch-row" data-index="${index}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x-circle">
                        <circle cx="12" cy="12" r="10"></circle>
                        <line x1="15" y1="9" x2="9" y2="15"></line>
                        <line x1="9" y1="9" x2="15" y2="15"></line>
                    </svg>
                </a>`
             }
           `
                   : ""
           }
         </td>
       </tr>`;
}

function populateBatchTable(data = [], allowEdit) {
    const $tbody = $("#itemBatchTable tbody");
    $tbody.empty();

    const lotNumber = getLotNumberForLocked();

    // If no saved data, seed one row
    if (!Array.isArray(data) || data.length === 0) {
        data = [
            {
                batch_number: allowEdit ? "" : lotNumber,
                manufacturing_year: "",
                expiry_date: "",
                quantity: expectedBatchQty,
            },
        ];
    }

    data.forEach((row, i) => {
        $tbody.append(
            generateBatchRow(
                i,
                {
                    batch_number:
                        row.batch_number ?? (allowEdit ? "" : lotNumber),
                    manufacturing_year: row.manufacturing_year ?? "",
                    expiry_date: row.expiry_date ?? "",
                    quantity: row.quantity ?? "",
                },
                allowEdit
            )
        );
    });

    updateBatchTotal();
    applyDateGuards();
}

// ---- open modal ------------------------------------------------

$(document).on("click", ".addBatchBtn", function () {
    const $btn = $(this);
    activeBatchRowIndex = $(this).data("row-count");
    isExpiry = $(this).data("is-expiry");

    // IMPORTANT: read flag robustly
    canEditBatch = getIsBatchEnable($btn); // true when data-is-batch-number="1"
    if (!canEditBatch) {
        // ensure hidden JSON is up-to-date and do nothing else
        autoSyncLockedBatchForRow(activeBatchRowIndex);
    }

    // get expected qty from row
    expectedBatchQty =
        Number(
            $(`#itemTable #row_${activeBatchRowIndex}`)
                .find("[name*='[order_qty]']")
                .val()
        ) || 0;

    if (!expectedBatchQty) {
        Swal.fire("Error!", "Please enter quantity first.", "error");
        return;
    }

    // read saved data for this row (if any)
    const $batchInput = $(
        `#itemTable #row_${activeBatchRowIndex} input[name*='[batch_details]']`
    );
    let existing = [];
    if ($batchInput.val()) {
        try {
            existing = JSON.parse($batchInput.val());
        } catch (e) {
            existing = [];
        }
    }

    // Always rebuild rows fresh based on the CURRENT flag
    populateBatchTable(existing, canEditBatch);

    // Toggle header action buttons INSIDE THIS MODAL
    const $modal = $("#itemBatchModal"); // <-- use your actual modal id
    const $hdrBtns = $modal.find(
        ".add-batch-row-header, .delete-batch-row-header"
    );
    $hdrBtns
        .toggleClass("d-none", !canEditBatch)
        .prop("disabled", !canEditBatch);

    $("#itemBatchRowIndex").val(activeBatchRowIndex);
    $("#itemBatchIsExpiry").val(isExpiry); // hidden input for expiry flag
    $modal.modal("show");
});

// ---- add row ---------------------------------------------------

$(document).on("click", ".add-batch-row-header", function (e) {
    if (!canEditBatch) {
        e.preventDefault();
        return false;
    }

    const $tbody = $("#itemBatchTable tbody");
    const index = $tbody.find("tr").length;

    const total = updateBatchTotal();
    if (total >= expectedBatchQty) return;

    const remaining = Math.max(0, expectedBatchQty - total);

    $tbody.append(
        generateBatchRow(
            index,
            {
                batch_number: "",
                manufacturing_year: "",
                expiry_date: "",
                quantity: remaining,
            },
            true
        )
    );

    updateBatchTotal();
    applyDateGuards();
});

// ---- delete selected rows -------------------------------------

$(document).on("click", ".delete-batch-row-header", function (e) {
    if (!canEditBatch) {
        e.preventDefault();
        return false;
    }

    const $tbody = $("#itemBatchTable tbody");
    const $rows = $tbody.find("tr");
    const $checked = $tbody.find(".batch-row-check:checked");

    if ($checked.length === 0) return;
    if ($checked.length >= $rows.length) {
        Swal.fire("Warning", "At least one batch row must remain.", "warning");
        return;
    }

    $checked.each(function () {
        const idx = $(this).data("index");
        if (idx !== 0) $(this).closest("tr").remove();
    });

    reindexBatchRows();
    updateBatchTotal();
});

// ---- delete one row -------------------------------------------

$(document).on("click", ".remove-batch-row", function (e) {
    if (!canEditBatch) {
        e.preventDefault();
        return false;
    }
    e.preventDefault();

    const $tbody = $("#itemBatchTable tbody");
    const totalRows = $tbody.find("tr").length;
    if (totalRows === 1) {
        Swal.fire("Warning", "At least one batch row is required.", "warning");
        return;
    }

    $(this).closest("tr").remove();
    reindexBatchRows();
    updateBatchTotal();
});

// ---- qty change -> total --------------------------------------

$(document).on("input", "#itemBatchTable tbody .batch-qty", function () {
    updateBatchTotal();
});

// ---- save batches ---------------------------------------------

$(document).on("click", "#saveItemBatchBtn", function (e) {
    e.preventDefault();

    const $tbody = $("#itemBatchTable tbody");
    const rows = $tbody.find("tr");
    const now = new Date();
    const currentYear = now.getFullYear();
    const today = new Date(todayISO()); // midnight today local
    const EPS = 1e-6;
    let isExpiry = Number($("#itemBatchIsExpiry").val()) || 0;
    console.log("Expiry flag:", isExpiry);

    // clear old errors
    $tbody.find(".invalid-feedback").remove();
    $tbody.find("input").removeClass("is-invalid");

    let isValid = true;
    let totalQty = 0;
    const payload = [];

    rows.each(function () {
        const $row = $(this);
        const $bn = $row.find(".batch-number");
        const $year = $row.find(".manufacturing-year");
        const $exp = $row.find(".expiry-date");
        const $qty = $row.find(".batch-qty");

        const batch_number = ($bn.val() || "").trim();
        const manufacturing_year = ($year.val() || "").trim();
        const expiry_date = ($exp.val() || "").trim();
        const quantity = parseFloat(($qty.val() || "").trim()) || 0;

        // Batch number required
        if (!batch_number) {
            $bn.addClass("is-invalid").after(
                '<div class="invalid-feedback">Batch number is required.</div>'
            );
            isValid = false;
        }

        // Manufacturing year strictly past (if present)
        if (manufacturing_year) {
            const yr = parseInt(manufacturing_year, 10);
            if (
                !/^\d{4}$/.test(manufacturing_year) ||
                yr <= 1900 ||
                yr > currentYear
            ) {
                $year
                    .addClass("is-invalid")
                    .after(
                        `<div class="invalid-feedback">Manufacturing year must be a valid past year (1901–${currentYear}).</div>`
                    );
                isValid = false;
            }
        }

        // Expiry date mandatory iff isExpiry = 1 AND strictly future
        if (isExpiry == 1) {
            console.log("expiry_date", expiry_date);

            if (!expiry_date) {
                $exp.addClass("is-invalid").after(
                    '<div class="invalid-feedback">Expiry date is required.</div>'
                );
                isValid = false;
            } else {
                const exp = new Date(expiry_date);
                // must be > today (tomorrow or later)
                if (!(exp > today)) {
                    $exp.addClass("is-invalid").after(
                        '<div class="invalid-feedback">Expiry date must be in the future.</div>'
                    );
                    isValid = false;
                }
            }
        }

        // Quantity must be > 0
        if (!(quantity > 0)) {
            $qty.addClass("is-invalid").after(
                '<div class="invalid-feedback">Quantity must be greater than 0.</div>'
            );
            isValid = false;
        }

        totalQty += quantity;
        payload.push({
            batch_number,
            manufacturing_year,
            expiry_date,
            quantity,
        });
    });

    if (!isValid) {
        Swal.fire(
            "Invalid Input",
            "Please correct the highlighted fields.",
            "error"
        );
        return;
    }

    if (Math.abs(totalQty - expectedBatchQty) > EPS) {
        Swal.fire(
            "Quantity Mismatch",
            `Total batch quantity (${totalQty}) must match order quantity (${expectedBatchQty}).`,
            "error"
        );
        return;
    }

    // persist to hidden input of that row
    const hiddenName = `components[${activeBatchRowIndex}][batch_details]`;
    const $rowWrap = $(`#itemTable #row_${activeBatchRowIndex}`);
    let $hidden = $rowWrap.find(`input[name="${hiddenName}"]`);
    if ($hidden.length === 0) {
        $rowWrap.append(`<input type="hidden" name="${hiddenName}" />`);
        $hidden = $rowWrap.find(`input[name="${hiddenName}"]`);
    }
    $hidden.val(JSON.stringify(payload));

    $("#itemBatchModal").modal("hide");
    $rowWrap.find(".addBatchBtn").addClass("text-success"); // optional visual cue
});

function todayISO() {
    const d = new Date();
    const m = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    return `${d.getFullYear()}-${m}-${day}`;
}
function isoNDaysFromToday(n) {
    const d = new Date();
    d.setDate(d.getDate() + n);
    const m = String(d.getMonth() + 1).padStart(2, "0");
    const day = String(d.getDate()).padStart(2, "0");
    return `${d.getFullYear()}-${m}-${day}`;
}

// Apply min/max guards to all visible inputs inside the modal
function applyDateGuards() {
    const currentYear = new Date().getFullYear();

    // Expiry date: strictly future -> min = tomorrow
    const minExpiry = isoNDaysFromToday(1);
    $("#itemBatchTable tbody .expiry-date").each(function () {
        $(this).attr("min", minExpiry);
    });

    // Manufacturing year (number): strictly past -> max = currentYear - 1
    const maxMfgYear = currentYear - 1;
    $("#itemBatchTable tbody .manufacturing-year").each(function () {
        // Keep your min 1900, adjust max to currentYear-1
        $(this).attr("max", maxMfgYear);
    });
}

function seedAllLockedRows() {
    $("#itemTable .addBatchBtn").each(function () {
        const idx = $(this).data("row-count");
        const canEdit = getIsBatchEnable($(this));
        if (!canEdit) autoSyncLockedBatchForRow(idx);
    });
}

// Build the single locked-batch payload for a row
function buildLockedBatchPayload(rowIndex) {
    const $row = $(`#itemTable #row_${rowIndex}`);
    const qty = Number($row.find('[name*="[order_qty]"]').val()) || 0;
    if (!qty) return []; // nothing to save if no qty

    const lotNumber = getLotNumberForLocked(); // yyyy-mm-dd/BOOK/DOC
    return [
        {
            batch_number: lotNumber,
            manufacturing_year: "",
            expiry_date: "",
            quantity: qty,
        },
    ];
}

// Write JSON into the hidden input for a row
function writeBatchHidden(rowIndex, payload) {
    const hiddenName = `components[${rowIndex}][batch_details]`;
    const $rowWrap = $(`#itemTable #row_${rowIndex}`);
    let $hidden = $rowWrap.find(`input[name="${hiddenName}"]`);

    if ($hidden.length === 0) {
        $rowWrap.append(`<input type="hidden" name="${hiddenName}" />`);
        $hidden = $rowWrap.find(`input[name="${hiddenName}"]`);
    }
    $hidden.val(JSON.stringify(payload || []));
}

// If row is LOCKED (is-batch-number = false), auto-sync hidden JSON
function autoSyncLockedBatchForRow(rowIndex) {
    const $btn = $(`#itemTable, #row_${rowIndex}, .addBatchBtn`);

    const canEdit = getIsBatchEnable($btn); // true means editable batches
    if (canEdit) return; // only act when locked (no batch numbers)

    const payload = buildLockedBatchPayload(rowIndex);
    writeBatchHidden(rowIndex, payload);
}
