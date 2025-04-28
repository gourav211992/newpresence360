<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\DefaultGroupCompanyOrg;
use App\Helpers\ConstantHelper;
use App\Traits\Deletable;

class SalesAccount extends Model
{
    use HasFactory, SoftDeletes, Deletable, DefaultGroupCompanyOrg;

    protected $table = 'erp_sales_accounts';

    protected $fillable = [
        'group_id',
        'company_id',
        'organization_id',
        'customer_category_id',
        'customer_sub_category_id', 
        'customer_id',
        'item_category_id', 
        'item_sub_category_id',
        'item_id',
        'book_id',
        'ledger_group_id',
        'ledger_id',
        'status',
    ];

    protected $casts = [
        'item_id' => 'array',
        'customer_id' => 'array',
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

    public function items()
    {
        return Item::whereIn('id', $this->item_id)->get();  
    }

    public function book()
    {
        return Book::whereIn('id', $this->book_id)->get();  
    }


    public function customerCategory()
    {
        return $this->belongsTo(Category::class, 'customer_category_id');
    }

    public function customerSubCategory()
    {
        return $this->belongsTo(Category::class, 'customer_sub_category_id');
    }

    public function customer()
    {
        return Customer::whereIn('id', $this->customer_id)->get();  
    }

    public function itemCategory()
    {
        return $this->belongsTo(Category::class, 'item_category_id');
    }

    public function itemSubCategory()
    {
        return $this->belongsTo(Category::class, 'item_sub_category_id');
    }
    
}
