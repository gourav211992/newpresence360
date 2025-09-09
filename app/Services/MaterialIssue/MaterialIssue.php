<?php
namespace App\Services\MaterialIssue;

use App\Helpers\CommonHelper;
use App\Helpers\ConstantHelper;
use App\Lib\Services\WHM\MaterialIssueWhmJob;
use App\Models\Configuration;
use App\Models\ErpMaterialIssueHeader;
use App\Helpers\Inventory\MaterialIssue\Constants as MIConstants;
use App\Helpers\Inventory\StockReservation;
use App\Helpers\InventoryHelper;

class MaterialIssue
{
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
            //Issue - Picking Job
            (new MaterialIssueWhmJob)->createJob($mi->id,'App\Models\ErpMaterialIssueHeader', CommonHelper::PICKING);
        }
    }

    public function maintainStockLedger(ErpMaterialIssueHeader $materialIssue) : string
    {
        $items = $materialIssue->items;
        //Seperate Issue and Receive Item Ids
        $itemIds = $items -> pluck('id') -> toArray();
        $issueDetailIds = $itemIds;
        $receiptDetailIds = $itemIds;
        //UIC Scan enabled - Create Job, Reserve Stock (No Issue/ Receive)
        if ($materialIssue -> enforce_uic_scanning == 'yes')
        {
            $stockReservation = StockReservation::stockReservation(ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME, $materialIssue -> id, $items);
            if ($stockReservation['status'] == 'error') {
                return $stockReservation['message'];
            }
            return "";
        }
        //Now Issue first
        $issueRecords = InventoryHelper::settlementOfInventoryAndStock($materialIssue->id, $issueDetailIds, ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME, $materialIssue->document_status, 'issue');
         if ($issueRecords['status'] == 'error') {
            return $issueRecords['message'];
        } else {
            return "";
        }
        //Now Recieve
        $receivableIssueTypes = [MIConstants::LOCATION_TRANSFER, MIConstants::SUB_LOCATION_TRANSFER, MIConstants::SUB_CONTRACTING, MIConstants::JOB_ORDER];
        if (in_array($materialIssue->issue_type, $receivableIssueTypes)) {
            $receiveRecords = InventoryHelper::settlementOfInventoryAndStock($materialIssue->id, $receiptDetailIds, ConstantHelper::MATERIAL_ISSUE_SERVICE_ALIAS_NAME, $materialIssue->document_status, 'receipt');
            if ($receiveRecords['status'] == 'error') {
                return $receiveRecords['message'];
            } else {
                return "";
            }
        }
        return "";
    }
}
