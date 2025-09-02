<?php


namespace App\Helpers\GenericImport;

use App\Helpers\ConstantHelper;

class GenericImportHelper
{
    public static function importConfigByAlias(string $alias): array
    {
        return match ($alias) {
            ConstantHelper::SO_SERVICE_ALIAS => [
                'type' => ConstantHelper::SO_SERVICE_ALIAS,
                'importer' => \App\Imports\GenericItemImport::class,
                'sample_file_prefix' => 'so_item',
                'route' => 'import.save',
                'view' => 'salesOrder.edit',
            ],
            ConstantHelper::PSV_SERVICE_ALIAS => [
                'type' => ConstantHelper::PSV_SERVICE_ALIAS,
                'importer' => \App\Imports\GenericItemImport::class,
                'sample_file_prefix' => 'psv_item',
                'route' => 'import.save',
                'view' => 'psv.edit',
            ],
            ConstantHelper::PO_SERVICE_ALIAS => [
                'type' => ConstantHelper::PO_SERVICE_ALIAS,
                'importer' => \App\Imports\GenericItemImport::class,
                'sample_file_prefix' => 'po_item',
                'route' => 'import.save',
                'view' => 'po.edit',
            ],
            ConstantHelper::PI_SERVICE_ALIAS => [
                'type' => ConstantHelper::PI_SERVICE_ALIAS,
                'importer' => \App\Imports\GenericItemImport::class,
                'sample_file_prefix' => 'pi_item',
                'route' => 'import.save',
                'view' => 'po.edit',
            ],
            ConstantHelper::RC_SERVICE_ALIAS => [
                'type' => ConstantHelper::RC_SERVICE_ALIAS,
                'importer' => \App\Imports\GenericItemImport::class,
                'sample_file_prefix' => 'rc_item',
                'route' => 'import.save',
                'view' => 'rate.contract.edit',
            ],
            default => throw new \Exception("Invalid alias"),
        };
    }

    public static function getHeaderMap(string $alias): array
    {
        return match ($alias) {
            ConstantHelper::SO_SERVICE_ALIAS => [
                'item_code'     => 'Item Code',
                'item_name'     => 'Item Name',
                'hsn_code'      => 'HSN Code',
                'uom_code'      => 'UOM Code',
                'order_qty'     => 'Order Qty',
                'rate'          => 'Rate',
                'delivery_date' => 'Delivery Date',
                'remarks'       => 'Remarks',
                'attribute'     => 'Attribute',
            ],
            ConstantHelper::PSV_SERVICE_ALIAS => [
                'item_code'     => 'Item Code',
                'item_name'     => 'Item Name',
                'attribute'     => 'Attribute',
                'uom_code'      => 'UOM Code',
                'physical_qty'  => 'Physical Qty',
                'remarks'       => 'Remarks',
            ],
            ConstantHelper::PI_SERVICE_ALIAS => [
                'item_code'     => 'Item Code',
                'item_name'     => 'Item Name',
                'attribute'     => 'Attribute',
                'uom_code'      => 'UOM Code',
                'required_qty'  => 'Required Qty',
                'vendor_name'  => 'Vendor Name',
                'remarks'       => 'Remarks',
            ],
            ConstantHelper::PO_SERVICE_ALIAS => [
                'item_code'     => 'Item Code',
                'item_name'     => 'Item Name',
                'attribute'     => 'Attribute',
                'uom_code'      => 'UOM Code',
                'qty'           => 'Qty',
                'rate'          => 'Rate',
                'delivery_date' => 'Delivery Date',
                'remarks'       => 'Remarks',
            ],
             ConstantHelper::RC_SERVICE_ALIAS => [
                'item_code'     => 'Item Code',
                'item_name'     => 'Item Name',
                'attribute'     => 'Attribute',
                'uom_code'      => 'UOM Code',
                'MOQ'           => 'MOQ',
                'from_qty'      => 'From Qty',
                'to_qty'        => 'To Qty',
                'rate'          => 'Rate',
                'lead_time'     => 'Lead Time',
                'effective_from'     => 'Effective From',
                'effective_upto'     => 'Effective Upto',
                'remarks'       => 'Remarks',
            ],
            default => [],
        };
    }
}
