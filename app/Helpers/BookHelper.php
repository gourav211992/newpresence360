<?php

namespace App\Helpers;

use App\Helpers\Helper;
use App\Helpers\ServiceParametersHelper;
use App\Models\Book;
use stdClass;

class BookHelper
{
	// In your Service or Helper class, e.g., BookHelper.php
	public static function fetchBookDocNoAndParameters($bookId, $documentDate)
	{
	    try {
	        $book = Book::find($bookId);
	        if (!$book) {
	            return ['data' => [], 'message' => "No record found!", 'status' => 404];
	        }

	        $docNum = Helper::generateDocumentNumberNew($book->id, $documentDate);
	        $parameters = new stdClass();
	        foreach (ServiceParametersHelper::SERVICE_PARAMETERS as $paramName => $paramNameVal) {
	            $param = ServiceParametersHelper::getBookLevelParameterValue($paramName, $book->id)['data'];
	            $parameters->{$paramName} = $param;
	        }

	        return [
	            'data' => [
	                'doc' => $docNum,
	                'book_code' => $book->book_code,
	                'parameters' => $parameters
	            ],
	            'message' => "fetched!",
	            'status' => 200
	        ];
	    } catch (Exception $ex) {
	        return [
	            'message' => 'Internal Server Error',
	            'error' => $ex->getMessage(),
	            'status' => 500
	        ];
	    }
	}
}