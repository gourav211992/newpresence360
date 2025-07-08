public function processPoItem(Request $request)
{
    $user = Helper::getAuthenticatedUser();
    $type = 'po';
    $ids = json_decode($request->ids, true) ?? [];
    $asnIds = json_decode($request->asnIds, true) ?? [];
    $asnItemIds = json_decode($request->asnItemIds, true) ?? [];
    $geIds = json_decode($request->geIds, true) ?? [];
    $geItemIds = json_decode($request->geItemIds, true) ?? [];
    $moduleTypes = json_decode($request->moduleTypes, true) ?? [];
    $vendor = null;

    if (count(array_unique($moduleTypes)) > 1) {
        return response()->json([
            'data' => ['pos' => ''],
            'status' => 422,
            'message' => "Multiple different module types are not allowed."
        ]);
    }

    $vendorAsn = null;
    $moduleType = $moduleTypes[0] ?? null;
    $tableRowCount = $request->tableRowCount ?: 0;
    $poItems = collect();
    $uniquePoIds = [];
    $headerExpenseIds = [];
    $finalExpenses = [];
    $purchaseData = [];
    $purchaseOrder = '';

    // ---------- GATE ENTRY ----------
    if ($moduleType === 'gate-entry') {
        $poItems = GateEntryDetail::with(
            [
                'gateEntryHeader',
                'gateEntryHeader.purchaseOrder',
                'poItem',
                'poItem.po',
            ]
        )
        ->whereIn('id', $geItemIds)
        ->groupBy('purchase_order_item_id')
        ->get();
        $poItemIds = $poItems->pluck('purchase_order_item_id')->unique()->toArray();
        $uniquePoIds = $poItems->pluck('purchase_order_id')->unique()->toArray();

        // Fetch gate-entry level header expenses
        $geExpenses = GateEntry::whereIn('id', $gateEntryIds)
            ->with(['headerExpenses' => function ($query) {
                $query->where('ted_level', 'H');
            }])
            ->get()
            ->keyBy('id');

        // Value of selected GE items
        $selectedGeItemValues = GateEntryDetail::whereIn('id', $poItemIds)
            ->select('header_id', \DB::raw('SUM(order_qty * rate) as total'))
            ->groupBy('header_id')
            ->pluck('total', 'header_id');

        foreach ($geExpenses as $geId => $entry) {
            $poId = $entry->po_id ?? $poItems->pluck('header_id')->first(); // fallback
            $poValue = $selectedPoItemValues[$poId] ?? 0;

            foreach ($entry->headerExpenses as $expense) {
                $perc = $poValue > 0 ? ($expense->ted_amount / $poValue) * 100 : 0;
                $amount = number_format(($poValue * $perc / 100), 2);

                $finalExpenses[] = [
                    'id' => $expense->id,
                    'gate_entry_id' => $geId,
                    'ted_id' => $expense->ted_id,
                    'ted_name' => $expense->ted_name,
                    'ted_amount' => $amount,
                    'ted_perc' => round($perc, 4),
                ];
            }
        }

        $purchaseData = GateEntryHeader::whereIn('id', $geIds)
            ->with(['items' => function ($query) use ($geIds) {
                $query->whereIn('id', $geIds);
            }])
            ->get();

        $purchaseOrder = GateEntryHeader::whereIn('id', $geIds)->first();

        $view = 'procurement.material-receipt.partials.gate-entry-item-row';
    } else {
        // ---------- SUPPLIER INVOICE ----------
        if ($moduleType === 'suppl-inv') {
            $filteredAsnIds = array_filter($asnIds);
            $uniqueAsnIds = array_unique($filteredAsnIds);

            if (count($uniqueAsnIds) > 1) {
                return response()->json([
                    'data' => ['pos' => ''],
                    'status' => 422,
                    'message' => "Multiple ASN are not allowed."
                ]);
            }

            $vendorAsn = VendorAsn::whereIn('id', $uniqueAsnIds)->first();
            $vendorAsnItems = VendorAsnItem::whereIn('id', $asnItemIds)
                ->with(['vendorAsn', 'po_item.item', 'po_item.attributes'])
                ->get();

            $poItems = $vendorAsnItems->map(function ($asnItem) {
                $poItem = $asnItem->po_item;
                if ($poItem) {
                    $poItem->avail_order_qty = $asnItem->order_qty;
                    $poItem->balance_qty = $asnItem->balance_qty;
                    $poItem->asn_id = $asnItem->vendor_asn_id;
                    $poItem->asn_item_id = $asnItem->id;
                    $poItem->available_qty = ((($asnItem->supplied_qty) - ($asnItem->short_close_qty ?? 0)) - ($asnItem->ge_qty ?? 0));
                    $poItem->vendorAsn = $asnItem->vendorAsn;
                }
                return $poItem;
            })->filter()->values();

            $uniquePoIds = $poItems->pluck('purchase_order_id')->unique()->toArray();
        } else{
            $poItems = PoItem::whereIn('id', $ids)->get();
            foreach ($poItems as $poItem) {
                $poItem->avail_order_qty = $poItem->order_qty ?? 0;
                $poItem->available_qty = ((($poItem->order_qty ?? 0) - ($poItem->short_close_qty ?? 0)) - ($poItem->ge_qty ?? 0));
            }
            $uniquePoIds = $poItems->pluck('purchase_order_id')->unique()->toArray();
        }

        $poExpenses = PurchaseOrder::whereIn('id', $uniquePoIds)
            ->with(['headerExpenses' => function ($query) {
                $query->where('ted_level', 'H');
            }])
            ->get()
            ->keyBy('id');

        $selectedPoItemValues = PoItem::whereIn('id', $ids)
            ->select('purchase_order_id', \DB::raw('SUM(order_qty * rate) as total'))
            ->groupBy('purchase_order_id')
            ->pluck('total', 'purchase_order_id');

        $poValues = PoItem::whereIn('purchase_order_id', $uniquePoIds)
            ->select('purchase_order_id', \DB::raw('SUM(order_qty * rate) as total'))
            ->groupBy('purchase_order_id')
            ->pluck('total', 'purchase_order_id');

        foreach ($poExpenses as $poId => $po) {
            $poValue = $poValues[$poId] ?? 0;
            $selectedPoItemValue = $selectedPoItemValues[$poId] ?? 0;

            foreach ($po->headerExpenses as $expense) {
                $perc = $poValue > 0 ? ($expense->ted_amount / $poValue) * 100 : 0;
                $amount = number_format(($selectedPoItemValue * $perc / 100), 2);

                $finalExpenses[] = [
                    'id' => $expense->id,
                    'purchase_order_id' => $poId,
                    'ted_id' => $expense->ted_id,
                    'ted_name' => $expense->ted_name,
                    'ted_amount' => $amount,
                    'ted_perc' => round($perc, 4),
                ];
            }
        }

        $purchaseData = PurchaseOrder::whereIn('id', $uniquePoIds)
            ->with(['items' => function ($query) use ($ids) {
                $query->whereIn('id', $ids);
            }])
            ->get();

        $purchaseOrder = PurchaseOrder::whereIn('id', $uniquePoIds)->first();

        $view = 'procurement.material-receipt.partials.po-item-row';
    }

    // ---------- COMMON TO ALL MODULES ----------
    $locations = InventoryHelper::getAccessibleLocations('stock');
    $pos = PurchaseOrder::whereIn('id', $uniquePoIds)->get();

    $vendorId = $pos->pluck('vendor_id')->unique();
    if ($vendorId->count() > 1) {
        return response()->json([
            'data' => ['pos' => ''],
            'status' => 422,
            'message' => "You can not select multiple vendors of PO items at a time."
        ]);
    } else {
        $vendor = Vendor::find($vendorId->first());
    }

    $html = view($view, [
        'pos' => $pos,
        'type' => $type,
        'poItems' => $poItems,
        'locations' => $locations,
        'moduleType' => $moduleType,
        'purchaseData' => $purchaseData,
        'tableRowCount' => $tableRowCount
    ])->render();

    return response()->json([
        'data' => [
            'pos' => $html,
            'vendor' => $vendor,
            'vendorAsn' => $vendorAsn,
            'moduleType' => $moduleType,
            'finalExpenses' => $finalExpenses,
            'purchaseOrder' => $purchaseOrder,
        ],
        'status' => 200,
        'message' => "fetched!"
    ]);
}









public function processPoItem(Request $request)
{
    $user = Helper::getAuthenticatedUser();
    $type = 'po';
    $ids = json_decode($request->ids, true) ?? [];
    $asnIds = json_decode($request->asnIds, true) ?? [];
    $asnItemIds = json_decode($request->asnItemIds, true) ?? [];
    $geIds = json_decode($request->geIds, true) ?? [];
    $geItemIds = json_decode($request->geItemIds, true) ?? [];
    $moduleTypes = json_decode($request->moduleTypes, true) ?? [];
    $vendor = null;
    // Ensure all module types are the same
    if (count(array_unique($moduleTypes)) > 1) {
        return response()->json([
            'data' => ['pos' => ''],
            'status' => 422,
            'message' => "Multiple different module types are not allowed."
        ]);
    }

    $vendorAsn = null;
    $moduleType = $moduleTypes[0] ?? null;
    $tableRowCount = $request->tableRowCount ?: 0;

    // Determine module type
    $moduleType = $moduleTypes[0] ?? null;

    if ($moduleType === 'gate-entry') {
        $filteredGeIds = array_filter($geIds);
        $uniqueGeIds = array_unique($filteredGeIds);

        if (count($uniqueGeIds) > 1) {
            return response()->json([
                'data' => ['pos' => ''],
                'status' => 422,
                'message' => "Multiple Gate Entry are not allowed."
            ]);
        }
        $geHeader = GateEntryHeader::whereIn('id', $uniqueGeIds)->first();

        $gateEntryItems = GateEntryDetail::whereIn('id', $geItemIds)
            ->with(['gateEntryHeader', 'po_item.item', 'po_item.attributes'])
            ->get();

        $poItems = $gateEntryItems->map(function ($geItem) {
            $poItem = $geItem->po_item;
            if ($poItem) {
                $poItem->avail_order_qty = $geItem->order_qty;
                $poItem->balance_qty = $geItem->accepted_qty;
                $poItem->ge_id = $geItem->header_id;
                $poItem->ge_item_id = $geItem->id;
                $poItem->available_qty = (($geItem->accepted_qty ?? 0) - ($geItem->mrn_qty ?? 0));
                $poItem->gateEntryHeader = $asnItem->gateEntryHeader;
            }
            return $poItem;
        })->filter()->values();

        $uniquePoIds = $poItems->pluck('purchase_order_id')->unique()->toArray();
    } elseif($moduleType === 'suppl-inv'){
        $filteredAsnIds = array_filter($asnIds);
        $uniqueAsnIds = array_unique($filteredAsnIds);

        if (count($uniqueAsnIds) > 1) {
            return response()->json([
                'data' => ['pos' => ''],
                'status' => 422,
                'message' => "Multiple ASN are not allowed."
            ]);
        }
        $vendorAsn = VendorAsn::whereIn('id', $uniqueAsnIds)->first();

        $vendorAsnItems = VendorAsnItem::whereIn('id', $asnItemIds)
            ->with(['vendorAsn', 'po_item.item', 'po_item.attributes'])
            ->get();

        $poItems = $vendorAsnItems->map(function ($asnItem) {
            $poItem = $asnItem->po_item;
            if ($poItem) {
                $poItem->avail_order_qty = $asnItem->order_qty;
                $poItem->balance_qty = $asnItem->balance_qty;
                $poItem->asn_id = $asnItem->vendor_asn_id;
                $poItem->asn_item_id = $asnItem->id;
                $poItem->available_qty = ((($asnItem->supplied_qty) - ($asnItem->short_close_qty ?? 0)) - ($asnItem->ge_qty ?? 0));
                $poItem->vendorAsn = $asnItem->vendorAsn;
            }
            return $poItem;
        })->filter()->values();

        $uniquePoIds = $poItems->pluck('purchase_order_id')->unique()->toArray();
    } else {
        $poItems = PoItem::whereIn('id', $ids)->get();
        foreach ($poItems as $poItem) {
            $poItem->avail_order_qty = $poItem->order_qty ?? 0;
            $poItem->available_qty = ((($poItem->order_qty ?? 0) - ($poItem->short_close_qty ?? 0)) - ($poItem->ge_qty ?? 0));
        }
        $uniquePoIds = $poItems->pluck('purchase_order_id')->unique()->toArray();
    }

    $locations = InventoryHelper::getAccessibleLocations('stock');
    $pos = PurchaseOrder::whereIn('id', $uniquePoIds)->get();

    $purchaseData = PurchaseOrder::whereIn('id', $uniquePoIds)
        ->with(['items' => function ($query) use ($ids) {
            $query->whereIn('id', $ids);
        }])
        ->get();

    $purchaseOrder = PurchaseOrder::whereIn('id', $uniquePoIds)->first();

    $finalExpenses = [];
    $poExpenses = PurchaseOrder::whereIn('id', $uniquePoIds)
        ->with(['headerExpenses' => function ($query) {
            $query->where('ted_level', 'H');
        }])
        ->get()
        ->keyBy('id');

    $selectedPoItemValues = PoItem::whereIn('id', $ids)
        ->select('purchase_order_id', \DB::raw('SUM(order_qty * rate) as total'))
        ->groupBy('purchase_order_id')
        ->pluck('total', 'purchase_order_id');

    $poValues = PoItem::whereIn('purchase_order_id', $uniquePoIds)
        ->select('purchase_order_id', \DB::raw('SUM(order_qty * rate) as total'))
        ->groupBy('purchase_order_id')
        ->pluck('total', 'purchase_order_id');

    foreach ($poExpenses as $poId => $po) {
        $poValue = $poValues[$poId] ?? 0;
        $selectedPoItemValue = $selectedPoItemValues[$poId] ?? 0;

        foreach ($po->headerExpenses as $expense) {
            $perc = $poValue > 0 ? ($expense->ted_amount / $poValue) * 100 : 0;
            $amount = number_format(($selectedPoItemValue * $perc / 100), 2);

            $finalExpenses[] = [
                'id' => $expense->id,
                'purchase_order_id' => $poId,
                'ted_id' => $expense->ted_id,
                'ted_name' => $expense->ted_name,
                'ted_amount' => $amount,
                'ted_perc' => round($perc, 4),
            ];
        }
    }

    $vendorId = $pos->pluck('vendor_id')->unique();
    if ($vendorId->count() > 1) {
        return response()->json([
            'data' => ['pos' => ''],
            'status' => 422,
            'message' => "You can not select multiple vendors of PO items at a time."
        ]);
    } else {
        $vendor = Vendor::find($vendorId->first());
    }

    $html = view('procurement.gate-entry.partials.po-item-row', [
        'pos' => $pos,
        'type' => $type,
        'poItems' => $poItems,
        'locations' => $locations,
        'moduleType' => $moduleType,
        'purchaseData' => $purchaseData
    ])->render();

    return response()->json([
        'data' => [
            'pos' => $html,
            'vendor' => $vendor,
            'vendorAsn' => $vendorAsn,
            'moduleType' => $moduleType,
            'finalExpenses' => $finalExpenses,
            'purchaseOrder' => $purchaseOrder,
        ],
        'status' => 200,
        'message' => "fetched!"
    ]);
}
