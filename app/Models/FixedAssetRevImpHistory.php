<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Helpers\Helper;
use App\Traits\Deletable;
use Illuminate\Support\Facades\DB;


class FixedAssetRevImpHistory extends Model
{
    use HasFactory, SoftDeletes, DefaultGroupCompanyOrg, Deletable;

    protected $table = 'erp_finance_fixed_asset_rev_history';

    protected $guarded = ['id'];
    public function book(){
       return $this->belongsTo(Book::class, 'book_id');
    }
    public function category()
    {
        return $this->belongsTo(ErpAssetCategory::class, 'category_id');
    }
    
    
}
