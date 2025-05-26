<?php

namespace App\Http\Controllers\API;

use App\Exceptions\ApiGenericException;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Validator;

class BookController extends Controller
{
    public function generateDocumentNumber(Request $request)
    {
        try {

            $validator = Validator::make($request->all(),[
                'book_id' => 'required|numeric|string',
                'document_date' => 'required|date',
            ]);
            if($validator->fails()){
                throw new ApiGenericException($validator->errors()->first());
            }
            $authUser = $request -> user();
            $documentNoDetails = Helper::generateDocumentNumberNew($request -> book_id, $request -> document_date, null, $authUser);
            return array(
                'message' => 'Document Number generated',
                'data' => array(
                    'document_no_details' => $documentNoDetails
                )
            );
        } catch(Exception $ex) {
            throw new ApiGenericException($ex -> getMessage());
        }
    }
}
