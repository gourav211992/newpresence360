<?php

namespace App\Exports\crm\csv;

use App\Helpers\GeneralHelper;
use App\Helpers\Helper;
use App\Models\Country;
use App\Models\ErpCustomer;
use Carbon\Carbon;

class CustomerOrderExport
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function export($fileName,$customers)
    {

        $filePath = public_path($fileName);
        $directoryPath = dirname($filePath);

        // // Check if the directory exists, and create it if not
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0777, true);  // 0777 is the permission, true makes it recursive
        }

        $header = [
            'Code',
            'Customer Name',
            'ORder Count',
            'Order Value',
            'Location'
        ];

        $handle = fopen($filePath, 'w');

        fputcsv($handle, $header);

        $customers->groupBy('erp_customers.customer_code')
            ->orderBy('distinct_order_count', 'DESC')
            ->chunk(1000, function ($rows) use ($handle) {
                foreach ($rows as $data) {
                    $row = array();
                    $row[] = '"' . $data->customer_code . '"';
                    $row[] = '"' . $data->company_name . '"';
                    $row[] = $data->distinct_order_count ? $data->distinct_order_count : 0;
                    $row[] = $data->total_order_value_sum ? Helper::currencyFormat($data->total_order_value_sum,'display') : 0;
                    $row[] = '"' . $data->full_address . '"';

                    fwrite($handle, implode(",", $row) . PHP_EOL);
                }
            });

        fclose($handle);

        return true;

    }
}
