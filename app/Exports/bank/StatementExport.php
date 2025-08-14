<?php

namespace App\Exports\bank;

use App\Helpers\CommonHelper;
use App\Models\BankReconciliation\BankStatement;
use App\Models\BankReconciliation\FailedBankStatement;

class StatementExport
{
    /**
    * @return \Illuminate\Support\Collection
    */

    public function export($fileName,$request,$id)
    {
        $statementsQuery = BankStatement::where('uid', $request->batch_uid);
            
        if ($request->type === 'failed-statement') {
            $statementsQuery = FailedBankStatement::where('uid', $request->batch_uid);
        }
        
        if ($statementsQuery->count() === 0) {
            // Write only header with no data
            $this->writeToCsv(collect([]), $fileName, $request);
            return;
        }

        $statementsQuery->chunk(1000, function ($data) use ($fileName, $request) {
            $this->writeToCsv($data, $fileName, $request);
        });
    }

    /**
     * Write data to CSV file
     * 
     * @param $gstrInvoiceTypes
     * @param string $fileName
     */
    private function writeToCsv($data, $fileName, $request)
    {   
        $header = [
            'Date',
            'Narration',
            'Chq/Ref No',
            'Debit Amount',
            'Credit Amount',
            'Balance',
            'Remarks'
        ];

        $rows = [];
        foreach ($data as $statement) {
            $rows[] = [
                $statement->date ? CommonHelper::dateFormat($statement->date) : '',
                $statement->narration, 
                $statement->ref_no, 
                $statement->debit_amt, 
                $statement->credit_amt, 
                $statement->balance,
                $request->type == 'failed-statement' ? $statement->errors : 'Success',
            ];
        }

        $filePath = public_path($fileName);
        $directoryPath = dirname($filePath);

        // Check if the directory exists, and create it if not
        if (!is_dir($directoryPath)) {
            mkdir($directoryPath, 0777, true);  // 0777 is the permission, true makes it recursive
        }

        $handle = fopen($filePath, 'w');

        // Write header
        fputcsv($handle, $header);

        // Write rows
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle); // Close the file after all chunks are processed
    }

}
