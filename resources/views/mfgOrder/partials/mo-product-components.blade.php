<div class="card">
    <div class="card-body customernewsection-form">
        <div class="border-bottom mb-2 pb-25">
            <div class="row">
                <div class="col-md-6">
                    <div class="newheader ">
                        <h4 class="card-title text-theme">Product Components :
                            <span class="badge rounded-pill badge-light-primary"> {{ $item->item_name }} - {{ $item->item_code }}</span>
                        </h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="table-responsive">
            <table id="itemTable" class="table myrequesttablecbox table-striped po-order-detail custnewpo-detail border newdesignerptable newdesignpomrnpad" data-json-key="components_json" data-row-selector="tr[id^='row_']">
                <thead>
                    <tr>
                        <th  width="150px">Item Code</th>
                        <th  width="200px">Item Name	</th>
                        <th  width="100px">Attributes</th>
                        <th  width="100px">UOM</th>
                        <th class="text-end" width="150px">BOM Qty.</th>
                        <th class="text-end" width="150px">Required Qty.</th>
                        <th width="150px" class="text-end">Available Stock</th>
                    </tr>
                </thead>
                <tbody class="">
                    @foreach($bomDetails as $bomDetail)
                    <tr>
                        <td>{{ $bomDetail->item?->item_code }}</td>
                        <td>{{ $bomDetail->item?->item_name }}</td>
                        <td>
                            @foreach(App\Helpers\ItemHelper::getItemAttributesWithValues($bomDetail->attributes) as $attribute)
                                <span class="badge rounded-pill badge-light-primary">
                                    <strong>{{ @$attribute['attribute_name'] }}</strong>: {{ @$attribute['attribute_value'] }}
                                </span>
                            @endforeach
                        </td>
                        <td>{{ $bomDetail->uom?->name }}</td>
                        <td class="text-end">{{ $bomDetail->bom_qty }}</td>
                        <td class="text-end">{{ $bomDetail->qty }}</td>
                        @php
                            $selectedAttr = collect($bomDetail->attributes)->pluck('attribute_value')->toArray();
                            $availableStocks = App\Helpers\InventoryHelper::totalInventoryAndStock(
                                $bomDetail->item_id,
                                $selectedAttr,
                                $bomDetail->uom_id,
                                $storeId,
                            );
                        @endphp
                        <td class="text-end {{ $bomDetail->qty > $availableStocks['confirmedStocks'] ? 'text-danger' : 'text-success'}}">
                            <strong>{{ @$availableStocks['confirmedStocks'] }}</strong>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
