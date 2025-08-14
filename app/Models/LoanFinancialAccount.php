<?php

namespace App\Models;
use App\Models\Ledger;
use App\Models\Group;

use Illuminate\Database\Eloquent\Model;

class LoanFinancialAccount extends Model
{
    // Specify the table associated with the model
    protected $table = 'erp_loan_financial_accounts';

    // Specify the attributes that are mass assignable
    protected $fillable = [
        'pro_ledger_id',
        'pro_ledger_group_id',
        'dis_ledger_id',
        'dis_ledger_group_id',
        'int_ledger_id',
        'int_ledger_group_id',
        'wri_ledger_id',
        'wri_ledger_group_id',
        'status'
    ];

    // If you have timestamps (created_at, updated_at) columns
    public $timestamps = true;

    public function ledgers($id)
    {
       return Ledger::where('id',$id)->first();
    }

    public function groups($id)
    {
       return Group::where('id',$id)->first();
    }

    public function group($id)
    {
       
       $led =  Ledger::where('id',$id)->first();
       $item = is_string($led->ledger_group_id) && str_starts_with($led->ledger_group_id, '[') ? json_decode($led->ledger_group_id, true) : $led->ledger_group_id;
       if(is_string($led->ledger_group_id))
       {
        return Group::where('id',$item)->get();
       }
       else
       {
        return Group::whereIn('id',$item)->get();
       }
      
    }

}

