/* ============================================================
   Item Batches Modal — GRN + Inspection + Accepted/Rejected
   - Inspection (required, editable) ≤ GRN
   - Accepted (optional, editable)
   - Rejected (optional, readonly) = Inspection − Accepted
   - Footer totals (GRN/Inspection/Accepted/Rejected/Balance)
   - Save requires per-row: Accepted + Rejected = Inspection
   - Detail row is auto-filled with sums; and if the Add Batch
     button is hidden (isBatchEditable=false), editing the detail
     row still updates the hidden payload (FIFO allocation).
   ============================================================ */

(function () {
    const MODAL_ID = "#itemBatchModal";
    const TABLE_ID = "#itemBatchTable";
    const SAVE_ID = "#saveItemBatchBtn";
    const EPS = 1e-9;

    let activeBatchRowIndex = null;

    // ---------------- utilities ----------------
    const parseJSONSafe = (s, fb) => {
        try {
            return JSON.parse(s);
        } catch {
            return fb;
        }
    };
    const decodeHtml = (s) =>
        String(s || "")
            .replace(/&quot;/g, '"')
            .replace(/&#039;|&apos;/g, "'")
            .replace(/&lt;/g, "<")
            .replace(/&gt;/g, ">")
            .replace(/&amp;/g, "&");

    function $rowWrap(idx) {
        const $a = $(`#itemTable #row_${idx}`);
        return $a.length ? $a : $(`#row_${idx}`);
    }

    function readHidden(idx) {
        const name = `components[${idx}][batch_details]`;
        const raw = (
            $rowWrap(idx).find(`input[name="${name}"]`).val() || ""
        ).trim();
        return parseJSONSafe(raw, []);
    }
    function writeHidden(idx, payload) {
        const $row = $rowWrap(idx);
        const name = `components[${idx}][batch_details]`;
        let $hidden = $row.find(`input[name="${name}"]`);
        if ($hidden.length === 0) {
            $row.append(`<input type="hidden" name="${name}" />`);
            $hidden = $row.find(`input[name="${name}"]`);
        }
        $hidden.val(JSON.stringify(payload || []));
    }

    function getDetailInputs(idx) {
        const $row = $rowWrap(idx);
        return {
            $row,
            $accepted: $row.find(
                `input[name="components[${idx}][accepted_qty]"]`
            ),
            $rejected: $row.find(
                `input[name="components[${idx}][rejected_qty]"]`
            ),
            // one of these may exist in your grid; we support all
            $inspected: $row.find(
                `input[name="components[${idx}][inspection_qty]"],
                 input[name="components[${idx}][inspected_qty]"],
                 input[name="components[${idx}][order_qty]"]`
            ),
            $btn: $row.find(".addBatchBtn"),
        };
    }

    function getBatchCountForRow(idx) {
        const { $btn } = getDetailInputs(idx);
        return Number($btn.data("batch-count")) || 0;
    }

    function isBatchEditableForRow(idx) {
        const { $btn } = getDetailInputs(idx);
        // Blade already hides/shows the button using style; still read it:
        return $btn.is(":visible");
    }

    // --------- MRN attr parsing (robust) ----------
    function sanitizeMrnAttr(raw) {
        let s = decodeHtml(raw || "").trim();
        s = s.replace(/(\d{4}-\d{2}-\d{2})\s*=\s*""\s*/g, "$1 ");
        s = s.replace(/\s{2,}/g, " ");
        return s;
    }
    function normalize(obj) {
        if (Array.isArray(obj)) return obj.map(normalize);
        if (obj && typeof obj === "object") {
            const out = {};
            for (const [k, v] of Object.entries(obj))
                out[String(k).trim()] = normalize(v);
            return out;
        }
        return obj;
    }
    function getMrnBatches(idx) {
        const rawAttr =
            $rowWrap(idx).find(".addBatchBtn").attr("data-mrn-batches") || "[]";
        let s = sanitizeMrnAttr(rawAttr);
        let obj = parseJSONSafe(s, null);
        if (!obj) {
            try {
                obj = new Function(`return (${s})`)();
            } catch {
                obj = [];
            }
        }
        obj = normalize(obj);
        return (Array.isArray(obj) ? obj : []).map((b) => ({
            id: b.id ?? null,
            mrn_batch_detail_id: b.mrn_batch_detail_id ?? b.id ?? null,
            batch_number: String(b.batch_number ?? ""),
            manufacturing_year: b.manufacturing_year ?? "",
            expiry_date: String(b.expiry_date ?? "")
                .replace(/T.*/, "")
                .replace(/\s+00:00:00$/, ""),
            quantity: Number(b.quantity ?? 0) || 0,
            inspection_qty:
                Number(
                    b.inspection_qty ??
                        Number(b.accepted_qty || 0) +
                            Number(b.rejected_qty || 0)
                ) || 0,
            accepted_qty: Number(b.accepted_qty ?? 0) || 0,
            rejected_qty: Number(b.rejected_qty ?? 0) || 0,
        }));
    }

    // --------- payload & rendering ----------
    function ensureFooter() {
        if (!$(TABLE_ID).find("tfoot").length) {
            $(TABLE_ID).append(`
          <tfoot>
            <tr class="fw-bold">
              <td colspan="4" class="text-end">Totals</td>
              <td class="total-grn text-end">0</td>
              <td class="total-insp text-end">0</td>
              <td class="total-acc text-end">0</td>
              <td class="total-rej text-end">0</td>
            </tr>
          </tfoot>
        `);
        }
    }

    function generateRow(i, r) {
        const grn = Number(r.mrn_qty ?? r.quantity ?? 0) || 0;
        const ins = Number(r.inspection_qty ?? 0) || 0;
        const acc = Number(r.accepted_qty ?? 0) || 0;
        const rej = Number(r.rejected_qty ?? 0) || 0;

        return `
        <tr data-index="${i}">
          <td>${i + 1}</td>
          <td>
            <input type="hidden" class="insp-batch-id" value="${r.id ?? ""}">
            <input type="hidden" class="mrn-batch-id" value="${
                r.mrn_batch_detail_id ?? ""
            }">
            <input type="hidden" class="mrn-qty" value="${grn}">
            <input type="text"   class="form-control mw-100 batch-number" value="${
                r.batch_number ?? ""
            }" readonly>
          </td>
          <td><input type="text" class="form-control mw-100 manufacturing-year" value="${
              r.manufacturing_year ?? ""
          }" readonly></td>
          <td><input type="text" class="form-control mw-100 expiry-date" value="${
              r.expiry_date ?? ""
          }"       readonly></td>
          <td><input type="number" step="any" class="form-control mw-100 text-end grn-qty"        value="${grn}" readonly></td>
          <td><input type="number" step="any" class="form-control mw-100 text-end inspection-qty" value="${ins}"></td>
          <td><input type="number" step="any" class="form-control mw-100 text-end accepted-qty"   value="${acc}"></td>
          <td><input type="number" step="any" class="form-control mw-100 text-end rejected-qty"   value="${rej}" readonly></td>
        </tr>
      `;
    }

    function renderTable(payload) {
        const $tb = $(`${TABLE_ID} tbody`);
        $tb.empty();
        (payload || []).forEach((r, i) => $tb.append(generateRow(i, r)));
        ensureFooter();
        updateFooter();
    }

    function payloadFromMrn(mrn, existing) {
        // If we already have a saved payload, use it as-is (don't override user input)
        if (Array.isArray(existing) && existing.length) return existing;

        // Defaults: inspection = grn, accepted = inspection, rejected = 0
        return (mrn || []).map((b) => {
            const grn = Number(b.quantity || 0) || 0;
            const insp = Number(b.inspection_qty ?? grn) || grn; // default to GRN
            const acc = Number(b.accepted_qty ?? insp) || insp; // default to inspection
            const rej = Math.max(insp - acc, 0);

            return {
                id: b.id,
                mrn_batch_detail_id: b.mrn_batch_detail_id,
                batch_number: b.batch_number,
                manufacturing_year: b.manufacturing_year,
                expiry_date: b.expiry_date,
                mrn_qty: grn,
                inspection_qty: insp,
                accepted_qty: acc,
                rejected_qty: rej,
            };
        });
    }

    // --------- totals & events ----------
    function readTotals() {
        let grn = 0,
            insp = 0,
            acc = 0,
            rej = 0;
        $(`${TABLE_ID} tbody tr`).each(function () {
            grn += Number($(this).find(".mrn-qty").val()) || 0;
            insp += Number($(this).find(".inspection-qty").val()) || 0;
            acc += Number($(this).find(".accepted-qty").val()) || 0;
            rej += Number($(this).find(".rejected-qty").val()) || 0;
        });
        return { grn, insp, acc, rej, bal: acc - rej };
    }

    function updateFooter() {
        const t = readTotals();
        const F = (n) => (Number.isFinite(n) ? (+n).toString() : "0");
        $(TABLE_ID).find(".total-grn").text(F(t.grn));
        $(TABLE_ID).find(".total-insp").text(F(t.insp));
        $(TABLE_ID).find(".total-acc").text(F(t.acc));
        $(TABLE_ID).find(".total-rej").text(F(t.rej));
    }

    const clamp = (n, lo, hi) => {
        n = Number(n);
        if (!Number.isFinite(n)) n = 0;
        return Math.min(Math.max(n, lo), hi);
    };

    // Inspection changed → clamp to [0, GRN], set Accepted = Inspection, Rejected = 0
    $(document).on(
        "input change",
        `${TABLE_ID} tbody .inspection-qty`,
        function () {
            const $tr = $(this).closest("tr");
            const grn = Number($tr.find(".mrn-qty").val()) || 0;
            const i = clamp($(this).val(), 0, grn);
            $(this).val(i);
            $tr.find(".accepted-qty").val(i);
            $tr.find(".rejected-qty").val(0);
            updateFooter();
        }
    );

    // Accepted changed → clamp to [0, Inspection], Rejected = Inspection − Accepted
    $(document).on(
        "input change",
        `${TABLE_ID} tbody .accepted-qty`,
        function (evt) {
            if (!evt.originalEvent) return;
            const $tr = $(this).closest("tr");
            const i = Number($tr.find(".inspection-qty").val()) || 0;
            const a = clamp($(this).val(), 0, i);
            $(this).val(a);
            $tr.find(".rejected-qty").val(i - a);
            updateFooter();
        }
    );

    // --------- OPEN MODAL ----------
    $(document).on("click", ".addBatchBtn", function () {
        activeBatchRowIndex = Number($(this).data("row-count"));

        const saved = readHidden(activeBatchRowIndex);
        const usingSaved = Array.isArray(saved) && saved.length > 0;
        const mrn = getMrnBatches(activeBatchRowIndex);
        const payload = payloadFromMrn(mrn, saved); // uses defaults only when no saved

        // If we used saved data, just ensure rejected = inspection - accepted (don’t override accepted/inspection)
        if (usingSaved) {
            payload.forEach((r) => {
                const insp = Number(r.inspection_qty || 0);
                const acc = Number(r.accepted_qty || 0);
                r.rejected_qty = Math.max(insp - acc, 0);
            });
        } else {
            // First open (no saved): force defaults exactly as requested
            payload.forEach((r) => {
                const grn = Number(r.mrn_qty || r.quantity || 0) || 0;
                r.inspection_qty = grn;
                r.accepted_qty = grn;
                r.rejected_qty = 0;
            });
        }

        renderTable(payload);
        $(MODAL_ID).modal("show");
    });

    // --------- SAVE ----------
    $(document).on("click", SAVE_ID, function (e) {
        e.preventDefault();

        const out = [];
        let valid = true;

        $(`${TABLE_ID} tbody tr`).each(function () {
            const $tr = $(this);
            const grn = Number($tr.find(".mrn-qty").val()) || 0;

            let i = clamp($tr.find(".inspection-qty").val(), 0, grn);
            $tr.find(".inspection-qty").val(i);

            // if Accepted blank/NaN → default to Inspection
            let aRaw = $tr.find(".accepted-qty").val();
            let a = aRaw === "" || aRaw === null ? i : clamp(aRaw, 0, i);
            $tr.find(".accepted-qty").val(a);

            let r = i - a;
            $tr.find(".rejected-qty").val(r);

            // Validation: inspection ≤ grn; and accepted + rejected == inspection
            if (Math.abs(a + r - i) > EPS) {
                valid = false;
                $tr.find(".accepted-qty").addClass("is-invalid");
            } else {
                $tr.find(".accepted-qty").removeClass("is-invalid");
            }

            out.push({
                id: $tr.find(".insp-batch-id").val() || null,
                mrn_batch_detail_id: $tr.find(".mrn-batch-id").val() || null,
                batch_number: $tr.find(".batch-number").val(),
                manufacturing_year: $tr.find(".manufacturing-year").val(),
                expiry_date: $tr.find(".expiry-date").val(),
                mrn_qty: grn,
                inspection_qty: i,
                accepted_qty: a,
                rejected_qty: r,
            });
        });

        if (!valid) {
            Swal.fire(
                "Allocation required",
                "For each batch, Accepted + Rejected must equal Inspection quantity (and Inspection must be ≤ GRN).",
                "error"
            );
            return;
        }

        // after you build `out` and validate...
        writeHidden(activeBatchRowIndex, out);

        // sum Accepted/Rejected from modal payload
        let totAcc = 0,
            totRej = 0;
        out.forEach((r) => {
            totAcc += Number(r.accepted_qty || 0);
            totRej += Number(r.rejected_qty || 0);
        });

        // write into the detail row (fire input+change so any other logic updates)
        const { $accepted, $rejected, $inspected } =
            getDetailInputs(activeBatchRowIndex);

        // $accepted.val(totAcc).trigger("input").trigger("change");
        // $rejected.val(totRej).trigger("input").trigger("change");
        $accepted.val(totAcc);
        $rejected.val(totRej);
        console.log("Setting Accepted:", totAcc, "Rejected:", totRej);

        // inspection qty on the row should be A+R
        if ($inspected.length) {
            $inspected.val(totAcc + totRej);
        }

        $(MODAL_ID).modal("hide");
    });

    // --------- locks: >1 MRN batches => detail readonly ----------
    function applyDetailLocks() {
        $('tr[id^="row_"]').each(function () {
            const idx = Number($(this).data("index"));
            const count = getBatchCountForRow(idx);
            const { $accepted, $rejected } = getDetailInputs(idx);
            if (count > 1) {
                $accepted.prop("readonly", true).addClass("bg-light");
                $rejected.prop("readonly", true).addClass("bg-light");
            } else {
                $accepted.prop("readonly", false).removeClass("bg-light");
                $rejected.prop("readonly", true).addClass("bg-light"); // always readonly
            }
        });
    }
    $(applyDetailLocks);

    // --------- when button is hidden OR any time detail changes, keep payload in sync (FIFO) ----------
    function fifoDistributeToBatches(batches, totalInspection, totalAccepted) {
        const out = [];
        let I = Number(totalInspection) || 0;
        let A = Math.min(Number(totalAccepted) || 0, I);

        (batches || []).forEach((b) => {
            const cap = Number(b.mrn_qty ?? b.quantity ?? 0) || 0;
            const i = Math.min(I, cap);
            I -= i;
            const a = Math.min(A, i);
            A -= a;
            const r = i - a;

            out.push({
                id: b.id ?? null,
                mrn_batch_detail_id: b.mrn_batch_detail_id ?? null,
                batch_number: b.batch_number ?? "",
                manufacturing_year: b.manufacturing_year ?? "",
                expiry_date: b.expiry_date ?? "",
                mrn_qty: cap,
                inspection_qty: i,
                accepted_qty: a,
                rejected_qty: r,
            });
        });

        // If there were no MRN batches at all, make a synthetic single row
        if (out.length === 0) {
            const i = Math.max(I, 0),
                a = Math.min(A, i),
                r = i - a;
            out.push({
                id: null,
                mrn_batch_detail_id: null,
                batch_number: "",
                manufacturing_year: "",
                expiry_date: "",
                mrn_qty: i, // fallback
                inspection_qty: i,
                accepted_qty: a,
                rejected_qty: r,
            });
        }
        return out;
    }

    function syncFromDetailToPayload(idx) {
        const { $accepted, $rejected, $inspected } = getDetailInputs(idx);
        const desiredInspection = Number($inspected.val()) || 0;
        const desiredAccepted = Number($accepted.val()) || 0;

        // get existing payload or MRN to know capacities
        let payload = readHidden(idx);
        if (!Array.isArray(payload) || payload.length === 0) {
            const mrn = getMrnBatches(idx);
            payload = payloadFromMrn(mrn, []);
        }

        // create a distribution (FIFO by batch list)
        const distributed = fifoDistributeToBatches(
            payload,
            desiredInspection,
            desiredAccepted
        );
        writeHidden(idx, distributed);

        // set rejected detail to keep detail consistent
        const totals = distributed.reduce(
            (t, r) => ({
                insp: t.insp + (r.inspection_qty || 0),
                acc: t.acc + (r.accepted_qty || 0),
                rej: t.rej + (r.rejected_qty || 0),
            }),
            { insp: 0, acc: 0, rej: 0 }
        );

        $rejected.val(totals.rej);
    }

    // on detail Accepted / Inspection change → always sync hidden payload;
    // when batches >1 we typically lock inputs, but if unlocked, we still sync.
    $(document).on(
        "input change",
        '[name^="components["][name$="[accepted_qty]"],[name^="components["][name$="[inspection_qty]"],[name^="components["][name$="[inspected_qty]"]',
        function () {
            const $tr = $(this).closest('tr[id^="row_"]');
            const idx = Number($tr.data("index"));
            // Always keep payload up-to-date (even when button hidden)
            syncFromDetailToPayload(idx);
        }
    );

    // --------- initial hidden payload seed (zeros if none) ----------
    $(function () {
        $('tr[id^="row_"]').each(function () {
            const idx = Number($(this).data("index"));
            if (!readHidden(idx)?.length) {
                const mrn = getMrnBatches(idx);
                const payload = payloadFromMrn(mrn, []); // uses defaults: insp=grn, acc=grn, rej=0
                writeHidden(idx, payload);
            }
        });
    });
})();
