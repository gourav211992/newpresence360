<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherHistory extends Model
{
    protected $table = 'erp_vouchers_history';

    use HasFactory;

    protected $fillable = [
        'source_id',
        'voucher_no',
        'voucher_name',
        'book_type_id',
        'date',
        'book_id',
        'document',
        'note',
        'remarks',
        'group_id',
        'company_id',
        'organization_id',
        'status',
        'approvalLevel',
        'approvalStatus'
    ];


    public function documents()
    {
        return $this->belongsTo(BookType::class, 'book_type_id');
    }

    public function series()
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    public function items()
    {
        return $this->hasMany(ItemDetailHistory::class,'voucher_id');
    }

    public function approvals()
    {
        return $this->hasMany(ApprovalProcess::class);
    }

    public function voucherable()
    {
        return $this->morphTo();
    }
}
