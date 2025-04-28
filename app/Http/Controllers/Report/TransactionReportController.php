<?php

namespace App\Http\Controllers\Report;

use App\Exports\Reports\TransactionReportExport;
use App\Helpers\ConstantHelper;
use App\Helpers\Helper;
use App\Jobs\SendEmailJob;
use App\Models\AuthUser;
use App\Services\Reports\TransactionReport;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Validator;


class TransactionReportController extends Controller
{
    public function index(Request $request, string $serviceAlias)
    {
        $reportService = new TransactionReport($serviceAlias);
        $data = $reportService -> getIndexPageData();
        return view('reports.transaction', $data);
    }

    public function emailReport(Request $request)
    {
        try{
            // Validate email_to and email_cc
            $validator = Validator::make($request->all(), [
                'email_to' => ['required', 'array', 'min:1'],
                'email_to.*' => ['required', 'email'],
                'email_cc' => ['nullable', 'array'],
                'email_cc.*' => ['required_with:email_cc', 'email'],
            ],
            [
                'email_to.required' => 'At least one recipient email is required.',
                'email_to.array' => 'The recipient emails must be provided as an array.',
                'email_to.min' => 'At least one email address must be specified in email_to.',
                'email_to.*.required' => 'Each email address in email_to is required.',
                'email_to.*.email' => 'Each email in email_to must be a valid email address.',

                'email_cc.array' => 'The CC emails must be provided as an array.',
                'email_cc.*.required_with' => 'Each email in email_cc is required if CC is present.',
                'email_cc.*.email' => 'Each email in email_cc must be a valid email address.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator -> messages() -> first()
                ], 422);
            }
            $headers = $request->input('displayedHeaders');
            $originalData = $request->input('displayedData');
            $appliedFilters = $request->input('filters');

            $blankSpaces = count($headers) - 1;
            $centerPosition = (int)floor($blankSpaces / 2);

            $fileName = 'report.xlsx';
            $filePath = storage_path('app/public/custom-report/' . $fileName);
            $directoryPath = storage_path('app/public/custom-report');
            $customHeader = array_merge(
                array_fill(0, $centerPosition, ''),
                ['Custom Report' ],
                array_fill(0, $blankSpaces - $centerPosition, '')
            );

            $remainingSpaces = $blankSpaces - count($appliedFilters) + 1;
            $filterHeader = array_merge($appliedFilters, array_fill(0, $remainingSpaces, ''));

            $excelData = Excel::raw(new TransactionReportExport($customHeader, $filterHeader, $headers, $originalData), \Maatwebsite\Excel\Excel::XLSX);

            if (!file_exists($directoryPath)) {
                mkdir($directoryPath, 0755, true);
            }
            file_put_contents($filePath, $excelData);
            if (!file_exists($filePath)) {
                throw new \Exception('File does not exist at path: ' . $filePath);
            }
            $email_to = $request->email_to ?? [];
            $email_cc = $request->email_cc ?? [];

            foreach($email_to as $email)
            {
                $user = AuthUser::where('email', $email)
                ->where('organization_id', Helper::getAuthenticatedUser()->organization_id)
                ->where('status', ConstantHelper::ACTIVE)
                ->first();


                if (!$user) {
                    $user = new AuthUser();
                    $user->email = $email;
                }

                $title = "Custom Report Generated";
                $heading = "Custom Report";

                $remarks = $request->remarks ?? null;
                $mail_from = '';
                $mail_from_name = '';
                $cc = implode(', ', $email_cc);
                $bcc = null;
                $attachment = $filePath ?? null;
                // $name = $user->name;
                $description = <<<HTML
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 600px; background-color: #ffffff; padding: 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); font-family: Arial, sans-serif; line-height: 1.6;">
                    <tr>
                        <td>
                            <h2 style="color: #2c3e50; font-size: 24px; margin-bottom: 20px;">{$heading}</h2>
                            <p style="font-size: 16px; color: #555; margin-bottom: 20px;">
                                Dear <strong style="color: #2c3e50;">user</strong>,
                            </p>

                            <p style="font-size: 15px; color: #333; margin-bottom: 20px;">
                                We hope this email finds you well. Please find your inventory report attached below.
                            </p>
                            <p style="font-size: 15px; color: #333; margin-bottom: 30px;">
                                <strong>Remark:</strong> {$remarks}
                            </p>
                            <p style="font-size: 14px; color: #777;">
                                If you have any questions or need further assistance, feel free to reach out to us.
                            </p>
                        </td>
                    </tr>
                </table>
                HTML;
                self::sendMail($user,$title,$description,$cc,$bcc, $attachment,$mail_from,$mail_from_name);
            }
            return response()->json([
                'status' => 'success',
                'message' => 'emails sent successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function sendMail($receiver, $title, $description, $cc= null, $bcc= null, $attachment, $mail_from=null, $mail_from_name=null)
    {
        if (!$receiver || !isset($receiver->email)) {
            return "Error: Receiver details are missing or invalid.";
        }
        dispatch(new SendEmailJob($receiver, $mail_from, $mail_from_name,$title,$description,$cc,$bcc, $attachment));
        return response() -> json([
            'status' => 'success',
            'message' => 'Email request sent succesfully',
        ]);

    }

}
