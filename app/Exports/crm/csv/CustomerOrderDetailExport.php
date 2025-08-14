<?php

namespace App\Exports\crm\csv;

use App\Helpers\GeneralHelper;
use App\Helpers\Helper;
use App\Models\Country;
use App\Models\ErpCustomer;
use App\Models\ErpOrderItem;
use Carbon\Carbon;

class CustomerOrderDetailExport
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function export($fileName,$orderItems,$currencyMaster)
    {
        $filePath = public_path($fileName);
        $directoryPath = dirname($filePath);

        // // Check if the directory exists, and create it if not
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0777, true);  // 0777 is the permission, true makes it recursive
        }

        $header = [
            'Order Date',
            'Order No.',
            'Store Type',
            'Item Code',
            'Item Name',
            'Uom',
            'Delivery Date',
            'Order Value',
            'Deliver Qty',
            'Balance Qty',
        ];

        $handle = fopen($filePath, 'w');

        fputcsv($handle, $header);

        $orderItems->chunk(1000, function ($rows) use ($handle,$currencyMaster) {
                foreach ($rows as $data) {
                    $row = array();
                    $row[] = $data->order_date ? GeneralHelper::dateFormat($data->order_date) : '';
                    $row[] = '"' . $data->order_number . '"';
                    $row[] = '"' . $data->store_type . '"';
                    $row[] = '"' . $data->item_code . '"';
                    $row[] = '"' . $data->item_name . '"';
                    $row[] = '"' . $data->uom . '"';
                    $row[] = $data->delivery_date ? GeneralHelper::dateFormat($data->delivery_date) : '-';
                    $row[] = $data->total_order_value ? @$currencyMaster->symbol.''.$data->total_order_value : '-';
                    $row[] = '"' . $data->order_quantity . '"';
                    $row[] = '"' . $data->delivered_quantity . '"';
                    $row[] = $data->order_quantity - $data->delivered_quantity;

                    fwrite($handle, implode(",", $row) . PHP_EOL);
                }
            });

        fclose($handle);

        return true;

    }
}
