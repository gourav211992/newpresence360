<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\BookType;
use App\Helpers\Helper;
use DB;

class LoanManagement extends Model
{
    use HasFactory;

    public static function getViewDetail(){
        return DB::table('home_loans')->select('id as h_id', 'type as type', 'name as h_name', 'email as h_email', 'mobile as h_phn','loan_amount as h_amount', 'ref_no', 'appli_no', 'age', 'status', 'concern_name')->get();
    }  
    
    public static function getOccupation(){
        return DB::table('erp_loan_occupations')->get();
    }

    public static function getBookType($name = ''){
        $user = Helper::getAuthenticatedUser();
        $book_type = BookType::where('status','Active')->whereHas('service', function($query) use ($name) {
            $query->where('alias', $name);
        })
        ->where('organization_id',$user->organization_id)
        ->pluck('id');
        $series = Book::whereIn('booktype_id',$book_type)->get();
        
        return $series;
    }
}