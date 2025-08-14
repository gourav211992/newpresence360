<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApprovalProcess extends Model
{
    protected $table = 'erp_approval_processes';

    use HasFactory;

    protected $fillable = [
        'user_id',
        'voucher_id',
        'book_id',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

}
