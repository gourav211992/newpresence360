<table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
    <tr>
        <td rowspan="2" style="border: 1px solid #000; padding: 3px; width: 40%; vertical-align: top;">
            <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                <tr>
                    <td colspan="3" style="font-weight: 900; font-size: 13px; padding-bottom: 3px;">
                        Product Details:
                    </td>
                </tr>
                <tr>
                    <td style="padding-top: 3px;">Name : </td>
                    <td style="padding-top: 3px; font-weight: 700;">{{ @$bom?->item?->item_name }}</td>
                </tr>
                <tr>
                    <td style="padding-top: 3px;">Code : </td>
                    <td style="padding-top: 3px; font-weight: 700;">{{ @$bom?->item?->item_code }}</td>
                </tr>
                @if ($bom?->item?->itemAttributes->count())
                    @foreach ($bom?->item?->itemAttributes as $index => $attribute)
                        @php
                            $headerAttribute = $bom
                                ->bomAttributes()
                                ->where('attribute_name', $attribute->attribute_group_id)
                                ->first();
                        @endphp
                        @if (isset($headerAttribute))
                            <tr>
                                <td style="padding-top: 3px;">
                                    {{ $headerAttribute?->headerAttribute?->name ?? 'NA' }}:</td>
                                <td style="padding-top: 3px;">
                                    {{ $headerAttribute?->headerAttributeValue?->value }}
                                </td>
                            </tr>
                        @endif
                        @if (!$loop->last)
                            <br />
                        @endif
                    @endforeach
                @endif
                <tr>
                    <td style="padding-top: 3px;">UOM:</td>
                    <td style="padding-top: 3px;">
                        {{ @$bom?->item?->uom?->name }}
                    </td>
                </tr>
                @if (@$bom?->customer)
                    <tr>
                        <td style="padding-top: 3px;">Customer:</td>
                        <td style="padding-top: 3px;">
                            {{ @$bom?->customer?->company_name }}
                        </td>
                    </tr>
                @endif
            </table>
        </td>
        <td rowspan="2"
            style="border: 1px solid #000; padding: 3px; border-left: none; vertical-align: top; width: 40%;">
            <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                <tr>
                    <td colspan="3" style="font-weight: 900; font-size: 13px; padding-bottom: 3px;">
                        Specifications:
                    </td>
                </tr>
                @if (isset($specifications))
                    @foreach ($specifications as $specification)
                        <tr>
                            <td style="padding-top: 3px;">{{ $specification?->specification_name }}: </td>
                            <td style="padding-top: 3px;">
                                {{ $specification->value }}
                            </td>
                        </tr>
                    @endforeach
                @endif
            </table>
        </td>
        <td
            style="border: 1px solid #000; padding: 3px;float: right; border-left: none;border-bottom: none; vertical-align: top; width: 20%;">
            <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="padding-top: 3px;"><b>Doc Date:</b></td>
                    @if ($bom->document_date)
                        <td style="font-weight: 900;padding-top: 3px;">
                            {{ date('d-M-y', strtotime($bom->document_date)) }}
                        </td>
                    @endif
                </tr>
                <tr>
                    <td style="padding-top: 3px;"><b>Series:</b></td>
                    <td style="font-weight: 900;padding-top: 3px;">
                        {{ @$bom?->book?->book_code }}
                    </td>
                </tr>
                <tr>
                    <td style="padding-top: 3px;"><b>Doc No:</b></td>
                    <td style="padding-top: 3px;font-weight: 900;">
                        {{ @$bom->document_number }}
                    </td>
                </tr>
                <tr>
                    <td style="padding-top: 3px"><b style="font-weight: 900;">Status:</b></td>
                    <td style="padding-top: 3px">
                        <span class="{{ $docStatusClass }}">
                            {{ $bom->display_status }}
                        </span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td
            style="border: 1px solid #000; padding: 3px; border-left: none; vertical-align: top; width: 35%; border-top: none;">
            <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                @if ($bom?->display_production_type)
                    <tr>
                        <td style="padding-top: 3px;">
                            <b>Production Type :</b>
                        </td>
                        <td style="padding-top: 3px;">{{ @$bom->display_production_type }}</td>
                    </tr>
                @endif
            </table>
        </td>
    </tr>
</table>

<table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
    <tr>
        <td
            style="padding: 2px; border: 1px solid #000; border-top: none; background: #80808070; text-align: center; font-weight: bold;width: 15px;">
            #
        </td>
        <td
            style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center; width: 98px;">
            Station / Section
        </td>
        <td
            style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center; width: 150px;">
            Item
        </td>
        <td
            style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center; width: 30px;">
            UOM
        </td>
        <td
            style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center; width: 50px;">
            Qty
        </td>
        @if ($consumption_method)
            <td
                style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center; width: 66px;">
                Norms
            </td>
        @endif
        @if ($canView)
            <td
                style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center; width: 50px;">
                Cost
            </td>
            <td
                style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center; width: 70px;">
                Item Value
            </td>
            <td
                style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: center; width: 50px;">
                Overhead
            </td>
            <td
                style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; background: #80808070; text-align: center; width: 70px;">
                Total Cost
            </td>
        @endif
    </tr>
    @php
        $item_total = 0;
        $over_total = 0;
        $allChildBoms = [];
    @endphp
    @foreach ($bom->bomItems as $key => $bomItem)
        @php
            $itemBom = \App\Helpers\ItemHelper::getBomIdNumbersOnItem($bomItem->item_id);
            $recursiveBomArr = \App\Helpers\ItemHelper::getRecursiveBomIdNumbersOnItem($bomItem->item_id);
            $allChildBoms = array_merge($allChildBoms, array_keys($recursiveBomArr));
        @endphp
        <tr>
            <td
                style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: center; word-break: break-word;">
                {{ $key + 1 }}</td>
            <td
                style="vertical-align: top; padding:10px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none; word-break: break-word;">
                {{ @$bomItem->station_name }} <br />
                {{ @$bomItem?->section_name }}<br />
                {{ @$bomItem?->sub_section_name }}
            </td>
            <td
                style="vertical-align: top; padding:10px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none;word-break: break-word;">
                <b>{{ @$bomItem?->item?->item_name }}</b><br />
                <b>{{ @$bomItem->item_code }}</b><br />
                @if ($bomItem?->item?->itemAttributes->count())
                    @foreach ($bomItem?->item?->itemAttributes as $index => $attribute)
                        @php
                            $headerAttribute = $bomItem
                                ->attributes()
                                ->where('attribute_name', $attribute->attribute_group_id)
                                ->first();
                        @endphp
                        @if (isset($headerAttribute))
                            {{ $headerAttribute?->headerAttribute?->name ?? 'NA' }}:
                            {{ $headerAttribute?->headerAttributeValue?->value }}
                            @if (!$loop->last)
                                <br />
                            @endif
                        @endif
                    @endforeach
                @endif
                @if ($bomItem->vendor_id)
                    <br /> Vendor: {{ $bomItem?->vendor?->company_name }}
                @endif
                @if ($bomItem->remark)
                    <br /> Remarks: {{ $bomItem->remark }}
                @endif
                @if (!empty($recursiveBomArr))
                    <br /> <b>Child BOM:</b>
                    #{{ @$itemBom->document_number }}
                    {{-- [ #{{ implode(', #', $recursiveBomArr) }} ] --}}
                @endif
            </td>
            <td
                style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: center;word-break: break-word;">
                {{ @$bomItem?->item?->uom?->name }}
            </td>
            <td
                style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;word-break: break-word;">
                {{ number_format(@$bomItem->qty, 4) }}
            </td>
            @if ($consumption_method && $bomItem?->norm)
                <td
                    style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;word-break: break-word;">
                    QPU:{{ number_format($bomItem->norm->qty_per_unit ?? 0, 4) }}
                    PCS:{{ number_format($bomItem->norm->total_qty ?? 0, 4) }}
                    STD:{{ number_format($bomItem->norm->std_qty ?? 0, 4) }}
                    NRM:{{ number_format($bomItem->norm->norms ?? 0, 4) }}
                </td>
            @endif

            @php
                $total = floatval($bomItem->qty) * floatval($bomItem->item_cost);
                $total = number_format($total, 4, '.', '');
            @endphp
            @if ($canView)
                <td
                    style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;word-break: break-word;">
                    {{ $canView ? number_format(floatval($bomItem->item_cost), 4, '.', '') : '' }}
                </td>
                <td
                    style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;word-break: break-word;">
                    {{ $canView ? number_format($total, 2) : '0.00' }}
                </td>
                <td
                    style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: right;word-break: break-word;">
                    {{ $canView ? number_format($bomItem->overhead_amount, 2) : '0.00' }}
                </td>
                <td
                    style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none;  text-align: right;word-break: break-word;">
                    {{ $canView ? number_format($total + $bomItem->overhead_amount, 2) : '0.00' }}
                </td>
            @endif
        </tr>
        @php
            $item_total += $total;
            $over_total += $bomItem->overhead_amount;
        @endphp
    @endforeach
    @if ($canView)
        <tr>
            <td colspan="{{ 6 + $consumption_method }}"
                style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: 1px solid #000; text-align: center;">
            </td>
            <td
                style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;word-break: break-word;">
                {{ $canView ? number_format($item_total, 2) : '0.00' }}
            </td>
            <td
                style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;word-break: break-word;">
                {{ $canView ? number_format($over_total, 2) : '0.00' }}
            </td>
            <td
                style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: right;word-break: break-word;">
                {{ $canView ? number_format($item_total + $over_total, 2) : '0.00' }}
            </td>
        </tr>
    @endif
</table>

<table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
    @if ($canView)
        <tr>
            <td style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
                <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                    <tr>
                        <td> <b>Total Value (In Words)</b> <br>
                            {{ $canView ? @$amountInWords : '0.00' }}
                        </td>
                    </tr>
                </table>
            </td>
            <td style="padding: 3px; border: 1px solid #000; border-top: none; border-left: none; vertical-align: top;">
                <table style="width: 100%; margin-bottom: 0px; margin-top: 10px;" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="text-align: right;">
                            <b>Total Item Cost:</b>
                        </td>
                        <td style="text-align: right;">
                            {{ $canView ? number_format($item_total + $over_total, 2) : '0.00' }}
                        </td>
                    </tr>

                    @if ($bom->bomOverheadItems->count() && $canView)
                        @php
                            $previousLevel = null;
                            $levelTotal = 0;
                            $items = $bom->bomOverheadItems;
                            $count = $items->count();
                            $tempTotal = $item_total + $over_total;
                        @endphp

                        @foreach ($items as $index => $bomOverheadItem)
                            @php
                                $currentLevel = $bomOverheadItem->level;
                                $nextLevel = $items[$index + 1]->level ?? null;
                                $levelChanged = $currentLevel !== $nextLevel;
                                $levelTotal += $bomOverheadItem->overhead_amount;
                                $isLastItem = $index === $count - 1;
                            @endphp
                            <tr>
                                <td style="text-align: right; padding-top: 3px;">
                                    {{ $bomOverheadItem->overhead_description ?? 'Overhead' }}
                                    @if (floatval($bomOverheadItem->overhead_perc))
                                        ({{ $bomOverheadItem->overhead_perc }}%)
                                    @endif :
                                </td>
                                <td style="text-align: right; padding-top: 3px;">
                                    {{ number_format($bomOverheadItem->overhead_amount, 2) }}
                                </td>
                            </tr>

                            @if ($levelChanged)
                                @php
                                    $tempTotal += $levelTotal;
                                @endphp
                                <tr>
                                    <td style="text-align: right; font-weight: bold; padding-top: 3px;">
                                        @if ($isLastItem)
                                            Grand Total:
                                        @else
                                            Sub Total:
                                        @endif
                                    </td>
                                    <td
                                        style="text-align: right; font-weight: bold; padding-top: 3px; border-top: 1px solid #000;">
                                        {{ number_format(floatval($tempTotal), 2) }}
                                    </td>
                                </tr>
                                @php $levelTotal = 0; @endphp
                            @endif
                        @endforeach
                    @endif

                    @if (!$bom->bomOverheadItems->count())
                        <tr>
                            <td style="text-align: right; padding-top: 3px;">
                                <b>Grand Total:</b>
                            </td>
                            <td style="text-align: right; padding-top: 3px;">
                                {{ $canView ? number_format($totalAmount, 2) : '0.00' }}
                            </td>
                        </tr>
                    @endif
                </table>
            </td>
        </tr>
    @endif

    @if ($bom?->bomInstructions?->count())
        <tr>
            <td colspan="2"
                style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">

                <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                    <tr>
                        <td style="font-weight: bold; font-size: 13px;padding-bottom:10px"> <b>Instructions
                                :</b></td>
                    </tr>
                    <tr>
                        <td
                            style="width:25%;font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: left;">
                            Station
                        </td>
                        @if (isset($sectionRequired) && $sectionRequired)
                            <td
                                style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: left;">
                                Section
                            </td>
                        @endif
                        @if (isset($subSectionRequired) && $subSectionRequired)
                            <td
                                style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: left;">
                                Sub Section
                            </td>
                        @endif
                        <td
                            style="font-weight: bold; padding: 2px; border: 1px solid #000; border-top: none; border-left: none; background: #80808070; text-align: left;">
                            Instruction
                        </td>
                    </tr>
                    @foreach ($bom->bomInstructions as $key => $bomInstruction)
                        <tr>
                            <td
                                style=" vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: left;">
                                {{ $bomInstruction?->station?->name }}
                            </td>
                            @if (isset($sectionRequired) && $sectionRequired)
                                <td
                                    style="vertical-align: top; padding:10px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none;">
                                    {{ @$bomInstruction?->section?->name }}
                                </td>
                            @endif
                            @if (isset($subSectionRequired) && $subSectionRequired)
                                <td
                                    style="vertical-align: top; padding:10px 3px; text-align:left; border: 1px solid #000; border-top: none; border-left: none;">
                                    {{ $bomInstruction?->subSection?->name }}
                                </td>
                            @endif
                            <td
                                style="vertical-align: top; padding:10px 3px; border: 1px solid #000; border-top: none; border-left: none; text-align: left;">
                                {!! $bomInstruction?->instructions !!}
                            </td>
                        </tr>
                    @endforeach
                </table>
            </td>
        </tr>
    @endif
    <tr>
        <td colspan="1"
            style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
            <table style="width: 100%; height: 100px; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                <tr>
                    <td align="center" valign="middle">
                        @foreach ($bom->getDocuments() as $attachment)
                            @if (Str::contains($attachment->mime_type, 'image'))
                                <img src="{{ $bom->getPdfDocumentUrl($attachment) }}"
                                    alt="Image : {{ $attachment->name }}"
                                    style="max-width: 100%; max-height: 100px;">
                            @endif
                        @endforeach
                    </td>
                </tr>
            </table>
        </td>
        <td colspan="1"
            style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; vertical-align: top;">
            <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="font-weight: bold; font-size: 13px;"> <b>Remark :</b></td>
                </tr>
                <tr>
                    <td>
                        <div style="min-height: 80px;">
                            {{ $bom->remarks }}
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td
            style="padding: 3px; border: 1px solid #000; width: 50%; border-top: none; border-right: none; vertical-align: top;">
            <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="padding-top: 5px;">Created By :</td>
                    <td style="padding-top: 5px;">
                        {{ @$bom?->createdBy?->name }}
                    </td>
                </tr>
                <tr>
                    <td style="padding-top: 5px;">Printed By :</td>
                    <td style="padding-top: 5px;">
                        {{ @$user?->name ?? '' }}
                    </td>
                </tr>
            </table>
        </td>
        <td style="padding: 3px; border: 1px solid #000; border-top: none; border-left: none; vertical-align: bottom;">
            <table style="width: 100%; margin-bottom: 0px;" cellspacing="0" cellpadding="0">
                <tr>
                    <td style="text-align: center; padding-bottom: 20px;">FOR
                        {{ Str::ucfirst(@$organization->name) }} </td>
                </tr>
                <tr>
                    <td>This is a computer generated document hence not require any signature. </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="2"
            style=" border: 1px solid #000; padding: 5px; text-align: center; font-size: 12px; border-top: none; text-align: center;">
            Regd. Office:{{ $organizationAddress?->display_address ?? '' }}
        </td>
    </tr>
</table>
