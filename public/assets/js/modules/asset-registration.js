// ---------- helpers ----------
function escapeHTML(str) {
    return String(str ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
function parseJSONSafe(s, fb) {
    try {
        return JSON.parse(s);
    } catch {
        return fb;
    }
}
function $rowWrap(idx) {
    const $a = $(`#itemTable #row_${idx}`);
    return $a.length ? $a : $(`#row_${idx}`);
}
function readHiddenAsset(idx) {
    const name = `components[${idx}][assetDetailData]`;
    const raw = (
        $rowWrap(idx).find(`input[name="${name}"]`).val() || ""
    ).trim();
    return raw ? parseJSONSafe(raw, null) : null;
}
function writeHiddenAsset(idx, obj) {
    const name = `components[${idx}][assetDetailData]`;
    const $row = $rowWrap(idx);
    let $h = $row.find(`input[name="${name}"]`);
    if (!$h.length) {
        $row.append(`<input type="hidden" name="${name}">`);
        $h = $row.find(`input[name="${name}"]`);
    }
    $h.val(JSON.stringify(obj || {}));
}
function rowItemTotalCost(idx) {
    return Number($rowWrap(idx).find("[name*='[item_total_cost]']").val()) || 0;
}

// ---------- open & populate ----------
$(document).on("click", ".assetDetailBtn", function () {
    const $btn = $(this);
    const row = Number($btn.data("row-count"));

    // 1) prefer saved hidden (update case), else data-asset from blade (create)
    const saved = readHiddenAsset(row);
    const dataAttr = parseJSONSafe($btn.attr("data-asset") || "{}", {});
    // Normalize defaults
    const salvagePct = Number(
        saved?.salvage_percentage ?? dataAttr.salvage_percentage ?? 0
    );
    const itemValue = rowItemTotalCost(row);
    const computedSalvage = (itemValue * salvagePct) / 100;

    const model = {
        asset_id: saved?.asset_id ?? dataAttr.asset_id ?? null,
        asset_name: saved?.asset_name ?? dataAttr.asset_name ?? "",
        asset_category_id:
            saved?.asset_category_id ?? dataAttr.asset_category_id ?? "",
        asset_category_name:
            saved?.asset_category_name ?? dataAttr.asset_category_name ?? "",
        asset_code: saved?.asset_code ?? dataAttr.asset_code ?? "",
        brand_name: saved?.brand_name ?? dataAttr.brand_name ?? "",
        model_no: saved?.model_no ?? dataAttr.model_no ?? "",
        estimated_life: saved?.estimated_life ?? dataAttr.estimated_life ?? "",
        salvage_percentage: salvagePct,
        // do NOT overwrite a previously saved salvage_value
        salvage_value:
            saved && saved.salvage_value != null
                ? Number(saved.salvage_value)
                : Number(dataAttr.salvage_value ?? computedSalvage),
        procurement_type:
            saved?.procurement_type ?? dataAttr.procurement_type ?? "",
        capitalization_date:
            saved?.capitalization_date ?? dataAttr.capitalization_date ?? "",
    };

    // 2) render form
    const assetCodeRO = model.asset_code ? "readonly" : ""; // if you want code to be locked when present
    const html = `
      <div class="row g-1" data-row-count="${row}">
        <div class="col-md-8">
            <label class="form-label">Asset Name <span class="text-danger">*</span></label>
            <input type="text" name="asset_name" class="form-control" required value="${escapeHTML(
                model.asset_name
            )}" />
            <input type="hidden" name="asset_id" class="form-control" value="${escapeHTML(
                model.asset_id
            )}" />
        </div>
  
        <div class="col-md-4">
          <label class="form-label">Asset Category <span class="text-danger">*</span></label>
          <input type="text" name="asset_category_name" class="form-control" required readonly value="${escapeHTML(
              model.asset_category_name
          )}" />
          <input type="hidden" name="asset_category_id" value="${escapeHTML(
              model.asset_category_id
          )}" />
        </div>
  
        <div class="col-md-4">
          <label class="form-label">Capitalization Date</label>
          <input type="date" name="capitalization_date" class="form-control" value="${escapeHTML(
              model.capitalization_date
          )}" />
        </div>
  
        <div class="col-md-4">
          <label class="form-label">Brand Name <span class="text-danger">*</span></label>
          <input type="text" name="brand_name" class="form-control" required value="${escapeHTML(
              model.brand_name
          )}" />
        </div>
  
        <div class="col-md-4">
          <label class="form-label">Model Number <span class="text-danger">*</span></label>
          <input type="text" name="model_no" class="form-control" required value="${escapeHTML(
              model.model_no
          )}" />
        </div>
  
        <div class="col-md-4">
          <label class="form-label">Estimated Life (years) <span class="text-danger">*</span></label>
          <input type="number" min="0" step="1" name="estimated_life" class="form-control" required value="${escapeHTML(
              model.estimated_life
          )}" />
        </div>
  
        <div class="col-md-4 d-none">
          <label class="form-label">Salvage Value <span class="text-danger">*</span></label>
          <input type="number" step="0.01" name="salvage_value" class="form-control" required value="${escapeHTML(
              model.salvage_value
          )}" readonly />
        </div>
  
        <div class="col-md-4">
          <label class="form-label">Procurement Type</label>
          <input type="text" name="procurement_type" class="form-control" value="${escapeHTML(
              model.procurement_type
          )}" readonly />
        </div>
  
        <div class="col-md-4 d-none">
          <label class="form-label">Asset Code</label>
          <input type="text" name="asset_code" class="form-control" value="${escapeHTML(
              model.asset_code
          )}" ${assetCodeRO} />
        </div>
      </div>
    `;

    $(".asset-detail-modal-body").html(html);
});

// ---------- save ----------
$(document).on("click", ".submitAssetBtn", function (e) {
    e.preventDefault();

    const $modal = $("#assetDetailModal");
    const $body = $modal.find(".asset-detail-modal-body");
    const row = Number($body.find(".row").data("row-count"));

    const pick = (name) => $body.find(`[name="${name}"]`).val();

    // required (except capitalization_date per your note)
    const required = [
        "asset_name",
        "asset_category_id",
        "brand_name",
        "model_no",
        "estimated_life",
        "salvage_value",
    ];
    let valid = true;
    required.forEach((n) => {
        if (n === "asset_id") return; // ⬅️ exclude asset_id from validation
        const $f = $body.find(`[name="${n}"]`);
        if (!$f.val() || String($f.val()).trim() === "") {
            $f.addClass("is-invalid");
            valid = false;
        } else {
            $f.removeClass("is-invalid");
        }
    });

    if (!valid) {
        Swal.fire({
            icon: "error",
            title: "Missing Fields",
            text: "Please fill all required asset detail fields.",
        });
        return;
    }

    // build model to persist
    const obj = {
        asset_id: pick("asset_id") ?? null, // set from server when persisted, or carry forward if you want
        asset_name: pick("asset_name"),
        asset_category_id: pick("asset_category_id"),
        asset_category_name: pick("asset_category_name"),
        asset_code: pick("asset_code"),
        brand_name: pick("brand_name"),
        model_no: pick("model_no"),
        estimated_life: Number(pick("estimated_life")) || 0,
        salvage_percentage: Number(pick("salvage_percentage")) || 0,
        salvage_value: Number(pick("salvage_value")) || 0,
        procurement_type: pick("procurement_type"),
        capitalization_date: pick("capitalization_date"),
    };

    writeHiddenAsset(row, obj);
    $modal.modal("hide");
    $rowWrap(row).find(".assetDetailBtn").addClass("text-success");
});

// === Auto-seed assetDetailData on page load (edit) ===
(function () {
    // optional: only run on edit pages if you set this flag server-side
    const IS_EDIT =
        typeof window.IS_EDIT !== "undefined" ? !!window.IS_EDIT : true;

    // light fallbacks (only if your helpers aren't already defined)
    function parseJSONSafe(s, fb) {
        try {
            return JSON.parse(s);
        } catch {
            return fb;
        }
    }
    function $rowWrap(idx) {
        const $a = $(`#itemTable #row_${idx}`);
        return $a.length ? $a : $(`#row_${idx}`);
    }
    if (typeof readHiddenAsset !== "function") {
        window.readHiddenAsset = function (idx) {
            const name = `components[${idx}][assetDetailData]`;
            const raw = (
                $rowWrap(idx).find(`input[name="${name}"]`).val() || ""
            ).trim();
            return raw ? parseJSONSafe(raw, null) : null;
        };
    }
    if (typeof writeHiddenAsset !== "function") {
        window.writeHiddenAsset = function (idx, obj) {
            const name = `components[${idx}][assetDetailData]`;
            const $row = $rowWrap(idx);
            let $h = $row.find(`input[name="${name}"]`);
            if (!$h.length) {
                $row.append(`<input type="hidden" name="${name}">`);
                $h = $row.find(`input[name="${name}"]`);
            }
            $h.val(JSON.stringify(obj || {}));
        };
    }
    if (typeof rowItemTotalCost !== "function") {
        window.rowItemTotalCost = function (idx) {
            return (
                Number(
                    $rowWrap(idx).find("[name*='[item_total_cost]']").val()
                ) || 0
            );
        };
    }

    function shouldSeedFromData(data) {
        // treat as "edit-like" if we have any meaningful values
        return !!(
            data &&
            (data.asset_id ||
                (data.asset_name && String(data.asset_name).trim() !== "") ||
                (data.brand_name && String(data.brand_name).trim() !== "") ||
                (data.model_no && String(data.model_no).trim() !== ""))
        );
    }

    function buildModel(idx, saved, dataAttr) {
        const salvagePct = Number(
            saved && saved.salvage_percentage != null
                ? saved.salvage_percentage
                : dataAttr.salvage_percentage ?? 0
        );
        const cost = rowItemTotalCost(idx);
        const computed = (cost * salvagePct) / 100;

        return {
            asset_id: saved?.asset_id ?? dataAttr.asset_id ?? null,
            asset_name: saved?.asset_name ?? dataAttr.asset_name ?? "",
            asset_category_id:
                saved?.asset_category_id ?? dataAttr.asset_category_id ?? "",
            asset_category_name:
                saved?.asset_category_name ??
                dataAttr.asset_category_name ??
                "",
            asset_code: saved?.asset_code ?? dataAttr.asset_code ?? "",
            brand_name: saved?.brand_name ?? dataAttr.brand_name ?? "",
            model_no: saved?.model_no ?? dataAttr.model_no ?? "",
            estimated_life:
                saved?.estimated_life ?? dataAttr.estimated_life ?? "",
            salvage_percentage: salvagePct,
            // if already saved, keep it; else compute from current item_total_cost
            salvage_value:
                saved && saved.salvage_value != null
                    ? Number(saved.salvage_value)
                    : Number(dataAttr.salvage_value ?? computed),
            procurement_type:
                saved?.procurement_type ?? dataAttr.procurement_type ?? "",
            capitalization_date:
                saved?.capitalization_date ??
                dataAttr.capitalization_date ??
                "",
        };
    }

    function seedForRow($btn) {
        const idx = Number($btn.data("row-count"));
        if (!idx) return;

        // 1) If hidden already has something, don't touch (user may have saved)
        const savedHidden = readHiddenAsset(idx);
        if (savedHidden) return;

        // 2) Read payload from data-asset (Blade)
        const raw = $btn.attr("data-asset") || "{}";
        const dataAttr = parseJSONSafe(raw, {});
        if (!IS_EDIT && !shouldSeedFromData(dataAttr)) return;

        // 3) Build model and write hidden
        const model = buildModel(idx, null, dataAttr);
        if (shouldSeedFromData(model)) {
            writeHiddenAsset(idx, model);
            // optional visual cue
            $btn.addClass("text-success");
        }
    }

    $(function () {
        // Iterate all rows that have asset details enabled
        $(".assetDetailBtn").each(function () {
            seedForRow($(this));
        });
    });
})();
