<?php
namespace App\Http\Controllers;

use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Storage;
class EinvoicePdfController extends Controller
{

    public function generateInvoiceQrPdf()
    {
        $data = [
            'AckNo' => '272510000015944',
            'AckDt' => '2025-03-19 15:54:00',
            'Irn' => 'a8a9788e4cd219f56608e0cc294c385f106675269f4b792cf2b9fe5e24081912',
            'SignedInvoice' => 'eyJhbGciOiJSUzI1NiIsImtpZCI6IjE1MTNCODIxRUU0NkM3NDlBNjNCODZFMzE4QkY3MTEwOTkyODdEMUYiLCJ4NXQiOiJGUk80SWU1R3gwbW1PNGJqR0w5eEVKa29mUjgiLCJ0eXAiOiJKV1QifQ.eyJpc3MiOiJOSUMgU2FuZGJveCIsImRhdGEiOiJ7XCJBY2tOb1wiOjI3MjUxMDAwMDAxNTk0NCxcIkFja0R0XCI6XCIyMDI1LTAyLTA2IDEyOjA2OjAwXCIsXCJJcm5cIjpcImE4YTk3ODhlNGNkMjE5ZjU2NjA4ZTBjYzI5NGMzODVmMTA2Njc1MjY5ZjRiNzkyY2YyYjlmZTVlMjQwODE5MTJcIixcIlZlcnNpb25cIjpcIjEuMVwiLFwiVHJhbkR0bHNcIjp7XCJUYXhTY2hcIjpcIkdTVFwiLFwiU3VwVHlwXCI6XCJCMkJcIixcIlJlZ1JldlwiOlwiWVwiLFwiSWdzdE9uSW50cmFcIjpcIk5cIn0sXCJEb2NEdGxzXCI6e1wiVHlwXCI6XCJJTlZcIixcIk5vXCI6XCJERUwvVEVTVC8yNlwiLFwiRHRcIjpcIjA2LzAyLzIwMjVcIn0sXCJTZWxsZXJEdGxzXCI6e1wiR3N0aW5cIjpcIjA3QUFBQ1MwMTg5QjJaUFwiLFwiTGdsTm1cIjpcIlNIRUVMQSBGT0FNIExURCBIT1wiLFwiQWRkcjFcIjpcIjYwNCBBc2hhZGVlcCwgOSBIYWlsZXkgUm9hZCwgUElOQ09ERSA6IDExMDAwMVwiLFwiTG9jXCI6XCJOZXcgRGVsaGlcIixcIlBpblwiOjExMDAwMSxcIlN0Y2RcIjpcIjdcIn0sXCJCdXllckR0bHNcIjp7XCJHc3RpblwiOlwiMTFBQUFDVDUxMzFBMlo5XCIsXCJMZ2xObVwiOlwiVElUQU4gQ09NUEFOWSBMSU1JVEVEIC0gV0FUQ0ggRElWSVNJT05cIixcIlBvc1wiOlwiMTFcIixcIkFkZHIxXCI6XCJOSDMxLFRJVEFOIENPTVBBTlkgTFREIFdBVENIIERJVklTSU9OIEJBR0hFWUtIT0xBIFBPIE1BSkhJVEFSLCwgUkFOR1BPLCBTSUtLSU1cIixcIkxvY1wiOlwiUkFOR1BPXCIsXCJQaW5cIjo3MzcxMzIsXCJTdGNkXCI6XCIxMVwifSxcIkRpc3BEdGxzXCI6e1wiTm1cIjpcIlNIRUVMQSBGT0FNIExURCBIT1wiLFwiQWRkcjFcIjpcIjYwNCBBc2hhZGVlcCwgOSBIYWlsZXkgUm9hZCwgUElOQ09ERSA6IDExMDAwMVwiLFwiTG9jXCI6XCJOZXcgRGVsaGlcIixcIlBpblwiOjExMDAwMSxcIlN0Y2RcIjpcIjA3XCJ9LFwiU2hpcER0bHNcIjp7XCJHc3RpblwiOlwiMTFBQUFDVDUxMzFBMlo5XCIsXCJMZ2xObVwiOlwiVElUQU4gQ09NUEFOWSBMSU1JVEVEIC0gV0FUQ0ggRElWSVNJT05cIixcIlRyZE5tXCI6XCJrdXZlbXB1IGxheW91dFwiLFwiQWRkcjFcIjpcIk5IMzEsVElUQU4gQ09NUEFOWSBMVEQgV0FUQ0ggRElWSVNJT04gQkFHSEVZS0hPTEEgUE8gTUFKSElUQVIsLCBSQU5HUE8sIFNJS0tJTVwiLFwiTG9jXCI6XCJSQU5HUE9cIixcIlBpblwiOjczNzEzMixcIlN0Y2RcIjpcIjExXCJ9LFwiSXRlbUxpc3RcIjpbe1wiSXRlbU5vXCI6MCxcIlNsTm9cIjpcIjFcIixcIklzU2VydmNcIjpcIk5cIixcIlByZERlc2NcIjpcIlAtUkVOVElORyBPRiBJTU1PVkFCTEUgUFJPUEVSVFlcIixcIkhzbkNkXCI6XCIzOTIzMzAxMFwiLFwiUXR5XCI6MSxcIlVuaXRcIjpcIk5PU1wiLFwiVW5pdFByaWNlXCI6MTQ1MjAwMCxcIlRvdEFtdFwiOjE0NTIwMDAsXCJEaXNjb3VudFwiOjAsXCJQcmVUYXhWYWxcIjowLFwiQXNzQW10XCI6MTQ1MjAwMCxcIkdzdFJ0XCI6MTgsXCJJZ3N0QW10XCI6MjYxMzYwLFwiQ2dzdEFtdFwiOjAsXCJTZ3N0QW10XCI6MCxcIkNlc1J0XCI6MCxcIkNlc0FtdFwiOjAsXCJDZXNOb25BZHZsQW10XCI6MCxcIlN0YXRlQ2VzUnRcIjowLFwiU3RhdGVDZXNBbXRcIjowLFwiU3RhdGVDZXNOb25BZHZsQW10XCI6MCxcIk90aENocmdcIjowLFwiVG90SXRlbVZhbFwiOjE3MTMzNjB9XSxcIlZhbER0bHNcIjp7XCJBc3NWYWxcIjoxNDUyMDAwLFwiQ2dzdFZhbFwiOjAsXCJTZ3N0VmFsXCI6MCxcIklnc3RWYWxcIjoyNjEzNjAsXCJDZXNWYWxcIjowLFwiU3RDZXNWYWxcIjowLFwiRGlzY291bnRcIjowLFwiT3RoQ2hyZ1wiOjAsXCJSbmRPZmZBbXRcIjowLFwiVG90SW52VmFsXCI6MTcxMzM2MCxcIlRvdEludlZhbEZjXCI6MH0sXCJBZGRsRG9jRHRsc1wiOlt7XCJVcmxcIjpcImh0dHBzOi8vZWludi1hcGlzYW5kYm94Lm5pYy5pblwiLFwiRG9jc1wiOlwiVGVzdCBEb2NcIixcIkluZm9cIjpcIkRvY3VtZW50IFRlc3RcIn1dLFwiRXdiRHRsc1wiOntcIlRyYW5zSWRcIjpcIjA5QUFSRko5MzM2TTFaRVwiLFwiVHJhbnNOYW1lXCI6XCJKU1JMIElORElBIExPR0lTVElDUyBMTFBcIixcIlRyYW5zTW9kZVwiOlwiMVwiLFwiRGlzdGFuY2VcIjoxMDAsXCJUcmFuc0RvY05vXCI6XCIxMjM0NVwiLFwiVHJhbnNEb2NEdFwiOlwiMDEvMDIvMjAyNVwiLFwiVmVoTm9cIjpcIlJKMTRHTjYxMDlcIixcIlZlaFR5cGVcIjpcIlJcIn19In0.w0BNZzj6PvYAknIukYzsinQE7tpd7vdfbwmD6JgChQkfFDR37tuV5oj5uZjfRjc0So4f4QrlS_AMdFaknOkU9WP9AAubXJsvXzdC2mP245nNoJaZWwt9bSzgbI9CNxnVkIr5sREM7R27L01119Z3neAMOsPYK_t7x0ddU2EgrRcHU1w4snrY6QRp1i3MnLNJ6hVMKlIS-UVTCz7TuaVn1QrWmNaTmCj5s6AbXuT1kSbigGxhlSD60o4WeuAG-2cGTG7RwGS-ld_QmlCv9miQUktI_xuGwooiNlHWuvtExycpNCy32mxYO3AioLRsXuQLo3PhN2I-zmfwrZKe6ucz2Q',
            'SignedQRCode' => 'eyJhbGciOiJSUzI1NiIsImtpZCI6IjE1MTNCODIxRUU0NkM3NDlBNjNCODZFMzE4QkY3MTEwOTkyODdEMUYiLCJ4NXQiOiJGUk80SWU1R3gwbW1PNGJqR0w5eEVKa29mUjgiLCJ0eXAiOiJKV1QifQ.eyJpc3MiOiJOSUMgU2FuZGJveCIsImRhdGEiOiJ7XCJTZWxsZXJHc3RpblwiOlwiMDdBQUFDUzAxODlCMlpQXCIsXCJCdXllckdzdGluXCI6XCIxMUFBQUNUNTEzMUEyWjlcIixcIkRvY05vXCI6XCJERUwvVEVTVC8yNlwiLFwiRG9jVHlwXCI6XCJJTlZcIixcIkRvY0R0XCI6XCIwNi8wMi8yMDI1XCIsXCJUb3RJbnZWYWxcIjoxNzEzMzYwLFwiSXRlbUNudFwiOjEsXCJNYWluSHNuQ29kZVwiOlwiMzkyMzMwMTBcIixcIklyblwiOlwiYThhOTc4OGU0Y2QyMTlmNTY2MDhlMGNjMjk0YzM4NWYxMDY2NzUyNjlmNGI3OTJjZjJiOWZlNWUyNDA4MTkxMlwiLFwiSXJuRHRcIjpcIjIwMjUtMDItMDYgMTI6MDY6MDBcIn0ifQ.Z8XTCaW-oMdEM0VR4A89PNqLZ7hWNe606j9afJGULs3YYjs-GSS6kPmc4Ku6hkBTdECt3ANJ6wJsJcJobb9cswT79_Np-i1W6O5HVEt10EJz4Qjlsqdu7NAiZJvMjm5tLK6FaWMB2ryJrgfxdrqRI8jVM1Otu_xvLha-aLn32Vwc6pNAC6T7USYppIOR6qGI1oF_UhBTROIvYVqsTvF3K1vTkvTLx2rfjFl6IfyVAjphzky8DemZV5e1uI8RpwNPWGguqF6M3NLW2sACZAD7tuP-KHIANXBMWhVoQ_9tYPtFjxlksV_xKUV9I-y9Ge6skBd-VO0geI35Vr9A9Ld-kA',
            'Status' => 'ACT',
        ];

        // Generate QR Code
        // $qrCodePath = $this->generateQRCode($data['SignedQRCode'], 'qr_' . $data['AckNo']);
        // Generate QR Code as base64
        $qrCodeBase64 = $this->generateQRCodeBase64($data['SignedQRCode']);

        // Generate Invoice PDF
        $pdfPath = $this->generateInvoicePdf($data, $qrCodeBase64);

        return response()->download($pdfPath)->deleteFileAfterSend();
    }


    private function generateQRCodeBase64($signedQRCode)
    {
        $qrCode = QrCode::create($signedQRCode);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        // Convert QR code to base64
        return 'data:image/png;base64,' . base64_encode($result->getString());
    }


    // Function to generate and save Invoice PDF
    private function generateInvoicePdf($data, $qrCodeBase64)
    {
        $options = new Options();
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);

        $html = view('einvoice.einvoice_pdf', compact('data', 'qrCodeBase64'))->render();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfPath = 'invoices/pdfs/invoice_' . $data['AckNo'] . '.pdf';
        Storage::disk('local')->put($pdfPath, $dompdf->output());

        return storage_path('app/' . $pdfPath);
    }

    // Function to generate and save QR code
    private function generateQRCode($signedQRCode, $fileName)
    {
        $qrCode = QrCode::create($signedQRCode);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $qrPath = 'invoices/qrcodes/' . $fileName . '.png';
        Storage::disk('local')->put($qrPath, $result->getString());

        return storage_path('app/' . $qrPath);
    }
}