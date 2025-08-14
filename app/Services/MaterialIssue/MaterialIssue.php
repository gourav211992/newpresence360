<?php
namespace App\Services\MaterialIssue;

use App\Helpers\CommonHelper;
use App\Helpers\ConstantHelper;
use App\Lib\Services\WHM\WhmJob;
use App\Models\Configuration;
use App\Models\ErpMaterialIssueHeader;
use App\Models\ErpMiItem;
use App\Models\ErpPwoItem;
use App\Models\JobOrder\JoItem;
use App\Models\JobOrder\JoProduct;
use App\Models\MoItem;
use App\Models\PiItem;

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

    public function createWhmJob(ErpMaterialIssueHeader $mi, $user)
    {
        // Get configuration detail
        $orgEnforceUicScanning = Configuration::where('type','organization')
            ->where('type_id', $user->organization_id)
            ->where('config_key', CommonHelper::ENFORCE_UIC_SCANNING)
            ->first();
        
        //If MI is approved
        if(in_array($mi->document_status, ConstantHelper::DOCUMENT_STATUS_APPROVED) 
            && $orgEnforceUicScanning && strtolower($orgEnforceUicScanning->config_value) === 'yes')
        {
            //Issue Job
            //Picking Job
            if ($mi -> from_sub_store -> is_warehouse_required) {
                (new WhmJob)->createJob($mi->id,'App\Models\ErpMaterialIssueHeader', CommonHelper::PICKING);
            //Dispatch Job
            } else if ($mi -> uic_scan_for_issue === "yes" || $mi -> to_sub_store -> is_warehouse_required) { 
                (new WhmJob)->createJob($mi->id,'App\Models\ErpMaterialIssueHeader', CommonHelper::DISPATCH);
            }
            //Receive Job
            //Putaway Job
            if ($mi -> to_sub_store -> is_warehouse_required) {
                (new WhmJob)->createJob($mi->id,'App\Models\ErpMaterialIssueHeader', CommonHelper::PUTAWAY);
            }
        }
    }
}
