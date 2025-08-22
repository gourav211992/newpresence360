<?php

namespace App\Http\Controllers\WHM;

use App\Helpers\CommonHelper;
use App\Helpers\ConstantHelper;
use App\Http\Controllers\Controller;
use App\Models\StockLedgerReservation;
use App\Models\WHM\ErpItemUniqueCode;
use App\Models\WHM\ErpWhmJob;
use Illuminate\Http\Request;
Use Pdf;
use Milon\Barcode\Facades\DNS2DFacade as DNS2D;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PrintQrController extends Controller
{
    public function getQrcodes(Request $request){
        $validator = Validator::make($request->all(),[
            'job_id' => ['required'],
        ],[
            'job_id.required' => 'Job id is required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first()
            ], 422);
        }

        $job = ErpWhmJob::find($request->job_id);
        if (!$job) {
            return response()->json(['message' => 'Invalid Job.'], 404);
        }

        if($job->type == CommonHelper::PICKING){

            $plHeaderId = $job->morphable_id;

            $reservedStock = StockLedgerReservation::where('issue_book_type',ConstantHelper::PL_SERVICE_ALIAS)
                ->where('issue_header_id',$plHeaderId);

            $transType = $reservedStock->pluck('receipt_book_type')
                ->toArray();

            $mrnItemIds = $reservedStock->pluck('receipt_detail_id')
                ->toArray();
            
            $mrnIds = $reservedStock->pluck('receipt_header_id')
                ->unique() 
                ->toArray();

            $items = ErpItemUniqueCode::whereIn('morphable_id',$mrnItemIds)
            ->whereIn('trns_type',$transType)
            ->whereNull('utilized_id')
            ->select('item_name','item_code','item_attributes','item_uid')
            ->get();
        }
        else{
            $items = ErpItemUniqueCode::where('job_id', $job->id)
                ->select('item_name','item_code','item_attributes','item_uid')
                ->get();
        }

        
        if ($items->isEmpty()) {
            return response()->json(['message' => 'No items found.'], 404);
        }

        foreach ($items as $item) {
            // Proper PNG QR code ko base64 encode karo
            $barcode = \DNS2D::getBarcodePNG($item->item_uid, 'QRCODE', 8, 8, [0,0,0]);
            $item->qr_code = 'data:image/png;base64,' . $barcode;
        }

        $pdf = \PDF::loadView('whm.pdfs.barcode-pdf', compact('items'))
            ->setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);

        return $pdf->download('item-barcodes.pdf');

    }
}
