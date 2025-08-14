<?php

namespace App\Helpers\TransactionReport;

use App\Helpers\ConstantHelper;
use App\Models\Legal;

class bomReportHelper
{
    public static function canViewItemCost(string $parentUrl = 'bil-of-material'): bool
    {
        $servicesAliasParam = $parentUrl === 'quotation-bom'
            ? ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS
            : ConstantHelper::BOM_SERVICE_ALIAS;

        $user = request()->user();

        if ($servicesAliasParam === ConstantHelper::COMMERCIAL_BOM_SERVICE_ALIAS) {
            return $user?->hasPermission('quotation_bom.item_cost_view') ?? false;
        }

        if ($servicesAliasParam === ConstantHelper::BOM_SERVICE_ALIAS) {
            return $user?->hasPermission('production_bom.item_cost_view') ?? false;
        }
        return false;
    }

    public static function getBomTableHeaders(string $parentUrl = 'bil-of-material'): array
    {
        $canView = self::canViewItemCost($parentUrl);
        $headers = [
            ['name' => 'S. No', 'field' => 'DT_RowIndex', 'header_class' => 'numeric-alignment', 'column_class' => 'text-end pe-2_5', 'header_style' => '', 'column_style' => ''],
            ['name' => 'Series', 'field' => 'book_code', 'header_class' => '', 'column_class' => 'no-wrap', 'header_style' => '', 'column_style' => ''],
            ['name' => 'BOM No', 'field' => 'document_number', 'header_class' => '', 'column_class' => 'no-wrap', 'header_style' => '', 'column_style' => ''],
            ['name' => 'BOM Date', 'field' => 'document_date', 'header_class' => '', 'column_class' => 'no-wrap', 'header_style' => '', 'column_style' => ''],
            ['name' => 'Product Code', 'field' => 'product_code', 'header_class' => '', 'column_class' => 'no-wrap', 'header_style' => '', 'column_style' => ''],
            ['name' => 'Product Name', 'field' => 'product_name', 'header_class' => '', 'column_class' => 'no-wrap', 'header_style' => '', 'column_style' => ''],
            ['name' => 'Product Attributes', 'field' => 'product_attributes', 'header_class' => '', 'column_class' => 'no-wrap', 'header_style' => '', 'column_style' => ''],
            ['name' => 'Product UOM', 'field' => 'product_uom', 'header_class' => '', 'column_class' => 'no-wrap', 'header_style' => '', 'column_style' => ''],
            ['name' => 'Production Type', 'field' => 'production_type', 'header_class' => '', 'column_class' => 'no-wrap', 'header_style' => '', 'column_style' => ''],
            ['name' => 'Production Route', 'field' => 'production_route', 'header_class' => '', 'column_class' => 'no-wrap', 'header_style' => '', 'column_style' => ''],
        ];

        if ($canView) {
            $headers = array_merge($headers, [
                ['name' => 'Total Item Cost', 'field' => 'product_cost', 'header_class' => 'numeric-alignment', 'column_class' => 'text-end pe-2_5', 'header_style' => '', 'column_style' => ''],
                ['name' => 'Overheads', 'field' => 'overhead_amount', 'header_class' => 'numeric-alignment', 'column_class' => 'text-end pe-2_5', 'header_style' => '', 'column_style' => ''],
                ['name' => 'Total Value', 'field' => 'total_cost', 'header_class' => 'numeric-alignment', 'column_class' => 'text-end pe-2_5', 'header_style' => '', 'column_style' => ''],
            ]);
        }

        $headers = array_merge($headers, [
            ['name' => 'Customizable ?', 'field' => 'customizable', 'header_class' => 'numeric-alignment', 'column_class' => 'text-end pe-2_5', 'header_style' => '', 'column_style' => ''],
            ['name' => 'Safety Buffer %', 'field' => 'safety_buffer', 'header_class' => 'numeric-alignment', 'column_class' => 'text-end pe-2_5', 'header_style' => '', 'column_style' => ''],
            ['name' => 'Item Code', 'field' => 'item_code', 'header_class' => '', 'column_class' => 'no-wrap', 'header_style' => '', 'column_style' => ''],
            ['name' => 'Item Name', 'field' => 'item_name', 'header_class' => '', 'column_class' => 'no-wrap', 'header_style' => '', 'column_style' => ''],
            ['name' => 'Item Attributes', 'field' => 'item_attributes', 'header_class' => '', 'column_class' => 'no-wrap', 'header_style' => '', 'column_style' => ''],
            ['name' => 'UOM', 'field' => 'item_uom', 'header_class' => '', 'column_class' => 'no-wrap', 'header_style' => '', 'column_style' => ''],
            ['name' => 'Qty', 'field' => 'item_qty', 'header_class' => 'numeric-alignment', 'column_class' => 'text-end pe-2_5', 'header_style' => '', 'column_style' => ''],
        ]);

        if ($canView) {
            $headers = array_merge($headers, [
                ['name' => 'Cost', 'field' => 'item_cost', 'header_class' => 'numeric-alignment', 'column_class' => 'text-end pe-2_5', 'header_style' => '', 'column_style' => ''],
                ['name' => 'Overheads', 'field' => 'item_overhead', 'header_class' => 'numeric-alignment', 'column_class' => 'text-end pe-2_5', 'header_style' => '', 'column_style' => ''],
                ['name' => 'Item Value', 'field' => 'item_value', 'header_class' => 'numeric-alignment', 'column_class' => 'text-end pe-2_5', 'header_style' => '', 'column_style' => ''],
            ]);
        }

        $headers = array_merge($headers, [
            ['name' => 'Station', 'field' => 'item_station', 'header_class' => '', 'column_class' => 'no-wrap', 'header_style' => '', 'column_style' => ''],
            ['name' => 'Section', 'field' => 'item_section', 'header_class' => '', 'column_class' => 'no-wrap', 'header_style' => '', 'column_style' => ''],
            ['name' => 'Sub Section', 'field' => 'item_sub_section', 'header_class' => '', 'column_class' => 'no-wrap', 'header_style' => '', 'column_style' => ''],
            ['name' => 'Vendor', 'field' => 'item_vendor', 'header_class' => '', 'column_class' => 'no-wrap', 'header_style' => '', 'column_style' => ''],
            ['name' => 'Status', 'field' => 'status', 'header_class' => '', 'column_class' => 'no-wrap', 'header_style' => 'text-align:center', 'column_style' => ''],
        ]);

        return $headers;
    }

    const BOM_FILTERS = [
        [
            'colSpan' => 'auto',
            'label' => 'BOM No',
            'id' => 'doc_number_filter',
            'requestName' => 'document_number',
            'term' => 'report_BOM_documents',
            'value_key' => 'id',
            'label_key' => 'document_number',
            'type' => 'input_text'
        ],
        [
            'colSpan' => 'auto',
            'label' => 'Product',
            'id' => 'product_filter',
            'requestName' => 'product_id',
            'term' => 'pr_item',
            'value_key' => 'id',
            'label_key' => 'item_name',
            'type' => 'auto_complete'
        ],
        [
            'colSpan' => 'auto',
            'label' => 'Item',
            'id' => 'item_filter',
            'requestName' => 'item_id',
            'term' => 'raw_items',
            'value_key' => 'id',
            'label_key' => 'item_name',
            'type' => 'auto_complete'
        ],
        [
            'colSpan' => 'auto',
            'label' => 'Station',
            'id' => 'station_filter',
            'requestName' => 'station_id',
            'term' => 'all_stations',
            'value_key' => 'id',
            'label_key' => 'name',
            'type' => 'auto_complete'
        ],
        [
            'colSpan' => 'auto',
            'label' => 'Status',
            'id' => 'status',
            'requestName' => 'status',
            'term' => 'document_statuses',
            'value_key' => 'id',
            'label_key' => 'name',
            'type' => 'auto_complete'
        ],
        [
            'colSpan' => 'auto',
            'label' => 'Company',
            'id' => 'company_filter',
            'requestName' => 'company_id',
            'term' => 'companies',
            'value_key' => 'id',
            'label_key' => 'name',
            'type' => 'auto_complete',
            'dependent' => ['organization_filter']
        ],
        [
            'colSpan' => 'auto',
            'label' => 'Organization',
            'id' => 'organization_filter',
            'requestName' => 'organization_id',
            'term' => 'organizations',
            'value_key' => 'id',
            'label_key' => 'name',
            'type' => 'auto_complete',
            'dependent' => ['location_filter']
        ],
        [
            'colSpan' => 'auto',
            'label' => 'Location',
            'id' => 'store_name',
            'requestName' => 'location_id',
            'term' => 'location',
            'value_key' => 'id',
            'label_key' => 'store_name',
            'type' => 'auto_complete'
        ],
        
    ];
}