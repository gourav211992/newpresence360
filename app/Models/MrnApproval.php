<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MrnApproval extends Model
{

    use SoftDeletes;

    protected $fillable = [
        'type',
        'mrn_header_id', 
        'approved_by', 
        'approval_date', 
        'approval_remark'
    ];

    protected $appends = [
    ];

    protected $hidden = ['deleted_at'];

    public function mrnHeader()
    {
        return $this->belongsTo(MrnHeader::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

}