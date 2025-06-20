<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Helpers\ConstantHelper;
use App\Traits\Deletable;

class StockAccount extends Model
{
    use HasFactory, SoftDeletes,Deletable, DefaultGroupCompanyOrg;

    protected $table = 'erp_stock_accounts';

    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'ledger_group_id',
        'ledger_id',
        'category_id',
        'sub_category_id',
        'item_id',
        'book_id',
        'book_code',
        'status'
    ];

    protected $casts = [
        'item_id' => 'array',
        'book_id' => 'array',
    ];

   
    public function group()
    {
        return $this->belongsTo(OrganizationGroup::class, 'group_id');
    }

    public function company()
    {
        return $this->belongsTo(OrganizationCompany::class, 'company_id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    public function ledgerGroup()
    {
        return $this->belongsTo(Group::class, 'ledger_group_id');
    }

    public function ledger()
    {
        return $this->belongsTo(Ledger::class, 'ledger_id');
    }

    public function subCategory()
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function items()
    {
        return Item::whereIn('id', $this->item_id)->get();  
    }

    public function book()
    {
        return Book::whereIn('id', $this->book_id)->get();  
    }

}
