<?php

namespace App\Models;

use App\Helpers\InventoryHelper;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\DefaultGroupCompanyOrg;
use App\Traits\Deletable;
use Illuminate\Support\Facades\Log;


class Voucher extends Model
{
    protected $table = 'erp_vouchers';

    use HasFactory, DefaultGroupCompanyOrg, Deletable;
    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            $model->document_status = $model->approvalStatus;
            $model->approval_level = $model->approvalLevel;
        });
    }

    protected static function booted()
    {
        static::addGlobalScope('defaultLocation', function ($builder) {
            $locs = InventoryHelper::getAccessibleLocations()->pluck('id')->toArray()??[];
            $builder->whereIn('erp_vouchers.location',$locs);
        });

        static::updated(function ($voucher) {
            if ($voucher->isDirty('approvalStatus') || $voucher->isDirty('document_status')) {
                $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

                // Find the file and line number of the first user-level call
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

                $logMessage = "Voucher Update (voucher_no: {$voucher->voucher_no}) | "
                    . implode(' | ', $changes)
                    . " | Source: {$file} (Line {$line})";

                Log::info($logMessage);
            }
        });
    }




    // protected $fillable = [
    //     'voucher_no',
    //     'voucher_name',
    //     'book_type_id',
    //     'date',
    //     'book_id',
    //     'document',
    //     'note',
    //     'remarks',
    //     'group_id',
    //     'company_id',
    //     'organization_id',
    //     'status',
    //     'approvalLevel',
    //     'approvalStatus'
    // ];

    protected $fillable = [
        'voucher_no',
        'document_date',
        'doc_number_type',
        'doc_reset_pattern',
        'doc_prefix',
        'doc_suffix',
        'doc_no',
        'voucher_name',
        'book_type_id',
        'book_id',
        'date',
        'amount',
        'currency_id',
        'currency_code',
        'org_currency_id',
        'org_currency_code',
        'org_currency_exg_rate',
        'comp_currency_id',
        'comp_currency_code',
        'comp_currency_exg_rate',
        'group_currency_id',
        'group_currency_code',
        'group_currency_exg_rate',
        'reference_service',
        'reference_doc_id',
        'document',
        'remarks',
        'group_id',
        'company_id',
        'organization_id',
        'created_at',
        'updated_at',
        'voucherable_type',
        'voucherable_id',
        'approvalLevel',
        'approvalStatus',
        'document_status',
        'revision_number',
        'revision_date',
        'location'
    ];

    protected $appends = [
        'created_by'
    ];

    public function documents()
    {
        return $this->belongsTo(OrganizationService::class, 'book_type_id');
    }

    public function series()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function items()
    {
        return $this->hasMany(ItemDetail::class);
    }
    public function ledger_items()
    {
        return $this->hasMany(ItemDetail::class)->select('credit_amt AS credit_amount', 'debit_amt AS debit_amount', 'ledger_parent_id', 'ledger_parent_id AS ledger_group_id', 'ledger_id', 'entry_type', 'due_date');
    }

    public function approvals()
    {
        return $this->hasMany(ApprovalProcess::class);
    }

    public function voucherable()
    {
        return $this->morphTo();
    }
    public function getCreatedByAttribute()
    {
        return $this->voucherable_id;
    }

    public function ErpLocation()
    {
        return $this->belongsTo(ErpStore::class, 'location', 'id');
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'id');
    }
}
