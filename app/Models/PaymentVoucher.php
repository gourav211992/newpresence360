<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Deletable;
use App\Traits\DefaultGroupCompanyOrg;
use Illuminate\Support\Facades\Log;
use App\Helpers\InventoryHelper;



class PaymentVoucher extends Model
{
    use HasFactory,Deletable,DefaultGroupCompanyOrg,Deletable;
    public $referencingRelationships = [
        'currency' => 'currency_id',
        'ledger' => 'ledger_id',
        'bank' => 'bank_id'
    ];
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
           // $model->approvalStatus = $model->document_status;
            $model->approval_level = $model->approvalLevel;
        });
    }
    
    

protected static function booted()
{
    static::addGlobalScope('defaultLocation', function ($builder) {
            $locs = InventoryHelper::getAccessibleLocations()->pluck('id')->toArray()??[];
            $builder->whereIn('location',$locs);
        });

    static::updated(function ($voucher) {
        if ($voucher->isDirty('approvalStatus') || $voucher->isDirty('document_status')) {
            $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

            // Loop to find the first user code (skip framework internals)
            foreach ($caller as $trace) {
                if (isset($trace['file']) && strpos($trace['file'], 'vendor') === false) {
                    $file = $trace['file'];
                    $line = $trace['line'];
                    break;
                }
            }

            $changes = [];

            if ($voucher->isDirty('approvalStatus')) {
                $changes[] = "approvalStatus: '{$voucher->getOriginal('approvalStatus')}' → '{$voucher->approvalStatus}'";
            }

            if ($voucher->isDirty('document_status')) {
                $changes[] = "document_status: '{$voucher->getOriginal('document_status')}' → '{$voucher->document_status}'";
            }

            $logMessage = "PaymentVoucher Update (voucher_no: {$voucher->voucher_no}) | "
                        . implode(' | ', $changes)
                        . " | Source: {$file} (Line {$line})";

            Log::info($logMessage);
        }
    });
}



    protected $guarded = ['id'];


    protected $table = 'erp_payment_vouchers';

    public function series()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }
    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function ledger()
    {
        return $this->belongsTo(Ledger::class, 'ledger_id')->where('status', 1);
    }
    
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function details()
    {
        return $this->hasMany(PaymentVoucherDetails::class,'payment_voucher_id');
    }

    public function approvals()
    {
        return $this->hasMany(ApprovalProcess::class);
    }
    public function created_by()
    {
        return $this->belongsTo(AuthUser::class, 'user_id');
    }

    public function ErpLocation()
    {
        return $this->belongsTo(ErpStore::class, 'location', 'id');
    }
    public function payments()
    {
        return $this->hasMany(VoucherReference::class,'payment_voucher_id');
    }
}
