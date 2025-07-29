<?php
namespace App\Services\MaterialIssue;

use App\Models\ErpMiItem;

class MaterialIssue
{
    public function deleteItem(ErpMiItem $miItem) : array
    {
        // MFG ORDER
        if (isset($miItem -> mo_item_id)) {
            //Back update in MO ITEM
            $moItem = MoItem::find($miItem -> mo_item_id);
            if (isset($moItem)) {
                $moItem -> mi_qty = $moItem -> mi_qty - $miItem -> issue_qty;
                $moItem -> save();
            }
        }
        //PWO
        if (isset($miItem -> pwo_item_id)) {
            //Back update in PWO ITEM
            $pwoItem = ErpPwoItem::find($miItem -> pwo_item_id);
            if (isset($pwoItem)) {
                $pwoItem -> mi_qty = $pwoItem -> mi_qty - $miItem -> issue_qty;
                $pwoItem -> save();
            }
        }
        //PURCHASE INDENT
        if (isset($miItem -> pi_item_id)) {
            //Back update in PI ITEM
            $piItem = PiItem::find($miItem -> pi_item_id);
            if (isset($piItem)) {
                $piItem -> mi_qty = $piItem -> mi_qty - $miItem -> issue_qty;
                $piItem -> save();
            }
        }
        //JO (SUB CONTRACTING)
        if (isset($miItem -> jo_item_id)) {
            //Back update in JO ITEM
            $joItem = JoItem::find($miItem -> jo_item_id);
            if (isset($joItem)) {
                $joItem -> mi_qty = $joItem -> mi_qty - $miItem -> issue_qty;
                $joItem -> save();
            }
        }
        //JO (JOB WORK)
        if (isset($miItem -> jo_product_id)) {
            //Back update in JO ITEM
            $joProduct = JoProduct::find($miItem -> jo_product_id);
            if (isset($joProduct)) {
                $joProduct -> mi_qty = $joProduct -> mi_qty - $miItem -> issue_qty;
                $joProduct -> save();
            }
        }
        // Delete all Attributes
        $miItem->attributes()->delete();
        //Final item delete
        $miItem->delete();
        return ['status' => 'success', 'message' => ''];
    }
}
