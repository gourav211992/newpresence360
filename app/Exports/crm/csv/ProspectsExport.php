<?php

namespace App\Exports\crm\csv;

use App\Helpers\GeneralHelper;
use App\Helpers\Helper;

class ProspectsExport
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
            'Customer Name',
            'Lead Status',
            'Customer Value',
            'Industry',
            'Current Supplier Split',
            'Last Contact Date',
        ];

        $handle = fopen($filePath, 'w');

        fputcsv($handle, $header);

        $customers->chunk(1000, function ($rows) use ($handle) {
                foreach ($rows as $data) {
                    $row = array();
                    $row[] = '"' . $data->company_name . '"';
                    $row[] =  isset($data->meetingStatus->title) ? '"' .$data->meetingStatus->title.'"' : '-' ;
                    $row[] =  '"' .$data->sales_figure.'"' ;
                    $row[] =  isset($data->industry->name) ? '"' .$data->industry->name.'"' : '-' ;
                    $row[] = '';
                    $row[] =  isset($data->latestDiary->created_at) ? GeneralHelper::dateFormat($data->latestDiary->created_at) : '-' ;
                                            

                    fwrite($handle, implode(",", $row) . PHP_EOL);
                }
            });

        fclose($handle);

        return true;

    }
}
