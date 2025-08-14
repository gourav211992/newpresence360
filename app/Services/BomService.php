<?php 
namespace App\Services;

use App\Helpers\ConstantHelper;
use App\Models\Bom;
use App\Models\BomDetail;
use App\Models\ErpSoItem;

class BomService
{
    protected $bomCache = [];
    public function getRawMaterialBreakdown(array $soItemIds, string $returnType = 'both')
    {
        $result = [];
        foreach ($soItemIds as $soItemId) {
            $soItem = ErpSoItem::findOrFail($soItemId);
            $bom = $this->getBom($soItem?->item_id); 
            if (!$bom) {
                throw new \Exception("No BOM found for item {$soItem->item_id} with UOM {$soItem->uom_id}");
            }
            $rawMaterials = [];
            $semiFinishedGoods = [];
            $referenceMap = [];
            $storeName =  $soItem?->header?->store?->store_name ?? '';
            $storeId =  $soItem?->header?->store_id;
            $docNo = $soItem?->header?->full_document_number ?? '';
            $docDate = $soItem?->header?->document_date ?? '';
            $avlQty = $soItem->getStockBalanceQty($storeId);
            $semiFinishedGoods['fg'] = [
                'so_id'         => $soItem?->sale_order_id,
                'level'         => 0,
                'parent_bom_id' => null,
                'bom_id'        => $bom->id,
                'item_name'     => $soItem->item->item_name ?? '',
                'item_id'       => $soItem->item_id,
                'item_code'     => $soItem->item->item_code ?? '',
                'uom_id'        => $soItem->uom_id,
                'uom_name'      => $soItem?->uom?->name,
                'attribute'     => $soItem->item_attributes_array(),
                'total_qty'     => $soItem->inventory_uom_qty,
                'children'      => []
            ];
            
            $this->traverseBom($bom->id, $soItem->inventory_uom_qty, $rawMaterials, $semiFinishedGoods, 1, $referenceMap, $storeName, $storeId, $docNo, $docDate,$soItem->id);
            if ($returnType === 'both' || $returnType === 'semi') {
                $semiFinishedGoods['fg']['bom_id'] = $bom->id;
                $semiFinishedGoods['fg']['store_name'] = $storeName;
                $semiFinishedGoods['fg']['store_id'] = $storeId;
                $semiFinishedGoods['fg']['doc_no'] = $docNo;
                $semiFinishedGoods['fg']['doc_date'] = $docDate;
                $semiFinishedGoods['fg']['avl_qty'] = $avlQty;
                $semiFinishedGoods['fg']['main_so_item'] = 1;
                $result[$soItemId]['semi_finished_goods'] = $semiFinishedGoods;

            }
            if ($returnType === 'both' || $returnType === 'raw') {
                $result[$soItemId]['raw_materials'] = $rawMaterials;
            }
        }
        return $result;
    }

    public function traverseBom($bomId, $multiplier, &$rawMaterials, &$semiFinishedGoods, $level, &$referenceMap, $storeName, $storeId, $docNo, $docDate,$soItemId)
    {
        if (!isset($this->bomCache[$bomId])) {
            $this->bomCache[$bomId] = BomDetail::where('bom_id', $bomId)->get();
        }
        foreach ($this->bomCache[$bomId] as $detail) {
            $childBom = $this->getBom($detail->item_id);
            $componentQty = $detail->qty * $multiplier;
            $key = $detail->item_id . '-' . $detail->uom_id;
            if ($childBom) {
                $avlQty = $detail->getStockBalanceQty($storeId);
                $childNode = [
                    'level'         => $level,
                    'parent_bom_id' => $bomId,
                    'item_name'     => $detail->item->item_name,
                    'item_id'       => $detail->item_id,
                    'item_code'     => $detail->item_code,
                    'uom_id'        => $detail->uom_id,
                    'uom_name'      => $detail?->uom?->name,
                    'total_qty'     => $componentQty,
                    'bom_id'        => $childBom->id,
                    'attribute'     => $detail->item_attributes_array(),
                    'children'      => [],
                    'store_name'    => $storeName,
                    'store_id'      => $storeId,
                    'doc_no'        => $docNo,
                    'doc_date'      => $docDate,
                    'avl_qty'       => $avlQty,
                    'main_so_item'  => 0,
                    'so_item_id'    => $soItemId,
                ];
                $referenceMap[$key] = $childNode;
                if (isset($referenceMap[$bomId])) {
                    $referenceMap[$bomId]['children'][$key] = &$referenceMap[$key];
                } else {
                    $semiFinishedGoods['fg']['children'][$key] = &$referenceMap[$key];
                }
                $referenceMap[$childBom->id] = &$referenceMap[$key];
                $this->traverseBom($childBom->id, $componentQty, $rawMaterials, $semiFinishedGoods, $level + 1, $referenceMap, $storeName, $storeId,$docNo, $docDate,$soItemId);
            } else {
                // if (isset($rawMaterials[$key])) {
                //     $rawMaterials[$key]['total_qty'] += $componentQty;
                // } else {
                //     $rawMaterials[$key] = [
                //         'level'         => $level,
                //         'parent_bom_id' => $bomId,
                //         'item_name'     => $detail->item->item_name,
                //         'item_id'       => $detail->item_id,
                //         'item_code'     => $detail->item_code,
                //         'uom_id'        => $detail->uom_id,
                //         'attribute'     => $detail->selected_item_attributes_array(),
                //         'total_qty'     => $componentQty,
                //     ];
                // }
            }
        }
    }
    
    public function getBom($itemId = null)
    {
        $bom = Bom::withDefaultGroupCompanyOrg()
            ->where('item_id', $itemId)
            ->where('type', ConstantHelper::BOM_SERVICE_ALIAS)
            ->whereIn('document_status', ConstantHelper::DOCUMENT_STATUS_APPROVED)
            ->first();
        return $bom;
    }

}
